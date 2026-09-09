<?php

namespace HTContactFormAdmin\Includes\Api\Endpoints\Integrations;

use HTContactForm\Integrations\MailPoet as MailPoetIntegration;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * MailPoet API Handler Class
 *
 * Handles all REST API endpoints for MailPoet integration including
 * status check, lists, and subscriber fields.
 */
class MailPoet {
    //-------------------------------------------------------------------------
    // PROPERTIES
    //-------------------------------------------------------------------------

    /** @var string REST API namespace */
    private $namespace = 'ht-form/v1';

    /** @var self|null Singleton instance */
    private static $instance = null;

    /** @var MailPoetIntegration MailPoet integration instance */
    private $mailpoet;

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
        // Initialize MailPoet integration class
        $this->mailpoet = MailPoetIntegration::get_instance();

        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes
     *
     * Sets up all the REST API endpoints for MailPoet integration
     */
    public function register_routes() {
        $routes = [
            // Check MailPoet status
            [
                'endpoint' => 'mailpoet/status',
                'methods'  => 'GET',
                'callback' => 'get_status',
            ],
            // Get MailPoet lists
            [
                'endpoint' => 'mailpoet/lists',
                'methods'  => 'GET',
                'callback' => 'get_lists',
            ],
            // Get MailPoet subscriber fields
            [
                'endpoint' => 'mailpoet/fields',
                'methods'  => 'GET',
                'callback' => 'get_fields',
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
     * Get MailPoet Status
     *
     * @return WP_REST_Response Response with MailPoet status
     */
    public function get_status() {
        $is_active = $this->mailpoet->is_active();

        return new WP_REST_Response([
            'active' => $is_active,
            'message' => $is_active
                ? esc_html__('MailPoet is active and ready.', 'ht-contactform')
                : esc_html__('MailPoet plugin is not installed or activated.', 'ht-contactform'),
        ], 200);
    }

    /**
     * Get MailPoet Lists
     *
     * @return WP_REST_Response|WP_Error Response with MailPoet lists or error
     */
    public function get_lists() {
        if (!$this->mailpoet->is_active()) {
            return new WP_Error(
                'mailpoet_not_active',
                esc_html__('MailPoet plugin is not installed or activated.', 'ht-contactform'),
                ['status' => 400]
            );
        }

        $lists = $this->mailpoet->get_lists();

        if (is_wp_error($lists)) {
            return new WP_Error(
                'mailpoet_api_error',
                $lists->get_error_message(),
                ['status' => 500]
            );
        }

        return rest_ensure_response($lists);
    }

    /**
     * Get MailPoet Subscriber Fields
     *
     * @return WP_REST_Response|WP_Error Response with MailPoet fields or error
     */
    public function get_fields() {
        if (!$this->mailpoet->is_active()) {
            return new WP_Error(
                'mailpoet_not_active',
                esc_html__('MailPoet plugin is not installed or activated.', 'ht-contactform'),
                ['status' => 400]
            );
        }

        $fields = $this->mailpoet->get_subscriber_fields();

        if (is_wp_error($fields)) {
            return new WP_Error(
                'mailpoet_api_error',
                $fields->get_error_message(),
                ['status' => 500]
            );
        }

        return rest_ensure_response($fields);
    }
}
