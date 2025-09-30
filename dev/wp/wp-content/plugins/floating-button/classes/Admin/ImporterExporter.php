<?php

/**
 * Class ImporterExporter
 *
 * This class provides functionality for exporting and importing data.
 *
 * @package    WowPlugin
 * @subpackage Admin
 * @author     Dmytro Lobov <dev@wow-company.com>, Wow-Company
 * @copyright  2024 Dmytro Lobov
 * @license    GPL-2.0+
 *
 */

namespace FloatingButton\Admin;

defined( 'ABSPATH' ) || exit;

use FloatingButton\Update\UpdateDB;
use FloatingButton\WOWP_Plugin;

/**
 * Class ImporterExporter
 *
 * This class provides functionality for exporting and importing data.
 */
class ImporterExporter {

	public static function form_export(): void {
		?>
        <form method="post">
            <p></p>
            <p>
				<?php
				submit_button( __( 'Export All Data', 'floating-button' ), 'primary', 'submit', false ); ?><?php
				wp_nonce_field( WOWP_Plugin::PREFIX . '_nonce', WOWP_Plugin::PREFIX . '_export_data' ); ?>
            </p>
        </form>

		<?php
	}

	public static function form_import(): void {
		?>
        <form method="post" enctype="multipart/form-data" action="">
            <p>
                <span class="wpie-file">
                <input type="file" name="import_file" class="" accept="*.json"/>
                </span>
            </p>
            <p>
                <label>
                    <input type="checkbox" name="wpie_import_update" value="1">
					<?php esc_html_e( 'Update item if item already exists.', 'floating-button' ); ?>
                </label>

            </p>

            <p>
				<?php
				submit_button( __( 'Import', 'floating-button' ), 'primary', 'submit', false ); ?><?php
				wp_nonce_field( WOWP_Plugin::PREFIX . '_nonce', WOWP_Plugin::PREFIX . '_import_data' ); ?>
            </p>
        </form>

		<?php
	}

	/**
	 * @throws \JsonException
	 */
	public static function import_data(): void {
		$verify = AdminActions::verify(WOWP_Plugin::PREFIX . '_import_data');

		if ( ! $verify ) {
			return;
		}
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verification is handled elsewhere.
		if ( ! isset( $_FILES['import_file'] ) || empty( $_FILES['import_file']['name'] ) ) {
			wp_die( esc_attr__( 'Please select a file to import', 'floating-button' ),
				esc_attr__( 'Error', 'floating-button' ),
				[ 'response' => 400 ] );
		}

		if ( self::get_file_extension( sanitize_text_field( $_FILES['import_file']['name'] ) ) !== 'json' ) {
			wp_die(
				esc_html__( 'Please upload a valid .json file', 'floating-button' ),
				esc_html__( 'Error', 'floating-button' ),
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

		$update = ! empty( $_POST['wpie_import_update'] ) ? '1' : '';
		// phpcs:enable

		foreach ( $settings as $key => $val ) {
			$data    = [];
			$formats = [];

			foreach ( $columns as $column ) {
				$name = $column->Field;

				if ( $name === 'param' ) {
					$param_input   = maybe_unserialize( $val->$name );
					$new_param     = UpdateDB::update_param( $param_input );
					$param_output  = maybe_serialize( $new_param );
					$data[ $name ] = $param_output;
				} else {
					$data[ $name ] = ! empty( $val->$name ) ? $val->$name : '';
				}

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
			'page' => WOWP_Plugin::SLUG,
		], admin_url( 'admin.php' ) );

		wp_safe_redirect( $redirect_link );
		exit;
	}

	private static function get_file_extension( $str ) {
		$parts = explode( '.', $str );

		return end( $parts );
	}

	/**
	 * @throws \JsonException
	 */
	public static function export_item( $id = 0, $action = '' ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page   = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash($_GET['page']) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash($_GET['action']) ) : $action;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$id     = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : $id;

		if ( ( $page !== WOWP_Plugin::SLUG ) || ( $action !== 'export' ) || empty( $id ) ) {
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

	/**
	 * @throws \JsonException
	 */
	public static function export_data(): bool {
		$file_name = WOWP_Plugin::SHORTCODE . '-database-' . gmdate( 'm-d-Y' ) . '.json';
		$data      = DBManager::get_all_data();
		if ( empty( $data ) ) {
			return false;
		}
		self::export( $file_name, $data );

		return true;
	}

	/**
	 * @throws \JsonException
	 */
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