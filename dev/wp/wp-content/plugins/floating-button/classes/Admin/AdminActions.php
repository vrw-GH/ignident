<?php

/**
 * Class AdminActions
 *
 * This class handles administrative actions in the plugin.
 *
 * @package    WowPlugin
 * @subpackage Admin
 * @author     Dmytro Lobov <dev@wow-company.com>, Wow-Company
 * @copyright  2024 Dmytro Lobov
 * @license    GPL-2.0+
 *
 */

namespace FloatingButton\Admin;

use FloatingButton\WOWP_Plugin;

defined( 'ABSPATH' ) || exit;

class AdminActions {

	public static function init(): void {
		add_action( 'admin_init', [ __CLASS__, 'actions' ] );
	}

	public static function actions(): bool {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$name = self::check_name( $_REQUEST );
		if ( ! $name ) {
			return false;
		}
		$verify = self::verify( $name );

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
		}
		elseif ( strpos( $name, '_activate_item' ) !== false ) {
			Settings::activate_item();
		} elseif ( strpos( $name, '_deactivate_item' ) !== false ) {
			Settings::deactivate_item();
		} elseif ( strpos( $name, '_activate_mode' ) !== false ) {
			Settings::activate_mode();
		} elseif ( strpos( $name, '_deactivate_mode' ) !== false ) {
			Settings::deactivate_mode();
		}

		return true;
	}

	public static function verify( $name ): bool {
		$nonce_action = WOWP_Plugin::PREFIX . '_nonce';
		$nonce        = isset( $_REQUEST[ $name ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $name ] ) ) : '';

		return ( ! empty( $nonce ) &&  wp_verify_nonce( $nonce, $nonce_action ) && current_user_can( 'manage_options' ) );
	}

	private static function check_name( $request ) {
		$names = [
			WOWP_Plugin::PREFIX . '_import_data',
			WOWP_Plugin::PREFIX . '_export_data',
			WOWP_Plugin::PREFIX . '_export_item',
			WOWP_Plugin::PREFIX . '_remove_item',
			WOWP_Plugin::PREFIX . '_settings',
			WOWP_Plugin::PREFIX . '_activate_item',
			WOWP_Plugin::PREFIX . '_deactivate_item',
			WOWP_Plugin::PREFIX . '_activate_mode',
			WOWP_Plugin::PREFIX . '_deactivate_mode',
		];

		foreach ( $request as $key => $value ) {

			if ( in_array( $key, $names, true ) ) {
				return $key;
			}
		}

		return false;

	}

}