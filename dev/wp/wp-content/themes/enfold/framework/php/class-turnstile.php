<?php
/**
 * Base class to handle Cloudflare Turnstile API functionality.
 *
 * @tutorial https://developers.cloudflare.com/turnstile/
 * @since 7.1.7
 */
if( ! defined('AVIA_FW') ) { exit( 'No direct script access allowed' ); }


if( ! class_exists( 'av_turnstile', false ) )
{
	class av_turnstile extends aviaFramework\base\object_properties
	{
		const API_URL = 'https://challenges.cloudflare.com/turnstile/v0/api.js';
		const API_VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

		/**
		 *
		 * @since 7.1.7
		 * @var av_turnstile
		 */
		static protected $_instance = null;

		/**
		 *
		 * @since 7.1.7
		 * @var string
		 */
		protected $site_key;

		/**
		 *
		 * @since 7.1.7
		 * @var string
		 */
		protected $secret_key;

		/**
		 * Stores the last verified keys (user might change keys and save without verifying)
		 *
		 * @since 7.1.7
		 * @var string					'' | 'string combination to verify' | 'verify_error'
		 */
		protected $verified_keys;

		/**
		 *
		 * @since 7.1.7
		 * @var boolean|null
		 */
		protected $loading_prohibited;

		/**
		 * Return the instance of this class
		 *
		 * @since 7.1.7
		 * @return av_turnstile
		 */
		static public function instance()
		{
			if( is_null( av_turnstile::$_instance ) )
			{
				av_turnstile::$_instance = new av_turnstile();
			}

			return av_turnstile::$_instance;
		}

		/**
		 * @since 7.1.7
		 */
		protected function __construct()
		{
			$this->loading_prohibited = null;
			$this->site_key = '';
			$this->secret_key = '';
			$this->verified_keys = '';

			//	needed because not all framework functions are loaded at this point
			add_action( 'after_setup_theme', array( $this, 'handler_after_setup_theme' ), 10 );

			add_action( 'wp_enqueue_scripts', array( $this, 'handler_wp_enqueue_scripts' ), 500 );
			add_action( 'admin_enqueue_scripts', array( $this, 'handler_wp_admin_enqueue_scripts' ), 500 );

			//	We hook with low priority as Turnstile verification should overrule other positive checks
			add_filter( 'avf_form_send', array( $this, 'handler_avf_form_send' ), 999999, 4 );
		}

		/**
		 * @since 7.1.7
		 */
		public function __destruct()
		{
			unset( $this->site_key );
			unset( $this->secret_key );
			unset( $this->verified_keys );
		}

		/**
		 * @since 7.1.7
		 */
		public function handler_after_setup_theme()
		{
			$this->site_key = avia_get_option( 'avia_turnstile_pkey', '' );
			$this->secret_key = avia_get_option( 'avia_turnstile_skey', '' );
			$this->verified_keys = avia_get_option( 'avia_turnstile_verify_state', '' );
		}

		/**
		 * In backend we must enqueue the js callback file so the "Check API Keys" button works.
		 *
		 * Registered as its own dedicated handle rather than hooking into the shared
		 * admin form-elements script, since it defines its own `avia_callback` entry
		 * and only needs to be present (in any order) before the button is clicked.
		 *
		 * @since 7.1.7
		 */
		public function handler_wp_admin_enqueue_scripts()
		{
			$vn = avia_get_theme_version();

			//	no minified build is shipped for this file - always load the plain version, otherwise
			//	"Merge & Compress JS/CSS Files: minified only" causes a 404 and the check button silently breaks
			wp_enqueue_script( 'avia_turnstile_admin_script', AVIA_JS_URL . "conditional_load/avia_turnstile_admin.js", array( 'jquery' ), $vn, true );

			$args = array(
					'api'				=> apply_filters( 'avf_turnstile_apiurl', av_turnstile::API_URL ),
					'invalid_keys'		=> __( 'You have to enter a site key and a secret key to verify it.', 'avia_framework' ),
					'api_load_error'	=> __( 'Cloudflare Turnstile API could not be loaded. We are not able to verify keys. Check your internet connection and try again.', 'avia_framework' ),
					'timeout'			=> __( 'A network timeout problem occurred. Please recheck the keys and try again.', 'avia_framework' ),
				);

			wp_localize_script( 'avia_turnstile_admin_script', 'AviaTurnstileData', $args );
		}

		/**
		 * Loads the Cloudflare Turnstile API script on the frontend.
		 * The widget itself is rendered implicitly by Cloudflare's script wherever
		 * it finds a `cf-turnstile` class - no explicit JS render call is needed.
		 *
		 * @since 7.1.7
		 */
		public function handler_wp_enqueue_scripts()
		{
			if( $this->is_loading_prohibited() )
			{
				return;
			}

			$vn = avia_get_theme_version();

			/**
			 * @since 7.1.7
			 * @param string $api_url
			 * @return string
			 */
			$api_url = apply_filters( 'avf_turnstile_apiurl', av_turnstile::API_URL );

			wp_enqueue_script( 'avia_turnstile_api_script', $api_url, array(), $vn, true );
		}

		/**
		 * Returns the site key
		 *
		 * @since 7.1.7
		 * @return string
		 */
		public function get_site_key()
		{
			return $this->site_key;
		}

		/**
		 * Returns the secret key
		 *
		 * @since 7.1.7
		 * @return string
		 */
		public function get_secret_key()
		{
			return $this->secret_key;
		}

		/**
		 *
		 * @since 7.1.7
		 * @return boolean
		 */
		public function is_loading_prohibited()
		{
			if( is_null( $this->loading_prohibited ) )
			{
				$prohibited = ! $this->is_activated() || ! $this->are_keys_set();

				/**
				 * Filter allows to suppress loading of script on desired pages
				 *
				 * @since 7.1.7
				 * @param boolean
				 * @return boolean
				 */
				$prohibited = apply_filters( 'avf_load_turnstile_api_prohibited', $prohibited );

				$this->loading_prohibited = is_bool( $prohibited ) ? $prohibited : true;
			}

			return $this->loading_prohibited;
		}

		/**
		 * Checks if Turnstile is activated in Theme Options
		 *
		 * @since 7.1.7
		 * @return boolean
		 */
		public function is_activated()
		{
			return 'avia_turnstile_active' == avia_get_option( 'avia_turnstile_active', '' );
		}

		/**
		 * Checks if both site and secret keys are set
		 *
		 * @since 7.1.7
		 * @return boolean
		 */
		public function are_keys_set()
		{
			return ! empty( $this->get_site_key() ) && ! empty( $this->get_secret_key() );
		}

		/**
		 * Allow admins to see more detailed error messages
		 *
		 * @since 7.1.7
		 * @return boolean
		 */
		protected function show_extended_errors()
		{
			$show_extended_errors = current_user_can( 'manage_options' );

			/**
			 * @since 7.1.7
			 * @param boolean
			 * @return boolean				return true to show extended messages
			 */
			return apply_filters( 'avf_turnstile_show_extended_error_messages', $show_extended_errors );
		}

		/**
		 * Verify a token with Cloudflare Turnstile.
		 *
		 * @since 7.1.7
		 * @param string $token
		 * @param string|null $secretkey			if null -> use the saved secret key
		 * @return array|WP_Error					check $result['success'] = true
		 */
		public function verify_token( $token, $secretkey = null )
		{
			if( is_null( $secretkey ) )
			{
				$secretkey = $this->get_secret_key();
			}

			$params = array(
						'body' => array(
										'secret'   => $secretkey,
										'response' => $token,
										'remoteip' => $_SERVER['REMOTE_ADDR'],
									)
						);

			$response = wp_safe_remote_post( av_turnstile::API_VERIFY_URL, $params );

			if( $response instanceof WP_Error )
			{
				$msg = $response->get_error_messages();
				$msg = implode( '<br />', $msg );

				return new WP_Error( 'site_down', sprintf( __( 'Unable to communicate with Cloudflare Turnstile: <br /><br />%s', 'avia_framework' ), $msg ) );
			}

			$code = wp_remote_retrieve_response_code( $response );
			if( 200 != $code )
			{
				$msg = wp_remote_retrieve_response_message( $response );
				if( empty( $msg ) )
				{
					$msg = __( 'Unknown error code', 'avia_framework' );
				}

				//	Cloudflare puts the actual reason in the JSON body ("error-codes"), even on non-200 responses - surface it if present
				$body = wp_remote_retrieve_body( $response );
				$decoded = json_decode( $body, true );

				if( ! empty( $decoded['error-codes'] ) && is_array( $decoded['error-codes'] ) )
				{
					$msg .= ' - ' . implode( ', ', $decoded['error-codes'] );
				}

				return new WP_Error( 'invalid_response', sprintf( __( 'Cloudflare Turnstile returned error %d (= %s).', 'avia_framework' ), $code, $msg ) );
			}

			$body = wp_remote_retrieve_body( $response );
			$result = json_decode( $body, true );

			return $result;
		}

		/**
		 * Check if we have a valid token on form submission.
		 *
		 * @since 7.1.7
		 * @param boolean $proceed
		 * @param array $new_post
		 * @param array $form_params
		 * @param avia_form $form_class
		 * @return boolean|null						true if you want to continue | null for error message
		 */
		public function handler_avf_form_send( $proceed, array $new_post, array $form_params, avia_form $form_class )
		{
			$use_turnstile = false;

			foreach( $form_class->form_elements as $element )
			{
				if( isset( $element['type'] ) && ( 'turnstile' == $element['type'] ) )
				{
					$use_turnstile = $element;
					break;
				}
			}

			if( false === $use_turnstile )
			{
				return $proceed;
			}

			if( true !== $proceed )
			{
				return $proceed;
			}

			$show_extended_errors = $this->show_extended_errors();
			$token = isset( $use_turnstile['token_input'] ) ? $use_turnstile['token_input'] : 'cf-turnstile-response';
			$token_value = isset( $_REQUEST[ $token ] ) ? trim( $_REQUEST[ $token ] ) : '';

			$reload_msg = '<br />' . __( 'Form could not be submitted. Please reload page and try again.', 'avia_framework' );

			if( empty( $token_value ) )
			{
				$form_class->submit_error .= __( 'Invalid form for Turnstile sent.', 'avia_framework' ) . $reload_msg;
				if( $show_extended_errors )
				{
					$form_class->submit_error .= '<br />' . __( 'Turnstile response token was missing.', 'avia_framework' );
				}
				return null;
			}

			$check = $this->verify_token( $token_value );

			if( $check instanceof WP_Error )
			{
				$form_class->submit_error .= __( 'Sorry, but the verification failed. Please reload the page and try again.', 'avia_framework' ) . $reload_msg;
				if( $show_extended_errors )
				{
					$form_class->submit_error .= '<br />' . $check->get_error_message();
				}
				return null;
			}

			if( empty( $check['success'] ) )
			{
				$form_class->submit_error .= __( 'Sorry, but the verification failed. Please reload the page and try again.', 'avia_framework' ) . $reload_msg;
				if( $show_extended_errors && ! empty( $check['error-codes'] ) )
				{
					$form_class->submit_error .= '<br />' . implode( '<br />', (array) $check['error-codes'] );
				}
				return null;
			}

			return $proceed;
		}

		/**
		 * Returns the last saved verified key string: "site_key secret_key"
		 *
		 * @since 7.1.7
		 * @return string
		 */
		public function get_verified_keys()
		{
			return $this->verified_keys;
		}

		/**
		 * Output options page backend HTML or perform the key verification and return HTML message
		 *
		 * @since 7.1.7
		 * @param string $api_key				unused - kept for parity with the generic ajax verify callback signature
		 * @param boolean $ajax
		 * @param array|boolean|null $check_keys
		 * @param array $element
		 * @return string|array
		 */
		public function backend_html( $api_key = '', $ajax = true, $check_keys = false, $element = array() )
		{
			$return = array(
							'html'                 => '',
							'update_input_fields'  => array()
						);

			$valid_key = false;

			$response_text  = __( 'Could not connect and verify these API Keys with Cloudflare Turnstile.', 'avia_framework' );
			$response_class = 'av-notice-error';

			$content_default  =			'<h4>' . esc_html__( 'Troubleshooting:', 'avia_framework' ) . '</h4>';
			$content_default .=			'<ol>';
			$content_default .=				'<li>';
			$content_default .=					esc_html__( 'Check if you typed the keys correctly.', 'avia_framework' );
			$content_default .=				'</li>';
			$content_default .=				'<li>';
			$content_default .=					esc_html__( 'Check that the domain you are testing on is listed in the Turnstile widget settings on the Cloudflare dashboard.', 'avia_framework' );
			$content_default .=				'</li>';
			$content_default .=				'<li>';
			$content_default .=					esc_html__( 'If none of this helps: deactivate all plugins and then check if the API works by using the button above. If thats the case then one of your plugins is interfering.', 'avia_framework' );
			$content_default .=				'</li>';
			$content_default .=			'</ol>';

			if( $ajax )
			{
				/**
				 * called by user pressing the ajax check button
				 */
				$token = isset( $check_keys['token'] ) ? trim( $check_keys['token'] ) : '';
				$sitekey = isset( $check_keys['sitekey'] ) ? trim( $check_keys['sitekey'] ) : '';
				$secretkey = isset( $check_keys['secretkey'] ) ? trim( $check_keys['secretkey'] ) : '';

				$check = $this->verify_token( $token, $secretkey );

				if( ! ( $check instanceof WP_Error ) && ! empty( $check['success'] ) )
				{
					$valid_key = true;
					$response_class = '';
					$response_text  = __( 'We were able to properly connect and verify your API keys with Cloudflare Turnstile', 'avia_framework' );

					//will be stripped from the final output but tells the ajax script to save the page after the check was performed
					$response_text .= ' avia_trigger_save';

					$return['update_input_fields']['avia_turnstile_verify_state'] = "{$sitekey} {$secretkey}";
				}
				else
				{
					$content_default = '';

					if( $check instanceof WP_Error )
					{
						$response_text = $check->get_error_message();
					}
					else
					{
						$msg = '';
						if( ! empty( $check['error-codes'] ) && is_array( $check['error-codes'] ) )
						{
							$msg = implode( '<br />', $check['error-codes'] );
						}

						$response_text = __( 'Error on connecting to Cloudflare Turnstile - please retry.', 'avia_framework' );
						if( ! empty( $msg ) )
						{
							$response_text .= '<br /><br />' . $msg;
						}
					}

					$return['update_input_fields']['avia_turnstile_verify_state'] = 'verify_error';
				}
			}
			else
			{
				/**
				 * called on a normal page load. in this case we either show the stored result or if we got no stored result we show nothing
				 */
				$check = "{$this->get_site_key()} {$this->get_secret_key()}";

				if( $this->get_verified_keys() == $check )
				{
					$valid_key = true;
				}
				else if( '' == $this->get_site_key() && '' == $this->get_secret_key() )
				{
					$response_class = '';
					$response_text = '';
				}
				else if( 'verify_error' == $this->get_verified_keys() )
				{
					$response_text = __( 'A connection error occurred last time we tried to verify your keys with Cloudflare Turnstile - please revalidate the keys.', 'avia_framework' );
				}
				else if( '' == $this->get_verified_keys() )
				{
					$response_text = __( 'Please verify the keys', 'avia_framework' );
				}
				else
				{
					$response_text = __( 'Please verify the keys - the last verified keys are different.', 'avia_framework' );
				}

				$content_default = '';

				if( $valid_key )
				{
					$response_class = '';
					$response_text  = __( 'Last time we checked we were able to connect to Cloudflare Turnstile with your API keys', 'avia_framework' );
				}
			}

			if( $valid_key )
			{
				$content_default = __( 'If you ever change your API key please verify the key here again, to test if it works properly', 'avia_framework' );
			}

			$output = '';

			if( ! empty( $response_text ) )
			{
				$output  =	"<div class='av-verification-response-wrapper'>";
				$output .=		"<div class='av-text-notice {$response_class}'>";
				$output .=			$response_text;
				$output .=		"</div>";
				$output .=		"<div class='av-verification-cell'>{$content_default}</div>";
				$output .=	"</div>";
			}

			if( $ajax )
			{
				$return['html'] = $output;
			}
			else
			{
				$return = $output;
			}

			return $return;
		}

	}

	/**
	 * Returns the main instance of av_turnstile to prevent the need to use globals.
	 *
	 * @since 7.1.7
	 * @return av_turnstile
	 */
	function Avia_Turnstile()
	{
		return av_turnstile::instance();
	}
}

Avia_Turnstile();


if( ! function_exists( 'av_turnstile_api_check' ) )
{
	/**
	 * Callback function:
	 *		- ajax callback from verification button
	 *		- php callback when creating output on option page
	 *
	 * @since 7.1.7
	 * @param string $value
	 * @param boolean $ajax
	 * @param array|null $js_value
	 * @param array $element
	 * @return string|array
	 */
	function av_turnstile_api_check( $value, $ajax = true, $js_value = null, $element = array() )
	{
		$api = Avia_Turnstile();
		return $api->backend_html( $value, $ajax, $js_value, $element );
	}
}
