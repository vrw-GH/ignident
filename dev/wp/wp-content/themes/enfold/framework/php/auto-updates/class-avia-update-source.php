<?php
/**
 * Contract for anything that can tell the theme updater which version is
 * available and where to download it.
 *
 * Enfold has always updated itself from Envato, and that assumption was welded
 * into the updater: the class deciding "is there a new version" and the class
 * talking to api.envato.com were the same conversation. Selling direct means a
 * customer may hold an Envato token or a SureCart licence key, and both have to
 * drive the same update.
 *
 * Envato is the only implementation today.
 *
 * @since 8.1
 */

if( ! defined( 'ABSPATH' ) ) { exit; }		// Exit if accessed directly


if( ! interface_exists( 'Avia_Update_Source', false ) )
{
	/**
	 * @since 8.1
	 */
	interface Avia_Update_Source
	{
		/**
		 * Identifies the source, matching the type strings the resolver works in.
		 *
		 * @since 8.1
		 * @return string			'envato' | 'surecart'
		 */
		public function get_type();

		/**
		 * Look up which of our themes have a newer version available.
		 *
		 * $candidates is the array built by avia_auto_updates::get_theme_keys():
		 *
		 *		array< string $theme_name, array{ item: array{
		 *			id: string,
		 *			url?: string,
		 *			wordpress_theme_metadata: array{
		 *				theme_name: string, stylesheet: string, version: string|false
		 *			}
		 *		} } >
		 *
		 * It is opaque to the updater, which is what lets a second source fill the
		 * same keys from somewhere other than Envato.
		 *
		 * @since 8.1
		 * @param array $candidates
		 * @return array
		 * @throws Avia_Update_Source_Exception
		 */
		public function get_available_products( array $candidates );

		/**
		 * Where to download one product from, resolved at install time.
		 *
		 * Takes the whole product entry rather than an id: Envato keys downloads
		 * by item id, other sources will not.
		 *
		 * @since 8.1
		 * @param array $product
		 * @return string
		 * @throws Avia_Update_Source_Exception
		 */
		public function get_download_url( array $product );

		/**
		 * Check the stored credential and report what could be reached with it.
		 *
		 * Errors are deliberately not part of the return value - they stay on
		 * get_errors(), because that is where the updater log reads them from.
		 *
		 * @since 8.1
		 * @return array			keys 'purchases' | 'username' | 'email'
		 */
		public function verify_credential();

		/**
		 * @since 8.1
		 * @return WP_Error|false			false when nothing went wrong
		 */
		public function get_errors();

		/**
		 * @since 8.1
		 * @return void
		 */
		public function clear_errors();
	}
}


if( ! class_exists( 'Avia_Update_Source_Exception', false ) )
{
	/**
	 * Base for anything an update source throws.
	 *
	 * Avia_Envato_Exception extends this, so every existing catch keeps working
	 * while the updater's own catches can widen to cover a second source.
	 *
	 * @since 8.1
	 */
	class Avia_Update_Source_Exception extends Exception
	{
		/**
		 * @since 8.1
		 * @param string $message
		 * @param int $code
		 * @param Throwable|null $previous
		 */
		public function __construct( $message = '', $code = 0, $previous = null )
		{
			parent::__construct( $message, $code, $previous );
		}
	}
}
