<?php
/**
 * update for version 4.3
 *
 * set caching and file merging to be deactivated for installations that get updated. new installations will have it enabled by default
 * will reduce errors with legacy installs
 *
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

//set global options
global $avia;

$theme_options = $avia->options['avia'];

if( ! empty( $theme_options ) )
{
	if(!isset($theme_options['merge_css']))
	{
		$theme_options['merge_css'] = "none";
	}

	if(!isset($theme_options['merge_js']))
	{
		$theme_options['merge_js'] = "none";
	}

	if(!isset($theme_options['disable_alb_elements']))
	{
		$theme_options['disable_alb_elements'] = "load_all";
	}

	//replace existing options with the new options
	$avia->options['avia'] = $theme_options;
	update_option($avia->option_prefix, $avia->options);

	\aviaFramework\avia_AdminNotices()->add_notice( 'performance_update', time() + \aviaFramework\avia_AdminNotices()->get_default_expire_time( basename( __FILE__ ) ) );
}

Avia_Builder()->element_manager()->handler_after_import_demo();

if( defined( 'WP_DEBUG' ) && WP_DEBUG )
{
	error_log( esc_html( __( 'Executed theme update script:', 'avia_framework' ) ) . ' ' . basename( __FILE__ ) );
}
