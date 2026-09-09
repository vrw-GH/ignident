<?php
/**
 * OnepageCRM API Endpoint Class
 *
 * Handles all REST API endpoints for OnepageCRM integration including
 * credential verification and data fetching for admin configuration.
 *
 * @package HTContactFormAdmin
 * @subpackage Api\Endpoints\Integrations
 */

namespace HTContactFormAdmin\Includes\Api\Endpoints\Integrations;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * OnepageCRM API Handler
 *
 * Provides REST API endpoints for OnepageCRM integration configuration
 */
class OnepageCRM {
    /**
     * REST API namespace
     *
     * @var string
     */
    private $namespace = 'ht-form/v1';

    /**
     * Singleton instance
     *
     * @var self|null
     */
    private static $instance = null;

    /**
     * API Key
     *
     * @var string
     */
    private $api_key;

    /**
     * User ID
     *
     * @var string
     */
    private $user_id;

    /**
     * API base URL
     *
     * @var string
     */
    private $api_base = 'https://app.onepagecrm.com/api/v3';

    /**
     * Get singleton instance
     *
     * @return self
     */
    public static function get_instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     *
     * Initializes the class and sets up WordPress hooks
     */
    public function __construct() {
        $integrations = get_option('ht_form_integrations', []);
        $this->api_key = $integrations['onepagecrm']['api_key'] ?? '';
        $this->user_id = $integrations['onepagecrm']['user_id'] ?? '';

        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes
     *
     * Sets up all the REST API endpoints for OnepageCRM integration
     */
    public function register_routes() {
        $routes = [
            // Verify credentials
            [
                'endpoint' => 'onepagecrm/verify',
                'methods'  => 'POST',
                'callback' => 'verify_credentials',
            ],
            // Get services
            [
                'endpoint' => 'onepagecrm/services',
                'methods'  => 'GET',
                'callback' => 'get_services',
            ],
            // Get fields for a specific service
            [
                'endpoint' => 'onepagecrm/get_fields',
                'methods'  => 'GET',
                'callback' => 'get_fields',
                'args'     => [
                    'service' => [
                        'required' => true,
                        'type'     => 'string',
                    ],
                ],
            ],
            // Get contacts
            [
                'endpoint' => 'onepagecrm/contacts',
                'methods'  => 'GET',
                'callback' => 'get_contacts',
            ],
            // Get pipelines
            [
                'endpoint' => 'onepagecrm/pipelines',
                'methods'  => 'GET',
                'callback' => 'get_pipelines',
            ],
            // Get statuses
            [
                'endpoint' => 'onepagecrm/statuses',
                'methods'  => 'GET',
                'callback' => 'get_statuses',
            ],
            // Get custom fields
            [
                'endpoint' => 'onepagecrm/custom-fields',
                'methods'  => 'GET',
                'callback' => 'get_custom_fields',
            ],
            // Get tags
            [
                'endpoint' => 'onepagecrm/tags',
                'methods'  => 'GET',
                'callback' => 'get_tags',
            ],
            // Get users
            [
                'endpoint' => 'onepagecrm/users',
                'methods'  => 'GET',
                'callback' => 'get_users',
            ],
            // Get lead sources
            [
                'endpoint' => 'onepagecrm/lead-sources',
                'methods'  => 'GET',
                'callback' => 'get_lead_sources',
            ],
        ];

        foreach ($routes as $route) {
            register_rest_route($this->namespace, $route['endpoint'], [
                'methods'             => $route['methods'],
                'callback'            => [$this, $route['callback']],
                'permission_callback' => [$this, 'check_permission'],
                'args'                => $route['args'] ?? []
            ]);
        }
    }

    /**
     * Check if current user has permission to access endpoints
     *
     * @return bool Whether user has manage_options capability
     */
    public function check_permission() {
        return current_user_can('manage_options');
    }

    /**
     * Make API request to OnepageCRM
     *
     * @param string $endpoint API endpoint
     * @param string $method HTTP method
     * @param array $data Request data
     * @param string $api_key Optional API key
     * @param string $user_id Optional User ID
     * @return array|WP_Error Response data or error
     */
    private function make_api_request($endpoint, $method = 'GET', $data = [], $api_key = null, $user_id = null) {
        $api_key = $api_key ?? $this->api_key;
        $user_id = $user_id ?? $this->user_id;

        if (empty($api_key) || empty($user_id)) {
            return new WP_Error(
                'onepagecrm_credentials_missing',
                'OnepageCRM API key and User ID are required.',
                ['status' => 400]
            );
        }

        // Build request URL
        $url = trailingslashit($this->api_base) . ltrim($endpoint, '/');

        // Prepare authentication
        $auth = base64_encode("{$user_id}:{$api_key}");

        // Prepare request arguments
        $args = [
            'method'  => strtoupper($method),
            'timeout' => 30,
            'headers' => [
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
            return new WP_Error(
                'http_request_failed',
                sprintf(
                    /* translators: %s: error message */
                    __('Failed to connect to OnepageCRM: %s', 'ht-contactform'),
                    $response->get_error_message()
                ),
                ['status' => 500]
            );
        }

        // Get response details
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        // Handle error responses
        if ($response_code < 200 || $response_code >= 300) {
            $error_message = $data['message'] ?? __('Unknown error occurred.', 'ht-contactform');

            return new WP_Error(
                'onepagecrm_api_error',
                $error_message,
                ['status' => $response_code]
            );
        }

        return $data;
    }

    /**
     * Verify OnepageCRM credentials
     *
     * @param string|WP_REST_Request $api_key_or_request API key or REST request object
     * @param string|null $user_id User ID (when called directly)
     * @return WP_REST_Response|WP_Error Response with verification result
     */
    public function verify_credentials($api_key_or_request, $user_id = null) {
        // Handle both REST API calls and direct method calls
        if ($api_key_or_request instanceof WP_REST_Request) {
            $api_key = sanitize_text_field($api_key_or_request->get_param('api_key'));
            $user_id = sanitize_text_field($api_key_or_request->get_param('user_id'));
        } else {
            $api_key = sanitize_text_field($api_key_or_request);
            $user_id = sanitize_text_field($user_id);
        }

        if (empty($api_key) || empty($user_id)) {
            return new WP_Error(
                'onepagecrm_credentials_missing',
                'API key and User ID are required.',
                ['status' => 400]
            );
        }

        // Test authentication with a simple API call
        $response = $this->make_api_request('contacts.json?per_page=1', 'GET', [], $api_key, $user_id);

        if (is_wp_error($response)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => $response->get_error_message()
            ], $response->get_error_data()['status'] ?? 400);
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => __('OnepageCRM credentials verified successfully.', 'ht-contactform'),
        ], 200);
    }

    /**
     * Get available OnepageCRM services
     *
     * @return WP_REST_Response Response with services list
     */
    public function get_services() {
        // OnepageCRM services are predefined
        $services = [
            [
                'value' => 'contact',
                'label' => __('Contact', 'ht-contactform'),
            ],
            [
                'value' => 'deal',
                'label' => __('Deal', 'ht-contactform'),
            ],
            [
                'value' => 'note',
                'label' => __('Note', 'ht-contactform'),
            ],
            [
                'value' => 'action',
                'label' => __('Action', 'ht-contactform'),
            ],
        ];

        return rest_ensure_response($services);
    }

    /**
     * Get fields for a specific service
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with field list
     */
    public function get_fields($request) {
        $service = $request->get_param('service');
        $config = \HTContactFormAdmin\Includes\Config\Editor\Integrations\OnepageCRM::get_instance();

        $fields = [];

        switch ($service) {
            case 'contact':
                $fields = $config->get_contact_service_fields();
                break;
            case 'deal':
                $fields = $config->get_deal_service_fields();
                break;
            case 'note':
                $fields = $config->get_note_service_fields();
                break;
            case 'action':
                $fields = $config->get_action_service_fields();
                break;
            default:
                return new WP_Error(
                    'invalid_service',
                    __('Invalid service type.', 'ht-contactform'),
                    ['status' => 400]
                );
        }

        return rest_ensure_response(['data' => $fields]);
    }

    /**
     * Get OnepageCRM contacts
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with contacts list
     */
    public function get_contacts() {
        $response = $this->make_api_request('contacts.json?per_page=100');

        if (is_wp_error($response)) {
            return $response;
        }

        $contacts = [];
        if (!empty($response['data']['contacts'])) {
            foreach ($response['data']['contacts'] as $contact) {
                $name = trim(($contact['contact']['first_name'] ?? '') . ' ' . ($contact['contact']['last_name'] ?? ''));
                if (empty($name)) {
                    $name = $contact['contact']['company_name'] ?? 'Unknown';
                }

                $contacts[] = [
                    'value' => (string)$contact['contact']['id'],
                    'label' => $name
                ];
            }
        }

        return rest_ensure_response($contacts);
    }

    /**
     * Get OnepageCRM pipelines
     *
     * @return WP_REST_Response|WP_Error Response with pipelines list
     */
    public function get_pipelines() {
        $response = $this->make_api_request('pipelines.json');

        if (is_wp_error($response)) {
            return $response;
        }

        $pipelines = [];
        if (!empty($response['data'])) {
            foreach ($response['data'] as $pipeline) {
                $pipelines[] = [
                    'value' => (string)$pipeline['id'],
                    'label' => $pipeline['name']
                ];
            }
        }

        return rest_ensure_response($pipelines);
    }

    /**
     * Get OnepageCRM contact statuses
     *
     * @return WP_REST_Response|WP_Error Response with statuses list
     */
    public function get_statuses() {
        $response = $this->make_api_request('statuses.json');

        if (is_wp_error($response)) {
            return $response;
        }

        $statuses = [];
        if (!empty($response['data'])) {
            foreach ($response['data'] as $status) {
                $statuses[] = [
                    'value' => (string)$status['status']['id'],
                    'label' => $status['status']['text']
                ];
            }
        }

        return rest_ensure_response($statuses);
    }

    /**
     * Get OnepageCRM custom fields
     *
     * @return WP_REST_Response|WP_Error Response with custom fields list
     */
    public function get_custom_fields() {
        $response = $this->make_api_request('custom_fields.json');

        if (is_wp_error($response)) {
            return $response;
        }

        $custom_fields = [];
        if (!empty($response['data'])) {
            foreach ($response['data'] as $field) {
                $custom_fields[] = [
                    'value' => (string)$field['id'],
                    'label' => $field['name'],
                    'type' => $field['type'] ?? 'text'
                ];
            }
        }

        return rest_ensure_response($custom_fields);
    }

    /**
     * Get OnepageCRM tags
     *
     * @return WP_REST_Response|WP_Error Response with tags list
     */
    public function get_tags() {
        $response = $this->make_api_request('tags.json');

        if (is_wp_error($response)) {
            return $response;
        }

        $tags = [];
        if (!empty($response['data'])) {
            foreach ($response['data'] as $tag) {
                $tags[] = [
                    'value' => $tag['name'],
                    'label' => $tag['name']
                ];
            }
        }

        return rest_ensure_response($tags);
    }

    /**
     * Get OnepageCRM users
     *
     * @return WP_REST_Response|WP_Error Response with users list
     */
    public function get_users() {
        $response = $this->make_api_request('users.json');

        if (is_wp_error($response)) {
            return $response;
        }

        $users = [];
        if (!empty($response['data'])) {
            foreach ($response['data'] as $user) {
                $users[] = [
                    'value' => (string)$user['id'],
                    'label' => $user['first_name'] . ' ' . $user['last_name']
                ];
            }
        }

        return rest_ensure_response($users);
    }

    /**
     * Get OnepageCRM lead sources
     *
     * @return WP_REST_Response|WP_Error Response with lead sources list
     */
    public function get_lead_sources() {
        $response = $this->make_api_request('lead_sources.json');

        if (is_wp_error($response)) {
            return $response;
        }

        $lead_sources = [];
        if (!empty($response['data'])) {
            foreach ($response['data'] as $source) {
                $lead_sources[] = [
                    'value' => $source['text'],
                    'label' => $source['text']
                ];
            }
        }

        return rest_ensure_response($lead_sources);
    }
}
