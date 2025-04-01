<?php
/**
 * update for version 4.8.8
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


//set global options
global $avia;

$theme_options = $avia->options['avia'];

if( isset( $theme_options['color-default_font_size'] ) )
{
	$theme_options['typo-default_font_size'] = $theme_options['color-default_font_size'];
	unset( $theme_options['color-default_font_size'] );
}

//replace existing options with the new options
$avia->options['avia'] = $theme_options;
update_option( $avia->option_prefix, $avia->options );

if( defined( 'WP_DEBUG' ) && WP_DEBUG )
{
	error_log( esc_html( __( 'Executed theme update script:', 'avia_framework' ) ) . ' ' . basename( __FILE__ ) );
}
