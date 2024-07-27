<?php
/**
 * Icon Grid Shortcode
 *
 * @author tinabillinger
 * @since 4.5
 * @deprecated 5.1.2			kept for sites that need the old styling and function
 *
 * Creates an icon grid with toolips or flip content
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

if( ! class_exists( 'avia_sc_icongrid', false ) )
{
	class avia_sc_icongrid extends aviaShortcodeTemplate
	{
		/**
		 * @since 4.8.8
		 * @var array
		 */
		protected $in_sc_exec;

		/**
		 * @since 4.8.8
		 * @var int
		 */
		protected $numrow;

		/**
		 * @since 4.8.8
		 * @var int
		 */
		protected $row_count;

		/**
		 * @since 4.8.8
		 * @var int
		 */
		protected $item_count;

		/**
		 * @since 4.8.8
		 * @var int
		 */
		protected $row_item;

		/**
		 *
		 * @since 4.5.1
		 * @param AviaBuilder $builder
		 */
		public function __construct( $builder )
		{
			parent::__construct( $builder );

			$this->in_sc_exec = false;
			$this->numrow = 0;
			$this->row_count = 1;
			$this->item_count = 0;
			$this->row_item = 0;
		}

		/**
		 * @since 4.5.1
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

			$this->config['name']			= __( 'Icon Grid', 'avia_framework' );
			$this->config['tab']			= __( 'Content Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-icongrid.png';
			$this->config['order']			= 90;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode']		= 'av_icongrid';
			$this->config['shortcode_nested'] = array( 'av_icongrid_item' );
			$this->config['tooltip']		= __( 'Creates an icon grid with toolips or flip content', 'avia_framework' );
			$this->config['preview']		= false;
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
			$this->config['alb_desc_id']	= 'alb_description';
			$this->config['name_item']		= __( 'Icon Grid Item', 'avia_framework' );
			$this->config['tooltip_item']	= __( 'An Icon Grid Element Item', 'avia_framework' );
		}

		protected function extra_assets()
		{
			wp_enqueue_style( 'avia-module-icon', AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/icon/icon.css' , array( 'avia-layout' ), false );
			wp_enqueue_style( 'avia-module-icongrid', AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/icongrid/v50/icongrid.css', array( 'avia-layout' ), false );

			wp_enqueue_script( 'avia-module-icongrid', AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/icongrid/v50/icongrid.js', array( 'avia-shortcodes' ), false, true );
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
							'template_id'	=> $this->popup_key( 'content_elements' ),
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
													$this->popup_key( 'styling_grid' ),
													$this->popup_key( 'styling_padding' ),
													$this->popup_key( 'styling_fonts' ),
													$this->popup_key( 'styling_font_colors' ),
													$this->popup_key( 'styling_background_colors' ),
													$this->popup_key( 'styling_borders' ),
													$this->popup_key( 'styling_boxshadow' ),
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
							),


						array(
								'type'				=> 'template',
								'template_id'		=> 'screen_options_toggle',
								'lockable'			=> true,
								'templates_include'	=> array(
													$this->popup_key( 'advanced_mobile' ),
													'screen_options_visibility'
												),
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
							'name'			=> __( 'Add/Edit Grid items', 'avia_framework' ),
							'desc'			=> __( 'Here you can add, remove and edit the items of your item grid.', 'avia_framework' ),
							'type'			=> 'modal_group',
							'id'			=> 'content',
							'modal_title'	=> __( 'Edit Grid Item', 'avia_framework' ),
							'editable_item'	=> true,
							'lockable'		=> true,
							'std'			=> array(
													array(
														'title'		=> __( 'Grid Title 1', 'avia_framework' ),
														'icon'		=> '43',
														'content'	=> __( 'Enter content here', 'avia_framework' ),
													),
													array(
														'title'		=> __( 'Grid Title 2', 'avia_framework' ),
														'icon'		=> '25',
														'content'	=> __( 'Enter content here', 'avia_framework' ),
													),
													array(
														'title'		=> __( 'Grid Title 3', 'avia_framework' ),
														'icon'		=> '64',
														'content'	=> __( 'Enter content here', 'avia_framework' ),
													),
												),
							'subelements' 	=> $this->create_modal()
						),

						array(
							'name'		=> __( 'Content Appearance', 'avia_framework' ),
							'desc'		=> __( 'Change the appearance of your icon grid', 'avia_framework' ),
							'id'		=> 'icongrid_styling',
							'type'		=> 'select',
							'std'		=> 'flipbox',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Content appears in Flip Box', 'avia_framework' )	=> 'flipbox',
												__( 'Content appears in Tooltip', 'avia_framework' )	=> 'tooltip',
											)
						),

						array(
							'name'		=> __( 'Mobile Flip Box Close Behaviour', 'avia_framework' ),
							'desc'		=> __( 'Select the behaviour of an open flipbox on mobile devices and touch screens', 'avia_framework' ),
							'id'		=> 'flipbox_force_close',
							'type'		=> 'select',
							'std'		=> 'flipbox',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Close only when visitor clicks in icongrid', 'avia_framework' )	=> '',
												__( 'Also close when user clicks outside icongrid', 'avia_framework' )	=> 'avia_flip_force_close',
											)
						),
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_elements' ), $c );

			/**
			 * Styling Tab
			 * ============
			 */
			$c = array(
						array(
							'name' 	=> __( 'Row Cells', 'avia_framework' ),
							'desc' 	=> __( 'Select the number of cells in a row. Each cell will contain 1 item and additional rows will be added to show all items. Consider the container size (e.g. column size) and the amount of text.', 'avia_framework' ),
							'id' 	=> 'icongrid_numrow',
							'type' 	=> 'select',
							'std' 	=> '3',
							'lockable'	=> true,
							'subtype'	=> array(
												__( '1 cell', 'avia_framework' )	=> '1',
												__( '2 cells', 'avia_framework' )	=> '2',
												__( '3 cells', 'avia_framework' )	=> '3',
												__( '4 cells', 'avia_framework' )	=> '4',
												__( '5 cells', 'avia_framework' )	=> '5',
											)
						),

						//	border options removed 4.8.8 as they are buggy.
//						array(
//							'name'		=> __( 'Grid Borders', 'avia_framework' ),
//							'desc'		=> __( 'Define the appearance of the grid borders here. Currently only &quot;No Borders&quot; is supported. Other options might be added in future.', 'avia_framework' ),
//							'id'		=> 'icongrid_borders',
//							'type'		=> 'select',
//							'std'		=> 'none',
//							'lockable'	=> true,
//							'subtype'	=> array(
//												__( 'No Borders', 'avia_framework' )					=> 'none',
//												__( '1px Borders between cells', 'avia_framework' )		=> 'between',
//												__( '1px Borders around each cell', 'avia_framework' )	=> 'all',
//											)
//						),

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Grid Styling', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_grid' ), $template );


			$c = array(
						array(
							'name'			=> __( 'Title Font Sizes', 'avia_framework' ),
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
												'desktop'	=> 'av-desktop-font-size-title',
												'medium'	=> 'av-medium-font-size-title',
												'small'		=> 'av-small-font-size-title',
												'mini'		=> 'av-mini-font-size-title'
											)
						),

						array(
							'name'			=> __( 'Sub-Title Font Sizes', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the sub titles.', 'avia_framework' ),
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
												'default'	=> 'custom_subtitle_size',
												'desktop'	=> 'av-desktop-font-size-1',
												'medium'	=> 'av-medium-font-size-1',
												'small'		=> 'av-small-font-size-1',
												'mini'		=> 'av-mini-font-size-1'
											)
						),

						array(
							'name'			=> __( 'Content Font Sizes', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the content on the backside of the flipbox or the tooltip.', 'avia_framework' ),
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
												'desktop'	=> 'av-desktop-font-size',
												'medium'	=> 'av-medium-font-size',
												'small'		=> 'av-small-font-size',
												'mini'		=> 'av-mini-font-size'
											)
						),

						array(
							'name'			=> __( 'Icon Font Sizes', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the icon.', 'avia_framework' ),
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
												'default'	=> 'custom_icon_size',
												'desktop'	=> 'av-desktop-font-size-2',
												'medium'	=> 'av-medium-font-size-2',
												'small'		=> 'av-small-font-size-2',
												'mini'		=> 'av-mini-font-size-2'
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

			$info = '<br /><strong>' . __( 'Important: To set padding to 0 you MUST use 0px or 0% (this is a backwards compatibility bug)', 'avia_framework' ) . '</strong>';

			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'padding',
							'name'			=> __( 'Items Padding', 'avia_framework' ),
							'desc'			=> __( 'Set the padding for the icongrid container. Both pixel and &percnt; based values are accepted. eg: 30px, 5&percnt;. Leave empty to use theme default.', 'avia_framework' ) . $info,
							'id'			=> 'icongrid_padding',
							'std'			=> '',
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Padding', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_padding' ), $template );

			$c = array(
						array(
							'name' 	=> __( 'Font Colors', 'avia_framework' ),
							'desc' 	=> __( 'Either use the themes default colors or apply some custom ones', 'avia_framework' ),
							'id' 	=> 'font_color',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Default', 'avia_framework' )	=> '',
												__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
											),
						),

						array(
							'name' 	=> __( 'Custom Icon Font Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_icon',
							'type' 	=> 'colorpicker',
							'rgba'  => true,
							'std' 	=> '',
							'container_class'	=> 'av_half av_half_first',
							'lockable'	=> true,
							'required'	=> array( 'font_color', 'equals', 'custom' )
						),

						array(
							'name' 	=> __( 'Custom Title Font Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_title',
							'type' 	=> 'colorpicker',
							'rgba'  => true,
							'std' 	=> '',
							'container_class' => 'av_half',
							'lockable'	=> true,
							'required'	=> array( 'font_color', 'equals', 'custom' )
						),

						array(
							'name' 	=> __( 'Custom Sub-Title Font Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_subtitle',
							'type' 	=> 'colorpicker',
							'rgba'  => true,
							'std' 	=> '',
							'container_class' => 'av_half',
							'lockable'	=> true,
							'required'	=> array( 'font_color', 'equals', 'custom' )
						),

						array(
							'name' 	=> __( 'Custom Content Font Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_content',
							'type' 	=> 'colorpicker',
							'rgba'  => true,
							'std' 	=> '',
							'container_class' => 'av_half',
							'lockable'	=> true,
							'required'	=> array( 'font_color', 'equals', 'custom' )
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Font Colors', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_font_colors' ), $template );

			$c = array(
						array(
							'name' 	=> __( 'Background Colors', 'avia_framework' ),
							'desc' 	=> __( 'Either use the themes default colors or apply some custom ones', 'avia_framework' ),
							'id' 	=> 'bg_color',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Default', 'avia_framework' )	=> '',
												__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
											),
						),

						array(
							'name'		=> __( 'Custom Background Front','avia_framework' ),
							'desc'		=> __( 'Select the type of background.', 'avia_framework' ),
							'id'		=> 'custom_front_bg_type',
							'type'		=> 'select',
							'std'		=> 'bg_color',
							'lockable'	=> true,
							'required'	=> array( 'bg_color', 'equals', 'custom' ),
							'subtype'	=> array(
												__( 'Background Color', 'avia_framework' )		=> 'bg_color',
												__( 'Background Gradient', 'avia_framework' )	=> 'bg_gradient',
											)
						),

						array(
							'name'		=> __( 'Custom Background Color Front', 'avia_framework' ),
							'desc'		=> __( 'Select a custom background color. Leave empty to use the default', 'avia_framework' ),
							'id'		=> 'custom_front_bg',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'custom_front_bg_type', 'equals', 'bg_color' )
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'gradient_colors',
							'id'			=> array(
													'custom_front_gradient_direction',
													'custom_front_gradient_color1',
													'custom_front_gradient_color2',
													'custom_front_gradient_color3'
												),
							'lockable'		=> true,
							'required'		=> array( 'custom_front_bg_type', 'equals', 'bg_gradient' )
						),

						array(
							'name' 	=> __( 'Custom Background Back / Tooltip', 'avia_framework' ),
							'desc' 	=> __( 'Select the type of background.', 'avia_framework' ),
							'id' 	=> 'custom_back_bg_type',
							'type' 	=> 'select',
							'std' 	=> 'bg_color',
							'lockable'	=> true,
							'required'	=> array( 'bg_color', 'equals', 'custom' ),
							'subtype'	=> array(
												__( 'Background Color','avia_framework' )		=> 'bg_color',
												__( 'Background Gradient','avia_framework' )	=> 'bg_gradient',
											)
						),

						array(
							'name'		=> __( 'Custom Background Color Back / Tooltip', 'avia_framework' ),
							'desc'		=> __( 'Select a custom background color. Leave empty to use the default', 'avia_framework' ),
							'id'		=> 'custom_back_bg',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'custom_back_bg_type', 'equals', 'bg_color' )
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'gradient_colors',
							'id'			=> array(
													'custom_back_gradient_direction',
													'custom_back_gradient_color1',
													'custom_back_gradient_color2',
													'custom_back_gradient_color3'
												),
							'lockable'		=> true,
							'required'		=> array( 'custom_back_bg_type', 'equals', 'bg_gradient' )
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Background Colors', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_background_colors' ), $template );

			$c = array(
//						array(
//							'name' 	=> __( 'Custom Grid Border Color', 'avia_framework' ),
//							'desc' 	=> __( 'Select a custom grid color. Leave empty to use the theme default', 'avia_framework' ),
//							'id' 	=> 'custom_grid',
//							'type' 	=> 'colorpicker',
//							'rgba'  => true,
//							'std' 	=> '',
//							'lockable'	=> true,
//							'required'	=> array( 'icongrid_borders', 'parent_in_array', 'between,all' )
//						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'border',
							'id'			=> 'border_front',
							'names'			=> array(
													'style'		=> __( 'Border Style Item', 'avia_framework' ),
													'width'		=> __( 'Border Width Item', 'avia_framework' ),
													'color'		=> __( 'Border Color Item', 'avia_framework' )
												),
							'default_check'	=> true,
							'lockable'		=> true
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'border',
							'id'			=> 'border_flip',
							'names'			=> array(
													'style'		=> __( 'Border Style Flipbox (= Backside)', 'avia_framework' ),
													'width'		=> __( 'Border Width Flipbox (= Backside)', 'avia_framework' ),
													'color'		=> __( 'Border Color Flipbox (= Backside)', 'avia_framework' )
												),
							'default_check'	=> true,
							'lockable'		=> true,
							'required'		=> array( 'icongrid_styling', 'not', 'tooltip' )
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'border_radius',
							'id'			=> 'border_radius',
							'name'			=> __( 'Border Radius (Front And Flipbox Backside)', 'avia_framework' ),
							'lockable'		=> true,
							'required'		=> array( 'icongrid_styling', 'not', 'tooltip' ),
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'border',
							'id'			=> 'border_tooltip',
							'names'			=> array(
													'style'		=> __( 'Border Style Tooltip', 'avia_framework' ),
													'width'		=> __( 'Border Width Tooltip', 'avia_framework' ),
													'color'		=> __( 'Border Color Tooltip', 'avia_framework' )
												),
							'default_check'	=> true,
							'lockable'		=> true,
							'required'		=> array( 'icongrid_styling', 'equals', 'tooltip' ),
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'border_radius',
							'id'			=> 'border_radius_tooltip',
							'name'			=> __( 'Border Radius Tooltip', 'avia_framework' ),
							'lockable'		=> true,
							'required'		=> array( 'icongrid_styling', 'equals', 'tooltip' ),
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Borders', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_borders' ), $template );

			$c = array(

						array(
							'type'			=> 'template',
							'template_id'	=> 'box_shadow',
							'default_check'	=> true,
							'lockable'		=> true
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'box_shadow',
							'id'			=> 'box_shadow_flip',
							'names'			=> array(
													__( 'Box Shadow Flipbox (= Backside)', 'avia_framework' ),
													__( 'Box Shadow Styling Flipbox (= Backside)', 'avia_framework' ),
													__( 'Box Shadow Color Flipbox (= Backside)', 'avia_framework' )
												),
							'default_check'	=> true,
							'lockable'		=> true,
							'required'		=> array( 'icongrid_styling', 'not', 'tooltip' )
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'box_shadow',
							'id'			=> 'box_shadow_tooltip',
							'names'			=> array(
													__( 'Box Shadow Tooltip', 'avia_framework' ),
													__( 'Box Shadow Styling Tooltip', 'avia_framework' ),
													__( 'Box Shadow Color Tooltip', 'avia_framework' )
												),
							'default_check'	=> true,
							'lockable'		=> true,
							'required'		=> array( 'icongrid_styling', 'equals', 'tooltip' )
						)

			);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Box Shadow', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_boxshadow' ), $template );


			/**
			 * Animation Tab
			 * =============
			 */

			$c = array(
						array(
							'name' 	=> __( 'Rotation Of Flip Box', 'avia_framework' ),
							'desc' 	=> __( 'Select the rotation axis for the flip box', 'avia_framework' ),
							'id' 	=> 'flip_axis',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'required'	=> array( 'icongrid_styling', 'equals', 'flipbox' ),
							'subtype'	=> array(
												__( 'Rotate Y-axis', 'avia_framework' )	=> '',
												__( 'Rotate X-axis', 'avia_framework' )	=> 'avia-flip-x',
											)
						)

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

			$c = array(
						array(
							'name'		=> __( 'Mobile Behaviour', 'avia_framework' ),
							'desc'		=> __( 'Choose how the cells inside the grid should behave on mobile devices and small screens', 'avia_framework' ),
							'id'		=> 'mobile',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Change layout on mobile devices', 'avia_framework' )					=> '',
												__( 'Keep selected layout just like on large screens', 'avia_framework' )	=> 'av-fixed-cells',
											)
						),

						array(
							'name'		=> __( 'Mobile Breaking Points', 'avia_framework' ),
							'desc'		=> __( 'Set the screen width when cells in a row should switch to fullwidth (or 50%)', 'avia_framework' ),
							'type'		=> 'heading',
							'required'	=> array( 'mobile', 'not', 'av-fixed-cells' ),
							'description_class'	=> 'av-builder-note av-neutral'
						),

						array(
							'name'		=> __( 'Responsive Break Points', 'avia_framework' ),
							'desc'		=> __( 'The cells in a row will switch to 50% width or fullwidth at these screen widths', 'avia_framework' ),
							'id'		=> 'mobile_breaking',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'mobile', 'not', 'av-fixed-cells' ),
							'subtype'	=> array(
												__( 'To fullwidth at a screen width of 767px or lower', 'avia_framework' )			=> '',
												__( 'To fullwidth at a screen width of 989px or lower', 'avia_framework' )			=> 'av-break-989',
												__( 'To 50% at a screen width of 989px, to fullwidth on 767px', 'avia_framework' )	=> 'av-50-break-989',
												__( 'To 50% at a screen width of 767px or lower', 'avia_framework' )					=> 'av-50-break-767',
											)
						)

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_mobile' ), $c, true );

		}

		/**
		 * Creates the modal popup for a single icongrid entry
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
													$this->popup_key( 'modal_content_front' ),
													$this->popup_key( 'modal_content_back' ),
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
													$this->popup_key( 'modal_styling_font_colors' ),
													$this->popup_key( 'modal_styling_background_colors' ),
													$this->popup_key( 'modal_styling_borders' ),
													$this->popup_key( 'modal_styling_boxshadow' )
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
							'name' 	=> __( 'Grid Item Title', 'avia_framework' ),
							'desc' 	=> __( 'Enter the grid item title here (Better keep it short)', 'avia_framework' ) ,
							'id' 	=> 'title',
							'type' 	=> 'input',
							'std' 	=> 'Grid Title',
							'lockable'	=> true
						),

						array(
							'name' 	=> __( 'Grid Item Sub-Title', 'avia_framework' ),
							'desc' 	=> __( 'Enter the grid item sub-title here', 'avia_framework' ) ,
							'id' 	=> 'subtitle',
							'type' 	=> 'input',
							'std' 	=> 'Grid Sub-Title',
							'lockable'	=> true
						),

						array(
							'name' 	=> __( 'Grid Item Icon', 'avia_framework' ),
							'desc' 	=> __( 'Select an icon for your grid item below', 'avia_framework' ),
							'id' 	=> 'icon',
							'type' 	=> 'iconfont',
							'std' 	=> '',
							'lockable'	=> true,
							'locked'	=> array( 'icon', 'font' )
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Grid Element Front Content', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_content_front' ), $template );


			$c = array(
						array(
							'name'		=> __( 'Grid Item Content', 'avia_framework' ),
							'desc'		=> __( 'Enter some content here. Will be used as backside of flipbox or tooltip popup.', 'avia_framework' ) ,
							'id'		=> 'content',
							'type'		=> 'tiny_mce',
							'std'		=> __( 'Grid Content goes here', 'avia_framework' ),
							'lockable'	=> true
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Grid Element Backside/Tooltip Content', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_content_back' ), $template );


			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name' 	=> __( 'Font Colors', 'avia_framework' ),
							'desc' 	=> __( 'Either use the themes default colors or apply some custom ones', 'avia_framework' ),
							'id' 	=> 'item_font_color',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
											__( 'Default', 'avia_framework' )		=> '',
											__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
										),
						),

						array(
							'name'		=> __( 'Custom Icon Font Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id'		=> 'item_custom_icon',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'container_class' => 'av_half av_half_first',
							'lockable'	=> true,
							'required'	=> array( 'item_font_color', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Custom Title Font Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id'		=> 'item_custom_title',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'container_class' => 'av_half',
							'lockable'	=> true,
							'required'	=> array( 'item_font_color', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Custom Sub-Title Font Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id'		=> 'item_custom_subtitle',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'container_class' => 'av_half',
							'lockable'	=> true,
							'required'	=> array( 'item_font_color', 'equals','custom' )
						),

						array(
							'name'		=> __( 'Custom Content Font Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id'		=> 'item_custom_content',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'container_class' => 'av_half',
							'lockable'	=> true,
							'required'	=> array( 'item_font_color', 'equals', 'custom' )
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Font Colors', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_styling_font_colors' ), $template );

			$c = array(
						array(
							'name' 	=> __( 'Background Colors', 'avia_framework' ),
							'desc' 	=> __( 'Either use the themes default colors or apply some custom ones', 'avia_framework' ),
							'id' 	=> 'item_bg_color',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
											__( 'Default', 'avia_framework' )	=> '',
											__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
										),
						),

						array(
							'name' 	=> __( 'Custom Background Front', 'avia_framework' ),
							'desc' 	=> __( 'Select the type of background.', 'avia_framework' ),
							'id' 	=> 'item_custom_front_bg_type',
							'type' 	=> 'select',
							'std' 	=> 'bg_color',
							'lockable'	=> true,
							'required'	=> array( 'item_bg_color', 'equals', 'custom' ),
							'subtype'	=> array(
											__( 'Background Color', 'avia_framework' )		=> 'bg_color',
											__( 'Background Gradient', 'avia_framework' )	=> 'bg_gradient',
										)
						),

						array(
							'name'		=> __( 'Custom Background Color Front', 'avia_framework' ),
							'desc'		=> __( 'Select a custom background color. Leave empty to use the default', 'avia_framework' ),
							'id'		=> 'item_custom_front_bg',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'container_class' => 'av_half av_half_first',
							'lockable'	=> true,
							'required'	=> array( 'item_custom_front_bg_type', 'equals', 'bg_color' )
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'gradient_colors',
							'id'			=> array(
													'item_custom_front_gradient_direction',
													'item_custom_front_gradient_color1',
													'item_custom_front_gradient_color2',
													'item_custom_front_gradient_color3'
												),
							'lockable'		=> true,
							'required'		=> array( 'item_custom_front_bg_type', 'equals', 'bg_gradient' )
						),

						array(
							'name'		=> __( 'Custom Background Back / Tooltip','avia_framework' ),
							'desc'		=> __( 'Select the type of background.', 'avia_framework' ),
							'id'		=> 'item_custom_back_bg_type',
							'type'		=> 'select',
							'std'		=> 'bg_color',
							'lockable'	=> true,
							'required'	=> array( 'item_bg_color', 'equals', 'custom' ),
							'subtype'	=> array(
											__( 'Background Color', 'avia_framework' )		=> 'bg_color',
											__( 'Background Gradient', 'avia_framework' )	=> 'bg_gradient',
										)
						),

						array(
							'name'		=> __( 'Custom Background Color Back / Tooltip', 'avia_framework' ),
							'desc'		=> __( 'Select a custom background color. Leave empty to use the default', 'avia_framework' ),
							'id'		=> 'item_custom_back_bg',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'item_custom_back_bg_type', 'equals', 'bg_color' )
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'gradient_colors',
							'id'			=> array(
													'item_custom_back_gradient_direction',
													'item_custom_back_gradient_color1',
													'item_custom_back_gradient_color2',
													'item_custom_back_gradient_color3'
												),
							'lockable'		=> true,
							'required'		=> array( 'item_custom_back_bg_type', 'equals', 'bg_gradient' )
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Background Colors', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_styling_background_colors' ), $template );


			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'border',
							'id'			=> 'item_border_front',
							'names'			=> array(
													'style'		=> __( 'Border Style Item', 'avia_framework' ),
													'width'		=> __( 'Border Width Item', 'avia_framework' ),
													'color'		=> __( 'Border Color Item', 'avia_framework' )
												),
							'default_check'	=> true,
							'lockable'		=> true
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'border',
							'id'			=> 'item_border_flip',
							'names'			=> array(
													'style'		=> __( 'Border Style Flipbox (= Backside)', 'avia_framework' ),
													'width'		=> __( 'Border Width Flipbox (= Backside)', 'avia_framework' ),
													'color'		=> __( 'Border Color Flipbox (= Backside)', 'avia_framework' )
												),
							'default_check'	=> true,
							'lockable'		=> true
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'border_radius',
							'id'			=> 'item_border_radius',
							'name'			=> __( 'Border Radius (Front And Flipbox Backside)', 'avia_framework' ),
							'lockable'		=> true
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'border',
							'id'			=> 'item_border_tooltip',
							'names'			=> array(
													'style'		=> __( 'Border Style Tooltip', 'avia_framework' ),
													'width'		=> __( 'Border Width Tooltip', 'avia_framework' ),
													'color'		=> __( 'Border Color Tooltip', 'avia_framework' )
												),
							'default_check'	=> true,
							'lockable'		=> true
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'border_radius',
							'id'			=> 'item_border_radius_tooltip',
							'name'			=> __( 'Border Radius Tooltip', 'avia_framework' ),
							'lockable'		=> true
						),

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Borders', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_styling_borders' ), $template );

			$c = array(

						array(
							'type'			=> 'template',
							'template_id'	=> 'box_shadow',
							'id'			=> 'item_box_shadow',
							'default_check'	=> true,
							'lockable'		=> true
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'box_shadow',
							'id'			=> 'item_box_shadow_flip',
							'names'			=> array(
													__( 'Box Shadow Flipbox (= Backside)', 'avia_framework' ),
													__( 'Box Shadow Styling Flipbox (= Backside)', 'avia_framework' ),
													__( 'Box Shadow Color Flipbox (= Backside)', 'avia_framework' )
												),
							'default_check'	=> true,
							'lockable'		=> true
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'box_shadow',
							'id'			=> 'item_box_shadow_tooltip',
							'names'			=> array(
													__( 'Box Shadow Tooltip', 'avia_framework' ),
													__( 'Box Shadow Styling Tooltip', 'avia_framework' ),
													__( 'Box Shadow Color Tooltip', 'avia_framework' )
												),
							'default_check'	=> true,
							'lockable'		=> true
						)

			);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Box Shadow', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_styling_boxshadow' ), $template );


			/**
			 * Advanced Tab
			 * ============
			 */

			$c = array(
						array(
							'type'				=> 'template',
							'template_id'		=> 'heading_tag',
							'theme_default'		=> 'h4',
							'context'			=> __CLASS__,
							'lockable'			=> true,
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
							'name'			=> __( 'Title Link?', 'avia_framework' ),
							'desc'			=> __( 'Do you want to apply a link to the title?', 'avia_framework' ),
							'lockable'		=> true,
							'subtypes'		=> array( 'no', 'manually', 'single', 'taxonomy' ),
//							'no_toggle'		=> true
						)

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_advanced_link' ), $c );

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

			$template = $this->update_template_lockable( 'title', __( 'Element', 'avia_framework' ). ': {{title}}', $locked );

			extract( av_backend_icon( array( 'args' => $attr ) ) ); // creates $font and $display_char if the icon was passed as param 'icon" and the font as "font"

			$params['innerHtml']  = '';
			$params['innerHtml'] .=		"<div class='avia_title_container' data-update_element_template='yes'>";
			$params['innerHtml'] .=			'<span ' . $this->class_by_arguments_lockable( 'font', $font, $locked ) . '>';
			$params['innerHtml'] .=				'<span ' . $this->update_option_lockable( array( 'icon', 'icon_fakeArg' ), $locked ) . " class='avia_tab_icon'>{$display_char}</span>";
			$params['innerHtml'] .=			'</span>';
			$params['innerHtml'] .=			"<span {$template} >" . __( 'Element', 'avia_framework' ) . ": {$attr['title']}</span>";
			$params['innerHtml'] .=		'</div>';

			return $params;
		}

		/**
		 * Create custom stylings
		 *
		 * @since 4.8.8
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles( array $args )
		{
			$result = parent::get_element_styles( $args );

			extract( $result );

			$default = array(
						'font_color'						=> '',
						'custom_icon'						=> '',
						'custom_title'						=> '',
						'custom_subtitle'					=> '',
						'custom_content'					=> '',
						'icongrid_padding'					=> '',
						'bg_color'							=> '',
						'custom_front_bg_type'				=> '',
						'custom_front_bg'					=> '',
						'custom_front_gradient_color1'		=> '',
						'custom_front_gradient_color2'		=> '',
						'custom_front_gradient_direction'	=> '',
						'custom_back_bg_type'				=> '',
						'custom_back_bg'					=> '',
						'custom_back_gradient_color1'		=> '',
						'custom_back_gradient_color2'		=> '',
						'custom_back_gradient_direction'	=> '',
						'icongrid_styling'					=> 'flipbox',
						'flipbox_force_close'				=> '',
						'flip_axis'							=> '',
						'icongrid_numrow'					=> '',
						'icongrid_borders'					=> 'none',
						'custom_title_size'					=> '',
						'custom_subtitle_size'				=> '',
						'custom_content_size'				=> '',
						'custom_icon_size'					=> '',
						'mobile'							=> '',
						'mobile_breaking'					=> ''
					);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$this->in_sc_exec = true;
			$this->numrow = $atts['icongrid_numrow'];
			$this->row_count = 1;
			$this->item_count = 0;
			$this->row_item = 0;

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			//	@since 4.8.8 remove buggy borders
			$atts['icongrid_borders'] = 'none';
			$atts['custom_grid'] = '';

			//	fix a backwards comp. bug with wrong atts
			$atts['size-title'] = $atts['custom_title_size'];
			$atts['size-1'] = $atts['custom_subtitle_size'];
			$atts['size'] = $atts['custom_content_size'];
			$atts['size-2'] = $atts['custom_icon_size'];

			$element_styling->create_callback_styles( $atts );


			$classes = array(
						'avia-icon-grid-container',
						$element_id,
						$atts['flip_axis']
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $meta, 'el_class' );
			$element_styling->add_responsive_classes( 'container', 'hide_element', $atts );
			$element_styling->add_responsive_font_sizes( 'li-front-title', 'size-title', $atts, $this );
			$element_styling->add_responsive_font_sizes( 'li-front-subtitle', 'size-1', $atts, $this );
			$element_styling->add_responsive_font_sizes( 'li-content-inner', 'size', $atts, $this );
			$element_styling->add_responsive_font_sizes( 'li-flipback', 'size', $atts, $this );
			$element_styling->add_responsive_font_sizes( 'li-front-icon', 'size-2', $atts, $this );

			$classes = array(
							'avia-icongrid',
							'clearfix',
							'avia_animate_when_almost_visible',
							"avia-icongrid-{$atts['icongrid_styling']}",
							$atts['flipbox_force_close'],
							"avia-icongrid-borders-{$atts['icongrid_borders']}",
							"avia-icongrid-numrow-{$atts['icongrid_numrow']}",
					);

			if( 'av-fixed-cells' != $atts['mobile'] )
			{
				$classes[] = 'av-flex-cells';
				$classes[] = ! empty( $atts['mobile_breaking'] ) ? $atts['mobile_breaking'] : 'av-break-767';
			}
			else
			{
				$classes[] = $atts['mobile'];
			}

			$element_styling->add_classes( 'container-ul', $classes );

			if( 'custom' == $atts['bg_color'] )
			{
				if( 'bg_gradient' == $atts['custom_front_bg_type'] )
				{
					if( 'tooltip' == $atts['icongrid_styling'] )
					{
						$element_styling->add_callback_styles( 'li-article', array( 'custom_front_gradient_direction' ) );
					}
					else
					{
						$element_styling->add_callback_styles( 'li-front', array( 'custom_front_gradient_direction' ) );
					}
				}
				else
				{
					if( 'tooltip' == $atts['icongrid_styling'] )
					{
						$element_styling->add_styles( 'li-article', array( 'background-color' => $atts['custom_front_bg'] ) );
					}
					else
					{
						$element_styling->add_styles( 'li-front', array( 'background-color' => $atts['custom_front_bg'] ) );
					}
				}

				if( 'bg_gradient' == $atts['custom_back_bg_type'] )
				{
					$element_styling->add_callback_styles( 'li-content', array( 'custom_back_gradient_direction' ) );
					$element_styling->add_callback_styles( 'li-flipback', array( 'custom_back_gradient_direction' ) );
				}
				else
				{
					$element_styling->add_styles( 'li-content', array( 'background-color' => $atts['custom_back_bg'] ) );
					$element_styling->add_styles( 'li-flipback', array( 'background-color' => $atts['custom_back_bg'] ) );
				}
			}

			//	this is a bug from older versions < 4.8.8 where 0 was interpreted as not set
			if( ! empty( $atts['icongrid_padding'] ) )
			{
				$element_styling->add_callback_styles( 'li-front', array( 'icongrid_padding' ) );
				$element_styling->add_callback_styles( 'li-content', array( 'icongrid_padding' ) );
				$element_styling->add_callback_styles( 'li-flipback', array( 'icongrid_padding' ) );
			}

			if( 'custom' == $atts['font_color'] )
			{
				$element_styling->add_styles( 'li-front-icon', array( 'color' => $atts['custom_icon'] ) );
				$element_styling->add_styles( 'li-front-title', array( 'color' => $atts['custom_title'] ) );
				$element_styling->add_styles( 'li-front-subtitle', array( 'color' => $atts['custom_subtitle'] ) );
				$element_styling->add_styles( 'li-content-text', array( 'color' => $atts['custom_content'] ) );
				$element_styling->add_styles( 'li-flipback-text', array( 'color' => $atts['custom_content'] ) );
			}


			if( $atts['icongrid_borders'] != 'none' )
			{
				$element_styling->add_styles( 'container-ul', array( 'border-color' => $atts['custom_grid'] ) );
				//	grid borders inside
				$element_styling->add_styles( 'container-li', array( 'color' => $atts['custom_grid'] ) );
			}

			if( 'tooltip' == $atts['icongrid_styling'] )
			{
				$element_styling->add_callback_styles( 'li-article', array( 'border_front', 'border_radius', 'box_shadow' ) );
			}
			else
			{
				$element_styling->add_callback_styles( 'li-front', array( 'border_front', 'border_radius', 'box_shadow' ) );
			}

			$element_styling->add_callback_styles( 'li-flipback', array( 'border_flip', 'border_radius', 'box_shadow_flip' ) );
			$element_styling->add_callback_styles( 'li-content', array( 'border_tooltip', 'border_radius_tooltip', 'box_shadow_tooltip' ) );

			$selectors = array(
						'container'				=> ".avia-icon-grid-container.{$element_id}",
						'container-ul'			=> ".avia-icon-grid-container.{$element_id} .avia-icongrid",
						'container-li'			=> ".avia-icon-grid-container.{$element_id} .avia-icongrid-wrapper",
						'li-article'			=> ".avia-icon-grid-container.{$element_id} .avia-icongrid-wrapper .article-icon-entry",
						'li-front'				=> ".avia-icon-grid-container.{$element_id} .avia-icongrid-wrapper .avia-icongrid-front",
						'li-front-icon'			=> ".avia-icon-grid-container.{$element_id} .avia-icongrid-wrapper .avia-icongrid-icon",
						'li-front-title'		=> ".avia-icon-grid-container.{$element_id} .avia-icongrid-wrapper .icongrid_title",
						'li-front-subtitle'		=> ".avia-icon-grid-container.{$element_id} .avia-icongrid-wrapper .icongrid_subtitle",
						'li-content'			=> ".avia-icon-grid-container.{$element_id} .avia-icongrid-wrapper .avia-icongrid-content",
						'li-content-inner'		=> ".avia-icon-grid-container.{$element_id} .avia-icongrid-wrapper .avia-icongrid-content .avia-icongrid-inner",
						'li-content-text'		=> ".avia-icon-grid-container.{$element_id} .avia-icongrid-wrapper .avia-icongrid-content .avia-icongrid-text",
						'li-flipback'			=> ".avia-icon-grid-container.{$element_id} .avia-icongrid-wrapper .avia-icongrid-flipback",
						'li-flipback-text'		=> ".avia-icon-grid-container.{$element_id} .avia-icongrid-wrapper .avia-icongrid-flipback .avia-icongrid-text",
					);

			$element_styling->add_selectors( $selectors );


			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['element_styling'] = $element_styling;

			$this->parent_atts = $atts;

			return $result;
		}

		/**
		 * Create custom stylings for items
		 * (also called when creating header implicit)
		 *
		 * @since 4.8.8
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles_item( array $args )
		{
			$result = parent::get_element_styles_item( $args );

			extract( $result );

			$default = array(
							'title'								=> '',
							'subtitle'							=> '',
							'link'								=> '',
							'icon'								=> '',
							'font'								=>'',
							'linktarget'						=> '',
							'custom_markup'						=> '',
							'item_font_color'					=> '',
							'item_custom_icon'					=> '',
							'item_custom_title'					=> '',
							'item_custom_subtitle'				=> '',
							'item_custom_content'				=> '',
							'item_bg_color'						=> '',
							'item_custom_front_bg_type'			=> '',
							'item_custom_front_gradient_color1'	=> '',
							'item_custom_front_gradient_color2'	=> '',
							'item_custom_front_gradient_direction' => '',
							'item_custom_front_bg'				=> '',
							'item_custom_back_bg_type'			=> '',
							'item_custom_back_bg'				=> '',
							'item_custom_back_gradient_color1'	=> '',
							'item_custom_back_gradient_color2'	=> '',
							'item_custom_back_gradient_direction' => ''
			);

			$default = $this->sync_sc_defaults_array( $default, 'modal_item', 'no_content' );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $this->config['shortcode_nested'][0], $default, $locked, $content );
			$meta = aviaShortcodeTemplate::set_frontend_developer_heading_tag( $atts );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode_nested'][0] );


			$element_styling->create_callback_styles( $atts, true );


			$classes = array(
						'avia-icongrid-wrapper',
						$element_id
					);

			$element_styling->add_classes( 'container-li-wrapper', $classes );


			if( 'custom' == $atts['item_bg_color'] )
			{
				if( 'bg_gradient' == $atts['item_custom_front_bg_type'] )
				{
					if( 'tooltip' == $this->parent_atts['icongrid_styling'] )
					{
						$element_styling->add_callback_styles( 'li-article', array( 'item_custom_front_gradient_direction' ) );
					}
					else
					{
						$element_styling->add_callback_styles( 'li-front', array( 'item_custom_front_gradient_direction' ) );
					}
				}
				else
				{
					//	avoid remove gradient from global settings
					if( '' != $atts['item_custom_front_bg'] )
					{
						if( 'tooltip' == $this->parent_atts['icongrid_styling'] )
						{
							$element_styling->add_styles( 'li-article', array( 'background' => 'unset' ) );
							$element_styling->add_styles( 'li-article', array( 'background-color' => $atts['item_custom_front_bg'] ) );
						}
						else
						{
							$element_styling->add_styles( 'li-front', array( 'background' => 'unset' ) );
							$element_styling->add_styles( 'li-front', array( 'background-color' => $atts['item_custom_front_bg'] ) );
						}
					}
				}

				if( 'bg_gradient' == $atts['item_custom_back_bg_type'] )
				{
					$element_styling->add_callback_styles( 'li-content', array( 'item_custom_back_gradient_direction' ) );
					$element_styling->add_callback_styles( 'li-flipback', array( 'item_custom_back_gradient_direction' ) );
				}
				else
				{
					//	avoid remove gradient from global settings
					if( '' != $atts['item_custom_back_bg'] )
					{
						$element_styling->add_styles( 'li-content', array( 'background' => 'unset' ) );
						$element_styling->add_styles( 'li-flipback', array( 'background' => 'unset' ) );
						$element_styling->add_styles( 'li-content', array( 'background-color' => $atts['item_custom_back_bg'] ) );
						$element_styling->add_styles( 'li-flipback', array( 'background-color' => $atts['item_custom_back_bg'] ) );
					}
				}
			}

			if( 'custom' == $atts['item_font_color'] )
			{
				$element_styling->add_styles( 'li-front-icon', array( 'color' => $atts['item_custom_icon'] ) );
				$element_styling->add_styles( 'li-front-title', array( 'color' => $atts['item_custom_title'] ) );
				$element_styling->add_styles( 'li-front-subtitle', array( 'color' => $atts['item_custom_subtitle'] ) );
				$element_styling->add_styles( 'li-content-text', array( 'color' => $atts['item_custom_content'] ) );
				$element_styling->add_styles( 'li-flipback-text', array( 'color' => $atts['item_custom_content'] ) );
			}

			if( 'tooltip' == $this->parent_atts['icongrid_styling'] )
			{
				$element_styling->add_callback_styles( 'li-article', array( 'item_border_front', 'item_border_radius', 'item_box_shadow' ) );
			}
			else
			{
				$element_styling->add_callback_styles( 'li-front', array( 'item_border_front', 'item_border_radius', 'item_box_shadow' ) );
			}

			$element_styling->add_callback_styles( 'li-flipback', array( 'item_border_flip', 'item_border_radius', 'item_box_shadow_flip' ) );
			$element_styling->add_callback_styles( 'li-content', array( 'item_border_tooltip', 'item_border_radius_tooltip', 'item_box_shadow_tooltip' ) );


			//	.avia-icon-grid-container needed to override global settings for all elements
			$selectors = array(
						'container-li-wrapper'		=> ".avia-icon-grid-container .avia-icongrid-wrapper.{$element_id}",
						'li-article'				=> ".avia-icon-grid-container .avia-icongrid-wrapper.{$element_id} .article-icon-entry",
						'li-front'					=> ".avia-icon-grid-container .avia-icongrid-wrapper.{$element_id} .avia-icongrid-front",
						'li-front-icon'				=> ".avia-icon-grid-container .avia-icongrid-wrapper.{$element_id} .avia-icongrid-icon",
						'li-front-title'			=> ".avia-icon-grid-container .avia-icongrid-wrapper.{$element_id} .icongrid_title",
						'li-front-subtitle'			=> ".avia-icon-grid-container .avia-icongrid-wrapper.{$element_id} .icongrid_subtitle",
						'li-content'				=> ".avia-icon-grid-container .avia-icongrid-wrapper.{$element_id} .avia-icongrid-content",
						'li-flipback'				=> ".avia-icon-grid-container .avia-icongrid-wrapper.{$element_id} .avia-icongrid-flipback",
						'li-content-text'			=> ".avia-icon-grid-container .avia-icongrid-wrapper.{$element_id} .avia-icongrid-content .avia-icongrid-text",
						'li-flipback-text'			=> ".avia-icon-grid-container .avia-icongrid-wrapper.{$element_id} .avia-icongrid-flipback .avia-icongrid-text",
					);

			$element_styling->add_selectors( $selectors );

			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['element_styling'] = $element_styling;
			$result['meta'] = $meta;

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
			global $avia_config;

			$result = $this->get_element_styles( compact( array( 'atts', 'content', 'shortcodename', 'meta' ) ) );

			extract( $result );
			extract( $atts );

			//	fullwidth elements in text area will break layout - set a flag to check in ShortcodeHelper::is_top_level()
			$avia_config['icongrid_container'] = 'icongrid';

			$item_html = ShortcodeHelper::avia_remove_autop( $content, true );

			unset( $avia_config['icongrid_container'] );

			$style_tag = $element_styling->get_style_tag( $element_id );
			$item_tag = $element_styling->style_tag_html( $this->subitem_inline_styles, 'sub-' . $element_id );
			$container_class = $element_styling->get_class_string( 'container' );
			$container_ul_class = $element_styling->get_class_string( 'container-ul' );

			$output	 = '';
			$output .= $style_tag;
			$output .= $item_tag;
			$output .= "<div {$meta['custom_el_id']} class='{$container_class}'>";
			$output .=		"<ul id='avia-icongrid-" . uniqid() . "' class='{$container_ul_class}'>";
			$output .=			$item_html;
			$output .=		'</ul>';
			$output .= '</div>';

			$this->in_sc_exec = false;
			$this->subitem_inline_styles = '';

			return $output;
		}

		/**
		 * Shortcode Handler
		 *
		 * @param array $atts
		 * @param string $content
		 * @param string $shortcodename
		 * @return string
		 */
		public function av_icongrid_item( $atts, $content = '', $shortcodename = '' )
		{
			/**
			 * Fixes a problem when 3-rd party plugins call nested shortcodes without executing main shortcode  (like YOAST in wpseo-filter-shortcodes)
			 */
			if( empty( $this->in_sc_exec ) )
			{
				return '';
			}

			$result = $this->get_element_styles_item( compact( array( 'atts', 'content', 'shortcodename' ) ) );

			extract( $result );

			$avia_icongrid_wrapper = array(
										'start'	=> 'div',
										'end'	=> 'div'
									);

			if( ! empty( $atts['link'] ) )
			{
				$atts['link'] = AviaHelper::get_url( $atts['link'] );

				if( ! empty( $atts['link'] ) )
				{
					$linktitle = $atts['title'];
					$blank = AviaHelper::get_link_target( $atts['linktarget'] );

					$avia_icongrid_wrapper['start'] = "a href='{$atts['link']}' title='" . esc_attr( $linktitle ) . "' {$blank}";
					$avia_icongrid_wrapper['end'] = 'a';
				}
			}

			$display_char = av_icon( $atts['icon'], $atts['font'] );
			$title_el = ! empty( $meta['heading_tag'] ) ? $meta['heading_tag'] : 'h4';
			$title_el_cls = ! empty( $meta['heading_class'] ) ? $meta['heading_class'] : '';
			$contentClass = '' == trim( $content ) ? 'av-icongrid-empty' : '';

			//	create additional classes that might be necessary or styling grid with borders
			$this->item_count ++;
			$this->row_item ++;

			if( $this->row_item > $this->numrow )
			{
				$this->row_item = 1;
				$this->row_count ++;
			}

			$item_classes = array(
						"av-row-with-{$this->numrow}-cells",
						"av-row-nr-{$this->row_count}",
						"av-cell-{$this->row_item}"
					);

			$item_classes[] = ( $this->item_count <= $this->numrow ) ? 'av-first-cell-row' : 'av-next-cell-row';

			if( 1 == $this->numrow )
			{
				$item_classes[] = 'av-one-cell-item';
			}
			else
			{
				if( 1 == $this->row_item )
				{
					$item_classes[] = 'av-first-cell-item';
				}
				else if( $this->numrow == $this->row_item )
				{
					$item_classes[] = 'av-last-cell-item';
				}
				else
				{
					$item_classes[] = 'av-inner-cell-item';
				}
			}

			$element_styling->add_classes( 'li-item', $item_classes );

			$markup = avia_markup_helper( array( 'context' => 'entry_title', 'echo' => false, 'custom_markup' => $atts['custom_markup'] ) );
			$markup_article = avia_markup_helper( array('context' => 'entry', 'echo' => false, 'custom_markup' => $atts['custom_markup'] ) );
			$markup_sub = avia_markup_helper( array( 'context' => 'entry_subtitle', 'echo' => false, 'custom_markup' => $atts['custom_markup'] ) );
			$markup_text  = avia_markup_helper( array( 'context' => 'entry_content', 'echo' => false, 'custom_markup' => $atts['custom_markup'] ) );

			/**
			 * @since 4.8.8
			 * @param string $heading_tag
			 * @param array $atts
			 * @param string $content
			 * @return string
			 */
			$subtitle_el = apply_filters( 'avf_sc_icongrid_subtitle_heading_tag', 'h6', $atts, $content );


			$container_class = $element_styling->get_class_string( 'container-li-wrapper' );
			$item_class = $element_styling->get_class_string( 'li-item' );
			$this->subitem_inline_styles .= $element_styling->get_style_tag( $element_id, 'rules_only' );

			$output  = '';
			$output .= "<li class='{$item_class}'>";
			$output .=	"<{$avia_icongrid_wrapper['start']} class='{$container_class}'>";
			$output .=		"<article class='article-icon-entry {$contentClass}' {$markup_article}>";
			$output .=			'<div class="avia-icongrid-front">';
			$output .=				'<div class="avia-icongrid-inner">';
			$output .=					"<div class='avia-icongrid-icon avia-font-{$atts['font']}'>";
			$output .=						"<span class='icongrid-char' {$display_char}></span>";
			$output .=					'</div>';
			$output .=					'<header class="entry-content-header">';

			if( ! empty( $atts['title'] ) )
			{
				$output .=					"<{$title_el} class='av_icongrid_title icongrid_title {$title_el_cls}' {$markup}>" . esc_html( $atts['title'] ). "</{$title_el}>";
			}
			if( ! empty( $atts['subtitle'] ) )
			{
				$output .=					"<{$subtitle_el} class='av_icongrid_subtitle icongrid_subtitle' {$markup_sub}>" . esc_html( $atts['subtitle'] ) . "</{$subtitle_el}>";
			}

			$output .=					'</header>';
			$output .=				'</div>';
			$output .=			'</div>';
			$output .=			'<div class="avia-icongrid-content">';
			$output .=				'<div class="avia-icongrid-inner">';
			$output .=					"<div class='avia-icongrid-text' {$markup_text}>";
			$output .=						ShortcodeHelper::avia_apply_autop( ShortcodeHelper::avia_remove_autop( $content ) );
			$output .=					'</div>';
			$output .=				'</div>';
			$output .=			'</div>';
			$output .=		'</article>';
			$output .=	"</{$avia_icongrid_wrapper['end']}>";
			$output .= '</li>';

			return $output;
		}
	}

}
