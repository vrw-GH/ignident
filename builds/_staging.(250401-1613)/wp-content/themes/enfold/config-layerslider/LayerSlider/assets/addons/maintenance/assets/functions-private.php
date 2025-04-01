<?php

// Prevent direct file access
defined( 'LS_ROOT_FILE' ) || exit;


// Static resources
add_action('admin_enqueue_scripts', function() {
	wp_enqueue_style('ls-addon-maintenance', LS_ROOT_URL.'/addons/maintenance/assets/css/settings.css', false, '1.0.0' );
	wp_enqueue_script('ls-addon-maintenance', LS_ROOT_URL.'/addons/maintenance/assets/js/settings.js', ['jquery'], '1.0.0' );
});


add_action('init', function() {

	// Load settings
	add_action('wp_ajax_ls_maintenance_load_addon_settings', function() {
		include LS_ROOT_PATH.'/addons/maintenance/assets/settings.php';
		exit;
	});

	// Save settings
	add_action('wp_ajax_ls_maintenance_save_addon_settings', function() {

		if( ! wp_verify_nonce( $_POST['nonce'], 'ls-save-addon-setting') ) {
			wp_send_json_error();
		}

		if( ! current_user_can( get_option('layerslider_custom_capability', 'manage_options') ) ) {
			wp_send_json_error();
		}

		$enabled 	= ! empty( $_POST['enabled'] );
		$capability = ! empty( $_POST['capability'] ) ? sanitize_text_field( $_POST['capability'] ) : 'manage_options';
		$type 		= ! empty( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : 'maintenance';
		$content 	= ! empty( $_POST['content'] ) ? sanitize_text_field( $_POST['content'] ) : 'project';
		$project 	= ! empty( $_POST['project'] ) ? (int) $_POST['project'] : 0;
		$page 		= ! empty( $_POST['page'] ) ? (int) $_POST['page'] : 0;
		$mode 		= ! empty( $_POST['mode'] ) ? sanitize_text_field( $_POST['mode'] ) : 'normal';
		$title 		= ! empty( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
		$background = ! empty( $_POST['background'] ) ? sanitize_text_field($_POST['background']) : '#ffffff';

		update_option( 'ls-maintenance-addon-enabled', $enabled );
		update_option( 'ls-maintenance-addon-capability', $capability );
		update_option( 'ls-maintenance-addon-type', $type );
		update_option( 'ls-maintenance-addon-content', $content );
		update_option( 'ls-maintenance-addon-project', $project );
		update_option( 'ls-maintenance-addon-page', $page );
		update_option( 'ls-maintenance-addon-mode', $mode );
		update_option( 'ls-maintenance-addon-title', $title );
		update_option( 'ls-maintenance-addon-background', $background );

		wp_send_json_success();
	});

	if( get_option( 'ls-maintenance-addon-enabled', false ) && LS_Config::isActivatedSite() ) {

		if( basename($_SERVER['PHP_SELF']) === 'index.php') {
			$role = get_option( 'ls-maintenance-addon-capability', 'manage_options' );
			$canManage = current_user_can( get_option( 'ls-maintenance-addon-capability', 'manage_options' ) );

			if( $canManage ) {
				add_action( 'admin_notices', function() {
					$notice = sprintf( '<div class="notice notice-error"><p>%s</p></div>', sprintf('LayerSlider’s Maintenance & Coming Soon add-on is %sactive%s. Don’t forget to %sturn it off%s once you’re done.', '<b>', '</b>', '<a href="'.admin_url('admin.php?page=layerslider#open-maintenance-addon').'">', '</a>') );
					echo $notice;
				});
			}
		}
	}
});