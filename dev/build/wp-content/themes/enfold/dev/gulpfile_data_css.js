/*
 * CSS directories and files to minimize
 *
 * @since 5.2
 */
const compatiblityCSS = {};

const foldersCSS = {
			'default':				'../css/',
			'framework':			'../framework/css/',
			'framework_cond':		'../framework/css/conditional_load/',
			'aviapopup':			'../js/aviapopup/',
			'config-bbpress':		'../config-bbpress/',
			'config-tribe':			'../config-events-calendar/',
			'config-gravity':		'../config-gravityforms/',
			'config-gutenberg':		'../config-gutenberg/css/',
			'config-woocommerce':	'../config-woocommerce/',
			'config-wc-bookings':	'../config-woocommerce/config-woocommerce-bookings/',
			'config-wc-admin':		'../config-woocommerce/admin/',
			'config-wpml':			'../config-wpml/',
			'config-alb-assets':	'../config-templatebuilder/avia-template-builder/assets/css/'

		};

const filegroupsCSS = {

			'default': [
							'admin-preview',
							'avia-snippet-cookieconsent',
							'avia-snippet-lightbox',
							'avia-snippet-site-preloader',
							'avia-snippet-widget',
							'base',
							'grid',
							'layout',
							'rtl',
							'shortcodes',
							'avia-snippet-fold-unfold'
						],

			'framework': [
							'avia_admin',
							'avia_colorpicker',
							'avia_sidebar'
						],

			'framework_cond': [
							'avia_admin_modern',
							'avia_block_widgets',
							'avia_gallery_mode',
							'avia_global_admin'
						],

			'aviapopup': [ 'magnific-popup' ],

			'config-bbpress':		[ 'bbpress-mod' ],
			'config-tribe':			[ 'event-mod' ],
			'config-gravity':		[ 'gravity-mod' ],
			'config-gutenberg':		[ 'avia_gutenberg', 'avia-gutenberg-editor' ],
			'config-woocommerce':	[ 'woocommerce-mod' ],
			'config-wc-bookings':	[ 'woocommerce-booking-mod' ],
			'config-wc-admin':		[ 'woo-admin-blocks' ],
			'config-wpml':			[ 'wpml-mod' ],

			'config-alb-assets': [
									'avia-builder-rtl',
									'avia-builder',
									'avia-custom-elements',
									'avia-media',
									'avia-modal'
								]
		};


const modulesCSS = {

			'audio-player':				[ 'audio-player' ],
			'blog':						[ 'blog' ],
			'buttonrow':				[ 'buttonrow' ],
			'buttons':					[ 'buttons' ],
			'buttons_fullwidth':		[ 'buttons_fullwidth' ],
			'catalogue':				[ 'catalogue' ],
			'comments':					[ 'comments' ],
			'contact':					[ 'contact' ],
			'contentslider':			[ 'contentslider' ],
			'countdown':				[ 'countdown' ],
			'events_upcoming':			[ 'events_upcoming' ],
			'gallery':					[ 'gallery' ],
			'gallery_horizontal':		[ 'gallery_horizontal' ],
			'google_maps':				[ 'google_maps' ],
			'grid_row':					[ 'grid_row' ],
			'heading':					[ 'heading' ],
			'headline_rotator':			[ 'headline_rotator' ],
			'hr':						[ 'hr' ],
			'icon':						[ 'icon' ],
			'icon_circles':				[ 'icon_circles' ],
			'iconbox':					[ 'iconbox' ],
			'icongrid':					[ 'icongrid' ],
			'iconlist':					[ 'iconlist' ],
			'image':					[ 'image' ],
			'image_diff':				[ 'image_diff' ],
			'image_hotspots':			[ 'image_hotspots' ],
			'lottie_animation':			[ 'lottie_animation' ],
			'magazine':					[ 'magazine' ],
			'masonry_entries':			[ 'masonry_entries' ],
			'menu':						[ 'menu' ],
			'notification':				[ 'notification' ],
			'numbers':					[ 'numbers' ],
			'portfolio':				[ 'portfolio' ],
			'post_metadata':			[ 'post_metadata' ],
			'postslider':				[ 'postslider' ],
			'progressbar':				[ 'progressbar' ],
			'promobox':					[ 'promobox' ],
			'search':					[ 'search' ],
			'slideshow':				[ 'slideshow' ],
			'slideshow_accordion':		[ 'slideshow_accordion' ],
			'slideshow_feature_image':	[ 'slideshow_feature_image' ],
			'slideshow_fullscreen':		[ 'slideshow_fullscreen' ],
			'slideshow_fullsize':		[ 'slideshow_fullsize' ],
			'slideshow_layerslider':	[ 'slideshow_layerslider' ],
			'social_share':				[ 'social_share' ],
			'tab_section':				[ 'tab_section' ],
			'table':					[ 'table' ],
			'tabs':						[ 'tabs' ],
			'team':						[ 'team' ],
			'testimonials':				[ 'testimonials' ],
			'timeline':					[ 'timeline' ],
			'toggles':					[ 'toggles' ],
			'video':					[ 'video' ]
		};

exports.foldersCSS = foldersCSS;
exports.filegroupsCSS = filegroupsCSS;
exports.modulesCSS = modulesCSS;
exports.compatiblityCSS = compatiblityCSS;

