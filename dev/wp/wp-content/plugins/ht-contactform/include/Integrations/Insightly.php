<?php

namespace HTContactForm\Integrations;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

use HTContactFormAdmin\Includes\Config\Editor\Integrations\Insightly as InsightlyConfig;
use HTContactFormAdmin\Includes\Services\Helper;

/**
 * Insightly Integration Class
 *
 * Handles all Insightly CRM integration functionality including API communication,
 * data validation, and form submission processing.
 *
 * @since 2.4.0
 */
class Insightly {
    
    /**
     * Helper instance
     *
     * @since 2.4.0
     * @var Helper
     */
    private $helper = null;

    /**
     * Insightly API Key
     *
     * @since 2.4.0
     * @var string
     */  
    private string $api_key;
    
    /**
     * Insightly API URL
     *
     * @since 2.4.0
     * @var string
     */
    private string $api_url;

    /**
     * Constructor
     *
     * Initializes the Insightly integration with API credentials from WordPress options.
     *
     * @since 2.4.0
     */
    public function __construct() {
        $integrations = get_option('ht_form_integrations', []);
        $api_key = $integrations['insightly']['api_key'] ?? '';
        $api_url = $integrations['insightly']['api_url'] ?? '';
        $this->api_key = $api_key;
        $this->api_url = $api_url;
        $this->helper = Helper::get_instance();
    }

    /**
     * Make an API request to Insightly
     *
     * Handles communication with the Insightly API using WordPress HTTP functions.
     *
     * @since 2.4.0
     * @param string $endpoint The API endpoint to call
     * @param string $method The HTTP method to use (GET, POST, etc.)
     * @param array $data The data to send with the request
     * @return array|WP_Error Response data on success, WP_Error on failure
     */
    private function api_request($endpoint, $method = 'GET', $data = []) {
        $url = trailingslashit($this->api_url) . ltrim($endpoint, '/');
        $args = [
            'method' => $method,
            'headers' => [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->api_key . ':'),
            ],
        ];
        if (!empty($data) && $method !== 'GET') {
            $args['body'] = wp_json_encode($data);
        }
        $response = wp_remote_request($url, $args);
        if (is_wp_error($response)) {
            return $response;
        }
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code < 200 || $response_code >= 300) {
            return new WP_Error(
                'api_error',
                sprintf(
                    esc_html__('Error from Insightly API: %s', 'ht-contactform'),
                    wp_remote_retrieve_body($response)
                ),
                ['status' => $response_code]
            );
        }
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error(
                'json_error',
                esc_html__('Invalid JSON response from Insightly', 'ht-contactform')
            );
        }
        return $data;
    }

    /**
     * Verify Insightly API key
     *
     * Tests the provided API credentials by making a request to the Insightly API.
     *
     * @since 2.4.0
     * @param WP_REST_Request $request The REST request object containing API credentials
     * @return WP_REST_Response|WP_Error Success or error response
     */
    public function verify($request) {
        $api_key = $request->get_param('api_key');
        $api_url = $request->get_param('api_url');
        $this->api_key = $api_key;
        $this->api_url = $api_url;
        $response = $this->api_request('instance');
        if (is_wp_error($response)) {
            return $this->error_response(
                'api_error',
                'Error from Insightly API: ' . $response->get_error_message(),
                400
            );
        }
        return $this->success_response(
            'Insightly api key verified successfully.',
            $response,
            true
        );
    }

    /**
     * Check API key and URL
     *
     * Validates that API credentials are available before making requests.
     *
     * @since 2.4.0
     * @return WP_Error|null Error response if credentials are missing, null otherwise
     */
    private function check_api_key_url() {
        if (!$this->api_key || !$this->api_url) {
            return $this->error_response(
                'missing_api_key',
                'API Key is required for Insightly integration.',
                400
            );
        }
    }

    /**
     * Get Organizations
     *
     * Retrieves a list of organizations from Insightly API.
     *
     * @since 2.4.0
     * @return WP_REST_Response|WP_Error|array List of organizations or error
     */
    public function get_organisations() {
        $this->check_api_key_url();
        $response = $this->api_request('organizations');
        if (is_wp_error($response)) {
            return $this->error_response(
                'api_error',
                'Error from Insightly API: ' . $response->get_error_message(),
                400
            );
        }
        if(empty($response)) {
            return [];
        }
        $response = array_map(function($item) {
            return [
                'value' => (string) $item['ORGANISATION_ID'],
                'label' => $item['ORGANISATION_NAME']
            ];
        }, $response);
        return $response;
    }

    /**
     * Get Lead Statuses
     *
     * Retrieves a list of lead statuses from Insightly API.
     *
     * @since 2.4.0
     * @return WP_REST_Response|WP_Error|array List of lead statuses or error
     */
    public function get_lead_statuses() {
        $this->check_api_key_url();
        $response = $this->api_request('LeadStatuses');
        if (is_wp_error($response)) {
            return $this->error_response(
                'api_error',
                'Error from Insightly API: ' . $response->get_error_message(),
                400
            );
        }
        if(empty($response)) {
            return [];
        }
        $response = array_map(function($item) {
            return [
                'value' => (string) $item['LEAD_STATUS_ID'],
                'label' => $item['LEAD_STATUS']
            ];
        }, $response);
        return $response;
    }

    /**
     * Get Lead Sources
     *
     * Retrieves a list of lead sources from Insightly API.
     *
     * @since 2.4.0
     * @return WP_REST_Response|WP_Error|array List of lead sources or error
     */
    public function get_lead_sources() {
        $this->check_api_key_url();
        $response = $this->api_request('LeadSources');
        if (is_wp_error($response)) {
            return $this->error_response(
                'api_error',
                'Error from Insightly API: ' . $response->get_error_message(),
                400
            );
        }
        if(empty($response)) {
            return [];
        }
        $response = array_map(function($item) {
            return [
                'value' => (string) $item['LEAD_SOURCE_ID'],
                'label' => $item['LEAD_SOURCE']
            ];
        }, $response);
        return $response;
    }

    /**
     * Get Projects
     *
     * Retrieves a list of projects from Insightly API.
     *
     * @since 2.4.0
     * @return WP_REST_Response|WP_Error|array List of projects or error
     */
    public function get_projects() {
        $this->check_api_key_url();
        $response = $this->api_request('Projects');
        if (is_wp_error($response)) {
            return $this->error_response(
                'api_error',
                'Error from Insightly API: ' . $response->get_error_message(),
                400
            );
        }
        if(empty($response)) {
            return [];
        }
        $response = array_map(function($item) {
            return [
                'value' => (string) $item['PROJECT_ID'],
                'label' => $item['PROJECT_NAME']
            ];
        }, $response);
        return $response;
    }

    /**
     * Get Project Categories
     *
     * Retrieves a list of project categories from Insightly API.
     *
     * @since 2.4.0
     * @return WP_REST_Response|WP_Error|array List of project categories or error
     */
    public function get_project_categories() {
        $this->check_api_key_url();
        $response = $this->api_request('ProjectCategories');
        if (is_wp_error($response)) {
            return $this->error_response(
                'api_error',
                'Error from Insightly API: ' . $response->get_error_message(),
                400
            );
        }
        if(empty($response)) {
            return [];
        }
        $response = array_map(function($item) {
            return [
                'value' => (string) $item['CATEGORY_ID'],
                'label' => $item['CATEGORY_NAME']
            ];
        }, $response);
        return $response;
    }

    /**
     * Get Users
     *
     * Retrieves a list of users from Insightly API.
     *
     * @since 2.4.0
     * @return WP_REST_Response|WP_Error|array List of users or error
     */
    public function get_users() {
        $this->check_api_key_url();
        $response = $this->api_request('Users');
        if (is_wp_error($response)) {
            return $this->error_response(
                'api_error',
                'Error from Insightly API: ' . $response->get_error_message(),
                400
            );
        }
        if(empty($response)) {
            return [];
        }
        $response = array_map(function($item) {
            return [
                'value' => (string) $item['USER_ID'],
                'label' => $item['FIRST_NAME'] . ' ' . $item['LAST_NAME']
            ];
        }, $response);
        return $response;
    }

    /**
     * Get Pipelines
     *
     * Retrieves a list of pipelines from Insightly API.
     *
     * @since 2.4.0
     * @return WP_REST_Response|WP_Error|array List of pipelines or error
     */
    public function get_pipelines() {
        $this->check_api_key_url();
        $response = $this->api_request('Pipelines');
        if (is_wp_error($response)) {
            return $this->error_response(
                'api_error',
                'Error from Insightly API: ' . $response->get_error_message(),
                400
            );
        }
        if(empty($response)) {
            return [];
        }
        $response = array_map(function($item) {
            return [
                'value' => (string) $item['PIPELINE_ID'],
                'label' => $item['PIPELINE_NAME']
            ];
        }, $response);
        return $response;
    }

    /**
     * Get Pipeline Stages
     *
     * Retrieves a list of pipeline stages from Insightly API.
     *
     * @since 2.4.0
     * @return WP_REST_Response|WP_Error|array List of pipeline stages or error
     */
    public function get_pipeline_stages() {
        $this->check_api_key_url();
        $response = $this->api_request('PipelineStages');
        if (is_wp_error($response)) {
            return $this->error_response(
                'api_error',
                'Error from Insightly API: ' . $response->get_error_message(),
                400
            );
        }
        if(empty($response)) {
            return [];
        }
        $response = array_map(function($item) {
            return [
                'value' => (string) $item['STAGE_ID'],
                'label' => $item['STAGE_NAME']
            ];
        }, $response);
        return $response;
    }

    /**
     * Get Opportunities
     *
     * Retrieves a list of opportunities from Insightly API.
     *
     * @since 2.4.0
     * @return WP_REST_Response|WP_Error|array List of opportunities or error
     */
    public function get_opportunities() {
        $this->check_api_key_url();
        $response = $this->api_request('Opportunities');
        if (is_wp_error($response)) {
            return $this->error_response(
                'api_error',
                'Error from Insightly API: ' . $response->get_error_message(),
                400
            );
        }
        if(empty($response)) {
            return [];
        }
        $response = array_map(function($item) {
            return [
                'value' => (string) $item['OPPORTUNITY_ID'],
                'label' => $item['OPPORTUNITY_NAME']
            ];
        }, $response);
        return $response;
    }

    /**
     * Get Task Categories
     *
     * Retrieves a list of task categories from Insightly API.
     *
     * @since 2.4.0
     * @return WP_REST_Response|WP_Error|array List of task categories or error
     */
    public function get_task_categories() {
        $this->check_api_key_url();
        $response = $this->api_request('TaskCategories');
        if (is_wp_error($response)) {
            return $this->error_response(
                'api_error',
                'Error from Insightly API: ' . $response->get_error_message(),
                400
            );
        }
        if(empty($response)) {
            return [];
        }
        $response = array_map(function($item) {
            return [
                'value' => (string) $item['CATEGORY_ID'],
                'label' => $item['CATEGORY_NAME']
            ];
        }, $response);
        return $response;
    }

    //-------------------------------------------------------------------------
    // HELPER METHODS
    //-------------------------------------------------------------------------


    /**
     * Get Fields
     *
     * Retrieves the available fields for a specific Insightly service.
     *
     * @since 2.4.0
     * @param WP_REST_Request $request The REST request object containing service parameter
     * @return WP_REST_Response|WP_Error Fields data or error response
     */
    public function get_fields($request) {
        $service = $request->get_param('service');
        $config = InsightlyConfig::get_instance();
        $fields = $config->get_fields($service);
        if (empty($fields)) {
            return $this->error_response(
                'insightly_error',
                'Insightly fields not found.',
                400
            );
        }
        return $this->success_response(
            'Insightly fields retrieved successfully.',
            $fields,
            true
        );
    }

    /**
     * Subscribe to Insightly Services
     *
     * Processes form submission data and sends it to the appropriate Insightly service.
     *
     * @since 2.4.0
     * @param object $integration Integration settings
     * @param array $form Form data
     * @param array $form_data Form submission data
     * @param array $meta Meta data
     * @return WP_REST_Response|WP_Error Success or error response
     */
    public function subscribe($integration, $form, $form_data, $meta) {

        // Check if integration is enabled for this form
        if (empty($integration->enabled) || empty($integration->service)) {
            return new WP_Error('insightly_error', 'Insightly integration is not enabled for this form');
        }

        $data = $this->prepare_data($integration, $form, $form_data, $meta);
        $data = array_filter($data);
        
        if($integration->service === 'contacts') {
            $result = $this->validate_contacts($data);
            if(is_wp_error($result)) {
                return $result;
            }
        }
        if($integration->service === 'organizations') {
            $result = $this->validate_organizations($data);
            if(is_wp_error($result)) {
                return $result;
            }
        }
        if($integration->service === 'leads') {
            $result = $this->validate_leads($data);
            if(is_wp_error($result)) {
                return $result;
            }
        }
        if($integration->service === 'opportunities') {
            $result = $this->validate_opportunities($data);
            if(is_wp_error($result)) {
                return $result;
            }
        }
        if($integration->service === 'projects') {
            $result = $this->validate_projects($data);
            if(is_wp_error($result)) {
                return $result;
            }
        }
        if($integration->service === 'tasks') {
            $result = $this->validate_tasks($data);
            if(is_wp_error($result)) {
                return $result;
            }
        }


        try {
            // Sync the contact with Insightly
            $response = $this->api_request($integration->service, 'POST', $data);
            
            // Handle API errors
            if (is_wp_error($response)) {
                error_log('Insightly Error: ' . $response->get_error_message());
                do_action('ht_form/insightly_integration_result', $response, 'failed', $response->get_error_message());
                return $response;
            }
            
            // Trigger success action and return response
            do_action('ht_form/insightly_integration_result', $response, 'success', 'Insightly subscription successful');
            return new WP_REST_Response([
                'success' => true,
                'message' => 'Insightly subscription successful',
                'response' => $response,
            ], 200);
        } catch (\Exception $e) {
            error_log('Insightly Exception: ' . $e->getMessage());
            do_action('ht_form/insightly_integration_result', null, 'failed', $e->getMessage());
            return new WP_Error('insightly_exception', $e->getMessage(), ['status' => 500]);
        }
        
    }

    /**
     * Prepare Data for Insightly API
     * 
     * Formats form submission data for the Insightly API based on integration settings.
     *
     * @since 2.4.0
     * @param object $integration Integration settings
     * @param array $form Form data
     * @param array $form_data Form submission data
     * @param array $meta Meta data
     * @return array Prepared data for Insightly API
     */
    public function prepare_data($integration, $form, $form_data, $meta) {
        $data = [];

        if(!empty($integration)) {
            foreach ($integration as $key => $value) {
                if(in_array($key, ['enabled', 'name', 'service', 'type', 'id'])) {
                    continue;
                }
                $data[$key] = $this->helper->filter_vars($value, $form_data, $form);
            }
        }
        return $data;
    }

    /**
     * Validate Contacts Data
     *
     * Ensures required fields are present for contact creation.
     *
     * @since 2.4.0
     * @param array $data Contact data to validate
     * @return WP_Error|true Error if validation fails, true if successful
     */
    private function validate_contacts($data) {
        if (empty($data['FIRST_NAME'])) {
            error_log('Insightly Error: First Name field is required');
            return new WP_Error('insightly_error', 'First Name field is required', ['status' => 400]);
        }
        if (empty($data['EMAIL_ADDRESS'])) {
            error_log('Insightly Error: Email field is required');
            return new WP_Error('insightly_error', 'Email field is required', ['status' => 400]);
        }
        if (!is_email($data['EMAIL_ADDRESS'])) {
            error_log('Insightly Error: Invalid email address');
            return new WP_Error('insightly_error', 'Invalid email address', ['status' => 400]);
        }
        return true;
    }

    /**
     * Validate Organizations Data
     *
     * Ensures required fields are present for organization creation.
     *
     * @since 2.4.0
     * @param array $data Organization data to validate
     * @return WP_Error|true Error if validation fails, true if successful
     */
    private function validate_organizations($data) {
        if (empty($data['ORGANISATION_NAME'])) {
            error_log('Insightly Error: Organization Name field is required');
            return new WP_Error('insightly_error', 'Organization Name field is required', ['status' => 400]);
        }
        return true;
    }

    /**
     * Validate Leads Data
     *
     * Ensures required fields are present for lead creation.
     *
     * @since 2.4.0
     * @param array $data Lead data to validate
     * @return WP_Error|true Error if validation fails, true if successful
     */
    private function validate_leads($data) {
        if (empty($data['FIRST_NAME'])) {
            error_log('Insightly Error: First Name field is required');
            return new WP_Error('insightly_error', 'First Name field is required', ['status' => 400]);
        }
        if (empty($data['LEAD_STATUS_ID'])) {
            error_log('Insightly Error: Lead Status field is required');
            return new WP_Error('insightly_error', 'Lead Status field is required', ['status' => 400]);
        }
        return true;
    }

    /**
     * Validate Opportunities Data
     *
     * Ensures required fields are present for opportunity creation.
     *
     * @since 2.4.0
     * @param array $data Opportunity data to validate
     * @return WP_Error|true Error if validation fails, true if successful
     */
    private function validate_opportunities($data) {
        if (empty($data['OPPORTUNITY_NAME'])) {
            error_log('Insightly Error: Opportunity Name field is required');
            return new WP_Error('insightly_error', 'Opportunity Name field is required', ['status' => 400]);
        }
        return true;
    }

    /**
     * Validate Projects Data
     *
     * Ensures required fields are present for project creation.
     *
     * @since 2.4.0
     * @param array $data Project data to validate
     * @return WP_Error|true Error if validation fails, true if successful
     */
    private function validate_projects($data) {
        if (empty($data['PROJECT_NAME'])) {
            error_log('Insightly Error: Project Name field is required');
            return new WP_Error('insightly_error', 'Project Name field is required', ['status' => 400]);
        }
        if (empty($data['STATUS'])) {
            error_log('Insightly Error: Project Status field is required');
            return new WP_Error('insightly_error', 'Project Status field is required', ['status' => 400]);
        }
        return true;
    }

    /**
     * Validate Tasks Data
     *
     * Ensures required fields are present for task creation.
     *
     * @since 2.4.0
     * @param array $data Task data to validate
     * @return WP_Error|true Error if validation fails, true if successful
     */
    private function validate_tasks($data) {
        if (empty($data['TITLE'])) {
            error_log('Insightly Error: Task Title field is required');
            return new WP_Error('insightly_error', 'Task Title field is required', ['status' => 400]);
        }
        return true;
    }

    /**
     * Create a Success Response
     *
     * Formats a standardized success response for REST API endpoints.
     *
     * @since 2.4.0
     * @param string $message Success message
     * @param array $data Response data
     * @param bool $success Success flag
     * @return WP_REST_Response Formatted REST response
     */
    private function success_response($message, $data = [], $success = true) {
        $response = [
            'success' => $success,
            'message' => esc_html__($message, 'ht-contactform'),
            'data' => $data
        ];
        return new WP_REST_Response($response, 200);
    }

    /**
     * Create an Error Response
     *
     * Formats a standardized error response for REST API endpoints.
     *
     * @since 2.4.0
     * @param string $code Error code
     * @param string $message Error message
     * @param int $status HTTP status code
     * @return WP_Error Formatted error response
     */
    private function error_response($code, $message, $status = 400) {
        return new WP_Error(
            $code,
            esc_html__($message, 'ht-contactform'),
            ['status' => $status]
        );
    }
}