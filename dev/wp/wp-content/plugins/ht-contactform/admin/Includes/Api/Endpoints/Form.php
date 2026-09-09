<?php

namespace HTContactFormAdmin\Includes\Api\Endpoints;

use HTContactFormAdmin\Includes\Models\Form as FormModel;
use HTContactFormAdmin\Includes\Models\Entries;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use WP_Post;

/**
 * Form API Handler Class
 * 
 * Handles all REST API endpoints for form management including CRUD operations,
 * form duplication, and form export functionality.
 */
class Form {
    //-------------------------------------------------------------------------
    // PROPERTIES
    //-------------------------------------------------------------------------
    
    /** @var string REST API namespace */
    private $namespace = 'ht-form/v1';

    /** @var string Custom post type name for forms */
    private $post_type = 'ht_form';

    /** @var self|null Singleton instance */
    private static $instance = null;
    
    /** @var FormModel Form model instance */
    private $form;
    /** @var Entries Entries model instance */
    private $entries;
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
        $this->form = FormModel::get_instance();
        $this->entries = Entries::get_instance();
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes
     * 
     * Sets up all the REST API endpoints for form management
     */
    public function register_routes() {
        $routes = [
            // Get all forms
            [
                'endpoint' => '/forms',
                'methods'  => 'GET',
                'callback' => 'get_forms'
            ],
            // Create new form
            [
                'endpoint' => '/forms',
                'methods'  => 'POST',
                'callback' => 'create_form'
            ],
            // Get single form
            [
                'endpoint' => '/forms/(?P<id>\d+)',
                'methods'  => 'GET',
                'callback' => 'get_form',
                'args'     => ['id' => ['validate_callback' => [$this, 'validate_numeric_id']]]
            ],
            // Update form
            [
                'endpoint' => '/forms/(?P<id>\d+)',
                'methods'  => 'PUT',
                'callback' => 'update_form',
                'args'     => ['id' => ['validate_callback' => [$this, 'validate_numeric_id']]]
            ],
            // Delete form
            [
                'endpoint' => '/forms/(?P<id>\d+)',
                'methods'  => 'DELETE',
                'callback' => 'delete_form',
                'args'     => ['id' => ['validate_callback' => [$this, 'validate_numeric_id']]]
            ],
            // Duplicate form
            [
                'endpoint' => '/forms/(?P<id>\d+)/duplicate',
                'methods'  => 'POST',
                'callback' => 'duplicate_form',
                'args'     => ['id' => ['validate_callback' => [$this, 'validate_numeric_id']]]
            ],
            // Export form
            [
                'endpoint' => '/forms/(?P<id>\d+)/export',
                'methods'  => 'GET',
                'callback' => 'export_form',
                'args'     => ['id' => ['validate_callback' => [$this, 'validate_numeric_id']]]
            ],
            // Get form integrations
            [
                'endpoint' => '/forms/(?P<id>\d+)/integrations',
                'methods'  => 'GET',
                'callback' => 'get_form_integrations',
                'args'     => ['id' => ['validate_callback' => [$this, 'validate_numeric_id']]]
            ],
            // Update or Create form integrations
            [
                'endpoint' => '/forms/(?P<id>\d+)/integrations',
                'methods'  => 'POST',
                'callback' => 'update_form_integrations',
                'args'     => ['id' => ['validate_callback' => [$this, 'validate_numeric_id']]]
            ],
            // Delete form integrations
            [
                'endpoint' => '/forms/(?P<id>\d+)/integrations/(?P<integration_id>\d+)',
                'methods'  => 'DELETE',
                'callback' => 'delete_form_integrations',
                'args'     => ['id' => ['validate_callback' => [$this, 'validate_numeric_id']], 'integration_id' => ['validate_callback' => [$this, 'validate_numeric_id']]]
            ]
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
     * Validate that the provided ID is numeric
     * 
     * @param mixed $param Parameter to validate
     * @return bool Whether the parameter is numeric
     */
    public function validate_numeric_id($param) {
        return is_numeric($param);
    }

    /**
     * Check if current user has permission to access endpoints
     * 
     * @return bool Whether user has manage_options capability
     */
    public function check_permission() {
        return current_user_can('manage_options');
    }

    //-------------------------------------------------------------------------
    // MAIN CRUD OPERATIONS
    //-------------------------------------------------------------------------
    
    /**
     * Get all forms
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response with all forms
     */
    public function get_forms($request) {
        $search_query = $request['search'] ?? '';
        $forms = $this->form->get_all($search_query);
        
        return rest_ensure_response($forms);
    }

    /**
     * Get single form
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with form data or error
     */
    public function get_form($request) {
        $form_id = $request['id'];
        $form = $this->form->get($form_id);
        
        if (is_wp_error($form)) {
            return $form;
        }
        
        return new WP_REST_Response($form, 200);
    }

    /**
     * Create new form
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with created form data or error
     */
    public function create_form($request) {
        $form_data = $request->get_json_params() ?: [
            'title'    => __('Untitled Form', 'ht-contactform'),
            'fields'   => [],
            'settings' => (object) []
        ];
        
        $form = $this->form->create($form_data);
        
        if (is_wp_error($form)) {
            return $form;
        }
        
        return new WP_REST_Response($form, 201);
    }

    /**
     * Update form
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with updated form data or error
     */
    public function update_form($request) {
        $form_id = $request['id'];
        $form_data = $request->get_json_params();
        
        if (empty($form_data)) {
            return new WP_Error(
                'invalid_data',
                __('Form data is required', 'ht-contactform'),
                ['status' => 400]
            );
        }
        
        $form = $this->form->update($form_id, $form_data);
        
        if (is_wp_error($form)) {
            return $form;
        }
        
        return new WP_REST_Response($form, 200);
    }

    /**
     * Delete form
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with success status or error
     */
    public function delete_form($request) {
        $form_id = $request['id'];
        
        $result = $this->form->delete($form_id);
        
        if (is_wp_error($result)) {
            return $result;
        }

        $result = $this->entries->delete_by_form_id($form_id);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return new WP_REST_Response([
            'message' => __('Form and its entries deleted successfully', 'ht-contactform')
        ], 200);
    }

    //-------------------------------------------------------------------------
    // ADDITIONAL OPERATIONS
    //-------------------------------------------------------------------------
    
    /**
     * Duplicate form
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with duplicated form data or error
     */
    public function duplicate_form($request) {
        $form_id = $request['id'];
        
        $form = $this->form->duplicate($form_id);
        
        if (is_wp_error($form)) {
            return $form;
        }
        
        $response_data = [
            'id'        => $form['id'],
            'title'     => $form['title'],
            'shortcode' => $form['shortcode'],
            'fields'    => $form['fields'],
            'settings'  => $form['settings'],
            'date'      => $form['created_at'],
            'entries'   => [
                'read'    => 0,
                'unread'  => 0,
                'total'   => 0
            ]
        ];
        
        return rest_ensure_response($response_data);
    }

    /**
     * Export form
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with exported form data or error
     */
    public function export_form($request) {
        $form_id = $request['id'];
        
        $export_data = $this->form->export($form_id);
        
        if (is_wp_error($export_data)) {
            return $export_data;
        }
        
        return rest_ensure_response($export_data);
    }

    /**
     * Get form integrations
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with exported form data or error
     */
    public function get_form_integrations($request) {
        $form_id = $request['id'];
        
        $integrations = $this->form->get_integrations($form_id);
        
        if (is_wp_error($integrations)) {
            return $integrations;
        }
        
        return rest_ensure_response($integrations);
    }

    /**
     * Update or create form integrations
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with exported form data or error
     */
    public function update_form_integrations($request) {
        $form_id = $request['id'];  
        $integration = $request['integration'];
        
        $integration = $this->form->update_integrations($form_id, $integration);
        
        if (is_wp_error($integration)) {
            return $integration;
        }
        
        return rest_ensure_response($integration);
    }
    /**
     * Delete form integrations
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with exported form data or error
     */
    public function delete_form_integrations($request) {
        $form_id = $request['id'];  
        $integration_id = $request['integration_id'];
        
        $integration = $this->form->delete_integrations($form_id, $integration_id);
        
        if (is_wp_error($integration)) {
            return $integration;
        }
        
        return rest_ensure_response($integration);
    }
}