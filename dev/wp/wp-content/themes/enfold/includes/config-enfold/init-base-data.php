<?php
/*
 * This helper file initialises base data for Enfold.
 * Moved from functions.php
 *
 * @since 7.0
 * @added_by Günter
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


global $avia_config;


/*
 * create a global var which stores the ids of all posts which are displayed on the current page. It will help us to filter duplicate posts
 */
$avia_config['posts_on_current_page'] = [];


/**
 * Define a folder in WP uploads directory where we place our dynamic created theme files.
 * Allows a better structure in the WP upload folder and also to exclude this folder from static ressources
 *
 * Make sure to return folder like "/f1/f2". There is no further check for slashes !!!
 *
 * Backwards compatibility:
 *
 * - /avia_custom_shapes is moved to /dynamic_avia/avia_custom_shapes in update routine (if new folder does not exist)
 * - /avia_fonts is not moved because there are infos in db entries. Risk to break existing sites is too high !!!
 *
 * In case you change the folder name before doing this:
 *
 *   - remove all uploaded icon fonts and type fonts from theme options page
 *   - rename folder /dynamic_avia to new folder name to keep existing uploads
 *   - return your custom folder via filter 'avf_dynamic_files_upload_folder'
 *   - upload your icon fonts and type fonts on theme options page again
 *
 * @since 5.3
 * @param string $folder
 * @return string
 */
$avia_config['dynamic_files_upload_folder'] = apply_filters( 'avf_dynamic_files_upload_folder', '/dynamic_avia' );

/*
 * These are the available color sets in your backend.
 * If more sets are added users will be able to create additional color schemes for certain areas
 *
 * The array key has to be the class name, the value is only used as tab heading on the styling page
 */
$color_sets = array(
				'header_color'      => 'Logo Area',
				'main_color'        => 'Main Content',
				'alternate_color'   => 'Alternate Content',
				'footer_color'      => 'Footer',
				'socket_color'      => 'Socket'
			);

/**
 * @since 4.8.6.3
 * @param array $color_sets
 * @return array
 */
$avia_config['color_sets'] = apply_filters( 'avf_color_sets', $color_sets );


/*
 * Define a set of colors to be used by ALB color selection popup in the palette below the iris select window
 * Can be filtered before rendering to backend
 *
 * @since 4.8.3
 */
$avia_config['default_alb_color_palette'] = array( '#000000', '#ffffff', '#B02B2C', '#edae44', '#eeee22', '#83a846', '#7bb0e7', '#745f7e', '#5f8789', '#d65799', '#4ecac2' );


/*
 * Register additional image thumbnail sizes
 * Those thumbnails are generated on image upload!
 *
 * If the size of an array was changed after an image was uploaded you either need to re-upload the image
 * or use the thumbnail regeneration plugin: http://wordpress.org/extend/plugins/regenerate-thumbnails/
 */

$avia_config['imgSize']['widget'] 			 	= array( 'width' => 36,  'height' => 36 );						// small preview pics eg sidebar news
$avia_config['imgSize']['square'] 		 	    = array( 'width' => 180, 'height' => 180 );		                // small image for blogs
$avia_config['imgSize']['featured'] 		 	= array( 'width' => 1500, 'height' => 430 );					// images for fullsize pages and fullsize slider
$avia_config['imgSize']['featured_large'] 		= array( 'width' => 1500, 'height' => 630 );					// images for fullsize pages and fullsize slider
$avia_config['imgSize']['extra_large'] 		 	= array( 'width' => 1500, 'height' => 1500 , 'crop' => false );	// images for fullscrren slider
$avia_config['imgSize']['portfolio'] 		 	= array( 'width' => 495, 'height' => 400 );						// images for portfolio entries (2,3 column)
$avia_config['imgSize']['portfolio_small'] 		= array( 'width' => 260, 'height' => 185 );						// images for portfolio 4 columns
$avia_config['imgSize']['gallery'] 		 		= array( 'width' => 845, 'height' => 684 );						// images for portfolio entries (2,3 column)
$avia_config['imgSize']['magazine'] 		 	= array( 'width' => 710, 'height' => 375 );						// images for magazines
$avia_config['imgSize']['masonry'] 		 		= array( 'width' => 705, 'height' => 705 , 'crop' => false );	// images for fullscreen masonry
$avia_config['imgSize']['entry_with_sidebar'] 	= array( 'width' => 845, 'height' => 321 );		            	// big images for blog and page entries
$avia_config['imgSize']['entry_without_sidebar']= array( 'width' => 1210, 'height' => 423 );					// images for fullsize pages and fullsize slider

/**
 *
 * @param array $avia_config['imgSize']
 * @return array
 */
$avia_config['imgSize'] = apply_filters( 'avf_modify_thumb_size', $avia_config['imgSize'] );


$avia_config['selectableImgSize'] = array(
			'square' 				=> __( 'Square', 'avia_framework' ),
			'featured'  			=> __( 'Featured Thin', 'avia_framework' ),
			'featured_large'  		=> __( 'Featured Large', 'avia_framework' ),
			'portfolio' 			=> __( 'Portfolio', 'avia_framework' ),
			'gallery' 				=> __( 'Gallery', 'avia_framework' ),
			'entry_with_sidebar' 	=> __( 'Entry with Sidebar', 'avia_framework' ),
			'entry_without_sidebar'	=> __( 'Entry without Sidebar', 'avia_framework' ),
			'extra_large' 			=> __( 'Fullscreen Sections/Sliders', 'avia_framework' )
		);

/**
 * @since 4.5.7.2
 * @param array $avia_config['selectableImgSize']
 * @param array $avia_config['imgSize']
 * @return array
 */
$avia_config['selectableImgSize'] = apply_filters( 'avf_modify_selectable_image_sizes', $avia_config['selectableImgSize'], $avia_config['imgSize'] );


$avia_config['readableImgSize'] = $avia_config['selectableImgSize'];

$avia_config['readableImgSize']['widget'] =				__( 'Widget', 'avia_framework' );
$avia_config['readableImgSize']['portfolio_small'] =	__( 'Portfolio small', 'avia_framework' );
$avia_config['readableImgSize']['magazine'] =			__( 'Magazine', 'avia_framework' );
$avia_config['readableImgSize']['masonry'] =			__( 'Masonry', 'avia_framework' );

/**
 *
 * @since 4.5.7.2
 * @param array $avia_config['readableImgSize']
 * @param array $avia_config['imgSize']
 * @return array
 */
$avia_config['readableImgSize'] = apply_filters( 'avf_modify_readable_image_sizes', $avia_config['readableImgSize'], $avia_config['imgSize'] );

/*
 * register the layout classes
 *
 */
$avia_config['layout']['fullsize'] = array(
								'content'	=> 'av-content-full alpha',
								'sidebar'	=> 'hidden',
								'meta'		=> '',
								'entry'		=> ''
							);

$avia_config['layout']['sidebar_left'] = array(
								'content'	=> 'av-content-small',
								'sidebar'	=> 'alpha' ,
								'meta'		=> 'alpha',
								'entry'		=> ''
							);

$avia_config['layout']['sidebar_right'] = array(
								'content'	=> 'av-content-small alpha',
								'sidebar'	=> 'alpha',
								'meta'		=> 'alpha',
								'entry'		=> 'alpha'
							);



/*
 * These are some of the font icons used in the theme, defined by the entypo-fontello icon font.
 * The font files and svg icon files are included by the aviaBuilder.
 * Common icons are stored here for easy retrieval and to allow users to override them
 *
 * @since ???
 * @since 7.0						svg icons are added
 * @param array $font_icons
 * @return array
 */
$avia_config['font_icons'] = apply_filters( 'avf_default_icons', array(

	//post formats +  types
	'standard' 		=> array( 'font' => 'entypo-fontello', 'icon' => 'ue836' ),
	'link'    		=> array( 'font' => 'entypo-fontello', 'icon' => 'ue822' ),
	'image'    		=> array( 'font' => 'entypo-fontello', 'icon' => 'ue80f' ),
	'audio'    		=> array( 'font' => 'entypo-fontello', 'icon' => 'ue801' ),
	'quote'   		=> array( 'font' => 'entypo-fontello', 'icon' => 'ue833' ),
	'gallery'   	=> array( 'font' => 'entypo-fontello', 'icon' => 'ue80e' ),
	'video'   		=> array( 'font' => 'entypo-fontello', 'icon' => 'ue80d' ),
	'portfolio'   	=> array( 'font' => 'entypo-fontello', 'icon' => 'ue849' ),
	'product'   	=> array( 'font' => 'entypo-fontello', 'icon' => 'ue859' ),

	'svg__standard' 	=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'pencil' ),
	'svg__link'    		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'link' ),
	'svg__image'    	=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'camera' ),
	'svg__audio'    	=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'note-beamed' ),
	'svg__quote'   		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'quote' ),
	'svg__gallery'   	=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'picture' ),
	'svg__video'   		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'video' ),
	'svg__portfolio'   	=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'docs' ),
	'svg__product'   	=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'basket' ),

	//social
	'behance' 		=> array( 'font' => 'entypo-fontello', 'icon' => 'ue915' ),
	'dribbble' 		=> array( 'font' => 'entypo-fontello', 'icon' => 'ue8fe' ),
	'facebook' 		=> array( 'font' => 'entypo-fontello', 'icon' => 'ue8f3' ),
	'flickr' 		=> array( 'font' => 'entypo-fontello', 'icon' => 'ue8ed' ),
	'linkedin' 		=> array( 'font' => 'entypo-fontello', 'icon' => 'ue8fc', 'display_name' => 'LinkedIn' ),
	'instagram' 	=> array( 'font' => 'entypo-fontello', 'icon' => 'uf16d' ),
	'pinterest' 	=> array( 'font' => 'entypo-fontello', 'icon' => 'ue8f8' ),
	'whatsapp'		=> array( 'font' => 'entypo-fontello', 'icon' => 'uf232', 'display_name' => 'WhatsApp' ),
	'skype' 		=> array( 'font' => 'entypo-fontello', 'icon' => 'ue90d' ),
	'tumblr' 		=> array( 'font' => 'entypo-fontello', 'icon' => 'ue8fa' ),
	'twitter' 		=> array( 'font' => 'entypo-fontello', 'icon' => 'ue932', 'display_name' => 'X' ),
	'tiktok' 		=> array( 'font' => 'entypo-fontello', 'icon' => 'ue930', 'display_name' => 'TikTok' ),
	'square-x-twitter' => array( 'font' => 'entypo-fontello', 'icon' => 'ue933', 'display_name' => 'X' ),
	'vimeo' 		=> array( 'font' => 'entypo-fontello', 'icon' => 'ue8ef' ),
	'rss' 			=> array( 'font' => 'entypo-fontello', 'icon' => 'ue853' ),
	'yelp'			=> array( 'font' => 'entypo-fontello', 'icon' => 'uf1e9' ),
	'youtube'		=> array( 'font' => 'entypo-fontello', 'icon' => 'uf16a' ),
	'xing'			=> array( 'font' => 'entypo-fontello', 'icon' => 'ue923' ),
	'soundcloud'	=> array( 'font' => 'entypo-fontello', 'icon' => 'ue913' ),
	'five_100_px'	=> array( 'font' => 'entypo-fontello', 'icon' => 'ue91d', 'display_name' => '500px' ),
	'vk'			=> array( 'font' => 'entypo-fontello', 'icon' => 'ue926' ),
	'reddit'		=> array( 'font' => 'entypo-fontello', 'icon' => 'ue927' ),
	'telegram'		=> array( 'font' => 'entypo-fontello', 'icon' => 'ue8b7' ),
	'digg'			=> array( 'font' => 'entypo-fontello', 'icon' => 'ue928' ),
	'delicious'		=> array( 'font' => 'entypo-fontello', 'icon' => 'ue929' ),
	'mail' 			=> array( 'font' => 'entypo-fontello', 'icon' => 'ue805' ),
	'threads'		=> array( 'font' => 'entypo-fontello', 'icon' => 'uf231', 'display_name' => 'Threads' ),

	'svg__behance' 		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'behance' ),
	'svg__dribbble'		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'dribbble' ),
	'svg__facebook' 	=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'facebook' ),
	'svg__flickr' 		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'flickr' ),
	'svg__linkedin' 	=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'linkedin', 'display_name' => 'LinkedIn' ),
	'svg__instagram' 	=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'instagram-1' ),
	'svg__pinterest' 	=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'pinterest' ),
	'svg__whatsapp'		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'whatsapp', 'display_name' => 'WhatsApp' ),
	'svg__skype' 		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'skype' ),
	'svg__tumblr' 		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'tumblr' ),
	'svg__twitter' 		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'x-twitter', 'display_name' => 'X' ),
	'svg__tiktok' 		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'tiktok', 'display_name' => 'TikTok' ),
	'svg__square-x-twitter' => array( 'font' => 'svg_entypo-fontello', 'icon' => 'square-x-twitter', 'display_name' => 'X' ),
	'svg__vimeo' 		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'vimeo' ),
	'svg__rss' 			=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'rss' ),
	'svg__yelp'			=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'yelp' ),
	'svg__youtube'		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'youtube-play' ),
	'svg__xing'			=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'xing' ),
	'svg__soundcloud'	=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'soundcloud' ),
	'svg__five_100_px'	=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'fivehundredpx', 'display_name' => '500px' ),
	'svg__vk'			=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'vk' ),
	'svg__reddit'		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'reddit' ),
	'svg__telegram'		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'paper-plane' ),
	'svg__digg'			=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'digg' ),
	'svg__delicious'	=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'delicious' ),
	'svg__mail' 		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'mail' ),
	'svg__threads'		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'threads', 'display_name' => 'Threads' ),

	//woocomemrce
	'cart' 			=> array( 'font' => 'entypo-fontello', 'icon' => 'ue859' ),
	'account'		=> array( 'font' => 'entypo-fontello', 'icon' => 'ue80a' ),
	'details'		=> array( 'font' => 'entypo-fontello', 'icon' => 'ue84b' ),

	'svg__cart' 	=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'basket' ),
	'svg__account'	=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'user' ),
	'svg__details'	=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'doc-text' ),

	//bbpress
	'supersticky'			=> array( 'font' => 'entypo-fontello', 'icon' => 'ue808' ),
	'sticky'				=> array( 'font' => 'entypo-fontello', 'icon' => 'ue809' ),
	'one_voice'				=> array( 'font' => 'entypo-fontello', 'icon' => 'ue83b' ),
	'multi_voice'			=> array( 'font' => 'entypo-fontello', 'icon' => 'ue83c' ),
	'closed'				=> array( 'font' => 'entypo-fontello', 'icon' => 'ue824' ),
	'sticky_closed'			=> array( 'font' => 'entypo-fontello', 'icon' => 'ue808\ue824' ),
	'supersticky_closed'	=> array( 'font' => 'entypo-fontello', 'icon' => 'ue809\ue824' ),

	'core__supersticky'			=> array( 'font' => 'entypo-fontello-enfold', 'icon' => 'ue808' ),
	'core__sticky'				=> array( 'font' => 'entypo-fontello-enfold', 'icon' => 'ue809' ),
	'core__one_voice'			=> array( 'font' => 'entypo-fontello-enfold', 'icon' => 'ue83b' ),
	'core__multi_voice'			=> array( 'font' => 'entypo-fontello-enfold', 'icon' => 'ue83c' ),
	'core__closed'				=> array( 'font' => 'entypo-fontello-enfold', 'icon' => 'ue824' ),
	'core__sticky_closed'		=> array( 'font' => 'entypo-fontello-enfold', 'icon' => 'ue808\ue824' ),
	'core__supersticky_closed'	=> array( 'font' => 'entypo-fontello-enfold', 'icon' => 'ue809\ue824' ),

	'svg__supersticky'	=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'star' ),
	'svg__sticky'		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'star-empty' ),
	'svg__one_voice'	=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'comment' ),
	'svg__multi_voice'	=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'chat' ),
	'svg__closed'		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'lock' ),

	//navigation, slider & controls
	'play' 			=> array( 'font' => 'entypo-fontello', 'icon' => 'ue897' ),
	'pause'			=> array( 'font' => 'entypo-fontello', 'icon' => 'ue899' ),
	'next'    		=> array( 'font' => 'entypo-fontello', 'icon' => 'ue879' ),
	'prev'    		=> array( 'font' => 'entypo-fontello', 'icon' => 'ue878' ),
	'next_big'  	=> array( 'font' => 'entypo-fontello', 'icon' => 'ue87d' ),
	'prev_big'  	=> array( 'font' => 'entypo-fontello', 'icon' => 'ue87c' ),
	'close'			=> array( 'font' => 'entypo-fontello', 'icon' => 'ue814' ),
	'reload'		=> array( 'font' => 'entypo-fontello', 'icon' => 'ue891' ),
	'mobile_menu'	=> array( 'font' => 'entypo-fontello', 'icon' => 'ue8a5' ),

	'core__play' 		=> array( 'font' => 'entypo-fontello-enfold', 'icon' => 'ue897' ),
	'core__pause'		=> array( 'font' => 'entypo-fontello-enfold', 'icon' => 'ue899' ),
	'core__next_big'  	=> array( 'font' => 'entypo-fontello-enfold', 'icon' => 'ue87d' ),
	'core__prev_big'  	=> array( 'font' => 'entypo-fontello-enfold', 'icon' => 'ue87c' ),

	'svg__play' 		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'play' ),
	'svg__pause'		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'pause' ),
	'svg__next'    		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'right-open-mini' ),
	'svg__prev'    		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'left-open-mini' ),
	'svg__next_big'  	=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'right-open-big' ),
	'svg__prev_big'  	=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'left-open-big' ),
	'svg__close'		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'cancel-circled' ),
	'svg__reload'		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'arrows-ccw' ),
	'svg__mobile_menu'	=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'list' ),

	//image hover overlays
	'ov_external'			=> array( 'font' => 'entypo-fontello', 'icon' => 'ue832' ),
	'ov_image'				=> array( 'font' => 'entypo-fontello', 'icon' => 'ue869' ),
	'ov_video'				=> array( 'font' => 'entypo-fontello', 'icon' => 'ue897' ),
	'lightbox_link'			=> array( 'font' => 'entypo-fontello', 'icon' => 'ue869' ),

	'core__ov_external'		=> array( 'font' => 'entypo-fontello-enfold', 'icon' => 'ue832' ),
	'core__ov_image'		=> array( 'font' => 'entypo-fontello-enfold', 'icon' => 'ue869' ),
	'core__ov_video'		=> array( 'font' => 'entypo-fontello-enfold', 'icon' => 'ue897' ),

	'svg__ov_external'		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'forward' ),
	'svg__ov_image'			=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'resize-full' ),
	'svg__ov_video'			=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'play' ),
	'svg__lightbox_link'	=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'resize-full' ),

	//misc
	'search'			=> array( 'font' => 'entypo-fontello', 'icon' => 'ue803' ),
	'info'				=> array( 'font' => 'entypo-fontello', 'icon' => 'ue81e' ),
	'clipboard'			=> array( 'font' => 'entypo-fontello', 'icon' => 'ue8d1' ),
	'scrolltop'			=> array( 'font' => 'entypo-fontello', 'icon' => 'ue876' ),
	'scrolldown'		=> array( 'font' => 'entypo-fontello', 'icon' => 'ue877' ),
	'bitcoin'			=> array( 'font' => 'entypo-fontello', 'icon' => 'ue92a' ),

	'svg__search'  		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'search' ),
	'svg__info'    		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'info' ),
	'svg__clipboard' 	=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'clipboard' ),
	'svg__scrolltop' 	=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'up-open' ),
	'svg__scrolldown' 	=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'down-open-mini' ),
	'svg__bitcoin' 		=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'bitcoin' ),

	'locked_option'			=> array( 'font' => 'entypo-fontello', 'icon' => 'ue824' ),
	'svg__locked_option'	=> array( 'font' => 'svg_entypo-fontello', 'icon' => 'lock' )

) );

/**
 * will be registered in function avia_nav_menus()
 *
 * 'plain' was added, because WP customizer does not support HTML
 */
$avia_config['nav_menus'] = array(
							'avia'	=> array(
										'html'	=> __( 'Main Menu', 'avia_framework' )
										),
							'avia2'	=> array(
										'html'	=> __( 'Secondary Menu', 'avia_framework' ) . ' <br/><small>(' . __( 'Will be displayed if you selected a header layout that supports a submenu', 'avia_framework' ) . ' <a target="_blank" href="' . admin_url( '?page=avia#goto_header' ) . '">' . __( 'here', 'avia_framework' ) . '</a>)</small>',
										'plain'	=> __( 'Secondary Menu - will be displayed if you selected a header layout that supports a submenu', 'avia_framework')
										),
							'avia3'	=> array(
										'html'	=> __( 'Footer Menu <br/><small>(no dropdowns)</small>', 'avia_framework' ),
										'plain'	=> __( 'Footer Menu (no dropdowns)', 'avia_framework' )
										)
									);

