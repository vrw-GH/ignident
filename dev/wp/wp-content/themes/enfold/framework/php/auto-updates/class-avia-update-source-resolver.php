<?php
/**
 * Decides which stored credential drives the updater.
 *
 * Today that decision lives in two places and is easy to misread as one:
 *
 *		class-avia-auto-updates.php:110		! empty( $state ) ? $token : ''
 *		class-avia-theme-updater.php:162	if( empty( $token ) ) { return false; }
 *
 * so the effective rule is `! empty( $state ) && ! empty( $token )`. Reproducing
 * that exactly matters more than improving it. Too permissive and the updater is
 * handed a credential it will reject forever; too strict and sites simply stop
 * being offered updates while the options page still reports them up to date -
 * and nothing anywhere would report that.
 *
 * Every input arrives as an argument. This class reads no options and applies no
 * filters, so the rule governing whether 200k installs receive updates can be
 * exercised without WordPress.
 *
 * @since 8.1
 */

if( ! defined( 'ABSPATH' ) ) { exit; }		// Exit if accessed directly


if( ! class_exists( 'Avia_Update_Source_Resolver', false ) )
{
	class Avia_Update_Source_Resolver
	{
		/**
		 * Which source wins when more than one credential is usable.
		 *
		 * SureCart first: Envato's rate limit is shared across every Enfold
		 * install and is an already-observed failure mode - the 429 branch in the
		 * Envato API class exists because it has happened - whereas SureCart is
		 * our own infrastructure.
		 *
		 * @since 8.1
		 */
		const DEFAULT_PRECEDENCE = array( 'surecart', 'envato' );

		/**
		 * @since 8.1
		 * @param array $candidates			each: array( 'type' => string, 'credential' => mixed, 'verified' => mixed )
		 * @param array $precedence			type strings, most preferred first
		 * @return array					array( 'type' => string, 'credential' => string, 'reason' => string )
		 */
		static public function resolve( array $candidates, array $precedence = self::DEFAULT_PRECEDENCE )
		{
			if( empty( $candidates ) )
			{
				return self::nothing( 'no_candidates' );
			}

			$eligible = array();

			foreach( $candidates as $candidate )
			{
				$type = isset( $candidate['type'] ) ? (string) $candidate['type'] : '';
				$credential = isset( $candidate['credential'] ) ? $candidate['credential'] : '';
				$verified = isset( $candidate['verified'] ) ? $candidate['verified'] : '';

				/**
				 * Both halves of the live gate, in the original order.
				 *
				 * empty() rather than a comparison against '': the stored state is
				 * a 'Y/m/d H:i' string, but the literal string '0' is falsy under
				 * empty() and truthy under `'' !==`. A site holding '0' would flip
				 * between "updates on" and "updates off" purely on which operator
				 * this used.
				 */
				if( empty( $verified ) || empty( $credential ) )
				{
					continue;
				}

				if( '' === $type )
				{
					continue;
				}

				$eligible[ $type ] = (string) $credential;
			}

			if( empty( $eligible ) )
			{
				return self::nothing( 'no_verified_candidate' );
			}

			foreach( $precedence as $type )
			{
				if( isset( $eligible[ $type ] ) )
				{
					return array(
								'type'			=> $type,
								'credential'	=> $eligible[ $type ],
								'reason'		=> 'precedence'
							);
				}
			}

			/**
			 * A usable credential of a type nobody asked for is not a winner.
			 * Selecting it would mean building a source we have no implementation
			 * for, which fails later and further from the cause.
			 */
			return self::nothing( 'no_verified_candidate' );
		}

		/**
		 * @since 8.1
		 * @param string $reason
		 * @return array
		 */
		static protected function nothing( $reason )
		{
			return array(
						'type'			=> '',
						'credential'	=> '',
						'reason'		=> $reason
					);
		}
	}
}
