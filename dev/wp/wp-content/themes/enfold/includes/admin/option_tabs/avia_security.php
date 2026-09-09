<?php
/**
 * Security Tab
 * ============
 *
 * Holds settings for third party spam/bot protection services that are not
 * tied to a specific vendor tab (e.g. Google Services).
 *
 * @since 7.1.7
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

global $avia_config, $avia_pages, $avia_elements;



$avia_elements[] = array(
			'slug'	        => 'security',
			'type'          => 'visual_group_start',
			'id'            => 'avia_turnstile_group_start',
			'class'         => 'av-verify-button-container',
			'nodescription' => true
		);

$turnstile_link = 'https://kriesi.at/documentation/enfold/contact-form/#cloudflare-turnstile';
$turnstile_admin = 'https://dash.cloudflare.com/?to=/:account/turnstile';

$turnstile_desc  = __( 'Add Cloudflare Turnstile widget functionality to the theme to verify if a user is a human. Currently only Enfold contact forms are supported and you can choose for each form individually if you want to use Turnstile.', 'avia_framework' ) . '<br />';
$turnstile_desc .= sprintf( __( 'Info about <a href="%1$s" target="_blank" rel="noopener noreferrer">Cloudflare Turnstile</a>. You need to create <a href="%2$s" target="_blank" rel="noopener noreferrer">site and secret keys</a> for your site.', 'avia_framework' ), $turnstile_link, $turnstile_admin );

$avia_elements[] = array(
			'slug'          => 'security',
			'name'          => __( 'Cloudflare Turnstile', 'avia_framework' ),
			'desc'          => $turnstile_desc,
			'id'            => 'avia_turnstile',
			'type'          => 'heading',
			'std'           => '',
			'nodescription' => true
		);

$avia_elements[] = array(
			'slug'     => 'security',
			'name'     => __( 'Select If You Want To Use Cloudflare Turnstile', 'avia_framework' ),
			'desc'     => '',
			'id'       => 'avia_turnstile_active',
			'type'     => 'select',
			'no_first' => true,
			'std'      => '',
			'subtype'  => array(
								__( 'Disable Turnstile', 'avia_framework' ) => '',
								__( 'Enable Turnstile', 'avia_framework' )  => 'avia_turnstile_active'
							)
		);

$avia_elements[] = array(
			'slug'     => 'security',
			'name'     => __( 'Site Key', 'avia_framework' ),
			'desc'     => __( 'Enter the Cloudflare Turnstile site key here.', 'avia_framework' ),
			'id'       => 'avia_turnstile_pkey',
			'type'     => 'text',
			'std'      => '',
			'required' => array( 'avia_turnstile_active', 'avia_turnstile_active' ),
		);

$avia_elements[] = array(
			'slug'     => 'security',
			'name'     => __( 'Secret Key', 'avia_framework' ),
			'desc'     => __( 'Enter the Cloudflare Turnstile secret key here.', 'avia_framework' ),
			'id'       => 'avia_turnstile_skey',
			'type'     => 'text',
			'std'      => '',
			'required' => array( 'avia_turnstile_active', 'avia_turnstile_active' ),
		);

$avia_elements[] = array(
			'slug'           => 'security',
			'name'           => '',
			'desc'           => '',
			'id'             => 'avia_turnstile_key_verify',
			'type'           => 'verification_field',
			'std'            => '',
			'required'       => array( 'avia_turnstile_active', 'avia_turnstile_active' ),
			'force_callback' => true,
			'input_ids'      => array( 'avia_turnstile_pkey', 'avia_turnstile_skey' ),
			'ajax'           => 'av_turnstile_api_check',
			'js_callback'    => 'av_turnstile_js_api_check',
			'class'          => 'av_full_description',
			'button-label'   => __( 'Check API Keys', 'avia_framework' ),
			'button-relabel' => __( ' Check API Keys', 'avia_framework' )
		);

$avia_elements[] = array(
			'slug'      => 'security',
			'name'      => __( 'Last verified keys - hidden - used for internal use only', 'avia_framework' ),
			'desc'      => '',
			'id'        => 'avia_turnstile_verify_state',
			'type'      => 'hidden',
			'std'       => ''
		);

$avia_elements[] = array(
			'slug'          => 'security',
			'type'          => 'visual_group_end',
			'id'            => 'avia_turnstile_group_end',
			'nodescription' => true
		);
