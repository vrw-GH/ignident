<?php
/**
 * Icon Grid / Flipbox Grid Shortcode
 *
 * @author tinabillinger
 * @since 4.5
 * @since 5.1.2			complete restructured and redesigned
 *
 * Creates an icon grid with toolips or flip content
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

/**
 * Fallback to old icon grid for sites that need the old function and stylings
 *
 * @since 5.1.2
 * @param boolean $fallback
 * @return boolean					true for fallback
 */
if( true === apply_filters( 'avf_fallback_avia_sc_icongrid', false ) )
{
	require_once( 'v50/icongrid.php' );

	return;
}

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

			$this->config['name']			= __( 'Icon/Flipbox Grid', 'avia_framework' );
			$this->config['tab']			= __( 'Content Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-icongrid.png';
			$this->config['order']			= 90;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode']		= 'av_icongrid';
			$this->config['shortcode_nested'] = array( 'av_icongrid_item' );
			$this->config['tooltip']		= __( 'Creates a grid with optional icon, text and background images for tooltips or flipbox', 'avia_framework' );
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
			$ver = Avia_Builder()->get_theme_version();
			$min_js = avia_minify_extension( 'js' );
			$min_css = avia_minify_extension( 'css' );

			wp_enqueue_style( 'avia-module-icon', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/icon/icon{$min_css}.css", array( 'avia-layout' ), $ver );
			wp_enqueue_style( 'avia-module-icongrid', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/icongrid/icongrid{$min_css}.css", array( 'avia-layout' ), $ver );

			wp_enqueue_script( 'avia-module-icongrid', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/icongrid/icongrid{$min_js}.js", array( 'avia-shortcodes' ), $ver, true );
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

			$desc  = __( 'Select the appearance of the icon grid.', 'avia_framework' ) . '<br />';
			$desc .= '<strong>' . __( 'Tooltip will always be in a square shape.', 'avia_framework' ) . '</strong>';

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
														'icon'		=> 'ue803',
														'content'	=> __( 'Enter content here', 'avia_framework' ),
													),
													array(
														'title'		=> __( 'Grid Title 2', 'avia_framework' ),
														'icon'		=> 'ue807',
														'content'	=> __( 'Enter content here', 'avia_framework' ),
													),
													array(
														'title'		=> __( 'Grid Title 3', 'avia_framework' ),
														'icon'		=> 'ue80a',
														'content'	=> __( 'Enter content here', 'avia_framework' ),
													),
												),
							'subelements' 	=> $this->create_modal()
						),

						array(
							'name'		=> __( 'Content Appearance', 'avia_framework' ),
							'desc'		=> $desc,
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
							'name'		=> __( 'Mobile Close Behaviour', 'avia_framework' ),
							'desc'		=> __( 'Select the behaviour of an open flipbox or tooltip on mobile devices and touch screens', 'avia_framework' ),
							'id'		=> 'flipbox_force_close',
							'type'		=> 'select',
							'std'		=> 'flipbox',
							'lockable'	=> true,
//							'required'	=> array( 'icongrid_styling', 'not', 'tooltip' ),
							'subtype'	=> array(
												__( 'Close only when visitor clicks in icongrid', 'avia_framework' )		=> '',
												__( 'Also close when visitor clicks outside icongrid', 'avia_framework' )	=> 'avia_flip_force_close',
											)
						),
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_elements' ), $c );

			/**
			 * Styling Tab
			 * ============
			 */

			$desc  = __( 'Select how the cells are aligned in the container and the distance between the cells and horizontal borders is assigned. Multiple lines are supported.', 'avia_framework' ) . '<br />';
			$desc .= '<a href="https://css-tricks.com/snippets/css/a-guide-to-flexbox/#aa-justify-content" target="_blank" rel="noopener noreferrer">' . __( 'Based on the CSS Flexible Box Layout Model', 'avia_framework' ) . '</a>';

			$c = array(
						array(
							'name'		=> __( 'Row Cells', 'avia_framework' ),
							'desc'		=> __( 'Select the number of cells in a row. Each cell will contain 1 item and additional rows will be added to show all items. Consider the container size (e.g. column size) and the amount of text.', 'avia_framework' ),
							'id'		=> 'icongrid_numrow',
							'type'		=> 'select',
							'std'		=> '3',
							'lockable'	=> true,
							'subtype'	=> array(
												__( '1 cell', 'avia_framework' )	=> '1',
												__( '2 cells', 'avia_framework' )	=> '2',
												__( '3 cells', 'avia_framework' )	=> '3',
												__( '4 cells', 'avia_framework' )	=> '4',
												__( '5 cells', 'avia_framework' )	=> '5',
											)
						),

						array(
							'name'		=> __( 'Cell Alignment', 'avia_framework' ),
							'desc'		=> $desc,
							'id'		=> 'cell_alignment',
							'type'		=> 'select',
							'std'		=> 'space-between',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'No distance to horizontal borders (= space-between)', 'avia_framework' )						=> 'space-between',
												__( 'Half distance to horizontal borders (= space-around)', 'avia_framework' )						=> 'space-around',
												__( 'Full distance to horizontal borders (= space-evenly)', 'avia_framework' )						=> 'space-evenly',
												__( 'Full distance to horizontal borders, centered without distance (= center)', 'avia_framework' )	=> 'center'
											)
						),

						array(
							'name'		=> __( 'Distance Between Cells In Row', 'avia_framework' ),
							'desc'		=> __( 'Select the distance between the cells in a row.', 'avia_framework' ),
							'id'		=> 'cell_distance',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> AviaHtmlHelper::number_array( 1, 500, 1, array( __( 'Use Default (= 0)', 'avia_framework' ) => '' ), 'px' )
						),

						array(
							'name'		=> __( 'Distance Between Rows', 'avia_framework' ),
							'desc'		=> __( 'Select the distance between the rows.', 'avia_framework' ),
							'id'		=> 'row_distance',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> AviaHtmlHelper::number_array( 1, 500, 1, array( __( 'Use Default (= 0)', 'avia_framework' ) => '' ), 'px' )
						),

						array(
							'name'		=> __( 'Minimum Height - For Flipbox Only', 'avia_framework' ),
							'desc'		=> __( 'Select the minimum height of the cells.', 'avia_framework' ),
							'id'		=> 'min_height',
							'type'		=> 'select',
							'std'		=> '',
							'required'	=> array( 'icongrid_styling', 'not', 'tooltip' ),
							'lockable'	=> true,
							'subtype'	=> AviaHtmlHelper::number_array( 1, 500, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' )
						)

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

			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'padding',
							'name'			=> __( 'Items Content Padding', 'avia_framework' ),
							'desc'			=> __( 'Set the padding for the content of a grid item. Both pixel and &percnt; based values are accepted. eg: 30px, 5&percnt;. Leave empty to use theme default.', 'avia_framework' ),
							'id'			=> 'items_padding',
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
												__( 'To 50% at a screen width of 767px or lower', 'avia_framework' )				=> 'av-50-break-767',
											)
						),

						array(
								'name'		=> __( 'Reverse Order Of Items On Mobile', 'avia_framework' ),
								'desc'		=> __( 'Check if you want to reverse the order of items when switching to mobile.', 'avia_framework' ),
								'id'		=> 'inverse_mobile',
								'type'		=> 'checkbox',
								'std'		=> '',
								'required'	=> array( 'mobile', 'not', 'av-fixed-cells' ),
								'lockable'	=> true
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
													$this->popup_key( 'modal_styling_background_images' ),
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
							'name'		=> __( 'Grid Item Title', 'avia_framework' ),
							'desc'		=> __( 'Enter the grid item title here - better keep it short', 'avia_framework' ),
							'id'		=> 'title',
							'type'		=> 'input',
							'std'		=> 'Grid Title',
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Grid Item Sub-Title', 'avia_framework' ),
							'desc'		=> __( 'Enter the grid item sub-title here - better keep it short', 'avia_framework' ),
							'id'		=> 'subtitle',
							'type'		=> 'textarea',
							'std'		=> 'Grid Sub-Title',
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Grid Item Icon', 'avia_framework' ),
							'desc'		=> __( 'Select if you want to display an icon', 'avia_framework' ),
							'id'		=> 'show_icon',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
											__( 'Show icon above title', 'avia_framework' )	=> '',
											__( 'Do not show an icon', 'avia_framework' )	=> 'no'
										)
						),

						array(
							'name'		=> __( 'Grid Item Icon', 'avia_framework' ),
							'desc'		=> __( 'Select an icon for your grid item below', 'avia_framework' ),
							'id'		=> 'icon',
							'type'		=> 'iconfont',
							'std'		=> '',
							'lockable'	=> true,
							'locked'	=> array( 'icon', 'font' ),
							'required'	=> array( 'show_icon', 'not', 'no' )
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


			$desc  = __( 'Enter some content here. Will be used as backside of flipbox or tooltip popup.', 'avia_framework' ) . '<br />';
			$desc .= '<strong>' . __( 'Better keep it short for tooltip due to square layout and check responsive behaviour.', 'avia_framework' ) . '</strong>';

			$c = array(
						array(
							'name'		=> __( 'Grid Item Content', 'avia_framework' ),
							'desc'		=> $desc,
							'id'		=> 'content',
							'type'		=> 'tiny_mce',
							'std'		=> __( 'Grid Content goes here', 'avia_framework' ),
							'lockable'	=> true
						),

						array(
								'name'		=> __( 'Inverse Layout - Ignored For Tooltip', 'avia_framework' ),
								'desc'		=> __( 'Check if you want to change front and backside. This will show the backside initially.', 'avia_framework' ),
								'id'		=> 'inverse_flip',
								'type'		=> 'checkbox',
								'std'		=> '',
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

			$desc  = __( 'Either upload a new, or choose an existing image from your media library.', 'avia_framework' ) . '<br /><br />';
			$desc .= '<strong>' . __( 'Depending on your settings for the background options a previous selected background color might not be visible because the image covers the complete background.', 'avia_framework' ) . '</strong>';

			$c = array(

						array(
							'name'			=> __( 'Background Image For Front Side', 'avia_framework' ),
							'desc'			=> $desc,
							'id'			=> 'front_bg_image',
							'type'			=> 'image',
							'fetch'			=> 'id',
//							'secondary_img'	=> true,
							'force_id_fetch' => true,
							'title'			=>  __( 'Insert Image', 'avia_framework' ),
							'button'		=> __( 'Insert', 'avia_framework' ),
							'std'			=> ''
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'image_size_select',
							'id'			=> 'front_image_size',
							'std'			=> 'no scaling',
							'show_std'		=> false,
							'required'		=> array( 'front_bg_image', 'not', '' ),
							'lockable'		=> true
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'background_image_position',
							'args'			=> array(
													'id_pos'		=> 'front_background_position',
													'id_repeat'		=> 'front_background_repeat'
												),
							'required'		=> array( 'front_bg_image', 'not', '' ),
							'lockable'		=> true
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'filter_blur',
							'id'			=> 'front_filter',
							'name'			=> __( 'Smoothen Front Background Image', 'avia_framework' ),
							'required'		=> array( 'front_bg_image', 'not', '' ),
							'lockable'		=> true
						),

						array(
							'name'			=> __( 'Background Image For Backside Flip Box - IGNORED When &quot;Content Appears In Tooltip&quot; Selected', 'avia_framework' ),
							'desc'			=> $desc,
							'id'			=> 'back_bg_image',
							'type'			=> 'image',
							'fetch'			=> 'id',
							'secondary_img'	=> true,
							'force_id_fetch' => true,
							'title'			=>  __( 'Insert Image', 'avia_framework' ),
							'button'		=> __( 'Insert', 'avia_framework' ),
							'std'			=> ''
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'image_size_select',
							'id'			=> 'back_image_size',
							'std'			=> 'no scaling',
							'show_std'		=> false,
							'required'		=> array( 'back_bg_image', 'not', '' ),
							'lockable'		=> true
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'background_image_position',
							'args'			=> array(
													'id_pos'		=> 'back_background_position',
													'id_repeat'		=> 'back_background_repeat'
												),
							'required'		=> array( 'back_bg_image', 'not', '' ),
							'lockable'		=> true
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'filter_blur',
							'id'			=> 'back_filter',
							'name'			=> __( 'Smoothen Backside Background Image', 'avia_framework' ),
							'required'		=> array( 'back_bg_image', 'not', '' ),
							'lockable'		=> true
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Background Images', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_styling_background_images' ), $template );



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
							'name'			=> __( 'Link Grid Item', 'avia_framework' ),
							'desc'			=> __( 'Apply a link to the grid item. On touch screens this will show the animation and then follow the link when touched again.', 'avia_framework' ),
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
						'items_padding'						=> '',
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
						'icongrid_numrow'					=> 3,
						'min_height'						=> '',
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

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			if( empty( $atts['icongrid_numrow'] ) || ! is_numeric( $atts['icongrid_numrow'] ) )
			{
				$atts['icongrid_numrow'] = 3;
			}

			if( $atts['icongrid_numrow'] < 1 || $atts['icongrid_numrow'] > 5 )
			{
				$atts['icongrid_numrow'] = 3;
			}

			if( empty( $atts['cell_alignment'] ) )
			{
				$atts['cell_alignment'] = 'space-between';
			}

			$this->in_sc_exec = true;
			$this->numrow = $atts['icongrid_numrow'];
			$this->row_count = 1;
			$this->item_count = 0;
			$this->row_item = 0;

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

			if( $atts['min_height'] != '' )
			{
				$element_styling->add_data_attributes( 'container', array( 'min-height' => $atts['min_height'] ) );
			}

			$classes = array(
							'avia-icongrid',
							'clearfix',
							'avia_animate_when_almost_visible',
							"avia-icongrid-{$atts['icongrid_styling']}",
							$atts['flipbox_force_close'],
							"avia-icongrid-numrow-{$atts['icongrid_numrow']}",
							"avia-cell-{$atts['cell_alignment']}"
					);

			if( 'av-fixed-cells' != $atts['mobile'] )
			{
				$classes[] = 'av-flex-cells';
				$classes[] = ! empty( $atts['mobile_breaking'] ) ? $atts['mobile_breaking'] : 'av-break-767';
				if( $atts['icongrid_numrow'] > 2 )
				{
					$classes[] = 'av-can-break-50';
				}
			}
			else
			{
				$classes[] = $atts['mobile'];
			}

			$element_styling->add_classes( 'container-ul', $classes );


			$element_styling->add_styles( 'container-ul', array( 'justify-content' => $atts['cell_alignment'] ) );

			if( '' != $atts['row_distance'] )
			{
				$element_styling->add_styles( 'container-ul', array( 'row-gap' => $atts['row_distance'] . 'px' ) );
			}

			if( '' != $atts['cell_distance'] && is_numeric( $atts['cell_distance'] ) )
			{
				$distance = (int) ( $atts['cell_distance'] );
				$space = 0;
				$space2 = 0;

				switch( $atts['cell_alignment'] )
				{
					case 'space-around':
						$space = $distance;
						$space2 = $distance;
						break;
					case 'space-evenly':
						$space = ( ( $atts['icongrid_numrow'] + 1 ) / $atts['icongrid_numrow'] ) * $distance;
						$space2 = 1.5 * $distance;
						break;
					case 'center':
						$space = ( 2.0 / $atts['icongrid_numrow'] ) * $distance;
						$space2 = $distance;
						break;
					case 'space-between':
					default:
						$space = ( ( $atts['icongrid_numrow'] - 1 ) / $atts['icongrid_numrow'] ) * $distance;
						$space2 = 0.5 * $distance;
						break;
				}

				switch( $atts['icongrid_numrow'] )
				{
					case 2:
						$rule = "calc(50% - {$space}px)";
						break;
					case 3:
						$rule = "calc(33.33% - {$space}px)";
						break;
					case 4:
						$rule = "calc(25% - {$space}px)";
						break;
					case 5:
						$rule = "calc(20% - {$space}px)";
						break;
					default:
						$rule = '';
				}

				$element_styling->add_styles( 'element-li', array( 'flex-basis'	=> $rule ) );


				$element_styling->add_media_queries( 'media-li-989', array( 'screen' => array( '768;989' => array( 'flex' => "0 1 calc(50% - {$space2}px)" ) ) ) );
				$element_styling->add_media_queries( 'media-li-767', array( 'screen' => array( '0;767' => array( 'flex' => "0 1 calc(50% - {$space2}px)" ) ) ) );
			}

			if( $atts['mobile'] != 'av-fixed-cells' && ! empty( $atts['inverse_mobile'] ) )
			{
				switch( $atts['mobile_breaking'] )
				{
					case 'av-50-break-989':
						$element_styling->add_media_queries( 'container-ul', array( 'screen' => array( '768;989' => array( 'flex-flow' => 'row-reverse wrap' ) ) ) );
						$element_styling->add_media_queries( 'container-ul', array( 'screen' => array( '0;767' => array( 'flex-flow' => 'column-reverse wrap' ) ) ) );
						break;
					case 'av-50-break-767':
						$element_styling->add_media_queries( 'container-ul', array( 'screen' => array( '0;767' => array( 'flex-flow' => 'row-reverse wrap' ) ) ) );
						break;
					case 'av-break-989':
						$element_styling->add_media_queries( 'container-ul', array( 'screen' => array( '0;989' => array( 'flex-flow' => 'column-reverse wrap' ) ) ) );
						break;
					case '':
						$element_styling->add_media_queries( 'container-ul', array( 'screen' => array( '0;767' => array( 'flex-flow' => 'column-reverse wrap' ) ) ) );
						break;
				}
			}

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

			if( $atts['items_padding'] != '' )
			{
				$element_styling->add_callback_styles( 'li-front-inner', array( 'items_padding' ) );
				$element_styling->add_callback_styles( 'li-content-inner', array( 'items_padding' ) );
				$element_styling->add_callback_styles( 'li-flipback-inner', array( 'items_padding' ) );
			}

			if( 'custom' == $atts['font_color'] )
			{
				$element_styling->add_styles( 'li-front-icon', array( 'color' => $atts['custom_icon'] ) );
				$element_styling->add_styles( 'li-front-title', array( 'color' => $atts['custom_title'] ) );
				$element_styling->add_styles( 'li-front-subtitle', array( 'color' => $atts['custom_subtitle'] ) );
				$element_styling->add_styles( 'li-content-text', array( 'color' => $atts['custom_content'] ) );
				$element_styling->add_styles( 'li-flipback-text', array( 'color' => $atts['custom_content'] ) );
			}

			if( 'tooltip' == $atts['icongrid_styling'] )
			{
				$element_styling->add_callback_styles( 'li-article', array( 'border_front', 'border_radius', 'box_shadow' ) );
				$element_styling->add_callback_styles( 'li-tooltip-bg-img', array( 'border_radius' ) );
			}
			else
			{
				$element_styling->add_callback_styles( 'li-front', array( 'border_front', 'border_radius', 'box_shadow' ) );
				$element_styling->add_callback_styles( 'li-front-bg-img', array( 'border_radius' ) );
			}

			$element_styling->add_callback_styles( 'li-flipback', array( 'border_flip', 'border_radius', 'box_shadow_flip' ) );
			$element_styling->add_callback_styles( 'li-flipback-bg-img', array( 'border_radius' ) );

			$element_styling->add_callback_styles( 'li-content', array( 'border_tooltip', 'border_radius_tooltip', 'box_shadow_tooltip' ) );

			$selectors = array(
						'container'				=> ".avia-icon-grid-container.{$element_id}",
						'container-ul'			=> ".avia-icon-grid-container.{$element_id} .avia-icongrid",
						'container-li'			=> ".avia-icon-grid-container.{$element_id} .avia-icongrid-wrapper",
						'element-li'			=> ".avia-icon-grid-container.{$element_id} li.av-icon-cell-item",
						'li-article'			=> ".avia-icon-grid-container.{$element_id} .avia-icongrid-wrapper .article-icon-entry",
						'li-front'				=> ".avia-icon-grid-container.{$element_id} .avia-icongrid-wrapper .avia-icongrid-front",
						'li-front-inner'		=> ".avia-icon-grid-container.{$element_id} .avia-icongrid-wrapper .avia-icongrid-front .avia-icongrid-inner",
						'li-front-icon'			=> ".avia-icon-grid-container.{$element_id} .avia-icongrid-wrapper .avia-icongrid-icon",
						'li-front-title'		=> ".avia-icon-grid-container.{$element_id} .avia-icongrid-wrapper .icongrid_title",
						'li-front-subtitle'		=> ".avia-icon-grid-container.{$element_id} .avia-icongrid-wrapper .icongrid_subtitle",
						'li-content'			=> ".avia-icon-grid-container.{$element_id} .avia-icongrid-wrapper .avia-icongrid-content",
						'li-content-inner'		=> ".avia-icon-grid-container.{$element_id} .avia-icongrid-wrapper .avia-icongrid-content .avia-icongrid-inner",
						'li-content-text'		=> ".avia-icon-grid-container.{$element_id} .avia-icongrid-wrapper .avia-icongrid-content .avia-icongrid-text",
						'li-flipback'			=> ".avia-icon-grid-container.{$element_id} .avia-icongrid-wrapper .avia-icongrid-flipback",
						'li-flipback-inner'		=> ".avia-icon-grid-container.{$element_id} .avia-icongrid-wrapper .avia-icongrid-flipback .avia-icongrid-inner",
						'li-flipback-text'		=> ".avia-icon-grid-container.{$element_id} .avia-icongrid-wrapper .avia-icongrid-flipback .avia-icongrid-text",

						'li-front-bg-img'		=> ".avia-icon-grid-container.{$element_id} .avia-icongrid-wrapper .avia-icongrid-front.bg-img:before",
						'li-tooltip-bg-img'		=> ".avia-icon-grid-container.{$element_id} .avia-icongrid-tooltip .avia-icongrid-wrapper .article-icon-entry.bg-img:before",
						'li-flipback-bg-img'	=> ".avia-icon-grid-container.{$element_id} .avia-icongrid-wrapper .avia-icongrid-flipback.bg-img:before",

						'media-li-989'			=> "#top .avia-icon-grid-container.{$element_id} .avia-icongrid.av-flex-cells.av-can-break-50.av-50-break-989 li",
						'media-li-767'			=> "#top .avia-icon-grid-container.{$element_id} .avia-icongrid.av-flex-cells.av-can-break-50.av-50-break-767 li",
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
							'front_bg_image'					=> '',
							'front_image_size'					=> 'no scaling',
							'back_bg_image'						=> '',
							'back_image_size'					=> 'no scaling',
							'font'								=> '',
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

			if( 'no scaling' == $atts['front_image_size'] )
			{
				$atts['front_image_size'] = '';
			}

			if( 'no scaling' == $atts['back_image_size'] )
			{
				$atts['back_image_size'] = '';
			}


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

			$this->set_background_image( 'front', $atts, $element_styling );
			$this->set_background_image( 'back', $atts, $element_styling );

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
				$element_styling->add_callback_styles( 'li-tooltip-bg-img', array( 'item_border_radius' ) );
			}
			else
			{
				$element_styling->add_callback_styles( 'li-front', array( 'item_border_front', 'item_border_radius', 'item_box_shadow' ) );
				$element_styling->add_callback_styles( 'li-front-bg-img', array( 'item_border_radius' ) );
			}

			$element_styling->add_callback_styles( 'li-flipback', array( 'item_border_flip', 'item_border_radius', 'item_box_shadow_flip' ) );
			$element_styling->add_callback_styles( 'li-flipback-bg-img', array( 'item_border_radius' ) );

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

						'li-front-bg-img'			=> ".avia-icon-grid-container .avia-icongrid-flipbox .avia-icongrid-wrapper.{$element_id} .avia-icongrid-front.bg-img:before",
						'li-tooltip-bg-img'			=> ".avia-icon-grid-container .avia-icongrid-tooltip .avia-icongrid-wrapper.{$element_id} .article-icon-entry.bg-img:before",
						'li-flipback-bg-img'		=> ".avia-icon-grid-container .avia-icongrid-flipbox .avia-icongrid-wrapper.{$element_id} .avia-icongrid-flipback.bg-img:before",
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
			$container_data = $element_styling->get_data_attributes_string( 'container' );
			$container_ul_class = $element_styling->get_class_string( 'container-ul' );

			$output	 = '';
			$output .= $style_tag;
			$output .= $item_tag;
			$output .= "<div {$meta['custom_el_id']} class='{$container_class}' {$container_data}>";
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

			if( '' == trim( $content ) )
			{
				$element_styling->add_classes( 'li-article', 'av-icongrid-empty' );
			}

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
			$item_classes[] = ! empty( $atts['inverse_flip'] ) ? 'invert-flip' : '';

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
			$article_class = $element_styling->get_class_string( 'li-article' );
			$item_class = $element_styling->get_class_string( 'li-item' );
			$front_class = $element_styling->get_class_string( 'li-front' );
			$flipback_class = $element_styling->get_class_string( 'li-flipback' );
			$this->subitem_inline_styles .= $element_styling->get_style_tag( $element_id, 'rules_only' );

			$output  = '';
			$output .= "<li class='av-icon-cell-item {$item_class}'>";
			$output .=	"<{$avia_icongrid_wrapper['start']} class='{$container_class}'>";
			$output .=		"<article class='article-icon-entry {$article_class}' {$markup_article}>";
			$output .=			"<div class='avia-icongrid-front {$front_class}'>";
			$output .=				'<div class="avia-icongrid-inner">';

			if( '' == $atts['show_icon'] )
			{
				$output .=				"<div class='avia-icongrid-icon avia-font-{$atts['font']}'>";
				$output .=					"<span class='icongrid-char' {$display_char}></span>";
				$output .=				'</div>';
			}

			$output .=					'<header class="entry-content-header">';

			if( ! empty( $atts['title'] ) )
			{
				$output .=					"<{$title_el} class='av_icongrid_title icongrid_title {$title_el_cls}' {$markup}>" . esc_html( $atts['title'] ). "</{$title_el}>";
			}
			if( ! empty( $atts['subtitle'] ) )
			{
				$output .=					"<{$subtitle_el} class='av_icongrid_subtitle icongrid_subtitle' {$markup_sub}>" . ShortcodeHelper::avia_apply_autop( ShortcodeHelper::avia_remove_autop( $atts['subtitle'] ) ) . "</{$subtitle_el}>";
			}

			$output .=					'</header>';
			$output .=				'</div>';
			$output .=			'</div>';

			if( 'tooltip' == $this->parent_atts['icongrid_styling'] )
			{
				$output .=		"<div class='avia-icongrid-content' data-class-flipback='{$flipback_class}'>";
				$output .=			'<div class="avia-icongrid-inner">';
				$output .=				"<div class='avia-icongrid-text' {$markup_text}>";
				$output .=					ShortcodeHelper::avia_apply_autop( ShortcodeHelper::avia_remove_autop( $content ) );
				$output .=				'</div>';
				$output .=			'</div>';
				$output .=		'</div>';
			}
			else
			{
				$output .=		"<div class='avia-icongrid-flipback {$flipback_class}'>";
				$output .=			'<div class="avia-icongrid-inner">';
				$output .=				"<div class='avia-icongrid-text' {$markup_text}>";
				$output .=					ShortcodeHelper::avia_apply_autop( ShortcodeHelper::avia_remove_autop( $content ) );
				$output .=				'</div>';
				$output .=			'</div>';
				$output .=		'</div>';
			}

			$output .=		'</article>';
			$output .=	"</{$avia_icongrid_wrapper['end']}>";
			$output .= '</li>';

			return $output;
		}

		/**
		 *
		 * @since 5.1.2
		 * @param string $which
		 * @param array $atts
		 * @param aviaElementStyling $element_styling
		 * @return boolean
		 */
		protected function set_background_image( $which, array $atts, aviaElementStyling &$element_styling )
		{
			switch( $which )
			{
				case 'back':
					$id = 'back_bg_image';
					$size = 'back_image_size';
					$position = 'back_background_position';
					$repeat = 'back_background_repeat';
					$filter = 'back_filter';
					$selector = 'li-flipback';
					$selector_img = 'li-flipback-bg-img';
					break;
				case 'front':
				default:
					$id = 'front_bg_image';
					$size = 'front_image_size';
					$position = 'front_background_position';
					$repeat = 'front_background_repeat';
					$filter = 'front_filter';
					$selector = 'li-front';
					$selector_img = 'tooltip' == $this->parent_atts['icongrid_styling'] ? 'li-tooltip-bg-img' : 'li-front-bg-img';
					break;
			}

			if( empty( $atts[ $id ] ) || ! is_numeric( $atts[ $id ] ) )
			{
				return false;
			}

			if( 'no scaling' == $atts[ $size ] )
			{
				$atts[ $size ] = 'full';
			}

			$src = wp_get_attachment_image_src( $atts[ $id ], $atts[ $size ] );

			if( empty( $src[0] ) )
			{
				return false;
			}

			if( 'tooltip' == $this->parent_atts['icongrid_styling'] )
			{
				if( 'li-tooltip-bg-img' == $selector_img )
				{
					$element_styling->add_classes( 'li-article', 'bg-img' );
				}
			}
			else
			{
				$element_styling->add_classes( $selector, 'bg-img' );
			}

			$bg_style = array();

			if( 'stretch' == $atts[ $repeat ] )
			{
				$bg_style['background-size'] = 'cover';
//				$element_styling->add_classes( $selector, 'avia-full-stretch' );
				$atts[ $repeat ] = 'no-repeat';
			}

			if( 'contain' == $atts[ $repeat ] )
			{
//				$element_styling->add_classes( $selector, 'avia-full-contain' );
				$bg_style['background-size'] = 'contain';
				$atts[ $repeat ] = 'no-repeat';
			}

			$bg_pos = $element_styling->background_position_string( $atts[ $position ] );
			$bg_image = "url({$src[0]}) {$bg_pos} {$atts[ $repeat ]} scroll";

			$element_styling->add_styles( $selector_img, array( 'background' => $bg_image ) );

			if( ! empty( $bg_style ) )
			{
				$element_styling->add_styles( $selector_img, $bg_style );
			}

			$element_styling->add_callback_styles( $selector_img, array( $filter ) );

			return true;
		}
	}

}
