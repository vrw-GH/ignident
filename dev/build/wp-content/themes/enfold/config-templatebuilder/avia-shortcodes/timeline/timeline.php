<?php

/**
 * Creates a timeline element with milestones that consist of a date, icon or image and text
 * Can be displayed in a vertical order or as a horizontal carousel slider
 *
 * @author tinabillinger
 * @since 4.3
 */

// Don't load directly
if( ! defined( 'ABSPATH' ) ) {   die('-1');   }

if( ! class_exists( 'avia_sc_timeline', false ) )
{
    class avia_sc_timeline extends aviaShortcodeTemplate
    {
		/**
		 * @since 4.5.7.2
		 * @var int
		 */
		static protected $timeline_count = 0;

		/**
		 *
		 * @since 4.8.7.5
		 * @var boolean
		 */
		protected $in_sc_exec;

		/**
		 * @since 4.8.7
		 * @var int
		 */
		protected $milestone_count;

		/**
		 *
		 * @since 4.5.5
		 * @param AviaBuilder $builder
		 */
		public function __construct( $builder )
		{
			$this->milestone_count = 0;

			parent::__construct( $builder );
		}

		/**
		 * @since 4.5.5
		 */
		public function __destruct()
		{
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

			$this->config['name']			= __( 'Timeline', 'avia_framework' );
			$this->config['tab']			= __( 'Content Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-timeline.png';
			$this->config['order']			= 70;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode']		= 'av_timeline';
			$this->config['shortcode_nested'] = array( 'av_timeline_item' );
			$this->config['tooltip']		= __( 'Creates a timeline', 'avia_framework' );
			$this->config['preview']		= 'large';
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
			$this->config['alb_desc_id']	= 'alb_description';
			$this->config['name_item']		= __( 'Timeline Item', 'avia_framework' );
			$this->config['tooltip_item']	= __( 'A Timeline Element Item', 'avia_framework' );
		}

		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_js = avia_minify_extension( 'js' );
			$min_css = avia_minify_extension( 'css' );

			//load css
			wp_enqueue_style( 'avia-module-slideshow', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/slideshow/slideshow{$min_css}.css", array( 'avia-layout' ), $ver );
			wp_enqueue_style( 'avia-module-timeline', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/timeline/timeline{$min_css}.css", array( 'avia-layout' ), $ver );

			//load js
			wp_enqueue_script( 'avia-module-slideshow', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/slideshow/slideshow{$min_js}.js", array( 'avia-shortcodes' ), $ver, true );
			wp_enqueue_script( 'avia-module-timeline', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/timeline/timeline{$min_js}.js", array( 'avia-shortcodes','avia-module-slideshow' ), $ver , true );
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
							'template_id'	=> $this->popup_key( 'content_timeline' )
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
													$this->popup_key( 'styling_colors' )
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
								'template_id'	=> $this->popup_key( 'advanced_animation' ),
								'nodescription' => true
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
							'name'			=> __( 'Add/Edit Milestones', 'avia_framework' ),
							'desc'			=> __( 'Here you can add, remove and edit the milestones of your timeline.', 'avia_framework' ),
							'type'			=> 'modal_group',
							'id'			=> 'content',
							'modal_title'	=> __( 'Edit Milestone', 'avia_framework' ),
							'editable_item'	=> true,
							'lockable'		=> true,
							'tmpl_set_default'	=> false,
							'std'			=> array(
													array(
														'date'	=> __( '2016', 'avia_framework' ),
														'title'	=> __( 'Milestone 1', 'avia_framework' ),
														'icon'	=> 'ue803',
														'content'	=> __( 'Enter content here', 'avia_framework' ),
													),
													array(
														'date'	=> __( '2017', 'avia_framework' ),
														'title'	=> __( 'Milestone 2', 'avia_framework' ),
														'icon'	=> 'ue856',
														'content'	=> __( 'Enter content here', 'avia_framework' ),
													),
													array(
														'date'	=> __( '2018', 'avia_framework' ),
														'title'	=> __( 'Milestone 3', 'avia_framework' ),
														'icon'	=> 'ue806',
														'content'	=> __( 'Enter content here', 'avia_framework' ),
													),
												),
							'subelements'	=> $this->create_modal()
						)
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_timeline' ), $c );

			/**
			 * Styling Tab
			 * ============
			 */

			$c = array(
						array(
							'name'	=> __( 'Timeline Orientation', 'avia_framework' ),
							'desc'	=> __( 'Set the orientation of the timeline', 'avia_framework' ),
							'id'	=> 'orientation',
							'type'	=> 'select',
							'std'	=> 'vertical',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Vertical', 'avia_framework' )		=> 'vertical',
												__( 'Horizontal', 'avia_framework' )	=> 'horizontal',
											)
						),

						array(
							'name'	=> __( 'Milestone Placement', 'avia_framework' ),
							'desc'	=> __( 'Set the placement of the milestones on the timeline', 'avia_framework' ),
							'id'	=> 'placement_v',
							'type'	=> 'select',
							'std'	=> 'alternate',
							'lockable'	=> true,
							'required'	=> array( 'orientation', 'equals', 'vertical' ),
							'subtype'	=> array(
												__( 'Alternate', 'avia_framework' )	=> 'alternate',
												__( 'Left', 'avia_framework' )		=> 'left',
												__( 'Right', 'avia_framework' )		=> 'right',
											)
						),

						array(
							'name'	=> __( 'Milestone Placement', 'avia_framework' ),
							'desc'	=> __( 'Set the placement of the milestones on the timeline', 'avia_framework' ),
							'id'	=> 'placement_h',
							'type'	=> 'select',
							'std'	=> 'top',
							'lockable'	=> true,
							'required'	=> array( 'orientation', 'equals', 'horizontal' ),
							'subtype'	=> array(
												__( 'Alternate', 'avia_framework' )	=> 'alternate',
												__( 'Top', 'avia_framework' )		=> 'top',
												__( 'Bottom', 'avia_framework' )	=> 'bottom',
											)
						),

						array(
							'name'	=> __( 'Number of Milestones per Slide', 'avia_framework' ),
							'desc'	=> __( 'Set the number of the milestones visible at a time', 'avia_framework' ),
							'id'	=> 'slides_num',
							'type'	=> 'select',
							'std'	=> '3',
							'lockable'	=> true,
							'required'	=> array( 'orientation', 'equals', 'horizontal' ),
							'subtype'	=> array(
												__( '1 Milestone', 'avia_framework' )	=> '1',
												__( '2 Milestones', 'avia_framework' )	=> '2',
												__( '3 Milestones', 'avia_framework' )	=> '3',
												__( '4 Milestones', 'avia_framework' )	=> '4',
												__( '5 Milestones', 'avia_framework' )	=> '5',
											)
						),

						array(
							'name'	=> __( 'Content Appearance', 'avia_framework' ),
							'desc'	=> __( 'Define the appearance of the content box', 'avia_framework' ),
							'id'	=> 'content_appearence',
							'type'	=> 'select',
							'std'	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__('Plain (default)', 'avia_framework')			=> '',
												__('Box Shadow with Arrow', 'avia_framework')	=> 'boxshadow',
											)
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'General Styling', 'avia_framework' ),
								'content'		=> $c
							),
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_general' ), $template );


			$c = array(
						array(
							'name'			=> __( 'Date Font Sizes', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the dates.', 'avia_framework' ),
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'lockable'		=> true,
							'textfield'		=> true,
							'subtype'		=> array(
												'default'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'desktop'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'medium'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
											),
							'id_sizes'		=> array(
												'default'	=> 'custom_date_size',
												'desktop'	=> 'av-desktop-font-size-title',
												'medium'	=> 'av-medium-font-size-title',
												'small'		=> 'av-small-font-size-title',
												'mini'		=> 'av-mini-font-size-title'
											)
						),

						array(
							'name'			=> __( 'Title Font Size', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the titles.', 'avia_framework' ),
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'lockable'		=> true,
							'textfield'		=> true,
							'subtype'		=> array(
												'default'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'desktop'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'medium'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
											),
							'id_sizes'		=> array(
												'default'	=> 'custom_title_size',
												'desktop'	=> 'av-desktop-font-size',
												'medium'	=> 'av-medium-font-size',
												'small'		=> 'av-small-font-size',
												'mini'		=> 'av-mini-font-size'
											)
						),

						array(
							'name'			=> __( 'Content Font Size', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the content.', 'avia_framework' ),
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'lockable'		=> true,
							'textfield'		=> true,
							'subtype'		=> array(
												'default'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'desktop'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'medium'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
											),
							'id_sizes'		=> array(
												'default'	=> 'custom_content_size',
												'desktop'	=> 'av-desktop-font-size-content',
												'medium'	=> 'av-medium-font-size-content',
												'small'		=> 'av-small-font-size-content',
												'mini'		=> 'av-mini-font-size-content'
											)

						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Font Sizes', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_fonts' ), $template );

			$c = array(
						array(
							'name'	=> __( 'Content Box Background Color', 'avia_framework' ),
							'desc'	=> __( 'Either use the themes default or define a custom background color', 'avia_framework' ),
							'id'	=> 'contentbox_bg_color',
							'type'	=> 'select',
							'std'	=> '',
							'lockable'	=> true,
							'required'	=> array( 'content_appearence', 'not', '' ),
							'subtype'	=> array(
												__( 'Default', 'avia_framework' )				=> '',
												__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
											),
						),

						array(
							'name'	=> __( 'Custom Content Box Background Color', 'avia_framework' ),
							'desc'	=> __( 'Select a custom background color. Leave empty to use the default', 'avia_framework' ),
							'id'	=> 'custom_contentbox_bg_color',
							'type'	=> 'colorpicker',
							'rgba'	=> true,
							'std'	=> '',
							'lockable'	=> true,
							'required'	=> array( 'contentbox_bg_color', 'equals', 'custom' )
						),

						array(
							'name'	=> __( 'Font Colors', 'avia_framework' ),
							'desc'	=> __( 'Either use the themes default colors or apply some custom ones', 'avia_framework' ),
							'id'	=> 'font_color',
							'type'	=> 'select',
							'std'	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Default', 'avia_framework' )				=> '',
												__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'

											),
						),

						array(
							'name'	=> __( 'Custom Date Font Color', 'avia_framework' ),
							'desc'	=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id'	=> 'custom_date',
							'type'	=> 'colorpicker',
							'rgba'	=> true,
							'std'	=> '',
							'container_class'	=> 'av_third av_third_first',
							'lockable'	=> true,
							'required'	=> array( 'font_color', 'equals', 'custom' )
						),

						array(
							'name'	=> __( 'Custom Title Font Color', 'avia_framework' ),
							'desc'	=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id'	=> 'custom_title',
							'type'	=> 'colorpicker',
							'rgba'	=> true,
							'std'	=> '',
							'container_class' => 'av_third',
							'lockable'	=> true,
							'required'	=> array( 'font_color', 'equals', 'custom' )
						),

						array(
							'name'	=> __( 'Custom Content Font Color', 'avia_framework' ),
							'desc'	=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id'	=> 'custom_content',
							'type'	=> 'colorpicker',
							'rgba'	=> true,
							'std'	=> '',
							'container_class' => 'av_third',
							'lockable'	=> true,
							'required'	=> array( 'font_color', 'equals', 'custom' )
						),

						array(
							'name'	=> __( 'Icon Colors', 'avia_framework' ),
							'desc'	=> __( 'Either use the themes default colors or apply some custom ones', 'avia_framework' ),
							'id'	=> 'icon_color',
							'type'	=> 'select',
							'std'	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Default', 'avia_framework')				=> '',
												__( 'Define Custom Colors', 'avia_framework')	=> 'custom'
											)
						),

						array(
							'name'	=> __( 'Custom Background Color', 'avia_framework' ),
							'desc'	=> __( 'Select a custom background color. Leave empty to use the default', 'avia_framework' ),
							'id'	=> 'icon_custom_bg',
							'type'	=> 'colorpicker',
							'rgba'	=> true,
							'std'	=> '',
							'container_class' => 'av_third av_third_first',
							'lockable'	=> true,
							'required'	=> array( 'icon_color', 'equals', 'custom' )
						),

						array(
							'name'	=> __( 'Custom Icon Font Color', 'avia_framework' ),
							'desc'	=> __( 'Select a custom icon font color. Leave empty to use the default or the accent color defined in individual Milestones.', 'avia_framework' ),
							'id'	=> 'icon_custom_font',
							'type'	=> 'colorpicker',
							'rgba'	=> true,
							'std'	=> '',
							'container_class' => 'av_third',
							'lockable'	=> true,
							'required'	=> array( 'icon_color', 'equals', 'custom' )
						),

						array(
							'name'	=> __( 'Custom Border Color', 'avia_framework' ),
							'desc'	=> __( 'Select a custom border color. Leave empty to use the default', 'avia_framework' ),
							'id'	=> 'icon_custom_border',
							'type'	=> 'colorpicker',
							'rgba'	=> true,
							'std'	=> '',
							'container_class' => 'av_third',
							'lockable'	=> true,
							'required'	=> array( 'icon_color', 'equals', 'custom' )
						),
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
			 * ============
			 */

			$c = array(
						array(
							'name' 	=> __( 'Animation', 'avia_framework' ),
							'desc' 	=> __( 'Should the items appear in an animated way?', 'avia_framework' ),
							'id' 	=> 'animation',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
											__( 'Animation activated',  'avia_framework' )		=> '',
											__( 'Animation deactivated',  'avia_framework' )	=> 'deactivated',
										)
						),
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Animation', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_animation' ), $template );

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
													$this->popup_key( 'modal_content_desc' ),
													$this->popup_key( 'modal_content_bullet' ),
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
													$this->popup_key( 'modal_styling_text' ),
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
						'type' 	=> 'toggle_container',
						'nodescription' => true
					),


					array(
							'type'			=> 'template',
							'template_id'	=> $this->popup_key( 'modal_advanced_link' )
						),

					array(
						'type'			=> 'template',
						'template_id'	=> 'developer_options_toggle',
						'args'			=> array(
												'sc'		=> $this,
												'nested'	=> 'av_timeline_item'
											)
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
							'name'	=> __( 'Milestone Date', 'avia_framework' ),
							'desc'	=> __( 'Enter the milestone date here. This can be any format. Use filter avf_customize_heading_settings to change the html tag.', 'avia_framework' ),
							'id'	=> 'date',
							'type'	=> 'input',
							'std'	=> '2018',
							'lockable'	=> true
                        ),
						array(
							'name'	=> __( 'Milestone Title', 'avia_framework' ),
							'desc'	=> __( 'Enter the Milestone Title here. Use filter avf_customize_heading_settings to change the html tag.', 'avia_framework' ),
							'id'	=> 'title',
							'type'	=> 'input',
							'std'	=> 'Milestone Title',
							'lockable'	=> true
						),
						array(
							'name'	=> __( 'Milestone Content', 'avia_framework' ),
							'desc'	=> __( 'Enter some content here', 'avia_framework' ) ,
							'id'	=> 'content',
							'type'	=> 'tiny_mce',
							'std'	=> __( 'Milestone Content goes here', 'avia_framework' ),
							'lockable'	=> true
						)
					);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Milestone Description', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_content_desc' ), $template );

			$c = array(
						array(
							'name'	=> __( 'Bullet Content', 'avia_framework' ),
							'desc'	=> __( 'Select the type of content for your milestone bullet', 'avia_framework' ),
							'id'	=> 'icon_image',
							'type'	=> 'select',
							'std'	=> 'icon',
							'lockable'	=> true,
							'subtype'	=> array(
											__( 'Add Icon', 'avia_framework' )			=> 'icon',
											__( 'Add Image', 'avia_framework' )			=> 'image',
											__( 'Auto Numbering', 'avia_framework' )	=> 'number',
										)
                        ),

						array(
							'name'	=> __( 'Bullet Shape', 'avia_framework' ),
							'desc'	=> __( 'Arrow shaped bullet', 'avia_framework' ),
							'id'	=> 'number_arrow',
							'type'	=> 'select',
							'std'	=> '',
							'lockable'	=> true,
							'required'	=> array( 'icon_image', 'equals', 'number' ),
							'subtype'	=> array(
											__( 'Round', 'avia_framework' )			=> '',
											__( 'Arrow Shaped', 'avia_framework' )	=> 'arrow',
										)
						),

						array(
							'name'	=> __( 'Milestone Icon', 'avia_framework' ),
							'desc'	=> __( 'Select an icon for your milestone below', 'avia_framework' ),
							'required'	=> array( 'icon_image', 'equals', 'icon' ),
							'id'	=> 'icon',
							'type'	=> 'iconfont',
							'std'	=> '',
							'lockable'	=> true,
							'locked'	=> array( 'icon', 'font' )
						),

						array(
							'name'		=> __( 'Choose Image', 'avia_framework' ),
							'desc'		=> __( 'Either upload a new, or choose an existing image from your media library', 'avia_framework' ),
							'id'		=> 'image',
							'type'		=> 'image',
							'title'		=> __( 'Insert Image', 'avia_framework' ),
							'button'	=> __( 'Insert', 'avia_framework' ),
							'std'		=> AviaBuilder::$path['imagesURL'] . 'placeholder.jpg',
							'lockable'	=> true,
							'required'	=> array( 'icon_image', 'equals', 'image' ),
							'locked'	=> array( 'image', 'attachment', 'attachment_size' )
                        )
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Milestone Bullet', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_content_bullet' ), $template );


			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name' => __( 'Vertical Alignment Title and Content', 'avia_framework' ),
							'desc' => __( 'Applies only for the vertical timeline.', 'avia_framework' ),
							'id' => 'milestone_valign',
							'type' => 'select',
							'std' => 'baseline',
							'lockable'	=> true,
							'subtype'	=> array(
											__( 'Baseline (Default)', 'avia_framework' )	=> 'baseline',
											__( 'Center', 'avia_framework' )				=> 'middle',
										)
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Text Alignment', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_styling_text' ), $template );




			$c = array(
						array(
							'name'	=> __( 'Milestone Colors', 'avia_framework' ),
							'desc'	=> __( 'Either use the themes default colors or apply some custom ones', 'avia_framework' ),
							'id'	=> 'milestone_color',
							'type'	=> 'select',
							'std'	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
											__( 'Default', 'avia_framework' )				=> '',
											__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
										),
						),

						array(
							'name'	=> __( 'Custom Milestone Icon Background Color', 'avia_framework' ),
							'desc'	=> __( 'Select a custom background color for the icon. Leave empty to use the default', 'avia_framework' ),
							'id'	=> 'custom_milestone_color',
							'type'	=> 'colorpicker',
							'rgba'	=> true,
							'std'	=> '',
							'container_class'	=> 'av_half av_half_first',
							'lockable'	=> true,
							'required'	=> array( 'milestone_color', 'equals', 'custom' )
						),

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
							'type'			=> 'template',
							'template_id'	=> 'linkpicker_toggle',
							'name'			=> __( 'Milestone Link', 'avia_framework' ),
							'desc'			=> __( 'Do you want to apply a link to the milestone?', 'avia_framework' ),
							'lockable'		=> true,
							'subtypes'		=> array( 'no', 'manually', 'single', 'taxonomy' ),
							'no_toggle'		=> true,
							'title_attr'	=> true
						),

						array(
							'name'	=> __( 'Link Location', 'avia_framework' ),
							'desc'	=> __( 'Where do you want to apply the link?', 'avia_framework' ),
							'id'	=> 'linkelement',
							'type'	=> 'select',
							'std'	=> 'all',
							'lockable'	=> true,
							'required'	=> array( 'link', 'not', '' ),
							'subtype'	=> array(
											__( 'Apply link to date, icon and headline', 'avia_framework' )	=> 'all',
											__( 'Apply link to icon and date', 'avia_framework' )			=> 'both',
											__( 'Apply link to icon and headline', 'avia_framework' )		=> 'icon_head',
											__( 'Apply link to date and headline', 'avia_framework' )		=> 'date_head',
											__( 'Apply link to icon only', 'avia_framework' )				=> 'icon_only'
										)
						)

				);

			$template = array(
				array(
					'type'			=> 'template',
					'template_id'	=> 'toggle',
					'title'			=> __( 'Milestone Link', 'avia_framework' ),
					'content'		=> $c
				),
			);


			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_advanced_link' ), $template );

		}

		/**
		 * Return a config array for the nested shortcde
		 *
		 * @since 4.6.4
		 * @param string $nested_shortcode
		 * @return array
		 */
		protected function get_nested_developer_elements( $nested_shortcode )
		{
			$config = array();

			if( 'av_timeline_item' == $nested_shortcode )
			{
				$config['id_name'] = 'custom_id';
				$config['id_show'] = 'yes';
			}

			return $config;
		}


		/**
         * Editor Sub Element - this function defines the visual appearance of an element that is displayed within a modal window and on click opens its own modal window
         * Works in the same way as Editor Element
		 *
         * @param array $params			holds the default values for $content and $args.
         * @return array				usually holds an innerHtml key that holds item specific markup.
         */
        public function editor_sub_element( $params )
        {
			$default = array();
			$locked = array();
			$attr = $params['args'];
			Avia_Element_Templates()->set_locked_attributes( $attr, $this, $this->config['shortcode_nested'][0], $default, $locked );

            extract( av_backend_icon( array( 'args' => $attr ) ) ); // creates $font and $display_char if the icon was passed as param 'icon' and the font as 'font'

            $params['innerHtml'] = '';
            $params['innerHtml'] .= "<div class='avia_title_container' data-update_element_template='yes'>";
            $params['innerHtml'] .=		'<span ' . $this->class_by_arguments_lockable( 'font', $font, $locked ) . '>';
            $params['innerHtml'] .=			'<span ' . $this->update_option_lockable( array( 'icon', 'icon_fakeArg' ), $locked ) . " class='avia_tab_icon'>{$display_char}</span>";
            $params['innerHtml'] .=		'</span>';
            $params['innerHtml'] .=		'<span ' . $this->update_option_lockable( 'date', $locked ) . ">{$attr['date']}</span>";
			$params['innerHtml'] .=		'<span ' . $this->update_option_lockable( 'title', $locked ) . ">: {$attr['title']}</span>";
			$params['innerHtml'] .= '</div>';

            return $params;
        }

		/**
		 * Override base class - we have global attributes here
		 *
		 * @since 4.8.7
		 * @return boolean
		 */
		public function has_global_attributes()
		{
			return true;
		}

		/**
		 * Create custom stylings
		 *
		 * @since 4.8.7
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles( array $args )
		{
			$result = parent::get_element_styles( $args );

			extract( $result );

			$default = array(
						'orientation'			=> 'vertical',
						'placement_v'			=> 'alternate',
						'placement_h'			=> 'alternate',
						'slides_num'			=> '3',
						'content_appearence'	=> '',
						'animation'				=> '',

						'custom_date_size'		=> '',
						'custom_title_size'		=> '',
						'custom_content_size'	=> '',

						'font_color'			=> '',
						'icon_color'			=> '',

						'custom_date'			=> '',
						'custom_title'			=> '',
						'custom_content'		=> '',

						'icon_custom_bg'		=> '',
						'icon_custom_font'		=> '',
						'icon_custom_border'	=> '',

						'contentbox_bg_color'	=> '',
						'custom_contentbox_bg_color' => ''
			);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

            $this->in_sc_exec = true;

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			//	backwards comp. - prepare responsive font sizes for media query
			$atts['size'] = $atts['custom_title_size'];
			$atts['size-title'] = $atts['custom_date_size'];
			$atts['size-content'] = $atts['custom_content_size'];

			$atts['placement'] = $atts['orientation'] == 'vertical' ? 'av-milestone-placement-' . $atts['placement_v'] : 'av-milestone-placement-' . $atts['placement_h'];


			$classes = array(
						'avia-timeline-container',
						$element_id,
						'av-slideshow-ui'
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $meta, 'el_class' );
			$element_styling->add_responsive_classes( 'container', 'hide_element', $atts );
			$element_styling->add_responsive_font_sizes( 'milestone-title', 'size', $atts, $this );
			$element_styling->add_responsive_font_sizes( 'milestone-date', 'size-title', $atts, $this );
			$element_styling->add_responsive_font_sizes( 'milestone-content', 'size-content', $atts, $this );

			if( 'horizontal' == $atts['orientation'] )
			{
				$element_styling->add_classes( 'container', 'avia-slideshow-carousel' );
			}

			$classes = array(
						'avia-timeline',
						'avia-timeline-' . $atts['orientation'],
						$atts['placement'],
						'avia-timeline-' . $atts['content_appearence'],
						'avia_animate_when_almost_visible'
					);

			$element_styling->add_classes( 'item_container_class', $classes );

			if( empty( $atts['animation'] ) )
			{
				$element_styling->add_classes( 'item_container_class', 'avia-timeline-animate' );
            }

			 /* custom font colors */
            if( 'custom' == $atts['font_color'] )
			{
				$element_styling->add_styles( 'milestone-date', array( 'color' => $atts['custom_date'] ) );
				$element_styling->add_styles( 'milestone-title', array( 'color' => $atts['custom_title'] ) );
				$element_styling->add_styles( 'milestone-content', array( 'color' => $atts['custom_content'] ) );
            }


			 /* custom content box styling */
            if( 'boxshadow' == $atts['content_appearence'] && 'custom' == $atts['contentbox_bg_color'] )
			{
				$element_styling->add_styles( 'milestone-contentbox', array( 'background-color' => $atts['custom_contentbox_bg_color'] ) );
            }

			 /* custom icon colors */
            if( 'custom' == $atts['icon_color'] )
			{
				$element_styling->add_styles( 'milestone-icon-inner', array(
													'background-color'	=> $atts['icon_custom_bg'],
													'color'				=> $atts['icon_custom_font']
												) );

				$element_styling->add_styles( 'milestone-icon-border', array( 'background-color' => $atts['icon_custom_border'] ) );

				$element_styling->add_styles( 'milestone-indicator', array( 'background-color' => $atts['icon_custom_bg'] ) );
				$element_styling->add_styles( 'milestone-article-footer', array( 'background-color' => $atts['icon_custom_bg'] ) );
			}


			$selectors = array(
							'container'					=> ".avia-timeline-container.{$element_id}",
							'milestone-date'			=> ".avia-timeline-container.{$element_id} .av-milestone-date",
							'milestone-title'			=> ".avia-timeline-container.{$element_id} .av-milestone-title",
							'milestone-content'			=> ".avia-timeline-container.{$element_id} .av-milestone-content",
							'milestone-contentbox'		=> ".avia-timeline-container.{$element_id} .av-milestone-contentbox",
							'milestone-icon-border'		=> ".avia-timeline-container.{$element_id} .av-milestone-icon.milestone-icon-hasborder",
							'milestone-icon-inner'		=> ".avia-timeline-container.{$element_id} .av-milestone-icon-inner",
							'milestone-indicator'		=> ".avia-timeline-container.{$element_id} .av-milestone-indicator",
							'milestone-article-footer'	=> ".avia-timeline-container.{$element_id} .av-milestone-article-footer"
				);

			$element_styling->add_selectors( $selectors );

			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['element_styling'] = $element_styling;

			return $result;
		}

		/**
		 * Create custom stylings for items
		 * (also called when creating header implicit)
		 *
		 * @since 4.8.7
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles_item( array $args )
		{
			//	get settings from container element and remove to get correct element id (override self::has_global_attributes() to activate)
			$parent_atts = isset( $args['atts']['parent_atts'] ) ? $args['atts']['parent_atts'] : null;
			unset( $args['atts']['parent_atts'] );

			$result = parent::get_element_styles_item( $args );

			/**
			 * Fixes a problem when 3-rd party plugins call nested shortcodes without executing main shortcode  (like YOAST in wpseo-filter-shortcodes)
			 */
			if( ! $this->in_sc_exec )
			{
				return $result;
			}

			extract( $result );

			if( is_null( $parent_atts ) )
			{
				$parent_atts = $this->parent_atts;
			}


			$default = array(
						'date'				=> '',
						'title'				=> '',
						'link'				=> '',
						'icon_image'		=> '',
						'number_arrow'		=> '',
						'image'				=> '',
						'attachment'		=> '',
						'attachment_size'	=> '',
						'icon'				=> '',
						'linkelement'		=> 'all',
						'linktarget'		=> '',
						'font'				=> '',
						'milestone_color'	=> '',
						'custom_milestone_color' => '',
						'milestone_valign'	=> '',
						'custom_id'			=> '',
						'custom_class'		=> ''
					);

			$default = $this->sync_sc_defaults_array( $default, 'modal_item', 'no_content' );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode_nested'][0] );

			/**
			 * @since 5.3
			 * @param string $class_animation
			 * @param array $atts
			 * @param aviaShortcodeTemplate $this
			 * @param string $shortcodename
			 * @return string
			 */
			$class_animation = apply_filters( 'avf_alb_element_animation', 'av-animated-generic', $atts, $this, $shortcodename );

			$classes = array(
						'av-milestone',
						$element_id,
						$class_animation,
						'fade-in'
					);

			$element_styling->add_classes( 'container', $classes );

			$classes = array(
						'av-milestone-icon',
						'milestone_icon'
					);

			$element_styling->add_classes( 'milestone-icon', $classes );

			$classes = array(
						'av-milestone-icon-inner',
						'milestone_inner'
					);

			$element_styling->add_classes( 'milestone-icon-inner', $classes );


            if( 'vertical' == $parent_atts['orientation'] && ! empty( $atts['milestone_valign'] ) )
			{
				$element_styling->add_classes( 'container', 'av-milestone-valign-' . $atts['milestone_valign'] );
			}

			if( ! empty( $atts['custom_class'] ) )
			{
				$element_styling->add_classes( 'container', AviaHelper::save_classes_string( $atts['custom_class'], '-', 'invalid-custom-class-found' ) );
			}

			if( 'custom' == $parent_atts['icon_color'] && ! empty( $parent_atts['icon_custom_border'] ) )
			{
				$element_styling->add_classes( 'milestone-icon', 'milestone-icon-hasborder' );
			}

			//	element specific settings
			if( 'custom' == $atts['milestone_color'] )
			{
				$element_styling->add_styles( 'milestone-icon-inner', array( 'background-color' => $atts['custom_milestone_color'] ) );
				$element_styling->add_styles( 'milestone-indicator', array( 'background-color' => $atts['custom_milestone_color'] ) );
				$element_styling->add_styles( 'milestone-article-footer', array( 'background-color' => $atts['custom_milestone_color'] ) );

				if( 'image' == $atts['icon_image'] )
				{
					$element_styling->add_styles( 'milestone-icon', array( 'border-color' => $atts['custom_milestone_color'] ) );
				}
			}

			 if( 'image' == $atts['icon_image'] )
			{
                if( ! empty( $atts['attachment'] ) )
				{
					/**
					 * Allows e.g. WPML to reroute to translated image
					 */
					$posts = get_posts( array(
											'include'			=> $atts['attachment'],
											'post_status'		=> 'inherit',
											'post_type'			=> 'attachment',
											'post_mime_type'	=> 'image',
											'order'				=> 'ASC',
											'orderby'			=> 'post__in' )
										);

                    if( is_array( $posts ) && ! empty( $posts ) )
					{
						$image_src = wp_get_attachment_image_src( $posts[0]->ID, $atts['attachment_size'] );
						if( ! empty( $image_src[0] ) )
						{
							$element_styling->add_styles( 'milestone-icon-inner', array( 'background-image' => $image_src[0] ) );
							$element_styling->add_classes( 'milestone-icon', 'milestone-icon-hasborder' );
						}
                    }
                }
            }


			$selectors = array(
							'container'					=> ".avia-timeline-container .av-milestone.{$element_id}",
							'milestone-icon'			=> ".avia-timeline-container .av-milestone.{$element_id} .x-av-milestone-icon",
							'milestone-icon-inner'		=> ".avia-timeline-container .av-milestone.{$element_id} .av-milestone-icon-inner",
							'milestone-indicator'		=> ".avia-timeline-container .av-milestone.{$element_id} .av-milestone-indicator",
							'milestone-article-footer'	=> ".avia-timeline-container .av-milestone.{$element_id} .av-milestone-article-footer"

					);

			$element_styling->add_selectors( $selectors );

			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['element_styling'] = $element_styling;

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
			$this->parent_atts = $atts;

			avia_sc_timeline::$timeline_count ++;

            $slider_attribute = 'horizontal' == $orientation ? "avia-data-slides='{$slides_num}'" : '';
			$id = ! empty( $meta['custom_el_id'] ) ? $meta['custom_el_id'] : ' id="avia-timeline-' . avia_sc_timeline::$timeline_count . '" ';

			$this->milestone_count = 0;
			$timeline_items_html = ShortcodeHelper::avia_remove_autop( $content, true );


			$style_tag = $element_styling->get_style_tag( $element_id );
			$item_tag = $element_styling->style_tag_html( $this->subitem_inline_styles, 'sub-' . $element_id );
			$container_class = $element_styling->get_class_string( 'container' );
			$item_container_class = $element_styling->get_class_string( 'item_container_class' );

            $output  = '';
			$output .= $style_tag;
			$output .= $item_tag;
            $output .=	"<div {$id} class='{$container_class}' {$slider_attribute}>";
            $output .=		"<ul class='{$item_container_class}'>";
            $output .=			$timeline_items_html;
            $output .=		'</ul>';


            if( 'horizontal' == $orientation )
			{
				$args = array(
							'class_main'	=> 'avia-slideshow-arrows avia-slideshow-controls av-timeline-nav ' . $element_styling->responsive_classes_string( 'hide_element', $atts ),
							'class_prev'	=> 'prev-slide av-timeline-nav-prev av-nav-btn',
							'class_next'	=> 'next-slide av-timeline-nav-next av-nav-btn'
						);

				$output .= aviaFrontTemplates::slide_navigation_arrows( $args );
            }

            $output .= '</div>';

			$this->in_sc_exec = false;

            return $output;
        }

		/**
		 *
		 * @param array $atts
		 * @param string $content
		 * @param string $shortcodename
		 * @return string
		 */
        public function av_timeline_item( $atts, $content = '', $shortcodename = '' )
        {
			/**
			 * Fixes a problem when 3-rd party plugins call nested shortcodes without executing main shortcode  (like YOAST in wpseo-filter-shortcodes)
			 */
			if( ! $this->in_sc_exec )
			{
				return '';
			}


			$result = $this->get_element_styles_item( compact( array( 'atts', 'content', 'shortcodename' ) ) );

			extract( $result );
			extract( $atts );


			$linktarget = '';
			$title_attr_markup = '';

			if( ! empty( $atts['link'] ) )
			{
				$atts['link'] = AviaHelper::get_url( $atts['link'] );
				$linktarget = AviaHelper::get_link_target( $atts['linktarget'] );
				$title_attr_markup = AviaHelper::get_link_title_attr_markup( $atts['title_attr'], $atts['title'] );
			}

			$list_class = $this->milestone_count % 2 == 0 ? 'av-milestone-odd' : 'av-milestone-even';
			$customid = ! empty( $atts['custom_id'] ) ? 'id="' . AviaHelper::save_string( $atts['custom_id'], '-', '', 'id' ) . '"' : '';


			$this->subitem_inline_styles .= $element_styling->get_style_tag( $element_id, 'rules_only' );
			$container_class = $element_styling->get_class_string( 'container' );
			$milestone_icon_class = $element_styling->get_class_string( 'milestone-icon' );
			$milestone_icon_inner_class = $element_styling->get_class_string( 'milestone-icon-inner' );


			/**
			 * Icon / image
			 */
			$icon_wrapper = array();
			if( in_array( $atts['linkelement'], array( 'all', 'both', 'icon_head', 'icon_only' ) ) &&  ! empty( $atts['link'] ) )
			{
				$icon_wrapper['start'] = "<a class='av-milestone-icon-wrap' href='{$atts['link']}' {$linktarget} {$title_attr_markup}>";
				$icon_wrapper['end'] = '</a>';
			}
			else
			{
				 $icon_wrapper['start'] = '<div class="av-milestone-icon-wrap">';
				 $icon_wrapper['end'] = '</div>';
			}

			$icon = $icon_wrapper['start'];

			if( $atts['icon_image'] == 'image' )
			{
				$icon .= "<span class='{$milestone_icon_class}'>";
				$icon .=	"<span class='{$milestone_icon_inner_class}'>&nbsp;</span>";
				$icon .= '</span>';
			}
			else if( $atts['icon_image'] == 'icon' )
			{
				$display_char = av_icon( $atts['icon'], $atts['font'] );

				$icon .= "<span class='{$milestone_icon_class} avia-font-{$atts['font']}'>";
				$icon .=	"<span class='{$milestone_icon_inner_class}'>";
				$icon .=		"<i class='milestone-char' {$display_char}></i>";
				$icon .=	'</span>';
				$icon .= '</span>';
			}
			else if( $atts['icon_image'] == 'number' )
			{
				$num = $this->milestone_count + 1;

				if( $atts['number_arrow'] == 'arrow' )
				{
					$milestone_icon_class .= ' milestone_bullet_arrow';
				}

				$icon .= "<span class='{$milestone_icon_class}'>";
				$icon .=	"<span class='{$milestone_icon_inner_class}'>";
				$icon .=		"<span class='milestone_number'>{$num}</span>";
				 $icon .=	'</span>';
				$icon .= '</span>';
			}

			$icon .= $icon_wrapper['end'];

			/**
			 * Date
			 */
			$default_heading = 'h2';
			$args = array(
						'heading'		=> $default_heading,
						'extra_class'	=> ''
					);

			$extra_args = array( $this, $atts, $content, 'date' );

			/**
			 * @since 4.5.5
			 * @return array
			 */
			$args = apply_filters( 'avf_customize_heading_settings', $args, __CLASS__, $extra_args );

			$heading = ! empty( $args['heading'] ) ? $args['heading'] : $default_heading;
			$css = ! empty( $args['extra_class'] ) ? $args['extra_class'] : '';

			$title_sanitized = sanitize_title( $atts['date'] );
			$date_wrapper = array();

			if( in_array( $atts['linkelement'], array( 'all', 'both', 'date_head' ) ) && ! empty( $atts['link'] ) )
			{
				$date_wrapper['start'] = "<{$heading} class='av-milestone-date {$css}' id='milestone-{$title_sanitized}'><a href='{$atts['link']}' {$linktarget} {$title_attr_markup}>";
				$date_wrapper['end'] = "</a></{$heading}>";
			}
			else
			{
				$date_wrapper['start'] = "<{$heading} class='av-milestone-date {$css}' id='milestone-{$title_sanitized}'><strong>";
				$date_wrapper['end'] = "</strong></{$heading}>";
			}

			$date  = '';
			$date .= $date_wrapper['start'];
			$date .=	$atts['date'];
			$date .=	"<span class='av-milestone-indicator'></span>";
			$date .= $date_wrapper['end'];


			/**
			 * Article / Content
			 */
			$article = '';
			$article .= "<article class='av-milestone-content-wrap'>";
			$article .=		"<div class='av-milestone-contentbox'>";
			if( ! empty( $atts['title'] ) )
			{
				$default_heading = 'h4';
				$args = array(
							'heading'		=> $default_heading,
							'extra_class'	=> ''
						);

				$extra_args = array( $this, $atts, $content, 'title' );

				/**
				 * @since 4.5.5
				 * @return array
				 */
				$args = apply_filters( 'avf_customize_heading_settings', $args, __CLASS__, $extra_args );

				$heading = ! empty( $args['heading'] ) ? $args['heading'] : $default_heading;
				$css = ! empty( $args['extra_class'] ) ? $args['extra_class'] : '';

				$title_class = "class='av-milestone-title {$css}'";
				$headline_wrap = array();

				if( in_array( $atts['linkelement'], array( 'all', 'icon_head', 'date_head' ) ) &&  ! empty( $atts['link'] ) )
				{
					$headline_wrap['start'] = "<{$heading} {$title_class}><a href='{$atts['link']}' {$linktarget} {$title_attr_markup}>";
					$headline_wrap['end'] = "</a></{$heading}>";
				}
				else
				{
					$headline_wrap['start'] = "<{$heading} {$title_class}>";
					$headline_wrap['end'] = "</{$heading}>";
				}

				$article .= '<header class="entry-content-header">';
				$article .=		$headline_wrap['start'];
				$article .=			$atts['title'];
				$article .=		$headline_wrap['end'];
				$article .= '</header>';
			}

			$article .=			"<div class='av-milestone-content'>";
			$article .=				ShortcodeHelper::avia_apply_autop( ShortcodeHelper::avia_remove_autop( $content ) );
			$article .=			'</div>';
			$article .=		'</div>';
			$article .=		"<footer class='av-milestone-article-footer entry-footer'></footer>";
			$article .=	'</article>';


			$output  = '';
			$output .= "<li {$customid} class='{$container_class} {$list_class}'>";

			switch ( $this->parent_atts['placement'] )
			{
				case 'av-milestone-placement-left':
					$output .= $date;
					$output .= $icon;
					$output .= $article;
					break;

				case 'av-milestone-placement-right':
					$output .= $date;
					$output .= $article;
					$output .= $icon;
					$output .= $date;
					break;

				case 'av-milestone-placement-alternate':
					if( $this->milestone_count % 2 == 0 )
					{
						$output .= $date;
						$output .= $icon;
						$output .= $article;
					}
					else
					{
						if( 'vertical' == $this->parent_atts['orientation'] )
						{
							$output .= $date;
						}

						$output .= $article;
						$output .= $icon;
						$output .= $date;
					}
					break;

				case 'av-milestone-placement-top':
					$output .= $date;
					$output .= $icon;
					$output .= $article;
					break;

				case 'av-milestone-placement-bottom':
					$output .= $article;
					$output .= $icon;
					$output .= $date;
					break;

			}

			$output .= '</li>';

			$this->milestone_count++;

			return $output;
		}

	}
}

