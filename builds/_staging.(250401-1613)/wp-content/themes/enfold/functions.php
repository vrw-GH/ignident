<?php
/**
 * Main theme entry file.
 *
 * ATTENTION: Please do not make any changes to this file. Use child theme or plugins to add customizations
 * ========================================================================================================
 *
 * @since 1.0
 * @since 7.0			split into several files in /includes/classes, /includes/config-enfold, /includes/helpers
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


global $avia_config;

if( ! is_array( $avia_config ) )
{
	$avia_config = [];
}

/*
 * if you run a child theme and don't want to load the default functions.php file
 * set the global var below in you childthemes function.php to true:
 *
 * example:
 *
 * global $avia_config;
 *
 * $avia_config = [];
 * $avia_config['use_child_theme_functions_only'] = true;
 *
 *
 * The default functions.php file will then no longer be loaded. You need to make sure then
 * to include framework and functions that you want to use by yourself.
 *
 * This is only recommended for advanced users
 */
if( isset( $avia_config['use_child_theme_functions_only'] ) )
{
	return;
}


/**
 * With WP 5.8 block editor was introduced to widget page. This is not supported by Enfold.
 * Based on https://wordpress.org/plugins/classic-widgets/ we disable this feature.
 *
 * For users who need to use it we updated our widgets but preview is not supported properly.
 *
 * ACTIVATING THIS FEATURE IS NOT SUPPORTED and in trial beta !!
 * =============================================================
 *
 * @since 4.9			started to update widgets to support Block Widget editor - but this is only in trial BETA and preview is not supported properly !!
 */
if( ! current_theme_supports( 'avia_enable_widgets_block_editor' ) )
{
	// Disables the block editor from managing widgets in the Gutenberg plugin.
	add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );

	// Disables the block editor from managing widgets.
	add_filter( 'use_widgets_block_editor', '__return_false' );
}



/**
 * Load config files
 *
 * @since 7.0
 */
require_once( 'includes/config-enfold/init-exclude-plugin-configs.php' );
require_once( 'includes/config-enfold/init-base-data.php' );
require_once( 'includes/config-enfold/init-support.php' );
require_once( 'includes/config-enfold/functions-wp-core.php' );
require_once( 'includes/config-enfold/functions-framework.php' );
require_once( 'includes/config-enfold/functions-enqueue.php' );
require_once( 'includes/config-enfold/functions-alb.php' );


/*
 * wpml multi site config file
 * needs to be loaded before the framework
 */
if( ! current_theme_supports( 'avia_exclude_wpml' ) )
{
	require_once( 'config-wpml/config.php' );
}

/**
 * layerslider plugin - needs to be loaded before framework because we need to add data to the options array
 *
 * To be backwards compatible we still support  add_theme_support('deactivate_layerslider');
 * This will override the option setting "activation" of the bundled plugin !!
 *
 * @since 4.2.1
 */
require_once( 'config-layerslider/config.php' );


/**
 * Needed by framework options page already - not only in frontend
 */
require_once( 'includes/classes/class-privacy-class.php' ); 			// holds privacy managment shortcodes and functions


/**
 *	AVIA FRAMEWORK by Kriesi
 *  ========================
 *
 * this include calls a file that automatically includes all the files within the folder framework and therefore makes
 * all functions and classes available for later use
 */
require_once 'framework/avia_framework.php';


/**
 * Init object so we can hook and add user defined image sizes
 */
$resp_images = Av_Responsive_Images();

/**
 * Get options and reinit responsive image object
 */
$resp_img_config = array(
		'default_jpeg_quality'	=> 100,						//	ensure best image quality - use filter avf_responsive_images_defaults to change
		'theme_images'			=> $avia_config['imgSize'],
		'readableImgSizes'		=> $avia_config['readableImgSize'],
		'no_lazy_loading_ids'	=> array()					//	add is's of images for permanently disable lazy loading attribute
	);

$resp_images->reinit( $resp_img_config );


avia_backend_add_thumbnail_size( $avia_config );


if( ! isset( $content_width ) )
{
	/**
	 * @used_by ?????
	 */
	$content_width = $avia_config['imgSize']['featured']['width'];
}


/*
 *  load some frontend functions in folder include:
 */
require_once( 'includes/admin/register-portfolio.php' );			// register custom post types for portfolio entries
require_once( 'includes/admin/register-widget-area.php' );			// register sidebar widgets for the sidebar and footer
require_once( 'includes/loop-comments.php' );						// necessary to display the comments properly
require_once( 'includes/helpers/helper-template-logic.php' ); 		// holds the template logic so the theme knows which templates to use
require_once( 'includes/classes/class-social-media-icons.php' );	// holds some helper functions necessary for twitter and facebook buttons
require_once( 'includes/helpers/helper-post-format.php' ); 			// holds actions and filter necessary for post formats
require_once( 'includes/helpers/helper-markup.php' ); 				// holds the markup logic (schema.org and html5)
require_once( 'includes/helpers/helper-assets.php' ); 				// holds asset managment functions
require_once( 'includes/classes/class-avia-custom-pages.php' ); 	// holds management functions for custom pages like 404, maintenance, footer page
require_once( 'includes/classes/class-responsive-typo.php' );		// management for responsive typos in theme options page

if( current_theme_supports( 'avia_conditionals_for_mega_menu' ) )
{
	require_once( 'includes/classes/class-conditional-mega-menu.php' );  // holds the walker for the responsive mega menu (must be activated by user)
}

require_once( 'includes/classes/class-responsive-mega-menu.php' ); 	// holds the walker for the responsive mega menu

//require_once( 'config-gutenberg/class-avia-gutenberg.php' );		//	gutenberg - might be necessary to move when part of WP core

require_once( 'config-templatebuilder/config.php' );				// Advanced Layout Builder plugin

if( function_exists( 'Avia_Builder' ) )
{
	//adds the plugin initalization scripts that add styles and functions
	require_once( 'config-gutenberg/class-avia-gutenberg.php' );		//	gutenberg - might be necessary to move when part of WP core
}

if( ! current_theme_supports( 'avia_exclude_bbPress' ) )
{
	require_once( 'config-bbpress/config.php' );					// compatibility with  bbpress forum plugin
}



if( ! current_theme_supports( 'avia_exclude_GFForms' ) )
{
	require_once( 'config-gravityforms/config.php' );				// compatibility with gravityforms plugin
}

if( ! current_theme_supports( 'avia_exclude_pojo_accessibility' ) )
{
	require_once( 'config-pojo-accessibility/class-avia-pojo-accessibility.php' );	//compatibility with "One Click Accessibility" plugin
}

if( ! current_theme_supports( 'avia_exclude_wp_accessibility' ) )
{
	require_once( 'config-wp-accessibility/class-avia-wp-accessibility.php' );		//compatibility with "WP Accessibility" plugin
}

if( ! current_theme_supports( 'avia_exclude_WooCommerce' ) )
{
	require_once( 'config-woocommerce/woo-loader.php' );			//compatibility with woocommerce plugin
}

if( ! current_theme_supports( 'avia_exclude_wpSEO' ) )
{
	require_once( 'config-wordpress-seo/config.php' );				//compatibility with Yoast WordPress SEO plugin
}

if( ! current_theme_supports( 'avia_exclude_rank_math' ) )
{
	require_once( 'config-rank-math/config.php' );				//compatibility with Rank Math SEO plugin
}

if( ! current_theme_supports( 'avia_exclude_menu_exchange' ) )
{
	require_once( 'config-menu-exchange/config.php' );				//compatibility with Zen Menu Logic and Themify_Conditional_Menus plugin
}

if( ! current_theme_supports( 'avia_exclude_relevanssi' ) )
{
	require_once( 'config-relevanssi/class-avia-relevanssi.php' );	//compatibility with relevanssi plugin
}

if( ! current_theme_supports( 'deactivate_tribe_events_calendar' ) )
{
	require_once( 'config-events-calendar/config.php' );			//compatibility with the Events Calendar plugin
}

if( ! current_theme_supports( 'avia_exclude_instagram_feed' ) )
{
	require_once( 'config-instagram-feed/class-avia-instagram-feed.php' );		//compatibility with Smash Balloon Instagram Feed plugin
}

if( ! current_theme_supports( 'avia_exclude_leaflet_map' ) )
{
	require_once( 'config-leaflet-maps/class-avia-leaflet-maps.php' );				//compatibility with Leflet Maps plugin
}

if( ! current_theme_supports( 'avia_exclude_lottie-animations' ) )
{
	require_once( 'config-lottie-animations/class-avia-lottie-animations.php' );	//support for lottie animations
}

if( current_theme_supports( 'avia_include_cookiebot' ) )
{
	require_once( 'config-cookiebot/class-avia-cookiebot.php' );					//cookiebot support - must be activated by user explicit as only in BETA
}

if( ! current_theme_supports( 'avia_exclude_acf' ) )			//	support for ACF - Advanced custom fields plugin
{
	require_once( 'config-acf/class-avia-acf.php' );
}

// if(is_admin())
require_once( 'includes/admin/class-helper-compat-update.php');			// include helper functions for new versions


/**
 *  register custom functions that are not related to the framework but necessary for the theme to run
 */
require_once( 'includes/config-enfold/functions-enfold.php' );


/**
 * disable loading of file when option is not selected
 */
if( ! empty( avia_get_option( 'old_browser_support' ) ) )
{
	require_once( 'includes/config-enfold/functions-legacy-browser.php' );
}

