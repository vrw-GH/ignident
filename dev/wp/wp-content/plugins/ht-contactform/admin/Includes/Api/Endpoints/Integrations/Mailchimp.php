<?php

namespace HTContactFormAdmin\Includes\Api\Endpoints\Integrations;

use HTContactForm\Integrations\Mailchimp as MailchimpIntegration;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Mailchimp API Handler Class
 * 
 * Handles all REST API endpoints for Mailchimp integration including tags and merge fields.
 */
class Mailchimp {
    //-------------------------------------------------------------------------
    // PROPERTIES
    //-------------------------------------------------------------------------
    
    /** @var string REST API namespace */
    private $namespace = 'ht-form/v1';

    /** @var self|null Singleton instance */
    private static $instance = null;
    
    /** @var string API Key */
    private $api_key;
    
    /** @var MailchimpIntegration Mailchimp integration instance */
    private $mailchimp;

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
        $this->api_key = $integrations['mailchimp']['api_key'] ?? '';
        
        // Initialize Mailchimp integration class
        $this->mailchimp = MailchimpIntegration::get_instance($this->api_key);
        
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes
     * 
     * Sets up all the REST API endpoints for Mailchimp integration
     */
    public function register_routes() {
        $routes = [
            // Get mailchimp tags
            [
                'endpoint' => 'mailchimp/tags/(?P<list_id>[a-zA-Z0-9-]+)',
                'methods'  => 'GET',
                'callback' => 'get_tags',
                'args'     => [
                    'list_id' => [
                        'required' => true,
                        'type'     => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ]
                ]
            ],
            // Get mailchimp fields
            [
                'endpoint' => 'mailchimp/fields/(?P<list_id>[a-zA-Z0-9-]+)',
                'methods'  => 'GET',
                'callback' => 'get_fields',
                'args'     => [
                    'list_id' => [
                        'required' => true,
                        'type'     => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ]
                ]
            ],
            // Get mailchimp lists
            [
                'endpoint' => 'mailchimp/lists',
                'methods'  => 'GET',
                'callback' => 'get_lists',
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
     * Verify Mailchimp API key
     *
     * @param string $api_key Mailchimp API key
     * @return WP_REST_Response|WP_Error Verification result
     */
    public function verify($api_key) {
        // Extract the data center from the API key
        $parts = explode('-', $api_key);
        if (count($parts) != 2) {
            return new WP_Error(
                'invalid_api_key',
                esc_html__('Invalid API key format. Mailchimp API keys should be in the format "key-datacenter".', 'ht-contactform'),
                ['status' => 400]
            );
        }
        
        $data_center = $parts[1];
        $url = "https://{$data_center}.api.mailchimp.com/3.0/";
        
        $args = [
            'method' => 'GET',
            'timeout' => 15,
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode("anystring:{$api_key}")
            ]
        ];
        
        $response = wp_remote_request($url, $args);
        
        // Check if request was successful
        if (is_wp_error($response)) {
            return new WP_Error(
                'connection_failed',
                sprintf(
                    /* translators: %s: error message */
                    __('Failed to connect to Mailchimp: %s', 'ht-contactform'),
                    $response->get_error_message()
                ),
                ['status' => 500]
            );
        }
        
        $http_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        switch ($http_code) {
            case 200:
                return new WP_REST_Response([
                    'success' => true,
                    'message' => esc_html__('Mailchimp API connection successful.', 'ht-contactform'),
                ], 200);
            default:
                $error_message = isset($data['detail']) ? $data['detail'] : esc_html__('Unknown error occurred.', 'ht-contactform');

                return new WP_REST_Response([
                    'success' => false,
                    'message' => $error_message
                ], $http_code);
        }
    }
    
    /**
     * Get Mailchimp Tags
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with Mailchimp tags or error
     */
    public function get_tags($request) {
        if (empty($this->api_key)) {
            return new WP_Error(
                'mailchimp_api_key_missing',
                'Mailchimp API key is not configured.',
                ['status' => 400]
            );
        }
        
        $list_id = $request->get_param('list_id');
        
        $tags = $this->mailchimp->get_search_tags($list_id);
        
        if (is_wp_error($tags)) {
            return new WP_Error(
                'mailchimp_api_error',
                $tags->get_error_message(),
                ['status' => 500]
            );
        }
        
        return rest_ensure_response($tags);
    }
    
    /**
     * Get Mailchimp Merge Fields
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with Mailchimp fields or error
     */
    public function get_fields($request) {
        if (empty($this->api_key)) {
            return new WP_Error(
                'mailchimp_api_key_missing',
                'Mailchimp API key is not configured.',
                ['status' => 400]
            );
        }
        
        $list_id = $request->get_param('list_id');
        
        $fields = $this->mailchimp->get_merge_fields($list_id);
        
        if (is_wp_error($fields)) {
            return new WP_Error(
                'mailchimp_api_error',
                $fields->get_error_message(),
                ['status' => 500]
            );
        }
        
        return rest_ensure_response($fields);
    }
    
    /**
     * Get Mailchimp Lists
     * 
     * @return array|WP_Error Response with Mailchimp lists or error
     */
    public function get_lists() {
        if (empty($this->api_key)) {
            return new WP_Error(
                'mailchimp_api_key_missing',
                'Mailchimp API key is not configured.',
                ['status' => 400]
            );
        }
        
        $lists = $this->mailchimp->get_lists();
        
        if (is_wp_error($lists)) {
            return new WP_Error(
                'mailchimp_api_error',
                $lists->get_error_message(),
                ['status' => 500]
            );
        }
        
        return rest_ensure_response($lists);
    }
}