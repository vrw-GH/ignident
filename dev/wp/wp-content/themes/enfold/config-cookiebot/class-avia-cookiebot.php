<?php
namespace enfold\Cookiebot;

/**
 * ==============================================================================
 * This implementation is only in BETA and fully dependent on input by community.
 * ==============================================================================
 *
 * To activate it add to your child theme functions.php:
 *
 * add_theme_support( 'avia_include_cookiebot' );
 *
 *
 * Implements support for plugin "Cookie banner plugin for WordPress – Cookiebot CMP by Usercentrics" ( https://wordpress.org/plugins/cookiebot/  )
 * Base solution inspired and based on Jan Thiel - see link to support forum
 *
 *
 * @link https://kriesi.at/support/topic/cookiebot-support-feature-request-with-patch/
 * @added_by Guenter
 * @since 5.7
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

if( ! class_exists( __NAMESPACE__ . '\Avia_Cookiebot', false ) )
{
	class Avia_Cookiebot
	{
		/**
		 * Holds the instance of this class
		 *
		 * @since 5.7
		 * @var enfold\Cookiebot\Avia_Cookiebot
		 */
		static private $_instance = null;

		/**
		 * Return the instance of this class
		 *
		 * @since 5.7
		 * @return enfold\Cookiebot\Avia_Cookiebot
		 */
		static public function instance()
		{
			if( is_null( Avia_Cookiebot::$_instance ) )
			{
				Avia_Cookiebot::$_instance = new Avia_Cookiebot();
			}

			return Avia_Cookiebot::$_instance;
		}

		/**
		 * @since 5.7
		 */
		protected function __construct()
		{
			add_filter( 'avf_allow_wp_hooks_dependency', [ $this, 'handler_avf_allow_wp_hooks_dependency' ], 500000, 1 );

			add_action( 'admin_enqueue_scripts', [ $this, 'handler_admin_enqueue_scripts' ], 10 );
			add_action( 'wp_enqueue_scripts', [ $this, 'handler_wp_enqueue_scripts' ], 10 );
		}

		/**
		 * @since 5.7
		 */
		public function handler_admin_enqueue_scripts()
		{
			$vn = avia_get_theme_version();
			$min_js = avia_minify_extension( 'js' );

			wp_enqueue_script( 'avia_cookiebot_js', AVIA_BASE_URL . "config-cookiebot/cookiebot{$min_js}.js", [ 'wp-hooks' ], $vn, false );
		}

		/**
		 * @since 5.7
		 */
		public function handler_wp_enqueue_scripts()
		{
			$vn = avia_get_theme_version();
			$min_js = avia_minify_extension( 'js' );

			wp_enqueue_script( 'avia_cookiebot_js', AVIA_BASE_URL . "config-cookiebot/cookiebot{$min_js}.js", [ 'wp-hooks', 'avia-js' ], $vn, false );
		}

		/**
		 * We force wp-hooks to be loaded as we need it
		 *
		 * @since 5.7
		 * @param array $dependencies
		 * @return array
		 */
		public function handler_avf_allow_wp_hooks_dependency( $dependencies = [] )
		{
			if( ! in_array( 'wp-hooks', $dependencies ) )
			{
				$dependencies[] = 'wp-hooks';
			}

			return $dependencies;
		}

	}

	/**
	 * Returns the main instance of enfold\Cookiebot\Avia_Cookiebot to prevent the need to use globals
	 *
	 * @since 5.7
	 * @return enfold\Cookiebot\Avia_Cookiebot
	 */
	function AviaCookiebot()
	{
		return Avia_Cookiebot::instance();
	}

	AviaCookiebot();

}
