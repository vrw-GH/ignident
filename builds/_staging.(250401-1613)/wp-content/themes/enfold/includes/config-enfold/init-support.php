<?php
/*
 * This helper file initialises theme support to WP, functionality of theme and plugins
 * Moved from functions.php
 *
 * @since 7.0
 * @added_by Günter
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


/*
 * add support for responsive mega menus
 */
add_theme_support( 'avia_mega_menu' );

/*
 * add support for improved backend styling
 */
add_theme_support( 'avia_improved_backend_style' );

/**
 * add support for toggles in framework option pages instead of checkboxes
 *
 * @since 4.6.3
 */
add_theme_support( 'avia_option_pages_toggles' );

/*
 * adds support for the new avia sidebar manager
 */
add_theme_support( 'avia_sidebar_manager' );

/**
 *
 */
add_theme_support( 'automatic-feed-links' );

/*
 *  add post format options
 */
add_theme_support( 'post-formats', array( 'link', 'quote', 'gallery', 'video', 'image', 'audio' ) );

/*
 * compat mode for easier theme switching from one avia framework theme to another
 */
add_theme_support( 'avia_post_meta_compat' );

/*
 * make sure that enfold widgets dont use the old slideshow parameter in widgets, but default post thumbnails
 */
add_theme_support( 'force-post-thumbnails-in-widget' );

/*
 * display page titles via wordpress default output
 *
 * @since 3.6
 */
add_theme_support( 'title-tag' );

/**
 * display custom nav menus
 */
add_theme_support( 'nav_menus' );


add_post_type_support( 'page', 'excerpt' );

/**
 * add support for post thumbnails
 */
add_theme_support( 'post-thumbnails' );

