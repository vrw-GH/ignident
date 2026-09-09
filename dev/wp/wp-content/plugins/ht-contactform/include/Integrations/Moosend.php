<?php
/**
 * Moosend Integration Class
 *
 * Handles all Moosend API interactions for form submissions including
 * mailing list management, subscriber creation, and custom field handling.
 *
 * @package HTContactForm
 * @subpackage Integrations
 */

namespace HTContactForm\Integrations;

use HTContactFormAdmin\Includes\Services\Helper;
use WP_Error;
use WP_REST_Response;

/**
 * Moosend Integration Handler
 *
 * Provides functionality to integrate form submissions with Moosend
 * mailing lists using their REST API.
 */
class Moosend {
    /**
     * Moosend API key
     *
     * @var string|null
     */
    private $api_key = null;

    /**
     * Moosend API base URL
     *
     * @var string
     */
    private $api_url = 'https://api.moosend.com/v3/';

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
     * @param string|null $api_key Moosend API key
     * @return self Instance of the Moosend class
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
     * @param string|null $api_key Moosend API key
     */
    public function __construct($api_key = null) {
        $this->api_key = is_string($api_key) ? trim($api_key) : '';
        if (class_exists(Helper::class)) {
            $this->helper = Helper::get_instance();
        }
    }

    /**
     * Make an API request to Moosend
     *
     * @param string $endpoint The API endpoint to call
     * @param string $method The HTTP method to use (GET, POST, PUT, DELETE)
     * @param array $data The data to send with the request
     * @return array|WP_Error Response data on success, WP_Error on failure
     */
    private function api_request($endpoint, $method = 'GET', $data = []) {
        if (empty($this->api_key)) {
            return new WP_Error('api_key_missing', __('Moosend API key is required', 'ht-contactform'));
        }

        // Moosend uses .json suffix and apikey as query parameter
        $url = $this->api_url . ltrim($endpoint, '/');

        // Add .json suffix if not present
        if (strpos($url, '.json') === false) {
            $url .= '.json';
        }

        // Add API key as query parameter
        // Note: Moosend API requires the API key as a query parameter - this is a Moosend API limitation
        // These credentials are only used server-side and are not exposed to the browser
        $url = add_query_arg('apikey', $this->api_key, $url);

        $args = [
            'method'    => strtoupper($method),
            'timeout'   => 30,
            'headers'   => [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
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
            $retry_after = wp_remote_retrieve_header($response, 'retry-after') ?: 10;
            return new WP_Error(
                'rate_limited',
                sprintf(
                    /* translators: %d: Number of seconds to wait */
                    __('Moosend API rate limit exceeded. Please retry after %d seconds.', 'ht-contactform'),
                    (int) $retry_after
                ),
                ['status' => 429, 'retry_after' => (int) $retry_after]
            );
        }

        if ($response_code < 200 || $response_code >= 300) {
            $error_data = json_decode($response_body, true);
            $error_message = $error_data['Error'] ?? $error_data['message'] ?? $response_body;

            return new WP_Error(
                'api_error',
                sprintf(
                    /* translators: %1$d: HTTP status code, %2$s: Error message */
                    __('Moosend API error [%1$d]: %2$s', 'ht-contactform'),
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
                __('Invalid JSON response from Moosend API', 'ht-contactform'),
                ['response_body' => substr($response_body, 0, 200)]
            );
        }

        return $decoded_data;
    }

    /**
     * Sanitize list ID for use in API URLs
     *
     * @param string $list_id Raw list ID
     * @return string Sanitized list ID
     */
    private function sanitize_list_id($list_id) {
        return preg_replace('/[^a-zA-Z0-9_-]/', '', sanitize_text_field($list_id));
    }

    /**
     * Get all mailing lists
     *
     * @return array|WP_Error Array of mailing lists on success, WP_Error on failure
     */
    public function get_lists() {
        $response = $this->api_request('lists');

        if (is_wp_error($response)) {
            return $response;
        }

        // Moosend returns { Context: { MailingLists: [...] } }
        if (isset($response['Context']['MailingLists']) && is_array($response['Context']['MailingLists'])) {
            return $response['Context']['MailingLists'];
        }

        return [];
    }

    /**
     * Get mailing list details including custom fields
     *
     * @param string $list_id Moosend mailing list ID
     * @return array|WP_Error List details on success, WP_Error on failure
     */
    public function get_list_details($list_id) {
        if (empty($list_id)) {
            return new WP_Error('missing_list_id', __('Mailing list ID is required', 'ht-contactform'));
        }

        $list_id = $this->sanitize_list_id($list_id);
        $response = $this->api_request('lists/' . $list_id . '/details');

        if (is_wp_error($response)) {
            return $response;
        }

        // Moosend returns { Context: {...} }
        if (isset($response['Context']) && is_array($response['Context'])) {
            return $response['Context'];
        }

        return [];
    }

    /**
     * Get custom fields for a mailing list
     *
     * @param string $list_id Moosend mailing list ID
     * @return array|WP_Error Array of custom fields on success, WP_Error on failure
     */
    public function get_custom_fields($list_id) {
        $details = $this->get_list_details($list_id);

        if (is_wp_error($details)) {
            return $details;
        }

        // Custom fields are in the CustomFieldsDefinition array
        if (isset($details['CustomFieldsDefinition']) && is_array($details['CustomFieldsDefinition'])) {
            return $details['CustomFieldsDefinition'];
        }

        return [];
    }

    /**
     * Subscribe to Moosend
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

        if (empty($integration->enabled) || empty($integration->list_id)) {
            return new WP_Error('integration_disabled', __('Integration is not enabled for this form', 'ht-contactform'));
        }

        // Process merge fields to get email, name, and custom fields
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
                    // Custom field
                    $custom_fields[] = sanitize_text_field($key) . '=' . sanitize_text_field($processed_value);
                }
            }
        }

        if (empty($email) || !is_email($email)) {
            return new WP_Error('invalid_email', __('Valid email address is required', 'ht-contactform'));
        }

        // Prepare subscriber data
        $subscriber_data = [
            'Email' => $email,
        ];

        if (!empty($name)) {
            $subscriber_data['Name'] = $name;
        }

        // Add custom fields if provided (Moosend expects CustomFields as array of "key=value" strings)
        if (!empty($custom_fields)) {
            $subscriber_data['CustomFields'] = $custom_fields;
        }

        // Handle double opt-in setting
        // HasExternalDoubleOptIn: true = we handle double opt-in externally (skip Moosend's), false = let Moosend handle it
        if (!empty($integration->double_opt_in)) {
            // Double opt-in enabled - let Moosend send confirmation email
            $subscriber_data['HasExternalDoubleOptIn'] = false;
        } else {
            // Double opt-in disabled - skip Moosend's confirmation process
            $subscriber_data['HasExternalDoubleOptIn'] = true;
        }

        // Subscribe endpoint
        $list_id = $this->sanitize_list_id($integration->list_id);
        $endpoint = 'subscribers/' . $list_id . '/subscribe';

        // Add subscriber
        $response = $this->api_request($endpoint, 'POST', $subscriber_data);

        if (is_wp_error($response)) {
            do_action('ht_form/moosend_integration_result', null, 'failed', $response->get_error_message());
            return $response;
        }

        do_action('ht_form/moosend_integration_result', $response, 'success', __('Moosend subscription successful', 'ht-contactform'));

        return new WP_REST_Response([
            'message' => __('Moosend subscription successful', 'ht-contactform'),
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
}
