<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://ays-pro.com/
 * @since      1.0.0
 *
 * @package    Ays_Pb
 * @subpackage Ays_Pb/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ays_Pb
 * @subpackage Ays_Pb/admin
 * @author     AYS Pro LLC <info@ays-pro.com>
 */
class Ays_Pb_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $popupbox_obj;
    private $settings_obj;
    private $popup_categories_obj;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_filter('set-screen-option', array(__CLASS__, 'set_screen'), 10, 3);
        $per_page_array = array(
            'popupboxes_per_page',
            'popup_categories_per_page',
        );

        foreach($per_page_array as $option_name){
            add_filter('set_screen_option_'.$option_name, array(__CLASS__, 'set_screen'), 10, 3);
        }
    }

    public static function set_screen( $status, $option, $value ) {
        return $value;
    }

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles($hook_suffix) {
        wp_enqueue_style($this->plugin_name . '-admin', plugin_dir_url(__FILE__) . 'css/admin.css', array(), $this->version, 'all');
        wp_enqueue_style($this->plugin_name . '-sweetalert', plugin_dir_url(__FILE__) . '/css/ays-pb-sweetalert2.min.css', array(), $this->version, 'all');

        if(false === strpos($hook_suffix, $this->plugin_name))
            return;

        // Extended styles
        wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( $this->plugin_name . '-animate', plugin_dir_url( __FILE__ ) . 'css/animate.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '-bootstrap', plugin_dir_url( __FILE__ ) . 'css/bootstrap.min.css', array(), $this->version, 'all' );
        wp_enqueue_style( $this->plugin_name . '-select2', plugin_dir_url( __FILE__ ) . 'css/ays-pb-select2.min.css', array(), $this->version, 'all' );
        wp_enqueue_style( $this->plugin_name . '-jquery-datetimepicker', plugin_dir_url(__FILE__) . 'css/jquery-ui-timepicker-addon.css', array(), $this->version, 'all' );
        wp_enqueue_style( $this->plugin_name . '-codemirror', plugin_dir_url(__FILE__) . 'css/ays-pb-codemirror.css', array(), $this->version, 'all');
        wp_enqueue_style( $this->plugin_name . '-dropdown', plugin_dir_url(__FILE__) .  '/css/dropdown.min.css', array(), $this->version, 'all');
        wp_enqueue_style( $this->plugin_name . '-transition', plugin_dir_url(__FILE__) .  '/css/transition.min.css', array(), $this->version, 'all');

        // Manual styles
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ays-pb-admin.css', array(), $this->version, 'all' );
        wp_enqueue_style( $this->plugin_name. '-banner', plugin_dir_url( __FILE__ ) . 'css/ays-pb-banner.css', array(), $this->version, 'all' );
        wp_enqueue_style( $this->plugin_name . "-pro-features", plugin_dir_url( __FILE__ ) . 'css/ays-pb-pro-features.css', array(), time(), 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts($hook_suffix) {
        global $wp_version;

        if ( false !== strpos($hook_suffix, "plugins.php") ) {
            wp_enqueue_script( $this->plugin_name . '-sweetalert', plugin_dir_url(__FILE__) . '/js/ays-pb-sweetalert2.all.min.js', array('jquery'), $this->version, true );
            wp_enqueue_script( $this->plugin_name . '-admin', plugin_dir_url(__FILE__) . 'js/admin.js', array('jquery'), $this->version, true );
            wp_localize_script( $this->plugin_name . '-admin', 'popup_box_ajax', array('ajax_url' => admin_url('admin-ajax.php')) );
        }

        if(false === strpos($hook_suffix, $this->plugin_name))
            return;

        $wp_post_types = get_post_types('', 'objects');
        $all_post_types = array();
        foreach ($wp_post_types as $pt){
            $all_post_types[] = array(
                $pt->name,
                $pt->label
            );
        }

        $pb_banner_date = $this->ays_pb_update_banner_time();

        $pb_ajax_data = array(
            'ajax' => admin_url('admin-ajax.php'),
            'post_types' => $all_post_types,   
            'nextPopupPage' => __( 'Are you sure you want to go to the next popup page?', "ays-popup-box"),
            'prevPopupPage' => __( 'Are you sure you want to go to the previous popup page?', "ays-popup-box"),
            'AYS_PB_ADMIN_URL' => AYS_PB_ADMIN_URL,
            'AYS_PB_PUBLIC_URL' => AYS_PB_PUBLIC_URL,
            'addVideo' => __( "Add Video", "ays-popup-box" ),
            'editVideo' => __( "Edit Video", "ays-popup-box" ),
            'addImage' => __( "Add Image", "ays-popup-box" ),
            'editImage' => __( "Edit Image", "ays-popup-box" ),
            'pleaseEnterMore' => __( "Please select more", "ays-popup-box" ),
            'errorMsg' => __( "Error", "ays-popup-box" ),
            'somethingWentWrong' => __( "Maybe something went wrong.", "ays-popup-box" ),
            'activated' => __( "Activated", "ays-popup-box" ),
            'loadResource' => __( "Can't load resource.", "ays-popup-box" ),
            'pbBannerDate' => $pb_banner_date,
        );

        $color_picker_strings = array(
            'clear' => __( 'Clear', "ays-popup-box" ),
            'clearAriaLabel' => __( 'Clear color', "ays-popup-box" ),
            'defaultString' => __( 'Default', "ays-popup-box" ),
            'defaultAriaLabel' => __( 'Select default color', "ays-popup-box" ),
            'pick' => __( 'Select Color', "ays-popup-box" ),
            'defaultLabel' => __( 'Color value', "ays-popup-box" ),
        );

        // Extended scripts
        wp_enqueue_script( 'jquery-effects-core' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_media();
		wp_enqueue_script( $this->plugin_name . '-popper', plugin_dir_url(__FILE__) . '/js/popper.min.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( $this->plugin_name . '-bootstrap', plugin_dir_url(__FILE__) . '/js/bootstrap.min.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name . '-select2', plugin_dir_url(__FILE__) . '/js/select2.min.js', array('jquery'), $this->version, true );
        wp_enqueue_script( $this->plugin_name . '-sweetalert', plugin_dir_url(__FILE__) . '/js/ays-pb-sweetalert2.all.min.js', array('jquery'), $this->version, true );
        wp_enqueue_script( $this->plugin_name . '-jquery.datetimepicker', plugin_dir_url( __FILE__ ) . 'js/jquery-ui-timepicker-addon.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name . '-wp-color-picker-alpha', plugin_dir_url( __FILE__ ) . 'js/wp-color-picker-alpha.min.js',array( 'wp-color-picker' ),$this->version, true );
        wp_enqueue_script( $this->plugin_name . '-dropdown-min', plugin_dir_url(__FILE__) . '/js/dropdown.min.js', array('jquery'), $this->version, true );
        wp_enqueue_script( $this->plugin_name . '-transition-min', plugin_dir_url(__FILE__) . '/js/transition.min.js', array('jquery'), $this->version, true );
        wp_localize_script( $this->plugin_name . '-wp-color-picker-alpha', 'wpColorPickerL10n', $color_picker_strings );

        // Manual scripts
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ays-pb-admin.js', array( 'jquery', 'wp-color-picker' ), $this->version, false );
        wp_enqueue_script( $this->plugin_name . '-banner' , plugin_dir_url( __FILE__ ) . 'js/ays-pb-banner.js', array( 'jquery'), $this->version, false );
        wp_enqueue_script( $this->plugin_name . 'custom-dropdown-adapter', plugin_dir_url( __FILE__ ) . 'js/ays-select2-dropdown-adapter.js', array('jquery'), $this->version, true );
        wp_localize_script( $this->plugin_name, 'pb', $pb_ajax_data );

        if( Ays_Pb_Data::ays_version_compare( $wp_version, '>=', '5.5' ) ){
            wp_enqueue_script( $this->plugin_name . 'ays-wp-load-scripts', plugin_dir_url(__FILE__) . 'js/ays-wp-load-scripts.js', array(), $this->version, true );
        }
	}

    public function ays_pb_update_banner_time() {
        $date = time() + ( 3 * 24 * 60 * 60 ) + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS);
        $next_3_days = gmdate('M d, Y H:i:s', $date);

        $ays_pb_banner_time = get_option('ays_pb_banner_time');

        if ( !$ays_pb_banner_time || is_null($ays_pb_banner_time) ) {
            update_option('ays_pb_banner_time', $next_3_days );
        }

        $get_ays_pb_banner_time = get_option('ays_pb_banner_time');

        $val = 60*60*24*0.5; // half day
        // $val = 60; // for testing | 1 min

        $current_date = current_time('mysql');
        $date_diff = strtotime($current_date) - intval(strtotime($get_ays_pb_banner_time));

        $days_diff = $date_diff / $val;
        if (intval($days_diff) > 0) {
            update_option('ays_pb_banner_time', $next_3_days);
            $get_ays_pb_banner_time = get_option('ays_pb_banner_time');
        }

        return $get_ays_pb_banner_time;
    }

    /**
	 * De-register JavaScript files for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function disable_scripts($hook_suffix) {
        if (false !== strpos($hook_suffix, $this->plugin_name)) {
            if (is_plugin_active('ai-engine/ai-engine.php')) {
                wp_deregister_script('mwai');
                wp_deregister_script('mwai-vendor');
                wp_dequeue_script('mwai');
                wp_dequeue_script('mwai-vendor');
            }
        }
	}

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        add_menu_page(
            __('Popup Box', "ays-popup-box"),
            __('Popup Box', "ays-popup-box"),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_setup_page'),
            plugin_dir_url(__FILE__) . '/images/icons/popup-sidemenu-logo.svg',
            6
        );
    }

    public function add_plugin_popups_submenu() {
        $hook_popupbox = add_submenu_page(
            $this->plugin_name,
            __('Popups', "ays-popup-box"),
            __('Popups', "ays-popup-box"),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_setup_page')
        );

        add_action( "load-$hook_popupbox", array($this, 'screen_option_popupbox') );
        add_action( "load-$hook_popupbox", array($this, 'add_tabs') );
    }

    public function add_plugin_categories_submenu() {
        $hook_categories = add_submenu_page(
            $this->plugin_name,
            __('Categories', "ays-popup-box"),
            __('Categories', "ays-popup-box"),
            'manage_options',
            $this->plugin_name . '-categories',
            array($this, 'display_plugin_categories_page')
        );

        add_action( "load-$hook_categories", array($this, 'screen_option_categories') );
        add_action( "load-$hook_categories", array($this, 'add_tabs') );
    }

    public function add_plugin_custom_fields_submenu() {
        $hook_popup_attributes = add_submenu_page(
            $this->plugin_name,
            __('Custom Fields', "ays-popup-box"),
            __('Custom Fields', "ays-popup-box"),
            'manage_options',
            $this->plugin_name . '-attributes',
            array($this, 'display_plugin_attributes_page')
        );

        add_action( "load-$hook_popup_attributes", array($this, 'add_tabs') );
    }

    public function add_plugin_reports_submenu() {
        $hook_reports = add_submenu_page(
            $this->plugin_name,
            __('Analytics', "ays-popup-box"),
            __('Analytics', "ays-popup-box"),
            'manage_options',
            $this->plugin_name . '-reports',
            array($this, 'display_plugin_results_page')
        );

        add_action( "load-$hook_reports", array($this, 'add_tabs') );
    }

    public function add_plugin_subscribes_submenu() {
        $hook_subscribes = add_submenu_page(
            $this->plugin_name,
            __('Submissions', "ays-popup-box"),
            __('Submissions', "ays-popup-box"),
            'manage_options',
            $this->plugin_name . '-subscribes',
            array($this, 'display_plugin_subscribes_page')
        );

        add_action( "load-$hook_subscribes", array($this, 'add_tabs') );
    }

    public function add_plugin_export_import_submenu() {
        $hook_export_import = add_submenu_page(
            $this->plugin_name,
            __('Export/Import', "ays-popup-box"),
            __('Export/Import', "ays-popup-box"),
            'manage_options',
            $this->plugin_name . '-export-import',
            array($this, 'display_plugin_export_import_page')
        );

        add_action( "load-$hook_export_import", array($this, 'add_tabs') );
    }

    public function add_plugin_settings_submenu() {
        $hook_settings = add_submenu_page(
            $this->plugin_name,
            __('General Settings', "ays-popup-box"),
            __('General Settings', "ays-popup-box"),
            'manage_options',
            $this->plugin_name . '-settings',
            array($this, 'display_plugin_settings_page')
        );

        add_action( "load-$hook_settings", array($this, 'screen_option_settings') );
        add_action( "load-$hook_settings", array($this, 'add_tabs') );
    }

    public function add_plugin_how_to_use_submenu() {
        $hook_how_to_use = add_submenu_page(
            $this->plugin_name,
            __('How to use', "ays-popup-box"),
            __('How to use', "ays-popup-box"),
            'manage_options',
            $this->plugin_name . '-how-to-use',
            array($this, 'display_plugin_how_to_use_page')
        );

        add_action( "load-$hook_how_to_use", array($this, 'add_tabs') );
    }

    public function add_plugin_featured_plugins_submenu() {
        $hook_featured_plugins = add_submenu_page(
            $this->plugin_name,
            __('Our Products', "ays-popup-box"),
            __('Our Products', "ays-popup-box"),
            'manage_options',
            $this->plugin_name . '-featured-plugins',
            array($this, 'display_plugin_featured_plugins_page')
        );

        add_action("load-$hook_featured_plugins", array($this, 'add_tabs') );
    }

    public function add_plugin_pro_features_submenu() {
        $hook_pro_features = add_submenu_page(
            $this->plugin_name,
            __('PRO Features', "ays-popup-box"),
            __('PRO Features', "ays-popup-box"),
            'manage_options',
            $this->plugin_name . '-pb-features',
            array($this, 'display_plugin_pro_features_page')
        );

        add_action( "load-$hook_pro_features", array($this, 'add_tabs') );
    }

    public function display_plugin_setup_page() {
		$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';

        switch ($action) {
            case 'add':
            case 'edit':
                include_once('partials/actions/ays-pb-admin-actions.php');
                break;
            default:
                include_once('partials/ays-pb-admin-display.php');
                break;
        }
    }

    public function display_plugin_categories_page() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';

        switch ($action) {
            case 'add':
            case 'edit':
                include_once('partials/actions/ays-pb-categories-actions.php');
                break;
            default:
                include_once('partials/ays-pb-categories-display.php');
        }
    }

    public function display_plugin_attributes_page() {
        include_once('partials/attributes/actions/ays-pb-attributes-actions.php');
    }

    public function display_plugin_results_page() {
        include_once('partials/reports/ays-pb-reports-display.php');
    }

    public function display_plugin_subscribes_page() {
        include_once('partials/subscribes/ays-pb-subscribes-display.php');
    }

    public function display_plugin_export_import_page() {
        include_once('partials/export-import/ays-pb-export-import.php');
    }

    public function display_plugin_settings_page() {
        include_once('partials/settings/popup-box-settings.php');
    }

    public function display_plugin_how_to_use_page() {
        include_once('partials/how-to-use/ays-pb-how-to-use.php');
    }

    public function display_plugin_featured_plugins_page() {
        include_once('partials/features/ays-pb-plugin-featured-display.php');
    }

    public function display_plugin_pro_features_page() {
        include_once 'partials/features/popup-box-pro-features-display.php';
    }

    public function screen_option_popupbox() {
		$option = 'per_page';
		$args = array(
			'label' => __('PopupBox', "ays-popup-box"),
			'default' => 20,
			'option' => 'popupboxes_per_page'
		);

		add_screen_option($option, $args);
		$this->popupbox_obj = new Ays_PopupBox_List_Table($this->plugin_name);
        $this->settings_obj = new Ays_PopupBox_Settings_Actions($this->plugin_name);
	}

    public function screen_option_categories() {
        $option = 'per_page';
        $args = array(
            'label' => __('Categories', "ays-popup-box"),
            'default' => 20,
            'option' => 'popup_categories_per_page'
        );

        add_screen_option($option, $args);
        $this->popup_categories_obj = new Popup_Categories_List_Table($this->plugin_name);
        $this->settings_obj = new Ays_PopupBox_Settings_Actions($this->plugin_name);
    }

    public function screen_option_settings() {
        $this->settings_obj = new Ays_PopupBox_Settings_Actions($this->plugin_name);
    }

    public function add_tabs() {
		$screen = get_current_screen();

		if (!$screen) {
			return;
		}

		$screen->add_help_tab(
			array(
				'id' => 'popupbox_help_tab',
				'title' => __('General Information:', "ays-popup-box"),
				'content' =>
					'<h2>' . __('Popup Information', "ays-popup-box") . '</h2>' .
					'<p>'
						. __('The WordPress Popup plugin will help you to create engaging popups with fully customizable and responsive designs. Attract your audience and convert them into email subscribers or paying customers.  Construct advertising offers, generate more leads by creating option forms and subscription popups.',  "ays-popup-box") .
                    '</p>'
			)
		);

		$screen->set_help_sidebar(
			'<p><strong>' . __('For more information:', "ays-popup-box") . '</strong></p>' .
			'<p>
                <a href="https://www.youtube.com/watch?v=YSf6-icT2Ro&list=PL18_gEiPDg8Ocrbwn1SUjs2XaSZlgHpWj" target="_blank">' . __('Youtube video tutorials', "ays-popup-box") . '</a>
            </p>' .
			'<p>
                <a href="https://ays-pro.com/wordpress-popup-box-plugin-user-manual" target="_blank">' . __('Documentation: ', "ays-popup-box") . '</a>
            </p>' .
			'<p>
                <a href="https://ays-pro.com/wordpress/popup-box" target="_blank">' . __('Popup Box plugin Premium version:', "ays-popup-box") . '</a>
            </p>'
		);
	}

    public static function get_listtables_title_length($listtable_name) {
        global $wpdb;

        $settings_table = $wpdb->prefix . "ays_pb_settings";
        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT meta_value 
                 FROM {$settings_table}
                 WHERE meta_key = %s", 'options')
        );
        $options = ($result == "") ? array() : json_decode(stripcslashes($result), true);
        $listtable_title_length = 5;

        if (!empty($options)) {
            switch ($listtable_name) {
                case 'popups':
                    $listtable_title_length = (isset($options['popup_title_length']) && intval($options['popup_title_length']) != 0) ? absint( intval($options['popup_title_length']) ) : 5;
                    break;
                case 'categories':
                    $listtable_title_length = (isset($options['categories_title_length']) && intval($options['categories_title_length']) != 0) ? absint( intval($options['categories_title_length']) ) : 5;
                    break;
                default:
                    $listtable_title_length = 5;
                    break;
            }

            return $listtable_title_length;
        }

        return $listtable_title_length;
    }

    public static function ays_pb_restriction_string($type, $x, $length) {
        $output = "";

        switch($type) {
            case "char":
                if (strlen($x) <= $length) {
                    $output = $x;
                } else {
                    $output = substr($x, 0, $length) . '...';
                }
                break;
            case "word":
                $res = explode(" ", $x);
                if (count($res) <= $length) {
                    $output = implode(" ", $res);
                } else {
                    $res = array_slice($res, 0, $length);
                    $output = implode(" ", $res) . '...';
                }
                break;
        }

        return $output;
    }

    public static function validateDate($date, $format = 'Y-m-d H:i:s') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    // Code Mirror
    function codemirror_enqueue_scripts($hook) {
        if (false === strpos($hook, $this->plugin_name)) {
            return;
        }

        if(function_exists('wp_enqueue_code_editor')) {
            $cm_settings['codeEditor'] = wp_enqueue_code_editor(array(
                'type' => 'text/css',
                'codemirror' => array(
                    'inputStyle' => 'contenteditable',
                    'theme' => 'cobalt',
                )
            ));

            wp_enqueue_script('wp-theme-plugin-editor');
            wp_localize_script('wp-theme-plugin-editor', 'cm_settings', $cm_settings);

            wp_enqueue_style('wp-codemirror');
        }
    }

    /**
     * Add settings action link to the plugins page.
     *
     * @since    1.0.0
     */
    public function add_action_links( $links ) {
        /*
         *  Documentation : https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
         */
        $settings_link = array(
            '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_name ) . '">' . __('Settings', "ays-popup-box") . '</a>',
            '<a href="https://ays-demo.com/popup-box-plugin-free-demo/" target="_blank">' . __('Demo', "ays-popup-box") . '</a>',
            '<a id="ays-pb-plugins-buy-now-button" href="https://ays-pro.com/wordpress/popup-box?utm_source=dashboard&utm_medium=popup-free&utm_campaign=plugins-buy-now-button" target="_blank">' . __('Upgrade 30% Sale', "ays-popup-box") . '</a>',
        );
        return array_merge(  $settings_link, $links );

    }

    public function add_plugin_row_meta($meta, $file) {

        if ($file == AYS_PB_BASENAME) {
            $meta[] = '<a href="https://wordpress.org/support/plugin/ays-popup-box/" target="_blank">' . esc_html__( 'Free Support', "ays-popup-box" ) . '</a>';
        }

        return $meta;
    }

    public function deactivate_plugin_option() {
        error_reporting(0);

        $request_value = isset( $_REQUEST['upgrade_plugin'] ) ? sanitize_text_field( $_REQUEST['upgrade_plugin'] ) : '';

        $upgrade_option = get_option( 'ays_pb_upgrade_plugin', '' );

        if ( $upgrade_option === '' ) {
            add_option( 'ays_pb_upgrade_plugin', $request_value );
        } else {
            update_option( 'ays_pb_upgrade_plugin', $request_value );
        }

        $response = array(
            'option' => get_option( 'ays_pb_upgrade_plugin', '' ),
        );

        wp_send_json_success( $response );
    }

    public function get_selected_options_pb() {

        if (isset($_POST['data']) && !empty($_POST['data'])) {
            $posts = get_posts(array(
                'post_type'   => $_POST['data'],
                'post_status' => 'publish',
                'numberposts' => -1

            ));
        } else {
            $posts = array();
        }

        $arr = array();
        foreach ( $posts as $post ) {
            array_push($arr, array($post->ID, $post->post_title));

        }
        echo json_encode($arr);
        wp_die();
    }

    public function close_warning_note_permanently() {
        $cookie_expiration = time() + 60*60*24*30;
        setcookie('ays_pb_show_warning_note', 'ays_pb_show_warning_note_value', $cookie_expiration, '/');
    }

    public function ays_pb_create_author() {
        error_reporting(0);

        // Check for permissions.
		if ( !Ays_Pb_Data::check_user_capability() ) {
            ob_end_clean();
            $ob_get_clean = ob_get_clean();
            echo json_encode(array(
                'results' => array()
            ));
            wp_die();
        }

        $search = isset($_REQUEST['search']) && $_REQUEST['search'] != '' ? sanitize_text_field($_REQUEST['search']) : null;
        $checked = isset($_REQUEST['val']) && $_REQUEST['val'] != '' ? sanitize_text_field($_REQUEST['val']) : null;
        $args = array(
            'fields' => array('ID', 'display_name', 'user_email', 'user_login', 'user_nicename')
        );

        if ($search !== null) {
            $args['search'] = '*' . esc_attr($search) . '*';
            $args['search_columns'] = array('ID', 'user_login', 'user_nicename', 'user_email', 'display_name');
        }

        $user_query = new WP_User_Query($args);
        $users = $user_query->get_results();
        $response = array(
            'results' => array()
        );

        if(empty($args)){
            $reports_users = '';
        }

        foreach ($users as $key => $user) {
            if ($checked !== null) {
                if ($user->ID == $checked) {
                    continue;
                }else{
                    $response['results'][] = array(
                        'id' => $user->ID,
                        'text' => $user->display_name
                    );
                }
            }else{
                $response['results'][] = array(
                    'id' => $user->ID,
                    'text' => $user->display_name,
                );
            }
        }     

        ob_end_clean();
        echo json_encode($response);
        wp_die();
    }

    public function get_next_or_prev_row_by_id( $id, $type = "next", $table = "ays_pb" ) {
        global $wpdb;
    
        if ( is_null( $table ) || empty( $table ) ) {
            return null;
        }
    
        $ays_table = esc_sql( $wpdb->prefix . $table );
    
        $where = array();
        $where_condition = "";
    
        $id     = (isset( $id ) && $id != "" && absint($id) != 0) ? absint( sanitize_text_field( $id ) ) : null;
        $type   = (isset( $type ) && $type != "") ? sanitize_text_field( $type ) : "next";
    
        if ( is_null( $id ) || $id == 0 ) {
            return null;
        }
    
        switch ( $type ) {
            case 'prev':
                $where[] = ' `id` < ' . $id . ' ORDER BY `id` DESC ';
            break;
            case 'next':
            default:
                $where[] = ' `id` > ' . $id;
                break;
        }
    
        if( ! empty($where) ){
            $where_condition = " WHERE " . implode( " AND ", $where );
        }
    
        $sql = "SELECT `id` FROM {$ays_table} ". $where_condition ." LIMIT 1";
        $results = $wpdb->get_row( $sql, 'ARRAY_A' );
    
        return $results;
    
    }

    public function popup_box_admin_footer($a){
        if(isset($_REQUEST['page'])){
            if(false !== strpos($_REQUEST['page'], $this->plugin_name)){
                ?>
                <div class="ays-pb-footer-support-box">
                    <span class="ays-pb-footer-link-row"><a href="https://wordpress.org/support/plugin/ays-popup-box/" target="_blank"><?php echo __( "Support", "ays-popup-box"); ?></a></span>
                    <span class="ays-pb-footer-slash-row">/</span>
                    <span class="ays-pb-footer-link-row"><a href="https://ays-pro.com/wordpress-popup-box-plugin-user-manual" target="_blank"><?php echo __( "Docs", "ays-popup-box"); ?></a></span>
                    <span class="ays-pb-footer-slash-row">/</span>
                    <span class="ays-pb-footer-link-row"><a href="https://ays-demo.com/popup-box-plugin-survey/" target="_blank"><?php echo __( "Suggest a Feature", "ays-popup-box"); ?></a></span>
                </div>
                <p style="font-size:13px;text-align:center;font-style:italic;">
                    <span style="margin-left:0px;margin-right:10px;" class="ays_heart_beat"><img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/hearth.svg"?>"></span>
                    <span><?php echo __( "If you love our plugin, please do big favor and rate us on WordPress.org", "ays-popup-box"); ?></span> 
                    <a target="_blank" class="ays-rated-link" href='http://bit.ly/3kYanHL'>
                        <span class="ays-dashicons ays-dashicons-star-empty"></span>
                    	<span class="ays-dashicons ays-dashicons-star-empty"></span>
                    	<span class="ays-dashicons ays-dashicons-star-empty"></span>
                    	<span class="ays-dashicons ays-dashicons-star-empty"></span>
                    	<span class="ays-dashicons ays-dashicons-star-empty"></span>
                    </a>
                    <span class="ays_heart_beat"><img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/hearth.svg"?>"></span>
                </p>
            <?php
            }
        }
    }

    public function ays_pb_dismiss_button(){
        // Run a security check.
        check_ajax_referer( AYS_PB_NAME . '-sale-banner', sanitize_key( $_REQUEST['_ajax_nonce'] ) );

        // Check for permissions.
        if ( ! current_user_can( 'manage_options' ) ) {
            ob_end_clean();
            $ob_get_clean = ob_get_clean();
            echo json_encode(array(
                'status' => false,
            ));
            wp_die();
        }

        $data = array(
            'status' => false,
        );

        if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'ays_pb_dismiss_button') { 
            if( (isset( $_REQUEST['_ajax_nonce'] ) && wp_verify_nonce( $_REQUEST['_ajax_nonce'], AYS_PB_NAME . '-sale-banner' )) && current_user_can( 'manage_options' )){
                update_option('ays_pb_sale_btn', 1); 
                update_option('ays_pb_sale_date', current_time( 'mysql' ));
                $data['status'] = true;
            }
        }

        ob_end_clean();
        $ob_get_clean = ob_get_clean();
        echo json_encode($data);
        wp_die();

    }

    public static function ays_pb_check_if_current_image_exists($image_url) {
        global $wpdb;

        $res = true;
        if( !isset($image_url) ){
            $res = false;
        }

        if ( isset($image_url) && !empty( $image_url ) ) {

            $re = '/-\d+[Xx]\d+\./';
            $subst = '.';

            $image_url = preg_replace($re, $subst, $image_url, 1);

            $attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $image_url ));
            if ( is_null( $attachment ) || empty( $attachment ) ) {
                $res = false;
            }
        }

        return $res;
    }

    /**
     * Display the Our Products content.
     *
     * @since 4.7.0
     */
    public function ays_pb_output_our_products_content() {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $all_plugins = get_plugins();
        $am_plugins = $this->ays_pb_get_our_plugins();
        $can_install_plugins = self::ays_pb_can_install('plugin');
        $can_activate_plugins = self::ays_pb_can_activate('plugin');

        $content = '';
        $content.= '<div class="ays-pb-cards-block">';

        foreach ($am_plugins as $plugin => $details) {
            $plugin_data = $this->ays_pb_get_plugin_data($plugin, $details, $all_plugins);
            $plugin_ready_to_activate = $can_activate_plugins && isset($plugin_data['status_class']) && $plugin_data['status_class'] === 'status-installed';
            $plugin_not_activated = !isset($plugin_data['status_class']) || $plugin_data['status_class'] !== 'status-active';
            $plugin_action_class = ( isset($plugin_data['action_class']) && esc_attr($plugin_data['action_class']) != "" ) ? esc_attr($plugin_data['action_class']) : "";
            $plugin_action_class_disbaled = strpos($plugin_action_class, 'status-active') !== false ? "disbaled='true'" : "";

            $content .= '
                <div class="ays-pb-card">
                    <div class="ays-pb-card__content flexible">
                        <div class="ays-pb-card__content-img-box">
                            <img class="ays-pb-card__img" src="' . esc_url($plugin_data['details']['icon']) . '" alt="' . esc_attr($plugin_data['details']['name'] ) . '">
                        </div>
                        <div class="ays-pb-card__text-block">
                            <h5 class="ays-pb-card__title">' . esc_html($plugin_data['details']['name']) . '</h5>
                            <p class="ays-pb-card__text">' . wp_kses_post($plugin_data['details']['desc']) . '
                                <span class="ays-pb-card__text-hidden">
                                    ' . wp_kses_post($plugin_data['details']['desc_hidden']) . '
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="ays-pb-card__footer">';
                        if ($can_install_plugins || $plugin_ready_to_activate || !$details['wporg']) {
                            $content .= '<button class="' . esc_attr($plugin_data['action_class']) . '" data-plugin="' . esc_attr($plugin_data['plugin_src']) . '" data-type="plugin" ' . $plugin_action_class_disbaled . '>
                                ' . wp_kses_post($plugin_data['action_text']) . '
                                <span class="ays_pb_loader display_none"><img src=' . AYS_PB_ADMIN_URL . '/images/loaders/loading.gif></span>
                            </button>';
                        } elseif ($plugin_not_activated) {
                            $content .= '<a href="' . esc_url($details['wporg']) . '" target="_blank" rel="noopener noreferrer">
                                ' . esc_html_e('WordPress.org', "ays-popup-box") . '
                                <span aria-hidden="true" class="dashicons dashicons-external"></span>
                            </a>';
                        }
            $content .='
                        <a target="_blank" href="' . esc_url($plugin_data['details']['buy_now']) . '" class="ays-pb-card__btn-primary">' . __('Buy Now', "ays-popup-box") . '</a>
                    </div>
                </div>';
        }

        $install_plugin_nonce = wp_create_nonce($this->plugin_name . '-install-plugin-nonce');
        $content.= '<input type="hidden" id="ays_pb_ajax_install_plugin_nonce" name="ays_pb_ajax_install_plugin_nonce" value="' . $install_plugin_nonce . '">';
        $content.= '</div>';

        echo $content;
    }

    /**
     * List of AM plugins that we propose to install.
     *
     * @since 4.7.0
     *
     * @return array
     */
    protected function ays_pb_get_our_plugins() {
        if (!isset($_SESSION)) {
            session_start();
        }

        $images_url = AYS_PB_ADMIN_URL . '/images/icons/';
        $plugin_url_arr = array();

        $plugin_slug = array(
            'quiz-maker',
            'poll-maker',
            'survey-maker',
            'gallery-photo-gallery',
            'secure-copy-content-protection',
            'personal-dictionary',
            'chart-builder',
        );

        foreach ($plugin_slug as $key => $slug) {
            if ( isset($_SESSION['ays_pd_our_product_links']) && !empty($_SESSION['ays_pd_our_product_links']) && isset($_SESSION['ays_pd_our_product_links'][$slug]) && !empty($_SESSION['ays_pd_our_product_links'][$slug]) ) {
                $plugin_url = ( isset($_SESSION['ays_pd_our_product_links'][$slug]) && $_SESSION['ays_pd_our_product_links'][$slug] != "" ) ? esc_url($_SESSION['ays_pd_our_product_links'][$slug]) : "";
            } else {
                $latest_version = $this->ays_pb_get_latest_plugin_version($slug);
                $plugin_url = 'https://downloads.wordpress.org/plugin/' . $slug . '.zip';
                if ($latest_version != '') {
                    $plugin_url = 'https://downloads.wordpress.org/plugin/' . $slug . '.' . $latest_version . '.zip';
                    $_SESSION['ays_pd_our_product_links'][$slug] = $plugin_url;
                }
            }

            $plugin_url_arr[$slug] = $plugin_url;
        }

        $plugins_array = array(
           'quiz-maker/quiz-maker.php' => array(
                'icon' => $images_url . 'icon-quiz-128x128.png',
                'name' => __('Quiz Maker', "ays-popup-box"),
                'desc' => __('With our Quiz Maker plugin it’s easy to make a quiz in a short time.', "ays-popup-box"),
                'desc_hidden' => __('You to add images to your quiz, order unlimited questions. Also you can style your quiz to satisfy your visitors.', "ays-popup-box"),
                'wporg' => 'https://wordpress.org/plugins/quiz-maker/',
                'buy_now' => 'https://ays-pro.com/wordpress/quiz-maker/',
                'url' => $plugin_url_arr['quiz-maker'],
            ),
            'poll-maker/poll-maker-ays.php' => array(
                'icon' => $images_url . 'icon-poll-128x128.png',
                'name' => __('Poll Maker', "ays-popup-box"),
                'desc' => __('Create amazing online polls for your WordPress website super easily.', "ays-popup-box"),
                'desc_hidden' => __('Build up various types of polls in a minute and get instant feedback on any topic or product.', "ays-popup-box"),
                'wporg' => 'https://wordpress.org/plugins/poll-maker/',
                'buy_now' => 'https://ays-pro.com/wordpress/poll-maker/',
                'url' => $plugin_url_arr['poll-maker'],
            ),
            'survey-maker/survey-maker.php' => array(
                'icon' => $images_url . 'icon-survey-128x128.png',
                'name' => __('Survey Maker', "ays-popup-box"),
                'desc' => __('Make amazing online surveys and get real-time feedback quickly and easily.', "ays-popup-box"),
                'desc_hidden' => __('Learn what your website visitors want, need, and expect with the help of Survey Maker. Build surveys without limiting your needs.', "ays-popup-box"),
                'wporg' => 'https://wordpress.org/plugins/survey-maker/',
                'buy_now' => 'https://ays-pro.com/wordpress/survey-maker',
                'url' => $plugin_url_arr['survey-maker'],
            ),
            'gallery-photo-gallery/gallery-photo-gallery.php' => array(
                'icon' => $images_url . 'icon-gallery-128x128.png',
                'name' => __('Gallery Photo Gallery', "ays-popup-box"),
                'desc' => __('Create unlimited galleries and include unlimited images in those galleries.', "ays-popup-box"),
                'desc_hidden' => __('Represent images in an attractive way. Attract people with your own single and multiple free galleries from your photo library.', "ays-popup-box"),
                'wporg' => 'https://wordpress.org/plugins/gallery-photo-gallery/',
                'buy_now' => 'https://ays-pro.com/wordpress/photo-gallery/',
                'url' => $plugin_url_arr['gallery-photo-gallery'],
            ),
            'secure-copy-content-protection/secure-copy-content-protection.php' => array(
                'icon' => $images_url . 'icon-sccp-128x128.png',
                'name' => __('Secure Copy Content Protection', "ays-popup-box"),
                'desc' => __('Disable the right click, copy paste, content selection and copy shortcut keys on your website.', "ays-popup-box"),
                'desc_hidden' => __('Protect web content from being plagiarized. Prevent plagiarism from your website with this easy to use plugin.', "ays-popup-box"),
                'wporg' => 'https://wordpress.org/plugins/secure-copy-content-protection/',
                'buy_now' => 'https://ays-pro.com/wordpress/secure-copy-content-protection/',
                'url' => $plugin_url_arr['secure-copy-content-protection'],
            ),
            'personal-dictionary/personal-dictionary.php' => array(
                'icon' => $images_url . 'icon-pd-128x128.png',
                'name' => __('Personal Dictionary', 'quiz-maker' ),
                'desc' => __('Allow your students to create personal dictionary, study and memorize the words.', 'quiz-maker' ),
                'desc_hidden' => __('Allow your users to create their own digital dictionaries and learn new words and terms as fastest as possible.', 'quiz-maker' ),
                'wporg' => 'https://wordpress.org/plugins/personal-dictionary/',
                'buy_now' => 'https://ays-pro.com/wordpress/personal-dictionary/',
                'url' => $plugin_url_arr['personal-dictionary'],
            ),
            'chart-builder/chart-builder.php' => array(
                'icon' => $images_url . 'icon-chart-128x128.png',
                'name' => __('Chart Builder', "ays-popup-box"),
                'desc' => __('Chart Builder plugin allows you to create beautiful charts', "ays-popup-box"),
                'desc_hidden' => __(' and graphs easily and quickly.', "ays-popup-box"),
                'wporg' => 'https://wordpress.org/plugins/chart-builder/',
                'buy_now' => 'https://ays-pro.com/wordpress/chart-builder/',
                'url' => $plugin_url_arr['chart-builder'],
            ),
        );

        return $plugins_array;
    }

    protected function ays_pb_get_latest_plugin_version($slug) {
        if ( is_null($slug) || empty($slug) ) {
            return "";
        }

        $version_latest = "";

        if (!function_exists('plugins_api')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin-install.php');
        }

        // set the arguments to get latest info from repository via API ##
        $args = array(
            'slug' => $slug,
            'fields' => array(
                'version' => true,
            )
        );

        /** Prepare our query */
        $call_api = plugins_api('plugin_information', $args);

        /** Check for Errors & Display the results */
        if (is_wp_error($call_api)) {
            $api_error = $call_api->get_error_message();
        } else {
            //echo $call_api; // everything ##
            if (!empty($call_api->version)) {
                $version_latest = $call_api->version;
            }
        }

        return $version_latest;
    }

    /**
     * Determine if the plugin/addon installations are allowed.
     *
     * @since 4.7.0
     *
     * @param string $type Should be `plugin` or `addon`.
     *
     * @return bool
     */
    public static function ays_pb_can_install($type) {
        return self::ays_pb_can_do('install', $type);
    }

    /**
     * Determine if the plugin/addon activations are allowed.
     *
     * @since 4.7.0
     *
     * @param string $type Should be `plugin` or `addon`.
     *
     * @return bool
     */
    public static function ays_pb_can_activate($type) {
        return self::ays_pb_can_do('activate', $type);
    }

    /**
     * Determine if the plugin/addon installations/activations are allowed.
     *
     * @since 4.7.0
     *
     * @param string $what Should be 'activate' or 'install'.
     * @param string $type Should be `plugin` or `addon`.
     *
     * @return bool
     */
    public static function ays_pb_can_do($what, $type) {
        if ( !in_array($what, array('install', 'activate'), true) ) {
            return false;
        }

        if ($type != 'plugin') {
            return false;
        }

        $capability = $what . '_plugins';

        if (!current_user_can($capability)) {
            return false;
        }

        // Determine whether file modifications are allowed and it is activation permissions checking.
        if ( $what === 'install' && ! wp_is_file_mod_allowed('ays_pb_can_install') ) {
            return false;
        }

        // All plugin checks are done.
        if ($type === 'plugin') {
            return true;
        }

        return false;
    }

    /**
     * Get AM plugin data to display in the Our Products section.
     *
     * @since 4.7.0
     *
     * @param string $plugin      Plugin slug.
     * @param array  $details     Plugin details.
     * @param array  $all_plugins List of all plugins.
     *
     * @return array
     */
    protected function ays_pb_get_plugin_data($plugin, $details, $all_plugins) {
        $have_pro = (!empty($details['pro']) && !empty($details['pro']['plug']));
        $show_pro = false;
        $plugin_data = array();

        if ($have_pro) {
            if (array_key_exists($plugin, $all_plugins)) {
                if (is_plugin_active($plugin)) {
                    $show_pro = true;
                }
            }

            if (array_key_exists($details['pro']['plug'], $all_plugins)) {
                $show_pro = true;
            }

            if ($show_pro) {
                $plugin = $details['pro']['plug'];
                $details = $details['pro'];
            }
        }

        if (array_key_exists($plugin, $all_plugins)) {
            if ( is_plugin_active( $plugin ) ) {
                // Status text/status.
                $plugin_data['status_class'] = 'status-active';
                $plugin_data['status_text'] = esc_html__('Active', "ays-popup-box");
                // Button text/status.
                $plugin_data['action_class'] = $plugin_data['status_class'] . ' ays-pb-card__btn-info disabled';
                $plugin_data['action_text'] = esc_html__('Activated', "ays-popup-box");
                $plugin_data['plugin_src'] = esc_attr($plugin);
            } else {
                // Status text/status.
                $plugin_data['status_class'] = 'status-installed';
                $plugin_data['status_text'] = esc_html__('Inactive', "ays-popup-box");
                // Button text/status.
                $plugin_data['action_class'] = $plugin_data['status_class'] . ' ays-pb-card__btn-info';
                $plugin_data['action_text'] = esc_html__('Activate', "ays-popup-box");
                $plugin_data['plugin_src'] = esc_attr($plugin);
            }
        } else {
            // Doesn't exist, install.
            // Status text/status.
            $plugin_data['status_class'] = 'status-missing';

            if ( isset($details['act']) && 'go-to-url' === $details['act'] ) {
                $plugin_data['status_class'] = 'status-go-to-url';
            }

            $plugin_data['status_text'] = esc_html__('Not Installed', "ays-popup-box");
            // Button text/status.
            $plugin_data['action_class'] = $plugin_data['status_class'] . ' ays-pb-card__btn-info';
            $plugin_data['action_text'] = esc_html__('Install Plugin', "ays-popup-box");
            $plugin_data['plugin_src'] = esc_url($details['url']);
        }

        $plugin_data['details'] = $details;

        return $plugin_data;
    }

    /**
     * Activate plugin.
     *
     * @since 4.7.0
     */
    public function ays_pb_activate_plugin() {
        // Run a security check.
        check_ajax_referer( $this->plugin_name . '-install-plugin-nonce', sanitize_key($_REQUEST['_ajax_nonce']) );

        // Check for permissions.
        if (!current_user_can('activate_plugins')) {
            wp_send_json_error( esc_html__('Plugin activation is disabled for you on this site.', "ays-popup-box") );
        }

        if (isset($_POST['plugin'])) {
            $plugin = sanitize_text_field( wp_unslash($_POST['plugin']) );
            $activate = activate_plugins($plugin);

            if (!is_wp_error($activate)) {
                wp_send_json_success( esc_html__('Plugin activated.', "ays-popup-box") );
            }
        }

        wp_send_json_error( esc_html__('Could not activate the plugin. Please activate it on the Plugins page.', "ays-popup-box") );
    }

    /**
     * Install plugin.
     *
     * @since 4.7.0
     */
    public function ays_pb_install_plugin() {
        // Run a security check.
        check_ajax_referer( $this->plugin_name . '-install-plugin-nonce', sanitize_key($_REQUEST['_ajax_nonce']) );

        $generic_error = esc_html__('There was an error while performing your request.', "ays-popup-box");
        $type = !empty($_POST['type']) ? sanitize_key($_POST['type']) : 'plugin';

        // Check if new installations are allowed.
        if (!self::ays_pb_can_install($type)) {
            wp_send_json_error($generic_error);
        }

        $error = $type === 'plugin' ? esc_html__('Could not install the plugin. Please download and install it manually.', "ays-popup-box") : "";

        $plugin_url = !empty($_POST['plugin']) ? esc_url_raw( wp_unslash($_POST['plugin']) ) : '';

        if (empty($plugin_url)) {
            wp_send_json_error($error);
        }

        // Prepare variables.
        $url = esc_url_raw(
            add_query_arg(
                [
                    'page' => 'ays-pb-featured-plugins',
                ],
                admin_url('admin.php')
            )
        );

        ob_start();
        $creds = request_filesystem_credentials($url, '', false, false, null);

        // Hide the filesystem credentials form.
        ob_end_clean();

        // Check for file system permissions.
        if ($creds === false) {
            wp_send_json_error($error);
        }

        if (!WP_Filesystem($creds)) {
            wp_send_json_error($error);
        }

        /*
         * We do not need any extra credentials if we have gotten this far, so let's install the plugin.
         */
        require_once AYS_PB_DIR . 'includes/admin/class-ays-pb-upgrader.php';
        require_once AYS_PB_DIR . 'includes/admin/class-ays-pb-install-skin.php';
        require_once AYS_PB_DIR . 'includes/admin/class-ays-pb-skin.php';

        // Do not allow WordPress to search/download translations, as this will break JS output.
        remove_action( 'upgrader_process_complete', array('Language_Pack_Upgrader', 'async_upgrade'), 20 );

        // Create the plugin upgrader with our custom skin.
        $installer = new AysPb\Helpers\AysPbPluginSilentUpgrader( new Ays_Pb_Install_Skin() );

        // Error check.
        if (!method_exists($installer, 'install')) {
            wp_send_json_error($error);
        }

        $installer->install($plugin_url);

        // Flush the cache and return the newly installed plugin basename.
        wp_cache_flush();

        $plugin_basename = $installer->plugin_info();

        if (empty($plugin_basename)) {
            wp_send_json_error($error);
        }

        $result = array(
            'msg' => $generic_error,
            'is_activated' => false,
            'basename' => $plugin_basename,
        );

        // Check for permissions.
        if (!current_user_can('activate_plugins')) {
            $result['msg'] = $type === 'plugin' ? esc_html__('Plugin installed.', "ays-popup-box") : "";

            wp_send_json_success($result);
        }

        // Activate the plugin silently.
        $activated = activate_plugin($plugin_basename);
        remove_action( 'activated_plugin', array('ays_sccp_activation_redirect_method', 'gallery_p_gallery_activation_redirect_method', 'poll_maker_activation_redirect_method'), 100 );

        if (!is_wp_error($activated)) {
            $result['is_activated'] = true;
            $result['msg'] = esc_html__('Plugin installed and activated.', "ays-popup-box");

            wp_send_json_success($result);
        }

        // Fallback error just in case.
        wp_send_json_error($result);
    }
}
