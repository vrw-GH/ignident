<?php

namespace HTContactFormAdmin\Includes\Api\Endpoints\Integrations;

use HTContactForm\Integrations\HubSpot as HubSpotIntegration;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * HubSpot API Handler Class
 *
 * Handles all REST API endpoints for HubSpot integration including
 * lists and contact properties.
 */
class HubSpot {
    //-------------------------------------------------------------------------
    // PROPERTIES
    //-------------------------------------------------------------------------

    /** @var string REST API namespace */
    private $namespace = 'ht-form/v1';

    /** @var self|null Singleton instance */
    private static $instance = null;

    /** @var string Access Token */
    private $access_token;

    /** @var HubSpotIntegration HubSpot integration instance */
    private $hubspot;

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
        $this->access_token = $integrations['hubspot']['access_token'] ?? '';

        // Initialize HubSpot integration class
        $this->hubspot = HubSpotIntegration::get_instance($this->access_token);

        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes
     *
     * Sets up all the REST API endpoints for HubSpot integration
     */
    public function register_routes() {
        $routes = [
            // Get HubSpot lists
            [
                'endpoint' => 'hubspot/lists',
                'methods'  => 'GET',
                'callback' => 'get_lists',
            ],
            // Verify HubSpot access token
            [
                'endpoint' => 'hubspot/verify',
                'methods'  => 'POST',
                'callback' => 'verify_access_token',
                'args'     => [
                    'access_token' => [
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ],
            // Get HubSpot contact properties
            [
                'endpoint' => 'hubspot/properties',
                'methods'  => 'GET',
                'callback' => 'get_properties',
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
     * Verify HubSpot access token
     *
     * @param WP_REST_Request|string $request_or_token REST request object or access token string
     * @return WP_REST_Response|WP_Error Verification result
     */
    public function verify($request_or_token) {
        // Support both REST request and direct call
        if ($request_or_token instanceof WP_REST_Request) {
            $access_token = $request_or_token->get_param('access_token');
        } else {
            $access_token = $request_or_token;
        }

        if (empty($access_token)) {
            return new WP_Error(
                'invalid_access_token',
                esc_html__('Access token is required.', 'ht-contactform'),
                ['status' => 400]
            );
        }

        // Test the access token
        $hubspot = HubSpotIntegration::get_instance($access_token);
        $result = $hubspot->verify();

        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => $result->get_error_message()
            ], 401);
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => esc_html__('HubSpot API connection successful.', 'ht-contactform'),
        ], 200);
    }

    /**
     * Get HubSpot Lists
     *
     * @return WP_REST_Response|WP_Error Response with HubSpot lists or error
     */
    public function get_lists() {
        if (empty($this->access_token)) {
            return new WP_Error(
                'hubspot_access_token_missing',
                'HubSpot access token is not configured.',
                ['status' => 400]
            );
        }

        $lists = $this->hubspot->get_lists();

        if (is_wp_error($lists)) {
            return new WP_Error(
                'hubspot_api_error',
                $lists->get_error_message(),
                ['status' => 500]
            );
        }

        return rest_ensure_response($lists);
    }

    /**
     * Get HubSpot Contact Properties
     *
     * @return WP_REST_Response|WP_Error Response with HubSpot properties or error
     */
    public function get_properties() {
        if (empty($this->access_token)) {
            return new WP_Error(
                'hubspot_access_token_missing',
                'HubSpot access token is not configured.',
                ['status' => 400]
            );
        }

        $properties = $this->hubspot->get_properties();

        if (is_wp_error($properties)) {
            return new WP_Error(
                'hubspot_api_error',
                $properties->get_error_message(),
                ['status' => 500]
            );
        }

        return rest_ensure_response($properties);
    }
}
