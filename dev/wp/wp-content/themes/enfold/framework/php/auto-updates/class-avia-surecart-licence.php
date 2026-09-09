<?php
/**
 * What a SureCart licence entitles a site to.
 *
 * Enfold sells one year of updates, so this decision has money attached in both
 * directions: too strict and a paying customer is refused a release they bought,
 * too loose and the year we sold quietly becomes permanent.
 *
 * Deliberately free of WordPress and of any HTTP client - it takes the licence
 * data and a timestamp and returns an answer, so the rule that governs who
 * receives updates can be read and tested on its own.
 *
 * DO NOT TRUST `status`. SureCart's own SDK treats a licence as valid whenever
 * its status is not literally 'revoked'. On the live account `status` tracks
 * ACTIVATION rather than validity - a paid, unactivated licence reads 'inactive'
 * - and it is unconfirmed whether SureCart flips it when the term ends. What
 * SureCart does reliably is set `revokes_at` to created_at + the price's
 * revoke_after_days (365 for us). Comparing that date ourselves is correct
 * whether or not SureCart ever revokes on its own.
 *
 * @since 8.1
 */

if( ! defined( 'ABSPATH' ) ) { exit; }		// Exit if accessed directly


if( ! class_exists( 'Avia_SureCart_Licence', false ) )
{
	class Avia_SureCart_Licence
	{
		/**
		 * @since 8.1
		 */
		const STATUS_VALID = 'valid';
		const STATUS_EXPIRED = 'expired';
		const STATUS_REVOKED = 'revoked';
		const STATUS_UNKNOWN = 'unknown';

		/**
		 * @since 8.1
		 * @param mixed $licence			decoded licence object from the API
		 * @param int|null $now				unix time, defaults to now
		 * @return string					one of the STATUS_* constants
		 */
		static public function status( $licence, $now = null )
		{
			$licence = self::normalize( $licence );

			if( empty( $licence['key'] ) )
			{
				return self::STATUS_UNKNOWN;
			}

			/**
			 * An explicit revocation outranks the date, so support sees the real
			 * reason rather than a generic expiry.
			 */
			if( isset( $licence['status'] ) && 'revoked' === $licence['status'] )
			{
				return self::STATUS_REVOKED;
			}

			$revokes_at = self::revokes_at( $licence );

			/**
			 * No date means no expiry. Reading a missing value as "expired" would
			 * lock out every perpetual licence we might ever issue.
			 */
			if( is_null( $revokes_at ) )
			{
				return self::STATUS_VALID;
			}

			$now = is_null( $now ) ? time() : (int) $now;

			return $revokes_at <= $now ? self::STATUS_EXPIRED : self::STATUS_VALID;
		}

		/**
		 * @since 8.1
		 * @param mixed $licence
		 * @param int|null $now
		 * @return boolean
		 */
		static public function is_valid( $licence, $now = null )
		{
			return self::STATUS_VALID === self::status( $licence, $now );
		}

		/**
		 * @since 8.1
		 * @param mixed $licence
		 * @return int|null					unix time, or null for a perpetual licence
		 */
		static public function revokes_at( $licence )
		{
			$licence = self::normalize( $licence );

			if( ! isset( $licence['revokes_at'] ) || '' === $licence['revokes_at'] || is_null( $licence['revokes_at'] ) )
			{
				return null;
			}

			return (int) $licence['revokes_at'];
		}

		/**
		 * Whole days left, which is what the 30 and 7 day renewal notices read.
		 *
		 * Rounded DOWN, so "7 days and 3 hours" reports 7 rather than 8 - a notice
		 * that fires a day early is harmless, one that fires a day late is not.
		 * Negative once the term has ended.
		 *
		 * @since 8.1
		 * @param mixed $licence
		 * @param int|null $now
		 * @return int|null					null for a perpetual licence
		 */
		static public function days_remaining( $licence, $now = null )
		{
			$revokes_at = self::revokes_at( $licence );

			if( is_null( $revokes_at ) )
			{
				return null;
			}

			$now = is_null( $now ) ? time() : (int) $now;

			return (int) floor( ( $revokes_at - $now ) / DAY_IN_SECONDS );
		}

		/**
		 * @since 8.1
		 * @param mixed $licence
		 * @return int
		 */
		static public function seats_used( $licence )
		{
			$licence = self::normalize( $licence );

			/**
			 * The two SureCart APIs spell this differently: the public licensing
			 * endpoint returns `activations_count`, the admin API `activation_count`.
			 * Reading only one of them makes an exhausted licence look empty, so
			 * every activation is allowed - and nothing reports it.
			 *
			 * @since 8.1
			 */
			if( isset( $licence['activations_count'] ) )
			{
				return (int) $licence['activations_count'];
			}

			return (int) ( $licence['activation_count'] ?? 0 );
		}

		/**
		 * @since 8.1
		 * @param mixed $licence
		 * @return int						0 means unlimited
		 */
		static public function seats_limit( $licence )
		{
			$licence = self::normalize( $licence );

			return (int) ( $licence['activation_limit'] ?? 0 );
		}

		/**
		 * Is there room for one more site?
		 *
		 * A limit of 0 or null means unlimited, NOT "no seats". Reading it the
		 * other way would refuse every activation on an unlimited licence.
		 *
		 * @since 8.1
		 * @param mixed $licence
		 * @return boolean
		 */
		static public function has_free_seat( $licence )
		{
			$limit = self::seats_limit( $licence );

			if( $limit <= 0 )
			{
				return true;
			}

			return self::seats_used( $licence ) < $limit;
		}

		/**
		 * The API hands back an object; tests and filters hand back an array.
		 * Anything else is not a licence and must not fatal - this data arrives
		 * over the network.
		 *
		 * @since 8.1
		 * @param mixed $licence
		 * @return array
		 */
		static protected function normalize( $licence )
		{
			if( is_object( $licence ) )
			{
				$licence = get_object_vars( $licence );
			}

			return is_array( $licence ) ? $licence : array();
		}
	}
}
