<?php
/**
 * COLUMNS
 *
 * Shortcode which creates columns for better content separation
 *
 */

 // Don't load directly
if( ! defined( 'ABSPATH' ) ) { exit; }



if( ! class_exists( 'avia_sc_columns', false ) )
{
	class avia_sc_columns extends aviaShortcodeTemplate
	{
		/**
		 *
		 * @var string
		 */
		static protected $extraClass = '';

		/**
		 *
		 * @var int|float
		 */
		static protected $calculated_size = 0;

		/**
		 *
		 * @var array
		 */
		static protected $first_atts = array();

		/**
		 * Needed to create a unique ID for fold/unfold
		 *
		 * @since 5.6
		 * @var int
		 */
		static protected $counter = 0;

		/**
		 * @since 4.8.4
		 * @var string
		 */
		static protected $first = '';

		/**
		 *
		 * @var array
		 */
		static protected $size_array = array(
						'av_one_full' 		=> 1.0,
						'av_one_half' 		=> 0.5,
						'av_one_third' 		=> 0.33,
						'av_one_fourth' 	=> 0.25,
						'av_one_fifth' 		=> 0.2,
						'av_two_third' 		=> 0.66,
						'av_three_fourth' 	=> 0.75,
						'av_two_fifth' 		=> 0.4,
						'av_three_fifth' 	=> 0.6,
						'av_four_fifth' 	=> 0.8
					);

		/**
		 * Holds an array or shortcode => column name
		 *
		 * @since 4.8
		 * @var array
		 */
		static protected $columns_array = array(
						'av_one_full'		=> '1/1',
						'av_one_half'		=> '1/2',
						'av_one_third'		=> '1/3',
						'av_one_fourth'		=> '1/4',
						'av_one_fifth'		=> '1/5',
						'av_two_third'		=> '2/3',
						'av_three_fourth'	=> '3/4',
						'av_two_fifth'		=> '2/5',
						'av_three_fifth'	=> '3/5',
						'av_four_fifth'		=> '4/5'
					);

		/**
		 * Defines the smallest base column for a column (needed to calculate grid layout)
		 *
		 * @since 4.8.7.1
		 * @var array
		 */
		static protected $base_column_size = array(
						'av_one_full'		=> 1.0,
						'av_one_half'		=> 0.5,
						'av_one_third'		=> 0.33,
						'av_one_fourth'		=> 0.25,
						'av_one_fifth'		=> 0.2,
						'av_two_third'		=> 0.33,
						'av_three_fourth'	=> 0.25,
						'av_two_fifth'		=> 0.2,
						'av_three_fifth'	=> 0.2,
						'av_four_fifth'		=> 0.2
					);

		/**
		 * Defines the maximum number of columns (needed to calculate grid layout)
		 *
		 * @since 4.8.7.1
		 * @var array
		 */
		static protected $columns_count = array(
						'av_one_full'		=> 1,
						'av_one_half'		=> 1,
						'av_one_third'		=> 1,
						'av_one_fourth'		=> 1,
						'av_one_fifth'		=> 1,
						'av_two_third'		=> 2,
						'av_three_fourth'	=> 3,
						'av_two_fifth'		=> 2,
						'av_three_fifth'	=> 3,
						'av_four_fifth'		=> 4
					);

		/**
		 * This constructor is implicity called by all derived classes
		 * To avoid duplicating code we put this in the constructor.
		 *
		 * Attention: shortcode_insert_button() is called from base constructor
		 *
		 * @since 4.2.1
		 * @param AviaBuilder $builder
		 */
		public function __construct( $builder )
		{
			parent::__construct( $builder );

			$this->config['version']			= '1.0';
			$this->config['type']				= 'layout';
			$this->config['self_closing']		= 'no';
			$this->config['contains_content']	= 'yes';
			$this->config['contains_text']		= 'no';
			$this->config['first_in_row']		= 'first';
			$this->config['duplicate_template']	= avia_sc_columns::$columns_array;
		}


		/**
		 * Create the config array for the shortcode button
		 */
		protected function shortcode_insert_button()
		{
			$this->config['name']			= '1/1';
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-full.png';
			$this->config['tab']			= __( 'Layout Elements', 'avia_framework' );
			$this->config['order']			= 100;
			$this->config['target']			= 'avia-section-drop';
			$this->config['shortcode']		= 'av_one_full';
			$this->config['html_renderer']	= false;
			$this->config['tooltip']		= __( 'Creates a single full width column', 'avia_framework' );
			$this->config['drag-level']		= 2;
			$this->config['drop-level']		= 2;
			$this->config['tinyMCE']		= array(
													'instantInsert' => '[av_one_full first]Add Content here[/av_one_full]'
												);
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
			$this->config['aria_label']		= 'yes';

			$this->config['base_element']	= 'yes';
			$this->config['name_template']	= __( 'Column Template', 'avia_framework' ) . ' ' . $this->config['name'];
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
			$desc_column  = __( 'Set a position for the column.', 'avia_framework' ) . '<br /><br />';
			$desc_column .= '<strong>' . __( 'ATTENTION with position: absolute. This might break the layout (esp. when used for first column in a row). Consider to put each row in an own Color Section and check frontend carefully - also different screen sizes.', 'avia_framework' ) . '</strong><br />';

			$this->elements = array(

				array(						/*	stores the 'first' variable that removes margin from the first column	*/
						'id'	=> 0,
						'std'	=> '',
						'type'	=> 'hidden'
					),

				array(
						'type' 	=> 'tab_container',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Row Settings' , 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'name' 	=> __( 'Row Settings', 'avia_framework' ),
							'desc' 	=> __( 'Row Settings apply to all columns in this row but can only be set in the first column', 'avia_framework' ),
							'type' 	=> 'heading',
							'description_class' => 'av-builder-note av-notice',
							'required'	=> array( '0', 'equals', '' ),
						),

					array(
							'name' 	=> __( 'Row Settings', 'avia_framework' ),
							'desc' 	=> __( 'These setting apply to all columns in this row and can only be set in the first column.', 'avia_framework' )
									 .'<br/><strong>'
									 . __( 'Please note:', 'avia_framework' )
									 .'</strong> '
									 . __( 'If you move another column into first position you will need to re-apply these settings.', 'avia_framework' ),
							'type' 	=> 'heading',
							'description_class' => 'av-builder-note av-notice column-settings-desc',
							'required'	=> array( '0', 'not', '' ),
						),

					array(
							'type'			=> 'template',
							'template_id'	=> 'toggle_container',
							'templates_include'	=> array(
													$this->popup_key( 'row_settings_row_layout' ),
													$this->popup_key( 'row_settings_row_margins' ),
													$this->popup_key( 'row_settings_row_screen_options' )
												),
							'nodescription' => true
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Layout', 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'type'			=> 'template',
							'template_id'	=> 'toggle_container',
							'templates_include'	=> array(
													$this->popup_key( 'layout_borders' ),
													$this->popup_key( 'layout_height' ),
													$this->popup_key( 'layout_padding' ),
													$this->popup_key( 'layout_svg_dividers' ),
													$this->popup_key( 'layout_fold_unfold' )
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
													$this->popup_key( 'styling_box_shadow' ),
													$this->popup_key( 'styling_background' ),
													$this->popup_key( 'styling_highlight' ),
													'fold_styling_toggle',
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
								'name'			=> __( 'Column Position', 'avia_framework' ),
								'desc'			=> $desc_column,
								'type'			=> 'template',
								'template_id'	=> 'position',
								'toggle'		=> true,
								'lockable'		=> true
							),

						array(
								'type'			=> 'template',
								'template_id'	=> $this->popup_key( 'advanced_column_link' ),
							),

						array(
								'type'			=> 'template',
								'template_id'	=> 'columns_visibility_toggle',
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
						'args'			=> array( 'sc'	=> $this )
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
			$std = array(
						__( 'Space between columns (6% - theme default)', 'avia_framework' )	=> '',
						__( 'No space between columns', 'avia_framework' )						=> 'no_margin',
					);

			/**
			 * @since 4.8.7.1
			 * @params int $max_distance
			 * @return int
			 */
			$max_distance = apply_filters( 'avf_alb_columns_grid_max_distance', 20 );

			$sub_dist = AviaHtmlHelper::number_array( 0.5, $max_distance, 0.5, $std, '%' );

			/**
			 * Row Settings Tab
			 * ================
			 */

			$c = array(

						array(
							'name'		=> __( 'Equal Height Columns', 'avia_framework' ),
							'desc'		=> __( 'Columns in this row can either have a height based on their content or all be of equal height based on the largest column ', 'avia_framework' ),
							'id'		=> 'min_height',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( '0', 'not', '' ),
							'subtype'	=> array(
												__( 'Individual height', 'avia_framework' )	=> '',
												__( 'Equal height', 'avia_framework' )		=> 'av-equal-height-column',
											)
						),

						array(
							'name'		=> __( 'Vertical Alignment','avia_framework' ),
							'desc'		=> __( 'If a column is larger than its content, were do you want to align the content vertically?', 'avia_framework' ),
							'id'		=> 'vertical_alignment',
							'type'		=> 'select',
							'std'		=> 'av-align-top',
							'lockable'	=> true,
							'required'	=> array( 'min_height', 'not', '' ),
							'subtype'	=> array(
												__( 'Top', 'avia_framework' )		=> 'av-align-top',
												__( 'Middle', 'avia_framework' )	=> 'av-align-middle',
												__( 'Bottom', 'avia_framework' )	=> 'av-align-bottom',
							)
						),

						array(
							'name'		=> __( 'Space Between Columns','avia_framework' ),
							'desc'		=> __( 'You can remove the default space between columns here or select a custom space (not used when fullwidth breakpoint active)', 'avia_framework' ),
							'id'		=> 'space',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( '0', 'not', '' ),
							'subtype'	=> $sub_dist
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'box_shadow',
							'id'			=> 'row_boxshadow',
							'id2'			=> 'row',
							'desc'			=> __( 'Add a box-shadow to the row', 'avia_framework' ),
							'names'			=> array(
													__( 'Row Box-Shadow', 'avia_framework' ),
													__( 'Row Box-Shadow Style', 'avia_framework' ),
													__( 'Row Box-Shadow Color', 'avia_framework' ),
													__( 'Row Box Shadow Animation', 'avia_framework' ),
													__( 'Row Box-Shadow Width', 'avia_framework' )
												),
							'sub_shadow'	=> array(
													__( 'No shadow', 'avia_framework' )		=> '',
													__( 'Outside', 'avia_framework' )		=> 'outside'
												),
							'std_shadow'	=> '',
							'checkbox'		=> true,
							'animated'		=> 'auto',
							'simplified'	=> true,
							'lockable'		=> true,
							'required'		=> array( 'min_height', 'not', '' )
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Row Layout', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'row_settings_row_layout' ), $template );


			$c = array(
						array(
							'name'		=> __( 'Custom Margins', 'avia_framework' ),
							'desc'		=> __( 'If checked allows you to set a custom top and bottom margin. Otherwise the margin is calculated by the theme based on surrounding elements', 'avia_framework' ),
							'id'		=> 'custom_margin',
							'type'		=> 'checkbox',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( '0', 'not', '' ),
						),

						array(
								'type'			=> 'template',
								'template_id'	=> 'margin_padding',
								'content'		=> array( 'margin' ),
								'name'			=> __( 'Custom Top And Bottom Margin', 'avia_framework' ),
								'desc'			=> __( 'Set a responsive top and bottom margin', 'avia_framework' ),
								'std_margin'	=> '0px',
								'lockable'		=> true,
								'required'		=> array( 'custom_margin', 'not', '' ),
								'multi_margin'	=> array(
														'top'		=> __( 'Top Margin', 'avia_framework' ),
														'bottom'	=> __( 'Bottom Margin', 'avia_framework' )
													)
							)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Row Margins', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'row_settings_row_margins' ), $template );

			$desc  = __( 'Select order of columns when switched to fullwidth. Individual position must be set in &quot;Advanced Tab -&gt; Responsive Toggle&quot;.', 'avia_framework' ) . '<br /><br />';
			$desc .= '<strong>' . __( ' This is currently a beta feature (added 4.8.7)', 'avia_framework' ) . '</strong>';

			$c = array(
						array(
							'name'		=> __( 'Fullwidth Break Point', 'avia_framework' ),
							'desc'		=> __( 'The columns in this row will switch to fullwidth at this screen width ', 'avia_framework' ),
							'id'		=> 'mobile_breaking',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( '0', 'not', '' ),
							'subtype'	=> array(
												__( 'On mobile devices (at a screen width of 767px or lower)', 'avia_framework' )	=> '',
												__( 'On tablets (at a screen width of 989px or lower)', 'avia_framework' )			=> 'av-break-at-tablet',
											)
						),

						array(
							'name'		=> __( 'Column Behaviour When Fullwidth', 'avia_framework' ),
							'desc'		=> $desc,
							'id'		=> 'mobile_column_order',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( '0', 'not', '' ),
							'subtype'	=> array(
												__( 'Same order as defined for desktop', 'avia_framework' )				=> '',
												__( 'Reverse order', 'avia_framework' )									=> 'reverse',
												__( 'Individually select position for each column', 'avia_framework' )	=> 'individual',
											)
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Row Screen Options', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'row_settings_row_screen_options' ), $template );


			/**
			 * Layout Tab
			 * ============
			 */

			$c = array(
						array(
							'name'		=> __( 'Border','avia_framework' ),
							'desc'		=> __( 'Select the borderwidth of the column here', 'avia_framework' ),
							'id'		=> 'border',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> AviaHtmlHelper::number_array( 1, 40, 1, array( __( 'None', 'avia_framework' ) => '' ) , 'px' )
						),

						array(
							'name'		=> __( 'Border Style', 'avia_framework' ),
							'desc'		=> __( 'Set the border style for your column here', 'avia_framework' ),
							'id'		=> 'border_style',
							'type'		=> 'select',
							'std'		=> 'solid',
							'lockable'	=> true,
							'required'	=> array( 'border', 'not', '' ),
							'subtype'	=> AviaPopupTemplates()->get_border_styles_options()
						),

						array(
							'name'		=> __( 'Border Color', 'avia_framework' ),
							'desc'		=> __( 'Set a border color for this column', 'avia_framework' ),
							'id'		=> 'border_color',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'border', 'not', '' )
						),

						array(
							'name'		=> __( 'Border Radius', 'avia_framework' ),
							'desc'		=> __( 'Set the border radius of the column', 'avia_framework' ),
							'id'		=> 'radius',
							'type'		=> 'multi_input',
							'sync'		=> true,
							'std'		=> '',
							'lockable'	=> true,
							'multi'		=> array(
											'top'		=> __( 'Top-Left-Radius', 'avia_framework' ),
											'right'		=> __( 'Top-Right-Radius', 'avia_framework' ),
											'bottom'	=> __( 'Bottom-Right-Radius', 'avia_framework' ),
											'left'		=> __( 'Bottom-Left-Radius', 'avia_framework' )
										)
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

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'layout_borders' ), $template );

			$desc  = __( 'Select a minimum height for the column - px and &percnt; are supported. Defaults to px. Leave empty to use theme default.', 'avia_framework' ) . '<br /><br />';
			$desc .= __( '&percnt; is based on the closest surrounding layout container (e.g. Color Section, Grid Row Cell, ...) or browser window height. Setting of first row column is used for all columns when using &quot;Equal Height Columns&quot;.','avia_framework' ) . '<br /><br />';
			$desc .= __( 'Consider this when using SVG dividers to avoid overlapping.','avia_framework' );

			$c = array(
						array(
							'name'		=> __( 'Minimum Column Height','avia_framework' ),
							'desc'		=> $desc,
							'id'		=> 'min_col_height',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Height', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'layout_height' ), $template );


			$c = array(

						array(
								'type'			=> 'template',
								'template_id'	=> 'margin_padding',
//								'toggle'		=> true,
								'content'		=> array( 'padding' ),
								'name'			=> __( 'Inner Padding', 'avia_framework' ),
								'desc'			=> __( 'Set the distance from the column content to the border here.', 'avia_framework' ),
								'lockable'		=> true
							)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Padding', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'layout_padding' ), $template );



			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'svg_divider_toggle',
								'lockable'		=> true
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'layout_svg_dividers' ), $template );


			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'fold_unfold_container_toggle',
								'$add_desc'		=> '<br /><br /><strong>' . __( 'Will only be used for Individual Height Colums (see Row Settings tab) !!!', 'avia_framework' ) . '</strong>',
								'lockable'		=> true
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'layout_fold_unfold' ), $template );




			/**
			 * Styling Tab
			 * ============
			 */

			$c = array(

						array(
							'type'			=> 'template',
							'template_id'	=> 'box_shadow',
							'id'			=> 'column_boxshadow',
							'id2'			=> 'column',
							'names'			=> array(
													__( 'Column Box-Shadow', 'avia_framework' ),
													__( 'Column Box-Shadow Style', 'avia_framework' ),
													__( 'Column Box-Shadow Color', 'avia_framework' ),
													__( 'Column Box Shadow Animation', 'avia_framework' ),
													__( 'Column Box-Shadow Width', 'avia_framework' )
												),
							'desc'			=> __( 'Add a box-shadow to the column','avia_framework' ),
							'sub_shadow'	=> array(
													__( 'No shadow', 'avia_framework' )		=> '',
													__( 'Outside', 'avia_framework' )		=> 'outside'
												),
							'std_shadow'	=> '',
							'checkbox'		=> true,
							'animated'		=> 'auto',
							'simplified'	=> true,
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

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_box_shadow' ), $template );

			$c = array(
						array(
							'name'		=> __( 'Background', 'avia_framework' ),
							'desc'		=> __( 'Select the type of background for the column.', 'avia_framework' ),
							'id'		=> 'background',
							'type'		=> 'select',
							'std'		=> 'bg_color',
							'lockable'	=> true,
							'subtype'	=> array(
											__( 'Background Color', 'avia_framework' )		=> 'bg_color',
											__( 'Background Gradient', 'avia_framework' )	=> 'bg_gradient',
										)
						),

						array(
							'name'		=> __( 'Custom Background Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom background color for this cell here. Leave empty for default color', 'avia_framework' ),
							'id'		=> 'background_color',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'background', 'equals', 'bg_color' )
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'gradient_colors',
							'id'			=> array( 'background_gradient_direction', 'background_gradient_color1', 'background_gradient_color2', 'background_gradient_color3' ),
							'lockable'		=> true,
							'required'		=> array( 'background', 'equals', 'bg_gradient' ),
							'container_class'	=> array( '', 'av_third av_third_first', 'av_third', 'av_third' ),
						),

						array(
							'name'		=> __( 'Custom Background Image', 'avia_framework' ),
							'desc'		=> __( "Either upload a new, or choose an existing image from your media library. Leave empty if you don't want to use a background image", 'avia_framework' ),
							'id'		=> 'src',
							'type'		=> 'image',
							'title'		=> __( 'Insert Image', 'avia_framework' ),
							'button'	=> __( 'Insert', 'avia_framework' ),
							'std'		=> '',
							'lockable'	=> true,
							'locked'	=> array( 'src', 'attachment', 'attachment_size' )
						),

					/*
						array(
							'name' 	=> __( 'Background Attachment', 'avia_framework' ),
							'desc' 	=> __( 'Background can either scroll with the page or be fixed', 'avia_framework' ),
							'id' 	=> 'background_attach',
							'type' 	=> 'select',
							'std' 	=> 'scroll',
							'required'	=> array( 'src', 'not', '' ),
							'subtype'	=> array(
											__( 'Scroll', 'avia_framework' )	=> 'scroll',
											__( 'Fixed', 'avia_framework' )		=> 'fixed',
							)
						),
*/
						array(
							'type'			=> 'template',
							'template_id'	=> 'background_image_position',
							'lockable'		=> true
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Background', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_background' ), $template );


			$c = array(
						array(
							'name'		=> __( 'Highlight Column', 'avia_framework' ),
							'desc'		=> __( 'Highlight this column by making it slightly bigger', 'avia_framework' ),
							'id'		=> 'highlight',
							'type'		=> 'checkbox',
							'std'		=> '',
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Highlight - Column Scaling', 'avia_framework' ),
							'desc'		=> __( 'How much should the highlighted column be increased in size?', 'avia_framework' ),
							'id'		=> 'highlight_size',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'highlight', 'not', '' ),
							'subtype'	=> AviaHtmlHelper::number_array( 1.1, 1.6, 0.1, array() ),
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Highlight', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_highlight' ), $template );


			/**
			 * Adcanced Tab
			 * ============
			 */

			$c = array(

						array(
							'type'			=> 'template',
							'template_id'	=> 'animation',
							'lockable'		=> true,
							'std_none'		=> '',
							'name'			=> __( 'Column Animation', 'avia_framework' ),
							'desc'			=> __( 'Add a small animation to the column when the user first scrolls to the column position. This is only to add some &quot;spice&quot; to the site.', 'avia_framework' ),
							'groups'		=> array( 'fade', 'slide', 'rotate', 'curtain', 'fade-adv', 'special' )
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'parallax',
							'lockable'		=> true,
							'std'			=> '',
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'fold_animation_toggle',
							'toggle'		=> false,
							'lockable'		=> true
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
							'type'			=> 'template',
							'template_id'	=> 'linkpicker_toggle',
							'name'			=> __( 'Column Link', 'avia_framework' ),
							'desc'			=> __( 'Select where this column should link to', 'avia_framework' ),
							'subtypes'		=> array( 'no', 'manually', 'single', 'taxonomy' ),
							'no_toggle'		=> true,
							'lockable'		=> true
						),

						array(
							'name'		=> __( 'Hover Effect', 'avia_framework' ),
							'desc'		=> __( 'Choose if you want to have a hover effect on the column', 'avia_framework' ),
							'id'		=> 'link_hover',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'link', 'not', '' ),
							'subtype'	=> array(
												__( 'No', 'avia_framework' )	=> '',
												__( 'Yes', 'avia_framework' )	=> 'opacity80'
											)
						),

						array(
							'name' 			=> __( 'Title Attribut', 'avia_framework' ),
							'desc' 			=> __( 'Add a title attribut for screen reader', 'avia_framework' ),
							'id' 			=> 'title_attr',
							'type' 			=> 'input',
							'std' 			=> '',
							'container_class' => 'av_half av_half_first',
							'lockable'		=> true,
							'required'		=> array( 'link', 'not', '' )
						),


						array(
							'name' 			=> __( 'Alt Attribut', 'avia_framework' ),
							'desc' 			=> __( 'Add an alt attribut for screen reader','avia_framework' ),
							'id' 			=> 'alt_attr',
							'type' 			=> 'input',
							'std' 			=> '',
							'container_class' => 'av_half',
							'lockable'		=> true,
							'required'		=> array( 'link', 'not', '' )
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Column Link', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_column_link' ), $template );

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
			$default = array();
			$locked = array();

			$attr = $params['args'];
			$content = $params['content'];
			Avia_Element_Templates()->set_locked_attributes( $attr, $this, $this->config['shortcode'], $default, $locked, $content );

			$args = $attr;			//		=>  extract( $params );
			$data = isset( $params['data'] ) && is_array( $params['data'] ) ? $params['data'] : array();
			$data_locked = array();
			$extraClass = '';

			$name = $this->config['shortcode'];
			$drag = $this->config['drag-level'];
			$drop = $this->config['drop-level'];

			$size = avia_sc_columns::$columns_array;

			$data['shortcodehandler'] 	= $this->config['shortcode'];
			$data['modal_title'] 		= __( 'Edit Column', 'avia_framework' );
			$data['modal_ajax_hook'] 	= $this->config['shortcode'];
			$data['dragdrop-level']		= $this->config['drag-level'];
			$data['allowed-shortcodes'] = $this->config['shortcode'];
			$data['closing_tag']		= $this->is_self_closing() ? 'no' : 'yes';
			$data['base_shortcode']		= $this->config['shortcode'];
			$data['element_title']		= $this->config['name'];
			$data['element_tooltip']	= $this->config['tooltip'];

			if( ! empty( $this->config['modal_on_load'] ) )
			{
				$data['modal_on_load'] 	= $this->config['modal_on_load'];
			}

			foreach( $locked as $key => $value )
			{
				$data_locked[ 'locked_' . $key ] = $value;
			}

			// add background color or gradient to indicator
			$el_bg = '';

			if( empty( $args['background'] ) || ( $args['background'] == 'bg_color' ) )
			{
				$el_bg = ! empty( $args['background_color'] ) ? " background:{$args['background_color']};" : '';
			}
			else
			{
				if( $args['background_gradient_color1'] && $args['background_gradient_color2'] )
				{
					$el_bg = "background:linear-gradient({$args['background_gradient_color1']},{$args['background_gradient_color2']});";
				}
			}

			$data_locked['initial_el_bg'] = $el_bg;
			$data_locked['initial_layout_element_bg'] = $this->get_bg_string( $args );

			$dataString  = AviaHelper::create_data_string( $data );
			$dataStringLocked = AviaHelper::create_data_string( $data_locked );

			$extraClass .= isset( $args['element_template'] ) && $args['element_template'] > 0 ? ' element_template_selected' : '  no_element_template';
			$extraClass	.= isset( $args[0] ) && $args[0] == 'first' ? ' avia-first-col' : '';


			$output  = "<div class='avia_layout_column avia_layout_column_no_cell avia_pop_class avia-no-visual-updates {$name} {$extraClass} av_drag' {$dataString} data-width='{$name}'>";

			$output .=		'<div class="avia_data_locked_container" ' . $dataStringLocked . ' data-update_element_template="yes"></div>';

			$output .=		"<div class='avia_sorthandle menu-item-handle'>";

			$output .=			"<a class='avia-smaller avia-change-col-size' href='#smaller' title='" . __( 'Decrease Column Size', 'avia_framework' ) . "'>-</a>";
			$output .=			"<span class='avia-col-size'>{$size[$name]}</span>";
			$output .=			"<a class='avia-bigger avia-change-col-size'  href='#bigger' title='" . __( 'Increase Column Size', 'avia_framework' ) . "'>+</a>";
			$output .=			"<a class='avia-delete'  href='#delete' title='" . __( 'Delete Column', 'avia_framework' ) . "'>x</a>";
			$output .=			"<a class='avia-save-element'  href='#save-element' title='" . __( 'Save Element as Template', 'avia_framework' ) . "'>+</a>";
			//$output .=		"<a class='avia-new-target'  href='#new-target' title='" . __( 'Move Element', 'avia_framework' ) . "'>+</a>";
			$output .=			"<a class='avia-clone'  href='#clone' title='" . __( 'Clone Column', 'avia_framework' ) . "' >" . __( 'Clone Column', 'avia_framework' ) . '</a>';
			$output .=			"<span class='avia-element-bg-color' style='{$el_bg}'></span>";

			if( ! empty( $this->config['popup_editor'] ) )
			{
				$output .=		"<a class='avia-edit-element'  href='#edit-element' title='" . __( 'Edit Column', 'avia_framework' ) . "'>" . __( 'edit', 'avia_framework' ) . '</a>';
			}

			$output .=		'</div>';
			$output .=		"<div class='avia_inner_shortcode avia_connect_sort av_drop ' data-dragdrop-level='{$drop}'>";
			$output .=			"<textarea data-name='text-shortcode' cols='20' rows='4'>" . ShortcodeHelper::create_shortcode_by_array( $name, $content, $params['args'] ) . '</textarea>';

			if( $content )
			{
				$content = $this->builder->do_shortcode_backend( $content );
			}

			$output .=		$content;
			$output .=		'</div>';
			$output .=		"<div class='avia-layout-element-bg' style='{$data_locked['initial_layout_element_bg']}'></div>";
			$output .= '</div>';

			return $output;
		}

		/**
		 * Create custom stylings
		 *
		 * @since 4.8.4
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles( array $args )
		{
			$result = parent::get_element_styles( $args );

			extract( $result );


			$default = array(
						'padding'						=> '',
						'min_col_height'				=> '',
						'background'					=> 'bg_color',
						'background_color'				=> '',
						'background_gradient_color1'	=> '',
						'background_gradient_color2'	=> '',
						'background_gradient_direction'	=> '',
						'background_position'			=> '',
						'background_repeat'				=> '',
						'background_attach'				=> 'scroll',
						'fetch_image'					=> '',
						'attachment_size'				=> '',
						'attachment'					=> '',
						'radius'						=> '',
						'space'							=> '',
						'border'						=> '',
						'border_color'					=> '',
						'border_style'					=> 'solid',
						'column_boxshadow'				=> '',
						'column_boxshadow_color'		=> 'rgba(0,0,0,0.1)',
						'column_boxshadow_width'		=> '10px',
						'row_boxshadow'					=> '',
						'row_boxshadow_color'			=> 'rgba(0,0,0,0.1)',
						'row_boxshadow_width'			=> '10px',
						'margin'						=> '',
						'custom_margin'					=> '',
						'min_height'					=> '',					//	= option "Equal Height Colums"
						'vertical_alignment'			=> 'av-align-top',
						'animation'						=> '',
						'link'							=> '',
						'linktarget'					=> '',
						'link_hover'					=> '',
						'title_attr'					=> '',
						'alt_attr'						=> '',
						'mobile_display'				=> '',
						'mobile_col_pos'				=> 0,
						'mobile_breaking'				=> '',
						'mobile_column_order'			=> '',
						'highlight'						=> '',
						'highlight_size'				=> ''
					);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			//	we skip $content override as we only allow styling of column to be locked
			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			if( '' == $atts['space'] )
			{
				/**
				 * Change theme default column space from 6px
				 * You MUST save theme options whenever you change the return value of this filter to clear post-css files
				 *
				 * @since 4.8.7.1
				 * @param string $atts['space']
				 * @return string|float
				 */
				$atts['space'] = apply_filters( 'avf_alb_default_column_space', $atts['space'] );
			}

			$min_col_height_in_pc = false;

			if( '' != $atts['min_col_height'] )
			{
				if( empty( $atts['min_col_height'] ) )
				{
					$atts['min_col_height'] = '';
				}
				else if( is_numeric( $atts['min_col_height'] ) )
				{
					$atts['min_col_height'] = trim( $atts['min_col_height'] ) . 'px';
				}
				else if( false !== stripos( $atts['min_col_height'], '%' ) )
				{
					$min_col_height_in_pc = true;
				}
			}

			/**
			 * Allow fallback to ignore responsive margin and padding in case a page/site gets broken
			 *
			 * @since 5.1
			 * @param boolean $disable_responsive_margin_padding
			 * @param array $atts
			 * @param array $result
			 * @param aviaShortcodeTemplate $this
			 * @return false|mixed						anything not false to fallback
			 */
			$disable_responsive_margin_padding = apply_filters( 'avf_disable_columns_responsive_margin_padding', false, $atts, $result, $this );

			//	set global variables
			avia_sc_columns::$first = ( isset( $atts[0]) && trim( $atts[0]) == 'first' ) ? 'first' : '';
			if( avia_sc_columns::$first )
			{
				avia_sc_columns::$first_atts = $atts;
			}

			//	in backend we must check for $first_atts !!!
			if( ! avia_sc_columns::$first && isset( avia_sc_columns::$first_atts['margin'] ) )
			{
				//	copy global values to column for responsive styles settings
				$atts['margin'] = avia_sc_columns::$first_atts['margin'];
				$atts['av-desktop-margin'] = avia_sc_columns::$first_atts['av-desktop-margin'];
				$atts['av-medium-margin'] = avia_sc_columns::$first_atts['av-medium-margin'];
				$atts['av-small-margin'] = avia_sc_columns::$first_atts['av-small-margin'];
				$atts['av-mini-margin'] = avia_sc_columns::$first_atts['av-mini-margin'];
			}


			if( empty( $atts['fold_height'] ) )
			{
				$atts['fold_height'] = 80;
			}

			/**
			 * Removed option, allows to place top of folded container from screen top when top of container unvisible
			 *
			 * @since 5.6
			 * @param int $avf_fold_top_offset
			 * @param array $atts
			 * @param aviaShortcodeTemplate $this
			 * @return int
			 */
			$atts['fold_top_offset'] = apply_filters( 'avf_fold_top_offset', 50, $atts, $this );

			$atts['fold_element_class'] = "av-fold-column-{$element_id}";


			$element_styling->create_callback_styles( $atts );


			$classes = array(
						'flex_column_table',
						$element_id,
						'sc-' . $shortcodename
					);

			$element_styling->add_classes( 'flex-column-table', $classes );


			$classes = array(
						'flex_column',
						$element_id,
						$shortcodename
					);

			$element_styling->add_classes( 'flex-column', $classes );

			$element_styling->add_classes_from_array( 'flex-column', $meta, 'el_class' );

			if( ! empty( avia_sc_columns::$first ) )
			{
				$element_styling->add_classes( 'flex-column', avia_sc_columns::$first );
			}

			if( ! empty( avia_sc_columns::$first_atts['space'] ) )
			{
				if( 'no_margin' == avia_sc_columns::$first_atts['space'] )
				{
					$element_styling->add_classes( 'flex-column', avia_sc_columns::$first_atts['space'] );
				}
				else
				{
					$grid_values = $this->calculate_grid_values( avia_sc_columns::$first_atts['space'] );

					if( 'av-equal-height-column' != avia_sc_columns::$first_atts['min_height'] )
					{
						$element_styling->add_styles( 'flex-column', array( 'width' => $grid_values['width'] . '%' ) );
						if( empty( avia_sc_columns::$first ) )
						{
							$element_styling->add_styles( 'flex-column', array( 'margin-left' => $grid_values['margin-left'] . '%' ) );
						}
					}
					else
					{
						$element_styling->add_styles( 'flex-column', array(
																		'width'			=> $grid_values['width'] . '%',
																		'margin-left'	=> 0
																	) );
						$element_styling->add_styles( 'placeholder', array( 'width' => $grid_values['margin-left'] . '%' ) );
					}
				}
			}

			if( ! empty( avia_sc_columns::$first_atts['mobile_breaking'] ) )
			{
				$element_styling->add_classes( 'flex-column', avia_sc_columns::$first_atts['mobile_breaking'] );
				$element_styling->add_classes( 'flex-column-table', avia_sc_columns::$first_atts['mobile_breaking'] . '-flextable' );
				$element_styling->add_classes( 'flex-column-wrapper', avia_sc_columns::$first_atts['mobile_breaking'] . '-flexwrapper' );
			}

			if( ! empty( avia_sc_columns::$first_atts['mobile_column_order'] ) )
			{
				switch( avia_sc_columns::$first_atts['mobile_column_order'] )
				{
					case 'reverse':
					case 'individual';
						$classes = array( 'av-mobile-columns-flex', 'av-columns-' . avia_sc_columns::$first_atts['mobile_column_order'] );
						$element_styling->add_classes( 'flex-column-table', $classes );
						$element_styling->add_classes( 'flex-column-wrapper', $classes );
						break;
				}

				if( 'individual' == avia_sc_columns::$first_atts['mobile_column_order'] )
				{
					$element_styling->add_styles( 'flex-column', array( 'order' => $atts['mobile_col_pos'] ) );
				}
			}

			if( ! empty( avia_sc_columns::$first_atts['min_height'] ) )
			{
				$classes = array(
								'flex_column_table_cell',
								avia_sc_columns::$first_atts['min_height'],
								avia_sc_columns::$first_atts['vertical_alignment']
							);

				$element_styling->add_classes( 'flex-column', $classes );
				$element_styling->add_classes( 'flex-column-table', avia_sc_columns::$first_atts['min_height'] . '-flextable' );
			}
			else
			{
				$element_styling->add_classes( 'flex-column', 'flex_column_div' );
			}

			if( ! empty( avia_sc_columns::$first_atts['custom_margin'] ) )
			{
				if( false !== $disable_responsive_margin_padding )
				{
					//	fallback
					//	currently only top and bottom margin
					$explode_margin = explode( ',', avia_sc_columns::$first_atts['margin'] );
					if( count( $explode_margin ) <= 1 )
					{
						$explode_margin[1] = $explode_margin[0];
					}

					if( is_numeric( $explode_margin[0] ) )
					{
						$explode_margin[0] .= 'px';
					}

					if( is_numeric( $explode_margin[1] ) )
					{
						$explode_margin[1] .= 'px';
					}

					$margins = array(
								'margin-top'	=> $explode_margin[0],
								'margin-bottom'	=> $explode_margin[1],
							);

					if( ! empty( avia_sc_columns::$first_atts['min_height'] ) )
					{
						$element_styling->add_styles( 'flex-column-table-margin', $margins );
					}
					else
					{
						$element_styling->add_styles( 'flex-column-margin', $margins );
						$element_styling->add_styles( 'resp-column-margin', $margins );
					}
				}
				else
				{
					if( ! empty( avia_sc_columns::$first_atts['min_height'] ) )
					{
						$element_styling->add_responsive_styles( 'flex-column-table-margin', 'margin', avia_sc_columns::$first_atts, $this );
					}
					else
					{
						$element_styling->add_responsive_styles( 'flex-column-margin', 'margin', avia_sc_columns::$first_atts, $this );
						$element_styling->add_responsive_styles( 'resp-column-margin', 'margin', avia_sc_columns::$first_atts, $this );
					}
				}
			}

			//	must be placed here to avoid to override z-index of position options
			if( ! empty( $atts['highlight'] ) && ! empty( $atts['highlight_size'] ) )
			{
				$transform = "scale({$atts['highlight_size']})";
				$element_styling->add_styles( 'flex-column', $element_styling->transform_rules( $transform ) );
				$element_styling->add_styles( 'flex-column', array( 'z-index' => 5 ) );
			}

			//	by default we animate box shadow for curtain reveal
			$curtain_reveal = false;
			$curtain_duration = 0;
			$delay_rule = array();

			/**
			 * Style Column div
			 * ================
			 */
			if( ! empty( $atts['animation'] ) )
			{
				if( false !== strpos( $atts['animation'], 'curtain-reveal-' ) )
				{
					$curtain_reveal = true;
					$curtain_duration = ! empty( $atts['animation_duration'] ) ? $atts['animation_duration'] : 4;
					$delay_rule = $element_styling->animation_delay_rules( $curtain_duration );

					$classes_curtain = array(
								'avia-curtain-reveal-overlay',
								'av-animated-when-visible-95',
								'animate-all-devices',
								$atts['animation']
							);

					//	animate in preview window
					if( is_admin() )
					{
						$classes_curtain[] = 'avia-animate-admin-preview';
					}

					$element_styling->add_classes( 'curtain', $classes_curtain );
					$element_styling->add_callback_styles( 'curtain', array( 'animation' ) );
				}
				else
				{
					$classes_anim = array(
								'av-animated-generic',
								 $atts['animation']
							);

					if( is_admin() )
					{
						$classes_anim[] = 'avia-animate-admin-preview';
					}

					$element_styling->add_callback_styles( 'flex-column-animation', array( 'animation' ) );
					$element_styling->add_classes( 'flex-column', $classes_anim );
				}
			}

			if( $element_styling->add_responsive_styles( 'flex-column', 'css_position', $atts, $this ) > 0 )
			{
				$element_styling->add_classes( 'flex-column', array( 'av-custom-positioned' ) );
			}

			if( $element_styling->add_callback_data_attributes( 'flex-column', array ( 'parallax' ) ) > 0 )
			{
				$element_styling->add_data_attributes( 'flex-column', array(
															'parallax-container'	=> ".{$element_id}"
														) );
				$element_styling->add_classes( 'flex-column', 'av-parallax-object' );
			}

			/**
			 * Style Boxshadows
			 */
			if( ! empty( $atts['row_boxshadow'] ) )
			{
				if( trim( $atts['row_boxshadow_color'] ) != '' )
				{
//					$box_shadow = "0 0 {$atts['row_boxshadow_width']}px 0 {$atts['row_boxshadow_color']}";
//					$element_styling->add_styles( 'flex-column-table', $element_styling->box_shadow_rules( $box_shadow ) );

					if( $curtain_reveal )
					{
						$element_styling->add_classes( 'flex-column-table', array( 'shadow-animated', 'av-animated-when-visible-95', 'animate-all-devices' ) );
						$element_styling->add_callback_styles( 'shadow-table-animated', array( 'row_boxshadow_animated' ) );
						$element_styling->add_styles( 'shadow-table-animated', $delay_rule );
					}
					else
					{
						$element_styling->add_classes( 'flex-column-table', 'shadow-not-animated' );
						$element_styling->add_callback_styles( 'flex-column-table', array( 'row_boxshadow_not_animated' ) );
					}
				}
			}

			if( ! empty( $atts['column_boxshadow'] ) )
			{
				if( trim( $atts['column_boxshadow_color'] ) != '' )
				{
//					$box_shadow = "0 0 {$atts['column_boxshadow_width']}px 0 {$atts['column_boxshadow_color']}";
//					$element_styling->add_styles( 'flex-column', $element_styling->box_shadow_rules( $box_shadow ) );

					if( $curtain_reveal )
					{
						$element_styling->add_classes( 'flex-column', array( 'shadow-animated', 'av-animated-when-visible-95', 'animate-all-devices' ) );
						$element_styling->add_callback_styles( 'shadow-column-animated', array( 'column_boxshadow_animated' ) );
						$element_styling->add_styles( 'shadow-column-animated', $delay_rule );
					}
					else
					{
						$element_styling->add_classes( 'flex-column', 'shadow-not-animated' );
						$element_styling->add_callback_styles( 'flex-column', array( 'column_boxshadow_not_animated' ) );
					}
				}
			}

			if( ! empty( $atts['mobile_display'] ) )
			{
				$element_styling->add_classes( 'flex-column', $atts['mobile_display'] );
			}


			if( ! empty( $atts['border'] ) )
			{
				$element_styling->add_styles( 'flex-column', array( 'border-width' => $atts['border'] . 'px' ) );

				if( ! empty( $atts['border_color'] ) )
				{
					$element_styling->add_styles( 'flex-column', array( 'border-color' => $atts['border_color'] ) );
				}

				if( ! empty( $atts['border_style'] ) )
				{
					$element_styling->add_styles( 'flex-column', array( 'border-style' => $atts['border_style'] ) );
				}
			}

			$radius_info = null;
			if( ! AviaHelper::empty_multi_input( $atts['radius'] ) )
			{
				$radius_info = AviaHelper::multi_value_result_lockable( $atts['radius'] );
				$element_styling->add_styles( 'flex-column', $element_styling->border_radius_rules( $radius_info['fill_with_0_style'] ) );
			}

			if( false !== $disable_responsive_margin_padding )
			{
				//	fallback
				if( ! AviaHelper::empty_multi_input( $atts['padding'] ) )
				{
					//	fallback - prior 4.8.4 '0px' was default
					if( $atts['padding'] == '0px' || $atts['padding'] == '0' || $atts['padding'] == '0%' )
					{
						//	class is not used in css !!!!
						$element_styling->add_classes( 'flex-column', 'av-zero-column-padding' );
						$atts['padding'] = '';
					}
					else
					{
						$padding_info = AviaHelper::multi_value_result_lockable( $atts['padding'] );
						$element_styling->add_styles( 'flex-column', array( 'padding' => $padding_info['fill_with_0_style'] ) );
					}
				}
			}
			else
			{
				//	removed with 5.1
				if( ! AviaHelper::empty_multi_input( $atts['padding'] ) )
				{
					//	fallback - prior 4.8.4 '0px' was default
					if( $atts['padding'] == '0px' || $atts['padding'] == '0' || $atts['padding'] == '0%' )
					{
						//	class is not used in css !!!!
						$element_styling->add_classes( 'flex-column', 'av-zero-column-padding' );
						$atts['padding'] = '';
						$atts['av-desktop-padding'] = '';
					}
				}

				$element_styling->add_responsive_styles( 'flex-column', 'padding', $atts, $this );
			}

			if( ! empty( $atts['min_col_height'] ) )
			{
				if( ! $min_col_height_in_pc )
				{
					if( empty( $atts['min_height'] ) )
					{
						$element_styling->add_styles( 'flex-column', array(
																'height'		=> 'auto',
																'min-height'	=> $atts['min_col_height']
															) );
					}
					else
					{
						//	flex table do not support min-height, height is ignored when content is larger
						$element_styling->add_styles( 'flex-column', array( 'height' => $atts['min_col_height'] ) );
					}
				}
				else
				{
					$col_args = array(
									'column-min-pc'			=> $atts['min_col_height'],
									'column-equal-height'	=> ! empty( $atts['min_height'] )
							);

					$element_styling->add_data_attributes( 'flex-column-min-height', $col_args );
					$element_styling->add_classes( 'flex-column', 'av-column-min-height-pc' );
				}
			}

			/**
			 * Style Background
			 * ================
			 */
			if( empty( $atts['attachment'] ) )
			{
				//	manual added shortcodes may contain an url only
				$atts['fetch_image'] = trim( $atts['src'] );
			}
			else if( ! empty( $atts['attachment'] ) )
			{
				$src = wp_get_attachment_image_src( $atts['attachment'], $atts['attachment_size'] );
				if( ! empty( $src[0] ) )
				{
					$atts['fetch_image'] = $src[0];
				}
			}

			if( $atts['background_repeat'] == 'stretch' )
			{
				$element_styling->add_classes( 'flex-column', 'avia-full-stretch' );
				$atts['background_repeat'] = 'no-repeat';
			}

			if( $atts['background_repeat'] == 'contain' )
			{
				$element_styling->add_classes( 'flex-column', 'avia-full-contain' );
				$atts['background_repeat'] = 'no-repeat';
			}

			// background image, color and gradient
			$bg_image = '';

			if( ! empty( $atts['fetch_image'] ) )
			{
				$bg_pos = $element_styling->background_position_string( $atts['background_position'] );
				$bg_image = "url({$atts['fetch_image']}) {$bg_pos} {$atts['background_repeat']} {$atts['background_attach']}";
			}

			if( $atts['background'] != 'bg_gradient' )
			{
				if( ! empty( $bg_image ) )
				{
					$element_styling->add_styles( 'flex-column', array( 'background' => "{$bg_image} {$atts['background_color']}" ) );
				}
				else if( ! empty( $atts['background_color'] ) )
				{
					$element_styling->add_styles( 'flex-column', array( 'background-color' => $atts['background_color'] ) );
				}
			}
			// assemble gradient declaration
			else if( ! empty( $atts['background_gradient_color1'] ) && ! empty( $atts['background_gradient_color2'] ) )
			{
				// fallback background color for IE9
				$element_styling->add_styles( 'flex-column', array( 'background-color' => $atts['background_gradient_color1'] ) );

				if( empty( $bg_image ) )
				{
					$element_styling->add_callback_styles( 'flex-column', array( 'background_gradient_direction' ) );
				}
				else
				{
					$gradient_val_array = $element_styling->get_callback_settings( 'background_gradient_direction', 'styles' );
					$gradient_val = isset( $gradient_val_array['background'] ) ? $gradient_val_array['background'] : '';

					//	',' is needed !!!
					$gradient_style = ! empty( $gradient_val ) ? "{$bg_image}, {$gradient_val}" : $bg_image;

					$element_styling->add_styles( 'flex-column', array( 'background' => $gradient_style ) );
				}
			}
			else
			{
				//	fallback to image and first gradient color
				if( ! empty( $bg_image ) )
				{
					$element_styling->add_styles( 'flex-column', array( 'background' => "{$bg_image} {$atts['background_gradient_color1']}" ) );
				}
				else if( ! empty( $atts['background_gradient_color1'] ) )
				{
					$element_styling->add_styles( 'flex-column', array( 'background-color' => $atts['background_gradient_color1'] ) );
				}
			}

			//	SVG Dividers
			$element_styling->add_classes( 'divider-top', array( 'avia-divider-svg', 'avia-divider-svg-' . $atts['svg_div_top'] ) );
			$element_styling->add_classes( 'divider-bottom', array( 'avia-divider-svg', 'avia-divider-svg-' . $atts['svg_div_bottom'] ) );

			$element_styling->add_callback_styles( 'divider-top-svg', array( 'svg_div_top_svg', 'svg_div_top_color' ) );
			$element_styling->add_callback_styles( 'divider-bottom-svg', array( 'svg_div_bottom_svg', 'svg_div_bottom_color' ) );

			$element_styling->add_callback_classes( 'divider-top', array( 'svg_div_top' ) );
			$element_styling->add_callback_classes( 'divider-bottom', array( 'svg_div_bottom' ) );

			//	adjust border for dividers
			if( ! empty( $atts['border'] ) )
			{
				$styles = array(
							'left'	=> "-{$atts['border']}px",
							'right'	=> "-{$atts['border']}px",
							'width'	=> 'auto'
						);

				if( ! empty( $atts['svg_div_top'] ) )
				{
					$element_styling->add_styles( 'divider-top-div', $styles );
					$element_styling->add_styles( 'divider-top-div', array( 'top' => "-{$atts['border']}px" ) );
				}

				if( ! empty( $atts['svg_div_bottom'] ) )
				{
					$element_styling->add_styles( 'divider-bottom-div', $styles );
					$element_styling->add_styles( 'divider-bottom-div', array( 'bottom' => "-{$atts['border']}px" ) );
				}
			}

			//	adjust border radius for dividers
			if( ! AviaHelper::empty_multi_input( $atts['radius'] ) && is_array( $radius_info ) )
			{
				$radius_info = $radius_info['rules_complete'];

				if( ! empty( $atts['svg_div_top'] ) )
				{
					//	rotating svg must be taken in account
					if( empty( $atts['svg_div_top_invert'] ) )
					{
						$element_styling->add_styles( 'divider-top-div', $element_styling->border_radius_rules( "{$radius_info[0]} {$radius_info[1]} 0 0" ) );
					}
					else
					{
						$element_styling->add_styles( 'divider-top-div', $element_styling->border_radius_rules( "0 0 {$radius_info[0]} {$radius_info[1]}" ) );
					}
				}

				if( ! empty( $atts['svg_div_bottom'] ) )
				{
					if( empty( $atts['svg_div_bottom_invert'] ) )
					{
						$element_styling->add_styles( 'divider-bottom-div', $element_styling->border_radius_rules( "{$radius_info[2]} {$radius_info[3]} 0 0" ) );
					}
					else
					{
						$element_styling->add_styles( 'divider-bottom-div', $element_styling->border_radius_rules( "0 0 {$radius_info[2]} {$radius_info[3]}" ) );
					}
				}
			}

			$link = AviaHelper::get_url( $atts['link'] );
			if( ! empty( $link ) )
			{
				$element_styling->add_classes( 'flex-column', array( 'avia-link-column', 'av-column-link' ) );

				if( ! empty( $atts['link_hover'] ) )
				{
					$element_styling->add_classes( 'flex-column', 'avia-link-column-hover' );
				}
			}

			if( empty( avia_sc_columns::$first_atts['min_height'] ) && ! empty( $atts['fold_type'] ) )
			{
				$f_classes = array(
						$atts['fold_type'],
						'avia-fold-column-wrap',
						'avia-fold-init',
						$atts['fold_element_class'],
						$atts['fold_text_style'],
						empty( $atts['fold_btn_align'] ) ? 'align-left' : $atts['fold_btn_align']
					);

				$element_styling->add_classes( 'fold-section', $f_classes );

				if( $atts['fold_text_style'] == '' )
				{
					$element_styling->add_styles( 'fold-button', array( 'color' => $atts['fold_text_color'] ) );
				}

				if( $atts['fold_text_style'] != '' && $atts['fold_btn_color'] == 'custom' )
				{
					$element_styling->add_styles( 'fold-button', array(
													'background-color'	=> $atts['fold_btn_bg_color'],
													'color'				=> $atts['fold_btn_font_color'],
												) );
				}

				$element_styling->add_responsive_font_sizes( 'fold-button', 'size-btn-text', $atts, $this );

				if( ! empty( $atts['fold_overlay_color'] ) )
				{
					$bg_rgb = avia_backend_hex_to_rgb_array( $atts['fold_overlay_color'] );

					$element_styling->add_styles( 'fold-unfold-after', array(
													'background'	=> "linear-gradient( to bottom, rgba({$bg_rgb[0]},{$bg_rgb[1]},{$bg_rgb[2]},0), rgba({$bg_rgb[0]},{$bg_rgb[1]},{$bg_rgb[2]},1) )"
												) );
				}

				$element_styling->add_styles( 'fold-unfold', array( 'max-height' => $atts['fold_height'] . 'px' ) );

				if( ! empty( $atts['fold_timer'] ) )
				{
					$rules = $element_styling->transition_duration_rules( $atts['fold_timer'] );

					$element_styling->add_styles( 'fold-unfold', $rules );
					$element_styling->add_styles( 'fold-unfold-after', $rules );
				}

				$element_styling->add_styles( 'fold-unfold-folded-after', array( 'z-index' => $atts['z_index_fold'] ) );

				//	prepare attributes for frontend
				$element_styling->add_data_attributes( 'fold-section', array(
												'type'		=> $atts['fold_type'],
												'height'	=> $atts['fold_height'],
												'more'		=> $atts['fold_more'],
												'less'		=> $atts['fold_less'],
												'context'	=> __CLASS__
											) );

			}


			$selectors = array(
							'flex-column-table-margin'	=> "#top .flex_column_table.av-equal-height-column-flextable.{$element_id}",
							'flex-column-table'			=> ".flex_column_table.av-equal-height-column-flextable.{$element_id}",
							'flex-column-margin'		=> "#top .flex_column.{$element_id}",
							'flex-column'				=> ".flex_column.{$element_id}",
							'flex-column-animation'		=> ".avia_transform .flex_column.{$element_id}",
							'divider-top-div'			=> ".flex_column.{$element_id} .avia-divider-svg-top",
							'divider-bottom-div'		=> ".flex_column.{$element_id} .avia-divider-svg-bottom",
							'divider-top-svg'			=> ".flex_column.{$element_id} .avia-divider-svg-top svg",
							'divider-bottom-svg'		=> ".flex_column.{$element_id} .avia-divider-svg-bottom svg",
							'resp-column-margin'		=> ".responsive #top #wrap_all .flex_column.{$element_id}",
							'placeholder'				=> "#top .flex_column_table.av-equal-height-column-flextable.{$element_id} .av-flex-placeholder",
							'curtain'					=> ".flex_column.{$element_id} .avia-curtain-reveal-overlay",
										//	animated box shadow
							'shadow-table-animated'		=> ".flex_column_table.av-equal-height-column-flextable.{$element_id}.avia_start_delayed_animation.shadow-animated",
							'shadow-column-animated'	=> ".flex_column.{$element_id}.avia_start_delayed_animation.shadow-animated",

							'fold-section'				=> ".avia-fold-unfold-section.{$atts['fold_element_class']}",
							'fold-unfold'				=> ".avia-fold-unfold-section.{$atts['fold_element_class']} .av-fold-unfold-container",
							'fold-unfold-after'			=> "#top .avia-fold-unfold-section.{$atts['fold_element_class']} .av-fold-unfold-container:after",
							'fold-unfold-folded-after'	=> ".avia-fold-unfold-section.{$atts['fold_element_class']} .av-fold-unfold-container.folded::after",
							'fold-button'				=> "#top .avia-fold-unfold-section.{$atts['fold_element_class']} .av-fold-button-container"
				);

			$element_styling->add_selectors( $selectors );

			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['element_styling'] = $element_styling;

			return $result;
		}

		/**
		 * Returns the modal popups svg divider preview windows in $svg_list
		 *
		 * @since 4.8.4
		 * @param array $args
		 * @param array $svg_list
		 * @return array
		 */
		public function build_svg_divider_preview( array $args, array $svg_list )
		{
			//	clear content and not needed settings - we only need minimal stylings
			$args['content'] = '';
			unset( $args['atts']['content'] );

			unset( $args['atts'][0] );		//	remove first
			$args['atts']['padding'] = '';
			$args['atts']['custom_margin'] = '';


			$result = $this->get_element_styles( $args );

			extract( $result );

			$dummy_text = __( 'Dummy Content to demonstrate &quot;Bring To Front&quot; option', 'avia_framework' );

			foreach( $svg_list as $id => $info )
			{
				$svg_height = $atts[ $id . '_height' ];
				$svg_max_height = $atts[ $id . '_max_height' ];

				if( ! is_numeric( $svg_height ) )
				{
					$svg_height = is_numeric( $svg_max_height ) ? $svg_max_height : 100;
				}

				$dummy_dist = $svg_height > 30 ? 20 : 5;
				$dummy_height = $svg_height + 20;

				$style_col = array(
								'height'	=> max( $svg_height + 120, 150 ) . 'px'
							);

				$style_dummy = array(
								'height'		=> $dummy_height . 'px',
								'line-height'	=> $dummy_height . 'px'
							);

				if( 'top' == $info['location'] )
				{
					$class = 'av-top-divider';
					$element_styling->add_styles( 'preview-column-top', $style_col );
					$element_styling->add_styles( 'preview-dummy-top', array( 'top' => $dummy_dist . 'px' ) );
					$element_styling->add_styles( 'preview-dummy-top', $style_dummy );
				}
				else
				{
					$class = 'av-bottom-divider';
					$element_styling->add_styles( 'preview-column-bottom', $style_col );
					$element_styling->add_styles( 'preview-dummy-bottom', array( 'bottom' => $dummy_dist . 'px' ) );
					$element_styling->add_styles( 'preview-dummy-bottom', $style_dummy );
				}

				$selectors = array(
								'preview-column-top'	=> ".flex_column.{$element_id}.av-top-divider",
								'preview-column-bottom'	=> ".flex_column.{$element_id}.av-bottom-divider",
								'preview-dummy-top'		=> ".flex_column.{$element_id}.av-top-divider .av-dummy-text",
								'preview-dummy-bottom'	=> ".flex_column.{$element_id}.av-bottom-divider .av-dummy-text"
							);


				$element_styling->add_selectors( $selectors );

				$style_tag = $element_styling->get_style_tag( $element_id );

				$html  = '';
				$html .= $style_tag;
				$html .= "<div class='svg-shape-container flex_column {$element_id} {$class}'>";

				if( ! empty( $atts[ $id ] ) )
				{
					$html .= AviaSvgShapes()->get_svg_dividers( $atts, $element_styling, $info['location'] );
				}

				$html .=		'<div class="av-dummy-text">';
				$html .=			'<p>' . esc_html( $dummy_text ) . '</p>';
				$html .=		'</div>';

				$html .= '</div>';

				$svg_list[ $id ]['html'] = $html;
			}

			return $svg_list;
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

			$avia_config['current_column'] = $shortcodename;

			$result = $this->get_element_styles( compact( array( 'atts', 'content', 'shortcodename', 'meta' ) ) );

			extract( $result );

			avia_sc_columns::$counter ++;


			$custom_el_id = $meta['custom_el_id'];

			//	we need an id to allow smoothscroll when folding the section
			if( empty( $meta['custom_el_id'] ) && $atts['fold_type'] != '' )
			{
				$custom_el_id = 'id="avia_sc_fold_column_' . avia_sc_columns::$counter . '"';
			}

			$link = AviaHelper::get_url( $atts['link'] );
			$link_data = '';
			$screen_reader_link = '';

			if( ! empty( $link ) )
			{
				$screen_reader = '';

				$link_data .= ' data-link-column-url="' . esc_attr( $link ) . '" ';

				if( ( strpos( $atts['linktarget'], '_blank' ) !== false ) )
				{
					$link_data .=  ' data-link-column-target="_blank" ';
					$screen_reader .= ' target="_blank" ';
				}

				//	we add this, but currently not supported in js
				if( strpos( $atts['linktarget'], 'nofollow' ) !== false )
				{
					$link_data .= ' data-link-column-rel="nofollow" ';
					$screen_reader .= ' rel="nofollow" ';
				}

				if( ! empty( $atts['title_attr'] ) )
				{
					$screen_reader .= ' title="' . esc_attr( $atts['title_attr'] ) . '"';
				}

				if( ! empty( $atts['alt_attr'] ) )
				{
					$screen_reader .= ' alt="' . esc_attr( $atts['alt_attr'] ) . '"';
				}

				/**
				 * Add an invisible link also for screen readers
				 */
				$screen_reader_link .=	'<a class="av-screen-reader-only" href=' . esc_attr( $link ) . " {$screen_reader}" . '>';
				$screen_reader_link .=		AviaHelper::get_screen_reader_url_text( $atts['link'] );
				$screen_reader_link .=	'</a>';
			}

			$aria_label = ! empty( $meta['aria_label'] ) ? " aria-label='{$meta['aria_label']}' " : '';

			if( avia_sc_columns::$first )
			{
				avia_sc_columns::$calculated_size = 0;

				if( ! empty( $meta['siblings']['prev']['tag'] ) && in_array( $meta['siblings']['prev']['tag'], array( 'av_one_full', 'av_one_half', 'av_one_third', 'av_two_third', 'av_three_fourth', 'av_one_fourth', 'av_one_fifth', 'av_textblock' ) ) )
				{
					avia_sc_columns::$extraClass = 'column-top-margin';
				}
				else
				{
					avia_sc_columns::$extraClass = '';
				}
			}

			$curtain_reveal_overlay = '';
			if( false !== strpos( $atts['animation'], 'curtain-reveal-' ) )
			{
				$curtain_class = $element_styling->get_class_string( 'curtain' );
				$curtain_reveal_overlay = "<div class='{$curtain_class}'></div>";
			}

			$style_tag = $element_styling->get_style_tag( $element_id );
			$table_class = $element_styling->get_class_string( 'flex-column-table' );
			$wrapper_class = $element_styling->get_class_string( 'flex-column-wrapper' );
			$column_class = $element_styling->get_class_string( 'flex-column' );
			$column_data = $element_styling->get_data_attributes_json_string( 'flex-column', 'parallax' );
			$column_min_data = $element_styling->get_data_attributes_json_string( 'flex-column-min-height', 'av-column-min-height' );


			$fold_section_class = '';
			$fold_section_data = '';
			$fold_container = '';

			if( empty( avia_sc_columns::$first_atts['min_height'] ) && ! empty( $atts['fold_type'] ) )
			{
				$fold_section_class = $element_styling->get_class_string( 'fold-section' );
				$fold_section_data = $element_styling->get_data_attributes_json_string( 'fold-section', 'fold_unfold' );

				$args = [
						'atts'			=> $atts,
						'wrapper_class'	=> 'av-section-fold-btn-wrap',
						'context'		=> __CLASS__
					];

				$fold_container .=		'<div class="av-fold-unfold-container folded">';
				$fold_container .=			'<div class="av-fold-unfold-inner"></div>';
				$fold_container .=		'</div>';
				$fold_container .=		aviaFrontTemplates::fold_unfold_button( $args );
			}



			$output  = '';


			if( ! empty( avia_sc_columns::$first_atts['min_height'] ) && avia_sc_columns::$calculated_size == 0 )
			{
				$output .= "<div class='{$table_class}'>";
			}
			else if( empty( avia_sc_columns::$first_atts['min_height'] ) && ! empty( avia_sc_columns::$first_atts['mobile_column_order'] ) && avia_sc_columns::$calculated_size == 0 )
			{
				$output .= "<div class='av-column-wrapper-individual {$wrapper_class}'>";
			}

			if( ! avia_sc_columns::$first && ! empty( avia_sc_columns::$first_atts['min_height'] ) && avia_sc_columns::$first_atts['space'] != 'no_margin' )
			{
				$output .= "<div class='av-flex-placeholder'></div>";
			}

			avia_sc_columns::$calculated_size += avia_sc_columns::$size_array[ $this->config['shortcode'] ];

			//	add it here to allow selector #top .flex_column_table.av-equal-height-column-flextable:not(:first-child) to work (grid.css)
			$output .= $style_tag;

			$output .= "<div {$custom_el_id} class='{$column_class} {$fold_section_class} " . avia_sc_columns::$extraClass . "' {$column_data} {$column_min_data} {$link_data} {$aria_label} {$fold_section_data}>";
			$output .=		$fold_container;
			$output .=		$curtain_reveal_overlay;
			$output .=		AviaSvgShapes()->get_svg_dividers( $atts, $element_styling );
			$output .=		$screen_reader_link;

			//if the user uses the column shortcode without the layout builder make sure that paragraphs are applied to the text
			$content = ( empty( $avia_config['conditionals']['is_builder_template'] ) ) ? ShortcodeHelper::avia_apply_autop( ShortcodeHelper::avia_remove_autop( $content ) ) : ShortcodeHelper::avia_remove_autop( $content, true );

			$output .=		trim( $content );
			$output .= '</div>';


			$force_close = false;

			if( isset( $meta['siblings'] ) && isset($meta['siblings']['next'] ) && isset( $meta['siblings']['next']['tag'] ) )
			{
				if( ! array_key_exists( $meta['siblings']['next']['tag'], avia_sc_columns::$size_array ) )
				{
					$force_close = true;
				}
			}

			/**
			 * check if row will break into next column
			 */
//			if( ( false === $force_close ) && ! empty( avia_sc_columns::$first_atts['min_height'] ) && ( 'av-equal-height-column' ==  avia_sc_columns::$first_atts['min_height'] ) )
			if( ( false === $force_close ) )
			{
				if( ! empty( avia_sc_columns::$first_atts['min_height'] ) || ! empty( avia_sc_columns::$first_atts['mobile_column_order'] ) )
				{
					if( ! isset( $meta['siblings']['next']['tag'] ) )
					{
						$force_close = true;
					}
					else if( ( avia_sc_columns::$calculated_size + avia_sc_columns::$size_array[ $meta['siblings']['next']['tag'] ] ) > 1.0 )
					{
						$force_close = true;
					}
				}
			}

			if( ! empty( avia_sc_columns::$first_atts['min_height'] ) && ( avia_sc_columns::$calculated_size >= 0.95 || $force_close ) )
			{
				$output .= "</div><!--close column table wrapper. Autoclose: {$force_close} -->";
				avia_sc_columns::$calculated_size = 0;
			}
			else if( empty( avia_sc_columns::$first_atts['min_height'] ) && ! empty( avia_sc_columns::$first_atts['mobile_column_order'] ) && ( avia_sc_columns::$calculated_size >= 0.95 || $force_close ) )
			{
				$output .= "</div><!--close flex column wrapper. Autoclose: {$force_close} -->";
				avia_sc_columns::$calculated_size = 0;
			}

			unset( $avia_config['current_column'] );

			return $output;
		}

		/**
		 * Returns the width of the column. As this is the base class for all columns we only need to implement it here.
		 *
		 * @since 4.2.1
		 * @return float
		 */
		public function get_element_width()
		{
			return isset( avia_sc_columns::$size_array[ $this->config['shortcode'] ] ) ? avia_sc_columns::$size_array[ $this->config['shortcode'] ] : 1.0;
		}

		/**
		 * Calculate the grid values for the given column
		 * If a column does not exist we assume fullscreen as fallback
		 * https://github.com/KriesiMedia/wp-themes/issues/3537
		 *
		 * @since 4.8.7.1
		 * @param int $space
		 * @return array
		 */
		protected function calculate_grid_values( $space )
		{
			$grid = array(
						'width'			=> 100,
						'margin-left'	=> 0
					);

			//	set theme default value
			if( ! is_numeric( $space ) )
			{
				$space = 6.0;
			}

			$space = (float) $space;

			$base_size = isset( avia_sc_columns::$base_column_size[ $this->config['shortcode'] ] ) ? avia_sc_columns::$base_column_size[ $this->config['shortcode'] ] : 1;

			if( 1.0 == $base_size )
			{
				return $grid;
			}

			$columns = (int) ( 1.0 / $base_size );
			$placeholders = $columns - 1;

			$base_width = ( 100.0 - $placeholders * $space ) / $columns;
			$columns_count = avia_sc_columns::$columns_count[ $this->config['shortcode'] ];

			//	adjust column width to allow multiple columns (e.g. 2/3, 3/4) in grid
			if( $columns_count > 1 )
			{
				$grid['width'] = ( $base_width * $columns_count ) + ( ( $columns_count - 1 ) * $space );
			}
			else
			{
				$grid['width'] = $base_width;
			}

			$grid[ 'margin-left' ] = $space;

			return $grid;
		}

		/**
		 * Get background image for ALB editor canvas only
		 *
		 * @param array $args
		 * @return string
		 */
		protected function get_bg_string( array $args )
		{
			$style = '';

			if( ! empty( $args['attachment'] ) || ! empty( $args['src'] ) )
			{
				$image = false;

				if( empty( $args['attachment'] ) )
				{
					//	manually added image link
					$image = $args['src'];
				}
				else
				{
					$src = wp_get_attachment_image_src( $args['attachment'], $args['attachment_size'] );
					if( ! empty( $src[0] ) )
					{
						$image = $src[0];
					}
				}

				if( $image )
				{
					$element_styling = new aviaElementStyling( $this, 'xxx' );
//					$bg = ! empty( $args['background_color'] ) ? $args['background_color'] : 'transparent';
					$bg = 'transparent';
					$pos = $element_styling->background_position_string( $args['background_position'], 'center center' );
					$repeat = ! empty( $args['background_repeat'] ) ? $args['background_repeat'] : 'no-repeat';
					$extra = '';

					if( $repeat == 'stretch' )
					{
						$repeat = 'no-repeat';
						$extra = 'background-size: cover;';
					}

					if( $repeat == 'contain' )
					{
						$repeat = 'no-repeat';
						$extra = 'background-size: contain;';
					}

					$style = "background: {$bg} url({$image}) {$repeat} {$pos}; {$extra}";
				}
			}

			return $style;
		}
	}
}



if ( ! class_exists( 'avia_sc_columns_one_half', false ) )
{
	class avia_sc_columns_one_half extends avia_sc_columns
	{

		protected function shortcode_insert_button()
		{
			$this->config['name']		= '1/2';
			$this->config['icon']		= AviaBuilder::$path['imagesURL'] . 'sc-half.png';
			$this->config['tab']		= __( 'Layout Elements', 'avia_framework' );
			$this->config['order']		= 90;
			$this->config['target']		= 'avia-section-drop';
			$this->config['shortcode'] 	= 'av_one_half';
			$this->config['html_renderer'] 	= false;
			$this->config['tooltip'] 	= __( 'Creates a single column with 50&percnt; width', 'avia_framework' );
			$this->config['drag-level'] = 2;
			$this->config['drop-level'] = 2;
			$this->config['tinyMCE'] 	= array(
												'name' => '1/2 + 1/2',
												'instantInsert' => '[av_one_half first]Add Content here[/av_one_half]\n\n\n[av_one_half]Add Content here[/av_one_half]'
											);
			$this->config['id_name']	= 'id';
			$this->config['id_show']	= 'yes';
			$this->config['aria_label']	= 'yes';

			$this->config['base_element']	= 'yes';
			$this->config['name_template']	= __( 'Column Template', 'avia_framework' ) . ' ' . $this->config['name'];
		}
	}
}


if ( ! class_exists( 'avia_sc_columns_one_third', false ) )
{
	class avia_sc_columns_one_third extends avia_sc_columns
	{

		protected function shortcode_insert_button()
		{
			$this->config['name']		= '1/3';
			$this->config['icon']		= AviaBuilder::$path['imagesURL'] . 'sc-third.png';
			$this->config['tab']		= __( 'Layout Elements', 'avia_framework' );
			$this->config['order']		= 80;
			$this->config['target']		= 'avia-section-drop';
			$this->config['shortcode'] 	= 'av_one_third';
			$this->config['html_renderer'] 	= false;
			$this->config['tooltip'] 	= __( 'Creates a single column with 33&percnt; width', 'avia_framework' );
			$this->config['drag-level'] = 2;
			$this->config['drop-level'] = 2;
			$this->config['tinyMCE'] 	= array(
												'name'			=> '1/3 + 1/3 + 1/3',
												'instantInsert'	=> '[av_one_third first]Add Content here[/av_one_third]\n\n\n[av_one_third]Add Content here[/av_one_third]\n\n\n[av_one_third]Add Content here[/av_one_third]'
											);
			$this->config['id_name']	= 'id';
			$this->config['id_show']	= 'yes';
			$this->config['aria_label']	= 'yes';

			$this->config['base_element']	= 'yes';
			$this->config['name_template']	= __( 'Column Template', 'avia_framework' ) . ' ' . $this->config['name'];
		}
	}
}

if ( ! class_exists( 'avia_sc_columns_two_third', false ) )
{
	class avia_sc_columns_two_third extends avia_sc_columns
	{

		protected function shortcode_insert_button()
		{
			$this->config['name']		= '2/3';
			$this->config['icon']		= AviaBuilder::$path['imagesURL'] . 'sc-two_third.png';
			$this->config['tab']		= __( 'Layout Elements', 'avia_framework' );
			$this->config['order']		= 70;
			$this->config['target']		= 'avia-section-drop';
			$this->config['shortcode'] 	= 'av_two_third';
			$this->config['html_renderer'] 	= false;
			$this->config['tooltip'] 	= __( 'Creates a single column with 67&percnt; width', 'avia_framework' );
			$this->config['drag-level'] = 2;
			$this->config['drop-level'] = 2;
			$this->config['tinyMCE'] 	= array(
												'name'			=> '2/3 + 1/3',
												'instantInsert'	=> '[av_two_third first]Add 2/3 Content here[/av_two_third]\n\n\n[av_one_third]Add 1/3 Content here[/av_one_third]'
											);
			$this->config['id_name']	= 'id';
			$this->config['id_show']	= 'yes';
			$this->config['aria_label']	= 'yes';

			$this->config['base_element']	= 'yes';
			$this->config['name_template']	= __( 'Column Template', 'avia_framework' ) . ' ' . $this->config['name'];
		}
	}
}

if ( ! class_exists( 'avia_sc_columns_one_fourth', false ) )
{
	class avia_sc_columns_one_fourth extends avia_sc_columns
	{

		protected function shortcode_insert_button()
		{
			$this->config['name']		= '1/4';
			$this->config['icon']		= AviaBuilder::$path['imagesURL'] . 'sc-fourth.png';
			$this->config['tab']		= __( 'Layout Elements', 'avia_framework' );
			$this->config['order']		= 60;
			$this->config['target']		= 'avia-section-drop';
			$this->config['shortcode'] 	= 'av_one_fourth';
			$this->config['tooltip'] 	= __( 'Creates a single column with 25&percnt; width', 'avia_framework' );
			$this->config['html_renderer'] 	= false;
			$this->config['drag-level'] = 2;
			$this->config['drop-level'] = 2;
			$this->config['tinyMCE'] 	= array(
												'name'			=> '1/4 + 1/4 + 1/4 + 1/4',
												'instantInsert'	=> '[av_one_fourth first]Add Content here[/av_one_fourth]\n\n\n[av_one_fourth]Add Content here[/av_one_fourth]\n\n\n[av_one_fourth]Add Content here[/av_one_fourth]\n\n\n[av_one_fourth]Add Content here[/av_one_fourth]'
											);
			$this->config['id_name']	= 'id';
			$this->config['id_show']	= 'yes';
			$this->config['aria_label']	= 'yes';

			$this->config['base_element']	= 'yes';
			$this->config['name_template']	= __( 'Column Template', 'avia_framework' ) . ' ' . $this->config['name'];
		}
	}
}

if ( ! class_exists( 'avia_sc_columns_three_fourth', false ) )
{
	class avia_sc_columns_three_fourth extends avia_sc_columns
	{

		protected function shortcode_insert_button()
		{
			$this->config['name']		= '3/4';
			$this->config['icon']		= AviaBuilder::$path['imagesURL'] . 'sc-three_fourth.png';
			$this->config['tab']		= __( 'Layout Elements', 'avia_framework' );
			$this->config['order']		= 50;
			$this->config['target']		= 'avia-section-drop';
			$this->config['shortcode'] 	= 'av_three_fourth';
			$this->config['tooltip'] 	= __( 'Creates a single column with 75&percnt; width', 'avia_framework' );
			$this->config['html_renderer'] 	= false;
			$this->config['drag-level'] = 2;
			$this->config['drop-level'] = 2;
			$this->config['tinyMCE'] 	= array(
												'name'			=> '3/4 + 1/4',
												'instantInsert'	=> '[av_three_fourth first]Add 3/4 Content here[/av_three_fourth]\n\n\n[av_one_fourth]Add 1/4 Content here[/av_one_fourth]'
											);
			$this->config['id_name']	= 'id';
			$this->config['id_show']	= 'yes';
			$this->config['aria_label']	= 'yes';

			$this->config['base_element']	= 'yes';
			$this->config['name_template']	= __( 'Column Template', 'avia_framework' ) . ' ' . $this->config['name'];
		}
	}
}

if ( ! class_exists( 'avia_sc_columns_one_fifth', false ) )
{
	class avia_sc_columns_one_fifth extends avia_sc_columns
	{

		protected function shortcode_insert_button()
		{
			$this->config['name']		= '1/5';
			$this->config['icon']		= AviaBuilder::$path['imagesURL'] . 'sc-fifth.png';
			$this->config['tab']		= __( 'Layout Elements', 'avia_framework' );
			$this->config['order']		= 40;
			$this->config['target']		= 'avia-section-drop';
			$this->config['shortcode'] 	= 'av_one_fifth';
			$this->config['html_renderer'] 	= false;
			$this->config['tooltip'] 	= __( 'Creates a single column with 20&percnt; width', 'avia_framework' );
			$this->config['drag-level'] = 2;
			$this->config['drop-level'] = 2;
			$this->config['tinyMCE'] 	= array(
												'name'			=> '1/5 + 1/5 + 1/5 + 1/5 + 1/5',
												'instantInsert'	=> '[av_one_fifth first]1/5[/av_one_fifth]\n\n\n[av_one_fifth]2/5[/av_one_fifth]\n\n\n[av_one_fifth]3/5[/av_one_fifth]\n\n\n[av_one_fifth]4/5[/av_one_fifth]\n\n\n[av_one_fifth]5/5[/av_one_fifth]'
											);
			$this->config['id_name']	= 'id';
			$this->config['id_show']	= 'yes';
			$this->config['aria_label']	= 'yes';

			$this->config['base_element']	= 'yes';
			$this->config['name_template']	= __( 'Column Template', 'avia_framework' ) . ' ' . $this->config['name'];
		}
	}
}

if ( ! class_exists( 'avia_sc_columns_two_fifth', false ) )
{
	class avia_sc_columns_two_fifth extends avia_sc_columns
	{

		protected function shortcode_insert_button()
		{
			$this->config['name']		= '2/5';
			$this->config['icon']		= AviaBuilder::$path['imagesURL'] . 'sc-two_fifth.png';
			$this->config['tab']		= __( 'Layout Elements', 'avia_framework' );
			$this->config['order']		= 39;
			$this->config['target']		= 'avia-section-drop';
			$this->config['shortcode'] 	= 'av_two_fifth';
			$this->config['html_renderer'] 	= false;
			$this->config['tooltip'] 	= __( 'Creates a single column with 40&percnt; width', 'avia_framework' );
			$this->config['drag-level'] = 2;
			$this->config['drop-level'] = 2;
			$this->config['tinyMCE'] 	= array(
												'name'			=> '2/5',
												'instantInsert'	=> '[av_two_fifth first]2/5[/av_two_fifth]'
											);
			$this->config['id_name']	= 'id';
			$this->config['id_show']	= 'yes';
			$this->config['aria_label']	= 'yes';

			$this->config['base_element']	= 'yes';
			$this->config['name_template']	= __( 'Column Template', 'avia_framework' ) . ' ' . $this->config['name'];
		}
	}
}

if ( ! class_exists( 'avia_sc_columns_three_fifth', false ) )
{
	class avia_sc_columns_three_fifth extends avia_sc_columns
	{

		protected function shortcode_insert_button()
		{
			$this->config['name']		= '3/5';
			$this->config['icon']		= AviaBuilder::$path['imagesURL'] . 'sc-three_fifth.png';
			$this->config['tab']		= __( 'Layout Elements', 'avia_framework' );
			$this->config['order']		= 38;
			$this->config['target']		= 'avia-section-drop';
			$this->config['shortcode'] 	= 'av_three_fifth';
			$this->config['html_renderer'] 	= false;
			$this->config['tooltip'] 	= __( 'Creates a single column with 60&percnt; width', 'avia_framework' );
			$this->config['drag-level'] = 2;
			$this->config['drop-level'] = 2;
			$this->config['tinyMCE'] 	= array(
												'name'			=> '3/5',
												'instantInsert'	=> '[av_three_fifth first]3/5[/av_three_fifth]'
											);
			$this->config['id_name']	= 'id';
			$this->config['id_show']	= 'yes';
			$this->config['aria_label']	= 'yes';

			$this->config['base_element']	= 'yes';
			$this->config['name_template']	= __( 'Column Template', 'avia_framework' ) . ' ' . $this->config['name'];
		}
	}
}

if ( ! class_exists( 'avia_sc_columns_four_fifth', false ) )
{
	class avia_sc_columns_four_fifth extends avia_sc_columns
	{

		protected function shortcode_insert_button()
		{
			$this->config['name']		= '4/5';
			$this->config['icon']		= AviaBuilder::$path['imagesURL'] . 'sc-four_fifth.png';
			$this->config['tab']		= __( 'Layout Elements', 'avia_framework' );
			$this->config['order']		= 37;
			$this->config['target']		= 'avia-section-drop';
			$this->config['shortcode'] 	= 'av_four_fifth';
			$this->config['html_renderer'] 	= false;
			$this->config['tooltip'] 	= __( 'Creates a single column with 80&percnt; width', 'avia_framework' );
			$this->config['drag-level'] = 2;
			$this->config['drop-level'] = 2;
			$this->config['tinyMCE'] 	= array(
												'name'			=> '4/5',
												'instantInsert'	=> '[av_four_fifth first]4/5[/av_four_fifth]'
											);
			$this->config['id_name']	= 'id';
			$this->config['id_show']	= 'yes';
			$this->config['aria_label']	= 'yes';

			$this->config['base_element']	= 'yes';
			$this->config['name_template']	= __( 'Column Template', 'avia_framework' ) . ' ' . $this->config['name'];
		}
	}
}

