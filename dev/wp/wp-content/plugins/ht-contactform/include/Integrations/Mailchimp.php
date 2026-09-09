<?php
/**
 * Mailchimp Integration Class
 *
 * Handles all Mailchimp API interactions for form submissions including
 * list management, member subscriptions, and merge field handling.
 *
 * @package HTContactForm
 * @subpackage Integrations
 */

namespace HTContactForm\Integrations;

use HTContactFormAdmin\Includes\Services\Helper;
use WP_Error;
use WP_REST_Response;

/**
 * Mailchimp Integration Handler
 *
 * Provides functionality to integrate form submissions with Mailchimp
 * marketing lists using WordPress native HTTP functions.
 *
 */
class Mailchimp {
    /**
     * Mailchimp API key
     *
     * @var string|null
     */
    private $api_key = null;

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
     * Get singleton instance
     *
     * @param string|null $api_key Mailchimp API key
     * @param array|null $form Form configuration
     * @param array|null $form_data Form submission data
     * @param array|null $meta Additional metadata
     * @return self Instance of the Mailchimp class
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
     * Initializes the Mailchimp integration with provided parameters
     * and sets up necessary WordPress hooks.
     *
     * @param string|null $api_key Mailchimp API key
     * @param array|null $form Form configuration
     * @param array|null $form_data Form submission data
     * @param array|null $meta Additional metadata
     */
    public function __construct($api_key = null, $form = null, $form_data = null, $meta = null) {
        $this->api_key = is_string($api_key) ? trim($api_key) : '';
        $this->form = $form;
        $this->form_data = $form_data;
        $this->meta = $meta;
        $this->helper = Helper::get_instance();
    
        add_action('wp_ajax_ht_form_mailchimp_field_tags', [$this, 'get_field_tags']);
        add_action('wp_ajax_nopriv_ht_form_mailchimp_field_tags', [$this, 'get_field_tags']);
    }

    /**
     * Make an API request to Mailchimp
     *
     * Handles communication with the Mailchimp API using WordPress HTTP functions
     * with comprehensive error handling and response validation.
     *
     * @param string $endpoint The API endpoint to call (without leading slash)
     * @param string $method The HTTP method to use (GET, POST, PATCH, DELETE)
     * @param array $data The data to send with the request
     * @return array|WP_Error Response data on success, WP_Error on failure
     * @throws \Exception If API key is invalid or malformed
     */
    private function api_request($endpoint, $method = 'GET', $data = []) {
        // Validate API key
        if (empty($this->api_key)) {
            throw new \Exception('Mailchimp API key is required');
        }
        
        $api_parts = explode('-', $this->api_key);
        if (count($api_parts) <= 1 || empty($api_parts[1])) {
            throw new \Exception('Invalid Mailchimp API key format');
        }

        // Build request URL
        $url = trailingslashit("https://{$api_parts[1]}.api.mailchimp.com/3.0") . ltrim($endpoint, '/');
        
        // Prepare request arguments
        $args = [
            'method'    => strtoupper($method),
            'headers'   => [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => "anystring {$this->api_key}",
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
            error_log('Mailchimp API Request Error: ' . $response->get_error_message());
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
            error_log("Mailchimp API Error [{$response_code}]: {$response_body}");
            
            // Try to decode error response for better error messages
            $error_data = json_decode($response_body, true);
            $error_message = $error_data['detail'] ?? $response_body;
            
            return new WP_Error(
                'api_error',
                sprintf(
                    /* translators: %1$d: HTTP status code, %2$s: Error message */
                    esc_html__('Mailchimp API error [%1$d]: %2$s', 'ht-contactform'),
                    $response_code,
                    $error_message
                ),
                ['status' => $response_code, 'response' => $error_data]
            );
        }

        // Validate JSON response
        $decoded_data = json_decode($response_body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Mailchimp API JSON Error: ' . json_last_error_msg());
            return new WP_Error(
                'json_error',
                esc_html__('Invalid JSON response from Mailchimp API', 'ht-contactform'),
                ['response_body' => $response_body]
            );
        }

        return $decoded_data;
    }

/**
     * Get all Mailchimp lists
     *
     * Retrieves all marketing lists from the Mailchimp account
     * associated with the provided API key.
     *
     * @return array|WP_Error Array of lists on success, WP_Error on failure
     */
    public function get_lists() {
        try {
            $response = $this->api_request('lists');
            return $response['lists'] ?? [];
        } catch (\Exception $e) {
            error_log('Mailchimp Lists Error: ' . $e->getMessage());
            return new WP_Error('mailchimp_error', $e->getMessage());
        }
    }

    /**
     * Get merge fields for a specific list
     *
     * Retrieves all merge fields (custom fields) configured for
     * a specific Mailchimp list.
     *
     * @param string $list_id The Mailchimp list ID
     * @return array|WP_Error Array of merge fields on success, WP_Error on failure
     */
    public function get_merge_fields($list_id) {
        if (empty($list_id)) {
            return new WP_Error('mailchimp_error', 'List ID is required');
        }
        
        try {
            $response = $this->api_request("lists/{$list_id}/merge-fields");
            return $response['merge_fields'] ?? [];
        } catch (\Exception $e) {
            error_log('Mailchimp Merge Fields Error: ' . $e->getMessage());
            return new WP_Error('mailchimp_error', $e->getMessage());
        }
    }

    /**
     * Get available tags for a specific list
     *
     * Retrieves all tags that can be applied to members
     * of a specific Mailchimp list.
     *
     * @param string $list_id The Mailchimp list ID
     * @return array|WP_Error Array of tags on success, WP_Error on failure
     */
    public function get_search_tags($list_id) {
        if (empty($list_id)) {
            return new WP_Error('mailchimp_error', 'List ID is required');
        }
        
        try {
            $response = $this->api_request("lists/{$list_id}/tag-search");
            return $response['tags'] ?? [];
        } catch (\Exception $e) {
            error_log('Mailchimp Tags Error: ' . $e->getMessage());
            return new WP_Error('mailchimp_error', $e->getMessage());
        }
    }

    /**
     * Handle AJAX request for field tags
     *
     * Processes AJAX requests to fetch merge fields and tags
     * for a specific Mailchimp list. Used by the admin interface.
     *
     * @return void Outputs JSON response and exits
     */
    public function get_field_tags() {
        check_ajax_referer('wp_rest', 'nonce');
        
        if (!isset($_GET['list_id']) || empty($_GET['list_id'])) {
            wp_send_json_error(['message' => 'List ID is required'], 400);
            return;
        }
        
        $list_id = sanitize_text_field($_GET['list_id']);
        $fields = $this->get_merge_fields($list_id);
        $tags = $this->get_search_tags($list_id);
        
        if (is_wp_error($fields)) {
            wp_send_json_error(['message' => $fields->get_error_message()], 400);
            return;
        }
        
        if (is_wp_error($tags)) {
            wp_send_json_error(['message' => $tags->get_error_message()], 400);
            return;
        }
        
        wp_send_json_success([
            'fields' => $fields,
            'tags' => $tags
        ]);
    }

    /**
     * Get a specific list member
     *
     * Retrieves information about a specific member from a Mailchimp list
     * using their email hash.
     *
     * @param string $list_id The Mailchimp list ID
     * @param string $hash MD5 hash of the member's lowercase email address
     * @return array|WP_Error Member data on success, WP_Error on failure
     */
    public function get_list_members($list_id, $hash) {
        if (empty($list_id) || empty($hash)) {
            return new WP_Error('mailchimp_error', 'List ID and hash are required');
        }
        
        try {
            $response = $this->api_request("lists/{$list_id}/members/{$hash}");
            return $response ?? [];
        } catch (\Exception $e) {
            error_log('Mailchimp List Members Error: ' . $e->getMessage());
            return new WP_Error('mailchimp_error', $e->getMessage());
        }
    }

    /**
     * Update an existing list member
     *
     * Updates information for an existing member in a Mailchimp list.
     *
     * @param string $list_id The Mailchimp list ID
     * @param string $hash MD5 hash of the member's lowercase email address
     * @param array $data Member data to update
     * @return array|WP_Error Updated member data on success, WP_Error on failure
     */
    public function update_list_member($list_id, $hash, $data) {
        if (empty($list_id) || empty($hash) || empty($data)) {
            return new WP_Error('mailchimp_error', 'List ID, hash and data are required');
        }
        
        try {
            $response = $this->api_request("lists/{$list_id}/members/{$hash}", 'PATCH', $data);
            return $response ?? [];
        } catch (\Exception $e) {
            error_log('Mailchimp List Members Error: ' . $e->getMessage());
            return new WP_Error('mailchimp_error', $e->getMessage());
        }
    }

    /**
     * Add a new list member
     *
     * Adds a new member to a Mailchimp list with the provided data.
     *
     * @param string $list_id The Mailchimp list ID
     * @param array $data Member data for the new subscriber
     * @return array|WP_Error New member data on success, WP_Error on failure
     */
    public function add_list_member($list_id, $data) {
        if (empty($list_id) || empty($data)) {
            return new WP_Error('mailchimp_error', 'List ID and data are required');
        }
        
        try {
            $response = $this->api_request("lists/{$list_id}/members", 'POST', $data);
            return $response ?? [];
        } catch (\Exception $e) {
            error_log('Mailchimp List Members Error: ' . $e->getMessage());
            return new WP_Error('mailchimp_error', $e->getMessage());
        }
    }

    /**
     * Subscribe to Mailchimp
     * 
     * @param object $integration Integration settings
     * @param array $form Form data
     * @param array $form_data Form submission data
     * @param array $meta Meta data
     * @return WP_Error|WP_REST_Response Whether the Mailchimp request was sent successfully
     */
    public function subscribe($integration, $form, $form_data, $meta) {
        // Check if integration is enabled for this form
        if (empty($integration->enabled) || empty($integration->list_id)) {
            return new WP_Error('mailchimp_error', 'Integration is not enabled for this form');
        }

        // Prepare data to send to Mailchimp
        $data = [
            'status' => !empty($integration->double_opt_in) ? 'pending' : 'subscribed',
            'vip' => !empty($integration->vip),
            'tags' => [],
        ];
        
        // Process tags
        if (!empty($integration->tags) && is_array($integration->tags)) {
            $data['tags'] = $integration->tags;
        }
        
        // Convert tags to strings and filter empty values
        if (!empty($data['tags'])) {
            $data['tags'] = array_map('strval', array_filter($data['tags']));
        }

        // Process merge fields
        $merge_fields = [];
        if (!empty($integration->merge_fields) && is_array($integration->merge_fields)) {
            foreach ($integration->merge_fields as $key => $value) {
                if (empty($value)) {
                    continue;
                }

                if (!str_contains($value, 'input.')) {
                    $merge_fields[$key] = $this->helper->filter_vars($value, $form_data, $form);
                } elseif (str_contains($value, 'input.')) {
                    preg_match_all('/{input\.([^}]+)}/', $value, $matches, PREG_SET_ORDER);
                    foreach ($matches as $match) {
                        $field_name = $match[1];
                        if (isset($form_data[$field_name])) {
                            $merge_fields[$key] = $form_data[$field_name];
                        }
                    }
                } else {
                    $merge_fields[$key] = sanitize_text_field($value);
                }
            }
        }
        
        // Check for required email field
        if (empty($merge_fields['email_address'])) {
            error_log('Mailchimp Error: Email field is required');
            return new WP_Error('mailchimp_error', 'Email field is required');
        }

        // Set email address from merge fields if available
        if (!empty($merge_fields['email_address'])) {
            $email = sanitize_email($merge_fields['email_address']);
            if (is_email($email)) {
                $data['email_address'] = $email;
            } else {
                error_log('Mailchimp Error: Invalid email address');
                return new WP_Error('mailchimp_error', 'Invalid email address');
            }
        }
        
        $data['merge_fields'] = $this->format_merge_fields($merge_fields);

                /**
         * Initialize Mailchimp API client and process subscription
         */
        try {
            /**
             * Get MD5 hash of lowercase email for member lookup
             */
            $member_hash = md5(strtolower($data['email_address']));
            
            /**
             * Check if member exists and handle accordingly
             */
            $member = $this->get_list_members($integration->list_id, $member_hash);
            
            if (is_wp_error($member)) {
                // Member doesn't exist or other error occurred
                $error_data = $member->get_error_data('api_error');
                $status_code = $error_data['status'] ?? 0;
                $error_message = $member->get_error_message();
                
                // Check if it's a 404 (member not found) error
                if ($status_code === 404 || 
                    strpos($error_message, 'Resource Not Found') !== false || 
                    strpos($error_message, 'could not be found') !== false) {
                    // Member doesn't exist, add new member
                    $response = $this->add_list_member($integration->list_id, $data);
                } else {
                    // Other API error
                    error_log('Mailchimp Error: ' . $error_message);
                    do_action('ht_form/mailchimp_integration_result', null, 'failed', $error_message);
                    return $member; // Return the WP_Error
                }
            } else {
                // Member exists, determine update strategy
                $member_status = $member['status'] ?? '';
                $member_id = $member['id'] ?? '';
                
                if ($member_status === 'unsubscribed' && !empty($integration->resubscribe)) {
                    // Resubscribe previously unsubscribed member
                    $response = $this->update_list_member($integration->list_id, $member_hash, $data);
                } elseif (!empty($member_id)) {
                    // Update existing member
                    $response = $this->update_list_member($integration->list_id, $member_hash, $data);
                } else {
                    // Fallback: add as new member (shouldn't reach here)
                    $response = $this->add_list_member($integration->list_id, $data);
                }
            }
            
            // Handle API response errors
            if (is_wp_error($response)) {
                error_log('Mailchimp Error: ' . $response->get_error_message());
                do_action('ht_form/mailchimp_integration_result', null, 'failed', $response->get_error_message());
                return $response;
            }
            
            /**
             * Success case - validate response structure
             */
            $response_status = $response['status'] ?? '';
            if (in_array($response_status, ['subscribed', 'pending'], true)) {
                /**
                 * Trigger hooks for external tracking
                 */
                do_action('ht_form/mailchimp_integration_result', $response, 'success', 'Mailchimp subscription successful');
                return new WP_REST_Response([
                    'message' => 'Mailchimp subscription successful',
                    'response' => $response,
                ], 200);
            }
            
            /**
             * Log unexpected response
             */
            error_log('Mailchimp Unexpected Response: ' . wp_json_encode($response));
            do_action('ht_form/mailchimp_integration_result', $response, 'failed', 'Unexpected response from Mailchimp');
            return new WP_Error('mailchimp_error', 'Unexpected response from Mailchimp');
            
        } catch (\Exception $e) {
            /**
             * Log the error
             */
            error_log('Mailchimp Error: ' . $e->getMessage());
            do_action('ht_form/mailchimp_integration_result', null, 'failed', $e->getMessage());
            return new WP_Error('mailchimp_error', $e->getMessage());
        }
    }

    /**
     * Format merge fields for Mailchimp API
     *
     * Processes form data to match Mailchimp's expected merge field format.
     * Handles special fields like names and addresses, sanitizes values based
     * on their type, and skips email_address as it's handled separately.
     *
     * @param array $data The raw merge field data from form submission
     * @return array Formatted merge fields ready for Mailchimp API
     */
    public function format_merge_fields($data) {
        if (!is_array($data)) {
            return [];
        }

        $merge_fields_data = [];
        
        foreach ($data as $key => $value) {
            /**
             * Skip email_address as it's handled separately in the main data array
             */
            if ($key === 'email_address') {
                continue;
            }
            
            /**
             * Handle array values (complex fields)
             */
            if (is_array($value)) {
                /**
                 * Handle name fields (combine into a single string)
                 */
                if ($this->is_name_field($value)) {
                    $merge_fields_data[$key] = implode(' ', array_filter($value));
                } 
                /**
                 * Handle address fields (format as Mailchimp expects)
                 */
                elseif ($this->is_address_field($value)) {
                    $address = $this->get_formatted_address($value);
                    if ($address) {
                        $merge_fields_data[$key] = $address;
                    }
                } 
                /**
                 * Handle other array values
                 */
                else {
                    $merge_fields_data[$key] = array_map('sanitize_text_field', $value);
                }
            } 
            /**
             * Handle scalar values
             */
            elseif ($value !== null && $value !== '') {
                if (is_email($value)) {
                    $merge_fields_data[$key] = sanitize_email($value);
                } elseif (is_numeric($value)) {
                    /**
                     * Preserve float values when needed
                     */
                    $merge_fields_data[$key] = strpos($value, '.') !== false ? (float) $value : (int) $value;
                } else {
                    $merge_fields_data[$key] = sanitize_text_field($value);
                }
            }
        }
        
        return array_filter($merge_fields_data);
    }
    
    /**
     * Check if an array contains name field components
     *
     * Determines whether the provided data array contains name-related
     * field components that should be combined into a single string.
     *
     * @param array $data The data array to check
     * @return bool True if it contains name field components, false otherwise
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
     * Determines whether the provided data array contains address-related
     * field components that should be formatted for Mailchimp's address structure.
     *
     * @param array $data The data array to check
     * @return bool True if it contains address field components, false otherwise
     */
    private function is_address_field($data) {
        $address_fields = [
            'addr1', 'addr2', 'city', 'state', 'zip', 'country',
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
     * Converts address field data into Mailchimp's expected address format.
     * Handles both current Mailchimp format and original field naming conventions.
     * Only returns formatted address if minimum required fields are present.
     *
     * @param array $data Address field data from form submission
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
        
        /**
         * Map data to Mailchimp format - handle both current and original formats
         */
        if (!empty($data['addr1'])) {
            $address_data['addr1'] = sanitize_text_field($data['addr1']);
        } elseif (!empty($data['address_line_1'])) {
            $address_data['addr1'] = sanitize_text_field($data['address_line_1']);
        }
        
        if (!empty($data['addr2'])) {
            $address_data['addr2'] = sanitize_text_field($data['addr2']);
        } elseif (!empty($data['address_line_2'])) {
            $address_data['addr2'] = sanitize_text_field($data['address_line_2']);
        }
        
        if (!empty($data['city'])) {
            $address_data['city'] = sanitize_text_field($data['city']);
        } elseif (!empty($data['address_city'])) {
            $address_data['city'] = sanitize_text_field($data['address_city']);
        }
        
        if (!empty($data['state'])) {
            $address_data['state'] = sanitize_text_field($data['state']);
        } elseif (!empty($data['address_state'])) {
            $address_data['state'] = sanitize_text_field($data['address_state']);
        }
        
        if (!empty($data['zip'])) {
            $address_data['zip'] = sanitize_text_field($data['zip']);
        } elseif (!empty($data['address_zip'])) {
            $address_data['zip'] = sanitize_text_field($data['address_zip']);
        }
        
        if (!empty($data['country'])) {
            $address_data['country'] = sanitize_text_field($data['country']);
        } elseif (!empty($data['address_country'])) {
            $address_data['country'] = sanitize_text_field($data['address_country']);
        }
        
        /**
         * Only include if we have the minimum required fields
         */
        if (!empty($address_data['addr1']) && !empty($address_data['city'])) {
            return array_filter($address_data);
        }
        return false;
    }
}
