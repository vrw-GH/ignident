<?php

namespace FloatingButton\Dashboard;

defined( 'ABSPATH' ) || exit;

use FloatingButton\WOW_Plugin;

class DashboardInitializer {

	public static function init(): void {
		self::header();
		echo '<div class="wrap wowp-wrap">';
		self::menu();
		self::include_pages();
		echo '</div>';
	}

	public static function header(): void {
		$logo_url = self::logo_url();
		$add_url  = add_query_arg( [
			'page'   => WOW_Plugin::SLUG,
			'tab'    => 'settings',
			'action' => 'new'
		], admin_url( 'admin.php' ) );
		?>
        <div class="wowp-header-wrapper">
            <div class="wowp-header-border"></div>
            <div class="wowp-header">
                <div class="wowp-logo">
                    <img src="<?php
					echo esc_url( $logo_url ); ?>" alt="<?php
					echo esc_attr( WOW_Plugin::info( 'name' ) ); ?> logo">
                </div>
                <h1><?php
					echo esc_html( WOW_Plugin::info( 'name' ) ); ?> <sup class="wowp-version"><?php
						echo esc_html( WOW_Plugin::info( 'version' ) ); ?></sup></h1>
                <a href="<?php
				echo esc_url( $add_url ); ?>" class="button button-primary"><?php
					esc_html_e( 'Add New', 'floating-button' ); ?>
                </a>
				<?php
				do_action( WOW_Plugin::PREFIX . '_admin_header_links' ); ?>
            </div>
        </div>

		<?php
	}

	public static function logo_url(): string {
		$logo_url = WOW_Plugin::url() . 'assets/img/plugin-logo.png';
		if ( filter_var( $logo_url, FILTER_VALIDATE_URL ) !== false ) {
			return $logo_url;
		}

		return '';
	}

	public static function menu(): void {
		$pages = DashboardHelper::get_files( 'pages' );

		$current_page = self::get_current_page();

		$action = ( isset( $_REQUEST["action"] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST["action"] ) ) : '';

		echo '<h2 class="nav-tab-wrapper wowp-nav-tab-wrapper">';
		foreach ( $pages as $key => $page ) {
			$class = ( $page['file'] === $current_page ) ? ' nav-tab-active' : '';
			$id    = '';

			if ( $action === 'update' && $page['file'] === 'settings' ) {
				$id           = ( isset( $_REQUEST["id"] ) ) ? absint( $_REQUEST["id"] ) : '';
				$page['name'] = __( 'Update', 'floating-button' ) . ' #' . $id;
			} elseif ( $page['file'] === 'settings' && ( $action !== 'new' && $action !== 'duplicate' ) ) {
				continue;
			}

			echo '<a class="nav-tab' . esc_attr( $class ) . '" href="' . esc_url( Link::menu( $page['file'], $action,
					$id ) ) . '">' . esc_html( $page['name'] ) . '</a>';
		}
		echo '</h2>';
	}

	public static function include_pages(): void {
		$current_page = self::get_current_page();

		$pages   = DashboardHelper::get_files( 'pages' );
		$default = DashboardHelper::first_file( 'pages' );

		$current = DashboardHelper::search_value( $pages, $current_page ) ? $current_page : $default;

		$file = DashboardHelper::get_file( $current, 'pages' );


		if ( $file !== false ) {
			$file = apply_filters( WOW_Plugin::PREFIX . '_admin_filter_file', $file, $current );

			$page_path = DashboardHelper::get_folder_path( 'pages' ) . '/' . $file;

			if ( file_exists( $page_path ) ) {
				require_once $page_path;
			}
		}
	}


	public static function get_current_page(): string {
		$default = DashboardHelper::first_file( 'pages' );

		return ( isset( $_REQUEST["tab"] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST["tab"] ) ) : $default;
	}


}