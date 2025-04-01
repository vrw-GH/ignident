<?php

if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly



\aviaFramework\avia_AdminNotices()->add_notice( 'enfold_60_welcome', time() + \aviaFramework\avia_AdminNotices()->get_default_expire_time( basename( __FILE__ ) ) );
\aviaFramework\updates\aviaThemeDataUpdater()->show_default_notice( false );


//set global options
global $avia;

$theme_options = $avia->options['avia'];

//	set default values - otherwise we get troubles when checking with avia_get_option() returning default value on ''
$avia->options['avia']['alb_dynamic_content'] = 'alb_dynamic_content alb_custom_layout';


update_option( $avia->option_prefix, $avia->options );


if( defined( 'WP_DEBUG' ) && WP_DEBUG )
{
	error_log( esc_html( __( 'Executed theme update script:', 'avia_framework' ) ) . ' ' . basename( __FILE__ ) );
}
