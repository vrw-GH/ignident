<?php

namespace HTContactFormAdmin\Includes\Models;

use HTContactFormAdmin\Includes\Config\Form as FormConfig;
use HTContactFormAdmin\Includes\Models\Entries;
use WP_Post;
use WP_Error;

/**
 * Form Model Class
 * 
 * Handles all form data operations including CRUD operations,
 * form data manipulation, and data sanitization.
 */
class Form {
    //-------------------------------------------------------------------------
    // PROPERTIES
    //-------------------------------------------------------------------------
    
    /** @var string Custom post type name for forms */
    private $post_type = 'ht_form';
    private $entries = null;

    private $default_fields = [];
    private $default_settings = [];
    private $default_integrations = [];

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
        // Initialize the model
        $form_config = FormConfig::get_instance();
        $this->entries = Entries::get_instance();
        $this->default_fields = $form_config->fields();
        $this->default_settings = $form_config->form_settings();
        $this->default_integrations = $form_config->form_editor_integrations();
    }

    /**
     * Default form settings reduced to the saved shape:
     * { section_key: { id, settings: { setting_id: value } } }
     *
     * @return array
     */
    public function get_default_settings(): array {
        $defaults = [];

        foreach ($this->default_settings as $section_key => $section) {
            $values = [];

            foreach (($section['settings'] ?? []) as $setting) {
                if (isset($setting['id'])) {
                    $values[$setting['id']] = $setting['value'] ?? null;
                }
            }

            $defaults[$section_key] = [
                'id'       => $section['id'] ?? $section_key,
                'settings' => $values,
            ];
        }

        return $defaults;
    }

    //-------------------------------------------------------------------------
    // MAIN CRUD OPERATIONS
    //-------------------------------------------------------------------------
    
    /**
     * Get all forms
     * 
     * @param string $search_query Optional search query
     * @return array Array of form data
     */
    public function get_all($search_query = '') {
        $args = [
            'post_type'      => $this->post_type,
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'post_status'    => 'publish'
        ];

        if ($search_query) {
            $args['s'] = $search_query;
        }

        $posts = get_posts($args);
        $forms = [];

        foreach ($posts as $post) {
            $form_data = $this->get_form_data($post->ID);
            $entries_count_unread = $this->entries->count_by_form($post->ID, 'unread'); // Placeholder for entries count
            $entries_count_read = $this->entries->count_by_form($post->ID, 'read'); // Placeholder for entries count
            $entries_count_total = $this->entries->count_by_form($post->ID, 'all'); // Placeholder for entries count

            $forms[] = [
                'id'         => $post->ID,
                'title'      => $post->post_title,
                'fields'     => $form_data['fields'],
                'settings'   => $form_data['settings'],
                'shortcode'  => "[ht_form id=\"{$post->ID}\"]",
                'entries'    => [
                    'unread' => $entries_count_unread,
                    'read'   => $entries_count_read,
                    'total'  => $entries_count_total
                ],
                'created_at' => $post->post_date,
                'updated_at' => $post->post_modified
            ];
        }

        return $forms;
    }

    /**
     * Get single form
     * 
     * @param int $form_id Form ID
     * @return array|WP_Error Form data or error
     */
    public function get($form_id) {
        $post = $this->get_form_post($form_id);
        if (is_wp_error($post)) {
            return $post;
        }

        $form_data = $this->get_form_data($post->ID);

        return [
            'id'         => $post->ID,
            'title'      => $post->post_title,
            'fields'     => $form_data['fields'],
            'settings'   => $form_data['settings'],
            'shortcode'  => "[ht_form id=\"{$post->ID}\"]",
            'created_at' => $post->post_date,
            'updated_at' => $post->post_modified
        ];
    }

    /**
     * Create new form
     * 
     * @param array $form_data Form data
     * @return array|WP_Error New form data or error
     */
    public function create($form_data) {
        $title = $form_data['title'] ?? __('Untitled Form', 'ht-contactform');
        
        $sanitized_data = $this->sanitize_form_data($form_data);

        // Prepare initial content as JSON
        $content_data = wp_json_encode([
            'fields'   => $sanitized_data['fields'] ?? [],
            'settings' => $sanitized_data['settings'] ?? (object) []
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Use wp_slash to preserve newlines and special characters during post insertion
        $post_id = wp_insert_post([
            'post_title'   => $title,
            'post_type'    => $this->post_type,
            'post_status'  => 'publish',
            'post_content' => wp_slash($content_data)
        ], true);

        if (is_wp_error($post_id)) {
            return new WP_Error(
                'creation_failed',
                'Failed to create form',
                ['status' => 500]
            );
        }

        return $this->get($post_id);
    }

    /**
     * Update existing form
     * 
     * @param int $form_id Form ID
     * @param array $form_data Form data
     * @return array|WP_Error Updated form data or error
     */
    public function update($form_id, $form_data) {
        $post = $this->get_form_post($form_id);
        if (is_wp_error($post)) {
            return $post;
        }

        if (empty($form_data)) {
            return new WP_Error(
                'invalid_data',
                'Form data is required',
                ['status' => 400]
            );
        }

        // Update post title
        $updated_post = wp_update_post([
            'ID'         => $post->ID,
            'post_title' => $form_data['title'],
            'post_type'  => $this->post_type
        ], true);

        if (is_wp_error($updated_post)) {
            return new WP_Error(
                'update_failed',
                'Failed to update form',
                ['status' => 500]
            );
        }

        // Save form data to post_content
        $save_result = $this->save_form_data($post->ID, $form_data);
        
        if (!$save_result) {
            return new WP_Error(
                'update_failed',
                'Failed to save form data',
                ['status' => 500]
            );
        }

        return $this->get($form_id);
    }

    /**
     * Delete form
     * 
     * @param int $form_id Form ID
     * @return bool|WP_Error Success status or error
     */
    public function delete($form_id) {
        $post = $this->get_form_post($form_id);
        if (is_wp_error($post)) {
            return $post;
        }

        $result = wp_delete_post($post->ID, true);
        if (!$result) {
            return new WP_Error(
                'delete_failed',
                'Failed to delete form',
                ['status' => 500]
            );
        }

        return true;
    }

    /**
     * Duplicate existing form
     * 
     * @param int $form_id Form ID
     * @return array|WP_Error New form data or error
     */
    public function duplicate($form_id) {
        $original_form = $this->get_form_post($form_id);
        
        if (is_wp_error($original_form)) {
            return $original_form;
        }

        // Get original form parsed data
        $form_data = $this->parse_post_content($original_form);
        
        // Prepare content for new form
        $content_data = wp_json_encode([
            'fields'   => $form_data['fields'],
            'settings' => $form_data['settings']
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Create new form post
        $new_form_id = wp_insert_post([
            'post_title'   => "{$original_form->post_title} (Copy)",
            'post_type'    => $this->post_type,
            'post_status'  => 'publish',
            'post_content' => wp_slash($content_data)
        ], true);

        if (is_wp_error($new_form_id)) {
            return new WP_Error(
                'form_duplication_failed',
                'Failed to duplicate form',
                ['status' => 500]
            );
        }

        // Get the new form data
        return $this->get($new_form_id);
    }

    /**
     * Export form data
     * 
     * @param int $form_id Form ID
     * @return array|WP_Error Form export data or error
     */
    public function export($form_id) {
        $form = $this->get_form_post($form_id);
        
        if (is_wp_error($form)) {
            return $form;
        }

        $form_data = $this->get_form_data($form_id);
        
        return [
            'title'         => $form->post_title,
            'fields'        => $form_data['fields'],
            'settings'      => $form_data['settings'],
            'date_exported' => current_time('mysql')
        ];
    }

    /**
     * Get form integrations
     * 
     * @param int $form_id Form ID
     * @return array|WP_Error Form integrations or error
     */
    public function get_integrations($form_id) {
        $integrations = get_post_meta($form_id, 'integrations', true);
        
        if (empty($integrations)) {
            return [];
        }
        return json_decode($integrations, true);
    }   

    /**
     * Update form integrations
     * 
     * @param int $form_id Form ID
     * @param array $integration Form integration
     * @return bool|WP_Error Form integrations or error
     */
    public function update_integrations($form_id, $integration) {
        $integrations = $this->get_integrations($form_id);
        
        if (empty($integrations)) {
            $integrations = [];
        }

        $integration = array_merge($integration, $this->sanitize_integration($integration));
        
        // Check if this integration already exists (by ID)
        $updated = false;
        if (isset($integration['id'])) {
            foreach ($integrations as $key => $existing) {
                if (isset($existing['id']) && (int)$existing['id'] === (int)$integration['id']) {
                    // Update existing integration
                    $integrations[$key] = $integration;
                    $updated = true;
                    break;
                }
            }
        }
        
        // If not updated, add as new
        if (!$updated) {
            array_push($integrations, $integration);
        }
        
        $result = update_post_meta($form_id, 'integrations', json_encode($integrations));   
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return true;
    }

    /**
     * Delete form integrations
     * 
     * @param int $form_id Form ID
     * @param array $integration_id Form integration
     * @return bool|WP_Error Form integrations or error
     */
    public function delete_integrations($form_id, $integration_id) {
        $integrations = $this->get_integrations($form_id);
        
        if (empty($integrations)) {
            $integrations = [];
        }

        $filtered_integrations = [];
        foreach ($integrations as $integration) {
            if ((int) $integration['id'] !== (int) $integration_id) {
                $filtered_integrations[] = $integration;
            }
        }
        
        $result = update_post_meta($form_id, 'integrations', json_encode($filtered_integrations));
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return true;
    }

    /**
     * Sanitize form integration
     * 
     * @param array $integration Form integration
     * @return array Sanitized form integration
     */
    public function sanitize_integration($integration) {
        $default_fields = $this->default_integrations[$integration['type']]['fields'];
        foreach ($integration as $key => $value) {
            // Skip type and id fields
            if (in_array($key, ['type', 'id'])) {
                continue;
            }
            
            $field = current(array_filter($default_fields, function($field) use ($key) {
                return $field['id'] === $key;
            }));

            if(!isset($field)) {
                $integration[$key] = sanitize_text_field($value);
                continue;
            }
            
            if(isset($field['fields']) && is_array($value)) {
                if($this->is_associative_array($value)) {
                    $sanitized_items = [];
                    foreach ($value as $k => $item) {
                        if (isset($field['callback'])) {
                            $sanitized_items[$k] = call_user_func($field['callback'], $item);
                        } else {
                            $sanitized_items[$k] = sanitize_text_field($item);
                        }
                    }
                } else {
                    $sanitized_items = [];
                    foreach ($value as $item) {
                        $sanitized_item = [];
                        foreach ($field['fields'] as $sub_field) {
                            $sub_field_id = $sub_field['id'];
                            $sub_field_value = isset($item[$sub_field_id]) ? $item[$sub_field_id] : '';
                            
                            if (isset($sub_field['callback'])) {
                                $sanitized_item[$sub_field_id] = call_user_func($sub_field['callback'], $sub_field_value);
                            } else {
                                $sanitized_item[$sub_field_id] = sanitize_text_field($sub_field_value);
                            }
                        }
                        $sanitized_items[] = $sanitized_item;
                    }
                }
                $integration[$key] = $sanitized_items;
            } elseif (is_array($value)) {
                $sanitized_items = [];
                foreach ($value as $item) {
                    if (isset($field['callback'])) {
                        $sanitized_items[] = call_user_func($field['callback'], $item);
                    } else {
                        $sanitized_items[] = sanitize_text_field($item);
                    }
                }
                $integration[$key] = $sanitized_items;
            } else {
                if (isset($field['callback'])) {
                    $integration[$key] = call_user_func($field['callback'], $value);
                } else {
                    $integration[$key] = sanitize_text_field($value);
                }
            }
        }
        
        return $integration;
    }

    //-------------------------------------------------------------------------
    // HELPER METHODS
    //-------------------------------------------------------------------------

    public function is_associative_array(array $arr): bool {
        if ([] === $arr) return false; // Empty array is not associative
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
    
    /**
     * Get form post by ID
     * 
     * @param int $post_id Post ID
     * @return WP_Post|WP_Error Post object or error if not found
     */
    private function get_form_post($post_id) {
        $post = get_post($post_id);
        
        if (!$post || $post->post_type !== $this->post_type) {
            return new WP_Error(
                'form_not_found',
                "Form not found -> $post_id",
                ['status' => 404]
            );
        }

        return $post;
    }

    /**
     * Get form data with merged default values from post content
     * 
     * @param int $post_id Post ID
     * @return array{fields: array, settings: object} Form data
     */
    private function get_form_data($post_id) {
        $post = $this->get_form_post($post_id);
        if (is_wp_error($post)) {
            return [
                'fields'   => [],
                'settings' => (object) []
            ];
        }

        // Parse form data from post content
        $saved_data = $this->parse_post_content($post);

        // Get default data
        $form_config = FormConfig::get_instance();
        $default_fields = $form_config->fields();
        $default_settings = $form_config->form_settings();

        // Merge fields and settings with defaults
        // $merged_fields = $this->merge_fields_with_defaults($saved_data['fields'], $default_fields);
        // $merged_settings = $this->merge_settings_with_defaults($saved_data['settings'], $default_settings);
        return $saved_data;
    }

    /**
     * Parse form data from post content
     * 
     * @param WP_Post $post Post object
     * @return array{fields: array, settings: object} Parsed data
     */
    private function parse_post_content($post) {
        $saved_data = [];
        if (!empty($post->post_content)) {
            $saved_data = json_decode($post->post_content, true, 512, JSON_UNESCAPED_UNICODE);
        }

        $saved_fields = isset($saved_data['fields']) && is_array($saved_data['fields']) 
            ? $saved_data['fields'] 
            : [];
            
        $saved_settings = isset($saved_data['settings']) 
            ? $saved_data['settings'] 
            : (object) [];
        
        if (is_array($saved_settings)) {
            $saved_settings = (object) $saved_settings;
        }

        return [
            'fields'   => $saved_fields,
            'settings' => $saved_settings
        ];
    }

    /**
     * Merge saved fields with default fields
     * 
     * @param array $saved_fields Saved fields from post content
     * @param array $default_fields Default fields from settings
     * @return array Merged fields
     */
    private function merge_fields_with_defaults($saved_fields, $default_fields) {
        $merged_fields = [];
        
        foreach ($saved_fields as $saved_field) {
            $default_field = $this->find_default_field($saved_field['type'], $default_fields);
            if (!$default_field) {
                continue;
            }
            
            // Merge settings
            $merged_settings = [];
            foreach ($default_field['settings'] as $default_setting) {
                $saved_setting = $this->find_setting_by_id(
                    $saved_field['settings'], 
                    $default_setting['id']
                );
                $merged_settings[] = array_merge($default_setting, $saved_setting ?: []);
            }
            
            // Create merged field
            $merged_fields[] = [
                'id'       => $saved_field['id'],
                'type'     => $default_field['type'],
                'label'    => $default_field['label'],
                'options'  => $default_field['options'] ?? [],
                'settings' => $merged_settings
            ];
        }
        
        return $merged_fields;
    }

    /**
     * Merge saved settings with default settings
     * 
     * @param object $saved_settings Saved settings from post content
     * @param array $default_settings Default settings from settings class
     * @return array Merged settings
     */
    private function merge_settings_with_defaults($saved_settings, $default_settings) {
        $merged_settings = [];
        
        foreach ($default_settings as $section_key => $default_section) {
            $saved_section = isset($saved_settings->{$section_key}) 
                ? (array) $saved_settings->{$section_key} 
                : [];
            
            // Merge settings within section
            $section_settings = [];
            foreach ($default_section['settings'] as $default_setting) {
                $saved_setting = $this->find_setting_by_id(
                    $saved_section['settings'] ?? [], 
                    $default_setting['id']
                );
                $section_settings[] = array_merge($default_setting, $saved_setting ?: []);
            }

            $merged_settings[$section_key] = [
                'id'       => $default_section['id'],
                'label'    => $default_section['label'],
                'settings' => $section_settings
            ];
        }
        
        return $merged_settings;
    }

    /**
     * Find a field in default fields by type
     * 
     * @param string $type Field type to find
     * @param array $default_fields Array of default fields
     * @return array|null Found field or null
     */
    private function find_default_field($type, $default_fields) {
        foreach ($default_fields as $field) {
            if ($field['type'] === $type) {
                return $field;
            }
        }
        return null;
    }

    /**
     * Find a setting by its ID
     * 
     * @param array|null $settings Array of settings
     * @param string $id Setting ID to find
     * @return array|null Found setting or null
     */
    private function find_setting_by_id($settings, $id) {
        if (!is_array($settings)) {
            return null;
        }
        
        foreach ($settings as $setting) {
            if ($setting['id'] === $id) {
                return $setting;
            }
        }
        
        return null;
    }

    /**
     * Save form data to post content
     * 
     * @param int $post_id Post ID
     * @param array $form_data Form data to save
     * @return bool Success status
     */
    private function save_form_data($post_id, $form_data) {
        $sanitized_data = $this->sanitize_form_data($form_data);
        
        // Convert the form data to JSON for storage
        $content_data = wp_json_encode([
            'fields'   => $sanitized_data['fields'],
            'settings' => $sanitized_data['settings']
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        
        // Update the post with new content
        $updated = wp_update_post([
            'ID'           => $post_id,
            'post_content' => wp_slash($content_data)
        ], true);
        
        return !is_wp_error($updated);
    }

    //-------------------------------------------------------------------------
    // SANITIZATION METHODS
    //-------------------------------------------------------------------------
    
    /**
     * Sanitize form data
     * 
     * @param array $form_data Raw form data
     * @return array{fields: array, settings: object} Sanitized form data
     */
    private function sanitize_form_data($form_data) {
        if (!is_array($form_data)) {
            return [
                'fields'   => [],
                'settings' => (object) []
            ];
        }

        $sanitized_fields = $this->sanitize_fields($form_data['fields'] ?? []);
        $sanitized_settings = $this->sanitize_settings($form_data['settings'] ?? []);

        return [
            'fields'   => $sanitized_fields,
            'settings' => (object) $sanitized_settings
        ];
    }

    /**
     * Sanitize fields
     * 
     * @param array $fields Fields to sanitize
     * @return array Sanitized fields
     */
    private function sanitize_fields($fields) {
        if (!is_array($fields)) {
            return [];
        }
        
        $sanitized_fields = [];
        
        foreach ($fields as $field) {
            if (!is_array($field)) {
                continue;
            }

            $sanitized_field = [
                'id'   => sanitize_key($field['id'] ?? ''),
                'type' => sanitize_key($field['type'] ?? ''),
            ];

            $default_field = $this->find_default_field($field['type'], $this->default_fields);
            // Sanitize field settings
            if (!empty($field['settings']) && is_array($field['settings'])) {
                $sanitized_field['settings'] = $this->sanitize_field_settings($field['settings'], $default_field);
            }

            $sanitized_fields[] = $sanitized_field;
        }
        
        return $sanitized_fields;
    }

    /**
     * Sanitize field settings
     * 
     * @param array $settings Settings to sanitize
     * @param array $default_field Default field settings
     * @return array Sanitized settings
     */
    private function sanitize_field_settings($settings, $default_field) {
        $sanitized_settings = [];
        foreach ($settings as $key => $value) {

            $field_setting = current(array_filter($default_field['settings'] ?? [], function($sett) use ($key) {
                return $sett['id'] === $key;
            }));
            
            $key = sanitize_key($key);
            $value = $this->sanitize_setting_value($value, $field_setting['type'] ?? '');

            $sanitized_settings[$key] = $value;
        }

        return $sanitized_settings;
    }

    /**
     * Sanitize form settings
     * 
     * @param array|object $settings Settings to sanitize
     * @return array Sanitized settings
     */
    private function sanitize_settings($settings) {
        if (is_object($settings)) {
            $settings = (array) $settings;
        }
        
        if (!is_array($settings)) {
            return [];
        }
        
        $sanitized_settings = [];
        
        foreach ($settings as $section_key => $section) {
            if (!is_array($section)) {
                continue;
            }

            $sanitized_section = [
                'id' => sanitize_key($section['id'] ?? ''),
                'settings' => []
            ];

            $default_section_settings = $this->default_settings[$section_key]['settings'] ?? [];

            if (!empty($section['settings']) && is_array($section['settings'])) {
                foreach ($section['settings'] as $key => $value) {
                    $default_section_setting = $this->find_setting_by_id($default_section_settings, $key);

                    $sanitized_section['settings'][$key] = $this->sanitize_setting_value($value, $default_section_setting['type']);
                }
            }

            $sanitized_settings[$section_key] = $sanitized_section;
        }
        
        return $sanitized_settings;
    }

    /**
     * Sanitize setting value based on type
     * 
     * @param mixed $value Value to sanitize
     * @param string $type Setting type
     * @return mixed Sanitized value
     */
    private function sanitize_setting_value($value, $type) {
        switch ($type) {
            case 'switch':
                return (bool) $value;
                
            case 'number':
                return is_numeric($value) ? (float) $value : 0;
                
            case 'textarea':
                return sanitize_textarea_field($value);
                
            case 'repeater':
            case 'cl_repeater':
            case 'repeater_sub_fields':
                return $this->sanitize_recursive($value);
                
            case 'select':
                if (is_array($value)) {
                    // For arrays, map sanitize_key
                    return array_map('sanitize_key', $value);
                }
                // For mask format options with special characters, preserve them
                if (strpos($value, '/') !== false || strpos($value, ':') !== false || 
                    strpos($value, '.') !== false || strpos($value, ' ') !== false) {
                    return $value;
                }
                return sanitize_text_field($value);
                
            case 'names':
                return $this->sanitize_names_field($value);
                
            case 'group':
            case 'collapse':
                $group_data = [];
                foreach ($value as $key => $val) {
                    if (is_array($val)) {
                        $group_data[$key] = $this->sanitize_recursive($val);
                    } elseif (is_bool($val)) {
                        $group_data[$key] = (bool) $val;
                    } elseif (is_numeric($val)) {
                        $group_data[$key] = (float) $val;
                    } elseif (is_string($val)) {
                        $group_data[$key] = sanitize_text_field($val);
                    }
                }
                return $group_data;

            case 'checkbox':
                return array_map('sanitize_text_field', $value);

            case 'richtext':
                return wp_kses_post($value);

            case 'chained_data':
                // Sanitize chained select data stored as object
                if (!is_array($value)) {
                    return [];
                }
                $sanitized = [];

                // Sanitize data_source (file or url)
                $sanitized['data_source'] = isset($value['data_source']) && in_array($value['data_source'], ['file', 'url'])
                    ? $value['data_source']
                    : 'file';

                // Sanitize remote_url
                $sanitized['remote_url'] = isset($value['remote_url']) ? esc_url_raw($value['remote_url']) : '';

                // Sanitize chained_data array (array of row objects)
                if (isset($value['chained_data']) && is_array($value['chained_data'])) {
                    $sanitized['chained_data'] = array_map(function($row) {
                        if (!is_array($row)) {
                            return [];
                        }
                        return array_map('sanitize_text_field', $row);
                    }, $value['chained_data']);
                } else {
                    $sanitized['chained_data'] = [];
                }

                // Sanitize chained_labels array
                if (isset($value['chained_labels']) && is_array($value['chained_labels'])) {
                    $sanitized['chained_labels'] = array_map('sanitize_text_field', $value['chained_labels']);
                } else {
                    $sanitized['chained_labels'] = [];
                }

                // Sanitize level_count
                $sanitized['level_count'] = isset($value['level_count']) ? absint($value['level_count']) : 0;

                return $sanitized;

            default:
                return sanitize_text_field($value);
        }
    }

    /**
     * Sanitize names field type
     * 
     * @param mixed $value Value to sanitize
     * @return array Sanitized names field
     */
    private function sanitize_names_field($value) {
        if (!is_array($value)) {
            return [];
        }
        
        $sanitized = [];
        
        foreach ($value as $key => $name_field) {
            if (is_array($name_field)) {
                $sanitized[$key] = [
                    'label'       => sanitize_text_field($name_field['label'] ?? ''),
                    'placeholder' => sanitize_text_field($name_field['placeholder'] ?? ''),
                    'value'       => sanitize_text_field($name_field['value'] ?? ''),
                    'message'     => sanitize_text_field($name_field['message'] ?? ''),
                    'required'    => (bool)($name_field['required'] ?? false),
                    'required_message' => sanitize_text_field($name_field['required_message'] ?? ''),
                ];
            }
        }
        
        return $sanitized;
    }

    /**
     * Recursively sanitize array values
     * 
     * @param mixed $value Value to sanitize
     * @return mixed Sanitized value
     */
    private function sanitize_recursive($value) {
        if (!is_array($value)) {
            return sanitize_text_field($value);
        }

        return array_map(function($item) {
            return $this->sanitize_recursive($item);
        }, $value);
    }
}