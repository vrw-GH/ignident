<?php
namespace aviaFramework\widgets;

/**
 * AVIA PARTNER WIDGET
 *
 * An advertising widget that displays 2 images with 125 x 125 px in size
 *
 * @package AviaFramework
 * @since ???
 * @since 4.9			Code was moved from class-framework-widgets.php
 */
if( ! defined( 'AVIA_FW' ) ) {  exit( 'No direct script access allowed' );  }


if( ! class_exists( __NAMESPACE__ . '\avia_one_partner_widget', false ) )
{

	//one image
	class avia_one_partner_widget extends \aviaFramework\widgets\avia_partner_widget
	{
		public function __construct()
		{
			$id_base = 'avia_one_partner_widget';
			$name = THEMENAME . ' ' . __( 'Big Advertising Area', 'avia_framework' );

			$widget_options = array(
								'classname'				=> 'avia_one_partner_widget',
								'description'			=> __( 'An advertising widget that displays 1 big image', 'avia_framework' ),
								'show_instance_in_rest' => true,
								'customize_selective_refresh' => false
							);

			parent::__construct( $id_base, $name, $widget_options );

			$this->add_cont = 1;
		}
	}

}
