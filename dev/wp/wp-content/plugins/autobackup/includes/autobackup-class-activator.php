<?php
if ( ! defined( 'ABSPATH' ) ){ exit; }
/**
 * Fired during plugin activation
 *
 * @link       https://www.autobackup.io/
 * @since      1.0.0
 *
 * @package    Auto_Backup
 * @subpackage Auto_Backup/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Auto_Backup
 * @subpackage Auto_Backup/includes
 * @author     Auto Backup <plugin@autobackup.io>
 */
class Auto_Backup_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		
		if (!is_dir(ABSPATH . 'wp-content/autobackups')) {
			mkdir(ABSPATH . 'wp-content/autobackups', 0777, TRUE);
		}
		
		if (!is_dir(ABSPATH . 'wp-content/autobackups/backups')) {
			mkdir(ABSPATH . 'wp-content/autobackups/backups', 0777, TRUE);
		}
		
		if (!is_dir(ABSPATH . 'wp-content/autobackups/backups-info')) {
			mkdir(ABSPATH . 'wp-content/autobackups/backups-info', 0777, TRUE);
		}
		if (!is_dir(ABSPATH . 'wp-content/autobackups/temp')) {
			mkdir(ABSPATH . 'wp-content/autobackups/temp', 0777, TRUE);
		}

	}

}
