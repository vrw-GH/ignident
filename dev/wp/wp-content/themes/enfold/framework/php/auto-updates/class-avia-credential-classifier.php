<?php
/**
 * Tells an Envato personal token apart from a SureCart licence key.
 *
 * One input field will accept either, so the shape of the string is the only
 * thing that says which service to ask. Deliberately free of WordPress and of
 * any update source: it takes a string and returns a type, so the rule that
 * decides where a customer's updates come from can be read and tested on its own.
 *
 * The discriminator is the DASHES, not the length. A SureCart key is a UUID and
 * always has four of them; an Envato personal token is a continuous alphanumeric
 * run and never has any. That is what makes the two rules mutually exclusive,
 * and therefore makes classify() independent of the order it tests them in.
 *
 * @since 8.1
 */

if( ! defined( 'ABSPATH' ) ) { exit; }		// Exit if accessed directly


if( ! class_exists( 'Avia_Credential_Classifier', false ) )
{
	class Avia_Credential_Classifier
	{
		/**
		 * @since 8.1
		 */
		const TYPE_UNKNOWN = '';
		const TYPE_ENVATO = 'envato';
		const TYPE_SURECART = 'surecart';

		/**
		 * An Envato personal token: one continuous alphanumeric run.
		 *
		 * The bounds are loose on purpose. Envato has documented slightly
		 * different lengths across API versions, and the load-bearing part of this
		 * pattern is the absence of anything that is not a letter or a digit - not
		 * the exact count. A range that is too tight refuses a valid token, and the
		 * customer's only symptom is that updates quietly stop.
		 *
		 * @since 8.1
		 */
		const PATTERN_ENVATO = '/^[A-Za-z0-9]{28,48}$/';

		/**
		 * A SureCart licence key: 8-4-4-4-12 hex.
		 *
		 * Matched as a shape, NOT as an RFC 4122 v4 UUID. SureCart documents this
		 * field as a key and makes no promise about the version or variant nibbles,
		 * so pinning them would risk locking a paying customer out of updates with
		 * no diagnosable symptom.
		 *
		 * @since 8.1
		 */
		const PATTERN_SURECART = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

		/**
		 * @since 8.1
		 * @param mixed $credential
		 * @return string			one of the TYPE_* constants
		 */
		static public function classify( $credential )
		{
			if( self::is_surecart_key( $credential ) )
			{
				return self::TYPE_SURECART;
			}

			if( self::is_envato_token( $credential ) )
			{
				return self::TYPE_ENVATO;
			}

			return self::TYPE_UNKNOWN;
		}

		/**
		 * Classify, but never answer "unknown" for a credential that exists.
		 *
		 * One input field accepts either service, so this is what decides where a
		 * customer's updates come from - and a wrong answer stops updates with no
		 * error and no symptom. An unrecognised string therefore falls back to
		 * Envato, the status quo for every existing customer: the worst case
		 * becomes a visible "this token is invalid" from Envato rather than
		 * silence.
		 *
		 * An EMPTY credential is genuinely nothing and still returns unknown -
		 * defaulting that would build a source for a customer who entered nothing.
		 *
		 * @since 8.1
		 * @param mixed $credential
		 * @param string $default
		 * @return string
		 */
		static public function classify_or_default( $credential, $default = self::TYPE_ENVATO )
		{
			if( '' === self::normalize( $credential ) )
			{
				return self::TYPE_UNKNOWN;
			}

			$type = self::classify( $credential );

			return self::TYPE_UNKNOWN === $type ? $default : $type;
		}

		/**
		 * @since 8.1
		 * @param mixed $credential
		 * @return boolean
		 */
		static public function is_envato_token( $credential )
		{
			$credential = self::normalize( $credential );

			return '' !== $credential && 1 === preg_match( self::PATTERN_ENVATO, $credential );
		}

		/**
		 * @since 8.1
		 * @param mixed $credential
		 * @return boolean
		 */
		static public function is_surecart_key( $credential )
		{
			$credential = self::normalize( $credential );

			return '' !== $credential && 1 === preg_match( self::PATTERN_SURECART, $credential );
		}

		/**
		 * Reduce anything the options blob might hold to a trimmed string.
		 *
		 * Two reasons this is defensive rather than a string type hint. Customers
		 * paste credentials out of emails and dashboards and bring the surrounding
		 * whitespace with them. And avia_get_option() returns whatever is stored,
		 * which for a corrupted or hand-edited options blob is not guaranteed to be
		 * a string at all - a type declaration would turn that into a TypeError on
		 * every admin page.
		 *
		 * @since 8.1
		 * @param mixed $credential
		 * @return string
		 */
		static protected function normalize( $credential )
		{
			if( is_string( $credential ) )
			{
				return trim( $credential );
			}

			if( is_int( $credential ) || is_float( $credential ) )
			{
				return (string) $credential;
			}

			return '';
		}
	}
}
