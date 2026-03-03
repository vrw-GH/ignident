<?php
/*
 * This helper file holds filters and action concerning framework
 * Moved from functions.php
 *
 * @since 7.0
 * @added_by Günter
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


//call functions from theme
add_filter( 'the_content_more_link', 'avia_remove_more_jump_link' );


/*
 * function that changes the icon of the theme update tab
 */
if( ! function_exists( 'avia_theme_update_filter' ) )
{
	function avia_theme_update_filter( $data )
	{
		if( current_theme_supports( 'avia_improved_backend_style' ) )
		{
			$data['icon'] = 'new/svg/arrow-repeat-two-7.svg';
		}
		return $data;
	}

	add_filter( 'avf_update_theme_tab', 'avia_theme_update_filter', 30, 1 );
}


if( ! function_exists( 'avia_force_generate_styles' ) )
{
	/**
	 * Layerslider reported problems with our merged and cached stylesheets after update of their plugin breaking layout of sliders
	 * Suggested to use 'upgrader_process_complete' to remove our files
	 *
	 * This action is called:
	 *
	 *  - once for every plugin when updated from plugin page
	 *  - once for all plugins when updated from update page
	 *
	 * @since 5.6.10
	 * @link https://developer.wordpress.org/reference/hooks/upgrader_process_complete/
	 * @param WP_Upgrader $upgrader_object
	 * @param array $options
	 */
	function avia_force_generate_styles( $upgrader_object, $options )
	{
		/**
		 * Return without performing any action
		 *
		 * @since 5.6.10
		 * @param WP_Upgrader $upgrader_object
		 * @param array $options
		 * @return boolean
		 */
		if( false !== apply_filters( 'avf_skip_upgrader_process_complete', false, $upgrader_object, $options ) )
		{
			return;
		}

		if( ! $upgrader_object instanceof WP_Upgrader )
		{
			return;
		}

		if( empty( $options['action'] ) || ! in_array( $options['action'], [ 'update' ] ) )
		{
			return;
		}

		if( empty( $options['type'] ) || ! in_array( $options['type'], [ 'plugin', 'core' ] ) )
		{
			return;
		}

		do_action( 'ava_after_theme_update' );
	}

	add_action( 'upgrader_process_complete', 'avia_force_generate_styles', 10, 2 );
}


if( ! function_exists( 'avia_generate_stylesheet' ) )
{
	/**
	 * saves the style options array into an external css file rather than fetching the data from the database
	 *
	 * @param array|false $options
	 */
	function avia_generate_stylesheet( $options = false )
	{
		global $avia, $avia_config;

		$safe_name = avia_backend_safe_string( $avia->base_data['prefix'] );
		$safe_name = apply_filters( 'avf_dynamic_stylesheet_filename', $safe_name );

		if( defined( 'AVIA_CSSFILE' ) && AVIA_CSSFILE === false )
		{
			$dir_flag = update_option( 'avia_stylesheet_dir_writable' . $safe_name, 'false' );
			$stylesheet_flag = update_option( 'avia_stylesheet_exists' . $safe_name, 'false' );
			return;
		}

		$wp_upload_dir = wp_upload_dir();
		$stylesheet_dir = $wp_upload_dir['basedir'] . $avia_config['dynamic_files_upload_folder'];
		$stylesheet_dir = str_replace( '\\', '/', $stylesheet_dir );
		$stylesheet_dir = apply_filters( 'avia_dyn_stylesheet_dir_path',  $stylesheet_dir );

		$isdir = avia_backend_create_folder( $stylesheet_dir );

		/*
		 * directory could not be created (WP upload folder not writeable)
		 * @todo save error in db and output error message for user.
		 * @todo maybe add mkdirfix: http://php.net/manual/de/function.mkdir.php
		 */
		if( $isdir === false )
		{
			$dir_flag = update_option( 'avia_stylesheet_dir_writable' . $safe_name, 'false' );
			$stylesheet_flag = update_option( 'avia_stylesheet_exists' . $safe_name, 'false' );
			return;
		}

		/*
		 *  Go ahead - WP managed to create the folder as expected
		 */
		$stylesheet = trailingslashit( $stylesheet_dir ) . $safe_name . '.css';

		/**
		 * @since ???
		 * @param string $stylesheet
		 * @return string
		 */
		$stylesheet = apply_filters( 'avia_dyn_stylesheet_file_path', $stylesheet );


		//import avia_superobject and reset the options array
		$avia_superobject = $GLOBALS['avia'];
		$avia_superobject->reset_options();

		//regenerate style array after saving options page so we can create a new css file that has the actual values and not the ones that were active when the script was called
		avia_prepare_dynamic_styles();

		//generate stylesheet content
		$generate_style = new avia_style_generator( $avia_superobject, false, false, false );
		$styles = $generate_style->create_styles();

		$created = avia_backend_create_file( $stylesheet, $styles, true );

		if( $created === true )
		{
			$dir_flag = update_option( 'avia_stylesheet_dir_writable' . $safe_name, 'true' );
			$stylesheet_flag = update_option( 'avia_stylesheet_exists' . $safe_name, 'true' );
			$dynamic_id = update_option( 'avia_stylesheet_dynamic_version' . $safe_name, uniqid() );
		}
		else
		{
			$dir_flag = update_option( 'avia_stylesheet_dir_writable' . $safe_name, 'false' );
			$stylesheet_flag = update_option( 'avia_stylesheet_exists' . $safe_name, 'false' );
			$dynamic_id = delete_option( 'avia_stylesheet_dynamic_version' . $safe_name );
		}
	}

	add_action( 'ava_after_theme_update', 'avia_generate_stylesheet', 30, 1 );				/*after theme update*/
	add_action( 'ava_after_import_demo_settings', 'avia_generate_stylesheet', 30, 1 );		/*after demo settings imoport*/
	add_action( 'avia_ajax_after_save_options_page', 'avia_generate_stylesheet', 30, 1 );	/*after options page saving*/
}


if( ! function_exists( 'avia_force_clear_caches' ) )
{
	/**
	 * Force popular cache plugins to clear their cache to force reloading - needed to support minified css files
	 * Plugins are found in ..\includes\admin\option_tabs\avia_performance.php - not all plugins provide functions.
	 * Results provided by ChatGTP.
	 *
	 * @link https://kriesi.at/support/topic/css-files-are-always-deleted/
	 * @link solution inspired by marcsteiner1  https://kriesi.at/support/topic/css-files-are-always-deleted/#post-1491455
	 * @since 7.1.4
	 * @param array|false $options
	 */
	function avia_force_clear_caches( $options = false )
	{
		/**
		 * Shortcut or use a custom function
		 *
		 * @since 7.1.4
		 * @param boolean $continue
		 * @param array|false $options
		 * @return boolean					false to shortcut
		 */
		if( true !== apply_filters( 'avf_before_force_clear_caches', true, $options ) )
		{
			return;
		}

		// Object Cache Pro: flush object cache
		if( function_exists( 'wp_cache_flush' ) )
		{
			wp_cache_flush();
		}

		// WP Rocket: full cache purge
		if( function_exists( 'rocket_clean_domain' ) )
		{
			rocket_clean_domain();
		}

		// WP Super Cache: Clear Supercache + Standard-Cache, no message
		if( function_exists( 'wp_cache_clear_cache' ) )
		{
			wp_cache_clear_cache( 'all', false );
		}

		// Comet Cache + Comet Cache Pro: full cache purge
		if( function_exists( 'comet_cache_flush' ) )
		{
			comet_cache_flush();
		}

		// W3 Total Cache: full cache purge
		if( function_exists( 'w3tc_flush_all' ) )
		{
			w3tc_flush_all();
		}

		// WP Fastest Cache: full cache purge
		if( function_exists( 'wpfc_clear_all_cache' ) )
		{
			wpfc_clear_all_cache();
		}

		// Simple Cache: full cache purge
		if( function_exists( 'simple_cache_flush' ) )
		{
			simple_cache_flush();
		}

		// Cachify: full cache purge
		if( class_exists( 'Cachify' ) )
		{
			$cachify = Cachify::instance();
			if( method_exists( $cachify, 'flush' ) )
			{
				$cachify->flush();
			}
		}

		// Hyper Cache: full cache purge
		if( function_exists( 'hyper_cache_flush' ) )
		{
			hyper_cache_flush(); // leert den gesamten Cache
		}

		// Cache Enabler: full cache purge
		if( class_exists( 'Cache_Enabler' ) )
		{
			$cache_enabler = Cache_Enabler::instance();
			if( method_exists( $cache_enabler, 'clear_cache' ) )
			{
				$cache_enabler->clear_cache();
			}
		}

		// Autoptimize: full cache purge
		if( function_exists( 'autoptimize_flush_cache' ) )
		{
			autoptimize_flush_cache(); // leert alle Autoptimize-Caches
		}

		// Breeze: full cache purge   (according to ChatGTP)
		do_action( 'breeze_clear_all_cache' );

		// Lite Speed Cache: full cache purge
		if( class_exists( 'LiteSpeed_Cache_API' ) )
		{
			if( method_exists( 'LiteSpeed_Cache_API', 'purge_all' ) )
			{
				LiteSpeed_Cache_API::purge_all();
			}
		}

		// Hummingbird Page Speed Optimization: full cache purge
		if( class_exists( 'WPMUDEV_Hummingbird' ) )
		{
			if( method_exists( 'WPMUDEV_Hummingbird', 'flush_cache' ) )
			{
				WPMUDEV_Hummingbird::flush_cache();
			}
		}

		// Powered Cache: full cache purge
		if( function_exists( '\PoweredCache\Utils\powered_cache_flush' ) )
		{
			\PoweredCache\Utils\powered_cache_flush();
		}

		// Borlabs Cache: full cache purge
		if( class_exists( 'BorlabsCache\BorlabsCache' ) )
		{
			$borlabsCache = BorlabsCache\BorlabsCache::getInstance();
			if( method_exists( $borlabsCache, 'flushAll' ) )
			{
				$borlabsCache->flushAll();
			}
		}

		/**
		 * @since 7.1.4
		 */
		do_action( 'ava_force_clear_caches', $options );
	}

	add_action( 'ava_after_theme_update', 'avia_force_clear_caches', 30, 1 );				/*after theme update*/
	add_action( 'ava_after_import_demo_settings', 'avia_force_clear_caches', 30, 1 );		/*after demo settings imoport*/
	add_action( 'avia_ajax_after_save_options_page', 'avia_force_clear_caches', 30, 1 );	/*after options page saving*/
}


if( ! function_exists( 'avia_generate_grid_dimension' ) )
{
	/**
	 *
	 * @param array|'' $options
	 * @param array $color_set
	 * @param array $styles
	 */
	function avia_generate_grid_dimension( $options, $color_set, $styles )
	{
		global $avia_config;

		if( $options !== '' )
		{
			extract( $options );
		}

		//	values from $options !!!
		if( empty( $content_width ) )
		{
			$content_width = 73;
		}

		if( empty( $combined_width ) )
		{
			$combined_width = 100;
		}

		if( empty( $responsive_size ) )
		{
			$responsive_size = '1130px';
		}

		if( $responsive_size != '' )
		{
			$css = "
					.container {
						width:{$combined_width}%;
					}

					.container .av-content-small.units {
						width:{$content_width}%;
					}

					.responsive .boxed#top,
					.responsive.html_boxed.html_header_sticky #header,
					.responsive.html_boxed.html_header_transparency #header{
						width: {$responsive_size};
						max-width:90%;
					}

					.responsive .container{
						max-width: {$responsive_size};
					}

				";

			$avia_config['style'][] = array(
										'key'	=> 'direct_input',
										'value'	=> AviaSuperobject()->styleGenerator()->css_strip_whitespace( $css, true )
									);
		}
	}

	add_action( 'ava_generate_styles', 'avia_generate_grid_dimension', 30, 3 ); /*after theme update*/
}


if( ! function_exists( 'avia_set_thumb_size' ) )
{
	/**
	 * change default thumbnail size and fullwidth size on theme activation
	 */
	function avia_set_thumb_size()
	{
		update_option( 'thumbnail_size_h', 80 );
		update_option( 'thumbnail_size_w', 80 );
		update_option( 'large_size_w', 1030 );
		update_option( 'large_size_h', 1030 );
	}

	add_action( 'avia_backend_theme_activation', 'avia_set_thumb_size' );
}

