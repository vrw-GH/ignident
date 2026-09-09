<?php
/**
 * OnepageCRM Integration Class
 *
 * Handles all OnepageCRM API interactions for form submissions including
 * contact creation, deal management, and tag application.
 *
 * @package HTContactForm
 * @subpackage Integrations
 */

namespace HTContactForm\Integrations;

use HTContactFormAdmin\Includes\Services\Helper;
use WP_Error;
use WP_REST_Response;

/**
 * OnepageCRM Integration Handler
 *
 * Provides functionality to integrate form submissions with OnepageCRM
 * CRM using WordPress native HTTP functions.
 */
class OnepageCRM {
    /**
     * OnepageCRM API key
     *
     * @var string|null
     */
    private $api_key = null;

    /**
     * OnepageCRM User ID
     *
     * @var string|null
     */
    private $user_id = null;

    /**
     * Form configuration data
     *
     * @var array|null
     */
    private $form = null;

    /**
     * Form submission data
     *
     * @var array|null
     */
    private $form_data = null;

    /**
     * Additional metadata
     *
     * @var array|null
     */
    private $meta = null;

    /**
     * Helper class instance
     *
     * @var Helper|null
     */
    private $helper = null;

    /**
     * Singleton instance
     *
     * @var self|null
     */
    private static $instance = null;

    /**
     * API base URL
     *
     * @var string
     */
    private $api_base = 'https://app.onepagecrm.com/api/v3';

    /**
     * Get instance (removed singleton pattern as this class needs unique instances per form)
     *
     * @deprecated Use new OnepageCRM() directly
     * @param string|null $api_key OnepageCRM API key
     * @param string|null $user_id OnepageCRM User ID
     * @param array|null $form Form configuration
     * @param array|null $form_data Form submission data
     * @param array|null $meta Additional metadata
     * @return self Instance of the OnepageCRM class
     */
    public static function get_instance($api_key = null, $user_id = null, $form = null, $form_data = null, $meta = null) {
        // Always create new instance - singleton pattern was inappropriate for this use case
        return new self($api_key, $user_id, $form, $form_data, $meta);
    }

    /**
     * Constructor
     *
     * Initializes the OnepageCRM integration with provided parameters.
     *
     * @param string|null $api_key OnepageCRM API key
     * @param string|null $user_id OnepageCRM User ID
     * @param array|null $form Form configuration
     * @param array|null $form_data Form submission data
     * @param array|null $meta Additional metadata
     */
    public function __construct($api_key = null, $user_id = null, $form = null, $form_data = null, $meta = null) {
        $this->api_key = is_string($api_key) ? trim($api_key) : '';
        $this->user_id = is_string($user_id) ? trim($user_id) : '';
        $this->form = $form;
        $this->form_data = $form_data;
        $this->meta = $meta;

        // Check if Helper class exists before instantiating
        if (!class_exists('HTContactFormAdmin\Includes\Services\Helper')) {
            throw new \Exception('Helper class not found. Please ensure the plugin is properly installed.');
        }

        try {
            $this->helper = Helper::get_instance();
        } catch (\Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('OnepageCRM: Failed to initialize Helper class - ' . $e->getMessage());
            }
            throw new \Exception('Failed to initialize OnepageCRM integration');
        }
    }

    /**
     * Make an API request to OnepageCRM
     *
     * Handles communication with the OnepageCRM API using WordPress HTTP functions
     * with comprehensive error handling and response validation.
     *
     * @param string $endpoint The API endpoint to call (without leading slash)
     * @param string $method The HTTP method to use (GET, POST, PUT, DELETE)
     * @param array $data The data to send with the request
     * @return array|WP_Error Response data on success, WP_Error on failure
     * @throws \Exception If API credentials are missing
     */
    private function api_request($endpoint, $method = 'GET', $data = []) {
        // Validate credentials
        if (empty($this->api_key) || empty($this->user_id)) {
            throw new \Exception('OnepageCRM API key and User ID are required');
        }

        // Build request URL
        $url = trailingslashit($this->api_base) . ltrim($endpoint, '/');

        // Prepare authentication
        $auth = base64_encode("{$this->user_id}:{$this->api_key}");

        // Prepare request arguments
        $args = [
            'method'    => strtoupper($method),
            'timeout'   => 30,
            'headers'   => [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => "Basic {$auth}",
                'User-Agent'    => 'HT-ContactForm/' . HTCONTACTFORM_VERSION,
            ],
        ];

        // Add request body for non-GET requests
        if (!empty($data) && $method !== 'GET') {
            $args['body'] = wp_json_encode($data);
        }

        // Make the request
        $response = wp_remote_request($url, $args);

        // Handle WordPress HTTP errors
        if (is_wp_error($response)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('OnepageCRM API Request Error: ' . $response->get_error_message());
            }
            return new WP_Error(
                'http_request_failed',
                sprintf(
                    /* translators: %s: Error message */
                    esc_html__('HTTP request failed: %s', 'ht-contactform'),
                    $response->get_error_message()
                )
            );
        }

        // Get response details
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        // Handle HTTP error status codes
        if ($response_code < 200 || $response_code >= 300) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("OnepageCRM API Error [{$response_code}]: API returned error response");
            }

            // Try to decode error response for better error messages
            $error_data = json_decode($response_body, true);
            $error_message = $error_data['message'] ?? $response_body;

            return new WP_Error(
                'api_error',
                sprintf(
                    /* translators: %1$d: HTTP status code, %2$s: Error message */
                    esc_html__('OnepageCRM API error [%1$d]: %2$s', 'ht-contactform'),
                    $response_code,
                    $error_message
                ),
                ['status' => $response_code, 'response' => $error_data]
            );
        }

        // Validate JSON response
        $decoded_data = json_decode($response_body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('OnepageCRM API JSON Error: ' . json_last_error_msg());
            }
            return new WP_Error(
                'json_error',
                esc_html__('Invalid JSON response from OnepageCRM API', 'ht-contactform'),
                ['response_body' => $response_body]
            );
        }

        return $decoded_data;
    }

    /**
     * Create contact in OnepageCRM
     *
     * @param object $integration Integration settings
     * @param array $form Form data
     * @param array $form_data Form submission data
     * @param array $meta Meta data
     * @return WP_Error|WP_REST_Response Contact creation result
     */
    public function create_contact($integration, $form, $form_data, $meta) {
        // Check if integration is enabled
        if (empty($integration->enabled)) {
            return new WP_Error('onepagecrm_error', 'Integration is not enabled for this form');
        }

        // Prepare contact data
        $contact_data = $this->prepare_contact_data($integration, $form, $form_data);

        // Check for required email field
        if (empty($contact_data['emails'])) {
            return new WP_Error('onepagecrm_error', 'Email field is required');
        }

        try {
            // Create contact
            $response = $this->api_request('contacts.json', 'POST', $contact_data);

            if (is_wp_error($response)) {
                do_action('ht_form/onepagecrm_integration_result', null, 'failed', $response->get_error_message());
                return $response;
            }

            $contact_id = $response['data']['contact']['id'] ?? null;

            // Create deal if enabled
            if (!empty($integration->create_deal) && !empty($contact_id)) {
                $this->create_deal($integration, $form, $form_data, $contact_id);
            }

            // Add note if provided
            if (!empty($integration->note) && !empty($contact_id)) {
                // Create a temporary integration object for note
                $note_integration = (object) [
                    'note_details' => $integration->note,
                    'contact_name' => null // Use $contact_id parameter instead
                ];
                $this->add_note($note_integration, $form, $form_data, $contact_id);
            }

            // Trigger success action
            do_action('ht_form/onepagecrm_integration_result', $response, 'success', 'OnepageCRM contact created successfully');

            return new WP_REST_Response([
                'message' => 'OnepageCRM contact created successfully',
                'response' => $response,
            ], 200);

        } catch (\Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('OnepageCRM Error: ' . $e->getMessage());
            }
            do_action('ht_form/onepagecrm_integration_result', null, 'failed', $e->getMessage());
            return new WP_Error('onepagecrm_error', $e->getMessage());
        }
    }

    /**
     * Prepare contact data for OnepageCRM API
     *
     * @param object $integration Integration settings
     * @param array $form Form data
     * @param array $form_data Form submission data
     * @return array Formatted contact data
     */
    private function prepare_contact_data($integration, $form, $form_data) {
        $data = [];

        // Helper function to process field value
        $process_field = function($field_name) use ($integration, $form_data, $form) {
            if (empty($integration->$field_name)) {
                return '';
            }
            return $this->helper->filter_vars($integration->$field_name, $form_data, $form);
        };

        // Process basic fields first
        $first_name = $process_field('first_name');
        $last_name = $process_field('last_name');
        $company_name = $process_field('company_name');
        $title = $process_field('title');
        $job_title = $process_field('job_title');
        $background = $process_field('background');
        $lead_source = $process_field('lead_source');

        // OnepageCRM requires either last_name OR company_name to be provided
        // Set them with fallback logic
        if (!empty($first_name)) {
            $data['first_name'] = sanitize_text_field($first_name);
        }

        if (!empty($last_name)) {
            $data['last_name'] = sanitize_text_field($last_name);
        } elseif (!empty($company_name)) {
            // Company name exists, so last_name is optional
            // Don't set last_name, just let company_name be set below
        } else {
            // Neither last_name nor company_name provided
            // Set last_name to first_name or 'Unknown'
            if (!empty($first_name)) {
                $data['last_name'] = sanitize_text_field($first_name);
            } else {
                $data['last_name'] = 'Unknown';
            }
        }

        if (!empty($company_name)) {
            $data['company_name'] = sanitize_text_field($company_name);
        }

        if (!empty($title)) {
            // OnepageCRM expects title with capital first letter: Mr, Mrs, Ms
            $data['title'] = ucfirst(strtolower(sanitize_text_field($title)));
        }

        if (!empty($job_title)) {
            $data['job_title'] = sanitize_text_field($job_title);
        }

        if (!empty($background)) {
            $data['background'] = sanitize_textarea_field($background);

            // Add length validation to prevent API issues
            if (strlen($data['background']) > 5000) {
                $data['background'] = substr($data['background'], 0, 5000);
            }
        }

        // Starred field (boolean)
        if (!empty($integration->starred)) {
            $data['starred'] = $integration->starred === 'yes';
        }

        // Email field
        $email = $process_field('email');
        if (!empty($email) && is_email($email)) {
            $email_type = !empty($integration->email_type) ? $integration->email_type : 'work';
            $data['emails'] = [
                [
                    'type' => sanitize_text_field($email_type),
                    'value' => sanitize_email($email)
                ]
            ];
        }

        // Phone field
        $phone = $process_field('phone');
        if (!empty($phone)) {
            $phone_type = !empty($integration->phone_type) ? $integration->phone_type : 'work';
            $data['phones'] = [
                [
                    'type' => sanitize_text_field($phone_type),
                    'value' => sanitize_text_field($phone)
                ]
            ];
        }

        // URL field
        $url = $process_field('url');
        if (!empty($url)) {
            $url_type = !empty($integration->url_type) ? $integration->url_type : 'website';
            $data['urls'] = [
                [
                    'type' => sanitize_text_field($url_type),
                    'value' => esc_url($url)
                ]
            ];
        }

        // Address fields
        $address_parts = [];
        $address_fields = ['address', 'city', 'state', 'zip_code', 'country_code'];
        foreach ($address_fields as $field) {
            $value = $process_field($field);
            if (!empty($value)) {
                $address_parts[$field] = sanitize_text_field($value);
            }
        }

        // Format address
        if (!empty($address_parts)) {
            $address_type = $process_field('address_type');
            if (empty($address_type)) {
                $address_type = 'work';
            }

            $data['addresses'] = [
                [
                    'type' => sanitize_text_field($address_type),
                    'address' => $address_parts['address'] ?? '',
                    'city' => $address_parts['city'] ?? '',
                    'state' => $address_parts['state'] ?? '',
                    'zip_code' => $address_parts['zip_code'] ?? '',
                    'country_code' => $address_parts['country_code'] ?? ''
                ]
            ];
        }

        // Status field
        $status = $process_field('status');
        if (!empty($status)) {
            $data['status_id'] = sanitize_text_field($status);
        }

        // Birthday field
        $birthday = $process_field('birthday');
        if (!empty($birthday)) {
            $data['birthday'] = sanitize_text_field($birthday);
        }

        return $data;
    }

    /**
     * Create deal in OnepageCRM
     *
     * @param object $integration Integration settings
     * @param array $form Form data
     * @param array $form_data Form submission data
     * @param string $contact_id Contact ID to link deal to (optional, will use integration->contact_name if available)
     * @return array|WP_Error Deal creation result
     */
    public function create_deal($integration, $form, $form_data, $contact_id = null) {
        // Priority 1: Use contact_name from integration if set (for Deal service)
        // Priority 2: Use contact_id parameter (for creating deal after contact creation)
        if (!empty($integration->contact_name)) {
            $contact_id = $this->helper->filter_vars($integration->contact_name, $form_data, $form);
        }

        if (empty($contact_id)) {
            return new WP_Error('onepagecrm_error', 'Contact ID is required to create a deal');
        }

        // Validate required deal_name field
        if (empty($integration->deal_name)) {
            return new WP_Error('onepagecrm_error', 'Deal name is required to create a deal');
        }

        $deal_data = [
            'name' => sanitize_text_field($this->helper->filter_vars($integration->deal_name, $form_data, $form)),
            'amount' => 0,
            'status' => 'pending',
            'contact_id' => sanitize_text_field($contact_id)
        ];

        // Validate that deal_name is not empty after processing
        if (empty($deal_data['name'])) {
            return new WP_Error('onepagecrm_error', 'Deal name cannot be empty');
        }

        // Process optional deal fields

        if (!empty($integration->deal_amount)) {
            $amount = $this->helper->filter_vars($integration->deal_amount, $form_data, $form);
            $deal_data['amount'] = floatval($amount);
        }

        if (!empty($integration->deal_status)) {
            $deal_data['status'] = sanitize_text_field($this->helper->filter_vars($integration->deal_status, $form_data, $form));
        }

        if (!empty($integration->pipeline_id)) {
            $deal_data['pipeline_id'] = sanitize_text_field($this->helper->filter_vars($integration->pipeline_id, $form_data, $form));
        }

        if (!empty($integration->stage_id)) {
            $deal_data['stage_id'] = sanitize_text_field($this->helper->filter_vars($integration->stage_id, $form_data, $form));
        }

        if (!empty($integration->expected_close_date)) {
            $deal_data['expected_close_date'] = sanitize_text_field($this->helper->filter_vars($integration->expected_close_date, $form_data, $form));
        }

        try {
            $response = $this->api_request('deals.json', 'POST', $deal_data);
            return $response;
        } catch (\Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('OnepageCRM Deal Error: ' . $e->getMessage());
            }
            return new WP_Error('onepagecrm_error', $e->getMessage());
        }
    }

    /**
     * Add note to contact
     *
     * @param object $integration Integration settings
     * @param array $form Form data
     * @param array $form_data Form submission data
     * @param string $contact_id Contact ID to link deal to (optional, will use integration->contact_name if available)
     * @return array|WP_Error Note creation result
     */
    public function add_note($integration, $form, $form_data, $contact_id = null) {
        // Priority 1: Use contact_name from integration if set (for Note service)
        // Priority 2: Use contact_id parameter (for creating note after contact creation)
        if (!empty($integration->contact_name)) {
            $contact_id = $this->helper->filter_vars($integration->contact_name, $form_data, $form);
        }

        if (empty($contact_id)) {
            return new WP_Error('onepagecrm_error', 'Contact ID is required to create a note');
        }
        $note_data = [
            'contact_id' => sanitize_text_field($contact_id),
        ];

        if (!empty($integration->note_details)) {
            $note_data['text'] = $this->helper->filter_vars($integration->note_details, $form_data, $form);
        }
        if (!empty($integration->note_date)) {
            $note_data['date'] = $this->helper->filter_vars($integration->note_date, $form_data, $form);
        }

        try {
            $response = $this->api_request('notes.json', 'POST', $note_data);
            return $response;
        } catch (\Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('OnepageCRM Note Error: ' . $e->getMessage());
            }
            return new WP_Error('onepagecrm_error', $e->getMessage());
        }
    }

    /**
     * Create action for contact
     *
     * @param object $integration Integration settings
     * @param array $form Form data
     * @param array $form_data Form submission data
     * @param string $contact_id Contact ID to link action to (optional, will use integration->contact_name if available)
     * @return array|WP_Error Action creation result
     */
    public function create_action($integration, $form, $form_data, $contact_id = null) {
        // Priority 1: Use contact_name from integration if set
        // Priority 2: Use contact_id parameter
        if (!empty($integration->contact_name)) {
            $contact_id = $this->helper->filter_vars($integration->contact_name, $form_data, $form);
        }

        if (empty($contact_id)) {
            return new WP_Error('onepagecrm_error', 'Contact ID is required to create an action');
        }

        $action_data = [
            'contact_id' => sanitize_text_field($contact_id),
        ];

        // Action status (required)
        if (!empty($integration->action_status)) {
            $action_data['status'] = sanitize_text_field($integration->action_status);
        } else {
            $action_data['status'] = 'asap'; // Default status
        }

        // Action details/text
        if (!empty($integration->action_details)) {
            $action_data['text'] = $this->helper->filter_vars($integration->action_details, $form_data, $form);
        }

        // Action date (for date-based actions)
        if (!empty($integration->action_date)) {
            $action_data['date'] = $this->helper->filter_vars($integration->action_date, $form_data, $form);
        }

        try {
            $response = $this->api_request('actions.json', 'POST', $action_data);
            return $response;
        } catch (\Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('OnepageCRM Action Error: ' . $e->getMessage());
            }
            return new WP_Error('onepagecrm_error', $e->getMessage());
        }
    }

}
