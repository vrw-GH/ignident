<?php
namespace HTContactFormAdmin\Includes;
/**
 * Diagnostic data.
 */

// If this file is accessed directly, exit.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class.
 */
class DiagnosticData {

    /**
     * Project name.
     */
    private $project_name;

    /**
     * Project type.
     */
    private $project_type;

    /**
     * Project version.
     */
    private $project_version;

    /**
     * Pro version Slug.
     */
    private $project_pro_slug;

    /**
     * Pro active.
     */
    private $project_pro_active;

    /**
     * Pro installed.
     */
    private $project_pro_installed;

    /**
     * Pro version.
     */
    private $project_pro_version;

    /**
     * Data center.
     */
    private $data_center;

    /**
     * Privacy policy.
     */
    private $privacy_policy;

    /**
     * Instance.
     */
    private static $_instance = null;

    /**
     * Get instance.
     */
    public static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        $this->project_name = 'HT Contact Form';
        $this->project_type = 'wordpress-plugin';
        $this->project_version = HTCONTACTFORM_VERSION;
        $this->data_center = 'https://n8n.aslamhasib.com/webhook/484fe1ab-9cdf-4318-8b6f-2b218ac47009';
        $this->privacy_policy = 'https://hasthemes.com/privacy-policy/';

        $this->project_pro_slug = '';
        $this->project_pro_active = $this->is_pro_plugin_active();
        $this->project_pro_installed = $this->is_pro_plugin_installed();
        $this->project_pro_version = $this->get_pro_version();

        if ( get_option( 'ht_contactform_diagnostic_data_agreed' ) === 'yes' || get_option( 'ht_contactform_diagnostic_data_notice' ) === 'no' ) {
            return;
        }

        add_action( 'wp_ajax_ht_contactform_diagnostic_data', function () {
            check_ajax_referer( 'ht_contactform_diagnostic_data_ajax_request' );
            $agreed = isset( $_POST['agreed'] ) ? sanitize_key( $_POST['agreed'] ) : '' ;
            if( $agreed === 'yes' ){
                $this->process_data( $agreed, true );
            } elseif( $agreed === 'no' ) {
                $this->process_data( $agreed, true );
            }
        } );

        add_action('admin_init', function(){

            $nonce = isset( $_GET['ht_contactform_diagnostic_data_nonce'] ) ? sanitize_key( wp_unslash($_GET['ht_contactform_diagnostic_data_nonce']) ) : '' ;
            if(!wp_verify_nonce( $nonce, 'ht_contactform_diagnostic_data_nonce' )){
                return;
            }
            $agreed  = isset( $_GET['ht_contactform_diagnostic_data_agreed'] ) ? sanitize_key( wp_unslash($_GET['ht_contactform_diagnostic_data_agreed']) ) : '' ;

            if( $agreed === 'yes' ){
                $this->process_data( $agreed );
            } elseif( $agreed === 'no' ) {
                $this->process_data( $agreed );
            }
        }, 11);

        add_action('admin_head', [$this, 'notice_css']);
        add_action('admin_footer', [$this, 'notice_js']);
    }

    public function notice_css() {
        echo '<style>
            .ht-contactform-diagnostic-data-notice,.woocommerce-embed-page .ht-contactform-diagnostic-data-notice{padding-top:.75em;padding-bottom:.75em;}.ht-contactform-diagnostic-data-notice .ht-contactform-diagnostic-data-buttons,.ht-contactform-diagnostic-data-notice .ht-contactform-diagnostic-data-list,.ht-contactform-diagnostic-data-notice .ht-contactform-diagnostic-data-message{padding:.25em 2px;margin:0;}.ht-contactform-diagnostic-data-notice .ht-contactform-diagnostic-data-list{display:none;color:#646970;}.ht-contactform-diagnostic-data-notice .ht-contactform-diagnostic-data-buttons{padding-top:.75em;}.ht-contactform-diagnostic-data-notice .ht-contactform-diagnostic-data-buttons .button{margin-right:5px;box-shadow:none;}.ht-contactform-diagnostic-data-loading{position:relative;}.ht-contactform-diagnostic-data-loading::before{position:absolute;content:"";width:100%;height:100%;top:0;left:0;background-color:rgba(255,255,255,.5);z-index:999;}.ht-contactform-diagnostic-data-disagree{border-width:0px !important;background-color: transparent!important; padding: 0!important;}.ht-contactform-diagnostic-data-list-toogle{cursor:pointer;color:#2271b1;text-decoration:none;}.ht-contactform-diagnostic-data-thanks{width:100%;}
            .ht_contactform_diagnostic_data_list{display:none;color:#646970;}.ht_contactform_diagnostic_data_list_toogle{cursor:pointer;color:#2271b1;text-decoration:none;} p.ht_contactform_diagnostic_data_buttons {margin-bottom: 0;} .ht_contactform_diagnostic_data_thanks {max-width: calc(100% - 20px); margin: 20px 0;}
        </style>';
    }
    
    public function notice_js() {
        $ajax_nonce = wp_create_nonce( "ht_contactform_diagnostic_data_ajax_request" );
        $ajax_url = admin_url( 'admin-ajax.php' );
        echo '<script type="text/javascript">;(function($) {
            "use strict";
            function htFormDismissThanksNotice(noticeWrap) {
                $(".ht_contactform_diagnostic_data_thanks .notice-dismiss").on("click", function(e) {
                    e.preventDefault();
                    let thisButton = $(this),
                        noticeWrap = thisButton.closest(".ht_contactform_diagnostic_data_thanks");
                    noticeWrap.fadeTo(100, 0, function() {
                        noticeWrap.slideUp(100, function() {
                            noticeWrap.remove()
                        })
                    })
                })
            };
            $(".ht_contactform_diagnostic_data_list_toogle").on("click", function(e) {
                e.preventDefault();
                $(this).parents(".ht_contactform_diagnostic_data_notice").find(".ht_contactform_diagnostic_data_list").slideToggle("fast")
            });
            $(".ht_contactform_diagnostic_data_button").on("click", function(e) {
                e.preventDefault();
                let thisButton = $(this),
                    noticeWrap = thisButton.closest(".ht_contactform-admin-notice"),
                    agreed = thisButton.hasClass("ht_contactform_diagnostic_data_agree") ? "yes" : "no";
                $.ajax({
                    type: "POST",
                    url: "'.esc_url($ajax_url).'",
                    data: {
                        action: "ht_contactform_diagnostic_data",
                        agreed: agreed,
                        _wpnonce: "'.esc_attr($ajax_nonce).'"
                    },
                    beforeSend: function() {
                        noticeWrap.addClass("ht_contactform_diagnostic_data_loading")
                    },
                    success: function(response) {
                        response = "object" === typeof response ? response : {};
                        let success = response.hasOwnProperty("success") ? response.success : "no",
                            notice = response.hasOwnProperty("notice") ? response.notice : "no",
                            thanks_notice = response.hasOwnProperty("thanks_notice") ? response.thanks_notice : "";
                        if ("yes" === success) {
                            noticeWrap.replaceWith(thanks_notice);
                        } else if ("no" === notice) {
                            noticeWrap.remove();
                        };
                        noticeWrap.removeClass("ht_contactform_diagnostic_data_loading");
                        htFormDismissThanksNotice(noticeWrap)
                    },
                    error: function() {
                        noticeWrap.removeClass("ht_contactform_diagnostic_data_loading")
                    },
                })
            })
        })(jQuery);</script>';
    }

    /**
     * Is capable user.
     */
    private function is_capable_user() {
        $result = 'no';

        if ( current_user_can( 'manage_options' ) ) {
            $result = 'yes';
        }

        return $result;
    }

    /**
     * Is show core notice.
     */
    private function is_show_core_notice() {
        $result = get_option( 'ht_contactform_diagnostic_data_notice', 'yes' );
        $result = ( ( 'yes' === $result ) ? 'yes' : 'no' );

        return $result;
    }

    /**
     * Is pro active.
     */
    private function is_pro_plugin_active() {

        $result = is_plugin_active( $this->project_pro_slug );
        $result = ( ( true === $result ) ? 'yes' : 'no' );

        return $result;
    }

    /**
     * Is pro installed.
     */
    private function is_pro_plugin_installed() {

        $plugins = get_plugins();
        $result = ( isset( $plugins[ $this->project_pro_slug ] ) ? 'yes' : 'no' );

        return $result;
    }

    /**
     * Get pro version.
     */
    private function get_pro_version() {

        $plugins = get_plugins();
        $data = ( ( isset( $plugins[ $this->project_pro_slug ] ) && is_array( $plugins[ $this->project_pro_slug ] ) ) ? $plugins[ $this->project_pro_slug ] : array() );
        $version = ( isset( $data['Version'] ) ? sanitize_text_field( $data['Version'] ) : '' );

        return $version;
    }

    /**
     * Process data.
     */
    private function process_data( $agreed, $ajax = false ) {
        $notice  = 'no';

        if ( 'yes' === $agreed ) {
            $data = $this->get_data();

            if ( ! empty( $data ) ) {
                $response = $this->send_request( $data );

                if ( is_wp_error( $response ) ) {
                    $agreed = 'no';
                    $notice = 'yes';
                }
            }
        }

        update_option( 'ht_contactform_diagnostic_data_agreed', $agreed );
        update_option( 'ht_contactform_diagnostic_data_notice', $notice );
        set_transient( 'ht_contactform-notice-id-diagnostic-data', true );

        if($ajax) {
            $response = [
                'success' => $agreed,
                'notice' => $notice,
            ];

            if ( 'yes' === $agreed ) {
                $response['thanks_notice'] = $this->get_thanks_notice();
            }

            wp_send_json( $response );
        } else {
            echo wp_kses_post($this->get_thanks_notice());
        }
    }

    /**
     * Get data.
     */
    private function get_data() {
        $hash = md5( current_time( 'U', true ) );

        $project = array(
            'name'          => $this->project_name,
            'type'          => $this->project_type,
            'version'       => $this->project_version,
            'pro_active'    => $this->project_pro_active,
            'pro_installed' => $this->project_pro_installed,
            'pro_version'   => $this->project_pro_version,
        );

        $site_title = get_bloginfo( 'name' );
        $site_description = get_bloginfo( 'description' );
        $site_url = wp_parse_url( home_url(), PHP_URL_HOST );
        $admin_email = get_option( 'admin_email' );

        $admin_first_name = '';
        $admin_last_name = '';
        $admin_display_name = '';

        $users = get_users( array(
            'role'    => 'administrator',
            'orderby' => 'ID',
            'order'   => 'ASC',
            'number'  => 1,
            'paged'   => 1,
        ) );

        $admin_user = ( ( is_array( $users ) && isset( $users[0] ) && is_object( $users[0] ) ) ? $users[0] : null );

        if ( ! empty( $admin_user ) ) {
            $admin_first_name = ( isset( $admin_user->first_name ) ? $admin_user->first_name : '' );
            $admin_last_name = ( isset( $admin_user->last_name ) ? $admin_user->last_name : '' );
            $admin_display_name = ( isset( $admin_user->display_name ) ? $admin_user->display_name : '' );
        }

        $ip_address = $this->get_ip_address();


        // Get Plugins
        $all_plugins = get_plugins();
        $active_plugins = get_option('active_plugins');
        $plugins_string = '';
        foreach($all_plugins as $plugin_path => $plugin) {
            $plugins_string .= sprintf(
                "%s (v%s) - %s | ",
                $plugin['Name'],
                $plugin['Version'],
                in_array($plugin_path, $active_plugins) ? 'Active' : 'Inactive'
            );
        }
        $plugins_string = rtrim($plugins_string, ' | ');
        // Get Themes
        $all_themes = wp_get_themes();
        $active_theme = wp_get_theme();
        $themes_string = '';
        foreach($all_themes as $theme_slug => $theme) {
            $themes_string .= sprintf(
                "%s (v%s) - %s | ",
                $theme->get('Name'),
                $theme->get('Version'),
                ($theme_slug === $active_theme->get_stylesheet()) ? 'Active' : 'Inactive'
            );
        }
        $themes_string = rtrim($themes_string, ' | ');

        $data = array(
            'hash'               => $hash,
            'project'            => $project,
            'site_title'         => $site_title,
            'site_description'   => $site_description,
            'site_address'       => $site_url,
            'site_url'           => $site_url,
            'admin_email'        => $admin_email,
            'admin_first_name'   => $admin_first_name,
            'admin_last_name'    => $admin_last_name,
            'admin_display_name' => $admin_display_name,
            'server_info'        => $this->get_server_info(),
            'wordpress_info'     => $this->get_wordpress_info(),
            'users_count'        => $this->get_users_count(),
            'plugins_count'      => $this->get_plugins_count(),
            'ip_address'         => $ip_address,
            'country_name'       => $this->get_country_from_ip( $ip_address ),
            'ht_form_created'    => $this->form_created(),
            'plugin_list'       => $plugins_string,
            'theme_list'        => $themes_string,
            'deactivate_reason' => '',
            'deactivate_message' => '',
        );

        return $data;
    }

    /**
     * Get server info.
     */
    private function get_server_info() {
        global $wpdb;

        $software = isset( $_SERVER['SERVER_SOFTWARE'] ) && ! empty( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash($_SERVER['SERVER_SOFTWARE']) ) : '';
        $php_version = function_exists( 'phpversion' ) ? phpversion() : '';
        $mysql_version = method_exists( $wpdb, 'db_version' ) ? $wpdb->db_version() : '';
        $php_max_upload_size = size_format( wp_max_upload_size() );
        $php_default_timezone = date_default_timezone_get();
        $php_soap = class_exists( 'SoapClient' ) ? 'yes' : 'no';
        $php_fsockopen = function_exists( 'fsockopen' ) ? 'yes' : 'no';
        $php_curl = function_exists( 'curl_init' ) ? 'yes' : 'no';

        $server_info = array(
            'software'             => $software,
            'php_version'          => $php_version,
            'mysql_version'        => $mysql_version,
            'php_max_upload_size'  => $php_max_upload_size,
            'php_default_timezone' => $php_default_timezone,
            'php_soap'             => $php_soap,
            'php_fsockopen'        => $php_fsockopen,
            'php_curl'             => $php_curl,
        );

        return $server_info;
    }

    /**
     * Get WordPress info.
     */
    private function get_wordpress_info() {
        $wordpress_info = array();

        $memory_limit = ( defined( 'WP_MEMORY_LIMIT' ) ? WP_MEMORY_LIMIT : '' );
        $debug_mode = ( ( defined('WP_DEBUG') && WP_DEBUG ) ? 'yes' : 'no' );
        $locale = get_locale();
        $version = get_bloginfo( 'version' );
        $multisite = ( is_multisite() ? 'yes' : 'no' );
        $theme_slug = get_stylesheet();

        $wordpress_info = array(
            'memory_limit' => $memory_limit,
            'debug_mode'   => $debug_mode,
            'locale'       => $locale,
            'version'      => $version,
            'multisite'    => $multisite,
            'theme_slug'   => $theme_slug,
        );

        $theme = wp_get_theme( $wordpress_info['theme_slug'] );

        if ( is_object( $theme ) && ! empty( $theme ) && method_exists( $theme, 'get' ) ) {
            $theme_name    = $theme->get( 'Name' );
            $theme_version = $theme->get( 'Version' );
            $theme_uri     = $theme->get( 'ThemeURI' );
            $theme_author  = $theme->get( 'Author' );

            $wordpress_info = array_merge( $wordpress_info, array(
                'theme_name'    => $theme_name,
                'theme_version' => $theme_version,
                'theme_uri'     => $theme_uri,
                'theme_author'  => $theme_author,
            ) );
        }

        return $wordpress_info;
    }

    /**
     * Get users count.
     */
    private function get_users_count() {
        $users_count = array();

        $users_count_data = count_users();

        $total_users = ( isset( $users_count_data['total_users'] ) ? $users_count_data['total_users'] : 0 );
        $avail_roles = ( isset( $users_count_data['avail_roles'] ) ? $users_count_data['avail_roles'] : array() );

        $users_count['total'] = $total_users;

        if ( is_array( $avail_roles ) && ! empty( $avail_roles ) ) {
            foreach ( $avail_roles as $role => $count ) {
                $users_count[ $role ] = $count;
            }
        }

        return $users_count;
    }

    /**
     * Get plugins count.
     */
    private function get_plugins_count() {
        $total_plugins_count = 0;
        $active_plugins_count = 0;
        $inactive_plugins_count = 0;

        $plugins = get_plugins();
        $plugins = ( is_array( $plugins ) ? $plugins : array() );

        $active_plugins = get_option( 'active_plugins', array() );
        $active_plugins = ( is_array( $active_plugins ) ? $active_plugins : array() );

        if ( ! empty( $plugins ) ) {
            foreach ( $plugins as $key => $data ) {
                if ( in_array( $key, $active_plugins, true ) ) {
                    $active_plugins_count++;
                } else {
                    $inactive_plugins_count++;
                }

                $total_plugins_count++;
            }
        }

        $plugins_count = array(
            'total'    => $total_plugins_count,
            'active'   => $active_plugins_count,
            'inactive' => $inactive_plugins_count,
        );

        return $plugins_count;
    }

    /**
     * Get IP Address
     */
    private function get_ip_address() {
        $response = wp_remote_get( 'https://icanhazip.com/', [ 'timeout' => 5 ] );

        if ( is_wp_error( $response ) ) {
            return '';
        }

        $ip_address = wp_remote_retrieve_body( $response );
        $ip_address = trim( $ip_address );

        if ( ! filter_var( $ip_address, FILTER_VALIDATE_IP ) ) {
            return '';
        }

        return $ip_address;
    }

    /**
     * Get Country Form ID Address
     */
    private function get_country_from_ip( $ip_address ) {
        // HTTPS + commercial-use-allowed provider (ip-api.com is HTTP-only and
        // non-commercial). Returns the full country name in 'country'.
        $api_url = 'https://ipwho.is/' . $ip_address;

        // Fetch data from the API
        $response = wp_remote_get( $api_url, [ 'timeout' => 5 ] );

        if ( is_wp_error( $response ) ) {
            return 'Error';
        }

        // Decode the JSON response
        $data = json_decode( wp_remote_retrieve_body($response), true );

        if ( is_array($data) && !empty($data['success']) && !empty($data['country']) ) {
            return $data['country'];
        }
        return 'Unknown';
    }

    private function form_created() {
        $args = [
            'post_type'      => 'ht_form',
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'post_status'    => 'publish'
        ];

        $posts = get_posts($args);
        return count($posts);
    }

    /**
     * Send request.
     */
    private function send_request( $data = array() ) {
        if ( ! is_array( $data ) || empty( $data ) ) {
            return;
        }

        $site_url = wp_parse_url( home_url(), PHP_URL_HOST );

        $headers = array(
            'user-agent' => $this->project_name . '/' . md5( $site_url ) . ';',
            'Accept'     => 'application/json',
        );

        $response = wp_remote_post( $this->data_center, array(
            'method'      => 'POST',
            'timeout'     => 30,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => false,
            'headers'     => $headers,
            'body'        => $data,
            'cookies'     => array(),
        ) );

        return $response;
    }

    /**
     * Check if this plugin should show the diagnostic data notice.
     * Returns false if already agreed, dismissed, or a sibling plugin takes priority.
     */
    public function should_show_notice() {
        if ( get_option( 'ht_contactform_diagnostic_data_agreed' ) === 'yes' || get_option( 'ht_contactform_diagnostic_data_notice' ) === 'no' ) {
            return false;
        }

        $sibling_plugins = array(
            'woolentor-addons/woolentor_addons_elementor.php' => array(
                'agreed'  => 'woolentor_diagnostic_data_agreed',
                'notice'  => 'woolentor_diagnostic_data_notice',
            ),
            'ht-mega-for-elementor/htmega_addons_elementor.php' => array(
                'agreed'  => 'htmega_diagnostic_data_agreed',
                'notice'  => 'htmega_diagnostic_data_notice',
            ),
            'ht-easy-google-analytics/ht-easy-google-analytics.php' => array(
                'agreed'  => 'htga4_diagnostic_data_agreed',
                'notice'  => 'htga4_diagnostic_data_notice',
            ),
            'hashbar-wp-notification-bar/init.php' => array(
                'agreed'  => 'hashbar_diagnostic_data_agreed',
                'notice'  => 'hashbar_diagnostic_data_notice',
            ),
            'support-genix-lite/support-genix-lite.php' => array(
                'agreed'  => 'support_genix_lite_diagnostic_data_agreed',
                'notice'  => 'support_genix_lite_diagnostic_data_notice',
            ),
            'pixelavo/pixelavo.php' => array(
                'agreed'  => 'pixelavo_diagnostic_data_agreed',
                'notice'  => 'pixelavo_diagnostic_data_notice',
            ),
            'swatchly/swatchly.php' => array(
                'agreed'  => 'swatchly_diagnostic_data_agreed',
                'notice'  => 'swatchly_diagnostic_data_notice',
            ),
            'extensions-for-cf7/extensions-for-cf7.php' => array(
                'agreed'  => 'ht_cf7extensions_diagnostic_data_agreed',
                'notice'  => 'ht_cf7extensions_diagnostic_data_notice',
            ),
            'whols/whols.php' => array(
                'agreed'  => 'whols_diagnostic_data_agreed',
                'notice'  => 'whols_diagnostic_data_notice',
            ),
            'wp-plugin-manager/plugin-main.php' => array(
                'agreed'  => 'htpm_diagnostic_data_agreed',
                'notice'  => 'htpm_diagnostic_data_notice',
            ),
            'just-tables/just-tables.php' => array(
                'agreed'  => 'justtables_diagnostic_data_agreed',
                'notice'  => 'justtables_diagnostic_data_notice',
            ),
            'really-simple-google-tag-manager/really-simple-google-tag-manager.php' => array(
                'agreed'  => 'simple_googletag_diagnostic_data_agreed',
                'notice'  => 'simple_googletag_diagnostic_data_notice',
            ),
            'insert-headers-and-footers-script/init.php' => array(
                'agreed'  => 'ihafs_diagnostic_data_agreed',
                'notice'  => 'ihafs_diagnostic_data_notice',
            ),
        );

        foreach ( $sibling_plugins as $plugin_slug => $options ) {
            if ( get_option( $options['agreed'] ) === 'yes' ) {
                update_option( 'ht_contactform_diagnostic_data_agreed', 'yes' );
                update_option( 'ht_contactform_diagnostic_data_notice', 'no' );
                return false;
            }
        }

        // Ensure only one HT plugin shows the diagnostic notice per request.
        global $ht_diagnostic_notice_owner;
        if ( isset( $ht_diagnostic_notice_owner ) && $ht_diagnostic_notice_owner !== 'ht_contactform' ) {
            return false;
        }
        $ht_diagnostic_notice_owner = 'ht_contactform';

        return true;
    }

    /**
     * Show notices.
     */
    public function show_notices() {
        if ( ! $this->should_show_notice() ) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification
        $action = isset($_GET['action'] ) ? sanitize_key( wp_unslash($_GET['action'] ) ) : '';

        if ( 'no' === $this->is_capable_user() || 'upload-plugin' == $action ) {
            return;
        }

        if ( 'yes' === $this->is_show_core_notice() ) {
            $this->show_core_notice();
        }
    }

    /**
     * Show core notice.
     */
    private function show_core_notice() {

        $message_l2 = sprintf( esc_html__( 'Server information (Web server, PHP version, MySQL version), WordPress information, site name, site URL, number of plugins, number of users, your name, and email address. You can rest assured that no sensitive data will be collected or tracked. %1$sPrivacy Policy%2$s', 'ht-contactform' ), '<a target="_blank" href="' . esc_url( $this->privacy_policy ) . '">', '</a>' );

        $button_text_1 = esc_html__( 'Count Me In', 'ht-contactform' );
        $button_link_1 = add_query_arg( array( 'ht_contactform_diagnostic_data_agreed' => 'yes', 'ht_contactform_diagnostic_data_nonce' => wp_create_nonce( 'ht_contactform_diagnostic_data_nonce' ) ) );

        $button_text_2 = esc_html__( 'No thanks', 'ht-contactform' );
        $button_link_2 = add_query_arg( array( 'ht_contactform_diagnostic_data_agreed' => 'no', 'ht_contactform_diagnostic_data_nonce' => wp_create_nonce( 'ht_contactform_diagnostic_data_nonce' ) ) );
        ?>
        <div class="ht_contactform_diagnostic_data_notice">
            <p class="ht_contactform_diagnostic_data_message"><?php echo wp_kses_post( sprintf( esc_html__( 'Want to help make %2$s%1$s%3$s even more awesome? Allow %1$s to collect diagnostic data and usage information. (%4$swhat we collect%5$s)', 'ht-contactform' ), esc_html( $this->project_name ), '<strong>', '</strong>', '<a href="#" class="ht_contactform_diagnostic_data_list_toogle">', '</a>' ) ); ?></p>
            <p class="ht_contactform_diagnostic_data_list"><?php echo wp_kses_post( $message_l2 ); ?></p>
            <p class="ht_contactform_diagnostic_data_buttons">
                <a href="<?php echo esc_url( $button_link_1 ); ?>" class="ht_contactform_diagnostic_data_button ht_contactform_diagnostic_data_agree button button-primary"><?php echo esc_html( $button_text_1 ); ?></a>
                <a href="<?php echo esc_url( $button_link_2 ); ?>" class="ht_contactform_diagnostic_data_button ht_contactform_diagnostic_data_disagree button button-secondary"><?php echo esc_html( $button_text_2 ); ?></a>
            </p>
        </div>
        <?php
    }
    
    /**
     * Get thanks notice.
     */
    private function get_thanks_notice() {
        /*
        * translators: %1$s: project name
        * translators: %2$s: strong start tag
        * translators: %3$s: strong end tag
        */
        $message = sprintf( esc_html__( 'Thank you very much for supporting %2$s%1$s%3$s.', 'ht-contactform' ), $this->project_name, '<strong>', '</strong>' );
        /*
        * translators: %1$s: Message content
        */
        $notice = sprintf( '<div class="ht_contactform_diagnostic_data_thanks notice notice-success is-dismissible"><p>%1$s</p><button type="button" class="notice-dismiss"><span class="screen-reader-text"></span></button></div>', wp_kses_post( $message ) );

        return $notice;
    }

}