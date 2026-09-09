<?php
namespace HTContactForm;

use HTContactForm\Services\FileManager;

/**
 * Ajax class
 * Handles AJAX requests for the plugin
 */
class Ajax {
    /**
     * Singleton instance
     *
     * @var object
     */
    private static $instance = null;

    /**
     * FileManager instance
     * 
     * @var FileManager
     */
    private $file_manager;
    
    /**
     * Get instance of this class
     *
     * @return object
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->file_manager = FileManager::get_instance();
        /**
         * Temporary file handlers
         */

        // Upload handler for temporary files
        add_action('wp_ajax_ht_form_temp_file_upload', [$this, 'temp_file_upload']);
        add_action('wp_ajax_nopriv_ht_form_temp_file_upload', [$this, 'temp_file_upload']);
        // Delete handler for temporary files
        add_action('wp_ajax_ht_form_temp_file_delete', [$this, 'temp_file_delete']);
        add_action('wp_ajax_nopriv_ht_form_temp_file_delete', [$this, 'temp_file_delete']);
    }
    
    /**
     * Handle Upload Temporary File on AJAX
     */
    public function temp_file_upload() {
        check_ajax_referer('ht_form_ajax_nonce', '_wpnonce');

        $file = $_FILES['ht_form_file'];
        
        // Check if file is present
        if (!isset($file)) {
            wp_send_json_error('No file uploaded.');
        }
        return $this->file_manager->temp_file_upload($file);
    }

    /**
     * Handle Delete Temporary File on AJAX
     */
    public function temp_file_delete() {
        check_ajax_referer('ht_form_ajax_nonce', '_wpnonce');
    
        $file_id = isset($_POST['ht_form_file_id']) ? sanitize_file_name(wp_unslash($_POST['ht_form_file_id'])) : '';
        if (!$file_id) {
            wp_send_json_error('No file ID provided');
        }
        if ($this->file_manager->temp_file_delete($file_id)) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Failed to delete file');
        }
    }

}