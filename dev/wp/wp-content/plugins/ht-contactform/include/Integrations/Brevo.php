<?php
namespace HTContactForm\Integrations;

use HTContactFormAdmin\Includes\Services\Helper;
use WP_Error;
use WP_REST_Response;

/**
 * Brevo Integration Class
 * 
 * Handles integration with Brevo email marketing service including API communication,
 * contact list management, and form submission processing.
 *
 * @since 2.4.0
 */
class Brevo {

    /** 
     * API Base URL
     * 
     * The base URL for all Brevo API requests.
     * 
     * @since 2.4.0
     * @var string
     */
    private const API_BASE_URL = 'https://api.brevo.com/v3/';

    /** 
     * API Key
     * 
     * Brevo API key used for authentication.
     * 
     * @since 2.4.0
     * @var string|null
     */
    private $api_key = null;
    
    /** 
     * Helper instance
     * 
     * Instance of the Helper class for utility functions.
     * 
     * @since 2.4.0
     * @var Helper
     */
    private $helper = null;

    /**
     * Constructor
     *
     * Initializes the Brevo integration with API credentials from WordPress options.
     *
     * @since 2.4.0
     */
    public function __construct() {
        $integrations = get_option('ht_form_integrations', []);
        $api_key = $integrations['brevo']['api_key'] ?? '';
        $this->api_key = $api_key;

        $this->helper = Helper::get_instance();
    }

    /**
     * Make an API request to Brevo
     *
     * Handles communication with the Brevo API using WordPress HTTP functions.
     *
     * @since 2.4.0
     * @param string $endpoint The API endpoint to call
     * @param string $method The HTTP method to use (GET, POST, etc.)
     * @param array $data The data to send with the request
     * @return array|WP_Error Response data on success, WP_Error on failure
     */
    private function api_request($endpoint, $method = 'GET', $data = []) {
        $url = self::API_BASE_URL . $endpoint;
        $args = [
            'method' => $method,
            'headers' => [
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'api-key' => $this->api_key,
            ],
        ];
        if (!empty($data) && $method !== 'GET') {
            $args['body'] = wp_json_encode($data);
        }
        $response = wp_remote_request($url, $args);
        if (is_wp_error($response)) {
            return $response;
        }
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code < 200 || $response_code >= 300) {
            return new WP_Error(
                'api_error',
                sprintf(
                    esc_html__('Error from Brevo API: %s', 'ht-contactform'),
                    wp_remote_retrieve_body($response)
                ),
                ['status' => $response_code]
            );
        }
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error(
                'json_error',
                esc_html__('Invalid JSON response from Brevo', 'ht-contactform')
            );
        }
        return $data;
    }

    /**
     * Verify Brevo API key
     *
     * Tests the provided API credentials by making a request to the Brevo API.
     *
     * @since 2.4.0
     * @param WP_REST_Request $request The REST request object containing API credentials
     * @return WP_REST_Response|WP_Error Success or error response
     */
    public function verify($request) {
        $api_key = $request->get_param('api_key');
        if (empty($api_key)) {
            return $this->error_response(
                'missing_api_key',
                'API Key is required for Brevo integration.',
                400
            );
        }
        $this->api_key = $api_key;
        $response = $this->api_request('account');
        if (is_wp_error($response)) {
            return $this->error_response(
                'api_error',
                'Error from Brevo API: ' . $response->get_error_message(),
                400
            );
        }
        return $this->success_response(
            'Brevo api key verified successfully.',
            $response,
            true
        );
    }

    /**
     * Get Brevo Contact Lists
     *
     * Retrieves all available contact lists from Brevo.
     *
     * @since 2.4.0
     * @return WP_REST_Response|WP_Error List of contact lists or error response
     */
    public function get_lists() {
        if (empty($this->api_key)) {
            return $this->error_response(
                'missing_api_key',
                'API Key is required for Brevo integration.',
                400
            );
        }
        $response = $this->api_request('contacts/lists');
        if (is_wp_error($response)) {
            return $response;
        }
        $lists = [];
        if (isset($response['lists']) && is_array($response['lists'])) {
            foreach ($response['lists'] as $list) {
                $lists[] = [
                    'value' => (string) $list['id'],
                    'label' => $list['name']
                ];
            }
        }
        return $this->success_response('Lists retrieved successfully', $lists);
    }

    /**
     * Subscribe to Brevo
     * 
     * Processes form submission data and adds contacts to Brevo lists.
     *
     * @since 2.4.0
     * @param object $integration Integration settings
     * @param array $form Form data
     * @param array $form_data Form submission data
     * @param array $meta Meta data
     * @return WP_Error|WP_REST_Response Success or error response
     */
    public function subscribe($integration, $form, $form_data, $meta) {
        
        if (empty($this->api_key)) {
            return $this->error_response(
                'missing_api_key',
                'API Key is required for Brevo integration.',
                400
            );
        }

        // Check if integration is enabled for this form
        if (empty($integration->enabled) || empty($integration->list_id)) {
            return new WP_Error('brevo_error', 'Integration is not enabled for this form');
        }

        $data = $this->prepare_data($integration, $form, $form_data, $meta);
        
        // Check for required email field
        if (empty($data['email'])) {
            error_log('Brevo Error: Email field is required');
            return new WP_Error('brevo_error', 'Email field is required', ['status' => 400]);
        }
        
        // Validate email format
        if (!is_email($data['email'])) {
            error_log('Brevo Error: Invalid email address');
            return new WP_Error('brevo_error', 'Invalid email address', ['status' => 400]);
        }
        try {
            $response = $this->api_request('contacts', 'POST', $data);
            
            // Handle API errors
            if (is_wp_error($response)) {
                error_log('Brevo Error: ' . $response->get_error_message());
                do_action('ht_form/brevo_integration_result', $response, 'failed', $response->get_error_message());
                return $response;
            }
            
            // Trigger success action and return response
            do_action('ht_form/brevo_integration_result', $response, 'success', 'Brevo subscription successful');
            return new WP_REST_Response([
                'success' => true,
                'message' => 'Brevo subscription successful',
                'response' => $response,
            ], 200);
        } catch (\Exception $e) {
            error_log('Brevo Exception: ' . $e->getMessage());
            do_action('ht_form/brevo_integration_result', null, 'failed', $e->getMessage());
            return new WP_Error('brevo_exception', $e->getMessage(), ['status' => 500]);
        }
    }


    /**
     * Prepare Data for Brevo API
     * 
     * Formats form submission data for the Brevo API based on integration settings.
     *
     * @since 2.4.0
     * @param object $integration Integration settings
     * @param array $form Form data
     * @param array $form_data Form submission data
     * @param array $meta Meta data
     * @return array Prepared data for Brevo API
     */
    public function prepare_data($integration, $form, $form_data, $meta) {
        $data = [
            "email" => "",
            "listIds" => [],
            'attributes' => [],
        ];

        // // Fields
        if(!empty($integration->list_id)) {
            $data['listIds'][] = (int) $integration->list_id;
        }
        if(!empty($integration->fields['email'])) {
            $data['email'] = $this->helper->filter_vars($integration->fields['email'], $form_data, $form);
        }
        if(!empty($integration->fields)) {
            foreach ($integration->fields as $key => $value) {
                if($key === 'email') {
                    continue;
                }
                $data['attributes'][$key] = $this->helper->filter_vars($value, $form_data, $form);
            }
        }

        return $data;
    }

    //-------------------------------------------------------------------------
    // HELPER METHODS
    //-------------------------------------------------------------------------
    
    /**
     * Create a Success Response
     *
     * Formats a standardized success response for REST API endpoints.
     *
     * @since 2.4.0
     * @param string $message Success message
     * @param array $data Response data
     * @param bool $success Success flag
     * @return WP_REST_Response Formatted REST response
     */
    private function success_response($message, $data = [], $success = true) {
        $response = [
            'success' => $success,
            'message' => esc_html__($message, 'ht-contactform'),
            'data' => $data
        ];
        return new WP_REST_Response($response, 200);
    }

    /**
     * Create an Error Response
     *
     * Formats a standardized error response for REST API endpoints.
     *
     * @since 2.4.0
     * @param string $code Error code
     * @param string $message Error message
     * @param int $status HTTP status code
     * @return WP_Error Formatted error response
     */
    private function error_response($code, $message, $status = 400) {
        return new WP_Error(
            $code,
            esc_html__($message, 'ht-contactform'),
            ['status' => $status]
        );
    }
}
