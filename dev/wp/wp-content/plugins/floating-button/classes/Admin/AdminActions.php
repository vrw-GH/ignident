<?php

namespace FloatingButton\Admin;

defined( 'ABSPATH' ) || exit;

use FloatingButton\Dashboard\DBManager;
use FloatingButton\Dashboard\ImporterExporter;
use FloatingButton\Dashboard\Settings;
use FloatingButton\WOW_Plugin;

class AdminActions {

	public function __construct() {
		add_action( 'admin_init', [ $this, 'actions' ] );
	}


	public function actions() {
		$name = $this->check_name( $_REQUEST );
		if ( ! $name ) {
			return false;
		}
		$verify = $this->verify( $name );

		if ( ! $verify ) {
			return false;
		}


		if ( strpos( $name, '_export_data' ) !== false ) {
			ImporterExporter::export_data();
		} elseif ( strpos( $name, '_export_item' ) !== false ) {
			ImporterExporter::export_item();
		} elseif ( strpos( $name, '_import_data' ) !== false ) {
			ImporterExporter::import_data();
		} elseif ( strpos( $name, '_remove_item' ) !== false ) {
			DBManager::remove_item();
		} elseif ( strpos( $name, '_settings' ) !== false ) {
			Settings::save_item();
		} elseif ( strpos( $name, '_activate_item' ) !== false ) {
			Settings::activate_item();
		} elseif ( strpos( $name, '_deactivate_item' ) !== false ) {
			Settings::deactivate_item();
		} elseif ( strpos( $name, '_activate_mode' ) !== false ) {
			Settings::activate_mode();
		} elseif ( strpos( $name, '_deactivate_mode' ) !== false ) {
			Settings::deactivate_mode();
		}
	}

	private function verify( $name ): bool {
		$nonce_action = WOW_Plugin::PREFIX . '_nonce';
		$nonce        = isset( $_REQUEST[ $name ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $name ] ) ) : '';

		return ( ! empty( $nonce ) &&  wp_verify_nonce( $nonce, $nonce_action ) && current_user_can( 'manage_options' ) );
	}

	private function check_name( $request ) {
		$names = [
			WOW_Plugin::PREFIX . '_import_data',
			WOW_Plugin::PREFIX . '_export_data',
			WOW_Plugin::PREFIX . '_export_item',
			WOW_Plugin::PREFIX . '_remove_item',
			WOW_Plugin::PREFIX . '_settings',
			WOW_Plugin::PREFIX . '_activate_item',
			WOW_Plugin::PREFIX . '_deactivate_item',
			WOW_Plugin::PREFIX . '_activate_mode',
			WOW_Plugin::PREFIX . '_deactivate_mode',
		];

		foreach ( $request as $key => $value ) {
			if ( in_array( $key, $names, true ) ) {
				return $key;
			}
		}

		return false;
	}

}