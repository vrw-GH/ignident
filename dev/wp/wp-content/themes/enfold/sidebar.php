<?php
if ( ! defined( 'ABSPATH' ) )	{ die(); }

##############################################################################
# Display the sidebar
##############################################################################

global $avia_config;

$default_sidebar = true;
$sidebar_pos = avia_layout_class( 'main', false );

$sidebar_smartphone = avia_get_option( 'smartphones_sidebar' ) == 'smartphones_sidebar' ? 'smartphones_sidebar_active' : '';

$sidebar = '';
if( strpos( $sidebar_pos, 'sidebar_left' )  !== false )
{
	$sidebar = 'left';
}
if( strpos( $sidebar_pos, 'sidebar_right' ) !== false )
{
	$sidebar = 'right';
}

/**
 * filter the sidebar position (eg woocommerce single product pages always want the same sidebar pos)
 *
 * @param string $sidebar
 * @return string
 */
$sidebar = apply_filters( 'avf_sidebar_position', $sidebar );

//if the layout hasn't the sidebar keyword defined we dont need to display one
if( empty( $sidebar ) )
{
	return;
}

if( ! empty( $avia_config['overload_sidebar' ] ) )
{
	$avia_config['currently_viewing'] = $avia_config['overload_sidebar'];
}

//get text alignment for left sidebar
$sidebar_text_alignment = '';

if( $sidebar == 'left' )
{
	$sidebar_left_textalign = avia_get_option( 'sidebar_left_textalign' );
	$sidebar_text_alignment = $sidebar_left_textalign !== '' ? 'sidebar_' . $sidebar_left_textalign : '';
}

$aria_label = 'aria-label="' . __( 'Sidebar', 'avia_framework' ) . '"';

/**
 * @since 6.0.3
 * @param string $aria_label
 * @param string $context
 * @param mixed $additional_args
 * @return string
 */
$aria_label = apply_filters( 'avf_aria_label_for_sidebar', $aria_label, __FILE__, null );


echo "<aside class='sidebar sidebar_{$sidebar} {$sidebar_text_alignment} {$sidebar_smartphone} " . avia_layout_class( 'sidebar', false ) . " units' {$aria_label} " . avia_markup_helper( array( 'context' => 'sidebar', 'echo' => false ) ) . '>';
	echo '<div class="inner_sidebar extralight-border">';

		//Display a subnavigation for pages that is automatically generated, so the users do not need to work with widgets
		$av_sidebar_menu = avia_sidebar_menu( false );
		if( $av_sidebar_menu )
		{
			echo $av_sidebar_menu;
			$default_sidebar = false;
		}

		$the_id = @get_the_ID();

		$custom_sidebar = '';
		if( ! empty( $the_id ) && is_singular() )
		{
			$custom_sidebar = get_post_meta( $the_id, 'sidebar', true );
		}

		/**
		 * @param string $custom_sidebar
		 * @return string
		 */
		$custom_sidebar = apply_filters( 'avf_custom_sidebar', $custom_sidebar );

		if( $custom_sidebar )
		{
			dynamic_sidebar( $custom_sidebar );
			$default_sidebar = false;
		}
		else
		{
			if( empty( $avia_config['currently_viewing'] ) )
			{
				$avia_config['currently_viewing'] = 'page';
			}

			// general shop sidebars
			if( $avia_config['currently_viewing'] == 'shop' && dynamic_sidebar( 'Shop Overview Page' ) )
			{
				$default_sidebar = false;
			}

			// single shop sidebars
			if( $avia_config['currently_viewing'] == 'shop_single' )
			{
				$default_sidebar = false;
			}

			if( $avia_config['currently_viewing'] == 'shop_single' && dynamic_sidebar( 'Single Product Pages' ) )
			{
				$default_sidebar = false;
			}

			// general blog sidebars
			if( $avia_config['currently_viewing'] == 'blog' && dynamic_sidebar('Sidebar Blog') )
			{
				$default_sidebar = false;
			}

			// general archive sidebars
			if( avia_get_option( 'archive_sidebar' ) == 'archive_sidebar_separate' )
			{
				if( $avia_config['currently_viewing'] == 'archive' && dynamic_sidebar( 'Sidebar Archives' ) )
				{
					$default_sidebar = false;
				}
			}

			// general pages sidebars
			if( $avia_config['currently_viewing'] == 'page' && dynamic_sidebar( 'Sidebar Pages' ) )
			{
				$default_sidebar = false;
			}

			// forum pages sidebars
			if( $avia_config['currently_viewing'] == 'forum' && dynamic_sidebar( 'Forum' ) )
			{
				$default_sidebar = false;
			}
		}

		//global sidebar
		if( dynamic_sidebar( 'Displayed Everywhere' ) )
		{
			$default_sidebar = false;
		}

		/**
		 * Filter to show default dummy sidebar
		 *
		 * @param false|string $default_sidebar
		 * @return false|string
		 */
		if( apply_filters( 'avf_show_default_sidebars', $default_sidebar ) )
		{
			 if( apply_filters( 'avf_show_default_sidebar_pages', true ) )
			 {
				 avia_dummy_widget(2);
			 }

			 if( apply_filters( 'avf_show_default_sidebar_categories', true ) )
			 {
				 avia_dummy_widget(3);
			 }

			 if( apply_filters( 'avf_show_default_sidebar_archiv', true ) )
			 {
				 avia_dummy_widget(4);
			 }

			//	customize default sidebar and add your sidebars
			do_action ( 'ava_add_custom_default_sidebars' );
		}

	echo '</div>';
echo '</aside>';
