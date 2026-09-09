<?php
namespace HTContactForm;

use HTContactFormAdmin\Includes\Models\Form;
use HTContactFormAdmin\Includes\Models\Entries;

/**
 * Cron Job class
 * 
 * Handles scheduled tasks for the HT Contact Form plugin,
 * including automatic cleanup of old form entries.
 */
class CronJob {

    /**
     * Form model instance
     * @var Form
     */
    private static $form = null;

    /**
     * Entries model instance
     * @var Entries
     */
    private static $entries = null;

    /**
     * Singleton instance
     * @var self
     */
    private static $_instance = null;

    /**
     * Batch size for large deletions
     * @var int
     */
    private $batch_size = 500;

    /**
     * Initialize a singleton instance
     * 
     * @return self The singleton instance
     */
    public static function get_instance() {
        if ( ! isset( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    /**
     * Initialize the class
     * 
     * Sets up models and registers cron events
     */
    private function __construct() {
        self::$form = Form::get_instance();
        self::$entries = Entries::get_instance();
        
        // Register a single cron event for all forms
        add_action('ht_contactform_remove_old_entries', [$this, 'process_all_forms_cleanup']);
        
        // Schedule the event if not already scheduled
        $this->scheduled_remove_old_entries();
        
        // Register deactivation hook to clean up scheduled events
        register_deactivation_hook(HTCONTACTFORM_PL_ROOT, [$this, 'unschedule_events']);
    }

    /**
     * Process cleanup for all forms that have cleanup enabled
     * 
     * Iterates through all forms and runs cleanup for those with the setting enabled
     * 
     * @return void
     */
    public function process_all_forms_cleanup() {
        try {
            $forms = self::$form->get_all();
            
            if (empty($forms)) {
                return;
            }
            
            foreach ($forms as $form) {
                $form_id = $form['id'];
                $general = $form['settings']->general;
                
                if (isset($general['settings']['delete_old_entries']) && 
                    !empty($general['settings']['delete_old_entries']) &&
                    !empty($general['settings']['delete_old_entries_after'])) {
                    
                    $delete_old_entries_after = $general['settings']['delete_old_entries_after'];
                    $this->remove_old_entries($form_id, $delete_old_entries_after);
                }
            }
        } catch (\Exception $e) {
            // Log error
            error_log('HT Contact Form - Error in cron job: ' . $e->getMessage());
        }
    }

    /**
     * Remove old entries for a specific form
     * 
     * Deletes entries older than the specified number of days
     * Uses batch processing for large deletions to avoid timeouts
     * 
     * @param int $form_id The form ID to clean up
     * @param int $delete_old_entries_after Number of days after which to delete entries
     * @return void
     */
    public function remove_old_entries($form_id, $delete_old_entries_after) {
        try {
            global $wpdb;
            
            // Allow other plugins to perform actions before deletion
            do_action('ht_contactform_before_remove_old_entries', $form_id, $delete_old_entries_after);
            
            // Calculate cutoff date
            $cutoff_date = date('Y-m-d H:i:s', strtotime("-$delete_old_entries_after days"));
            
            // Get count before deletion for logging
            $count_query = $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}ht_form_entries
                    WHERE form_id = %d AND created_at < %s",
                $form_id,
                $cutoff_date
            );
            $total_count = (int) $wpdb->get_var($count_query);
            
            if ($total_count > 0) {
                $deleted_count = 0;
                
                // For large deletions, use batch processing
                if ($total_count > $this->batch_size) {
                    $iterations = ceil($total_count / $this->batch_size);
                    
                    for ($i = 0; $i < $iterations; $i++) {
                        // Get IDs for the current batch
                        $batch_ids_query = $wpdb->prepare(
                            "SELECT id FROM {$wpdb->prefix}ht_form_entries
                                WHERE form_id = %d AND created_at < %s
                                ORDER BY id ASC LIMIT %d",
                            $form_id,
                            $cutoff_date,
                            $this->batch_size
                        );
                        
                        $batch_ids = $wpdb->get_col($batch_ids_query);
                        
                        if (empty($batch_ids)) {
                            break;
                        }
                        
                        $ids_string = implode(',', array_map('intval', $batch_ids));
                        
                        $batch_delete_query = "DELETE FROM {$wpdb->prefix}ht_form_entries
                            WHERE id IN ($ids_string)";
                        
                        $batch_result = $wpdb->query($batch_delete_query);
                        
                        if ($batch_result !== false) {
                            $deleted_count += $batch_result;
                        }
                    }
                } else {
                    // For smaller deletions, use a single query
                    $delete_query = $wpdb->prepare(
                        "DELETE FROM {$wpdb->prefix}ht_form_entries
                            WHERE form_id = %d AND created_at < %s",
                        $form_id,
                        $cutoff_date
                    );
                    
                    $result = $wpdb->query($delete_query);
                    
                    if ($result !== false) {
                        $deleted_count = $result;
                    }
                }
                
                if ($deleted_count > 0) {
                    // Invalidate cache for this form
                    $this->invalidate_form_cache($form_id);
                    
                    error_log(sprintf(
                        'HT Contact Form - Deleted %d old entries for form #%d (older than %d days)',
                        $deleted_count,
                        $form_id,
                        $delete_old_entries_after
                    ));
                    
                    // Allow other plugins to perform actions after deletion
                    do_action('ht_contactform_after_remove_old_entries', $form_id, $deleted_count, $delete_old_entries_after);
                }
            }
        } catch (\Exception $e) {
            error_log('HT Contact Form - Error removing old entries: ' . $e->getMessage());
        }
    }

    /**
     * Invalidate form cache after entry deletion
     * 
     * Clears transients related to form entry counts
     * 
     * @param int $form_id The form ID to invalidate cache for
     * @return void
     */
    private function invalidate_form_cache($form_id) {
        // Delete form data transient
        $transient_key = 'ht_form_data_' . $form_id;
        delete_transient($transient_key);
    }

    /**
     * Schedule the daily cleanup task
     * 
     * @return void
     */
    public function scheduled_remove_old_entries() {
        try {
            if (!wp_next_scheduled('ht_contactform_remove_old_entries')) {
                $time = strtotime('00:00 today');
                wp_schedule_event($time, 'daily', 'ht_contactform_remove_old_entries');
                
                error_log('HT Contact Form - Scheduled daily cleanup task');
            }
        } catch (\Exception $e) {
            error_log('HT Contact Form - Error scheduling cleanup task: ' . $e->getMessage());
        }
    }
    
    /**
     * Unschedule all cron events on plugin deactivation
     * 
     * @return void
     */
    public function unschedule_events() {
        $timestamp = wp_next_scheduled('ht_contactform_remove_old_entries');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'ht_contactform_remove_old_entries');
        }
    }
}