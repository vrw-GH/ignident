<?php

namespace HTContactFormAdmin\Includes\Api\Endpoints;

use HTContactFormAdmin\Includes\Models\Form as FormModel;
use HTContactFormAdmin\Includes\Models\Entries;
use HTContactFormAdmin\Includes\Services\Helper;
use HTContactFormAdmin\Includes\Services\Mailer;
use HTContactFormAdmin\Includes\Services\Webhook;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use WP_Post;

/**
 * Form Submission API Handler Class
 * 
 * Handles all REST API endpoints for form submission management including CRUD operations.
 */
class Submission {
    //-------------------------------------------------------------------------
    // PROPERTIES
    //-------------------------------------------------------------------------
    
    /** @var string REST API namespace */
    private $namespace = 'ht-form/v1';

    /** @var self|null Singleton instance */
    private static $instance = null;
    
    /** @var FormModel Form model instance */
    private $form;
    /** @var Entries Form Entries instance */
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
            // Form submission - public endpoint
            [
                'endpoint' => '/submission',
                'methods'  => 'POST',
                'callback' => 'submit_form',
                'permission_callback' => [$this, 'check_permission']
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
        // Submissions are allowed for everyone, but we may add spam protection here
        return true;
    }

    //-------------------------------------------------------------------------
    // MAIN OPERATIONS
    //-------------------------------------------------------------------------
    
    /**
     * Submit form
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with submitted form data or error
     */
    public function submit_form($request) {
        // Process the submission
        try {
            $form_data = $request->get_params();
            $form_id = absint($form_data['ht_form_id']);

            // Refresh user agent from current request
            Helper::refresh_user_agent($request);

            // Get Form Data
            // Return the form if not found
            $form = $this->form->get($form_id);
            if (is_wp_error($form)) {
                return $form;
            }

            // Check form restrictions
            $settings = $form['settings'] ?? [];
            $restriction_settings = $settings->form_restriction['settings'] ?? [];
            // IP restrictions
            if(!empty($restriction_settings['enable_ip_restriction'])) {
                $ip_restrictions_check = apply_filters('ht_form_submission_ip_restrictions_check', null, $restriction_settings);
                if (is_wp_error($ip_restrictions_check)) {
                    return $ip_restrictions_check;
                }
            }
            // Country restrictions
            if(!empty($restriction_settings['enable_country_restriction'])) {
                $country_restrictions_check = apply_filters('ht_form_submission_country_restrictions_check', null, $restriction_settings);
                if (is_wp_error($country_restrictions_check)) {
                    return $country_restrictions_check;
                }
            }

            // Check honeypot field - if it's filled, it's probably a bot
            if (isset($form_data['ht_form_hp_email']) && !empty($form_data['ht_form_hp_email'])) {
                return new WP_Error(
                    'honeypot_filled',
                    __('Spam detected. Please try again.', 'ht-contactform'),
                    ['status' => 400]
                );
            }

            // Check for minimum submission time if enabled
            $settings = $form['settings'] ?? [];
            $spam_settings = $settings->spam_protection['settings'] ?? [];
            
            if (!empty($spam_settings['enable_minimum_time_to_submit']) && 
                !empty($spam_settings['minimum_time_to_submit']) && 
                !empty($form_data['ht_form_timestamp'])) {
                
                $min_seconds = (int) $spam_settings['minimum_time_to_submit'];
                $submit_timestamp = (int) $form_data['ht_form_timestamp'];
                $current_time = time();
                $elapsed_time = $current_time - $submit_timestamp;
                
                // If submission is too quick, treat as spam
                if ($elapsed_time < $min_seconds) {
                    return new WP_Error(
                        'submission_too_quick',
                        __('Submission was too quick. Please wait a moment and try again.', 'ht-contactform'),
                        ['status' => 400]
                    );
                }
            }

            // Remove honeypot and timestamp fields from the submission data
            unset($form_data['ht_form_hp_email'], $form_data['ht_form_timestamp']);

            // Verify the captcha. Whether one is required is decided by the form
            // configuration, so a request that omits the token fails here.
            $captcha_result = Helper::verify_form_captcha($form['fields'], $form_data);
            if ($captcha_result !== true) {
                return new WP_Error(
                    $captcha_result['code'],
                    $captcha_result['message'],
                    ['status' => $captcha_result['status']]
                );
            }

            // Remove captcha responses from the submission data
            unset($form_data['g-recaptcha-response']);
            unset($form_data['h-captcha-response']);
            $captcha_field = Helper::get_form_captcha_field($form['fields']);
            if (!empty($captcha_field)) {
                unset($form_data[$captcha_field['name']]);
            }

            // Sanitize Form Data
            $form_data = $this->sanitize_data($form_id, $form_data, $form['fields']);

            // Validate form data
            if (empty($form_data) || empty($form_data['form_id'])) {
                return new WP_Error(
                    'invalid_form_data',
                    __('Form data or form ID is missing', 'ht-contactform'),
                    ['status' => 400]
                );
            }

            $form_id = absint($form_data['form_id']);
            
            // Check for unique email
            // $this->check_unique_email($form, $form_data);
            
            if (is_wp_error($form)) {
                return $form;
            }
            // Basic validation
            $errors = $this->validate_data($form_data, $form);
            
            if (!empty($errors)) {
                return new WP_Error(
                    'validation_failed',
                    __('Form validation failed', 'ht-contactform'),
                    [
                        'status' => 400,
                        'errors' => $errors
                    ]
                );
            }

            $form_data = $this->handle_files_upload($form_data, $form);
            
            // Process form actions (email, storage, etc.)
            $this->process_form_actions($form_data, $form);

            $form_confirmation = (object) $form['settings']->confirmation['settings'];
            $confirmation = [
                'type'=> $form_confirmation->confirmation_type,
                'message'=> $form_confirmation->confirmation_message,
                'page' => get_permalink(absint($form_confirmation->confirmation_page)),
                'redirect'=> $form_confirmation->confirmation_redirect,
                'newTab'=> $form_confirmation->confirmation_new_tab,
            ];
            
            // Return success response
            return new WP_REST_Response([
                'success' => true,
                'confirmation' => $confirmation
            ], 200);
            
        } catch (\Exception $e) {
            return new WP_Error(
                'submission_error',
                $e->getMessage(),
                ['status' => 500]
            );
        }
    }

    
    /**
     * Sanitize form submission data
     *
     * @param array $form_data Raw form data submitted by the user
     * @return array Sanitized form data
     */
    public function sanitize_data($form_id, $form_data, $fields) {
        // Sanitize Form Data
        $sanitized_data = [];

        // Preserve form_id and reference fields
        $sanitized_data['form_id'] = $form_id;
        $sanitized_data['_wp_http_referer'] = !empty($form_data['_wp_http_referer']) ? 
            sanitize_text_field($form_data['_wp_http_referer']) : '';

        if (!empty($fields)) {
            // Loop through form fields and sanitize based on field type
            foreach ($fields as $field) {
                $field_name = $field['settings']['name_attribute'] ?? '';
                $field_type = $field['type'];
                // Skip if field doesn't exist in submission (but allow empty arrays for chained_select)
                if (empty($form_data[$field_name]) && $field_type !== 'chained_select') {
                    continue;
                }
                // For chained_select, ensure we have an array even if empty
                if ($field_type === 'chained_select' && !isset($form_data[$field_name])) {
                    continue;
                }
                
                // Sanitize based on field type
                switch ($field_type) {
                    case 'email':
                        $sanitized_data[$field_name] = sanitize_email($form_data[$field_name]);
                        break;
                        
                    case 'textarea':
                        $sanitized_data[$field_name] = sanitize_textarea_field($form_data[$field_name]);
                        break;
                        
                    case 'number':
                        $sanitized_data[$field_name] = is_numeric($form_data[$field_name]) ? 
                            floatval($form_data[$field_name]) : '';
                        break;
                        
                    case 'tel':
                    case 'phone':
                        // Basic phone sanitization (keeping only digits, plus, dashes, and parentheses)
                        $sanitized_data[$field_name] = preg_replace('/[^0-9\+\-\(\) ]/', '', $form_data[$field_name]);
                        break;
                        
                    case 'url':
                        $sanitized_data[$field_name] = sanitize_url($form_data[$field_name]);
                        break;
                        
                    case 'multiple_choices':
                    case 'checkboxes':
                    case 'radio':
                    case 'ratings':
                        // For multi-value fields like checkboxes
                        if (is_array($form_data[$field_name])) {
                            $sanitized_data[$field_name] = array_map('sanitize_text_field', $form_data[$field_name]);
                        } else {
                            $sanitized_data[$field_name] = sanitize_text_field($form_data[$field_name]);
                        }
                        break;
                        
                    case 'date':
                        // Simple date validation (basic format check)
                        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $form_data[$field_name])) {
                            $sanitized_data[$field_name] = $form_data[$field_name];
                        } else {
                            $sanitized_data[$field_name] = '';
                        }
                        break;
                        
                    case 'file':
                        // Files should be handled separately via $_FILES
                        $sanitized_data[$field_name] = '';
                        break;
                        
                    case 'name':
                        // For name fields with multiple components
                        if (is_array($form_data[$field_name])) {
                            $sanitized_data[$field_name] = [];
                            foreach ($form_data[$field_name] as $name_key => $name_value) {
                                $sanitized_data[$field_name][$name_key] = sanitize_text_field($name_value);
                            }
                        } else {
                            $sanitized_data[$field_name] = sanitize_text_field($form_data[$field_name]);
                        }
                        break;

                    case 'address':
                        // For name fields with multiple components
                        if (is_array($form_data[$field_name])) {
                            $sanitized_data[$field_name] = [];
                            foreach ($form_data[$field_name] as $name_key => $name_value) {
                                $sanitized_data[$field_name][$name_key] = sanitize_text_field($name_value);
                            }
                        } else {
                            $sanitized_data[$field_name] = sanitize_text_field($form_data[$field_name]);
                        }
                        break;

                    case 'file_upload':
                    case 'image_upload':
                        if(is_array($form_data[$field_name])) {
                            $sanitized_data[$field_name] = [];
                            foreach ($form_data[$field_name] as $file_value) {
                                $sanitized_data[$field_name][] = esc_url_raw($file_value);
                            }
                        } else {
                            $sanitized_data[$field_name] = esc_url_raw($form_data[$field_name]);
                        }
                        break;

                    case 'repeater':
                        // For repeater fields with multiple rows of sub-fields
                        if (is_array($form_data[$field_name])) {
                            $sanitized_data[$field_name] = [];
                            $sub_fields = $field['settings']['sub_fields'] ?? [];

                            foreach ($form_data[$field_name] as $row_index => $row_data) {
                                if (!is_array($row_data)) {
                                    continue;
                                }

                                $sanitized_row = [];
                                foreach ($sub_fields as $sub_field) {
                                    $sub_field_name = $sub_field['settings']['name_attribute'] ?? $sub_field['id'];

                                    if (!isset($row_data[$sub_field_name])) {
                                        continue;
                                    }

                                    $sub_field_value = $row_data[$sub_field_name];
                                    $sub_field_type = $sub_field['type'];

                                    // Sanitize based on sub-field type
                                    switch ($sub_field_type) {
                                        case 'email':
                                            $sanitized_row[$sub_field_name] = sanitize_email($sub_field_value);
                                            break;
                                        case 'textarea':
                                            $sanitized_row[$sub_field_name] = sanitize_textarea_field($sub_field_value);
                                            break;
                                        case 'number':
                                            $sanitized_row[$sub_field_name] = is_numeric($sub_field_value) ?
                                                floatval($sub_field_value) : '';
                                            break;
                                        case 'url':
                                            $sanitized_row[$sub_field_name] = sanitize_url($sub_field_value);
                                            break;
                                        case 'tel':
                                        case 'phone':
                                            $sanitized_row[$sub_field_name] = preg_replace('/[^0-9\+\-\(\) ]/', '', $sub_field_value);
                                            break;
                                        case 'checkboxes':
                                        case 'multiple_choices':
                                            if (is_array($sub_field_value)) {
                                                $sanitized_row[$sub_field_name] = array_map('sanitize_text_field', $sub_field_value);
                                            } else {
                                                $sanitized_row[$sub_field_name] = sanitize_text_field($sub_field_value);
                                            }
                                            break;
                                        default:
                                            $sanitized_row[$sub_field_name] = sanitize_text_field($sub_field_value);
                                            break;
                                    }
                                }

                                $sanitized_data[$field_name][] = $sanitized_row;
                            }
                        }
                        break;

                    case 'post_select':
                        // Sanitize post selection value (post title)
                        $sanitized_data[$field_name] = sanitize_text_field($form_data[$field_name]);
                        break;

                    case 'color':
                        // Validate and sanitize hex color value
                        $color_value = $form_data[$field_name];
                        if (preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color_value)) {
                            $sanitized_data[$field_name] = sanitize_hex_color($color_value);
                        } else {
                            $sanitized_data[$field_name] = '#000000';
                        }
                        break;

                    case 'nps':
                        // Validate NPS score (0-10)
                        $nps_value = absint($form_data[$field_name]);
                        if ($nps_value >= 0 && $nps_value <= 10) {
                            $sanitized_data[$field_name] = $nps_value;
                        } else {
                            $sanitized_data[$field_name] = 0;
                        }
                        break;

                    case 'richtext':
                        $content = $form_data[$field_name];

                        // Primary sanitization: wp_kses decodes HTML entities, strips control
                        // characters (0x00-0x1F), and checks href protocol against an explicit
                        // allowlist — covers newline/entity/unquoted bypass vectors that regex cannot.
                        $allowed_html = [
                            'p'          => [ 'class' => true, 'style' => true ],
                            'br'         => [],
                            'strong'     => [],
                            'b'          => [],
                            'em'         => [],
                            'i'          => [],
                            'u'          => [],
                            's'          => [],
                            'strike'     => [],
                            'a'          => [ 'href' => true, 'target' => true, 'rel' => true, 'class' => true ],
                            'ul'         => [],
                            'ol'         => [],
                            'li'         => [ 'class' => true ],
                            'h1'         => [ 'class' => true, 'style' => true ],
                            'h2'         => [ 'class' => true, 'style' => true ],
                            'h3'         => [ 'class' => true, 'style' => true ],
                            'h4'         => [ 'class' => true, 'style' => true ],
                            'blockquote' => [ 'class' => true ],
                            'pre'        => [ 'class' => true ],
                            'code'       => [ 'class' => true ],
                            'span'       => [ 'class' => true, 'style' => true ],
                            'button'     => [],
                        ];
                        $content = wp_kses( $content, $allowed_html, [ 'http', 'https', 'mailto', 'tel' ] );

                        // Step 4: Sanitize style attributes - only allow safe CSS properties
                        $content = preg_replace_callback(
                            '/style\s*=\s*"([^"]*)"/i',
                            function($matches) {
                                $style = $matches[1];
                                $safe_styles = [];

                                // Allow color (but not inside background-color match)
                                if (preg_match('/(?<![a-z-])color\s*:\s*([^;]+)/i', $style, $match)) {
                                    $value = trim($match[1]);
                                    // Only allow rgb(), rgba(), hex colors, and color names
                                    if (preg_match('/^(rgb\s*\([^)]+\)|rgba\s*\([^)]+\)|#[a-fA-F0-9]{3,8}|[a-zA-Z]+)$/i', $value)) {
                                        $safe_styles[] = 'color: ' . $value;
                                    }
                                }

                                // Allow background-color
                                if (preg_match('/background-color\s*:\s*([^;]+)/i', $style, $match)) {
                                    $value = trim($match[1]);
                                    if (preg_match('/^(rgb\s*\([^)]+\)|rgba\s*\([^)]+\)|#[a-fA-F0-9]{3,8}|[a-zA-Z]+)$/i', $value)) {
                                        $safe_styles[] = 'background-color: ' . $value;
                                    }
                                }

                                // Allow text-align
                                if (preg_match('/text-align\s*:\s*(left|center|right|justify)/i', $style, $match)) {
                                    $safe_styles[] = 'text-align: ' . strtolower($match[1]);
                                }

                                return empty($safe_styles) ? '' : 'style="' . esc_attr(implode('; ', $safe_styles)) . '"';
                            },
                            $content
                        );

                        // Step 5: Sanitize class names - only allow Quill's classes
                        $content = preg_replace_callback(
                            '/class\s*=\s*"([^"]*)"/i',
                            function($matches) {
                                $allowed_classes = ['ql-align-center', 'ql-align-right', 'ql-align-justify', 'ql-indent-1', 'ql-indent-2', 'ql-indent-3', 'ql-indent-4', 'ql-indent-5', 'ql-indent-6', 'ql-indent-7', 'ql-indent-8', 'ql-code-block'];
                                $classes = explode(' ', $matches[1]);
                                $safe_classes = array_intersect($classes, $allowed_classes);
                                return empty($safe_classes) ? '' : 'class="' . esc_attr(implode(' ', $safe_classes)) . '"';
                            },
                            $content
                        );

                        // Step 6: Validate max_length if set
                        $max_length = $field['settings']['max_length'] ?? 0;
                        if ($max_length > 0) {
                            $text_content = wp_strip_all_tags($content);
                            if (mb_strlen($text_content) > $max_length) {
                                // Truncate to max length by removing content from end
                                // Note: This is a fallback; frontend should enforce this
                                $content = mb_substr($content, 0, $max_length * 3); // Approximate HTML overhead
                            }
                        }

                        $sanitized_data[$field_name] = $content;
                        break;

                    case 'signature':
                        // Handle signature as base64 data URL
                        $signature_data = $form_data[$field_name];
                        if (!empty($signature_data) && preg_match('/^data:image\/(png|jpeg|jpg);base64,/', $signature_data)) {
                            // Save signature to media library
                            $upload_result = $this->save_signature_to_media_library($signature_data, $form_id);
                            if (!empty($upload_result['url'])) {
                                $sanitized_data[$field_name] = esc_url_raw($upload_result['url']);
                            } else {
                                $sanitized_data[$field_name] = '';
                            }
                        } else {
                            $sanitized_data[$field_name] = '';
                        }
                        break;

                    case 'chained_select':
                        // Handle chained select as array of level values
                        if (is_array($form_data[$field_name])) {
                            $sanitized_data[$field_name] = [];
                            foreach ($form_data[$field_name] as $key => $val) {
                                $sanitized_data[$field_name][sanitize_key($key)] = sanitize_text_field($val);
                            }
                        } else {
                            $sanitized_data[$field_name] = [];
                        }
                        break;

                    // Default sanitization for text and other field types
                    default:
                        $sanitized_data[$field_name] = sanitize_text_field($form_data[$field_name]);
                        break;
                }
            }
        } else {
            // If we can't get the form structure, do basic sanitization on all fields
            foreach ($form_data as $key => $value) {
                if ($key === 'ht_form_id') {
                    $sanitized_data[$key] = absint($value);
                } else if (is_array($value)) {
                    $sanitized_data[$key] = array_map('sanitize_text_field', $value);
                } else {
                    $sanitized_data[$key] = sanitize_text_field($value);
                }
            }
        }
        return $sanitized_data;
    }

    private function get_field_setting_value($settings, $type) {
        return array_reduce($settings, function($carry, $setting) use ($type) {
            return $setting['type'] === $type ? $setting['value'] : $carry;
        }, '');
    }

    /**
     * Save signature base64 data to WordPress media library
     *
     * @param string $base64_data Base64 encoded image data URL
     * @param int $form_id Form ID for generating filename
     * @return array Array with 'url' and 'file' keys, or empty array on failure
     */
    private function save_signature_to_media_library($base64_data, $form_id) {
        // Extract the base64 data
        $data = explode(',', $base64_data);
        if (count($data) !== 2) {
            return [];
        }

        // Decode the base64 data
        $decoded = base64_decode($data[1]);
        if (!$decoded) {
            return [];
        }

        // Generate unique filename with microtime for better uniqueness
        $filename = 'signature_' . absint($form_id) . '_' . str_replace('.', '', (string) microtime(true)) . '.png';

        // Use WordPress upload function
        $upload = wp_upload_bits($filename, null, $decoded);

        if (!empty($upload['error'])) {
            return [];
        }

        // Create attachment in media library
        $attachment = [
            'post_mime_type' => 'image/png',
            'post_title' => sanitize_file_name($filename),
            'post_status' => 'inherit',
        ];

        $attach_id = wp_insert_attachment($attachment, $upload['file']);

        if (!is_wp_error($attach_id)) {
            // Generate attachment metadata
            require_once ABSPATH . 'wp-admin/includes/image.php';
            $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
            wp_update_attachment_metadata($attach_id, $attach_data);
        }

        return $upload;
    }

    /**
     * Validate form submission data against form configuration
     * 
     * @param array $form_data Submitted form data
     * @param array $form Form configuration
     * @return array Array of validation errors (field_name => error_message)
     */
    public function validate_data($form_data, $form) {
        $errors = [];
        $fields = $form['fields'] ?? [];
        
        foreach ($fields as $field) {
            $field_name = $field['settings']['name_attribute'] ?? '';
            $field_label = $field['settings']['label'] ?? $field_name;
            $is_required = !empty($field['settings']['required']);
            
            // Skip if field is not required
            if (!$is_required) {
                continue;
            }

            // Rich Text required validation - strip HTML to check actual content
            if ($field['type'] === 'richtext' && $is_required) {
                $value = wp_strip_all_tags($form_data[$field_name] ?? '');
                if (empty(trim($value))) {
                    $errors[$field_name] = sprintf(
                        /* translators: %s: field label */
                        __('%s is required.', 'ht-contactform'),
                        $field_label
                    );
                    continue;
                }
            }

            // Signature required validation - check for valid signature data
            // After sanitization, signature is saved to media library and becomes a URL
            if ($field['type'] === 'signature' && $is_required) {
                $value = $form_data[$field_name] ?? '';
                // Accept either base64 data URL (pre-sanitization) or saved image URL (post-sanitization)
                $is_valid = !empty($value) && (
                    preg_match('/^data:image\/(png|jpeg|jpg);base64,/', $value) ||
                    filter_var($value, FILTER_VALIDATE_URL)
                );
                if (!$is_valid) {
                    $errors[$field_name] = sprintf(
                        /* translators: %s: field label */
                        __('%s is required.', 'ht-contactform'),
                        $field_label
                    );
                    continue;
                }
            }

            // Chained Select required validation - check if all levels have values
            if ($field['type'] === 'chained_select' && $is_required) {
                $values = $form_data[$field_name] ?? [];
                if (!is_array($values) || empty(array_filter($values))) {
                    $errors[$field_name] = sprintf(
                        /* translators: %s: field label */
                        __('%s is required.', 'ht-contactform'),
                        $field_label
                    );
                    continue;
                }
            }

            // Check required fields (generic - for other field types)
            if ($is_required && (!isset($form_data[$field_name]) || $form_data[$field_name] === '')) {
                $errors[$field_name] = sprintf(
                    /* translators: %s: field label */
                    __('%s is required.', 'ht-contactform'),
                    $field_label
                );
                continue;
            }
            
            // Validate email format
            if ($field['type'] === 'email' && !empty($form_data[$field_name])) {
                if (!is_email($form_data[$field_name])) {
                    $errors[$field_name] = sprintf(
                        /* translators: %s: field label */
                        __('%s must be a valid email address.', 'ht-contactform'),
                        $field_label
                    );
                }
            }

            // Validate repeater field
            if ($field['type'] === 'repeater') {
                $sub_fields = $field['settings']['sub_fields'] ?? [];

                // Check if repeater data exists
                $repeater_data = $form_data[$field_name] ?? [];

                if (!is_array($repeater_data)) {
                    $repeater_data = [];
                }

                // Validate sub-fields in each row
                foreach ($repeater_data as $row_index => $row_data) {
                    if (!is_array($row_data)) {
                        continue;
                    }

                    foreach ($sub_fields as $sub_field) {
                        $sub_field_name = $sub_field['settings']['name_attribute'] ?? $sub_field['id'];
                        $sub_field_label = $sub_field['settings']['label'] ?? $sub_field_name;
                        $sub_is_required = !empty($sub_field['settings']['required']);

                        // Check required sub-fields
                        if ($sub_is_required && (!isset($row_data[$sub_field_name]) || $row_data[$sub_field_name] === '')) {
                            $row_number = $row_index + 1;
                            $errors["{$field_name}[{$row_index}][{$sub_field_name}]"] = sprintf(
                                /* translators: 1: sub-field label, 2: row number */
                                __('%1$s is required in row %2$d.', 'ht-contactform'),
                                $sub_field_label,
                                $row_number
                            );
                        }

                        // Validate email format in sub-fields (if email_validation is enabled)
                        if ($sub_field['type'] === 'email' && !empty($row_data[$sub_field_name])) {
                            $email_validation_enabled = !empty($sub_field['settings']['email_validation']) || !empty($sub_field['email_validation']);
                            if ($email_validation_enabled && !is_email($row_data[$sub_field_name])) {
                                $row_number = $row_index + 1;
                                $errors["{$field_name}[{$row_index}][{$sub_field_name}]"] = sprintf(
                                    /* translators: 1: sub-field label, 2: row number */
                                    __('%1$s must be a valid email address in row %2$d.', 'ht-contactform'),
                                    $sub_field_label,
                                    $row_number
                                );
                            }
                        }
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Handle file uploads
     *
     * @param array $form_data Form data
     * @param array $form Form configuration
     * @return array Array of uploaded files
     */
    public function handle_files_upload($form_data, $form) {
        $upload_dir = wp_upload_dir();

        // Get draft_key from form data if resuming a saved draft
        $draft_key = $form_data['ht_form_draft_key'] ?? null;

        foreach ($form['fields'] as $field) {
            if ($field['type'] === 'file_upload' || $field['type'] === 'image_upload') {
                $destination = $field['settings']['upload_location'] ?? 'ht_form_default';
                $field_name = $field['settings']['name_attribute'];

                if (isset($form_data[$field_name])) {
                    $files = $form_data[$field_name];
                    if (!empty($files)) {
                        foreach ($files as $key => $file_value) {
                            // Extract filename from URL if it's a full URL (from resumed draft)
                            $file_name = $this->extract_filename($file_value);
                            $file_name = sanitize_file_name($file_name);

                            // Find the file in temp or drafts folder
                            $source_path = $this->find_file_source($file_name, $draft_key, $upload_dir);

                            if ($source_path) {
                                $form_data[$field_name][$key] = $this->upload_file_from_path($source_path, $file_name, $destination);
                            } else {
                                $form_data[$field_name][$key] = '';
                            }
                        }
                    }
                }
            }
        }
        return $form_data;
    }

    /**
     * Extract filename from value (could be filename or URL)
     *
     * @param string $file_value File value (filename or URL)
     * @return string Extracted filename
     */
    private function extract_filename($file_value) {
        if (strpos($file_value, 'http://') === 0 || strpos($file_value, 'https://') === 0) {
            $parsed = wp_parse_url($file_value);
            return basename($parsed['path'] ?? $file_value);
        }
        return $file_value;
    }

    /**
     * Find file in temp or drafts folder
     *
     * @param string $file_name File name
     * @param string|null $draft_key Draft key for resumed forms
     * @param array $upload_dir WordPress upload directory info
     * @return string|null Full path to file or null if not found
     */
    private function find_file_source($file_name, $draft_key, $upload_dir) {
        // First check temp folder (new uploads)
        $temp_path = $upload_dir['basedir'] . '/ht_form/temp/' . $file_name;
        if (file_exists($temp_path)) {
            return $temp_path;
        }

        // Then check drafts folder (resumed files)
        if ($draft_key) {
            $draft_key = sanitize_file_name($draft_key);
            $draft_path = $upload_dir['basedir'] . '/ht_form/drafts/' . $draft_key . '/' . $file_name;
            if (file_exists($draft_path)) {
                return $draft_path;
            }
        }

        return null;
    }

    /**
     * Upload file to media library or default directory from a source path
     *
     * @param string $source_path Full path to source file
     * @param string $file_name File name with extension
     * @param string $destination Destination type (media_library or ht_form_default)
     * @return string|false URL of uploaded file or false on failure
     */
    public function upload_file_from_path($source_path, $file_name, $destination) {
        $upload_dir = wp_upload_dir();

        if ($destination === 'media_library') {
            // === ATTACH TO MEDIA LIBRARY ===
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';

            $file = [
                'name' => basename($file_name),
                'tmp_name' => $source_path,
                'type' => mime_content_type($source_path),
                'error' => 0,
                'size' => filesize($source_path),
            ];
            $attachment_id = media_handle_sideload($file, 0);
            if (is_wp_error($attachment_id)) {
                return $attachment_id->get_error_message();
            } else {
                @unlink($source_path);
                return wp_get_attachment_url($attachment_id);
            }
        } elseif ($destination === 'ht_form_default') {
            $dest_dir = $upload_dir['basedir'] . '/ht_form';
            if (!file_exists($dest_dir)) {
                wp_mkdir_p($dest_dir);
            }
            $unique_name = wp_unique_filename($dest_dir, $file_name);
            $file_path = "$dest_dir/$unique_name";
            if (rename($source_path, $file_path)) {
                // Return URL instead of file path
                return $upload_dir['baseurl'] . '/ht_form/' . $unique_name;
            }
        }
        return false;
    }

    /**
     * Upload file to media library or default directory (legacy method)
     *
     * @param string $file_name File name with extension
     * @param string $destination Destination directory
     * @return int|string Attachment ID or file path
     * @deprecated Use upload_file_from_path instead
     */
    public function upload_file($file_name, $destination) {
        $upload_dir = wp_upload_dir();
        $temp_file = $upload_dir['basedir'] . '/ht_form/temp/' . $file_name;
        return $this->upload_file_from_path($temp_file, $file_name, $destination);
    }

    /**
     * Process form actions (send email, store submission, etc.)
     * 
     * @param array $form_data Submitted form data
     * @param array $form Form configuration
     */
    public function process_form_actions($form_data, $form) {
        $settings = $form['settings'] ?? [];
        $global = get_option('ht_form_global_settings', []);
        $meta = [
            'user_id'     => get_current_user_id(),
            'ip_address'  => !empty($global['miscellaneous']['disable_ip_logging']) ? '' : Helper::get_ip(),
            'browser'     => Helper::get_browser(),
            'device'      => Helper::get_device(),
            'created_at'  => current_time('mysql'),
            'updated_at'  => current_time('mysql')
        ];
        
        // Send email notification if enabled
        if (!empty($settings->notification['settings']['enable_notification'])) {
            $mailer = Mailer::get_instance($form, $form_data, $meta);
            $mailer->send();
        }
        
        // Store submission in database if enabled
        if (!empty($settings->general['settings']['store_submissions'])) {
            $result = $this->store_submission($form_data, $meta);
            if (is_wp_error($result)) {
                return $result;
            }
        }
        
        // Run action hook for third-party integrations
        do_action('ht_form/after_submission', $form, $form_data, $meta);
    }

    /**
     * Store form submission in database
     * 
     * @param array $form_data Submitted form data
     * @param array $meta Entry metadata
     * @return int|WP_Error New entry ID or error
     */
    private function store_submission($form_data, $meta) {
        $result = $this->entries->create($form_data, $meta);
        if (is_wp_error($result)) {
            return $result;
        }
        
        return $result;
    }
}