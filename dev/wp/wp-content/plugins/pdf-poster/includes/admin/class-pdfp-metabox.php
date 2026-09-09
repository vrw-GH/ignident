<?php

namespace PDFPro\Admin;

use PDFPro\Helper\PDFP_Functions as Utils;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('PDFPro\Admin\PDFP_MetaBox')) {
	class PDFP_MetaBox {
		private $metabox_prefix = '_fpdf';
		private $option = null;

		public function register() {
			add_action('init', array($this, 'register_metabox'), 0);
		}

		public function register_metabox() {
			if (class_exists('\CSF')) {
				\CSF::createMetabox($this->metabox_prefix, array(
					'title' => __('PDF Poster Configuration', 'pdf-poster'),
					'post_type' => 'pdfposter',
					'theme' => 'light'
				));

				$this->configure();
				$this->controls();
				$this->actions();
				$this->popup();
				$this->protect_content();
				$this->watermark();
				$this->social_share();
				$this->styles();
				$this->performance();
				$this->ads();
				$this->analytics();
			}
		}

		/**
		 * Options for the Viewer button set.
		 *
		 * FlipBook and Slider are free, but they render through dFlip -- so they are only
		 * offered when that engine is actually on disk. Adobe needs the premium PDF Embed
		 * bridge and Scroll is premium-only; both are still listed so they keep selling.
		 * Wrapping their labels in .pdfp-lock-field .pdfp-pro-option makes the
		 * PDFP_ProModal click handler open the upgrade modal instead of selecting them,
		 * leaving the current choice untouched.
		 */
		private function viewer_options() {
			$options = array(
				'default' => __('Default', 'pdf-poster'),
			); 

			if (Utils::pdfp_has_flipbook_engine()) {
				$options['flipbook'] = __('FlipBook', 'pdf-poster') . Utils::pdfp_new_badge();
				$options['slider'] = __('Slider', 'pdf-poster') . Utils::pdfp_new_badge();
			} 
			return $options;
		}

		public function configure() {
			if (!$this->option) {
				$this->option = get_option('fpdf_option');
			}

			\CSF::createSection($this->metabox_prefix, array(
				'title' => 'General',
				'fields' => array(
					array(
						'id' => 'viewer',
						'type' => 'button_set',
						'title' => __('Viewer', 'pdf-poster'),
						'desc' => __('Select the PDF viewer engine.', 'pdf-poster'),
						'default' => 'default',
						'options' => $this->viewer_options()
					),
					array(
						'id' => 'source',
						'type' => 'upload',
						'title' => __('PDF Source', 'pdf-poster'),
						'desc' => __('Select or upload your PDF file.', 'pdf-poster'),
						'attributes' => array('id' => 'picker_field')
					),
					array(
						'id' => 'flipbook_source_type',
						'title' => __('Viewer Source', 'pdf-poster') . Utils::pdfp_new_badge(),
						'type' => 'button_set',
						'default' => 'pdf',
						'options' => array(
							'pdf' => __('PDF File', 'pdf-poster'),
							'images' => __('Image Gallery', 'pdf-poster'),
						),
						'desc' => __('Build the flipbook, slider or scroll view from a PDF file or from an ordered set of images.', 'pdf-poster'),
						'dependency' => array('viewer', 'any', 'flipbook,slider,scroll', true)
					),
					array(
						'id' => 'flipbook_images',
						'title' => __('Pages (Images)', 'pdf-poster'),
						'type' => 'gallery',
						'desc' => __('Select images in the order they should appear as pages.', 'pdf-poster'),
						'dependency' => array(
							array('flipbook_source_type', '==', 'images'),
							array('viewer', 'any', 'flipbook,slider,scroll', true),
						)
					),
					array(
						'id' => 'device_preview',
						'type' => 'button_set',
						'title' => __('Preview Device', 'pdf-poster') . Utils::pdfp_new_badge(),
						'options' => array(
							'desktop' => __('Desktop', 'pdf-poster'),
							'tablet' => __('Tablet', 'pdf-poster'),
							'mobile' => __('Mobile', 'pdf-poster'),
						),
						'default' => 'desktop',
					),
					array(
						'id' => 'height',
						'title' => __('Height (Desktop)', 'pdf-poster'),
						'type' => 'dimensions',
						'width' => false,
						'desc' => __('Set the height of the viewer for desktop.', 'pdf-poster'),
						'default' => Utils::pdfp_preset('preset_height', [
							'height' => 842,
							'unit' => 'px'
						]),
						'dependency' => array('device_preview', '==', 'desktop')
					),
					array(
						'id' => 'height_tablet',
						'title' => __('Height (Tablet)', 'pdf-poster'),
						'type' => 'dimensions',
						'width' => false,
						'desc' => __('Set the height of the viewer for tablet.', 'pdf-poster'),
						'default' => [
							'height' => 700,
							'unit' => 'px'
						],
						'dependency' => array('device_preview', '==', 'tablet')
					),
					array(
						'id' => 'height_mobile',
						'title' => __('Height (Mobile)', 'pdf-poster'),
						'type' => 'dimensions',
						'width' => false,
						'desc' => __('Set the height of the viewer for mobile.', 'pdf-poster'),
						'default' => [
							'height' => 400,
							'unit' => 'px'
						],
						'dependency' => array('device_preview', '==', 'mobile')
					),
					array(
						'id' => 'width',
						'title' => __('Width (Desktop)', 'pdf-poster'),
						'type' => 'dimensions',
						'height' => false,
						'desc' => __('Set the width of the viewer for desktop.', 'pdf-poster'),
						'default' => Utils::pdfp_preset('preset_width', [
							'width' => '100',
							'unit' => '%'
						]),
						'dependency' => array('device_preview', '==', 'desktop')
					),
					array(
						'id' => 'width_tablet',
						'title' => __('Width (Tablet)', 'pdf-poster'),
						'type' => 'dimensions',
						'height' => false,
						'desc' => __('Set the width of the viewer for tablet.', 'pdf-poster'),
						'default' => [
							'width' => '100',
							'unit' => '%'
						],
						'dependency' => array('device_preview', '==', 'tablet')
					),
					array(
						'id' => 'width_mobile',
						'title' => __('Width (Mobile)', 'pdf-poster'),
						'type' => 'dimensions',
						'height' => false,
						'desc' => __('Set the width of the viewer for mobile.', 'pdf-poster'),
						'default' => [
							'width' => '100',
							'unit' => '%'
						],
						'dependency' => array('device_preview', '==', 'mobile')
					),
					Utils::pro_feature_list(array(
						__('Industry-Leading Adobe Viewer', 'pdf-poster'),
						__('Continuous Scroll Viewer for Long Reports', 'pdf-poster'),
						__('Effortless Cloud Sync (Dropbox & Google Drive)', 'pdf-poster'),
					)),
				)
			));
		}

		public function controls()
		{
			\CSF::createSection($this->metabox_prefix, array(
				'title' => __('Controls', 'pdf-poster'),
				'fields' => array(
					array(
						'id' => 'show_filename',
						'title' => __('Display Filename', 'pdf-poster'),
						'type' => 'switcher',
						'default' => Utils::pdfp_preset('preset_show_filename', true),
						'desc' => __('Show the filename at the top of the viewer.', 'pdf-poster')
					),
					array(
						'id' => 'keyboard_nav',
						'title' => __('Keyboard Navigation', 'pdf-poster') . Utils::pdfp_new_badge(),
						'type' => 'switcher',
						'default' => Utils::pdfp_preset('preset_keyboard_nav', false),
						'desc' => __('Let visitors use the Left/Right arrow keys to change pages.', 'pdf-poster')
					),
					array(
						'id' => 'rtl_mode',
						'title' => __('RTL Layout', 'pdf-poster') . Utils::pdfp_new_badge(),
						'type' => 'button_set',
						'default' => 'off',
						'options' => array(
							'off' => __('Off', 'pdf-poster'),
							'on' => __('On', 'pdf-poster'),
							'auto' => __('Auto', 'pdf-poster'),
						),
						'desc' => __('Flip the viewer layout for right-to-left languages (Arabic, Hebrew, etc.). "Auto" follows the site language.', 'pdf-poster')
					),
					array(
						'id' => 'theme_mode',
						'title' => __('Viewer Theme', 'pdf-poster') . Utils::pdfp_new_badge(),
						'type' => 'button_set',
						'default' => 'light',
						'options' => array(
							'light' => __('Light', 'pdf-poster'),
							'dark' => __('Dark', 'pdf-poster'),
							'auto' => __('Auto', 'pdf-poster'),
						),
						'desc' => __('Controls the viewer toolbar/background theme. "Auto" follows the visitor\'s system. This never changes the PDF page content itself.', 'pdf-poster')
					),
					array(
						'id' => 'flipbook_sound',
						'title' => __('Page Flip Sound', 'pdf-poster') . Utils::pdfp_new_badge(),
						'type' => 'switcher',
						'default' => Utils::pdfp_preset('preset_flipbook_sound', true),
						'desc' => __('Play a page-turn sound effect in Flipbook and Slider modes.', 'pdf-poster'),
						'dependency' => array('viewer', 'any', 'flipbook,slider', true)
					),
					array(
						'id' => 'annotation_mode',
						'title' => __('Annotation Mode', 'pdf-poster') . Utils::pdfp_new_badge(),
						'type' => 'switcher',
						'default' => Utils::pdfp_preset('preset_annotation_mode', true),
						'desc' => __('Show notes, highlights, comments, and clickable links that are saved inside the PDF.', 'pdf-poster'),
						'dependency' => array('viewer', '==', 'default', true)
					),
					array(
						'id' => 'open_links_in_new_tab',
						'title' => __('Open PDF links in new tab', 'pdf-poster') . Utils::pdfp_new_badge(),
						'type' => 'switcher',
						'default' => Utils::pdfp_preset('preset_open_links_in_new_tab', false),
						'desc' => __('Open links clicked inside the PDF in a new browser tab, keeping your current page open.', 'pdf-poster'),
						'dependency' => array(
							array('viewer', '==', 'default', true),
							array('annotation_mode', '==', '1', true)
						)
					),
					Utils::pro_feature_list(array(
						__("Distraction-Free 'Reader Mode'", 'pdf-poster'),
						__('Toggle Thumbnails Navigation', 'pdf-poster'),
						__('Auto-Open Sidebar by Default', 'pdf-poster'),
						__('Horizontal Scrollbar Support', 'pdf-poster'),
						__('Custom Initial Page & Zoom Level', 'pdf-poster'),
						__('Hide the Right-Side Toolbar', 'pdf-poster'),
						__('Adobe Embed Modes & View Modes', 'pdf-poster'),
					)),
				)
			));
		}

		public function actions()
		{
			\CSF::createSection($this->metabox_prefix, array(
				'title' => __('Actions', 'pdf-poster'),
				'fields' => array(
					array(
						'id' => 'print',
						'title' => __('Allow Printing', 'pdf-poster'),
						'type' => 'switcher',
						'default' => Utils::pdfp_preset('preset_print'),
						'desc' => __('Allow visitors to print the PDF document.', 'pdf-poster')
					),
					array(
						'id' => 'show_download_btn',
						'title' => __('Download Button', 'pdf-poster'),
						'type' => 'switcher',
						'default' => Utils::pdfp_preset('preset_show_download_btn', true),
						'desc' => __('Display a download button at the top of the viewer.', 'pdf-poster'),
						'dependency' => array('flipbook_source_type', '!=', 'images')
					),
					array(
						'id' => 'fullscreen_btn_text',
						'title' => __('Fullscreen Label', 'pdf-poster'),
						'type' => 'text',
						'desc' => __('Customize the text for the fullscreen button.', 'pdf-poster'),
						'default' => Utils::pdfp_preset('preset_fullscreen_btn_text', 'View Fullscreen')
					),
					Utils::pro_feature_list(array(
						__('Customize Download Button Label', 'pdf-poster'),
						__('Open Fullscreen in New Tab', 'pdf-poster'),
						__('Custom Actions Position (Top/Bottom)', 'pdf-poster'),
					)),
				)
			));
		}


		public function popup()
		{
			\CSF::createSection($this->metabox_prefix, array(
				'title' => Utils::pdfp_pro_title(__('Popup', 'pdf-poster')),
				'fields' => array(
					Utils::pro_feature_list(array(
						__('Enable Modal Popups', 'pdf-poster'),
						__('Multiple Trigger Types (Button/Image)', 'pdf-poster'),
						__('Custom Trigger Alignment', 'pdf-poster'),
						__('PDF Icon Overlay on Images', 'pdf-poster'),
					)),
				),
			));
		}

		public function protect_content()
		{
			\CSF::createSection($this->metabox_prefix, array(
				'title' => Utils::pdfp_pro_title(__('Protect Content', 'pdf-poster')),
				'fields' => array(
					Utils::pro_feature_list(array(
						__('Disable Right-Click Interactions', 'pdf-poster'),
						__('Disable Text Selection', 'pdf-poster'),
						__('Suppress Blocked Warning Alerts', 'pdf-poster'),
					)),
				)
			));
		}

		public function watermark()
		{
			\CSF::createSection($this->metabox_prefix, array(
				'title' => Utils::pdfp_pro_title(__('Watermark & Branding', 'pdf-poster')),
				'fields' => array(
					// Shown rather than listed: the six shipped looks sell this section
					// better than a row of labels, so the ledger gives way to the card
					// the Pro build opens the section with.
					Utils::pdfp_watermark_intro(),
				)
			));
		}

		public function social_share()
		{
			\CSF::createSection($this->metabox_prefix, array(
				'title' => __('Social Share', 'pdf-poster'),
				'fields' => array(
					array(
						'id' => 'social_share',
						'title' => __('Enable Sharing', 'pdf-poster'),
						'type' => 'switcher',
						'desc' => esc_html__('Enable social sharing buttons for the PDF.', 'pdf-poster'),
						'default' => false,
					),
					array(
						'id' => 'social_share_position',
						'title' => __('Share Position', 'pdf-poster'),
						'type' => 'select',
						'desc' => esc_html__('Select where the sharing buttons should appear.', 'pdf-poster'),
						'default' => 'top',
						'options' => array(
							'top' => esc_html__('Top', 'pdf-poster'),
							'bottom' => esc_html__('Bottom', 'pdf-poster'),
						),
						'dependency' => array('social_share', '==', '1', true)
					),
					array(
						'id' => 'social_share_facebook',
						'title' => __('Enable Facebook', 'pdf-poster'),
						'type' => 'switcher',
						'desc' => esc_html__('Allow sharing on Facebook.', 'pdf-poster'),
						'default' => true,
						'dependency' => array('social_share', '==', '1', true)
					),
					array(
						'id' => 'social_share_twitter',
						'title' => __('Enable Twitter', 'pdf-poster'),
						'type' => 'switcher',
						'desc' => esc_html__('Allow sharing on Twitter.', 'pdf-poster'),
						'default' => true,
						'dependency' => array('social_share', '==', '1', true)
					),
					array(
						'id' => 'social_share_linkedin',
						'title' => __('Enable LinkedIn', 'pdf-poster'),
						'type' => 'switcher',
						'desc' => esc_html__('Allow sharing on LinkedIn.', 'pdf-poster'),
						'default' => true,
						'dependency' => array('social_share', '==', '1', true)
					),
					array(
						'id' => 'social_share_pinterest',
						'title' => __('Enable Pinterest', 'pdf-poster'),
						'type' => 'switcher',
						'desc' => esc_html__('Allow sharing on Pinterest.', 'pdf-poster'),
						'default' => true,
						'dependency' => array('social_share', '==', '1')
					),
					array(
						'id' => 'social_share_mailto',
						'title' => __('Enable Email', 'pdf-poster') . Utils::pdfp_new_badge(),
						'type' => 'switcher',
						'desc' => esc_html__('Allow sharing via Email.', 'pdf-poster'),
						'default' => true,
						'dependency' => array('social_share', '==', '1')
					),
				)
			));
		}

		public function styles()
		{
			\CSF::createSection($this->metabox_prefix, array(
				'title' => __('Styles', 'pdf-poster'),
				'fields' => array(
					array(
						'id' => 'popup_btn_bg',
						'title' => __('Button Background', 'pdf-poster'),
						'type' => 'color',
						'desc' => __('Choose a background color for the buttons.', 'pdf-poster'),
						'default' => '#1e73be',
					),
					array(
						'id' => 'popup_btn_color',
						'title' => __('Button Color', 'pdf-poster'),
						'type' => 'color',
						'desc' => __('Choose a text color for the buttons.', 'pdf-poster'),
						'default' => '#fff'
					),
					array(
						'id' => 'popup_btn_font_size',
						'title' => __('Font Size', 'pdf-poster'),
						'type' => 'number',
						'desc' => esc_html__('Set the font size for the buttons.', 'pdf-poster'),
						'default' => 1,
						'unit' => 'rem'
					),
					array(
						'id' => 'popup_btn_padding',
						'title' => __('Padding', 'pdf-poster'),
						'type' => 'spacing',
						'desc' => __('Set the internal spacing for the buttons.', 'pdf-poster'),
						'default' => [
							'top' => '10',
							'bottom' => '10',
							'left' => '20',
							'right' => '20',
						],
						'units' => array('px')
					),
				),
			));
		}

		public function performance()
		{
			\CSF::createSection($this->metabox_prefix, array(
				'title' => __('Performance & Reliability', 'pdf-poster'),
				'fields' => array(
					array(
						'id' => 'progressive_loading',
						'title' => __('Fast Loading (Progressive Rendering)', 'pdf-poster') . Utils::pdfp_new_badge(),
						'type' => 'switcher',
						'default' => Utils::pdfp_preset('preset_progressive_loading', true),
						'desc' => __('Stream large PDFs so the first page appears sooner. Turn off only if your host mishandles range requests.', 'pdf-poster'),
					),
					array(
						'id' => 'default_browser',
						'title' => __('Google Doc Viewer', 'pdf-poster') . Utils::pdfp_new_badge(),
						'type' => 'switcher',
						'default' => Utils::pdfp_preset('preset_default_browser'),
						'desc' => __('Enable Google Doc Viewer as a fallback (Recommended for Edge).', 'pdf-poster'),
					),
					Utils::pro_feature_list(array(
						__('Load Latest Document Version', 'pdf-poster'),
					)),
				)
			));
		}

		public function ads()
		{
			\CSF::createSection($this->metabox_prefix, array(
				'title' => Utils::pdfp_pro_title(__('Ads', 'pdf-poster'), "Upcoming"),
				'fields' => array(
					Utils::upcoming_section()
				)
			));
		}

		public function analytics()
		{
			\CSF::createSection($this->metabox_prefix, array(
				'title' => Utils::pdfp_pro_title(__('Analytics', 'pdf-poster'), "Upcoming"),
				'fields' => array(
					Utils::upcoming_section()
				)
			));
		}

	}
}
