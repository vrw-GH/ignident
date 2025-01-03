<?php
namespace aviaFramework;

/**
 * Helper class that implements fuctions to support accessibilty
 *
 * @author guenter
 * @since 6.0
 */
if( ! defined( 'ABSPATH' ) ) { exit; }

if( ! class_exists( __NAMESPACE__ . '\accessibility', false ) )
{
	class accessibility
	{
		/**
		 * Holds the instance of this class
		 *
		 * @since 6.0
		 * @var aviaFramework\accessibility
		 */
		static private $_instance = null;

		/**
		 * Return the instance of this class
		 *
		 * @since 6.0
		 * @return aviaFramework\accessibility
		 */
		static public function instance()
		{
			if( is_null( accessibility::$_instance ) )
			{
				accessibility::$_instance = new accessibility();
			}

			return accessibility::$_instance;
		}

		/**
		 * @since 6.0
		 */
		protected function __construct()
		{

		}

		/**
		 * Returns markup for screenreader to ignore heading when empty
		 *
		 * @link https://kriesi.at/support/topic/accessibility-compliance-of-forms-and-tab-elements-in-the-enfold-theme/#post-1443669
		 * @since 6.0
		 * @param string $content
		 * @param string $heading_tag
		 * @param string $quote
		 * @return string
		 */
		public function heading_markup( $content, $heading_tag, $quote = '"' )
		{
			if( ! empty( $content ) )
			{
				return '';
			}

			if( ! in_array( $heading_tag, [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ] ) )
			{
				return '';
			}

			if( ! in_array( $quote, [ '"', "'" ] ) )
			{
				$quote = '"';
			}

			return $quote == '"' ? 'aria-hidden="true" tabindex="-1"' : "aria-hidden='true' tabindex='-1'";
		}
	}

	/**
	 * Returns the main instance of aviaFramework\accessibility to prevent the need to use globals
	 *
	 * @since 6.0
	 * @return aviaFramework\accessibility
	 */
	function Accessibility()
	{
		return accessibility::instance();
	}

}
