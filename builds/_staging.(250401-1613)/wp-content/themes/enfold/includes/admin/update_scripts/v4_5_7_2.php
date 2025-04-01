<?php
/**
 * update for version 4.5.7.2
 *
 * Remove Google Plus
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

//set global options
global $avia;

$theme_options = $avia->options['avia'];

$social_icons = isset( $theme_options['social_icons'] ) ? $theme_options['social_icons'] : array();
$new_icons = array();

foreach( $social_icons as $key => $icon )
{
	if( isset( $icon['social_icon'] ) && ( 'gplus' == $icon['social_icon'] ) )
	{
		continue;
	}

	$new_icons[] = $icon;
}

$theme_options['social_icons'] = $new_icons;

if( empty( $theme_options['developer_options'] ) )
{
	$theme_options['developer_options'] = 'hide';
}

//replace existing options with the new options
$avia->options['avia'] = $theme_options;
update_option( $avia->option_prefix, $avia->options );

if( defined( 'WP_DEBUG' ) && WP_DEBUG )
{
	error_log( esc_html( __( 'Executed theme update script:', 'avia_framework' ) ) . ' ' . basename( __FILE__ ) );
}
