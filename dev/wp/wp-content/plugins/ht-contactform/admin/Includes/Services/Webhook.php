<?php

namespace HTContactFormAdmin\Includes\Services;

use HTContactFormAdmin\Includes\Services\Helper;
use WP_Error;
use WP_REST_Response;

/**
 * Webhook Class
 * 
 * Handles webhook functionality for sending form data to external services.
 */
class Webhook {
    //-------------------------------------------------------------------------
    // PROPERTIES
    //-------------------------------------------------------------------------

    /** @var array|null Form data */
    private $form = null;
    /** @var array|null Form fields */
    private $fields = [];
    /** @var object|null Form settings */
    private $settings = null;
    /** @var object|null Webhook settings */
    private $webhook = null;
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
            $this->webhook = !empty($form['settings']->webhook['settings']) ? (object) $form['settings']->webhook['settings'] : (object)[];
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
     * @return WP_Error|WP_REST_Response Whether the webhook request was sent successfully
     */
    public function send($webhook) {
        // Check if webhooks are enabled for this form
        if (empty($webhook->enabled) || empty($webhook->url)) {
            return new WP_Error('webhook_error', 'Webhooks are not enabled for this form');
        }

        $webhook_url = esc_url_raw($webhook->url);
        if (empty($webhook_url)) {
            return new WP_Error('webhook_error', 'Invalid webhook URL');
        }
        
        // Add custom HTTP headers if provided
        $headers = [
            'Content-Type' => 'application/json',
        ];

        // Add any custom headers
        if (isset($webhook->header_type) && $webhook->header_type === 'with_headers' && !empty($webhook->headers) && is_array($webhook->headers)) {
            foreach ($webhook->headers as $header) {
                if (!empty($header['key']) && !empty($header['value'])) {
                    if(str_contains($header['key'], '{') || str_contains($header['key'], '}')) {
                        $headers[sanitize_key($header['key'])] = $this->helper->filter_vars($header['value'], $this->form_data);
                    } else {
                        $headers[sanitize_key($header['key'])] = sanitize_text_field($header['value']);
                    }
                }
            }
        }

        // Prepare data to send in webhook
        $data = [];
        if (isset($webhook->body_fields) && $webhook->body_fields === 'selected_fields' && !empty($webhook->fields) && is_array($webhook->fields)) {
            foreach ($webhook->fields as $field) {
                if (!empty($field['key']) && !empty($field['value'])) {
                    if(str_contains($field['value'], '{') || str_contains($field['value'], '}')) {
                        $data[$field['key']] = $this->helper->filter_vars($field['value'], $this->form_data);
                    } else {
                        $data[$field['key']] = sanitize_text_field($field['value']);
                    }
                }
            }
        } elseif (isset($webhook->body_fields) && $webhook->body_fields === 'all_fields') {
            $data = $this->prepare_data();
        }
        
        // Send the webhook request
        $response = wp_remote_post($webhook_url, [
            'method'      => $webhook->method,
            'timeout'     => 30,
            'redirection' => 5,
            'httpversion' => '1.1',
            'headers'     => $headers,
            'body'        => wp_json_encode($data),
            'cookies'     => []
        ]);

        if (is_wp_error($response)) {
            error_log('Webhook Error: ' . $response->get_error_message());
            // Trigger hooks for external tracking
            do_action('ht_form/webhook_integration_result', $response, 'failed', $response->get_error_message());
            return new WP_Error('webhook_error', $response->get_error_message());
        }

        // Trigger hooks for external tracking
        do_action('ht_form/webhook_integration_result', $response, 'success', 'Webhook sent successfully');

        return new WP_REST_Response([
            'message' => 'Webhook sent successfully',
            'response' => $response,
        ], 200);
    }

    /**
     * Prepare data for webhook
     * 
     * @return array Data to send in webhook
     */
    private function prepare_data() {
        $data = [
            'form_id'     => $this->form['id'],
            'form_title'  => $this->form['title'],
            'form_data'   => $this->form_data,
            'date'        => current_time('mysql'),
            'entry_meta'  => $this->meta
        ];

        $data = array_filter($data);

        // Allow customizing webhook data
        return apply_filters('ht_form/webhook_data', $data, $this->form, $this->form_data);
    }
}