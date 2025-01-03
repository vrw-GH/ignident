<?php
if ( ! defined( 'ABSPATH' ) ){ exit; }
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.autobackup.io/
 * @since      1.0.0
 *
 * @package    Auto_Backup
 * @subpackage Auto_Backup/includes
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
 * @package    Auto_Backup
 * @subpackage Auto_Backup/includes
 * @author     Auto Backup <plugin@autobackup.io>
 */
class Auto_Backup {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Auto_Backup_Loader    $loader    Maintains and registers all hooks for the plugin.
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
		if ( defined( 'AUTO_BACKUP_VERSION' ) ) {
			$this->version = AUTO_BACKUP_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'autobackup';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Auto_Backup_Loader. Orchestrates the hooks of the plugin.
	 * - Auto_Backup_i18n. Defines internationalization functionality.
	 * - Auto_Backup_Admin. Defines all hooks for the admin area.
	 * - Auto_Backup_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/autobackup-class-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/autobackup-class-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/autobackup-class-admin.php';
		
		/**
		 * Class responsible for Dropbox API
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/autobackup-class-db-api.php';
		
		/**
		 * Class responsible for Google Drive API
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/autobackup-class-gdrive-api.php';
		
		/**
		 * Class responsible for AWS S3 API
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/autobackup-class-s3-api.php';
		
		/**
		 * Class responsible for AWS NeevCloud API
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/autobackup-class-neevcloud-api.php';
		
		/**
		 * Class responsible for FTP API
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/autobackup-class-ftp-api.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		$this->loader = new Auto_Backup_Loader();

	}
  
	/** 
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Auto_Backup_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Auto_Backup_i18n();

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

		$plugin_admin = new Auto_Backup_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'auto_backup_custom_menu_page' );
		
		$this->loader->add_action( 'wp_ajax_auto_backup_create_ajax', $plugin_admin, 'auto_backup_create_ajax' );
		$this->loader->add_action( 'wp_ajax_auto_backup_delete_ajax', $plugin_admin, 'auto_backup_delete_ajax' );
		$this->loader->add_action( 'wp_ajax_auto_backup_restore_ajax', $plugin_admin, 'auto_backup_restore_ajax' );
		$this->loader->add_action( 'wp_ajax_auto_backup_save_storage_data', $plugin_admin, 'auto_backup_save_storage_data' );
		$this->loader->add_action( 'wp_ajax_auto_backup_download_file', $plugin_admin, 'auto_backup_download_file' );
		$this->loader->add_action( 'wp_ajax_auto_backup_save_scheduled_data', $plugin_admin, 'auto_backup_save_scheduled_data' );
		$this->loader->add_action( 'wp_ajax_auto_backup_import_data', $plugin_admin, 'auto_backup_import_data' );
		$this->loader->add_action( 'wp_ajax_auto_backup_sortingbydate', $plugin_admin, 'auto_backup_sortingbydate' );
		$this->loader->add_action( 'wp_ajax_auto_backup_sortingbysize', $plugin_admin, 'auto_backup_sortingbysize' );
		
		$this->loader->add_action( 'wp_ajax_auto_backup_delete_schedule', $plugin_admin, 'auto_backup_delete_schedule' );
		
		$this->loader->add_action('admin_footer', $plugin_admin, 'auto_backup_admin_footer');
		
		$this->loader->add_action( 'auto_backup_sheduled_databaase_hook', $plugin_admin, 'auto_backup_scheduled_db_backup' );
		
		$this->loader->add_action( 'auto_backup_sheduled_files_hook', $plugin_admin, 'auto_backup_scheduled_files_backup' );
		
		$this->loader->add_filter( 'cron_schedules', $plugin_admin, 'auto_backup_schedule_filter' );

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
	 * @return    Auto_Backup_Loader    Orchestrates the hooks of the plugin.
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
	
	/**
	 * wpwebp convert bytes
	 */
	public function auto_backup_beautify_bytes($bytes){
		if ($bytes >= 1073741824)
		{
			$bytes = number_format($bytes / 1073741824, 2) . ' GB';
		}
		elseif ($bytes >= 1048576)
		{
			$bytes = number_format($bytes / 1048576, 2) . ' MB';
		}
		elseif ($bytes >= 1024)
		{
			$bytes = number_format($bytes / 1024, 2) . ' KB';
		}
		elseif ($bytes > 1)
		{
			$bytes = $bytes . ' bytes';
		}
		elseif ($bytes == 1)
		{
			$bytes = $bytes . ' byte';
		}
		return $bytes;
	}

} 