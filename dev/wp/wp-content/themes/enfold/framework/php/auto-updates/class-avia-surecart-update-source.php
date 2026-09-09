<?php
/**
 * The SureCart implementation of Avia_Update_Source.
 *
 * Answers the same three questions as the Envato source - which versions exist,
 * where to download one, is this credential any good - against our own licensing
 * rather than a marketplace.
 *
 * @since 8.1
 */

if( ! defined( 'ABSPATH' ) ) { exit; }		// Exit if accessed directly

require_once( __DIR__ . '/class-avia-update-source.php' );
require_once( __DIR__ . '/class-avia-surecart-licence.php' );


if( ! class_exists( 'Avia_SureCart_Update_Source', false ) )
{
	class Avia_SureCart_Update_Source implements Avia_Update_Source
	{
		/**
		 * Where the activation id is kept between requests. Without it SureCart
		 * refuses to expose a release, so losing it means losing updates until
		 * the licence is verified again.
		 *
		 * @since 8.1
		 */
		const ACTIVATION_OPTION = 'avia_surecart_activation_id';

		/**
		 * @since 8.1
		 * @var string
		 */
		protected $credential;

		/**
		 * @since 8.1
		 * @var Avia_SureCart_Base_API|null
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
			return 'surecart';
		}

		/**
		 * Built on first use, matching the Envato source, so that constructing a
		 * source stays free of option reads and of WordPress generally.
		 *
		 * @since 8.1
		 * @return Avia_SureCart_Base_API
		 */
		protected function api()
		{
			if( ! $this->api instanceof Avia_SureCart_Base_API )
			{
				if( ! class_exists( 'Avia_SureCart_Base_API', false ) )
				{
					require_once( __DIR__ . '/class-avia-surecart-base-api.php' );
				}

				$this->api = new Avia_SureCart_Base_API( $this->credential, $this->public_token() );
			}

			return $this->api;
		}

		/**
		 * Our store's public token, which every licensing route needs.
		 *
		 * Shipped in the theme on purpose. It identifies OUR store, not the
		 * customer - SureCart calls it a public token and its own SDK takes it as
		 * a constructor argument for exactly this reason, so every copy of a
		 * licensed plugin or theme carries one. It grants nothing on its own: the
		 * licence key is what authorises anything, and the admin API token, which
		 * really is a secret, is a different value entirely and is not here.
		 *
		 * Without it every licensing call comes back 401 and every customer is
		 * told the update service rejected our credentials, so a customer install
		 * - which has the theme and no SureCart plugin - cannot be left to find
		 * this for itself.
		 *
		 * @since 8.1
		 */
		const PUBLIC_TOKEN = 'pt_5qZNDhryW72jvDtgrYuVqgK6';

		/**
		 * Prefers the store this site is actually connected to, when there is one.
		 *
		 * On our own installs the SureCart plugin is present and authoritative -
		 * it also means a staging store works without editing the theme. Customer
		 * sites have no plugin and fall through to the shipped token.
		 *
		 * @since 8.1
		 * @return string
		 */
		protected function public_token()
		{
			$token = '';

			if( class_exists( '\SureCart\Models\Account' ) )
			{
				$account = \SureCart\Models\Account::find();

				if( ! is_wp_error( $account ) && ! empty( $account->public_token ) )
				{
					$token = (string) $account->public_token;
				}
			}

			if( '' === $token )
			{
				$token = self::PUBLIC_TOKEN;
			}

			return (string) apply_filters( 'avf_surecart_public_token', $token );
		}

		/**
		 * Which of our themes have a newer version available.
		 *
		 * SureCart knows about one product, so rather than asking per candidate
		 * this fetches the current release once and writes its version onto the
		 * candidate it belongs to.
		 *
		 * @since 8.1
		 * @param array $candidates
		 * @return array
		 * @throws Avia_SureCart_Exception
		 */
		public function get_available_products( array $candidates )
		{
			$release = $this->api()->get_current_release( $this->activation_id() );

			return self::apply_release( $candidates, $release );
		}

		/**
		 * Match a release onto the candidate it describes.
		 *
		 * Pure, and separated out because it is the piece most likely to be wrong
		 * in a way nothing reports: a release that matches no candidate simply
		 * yields no update, with no error anywhere.
		 *
		 * Matched on the STYLESHEET against release_json.slug rather than on the
		 * theme name. SureCart requires that slug to equal the folder name -
		 * updates silently never appear otherwise - so it is the one field
		 * guaranteed to line up. Names are editable and translated.
		 *
		 * @since 8.1
		 * @param array $candidates
		 * @param mixed $release				media object from expose_current_release
		 * @return array
		 */
		static public function apply_release( array $candidates, $release )
		{
			$release = is_object( $release ) ? get_object_vars( $release ) : $release;

			if( ! is_array( $release ) || empty( $release['release_json'] ) )
			{
				return $candidates;
			}

			$json = (array) $release['release_json'];
			$slug = isset( $json['slug'] ) ? (string) $json['slug'] : '';
			$version = isset( $json['version'] ) ? (string) $json['version'] : '';

			if( '' === $slug || '' === $version )
			{
				return $candidates;
			}

			foreach( $candidates as $name => $candidate )
			{
				$stylesheet = $candidate['item']['wordpress_theme_metadata']['stylesheet'] ?? '';

				if( $stylesheet === $slug )
				{
					$candidates[ $name ]['item']['wordpress_theme_metadata']['version'] = $version;
				}
			}

			return $candidates;
		}

		/**
		 * A freshly signed download URL.
		 *
		 * Deliberately fetched here rather than reused from the update check: the
		 * URL carries a 15 minute expiry, so one minted during the twice daily
		 * version check would be long dead by the time anyone pressed the button.
		 *
		 * @since 8.1
		 * @param array $product
		 * @return string
		 * @throws Avia_SureCart_Exception
		 */
		public function get_download_url( array $product )
		{
			$release = $this->api()->get_current_release( $this->activation_id() );

			return isset( $release['url'] ) ? (string) $release['url'] : '';
		}

		/**
		 * Check the licence and claim a seat.
		 *
		 * Returns the same shape the option page already renders, so the existing
		 * template keeps working - though the licence-specific screen in the
		 * tracker will want more than this.
		 *
		 * @since 8.1
		 * @return array
		 */
		public function verify_credential()
		{
			$info = array();

			try
			{
				$licence = $this->api()->get_licence();
			}
			catch ( Avia_Update_Source_Exception $ex )
			{
				return array( 'purchases' => '' );
			}

			$status = Avia_SureCart_Licence::status( $licence );

			/**
			 * 'purchases' is Envato's word, kept because the gate that decides
			 * whether a credential counts as verified still reads this key. See
			 * the tracker issue on generalising the verified state.
			 */
			$info['purchases'] = Avia_SureCart_Licence::STATUS_VALID === $status ? 'success' : '';
			$info['licence_status'] = $status;
			$info['revokes_at'] = Avia_SureCart_Licence::revokes_at( $licence );
			$info['days_remaining'] = Avia_SureCart_Licence::days_remaining( $licence );
			$info['seats_used'] = Avia_SureCart_Licence::seats_used( $licence );
			$info['seats_limit'] = Avia_SureCart_Licence::seats_limit( $licence );

			if( Avia_SureCart_Licence::STATUS_VALID !== $status )
			{
				return $info;
			}

			try
			{
				$activation = $this->api()->create_activation( $licence['id'] ?? '' );

				if( ! empty( $activation['id'] ) )
				{
					update_option( self::ACTIVATION_OPTION, $activation['id'] );
					$info['activation_id'] = $activation['id'];
				}
			}
			catch ( Avia_Update_Source_Exception $ex )
			{
				// A licence that verifies but cannot claim a seat is still worth
				// reporting as verified; the seat failure is its own message.
				$info['activation_id'] = '';
			}

			return $info;
		}

		/**
		 * @since 8.1
		 * @return string
		 */
		protected function activation_id()
		{
			return (string) get_option( self::ACTIVATION_OPTION, '' );
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
