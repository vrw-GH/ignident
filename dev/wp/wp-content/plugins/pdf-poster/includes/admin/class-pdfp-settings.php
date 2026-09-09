<?php

namespace PDFPro\Admin;

use PDFPro\Helper\PDFP_Functions as Utils;

if (! defined('ABSPATH')) exit;

if ( ! class_exists( 'PDFPro\Admin\PDFP_Settings' ) ) {
	class PDFP_Settings {

	private $option_prefix = 'fpdf_option';
	public function register() {
		add_action('init', array($this, 'init'), 0);
	}


	public function init() {
		if (class_exists('\CSF')) {
			\CSF::createOptions($this->option_prefix, array(
				'framework_title' => __('PDF Poster Settings', 'pdf-poster'),
				'menu_title'  => __('Settings', 'pdf-poster'),
				'menu_slug'   => 'fpdf-settings',
				'menu_type'   => 'submenu',
				'menu_parent' => 'edit.php?post_type=pdfposter',
				'theme' => 'light',
				'show_bar_menu' => false,
				'footer_text' => 'Thank you for using PDF Poster',
			));
			

			$this->shortcode();
			$this->gutenberg_integration();
			$this->custom_css();
			$this->preset();
			$this->cloud_api();
		}
	}

	public function shortcode() {
		\CSF::createSection($this->option_prefix, array(
			'title' => __('Quick Embedder', 'pdf-poster'),
			'fields' => array(
				array(
					'type'    => 'content',
					'content' => '
						<div class="pdfp-docs-notice">
							<div class="pdfp-docs-notice-icon">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
							</div>
							<div class="pdfp-docs-notice-content">
								<h4>' . __('Documentation & Help', 'pdf-poster') . '</h4>
								<p>' . __('Need help configuring the Quick Embedder? Check out our full documentation for expert tips and advanced settings.', 'pdf-poster') . '</p>
							</div>
							<div class="pdfp-docs-notice-action">
								<a href="https://bplugins.com/docs/pdf-poster/settings/quick-embedder-2/" target="_blank" class="pdfp-docs-btn">
									' . __('View Documentation', 'pdf-poster') . '
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
								</a>
							</div>
						</div>
					'
				),
				Utils::quick_embed_shortcode(),
				array(
					'id' => 'height',
					'title' => __('Viewer Height', 'pdf-poster'),
					'type' => 'dimensions',
					'default' => [
						'height' => '800',
						'unit' => 'px'
					],
					'width' => false,
					'desc' => __('Set the height of the PDF viewer.', 'pdf-poster')
				),
				array(
					'id' => 'width',
					'title' => __('Viewer Width', 'pdf-poster'),
					'type' => 'dimensions',
					'default' => [
						'width' => '100',
						'unit' => '%'
					],
					'height' => false,
					'desc' => __('Set the width of the PDF viewer.', 'pdf-poster')
				),
				array(
					'id' => 'show_filename',
					'title' => __('Display Filename', 'pdf-poster'),
					'type' => 'switcher',
					'desc' => __('Show the filename at the top of the viewer.', 'pdf-poster')
				),
				array(
					'id' => 'show_download_btn',
					'title' => __('Download Button', 'pdf-poster'),
					'type' => 'switcher',
					'desc' => __('Display a download button at the top of the viewer.', 'pdf-poster')
				),
				array(
					'id' => 'download_btn_text',
					'title' => __('Download Label', 'pdf-poster'),
					'type' => 'text',
					'default' => 'Download File',
					'desc' => __('Custom text for the download button.', 'pdf-poster'),
					'dependency' => array('show_download_btn', '==', '1')
				),
				Utils::pro_feature_list(array(
					__('Enable Printing', 'pdf-poster'),
					__('Default Browser Viewer Support', 'pdf-poster'),
					__('Premium Fullscreen Button & Label', 'pdf-poster'),
					__('Open Fullscreen in New Tab', 'pdf-poster'),
					__('Advanced Content Protection (Disable Right-Click)', 'pdf-poster'),
					__('Suppress Blocked Warning Alerts', 'pdf-poster'),
					__('Enable Thumbnails Navigation', 'pdf-poster'),
				)),
			)
		));
	}

	public function gutenberg_integration() {
		\CSF::createSection($this->option_prefix, array(
			'title' => __('Shortcode', 'pdf-poster'),
			'fields' => array(
				array(
					'id' => 'pdfp_gutenberg_enable',
					'type' => 'switcher',
					'title' => __('Gutenberg Integration', 'pdf-poster'),
					'desc' => __('Enable the PDF Poster block and shortcode generator in the Gutenberg editor.', 'pdf-poster'),
					'default' => get_option('pdfp_gutenberg_enable', false)
				)
			)
		));
	}

	public function custom_css() {
		\CSF::createSection($this->option_prefix, array(
			'title' => Utils::pdfp_pro_title(__('Custom CSS', 'pdf-poster')),
			'fields' => array(
				Utils::pro_feature_list(array(
					__('Full Design Control & Branding', 'pdf-poster'),
					__('Override Default Viewer Styles', 'pdf-poster'),
					__('Global CSS Application', 'pdf-poster'),
				)),
			)
		));
	}

	public function preset() {
		\CSF::createSection($this->option_prefix, array(
			'title' => Utils::pdfp_pro_title(__('Presets', 'pdf-poster')),
			'fields' => array(
				Utils::pro_feature_list(array(
					__('Global Site-Wide Defaults', 'pdf-poster'),
					__('Automatic Settings Application', 'pdf-poster'),
					__('Consistent Viewer Experience', 'pdf-poster'),
					__('Classic Shortcode Integration', 'pdf-poster'),
				)),
			)
		));
	}

	public function cloud_api() {
		\CSF::createSection($this->option_prefix, array(
			'title' => Utils::pdfp_pro_title(__('Cloud Integration', 'pdf-poster')),
			'fields' => array(
				Utils::pro_feature_list(array(
					__('Direct Dropbox Connectivity', 'pdf-poster'),
					__('Google Drive Cloud Picker', 'pdf-poster'),
					__('Premium Adobe PDF Embed API', 'pdf-poster'),
					__('Seamless External Hosting', 'pdf-poster'),
				)),
			)
		));
	}
}
}
