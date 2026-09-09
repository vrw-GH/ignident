<?php
/**
 * Demo Import Tab
 * ===============
 *
 * @since 4.8.2
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

global $avia_config, $avia_pages, $avia_elements;




$avia_elements[] = array(
						'slug'			=> 'demo',
						'name'			=> __( 'Download And Import Demo Files', 'avia_framework' ),
						'desc'			=> __( 'New to WordPress? Download any of our demos and import their sample pages to see how everything is built.', 'avia_framework' ) . '<br /><br />' .
										   __( 'We recommend to use a clean WP installation for importing a demo to avoid conflicts with existing content.', 'avia_framework' ) .
										   '<br/><br/><strong class="av-text-notice">' .
										   __( 'Notice: If you want to completely remove a demo installation after importing it, you can use a plugin like', 'avia_framework' ) . ' <a target="_blank" href="https://wordpress.org/plugins/wordpress-reset/">WordPress Reset</a></strong>',
						'id'			=> 'demoimportdescription',
						'std'			=> '',
						'type'			=> 'heading',
						'nodescription'	=> true
					);


include( AVIA_BASE . 'includes/admin/register-demo-import.php' );



