<?php

namespace HTContactForm\Integrations;

use HTContactFormAdmin\Includes\Services\Helper;
use WP_Error;
use WP_REST_Response;

/**
 * Zapier Class
 * 
 * Handles zapier functionality for sending form data to external services.
 */
class Zapier {
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
     * @param object $zapier Zapier configuration object
     * @return WP_Error|WP_REST_Response Whether the webhook request was sent successfully or WP_Error on failure
     */
    public function send($zapier) {
        // Check if webhooks are enabled for this form
        if (empty($zapier->enabled) || empty($zapier->url)) {
            error_log("HT Contact Form Zapier Error: Zapier webhook is not enabled or URL is missing");
            return new WP_Error('zapier_error', 'Zapier webhook is not enabled or URL is missing');
        }

        $zapier_url = esc_url_raw($zapier->url);
        if (empty($zapier_url)) {
            error_log("HT Contact Form Zapier Error: Invalid Zapier webhook URL");
            return new WP_Error('zapier_error', 'Invalid Zapier webhook URL');
        }

        // Add custom HTTP headers
        $headers = [
            'Content-Type' => 'application/json',
        ];

        // Prepare data to send in webhook
        $data = $this->prepare_data($zapier);
        
        // Send the webhook request
        $response = wp_remote_post($zapier_url, [
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
                __('Zapier webhook error: %s', 'ht-contactform'),
                $response->get_error_message()
            );
            error_log("HT Contact Form Zapier Error: $error_message");
            do_action('ht_form/zapier_integration_result', $response, 'failed', $error_message);
            return new WP_Error('zapier_error', $error_message);
        }
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code < 200 || $response_code >= 300) {
            $error_message = sprintf(
                __('Zapier webhook returned error code: %s', 'ht-contactform'),
                $response_code
            );
            error_log("HT Contact Form Zapier Error: $error_message");
            do_action('ht_form/zapier_integration_result', $response, 'failed', $error_message);
            return new WP_Error('zapier_error', $error_message);
        }


        // Trigger hooks for external tracking
        do_action('ht_form/zapier_integration_result', $response, 'success', 'Zapier notification sent successfully');

        return new WP_REST_Response([
            'message' => 'Zapier notification sent successfully',
            'response' => $response,
        ], 200);
    }

    /**
     * Prepare data for webhook
     * 
     * @param object $zapier Zapier configuration object
     * @return array Data to send in webhook
     */
    private function prepare_data($zapier) {
        
        $data = $this->prepare_fields_data($zapier);

        $data = array_filter($data);

        // Allow customizing webhook data
        return apply_filters('ht_form/zapier_data', $data, $this->form, $this->form_data);
    }

    /**
     * Prepare fields data for Zapier message
     * 
     * @param object $zapier Zapier configuration object
     * @return array Array of field data for Zapier
     */
    public function prepare_fields_data($zapier) {
        $data = [];
        
        // Selected fields mode
        if (isset($zapier->fields) && !empty($zapier->fields)) {
            foreach ($zapier->fields as $field) {
                $value = $this->form_data[$field] ?? '';
                $data[$field] = $value;
            }
        }
        
        return $data;
    }
}