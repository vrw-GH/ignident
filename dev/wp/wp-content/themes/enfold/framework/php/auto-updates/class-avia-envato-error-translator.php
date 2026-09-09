<?php
/**
 * Turns a failed Envato response into the sentence a customer reads.
 *
 * Whatever this produces ends up on the theme options page and in the updater
 * log, and it is the customer's only explanation for why updates stopped. The
 * difference between "your private token is invalid" and a bare error code is
 * the difference between a fix they can make themselves and a support ticket.
 *
 * Extracted from Avia_Envato_Base_API::add_envato_api_error(), which interleaved
 * four WordPress response accessors with the string building. The accessors stay
 * in the API class where the response object lives; only the decision moved. That
 * split is what makes these messages testable without reimplementing WP_Error.
 *
 * @since 8.1
 */

if( ! defined( 'ABSPATH' ) ) { exit; }		// Exit if accessed directly


if( ! class_exists( 'Avia_Envato_Error_Translator', false ) )
{
	class Avia_Envato_Error_Translator
	{
		/**
		 * @since 8.1
		 */
		const CODE_API = 'Envato API Error';

		/**
		 * The expired-session case is filed separately because backend_html()
		 * groups the log by error code, and a login problem is not an API fault.
		 *
		 * @since 8.1
		 */
		const CODE_LOGIN = 'Envato Login';

		/**
		 * @since 8.1
		 * @param int|string $code				HTTP status code
		 * @param string $message				HTTP status message
		 * @param array|null $body				decoded response body
		 * @param string $prefix				which request failed, e.g. 'Purchases:'
		 * @param string|null $retry_after		value of the Retry-After header, if any
		 * @return array						array( error code => message )
		 */
		static public function translate( $code, $message, $body = array(), $prefix = '', $retry_after = null )
		{
			if( 401 == $code )
			{
				return array( self::CODE_API => $prefix . ' ' . __( 'Your private token is invalid.', 'avia_framework' ) );
			}

			if( 429 == $code )
			{
				return array( self::CODE_API => self::rate_limit( $prefix, $retry_after ) );
			}

			$report = sprintf( __( 'Errorcode %s returned by Envato: %s', 'avia_framework' ), $code, $message );

			if( ! empty( $prefix ) )
			{
				$report = $prefix . ' ' . $report;
			}

			if( ! is_array( $body ) || ! isset( $body['error'] ) )
			{
				return array( self::CODE_API => $report );
			}

			if( 'invalid_grant' == $body['error'] )
			{
				return array( self::CODE_LOGIN => __( 'The valid access time to Envato has expired. Please login again with your Envato Username. Thank you.', 'avia_framework' ) );
			}

			unset( $body['error'] );

			/**
			 * A body carrying nothing but 'error' stops here - which is also why
			 * the 404 hint below never appears for one. Preserved deliberately.
			 */
			if( empty( $body ) )
			{
				return array( self::CODE_API => $report );
			}

			foreach( $body as $key => $value )
			{
				$body[ $key ] = $key . ': ' . $value;
			}

			$report .= ':<br />- ' . implode( '<br />- ', $body );

			if( 404 == $code )
			{
				$report .= ':<br />- ' . __( 'Possible cause: your download limit might be exceeded - please try again later.', 'avia_framework' );
			}

			return array( self::CODE_API => $report );
		}

		/**
		 * Envato's rate limit is shared across every Enfold install, so this is a
		 * message customers really see.
		 *
		 * @since 8.1
		 * @param string $prefix
		 * @param string|null $retry_after
		 * @return string
		 */
		static protected function rate_limit( $prefix, $retry_after )
		{
			if( ! empty( $retry_after ) && is_numeric( $retry_after ) )
			{
				$report = $prefix . ' ' . sprintf( __( 'Envato Rate Limit exceeded - Requests are blocked for %d seconds.', 'avia_framework' ), $retry_after );
			}
			else
			{
				$report = $prefix . ' ' . __( 'Envato Rate Limit for requests exceeded.', 'avia_framework' );
			}

			return $report . ' ' . __( 'We are unable to get the download URL for your products.', 'avia_framework' );
		}
	}
}
