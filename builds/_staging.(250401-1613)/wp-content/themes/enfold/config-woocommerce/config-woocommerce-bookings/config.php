<?php
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

if( ! is_admin() )
{
	add_action( 'init', 'avia_woocommerce_bookings_register_assets' );
}

if( ! function_exists( 'avia_woocommerce_bookings_register_assets' ) )
{
	function avia_woocommerce_bookings_register_assets()
	{
		$vn = avia_get_theme_version();
		$min_css = avia_minify_extension( 'css' );

		wp_enqueue_style( 'avia-woocommerce-bookings-css', AVIA_BASE_URL . "config-woocommerce/config-woocommerce-bookings/woocommerce-booking-mod{$min_css}.css", array(), $vn );
	}
}