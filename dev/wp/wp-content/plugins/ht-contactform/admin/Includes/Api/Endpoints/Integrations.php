<?php

namespace HTContactFormAdmin\Includes\Api\Endpoints;

use HTContactFormAdmin\Includes\Config\Form as FormConfig;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\Mailchimp;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\ActiveCampaign;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\MailerLite;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\ConstantContact;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\GetResponse;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\Drip;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\Moosend;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\iContact;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\Trello;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\HubSpot;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\Notion;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\OnepageCRM;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\TwentyCRM;

use HTContactForm\Integrations\Insightly;
use HTContactForm\Integrations\Brevo;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Integrations API Handler Class
 * 
 * Handles all REST API endpoints for integrations management
 */
class Integrations {
    //-------------------------------------------------------------------------
    // PROPERTIES
    //-------------------------------------------------------------------------
    
    /** @var string REST API namespace */
    private const NAMESPACE = 'ht-form/v1';

    /** @var self|null Singleton instance */
    private static $instance = null;
    
    /** @var array Integrations settings */
    private $integrations_settings;
    /**
     * Option name for storing integrations settings
     */
    public const OPTION_NAME = 'ht_form_integrations';

    //-------------------------------------------------------------------------
    // INTEGRATIONS INSTANCES
    //-------------------------------------------------------------------------

    /** 
     * Insightly integration instance
     * 
     * @var Insightly
     */
    private $insightly = null;

    /** 
     * Brevo integration instance
     * 
     * @var Brevo
     */
    private $brevo = null;

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
        $this->integrations_settings = FormConfig::get_instance()->form_integrations();
        add_action('rest_api_init', [$this, 'register_routes']);

        $this->brevo = new Brevo();
        $this->insightly = new Insightly();
        
        // Initialize integration classes that need to register their own routes
        $this->init_integration_classes();
    }

    /**
     * Initialize integration classes that need to register their own routes
     */
    private function init_integration_classes() {
        // Get saved integrations
        $saved_integrations = get_option(self::OPTION_NAME, []);
        
        // Initialize Constant Contact if settings exist
        if (!empty($saved_integrations['constantcontact'])) {
            $cc_settings = $saved_integrations['constantcontact'];
            if (!empty($cc_settings['client_id']) && !empty($cc_settings['client_secret'])) {
                new ConstantContact($cc_settings['client_id'], $cc_settings['client_secret']);
            }
        }
    }

    /**
     * Register routes
     */
    public function register_routes() {
        $routes = [
            [
                'endpoint' => 'integrations',
                'methods'  => 'GET',
                'callback' => [$this, 'get_integrations'],
            ],
            [
                'endpoint' => 'integrations',
                'methods'  => 'PUT',
                'callback' => [$this, 'update_integrations'],
                'args'                => [
                    'settings' => [
                        'required' => true,
                        'type'     => 'object',
                    ],
                ],
            ],
            [
                'endpoint' => 'integrations/verify',
                'methods'  => 'POST',
                'callback' => [$this, 'verify_integration'],
                'args'                => [
                    'integration' => [
                        'required' => true,
                        'type'     => 'string',
                    ],
                    'settings' => [
                        'required' => true,
                        'type'     => 'object',
                    ],
                ],
            ],
            
            // Brevo Integration API Endpoints
            [
                'endpoint' => 'brevo/verify',
                'methods'  => 'POST',
                'callback' => [$this->brevo, 'verify'],
                'args'     => [
                    'api_key' => [
                        'required' => true,
                        'type'     => 'string',
                    ],
                ],
            ],
            [
                'endpoint' => 'brevo/get_lists',
                'methods'  => 'GET',
                'callback' => [$this->brevo, 'get_lists'],
            ],

            // Insightly Integration API Endpoints
            [
                'endpoint' => 'insightly/verify',
                'methods'  => 'POST',
                'callback' => [$this->insightly, 'verify'],
                'args'     => [
                    'api_key' => [
                        'required' => true,
                        'type'     => 'string',
                    ],
                    'api_url' => [
                        'required' => true,
                        'type'     => 'string',
                    ],
                ],
            ],
            [
                'endpoint' => 'insightly/get_fields',
                'methods'  => 'GET',
                'callback' => [$this->insightly, 'get_fields'],
                'args'     => [
                    'service' => [
                        'required' => true,
                        'type'     => 'string',
                    ],
                ],
            ],
        ];
        foreach ($routes as $route) {
            register_rest_route(self::NAMESPACE, $route['endpoint'], [
                'methods'             => $route['methods'],
                'callback'            => $route['callback'],
                'permission_callback' => [$this, 'permissions_check'],
                'args'                => $route['args'] ?? []
            ]);
        }
    }

    /**
     * Check if user has permission
     *
     * @param WP_REST_Request $request Request object
     * @return bool|WP_Error True if user has permission, WP_Error otherwise
     */
    public function permissions_check() {
        if (!current_user_can('manage_options')) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You do not have permission to manage integrations.', 'ht-contactform'),
                ['status' => 403]
            );
        }
        return true;
    }

    /**
     * Get integration settings
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function get_integrations($request) {
        $default = $this->get_default_integrations();
        $settings = get_option(self::OPTION_NAME, $default);
        return new WP_REST_Response($settings, 200);
    }

    /**
     * Update integrations settings
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object
     */
    public function update_integrations($request) {
        $settings = $request->get_param('settings');
        if (!$settings) {
            // Try to get from JSON body for backward compatibility
            $settings = $request->get_json_params();
        }
        
        $sanitize_data = $this->sanitize_integration_data($settings);
            
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
        wp_cache_delete('ht_form_integrations', 'options');
        
        return new WP_REST_Response($sanitize_data, 200);
    }

    /**
     * Verify integration settings
     *
     * @param WP_REST_Request $request Request object
     * @return WP_Error|WP_REST_Response
     */
    public function verify_integration($request) {
        $integration = $request->get_param('integration');
        $settings = $request->get_param('settings');
        
        // Validate settings
        if (!is_array($settings)) {
            return new WP_Error(
                'invalid_settings',
                esc_html__('Settings must be an object.', 'ht-contactform'),
                ['status' => 400]
            );
        }

        // Check if integration type is supported
        $supported_integrations = ['mailchimp', 'activecampaign', 'mailerlite', 'constantcontact', 'brevo', 'insightly', 'onepagecrm', 'getresponse', 'drip', 'moosend', 'icontact', 'mailpoet', 'notion', 'trello', 'hubspot', 'zohocrm', 'twentycrm'];
        if (!in_array($integration, $supported_integrations)) {
            return new WP_Error(
                'unsupported_integration',
                sprintf(esc_html__('Integration type "%s" is not supported.', 'ht-contactform'), $integration),
                ['status' => 400]
            );
        }

        $result = false;

        // Verify Mailchimp API key
        if ($integration === 'mailchimp') {
            if (empty($settings['api_key'])) {
                return new WP_Error(
                    'missing_api_key',
                    esc_html__('API key is required for Mailchimp integration.', 'ht-contactform'),
                    ['status' => 400]
                );
            }
            
            $api_key = $settings['api_key'];
            $mailchimp = Mailchimp::get_instance();
            $result = $mailchimp->verify($api_key);
            
            if (is_wp_error($result)) {
                return $result;
            }
        }

        // Verify ActiveCampaign API key & URL
        if ($integration === 'activecampaign') {
            $activecampaign = ActiveCampaign::get_instance();
            $result = $activecampaign->verify($settings['api_key'], $settings['api_url']);
            
            if (is_wp_error($result)) {
                return $result;
            }
        }

        // Verify MailerLite API key & URL
        if ($integration === 'mailerlite') {
            $mailerlite = MailerLite::get_instance();
            $result = $mailerlite->verify($settings['api_key']);
            
            if (is_wp_error($result)) {
                return $result;
            }
        }

        // Verify Constant Contact API key & URL
        if ($integration === 'constantcontact') {
            $constantcontact = new ConstantContact($settings['client_id'], $settings['client_secret']);
            $result = $constantcontact->verify();

            if (is_wp_error($result)) {
                return $result;
            }
        }

        // Verify OnepageCRM User ID & API key
        if ($integration === 'onepagecrm') {
            if (empty($settings['user_id']) || empty($settings['api_key'])) {
                return new WP_Error(
                    'missing_credentials',
                    esc_html__('User ID and API key are required for OnepageCRM integration.', 'ht-contactform'),
                    ['status' => 400]
                );
            }

            $onepagecrm = OnepageCRM::get_instance();
            $result = $onepagecrm->verify_credentials($settings['api_key'], $settings['user_id']);

            if (is_wp_error($result)) {
                return $result;
            }
        }

        // Verify GetResponse API key
        if ($integration === 'getresponse') {
            if (empty($settings['api_key'])) {
                return new WP_Error(
                    'missing_api_key',
                    esc_html__('API key is required for GetResponse integration.', 'ht-contactform'),
                    ['status' => 400]
                );
            }

            $getresponse = GetResponse::get_instance();
            $result = $getresponse->verify($settings['api_key']);

            if (is_wp_error($result)) {
                return $result;
            }
        }

        // Verify Drip API key
        if ($integration === 'drip') {
            if (empty($settings['api_key'])) {
                return new WP_Error(
                    'missing_api_key',
                    esc_html__('API key is required for Drip integration.', 'ht-contactform'),
                    ['status' => 400]
                );
            }

            $drip = Drip::get_instance();
            $result = $drip->verify($settings['api_key']);

            if (is_wp_error($result)) {
                return $result;
            }
        }

        // Verify Moosend API key
        if ($integration === 'moosend') {
            if (empty($settings['api_key'])) {
                return new WP_Error(
                    'missing_api_key',
                    esc_html__('API key is required for Moosend integration.', 'ht-contactform'),
                    ['status' => 400]
                );
            }

            $moosend = Moosend::get_instance();
            $result = $moosend->verify($settings['api_key']);

            if (is_wp_error($result)) {
                return $result;
            }
        }

        // Verify iContact credentials
        if ($integration === 'icontact') {
            if (empty($settings['app_id']) || empty($settings['username']) || empty($settings['password'])) {
                return new WP_Error(
                    'missing_credentials',
                    esc_html__('App ID, Username, and Password are required for iContact integration.', 'ht-contactform'),
                    ['status' => 400]
                );
            }

            $icontact = iContact::get_instance();
            $result = $icontact->verify([
                'app_id' => $settings['app_id'],
                'username' => $settings['username'],
                'password' => $settings['password'],
            ]);

            if (is_wp_error($result)) {
                return $result;
            }
        }

        // Verify Notion API key
        if ($integration === 'notion') {
            if (empty($settings['api_key'])) {
                return new WP_Error(
                    'missing_api_key',
                    esc_html__('API key is required for Notion integration.', 'ht-contactform'),
                    ['status' => 400]
                );
            }

            $notion = Notion::get_instance();
            $result = $notion->verify($settings['api_key']);

            if (is_wp_error($result)) {
                return $result;
            }
        }

        // Verify Trello API key and token
        if ($integration === 'trello') {
            if (empty($settings['api_key']) || empty($settings['api_token'])) {
                return new WP_Error(
                    'missing_credentials',
                    esc_html__('API key and API token are required for Trello integration.', 'ht-contactform'),
                    ['status' => 400]
                );
            }

            $trello = Trello::get_instance();
            $result = $trello->verify($settings['api_key'], $settings['api_token']);

            if (is_wp_error($result)) {
                return $result;
            }
        }

        // Verify HubSpot access token
        if ($integration === 'hubspot') {
            if (empty($settings['access_token'])) {
                return new WP_Error(
                    'missing_access_token',
                    esc_html__('Access token is required for HubSpot integration.', 'ht-contactform'),
                    ['status' => 400]
                );
            }

            $hubspot = HubSpot::get_instance();
            $result = $hubspot->verify($settings['access_token']);

            if (is_wp_error($result)) {
                return $result;
            }
        }

        // Verify Twenty CRM API key and instance URL
        if ($integration === 'twentycrm') {
            if (empty($settings['api_key']) || empty($settings['base_url'])) {
                return new WP_Error(
                    'missing_credentials',
                    esc_html__('API key and instance URL are required for Twenty CRM integration.', 'ht-contactform'),
                    ['status' => 400]
                );
            }

            $twentycrm = TwentyCRM::get_instance();
            $result = $twentycrm->verify([
                'api_key'  => $settings['api_key'],
                'base_url' => $settings['base_url'],
            ]);

            if (is_wp_error($result)) {
                return $result;
            }
        }

        // Zoho CRM uses OAuth - verification happens via OAuth callback
        if ($integration === 'zohocrm') {
            if (empty($settings['client_id']) || empty($settings['client_secret'])) {
                return new WP_Error(
                    'missing_credentials',
                    esc_html__('Client ID and Client Secret are required for Zoho CRM integration.', 'ht-contactform'),
                    ['status' => 400]
                );
            }

            $zohocrm = new \HTContactForm\Integrations\ZohoCRM(
                $settings['client_id'],
                $settings['client_secret'],
                $settings['data_center'] ?? 'com'
            );

            // If already connected, return success
            if ($zohocrm->is_connected()) {
                return new WP_REST_Response([
                    'success' => true,
                    'message' => __('Zoho CRM is connected.', 'ht-contactform'),
                ], 200);
            }

            // Not connected - return auth URL for OAuth flow
            return new WP_REST_Response([
                'success' => true,
                'message' => __('Redirecting to Zoho for authorization...', 'ht-contactform'),
                'data' => [
                    'auth_url' => $zohocrm->get_auth_url(),
                ],
            ], 200);
        }

        // Verify MailPoet - just check if plugin is active
        if ($integration === 'mailpoet') {
            // MailPoet doesn't need API verification - it's a local plugin
            // Just check if the plugin is active
            if (!class_exists('\\MailPoet\\API\\API')) {
                return new WP_Error(
                    'mailpoet_not_active',
                    esc_html__('MailPoet plugin is not installed or activated.', 'ht-contactform'),
                    ['status' => 400]
                );
            }

            return new WP_REST_Response([
                'success' => true,
                'message' => esc_html__('MailPoet is active and ready to use.', 'ht-contactform'),
            ], 200);
        }

        return new WP_REST_Response($result->get_data(), 200);
    }

    /**
     * Get default integration settings
     *
     * @return array Default integration settings
     */
    private function get_default_integrations() {
        return array_reduce($this->integrations_settings['settings'], function($carry, $integration) {
            $default_options = [];
            if(!empty($integration['options'])) {
                $default_options = array_reduce($integration['options'], function($carry, $option) {
                    $carry[$option['id']] = $option['value'];
                    return $carry;
                }, []);
            }
            $carry[$integration['id']] = array_merge(
                ['enabled' => $integration['value']],
                $default_options
            );
            
            return $carry;
        }, []);
    }

    /**
     * Sanitize integration data
     *
     * @param array $data Data to sanitize
     * @return array Sanitized data
     */
    private function sanitize_integration_data($data) {
        if (!is_array($data)) {
            return [];
        }

        $sanitized = [];
        
        foreach ($data as $key => $integration) {
            if (!is_array($integration)) {
                continue;
            }
            
            $sanitized[$key] = [];
            
            // Sanitize enabled status
            if (isset($integration['enabled'])) {
                $sanitized[$key]['enabled'] = (bool) $integration['enabled'];
            }

            $setting = current(array_filter($this->integrations_settings['settings'], function($setting) use ($key) {
                return $setting['id'] === $key;
            }));

            if(empty($setting['options'])) {
                continue;
            }
            
            // Sanitize other options
            foreach ($integration as $option_id => $value) {
                if ($option_id === 'enabled') {
                    continue;
                }

                $option = current(array_filter($setting['options'], function($option) use ($option_id) {
                    return $option['id'] === $option_id;
                }));
                $callback = $option['callback'];

                if(is_callable($callback)) {
                    $sanitized[$key][$option_id] = call_user_func($callback, $value);
                }
                
                switch ($callback) {
                    case 'number':
                        $sanitized[$key][$option_id] = is_numeric($value) ? (float) $value : 0;
                        break;
                        
                    case 'switch':
                    case 'checkbox':
                        $sanitized[$key][$option_id] = (bool) $value;
                        break;
                        
                    default:
                        $sanitized[$key][$option_id] = $value;
                        break;
                }
            }
        }
        
        return $sanitized;
    }
    
}