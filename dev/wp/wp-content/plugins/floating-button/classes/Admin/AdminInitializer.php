<?php

namespace FloatingButton\Admin;

defined( 'ABSPATH' ) || exit;

// Exit if accessed directly.
use FloatingButton\WOW_Plugin;

class AdminInitializer {

	public static function init(): void {
		add_filter( 'plugin_action_links', [ __CLASS__, 'settings_link' ], 10, 2 );
		add_filter( 'admin_footer_text', [ __CLASS__, 'footer_text' ] );
		add_action( 'admin_menu', [ __CLASS__, 'add_admin_page' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'admin_scripts' ] );
		new AdminNotices;
		new AdminActions;
	}

	public static function settings_link( $links, $file ) {
		if ( false === strpos( $file, WOW_Plugin::basename() ) ) {
			return $links;
		}
		$link          = admin_url( 'admin.php?page=' . WOW_Plugin::SLUG );
		$text          = esc_attr__( 'Settings', 'floating-button' );
		$settings_link = '<a href="' . esc_url( $link ) . '">' . esc_attr( $text ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	public static function footer_text( $footer_text ) {
		$screen = get_current_screen();

		if ( isset( $screen->id ) && $screen->id === 'wow-plugins_page_' . WOW_Plugin::SLUG ) {
			$text = sprintf(
			/* translators: 1: Rating link (URL), 2: Plugin name */
				__( 'Thank you for using <b>%2$s</b>! Please <a href="%1$s" target="_blank">rate us</a>', 'floating-button' ),
				esc_url( WOW_Plugin::info('url') ),
				esc_attr( WOW_Plugin::info('name') )
			);

			return str_replace( '</span>', '', $footer_text ) . ' | ' . $text . '</span>';
		}

		return $footer_text;
	}

	public static function add_admin_page(): void {
		$parent     = 'wow-company';
		$title      = WOW_Plugin::info('name') . ' version ' . WOW_Plugin::info('version');
		$menu_title = WOW_Plugin::info('name');
		$capability = 'manage_options';
		$slug       = WOW_Plugin::SLUG;
		add_submenu_page( $parent, $title, $menu_title, $capability, $slug, [ __CLASS__, 'plugin_page' ] );
	}

	public static function plugin_page(): void {
		do_action( WOW_Plugin::PREFIX . '_admin_page' );
	}

	public static function admin_scripts( $hook ): void {
		$page = 'wow-plugins_page_' . WOW_Plugin::SLUG;

		if ( $page !== $hook ) {
			return;
		}

		do_action( WOW_Plugin::PREFIX . '_admin_load_styles_scripts' );
	}

}