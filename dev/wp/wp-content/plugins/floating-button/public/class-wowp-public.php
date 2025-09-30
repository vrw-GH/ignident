<?php
/**
 * Class WOWP_Public
 *
 * This class handles the public functionality of the Float Menu Pro plugin.
 *
 * @package    FloatingButton
 * @subpackage Public
 * @author     Dmytro Lobov <hey@wow-company.com>, Wow-Company
 * @copyright  2024 Dmytro Lobov
 * @license    GPL-2.0+
 */

namespace FloatingButton;

use FloatingButton\Admin\DBManager;

//use FloatingButton\Maker\Content;
use FloatingButton\Publish\Conditions;
use FloatingButton\Publish\Display;
use FloatingButton\Publish\EnqueueScript;
use FloatingButton\Publish\EnqueueStyle;
use FloatingButton\Publish\Singleton;

defined( 'ABSPATH' ) || exit;

class WOWP_Public {

	private string $pefix;

	public function __construct() {
		// prefix for plugin assets
		$this->pefix = ! WOWP_Plugin::DEVMODE ? '.min' : '';

		$this->includes();

		add_shortcode( WOWP_Plugin::SHORTCODE, [ $this, 'shortcode' ] );
		add_action( 'wp_ajax_wowp_likes', [ $this, 'update_likes' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'assets' ] );
		add_action( 'wp_footer', [ $this, 'footer' ] );
	}

	public function update_likes(): void {
		if ( ! isset( $_POST['_ajax_nonce'] ) ) {
			wp_die();
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_ajax_nonce'] ) ), '_wowp_likes' ) ) {
			wp_die();
		}

		$post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : 0;
		$likes   = get_post_meta( $post_id, '_wowp_likes', true );
		if ( empty( $likes ) ) {
			$likes = 0;
		}
		$counter = absint( $likes ) + 1;
		update_post_meta( $post_id, '_wowp_likes', $counter );
		wp_die();
	}

	public function includes(): void {
		$path_maker = plugin_dir_path( __FILE__ ) . 'class-wowp-maker.php';
		require_once apply_filters( WOWP_Plugin::PREFIX . '_include_maker', $path_maker );
	}

	public function shortcode( $atts ): string {
		$atts = shortcode_atts(
			[ 'id' => "" ],
			$atts,
			WOWP_Plugin::SHORTCODE
		);

		if ( empty( $atts['id'] ) ) {
			return '';
		}

		$singleton = Singleton::getInstance();

		if ( $singleton->hasKey( $atts['id'] ) ) {
			return '';
		}

		$result = DBManager::get_data_by_id( $atts['id'] );

		if ( empty( $result->param ) ) {
			return '';
		}

		$conditions = Conditions::init( $result );

		if ( $conditions === false ) {
			return '';
		}

		$param = maybe_unserialize( $result->param );
		$singleton->setValue( $atts['id'], $param );

		return '';
	}

	public function assets(): void {
		$this->check_display();
		$this->check_shortcode();

		$singleton = Singleton::getInstance();
		$args      = $singleton->getValue();

		foreach ( $args as $id => $param ) {
			$style = new EnqueueStyle( $id, $param );
			$style->init();

			$script = new EnqueueScript( $id, $param );
			$script->init();
		}
	}


	public function footer(): void {
		$handle          = WOWP_Plugin::SLUG;
		$assets          = plugin_dir_url( __FILE__ ) . 'assets/';
		$assets          = apply_filters( WOWP_Plugin::PREFIX . '_frontend_assets', $assets );
		$version         = WOWP_Plugin::info( 'version' );
		$url_fontawesome = WOWP_Plugin::url() . 'vendors/fontawesome/css/all.css';

		$singleton = Singleton::getInstance();
		$args      = $singleton->getValue();

		if ( empty( $args ) ) {
			return;
		}

		foreach ( $args as $id => $param ) {
			$content = new WOWP_Maker( $id, $param, );
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Safe output, handled inside Content::init()
			echo $content->init();
		}
	}

	private function check_display(): void {
		$results = DBManager::get_all_data();
		if ( $results !== false ) {
			$singleton = Singleton::getInstance();
			foreach ( $results as $result ) {
				$param = maybe_unserialize( $result->param );
				if ( Display::init( $result->id, $param ) === true && Conditions::init( $result ) === true ) {
					$singleton->setValue( $result->id, $param );
				}
			}
		}
	}

	private function check_shortcode(): void {
		global $post;
		$shortcode = WOWP_Plugin::SHORTCODE;
		$singleton = Singleton::getInstance();

		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, $shortcode ) ) {
			$pattern = get_shortcode_regex( [ $shortcode ] );
			if ( preg_match_all( '/' . $pattern . '/s', $post->post_content, $matches )
			     && array_key_exists( 2, $matches )
			     && in_array( $shortcode, $matches[2] )
			) {
				foreach ( $matches[3] as $attrs ) {
					$attrs = shortcode_parse_atts( $attrs );
					if ( $attrs && is_array( $attrs ) && array_key_exists( 'id', $attrs ) ) {
						$result = DBManager::get_data_by_id( $attrs['id'] );

						if ( ! empty( $result->param ) ) {
							$param = maybe_unserialize( $result->param );
							if ( Conditions::init( $result ) === true ) {
								$singleton->setValue( $attrs['id'], $param );
							}
						}
					}
				}
			}
		}
	}


}