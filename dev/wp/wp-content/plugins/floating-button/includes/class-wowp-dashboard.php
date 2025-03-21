<?php

namespace FloatingButton;

use FloatingButton\Admin\AdminInitializer;
use FloatingButton\Dashboard\DashboardInitializer;

defined( 'ABSPATH' ) || exit;

class WOWP_Dashboard {

	public function __construct() {
		add_action( WOW_Plugin::PREFIX . '_admin_load_styles_scripts', [ $this, 'load_styles_scripts' ] );
		add_action( WOW_Plugin::PREFIX . '_admin_page', [ $this, 'dashboard' ] );
		add_action( WOW_Plugin::PREFIX . '_admin_header_links', [ $this, 'header_links' ] );
		add_filter( WOW_Plugin::PREFIX . '_save_settings', [ $this, 'save_settings' ], 10, 2 );
		add_filter( WOW_Plugin::PREFIX . '_default_custom_post', [ $this, 'default_custom_post' ] );

		AdminInitializer::init();
	}

	public function default_custom_post( $display_def ) {
		if ( str_contains( $display_def, 'custom_post_selected' ) ) {
			return 'post_selected';
		}
		if ( str_contains( $display_def, 'custom_post_tax' ) ) {
			return 'post_category';
		}
		if ( str_contains( $display_def, 'custom_post_all' ) ) {
			return 'post_all';
		}

		return $display_def;
	}

	public function save_settings( $settings, $request ): array {
		$param = ! empty( $request['param'] ) ? map_deep( $request['param'], [ $this, 'sanitize_param' ] ) : [];

		$settings['data']['title'] = ! empty( $request['title'] ) ? sanitize_text_field( wp_unslash( $request['title'] ) ) : '';
		$settings['formats'][]     = '%s';

		$settings['data']['status'] = ! empty( $request['status'] ) ? sanitize_textarea_field( wp_unslash( $request['status'] ) ) : '';
		$settings['formats'][]      = '%d';

		$settings['data']['mode'] = ! empty( $request['mode'] ) ? sanitize_textarea_field( wp_unslash( $request['mode'] ) ) : '';
		$settings['formats'][]    = '%d';

		$settings['data']['tag'] = ! empty( $request['tag'] ) ? sanitize_textarea_field( wp_unslash( $request['tag'] ) ) : '';
		$settings['formats'][]   = '%s';


		$param                     = $this->additional_sanitize( $param, $request['param'] );
		$settings['data']['param'] = maybe_serialize( $param );
		$settings['formats'][]     = '%s';

		return $settings;
	}

	public function additional_sanitize( $param, $arr ) {
		// For main button
		if ( isset( $arr['extra_style'] ) ) {
			$param['extra_style'] = sanitize_textarea_field( wp_unslash( $arr['extra_style'] ) );
		}
		if ( isset( $arr['item_link'] ) ) {
			$param['item_link'] = sanitize_url( $arr['item_link'] );
		}

		if ( isset( $arr['custom_icon_url'] ) ) {
			$param['custom_icon_url'] = sanitize_url( $arr['custom_icon_url'] );
		}

		if ( isset( $arr['custom_icon_emoji'] ) ) {
			$param['custom_icon_emoji'] = wp_kses_post( wp_encode_emoji( $arr['custom_icon_emoji'] ) );
		}

		// For menu 1
		if ( isset( $arr['menu_1']['item_link'] ) && is_array( $arr['menu_1']['item_link'] ) ) {
			$param['menu_1']['item_link'] = map_deep( $arr['menu_1']['item_link'], 'sanitize_url' );
		}

		if ( isset( $arr['menu_1']['custom_icon_url'] ) && is_array( $arr['menu_1']['custom_icon_url'] ) ) {
			$param['menu_1']['custom_icon_url'] = map_deep( $arr['menu_1']['custom_icon_url'], 'sanitize_url' );
		}

		if ( isset( $arr['menu_1']['custom_icon_emoji'] ) && is_array( $arr['menu_1']['custom_icon_emoji'] ) ) {
			$param['menu_1']['custom_icon_emoji'] = map_deep( $arr['menu_1']['custom_icon_emoji'], [
				$this,
				'sanitize_emoji'
			] );
		}

		// For menu 2
		if ( isset( $arr['menu_2']['item_link'] ) && is_array( $arr['menu_2']['item_link'] ) ) {
			$param['menu_2']['item_link'] = map_deep( $arr['menu_2']['item_link'], 'sanitize_url' );
		}

		if ( isset( $arr['menu_2']['custom_icon_url'] ) && is_array( $arr['menu_2']['custom_icon_url'] ) ) {
			$param['menu_2']['custom_icon_url'] = map_deep( $arr['menu_2']['custom_icon_url'], 'sanitize_url' );
		}

		if ( isset( $arr['menu_2']['custom_icon_emoji'] ) && is_array( $arr['menu_2']['custom_icon_emoji'] ) ) {
			$param['menu_2']['custom_icon_emoji'] = map_deep( $arr['menu_2']['custom_icon_emoji'], [
				$this,
				'sanitize_emoji'
			] );
		}

		return $param;
	}


	public function sanitize_emoji( $text ): string {
		return wp_kses_post( wp_encode_emoji( wp_unslash( $text ) ) );
	}


	public function sanitize_param( $value ): string {
		return sanitize_text_field( wp_unslash( $value ) );
	}

	public function load_styles_scripts(): void {
		$assets_url = WOW_Plugin::url() . 'assets/';
		$version    = WOW_Plugin::info( 'version' );
		$slug       = WOW_Plugin::SLUG;

		wp_enqueue_style( $slug . '-admin', $assets_url . 'css/admin.css', null, $version );

		wp_enqueue_style( $slug . '-admin-fontawesome', $assets_url . 'vendors/fontawesome/css/fontawesome-all.min.css',
			null, '6.6' );

		// include fonticonpicker styles & scripts


		$fonticonpicker_css = $assets_url . 'vendors/fonticonpicker/jquery.fonticonpicker.min.css';
		wp_enqueue_style( $slug . '-fonticonpicker', $fonticonpicker_css, null, $version );

		$fonticonpicker_dark_css = $assets_url . 'vendors/fonticonpicker/jquery.fonticonpicker.grey.min.css';
		wp_enqueue_style( $slug . '-fonticonpicker-darkgrey', $fonticonpicker_dark_css, null, $version );

		$fonticonpicker_js = $assets_url . 'vendors/fonticonpicker/jquery.fonticonpicker.js';
		wp_enqueue_script( $slug . '-fonticonpicker', $fonticonpicker_js, [ 'jquery' ], $version, true );

		// include the color picker
		wp_enqueue_script( 'code-editor' );
		wp_enqueue_style( 'code-editor' );
		wp_enqueue_script( 'csslint' );

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_media();

		wp_enqueue_script( 'jquery-ui-sortable' );

		$url_alpha = $assets_url . 'js/wp-color-picker-alpha.js';
		wp_enqueue_script( 'wp-color-picker-alpha', $url_alpha, array( 'wp-color-picker' ), '3.0.0', true );


		wp_enqueue_script( $slug . '-admin-jquery', $assets_url . 'js/admin-jquery.js', [
			'jquery',
			'wp-color-picker-alpha'
		], $version, true );
	}

	public function dashboard(): void {
		DashboardInitializer::init();
	}


	public function header_links(): void {
		$logo = WOW_Plugin::url() . 'assets/img/wow-icon.png';
		?>
        <div class="wowp-links">
            <a href="https://wow-estore.com/" target="_blank">
                <img src="<?php
				echo esc_url( $logo ); ?>" alt="Logo Wow-Estore.com">
                <span>Wow-Estore</span>
            </a>
            <a href="https://wow-estore.com/guides/floating-button/" target="_blank">
                <span class="dashicons dashicons-book-alt"></span>
                <span>Docs</span>
            </a>
            <a href="https://wordpress.org/support/plugin/floating-button/reviews/?filter=5" target="_blank">
                <span class="dashicons dashicons-star-filled"></span>
                <span>Reviews</span>
            </a>
        </div>

		<?php
	}


}