<?php

namespace HTContactFormAdmin\Includes\Models;

use WP_Error;
use OpenSpout\Writer\Common\Creator\WriterEntityFactory;
use HTContactFormAdmin\Includes\Models\Form;

/**
 * Entries Model Class
 * 
 * Handles all form submission entries operations including retrieval,
 * creation, updating, deletion, and search functionality.
 */
class Entries {
    //-------------------------------------------------------------------------
    // PROPERTIES
    //-------------------------------------------------------------------------
    
    /** @var string DB table name for form entries */
    private $table;

    /** @var Form|null Form model instance */
    private $form;

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
        $this->table = "{$wpdb->prefix}ht_form_entries";
        
        // Create table if it doesn't exist
        $this->maybe_create_table();
    }
    
    /**
     * Create the submissions table if it doesn't exist
     */
    private function maybe_create_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$this->table}'") != $this->table) {
            $sql = "CREATE TABLE {$this->table} (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                form_id bigint(20) NOT NULL,
                form_data longtext NOT NULL,
                status varchar(50) DEFAULT 'unread' NOT NULL,
                viewed_at datetime DEFAULT NULL,
                source_url varchar(255) NOT NULL,
                user_id bigint(20) DEFAULT NULL,
                browser varchar(100) NOT NULL,
                device varchar(100) NOT NULL,
                ip_address varchar(100) DEFAULT NULL,
                created_at datetime NOT NULL,
                updated_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY form_id (form_id),
                KEY status (status),
                KEY created_at (created_at)
            ) $charset_collate;";
            
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }
    }
    
    //-------------------------------------------------------------------------
    // MAIN CRUD OPERATIONS
    //-------------------------------------------------------------------------
    
    /**
     * Get all entries with pagination
     * 
     * @param array $args {
     *     Optional. Arguments to retrieve entries.
     *     @type int    $form_id     Filter by form ID
     *     @type string $status      Filter by status ('read', 'unread', 'all')
     *     @type string $search      Search in entry content
     *     @type int    $page        Page number
     *     @type int    $per_page    Items per page (set to -1 for all items)
     *     @type string $orderby     Order by field (default: 'created_at')
     *     @type string $order       ASC or DESC (default: 'DESC')
     * }
     * @return array {
     *     @type array  $items       Array of entries
     *     @type int    $total       Total number of entries
     *     @type int    $total_pages Total number of pages
     * }
     */
    public function all($args = []) {
        global $wpdb;

        $form = Form::get_instance();
        
        // Check if per_page was explicitly provided
        $per_page_provided = array_key_exists('per_page', $args);
        
        // Default arguments
        $defaults = [
            'form_id'   => 0,
            'status'    => 'all',
            'search'    => '',
            'page'      => 1,
            'per_page'  => $per_page_provided ? $per_page_provided : -1, // If not provided, show all entries
            'orderby'   => 'created_at',
            'order'     => 'DESC'
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        // Ensure valid values
        $args['page'] = max(1, intval($args['page']));
        
        // Special handling for per_page: -1 means all entries
        $show_all = $args['per_page'] < 0;
        if (!$show_all) {
            $args['per_page'] = max(1, intval($args['per_page']));
        }
        
        // Build WHERE clause
        $where = [];
        $where_format = [];
        
        if ($args['form_id']) {
            $where[] = 'form_id = %d';
            $where_format[] = $args['form_id'];
        }
        
        if ($args['status'] !== 'all') {
            $where[] = 'status = %s';
            $where_format[] = $args['status'];
        }
        
        if ($args['search']) {
            $where[] = 'form_data LIKE %s';
            $where_format[] = '%' . $wpdb->esc_like($args['search']) . '%';
        }
        
        // Combine WHERE clauses
        $where_sql = '';
        if (!empty($where)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where);
        }
        
        // Validate orderby field
        $allowed_orderby = ['id', 'form_id', 'status', 'created_at', 'updated_at'];
        if (!in_array($args['orderby'], $allowed_orderby)) {
            $args['orderby'] = 'created_at';
        }
        
        // Validate order
        $args['order'] = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';
        
        // Count total items for pagination
        $count_query = "SELECT COUNT(*) FROM {$this->table} $where_sql";
        if (!empty($where_format)) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $count_query = $wpdb->prepare($count_query, $where_format); 
        }
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $total = (int) $wpdb->get_var($count_query);
        
        // Main query with or without pagination
        if ($show_all) {
            // Show all entries without LIMIT
            $entries_query = "SELECT * FROM {$this->table} $where_sql ORDER BY {$args['orderby']} {$args['order']}";
            if (!empty($where_format)) {
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                $entries = $wpdb->get_results($wpdb->prepare($entries_query, $where_format), ARRAY_A); 
            } else {
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                $entries = $wpdb->get_results($entries_query, ARRAY_A); 
            }
            $total_pages = 1;
        } else {
            // Calculate offset for pagination
            $offset = ($args['page'] - 1) * $args['per_page'];
            
            // Query with pagination
            $entries_query = "SELECT * FROM {$this->table} $where_sql ORDER BY {$args['orderby']} {$args['order']} LIMIT %d OFFSET %d";
            $entries_query_args = array_merge($where_format, [$args['per_page'], $offset]);

            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $entries = $wpdb->get_results($wpdb->prepare($entries_query, $entries_query_args), ARRAY_A);
            
            $total_pages = ceil($total / $args['per_page']);
        }
        
        // Parse JSON form data for each entry
        foreach ($entries as &$entry) {
            $entry['form_data'] = json_decode($entry['form_data'], true);

            if($form->get($entry['form_id'])) {
                $entry['form'] = $form->get($entry['form_id'])['title'];
            }
            
            // Get user information
            if (!empty($entry['user_id'])) {
                $user = get_user_by('id', $entry['user_id']);
                $entry['user'] = $user ? $user->display_name : __('Guest', 'ht-contactform');
            } else {
                $entry['user'] = __('Guest', 'ht-contactform');
        }
        }
        
        // Return paginated results
        return [
            'items' => $entries,
            'total' => $total,
            'total_pages' => $total_pages
        ];
    }

    /**
     * Get a submission entry by ID with Laravel-style attribute access
     * 
     * @param int $id Entry ID
     * @return object|WP_Error Entry object or error
     */
    public function find($id) {
        global $wpdb;
        
        $entry = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id),
            ARRAY_A
        );
        
        if (!$entry) {
            // Log the error for debugging
            error_log("HT ContactForm DB Error: {$wpdb->last_error}");
            return new WP_Error(
                'entry_not_found',
                __('Entry not found', 'ht-contactform'),
                ['status' => 404]
            );
        }
        
        // Parse the JSON form data
        $entry['form_data'] = json_decode($entry['form_data'], true);
        
        // Get user information
        if (!empty($entry['user_id'])) {
            $user = get_user_by('id', $entry['user_id']);
            $entry['user'] = $user ? $user->display_name : __('Guest', 'ht-contactform');
        } else {
            $entry['user'] = __('Guest', 'ht-contactform');
        }
        
        // Convert to object for attribute access
        return (object) $entry;
    }
    
    /**
     * Create a new entry
     * 
     * @param array $data Entry data including form_id and form_data
     * @param array $meta Entry metadata
     * @return int|WP_Error New entry ID or error
     */
    public function create($data, $meta) {
        global $wpdb;
        
        // Validate required data
        if (empty($data['form_id']) || empty($data)) {
            return new WP_Error(
                'missing_data',
                __('Form ID and form data are required', 'ht-contactform'),
                ['status' => 400]
            );
        }
        
        // Prepare entry data
        $entry = [
            'form_id'     => absint($data['form_id']),
            'form_data'   => wp_json_encode($data),
            'status'      => 'unread',
            'source_url'  => site_url(sanitize_url($data['_wp_http_referer'])),
            'user_id'     => $meta['user_id'],
            'ip_address'  => $meta['ip_address'],
            'browser'     => $meta['browser'],
            'device'      => $meta['device'],
            'created_at'  => $meta['created_at'],
            'updated_at'  => $meta['updated_at']
        ];
        
        // Insert into database
        $result = $wpdb->insert($this->table, $entry);
        
        if ($result === false) {
            // Log the error for debugging
            error_log("HT ContactForm DB Error: {$wpdb->last_error}");
            return new WP_Error(
                'db_error',
                __('Failed to save form submission', 'ht-contactform'),
                ['status' => 500]
            );
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Update an entry
     * 
     * @param int $id Entry ID
     * @param array $data Entry data to update
     * @return bool|WP_Error True on success or error
     */
    public function update($id, $data) {
        global $wpdb;
        
        // Get existing entry
        $entry = $this->find($id);
        if (is_wp_error($entry)) {
            return $entry;
        }
        
        // Prepare update data
        $update_data = [];
        
        if (isset($data['status'])) {
            $update_data['status'] = sanitize_text_field($data['status']);
            
            // If marking as read and viewed_at is not set, set it now
            if ($data['status'] === 'read' && empty($entry->viewed_at)) {
                $update_data['viewed_at'] = current_time('mysql');
            }
        }
        
        // Always update the updated_at timestamp
        $update_data['updated_at'] = current_time('mysql');
        
        // Update only if we have data to update
        if (empty($update_data)) {
            return true; // Nothing to update
        }
        
        // Update the database record
        $result = $wpdb->update(
            $this->table,
            $update_data,
            ['id' => $id]
        );
        
        if ($result === false) {
            // Log the error for debugging
            error_log("HT ContactForm DB Error: {$wpdb->last_error}");
            return new WP_Error(
                'update_failed',
                __('Failed to update entry', 'ht-contactform'),
                ['status' => 500]
            );
        }
        
        return true;
    }
    
    /**
     * Delete an entry
     * 
     * @param int $id Entry ID
     * @return bool|WP_Error True on success or error
     */
    public function delete($id) {
        global $wpdb;
        
        // Check if entry exists
        $entry = $this->find($id);
        if (is_wp_error($entry)) {
            return $entry;
        }
        
        // Delete the entry
        $result = $wpdb->delete(
            $this->table,
            ['id' => $id],
            ['%d']
        );
        
        if ($result === false) {
            // Log the error for debugging
            error_log("HT ContactForm DB Error: {$wpdb->last_error}");
            return new WP_Error(
                'delete_failed',
                __('Failed to delete entry', 'ht-contactform'),
                ['status' => 500]
            );
        }
        
        return true;
    }
    
    /**
     * Delete all entries for a form
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
     * Get count of entries for a form
     * 
     * @param int $form_id Form ID
     * @param string $status Optional. Status to count ('read', 'unread', or 'all')
     * @return int Count of entries
     */
    public function count_by_form($form_id, $status = 'all') {
        global $wpdb;
        
        $query = "SELECT COUNT(*) FROM {$this->table} WHERE form_id = %d";
        $params = [$form_id];
        
        if ($status !== 'all') {
            $query .= " AND status = %s";
            $params[] = $status;
        }
        
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return (int) $wpdb->get_var($wpdb->prepare($query, $params));
    }
    
    /**
     * Mark entry as read
     * 
     * @param int $id Entry ID
     * @return bool|WP_Error True on success or error
     */
    public function mark_as_read($id) {
        return $this->update($id, [
            'status' => 'read',
            'viewed_at' => current_time('mysql')
        ]);
    }
    
    /**
     * Mark entry as unread
     * 
     * @param int $id Entry ID
     * @return bool|WP_Error True on success or error
     */
    public function mark_as_unread($id) {
        return $this->update($id, [
            'status' => 'unread',
            'viewed_at' => null
        ]);
    }
    
    /**
     * Mark entry as deleted
     * 
     * @param int $id Entry ID
     * @return bool|WP_Error True on success or error
     */
    public function mark_as_delete($id) {
        return $this->update($id, [
            'status' => 'deleted',
            'viewed_at' => null
        ]);
    }
    
    /**
     * Mark multiple entries as read/unread/deleted
     * 
     * @param array $ids Array of entry IDs
     * @return bool True on success
     */
    public function bulk_mark_as($ids, $status) {
        global $wpdb;
        
        if (empty($ids)) {
            return true;
        }
        
        $ids = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        
        $query = $wpdb->prepare(
            "UPDATE {$this->table} SET status = %s, viewed_at = %s, updated_at = %s WHERE id IN ($placeholders)",
            array_merge([$status, current_time('mysql'), current_time('mysql')], $ids)
        );
        
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $wpdb->query($query) !== false;
    }
    
    /**
     * Export entries to different formats (CSV, Excel, JSON, ODS)
     * 
     * @param int $form_id Form ID
     * @param string $format Export format (csv, excel, json, ods)
     * @return string|array Exported data
     */
    public function export($form_id, $format = 'csv') {
        global $wpdb;
        
        // Get form to get field names
        $form_model = Form::get_instance();
        $form = $form_model->get($form_id);
        
        if (is_wp_error($form)) {
            return '';
        }
        
        // Get all entries for this form
        $entries = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE form_id = %d ORDER BY created_at DESC", $form_id),
            ARRAY_A
        );
        
        if (empty($entries)) {
            return '';
        }
        
        // Build field mapping
        $fields = [];
        foreach ($form['fields'] as $field) {
            // Skip if we don't have proper settings structure
            if (!isset($field['settings']) || !is_array($field['settings'])) {
                continue;
            }
            
            // Find field properties safely
            $label_value = !empty($field['settings']['label']) ? $field['settings']['label'] : '';
            $admin_label_value = !empty($field['settings']['admin_label']) ? $field['settings']['admin_label'] : '';
            $name_value = isset($field['settings']['name_attribute']) ? $field['settings']['name_attribute'] : '';
            
            // Skip if we don't have a name to use as a key
            if (empty($name_value)) {
                continue;
            }
            
            // Use admin_label if available, otherwise label, or fallback to name
            $fields[$name_value] = !empty($admin_label_value) ? $admin_label_value : 
                                (!empty($label_value) ? $label_value : $name_value);
        }
        
        // Add metadata columns
        $columns = [];
        $columns[] = 'ID';
        $columns[] = 'Form ID';
        
        // Add form field columns
        foreach ($fields as $field_name => $field_label) {
            $columns[] = $field_label;
        }
        
        $columns[] = 'Submission Date';
        $columns[] = 'IP Address';
        $columns[] = 'Status';
        
        // Prepare data for export
        $export_data = [];
        foreach ($entries as $entry) {
            $form_data = json_decode($entry['form_data'], true);
            
            $row = [];
            
            // Add field values as key-value pairs
            foreach (array_keys($fields) as $field_name) {
                if (isset($form_data[$field_name]) && is_array($form_data[$field_name])) {
                    // Check if it's a repeater field (array of objects)
                    if (!empty($form_data[$field_name][0]) && is_array($form_data[$field_name][0])) {
                        // Format repeater data for export
                        $repeater_rows = [];
                        foreach ($form_data[$field_name] as $row_index => $row_data) {
                            $row_num = $row_index + 1;
                            $row_values = array_map(function($val) {
                                return is_array($val) ? implode(', ', $val) : $val;
                            }, $row_data);
                            $repeater_rows[] = "Row {$row_num}: " . implode(' | ', $row_values);
                        }
                        $row[$fields[$field_name]] = implode('; ', $repeater_rows);
                    } else if(array_keys($form_data[$field_name]) !== range(0, count($form_data[$field_name]) - 1)) {
                        // Associative array (like name or address fields)
                        $row[$fields[$field_name]] = implode(' ', $form_data[$field_name]) ?? '';
                    } else {
                        // Simple indexed array (like checkboxes)
                        $row[$fields[$field_name]] = implode(', ', $form_data[$field_name]) ?? '';
                    }
                } else {
                    $row[$fields[$field_name]] = $form_data[$field_name] ?? '';
                }
            }
            
            // Add metadata
            $row['ID'] = $entry['id'];
            $row['Form ID'] = $entry['form_id'];
            $row['Submission Date'] = $entry['created_at'];
            $row['IP Address'] = $entry['ip_address'];
            $row['Status'] = $entry['status'];
            
            $export_data[] = $row;
        }
        
        // Export based on format
        $format = strtolower($format);
        
        switch ($format) {
            case 'json':
                return json_encode($export_data);
                
            case 'xlsx':
                return $this->array_to_xlsx($export_data);
                
            case 'ods':
                return $this->array_to_ods($export_data);
                
            case 'csv':
            default:
                return $this->array_to_csv($export_data);
        }
    }

    /**
     * Convert array data to CSV using OpenSpout
     * 
     * @param array $data Data to convert
     * @return string CSV content
     */
    private function array_to_csv($data) {
        if (empty($data)) {
            return '';
        }
        
        // Start output buffering
        ob_start();
        
        // Use OpenSpout for CSV export
        $writer = WriterEntityFactory::createCSVWriter();
        $writer->openToFile('php://output');
        
        // Add CSV header (keys of first row)
        $headerRow = WriterEntityFactory::createRowFromArray(array_keys($data[0]));
        $writer->addRow($headerRow);
        
        // Add entries
        foreach ($data as $row) {
            $dataRow = WriterEntityFactory::createRowFromArray($row);
            $writer->addRow($dataRow);
        }
        
        $writer->close();
        $csv_content = ob_get_clean();
        
        return $csv_content;
    }

    /**
     * Convert array data to XLSX using OpenSpout
     * 
     * @param array $data Data to convert
     * @return string XLSX content
     */
    private function array_to_xlsx($data) {
        if (empty($data)) {
            return '';
        }
        
        // Start output buffering
        ob_start();
        
        // Use OpenSpout for XLSX export
        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToFile('php://output');
        
        // Set the sheet name (get the first sheet that's created by default)
        $firstSheet = $writer->getCurrentSheet();
        $firstSheet->setName('Form Entries');
        
        // Add XLSX header (keys of first row)
        $headerRow = WriterEntityFactory::createRowFromArray(array_keys($data[0]));
        $writer->addRow($headerRow);
        
        // Add entries
        foreach ($data as $row) {
            $dataRow = WriterEntityFactory::createRowFromArray($row);
            $writer->addRow($dataRow);
        }
        
        $writer->close();
        $xlsx_content = ob_get_clean();
        
        return $xlsx_content;
    }

    /**
     * Convert array data to ODS using OpenSpout
     * 
     * @param array $data Data to convert
     * @return string ODS content
     */
    private function array_to_ods($data) {
        if (empty($data)) {
            return '';
        }
        
        // Start output buffering
        ob_start();
        
        // Use OpenSpout for ODS export
        $writer = WriterEntityFactory::createODSWriter();
        $writer->openToFile('php://output');
        
        // Set the sheet name (get the first sheet that's created by default)
        $firstSheet = $writer->getCurrentSheet();
        $firstSheet->setName('Form Entries');
        
        // Add ODS header (keys of first row)
        $headerRow = WriterEntityFactory::createRowFromArray(array_keys($data[0]));
        $writer->addRow($headerRow);
        
        // Add entries
        foreach ($data as $row) {
            $dataRow = WriterEntityFactory::createRowFromArray($row);
            $writer->addRow($dataRow);
        }
        
        $writer->close();
        $ods_content = ob_get_clean();
        
        return $ods_content;
    }
}