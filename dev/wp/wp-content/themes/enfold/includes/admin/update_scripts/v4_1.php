<?php
/**
 * update for version 4.1
 *
 * update the main menu icon and move the scale and color from advanced editor to a normal option
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

//fetch advanced data
global $avia;

$theme_options = $avia->options['avia'];
$advanced = avia_get_option('advanced_styling');

if(isset($theme_options['menu_display']) && $theme_options['menu_display'] == 'burger_menu')
{
	$theme_options['overlay_style'] = 'av-overlay-full';
	$theme_options['submenu_clone'] = 'av-submenu-noclone';
	$theme_options['submenu_visibility'] = 'av-submenu-hidden av-submenu-display-hover';
}
else
{
	$theme_options['overlay_style'] = 'av-overlay-side av-overlay-side-classic';

	if(isset($theme_options['header_mobile_behavior']) && $theme_options['header_mobile_behavior'] != "")
	{
		$theme_options['submenu_visibility'] = 'av-submenu-hidden av-submenu-display-click';
	}
	else
	{
		$theme_options['submenu_visibility'] = '';
	}

	$theme_options['submenu_clone'] = 'av-submenu-noclone';
}

if(!empty($advanced))
{
	foreach($advanced as $rule)
	{
		if( isset($rule) && $rule['id'] == 'main_menu_icon_style' )
		{
			if( ! empty( $rule['color'] ) )
			{
				$theme_options['burger_color'] = $rule['color'];
			}

			if( ! empty( $rule['size'] ) )
			{
				$theme_options['burger_size'] = 'av-small-burger-icon';
			}

			break;
		}
	}
}


//replace existing options with the new options
$avia->options['avia'] = $theme_options;
update_option($avia->option_prefix, $avia->options);

if( defined( 'WP_DEBUG' ) && WP_DEBUG )
{
	error_log( esc_html( __( 'Executed theme update script:', 'avia_framework' ) ) . ' ' . basename( __FILE__ ) );
}
