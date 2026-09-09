<?php
/**
 * Frontend Form Fields Handler
 */

namespace HTContactForm\UI;

use HTContactFormAdmin\Includes\Services\Helper;

// If this file is accessed directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fields class
 * Handles field rendering functionality for displaying forms on the frontend
 */
class Fields {
    /**
     * Singleton instance
     *
     * @var object
     */
    private static $instance = null;

    /**
     * Form icons
     *
     * @var array
     */
    protected $icons = [
        'info' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>',
    ];

    /**
     * Global settings
     *
     * @var array
     */
    protected $global_settings;
    protected $helper;

    protected $file_types = [
        'image' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/jpg',
        ],
        'audio' => [
            'audio/mpeg',
            'audio/wav',
            'audio/ogg',
            'audio/oga',
            'audio/wma',
            'audio/mka',
            'audio/m4a',
            'audio/ra',
            'audio/mid',
            'audio/midi',
        ],
        'video' => [
            'video/mp4',
            'video/mpeg',
            'video/ogg',
            'video/avi',
            'video/divx',
            'video/flv',
            'video/mov',
            'video/ogv',
            'video/mkv',
            'video/m4v',
            'video/mpg',
            'video/mpeg',
            'video/mpe',
        ],
        'pdf' => [
            'application/pdf',
        ],
        'doc' => [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'text/plain',
        ],
        'zip' => [
            'application/zip',
            'application/x-zip-compressed',
            'application/x-rar-compressed',
            'application/rar',
            'application/x-7z-compressed',
            'application/gzip',
            'application/x-gzip',
        ],
        'exe' => [
            'application/exe',
            'application/x-exe',
        ],
        'csv' => [
            'text/csv',
        ],
    ];

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
        $this->global_settings = get_option('ht_form_global_settings', []);
        $this->helper = Helper::get_instance();
    }

    /**
     * Render individual field
     *
     * @param array $classes Field classes
     * @param string $field_type Field type
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @param bool $allow_condition Allow condition attribute
     * @return string
     */
    public function render_field($classes, $field_type, $field_id, $settings, $allow_condition = true) {
        $help_message_pos = $this->global_settings['layout']['help_message_placement'] ?? 'below_input_element';
        if (!empty($settings['help_message_position'])) {
            $help_message_pos = sanitize_text_field($settings['help_message_position']);
        }
        $error_message_placement = $this->global_settings['layout']['error_message_placement'] ?? 'below_input_element';

        $allow_conditional_match = null;
        $conditional_logic = null;
        if(!empty($settings['enable_condition']) && $allow_condition) {
            $conditional_match = $settings['conditional_match'];
            $conditional_logic = json_encode($settings['conditional_logic']);
        }
        return sprintf(
            '<div data-id="%1$s" class="%2$s" %3$s %4$s>
                <div class="ht-form-elem-inner">
                    <div class="ht-form-elem-head">%5$s%6$s%7$s</div>
                    <div class="ht-form-elem-content">
                        %8$s
                        %9$s
                        %10$s
                    </div>
                </div>
                %11$s
                %12$s
            </div>',
            esc_attr($field_id),
            implode(' ', array_filter($classes)),
            !empty($settings['enable_condition']) && $allow_condition ? "data-condition-match=" . esc_attr($conditional_match) : '',
            !empty($settings['enable_condition']) && $allow_condition ? "data-condition-logic=" . esc_attr($conditional_logic) : '',
            !empty($settings['label']) ? $this->field_label($field_id, $settings) : '',
            $help_message_pos === 'next_to_label' ? $this->field_message_tooltip($settings) : '',
            $error_message_placement === 'below_label'? '<span class="ht-form-elem-error"></span>' :'',
            $this->field_prefix($settings),
            method_exists($this, "field_$field_type") ? call_user_func([$this, "field_$field_type"], $field_id, $settings) : '',
            $this->field_suffix($settings),
            $help_message_pos === 'below_input_element' ? $this->field_message($settings) : '',
            $error_message_placement === 'below_input_element' ? '<span class="ht-form-elem-error"></span>' :'',
        );
    }

    /**
     * Render field label
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_label($field_id, $settings) {
        $classes = ['ht-form-elem-label'];
        if(!empty($settings['required'])) {
            $classes[] = 'ht-form-elem-is-required';
        }
        if(!empty($settings['hide_label'])) {
            $classes[] = 'ht-form-elem-label-hidden';
            return '';
        }
        return sprintf(
            '<label for="%1$s" class="' . implode(' ', $classes) . '" aria-label="%2$s">%3$s</label>',
            esc_attr($field_id),    
            esc_attr($settings['label']),
            esc_html($settings['label'])
        );
    }
    
    /**
     * Render field prefix
     *
     * @param array $settings Field settings
     * @return string
     */
    public function field_prefix($settings) {
        if(empty($settings['prefix_label'])) {
            return '';
        }
        $classes = ['ht-form-elem-prefix'];
        return sprintf(
            '<span class="' . implode(' ', $classes) . '">%s</span>',
            esc_html($settings['prefix_label'])
        );
    }
    
    /**
     * Render field suffix
     *
     * @param array $settings Field settings
     * @return string
     */
    public function field_suffix($settings) {
        if(empty($settings['suffix_label'])) {
            return '';
        }
        $classes = ['ht-form-elem-suffix'];
        return sprintf(
            '<span class="' . implode(' ', $classes) . '">%s</span>',
            esc_html($settings['suffix_label'])
        );
    }

    /**
     * Render field help message
     *
     * @param array $settings Field settings
     * @return string
     */
    public function field_message($settings) {
        if(empty($settings['help_message'])) {
            return '';
        }
        $classes = ['ht-form-elem-help'];
        return sprintf(
            '<p class="' . implode(' ', $classes) . '">%s</p>',
            esc_html($settings['help_message'])
        );
    }

    /**
     * Render field tooltip help message
     *
     * @param array $settings Field settings
     * @return string
     */
    public function field_message_tooltip($settings) {
        if(empty($settings['help_message'])) {
            return '';
        }
        $classes = ['ht-form-elem-help-tooltip'];
        return sprintf(
            '<span class="' . implode(' ', $classes) . '">%s<span class="ht-form-elem-help-tooltip-text">%s</span></span>',
            $this->icons['info'],
            esc_html($settings['help_message'])
        );
    }
    
    /**
     * Render name field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_name($field_id, $settings) {
        // Create field wrapper
        $wrapper_classes = [
            'ht-form-elem',
            'ht-form-elem-input-field',
            !empty($settings['field_size']) ? 'ht-form-elem-' . sanitize_html_class($settings['field_size']) : 'medium',
        ];

        if (!empty($settings['field_class'])) {
            $wrapper_classes[] = sanitize_html_class($settings['field_class']);
        }

        if(empty($settings['label_position']) && empty($this->global_settings['layout']['label_position'])) {
            $wrapper_classes[] = 'ht-form-elem-label-top';
        } else if(empty($settings['label_position']) && !empty($this->global_settings['layout']['label_position'])) {
            $wrapper_classes[] = 'ht-form-elem-label-' . sanitize_html_class($this->global_settings['layout']['label_position']);
        } else if(!empty($settings['label_position'])) {
            $wrapper_classes[] = 'ht-form-elem-label-' . sanitize_html_class($settings['label_position']);
        }

        $names = [];

        if($settings['name_format'] === 'simple') {
            $settings['label'] = $settings['names']['simple']['label'];
            $settings['placeholder'] = $settings['names']['simple']['placeholder'];
            $settings['default_value'] = $settings['names']['simple']['value'];
            $settings['help_message'] = $settings['names']['simple']['message'];
            $settings['required'] = $settings['names']['simple']['required'];
            $settings['required_message'] = $settings['names']['simple']['required_message'];
            $names[] = $this->render_field($wrapper_classes, 'input', "{$field_id}_simple", $settings, false);
        }

        if($settings['name_format'] === 'first_last' || $settings['name_format'] === 'first_middle_last') {

            $first['label'] = $settings['names']['first_name']['label'];
            $first['placeholder'] = $settings['names']['first_name']['placeholder'];
            $first['default_value'] = $settings['names']['first_name']['value'];
            $first['help_message'] = $settings['names']['first_name']['message'];
            $first['required'] = $settings['names']['first_name']['required'];
            $first['required_message'] = $settings['names']['first_name']['required_message'];
            $first['name_attribute'] = $settings['name_attribute'] . '[first_name]';
            $names[] = $this->render_field($wrapper_classes, 'input', "{$field_id}_first_name", array_merge($settings, $first), false);

            if($settings['name_format'] === 'first_middle_last') {
                $middle['label'] = $settings['names']['middle_name']['label'];
                $middle['placeholder'] = $settings['names']['middle_name']['placeholder'];
                $middle['default_value'] = $settings['names']['middle_name']['value'];
                $middle['help_message'] = $settings['names']['middle_name']['message'];
                $middle['required'] = $settings['names']['middle_name']['required'];
                $middle['required_message'] = $settings['names']['middle_name']['required_message'];
                $middle['name_attribute'] = $settings['name_attribute'] . '[middle_name]';
                $names[] = $this->render_field($wrapper_classes, 'input', "{$field_id}_middle_name", array_merge($settings, $middle), false);
            }

            $last['label'] = $settings['names']['last_name']['label'];
            $last['placeholder'] = $settings['names']['last_name']['placeholder'];
            $last['default_value'] = $settings['names']['last_name']['value'];
            $last['help_message'] = $settings['names']['last_name']['message'];
            $last['required'] = $settings['names']['last_name']['required'];
            $last['required_message'] = $settings['names']['last_name']['required_message'];
            $last['name_attribute'] = $settings['name_attribute'] . '[last_name]';
            $names[] = $this->render_field($wrapper_classes, 'input', "{$field_id}_last_name", array_merge($settings, $last), false);

        }

        $conditional_match = null;
        $conditional_logic = null;
        if(!empty($settings['enable_condition'])) {
            $conditional_match = $settings['conditional_match'];
            $conditional_logic = json_encode($settings['conditional_logic']);
        }

        return sprintf('<div data-id="%1$s" class="ht-form-elem ht-form-elem-group ht-form-elem-name" %2$s %3$s>%4$s</div>',
            esc_attr($field_id),
            !empty($settings['enable_condition']) ? "data-condition-match=" . esc_attr($conditional_match) : '',
            !empty($settings['enable_condition']) ? "data-condition-logic=" . esc_attr($conditional_logic) : '',
            implode('', $names)
        );
    }   

    /**
     * Render email field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_email($field_id, $settings) {
        $attributes = [
            'type' => 'email',
            'id' => $field_id,
            'class' => 'ht-form-elem-input',
            'value' => !empty($settings['default_value']) ? $settings['default_value'] : '',
            'placeholder' => !empty($settings['placeholder']) ? $settings['placeholder'] : '',
            'required' => !empty($settings['required']) ? true : false,
            'data-required-message' => !empty($settings['required']) && !empty($settings['required_message']) ? $settings['required_message'] : '',
            'data-email-validation' => !empty($settings['email_validation']) ? $settings['email_validation'] : '',
            'data-email-validation-message' => !empty($settings['email_validation']) && !empty($settings['email_validation_message']) ? $settings['email_validation_message'] : '',
            'name' => !empty($settings['name_attribute']) ? $settings['name_attribute'] : '',
            'data-email-unique' => !empty($settings['email_unique']) ? true : false,
        ];
        // Build attribute string
        $attributes_string = '';
        foreach ($attributes as $key => $value) {
            if($value) {
                $attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
            }
        }
        return sprintf(
            '<input %s/>',
            $attributes_string
        );
    }

    /**
     * Render website url field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_url($field_id, $settings) {
        $attributes = [
            'type' => 'url',
            'id' => $field_id,
            'class' => 'ht-form-elem-input',
            'value' => !empty($settings['default_value']) ? $settings['default_value'] : '',
            'placeholder' => !empty($settings['placeholder']) ? $settings['placeholder'] : '',
            'required' => !empty($settings['required']) ? true : false,
            'data-required-message' => !empty($settings['required']) && !empty($settings['required_message']) ? $settings['required_message'] : '',
            'name' => !empty($settings['name_attribute']) ? $settings['name_attribute'] : '',
            'data-validate' => !empty($settings['validate_url']) ? true : false,
            'data-validation-message' =>  !empty($settings['validate_url']) && !empty($settings['validate_url_message']) ? $settings['validate_url_message'] : '',
        ];
        // Build attribute string
        $attributes_string = '';
        foreach ($attributes as $key => $value) {
            if($value) {
                $attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
            }
        }
        return sprintf(
            '<input %s/>',
            $attributes_string
        );
    }

    /**
     * Render hidden field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_hidden($field_id, $settings) {
        $attributes = [
            'type' => 'hidden',
            'id' => $field_id,
            'class' => 'ht-form-elem-input',
            'value' => !empty($settings['default_value']) ? $settings['default_value'] : '',
            'name' => !empty($settings['name_attribute']) ? $settings['name_attribute'] : '',
            'readonly' => true,
        ];
        // Build attribute string
        $attributes_string = '';
        foreach ($attributes as $key => $value) {
            if($value) {
                $attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
            }
        }
        return sprintf(
            '<input %s/>',
            $attributes_string
        );
    }

    /**
     * Render Custom HTML field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_custom_html($field_id, $settings) {
        $content = !empty($settings['html']) ? $settings['html'] : '';
        $content = $this->helper->filter_vars($content);
        if(str_contains($content, '{input.')) {
            // Match all {input.something} patterns
            preg_match_all('/{input\.[^}]+}/', $content, $matches);
            
            if (!empty($matches[0])) {
                foreach ($matches[0] as $match) {
                    // Extract the field name from the tag (e.g., {input.email} -> email)
                    $field_name = str_replace(['{input.', '}'], '', $match);
                    // Convert dot notation to array notation (e.g., name.first_name.nested -> name[first_name][nested])
                    if (strpos($field_name, '.') !== false) {
                        $parts = explode('.', $field_name);
                        $base = array_shift($parts);
                        $field_name = $base;
                        foreach ($parts as $part) {
                            $field_name .= "[$part]";
                        }
                    }
                    
                    // Create a span with data attribute to reference this field
                    $span = '<span class="ht-form-elem-dynamic" data-ref="' . esc_attr($field_name) . '"></span>';
                    
                    // Replace the tag with the span
                    $content = str_replace($match, $span, $content);
                }
            }
        }
        return wp_kses_post("<div class=\"ht-form-elem-custom-html\">$content</div>");
    }

    /**
     * Render input field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_input($field_id, $settings) {
        $attributes = [
            'type' => 'text',
            'id' => $field_id,
            'class' => 'ht-form-elem-input',
            'value' => !empty($settings['default_value']) ? $settings['default_value'] : '',
            'placeholder' => !empty($settings['placeholder']) ? $settings['placeholder'] : '',
            'required' => !empty($settings['required']) ? true : false,
            'data-required-message' => !empty($settings['required']) && !empty($settings['required_message']) ? $settings['required_message'] : '',
            'name' => !empty($settings['name_attribute']) ? $settings['name_attribute'] : '',
            'maxlength' => !empty($settings['max_length']) ? $settings['max_length'] : '',
        ];
        // Build attribute string
        $attributes_string = '';
        foreach ($attributes as $key => $value) {
            if($value) {
                $attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
            }
        }
        return sprintf(
            '<input %s/>',
            $attributes_string
        );
    }

    /**
     * Render password field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_password($field_id, $settings) {
        $attributes = [
            'type' => 'password',
            'id' => $field_id,
            'class' => 'ht-form-elem-input',
            'value' => !empty($settings['default_value']) ? $settings['default_value'] : '',
            'placeholder' => !empty($settings['placeholder']) ? $settings['placeholder'] : '',
            'required' => !empty($settings['required']) ? true : false,
            'data-required-message' => !empty($settings['required']) && !empty($settings['required_message']) ? $settings['required_message'] : '',
            'name' => !empty($settings['name_attribute']) ? $settings['name_attribute'] : '',
        ];
        // Build attribute string
        $attributes_string = '';
        foreach ($attributes as $key => $value) {
            if($value) {
                $attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
            }
        }
        return sprintf(
            '<input %s/>',
            $attributes_string
        );
    }

    /**
     * Render date time field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_date_time($field_id, $settings) {
        $attributes = [
            'type' => 'text',
            'id' => $field_id,
            'class' => 'ht-form-elem-input ht-form-elem-datetime',
            'value' => !empty($settings['default_value']) ? $settings['default_value'] : '',
            'placeholder' => !empty($settings['placeholder']) ? $settings['placeholder'] : '',
            'required' => !empty($settings['required']) ? true : false,
            'data-required-message' => !empty($settings['required']) && !empty($settings['required_message']) ? $settings['required_message'] : '',
            'name' => !empty($settings['name_attribute']) ? $settings['name_attribute'] : '',
            'data-format' => !empty($settings['format']) ? $settings['format'] : '',
            'data-range' => !empty($settings['range']) ? true : false,
            'data-multiple' => !empty($settings['multiple']) ? true : false,
        ];
        // Build attribute string
        $attributes_string = '';
        foreach ($attributes as $key => $value) {
            if($value) {
                $attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
            }
        }
        return sprintf(
            '<input %s/>',
            $attributes_string
        );
    }

    /**
     * Render phone/tel field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_phone($field_id, $settings) {
        $validate = !empty($settings['validate']) ? true : false;
        $validate_message = !empty($settings['validate_message']) ? $settings['validate_message'] : '';
        $classes = [
            'ht-form-elem-input',
            $validate ? 'ht-form-elem-input-tel' : '',
        ];
        $initial_country = $validate && !empty($settings['default_country']) ? $settings['default_country'] : '';
        $exclude_countries = $validate && !empty($settings['country_list_type']) && $settings['country_list_type'] === 'exclude' ? implode(',', $settings['country_list']) : '';
        $only_countries = $validate && !empty($settings['country_list_type']) && $settings['country_list_type'] === 'include' ? implode(',', $settings['country_list']) : '';

        if(!empty($settings['auto_country_select'])) {
            $geo_data = $this->helper->get_geolocation_data($this->helper->get_ip());
            $initial_country = !empty($geo_data['country']) ? $geo_data['country'] : $initial_country;
        }
        $attributes = [
            'type' => 'tel',
            'id' => $field_id,
            'class' => implode(' ', array_filter($classes)),
            'value' => !empty($settings['default_value']) ? $settings['default_value'] : '',
            'placeholder' => !empty($settings['placeholder']) && !$validate ? $settings['placeholder'] : '',
            'required' => !empty($settings['required']) ? true : false,
            'data-required-message' => !empty($settings['required']) && !empty($settings['required_message']) ? $settings['required_message'] : '',
            'data-validation' => $validate,
            'data-validation-message' => $validate_message,
            'data-initial-country' => $initial_country,
            'data-exclude-countries' => $exclude_countries,
            'data-only-countries' => $only_countries,
            'name' => !empty($settings['name_attribute']) ? $settings['name_attribute'] : '',
        ];
        // Build attribute string
        $attributes_string = '';
        foreach ($attributes as $key => $value) {
            if($value) {
                $attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
            }
        }
        return sprintf(
            '<input %s/>',
            $attributes_string
        );
    }

    /**
     * Render country field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_country($field_id, $settings) {
        $classes = [
            'ht-form-elem-input',
            'ht-form-elem-input-country',
        ];
        $initial_country = !empty($settings['default_country']) ? $settings['default_country'] : '';
        $exclude_countries = !empty($settings['country_list_type']) && $settings['country_list_type'] === 'exclude' ? implode(',', $settings['country_list']) : '';
        $only_countries = !empty($settings['country_list_type']) && $settings['country_list_type'] === 'include' ? implode(',', $settings['country_list']) : '';

        if(!empty($settings['auto_country_select'])) {
            $geo_data = $this->helper->get_geolocation_data($this->helper->get_ip());
            $initial_country = !empty($geo_data['country']) ? $geo_data['country'] : $initial_country;
        }
        $attributes = [
            'type' => 'text',
            'id' => $field_id,
            'class' => implode(' ', array_filter($classes)),
            'value' => !empty($settings['default_value']) ? $settings['default_value'] : '',
            'placeholder' => !empty($settings['placeholder']) ? $settings['placeholder'] : '',
            'required' => !empty($settings['required']) ? true : false,
            'data-required-message' => !empty($settings['required']) && !empty($settings['required_message']) ? $settings['required_message'] : '',
            'data-initial-country' => strtolower($initial_country),
            'data-exclude-countries' => $exclude_countries,
            'data-only-countries' => $only_countries,
            'name' => !empty($settings['name_attribute']) ? $settings['name_attribute'] : '',
        ];
        // Build attribute string
        $attributes_string = '';
        foreach ($attributes as $key => $value) {
            if($value) {
                $attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
            }
        }
        return sprintf(
            '<input %s/>',
            $attributes_string
        );
    }

    /**
     * Render address field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_address($field_id, $settings) {
        $geo_data = $this->helper->get_geolocation_data($this->helper->get_ip());
        if (!is_array($geo_data)) {
            $geo_data = [];
        }
        $attributes = [
            'id' => $field_id,
            'class' => 'ht-form-elem-group ht-form-elem-address',
        ];
        // Build attribute string
        $attributes_string = '';
        foreach ($attributes as $key => $value) {
            if($value) {
                $attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
            }
        }
        
        // Create field wrapper
        $wrapper_classes = [
            'ht-form-elem',
            !empty($settings['field_size']) ? 'ht-form-elem-' . sanitize_html_class($settings['field_size']) : 'medium',
        ];

        $fields = [];

        if(!empty($settings['enable_address_line_1'])) {
            $wrapper_classes[] = 'ht-form-elem-input-field';
            $fields[] = $this->render_field(
                $wrapper_classes,
                'input',
                "{$field_id}_address_line_1",
                array_merge($settings, [
                    'label' => !empty($settings['address_line_1']['label']) ? $settings['address_line_1']['label'] : '',
                    'default_value' => !empty($settings['address_line_1']['value']) ? $settings['address_line_1']['value'] : '',
                    'placeholder' => !empty($settings['address_line_1']['placeholder']) ? $settings['address_line_1']['placeholder'] : '',
                    'required' => !empty($settings['address_line_1']['required']) ? true : false,
                    'data-required-message' => !empty($settings['address_line_1']['required_message']) ? $settings['address_line_1']['required_message'] : '',
                    'name_attribute' => !empty($settings['name_attribute']) ? $settings['name_attribute'] . '[address_line_1]' : '',
                ]),
                false
            );
        }
        if(!empty($settings['enable_address_line_2'])) {
            $wrapper_classes[] = 'ht-form-elem-input-field';
            $fields[] = $this->render_field(
                $wrapper_classes,
                'input',
                "{$field_id}_address_line_2",
                array_merge($settings, [
                    'label' => !empty($settings['address_line_2']['label']) ? $settings['address_line_2']['label'] : '',
                    'default_value' => !empty($settings['address_line_2']['value']) ? $settings['address_line_2']['value'] : '',
                    'placeholder' => !empty($settings['address_line_2']['placeholder']) ? $settings['address_line_2']['placeholder'] : '',
                    'required' => !empty($settings['address_line_2']['required']) ? true : false,
                    'data-required-message' => !empty($settings['address_line_2']['required_message']) ? $settings['address_line_2']['required_message'] : '',
                    'name_attribute' => !empty($settings['name_attribute']) ? $settings['name_attribute'] . '[address_line_2]' : '',
                ]),
                false
            );
        }
        if(!empty($settings['enable_city'])) {
            $wrapper_classes[] = 'ht-form-elem-input-field';
            $value = !empty($settings['city']['value']) ? $settings['city']['value'] : '';
            if(!empty($settings['city']['auto_fill'])) {
                $value = $geo_data['city'] ?? '';
            }
            $fields[] = $this->render_field(
                $wrapper_classes,
                'input',
                "{$field_id}_city",
                array_merge($settings, [
                    'label' => !empty($settings['city']['label']) ? $settings['city']['label'] : '',
                    'default_value' => $value,
                    'placeholder' => !empty($settings['city']['placeholder']) ? $settings['city']['placeholder'] : '',
                    'required' => !empty($settings['city']['required']) ? true : false,
                    'data-required-message' => !empty($settings['city']['required_message']) ? $settings['city']['required_message'] : '',
                    'name_attribute' => !empty($settings['name_attribute']) ? $settings['name_attribute'] . '[address_city]' : '',
                ]),
                false
            );
        }
        if(!empty($settings['enable_state'])) {
            $wrapper_classes[] = 'ht-form-elem-input-field';
            $value = !empty($settings['state']['value']) ? $settings['state']['value'] : '';
            if(!empty($settings['state']['auto_fill'])) {
                $value = $geo_data['region'] ?? '';
            }
            $fields[] = $this->render_field(
                $wrapper_classes,
                'input',
                "{$field_id}_state",
                array_merge($settings, [
                    'label' => !empty($settings['state']['label']) ? $settings['state']['label'] : '',
                    'default_value' => $value,
                    'placeholder' => !empty($settings['state']['placeholder']) ? $settings['state']['placeholder'] : '',
                    'required' => !empty($settings['state']['required']) ? true : false,
                    'data-required-message' => !empty($settings['state']['required_message']) ? $settings['state']['required_message'] : '',
                    'name_attribute' => !empty($settings['name_attribute']) ? $settings['name_attribute'] . '[address_state]' : '',
                ]),
                false
            );
        }
        if(!empty($settings['enable_zip'])) {
            $wrapper_classes[] = 'ht-form-elem-input-field';
            $value = !empty($settings['zip']['value']) ? $settings['zip']['value'] : '';
            if(!empty($settings['zip']['auto_fill'])) {
                $value = $geo_data['postal'] ?? '';
            }
            $fields[] = $this->render_field(
                $wrapper_classes,
                'input',
                "{$field_id}_zip",
                array_merge($settings, [
                    'label' => !empty($settings['zip']['label']) ? $settings['zip']['label'] : '',
                    'default_value' => $value,
                    'placeholder' => !empty($settings['zip']['placeholder']) ? $settings['zip']['placeholder'] : '',
                    'required' => !empty($settings['zip']['required']) ? true : false,
                    'data-required-message' => !empty($settings['zip']['required_message']) ? $settings['zip']['required_message'] : '',
                    'name_attribute' => !empty($settings['name_attribute']) ? $settings['name_attribute'] . '[address_zip]' : '',
                ]),
                false
            );
        }
        
        if(!empty($settings['enable_country'])) {
            $wrapper_classes[] = 'ht-form-elem-country-field';
            $value = !empty($settings['country']['value']) ? $settings['country']['value'] : '';

            $fields[] = $this->render_field(
                $wrapper_classes,
                'country',
                "{$field_id}_country",
                array_merge($settings, [
                    'label' => !empty($settings['country']['label']) ? $settings['country']['label'] : '',
                    'default_value' => $value,
                    'default_country' => !empty($settings['country']['default_country']) ? $settings['country']['default_country'] : '',
                    'auto_country_select' => !empty($settings['country']['auto_fill']) ? true : false,
                    'country_list_type' => !empty($settings['country']['country_list_type']) ? $settings['country']['country_list_type'] : '',
                    'country_list' => !empty($settings['country']['country_list']) ? $settings['country']['country_list'] : [],
                    'placeholder' => !empty($settings['country']['placeholder']) ? $settings['country']['placeholder'] : '',
                    'required' => !empty($settings['country']['required']) ? true : false,
                    'data-required-message' => !empty($settings['country']['required_message']) ? $settings['country']['required_message'] : '',
                    'name_attribute' => !empty($settings['name_attribute']) ? $settings['name_attribute'] . '[address_country]' : '',
                ]),
                false
            );
        }

        return sprintf(
            '<div %s>
                %s
            </div>',
            $attributes_string,
            implode('', $fields)
        );
    }

    /**
     * Render input mask field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_mask_input($field_id, $settings) {
        $attributes = [
            'type' => 'text',
            'id' => $field_id,
            'class' => 'ht-form-elem-input ht-form-elem-input-mask',
            'value' => !empty($settings['default_value']) ? $settings['default_value'] : '',
            'placeholder' => !empty($settings['placeholder']) ? $settings['placeholder'] : '',
            'data-mask' => !empty($settings['mask']) ? $settings['mask'] : '',
            'required' => !empty($settings['required']) ? true : false,
            'data-required-message' => !empty($settings['required']) && !empty($settings['required_message']) ? $settings['required_message'] : '',
            'name' => !empty($settings['name_attribute']) ? $settings['name_attribute'] : '',
        ];
        // Build attribute string
        $attributes_string = '';
        foreach ($attributes as $key => $value) {
            if($value) {
                $attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
            }
        }
        return sprintf(
            '<input %s/>',
            $attributes_string
        );
    }

    /**
     * Render number field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_number($field_id, $settings) {
        $attributes = [
            'type' => 'number',
            'id' => $field_id,
            'class' => 'ht-form-elem-input',
            'value' => !empty($settings['default_value']) ? $settings['default_value'] : '',
            'placeholder' => !empty($settings['placeholder']) ? $settings['placeholder'] : '',
            'min' => isset($settings['min']) ? (int) $settings['min'] : '',
            'max' => isset($settings['max']) ? (int) $settings['max'] : '',
            'step' => isset($settings['step']) ? (int) $settings['step'] : '',
            'required' => !empty($settings['required']) ? true : false,
            'data-required-message' => !empty($settings['required']) && !empty($settings['required_message']) ? $settings['required_message'] : '',
            'name' => !empty($settings['name_attribute']) ? $settings['name_attribute'] : '',
        ];
        // Build attribute string
        $attributes_string = '';
        foreach ($attributes as $key => $value) {
            if($value !== false && $value !== null && $value !== '') {
                $attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
            }
        }
        return sprintf(
            '<input %s/>',
            $attributes_string
        );
    }

    /**
     * Render textarea field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_textarea($field_id, $settings) {
        $attributes = [
            'id' => $field_id,
            'class' => 'ht-form-elem-textarea',
            'placeholder' => !empty($settings['placeholder']) ? $settings['placeholder'] : '',
            'required' => !empty($settings['required']) ? true : false,
            'data-required-message' => !empty($settings['required']) && !empty($settings['required_message']) ? $settings['required_message'] : '',
            'name' => !empty($settings['name_attribute']) ? $settings['name_attribute'] : '',
            'maxlength' => !empty($settings['max_length']) ? $settings['max_length'] : '',
        ];
        // Build attribute string
        $attributes_string = '';
        foreach ($attributes as $key => $value) {
            if($value) {
                $attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
            }
        }
        return sprintf(
            '<textarea %1$s>%2$s</textarea>',
            $attributes_string,
            !empty($settings['default_value']) ? $settings['default_value'] : '',
        );
    }

    /**
     * Render select/dropdown field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_dropdown($field_id, $settings) {
        $attributes = [
            'id' => $field_id,
            'class' => 'ht-form-elem-select',
            'required' => !empty($settings['required']) ? true : false,
            'data-required-message' => !empty($settings['required']) && !empty($settings['required_message']) ? $settings['required_message'] : '',
            'data-searchable' => !empty($settings['searchable']) ? $settings['searchable'] : false,
            'data-placeholder' => !empty($settings['placeholder']) ? $settings['placeholder'] : '',
            'data-ht-select' => true,
            'name' => !empty($settings['name_attribute']) ? $settings['name_attribute'] : '',
        ];
        // Build attribute string
        $attributes_string = '';
        foreach ($attributes as $key => $value) {
            if($value) {
                $attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
            }
        }
        $options = '';
        if(!empty($settings['placeholder'])) {
            $options .= sprintf(
                '<option value="" disabled selected>%s</option>',
                esc_html($settings['placeholder'])
            );
        }
        if(!empty($settings['options'])) {
            foreach ($settings['options'] as $option) {
                $options .= sprintf(
                    '<option value="%s" %s>%s</option>', 
                    esc_attr($option['value']), 
                    $option['selected'] ? 'selected' : '', 
                    esc_html($option['label'])
                );
            }
        }
        return sprintf(
            '<select %s>%s</select>',
            $attributes_string,
            $options
        );
    }

    /**
     * Render post select field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_post_select($field_id, $settings) {
        $post_type = !empty($settings['post_type']) ? sanitize_text_field($settings['post_type']) : 'post';
        $post_status = !empty($settings['post_status']) ? sanitize_text_field($settings['post_status']) : 'publish';
        $posts_per_page = !empty($settings['posts_per_page']) ? absint($settings['posts_per_page']) : 100;

        // Validate post type exists
        if (!post_type_exists($post_type)) {
            $post_type = 'post';
        }

        // Query posts (ordered by date ascending - oldest first)
        $args = [
            'post_type' => $post_type,
            'post_status' => $post_status,
            'posts_per_page' => $posts_per_page,
            'orderby' => 'date',
            'order' => 'ASC',
            'no_found_rows' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ];

        // Check cache first
        $cache_key = 'ht_form_posts_' . md5(serialize($args));
        $posts = get_transient($cache_key);

        if (false === $posts) {
            $posts = get_posts($args);
            // Cache for 5 minutes
            set_transient($cache_key, $posts, 5 * MINUTE_IN_SECONDS);
        }

        // Build select attributes
        $attributes = [
            'id' => $field_id,
            'class' => 'ht-form-elem-select',
            'required' => !empty($settings['required']) ? true : false,
            'data-required-message' => !empty($settings['required']) && !empty($settings['required_message']) ? $settings['required_message'] : '',
            'data-searchable' => !empty($settings['searchable']) ? $settings['searchable'] : false,
            'data-placeholder' => !empty($settings['placeholder']) ? $settings['placeholder'] : '',
            'data-ht-select' => true,
            'name' => !empty($settings['name_attribute']) ? $settings['name_attribute'] : '',
        ];

        // Handle empty posts case
        if (empty($posts)) {
            $attributes['disabled'] = true;
            $attributes_string = '';
            foreach ($attributes as $key => $value) {
                if ($value) {
                    $attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
                }
            }
            $post_type_obj = get_post_type_object($post_type);
            $post_type_label = $post_type_obj ? $post_type_obj->labels->name : $post_type;
            return sprintf(
                '<select %s><option value="">%s</option></select>',
                $attributes_string,
                esc_html(sprintf(__('No %s available', 'ht-contactform'), strtolower($post_type_label)))
            );
        }

        // Build attribute string
        $attributes_string = '';
        foreach ($attributes as $key => $value) {
            if ($value) {
                $attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
            }
        }

        // Build options
        $options = '';
        if (!empty($settings['placeholder'])) {
            $options .= sprintf(
                '<option value="" disabled selected>%s</option>',
                esc_html($settings['placeholder'])
            );
        }

        foreach ($posts as $post) {
            $options .= sprintf(
                '<option value="%s">%s</option>',
                esc_attr($post->post_title),
                esc_html($post->post_title)
            );
        }

        return sprintf(
            '<select %s>%s</select>',
            $attributes_string,
            $options
        );
    }

    /**
     * Render select/dropdown field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_multiple_choices($field_id, $settings) {
        $attributes = [
            'id' => $field_id,
            'class' => 'ht-form-elem-select',
            'required' => !empty($settings['required']) ? true : false,
            'data-required-message' => !empty($settings['required']) && !empty($settings['required_message']) ? $settings['required_message'] : '',
            'data-searchable' => !empty($settings['searchable']) ? $settings['searchable'] : false,
            'data-placeholder' => !empty($settings['placeholder']) ? $settings['placeholder'] : '',
            'data-maxselect' => !empty($settings['max_selection']) ? $settings['max_selection'] : '',
            'multiple' => true,
            'data-ht-select' => true,
            'name' => !empty($settings['name_attribute']) ? $settings['name_attribute'] : '',
        ];
        // Build attribute string
        $attributes_string = '';
        foreach ($attributes as $key => $value) {
            if($value) {
                $attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
            }
        }
        $options = '';
        if(!empty($settings['options'])) {
            foreach ($settings['options'] as $option) {
                $options .= sprintf(
                    '<option value="%s" %s>%s</option>', 
                    esc_attr($option['value']), 
                    $option['selected'] ? 'selected' : '', 
                    esc_html($option['label'])
                );
            }
        }
        return sprintf(
            '<select %s>%s</select>',
            $attributes_string,
            $options
        );
    }

    /**
     * Render checkboxes field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_checkboxes($field_id, $settings) {
        $attributes = [
            'class' => implode(' ', array_filter([
                'ht-form-elem-checkboxes',
                $settings['layout'] ? "ht-form-elem-checkboxes-" . esc_attr($settings['layout']) : false
            ])),
        ];
        // Build attribute string
        $attributes_string = '';
        foreach ($attributes as $key => $value) {
            if($value) {
                $attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
            }
        }
        $options = '';
        if(!empty($settings['options'])) {
            foreach ($settings['options'] as $option) {
                $item_attributes = [
                    'type' => 'checkbox',
                    'id' => $field_id .'_'. $option['value'],
                    'name' => !empty($settings['name_attribute']) ? $settings['name_attribute'] . '[]' : '',
                    'value' => $option['value'],
                    'checked' => $option['selected'] ? 'checked' : '',
                    'required' => $settings['required'] ? true : false,
                    'data-required-message' => !empty($settings['required']) && !empty($settings['required_message']) ? $settings['required_message'] : '',
                ];
                // Build attribute string
                $item_attributes_string = '';
                foreach ($item_attributes as $key => $value) {
                    if($value) {
                        $item_attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
                    }
                }
                $options .= sprintf(
                    '<div class="ht-form-elem-checkbox">
                        <div class="ht-form-elem-checkbox-inner">
                            <input %s/>
                            <svg viewBox="0 0 10 7" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="ht-form-elem-checkbox-icon"><path d="M4 4.586L1.707 2.293A1 1 0 1 0 .293 3.707l3 3a.997.997 0 0 0 1.414 0l5-5A1 1 0 1 0 8.293.293L4 4.586z" fill="currentColor" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
                        </div>
                        <label for="%s">%s</label>
                    </div>' . PHP_EOL,
                    $item_attributes_string,
                    esc_attr($field_id .'_'. $option['value']),
                    esc_html($option['label'])
                );
            }
        }
        return sprintf(
            '<div %s>%s</div>',
            $attributes_string,
            $options
        );
    }

    /**
     * Render radio field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_radio($field_id, $settings) {
        $attributes = [
            'class' => implode(' ', array_filter([
                'ht-form-elem-radios',
                $settings['layout'] ? "ht-form-elem-radios-" . esc_attr($settings['layout']) : false
            ])),
        ];
        // Build attribute string
        $attributes_string = '';
        foreach ($attributes as $key => $value) {
            if($value) {
                $attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
            }
        }
        $options = '';
        if(!empty($settings['options'])) {
            foreach ($settings['options'] as $option) {
                $item_attributes = [
                    'type' => 'radio',
                    'id' => $field_id .'_'. $option['value'],
                    'name' => !empty($settings['name_attribute']) ? $settings['name_attribute'] : '',
                    'value' => $option['value'],
                    'checked' => $option['selected'] ? 'checked' : '',
                    'required' => $settings['required'] ? true : false,
                    'data-required-message' => !empty($settings['required']) && !empty($settings['required_message']) ? $settings['required_message'] : '',
                ];
                // Build attribute string
                $item_attributes_string = '';
                foreach ($item_attributes as $key => $value) {
                    if($value) {
                        $item_attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
                    }
                }
                $options .= sprintf(
                    '<div class="ht-form-elem-radio-item">
                        <div class="ht-form-elem-radio-inner">
                            <input %s/>
                            <span class="ht-form-elem-radio-icon"></span>
                        </div>
                        <label for="%s">%s</label>
                    </div>' . PHP_EOL,
                    $item_attributes_string,
                    esc_attr($field_id .'_'. $option['value']),
                    esc_html($option['label'])
                );
            }
        }
        return sprintf(
            '<div %s>%s</div>',
            $attributes_string,
            $options
        );
    }

    /**
     * Render slider field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_slider($field_id, $settings) {
        $attributes = [
            'type' => 'range',
            'id' => $field_id,
            'class' => 'ht-form-elem-range',
            'value' => !empty($settings['default_value']) ? $settings['default_value'] : 0,
            'name' => !empty($settings['name_attribute']) ? $settings['name_attribute'] : '',
            'min' => !empty($settings['min']) ? $settings['min'] : 0,
            'max' => !empty($settings['max']) ? $settings['max'] : 100,
            'step' => !empty($settings['step']) ? $settings['step'] : 1,
            'required' => !empty($settings['required']) ? true : false,
            'data-required-message' => !empty($settings['required']) && !empty($settings['required_message']) ? $settings['required_message'] : '',
        ];
        // Build attribute string
        $attributes_string = '';
        foreach ($attributes as $key => $value) {
            if($value) {
                $attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
            }
        }
        return sprintf(
            '<div class="ht-form-elem-content-range"><input %1$s/>%2$s</div>',
            $attributes_string,
            !empty($settings['value_display']) ? '<p class="ht-form-elem-range-value">' . str_replace('{value}',    '<span class="ht-form-elem-range-amount">'.esc_attr($settings['default_value']).'</span>', esc_html($settings['value_display'])) . '</p>' : ''
        );
    }

    /**
     * Render file upload field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_file_upload($field_id, $settings) {
        $required = !empty($settings['required']) ? true : false;
        $required_message = !empty($settings['required']) && !empty($settings['required_message']) ? $settings['required_message'] : '';
        $button_text = !empty($settings['button_text']) ? $settings['button_text'] : '';
        $interface = !empty($settings['upload_interface']) ? $settings['upload_interface'] : 'button';
        $max_file_size = !empty($settings['max_file_size']) ? $settings['max_file_size'] : '';
        $max_file_size_message = !empty($settings['max_file_size_message']) ? $settings['max_file_size_message'] : '';
        $max_file_count = !empty($settings['max_files']) ? $settings['max_files'] : '';
        $max_file_count_message = !empty($settings['max_files_message']) ? $settings['max_files_message'] : '';
        $allow_types = !empty($settings['allow_types']) ? $settings['allow_types'] : [];
        $allow_types_message = !empty($settings['allow_types_message']) ? $settings['allow_types_message'] : '';
        $upload_location = !empty($settings['upload_location']) ? $settings['upload_location'] : 'default';
        $name_attribute = !empty($settings['name_attribute']) ? $settings['name_attribute'] : '';

        $file_allows = array_map(function($type){
            return $this->file_types[$type];
        }, $allow_types);

        $file_allows = implode(',', array_merge(...$file_allows));

        return sprintf(
            '<label for="%1$s" class="ht-form-elem-file-upload ht-form-elem-file-upload-%2$s">
                <input
                    type="file"
                    id="%1$s"
                    name="%3$s[]"
                    multiple
                    accept="%4$s"
                    %5$s
                    data-required-message="%6$s"
                    data-max-file-size="%7$sMB"
                    data-max-file-size-message="%8$s"
                    data-max-files="%9$s"
                    data-max-files-message="%10$s"
                    data-allow-message="%11$s"
                    data-upload-location="%12$s"
                />
            </label>',
            esc_attr($field_id),
            esc_html($interface),
            esc_attr($name_attribute),
            esc_attr($file_allows),
            $required ? 'required' : '',
            esc_attr($required_message),
            esc_attr($max_file_size),
            esc_attr($max_file_size_message),
            esc_attr($max_file_count),
            esc_attr($max_file_count_message),
            esc_attr($allow_types_message),
            esc_attr($upload_location)
        );
    }

    /**
     * Render image upload field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_image_upload($field_id, $settings) {
        $required = !empty($settings['required']) ? true : false;
        $required_message = !empty($settings['required']) && !empty($settings['required_message']) ? $settings['required_message'] : '';
        $button_text = !empty($settings['button_text']) ? $settings['button_text'] : '';
        $interface = !empty($settings['upload_interface']) ? $settings['upload_interface'] : 'button';
        $max_file_size = !empty($settings['max_file_size']) ? $settings['max_file_size'] : '';
        $max_file_size_message = !empty($settings['max_file_size_message']) ? $settings['max_file_size_message'] : '';
        $max_file_count = !empty($settings['max_files']) ? $settings['max_files'] : '';
        $max_file_count_message = !empty($settings['max_files_message']) ? $settings['max_files_message'] : '';
        $allow_types = !empty($settings['allow_types']) ? $settings['allow_types'] : [];
        $allow_types_message = !empty($settings['allow_types_message']) ? $settings['allow_types_message'] : '';
        $upload_location = !empty($settings['upload_location']) ? $settings['upload_location'] : 'default';
        $name_attribute = !empty($settings['name_attribute']) ? $settings['name_attribute'] : '';

        $file_allows = implode(',', $allow_types);

        return sprintf(
            '<label class="ht-form-elem-image-upload ht-form-elem-image-upload-%2$s">
                <input
                    type="file"
                    id="%1$s"
                    name="%3$s[]"
                    multiple
                    accept="%4$s"
                    %5$s
                    data-required-message="%6$s"
                    data-max-file-size="%7$sMB"
                    data-max-file-size-message="%8$s"
                    data-max-files="%9$s"
                    data-max-files-message="%10$s"
                    data-allow-message="%11$s"
                    data-upload-location="%12$s"
                />
            </label>',
            esc_attr($field_id),
            esc_html($interface),
            esc_attr($name_attribute),
            esc_attr($file_allows),
            $required ? 'required' : '',
            esc_attr($required_message),
            esc_attr($max_file_size),
            esc_attr($max_file_size_message),
            esc_attr($max_file_count),
            esc_attr($max_file_count_message),
            esc_attr($allow_types_message),
            esc_attr($upload_location)
        );
    }

    /**
     * Render shortcode field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_shortcode($field_id, $settings) {
        $shortcode = !empty($settings['shortcode']) ? $settings['shortcode'] : '';
        $shortcode = do_shortcode($shortcode);
        return $shortcode;
    }

    /**
     * Render ratings field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_ratings($field_id, $settings) {
        $attributes = [
            'class' => implode(' ', array_filter([
                'ht-form-elem-ratings',
            ])),
        ];
        // Build attribute string
        $attributes_string = '';
        foreach ($attributes as $key => $value) {
            if($value) {
                $attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
            }
        }
        $options = '';
        if(!empty($settings['options'])) {
            foreach ($settings['options'] as $index => $option) {
                $input_attributes = [
                    'type' => 'radio',
                    'id' => $field_id .'_'. $option['value'],
                    'name' => !empty($settings['name_attribute']) ? $settings['name_attribute'] : '',
                    'value' => $option['value'],
                    'checked' => $option['selected'] ? 'checked' : '',
                    'required' => $settings['required'] ? true : false,
                    'data-required-message' => !empty($settings['required']) && !empty($settings['required_message']) ? $settings['required_message'] : '',
                ];
                // Build attribute string
                $input_attributes_string = '';
                foreach ($input_attributes as $key => $value) {
                    if($value) {
                        $input_attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
                    }
                }
                
                // Find the selected option index
                $selected_index = -1;
                foreach ($settings['options'] as $i => $opt) {
                    if ($opt['selected']) {
                        $selected_index = $i;
                        break;
                    }
                }
                
                $label_attributes = [
                    'for' => $field_id .'_'. $option['value'],
                ];
                if($settings['show_text_on_hover']) {
                    $label_attributes['data-tooltip'] = $option['label'];
                }
                
                // Add active class to current and all previous options if any option is selected
                if ($selected_index >= 0 && $index <= $selected_index) {
                    $label_attributes['class'] = 'active';
                }
                
                // Build attribute string
                $label_attributes_string = '';
                foreach ($label_attributes as $key => $value) {
                    if($value) {
                        $label_attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
                    }
                }
                $options .= sprintf(
                    '<label %s>
                        <input %s/>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-star"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8.243 7.34l-6.38 .925l-.113 .023a1 1 0 0 0 -.44 1.684l4.622 4.499l-1.09 6.355l-.013 .11a1 1 0 0 0 1.464 .944l5.706 -3l5.693 3l.1 .046a1 1 0 0 0 1.352 -1.1l-1.091 -6.355l4.624 -4.5l.078 -.085a1 1 0 0 0 -.633 -1.62l-6.38 -.926l-2.852 -5.78a1 1 0 0 0 -1.794 0l-2.853 5.78z" /></svg>
                    </label>' . PHP_EOL,
                    $label_attributes_string,
                    $input_attributes_string
                );
            }
        }
        return sprintf(
            '<div %s>%s</div>',
            $attributes_string,
            $options
        );
    }

    /**
     * Render GDPR field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_gdpr($field_id, $settings) {
        $desc = !empty($settings['description']) ? $settings['description'] : '';
        $label_attributes = [
            'for' => $field_id,
            'class' => 'ht-form-elem-gdpr',
            'aria-label' => $desc,
        ];
        // Build attribute string
        $label_attributes_string = '';
        foreach ($label_attributes as $key => $value) {
            if($value) {
                $label_attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
            }
        }
        $attributes = [
            'type' => 'checkbox',
            'id' => $field_id,
            'class' => 'ht-form-elem-input',
            'value' => 'yes',
            'name' => !empty($settings['name_attribute']) ? $settings['name_attribute'] : '',
            'required' => true,
            'data-required-message' => !empty($settings['required_message']) ? $settings['required_message'] : '',
        ];
        // Build attribute string
        $attributes_string = '';
        foreach ($attributes as $key => $value) {
            if($value) {
                $attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
            }
        }
        return sprintf(
            '<label %s>
                <div class="ht-form-elem-gdpr-inner">
                    <input %s/>
                    <svg viewBox="0 0 10 7" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="ht-form-elem-checkbox-icon"><path d="M4 4.586L1.707 2.293A1 1 0 1 0 .293 3.707l3 3a.997.997 0 0 0 1.414 0l5-5A1 1 0 1 0 8.293.293L4 4.586z" fill="currentColor" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
                </div>
                <span class="ht-form-elem-gdpr-desc">%s</span>
            </label>',
            $label_attributes_string,
            $attributes_string,
            $desc
        );
    }

    /**
     * Render Terms and Conditions field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_terms_conditions($field_id, $settings) {
        $content = !empty($settings['content']) ? $settings['content'] : '';
        $label_attributes = [
            'for' => $field_id,
            'class' => 'ht-form-elem-terms-conditions',
            'aria-label' => $content,
        ];
        // Build attribute string
        $label_attributes_string = '';
        foreach ($label_attributes as $key => $value) {
            if($value) {
                $label_attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
            }
        }
        $attributes = [
            'type' => 'checkbox',
            'id' => $field_id,
            'class' => 'ht-form-elem-input',
            'value' => 'yes',
            'name' => !empty($settings['name_attribute']) ? $settings['name_attribute'] : '',
            'required' => true,
            'data-required-message' => !empty($settings['required_message']) ? $settings['required_message'] : '',
        ];
        // Build attribute string
        $attributes_string = '';
        foreach ($attributes as $key => $value) {
            if($value) {
                $attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
            }
        }
        return sprintf(
            '<label %s>
                <div class="ht-form-elem-terms-conditions-inner">
                    <input %s/>
                    <svg viewBox="0 0 10 7" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="ht-form-elem-checkbox-icon"><path d="M4 4.586L1.707 2.293A1 1 0 1 0 .293 3.707l3 3a.997.997 0 0 0 1.414 0l5-5A1 1 0 1 0 8.293.293L4 4.586z" fill="currentColor" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
                </div>
                <span class="ht-form-elem-terms-conditions-desc">%s</span>
            </label>',
            $label_attributes_string,
            $attributes_string,
            wp_kses_post($content)
        );
    }

    /**
     * Render submit button
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_submit($field_id, $settings) {
        $button_style = !empty($settings['style']) ? sanitize_html_class($settings['style']) : '';
        $attributes = [
            'type' => 'submit',
            'id' => $field_id,
            'class' => implode(' ', array_filter([
                'ht-form-elem-button-submit',
                $button_style ? "ht-form-elem-button-submit-" . esc_attr($button_style) : false
            ])),
        ];
        // Build attribute string
        $attributes_string = '';
        foreach ($attributes as $key => $value) {
            if($value) {
                $attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
            }
        }
        return sprintf(
            '<button %s>%s</button>',
            $attributes_string,
            !empty($settings['default_value']) ? $settings['default_value'] : __('Submit', 'ht-contactform')
        );
    }

    /**
     * Render reCAPTCHA field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_recaptcha($field_id, $settings) {
        // Get active reCAPTCHA version
        $active_version = $this->global_settings['captcha']['recaptcha_active_version'] ?? '';

        // Determine site key based on active version
        if ($active_version === 'v2') {
            $site_key = $this->global_settings['captcha']['recaptcha_v2_site_key'] ?? '';
        } elseif ($active_version === 'v3') {
            $site_key = $this->global_settings['captcha']['recaptcha_v3_site_key'] ?? '';
        } else {
            return ''; // reCAPTCHA disabled
        }

        if (empty($site_key)) {
            return '<div class="ht-form-recaptcha-error">' . esc_html__('reCAPTCHA site key is not configured.', 'ht-contactform') . '</div>';
        }

        $attributes = [
            'type' => 'hidden',
            'name' => !empty($settings['name_attribute']) ? $settings['name_attribute'] : 'g-recaptcha-response',
            'id' => $field_id,
            'value' => '',
            'required' => true,
        ];

        // Build attribute string
        $attributes_string = '';
        foreach ($attributes as $key => $value) {
            if($value) {
                $attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
            }
        }

        $output = '<input ' . $attributes_string . '/>';

        // Only render visible widget for v2
        if ($active_version === 'v2') {
            $output .= sprintf(
                '<div class="g-recaptcha" data-sitekey="%s"></div>',
                esc_attr($site_key)
            );
        }

        return $output;
    }

    /**
     * Render hCaptcha field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_hcaptcha($field_id, $settings) {
        $site_key = isset($this->global_settings['captcha']['hcaptcha_site_key']) ?
                    $this->global_settings['captcha']['hcaptcha_site_key'] : '';

        if (empty($site_key)) {
            return '<div class="ht-form-hcaptcha-error">' . esc_html__('hCaptcha site key is not configured.', 'ht-contactform') . '</div>';
        }

        $attributes = [
            'type' => 'hidden',
            'name' => !empty($settings['name_attribute']) ? $settings['name_attribute'] : 'h-captcha-response',
            'id' => $field_id,
            'value' => '',
            'required' => true,
        ];

        // Build attribute string
        $attributes_string = '';
        foreach ($attributes as $key => $value) {
            if($value) {
                $attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
            }
        }

        $output = '<input ' . $attributes_string . '/>';
        $output .= sprintf(
            '<div class="h-captcha" data-sitekey="%s"></div>',
            esc_attr($site_key)
        );

        return $output;
    }

    /**
     * Render repeater field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_repeater($field_id, $settings) {
        $name_attribute = !empty($settings['name_attribute']) ? sanitize_text_field($settings['name_attribute']) : $field_id;
        $sub_fields = !empty($settings['sub_fields']) ? $settings['sub_fields'] : [];
        $add_button_text = !empty($settings['add_button_text']) ? esc_html($settings['add_button_text']) : __('Add Row', 'ht-contactform');
        $remove_button_text = !empty($settings['remove_button_text']) ? esc_html($settings['remove_button_text']) : __('Remove', 'ht-contactform');
        $row_label = !empty($settings['row_label']) ? esc_html($settings['row_label']) : __('Row {n}', 'ht-contactform');

        $output = sprintf(
            '<div class="ht-form-repeater-wrapper"
                  data-row-label="%s"
                  data-name="%s"
                  data-remove-text="%s">',
            esc_attr($row_label),
            esc_attr($name_attribute),
            esc_attr($remove_button_text)
        );

        // Render initial row
        $output .= '<div class="ht-form-repeater-rows">';
        $output .= $this->render_repeater_row($field_id, $name_attribute, $sub_fields, 0, $remove_button_text, $row_label);
        $output .= '</div>';

        $output .= '</div>';

        return $output;
    }

    /**
     * Render single repeater row
     *
     * @param string $field_id Field ID
     * @param string $name_attribute Name attribute
     * @param array $sub_fields Sub fields to render
     * @param int $index Row index
     * @param string $remove_button_text Remove button text
     * @param string $row_label Row label template
     * @param int $min_rows Minimum rows
     * @return string
     */
    private function render_repeater_row($field_id, $name_attribute, $sub_fields, $index, $remove_button_text, $row_label) {
        $row_number = $index + 1;
        $label = str_replace('{n}', $row_number, $row_label);

        $output = sprintf(
            '<div class="ht-form-repeater-row" data-row-index="%s">',
            esc_attr($index)
        );

        // Row content with sub-fields
        $output .= '<div class="ht-form-repeater-row-content">';

        foreach ($sub_fields as $sub_field) {
            // Clone the sub-field and update its name attribute for array storage
            $sub_field_settings = $sub_field['settings'];

            // Compute sub-field name: use custom name_attribute, else label (sanitized), else id
            $sub_field_name = '';
            if (!empty($sub_field_settings['name_attribute'])) {
                $sub_field_name = $sub_field_settings['name_attribute'];
            } elseif (!empty($sub_field['name_attribute'])) {
                $sub_field_name = $sub_field['name_attribute'];
            } elseif (!empty($sub_field['label'])) {
                $sub_field_name = strtolower(str_replace(' ', '_', trim($sub_field['label'])));
            } else {
                $sub_field_name = $sub_field['id'];
            }

            // Update name attribute to include repeater array notation
            $sub_field_settings['name_attribute'] = "{$name_attribute}[{$index}][{$sub_field_name}]";

            // Wrap sub-field in column div
            $output .= '<div class="ht-form-repeater-column">';

            // Render the sub-field
            $classes = ['ht-form-elem', "ht-form-elem-{$sub_field['type']}"];
            if (!empty($sub_field_settings['size'])) {
                $classes[] = "ht-form-elem-{$sub_field_settings['size']}";
            }

            $sub_field_id = $sub_field['id'] . '_' . $index;
            $output .= $this->render_field($classes, $sub_field['type'], $sub_field_id, $sub_field_settings, false);

            $output .= '</div>'; // Close column
        }

        // Render button group with remove and add buttons
        $output .= '<div class="ht-form-repeater-button-group">';

        // Remove button (icon only)
        $output .= sprintf(
            '<button type="button" class="ht-form-repeater-remove-btn" data-row-index="%s" title="%s">
                <span class="ht-form-repeater-btn-icon">×</span>
            </button>',
            esc_attr($index),
            esc_attr($remove_button_text)
        );

        // Add button (icon only)
        $output .= sprintf(
            '<button type="button" class="ht-form-repeater-add-btn" data-field-id="%s" title="%s">
                <span class="ht-form-repeater-btn-icon">+</span>
            </button>',
            esc_attr($field_id),
            esc_attr(__('Add Row', 'ht-contactform'))
        );

        $output .= '</div>'; // Close button group

        $output .= '</div>'; // Close row-content
        $output .= '</div>'; // Close row

        return $output;
    }

    /**
     * Render Section Break field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_section_break($field_id, $settings) {
        $heading = !empty($settings['heading']) ? $settings['heading'] : '';
        $description = !empty($settings['description']) ? $settings['description'] : '';
        $divider_style = !empty($settings['divider_style']) ? $settings['divider_style'] : 'solid';
        $divider_color = !empty($settings['divider_color']) ? $settings['divider_color'] : '#dddddd';
        $divider_width = isset($settings['divider_width']) ? intval($settings['divider_width']) : 3;
        $alignment = !empty($settings['heading_alignment']) ? $settings['heading_alignment'] : 'left';
        $field_class = !empty($settings['field_class']) ? $settings['field_class'] : '';

        $classes = ['ht-form-section-break', 'ht-form-section-break-align-' . esc_attr($alignment)];
        if ($field_class) {
            $classes[] = esc_attr($field_class);
        }

        $output = '<div class="' . implode(' ', $classes) . '">';

        if ($heading) {
            $output .= '<h3 class="ht-form-section-break-heading">' . esc_html($heading) . '</h3>';
        }

        if ($description) {
            $output .= '<div class="ht-form-section-break-description">' . wp_kses_post($description) . '</div>';
        }

        if ($divider_style !== 'none') {
            $output .= '<div class="ht-form-section-break-divider" style="border-bottom-style: ' . esc_attr($divider_style) . '; border-bottom-color: ' . esc_attr($divider_color) . '; border-bottom-width: ' . esc_attr($divider_width) . 'px;"></div>';
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Render Action Hook field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_action_hook($field_id, $settings) {
        $hook_name = !empty($settings['hook_name']) ? sanitize_key($settings['hook_name']) : 'custom_hook';
        $full_hook_name = 'ht_form_' . $hook_name;

        // Start output buffering to capture any hook output
        ob_start();
        do_action($full_hook_name, $field_id, $settings);
        $hook_output = ob_get_clean();

        // Only wrap in div if there's output
        if (!empty($hook_output)) {
            return "<div class=\"ht-form-action-hook\">$hook_output</div>";
        }

        return '';
    }

    /**
     * Render Color Picker field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_color($field_id, $settings) {
        $default_color = !empty($settings['default_color']) ? $settings['default_color'] : '#000000';
        $name_attribute = !empty($settings['name_attribute']) ? $settings['name_attribute'] : 'color';
        $required = !empty($settings['required']) ? true : false;
        $required_message = !empty($settings['required']) && !empty($settings['required_message']) ? $settings['required_message'] : '';

        $attributes = [
            'type' => 'color',
            'id' => $field_id,
            'class' => 'ht-form-elem-color-input',
            'value' => esc_attr($default_color),
            'name' => esc_attr($name_attribute),
        ];

        if ($required) {
            $attributes['required'] = 'required';
            $attributes['data-required-message'] = esc_attr($required_message);
        }

        // Build attribute string
        $attributes_string = '';
        foreach ($attributes as $key => $value) {
            if ($value !== '' && $value !== false) {
                $attributes_string .= sprintf(' %s="%s"', $key, $value);
            }
        }

        return sprintf(
            '<div class="ht-form-elem-color"><input%1$s /><span class="ht-form-elem-color-value">%2$s</span></div>',
            $attributes_string,
            esc_html($default_color)
        );
    }

    /**
     * Render Net Promoter Score field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_nps($field_id, $settings) {
        $name_attribute = !empty($settings['name_attribute']) ? $settings['name_attribute'] : 'nps_score';
        $required = !empty($settings['required']) ? true : false;
        $required_message = !empty($settings['required']) && !empty($settings['required_message']) ? $settings['required_message'] : '';
        $show_labels = isset($settings['show_labels']) ? $settings['show_labels'] : true;
        $low_label = !empty($settings['low_label']) ? $settings['low_label'] : __('Not at all likely', 'ht-contactform');
        $high_label = !empty($settings['high_label']) ? $settings['high_label'] : __('Extremely likely', 'ht-contactform');
        $color_coding = isset($settings['color_coding']) ? $settings['color_coding'] : true;

        $output = '<div class="ht-form-elem-nps">';

        // Labels
        if ($show_labels) {
            $output .= '<div class="ht-form-elem-nps-labels">';
            $output .= '<span class="ht-form-elem-nps-label-low">' . esc_html($low_label) . '</span>';
            $output .= '<span class="ht-form-elem-nps-label-high">' . esc_html($high_label) . '</span>';
            $output .= '</div>';
        }

        // Scale options (0-10)
        $output .= '<div class="ht-form-elem-nps-scale">';
        for ($i = 0; $i <= 10; $i++) {
            $color_class = '';
            if ($color_coding) {
                if ($i <= 6) {
                    $color_class = 'ht-nps-detractor';
                } elseif ($i <= 8) {
                    $color_class = 'ht-nps-passive';
                } else {
                    $color_class = 'ht-nps-promoter';
                }
            }

            $output .= '<label class="ht-form-elem-nps-option ' . esc_attr($color_class) . '">';
            $output .= '<input type="radio" name="' . esc_attr($name_attribute) . '" value="' . $i . '"';
            if ($required && $i === 0) {
                $output .= ' required data-required-message="' . esc_attr($required_message) . '"';
            }
            $output .= ' />';
            $output .= '<span class="ht-form-elem-nps-value">' . $i . '</span>';
            $output .= '</label>';
        }
        $output .= '</div>';

        $output .= '</div>';

        return $output;
    }

    /**
     * Rich Text Editor field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_richtext($field_id, $settings) {
        $name_attribute = isset($settings['name_attribute']) ? $settings['name_attribute'] : 'richtext_content';
        $placeholder = isset($settings['placeholder']) ? $settings['placeholder'] : '';
        $required = isset($settings['required']) && $settings['required'];
        $required_message = isset($settings['required_message']) ? $settings['required_message'] : __('This field is required', 'ht-contactform');
        $toolbar = isset($settings['toolbar']) ? $settings['toolbar'] : 'basic';
        $min_height = isset($settings['min_height']) ? absint($settings['min_height']) : 150;
        $max_length = isset($settings['max_length']) && !empty($settings['max_length']) ? absint($settings['max_length']) : 0;
        $size = isset($settings['size']) ? $settings['size'] : 'default';

        $output = '<div class="ht-form-elem-richtext ht-form-elem-richtext-' . esc_attr($size) . '"';
        $output .= ' data-placeholder="' . esc_attr($placeholder) . '"';
        $output .= ' data-toolbar="' . esc_attr($toolbar) . '"';
        $output .= ' data-min-height="' . esc_attr($min_height) . '"';
        if ($max_length > 0) {
            $output .= ' data-max-length="' . esc_attr($max_length) . '"';
        }
        $output .= '>';

        // Editor container
        $output .= '<div class="ht-form-elem-richtext-editor" style="min-height: ' . esc_attr($min_height) . 'px;"></div>';

        // Hidden input for form submission
        $output .= '<input type="hidden" name="' . esc_attr($name_attribute) . '"';
        if ($required) {
            $output .= ' required data-required-message="' . esc_attr($required_message) . '"';
        }
        $output .= ' class="ht-form-elem-richtext-value" />';

        $output .= '</div>';

        return $output;
    }

    /**
     * Signature field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_signature($field_id, $settings) {
        $name_attribute = isset($settings['name_attribute']) ? $settings['name_attribute'] : 'signature';
        $required = isset($settings['required']) && $settings['required'];
        $required_message = isset($settings['required_message']) ? $settings['required_message'] : __('This field is required', 'ht-contactform');
        $canvas_width = isset($settings['canvas_width']) ? absint($settings['canvas_width']) : 0;
        $canvas_height = isset($settings['canvas_height']) ? absint($settings['canvas_height']) : 200;
        $pen_color = isset($settings['pen_color']) ? sanitize_hex_color($settings['pen_color']) : '#000000';
        $background_color = isset($settings['background_color']) ? sanitize_hex_color($settings['background_color']) : '#ffffff';
        $pen_width = isset($settings['pen_width']) ? absint($settings['pen_width']) : 2;
        $show_clear_button = isset($settings['show_clear_button']) ? $settings['show_clear_button'] : true;
        $clear_button_text = isset($settings['clear_button_text']) ? $settings['clear_button_text'] : __('Clear', 'ht-contactform');

        $canvas_style = 'height: ' . esc_attr($canvas_height) . 'px;';
        if ($canvas_width > 0) {
            $canvas_style .= ' width: ' . esc_attr($canvas_width) . 'px;';
        } else {
            $canvas_style .= ' width: 100%;';
        }

        $output = '<div class="ht-form-elem-signature"';
        $output .= ' data-pen-color="' . esc_attr($pen_color) . '"';
        $output .= ' data-background-color="' . esc_attr($background_color) . '"';
        $output .= ' data-pen-width="' . esc_attr($pen_width) . '"';
        $output .= '>';

        $output .= '<div class="ht-form-elem-signature-canvas-wrapper" style="background-color: ' . esc_attr($background_color) . ';">';
        $output .= '<canvas class="ht-form-elem-signature-canvas" style="' . $canvas_style . '"></canvas>';
        $output .= '</div>';

        // Hidden input for storing signature data
        $output .= '<input type="hidden" name="' . esc_attr($name_attribute) . '"';
        if ($required) {
            $output .= ' required data-required-message="' . esc_attr($required_message) . '"';
        }
        $output .= ' class="ht-form-elem-signature-value" />';

        // Clear button
        if ($show_clear_button) {
            $output .= '<button type="button" class="ht-form-elem-signature-clear">' . esc_html($clear_button_text) . '</button>';
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Chained Select field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_chained_select($field_id, $settings) {
        $name_attribute = isset($settings['name_attribute']) ? $settings['name_attribute'] : 'chained_select';
        $required = isset($settings['required']) && $settings['required'];
        $required_message = isset($settings['required_message']) ? $settings['required_message'] : __('This field is required', 'ht-contactform');
        $default_placeholder = isset($settings['placeholder']) ? $settings['placeholder'] : __('Select...', 'ht-contactform');
        $layout = isset($settings['layout']) ? $settings['layout'] : 'vertical';
        $size = isset($settings['size']) ? $settings['size'] : 'default';

        // Get stored data from settings (parsed CSV data stored directly)
        $stored_data = isset($settings['chained_data_input']) ? $settings['chained_data_input'] : [];
        $raw_data = isset($stored_data['chained_data']) ? $stored_data['chained_data'] : [];
        $csv_labels = isset($stored_data['chained_labels']) ? $stored_data['chained_labels'] : [];
        $level_count = isset($stored_data['level_count']) ? (int) $stored_data['level_count'] : 0;

        // Build hierarchical data from flat data for JavaScript
        $chained_data = $this->build_hierarchical_data($raw_data, $level_count);

        // Default to 2 levels if no data
        if ($level_count < 2) {
            $level_count = 2;
        }

        $layout_class = $layout === 'horizontal' ? 'ht-form-elem-chained-select-horizontal' : 'ht-form-elem-chained-select-vertical';
        $output = '<div class="ht-form-elem-chained-select ' . esc_attr($layout_class) . ' ht-form-elem-chained-select-' . esc_attr($size) . '" data-level-count="' . esc_attr($level_count) . '"';
        if ($required) {
            $output .= ' data-required="true"';
        }
        $output .= '>';

        // Store chained data as JSON for JavaScript (includes hierarchical data for level 1->2)
        $output .= '<script type="application/json" class="ht-form-chained-data">' . wp_json_encode($chained_data) . '</script>';

        // Store raw data for multi-level filtering (level 3+)
        $output .= '<script type="application/json" class="ht-form-chained-raw-data">' . wp_json_encode($raw_data) . '</script>';

        // Store labels
        $output .= '<script type="application/json" class="ht-form-chained-labels">' . wp_json_encode($csv_labels) . '</script>';

        // Render all level selects dynamically
        for ($level = 1; $level <= $level_count; $level++) {
            $level_label = isset($csv_labels[$level - 1]) ? $csv_labels[$level - 1] : sprintf(__('Level %d', 'ht-contactform'), $level);
            $placeholder = $default_placeholder;

            $output .= '<div class="ht-form-elem-chained-select-level">';

            if (!empty($level_label)) {
                $output .= '<label class="ht-form-elem-chained-select-label">' . esc_html($level_label) . '</label>';
            }

            $output .= '<select name="' . esc_attr($name_attribute) . '[level_' . $level . ']" class="ht-form-elem-chained-select-input ht-form-elem-chained-level-' . $level . '" data-level="' . $level . '" data-placeholder="' . esc_attr($placeholder) . '"';

            // Only first level is enabled initially, others are disabled
            if ($level > 1) {
                $output .= ' disabled';
            }

            // Required only on first level
            if ($required && $level === 1) {
                $output .= ' required data-required-message="' . esc_attr($required_message) . '"';
            }

            $output .= '>';
            $output .= '<option value="">' . esc_html($placeholder) . '</option>';

            // Only populate level 1 options, others are populated via JavaScript
            if ($level === 1) {
                foreach ($chained_data as $option) {
                    $output .= '<option value="' . esc_attr($option['value']) . '">' . esc_html($option['label']) . '</option>';
                }
            }

            $output .= '</select>';
            $output .= '</div>';
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Build hierarchical data structure for chained selects
     *
     * @param array $raw_data Flat data rows
     * @param int $level_count Number of levels
     * @return array Hierarchical data for level 1
     */
    private function build_hierarchical_data($raw_data, $level_count) {
        $grouped = [];

        foreach ($raw_data as $row) {
            $l1_value = $row['level_1'];
            if (empty($l1_value)) {
                continue;
            }

            if (!isset($grouped[$l1_value])) {
                $grouped[$l1_value] = [
                    'label' => $l1_value,
                    'value' => $l1_value,
                    'children' => [],
                ];
            }

            // For 2-level, add level 2 as children
            if ($level_count >= 2 && !empty($row['level_2'])) {
                $l2_value = $row['level_2'];
                $child_exists = false;

                foreach ($grouped[$l1_value]['children'] as $child) {
                    if ($child['value'] === $l2_value) {
                        $child_exists = true;
                        break;
                    }
                }

                if (!$child_exists) {
                    $grouped[$l1_value]['children'][] = [
                        'label' => $l2_value,
                        'value' => $l2_value,
                    ];
                }
            }
        }

        return array_values($grouped);
    }

    /**
     * Render Save & Resume field
     *
     * @param string $field_id Field ID
     * @param array $settings Field settings
     * @return string
     */
    public function field_save_resume($field_id, $settings) {
        $button_text = isset($settings['save_button_text']) ? $settings['save_button_text'] : __('Save Progress', 'ht-contactform');
        $button_style = isset($settings['save_button_style']) ? $settings['save_button_style'] : 'default';
        $button_align = isset($settings['save_button_align']) ? $settings['save_button_align'] : 'left';
        $expiry_days = isset($settings['draft_expiry_days']) ? absint($settings['draft_expiry_days']) : 30;
        $show_email = isset($settings['show_email_option']) ? (bool) $settings['show_email_option'] : true;
        $email_button_text = isset($settings['email_button_text']) ? $settings['email_button_text'] : __('Email me the link', 'ht-contactform');
        $success_message = isset($settings['success_message']) ? $settings['success_message'] : __('Your progress has been saved!', 'ht-contactform');
        $copy_link_text = isset($settings['copy_link_text']) ? $settings['copy_link_text'] : __('Copy Resume Link', 'ht-contactform');
        $resume_notice_text = isset($settings['resume_notice_text']) ? $settings['resume_notice_text'] : __('Resuming your saved progress...', 'ht-contactform');
        $field_class = isset($settings['field_class']) ? $settings['field_class'] : '';

        $classes = ['ht-form-save-resume-wrapper', 'ht-form-elem-align-' . esc_attr($button_align)];
        if ($field_class) {
            $classes[] = esc_attr($field_class);
        }

        $output = '<div class="' . implode(' ', $classes) . '"';
        $output .= ' data-expiry="' . esc_attr($expiry_days) . '"';
        $output .= ' data-show-email="' . esc_attr($show_email ? 'true' : 'false') . '"';
        $output .= ' data-success-message="' . esc_attr($success_message) . '"';
        $output .= ' data-copy-link-text="' . esc_attr($copy_link_text) . '"';
        $output .= ' data-email-button-text="' . esc_attr($email_button_text) . '"';
        $output .= ' data-resume-notice-text="' . esc_attr($resume_notice_text) . '"';
        $output .= '>';

        // Save button
        $output .= '<button type="button" class="ht-form-save-btn ht-form-save-btn-' . esc_attr($button_style) . '">';
        $output .= '<span class="ht-form-save-btn-text">' . esc_html($button_text) . '</span>';
        $output .= '<span class="ht-form-save-btn-loading" style="display: none;">';
        $output .= '<svg class="ht-form-spinner" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/></path></svg>';
        $output .= '</span>';
        $output .= '</button>';

        // Modal template for success (inside wrapper so JS can find it)
        $output .= $this->render_save_resume_modal($settings);

        $output .= '</div>';

        return $output;
    }

    /**
     * Render the save resume success modal template
     *
     * @param array $settings Field settings
     * @return string
     */
    private function render_save_resume_modal($settings) {
        $success_message = isset($settings['success_message']) ? $settings['success_message'] : __('Your progress has been saved!', 'ht-contactform');
        $copy_link_text = isset($settings['copy_link_text']) ? $settings['copy_link_text'] : __('Copy Resume Link', 'ht-contactform');
        $show_email = isset($settings['show_email_option']) ? (bool) $settings['show_email_option'] : true;
        $email_button_text = isset($settings['email_button_text']) ? $settings['email_button_text'] : __('Email me the link', 'ht-contactform');

        $output = '<div class="ht-form-save-modal" style="display: none;">';
        $output .= '<div class="ht-form-save-modal-overlay"></div>';
        $output .= '<div class="ht-form-save-modal-content">';

        // Close button
        $output .= '<button type="button" class="ht-form-save-modal-close" aria-label="' . esc_attr__('Close', 'ht-contactform') . '">&times;</button>';

        // Success icon
        $output .= '<div class="ht-form-save-modal-icon">';
        $output .= '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
        $output .= '</div>';

        // Success message
        $output .= '<h3 class="ht-form-save-modal-title">' . esc_html($success_message) . '</h3>';

        // Resume link section
        $output .= '<div class="ht-form-save-modal-link-section">';
        $output .= '<input type="text" class="ht-form-save-modal-link-input" readonly />';
        $output .= '<button type="button" class="ht-form-save-modal-copy-btn">';
        $output .= '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>';
        $output .= '<span>' . esc_html($copy_link_text) . '</span>';
        $output .= '</button>';
        $output .= '</div>';

        // Email section (if enabled)
        if ($show_email) {
            $output .= '<div class="ht-form-save-modal-email-section">';
            $output .= '<p class="ht-form-save-modal-email-divider">' . esc_html__('or', 'ht-contactform') . '</p>';
            $output .= '<div class="ht-form-save-modal-email-form">';
            $output .= '<input type="email" class="ht-form-save-modal-email-input" placeholder="' . esc_attr__('Enter your email', 'ht-contactform') . '" />';
            $output .= '<button type="button" class="ht-form-save-modal-email-btn">';
            $output .= '<span class="ht-form-btn-text">' . esc_html($email_button_text) . '</span>';
            $output .= '<span class="ht-form-btn-loading" style="display: none;">';
            $output .= '<svg class="ht-form-spinner" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/></path></svg>';
            $output .= '</span>';
            $output .= '</button>';
            $output .= '</div>';
            $output .= '<p class="ht-form-save-modal-email-status"></p>';
            $output .= '</div>';
        }

        // Expiry notice
        $output .= '<p class="ht-form-save-modal-expiry">';
        $output .= sprintf(
            /* translators: %d: Number of days */
            esc_html__('This link will expire in %d days.', 'ht-contactform'),
            isset($settings['draft_expiry_days']) ? absint($settings['draft_expiry_days']) : 30
        );
        $output .= '</p>';

        $output .= '</div>'; // .ht-form-save-modal-content
        $output .= '</div>'; // .ht-form-save-modal

        return $output;
    }
}