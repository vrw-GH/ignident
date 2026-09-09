<?php

namespace HTContactFormAdmin\Includes\Api\Endpoints\Integrations;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * ActiveCampaign API Handler Class
 * 
 * Handles all REST API endpoints for ActiveCampaign integration including tags, lists, fields, and forms.
 * Provides methods for verifying API credentials and syncing contacts.
 */
class ActiveCampaign {
    //-------------------------------------------------------------------------
    // PROPERTIES
    //-------------------------------------------------------------------------
    
    /** @var string REST API namespace */
    private $namespace = 'ht-form/v1';

    /** @var self|null Singleton instance */
    private static $instance = null;
    
    /** @var string API Key */
    private $api_key;
    
    /** @var string API URL */
    private $api_url;

    //-------------------------------------------------------------------------
    // INITIALIZATION
    //-------------------------------------------------------------------------
    
    /**
     * Get singleton instance
     * 
     * @return self
     */
    public static function get_instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     * 
     * Initializes the class and sets up WordPress hooks
     */
    public function __construct() {
        $integrations = get_option('ht_form_integrations', []);
        $this->api_key = $integrations['activecampaign']['api_key'] ?? '';
        $this->api_url = $integrations['activecampaign']['api_url'] ?? '';
        
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes
     * 
     * Sets up all the REST API endpoints for ActiveCampaign integration
     */
    public function register_routes() {
        $routes = [
            // Get ActiveCampaign tags
            [
                'endpoint' => 'activecampaign/tags',
                'methods'  => 'GET',
                'callback' => 'get_tags',
            ],
            // Get ActiveCampaign fields
            [
                'endpoint' => 'activecampaign/fields',
                'methods'  => 'GET',
                'callback' => 'get_fields',
            ],
            // Get ActiveCampaign lists
            [
                'endpoint' => 'activecampaign/lists',
                'methods'  => 'GET',
                'callback' => 'get_lists',
            ],
            // Get ActiveCampaign forms
            [
                'endpoint' => 'activecampaign/forms',
                'methods'  => 'GET',
                'callback' => 'get_forms',
            ],
            // Verify ActiveCampaign credentials
            [
                'endpoint' => 'activecampaign/verify',
                'methods'  => 'POST',
                'callback' => 'verify_endpoint',
                'args'     => [
                    'api_key' => [
                        'required' => true,
                        'type'     => 'string',
                    ],
                    'api_url' => [
                        'required' => true,
                        'type'     => 'string',
                    ],
                ],
            ],
        ];

        foreach ($routes as $route) {
            register_rest_route($this->namespace, $route['endpoint'], [
                'methods'             => $route['methods'],
                'callback'            => [$this, $route['callback']],
                'permission_callback' => [$this, 'check_permission'],
                'args'                => $route['args'] ?? []
            ]);
        }
    }

    //-------------------------------------------------------------------------
    // VALIDATION & PERMISSIONS
    //-------------------------------------------------------------------------
    
    /**
     * Check if current user has permission to access endpoints
     * 
     * @return bool Whether user has manage_options capability
     */
    public function check_permission() {
        return current_user_can('manage_options');
    }

    //-------------------------------------------------------------------------
    // MAIN OPERATIONS
    //-------------------------------------------------------------------------

    /**
     * ActiveCampaign API Client
     * 
     * Makes requests to the ActiveCampaign API with proper error handling
     * 
     * @param string $endpoint API endpoint to call (without the base URL)
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param array $data Request data for POST/PUT requests
     * @param string $api_key Optional API key (uses instance API key if not provided)
     * @param string $api_url Optional API URL (uses instance API URL if not provided)
     * @return WP_Error|WP_REST_Response Response data or error
     */
    public function client($endpoint, $method = 'GET', $data = [], $api_key = null, $api_url = null) {
        // Use instance API key/URL if not provided
        $api_key = $api_key ?? $this->api_key;
        $api_url = $api_url ?? $this->api_url;
        
        // Validate API credentials
        if (empty($api_key) || empty($api_url)) {
            return new WP_Error(
                'missing_api_key_url',
                esc_html__('API key & API URL are required for ActiveCampaign integration.', 'ht-contactform'),
                ['status' => 400]
            );
        }
        
        // Validate URL format
        if (!filter_var($api_url, FILTER_VALIDATE_URL)) {
            return new WP_Error(
                'invalid_url',
                __('Invalid API URL format.', 'ht-contactform'),
                ['status' => 400]
            );
        }
        
        // Ensure API URL ends with /api/3/
        if (substr($api_url, -1) !== '/') {
            $api_url .= '/';
        }
        
        if (strpos($api_url, '/api/3/') === false) {
            $api_url .= 'api/3/';
        } else if (substr($api_url, -7) !== '/api/3/') {
            $api_url = preg_replace('|/api/3/?.*|', '/api/3/', $api_url);
        }
        
        // Ensure endpoint doesn't start with a slash
        $endpoint = ltrim($endpoint, '/');
        
        // Build full URL
        $url = $api_url . $endpoint;
        
        // Set up common request arguments
        $args = [
            'method' => $method,
            'headers' => [
                'Api-Token' => $api_key,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'timeout' => 30,
            'user-agent' => 'HT Contact Form - ' . HTCONTACTFORM_VERSION,
        ];
        
        // Add body data for POST/PUT requests
        if (in_array($method, ['POST', 'PUT']) && !empty($data)) {
            $args['body'] = wp_json_encode($data);
        }
        
        // Make the request
        $response = wp_remote_request($url, $args);
        
        // Check for connection errors
        if (is_wp_error($response)) {
            return new WP_Error(
                'connection_failed',
                sprintf(
                    /* translators: %s: error message */
                    __('Failed to connect to ActiveCampaign: %s', 'ht-contactform'),
                    $response->get_error_message()
                ),
                ['status' => 500]
            );
        }
        
        // Get response code
        $response_code = wp_remote_retrieve_response_code($response);
        
        // Check for HTTP errors
        if (is_wp_error($response)) {
            return new WP_Error(
                'connection_failed',
                sprintf(
                    /* translators: %s: error message */
                    __('Failed to connect to ActiveCampaign: %s', 'ht-contactform'),
                    $response->get_error_message()
                ),
                ['status' => 500]
            );
        }
        
        // Parse and return response
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error(
                'invalid_response',
                __('Invalid response received from ActiveCampaign API.', 'ht-contactform'),
                ['status' => 500]
            );
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $data,
        ], $response_code);
    }
    
    /**
     * Verify ActiveCampaign API key & URL
     *
     * @param string $api_key ActiveCampaign API key
     * @param string $api_url ActiveCampaign API URL
     * @return WP_Error|WP_REST_Response Verification result
     */
    public function verify($api_key, $api_url) {
        $response = $this->client('users/me', 'GET', [], $api_key, $api_url);
        if (is_wp_error($response)) {
            return $response;
        }
        
        // Extract data from the WP_REST_Response object
        $response_data = $response->get_data();
        
        if (!isset($response_data['data']['user'])) {
            return new WP_Error(
                'invalid_response',
                __('Invalid response from ActiveCampaign API. User data not found.', 'ht-contactform'),
                ['status' => 500]
            );
        }
        
        $user = $response_data['data']['user'];
        
        return new WP_REST_Response([
            'success' => true,
            'message' => __('ActiveCampaign connection successful.', 'ht-contactform'),
            'account' => [
                'id' => $user['id'] ?? '',
                'email' => $user['email'] ?? '',
                'username' => $user['username'] ?? '',
            ]
        ], 200);
    }
    
    /**
     * REST API endpoint for verification
     *
     * @param WP_REST_Request $request
     * @return WP_Error|WP_REST_Response
     */
    public function verify_endpoint(WP_REST_Request $request) {
        $api_key = $request->get_param('api_key');
        $api_url = $request->get_param('api_url');
        
        return $this->verify($api_key, $api_url);
    }

    /**
     * Get ActiveCampaign lists
     *
     * @return WP_Error|WP_REST_Response|array Lists data
     */
    public function get_lists() {
        $response = $this->client('lists?limit=100', 'GET');
        if (is_wp_error($response)) {
            return new WP_Error('get_lists_error', $response->get_error_message(), ['status' => 400]);
        }
        $response_data = $response->get_data();
        if(!$response_data['success']) {
            return new WP_Error('get_lists_error', $response_data['message'], ['status' => 400]);
        }
        return $response_data['data']['lists'];
    }

    /**
     * Get ActiveCampaign tags
     *
     * @return WP_Error|WP_REST_Response|array Tags data
     */
    public function get_tags() {
        $response = $this->client('tags?limit=100', 'GET');
        if (is_wp_error($response)) {
            return new WP_Error('get_tags_error', $response->get_error_message(), ['status' => 400]);
        }
        $response_data = $response->get_data();
        if(!$response_data['success']) {
            return new WP_Error('get_tags_error', $response_data['message'], ['status' => 400]);
        }
        return $response_data['data']['tags'];
    }

    /**
     * Get ActiveCampaign custom fields
     *
     * @return WP_Error|WP_REST_Response|array Fields data
     */
    public function get_fields() {
        $response = $this->client('fields?limit=100', 'GET');
        if (is_wp_error($response)) {
            return new WP_Error('get_fields_error', $response->get_error_message(), ['status' => 400]);
        }
        $response_data = $response->get_data();
        if(!$response_data['success']) {
            return new WP_Error('get_fields_error', $response_data['message'], ['status' => 400]);
        }
        return $response_data['data']['fields'];
    }

    /**
     * Get ActiveCampaign forms
     *
     * @return WP_Error|WP_REST_Response|array Forms data
     */
    public function get_forms() {
        $response = $this->client('forms?limit=100', 'GET');
        if (is_wp_error($response)) {
            return new WP_Error('get_forms_error', $response->get_error_message(), ['status' => 400]);
        }
        $response_data = $response->get_data();
        if(!$response_data['success']) {
            return new WP_Error('get_forms_error', $response_data['message'], ['status' => 400]);
        }
        return $response_data['data']['forms'];
    }

    /**
     * Sync contacts with ActiveCampaign
     *
     * @param array $data Contact data
     * @return WP_Error|WP_REST_Response Response data
     */
    public function sync_contacts($data) {
        if (empty($data['contact']) || empty($data['contact']['email'])) {
            return new WP_Error(
                'missing_email',
                __('Email is required for contact sync.', 'ht-contactform'),
                ['status' => 400]
            );
        }
        
        $response = $this->client('contact/sync', 'POST', $data);
        
        if (is_wp_error($response)) {
            return new WP_Error(
                'sync_contacts_error',
                $response->get_error_message(),
                ['status' => 400]
            );
        }
        $response_data = $response->get_data();
        if(!$response_data['success']) {
            return new WP_Error('sync_contacts_error', $response_data['message'], ['status' => 400]);
        }
        return new WP_REST_Response([
            'success' => $response_data['success'] ?? false,
            'data' => $response_data['data'] ?? [],
        ], $response->status ?? 200);
    }
    
    /**
     * Add tags to a contact
     *
     * @param string $contact_id Contact ID
     * @param array $tag_ids Array of tag IDs
     * @return WP_Error|WP_REST_Response Response data
     */
    public function add_tags_to_contact($contact_id, $tag_ids) {
        if (empty($contact_id) || empty($tag_ids) || !is_array($tag_ids)) {
            return new WP_Error(
                'missing_data',
                __('Contact ID and tag IDs are required.', 'ht-contactform'),
                ['status' => 400]
            );
        }
        
        $results = [];
        $has_error = false;
        $error_message = '';
        
        foreach ($tag_ids as $tag_id) {
            $data = [
                'contactTag' => [
                    'contact' => $contact_id,
                    'tag' => $tag_id
                ]
            ];
            
            $response = $this->client('contactTags', 'POST', $data);
            if (is_wp_error($response)) {
                $has_error = true;
                $error_message = $response->get_error_message();
                $results[] = [
                    'tag_id' => $tag_id,
                    'success' => false,
                    'message' => $error_message
                ];
            } else {
                $response_data = $response->get_data();
                $results[] = [
                    'tag_id' => $tag_id,
                    'success' => true,
                    'data' => $response_data
                ];
            }
        }
        
        if ($has_error) {
            return new WP_REST_Response([
                'success' => false,
                'message' => $error_message,
                'data' => $results
            ], 207); // 207 Multi-Status is appropriate for partial success
        }
        
        return new WP_REST_Response([
            'success' => true,
            'message' => __('Tags added successfully.', 'ht-contactform'),
            'data' => $results
        ], 200);
    }

    /**
     * Add a contact to a list.
     *
     * @param string $contact_id Contact ID
     * @param string $list_id List ID
     * @return WP_Error|WP_REST_Response Response data
     */
    public function add_contact_to_list($contact_id, $list_id) {
        $data = [
            'contactList' => [
                'contact' => $contact_id,
                'list' => $list_id,
                'status' => 1,
            ]
        ];
        
        $response = $this->client('contactLists', 'POST', $data);
        if (is_wp_error($response)) {
            return $response;
        }
        
        return $response;
    }   

    /**
     * Add a note to a contact.
     *
     * @param string $contact_id Contact ID
     * @param string $note Note
     * @return WP_Error|WP_REST_Response Response data
     */
    public function add_note($contact_id, $note) {
        $data = [
            'note' => [
                'note' => $note,
                'relid' => $contact_id,
                'reltype' => 'Subscriber',
            ]
        ];
        
        $response = $this->client('notes', 'POST', $data);
        if (is_wp_error($response)) {
            return $response;
        }
        
        return $response;
    }   
}