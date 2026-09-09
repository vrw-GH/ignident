<?php
/**
 * GetResponse Integration Class
 *
 * Handles all GetResponse API interactions for form submissions including
 * campaign management, contact creation, and custom field handling.
 *
 * @package HTContactForm
 * @subpackage Integrations
 */

namespace HTContactForm\Integrations;

use HTContactFormAdmin\Includes\Services\Helper;
use WP_Error;
use WP_REST_Response;

/**
 * GetResponse Integration Handler
 *
 * Provides functionality to integrate form submissions with GetResponse
 * campaigns using their REST API.
 */
class GetResponse {
    /**
     * GetResponse API key
     *
     * @var string|null
     */
    private $api_key = null;

    /**
     * GetResponse API base URL
     *
     * @var string
     */
    private $api_url = 'https://api.getresponse.com/v3/';

    /**
     * Helper class instance
     *
     * @var Helper|null
     */
    private $helper = null;

    /**
     * Singleton instance
     *
     * @var self|null
     */
    private static $instance = null;

    /**
     * Get singleton instance
     *
     * @param string|null $api_key GetResponse API key
     * @return self Instance of the GetResponse class
     */
    public static function get_instance($api_key = null) {
        // Always create new instance if API key is provided and different from current
        if (!isset(self::$instance) || ($api_key !== null && self::$instance->api_key !== $api_key)) {
            self::$instance = new self($api_key);
        }
        return self::$instance;
    }

    /**
     * Constructor
     *
     * @param string|null $api_key GetResponse API key
     */
    public function __construct($api_key = null) {
        $this->api_key = is_string($api_key) ? trim($api_key) : '';
        $this->helper = Helper::get_instance();
    }

    /**
     * Make an API request to GetResponse
     *
     * @param string $endpoint The API endpoint to call
     * @param string $method The HTTP method to use (GET, POST, PUT, DELETE)
     * @param array $data The data to send with the request
     * @return array|WP_Error Response data on success, WP_Error on failure
     */
    private function api_request($endpoint, $method = 'GET', $data = []) {
        if (empty($this->api_key)) {
            return new WP_Error('api_key_missing', __('GetResponse API key is required', 'ht-contactform'));
        }

        $url = $this->api_url . ltrim($endpoint, '/');

        $args = [
            'method'    => strtoupper($method),
            'timeout'   => 30,
            'headers'   => [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'X-Auth-Token'  => 'api-key ' . $this->api_key,
                'User-Agent'    => 'HT-ContactForm/' . HTCONTACTFORM_VERSION,
            ],
        ];

        if (!empty($data) && $method !== 'GET') {
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

        if ($response_code < 200 || $response_code >= 300) {
            $error_data = json_decode($response_body, true);
            $error_message = $error_data['message'] ?? $response_body;

            return new WP_Error(
                'api_error',
                sprintf(
                    /* translators: %1$d: HTTP status code, %2$s: Error message */
                    __('GetResponse API error [%1$d]: %2$s', 'ht-contactform'),
                    $response_code,
                    $error_message
                ),
                ['status' => $response_code, 'response' => $error_data]
            );
        }

        $decoded_data = json_decode($response_body, true);
        if (json_last_error() !== JSON_ERROR_NONE && !empty($response_body)) {
            return new WP_Error(
                'json_decode_error',
                __('Invalid JSON response from GetResponse API', 'ht-contactform'),
                ['response_body' => substr($response_body, 0, 200)]
            );
        }

        return $decoded_data;
    }

    /**
     * Get all campaigns (lists)
     *
     * @return array|WP_Error Array of campaigns on success, WP_Error on failure
     */
    public function get_campaigns() {
        $response = $this->api_request('campaigns');

        if (is_wp_error($response)) {
            return $response;
        }

        return is_array($response) ? $response : [];
    }

    /**
     * Get custom fields
     *
     * @return array|WP_Error Array of custom fields on success, WP_Error on failure
     */
    public function get_custom_fields() {
        $response = $this->api_request('custom-fields');

        if (is_wp_error($response)) {
            return $response;
        }

        return is_array($response) ? $response : [];
    }

    /**
     * Get tags
     *
     * @return array|WP_Error Array of tags on success, WP_Error on failure
     */
    public function get_tags() {
        $response = $this->api_request('tags');

        if (is_wp_error($response)) {
            return $response;
        }

        return is_array($response) ? $response : [];
    }

    /**
     * Subscribe to GetResponse
     *
     * @param object $integration Integration settings
     * @param array $form Form data
     * @param array $form_data Form submission data
     * @param array $meta Meta data
     * @return WP_Error|WP_REST_Response Response
     */
    public function subscribe($integration, $form, $form_data, $meta) {
        if (empty($integration->enabled) || empty($integration->campaign_id)) {
            return new WP_Error('integration_disabled', __('Integration is not enabled for this form', 'ht-contactform'));
        }

        // Process merge fields to get email and custom fields
        $email = '';
        $name = '';
        $custom_fields = [];

        if (!empty($integration->merge_fields) && is_array($integration->merge_fields)) {
            foreach ($integration->merge_fields as $key => $value) {
                if (empty($value)) {
                    continue;
                }

                $processed_value = $this->process_field_value($value, $form_data, $form);

                if ($key === 'email') {
                    $email = sanitize_email($processed_value);
                } elseif ($key === 'name') {
                    $name = sanitize_text_field($processed_value);
                } else {
                    // Custom field - apply appropriate sanitization
                    $sanitized_value = $processed_value;

                    // Check if it's a country value - remove localized text in parentheses
                    if ($this->is_country_value($processed_value)) {
                        $sanitized_value = $this->sanitize_country($processed_value);
                    }

                    $custom_fields[] = [
                        'customFieldId' => $key,
                        'value' => [sanitize_text_field($sanitized_value)]
                    ];
                }
            }
        }

        if (empty($email) || !is_email($email)) {
            return new WP_Error('invalid_email', __('Valid email address is required', 'ht-contactform'));
        }

        // Prepare contact data
        $contact_data = [
            'email' => $email,
            'campaign' => [
                'campaignId' => $integration->campaign_id
            ],
        ];

        if (!empty($name)) {
            $contact_data['name'] = $name;
        }

        if (!empty($custom_fields)) {
            $contact_data['customFieldValues'] = $custom_fields;
        }

        // Add day of cycle if specified (valid range: 0-1000)
        if (isset($integration->day_of_cycle) && $integration->day_of_cycle !== '') {
            $day_of_cycle = intval($integration->day_of_cycle);
            if ($day_of_cycle >= 0 && $day_of_cycle <= 1000) {
                $contact_data['dayOfCycle'] = $day_of_cycle;
            }
        }

        // Add tags if specified
        if (!empty($integration->tags) && is_array($integration->tags)) {
            $contact_data['tags'] = array_map(function($tag_id) {
                return ['tagId' => sanitize_text_field($tag_id)];
            }, $integration->tags);
        }

        // Add contact
        $response = $this->api_request('contacts', 'POST', $contact_data);

        if (is_wp_error($response)) {
            do_action('ht_form/getresponse_integration_result', null, 'failed', $response->get_error_message());
            return $response;
        }

        do_action('ht_form/getresponse_integration_result', $response, 'success', __('GetResponse subscription successful', 'ht-contactform'));

        return new WP_REST_Response([
            'message' => __('GetResponse subscription successful', 'ht-contactform'),
            'response' => $response,
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
     * Check if value looks like a country name with localized text
     *
     * @param string $value The value to check
     * @return bool True if value appears to be a country with localized text
     */
    private function is_country_value($value) {
        // Check if value contains parenthesis with non-ASCII characters (localized country names)
        return (bool) preg_match('/\s*\([^\)]*[^\x00-\x7F]+[^\)]*\)/', $value);
    }

    /**
     * Sanitize country value - extract English name only
     *
     * @param string $value The country display name (e.g., "Afghanistan (‫افغانستان‬‎)")
     * @return string The English country name (e.g., "Afghanistan")
     */
    private function sanitize_country($value) {
        // Extract English name before parenthesis (removes localized text)
        return trim(preg_replace('/\s*\(.*\)$/', '', $value));
    }
}
