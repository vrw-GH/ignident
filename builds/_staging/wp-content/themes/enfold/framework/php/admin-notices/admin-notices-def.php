<?php
/**
 * Template part is included in aviaAdminNotices::init_notices()
 *
 * This file contains default messages and can be used as a frame for custom addons.
 * Create files similar to this and return path to these files with filter 'avf_admin_notices_definition_files'.
 * You can override any of these messages by using the same key.
 *
 *
 * Notices structure:
 *
 *		'uniqueKey'		=>  array(
 *
 *				'class'				=> 'success' | 'info' | 'error' | 'warning' | 'custom'    (WP stylings)
 *				'add_class'			=> [any additional class for styling]
 *				'msg'				=> content to display placed inside <p> tag
 *				'html'				=> HTML content placed inside <div class="..."> tag
 *				'template'			=> [path to file] - must echo valid HTML in own container   e.g. 'templates/v60.php'
 *				'close'				=> 'dismiss' | 'hide'			hide will show both "Dismiss" and "X"
 *				'dismiss'			=> 'user_only' | 'all_users'
 *				'capability'		=> [user capability] | 'all'  .....   manage_options, edit_posts
 *
 *						)
 *
 * @since 6.0
 */

// $notices is in scope of function where this file is included. You do not need to return anything.
if( ! is_array( $notices ) )
{
	$notices = [];
}


//default update success
$vn = avia_get_theme_version();
if( is_child_theme() )
{
	$vn .= ' ' . __( '(Parent Theme)', 'avia_framework' );
}

$name = avia_get_theme_name();

//	a default minimal message after update
$notices['version_update_success'] = [
				'class'			=> 'success',
				'msg'			=> sprintf( __( '%1$s update to version %2$s was successfull', 'avia_framework' ), $name, $vn ),
				'close'			=> 'dismiss',
				'dismiss'		=> 'all_users',
				'capability'	=> 'manage_options'
			];

$notices['welcome_new'] = [
				'class'			=> 'custom',
				'template'		=> trailingslashit( dirname( __FILE__ ) ) . 'templates/welcome-new.php',
				'close'			=> 'dismiss',
				'dismiss'		=> 'user_only',
				'capability'	=> 'manage_options'
			];

$notices['welcome_update'] = [
				'class'			=> 'custom',
				'template'		=> trailingslashit( dirname( __FILE__ ) ) . 'templates/welcome-update.php',
				'close'			=> 'dismiss',
				'dismiss'		=> 'user_only',
				'capability'	=> 'manage_options'
			];
