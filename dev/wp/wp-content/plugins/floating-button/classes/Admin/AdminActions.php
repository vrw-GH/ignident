<?php
/**
 * AdminActions class for Floating Button plugin.
 *
 * @package FloatingButton\Admin
 *
 * Methods:
 * - init()            Initialize admin actions hook
 * - actions()         Handle admin requests based on request name
 * - verify( $name )   Verify nonce and user capability
 * - check_name()      Detect action name from $_REQUEST
 */

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

	public function actions(): void {
		$name = $this->check_name();
		if ( ! $name || ! $this->verify( $name ) ) {
			return;
		}

		$map = [
			'_export_data'     => [ ImporterExporter::class, 'export_data' ],
			'_export_item'     => [ ImporterExporter::class, 'export_item' ],
			'_import_data'     => [ ImporterExporter::class, 'import_data' ],
			'_remove_item'     => [ DBManager::class,        'remove_item' ],
			'_settings'        => [ Settings::class,         'save_item' ],
			'_activate_item'   => [ Settings::class,         'activate_item' ],
			'_deactivate_item' => [ Settings::class,         'deactivate_item' ],
			'_activate_mode'   => [ Settings::class,         'activate_mode' ],
			'_deactivate_mode' => [ Settings::class,         'deactivate_mode' ],
		];

		foreach ( $map as $key => $callback ) {
			if ( is_callable( $callback ) && strpos( $name, $key ) !== false ) {
				$callback(); // call static method
				break;
			}
		}
	}

	private function verify( string $name ): bool {
		$nonce_action = WOW_Plugin::PREFIX . '_nonce';
		$nonce        = sanitize_text_field( wp_unslash( $_REQUEST[ $name ] ?? '' ) );

		return $nonce && wp_verify_nonce( $nonce, $nonce_action ) && current_user_can( 'manage_options' );
	}

	private function check_name(): string {
		$actions = [
			'_import_data',
			'_export_data',
			'_export_item',
			'_remove_item',
			'_settings',
			'_activate_item',
			'_deactivate_item',
			'_activate_mode',
			'_deactivate_mode',
		];

		foreach ( $actions as $action ) {
			$name = WOW_Plugin::PREFIX . $action;
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $_REQUEST[ $name ] ) ) {
				return $name;
			}
		}

		return '';
	}
}