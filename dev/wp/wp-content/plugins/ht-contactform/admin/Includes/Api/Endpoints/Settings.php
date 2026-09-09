<?php

namespace HTContactFormAdmin\Includes\Api\Endpoints;

use HTContactFormAdmin\Includes\Config\Form as FormConfig;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Settings API Handler Class
 * 
 * Handles all REST API endpoints for global settings management
 */
class Settings {
    //-------------------------------------------------------------------------
    // PROPERTIES
    //-------------------------------------------------------------------------
    
    /** @var string REST API namespace */
    private $namespace = 'ht-form/v1';

    /** @var self|null Singleton instance */
    private static $instance = null;
    
    /** @var array Global settings */
    private $global_settings;
    /**
     * Option name for storing global settings
     */
    public const OPTION_NAME = 'ht_form_global_settings';

    //-------------------------------------------------------------------------
    // INITIALIZATION
    //-------------------------------------------------------------------------
    
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
        $this->global_settings = FormConfig::get_instance()->form_global_settings();
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register routes
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/settings',
            [
                [
                    'methods'             => 'GET',
                    'callback'            => [$this, 'get_settings'],
                    'permission_callback' => [$this, 'permissions_check'],
                ],
                [
                    'methods'             => 'PUT',
                    'callback'            => [$this, 'update_settings'],
                    'permission_callback' => [$this, 'permissions_check'],
                    'args'                => [
                        'settings' => [
                            'required' => true,
                            'type'     => 'object',
                        ],
                    ],
                ]
            ]
        );

        // Captcha key verification endpoint
        register_rest_route(
            $this->namespace,
            '/settings/verify-captcha',
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'verify_captcha'],
                'permission_callback' => [$this, 'permissions_check'],
                'args'                => [
                    'type' => [
                        'required' => true,
                        'type'     => 'string',
                    ],
                    'secret_key' => [
                        'required' => true,
                        'type'     => 'string',
                    ],
                ],
            ]
        );
    }

    /**
     * Check if user has permission
     *
     * @param WP_REST_Request $request Request object
     * @return bool|WP_Error True if user has permission, WP_Error otherwise
     */
    public function permissions_check($request) {
        if (!current_user_can('manage_options')) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You do not have permission to manage settings.', 'ht-contactform'),
                ['status' => 403]
            );
        }
        return true;
    }

    /**
     * Get global settings
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function get_settings($request) {
        $default = array_reduce($this->global_settings, function($carry, $section) {
            $carry[$section['id']] = array_reduce($section['settings'], function($carry, $field) {
                $carry[$field['id']] = $field['value'];
                return $carry;
            }, []);
            return $carry;
        }, []);

        $settings = get_option(self::OPTION_NAME, $default);

        // Migrate old reCAPTCHA settings to new multi-version structure
        $settings = $this->migrate_recaptcha_settings($settings);

        return new WP_REST_Response($settings, 200);
    }

    /**
     * Migrate old reCAPTCHA settings to new multi-version structure
     *
     * Old structure: recaptcha_version, recaptcha_site_key, recaptcha_secret_key
     * New structure: recaptcha_active_version, recaptcha_v2_site_key, recaptcha_v2_secret_key,
     *                recaptcha_v3_site_key, recaptcha_v3_secret_key
     *
     * @param array $settings Current settings
     * @return array Migrated settings
     */
    private function migrate_recaptcha_settings($settings) {
        // Ensure captcha array exists (fix potential undefined index)
        if (!isset($settings['captcha']) || !is_array($settings['captcha'])) {
            return $settings;
        }

        // Check if migration is needed (old keys exist, new keys don't)
        if (!empty($settings['captcha']['recaptcha_site_key']) &&
            empty($settings['captcha']['recaptcha_v2_site_key']) &&
            empty($settings['captcha']['recaptcha_v3_site_key'])) {

            $old_version = $settings['captcha']['recaptcha_version'] ?? 'reCAPTCHAv2';
            $target = ($old_version === 'reCAPTCHAv3') ? 'v3' : 'v2';

            // Migrate keys to appropriate version
            $settings['captcha']["recaptcha_{$target}_site_key"] = $settings['captcha']['recaptcha_site_key'];
            $settings['captcha']["recaptcha_{$target}_secret_key"] = $settings['captcha']['recaptcha_secret_key'] ?? '';
            $settings['captcha']['recaptcha_active_version'] = $target;

            // Clean up old settings keys after migration
            unset($settings['captcha']['recaptcha_version']);
            unset($settings['captcha']['recaptcha_site_key']);
            unset($settings['captcha']['recaptcha_secret_key']);

            // Save migrated settings
            update_option(self::OPTION_NAME, $settings);
        }

        return $settings;
    }

    /**
     * Update global settings
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object
     */
    public function update_settings($request) {
        $settings = $request->get_param('settings');
        if (!$settings) {
            // Try to get from JSON body for backward compatibility
            $settings = $request->get_json_params();
        }
        
        $sanitize_data = [];
        foreach ($this->global_settings as $key => $section) {
            $sanitize_data[$key] = array_reduce($section['settings'], function($carry, $field) use ($settings, $key) {
                if (isset($settings[$key]) && isset($settings[$key][$field['id']])) {
                    if($field['type'] === 'input' || $field['type'] === 'password') {
                        $carry[$field['id']] = sanitize_text_field($settings[$key][$field['id']]);
                    } else if($field['type'] === 'switch') {
                        $carry[$field['id']] = (bool) $settings[$key][$field['id']];
                    } else if($field['type'] === 'textarea') {
                        $carry[$field['id']] = sanitize_textarea_field($settings[$key][$field['id']]);
                    } else if($field['type'] === 'select') {
                        $carry[$field['id']] = sanitize_text_field($settings[$key][$field['id']]);
                    } else if($field['type'] === 'radio' || $field['type'] === 'radio_card') {
                        $carry[$field['id']] = sanitize_text_field($settings[$key][$field['id']]);
                    }
                }
                return $carry;
            }, []);
        }

        // Whitelist validation for recaptcha_active_version
        if (isset($sanitize_data['captcha']['recaptcha_active_version'])) {
            $valid_versions = ['v2', 'v3'];
            if (!in_array($sanitize_data['captcha']['recaptcha_active_version'], $valid_versions, true)) {
                $sanitize_data['captcha']['recaptcha_active_version'] = 'v2';
            }
        }

        // Validate settings
        if (!is_array($settings)) {
            return new WP_Error(
                'invalid_settings',
                esc_html__('Settings must be an object.', 'ht-contactform'),
                ['status' => 400]
            );
        }
        
        // Update settings
        update_option(self::OPTION_NAME, $sanitize_data);
        
        // Clear any caches
        wp_cache_delete('ht_form_global_settings', 'options');

        return new WP_REST_Response($sanitize_data, 200);
    }

    /**
     * Verify captcha API keys
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object
     */
    public function verify_captcha($request) {
        $type = $request->get_param('type');
        $secret_key = sanitize_text_field($request->get_param('secret_key'));

        if (empty($secret_key)) {
            return new WP_Error(
                'missing_key',
                esc_html__('Secret key is required', 'ht-contactform'),
                ['status' => 400]
            );
        }

        // Determine API URL based on captcha type
        if ($type === 'recaptcha_v2' || $type === 'recaptcha_v3') {
            $url = 'https://www.google.com/recaptcha/api/siteverify';
        } elseif ($type === 'hcaptcha') {
            $url = 'https://hcaptcha.com/siteverify';
        } else {
            return new WP_Error(
                'invalid_type',
                esc_html__('Invalid captcha type', 'ht-contactform'),
                ['status' => 400]
            );
        }

        // Make test request to verify the secret key
        $response = wp_remote_post($url, [
            'body' => [
                'secret' => $secret_key,
                'response' => 'test-verification-request',
            ],
        ]);

        if (is_wp_error($response)) {
            return new WP_Error(
                'connection_failed',
                esc_html__('Failed to connect to captcha API', 'ht-contactform'),
                ['status' => 500]
            );
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $error_codes = $body['error-codes'] ?? [];

        // Check for invalid secret key error
        if (in_array('invalid-input-secret', $error_codes)) {
            return new WP_Error(
                'invalid_secret',
                esc_html__('Invalid secret key', 'ht-contactform'),
                ['status' => 400]
            );
        }

        // If we get here, secret key is valid (missing-input-response is expected)
        return new WP_REST_Response([
            'success' => true,
            'message' => esc_html__('Secret key is valid', 'ht-contactform'),
        ], 200);
    }
}