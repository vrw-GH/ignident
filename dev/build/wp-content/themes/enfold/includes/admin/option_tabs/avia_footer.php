<?php
/**
 * Footer Tab
 * ==========
 *
 * @since 4.8.2
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

global $avia_config, $avia_pages, $avia_elements;

$desc1  = '<strong><br />';
$desc1 .=		__( 'Info: If a footer page is selected and no &quot;Page Based Footer&quot; option is selected, the selected page will show a message in the page list, that this option is inactive, so that editors are reminded that it is a &quot;special&quot; page and making changes might break intended site layout when reactivated.', 'avia_framework' ) . ' ';
$desc1 .=		__( 'To disable this message in the page list please change your page selection to &quot;Select Page&quot; so no page is selected and then disable "Page Based Footer" option.', 'avia_framework' );
$desc1 .= '</strong>';

$avia_elements[] = array(
			'slug'		=> 'footer',
			'name'		=> __( 'Default Footer &amp; Socket Settings', 'avia_framework' ),
			'desc'		=> __( 'Do you want to display the footer widgets &amp; footer socket or a page content as footer? This default setting can be changed individually for each page.', 'avia_framework' ) . $desc1,
			'id'		=> 'display_widgets_socket',
			'type'		=> 'select',
			'std'		=> 'all',
			'no_first'	=> true,
			'globalcss'	=> true,
			'subtype'	=> array(
								__( 'Widget based footer options', 'avia_framework' ) => array(
										__( 'Display the footer widgets & socket', 'avia_framework' )			=> 'all',
										__( 'Display only the footer widgets (no socket)', 'avia_framework' )	=> 'nosocket',
										__( 'Display only the socket (no footer widgets)', 'avia_framework' )	=> 'nofooterwidgets',
										__( "Don't display the socket & footer widgets", 'avia_framework' )		=> 'nofooterarea',
								),

								__( 'Page based Footer options', 'avia_framework' ) => array(
										__( 'Select a page to replace footer and keep socket','avia_framework' )	=> 'page_in_footer_socket',
										__( 'Select a page to replace both footer and socket','avia_framework' )	=> 'page_in_footer',
								)
							)
		);

$desc2  = __( 'Select a page to display the content of this page in the footer area. You may also use pages created with the advanced layout builder.', 'avia_framework' ) . '<br />';
$desc2 .= '<strong>' . __( 'LIMITATION:', 'avia_framework' ) . ' ';
$desc2 .=		__( 'Using ALB elements with page specific content (e.g. like share links for social media like facebook, X, ..) or custom field content will use data of this page and NOT from the page where this footer page is used.', 'avia_framework' );
$desc2 .= '</strong>';


$avia_elements[] = array(
			'slug'			=> 'footer',
			'name'			=> __( 'Select Page', 'avia_framework' ),
			'desc'			=> $desc2,
			'id'			=> 'footer_page',
			'type'			=> 'select',
			'subtype'		=> 'page',
			'std'			=> '',
			'with_first'	=> true,
			'required'		=> array( 'display_widgets_socket', '{contains_array}page_in_footer_socket;page_in_footer' ),
			'class'			=> 'avia-style',
			'globalcss'		=> true
		);

$avia_elements[] = array(
			'slug'			=> 'footer',
			'type'			=> 'visual_group_start',
			'id'			=> 'avia_footer_behavior_start',
			'nodescription'	=> true,
			'required'		=> array( 'display_widgets_socket', '{contains_array}all;nosocket;nofooterwidgets;page_in_footer_socket;page_in_footer' ),
		);

$desc  = __( 'Select the behavior of the footer when user scrolls the page. Curtain effect will hide or show footer content when page is scrolled.', 'avia_framework' ) . ' ';
$desc .= __( 'Single page settings will use the option settings from here when selecting theme default in the metabox options.', 'avia_framework' );
$desc .= '<br /><strong>';
$desc .=	__( 'Only supported for stretched layout.', 'avia_framework' );
$desc .= '</strong>';

$avia_elements[] = array(
			'slug'		=> 'footer',
			'name'		=> __( 'Footer Behavior (currently in beta)', 'avia_framework' ),
			'desc'		=> $desc,
			'id'		=> 'footer_behavior',
			'type'		=> 'select',
			'std'		=> '',
			'no_first'	=> true,
//			'required'	=> array( 'color-body_style', 'stretched' ),
			'globalcss'	=> true,
			'subtype'	=> array(
								__( 'Scroll with page (default)', 'avia_framework' )	=> '',
								__( 'Sticky with curtain effect', 'avia_framework' )	=> 'curtain_footer'
							)
		);

/**
 * @since 4.8.6.3
 * @param boolean $show_curtains_media_option
 * @return boolean|mixed				true to show option
 */
if( true === apply_filters( 'avf_show_curtains_media_option', false ) )
{
	$avia_elements[] = array(
			'slug'		=> 'footer',
			'name'		=> __( 'Media Switch Back', 'avia_framework' ),
			'desc'		=> __( 'Select to switch back to scroll footer content with page when footer height exceeds selected limit.', 'avia_framework' ),
			'id'		=> 'curtains_media',
			'type'		=> 'select',
			'std'		=> '80',
			'no_first'	=> true,
			'required'	=> array( 'footer_behavior', 'curtain_footer' ),
			'globalcss'	=> true,
			'subtype'	=> array(
								__( '10% of viewport height', 'avia_framework' )				=> '10',
								__( '20% of viewport height', 'avia_framework' )				=> '20',
								__( '30% of viewport height', 'avia_framework' )				=> '30',
								__( '40% of viewport height', 'avia_framework' )				=> '40',
								__( '50% of viewport height', 'avia_framework' )				=> '50',
								__( '60% of viewport height', 'avia_framework' )				=> '60',
								__( '70% of viewport height', 'avia_framework' )				=> '70',
								__( '80% of viewport height (default)', 'avia_framework' )		=> '80',
								__( '90% of viewport height', 'avia_framework' )				=> '90',
//								__( 'smaller 989px (Tablets Landscape)', 'avia_framework' )		=> 'av-curtain-medium',
//								__( 'smaller 767px (Tablets Portrait)', 'avia_framework' )		=> 'av-curtain-small',
//								__( 'smaller 479px (Smartphone Portrait)', 'avia_framework' )	=> 'av-curtain-mini',
							)
		);
}
else
{
	$avia_elements[] = array(
			'slug'		=> 'footer',
			'id'		=> 'curtains_media',
			'type'		=> 'hidden',
			'std'		=> '80'
		);
}

$avia_elements[] = array(
			'slug'			=> 'footer',
			'type'			=> 'visual_group_end',
			'id'			=> 'avia_footer_behavior_end',
			'nodescription' => true,
			'required'		=> array( 'display_widgets_socket', '{contains_array}all;nosocket;nofooterwidgets;page_in_footer_socket;page_in_footer' ),
		);

$avia_elements[] = array(
			'slug'		=> 'footer',
			'name'		=> __( 'Footer Columns', 'avia_framework' ),
			'desc'		=> __( 'How many columns should be displayed in your footer', 'avia_framework' ),
			'id'		=> 'footer_columns',
			'type'		=> 'select',
			'std'		=> '4',
			'required'	=> array( 'display_widgets_socket', '{contains_array}all;nosocket' ),
			'globalcss'	=> true,
			'subtype'	=> array(
								__( '1', 'avia_framework' ) => '1',
								__( '2', 'avia_framework' ) => '2',
								__( '3', 'avia_framework' ) => '3',
								__( '4', 'avia_framework' ) => '4',
								__( '5', 'avia_framework' ) => '5',
								__( '6', 'avia_framework' ) => '6'
							)
		);

$avia_elements[] = array(
			'slug'		=> 'footer',
			'name'		=> __( 'Copyright', 'avia_framework' ),
			'desc'		=> __( 'Add a custom copyright text at the bottom of your site. eg:', 'avia_framework' ) . '<br/><strong>&copy; ' . __( 'Copyright','avia_framework' ) . '  - ' . get_bloginfo( 'name' ) . '</strong>',
			'id'		=> 'copyright',
			'type'		=> 'text',
			'std'		=> '',
			'required'	=> array( 'display_widgets_socket', '{contains_array}all;nofooterwidgets;page_in_footer_socket' ),
			'globalcss'	=> true
		);


$avia_elements[] = array(
			'slug'		=> 'footer',
			'name'		=> __( 'Social Icons', 'avia_framework' ),
			'desc'		=> __( 'Check to display the social icons defined in', 'avia_framework' ) .
								" <a href='#goto_social'>" .
								__( 'Social Profiles', 'avia_framework' ) .
								'</a> ' .
								 __( 'in your socket', 'avia_framework' ),
			'id'		=> 'footer_social',
			'required'	=> array( 'display_widgets_socket', '{contains_array}all;nofooterwidgets;page_in_footer_socket' ),
			'type'		=> 'checkbox',
			'std'		=> '',
			'globalcss'	=> true,
		);


