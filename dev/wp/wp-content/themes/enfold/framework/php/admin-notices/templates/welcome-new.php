<?php
/**
 * Contains the Welcome Notice for a new installed theme
 *
 * @since 6.0
 * @added_by Günter
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

$name = avia_get_theme_name();


echo	'<div class="container avia-welcome-new">';
echo		'<h2>' . sprintf( __( 'Welcome to %1$s', 'avia_framework' ), $name ) . '</h2>';
echo		'<p>' . sprintf( __( 'Thanks for choosing %1$s - we hope you enjoy building with it.', 'avia_framework' ), $name ) . '</p>';
echo	'</div>';
