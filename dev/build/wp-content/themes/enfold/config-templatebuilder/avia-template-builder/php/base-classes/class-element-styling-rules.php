<?php
namespace aviaBuilder\base;

/**
 * This base class implements special styling rules for compatibility with all browsers
 *
 *
 * @author		Günter
 * @since 4.8.4
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( __NAMESPACE__ . '\aviaElementStylingRules', false ) )
{

	class aviaElementStylingRules extends \aviaBuilder\base\aviaElementStylingBase
	{
		/**
		 * Flag to indicate use of fallback CSS for old browsers
		 * Stored in a static variable to minify filter call to theme option
		 *
		 * @since 5.6.3
		 * @var boolean
		 */
		static protected $fallback_css = null;


		/**
		 *
		 * @since 4.8.4
		 * @param \aviaShortcodeTemplate $shortcode
		 * @param string $element_id
		 */
		protected function __construct( \aviaShortcodeTemplate $shortcode = null, $element_id = '' )
		{
			parent::__construct( $shortcode, $element_id );

			if( ! is_bool( aviaElementStylingRules::$fallback_css ) )
			{
				/**
				 *
				 * @used_by							functions.php / avia_old_browser_support()
				 * @since 5.6.3
				 * @param boolean $fallback_css
				 * @param string $context					'css' | 'js'
				 * @return boolean
				 */
				aviaElementStylingRules::$fallback_css = apply_filters( 'avf_old_browser_support', false, 'css' );
			}
		}

		/**
		 * @since 4.8.4
		 */
		public function __destruct()
		{
			parent::__destruct();
		}

		/**
		 * Returns all transition rules for a combined string
		 *
		 * @since 4.8.4
		 * @param string $rule_string
		 * @return array
		 */
		public function transition_rules( $rule_string )
		{
			if( aviaElementStylingRules::$fallback_css )
			{
				$transition = array(
							'-webkit-transition'	=> $rule_string,
							'-moz-transition'		=> $rule_string,
							'-ms-transition'		=> $rule_string,
							'-o-transition'			=> $rule_string,
							'transition'			=> $rule_string
						);
			}
			else
			{
				$transition = array(
							'transition'			=> $rule_string
						);
			}

			/**
			 * @since 4.8.4
			 * @param array $transition
			 * @param string $rule_string
			 * @return array
			 */
			return apply_filters( 'avf_css_rules_transition', $transition, $rule_string );
		}

		/**
		 * Returns all animation-duration rules for a string
		 * Defaults to s
		 *
		 * @since 5.0
		 * @param string $rule_string
		 * @return array
		 */
		public function transition_duration_rules( $rule_string )
		{
			if( is_numeric( $rule_string ) )
			{
				$rule_string .= 's';
			}

			if( aviaElementStylingRules::$fallback_css )
			{
				$duration = array(
							'-webkit-transition-duration'	=> $rule_string,
							'-moz-transition-duration'		=> $rule_string,
							'-ms-transition-duration'		=> $rule_string,
							'-o-transition-duration'		=> $rule_string,
							'transition-duration'			=> $rule_string
						);
			}
			else
			{
				$duration = array(
							'transition-duration'			=> $rule_string
						);
			}

			/**
			 * @since 5.0
			 * @param array $duration
			 * @param string $rule_string
			 * @return array
			 */
			return apply_filters( 'avf_css_rules_transition_duration', $duration, $rule_string );
		}

		/**
		 * Returns all transform rules for a rule string
		 *
		 * @since 4.8.4
		 * @param string $rule_string
		 * @return array
		 */
		public function transform_rules( $rule_string )
		{
			if( aviaElementStylingRules::$fallback_css )
			{
				$transform = array(
							'-webkit-transform'	=> $rule_string,
							'-moz-transform'	=> $rule_string,
							'-ms-transform'		=> $rule_string,
							'-o-transform'		=> $rule_string,
							'transform'			=> $rule_string
						);
			}
			else
			{
				$transform = array(
							'transform'			=> $rule_string
						);
			}

			/**
			 * @since 4.8.4
			 * @param array $transform
			 * @param string $rule_string
			 * @return array
			 */
			return apply_filters( 'avf_css_rules_transform', $transform, $rule_string );
		}



		/**
		 * Returns all animation rules for a combined string
		 *
		 * @since 4.8.4
		 * @param string $rule_string
		 * @return array
		 */
		public function animation_rules( $rule_string )
		{
			if( aviaElementStylingRules::$fallback_css )
			{
				$animation = array(
							'-webkit-animation'	=> $rule_string,
							'-moz-animation'	=> $rule_string,
							'-o-animation'		=> $rule_string,
							'animation'			=> $rule_string
						);
			}
			else
			{
				$animation = array(
							'animation'			=> $rule_string
						);
			}

			/**
			 * @since 4.8.4
			 * @param array $animation
			 * @param string $rule_string
			 * @return array
			 */
			return apply_filters( 'avf_css_rules_animation', $animation, $rule_string );
		}

		/**
		 * Returns all box-shadow rules for a combined string
		 *
		 * @since 4.8.4
		 * @param string $rule_string
		 * @return array
		 */
		public function box_shadow_rules( $rule_string )
		{
			if( aviaElementStylingRules::$fallback_css )
			{
				$box_shadow = array(
							'-webkit-box-shadow'	=> $rule_string,
							'-moz-box-shadow'		=> $rule_string,
							'box-shadow'			=> $rule_string
						);
			}
			else
			{
				$box_shadow = array(
							'box-shadow'			=> $rule_string
						);
			}


			/**
			 * @since 4.8.4
			 * @param array $box_shadow
			 * @param string $rule_string
			 * @return array
			 */
			return apply_filters( 'avf_css_rules_box_shadow', $box_shadow, $rule_string );
		}

		/**
		 * Returns all box-shadow rules for a combined string
		 *
		 * @since 4.8.4
		 * @param string $rule_string
		 * @return array
		 */
		public function border_radius_rules( $rule_string )
		{
			if( aviaElementStylingRules::$fallback_css )
			{
				$border_radius = array(
							'-webkit-border-radius'	=> $rule_string,
							'-moz-border-radius'	=> $rule_string,
							'border-radius'			=> $rule_string
						);
			}
			else
			{
				$border_radius = array(
							'border-radius'			=> $rule_string
						);
			}

			/**
			 * @since 4.8.4
			 * @param array $border_radius
			 * @param string $rule_string
			 * @return array
			 */
			return apply_filters( 'avf_css_rules_border_radius', $border_radius, $rule_string );
		}

		/**
		 * Returns the position rules for an element.
		 * Base is the array returned by multi input field.
		 *
		 * @since 5.0
		 * @param array $rules
		 * @param array $where
		 * @return array
		 */
		public function position_rules( array $rules, array $where = array( 'top', 'right', 'bottom', 'left' ) )
		{
			//	ensure we have 4 elements
			$location = array_merge( array( 'top', 'right', 'bottom', 'left' ), $where );

			$position = array();

			foreach( $rules as $index => $rule )
			{
				if( trim( $rule ) != '' )
				{
					if( is_numeric( $rule ) )
					{
						$rule .= 'px';
					}

					$position[ $location[ $index ] ] = $rule;
				}
			}

			/**
			 * @since 5.0
			 * @param array $position
			 * @param array $rules
			 * @param array $where
			 * @return array
			 */
			return apply_filters( 'avf_css_rules_position', $position, $rules, $where );
		}

		/**
		 * Returns all gradient color rules rules for a combined string
		 * Currently we do not add support for browser compatibility:
		 *		-moz-....
		 *		-webkit-...
		 *
		 * Seperate with , does not work in FF
		 *
		 * @since 4.8.4
		 * @param string $rule_prefix
		 * @param string $rule_colors
		 * @param string $fallback_color		for older browsers only
		 * @return array
		 */
		public function gradient_color_rules( $rule_prefix, $rule_colors, $fallback_color = '' )
		{
			$background = array();

			//	MUST be placed before background for transparent rgba colors to work
			if( ! empty( $fallback_color ) )
			{
				$background['background-color'] = $fallback_color;
			}

			$background['background'] = "$rule_prefix( {$rule_colors} )";

			/**
			 * @since 4.8.4
			 * @param array $background
			 * @param string $rule_prefix
			 * @param string $rule_colors
			 * @param string $fallback_color
			 * @return array
			 */
			return apply_filters( 'avf_css_rules_gradient_color', $background, $rule_prefix, $rule_colors, $fallback_color );
		}

		/**
		 * @since 4.8.4
		 * @since 5.0				renamed sonar_keyframes to keyframes, added $webkit_animation
		 * @param string $id
		 * @param string $animation
		 * @param string $webkit_animation
		 * @return array
		 */
		public function keyframes( $id, $animation, $webkit_animation = '' )
		{
			if( empty( $webkit_animation ) )
			{
				$webkit_animation = $animation;
			}

			if( aviaElementStylingRules::$fallback_css )
			{
				$keyframes = array(
						"@-webkit-keyframes {$id} {" . $this->new_ln . $webkit_animation . $this->new_ln . '}',
						"@keyframes {$id} {" . $this->new_ln . $animation . $this->new_ln . '}'
					);
			}
			else
			{
				$keyframes = array(
						"@keyframes {$id} {" . $this->new_ln . $animation . $this->new_ln . '}'
					);
			}

			/**
			 * @since 5.0
			 * @param array $keyframes
			 * @param string $id
			 * @param string $animation
			 * @param string $webkit_animation
			 * @return array
			 */
			return apply_filters( 'avf_css_rules_keyframes', $keyframes, $id, $animation, $webkit_animation );
		}

		/**
		 * Translate option value string for background position to % value and reorder to get compatible with
		 * minification plugins only translating position strings to % value without checking logic resulting in broken layout
		 * (e.g. https://wordpress.org/plugins/sg-cachepress/ reported https://kriesi.at/support/topic/color-section-disappeared-after-update/)
		 *
		 * Currently we only translate rules defined in Avia_Popup_Templates::background_image_position()
		 * Logic might need to be extended if necessary
		 *
		 * @since 4.8.6.1
		 * @param string $bg_pos_string
		 * @param string $default
		 * @return string
		 */
		public function background_position_string( $bg_pos_string, $default = '' )
		{
			if( ! is_string( $bg_pos_string ) )
			{
				return '';
			}

			if( empty( $bg_pos_string ) )
			{
				if( empty( $default ) )
				{
					return '';
				}

				$bg_pos_string = $default;
			}

			switch( $bg_pos_string )
			{
				case 'top left':
					$pos = '0% 0%';
					break;
				case 'top center':
					$pos = '50% 0%';
					break;
				case 'top right':
					$pos = '100% 0%';
					break;
				case 'bottom left':
					$pos = '0% 100%';
					break;
				case 'bottom center':
					$pos = '50% 100%';
					break;
				case 'bottom right':
					$pos = '100% 100%';
					break;
				case 'center left':
					$pos = '0% 50%';
					break;
				case 'center center':
					$pos = '50% 50%';
					break;
				case 'center right':
					$pos = '100% 50%';
					break;
				default:
					$pos = '0% 0%';
					break;
			}

			return $pos;
		}

		/**
		 * Returns rules for grayscale
		 *
		 * @since 4.8.7
		 * @param string $grayscale_value
		 * @return array
		 */
		public function grayscale_rule( $grayscale_value )
		{
			if( ! is_numeric( $grayscale_value ) || empty( $grayscale_value ) )
			{
				$grayscale_value = 0;
			}

			if( aviaElementStylingRules::$fallback_css )
			{
				$grayscale = array(
							'-webkit-filter'	=>  "grayscale($grayscale_value%)",
							'filter'			=>  "grayscale($grayscale_value%)"
						);
			}
			else
			{
				$grayscale = array(
							'filter'			=>  "grayscale($grayscale_value%)"
						);
			}

			/**
			 * @since 4.8.7
			 * @param array $grayscale
			 * @param string $grayscale_value
			 * @return array
			 */
			return apply_filters( 'avf_css_rules_grayscale', $grayscale, $grayscale_value );
		}

		/**
		 * Returns rules for blur
		 *
		 * @since 5.1.2
		 * @param string $blur_value
		 * @return array
		 */
		public function blur_rule( $blur_value )
		{
			if( ! is_numeric( $blur_value ) || empty( $blur_value ) )
			{
				$blur_value = 0;
			}

			if( aviaElementStylingRules::$fallback_css )
			{
				$blur = array(
							'-webkit-filter'	=>  "blur({$blur_value}px)",
							'filter'			=>  "blur({$blur_value}px)"
						);
			}
			else
			{
				$blur = array(
							'filter'			=>  "blur({$blur_value}px)"
						);
			}

			/**
			 * @since 5.1.2
			 * @param array $blur
			 * @param string $blur_value
			 * @return array
			 */
			return apply_filters( 'avf_css_rules_blur', $blur, $blur_value );
		}

		/**
		 * Returns all column count rules for a textblock
		 *
		 * @since 4.8.8
		 * @param string $rule_string
		 * @return array
		 */
		public function textblock_column_count_rules( $rule_string )
		{
			if( aviaElementStylingRules::$fallback_css )
			{
				$column_count = array(
							'-webkit-column-count'	=> $rule_string,
							'-moz-column-count'		=> $rule_string,
							'column-count'			=> $rule_string
						);
			}
			else
			{
				$column_count = array(
							'column-count'			=> $rule_string
						);
			}

			/**
			 * @since 4.8.8
			 * @param array $column_count
			 * @param string $rule_string
			 * @return array
			 */
			return apply_filters( 'avf_css_rules_textblock_column_count', $column_count, $rule_string );
		}

		/**
		 * Returns all column gap rules for a textblock
		 *
		 * @since 4.8.8
		 * @param string $rule_string
		 * @return array
		 */
		public function textblock_column_gap_rules( $rule_string )
		{
			if( aviaElementStylingRules::$fallback_css )
			{
				$column_gap = array(
							'-webkit-column-gap'	=> $rule_string,
							'-moz-column-gap'		=> $rule_string,
							'column-gap'			=> $rule_string
						);
			}
			else
			{
				$column_gap = array(
							'column-gap'			=> $rule_string
						);
			}

			/**
			 * @since 4.8.8
			 * @param array $column_gap
			 * @param string $rule_string
			 * @return array
			 */
			return apply_filters( 'avf_css_rules_textblock_column_gap', $column_gap, $rule_string );
		}

		/**
		 * Returns all animation-duration rules for a string
		 * Defaults to s
		 *
		 * @since 5.0
		 * @param string $rule_string
		 * @return array
		 */
		public function animation_duration_rules( $rule_string )
		{
			//	currently we only support one duration value (allowed would be  xx,xx,xx,...)
			if( is_numeric( $rule_string ) )
			{
				$rule_string .= 's';
			}

			if( aviaElementStylingRules::$fallback_css )
			{
				$animation_duration = array(
							'-webkit-animation-duration'	=> $rule_string,
							'-moz-animation-duration'		=> $rule_string,
							'animation-duration'			=> $rule_string
						);
			}
			else
			{
				$animation_duration = array(
							'animation-duration'			=> $rule_string
						);
			}

			/**
			 * @since 5.0
			 * @param array $animation_duration
			 * @param string $rule_string
			 * @return array
			 */
			return apply_filters( 'avf_css_rules_animation_duration', $animation_duration, $rule_string );
		}

		/**
		 * Returns all animation-duration rules for a string
		 * Defaults to s
		 *
		 * @since 5.0
		 * @param string $rule_string
		 * @return array
		 */
		public function animation_delay_rules( $rule_string )
		{
			//	currently we only support one duration value (allowed would be  xx,xx,xx,...)
			if( is_numeric( $rule_string ) )
			{
				$rule_string .= 's';
			}

			if( aviaElementStylingRules::$fallback_css )
			{
				$animation_duration = array(
							'-webkit-animation-delay'	=> $rule_string,
							'-moz-animation-delay'		=> $rule_string,
							'animation-delay'			=> $rule_string
						);
			}
			else
			{
				$animation_duration = array(
							'animation-delay'			=> $rule_string
						);
			}

			/**
			 * @since 5.0
			 * @param array $animation_duration
			 * @param string $rule_string
			 * @return array
			 */
			return apply_filters( 'avf_css_rules_animation_delay', $animation_duration, $rule_string );
		}

	}
}
