<?php

namespace HTContactFormAdmin\Includes\Api\Endpoints\Integrations;

use HTContactForm\Integrations\ZohoCRM as ZohoCRMIntegration;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Zoho CRM API Endpoints
 *
 * Handles REST API endpoints for Zoho CRM OAuth authentication and data retrieval.
 */
class ZohoCRM {
    /** @var self|null Singleton instance */
    private static $instance = null;

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
     */
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
        add_action('admin_init', [$this, 'handle_oauth_callback']);
    }

    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Get auth URL
        register_rest_route('ht-form/v1', '/zohocrm/auth-url', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_auth_url'],
            'permission_callback' => [$this, 'permissions_check'],
        ]);

        // Disconnect
        register_rest_route('ht-form/v1', '/zohocrm/disconnect', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'disconnect'],
            'permission_callback' => [$this, 'permissions_check'],
        ]);

        // Check connection status
        register_rest_route('ht-form/v1', '/zohocrm/status', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_status'],
            'permission_callback' => [$this, 'permissions_check'],
        ]);

        // Get modules
        register_rest_route('ht-form/v1', '/zohocrm/modules', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_modules'],
            'permission_callback' => [$this, 'permissions_check'],
        ]);

        // Get module fields
        register_rest_route('ht-form/v1', '/zohocrm/fields', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_fields'],
            'permission_callback' => [$this, 'permissions_check'],
            'args' => [
                'module' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Get data centers
        register_rest_route('ht-form/v1', '/zohocrm/data-centers', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_data_centers'],
            'permission_callback' => [$this, 'permissions_check'],
        ]);
    }

    /**
     * Permission check for REST API
     *
     * @return bool
     */
    public function permissions_check() {
        return current_user_can('manage_options');
    }

    /**
     * Handle OAuth callback from Zoho
     */
    public function handle_oauth_callback() {
        if (!isset($_GET['zohocrm_auth'])) {
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized access');
        }

        // Handle error from Zoho
        if (isset($_GET['error'])) {
            $error = sanitize_text_field($_GET['error']);
            $error_description = isset($_GET['error_description']) ? sanitize_text_field($_GET['error_description']) : $error;

            wp_redirect(admin_url('admin.php?page=htcontact-form&path=integrations&zohocrm_error=' . urlencode($error_description)));
            exit;
        }

        // Handle authorization code
        if (isset($_GET['code'])) {
            $code = sanitize_text_field($_GET['code']);

            // Get settings
            $settings = get_option('ht_form_integrations', []);
            $zohocrm_settings = $settings['zohocrm'] ?? [];

            if (empty($zohocrm_settings['client_id']) || empty($zohocrm_settings['client_secret'])) {
                wp_redirect(admin_url('admin.php?page=htcontact-form&path=integrations&zohocrm_error=' . urlencode('Client ID and Secret not configured')));
                exit;
            }

            $zohocrm = new ZohoCRMIntegration(
                $zohocrm_settings['client_id'],
                $zohocrm_settings['client_secret'],
                $zohocrm_settings['data_center'] ?? 'com'
            );

            $result = $zohocrm->exchange_code_for_tokens($code);

            if (is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=htcontact-form&path=integrations&zohocrm_error=' . urlencode($result->get_error_message())));
                exit;
            }

            wp_redirect(admin_url('admin.php?page=htcontact-form&path=integrations&zohocrm_success=1'));
            exit;
        }

        wp_redirect(admin_url('admin.php?page=htcontact-form&path=integrations'));
        exit;
    }

    /**
     * Get authorization URL
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response
     */
    public function get_auth_url(WP_REST_Request $request) {
        $settings = get_option('ht_form_integrations', []);
        $zohocrm_settings = $settings['zohocrm'] ?? [];

        if (empty($zohocrm_settings['client_id']) || empty($zohocrm_settings['client_secret'])) {
            return new WP_Error('zohocrm_error', 'Please configure Client ID and Client Secret first');
        }

        $zohocrm = new ZohoCRMIntegration(
            $zohocrm_settings['client_id'],
            $zohocrm_settings['client_secret'],
            $zohocrm_settings['data_center'] ?? 'com'
        );

        return new WP_REST_Response([
            'auth_url' => $zohocrm->get_auth_url(),
            'redirect_uri' => $zohocrm->get_redirect_uri(),
        ]);
    }

    /**
     * Disconnect from Zoho CRM
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response
     */
    public function disconnect(WP_REST_Request $request) {
        $zohocrm = new ZohoCRMIntegration();
        $zohocrm->disconnect();

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Disconnected from Zoho CRM',
        ]);
    }

    /**
     * Get connection status
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response
     */
    public function get_status(WP_REST_Request $request) {
        $settings = get_option('ht_form_integrations', []);
        $zohocrm_settings = $settings['zohocrm'] ?? [];

        $zohocrm = new ZohoCRMIntegration(
            $zohocrm_settings['client_id'] ?? '',
            $zohocrm_settings['client_secret'] ?? '',
            $zohocrm_settings['data_center'] ?? 'com'
        );

        return new WP_REST_Response([
            'connected' => $zohocrm->is_connected(),
            'redirect_uri' => $zohocrm->get_redirect_uri(),
        ]);
    }

    /**
     * Get available modules
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response
     */
    public function get_modules(WP_REST_Request $request) {
        $settings = get_option('ht_form_integrations', []);
        $zohocrm_settings = $settings['zohocrm'] ?? [];

        if (empty($zohocrm_settings['client_id']) || empty($zohocrm_settings['client_secret'])) {
            return new WP_Error('zohocrm_error', 'Zoho CRM not configured');
        }

        $zohocrm = new ZohoCRMIntegration(
            $zohocrm_settings['client_id'],
            $zohocrm_settings['client_secret'],
            $zohocrm_settings['data_center'] ?? 'com'
        );

        $modules = $zohocrm->get_modules();

        if (is_wp_error($modules)) {
            return $modules;
        }

        return new WP_REST_Response($modules);
    }

    /**
     * Get fields for a module
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response
     */
    public function get_fields(WP_REST_Request $request) {
        $module = $request->get_param('module');

        if (empty($module)) {
            return new WP_Error('zohocrm_error', 'Module is required');
        }

        $settings = get_option('ht_form_integrations', []);
        $zohocrm_settings = $settings['zohocrm'] ?? [];

        if (empty($zohocrm_settings['client_id']) || empty($zohocrm_settings['client_secret'])) {
            return new WP_Error('zohocrm_error', 'Zoho CRM not configured');
        }

        $zohocrm = new ZohoCRMIntegration(
            $zohocrm_settings['client_id'],
            $zohocrm_settings['client_secret'],
            $zohocrm_settings['data_center'] ?? 'com'
        );

        $fields = $zohocrm->get_module_fields($module);

        if (is_wp_error($fields)) {
            return $fields;
        }

        return new WP_REST_Response($fields);
    }

    /**
     * Get data centers list
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response
     */
    public function get_data_centers(WP_REST_Request $request) {
        return new WP_REST_Response(ZohoCRMIntegration::get_data_centers());
    }
}
