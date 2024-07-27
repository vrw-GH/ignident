<?php
namespace aviaBuilder\base;

/**
 * This class implements support for responsive styling rules in post css files
 * used to replace calls to AviaHelper::av_mobile_sizes( $atts ) in shortcode handler
 *
 *
 * @author		Günter
 * @since 4.8.8
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( __NAMESPACE__ . '\aviaElementStylinResponsive', false ) )
{

	class aviaElementStylingResponsive extends \aviaBuilder\base\aviaElementStylingRules
	{
		/**
		 * Containes sizes for media queries
		 *
		 * @since 4.8.8
		 * @var array
		 */
		private $media_sizes;

		/**
		 * Contains array of media prefixes
		 *
		 * @since 5.0
		 * @var array
		 */
		private $media_prefix;

		/**
		 *
		 * @since 4.8.8
		 * @param \aviaShortcodeTemplate $shortcode
		 * @param string $element_id
		 */
		protected function __construct( \aviaShortcodeTemplate $shortcode = null, $element_id = '' )
		{
			parent::__construct( $shortcode, $element_id );

			/**
			 * @since 4.8.8
			 * @param array $limits
			 * @return array
			 */
			$this->media_sizes = apply_filters( 'avf_responsive_media_sizes', array(
									'av-desktop'	=> array( 990, 0 ),
									'av-medium'		=> array( 768, 989 ),
									'av-small'		=> array( 480, 767 ),
									'av-mini'		=> array( 0, 479 )
							) );

			$this->media_prefix = array( '', 'av-desktop-', 'av-medium-', 'av-small-', 'av-mini-' );
		}

		/**
		 * @since 4.8.8
		 */
		public function __destruct()
		{
			parent::__destruct();

			unset( $this->media_sizes );
			unset( $this->media_prefix );
		}

		/**
		 * Returns an array of media prefixes added to responsive id's
		 *
		 * @since 5.0
		 * @param string $add
		 * @return array
		 */
		public function get_media_prefixes( $add = '' )
		{
			if( '' == $add )
			{
				return $this->media_prefix;
			}

			$new = array();

			foreach( $this->media_prefix as $media_prefix )
			{
				$new[] = '' == $media_prefix ? $media_prefix : $media_prefix . $add;
			}

			return $new;
		}

		/**
		 * Adds classes to given container to hide element depending on screen width
		 *
		 * @since 4.8.8
		 * @since 4.8.9							added $font_id and 'font_sizes' for av-...-font-size-overwrite
		 * @param string $container
		 * @param string $what					'hide_element' | 'columns' | 'font_sizes'
		 * @param array $atts
		 * @param string $font_id
		 */
		public function add_responsive_classes( $container, $what = 'hide_element', array $atts = array(), $font_id = '' )
		{
			$classes = array();

			switch( $what )
			{
				case 'columns':
					$this->responsive_columns_classes( $classes, $atts );
					break;
				case 'hide_element':
					$this->responsive_hide_element_classes( $classes, $atts );
					break;
				case 'font_sizes':
					$this->responsive_font_sizes_classes( $classes, $atts, $font_id );
					break;
			}

			if( ! empty( $classes ) )
			{
				$this->add_classes( $container, $classes );
			}
		}

		/**
		 * Return the class strings
		 *
		 * @since 4.8.8
		 * @param array $atts
		 * @return string
		 */
		public function responsive_classes_string( $what = 'hide_element', array $atts = array() )
		{
			$classes = array();

			switch( $what )
			{
				case 'columns':
					$this->responsive_columns_classes( $classes, $atts );
					break;
				case 'hide_element':
					$this->responsive_hide_element_classes( $classes, $atts );
					break;
			}

			return ! empty( $classes ) ? trim( implode( ' ', $classes ) ) : '';
		}

		/**
		 * Add responsive font sizes media queries to container
		 *
		 * @since 4.8.8
		 * @since 4.8.8.1						added $sc_context
		 * @since 4.8.9							added $important
		 * @param string $container
		 * @param string $font_id
		 * @param array $atts
		 * @param \aviaShortcodeTemplate|null $sc_context
		 * @param string $important
		 */
		public function add_responsive_font_sizes( $container, $font_id, array $atts = array(), \aviaShortcodeTemplate $sc_context = null, $important = '' )
		{
			/**
			 * Allow to skip responsive font handling on element basis
			 *
			 * @since 4.8.8.1
			 * @param boolean $skip
			 * @param array $atts
			 * @param \aviaShortcodeTemplate|null $sc_context
			 * @param string $font_id
			 * @param string $container
			 */
			if( false !== apply_filters( 'avf_el_styling_responsive_font_size_skip', false, $atts, $sc_context, $font_id, $container ) )
			{
				return;
			}

			$prefixes = $this->get_media_prefixes( 'font-' );
			$hidden_by_default = false;
			$hidden_desktop = false;
			$hide_desktop_fonts = false;		//	false | true | display rule (e.g. block, inline-block)

			if( $sc_context instanceof \aviaShortcodeTemplate && isset( $sc_context->config['hide_desktop_fonts'] ) )
			{
				$hide_desktop_fonts = $sc_context->config['hide_desktop_fonts'];
			}

			foreach( $prefixes as $prefix )
			{
				$key = $prefix . $font_id;
				$value = isset( $atts[ $key ] ) ? trim( $atts[ $key ] ) : '';

				if( is_numeric( $value ) )
				{
					$value .= 'px';
				}

				$css_val = trim( "$value $important" );

				if( '' == $prefix )
				{
					if( '' == $value )
					{
						continue;
					}

					if( $value != 'hidden' )
					{
						$this->add_styles( $container, array( 'font-size' => $css_val ) );
					}
					else
					{
						$hidden_by_default = true;

						if( false !== $hide_desktop_fonts )
						{
							$this->add_styles( $container, array( 'display' => 'none' ) );
							$hidden_desktop = true;
						}
					}

					continue;
				}

				$media = $this->media_sizes[ str_replace( '-font-', '', $prefix ) ];

				if( false !== strpos( $prefix, 'desktop' ) )
				{
					if( '' == $value )
					{
						continue;
					}

					if( 'hidden' == $value )
					{
						if( $hidden_by_default )
						{
							continue;
						}

						if( $hide_desktop_fonts !== false )
						{
							$rule = array( 'display' => 'none' );
							$hidden_desktop = true;
						}
					}
					else
					{
						$rule = array( 'font-size' => $css_val );

						if( $hidden_desktop && is_string( $hide_desktop_fonts ) )
						{
							$rule['display'] = $hide_desktop_fonts;
						}
					}
				}
				else
				{
					if( '' == $value && $hidden_by_default || 'hidden' == $value )
					{
						$rule = array( 'display' => 'none' );
					}
					else if( '' == $value )
					{
						continue;
					}
					else
					{
						$rule = array( 'font-size' => $css_val );

						if( $hidden_desktop && is_string( $hide_desktop_fonts ) )
						{
							$rule['display'] = $hide_desktop_fonts;
						}
					}
				}

				$mq_rule = array( 'screen' => array( "$media[0];$media[1]" => $rule ) );
				$this->add_media_queries( $container, $mq_rule );
			}
		}

		/**
		 * Add responsive element position media queries to container
		 *
		 * @since 5.0
		 * @param string $container
		 * @param string $id
		 * @param array $atts
		 * @param \aviaShortcodeTemplate|null $sc_context
		 * @param null|string $important					null | '!important'
		 * @return int
		 */
		public function add_responsive_styles( $container, $id, array $atts = array(), \aviaShortcodeTemplate $sc_context = null, $important = null )
		{
			$added = 0;

			/**
			 * Allow to skip responsive styles handling on element basis
			 *
			 * @since 5.0
			 * @since 5.3							added $this
			 * @param boolean $skip
			 * @param array $atts
			 * @param \aviaShortcodeTemplate|null $sc_context
			 * @param string $id
			 * @param string $container
			 * @param aviaElementStyling $this
			 * @return false|mixed						anything except false will return
			 */
			if( false !== apply_filters( 'avf_el_styling_responsive_styles_skip', false, $atts, $sc_context, $id, $container, $this ) )
			{
				return $added;
			}

			/**
			 * Allow to ignore responsive styles handling on element basis and use for all screen sizes
			 *
			 * @since 5.3
			 * @param boolean $skip
			 * @param array $atts
			 * @param \aviaShortcodeTemplate|null $sc_context
			 * @param string $id
			 * @param string $container
			 * @param aviaElementStyling $this
			 * @return false|mixed						anything except false will only use default setting for
			 */
			if( false !== apply_filters( 'avf_el_styling_responsive_styles_ignore', false, $atts, $sc_context, $id, $container, $this ) )
			{
				$rule = isset( $this->callback_settings[ $id ]['styles'] ) ? $this->callback_settings[ $id ]['styles'] : array();

				if( ! is_array( $rule ) || empty( $rule ) )
				{
					return $added;
				}

				$this->add_styles( $container, $rule );
				$added++;

				return $added;
			}

			$prefixes = $this->get_media_prefixes();

			foreach( $prefixes as $prefix )
			{
				$key = $prefix . $id;
				$rule = isset( $this->callback_settings[ $key ]['styles'] ) ? $this->callback_settings[ $key ]['styles'] : array();

				if( ! is_array( $rule ) || empty( $rule ) )
				{
					continue;
				}

				if( ! empty( $important ) )
				{
					foreach( $rule as &$value )
					{
						$value .= " {$important}";
					}

					unset( $value );
				}

				if( '' == $prefix )
				{
					$this->add_styles( $container, $rule );
				}
				else
				{
					$media_size = substr( $prefix, 0, strlen( $prefix ) - 1 );
					$media = $this->media_sizes[ $media_size ];

					$mq_rule = array( 'screen' => array( "$media[0];$media[1]" => $rule ) );
					$this->add_media_queries( $container, $mq_rule );
				}

				$added++;
			}

			return $added;
		}

		/**
		 * Adds classes to change number of columns depending on screen width
		 *
		 * @since 4.8.8
		 * @param array &$classes
		 * @param array $atts
		 */
		protected function responsive_columns_classes( array &$classes, array $atts = array() )
		{
			$column_atts = array( 'av-desktop-columns', 'av-medium-columns', 'av-small-columns', 'av-mini-columns' );

			foreach( $column_atts as $key )
			{
				if( ! empty( $atts[ $key ] ) )
				{
					$classes[] = "{$key}-overwrite";
					$classes[] = "{$key}-{$atts[ $key ]}";
				}
			}
		}

		/**
		 * Adds classes to array $classes to hide element depending on screen width
		 *
		 * @since 4.8.8
		 * @param array &$classes
		 * @param array $atts
		 * @return string
		 */
		protected function responsive_hide_element_classes( array &$classes, array $atts = array() )
		{
			$display_atts = array( 'av-desktop-hide', 'av-medium-hide', 'av-small-hide', 'av-mini-hide' );

			foreach( $display_atts as $key )
			{
				if( ! empty( $atts[ $key ] ) )
				{
					$classes[] = $key;
				}
			}
		}

		/**
		 * Add a class
		 *
		 * @since 4.8.9
		 * @param array $classes
		 * @param array $atts
		 * @param string $font_id
		 */
		protected function responsive_font_sizes_classes( array &$classes, array $atts = array(), $font_id = '' )
		{
			if( empty( $font_id ) )
			{
				return;
			}

			$prefixes = array( '', 'av-medium-font-', 'av-small-font-', 'av-mini-font-' );

			foreach( $prefixes as $key )
			{
				if( ! empty( $atts[ $key . $font_id ] ) )
				{
					if( '' == $key )
					{
						$classes[] = 'av-font-size-overwrite-css';
					}
					else
					{
						$classes[] = $key . 'size-overwrite-css';
					}
				}
			}
		}

	}
}
