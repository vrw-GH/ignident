<?php
namespace HTContactForm\Integrations;

use HTContactFormAdmin\Includes\Services\Helper;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\ConstantContact as ConstantContactEndpoint;
use WP_Error;
use WP_REST_Response;

/**
 * ConstantContact Integration Class
 * 
 * Handles form submission integration with ConstantContact
 */
class ConstantContact {
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
    
    /** @var array Default ConstantContact fields */
    private $default_fields = ['email', 'firstName', 'lastName', 'phone'];

    /**
     * Constructor
     * 
     * @param string $api_key API key
     * @param array $form Form configuration
     * @param array $form_data Form submission data
     * @param array $meta Form submission metadata
     */
    public function __construct($form = null, $form_data = null, $meta = null) {
        $this->form = $form;
        $this->form_data = $form_data;
        $this->meta = $meta;
        $this->helper = Helper::get_instance();
    }

    /**
     * Subscribe to ConstantContact
     * 
     * @param object $integration Integration settings
     * @return WP_Error|WP_REST_Response Whether the ConstantContact request was sent successfully
     */
    public function subscribe($integration) {

        // Check if integration is enabled for this form
        if (empty($integration->enabled) || empty($integration->list_id)) {
            return new WP_Error('constantcontact_error', 'Integration is not enabled for this form');
        }

        $data = $this->prepare_data($integration, $this->form, $this->form_data, $this->meta);
        
        // Check for required email field
        if (empty($data['email_address']['address'])) {
            error_log('ConstantContact Error: Email field is required');
            return new WP_Error('constantcontact_error', 'Email field is required', ['status' => 400]);
        }
        
        // Validate email format
        if (!is_email($data['email_address']['address'])) {
            error_log('ConstantContact Error: Invalid email address');
            return new WP_Error('constantcontact_error', 'Invalid email address', ['status' => 400]);
        }

        try {
            // Get API client instance
            $constantcontact = new ConstantContactEndpoint($integration->client_id, $integration->client_secret);
            
            // Sync the contact with ConstantContact
            $response = $constantcontact->subscribe($data);
            
            // Handle API errors
            if (is_wp_error($response)) {
                error_log('ConstantContact Error: ' . $response->get_error_message());
                do_action('ht_form/constantcontact_integration_result', $response, 'failed', $response->get_error_message());
                return $response;
            }
            
            // Trigger success action and return response
            do_action('ht_form/constantcontact_integration_result', $response, 'success', 'ConstantContact subscription successful');
            return new WP_REST_Response([
                'success' => true,
                'message' => 'ConstantContact subscription successful',
                'response' => $response,
            ], 200);
        } catch (\Exception $e) {
            error_log('ConstantContact Exception: ' . $e->getMessage());
            do_action('ht_form/constantcontact_integration_result', null, 'failed', $e->getMessage());
            return new WP_Error('constantcontact_exception', $e->getMessage(), ['status' => 500]);
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
            "email_address" => [
                "address" => "",
                "permission_to_send" => ""
            ],
            "first_name" => "",
            "last_name" => "",
            "job_title" => '',
            "company_name" => '',
            "create_source" => 'Contact',
            "birthday_month" => '',
            "birthday_day" => '',
            "anniversary" => current_time('Y-m-d'),
            'custom_field' => [],
            'phone_numbers' => [],
            'street_addresses' => [],
            'list_memberships' => [],
            'taggings' => [],
            'notes' => [],
        ];

        // Fields
        if(!empty($integration->fields['email_address'])) {
            $data['email_address']['address'] = $this->helper->filter_vars($integration->fields['email_address'], $form_data, $form);
        }
        if(!empty($integration->fields['permission_to_send'])) {
            $data['email_address']['permission_to_send'] = $integration->fields['permission_to_send'];
        }
        if(!empty($integration->fields['first_name'])) {
            $data['first_name'] = $this->helper->filter_vars($integration->fields['first_name'], $form_data, $form);
        }
        if(!empty($integration->fields['last_name'])) {
            $data['last_name'] = $this->helper->filter_vars($integration->fields['last_name'], $form_data, $form);
        }
        if(!empty($integration->fields['job_title'])) {
            $data['job_title'] = $this->helper->filter_vars($integration->fields['job_title'], $form_data, $form);
        }
        if(!empty($integration->fields['company_name'])) {
            $data['company_name'] = $this->helper->filter_vars($integration->fields['company_name'], $form_data, $form);
        }
        if(!empty($integration->fields['birthday_month'])) {
            $data['birthday_month'] = $this->helper->filter_vars($integration->fields['birthday_month'], $form_data, $form);
        }
        if(!empty($integration->fields['birthday_day'])) {
            $data['birthday_day'] = $this->helper->filter_vars($integration->fields['birthday_day'], $form_data, $form);
        }
        if(!empty($integration->fields['anniversary'])) {
            $data['anniversary'] = $this->helper->filter_vars($integration->fields['anniversary'], $form_data, $form);
        }
        if(!empty($integration->fields['home_phone'])) {
            $data['phone_numbers'][] = [
                'phone_number' => $this->helper->filter_vars($integration->fields['home_phone'], $form_data, $form),
                'kind' => 'home'
            ];
        }
        if(!empty($integration->fields['work_phone'])) {
            $data['phone_numbers'][] = [
                'phone_number' => $this->helper->filter_vars($integration->fields['work_phone'], $form_data, $form),
                'kind' => 'work'
            ];
        }
        if(!empty($integration->fields['mobile_phone'])) {
            $data['phone_numbers'][] = [
                'phone_number' => $this->helper->filter_vars($integration->fields['mobile_phone'], $form_data, $form),
                'kind' => 'mobile'
            ];
        }
        if(!empty($integration->fields['home_address_street']) || !empty($integration->fields['home_address_city']) || !empty($integration->fields['home_address_state']) || !empty($integration->fields['home_address_postal_code']) || !empty($integration->fields['home_address_country'])) {
            $data['street_addresses'][] = [
                'kind' => 'home',
                'street' => $this->helper->filter_vars($integration->fields['home_address_street'], $form_data, $form),
                'city' => $this->helper->filter_vars($integration->fields['home_address_city'], $form_data, $form),
                'state' => $this->helper->filter_vars($integration->fields['home_address_state'], $form_data, $form),
                'postal_code' => $this->helper->filter_vars($integration->fields['home_address_postal_code'], $form_data, $form),
                'country' => $this->helper->filter_vars($integration->fields['home_address_country'], $form_data, $form)
            ];
        }
        if(!empty($integration->fields['work_address_street']) || !empty($integration->fields['work_address_city']) || !empty($integration->fields['work_address_state']) || !empty($integration->fields['work_address_postal_code']) || !empty($integration->fields['work_address_country'])) {
            $data['street_addresses'][] = [
                'kind' => 'work',
                'street' => $this->helper->filter_vars($integration->fields['work_address_street'], $form_data, $form),
                'city' => $this->helper->filter_vars($integration->fields['work_address_city'], $form_data, $form),
                'state' => $this->helper->filter_vars($integration->fields['work_address_state'], $form_data, $form),
                'postal_code' => $this->helper->filter_vars($integration->fields['work_address_postal_code'], $form_data, $form),
                'country' => $this->helper->filter_vars($integration->fields['work_address_country'], $form_data, $form)
            ];
        }
        if(!empty($integration->fields['other_address_street']) || !empty($integration->fields['other_address_city']) || !empty($integration->fields['other_address_state']) || !empty($integration->fields['other_address_postal_code']) || !empty($integration->fields['other_address_country'])) {
            $data['street_addresses'][] = [
                'kind' => 'other',
                'street' => $this->helper->filter_vars($integration->fields['other_address_street'], $form_data, $form),
                'city' => $this->helper->filter_vars($integration->fields['other_address_city'], $form_data, $form),
                'state' => $this->helper->filter_vars($integration->fields['other_address_state'], $form_data, $form),
                'postal_code' => $this->helper->filter_vars($integration->fields['other_address_postal_code'], $form_data, $form),
                'country' => $this->helper->filter_vars($integration->fields['other_address_country'], $form_data, $form)
            ];
        }

        // Tags
        if(!empty($integration->tags)) {
            $data['taggings'] = $integration->tags;
        }

        // List ID
        if(!empty($integration->list_id)) {
            $data['list_memberships'][] = $integration->list_id;
        }

        // Custom Fields
        if(!empty($integration->custom_fields)) {
            foreach ($integration->custom_fields as $key => $value) {
                $data['custom_field'][] = [
                    'custom_field_id' => $key,
                    'value' => $value
                ];
            }
        }

        return $data;
    }
}
