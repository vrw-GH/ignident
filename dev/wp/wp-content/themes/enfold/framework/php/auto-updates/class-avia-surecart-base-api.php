<?php
/**
 * Talks to SureCart's public licensing API.
 *
 * Deliberately hand written rather than vendoring SureCart's WordPress SDK. The
 * SDK is a reasonable piece of code and its endpoint contract is what this class
 * follows, but it also ships an Updater that registers
 * pre_set_site_transient_update_themes - the very filter Enfold already owns -
 * and a settings screen we do not want. Its hooks are opt-in, so vendoring would
 * be safe, but it carries no tagged releases, which makes "swap the directory to
 * upgrade" a fiction. Three endpoints against wp_remote_get, in the same shape as
 * Avia_Envato_Base_API next door, is less to own and easier to read.
 *
 * Every route and field below was verified against the live account on
 * 2026-08-19, not taken from documentation.
 *
 * @since 8.1
 */

if( ! defined( 'ABSPATH' ) ) { exit; }		// Exit if accessed directly

require_once( __DIR__ . '/class-avia-update-source.php' );


if( ! class_exists( 'Avia_SureCart_Base_API', false ) )
{
	class Avia_SureCart_Base_API
	{
		/**
		 * Overridable so a staging store can be pointed elsewhere, matching the
		 * SDK's own SURECART_LICENSING_ENDPOINT / surecart_licensing_endpoint.
		 *
		 * @since 8.1
		 */
		const API_BASE = 'https://api.surecart.com/';

		/**
		 * How long a download URL stays valid, in seconds.
		 *
		 * Short on purpose. The URL is fetched at install time rather than stored
		 * in the update transient, because a link minted during the twice daily
		 * update check would be long dead by the time anyone pressed the button.
		 *
		 * @since 8.1
		 */
		const RELEASE_URL_TTL = 900;

		/**
		 * The customer's licence key.
		 *
		 * @since 8.1
		 * @var string
		 */
		protected $credential;

		/**
		 * The store's public token (pt_…). NOT the admin API token: the licensing
		 * routes live under /v1/public/ and reject the admin one.
		 *
		 * @since 8.1
		 * @var string
		 */
		protected $public_token;

		/**
		 * @since 8.1
		 * @var WP_Error
		 */
		protected $errors;

		/**
		 * @since 8.1
		 * @param string $credential
		 * @param string $public_token
		 */
		public function __construct( $credential, $public_token = '' )
		{
			$this->credential = (string) $credential;
			$this->public_token = (string) $public_token;
			$this->errors = new WP_Error();
		}

		/**
		 * @since 8.1
		 */
		public function __destruct()
		{
			unset( $this->errors );
		}

		/**
		 * @since 8.1
		 * @return string
		 */
		protected function base()
		{
			if( defined( 'SURECART_LICENSING_ENDPOINT' ) )
			{
				return trailingslashit( SURECART_LICENSING_ENDPOINT );
			}

			return trailingslashit( apply_filters( 'avf_surecart_api_base', self::API_BASE ) );
		}

		/**
		 * The licence as SureCart sees it.
		 *
		 * @since 8.1
		 * @return array
		 * @throws Avia_SureCart_Exception
		 */
		public function get_licence()
		{
			return $this->request( 'GET', 'v1/public/licenses/' . rawurlencode( $this->credential ), array(), __( 'Licence:', 'avia_framework' ) );
		}

		/**
		 * Claim a seat for this site.
		 *
		 * The fingerprint is the site URL, which is also how SureCart identifies
		 * the activation later - so a site that changes address looks like a new
		 * one, and a site deleted without deactivating leaves its seat behind.
		 *
		 * @since 8.1
		 * @param string $licence_id
		 * @return array
		 * @throws Avia_SureCart_Exception
		 */
		public function create_activation( $licence_id )
		{
			$body = array(
						'activation' => array(
											'fingerprint'	=> esc_url_raw( get_site_url() ),
											'name'			=> get_bloginfo( 'name' ),
											'license'		=> $licence_id
										)
					);

			return $this->request( 'POST', 'v1/public/activations', $body, __( 'Activation:', 'avia_framework' ) );
		}

		/**
		 * Give a seat back.
		 *
		 * @since 8.1
		 * @param string $activation_id
		 * @return array
		 * @throws Avia_SureCart_Exception
		 */
		public function delete_activation( $activation_id )
		{
			return $this->request( 'DELETE', 'v1/public/activations/' . rawurlencode( $activation_id ), array(), __( 'Deactivation:', 'avia_framework' ) );
		}

		/**
		 * The current release, complete with a signed, expiring download URL.
		 *
		 * Returns a media object carrying release_json (name, slug, version,
		 * requires, tested, requires_php), url and url_expires_at. An activation
		 * is required - SureCart will not expose a release to a licence that no
		 * site has claimed.
		 *
		 * @since 8.1
		 * @param string $activation_id
		 * @return array
		 * @throws Avia_SureCart_Exception
		 */
		public function get_current_release( $activation_id )
		{
			$route = add_query_arg(
							array(
								'activation_id'	=> $activation_id,
								'expose_for'	=> self::RELEASE_URL_TTL
							),
							'v1/public/licenses/' . rawurlencode( $this->credential ) . '/expose_current_release'
						);

			return $this->request( 'GET', $route, array(), __( 'Release:', 'avia_framework' ) );
		}

		/**
		 * @since 8.1
		 * @param string $method
		 * @param string $route
		 * @param array $body
		 * @param string $prefix			which request failed, for the log
		 * @return array
		 * @throws Avia_SureCart_Exception
		 */
		protected function request( $method, $route, array $body = array(), $prefix = '' )
		{
			/**
			 * avia_auto_updates is an admin-only class, and this API is reachable
			 * from cron and from WP-CLI where it has never been loaded. A
			 * user-agent string is not worth a fatal.
			 */
			$version = class_exists( 'avia_auto_updates', false ) ? avia_auto_updates::get_version() : '';

			$args = array(
						'method'		=> $method,
						'timeout'		=> apply_filters( 'avf_surecart_http_timeout', 30 ),
						'headers'		=> array(
											'Accept'		=> 'application/json',
											'User-Agent'	=> 'Enfold/' . $version . '; ' . get_bloginfo( 'url' )
										)
					);

			/**
			 * The public routes still want the store's public token. Without it
			 * every call is a 401, which reads like an invalid licence key.
			 */
			if( '' !== $this->public_token )
			{
				$args['headers']['Authorization'] = 'Bearer ' . $this->public_token;
			}

			if( ! empty( $body ) )
			{
				$args['body'] = $body;
			}

			$response = wp_remote_request( $this->base() . $route, $args );

			if( is_wp_error( $response ) )
			{
				$this->add_error_object( $response );
				throw new Avia_SureCart_Exception();
			}

			$code = wp_remote_retrieve_response_code( $response );
			$decoded = json_decode( wp_remote_retrieve_body( $response ), true );

			if( ! in_array( (int) $code, array( 200, 201 ), true ) )
			{
				if( ! class_exists( 'Avia_SureCart_Error_Translator', false ) )
				{
					require_once( __DIR__ . '/class-avia-surecart-error-translator.php' );
				}

				foreach( Avia_SureCart_Error_Translator::translate( $code, $decoded, $prefix ) as $error_code => $message )
				{
					$this->errors->add( $error_code, $message );
				}

				throw new Avia_SureCart_Exception( '', (int) $code );
			}

			if( ! is_array( $decoded ) )
			{
				$this->errors->add( 'SureCart wrong datastructure', $prefix . ' ' . __( 'SureCart returned a response we could not read. Unable to check for updates.', 'avia_framework' ) );
				throw new Avia_SureCart_Exception();
			}

			return $decoded;
		}

		/**
		 * @since 8.1
		 * @return WP_Error|false
		 */
		public function get_errors()
		{
			return count( $this->errors->errors ) > 0 ? $this->errors : false;
		}

		/**
		 * @since 8.1
		 * @return void
		 */
		public function clear_errors()
		{
			$this->errors = new WP_Error();
		}

		/**
		 * @since 8.1
		 * @param WP_Error $error
		 * @return void
		 */
		protected function add_error_object( WP_Error $error )
		{
			foreach( $error->get_error_codes() as $code )
			{
				foreach( $error->get_error_messages( $code ) as $message )
				{
					$this->errors->add( $code, $message );
				}
			}
		}
	}
}


if( ! class_exists( 'Avia_SureCart_Exception', false ) )
{
	/**
	 * @since 8.1
	 */
	class Avia_SureCart_Exception extends Avia_Update_Source_Exception
	{
	}
}
