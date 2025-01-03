<?php
/**
 * Holds html templates to be reused in ALB frontend shortcodes
 *
 * @added_by guenter
 * @since 4.8.3
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'aviaFrontTemplates', false ) )
{
	class aviaFrontTemplates
	{
		/**
		 * Returns HTML for arrows for e.g. slideshows
		 *
		 * @since 4.8.3
		 * @param array $args			depends on context
		 * @return type
		 */
		static public function slide_navigation_arrows( array $args = array() )
		{
			$class_main = isset( $args['class_main'] ) ? $args['class_main'] : 'avia-slideshow-arrows avia-slideshow-controls';
			$container_styles = isset( $args['container_styles'] ) ? $args['container_styles'] : '';

			$icon_prev = isset( $args['icon_prev'] ) ? av_icon_string( $args['icon_prev'] ) : av_icon_string( 'prev_big' );
			$icon_next = isset( $args['icon_next'] ) ? av_icon_string( $args['icon_next'] ) : av_icon_string( 'next_big' );
			$class_prev = isset( $args['class_prev'] ) ? $args['class_prev'] : '';
			$class_next = isset( $args['class_next'] ) ? $args['class_next'] : '';
			$text_prev = isset( $args['text_prev'] ) ? $args['text_prev'] : __( 'Previous', 'avia_framework' );
			$text_next = isset( $args['text_next'] ) ? $args['text_next'] : __( 'Next', 'avia_framework' );

			$aria_prev = false === strpos( $icon_prev, 'aria-hidden=' ) ? 'aria-hidden="true"' : '';
			$aria_next = false === strpos( $icon_next, 'aria-hidden=' ) ? 'aria-hidden="true"' : '';

			$html  = '';

			$html .= "<div class='{$class_main}' {$container_styles}>";
			$html .= 	"<a href='#prev' class='prev-slide {$class_prev}' {$icon_prev} {$aria_prev} tabindex='-1'>{$text_prev}</a>";
			$html .= 	"<a href='#next' class='next-slide {$class_next}' {$icon_next} {$aria_next} tabindex='-1'>{$text_next}</a>";
			$html .= '</div>';

			/**
			 * Customize slide navigation arrows
			 *
			 * @since 4.8.3
			 * @param string $html
			 * @param array $args
			 * @return string
			 */
			return apply_filters( 'avf_slide_navigation_arrows_html', $html, $args );
		}

		/**
		 * Returns HTML for navigation dots for e.g. slideshows
		 *
		 * @since 4.8.3
		 * @param array $args			depends on context
		 * @return string
		 */
		static public function slide_navigation_dots( array $args = array() )
		{
			$class_main = isset( $args['class_main'] ) ? $args['class_main'] : 'avia-slideshow-dots avia-slideshow-controls';
			$total_entries = isset( $args['total_entries'] ) ? $args['total_entries'] : 0;
			$container_entries = isset( $args['container_entries'] ) ? $args['container_entries'] : 1;

			$containers = $total_entries / (int) $container_entries;
			$final_cont = $total_entries % (int) $container_entries ? ( (int) $containers + 1 )  : (int) $containers;

			$active = 'active';

			$html  = '';
			$html .= "<div class='{$class_main}'>";

			for( $i = 1; $i <= $final_cont; $i++ )
			{
				$html .= "<a href='#{$i}' class='goto-slide {$active}' >{$i}</a>";
				$active = '';
			}

			$html .= '</div>';

			/**
			 * Customize slide navigation dots
			 *
			 * @since 4.8.3
			 * @param string $html
			 * @param array $args
			 * @return string
			 */
			return apply_filters( 'avf_slide_navigation_dots_html', $html, $args );
		}

		/**
		 * Returns HTML for fold/unfold button
		 *
		 * @since 5.6
		 * @param array $args			depends on context
		 * @return string
		 */
		static public function fold_unfold_button( array $args = [] )
		{
			$atts = is_array( $args['atts'] ) ? $args['atts'] : [];
			$wrapper_class = ! empty( $args[ 'wrapper_class' ] ) ? $args[ 'wrapper_class' ] : '';

			$fold_more = ! empty( $atts['fold_more'] ) ? $atts['fold_more'] : __( 'Read More', 'avia_framework' );
			$fold_top_offset = ! empty( $atts['fold_top_offset'] ) ? $atts['fold_top_offset'] : 50;

			$btn_class = '';

			if( $atts['fold_text_style'] != '' )
			{
				$wrapper_class .= ' avia-button-wrap';
				$btn_class .= " avia-button {$atts['fold_text_style']} avia-color-{$atts['fold_btn_color']}";
			}

			$html  = '';

			$html .= "<div class='av-fold-button-wrapper {$wrapper_class}'>";
			$html .=	'<a href="#" class="av-fold-button-container ' . $btn_class . '" data-ignore_hash="1" data-no_scroll_in_viewport="1" data-scroll_top_offset="' . $fold_top_offset . '">';
			$html .=			$fold_more;
			$html .=	'</a>';
			$html .= '</div>';

			/**
			 * Customize fold button
			 *
			 * @since 5.6
			 * @param string $html
			 * @param array $args
			 * @return string
			 */
			return apply_filters( 'avf_fold_unfold_button_html', $html, $args );
		}

	}

}
