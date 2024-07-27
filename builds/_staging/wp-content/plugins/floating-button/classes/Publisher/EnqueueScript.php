<?php

namespace FloatingButton\Publisher;

defined( 'ABSPATH' ) || exit;

use FloatingButton\Dashboard\DBManager;
use FloatingButton\Optimization\Obfuscator;
use FloatingButton\WOW_Plugin;

class EnqueueScript {

	/**
	 * @var mixed
	 */
	private $id;
	private $param;

	public function __construct( $result ) {
		$this->id    = $result->id;
		$this->param = maybe_unserialize( $result->param );
	}

	/**
	 * @throws \JsonException
	 */
	public function init(): void {

		$slug    = WOW_Plugin::SLUG;
		$version = WOW_Plugin::info( 'version' );
		$asset   = WOW_Plugin::url() . 'assets/';

		$pre_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$url_script = $asset . 'js/script' . $pre_suffix . '.js';
		wp_enqueue_script( $slug, $url_script, array( 'jquery' ), $version, true );

		$inline_script = $this->inline();
		wp_add_inline_script( $slug, $inline_script, 'before' );

		wp_localize_script( $slug, 'wowp_flBtn', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( '_wowp_likes' ),
		) );
	}

	/**
	 * @throws \JsonException
	 */
	public function inline(): string {
		$param = $this->param;

		$arg = [
			'element' => 'floatBtn-' . absint( $this->id ),
		];

		if ( ! empty( $param['showAfterPosition'] ) ) {
			$arg['showAfterPosition'] = (int) $param['showAfterPosition'];
		}

		if ( ! empty( $param['hideAfterPosition'] ) ) {
			$arg['hideAfterPosition'] = (int) $param['hideAfterPosition'];
		}

		if ( ! empty( $param['showAfterTimer'] ) ) {
			$arg['showAfterTimer'] = (int) $param['showAfterTimer'];
		}

		if ( ! empty( $param['hideAfterTimer'] ) ) {
			$arg['hideAfterTimer'] = (int) $param['hideAfterTimer'];
		}

		if ( ! empty( $param['uncheckedBtn'] ) ) {
			$arg['uncheckedBtn'] = true;
		}

		if ( ! empty( $param['uncheckedSubBtn'] ) ) {
			$arg['uncheckedSubBtn'] = true;
		}

		if ( ! empty( $param['hideBtns'] ) ) {
			$arg['hideBtns'] = true;
		}

		if ( is_singular() ) {
			$arg['pageId'] = get_the_ID();
		}

		return 'var FloatingButton_' . absint( $this->id ) . ' = ' . wp_json_encode( $arg );

	}


}