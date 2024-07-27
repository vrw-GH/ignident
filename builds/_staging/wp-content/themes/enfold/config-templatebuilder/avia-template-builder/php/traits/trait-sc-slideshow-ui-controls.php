<?php
namespace aviaBuilder\traits;

/**
 * Helper to add classes, styles and data attribute to element styling object
 *
 * @author		Günter
 * @since 5.0
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

if( ! trait_exists( __NAMESPACE__ . '\scSlideshowUIControls' ) )
{
	trait scSlideshowUIControls
	{

		/**
		 * @since 5.0
		 */
		protected function _construct_scSlideshowUIControls()
		{

		}

		/**
		 * @since 5.0
		 */
		protected function _destruct_scSlideshowUIControls()
		{

		}

		/**
		 * Add the slideshow classes, styles, attributes and prepares slideshow json data
		 *
		 * @since 5.0
		 * @param array $args
		 * @return void
		 */
		protected function addSlideshowAttributes( array $args = array() )
		{
			if( empty( $args ) )
			{
				return;
			}

			extract( $args );

			$is_small = isset( $args['slider_nav'] ) && 'small' == $args['slider_nav'];

			if( $is_small )
			{
				$this->transformSmallSlideshowSettings( $atts );
			}

			$classes = array(
						'av-slideshow-ui',
						$atts['control_layout'],
						$atts['nav_visibility_desktop']
			);

			if( 'av-control-hidden' == $atts['control_layout'] )
			{
				$classes[] = 'av-no-slider-navigation';
				$classes[] = 'av-hide-nav-arrows';
			}
			else
			{
				if( false === strpos( $atts['slider_navigation'], 'av-navigate-arrows' ) )
				{
					$classes[] = 'av-hide-nav-arrows';
				}

				if( false === strpos( $atts['slider_navigation'], 'av-navigate-dots' ) )
				{
					$classes[] = 'av-no-slider-navigation';
				}
			}

			if( isset( $atts['hoverpause'] ) && true === $atts['hoverpause'] )
			{
				$classes[] = 'av-slider-hover-pause';
			}

			$element_styling->add_classes( 'container', $classes );

			if( 'av-control-default' == $atts['control_layout'] )
			{
				if( isset(  $atts['nav_arrow_color'] ) )
				{
					$element_styling->add_styles( 'slide-arrows', array( 'color' => $atts['nav_arrow_color'] ), 'skip_empty' );
					$element_styling->add_styles( 'slide-arrows', array( 'background-color' => $atts['nav_arrow_bg_color'] ), 'skip_empty' );
				}

				if( isset(  $atts['nav_dots_color'] ) )
				{
					$element_styling->add_styles( 'nav-dots', array( 'background-color' => $atts['nav_dots_color'] ), 'skip_empty' );
					$element_styling->add_styles( 'nav-dots-active', array( 'background-color' => $atts['nav_dot_active_color'] ), 'skip_empty' );
				}
			}

			if( $autoplay_option == $atts['autoplay'] )
			{
				$element_styling->add_classes( 'container', 'av-slideshow-autoplay' );
				$val_autoplay = true;

				if( ! empty( $atts['autoplay_stopper'] ) )
				{
					$element_styling->add_classes( 'container', 'av-loop-once' );
					$val_loop = 'once';
				}
				else
				{
					$element_styling->add_classes( 'container', 'av-loop-endless' );
					$val_loop = 'endless';
				}
			}
			else
			{
				$element_styling->add_classes( 'container', array( 'av-slideshow-manual', 'av-loop-once' ) );
				$val_autoplay = false;
				$val_loop = 'once';
			}

			if( '' == $atts['interval'] )
			{
				$atts['interval'] = 5;
			}

			if( false !== strpos( $atts['manual_stopper'], 'manual_stopper' ) )
			{
				$element_styling->add_classes( 'container', 'av-loop-manual-once' );
				$val_loop_manual = 'manual-once';
			}
			else
			{
				$element_styling->add_classes( 'container', 'av-loop-manual-endless' );
				$val_loop_manual = 'manual-endless';
			}

			$defaults = array(
							'animation'			=> 'slide',
							'autoplay'			=> false,
							'loop_autoplay'		=> 'once',
							'interval'			=> 5,
							'loop_manual'		=> 'manual-once',
							'autoplay_stopper'	=> true,
							'noNavigation'		=> false,
							'bg_slider'			=> false,
							'keep_padding'		=> '',
							'hoverpause'		=> false,
							'show_slide_delay'	=> 0
					);

			$atts = array_merge( $defaults, $atts );

			$slideshow_options = array(
									'animation'			=> $atts['animation'],
									'autoplay'			=> $val_autoplay,
									'loop_autoplay'		=> $val_loop,
									'interval'			=> floatval( $atts['interval'] ),
									'loop_manual'		=> $val_loop_manual,
									'autoplay_stopper'	=> 'aviaTBautoplay_stopper' == $atts['autoplay_stopper'],
									'noNavigation'		=> 'av-control-hidden' == $atts['control_layout'],
									'bg_slider'			=> $atts['bg_slider'] === 'true' || $atts['bg_slider'] === true
				);

			if( is_numeric( $atts['transition_speed'] ) )
			{
				$slideshow_options[ 'transitionSpeed' ] = (int) $atts['transition_speed'];
			}

			//	set hardcoded in default_args !!
			if( isset( $atts['keep_padding'] ) )
			{
				$slideshow_options['keep_padding'] = $atts['keep_padding'];
			}

			if( isset( $atts['hoverpause'] ) )
			{
				$slideshow_options['hoverpause'] = $atts['hoverpause'];
			}

			if( isset( $atts['show_slide_delay'] ) && is_numeric( $atts['show_slide_delay'] ) )
			{
				$slideshow_options['show_slide_delay'] = $atts['show_slide_delay'];
			}



			if( isset( $atts['handle'] ) )
			{
				if( 'av_fullscreen' == $atts['handle'] )
				{
					$slideshow_options['slide_height'] = isset( $atts['slide_height'] ) ? $atts['slide_height'] : '100';
					$slideshow_options['image_attachment'] = isset( $atts['image_attachment'] ) ? $atts['image_attachment'] : '';
				}
			}

			$element_styling->add_data_attributes( 'container', $slideshow_options );

			if( ! $is_small )
			{
				$selectors = array(
							'slide-arrows'		=> "#top .av-slideshow-ui.{$element_id} .avia-slideshow-arrows a",
							'nav-dots'			=> ".av-slideshow-ui.{$element_id} .avia-slideshow-dots a:not(.active)",
							'nav-dots-active'	=> ".av-slideshow-ui.{$element_id} .avia-slideshow-dots a.active"
						);
			}
			else
			{
				$selectors = array(
							'slide-arrows'		=> "#top .av-slideshow-ui.{$element_id} .avia-slideshow-arrows a",
							'nav-dots'			=> "#top .av-slideshow-ui.{$element_id} .avia-slideshow-dots a:not(.active)",
							'nav-dots-active'	=> "#top .av-slideshow-ui.{$element_id} .avia-slideshow-dots a.active"
						);
			}

			$element_styling->add_selectors( $selectors );
		}

		/**
		 * Transform settings so we can use slider navigation functions for "normal" slideshows
		 * Be careful when using this function as it changes option $atts['nav_visibility_desktop'] which might cause strange
		 * behaviour when called multiple times creating post css file.
		 *
		 * @since 5.0
		 * @param array $atts
		 */
		private function transformSmallSlideshowSettings( array &$atts )
		{
			if( empty( $atts['control_layout'] ) )
			{
				$atts['control_layout'] = 'av-control-default';
			}

			switch( $atts['navigation'] )
			{
				case 'no':
					$atts['control_layout'] = 'av-control-hidden';
					$atts['slider_navigation'] = 'av-navigate-arrows';
					break;
				case 'dots':
					$atts['slider_navigation'] = 'av-navigate-dots';
					break;
				case 'arrows':
				default;
					$atts['slider_navigation'] = 'av-navigate-arrows';
					break;
			}

			switch( $atts['nav_visibility_desktop'] )
			{
				case 'onhover':
					$atts['nav_visibility_desktop'] = '';
					break;
				case '':
				default:
					$atts['nav_visibility_desktop'] = 'av-nav-arrows-visible av-nav-dots-visible';
					break;
			}
		}
	}
}
