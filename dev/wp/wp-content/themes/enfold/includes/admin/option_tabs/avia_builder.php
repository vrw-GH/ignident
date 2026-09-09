<?php
/**
 * Layout Builder Tab
 * ==================
 *
 * Ordered by how often a setting is actually changed, not by how big the feature is:
 * what you look at every time you open the builder comes first, one time setup comes
 * later, developer settings last behind their toggle.
 *
 * @since 4.8.2
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

global $avia_config, $avia_pages, $avia_elements;


$avia_elements[] = array(
			'slug'          => 'builder',
			'name'          => __( 'Advanced Layout Builder Options','avia_framework' ),
			'desc'          => '',
			'id'            => 'avia_builder_general',
			'type'          => 'heading',
			'std'           => '',
			'nodescription' => true
		);


/* ------------------------------------------------------------------------------------
 *	Editing Experience
 * --------------------------------------------------------------------------------- */

$avia_elements[] = array(
			'slug'			=> 'builder',
			'name'			=> __( 'Editing Experience', 'avia_framework' ),
			'desc'			=> __( 'How the Layout Builder looks and behaves while you work on a page.', 'avia_framework' ),
			'id'			=> 'alb_header_editing',
			'type'			=> 'heading',
			'std'			=> '',
			'nodescription'	=> true
		);

$avia_elements[] = array(
			'slug'			=> 'builder',
			'type'			=> 'visual_group_start',
			'id'			=> 'avia_alb_editing',
			'nodescription'	=> true
		);


$desc  = __( 'Choose where the elements you can add to a page are shown in the Layout Builder.', 'avia_framework' );
$desc .= '<br /><br />';
$desc .= '<strong>' . __( 'Sidebar', 'avia_framework' ) . ':</strong> ';
$desc .=	__( 'A full height panel at the side of the screen with its own scrollbar. The elements stay in reach while you scroll through a long page.', 'avia_framework' );
$desc .= '<br />';
$desc .= '<strong>' . __( 'Toolbar', 'avia_framework' ) . ':</strong> ';
$desc .=	__( 'The classic bar across the top of the editor, as in earlier versions of Enfold.', 'avia_framework' );

$avia_elements[] = array(
			'slug'		=> 'builder',
			'name'		=> __( 'Position Of The Element Panel', 'avia_framework' ),
			'desc'		=> $desc,
			'id'		=> 'alb_element_panel_layout',
			'type'		=> 'select',
			'std'		=> 'sidebar',
			'no_first'	=> true,
			'subtype'	=> array(
								__( 'Sidebar at the side of the screen', 'avia_framework' )	=> 'sidebar',
								__( 'Toolbar across the top (classic)', 'avia_framework' )	=> 'toolbar',
							)
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
			'desc'		=> __( 'Group element options into collapsible sections, or show them all at once.', 'avia_framework' ),
			'id'		=> 'alb_options_toggles',
			'type'		=> 'select',
			'std'		=> '',
			'no_first'	=> true,
			'globalcss'	=> true,
			'subtype'	=> $subtype
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
			'id'			=> 'avia_alb_editing_close',
			'nodescription'	=> true
		);


/* ------------------------------------------------------------------------------------
 *	Permissions
 * --------------------------------------------------------------------------------- */

$avia_elements[] = array(
			'slug'			=> 'builder',
			'name'			=> __( 'Permissions', 'avia_framework' ),
			'desc'			=> __( 'Control who is allowed to change the structure of a layout.', 'avia_framework' ),
			'id'			=> 'alb_header_permissions',
			'type'			=> 'heading',
			'std'			=> '',
			'nodescription'	=> true
		);

$lock_alb_type = 'checkbox';

if( ! current_user_can( 'switch_themes' ) )
{
	$lock_alb_type = 'hidden';
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
			'desc'		=> __( 'Prevents non-administrators from adding, moving, or deleting elements. They can still edit the content of existing elements.', 'avia_framework' ),
			'id'		=> 'lock_alb',
			'type'		=> $lock_alb_type,
			'std'		=> '',
			'globalcss'	=> true
		);


$avia_elements[] = array(
			'slug'		=> 'builder',
			'name'		=> __( 'Lock Advanced Layout Builder For Admins As Well', 'avia_framework' ),
			'desc'		=> __( 'Locks the layout for everyone, including administrators, to prevent accidental changes. Uncheck this to edit layouts again.', 'avia_framework' ),
			'id'		=> 'lock_alb_for_admins',
			'type'		=> $lock_alb_type,
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


/* ------------------------------------------------------------------------------------
 *	Features
 * --------------------------------------------------------------------------------- */

$avia_elements[] = array(
			'slug'			=> 'builder',
			'name'			=> __( 'Features', 'avia_framework' ),
			'desc'			=> __( 'Turn on the larger Layout Builder features and choose which post types may use the builder. These are usually set once.', 'avia_framework' ),
			'id'			=> 'alb_header_features',
			'type'			=> 'heading',
			'std'			=> '',
			'nodescription'	=> true
		);

$avia_elements[] = array(
			'slug'			=> 'builder',
			'type'			=> 'visual_group_start',
			'id'			=> 'avia_alb_dynamic_content_group',
			'nodescription'	=> true
		);


$desc  = __( 'Select if you want to use dynamic content (e.g. post data, custom fields) and create modifiable custom layouts for post types.', 'avia_framework' );
$desc .= '<br /><br />';
$desc .= '<strong>';
$desc .=	__( 'After activating this feature you must reload backend to load the necessary menus to work with the &quot;Custom Layout Screens&quot;.', 'avia_framework' );
$desc .= '</strong>';
$desc .= '<br /><div class="av-text-notice">';
$desc .=	__( 'Attention when using caching plugins: Whenever you make changes to a &quot;Custom Layout&quot; please clear your server cache to show the changes.', 'avia_framework' );
$desc .= '</div>';

$info  = '';
$info .= __( 'We recommend to use ACF (Advanced Custom Field) plugin for a user friendly way to manage content of custom fields and custom post types:', 'avia_framework' );
$info .= '<a href="https://wordpress.org/plugins/advanced-custom-fields/" target="_blank" rel="noopener noreferrer"> ' . __( 'Download plugin from Wordpress', 'avia_framework' ) . '</a>';


$avia_elements[] = array(
			'slug'		=> 'builder',
			'name'		=> __( 'Custom Layout And Dynamic Content', 'avia_framework' ),
			'desc'		=> $desc,
			'info'		=> $info,
			'docu'		=> [
								'url'	=> 'https://kriesi.at/documentation/enfold/custom-layout-and-dynamic-content/',
								'title'	=> __( 'See documentation how to use it and to get more information.', 'avia_framework' )
							],
			'id'		=> 'alb_dynamic_content',
			'type'		=> 'select',
			'std'		=> 'alb_dynamic_content alb_custom_layout',
			'no_first'	=> true,
			'globalcss'	=> true,
			'subtype'	=> array(
								__( 'Disabled', 'avia_framework' )													=> '',
								__( 'Dynamic content only', 'avia_framework' )										=> 'alb_dynamic_content',
								__( 'Custom layout (admins only) and dynamic content', 'avia_framework' )			=> 'alb_dynamic_content alb_custom_layout',
								__( 'Custom layout (admins and editors) and dynamic content', 'avia_framework' )	=> 'alb_dynamic_content alb_custom_layout editors',
							)
		);

$numbers = array(
			__( 'Default (=100)', 'avia_framework' )	=> '',
			__( 'All', 'avia_framework' )				=> 0,
			__( 'Skip all', 'avia_framework' )			=> 'skip',
			'10'										=> 10
		);

for( $i = 50; $i <= 900; $i += 50 )
{
	$numbers[ $i ] = $i;
}

$desc  = __( 'Large sites might need a significant database query time for WP default custom fields resulting in a long loading time in backend. Selecting a limit will speed it up - the smaller the faster.', 'avia_framework' ) . '<br />';

$avia_elements[] = array(
			'slug'		=> 'builder',
			'name'		=> __( 'Limit Number Of WP Default Custom Fields', 'avia_framework' ),
			'desc'		=> $desc,
			'docu'		=> [
								'url'	=> 'https://kriesi.at/documentation/enfold/custom-layout-and-dynamic-content/#loading-wp-custom-fields-in-backend',
								'title'	=> __( 'Read more in documentation to get more information.', 'avia_framework' )
							],
			'id'		=> 'alb_dynamic_limit_cf',
			'type'		=> 'select',
			'std'		=> '',
			'no_first'	=> true,
			'globalcss'	=> true,
			'required'	=> array( 'alb_dynamic_content', '{contains}alb_dynamic_content' ),
			'subtype'	=> $numbers
		);


$desc  = __( 'By default ALB is activated for post types page, post, portfolio, product, alb_elements, alb_custom_layout. Here you can add more post types to use with ALB. Enter each post type in a new line.', 'avia_framework' );

$att1  = __( 'LIMITATION: It might be necessary to make customizations in frontend templates for 3rd party post types to work with ALB - this is not a bug.', 'avia_framework' ) . '<br /><br />';
$att1 .= __( 'When using ACF plugin to add custom post types the post type is set in option &quot;Post Type Key&quot; and it must be set to public.', 'avia_framework' );

$avia_elements[] = array(
			'slug'		=> 'builder',
			'name'		=> __( 'Activate Your Custom Post Types For ALB', 'avia_framework' ),
			'desc'		=> $desc,
			'attention'	=> $att1,
			'id'		=> 'alb_active_post_types',
			'type'		=> 'textarea',
			'std'		=> '',
			'globalcss'	=> true
		);


$avia_elements[] = array(
			'slug'			=> 'builder',
			'type'			=> 'visual_group_end',
			'id'			=> 'avia_alb_dynamic_content_group_close',
			'nodescription'	=> true
		);


/* ------------------------------------------------------------------------------------
 *	Developer Settings
 * --------------------------------------------------------------------------------- */

$avia_elements[] = array(
			'slug'			=> 'builder',
			'name'			=> __( 'Developer Settings', 'avia_framework' ),
			'desc'			=> __( 'Settings for developers and advanced users. Leave the option below unchecked if you do not need them.', 'avia_framework' ),
			'id'			=> 'alb_header_developer',
			'type'			=> 'heading',
			'std'			=> '',
			'nodescription'	=> true
		);

$avia_elements[] =	array(
			'slug'	=> 'builder',
			'name'	=> __( 'Show Advanced Options', 'avia_framework' ),
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
			'desc'		=> __( 'Hide developer options such as custom IDs and CSS classes, found on the Advanced tab of each element.', 'avia_framework' ),
			'docu'		=> [
								'url'	=> 'https://kriesi.at/documentation/enfold/intro-to-layout-builder/#developer-options',
								'title'	=> __( 'Read more in documentation: Intro to Layout Builder.', 'avia_framework' )
							],
			'id'		=> 'alb_developer_options',
			'type'		=> 'checkbox',
			'std'		=> '',
			'globalcss'	=> true,
			'required'	=> array( 'avia_alb_show_advanced_options', '{contains_array}avia_alb_show_advanced_options' )
		);

$avia_elements[] = array(
			'slug'		=> 'builder',
			'name'		=> __( 'Typography Input Fields', 'avia_framework' ),
			'desc'		=> __( 'Replace the font-size dropdowns with text fields so you can enter custom units. For advanced users.', 'avia_framework' ),
			'id'		=> 'alb_developer_ext_typo',
			'type'		=> 'checkbox',
			'std'		=> '',
			'globalcss'	=> true,
			'required'	=> array( 'avia_alb_show_advanced_options', '{contains_array}avia_alb_show_advanced_options' )
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
			'slug'		=> 'builder',
			'name'		=> __( 'Debug Mode (Backend Only)', 'avia_framework' ),
			'desc'		=> __( 'Show the generated shortcodes in a text area below the editor. For developers only — editing them there can break the layout editor.', 'avia_framework' ),
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
