<?php
/**
 * Plugin Name: WITW Mail Utility
 * Description: Override wp_mail() with the phpmailer_init action. All options to be set in ENV/config, or must deactivate this plugin. (Must define WITW_MAILER & SMTP_... info)
 * Version: 1.0
 * Author: Wright IT Works
 * Author URI: https://www.wrightsdesk.com/
 */

#--- config/env ---
#define('WITW_MAILER', 	'PHPMailer');	// PHPMailer, ''/undef:system
#define('SMTP_FROM', 	'from@email');
#define('SMTP_FROMNAME','fromName');
#define('SMTP_PORT', 	587);
#define('SMTP_SECURE', 	'TLS');
#define('SMTP_HOST', 	'smtphost'); 
#define('SMTP_USER',	'user'); 
#define('SMTP_PASS', 	'pwd');

if ( !defined('ABSPATH') || defined('WITW_MAILER') ? WITW_MAILER<>'PHPMailer' : false ) 
    exit;

add_action('phpmailer_init', function ($phpmailer) {
    $phpmailer->isSMTP();
    $phpmailer->Host       = defined('SMTP_HOST') 	? SMTP_HOST 	: '';
    $phpmailer->Port       = defined('SMTP_PORT') 	? SMTP_PORT 	: '';
    $phpmailer->SMTPSecure = defined('SMTP_SECURE') 	? SMTP_SECURE 	: 'auto';
    $phpmailer->Username   = defined('SMTP_USER') 	? SMTP_USER 	: '';
    $phpmailer->Password   = defined('SMTP_PASS') 	? SMTP_PASS 	: '';
    $phpmailer->SMTPAuth   = true;
    $phpmailer->From       = defined('SMTP_FROM') 	? SMTP_FROM 	: 'info@tobeset';
    $phpmailer->FromName   = defined('SMTP_FROMNAME')	? SMTP_FROMNAME : 'tobeset';
});
