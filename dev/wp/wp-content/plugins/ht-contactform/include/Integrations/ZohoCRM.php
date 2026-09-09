<?php

namespace HTContactForm\Integrations;

use WP_Error;

/**
 * Zoho CRM Class
 *
 * Handles Zoho CRM integration with OAuth2 authentication for creating
 * Leads, Contacts, and other CRM records from form submissions.
 */
class ZohoCRM {
    //-------------------------------------------------------------------------
    // CONSTANTS
    //-------------------------------------------------------------------------

    /**
     * Zoho data center configurations
     * Each region has different OAuth and API endpoints
     */
    const DATA_CENTERS = [
        'com' => [
            'label' => 'United States (zoho.com)',
            'accounts_url' => 'https://accounts.zoho.com',
            'api_url' => 'https://www.zohoapis.com',
        ],
        'eu' => [
            'label' => 'Europe (zoho.eu)',
            'accounts_url' => 'https://accounts.zoho.eu',
            'api_url' => 'https://www.zohoapis.eu',
        ],
        'in' => [
            'label' => 'India (zoho.in)',
            'accounts_url' => 'https://accounts.zoho.in',
            'api_url' => 'https://www.zohoapis.in',
        ],
        'com.au' => [
            'label' => 'Australia (zoho.com.au)',
            'accounts_url' => 'https://accounts.zoho.com.au',
            'api_url' => 'https://www.zohoapis.com.au',
        ],
        'com.cn' => [
            'label' => 'China (zoho.com.cn)',
            'accounts_url' => 'https://accounts.zoho.com.cn',
            'api_url' => 'https://www.zohoapis.com.cn',
        ],
        'jp' => [
            'label' => 'Japan (zoho.jp)',
            'accounts_url' => 'https://accounts.zoho.jp',
            'api_url' => 'https://www.zohoapis.jp',
        ],
    ];

    /**
     * OAuth scopes required for CRM access
     */
    const OAUTH_SCOPES = 'ZohoCRM.modules.ALL,ZohoCRM.settings.ALL,ZohoCRM.settings.modules.READ';

    /**
     * Supported modules (services) for form integrations
     */
    const SUPPORTED_MODULES = [
        'Leads',
        'Contacts',
        'Accounts',
        'Deals',
        'Tasks',
        'Campaigns',
        'Vendors',
        'Cases',
        'Solutions',
    ];

    //-------------------------------------------------------------------------
    // PROPERTIES
    //-------------------------------------------------------------------------

    /** @var string Client ID */
    private $client_id = '';

    /** @var string Client Secret */
    private $client_secret = '';

    /** @var string Access Token */
    private $access_token = '';

    /** @var string Refresh Token */
    private $refresh_token = '';

    /** @var string Data Center */
    private $data_center = 'com';

    /** @var self|null Singleton instance */
    private static $instance = null;

    //-------------------------------------------------------------------------
    // INITIALIZATION
    //-------------------------------------------------------------------------

    /**
     * Get singleton instance
     *
     * @param string $client_id Client ID
     * @param string $client_secret Client Secret
     * @param string $data_center Data Center
     * @return self
     */
    public static function get_instance($client_id = '', $client_secret = '', $data_center = 'com') {
        if (!isset(self::$instance)) {
            self::$instance = new self($client_id, $client_secret, $data_center);
        }
        return self::$instance;
    }

    /**
     * Constructor
     *
     * @param string $client_id Client ID
     * @param string $client_secret Client Secret
     * @param string $data_center Data Center
     */
    public function __construct($client_id = '', $client_secret = '', $data_center = 'com') {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->data_center = $data_center;

        // Load stored tokens
        $tokens = get_option('ht_form_zohocrm_tokens', []);
        if (!empty($tokens['access_token'])) {
            $this->access_token = $tokens['access_token'];
        }
        if (!empty($tokens['refresh_token'])) {
            $this->refresh_token = $tokens['refresh_token'];
        }
    }

    //-------------------------------------------------------------------------
    // OAUTH METHODS
    //-------------------------------------------------------------------------

    /**
     * Get OAuth authorization URL
     *
     * @return string Authorization URL
     */
    public function get_auth_url() {
        $dc = self::DATA_CENTERS[$this->data_center] ?? self::DATA_CENTERS['com'];
        $redirect_uri = $this->get_redirect_uri();

        $params = [
            'scope' => self::OAUTH_SCOPES,
            'client_id' => $this->client_id,
            'response_type' => 'code',
            'access_type' => 'offline',
            'redirect_uri' => $redirect_uri,
            'prompt' => 'consent',
        ];

        return $dc['accounts_url'] . '/oauth/v2/auth?' . http_build_query($params);
    }

    /**
     * Get redirect URI for OAuth callback
     *
     * @return string Redirect URI
     */
    public function get_redirect_uri() {
        return admin_url('admin.php?page=htcontact-form&path=integrations&zohocrm_auth=1');
    }

    /**
     * Exchange authorization code for tokens
     *
     * @param string $code Authorization code
     * @return array|WP_Error Token response or error
     */
    public function exchange_code_for_tokens($code) {
        $dc = self::DATA_CENTERS[$this->data_center] ?? self::DATA_CENTERS['com'];
        $token_url = $dc['accounts_url'] . '/oauth/v2/token';

        $response = wp_remote_post($token_url, [
            'timeout' => 30,
            'body' => [
                'grant_type' => 'authorization_code',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'redirect_uri' => $this->get_redirect_uri(),
                'code' => $code,
            ],
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error'])) {
            return new WP_Error('zohocrm_oauth_error', $body['error']);
        }

        if (!isset($body['access_token'])) {
            return new WP_Error('zohocrm_oauth_error', 'No access token in response');
        }

        // Store tokens with credentials to validate they haven't changed
        $tokens = [
            'access_token' => $body['access_token'],
            'refresh_token' => $body['refresh_token'] ?? $this->refresh_token,
            'expires_in' => $body['expires_in'] ?? 3600,
            'token_type' => $body['token_type'] ?? 'Bearer',
            'created_at' => time(),
            'client_id' => $this->client_id,
            'client_secret_hash' => md5($this->client_secret),
            'data_center' => $this->data_center,
        ];

        update_option('ht_form_zohocrm_tokens', $tokens);

        $this->access_token = $tokens['access_token'];
        $this->refresh_token = $tokens['refresh_token'];

        return $tokens;
    }

    /**
     * Refresh access token using refresh token
     *
     * @return array|WP_Error New token response or error
     */
    public function refresh_access_token() {
        if (empty($this->refresh_token)) {
            return new WP_Error('zohocrm_error', 'No refresh token available');
        }

        $dc = self::DATA_CENTERS[$this->data_center] ?? self::DATA_CENTERS['com'];
        $token_url = $dc['accounts_url'] . '/oauth/v2/token';

        $response = wp_remote_post($token_url, [
            'timeout' => 30,
            'body' => [
                'grant_type' => 'refresh_token',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'refresh_token' => $this->refresh_token,
            ],
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error'])) {
            return new WP_Error('zohocrm_oauth_error', $body['error']);
        }

        if (!isset($body['access_token'])) {
            return new WP_Error('zohocrm_oauth_error', 'No access token in response');
        }

        // Update stored tokens
        $tokens = get_option('ht_form_zohocrm_tokens', []);
        $tokens['access_token'] = $body['access_token'];
        $tokens['expires_in'] = $body['expires_in'] ?? 3600;
        $tokens['created_at'] = time();

        update_option('ht_form_zohocrm_tokens', $tokens);

        $this->access_token = $body['access_token'];

        return $tokens;
    }

    /**
     * Check if access token is expired and refresh if needed
     *
     * @return bool|WP_Error True if token is valid, WP_Error on failure
     */
    private function ensure_valid_token() {
        $tokens = get_option('ht_form_zohocrm_tokens', []);

        if (empty($tokens['access_token'])) {
            return new WP_Error('zohocrm_error', 'Not authenticated with Zoho CRM');
        }

        // Check if token is expired (with 5 minute buffer)
        $expires_at = ($tokens['created_at'] ?? 0) + ($tokens['expires_in'] ?? 3600) - 300;

        if (time() > $expires_at) {
            $result = $this->refresh_access_token();
            if (is_wp_error($result)) {
                return $result;
            }
        }

        return true;
    }

    /**
     * Revoke tokens and disconnect
     *
     * @return bool
     */
    public function disconnect() {
        delete_option('ht_form_zohocrm_tokens');

        // Clear cached data
        delete_transient('ht_form_zohocrm_modules');
        foreach (self::SUPPORTED_MODULES as $module) {
            delete_transient('ht_form_zohocrm_fields_' . sanitize_key($module));
        }

        $this->access_token = '';
        $this->refresh_token = '';
        return true;
    }

    /**
     * Check if connected to Zoho CRM
     *
     * @return bool
     */
    public function is_connected() {
        $tokens = get_option('ht_form_zohocrm_tokens', []);

        // Check tokens exist
        if (empty($tokens['access_token']) || empty($tokens['refresh_token'])) {
            return false;
        }

        // Tokens must have stored credentials (requires re-auth for old tokens)
        if (empty($tokens['client_id']) || empty($tokens['client_secret_hash'])) {
            return false;
        }

        // Validate credentials haven't changed
        if ($tokens['client_id'] !== $this->client_id) {
            return false;
        }

        if ($tokens['client_secret_hash'] !== md5($this->client_secret)) {
            return false;
        }

        // Validate data center hasn't changed (if stored)
        if (!empty($tokens['data_center']) && $tokens['data_center'] !== $this->data_center) {
            return false;
        }

        return true;
    }

    //-------------------------------------------------------------------------
    // API METHODS
    //-------------------------------------------------------------------------

    /**
     * Make API request to Zoho CRM
     *
     * @param string $endpoint API endpoint
     * @param string $method HTTP method
     * @param array $data Request data
     * @return array|WP_Error Response or error
     */
    private function api_request($endpoint, $method = 'GET', $data = []) {
        $valid = $this->ensure_valid_token();
        if (is_wp_error($valid)) {
            return $valid;
        }

        $dc = self::DATA_CENTERS[$this->data_center] ?? self::DATA_CENTERS['com'];
        $url = $dc['api_url'] . '/crm/v2/' . ltrim($endpoint, '/');

        $args = [
            'method' => $method,
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $this->access_token,
                'Content-Type' => 'application/json',
            ],
        ];

        if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $args['body'] = wp_json_encode($data);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        // Handle token expiration
        if ($code === 401) {
            // Try to refresh token and retry
            $refresh_result = $this->refresh_access_token();
            if (is_wp_error($refresh_result)) {
                return new WP_Error('zohocrm_auth_error', 'Authentication failed. Please reconnect to Zoho CRM.');
            }

            // Retry the request
            $args['headers']['Authorization'] = 'Zoho-oauthtoken ' . $this->access_token;
            $response = wp_remote_request($url, $args);

            if (is_wp_error($response)) {
                return $response;
            }

            $code = wp_remote_retrieve_response_code($response);
            $body = json_decode(wp_remote_retrieve_body($response), true);
        }

        if ($code >= 400) {
            $error_message = $body['message'] ?? $body['error'] ?? 'API request failed';
            return new WP_Error('zohocrm_api_error', "Zoho CRM API error [{$code}]: {$error_message}");
        }

        return $body;
    }

    /**
     * Get available CRM modules
     *
     * @return array|WP_Error Modules list or error
     */
    public function get_modules() {
        // Check cache first
        $cache_key = 'ht_form_zohocrm_modules';
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        $response = $this->api_request('settings/modules');

        if (is_wp_error($response)) {
            return $response;
        }

        $modules = [];
        if (!empty($response['modules'])) {
            foreach ($response['modules'] as $module) {
                // Only include supported modules that can create records
                if (!empty($module['creatable']) && $module['creatable'] === true && in_array($module['api_name'], self::SUPPORTED_MODULES, true)) {
                    $modules[] = [
                        'api_name' => $module['api_name'],
                        'plural_label' => $module['singular_label'], // Use singular for cleaner display
                        'singular_label' => $module['singular_label'],
                    ];
                }
            }
        }

        // Cache for 1 hour
        set_transient($cache_key, $modules, HOUR_IN_SECONDS);

        return $modules;
    }

    /**
     * Get fields for a specific module
     *
     * @param string $module Module API name
     * @return array|WP_Error Fields list or error
     */
    public function get_module_fields($module) {
        // Validate module against supported list
        if (!in_array($module, self::SUPPORTED_MODULES, true)) {
            return new WP_Error('zohocrm_error', 'Unsupported module');
        }

        // Check cache first
        $cache_key = 'ht_form_zohocrm_fields_' . sanitize_key($module);
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        $response = $this->api_request("settings/fields?module={$module}");

        if (is_wp_error($response)) {
            return $response;
        }

        $fields = [];
        if (!empty($response['fields'])) {
            foreach ($response['fields'] as $field) {
                // Skip read-only fields (but include editable fields)
                $is_read_only = isset($field['read_only']) && $field['read_only'] === true;

                if (!$is_read_only) {
                    $field_data = [
                        'api_name' => $field['api_name'],
                        'field_label' => $field['field_label'],
                        'data_type' => $field['data_type'],
                        'required' => $field['system_mandatory'] ?? false,
                    ];

                    // Include picklist values for picklist fields (like Lead Source, Industry, Rating, etc.)
                    if ($field['data_type'] === 'picklist' && !empty($field['pick_list_values']) && is_array($field['pick_list_values'])) {
                        $options = [];
                        foreach ($field['pick_list_values'] as $pick_value) {
                            if (!empty($pick_value['display_value'])) {
                                $options[] = [
                                    'value' => $pick_value['display_value'],
                                    'label' => $pick_value['display_value'],
                                ];
                            }
                        }
                        if (!empty($options)) {
                            $field_data['options'] = $options;
                        }
                    }

                    $fields[] = $field_data;
                }
            }
        }

        // Sort: required fields first, then alphabetically
        usort($fields, function($a, $b) {
            if ($a['required'] !== $b['required']) {
                return $b['required'] ? 1 : -1;
            }
            return strcmp($a['field_label'], $b['field_label']);
        });

        // Cache for 1 hour
        set_transient($cache_key, $fields, HOUR_IN_SECONDS);

        return $fields;
    }

    /**
     * Create a record in Zoho CRM
     *
     * @param string $module Module API name
     * @param array $data Record data
     * @return array|WP_Error Created record or error
     */
    public function create_record($module, $data) {
        $payload = [
            'data' => [$data],
            'trigger' => ['workflow'],
        ];

        $response = $this->api_request($module, 'POST', $payload);

        if (is_wp_error($response)) {
            return $response;
        }

        if (!empty($response['data'][0]['code']) && $response['data'][0]['code'] !== 'SUCCESS') {
            return new WP_Error(
                'zohocrm_create_error',
                $response['data'][0]['message'] ?? 'Failed to create record'
            );
        }

        return $response;
    }

    /**
     * Search for existing record by email
     *
     * @param string $module Module API name
     * @param string $email Email to search
     * @return array|WP_Error|null Record if found, null if not found, WP_Error on failure
     */
    public function find_by_email($module, $email) {
        $encoded_email = rawurlencode($email);
        $response = $this->api_request("{$module}/search?email={$encoded_email}");

        if (is_wp_error($response)) {
            return $response;
        }

        if (!empty($response['data'][0])) {
            return $response['data'][0];
        }

        return null;
    }

    /**
     * Update an existing record
     *
     * @param string $module Module API name
     * @param string $record_id Record ID
     * @param array $data Updated data
     * @return array|WP_Error Updated record or error
     */
    public function update_record($module, $record_id, $data) {
        $payload = [
            'data' => [$data],
        ];

        $response = $this->api_request("{$module}/{$record_id}", 'PUT', $payload);

        if (is_wp_error($response)) {
            return $response;
        }

        return $response;
    }

    //-------------------------------------------------------------------------
    // FORM SUBMISSION HANDLER
    //-------------------------------------------------------------------------

    /**
     * Process form submission and create/update CRM record
     *
     * @param object $integration Integration configuration
     * @param array $form Form configuration
     * @param array $form_data Submitted form data
     * @param array $meta Entry metadata
     * @return array|WP_Error Result or error
     */
    public function subscribe($integration, $form, $form_data, $meta) {
        if (empty($integration->enabled)) {
            return new WP_Error('zohocrm_error', 'Integration is not enabled');
        }

        if (empty($integration->module)) {
            return new WP_Error('zohocrm_error', 'No module selected');
        }

        // Build record data from field mapping
        $record_data = [];

        if (!empty($integration->field_mapping) && is_array($integration->field_mapping)) {
            foreach ($integration->field_mapping as $zoho_field => $form_value) {
                if (!empty($form_value)) {
                    // Replace smart tags with actual values
                    $value = $this->replace_smart_tags($form_value, $form_data);
                    if (!empty($value)) {
                        $record_data[$zoho_field] = $value;
                    }
                }
            }
        }

        if (empty($record_data)) {
            return new WP_Error('zohocrm_error', 'No field data to submit');
        }

        // Check for duplicate handling
        $email_field = $record_data['Email'] ?? null;
        $update_existing = !empty($integration->update_existing);

        if ($update_existing && $email_field) {
            $existing = $this->find_by_email($integration->module, $email_field);
            if (!is_wp_error($existing) && $existing) {
                return $this->update_record($integration->module, $existing['id'], $record_data);
            }
        }

        // Create new record
        return $this->create_record($integration->module, $record_data);
    }

    /**
     * Replace smart tags with form data values
     *
     * @param string $value Value with potential smart tags
     * @param array $form_data Form data
     * @return string Processed value
     */
    private function replace_smart_tags($value, $form_data) {
        // Match {field_name} pattern
        return preg_replace_callback('/\{([^}]+)\}/', function($matches) use ($form_data) {
            $tag = $matches[1];

            // Handle special tags
            if ($tag === 'site_url') {
                return home_url();
            }
            if ($tag === 'admin_email') {
                return get_option('admin_email');
            }

            // Strip 'input.' prefix if present
            if (strpos($tag, 'input.') === 0) {
                $tag = substr($tag, 6); // Remove 'input.' (6 characters)
            }

            // Handle nested field access (e.g., 'name.last_name' -> $form_data['name']['last_name'])
            $keys = explode('.', $tag);
            $result = $form_data;

            foreach ($keys as $key) {
                if (is_array($result) && isset($result[$key])) {
                    $result = $result[$key];
                } else {
                    return ''; // Key not found
                }
            }

            // If result is still an array, convert to string
            if (is_array($result)) {
                return implode(' ', array_filter($result));
            }

            return (string) $result;
        }, $value);
    }

    //-------------------------------------------------------------------------
    // STATIC HELPERS
    //-------------------------------------------------------------------------

    /**
     * Get data centers list for dropdown
     *
     * @return array Data centers
     */
    public static function get_data_centers() {
        $options = [];
        foreach (self::DATA_CENTERS as $key => $dc) {
            $options[] = [
                'value' => $key,
                'label' => $dc['label'],
            ];
        }
        return $options;
    }
}
