<?php
/**
 * Plugin Name:       Floating Button
 * Plugin URI:        https://wow-estore.com/item/floating-button-pro/
 * Description:       Easily Generate and manage sticky Floating Buttons.
 * Version:           6.0.11
 * Author:            Wow-Company
 * Author URI:        https://wow-estore.com/
 * Author Email:      hey@wow-company.com
 * Item ID:           25955
 * Store URI:         https://wow-estore.com/
 * Support URI:       https://wordpress.org/support/plugin/floating-button/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       floating-button
 * Domain Path:       /languages
 *
 * PHP version 7.4
 *
 * @category    Wordpress_Plugin
 * @package     Wow_Plugin
 * @author      Wow-Company <yoda@wow-company.com>
 * @copyright   2019 Wow-Company
 * @license     GNU Public License
 * @version     1.1
 */

// Required set the namespace for plugin.
namespace FloatingButton;

// Exit if accessed directly.
use FloatingButton\Dashboard\DBManager;
use FloatingButton\Update\UpdateDB;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WOW_Plugin' ) ) :

	final class WOW_Plugin {

		// Plugin slug
		public const SLUG = 'floating-button';

		// Plugin prefix
		public const PREFIX = 'wow_fbtnp';

		// Plugin Shortcode
		public const SHORTCODE = 'Floating-Button';

		private static $instance;
		/**
		 * @var Autoloader
		 */
		private $autoloader;
		/**
		 * @var Wow_Dashboard
		 */
		private $dashboard;
		private $public;

		public static function instance(): WOW_Plugin {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WOW_Plugin ) ) {
				self::$instance = new self;

				self::$instance->includes();
				self::$instance->autoloader = new Autoloader( 'FloatingButton' );
				self::$instance->dashboard  = new WOWP_Dashboard();
				self::$instance->public     = new WOWP_Public();

				register_activation_hook( __FILE__, [ self::$instance, 'plugin_activate' ] );
				add_action( 'plugins_loaded', [ self::$instance, 'loaded' ] );
			}


			return self::$instance;
		}

		// Plugin Root File.
		public static function file(): string {
			return __FILE__;
		}

		// Plugin Base Name.
		public static function basename(): string {
			return plugin_basename( __FILE__ );
		}

		// Plugin Folder Path.
		public static function dir(): string {
			return plugin_dir_path( __FILE__ );
		}

		// Plugin Folder URL.
		public static function url(): string {
			return plugin_dir_url( __FILE__ );
		}

		// Get Plugin Info
		public static function info( $show = '' ): string {
			$data        = [
				'name'    => 'Plugin Name',
				'version' => 'Version',
				'url'     => 'Plugin URI',
				'author'  => 'Author',
				'email'   => 'Author Email',
				'store'   => 'Store URI',
				'item_id' => 'Item ID',
				'support' => 'Support URI'
			];
			$plugin_data = get_file_data( __FILE__, $data, false );

			return $plugin_data[ $show ] ?? '';
		}

		/**
		 * Include required files.
		 *
		 * @access private
		 * @since  1.0
		 */
		private function includes(): void {
			if ( ! class_exists( 'Wow_Company' ) ) {
				require_once self::dir() . 'includes/class-wow-company.php';
			}

			require_once self::dir() . 'classes/Autoloader.php';
			require_once self::dir() . 'includes/class-wowp-dashboard.php';
			require_once self::dir() . 'includes/class-wowp-public.php';
		}

		/**
		 * Throw error on object clone.
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
		 * @return void
		 * @access protected
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_attr__( 'Cheatin&#8217; huh?', 'floating-button' ), '1.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @return void
		 * @access protected
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_attr__( 'Cheatin&#8217; huh?', 'floating-button' ), '1.0' );
		}

		public function plugin_activate(): void {
			$columns = "
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			title VARCHAR(200) NOT NULL,
			param LONGTEXT,
			status BOOLEAN,
			mode BOOLEAN,
			tag TEXT,
			UNIQUE KEY id (id)
			";
			DBManager::create( $columns );

			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			if ( is_plugin_active( 'floating-button-pro/floating-button-pro.php' ) ) {
				deactivate_plugins( 'floating-button-pro/floating-button-pro.php' );
			}
		}

		/**
		 * Download the folder with languages.
		 *
		 * @access Publisher
		 * @return void
		 */
		public function loaded(): void {
			UpdateDB::init();
			$languages_folder = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
			load_plugin_textdomain( 'floating-button', false, $languages_folder );
		}
	}

endif;

function wow_plugin_run() {
	return WOW_Plugin::instance();
}

wow_plugin_run();