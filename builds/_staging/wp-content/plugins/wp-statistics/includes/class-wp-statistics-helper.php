<?php

namespace WP_STATISTICS;

use Exception;
use WP_STATISTICS;
use WP_Statistics\Service\Integrations\WpConsentApi;
use WP_Statistics\Utils\Request;
use WP_Statistics\Utils\Signature;
use WP_Statistics_Mail;

class Helper
{
    /**
     * WP Statistics WordPress Log
     *
     * @param $function
     * @param $message
     * @param $version
     */
    public static function doing_it_wrong($function, $message, $version = '')
    {
        if (empty($version)) {
            $version = WP_STATISTICS_VERSION;
        }
        $message .= ' Backtrace: ' . wp_debug_backtrace_summary();
        if (is_ajax()) {
            do_action('doing_it_wrong_run', $function, $message, $version);
            error_log("{$function} was called incorrectly. {$message}. This message was added in version {$version}.");
        } else {
            _doing_it_wrong($function, $message, $version); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    }

    /**
     * Returns an array of site id's
     *
     * @return array
     */
    public static function get_wp_sites_list()
    {
        $site_list = array();
        $sites     = get_sites();
        foreach ($sites as $site) {
            $site_list[] = $site->blog_id;
        }
        return $site_list;
    }

    /**
     * What type of request is this?
     *
     * @param string $type admin, ajax, cron or frontend.
     * @return bool
     */
    public static function is_request($type)
    {
        switch ($type) {
            case 'admin':
                return is_admin();
            case 'ajax':
                return defined('DOING_AJAX');
            case 'cron':
                return defined('DOING_CRON');
            case 'wp-cli':
                return defined('WP_CLI') && WP_CLI;
            case 'frontend':
                return (!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON') && !self::is_rest_request();
        }
    }

    /**
     * Returns true if the request is a non-legacy REST API request.
     *
     * @return bool
     */
    public static function is_rest_request()
    {
        if (empty($_SERVER['REQUEST_URI'])) {
            return false;
        }

        // Backward-Compatibility with Bypass Ad Blockers option
        if (self::isBypassAdBlockersRequest()) {
            return true;
        }

        $rest_prefix = trailingslashit(rest_get_url_prefix());
        return (false !== strpos($_SERVER['REQUEST_URI'], $rest_prefix)) or isset($_REQUEST['rest_route']);
    }

    /**
     * Returns true if the request belongs to "Bypass Ad Blockers" feature.
     *
     * @return  bool
     */
    public static function isBypassAdBlockersRequest()
    {
        return (Request::compare('action', 'wp_statistics_hit') || Request::compare('action', 'wp_statistics_online'));
    }

    /**
     * Check is Login Page
     *
     * @return bool
     */
    public static function is_login_page()
    {
        // Check From global WordPress
        if (isset($GLOBALS['pagenow']) and in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))) {
            return true;
        }

        if (defined('WP_CLI') && WP_CLI) {
            return false;
        }

        // Backward compatibility
        if (empty($_SERVER['SERVER_PROTOCOL']) or empty($_SERVER['HTTP_HOST'])) {
            return false;
        }

        // Check Native php
        $protocol   = strpos(strtolower($_SERVER['SERVER_PROTOCOL']), 'https') === false ? 'http' : 'https';
        $host       = sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST']));
        $script     = sanitize_text_field(wp_unslash($_SERVER['SCRIPT_NAME']));
        $currentURL = $protocol . '://' . $host . $script;
        $loginURL   = wp_login_url();

        if ($currentURL == $loginURL) {
            return true;
        }

        return false;
    }

    /**
     * Get Screen ID
     *
     * @return string
     */
    public static function get_screen_id()
    {
        $screen    = get_current_screen();
        $screen_id = $screen ? $screen->id : '';
        return $screen_id;
    }

    /**
     * Get File Path Of Plugins File
     *
     * @param $path
     * @return string
     */
    public static function get_file_path($path)
    {
        return wp_normalize_path(path_join(WP_STATISTICS_DIR, $path));
    }

    /**
     * Determine if a Cache Plugin is Active
     *
     * @return array
     */
    public static function checkActiveCachePlugin()
    {
        $use = array('status' => false, 'plugin' => '');

        // TODO: Optimize this function
        /* WordPress core */
        if (defined('WP_CACHE') && WP_CACHE) {
            $use = array('status' => true, 'plugin' => 'core');
        }

        /* WP Rocket */
        if (function_exists('get_rocket_cdn_url')) {
            $use = array('status' => true, 'plugin' => 'WP Rocket');
        }

        /* WP Super Cache */
        if (function_exists('wpsc_init')) {
            $use = array('status' => true, 'plugin' => 'WP Super Cache');
        }

        /* Comet Cache */
        if (function_exists('___wp_php_rv_initialize')) {
            $use = array('status' => true, 'plugin' => 'Comet Cache');
        }

        /* WP Fastest Cache */
        if (class_exists('WpFastestCache')) {
            $use = array('status' => true, 'plugin' => 'WP Fastest Cache');
        }

        /* Cache Enabler */
        if (defined('CE_MIN_WP')) {
            $use = array('status' => true, 'plugin' => 'Cache Enabler');
        }

        /* W3 Total Cache */
        if (defined('W3TC')) {
            $use = array('status' => true, 'plugin' => 'W3 Total Cache');
        }

        /* WP-Optimize */
        if (class_exists('WP_Optimize')) {
            $use = array('status' => true, 'plugin' => 'WP-Optimize');
        }

        return apply_filters('wp_statistics_cache_status', $use);
    }

    /**
     * Get WordPress Uploads DIR
     *
     * @param string $path
     * @return mixed
     * @default For WP Statistics Plugin is 'wp-statistics' dir
     */
    public static function get_uploads_dir($path = '')
    {
        $upload_dir = wp_upload_dir();
        return wp_normalize_path(path_join($upload_dir['basedir'], $path));
    }

    /**
     * Get Robots List
     *
     * @param string $type
     * @return array|bool|string
     */
    public static function get_robots_list($type = 'list')
    {
        # Set Default
        $list = array();

        # Load From file
        include WP_STATISTICS_DIR . "includes/defines/robots-list.php";
        if (isset($wps_robots_list_array)) {
            $list = $wps_robots_list_array;
        }

        return ($type == "array" ? $list : implode("\n", $list));
    }

    /**
     * Get URL Query Parameters List
     *
     * @param string $type
     * @return array|bool|string
     */
    public static function get_query_params_allow_list($type = 'array')
    {
        # Set Default
        $list = [];

        if (Option::get('query_params_allow_list') !== false) {
            # Load from options
            $list = array_map('trim', explode("\n", Option::get('query_params_allow_list')));
        } else {
            # Load the default options
            $list = self::get_default_query_params_allow_list();
        }

        return ($type == "array" ? $list : implode("\n", $list));
    }


    /**
     * Get the default URL Query Parameters List
     * @param string $type
     * @return array|string
     */
    public static function get_default_query_params_allow_list($type = 'array')
    {
        include WP_STATISTICS_DIR . "includes/defines/query-params-allow-list.php";
        $list = isset($wps_query_params_allow_list_array) ? $wps_query_params_allow_list_array : [];
        return ($type == "array" ? $list : implode("\n", $list));
    }

    /**
     * Get Number Days From install this plugin
     * this method used for `ALL` Option in Time Range Pages
     */
    public static function get_date_install_plugin()
    {
        global $wpdb;

        //Create Empty default Option
        $first_day = '';

        //First Check Visitor Table , if not exist Web check Pages Table
        $list_tbl = array(
            'visitor' => array('order_by' => 'ID', 'column' => 'last_counter'),
            'pages'   => array('order_by' => 'page_id', 'column' => 'date'),
        );
        foreach ($list_tbl as $tbl => $val) {
            $first_day = $wpdb->get_var(
                $wpdb->prepare("SELECT %s FROM `" . WP_STATISTICS\DB::table($tbl) . "` ORDER BY %s ASC LIMIT 1", $val['column'], $val['order_by'])
            );
            if (!empty($first_day)) {
                break;
            }
        }

        //Calculate hit day if range is exist
        if (empty($first_day)) {
            return false;
        } else {
            return $first_day;
        }
    }

    /**
     * Check User Is Using Gutenberg Editor
     */
    public static function is_gutenberg()
    {
        $current_screen = get_current_screen();
        return ((method_exists($current_screen, 'is_block_editor') && $current_screen->is_block_editor()) || (function_exists('is_gutenberg_page')) && is_gutenberg_page());
    }

    /**
     * Get List WordPress Post Type
     *
     * @return array
     */
    public static function get_list_post_type()
    {
        // Get default post types which are public (exclude media post type)
        $post_types = get_post_types(array('public' => true, '_builtin' => true), 'names', 'and');
        $post_types = array_diff($post_types, ['attachment']);

        // Get custom post types which are public
        $custom_post_types = get_post_types(array('public' => true, '_builtin' => false), 'names', 'and');

        foreach ($custom_post_types as $name) {
            $post_types[] = $name;
        }

        return $post_types;
    }

    /**
     * Get Built-in Post Types List
     */
    public static function getDefaultPostTypes()
    {
        $postTypes = get_post_types(array('public' => true, '_builtin' => true), 'names', 'and');
        $postTypes = array_diff($postTypes, ['attachment']);;

        return array_values($postTypes);
    }

    /**
     * Get Custom Post Types List
     */
    public static function getCustomPostTypes()
    {
        return array_values(get_post_types(array('public' => true, '_builtin' => false), 'names', 'and'));
    }

    /**
     * Get all Post Types (built-in and custom)
     *
     * @return array
     */
    public static function getPostTypes()
    {
        return array_merge(self::getDefaultPostTypes(), self::getCustomPostTypes());
    }

    public static function get_updated_list_post_type()
    {
        return array_map(function ($postType) {
            return in_array($postType, ['post', 'page', 'product', 'attachment']) ? $postType : 'post_type_' . $postType;
        }, self::get_list_post_type());
    }

    /**
     * Check Url Scheme
     *
     * @param $url
     * @param array $accept
     * @return bool
     */
    public static function check_url_scheme($url, $accept = array('http', 'https'))
    {
        $scheme = @wp_parse_url($url, PHP_URL_SCHEME);
        return in_array($scheme, $accept);
    }

    /**
     * Get WordPress Version
     *
     * @return mixed|string
     */
    public static function get_wordpress_version()
    {
        return get_bloginfo('version');
    }

    /**
     * Convert Json To Array
     *
     * @param $json
     * @return bool|mixed
     */
    public static function json_to_array($json)
    {

        // Sanitize Slash Data
        $data = wp_unslash($json);

        // Check Validate Json Data
        if (!empty($data) && is_string($data) && is_array(json_decode($data, true)) && json_last_error() == 0) {
            return json_decode($data, true);
        }

        return false;
    }

    /**
     * Standard Json Encode
     *
     * @param $array
     * @return false|string
     */
    public static function standard_json_encode($array)
    {

        //Fixed entity decode Html
        foreach ((array)$array as $key => $value) {
            if (!is_scalar($value)) {
                continue;
            }
            $array[$key] = html_entity_decode((string)$value, ENT_QUOTES, 'UTF-8');
        }

        return wp_json_encode($array, JSON_UNESCAPED_SLASHES);
    }

    /**
     * Show Site Icon by Url
     *
     * @param $url
     * @param int $size
     * @param string $style
     * @return bool|string
     */
    public static function show_site_icon($url, $size = 16, $style = '')
    {
        $url = preg_replace('/^https?:\/\//', '', $url);
        if ($url != "") {
            $img_url = "https://www.google.com/s2/favicons?domain=" . $url;
            return '<img src="' . $img_url . '" width="' . $size . '" height="' . $size . '" style="' . ($style == "" ? 'vertical-align: -3px;' : '') . '" />';
        }

        return false;
    }

    /**
     * Get Domain name from url
     * e.g : https://wp-statistics.com/add-ons/ -> wp-statistics.com
     *
     * @param $url
     * @return mixed
     */
    public static function get_domain_name($url)
    {
        //Remove protocol
        $url = preg_replace("(^https?://)", "", trim($url));
        //remove w(3)
        $url = preg_replace('#^(http(s)?://)?w{3}\.#', '$1', $url);
        //remove all Query
        $url = explode("/", $url);

        return $url[0];
    }

    /**
     * Get Site title By Url
     *
     * @param $url string e.g : wp-statistics.com
     * @return bool|string
     */
    public static function get_site_title_by_url($url)
    {

        //Get Body Page
        $html = Helper::get_html_page($url);
        if ($html === false) {
            return false;
        }

        //Get Page Title
        if (class_exists('DOMDocument')) {
            $dom            = new \DOMDocument;
            $internalErrors = libxml_use_internal_errors(true);
            @$dom->loadHTML($html);
            libxml_use_internal_errors($internalErrors);
            $title = '';
            if (isset($dom) and $dom->getElementsByTagName('title')->length > 0) {
                $title = $dom->getElementsByTagName('title')->item('0')->nodeValue;
            }
            return (wp_strip_all_tags($title) == "" ? false : wp_strip_all_tags($title));
        }

        return false;
    }

    /**
     * Get Html Body Page By Url
     *
     * @param $url string e.g : wp-statistics.com
     * @return bool
     */
    public static function get_html_page($url)
    {

        //sanitize Url
        $parse_url = wp_parse_url($url);
        $urls[]    = esc_url_raw($url);

        //Check Protocol Url
        if (!array_key_exists('scheme', $parse_url)) {
            $urls      = array();
            $url_parse = wp_parse_url($url);
            foreach (array('http://', 'https://') as $scheme) {
                $urls[] = preg_replace('/([^:])(\/{2,})/', '$1/', $scheme . path_join((isset($url_parse['host']) ? $url_parse['host'] : ''), (isset($url_parse['path']) ? $url_parse['path'] : '')));
            }
        }

        //Send Request for Get Page Html
        foreach ($urls as $page) {
            $response = wp_remote_get($page, array(
                'timeout'    => 30,
                'user-agent' => "Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.71 Safari/537.36"
            ));
            if (is_wp_error($response)) {
                continue;
            }
            $data = wp_remote_retrieve_body($response);
            if (is_wp_error($data)) {
                continue;
            }
            return (wp_strip_all_tags($data) == "" ? false : $data);
        }

        return false;
    }

    /**
     * Generate Random String
     *
     * @param $num
     * @return string
     */
    public static function random_string($num = 50)
    {
        $characters   = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $num; $i++) {
            $randomString .= $characters[wp_rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }

    /**
     * Get Post List From custom Post Type
     *
     * @param array $args
     * @area utility
     * @return mixed
     */
    public static function get_post_list($args = array())
    {

        //Prepare Arg
        $defaults = array(
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => '-1',
            'order'          => 'ASC',
            'fields'         => 'ids'
        );
        $args     = wp_parse_args($args, $defaults);

        //Get Post List
        $query = new \WP_Query($args);
        $list  = array();
        foreach ($query->posts as $ID) {
            $list[$ID] = esc_html(get_the_title($ID));
        }

        return $list;
    }

    /**
     * Check WordPress Post is Published
     *
     * @param $ID
     * @return bool
     */
    public static function IsPostPublished($ID)
    {
        return get_post_status($ID) == 'public';
    }

    /**
     * Generate RGBA colors
     *
     * @param        $num
     * @param string $opacity
     * @param bool $quote
     * @return string
     */
    public static function GenerateRgbaColor($num, $opacity = '1', $quote = true)
    {
        $hash   = md5('color' . $num);
        $rgba   = "rgba(%s, %s, %s, %s)";
        $format = ($quote === true ? "'$rgba'" : $rgba);

        return sprintf($format,
            hexdec(substr($hash, 0, 2)),
            hexdec(substr($hash, 2, 2)),
            hexdec(substr($hash, 4, 2)),
            $opacity
        );
    }

    /**
     * Remove Query String From Url
     *
     * @param $url
     * @return bool|string
     */
    public static function RemoveQueryStringUrl($url)
    {
        return substr($url, 0, strrpos($url, "?"));
    }

    /**
     *
     * Filter certain query string in the URL based on Query Params Allowed List
     * @param string $url
     * @param array $allowedParams
     * @return string
     */
    public static function FilterQueryStringUrl($url, $allowedParams)
    {
        // Get query from the URL
        $urlQuery = strpos($url, '?');

        // Check if the URL has query strings
        if ($urlQuery !== false) {
            global $wp;
            $internalQueryParams = $wp->public_query_vars;
            $permalinkStructure  = get_option('permalink_structure');

            // Extract the URL path and query string
            $urlPath     = substr($url, 0, $urlQuery);
            $queryString = substr($url, $urlQuery + 1);

            // Parse the query string into an array
            parse_str($queryString, $parsedQuery);

            // Get the first query param key
            reset($parsedQuery);
            $firstKey = key($parsedQuery);

            // Loop through query params and unset ones not allowed, except the first one
            foreach ($parsedQuery as $key => $value) {
                $allowedQueryVars = $allowedParams;

                // If ugly permalink is enabled, ignore the first key if it's internal
                if (empty($permalinkStructure) && $key === $firstKey) {
                    $allowedQueryVars = array_merge($internalQueryParams, $allowedParams);
                }

                if (!in_array($key, $allowedQueryVars)) {
                    unset($parsedQuery[$key]);
                }
            }

            // Rebuild URL with allowed params, keeping the first query param
            if (!empty($parsedQuery)) {
                $filteredQuery = http_build_query($parsedQuery);
                $url           = $urlPath . '?' . $filteredQuery;
            } else {
                $url = $urlPath;
            }
        }

        return $url;
    }

    /**
     * Sort associative array
     *
     * @param $array
     * @param $subfield
     * @param int $type
     * @return void
     * @see https://stackoverflow.com/questions/1597736/how-to-sort-an-array-of-associative-arrays-by-value-of-a-given-key-in-php
     */
    public static function SortByKeyValue(&$array, $subfield, $type = SORT_DESC)
    {
        $sort_array = array();
        foreach ($array as $key => $row) {
            $sort_array[$key] = $row[$subfield];
        }
        array_multisort($sort_array, $type, $array);
    }

    /**
     * Format array for the datepicker
     *
     * @param $array_to_strip
     * @return array
     */
    public static function strip_array_indices($array_to_strip)
    {
        $NewArray = array();
        foreach ($array_to_strip as $objArrayItem) {
            $NewArray[] = $objArrayItem;
        }

        return ($NewArray);
    }

    /**
     * Set All Option For DatePicker
     *
     * @example add_filter( 'wp_statistics_days_ago_request', array( '', 'set_all_option_datepicker' ) );
     */
    public static function set_all_option_datepicker()
    {
        $first_day = Helper::get_date_install_plugin();
        return ($first_day === false ? 30 : (int)TimeZone::getNumberDayBetween($first_day));
    }

    /**
     * Url Decode
     *
     * @param $value
     * @return string
     */
    public static function getUrlDecode($value)
    {
        return mb_convert_encoding(urldecode($value), 'ISO-8859-1', 'UTF-8');
    }

    /**
     * Check is Assoc Array
     *
     * @param array $arr
     * @return bool
     */
    public static function isAssoc(array $arr)
    {
        if (array() === $arr) {
            return false;
        }
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Create Condition SQL
     *
     * @param array $args
     * @return string
     */
    public static function getConditionSQL($args = array())
    {

        // Create Empty SQL
        $sql = '';

        // Check Number Params
        if (self::isAssoc($args)) {
            $condition[] = $args;
        } else {
            $condition = $args;
        }

        // Add WHERE
        if (count($condition) > 0) {
            $sql .= ' WHERE ';
        }

        // Push To SQL
        $i = 0;
        foreach ($condition as $params) {
            if ($i > 0) {
                $sql .= ' AND ';
            }
            if ($params['compare'] == "BETWEEN") {
                $sql .= $params['key'] . " " . $params['compare'] . " " . (is_numeric($params['from']) ? $params['from'] : "'" . $params['from'] . "'") . " AND " . (is_numeric($params['to']) ? $params['to'] : "'" . $params['to'] . "'");
            } else {
                $sql .= $params['key'] . " " . $params['compare'] . " " . (is_numeric($params['value']) ? $params['value'] : "'" . $params['value'] . "'");
            }
            $i++;
        }

        return $sql;
    }

    /**
     * Send Email
     *
     * @param $to
     * @param $subject
     * @param $content
     * @param bool $email_template
     * @param array $args
     * @return bool
     */
    public static function send_mail($to, $subject, $content, $email_template = true, $args = array())
    {
        // Email Template
        if ($email_template) {
            $email_template = WP_STATISTICS_DIR . 'includes/admin/templates/emails/layout.php';
            $email_template = apply_filters('wp_statistics_email_template_layout', $email_template);
            $email_template = wp_normalize_path($email_template);
        }

        $schedule = Option::get('time_report', false);
        if (is_plugin_active('wp-statistics-advanced-reporting/wp-statistics-advanced-reporting.php')) {
            $emailTitle = __('<span style="font-family: \'Roboto\', Arial, Helvetica, sans-serif; text-align: left;font-size: 21px; font-weight: 500; line-height: 24.61px; color: #0C0C0D;">Your Website Performance Overview</span>', 'wp-statistics');
        } else {
            $emailTitle = sprintf(
            // translators: %1$s: Website URL.
                __('<span style="font-family: \'Roboto\', Arial, Helvetica, sans-serif; text-align: center;font-size: 16px; font-style: italic; font-weight: 400; line-height: 18.75px; color: #5E5E64;">Sent from </span><a style="color: #175DA4;text-decoration: underline" href="https://%1$s">%1$s</a>', 'wp-statistics'),
                wp_parse_url(get_site_url(), PHP_URL_HOST)
            );
        }

        if ($schedule && array_key_exists($schedule, Schedule::getSchedules())) {
            $schedule   = Schedule::getSchedules()[$schedule];
            $emailTitle .= is_plugin_active('wp-statistics-advanced-reporting/wp-statistics-advanced-reporting.php') ? '' : sprintf(__('<p style="margin-bottom:16px;margin-top:8px;padding:0;font-family: \'Roboto\',Arial, Helvetica, sans-serif; font-size: 16px; font-style: italic; font-weight: 500; line-height: 18.75px; text-align: center;"><small style="color:#5E5E64;font-family: \'Roboto\',Arial, Helvetica, sans-serif; font-size: 16px; font-style: italic; font-weight: 500; line-height: 18.75px; text-align: center">Report Date Range:</small> %s to %s</p>', 'wp-statistics'), $schedule['start'], $schedule['end']);
        }

        //Template Arg
        $template_arg = array(
            'title'        => $subject,
            'logo'         => '',
            'content'      => $content,
            'site_url'     => home_url(),
            'site_title'   => get_bloginfo('name'),
            'footer_text'  => '',
            'email_title'  => apply_filters('wp_statistics_email_title', $emailTitle),
            'logo_image'   => apply_filters('wp_statistics_email_logo', WP_STATISTICS_URL . 'assets/images/logo-statistics-header-blue.png'),
            'logo_url'     => apply_filters('wp_statistics_email_logo_url', get_bloginfo('url')),
            'copyright'    => apply_filters('wp_statistics_email_footer_copyright', Admin_Template::get_template('emails/copyright', array(), true)),
            'email_header' => apply_filters('wp_statistics_email_header', ""),
            'email_footer' => apply_filters('wp_statistics_email_footer', ""),
            'is_rtl'       => (is_rtl() ? true : false)
        );
        $arg          = wp_parse_args($args, $template_arg);

        /**
         * Send Email
         */
        try {

            WP_Statistics_Mail::init()
                ->setTo($to)
                ->setSubject($subject)
                ->setBody($content)
                ->setTemplate($email_template, $arg)
                ->send();

            return true;

        } catch (Exception $e) {
            \WP_Statistics::log($e->getMessage());

            return false;
        }
    }

    /**
     * Send SMS With WP SMS Plugin
     *
     * @param $to
     * @param $text
     * @return bool
     */
    public static function send_sms($to, $text)
    {
        if (function_exists('wp_sms_send')) {
            $run = wp_sms_send($to, $text);
            return (is_wp_error($run) ? false : true);
        }

        return false;
    }

    /**
     * Get List Taxonomy
     *
     * @param bool $hide_empty
     * @return array
     */
    public static function get_list_taxonomy($hide_empty = false)
    {
        $taxonomies = array('category' => __("Category", "wp-statistics"), "post_tag" => __("Tags", "wp-statistics"));
        $get_tax    = get_taxonomies(array('public' => true, '_builtin' => false), 'objects', 'and');
        foreach ($get_tax as $object) {
            $object = get_object_vars($object);
            if ($hide_empty === true) {
                $count_term_in_tax = wp_count_terms($object['name']);
                if ($count_term_in_tax > 0 and isset($object['rewrite']['slug'])) {
                    $taxonomies[$object['name']] = $object['labels']->name;
                }
            } else {
                if (isset($object['rewrite']['slug'])) {
                    $taxonomies[$object['name']] = $object['labels']->name;
                }
            }
        }

        return $taxonomies;
    }

    /**
     * Checks if the given taxonomy is a custom taxonomy.
     *
     * @param string $taxonomy The taxonomy name to check.
     * @return bool True if the taxonomy is custom, false otherwise.
     */
    public static function isCustomTaxonomy($taxonomy)
    {
        $taxonomy = get_taxonomy($taxonomy);

        if (!empty($taxonomy)) {
            return !$taxonomy->_builtin;
        }

        return false;
    }


    /**
     * Checks if the given taxonomy is a custom taxonomy.
     *
     * @param string $taxonomy The taxonomy name to check.
     * @return bool True if the taxonomy is custom, false otherwise.
     */
    public static function isCustomPostType($postType)
    {
        $customPostTypes = self::getCustomPostTypes();
        return in_array($postType, $customPostTypes) ? true : false;
    }

    /**
     * Retrieves an array of post types associated with a given taxonomy.
     *
     * @param string $taxonomy The taxonomy to search for.
     * @return array An array of post types associated with the given taxonomy.
     */
    public static function getPostTypesByTaxonomy($taxonomy)
    {
        $taxonomyPostTypes = [];
        $postTypes         = self::getPostTypes();

        foreach ($postTypes as $postType) {
            $taxonomies = get_object_taxonomies($postType);

            if (in_array($taxonomy, $taxonomies)) {
                $taxonomyPostTypes[] = $postType;
            }
        }

        return $taxonomyPostTypes;
    }

    /**
     * Create Condition Where Time in MySql
     *
     * @param string $field : date column name in database table
     * @param string $time : Time return
     * @param array $range : an array contain two Date e.g : array('start' => 'xx-xx-xx', 'end' => 'xx-xx-xx', 'is_day' => true, 'current_date' => true)
     *
     * ---- Time Range -----
     * today
     * yesterday
     * week
     * month
     * year
     * total
     * “-x” (i.e., “-10” for the past 10 days)
     * ----------------------
     *
     * @return string|bool
     *
     * @todo Make the return values for "month", "last-month" and "2-months-ago" more dynamic (29, 30 or 31 depending on the current month).
     */
    public static function mysql_time_conditions($field = 'date', $time = 'total', $range = array())
    {
        //Get Current Date From WP
        $current_date = TimeZone::getCurrentDate('Y-m-d');

        //Create Field Sql
        $field_sql = function ($time) use ($current_date, $field, $range) {
            $is_current     = array_key_exists('current_date', $range);
            $getCurrentDate = TimeZone::getCurrentDate('Y-m-d', (int)$time);
            return "`$field` " . ($is_current === true ? '=' : 'BETWEEN') . " '{$getCurrentDate}'" . ($is_current === false ? " AND '{$current_date}'" : "");
        };

        //Check Time
        switch ($time) {
            case 'today':
                $where = "`$field` = '{$current_date}'";
                break;
            case 'day-before-yesterday':
                $fromDate = TimeZone::getTimeAgo(2, 'Y-m-d');
                $toDate   = TimeZone::getTimeAgo(1, 'Y-m-d');
                $where    = "`$field` BETWEEN '{$fromDate}' AND '{$toDate}'";
                break;
            case 'yesterday':
                $getCurrentDate = TimeZone::getTimeAgo(1, 'Y-m-d');
                $where          = "`$field` = '{$getCurrentDate}'";
                break;
            case '2-weeks-ago':
                $fromDate = TimeZone::getTimeAgo(21, 'Y-m-d');
                $toDate   = TimeZone::getTimeAgo(14, 'Y-m-d');
                $where    = "`$field` BETWEEN '{$fromDate}' AND '{$toDate}'";
                break;
            case 'last-week':
                $fromDate = TimeZone::getTimeAgo(14, 'Y-m-d');
                $toDate   = TimeZone::getTimeAgo(7, 'Y-m-d');
                $where    = "`$field` BETWEEN '{$fromDate}' AND '{$toDate}'";
                break;
            case 'week':
                $where = $field_sql(-7);
                break;
            case 'two-weeks':
                $where = $field_sql(-14);
                break;
            case 'last-two-weeks':
                $fromDate = TimeZone::getTimeAgo(28, 'Y-m-d');
                $toDate   = TimeZone::getTimeAgo(14, 'Y-m-d');
                $where    = "`$field` BETWEEN '{$fromDate}' AND '{$toDate}'";
                break;
            case '2-months-ago':
                $fromDate = TimeZone::getTimeAgo(90, 'Y-m-d');
                $toDate   = TimeZone::getTimeAgo(60, 'Y-m-d');
                $where    = "`$field` BETWEEN '{$fromDate}' AND '{$toDate}'";
                break;
            case 'last-month':
                $fromDate = TimeZone::getTimeAgo(60, 'Y-m-d');
                $toDate   = TimeZone::getTimeAgo(30, 'Y-m-d');
                $where    = "`$field` BETWEEN '{$fromDate}' AND '{$toDate}'";
                break;
            case 'month':
                $where = $field_sql(-30);
                break;
            case '60days':
                $where = $field_sql(-60);
                break;
            case '90days':
                $where = $field_sql(-90);
                break;
            case 'year':
                $where = $field_sql(-365);
                break;
            case 'this-year':
                $fromDate = TimeZone::getLocalDate('Y-m-d', strtotime(gmdate('Y-01-01')));
                $toDate   = TimeZone::getCurrentDate('Y-m-d');
                $where    = "`$field` BETWEEN '{$fromDate}' AND '{$toDate}'";
                break;
            case 'last-year':
                $fromDate = TimeZone::getTimeAgo(365, 'Y-01-01');
                $toDate   = TimeZone::getTimeAgo(365, 'Y-12-31');
                $where    = "`$field` BETWEEN '{$fromDate}' AND '{$toDate}'";
                break;
            case 'total':
                $where = "";
                break;
            default:
                if (array_key_exists('is_day', $range)) {
                    //Check a day
                    if (TimeZone::isValidDate($time)) {
                        $where = "`$field` = '{$time}'";
                    } else {
                        $getCurrentDate = TimeZone::getCurrentDate('Y-m-d', $time);
                        $where          = "`$field` = '{$getCurrentDate}'";
                    }
                } elseif (array_key_exists('start', $range) and array_key_exists('end', $range)) {
                    //Check Between Two Time
                    $getCurrentDate    = TimeZone::getCurrentDate('Y-m-d', '-0', strtotime($range['start']));
                    $getCurrentEndDate = TimeZone::getCurrentDate('Y-m-d', '-0', strtotime($range['end']));
                    $where             = "`$field` BETWEEN '{$getCurrentDate}' AND '{$getCurrentEndDate}'";
                } else {
                    //Check From a Date To Now
                    $where = $field_sql($time);
                }
        }

        return $where;
    }

    /**
     * Easy U-sort Array
     *
     * @param $a
     * @param $b
     * @return bool
     */
    public static function compare_uri_hits($a, $b)
    {
        return $a[1] < $b[1];
    }

    /**
     * Easy U-sort Array
     *
     * @param $a
     * @param $b
     * @return int
     */
    public static function compare_uri_hits_int($a, $b)
    {
        if ($b[1] == $a[1]) return 0;
        if ($b[1] > $a[1]) return 1;
        if ($b[1] < $a[1]) return -1;

    }

    /**
     * Return Number Posts in WordPress
     *
     * @return int
     */
    public static function getCountPosts()
    {
        $count_posts = wp_count_posts('post');

        $ret = 0;
        if (is_object($count_posts)) {
            $ret = $count_posts->publish;
        }
        return $ret;
    }

    /**
     * Get Count Pages WordPress
     *
     * @return int
     */
    public static function getCountPages()
    {
        $count_pages = wp_count_posts('page');

        $ret = 0;
        if (is_object($count_pages)) {
            $ret = $count_pages->publish;
        }
        return $ret;
    }

    /**
     * Get All WordPress Count
     *
     * @return mixed
     */
    public static function getCountComment()
    {
        global $wpdb;

        $countcomms = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = '1'");
        return $countcomms;
    }

    /**
     * Get Count Comment Spam
     *
     * @return mixed
     */
    public static function getCountSpam()
    {
        return number_format_i18n(get_option('akismet_spam_count'));
    }

    /**
     * Get Count All WordPress Users
     *
     * @return mixed
     */
    public static function getCountUsers()
    {
        $result = count_users();
        return $result['total_users'];
    }

    /**
     * Return the last date a post was published on your site.
     *
     * @return string
     */
    public static function getLastPostDate()
    {
        global $wpdb;

        $db_date     = $wpdb->get_var("SELECT post_date FROM {$wpdb->posts} WHERE post_type='post' AND post_status='publish' ORDER BY post_date DESC LIMIT 1");
        $date_format = get_option('date_format');
        return TimeZone::getCurrentDate_i18n($date_format, $db_date, false);
    }

    /**
     * Returns the average number of days between posts on your site.
     *
     * @param bool $days
     * @return float
     */
    public static function getAveragePost($days = false)
    {
        global $wpdb;

        $get_first_post = $wpdb->get_var("SELECT post_date FROM {$wpdb->posts} WHERE post_status = 'publish' ORDER BY post_date LIMIT 1");
        $get_total_post = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'post'");

        $days_spend = intval(
            (time() - strtotime($get_first_post)) / 86400
        ); // 86400 = 60 * 60 * 24 = number of seconds in a day

        if ($days == true) {
            if ($get_total_post == 0) {
                $get_total_post = 1;
            } // Avoid divide by zero errors.

            return round($days_spend / $get_total_post, 0);
        } else {
            if ($days_spend == 0) {
                $days_spend = 1;
            } // Avoid divide by zero errors.

            return round($get_total_post / $days_spend, 2);
        }
    }

    /**
     * Returns the average number of days between comments on your site.
     *
     * @param bool $days
     * @return float
     */
    public static function getAverageComment($days = false)
    {

        global $wpdb;

        $get_first_comment = $wpdb->get_var("SELECT comment_date FROM {$wpdb->comments} ORDER BY comment_date LIMIT 1");
        $get_total_comment = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = '1'");

        $days_spend = intval(
            (time() - strtotime($get_first_comment)) / 86400
        ); // 86400 = 60 * 60 * 24 = number of seconds in a day

        if ($days == true) {
            if ($get_total_comment == 0) {
                $get_total_comment = 1;
            } // Avoid divide by zero errors.

            return round($days_spend / $get_total_comment, 0);
        } else {
            if ($days_spend == 0) {
                $days_spend = 1;
            } // Avoid divide by zero errors.

            return round($get_total_comment / $days_spend, 2);
        }
    }

    /**
     * Returns the average number of days between user registrations on your site.
     *
     * @param bool $days
     * @return float
     */
    public static function getAverageRegisterUser($days = false)
    {

        global $wpdb;

        $get_first_user = $wpdb->get_var("SELECT `user_registered` FROM {$wpdb->users} ORDER BY user_registered LIMIT 1");
        $get_total_user = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users}");

        $days_spend = intval(
            (time() - strtotime($get_first_user)) / 86400
        );

        if ($days == true) {
            if ($get_total_user == 0) {
                $get_total_user = 1;
            }

            return round($days_spend / $get_total_user, 0);
        } else {
            if ($days_spend == 0) {
                $days_spend = 1;
            }

            return round($get_total_user / $days_spend, 2);
        }
    }

    /**
     * Returns default parameters for hits request
     *
     * @return array
     */
    public static function getHitsDefaultParams()
    {
        // Create Empty Params Object
        $params = array();

        //Set Page Type
        $get_page_type          = Pages::get_page_type();
        $params['source_type']  = $get_page_type['type'];
        $params['source_id']    = $get_page_type['id'];
        $params['search_query'] = (isset($get_page_type['search_query']) ? base64_encode(esc_html($get_page_type['search_query'])) : '');

        // page url
        $params['page_uri'] = base64_encode(Pages::get_page_uri());

        /**
         * Signature
         * @version 14.9
         */
        if (self::isRequestSignatureEnabled()) {
            $params['signature'] = Signature::generate([
                $get_page_type['type'],
                (int)$get_page_type['id']
            ]);
        }

        return $params;
    }

    /**
     * The version number will be anonymous using this function
     *
     * @param $version
     * @return string
     * @example 106.2.124.0 -> 106.0.0.0
     *
     */
    public static function makeAnonymousVersion($version)
    {
        $mainVersion         = substr($version, 0, strpos($version, '.'));
        $subVersion          = substr($version, strpos($version, '.') + 1);
        $anonymousSubVersion = preg_replace('/[0-9]+/', '0', $subVersion);

        return "{$mainVersion}.{$anonymousSubVersion}";
    }

    /**
     * Do not track browser detection
     *
     * @return bool
     */
    public static function dntEnabled()
    {
        if (Option::get('do_not_track')) {
            return (isset($_SERVER['HTTP_DNT']) && $_SERVER['HTTP_DNT'] == 1) or (function_exists('getallheaders') && isset(getallheaders()['DNT']) && getallheaders()['DNT'] == 1);
        }

        return false;
    }

    public static function getRequestUri()
    {
        if (self::is_rest_request() and isset($_REQUEST['page_uri'])) {
            return base64_decode($_REQUEST['page_uri']);
        }

        return sanitize_url(wp_unslash($_SERVER['REQUEST_URI']));
    }

    /**
     * Check whether an add-on is active or not
     *
     * @param string $slug
     * @return bool
     */
    public static function isAddOnActive($slug)
    {
        if (!function_exists('is_plugin_active')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        $pluginName = sprintf('wp-statistics-%1$s/wp-statistics-%1$s.php', $slug);

        return is_plugin_active($pluginName);
    }

    public static function convertBytes($input)
    {
        $unit  = strtoupper(substr($input, -1));
        $value = (int)$input;
        switch ($unit) {
            case 'G':
                $value *= 1024;
            case 'M':
                $value *= 1024;
            case 'K':
                $value *= 1024;
        }
        return $value;
    }

    public static function checkMemoryLimit()
    {
        if (!function_exists('memory_get_peak_usage') or !function_exists('ini_get')) {
            return false;
        }

        $memoryLimit = ini_get('memory_limit');

        if (memory_get_peak_usage(true) > self::convertBytes($memoryLimit)) {
            return true;
        }

        return false;
    }

    public static function yieldARow($rows)
    {
        $i = 0;
        while ($row = current($rows)) {
            yield $row;
            unset($rows[$i]);
            $i++;
        }
    }

    public static function prepareArrayToStringForQuery($fields = array())
    {
        global $wpdb;

        foreach ($fields as &$value) {
            $value = $wpdb->prepare('%s', $value);
        }

        return implode(', ', $fields);
    }


    /**
     * Formats a number into a string with appropriate units (K, M, B, T).
     *
     * @param int|float $number The number to be formatted.
     * @param int $precision The number of decimal places to round the result to for numbers without units. Default is 0.
     * @return string The formatted number with appropriate units.
     */
    public static function formatNumberWithUnit($number, $precision = 0)
    {
        if (!is_numeric($number)) return 0;

        $units = ['', 'K', 'M', 'B', 'T'];
        for ($i = 0; $number >= 1000 && $i < 4; $i++) {
            $number /= 1000;
        }

        if (empty($units[$i])) {
            $formattedNumber = round($number, $precision);
        } else {
            $formattedNumber = round($number, 1) . $units[$i];
        }

        return $formattedNumber;
    }


    /**
     * Filters an array by keeping only the keys specified in the second argument.
     *
     * @param array $arr The array to be filtered.
     * @param array $keys The keys to keep in the array.
     * @return array The filtered array.
     */
    public static function filterArrayByKeys($array, $keys)
    {
        return array_intersect_key($array, array_flip($keys));
    }


    /**
     * Divides two numbers.
     *
     * @param int|float $dividend The number to be divided.
     * @param int|float $divisor The number to divide by.
     * @param int $precision The number of decimal places to round the result to. Default is 2.
     * @return float The result of the division, rounded to the specified precision. Returns 0 if the divisor is 0.
     */
    public static function divideNumbers($dividend, $divisor, $precision = 2)
    {
        if ($divisor == 0) {
            return 0;
        }
        return round($dividend / $divisor, $precision);
    }


    /**
     * Calculates the difference between two dates.
     *
     * @param string $date1 The first date.
     * @param string $date2 The second date.
     */
    public static function calculateDateDifference($date1, $date2 = 'now')
    {
        // Convert dates to DateTime objects
        $datetime1 = new \DateTime($date1);
        $datetime2 = new \DateTime($date2);

        $interval = $datetime1->diff($datetime2);

        if ($interval->y > 0) {
            return _n('a year', sprintf('%d years', $interval->y), $interval->y, 'wp-statistics');
        } elseif ($interval->m > 0) {
            return _n('a month', sprintf('%d months', $interval->m), $interval->m, 'wp-statistics');
        } elseif ($interval->d >= 7) {
            $weeks = floor($interval->d / 7);
            return _n('a week', sprintf('%d weeks', $weeks), $weeks, 'wp-statistics');
        } else {
            return _n('a day', sprintf('%d days', $interval->d), $interval->d, 'wp-statistics');
        }
    }

    /**
     * Retrieves the name of a post type.
     *
     * @param string $postType The post type to retrieve the name for.
     * @param bool $singular Whether to retrieve the singular name or the plural name.
     *
     * @return string The name of the post type.
     */
    public static function getPostTypeName($postType, $singular = false)
    {
        $postTypeObj = get_post_type_object($postType);

        if (empty($postTypeObj)) return '';

        return $singular == true
            ? $postTypeObj->labels->singular_name
            : $postTypeObj->labels->name;
    }

    /**
     * Retrieves the name of a taxonomy.
     *
     * @param string $taxonomy The taxonomy to retrieve the name for.
     * @param bool $singular Whether to retrieve the singular name or the plural name.
     *
     * @return string The name of the taxonomy.
     */
    public static function getTaxonomyName($taxonomy, $singular = false)
    {
        $taxonomy = get_taxonomy($taxonomy);

        if (empty($taxonomy)) return '';

        return $singular == true
            ? $taxonomy->labels->singular_name
            : $taxonomy->labels->name;
    }


    /**
     * Retrieves the country code based on the timezone string.
     *
     * @return string The country code corresponding to the timezone.
     */
    public static function getTimezoneCountry()
    {
        $timezone    = get_option('timezone_string');
        $countryCode = TimeZone::getCountry($timezone);
        return $countryCode;
    }

    /**
     * Returns full URL of a DIR.
     *
     * @param string $dir
     *
     * @return  string          URL. Empty on error.
     * @source  https://wordpress.stackexchange.com/a/264870/
     */
    public static function dirToUrl($dir)
    {
        if (!is_file($dir)) {
            return '';
        }

        return esc_url_raw(str_replace(
            wp_normalize_path(untrailingslashit(ABSPATH)),
            site_url(),
            wp_normalize_path($dir)
        ));
    }

    /**
     * Returns full DIR of a local URL.
     *
     * @param string $url
     *
     * @return  string          DIR. Empty on error.
     */
    public static function urlToDir($url)
    {
        if (stripos($url, site_url()) === false) {
            return '';
        }

        return (str_replace(
            site_url(),
            wp_normalize_path(untrailingslashit(ABSPATH)),
            $url
        ));
    }

    public static function getReportEmailTip()
    {
        $tips = [
            [
                'title'   => __('Optimize Your Content Strategy', 'wp-statistics'),
                'content' => __('Use WP Statistics to identify your most popular pages and posts. Analyze the data to understand what content resonates with your audience, and use these insights to guide your content creation efforts.', 'wp-statistics'),
            ],
            [
                'title'   => __('Optimize Your Data Accuracy', 'wp-statistics'),
                'content' => __(sprintf('For maximum accuracy, enable the cache compatibility mode on your website and check your filtering settings. By following these steps, traffic data becomes more accurate. For more details, read %1$s.', '<a href="https://wp-statistics.com/resources/enhancing-data-accuracy/?utm_source=wp-statistics&utm_medium=email&utm_campaign=tips" target="_blank">Enhancing Data Accuracy</a>'), 'wp-statistics'),
            ],
            [
                'title'   => __('Keep the plugin up-to-date', 'wp-statistics'),
                'content' => __('Ensure that your WP Statistics plugin is up-to-date in order to get the latest features and security improvements.', 'wp-statistics'),
            ],
            [
                'title'   => __('Maintain Privacy Compliance', 'wp-statistics'),
                'content' => __(sprintf('To ensure that your website complies with the latest privacy standards, use the Privacy Audit feature in WP Statistics. It provides actionable recommendations for improving your privacy compliance by assessing your WP Statistics\' current settings. For more information, refer to our %1$s.', '<a href="https://wp-statistics.com/resources/privacy-audit/?utm_source=wp-statistics&utm_medium=email&utm_campaign=privacy" target="_blank">Privacy Audit Guide</a>'), 'wp-statistics'),
            ],
            [
                'title'   => __('WordPress Export and Erasure', 'wp-statistics'),
                'content' => __(sprintf('If you record PII data with WP Statistics, use WordPress data export and erasure features to manage this information. This ensures compliance with privacy regulations like GDPR. For more details, see our %1$s.', '<a href="https://wp-statistics.com/resources/compliant-with-wordpress-data-export-and-erasure/?utm_source=wp-statistics&utm_medium=email&utm_campaign=tips" target="_blank">Data Export and Erasure Guide</a>'), 'wp-statistics'),
            ],
            [
                'title'   => __('Track Links and Downloads', 'wp-statistics'),
                'content' => __(sprintf('Track how users interact with your site\'s links and downloads using the Link and Download Tracker feature. You can use this information to improve content engagement and understand user behavior. %1$s.', '<a href="https://wp-statistics.com/product/wp-statistics-data-plus/?utm_source=wp-statistics&utm_medium=email&utm_campaign=dp" target="_blank">Read more</a>'), 'wp-statistics'),
            ],
            [
                'title'   => __('Advanced Filtering', 'wp-statistics'),
                'content' => __(sprintf('Analyze specific query parameters, including UTM tags, for each piece of content. Tracking marketing campaigns and engagement allows you to refine your strategies and maximize their impact. %1$s.', '<a href="https://wp-statistics.com/product/wp-statistics-data-plus/?utm_source=wp-statistics&utm_medium=email&utm_campaign=dp" target="_blank">Read more</a>'), 'wp-statistics'),
            ],
            [
                'title'   => __('Weekly Traffic Comparison Widget', 'wp-statistics'),
                'content' => __(sprintf('On the Overview page, the Weekly Traffic Comparison widget provides a quick snapshot of your main metrics. You can analyze traffic changes, identify trends, and make data-driven decisions to improve your site\'s performance with this feature. %1$s.', '<a href="https://wp-statistics.com/product/wp-statistics-data-plus/?utm_source=wp-statistics&utm_medium=email&utm_campaign=dp" target="_blank">Read more</a>'), 'wp-statistics'),
            ],
            [
                'title'   => __('Traffic by Hour Widget', 'wp-statistics'),
                'content' => __(sprintf('On the Overview page, the Traffic by Hour widget displays visitor patterns by hour. Ensure maximum engagement and efficiency by optimizing server resources and scheduling content releases for peak visitor times. %1$s.', '<a href="https://wp-statistics.com/product/wp-statistics-data-plus/?utm_source=wp-statistics&utm_medium=email&utm_campaign=dp" target="_blank">Read more</a>'), 'wp-statistics'),
            ],
            [
                'title'   => __('Content-Specific Analytics', 'wp-statistics'),
                'content' => __(sprintf('Analyze each piece of content in detail, including views, visitor locations, and online users. Based on user data, these insights can help you optimize content. %1$s.', '<a href="https://wp-statistics.com/product/wp-statistics-data-plus/?utm_source=wp-statistics&utm_medium=email&utm_campaign=dp" target="_blank">Read more</a>'), 'wp-statistics'),
            ],
            [
                'title'   => __('Custom Post Type Tracking', 'wp-statistics'),
                'content' => __(sprintf('Track all custom post types as well as posts and pages. This ensures complete analytics across all content types on your site. %1$s.', '<a href="https://wp-statistics.com/product/wp-statistics-data-plus/?utm_source=wp-statistics&utm_medium=email&utm_campaign=dp" target="_blank">Read more</a>'), 'wp-statistics'),
            ],
            [
                'title'   => __('Custom Taxonomy Analytics', 'wp-statistics'),
                'content' => __(sprintf('Track custom taxonomies along with default taxonomies like Categories and Tags to gain deeper insights into all taxonomies used on your site. %1$s.', '<a href="https://wp-statistics.com/product/wp-statistics-data-plus/?utm_source=wp-statistics&utm_medium=email&utm_campaign=dp" target="_blank">Read more</a>'), 'wp-statistics'),
            ],
            [
                'title'   => __('Real-Time Stats', 'wp-statistics'),
                'content' => __(sprintf('Monitor your website\'s traffic and activity in real time. Your WordPress statistics are displayed instantly, so you don\'t need to refresh your page every time someone visits your blog. Watch your website\'s performance live. %1$s.', '<a href="https://wp-statistics.com/product/wp-statistics-realtime-stats/?utm_source=wp-statistics&utm_medium=email&utm_campaign=real-time" target="_blank">Read more</a>'), 'wp-statistics'),
            ],
            [
                'title'   => __('Mini Chart', 'wp-statistics'),
                'content' => __(sprintf('Track your content\'s performance with mini charts. Quick access to traffic data is provided by an admin bar. The chart type and color can be customized according to your preferences. Analyze your content\'s performance and make informed decisions to enhance its success.  %1$s.', '<a href="https://wp-statistics.com/product/wp-statistics-mini-chart/?utm_source=wp-statistics&utm_medium=email&utm_campaign=mini-chart" target="_blank">Read more</a>'), 'wp-statistics'),
            ],
        ];

        return $tips[array_rand($tips)];
    }

    /**
     * Get the device category name
     * Remove device subtype, for example: mobile:smart -> mobile
     *
     * @param string $device
     *
     * @return string
     */
    public static function getDeviceCategoryName($device)
    {
        if (strpos($device, ':') !== false) {
            $device = explode(':', $device)[0];
        }
        return $device;
    }

    /**
     * Get default date format
     * @param bool $withTime
     * @return string
     */
    public static function getDefaultDateFormat($withTime = false, $excludeYear = false)
    {
        $dateFormat = get_option('date_format');
        $timeFormat = get_option('time_format');

        if (empty($dateFormat)) {
            $dateFormat = 'Y-m-d';
        }

        if (empty($timeFormat)) {
            $timeFormat = 'g:i a';
        }

        $dateTimeFormat = $withTime ? $dateFormat . ' ' . $timeFormat : $dateFormat;

        if ($excludeYear) {
            $dateTimeFormat = str_replace(
                [', Y', 'Y ,', 'Y', ',Y', 'Y,', 'y', ', y', 'y ,', ',y', 'y,'], '', $dateTimeFormat
            );
        }

        return $dateTimeFormat;
    }

    /**
     * Checks if the WordPress admin bar is showing and can current user see it?
     *
     * @return  boolean
     */
    public static function isAdminBarShowing()
    {
        /**
         * Show/Hide WP Statistics Admin Bar
         *
         * @example add_filter('wp_statistics_show_admin_bar', function(){ return false; });
         */
        $showAdminBar = has_filter('wp_statistics_show_admin_bar') ? apply_filters('wp_statistics_show_admin_bar', true) : Option::get('menu_bar');

        return ($showAdminBar && is_admin_bar_showing() && User::Access());
    }

    /**
     * Calculates percentage difference between two numbers.
     *
     * @param int|float $firstNumber
     * @param int|float $secondNumber
     *
     * @return  float
     */
    public static function calculatePercentageChange($firstNumber, $secondNumber)
    {
        if (!is_numeric($firstNumber)) {
            $firstNumber = 0;
        }
        if (!is_numeric($secondNumber)) {
            $secondNumber = 0;
        }
        if ($firstNumber == $secondNumber) {
            return 0;
        }

        // Multiply the final result by -1 if the second number is smaller (decreasing change)
        $multiply = 1;
        if ($firstNumber > $secondNumber) {
            $multiply = -1;
        }

        // The first part of the formula depends on whether it's an increasing change or decreasing
        $change = $firstNumber > $secondNumber ? $firstNumber - $secondNumber : $secondNumber - $firstNumber;

        // Final part of the formula: ($change / $firstNumber) * 100
        $result = $firstNumber == 0 ? $change : ($change / $firstNumber);
        $result *= 100;
        $result *= $multiply;

        return $result;
    }

    /**
     * Checks if "Anonymous Tracking" option is enabled and user hasn't given consent yet.
     *
     * In this case, we have to track user's information anonymously.
     *
     * @return  bool
     */
    public static function shouldTrackAnonymously()
    {
        $selectedConsentLevel = Option::get('consent_level_integration', 'disabled');

        return WpConsentApi::isWpConsentApiActive() &&
            $selectedConsentLevel !== 'disabled' &&
            Option::get('anonymous_tracking', false) == true &&
            !(function_exists('wp_has_consent') && wp_has_consent($selectedConsentLevel));
    }

    /**
     * Checks if the WP Statistics request signature is enabled.
     *
     * This function uses the 'wp_statistics_request_signature_enabled' filter to determine if the request
     * signature feature in WP Statistics is enabled. By default, it returns true, but this can be modified
     * by using the filter in other parts of your theme or plugin.
     *
     * @return bool True if the request signature feature is enabled, otherwise false.
     */
    public static function isRequestSignatureEnabled()
    {
        return apply_filters('wp_statistics_request_signature_enabled', true);
    }
}
