<?php
/**
 * Layout Builder Tab
 * ==================
 *
 * @since 4.8.2
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

global $avia_config, $avia_pages, $avia_elements;


$avia_elements[] = array(
			'slug'			=> 'builder',
			'type'			=> 'visual_group_start',
			'id'			=> 'avia_alb_general',
			'nodescription'	=> true
		);

$avia_elements[] = array(
			'slug'          => 'builder',
			'name'          => __( 'General Builder Options','avia_framework' ),
			'desc'          => '',
			'id'            => 'avia_builder_general',
			'type'          => 'heading',
			'std'           => '',
			'nodescription' => true
		);

$avia_elements[] = array(
			'slug'		=> 'builder',
			'name'		=> __( 'Disable Advanced Layout Builder Preview In Backend', 'avia_framework' ),
			'desc'		=> __( 'Check to disable the live preview of your advanced layout builder elements', 'avia_framework' ),
			'id'		=> 'preview_disable',
			'type'		=> 'checkbox',
			'std'		=> '',
			'globalcss'	=> true,
		);


$avia_elements[] = array(
			'slug'			=> 'builder',
			'type'			=> 'visual_group_end',
			'id'			=> 'avia_alb_general_close',
			'nodescription' => true
		);


$loack_alb = 'checkbox';

if( ! current_user_can( 'switch_themes' ) )
{
	$loack_alb = 'hidden';
}

$avia_elements[] = array(
			'slug'			=> 'builder',
			'type'			=> 'visual_group_start',
			'id'			=> 'avia_lock_alb',
			'nodescription'	=> true
		);

$avia_elements[] = array(
			'slug'		=> 'builder',
			'name'		=> __( 'Lock Advanced Layout Builder', 'avia_framework' ),
			'desc'		=> __( 'This removes the ability to move or delete existing template builder elements, or add new ones, for everyone who is not an administrator. The content of an existing element can still be changed by everyone who can edit that entry.', 'avia_framework' ),
			'id'		=> 'lock_alb',
			'type'		=> $loack_alb,
			'std'		=> '',
			'globalcss'	=> true
		);


$avia_elements[] = array(
			'slug'		=> 'builder',
			'name'		=> __( 'Lock Advanced Layout Builder For Admins As Well', 'avia_framework' ),
			'desc'		=> __( 'This will lock the elements for all administrators including you, to prevent accidental changing of a page layout. In order to change a page layout later, you will need to uncheck this option first', 'avia_framework' ),
			'id'		=> 'lock_alb_for_admins',
			'type'		=> $loack_alb,
			'std'		=> '',
			'required'	=> array( 'lock_alb', 'lock_alb' ),
			'globalcss'	=> true
		);

$avia_elements[] = array(
			'slug'			=> 'builder',
			'type'			=> 'visual_group_end',
			'id'			=> 'avia_lock_alb_close',
			'nodescription'	=> true
		);

$avia_elements[] =	array(
			'slug'	=> 'builder',
			'name'	=> __( 'Show advanced options', 'avia_framework' ),
			'desc'	=> __( 'Show special options for advanced users or developers, who know what they are doing.', 'avia_framework' ),
			'id'	=> 'avia_alb_show_advanced_options',
			'type'	=> 'checkbox',
			'std'	=> false
		);

$avia_elements[] = array(
			'slug'			=> 'builder',
			'type'			=> 'visual_group_start',
			'id'			=> 'avia_alb_developers',
			'nodescription'	=> true,
			'required'		=> array( 'avia_alb_show_advanced_options', '{contains_array}avia_alb_show_advanced_options' )
		);

$avia_elements[] = array(
			'slug'		=> 'builder',
			'name'		=> __( 'Hide Advanced Layout Builder Developer Options', 'avia_framework' ),
			'desc'		=> __( 'Activate to hide the developer options for template builder elements. (Usually located in the "advanced" tab of the element and containing options like custom IDs and CSS classes). More details can be found in our documentation: ', 'avia_framework' ) . '<a href="https://kriesi.at/documentation/enfold/intro-to-layout-builder/#developer-options" target="_blank" rel="noopener noreferrer">' . __( 'Intro to Layout Builder', 'avia_framework' ) . '</a>.',
			'id'		=> 'alb_developer_options',
			'type'		=> 'checkbox',
			'std'		=> '',
			'globalcss'	=> true,
			'required'	=> array( 'avia_alb_show_advanced_options', '{contains_array}avia_alb_show_advanced_options' )
		);

$avia_elements[] = array(
			'slug'		=> 'builder',
			'name'		=> __( 'Typography Input Fields', 'avia_framework' ),
			'desc'		=> __( 'Activate to replace predefined selectboxes with font sizes with text fields to use custom units. Only recommended for experienced users who know, what they are doing. This is in active beta (since 5.0.1).', 'avia_framework' ),
			'id'		=> 'alb_developer_ext_typo',
			'type'		=> 'checkbox',
			'std'		=> '',
			'globalcss'	=> true,
			'required'	=> array( 'avia_alb_show_advanced_options', '{contains_array}avia_alb_show_advanced_options' )
		);

$avia_elements[] = array(
			'slug'		=> 'builder',
			'name'		=> __( 'Debug Mode (Backend Only)', 'avia_framework' ),
			'desc'		=> __( 'Select to enable debug output and show ALB shortcodes in a text area below drag/drop canvas. Only recommended for experienced users and developers who need to get access to the generated shortcodes and know, what they are doing. Changes to this field might break the layout editor - so avoid making any changes there.', 'avia_framework' ),
			'id'		=> 'alb_developer_debug_mode',
			'type'		=> 'select',
			'std'		=> '',
			'no_first'	=> true,
			'globalcss'	=> true,
			'required'	=> array( 'avia_alb_show_advanced_options', '{contains_array}avia_alb_show_advanced_options' ),
			'subtype'	=> array(
								__( 'Disable debug mode (recommended)', 'avia_framework' )	=> '',
								__( 'Enable for admins only', 'avia_framework' )			=> 'debug-admins',
								__( 'Enable for all users', 'avia_framework' )				=> 'debug'
						)
		);

$avia_elements[] = array(
			'slug'		=> 'builder',
			'name'		=> __( 'Custom Color Palette', 'avia_framework' ),
			'desc'		=> __( 'Check if you want to define your custom color palette for the color selection popup in modal popup window options', 'avia_framework' ),
			'id'		=> 'alb_use_custom_colors',
			'type'		=> 'checkbox',
			'std'		=> '',
			'globalcss'	=> true,
			'required'	=> array( 'avia_alb_show_advanced_options', '{contains_array}avia_alb_show_advanced_options' )
		);

$desc  = __( 'You can enter up to 22 colors, enter each color in a new line in the order you like, either &quot;#efefef&quot; or &quot;rgba(0,0,0,0.3)&quot;.', 'avia_framework' ) . '<br />';
$desc .= __( 'Default color palette is:', 'avia_framework' ) . '<br /><br />' . implode( '<br />', $avia_config['default_alb_color_palette'] );

$avia_elements[] = array(
			'slug'		=> 'builder',
			'name'		=> __( 'Enter Your Custom Color Palette', 'avia_framework' ),
			'desc'		=> $desc,
			'id'		=> 'alb_custom_color_palette',
			'type'		=> 'textarea',
			'std'		=> '',
			'required'	=> array( 'alb_use_custom_colors', 'alb_use_custom_colors' ),
			'globalcss'	=> true
		);


$avia_elements[] = array(
			'slug'			=> 'builder',
			'type'			=> 'visual_group_end',
			'id'			=> 'avia_alb_developers_close',
			'nodescription'	=> true,
			'required'		=> array( 'avia_alb_show_advanced_options', '{contains_array}avia_alb_show_advanced_options' ),
		);

//
//	Removed - let's wait if we get more reports
//	===========================================
//
//
//$avia_elements[] = array(
//			'slug'			=> 'builder',
//			'type'			=> 'visual_group_start',
//			'id'			=> 'avia_alb_post_css',
//			'nodescription'	=> true
//		);
//
//$desc  = __( 'By default we add styling rules for ALB elements on a page/post/.. to a css file for this page/post/.. ( located in default WP uploads folder ../uploads/avia_posts_css/ ) - started with 4.8.4.', 'avia_framework' );
//$desc .= '<br />';
//$desc .= __( 'In rare cases if you use a cache plugin and encounter problems in layout we can add these rules to html &lt;style&gt;...&lt;/style&gt; tags instead.', 'avia_framework' );
//
//$avia_elements[] = array(
//			'slug'		=> 'builder',
//			'name'		=> __( 'CSS Styles Handling', 'avia_framework' ),
//			'desc'		=> $desc,
//			'id'		=> 'post_css_file_handling',
//			'type'		=> 'select',
//			'std'		=> '',
//			'no_first'	=> true,
//			'globalcss'	=> true,
//			'subtype'	=> array(
//								__( 'Use CSS files (recommended)', 'avia_framework' )	=> '',
//								__( 'Add to html style tags', 'avia_framework' )		=> 'html_style_tag',
//							)
//		);
//
//$avia_elements[] = array(
//			'slug'			=> 'builder',
//			'type'			=> 'visual_group_end',
//			'id'			=> 'avia_alb_post_css_close',
//			'nodescription'	=> true
//		);


$avia_elements[] = array(
			'slug'			=> 'builder',
			'type'			=> 'visual_group_start',
			'id'			=> 'avia_alb_options_toggles',
			'nodescription'	=> true
		);



$subtype = array(
				__( 'Use Toggle Feature', 'avia_framework' )						=> '',
				__( 'Disable Toggles and display all options', 'avia_framework' )	=> 'section_headers',
			);

/**
 * @since 4.7.3.1
 * @param boolean
 * @return boolean
 */
if( false !== apply_filters( 'avf_show_option_toggles_advanced', false ) )
{
	$subtype[ __( 'Show all options without section headers', 'avia_framework' ) ] = 'no_section_headers';
}

$avia_elements[] = array(
			'slug'		=> 'builder',
			'name'		=> __( 'Options Toggles In Modal Popup', 'avia_framework' ),
			'desc'		=> __( 'Select if you want to display toggles in modal windows for advanced layout builder elements or you prefer to see all options at once (old style)', 'avia_framework' ),
			'id'		=> 'alb_options_toggles',
			'type'		=> 'select',
			'std'		=> '',
			'no_first'	=> true,
			'globalcss'	=> true,
			'subtype'	=> $subtype
		);

$avia_elements[] = array(
			'slug'			=> 'builder',
			'type'			=> 'visual_group_end',
			'id'			=> 'avia_alb_options_toggles_close',
			'nodescription'	=> true
		);
