<?php
namespace aviaFramework\widgets;

/**
 * AVIA PORTFOLIOBOX
 *
 * Widget that creates a list of latest portfolio entries. Basically the same widget as the newsbox with some minor modifications, therefore it just extends the Newsbox
 *
 * @package AviaFramework
 * @depends \aviaFramework\widgets\avia_newsbox
 * @since ???
 * @since 4.9			Code was moved from class-framework-widgets.php
 */
if( ! defined( 'AVIA_FW' ) ) {  exit( 'No direct script access allowed' );  }


if( ! class_exists( __NAMESPACE__ . '\avia_portfoliobox', false ) )
{
	class avia_portfoliobox extends \aviaFramework\widgets\avia_newsbox
	{
		public function __construct()
		{
			$id_base = 'portfoliobox';
			$name = THEMENAME . ' ' . __( 'Latest Portfolio', 'avia_framework' );

			$widget_options = array(
						'classname'				=> 'newsbox',
						'description'			=> __( 'A Sidebar widget to display latest portfolio entries in your sidebar', 'avia_framework' ),
						'show_instance_in_rest' => true,
						'customize_selective_refresh' => false
					);

			parent::__construct( $id_base, $name, $widget_options );

			$this->avia_term = 'portfolio_entries';
			$this->avia_post_type = 'portfolio';
			$this->avia_new_query = ''; //set a custom query here
		}
	}
}

