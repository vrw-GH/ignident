<?php

namespace FloatingButton\Publisher;

defined( 'ABSPATH' ) || exit;

use FloatingButton\Optimization\CSSMinifier;
use FloatingButton\WOW_Plugin;

class EnqueueStyle {

	/**
	 * @var mixed
	 */
	private $id;
	private $param;

	public function __construct( $result ) {
		$this->id    = $result->id;
		$this->param = maybe_unserialize( $result->param );
	}

	public function init(): void {
		$param   = $this->param;
		$slug    = WOW_Plugin::SLUG;
		$version = WOW_Plugin::info( 'version' );
		$asset   = WOW_Plugin::url() . 'assets/';

		$pre_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$url_style = $asset . 'css/frontend' . $pre_suffix . '.css';
		wp_enqueue_style( $slug, $url_style, null, $version );

		$inline_style = $this->inline();
		wp_add_inline_style( $slug, $inline_style );

		if ( empty( $param['disable_fontawesome'] ) ) {
			$url_icons = $asset . 'vendors/fontawesome/css/fontawesome-all' . $pre_suffix . '.css';
			wp_enqueue_style( $slug . '-fontawesome', $url_icons, null, '6.6' );
		}

		if ( ! empty( $param['button_animation'] ) ) {
			$url_animation = $asset . 'css/animation' . $pre_suffix . '.css';
			wp_enqueue_style( $slug . '-animation', $url_animation, null, $version );
		}

	}

	public function inline(): string {
		$css = $this->main_btn();
		$css .= $this->size();
		$css .= $this->tooltip_size();
		$css .= $this->main_btn_anim();
		$css .= $this->offset();
		$css .= $this->items();
		$css .= $this->small_screen();
		$css .= $this->large_screen();
		$css .= $this->extra_style();

		return trim( preg_replace( '~\s+~s', ' ', $css ) );
	}


	public function items(): string {
		$param = $this->param;
		$css   = '';
		$menus = [ 'menu_1' => 'flBtn-first', 'menu_2' => 'flBtn-second' ];
		foreach ( $menus as $key => $class ) {
			$count = isset( $param[ $key ]['item_type'] ) ? count( $param[ $key ]['item_type'] ) : 0;
			for ( $i = 0; $i < $count; $i ++ ) {
				$item = $i + 1;
				$css  .= '#floatBtn-' . absint( $this->id ) . ' .' . esc_attr( $class ) . ' li:nth-child(' . absint( $item ) . ') {';
				$css  .= '--flBtn-color: ' . esc_attr( $param[ $key ]['icon_color'][ $i ] ) . ';';
				$css  .= '--flBtn-h-color: ' . esc_attr( $param[ $key ]['icon_hcolor'][ $i ] ) . ';';
				$css  .= '--flBtn-bg: ' . esc_attr( $param[ $key ]['button_color'][ $i ] ) . ';';
				$css  .= '--flBtn-h-bg: ' . esc_attr( $param[ $key ]['button_hcolor'][ $i ] ) . ';';
				$css  .= '}';
			}
		}

		return $css;
	}

	public function main_btn(): string {
		$param = $this->param;

		return ' 
		#floatBtn-' . absint( $this->id ) . ' > a,
		#floatBtn-' . absint( $this->id ) . ' > .flBtn-label {
			--flBtn-bg: ' . esc_attr( $param['button_color'] ) . ';
			--flBtn-color: ' . esc_attr( $param['icon_color'] ) . ';
			--flBtn-h-color: ' . esc_attr( $param['icon_hcolor'] ) . ';
			--flBtn-h-bg: ' . esc_attr( $param['button_hcolor'] ) . ';
		}
		#floatBtn-' . absint( $this->id ) . ' [data-tooltip] {
			--flBtn-tooltip-bg: ' . esc_attr( $param['tooltip_background'] ) . ';
			--flBtn-tooltip-color: ' . esc_attr( $param['tooltip_color'] ) . ';
		}';
	}

	public function main_btn_anim(): string {
		$param = $this->param;
		$css   = '';
		if ( ! empty(  $param['btn_anim_count'] ) ) {
			$css .= '
				#floatBtn-' . absint( $this->id ) . '.flBtn-animated {
					animation-iteration-count: ' . absint( $param['btn_anim_count'] ) . ';
				}
			';
		}

		if ( ! empty(  $param['btn_anim_delay'] ) ) {
			$css .= '
				#floatBtn-' . absint( $this->id ) . '.flBtn-animated {
					animation-delay: ' . absint( $param['btn_anim_delay'] ) . 'ms;
				}
			';
		}

		return $css;

	}

	public function size(): string {
		$param = $this->param;

		if ( $param['size'] !== 'flBtn-custom' ) {
			return '';
		}

		$css = '
		#floatBtn-' . absint( $this->id ) . ' {
			--flBtn-size: ' . absint( $param['ul_size'] ) . 'px;
			--flBtn-box: ' . absint( $param['ul_box'] ) . 'px;
			--flBtn-label-size: ' . absint( $param['label_size'] ) . 'px;
			--flBtn-label-box: ' . absint( $param['label_box'] ) . 'px;
		}';


		return $css;
	}

	public function tooltip_size(): string {
		$param = $this->param;
		$css = '';
		if ( ! empty( $param['tooltip_size_check'] ) && $param['tooltip_size_check'] === 'custom' ) {
			$css = '#floatBtn-' . absint( $this->id ) . ' {
				--flBtn-tooltip-size: ' . absint( $param['tooltip_size'] ) . 'px;
				--flBtn-tooltip-ul-size: ' . absint( $param['tooltip_ul_size'] ) . 'px;
			}';
		}
		return $css;
	}

	public function offset(): string {
		$param    = $this->param;
		$v_offset = ! empty( $param['v_offset'] ) ? $param['v_offset'] . 'px' : '0';
		$h_offset = ! empty( $param['h_offset'] ) ? $param['h_offset'] . 'px' : '0';
		$css      = '';
		if ( ! empty( $v_offset ) || ! empty( $h_offset ) ) {
			$css .= '#floatBtn-' . absint( $this->id ) . ' {
			--flBtn-v-offset: ' . esc_attr( $v_offset ) . ';
            --flBtn-h-offset: ' . esc_attr( $h_offset ) . ';
		}';

		}

		return $css;
	}

	public function small_screen(): string {
		if ( empty( $this->param['include_mobile'] ) ) {
			return '';
		}
		$screen = ! empty( $this->param['screen'] ) ? $this->param['screen'] : 480;

		return '
			@media only screen and (max-width: ' . esc_attr( $screen ) . 'px){
				#floatBtn-' . absint( $this->id ) . ' {
					display:none;
				}
			}';
	}

	public function large_screen(): string {
		if ( empty( $this->param['include_more_screen'] ) ) {
			return '';
		}
		$screen = ! empty( $this->param['screen_more'] ) ? $this->param['screen_more'] : 1200;

		return '
			@media only screen and (min-width: ' . esc_attr( $screen ) . 'px){
				#floatBtn-' . absint( $this->id ) . ' {
					display:none;
				}
			}';
	}

	public function extra_style() {
		if ( empty( $this->param['extra_style'] ) ) {
			return '';
		}

		return $this->param['extra_style'];

	}

}