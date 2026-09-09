<?php

namespace HTContactFormAdmin\Includes\Api\Endpoints\Integrations;

use HTContactForm\Integrations\Drip as DripIntegration;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Drip API Handler Class
 *
 * Handles all REST API endpoints for Drip integration including
 * accounts, tags, and custom fields.
 */
class Drip {
    //-------------------------------------------------------------------------
    // PROPERTIES
    //-------------------------------------------------------------------------

    /** @var string REST API namespace */
    private $namespace = 'ht-form/v1';

    /** @var self|null Singleton instance */
    private static $instance = null;

    /** @var string API Key */
    private $api_key;

    /** @var DripIntegration Drip integration instance */
    private $drip;

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
        $this->api_key = $integrations['drip']['api_key'] ?? '';

        // Initialize Drip integration class
        $this->drip = DripIntegration::get_instance($this->api_key);

        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Validate account ID format
     *
     * @param string $param Account ID to validate
     * @return bool Whether the account ID is valid
     */
    public function validate_account_id($param) {
        return is_string($param) && preg_match('/^[a-zA-Z0-9_-]+$/', $param);
    }

    /**
     * Register REST API routes
     *
     * Sets up all the REST API endpoints for Drip integration
     */
    public function register_routes() {
        // Common args for account_id parameter with sanitization and validation
        $account_id_args = [
            'account_id' => [
                'required'          => true,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => [$this, 'validate_account_id'],
            ],
        ];

        $routes = [
            // Get Drip accounts
            [
                'endpoint' => 'drip/accounts',
                'methods'  => 'GET',
                'callback' => 'get_accounts',
            ],
            // Verify Drip API key
            [
                'endpoint' => 'drip/verify',
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
            // Get Drip tags
            [
                'endpoint' => 'drip/tags',
                'methods'  => 'GET',
                'callback' => 'get_tags',
                'args'     => $account_id_args,
            ],
            // Get Drip custom fields
            [
                'endpoint' => 'drip/fields',
                'methods'  => 'GET',
                'callback' => 'get_custom_fields',
                'args'     => $account_id_args,
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
     * Verify Drip API key (string parameter version)
     *
     * @param string $api_key Drip API key
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

        // Test the API key by fetching accounts
        $drip = DripIntegration::get_instance($api_key);
        $accounts = $drip->get_accounts();

        if (is_wp_error($accounts)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => $accounts->get_error_message()
            ], 401);
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => esc_html__('Drip API connection successful.', 'ht-contactform'),
        ], 200);
    }

    /**
     * Verify Drip API key (REST endpoint version)
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

        // Test the API key by fetching accounts
        $drip = DripIntegration::get_instance($api_key);
        $accounts = $drip->get_accounts();

        if (is_wp_error($accounts)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => $accounts->get_error_message()
            ], 401);
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => esc_html__('Drip API connection successful.', 'ht-contactform'),
        ], 200);
    }

    /**
     * Get Drip Accounts
     *
     * @return WP_REST_Response|WP_Error Response with Drip accounts or error
     */
    public function get_accounts() {
        if (empty($this->api_key)) {
            return new WP_Error(
                'drip_api_key_missing',
                'Drip API key is not configured.',
                ['status' => 400]
            );
        }

        $accounts = $this->drip->get_accounts();

        if (is_wp_error($accounts)) {
            return new WP_Error(
                'drip_api_error',
                $accounts->get_error_message(),
                ['status' => 500]
            );
        }

        return rest_ensure_response($accounts);
    }

    /**
     * Get Drip Tags
     *
     * @param WP_REST_Request $request REST request object
     * @return WP_REST_Response|WP_Error Response with Drip tags or error
     */
    public function get_tags(WP_REST_Request $request) {
        if (empty($this->api_key)) {
            return new WP_Error(
                'drip_api_key_missing',
                'Drip API key is not configured.',
                ['status' => 400]
            );
        }

        $account_id = $request->get_param('account_id');

        $tags = $this->drip->get_tags($account_id);

        if (is_wp_error($tags)) {
            return new WP_Error(
                'drip_api_error',
                $tags->get_error_message(),
                ['status' => 500]
            );
        }

        return rest_ensure_response($tags);
    }

    /**
     * Get Drip Custom Fields
     *
     * @param WP_REST_Request $request REST request object
     * @return WP_REST_Response|WP_Error Response with Drip custom fields or error
     */
    public function get_custom_fields(WP_REST_Request $request) {
        if (empty($this->api_key)) {
            return new WP_Error(
                'drip_api_key_missing',
                'Drip API key is not configured.',
                ['status' => 400]
            );
        }

        $account_id = $request->get_param('account_id');

        $fields = $this->drip->get_custom_fields($account_id);

        if (is_wp_error($fields)) {
            return new WP_Error(
                'drip_api_error',
                $fields->get_error_message(),
                ['status' => 500]
            );
        }

        return rest_ensure_response($fields);
    }
}
