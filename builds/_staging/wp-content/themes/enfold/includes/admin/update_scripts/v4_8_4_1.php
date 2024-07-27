<?php
/**
 * update for version 4.8.4.1
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


//set global options
global $avia;

$theme_options = $avia->options['avia'];

//	set social profile links to disabled - hardcoded for theme defined only
$social_profile_array = array(
					'five_100_px',
					'behance',
					'dribbble',
					'flickr',
					'instagram',
					'skype',
					'soundcloud',
					'vimeo',
					'xing',
					'youtube',
					'rss'
				);

foreach( $social_profile_array as $profile )
{
	$theme_options[ 'share_' . $profile ] = 'disabled';
}

//replace existing options with the new options
$avia->options['avia'] = $theme_options;
update_option( $avia->option_prefix, $avia->options );

if( defined( 'WP_DEBUG' ) && WP_DEBUG )
{
	error_log( esc_html( __( 'Executed theme update script:', 'avia_framework' ) ) . ' ' . basename( __FILE__ ) );
}
