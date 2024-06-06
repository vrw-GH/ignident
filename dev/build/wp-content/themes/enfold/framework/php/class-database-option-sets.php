<?php
/**
 * This file holds the class needed to create dynamic option pages and clone, create and remove option arrays from the option pages
 *
 * @author		Christian "Kriesi" Budschedl
 * @copyright	Copyright (c) Christian Budschedl
 * @link		http://kriesi.at
 * @link		http://aviathemes.com
 * @since		Version 1.0
 * @package 	AviaFramework
 * @since 5.6	copied to /legacy and not needed functions removed
 */
if( ! defined( 'AVIA_FW') ) {  exit('No direct script access allowed');  }


if( ! class_exists( 'avia_database_set', false ) )
{
	class avia_database_set extends aviaFramework\base\object_properties
	{
		/**
		 * avia superobject
		 *
		 * @since ????
		 * @var avia_superobject
		 */
		protected $avia_superobject;

		/**
		 * array we should use for iteratetion
		 *
		 * @since ????
		 * @var array
		 */
		public $elements;


		/**
		 * The constructor sets the default element for iteration
		 *
		 * @since ????
		 * @param avia_superobject $avia_superobject
		 */
		public function __construct( $avia_superobject = false )
		{
			if( ! $avia_superobject )
			{
				$this->avia_superobject = $GLOBALS['avia'];
			}
			else
			{
				$this->avia_superobject = $avia_superobject;
			}

			$this->elements = $this->avia_superobject->option_page_data;
		}

		/**
		 * @since 5.2
		 */
		public function __destruct()
		{
			unset( $this->avia_superobject );
			unset( $this->elements );
		}

		/**
		 *  The recursive get function retrieves a unqiue array by array key that was requested within an array of choice
		 *  If no array is defined the global unmodified option array will be checked. The values returned is a
		 *  direct reference to this option array, therefore editing the value later will also modify the option array
		 *  The function will call itself with a subarray when an element of type "group" is encountered
		 *
		 * @since ????
		 * @param string $slug
		 * @param array $elements
		 * @return array|null
		 */
		public function get( $slug, $elements = false )
		{
			if( ! $elements )
			{
				$elements = $this->elements;
			}

			foreach( $elements as $element)
			{
				if( $element['type'] == 'group' )
				{
					$option = $this->get( $slug, $element['subelements'] );

					if( $option )
					{
						return $option;
					}
				}

				if( isset( $element['id'] ) && $element['id'] == $slug )
				{
					return $element;
				}
			}

			return null;
		}

	}
}











