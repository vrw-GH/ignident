<?php
namespace aviaBuilder\traits;

/**
 * Helper to add a default font
 *
 * @author		Günter
 * @since 7.0
 */

if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

if( ! trait_exists( __NAMESPACE__ . '\modalIconfontHelper' ) )
{
	trait modalIconfontHelper
	{
		/**
		 * Iterates over elements and adds the default font argument
		 * (allows to override usage of entypo-fontello as default font
		 * (which was used prior svg icon sets were added)
		 *
		 * @since 7.0
		 * @param array $elements
		 * @return array
		 */
		protected function get_defaults( array &$elements )
		{
			$args = parent::get_defaults( $elements );
			return $this->set_iconfont( $args, $elements );
		}

		/**
		 * Called to return default values for js template used when a new subelement is added.
		 *
		 * @since 7.0
		 * @param array $args
		 * @param array $subelements
		 * @return array
		 */
		public function get_defaults_subelements_for_modal( array $args, array $subelements )
		{
			return $this->set_iconfont( $args, $subelements );
		}

		/**
		 * 
		 * @since 7.0
		 * @param array $args
		 * @param array $elements
		 * @return array
		 */
		protected function set_iconfont( array $args, array $elements )
		{
			foreach( $elements as &$element )
			{
				if( isset( $element['type'] ) && 'iconfont' == $element['type'] )
				{
					if( ! empty( $element['std_font'] ) )
					{
						$args['font'] = $element['std_font'];
					}

					break;
				}
			}

			unset( $element );

			return $args;
		}
	}
}
