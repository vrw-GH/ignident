<?php
/**
 * Featured Image Slider
 *
 * Display a Slideshow of featured images from various posts
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_sc_featureimage_slider', false ) )
{
	class avia_sc_featureimage_slider extends aviaShortcodeTemplate
	{
		/**
		 *
		 * @var int
		 */
		static public $slide_count = 0;

		/**
		 * Save avia_feature_image_slider objects for reuse. As we need to access the same object when creating the post css file in header,
		 * create the styles and HTML creation. Makes sure to get the same id.
		 *
		 *			$element_id	=> avia_feature_image_slider
		 *
		 * @since 4.8.9
		 * @var array
		 */
		protected $obj_featureimage_slider = array();

		/**
		 * @since 4.8.9
		 * @param AviaBuilder $builder
		 */
		public function __construct( $builder )
		{
			parent::__construct( $builder );

			$this->obj_featureimage_slider = array();
		}

		/**
		 * @since 4.8.9
		 */
		public function __destruct()
		{
			unset( $this->obj_featureimage_slider );

			parent::__destruct();
		}

		/**
		 * Create the config array for the shortcode button
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['is_fullwidth']	= 'yes';
			$this->config['base_element']	= 'yes';

			/**
			 * inconsistent behaviour up to 4.2: a new element was created with a close tag, after editing it was self closing !!!
			 * @since 4.2.1: We make new element self closing now because no id='content' exists.
			 */
			$this->config['self_closing']	= 'yes';

			$this->config['name']			= __( 'Featured Image Slider', 'avia_framework' );
			$this->config['tab']			= __( 'Media Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-postslider.png';
			$this->config['order']			= 30;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode']		= 'av_feature_image_slider';
			$this->config['tooltip']		= __( 'Display a Slideshow of featured images from various posts', 'avia_framework' );
			$this->config['drag-level']		= 3;
			$this->config['preview']		= 0;
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
			$this->config['alb_desc_id']	= 'alb_description';
		}

		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_js = avia_minify_extension( 'js' );
			$min_css = avia_minify_extension( 'css' );

			//load css
			wp_enqueue_style( 'avia-module-slideshow', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/slideshow/slideshow{$min_css}.css", array( 'avia-layout' ), $ver );
			wp_enqueue_style( 'avia-module-slideshow-feature-image', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/slideshow_feature_image/slideshow_feature_image{$min_css}.css", array( 'avia-module-slideshow' ), $ver );

				//load js
			wp_enqueue_script( 'avia-module-slideshow', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/slideshow/slideshow{$min_js}.js", array( 'avia-shortcodes' ), $ver, true );
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
													$this->popup_key( 'styling_slider' ),
													$this->popup_key( 'styling_navigation' ),
													$this->popup_key( 'styling_nav_colors' ),
													$this->popup_key( 'styling_preview' ),
													$this->popup_key( 'styling_fonts' ),
													$this->popup_key( 'styling_colors' ),
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
								'template_id'	=> $this->popup_key( 'advanced_animation' )
							),

						array(
								'type'			=> 'template',
								'template_id'	=> $this->popup_key( 'advanced_overlay' )
							),

						array(
								'type'			=> 'template',
								'template_id'	=> $this->popup_key( 'advanced_heading' )
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
			/**
			 * Content Tab
			 * ===========
			 */

			$c = array(

						array(
								'name'		=> __( 'Which Entries?', 'avia_framework' ),
								'desc'		=> __( 'Select which entries should be displayed by selecting a taxonomy', 'avia_framework' ),
								'id'		=> 'link',
								'type'		=> 'linkpicker',
								'fetchTMPL'	=> true,
								'multiple'	=> 6,
								'std'		=> 'category',
								'lockable'	=> true,
								'subtype'	=> array( __( 'Display entries from:', 'avia_framework' ) => 'taxonomy' )
						),

						array(
								'name' 	=> __( 'Title, Excerpt, Read More Button', 'avia_framework' ),
								'desc' 	=> __( 'Choose the content you want to display. Please keep in mind that excerpt will be hidden on mobile devices below 767px if you do not select a responsive &quot;Caption Content Font Size&quot;', 'avia_framework' ),
								'id' 	=> 'contents',
								'type' 	=> 'select',
								'std' 	=> 'title',
								'lockable'	=> true,
								'subtype'	=> array(
													__( 'Only Title', 'avia_framework' )						=> 'title',
													__( 'Title + Read More Button', 'avia_framework' )			=> 'title_read_more',
													__( 'Title + Excerpt + Read More Button', 'avia_framework' ) => 'title_excerpt_read_more',
												)
						)
				);

			if( current_theme_supports( 'add_avia_builder_post_type_option' ) )
			{
				$element = array(
								'type'			=> 'template',
								'template_id'	=> 'avia_builder_post_type_option',
								'lockable'		=> true,
							);

				array_unshift( $c, $element );
			}

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
							'lockable'		=> true
						),

						array(
							'type'			=> 'template',
							'template_id' 	=> 'date_query',
							'lockable'		=> true,
							'period'		=> true
						),

						array(
							'name' 	=> __( 'Entry Number', 'avia_framework' ),
							'desc' 	=> __( 'How many items should be displayed?', 'avia_framework' ),
							'id' 	=> 'items',
							'type' 	=> 'select',
							'std' 	=> '3',
							'lockable'	=> true,
							'subtype'	=> AviaHtmlHelper::number_array( 1, 100, 1, array( __( 'All', 'avia_framework' ) => '-1' ) )
						),

						array(
							'name' 	=> __( 'Offset Number', 'avia_framework' ),
							'desc' 	=> __( 'The offset determines where the query begins pulling posts. Useful if you want to remove a certain number of posts because you already query them with another element.', 'avia_framework' ),
							'id' 	=> 'offset',
							'type' 	=> 'select',
							'std' 	=> 'enforce_duplicates',
							'lockable'	=> true,
							'subtype'	=> AviaHtmlHelper::number_array( 1, 100, 1, array(
														__( 'Deactivate offset', 'avia_framework' )	=> '0',
														__( 'Do not allow duplicate posts on the entire page (set offset automatically)', 'avia_framework' ) => 'no_duplicates',
														__( 'Enforce duplicates (if a blog element on the page should show the same entries as this slider use this setting)', 'avia_framework' ) => 'enforce_duplicates'
													)
												)
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
							'name'  => __( 'Slider Width/Height Ratio', 'avia_framework' ),
							'desc'  => __( 'The slider will always stretch the full available width. Here you can enter the corresponding height (eg: 4:3, 16:9) or a fixed height in px (eg: 300px)', 'avia_framework' ),
							'id'    => 'slider_size',
							'type' 	=> 'input',
							'std' 	=> '16:9',
							'lockable'	=> true
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Slider', 'avia_framework' ),
								'content'		=> $c
							),
					);



			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_slider' ), $template );

			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'slideshow_controls',
							'lockable'		=> true
						),
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
							'name' 	=> __( 'Preview Image Size', 'avia_framework' ),
							'desc' 	=> __( 'Set the image size of the preview images', 'avia_framework' ),
							'id' 	=> 'preview_mode',
							'type' 	=> 'select',
							'std' 	=> 'auto',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Set the preview image size automatically based on slider height', 'avia_framework' )	=> 'auto',
												__( 'Choose the preview image size manually (select thumbnail size)', 'avia_framework' )	=> 'custom'
											)
						),

						array(
							'name' 	=> __( 'Select custom preview image size', 'avia_framework' ),
							'desc' 	=> __( 'Choose image size for Preview Image', 'avia_framework' ),
							'id' 	=> 'image_size',
							'type' 	=> 'select',
							'std' 	=> 'portfolio',
							'lockable'	=> true,
							'required' 	=> array( 'preview_mode', 'equals', 'custom' ),
							'subtype' =>  AviaHelper::get_registered_image_sizes( array( 'logo' ) )
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Preview Image Size', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_preview' ), $template );


			$c = array(
						array(
							'name'			=> __( 'Caption Title Font Sizes', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the caption titles.', 'avia_framework' ),
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'lockable'		=> true,
							'textfield'		=> true,
							'required'		=> array( 'contents', 'not', '' ),
							'subtype'		=> array(
												'default'	=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'desktop'	=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'medium'	=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
											),
							'id_sizes'		=> array(
												'default'	=> 'size-title',
												'desktop'	=> 'av-desktop-font-size-title',
												'medium'	=> 'av-medium-font-size-title',
												'small'		=> 'av-small-font-size-title',
												'mini'		=> 'av-mini-font-size-title'
											)
						),

						array(
							'name'			=> __( 'Caption Content Font Sizes', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the excerpt.', 'avia_framework' ),
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'lockable'		=> true,
							'textfield'		=> true,
							'required'		=> array( 'contents', 'equals', 'title_excerpt_read_more' ),
							'subtype'		=> array(
												'default'	=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'desktop'	=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'medium'	=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
											),
							'id_sizes'		=> array(
												'default'	=> 'size',
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
							'name'		=> __( 'Content Color', 'avia_framework' ),
							'desc'		=> __( 'Select a color for the content here. Leave blank for theme default.', 'avia_framework' ),
							'id'		=> 'content_color',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true,
							'required'	=> array( 'contents', 'equals', 'title_excerpt_read_more' )
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


			$c = array(
						array(
								'type'			=> 'template',
								'template_id'	=> 'image_copyright_styling_toggle',
								'lockable'		=> true
						)

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_copyright' ), $c );


			/**
			 * Animation Tab
			 * ===========
			 */

			$c = array(

						array(
							'type'			=> 'template',
							'template_id'	=> 'slideshow_transition',
							'select_trans'	=> array( 'slide', 'fade' ),
							'std_trans'		=> 'fade',
							'lockable'		=> true
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'slideshow_rotation',
							'select_vals'	=> 'yes,no',
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

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_animation' ), $template );


			$c = array(
						array(
								'type'			=> 'template',
								'template_id'	=> 'slideshow_overlay',
								'lockable'		=> true
							),
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Overlay', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_overlay' ), $template );

			$c = array(
						array(
							'type'				=> 'template',
							'template_id'		=> 'heading_tag',
							'theme_default'		=> 'h2',
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

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_heading' ), $template );

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

			$default = array_merge( avia_feature_image_slider::default_args(), $this->get_default_sc_args() );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );
			$meta = aviaShortcodeTemplate::set_frontend_developer_heading_tag( $atts, $meta );

			$add = array(
						'handle'		=> $shortcodename,
//						'content'		=> ShortcodeHelper::shortcode2array( $content, 1 ),
						'class'			=> '',
						'custom_markup'	=> '',
						'el_id'			=> '',
						'heading_tag'	=> '',
						'heading_class'	=> ''
				);

			$defaults = array_merge( $default, $add );

			$atts = shortcode_atts( $defaults, $atts, $this->config['shortcode'] );

			if( ! isset( $this->obj_featureimage_slider[ $element_id ] ) )
			{
				$feature_image_slider = new avia_feature_image_slider( $atts, $this );
				$this->obj_featureimage_slider[ $element_id ] = $feature_image_slider;
				$feature_image_slider->query_entries();
			}
			else
			{
				$feature_image_slider = $this->obj_featureimage_slider[ $element_id ];
			}

			$update = array(
							'class'				=> ! empty( $meta['custom_class'] ) ? $meta['custom_class'] : '',
							'custom_markup'		=> ! empty( $meta['custom_markup'] ) ? $meta['custom_markup'] : '',
							'el_id'				=> ! empty( $meta['custom_el_id'] ) ? $meta['custom_el_id'] : '',
							'heading_tag'		=> ! empty( $meta['heading_tag'] ) ? $meta['heading_tag'] : '',
							'heading_class'		=> ! empty( $meta['heading_class'] ) ? $meta['heading_class'] : '',
						);

			$atts = $feature_image_slider->update_config( $update );



			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['element_styling'] = $element_styling;
			$result['meta'] = $meta;

			$result = $feature_image_slider->get_element_styles( $result );

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

			$skipSecond = false;
			avia_sc_featureimage_slider::$slide_count++;
			$av_display_classes = $element_styling->responsive_classes_string( 'hide_element', $atts );

			$params['class'] = "avia-featureimage-slider-wrap main_color {$av_display_classes} {$meta['el_class']}";
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

			$params['id'] = AviaHelper::save_string( $meta['custom_id_val'], '-', 'avia_feature_image_slider_' . avia_sc_featureimage_slider::$slide_count );

			if( '' == $atts['el_id'] )
			{
				$atts['el_id'] = ' id="avia_feature_image_slider_' . avia_sc_featureimage_slider::$slide_count . '" ';
			}

			if( ShortcodeHelper::is_top_level() )
			{
				$atts['el_id'] = '';
				$atts['class'] = '';
			}

			$feature_image_slider = $this->obj_featureimage_slider[ $element_id ];
			$feature_image_slider->update_config( $atts );
			$slide_html = $feature_image_slider->html();

			//if the element is nested within a section or a column dont create the section shortcode around it
			if( ! ShortcodeHelper::is_top_level() )
			{
				return $slide_html;
			}

			// $slide_html  = "<div class='container'>" . $slide_html . "</div>";

			$output  = '';
			$output .= avia_new_section( $params );
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
				$output .= avia_new_section( array( 'close' => false, 'id' => 'after_feature_image_slider_' . avia_sc_featureimage_slider::$slide_count ) );
			}

			return $output;
		}
	}
}


if ( ! class_exists( 'avia_feature_image_slider', false ) )
{
	class avia_feature_image_slider extends \aviaBuilder\base\aviaSubItemQueryBase
	{
		use \aviaBuilder\traits\scSlideshowUIControls;

		/**
		 * @since < 4.0
		 * @var int
		 */
		protected $slide_count;

		/**
		 * @since < 4.0
		 * @var array
		 */
		protected $entries;

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
		public function __construct( $atts = array(), aviaShortcodeTemplate $sc_context = null )
		{
			parent::__construct( $atts, $sc_context, avia_feature_image_slider::default_args() );

			$this->slide_count = 0;
			$this->current_page = 1;

			if( $this->config['autoplay'] == 'no' )
			{
				$this->config['autoplay'] = false;
			}

			/**
			 * @since 4.8.9
			 * param array $this->config
			 * @return array
			 */
			$this->config = apply_filters( 'avf_feature_image_slider_config', $this->config );
		}

		/**
		 * @since 4.5.6.1
		 */
		public function __destruct()
		{
			unset( $this->entries );

			parent::__destruct();
		}

		/**
		 * Returns the default args
		 *
		 * @since 4.8
		 * @param array $args
		 * @return array
		 */
		static public function default_args( array $args = array() )
		{
			$default = array(
							'items'					=> '16',
							'taxonomy'				=> 'category',
							'post_type'				=> get_post_types(),
							'contents'				=> 'title',
							'preview_mode'			=> 'auto',
							'image_size'			=> 'portfolio',
							'autoplay'				=> 'no',
							'autoplay_stopper'		=> '',
							'animation'				=> 'fade',
							'paginate'				=> 'no',
							'use_main_query_pagination' => 'no',
							'interval'				=> 5,
							'class'					=> '',
							'categories'			=> array(),
							'wc_prod_visible'		=> '',
							'wc_prod_hidden'		=> '',
							'wc_prod_featured'		=> '',
							'prod_order_by'			=> '',
							'prod_order'			=> '',
							'custom_query'			=> array(),
							'lightbox_size'			=> 'large',
							'offset'				=> 0,
							'bg_slider'				=> true,
							'keep_padding'			=> true,
							'custom_markup'			=> '',
							'slider_size'			=> '16:9',
							'control_layout'		=> 'av-control-default',
							'slider_navigation'		=> 'av-navigate-arrows av-navigate-dots',
							'overlay_enable'		=> '',
							'overlay_opacity'		=> '',
							'overlay_color'			=> '',
							'overlay_pattern'		=> '',
							'overlay_custom_pattern' => '',
							'date_filter'			=> '',
							'date_filter_start'		=> '',
							'date_filter_end'		=> '',
							'date_filter_format'	=> 'yy/mm/dd',		//	'yy/mm/dd' | 'dd-mm-yy'	| yyyymmdd
							'period_filter_unit_1'	=> '',
							'period_filter_unit_2'	=> '',
							'el_id'					=> '',
							'heading_tag'			=> '',
							'heading_class'			=> ''
					);

			$default = array_merge( $default, $args );

			/**
			 * @since 4.8.9
			 * @param array $default
			 * @return array
			 */
			return apply_filters( 'avf_feature_image_slider_defaults', $default );
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

			$classes = array(
							'avia-slideshow',
							'avia-featureimage-slideshow',
							$element_id,
							'avia-animated-caption',
							'avia-slideshow-' . $this->config['image_size'],
							"avia-{$this->config['animation']}-slider",
						);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes( 'container', $this->config['class'] );

			$element_styling->add_responsive_classes( 'container', 'hide_element', $this->config );
			$element_styling->add_responsive_font_sizes( 'title', 'size-title', $atts, $this->sc_context );
			$element_styling->add_responsive_font_sizes( 'content', 'size', $atts, $this->sc_context );
			$element_styling->add_responsive_classes( 'container', 'font_sizes', $this->config, 'size' );

			$ui_args = array(
						'element_id'		=> $element_id,
						'element_styling'	=> $element_styling,
						'atts'				=> $this->config,
						'autoplay_option'	=> 'yes',
						'context'			=> __CLASS__,
					);

			$this->addSlideshowAttributes( $ui_args );

			if( strpos( $this->config['slider_size'], ':') !== false )
			{
				$ratio = explode( ':', trim( $this->config['slider_size'] ) );
				if( empty( $ratio[0] ) )
				{
					$ratio[0] = 16;
				}

				if( empty( $ratio[1] ) )
				{
					$ratio[1] = 9;
				}

				$element_styling->add_styles( 'slide-ul', array( 'padding-bottom' => ( 100 / ( (int) $ratio[0] / (int) $ratio[1] ) ) . '%' ) );
			}
			else
			{
				$element_styling->add_styles( 'slide-ul', array( 'height' => (int) $this->config['slider_size'] . 'px' ) );
			}

			$element_styling->add_styles( 'title', array( 'color' => $this->config['title_color'] ) );
			if( 'title_excerpt_read_more' == $this->config['contents'] )
			{
				$element_styling->add_styles( 'content', array( 'color' => $this->config['content_color'] ) );
			}

			if( ! empty( $this->config['img_copyright'] ) )
			{
				if( ! empty( $this->config['img_copyright_color'] ) )
				{
					$element_styling->add_styles( 'copyright', array( 'color' => $this->config['img_copyright_color'] ) );
				}

				if( ! empty( $this->config['img_copyright_bg'] ) )
				{
					$element_styling->add_styles( 'copyright', array( 'background-color' => $this->config['img_copyright_bg'] ) );
				}

				$element_styling->add_responsive_font_sizes( 'copyright', 'img_copyright_font', $this->config, $this->sc_context );
			}


			//	attention: !important rules for 'title' and 'content' in other slideshow css files
			$selectors = array(
						'container'		=> ".avia-featureimage-slideshow.{$element_id}",
						'slide-ul'		=> ".avia-featureimage-slideshow.{$element_id} .avia-slideshow-inner",
						'title'			=> "#top .avia-featureimage-slideshow.{$element_id} .avia-caption-title *",
						'content'		=> "#top #wrap_all .avia-featureimage-slideshow.{$element_id} .avia-caption-content *",
						'copyright'		=> ".avia-featureimage-slideshow.{$element_id} .av-image-copyright"
					);

			$element_styling->add_selectors( $selectors );

			foreach( $this->entries->posts as $key => $slide )
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
							'avia-featureimage-slide',
							$element_id,
							'slide-' . ( $key + 1 )
						);

			$element_styling->add_classes( 'container', $classes );


			$selectors = array(
						'container'			=> ".avia-featureimage-slideshow .avia-featureimage-slide.{$element_id}",

					);

			$element_styling->add_selectors( $selectors );

			$result['element_styling'] = $element_styling;

			return $result;
		}

		/**
		 *
		 * @return string
		 */
		public function html()
		{
			if( $this->slide_count == 0 )
			{
				return '';
			}

            $markup = avia_markup_helper( array( 'context' => 'image', 'echo' => false, 'custom_markup' => $this->config['custom_markup'] ) );

			$style_tag = $this->element_styles->get_style_tag( $this->element_id );
			$container_class = $this->element_styles->get_class_string( 'container' );
			$data_slideshow_options = $this->element_styles->get_data_attributes_json_string( 'container', 'slideshow-options' );

			$output  = '';
			$output .= $style_tag;
			$output .= "<div {$this->config['el_id']} class='{$container_class} avia-slideshow-" . avia_sc_featureimage_slider::$slide_count . "' {$data_slideshow_options} {$markup}>";
			$output .=		'<ul class="avia-slideshow-inner avia-slideshow-fixed-height">';
			$output .=			$this->slide_html();
			$output .=		'</ul>';

			if( $this->slide_count > 1 )
			{
				$output .= $this->slide_navigation_arrows();

				if( 'av-control-hidden' != $this->config['control_layout'] && false !== strpos( $this->config['slider_navigation'], 'av-navigate-dots' )  )
				{
					$output .= $this->slide_navigation_dots();
				}
			}

			if( ! empty( $this->config['caption_override'] ) )
			{
				$output .= $this->config['caption_override'];
			}

			$output .= '</div>';

			return $output;
		}

		/**
		 * Renders the slides
		 *
		 * @return string
		 */
		protected function slide_html()
		{
			$html = '';

			$copyright_class = ! empty( $this->config['img_copyright'] ) ? 'av-copyright-' . $this->config['img_copyright'] : '';
            $markup_url = avia_markup_helper( array( 'context' => 'image_url', 'echo' => false, 'custom_markup' => $this->config['custom_markup'] ) );

			foreach( $this->entries->posts as $key => $slide )
			{
				$thumb_id = get_post_thumbnail_id( $slide->ID );
				$slide_data = '';
				$slide_class = '';
				$copy_html = '';

				$img = wp_get_attachment_image_src( $thumb_id, $this->config['image_size'] );
				$link = get_post_meta( $slide->ID , '_portfolio_custom_link', true ) != '' ? get_post_meta( $slide->ID , '_portfolio_custom_link_url', true ) : get_permalink( $slide->ID );
				$title = get_the_title( $slide->ID );

				$default_heading = ! empty( $this->config['heading_tag'] ) ? $this->config['heading_tag'] : 'h2';
				$args = array(
							'heading'		=> $default_heading,
							'extra_class'	=> $this->config['heading_class']
						);

				$extra_args = array( $this, $slide, $key, __METHOD__ );

				/**
				 * @since 4.5.5
				 * @return array
				 */
				$args = apply_filters( 'avf_customize_heading_settings', $args, __CLASS__, $extra_args );

				$heading = ! empty( $args['heading'] ) ? $args['heading'] : $default_heading;
				$css = ! empty( $args['extra_class'] ) ? $args['extra_class'] : $this->config['heading_class'];

				if( ! empty( $copyright_class ) )
				{
					$copyright_text = get_post_meta( $thumb_id, '_avia_attachment_copyright', true );

					if( ! empty( $copyright_text ) )
					{
						$copy_html .= "<div class='av-image-copyright {$copyright_class}'>";
						$copy_html .=		"<span>{$copyright_text}</span>";
						$copy_html .= '</div>';
					}
				}

				$caption  = '';
				$caption .= '<div class="caption_fullwidth av-slideshow-caption caption_center">';
				$caption .=		'<div class="container caption_container">';
				$caption .=			'<div class="slideshow_caption">';
				$caption .=				'<div class="slideshow_inner_caption">';
				$caption .=					'<div class="slideshow_align_caption">';
				$caption .=						"<{$heading} class='avia-caption-title {$css}'><a href='{$link}' {$markup_url}>{$title}</a></{$heading}>";

				if( strpos( $this->config['contents'], 'excerpt' ) !== false )
				{
					$excerpt = ! empty( $slide->post_excerpt ) ? $slide->post_excerpt : avia_backend_truncate( $slide->post_content, apply_filters( 'avf_feature_image_slider_excerpt_length', 320 ), apply_filters( 'avf_feature_image_slider_excerpt_delimiter', ' ' ), '…', true, '' );

					if( ! empty( $excerpt ) )
					{
						$caption .= '<div class="avia-caption-content" itemprop="description">';
						$caption .=		wpautop( $excerpt );
						$caption .= '</div>';
					}
				}

				if( strpos( $this->config['contents'], 'read_more' ) !== false )
				{
					$caption .= '<a href="' . $link . '" class="avia-slideshow-button avia-button avia-color-light" data-duration="800" data-easing="easeInOutQuad">' . __( 'Read more', 'avia_framework' ) . '</a>';
				}

				$caption .=					'</div>';
				$caption .=				'</div>';
				$caption .=			'</div>';
				$caption .=		'</div>';
				$caption .=		$this->create_overlay();
				$caption .=	'</div>';

				if( ! is_array( $img ) )
				{
					$slide_class .= ' av-no-image-slider';
				}
				else
				{
					$slide_data = "data-img-url='{$img[0]}'";
				}

				//	add item container data
				$item_info = $this->element_styles->get_subitem_styling_info( $key );
				$container_class = $item_info['element_styling']->get_class_string( 'container' );

				$html .= "<li {$slide_data} class='{$container_class} {$slide_class} slide-id-{$slide->ID}'>";
				$html .=	$caption;
				$html .=	$copy_html;
				$html .= '</li>';
			}

			return $html;
		}

		/**
		 *
		 * @return string
		 */
		protected function slide_navigation_dots()
		{
			$args = array(
						'total_entries'		=> $this->slide_count,
						'container_entries'	=> 1,
						'context'			=> get_class( $this ),
						'params'			=> $this->config
					);


			return aviaFrontTemplates::slide_navigation_dots( $args );
		}

		/**
		 * Create arrows to scroll slides
		 *
		 * @since 4.8.3			reroute to aviaFrontTemplates
		 * @return string
		 */
		protected function slide_navigation_arrows()
		{
			$args = array(
						'context'		=> get_class( $this ),
						'params'		=> $this->config
					);

			return aviaFrontTemplates::slide_navigation_arrows( $args );
		}

		/**
		 *
		 * @return string
		 */
		protected function create_overlay()
		{
			extract( $this->config );

			/*check/create overlay*/
			$overlay = '';

			if( ! empty( $overlay_enable ) )
			{
				$overlay_src = '';
				$overlay = "opacity: {$overlay_opacity}; ";

				if( ! empty( $overlay_color ) )
				{
					$overlay .= "background-color: {$overlay_color}; ";
				}

				if( ! empty( $overlay_pattern ) )
				{
					if( $overlay_pattern == 'custom' )
					{
						$overlay_src = $overlay_custom_pattern;
					}
					else
					{
						$overlay_src = str_replace( '{{AVIA_BASE_URL}}', AVIA_BASE_URL, $overlay_pattern );
					}
				}

				if( ! empty( $overlay_src ) )
				{
					$overlay .= "background-image: url({$overlay_src}); background-repeat: repeat;";
				}

				$overlay = "<div class='av-section-color-overlay' style='{$overlay}'></div>";
			}

			return $overlay;
		}

		/**
		 * fetch new entries
		 *
		 * @since < 4.0
		 * @param array $params
		 */
		public function query_entries( $params = array() )
		{
			global $avia_config;

			if( empty( $params ) )
			{
				$params = $this->config;
			}

			if( empty( $params['custom_query'] ) )
            {
				$terms = array();

				if( isset( $params['link'] ) )
				{
					$link = explode(',', $params['link'], 2 );
					$params['taxonomy'] = $link[0];
					$params['categories'] = isset( $link[1] ) ? $link[1] : '';
				}

				$query = array();

				if( ! empty( $params['categories'] ) )
				{
					//get the categories
					$terms = explode( ',', $params['categories'] );
				}

				if( $params['use_main_query_pagination'] == 'yes' )
				{
					$this->current_page = ( $params['paginate'] != 'no' ) ? avia_get_current_pagination_number() : 1;
				}
				else
				{
					$this->current_page = ( $params['paginate'] != 'no' ) ? avia_get_current_pagination_number( 'avia-element-paging' ) : 1;
				}


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
					 * You also need to add post_status 'private' to the query params with filter avia_feature_image_slider_query.
					 *
					 * @since 4.4.2
					 * @added_by Günter
					 * @param array $term_args
					 * @param array $params
					 * @return array
					 */
					$term_args = apply_filters( 'avf_av_feature_image_slider_term_args', $term_args, $params );

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
					$no_duplicates = true;
				}

				if( $params['offset'] == 'enforce_duplicates' )
				{
					$params['offset'] = 0;
					$no_duplicates = false;
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

				$query = array(	'orderby'		=> $orderby,
								'order'			=> $order,
								'paged'			=> $this->current_page,
								'post_type'		=> $params['post_type'],
//								'post_status'	=> 'publish',
								'offset'		=> $params['offset'],
								'posts_per_page' => $params['items'],
								'post__not_in'	=> ( ! empty( $no_duplicates ) ) ? $avia_config['posts_on_current_page'] : array(),
								'meta_query'	=> $meta_query,
								'tax_query'		=> $tax_query,
								'date_query'	=> $date_query,
							);

				if( ! empty( $ordering_args['meta_key'] ) )
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
			$query = apply_filters( 'avia_feature_image_slider_query', $query, $params );

			$this->entries = new WP_Query( $query );
			$this->slide_count = $this->entries->post_count;

		    // store the queried post ids in
            if( ( $this->entries->post_count > 0 ) && $params['offset'] != 'enforce_duplicates' )
            {
				foreach( $this->entries->posts as $entry )
                {
                    $avia_config['posts_on_current_page'][] = $entry->ID;
                }
            }

			if( function_exists( 'WC' ) )
			{
				avia_wc_clear_catalog_ordering_args_filters();
				$avia_config['woocommerce']['disable_sorting_options'] = false;
			}

		}
	}
}
