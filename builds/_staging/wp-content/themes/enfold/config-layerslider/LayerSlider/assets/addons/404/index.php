<?php

// Prevent direct file access
defined( 'LS_ROOT_FILE' ) || exit;

// Load private functions
if( is_admin() ) {
	require_once __DIR__.'/assets/functions-private.php';


// Load public functions
} elseif( get_option( 'ls-404-addon-enabled', false ) && LS_Config::isActivatedSite() ) {

	require_once __DIR__.'/assets/functions-public.php';
}