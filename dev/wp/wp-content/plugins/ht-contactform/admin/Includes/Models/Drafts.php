<?php

namespace HTContactFormAdmin\Includes\Models;

use WP_Error;
use HTContactForm\Services\FileManager;

/**
 * Drafts Model Class
 *
 * Handles all form draft operations for save & resume functionality including
 * storage, retrieval, cleanup, and email delivery of resume links.
 */
class Drafts {
    //-------------------------------------------------------------------------
    // PROPERTIES
    //-------------------------------------------------------------------------

    /** @var string DB table name for form drafts */
    private $table;

    /** @var self|null Singleton instance */
    private static $instance = null;

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
     */
    public function __construct() {
        global $wpdb;
        $this->table = "{$wpdb->prefix}ht_form_drafts";

        // Create table if it doesn't exist
        $this->maybe_create_table();

        // Schedule cleanup cron
        $this->schedule_cleanup();
    }

    /**
     * Create the drafts table if it doesn't exist
     */
    private function maybe_create_table() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$this->table}'") != $this->table) {
            $sql = "CREATE TABLE {$this->table} (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                draft_key varchar(64) NOT NULL,
                access_token varchar(128) DEFAULT NULL,
                form_id bigint(20) NOT NULL,
                form_data longtext NOT NULL,
                email varchar(255) DEFAULT NULL,
                expires_at datetime NOT NULL,
                created_at datetime NOT NULL,
                updated_at datetime NOT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY draft_key (draft_key),
                KEY form_id (form_id),
                KEY expires_at (expires_at)
            ) $charset_collate;";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);

            return;
        }

        // Migration: add access_token column to existing installs (pre-2.9.3)
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $has_token = $wpdb->get_var("SHOW COLUMNS FROM {$this->table} LIKE 'access_token'");
        if (!$has_token) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
            $wpdb->query("ALTER TABLE {$this->table} ADD COLUMN access_token varchar(128) DEFAULT NULL AFTER draft_key");
        }
    }

    /**
     * Schedule cleanup cron job for expired drafts
     */
    public function schedule_cleanup() {
        if (!wp_next_scheduled('ht_form_drafts_cleanup')) {
            wp_schedule_event(time(), 'daily', 'ht_form_drafts_cleanup');
        }
        add_action('ht_form_drafts_cleanup', [$this, 'cleanup_expired']);
    }

    /**
     * Remove expired drafts from database and their associated files
     */
    public function cleanup_expired() {
        global $wpdb;

        // Get draft keys before deleting (for file cleanup)
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $expired_drafts = $wpdb->get_col(
            "SELECT draft_key FROM {$this->table} WHERE expires_at < NOW()"
        );

        if (empty($expired_drafts)) {
            return;
        }

        // Delete files for each expired draft, track which ones succeeded
        $file_manager = FileManager::get_instance();
        $successfully_cleaned = [];

        foreach ($expired_drafts as $draft_key) {
            if ($file_manager->delete_draft_files($draft_key)) {
                $successfully_cleaned[] = $draft_key;
            } else {
                error_log("HT ContactForm: Failed to delete draft files for: {$draft_key}");
            }
        }

        // Only delete DB records for drafts whose files were successfully deleted
        if (!empty($successfully_cleaned)) {
            $placeholders = implode(',', array_fill(0, count($successfully_cleaned), '%s'));
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$this->table} WHERE draft_key IN ({$placeholders})",
                    $successfully_cleaned
                )
            );
        }
    }

    //-------------------------------------------------------------------------
    // MAIN CRUD OPERATIONS
    //-------------------------------------------------------------------------

    /**
     * Generate a unique draft key
     *
     * @return string UUID-like unique key
     */
    private function generate_draft_key() {
        return wp_generate_uuid4();
    }

    /**
     * Generate a high-entropy access token (the per-draft owner capability)
     *
     * @return string 64-char random token
     */
    private function generate_access_token() {
        return wp_generate_password(64, false, false);
    }

    /**
     * Hash an access token for storage / comparison
     *
     * @param string $token Raw access token
     * @return string Hashed token
     */
    private function hash_access_token($token) {
        return hash_hmac('sha256', $token, wp_salt('auth'));
    }

    /**
     * Verify a raw access token against a draft row.
     *
     * Legacy drafts created before access tokens existed have an empty
     * access_token and are grandfathered (token check skipped) so previously
     * issued resume links keep working until they expire.
     *
     * @param object|array $draft Draft row (must include access_token)
     * @param string       $token Raw access token supplied by the caller
     * @return bool True if authorized to access the draft
     */
    public function verify_access_token($draft, $token) {
        $stored = is_object($draft) ? ($draft->access_token ?? '') : ($draft['access_token'] ?? '');

        // Grandfather legacy tokenless drafts.
        if (empty($stored)) {
            return true;
        }

        if (empty($token) || !is_string($token)) {
            return false;
        }

        return hash_equals($stored, $this->hash_access_token($token));
    }

    /**
     * Create a new draft
     *
     * @param array $data {
     *     @type int    $form_id     Form ID
     *     @type array  $form_data   Form data to save
     *     @type string $email       Optional email for link delivery
     *     @type int    $expiry_days Days until draft expires (default: 30)
     * }
     * @return array|WP_Error Draft info including key and URL, or error
     */
    public function create($data) {
        global $wpdb;

        // Validate required data
        if (empty($data['form_id']) || empty($data['form_data'])) {
            return new WP_Error(
                'missing_data',
                __('Form ID and form data are required', 'ht-contactform'),
                ['status' => 400]
            );
        }

        $draft_key = $this->generate_draft_key();
        $access_token = $this->generate_access_token();
        $expiry_days = isset($data['expiry_days']) ? absint($data['expiry_days']) : 30;
        $expires_at = wp_date('Y-m-d H:i:s', strtotime("+{$expiry_days} days"));
        $now = wp_date('Y-m-d H:i:s');

        // Prepare draft data
        $draft = [
            'draft_key'    => $draft_key,
            'access_token' => $this->hash_access_token($access_token),
            'form_id'      => absint($data['form_id']),
            'form_data'    => wp_json_encode($data['form_data']),
            'email'        => !empty($data['email']) ? sanitize_email($data['email']) : null,
            'expires_at'   => $expires_at,
            'created_at'   => $now,
            'updated_at'   => $now,
        ];

        // Insert into database
        $result = $wpdb->insert($this->table, $draft);

        if ($result === false) {
            error_log("HT ContactForm Draft DB Error: {$wpdb->last_error}");
            return new WP_Error(
                'db_error',
                __('Failed to save form draft', 'ht-contactform'),
                ['status' => 500]
            );
        }

        return [
            'id'           => $wpdb->insert_id,
            'draft_key'    => $draft_key,
            'access_token' => $access_token,
            'expires_at'   => $expires_at,
        ];
    }

    /**
     * Find a draft by its unique key
     *
     * @param string $draft_key The unique draft key
     * @return object|WP_Error Draft object or error
     */
    public function find_by_key($draft_key) {
        global $wpdb;

        $draft = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE draft_key = %s AND expires_at > NOW()",
                $draft_key
            ),
            ARRAY_A
        );

        if (!$draft) {
            return new WP_Error(
                'draft_not_found',
                __('Draft not found or has expired', 'ht-contactform'),
                ['status' => 404]
            );
        }

        // Parse the JSON form data
        $draft['form_data'] = json_decode($draft['form_data'], true);

        return (object) $draft;
    }

    /**
     * Find a draft by ID
     *
     * @param int $id Draft ID
     * @return object|WP_Error Draft object or error
     */
    public function find($id) {
        global $wpdb;

        $draft = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id),
            ARRAY_A
        );

        if (!$draft) {
            return new WP_Error(
                'draft_not_found',
                __('Draft not found', 'ht-contactform'),
                ['status' => 404]
            );
        }

        // Parse the JSON form data
        $draft['form_data'] = json_decode($draft['form_data'], true);

        return (object) $draft;
    }

    /**
     * Update an existing draft
     *
     * @param string $draft_key The unique draft key
     * @param array  $form_data Updated form data
     * @return bool|WP_Error True on success or error
     */
    public function update($draft_key, $form_data) {
        global $wpdb;

        // Check if draft exists
        $draft = $this->find_by_key($draft_key);
        if (is_wp_error($draft)) {
            return $draft;
        }

        // Update the draft
        $result = $wpdb->update(
            $this->table,
            [
                'form_data'  => wp_json_encode($form_data),
                'updated_at' => current_time('mysql'),
            ],
            ['draft_key' => $draft_key]
        );

        if ($result === false) {
            error_log("HT ContactForm Draft DB Error: {$wpdb->last_error}");
            return new WP_Error(
                'update_failed',
                __('Failed to update draft', 'ht-contactform'),
                ['status' => 500]
            );
        }

        if ($result === 0) {
            return new WP_Error(
                'draft_not_found',
                __('Draft not found or no changes made', 'ht-contactform'),
                ['status' => 404]
            );
        }

        return true;
    }

    /**
     * Delete a draft by its key
     *
     * @param string $draft_key The unique draft key
     * @return bool|WP_Error True on success or error
     */
    public function delete($draft_key) {
        global $wpdb;

        // Delete associated files first
        $file_manager = FileManager::get_instance();
        $file_manager->delete_draft_files($draft_key);

        $result = $wpdb->delete(
            $this->table,
            ['draft_key' => $draft_key],
            ['%s']
        );

        if ($result === false) {
            error_log("HT ContactForm Draft DB Error: {$wpdb->last_error}");
            return new WP_Error(
                'delete_failed',
                __('Failed to delete draft', 'ht-contactform'),
                ['status' => 500]
            );
        }

        return true;
    }

    /**
     * Delete all drafts for a form
     *
     * @param int $form_id Form ID
     * @return bool True on success
     */
    public function delete_by_form_id($form_id) {
        global $wpdb;

        $result = $wpdb->delete(
            $this->table,
            ['form_id' => $form_id],
            ['%d']
        );

        return $result !== false;
    }

    /**
     * Get count of drafts for a form
     *
     * @param int $form_id Form ID
     * @return int Count of drafts
     */
    public function count_by_form($form_id) {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table} WHERE form_id = %d AND expires_at > NOW()",
                $form_id
            )
        );
    }

    /**
     * Find the most recent non-expired draft for a form
     *
     * @param int $form_id Form ID
     * @return array|null Draft data or null if not found
     */
    public function find_by_form_id($form_id) {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $draft = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE form_id = %d AND expires_at > NOW() ORDER BY updated_at DESC LIMIT 1",
                $form_id
            ),
            ARRAY_A
        );

        return $draft ?: null;
    }

    /**
     * Update draft by ID with new data and extended expiry
     *
     * @param int   $id          Draft ID
     * @param array $form_data   New form data
     * @param int   $expiry_days Days until expiry
     * @return bool True on success
     */
    public function update_by_id($id, $form_data, $expiry_days = 30) {
        global $wpdb;

        $expires_at = wp_date('Y-m-d H:i:s', strtotime("+{$expiry_days} days"));

        $result = $wpdb->update(
            $this->table,
            [
                'form_data'  => wp_json_encode($form_data),
                'expires_at' => $expires_at,
                'updated_at' => wp_date('Y-m-d H:i:s'),
            ],
            ['id' => $id]
        );

        return $result !== false;
    }

    //-------------------------------------------------------------------------
    // EMAIL OPERATIONS
    //-------------------------------------------------------------------------

    /**
     * Send resume link to email
     *
     * @param string $draft_key The draft key
     * @param string $email     Email address to send to
     * @param string $page_url  The page URL where form is displayed
     * @return bool|WP_Error True on success or error
     */
    public function send_email($draft_key, $email, $page_url, $access_token = '') {
        // Validate email
        if (!is_email($email)) {
            return new WP_Error(
                'invalid_email',
                __('Please provide a valid email address', 'ht-contactform'),
                ['status' => 400]
            );
        }

        // Check if draft exists
        $draft = $this->find_by_key($draft_key);
        if (is_wp_error($draft)) {
            return $draft;
        }

        // Build resume URL (includes the access token so the emailed link can
        // authorize the read back to its owner)
        $resume_url = $this->build_resume_url($draft_key, $page_url, $access_token);

        // Get site name
        $site_name = get_bloginfo('name');

        // Email subject
        $subject = sprintf(
            /* translators: %s: Site name */
            __('[%s] Your Form Progress Has Been Saved', 'ht-contactform'),
            $site_name
        );

        // Email body
        $message = sprintf(
            /* translators: 1: Site name, 2: Resume URL, 3: Expiry date */
            __(
                "Hello,\n\n" .
                "Your form progress has been saved. You can continue filling out the form by clicking the link below:\n\n" .
                "%1\$s\n\n" .
                "This link will expire on %2\$s.\n\n" .
                "If you did not request this email, you can safely ignore it.\n\n" .
                "Best regards,\n%3\$s",
                'ht-contactform'
            ),
            $resume_url,
            wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($draft->expires_at)),
            $site_name
        );

        // Send email
        $sent = wp_mail($email, $subject, $message);

        if (!$sent) {
            return new WP_Error(
                'email_failed',
                __('Failed to send email. Please try again.', 'ht-contactform'),
                ['status' => 500]
            );
        }

        // Update draft with email
        global $wpdb;
        $wpdb->update(
            $this->table,
            ['email' => sanitize_email($email)],
            ['draft_key' => $draft_key]
        );

        return true;
    }

    //-------------------------------------------------------------------------
    // UTILITY METHODS
    //-------------------------------------------------------------------------

    /**
     * Build resume URL from draft key and page URL
     *
     * @param string $draft_key Draft key
     * @param string $page_url  Page URL (optional, uses current URL if not provided)
     * @return string Full resume URL
     */
    public function build_resume_url($draft_key, $page_url = '', $access_token = '') {
        if (empty($page_url)) {
            $page_url = home_url(add_query_arg([]));
        }

        // Remove any existing resume params
        $page_url = remove_query_arg(['ht_form_resume', 'ht_form_token'], $page_url);

        $args = ['ht_form_resume' => $draft_key];
        if (!empty($access_token)) {
            $args['ht_form_token'] = $access_token;
        }

        return add_query_arg($args, $page_url);
    }
}
