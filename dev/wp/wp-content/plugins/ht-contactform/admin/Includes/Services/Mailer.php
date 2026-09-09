<?php

namespace HTContactFormAdmin\Includes\Services;

/**
 * Mailer Class
 * 
 * Handles mailer functions for the plugin.
 */
class Mailer {
    //-------------------------------------------------------------------------
    // PROPERTIES
    //-------------------------------------------------------------------------

    /** @var array|null Form data */
    private $form = null;
    /** @var array|null Form fields */
    private $fields = [];
    /** @var object|null Form settings */
    private $settings = null;
    /** @var object|null Notification settings */
    private $notification = null;
    /** @var object|null Global settings */
    private $global = null;

    /** @var array|null Form data */
    private $form_data = null; 

    /** @var array|null Entry metadata */
    private $meta = null;

    /** @var Helper|null Helper instance */
    private $helper = null;

    /** @var self|null Singleton instance */
    private static $instance = null;

    //-------------------------------------------------------------------------
    // INITIALIZATION
    //-------------------------------------------------------------------------
    
    /**
     * Get singleton instance
     * 
     * @param array $form Form configuration
     * @param array $form_data Submitted form data
     * @param array $meta Entry metadata
     * @return self
     */
    public static function get_instance($form = null, $form_data = null, $meta = null) {
        if (!isset(self::$instance)) {
            self::$instance = new self($form, $form_data, $meta);
        }
        return self::$instance;
    }

    /**
     * Constructor
     * 
     * Initializes the class and sets up WordPress hooks
     */
    public function __construct($form = null, $form_data = null, $meta = null) {
        if(!empty($form)) {
            $this->form = $form;
            $this->fields = !empty($form['fields']) ? $form['fields'] : [];
            $this->settings = !empty($form['settings']) ? $form['settings'] : (object)[];
            $this->notification = !empty($form['settings']->notification['settings']) ? (object) $form['settings']->notification['settings'] : (object)[];
            $this->global = get_option('ht_form_global_settings', []);
        }
        if(!empty($form_data)) {
            $this->form_data = $form_data;
        }
        if(!empty($meta)) {
            $this->meta = $meta;
        }
        $this->helper = Helper::get_instance();
    }

    //-------------------------------------------------------------------------
    // MAIN OPERATIONS
    //-------------------------------------------------------------------------
    

    /**
     * Send email notification
     * 
     * @return bool Whether the email was sent successfully
     */
    public function send() {
        // Set recipient
        $to = $this->get_recipient();

        // Set subject
        /* translators: %s: form title */
        $subject = $this->notification->form_subject ?? sprintf(__('New Form Entry - %s', 'ht-contactform'), $this->form['title']);
        
        $subject = $this->helper->filter_vars($subject, $this->form_data);
        $notification_body = $this->notification->form_email_body;
        $lines = explode("\n", $notification_body); // Split into lines
        $data = [];
        foreach($lines as $line) {
            if(str_contains($line, '{all_fields}')) {
                $data = array_merge($data, $this->form_data);
            } else {
                $data[] = $this->helper->filter_vars($line, $this->form_data);
            }
        }
        
        $data = array_filter($data, function($key) {
            return $key !== 'form_id' && $key !== '_wp_http_referer' && $key !== 'ht_form_timestamp';
        }, ARRAY_FILTER_USE_KEY);


        // Filter out empty arrays
        $data = array_filter($data, function($value) {
            if (is_array($value)) {
                // Filter out empty values from the array
                $filtered = array_filter($value);
                // Only keep arrays that have values after filtering
                return !empty($filtered);
            }
            return true; // Keep non-array values
        });

        $template = 1;
        if(!empty($this->notification->template)) {
            $template = sanitize_file_name($this->notification->template);
        } else if(!empty($this->global['email']['template'])) {
            $template = sanitize_file_name($this->global['email']['template']);
        }
        
        // Load the email template using the load_email_template method
        $content = $this->load_template("email-{$template}", [
            'subject' => $subject,
            'form' => $this->form,
            'data' => $data,
            'meta' => $this->meta,
            'footer_text' => !empty($this->global['email']['footer_text']) ? $this->global['email']['footer_text'] : ''
        ]);
        
        // Set up email headers
        $headers = $this->get_headers();
        
        // Trigger before email send action
        do_action('ht_form/before_email_send', $this->form, $this->form_data);

        // Send the email
        $send = wp_mail($to, $subject, $content, $headers);

        // Trigger after email send action
        do_action('ht_form/email_sent', $send, $this->form, $this->form_data);

        return $send;
    }

    /**
     * Get email headers
     * 
     * @return array Email headers
     */
    private function get_headers() {
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $this->get_form_name() . ' <' . $this->get_form_email() . '>',
            'Reply-To: ' . $this->get_form_reply_to()
        ];
        $headers = apply_filters('ht_form/email_headers', $headers, $this->form, $this->form_data);
        return $headers;
    }

    /**
     * Get email recipient
     * 
     * @return string Email recipient
     */
    private function get_recipient() {
        $raw = !empty($this->notification->form_send_to_email) ? $this->notification->form_send_to_email : '';

        $emails = [];
        foreach(explode(',', $raw) as $token) {
            $token = trim($token);
            if($token === '') {
                continue;
            }
            if(str_contains($token, '{') || str_contains($token, '}')) {
                $token = $this->helper->filter_vars($token, $this->form_data);
            }
            // A resolved smart tag may itself expand to multiple comma-separated emails
            foreach(explode(',', $token) as $email) {
                $email = trim($email);
                if($email !== '' && is_email($email)) {
                    $emails[] = sanitize_email($email);
                }
            }
        }

        $emails = array_unique($emails);

        if(empty($emails)) {
            return sanitize_email(get_option('admin_email'));
        }

        return implode(', ', $emails);
    }

    /**
     * Get email sender name
     * 
     * @return string Email sender name
     */
    private function get_form_name() {
        if(!isset($this->notification->form_name)) {
            return sanitize_text_field(get_bloginfo('name'));
        }
        // $fields = $this->fields;
        // if(str_contains($this->notification->form_name, '{') && str_contains($this->notification->form_name, '}')) {
        //     foreach($fields as $field) {
        //         if(isset($field['name_attribute']) && !empty($field['name_attribute']) && str_contains($this->notification->form_name, $field['name_attribute'])) {
        //             return sanitize_text_field($this->form_data[$field['name_attribute']]);
        //         }
        //     }
        // }
        return sanitize_text_field($this->helper->filter_vars($this->notification->form_name, $this->form_data));
    }

    /**
     * Get email sender email
     * 
     * @return string Email sender email
     */
    private function get_form_email() {
        if(isset($this->notification->form_email) && is_email($this->notification->form_email)) {
            return sanitize_email($this->notification->form_email);
        }
        if(!empty($this->notification->form_email)) {
            if(str_contains($this->notification->form_email, '{') || str_contains($this->notification->form_email, '}')) {
                $email = $this->helper->filter_vars($this->notification->form_email, $this->form_data);
                if(is_email($email)) {
                    return sanitize_email($email);
                }
            }
        }
        return sanitize_email(get_option('admin_email'));
    }

    /**
     * Get email reply-to address
     * 
     * @return string Email reply-to address
     */
    private function get_form_reply_to() {
        if(isset($this->notification->form_reply_to) && is_email($this->notification->form_reply_to)) {
            return sanitize_email($this->notification->form_reply_to);
        }
        if($this->notification->form_reply_to !== '{admin_email}' && !empty($this->notification->form_reply_to)) {
            if(str_contains($this->notification->form_reply_to, '{') || str_contains($this->notification->form_reply_to, '}')) {
                $reply_to = $this->helper->filter_vars($this->notification->form_reply_to, $this->form_data);
                if(is_email($reply_to)) {
                    return sanitize_email($reply_to);
                }
            }
        }
        return $this->get_form_email();
    }

    /**
     * Load an email template from the plugin's templates directory
     * 
     * @param string $template_name Template name without file extension
     * @param array $args Arguments to pass to the template
     * @return string The template content or empty string if template not found
     */
    private function load_template($template_name, $args = []) {
        // Get the template file path
        $template_path = HTCONTACTFORM_PL_PATH . 'templates/email/' . $template_name . '.php';
        
        // Check if the template file exists
        if (!file_exists($template_path)) {
            return '';
        }
        
        // Start output buffering
        ob_start();
        
        // Include the template file with the provided arguments
        include $template_path;
        
        // Get and return the buffered content
        return ob_get_clean();
    }
}