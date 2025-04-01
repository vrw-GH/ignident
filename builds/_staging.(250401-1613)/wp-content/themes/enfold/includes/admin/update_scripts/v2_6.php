<?php
/**
 * update for version 2.6:
 *
 * we need to map the single string that defines which header we are using to the multiple
 * new options and save them, so the user does not need to manually update the header
 *
 * also the post specific layout option that shows/hides the title bar is saved with a new name and value set
 * so it can easily overwrite the global option
 *
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

//set global options
global $avia;

$theme_options = $avia->options['avia'];


/**
 * if one of those settings is not available the user has never saved the theme options. No need to change anything
 *
 * @since 6.0  Seems that 'header_setting' does not exist any longer - when testing this with later versions this option does not exist ???
 */
if( empty( $theme_options ) || ! isset( $theme_options['header_setting'] ) )
{
	if( defined( 'WP_DEBUG' ) && WP_DEBUG )
	{
		error_log( esc_html( __( 'Executed theme update script:', 'avia_framework' ) ) . ' ' . basename( __FILE__ ) );
	}

	return;
}


//set defaults
$theme_options['header_layout'] 		= "logo_left menu_right";
$theme_options['header_size'] 			= "slim";
$theme_options['header_sticky'] 		= "header_sticky";
$theme_options['header_shrinking'] 		= "header_shrinking";
$theme_options['header_social'] 		= "";
$theme_options['header_secondary_menu'] = "";
$theme_options['header_phone_active']	= "";

if( ! empty( $theme_options['phone'] ) )
{
	$theme_options['header_phone_active'] = "phone_active_right extra_header_active";
}

//overwrite defaults based on the selection
switch( $theme_options['header_setting'] )
{
	case 'nonfixed_header':

		$theme_options['header_sticky'] 	= "";
		$theme_options['header_shrinking'] 	= "";
		$theme_options['header_social'] 	= "";
		break;
	case 'fixed_header social_header':

		$theme_options['header_size'] 			= "large";
		$theme_options['header_social'] 		= "icon_active_left extra_header_active";
		$theme_options['header_secondary_menu'] = "secondary_right extra_header_active";
		break;
	case 'nonfixed_header social_header':

		$theme_options['header_size'] 			= "large";
		$theme_options['header_sticky'] 		= "";
		$theme_options['header_shrinking'] 		= "";
		$theme_options['header_social'] 		= "icon_active_left extra_header_active";
		$theme_options['header_secondary_menu'] = "secondary_right extra_header_active";
		break;
	case 'nonfixed_header social_header bottom_nav_header':

		$theme_options['header_layout'] 		= "logo_left bottom_nav_header";
		$theme_options['header_sticky'] 		= "";
		$theme_options['header_shrinking'] 		= "";
		$theme_options['header_social'] 		= "icon_active_main";
		$theme_options['header_secondary_menu'] = "secondary_right extra_header_active";
		break;
}

//replace existing options with the new options
$avia->options['avia'] = $theme_options;
update_option($avia->option_prefix, $avia->options);


//update post specific options
$getPosts = new WP_Query(
	array(
		'post_type'     => array( 'post', 'page', 'portfolio', 'product' ),
		'post_status'   => 'publish',
		'posts_per_page'=>-1,
		'meta_query'	=> array(
					array(
						'key' => 'header'
					)
				)
	));

if( ! empty( $getPosts->posts ) )
{
	foreach( $getPosts->posts as $post )
	{
		$header_setting = get_post_meta( $post->ID, 'header', true );
		switch( $header_setting )
		{
			case "yes":
				update_post_meta( $post->ID, 'header_title_bar', '' );
				break;
			case "no":
				update_post_meta( $post->ID, 'header_title_bar', 'hidden_title_bar' );
				break;
		}
	}
}

if( defined( 'WP_DEBUG' ) && WP_DEBUG )
{
	error_log( esc_html( __( 'Executed theme update script:', 'avia_framework' ) ) . ' ' . basename( __FILE__ ) );
}
