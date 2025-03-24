<?php

namespace FloatingButton\Dashboard;

defined( 'ABSPATH' ) || exit;

use FloatingButton\WOW_Plugin;

class DBManager {

	public static function remove_item() {
		$page   = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
		$id     = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : '';

		if ( ( $page !== WOW_Plugin::SLUG ) || ( $action !== 'delete' ) || empty( $id ) ) {
			return false;
		}

		global $wpdb;
		$table = $wpdb->prefix . WOW_Plugin::PREFIX;

		$result = $wpdb->delete( $table, [ 'id' => $id ], [ '%d' ] );

		if ( $result ) {
			wp_safe_redirect( Link::remove_item() );
			exit;
		}

		return false;
	}


	public static function delete( $id ) {
		if ( ! isset( $id ) ) {
			return false;
		}

		global $wpdb;
		$table = $wpdb->prefix . WOW_Plugin::PREFIX;

		return $wpdb->delete( $table, [ 'id' => $id ], [ '%d' ] );
	}

	public static function create( $columns ): void {
		global $wpdb;
		$table = $wpdb->prefix . WOW_Plugin::PREFIX;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} ($columns) {$charset_collate};";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	public static function get_all_data() {
		global $wpdb;
		$table  = $wpdb->prefix . WOW_Plugin::PREFIX;
		$result = $wpdb->get_results( "SELECT * FROM $table ORDER BY id ASC" );

		return ! empty( $result ) ? $result : false;
	}

	public static function get_data_by_id( $id = '' ) {
		if ( empty( $id ) ) {
			return false;
		}
		global $wpdb;
		$table = $wpdb->prefix . WOW_Plugin::PREFIX;

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id=%d", absint( $id ) ) );
	}

	public static function get_param_id( $id = '' ) {
		if ( empty( $id ) ) {
			return false;
		}
		$result = self::get_data_by_id( $id );

		return maybe_unserialize( $result->param );
	}

	public static function get_data_by_title( $title = '' ) {
		if ( empty( $title ) ) {
			return false;
		}

		global $wpdb;
		$table = $wpdb->prefix . WOW_Plugin::PREFIX;

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE title=%s", sanitize_text_field( $title ) ) );
	}

	public static function update( $data, $where, $data_formats ): void {
		global $wpdb;
		$table  = $wpdb->prefix . WOW_Plugin::PREFIX;
		$result = $wpdb->update( $table, $data, $where, $data_formats );
	}

	public static function insert( $data, $data_formats ) {
		global $wpdb;
		$table = $wpdb->prefix . WOW_Plugin::PREFIX;

		$result = $wpdb->insert( $table, $data, $data_formats );

		if ( $result ) {
			return $wpdb->insert_id;
		}

		return false;
	}

	public static function check_row( $id = '' ): bool {
		global $wpdb;
		$table = $wpdb->prefix . WOW_Plugin::PREFIX;
		if ( empty( $id ) ) {
			return false;
		}

		$check_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
		if ( ! empty( $check_row ) ) {
			return true;
		}

		return false;
	}

	public static function get_columns() {
		global $wpdb;
		$table_name = $wpdb->prefix . WOW_Plugin::PREFIX;

		return $wpdb->get_results( "DESCRIBE $table_name" );
	}

	public static function display_tags(): void {
		global $wpdb;
		$table  = $wpdb->prefix . WOW_Plugin::PREFIX;
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

	public static function get_tags_from_table() {
		global $wpdb;
		$table    = $wpdb->prefix . WOW_Plugin::PREFIX;
		$all_tags = $wpdb->get_results( "SELECT DISTINCT tag FROM $table ORDER BY tag ASC", ARRAY_A );

		return ! empty( $all_tags ) ? $all_tags : false;
	}

}