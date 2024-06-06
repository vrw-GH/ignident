<?php
/**
 * Accordion Slider
 *
 * Display an accordion slider with images or post entries
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_sc_slider_accordion', false ) )
{
	class avia_sc_slider_accordion extends aviaShortcodeTemplate
	{
		/**
		 *
		 * @var int
		 */
		static $slide_count = 0;

		/**
		 * Save avia_accordion_slider objects for reuse. As we need to access the same object when creating the post css file in header,
		 * create the styles and HTML creation. Makes sure to get the same id.
		 *
		 *			$element_id	=> avia_accordion_slider
		 *
		 * @since 4.8.9
		 * @var array
		 */
		protected $obj_accordion_slider = array();

		/**
		 * @since 4.8
		 * @param AviaBuilder $builder
		 */
		public function __construct( $builder )
		{
			parent::__construct( $builder );

			$this->obj_accordion_slider = array();
		}

		/**
		 * @since 4.8
		 */
		public function __destruct()
		{
			unset( $this->obj_accordion_slider );

			parent::__destruct();
		}

		/**
		 * Create the config array for the shortcode button
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['is_fullwidth']	= 'yes';
			$this->config['self_closing']	= 'no';
			$this->config['base_element']	= 'yes';

			$this->config['name']			= __( 'Accordion Slider', 'avia_framework' );
			$this->config['tab']			= __( 'Media Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-accordion-slider.png';
			$this->config['order']			= 20;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode'] 		= 'av_slideshow_accordion';
			$this->config['shortcode_nested'] = array( 'av_slide_accordion' );
			$this->config['tooltip'] 	    = __( 'Display an accordion slider with images or post entries', 'avia_framework' );
			$this->config['drag-level'] 	= 3;
			$this->config['preview'] 		= false;
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
			$this->config['alb_desc_id']	= 'alb_description';
			$this->config['name_item']		= __( 'Accordion Slider Item', 'avia_framework' );
			$this->config['tooltip_item']	= __( 'An Accordion Slider image or video item', 'avia_framework' );
		}

		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_js = avia_minify_extension( 'js' );
			$min_css = avia_minify_extension( 'css' );

			//load css
			wp_enqueue_style( 'avia-module-slideshow-accordion', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/slideshow_accordion/slideshow_accordion{$min_css}.css", array( 'avia-layout' ), $ver );

				//load js
			wp_enqueue_script( 'avia-module-slideshow-accordion', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/slideshow_accordion/slideshow_accordion{$min_js}.js", array( 'avia-shortcodes' ), $ver, true );
		}

		/**
		 * Popup Elements
		 *
		 * If this function is defined in a child class the element automatically gets an edit button, that, when pressed
		 * opens a modal window that allows to edit the element properties
		 *
		 * @return void
		 */
		protected function popup_elements()
		{
			$this->elements = array(

				array(
						'type' 	=> 'tab_container',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Content', 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'type'			=> 'template',
							'template_id'	=> 'toggle_container',
							'templates_include'	=> array(
													$this->popup_key( 'content_entries' ),
													$this->popup_key( 'content_filter' ),
													$this->popup_key( 'content_caption' )
												),
							'nodescription' => true
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Styling', 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'type'			=> 'template',
							'template_id'	=> 'toggle_container',
							'templates_include'	=> array(
													$this->popup_key( 'styling_general' ),
													$this->popup_key( 'styling_fonts' ),
													$this->popup_key( 'styling_colors' ),
												),
							'nodescription' => true
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Advanced', 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'type' 	=> 'toggle_container',
							'nodescription' => true
						),

						array(
								'type'			=> 'template',
								'template_id'	=> $this->popup_key( 'advanced_animation_slider' ),
								'nodescription' => true
							),

						array(
								'type'			=> 'template',
								'template_id'	=> $this->popup_key( 'advanced_heading' ),
								'nodescription' => true
							),

						array(
								'type'			=> 'template',
								'template_id'	=> 'lazy_loading_toggle',
								'lockable'		=> true
							),

						array(
								'type'			=> 'template',
								'template_id'	=> 'screen_options_toggle',
								'lockable'		=> true
							),

						array(
								'type'			=> 'template',
								'template_id'	=> 'developer_options_toggle',
								'args'			=> array( 'sc' => $this )
							),

					array(
							'type' 	=> 'toggle_container_close',
							'nodescription' => true
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type'			=> 'template',
						'template_id'	=> 'element_template_selection_tab',
						'args'			=> array( 'sc' => $this )
					),

				array(
						'type' 	=> 'tab_container_close',
						'nodescription' => true
					)

				);

		}

		/**
		 * Create and register templates for easier maintainance
		 *
		 * @since 4.6.4
		 */
		protected function register_dynamic_templates()
		{
			$this->register_modal_group_templates();

			/**
			 * Content Tab
			 * ===========
			 */

			$c = array(
						array(
							'name' 	=> __( 'Which type of slider is this?', 'avia_framework' ),
							'desc' 	=> __( 'Slides can either be generated based on images you choose or on recent post entries', 'avia_framework' ),
							'id' 	=> 'slide_type',
							'type' 	=> 'select',
							'std' 	=> 'image-based',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Image based Slider', 'avia_framework' )	=> 'image-based',
												__( 'Entry based Slider', 'avia_framework' )	=> 'entry-based',
											)
						),

						array(
							'name'		=> __( 'Which Entries?', 'avia_framework' ),
							'desc'		=> __( 'Select which entries should be displayed by selecting a taxonomy', 'avia_framework' ),
							'id'		=> 'link',
							'type'		=> 'linkpicker',
							'fetchTMPL'	=> true,
							'multiple'	=> 6,
							'std'		=> 'category',
							'lockable'	=> true,
							'required'	=> array( 'slide_type', 'is_empty_or', 'entry-based' ),
							'subtype'	=> array( __( 'Display Entries from:', 'avia_framework' ) => 'taxonomy' )
					),

					array(
							'type'			=> 'modal_group',
							'id'			=> 'content',
							'modal_title'	=> __( 'Edit Form Element', 'avia_framework' ),
							'add_label'		=> __( 'Add single image', 'avia_framework' ),
							'container_class'	=> 'avia-element-fullwidth avia-multi-img',
							'std'			=> array(),
							'editable_item'	=> true,
							'lockable'		=> true,
							'tmpl_set_default'	=> false,
							'required'		=> array( 'slide_type', 'equals', 'image-based' ),
							'creator'		=> array(
													'name'		=> __( 'Add Images', 'avia_framework' ),
													'desc'		=> __( 'Here you can add new Images to the slideshow.', 'avia_framework' ),
													'id'		=> 'id',
													'type'		=> 'multi_image',
													'title'		=> __( 'Add multiple Images', 'avia_framework' ),
													'button'	=> __( 'Insert Images', 'avia_framework' ),
													'std'		=> ''
												),
							'subelements'	=> $this->create_modal()
					)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Select Entries', 'avia_framework' ),
								'content'		=> $c
							),
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_entries' ), $template );

			$c = array(
						array(
							'type'			=> 'template',
							'template_id' 	=> 'wc_options_non_products',
							'lockable'		=> true,
						),

						array(
							'type'					=> 'template',
							'template_id'			=> 'date_query',
							'lockable'				=> true,
							'period'				=> true,
							'template_required'		=> array(
															0	=> array( 'slide_type', 'is_empty_or', 'entry-based' )
														)
						),

						array(
							'name' 	=> __( 'Number of entries', 'avia_framework' ),
							'desc' 	=> __( 'How many entries should be displayed?', 'avia_framework' ),
							'id' 	=> 'items',
							'type' 	=> 'select',
							'std' 	=> '5',
							'lockable'	=> true,
							'required'	=> array( 'slide_type', 'is_empty_or', 'entry-based' ),
							'subtype'	=> AviaHtmlHelper::number_array( 1, 12, 1 )
						),

						array(
							'name' 	=> __( 'Offset Number', 'avia_framework' ),
							'desc' 	=> __( 'The offset determines where the query begins pulling entries. Useful if you want to remove a certain number of entries because you already query them with another element.', 'avia_framework' ),
							'id' 	=> 'offset',
							'type' 	=> 'select',
							'std' 	=> '0',
							'lockable'	=> true,
							'required'	=> array('slide_type','is_empty_or','entry-based'),
							'subtype'	=> AviaHtmlHelper::number_array( 1, 100, 1, array( __( 'Deactivate offset', 'avia_framework' ) => '0', __( 'Do not allow duplicate posts on the entire page (set offset automatically)', 'avia_framework' ) => 'no_duplicates' ) )
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Filter', 'avia_framework' ),
								'content'		=> $c
							),
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_filter' ), $template );

			$c = array(
						array(
							'name' 	=> __( 'Slide Title', 'avia_framework' ),
							'desc' 	=> __( 'Display the entry title by default?', 'avia_framework' ),
							'id' 	=> 'title',
							'type' 	=> 'select',
							'std' 	=> 'active',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Yes - display everywhere', 'avia_framework' )							=> 'active',
												__( 'Yes - display, but remove title on mobile devices', 'avia_framework' )	=> 'no-mobile',
												__( 'Display only on active slides', 'avia_framework' )						=> 'on-hover',
												__( 'No, never display title', 'avia_framework' )							=> 'inactive'
											)
						),

						array(
							'name' 	=> __( 'Display Excerpt?', 'avia_framework' ),
							'desc' 	=> __( 'Check if excerpt/caption of the slide should also be displayed', 'avia_framework' ),
							'id' 	=> 'excerpt',
							'type' 	=> 'checkbox',
							'std' 	=> '',
							'lockable'	=> true,
							'required'	=> array( 'title', 'not', 'inactive' )
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Caption', 'avia_framework' ),
								'content'		=> $c
							),
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_caption' ), $template );

			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name'		=> __( 'Accordion Image Size', 'avia_framework' ),
							'desc'		=> __( 'Choose image and Video size for your slideshow.', 'avia_framework' ),
							'id'		=> 'size',
							'type'		=> 'select',
							'std'		=> 'featured',
							'lockable'	=> true,
							'subtype'	=>  AviaHelper::get_registered_image_sizes( 500, false, true )
						),

						array(
							'name'		=> __( 'Alignment', 'avia_framework' ),
							'desc'		=> __( 'Change the alignment of title and excerpt here', 'avia_framework' ),
							'id'		=> 'accordion_align',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Default', 'avia_framework' )	=> '',
												__( 'Centered', 'avia_framework' )	=> 'av-accordion-text-center',
											)
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'General', 'avia_framework' ),
								'content'		=> $c
							),
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_general' ), $template );

			$c = array(
						array(
							'name'			=> __( 'Caption Font Sizes', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the caption titles.', 'avia_framework' ),
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'lockable'		=> true,
							'textfield'		=> true,
							'required'		=> array( 'title', 'not', 'inactive' ),
							'subtype'		=> array(
												'default'	=> AviaHtmlHelper::number_array( 10, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'desktop'	=> AviaHtmlHelper::number_array( 10, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'medium'	=> AviaHtmlHelper::number_array( 10, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 10, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 10, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
											),
							'id_sizes'		=> array(
												'default'	=> 'custom_title_size',
												'desktop'	=> 'av-desktop-font-size-title',
												'medium'	=> 'av-medium-font-size-title',
												'small'		=> 'av-small-font-size-title',
												'mini'		=> 'av-mini-font-size-title'
											)
						),

						array(
							'name'			=> __( 'Excerpt Font Sizes', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the excerpt.', 'avia_framework' ),
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'lockable'		=> true,
							'textfield'		=> true,
							'required'		=> array( 'excerpt', 'not', '' ),
							'subtype'		=> array(
												'default'	=> AviaHtmlHelper::number_array( 10, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'desktop'	=> AviaHtmlHelper::number_array( 10, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'medium'	=> AviaHtmlHelper::number_array( 10, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 10, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 10, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
											),
							'id_sizes'		=> array(
												'default'	=> 'custom_excerpt_size',
												'desktop'	=> 'av-desktop-font-size',
												'medium'	=> 'av-medium-font-size',
												'small'		=> 'av-small-font-size',
												'mini'		=> 'av-mini-font-size'
											)
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Fonts', 'avia_framework' ),
								'content'		=> $c
							),
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_fonts' ), $template );

			$c = array(
						array(
							'name'		=> __( 'Title Color', 'avia_framework' ),
							'desc'		=> __( 'Select a color for the title here. Leave blank for theme default.', 'avia_framework' ),
							'id'		=> 'title_color',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Excerpt Color', 'avia_framework' ),
							'desc'		=> __( 'Select a color for the excerpt here. Leave blank for theme default.', 'avia_framework' ),
							'id'		=> 'excerpt_color',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true,
							'required'	=> array( 'excerpt', 'not', '' )
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Colors', 'avia_framework' ),
								'content'		=> $c
							),
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_colors' ), $template );

			/**
			 * Advanced Tab
			 * ===========
			 */

			$c = array(

						array(
							'type'				=> 'template',
							'template_id'		=> 'slideshow_rotation',
							'stop_id'			=> 'autoplay_stopper',
							'manual_stop_id'	=> false,
							'lockable'			=> true
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Slider Animation', 'avia_framework' ),
								'content'		=> $c
							),
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_animation_slider' ), $template );

			$c = array(
						array(
							'type'				=> 'template',
							'template_id'		=> 'heading_tag',
							'theme_default'		=> 'h3',
							'context'			=> __CLASS__,
							'lockable'			=> true,
							'required'			=> array( 'slide_type', 'is_empty_or', 'entry-based' )
						),


				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Heading Tag', 'avia_framework' ),
								'content'		=> $c
							),
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_heading' ), $template );

		}

		/**
		 * Creates the modal popup for a single entry
		 *
		 * @since 4.6.4
		 * @return array
		 */
		protected function create_modal()
		{
			$elements = array(

				array(
						'type' 	=> 'tab_container',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Content', 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'type'			=> 'template',
							'template_id'	=> 'toggle_container',
							'templates_include'	=> array(
													$this->popup_key( 'modal_content_image' ),
													$this->popup_key( 'modal_content_caption' ),
												),
							'nodescription' => true
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Advanced', 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'type'			=> 'template',
							'template_id'	=> 'toggle_container',
							'templates_include'	=> array(
													$this->popup_key( 'modal_advanced_heading' ),
													$this->popup_key( 'modal_advanced_link' )
												),
							'nodescription' => true
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type'			=> 'template',
						'template_id'	=> 'element_template_selection_tab',
						'args'			=> array(
												'sc'			=> $this,
												'modal_group'	=> true
											)
					),

				array(
						'type' 	=> 'tab_container_close',
						'nodescription' => true
					)

				);

			return $elements;
		}

		/**
		 * Register all templates for the modal group popup
		 *
		 * @since 4.6.4
		 */
		protected function register_modal_group_templates()
		{
			/**
			 * Content Tab
			 * ===========
			 */
			$c = array(
						array(
							'name'		=> __( 'Choose another Image', 'avia_framework' ),
							'desc'		=> __( 'Either upload a new, or choose an existing image from your media library', 'avia_framework' ),
							'id'		=> 'id',
							'type'		=> 'image',
							'fetch'		=> 'id',
							'title'		=> __( 'Change Image', 'avia_framework' ),
							'button'	=> __( 'Change Image', 'avia_framework' ),
							'std'		=> '',
							'lockable'	=> true
						),


					);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Image', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_content_image' ), $template );


			$c = array(
						array(
							'name' 	=> __( 'Caption Title', 'avia_framework' ),
							'desc' 	=> __( 'Enter a caption title for the slide here', 'avia_framework' ),
							'id' 	=> 'title',
							'type' 	=> 'input',
							'std' 	=> '',
							'lockable'	=> true
						),

						array(
							'name' 	=> __( 'Caption Text', 'avia_framework' ),
							'desc' 	=> __( 'Enter some additional caption text', 'avia_framework' ),
							'id' 	=> 'content',
							'type' 	=> 'textarea',
							'std' 	=> '',
							'lockable'	=> true
						),

					);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Image Caption', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_content_caption' ), $template );

			/**
			 * Advanced Tab
			 * ===========
			 */

			$c = array(
						array(
							'type'				=> 'template',
							'template_id'		=> 'heading_tag',
							'theme_default'		=> 'h3',
							'context'			=> __CLASS__,
							'lockable'			=> true
						),

					);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Heading Tag', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_advanced_heading' ), $template );

			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'linkpicker_toggle',
							'name'			=> __( 'Image Link', 'avia_framework' ),
							'desc'			=> __( 'Where should the Image link to?', 'avia_framework' ),
							'target_id'		=> 'link_target',
							'lockable'		=> true,
							'subtypes'		=> array( 'lightbox', 'manually', 'single', 'taxonomy' ),
						)

					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_advanced_link' ), $c );

		}

		/**
		 * Editor Element - this function defines the visual appearance of an element on the AviaBuilder Canvas
		 * Most common usage is to define some markup in the $params['innerHtml'] which is then inserted into the drag and drop container
		 * Less often used: $params['data'] to add data attributes, $params['class'] to modify the className
		 *
		 * @param array $params			holds the default values for $content and $args.
		 * @return array				usually holds an innerHtml key that holds item specific markup.
		 */
		public function editor_element( $params )
		{
			$params = parent::editor_element( $params );
			$params['innerHtml'] .=	AviaPopupTemplates()->get_html_template( 'alb_element_fullwidth_stretch' );

			return $params;
		}

		/**
		 * Editor Sub Element - this function defines the visual appearance of an element that is displayed within a modal window and on click opens its own modal window
		 * Works in the same way as Editor Element
		 *
		 * @param array $params this array holds the default values for $content and $args.
		 * @return $params the return array usually holds an innerHtml key that holds item specific markup.
		 */
		public function editor_sub_element( $params )
		{
			/**
			 * Fix a bug in 4.7 and 4.7.1 renaming option id (no longer backwards comp.) - can be removed in a future version again
			 */
			if( isset( $params['args']['linktarget'] ) )
			{
				$params['args']['link_target'] = $params['args']['linktarget'];
			}

			$default = array();
			$locked = array();
			$attr = $params['args'];
			$content = $params['content'];
			Avia_Element_Templates()->set_locked_attributes( $attr, $this, $this->config['shortcode_nested'][0], $default, $locked, $content );

			$img_template = $this->update_option_lockable( array( 'id', 'img_fakeArg' ), $locked );
			$title_templ = $this->update_option_lockable( 'title', $locked );
			$content_tmpl = $this->update_option_lockable( 'content', $locked );

			$thumbnail = isset( $attr['id'] ) ? wp_get_attachment_image( $attr['id'] ) : '';

			$params['innerHtml']  = '';
			$params['innerHtml'] .=	'<div class="avia_title_container" data-update_element_template="yes">';
			$params['innerHtml'] .=		"<span class='avia_slideshow_image' {$img_template} >{$thumbnail}</span>";
			$params['innerHtml'] .=		'<div class="avia_slideshow_content">';
			$params['innerHtml'] .=			"<h4 class='avia_title_container_inner' {$title_templ} >{$attr['title']}</h4>";
			$params['innerHtml'] .=			"<p class='avia_content_container' {$content_tmpl}>" . stripslashes( $content ) . '</p>';
			$params['innerHtml'] .=		'</div>';
			$params['innerHtml'] .= '</div>';

			return $params;
		}

		/**
		 *
		 * @since 4.8.9
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles( array $args )
		{
			$result = parent::get_element_styles( $args );

			//	@since 4.9 - bugfix for cloned element where av_uid was not changed
			if( current_theme_supports( 'avia_post_css_slideshow_fix' ) )
			{
				$result['element_id'] = 'av-' . md5( $result['element_id'] . $result['content'] );
			}

			extract( $result );

			$default = array(
						'slide_type'			=> 'image-based',
						'link'					=> '',
						'wc_prod_visible'		=> '',
						'wc_prod_hidden'		=> '',
						'wc_prod_featured'		=> '',
						'prod_order_by'			=> '',
						'prod_order'			=> '',
						'date_filter'			=> '',
						'date_filter_start'		=> '',
						'date_filter_end'		=> '',
						'date_filter_format'	=> 'yy/mm/dd',		//	'yy/mm/dd' | 'dd-mm-yy'	| yyyymmdd
						'period_filter_unit_1'	=> '',
						'period_filter_unit_2'	=> '',
						'size'					=> '',
						'items'					=> '',
						'autoplay'				=> 'false',
						'title'					=> 'active',
						'excerpt'				=> '',
						'interval'				=> 5,
						'offset'				=> 0,
						'custom_title_size'		=> '',
						'custom_excerpt_size'	=> '',
						'accordion_align'		=> '',
						'lazy_loading'			=> 'disabled',
						'img_scrset'			=> ''
				);

			// Backwards comp. - make sure to provide "old" defaults for options not set and override with default options provided
			$default = avia_accordion_slider::default_args( $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' ) );


			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );
			$meta = aviaShortcodeTemplate::set_frontend_developer_heading_tag( $atts, $meta );

			$add = array(
						'handle'		=> $shortcodename,
						'content'		=> ShortcodeHelper::shortcode2array( $content, 1 ),
						'class'			=> '',
						'custom_markup'	=> '',
						'el_id'			=> '',
						'heading_tag'	=> '',
						'heading_class'	=> ''
				);

			$defaults = array_merge( $default, $add );

			$atts = shortcode_atts( $defaults, $atts, $this->config['shortcode'] );

			foreach( $atts['content'] as &$item )
			{
				$item_def = $this->get_default_modal_group_args();
				Avia_Element_Templates()->set_locked_attributes( $item['attr'], $this, $this->config['shortcode_nested'][0], $item_def, $locked, $item['content'] );
			}

			unset( $item );


			if( ! isset( $this->obj_accordion_slider[ $element_id ] ) )
			{
				$this->obj_accordion_slider[ $element_id ] = new avia_accordion_slider( $atts, $this );
			}

			$accordion_slider = $this->obj_accordion_slider[ $element_id ];

			$update = array(
							'class'				=> ! empty( $meta['custom_class'] ) ? $meta['custom_class'] : '',
							'custom_markup'		=> ! empty( $meta['custom_markup'] ) ? $meta['custom_markup'] : '',
							'el_id'				=> ! empty( $meta['custom_el_id'] ) ? $meta['custom_el_id'] : '',
							'heading_tag'		=> ! empty( $meta['heading_tag'] ) ? $meta['heading_tag'] : '',
							'heading_class'		=> ! empty( $meta['heading_class'] ) ? $meta['heading_class'] : '',
						);

			$atts = $accordion_slider->update_config( $update );


			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['element_styling'] = $element_styling;
			$result['meta'] = $meta;

			$result = $accordion_slider->get_element_styles( $result );

			return $result;
		}

		/**
		 * Frontend Shortcode Handler
		 *
		 * @param array $atts array of attributes
		 * @param string $content text within enclosing form of shortcode element
		 * @param string $shortcodename the shortcode found, when == callback name
		 * @return string $output returns the modified html string
		 */
		public function shortcode_handler( $atts, $content = '', $shortcodename = '', $meta = '' )
		{
			$result = $this->get_element_styles( compact( array( 'atts', 'content', 'shortcodename', 'meta' ) ) );

			extract( $result );
			extract( $atts );

			if( 'disabled' == $atts['img_scrset'] )
			{
				Av_Responsive_Images()->force_disable( 'disabled' );
			}

			$skipSecond = false;
			avia_sc_slider_accordion::$slide_count++;
			$av_display_classes = $element_styling->responsive_classes_string( 'hide_element', $atts );

			$params['class'] = "avia-accordion-slider-wrap main_color avia-shadow {$av_display_classes} {$meta['el_class']}";
			$params['open_structure'] = false;
			$params['custom_markup'] = $meta['custom_markup'];

			//we dont need a closing structure if the element is the first one or if a previous fullwidth element was displayed before
			if( $meta['index'] == 0 )
			{
				$params['close'] = false;
			}

			if( ! empty( $meta['siblings']['prev']['tag'] ) && in_array( $meta['siblings']['prev']['tag'], AviaBuilder::$full_el_no_section ) )
			{
				$params['close'] = false;
			}

			if( $meta['index'] > 0 )
			{
				$params['class'] .= ' slider-not-first';
			}

			$params['id'] = AviaHelper::save_string( $meta['custom_id_val'], '-', 'accordion_slider_' . avia_sc_slider_accordion::$slide_count );

			if( '' == $atts['el_id'] )
			{
				$atts['el_id'] = ' id="accordion_slider_' . avia_sc_slider_accordion::$slide_count . '" ';
			}

			if( ShortcodeHelper::is_top_level() )
			{
				$atts['el_id'] = '';
				$atts['class'] = '';
			}

			$accordion_slider = $this->obj_accordion_slider[ $element_id ];
			$accordion_slider->update_config( $atts );
			$slide_html = $accordion_slider->html();

			Av_Responsive_Images()->force_disable( 'reset' );

			//if the element is nested within a section or a column dont create the section shortcode around it
			if( ! ShortcodeHelper::is_top_level() )
			{
				return $slide_html;
			}

			$output  = '';
			$output .=  avia_new_section( $params );
			$output .=		$slide_html;
			$output .= '</div>'; //close section


			//if the next tag is a section dont create a new section from this shortcode
			if( ! empty( $meta['siblings']['next']['tag'] ) && in_array( $meta['siblings']['next']['tag'],  AviaBuilder::$full_el ) )
			{
				$skipSecond = true;
			}

			//if there is no next element dont create a new section.
			if( empty( $meta['siblings']['next']['tag'] ) )
			{
				$skipSecond = true;
			}

			if( empty( $skipSecond ) )
			{
				$output .= avia_new_section( array( 'close' => false, 'id' => 'after_accordion_slider_' . avia_sc_slider_accordion::$slide_count ) );
			}

			return $output;
		}
	}
}


if( ! class_exists( 'avia_accordion_slider', false ) )
{
	class avia_accordion_slider extends \aviaBuilder\base\aviaSubItemQueryBase
	{
		/**
		 * slider count for the current page
		 *
		 * @since < 4.0
		 * @var int
		 */
//		static protected $slider = 0;

		/**
		 * entries or image slides
		 *
		 * @since < 4.0
		 * @var array
		 */
		protected $slides;

		/**
		 * number of slides
		 *
		 * @since < 4.0
		 * @var int
		 */
		protected $slide_count;

		/**
		 * Width of a single item
		 *
		 * @since 4.8.9
		 * @var int
		 */
		protected $item_width;

		/**
		 *
		 * @since < 4.0
		 * @var array
		 */
		protected $id_array;

		/**
		 *
		 * @since 4.7.6.4
		 * @var int
		 */
		protected $current_page;

		/**
		 *
		 * @since 4.8.9						added $sc_context
		 * @param array $atts
		 * @param aviaShortcodeTemplate $sc_context
		 */
		public function __construct( array $atts, aviaShortcodeTemplate $sc_context = null )
		{
			parent::__construct( $atts, $sc_context, avia_accordion_slider::default_args() );

			$this->slide_count = 0;
			$this->slides = array();
			$this->item_width = 100;
			$this->id_array = array();
			$this->current_page = 1;


			/**
			 * @since ????
			 * param array $this->config
			 * @return array
			 */
			$this->config = apply_filters( 'avf_aviaccordion_config', $this->config );

			$this->get_height();
			$this->get_slides();
		}

		/**
		 * @since 4.5
		 */
		public function __destruct()
		{
			unset( $this->slides );
			unset( $this->id_array );

			parent::__destruct();
		}

		/**
		 * Returns the defaults array for this class
		 *
		 * @since 4.8.9
		 * @param array $args
		 * @return array
		 */
		static public function default_args( array $args = array() )
		{
			$default = array(
							'slide_type'			=> 'image-based',
							'link'					=> '',
							'wc_prod_visible'		=>	'',
							'wc_prod_hidden'		=> '',
							'wc_prod_featured'		=> '',
							'prod_order_by'			=>	'',
							'prod_order'			=>	'',
							'size'					=> '',
							'items'					=> '',
							'autoplay'				=> 'false',
							'interval'				=> 5,
							'autoplay_stopper'		=> '',
							'offset'				=> 0,
							'title'					=> 'active',
							'excerpt'				=> '',
							'content'				=> array(),
							'custom_title_size'		=> '',
							'custom_excerpt_size'	=> '',
							'title_color'			=> '',
							'excerpt_color'			=> '',
							'custom_markup'			=> '',
							'accordion_align'		=> '',
							'el_id'					=> '',
							'class'					=> '',
							'lazy_loading'			=> 'disabled',
							'paginate'				=> 'no',
							'max-height'			=> '',			//	calculated value
							'default-height'		=> ''			//	calculated value
						);

			$default = array_merge( $default, $args );

			/**
			 * @since 4.8.9
			 * @param array $default
			 * @return array
			 */
			return apply_filters( 'avf_accordion_slider_defaults', $default );
		}

		/**
		 * Create custom stylings
		 *
		 * Attention: Due to paging we cannot add any backgrouund images to selectors !!!!
		 * =========
		 *
		 * @since 4.8.9
		 * @param array $result
		 * @return array
		 */
		public function get_element_styles( array $result )
		{
			extract( $result );

			//	backwards comp. - prepare responsive font sizes for media query
			$atts['size-title'] = $atts['custom_title_size'];
			$atts['size-excerpt'] = $atts['custom_excerpt_size'];
			$atts['av-medium-font-size-excerpt'] = $atts['av-medium-font-size'];
			$atts['av-small-font-size-excerpt'] = $atts['av-small-font-size'];
			$atts['av-mini-font-size-excerpt'] = $atts['av-mini-font-size'];

			if( count( $this->slides ) == 0 )
			{
				return $result;
			}

			$this->item_width = 100 / count( $this->slides );

			$classes = array(
							'aviaccordion',
							'avia-accordion',
							$element_id,
							'aviaccordion-title-' . $this->config['title']
						);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes( 'container', $this->config['class'] );

			$element_styling->add_responsive_classes( 'container', 'hide_element', $this->config );
			$element_styling->add_responsive_font_sizes( 'title', 'size-title', $atts, $this->sc_context );
			$element_styling->add_responsive_font_sizes( 'excerpt', 'size-excerpt', $atts, $this->sc_context );


			if( 'true' == $this->config['autoplay'] )
			{
				$element_styling->add_classes( 'container', 'av-slideshow-autoplay' );
				$val_autoplay = true;

				if( ! empty( $this->config['autoplay_stopper'] ) )
				{
					$element_styling->add_classes( 'container', 'av-loop-once' );
					$val_loop = 'once';
				}
				else
				{
					$element_styling->add_classes( 'container', 'av-loop-endless' );
					$val_loop = 'endless';
				}
			}
			else
			{
				$element_styling->add_classes( 'container', array( 'av-slideshow-manual', 'av-loop-once' ) );
				$val_autoplay = false;
				$val_loop = 'once';
			}

			if( '' == $this->config['interval'] )
			{
				$this->config['interval'] = 5;
			}

			$slideshow_options = array(
									'interval'			=> floatval( $this->config['interval'] ),
									'autoplay'			=> $val_autoplay,
									'loop_autoplay'		=> $val_loop
				);

			$element_styling->add_data_attributes( 'container', $slideshow_options );




			if( $this->config['max-height'] != '')
			{
				$element_styling->add_styles( 'container', array( 'max-height' => $this->config['max-height'] . 'px' ) );
			}

			$element_styling->add_styles( 'preview', array( 'width' => ceil( $this->item_width ) + 0.1 . '%' ) );

			$element_styling->add_styles( 'title', array( 'color' => $this->config['title_color'] ) );
			if( ! empty( $this->config['excerpt'] ) )
			{
				$element_styling->add_styles( 'excerpt', array( 'color' => $this->config['excerpt_color'] ) );
			}

			$selectors = array(
						'container'			=> ".avia-accordion.{$element_id}",
						'preview'			=> ".avia-accordion.{$element_id} .aviaccordion-preview",
						'title'				=> "#top .avia-accordion.{$element_id} .aviaccordion-title",
						'excerpt'			=> "#top .avia-accordion.{$element_id} .aviaccordion-excerpt"
					);

			$element_styling->add_selectors( $selectors );

			foreach( $this->slides as $key => $slide )
			{
				$result_item = array();
				$result_item['key'] = $key;
				$result_item['content'] = $slide;
				$result_item['element_id'] = $element_id . '__' . $key;
				$result_item['element_styling'] = new aviaElementStyling( $this->sc_context, $result_item['element_id'] );

				$result_item = $this->get_element_styles_item( $result_item );

				$element_styling->add_subitem_styling( $result_item['element_id'], $result_item['element_styling'] );
			}

			//	save data for later HTML output
			$this->element_id = $element_id;
			$this->element_styles = $element_styling;

			$result['element_styling'] = $element_styling;

			return $result;
		}

		/**
		 * Create custom item stylings
		 * Items are called in object of main shortcode attributes
		 *
		 * @since 4.8.9
		 * @param array $result
		 * @return array
		 */
		public function get_element_styles_item( array $result )
		{
			$result = $this->sc_context->get_element_styles_query_item( $result );

			extract( $result );

			$classes = array(
							'aviaccordion-slide',
							$element_id,
							'aviaccordion-slide-' . ( $key + 1 )
						);

			$element_styling->add_classes( 'container', $classes );

			$element_styling->add_styles( 'container', array( 'left' => ( $this->item_width * $key ) . '%' ) );


			$selectors = array(
						'container'			=> ".avia-accordion .aviaccordion-slide.{$element_id}",
						'preview'			=> ".avia-accordion .aviaccordion-slide.{$element_id} .aviaccordion-preview"

					);

			$element_styling->add_selectors( $selectors );

			$result['element_styling'] = $element_styling;

			return $result;
		}

		/**
		 * Set slider height in $config
		 *
		 * @since < 4.0
		 */
		protected function get_height()
		{
			//check how large the slider is and change the classname accordingly
			global $_wp_additional_image_sizes;

			if( isset( $_wp_additional_image_sizes[ $this->config['size'] ]['width'] ) )
			{
				$width  = $_wp_additional_image_sizes[ $this->config['size'] ]['width'];
				$height = $_wp_additional_image_sizes[ $this->config['size'] ]['height'];

				/**
				 * Avoid notices in case user manipulated image settings to 0
				 */
				$height = is_numeric( $height ) && $height > 0 ? $height : get_option( 'medium_size_h', 300 );
				$width = is_numeric( $width ) && $width > 0 ? $width : get_option( 'medium_size_w', $height );
			}
			else if( get_option( $this->config['size'] . '_size_w' ) )
			{
				$width = get_option( $this->config['size'] . '_size_w' );
				$height = get_option( $this->config['size'] . '_size_h' );
			}

			$this->config['max-height'] = $height;
			$this->config['default-height'] = ( 100 / $width ) * $height;
		}

		/**
		 * Get the slides depending on setting
		 *
		 * @since < 4.0
		 */
		protected function get_slides()
		{
			if( $this->config['slide_type'] == 'image-based' )
			{
				$this->get_image_based_slides();
			}
			else
			{
				$this->extract_terms();
				$this->query_entries();

				$dev_tags = aviaShortcodeTemplate::set_frontend_developer_heading_tag( $this->config );

				foreach( $this->slides as $key => $slide )
				{
					$thumb_id = get_post_thumbnail_id( $slide->ID );
					if( $this->config['lazy_loading'] != 'enabled' )
					{
						Av_Responsive_Images()->add_attachment_id_to_not_lazy_loading( $thumb_id );
					}

					$this->slides[ $key ]->av_attachment = wp_get_attachment_image( $thumb_id, $this->config['size'], false, array( 'class' => 'aviaccordion-image' ) );
					$this->slides[ $key ]->av_permalink = get_post_meta( $slide->ID, '_portfolio_custom_link', true ) != '' ? get_post_meta( $slide->ID, '_portfolio_custom_link_url', true ) : get_permalink( $slide->ID );
					$this->slides[ $key ]->av_target = '';
					$this->slides[ $key ]->post_excerpt = ! empty( $slide->post_excerpt ) ? $slide->post_excerpt : avia_backend_truncate( $slide->post_content, apply_filters( 'avf_aviaccordion_excerpt_length', 120 ), apply_filters( 'avf_aviaccordion_excerpt_delimiter', ' ' ), '…', true, '' );
					$this->slides[ $key ]->heading_tag = $dev_tags['heading_tag'];
					$this->slides[ $key ]->heading_class = $dev_tags['heading_class'];
				}
			}
		}

		/**
		 * @since < 4.0
		 */
		protected function get_image_based_slides()
		{
			foreach( $this->config['content'] as $key => $slide )
			{
				if( ! isset( $slide['attr']['link'] ) || empty( $slide['attr']['link'] ) )
				{
					$slide['attr']['link'] = 'lightbox';
				}

				/**
				 * Fix a bug in 4.7 and 4.7.1 renaming option id (no longer backwards comp.) - can be removed in a future version again
				 */
				if( isset( $slide['attr']['linktarget'] ) )
				{
					$slide['attr']['link_target'] = $slide['attr']['linktarget'];
				}

				$dev_tags = aviaShortcodeTemplate::set_frontend_developer_heading_tag( $slide['attr'] );

				if( $this->config['lazy_loading'] != 'enabled' && $slide['attr']['id'] )
				{
					Av_Responsive_Images()->add_attachment_id_to_not_lazy_loading( $slide['attr']['id'] );
				}

				$this->slides[ $key ] = new stdClass();
				$this->slides[ $key ]->post_title = isset( $slide['attr']['title'] ) ? $slide['attr']['title'] : '';
				$this->slides[ $key ]->post_excerpt = $slide['content'];
				$this->slides[ $key ]->av_attachment = wp_get_attachment_image( $slide['attr']['id'], $this->config['size'], false, array( 'class' => 'aviaccordion-image' ) );
				$this->slides[ $key ]->av_permalink = isset( $slide['attr']['link'] ) ? AviaHelper::get_url( $slide['attr']['link'], $slide['attr']['id'], true ) : '';
				$this->slides[ $key ]->av_target = empty( $slide['attr']['link_target'] ) ? '' : "target='{$slide['attr']['link_target']}'" ;
				$this->slides[ $key ]->heading_tag = $dev_tags['heading_tag'];
				$this->slides[ $key ]->heading_class = $dev_tags['heading_class'];
				$this->slides[ $key ]->av_attachment_meta = '';

				if( 'lightbox' == $slide['attr']['link'] )
				{
					$title = get_the_title( $slide['attr']['id'] );
					if( ! empty( $title ) )
					{
						$this->slides[ $key ]->av_attachment_meta .= ' title="' . esc_attr( $title ) . '"';
					}

					$alt = get_post_meta( $slide['attr']['id'], '_wp_attachment_image_alt', true );
					if( ! empty( $alt ) )
					{
						$this->slides[ $key ]->av_attachment_meta .= ' alt="' . esc_attr( $alt ) . '"';
					}
				}
			}
		}

		/**
		 * @since < 4.0
		 */
		protected function extract_terms()
		{
			if( isset( $this->config['link'] ) )
			{
				$this->config['link'] = explode( ',', $this->config['link'], 2 );
				$this->config['taxonomy'] = $this->config['link'][0];

				if( isset( $this->config['link'][1] ) )
				{
					$this->config['categories'] = $this->config['link'][1];
				}
				else
				{
					$this->config['categories'] = array();
				}
			}
		}

		/**
		 *
		 * @since < 4.0
		 * @param array $params
		 * @param boolean $return
		 * @return array[WP_Post]|void
		 */
		protected function query_entries( $params = array(), $return = false )
		{
			global $avia_config;

			if( empty( $params ) )
			{
				$params = $this->config;
			}

			if( empty( $params['custom_query'] ) )
            {
				$query = array();

				if( ! empty( $params['categories'] ) )
				{
					//get the portfolio categories
					$terms 	= explode( ',', $params['categories'] );
				}

				$this->current_page = ( $params['paginate'] != 'no' ) ? avia_get_current_pagination_number( 'avia-element-paging' ) : 1;

				//if we find no terms for the taxonomy fetch all taxonomy terms
				if( empty( $terms[0] ) || is_null( $terms[0] ) || $terms[0] === 'null' )
				{
					$term_args = array(
								'taxonomy'		=> $params['taxonomy'],
								'hide_empty'	=> true
							);
					/**
					 * To display private posts you need to set 'hide_empty' to false,
					 * otherwise a category with ONLY private posts will not be returned !!
					 *
					 * You also need to add post_status 'private' to the query params with filter avf_accordion_entries_query.
					 *
					 * @since 4.4.2
					 * @added_by Günter
					 * @param array $term_args
					 * @param array $params
					 * @return array
					 */
					$term_args = apply_filters( 'avf_av_slideshow_accordion_term_args', $term_args, $params );

					$allTax = AviaHelper::get_terms( $term_args );

					$terms = array();
					foreach( $allTax as $tax )
					{
						$terms[] = $tax->term_id;
					}
				}

				if( $params['offset'] == 'no_duplicates' )
                {
                    $params['offset'] = 0;
                    if( empty( $params['ignore_duplicate_rule'] ) )
					{
						$no_duplicates = true;
					}
                }

				if( empty( $params['post_type'] ) )
				{
					$params['post_type'] = get_post_types();
				}

				if( is_string( $params['post_type'] ) )
				{
					$params['post_type'] = explode( ',', $params['post_type'] );
				}

				$orderby = 'date';
				$order = 'DESC';

				$date_query = AviaHelper::date_query( array(), $params );

				// Meta query - replaced by Tax query in WC 3.0.0
				$meta_query = array();
				$tax_query = array();
				$ordering_args = array();

				// check if taxonomy are set to product or product attributes
				$tax = get_taxonomy( $params['taxonomy'] );

				if( class_exists( 'WooCommerce', false ) && is_object( $tax ) && isset( $tax->object_type ) && in_array( 'product', (array) $tax->object_type ) )
				{
					$avia_config['woocommerce']['disable_sorting_options'] = true;

					avia_wc_set_out_of_stock_query_params( $meta_query, $tax_query, $params['wc_prod_visible'] );
					avia_wc_set_hidden_prod_query_params( $meta_query, $tax_query, $params['wc_prod_hidden'] );
					avia_wc_set_featured_prod_query_params( $meta_query, $tax_query, $params['wc_prod_featured'] );

						//	sets filter hooks !!
					$ordering_args = avia_wc_get_product_query_order_args( $params['prod_order_by'], $params['prod_order'] );

					$orderby = $ordering_args['orderby'];
					$order = $ordering_args['order'];
				}

				if( ! empty( $terms ) )
				{
					$tax_query[] =  array(
										'taxonomy' 	=>	$params['taxonomy'],
										'field' 	=>	'id',
										'terms' 	=>	$terms,
										'operator' 	=>	'IN'
								);
				}


				$query = array(	'orderby'		=>	$orderby,
								'order'			=>	$order,
								'paged'			=>	$this->current_page,
								'post_type'		=>	$params['post_type'],
//								'post_status'	=>	'publish',
								'offset'		=>	$params['offset'],
								'posts_per_page' =>	$params['items'],
								'post__not_in'	=>	( ! empty( $no_duplicates ) ) ? $avia_config['posts_on_current_page'] : array(),
								'meta_query'	=>	$meta_query,
								'tax_query'		=>	$tax_query,
								'date_query'	=> $date_query,
							);

				if ( ! empty( $ordering_args['meta_key'] ) )
				{
					$query['meta_key'] = $ordering_args['meta_key'];
				}

			}
			else
			{
				$query = $params['custom_query'];
			}

			/**
			 *
			 * @since < 4.0
			 * @param array $query
			 * @param array $params
			 * @return array
			 */
			$query = apply_filters( 'avf_accordion_entries_query', $query, $params );

			$result = new WP_Query( $query );
			$entries = $result->posts;

			if( ( $result->post_count > 0 ) && empty( $params['ignore_duplicate_rule'] ) )
			{
				foreach( $entries as $entry )
	            {
					 $avia_config['posts_on_current_page'][] = $entry->ID;
	            }
			}

			if( function_exists( 'WC' ) )
			{
				avia_wc_clear_catalog_ordering_args_filters();
				$avia_config['woocommerce']['disable_sorting_options'] = false;
			}

			if( $return )
			{
				return $entries;
			}
			else
			{
				$this->slides = $entries;
			}
		}

		/**
		 *
		 * @since < 4.0
		 * @return string
		 */
		public function html()
		{
			$slideCount = count( $this->slides );
			if( $slideCount == 0 )
			{
				return '';
			}

			$markup_container = avia_markup_helper( array( 'context' => 'blog', 'echo' => false, 'custom_markup' => $this->config['custom_markup'] ) );


			$style_tag = $this->element_styles->get_style_tag( $this->element_id );
			$container_class = $this->element_styles->get_class_string( 'container' );
			$data_slideshow_options = $this->element_styles->get_data_attributes_json_string( 'container', 'slideshow-options' );

			$output = '';
			$output .= $style_tag;
			$output .= "<div {$this->config['el_id']} class='{$container_class} {$this->config['class']}' {$data_slideshow_options} {$markup_container}>";
			$output .= 		'<ul class="aviaccordion-inner">';

			foreach( $this->slides as $key => $slide )
			{
//				$dev_tags = aviaShortcodeTemplate::set_frontend_developer_heading_tag( $this->config );

				$left_pos = $this->item_width * $key;
				$data = "data-av-left='{$left_pos}'";

				$slide_id = isset( $slide->ID ) ? $slide->ID : '';
				$markup_entry = avia_markup_helper( array( 'context' => 'entry', 'echo' => false, 'id' => $slide_id, 'custom_markup' => $this->config['custom_markup'] ) );
				$lightbox_attr = Av_Responsive_Images()->html_attr_image_src( $slide->av_permalink, false );

				$av_attachment_meta = isset( $slide->av_attachment_meta ) ? $slide->av_attachment_meta : '';

				/**
				 * Remove or alter lightbox title and alt attribute
				 *
				 * @since 4.8.2
				 * @param string $av_attachment_meta
				 * @param string $context
				 * @param object $slide
				 * @param int $key
				 * @return string
				 */
				$av_attachment_meta = apply_filters( 'avf_image_metadata_lightbox_link', $av_attachment_meta, 'avia_sc_slider_accordion', $slide, $key );

				//	add item container data
				$item_info = $this->element_styles->get_subitem_styling_info( $key );
				$container_class = $item_info['element_styling']->get_class_string( 'container' );

				$output .= "<li class='{$container_class}' {$data} {$markup_entry}>";
				$output .=		"<a class='aviaccordion-slide-link noHover' {$lightbox_attr} {$av_attachment_meta} {$slide->av_target}>";
				$output .=			"<div class='aviaccordion-preview {$this->config['accordion_align']}'>";


				if( $this->config['title'] !== 'inactive' && ( ! empty( $slide->post_title ) || ! empty( $slide->post_excerpt) ) )
				{
					$markup_title = avia_markup_helper( array( 'context' => 'entry_title', 'echo' => false, 'id' => $slide_id, 'custom_markup' => $this->config['custom_markup'] ) );
					$markup_content = avia_markup_helper( array( 'context' => 'entry_content', 'echo' => false, 'id' => $slide_id, 'custom_markup' => $this->config['custom_markup'] ) );

					$default_heading = ! empty( $slide->heading_tag ) ? $slide->heading_tag : 'h3';
					$args = array(
								'heading'		=> $default_heading,
								'extra_class'	=> $slide->heading_class
							);

					$extra_args = array( $this, $key, $slide );

					/**
					 * @since 4.5.5
					 * @return array
					 */
					$args = apply_filters( 'avf_customize_heading_settings', $args, __CLASS__, $extra_args );

					$heading = ! empty( $args['heading'] ) ? $args['heading'] : $default_heading;
					$css = ! empty( $args['extra_class'] ) ? $args['extra_class'] : $slide->heading_class;

					$output .= '<div class="aviaccordion-preview-title-pos">';
					$output .=		'<div class="aviaccordion-preview-title-wrap">';
					$output .=			'<div class="aviaccordion-preview-title">';

					$output .= ! empty( $slide->post_title ) ? "<{$heading} class='aviaccordion-title {$css}' {$markup_title}>{$slide->post_title}</{$heading}>" : '';
					$output .= ! empty( $slide->post_excerpt ) && ! empty( $this->config['excerpt'] ) ? "<div class='aviaccordion-excerpt' {$markup_content}>" . wpautop( $slide->post_excerpt ) . "</div>" : '';

					$output .=			'</div>';
					$output .=		'</div>';
					$output .= '</div>';
				}
				$output .=				'</div>';
				$output .=			$slide->av_attachment;
				$output .=		'</a>';
				$output .= '</li>';
			}


			$output .= 		'</ul>';
			$output .= 		"<div class='aviaccordion-spacer' style='padding-bottom:{$this->config['default-height']}%'></div>";
			$output .= '</div>';

			return $output;
		}
	}

}

