<?php

namespace HTContactFormAdmin\Includes\Api\Endpoints\Integrations;

use MailerLite\MailerLite as MailerLiteIntegration;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * MailerLite API Handler Class
 * 
 * Handles all REST API endpoints for MailerLite integration including groups and merge fields.
 */
class MailerLite {
    //-------------------------------------------------------------------------
    // PROPERTIES
    //-------------------------------------------------------------------------
    
    /** @var string REST API namespace */
    private $namespace = 'ht-form/v1';

    /** @var self|null Singleton instance */
    private static $instance = null;
    
    /** @var string API Key */
    private $api_key;
    
    /** @var MailerLiteIntegration MailerLite integration instance */
    private $mailerlite;

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
        $this->api_key = $integrations['mailerlite']['api_key'] ?? '';
        
        // Initialize MailerLite integration class
        $this->mailerlite = new MailerLiteIntegration(['api_key' => $this->api_key]);
        
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes
     * 
     * Sets up all the REST API endpoints for MailerLite integration
     */
    public function register_routes() {
        $routes = [
            // Get MailerLite fields
            [
                'endpoint' => 'mailerlite/fields',
                'methods'  => 'GET',
                'callback' => 'get_fields',
            ],
            // Get mailerLite groups
            [
                'endpoint' => 'mailerlite/groups',
                'methods'  => 'GET',
                'callback' => 'get_groups',
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
     * Verify MailerLite API key
     *
     * @param string $api_key MailerLite API key
     * @return WP_REST_Response|WP_Error Verification result
     */
    public function verify($api_key) {
        if (empty($api_key)) {
            return new WP_Error(
                'missing_api_key',
                esc_html__('API key is required for MailerLite integration.', 'ht-contactform'),
                ['status' => 400]
            );
        }
        
        $mailerlite = new MailerLiteIntegration(['api_key' => $api_key]);

        $response = $mailerlite->fields->get();
        
        if (is_wp_error($response)) {
            return new WP_Error(
                'connection_failed',
                sprintf(
                    /* translators: %s: error message */
                    __('Failed to connect to MailerLite: %s', 'ht-contactform'),
                    $response->get_error_message()
                ),
                ['status' => 500]
            );
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => esc_html__('MailerLite API connection successful.', 'ht-contactform'),
        ], 200);
    }
    
    /**
     * Get MailerLite Lists
     * 
     * @param WP_REST_Request $request Request object
     * @return array|WP_Error Response with MailerLite lists or error
     */
    public function get_groups() {
        if (empty($this->api_key)) {
            return new WP_Error(
                'missing_api_key',
                esc_html__('API key is required for MailerLite integration.', 'ht-contactform'),
                ['status' => 400]
            );
        }
        
        $mailerlite = new MailerLiteIntegration(['api_key' => $this->api_key]);

        $response = $mailerlite->groups->get();
        
        if (is_wp_error($response)) {
            return new WP_Error(
                'mailerlite_api_error',
                $response->get_error_message(),
                ['status' => 500]
            );
        }

        $body = wp_remote_retrieve_body($response);
        
        return (array) $body['data'] ?? [];
    }
    
    /**
     * Get MailerLite Merge Fields
     * 
     * @return array|WP_Error Response with MailerLite fields or error
     */
    public function get_fields() {
        if (empty($this->api_key)) {
            return new WP_Error(
                'missing_api_key',
                esc_html__('API key is required for MailerLite integration.', 'ht-contactform'),
                ['status' => 400]
            );
        }
        
        $mailerlite = new MailerLiteIntegration(['api_key' => $this->api_key]);

        $response = $mailerlite->fields->get();
        
        if (is_wp_error($response)) {
            return new WP_Error(
                'mailerlite_api_error',
                $response->get_error_message(),
                ['status' => 500]
            );
        }

        $body = wp_remote_retrieve_body($response);
        
        return (array) $body['data'] ?? [];
    }
}