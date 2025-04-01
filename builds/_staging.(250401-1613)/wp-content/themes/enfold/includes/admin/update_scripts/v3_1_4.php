<?php
/**
 * update for version 3.1.4
 *
 * update the widget locations to avoid error notice in 4.2 and to prevent the sorting bugs
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

$map = array('av_everywhere', 'av_blog', 'av_pages');

if( class_exists( 'woocommerce', false ) )
{
	$map[] = 'av_shop_overview';
	$map[] = 'av_shop_single';
}

for ($i = 1; $i <= avia_get_option('footer_columns','5'); $i++)
{
	$map[] = 'av_footer_' . $i;
}

if( class_exists( 'bbPress', false ) )
{
	$map[] = 'av_forum';
}

$dynamic = get_option('avia_sidebars');

if(is_array($dynamic) && !empty($dynamic))
{
	foreach($dynamic as $key => $value)
	{
		$map[] = avia_backend_safe_string( $value, '-' ) ;
	}
}

$current_sidebars = get_option('sidebars_widgets');

if(!empty($current_sidebars) && isset($current_sidebars['sidebar-1']))
{
	$new_sidebars = array('wp_inactive_widgets' => $current_sidebars['wp_inactive_widgets']);

	foreach($map as $key => $sidebar)
	{
		if( isset( $current_sidebars[ 'sidebar-' . ( $key + 1 ) ] ) )
		{
			$new_sidebars[ $sidebar ] = $current_sidebars[ 'sidebar-' . ( $key + 1 ) ];
		}
	}

	update_option( 'sidebars_widgets', $new_sidebars );
}

if( defined( 'WP_DEBUG' ) && WP_DEBUG )
{
	error_log( esc_html( __( 'Executed theme update script:', 'avia_framework' ) ) . ' ' . basename( __FILE__ ) );
}
