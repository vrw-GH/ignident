<?php

namespace HTContactFormAdmin\Includes\Api\Endpoints;

use HTContactFormAdmin\Includes\Models\Drafts;
use HTContactFormAdmin\Includes\Models\Form as FormModel;
use HTContactForm\Services\FileManager;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Draft API Handler Class
 *
 * Handles all REST API endpoints for form save & resume functionality including
 * saving drafts, loading drafts, and emailing resume links.
 */
class Draft {
    //-------------------------------------------------------------------------
    // PROPERTIES
    //-------------------------------------------------------------------------

    /** @var string REST API namespace */
    private $namespace = 'ht-form/v1';

    /** @var self|null Singleton instance */
    private static $instance = null;

    /** @var Drafts Drafts model instance */
    private $drafts;

    /** @var FormModel Form model instance */
    private $form;

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
        $this->drafts = Drafts::get_instance();
        $this->form = FormModel::get_instance();
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes
     *
     * Sets up all the REST API endpoints for draft management
     */
    public function register_routes() {
        $routes = [
            // Save form progress (public - uses nonce verification)
            [
                'endpoint'            => '/draft/save',
                'methods'             => 'POST',
                'callback'            => 'save_draft',
                'permission_callback' => '__return_true', // Public endpoint
            ],
            // Load draft data (public)
            [
                'endpoint'            => '/draft/(?P<key>[a-f0-9-]+)',
                'methods'             => 'GET',
                'callback'            => 'get_draft',
                'permission_callback' => '__return_true', // Public endpoint
                'args'                => [
                    'key' => [
                        'validate_callback' => [$this, 'validate_draft_key'],
                    ],
                ],
            ],
            // Email resume link (public - uses nonce verification)
            [
                'endpoint'            => '/draft/email',
                'methods'             => 'POST',
                'callback'            => 'email_draft',
                'permission_callback' => '__return_true', // Public endpoint
            ],
            // Update draft (public - uses nonce verification)
            [
                'endpoint'            => '/draft/(?P<key>[a-f0-9-]+)',
                'methods'             => 'PUT',
                'callback'            => 'update_draft',
                'permission_callback' => '__return_true', // Public endpoint
                'args'                => [
                    'key' => [
                        'validate_callback' => [$this, 'validate_draft_key'],
                    ],
                ],
            ],
            // Delete draft (admin only)
            [
                'endpoint'            => '/draft/(?P<key>[a-f0-9-]+)',
                'methods'             => 'DELETE',
                'callback'            => 'delete_draft',
                'permission_callback' => [$this, 'check_admin_permission'],
                'args'                => [
                    'key' => [
                        'validate_callback' => [$this, 'validate_draft_key'],
                    ],
                ],
            ],
            // Move temp files to draft storage (public - uses nonce verification)
            [
                'endpoint'            => '/draft/files',
                'methods'             => 'POST',
                'callback'            => 'move_files_to_draft',
                'permission_callback' => '__return_true', // Public endpoint
            ],
        ];

        foreach ($routes as $route) {
            register_rest_route($this->namespace, $route['endpoint'], [
                'methods'             => $route['methods'],
                'callback'            => [$this, $route['callback']],
                'permission_callback' => $route['permission_callback'],
                'args'                => $route['args'] ?? [],
            ]);
        }
    }

    //-------------------------------------------------------------------------
    // VALIDATION & PERMISSIONS
    //-------------------------------------------------------------------------

    /**
     * Validate draft key format (UUID)
     *
     * @param mixed $param Parameter to validate
     * @return bool Whether the parameter is a valid UUID
     */
    public function validate_draft_key($param) {
        return preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/', $param);
    }

    /**
     * Verify nonce for public endpoints
     *
     * @param WP_REST_Request $request Request object
     * @return bool|WP_Error True if valid, error otherwise
     */
    private function verify_nonce($request) {
        $nonce = $request->get_header('X-WP-Nonce');

        if (empty($nonce) || !wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error(
                'invalid_nonce',
                __('Security verification failed. Please refresh the page and try again.', 'ht-contactform'),
                ['status' => 403]
            );
        }

        return true;
    }

    /**
     * Check if current user has admin permission
     *
     * @return bool Whether user has manage_options capability
     */
    public function check_admin_permission() {
        return current_user_can('manage_options');
    }

    //-------------------------------------------------------------------------
    // MAIN OPERATIONS
    //-------------------------------------------------------------------------

    /**
     * Save form progress as draft
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with draft info or error
     */
    public function save_draft($request) {
        // Verify nonce
        $nonce_check = $this->verify_nonce($request);
        if (is_wp_error($nonce_check)) {
            return $nonce_check;
        }

        $form_id = $request->get_param('form_id');
        $form_data = $request->get_param('form_data');
        $page_url = $request->get_param('page_url');
        $expiry_days = $request->get_param('expiry_days');

        // Validate page_url if provided
        if (!empty($page_url) && !wp_http_validate_url($page_url)) {
            return new WP_Error(
                'invalid_page_url',
                __('Invalid page URL', 'ht-contactform'),
                ['status' => 400]
            );
        }

        // Validate expiry_days bounds (1-365)
        if (!empty($expiry_days)) {
            $expiry_days = absint($expiry_days);
            if ($expiry_days < 1 || $expiry_days > 365) {
                $expiry_days = 30; // Default to 30 if out of bounds
            }
        }

        // Validate form_id
        if (empty($form_id)) {
            return new WP_Error(
                'missing_form_id',
                __('Form ID is required', 'ht-contactform'),
                ['status' => 400]
            );
        }

        // Validate form exists
        $form = $this->form->get($form_id);
        if (is_wp_error($form)) {
            return new WP_Error(
                'invalid_form',
                __('Invalid form ID', 'ht-contactform'),
                ['status' => 400]
            );
        }

        // Validate form_data
        if (empty($form_data) || !is_array($form_data)) {
            return new WP_Error(
                'missing_form_data',
                __('Form data is required', 'ht-contactform'),
                ['status' => 400]
            );
        }

        $expiry = $expiry_days ?: 30;

        // If the caller supplies its own draft key + access token, update that
        // draft in place (same visitor re-saving). Drafts are NEVER looked up
        // by form_id: doing so would collapse every visitor's data into one
        // shared record and hand its key to whoever saved next. Ownership is
        // proven by possession of the per-draft access token.
        $draft_key    = $request->get_param('draft_key');
        $access_token = $request->get_param('access_token');

        if (!empty($draft_key) && $this->validate_draft_key($draft_key)) {
            $existing_draft = $this->drafts->find_by_key($draft_key);

            if (!is_wp_error($existing_draft)
                && (int) $existing_draft->form_id === absint($form_id)
                && $this->drafts->verify_access_token($existing_draft, $access_token)
            ) {
                $this->drafts->update_by_id($existing_draft->id, $form_data, $expiry);

                $resume_url = $this->drafts->build_resume_url($draft_key, $page_url, $access_token);
                $expires_at = wp_date('Y-m-d H:i:s', strtotime("+{$expiry} days"));

                return new WP_REST_Response([
                    'success'      => true,
                    'draft_key'    => $draft_key,
                    'access_token' => $access_token,
                    'resume_url'   => $resume_url,
                    'expires_at'   => $expires_at,
                ], 200);
            }
        }

        // Otherwise create a brand-new draft with its own unique key + token.
        $result = $this->drafts->create([
            'form_id'     => $form_id,
            'form_data'   => $form_data,
            'expiry_days' => $expiry,
        ]);

        if (is_wp_error($result)) {
            return $result;
        }

        // Build resume URL
        $resume_url = $this->drafts->build_resume_url($result['draft_key'], $page_url, $result['access_token']);

        return new WP_REST_Response([
            'success'      => true,
            'draft_key'    => $result['draft_key'],
            'access_token' => $result['access_token'],
            'resume_url'   => $resume_url,
            'expires_at'   => $result['expires_at'],
        ], 200);
    }

    /**
     * Get draft data by key
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with draft data or error
     */
    public function get_draft($request) {
        // Verify nonce — reads must originate from a rendered form page, not an
        // arbitrary unauthenticated cross-site request.
        $nonce_check = $this->verify_nonce($request);
        if (is_wp_error($nonce_check)) {
            return $nonce_check;
        }

        $draft_key = $request->get_param('key');
        $access_token = $request->get_param('token');

        $draft = $this->drafts->find_by_key($draft_key);

        if (is_wp_error($draft)) {
            return $draft;
        }

        // Authorize by per-draft access token (ownership check). Prevents
        // harvesting another visitor's saved PII by replaying a draft key.
        if (!$this->drafts->verify_access_token($draft, $access_token)) {
            return new WP_Error(
                'forbidden',
                __('You are not authorized to access this draft.', 'ht-contactform'),
                ['status' => 403]
            );
        }

        return new WP_REST_Response([
            'success'   => true,
            'form_id'   => $draft->form_id,
            'form_data' => $draft->form_data,
        ], 200);
    }

    /**
     * Update existing draft
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with success status or error
     */
    public function update_draft($request) {
        // Verify nonce
        $nonce_check = $this->verify_nonce($request);
        if (is_wp_error($nonce_check)) {
            return $nonce_check;
        }

        $draft_key = $request->get_param('key');
        $access_token = $request->get_param('access_token');
        $form_data = $request->get_param('form_data');

        if (empty($form_data) || !is_array($form_data)) {
            return new WP_Error(
                'missing_form_data',
                __('Form data is required', 'ht-contactform'),
                ['status' => 400]
            );
        }

        // Authorize by per-draft access token (ownership check).
        $existing_draft = $this->drafts->find_by_key($draft_key);
        if (is_wp_error($existing_draft)) {
            return $existing_draft;
        }
        if (!$this->drafts->verify_access_token($existing_draft, $access_token)) {
            return new WP_Error(
                'forbidden',
                __('You are not authorized to modify this draft.', 'ht-contactform'),
                ['status' => 403]
            );
        }

        $result = $this->drafts->update($draft_key, $form_data);

        if (is_wp_error($result)) {
            return $result;
        }

        return new WP_REST_Response([
            'success' => true,
        ], 200);
    }

    /**
     * Email resume link to user
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with success status or error
     */
    public function email_draft($request) {
        // Verify nonce
        $nonce_check = $this->verify_nonce($request);
        if (is_wp_error($nonce_check)) {
            return $nonce_check;
        }

        $draft_key = $request->get_param('draft_key');
        $access_token = $request->get_param('access_token');
        $email = $request->get_param('email');
        $page_url = $request->get_param('page_url');

        // Validate draft_key
        if (empty($draft_key) || !$this->validate_draft_key($draft_key)) {
            return new WP_Error(
                'invalid_draft_key',
                __('Invalid draft key', 'ht-contactform'),
                ['status' => 400]
            );
        }

        // Validate email
        if (empty($email) || !is_email($email)) {
            return new WP_Error(
                'invalid_email',
                __('Please provide a valid email address', 'ht-contactform'),
                ['status' => 400]
            );
        }

        // Authorize by per-draft access token — only the owner may email the
        // resume link (which itself carries the token) to an address.
        $draft = $this->drafts->find_by_key($draft_key);
        if (is_wp_error($draft)) {
            return $draft;
        }
        if (!$this->drafts->verify_access_token($draft, $access_token)) {
            return new WP_Error(
                'forbidden',
                __('You are not authorized to access this draft.', 'ht-contactform'),
                ['status' => 403]
            );
        }

        // Send email
        $result = $this->drafts->send_email($draft_key, $email, $page_url, $access_token);

        if (is_wp_error($result)) {
            return $result;
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => __('Resume link has been sent to your email', 'ht-contactform'),
        ], 200);
    }

    /**
     * Delete draft (admin only)
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with success status or error
     */
    public function delete_draft($request) {
        $draft_key = $request->get_param('key');

        $result = $this->drafts->delete($draft_key);

        if (is_wp_error($result)) {
            return $result;
        }

        return new WP_REST_Response([
            'success' => true,
        ], 200);
    }

    /**
     * Move temp files to draft storage
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with moved files or error
     */
    public function move_files_to_draft($request) {
        // Verify nonce
        $nonce_check = $this->verify_nonce($request);
        if (is_wp_error($nonce_check)) {
            return $nonce_check;
        }

        $draft_key = $request->get_param('draft_key');
        $access_token = $request->get_param('access_token');
        $file_ids = $request->get_param('file_ids');

        // Validate draft_key
        if (empty($draft_key) || !$this->validate_draft_key($draft_key)) {
            return new WP_Error(
                'invalid_draft_key',
                __('Invalid draft key', 'ht-contactform'),
                ['status' => 400]
            );
        }

        // Validate file_ids
        if (empty($file_ids) || !is_array($file_ids)) {
            return new WP_Error(
                'invalid_file_ids',
                __('Invalid file IDs', 'ht-contactform'),
                ['status' => 400]
            );
        }

        // Verify draft exists
        $draft = $this->drafts->find_by_key($draft_key);
        if (is_wp_error($draft)) {
            return $draft;
        }

        // Authorize by per-draft access token (ownership check).
        if (!$this->drafts->verify_access_token($draft, $access_token)) {
            return new WP_Error(
                'forbidden',
                __('You are not authorized to modify this draft.', 'ht-contactform'),
                ['status' => 403]
            );
        }

        // Move files to draft storage
        $file_manager = FileManager::get_instance();
        $moved_files = $file_manager->move_to_draft($file_ids, $draft_key);

        return new WP_REST_Response([
            'success'     => true,
            'moved_files' => $moved_files,
        ], 200);
    }
}
