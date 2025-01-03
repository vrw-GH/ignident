<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       http://ays-pro.com/
 * @since      1.0.0
 *
 * @package    Ays_Pb
 */

// If uninstall not called from WordPress, then exit.
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if (get_option('ays_pb_upgrade_plugin','false') === 'false') {
    global $wpdb;
    $pb_table = $wpdb->prefix . 'ays_pb';
    $pb_categories = $wpdb->prefix . 'ays_pb_categories';
    $settings_table = $wpdb->prefix . 'ays_pb_settings';

    $wpdb->query("DROP TABLE IF EXISTS `" . $pb_table . "`");
    $wpdb->query("DROP TABLE IF EXISTS `" . $pb_categories . "`");
    $wpdb->query("DROP TABLE IF EXISTS `" . $settings_table . "`");

    delete_option("ays_pb_db_version");
    delete_option("ays_pb_upgrade_plugin");
    delete_option("ays_pb_sale_date");
    delete_option("ays_pb_sale_btn");
    delete_option("ays_pb_banner_time");
}