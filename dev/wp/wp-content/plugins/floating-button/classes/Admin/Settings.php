<?php

/**
 * Class Settings
 *
 * Handles the settings functionality for the plugin.
 *
 * @package    WowPlugin
 * @subpackage Admin
 * @author     Dmytro Lobov <dev@wow-company.com>, Wow-Company
 * @copyright  2024 Dmytro Lobov
 * @license    GPL-2.0+
 */

namespace FloatingButton\Admin;

defined( 'ABSPATH' ) || exit;

use FloatingButton\WOWP_Plugin;

class Settings {

	public function __construct() {
//		add_action('wp_ajax_'.WOWP_Plugin::PREFIX . '_ajax_settings', [$this, 'save_item']);
	}

	public static function init(): void {

		$pages   = DashboardHelper::get_files( 'settings' );
		$options = self::get_options();
		$checked = $options['setting_tab'] ?? 1;

		echo '<h3 class="wpie-tabs">';
		foreach ( $pages as $key => $page ) {
			$class = ( absint( $checked ) === $key ) ? ' selected' : '';
			echo '<label class="wpie-tab-label' . esc_attr( $class ) . '" for="setting_tab_' . absint( $key ) . '">' . esc_html( $page['name'] ) . '</label>';
		}
		echo '</h3>';

		echo '<div class="wpie-tabs-contents">';
		foreach ( $pages as $key => $page ) {
			$file = DashboardHelper::get_folder_path( 'settings' ) . '/' . $key . '.' . $page['file'] . '.php';
			echo '<input type="radio" class="wpie-tab-toggle" name="param[setting_tab]" value="' . absint( $key ) . '" id="setting_tab_' . absint( $key ) . '" ' . checked( $key, $checked, false ) . '>';
			if ( file_exists( $file ) ) {
				echo '<div class="wpie-tab-content">';
				require_once $file;
				echo '</div>';
			}
		}
		echo '</div>';

	}

	public static function save_item() {
		$raw_data = file_get_contents('php://input');
		$request = json_decode($raw_data, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			wp_send_json_error(['message' => 'Invalid JSON']);
		}

		if (!isset($request['security']) || !wp_verify_nonce($request['security'], WOWP_Plugin::PREFIX . '_settings')) {
			wp_send_json_error(['message' => 'Invalid nonce'], 400);
		}

		$info = $request['info'];


		$id = isset( $info['tool_id'] ) ? absint( $info['tool_id'] ) : 0;

		$settings = apply_filters( WOWP_Plugin::PREFIX . '_save_settings', $info['param'] );

		$data    = [
			'title'  => isset( $info['title'] ) ? sanitize_text_field( wp_unslash( $info['title'] ) ) : '',
			'status' => isset( $info['status'] ) ? 1 : 0,
			'mode'   => isset( $info['mode'] ) ? 1 : 0,
			'tag'    => isset( $info['tag'] ) ? sanitize_text_field( wp_unslash( $info['tag'] ) ) : '',
			'param'  => maybe_serialize( $settings ),
		];
		$formats = [
			'%s',
			'%d',
			'%d',
			'%s',
			'%s'
		];

		if ( empty( $id ) ) {
			$id_item = DBManager::insert( $data, $formats );
		} else {
			$where = [
				'id' => absint( $id ),
			];
			DBManager::update( $data, $where, $formats );
			$id_item = $id;
		}

		wp_send_json_success(['id' => absint($id_item) ]);
		exit;
		// phpcs:enable
	}

	public static function deactivate_item( $id = 0 ): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : $id;

		if ( ! empty( $id ) ) {
			DBManager::update( [ 'status' => '1' ], [ 'ID' => $id ], [ '%d' ] );
		}

	}

	public static function activate_item( $id = 0 ): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : $id;

		if ( ! empty( $id ) ) {
			DBManager::update( [ 'status' => '' ], [ 'ID' => $id ], [ '%d' ] );
		}

	}

	public static function deactivate_mode( $id = 0 ): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : $id;

		if ( ! empty( $id ) ) {
			DBManager::update( [ 'mode' => '' ], [ 'ID' => $id ], [ '%d' ] );
		}

	}

	public static function activate_mode( $id = 0 ): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : $id;

		if ( ! empty( $id ) ) {
			DBManager::update( [ 'mode' => '1' ], [ 'ID' => $id ], [ '%d' ] );
		}
	}

	public static function get_options() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$id = isset( $_REQUEST['id'] ) ? absint( $_REQUEST['id'] ) : 0;

		if ( empty( $id ) ) {
			return false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : 'update';
		$result = DBManager::get_data_by_id( $id );

		if ( empty( $result ) ) {
			return false;
		}

		$param = ( ! empty( $result->param ) ) ? maybe_unserialize( $result->param ) : [];

		$param['tag']    = $result->tag;
		$param['status'] = $result->status;
		$param['mode']   = $result->mode;

		if ( $action === 'duplicate' ) {
			$param['id']    = '';
			$param['title'] = '';
		} else {
			$param['id']    = $id;
			$param['title'] = $result->title;
		}

		return $param;
	}

	public static function option( $name, $option ) {
		return $options[ $name ] ?? '';
	}

}