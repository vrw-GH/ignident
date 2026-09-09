<?php
/**
 * This class is based on the new ENVATO 3.0 API and handles the automatic theme update
 *
 * @since 4.4.3
 * @added_by Günter
 *
 * to debug: set_site_transient('update_themes',null);
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

/**
 * Must be loaded before the class_exists guard below, not inside it.
 *
 * Avia_Envato_Exception extends Avia_Update_Source_Exception, and there is no
 * autoloader - so if this file is ever reached before the interface file, the
 * extends is a fatal on every admin page. class-avia-envato-base-api.php throws
 * Avia_Envato_Exception without requiring anything itself, relying on having
 * been loaded from here, which makes this the one place that has to be right.
 *
 * @since 8.1
 */
require_once( __DIR__ . '/class-avia-update-source.php' );

/**
 * The renewal reminder is used from the verification path below, and reads a
 * licence status constant from Avia_SureCart_Licence, so both are loaded here
 * for the same reason as above - there is no autoloader.
 *
 * @since 8.1
 */
require_once( __DIR__ . '/class-avia-surecart-licence.php' );
require_once( __DIR__ . '/class-avia-licence-reminder.php' );


if( ! class_exists( 'Avia_Theme_Updater', false ) )
{
	class Avia_Theme_Updater
	{
		/**
		 * Holds the instance of this class
		 *
		 * @since 4.4.3
		 * @var Avia_Theme_Updater
		 */
		static private $_instance = null;

		/**
		 * Envato author name(s) for the themes/plugins to check
		 *
		 * @var string|array
		 */
		protected $authors;

		/**
		 * The credential the update source authenticates with - an Envato personal
		 * token today, a SureCart licence key once that source exists.
		 *
		 * @since 4.4.3
		 * @since 8.1				renamed from $personal_token
		 * @var string
		 */
		protected $credential;

		/**
		 * Which kind of credential $credential is, in the resolver's terms.
		 *
		 * @since 8.1
		 * @var string				'envato' | 'surecart'
		 */
		protected $source_type;

		/**
		 * @since 4.4.3
		 * @since 8.1				an Avia_Update_Source rather than the Envato API directly
		 * @var Avia_Update_Source|null
		 */
		protected $update_source;

		/**
		 * function wp_update_themes calls filter pre_set_site_transient_update_themes twice during theme version check.
		 * As we have a rate limiting by Envato we cache the result here. We also cache the results in a transient.
		 *
		 * @since 4.4.3
		 * @var array
		 */
		protected $envato_results_cache;

		/**
		 * Optionname that saves state of last update - especially if there are Rate Limiting errors
		 *
		 * @since 4.4.3
		 * @var string
		 */
		protected $transient_logfile_name;

		/**
		 * Optionname that saves last update from envato to avoid multiple calls within a given period
		 *
		 * @since 4.5.2
		 * @var string
		 */
		protected $transient_cache_name;

		/**
		 * Return the instance of this class
		 *
		 * @since 4.4.3
		 * @param array $args
		 * @return Avia_Theme_Updater
		 */
		static public function instance( array $args = array() )
		{
			if( is_null( Avia_Theme_Updater::$_instance ) )
			{
				Avia_Theme_Updater::$_instance = new Avia_Theme_Updater( $args );
			}

			return Avia_Theme_Updater::$_instance;
		}

		/**
		 * @since 4.4.3
		 * @param array $args
		 */
		protected function __construct( array $args )
		{
			$this->authors = array();
			$this->credential = '';
			$this->source_type = '';
			$this->update_source = null;
			$this->envato_results_cache = null;
			$this->transient_logfile_name = '';
			$this->transient_cache_name = '';

			$this->init( $args );

			add_filter( 'pre_set_site_transient_update_themes', array( $this, 'handler_pre_set_site_transient_update_themes' ), 10, 1 );
			add_filter( 'upgrader_package_options', array( $this, 'handler_upgrader_package_options' ), 1000, 1 );
		}

		/**
		 * @since 4.4.3
		 */
		public function __destruct()
		{
			unset( $this->authors );
			unset( $this->update_source );
			unset( $this->envato_results_cache );
		}

		/**
		 * Allows a late initialisation of the object
		 *
		 * @since 4.4.3
		 * @param array $args
		 */
		public function init( array $args )
		{
			if( isset( $args['authors'] ) )
			{
				$this->authors = $args['authors'];

				if( ! is_array( $this->authors ) )
				{
					$this->authors = array( $this->authors );
				}
			}

			$old_token = $this->credential;

			/**
			 * 'personal_token' is kept as the argument name alongside the newer
			 * 'credential'. It is part of the public surface - child themes and the
			 * AviaSupport plugins call AviaThemeUpdater() with it - so renaming it
			 * outright would silently stop their updates.
			 *
			 * @since 8.1
			 */
			if( isset( $args['credential'] ) )
			{
				$this->credential = $args['credential'];
			}
			else if( isset( $args['personal_token'] ) )
			{
				$this->credential = $args['personal_token'];
			}

			if( isset( $args['source_type'] ) && '' !== $args['source_type'] )
			{
				$this->source_type = $args['source_type'];
			}

			if( $old_token != $this->credential )
			{
				unset( $this->update_source );
				$this->update_source = null;
			}
		}


		/**
		 * The update source for the stored credential, or false when there is none.
		 *
		 * Returning false rather than an unconfigured object is load-bearing:
		 * handler_pre_set_site_transient_update_themes() reads that false as "this
		 * site has no credential" and clears the updater log and the results cache.
		 * An object that merely reports itself unconfigured would make that branch
		 * unreachable and leave stale data behind on every site without a token.
		 *
		 * @since 4.4.3
		 * @since 8.1				returns an Avia_Update_Source, built by the factory
		 * @param string $new_token
		 * @return Avia_Update_Source|false
		 */
		protected function get_update_source( $new_token = '' )
		{
			/**
			 * "Was a token handed in?" is a question about the argument being
			 * absent, not about it being falsy - and empty( '0' ) is true.
			 *
			 * With empty() here, a customer pasting 0 into the token field had
			 * their PREVIOUS token verified instead, was told the check succeeded,
			 * and was then never offered another update, because the stored '0'
			 * fails this same test on every later request. The option page kept
			 * reporting the site up to date throughout.
			 *
			 * @since 8.1
			 */
			$supplied = '' !== (string) $new_token;
			$token = $supplied ? $new_token : $this->credential;

			if( '' === (string) $token )
			{
				return false;
			}

			if( ! class_exists( 'Avia_Update_Source_Factory', false ) )
			{
				require_once( __DIR__ . '/class-avia-update-source-factory.php' );
			}

			/**
			 * One field accepts either an Envato token or a SureCart licence key,
			 * so the service follows the credential rather than being fixed when
			 * the updater is built - a customer who swaps one for the other must
			 * not have to change a setting they cannot see.
			 *
			 * classify_or_default() never answers "unknown" for a credential that
			 * exists: anything unrecognised is tried against Envato, which is the
			 * status quo for every existing customer and fails visibly rather than
			 * silently. $source_type remains an explicit override for filters.
			 *
			 * @since 8.1
			 */
			$type = $this->source_type;

			if( '' === (string) $type )
			{
				if( ! class_exists( 'Avia_Credential_Classifier', false ) )
				{
					require_once( __DIR__ . '/class-avia-credential-classifier.php' );
				}

				$type = Avia_Credential_Classifier::classify_or_default( $token );
			}

			/**
			 * A token passed in explicitly is being verified, not used - it must not
			 * replace the source we already hold, or a failed verification would
			 * leave the site authenticating with the rejected credential.
			 */
			if( $supplied )
			{
				return Avia_Update_Source_Factory::create( $type, $new_token );
			}

			if( empty( $this->update_source ) )
			{
				$this->update_source = Avia_Update_Source_Factory::create( $type, $this->credential );
			}

			return $this->update_source;
		}


		/**
		 * Checks for theme update and adds theme package to update array
		 *
		 * @since 4.4.3
		 * @param stdClass $updates
		 * @return stdClass
		 */
		public function handler_pre_set_site_transient_update_themes( $updates )
		{
			/**
			 * Filter for theme updater transient
			 */
			if( ! isset( $updates->checked ) )
			{
				return $updates;
			}

			$this->authors = apply_filters( 'avf_theme_updater_authors', $this->authors );
			$this->credential = apply_filters( 'avf_theme_updater_personal_token', $this->credential );

			$api = $this->get_update_source();
			if( ! $api instanceof Avia_Update_Source )
			{
				/**
				 * We have no Envato token -> clear all saved log data and cache
				 */
				$this->clear_updater_log();
				$this->clear_cache();

				return $updates;
			}

			if( current_theme_supports( 'avia_envato_extended_log' ) )
			{
				$this->add_info_to_updater_log( __( 'Theme update check started', 'avia_framework' )  );
			}

			/**
			 * If we have already cached the result from Envato we can take this and avoid multiple requests for the same data
			 */
			if( $this->get_cache() )
			{
				if( ! empty( $this->envato_results_cache ) )
				{
					$purchases = avia_auto_updates::get_theme_keys();

					/**
					 * Check if theme has updated already and remove from cache
					 */
					foreach( $this->envato_results_cache as $theme => $update )
					{
						$theme_key = '';
						$stylesheet = '';
						foreach( $purchases as $name => $purchase )
						{
							if( $purchase['item']['wordpress_theme_metadata']['stylesheet'] == $theme )
							{
								$theme_key = $name;
								$stylesheet = $purchases[ $name ]['item']['wordpress_theme_metadata']['stylesheet'];
								break;
							}
						}

						$do_update = false;
						if( ! empty( $theme_key ) && isset( $purchases[ $theme_key ]['item']['wordpress_theme_metadata']['version'] ) )
						{
							if( version_compare( $purchases[ $theme_key ]['item']['wordpress_theme_metadata']['version'], $update['new_version'], '<' ) )
							{
								$do_update = true;
							}
							else if( version_compare( $purchases[ $theme_key ]['item']['wordpress_theme_metadata']['version'], $update['new_version'], '=' ) )
							{
								unset( $this->envato_results_cache[ $theme ] );
								$this->update_cache();
							}
						}

						if( $do_update )
						{
							$updates->response[ $theme ] = $update;
						}
						else if( array_key_exists( $theme, $updates->response ) )
						{
							unset( $updates->response[ $theme ] );
						}
					}
				}

				if( current_theme_supports( 'avia_envato_extended_log' ) )
				{
					$this->add_info_to_updater_log( __( 'Cache used', 'avia_framework' )  );
				}

				return $updates;
			}

			if( current_theme_supports( 'avia_envato_extended_log' ) )
			{
				$this->add_info_to_updater_log( __( 'No cache, Envato API request started', 'avia_framework' )  );
			}

			try
			{
				/**
				 * Which of the two Envato endpoints answers this moved into the
				 * Envato source: asking a marketplace what an account purchased is
				 * not a question a licence server can be asked.
				 *
				 * @since 4.5.3
				 * @since 8.1				delegated to the update source
				 */
				$purchases = $api->get_available_products( avia_auto_updates::get_theme_keys() );

				$installed = function_exists( 'wp_get_themes' ) ? wp_get_themes() : get_themes();
				$filtered = array();

				/**
				 * Attention:	In case there are multiple installs of the same theme in different folders
				 * =========	only the last folder is kept for update. As this is a rare situation in
				 *				production sites we can ignore this.
				 */
				foreach( $installed as $theme )
				{
					if( ! in_array( $theme->{'Author Name'}, $this->authors ) )
					{
						continue;
					}

					$filtered[ $theme->Name ] = $theme;
				}
			}
			catch ( Avia_Update_Source_Exception $ex )
			{
				$this->add_to_updater_log( $api );
				return $updates;
			}

			$errors_occured = false;
			$package_errors = array();

			foreach( $purchases as $purchase )
			{
				$theme_name = isset( $purchase['item']['wordpress_theme_metadata']['theme_name'] ) ? $purchase['item']['wordpress_theme_metadata']['theme_name'] : '';
				if( ! empty( $theme_name ) && isset( $filtered[ $theme_name ] ) )
				{
					/**
					 * Found the theme - check if we need to update
					 */
					$current = $filtered[ $theme_name ];
					if( version_compare( $current->Version, $purchase['item']['wordpress_theme_metadata']['version'], '<' ) )
					{
						try
						{
							$update = array(
											'url'				=> $purchase['item']['url'],
											'new_version'		=> $purchase['item']['wordpress_theme_metadata']['version'],
											'envato_item_id'	=> $purchase['item']['id'],
											'package'			=> ''
										);

							$updates->response[ $current->Stylesheet ] = $update;
							$this->add_to_cache( $current->Stylesheet, $update );

							if( current_theme_supports( 'avia_envato_extended_log' ) )
							{
								$this->add_info_to_updater_log( sprintf( __( 'Existing download package found for %s - %s', 'avia_framework' ), $current->Name, $update['new_version'] )  );
							}
						}
						catch( Avia_Update_Source_Exception $ex )
						{
							$errors_occured = true;
							$package_errors[] = $current->Name . ' - ' . $purchase['item']['wordpress_theme_metadata']['version'];
							continue;
						}
					}
				}
			}

			/**
			 * In case of an error we should try again to get the results.
			 * As we had troubles with too many requests we keep what we have and try again
			 * when cache is expired.
			 */
			if( $errors_occured )
			{
//				$this->clear_local_cache();
			}

			$this->update_cache();

			$this->add_to_updater_log( $api, $package_errors );
			return $updates;
		}


		/**
		 * Get download URL for our products from Envato
		 *
		 * @since 4.5.3
		 * @param array $options
		 * @return array
		 */
		public function handler_upgrader_package_options( array $options )
		{
			/**
			 * Ignore, if a package URL is already set - not our product
			 */
			if( ! empty( $options['package'] ) )
			{
				return $options;
			}


			$api = $this->get_update_source();
			if( ! $api instanceof Avia_Update_Source )
			{
				return $options;
			}

			/**
			 * At the moment we only have theme(s) to update
			 */
			if( empty( $options['hook_extra']['theme'] ) )
			{
				return $options;
			}

			$stylesheet = $options['hook_extra']['theme'];

			$purchases = avia_auto_updates::get_theme_keys();

			$product = null;
			foreach( $purchases as $theme => $purchase )
			{
				if( $purchase['item']['wordpress_theme_metadata']['stylesheet'] == $stylesheet )
				{
					$product = $purchase;
					break;
				}
			}

			if( is_null( $product ) )
			{
				return $options;
			}

			if( current_theme_supports( 'avia_envato_extended_log' ) )
			{
				$this->add_info_to_updater_log( $purchase['item']['wordpress_theme_metadata']['theme_name'] . ': ' . __( 'Envato API request for download URL started', 'avia_framework' )  );
			}

			$errors_occured = false;
			$package_errors = array();

			try
			{
				$options['package'] = $api->get_download_url( $product );
			}
			catch( Avia_Update_Source_Exception $ex )
			{
				$options['package'] = '';
				$errors_occured = true;
				$package_errors[] = $purchase['item']['wordpress_theme_metadata']['theme_name'] . ' - ' . __( 'Request for Download URL failed.', 'avia_framework' );
			}

			if( $errors_occured )
			{
				$this->add_to_updater_log( $api, $package_errors );
			}

			if( current_theme_supports( 'avia_envato_extended_log' ) )
			{
				$this->add_info_to_updater_log( $purchase['item']['wordpress_theme_metadata']['theme_name'] . ': ' . __( 'Envato API request for download URL finished', 'avia_framework' )  );
			}

			return $options;
		}


		/**
		 * Output the HTML below the verify input field
		 * Keep backwards comp with old API - but do not allow to enter new values
		 *
		 * @since 4.4.3
		 * @param string $new_token
		 * @param boolean $ajax
		 * @return string
		 */
		public function backend_html( $new_token, $ajax )
		{
			$new_token = trim( $new_token );

			$data = array(
					'updates_envato_token'			=> trim( avia_get_option( 'updates_envato_token' ) ),
					'updates_envato_token_state'	=> trim( avia_get_option( 'updates_envato_token_state' ) ),
					'updates_envato_verified_token'	=> trim( avia_get_option( 'updates_envato_verified_token' ) ),
					'updates_username'				=> trim( avia_get_option( 'updates_username' ) ),
					'updates_api_key'				=> trim( avia_get_option( 'updates_api_key' ) ),
					'updates_envato_info'			=> trim( avia_get_option( 'updates_envato_info' ) )
				);

			if( $ajax )
			{
				$data = $this->verify_token( $data, $new_token );
			}

			$notice = '';
			$deprecated = '';

			if( ! empty( $new_token ) )
			{
				$default = array(
								'purchases'	=> '',
								'username'	=> '',
								'email'		=> '',
								'errors'	=> ''
							);

				$arr_info = json_decode( $data['updates_envato_info'] );
				$arr_info = wp_parse_args( $arr_info, $default );


				$purchases = ! empty( $arr_info['purchases'] ) ? __( 'Your purchases', 'avia_framework' ) : __( 'Purchases could not be accessed', 'avia_framework' );
				$username = ! empty( $arr_info['username'] ) ? __( 'Your username: ', 'avia_framework' ) . $arr_info['username'] : __( 'Username could not be accessed (needed for your information only)', 'avia_framework' );
				$email = ! empty( $arr_info['email'] ) ? __( 'Your E-Mail: ', 'avia_framework' ) . $arr_info['email']  : __( 'E-Mail could not be accessed (needed for your information only)', 'avia_framework' );

				$error_msg = '';
				if( ! empty( $arr_info['errors'] ) )
				{
					$error_msg .=	'<p>';
					$error_msg .=		__( 'Following errors occurred:', 'avia_framework' );
					$error_msg .=	'</p>';
					$error_msg .=	'<ul>';
					foreach ( $arr_info['errors'] as $value )
					{
						$error_msg .=	'<li>' . $value . '</li>';
					}
					$error_msg .=	'</ul>';
				}

				if( ! empty( $data['updates_envato_token_state'] ) )
				{
					$warning = '';
					if( $ajax )
					{
						$data['updates_envato_verified_token'] = $new_token;
					}
					else if( $data['updates_envato_token'] != $data['updates_envato_verified_token'] )
					{
						$warning .=		'<div class="av-verification-cell av-privacy-token-notice av-update-token-changed" style="font-size: 1.5em;">';

						if( '' == $data['updates_envato_verified_token'] )
						{
							$warning .=		__( 'Please verify the key.', 'avia_framework' );
						}
						else
						{
							$warning .=		__( 'Please verify the key - the last verified key is different.', 'avia_framework' );
						}

						$warning .=		'</div>' ;
					}

					/**
					 * Two credentials, two screens.
					 *
					 * The bullets below are Envato concepts. A direct customer has
					 * no purchase list, no marketplace username and no marketplace
					 * e-mail, so showing them the Envato block reports two failures
					 * on a perfectly healthy licence. They get their licence state
					 * instead, which is information Envato could never give them.
					 *
					 * Seat usage is deliberately absent: SureCart does not enforce
					 * the activation limit and its counter reads zero even with
					 * activations present, so any number here would be false. See
					 * the tracker issue on activation limits.
					 *
					 * @since 8.1
					 */
					$notice .=	'<div class="av-text-notice">';
					$notice .=		$warning;

					if( isset( $arr_info['licence_status'] ) )
					{
						/**
						 * Remember the end date so the renewal reminder never has to ask
						 * for it. revokes_at is a fixed date rather than a moving state,
						 * so storing it here means no API call on an admin page load.
						 *
						 * @since 8.1
						 */
						Avia_Licence_Reminder::remember( $arr_info );

						$notice .=	$this->licence_notice_html( $arr_info, $data['updates_envato_token_state'] );
					}
					else
					{
						$notice .=		'<p>';
						$notice .=			sprintf( __( 'We checked the token on %s and we were able to connect to Envato and could access the following information:', 'avia_framework' ), $data['updates_envato_token_state'] );
						$notice .=		'</p>';
						$notice .=		'<ul>';
						$notice .=			'<li>' . $purchases . '</li>';
						$notice .=			'<li>' . $username . '</li>';
						$notice .=			'<li>' . $email . '</li>';
						$notice .=		'</ul>';
					}

					$notice .=		$error_msg;
					$notice .=	'</div>';

					if( ! isset( $arr_info['licence_status'] ) )
					{
						$notice .=	'<div class="av-verification-cell av-privacy-token-notice">';
						$notice .=		__( 'If you ever edit the restrictions of your personal token please re-validate it again to test if it works properly', 'avia_framework' );
						$notice .=	'</div>';
					}
				}
				else
				{
					$notice .=	'<div class="av-text-notice av-notice-error">';
					$notice .=		'<p>';
					/**
					 * The string carries no placeholder - dropping the unused
					 * argument rather than adding a %s, because changing the msgid
					 * would silently fall back to English for every translated site.
					 */
					$notice .=			__( 'Last time we checked the token we were not able to connected to Envato:', 'avia_framework' );
					$notice .=		'</p>';
					$notice .=		'<ul>';
					$notice .=			'<li>' . $purchases . '</li>';
					$notice .=			'<li>' . $username . '</li>';
					$notice .=			'<li>' . $email . '</li>';
					$notice .=		'</ul>';
					$notice .=		$error_msg;
					$notice .=	'</div>';
				}
			}

			/**
			 * Backwards compatibility (can be removed in future when Envato API < 3.0 is deprecated):
			 *
			 * Add a message to switch to new API and show old API access info.
			 *
			 * @since 4.4.3
			 */
			if( empty( $new_token ) && ! empty( $data['updates_username'] ) && ! empty( $data['updates_api_key'] ) )
			{
				$old_api = '';
				$info = '';

				$old_api .=		'<p class="av-text-notice av-notice-error av-notice-noborder">';
				$old_api .=			__( 'Attention: The old Envato API is deprecated and will be shut down soon. In order to be able to use automated theme updates please generate a new valid API token and enter it above. Your themeforest username and your old API key will then be removed from your installation since they are no longer required.', 'avia_framework' );
				$old_api .=		'</p>';

				$info .=	'<div class="avia_section avia_text">';
				$info .=		'<h4>' . __( 'Your Themeforest User Name:', 'avia_framework' ) . '</h4>';
				$info .=		'<div class="avia_control_container">';
				$info .=			'<div class="avia_control">';
				$info .=				'<div class="avia_style_wrap">';
				$info .=					'<input class="" value="' . $data['updates_username'] . '" readonly="readonly" type="text">';
				$info .=				'</div>';
				$info .=			'</div>';
				$info .=		'</div>';
				$info .=	'</div>';

				$info .=	'<div class="avia_section avia_text">';
				$info .=		'<h4>' . __( 'Your Themeforest API Key', 'avia_framework' ) . '</h4>';
				$info .=		'<div class="avia_control_container">';
				$info .=			'<div class="avia_control">';
				$info .=				'<div class="avia_style_wrap">';
				$info .=					'<input class="" value="' . $data['updates_api_key'] . '" readonly="readonly" type="text">';
				$info .=				'</div>';
				$info .=			'</div>';
				$info .=		'</div>';
				$info .=	'</div>';


				$deprecated .=	'<div class="avia_section avia_envato_deprecated-section">';
				$deprecated .=		$old_api;
				$deprecated .=		$info;
				$deprecated .=	'</div>';
			}

			$output  = 'avia_trigger_save ';
			$output .=	'<div class="av-verification-response-wrapper">';
			$output .=		$notice;
			$output .=		$deprecated;
			$output .=	'</div>';

			if( $ajax )
			{
				$response['html'] = $output;
				unset( $data['updates_envato_token'] );
				$response['update_input_fields'] = $data;
			}
			else
			{
				$response = $output;
			}

			return $response;
		}

		/**
		 * Check the given token with Envato API. Tries to access:
		 *		- purchases
		 *		- username
		 *		- email
		 *
		 * @since 4.4.3
		 * @param array $data
		 * @param string $new_token
		 * @return array
		 */
		protected function verify_token( array $data, $new_token )
		{
			$old_token = $data['updates_envato_token'];

			if( '' == $new_token )
			{
				$this->clear_updater_log();

				$data['updates_envato_token_state'] = '';
				$data['updates_envato_info'] = '';
				return $data;
			}

			if( $old_token != $new_token )
			{
				$this->clear_updater_log();
			}

			//	force a check for theme version when a new token or a revalidate of token
			$this->clear_cache();
			set_site_transient( 'update_themes', null );

			$api = $this->get_update_source( $new_token );

			/**
			 * The probing moved into the source, which knows what its own service
			 * can be asked. The order it probes in is load-bearing - errors
			 * accumulate in that order and are listed in that order on the option
			 * page - so it is documented there rather than reproduced here.
			 *
			 * @since 8.1
			 */
			$info = $api->verify_credential();

			$errors = $api->get_errors();
			if( $errors instanceof WP_Error )
			{
				$info['errors'] = $errors->get_error_messages();
			}

			$data['updates_envato_token'] = $new_token;
			$data['updates_envato_token_state'] = ! empty( $info['purchases'] ) ? date( 'Y/m/d H:i' ) : '';
			$data['updates_envato_info']  = json_encode( $info );

			/**
			 * Remove deprecated data
			 */
			$data['updates_username'] = '';
			$data['updates_api_key'] = '';

			return $data;
		}


		/**
		 * Returns the transient logfile name depending on theme name
		 *
		 * @since 4.4.3
		 * @return string
		 */
		public function get_logfile_name()
		{
			if( empty( $this->transient_logfile_name ) )
			{
				$this->transient_logfile_name = apply_filters( 'avf_theme_updater_transient_logfile_name', '_av_' . avia_auto_updates::get_themename( 'parent' ) . '_updater_log' );
			}

			return Avia_Theme_Updater::validate_transient( $this->transient_logfile_name );
		}

		/**
		 * Returns the transient logfile name depending on theme name
		 *
		 * @since 4.5.2
		 * @return string
		 */
		public function get_cache_name()
		{
			if( empty( $this->transient_cache_name ) )
			{
				$this->transient_cache_name = apply_filters( 'avf_theme_updater_transient_cache_name', '_av_' . avia_auto_updates::get_themename( 'parent' ) . '_updater_cache' );
			}

			return Avia_Theme_Updater::validate_transient( $this->transient_cache_name );
		}


		/**
		 * Tries to read cache from transient and stores it in local array
		 *
		 * @since 4.5.2
		 * @return boolean				true, if cache exists
		 */
		protected function get_cache()
		{
			static $force_check_executed = false;

			if( is_array( $this->envato_results_cache ) )
			{
				return true;
			}

			/**
			 * From theme option page we want to force a check
			 */
			if( isset( $_REQUEST['force-check'] ) && ! $force_check_executed )
			{
				$force_check_executed = true;
				return false;
			}

			$transient = $this->get_cache_name();
			$cache = get_transient( $transient );

			if( false === $cache || ! is_array( $cache ) )
			{
				return false;
			}

			$this->envato_results_cache = $cache;
			return true;
		}

		/**
		 * Add update info to internal cache array
		 *
		 * @since 4.5.2
		 * @param string $theme_name
		 * @param array $update
		 */
		protected function add_to_cache( $theme_name, $update )
		{
			if( ! is_array( $this->envato_results_cache ) )
			{
				$this->envato_results_cache = array();
			}

			$this->envato_results_cache[ $theme_name ] = $update;
		}

		/**
		 * Clears the local cache
		 *
		 * @since 4.5.2
		 */
		protected function clear_local_cache()
		{
			unset( $this->envato_results_cache );
			$this->envato_results_cache = null;
		}

		/**
		 * Saves cache to transient
		 *
		 * @since 4.5.2
		 * @param boolean $set_locale
		 */
		protected function update_cache( $set_locale = false )
		{
			/**
			 * We block update requests in any case to limit Envato API calls for download URL's !!
			 */
			$cache = is_array( $this->envato_results_cache ) ? $this->envato_results_cache : array();

			if( $set_locale )
			{
				$this->envato_results_cache = $cache;
			}

			$transient = $this->get_cache_name();
			$timeout = apply_filters( 'avf_updater_cache_timeout', 12 * HOUR_IN_SECONDS );

			return set_transient( $transient, $cache, $timeout );
		}

		/**
		 * Clears local and saved cache and removes the transient
		 *
		 * @since 4.5.2
		 */
		protected function clear_cache()
		{
			$this->clear_local_cache();

			$transient = $this->get_cache_name();
			delete_transient( $transient );
		}

		/**
		 * Returns the stored transient or an empty array
		 *
		 * @since 4.4.3
		 * @return array
		 */
		public function get_updater_log()
		{
			$transient = $this->get_logfile_name();
			$log = get_transient( $transient );
			return ( false !== $log ) ? $log : array();
		}

		/**
		 * Updates the transient
		 *
		 * @since 4.4.3
		 * @param array $log
		 * @return boolean
		 */
		protected function update_updater_log( array $log )
		{
			$transient = $this->get_logfile_name();
			$timeout = apply_filters( 'avf_updater_log_timeout', MONTH_IN_SECONDS );

			return set_transient( $transient, $log, $timeout );
		}

		/**
		 * Reset transient to an empty array
		 *
		 * @since 4.4.3
		 * @return boolean
		 */
		protected function clear_updater_log()
		{
			return $this->update_updater_log( array() );
		}


		/**
		 * A link to renew, pointed at whichever place can actually help.
		 *
		 * A lapsed licence and a soon-to-lapse one need different destinations -
		 * one can restore and keep its key, the other can only buy a new one. The
		 * level decides; see Avia_Licence_Reminder's two URL constants.
		 *
		 * @since 8.1
		 * @param string $level			an Avia_Licence_Reminder LEVEL_* constant
		 * @return string
		 */
		protected function renew_link_html( $level = '' )
		{
			$url = Avia_Licence_Reminder::renew_url( $level );

			if( '' === $url )
			{
				return '';
			}

			return ' <a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . __( 'Renew your licence', 'avia_framework' ) . '</a>';
		}

		/**
		 * What a direct customer sees after checking their licence key.
		 *
		 * Says what their licence actually is - valid, expired or revoked - and
		 * when the update period ends, which is information a ThemeForest buyer
		 * never had. Nothing here disables anything: an expired licence stops
		 * updates, the theme keeps working, and the copy says so, because the
		 * moment a customer believes their site is at risk we have lost them
		 * whether or not it is true.
		 *
		 * Seat usage is deliberately not shown - see the note at the call site.
		 *
		 * @since 8.1
		 * @param array $info				as returned by verify_credential()
		 * @param string $checked_on		timestamp of the last verification
		 * @return string
		 */
		protected function licence_notice_html( array $info, $checked_on )
		{
			$status = $info['licence_status'] ?? '';
			$revokes_at = $info['revokes_at'] ?? null;
			$days = $info['days_remaining'] ?? null;

			$date = is_null( $revokes_at ) ? '' : date_i18n( get_option( 'date_format' ), (int) $revokes_at );

			$html = '<p>';

			if( 'valid' === $status )
			{
				$html .= sprintf( __( 'We checked your licence on %s and it is active.', 'avia_framework' ), $checked_on );
			}
			else if( 'expired' === $status )
			{
				/**
				 * An expired licence cannot be restored, so renewing means a new
				 * purchase and a new key. Said here rather than discovered after
				 * paying, and pointed at somewhere they can actually buy.
				 */
				$html .= __( 'Your update period has ended. Enfold keeps working, and you can renew for another year of updates and support. You will receive a new licence key to enter.', 'avia_framework' );
				$html .= $this->renew_link_html( Avia_Licence_Reminder::LEVEL_ENDED );
			}
			else if( 'revoked' === $status )
			{
				/**
				 * Revoked is a refund or a chargeback rather than a lapse, so no
				 * renewal link - somebody who asked for their money back should not
				 * be invited to buy again in the same breath.
				 */
				$html .= __( 'This licence is no longer active. Enfold keeps working, but it will not receive updates.', 'avia_framework' );
			}
			else
			{
				$html .= __( 'We could not read the state of this licence.', 'avia_framework' );
			}

			$html .= '</p>';

			if( 'valid' === $status && '' !== $date )
			{
				$html .= '<ul>';
				$html .= '<li>' . sprintf( __( 'Updates and support until %s', 'avia_framework' ), $date ) . '</li>';

				/**
				 * Only mentioned once it is close enough to act on. A year of
				 * runway does not need a countdown, and showing one turns a
				 * reassuring screen into a nagging one.
				 */
				if( ! is_null( $days ) && $days <= 30 )
				{
					$html .= '<li>' . sprintf( _n( '%s day remaining', '%s days remaining', (int) max( 0, $days ), 'avia_framework' ), number_format_i18n( max( 0, $days ) ) ) . '</li>';
				}

				$html .= '</ul>';
			}
			else if( 'expired' === $status && '' !== $date )
			{
				$html .= '<ul><li>' . sprintf( __( 'Your update period ended on %s', 'avia_framework' ), $date ) . '</li></ul>';
			}

			return $html;
		}

		/**
		 * Note a plain progress message in the updater log.
		 *
		 * Six call sites used to instantiate an Avia_Envato_Exception purely to
		 * carry a string into the log, which read as an error being thrown when
		 * nothing had gone wrong.
		 *
		 * It routes through add_to_updater_log() rather than appending directly, so
		 * that it cannot miss the trimming below - the extended log keeps 500
		 * entries, and a path that skipped the cap would grow the transient without
		 * bound on exactly the sites that switched extended logging on.
		 *
		 * @since 8.1
		 * @param string $message
		 * @return boolean
		 */
		protected function add_info_to_updater_log( $message )
		{
			return $this->add_to_updater_log( (string) $message );
		}

		/**
		 * Adds an update message to the queue and removes the oldest if necessary
		 *
		 * @since 4.4.3
		 * @since 8.1				accepts an Avia_Update_Source, and a plain string
		 * @param Avia_Update_Source|Avia_Envato_Exception|string  $info
		 * @param array $package_errors
		 * @param string $clear_errors									'clear_errors' | 'no_clear_errors'
		 * @return boolean
		 */
		protected function add_to_updater_log( $info, $package_errors = null, $clear_errors = 'clear_errors' )
		{
			$log = $this->get_updater_log();

			$entries = ! current_theme_supports( 'avia_envato_extended_log' ) ? 20 : 500;
			$max_entries = apply_filters( 'avf_updater_log_max_entries', $entries );

			if( count( $log ) >= $max_entries )
			{
				$log = array_slice( $log, count( $log ) - $max_entries + 1 );
			}

			if( $info instanceof Avia_Update_Source )
			{
				$entry = array(
								'time'		=> date( 'Y/m/d H:i' ),
								'errors'	=> array(),
							);

				$errors = $info->get_errors();
				if( $errors instanceof WP_Error )
				{
					$entry['errors'] = $errors->get_error_messages();
				}

				if( is_array( $package_errors ) )
				{
					$entry['package_errors'] = $package_errors;
				}

				$log[] = $entry;

				if( 'clear_errors' == $clear_errors )
				{
					$info->clear_errors();
				}
			}
			else if( $info instanceof Avia_Envato_Exception )
			{
				$log[] = array(
							'time'		=> date( 'Y/m/d H:i' ),
							'info'		=> $info->getMessage()
						);
			}
			else if( is_string( $info ) )
			{
				/**
				 * Byte identical to the exception branch above - the entry shape is
				 * what backend_html() renders, and it must not change.
				 *
				 * @since 8.1
				 */
				$log[] = array(
							'time'		=> date( 'Y/m/d H:i' ),
							'info'		=> $info
						);
			}

			return $this->update_updater_log( $log );
		}

		/**
		 * Helper function to validate transient ID's.
		 *
		 * @since 4.4.3
		 * @param string $transient
		 * @return string
		 */
		static public function validate_transient( $transient = '' )
		{
		  return preg_replace( '/[^A-Za-z0-9\_\-]/i', '', str_replace( ':', '_', $transient ) );
		}

	}

	/**
	 * Get the only instance of this class
	 *
	 * @since 4.4.3
	 * @return Avia_Theme_Updater
	 */
	function AviaThemeUpdater( array $args = array() )
	{
		return Avia_Theme_Updater::instance( $args );
	}
}

if( ! class_exists( 'Avia_Envato_Exception', false ) )
{
	/**
	 * Simple base class to allow use of try / catch blocks
	 *
	 * @since 4.4.3
	 * @since 8.1				extends Avia_Update_Source_Exception, so the updater
	 *							can catch failures from any source while every
	 *							existing catch of this class keeps working
	 */
	class Avia_Envato_Exception extends Avia_Update_Source_Exception
	{

		/**
		 *
		 * @since 4.4.3
		 * @param string $message
		 * @param int $code
		 * @param \Throwable $previous
		 */
		public function __construct( $message = "", $code = 0, $previous = null )
		{
			parent::__construct( $message, $code, $previous );
		}

	}
}


