<?php
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


/**
 * Check if the avia builder was already included as plugin
 *
 * @return boolean
 */
function avia_builder_plugin_enabled()
{
	if( class_exists( 'AviaBuilder', false ) )
	{
		return true;

	}

	return false;
}


/**
 * Set the folder that contains the shortcodes
 * Override default paths set by "plugin".
 * Child theme and all other extensions must hook with a lower priority !
 *
 * To override theme shortcodes load your shortcodes before (insert your paths at the beginning of the array)
 *
 * @since 4.8.9 you may also use filter 'avf_before_load_single_shortcode_file'
 *
 * @param array $paths
 * @return array
 */
function avia_set_shortcode_folders( $paths = array() )
{
	$paths = array(
					dirname( __FILE__ ) . '/avia-shortcode-helpers/',
					dirname( __FILE__ ) . '/avia-shortcodes/'
				);

	return $paths;
}

add_filter( 'avia_load_shortcodes', 'avia_set_shortcode_folders', 1, 1 );


/**
 * Set the folder that contains assets like js and imgs.
 * Override default url set by "plugin".
 * Child theme and all other extensions must hook with a lower priority !
 *
 * @param string $url
 * @return string
 */
function avia_builder_plugins_url( $url )
{
	$url = get_template_directory_uri() . '/config-templatebuilder/';
	return $url;
}

add_filter( 'avia_builder_plugins_url', 'avia_builder_plugins_url', 1, 1 );


/**
 * If the builder was not included via plugin we include it now via theme
 */
if( ! avia_builder_plugin_enabled() )
{
	require_once( dirname( __FILE__ ) . '/avia-template-builder/php/base-classes/class-object-properties.php' );
	require_once( dirname( __FILE__ ) . '/avia-template-builder/php/class-template-builder.php' );

	//define( 'AVIA_BUILDER_TEXTDOMAIN',  'avia_framework' );


	//activates the builder safe mode. this hides the shortcodes that are built with the content builder from the default wordpress content editor.
	//can also be set to 'debug', to show shortcode content and extra shortcode container field
	Avia_Builder()->setMode( 'safe' );

	/**
	 * Set all elements that are fullwidth and need to interact with the section shortcode. av_section is included automatically
	 *
	 * @deprecated since 4.7.6.4			use $this->config['is_fullwidth'] = 'yes';
	 */
//	Avia_Builder()->setFullwidthElements( array('av_revolutionslider', 'av_layerslider' ,'av_slideshow_full', 'av_fullscreen', 'av_masonry_entries','av_masonry_gallery', 'av_google_map', 'av_slideshow_accordion', 'av_image_hotspot', 'av_portfolio', 'av_submenu', 'av_layout_row', 'av_button_big','av_feature_image_slider','av_tab_section','av_horizontal_gallery','av_postcontent') );
}



