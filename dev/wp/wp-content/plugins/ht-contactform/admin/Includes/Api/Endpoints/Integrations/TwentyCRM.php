<?php

namespace HTContactFormAdmin\Includes\Api\Endpoints\Integrations;

use HTContactForm\Integrations\TwentyCRM as TwentyCRMIntegration;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Twenty CRM API Handler Class
 *
 * Handles all REST API endpoints for the Twenty CRM integration including
 * credential verification and schema (object/field) discovery.
 */
class TwentyCRM {
    //-------------------------------------------------------------------------
    // PROPERTIES
    //-------------------------------------------------------------------------

    /** @var string REST API namespace */
    private $namespace = 'ht-form/v1';

    /** @var self|null Singleton instance */
    private static $instance = null;

    /** @var string API key */
    private $api_key;

    /** @var string Instance URL */
    private $base_url;

    /** @var TwentyCRMIntegration Twenty CRM integration instance */
    private $twentycrm;

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
        $integrations   = get_option('ht_form_integrations', []);
        $this->api_key  = $integrations['twentycrm']['api_key'] ?? '';
        $this->base_url = $integrations['twentycrm']['base_url'] ?? '';

        $this->twentycrm = TwentyCRMIntegration::get_instance($this->api_key, $this->base_url);

        add_action('rest_api_init', [$this, 'register_routes']);
        add_action('update_option_ht_form_integrations', [$this, 'flush_metadata_cache'], 10, 0);
    }

    /**
     * Register REST API routes
     *
     * Sets up all the REST API endpoints for the Twenty CRM integration
     */
    public function register_routes() {
        $routes = [
            // Verify Twenty CRM credentials
            [
                'endpoint' => 'twentycrm/verify',
                'methods'  => 'POST',
                'callback' => 'verify',
                'args'     => [
                    'api_key' => [
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'base_url' => [
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'esc_url_raw',
                    ],
                ],
            ],
            // Get workspace objects
            [
                'endpoint' => 'twentycrm/objects',
                'methods'  => 'GET',
                'callback' => 'get_objects',
                'args'     => [
                    'raw' => [
                        'required'          => false,
                        'type'              => 'boolean',
                        'default'           => false,
                        'sanitize_callback' => 'rest_sanitize_boolean',
                    ],
                    'source' => [
                        'required'          => false,
                        'type'              => 'string',
                        'default'           => 'objects',
                        'enum'              => ['objects', 'fields', 'graphql'],
                        'sanitize_callback' => 'sanitize_key',
                    ],
                ],
            ],
            // Get mappable fields of an object
            [
                'endpoint' => 'twentycrm/fields',
                'methods'  => 'GET',
                'callback' => 'get_fields',
                'args'     => [
                    'object' => [
                        'required'          => false,
                        'type'              => 'string',
                        'default'           => 'person',
                        'sanitize_callback' => 'sanitize_key',
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

    /**
     * Drop cached workspace metadata when credentials change
     *
     * @return void
     */
    public function flush_metadata_cache() {
        $this->twentycrm->flush_metadata_cache();
    }

    //-------------------------------------------------------------------------
    // MAIN OPERATIONS
    //-------------------------------------------------------------------------

    /**
     * Verify Twenty CRM credentials
     *
     * @param WP_REST_Request|array $request REST request object, or a settings array
     * @return WP_REST_Response Verification result
     */
    public function verify($request) {
        $api_key  = '';
        $base_url = '';

        if ($request instanceof WP_REST_Request) {
            $api_key  = (string) $request->get_param('api_key');
            $base_url = (string) $request->get_param('base_url');
        } elseif (is_array($request)) {
            // Called directly by the shared integrations verify endpoint.
            $api_key  = (string) ($request['api_key'] ?? '');
            $base_url = (string) ($request['base_url'] ?? '');
        }

        if (empty($api_key) || empty($base_url)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => esc_html__('API key and instance URL are required.', 'ht-contactform'),
            ], 400);
        }

        $twentycrm = TwentyCRMIntegration::get_instance($api_key, $base_url);
        $result    = $twentycrm->verify();

        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => $result->get_error_message(),
            ], 401);
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => esc_html__('Twenty CRM API connection successful.', 'ht-contactform'),
        ], 200);
    }

    /**
     * Get Twenty CRM workspace objects
     *
     * Pass ?raw=1 to return the untouched Metadata API payload, which is
     * useful when a self-hosted instance reports an unexpected envelope.
     *
     * @param WP_REST_Request $request REST request object
     * @return WP_REST_Response|WP_Error Response with objects or error
     */
    public function get_objects($request = null) {
        $missing = $this->credentials_error();

        if ($missing) {
            return $missing;
        }

        if ($request instanceof WP_REST_Request && $request->get_param('raw')) {
            $source = (string) $request->get_param('source');
            $raw    = $this->twentycrm->get_raw_metadata($source !== '' ? $source : 'objects');

            if (is_wp_error($raw)) {
                return new WP_Error(
                    'twentycrm_api_error',
                    $raw->get_error_message(),
                    ['status' => 500]
                );
            }

            return rest_ensure_response($raw);
        }

        $objects = $this->twentycrm->get_objects();

        if (is_wp_error($objects)) {
            return new WP_Error(
                'twentycrm_api_error',
                $objects->get_error_message(),
                ['status' => 500]
            );
        }

        return rest_ensure_response($objects);
    }

    /**
     * Get mappable fields for a Twenty CRM object
     *
     * @param WP_REST_Request $request REST request object
     * @return WP_REST_Response|WP_Error Response with fields or error
     */
    public function get_fields($request) {
        $missing = $this->credentials_error();

        if ($missing) {
            return $missing;
        }

        $object = 'person';

        if ($request instanceof WP_REST_Request) {
            $object = (string) ($request->get_param('object') ?: 'person');
        }

        $fields = $this->twentycrm->get_fields($object);

        if (is_wp_error($fields)) {
            return new WP_Error(
                'twentycrm_api_error',
                $fields->get_error_message(),
                ['status' => 500]
            );
        }

        return rest_ensure_response($fields);
    }

    /**
     * Build the missing credentials error, when applicable
     *
     * @return WP_Error|null WP_Error when credentials are missing, null otherwise
     */
    private function credentials_error() {
        if (empty($this->api_key) || empty($this->base_url)) {
            return new WP_Error(
                'twentycrm_credentials_missing',
                esc_html__('Twenty CRM API key and instance URL are not configured.', 'ht-contactform'),
                ['status' => 400]
            );
        }

        return null;
    }
}
