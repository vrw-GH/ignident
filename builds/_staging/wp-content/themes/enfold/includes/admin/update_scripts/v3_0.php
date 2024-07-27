<?php
/**
 * update for version 3.0
 *
 * updates responsive option and splits it into multiple fields for more flexibility
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

//set global options
global $avia;

$theme_options = $avia->options['avia'];

//if one of those settings is not available the user has never saved the theme options. No need to change anything
if( empty( $theme_options ) )
{
	if( defined( 'WP_DEBUG' ) && WP_DEBUG )
	{
		error_log( esc_html( __( 'Executed theme update script:', 'avia_framework' ) ) . ' ' . basename( __FILE__ ) );
	}

	return;
}

if( empty( $theme_options['responsive_layout'] ) )
{
	$theme_options['responsive_layout'] = "responsive responsive_large";
}

$responsive = "enabled";
$size		= "1130px";

switch( $theme_options['responsive_layout'] )
{
	case "responsive" :
		$responsive = "enabled";
		break;
	case "responsive responsive_large" :
		$responsive = "enabled";
		$size = "1310px";
		break;
	case "static_layout" :
		$responsive = "disabled";
		break;
}

$theme_options['responsive_active'] = $responsive;
$theme_options['responsive_size']   = $size;

//replace existing options with the new options
$avia->options['avia'] = $theme_options;
update_option($avia->option_prefix, $avia->options);

if( defined( 'WP_DEBUG' ) && WP_DEBUG )
{
	error_log( esc_html( __( 'Executed theme update script:', 'avia_framework' ) ) . ' ' . basename( __FILE__ ) );
}
