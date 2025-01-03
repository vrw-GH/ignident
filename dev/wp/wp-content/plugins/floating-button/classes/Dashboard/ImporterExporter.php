<?php

namespace FloatingButton\Dashboard;

defined( 'ABSPATH' ) || exit;

use FloatingButton\WOW_Plugin;

class ImporterExporter {
	private $settings;

	public static function form_export(): void {
		?>
        <form method="post">
            <p></p>
            <p>
				<?php
				submit_button( __( 'Export All Data', 'floating-button' ), 'secondary', 'submit', false ); ?><?php
				wp_nonce_field( WOW_Plugin::PREFIX . '_nonce', WOW_Plugin::PREFIX . '_export_data' ); ?>
            </p>
        </form>

		<?php
	}

	public static function form_import(): void {
		?>
        <form method="post" enctype="multipart/form-data" action="">
            <p>
                <span class="wowp-file">
                <input type="file" name="import_file" accept="*.json"/>
                </span>
            </p>
            <p>
                <label>
                    <input type="checkbox" name="wowp_import_update" value="1">
					<?php
					esc_attr_e( 'Update item if item already exists.', 'floating-button' ); ?>
                </label>

            </p>

            <p>
				<?php
				submit_button( __( 'Import', 'floating-button' ), 'secondary', 'submit', false ); ?><?php
				wp_nonce_field( WOW_Plugin::PREFIX . '_nonce', WOW_Plugin::PREFIX . '_import_data' ); ?>
            </p>
        </form>

		<?php
	}

	public static function import_data(): void {
		if ( ! isset( $_FILES['import_file'] ) || empty( $_FILES['import_file']['name'] ) ) {
			wp_die( esc_attr__( 'Please select a file to import', 'floating-button' ),
				esc_attr__( 'Error', 'floating-button' ),
				[ 'response' => 400 ] );
		}

		if ( self::get_file_extension( sanitize_text_field( $_FILES['import_file']['name'] ) ) !== 'json' ) {
			wp_die( esc_attr__( 'Please upload a valid .json file', 'floating-button' ),
				esc_attr__( 'Error', 'floating-button' ),
				[ 'response' => 400 ] );
		}

		if ( empty( $_FILES['import_file']['tmp_name'] ) ) {
			wp_die( esc_attr__( 'Please select a file to import', 'floating-button' ),
				esc_attr__( 'Error', 'floating-button' ),
				[ 'response' => 400 ] );
		}

		$import_file = sanitize_text_field( $_FILES['import_file']['tmp_name'] );
		$settings    = wp_json_file_decode( $import_file );

		$columns = DBManager::get_columns();

		$update = ! empty( $_POST['wowp_import_update'] ) ? '1' : '';

		foreach ( $settings as $key => $val ) {
			$data    = [];
			$formats = [];

			foreach ( $columns as $column ) {
				$name          = $column->Field;
				$data[ $name ] = ! empty( $val->$name ) ? $val->$name : '';
				if ( $name === 'id' || $name === 'status' || $name === 'mode' ) {
					$formats[] = '%d';
				} else {
					$formats[] = '%s';
				}
			}

			$check_row = DBManager::check_row( $data['id'] );

			if ( ! empty( $update ) && ! empty( $check_row ) ) {
				$where = [
					'id' => absint( $data['id'] ),
				];
				$index = array_search( 'id', array_keys( $data ), true );
				unset( $data['id'], $formats[ $index ] );

				DBManager::update( $data, $where, $formats );
			} elseif ( ! empty( $check_row ) ) {
				$index = array_search( 'id', array_keys( $data ), true );
				unset( $data['id'], $formats[ $index ] );

				DBManager::insert( $data, $formats );
			} else {
				DBManager::insert( $data, $formats );
			}
		}

		$redirect_link = add_query_arg( [
			'page' => WOW_Plugin::SLUG,
		], admin_url( 'admin.php' ) );

		wp_safe_redirect( $redirect_link );
		exit;
	}

	private static function get_file_extension( $str ) {
		$parts = explode( '.', $str );

		return end( $parts );
	}

	public static function export_item( $id = 0, $action = '' ) {
		$page   = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : $action;
		$id     = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : $id;

		if ( ( $page !== WOW_Plugin::SLUG ) || ( $action !== 'export' ) || empty( $id ) ) {
			return false;
		}

		$data = DBManager::get_data_by_id( $id );
		if ( ! $data ) {
			return false;
		}

		$name      = trim( $data->title );
		$name      = str_replace( ' ', '-', $name );
		$file_name = $name . '-database-' . gmdate( 'm-d-Y' ) . '.json';
		self::export( $file_name, [ $data ] );

		return true;
	}

	public static function export_data(): bool {
		$file_name = WOW_Plugin::SHORTCODE . '-database-' . gmdate( 'm-d-Y' ) . '.json';
		$data      = DBManager::get_all_data();
		if ( empty( $data ) ) {
			return false;
		}
		self::export( $file_name, $data );

		return true;
	}

	private static function export( $file_name, $data ): void {
		ignore_user_abort( true );
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $file_name );
		header( "Expires: 0" );

		echo wp_json_encode( $data );
		exit;
	}

}