<?php
/**
 * DBManager class for Floating Button plugin.
 *
 * @package FloatingButton\Admin
 *
 *  Methods:
 *  - create()                Create database table
 *  - get_columns()           Get table column structure
 *  - insert()                Insert new row
 *  - update()                Update existing row
 *  - delete()                Delete row by ID
 *  - remove_item()           Handle item removal from GET request
 *  - get_all_data()          Get all rows from table
 *  - get_data_by_id()        Get single row by ID
 *  - get_data_by_title()     Get row by title
 *  - get_param_id()          Get and unserialize param by ID
 *  - check_row()             Check if row exists by ID
 *  - get_tags_from_table()   Get unique tags
 *  - display_tags()          Output HTML <option> tags for tags
 */

namespace FloatingButton\Dashboard;

defined( 'ABSPATH' ) || exit;

use FloatingButton\WOW_Plugin;

class DBManager {

	/**
	 * Create database table.
	 */
	public static function create( $columns ): void {
		global $wpdb;

		$table           = $wpdb->prefix . WOW_Plugin::PREFIX;
		$charset_collate = $wpdb->get_charset_collate();
		$sql             = "CREATE TABLE {$table} ($columns) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Get table columns.
	 */
	public static function get_columns() {
		global $wpdb;
		$table = esc_sql( $wpdb->prefix . WOW_Plugin::PREFIX );

		return $wpdb->get_results( "DESCRIBE {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Insert new row.
	 */
	public static function insert( $data, $data_formats ) {
		global $wpdb;
		$table = esc_sql( $wpdb->prefix . WOW_Plugin::PREFIX );

		$wpdb->insert( $table, $data, $data_formats ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		return $wpdb->insert_id ?: false;
	}

	/**
	 * Update row.
	 */
	public static function update( $data, $where, $data_formats ): void {
		global $wpdb;
		$table = esc_sql( $wpdb->prefix . WOW_Plugin::PREFIX );

		$wpdb->update( $table, $data, $where, $data_formats ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Delete row by ID.
	 */
	public static function delete( $id ) {
		if ( empty( $id ) ) {
			return false;
		}

		global $wpdb;
		$table = $wpdb->prefix . WOW_Plugin::PREFIX;

		return $wpdb->delete( $table, [ 'id' => $id ], [ '%d' ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Remove item via GET request with nonce verification.
	 */
	public static function remove_item() {
		if ( ! AdminActions::verify( WOW_Plugin::PREFIX . '_remove_item' ) ) {
			return false;
		}
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$page   = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
		$id     = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : '';
		// phpcs:enable

		if ( ( $page !== WOW_Plugin::SLUG ) || ( $action !== 'delete' ) || empty( $id ) ) {
			return false;
		}

		global $wpdb;
		$table  = $wpdb->prefix . WOW_Plugin::PREFIX;
		$result = $wpdb->delete( $table, [ 'id' => $id ], [ '%d' ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( $result ) {
			wp_safe_redirect( Link::remove_item() );
			exit;
		}

		return false;
	}

	/**
	 * Get all rows.
	 */
	public static function get_all_data() {
		global $wpdb;

		$table  = esc_sql( $wpdb->prefix . WOW_Plugin::PREFIX );
		$result = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id ASC" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		return ( ! empty( $result ) && is_array( $result ) ) ? $result : false;
	}

	/**
	 * Get row by ID.
	 */
	public static function get_data_by_id( $id = 0 ) {
		if ( empty( $id ) ) {
			return false;
		}
		global $wpdb;
		$table = esc_sql( $wpdb->prefix . WOW_Plugin::PREFIX );

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id=%d", absint( $id ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Get row by title.
	 */
	public static function get_data_by_title( $title = '' ) {
		if ( empty( $title ) ) {
			return false;
		}

		global $wpdb;
		$table = esc_sql( $wpdb->prefix . WOW_Plugin::PREFIX );

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE title=%s", sanitize_text_field( $title ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Get param value from row by ID.
	 */
	public static function get_param_id( $id = 0 ) {
		if ( empty( $id ) ) {
			return false;
		}
		$result = self::get_data_by_id( $id );

		return isset( $result->param ) ? maybe_unserialize( $result->param ) : false;
	}

	/**
	 * Check if row exists by ID.
	 */
	public static function check_row( $id = 0 ): bool {
		if ( empty( $id ) ) {
			return false;
		}

		global $wpdb;
		$table = esc_sql( $wpdb->prefix . WOW_Plugin::PREFIX );

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", absint( $id ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		return ! empty( $row );
	}

	/**
	 * Get unique tags from the table.
	 */
	public static function get_tags_from_table() {
		global $wpdb;
		$table = esc_sql( $wpdb->prefix . WOW_Plugin::PREFIX );

		$all_tags = $wpdb->get_results( "SELECT DISTINCT tag FROM {$table} ORDER BY tag ASC", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		return ! empty( $all_tags ) ? $all_tags : false;
	}

	/**
	 * Output <option> tags for unique tags.
	 */
	public static function display_tags(): void {
		global $wpdb;
		$table = esc_sql( $wpdb->prefix . WOW_Plugin::PREFIX );
		$tags  = [];

		$result = $wpdb->get_results( "SELECT * FROM {$table} order by tag DESC", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( ! empty( $result ) ) {
			foreach ( $result as $column ) {
				if ( ! empty( $column['tag'] ) ) {
					$tags[ $column['tag'] ] = $column['tag'];
				}
			}
		}
		if ( ! empty( $tags ) ) {
			foreach ( $tags as $tag ) {
				printf( '<option value="%s"></option>', esc_attr( $tag ) );
			}
		}
	}

}