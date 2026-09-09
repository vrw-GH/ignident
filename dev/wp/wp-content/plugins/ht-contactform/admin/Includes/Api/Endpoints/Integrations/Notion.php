<?php

namespace HTContactFormAdmin\Includes\Api\Endpoints\Integrations;

use HTContactForm\Integrations\Notion as NotionIntegration;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Notion API Handler Class
 *
 * Handles all REST API endpoints for Notion integration including
 * databases and properties.
 */
class Notion {
    //-------------------------------------------------------------------------
    // PROPERTIES
    //-------------------------------------------------------------------------

    /** @var string REST API namespace */
    private $namespace = 'ht-form/v1';

    /** @var self|null Singleton instance */
    private static $instance = null;

    /** @var string API Key */
    private $api_key;

    /** @var NotionIntegration Notion integration instance */
    private $notion;

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
        $this->api_key = $integrations['notion']['api_key'] ?? '';

        // Initialize Notion integration class
        $this->notion = NotionIntegration::get_instance($this->api_key);

        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Validate database ID format
     *
     * @param string $param Database ID to validate
     * @return bool Whether the database ID is valid
     */
    public function validate_database_id($param) {
        // Notion IDs are UUIDs with or without hyphens
        return is_string($param) && preg_match('/^[a-zA-Z0-9-]+$/', $param);
    }

    /**
     * Register REST API routes
     *
     * Sets up all the REST API endpoints for Notion integration
     */
    public function register_routes() {
        // Common args for database_id parameter with sanitization and validation
        $database_id_args = [
            'database_id' => [
                'required'          => true,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => [$this, 'validate_database_id'],
            ],
        ];

        $routes = [
            // Get Notion databases
            [
                'endpoint' => 'notion/databases',
                'methods'  => 'GET',
                'callback' => 'get_databases',
            ],
            // Verify Notion API key
            [
                'endpoint' => 'notion/verify',
                'methods'  => 'POST',
                'callback' => 'verify_api_key',
                'args'     => [
                    'api_key' => [
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ],
            // Get Notion database properties
            [
                'endpoint' => 'notion/properties',
                'methods'  => 'GET',
                'callback' => 'get_properties',
                'args'     => $database_id_args,
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
     * Verify Notion API key (string parameter version)
     *
     * @param string $api_key Notion API key
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

        // Test the API key by fetching databases
        $notion = NotionIntegration::get_instance($api_key);
        $databases = $notion->get_databases();

        if (is_wp_error($databases)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => $databases->get_error_message()
            ], 401);
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => esc_html__('Notion API connection successful.', 'ht-contactform'),
        ], 200);
    }

    /**
     * Verify Notion API key (REST endpoint version)
     *
     * @param WP_REST_Request $request REST request object
     * @return WP_REST_Response|WP_Error Verification result
     */
    public function verify_api_key(WP_REST_Request $request) {
        $api_key = $request->get_param('api_key');

        if (empty($api_key)) {
            return new WP_Error(
                'invalid_api_key',
                esc_html__('API key is required.', 'ht-contactform'),
                ['status' => 400]
            );
        }

        // Test the API key by fetching databases
        $notion = NotionIntegration::get_instance($api_key);
        $databases = $notion->get_databases();

        if (is_wp_error($databases)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => $databases->get_error_message()
            ], 401);
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => esc_html__('Notion API connection successful.', 'ht-contactform'),
        ], 200);
    }

    /**
     * Get Notion Databases
     *
     * @return WP_REST_Response|WP_Error Response with Notion databases or error
     */
    public function get_databases() {
        if (empty($this->api_key)) {
            return new WP_Error(
                'notion_api_key_missing',
                'Notion API key is not configured.',
                ['status' => 400]
            );
        }

        $databases = $this->notion->get_databases();

        if (is_wp_error($databases)) {
            return new WP_Error(
                'notion_api_error',
                $databases->get_error_message(),
                ['status' => 500]
            );
        }

        return rest_ensure_response($databases);
    }

    /**
     * Get Notion Database Properties
     *
     * @param WP_REST_Request $request REST request object
     * @return WP_REST_Response|WP_Error Response with database properties or error
     */
    public function get_properties(WP_REST_Request $request) {
        if (empty($this->api_key)) {
            return new WP_Error(
                'notion_api_key_missing',
                'Notion API key is not configured.',
                ['status' => 400]
            );
        }

        $database_id = $request->get_param('database_id');

        $properties = $this->notion->get_database_properties($database_id);

        if (is_wp_error($properties)) {
            return new WP_Error(
                'notion_api_error',
                $properties->get_error_message(),
                ['status' => 500]
            );
        }

        return rest_ensure_response($properties);
    }
}
