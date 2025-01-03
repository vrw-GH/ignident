<?php
/*
 * Define default arrays for option values
 *
 * @since 4.8.8
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

global $avia_admin_values;

//	avoid unnecessary double init
if( is_array( $avia_admin_values ) && ! empty( $avia_admin_values ) )
{
	return;
}

$avia_admin_values = array();

$avia_admin_values['font-weight'] = array(
							__( 'Default', 'avia_framework' )	=> '' ,
							__( 'Normal', 'avia_framework' )	=> 'normal',
							__( 'Bold', 'avia_framework' )		=> 'bold',
							__( 'Light', 'avia_framework' )		=> 'lighter',
							__( '200', 'avia_framework' )		=> '200',
							__( '300', 'avia_framework' )		=> '300',
							__( '400', 'avia_framework' )		=> '400',
							__( '500', 'avia_framework' )		=> '500',
							__( '600', 'avia_framework' )		=> '600',
							__( '700', 'avia_framework' )		=> '700',
							__( '800', 'avia_framework' )		=> '800',
							__( '900', 'avia_framework' )		=> '900'
						);

$avia_admin_values['text-transform'] = array(
							__( 'Default', 'avia_framework' )	=> '',
							__( 'None', 'avia_framework' )		=> 'none',
							__( 'Uppercase', 'avia_framework' )	=> 'uppercase',
							__( 'Lowercase', 'avia_framework' )	=> 'lowercase'
						);

$avia_admin_values['text-align'] = array(
							__( 'Default', 'avia_framework' )	=> '',
							__( 'Left', 'avia_framework' )		=> 'left',
							__( 'Center', 'avia_framework' )	=> 'center',
							__( 'Right', 'avia_framework' )		=> 'right'
						);

$avia_admin_values['text-decoration'] = array(
							__( 'Default', 'avia_framework' )		=> '',
							__( 'None', 'avia_framework' )			=> 'none !important' ,
							__( 'Underline', 'avia_framework' )		=> 'underline !important',
							__( 'Overline', 'avia_framework' )		=> 'overline !important',
							__( 'Line Through', 'avia_framework' )	=> 'line-through !important'
						);

$avia_admin_values['display'] = array(
							__( 'Default', 'avia_framework' )		=> '',
							__( 'Inline', 'avia_framework' )		=> 'inline',
							__( 'Inline Block', 'avia_framework' )	=> 'inline-block',
							__( 'Block', 'avia_framework' )			=> 'block'
						);

$avia_admin_values['font-size'] = array(
							__( 'Default', 'avia_framework' )	=> ''
						);

for( $i = 8; $i <= 80; $i++ )
{
	$avia_admin_values['font-size'][ "{$i}px" ] = "{$i}px";
}

$avia_admin_values['line-height'] = array(
							__( 'Default', 'avia_framework' )	=> ''
						);

for( $i = 0.1; $i <= 5; $i += 0.1 )
{
	$avia_admin_values['line-height'][ "{$i}em" ] = "{$i}em";
}

//	values for typography tab in "General Styling"
$avia_admin_values['customize-typo-elements'] = array(
									'content-font'	=> 'default_font_size',
									'h1'			=> 'custom_h1',
									'h2'			=> 'custom_h2',
									'h3'			=> 'custom_h3',
									'h4'			=> 'custom_h4',
									'h5'			=> 'custom_h5',
									'h6'			=> 'custom_h6'
								);

$avia_admin_values['customize-typo-elements-rules'] = array(
									'content-font'	=> array( 'font-size' ),
									'h1'			=> array( 'font-size', 'font-weight', 'line-height', 'margin' ),
									'h2'			=> array( 'font-size', 'font-weight', 'line-height', 'margin' ),
									'h3'			=> array( 'font-size', 'font-weight', 'line-height', 'margin' ),
									'h4'			=> array( 'font-size', 'font-weight', 'line-height', 'margin' ),
									'h5'			=> array( 'font-size', 'font-weight', 'line-height', 'margin' ),
									'h6'			=> array( 'font-size', 'font-weight', 'line-height', 'margin' )
								);

$avia_admin_values['default-typo-elements-rules'] = array(
									'content-font'	=> array(
															'font-size'	=> '13px/1.65em'
														),
									'h1'			=> array(
															'font-size'		=> '34px',
															'font-weight'	=> '600',
															'line-height'	=> '1.1em',
															'margin'		=> '0 0 14px 0'
														),
									'h2'			=> array(
															'font-size'		=> '28px',
															'font-weight'	=> '600',
															'line-height'	=> '1.1em',
															'margin'		=> '0 0 10px 0'
														),
									'h3'			=> array(
															'font-size'		=> '20px',
															'font-weight'	=> '600',
															'line-height'	=> '1.1em',
															'margin'		=> '0 0 8px 0'
														),
									'h4'			=> array(
															'font-size'		=> '18px',
															'font-weight'	=> '600',
															'line-height'	=> '1.1em',
															'margin'		=> '0 0 4px 0'
														),
									'h5'			=> array(
															'font-size'		=> '16px',
															'font-weight'	=> '600',
															'line-height'	=> '1.1em',
															'margin'		=> '0'
														),
									'h6'			=> array(
															'font-size'		=> '14px',
															'font-weight'	=> '600',
															'line-height'	=> '1.1em',
															'margin'		=> '0'
														)
								);


$avia_admin_values['responsive-sizes'] = array(
							__( 'Default', 'avia_framework' )		=> '',
							__( 'Medium', 'avia_framework' )		=> 'medium',
							__( 'Small', 'avia_framework' )			=> 'small',
							__( 'Very Small', 'avia_framework' )	=> 'mini'
						);

/**
 *
 * @since 4.8.8
 * @param array $avia_admin_values
 * @return array
 */
$avia_admin_values = apply_filters( 'avf_admin_option_values', $avia_admin_values );
