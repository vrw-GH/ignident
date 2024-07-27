<?php
namespace enfoldLegacyFunctions;

/**
 * Hold functions that support fallback to outdated stuff or use of legacy code
 * All code placed here will be removed without any further notice -so do not rely on it
 *
 * @since 5.6.3
 * @added_by Günter
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! function_exists( '\enfoldLegacyFunctions\avia_old_browser_support' ) )
{
	/**
 	 * Theme option must be enabled for old browser support otherwise this file is not loaded !!!
	 * Activate/deactivate old browser support e.g. for certain pages only
	 *
	 * @since 5.6.3
	 * @param boolean $fallback
	 * @param string $context				'css' | 'js'
	 * @return boolean
	 */
	function avia_old_browser_support( $fallback, $context )
	{
		if( ! $fallback )
		{
			$fallback = ! empty( \avia_get_option( 'old_browser_support' ) );
		}

		return $fallback;
	}

	add_filter( 'avf_old_browser_support', '\enfoldLegacyFunctions\avia_old_browser_support', 10, 2 );
}

if( ! function_exists( '\enfoldLegacyFunctions\avia_load_legacy_assets' ) )
{
	/**
	 * @used_by				functions-legacy-browser.php / avia_old_browser_support()     10
	 * @since 5.6.3
	 * @param boolean $fallback_css
	 * @param string $context					'css' | 'js'
	 * @return boolean
	 */
	$fallback_css = apply_filters( 'avf_old_browser_support', false, 'css' );

	//	we load in frontend and backend
	if( $fallback_css )
	{
		if( is_admin() )
		{
			add_action( 'ava_framework_before_print_admin_page_styles', '\enfoldLegacyFunctions\avia_load_legacy_assets', 1 );
			add_filter( 'avf_preview_window_css_files', '\enfoldLegacyFunctions\avia_load_preview_window_css_files', 1 );
		}
		else
		{
			add_action( 'init', '\enfoldLegacyFunctions\avia_load_legacy_assets', 1 );
		}
	}

	/**
	 * Load legacy assets
	 *
	 * @since 5.6.3
	 */
	function avia_load_legacy_assets()
	{
		$template_url = get_template_directory_uri();
		$vn = avia_get_theme_version();

		//	as minify removes old rules we must load unminified file
		if( is_admin() )
		{
			wp_enqueue_style( 'avia-legacy_framework',  $template_url . '/css/legacy/legacy_framework.css', array(), $vn, 'all' );
			wp_enqueue_style( 'avia-legacy_template_builder_builder',  $template_url . '/css/legacy/legacy_template_builder_builder.css', array(), $vn, 'all' );
			wp_enqueue_style( 'avia-legacy_template_builder_shortcodes',  $template_url . '/css/legacy/legacy_template_builder_shortcodes.css', array(), $vn, 'all' );
			wp_enqueue_style( 'avia-legacy_configs',  $template_url . '/css/legacy/legacy_configs.css', array(), $vn, 'all' );
		}
		else
		{
			wp_enqueue_style( 'avia-legacy_enfold',  $template_url . '/css/legacy/legacy_enfold.css', array(), $vn, 'all' );
			wp_enqueue_style( 'avia-legacy_template_builder_shortcodes',  $template_url . '/css/legacy/legacy_template_builder_shortcodes.css', array(), $vn, 'all' );
			wp_enqueue_style( 'avia-legacy_configs',  $template_url . '/css/legacy/legacy_configs.css', array(), $vn, 'all' );
		}
	}
}

if( ! function_exists( '\enfoldLegacyFunctions\avia_load_preview_window_css_files' ) )
{
	/**
	 * Load legacy assets for modal preview window
	 *
	 * @since 5.6.3
	 * @param array $css
	 * @return array
	 */
	function avia_load_preview_window_css_files( array $css )
	{
		$template_url = get_template_directory_uri();

		$add = [
				$template_url . '/css/legacy/legacy_enfold.css'							=> 1,
				$template_url . '/css/legacy/legacy_template_builder_shortcodes.css'	=> 1
			];

		return array_merge( $add, $css );
	}
}

