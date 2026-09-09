<?php

namespace HTContactForm\Integrations;

use HTContactFormAdmin\Includes\Services\Helper;
use WP_Error;
use WP_REST_Response;

/**
 * Slack Class
 * 
 * Handles slack functionality for sending form data to external services.
 */
class Slack {
    //-------------------------------------------------------------------------
    // PROPERTIES
    //-------------------------------------------------------------------------

    /** @var array|null Form data */
    private $form = null;
    /** @var array|null Form fields */
    private $fields = [];
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
     * 
     * @param array|null $form Form configuration
     * @param array|null $form_data Submitted form data
     * @param array|null $meta Entry metadata
     */
    public function __construct($form = null, $form_data = null, $meta = null) {
        if(!empty($form)) {
            $this->form = $form;
            $this->fields = !empty($form['fields']) ? $form['fields'] : [];
        }
        if(!empty($form_data)) {
            $this->form_data = $form_data;
        }
        if(!empty($meta)) {
            $this->meta = $meta;
        }
        $this->helper = Helper::get_instance();
    }

    /**
     * Send data to webhook
     * 
     * @param object $slack Slack configuration object
     * @return WP_Error|WP_REST_Response Whether the webhook request was sent successfully or WP_Error on failure
     */
    public function send($slack) {
        // Check if webhooks are enabled for this form
        if (empty($slack->enabled) || empty($slack->url)) {
            error_log("HT Contact Form Slack Error: Slack webhook is not enabled or URL is missing");
            return new WP_Error('slack_error', 'Slack webhook is not enabled or URL is missing');
        }

        $slack_url = esc_url_raw($slack->url);
        if (empty($slack_url)) {
            error_log("HT Contact Form Slack Error: Invalid Slack webhook URL");
            return new WP_Error('slack_error', 'Invalid Slack webhook URL');
        }

        // Add custom HTTP headers
        $headers = [
            'Content-Type' => 'application/json',
        ];

        // Prepare data to send in webhook
        $data = $this->prepare_data($slack);
        
        // Send the webhook request
        $response = wp_remote_post($slack_url, [
            'method'      => 'POST',
            'timeout'     => 30,
            'redirection' => 5,
            'httpversion' => '1.1',
            'headers'     => $headers,
            'body'        => wp_json_encode($data),
            'user-agent'  => 'HT Contact Form/' . HTCONTACTFORM_VERSION,
        ]);

        // Check for errors
        if (is_wp_error($response)) {
            $error_message = sprintf(
                __('Slack webhook error: %s', 'ht-contactform'),
                $response->get_error_message()
            );
            error_log("HT Contact Form Slack Error: $error_message");
            do_action('ht_form/slack_integration_result', $response, 'failed', $error_message);
            return new WP_Error('slack_error', $error_message);
        }
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code < 200 || $response_code >= 300) {
            $error_message = sprintf(
                __('Slack webhook returned error code: %s', 'ht-contactform'),
                $response_code
            );
            error_log("HT Contact Form Slack Error: $error_message");
            do_action('ht_form/slack_integration_result', $response, 'failed', $error_message);
            return new WP_Error('slack_error', $error_message);
        }


        // Trigger hooks for external tracking
        do_action('ht_form/slack_integration_result', $response, 'success', 'Slack notification sent successfully');

        return new WP_REST_Response([
            'message' => 'Slack notification sent successfully',
            'response' => $response,
        ], 200);
    }

    /**
     * Prepare data for webhook
     * 
     * @param object $slack Slack configuration object
     * @return array Data to send in webhook
     */
    private function prepare_data($slack) {
        $form_title = $this->form['title'] ?? __('Unknown Form', 'ht-contactform');
        $text = sprintf(__('New entry submitted on %s', 'ht-contactform'), $form_title);
        // Create a proper admin URL to the form entries
        $admin_url = admin_url('admin.php');
        $form_id = $this->form['id'] ?? 0;
        $entries_url = add_query_arg(
            [
                'page' => 'htcontact-form',
                'path' => 'entries',
                'form_id' => $form_id
            ],
            $admin_url
        );
        
        $data = [
            'text' => $text,
            'attachments' => [
                [
                    'color'       => '#2eb886',
                    'title'       => sprintf(__('Form: %s', 'ht-contactform'), $form_title),
                    'title_link'  => $entries_url,
                    'fields'      => $this->prepare_fields_data($slack),
                    'footer'      => $slack->footer ?? '',
                    'ts'          => time(),
                ]
            ],
        ];

        $data = array_filter($data);

        // Allow customizing webhook data
        return apply_filters('ht_form/slack_data', $data, $this->form, $this->form_data);
    }

    /**
     * Prepare fields data for Slack message
     * 
     * @param object $slack Slack configuration object
     * @return array Array of field data for Slack
     */
    public function prepare_fields_data($slack) {
        $fields = [];
        
        // Selected fields mode
        if (isset($slack->fields) && !empty($slack->fields)) {
            foreach ($slack->fields as $field) {
                $label = $this->helper->get_field_admin_label($this->fields, $field);
                $field_type = $this->helper->get_field_type($this->fields, $field);
                $value = $this->form_data[$field] ?? '';

                if(is_array($value)) {
                    $value = $this->helper->format_array_value($value, $field_type);
                }
                // Format based on field type
                $value = $this->helper->format_field_value($value, $field_type);

                if($value && $label) {
                    $fields[] = [
                        'title' => $label,
                        'value' => sanitize_text_field($value),
                        'short' => true,
                    ];
                }
            }
        }
        
        return $fields;
    }
}