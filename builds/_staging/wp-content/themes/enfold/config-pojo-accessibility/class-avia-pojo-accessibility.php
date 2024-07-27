<?php
/**
 * Implements support for plugin "One Click Accessibility" ( https://de.wordpress.org/plugins/pojo-accessibility/  )
 *
 * @added_by Guenter
 * @since 4.8.7
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

if( ! class_exists( 'Avia_Pojo_Accessibility', false ) )
{

	class Avia_Pojo_Accessibility
	{
		/**
		 * Holds the instance of this class
		 *
		 * @since 4.8.7
		 * @var Avia_Pojo_Accessibility
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
		 * @return Avia_Pojo_Accessibility
		 */
		static public function instance()
		{
			if( is_null( Avia_Pojo_Accessibility::$_instance ) )
			{
				Avia_Pojo_Accessibility::$_instance = new Avia_Pojo_Accessibility();
			}

			return Avia_Pojo_Accessibility::$_instance;
		}

		/**
		 * @since 4.8.7
		 */
		protected function __construct()
		{
			$this->options = array();

			//	hook late to override plugins CSS
			add_filter( 'wp_head', array( $this, 'handler_wp_head' ), 500 );
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

			/**
			 * Add a focus caret around menu items
			 *
			 * Preepared to add theme default colors to plugin.
			 * Currently not supported.
			 */
			$pojo_a11y = get_option( 'pojo_a11y_customizer_options' );

			if( ! is_array( $pojo_a11y ) )
			{
				return;
			}

			$rules = $this->default_caret_rules( $pojo_a11y );;

			if( ! empty( $rules ) )
			{
				$style  = '<style type="text/css" id="avia-pojo-accessibility-override">' . "\n";
				$style .=		$rules;
				$style .= '</style>';

				echo $style;
			}
		}

		/**
		 * @since 4.8.7
		 * @param array $pojo_a11y
		 * @return string
		 */
		protected function default_caret_rules( array $pojo_a11y = array() )
		{
			$css = '';

			$pojo_width = isset( $pojo_a11y['a11y_focus_outline_width'] ) ? $pojo_a11y['a11y_focus_outline_width'] : '2px';
			$pojo_style = isset( $pojo_a11y['a11y_focus_outline_style'] ) ? $pojo_a11y['a11y_focus_outline_style'] : 'dotted';
			$pojo_color = isset( $pojo_a11y['a11y_focus_outline_color'] ) ? $pojo_a11y['a11y_focus_outline_color'] : '';

			$sel = array();
			$rules = array();

			$rules[] = "outline-width: 0px !important;";
			$rules[] = "border-width: {$pojo_width} !important;";
			$rules[] = "border-style: {$pojo_style} !important;";
			if( ! empty( $pojo_color ) )
			{
				$rules[] = "border-color: {$pojo_color} !important;";
			}

			$rules[] = 'overflow: visible !important;';

			$sel[] = 'body#top.pojo-a11y-focusable .main_menu ul li a:focus';
			$sel[] = 'body#top.pojo-a11y-focusable .sub_menu ul li a:focus';
			$sel[] = 'body#top.pojo-a11y-focusable .sub_menu li li a:focus';
			$sel[] = 'body#top.pojo-a11y-focusable .av-subnav-menu a:focus';

			$sel[] = 'body#top.pojo-a11y-focusable .widget_pages ul li a:focus';
			$sel[] = 'body#top.pojo-a11y-focusable .widget_nav_menu ul li a:focus';


			$css .= implode( ",\n", $sel ) . "{\n";
			$css .= implode( "\n", $rules ) . "\n}\n";

			$css .= "

body#top.pojo-a11y-focusable .widget_pages ul li a:focus,
body#top.pojo-a11y-focusable .widget_nav_menu ul li a:focus{
	padding-left: 5px;
	padding-right: 5px;
}

";

			return $css;
		}

		/**
		 * Currently only a dummy to adjust color of focus caret to theme colors
		 *
		 * @since 4.8.7
		 * @param array $pojo_a11y
		 * @return string
		 */
		protected function custom_caret_rules( array $pojo_a11y = array() )
		{
			global $avia_config;

			$rules = '';

			$pojo_width = isset( $pojo_a11y['a11y_focus_outline_width'] ) ? $pojo_a11y['a11y_focus_outline_width'] : 2;
			$pojo_style = isset( $pojo_a11y['a11y_focus_outline_style'] ) ? $pojo_a11y['a11y_focus_outline_style'] : 'dotted';

			$color_set = $avia_config['backend_colors']['color_set'];

			foreach( $color_set as $key => $colors )
			{
				$key = ".$key";

				extract( $colors );

				$pojo_color1 = isset( $pojo_a11y['a11y_focus_outline_color'] ) ? $pojo_a11y['a11y_focus_outline_color'] : $color;
				$pojo_color2 = isset( $pojo_a11y['a11y_focus_outline_color'] ) ? $pojo_a11y['a11y_focus_outline_color'] : $secondary;

				$rules .= "

body#top.pojo-a11y-focusable $key .main_menu ul li a:focus,
body#top.pojo-a11y-focusable $key .sub_menu ul li a:focus,
body#top.pojo-a11y-focusable $key .sub_menu li li a:focus,
body#top.pojo-a11y-focusable $key .av-subnav-menu a:focus{
	outline-width: 0px !important;
	border: $pojo_width $pojo_style $pojo_color1 !important;
	overflow: visible !important;
}

body#top.pojo-a11y-focusable $key .widget_pages ul li a:focus,
body#top.pojo-a11y-focusable $key .widget_nav_menu ul li a:focus{
	outline-width: 0px !important;
	border: $pojo_width $pojo_style $pojo_color2 !important;
	overflow: visible !important;
}

body#top.pojo-a11y-focusable $key .widget_pages ul li a:focus,
body#top.pojo-a11y-focusable $key .widget_nav_menu ul li a:focus{
	padding-left: 5px;
	padding-right: 5px;
}

body1#top.pojo-a11y-focusable $key.av_header_transparency .main_menu #avia-menu > li > a:focus,
body1#top.pojo-a11y-focusable $key.av_header_transparency nav.main_menu > ul > li a:focus{
	border-color: inherit !important;
}

\n";

			}

			return $rules;
		}
	}

	/**
	 * Returns the main instance of Avia_Pojo_Accessibility to prevent the need to use globals
	 *
	 * @since 4.8.7
	 * @return Avia_Pojo_Accessibility
	 */
	function AviaPojoAccessibility()
	{
		return Avia_Pojo_Accessibility::instance();
	}

	AviaPojoAccessibility();
}
