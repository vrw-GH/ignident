<?php
/**
 * Trello Integration Class
 *
 * Handles all Trello API interactions for form submissions including
 * board/list management and card creation.
 *
 * @package HTContactForm
 * @subpackage Integrations
 */

namespace HTContactForm\Integrations;

use HTContactFormAdmin\Includes\Services\Helper;
use WP_Error;
use WP_REST_Response;

/**
 * Trello Integration Handler
 *
 * Provides functionality to integrate form submissions with Trello
 * boards using their REST API.
 */
class Trello {
    /**
     * Trello API key
     *
     * @var string|null
     */
    private $api_key = null;

    /**
     * Trello API token
     *
     * @var string|null
     */
    private $api_token = null;

    /**
     * Trello API base URL
     *
     * @var string
     */
    private $api_url = 'https://api.trello.com/1/';

    /**
     * Helper class instance
     *
     * @var Helper|null
     */
    private $helper = null;

    /**
     * Instances storage by credentials
     *
     * @var array
     */
    private static $instances = [];

    /**
     * Get instance by credentials
     *
     * @param string|null $api_key Trello API key
     * @param string|null $api_token Trello API token
     * @return self Instance of the Trello class
     */
    public static function get_instance($api_key = null, $api_token = null) {
        $key = md5(($api_key ?? '') . ($api_token ?? ''));
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new self($api_key, $api_token);
        }
        return self::$instances[$key];
    }

    /**
     * Constructor
     *
     * @param string|null $api_key Trello API key
     * @param string|null $api_token Trello API token
     */
    public function __construct($api_key = null, $api_token = null) {
        $this->api_key = is_string($api_key) ? trim($api_key) : '';
        $this->api_token = is_string($api_token) ? trim($api_token) : '';
        if (class_exists(Helper::class)) {
            $this->helper = Helper::get_instance();
        }
    }

    /**
     * Make an API request to Trello
     *
     * @param string $endpoint The API endpoint to call
     * @param string $method The HTTP method to use (GET, POST, PUT, DELETE)
     * @param array $data The data to send with the request
     * @return array|WP_Error Response data on success, WP_Error on failure
     */
    private function api_request($endpoint, $method = 'GET', $data = []) {
        if (empty($this->api_key) || empty($this->api_token)) {
            return new WP_Error('credentials_missing', __('Trello API key and token are required', 'ht-contactform'));
        }

        // Add authentication to the endpoint
        // Note: Trello API requires key/token as query parameters - this is a Trello API limitation
        // These credentials are only used server-side and are not exposed to the browser
        $auth_params = http_build_query([
            'key' => $this->api_key,
            'token' => $this->api_token,
        ]);

        $url = $this->api_url . ltrim($endpoint, '/');
        $url .= (strpos($url, '?') !== false ? '&' : '?') . $auth_params;

        $args = [
            'method'    => strtoupper($method),
            'timeout'   => 30,
            'headers'   => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
        ];

        if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $args['body'] = wp_json_encode($data);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            return new WP_Error(
                'http_request_failed',
                sprintf(
                    /* translators: %s: Error message */
                    __('HTTP request failed: %s', 'ht-contactform'),
                    $response->get_error_message()
                )
            );
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        // Handle rate limiting
        if ($response_code === 429) {
            return new WP_Error(
                'rate_limited',
                __('Trello API rate limit exceeded. Please try again later.', 'ht-contactform'),
                ['status' => 429]
            );
        }

        if ($response_code < 200 || $response_code >= 300) {
            return new WP_Error(
                'api_error',
                sprintf(
                    /* translators: %1$d: HTTP status code, %2$s: Error message */
                    __('Trello API error [%1$d]: %2$s', 'ht-contactform'),
                    $response_code,
                    $response_body
                ),
                ['status' => $response_code]
            );
        }

        $decoded_data = json_decode($response_body, true);
        if (json_last_error() !== JSON_ERROR_NONE && !empty($response_body)) {
            return new WP_Error(
                'json_decode_error',
                __('Invalid JSON response from Trello API', 'ht-contactform'),
                ['response_body' => substr($response_body, 0, 200)]
            );
        }

        return $decoded_data;
    }

    /**
     * Verify API credentials
     *
     * @return array|WP_Error Member data on success, WP_Error on failure
     */
    public function verify() {
        $response = $this->api_request('members/me', 'GET');

        if (is_wp_error($response)) {
            return $response;
        }

        return [
            'id' => $response['id'] ?? '',
            'username' => $response['username'] ?? '',
            'fullName' => $response['fullName'] ?? '',
            'email' => $response['email'] ?? '',
        ];
    }

    /**
     * Get all boards for the authenticated user
     *
     * @return array|WP_Error Array of boards on success, WP_Error on failure
     */
    public function get_boards() {
        $response = $this->api_request('members/me/boards?fields=name,url,closed');

        if (is_wp_error($response)) {
            return $response;
        }

        if (is_array($response)) {
            // Filter out closed boards
            return array_values(array_filter(
                array_map(function($board) {
                    return [
                        'id' => $board['id'],
                        'name' => $board['name'],
                        'url' => $board['url'] ?? '',
                    ];
                }, $response),
                function($board) use ($response) {
                    $original = array_filter($response, fn($b) => $b['id'] === $board['id']);
                    $original = reset($original);
                    return empty($original['closed']);
                }
            ));
        }

        return [];
    }

    /**
     * Get all lists for a board
     *
     * @param string $board_id Trello board ID
     * @return array|WP_Error Array of lists on success, WP_Error on failure
     */
    public function get_lists($board_id) {
        if (empty($board_id)) {
            return new WP_Error('missing_board_id', __('Board ID is required', 'ht-contactform'));
        }

        $board_id = sanitize_text_field($board_id);
        $response = $this->api_request("boards/{$board_id}/lists?fields=name,closed");

        if (is_wp_error($response)) {
            return $response;
        }

        if (is_array($response)) {
            // Filter out closed/archived lists
            return array_values(array_filter(
                array_map(function($list) {
                    return [
                        'id' => $list['id'],
                        'name' => $list['name'],
                    ];
                }, $response),
                function($list) use ($response) {
                    $original = array_filter($response, fn($l) => $l['id'] === $list['id']);
                    $original = reset($original);
                    return empty($original['closed']);
                }
            ));
        }

        return [];
    }

    /**
     * Get labels for a board
     *
     * @param string $board_id Trello board ID
     * @return array|WP_Error Array of labels on success, WP_Error on failure
     */
    public function get_labels($board_id) {
        if (empty($board_id)) {
            return new WP_Error('missing_board_id', __('Board ID is required', 'ht-contactform'));
        }

        $board_id = sanitize_text_field($board_id);
        $response = $this->api_request("boards/{$board_id}/labels?fields=name,color");

        if (is_wp_error($response)) {
            return $response;
        }

        if (is_array($response)) {
            return array_map(function($label) {
                return [
                    'id' => $label['id'],
                    'name' => $label['name'] ?: ucfirst($label['color']),
                    'color' => $label['color'],
                ];
            }, $response);
        }

        return [];
    }

    /**
     * Create a card in a Trello list (form submission)
     *
     * @param object $integration Integration settings
     * @param array $form Form data
     * @param array $form_data Form submission data
     * @param array $meta Meta data
     * @return WP_Error|WP_REST_Response Response
     */
    public function create_card($integration, $form, $form_data, $meta) {
        // Validate integration object
        if (!is_object($integration)) {
            return new WP_Error('invalid_integration', __('Invalid integration configuration', 'ht-contactform'));
        }

        if (empty($integration->enabled) || empty($integration->list_id)) {
            return new WP_Error('integration_disabled', __('Integration is not enabled or list not selected', 'ht-contactform'));
        }

        // Build card data
        $card_data = [
            'idList' => sanitize_text_field($integration->list_id),
        ];

        // Process card name
        if (!empty($integration->card_name)) {
            $card_data['name'] = $this->process_field_value($integration->card_name, $form_data, $form);
        } else {
            $card_data['name'] = sprintf(
                /* translators: %s: Form name */
                __('Form Submission: %s', 'ht-contactform'),
                $form['name'] ?? 'Contact Form'
            );
        }

        // Process card description
        if (!empty($integration->card_desc)) {
            $card_data['desc'] = $this->process_field_value($integration->card_desc, $form_data, $form);
        }

        // Process due date
        if (!empty($integration->card_due)) {
            $due_date = $this->process_field_value($integration->card_due, $form_data, $form);
            if (!empty($due_date)) {
                $timestamp = $this->parse_date($due_date);
                if ($timestamp !== false) {
                    $card_data['due'] = gmdate('Y-m-d\TH:i:s\Z', $timestamp);
                }
            }
        }

        // Process labels
        if (!empty($integration->card_labels) && is_array($integration->card_labels)) {
            $card_data['idLabels'] = implode(',', array_map('sanitize_text_field', $integration->card_labels));
        }

        // Process position
        if (!empty($integration->card_position)) {
            $card_data['pos'] = sanitize_text_field($integration->card_position);
        }

        // Make API request to create card
        $response = $this->api_request('cards', 'POST', $card_data);

        if (is_wp_error($response)) {
            do_action('ht_form/trello_integration_result', null, 'failed', $response->get_error_message());
            return $response;
        }

        do_action('ht_form/trello_integration_result', $response, 'success', __('Trello card created successfully', 'ht-contactform'));

        return new WP_REST_Response([
            'message' => __('Trello card created successfully', 'ht-contactform'),
            'card_id' => $response['id'] ?? null,
            'url' => $response['url'] ?? null,
        ], 200);
    }

    /**
     * Process field value with smart tags
     *
     * @param string $value The field value or smart tag
     * @param array $form_data Form submission data
     * @param array $form Form configuration
     * @return string Processed value
     */
    private function process_field_value($value, $form_data, $form) {
        return $this->helper->filter_vars($value, $form_data, $form);
    }

    /**
     * Parse date string into timestamp
     *
     * Auto-detects common date formats and converts to timestamp.
     *
     * @param string $date_string Date string to parse
     * @return int|false Unix timestamp or false on failure
     */
    private function parse_date($date_string) {
        $date_string = trim($date_string);

        // Use WordPress timezone setting
        $timezone = wp_timezone();

        // Check if time is included (HH:MM or HH:MM:SS)
        $has_time = preg_match('/\d{1,2}:\d{2}(:\d{2})?/', $date_string);

        // Detect format based on pattern and parse accordingly
        // ISO format: YYYY-MM-DD (e.g., 2026-01-21)
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $date_string)) {
            $format = $has_time ? 'Y-m-d H:i' : 'Y-m-d';
            $date = \DateTime::createFromFormat('!' . $format, $date_string, $timezone);
            if ($date !== false) {
                return $date->getTimestamp();
            }
        }

        // Date with slashes: check if first part > 12 to determine DD/MM or MM/DD
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})/', $date_string, $matches)) {
            $first = (int) $matches[1];

            // If first > 12, it must be DD/MM/YYYY
            if ($first > 12) {
                $format = $has_time ? 'd/m/Y H:i' : 'd/m/Y';
            } else {
                // Default to MM/DD/YYYY (US format)
                $format = $has_time ? 'm/d/Y H:i' : 'm/d/Y';
            }

            $date = \DateTime::createFromFormat('!' . $format, $date_string, $timezone);
            if ($date !== false) {
                return $date->getTimestamp();
            }
        }

        // Date with dashes (not ISO): check if first part > 12 to determine DD-MM or MM-DD
        if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})/', $date_string, $matches)) {
            $first = (int) $matches[1];

            // If first > 12, it must be DD-MM-YYYY
            if ($first > 12) {
                $format = $has_time ? 'd-m-Y H:i' : 'd-m-Y';
            } else {
                // Default to MM-DD-YYYY (US format with dashes)
                $format = $has_time ? 'm-d-Y H:i' : 'm-d-Y';
            }

            $date = \DateTime::createFromFormat('!' . $format, $date_string, $timezone);
            if ($date !== false) {
                return $date->getTimestamp();
            }
        }

        // Fallback to strtotime for relative dates like "+7 days" or text formats
        $timestamp = strtotime($date_string);
        return $timestamp;
    }
}
