<?php
/**
 * The Envato implementation of Avia_Update_Source.
 *
 * A thin adapter over Avia_Envato_Base_API, and deliberately nothing more. Every
 * decision it makes was already being made inside Avia_Theme_Updater; moving it
 * here is what lets a second source answer the same three questions without the
 * updater knowing which one it is talking to.
 *
 * @since 8.1
 */

if( ! defined( 'ABSPATH' ) ) { exit; }		// Exit if accessed directly

require_once( __DIR__ . '/class-avia-update-source.php' );


if( ! class_exists( 'Avia_Envato_Update_Source', false ) )
{
	class Avia_Envato_Update_Source implements Avia_Update_Source
	{
		/**
		 * Envato personal token
		 *
		 * @since 8.1
		 * @var string
		 */
		protected $credential;

		/**
		 * @since 8.1
		 * @var Avia_Envato_Base_API|null
		 */
		protected $api;

		/**
		 * @since 8.1
		 * @param string $credential
		 */
		public function __construct( $credential )
		{
			$this->credential = (string) $credential;
			$this->api = null;
		}

		/**
		 * @since 8.1
		 */
		public function __destruct()
		{
			unset( $this->api );
		}

		/**
		 * @since 8.1
		 * @return string
		 */
		public function get_type()
		{
			return 'envato';
		}

		/**
		 * Build the API object on first use, not in the constructor.
		 *
		 * avia_auto_updates::get_theme_name() and ::get_version() read the theme
		 * headers, so constructing eagerly would move when that happens. Matching
		 * the old get_envato_api() timing keeps this a refactor - and keeps the
		 * source constructible without WordPress.
		 *
		 * @since 8.1
		 * @return Avia_Envato_Base_API
		 */
		protected function api()
		{
			if( ! $this->api instanceof Avia_Envato_Base_API )
			{
				if( ! class_exists( 'Avia_Envato_Base_API', false ) )
				{
					require_once( __DIR__ . '/class-avia-envato-base-api.php' );
				}

				$this->api = new Avia_Envato_Base_API( $this->credential, avia_auto_updates::get_theme_name(), avia_auto_updates::get_version() );
			}

			return $this->api;
		}

		/**
		 * @since 8.1
		 * @param array $candidates
		 * @return array
		 * @throws Avia_Envato_Exception
		 */
		public function get_available_products( array $candidates )
		{
			/**
			 * Backwards comp. for WP - keep existing code in case we need a fallback
			 *
			 * Envato specific, which is why it belongs here rather than in the
			 * updater: get_purchases() asks Envato what this account bought, while
			 * get_product_infos() looks up the versions of themes we already found
			 * installed. Only the second makes sense for a source that has no
			 * concept of a marketplace purchase.
			 *
			 * @since 4.5.3
			 */
			if( current_theme_supports( 'avia_envato_purchase_query' ) || ! function_exists( 'wp_get_themes' ) )
			{
				return $this->api()->get_purchases();
			}

			return $this->api()->get_product_infos( $candidates );
		}

		/**
		 * @since 8.1
		 * @param array $product
		 * @return string
		 * @throws Avia_Envato_Exception
		 */
		public function get_download_url( array $product )
		{
			return $this->api()->get_wp_download_url( $product['item']['id'] );
		}

		/**
		 * Ask Envato what this token can reach.
		 *
		 * The order is load-bearing: errors accumulate on the API object in the
		 * order they occur, and that is the order they are listed on the theme
		 * options page. Only 'purchases' decides whether the token counts as
		 * verified - a username or email failure is cosmetic.
		 *
		 * @since 8.1
		 * @return array
		 */
		public function verify_credential()
		{
			$info = array();

			try
			{
				$this->api()->get_purchases();
				$info['purchases'] = 'success';
			}
			catch ( Avia_Update_Source_Exception $ex )
			{
				$info['purchases'] = '';
			}

			try
			{
				$info['username'] = $this->api()->get_userdata( 'username' );
			}
			catch ( Avia_Update_Source_Exception $ex )
			{
				$info['username'] = '';
			}

			try
			{
				$info['email'] = $this->api()->get_userdata( 'email' );
			}
			catch ( Avia_Update_Source_Exception $ex )
			{
				$info['email'] = '';
			}

			return $info;
		}

		/**
		 * @since 8.1
		 * @return WP_Error|false
		 */
		public function get_errors()
		{
			return $this->api()->get_errors();
		}

		/**
		 * @since 8.1
		 * @return void
		 */
		public function clear_errors()
		{
			$this->api()->clear_errors();
		}
	}
}
