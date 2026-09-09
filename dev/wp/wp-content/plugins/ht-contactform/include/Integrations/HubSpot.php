<?php
/**
 * HubSpot Integration Class
 *
 * Handles all HubSpot API interactions for form submissions including
 * contact creation, list management, and property handling.
 *
 * @package HTContactForm
 * @subpackage Integrations
 */

namespace HTContactForm\Integrations;

use HTContactFormAdmin\Includes\Services\Helper;
use WP_Error;
use WP_REST_Response;

/**
 * HubSpot Integration Handler
 *
 * Provides functionality to integrate form submissions with HubSpot
 * CRM using their REST API v3.
 */
class HubSpot {
    /**
     * HubSpot Private App Access Token
     *
     * @var string|null
     */
    private $access_token = null;

    /**
     * HubSpot API base URL
     *
     * @var string
     */
    private $api_url = 'https://api.hubapi.com/';

    /**
     * Helper class instance
     *
     * @var Helper|null
     */
    private $helper = null;

    /**
     * Instances storage by access token
     *
     * @var array
     */
    private static $instances = [];

    /**
     * Get instance by access token
     *
     * @param string|null $access_token HubSpot Private App Access Token
     * @return self Instance of the HubSpot class
     */
    public static function get_instance($access_token = null) {
        $key = md5($access_token ?? '');
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new self($access_token);
        }
        return self::$instances[$key];
    }

    /**
     * Constructor
     *
     * @param string|null $access_token HubSpot Private App Access Token
     */
    public function __construct($access_token = null) {
        $this->access_token = is_string($access_token) ? trim($access_token) : '';
        if (class_exists(Helper::class)) {
            $this->helper = Helper::get_instance();
        }
    }

    /**
     * Make an API request to HubSpot
     *
     * @param string $endpoint The API endpoint to call
     * @param string $method The HTTP method to use (GET, POST, PUT, PATCH, DELETE)
     * @param array $data The data to send with the request
     * @return array|WP_Error Response data on success, WP_Error on failure
     */
    private function api_request($endpoint, $method = 'GET', $data = []) {
        if (empty($this->access_token)) {
            return new WP_Error('access_token_missing', __('HubSpot Access Token is required', 'ht-contactform'));
        }

        $url = $this->api_url . ltrim($endpoint, '/');

        $args = [
            'method'    => strtoupper($method),
            'timeout'   => 30,
            'headers'   => [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $this->access_token,
                'User-Agent'    => 'HT-ContactForm/' . HTCONTACTFORM_VERSION,
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

        // Handle rate limiting (100 requests per 10 seconds)
        if ($response_code === 429) {
            $retry_after = wp_remote_retrieve_header($response, 'retry-after') ?: 10;
            return new WP_Error(
                'rate_limited',
                sprintf(
                    /* translators: %d: Number of seconds to wait */
                    __('HubSpot API rate limit exceeded. Please retry after %d seconds.', 'ht-contactform'),
                    (int) $retry_after
                ),
                ['status' => 429, 'retry_after' => (int) $retry_after]
            );
        }

        if ($response_code < 200 || $response_code >= 300) {
            $error_data = json_decode($response_body, true);
            $error_message = $error_data['message'] ?? $response_body;

            return new WP_Error(
                'api_error',
                sprintf(
                    /* translators: %1$d: HTTP status code, %2$s: Error message */
                    __('HubSpot API error [%1$d]: %2$s', 'ht-contactform'),
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
                __('Invalid JSON response from HubSpot API', 'ht-contactform'),
                ['response_body' => substr($response_body, 0, 200)]
            );
        }

        return $decoded_data;
    }

    /**
     * Verify API credentials
     *
     * @return array|WP_Error Account info on success, WP_Error on failure
     */
    public function verify() {
        // Test credentials by fetching account info
        $response = $this->api_request('crm/v3/objects/contacts?limit=1');

        if (is_wp_error($response)) {
            return $response;
        }

        return [
            'success' => true,
            'message' => __('HubSpot credentials verified successfully', 'ht-contactform'),
        ];
    }

    /**
     * Get contact lists (static lists only)
     *
     * @return array|WP_Error Array of lists on success, WP_Error on failure
     */
    public function get_lists() {
        $response = $this->api_request('crm/v3/lists/search', 'POST', [
            'processingTypes' => ['MANUAL', 'SNAPSHOT'],
            'objectTypeId' => '0-1', // Contacts
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        if (!isset($response['lists']) || !is_array($response['lists'])) {
            return [];
        }

        return array_map(function($list) {
            return [
                'id' => $list['listId'] ?? $list['id'] ?? '',
                'name' => $list['name'] ?? '',
                'type' => $list['processingType'] ?? '',
            ];
        }, $response['lists']);
    }

    /**
     * Get contact properties
     *
     * @return array|WP_Error Array of properties on success, WP_Error on failure
     */
    public function get_properties() {
        $response = $this->api_request('crm/v3/properties/contacts');

        if (is_wp_error($response)) {
            return $response;
        }

        if (!isset($response['results']) || !is_array($response['results'])) {
            return [];
        }

        // Filter to editable properties and format
        $properties = [];
        foreach ($response['results'] as $prop) {
            // Skip read-only and calculated properties
            if (!empty($prop['calculated']) || !empty($prop['readOnlyValue'])) {
                continue;
            }

            $properties[] = [
                'name' => $prop['name'] ?? '',
                'label' => $prop['label'] ?? $prop['name'] ?? '',
                'type' => $prop['type'] ?? 'string',
                'fieldType' => $prop['fieldType'] ?? 'text',
                'groupName' => $prop['groupName'] ?? '',
            ];
        }

        return $properties;
    }

    /**
     * Create or update a contact
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

        if (empty($integration->enabled)) {
            return new WP_Error('integration_disabled', __('Integration is not enabled for this form', 'ht-contactform'));
        }

        // Process merge fields to get properties
        $properties = [];
        $email = '';

        if (!empty($integration->merge_fields) && is_array($integration->merge_fields)) {
            foreach ($integration->merge_fields as $key => $value) {
                if (empty($value)) {
                    continue;
                }

                $processed_value = $this->process_field_value($value, $form_data, $form);

                if ($key === 'email') {
                    $email = sanitize_email($processed_value);
                    $properties['email'] = $email;
                } else {
                    $properties[$key] = sanitize_text_field($processed_value);
                }
            }
        }

        if (empty($email) || !is_email($email)) {
            return new WP_Error('invalid_email', __('Valid email address is required', 'ht-contactform'));
        }

        // Create or update contact
        $contact_data = [
            'properties' => $properties,
        ];

        // First try to find existing contact by email
        $existing_contact = $this->find_contact_by_email($email);

        if (is_wp_error($existing_contact)) {
            // Contact doesn't exist, create new
            $response = $this->api_request('crm/v3/objects/contacts', 'POST', $contact_data);
        } else {
            // Contact exists, update
            $contact_id = $existing_contact['id'];
            $response = $this->api_request('crm/v3/objects/contacts/' . $contact_id, 'PATCH', $contact_data);
        }

        if (is_wp_error($response)) {
            do_action('ht_form/hubspot_integration_result', null, 'failed', $response->get_error_message());
            return $response;
        }

        $contact_id = $response['id'] ?? null;

        // Add to list if specified
        if (!empty($integration->list_id) && !empty($contact_id)) {
            $this->add_contact_to_list($contact_id, $integration->list_id);
        }

        do_action('ht_form/hubspot_integration_result', $response, 'success', __('HubSpot contact created/updated successfully', 'ht-contactform'));

        return new WP_REST_Response([
            'message' => __('HubSpot contact created/updated successfully', 'ht-contactform'),
            'contact_id' => $contact_id,
        ], 200);
    }

    /**
     * Find contact by email
     *
     * @param string $email Email address
     * @return array|WP_Error Contact data or error if not found
     */
    private function find_contact_by_email($email) {
        $response = $this->api_request('crm/v3/objects/contacts/search', 'POST', [
            'filterGroups' => [
                [
                    'filters' => [
                        [
                            'propertyName' => 'email',
                            'operator' => 'EQ',
                            'value' => $email,
                        ]
                    ]
                ]
            ],
            'limit' => 1,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        if (empty($response['results']) || !is_array($response['results']) || count($response['results']) === 0) {
            return new WP_Error('contact_not_found', __('Contact not found', 'ht-contactform'));
        }

        return $response['results'][0];
    }

    /**
     * Add contact to a list
     *
     * @param string $contact_id Contact ID
     * @param string $list_id List ID
     * @return array|WP_Error Response
     */
    private function add_contact_to_list($contact_id, $list_id) {
        $list_id = sanitize_text_field($list_id);

        return $this->api_request('crm/v3/lists/' . $list_id . '/memberships/add', 'PUT', [
            (int) $contact_id,
        ]);
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
