<?php
/**
 * MAIN THEME OPTIONS PAGE
 * =======================
 *
 * @since 4.8.2: modified
 *
 * Defines the option pages and tab structure and includes all files that hold the options.
 * Options for each tab are stored in seperate files, each for a tab.
 *
 * Option ID's must be unique, no matter in which options page or tab they are defined. Retrieving an option checks all pages and returns the first value found.
 * The internal options array holds the options seperated in parent pages (this has already been done prior 4.8.2):
 *
 *		array(
 *			'avia'		=> array(	'options_id' => value  )
 *			'avia_ext'	=> array(	'options_id' => value  )
 *			'...'		=> array(	'options_id' => value  )
 *		);
 *
 * This means, that when you move options between pages you have to move them in options array also (otherwise settings might get lost or return wrong results).
 *
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

global $avia_config, $avia_pages, $avia_elements, $avia_admin_values;


//	clear global values - avia_superobject::reset_options() includes this file a second time in avia_superobject::_create_option_arrays()
$avia_pages = array();
$avia_elements = array();

//	register default arrays for options
include( 'register-admin-option-values.php' );


/**
 * $avia_pages holds the data necessary for backend page creation.
 * For first element in a page 'slug' and 'parent' must have the same value
 *
 * @since 4.8.2	array key was changed to slug (prior numeric)
 */

$options_default_path = AVIA_BASE . 'includes/admin/';

/**
 * Options Page Theme Options (= main theme options page)
 * ======================================================
 *
 * The first entry defines the WP Dashboard Menu Entry !!!
 */
$avia_pages['avia'] = array(
				'parent'	=> 'avia',
				'slug'		=> 'avia',
				'icon'		=> "new/svg/spanner-screwdriver-7.svg",
				'title'		=> __( 'Theme Options', 'avia_framework' ),
				'include'	=> $options_default_path . 'option_tabs/avia_avia.php'
		);

$avia_pages['layout'] = array(
				'parent'	=> 'avia',
				'slug'		=> 'layout',
				'icon'		=> "new/svg/window-within-7.svg",
				'title'		=> __( 'General Layout', 'avia_framework' ),
				'include'	=> $options_default_path . 'option_tabs/avia_layout.php'
		);

$avia_pages['styling'] = array(
				'parent'	=> 'avia',
				'slug'		=> 'styling',
				'icon'		=> "new/svg/color-palette-7.svg",
				'title'		=> __( 'General Styling', 'avia_framework' ),
				'include'	=> $options_default_path . 'option_tabs/avia_styling.php'
		);

$avia_pages['customizer'] = array(
				'parent'	=> 'avia',
				'slug'		=> 'customizer',
				'icon'		=> "new/svg/magic-wand-7.svg",
				'title'		=> __( 'Advanced Styling', 'avia_framework' ),
				'include'	=> $options_default_path . 'option_tabs/avia_customizer.php'
		);

$avia_pages['menu'] = array(
				'parent'	=> 'avia',
				'slug'		=> 'menu',
				'icon'		=> "new/svg/custom-menu.svg",
				'title'		=> __( 'Main Menu', 'avia_framework' ),
				'include'	=> $options_default_path . 'option_tabs/avia_menu.php'
		);

$avia_pages['header'] = array(
				'parent'	=> 'avia',
				'slug'		=> 'header',
				'icon'		=> "new/svg/layout-arrange-02-7.svg",
				'title'		=> __( 'Header', 'avia_framework' ),
				'include'	=> $options_default_path . 'option_tabs/avia_header.php'
		);

$avia_pages['sidebars'] = array(
				'parent'	=> 'avia',
				'slug'		=> 'sidebars',
				'icon'		=> "new/svg/layout-arrange-13-7.svg",
				'title'		=> __( 'Sidebar Settings', 'avia_framework' ),
				'include'	=> $options_default_path . 'option_tabs/avia_sidebars.php'
		);

$avia_pages['footer'] = array(
				'parent'	=> 'avia',
				'slug'		=> 'footer',
				'icon'		=> "new/svg/layout-reverse.svg",
				'title'		=> __( 'Footer', 'avia_framework' ),
				'include'	=> $options_default_path . 'option_tabs/avia_footer.php'
		);

$avia_pages['builder'] = array(
				'parent'	=> 'avia',
				'slug'		=> 'builder',
				'icon'		=> "new/svg/window-three-7.svg",
				'title'		=> __( 'Layout Builder', 'avia_framework' ),
				'include'	=> $options_default_path . 'option_tabs/avia_builder.php'
		);

$avia_pages['avia_element_templates'] = array(
				'parent'	=> 'avia',
				'slug'		=> 'avia_element_templates',
				'icon'		=> "new/svg/window-plus-7.svg",
				'title'		=> __( 'Custom Elements', 'avia_framework' ),
				'include'	=> $options_default_path . 'option_tabs/avia_element_templates.php'
		);

$avia_pages['blog'] = array(
				'parent'	=> 'avia',
				'slug'		=> 'blog',
				'icon'		=> "new/svg/note-write-7.svg",
				'title'		=> __( 'Blog Layout', 'avia_framework' ),
				'include'	=> $options_default_path . 'option_tabs/avia_blog.php'
		);

$avia_pages['social'] = array(
				'parent'	=> 'avia',
				'slug'		=> 'social',
				'icon'		=> "new/svg/circle-user-7.svg",
				'title'		=> __( 'Social Profiles', 'avia_framework' ),
				'include'	=> $options_default_path . 'option_tabs/avia_social.php'
		);

$avia_pages['performance'] = array(
				'parent'	=> 'avia',
				'slug'		=> 'performance',
				'icon'		=> "new/svg/performance.svg",
				'title'		=> __( 'Performance', 'avia_framework' ),
				'include'	=> $options_default_path . 'option_tabs/avia_performance.php'
		);

$avia_pages['cookie'] = array(
				'parent'	=> 'avia',
				'slug'		=> 'cookie',
				'icon'		=> "new/svg/cookie.svg",
				'title'		=> __( 'Privacy and Cookies', 'avia_framework' ),
				'include'	=> $options_default_path . 'option_tabs/avia_cookie.php'
		);

$avia_pages['seo'] = array(
				'parent'	=> 'avia',
				'slug'		=> 'seo',
				'icon'		=> "new/svg/seo-7.svg",
				'title'		=> __( 'SEO Support', 'avia_framework' ),
				'include'	=> $options_default_path . 'option_tabs/avia_seo.php'
		);

$avia_pages['newsletter'] = array(
				'parent'	=> 'avia',
				'slug'		=> 'newsletter',
				'icon'		=> "new/svg/newspaper-7.svg",
				'title'		=> __( 'Newsletter', 'avia_framework' ),
				'include'	=> $options_default_path . 'option_tabs/avia_newsletter.php'
		);

$avia_pages['google'] = array(
				'parent'	=> 'avia',
				'slug'		=> 'google',
				'icon'		=> "new/svg/paper-map-7.svg",
				'title'		=> __( 'Google Services', 'avia_framework' ),
				'include'	=> $options_default_path . 'option_tabs/avia_google.php'
		);

$visible = class_exists( 'woocommerce', false ) ? '' : 'hidden';

$avia_pages['shop'] = array(
				'parent'	=> 'avia',
				'slug'		=> 'shop',
				'icon'		=> "new/svg/shopping-cart-7.svg",
				'title'		=> __( 'Shop Options', 'avia_framework' ),
				'class'		=> $visible,
				'include'	=> $options_default_path . 'option_tabs/avia_shop.php'
		);

$visible = current_theme_supports( 'avia_disable_dummy_import' ) ? 'hidden' : '';

$avia_pages['demo'] = array(
				'parent'	=> 'avia',
				'slug'		=> 'demo',
				'icon'		=> "new/svg/window-up-7.svg",
				'title'		=> __( 'Demo Import', 'avia_framework' ),
				'class'		=> $visible,
				'include'	=> $options_default_path . 'option_tabs/avia_demo.php'
		);

$visible = current_theme_supports( 'avia_disable_import_export' ) ? 'hidden' : '';

$avia_pages['upload'] = array(
				'parent'	=> 'avia',
				'slug'		=> 'upload',
				'icon'		=> "new/svg/connect-arrow-up-down-7.svg",
				'title'		=> __( 'Import/Export/...', 'avia_framework' ),
				'class'		=> $visible,
				'include'	=> $options_default_path . 'option_tabs/avia_upload.php'
		);



/**
 * Options Page Theme Extensions
 * =============================
 */
$avia_pages['avia_ext'] = array(
				'parent'	=> 'avia_ext',
				'slug'		=> 'avia_ext',
				'icon'		=> 'new/svg/server-plus-7.svg',
				'title'		=>  __( 'Theme Extensions', 'avia_framework' ),
				'include'	=> $options_default_path . 'option_tabs/extensions/avia_ext_avia_ext.php'
		);



$avia_pages['leaflet_maps'] = array(
				'parent'	=> 'avia_ext',
				'slug'		=> 'leaflet_maps',
				'icon'		=> 'new/svg/leaf.svg',
				'title'		=>  __( 'OpenStreetMaps', 'avia_framework' ),
				'include'	=> $options_default_path . 'option_tabs/extensions/avia_ext_leaflet_maps.php'
		);

$avia_pages['pojo_accessibility'] = array(
				'parent'	=> 'avia_ext',
				'slug'		=> 'accessibility',
				'icon'		=> 'new/svg/accessibility.svg',
				'title'		=>  __( 'Accessibility', 'avia_framework' ),
				'include'	=> $options_default_path . 'option_tabs/extensions/avia_ext_accessibility.php'
		);


/**
 * Add custom theme option page tabs or modify
 *
 * @since 4.8.2
 * @param array $avia_pages
 * @return array
 */
$avia_pages = apply_filters( 'avf_theme_options_pages', $avia_pages );


//required for the general styling color schemes
include( 'register-backend-styles.php' );

//required for the advanced styling wizard
include( 'register-backend-advanced-styles.php' );


/**
 * Allow to include a user defined file to add or alter backend styles
 *
 * @since 4.5.5
 * @return string		full path to the include file ( not a relative path !!! )
 */
$custom_path = apply_filters( 'avf_register_custom_backend_styles', '' );
if( ! empty( $custom_path ) && file_exists( $custom_path ) )
{
	include $custom_path;
}


//	define icons for global use
$iconSpan = "<span class='pr-icons'>
				<img src='" . AVIA_IMG_URL . "icons/new/svg/social_facebook.svg' alt='' />
				<img src='" . AVIA_IMG_URL . "icons/new/svg/social_twitter.svg' alt='' />
				<img src='" . AVIA_IMG_URL . "icons/new/svg/social_flickr.svg' alt='' />
			</span>";


//	load option tabs content
foreach( $avia_pages as $key => $page_info )
{
	if( isset( $page_info['include'] ) && ! empty( $page_info['include'] ) )
	{
		include( $page_info['include'] );

		/**
		 * @used_by				avia_WPML					10
		 * @since 4.8
		 * @param string $context
		 */
		do_action( 'ava_theme_options_elements_tab', $page_info['slug'] );
	}
}


/**
 * Modify theme options elements
 *
 * @since 4.8.2
 * @param array $avia_elements
 * @return array
 */
$avia_elements = apply_filters( 'avf_theme_options_elements', $avia_elements );
