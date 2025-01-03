<?php
/**
 * Easy Slider
 *
 * Shortcode that allows to display a simple slideshow
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_sc_slider', false ) )
{
	class avia_sc_slider extends aviaShortcodeTemplate
	{
		/**
		 * Save avia_slideshow objects for reuse. As we need to access the same object when creating the post css file in header,
		 * create the styles and HTML creation. Makes sure to get the same id.
		 *
		 *			$element_id	=> avia_slideshow
		 *
		 * @since 4.8.9
		 * @var array
		 */
		protected $obj_slideshow;

		/**
		 * @since 4.8.9
		 * @param AviaBuilder $builder
		 */
		public function __construct( AviaBuilder $builder )
		{
			parent::__construct( $builder );

			$this->obj_slideshow = array();
		}

		/**
		 * @since 4.8.9
		 */
		public function __destruct()
		{
			unset( $this->obj_slideshow );

			parent::__destruct();
		}

		/**
		 * Create the config array for the shortcode button
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['self_closing']	= 'no';
			$this->config['base_element']	= 'yes';

			$this->config['name']			= __( 'Easy Slider', 'avia_framework' );
			$this->config['tab']			= __( 'Media Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-slideshow.png';
			$this->config['order']			= 85;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode'] 		= 'av_slideshow';
			$this->config['shortcode_nested'] = array( 'av_slide' );
			$this->config['tooltip'] 	    = __( 'Display a simple slideshow element', 'avia_framework' );
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
			$this->config['alb_desc_id']	= 'alb_description';
			$this->config['name_item']		= __( 'Easy Slider Item', 'avia_framework' );
			$this->config['tooltip_item']	= __( 'An Easy Slider image or video item', 'avia_framework' );
		}

		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_js = avia_minify_extension( 'js' );
			$min_css = avia_minify_extension( 'css' );

			//load css
			wp_enqueue_style( 'avia-module-slideshow', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/slideshow/slideshow{$min_css}.css", array( 'avia-layout' ), $ver );

				//load js
			wp_enqueue_script( 'avia-module-slideshow', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/slideshow/slideshow{$min_js}.js", array( 'avia-shortcodes' ), $ver, true );
			wp_enqueue_script( 'avia-module-slideshow-video', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/slideshow/slideshow-video{$min_js}.js", array( 'avia-shortcodes' ), $ver, true );
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
													$this->popup_key( 'content_slideshow' ),
													$this->popup_key( 'content_copyright' )
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
													$this->popup_key( 'styling_slide' ),
													$this->popup_key( 'styling_navigation' ),
													$this->popup_key( 'styling_nav_colors' ),
													$this->popup_key( 'styling_copyright' )
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
								'template_id'	=> $this->popup_key( 'advanced_animation_slider' )
							),

						array(
								'type'			=> 'template',
								'template_id'	=> $this->popup_key( 'advanced_animation_image' )
							),

						array(
								'type'			=> 'template',
								'template_id'	=> $this->popup_key( 'advanced_privacy' )
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
							'type'			=> 'modal_group',
							'id'			=> 'content',
							'modal_title'	=> __( 'Edit Form Element', 'avia_framework' ),
							'add_label'		=> __( 'Add single image or video', 'avia_framework' ),
							'container_class'	=> 'avia-element-fullwidth avia-multi-img',
							'std'			=> array(),
							'editable_item'	=> true,
							'lockable'		=> true,
							'tmpl_set_default'	=> false,
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
								'title'			=> __( 'Select Images', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_slideshow' ), $template );


			$c = array(
						array(
								'type'			=> 'template',
								'template_id'	=> 'image_copyright_toggle',
								'lockable'		=> true
						)

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_copyright' ), $c );


			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name'		=> __( 'Slideshow Image Size', 'avia_framework' ),
							'desc'		=> __( 'Choose the size of the image that loads into the slideshow.', 'avia_framework' ),
							'id'		=> 'size',
							'type'		=> 'select',
							'std'		=> 'featured',
							'lockable'	=> true,
							'subtype'	=> AviaHelper::get_registered_image_sizes( array( 'thumbnail', 'logo', 'widget', 'slider_thumb' ) )
						),

						array(
							'name'		=> __( 'Use first slides caption as permanent caption', 'avia_framework' ),
							'desc'		=> __( 'If checked the caption will be placed on top of the slider. Please note that slideshow link settings and other captions will be ignored, and player controls will not be accessible when this option is enabled.', 'avia_framework' ) ,
							'id'		=> 'perma_caption',
							'type'		=> 'checkbox',
							'std'		=> '',
							'lockable'	=> true
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Slides', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_slide' ), $template );


			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'slideshow_controls',
							'lockable'		=> true
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Navigation Controls', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_navigation' ), $template );


			$c = array(

						array(
							'type'			=> 'template',
							'template_id'	=> 'slideshow_navigation_colors',
							'lockable'		=> true
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Navigation Control Colors', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_nav_colors' ), $template );


			$c = array(
						array(
								'type'			=> 'template',
								'template_id'	=> 'image_copyright_styling_toggle',
								'lockable'		=> true
						)

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_copyright' ), $c );


			/**
			 * Advanced Tab
			 * ============
			 */

			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'slideshow_transition',
							'lockable'		=> true
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'slideshow_rotation',
							'stop_id'		=> 'autoplay_stopper',
							'lockable'		=> true
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
							'type'			=> 'template',
							'template_id'	=> 'slideshow_image_scale',
							'lockable'		=> true
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Image Animation', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_animation_image' ), $template );


			$c = array(
						array(
							'name'		=> __( 'Lazy Load Videos', 'avia_framework' ),
							'desc'		=> __( 'Option to only load the preview image of a video slide. The actual videos will only be fetched once the user clicks on the image (Waiting for user interaction speeds up the inital pageload)', 'avia_framework' ),
							'id'		=> 'conditional_play',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
											__( 'Always load videos', 'avia_framework' )		=> '',
											__( 'Wait for user interaction or for a slide with active autoplay to load the video', 'avia_framework' )	=> 'confirm_all'
										),
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Privacy', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_privacy' ), $template );

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
													$this->popup_key( 'modal_content_slidecontent' ),
													$this->popup_key( 'modal_content_fallback' ),
													$this->popup_key( 'modal_content_caption' )
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
													$this->popup_key( 'modal_styling_format' ),
													$this->popup_key( 'modal_styling_player' ),
													$this->popup_key( 'modal_styling_fonts' ),
													$this->popup_key( 'modal_styling_colors' )
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
							'name'		=> __( 'Select Type Of Slide','avia_framework' ),
							'id'		=> 'slide_type',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Image Slide', 'avia_framework' )	=> 'image',
												__( 'Video Slide', 'avia_framework' )	=> 'video',
											)
						),

						array(
							'name'		=> __( 'Choose another Image', 'avia_framework' ),
							'desc'		=> __( 'Either upload a new, or choose an existing image from your media library', 'avia_framework' ),
							'id'		=> 'id',
							'type'		=> 'image',
							'fetch'		=> 'id',
							'title'		=> __( 'Change Image', 'avia_framework' ),
							'button'	=> __( 'Change Image', 'avia_framework' ),
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'slide_type', 'is_empty_or', 'image' ),

						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'video',
							'id'			=> 'video',
							'args'			=> array(
													'sc'	=> $this
												),
							'lockable'		=> true,
							'required'		=> array( 'slide_type', 'equals', 'video' ),
						),

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Select Slide Content', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_content_slidecontent' ), $template );


			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'slideshow_fallback_image',
							'lockable'		=> true
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Fallback images', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_content_fallback' ), $template );

			$c = array(
						array(
							'name'		=> __( 'Caption Title', 'avia_framework' ),
							'desc'		=> __( 'Enter a caption title for the slide here', 'avia_framework' ) ,
							'id'		=> 'title',
							'std'		=> '',
							'type'		=> 'input',
							'lockable'	=> true,
						),

						array(
							'name'		=> __( 'Caption Text', 'avia_framework' ),
							'desc'		=> __( 'Enter some additional caption text', 'avia_framework' ) ,
							'id'		=> 'content',
							'type'		=> 'textarea',
							'std'		=> '',
							'lockable'	=> true,
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Slide Caption', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_content_caption' ), $template );


			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name'		=> __( 'Video Aspect Ratio', 'avia_framework' ),
							'desc'		=> __( 'In order to calculate the correct height and width for the video slide you need to enter a aspect ratio (width:height). usually: 16:9 or 4:3.', 'avia_framework' ) . '<br/>' . __( 'If left empty 16:9 will be used', 'avia_framework' ) ,
							'id'		=> 'video_ratio',
							'std'		=> '16:9',
							'type'		=> 'input',
							'lockable'	=> true,
							'required'	=> array( 'slide_type', 'equals', 'video' ),
						),

/*
						array(
							'name' 	=> __( 'Video Size', 'avia_framework' ),
							'desc' 	=> __( 'By default the video will try to match the default slideshow size that was selected in the slider settings at &quot;Slideshow Image and Video Size&quot;', 'avia_framework' ),
							'id' 	=> 'video_format',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'required'	=> array( 'slide_type', 'equals', 'video' ),
							'subtype'	=> array(
												__( 'Try to match the default slideshow size (Video will not be cropped, but black borders will be visible at each side)', 'avia_framework' )				=> '',
												__( 'Try to match the default slideshow size but stretch the video to fill the whole slider (video will be cropped at top and bottom)', 'avia_framework' )	=> 'stretch',
												__( 'Show the full Video without cropping',  'avia_framework' ) =>'full',
											)
						),
*/


				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Video Format', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_styling_format' ), $template );

			$c = array(
						array(
								'type'			=> 'template',
								'template_id'	=> 'slideshow_player',
								'lockable'		=> true,
								'required'		=> array( 'slide_type', 'equals', 'video' )
							),

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Player Settings', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_styling_player' ), $template );


			$c = array(
						array(
							'name'			=> __( 'Caption Title Font Size', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the titles.', 'avia_framework' ),
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'lockable'		=> true,
							'textfield'		=> true,
							'subtype'		=> array(
												'default'	=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'desktop'	=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'medium'	=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
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
							'name'			=> __( 'Caption Content Font Size', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the titles.', 'avia_framework' ),
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'lockable'		=> true,
							'textfield'		=> true,
							'subtype'		=> array(
												'default'	=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'desktop'	=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'medium'	=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
											),
							'id_sizes'		=> array(
												'default'	=> 'custom_size',
												'desktop'	=> 'av-desktop-font-size',
												'medium'	=> 'av-medium-font-size',
												'small'		=> 'av-small-font-size',
												'mini'		=> 'av-mini-font-size'
											)
						),

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Font Sizes', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_styling_fonts' ), $template );

			$c = array(
						array(
							'name'		=> __( 'Font Colors', 'avia_framework' ),
							'desc'		=> __( 'Either use the themes default colors or apply some custom ones', 'avia_framework' ),
							'id'		=> 'font_color',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Default', 'avia_framework' )				=> '',
												__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
											),
						),

						array(
							'name'		=> __( 'Custom Caption Title Font Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id'		=> 'custom_title',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'container_class' => 'av_half av_half_first',
							'lockable'	=> true,
							'required'	=> array( 'font_color', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Custom Caption Content Font Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id'		=> 'custom_content',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'container_class' => 'av_half',
							'lockable'	=> true,
							'required'	=> array( 'font_color', 'equals', 'custom' )
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

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_styling_colors' ), $template );

			/**
			 * Advanced Tab
			 * ===========
			 */

			$c = array(
						array(
							'name'		=> __( 'Apply A Link To The Slide', 'avia_framework' ),
							'desc'		=> __( 'You can choose to apply the link to the whole image', 'avia_framework' ),
							'id'		=> 'link_apply',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'slide_type', 'is_empty_or', 'image' ),
							'subtype'	=> array(
												__( 'No link for this slide', 'avia_framework' )	=> '',
												__( 'Apply link to image', 'avia_framework' )		=> 'image'
											)
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'linkpicker_toggle',
							'name'			=> __( 'Image Link', 'avia_framework' ),
							'desc'			=> __( 'Where should the image link to?', 'avia_framework' ),
							'subtypes'		=> array( 'lightbox', 'manually', 'single', 'taxonomy' ),
							'target_id'		=> 'link_target',
							'lockable'		=> true,
							'required'		=> array( 'link_apply', 'equals', 'image' ),
							'no_toggle'		=> true
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Link Settings', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_advanced_link' ), $template );


			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'heading_tag',
							'theme_default'	=> 'h2',
							'context'		=> __CLASS__,
							'lockable'		=> true,
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Heading Tag', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_advanced_heading' ), $template );

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
			return $params;
		}

		/**
		 * Editor Sub Element - this function defines the visual appearance of an element that is displayed within a modal window and on click opens its own modal window
		 * Works in the same way as Editor Element
		 *
		 * @param array $params		holds the default values for $content and $args.
		 * @return array			usually holds an innerHtml key that holds item specific markup.
		 */
		public function editor_sub_element( $params )
		{
			$default = array();
			$locked = array();
			$attr = $params['args'];
			$content = $params['content'];
			Avia_Element_Templates()->set_locked_attributes( $attr, $this, $this->config['shortcode_nested'][0], $default, $locked, $content );

			$img_templ = $this->update_option_lockable( array( 'id', 'img_fakeArg' ), $locked );
			$title_templ = $this->update_option_lockable( 'title', $locked );
			$content_tmpl = $this->update_option_lockable( 'content', $locked );
			$video_tmpl = $this->update_option_lockable( 'video', $locked );

			$thumbnail = isset( $attr['id'] ) ? wp_get_attachment_image( $attr['id'] ) : '';

			$params['innerHtml']  = '';
			$params['innerHtml'] .=		"<div class='avia_title_container' data-update_element_template='yes'>";
			$params['innerHtml'] .=			'<div ' . $this->class_by_arguments_lockable( 'slide_type', $params['args'], $locked ) . '>';
			$params['innerHtml'] .=				"<span class='avia_slideshow_image' {$img_templ} >{$thumbnail}</span>";
			$params['innerHtml'] .=				"<div class='avia_slideshow_content'>";
			$params['innerHtml'] .=					"<h4 class='avia_title_container_inner' {$title_templ} >{$attr['title']}</h4>";
			$params['innerHtml'] .=					"<p class='avia_content_container' {$content_tmpl}>" . stripslashes( $content ) . '</p>';
			$params['innerHtml'] .=					"<small class='avia_video_url' {$video_tmpl}>" . stripslashes( $attr['video'] ) . '</small>';
			$params['innerHtml'] .=				'</div>';
			$params['innerHtml'] .=			'</div>';
			$params['innerHtml'] .=		'</div>';

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
						'size'				=> 'featured',
						'animation'			=> 'slide',
						'transition_speed'	=> '',
						'conditional_play'	=> '',
						'ids'				=> '',
						'autoplay'			=> 'false',
						'interval'			=> 5,
						'control_layout'	=> 'av-control-default',
						'perma_caption'		=> '',
						'autoplay_stopper'	=> '',
						'lazy_loading'		=> 'disabled',
						'img_scrset'		=> ''
				);

			// Backwards comp. - make sure to provide "old" defaults for options not set and override with default options provided
			$default = array_merge( avia_slideshow::default_args(), $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' ) );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$add = array(
						'handle'		=> $shortcodename,
						'content'		=> is_array( $content ) ? $content : ShortcodeHelper::shortcode2array( $content, 1 ),
						'class'			=> '',
						'custom_markup'	=> '',
						'el_id'			=> '',
						'heading_tag'	=> '',
						'heading_class'	=> ''
				);

			$defaults = array_merge( $default, $add );

			$atts = shortcode_atts( $defaults, $atts, $this->config['shortcode'] );

			foreach( $atts['content'] as $key => &$item )
			{
				$item_def = $this->get_default_modal_group_args();
				Avia_Element_Templates()->set_locked_attributes( $item['attr'], $this, $this->config['shortcode_nested'][0], $item_def, $locked, $item['content'] );
			}

			unset( $item );

			/**
			 * For videos reset any image related link settings - might brek frontend output
			 */
			foreach( $atts['content'] as &$slide )
			{
				if( isset( $slide['attr']['slide_type'] ) && 'video' == $slide['attr']['slide_type'] )
				{
					$slide['attr']['link_apply'] = '';
				}
			}

			unset( $slide );


			if( ! isset( $this->obj_slideshow[ $element_id ] ) )
			{
				$this->obj_slideshow[ $element_id ] = new avia_slideshow( $atts, $this );
			}

			$slideshow = $this->obj_slideshow[ $element_id ];

			$update = array(
							'class'				=> ! empty( $meta['el_class'] ) ? $meta['el_class'] : '',
							'custom_markup'		=> ! empty( $meta['custom_markup'] ) ? $meta['custom_markup'] : '',
							'el_id'				=> ! empty( $meta['custom_el_id'] ) ? $meta['custom_el_id'] : ''
						);

			$atts = $slideshow->update_config( $update );


			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['element_styling'] = $element_styling;
			$result['meta'] = $meta;

			$result = $slideshow->get_element_styles( $result );

			return $result;
		}

		/**
		 * Creates and returns the avia_slideshow object.
		 * If element id might not be unique create inline styles.
		 *
		 * @since 4.8.9
		 * @param array $atts
		 * @param array $content
		 * @param string $shortcodename
		 * @param array $meta
		 * @param string $element_id
		 * @return avia_slideshow
		 */
		public function get_avia_slideshow_object( array $atts = array(), array $content = array(), $shortcodename = '', $meta = array(), $element_id = '' )
		{
			static $slider_object_counter = 0;

			$slider_object_counter ++;

			if( empty( $element_id ) )
			{
				$element_id = uniqid( "av_slideshow_obj-$slider_object_counter-" );
			}

			$result = $this->get_element_styles( compact( array( 'atts', 'content', 'shortcodename', 'meta', 'element_id' ) ) );

			return $this->obj_slideshow[ $result[ 'element_id' ] ];
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

			$slideshow = $this->obj_slideshow[ $element_id ];
			$html = $slideshow->html();

			Av_Responsive_Images()->force_disable( 'reset' );

			return $html;
		}
	}
}

