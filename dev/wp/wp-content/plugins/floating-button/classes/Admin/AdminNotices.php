<?php

/**
 * Class AdminNotices
 *
 * This class handles the admin notices for the plugin.
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

class AdminNotices {


	public static function init(): void {
		add_action( 'admin_notices', [ __CLASS__, 'admin_notice' ] );
	}

	public static function admin_notice(): bool {

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Nonce verification is handled elsewhere.
		if ( ! isset( $_GET['page'] ) ) {
			return false;
		}

		if ( $_GET['page'] !== WOWP_Plugin::SLUG ) {
			return false;
		}

		$notice = isset( $_GET['notice'] ) ? sanitize_text_field( wp_unslash( $_GET['notice'] ) ) : '';
		// phpcs:enable

		if ( ! empty( $notice ) && $notice === 'save_item' ) {
			self::save_item();
		} elseif ( ! empty( $notice ) && $notice === 'remove_item' ) {
			self::remove_item();
		}

		return true;
	}

	public static function save_item(): void {
		if ( isset( $_REQUEST['nonce'] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) );

			if ( wp_verify_nonce( $nonce, 'save-item' ) ) {
				$text = __( 'Item Saved', 'floating-button' );
				echo '<div class="wpie-notice notice notice-success is-dismissible">' . esc_html( $text ) . '</div>';
			}
		}
	}
	public static function remove_item(): void {
		$text = __( 'Item Remove', 'floating-button' );
		echo '<div class="wpie-notice notice notice-warning is-dismissible">' . esc_html( $text ) . '</div>';
	}

}