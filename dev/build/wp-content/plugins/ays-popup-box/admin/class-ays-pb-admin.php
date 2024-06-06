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
            'pbBannerDate' => $pb_banner_date,
        );

        $color_picker_strings = array(
            'clear' => __( 'Clear', $this->plugin_name ),
            'clearAriaLabel' => __( 'Clear color', $this->plugin_name ),
            'defaultString' => __( 'Default', $this->plugin_name ),
            'defaultAriaLabel' => __( 'Select default color', $this->plugin_name ),
            'pick' => __( 'Select Color', $this->plugin_name ),
            'defaultLabel' => __( 'Color value', $this->plugin_name ),
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
        $next_3_days = date('M d, Y H:i:s', $date);

        $ays_pb_banner_time = get_option('ays_pb_banner_time');

        if ( !$ays_pb_banner_time || is_null( $ays_pb_banner_time ) ) {
            update_option('ays_pb_banner_time', $next_3_days ); 
        }

        $get_ays_pb_banner_time = get_option('ays_pb_banner_time');

        $val = 60*60*24*0.5; // half day
        // $val = 60; // for testing | 1 min

        $current_date = current_time( 'mysql' );
        $date_diff = strtotime($current_date) - intval(strtotime($get_ays_pb_banner_time));

        $days_diff = $date_diff / $val;
        if(intval($days_diff) > 0 ){
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
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        add_menu_page(
            __('Popup Box'),
            __('Popup Box'),
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
            __('Popups', $this->plugin_name),
            __('Popups', $this->plugin_name),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_setup_page')
        );

        add_action( "load-$hook_popupbox", array( $this, 'screen_option_popupbox' ) );
        add_action( "load-$hook_popupbox", array( $this, 'add_tabs' ));
    }

    public function add_tabs() {
		$screen = get_current_screen();

		if ( ! $screen) {
			return;
		}

		$screen->add_help_tab(
			array(
				'id'      => 'popupbox_help_tab',
				'title'   => __( 'General Information:
                    '),
				'content' =>
					'<h2>' . __( 'Popup Information', "ays-popup-box") . '</h2>' .
					'<p>' .
						__( 'The WordPress Popup plugin will help you to create engaging popups with fully customizable and responsive designs. Attract your audience and convert them into email subscribers or paying customers.  Construct advertising offers, generate more leads by creating option forms and subscription popups.',  "ays-popup-box" ).'</p>'
			)
		);

		$screen->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', "ays-popup-box") . '</strong></p>' .
			'<p>
                <a href=" https://www.youtube.com/watch?v=YSf6-icT2Ro&list=PL18_gEiPDg8Ocrbwn1SUjs2XaSZlgHpWj" target="_blank">' . __( 'Youtube video tutorials', "ays-popup-box" ) . '</a>
            </p>' .
			'<p>
                <a href="https://ays-pro.com/wordpress-popup-box-plugin-user-manual" target="_blank">' . __( 'Documentation: ', "ays-popup-box" ) . '</a>
            </p>' .
			'<p>
                <a href="https://ays-pro.com/wordpress/popup-box" target="_blank">' . __( 'Popup Box plugin Premium version:', "ays-popup-box" ) . '</a>
            </p>'
		);
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

        add_action( "load-$hook_categories", array( $this, 'screen_option_categories' ) );
        add_action( "load-$hook_categories", array( $this, 'add_tabs' ));
    }

    public function add_plugin_pro_features_submenu(){
        $hook_pro_features = add_submenu_page(
            $this->plugin_name,
            __('PRO Features', "ays-popup-box"),
            __('PRO Features', "ays-popup-box"),
            'manage_options',
            $this->plugin_name . '-pb-features',
            array($this, 'pb_display_plugin_pro_features_page')
        );

        add_action( "load-$hook_pro_features", array( $this, 'add_tabs' ));
    }

    public function add_plugin_reports_submenu(){
        $results_text = __('Analytics', "ays-popup-box");
        $hook_reports = add_submenu_page(
            $this->plugin_name,
            $results_text,
            $results_text,
            'manage_options',
            $this->plugin_name . '-reports',
            array($this, 'display_plugin_results_page')
        );

        add_action( "load-$hook_reports", array( $this, 'add_tabs' ));
    }

    public function add_plugin_custom_fields_submenu(){
        $hook_popup_attributes = add_submenu_page(
            $this->plugin_name,
            __('Custom Fields', "ays-popup-box"),
            __('Custom Fields', "ays-popup-box"),
            'manage_options',
            $this->plugin_name . '-attributes',
            array($this, 'display_plugin_attributes_page')
        );

        // add_action("load-$hook_popup_attributes", array($this, 'screen_option_attributes'));
        add_action("load-$hook_popup_attributes", array( $this, 'add_tabs' ));
    }

    public function add_plugin_export_import_submenu(){
        $results_text = __('Export/Import', "ays-popup-box");
        $hook_export_import = add_submenu_page(
            $this->plugin_name,
            $results_text,
            $results_text,
            'manage_options',
            $this->plugin_name . '-export-import',
            array($this, 'display_plugin_export_import_page')
        );

        add_action( "load-$hook_export_import", array( $this, 'add_tabs' ));
    }

    public function add_plugin_subscribes_submenu(){
        $results_text = __('Submissions', "ays-popup-box");
        $hook_subscribes = add_submenu_page(
            $this->plugin_name,
            $results_text,
            $results_text,
            'manage_options',
            $this->plugin_name . '-subscribes',
            array($this, 'display_plugin_subscribes_page')
        );

        add_action( "load-$hook_subscribes", array( $this, 'add_tabs' ));
    }


    public function add_plugin_settings_submenu(){
        $hook_settings = add_submenu_page( $this->plugin_name,
            __('General Settings', "ays-popup-box"),
            __('General Settings', "ays-popup-box"),
            'manage_options',
            $this->plugin_name . '-settings',
            array($this, 'display_plugin_settings_page') 
        );
        add_action("load-$hook_settings", array($this, 'screen_option_settings'));
        add_action( "load-$hook_settings", array( $this, 'add_tabs' ));
    }

    public function add_plugin_how_to_use_submenu(){
        $hook_how_to_use = add_submenu_page( $this->plugin_name,
            __('How to use', "ays-popup-box"),
            __('How to use', "ays-popup-box"),
            'manage_options',
            $this->plugin_name . '-how-to-use',
            array($this, 'display_plugin_how_to_use_page') 
        );
        add_action("load-$hook_how_to_use", array($this, 'screen_option_settings'));
        add_action( "load-$hook_how_to_use", array( $this, 'add_tabs' ));
    }

    public function add_plugin_featured_plugins_submenu(){
        $hook_featured_plugins = add_submenu_page( $this->plugin_name,
            __('Our Products', "ays-popup-box"),
            __('Our Products', "ays-popup-box"),
            'manage_options',
            $this->plugin_name . '-featured-plugins',
            array($this, 'display_plugin_featured_plugins_page') 
        );
        add_action( "load-$hook_featured_plugins", array( $this, 'add_tabs' ));
    }

    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_setup_page() {
		$action = (isset($_GET['action'])) ? sanitize_text_field( $_GET['action'] ) : '';

        switch ( $action ) {
            case 'add':
                include_once( 'partials/actions/ays-pb-admin-actions.php' );
                break;
            case 'edit':
                include_once( 'partials/actions/ays-pb-admin-actions.php' );
                break;
            default:
                include_once( 'partials/ays-pb-admin-display.php' );
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
            '<a id="ays-pb-plugins-buy-now-button" href="https://ays-pro.com/wordpress/popup-box?utm_source=dashboard&utm_medium=popup-free&utm_campaign=plugins-buy-now-button" target="_blank">' . __('Upgrade 20% Sale', "ays-popup-box") . '</a>',
        );
        return array_merge(  $settings_link, $links );

    }

    public function add_plugin_row_meta($meta, $file) {

        if ($file == AYS_PB_BASENAME) {
            $meta[] = '<a href="https://wordpress.org/support/plugin/ays-popup-box/" target="_blank">' . esc_html__( 'Free Support', "ays-popup-box" ) . '</a>';
        }

        return $meta;
    }

    public function display_plugin_categories_page(){
        $action = (isset($_GET['action'])) ? sanitize_text_field($_GET['action']) : '';

        switch ($action) {
            case 'add':
                include_once('partials/actions/ays-pb-categories-actions.php');
                break;
            case 'edit':
                include_once('partials/actions/ays-pb-categories-actions.php');
                break;
            default:
                include_once('partials/ays-pb-categories-display.php');
        }
    }

    public function display_plugin_attributes_page(){
        $action = (isset($_GET['action'])) ? sanitize_text_field($_GET['action']) : '';

        switch ($action) {
            case 'add':
                include_once('partials/attributes/actions/ays-pb-attributes-actions.php');
                break;
            case 'edit':
                include_once('partials/attributes/actions/ays-pb-attributes-actions.php');
                break;
            default:
                include_once('partials/attributes/actions/ays-pb-attributes-actions.php');
        }
    }

    public function pb_display_plugin_pro_features_page() {
        include_once 'partials/features/popup-box-pro-features-display.php';
    }

    public function display_plugin_settings_page(){        
        include_once('partials/settings/popup-box-settings.php');
    }

    public function display_plugin_how_to_use_page(){        
        include_once('partials/how-to-use/ays-pb-how-to-use.php');
    }

    public function display_plugin_results_page(){
        include_once('partials/reports/ays-pb-reports-display.php');
    }
    public function display_plugin_export_import_page(){
        include_once('partials/export-import/ays-pb-export-import.php');
    }
    public function display_plugin_subscribes_page(){
        include_once('partials/subscribes/ays-pb-subscribes-display.php');
    }
    public function display_plugin_featured_plugins_page(){
        include_once('partials/features/ays-pb-plugin-featured-display.php');
    }
	
	public function screen_option_popupbox() {
		$option = 'per_page';
		$args   = array(
			'label'   => __('PopupBox', "ays-popup-box"),
			'default' => 20,
			'option'  => 'popupboxes_per_page'
		);

		add_screen_option( $option, $args );
		$this->popupbox_obj = new Ays_PopupBox_List_Table($this->plugin_name);
        $this->settings_obj = new Ays_PopupBox_Settings_Actions($this->plugin_name);
	}

    public function screen_option_categories() {
        $option = 'per_page';
        $args   = array(
            'label'   => __('Categories', "ays-popup-box"),
            'default' => 20,
            'option'  => 'popup_categories_per_page'
        );

        add_screen_option($option, $args);
        $this->popup_categories_obj = new Popup_Categories_List_Table($this->plugin_name);
        $this->settings_obj = new Ays_PopupBox_Settings_Actions($this->plugin_name);
    }

    public function screen_option_settings() {
        $this->settings_obj = new Ays_PopupBox_Settings_Actions($this->plugin_name);
    }

    public static function validateDate($date, $format = 'Y-m-d H:i:s'){
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
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

        $search = isset($_REQUEST['search']) && $_REQUEST['search'] != '' ? $_REQUEST['search'] : null;
        $checked = isset($_REQUEST['val']) && $_REQUEST['val'] !='' ? $_REQUEST['val'] : null;
        $args = array();
        $arg = '';

        if($search !== null){
            $arg .= $search;
            $arg .= '*';
            $args['search'] = $arg;
        }
        
        $users = get_users( $args ); 
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

    public static function ays_pb_restriction_string($type, $x, $length){
        $output = "";
        switch($type){
            case "char":                
                if(strlen($x)<=$length){
                    $output = $x;
                } else {
                    $output = substr($x,0,$length) . '...';
                }
                break;
            case "word":
                $res = explode(" ", $x);
                if(count($res)<=$length){
                    $output = implode(" ",$res);
                } else {
                    $res = array_slice($res,0,$length);
                    $output = implode(" ",$res) . '...';
                }
            break;
        }
        return $output;
    }

    public static function get_listtables_title_length( $listtable_name ) {
        global $wpdb;

        $settings_table = $wpdb->prefix . "ays_pb_settings";
        $sql = "SELECT meta_value FROM ".$settings_table." WHERE meta_key = 'options'";
        $result = $wpdb->get_var($sql);
        $options = ($result == "") ? array() : json_decode(stripcslashes($result), true);
        $listtable_title_length = 5;
        if(! empty($options) ){
            switch ( $listtable_name ) {
                case 'popups':
                    $listtable_title_length = (isset($options['popup_title_length']) && intval($options['popup_title_length']) != 0) ? absint(intval($options['popup_title_length'])) : 5;
                    break; 
                case 'categories':
                    $listtable_title_length = (isset($options['categories_title_length']) && intval($options['categories_title_length']) != 0) ? absint(intval($options['categories_title_length'])) : 5;
                    break;
                default:
                    $listtable_title_length = 5;
                    break;
            }
            return $listtable_title_length;
        }
        return $listtable_title_length;
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
}
