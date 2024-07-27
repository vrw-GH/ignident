<?php
/**
 * General Styling Tab
 * ===================
 *
 * @since 4.8.2
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

global $avia_config, $avia_pages, $avia_elements, $avia_admin_values;


$avia_elements[] = array(
			'slug'		=> 'styling',
			'name'		=> __( 'Select A Predefined Color Scheme', 'avia_framework' ),
			'desc'		=> __( 'Choose a predefined color scheme here. You can edit the settings of the scheme below then.', 'avia_framework' ),
			'id'		=> 'color_scheme',
			'type'		=> 'link_controller',
			'std'		=> 'Blue',
			'class'		=> 'link_controller_list',
			'subtype'	=> $styles
		);

$avia_elements[] = array(
			'slug'	=> 'styling',
			'id' 	=> 'default_slideshow_target',
			'type' 	=> 'target',
			'std' 	=> "
					<style type='text/css'>

						#boxed .live_bg_wrap{ padding:0 23px;   border:1px solid #e1e1e1; background-position: top center;}
						#av-framed-box .live_bg_wrap{ padding:23px;   border:1px solid #e1e1e1; background-position: top center;}
						.live_bg_small{font-size:10px; color:#999;     height: 23px; display: block;}
						.live_bg_wrap{ padding: 0; background:#f8f8f8; overflow:hidden; background-position: top center;}
						.live_bg_wrap div{overflow:hidden; position:relative;}
						#avia_options_page .live_bg_wrap h3{margin: 0 0 5px 0 ; color:inherit; font-size:25px;}
						#avia_options_page .live_bg_wrap .main_h3{font-weight:bold; font-size:25px;  }
						.border{border:1px solid; border-bottom-style:none; border-bottom-width:0; padding:13px; width:100%;}
						#av-framed-box .border{}

						.live_header_color {position: relative;width: 100%;left: }
						.bg2{border:1px solid; margin:4px; display:block; float:right; padding:15px; }
						.content_p{display:block; float:left; width: 100%;}
						.live-socket_color{font-size:11px;}
						.live-footer_color a{text-decoration:none;}
						.live-socket_color a{text-decoration:none;  position:absolute; top:28%; right:13px;}

						#avia_preview .webfont_google_webfont{  font-weight:normal; }
						.webfont_default_font{  font-weight:normal; font-size:13px; line-height:1.7em;}

						div .link_controller_list a{ width:113px; font-size:13px;}
						.avia_half{width: 50%; float:left; min-height:210px;}
						.avia_half .bg2{float:none; margin-left:0;}
						.avia_half_2{border-left:none; padding-left:14px;}
						#av-framed-box #header_left  .avia_half { width: 179px; height:250px;}
						.live-slideshow_color{text-align:center;}
						.text_small_outside{position:relative; top:-15px; display:block; left: 10px;}

						#header_left .live-header_color{ float:left;  width:30%; min-height: 424px; border-bottom:1px solid; border-right: none;}
						#header_right .live-header_color{float:right; width:30%; min-height: 424px; border-bottom:1px solid; border-left:  none;}
						#header_left .live-header_color .bg2,
						#header_right .live-header_color .bg2,
						#header_right .av_header_block_1,
						#header_left .av_header_block_1{
							float:none;
							width:100%;
						}
						.av-sub-logo-area{overflow:hidden;}

						#boxed #header_left .live-header_color, #boxed #header_right .live-header_color{min-height: 424px; }
						#header_right .avia_half, #header_left .avia_half{min-height: 250px;}
						#boxed .live-socket_color{border-bottom:1px solid;}
						.av_header_block_1{width:70%; float:left;}
						.live-header_color .bg2{width:30%; margin: 15px 0 0 0;}
						#av-framed-box .live-socket_color.border{border-bottom-style:solid; border-bottom-width:1px;}
					</style>





					<small class='live_bg_small'>{$frontend_label}</small>

					<div id='avia_preview' class='live_bg_wrap webfont_default_font'>
					<div class='avprev-design-container'>
					<!--<small class='text_small_outside'>Next Event: in 10 hours 5 minutes.</small>-->


						<div class='live-header_color border'>
							<div class='av_header_block_1'>
								<h3 class='heading webfont_google_webfont'>Logo Area Heading</h3>
								<span class='text'>Active Menu item | </span>
								<span class='meta'>Inactive Menu item</span><br/>
								<a class='a_link' href='#'>custom text link</a>
								<a class='an_activelink' href='#'>hovered link</a>
							</div>
							<div class='bg2'>Highlight Background + Border Color</div>
						</div>

						<div class='av-sub-logo-area'>



						<div class='live-main_color border avia_half'>
							<h3 class='webfont_google_webfont main_h3 heading'>Main Content heading</h3>
								<p class='content_p'>This is default content with a default heading. Font color, headings and link colors can be choosen below. <br/>
									<a class='a_link' href='#'>A link</a>
									<a class='an_activelink' href='#'>A hovered link</a>
									<span class='meta'>Secondary Font</span>

								</p>

								<div class='bg2'>Highlight Background + Border Color</div>
						</div>



						<div class='live-alternate_color border avia_half avia_half_2'>
								<h3 class='webfont_google_webfont main_h3 heading'>Alternate Content Area</h3>
								<p class='content_p'>This is content of an alternate content area. Choose font color, headings and link colors below. <br/>
									<a class='a_link' href='#'>A link</a>
									<a class='an_activelink' href='#'>A hovered link</a>
									<span class='meta'>Secondary Font</span>

								</p>

								<div class='bg2'>Highlight Background + Border Color</div>
						</div>

						<div class='live-footer_color border'>
							<h3 class='webfont_google_webfont heading'>Demo heading (Footer)</h3>
							<p>This is text on the footer background</p>
							<a class='a_link' href='#'>Link | Link 2</a>
							<span class='meta'> | Secondary Font</span>

						</div>

						<div class='live-socket_color border'>Socket Text <a class='a_link' href='#'>Link | Link 2</a>
													<span class='meta'> | Secondary Font</span>

						</div>
					</div>
					</div>
					</div>

					",
			'nodescription' => true
		);



//START TAB CONTAINER
$avia_elements[] = array(
				'slug'			=> 'styling',
				'type'			=> 'visual_group_start',
				'id'			=> 'avia_styling_colorset_start',
				'nodescription'	=> true,
				'class'			=> 'avia_tab_container avia_set'
			);


/**
 * Create color sets for #header, Main Content, Secondary Content, Footer, Socket, Slideshow
 */

$colorsets = $avia_config['color_sets'];
$iterator = 1;


foreach( $colorsets as $set_key => $set_value )
{
	$iterator ++;

	// START TAB
	$avia_elements[] = array(
				'slug'			=> 'styling',
				'name'			=> $set_value,
				'type'			=> 'visual_group_start',
				'id'			=> 'avia_styling_colorset_tab_' . $iterator . '_start',
				'class'			=> 'avia_tab avia_tab' . $iterator,
				'nodescription'	=> true
			);

	$avia_elements[] = array(
				'slug'		=> 'styling',
				'name'		=> $set_value .' '. __( 'Background Color', 'avia_framework' ),
				'desc'		=> __( 'Default Background color', 'avia_framework' ),
				'id'		=> "colorset-$set_key-bg",
				'type'		=> 'colorpicker',
				'std'		=> '#ffffff',
				'class'		=> 'av_2columns av_col_1',
				'target'	=> array( "default_slideshow_target::.live-$set_key::background-color" ),
				'globalcss'	=> true
			);

	$avia_elements[] = array(
				'slug'		=> 'styling',
				'name'		=> __( 'Alternate Background Color', 'avia_framework' ),
				'desc'		=> __( 'Alternate Background for menu hover, tables etc', 'avia_framework' ),
				'id'		=> "colorset-$set_key-bg2",
				'type'		=> 'colorpicker',
				'std'		=> '#f8f8f8',
				'class'		=> 'av_2columns av_col_2',
				'target'	=> array( "default_slideshow_target::.live-$set_key .bg2::background-color" ),
				'globalcss'	=> true
			);

	$avia_elements[] = array(
				'slug'		=> 'styling',
				'name'		=> __( 'Primary Color', 'avia_framework' ),
				'desc'		=> __( 'Font color for links, dropcaps and other elements', 'avia_framework' ),
				'id'		=> "colorset-$set_key-primary",
				'type'		=> 'colorpicker',
				'std'		=> '#719430',
				'class'		=> 'av_2columns av_col_1',
				'target'	=> array( "default_slideshow_target::.live-$set_key .a_link, .live-$set_key-wrap-top::color,border-color" ),
				'globalcss'	=> true
			);

	$avia_elements[] = array(
				'slug'		=> 'styling',
				'name'		=> __( 'Highlight Color', 'avia_framework' ),
				'desc'		=> __( 'Secondary color for link and button hover, etc', 'avia_framework' ).'<br/>',
				'id'		=> "colorset-$set_key-secondary",
				'type'		=> 'colorpicker',
				'std'		=> '#8bba34',
				'class'		=> 'av_2columns av_col_2',
				'target'	=> "default_slideshow_target::.live-$set_key .an_activelink::color",
				'globalcss'	=> true
			);


	$avia_elements[] = array(
				'slug'		=> 'styling',
				'name'		=> $set_value . ' ' . __( 'Font Color', 'avia_framework' ),
				'id'		=> "colorset-$set_key-color",
				'type'		=> 'colorpicker',
				'std'		=> '#000000',
				'class'		=> 'av_2columns av_col_1',
				'target'	=> array( "default_slideshow_target::.live-$set_key::color" ),
				'globalcss'	=> true
			);

	$avia_elements[] = array(
				'slug'		=> 'styling',
				'name'		=> $set_value . ' ' . __( 'Secondary Font Color', 'avia_framework' ),
				'id'		=> "colorset-$set_key-meta",
				'type'		=> 'colorpicker',
				'std'		=> '#969696',
				'class'		=> 'av_2columns av_col_2',
				'target'	=> array( "default_slideshow_target::.live-$set_key .meta::color" ),
				'globalcss'	=> true
			);

	$avia_elements[] = array(
				'slug'		=> 'styling',
				'name'		=> $set_value . ' ' . __( 'Heading Color', 'avia_framework' ),
				'id'		=> "colorset-$set_key-heading",
				'type'		=> 'colorpicker',
				'std'		=> '#000000',
				'class'		=> 'av_2columns av_col_1',
				'target'	=> array( "default_slideshow_target::.live-$set_key .heading::color" ),
				'globalcss'	=> true
			);

	$avia_elements[] = array(
				'slug'		=> 'styling',
				'name'		=> __( 'Border Colors', 'avia_framework' ),
				'id'		=> "colorset-$set_key-border",
				'type'		=> 'colorpicker',
				'std'		=> '#ebebeb',
				'class'		=> 'av_2columns av_col_2',
				'target'	=> array( "default_slideshow_target::.live-$set_key.border, .live-$set_key .bg2::border-color" ),
				'globalcss'	=> true
			);

	$avia_elements[] = array(
				'slug'			=> 'styling',
				'id'			=> 'hr_colorset_' . $set_key,
				'type'			=> 'hr',
				'nodescription'	=> true
			);


	$avia_elements[] = array(
				'slug'			=> 'styling',
				'name'			=> __( 'Background Image', 'avia_framework' ),
				'desc'			=> __( 'The background image of your', 'avia_framework' ) . ' ' . $set_value . '<br/>',
				'id'			=> "colorset-$set_key-img",
				'type'			=> 'select',
				'std'			=> '',
				'no_first'		=> true,
				'class'			=> 'av_2columns av_col_1',
				'subtype'		=> array(
										__( 'No Background Image', 'avia_framework' )	=> '',
										__( 'Upload custom image', 'avia_framework' )	=> 'custom'
									),
				'target'		=> array( "default_slideshow_target::.live-$set_key::background-image" ),
				'folder'		=> 'images/background-images/',
				'folderlabel'	=> '',
				'group'			=> 'Select predefined pattern',
				'globalcss'		=> true
			);


	$avia_elements[] = array(
				'slug'		=> 'styling',
				'name'		=> __( 'Custom Background Image', 'avia_framework' ),
				'desc'		=> __( 'Upload a BG image for your', 'avia_framework' ) . ' ' . $set_value . '<br/>',
				'id'		=> "colorset-$set_key-customimage",
				'type'		=> 'upload',
				'std'		=> '',
				'class'		=> 'set_blank_on_hide av_2columns av_col_2',
				'label'		=> __( 'Use Image', 'avia_framework' ),
				'required'	=> array( "colorset-$set_key-img", 'custom' ),
				'target'	=> array( "default_slideshow_target::.live-$set_key::background-image" ),
				'globalcss'	=> true
			);


	$avia_elements[] = array(
				'slug'		=> 'styling',
				'name'		=> __( 'Position Of The Image', 'avia_framework' ),
				'desc'		=> '',
				'id'		=> "colorset-$set_key-pos",
				'type'		=> 'select',
				'std'		=> 'top left',
				'no_first'	=> true,
				'class'		=> 'av_2columns av_col_1',
				'required'	=> array( "colorset-$set_key-img", '{true}' ),
				'target'	=> array( "default_slideshow_target::.live-$set_key::background-position" ),
				'globalcss'	=> true,
				'subtype'	=> array(
									__( 'Top Left', 'avia_framework' )		=> 'top left',
									__( 'Top Center', 'avia_framework' )	=> 'top center',
									__(	'Top Right', 'avia_framework' )		=> 'top right',
									__( 'Bottom Left', 'avia_framework' )	=> 'bottom left',
									__( 'Bottom Center', 'avia_framework' )	=> 'bottom center',
									__(	'Bottom Right', 'avia_framework' )	=> 'bottom right',
									__(	'Center Left ', 'avia_framework' )	=> 'center left',
									__( 'Center Center', 'avia_framework' )	=> 'center center',
									__(	'Center Right', 'avia_framework' )	=> 'center right'
								)
			);

	$avia_elements[] = array(
				'slug'		=> 'styling',
				'name'		=> __( 'Repeat', 'avia_framework' ),
				'desc'		=> '',
				'id'		=> "colorset-$set_key-repeat",
				'type'		=> 'select',
				'std'		=> 'no-repeat',
				'class'		=> 'av_2columns av_col_2',
				'no_first'	=> true,
				'required'	=> array( "colorset-$set_key-img", '{true}' ),
				'globalcss'	=> true,
				'target'	=> array( "default_slideshow_target::.live-$set_key::background-repeat" ),
				'subtype'	=> array(
									__( 'no repeat', 'avia_framework' )			=> 'no-repeat',
									__( 'Repeat', 'avia_framework' )			=> 'repeat',
									__( 'Tile Horizontally', 'avia_framework' )	=> 'repeat-x',
									__( 'Tile Vertically', 'avia_framework' )	=> 'repeat-y',
//									__( 'Stretch Fullscreen', 'avia_framework' )=> 'fullscreen'
								)
			);

	$avia_elements[] = array(
				'slug'		=> 'styling',
				'name'		=> __( 'Attachment', 'avia_framework' ),
				'desc'		=> '',
				'id'		=> "colorset-$set_key-attach",
				'type'		=> 'select',
				'std'		=> 'scroll',
				'class'		=> 'av_2columns av_col_1',
				'no_first'	=> true,
				'required'	=> array( "colorset-$set_key-img", '{true}' ),
				'globalcss'	=> true,
				'target'	=> array( "default_slideshow_target::.live-$set_key::background-attachment" ),
				'subtype'	=> array(
									__( 'Scroll', 'avia_framework' )	=> 'scroll',
									__( 'Fixed', 'avia_framework' )		=> 'fixed'
								)
			);

	// END TAB
	$avia_elements[] = array(
				'slug'			=> 'styling',
				'type'			=> 'visual_group_end',
				'id'			=> 'avia_styling_colorset_tab_' . $iterator . '_end',
				'nodescription' => true
			);
}



// START TAB
$avia_elements[] = array(
			'slug'			=> 'styling',
			'name'			=>__( 'Body background', 'avia_framework' ),
			'type'			=> 'visual_group_start',
			'id'			=> 'avia_styling_body_background_start',
			'nodescription' => true,
			'class'			=> 'avia_tab avia_tab2',
			'required'		=> array( 'color-body_style', '{contains}box' ),
			'inactive'		=> __( "These options are only available if you select the 'boxed' or 'framed' layout. Your currently have a different layout selected", 'avia_framework' ) . '<br/><br/>' .
									__( 'You can change that setting', 'avia_framework' ) .
									" <a href='#goto_layout'>" .
									__( 'at General Layout', 'avia_framework' ) .
									'</a>'
		);

$avia_elements[] = array(
			'slug'		=> 'styling',
			'name'		=> __( 'Body Background Color', 'avia_framework' ),
			'desc'		=> __( 'Background color for your site', 'avia_framework' ) . '<br/>' .
								__( 'This is the color that is displayed behind your boxed content area', 'avia_framework' ),
			'id'		=> 'color-body_color',
			'type'		=> 'colorpicker',
			'std'		=> '#eeeeee',
/* 			'class'		=> 'av_2columns av_col_2', */
			'target'	=> array( 'default_slideshow_target::.live_bg_wrap::background-color' ),
			'globalcss'	=> true
		);

$avia_elements[] = array(
			'slug'			=> 'styling',
			'name'			=> __( 'Background Image', 'avia_framework' ),
			'desc'			=> __( 'The background image of your Body', 'avia_framework' ) . '<br/><br/>',
			'id'			=> 'color-body_img',
			'type'			=> 'select',
			'std'			=> '',
			'no_first'		=> true,
			'class'			=> 'av_2columns av_col_1 set_blank_on_hide',
			'required'		=> array( 'color-body_style', 'boxed' ),
			'target'		=> array( 'default_slideshow_target::.live_bg_wrap::background-image' ),
			'subtype'		=> array(
									__( 'No Background Image', 'avia_framework' )	=> '',
									__( 'Upload custom image', 'avia_framework' )	=> 'custom'
								),
			'folder'		=> 'images/background-images/',
			'folderlabel'	=> '',
			'group'			=> 'Select predefined pattern',
			'globalcss'		=> true
		);

$avia_elements[] = array(
			'slug'		=> 'styling',
			'name'		=> __( 'Custom Background Image', 'avia_framework' ),
			'desc'		=> __( 'Upload a BG image for your Body', 'avia_framework' ).'<br/><br/>',
			'id'		=> 'color-body_customimage',
			'type'		=> 'upload',
			'std'		=> '',
			'class'		=> 'set_blank_on_hide av_2columns av_col_2',
			'label'		=> __( 'Use Image', 'avia_framework' ),
			'required'	=> array( 'color-body_img', 'custom' ),
			'target'	=> array( 'default_slideshow_target::.live_bg_wrap::background-image' ),
			'globalcss'	=> true
		);


$avia_elements[] = array(
			'slug'		=> 'styling',
			'name'		=> __( 'Position Of The Image', 'avia_framework' ),
			'desc'		=> '',
			'id'		=> 'color-body_pos',
			'type'		=> 'select',
			'std'		=> 'top left',
			'no_first'	=> true,
			'class'		=> 'av_2columns av_col_1',
			'required'	=> array( 'color-body_img', '{true}' ),
			'target'	=> array( 'default_slideshow_target::.live_bg_wrap::background-position' ),
			'globalcss'	=> true,
			'subtype'	=> array(
								__( 'Top Left', 'avia_framework' )		=> 'top left',
								__( 'Top Center', 'avia_framework' )	=> 'top center',
								__(	'Top Right', 'avia_framework' )		=> 'top right',
								__( 'Bottom Left', 'avia_framework' )	=> 'bottom left',
								__( 'Bottom Center', 'avia_framework' )	=> 'bottom center',
								__(	'Bottom Right', 'avia_framework' )	=> 'bottom right',
								__(	'Center Left ', 'avia_framework' )	=> 'center left',
								__( 'Center Center', 'avia_framework' )	=> 'center center',
								__(	'Center Right', 'avia_framework' )	=> 'center right'
							)
		);

$avia_elements[] = array(
			'slug'		=> 'styling',
			'name'		=> __( 'Repeat', 'avia_framework' ),
			'desc'		=> '',
			'id'		=> 'color-body_repeat',
			'type'		=> 'select',
			'std'		=> 'no-repeat',
			'class'		=> 'av_2columns av_col_2',
			'no_first'	=> true,
			'required'	=> array( 'color-body_img', '{true}' ),
			'target'	=> array( 'default_slideshow_target::.live_bg_wrap::background-repeat' ),
			'globalcss'	=> true,
			'subtype'	=> array(
								__( 'no repeat', 'avia_framework' )				=> 'no-repeat',
								__( 'Repeat', 'avia_framework' )				=> 'repeat',
								__( 'Tile Horizontally', 'avia_framework' )		=> 'repeat-x',
								__( 'Tile Vertically', 'avia_framework' )		=> 'repeat-y',
								__( 'Stretch Fullscreen', 'avia_framework' )	=> 'fullscreen'
							)
		);

$avia_elements[] = array(
			'slug'		=> 'styling',
			'name'		=> __( 'Attachment', 'avia_framework' ),
			'desc'		=> '',
			'id'		=> 'color-body_attach',
			'type'		=> 'select',
			'std'		=> 'scroll',
			'class'		=> 'av_2columns av_col_1',
			'no_first'	=> true,
			'required'	=> array( 'color-body_img', '{true}' ),
			'target'	=> array( 'default_slideshow_target::.live_bg_wrap::background-attachment' ),
			'globalcss'	=> true,
			'subtype'	=> array(
								__( 'Scroll', 'avia_framework' )	=> 'scroll',
								__( 'Fixed', 'avia_framework' )		=> 'fixed'
							)
		);

// END TAB
$avia_elements[] = array(
			'slug'			=> 'styling',
			'type'			=> 'visual_group_end',
			'id'			=> 'avia_styling_body_background_end',
			'nodescription' => true
		);



// START TAB
$avia_elements[] = array(
			'slug'			=> 'styling',
			'name'			=> __( 'Fonts', 'avia_framework' ),
			'type'			=> 'visual_group_start',
			'id'			=> 'avia_styling_fonts_start',
			'class'			=> 'avia_tab avia_tab2',
			'nodescription'	=> true
		);

//	Link to typography examples
$href = 'https://fonts.google.com/share?selection.family=Advent%20Pro:wght@100;400;600|Alice|Allerta|Antic|Arimo:wght@400;700|Arvo|Audiowide|Bad%20Script|Bangers|Barlow:wght@400;500;600;700|Baumans|Bitter|Cabin|Cardo|Carme|Caveat:wght@400;700|Coda|Codystar:wght@300;400|Comfortaa:wght@300;400;700|Cormorant%20Garamond:wght@300;400;700|Coustard|Damion|Dancing%20Script|Dosis:wght@200;300;400;500;600;700;800|Droid%20Sans|Droid%20Serif|EB%20Garamond|Exo:wght@100;400;700;900|Finger%20Paint|Fira%20Sans:wght@100;400;700|Fjord%20One|Flamenco:wght@300;400|Great%20Vibes|Gruppo|Heebo:wght@100;400;700|Herr%20Von%20Muellerhoff|IBM%20Plex%20Serif:wght@300;400;700|Inconsolata|Josefin%20Sans|Josefin%20Slab|Kameron|Karla:wght@400;700|Kreon|Lato:wght@300;400;700|League%20Script|Lobster|Lora|Mako|Marck%20Script|Mate%20SC|Megrim|Merienda:wght@400;700|Merriweather:wght@300;400;700|Metrophobic|Molengo|Montserrat|Muli|News%20Cycle|Nixie%20One|Nobile|Open%20Sans%20Condensed:wght@300;700|Open%20Sans:wght@400;600|Orbitron|Oregano|Oswald|PT%20Sans|Pacifico|Parisienne|Petit%20Formal%20Script|Pinyon%20Script|Playfair%20Display:wght@400;700|Podkova|Poiret%20One|Poly|Press%20Start%202P|Quattrocento|Questrial|Quicksand:wght@400;600|Raleway|Righteous|Roboto:wght@100;400;700|Sacramento|Salsa|Signika%20Negative|Source%20Serif%20Pro:wght@400;600;700|Special%20Elite|Sunshiney|Tangerine|Tenor%20Sans|Varela%20Round|Work%20Sans:wght@100;400;700|Yellowtail&sort=alpha';

$web_safe = '<a href="https://www.cssfontstack.com/" target="_blank" rel="noopener noreferrer">' . __( 'devices', 'avia_framework' ) . '</a>';
$google_webfont  = '<a href="' . $href . '" target="_blank" rel="noopener noreferrer">' . __( 'Google Webkit Fonts', 'avia_framework' ) . '</a>';

$desc = sprintf( __( 'The heading font allows you to use a wide range of fonts for your headings. Upload your own fonts, use web save fonts (faster rendering, but not mandatory installed on all %1$s) or %2$s (more unique).', 'avia_framework' ), $web_safe, $google_webfont );

$avia_elements[] = array(
			'slug'		=> 'styling',
			'name'		=> __( 'Heading Font', 'avia_framework' ),
			'desc'		=> $desc,
			'id'		=> 'google_webfont',
			'type'		=> 'select',
			'no_first'	=> true,
			'class'		=> 'av_2columns av_col_1',
			'onchange'	=> 'avia_add_google_font',
			'std'		=> 'Open Sans',
			'globalcss'	=> true,
			'subtype'	=> apply_filters( 'avf_heading_font_select_options_list', AviaSuperobject()->type_fonts()->get_font_select_options_list() )
		);


$desc = sprintf( __( 'Choose a font for your body text. Upload your own fonts, use web safe fonts (faster rendering, but not mandatory installed on all %1$s) or %2$s (more unique).', 'avia_framework' ),$web_safe, $google_webfont );

$avia_elements[] = array(
			'slug'		=> 'styling',
			'name'		=> __( 'Font For Your Body Text', 'avia_framework' ),
			'desc'		=> $desc . '<br/>',
			'id'		=> 'default_font',
			'type'		=> 'select',
			'no_first'	=> true,
			'class'		=> 'av_2columns av_col_2',
			'onchange'	=> 'avia_add_google_font',
			'std'		=> 'Helvetica-Neue,Helvetica-websave',
			'globalcss'	=> true,
			'subtype'	=> apply_filters( 'avf_content_font_select_options_list', AviaSuperobject()->type_fonts()->get_font_select_options_list() )
		);

// END TAB
$avia_elements[] = array(
			'slug'			=> 'styling',
			'type'			=> 'visual_group_end',
			'id'			=> 'avia_styling_fonts_end',
			'nodescription' => true
		);



// START TAB
$avia_elements[] = array(
			'slug'			=> 'styling',
			'name'			=> __( 'Typography', 'avia_framework' ),
			'type'			=> 'visual_group_start',
			'id'			=> 'avia_styling_typo_start',
			'class'			=> 'avia_tab avia_tab2',
			'nodescription'	=> true
		);

$desc  = __( 'Override theme default settings. Only recommended for advanced users. Added with 4.8.8 and is in active BETA.', 'avia_framework' ) . ' ';
$desc .= __( 'More detailed information for typography settings you find here:', 'avia_framework' ) . '  ';
$desc .= '<a href="https://spencermortensen.com/articles/typographic-scale/" target="_blank" rel="noopener noreferrer">The typographic scale</a>,  ';
$desc .= '<a href="https://type-scale.com/" target="_blank" rel="noopener noreferrer">A type testing tool</a><br /><br />';
$desc .= '<strong>' . __( 'Need more flexibility for CSS units:', 'avia_framework' ) . '</strong>';
$desc .= '<ul>';
$desc .=	'<li>' . __( 'To functions.php add: add_theme_support( &quot;avia_options_extended_typography&quot; );', 'avia_framework' ) . '</li>';
$desc .=	'<li>' . __( 'Leave input fields empty to use theme defaults. Make sure you add valid CSS units. There is no check for that.', 'avia_framework' ) . '</li>';
$desc .=	'<li>' . sprintf( __( 'To skip responsive font settings in ALB elements you can use %s this filter %s', 'avia_framework' ), '<a href="https://github.com/KriesiMedia/enfold-library/blob/master/actions%20and%20filters/Layout/avf_el_styling_responsive_font_size_skip.php" target="_blank" rel="noopener noreferrer">', '</a>' ) . '</li>';
$desc .= '</ul>';
$desc .= '<strong>' . __( 'Screen Sizes:', 'avia_framework' ) . '</strong>';
$desc .= '<ul>';
$desc .=	'<li>' . __( 'Default: all screensizes (no media query)', 'avia_framework' ) . '</li>';
$desc .=	'<li>' . __( 'Medium: between 768px and 989px - eg: Tablet Landscape', 'avia_framework' ) . '</li>';
$desc .=	'<li>' . __( 'Small: between 480px and 767px - eg: Tablet Portrait', 'avia_framework' ) . '</li>';
$desc .=	'<li>' . __( 'Very Small: smaller than 479px - eg: Smartphone Portrait', 'avia_framework' ) . '</li>';
$desc .= '</ul>';

$avia_elements[] = array(
			'slug'			=> 'styling',
			'name'			=> __( 'Advanced Options: Customize Typography Settings', 'avia_framework' ),
			'desc'			=> $desc,
			'id'			=> 'styling_header_typo',
			'type'			=> 'heading',
			'nodescription' => true
		);

$typo_placeholder = __( 'default or valid CSS', 'avia_framework' );
$typo_input_type = ! current_theme_supports( 'avia_options_extended_typography' ) ? 'select' : 'text';

//	loop through all typo elements
foreach( $avia_admin_values['customize-typo-elements'] as $typo_element => $base_opt_key )
{
	$chk_id = "styling_typo_chk_{$typo_element}";
	$requ = array( $chk_id, "{contains_array}{$chk_id}" );;
	$typo_el_rules = $avia_admin_values['customize-typo-elements-rules'][ $typo_element ];

	switch( $typo_element )
	{
		case 'content-font':
			$desc = __( 'Default Content Font Size (eg: blog post content) - Selector: body { ... }', 'avia_framework' );
			break;
		case 'h1':
			$desc = __( 'H1 - Selector: h1 { ... }', 'avia_framework' );
			break;
		case 'h2':
			$desc = __( 'H2 - Selector: h2 { ... }', 'avia_framework' );
			break;
		case 'h3':
			$desc = __( 'H3 - Selector: h3 { ... }', 'avia_framework' );
			break;
		case 'h4':
			$desc = __( 'H4 - Selector: h4 { ... }', 'avia_framework' );
			break;
		case 'h5':
			$desc = __( 'H5 - Selector: h5 { ... }', 'avia_framework' );
			break;
		case 'h6':
			$desc = __( 'H6 - Selector: h6 { ... }', 'avia_framework' );
			break;
	}



	$avia_elements[] = array(
						'slug'		=> 'styling',
						'desc'		=> $desc,
						'id'		=> $chk_id,
						'type'		=> 'checkbox',
						'std'		=> false
					);

	//	loop through all css rules
	foreach( $typo_el_rules as $rule )
	{
		$iterator = 1;

		//	loop through all media sizes
		foreach( $avia_admin_values['responsive-sizes'] as $desc => $media )
		{
			$media_key = empty( $media ) ? '' : "-{$media}";
			$theme_def = isset( $avia_admin_values['default-typo-elements-rules'][ $typo_element ][ $rule ] ) ? $avia_admin_values['default-typo-elements-rules'][ $typo_element ][ $rule ] : '';

			switch( $rule )
			{
				case 'font-size':
					$heading_desc = '' == $theme_def ? __( 'Font Size:', 'avia_framework' ) : sprintf( __( 'Font Size (default %s):', 'avia_framework' ), $theme_def );
					$type = $typo_input_type;
					$subtype = $avia_admin_values['font-size'];

					if( 'content-font' == $typo_element )
					{
						$id_input = "typo-{$base_opt_key}{$media_key}";
					}
					else
					{
						$id_input = "typo-{$rule}-{$base_opt_key}{$media_key}";
					}
					break;
				case 'font-weight':
					$heading_desc = '' == $theme_def ? __( 'Font Weight:', 'avia_framework' ) : sprintf( __( 'Font Weight (default %s):', 'avia_framework' ), $theme_def );
					$type = 'select';
					$subtype = $avia_admin_values['font-weight'];
					$id_input = "typo-{$rule}-{$base_opt_key}{$media_key}";
					break;
				case 'line-height':
					$heading_desc = '' == $theme_def ? __( 'Line Height:', 'avia_framework' ) : sprintf( __( 'Line Height (default %s):', 'avia_framework' ), $theme_def );
					$type = $typo_input_type;
					$subtype = $avia_admin_values['line-height'];
					$id_input = "typo-{$rule}-{$base_opt_key}{$media_key}";
					break;
				case 'margin':
					$heading_desc = '' == $theme_def ? __( 'Margin (e.g. 0 0 10px 0 ):', 'avia_framework' ) : sprintf( __( 'Margin (default %s):', 'avia_framework' ), $theme_def );
					$type = 'text';
					$id_input = "typo-{$rule}-{$base_opt_key}{$media_key}";
					break;
			}

			if( 1 == $iterator )
			{
				$avia_elements[] = array(
						'slug'			=> 'styling',
						'desc'			=> $heading_desc,
						'id'			=> "head_{$id_input}",
						'type'			=> 'heading',
						'std'			=> '',
						'nodescription' => true,
						'class'			=> 'av-no-border',
						'required'		=> $requ
					);
			}

			if( 'select' == $type )
			{
				$avia_elements[] = array(
							'slug'		=> 'styling',
							'name'		=> $desc,
							'id'		=> $id_input,
							'type'		=> $type,
							'std'		=> '',
							'no_first'	=> true,
							'class'		=> "av_4columns av_col_{$iterator} av_no_desc",
							'required'	=> $requ,
							'globalcss'	=> true,
							'subtype'	=> $subtype
						);
			}
			else if( 'text' == $type )
			{
				$avia_elements[] = array(
							'slug'			=> 'styling',
							'name'			=> $desc,
							'id'			=> $id_input,
							'type'			=> $type,
							'std'			=> '',
							'class'			=> "av_4columns av_col_{$iterator} av_no_desc",
							'placeholder'	=> $typo_placeholder,
							'required'		=> $requ,
							'globalcss'		=> true
						);
			}

			$iterator++;
		}
	}
}


// END TAB
$avia_elements[] = array(
			'slug'			=> 'styling',
			'type'			=> 'visual_group_end',
			'id'			=> 'avia_styling_typo_end',
			'nodescription' => true
		);



// END TAB CONTAINER
$avia_elements[] = array(
			'slug'			=> 'styling',
			'type'			=> 'visual_group_end',
			'id'			=> 'avia_styling_colorset_end',
			'nodescription' => true
		);





$avia_elements[] = array(
			'slug'	=> 'styling',
			'name'	=> __( 'Quick CSS', 'avia_framework' ),
			'desc'	=> __( 'Just want to do some quick CSS changes? Enter them here, they will be applied to the theme. If you need to change major portions of the theme please use the custom.css file', 'avia_framework' ) .
							" <a target='_blank' href='https://kriesi.at/documentation/enfold/using-a-child-theme/' rel='noopener noreferrer'>" .
							__( 'or the Enfold Child theme.', 'avia_framework' ) .
							'</a>',
			'id'	=> 'quick_css',
			'type'	=> 'textarea',
			'std'	=> ''
		);


$avia_elements[] = array(
			'slug'		=> 'styling',
			'name'		=> __( 'Show Custom Styles In Backend Block Editor', 'avia_framework' ),
			'desc'		=> __( 'Select if you want to see theme custom styles when editing a page/post with the block editor.', 'avia_framework' ),
			'id'		=> 'block_editor_theme_support',
			'type'		=> 'select',
			'required'	=> array( 'enable_wp_classic_editor', '' ),
			'std'		=> '',
			'no_first'	=> true,
			'globalcss'	=> true,
			'subtype'	=> array(
								__( 'Show Theme Custom Styles', 'avia_framework' )			=> '',
								__( 'Use Block Editor Default Styles', 'avia_framework' )	=> 'no_theme_support',
							)
				);

