<?php

namespace HTContactForm\Integrations;

use HTContactFormAdmin\Includes\Services\Helper;
use WP_Error;
use WP_REST_Response;

/**
 * SupportGenix Class
 * 
 * Handles supportGenix functionality for sending form data to external services.
 */
class SupportGenix {
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
     * Send data to supportGenix
     * 
     * @param object $supportgenix supportGenix configuration object
     * @return WP_Error|WP_REST_Response|array Whether the webhook request was sent successfully or WP_Error on failure
     */
    public function send($supportgenix) {
        // Check if webhooks are enabled for this form
        if (empty($supportgenix->enabled)) {
            error_log("HT Contact Form SupportGenix Error: SupportGenix webhook is not enabled");
            return new WP_Error('supportGenix_error', 'SupportGenix webhook is not enabled');
        }

        // Prepare data to send in webhook
        $data = $this->prepare_data($supportgenix);

        if (class_exists('Apbd_wps_ht_contact_form')) {
            $instance = \Apbd_wps_ht_contact_form::GetModuleInstance();
            $instance->CreateTicket($data);
        }

        return $data;
    }

    /**
     * Prepare data for webhook
     * 
     * @param object $supportgenix supportGenix configuration object
     * @return array Data to send in webhook
     */
    private function prepare_data($supportgenix) {
        $data = $this->prepare_fields_data($supportgenix);

        $data = array_filter($data);

        // Allow customizing webhook data
        return apply_filters('ht_form/supportgenix_data', $data, $this->form, $this->form_data);
    }

    /**
     * Prepare fields data for supportGenix message
     * 
     * @param object $supportgenix supportGenix configuration object
     * @return array Array of field data for supportGenix
     */
    public function prepare_fields_data($supportgenix) {
        $data = [];
        foreach ($supportgenix as $key => $value) {
            if($key === 'ticket_customer_fields') {
                foreach ($value as $sub) {
                    if(!empty($sub['value']) && (str_contains($sub['value'], '{') || str_contains($sub['value'], '}'))) {
                        $data["ticket_custom_fields__{$sub['key']}"] = $this->helper->filter_vars($sub['value'], $this->form_data, $this->form);
                    } else {
                        $data["ticket_custom_fields__{$sub['key']}"] = $sub['value'];
                    }
                }
            } elseif($key === 'custom_fields'){
                foreach ($value as $key => $subValue) {
                    if(!empty($subValue) && (str_contains($subValue, '{') || str_contains($subValue, '}'))) {
                        $data["ticket_custom_fields__{$key}"] = $this->helper->filter_vars($subValue, $this->form_data, $this->form);
                    } else {
                        $data["ticket_custom_fields__{$key}"] = $subValue;
                    }
                }
            } else {
                if(!empty($value) && (str_contains($value, '{') || str_contains($value, '}'))) {
                    $data[$key] = $this->helper->filter_vars($value, $this->form_data, $this->form);
                    if($key === 'ticket_attachments') {
                        $data[$key] = explode(', ', $data[$key]);
                    }
                } else {
                    $data[$key] = $value;
                }
            }
        }
        if(is_user_logged_in()) {
            $user = wp_get_current_user();
            $data['user_email'] = $user->user_email;
            $data['user_first_name'] = $user->first_name;
            $data['user_last_name'] = $user->last_name;
        }
        return $data;
    }
}