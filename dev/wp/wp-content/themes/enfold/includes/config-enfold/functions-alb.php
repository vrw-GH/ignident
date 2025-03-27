<?php
/*
 * This helper file holds functions to integrate ALB in theme
 * Moved from functions.php and functions-enfold.php
 *
 * @since 7.0
 * @added_by Günter
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! function_exists( 'av_builder_meta_box_elements_content' ) )
{
	/**
	 * Adjust element content to reflect main option settings
	 * e.g. with sdding page as footer feature we need to adjust select box content of footer settings
	 *
	 * @since 4.2.7
	 * @added_by Günter
	 * @param array $elements
	 * @return array
	 */
	function av_builder_meta_box_elements_content( array $elements )
	{
		$footer_options	= avia_get_option( 'display_widgets_socket', 'all' );

		if( false !== strpos( $footer_options, 'page' ) )
		{
			$desc = __( 'Display the footer page?', 'avia_framework' );
			$subtype = array(
							__( 'Default Layout - set in', 'avia_framework' ) . ' ' . THEMENAME. ' > ' . __( 'Footer', 'avia_framework' )	=> '',
							__( 'Use selected page to display as footer and socket', 'avia_framework' )		=> 'page_in_footer_socket',
							__( 'Use selected page to display as footer (no socket)', 'avia_framework' )	=> 'page_in_footer',
							__( 'Don\'t display the socket & page', 'avia_framework' )						=> 'nofooterarea'
						);
		}
		else
		{
			$desc = __( 'Display the footer widgets?', 'avia_framework' );
			$subtype = array(
							__( 'Default Layout - set in', 'avia_framework' ) . ' ' . THEMENAME . ' > ' . __( 'Footer', 'avia_framework' ) => '',
							__( 'Display the footer widgets & socket', 'avia_framework' )					=> 'all',
							__( 'Display only the footer widgets (no socket)', 'avia_framework' )			=> 'nosocket',
							__( 'Display only the socket (no footer widgets)', 'avia_framework' )			=> 'nofooterwidgets',
							__( 'Don\'t display the socket & footer widgets', 'avia_framework' )			=> 'nofooterarea'
						);
		}

		foreach( $elements as &$element )
		{
			if( 'footer' == $element['id'] )
			{
				$element['desc'] = $desc;
				$element['subtype'] = $subtype;
			}
		}

		return $elements;
	}

	add_filter( 'avf_builder_elements', 'av_builder_meta_box_elements_content', 10000, 1 );
}

if( ! function_exists( 'av_disable_live_preview' ) )
{
	/**
	 * Disable element live preview
	 *
	 * @param array $data
	 * @return array
	 */
	function av_disable_live_preview( $data )
	{
		if( avia_get_option( 'preview_disable' ) == 'preview_disable' )
		{
			$data['preview'] = 0;
		}

		return $data;
	}

	add_filter( 'avf_backend_editor_element_data_filter', 'av_disable_live_preview', 10, 1 );
}


if( ! function_exists( 'av_print_custom_font_size' ) )
{
	/**
	 * mobile sizes that overwrite elements default sizes
	 *
	 * @param object $request
	 */
	function av_print_custom_font_size( $request )
	{
		if( class_exists( 'AviaHelper', false ) )
		{
			echo AviaHelper::av_print_mobile_sizes();
		}
	}

	add_action( 'wp_footer', 'av_print_custom_font_size' );
}


if( ! function_exists( 'avia_disable_alb_drag_drop' ) )
{
	/**
	 * Disables the alb drag and drop for non admins
	 *
	 * @param boolean $disable
	 * @return boolean
	 */
	function avia_disable_alb_drag_drop( $disable )
	{
		if( ! current_user_can( 'switch_themes' ) || avia_get_option( 'lock_alb_for_admins', 'disabled' ) != 'disabled' )
		{
			$disable = avia_get_option( 'lock_alb', 'disabled' ) != 'disabled' ? true : false;
		}

		return $disable;
	}

	add_filter( 'avf_allow_drag_drop', 'avia_disable_alb_drag_drop', 30, 1 );
}

if( ! function_exists( 'avia_add_hide_featured_image_select' ) )
{
	/**
	 * Add a select box to hide featured image on single post
	 *
	 * @param array $elements
	 * @return array
	 */
	function avia_add_hide_featured_image_select( array $elements )
	{
		if( ! is_admin() || ! function_exists( 'get_current_screen' ) )
		{
			return $elements;
		}

		$screen = get_current_screen();
		if( ! $screen instanceof WP_Screen )
		{
			return $elements;
		}

		$hide_pt = apply_filters( 'avf_display_featured_image_posttypes', array( 'post', 'portfolio' ) );

		if( ! in_array( $screen->post_type, $hide_pt ) )
		{
			return $elements;
		}

		switch( $screen->post_type )
		{
			case 'post':
				$desc = __( 'Select to display featured image for a single post entry.', 'avia_framework' );
				break;
			case 'portfolio':
				$desc = __( 'Select to display featured image for a single portfolio entry.', 'avia_framework' );
				break;
			default:
				$desc = apply_filters( 'avf_display_featured_image_desc', __( 'Select to display featured image for a single entry.', 'avia_framework' ) );
				break;
		}

		$elements[] = array(
						'slug'		=> 'layout',
						'name'		=> __( 'Featured Image', 'avia_framework' ),
						'desc'		=> $desc,
						'id'		=> '_avia_hide_featured_image',
						'type'		=> 'select',
						'std'		=> '',
						'class'		=> 'avia-style',
						'subtype'	=> array(
											__( 'Show on single entry', 'avia_framework' ) => '',
											__( 'Hide on single entry', 'avia_framework' ) => '1'
										)
					);

		return $elements;
	}

	add_filter( 'avf_builder_elements', 'avia_add_hide_featured_image_select', 10, 1 );
}

