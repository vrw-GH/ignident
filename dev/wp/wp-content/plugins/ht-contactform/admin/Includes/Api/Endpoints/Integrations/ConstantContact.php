<?php

namespace HTContactFormAdmin\Includes\Api\Endpoints\Integrations;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Trait for managing plugin options with prefixed names
 */
trait ConstantContactOptionTrait {
    /** @var array Option names for storing tokens and state */
    private static $option_names = [
        'access_token' => 'ht_contactform_cc_access_token',
        'refresh_token' => 'ht_contactform_cc_refresh_token',
        'token_expires' => 'ht_contactform_cc_token_expires',
        'oauth_state' => 'ht_contactform_cc_oauth_state',
        'oauth_state_time' => 'ht_contactform_cc_oauth_state_time',
        'connection_successful' => 'ht_contactform_cc_connection_successful'
    ];

    private function get_option($key, $default = false) {
        if (!isset(self::$option_names[$key])) {
            return $default;
        }
        return get_option(self::$option_names[$key], $default);
    }

    private function update_option($key, $value) {
        if (!isset(self::$option_names[$key])) {
            return false;
        }
        return update_option(self::$option_names[$key], $value);
    }

    private function delete_option($key) {
        if (!isset(self::$option_names[$key])) {
            return false;
        }
        return delete_option(self::$option_names[$key]);
    }
}

/**
 * Constant Contact API Handler Class
 *
 * Handles all REST API endpoints for Constant Contact integration including groups and merge fields.
 */
class ConstantContact {
    use ConstantContactOptionTrait;

    //-------------------------------------------------------------------------
    // CONSTANTS
    //-------------------------------------------------------------------------
    private const NAMESPACE = 'ht-form/v1';
    private const TOKEN_ENDPOINT = 'https://authz.constantcontact.com/oauth2/default/v1/token';
    private const AUTH_ENDPOINT = 'https://authz.constantcontact.com/oauth2/default/v1/authorize';
    private const API_BASE_URL = 'https://api.cc.email/v3';

    //-------------------------------------------------------------------------
    // PROPERTIES
    //-------------------------------------------------------------------------
    /** @var string Client ID */
    private $client_id;
    /** @var string Client Secret */
    private $client_secret;

    //-------------------------------------------------------------------------
    // INITIALIZATION
    //-------------------------------------------------------------------------
    /**
     * Constructor
     *
     * @param string $client_id Constant Contact client ID
     * @param string $client_secret Constant Contact client secret
     */
    public function __construct($client_id, $client_secret) {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes
     */
    public function register_routes() {
        $routes = [
            [
                'endpoint' => 'constantcontact/fields',
                'methods'  => 'GET',
                'callback' => 'get_contact_custom_fields',
            ],
            [
                'endpoint' => 'constantcontact/lists',
                'methods'  => 'GET',
                'callback' => 'get_contact_lists',
            ],
            [
                'endpoint' => 'constantcontact/tags',
                'methods'  => 'GET',
                'callback' => 'get_contact_tags',
            ],
            [
                'endpoint' => 'constantcontact/verify',
                'methods'  => 'GET',
                'callback' => 'verify',
            ],
            [
                'endpoint' => 'constantcontact/callback',
                'methods'  => 'GET',
                'callback' => 'handle_oauth_callback',
                'permission_callback' => '__return_true',
            ],
        ];
        foreach ($routes as $route) {
            register_rest_route(self::NAMESPACE, $route['endpoint'], [
                'methods'             => $route['methods'],
                'callback'            => [$this, $route['callback']],
                'permission_callback' => $route['permission_callback'] ?? [$this, 'check_permission'],
                'args'                => $route['args'] ?? []
            ]);
        }
    }

    //-------------------------------------------------------------------------
    // VALIDATION & PERMISSIONS
    //-------------------------------------------------------------------------
    /**
     * Check if current user has permission to access endpoints
     * @return bool
     */
    public function check_permission() {
        return current_user_can('manage_options');
    }

    //-------------------------------------------------------------------------
    // OAUTH AUTHENTICATION
    //-------------------------------------------------------------------------
    /**
     * Verify Constant Contact API key
     * @return WP_REST_Response|WP_Error
     */
    public function verify() {
        if (empty($this->client_id) || empty($this->client_secret)) {
            return $this->error_response(
                'missing_api_key',
                'Client ID and Client Secret are required for Constant Contact integration.',
                400
            );
        }
        if ($this->is_connected()) {
            return $this->success_response(
                'Constant Contact is already connected.',
                ['connected' => true]
            );
        }
        $auth_url = $this->get_authorization_url();
        return $this->success_response(
            'Authorization required. Please complete OAuth flow.',
            [
                'connected' => false,
                'auth_url' => $auth_url
            ],
            false
        );
    }

    /**
     * Generate authorization URL for OAuth flow
     * @return string
     */
    private function get_authorization_url() {
        $state = wp_generate_password(12, false);
        $this->update_option('oauth_state', $state);
        $this->update_option('oauth_state_time', time());
        // Get the raw callback URL without any automatic encoding
        $callback_url = rest_url(self::NAMESPACE . '/constantcontact/callback');
        // Remove any trailing slashes that might be added by WordPress
        $callback_url = untrailingslashit($callback_url);
        $auth_url = add_query_arg([
            'response_type' => 'code',
            'client_id' => $this->client_id,
            'redirect_uri' => $callback_url,
            'scope' => 'contact_data offline_access',
            'state' => $state
        ], self::AUTH_ENDPOINT);
        return $auth_url;
    }

    /**
     * Handle OAuth callback from Constant Contact
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function handle_oauth_callback(WP_REST_Request $request) {
        $code = $request->get_param('code');
        $state = $request->get_param('state');
        $error = $request->get_param('error');
        if (!empty($error)) {
            return $this->error_response(
                'oauth_error',
                sprintf('Authorization error: %s', $error),
                400
            );
        }
        $stored_state = $this->get_option('oauth_state', '');
        $state_time = $this->get_option('oauth_state_time', 0);
        if (empty($stored_state) || $stored_state !== $state || (time() - $state_time) > 600) {
            return $this->error_response(
                'invalid_state',
                'Invalid state parameter. Please try again.',
                400
            );
        }
        $result = $this->exchange_code_for_token($code);
        if (is_wp_error($result)) {
            return $result;
        }
        $this->delete_option('oauth_state');
        $this->delete_option('oauth_state_time');
        $this->update_option('connection_successful', true);
        
        // Need to disable REST API response handling and output HTML directly
        header('Content-Type: text/html; charset=UTF-8');
        status_header(200);
        nocache_headers();
        
        // Send a minimal HTML page that immediately closes itself
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Closing...</title>
            <script>
                // Try to close the window immediately
                window.close();
                
                // If the window doesn\'t close (some browsers prevent it), 
                // show a message with instructions
                document.addEventListener("DOMContentLoaded", function() {
                    setTimeout(function() {
                        document.getElementById("message").style.display = "block";
                    }, 500);
                });
            </script>
            <style>
                body { font-family: sans-serif; text-align: center; padding: 20px; }
                #message { margin-top: 20px; }
            </style>
        </head>
        <body>
            <h3>Authorization Successful!</h3>
            <div id="message">
                <p>You can now close this window and return to the form.</p>
            </div>
        </body>
        </html>';
        
        // This is crucial - we need to exit to prevent WordPress from 
        // further processing and overriding our output
        exit;
    }

    /**
     * Exchange authorization code for access token
     * @param string $code
     * @return true|WP_Error
     */
    private function exchange_code_for_token($code) {
        $callback_url = rest_url(self::NAMESPACE . '/constantcontact/callback');
        $response = $this->make_token_request([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $callback_url
        ]);
        if (is_wp_error($response)) {
            return $response;
        }
        $this->save_token_data($response);
        return true;
    }

    /**
     * Make a request to the token endpoint
     * @param array $body
     * @return array|WP_Error
     */
    private function make_token_request($body) {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic ' . base64_encode("{$this->client_id}:{$this->client_secret}")
        ];
        $response = wp_remote_post(self::TOKEN_ENDPOINT, [
            'headers' => $headers,
            'body' => $body,
            'timeout' => 30
        ]);
        if (is_wp_error($response)) {
            return $response;
        }
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $error_body = wp_remote_retrieve_body($response);
            return new WP_Error(
                'token_request_failed',
                sprintf(
                    esc_html__('Failed to get access token. Response: %s', 'ht-contactform'),
                    $error_body
                )
            );
        }
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error(
                'json_error',
                esc_html__('Invalid JSON response from Constant Contact', 'ht-contactform')
            );
        }
        return $data;
    }

    /**
     * Save token data from OAuth response
     * @param array $data
     */
    private function save_token_data($data) {
        $this->update_option('access_token', $data['access_token']);
        $this->update_option('refresh_token', $data['refresh_token']);
        $this->update_option('token_expires', time() + $data['expires_in']);
    }

    /**
     * Check if we have a valid access token
     * @return bool
     */
    public function is_connected() {
        $token = $this->get_access_token();
        $expires = $this->get_option('token_expires', 0);
        return !empty($token) && time() < $expires;
    }

    /**
     * Get stored access token
     * @return string|false
     */
    public function get_access_token() {
        $token = $this->get_option('access_token', false);
        if (!$this->is_token_valid() && $this->get_option('refresh_token')) {
            $this->refresh_token();
            $token = $this->get_option('access_token', false);
        }
        return $token;
    }

    /**
     * Check if current token is valid
     * @return bool
     */
    private function is_token_valid() {
        $expires = $this->get_option('token_expires', 0);
        return time() < $expires;
    }

    /**
     * Refresh the access token using refresh token
     * @return bool
     */
    private function refresh_token() {
        $refresh_token = $this->get_option('refresh_token', '');
        if (empty($refresh_token)) {
            return false;
        }
        $response = $this->make_token_request([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token
        ]);
        if (is_wp_error($response)) {
            return false;
        }
        $this->save_token_data($response);
        return true;
    }

    //-------------------------------------------------------------------------
    // API OPERATIONS
    //-------------------------------------------------------------------------
    /**
     * Make an API request to Constant Contact
     * @param string $endpoint
     * @param string $method
     * @param array $data
     * @return array|WP_Error
     */
    private function api_request($endpoint, $method = 'GET', $data = []) {
        if (!$this->is_connected()) {
            return new WP_Error(
                'not_connected',
                esc_html__('Not connected to Constant Contact. Please connect first.', 'ht-contactform')
            );
        }
        $url = trailingslashit(self::API_BASE_URL) . ltrim($endpoint, '/');
        $access_token = $this->get_access_token();
        $args = [
            'method' => $method,
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json'
            ],
            'timeout' => 30
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
                    esc_html__('Error from Constant Contact API: %s', 'ht-contactform'),
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
                esc_html__('Invalid JSON response from Constant Contact', 'ht-contactform')
            );
        }
        return $data;
    }

    /**
     * Get Constant Contact contact lists
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function get_contact_lists(WP_REST_Request $request) {
        $response = $this->api_request('contact_lists');
        if (is_wp_error($response)) {
            return $response;
        }
        $lists = [];
        if (isset($response['lists']) && is_array($response['lists'])) {
            foreach ($response['lists'] as $list) {
                $lists[] = [
                    'value' => $list['list_id'],
                    'label' => $list['name']
                ];
            }
        }
        return $this->success_response('Lists retrieved successfully', $lists);
    }

    /**
     * Get Constant Contact contact tags
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function get_contact_tags(WP_REST_Request $request) {
        $response = $this->api_request('contact_tags');
        if (is_wp_error($response)) {
            return $response;
        }
        $tags = [];
        if (isset($response['tags']) && is_array($response['tags'])) {
            foreach ($response['tags'] as $tag) {
                $tags[] = [
                    'value' => $tag['tag_id'],
                    'label' => $tag['name']
                ];
            }
        }
        return $this->success_response('Tags retrieved successfully', $tags);
    }

    /**
     * Get Constant Contact contact custom fields
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function get_contact_custom_fields(WP_REST_Request $request) {
        $response = $this->api_request('contact_custom_fields');
        if (is_wp_error($response)) {
            return $response;
        }
        $custom_fields = [];
        if (isset($response['custom_fields']) && is_array($response['custom_fields'])) {
            foreach ($response['custom_fields'] as $custom_field) {
                $custom_fields[] = [
                    'value' => $custom_field['custom_field_id'],
                    'label' => $custom_field['label']
                ];
            }
        }
        return $this->success_response('Custom Fields retrieved successfully', $custom_fields);
    }

    /**
     * Subscribe Constant Contact contact
     * @param array $data
     * @return WP_REST_Response|WP_Error
     */
    public function subscribe($data) {
        // Try up to 3 times for 500 errors which might be temporary
        $max_retries = 3;
        $retry_count = 0;
        
        while ($retry_count < $max_retries) {
            $response = $this->api_request('contacts', 'POST', $data);
            
            // If not an error or not a 500 error, break the loop
            if (!is_wp_error($response) || 
                !isset($response->error_data['api_error']['status']) || 
                $response->error_data['api_error']['status'] !== 500) {
                break;
            }
            
            // Wait briefly before retrying (increasing delay with each retry)
            $retry_delay = pow(2, $retry_count) * 0.5; // 0.5s, 1s, 2s
            sleep($retry_delay);
            $retry_count++;
        }
        
        if (is_wp_error($response)) {
            // Try to extract more specific error information
            $error_message = $response->get_error_message();
            $parsed_error = $this->parse_api_error($error_message);
            
            if ($parsed_error) {
                // Return a more specific error with the parsed details
                return new \WP_Error(
                    'constant_contact_error',
                    $parsed_error['message'],
                    ['status' => $response->error_data['api_error']['status'] ?? 400]
                );
            }
            
            return $response;
        }
        
        return $this->success_response('Contact subscribed successfully', $response);
    }
    
    /**
     * Parse Constant Contact API error messages
     * @param string $error_message
     * @return array|false
     */
    private function parse_api_error($error_message) {
        // Extract the JSON part from the error message
        if (preg_match('/\[(.*)\]/', $error_message, $matches)) {
            $json_error = $matches[1];
            $error_data = json_decode($json_error, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($error_data)) {
                // Common Constant Contact error codes and user-friendly messages
                $error_codes = [
                    'contacts.api.internal_server_error' => 'Constant Contact is experiencing technical difficulties. Please try again later.',
                    'contacts.api.email_already_exists' => 'This email is already subscribed to your Constant Contact account.',
                    'contacts.api.invalid_email_address' => 'The email address format is invalid.',
                    'contacts.api.duplicate_in_request' => 'Duplicate email address in the request.',
                    'contacts.api.contact_limit_exceeded' => 'Your Constant Contact account has reached its contact limit.',
                ];
                
                foreach ($error_data as $error) {
                    if (isset($error['error_key']) && isset($error_codes[$error['error_key']])) {
                        return [
                            'code' => $error['error_key'],
                            'message' => $error_codes[$error['error_key']],
                            'original' => $error['error_message'] ?? ''
                        ];
                    } else if (isset($error['error_key']) && isset($error['error_message'])) {
                        return [
                            'code' => $error['error_key'],
                            'message' => 'Constant Contact error: ' . $error['error_message'],
                            'original' => $error['error_message']
                        ];
                    }
                }
            }
        }
        
        return false;
    }

    //-------------------------------------------------------------------------
    // HELPER METHODS
    //-------------------------------------------------------------------------
    /**
     * Create a success response
     * @param string $message
     * @param array $data
     * @param bool $success
     * @return WP_REST_Response
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
     * Create an error response
     * @param string $code
     * @param string $message
     * @param int $status
     * @return WP_Error
     */
    private function error_response($code, $message, $status = 400) {
        return new WP_Error(
            $code,
            esc_html__($message, 'ht-contactform'),
            ['status' => $status]
        );
    }
}