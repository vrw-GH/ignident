<?php
/**
 * Uninstall PDF Poster
 *
 * Fired when the plugin is uninstalled (deleted) via the WordPress admin.
 * Removes all database tables and options created by the plugin.
 *
 * @package PDFPro
 */

// Exit if not called from WordPress uninstall context.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Run the uninstall cleanup processes.
 */
function pdfp_uninstall_plugin() {
	global $wpdb;

	// -----------------------------------------------------------------
	// 1. Drop custom database tables created by the plugin.
	// -----------------------------------------------------------------
	$tables = [
		$wpdb->prefix . 'pdfposter_presets',
	];

	foreach ( $tables as $table ) {
		$table = sanitize_key( $table );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query( 'DROP TABLE IF EXISTS `' . esc_sql( $table ) . '`' );
	}

	// -----------------------------------------------------------------
	// 2. Delete plugin options stored in wp_options.
	// -----------------------------------------------------------------
	$options = [
		'fpdf_option',
		'pdfposter_presets_database_version',
	];

	foreach ( $options as $option ) {
		delete_option( $option );
	}

	// -----------------------------------------------------------------
	// 3. Remove all post meta added by the plugin from pdfposter posts.
	// -----------------------------------------------------------------
	$meta_keys = [
		'isGutenberg',
		'_pdfp_pdf_source',
		'_pdfp_settings',
	];

	foreach ( $meta_keys as $meta_key ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		$wpdb->delete( $wpdb->postmeta, [ 'meta_key' => $meta_key ] );
	}
}

pdfp_uninstall_plugin();

