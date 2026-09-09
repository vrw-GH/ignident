<?php
/**
 * General Styling Tab
 * ===================
 *
 * @since 4.8.2
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

global $avia_config, $avia_pages, $avia_elements;



$avia_elements[] = array(
			'slug'		=> 'customizer',
			'name'		=> __( 'Here you can select a number of different elements and change their default styling', 'avia_framework' ),
			'desc'		=> __( 'If a value is left empty or set to default then it will not be changed from the value defined in your CSS files', 'avia_framework' ) . 
								'<br/><br/>',
			'id'		=> 'advanced_styling',
			'type'		=> 'styling_wizard',
			'order'		=> array(
								__( 'HTML Tags', 'avia_framework' ),
								__( 'Headings', 'avia_framework' ),
								__( 'Main Menu', 'avia_framework' ),
								__( 'Main Menu (Icon)', 'avia_framework' ),
								__( 'Cookie Consent Bar', 'avia_framework' ),
								__( 'Misc', 'avia_framework' )
							),
			'std'		=> '',
			'class'		=> '',
			'globalcss'	=> true,
			'elements'	=> $advanced
		);

