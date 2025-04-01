<?php

namespace FloatingButton\Admin;

use FloatingButton\WOW_Plugin;

defined( 'ABSPATH' ) || exit;

class AdminNotices {


	public function __construct() {
		add_action( 'admin_notices', [ $this, 'admin_notice' ] );
	}

	public function admin_notice() {
		$notice = isset( $_GET['notice'] ) ? sanitize_text_field( wp_unslash( $_GET['notice'] ) ) : '';

		if ( ! isset( $_GET['page'] ) ) {
			return false;
		}

		if ( $_GET['page'] !== WOW_Plugin::SLUG ) {
			return false;
		}

		if ( ! empty( $notice ) && $notice === 'save_item' ) {
			$this->save_item();
		} elseif ( ! empty( $notice ) && $notice === 'remove_item' ) {
			$this->remove_item();
		}
	}

	public function save_item() {
		$text = __( 'Item saved.', 'floating-button' );
		echo '<div class="wowp-notice notice notice-success is-dismissible">' . esc_html( $text ) . '</div>';
	}

	public function remove_item() {
		$text = __( 'Item delete.', 'floating-button' );
		echo '<div class="wowp-notice notice notice-warning is-dismissible">' . esc_html( $text ) . '</div>';
	}

}