<?php
/**
 * Builds the update source for a resolved credential.
 *
 * Small on purpose. It exists so that adding a second source is one case here
 * plus one new file, rather than an edit inside Avia_Theme_Updater - the class
 * whose behaviour has to stay provably unchanged.
 *
 * @since 8.1
 */

if( ! defined( 'ABSPATH' ) ) { exit; }		// Exit if accessed directly

require_once( __DIR__ . '/class-avia-update-source.php' );


if( ! class_exists( 'Avia_Update_Source_Factory', false ) )
{
	class Avia_Update_Source_Factory
	{
		/**
		 * Returns false rather than a null object when there is nothing to build.
		 *
		 * That false is load-bearing. Avia_Theme_Updater treats "no source" as the
		 * signal to clear the updater log and the results cache, so a factory
		 * handing back an unconfigured object would leave stale data behind
		 * forever and change behaviour for every site without a token.
		 *
		 * @since 8.1
		 * @param string $type					one of the classifier's TYPE_* values
		 * @param string $credential
		 * @return Avia_Update_Source|false
		 */
		static public function create( $type, $credential )
		{
			/**
			 * Compared against '' rather than tested with empty(), because
			 * empty( '0' ) is true. A customer who pastes 0 into the token field
			 * must get a credential that the API rejects - visibly - instead of
			 * one that silently reads as "no credential supplied".
			 *
			 * @since 8.1
			 */
			if( '' === (string) $credential || '' === (string) $type )
			{
				return false;
			}

			switch( $type )
			{
				case 'envato':
					if( ! class_exists( 'Avia_Envato_Update_Source', false ) )
					{
						require_once( __DIR__ . '/class-avia-envato-update-source.php' );
					}

					return new Avia_Envato_Update_Source( $credential );

				case 'surecart':
					if( ! class_exists( 'Avia_SureCart_Update_Source', false ) )
					{
						require_once( __DIR__ . '/class-avia-surecart-update-source.php' );
					}

					return new Avia_SureCart_Update_Source( $credential );
			}

			/**
			 * An unknown type is not an error worth throwing over - the credential
			 * simply cannot be used, which is the same outcome as not having one.
			 */
			return false;
		}
	}
}
