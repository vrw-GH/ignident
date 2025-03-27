<?php
/*
 * This helper file holds core enqueue filters and action
 * Moved from functions.php
 *
 * @since 7.0
 * @added_by Günter
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly



if( ! function_exists( 'avia_register_frontend_scripts' ) )
{
	/**
	 * Register frontend scripts
	 *
	 */
	function avia_register_frontend_scripts()
	{
		global $avia_config;

		$vn = avia_get_theme_version();
		$options = avia_get_option();

		$template_url = get_template_directory_uri();
		$child_theme_url = get_stylesheet_directory_uri();

		$min_js = avia_minify_extension( 'js' );
		$min_css = avia_minify_extension( 'css' );

		/**
		 * @since 5.3   3-rd party use non minified only in debug mode
		 */
		$debug_js = '.min';
		$debug_css = '.min';

		if( defined( 'WP_DEBUG') && WP_DEBUG )
		{
			$debug_js = '';
			$debug_css = '';
		}

		/**
		 * We do not load wp-hooks for frontend - which is not by default
		 * To avoid checking for wp.hooks functions in js we added wrapper functions in avia-js.js
		 *
		 * @used_by				Avia_Cookiebot			500000
		 * @since 5.7
		 * @param array $dependency
		 * @return array
		 */
		$dependency = apply_filters( 'avf_allow_wp_hooks_dependency', array() );

		//	force load in header as shortcode scripts throw error
		wp_enqueue_script( 'avia-js', "{$template_url}/js/avia-js{$min_js}.js", $dependency, $vn, false );

		//register js
		wp_enqueue_script( 'avia-compat', "{$template_url}/js/avia-compat{$min_js}.js", array(), $vn, false ); //needs to be loaded at the top to prevent bugs
		wp_enqueue_script( 'avia-waypoints', "{$template_url}/js/waypoints/waypoints{$debug_js}.js", array( 'jquery' ), $vn, true );

		wp_enqueue_script( 'avia-default', "{$template_url}/js/avia{$min_js}.js", array( 'jquery', 'avia-waypoints' ), $vn, true );
		wp_enqueue_script( 'avia-hamburger-menu', "{$template_url}/js/avia-snippet-hamburger-menu{$min_js}.js", array( 'jquery', 'avia-default' ), $vn, true );
		wp_enqueue_script( 'avia-shortcodes', "{$template_url}/js/shortcodes{$min_js}.js", array( 'jquery', 'avia-default' ), $vn, true );
//		wp_enqueue_script( 'avia-parallax', $template_url . '/js/parallax.js', array( 'jquery', 'avia-default' ), $vn, true );
		wp_enqueue_script( 'avia-parallax-support', "{$template_url}/js/avia-snippet-parallax{$min_js}.js", array( 'jquery', 'avia-default' ), $vn, true );
		wp_enqueue_script( 'avia-fold-unfold', "{$template_url}/js/avia-snippet-fold-unfold{$min_js}.js", array( 'avia-default' ), $vn, true );

		wp_enqueue_script( 'jquery' );



		//register styles
		wp_register_style( 'avia-style',  $child_theme_url . '/style.css', array(), $vn, 'all' ); //only include in childthemes. has no purpose in main theme
		wp_register_style( 'avia-custom',  $template_url . '/css/custom.css', array(), $vn, 'all' );

		wp_enqueue_style( 'avia-grid', "{$template_url}/css/grid{$min_css}.css", array(), $vn, 'all' );
		wp_enqueue_style( 'avia-base', "{$template_url}/css/base{$min_css}.css", array( 'avia-grid' ), $vn, 'all' );
		wp_enqueue_style( 'avia-layout', "{$template_url}/css/layout{$min_css}.css", array( 'avia-base' ), $vn, 'all' );
		wp_enqueue_style( 'avia-scs', "{$template_url}/css/shortcodes{$min_css}.css", array( 'avia-layout' ), $vn, 'all' );
		wp_enqueue_style( 'avia-fold-unfold', "{$template_url}/css/avia-snippet-fold-unfold{$min_css}.css", array( 'avia-scs' ), $vn, 'all' );


		/************************************************************************
		Conditional style and script calling, based on theme options or other conditions
		*************************************************************************/

		$condition = ( isset( $options['header_position'] ) && $options['header_position'] == 'header_top' );
		$condition2 = ( isset( $options['header_sticky'] ) && $options['header_sticky'] == 'header_sticky' ) && $condition;
		$condition3 = ( isset( $options['reading_progress'] ) && $options['reading_progress'] != '' ) && $condition2;
		avia_enqueue_script_conditionally( $condition3, 'avia-header-reading-progress', "{$template_url}/js/avia-snippet-header-reading-progress{$min_js}.js", array( 'avia-default' ), $vn, true );


		//lightbox inclusion
		$condition = ! empty( $avia_config['use_standard_lightbox'] ) && ( 'disabled' != $avia_config['use_standard_lightbox'] );
		avia_enqueue_style_conditionally( $condition, 'avia-popup-css', "{$template_url}/js/aviapopup/magnific-popup{$debug_css}.css", array( 'avia-layout' ), $vn, 'screen' );
		avia_enqueue_style_conditionally( $condition, 'avia-lightbox', "{$template_url}/css/avia-snippet-lightbox{$min_css}.css", array( 'avia-layout' ), $vn, 'screen' );
		avia_enqueue_script_conditionally( $condition, 'avia-popup-js', "{$template_url}/js/aviapopup/jquery.magnific-popup{$debug_js}.js", array( 'jquery' ), $vn, true );
		avia_enqueue_script_conditionally( $condition, 'avia-lightbox-activation', "{$template_url}/js/avia-snippet-lightbox{$min_js}.js", array( 'avia-default' ), $vn, true );


		//mega menu inclusion (only necessary with sub menu items)
		$condition = ( avia_get_submenu_count('avia') > 0 );
		avia_enqueue_script_conditionally( $condition, 'avia-megamenu', "{$template_url}/js/avia-snippet-megamenu{$min_js}.js", array( 'avia-default' ), $vn, true );


		//sidebar menu inclusion (only necessary when header position is set to be a sidebar)
		$condition = ( isset( $options['header_position'] ) && $options['header_position'] != 'header_top' );
		avia_enqueue_script_conditionally( $condition , 'avia-sidebarmenu', "{$template_url}/js/avia-snippet-sidebarmenu{$min_js}.js", array( 'avia-default' ), $vn, true );


		//sticky header with header size calculator
		$condition  = ( isset( $options['header_position'] ) && $options['header_position'] == 'header_top' );
		$condition2 = ( isset( $options['header_sticky'] ) && $options['header_sticky'] == 'header_sticky' ) && $condition;
		avia_enqueue_script_conditionally( $condition2 , 'avia-sticky-header', "{$template_url}/js/avia-snippet-sticky-header{$min_js}.js", array( 'avia-default' ), $vn, true );

		//	footer - curtain behaviour
		$condition = ( isset( $options['color-body_style'] ) && $options['color-body_style'] == 'stretched' );
		avia_enqueue_script_conditionally( $condition, 'avia-footer-effects', "{$template_url}/js/avia-snippet-footer-effects{$min_js}.js", array( 'avia-default' ), $vn, true );

		//site preloader || post navigation (only css needed)
		$condition = ( isset( $options['preloader'] ) && $options['preloader'] == 'preloader' );
		$condition2 = false;
		if( isset( $options['disable_post_nav'] ) )
		{
			$condition2 = ( $options['disable_post_nav'] != 'disable_post_nav' ) && ( isset( $options['post_nav_swipe'] ) && $options['post_nav_swipe'] != '' );
		}
		else
		{
			$condition2 = ( isset( $options['post_nav_swipe'] ) && $options['post_nav_swipe'] != '' );
		}
		$condition3 = $condition || $condition2;
		avia_enqueue_script_conditionally( $condition , 'avia-siteloader-js', "{$template_url}/js/avia-snippet-site-preloader{$min_js}.js", array( 'avia-default' ), $vn, true, false );
		avia_enqueue_style_conditionally(  $condition3 , 'avia-siteloader', "$template_url/css/avia-snippet-site-preloader{$min_css}.css", array( 'avia-layout' ), $vn, 'screen', false );


		//load widget assets only if we got active widgets
		$condition = ( avia_get_active_widget_count() > 0 );
		avia_enqueue_script_conditionally( $condition , 'avia-widget-js', "{$template_url}/js/avia-snippet-widget{$min_js}.js", array( 'avia-default' ), $vn, true );
		avia_enqueue_style_conditionally(  $condition , 'avia-widget-css', "{$template_url}/css/avia-snippet-widget{$min_css}.css", array( 'avia-layout' ), $vn, 'screen' );


		//load mediaelement js
		$opt_mediaelement = isset( $options['disable_mediaelement'] ) ? $options['disable_mediaelement'] : '';

		$condition = true;
		if( 'force_mediaelement' != $opt_mediaelement )
		{
			$condition  = ( $opt_mediaelement != 'disable_mediaelement' ) && av_video_assets_required();
		}

		/**
		 * Allow to force loading of WP media element for 3rd party plugins. Nedded for wp_enqueue_media() to load properly.
		 *
		 * @since 4.1.2
		 * @param boolean $condition
		 * @param array $options
		 * @return boolean
		 */
		$condition = apply_filters( 'avf_enqueue_wp_mediaelement', $condition, $options );

		$condition2 = ( version_compare( get_bloginfo( 'version' ), '4.9', '>=' ) ) && $condition;
		avia_enqueue_script_conditionally( $condition , 'wp-mediaelement');
		avia_enqueue_style_conditionally( $condition2 , 'wp-mediaelement'); //With WP 4.9 we need to load the stylesheet separately


		//comment reply script
		global $post;
		$condition = ! ( isset( $options['disable_blog'] ) && $options['disable_blog'] == 'disable_blog' ) && $post && comments_open();
		$condition = ( is_singular() && get_option( 'thread_comments' ) ) && $condition;
		avia_enqueue_script_conditionally( $condition , 'comment-reply' );


		//rtl inclusion
		avia_enqueue_style_conditionally( is_rtl(), 'avia-rtl', "{$template_url}/css/rtl{$min_css}.css", array(), $vn, 'all' );


		//disable jquery migrate if no plugins are active (enfold does not need it) or if user asked for it in optimization options
		$condition = avia_count_active_plugins() == 0 || ( isset( $options['disable_jq_migrate'] ) && $options['disable_jq_migrate'] != 'disable_jq_migrate' );
		if( ! $condition )
		{
			avia_disable_query_migrate();
		}

		//move jquery to footer if no unkown plugins are active
		if( av_count_untested_plugins() == 0 || ( isset( $options['jquery_in_footer'] ) && $options['jquery_in_footer'] == 'jquery_in_footer' ) )
		{
			av_move_jquery_into_footer();
		}

		/************************************************************************
		Inclusion of the dynamic stylesheet
		*************************************************************************/
		global $avia, $avia_config;

		$safe_name = avia_backend_safe_string( $avia->base_data['prefix'] );
		$safe_name = apply_filters( 'avf_dynamic_stylesheet_filename', $safe_name );

		if( get_option( 'avia_stylesheet_exists' . $safe_name) == 'true' )
		{
			$avia_upload_dir = wp_upload_dir();

			/**
			 * Change the default dynamic upload url
			 *
			 * @since 4.4
			 */
			$avia_dyn_upload_path = apply_filters( 'avf_dyn_stylesheet_dir_url',  $avia_upload_dir['baseurl'] . $avia_config['dynamic_files_upload_folder'] );
			$avia_dyn_upload_path = trailingslashit( $avia_dyn_upload_path );

			if( is_ssl() )
			{
				$avia_dyn_upload_path = str_replace( 'http://', 'https://', $avia_dyn_upload_path );
			}

			/**
			 * Change the default dynamic stylesheet name
			 *
			 * @since 4.4
			 */
			$avia_dyn_stylesheet_url = apply_filters( 'avf_dyn_stylesheet_file_url', $avia_dyn_upload_path . $safe_name . '.css' );

			$version_number = get_option( 'avia_stylesheet_dynamic_version' . $safe_name );
			if( empty( $version_number ) )
			{
				$version_number = $vn;
			}

			wp_enqueue_style( 'avia-dynamic', $avia_dyn_stylesheet_url, array(), $version_number, 'all' );
		}

		wp_enqueue_style( 'avia-custom' );

		if( $child_theme_url != $template_url )
		{
			wp_enqueue_style( 'avia-style' );
		}
	}

	if( ! is_admin() )
	{
		add_action( 'wp_enqueue_scripts', 'avia_register_frontend_scripts' );
	}
}


if( ! function_exists( 'avia_remove_default_video_styling' ) )
{
	/**
	 * Remove default style for videos
	 *
	 * With WP 4.9 we need to load the stylesheet separately - therefore we must not remove it
	 */
	function avia_remove_default_video_styling()
	{
		if( version_compare( get_bloginfo( 'version' ), '4.9', '<' ) )
		{
			wp_dequeue_style( 'mediaelement' );
		}

		// wp_dequeue_script( 'wp-mediaelement' );
		// wp_dequeue_style( 'wp-mediaelement' );
	}

	if( ! is_admin() )
	{
		add_action( 'wp_footer', 'avia_remove_default_video_styling', 1 );
	}
}