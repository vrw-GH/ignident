<?php
/**
 * Template part is included in aviaAdminNotices::init_notices()
 *
 * This file contains Enfold default messages and and is returned with filter 'avf_admin_notices_definition_files'
 * to aviaAdminNotices
 *
 * Notices structure see also aviaAdminNotices:
 *
 *		'uniqueKey'		=>  array(
 *
 *				'class'				=> 'success' | 'info' | 'error' | 'warning' | 'custom'    (WP stylings)
 *				'add_class'			=> [any additional class for styling]
 *				'msg'				=> content to display placed inside <p> tag
 *				'html'				=> HTML content placed inside <div> tag
 *				'template'			=> [complete path to file] - must echo valid HTML in own container   e.g. 'c:/your_server/..../templates/v6_0.php'
 *				'close'				=> 'dismiss' | 'hide'		hide will show both "Dismiss" and "X"
 *				'dismiss'			=> 'user_only' | 'all_users'
 *				'capability'		=> [user capability] | 'all'		manage_options,edit_posts
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

$notices['performance_update'] = [
				'class'			=> 'info',
				'msg'			=> "<strong>Attention:</strong> The last Enfold update added a lot of performance options. Make sure to read more about it <a href='https://kriesi.at/archives/enfold-4-3-performance-update' target='_blank' rel='noopener noreferrer'>here</a><br><br>If you are running a caching plugin please make sure to reset your cached files, since the CSS and JS file structure of the theme changed heavily",
				'close'			=> 'dismiss',
				'dismiss'		=> 'all_users',
				'capability'	=> 'edit_posts'
			];

//update to version 4.4 - gdpr update. display notice and link to blog post
$notices['gdpr_update'] = [
				'class'			=> 'info',
				'msg'			=> "<strong>Attention:</strong> Enfold was updated for GDPR compliance. Make sure to read more about it <a href='https://kriesi.at/archives/enfold-4-4-and-the-gdpr-general-data-protection-regulation' target='_blank' rel='noopener noreferrer'>here</a>",
				'close'			=> 'dismiss',
				'dismiss'		=> 'user_only',
				'capability'	=> 'edit_posts'
			];

$notices['gdpr_update_2'] = [
				'class'			=> 'info',
				'msg'			=> '<strong>Attention:</strong> Enfold GDPR compliance has been extended to support &quot;Must Opt In&quot;. Options and shortcodes have been extended. Please read the description on the theme options tab carefully and check the extended documentation <a href="https://kriesi.at/documentation/enfold/privacy-cookies/#implementation-of-data-security-in-enfold" target="_blank" rel="noopener noreferrer">here</a>.',
				'close'			=> 'dismiss',
				'dismiss'		=> 'user_only',
				'capability'	=> 'edit_posts'
			];

$notices['enfold_60_welcome'] = [
				'class'			=> 'custom',
				'template'		=> trailingslashit( dirname( __FILE__ ) ) . 'templates/v6_0.php',
				'close'			=> 'dismiss',
				'dismiss'		=> 'user_only',
				'capability'	=> 'manage_options'
			];

$notices['enfold_601_welcome'] = [
				'class'			=> 'custom',
				'template'		=> trailingslashit( dirname( __FILE__ ) ) . 'templates/v6_0_1.php',
				'close'			=> 'dismiss',
				'dismiss'		=> 'user_only',
				'capability'	=> 'manage_options'
			];

$notices['enfold_70_welcome'] = [
				'class'			=> 'custom',
				'template'		=> trailingslashit( dirname( __FILE__ ) ) . 'templates/v7_0.php',
				'close'			=> 'dismiss',
				'dismiss'		=> 'user_only',
				'capability'	=> 'manage_options'
			];

