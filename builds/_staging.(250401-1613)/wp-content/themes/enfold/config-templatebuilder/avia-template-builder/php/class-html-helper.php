<?php
/**
* Central Template builder class.
 *
 * Provides functions to build the modal popup window in backend with the option input boxes.
 *
 * To add a custom option 'type' handler or override an existing:
 *
 *		- use this as a frame and create an own file
 *		- add your new type functions or e.g. input to override the input core function
 *		- hook into filter avf_before_load_single_builder_core_file
 *		- when this file is loaded load your file instead and you may return true to skip unnecessary loading this file
 *
 * @since 4.8.9		code of class was moved to extends classes to allow adding new or override existing
 */
if( ! defined( 'ABSPATH' ) )	{	exit;	}		// Exit if accessed directly

if( ! class_exists( 'AviaHtmlHelper', false ) )
{
	class AviaHtmlHelper extends \aviaBuilder\base\aviaModalElements
	{



	} // end class

} // end if ! class_exists

