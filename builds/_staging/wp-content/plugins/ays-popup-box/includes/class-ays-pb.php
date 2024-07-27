<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://ays-pro.com/
 * @since      1.0.0
 *
 * @package    Ays_Pb
 * @subpackage Ays_Pb/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Ays_Pb
 * @subpackage Ays_Pb/includes
 * @author     AYS Pro LLC <info@ays-pro.com>
 */
class Ays_Pb {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Ays_Pb_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined('AYS_PB_NAME_VERSION') ) {
			$this->version = AYS_PB_NAME_VERSION;
		} else {
			$this->version = '1.0.1';
		}
		$this->plugin_name = 'ays-pb';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_integrations_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Ays_Pb_Loader. Orchestrates the hooks of the plugin.
	 * - Ays_Pb_i18n. Defines internationalization functionality.
	 * - Ays_Pb_Admin. Defines all hooks for the admin area.
	 * - Ays_Pb_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

        if ( !class_exists('WP_List_Table') ) {
            require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
        }

		/**
		 * The class responsible for all plugin data
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ays-pb-data.php';

		/**
		 * The class responsible for defining all functions for getting all popup integrations
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ays-pb-integrations.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ays-pb-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ays-pb-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ays-pb-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ays-pb-public.php';

		/*
         * The class is responsible for showing popup boxes in wordpress default WP_LIST_TABLE style
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/lists/class-ays-pb-list-table.php';

        /**
		 * The class is responsible for showing popup box categories
		 */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/lists/class-ays-pb-popup-categories-list-table.php';

        /**
		 * The class is responsible for showing popup box settings
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/settings/popup-box-settings-actions.php';

        /**
		 * The class is responsible for showing User Information Shortdodes
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/class-ays-pb-user-information-shortcodes.php';

        /**
		 * The class is responsible for showing PB Categories Shortdodes
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/class-pb-category-shortcode.php';
		
		$this->loader = new Ays_Pb_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Ays_Pb_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Ays_Pb_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Ays_Pb_Admin( $this->get_plugin_name(), $this->get_version() );
		$data_admin = new Ays_Pb_Data( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'disable_scripts', 100 );

        // Add menu item
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );

		// Add Popups submenu
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_popups_submenu', 75 );

        // Add Popup categories
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_categories_submenu', 80 );

		// Add Popup attributes
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_custom_fields_submenu', 85 );

        // Add Reports submenu
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_reports_submenu', 90 );

        // Add Subscribes submenu
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_subscribes_submenu', 95 );

		// Add Export/Import submenu
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_export_import_submenu', 100 );

        // Add Settings submenu
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_settings_submenu', 105 );

        // Add How to use submenu
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_how_to_use_submenu', 110 );

        // Add Our Products submenu
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_featured_plugins_submenu', 115 );

        // Add Pro Features submenu
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_pro_features_submenu', 120 );

        // Add settings link to the plugin
        $plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_name . '.php' );
        $this->loader->add_filter( 'plugin_action_links_' . $plugin_basename, $plugin_admin, 'add_action_links' );

        // Add row meta link to the plugin
        $this->loader->add_filter( 'plugin_row_meta', $plugin_admin, 'add_plugin_row_meta', 10,2 );

		// Plugin deactivation funtionality
        $this->loader->add_action( 'wp_ajax_deactivate_plugin_option_pb', $plugin_admin, 'deactivate_plugin_option' );
        $this->loader->add_action( 'wp_ajax_nopriv_deactivate_plugin_option_pb', $plugin_admin , 'deactivate_plugin_option' );

		// Functionality for display option's post types suboption
        $this->loader->add_action( 'wp_ajax_get_selected_options_pb', $plugin_admin, 'get_selected_options_pb' );

        // Code Mirror
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'codemirror_enqueue_scripts' );

		// Add plugin footer
        $this->loader->add_action( 'in_admin_footer', $plugin_admin, 'popup_box_admin_footer', 1 );

		// Sale banner
		$this->loader->add_action( 'admin_notices', $data_admin, 'ays_pb_sale_baner', 1 );

		// Sale banner dismiss button
		$this->loader->add_action( 'wp_ajax_ays_pb_dismiss_button', $plugin_admin, 'ays_pb_dismiss_button' );
        $this->loader->add_action( 'wp_ajax_nopriv_ays_pb_dismiss_button', $plugin_admin, 'ays_pb_dismiss_button' );

		// Change the popup author option functionality
		$this->loader->add_action( 'wp_ajax_ays_pb_create_author', $plugin_admin, 'ays_pb_create_author' );
        $this->loader->add_action( 'wp_ajax_nopriv_ays_pb_create_author', $plugin_admin, 'ays_pb_create_author' );

		// Close popup and caches plugins conflict warning note for 1 month
		$this->loader->add_action( 'wp_ajax_close_warning_note_permanently', $plugin_admin, 'close_warning_note_permanently' );
        $this->loader->add_action( 'wp_ajax_nopriv_close_warning_note_permanently', $plugin_admin, 'close_warning_note_permanently' );
    }

	/**
	 * Register all of the hooks related to the integrations functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_integrations_hooks() {

		$plugin_integrations = new Popup_Box_Integrations( $this->get_plugin_name(), $this->get_version() );

		// Popup Box Integrations / popup page
		$this->loader->add_action( 'ays_pb_popup_page_integrations', $plugin_integrations, 'ays_popup_page_integrations_content' );		

		// Popup Box Integrations / settings page
		$this->loader->add_action( 'ays_pb_settings_page_integrations', $plugin_integrations, 'ays_settings_page_integrations_content' );

		// ===== MailChimp integration ====
			// MailChimp integration / popup page
			$this->loader->add_filter( 'ays_pb_popup_page_integrations_contents', $plugin_integrations, 'ays_popup_page_mailchimp_content', 1, 2 );

			// MailChimp integration / settings page
			$this->loader->add_filter( 'ays_pb_settings_page_integrations_contents', $plugin_integrations, 'ays_settings_page_mailchimp_content', 1, 2 );
		// ===== MailChimp integration ====

		// ===== Campaign Monitor integration =====
			// Campaign Monitor integration / popup page
			$this->loader->add_filter( 'ays_pb_popup_page_integrations_contents', $plugin_integrations, 'ays_popup_page_camp_monitor_content', 20, 2 );

			// Campaign Monitor integration / settings page
			$this->loader->add_filter( 'ays_pb_settings_page_integrations_contents', $plugin_integrations, 'ays_settings_page_campaign_monitor_content', 20, 2 );
		// ===== Campaign Monitor integration =====

		// ===== Active Campaign integration =====
			// Active Campaign integration / popup page
			$this->loader->add_filter( 'ays_pb_popup_page_integrations_contents', $plugin_integrations, 'ays_popup_page_active_camp_content', 30, 2 );

			// Active Campaign integration / settings page
			$this->loader->add_filter( 'ays_pb_settings_page_integrations_contents', $plugin_integrations, 'ays_settings_page_active_camp_content', 30, 2 );
		// ===== Active Campaign integration =====

		// ===== GetResponse integration ====
			// GetResponse integration / settings page
			$this->loader->add_filter( 'ays_pb_settings_page_integrations_contents', $plugin_integrations, 'ays_settings_page_get_response_content', 100, 2 );

			// GetResponse integration / popup page
			$this->loader->add_filter( 'ays_pb_popup_page_integrations_contents', $plugin_integrations, 'ays_popup_page_get_response_content', 100, 2 );
		// ===== GetResponse integration ====

		// ===== ConvertKit integration ====
			// ConvertKit integration / settings page
			$this->loader->add_filter( 'ays_pb_settings_page_integrations_contents', $plugin_integrations, 'ays_settings_page_convert_kit_content', 110, 2 );

			// ConvertKit integration / popup page
			$this->loader->add_filter( 'ays_pb_popup_page_integrations_contents', $plugin_integrations, 'ays_popup_page_convert_kit_content', 110, 2 );
		// ===== ConvertKit integration ====

		// ===== Sendinblue integration ====
			// Sendinblue integration / settings page
			$this->loader->add_filter( 'ays_pb_settings_page_integrations_contents', $plugin_integrations, 'ays_settings_page_sendinblue_content', 115, 2 );

			// Sendinblue integration / popup page
			$this->loader->add_filter( 'ays_pb_popup_page_integrations_contents', $plugin_integrations, 'ays_popup_page_sendinblue_content', 115, 2 );
		// ===== Sendinblue integration ====

		// ===== MailerLite integration ====
			// MailerLite integration / settings page
			$this->loader->add_filter( 'ays_pb_settings_page_integrations_contents', $plugin_integrations, 'ays_settings_page_mailerLite_content', 120, 2 );

			// MailerLite integration / popup page
			$this->loader->add_filter( 'ays_pb_popup_page_integrations_contents', $plugin_integrations, 'ays_popup_page_mailerLite_content', 120, 2 );
		// ===== MailerLite integration ====
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Ays_Pb_Public( $this->get_plugin_name(), $this->get_version() );
		$plugin_public_user_information = new Ays_Popup_Box_User_Information_Public( $this->get_plugin_name(), $this->get_version() );
		$plugin_public_category = new Popup_Box_Popup_Category( $this->get_plugin_name(), $this->get_version() );

		/*
		 * Generating shortcode on init action
		 */
		$this->loader->add_action( 'init', $plugin_public, 'ays_generate_shortcode' );

		// Display popups
		$this->loader->add_action( 'wp_footer', $plugin_public, 'ays_shortcodes_show_all' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_footer', $plugin_public, 'enqueue_styles_footer' );

		// Set cookie for display popup once per user option
        $this->loader->add_action( 'wp_ajax_ays_pb_set_cookie_only_once', $plugin_public, 'ays_pb_set_cookie_only_once' );
        $this->loader->add_action( 'wp_ajax_nopriv_ays_pb_set_cookie_only_once', $plugin_public , 'ays_pb_set_cookie_only_once' );

		// Increment popup views on each popup displaying
		$this->loader->add_action( 'wp_ajax_ays_increment_pb_views', $plugin_public, 'ays_increment_pb_views' );
        $this->loader->add_action( 'wp_ajax_nopriv_ays_increment_pb_views', $plugin_public , 'ays_increment_pb_views' );

		// Increment popup conversions
		$this->loader->add_action( 'wp_ajax_ays_increment_pb_conversions', $plugin_public, 'ays_increment_pb_conversions' );
        $this->loader->add_action( 'wp_ajax_nopriv_ays_increment_pb_conversions', $plugin_public , 'ays_increment_pb_conversions' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Ays_Pb_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
