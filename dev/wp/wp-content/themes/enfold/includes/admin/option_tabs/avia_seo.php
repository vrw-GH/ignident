<?php
/**
 * SEO Support Tab (Search Engine Support)
 * =======================================
 *
 * @since 4.8.2
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

global $avia_config, $avia_pages, $avia_elements;


$avia_elements[] = array(
			'slug'			=> 'seo',
			'name'			=> __( 'Search Engine Optimization Support','avia_framework' ),
			'desc'			=> __( 'The options here allow you to fine tune support of search engines or integration of search engine plugins.', 'avia_framework' ),
			'id'			=> 'seo_header',
			'std'			=> '',
			'type'			=> 'heading',
			'nodescription' => true
		);

$avia_elements[] = array(
			'slug'			=> 'seo',
			'type'			=> 'visual_group_start',
			'id'			=> 'avia_seo_index_start',
			'nodescription' => true
		);

$avia_elements[] = array(
			'slug'		=> 'seo',
			'name'		=> __( 'Meta Tag &quot;robots&quot;', 'avia_framework' ),
			'desc'		=> __( 'Select how Enfold will handle this header meta tag (index, follow). If you use a SEO plugin, you should leave this to plugin.', 'avia_framework' ),
			'id'		=> 'seo_robots',
			'type'		=> 'select',
			'std'		=> '',
			'no_first'	=> true,
			'globalcss'	=> true,
			'subtype'	=> array(
								__( 'Use Enfold default settings', 'avia_framework' )	=> '',
								__( 'Let SEO plugin set this tag', 'avia_framework' )	=> 'plugin',
							)
		);

$avia_elements[] = array(
			'slug'		=> 'seo',
			'name'		=> __( 'Automated Schema.org HTML Markup', 'avia_framework' ),
			'desc'		=> __( 'Adds basic Schema.org markup to help search engines understand your content. Turn it off if you add your own markup via a plugin or custom code.', 'avia_framework' ),
			'id'		=> 'markup',
			'type'		=> 'select',
			'std'		=> '',
			'no_first'	=> true,
			'globalcss'	=> true,
			'subtype'	=> array(
								__( 'Activated', 'avia_framework' )		=> '',
								__( 'Not activated', 'avia_framework' )	=> 'inactive'
							)
		);


$avia_elements[] = array(
			'slug'		=> 'seo',
			'name'		=> __( 'Preprocessing Shortcodes In Header', 'avia_framework' ),
			'desc'		=> __( 'Let SEO plugins process shortcodes while building the page header. Off by default because it can slow things down.', 'avia_framework' ),
			'id'		=> 'preprocess_shortcodes_in_header',
			'type'		=> 'select',
			'std'		=> '',
			'no_first'	=> true,
			'globalcss'	=> true,
			'subtype'	=> array(
							__( 'Do not allow to preprocess shortcodes', 'avia_framework' )		=> '',
							__( 'allow to preprocess shortcodes', 'avia_framework' )			=> 'preprocess_shortcodes_in_header'
						)
		);

$avia_elements[] = array(
			'slug'			=> 'seo',
			'type'			=> 'visual_group_end',
			'id'			=> 'avia_seo_index_end',
			'nodescription' => true
		);


$avia_elements[] = array(
			'slug'			=> 'seo',
			'type'			=> 'visual_group_start',
			'id'			=> 'avia_alb_seo_group_start',
			'nodescription'	=> true
		);

$avia_elements[] = array(
			'slug'		=> 'seo',
			'name'		=> __( 'Support ALB Elements To Qualify Links', 'avia_framework' ),
			'desc'		=> __( 'Add options to set link relationships (nofollow, sponsored, ugc) on Layout Builder elements.', 'avia_framework' ),
			'id'		=> 'alb_seo_qualify_links',
			'type'		=> 'checkbox',
			'std'		=> '',
			'globalcss'	=> true
		);

$avia_elements[] = array(
			'slug'			=> 'seo',
			'type'			=> 'visual_group_end',
			'id'			=> 'avia_alb_seo_group_close',
			'nodescription'	=> true
		);

