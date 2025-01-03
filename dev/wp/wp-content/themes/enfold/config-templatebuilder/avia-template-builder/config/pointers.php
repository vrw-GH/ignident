<?php
/**
 * Define an admin pointer popup window to new users that can be dismissed
 *
 * @since ???
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

$pointers = array();
$screens = Avia_Builder()->get_supported_post_types();
$lables = array();

foreach( $screens as $screen )
{
	$obj = get_post_type_object( $screen );
	if( $obj instanceof WP_Post_Type )
	{
		$lables[] = $obj->labels->singular_name;
	}
}

foreach( $screens as $screen )
{
	$pointers[] = array(
					'id'		=> 'builder-button-pointer',   // unique id for this pointer
					'screen'	=> $screen, // this is the page hook we want our pointer to show on
					'target'	=> '#avia-builder-button', // the css selector for the pointer to be tied to, best to use ID's
					'title'		=> 'Avia Layout Builder',
					'content'	=> __( 'The Avia Layout Builder allows you to create unique layouts with an easy to use, drag and drop interface.', 'avia_framework' ) . '<br/><br/>' . __( 'The Builder is available on:', 'avia_framework' ) . ' ' . implode( ', ', $lables ),
					'position'	=> array(
									'edge'	=> 'left', //top, bottom, left, right
									'align'	=> 'middle' //top, bottom, left, right, middle
								 )
				);

}
