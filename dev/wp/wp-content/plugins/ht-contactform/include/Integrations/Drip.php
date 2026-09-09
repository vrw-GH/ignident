<?php
/**
 * Drip Integration Class
 *
 * Handles all Drip API interactions for form submissions including
 * account management, subscriber creation, and tag handling.
 *
 * @package HTContactForm
 * @subpackage Integrations
 */

namespace HTContactForm\Integrations;

use HTContactFormAdmin\Includes\Services\Helper;
use WP_Error;
use WP_REST_Response;

/**
 * Drip Integration Handler
 *
 * Provides functionality to integrate form submissions with Drip
 * campaigns using their REST API.
 */
class Drip {
    /**
     * Drip API key
     *
     * @var string|null
     */
    private $api_key = null;

    /**
     * Drip API base URL
     *
     * @var string
     */
    private $api_url = 'https://api.getdrip.com/v2/';

    /**
     * Helper class instance
     *
     * @var Helper|null
     */
    private $helper = null;

    /**
     * Instances storage by API key
     *
     * @var array
     */
    private static $instances = [];

    /**
     * Get instance by API key
     *
     * @param string|null $api_key Drip API key
     * @return self Instance of the Drip class
     */
    public static function get_instance($api_key = null) {
        $key = md5($api_key ?? '');
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new self($api_key);
        }
        return self::$instances[$key];
    }

    /**
     * Constructor
     *
     * @param string|null $api_key Drip API key
     */
    public function __construct($api_key = null) {
        $this->api_key = is_string($api_key) ? trim($api_key) : '';
        if (class_exists(Helper::class)) {
            $this->helper = Helper::get_instance();
        }
    }

    /**
     * Make an API request to Drip
     *
     * @param string $endpoint The API endpoint to call
     * @param string $method The HTTP method to use (GET, POST, PUT, DELETE)
     * @param array $data The data to send with the request
     * @return array|WP_Error Response data on success, WP_Error on failure
     */
    private function api_request($endpoint, $method = 'GET', $data = []) {
        if (empty($this->api_key)) {
            return new WP_Error('api_key_missing', __('Drip API key is required', 'ht-contactform'));
        }

        $url = $this->api_url . ltrim($endpoint, '/');

        $args = [
            'method'    => strtoupper($method),
            'timeout'   => 30,
            'headers'   => [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->api_key . ':'),
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

        // Handle rate limiting
        if ($response_code === 429) {
            $retry_after = wp_remote_retrieve_header($response, 'retry-after') ?: 60;
            return new WP_Error(
                'rate_limited',
                sprintf(
                    /* translators: %d: Number of seconds to wait */
                    __('Drip API rate limit exceeded. Please retry after %d seconds.', 'ht-contactform'),
                    (int) $retry_after
                ),
                ['status' => 429, 'retry_after' => (int) $retry_after]
            );
        }

        if ($response_code < 200 || $response_code >= 300) {
            $error_data = json_decode($response_body, true);
            $error_message = $error_data['errors'][0]['message'] ?? $error_data['message'] ?? $response_body;

            return new WP_Error(
                'api_error',
                sprintf(
                    /* translators: %1$d: HTTP status code, %2$s: Error message */
                    __('Drip API error [%1$d]: %2$s', 'ht-contactform'),
                    $response_code,
                    $error_message
                ),
                ['status' => $response_code, 'response' => $error_data]
            );
        }

        // Handle 204 No Content response
        if ($response_code === 204 || empty($response_body)) {
            return [];
        }

        $decoded_data = json_decode($response_body, true);
        if (json_last_error() !== JSON_ERROR_NONE && !empty($response_body)) {
            return new WP_Error(
                'json_decode_error',
                __('Invalid JSON response from Drip API', 'ht-contactform'),
                ['response_body' => substr($response_body, 0, 200)]
            );
        }

        return $decoded_data;
    }

    /**
     * Sanitize account ID for use in API URLs
     *
     * @param string $account_id Raw account ID
     * @return string Sanitized account ID
     */
    private function sanitize_account_id($account_id) {
        return preg_replace('/[^a-zA-Z0-9_-]/', '', sanitize_text_field($account_id));
    }

    /**
     * Get all accounts
     *
     * @return array|WP_Error Array of accounts on success, WP_Error on failure
     */
    public function get_accounts() {
        $response = $this->api_request('accounts');

        if (is_wp_error($response)) {
            return $response;
        }

        return isset($response['accounts']) && is_array($response['accounts']) ? $response['accounts'] : [];
    }

    /**
     * Get tags for an account
     *
     * @param string $account_id Drip account ID
     * @return array|WP_Error Array of tags on success, WP_Error on failure
     */
    public function get_tags($account_id) {
        if (empty($account_id)) {
            return new WP_Error('missing_account_id', __('Account ID is required', 'ht-contactform'));
        }

        $account_id = $this->sanitize_account_id($account_id);
        $response = $this->api_request($account_id . '/tags');

        if (is_wp_error($response)) {
            return $response;
        }

        return isset($response['tags']) && is_array($response['tags']) ? $response['tags'] : [];
    }

    /**
     * Get custom fields for an account
     *
     * @param string $account_id Drip account ID
     * @return array|WP_Error Array of custom fields on success, WP_Error on failure
     */
    public function get_custom_fields($account_id) {
        if (empty($account_id)) {
            return new WP_Error('missing_account_id', __('Account ID is required', 'ht-contactform'));
        }

        $account_id = $this->sanitize_account_id($account_id);
        $response = $this->api_request($account_id . '/custom_field_identifiers');

        if (is_wp_error($response)) {
            return $response;
        }

        return isset($response['custom_field_identifiers']) && is_array($response['custom_field_identifiers'])
            ? $response['custom_field_identifiers']
            : [];
    }

    /**
     * Subscribe to Drip
     *
     * @param object $integration Integration settings
     * @param array $form Form data
     * @param array $form_data Form submission data
     * @param array $meta Meta data
     * @return WP_Error|WP_REST_Response Response
     */
    public function subscribe($integration, $form, $form_data, $meta) {
        // Validate integration object
        if (!is_object($integration)) {
            return new WP_Error('invalid_integration', __('Invalid integration configuration', 'ht-contactform'));
        }

        if (empty($integration->enabled) || empty($integration->account_id)) {
            return new WP_Error('integration_disabled', __('Integration is not enabled for this form', 'ht-contactform'));
        }

        // Process merge fields to get email, standard fields, and custom fields
        $email = '';
        $standard_fields = [];
        $custom_fields = [];

        // Standard Drip subscriber fields (time_zone is auto-filled)
        $drip_standard_fields = [
            'first_name', 'last_name', 'address1', 'address2',
            'city', 'state', 'zip', 'country', 'phone'
        ];

        if (!empty($integration->merge_fields) && is_array($integration->merge_fields)) {
            foreach ($integration->merge_fields as $key => $value) {
                if (empty($value)) {
                    continue;
                }

                $processed_value = $this->process_field_value($value, $form_data, $form);

                if ($key === 'email') {
                    $email = sanitize_email($processed_value);
                } elseif (in_array($key, $drip_standard_fields)) {
                    $standard_fields[$key] = sanitize_text_field($processed_value);
                } else {
                    // Custom field
                    $custom_fields[$key] = sanitize_text_field($processed_value);
                }
            }
        }

        if (empty($email) || !is_email($email)) {
            return new WP_Error('invalid_email', __('Valid email address is required', 'ht-contactform'));
        }

        // Prepare subscriber data
        $subscriber_data = [
            'subscribers' => [
                [
                    'email' => $email,
                ]
            ]
        ];

        // Add standard fields if provided
        foreach ($standard_fields as $field_key => $field_value) {
            if (!empty($field_value)) {
                $subscriber_data['subscribers'][0][$field_key] = $field_value;
            }
        }

        // Auto-fill time zone from WordPress settings
        $timezone_string = wp_timezone_string();
        if (!empty($timezone_string)) {
            $subscriber_data['subscribers'][0]['time_zone'] = $timezone_string;
        }

        // Add custom fields if provided
        if (!empty($custom_fields)) {
            $subscriber_data['subscribers'][0]['custom_fields'] = $custom_fields;
        }

        // Add tags if specified (supports comma-separated string with smart tags)
        if (!empty($integration->tags) && (is_string($integration->tags) || is_array($integration->tags))) {
            $processed_tags = $this->process_tags($integration->tags, $form_data, $form);
            if (!empty($processed_tags)) {
                $subscriber_data['subscribers'][0]['tags'] = $processed_tags;
            }
        }

        // Use general subscriber endpoint with sanitized account ID
        $account_id = $this->sanitize_account_id($integration->account_id);
        $endpoint = $account_id . '/subscribers';

        // Add subscriber
        $response = $this->api_request($endpoint, 'POST', $subscriber_data);

        if (is_wp_error($response)) {
            do_action('ht_form/drip_integration_result', null, 'failed', $response->get_error_message());
            return $response;
        }

        do_action('ht_form/drip_integration_result', $response, 'success', __('Drip subscription successful', 'ht-contactform'));

        return new WP_REST_Response([
            'message' => __('Drip subscription successful', 'ht-contactform'),
            'response' => $response,
        ], 200);
    }

    /**
     * Process tags string with smart tag support
     *
     * Handles comma-separated tags with smart tag replacement.
     * Example: "newsletter, vip, {input.interest}" where interest = ["tech", "sports"]
     * Result: ["newsletter", "vip", "tech", "sports"]
     *
     * @param string|array $tags Tags input (string or array for backward compat)
     * @param array $form_data Form submission data
     * @param array $form Form configuration
     * @return array Processed tags array
     */
    private function process_tags($tags, $form_data, $form) {
        // Backward compatibility: if already array, sanitize and return
        if (is_array($tags)) {
            return array_map('sanitize_text_field', array_filter($tags));
        }

        if (empty($tags) || !is_string($tags)) {
            return [];
        }

        // Process smart tags in the string
        $processed = $this->process_field_value($tags, $form_data, $form);

        // Split by comma, trim whitespace, filter empty
        $tag_array = array_filter(
            array_map('trim', explode(',', $processed))
        );

        // Sanitize each tag and re-index array
        return array_values(array_map('sanitize_text_field', $tag_array));
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
}
