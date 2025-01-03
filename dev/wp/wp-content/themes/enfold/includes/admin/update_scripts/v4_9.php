<?php
/**
 * update for version 4.9
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


//set global options
global $avia;

$theme_options = $avia->options['avia'];

$fonts = AviaSuperobject()->type_fonts()->websafe_fonts_select_list();

//	we removed '-' in option values for websafe fonts -> breaks option selection
//	see AviaTypeFonts->websafe_fonts_select_list()
$heading_font = avia_get_option( 'google_webfont' );
$content_font = avia_get_option( 'default_font' );

$heading_font = false !== strpos( $heading_font, '-' ) ? str_replace( '-', ' ', $heading_font ) : '';
$heading_font = false !== strpos( $heading_font, ' websave' ) ? str_replace( ' websave', '-websave', $heading_font ) : '';

$content_font = false !== strpos( $content_font, '-' ) ? str_replace( '-', ' ', $content_font ) : '';
$content_font = false !== strpos( $content_font, ' websave' ) ? str_replace( ' websave', '-websave', $content_font ) : '';

if( ! empty( $heading_font ) || ! empty( $content_font ) )
{
	foreach( $fonts as $font )
	{
		if( ! empty( $heading_font ) )
		{
			if( false !== stripos( $font, $heading_font ) )
			{
				$avia->options['avia']['google_webfont'] = $font;
				$heading_font = '';
			}
		}

		if( ! empty( $content_font ) )
		{
			if( false !== stripos( $font, $content_font ) )
			{
				$avia->options['avia']['default_font'] = $font;
				$content_font = '';
			}
		}
	}
}

//	update advanced stylings
if( ! isset( $avia->options['avia']['advanced_styling'] ) )
{
	$avia->options['avia']['advanced_styling'] = array();
}

if( is_array( $avia->options['avia']['advanced_styling'] ) )
{
	foreach( $avia->options['avia']['advanced_styling'] as $index => $styling )
	{
		if( isset( $styling['font_family'] ) )
		{
			$font_family = $styling['font_family'];
			$font_family = false !== strpos( $font_family, '-' ) ? str_replace( '-', ' ', $font_family ) : '';
			$font_family = false !== strpos( $font_family, ' websave' ) ? str_replace( ' websave', '-websave', $font_family ) : '';

			if( empty( $font_family ) )
			{
				continue;
			}

			foreach( $fonts as $font )
			{
				if( ! empty( $font_family ) )
				{
					if( false !== stripos( $font, $font_family ) )
					{
						$avia->options['avia']['advanced_styling'][ $index ]['font_family'] = $font;
						$font_family = '';
					}
				}
			}
		}
	}
}

update_option( $avia->option_prefix, $avia->options );

if( defined( 'WP_DEBUG' ) && WP_DEBUG )
{
	error_log( esc_html( __( 'Executed theme update script:', 'avia_framework' ) ) . ' ' . basename( __FILE__ ) );
}
