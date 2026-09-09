<?php
/**
 * ShortCode Handler
 */

namespace HTContactForm\UI;

use HTContactForm\UI\Fields;
use HTContactForm\UI\Styler;

use HTContactFormAdmin\Includes\Models\Form as FormModel;
use HTContactFormAdmin\Includes\Models\Entries;
use HTContactFormAdmin\Includes\Services\Helper;
use HTContactFormAdmin\Includes\Services\Mailer;
use HTContactFormAdmin\Includes\Api\Endpoints\Submission as SubmissionEndpoint;

// If this file is accessed directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ShortCode class
 * Handles shortcode functionality for displaying forms on the frontend
 */
class ShortCode {

    /**
     * Instance of this class
     *
     * @var object
     */
    protected static $instance = null;

    /**
     * Assets
     */
    protected $assets = [];

    /**
     * Form model instance
     *
     * @var object
     */
    protected $form;

    protected $global_settings;

    /**
     * Entries model instance
     *
     * @var object
     */
    protected $entries;

    /**
     * Fields model instance
     *
     * @var object
     */
    protected $fields;

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
        $this->form = FormModel::get_instance();
        $this->global_settings = get_option('ht_form_global_settings', []);
        $this->entries = Entries::get_instance();
        $this->fields = Fields::get_instance();
        $this->register_shortcodes();

        // Register handler for non-JavaScript form submissions
        add_action('admin_post_ht_form_submit_nojs', [$this, 'handle_form_submission_nojs']);
        add_action('admin_post_nopriv_ht_form_submit_nojs', [$this, 'handle_form_submission_nojs']);
    }

    /**
     * Get the active reCAPTCHA site key based on active version
     *
     * @return string Site key or empty string
     */
    private function get_active_recaptcha_site_key() {
        $active_version = $this->global_settings['captcha']['recaptcha_active_version'] ?? '';

        if ($active_version === 'v2') {
            return $this->global_settings['captcha']['recaptcha_v2_site_key'] ?? '';
        } elseif ($active_version === 'v3') {
            return $this->global_settings['captcha']['recaptcha_v3_site_key'] ?? '';
        }

        return '';
    }

    /**
     * Register shortcodes
     *
     * @return void
     */
    public function register_shortcodes() {
        add_shortcode('ht_form', [$this, 'render_form']);
    }

    /**
     * Render form shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function render_form($atts) {
        $attributes = shortcode_atts(
            [
                'id' => 0,
            ],
            $atts
        );

        $form_id = absint($attributes['id']);

        if (empty($form_id)) {
            return sprintf('<div class="ht-form-message"><div class="ht-form-error">%s</div></div>', __('Form ID is required.', 'ht-contactform'));
        }

        // Get form data
        $form = $this->form->get($form_id);

        if(is_wp_error($form)) {
            return sprintf('<div class="ht-form-message"><div class="ht-form-error">%s</div></div>', $form->get_error_message());
        }

        // Start output buffer
        ob_start();
        
        // Check for form submission status from URL parameters (for non-JS submissions)
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $form_success = isset($_GET['form_success']) ? absint($_GET['form_success']) : 0;
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $form_error = isset($_GET['form_error']) ? sanitize_text_field(wp_unslash($_GET['form_error'])) : '';

        // Generate a unique form ID for the page
        $unique_id = 'ht-form-' . $form_id;

        // Get form data
        $fields = $form['fields'] ?? [];
        $settings = $form['settings'] ?? [];

        if(!empty($settings->styler['settings']['enable_styler'])) {
            $styler = Styler::get_instance($unique_id, $settings->styler['settings']);
            echo '<style>' . $styler->get_style() . '</style>';
        }

        // Form wrapper classes
        $form_classes = ['ht-form'];
        if (!empty($settings->general['settings']['class'])) {
            $form_classes[] = sanitize_html_class($settings->general['settings']['class']);
        }

        // Determine if AJAX is enabled
        $ajax_enabled = !empty($settings->general['settings']['enable_ajax']);

        // Form attributes
        $form_attributes = [
            'id'                 => $unique_id,
            'class'              => implode(' ', $form_classes),
            'data-form-id'       => $form_id,
            'data-ajax-enabled'  => $ajax_enabled ? 'true' : 'false',
            'method'             => 'post',
            'enctype'            => 'multipart/form-data',
            'novalidate'         => 'novalidate',
            'action'             => esc_url(admin_url('admin-post.php')), // Add action for non-JS submissions
        ];

        // Build attribute string
        $attributes_string = '';
        foreach ($form_attributes as $key => $value) {
            $attributes_string .= sprintf(' %s="%s"', $key, esc_attr($value));
        }

        // Start form
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $attributes_string is already escaped in the loop above
        echo sprintf('<form%s>', $attributes_string);
        
        // Display success message if applicable
        if ($form_success && $form_success === $form_id) {
            $this->show_success_message($form);
        }
        
        // Display error message if applicable
        if (!empty($form_error)) {
            $this->show_error_message($form_error, $form);
        }

        // Add WordPress nonce
        wp_nonce_field('ht_form_submit', 'ht_form_nonce');

        // Add hidden form ID field
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo sprintf('<input type="hidden" name="ht_form_id" value="%d">', $form_id);

        // Add hidden action field for non-JS form submissions
        echo '<input type="hidden" name="action" value="ht_form_submit_nojs">';

        // Add hidden timestamp field for minimum submission time validation
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo sprintf('<input type="hidden" name="ht_form_timestamp" value="%d">', time());

        // Add honeypot field
        if($settings->spam_protection['settings']['enable_spam_protection']) {
            $this->add_honeypot_field();
        }

        // Render form fields
        $this->render_fields($fields);

        // End form
        echo '</form>';

        // Enqueue frontend assets
        $this->enqueue_assets();

        // Return and clean output buffer
        return ob_get_clean();
    }

    /**
     * Render form fields
     *
     * @param array $fields Form fields
     * @return void
     */
    private function render_fields($fields) {
        if (empty($fields) || !is_array($fields)) {
            return;
        }

        echo '<div class="ht-form-elem-group">';

        foreach ($fields as $field) {
            $field_type = $field['type'] ?? '';
            $field_id = $field['id'] ?? '';
            $field_settings = $field['settings'] ?? [];

            if (empty($field_type) || empty($field_id)) {
                continue;
            }

            $this->manage_field_assets($field_type, $field_settings);
            if($field_type === 'repeater') {
                foreach ($field_settings['sub_fields'] as $sub_field) {
                    $this->manage_field_assets($sub_field['type'], $sub_field['settings'] ?? []);
                }
            }

            // Convert settings array to associative array
            $settings = $field_settings;

            // Create field wrapper
            $wrapper_classes = [
                'ht-form-elem',
                $field_type ? 'ht-form-elem-' . sanitize_html_class($field_type) . '-field' : '',
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
            if(!empty($settings['align'])) {
                $wrapper_classes[] = 'ht-form-elem-align-' . sanitize_html_class($settings['align']);
            }

            if($field_type === 'name') {
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo $this->fields->field_name($field_id, $settings);
            // } else if($field_type === 'action_hook') {
            //     // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            //     echo $this->fields->field_action_hook($field_id, $settings);
            } else {
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo $this->fields->render_field($wrapper_classes, $field_type, $field_id, $settings);
            }
        }

        echo '</div>'; // Close fields wrapper
    }

    /**
     * Manage field assets
     * 
     * @param string $field_type Field type
     * @return void
     */
    private function manage_field_assets($field_type, $field_settings) {
        if($field_type === 'dropdown' || $field_type === 'multiple_choices' || $field_type === 'post_select') {
            $this->assets[] = 'select';
        }
        if($field_type === 'mask_input') {
            $this->assets[] = 'imask';
        }
        if($field_type === 'phone' && !empty($field_settings['validate'])) {
            $this->assets[] = 'intl-tel-input';
        }
        if($field_type === 'country' || $field_type === 'address') {
            $this->assets[] = 'country-select';
        }
        if($field_type === 'date_time') {
            $this->assets[] = 'flatpickr';
        }
        if($field_type === 'file_upload' || $field_type === 'image_upload') {
            $this->assets[] = 'filepond';
            $this->assets[] = 'filepond-preview';
            $this->assets[] = 'filepond-size-validate';
            $this->assets[] = 'filepond-type-validate';
        }
        if($field_type === 'recaptcha') {
            $active_version = $this->global_settings['captcha']['recaptcha_active_version'] ?? '';
            if ($active_version === 'v2') {
                $this->assets[] = 'recaptcha-v2';
            } elseif ($active_version === 'v3') {
                $this->assets[] = 'recaptcha-v3';
            }
        }
        if($field_type === 'hcaptcha') {
            $this->assets[] = 'hcaptcha';
        }
        if($field_type === 'richtext') {
            $this->assets[] = 'quill';
        }
        if($field_type === 'signature') {
            $this->assets[] = 'signature-pad';
        }
    }

    /**
     * Helper method to add a parameter to URL while preserving existing parameters
     * 
     * @param string $url Base URL
     * @param string $param Parameter name
     * @param string $value Parameter value
     * @return string URL with parameter added
     */
    private function add_url_param($url, $param, $value) {
        $url_parts = wp_parse_url($url);
        $query = [];
        
        // If query string exists, parse it
        if (isset($url_parts['query'])) {
            parse_str($url_parts['query'], $query);
        }
        
        // Update or add our parameter
        $query[$param] = $value;
        
        // If adding form_error, remove form_success and vice versa
        if ($param === 'form_error' && isset($query['form_success'])) {
            unset($query['form_success']);
        } else if ($param === 'form_success' && isset($query['form_error'])) {
            unset($query['form_error']);
        }
        
        // Rebuild URL
        $url_parts['query'] = http_build_query($query);
        
        return $this->build_url($url_parts);
    }
    
    /**
     * Helper method to rebuild URL from parse_url parts
     * 
     * @param array $parts URL parts from parse_url
     * @return string Rebuilt URL
     */
    private function build_url($parts) {
        $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $host = isset($parts['host']) ? $parts['host'] : '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $user = isset($parts['user']) ? $parts['user'] : '';
        $pass = isset($parts['pass']) ? ':' . $parts['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parts['path']) ? $parts['path'] : '';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';
        
        return "$scheme$user$pass$host$port$path$query$fragment";
    }

    /**
     * Add honeypot field to form
     * This invisible field should not be filled out by humans
     * Bots will typically fill out all fields they find
     * 
     * @return void
     */
    private function add_honeypot_field() {
        ?>
        <div class="ht-form-honeypot-wrapper" aria-hidden="true" style="position: absolute; left: -9999px; top: -9999px; z-index: -999; opacity: 0; height: 0; width: 0; overflow: hidden;">
            <label for="ht_form_email_verify">
                <?php esc_html_e('Please leave this field empty', 'ht-contactform'); ?>
            </label>
            <input type="text" name="ht_form_hp_email" id="ht_form_email_verify" autocomplete="off" tabindex="-1">
        </div>
        <?php
    }

    /**
     * Handle non-JavaScript form submissions
     * This provides a fallback when JavaScript is disabled
     * 
     * @return void
     */
    public function handle_form_submission_nojs() {
        // Check if form ID and nonce are set
        if (!isset($_POST['ht_form_id']) || !isset($_POST['ht_form_nonce'])) {
            wp_redirect($this->add_url_param(wp_get_referer(), 'form_error', 'invalid_request'));
            exit;
        }

        // Verify nonce
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ht_form_nonce'])), 'ht_form_submit')) {
            wp_redirect($this->add_url_param(wp_get_referer(), 'form_error', 'invalid_nonce'));
            exit;
        }

        // Get form ID
        $form_id = absint($_POST['ht_form_id']);
        if (empty($form_id)) {
            wp_redirect($this->add_url_param(wp_get_referer(), 'form_error', 'invalid_form'));
            exit;
        }

        // Get form data
        $form = $this->form->get($form_id);
        if (!$form) {
            wp_redirect($this->add_url_param(wp_get_referer(), 'form_error', 'form_not_found'));
            exit;
        }

        // Check honeypot field - if it's filled, it's probably a bot
        if (isset($_POST['ht_form_hp_email']) && !empty($_POST['ht_form_hp_email'])) {
            wp_redirect($this->add_url_param(wp_get_referer(), 'form_error', 'honeypot_filled'));
            exit;
        }

        // Check form restrictions
        $settings = $form['settings'] ?? (object)[];
        $restriction_settings = $settings->form_restriction['settings'] ?? [];
        // IP restrictions
        if(!empty($restriction_settings['enable_ip_restriction'])) {
            $ip_restrictions_check = apply_filters('ht_form_submission_ip_restrictions_check', null, $restriction_settings);
            if (is_wp_error($ip_restrictions_check)) {
                wp_redirect($this->add_url_param(wp_get_referer(), 'form_error', 'ip_restricted'));
                exit;
            }
        }
        // Country restrictions
        if(!empty($restriction_settings['enable_country_restriction'])) {
            $country_restrictions_check = apply_filters('ht_form_submission_country_restrictions_check', null, $restriction_settings);
            if (is_wp_error($country_restrictions_check)) {
                wp_redirect($this->add_url_param(wp_get_referer(), 'form_error', 'country_restricted'));
                exit;
            }
        }
        
        // Check for minimum submission time if enabled
        $spam_settings = $settings->spam_protection['settings'] ?? [];
        
        if (!empty($spam_settings['enable_minimum_time_to_submit']) && 
            !empty($spam_settings['minimum_time_to_submit']) && 
            !empty($_POST['ht_form_timestamp'])) {
            
            $min_seconds = (int) $spam_settings['minimum_time_to_submit'];
            $submit_timestamp = (int) $_POST['ht_form_timestamp'];
            $current_time = time();
            $elapsed_time = $current_time - $submit_timestamp;
            
            // If submission is too quick, treat as spam
            if ($elapsed_time < $min_seconds) {
                wp_redirect($this->add_url_param(wp_get_referer(), 'form_error', 'submission_too_quick'));
                exit;
            }
        }

        // Process the form submission
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce already verified above
        $form_data = wp_unslash($_POST);
        unset($form_data['action'], $form_data['ht_form_nonce'], $form_data['ht_form_id']);

        // Verify the captcha. Whether one is required is decided by the form
        // configuration, so a request that omits the token fails here.
        $captcha_result = Helper::verify_form_captcha($form['fields'], $form_data);
        if ($captcha_result !== true) {
            wp_redirect($this->add_url_param(wp_get_referer(), 'form_error', $captcha_result['code']));
            exit;
        }

        // Remove honeypot field from the submission data
        unset($form_data['ht_form_hp_email'], $form_data['ht_form_timestamp']);

        // Remove captcha responses from the submission data
        unset($form_data['g-recaptcha-response']);
        unset($form_data['h-captcha-response']);
        $captcha_field = Helper::get_form_captcha_field($form['fields']);
        if (!empty($captcha_field)) {
            unset($form_data[$captcha_field['name']]);
        }

        $submission = SubmissionEndpoint::get_instance();
        $form_data = $submission->sanitize_data($form_id, $form_data, $form['fields']);
        if (empty($form_data)) {
            wp_redirect($this->add_url_param(wp_get_referer(), 'form_error', 'invalid_form_data'));
            exit;
        }
        // Basic validation
        $errors = $submission->validate_data($form_data, $form);
            
        if (!empty($errors)) {
            wp_redirect($this->add_url_param(wp_get_referer(), 'form_error', 'validation_failed'));
            exit;
        }

        $form_data = $submission->handle_files_upload($form_data, $form);

        // Get meta data
        $meta = [
            'user_id'     => get_current_user_id(),
            'ip_address'  => !empty($this->global_settings['miscellaneous']['disable_ip_logging']) ? '' : Helper::get_ip(),
            'browser'     => Helper::get_browser(),
            'device'      => Helper::get_device(),
            'created_at'  => current_time('mysql'),
            'updated_at'  => current_time('mysql')
        ];
        
        // Initialize Mailer class for handling the submission
        if (!empty($settings->notification['settings']['enable_notification'])) {
            $mailer = Mailer::get_instance($form, $form_data, $meta);
            $send = $mailer->send();
            if (is_wp_error($send)) {
                // Redirect back with error
                wp_redirect($this->add_url_param(wp_get_referer(), 'form_error', $send->get_error_code()));
                exit;
            }
        }

        // Store submission in database
        if (!empty($settings->general['settings']['store_submissions'])) {
            $form_data['form_id'] = $form_id;
            $result = $this->entries->create($form_data, $meta);
            if (is_wp_error($result)) {
                wp_redirect($this->add_url_param(wp_get_referer(), 'form_error', 'database_error'));
                exit;
            }
        }

        // Form Confirmation
        $redirect_url = wp_get_referer();
        $confirmation = $settings->confirmation['settings'] ?? [];

        // Handle confirmation redirect if set
        if (!empty($confirmation['confirmation_type']) && 
            $confirmation['confirmation_type'] === 'redirect' && 
            !empty($confirmation['confirmation_redirect'])) {
            
            $redirect_url = esc_url($confirmation['confirmation_redirect']);
            
            // Check if we should open in new tab
            if (!empty($confirmation['confirmation_new_tab'])) {
                // For non-JS, we can't open in new tab, so we'll just redirect
                wp_redirect($redirect_url);
                exit;
            }
        } else if (!empty($confirmation['confirmation_type']) && $confirmation['confirmation_type'] === 'page' && !empty($confirmation['confirmation_page'])) {
            // Get the page URL
            $redirect_url = get_permalink($confirmation['confirmation_page']);
        } else {
            // Default to message on same page
            $redirect_url = $this->add_url_param(wp_get_referer(), 'form_success', $form_id);
        }
        
        // Run action hook for third-party integrations
        do_action('ht_form/after_submission', $form, $form_data, $meta);

        // Redirect to success URL
        wp_redirect($redirect_url);
        exit;
    }

    /**
     * Show success message
     * @param mixed $form
     * @return void
     */
    private function show_success_message($form) {
        $settings = $form['settings'] ?? [];
        $confirmation_message = '';
            
        if (!empty($settings->confirmation['settings']['confirmation_message'])) {
            $confirmation_message = $settings->confirmation['settings']['confirmation_message'];
        } else {
            $confirmation_message = __('Thanks for contacting us! We will be in touch with you shortly.', 'ht-contactform');
        }
            
        echo '<div class="ht-form-message"><div class="ht-form-success">' . wp_kses_post($confirmation_message) . '</div></div>';
            // Continue to display the form below the message
    }

    /**
     * Show error message
     * @param mixed $form_error
     * @return void
     */
    private function show_error_message($form_error, $form) {
        $settings = $form['settings'] ?? [];
        $error_message = '';
        switch ($form_error) {
            case 'invalid_request':
                $error_message = __('Invalid form request. Please try again.', 'ht-contactform');
                break;
            case 'invalid_nonce':
                $error_message = __('Security check failed. Please try again.', 'ht-contactform');
                break;
            case 'invalid_form':
                $error_message = __('Invalid form ID. Please try again.', 'ht-contactform');
                break;
            case 'invalid_form_data':
                $error_message = __('Form is missing. Please try again.', 'ht-contactform');
                break;
            case 'validation_failed':
                $error_message = __('Form validation failed. Please try again.', 'ht-contactform');
                break;
            case 'form_not_found':
                $error_message = __('Form not found. Please try again.', 'ht-contactform');
                break;
            case 'submission_too_quick':
                $error_message = __('Submission was too quick. Please wait a moment and try again.', 'ht-contactform');
                break;
            case 'honeypot_filled':
                $error_message = __('Spam detected. Please try again.', 'ht-contactform');
                break;
            case 'database_error':
                $error_message = __('An error occurred while storing your submission.', 'ht-contactform');
                break;
            case 'recaptcha_required':
                $error_message = __('Please complete the reCAPTCHA challenge.', 'ht-contactform');
                break;
            case 'recaptcha_connection_failed':
                $error_message = __('Failed to connect to reCAPTCHA server.', 'ht-contactform');
                break;
            case 'recaptcha_failed':
                $error_message = __('reCAPTCHA verification failed. Please try again.', 'ht-contactform');
                break;
            case 'hcaptcha_required':
                $error_message = __('Please complete the hCaptcha challenge.', 'ht-contactform');
                break;
            case 'hcaptcha_connection_failed':
                $error_message = __('Failed to connect to hCaptcha server.', 'ht-contactform');
                break;
            case 'hcaptcha_failed':
                $error_message = __('hCaptcha verification failed. Please try again.', 'ht-contactform');
                break;
            case 'ip_restricted':
                $error_message = $settings->form_restriction['settings']['restrict_ip_message'] ?? __('Your IP address is restricted. Please try again.', 'ht-contactform');
                break;
            case 'country_restricted':
                $error_message = $settings->form_restriction['settings']['restrict_country_message'] ?? __('Your country is restricted. Please try again.', 'ht-contactform');
                break;
            default:
                $error_message = __('An error occurred while processing your submission. Please try again.', 'ht-contactform');
                break;
        }
        
        echo '<div class="ht-form-message"><div class="ht-form-error">' . esc_html($error_message) . '</div></div>';
    }

    /**
     * Enqueue frontend assets
     *
     * @return void
     */
    private function enqueue_assets() {

        $style_handles = [];
        $script_handles = [];

        foreach ($this->assets as $asset) {
            wp_enqueue_style("ht-{$asset}");
            $style_handles[] = "ht-{$asset}";
            wp_enqueue_script("ht-{$asset}");
            $script_handles[] = "ht-{$asset}";
        }

        // Enqueue styles
        wp_enqueue_style( 'ht-form');
        $style_handles[] = 'ht-form';

        wp_enqueue_script('ht-axios');
        $script_handles[] = 'ht-axios';

        // Enqueue scripts
        wp_enqueue_script('ht-form');
        $script_handles[] = 'ht-form';

        // Localize script
        $l10n = [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ht_form_ajax_nonce'),
            'rest_url' => rest_url(),
            'rest_nonce' => wp_create_nonce('wp_rest'),
            'plugin_url' => HTCONTACTFORM_PL_URL,
            'upload_url' => wp_upload_dir()['baseurl'],
            'captcha' => [
                'recaptcha_active_version' => $this->global_settings['captcha']['recaptcha_active_version'] ?? '',
                'recaptcha_site_key' => $this->get_active_recaptcha_site_key(),
                'hcaptcha_site_key' => $this->global_settings['captcha']['hcaptcha_site_key'] ?? '',
            ],
            'i18n' => $this->global_settings['validation_messages'] ?? [
                "character_limit" => __("You have exceeded the number of allowed characters.", 'ht-contactform'),
                "email" => __("Please enter a valid email address.", 'ht-contactform'),
                "input_mask" => __("Please enter a valid {format} format.", 'ht-contactform'),
                "phone" => __("Please enter a valid phone number.", 'ht-contactform'),
                "maximum_number" => __("You have exceeded the number of allowed maximum.", 'ht-contactform'),
                "minimum_number" => __("You have exceeded the number of allowed minimum.", 'ht-contactform'),
                "number" => __("Please enter a valid number.", 'ht-contactform'),
                "required" => __("This field is required.", 'ht-contactform'),
                "selection_limit" => __("You have exceeded the number of allowed selections.", 'ht-contactform'),
                "url" => __("Please enter a valid URL.", 'ht-contactform'),
            ],
        ];
        wp_localize_script('ht-form', 'ht_form', $l10n);

        // If the form is rendered after WordPress has already printed the
        // footer script/style batch (e.g. a shortcode echoed from a plugin
        // hooked into wp_footer at a very late priority, such as a popup),
        // the assets above would never be output. Flush them immediately;
        // wp_print_scripts()/wp_print_styles() skip handles already
        // printed, so this is a no-op in the normal early-enqueue case.
        if (did_action('wp_print_footer_scripts')) {
            wp_print_styles($style_handles);
            wp_print_scripts($script_handles);

            // wp_print_scripts() skips 'ht-form' if it was already printed
            // earlier in the request, which would silently drop this
            // render's localized data (e.g. captcha config). Rebuild and
            // overwrite the stored "data" ourselves instead of trusting
            // get_data(): wp_localize_script() PREPENDS to any existing
            // value for this handle rather than replacing it, so a stale
            // earlier declaration would otherwise print alongside this
            // one. Overwriting keeps only this render's data.
            $inline_script = 'var ht_form = ' . wp_json_encode($l10n) . ';';
            wp_scripts()->add_data('ht-form', 'data', $inline_script);
            printf("<script id='ht-form-js-extra'>\n%s\n</script>\n", $inline_script);
        }
    }
}