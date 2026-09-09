<?php
/**
 * Turns a failed SureCart response into the sentence a customer reads.
 *
 * Same split as the Envato side: the API class does the HTTP, this decides the
 * wording, so the messages that explain why updates stopped can be tested
 * without a network.
 *
 * Shapes below were taken from real responses on 2026-08-19, not from docs:
 *   401 {"code":"unauthorized","message":"Invalid API token provided."}
 *   404 {"code":"not_found", …}
 *   422 {"code":"media.invalid","message":"Failed to save media",
 *        "validation_errors":[{"message":"File can't be blank", …}]}
 *
 * @since 8.1
 */

if( ! defined( 'ABSPATH' ) ) { exit; }		// Exit if accessed directly


if( ! class_exists( 'Avia_SureCart_Error_Translator', false ) )
{
	class Avia_SureCart_Error_Translator
	{
		/**
		 * @since 8.1
		 */
		const CODE_API = 'SureCart API Error';
		const CODE_LICENCE = 'SureCart Licence';

		/**
		 * @since 8.1
		 * @param int|string $code			HTTP status
		 * @param array|null $body			decoded response body
		 * @param string $prefix			which request failed
		 * @return array					array( error code => message )
		 */
		static public function translate( $code, $body = array(), $prefix = '' )
		{
			$body = is_array( $body ) ? $body : array();
			$prefix = '' === $prefix ? '' : $prefix . ' ';

			/**
			 * A 404 on a licence route means the key is not one of ours. Said
			 * plainly, because it is the one message a customer can act on.
			 */
			if( 404 == $code )
			{
				return array( self::CODE_LICENCE => $prefix . __( 'This licence key was not found. Please check it and try again.', 'avia_framework' ) );
			}

			/**
			 * 401 here is almost never the customer's fault - the licence routes
			 * authenticate with OUR store token, so a rejection points at our
			 * configuration rather than at their key. Saying "invalid licence"
			 * would send them hunting for a problem they cannot fix.
			 */
			if( 401 == $code || 403 == $code )
			{
				return array( self::CODE_API => $prefix . __( 'The update service rejected our store credentials. This is a problem on our side - please contact support.', 'avia_framework' ) );
			}

			if( 429 == $code )
			{
				return array( self::CODE_API => $prefix . __( 'Too many update requests were made. Please try again in a few minutes.', 'avia_framework' ) );
			}

			$detail = self::detail( $body );

			if( '' !== $detail )
			{
				return array( self::CODE_API => $prefix . sprintf( __( 'Errorcode %s returned by SureCart: %s', 'avia_framework' ), $code, $detail ) );
			}

			return array( self::CODE_API => $prefix . sprintf( __( 'Errorcode %s returned by SureCart.', 'avia_framework' ), $code ) );
		}

		/**
		 * The most specific thing the body has to say.
		 *
		 * Validation errors are preferred over the generic message: "Failed to
		 * save media" tells nobody anything, while "File can't be blank" is the
		 * actual reason.
		 *
		 * @since 8.1
		 * @param array $body
		 * @return string
		 */
		static protected function detail( array $body )
		{
			if( ! empty( $body['validation_errors'] ) && is_array( $body['validation_errors'] ) )
			{
				$messages = array();

				foreach( $body['validation_errors'] as $error )
				{
					if( is_array( $error ) && ! empty( $error['message'] ) )
					{
						$messages[] = $error['message'];
					}
				}

				if( ! empty( $messages ) )
				{
					return implode( ', ', $messages );
				}
			}

			return ! empty( $body['message'] ) && is_string( $body['message'] ) ? $body['message'] : '';
		}
	}
}
