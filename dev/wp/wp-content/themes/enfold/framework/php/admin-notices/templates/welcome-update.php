<?php
/**
 * Contains the Update Welcome Notice in admin notice
 *
 * @since 6.0
 * @added_by Günter
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

$vn = avia_get_theme_version();
$name = avia_get_theme_name();


echo	'<div class="container avia-welcome-update">';
echo		'<h2>' . sprintf( __( 'Welcome to version %1$s of %2$s - version update was successful', 'avia_framework' ), $vn, $name ) . '</h2>';
echo		'<p>' . __( 'Bugs have been fixed and new features have been added. Enjoy them ....', 'avia_framework' ) . '</p>';
echo	'</div>';
