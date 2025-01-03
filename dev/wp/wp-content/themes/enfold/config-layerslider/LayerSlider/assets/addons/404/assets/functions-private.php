<?php

// Prevent direct file access
defined( 'LS_ROOT_FILE' ) || exit;


// Static resources
add_action('admin_enqueue_scripts', function() {
	wp_enqueue_style('ls-addon-404', LS_ROOT_URL.'/addons/404/assets/css/settings.css', false, '1.0.0' );
	wp_enqueue_script('ls-addon-404', LS_ROOT_URL.'/addons/404/assets/js/settings.js', ['jquery'], '1.0.0' );
});



add_action('init', function() {

	// Load settings
	add_action('wp_ajax_ls_404_load_addon_settings', function() {
		include LS_ROOT_PATH.'/addons/404/assets/settings.php';
		exit;
	});

	// Save settings
	add_action('wp_ajax_ls_404_save_addon_settings', function() {

		if( ! wp_verify_nonce( $_POST['nonce'], 'ls-save-addon-setting') ) {
			wp_send_json_error();
		}

		if( ! current_user_can( get_option('layerslider_custom_capability', 'manage_options') ) ) {
			wp_send_json_error();
		}

		$enabled 	= ! empty( $_POST['enabled'] );
		$type 		= ! empty( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : 'project';
		$project 	= ! empty( $_POST['project'] ) ? (int) $_POST['project'] : 0;
		$page 		= ! empty( $_POST['page'] ) ? (int) $_POST['page'] : 0;
		$mode 		= ! empty( $_POST['mode'] ) ? sanitize_text_field( $_POST['mode'] ) : 'normal';
		$title 		= ! empty( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
		$background = ! empty( $_POST['background'] ) ? sanitize_text_field($_POST['background']) : '#ffffff';

		update_option( 'ls-404-addon-enabled', $enabled );
		update_option( 'ls-404-addon-type', $type );
		update_option( 'ls-404-addon-project', $project );
		update_option( 'ls-404-addon-page', $page );
		update_option( 'ls-404-addon-mode', $mode );
		update_option( 'ls-404-addon-title', $title );
		update_option( 'ls-404-addon-background', $background );

		wp_send_json_success();
	});

});