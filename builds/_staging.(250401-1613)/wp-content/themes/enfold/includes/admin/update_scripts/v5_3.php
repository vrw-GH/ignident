<?php
/**
 * update for version 5.3
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

global $avia_config;

$wp_upload_dir = wp_upload_dir();

$old_shape_folder = trailingslashit( $wp_upload_dir['basedir'] ) . 'avia_custom_shapes';
$new_shape_folder = $wp_upload_dir['basedir'] . trailingslashit( $avia_config['dynamic_files_upload_folder'] ) . 'avia_custom_shapes';

if( ! is_dir( $new_shape_folder ) )
{
	if( is_dir( $old_shape_folder ) )
	{
		rename( $old_shape_folder, $new_shape_folder );
	}
}

if( defined( 'WP_DEBUG' ) && WP_DEBUG )
{
	error_log( esc_html( __( 'Executed theme update script:', 'avia_framework' ) ) . ' ' . basename( __FILE__ ) );
}

