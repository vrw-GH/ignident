<?php
namespace HTContactForm\Integrations;

use HTContactFormAdmin\Includes\Services\Helper;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\ActiveCampaign as ActiveCampaignEndpoint;
use WP_Error;
use WP_REST_Response;

/**
 * ActiveCampaign Integration Class
 * 
 * Handles form submission integration with ActiveCampaign
 */
class ActiveCampaign {
    /** @var string|null API key */
    private $api_key = null;
    
    /** @var array|null Form configuration */
    private $form = null;
    
    /** @var array|null Form submission data */
    private $form_data = null;
    
    /** @var array|null Form submission metadata */
    private $meta = null;
    
    /** @var Helper Helper instance */
    private $helper = null;
    
    /** @var array Default ActiveCampaign fields */
    private $default_fields = ['email', 'firstName', 'lastName', 'phone'];
    
    /** @var self|null Singleton instance */
    private static $instance = null;

    /**
     * Get instance
     * 
     * @param string $api_key API key
     * @param array $form Form configuration
     * @param array $form_data Form submission data
     * @param array $meta Form submission metadata
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
     * @param string $api_key API key
     * @param array $form Form configuration
     * @param array $form_data Form submission data
     * @param array $meta Form submission metadata
     */
    public function __construct($api_key = null, $form = null, $form_data = null, $meta = null) {
        $this->api_key = is_string($api_key) ? trim($api_key) : '';
        $this->form = $form;
        $this->form_data = $form_data;
        $this->meta = $meta;
        $this->helper = Helper::get_instance();
    }

    /**
     * Subscribe to ActiveCampaign
     * 
     * @param object $integration Integration settings
     * @param array $form Form data
     * @param array $form_data Form submission data
     * @param array $meta Meta data
     * @return WP_Error|WP_REST_Response Whether the ActiveCampaign request was sent successfully
     */
    public function subscribe($integration, $form, $form_data, $meta) {
        // Check if integration is enabled for this form
        if (empty($integration->enabled) || empty($integration->list_id)) {
            return new WP_Error('activecampaign_error', 'Integration is not enabled for this form');
        }

        $data = $this->prepare_data($integration, $form, $form_data, $meta);
        
        // Check for required email field
        if (empty($data['contact']['email'])) {
            error_log('ActiveCampaign Error: Email field is required');
            return new WP_Error('activecampaign_error', 'Email field is required', ['status' => 400]);
        }
        
        // Validate email format
        if (!is_email($data['contact']['email'])) {
            error_log('ActiveCampaign Error: Invalid email address');
            return new WP_Error('activecampaign_error', 'Invalid email address', ['status' => 400]);
        }

        try {
            // Get API client instance
            $activecampaign = ActiveCampaignEndpoint::get_instance();
            
            // Sync the contact with ActiveCampaign
            $response = $activecampaign->sync_contacts($data);
            
            // Handle API errors
            if (is_wp_error($response)) {
                error_log('ActiveCampaign Error: ' . $response->get_error_message());
                do_action('ht_form/activecampaign_integration_result', $response, 'failed', $response->get_error_message());
                return $response;
            }

            $response_data = $response->get_data();
            $contact_id = $response_data['data']['contact']['id'];

            // Add contact to list if specified
            if (!empty($integration->list_id)) {
                $activecampaign->add_contact_to_list($contact_id, $integration->list_id);
            }
            
            // Process tags if specified and contact was created successfully
            if (!empty($integration->tags) && !empty($contact_id)) {
                $this->process_tags($activecampaign, $contact_id, $integration->tags);
            }

            // Add note to contact if specified
            if (!empty($integration->note)) {
                $activecampaign->add_note($contact_id, $integration->note);
            }
            
            // Trigger success action and return response
            do_action('ht_form/activecampaign_integration_result', $response, 'success', 'ActiveCampaign subscription successful');
            return new WP_REST_Response([
                'success' => true,
                'message' => 'ActiveCampaign subscription successful',
                'response' => $response,
            ], 200);
        } catch (\Exception $e) {
            // Log any exceptions
            error_log('ActiveCampaign Exception: ' . $e->getMessage());
            do_action('ht_form/activecampaign_integration_result', null, 'failed', $e->getMessage());
            return new WP_Error('activecampaign_exception', $e->getMessage(), ['status' => 500]);
        }
    }

    /**
     * Process tags for a contact
     * 
     * @param ActiveCampaignEndpoint $api API client instance
     * @param string $contact_id Contact ID
     * @param array $tags Tags to add
     * @return void
     */
    private function process_tags($api, $contact_id, $tags) {
        if (empty($tags) || !is_array($tags)) {
            return;
        }
        try {
            $response = $api->add_tags_to_contact($contact_id, $tags);
            if (is_wp_error($response)) {
                error_log('ActiveCampaign Tag Error: ' . $response->get_error_message());
                return;
            }
        } catch (\Exception $e) {
            error_log('ActiveCampaign Tag Error: ' . $e->getMessage());
        }
    }


    /**
     * Prepare data for ActiveCampaign API
     * 
     * @param object $integration Integration settings
     * @param array $form Form data
     * @param array $form_data Form submission data
     * @param array $meta Meta data
     * @return array Prepared data for ActiveCampaign API
     */
    public function prepare_data($integration, $form, $form_data, $meta) {
        $data = [
            "contact" => [
                "email" => "",
                "firstName" => "",
                "lastName" => "",
                "phone" => "",
                "fieldValues" => [],
            ],
        ];
        
        // Process merge fields
        if (!empty($integration->merge_fields) && is_array($integration->merge_fields)) {
            foreach ($integration->merge_fields as $key => $value) {
                if (empty($value)) {
                    continue;
                }

                // Process dynamic tags in values
                $processed_value = $value;
                if (str_contains($value, '{') && str_contains($value, '}')) {
                    $processed_value = $this->helper->filter_vars($value, $form_data, $form);
                }
                
                // Handle default fields vs custom fields
                if (in_array($key, $this->default_fields)) {
                    $data['contact'][$key] = sanitize_text_field($processed_value);
                } else {
                    // Custom fields need to be added to fieldValues array
                    $data['contact']['fieldValues'][] = [
                        'field' => $key,
                        'value' => sanitize_text_field($processed_value)
                    ];
                }
            }
        }
        return $data;
    }

    /**
     * Format merge fields for ActiveCampaign API
     * 
     * @param array $data The raw merge field data
     * @return array Formatted merge fields ready for ActiveCampaign API
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
                    if (isset($value['first_name'])) {
                        $merge_fields_data['firstName'] = sanitize_text_field($value['first_name']);
                    }
                    if (isset($value['last_name'])) {
                        $merge_fields_data['lastName'] = sanitize_text_field($value['last_name']);
                    }
                } 
                // Handle address fields
                elseif ($this->is_address_field($value)) {
                    $address = $this->get_formatted_address($value);
                    if ($address) {
                        // Add address fields to merge_fields_data
                        foreach ($address as $addr_key => $addr_value) {
                            $merge_fields_data[$addr_key] = $addr_value;
                        }
                    }
                } 
                // Handle other array values
                else {
                    $merge_fields_data[$key] = implode(', ', array_map('sanitize_text_field', $value));
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
     * Check if an array contains name field components
     *
     * @param array $data The data to check
     * @return bool True if it contains name components
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
     * Format address data for ActiveCampaign API
     * 
     * @param array $data Address field data
     * @return array|false Formatted address data or false if required fields are missing
     */
    public function get_formatted_address($data) {
        if (!is_array($data)) {
            return false;
        }
        
        $address_data = [];
        
        // Map form data to ActiveCampaign format
        if (!empty($data['address_line_1'])) {
            $address_data['address1'] = sanitize_text_field($data['address_line_1']);
        }
        if (!empty($data['address_line_2'])) {
            $address_data['address2'] = sanitize_text_field($data['address_line_2']);
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
        if (!empty($address_data['address1']) && !empty($address_data['city'])) {
            return $address_data;
        }
        return false;
    }
}
