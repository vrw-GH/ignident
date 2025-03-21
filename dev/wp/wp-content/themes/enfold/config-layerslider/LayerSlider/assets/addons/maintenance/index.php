<?php

// Prevent direct file access
defined( 'LS_ROOT_FILE' ) || exit;

$addonEnabled = get_option( 'ls-maintenance-addon-enabled', false ) && LS_Config::isActivatedSite();

// Load private functions
if( is_admin() ) {
	require_once __DIR__.'/assets/functions-private.php';


// Load public functions
} elseif( $addonEnabled ) {

	require_once __DIR__.'/assets/functions-public.php';
}


// Admin menu bar item
if( $addonEnabled ) {
	add_action('init', function() {
		$role 		= get_option( 'ls-maintenance-addon-capability', 'manage_options' );
		$canManage 	= current_user_can( get_option( 'ls-maintenance-addon-capability', 'manage_options' ) );

		if( $canManage ) {
			add_action('admin_bar_menu', function( $admin_bar ) {
				$type = get_option( 'ls-maintenance-addon-type', 'maintenance' );
				$admin_bar->add_menu([
					'id'    => 'ab-layerslider-maintenance',
					'title' => $type === 'maintenance' ? __('Maintenance Mode Active', 'LayerSlider') : __('Coming Soon Mode Active', 'LayerSlider'),
					'href'  => admin_url('admin.php?page=layerslider#open-maintenance-addon'),
					'meta' => [
						'class' => 'ab-layerslider-maintenance'
					]
					]);
			}, 40);

			add_action( 'wp_head', 'ls_maintenance_menu_item_styles' );
			add_action( 'admin_head', 'ls_maintenance_menu_item_styles');
		}
	});
}

function ls_maintenance_menu_item_styles() {
echo <<<STYLES
	<style>
		.ab-layerslider-maintenance a.ab-item {
			font-weight: bold !important;
			background: #f00 !important;
			color: white !important;
		}

		.ab-layerslider-maintenance:hover a {
			color: white !important;
			background: #d00 !important;
		}
	</style>
STYLES;
}