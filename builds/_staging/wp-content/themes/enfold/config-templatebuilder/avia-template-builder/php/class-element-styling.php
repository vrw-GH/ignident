<?php
/**
 * This class provides callback methods for ALB modal popup element settings to generate style rules combining several setting fields.
 * This avoids duplicating code in shortcode handler to build the style strings.
 *
 *
 * @author		Günter
 * @since 4.8.4
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

if( ! class_exists( 'aviaElementStyling', false ) )
{
	class aviaElementStyling extends \aviaBuilder\base\aviaElementStylingResponsive
	{
		/**
		 * @since 4.8.4
		 * @param aviaShortcodeTemplate|null $shortcode
		 * @param string $element_id
		 */
		public function __construct( aviaShortcodeTemplate $shortcode = null, $element_id = '' )
		{
			parent::__construct( $shortcode, $element_id );
		}

		/**
		 * @since 4.8.4
		 */
		public function __destruct()
		{
			parent::__destruct();
		}

		/**
		 * Styles for "Box Shadow"
		 *
		 *		- $id
		 *		- $id . '_style'
		 *		- $id . '_width'
		 *		- $id . '_color'
		 *		- $id . '_duration'
		 *
		 * @since 4.8.4
		 * @since 5.0						added support for animation
		 * @param array $element
		 * @param array $atts
		 */
		protected function box_shadow( array $element, array $atts )
		{
			$callback = $element['styles_cb'];

			$id = \AviaHelper::array_value( $callback, 'id' );
			if( empty( $id ) )
			{
				return;
			}

			$id2 = \AviaHelper::array_value( $callback, 'id2', '' );

			//	ensure to have an empty structure we can rely on later
			$this->callback_settings[ $id ]['styles'] = array();

			$shadow = \AviaHelper::array_value( $atts, $id );
			if( empty( $shadow ) )
			{
				return;
			}

			//	check if checkbox is checked -> defaults to outside
			if( 'outside' == $shadow || false !== strpos( $shadow, $id ) )
			{
				$shadow = '';
			}

			$simplified = \AviaHelper::array_value( $callback, 'simplified', false );
			if( ! $simplified )
			{
				$style = \AviaHelper::array_value( $atts, $id . '_style' );
				$style = \AviaHelper::multi_value_result_lockable( $style );
				$style = $style['fill_with_0_style'];
			}
			else
			{
				$width = \AviaHelper::array_value( $atts, $id . '_width' );
				$style = "0 0 {$width}px 0";
			}

			$color = \AviaHelper::array_value( $atts, $id . '_color' );

			if( 'none' == $shadow )
			{
				$string = $shadow;
			}
			else
			{
				$string = "{$shadow} {$style} {$color}";
			}

			$animated = \AviaHelper::array_value( $callback, 'animated', false );

			if( false !== $animated )
			{
				if( 'manually' == $animated )
				{
					$duration = \AviaHelper::array_value( $atts, $id . '_duration', '' );
					if( ! is_numeric( $duration ) )
					{
						$animated = false;
					}
				}
				else
				{
					$duration = 4;
				}
			}

			$rules = $this->box_shadow_rules( $string );

			if( false === $animated || 'none' == $shadow )
			{
				$this->callback_settings[ $id ]['styles'] = array_merge( $this->callback_settings[ $id ]['styles'], $rules );
				return;
			}

			//	we save fixed rules to allow activate via class if animation not needed
			$this->callback_settings[ $id . '_not_animated' ]['styles'] = $rules;

			$id .= '_animated';

			$this->callback_settings[ $id ]['styles'] = array();
			$this->callback_settings[ $id ]['keyframes'] = array();

			$shadow_id = "av_boxShadowEffect_{$this->element_id}";
			if( ! empty( $id2 ) )
			{
				$shadow_id .= "-{$id2}";
			}

			$animation  = "0%   { box-shadow: {$shadow} 0 0 0 0 {$color}; opacity: 1; }{$this->new_ln}";
			$animation .= "100% { box-shadow: {$string}; opacity: 1; }";

			$keyframes = $this->keyframes( $shadow_id, $animation );

			$this->callback_settings[ $id ]['keyframes'] = array_merge( $this->callback_settings[ $id ]['keyframes'], $keyframes );

			$rules = "{$shadow_id} {$duration}s cubic-bezier(0.17,0.84,0.44,1) 1 forwards";
			$animation_rules = $this->animation_rules( $rules );

			$this->callback_settings[ $id ]['styles'] = array_merge( $this->callback_settings[ $id ]['styles'], $animation_rules );
		}

		/**
		 * Styles for "Border"
		 *
		 * @since 4.8.4
		 * @param array $element
		 * @param array $atts
		 */
		protected function border( array $element, array $atts )
		{
			$callback = $element['styles_cb'];

			$id = \AviaHelper::array_value( $callback, 'id' );
			if( empty( $id ) )
			{
				return;
			}

			//	ensure to have an empty structure we can rely on later
			$this->callback_settings[ $id ]['styles'] = array();

			$border = \AviaHelper::array_value( $atts, $id );
			if( empty( $border ) )
			{
				return;
			}

			$this->callback_settings[ $id ]['styles']['border-style'] = $border;

			$width = \AviaHelper::array_value( $atts, $id . '_width' );

			if( ! \AviaHelper::empty_multi_input( $width ) )
			{
				$width = AviaHelper::multi_value_result_lockable( $width );
				$this->callback_settings[ $id ]['styles']['border-width'] = $width['fill_with_0_style'];
			}

			$color = isset( $atts[ $id . '_color' ] ) ? $atts[ $id . '_color' ] : '';

			if( ! empty( $color ) )
			{
				$this->callback_settings[ $id ]['styles']['border-color'] = $color;
			}
		}

		/**
		 * Styles for "Border Radius"
		 *
		 * @since 4.8.4
		 * @param array $element
		 * @param array $atts
		 */
		protected function border_radius( array $element, array $atts )
		{
			$callback = $element['styles_cb'];

			$id = \AviaHelper::array_value( $callback, 'id' );
			if( empty( $id ) )
			{
				return;
			}

			//	ensure to have an empty structure we can rely on later
			$this->callback_settings[ $id ]['styles'] = array();

			$radius = \AviaHelper::array_value( $atts, $id );

			if( ! \AviaHelper::empty_multi_input( $radius ) )
			{
				$radius = AviaHelper::multi_value_result_lockable( $radius );
				$rules = $this->border_radius_rules( $radius['fill_with_0_style'] );

				$this->callback_settings[ $id ]['styles'] = array_merge( $this->callback_settings[ $id ]['styles'], $rules );
			}

		}

		/**
		 * Styles for "Padding"
		 *
		 * @since 4.8.5
		 * @param array $element
		 * @param array $atts
		 */
		protected function padding( array $element, array $atts )
		{
			$callback = $element['styles_cb'];

			$id = \AviaHelper::array_value( $callback, 'id' );
			if( empty( $id ) )
			{
				return;
			}

			//	ensure to have an empty structure we can rely on later
			$this->callback_settings[ $id ]['styles'] = array();

			$padding = \AviaHelper::array_value( $atts, $id );

			if( ! \AviaHelper::empty_multi_input( $padding ) )
			{
				$padding = AviaHelper::multi_value_result_lockable( $padding );
				$this->callback_settings[ $id ]['styles']['padding'] = $padding['fill_with_0_style'];
			}

		}

		/**
		 * Styles for "Margin"
		 *
		 * @since 4.8.5
		 * @param array $element
		 * @param array $atts
		 */
		protected function margin( array $element, array $atts )
		{
			$callback = $element['styles_cb'];

			$id = \AviaHelper::array_value( $callback, 'id' );
			if( empty( $id ) )
			{
				return;
			}

			//	ensure to have an empty structure we can rely on later
			$this->callback_settings[ $id ]['styles'] = array();

			$margin = \AviaHelper::array_value( $atts, $id );

			if( ! \AviaHelper::empty_multi_input( $margin ) )
			{
				$margin = AviaHelper::multi_value_result_lockable( $margin );
				$this->callback_settings[ $id ]['styles']['margin'] = $margin['fill_with_0_style'];
			}
		}

		/**
		 * Styles for "Gradient Colors"
		 *
		 * @since 4.8.4
		 * @param array $element
		 * @param array $atts
		 */
		protected function gradient_colors( array $element, array $atts )
		{
			$callback = $element['styles_cb'];

			$id = \AviaHelper::array_value( $callback, 'id' );
			if( empty( $id ) )
			{
				return;
			}

			if( ! is_array( $id ) )
			{
				$id_save = $id;
				$id_array = array(
							$id . '_direction',
							$id . '_1',
							$id . '_2',
							$id . '_3',
						);
			}
			else
			{
				$id_save = $id[0];
				$id_array = $id;
			}

			//	ensure to have an empty structure we can rely on later
			$this->callback_settings[ $id_save ]['styles'] = array();

			$direction = \AviaHelper::array_value( $atts, $id_array[0] );
			$col1 = \AviaHelper::array_value( $atts, $id_array[1] );
			$col2 = \AviaHelper::array_value( $atts, $id_array[2] );
			$col3 = \AviaHelper::array_value( $atts, $id_array[3] );

			if( empty( $col1 ) || empty( $col2 ) )
			{
				return;
			}

			$rule_prefix = 'linear-gradient';
			$rule_colors = '';
			$append3 = true;

			switch( $direction )
			{
				case 'vertical':
					$rule_colors = "to bottom, {$col1}, {$col2}";
					break;
				case 'vertical_rev':
					$rule_colors = "to top, {$col1}, {$col2}";
					break;
				case 'horizontal':
					$rule_colors = "to right, {$col1}, {$col2}";
					break;
				case 'horizontal_rev':
					$rule_colors = "to left, {$col1}, {$col2}";
					break;
				case 'diagonal_tb':
					$rule_colors = "to bottom right, {$col1}, {$col2}";
					break;
				case 'diagonal_tb_rev':
					$rule_colors = "to top left, {$col1}, {$col2}";
					break;
				case 'diagonal_bt':
					$rule_colors = "to top right, {$col1}, {$col2}";
					break;
				case 'diagonal_bt_rev':
					$rule_colors = "to bottom left, {$col1}, {$col2}";
					break;
				case 'radial':
					$rule_prefix = 'radial-gradient';
					$rule_colors = "{$col1}, {$col2}";
					break;
				case 'radial_rev':
					$rule_prefix = 'radial-gradient';
					$rule_colors = "{$col2}, {$col1}";
					$append3 = false;
					break;
			}

			if( empty( $rule_colors ) )
			{
				return;
			}

			if( ! empty( $col3 ) )
			{
				$rule_colors = $append3 ? "{$rule_colors}, {$col3}" : "{$col3}, {$rule_colors}";
			}

			$background = $this->gradient_color_rules( $rule_prefix, $rule_colors, $col1 );

			$this->callback_settings[ $id_save ]['styles'] = array_merge( $this->callback_settings[ $id_save ]['styles'], $background );
		}

		/**
		 * Styles for "Slideshow Image Scale"
		 *
		 *
		 * @since 5.0
		 * @param array $element
		 * @param array $atts
		 */
		protected function slideshow_image_scale( array $element, array $atts )
		{
			$callback = $element['styles_cb'];

			$id = \AviaHelper::array_value( $callback, 'id' );
			if( empty( $id ) )
			{
				return;
			}

			//	ensure to have an empty structure we can rely on later
			$this->callback_settings[ $id ]['styles'] = array();
			$this->callback_settings[ $id . '_active' ]['styles'] = array();
			$this->callback_settings[ $id ]['keyframes'] = array();

			$img_scale_start = \AviaHelper::array_value( $atts, $id, '' );
			if( '' == $img_scale_start )
			{
				return;
			}

			$img_scale_id = "av_slideshow_img_scale_{$this->element_id}";

			$img_scale_end = \AviaHelper::array_value( $atts, $id . '_end', '3', 'not_empty' );
			$direction = \AviaHelper::array_value( $atts, $id . '_direction' );
			$duration = \AviaHelper::array_value( $atts, $id . '_duration', '3', 'not_empty' );
			$opacity = \AviaHelper::array_value( $atts, $id . '_opacity', '1' );

			$img_scale_start = 1 + ( $img_scale_start / 100.0 );
			$img_scale_end = 1 + ( $img_scale_end / 100.0 );

			//	slides count starts with 1
			$animation_odd = '';
			$webkit_animation_odd = '';

			$animation_even = '';
			$webkit_animation_even = '';

			$animation_odd .= "   0% { transform: scale({$img_scale_start}); opacity: {$opacity}; }$this->new_ln";
			$animation_odd .= " 100% { transform: scale({$img_scale_end}); opacity: 1; }";

			$webkit_animation_odd .= "   0% { -webkit-transform: scale({$img_scale_start}); opacity: {$opacity}; }$this->new_ln";
			$webkit_animation_odd .= " 100% { -webkit-transform: scale({$img_scale_end}); opacity: 1; }";

			$animation_even .= "   0% { transform: scale({$img_scale_end}); opacity: {$opacity}; }$this->new_ln";
			$animation_even .= " 100% { transform: scale({$img_scale_start}); opacity: 1; }";

			$webkit_animation_even .= "   0% { -webkit-transform: scale({$img_scale_end}); opacity: {$opacity}; }$this->new_ln";
			$webkit_animation_even .= " 100% { -webkit-transform: scale({$img_scale_start}); opacity: 1; }";


			$keyframes = $this->keyframes( $img_scale_id, $animation_odd, $webkit_animation_odd );
			$this->callback_settings[ $id ]['keyframes'] = array_merge( $this->callback_settings[ $id ]['keyframes'], $keyframes );

			if( $direction != '' )
			{
				$keyframes = $this->keyframes( $img_scale_id . '_even', $animation_even, $webkit_animation_even );
				$this->callback_settings[ $id ]['keyframes'] = array_merge( $this->callback_settings[ $id ]['keyframes'], $keyframes );
			}

			$img_animate = "{$img_scale_id} {$duration}s 1 ease-in-out";
			$animation_rules = $this->animation_rules( $img_animate );
			$scale_rules = $this->transform_rules( "scale({$img_scale_end})");

			$this->callback_settings[ $id . '_active' ]['styles'] = array_merge( $this->callback_settings[ $id ]['styles'], $animation_rules, $scale_rules );
			$this->callback_settings[ $id . '_active' ]['styles']['opacity'] = 1;

			if( $direction != '' )
			{
				$img_animate = "{$img_scale_id}_even {$duration}s 1 ease-in-out";
				$animation_rules = $this->animation_rules( $img_animate );
				$scale_rules = $this->transform_rules( "scale({$img_scale_start})");

				$this->callback_settings[ $id . '_active_even' ]['styles'] = array_merge( $this->callback_settings[ $id ]['styles'], $animation_rules, $scale_rules );
				$this->callback_settings[ $id . '_active_even' ]['styles']['opacity'] = 1;
			}

			if( $opacity < 1 )
			{
				$this->callback_settings[ $id ]['styles']['opacity'] = $opacity;
			}
		}

		/**
		 * Styles for "Sonar Effect"
		 *
		 * @since 4.8.4
		 * @param array $element
		 * @param array $atts
		 */
		protected function sonar_effect( array $element, array $atts )
		{
			$callback = $element['styles_cb'];

			$id = \AviaHelper::array_value( $callback, 'id' );
			if( empty( $id ) )
			{
				return;
			}

			//	ensure to have an empty structure we can rely on later
			$this->callback_settings[ $id ]['styles'] = array();
			$this->callback_settings[ $id ]['keyframes'] = array();

			$effect = \AviaHelper::array_value( $atts, $id . '_effect' );
			if( empty( $effect ) )
			{
				return;
			}

			$color = \AviaHelper::array_value( $atts, $id . '_color', '#ffffff', 'not_empty' );
			$duration = \AviaHelper::array_value( $atts, $id . '_duration', '1', 'not_empty' );
			$scale = \AviaHelper::array_value( $atts, $id . '_scale', '1.5', 'not_empty' );
			$el_opac = \AviaHelper::array_value( $atts, $id . '_opac', '0.5', 'not_empty' );

			$infinite = in_array( $effect, array( 'shadow_permanent', 'pulsate_permanent', 'shadow_hover_perm', 'pulsate_hover_perm', 'element_permanent', 'element_hover_perm' ) ) ? ' .1s infinite' : '';
			$sonar_id = "av_sonarEffect_{$this->element_id}";

			$el_opac = in_array( $effect, array( 'pulsate_permanent', 'pulsate_hover_once', 'pulsate_hover_perm', 'element_permanent', 'element_hover_once', 'element_hover_perm' ) ) ? $el_opac : '0.5';
			$shadow_opac = false !== strpos( $effect, 'shadow' ) ? '0' : $el_opac;


			$animation = '';
			$webkit_animation = '';

			if( false === strpos( $effect, 'element') )
			{
				$animation .= '  0% {opacity: 0.3;}' . $this->new_ln;
				$animation .= " 40% {opacity: {$el_opac}; box-shadow: 0 0 0 2px rgba(255,255,255,0.1), 0 0 10px 10px {$color}, 0 0 0 10px rgba(255,255,255,0.5);}" . $this->new_ln;
				$animation .= "100% {opacity: {$shadow_opac}; box-shadow: 0 0 0 2px rgba(255,255,255,0.1), 0 0 10px 10px {$color}, 0 0 0 10px rgba(255,255,255,0.5); transform: scale({$scale});}";

				$webkit_animation .= '  0% {opacity: 0.3;}' . $this->new_ln;
				$webkit_animation .= " 40% {opacity: {$el_opac}; -webkit-box-shadow: 0 0 0 2px rgba(255,255,255,0.1), 0 0 10px 10px {$color}, 0 0 0 10px rgba(255,255,255,0.5);}" . $this->new_ln;
				$webkit_animation .= "100% {opacity: {$shadow_opac}; -webkit-box-shadow: 0 0 0 2px rgba(255,255,255,0.1), 0 0 10px 10px {$color}, 0 0 0 10px rgba(255,255,255,0.5); -webkit-transform: scale({$scale});}";

			}
			else
			{
				$animation .= '  0% {opacity: 0.3;}' . $this->new_ln;
				$animation .= " 50% {opacity: {$el_opac};}" . $this->new_ln;
				$animation .= "100% {opacity: {$el_opac}; transform: scale({$scale});}";

				$webkit_animation .= '  0% {opacity: 0.3;}' . $this->new_ln;
				$webkit_animation .= " 50% {opacity: {$el_opac};}" . $this->new_ln;
				$webkit_animation .= "100% {opacity: {$el_opac}; -webkit-transform: scale({$scale});}";
			}

			$keyframes = $this->keyframes( $sonar_id, $animation, $webkit_animation );

			$this->callback_settings[ $id ]['keyframes'] = array_merge( $this->callback_settings[ $id ]['keyframes'], $keyframes );

			$sonar = "{$sonar_id} {$duration}s ease-in-out{$infinite}";
			$animation_rules = $this->animation_rules( $sonar );

			$this->callback_settings[ $id ]['styles'] = array_merge( $this->callback_settings[ $id ]['styles'], $animation_rules );
		}

		/**
		 * Styles for a single "SVG divider"
		 *
		 *		- $id
		 *		- $id . '_color'
		 *
		 * @since 4.8.4
		 * @param array $element
		 * @param array $atts
		 */
		protected function svg_divider( array $element, array $atts )
		{
			$callback = $element['styles_cb'];

			$id = \AviaHelper::array_value( $callback, 'id' );
			if( empty( $id ) )
			{
				return;
			}

			//	ensure to have an empty structure we can rely on later
			$this->callback_settings[ $id ]['styles'] = array();
			$this->callback_settings[ $id ]['classes'] = array();

			$svg = \AviaHelper::array_value( $atts, $id );
			if( empty( $svg ) )
			{
				return;
			}

			$location = \AviaHelper::array_value( $callback, 'location', 'top', 'not_empty' );

			$shape = \AviaHelper::array_value( $atts, $id );
			$color = \AviaHelper::array_value( $atts, $id . '_color' );
			$height = \AviaHelper::array_value( $atts, $id . '_height' );
			$max_height = \AviaHelper::array_value( $atts, $id . '_max_height' );
			$width = \AviaHelper::array_value( $atts, $id . '_width' );
			$flip = \AviaHelper::array_value( $atts, $id . '_flip' );
			$invert = \AviaHelper::array_value( $atts, $id . '_invert' );
			$front = \AviaHelper::array_value( $atts, $id . '_front' );
			$opacity = \AviaHelper::array_value( $atts, $id . '_opacity' );

			/**
			 * Add a substyle for 'svg' - we use _svg to get a unique id
			 */
			$svg_id = $id . '_svg';

			if( is_numeric( $height ) )
			{
				$this->callback_settings[ $svg_id ]['styles']['height'] = $height . 'px';
			}
			else
			{
				$this->callback_settings[ $svg_id ]['styles']['height'] = $height;
				if( is_numeric( $max_height ) )
				{
					$this->callback_settings[ $svg_id ]['styles']['max-height'] = $max_height . 'px';
				}
			}

			$this->callback_settings[ $svg_id ]['styles']['opacity'] = $opacity;

			if( AviaSvgShapes()->supports_width( $shape ) )
			{
				$this->callback_settings[ $svg_id ]['styles']['width'] = "calc($width% + 1.3px)";
			}

			/**
			 * Add an additional substyle for 'path' - we use _color to get a unique id
			 */
			$sub_id = $id . '_color';
			if( ! empty( $color ) )
			{
				$this->callback_settings[ $sub_id ]['styles']['fill'] = $color;
			}

			if( 'top' != $location )
			{
				$this->callback_settings[ $id ]['classes'][] = 'avia-divider-svg-bottom';
			}
			else
			{
				$this->callback_settings[ $id ]['classes'][] = 'avia-divider-svg-top';
			}

			if( ! empty( $flip ) && AviaSvgShapes()->can_flip( $shape ) )
			{
				$this->callback_settings[ $id ]['classes'][] = 'avia-flipped-svg';
			}

			if( ! empty( $front ) )
			{
				$this->callback_settings[ $id ]['classes'][] = 'avia-to-front';
			}

			if( ! empty( $invert ) && AviaSvgShapes()->can_invert( $shape ) )
			{
				$this->callback_settings[ $id ]['classes'][] = 'avia-svg-negative';
			}
			else
			{
				$this->callback_settings[ $id ]['classes'][] = 'avia-svg-original';
			}
		}

		/**
		 * Styles for "textblock_column_toggle"
		 *
		 *			- $id . '_align'
		 *			- $id
		 *			- $id . '_gap'
		 *			- $id . '_mobile'
		 *
		 * @since 4.8.4
		 * @param array $element
		 * @param array $atts
		 */
		protected function textblock_column_toggle( array $element, array $atts )
		{
			$callback = $element['styles_cb'];

			$id = \AviaHelper::array_value( $callback, 'id' );
			if( empty( $id ) )
			{
				return;
			}

			//	ensure to have an empty structure we can rely on later
			$this->callback_settings[ $id ]['styles'] = array();

			$align = \AviaHelper::array_value( $atts, $id . '_align' );
			if( ! empty( $align ) )
			{
				$this->callback_settings[ $id ]['styles']['text-align'] = $align;
			}

			$columns = \AviaHelper::array_value( $atts, $id );
			if( empty( $columns ) )
			{
				return;
			}

			$rules = $this->textblock_column_count_rules( $columns );
			$this->callback_settings[ $id ]['styles'] = array_merge( $this->callback_settings[ $id ]['styles'], $rules );

			$distance = \AviaHelper::array_value( $atts, $id . '_gap' );

			if( trim( $distance ) != '' )
			{
				if( is_numeric( $distance ) )
				{
					$distance .= '%';
				}

				$rules = $this->textblock_column_gap_rules( $distance );
				$this->callback_settings[ $id ]['styles'] = array_merge( $this->callback_settings[ $id ]['styles'], $rules );
			}

			$mobile = \AviaHelper::array_value( $atts, $id . '_mobile' );

			if( empty( $mobile ) )
			{
				return;
			}

			$this->callback_settings[ $id ]['media']['screen'][ '0;' . $mobile ]['column-count'] = 1;
			$this->callback_settings[ $id . '_first_p' ]['media']['screen'][ '0;' . $mobile ]['margin-top'] = '0.85em';
		}

		/**
		 * Styles for "position"
		 *
		 *			- $id
		 *
		 * Responsive :
		 *
		 *			- $id . '_location'
		 *			- $id . '_z_index'
		 *
		 * Responsive styles are added with $prefixes
		 *
		 * @since 5.0
		 * @param array $element
		 * @param array $atts
		 */
		protected function position( array $element, array $atts )
		{
			//	do not load these styling in preview
			if( is_admin() )
			{
				return;
			}

			$callback = $element['styles_cb'];

			$id = \AviaHelper::array_value( $callback, 'id' );
			if( empty( $id ) )
			{
				return;
			}

			$content = \AviaHelper::array_value( $callback, 'content', false );
			if( empty( $content ) || ! is_array( $content ) )
			{
				return;
			}

			$has_position = in_array( 'position', $content );
			$has_z_index = in_array( 'z_index', $content );

			$prefixes = $this->get_media_prefixes();

			foreach( $prefixes as $prefix )
			{
				//	ensure to have an empty structure we can rely on later
				$this->callback_settings[ $prefix . $id ]['styles'] = array();

				if( $has_position )
				{
					$position = \AviaHelper::array_value( $atts, $prefix . $id );

					if( in_array( $position, array( 'relative', 'absolute' ) ) )
					{
						$location = \AviaHelper::array_value( $atts, $prefix . $id . '_location' );
						if( ! \AviaHelper::empty_multi_input( $location ) )
						{
							$this->callback_settings[ $prefix . $id ]['styles']['position'] = $position;

							$location = \AviaHelper::multi_value_result_lockable( $location );
							$rules = $this->position_rules( $location['locked_opt_info'] );

							$this->callback_settings[ $prefix . $id ]['styles'] = array_merge( $this->callback_settings[ $prefix . $id ]['styles'], $rules );
						}
					}
				}

				if( $has_z_index )
				{
					$z_index = \AviaHelper::array_value( $atts, $prefix . $id . '_z_index' );
					if( is_numeric( $z_index ) )
					{
						$this->callback_settings[ $prefix . $id ]['styles']['z-index'] = $z_index;
					}
				}
			}
		}

		/**
		 * Styles for "parallax" animation
		 *
		 *			- $id
		 *
		 * Responsive :
		 *
		 *			- $id . '_parallax'
		 *			- $id . '_parallax_speed'
		 *
		 * Responsive styles are added with $prefixes
		 *
		 * @since 5.0
		 * @param array $element
		 * @param array $atts
		 */
		protected function parallax( array $element, array $atts )
		{
			//	do not load these styling in preview
			if( is_admin() )
			{
				return;
			}

			$callback = $element['styles_cb'];

			$id = \AviaHelper::array_value( $callback, 'id' );
			if( empty( $id ) )
			{
				return;
			}

			$prefixes = $this->get_media_prefixes();

			//	ensure to have an empty structure we can rely on later
			$this->callback_settings[ $id ]['data'] = array();

			$has_parallax = false;

			foreach( $prefixes as $prefix )
			{
				$parallax = \AviaHelper::array_value( $atts, $prefix . $id . '_parallax' );
				if( in_array( $parallax, array( 'bottom_top', 'left_right', 'right_left' ) ) )
				{
					$has_parallax = true;
					break;
				}
			}

			foreach( $prefixes as $prefix )
			{
				//	ensure to have an empty structure we can rely on later
				$this->callback_settings[ $prefix . $id ]['styles'] = array();

				//	avoids unnecessary HTML and js if element has no parallax
				if( $has_parallax )
				{
					$parallax = \AviaHelper::array_value( $atts, $prefix . $id . '_parallax' );
					$speed = \AviaHelper::array_value( $atts, $prefix . $id . '_parallax_speed' );

					if( ! in_array( $parallax, array( 'bottom_top', 'left_right', 'right_left', 'none' ) ) )
					{
						$parallax = '';
					}

					if( '' == $parallax )
					{
						if( '' == $prefix )
						{
							$val = 'no_parallax';
							$this->callback_settings[ $id ]['data'][ "{$prefix}parallax" ] = $val;
							$this->callback_settings[ $id ]['data'][ "{$prefix}parallax_speed" ] = $val;
						}
						else
						{
							$val = 'inherit';
							$this->callback_settings[ $id ]['data'][ "{$prefix}parallax" ] = $val;
							$this->callback_settings[ $id ]['data'][ "{$prefix}parallax_speed" ] = $val;
						}
					}
					else
					{
						if( 'none' == $parallax )
						{
							$val = 'no_parallax';
							$this->callback_settings[ $id ]['data'][ "{$prefix}parallax" ] = $val;
							$this->callback_settings[ $id ]['data'][ "{$prefix}parallax_speed" ] = $val;
						}
						else
						{
							$this->callback_settings[ $id ]['data'][ "{$prefix}parallax" ] = $parallax;
							$this->callback_settings[ $id ]['data'][ "{$prefix}parallax_speed" ] = $speed;
						}
					}
				}
			}
		}

		/**
		 * Styles for "transform"
		 *
		 *			- $id
		 *
		 * Responsive :
		 *
		 *			- $id . '_rotation'
		 *			- $id . '_scale'
		 *			- $id . '_skew'
		 *
		 * Responsive styles are added with $prefixes
		 *
		 * @since 5.0
		 * @param array $element
		 * @param array $atts
		 */
		protected function transform( array $element, array $atts )
		{
			$callback = $element['styles_cb'];

			$id = \AviaHelper::array_value( $callback, 'id' );
			if( empty( $id ) )
			{
				return;
			}

			$content = \AviaHelper::array_value( $callback, 'content', false );
			if( empty( $content ) || ! is_array( $content ) )
			{
				return;
			}

			$has_perspective = in_array( 'perspective', $content );
			$has_rotation = in_array( 'rotation', $content );
			$has_scale = in_array( 'scale', $content );
			$has_skew = in_array( 'skew', $content );
			$has_translate = in_array( 'translate', $content );

			$prefixes = $this->get_media_prefixes();

			//	ensure to have an empty structure we can rely on later
			$this->callback_settings[ $id ]['data'] = array();

			foreach( $prefixes as $prefix )
			{
				//	ensure to have an empty structure we can rely on later
				$this->callback_settings[ $prefix . $id ]['styles'] = array();

				$trans = array();

				if( $has_perspective )
				{
					$perspective = \AviaHelper::array_value( $atts, $prefix . $id . '_perspective' );

					if( trim( $perspective ) != '' )
					{
						if( is_numeric( $perspective ) )
						{
							$perspective .= 'px';
						}

						$trans[] = "perspective( {$perspective} )";
					}
				}

				if( $has_rotation )
				{
					$rotate = \AviaHelper::array_value( $atts, $prefix . $id . '_rotation' );

					if( ! \AviaHelper::empty_multi_input( $rotate ) )
					{
						$rotation = \AviaHelper::multi_value_result_lockable( $rotate );
						$opts = $rotation['locked_opt_info'];

						if( '' == $opts[0] )
						{
							$opts[0] = 0;
						}

						if( '' == $opts[1] )
						{
							$opts[1] = 0;
						}

						if( '' == $opts[2] )
						{
							$opts[2] = 1;
						}

						if( '' == $opts[3] )
						{
							$opts[3] = 0;
						}

						if( is_numeric( $opts[3] ) )
						{
							$opts[3] .= 'deg';
						}

						$trans[] = "rotate3d( {$opts[0]}, {$opts[1]}, {$opts[2]}, {$opts[3]} )";

					}
				}

				if( $has_scale )
				{
					$scale = \AviaHelper::array_value( $atts, $prefix . $id . '_scale' );

					if( ! \AviaHelper::empty_multi_input( $scale ) )
					{
						$scaled = \AviaHelper::multi_value_result_lockable( $scale );
						$opts = $scaled['locked_opt_info'];

						if( '' == $opts[0] )
						{
							$opts[0] = 1;
						}

						if( '' == $opts[1] )
						{
							$opts[1] = 1;
						}

						if( '' == $opts[2] )
						{
							$opts[2] = 1;
						}

						$trans[] = "scale3d( {$opts[0]}, {$opts[1]}, {$opts[2]} )";
					}
				}

				if( $has_skew )
				{
					$skew = \AviaHelper::array_value( $atts, $prefix . $id . '_skew' );

					if( ! \AviaHelper::empty_multi_input( $skew ) )
					{
						$skewed = \AviaHelper::multi_value_result_lockable( $skew );
						$opts = $skewed['locked_opt_info'];

						if( '' == $opts[0] )
						{
							$opts[0] = 0;
						}

						if( '' == $opts[1] )
						{
							$opts[1] = 0;
						}

						if( is_numeric( $opts[0] ) )
						{
							$opts[0] .= 'deg';
						}

						if( is_numeric( $opts[1] ) )
						{
							$opts[1] .= 'deg';
						}

						$trans[] = "skew( {$opts[0]}, {$opts[1]} )";
					}
				}

				if( $has_translate )
				{
					$translate = \AviaHelper::array_value( $atts, $prefix . $id . '_translate' );

					if( ! \AviaHelper::empty_multi_input( $translate ) )
					{
						$translated = \AviaHelper::multi_value_result_lockable( $translate );
						$opts = $translated['locked_opt_info'];

						if( '' == $opts[0] )
						{
							$opts[0] = 0;
						}

						if( '' == $opts[1] )
						{
							$opts[1] = 0;
						}

						if( '' == $opts[2] )
						{
							$opts[2] = 0;
						}

						$trans[] = "translate3d( {$opts[0]}, {$opts[1]}, {$opts[2]} )";
					}
				}

				if( ! empty( $trans ) )
				{
					$this->callback_settings[ $prefix . $id ]['styles']['transform'] = implode( ' ', $trans );
				}
			}
		}

		/**
		 * Styles for "element_animation"
		 *
		 *			- $id
		 *			- $id . '_duration'
		 *			- $id . '_custom_bg_color'
		 *
		 *
		 * @since 5.0
		 * @param array $element
		 * @param array $atts
		 */
		protected function animation( array $element, array $atts )
		{
			$callback = $element['styles_cb'];

			$id = \AviaHelper::array_value( $callback, 'id' );
			if( empty( $id ) )
			{
				return;
			}

			//	ensure to have an empty structure we can rely on later
			$this->callback_settings[ $id ]['styles'] = array();

			$animation = \AviaHelper::array_value( $atts, $id );
			$duration = \AviaHelper::array_value( $atts, $id . '_duration' );
			if( ! empty( $duration ) )
			{
				$rules = $this->animation_duration_rules( $duration );

				$this->callback_settings[ $id ]['styles'] = array_merge( $this->callback_settings[ $id ]['styles'], $rules );
			}

			if( false !== strpos( $animation, 'curtain-reveal' ) )
			{
				$color_array = $this->get_animation_color_multilist( $atts, $id );

				if( ! empty( $color_array ) )
				{
					// use first color as a fallback color
					$this->callback_settings[ $id ]['styles']['background'] = $color_array[0];

					if( count( $color_array ) > 1 )
					{
						foreach( $color_array as $key => $color )
						{
							$this->callback_settings[ "{$id}__{$key}" ]['styles']['background'] = $color;
						}
					}
				}

				$z_index = \AviaHelper::array_value( $atts, $id . '_z_index_curtain' );
				if( ! empty( $z_index ) && is_numeric( $z_index )  )
				{
					$this->callback_settings[ $id ]['styles']['z-index'] = (int) $z_index;
				}
			}
		}

		/**
		 * Styles for "margin_padding"
		 *
		 *			- $id => array(
		 *							'margin'	=> $id_margin,
		 *							'padding'	=> $id_padding
		 *						),
		 *
		 *
		 * Responsive styles are added with $prefixes
		 *
		 * @since 5.1
		 * @param array $element
		 * @param array $atts
		 */
		protected function margin_padding( array $element, array $atts )
		{
			$callback = $element['styles_cb'];

			$id = \AviaHelper::array_value( $callback, 'id' );
			if( empty( $id ) || ! is_array( $id ) )
			{
				return;
			}

			$content = \AviaHelper::array_value( $callback, 'content', false );
			if( empty( $content ) || ! is_array( $content ) )
			{
				return;
			}

			$multi = \AviaHelper::array_value( $callback, 'multi', array() );

			$id_margin = isset( $id['margin'] ) ? $id['margin'] : '';
			$id_padding = isset( $id['padding'] ) ? $id['padding'] : '';

			$has_margin = in_array( 'margin', $content );
			$has_padding = in_array( 'padding', $content );

			$prefixes = $this->get_media_prefixes();

			foreach( $prefixes as $prefix )
			{
				if( $has_margin && ! empty( $id_margin ) )
				{
					//	ensure to have an empty structure we can rely on later
					if( ! isset( $this->callback_settings[ $prefix . $id_margin ]['styles'] ) )
					{
						$this->callback_settings[ $prefix . $id_margin ]['styles'] = array();
					}

					$margin = \AviaHelper::array_value( $atts, $prefix . $id_margin );
					if( ! \AviaHelper::empty_multi_input( $margin ) )
					{
						if( isset( $multi['margin'] ) && is_array( $multi['margin'] ) )
						{
							$margin_css = AviaHelper::multi_value_result_lockable( $margin, 'margin', array_keys( $multi['margin'] ) );
							$this->callback_settings[ $prefix . $id_margin ]['styles'] = array_merge( $this->callback_settings[ $prefix . $id_margin ]['styles'], $margin_css['set_values_rules'] );
						}
						else
						{
							$margin_css = AviaHelper::multi_value_result_lockable( $margin );
							$this->callback_settings[ $prefix . $id_margin ]['styles']['margin'] = $margin_css['fill_with_0_style'];
						}
					}
				}

				if( $has_padding && ! empty( $id_padding ) )
				{
					if( ! isset( $this->callback_settings[ $prefix . $id_padding ]['styles'] ) )
					{
						$this->callback_settings[ $prefix . $id_padding ]['styles'] = array();
					}

					$padding = \AviaHelper::array_value( $atts, $prefix . $id_padding );
					if( ! \AviaHelper::empty_multi_input( $padding ) )
					{
						if( isset( $multi['padding'] ) && is_array( $multi['padding'] ) )
						{
							$padding_css = AviaHelper::multi_value_result_lockable( $padding, 'padding', array_keys( $multi['padding'] ) );
							$this->callback_settings[ $prefix . $id_padding ]['styles'] = array_merge( $this->callback_settings[ $prefix . $id_padding ]['styles'], $padding_css['set_values_rules'] );
						}
						else
						{
							$padding_css = AviaHelper::multi_value_result_lockable( $padding );
							$this->callback_settings[ $prefix . $id_padding ]['styles']['padding'] = $padding_css['fill_with_0_style'];
						}
					}
				}
			}
		}

		/**
		 * Styles for "responsive_visibility" - hide a container
		 *
		 *			- $id
		 *
		 * Responsive :
		 *
		 *			- 'av-desktop-hide-' . $id
		 *
		 * Responsive styles are added with $prefixes
		 *
		 * @since 5.1
		 * @param array $element
		 * @param array $atts
		 */
		protected function responsive_visibility( array $element, array $atts )
		{
			//	do not load these styling in preview
			if( is_admin() )
			{
				return;
			}

			$callback = $element['styles_cb'];

			$id = \AviaHelper::array_value( $callback, 'id' );
			if( empty( $id ) )
			{
				return;
			}

			$prefixes = $this->get_media_prefixes();

			foreach( $prefixes as $prefix )
			{
				//	ensure to have an empty structure we can rely on later
				$this->callback_settings[ $prefix . $id ]['styles'] = array();

				$hide = \AviaHelper::array_value( $atts, $prefix . $id );

				if( ! empty( $hide ) )
				{
					$this->callback_settings[ $prefix . $id ]['styles']['display'] = 'none';
				}
			}
		}

		/**
		 * Returns a list of colors. In case only '_custom_bg_color' exists this is returned.
		 *
		 * @since 5.0
		 * @param array $atts
		 * @param string $id
		 * @return array
		 */
		public function get_animation_color_multilist( array $atts, $id )
		{
			$color_list = array();

			$custom_bg_color = \AviaHelper::array_value( $atts, $id . '_custom_bg_color' );
			$color_multi_list = \AviaHelper::array_value( $atts, $id . '_custom_bg_color_multi_list' );

			if( empty( $color_multi_list ) && empty( $custom_bg_color ) )
			{
				return $color_list;
			}

			if( empty( $color_multi_list ) )
			{
				$color_list[] = $custom_bg_color;
				return $color_list;
			}

			$color_multi_array = preg_split('/<br[^>]*>|\||\n/i', $color_multi_list );
			$color_multi_array = array_values( array_filter( array_map( 'trim', $color_multi_array ) ) );

			return $color_multi_array;
		}

		/**
		 * Styles for "Filter: blur()"
		 *
		 * @since 5.1.2
		 * @param array $element
		 * @param array $atts
		 */
		protected function filter_blur( array $element, array $atts )
		{
			$callback = $element['styles_cb'];

			$id = \AviaHelper::array_value( $callback, 'id' );
			if( empty( $id ) )
			{
				return;
			}

			//	ensure to have an empty structure we can rely on later
			$this->callback_settings[ $id ]['styles'] = array();

			$blur = \AviaHelper::array_value( $atts, $id );

			if( ! empty( $blur ) )
			{
				$rules = $this->blur_rule( $blur );
				$this->callback_settings[ $id ]['styles'] = array_merge( $this->callback_settings[ $id ]['styles'], $rules );
			}

		}

		/**
		 * Styles for Slideshow Section Margin And Padding
		 *
		 * Responsive :
		 *
		 *		- Selectbox defaults are added hardcoded
		 *
		 * Responsive styles are added with $prefixes
		 *
		 * @since 5.0
		 * @param array $element
		 * @param array $atts
		 */
		protected function slideshow_section_margin_padding( array $element, array $atts )
		{
			$def_tab_padding =array(
									''		=> array( 10 ),
									'none'	=> array( 0 ),
									'small'	=> array( 0 ),
									'large'	=> array( 20 )
								);

			$def_padding = array(
									''				=> array( 50, 50 ),
									'no-padding'	=> array( 0, 0 ),
									'small'			=> array( 20, 20 ),
									'large'			=> array( 70, 70 ),
									'huge'			=> array( 130, 130 ),
								);

			$callback = $element['styles_cb'];

			$prefixes = $this->get_media_prefixes();

			foreach( $prefixes as $prefix )
			{
				//	ensure to have an empty structure we can rely on later
				$this->callback_settings[ $prefix . 'margin' ]['styles'] = array();
				$this->callback_settings[ $prefix . 'tab_padding' ]['styles'] = array();
				$this->callback_settings[ $prefix . 'padding' ]['styles'] = array();

				$margin = \AviaHelper::array_value( $atts, $prefix . 'margin' );

				if( ! \AviaHelper::empty_multi_input( $margin ) )
				{
					$margin_rules = \AviaHelper::multi_value_result_lockable( $margin, 'margin', array( 'top', 'bottom' ) );
					$this->callback_settings[ $prefix . 'margin' ]['styles'] = array_merge( $this->callback_settings[ $prefix . 'margin' ]['styles'], $margin_rules['set_values_rules'] );
				}

				$tab_padding = \AviaHelper::array_value( $atts, $prefix . 'tab_padding' );
				$tab_padding_custom = \AviaHelper::array_value( $atts, $prefix . 'tab_padding_custom' );

				//	in responsive we treat default values as custom values
				if( '' != $prefix )
				{
					if( 'custom' != $tab_padding )
					{
						$tab_padding_custom = "{$def_tab_padding[ $tab_padding ][0]}px";
						$tab_padding = 'custom';
					}
				}

				if( 'custom' == $tab_padding )
				{
					if( ! \AviaHelper::empty_multi_input( $tab_padding_custom ) )
					{
						$tb = \AviaHelper::multi_value_result_lockable( $tab_padding_custom, 'padding', array( 'top' ) );
						$this->callback_settings[ $prefix . 'tab_padding' ]['styles'] = array_merge( $this->callback_settings[ $prefix . 'tab_padding' ]['styles'], $tb['set_values_rules'] );
					}
				}

				$padding = \AviaHelper::array_value( $atts, $prefix . 'padding' );
				$padding_custom = \AviaHelper::array_value( $atts, $prefix . 'padding_custom' );

				//	in responsive we treat default values as custom values
				if( '' != $prefix )
				{
					if( 'custom' != $padding )
					{
						$padding_custom = "{$def_padding[ $padding ][0]}px,0,{$def_padding[ $padding ][1]}px,0";
						$padding = 'custom';
					}
				}

				if( 'custom' == $padding )
				{
					if( ! \AviaHelper::empty_multi_input( $padding_custom ) )
					{
						$tb = \AviaHelper::multi_value_result_lockable( $padding_custom, 'padding' );
						$this->callback_settings[ $prefix . 'padding' ]['styles'] = array_merge( $this->callback_settings[ $prefix . 'padding' ]['styles'], $tb['set_values_rules'] );
					}
				}

			}
		}

		/**
		 * Styles for "Filter: grayscale()"
		 *
		 * @since 5.1.2
		 * @param array $element
		 * @param array $atts
		 */
		protected function filter_grayscale( array $element, array $atts )
		{
			$callback = $element['styles_cb'];

			$id = \AviaHelper::array_value( $callback, 'id' );
			if( empty( $id ) )
			{
				return;
			}

			//	ensure to have an empty structure we can rely on later
			$this->callback_settings[ $id ]['styles'] = array();

			$grayscale = \AviaHelper::array_value( $atts, $id );

			if( ! empty( $grayscale ) )
			{
				$rules = $this->grayscale_rule( $grayscale );
				$this->callback_settings[ $id ]['styles'] = array_merge( $this->callback_settings[ $id ]['styles'], $rules );
			}

		}

		/**
		 * Styles for "mask_overlay" options
		 *
		 *			- $id
		 *			- $id . '_shape'
		 *			- $id . '_size'
		 *			- $id . '_scale'
		 *			- $id . '_position'
		 *			- $id . '_repeat'
		 *			- $id . '_rotate'
		 *			- $id . '_rad_shape'
		 *			- $id . '_rad_position'
		 *			- $id . '_opacity1'
		 *			- $id . '_opacity2'
		 *			- $id . '_opacity3'
		 *
		 *
		 * @since 5.1.2
		 * @param array $element
		 * @param array $atts
		 */
		protected function mask_overlay( array $element, array $atts )
		{
			$callback = $element['styles_cb'];

			$id = \AviaHelper::array_value( $callback, 'id' );
			if( empty( $id ) )
			{
				return;
			}

			//	ensure to have an empty structure we can rely on later
			$this->callback_settings[ $id ]['styles'] = array();

			$mask = \AviaHelper::array_value( $atts, $id );

			if( ! in_array( $mask, array( 'image', 'linear_gradient', 'radial_gradient' ) ) )
			{
				return;
			}

			$shape = \AviaHelper::array_value( $atts, $id . '_shape', 'blob', 'not_empty' );

			//	default shapes must be svg and have no file extension
			if( false === strpos( $shape, '.' ) )
			{
				$img_url = Avia_Builder()->paths['assetsURL'] . 'masks/' . $shape . '.svg';
			}
			else
			{
				$img_url = $shape;
			}

			$size = \AviaHelper::array_value( $atts, $id . '_size', 'contain', 'not_empty' );
			$scale = \AviaHelper::array_value( $atts, $id . '_scale' );
			$position = \AviaHelper::array_value( $atts, $id . '_position', 'center center', 'not_empty' );
			$repeat = \AviaHelper::array_value( $atts, $id . '_repeat', 'no-repeat', 'not_empty' );

			$rotate = \AviaHelper::array_value( $atts, $id . '_rotate' );
			$rad_shape = \AviaHelper::array_value( $atts, $id . '_rad_shape', 'circle', 'not_empty' );
			$rad_position = \AviaHelper::array_value( $atts, $id . '_rad_position', 'center center', 'not_empty' );

			$opacity1 = \AviaHelper::array_value( $atts, $id . '_opacity1' );
			$opacity2 = \AviaHelper::array_value( $atts, $id . '_opacity2' );
			$opacity3 = \AviaHelper::array_value( $atts, $id . '_opacity3' );

			$rules = array();

			if( 'image' == $mask )
			{
				$rules[ '-webkit-mask-image' ] = "url( $img_url )";

				if( 'custom' != $size )
				{
					$rules[ '-webkit-mask-size' ] = $size;
				}
				else
				{
					if( is_numeric( $scale ) )
					{
						$scale .= 'px';
					}

					$rules[ '-webkit-mask-size' ] = $scale;
				}

				$rules[ '-webkit-mask-position' ] = $position;
				$rules[ '-webkit-mask-repeat' ] = $repeat;
			}
			else if( 'linear_gradient' == $mask )
			{
				$linear = array(
							empty( $rotate ) ? '0deg' : "{$rotate}deg",
							"rgb(0, 0, 0, {$opacity1})",
							"rgb(0, 0, 0, {$opacity2})"
						);

				if( is_numeric( $opacity3 ) )
				{
					$linear[] = "rgb(0, 0, 0, {$opacity3})";
				}

				$rules[ '-webkit-mask-image' ] = 'linear-gradient(' . implode( ',', $linear ) . ')';
				$rules[ '-webkit-mask-size' ] = 'cover';
				$rules[ '-webkit-mask-position' ] = 'center center';

			}
			else
			{
				$radial = array(
							"{$rad_shape} at {$rad_position}",
							"rgb(0, 0, 0, {$opacity1})",
							"rgb(0, 0, 0, {$opacity2})"
						);

				if( is_numeric( $opacity3 ) )
				{
					$radial[] = "rgb(0, 0, 0, {$opacity3})";
				}

				$rules[ '-webkit-mask-image' ] = 'radial-gradient(' . implode( ',', $radial ) . ')';
				$rules[ '-webkit-mask-size' ] = 'cover';
				$rules[ '-webkit-mask-position' ] = 'center center';
			}

			/**
			 * https://caniuse.com/?search=mask
			 *
			 * @since 5.6.3:		we leave vendor prefix due to little support for unprefixed
			 */
			foreach( $rules as $rule => $value )
			{
				$new = str_replace( '-webkit-', '', $rule );
				$rules[ $new ] = $value;
			}

			$this->callback_settings[ $id ]['styles'] = array_merge( $this->callback_settings[ $id ]['styles'], $rules );
		}

	}
}
