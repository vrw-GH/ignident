<?php
namespace HTContactForm\Integrations;

use HTContactFormAdmin\Includes\Services\Helper;
use MailerLite\MailerLite as MailerLiteApiClient;
use WP_Error;
use WP_REST_Response;

class MailerLite {
    private $api_key = null;
    private $form = null;
    private $form_data = null;
    private $meta = null;
    private $helper = null;

    private static $instance = null;

    /**
     * Get instance
     * 
     * @param string $api_key
     * @param array $form
     * @param array $form_data
     * @param array $meta
     * @return self
     */
    public static function get_instance($api_key = null, $form = null, $form_data = null, $meta = null) {
        if (!isset(self::$instance) || $api_key !== null) {
            self::$instance = new self($api_key, $form, $form_data, $meta);
        }
        return self::$instance;
    }

    /**
     * Constructor
     * 
     * @param string $api_key
     * @param array $form
     * @param array $form_data
     * @param array $meta
     */
    public function __construct($api_key = null, $form = null, $form_data = null, $meta = null) {
        $this->api_key = is_string($api_key) ? trim($api_key) : '';
        $this->form = $form;
        $this->form_data = $form_data;
        $this->meta = $meta;
        $this->helper = Helper::get_instance();
    }

    /**
     * Subscribe to MailerLite
     * 
     * @param object $integration Integration settings
     * @param array $form Form data
     * @param array $form_data Form submission data
     * @param array $meta Meta data
     * @return WP_Error|WP_REST_Response Whether the MailerLite request was sent successfully
     */
    public function subscribe($integration, $form, $form_data, $meta) {
        // Check if integration is enabled for this form
        if (empty($integration->enabled) || empty($integration->group)) {
            return new WP_Error('mailerlite_error', 'Integration is not enabled for this form');
        }

        // Prepare data to send to MailerLite
        $data = $this->prepare_data($integration, $form, $form_data, $meta);

        try {
            $client = new MailerLiteApiClient(['api_key' => $this->api_key]);
            $response = $client->subscribers->create($data);
            
            // Handle API errors
            if (is_wp_error($response)) {
                error_log('MailerLite Error: ' . $response->get_error_message());
                do_action('ht_form/mailerlite_integration_result', $response, 'failed', $response->get_error_message());
                return $response;
            }
            
            // Trigger success action and return response
            do_action('ht_form/mailerlite_integration_result', $response, 'success', 'MailerLite subscription successful');
            return new WP_REST_Response([
                'success' => true,
                'message' => 'MailerLite subscription successful',
                'response' => $response,
            ], 200);
        } catch (\Exception $e) {
            // Log any exceptions
            error_log('MailerLite Exception: ' . $e->getMessage());
            do_action('ht_form/mailerlite_integration_result', null, 'failed', $e->getMessage());
            return new WP_Error('mailerlite_exception', $e->getMessage(), ['status' => 500]);
        }
    }

    /**
     * Prepare data for MailerLite API
     * 
     * @param object $integration Integration settings
     * @param array $form Form data
     * @param array $form_data Form submission data
     * @param array $meta Meta data
     * @return array Prepared data for MailerLite API
     */
    public function prepare_data($integration, $form, $form_data, $meta) {
        $data = [
            'email' => '',
            'fields' => [],
            'groups' => [$integration->group],
            'status' => 'active',
            'subscribed_at' => date('Y-m-d H:i:s'),
        ];

        $data['email'] = $this->helper->filter_vars($integration->merge_fields['email_address'], $form_data, $form);
        
        // Process merge fields
        if (!empty($integration->merge_fields) && is_array($integration->merge_fields)) {
            foreach ($integration->merge_fields as $key => $value) {
                if (empty($value) || $key === 'email_address') {
                    continue;
                }

                // Process dynamic tags in values
                $processed_value = $value;
                if (str_contains($value, '{') && str_contains($value, '}')) {
                    $processed_value = $this->helper->filter_vars($value, $form_data, $form);
                }
                
                $data['fields'][$key] = sanitize_text_field($processed_value);
            }
        }
        $data['fields'] = $this->format_merge_fields($data['fields']);
        return $data;
    }

    /**
     * Format merge fields for MailerLite API
     * 
     * Processes form data to match MailerLite's expected merge field format:
     * - Handles special fields like names and addresses
     * - Sanitizes values based on their type
     * - Skips email_address as it's handled separately
     * 
     * @param array $data The raw merge field data
     * @return array Formatted merge fields ready for MailerLite API
     */
    public function format_merge_fields($data) {
        if (!is_array($data)) {
            return [];
        }

        $merge_fields_data = [];
        
        foreach ($data as $key => $value) {
            // Skip email as it's handled separately in the main data array
            if ($key === 'email') {
                continue;
            }
            
            // Handle array values (complex fields)
            if (is_array($value)) {
                // Handle name fields (combine into a single string)
                if ($this->is_name_field($value)) {
                    $merge_fields_data[$key] = implode(' ', array_filter($value));
                } 
                // Handle address fields (format as MailerLite expects)
                elseif ($this->is_address_field($value)) {
                    $address = $this->get_formatted_address($value);
                    if ($address) {
                        $merge_fields_data[$key] = $address;
                    }
                } 
                // Handle other array values
                else {
                    $merge_fields_data[$key] = array_map('sanitize_text_field', $value);
                }
            } 
            // Handle scalar values
            elseif ($value !== null && $value !== '') {
                if (is_email($value)) {
                    $merge_fields_data[$key] = sanitize_email($value);
                } elseif (is_numeric($value)) {
                    // Preserve float values when needed
                    $merge_fields_data[$key] = strpos($value, '.') !== false ? (float) $value : (int) $value;
                } else {
                    $merge_fields_data[$key] = sanitize_text_field($value);
                }
            }
        }
        
        return $merge_fields_data;
    }
    
    /**
     * Check if an array contains address field components
     *
     * @param array $data The data to check
     * @return bool True if it contains address components
     */
    private function is_name_field($data) {
        $name_fields = [
            'first_name', 'last_name', 'middle_name'
        ];
        
        foreach ($name_fields as $field) {
            if (isset($data[$field])) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if an array contains address field components
     *
     * @param array $data The data to check
     * @return bool True if it contains address components
     */
    private function is_address_field($data) {
        $address_fields = [
            'address_line_1', 'address_line_2', 'address_city', 
            'address_state', 'address_zip', 'address_country'
        ];
        
        foreach ($address_fields as $field) {
            if (isset($data[$field])) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Format address data for Mailchimp API
     * 
     * @param array $data Address field data
     * @return array|false Formatted address data or false if required fields are missing
     */
    public function get_formatted_address($data) {
        if (!is_array($data)) {
            return false;
        }
        
        $address_data = [
            'addr1' => '',
            'addr2' => '',
            'city' => '',
            'state' => '',
            'zip' => '',
            'country' => ''
        ];
        
        // Map your data to Mailchimp format
        if (!empty($data['address_line_1'])) {
            $address_data['addr1'] = sanitize_text_field($data['address_line_1']);
        }
        if (!empty($data['address_line_2'])) {
            $address_data['addr2'] = sanitize_text_field($data['address_line_2']);
        }
        if (!empty($data['address_city'])) {
            $address_data['city'] = sanitize_text_field($data['address_city']);
        }
        if (!empty($data['address_state'])) {
            $address_data['state'] = sanitize_text_field($data['address_state']);
        }
        if (!empty($data['address_zip'])) {
            $address_data['zip'] = sanitize_text_field($data['address_zip']);
        }
        if (!empty($data['address_country'])) {
            $address_data['country'] = sanitize_text_field($data['address_country']);
        }
        
        // Only include if we have the minimum required fields
        if (!empty($address_data['addr1']) && !empty($address_data['city'])) {
            return $address_data;
        }
        return false;
    }
}
