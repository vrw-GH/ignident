<?php

namespace HTContactFormAdmin\Includes\Api\Endpoints\Integrations;

use HTContactForm\Integrations\iContact as iContactIntegration;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * iContact API Handler Class
 *
 * Handles all REST API endpoints for iContact integration including
 * accounts, client folders, lists, and custom fields.
 */
class iContact {
    //-------------------------------------------------------------------------
    // PROPERTIES
    //-------------------------------------------------------------------------

    /** @var string REST API namespace */
    private $namespace = 'ht-form/v1';

    /** @var self|null Singleton instance */
    private static $instance = null;

    /** @var array API credentials */
    private $credentials = [];

    /** @var iContactIntegration iContact integration instance */
    private $icontact;

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

        $this->credentials = [
            'app_id'           => $integrations['icontact']['app_id'] ?? '',
            'username'         => $integrations['icontact']['username'] ?? '',
            'password'         => $integrations['icontact']['password'] ?? '',
            'account_id'       => $integrations['icontact']['account_id'] ?? '',
            'client_folder_id' => $integrations['icontact']['client_folder_id'] ?? '',
        ];

        // Initialize iContact integration class
        $this->icontact = iContactIntegration::get_instance($this->credentials);

        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Validate ID format
     *
     * @param string $param ID to validate
     * @return bool Whether the ID is valid
     */
    public function validate_id($param) {
        return is_string($param) && preg_match('/^[a-zA-Z0-9_-]+$/', $param);
    }

    /**
     * Register REST API routes
     *
     * Sets up all the REST API endpoints for iContact integration
     */
    public function register_routes() {
        $routes = [
            // Verify iContact credentials
            [
                'endpoint' => 'icontact/verify',
                'methods'  => 'POST',
                'callback' => 'verify_credentials',
                'args'     => [
                    'app_id' => [
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'username' => [
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'password' => [
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ],
            // Get iContact accounts
            [
                'endpoint' => 'icontact/accounts',
                'methods'  => 'GET',
                'callback' => 'get_accounts',
            ],
            // Get iContact client folders
            [
                'endpoint' => 'icontact/folders',
                'methods'  => 'GET',
                'callback' => 'get_client_folders',
                'args'     => [
                    'account_id' => [
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                        'validate_callback' => [$this, 'validate_id'],
                    ],
                ],
            ],
            // Get iContact lists
            [
                'endpoint' => 'icontact/lists',
                'methods'  => 'GET',
                'callback' => 'get_lists',
            ],
            // Get iContact custom fields
            [
                'endpoint' => 'icontact/fields',
                'methods'  => 'GET',
                'callback' => 'get_custom_fields',
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
     * Verify iContact credentials
     *
     * @param WP_REST_Request|array $request_or_credentials REST request object or credentials array
     * @return WP_REST_Response|WP_Error Verification result
     */
    public function verify($request_or_credentials) {
        // Support both REST request and direct call
        if ($request_or_credentials instanceof WP_REST_Request) {
            $credentials = [
                'app_id'   => $request_or_credentials->get_param('app_id'),
                'username' => $request_or_credentials->get_param('username'),
                'password' => $request_or_credentials->get_param('password'),
            ];
        } else {
            $credentials = $request_or_credentials;
        }

        if (empty($credentials['app_id']) || empty($credentials['username']) || empty($credentials['password'])) {
            return new WP_Error(
                'invalid_credentials',
                esc_html__('All credentials (App ID, Username, Password) are required.', 'ht-contactform'),
                ['status' => 400]
            );
        }

        // Test credentials by fetching accounts
        $icontact = iContactIntegration::get_instance($credentials);
        $accounts = $icontact->get_accounts();

        if (is_wp_error($accounts)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => $accounts->get_error_message()
            ], 401);
        }

        return new WP_REST_Response([
            'success'  => true,
            'message'  => esc_html__('iContact API connection successful.', 'ht-contactform'),
            'accounts' => $accounts,
        ], 200);
    }

    /**
     * Get iContact Accounts
     *
     * @return WP_REST_Response|WP_Error Response with iContact accounts or error
     */
    public function get_accounts() {
        if (!$this->icontact->has_credentials()) {
            return new WP_Error(
                'icontact_credentials_missing',
                'iContact API credentials are not configured.',
                ['status' => 400]
            );
        }

        $accounts = $this->icontact->get_accounts();

        if (is_wp_error($accounts)) {
            return new WP_Error(
                'icontact_api_error',
                $accounts->get_error_message(),
                ['status' => 500]
            );
        }

        return rest_ensure_response($accounts);
    }

    /**
     * Get iContact Client Folders
     *
     * @param WP_REST_Request $request REST request object
     * @return WP_REST_Response|WP_Error Response with client folders or error
     */
    public function get_client_folders(WP_REST_Request $request) {
        if (!$this->icontact->has_credentials()) {
            return new WP_Error(
                'icontact_credentials_missing',
                'iContact API credentials are not configured.',
                ['status' => 400]
            );
        }

        $account_id = $request->get_param('account_id');

        $folders = $this->icontact->get_client_folders($account_id);

        if (is_wp_error($folders)) {
            return new WP_Error(
                'icontact_api_error',
                $folders->get_error_message(),
                ['status' => 500]
            );
        }

        return rest_ensure_response($folders);
    }

    /**
     * Get iContact Lists
     *
     * @return WP_REST_Response|WP_Error Response with iContact lists or error
     */
    public function get_lists() {
        if (!$this->icontact->has_credentials()) {
            return new WP_Error(
                'icontact_credentials_missing',
                'iContact API credentials are not configured.',
                ['status' => 400]
            );
        }

        if (!$this->icontact->has_account()) {
            return new WP_Error(
                'icontact_account_missing',
                'iContact account is not configured. Please set Account ID and Client Folder ID.',
                ['status' => 400]
            );
        }

        $lists = $this->icontact->get_lists();

        if (is_wp_error($lists)) {
            return new WP_Error(
                'icontact_api_error',
                $lists->get_error_message(),
                ['status' => 500]
            );
        }

        return rest_ensure_response($lists);
    }

    /**
     * Get iContact Custom Fields
     *
     * @return WP_REST_Response|WP_Error Response with iContact custom fields or error
     */
    public function get_custom_fields() {
        if (!$this->icontact->has_credentials()) {
            return new WP_Error(
                'icontact_credentials_missing',
                'iContact API credentials are not configured.',
                ['status' => 400]
            );
        }

        if (!$this->icontact->has_account()) {
            return new WP_Error(
                'icontact_account_missing',
                'iContact account is not configured. Please set Account ID and Client Folder ID.',
                ['status' => 400]
            );
        }

        $fields = $this->icontact->get_custom_fields();

        if (is_wp_error($fields)) {
            return new WP_Error(
                'icontact_api_error',
                $fields->get_error_message(),
                ['status' => 500]
            );
        }

        return rest_ensure_response($fields);
    }
}
