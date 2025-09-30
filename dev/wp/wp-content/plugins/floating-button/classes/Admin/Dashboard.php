<?php

/**
 * Class Dashboard
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

class Dashboard {

	public static function init(): void {
		add_filter( 'plugin_action_links', [ __CLASS__, 'settings_link' ], 10, 2 );
		add_filter( 'admin_footer_text', [ __CLASS__, 'footer_text' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'admin_assets' ] );
		add_action( 'admin_menu', [ __CLASS__, 'admin_page' ] );
		AdminNotices::init();
	}

	public static function settings_link( $links, $file ) {
		if ( false === strpos( $file, WOWP_Plugin::basename() ) ) {
			return $links;
		}
		$link          = admin_url( 'admin.php?page=' . WOWP_Plugin::SLUG );
		$text          = esc_attr__( 'Settings', 'floating-button' );
		$settings_link = '<a href="' . esc_url( $link ) . '">' . esc_attr( $text ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	public static function footer_text( $footer_text ) {
		global $pagenow;

		// No nonce verification is required as this is a read-only operation to check the current admin page.
		if ( $pagenow === 'admin.php' && ( isset( $_GET['page'] ) && $_GET['page'] === WOWP_Plugin::SLUG ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$text = sprintf(
			/* translators: 1: Rating link (URL), 2: Plugin name */
				__( 'Thank you for using <b>%2$s</b>! Please <a href="%1$s" target="_blank">rate us</a>', 'floating-button' ),
				esc_url( WOWP_Plugin::info( 'rating' ) ),
				esc_attr( WOWP_Plugin::info( 'name' ) )
			);

			return str_replace( '</span>', '', $footer_text ) . ' | ' . $text . '</span>';
		}

		return $footer_text;
	}

	public static function admin_assets( $hook ): void {

		$page = 'wow-plugins_page_' . WOWP_Plugin::SLUG;
		if ( $page !== $hook ) {
			return;
		}
		do_action( WOWP_Plugin::PREFIX . '_admin_load_assets' );

		$slug       = WOWP_Plugin::SLUG;
		$version    = WOWP_Plugin::info( 'version' );
		$assets_url = WOWP_Plugin::url() . 'admin/assets/';

		$styles = DashboardHelper::get_files( 'assets/css' );

		if ( ! empty( $styles ) ) {
			foreach ( $styles as $key => $style ) {
				$name = $style['file'];
				$file = $key . '.' . $name;
				wp_enqueue_style( $slug . '-admin-' . $name, $assets_url . 'css/' . $file . '.css', null, $version );
				wp_style_add_data($slug . '-admin-' . $name, 'rtl', 'replace');
			}
		}

		$scripts = DashboardHelper::get_files( 'assets/js' );
		if ( ! empty( $scripts ) ) {
			foreach ( $scripts as $key => $script ) {
				$name = $script['file'];
				$file = $key . '.' . $name;
				wp_enqueue_script( $slug . '-admin-' . $name, $assets_url . 'js/' . $file . '.js', [ 'jquery' ], $version, true );
				if ( $name === 'general' ) {
					wp_localize_script( $slug . '-admin-' . $name, 'wowp_ajax_object', array(
						'url'   => admin_url( 'admin-ajax.php' ),
						'security' => wp_create_nonce( WOWP_Plugin::PREFIX . '_settings' ),
                        'action' => WOWP_Plugin::PREFIX . '_ajax_settings',
                        'prefix' => WOWP_Plugin::PREFIX,
					) );
				}
			}
		}


	}

	public static function admin_page(): void {
		$page_title  = WOWP_Plugin::info( 'name' );
		$menu_title  = WOWP_Plugin::info( 'menu_title' );
		$capability  = 'manage_options';
		$parent_slug = 'wow-company';
		add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, WOWP_Plugin::SLUG, [
			__CLASS__,
			'dashboard'
		] );
	}

	public static function dashboard(): void {
		self::header();
		echo '<div class="wrap wpie-wrap">';
		self::menu();
		self::include_pages();
		echo '</div>';
	}

	// phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
	public static function header(): void {
		$logo_url = self::logo_url();
		?>
        <div class="wpie-header-wrapper">
            <div class="wpie-header-border"></div>
            <div class="wpie-header">
                <div class="wpie-header__container">
					<?php if ( ! empty( $logo_url ) ): ?>
                        <div class="wpie-logo">
                            <img src="<?php echo esc_url( $logo_url ); ?>"
                                 alt="<?php echo esc_attr( WOWP_Plugin::info( 'name' ) ); ?> logo">
                        </div>
					<?php endif; ?>
                    <h1>
						<?php echo esc_html( WOWP_Plugin::info( 'name' ) ); ?>
                        <sup class="wpie-version"><?php echo esc_html( WOWP_Plugin::info( 'version' ) ); ?></sup>
                    </h1>
                    <a href="<?php echo esc_url( Link::add_new_item() ); ?>" class="button button-primary">
						<?php esc_html_e( 'Add New', 'floating-button' ); ?>
                    </a>
	                <?php do_action( WOWP_Plugin::PREFIX . '_admin_after_button' ); ?>
					<?php do_action( WOWP_Plugin::PREFIX . '_admin_header_links' ); ?>
                </div>
            </div>
        </div>
		<?php
	}

	// phpcs:enable


	public static function logo_url(): string {
		$logo_url = WOWP_Plugin::url() . 'admin/assets/img/plugin-logo.png';
		if ( filter_var( $logo_url, FILTER_VALIDATE_URL ) !== false ) {
			return $logo_url;
		}

		return '';
	}

	public static function menu(): void {
		$pages = DashboardHelper::get_files( 'pages' );

		$pages = apply_filters(WOWP_Plugin::PREFIX. '_admin_pages_menu', $pages);

		$current_page = self::get_current_page();

		//No nonce checking is required as this is just reading the parameters.
		$action = ( isset( $_REQUEST["action"] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST["action"] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		echo '<h2 class="nav-tab-wrapper wpie-nav-tab-wrapper">';
		foreach ( $pages as $key => $page ) {
			$class = ( $page['file'] === $current_page ) ? ' nav-tab-active wowp-page-' .$page['file'] : ' wowp-page-' . $page['file'];
			$id    = '';

			if ( $action === 'update' && $page['file'] === 'settings' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$id           = ( isset( $_REQUEST["id"] ) ) ? absint( $_REQUEST["id"] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$page['name'] = __( 'Update', 'floating-button' ) . ' #' . $id;
			} elseif ( $page['file'] === 'settings' && ( $action !== 'new' && $action !== 'duplicate' ) ) {
				continue;
			}
            if($page['file'] === 'pro') {
	            echo '<a class="nav-tab' . esc_attr( $class ) . '" href="' . esc_url( Link::menu( $page['file'], $action, $id ) ) . '"><span class="wpie-icon wpie_icon-rocket"></span>' . esc_html( $page['name'] ) . '</a>';
            } else {
	            echo '<a class="nav-tab' . esc_attr( $class ) . '" href="' . esc_url( Link::menu( $page['file'], $action, $id ) ) . '">' . esc_html( $page['name'] ) . '</a>';
            }

		}
		echo '</h2>';
	}

	public static function get_current_page(): string {
		$default = DashboardHelper::first_file( 'pages' );

		// Nonce verification is not required as the “tab” parameter is read-only.
		return ( isset( $_REQUEST["tab"] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST["tab"] ) ) : $default; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	public static function include_pages(): void {
		$current_page = self::get_current_page();

		$pages = DashboardHelper::get_files( 'pages' );
		$pages = apply_filters( WOWP_Plugin::PREFIX . '_admin_pages_menu', $pages );

		$default = DashboardHelper::first_file( 'pages' );

		$current = DashboardHelper::search_value( $pages, $current_page ) ? $current_page : $default;

		$file = DashboardHelper::get_file( $current, 'pages' );

		$file_path = DashboardHelper::get_folder_path( 'pages' ) . '/' . $file;

		$page_path = apply_filters( WOWP_Plugin::PREFIX . '_admin_filter_file', $file_path, $file, $current );

		if ( file_exists( $page_path ) ) {
			require_once $page_path;
		}

	}

}