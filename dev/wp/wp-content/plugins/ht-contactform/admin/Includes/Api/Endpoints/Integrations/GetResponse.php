<?php

namespace HTContactFormAdmin\Includes\Api\Endpoints\Integrations;

use HTContactForm\Integrations\GetResponse as GetResponseIntegration;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * GetResponse API Handler Class
 *
 * Handles all REST API endpoints for GetResponse integration including
 * campaigns, custom fields, and tags.
 */
class GetResponse {
    //-------------------------------------------------------------------------
    // PROPERTIES
    //-------------------------------------------------------------------------

    /** @var string REST API namespace */
    private $namespace = 'ht-form/v1';

    /** @var self|null Singleton instance */
    private static $instance = null;

    /** @var string API Key */
    private $api_key;

    /** @var GetResponseIntegration GetResponse integration instance */
    private $getresponse;

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
        $this->api_key = $integrations['getresponse']['api_key'] ?? '';

        // Initialize GetResponse integration class
        $this->getresponse = GetResponseIntegration::get_instance($this->api_key);

        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes
     *
     * Sets up all the REST API endpoints for GetResponse integration
     */
    public function register_routes() {
        $routes = [
            // Get GetResponse campaigns (lists)
            [
                'endpoint' => 'getresponse/campaigns',
                'methods'  => 'GET',
                'callback' => 'get_campaigns',
            ],
            // Get GetResponse custom fields
            [
                'endpoint' => 'getresponse/fields',
                'methods'  => 'GET',
                'callback' => 'get_custom_fields',
            ],
            // Get GetResponse tags
            [
                'endpoint' => 'getresponse/tags',
                'methods'  => 'GET',
                'callback' => 'get_tags',
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
     * Verify GetResponse API key
     *
     * @param string $api_key GetResponse API key
     * @return WP_REST_Response|WP_Error Verification result
     */
    public function verify($api_key) {
        if (empty($api_key)) {
            return new WP_Error(
                'invalid_api_key',
                esc_html__('API key is required.', 'ht-contactform'),
                ['status' => 400]
            );
        }

        // Test the API key by fetching campaigns
        $getresponse = GetResponseIntegration::get_instance($api_key);
        $campaigns = $getresponse->get_campaigns();

        if (is_wp_error($campaigns)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => $campaigns->get_error_message()
            ], 401);
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => esc_html__('GetResponse API connection successful.', 'ht-contactform'),
        ], 200);
    }

    /**
     * Get GetResponse Campaigns (Lists)
     *
     * @return WP_REST_Response|WP_Error Response with GetResponse campaigns or error
     */
    public function get_campaigns() {
        if (empty($this->api_key)) {
            return new WP_Error(
                'getresponse_api_key_missing',
                'GetResponse API key is not configured.',
                ['status' => 400]
            );
        }

        // Check cache first
        $cache_key = 'ht_form_gr_campaigns_' . md5($this->api_key);
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return rest_ensure_response($cached);
        }

        $campaigns = $this->getresponse->get_campaigns();

        if (is_wp_error($campaigns)) {
            return new WP_Error(
                'getresponse_api_error',
                $campaigns->get_error_message(),
                ['status' => 500]
            );
        }

        // Cache for 1 hour
        set_transient($cache_key, $campaigns, HOUR_IN_SECONDS);

        return rest_ensure_response($campaigns);
    }

    /**
     * Get GetResponse Custom Fields
     *
     * @return WP_REST_Response|WP_Error Response with GetResponse custom fields or error
     */
    public function get_custom_fields() {
        if (empty($this->api_key)) {
            return new WP_Error(
                'getresponse_api_key_missing',
                'GetResponse API key is not configured.',
                ['status' => 400]
            );
        }

        // Check cache first
        $cache_key = 'ht_form_gr_fields_' . md5($this->api_key);
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return rest_ensure_response($cached);
        }

        $fields = $this->getresponse->get_custom_fields();

        if (is_wp_error($fields)) {
            return new WP_Error(
                'getresponse_api_error',
                $fields->get_error_message(),
                ['status' => 500]
            );
        }

        // Cache for 1 hour
        set_transient($cache_key, $fields, HOUR_IN_SECONDS);

        return rest_ensure_response($fields);
    }

    /**
     * Get GetResponse Tags
     *
     * @return WP_REST_Response|WP_Error Response with GetResponse tags or error
     */
    public function get_tags() {
        if (empty($this->api_key)) {
            return new WP_Error(
                'getresponse_api_key_missing',
                'GetResponse API key is not configured.',
                ['status' => 400]
            );
        }

        // Check cache first
        $cache_key = 'ht_form_gr_tags_' . md5($this->api_key);
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return rest_ensure_response($cached);
        }

        $tags = $this->getresponse->get_tags();

        if (is_wp_error($tags)) {
            return new WP_Error(
                'getresponse_api_error',
                $tags->get_error_message(),
                ['status' => 500]
            );
        }

        // Cache for 1 hour
        set_transient($cache_key, $tags, HOUR_IN_SECONDS);

        return rest_ensure_response($tags);
    }
}
