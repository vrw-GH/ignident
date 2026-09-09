<?php
/**
 * Notion Integration Class
 *
 * Handles all Notion API interactions for form submissions including
 * database management and page creation.
 *
 * @package HTContactForm
 * @subpackage Integrations
 */

namespace HTContactForm\Integrations;

use HTContactFormAdmin\Includes\Services\Helper;
use WP_Error;
use WP_REST_Response;

/**
 * Notion Integration Handler
 *
 * Provides functionality to integrate form submissions with Notion
 * databases using their REST API.
 */
class Notion {
    /**
     * Notion API key (Internal Integration Token)
     *
     * @var string|null
     */
    private $api_key = null;

    /**
     * Notion API base URL
     *
     * @var string
     */
    private $api_url = 'https://api.notion.com/v1/';

    /**
     * Notion API version
     *
     * @var string
     */
    private $api_version = '2022-06-28';

    /**
     * Helper class instance
     *
     * @var Helper|null
     */
    private $helper = null;

    /**
     * Instances storage by API key
     *
     * @var array
     */
    private static $instances = [];

    /**
     * Get instance by API key
     *
     * @param string|null $api_key Notion API key
     * @return self Instance of the Notion class
     */
    public static function get_instance($api_key = null) {
        $key = md5($api_key ?? '');
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new self($api_key);
        }
        return self::$instances[$key];
    }

    /**
     * Constructor
     *
     * @param string|null $api_key Notion API key (Internal Integration Token)
     */
    public function __construct($api_key = null) {
        $this->api_key = is_string($api_key) ? trim($api_key) : '';
        if (class_exists(Helper::class)) {
            $this->helper = Helper::get_instance();
        }
    }

    /**
     * Make an API request to Notion
     *
     * @param string $endpoint The API endpoint to call
     * @param string $method The HTTP method to use (GET, POST, PATCH, DELETE)
     * @param array $data The data to send with the request
     * @return array|WP_Error Response data on success, WP_Error on failure
     */
    private function api_request($endpoint, $method = 'GET', $data = []) {
        if (empty($this->api_key)) {
            return new WP_Error('api_key_missing', __('Notion API key is required', 'ht-contactform'));
        }

        $url = $this->api_url . ltrim($endpoint, '/');

        $args = [
            'method'    => strtoupper($method),
            'timeout'   => 30,
            'headers'   => [
                'Content-Type'    => 'application/json',
                'Accept'          => 'application/json',
                'Authorization'   => 'Bearer ' . $this->api_key,
                'Notion-Version'  => $this->api_version,
                'User-Agent'      => 'HT-ContactForm/' . HTCONTACTFORM_VERSION,
            ],
        ];

        if (!empty($data) && $method !== 'GET') {
            $args['body'] = wp_json_encode($data);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            return new WP_Error(
                'http_request_failed',
                sprintf(
                    /* translators: %s: Error message */
                    __('HTTP request failed: %s', 'ht-contactform'),
                    $response->get_error_message()
                )
            );
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        // Handle rate limiting
        if ($response_code === 429) {
            $retry_after = wp_remote_retrieve_header($response, 'retry-after') ?: 60;
            return new WP_Error(
                'rate_limited',
                sprintf(
                    /* translators: %d: Number of seconds to wait */
                    __('Notion API rate limit exceeded. Please retry after %d seconds.', 'ht-contactform'),
                    (int) $retry_after
                ),
                ['status' => 429, 'retry_after' => (int) $retry_after]
            );
        }

        if ($response_code < 200 || $response_code >= 300) {
            $error_data = json_decode($response_body, true);
            $error_message = $error_data['message'] ?? $response_body;

            return new WP_Error(
                'api_error',
                sprintf(
                    /* translators: %1$d: HTTP status code, %2$s: Error message */
                    __('Notion API error [%1$d]: %2$s', 'ht-contactform'),
                    $response_code,
                    $error_message
                ),
                ['status' => $response_code, 'response' => $error_data]
            );
        }

        $decoded_data = json_decode($response_body, true);
        if (json_last_error() !== JSON_ERROR_NONE && !empty($response_body)) {
            return new WP_Error(
                'json_decode_error',
                __('Invalid JSON response from Notion API', 'ht-contactform'),
                ['response_body' => substr($response_body, 0, 200)]
            );
        }

        return $decoded_data;
    }

    /**
     * Search for databases the integration has access to
     *
     * @return array|WP_Error Array of databases on success, WP_Error on failure
     */
    public function get_databases() {
        $response = $this->api_request('search', 'POST', [
            'filter' => [
                'value' => 'database',
                'property' => 'object'
            ],
            'page_size' => 100
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        if (isset($response['results']) && is_array($response['results'])) {
            return array_map(function($db) {
                // Get database title
                $title = '';
                if (!empty($db['title'])) {
                    foreach ($db['title'] as $text) {
                        $title .= $text['plain_text'] ?? '';
                    }
                }
                return [
                    'id' => $db['id'],
                    'title' => $title ?: __('Untitled', 'ht-contactform'),
                    'url' => $db['url'] ?? '',
                ];
            }, $response['results']);
        }

        return [];
    }

    /**
     * Get database properties/schema
     *
     * @param string $database_id Notion database ID
     * @return array|WP_Error Array of properties on success, WP_Error on failure
     */
    public function get_database_properties($database_id) {
        if (empty($database_id)) {
            return new WP_Error('missing_database_id', __('Database ID is required', 'ht-contactform'));
        }

        $database_id = sanitize_text_field($database_id);
        $response = $this->api_request('databases/' . $database_id);

        if (is_wp_error($response)) {
            return $response;
        }

        if (isset($response['properties']) && is_array($response['properties'])) {
            $properties = [];
            foreach ($response['properties'] as $name => $prop) {
                // Skip computed/read-only properties
                $skip_types = ['rollup', 'created_by', 'created_time', 'last_edited_by', 'last_edited_time', 'formula'];
                if (in_array($prop['type'], $skip_types)) {
                    continue;
                }

                $properties[] = [
                    'id' => $prop['id'],
                    'name' => $name,
                    'type' => $prop['type'],
                ];
            }
            return $properties;
        }

        return [];
    }

    /**
     * Create a page in a Notion database (form submission)
     *
     * @param object $integration Integration settings
     * @param array $form Form data
     * @param array $form_data Form submission data
     * @param array $meta Meta data
     * @return WP_Error|WP_REST_Response Response
     */
    public function create_page($integration, $form, $form_data, $meta) {
        // Validate integration object
        if (!is_object($integration)) {
            return new WP_Error('invalid_integration', __('Invalid integration configuration', 'ht-contactform'));
        }

        if (empty($integration->enabled) || empty($integration->database_id)) {
            return new WP_Error('integration_disabled', __('Integration is not enabled for this form', 'ht-contactform'));
        }

        // Build properties from merge fields
        $properties = [];

        // Handle merge_fields as object or array
        $merge_fields = $integration->merge_fields;
        if (is_object($merge_fields)) {
            $merge_fields = (array) $merge_fields;
        }

        // Fetch database properties to get correct types
        $db_properties = $this->get_database_properties($integration->database_id);
        $property_type_map = [];
        if (!is_wp_error($db_properties) && is_array($db_properties)) {
            foreach ($db_properties as $prop) {
                $property_type_map[$prop['name']] = $prop['type'];
            }
        }

        if (!empty($merge_fields) && is_array($merge_fields)) {
            foreach ($merge_fields as $property_name => $value) {
                if (empty($value)) {
                    continue;
                }

                $processed_value = $this->process_field_value($value, $form_data, $form);

                if (empty($processed_value)) {
                    continue;
                }

                // Get property type from database properties
                $property_type = $property_type_map[$property_name] ?? 'rich_text';
                $properties[$property_name] = $this->format_property_value($processed_value, $property_type);
            }
        }

        if (empty($properties)) {
            return new WP_Error('no_properties', __('No properties to add to the page', 'ht-contactform'));
        }

        // Create page data
        $page_data = [
            'parent' => [
                'database_id' => sanitize_text_field($integration->database_id)
            ],
            'properties' => $properties
        ];

        // Make API request to create page
        $response = $this->api_request('pages', 'POST', $page_data);

        if (is_wp_error($response)) {
            do_action('ht_form/notion_integration_result', null, 'failed', $response->get_error_message());
            return $response;
        }

        do_action('ht_form/notion_integration_result', $response, 'success', __('Notion page created successfully', 'ht-contactform'));

        return new WP_REST_Response([
            'message' => __('Notion page created successfully', 'ht-contactform'),
            'page_id' => $response['id'] ?? null,
            'url' => $response['url'] ?? null,
        ], 200);
    }

    /**
     * Format a value according to Notion property type
     *
     * @param string $value The processed value
     * @param string $type The Notion property type
     * @return array Formatted property value for Notion API
     */
    private function format_property_value($value, $type) {
        switch ($type) {
            case 'title':
                return [
                    'title' => [
                        [
                            'text' => [
                                'content' => sanitize_text_field($value)
                            ]
                        ]
                    ]
                ];

            case 'rich_text':
                return [
                    'rich_text' => [
                        [
                            'text' => [
                                'content' => sanitize_textarea_field($value)
                            ]
                        ]
                    ]
                ];

            case 'number':
                return [
                    'number' => is_numeric($value) ? floatval($value) : 0
                ];

            case 'select':
                return [
                    'select' => [
                        'name' => sanitize_text_field($value)
                    ]
                ];

            case 'multi_select':
                $options = array_map('trim', explode(',', $value));
                return [
                    'multi_select' => array_map(function($opt) {
                        return ['name' => sanitize_text_field($opt)];
                    }, $options)
                ];

            case 'date':
                return [
                    'date' => [
                        'start' => sanitize_text_field($value)
                    ]
                ];

            case 'checkbox':
                return [
                    'checkbox' => filter_var($value, FILTER_VALIDATE_BOOLEAN)
                ];

            case 'email':
                return [
                    'email' => sanitize_email($value)
                ];

            case 'phone_number':
                return [
                    'phone_number' => sanitize_text_field($value)
                ];

            case 'url':
                return [
                    'url' => esc_url_raw($value)
                ];

            default:
                // Default to rich_text
                return [
                    'rich_text' => [
                        [
                            'text' => [
                                'content' => sanitize_text_field($value)
                            ]
                        ]
                    ]
                ];
        }
    }

    /**
     * Process field value with smart tags
     *
     * @param string $value The field value or smart tag
     * @param array $form_data Form submission data
     * @param array $form Form configuration
     * @return string Processed value
     */
    private function process_field_value($value, $form_data, $form) {
        return $this->helper->filter_vars($value, $form_data, $form);
    }
}
