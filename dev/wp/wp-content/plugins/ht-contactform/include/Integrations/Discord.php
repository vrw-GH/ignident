<?php

namespace HTContactForm\Integrations;

use HTContactFormAdmin\Includes\Services\Helper;
use WP_Error;
use WP_REST_Response;

/**
 * Discord Class
 * 
 * Handles discord functionality for sending form data to external services.
 */
class Discord {
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
     * @param object $discord Discord configuration object
     * @return WP_Error|WP_REST_Response Whether the webhook request was sent successfully or WP_Error on failure
     */
    public function send($discord) {
        // Check if webhooks are enabled for this form
        if (empty($discord->enabled) || empty($discord->url)) {
            error_log("HT Contact Form Discord Error: Discord webhook is not enabled or URL is missing");
            return new WP_Error('discord_error', 'Discord webhook is not enabled or URL is missing');
        }

        $discord_url = esc_url_raw($discord->url);
        if (empty($discord_url)) {
            error_log("HT Contact Form Discord Error: Invalid Discord webhook URL");
            return new WP_Error('discord_error', 'Invalid Discord webhook URL');
        }

        // Add custom HTTP headers
        $headers = [
            'Content-Type' => 'application/json',
        ];

        // Prepare data to send in webhook
        $data = $this->prepare_data($discord);
        
        // Send the webhook request
        $response = wp_remote_post($discord_url, [
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
                __('Discord webhook error: %s', 'ht-contactform'),
                $response->get_error_message()
            );
            error_log("HT Contact Form Discord Error: $error_message");
            do_action('ht_form/discord_integration_result', $response, 'failed', $error_message);
            return new WP_Error('discord_error', $error_message);
        }
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code < 200 || $response_code >= 300) {
            $error_message = sprintf(
                __('Discord webhook returned error code: %s', 'ht-contactform'),
                $response_code
            );
            error_log("HT Contact Form Discord Error: $error_message");
            do_action('ht_form/discord_integration_result', $response, 'failed', $error_message);
            return new WP_Error('discord_error', $error_message);
        }


        // Trigger hooks for external tracking
        do_action('ht_form/discord_integration_result', $response, 'success', 'Discord notification sent successfully');

        return new WP_REST_Response([
            'message' => 'Discord notification sent successfully',
            'response' => $response,
        ], 200);
    }

    /**
     * Prepare data for webhook
     * 
     * @param object $discord Discord configuration object
     * @return array Data to send in webhook
     */
    private function prepare_data($discord) {
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
            'content' => $text,
            'embeds' => [
                [
                    'color'       => hexdec('3F9EFF'),
                    'title'       => sprintf(__('Form: %s', 'ht-contactform'), $form_title),
                    'url'         => $entries_url,
                    'fields'      => $this->prepare_fields_data($discord),
                    'footer'      => [
                        'text' => $discord->footer ?? ''
                    ],
                ]
            ],
        ];

        $data = array_filter($data);

        // Allow customizing webhook data
        return apply_filters('ht_form/discord_data', $data, $this->form, $this->form_data);
    }

    /**
     * Prepare fields data for Discord message
     * 
     * @param object $discord Discord configuration object
     * @return array Array of field data for Discord
     */
    public function prepare_fields_data($discord) {
        $fields = [];
        
        // Selected fields mode
        if (isset($discord->fields) && !empty($discord->fields)) {
            foreach ($discord->fields as $field) {
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
                        'name' => $label,
                        'value' => sanitize_text_field($value),
                        'inline' => true,
                    ];
                }
            }
        }
        
        return $fields;
    }
}