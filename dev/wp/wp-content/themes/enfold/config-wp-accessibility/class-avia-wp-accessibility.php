<?php
/**
 * Implements support for plugin "WP Accessibility" ( https://wordpress.org/plugins/wp-accessibility/  )
 *
 * @added_by Guenter
 * @since 4.8.7
 */

if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'Avia_WP_Accessibility', false ) )
{

	class Avia_WP_Accessibility
	{
		/**
		 * Holds the instance of this class
		 *
		 * @since 4.8.7
		 * @var Avia_WP_Accessibility
		 */
		static private $_instance = null;

		/**
		 * @since 4.8.7
		 * @var array
		 */
		protected $options;

		/**
		 * Return the instance of this class
		 *
		 * @since 4.8.7
		 * @return Avia_WP_Accessibility
		 */
		static public function instance()
		{
			if( is_null( Avia_WP_Accessibility::$_instance ) )
			{
				Avia_WP_Accessibility::$_instance = new Avia_WP_Accessibility();
			}

			return Avia_WP_Accessibility::$_instance;
		}

		/**
		 * @since 4.8.7
		 */
		protected function __construct()
		{
			$this->options = array();

			//	hook late to override plugins CSS
			add_filter( 'wp_head', array( $this, 'handler_wp_head' ), 500 );
			add_filter( 'body_class', array( $this, 'handler_wp_body_class' ), 10, 2 );
		}

		/**
		 * @since 4.8.7
		 */
		public function __destruct()
		{
			unset( $this->options );
		}

		/**
		 * Output stylings inline in style tag to override plugin settings
		 *
		 * @since 4.8.7
		 */
		public function handler_wp_head()
		{
			if( empty( $this->options ) )
			{
				$this->options = avia_get_option();
			}

			$underline = ( 'on' === get_option( 'wpa_underline' ) ) ? true : false;
			$focus = ( 'on' === get_option( 'wpa_focus' ) ) ? true : false;
			$focus_color = get_option( 'wpa_focus_color' );

			//	check if there is something to do for us
			if( ! ( $underline || $focus ) )
			{
				return;
			}

			$rules = array();

			/**
			 * Add "underline" to a tags - can be filtered
			 *
			 * Needed because plugin default implementation does not work for menus
			 */
			if( $underline )
			{
				$rules[] = $this->underline_rules();
			}

			/**
			 * Add a focus caret around menu items
			 *
			 * Preepared to add theme default colors to plugin.
			 * Currently not supported.
			 */
			if( $focus )
			{
				$rules[] = $this->focus_caret_rules( $focus_color );
			}

			if( ! empty( $rules ) )
			{
				$rules = array_filter( $rules );

				$style  = '<style type="text/css" id="avia-wp-accessibility-override">' . "\n";
				$style .=		implode( "\n", $rules );;
				$style .= '</style>';

				echo $style;
			}
		}

		/**
		 * Add an additional class to identify that user has selected focus rect
		 *
		 * @since 4.8.7
		 * @param array $classes
		 * @param array $class
		 * @return array
		 */
		public function handler_wp_body_class( $classes, $class )
		{
			if( 'on' === get_option( 'wpa_focus' ) )
			{
				$classes[] = 'wp-accessibility-focusable';
			}

			return $classes;
		}

		/**
		 * @since 4.8.7
		 * @return string
		 */
		protected function underline_rules()
		{
			$css = "

body #wrap_all a{
	text-decoration: underline !important;
}

";

			/**
			 * @since 4.8.7
			 * @param string $css
			 * @return string
			 */
			return apply_filters( 'avf_wp_accessibility_underline', $css );
		}

		/**
		 * @since 4.8.7
		 * @param string $color
		 * @return string
		 */
		protected function focus_caret_rules( $color = '' )
		{
			$css = '';

			/**
			 * @since 4.8.7
			 *
			 */
			$width = apply_filters( 'avf_wp_accessibility_outline_width', '1px' );
			$style = apply_filters( 'avf_wp_accessibility_outline_style', 'solid' );

			if( ! empty( $color ) )
			{
				$match = array();
				preg_match( "/^[a-f0-9]{2,}$/i", $color, $match, PREG_OFFSET_CAPTURE );

				//	if user entered a named color value (or incorrect color) we leave it
				if( ! empty( $match ) && isset( $match[0][0] ) )
				{
					$color = '#' . $match[0][0];
				}
			}
			else
			{
				$color = '#000000';
			}

			$sel = array();
			$rules = array();

			$rules[] = 'outline-width: 0px !important;';
			$rules[] = "border-width: {$width} !important;";
			$rules[] = "border-style: {$style} !important;";
			$rules[] = "border-color: {$color} !important;";
			$rules[] = 'overflow: visible !important;';

			$sel[] = 'body#top.wp-accessibility-focusable .main_menu ul li a:focus';
			$sel[] = 'body#top.wp-accessibility-focusable .sub_menu ul li a:focus';
			$sel[] = 'body#top.wp-accessibility-focusable .sub_menu li li a:focus';
			$sel[] = 'body#top.wp-accessibility-focusable .av-subnav-menu a:focus';

			$sel[] = 'body#top.wp-accessibility-focusable .widget_pages ul li a:focus';
			$sel[] = 'body#top.wp-accessibility-focusable .widget_nav_menu ul li a:focus';


			$css .= implode( ",\n", $sel ) . "{\n";
			$css .= implode( "\n", $rules ) . "\n}\n";

			$css .= "

body#top.wp-accessibility-focusable :focus{
	outline: unset !important;
	outline-offset: unset !important;
}

body#top.wp-accessibility-focusable .widget_pages ul li a:focus,
body#top.wp-accessibility-focusable .widget_nav_menu ul li a:focus{
	padding-left: 5px;
	padding-right: 5px;
}

";

			return $css;
		}

	}

	/**
	 * Returns the main instance of Avia_WP_Accessibility to prevent the need to use globals
	 *
	 * @since 4.8.7
	 * @return Avia_WP_Accessibility
	 */
	function AviaWPAccessibility()
	{
		return Avia_WP_Accessibility::instance();
	}

	AviaWPAccessibility();
}
