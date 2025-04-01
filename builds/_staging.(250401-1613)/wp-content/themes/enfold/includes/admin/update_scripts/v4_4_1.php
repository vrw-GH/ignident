<?php
/**
 * update for version 4.4.1
 *
 * remove the mirzepapa username and api key that somehow ended up in a demo import file from all installations
 *
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( avia_get_option( 'updates_username' ) == 'mirzepapa' )
{
	avia_delete_option( 'updates_api_key' );
	avia_delete_option( 'updates_username' );
}

if( defined( 'WP_DEBUG' ) && WP_DEBUG )
{
	error_log( esc_html( __( 'Executed theme update script:', 'avia_framework' ) ) . ' ' . basename( __FILE__ ) );
}
