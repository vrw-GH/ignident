<?php
/**
 * One Click Accessibility Plugin Tab
 * ==================================
 *
 * @since 4.8.7
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

global $avia_config, $avia_pages, $avia_elements;

$desc  = __( 'Make your site more accessible for people with disabilities.', 'avia_framework' ) . '<br /><br />';
$desc .= '<strong class="av-text-notice">';
$desc .=	__( 'PLEASE NOTE:', 'avia_framework' ) . '<br /><br />';
$desc .=	__( 'WE DO NOT GUARANTEE TO FULFILL ANY LEGAL RULES FOR ACCESSIBILITY !!!', 'avia_framework' );
$desc .= '</strong><br />';
$desc .= __( 'It is the responsibility of the site owner to design the layout and content of the site to fulfill any legal rules that their site may be subject to. Please be aware that not all theme functions and ALB elements can support all accessibility enhancements for all types of disabilities. Please check this carefully.', 'avia_framework' ) . '<br /><br />';
$desc .= __( 'The following options activate theme integrated support. With 4.8.8 we are at the beginning of a step by step process to enhance this. Any feedback is appreciated to improve it.', 'avia_framework' );

$avia_elements[] = array(
			'slug'		=> 'accessibility',
			'name'		=> __( 'Accessibility Support', 'avia_framework' ),
			'desc'		=> $desc,
			'id'		=> 'accessibility_header',
			'type'		=> 'heading',
			'nodescription' => true
		);

$avia_elements[] = array(
			'slug'			=> 'accessibility',
			'type'			=> 'visual_group_start',
			'id'			=> 'accessibility_theme_start',
			'nodescription' => true
		);

$avia_elements[] = array(
			'slug'			=> 'accessibility',
			'name'			=> __( 'Accessibility Conformance Level', 'avia_framework' ),
			'desc'			=> __( 'Choose theme accessibility conformance level. Please note that your layout may look a bit different depending on your choice.', 'avia_framework' ),
			'id'			=> 'accessibility_conformance_option',
			'type'			=> 'select',
			'std'			=> '',
			'no_first'		=> true,
			'global'		=> true,
			'subtype'		=> array(
									__( 'Disabled', 'avia_framework' )		=> '',
									__( 'A (Lowest)', 'avia_framework' )	=> 'a_level',
									__( 'AA (Midrange)', 'avia_framework' )	=> 'aa_level',
									__( 'AAA (Highest)', 'avia_framework' )	=> 'aaa_level',
								)
		);

$avia_elements[] = array(
			'slug'			=> 'accessibility',
			'type'			=> 'visual_group_end',
			'id'			=> 'accessibility_theme_close',
			'nodescription' => true
		);



$desc  = __( 'These open source plugins allow you to easily make your site more accessible for people with disabilities.', 'avia_framework' ) . '<br /><br />';
$desc .= '<strong class="av-text-notice">';
$desc .=	__( 'THESE PLUGINS DO NOT GUARANTEE TO FULFILL ANY LEGAL RULES FOR ACCESSIBILITY !!!', 'avia_framework' );
$desc .= '</strong><br />';
$desc .= __( 'It is the responsibility of the site owner to design the layout and content of the site to fulfill any legal rules that their site may be subject to. Please be aware that not all theme functions and ALB elements can support all accessibility enhancements for all types of disabilities. Please check this carefully.', 'avia_framework' ) . '<br /><br />';
$desc .= __( 'With 4.8.7 we started to support stylings in menus.', 'avia_framework' );

$avia_elements[] = array(
			'slug'		=> 'accessibility',
			'name'		=> __( '3rd Party Accessibility Support', 'avia_framework' ),
			'desc'		=> $desc,
			'id'		=> 'accessibility_header_extern',
			'type'		=> 'heading',
			'nodescription' => true
		);

$avia_elements[] = array(
			'slug'			=> 'accessibility',
			'type'			=> 'visual_group_start',
			'id'			=> 'accessibility_container_start',
			'nodescription' => true
		);


$desc  = __( 'We were able to detect this plugin. Nothing left to do here. Please keep your plugin up to date.', 'avia_framework' ) . '<br /><br />';

$desc_opt = __( 'Options for setup are found here:', 'avia_framework' ) . '<br />';

$desc1  = '<ul>';
$desc1 .=	'<li>' . __( 'Simple to use with only a few options', 'avia_framework' ) . '</li>';
$desc1 .= '</ul>';

$desc_opt_pojo  = $desc_opt;
$desc_opt_pojo .= '<ul>';
$desc_opt_pojo .=	'<li>' . sprintf( __( '<a href="%s" rel="noopener noreferrer" target="_blank">General Settings</a>', 'avia_framework' ), admin_url( 'admin.php?page=accessibility-settings' ) ) . '</li>';
$desc_opt_pojo .=	'<li>' . sprintf( __( '<a href="%s" rel="noopener noreferrer" target="_blank">Frontend Toolbar Settings</a>', 'avia_framework' ), admin_url( 'admin.php?page=accessibility-toolbar' ) ) . '</li>';
$desc_opt_pojo .=	'<li>' . sprintf( __( '<a href="%s" rel="noopener noreferrer" target="_blank">Frontend Customizer</a>', 'avia_framework' ), admin_url( 'customize.php?autofocus[section]=accessibility' ) ) . '</li>';
$desc_opt_pojo .= '</ul>';

$desc_opt_wp_a  = $desc_opt;
$desc_opt_wp_a .= '<ul>';
$desc_opt_wp_a .=	'<li>' . sprintf( __( '<a href="%s" rel="noopener noreferrer" target="_blank">Settings</a>', 'avia_framework' ), admin_url( 'admin.php/options-general.php?page=wp-accessibility%2Fwp-accessibility.php' ) ) . '</li>';
$desc_opt_wp_a .= '</ul>';

$avia_elements[] = array(
			'slug'			=> 'accessibility',
			'name'			=> __( 'Accessibility Plugin', 'avia_framework' ),
			'desc'			=> __( 'These free plugins provides accessibility support in frontend. You can download and activate the plugin here.', 'avia_framework' ),
			'id'			=> 'accessibility_plugin_check',
			'type'			=> 'plugin_check',
			'nodescription'	=> true,
			'recommend_count'	=> -1,
			'no_found'		=> __( 'We were not able to detect this plugin.', 'avia_framework' ),
			'found'			=> $desc,
			'too_many'		=>__( 'We were able to detect multiple active accessibility plugins. It is recommended to use only one!', 'avia_framework' ),
			'plugins'		=> array(
						'One Click Accessibility' => array(
												'download'		=> 'pojo-accessibility',
												'file'			=> 'pojo-accessibility/pojo-accessibility.php',
												'desc'			=> $desc1,
												'target'		=> '',
												'desc_active'	=> $desc_opt_pojo
									),
						'WP Accessibility'	=> array(
												'download'		=> 'wp-accessibility',
												'file'			=> 'wp-accessibility/wp-accessibility.php',
												'desc'			=> $desc1,
												'target'		=> '',
												'desc_active'	=> $desc_opt_wp_a
									)
						)
		);

$avia_elements[] = array(
			'slug'			=> 'accessibility',
			'type'			=> 'visual_group_end',
			'id'			=> 'accessibility_container_close',
			'nodescription' => true
		);

