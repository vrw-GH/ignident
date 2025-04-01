/*
 * Main file to minimize theme js files ONLY
 * =========================================
 *
 * Only kept in case we find a CSS solution in future
 *
 * > npm install
 * > npm run build
 *
 * @since 5.2
 */
const path = require('path');

const framework = 'framework/js/';
const alb_assets = 'config-templatebuilder/avia-template-builder/assets/js/';
const alb_modules = 'config-templatebuilder/avia-shortcodes/';

module.exports = {
	context: path.resolve( __dirname, '' ),
	mode: 'production',

	output: {
			path: path.resolve( __dirname, '../' )
		},
	entry: {

		//	files in /js
		'avia-compat': { import: '../js/avia-compat.js', filename: 'js/avia-compat.min.js' },
		'avia-default': { import: '../js/avia.js', filename: 'js/avia.min.js' },
		'avia-snippet-hamburger-menu': { import: '../js/avia-snippet-hamburger-menu.js', filename: 'js/avia-snippet-hamburger-menu.min.js' },
		'avia-shortcodes': { import: '../js/shortcodes.js', filename: 'js/shortcodes.min.js' },
		'avia-parallax-support': { import: '../js/avia-snippet-parallax.js', filename: 'js/avia-snippet-parallax.min.js' },
		'avia-lightbox-activation': { import: '../js/avia-snippet-lightbox.js', filename: 'js/avia-snippet-lightbox.min.js' },
		'avia-megamenu': { import: '../js/avia-snippet-megamenu.js', filename: 'js/avia-snippet-megamenu.min.js' },
		'avia-sidebarmenu': { import: '../js/avia-snippet-sidebarmenu.js', filename: 'js/avia-snippet-sidebarmenu.min.js' },
		'avia-sticky-header': { import: '../js/avia-snippet-sticky-header.js', filename: 'js/avia-snippet-sticky-header.min.js' },
		'avia-footer-effects': { import: '../js/avia-snippet-footer-effects.js', filename: 'js/avia-snippet-footer-effects.min.js' },
		'avia-siteloader-js': { import: '../js/avia-snippet-site-preloader.js', filename: 'js/avia-snippet-site-preloader.min.js' },
		'avia-widget-js': { import: '../js/avia-snippet-widget.js', filename: 'js/avia-snippet-widget.min.js' },
		'avia-cookie-js': { import: '../js/avia-snippet-cookieconsent.js', filename: 'js/avia-snippet-cookieconsent.min.js' },
		'avia-popup-js': { import: '../js/aviapopup/jquery.magnific-popup.js', filename: 'js/aviapopup/jquery.magnific-popup.min.js' },
		'avia-waypoints': { import: '../js/waypoints/waypoints.js', filename: 'js/waypoints/waypoints.min.js' },

		//	files in /framework/js
		'avia_fw_advanced_form_elements': { import: '../' + framework + 'avia_advanced_form_elements.js', filename: framework + 'avia_advanced_form_elements.min.js' },
		'avia_fw_colorpicker': { import: '../' + framework + 'avia_colorpicker.js', filename: framework + 'avia_colorpicker.min.js' },
		'avia_fw_media': { import: '../' + framework + 'avia_media.js', filename: framework + 'avia_media.min.js' },
		'avia_fw_media_advanced': { import: '../' + framework + 'avia_media_advanced.js', filename: framework + 'avia_media_advanced.min.js' },
		'avia_fw_media_wp35': { import: '../' + framework + 'avia_media_wp35.js', filename: framework + 'avia_media_wp35.min.js' },
		'avia_fw_mega_menu': { import: '../' + framework + 'avia_mega_menu.js', filename: framework + 'avia_mega_menu.min.js' },
		'avia_fw_option_pages': { import: '../' + framework + 'avia_option_pages.js', filename: framework + 'avia_option_pages.min.js' },
		'avia_fw_sidebar': { import: '../' + framework + 'avia_sidebar.js', filename: framework + 'avia_sidebar.min.js' },

		//	files in /framework/js/conditional_load/
		'avia_fw_conditional-mega-menu': { import: '../' + framework + 'conditional_load/avia_conditional_mega_menu.js', filename: framework + 'conditional_load/avia_conditional_mega_menu.min.js' },
		'avia_fw_facebook_front_script': { import: '../' + framework + 'conditional_load/avia_facebook_front.js', filename: framework + 'conditional_load/avia_facebook_front.min.js' },
		'avia_fw_google_maps_front_script': { import: '../' + framework + 'conditional_load/avia_google_maps_front.js', filename: framework + 'conditional_load/avia_google_maps_front.min.js' },
		'avia_fw_google_maps_api_script': { import: '../' + framework + 'conditional_load/avia_google_maps_api.js', filename: framework + 'conditional_load/avia_google_maps_api.min.js' },
		'avia_fw_google_maps_widget_admin_script': { import: '../' + framework + 'conditional_load/avia_google_maps_widget_admin.js', filename: framework + 'conditional_load/avia_google_maps_widget_admin.min.js' },
		'avia_fw_google_recaptcha_front_script': { import: '../' + framework + 'conditional_load/avia_google_recaptcha_front.js', filename: framework + 'conditional_load/avia_google_recaptcha_front.min.js' },
		'avia_fw_google_recaptcha_api_script': { import: '../' + framework + 'conditional_load/avia_google_recaptcha_api.js', filename: framework + 'conditional_load/avia_google_recaptcha_api.min.js' },

		//	files in /config-*  (excl. templatebuilder)
		'avia_gutenberg_script': { import: '../config-gutenberg/js/avia_gutenberg.js', filename: 'config-gutenberg/js/avia_gutenberg.min.js' },
		'avia_blocks_front_script': { import: '../config-gutenberg/js/avia_blocks_front.js', filename: 'config-gutenberg/js/avia_blocks_front.min.js' },
		'avia_leaflet_maps_backend_script': { import: '../config-leaflet-maps/js/avia-leaflet-maps.js', filename: 'config-leaflet-maps/js/avia-leaflet-maps.min.js' },
		'avia_rank_math_js': { import: '../config-rank-math/rank-math-mod.js', filename: 'config-rank-math/rank-math-mod.min.js' },
		'avia_woocommerce-js': { import: '../config-woocommerce/woocommerce-mod.js', filename: 'config-woocommerce/woocommerce-mod.min.js' },
		'avia_yoast_seo_js': { import: '../config-wordpress-seo/wpseo-mod.js', filename: 'config-wordpress-seo/wpseo-mod.min.js' },
		'avia_wpml-script': { import: '../config-wpml/wpml-mod.js', filename: 'config-wpml/wpml-mod.min.js' },

		//	files in /config-templatebuilder/avia-template-builder/assets/js/
		'avia_alb_admin-preview': { import: '../' + alb_assets + 'avia-admin-preview.js', filename: alb_assets + 'avia-admin-preview.min.js' },
		'avia_alb_analytics': { import: '../' + alb_assets + 'avia-analytics.js', filename: alb_assets + 'avia-analytics.min.js' },
		'avia_alb_builder_js': { import: '../' + alb_assets + 'avia-builder.js', filename: alb_assets + 'avia-builder.min.js' },
		'avia_alb_element_js': { import: '../' + alb_assets + 'avia-element-behavior.js', filename: alb_assets + 'avia-element-behavior.min.js' },
		'avia_alb_modal_js': { import: '../' + alb_assets + 'avia-modal.js', filename: alb_assets + 'avia-modal.min.js' },
		'avia_alb_custom_elements_js': { import: '../' + alb_assets + 'avia-custom-elements.js', filename: alb_assets + 'avia-custom-elements.min.js' },
		'avia_alb_history_js': { import: '../' + alb_assets + 'avia-history.js', filename: alb_assets + 'avia-history.min.js' },
		'avia_alb_tooltip_js': { import: '../' + alb_assets + 'avia-tooltip.js', filename: alb_assets + 'avia-tooltip.min.js' },
		'avia_alb_media_js': { import: '../' + alb_assets + 'avia-media.js', filename: alb_assets + 'avia-media.min.js' },
		'avia_alb_tab_section_js': { import: '../' + alb_assets + 'avia-tab-section.js', filename: alb_assets + 'avia-tab-section.min.js' },
		'avia_alb_tab_toggle_js': { import: '../' + alb_assets + 'avia-tab-toggle.js', filename: alb_assets + 'avia-tab-toggle.min.js' },
		'avia_alb_table_js': { import: '../' + alb_assets + 'avia-table.js', filename: alb_assets + 'avia-table.min.js' },
		'avia_alb_template_save_js': { import: '../' + alb_assets + 'avia-template-saving.js', filename: alb_assets + 'avia-template-saving.min.js' },
		'avia_alb_tinymce_btn_js': { import: '../' + alb_assets + 'avia-tinymce-buttons.js', filename: alb_assets + 'avia-tinymce-buttons.min.js' },
		'avia_alb_tinymce_btn4_js': { import: '../' + alb_assets + 'avia-tinymce-buttons-4.js', filename: alb_assets + 'avia-tinymce-buttons-4.min.js' },
		'avia_alb_tinymce_linebreak_js': { import: '../' + alb_assets + 'avia-tinymce-linebreak.js', filename: alb_assets + 'avia-tinymce-linebreak.min.js' },

		//	files in /config-templatebuilder/avia-shortcodes/
		'avia-module-audioplayer': { import: '../' + alb_modules + 'audio-player/audio-player.js', filename: alb_modules + 'audio-player/audio-player.min.js' },
		'avia-module-contact': { import: '../' + alb_modules + 'contact/contact.js', filename: alb_modules + 'contact/contact.min.js' },
		'avia-module-countdown': { import: '../' + alb_modules + 'countdown/countdown.js', filename: alb_modules + 'countdown/countdown.min.js' },
		'avia-module-gallery': { import: '../' + alb_modules + 'gallery/gallery.js', filename: alb_modules + 'gallery/gallery.min.js' },
		'avia-module-gallery-hor': { import: '../' + alb_modules + 'gallery_horizontal/gallery_horizontal.js', filename: alb_modules + 'gallery_horizontal/gallery_horizontal.min.js' },
		'avia-module-rotator': { import: '../' + alb_modules + 'headline_rotator/headline_rotator.js', filename: alb_modules + 'headline_rotator/headline_rotator.min.js' },
		'avia-module-icon-circles': { import: '../' + alb_modules + 'icon_circles/icon_circles.js', filename: alb_modules + 'icon_circles/icon_circles.min.js' },
		'avia-module-icongrid': { import: '../' + alb_modules + 'icongrid/icongrid.js', filename: alb_modules + 'icongrid/icongrid.min.js' },
		'avia-module-iconlist': { import: '../' + alb_modules + 'iconlist/iconlist.js', filename: alb_modules + 'iconlist/iconlist.min.js' },
		'avia-module-hotspot': { import: '../' + alb_modules + 'image_hotspots/image_hotspots.js', filename: alb_modules + 'image_hotspots/image_hotspots.min.js' },
		'avia-module-magazine': { import: '../' + alb_modules + 'magazine/magazine.js', filename: alb_modules + 'magazine/magazine.min.js' },
		'avia-module-masonry': { import: '../' + alb_modules + 'masonry_entries/masonry_entries.js', filename: alb_modules + 'masonry_entries/masonry_entries.min.js' },
		'avia-module-menu': { import: '../' + alb_modules + 'menu/menu.js', filename: alb_modules + 'menu/menu.min.js' },
		'avia-module-notification': { import: '../' + alb_modules + 'notification/notification.js', filename: alb_modules + 'notification/notification.min.js' },
		'avia-module-numbers': { import: '../' + alb_modules + 'numbers/numbers.js', filename: alb_modules + 'numbers/numbers.min.js' },
		'avia-module-portfolio': { import: '../' + alb_modules + 'portfolio/portfolio.js', filename: alb_modules + 'portfolio/portfolio.min.js' },
		'avia-module-progress-bar': { import: '../' + alb_modules + 'progressbar/progressbar.js', filename: alb_modules + 'progressbar/progressbar.min.js' },
		'avia-module-slideshow': { import: '../' + alb_modules + 'slideshow/slideshow.js', filename: alb_modules + 'slideshow/slideshow.min.js' },
		'avia-module-slideshow-video': { import: '../' + alb_modules + 'slideshow/slideshow-video.js', filename: alb_modules + 'slideshow/slideshow-video.min.js' },
		'avia-module-slideshow-accordion': { import: '../' + alb_modules + 'slideshow_accordion/slideshow_accordion.js', filename: alb_modules + 'slideshow_accordion/slideshow_accordion.min.js' },
		'avia-module-slideshow-fullscreen': { import: '../' + alb_modules + 'slideshow_fullscreen/slideshow_fullscreen.js', filename: alb_modules + 'slideshow_fullscreen/slideshow_fullscreen.min.js' },
		'avia-module-slideshow-ls': { import: '../' + alb_modules + 'slideshow_layerslider/slideshow_layerslider.js', filename: alb_modules + 'slideshow_layerslider/slideshow_layerslider.min.js' },
		'avia-module-tabsection': { import: '../' + alb_modules + 'tab_section/tab_section.js', filename: alb_modules + 'tab_section/tab_section.min.js' },
		'avia-module-tabs': { import: '../' + alb_modules + 'tabs/tabs.js', filename: alb_modules + 'tabs/tabs.min.js' },
		'avia-module-testimonials': { import: '../' + alb_modules + 'testimonials/testimonials.js', filename: alb_modules + 'testimonials/testimonials.min.js' },
		'avia-module-timeline': { import: '../' + alb_modules + 'timeline/timeline.js', filename: alb_modules + 'timeline/timeline.min.js' },
		'avia-module-toggles': { import: '../' + alb_modules + 'toggles/toggles.js', filename: alb_modules + 'toggles/toggles.min.js' },
		'avia-module-video': { import: '../' + alb_modules + 'video/video.js', filename: alb_modules + 'video/video.min.js' }
	}
};
