<?php

namespace FloatingButton;

defined( 'ABSPATH' ) || exit;

use FloatingButton\Dashboard\DBManager;
use FloatingButton\Publisher\Conditions;
use FloatingButton\Publisher\Display;
use FloatingButton\Publisher\EnqueueScript;
use FloatingButton\Publisher\EnqueueStyle;
use FloatingButton\Publisher\PageTemplate;
use FloatingButton\Publisher\Shortcodes;
use FloatingButton\Publisher\Singleton;

class WOWP_Public {

	public function __construct() {

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ], 50 );
		add_action( 'wp_footer', [ $this, 'display' ], 50 );
		add_shortcode( WOW_Plugin::SHORTCODE, [ $this, 'shortcode' ] );
		$this->includes();

	}


	public function enqueue_styles(): void {
		$display = Display::init();
		foreach ( $display as $id => $result ) {
			$style = new EnqueueStyle($result);
			$style->init();
		}
	}

	public function includes(): void {
		require_once 'class-menu-maker.php';
	}

	public function display(): void {

		$singleton  = Singleton::getInstance();
		$shortcodes = $singleton->getValue();
		$display = Display::init();

		foreach ( $display as $id => $result ) {
			if ( array_key_exists( $id, $shortcodes ) ) {
				continue;
			}

			echo do_shortcode( '[' . esc_attr( WOW_Plugin::SHORTCODE ) . ' id=' . absint( $id ) . ']' );

		}
	}

	public function shortcode( $atts ) {

		$atts = shortcode_atts(
			[ 'id' => "" ],
			$atts,
			WOW_Plugin::SHORTCODE
		);

		if ( ! empty( $atts['id'] ) ) {
			$result = DBManager::get_data_by_id( $atts['id'] );
		} else {
			return false;
		}

		if ( empty( $result ) ) {
			return false;
		}

		$param  = maybe_unserialize( $result->param );
		$walker = new Menu_Maker( $atts['id'], $param, $result->title );
		$menu = $walker->init();

		$style = new EnqueueStyle($result);
		$style->init();

		$singleton = Singleton::getInstance();
		$singleton->setValue( $result->id, $result );

		return $menu;

	}

}