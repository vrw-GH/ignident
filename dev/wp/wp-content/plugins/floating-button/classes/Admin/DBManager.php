<?php
/**
 * DBManager class for Float Menu Pro plugin.
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

namespace FloatingButton\Admin;

defined( 'ABSPATH' ) || exit;

use FloatingButton\WOWP_Plugin;

class DBManager {

	public static function create( $columns ): void {
		global $wpdb;

		$table           = esc_sql( $wpdb->prefix . WOWP_Plugin::PREFIX );
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table ($columns) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	public static function get_columns() {
		global $wpdb;
		$table = esc_sql( $wpdb->prefix . WOWP_Plugin::PREFIX );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_results( "DESCRIBE $table" );
	}

	public static function insert( $data, $data_formats ) {
		global $wpdb;
		$table = esc_sql( $wpdb->prefix . WOWP_Plugin::PREFIX );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->insert( $table, $data, $data_formats );

		if ( $result ) {
			return $wpdb->insert_id;

		}

		return false;
	}

	public static function update( $data, $where, $data_formats ): void {
		global $wpdb;
		$table = esc_sql( $wpdb->prefix . WOWP_Plugin::PREFIX );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->update( $table, $data, $where, $data_formats );
	}

	public static function delete( $id ) {
		if ( ! isset( $id ) ) {
			return false;
		}

		global $wpdb;
		$table = esc_sql( $wpdb->prefix . WOWP_Plugin::PREFIX );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->delete( $table, [ 'id' => $id ], [ '%d' ] );

	}

	public static function remove_item() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : '';

		if ( ( $page !== WOWP_Plugin::SLUG ) || ( $action !== 'delete' ) || empty( $id ) ) {
			return false;
		}

		global $wpdb;
		$table = esc_sql( $wpdb->prefix . WOWP_Plugin::PREFIX );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->delete( $table, [ 'id' => $id ], [ '%d' ] );

		if ( $result ) {
			wp_safe_redirect( Link::remove_item() );
			exit;
		}

		return false;
	}

	public static function get_all_data() {
		global $wpdb;
		$table = esc_sql( $wpdb->prefix . WOWP_Plugin::PREFIX );

		$sql = "SELECT * FROM $table WHERE status = 0";

		if ( ! current_user_can( 'manage_options' ) ) {
			$sql .= " AND mode = 0";
		}

		$sql .= " ORDER BY id ASC";

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->get_results( $sql );

		return ( ! empty( $result ) && is_array( $result ) ) ? $result : false;
	}

	public static function get_data_by_id( $id = '' ) {
		if ( empty( $id ) ) {
			return false;
		}
		global $wpdb;
		$table = esc_sql( $wpdb->prefix . WOWP_Plugin::PREFIX );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id=%d", absint( $id ) ) );
	}

	public static function get_data_by_title( $title = '' ) {
		if ( empty( $title ) ) {
			return false;
		}

		global $wpdb;
		$table = esc_sql( $wpdb->prefix . WOWP_Plugin::PREFIX );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE title=%s", sanitize_text_field( $title ) ) );
	}

	public static function get_param_id( $id = '' ) {
		if ( empty( $id ) ) {
			return false;
		}
		$result = self::get_data_by_id( $id );

		return maybe_unserialize( $result->param );
	}

	public static function check_row( $id = '' ): bool {
		global $wpdb;
		$table = esc_sql( $wpdb->prefix . WOWP_Plugin::PREFIX );
		if ( empty( $id ) ) {
			return false;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$check_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
		if ( ! empty( $check_row ) ) {
			return true;
		}

		return false;
	}

	public static function get_tags_from_table() {
		global $wpdb;
		$table = esc_sql( $wpdb->prefix . WOWP_Plugin::PREFIX );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$all_tags = $wpdb->get_results( "SELECT DISTINCT tag FROM $table ORDER BY tag ASC", ARRAY_A );

		return ! empty( $all_tags ) ? $all_tags : false;
	}

	public static function display_tags(): void {
		global $wpdb;
		$table = esc_sql( $wpdb->prefix . WOWP_Plugin::PREFIX );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->get_results( "SELECT * FROM $table order by tag desc", ARRAY_A );
		$tags   = [];
		if ( ! empty( $result ) ) {
			foreach ( $result as $column ) {
				if ( ! empty( $column['tag'] ) ) {
					$tags[ $column['tag'] ] = $column['tag'];
				}
			}
		}
		if ( ! empty( $tags ) ) {
			foreach ( $tags as $tag ) {
				echo '<option value="' . esc_attr( $tag ) . '">';
			}
		}
	}
}