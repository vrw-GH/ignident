<?php

namespace HTContactFormAdmin\Includes\Services;

/**
 * Helper Class
 * 
 * Handles general helper functions for the plugin.
 */
class Helper {
    //-------------------------------------------------------------------------
    // PROPERTIES
    //-------------------------------------------------------------------------

    // Get user agent string
    private static $userAgent = null;

    /** @var self|null Singleton instance */
    private static $instance = null;

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
        self::$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
    }

    //-------------------------------------------------------------------------
    // GETTERS
    //-------------------------------------------------------------------------
    
    /**
     * Get user agent string
     * 
     * @return string User agent string
     */
    public static function get_user_agent() {
        return self::$userAgent;
    }

    /**
     * Refresh user agent from current request
     * 
     * @param \WP_REST_Request $request Optional REST request object
     * @return string Current user agent
     */
    public static function refresh_user_agent($request = null) {
        if ($request !== null) {
            self::$userAgent = $request->get_header('user-agent') ?? '';
        } else {
            self::$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
        }
        return self::$userAgent;
    }

    /**
     * Get browser name
     * 
     * @return string Browser name
     */
    public static function get_browser() {
        // Ensure we have the user agent
        if (empty(self::$userAgent)) {
            self::refresh_user_agent();
        }
        
        $browser = "Unknown";
        $browsers = [
            '/msie|trident/i'      => 'Internet Explorer',
            '/firefox/i'           => 'Firefox',
            '/edg/i'               => 'Edge',  // Modern Edge (Chromium-based)
            '/edge/i'              => 'Edge',  // Legacy Edge
            '/chrome/i'            => 'Chrome',
            '/safari/i'            => 'Safari',
            '/opera|OPR/i'         => 'Opera',
            '/netscape/i'          => 'Netscape',
            '/maxthon/i'           => 'Maxthon',
            '/konqueror/i'         => 'Konqueror',
            '/ucbrowser/i'         => 'UC Browser',
            '/mobile/i'            => 'Mobile Browser'
        ];
    
        foreach ($browsers as $regex => $value) {
            if (preg_match($regex, self::$userAgent)) {
                $browser = $value;
                break;
            }
        }
    
        return $browser;
    }

    /**
     * Get device type
     * 
     * @return string Device type
     */
    public static function get_device() {
        // Ensure we have the user agent
        if (empty(self::$userAgent)) {
            self::refresh_user_agent();
        }
        
        $device = "Unknown";
        
        $devices = [
            '/iphone/i'            => 'iPhone',
            '/ipad/i'              => 'iPad',
            '/android.*mobile/i'   => 'Android Mobile',
            '/android/i'           => 'Android', 
            '/blackberry/i'        => 'BlackBerry',
            '/webos/i'             => 'Mobile',
            '/ipod/i'              => 'iPod',
            '/windows phone/i'     => 'Windows Phone',
            '/windows nt 10/i'     => 'Windows 10',
            '/windows nt 6.3/i'    => 'Windows 8.1',
            '/windows nt 6.2/i'    => 'Windows 8',
            '/windows nt 6.1/i'    => 'Windows 7',
            '/windows nt 6.0/i'    => 'Windows Vista',
            '/windows nt 5.2/i'    => 'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'    => 'Windows XP',
            '/windows xp/i'        => 'Windows XP',
            '/windows/i'           => 'Windows',
            '/macintosh|mac os x/i' => 'macOS',
            '/mac_powerpc/i'       => 'Mac OS 9',
            '/linux/i'             => 'Linux',
            '/ubuntu/i'            => 'Ubuntu',
            '/ios/i'               => 'iOS',
            '/tablet/i'            => 'Tablet',
            '/mobile/i'            => 'Mobile'
        ];
        
        foreach ($devices as $regex => $value) {
            if (preg_match($regex, self::$userAgent)) {
                $device = $value;
                break;
            }
        }
        
        return $device;
    }

    /**
     * Get visitor's real IP address
     * 
     * Checks various proxy headers to determine the actual client IP address
     * 
     * @return string The visitor's IP address
     */
    public static function get_ip() {
        // REMOTE_ADDR is the only non-spoofable source (the actual TCP peer).
        $ip_address = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';

        // Proxy/CDN headers (X-Forwarded-For, CF-Connecting-IP, etc.) are
        // user-controllable and trivially spoofable. Only honor them when the
        // site owner explicitly opts in via this filter (i.e. they run behind a
        // trusted reverse proxy / Cloudflare that overwrites these headers).
        if (apply_filters('htcf_trust_proxy_headers', false)) {
            // Cloudflare sets a single, already-resolved client IP.
            if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
                $cf_ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_CF_CONNECTING_IP']));
                if (filter_var($cf_ip, FILTER_VALIDATE_IP)) {
                    $ip_address = $cf_ip;
                }
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                // First valid entry is the original client; later entries are proxies.
                $ip_list = explode(',', sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR'])));
                foreach ($ip_list as $forwarded_ip) {
                    $forwarded_ip = trim($forwarded_ip);
                    if (filter_var($forwarded_ip, FILTER_VALIDATE_IP)) {
                        $ip_address = $forwarded_ip;
                        break;
                    }
                }
            } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
                $real_ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_X_REAL_IP']));
                if (filter_var($real_ip, FILTER_VALIDATE_IP)) {
                    $ip_address = $real_ip;
                }
            }
        }

        // Local development: no public IP available, fall back to server's public IP.
        if ($ip_address === '127.0.0.1' || $ip_address === '::1' || $ip_address === '') {
            $remote = self::get_remote_ip();
            if ($remote) {
                return $remote;
            }
        }
        return $ip_address;
    }

    /**
     * Get remote IP address
     * 
     * @return string The remote IP address
     */
    public static function get_remote_ip() {
        $api_url = "https://api.ipify.org?format=json";
        $response = wp_remote_get($api_url, ['timeout' => 5]);
        if (is_wp_error($response)) {
            return false;
        }
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (!is_array($data) || empty($data['ip'])) {
            return false;
        }
        return $data['ip'];
    }

    /**
     * Get geolocation data
     * 
     * @param string $ip IP address
     * @param string $key Key to retrieve specific data
     * @return mixed Geolocation data or false on error
     */
    public static function get_geolocation_data($ip, $key = null) {
        if (empty($ip) || !filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        // Cache per IP to avoid a blocking external call on every page render
        // and to stay well within the geolocation provider's request quota.
        $cache_key = 'htcf_geo_' . md5($ip);
        $data = get_transient($cache_key);
        if ($data === false) {
            $api_url = "https://ipwho.is/{$ip}";
            $response = wp_remote_get($api_url, ['timeout' => 5]);
            if (is_wp_error($response)) {
                return false;
            }
            $data = json_decode(wp_remote_retrieve_body($response), true);
            if (!is_array($data) || empty($data['success'])) {
                return false;
            }
            // Normalize keys so callers can keep using ['country'] as the ISO2 code
            // (ipwho.is returns the ISO2 code in 'country_code', full name in 'country').
            $data['country'] = isset($data['country_code']) ? $data['country_code'] : '';
            set_transient($cache_key, $data, DAY_IN_SECONDS);
        }

        if($key) {
            return isset($data[$key]) ? $data[$key] : false;
        }
        return $data;
    }

    /**
     * Validate reCAPTCHA response
     *
     * @param string $recaptcha_response The reCAPTCHA response
     * @return array|bool {code: string, message: string, status: int}
     */
    public static function validate_recaptcha($recaptcha_response) {
        $global_settings = get_option('ht_form_global_settings', []);

        // Get active version and corresponding secret key
        $active_version = $global_settings['captcha']['recaptcha_active_version'] ?? '';

        if ($active_version === 'v2') {
            $secret_key = $global_settings['captcha']['recaptcha_v2_secret_key'] ?? '';
        } elseif ($active_version === 'v3') {
            $secret_key = $global_settings['captcha']['recaptcha_v3_secret_key'] ?? '';
        } else {
            return [
                'code' => 'recaptcha_not_configured',
                'message' => __('reCAPTCHA is not configured.', 'ht-contactform'),
                'status' => 400
            ];
        }

        if (empty($secret_key)) {
            return [
                'code' => 'recaptcha_not_configured',
                'message' => __('reCAPTCHA is not configured.', 'ht-contactform'),
                'status' => 400
            ];
        }

        $recaptcha_response = sanitize_text_field($recaptcha_response);

        // If response is empty, return error
        if (empty($recaptcha_response)) {
            return [
                'code' => 'recaptcha_required',
                'message' => __('Please complete the reCAPTCHA challenge.', 'ht-contactform'),
                'status' => 400
            ];
        }

        // Verify with Google's API
        $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
        $response = wp_remote_post($verify_url, [
            'body' => [
                'secret' => $secret_key,
                'response' => $recaptcha_response,
                'remoteip' => !empty($global_settings['miscellaneous']['disable_ip_logging']) ? '' : Helper::get_ip(),
            ],
        ]);

        // Check for errors in the request
        if (is_wp_error($response)) {
            return [
                'code' => 'recaptcha_connection_failed',
                'message' => __('Failed to connect to reCAPTCHA server.', 'ht-contactform'),
                'status' => 500
            ];
        }

        // Parse the response
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);

        // For v3, check the score
        if ($active_version === 'v3' &&
            (!isset($result['success']) || !$result['success'] ||
            (isset($result['score']) && $result['score'] < 0.5))) {
            return [
                'code' => 'recaptcha_failed',
                'message' => __('reCAPTCHA verification failed. Please try again.', 'ht-contactform'),
                'status' => 400
            ];
        }
        // For v2, just check success
        elseif ($active_version === 'v2' &&
            (!isset($result['success']) || !$result['success'])) {
            return [
                'code' => 'recaptcha_failed',
                'message' => __('reCAPTCHA verification failed. Please try again.', 'ht-contactform'),
                'status' => 400
            ];
        }

        return true;
    }

    /**
     * Validate hCaptcha response
     *
     * @param string $hcaptcha_response hCaptcha response token
     * @return array|bool {code: string, message: string, status: int}
     */
    public static function validate_hcaptcha($hcaptcha_response) {
        $global_settings = get_option('ht_form_global_settings', []);

        if (empty($global_settings['captcha']['hcaptcha_secret_key'])) {
            return [
                'code' => 'hcaptcha_not_configured',
                'message' => __('hCaptcha is not configured.', 'ht-contactform'),
                'status' => 400
            ];
        }

        $secret_key = $global_settings['captcha']['hcaptcha_secret_key'];
        $hcaptcha_response = sanitize_text_field($hcaptcha_response);

        if (empty($hcaptcha_response)) {
            return [
                'code' => 'hcaptcha_required',
                'message' => __('Please complete the hCaptcha challenge.', 'ht-contactform'),
                'status' => 400
            ];
        }

        // Verify with hCaptcha API
        $response = wp_remote_post('https://hcaptcha.com/siteverify', [
            'body' => [
                'secret' => $secret_key,
                'response' => $hcaptcha_response,
                'remoteip' => !empty($global_settings['miscellaneous']['disable_ip_logging']) ? '' : Helper::get_ip(),
            ],
        ]);

        if (is_wp_error($response)) {
            return [
                'code' => 'hcaptcha_connection_failed',
                'message' => __('Failed to connect to hCaptcha server.', 'ht-contactform'),
                'status' => 500
            ];
        }

        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);

        if (!isset($result['success']) || !$result['success']) {
            return [
                'code' => 'hcaptcha_failed',
                'message' => __('hCaptcha verification failed. Please try again.', 'ht-contactform'),
                'status' => 400
            ];
        }

        return true;
    }

    /**
     * Find the captcha field configured on a form
     *
     * @param array $fields Form fields
     * @return array|null {type: string, name: string} or null when the form has no captcha field
     */
    public static function get_form_captcha_field($fields) {
        if (empty($fields) || !is_array($fields)) {
            return null;
        }

        $defaults = [
            'recaptcha' => 'g-recaptcha-response',
            'hcaptcha'  => 'h-captcha-response',
        ];

        foreach ($fields as $field) {
            $type = is_array($field) ? ($field['type'] ?? '') : '';
            if (!isset($defaults[$type])) {
                continue;
            }

            $settings = (is_array($field) && !empty($field['settings']) && is_array($field['settings'])) ? $field['settings'] : [];

            return [
                'type' => $type,
                'name' => !empty($settings['name_attribute']) ? $settings['name_attribute'] : $defaults[$type],
            ];
        }

        return null;
    }

    /**
     * Verify the captcha for a form submission
     *
     * Whether a captcha is required is decided by the form's own field
     * configuration, never by what the request happens to contain. A request
     * that omits the token fails verification instead of skipping it.
     *
     * @param array $fields Form fields
     * @param array $data Submitted data
     * @return array|bool True when the submission may proceed, otherwise {code, message, status}
     */
    public static function verify_form_captcha($fields, $data) {
        $captcha_field = self::get_form_captcha_field($fields);

        // No captcha field on this form: nothing to verify.
        if (empty($captcha_field)) {
            return true;
        }

        $token = (is_array($data) && isset($data[$captcha_field['name']]) && is_string($data[$captcha_field['name']]))
            ? $data[$captcha_field['name']]
            : '';

        if ($captcha_field['type'] === 'hcaptcha') {
            return self::validate_hcaptcha($token);
        }

        return self::validate_recaptcha($token);
    }

    /**
     * Get body tags
     *
     * @return array Body tags
     */
    public function get_body_tags() {
        return [
            [
                'id' => 'admin_email',
                'label' => __('Admin Email', 'ht-contactform'),
                'value' => '{admin_email}',
                'group' => 'Others',
                'callback' => 'parse_wp'
            ],
            [
                'id' => 'form_id',
                'label' => __('Form ID', 'ht-contactform'),
                'value' => '{form_id}',
                'group' => 'Others',
                'callback' => 'parse_form'
            ],
            [
                'id' => 'form_title',
                'label' => __('Form Title', 'ht-contactform'),
                'value' => '{form_title}',
                'group' => 'Others',
                'callback' => 'parse_form'
            ],
            [
                'id' => 'post_title',
                'label' => __('Embedded Post/Page Title', 'ht-contactform'),
                'value' => '{post_title}',
                'group' => 'Others',
                'callback' => 'parse_post'
            ],
            [
                'id' => 'post_url',
                'label' => __('Embedded Post/Page URL', 'ht-contactform'),
                'value' => '{post_url}',
                'group' => 'Others',
                'callback' => 'parse_post'
            ],
            [
                'id' => 'post_id',
                'label' => __('Embedded Post/Page ID', 'ht-contactform'),
                'value' => '{post_id}',
                'group' => 'Others',
                'callback' => 'parse_post'
            ],
            [
                'id' => 'date',
                'label' => __('Date', 'ht-contactform'),
                'value' => '{date format="m/d/Y"}',
                'group' => 'Others',
                'callback' => 'parse_date'
            ],
            [
                'id' => 'query_var',
                'label' => __('Query String Variable', 'ht-contactform'),
                'value' => '{query_var key=""}',
                'group' => 'Others',
                'callback' => 'parse_query_var'
            ],
            [
                'id' => 'user_id',
                'label' => __('User ID', 'ht-contactform'),
                'value' => '{user_id}',
                'group' => 'Others',
                'callback' => 'parse_user'
            ],
            [
                'id' => 'user_display',
                'label' => __('User Display Name', 'ht-contactform'),
                'value' => '{user_display}',
                'group' => 'Others',
                'callback' => 'parse_user'
            ],
            [
                'id' => 'user_full_name',
                'label' => __('User Full Name', 'ht-contactform'),
                'value' => '{user_full_name}',
                'group' => 'Others',
                'callback' => 'parse_user'
            ],
            [
                'id' => 'user_first_name',
                'label' => __('User First Name', 'ht-contactform'),
                'value' => '{user_first_name}',
                'group' => 'Others',
                'callback' => 'parse_user'
            ],
            [
                'id' => 'user_last_name',
                'label' => __('User Last Name', 'ht-contactform'),
                'value' => '{user_last_name}',
                'group' => 'Others',
                'callback' => 'parse_user'
            ],
            [
                'id' => 'user_email',
                'label' => __('User Email', 'ht-contactform'),
                'value' => '{user_email}',
                'group' => 'Others',
                'callback' => 'parse_user'
            ],
            [
                'id' => 'user_meta',
                'label' => __('User Meta', 'ht-contactform'),
                'value' => '{user_meta key=""}',
                'group' => 'Others',
                'callback' => 'parse_user'
            ],
            [
                'id' => 'author_id',
                'label' => __('Author ID', 'ht-contactform'),
                'value' => '{author_id}',
                'group' => 'Others',
                'callback' => 'parse_author'
            ],
            [
                'id' => 'author_display',
                'label' => __('Author Name', 'ht-contactform'),
                'value' => '{author_display}',
                'group' => 'Others',
                'callback' => 'parse_author'
            ],
            [
                'id' => 'author_email',
                'label' => __('Author Email', 'ht-contactform'),
                'value' => '{author_email}',
                'group' => 'Others',
                'callback' => 'parse_author'
            ],
            [
                'id' => 'url_referer',
                'label' => __('Referrer URL', 'ht-contactform'),
                'value' => '{url_referer}',
                'group' => 'Others',
                'callback' => 'parse_url'
            ],
            [
                'id' => 'url_login',
                'label' => __('Login URL', 'ht-contactform'),
                'value' => '{url_login}',
                'group' => 'Others',
                'callback' => 'parse_url'
            ],
            [
                'id' => 'url_logout',
                'label' => __('Logout URL', 'ht-contactform'),
                'value' => '{url_logout}',
                'group' => 'Others',
                'callback' => 'parse_url'
            ],
            [
                'id' => 'url_register',
                'label' => __('Register URL', 'ht-contactform'),
                'value' => '{url_register}',
                'group' => 'Others',
                'callback' => 'parse_url'
            ],
            [
                'id' => 'url_lost_password',
                'label' => __('Lost Password URL', 'ht-contactform'),
                'value' => '{url_lost_password}',
                'group' => 'Others',
                'callback' => 'parse_url'
            ],
            [
                'id' => 'site_title',
                'label' => __('Site Title', 'ht-contactform'),
                'value' => '{site_title}',
                'group' => 'Others',
                'callback' => 'parse_wp'
            ],
            [
                'id' => 'site_url',
                'label' => __('Site URL', 'ht-contactform'),
                'value' => '{site_url}',
                'group' => 'Others',
                'callback' => 'parse_wp'
            ]
        ];
    }
    
    /**
     * Check if an array contains address field components
     *
     * @param array $data The data to check
     * @return bool True if it contains address components
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
     * Filter content for predefined variable like {admin_email}, {site_title}, {site_url}, {current_date}, {current_time}
     * 
     * @param string $data Content to filter
     * @param array $form_data Form data
     * @return string Filtered content
     */
    public function filter_vars($data, $form_data = [], $form = []) {
        $body_tags = $this->get_body_tags();
        // Process {input.field_name} patterns
        if(str_contains($data, '{input.')) {
            preg_match_all('/{input\.([^}]+?)(?:\.([^}]+))?}/', $data, $matches, PREG_SET_ORDER);
            foreach($matches as $match) {
                $placeholder = $match[0]; // Full match like {input.name}
                $field_name = $match[1]; // Captured group like "name"
                if(isset($match[2])) {
                    $field_sub_name = $match[2]; // Captured group like "first_name"
                }
                if(isset($form_data[$field_name])) {
                    $replacement = $form_data[$field_name];
                    if(is_array($replacement)) {
                        if(isset($field_sub_name)) {
                            $replacement = $replacement[$field_sub_name];
                        } elseif($this->is_name_field($replacement)) {
                            $replacement = implode(' ', array_filter($replacement));
                        } else {
                            $replacement = implode(', ', array_map('sanitize_text_field', $replacement));
                        }
                    } else {
                        $replacement = sanitize_text_field($replacement);
                    }
                    $data = str_replace($placeholder, $replacement, $data);
                }
            }
        }
        
        // Process other tags
        foreach($body_tags as $tag) {
            if(strpos($data, $tag['value']) !== false) {
                // Extract the tag pattern from the data string
                $pattern = $tag['value'];
                
                // Get the replacement value using the callback
                if(isset($tag['callback']) &&  str_contains($tag['callback'], 'form')) {
                    $replacement = call_user_func([$this, $tag['callback']], $pattern, $form);
                } else {
                    $replacement = call_user_func([$this, $tag['callback']], $pattern);
                }
                
                // Replace only the specific tag pattern in the data string
                $data = str_replace($pattern, $replacement, $data);
            }
        }
        
        return $data;
    }

    /**
     * Parse WordPress variables
     * 
     * @param string $value Value to parse
     * @return string The parsed value
     */
    private function parse_wp($value) {
        if($value === '{admin_email}') {
            return sanitize_email(get_option('admin_email'));
        }
        if($value === '{site_title}') {
            return sanitize_text_field(get_bloginfo('name'));
        }
        if($value === '{site_url}') {
            return sanitize_text_field(get_bloginfo('url'));
        }
        return $value;
    }

    /**
     * Parse URL variables
     * 
     * @param string $value Value to parse
     * @return string The parsed value
     */
    private function parse_url($value) {
        if($value === '{url_referer}' && isset($_SERVER['HTTP_REFERER'])) {
            return sanitize_text_field(wp_unslash($_SERVER['HTTP_REFERER']));
        }
        if($value === '{url_login}') {
            return sanitize_text_field(wp_login_url());
        }
        if($value === '{url_logout}') {
            return sanitize_text_field(wp_logout_url());
        }
        if($value === '{url_register}') {
            return sanitize_text_field(wp_registration_url());
        }
        if($value === '{url_lost_password}') {
            return sanitize_text_field(wp_lostpassword_url());
        }
        return $value;
    }

    /**
     * Parse form variables
     * 
     * @param string $value Value to parse
     * @param array $form Form data
     * @return string The parsed value
     */
    private function parse_form($value, $form = []) {
        if($value === '{form_title}') {
            return !empty($form['title']) ? sanitize_text_field($form['title']) : '';
        }
        if($value === '{form_id}') {
            return !empty($form['id']) ? sanitize_text_field($form['id']) : '';
        }
        return $value;
    }

    /**
     * Parse post variables
     * 
     * @param string $value Value to parse
     * @return string The parsed value
     */
    private function parse_post($value) {
        if($value === '{post_title}') {
            return sanitize_text_field(get_the_title());
        }
        if($value === '{post_url}') {
            return sanitize_text_field(get_permalink());
        }
        if($value === '{post_id}') {
            return sanitize_text_field(get_the_ID());
        }
        return $value;
    }

    /**
     * Parse user variables
     * 
     * @param string $value Value to parse
     * @return string The parsed value
     */
    private function parse_user($value) {
        $current_user = wp_get_current_user();
        
        if($value === '{user_id}') {
            return sanitize_text_field($current_user->ID);
        }
        if($value === '{user_email}') {
            return sanitize_email($current_user->user_email);
        }
        if($value === '{user_display}') {
            return sanitize_text_field($current_user->display_name);
        }
        if($value === '{user_first_name}') {
            return sanitize_text_field($current_user->first_name);
        }
        if($value === '{user_last_name}') {
            return sanitize_text_field($current_user->last_name);
        }
        if($value === '{user_full_name}') {
            return sanitize_text_field("{$current_user->first_name} {$current_user->last_name}");
        }
        if(str_contains($value,"user_meta")) {
            $meta_key = substr($value, 16, -2);
            return sanitize_text_field(get_user_meta($current_user->ID, $meta_key, true));
        }
        return $value;
    }

    /**
     * Parse author variables
     * 
     * @param string $value Value to parse
     * @return string The parsed value
     */
    private function parse_author($value) {
        $user_id = get_current_user_id();
        if($value === '{author_id}') {
            return sanitize_text_field(get_the_author_meta('ID', $user_id));
        }
        if($value === '{author_email}') {
            return sanitize_email(get_the_author_meta('user_email', $user_id));
        }
        if($value === '{author_display}') {
            return sanitize_text_field(get_the_author_meta('display_name', $user_id));
        }
        return $value;
    }

    /**
     * Parse date variables
     * 
     * @param string $value Value to parse
     * @return string The parsed value
     */
    private function parse_date($value) {
        if(str_contains($value, '{date')) {
            $format = substr($value, 14, -2);
            return sanitize_text_field(gmdate($format));
        }
        return $value;
    }

    /**
     * Parse query variables
     * 
     * @param string $value Value to parse
     * @return string The parsed value
     */
    private function parse_query_var($value) {
        if(str_contains($value,'{query_var')) {
            $key = substr($value, 16, -2);
            return sanitize_text_field(get_query_var($key));
        }
        return $value;
    }



    /**
     * Get form field by field name
     * 
     * @param array $fields Form fields
     * @param string $field_name Field name
     * @return string Field
     */
    public function get_field($fields, $field_name) {
        foreach ($fields as $field) {
            if ($field['type'] !== 'submit' && $field['settings']['name_attribute'] === $field_name) {
                return $field;
            }
        }
        return '';
    }

    /**
     * Get form field admin label by field name
     * 
     * @param array $fields Form fields
     * @param string $field_name Field name
     * @return string Field admin label
     */
    public function get_field_admin_label($fields, $field_name) {
        foreach ($fields as $field) {
            if ($field['type'] !== 'submit' && $field['settings']['name_attribute'] === $field_name) {
                return $field['settings']['admin_label'];
            }
        }
        return '';
    }

    /**
     * Get form field type by field name
     * 
     * @param array $fields Form fields
     * @param string $field_name Field name
     * @return string Field type
     */
    public function get_field_type($fields, $field_name) {
        foreach ($fields as $field) {
            if ($field['type'] !== 'submit' && $field['settings']['name_attribute'] === $field_name) {
                return $field['type'];
            }
        }
        return '';
    }

    /**
     * Format array value based on field type for integrations
     * 
     * @param array $value Field value
     * @param string $field_type Field type
     * @return string Formatted value
     */
    public function format_array_value($value, $field_type) {
        if ($field_type === 'name' || $field_type === 'address') {
            return implode(' ', $value);
        }
        return implode(', ', $value);
    }

    /**
     * Format field value based on field type for integrations
     * 
     * @param mixed $value Field value
     * @param string $field_type Field type
     * @return string Formatted value
     */
    public function format_field_value($value, $field_type) {
        if ($field_type === 'textarea') {
            return nl2br(esc_html($value));
        }
        return wp_kses_post($value);
    }

}