<?php
/**
 * update for version 4.4
 *
 * we change the cookie consent buttons to a group element and users can add unlimited buttons. need to port data structure of old buttons
 *
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

//set global options
global $avia;

$theme_options = $avia->options['avia'];

if( ! empty( $theme_options ) )
{
	$theme_options['msg_bar_buttons'] = array();

	//cookie linktext
	if( isset( $theme_options['cookie_infolink'] ) && $theme_options['cookie_infolink'] == 'cookie_infolink' )
	{
		$theme_options['msg_bar_buttons'][] = array(
							'msg_bar_button_label'	=> $theme_options['cookie_linktext'],
							'msg_bar_button_action'	=> 'link',
							'msg_bar_button_link'	=> $theme_options['cookie_linksource']
						);
	}

	if( isset( $theme_options['cookie_buttontext'] ) )
	{
	//dismiss button
		$theme_options['msg_bar_buttons'][] = array(
							'msg_bar_button_label' => $theme_options['cookie_buttontext'],
							'msg_bar_button_action' => '',
							'msg_bar_button_link' => ''
						);
	}

	//replace existing options with the new options
	$avia->options['avia'] = $theme_options;
	update_option( $avia->option_prefix, $avia->options );

	\aviaFramework\avia_AdminNotices()->add_notice( 'gdpr_update', time() + \aviaFramework\avia_AdminNotices()->get_default_expire_time( basename( __FILE__ ) ) );
}

if( defined( 'WP_DEBUG' ) && WP_DEBUG )
{
	error_log( esc_html( __( 'Executed theme update script:', 'avia_framework' ) ) . ' ' . basename( __FILE__ ) );
}
