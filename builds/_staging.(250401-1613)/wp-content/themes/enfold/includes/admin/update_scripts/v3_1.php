<?php
/**
 * update for version 3.1
 *
 * updates the main menu separator setting in case a user had a bottom nav main menu
 * also adds the values for meta and heading to the theme options array so they can be set manually
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

//set global options
global $avia, $avia_config;

$theme_options = $avia->options['avia'];

//if one of those settings is not available the user has never saved the theme options. No need to change anything
if(empty( $theme_options ))
{
	if( defined( 'WP_DEBUG' ) && WP_DEBUG )
	{
		error_log( esc_html( __( 'Executed theme update script:', 'avia_framework' ) ) . ' ' . basename( __FILE__ ) );
	}

	return;
}

if( strpos($theme_options['header_layout'],'bottom_nav_header') !== false )
{
	$theme_options['header_menu_border'] = "seperator_big_border";
}

//removes the old calculated meta and heading colors and changes it to a custom color that can be set by the user
$colorsets = $avia_config['color_sets'];

if(!empty($colorsets))
{
	foreach($colorsets as $set_key => $set_value)
	{
		if(isset($avia_config['backend_colors']['color_set'][$set_key]))
		{
			if(isset($avia_config['backend_colors']['color_set'][$set_key]['meta']))
			{
				$theme_options["colorset-$set_key-meta"] = $avia_config['backend_colors']['color_set'][$set_key]['meta'];
			}

			if(isset($avia_config['backend_colors']['color_set'][$set_key]['heading']))
			{
				$new_heading = $avia_config['backend_colors']['color_set'][$set_key]['heading'];

				if('footer_color' == $set_key)
				{
					$new_heading = $avia_config['backend_colors']['color_set'][$set_key]['meta'];
				}

				$theme_options["colorset-$set_key-heading"] = $new_heading;
			}

		}
	}
}

//replace existing options with the new options
$avia->options['avia'] = $theme_options;
update_option( $avia->option_prefix, $avia->options );

if( defined( 'WP_DEBUG' ) && WP_DEBUG )
{
	error_log( esc_html( __( 'Executed theme update script:', 'avia_framework' ) ) . ' ' . basename( __FILE__ ) );
}
