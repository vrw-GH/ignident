<?php

namespace HTContactFormAdmin\Includes\Api\Endpoints;

use HTContactFormAdmin\Includes\Models\Entries;
use HTContactFormAdmin\Includes\Models\Form as FormModel;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Form Entries API Handler Class
 * 
 * Handles all REST API endpoints for form entries management including retrieval,
 * updating status, deletion, and export functionality.
 */
class Entry {
    //-------------------------------------------------------------------------
    // PROPERTIES
    //-------------------------------------------------------------------------
    
    /** @var string REST API namespace */
    private $namespace = 'ht-form/v1';

    /** @var self|null Singleton instance */
    private static $instance = null;
    
    /** @var Entries Entries model instance */
    private $entries;
    
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
        $this->entries = Entries::get_instance();
        $this->form = FormModel::get_instance();
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes
     * 
     * Sets up all the REST API endpoints for form entries management
     */
    public function register_routes() {
        $routes = [
            // Get all entries with filtering and pagination
            [
                'endpoint' => '/entries',
                'methods'  => 'GET',
                'callback' => 'get_entries'
            ],
            // Get single entry
            [
                'endpoint' => '/entries/(?P<id>\d+)',
                'methods'  => 'GET',
                'callback' => 'get_entry',
                'args'     => ['id' => ['validate_callback' => [$this, 'validate_numeric_id']]]
            ],
            // Mark entry as read
            [
                'endpoint' => '/entries/(?P<id>\d+)/mark-as-read',
                'methods'  => 'PUT',
                'callback' => 'mark_as_read',
                'args'     => ['id' => ['validate_callback' => [$this, 'validate_numeric_id']]]
            ],
            // Mark entry as unread
            [
                'endpoint' => '/entries/(?P<id>\d+)/mark-as-unread',
                'methods'  => 'PUT',
                'callback' => 'mark_as_unread',
                'args'     => ['id' => ['validate_callback' => [$this, 'validate_numeric_id']]]
            ],
            // Mark entry as delete
            [
                'endpoint' => '/entries/(?P<id>\d+)/mark-as-delete',
                'methods'  => 'PUT',
                'callback' => 'mark_as_delete',
                'args'     => ['id' => ['validate_callback' => [$this, 'validate_numeric_id']]]
            ],
            // Bulk mark as read
            [
                'endpoint' => '/entries/bulk-mark-as',
                'methods'  => 'PUT',
                'callback' => 'bulk_mark_as'
            ],
            // Bulk delete
            [
                'endpoint' => '/entries/bulk-delete',
                'methods'  => 'DELETE',
                'callback' => 'bulk_delete'
            ],
            // Delete entry
            [
                'endpoint' => '/entries/(?P<id>\d+)',
                'methods'  => 'DELETE',
                'callback' => 'delete_entry',
                'args'     => ['id' => ['validate_callback' => [$this, 'validate_numeric_id']]]
            ],
            // Export entries to CSV
            [
                'endpoint' => '/entries/export',
                'methods'  => 'GET',
                'callback' => 'export_entries'
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
    // MAIN OPERATIONS
    //-------------------------------------------------------------------------
    
    /**
     * Get all entries with filtering and pagination
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with entries or error
     */
    public function get_entries($request) {
        $args = [];
        
        // Extract query parameters
        $form_id = $request->get_param('form_id');
        if (!empty($form_id)) {
            $args['form_id'] = absint($form_id);
        }
        
        $status = $request->get_param('status');
        if (!empty($status) && in_array($status, ['read', 'unread'])) {
            $args['status'] = sanitize_text_field($status);
        }
        
        $search = $request->get_param('search');
        if (!empty($search)) {
            $args['search'] = sanitize_text_field($search);
        }
        
        $page = $request->get_param('page');
        if (!empty($page)) {
            $args['page'] = absint($page);
        }
        
        $per_page = $request->get_param('per_page');
        if (!empty($per_page)) {
            // Allow negative values like -1 to indicate "all entries"
            $args['per_page'] = intval($per_page);
        }
        
        $orderby = $request->get_param('orderby');
        if (!empty($orderby)) {
            $args['orderby'] = sanitize_text_field($orderby);
        }
        
        $order = $request->get_param('order');
        if (!empty($order) && in_array(strtoupper($order), ['ASC', 'DESC'])) {
            $args['order'] = strtoupper(sanitize_text_field($order));
        }
        
        // Get entries from database
        $result = $this->entries->all($args);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return new WP_REST_Response($result, 200);
    }
    
    /**
     * Get a single entry by ID
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with entry or error
     */
    public function get_entry($request) {
        $id = $request->get_param('id');
        $entry = $this->entries->find($id);
        
        if (is_wp_error($entry)) {
            return $entry;
        }
        
        return new WP_REST_Response($entry, 200);
    }
    
    /**
     * Mark an entry as read
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with updated entry or error
     */
    public function mark_as_read($request) {
        $id = $request->get_param('id');
        $result = $this->entries->mark_as_read($id);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        // Get updated entry
        $entry = $this->entries->find($id);
        
        return new WP_REST_Response($entry, 200);
    }
    
    /**
     * Mark an entry as unread
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with updated entry or error
     */
    public function mark_as_unread($request) {
        $id = $request->get_param('id');
        $result = $this->entries->mark_as_unread($id);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        // Get updated entry
        $entry = $this->entries->find($id);
        
        return new WP_REST_Response($entry, 200);
    }
    
    /**
     * Mark an entry as deleted
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with updated entry or error
     */
    public function mark_as_delete($request) {
        $id = $request->get_param('id');
        $result = $this->entries->mark_as_delete($id);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        // Get updated entry
        $entry = $this->entries->find($id);
        
        return new WP_REST_Response($entry, 200);
    }
    
    /**
     * Bulk mark entries as read
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with success status or error
     */
    public function bulk_mark_as($request) {
        $entry_ids = $request->get_param('ids');
        $status = $request->get_param('status');
        
        if (empty($entry_ids) || !is_array($entry_ids)) {
            return new WP_Error(
                'missing_ids',
                __('Entry IDs are required', 'ht-contactform'),
                ['status' => 400]
            );
        }
        
        // Make sure all IDs are numeric
        $entry_ids = array_map('absint', $entry_ids);
        
        $result = $this->entries->bulk_mark_as($entry_ids, $status);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return new WP_REST_Response(['success' => true], 200);
    }

    public function bulk_delete($request) {
        // For DELETE requests, we need to get the data from the parsed body
        $params = $request->get_json_params();
        $ids = isset($params['ids']) ? $params['ids'] : null;
        
        if (empty($ids) || !is_array($ids)) {
            return new WP_Error(
                'missing_ids',
                __('Entry IDs are required', 'ht-contactform'),
                ['status' => 400]
            );
        }
        
        // Make sure all IDs are numeric
        $ids = array_map('absint', $ids);

        foreach ($ids as $id) {
            $result = $this->entries->delete($id);
            
            if (is_wp_error($result)) {
                return $result;
            }
        }
        
        return new WP_REST_Response(['success' => true], 200);
    }
    
    /**
     * Delete an entry
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with success status or error
     */
    public function delete_entry($request) {
        $id = $request->get_param('id');
        $result = $this->entries->delete($id);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return new WP_REST_Response(['success' => true], 200);
    }
    
    /**
     * Export entries in various formats (CSV, Excel, JSON)
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with data or error
     */
    public function export_entries($request) {
        $form_id = $request->get_param('form_id');
        $format = $request->get_param('format') ?: 'csv'; // Default to CSV if not specified
        
        if (empty($form_id)) {
            return new WP_Error(
                'missing_form_id',
                __('Form ID is required', 'ht-contactform'),
                ['status' => 400]
            );
        }
        
        // Check if form exists
        $form = $this->form->get($form_id);
        if (is_wp_error($form)) {
            return $form;
        }
        
        // Generate content based on format
        $content = $this->entries->export($form_id, $format);
        
        // Generate filename
        $filename = str_replace(' ', '-', sanitize_title($form['title'])) . '-entries-' . gmdate('Y-m-d') . '.' . $format;
        
        // Set appropriate headers based on format
        switch (strtolower($format)) {
            case 'json':
                header('Content-Type: application/json; charset=utf-8');
                break;
                
            case 'xlsx':
                header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                break;
                
            case 'ods':
                header('Content-Type: application/vnd.oasis.opendocument.spreadsheet; charset=utf-8');
                break;
                
            case 'csv':
            default:
                header('Content-Type: text/csv; charset=utf-8');
                break;
        }
        
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Output content directly
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $content;
        exit;
    }
}