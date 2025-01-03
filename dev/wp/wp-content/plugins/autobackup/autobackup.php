<?php
if ( ! defined( 'ABSPATH' ) ){ exit; }
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.autobackup.io
 * @since             1.0.0
 * @package           Auto_Backup
 *
 * @wordpress-plugin
 * Plugin Name:       Auto Backup
 * Plugin URI:        https://www.autobackup.io
 * Description:       WordPress plugin for backup and restoration with cloud storage like NeevCloud, Google Drive, DropBox, AWS S3, FTP etc.
 * Version:           1.0.3
 * Author:            Autobackup
 * Author URI:        https://www.autobackup.io
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       autobackup
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'AUTO_BACKUP_VERSION', '1.0.3' );
define( 'AUTO_BACKUP_PATH', WP_PLUGIN_DIR . '/autobackup' );
define( 'AUTO_BACKUP_URL', WP_PLUGIN_URL . '/autobackup' );
define( 'AUTO_BACKUP_DIR', WP_CONTENT_DIR . '/autobackups' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/autobackup-class-activator.php
 */
function auto_backup_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/autobackup-class-activator.php';
	Auto_Backup_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/autobackup-class-deactivator.php
 */
function auto_backup_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/autobackup-class-deactivator.php';
	Auto_Backup_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'auto_backup_activate' );
register_deactivation_hook( __FILE__, 'auto_backup_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/autobackup-class.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function auto_backup_run() {
   $plugin = new Auto_Backup();
   $plugin->run();
}
auto_backup_run();  