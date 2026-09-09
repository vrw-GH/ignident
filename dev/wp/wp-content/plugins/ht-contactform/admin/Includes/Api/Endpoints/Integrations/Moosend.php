<?php

namespace HTContactFormAdmin\Includes\Api\Endpoints\Integrations;

use HTContactForm\Integrations\Moosend as MoosendIntegration;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Moosend API Handler Class
 *
 * Handles all REST API endpoints for Moosend integration including
 * mailing lists and custom fields.
 */
class Moosend {
    //-------------------------------------------------------------------------
    // PROPERTIES
    //-------------------------------------------------------------------------

    /** @var string REST API namespace */
    private $namespace = 'ht-form/v1';

    /** @var self|null Singleton instance */
    private static $instance = null;

    /** @var string API Key */
    private $api_key;

    /** @var MoosendIntegration Moosend integration instance */
    private $moosend;

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
        $this->api_key = $integrations['moosend']['api_key'] ?? '';

        // Initialize Moosend integration class
        $this->moosend = MoosendIntegration::get_instance($this->api_key);

        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Validate list ID format
     *
     * @param string $param List ID to validate
     * @return bool Whether the list ID is valid
     */
    public function validate_list_id($param) {
        return is_string($param) && preg_match('/^[a-zA-Z0-9_-]+$/', $param);
    }

    /**
     * Register REST API routes
     *
     * Sets up all the REST API endpoints for Moosend integration
     */
    public function register_routes() {
        // Common args for list_id parameter with sanitization and validation
        $list_id_args = [
            'list_id' => [
                'required'          => true,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => [$this, 'validate_list_id'],
            ],
        ];

        $routes = [
            // Get Moosend mailing lists
            [
                'endpoint' => 'moosend/lists',
                'methods'  => 'GET',
                'callback' => 'get_lists',
            ],
            // Verify Moosend API key
            [
                'endpoint' => 'moosend/verify',
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
            // Get Moosend custom fields for a list
            [
                'endpoint' => 'moosend/fields',
                'methods'  => 'GET',
                'callback' => 'get_custom_fields',
                'args'     => $list_id_args,
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
     * Verify Moosend API key (string parameter version)
     *
     * @param string $api_key Moosend API key
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

        // Test the API key by fetching lists
        $moosend = MoosendIntegration::get_instance($api_key);
        $lists = $moosend->get_lists();

        if (is_wp_error($lists)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => $lists->get_error_message()
            ], 401);
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => esc_html__('Moosend API connection successful.', 'ht-contactform'),
        ], 200);
    }

    /**
     * Verify Moosend API key (REST endpoint version)
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

        // Test the API key by fetching lists
        $moosend = MoosendIntegration::get_instance($api_key);
        $lists = $moosend->get_lists();

        if (is_wp_error($lists)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => $lists->get_error_message()
            ], 401);
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => esc_html__('Moosend API connection successful.', 'ht-contactform'),
        ], 200);
    }

    /**
     * Get Moosend Mailing Lists
     *
     * @return WP_REST_Response|WP_Error Response with Moosend lists or error
     */
    public function get_lists() {
        if (empty($this->api_key)) {
            return new WP_Error(
                'moosend_api_key_missing',
                'Moosend API key is not configured.',
                ['status' => 400]
            );
        }

        $lists = $this->moosend->get_lists();

        if (is_wp_error($lists)) {
            return new WP_Error(
                'moosend_api_error',
                $lists->get_error_message(),
                ['status' => 500]
            );
        }

        return rest_ensure_response($lists);
    }

    /**
     * Get Moosend Custom Fields
     *
     * @param WP_REST_Request $request REST request object
     * @return WP_REST_Response|WP_Error Response with Moosend custom fields or error
     */
    public function get_custom_fields(WP_REST_Request $request) {
        if (empty($this->api_key)) {
            return new WP_Error(
                'moosend_api_key_missing',
                'Moosend API key is not configured.',
                ['status' => 400]
            );
        }

        $list_id = $request->get_param('list_id');

        $fields = $this->moosend->get_custom_fields($list_id);

        if (is_wp_error($fields)) {
            return new WP_Error(
                'moosend_api_error',
                $fields->get_error_message(),
                ['status' => 500]
            );
        }

        return rest_ensure_response($fields);
    }
}
