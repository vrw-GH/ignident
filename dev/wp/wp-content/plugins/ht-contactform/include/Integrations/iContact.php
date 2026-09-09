<?php
/**
 * iContact Integration Class
 *
 * Handles all iContact API interactions for form submissions including
 * list management, contact creation, and subscriptions.
 *
 * @package HTContactForm
 * @subpackage Integrations
 */

namespace HTContactForm\Integrations;

use HTContactFormAdmin\Includes\Services\Helper;
use WP_Error;
use WP_REST_Response;

/**
 * iContact Integration Handler
 *
 * Provides functionality to integrate form submissions with iContact
 * email marketing using their REST API v2.2.
 */
class iContact {
    /**
     * iContact App ID
     *
     * @var string|null
     */
    private $app_id = null;

    /**
     * iContact API Username
     *
     * @var string|null
     */
    private $username = null;

    /**
     * iContact API Password
     *
     * @var string|null
     */
    private $password = null;

    /**
     * iContact Account ID
     *
     * @var string|null
     */
    private $account_id = null;

    /**
     * iContact Client Folder ID
     *
     * @var string|null
     */
    private $client_folder_id = null;

    /**
     * iContact API base URL
     *
     * @var string
     */
    private $api_url = 'https://app.icontact.com/icp/';

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
     * @param array $credentials iContact API credentials
     * @return self Instance of the iContact class
     */
    public static function get_instance($credentials = []) {
        $key = md5(wp_json_encode($credentials));
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new self($credentials);
        }
        return self::$instances[$key];
    }

    /**
     * Constructor
     *
     * @param array $credentials iContact API credentials
     */
    public function __construct($credentials = []) {
        $this->app_id = isset($credentials['app_id']) && is_string($credentials['app_id']) ? trim($credentials['app_id']) : '';
        $this->username = isset($credentials['username']) && is_string($credentials['username']) ? trim($credentials['username']) : '';
        $this->password = isset($credentials['password']) && is_string($credentials['password']) ? trim($credentials['password']) : '';
        $this->account_id = isset($credentials['account_id']) && is_string($credentials['account_id']) ? trim($credentials['account_id']) : '';
        $this->client_folder_id = isset($credentials['client_folder_id']) && is_string($credentials['client_folder_id']) ? trim($credentials['client_folder_id']) : '';

        if (class_exists(Helper::class)) {
            $this->helper = Helper::get_instance();
        }
    }

    /**
     * Check if credentials are configured
     *
     * @return bool Whether credentials are set
     */
    public function has_credentials() {
        return !empty($this->app_id) && !empty($this->username) && !empty($this->password);
    }

    /**
     * Check if account is configured
     *
     * @return bool Whether account info is set
     */
    public function has_account() {
        return !empty($this->account_id) && !empty($this->client_folder_id);
    }

    /**
     * Make an API request to iContact
     *
     * @param string $endpoint The API endpoint to call
     * @param string $method The HTTP method to use (GET, POST, PUT, DELETE)
     * @param array $data The data to send with the request
     * @return array|WP_Error Response data on success, WP_Error on failure
     */
    private function api_request($endpoint, $method = 'GET', $data = []) {
        if (!$this->has_credentials()) {
            return new WP_Error('credentials_missing', __('iContact API credentials are required', 'ht-contactform'));
        }

        $url = $this->api_url . ltrim($endpoint, '/');

        $args = [
            'method'    => strtoupper($method),
            'timeout'   => 30,
            'headers'   => [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'API-Version'   => '2.2',
                'API-AppId'     => $this->app_id,
                'API-Username'  => $this->username,
                'API-Password'  => $this->password,
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
                    __('iContact API rate limit exceeded. Please retry after %d seconds.', 'ht-contactform'),
                    (int) $retry_after
                ),
                ['status' => 429, 'retry_after' => (int) $retry_after]
            );
        }

        if ($response_code < 200 || $response_code >= 300) {
            $error_data = json_decode($response_body, true);
            $error_message = $error_data['errors'][0] ?? $error_data['message'] ?? $response_body;

            return new WP_Error(
                'api_error',
                sprintf(
                    /* translators: %1$d: HTTP status code, %2$s: Error message */
                    __('iContact API error [%1$d]: %2$s', 'ht-contactform'),
                    $response_code,
                    is_array($error_message) ? wp_json_encode($error_message) : $error_message
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
                __('Invalid JSON response from iContact API', 'ht-contactform'),
                ['response_body' => substr($response_body, 0, 200)]
            );
        }

        return $decoded_data;
    }

    /**
     * Sanitize ID for use in API URLs
     *
     * @param string $id Raw ID
     * @return string Sanitized ID
     */
    private function sanitize_id($id) {
        return preg_replace('/[^a-zA-Z0-9_-]/', '', sanitize_text_field($id));
    }

    /**
     * Get base path for account endpoints
     *
     * @return string Base path
     */
    private function get_account_path() {
        $account_id = $this->sanitize_id($this->account_id);
        $folder_id = $this->sanitize_id($this->client_folder_id);
        return "a/{$account_id}/c/{$folder_id}";
    }

    /**
     * Get all accounts
     *
     * @return array|WP_Error Array of accounts on success, WP_Error on failure
     */
    public function get_accounts() {
        $response = $this->api_request('a');

        if (is_wp_error($response)) {
            return $response;
        }

        return isset($response['accounts']) && is_array($response['accounts']) ? $response['accounts'] : [];
    }

    /**
     * Get client folders for an account
     *
     * @param string $account_id Account ID
     * @return array|WP_Error Array of folders on success, WP_Error on failure
     */
    public function get_client_folders($account_id) {
        if (empty($account_id)) {
            return new WP_Error('missing_account_id', __('Account ID is required', 'ht-contactform'));
        }

        $account_id = $this->sanitize_id($account_id);
        $response = $this->api_request("a/{$account_id}/c");

        if (is_wp_error($response)) {
            return $response;
        }

        return isset($response['clientfolders']) && is_array($response['clientfolders']) ? $response['clientfolders'] : [];
    }

    /**
     * Get lists for account
     *
     * @return array|WP_Error Array of lists on success, WP_Error on failure
     */
    public function get_lists() {
        if (!$this->has_account()) {
            return new WP_Error('missing_account', __('Account ID and Client Folder ID are required', 'ht-contactform'));
        }

        $response = $this->api_request($this->get_account_path() . '/lists');

        if (is_wp_error($response)) {
            return $response;
        }

        return isset($response['lists']) && is_array($response['lists']) ? $response['lists'] : [];
    }

    /**
     * Get custom fields
     *
     * @return array|WP_Error Array of custom fields on success, WP_Error on failure
     */
    public function get_custom_fields() {
        if (!$this->has_account()) {
            return new WP_Error('missing_account', __('Account ID and Client Folder ID are required', 'ht-contactform'));
        }

        $response = $this->api_request($this->get_account_path() . '/customfields');

        if (is_wp_error($response)) {
            return $response;
        }

        // Handle both lowercase and camelCase response keys
        if (isset($response['customfields']) && is_array($response['customfields'])) {
            return $response['customfields'];
        }
        if (isset($response['customFields']) && is_array($response['customFields'])) {
            return $response['customFields'];
        }

        return [];
    }

    /**
     * Subscribe to iContact list
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

        if (!$this->has_account()) {
            return new WP_Error('missing_account', __('iContact account is not configured', 'ht-contactform'));
        }

        // Process merge fields to get email and other fields
        $email = '';
        $contact_fields = [];

        // Standard iContact contact fields
        $icontact_standard_fields = [
            'prefix', 'firstName', 'lastName', 'suffix',
            'street', 'street2', 'city', 'state', 'postalCode', 'phone',
            'fax', 'business'
        ];

        if (!empty($integration->merge_fields) && is_array($integration->merge_fields)) {
            foreach ($integration->merge_fields as $key => $value) {
                if (empty($value)) {
                    continue;
                }

                $processed_value = $this->process_field_value($value, $form_data, $form);

                if ($key === 'email') {
                    $email = sanitize_email($processed_value);
                } elseif (in_array($key, $icontact_standard_fields)) {
                    $contact_fields[$key] = sanitize_text_field($processed_value);
                } else {
                    // Custom field - use the key directly (privateName from iContact)
                    $contact_fields[$key] = sanitize_text_field($processed_value);
                }
            }
        }

        if (empty($email) || !is_email($email)) {
            return new WP_Error('invalid_email', __('Valid email address is required', 'ht-contactform'));
        }

        // Prepare contact data
        $contact_data = array_merge(['email' => $email], $contact_fields);

        // Create contact
        $contact_response = $this->api_request(
            $this->get_account_path() . '/contacts',
            'POST',
            [$contact_data]
        );

        if (is_wp_error($contact_response)) {
            do_action('ht_form/icontact_integration_result', null, 'failed', $contact_response->get_error_message());
            return $contact_response;
        }

        // Get contact ID from response
        $contact_id = null;
        if (isset($contact_response['contacts'][0]['contactId'])) {
            $contact_id = $contact_response['contacts'][0]['contactId'];
        }

        if (empty($contact_id)) {
            return new WP_Error('contact_creation_failed', __('Failed to create contact in iContact', 'ht-contactform'));
        }

        // Subscribe contact to list
        $subscription_data = [
            [
                'contactId' => $contact_id,
                'listId'    => $this->sanitize_id($integration->list_id),
                'status'    => 'normal',
            ]
        ];

        $subscription_response = $this->api_request(
            $this->get_account_path() . '/subscriptions',
            'POST',
            $subscription_data
        );

        if (is_wp_error($subscription_response)) {
            do_action('ht_form/icontact_integration_result', null, 'failed', $subscription_response->get_error_message());
            return $subscription_response;
        }

        do_action('ht_form/icontact_integration_result', $subscription_response, 'success', __('iContact subscription successful', 'ht-contactform'));

        return new WP_REST_Response([
            'message' => __('iContact subscription successful', 'ht-contactform'),
            'contact_id' => $contact_id,
            'response' => $subscription_response,
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
