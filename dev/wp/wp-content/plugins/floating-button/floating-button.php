<?php
/**
 *  Plugin Name:       Floating Button
 *  Plugin URI:        https://wow-estore.com/item/floating-button-pro/
 *  Description:       Easily Generate and manage sticky Floating Buttons.
 *  Version:           7.0
 *  Author:            Wow-Company
 *  Author URI:        https://wow-estore.com/
 *  License:           GPL-2.0+
 *  License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *  Text Domain:       floating-button
 *  Domain Path:       /languages
 *  Item ID:           25955
 *  Store URI:         https://wow-estore.com/
 *  Author Email:      hey@wow-company.com
 *  Plugin Menu:       Floating Button
 *  Rating URI:
 *  Rating URI:        https://wordpress.org/support/plugin/floating-button/reviews/?filter=5#new-post
 *  Support URI:       https://wordpress.org/support/plugin/floating-button/
 *  Item URI:          https://wow-estore.com/item/floating-button-pro/
 *  Demo URI:          https://demo.wow-estore.com/floating-button-pro/
 *  Change URI:        https://wordpress.org/plugins/floating-button/#developers
 *
 *  PHP version        7.4
 *
 * @category    Wordpress_Plugin
 * @package     Wow_Plugin
 * @author      Dmytro Lobov <hey@wow-company.com>, Wow-Company
 * @copyright   2024 Dmytro Lobov
 * @license     GPL-2.0+
 */

namespace FloatingButton;

use FloatingButton\Admin\DBManager;
use FloatingButton\Update\UpdateDB;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WOWP_Plugin' ) ) :

	final class WOWP_Plugin {

		// Plugin slug
		public const SLUG = 'floating-button';

		// Plugin prefix
		public const PREFIX = 'wow_fbtnp';

		// Plugin Shortcode
		public const SHORTCODE = 'Floating-Button';

		public const DEVMODE = false;

		private static $instance;

		private WOWP_Admin $admin;
		private Autoloader $autoloader;
		private WOWP_Public $public;

		public static function instance(): WOWP_Plugin {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
				self::$instance = new self;

				self::$instance->includes();

				self::$instance->autoloader = new Autoloader( 'FloatingButton' );
				self::$instance->admin      = new WOWP_Admin();
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
				'name'       => 'Plugin Name',
				'version'    => 'Version',
				'url'        => 'Plugin URI',
				'author'     => 'Author',
				'email'      => 'Author Email',
				'store'      => 'Store URI',
				'item_id'    => 'Item ID',
				'menu_title' => 'Plugin Menu',
				'rating'     => 'Rating URI',
				'support'    => 'Support URI',
				'pro'        => 'Item URI',
				'demo'       => 'Demo URI',
				'change'     => 'Change URI',
				'docs'       => 'Documentation',
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
			require_once self::dir() . 'admin/class-wowp-admin.php';
			require_once self::dir() . 'public/class-wowp-public.php';
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

			if ( is_plugin_active( 'floating-button-pro/floating-button-pro.php' ) ) {
				deactivate_plugins( 'floating-button-pro/floating-button-pro.php' );
			}

			$columns = "
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			title VARCHAR(200),
			param longtext,
			status boolean DEFAULT 0 NOT NULL,
			mode boolean DEFAULT 0 NOT NULL,
			tag text,
			PRIMARY KEY  (id)
			";

			DBManager::create( $columns );

			update_site_option( self::PREFIX . '_db_version', '7.0' );
		}

		/**
		 * Download the folder with languages.
		 *
		 * @access Publisher
		 * @return void
		 */
		public function loaded(): void {
			UpdateDB::init();
		}
	}

endif;

function wp_plugin_run(): WOWP_Plugin {
	return WOWP_Plugin::instance();
}

wp_plugin_run();
