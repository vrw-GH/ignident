<?php
/*
 * This helper file checks for active plugins and disables include of config files
 *
 * @since 4.5.7.1
 * @added_by Günter
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'bbPress', false ) )
{
	add_theme_support( 'avia_exclude_bbPress' );
}

if ( ! class_exists( 'Tribe__Events__Main', false ) )
{
	add_theme_support( 'deactivate_tribe_events_calendar' );
}

if( ! class_exists( 'GFForms', false ) )
{
	add_theme_support( 'avia_exclude_GFForms' );
}

if( ! class_exists( 'ZenOfWPMenuLogic', false ) && ! class_exists( 'Themify_Conditional_Menus', false ) )
{
	add_theme_support( 'avia_exclude_menu_exchange' );
}

if( ! class_exists( 'Pojo_Accessibility', false ) )
{
	add_theme_support( 'avia_exclude_pojo_accessibility' );
}

if( ! function_exists( 'wpacc_enqueue_scripts' ) )
{
	add_theme_support( 'avia_exclude_wp_accessibility' );
}

if( ! isset( $relevanssi_variables ) || ! isset( $relevanssi_variables['file'] ) )
{
	add_theme_support( 'avia_exclude_relevanssi' );
}

if ( ! class_exists( 'WooCommerce', false ) )
{
	add_theme_support( 'avia_exclude_WooCommerce' );
}

if( ! class_exists( 'wpSEO', false ) && ! defined( 'WPSEO_VERSION' ) )
{
	add_theme_support( 'avia_exclude_wpSEO' );
}

if( ! ( defined( 'ICL_SITEPRESS_VERSION' ) && defined( 'ICL_LANGUAGE_CODE' ) ) )
{
	add_theme_support( 'avia_exclude_wpml' );
}

if( ! class_exists( 'SB_Instagram_Feed', false ) )
{
	add_theme_support( 'avia_exclude_instagram_feed' );
}

if( ! class_exists( 'Leaflet_Map', false ) )
{
	add_theme_support( 'avia_exclude_leaflet_map' );
}


/**
 *
 * @since 4.5.7.1
 */
do_action( 'ava_deactivate_enfold_plugin_addons' );