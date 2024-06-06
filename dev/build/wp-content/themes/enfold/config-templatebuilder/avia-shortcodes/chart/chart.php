<?php
/*
 * Implements a chart based on the js plugin:
 *
 *  - https://www.chartjs.org/
 *  - https://www.chartjs.org/docs/3.9.1/
 *
 * js file is updated in non minified version from:
 *
 *  - https://cdnjs.com/libraries/Chart.js
 *
 * Information to extend for dynamic data:
 *
 *  - https://kriesi.at/documentation/enfold/chart-element/
 *
 * @since 5.3
 */

if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if ( ! class_exists( 'avia_sc_chart', false ) )
{

	class avia_sc_chart extends aviaShortcodeTemplate
	{
		/**
		 * @since 5.3
		 * @var int
		 */
		protected $chart_count;

		/**
		 * @since 5.3
		 * @param \AviaBuilder $builder
		 */
		public function __construct( \AviaBuilder $builder )
		{
			parent::__construct( $builder );

			$this->chart_count = 0;
		}

		/**
		 * Create the config array for the shortcode button
		 *
		 * @since 5.3
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['self_closing']	= 'no';
			$this->config['base_element']	= 'yes';

			$this->config['name']			= __( 'Chart', 'avia_framework' );
			$this->config['tab']			= __( 'Content Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-chart.png';
			$this->config['order']			= 5;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode']		= 'av_chart';
			$this->config['shortcode_nested'] = array( 'av_chart_dataset' );
			$this->config['tooltip']		= __( 'Displays different types of charts based on static user data - or dynamic data with filter', 'avia_framework' );
//			$this->config['drag-level'] 	= 3;
			$this->config['preview'] 		= 'large';
			$this->config['disabling_allowed'] = true;

			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
			$this->config['alb_desc_id']	= 'alb_description';
			$this->config['aria_label']		= 'yes';
			$this->config['name_item']		= __( 'Chart Dataset Item', 'avia_framework' );
			$this->config['tooltip_item']	= __( 'A Chart Dataset Item', 'avia_framework' );
        }

		/**
		 * @since 5.3
		 */
		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
//			$min_css = avia_minify_extension( 'css' );
			$min_js = avia_minify_extension( 'js' );

			//load css
//			wp_enqueue_style( 'avia-module-chart', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/chart/chart{$min_css}.css", array( 'avia-layout' ), $ver );

			//load js     (chart-js.js we must always use .min as enfold compression produces an error when merging - line 478 - not ES6 compatible )
			wp_enqueue_script( 'avia-module-chart-js', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/chart/chart-js.min.js", array( 'avia-shortcodes' ), $ver, true );
			wp_enqueue_script( 'avia-module-chart', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/chart/chart{$min_js}.js", array( 'avia-shortcodes', 'avia-module-chart-js' ), $ver, true );
		}

		/**
		 * Popup Elements
		 *
		 * If this function is defined in a child class the element automatically gets an edit button, that, when pressed
		 * opens a modal window that allows to edit the element properties
		 *
		 * @since 5.3
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
													$this->popup_key( 'content_type' ),
													$this->popup_key( 'content_data' ),
													$this->popup_key( 'content_title' )
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
													$this->popup_key( 'styling_colors' ),
													$this->popup_key( 'styling_spacing' ),
													$this->popup_key( 'styling_tooltip' )
												),
							'nodescription'	=> true
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
								'template_id'	=> 'screen_options_toggle',
								'lockable'		=> true,
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
		 * @since 5.3
		 */
		protected function register_dynamic_templates()
		{
			$this->register_modal_group_templates();

			/**
			 * Content Tab
			 * ===========
			 */

			$desc  = __( 'Select the type of chart for your datasets.', 'avia_framework' ) . ' ';
			$desc .= sprintf( __( 'More information about the different chart types you can find %s here %s.', 'avia_framework' ), '<a href="https://www.chartjs.org/docs/latest/charts/bar.html" target="_blank" rel="noopener noreferrer">', '</a>' ) . ' ';
			$desc .= __( 'Bundled is js version 3.9.1 - so there is no access to any external ressources.', 'avia_framework' ) . '<br /><br />';
			$desc .= sprintf( __( 'Need a chart with dynamic data (e.g. to display currency exchange rates of the last week): Check our %s documentation %s', 'avia_framework' ), '<a href="https://kriesi.at/documentation/enfold/chart-element/#add-dynamic-data-support" target="_blank" rel="noopener noreferrer">', '</a>' );

			$c = array(
						array(
							'name'		=> __( 'Type Of Chart', 'avia_framework' ),
							'desc'		=> $desc,
							'id'		=> 'chart_type',
							'type'		=> 'select',
							'std'		=> 'bar',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Bar Chart', 'avia_framework' )			=> 'bar',
												__( 'Bubble Chart', 'avia_framework' )		=> 'bubble',
												__( 'Doughnut Chart', 'avia_framework' )	=> 'doughnut',
												__( 'Pie Chart', 'avia_framework' )			=> 'pie',
												__( 'Line Chart', 'avia_framework' )		=> 'line',
												__( 'Polar Area Chart', 'avia_framework' )	=> 'polarArea',
												__( 'Radar Chart', 'avia_framework' )		=> 'radar',
												__( 'Scatter Chart', 'avia_framework' )		=> 'scatter'
											)
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Chart Type', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_type' ), $template );

			$desc  = __( 'Here you can add, remove and edit the datasets for your chart.', 'avia_framework' ) . '<br />';
			$desc .= __( 'A dataset consists of a series of datapoints. You may use multiple datasets.', 'avia_framework' );

			$c = array(

						array(
							'name'			=> __( 'Add/Edit Datasets', 'avia_framework' ),
							'desc'			=> $desc,
							'type'			=> 'modal_group',
							'id'			=> 'content',
							'modal_title'	=> __( 'Edit Dataset', 'avia_framework' ),
							'editable_item'	=> true,
							'lockable'		=> true,
							'std'			=> array(
													array(
														'dataset_label'		=> __( 'Dataset 1', 'avia_framework' ),
														'dataset_data'		=> '43;15;27;50'
													),
													array(
														'dataset_label'		=> __( 'Dataset 2', 'avia_framework' ),
														'dataset_data'		=> '28;35;19;20'
													)
												),
							'subelements' 	=> $this->create_modal()
						),

						array(
							'name'		=> __( 'X-Axis Labels', 'avia_framework' ),
							'desc'		=> __( 'Add the descriptive x-axis lables for your data points. Seperate with ;. Number of labels should correspond with number of datapoints. Default labels are created if needed.', 'avia_framework' ),
							'id'		=> 'chart_labels',
							'type'		=> 'textarea',
							'std'		=> 'Point1;Point2;Point3;Point4',
							'lockable'	=> true,
//							'tmpl_set_default'	=> false,
						)

				);


			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Chart Data', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_data' ), $template );


			$c = array(

						array(
							'name'		=> __( 'Chart Title', 'avia_framework' ),
							'desc'		=> __( 'Enter a title for the chart - better keep it short. Leave empty if not needed. Click outside input field to show more options if needed.', 'avia_framework' ),
							'id'		=> 'chart_title',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Chart Subtitle', 'avia_framework' ),
							'desc'		=> __( 'Enter an additional subtitle for the chart - better keep it short. Leave empty if not needed.', 'avia_framework' ),
							'id'		=> 'chart_subtitle',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'chart_title', 'not', '' )
						),

						array(
							'name'		=> __( 'Position Of Chart Title', 'avia_framework' ),
							'desc'		=> __( 'Select position of the titles', 'avia_framework' ),
							'id'		=> 'title_pos',
							'type'		=> 'select',
							'std'		=> 'top',
							'lockable'	=> true,
							'required'	=> array( 'chart_title', 'not', '' ),
							'subtype'	=> array(
												__( 'Top', 'avia_framework' )		=> 'top',
												__( 'Left', 'avia_framework' )		=> 'left',
												__( 'Bottom', 'avia_framework' )	=> 'bottom',
												__( 'Right', 'avia_framework' )		=> 'right'
											)
						)

				);


			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Chart Title, Subtitle, Position', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_title' ), $template );


			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name'		=> __( 'Container Background Color', 'avia_framework' ),
							'desc'		=> __( 'Select a background color for container around the chart area', 'avia_framework' ),
							'id'		=> 'container_background',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Chart Background Color', 'avia_framework' ),
							'desc'		=> __( 'Select a background color for the chart area', 'avia_framework' ),
							'id'		=> 'chart_background',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Title Color', 'avia_framework' ),
							'desc'		=> __( 'Select a color for the chart title. Leave empty for default.', 'avia_framework' ),
							'id'		=> 'title_color',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true,
							'required'	=> array( 'chart_title', 'not', '' )
						),

						array(
							'name'		=> __( 'Subtitle Color', 'avia_framework' ),
							'desc'		=> __( 'Select a color for the chart subtitle. Leave empty for default.', 'avia_framework' ),
							'id'		=> 'subtitle_color',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true,
							'required'	=> array( 'chart_title', 'not', '' )
						),

						array(
							'name'		=> __( 'Legend Text Color', 'avia_framework' ),
							'desc'		=> __( 'Select a color for the legend of the data points. Leave empty for default.', 'avia_framework' ),
							'id'		=> 'legend_color',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'X-Axis Legend Color', 'avia_framework' ),
							'desc'		=> __( 'Select a color for the legend on the x-axis data points. Leave empty for default.', 'avia_framework' ),
							'id'		=> 'x_color',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Y-Axis Legend Color', 'avia_framework' ),
							'desc'		=> __( 'Select a color for the legend on the y-axis values. Leave empty for default.', 'avia_framework' ),
							'id'		=> 'y_color',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true
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
							'template_id'	=> 'margin_padding',
							'name'			=> '',
							'desc'			=> '',
							'content'		=> 'padding',
							'sync_padding'	=> true,
							'name_padding' 	=> __( 'Container Padding', 'avia_framework' ),
							'desc_padding' 	=> __( 'Set the padding for the chart inside the surrounding container. Valid CSS units are accepted, eg: 30px, 5&percnt;. px is used as default unit.', 'avia_framework' ),
							'id_padding'	=> 'container_padding',
							'std_padding'	=> '',
							'lockable'		=> true
						),

						array(
							'name'		=> __( 'Chart Padding', 'avia_framework' ),
							'desc'		=> __( 'Set the padding in px of the chart in the chart area. Only numbers are allowed. Leave empty to use theme default.', 'avia_framework' ),
							'id'		=> 'chart_padding',
							'type'		=> 'multi_input',
							'sync'		=> true,
							'std'		=> '',
							'lockable'	=> true,
							'multi'		=> array(
												'top'		=> __( 'Top Padding', 'avia_framework' ),
												'right'		=> __( 'Right Padding', 'avia_framework' ),
												'bottom'	=> __( 'Bottom Padding', 'avia_framework' ),
												'left'		=> __( 'Left Padding', 'avia_framework' )
											)
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Spacing', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_spacing' ), $template );


			$c = array(

						array(
							'name'		=> __( 'Tooltip On Hover', 'avia_framework' ),
							'desc'		=> __( 'Select to show a tooltip when moving the mouse over the chart', 'avia_framework' ),
							'id'		=> 'tt_pos',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Average', 'avia_framework' )	=> '',
												__( 'Nearest', 'avia_framework' )	=> 'nearest',
												__( 'Disabled', 'avia_framework' )	=> 'disabled'
											)
						),

						array(
							'name'		=> __( 'Color Boxes', 'avia_framework' ),
							'desc'		=> __( 'Select to show color boxes', 'avia_framework' ),
							'id'		=> 'tt_color_box',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'tt_pos', 'not', 'disabled' ),
							'subtype'	=> array(
												__( 'Show', 'avia_framework' )		=> '',
												__( 'Hide', 'avia_framework' )		=> 'hide'
											)
						),

						array(
							'name'		=> __( 'X-Alignment', 'avia_framework' ),
							'desc'		=> __( 'Select the position of the tooltip caret', 'avia_framework' ),
							'id'		=> 'tt_align_x',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'tt_pos', 'not', 'disabled' ),
							'subtype'	=> array(
												__( 'Auto', 'avia_framework' )		=> '',
												__( 'Left', 'avia_framework' )		=> 'left',
												__( 'Center', 'avia_framework' )	=> 'center',
												__( 'Right', 'avia_framework' )		=> 'right'
											)
						),

						array(
							'name'		=> __( 'Y-Alignment', 'avia_framework' ),
							'desc'		=> __( 'Select the position of the tooltip caret', 'avia_framework' ),
							'id'		=> 'tt_align_y',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'tt_pos', 'not', 'disabled' ),
							'subtype'	=> array(
												__( 'Auto', 'avia_framework' )		=> '',
												__( 'Top', 'avia_framework' )		=> 'top',
												__( 'Center', 'avia_framework' )	=> 'center',
												__( 'Bottom', 'avia_framework' )	=> 'bottom'
											)
						),

						array(
							'name'		=> __( 'Background Color', 'avia_framework' ),
							'desc'		=> __( 'Select a color for the background of the tooltip. Leave empty for default.', 'avia_framework' ),
							'id'		=> 'tt_bg_color',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true,
							'required'	=> array( 'tt_pos', 'not', 'disabled' )
						),

						array(
							'name'		=> __( 'Title Text Color', 'avia_framework' ),
							'desc'		=> __( 'Select a color for the title text of the tooltip. Leave empty for default.', 'avia_framework' ),
							'id'		=> 'tt_title_color',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true,
							'required'	=> array( 'tt_pos', 'not', 'disabled' )
						),

						array(
							'name'		=> __( 'Body Text Color', 'avia_framework' ),
							'desc'		=> __( 'Select a color for the body text of the tooltip. Leave empty for default.', 'avia_framework' ),
							'id'		=> 'tt_body_color',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true,
							'required'	=> array( 'tt_pos', 'not', 'disabled' )
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Tooltip', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_tooltip' ), $template );



			/**
			 * Advanced Tab
			 * ===========
			 */



		}

		/**
		 * Creates the modal popup for a single dataset entry
		 *
		 * @since 5.3
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
													$this->popup_key( 'modal_content_data' ),
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
													$this->popup_key( 'modal_styling_chart_colors' )
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

			$desc  = __( 'Add the dataset datapoints. Seperate with ;. Use numeric values for a point (e.g. 5, 5.20)', 'avia_framework' ) . '<br /><br />';
			$desc .= __( '-- Bubble Chart datapoint structure: x/y/r  (e.g. 5/20/3;15/30/2 )', 'avia_framework' ) . '<br />';
			$desc .= __( '-- Scatter Chart datapoint structure: x/y  (e.g. 5/20;10/15 ) ', 'avia_framework' ) . '<br />';

			$c = array(
						array(
							'name'		=> __( 'Dataset Label', 'avia_framework' ),
							'desc'		=> __( 'Define a label for your dataset. Will be displayed above the chart and on hover.', 'avia_framework' ),
							'id'		=> 'dataset_label',
							'type'		=> 'input',
							'std'		=> __( 'My Dataset', 'avia_framework' ),
							'lockable'	=> true,
							'tmpl_set_default'	=> false
						),

						array(
							'name' 	=> __( 'Dataset Datapoints', 'avia_framework' ),
							'desc' 	=> $desc,
							'id' 	=> 'dataset_data',
							'type' 	=> 'textarea',
							'std' 	=> '',
							'lockable'	=> true,
							'tmpl_set_default'	=> false
						)
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_content_data' ), $c );


			/**
			 * Styling Tab
			 * ===========
			 */

			$desc  = __( 'Allowed color formats are string in hexadecimal, RGB, named colors or HSL (e.g. #fefefe, rgba(214,87,153,0.58), green).', 'avia_framework' ) . '<br /><br />';
			$desc .= __( 'Add a color for each datapoint, seperate by ; (e.g. #fefefe;#efefef;green). Missing colors will loop from the beginning again.', 'avia_framework' ) . ' ';
			$desc .= __( 'Depending on the selected chart type not all settings will be used.', 'avia_framework' );


			$c = array(

						array(
							'name'		=> __( 'Color Information', 'avia_framework' ),
							'desc'		=> $desc,
							'type'		=> 'heading'
						),

						array(
							'name'		=> __( 'Color Picker Helper', 'avia_framework' ),
							'desc'		=> __( 'Select a custom color which you can copy into the color settings textareas below.', 'avia_framework' ),
							'id'		=> 'temp_color',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> false
						),

						array(
							'name'		=> __( 'Border/Line Colors', 'avia_framework' ),
							'desc'		=> __( 'Add the border or line colors.', 'avia_framework' ),
							'id'		=> 'border_colors',
							'type'		=> 'textarea',
							'std'		=> '',
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Background Colors', 'avia_framework' ),
							'desc'		=> __( 'Add the background colors.', 'avia_framework' ),
							'id'		=> 'background_colors',
							'type'		=> 'textarea',
							'std'		=> '',
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Border Width/Line Width', 'avia_framework' ),
							'desc'		=> __( 'Select a border or line width.', 'avia_framework' ),
							'id'		=> 'border_width',
							'type'		=> 'select',
							'std'		=> '',
							'subtype'	=> AviaHtmlHelper::number_array( 0, 30, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Border Radius', 'avia_framework' ),
							'desc'		=> __( 'Select a border radius.', 'avia_framework' ),
							'id'		=> 'border_radius',
							'type'		=> 'select',
							'std'		=> '',
							'subtype'	=> AviaHtmlHelper::number_array( 0, 30, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
							'lockable'	=> true
						)

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_styling_chart_colors' ), $c );

		}

		/**
		 * Editor Sub Element - this function defines the visual appearance of an element that is displayed within a modal window and on click opens its own modal window
		 * Works in the same way as Editor Element
		 *
		 * @since 5.3
		 * @param array $params			holds the default values for $content and $args.
		 * @return array				usually holds an innerHtml key that holds item specific markup.
		 */
		public function editor_sub_element( $params )
		{
			$default = array();
			$locked = array();
			$attr = $params['args'];
			Avia_Element_Templates()->set_locked_attributes( $attr, $this, $this->config['shortcode_nested'][0], $default, $locked );

			$template = $this->update_template_lockable( 'dataset_label', __( 'Dataset', 'avia_framework' ). ': {{dataset_label}}', $locked );

			$params['innerHtml']  = '';
			$params['innerHtml'] .=		"<div class='avia_title_container' data-update_element_template='yes'>";
			$params['innerHtml'] .=			"<span {$template} >" . __( 'Dataset', 'avia_framework' ) . ": {$attr['dataset_label']}</span>";
			$params['innerHtml'] .=		'</div>';

			return $params;
		}

		/**
		 * Returns false by default.
		 * Override in a child class if you need to change this behaviour.
		 *
		 * @since 5.3
		 * @param string $shortcode
		 * @return boolean
		 */
		public function is_nested_self_closing( $shortcode )
		{
			if( in_array( $shortcode, $this->config['shortcode_nested'] ) )
			{
				return true;
			}

			return false;
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
							'chart_type'		=> 'bar',
							'chart_labels'		=> ''
						);


			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$datasets = ShortcodeHelper::shortcode2array( $content );

			foreach( $datasets as $key => &$item )
			{
				$item_def = $this->get_default_modal_group_args();
				Avia_Element_Templates()->set_locked_attributes( $item['attr'], $this, $this->config['shortcode_nested'][0], $item_def, $locked, $item['content'] );
			}

			unset( $item );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			$atts['datasets'] = $datasets;


			$element_styling->create_callback_styles( $atts );


			$classes = array(
						'avia-chart-container',
						"av-chart-{$atts['chart_type']}",
						$element_id
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $meta, 'el_class' );
			$element_styling->add_responsive_classes( 'container', 'hide_element', $atts );

			if( ! empty( $atts['container_background'] ) )
			{
				$element_styling->add_styles( 'container', array( 'background-color' => $atts['container_background'] ) );
			}

			if( ! empty( $atts['chart_background'] ) )
			{
				$element_styling->add_styles( 'chart', array( 'background-color' => $atts['chart_background'] ) );
			}

			$element_styling->add_responsive_styles( 'container-icon', 'container_padding', $atts, $this );

			if( ! empty( $atts['container_padding'] ) )
			{
				$element_styling->add_callback_styles( 'container', array( 'container_padding' ) );
			}

			$selectors = array(
							'container'		=> ".avia-chart-container.{$element_id}",
							'chart'			=> ".avia-chart-container.{$element_id} .avia-chart"
				);

			$element_styling->add_selectors( $selectors );

			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['meta'] = $meta;
			$result['content'] = $content;
			$result['element_styling'] = $element_styling;

			return $result;
		}

		/**
		 * Create custom stylings for items
		 * (also called when creating header implicit)
		 *
		 * @since 5.3
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles_item( array $args )
		{
			$result = parent::get_element_styles_item( $args );

			extract( $result );

			$default = array(
							'dataset_label'	=> '',
							'dataset_data'	=> ''
						);

			$default = $this->sync_sc_defaults_array( $default, 'modal_item', 'no_content' );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode_nested'][0] );

			$element_styling->add_selectors( [] );

			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['element_styling'] = $element_styling;

			return $result;
		}

		/**
		 * Frontend Shortcode Handler
		 *
		 * @since 5.3
		 * @param array $atts					array of attributes
		 * @param string $content				text within enclosing form of shortcode element
		 * @param string $shortcodename			the shortcode found, when == callback name
		 * @return string						$output returns the modified html string
		 */
		public function shortcode_handler( $atts, $content = '', $shortcodename = '', $meta = '' )
		{
			$result = $this->get_element_styles( compact( array( 'atts', 'content', 'shortcodename', 'meta' ) ) );

			extract( $result );
			extract( $atts );

			$this->chart_count++;

			$config = $this->get_chart_config( $atts );

			/**
			 * Filter the complete config array for the chart. See https://www.chartjs.org/docs/latest/. Make sure not to break the structure.
			 * Use \stdClass for js {} and array for js [].
			 * see also https://kriesi.at/documentation/enfold/chart-element/
			 *
			 * @since 5.3
			 * @param \stdClass $config
			 * @param array $atts
			 * @param array $meta
			 * @return \stdClass
			 */
			$config = apply_filters( 'avf_chartjs_config_object', $config, $atts, $meta );

			$data_json = json_encode( $config );

			$aria_label = '';
			$fallback = '';

			if( ! empty( $meta['aria_label'] ) )
			{
				$aria_label = " aria-label='{$meta['aria_label']}' ";
				$fallback = '<p>' . esc_html( $meta['aria_label'] ) . '</p>';
			}

			$style_tag = $element_styling->get_style_tag( $element_id );
			$container_class = $element_styling->get_class_string( 'container' );

			$output  = '';
			$output .= $style_tag;
			$output .= "<div {$meta['custom_el_id']} class='avia-chart-{$this->chart_count} {$container_class}' data-chart_config='{$data_json}'>";
			$output .=		"<canvas class='avia-chart' {$aria_label} role='img'>";
			$output .=			$fallback;
			$output .=		'</canvas>';
			$output .= '</div>';

			return $output;
		}

		/**
		 * Returns the config object for the chart ready to encode to json
		 *
		 * @since 5.3
		 * @param array $atts
		 * @return \stdClass
		 */
		protected function get_chart_config( array $atts )
		{
			//	define object to hold settings and convert to json later
			$config = new stdClass();

			$config->type = $atts['chart_type'];

			$config_options = $this->config_options( $atts );

			if( ! \AviaHelper::is_empty( $config_options ) )
			{
				$config->options = $config_options;
			}


			$config_data = new stdClass();

			$config_data->datasets = [];
			$data_points = [];

			foreach( $atts['datasets'] as $index => $dataset )
			{
				$attr = $dataset['attr'];

				$data = \AviaHelper::array_value( $attr, 'dataset_data' );
				$data = array_map( 'trim', explode( ';', $data ) );

				/**
				 * Filter dataset data to allow dynamic data
				 * see docu how to use
				 *
				 * @since 5.3
				 * @param array $data
				 * @param int $index
				 * @param array $atts
				 * @return array
				 */
				$data = apply_filters( 'avf_chart_dataset_data', $data, $index, $atts );


				$dataset_label = \AviaHelper::array_value( $attr, 'dataset_label' );

				/**
				 * Filter dataset label for dataset to allow dynamic data
				 *
				 * @since 5.3
				 * @param string $dataset_label
				 * @param int $index
				 * @param array $atts
				 * @return string
				 */
				$dataset_label = apply_filters( 'avf_chart_dataset_label', trim( $dataset_label ), $index, $atts );


				//	fallback to add missing lables for data points
				$data_points[] = count( $data );

				$data = $this->fix_datapoint_structure( $data, $atts['chart_type'] );

				$background_colors = \AviaHelper::array_value( $attr, 'background_colors' );
				$border_colors = \AviaHelper::array_value( $attr, 'border_colors' );

				if( '' != trim( $background_colors ) )
				{
					$background_colors = array_map( 'trim', explode( ';', $background_colors ) );
				}

				if( '' != trim( $border_colors ) )
				{
					$border_colors = array_map( 'trim', explode( ';', $border_colors ) );
				}


				$set = new stdClass();

				$set->label = $dataset_label;
				$set->data = $data;

				if( ! empty( $background_colors ) )
				{
					$set->backgroundColor = $background_colors;
				}

				if( ! empty( $border_colors ) )
				{
					$set->borderColor = $border_colors;
				}

				if( ! empty( $attr['border_width'] ) && is_numeric( $attr['border_width'] ) )
				{
					$set->borderWidth = (int) $attr['border_width'];
				}

				if( ! empty( $attr['border_radius'] ) && is_numeric( $attr['border_radius'] ) )
				{
					$set->borderRadius = (int) $attr['border_radius'];
				}

				$config_data->datasets[] = $set;
			}

			$chart_labels = array_map( 'trim', explode( ';', $atts['chart_labels'] ) );

			//	fallback - adjust chart labels if missing
			$data_points_max = max( $data_points );
			if( count( $chart_labels ) < $data_points_max )
			{
				if( empty( $chart_labels ) || $chart_labels[0] == '' )
				{
					$chart_labels[0] = 'Point 1';
				}

				for( $i = 0; $i < $data_points_max; $i++ )
				{
					if( ! isset( $chart_labels[ $i ] ) || '' == $chart_labels[ $i ] )
					{
						$chart_labels[ $i ] = 'Point ' . ( $i + 1 );
					}
				}
			}

			/**
			 * Filter chart labels on the x-axis to allow dynamic data
			 *
			 * @since 5.3
			 * @param array $chart_labels
			 * @param array $attr
			 * @return array
			 */
			$config_data->labels = apply_filters( 'avf_chart_labels', $chart_labels, $atts );

			$config->data = $config_data;

			return $config;
		}

		/**
		 * Fix datapoint structure for special chart types with default values.
		 *
		 * @since 5.3
		 * @param array $data_points
		 * @param string $chart_type
		 * @return array
		 */
		protected function fix_datapoint_structure( array $data_points, $chart_type )
		{
			$mod_points = [];

			foreach( $data_points as $data_point )
			{
				$val = array_map( 'trim', explode( '/', $data_point ) );

				if( ! is_numeric( $val[0] ) )
				{
					$val[0] = 0;
				}

				switch( $chart_type )
				{
					case 'bubble':
						if( ! isset( $val[1] ) || ! is_numeric( $val[1] ) )
						{
							$val[1] = $val[0];
						}

						if( ! isset( $val[2] ) || ! is_numeric( $val[2] ) )
						{
							$val[2] = 10;
						}

						$point = new stdClass();
						$point->x = $val[0];
						$point->y = $val[1];
						$point->r = $val[2];
						break;
					case 'scatter':
						if( ! isset( $val[1] ) || ! is_numeric( $val[1] ) )
						{
							$val[1] = $val[0];
						}

						$point = new stdClass();
						$point->x = $val[0];
						$point->y = $val[1];
						break;
					default:
						$point = $val[0];
						break;
				}

				$mod_points[] = $point;
			}

			return $mod_points;
		}

		/**
		 * Fill the options for config->options object
		 *
		 * @since 5.3
		 * @param array $atts
		 * @return \stdClass				can be empty object !!!!
		 */
		protected function config_options( array $atts )
		{
			$options = new stdClass();

			$opt_layout = new stdClass();

			$chart_padding = \AviaHelper::array_value( $atts, 'chart_padding' );
			if( ! \AviaHelper::empty_multi_input( $chart_padding ) )
			{
				$padding = AviaHelper::multi_value_result_lockable( $chart_padding );
				$rules = $padding['fill_with_0_val'];

				$opt_layout->padding = new stdClass();
				$opt_layout->padding->top = str_replace( 'px', '', $rules[0] );
				$opt_layout->padding->right = str_replace( 'px', '', $rules[1] );
				$opt_layout->padding->bottom = str_replace( 'px', '', $rules[2] );
				$opt_layout->padding->left = str_replace( 'px', '', $rules[3] );
			}

			if( ! \AviaHelper::is_empty( $opt_layout ) )
			{
				$options->layout = $opt_layout;
			}

			$opt_plugins = new stdClass();

			$chart_title = \AviaHelper::array_value( $atts, 'chart_title' );
			if( trim( $chart_title ) != '' )
			{
				$title = new stdClass();

				$title->display = true;
				$title->text = esc_html( $chart_title );

				$title_pos = \AviaHelper::array_value( $atts, 'title_pos', 'top', 'not_empty' );

				$title->position = $title_pos;

				$title_color = \AviaHelper::array_value( $atts, 'title_color' );
				if( $title_color != '' )
				{
					$title->color = $title_color;
				}

				$opt_plugins->title = $title;

				$chart_subtitle = \AviaHelper::array_value( $atts, 'chart_subtitle' );

				if( trim( $chart_subtitle ) != '' )
				{
					$sub_title = new stdClass();

					$sub_title->display = true;
					$sub_title->text = esc_html( $chart_subtitle );
					$sub_title->position = $title_pos;

					$subtitle_color = \AviaHelper::array_value( $atts, 'subtitle_color' );
					if( $subtitle_color != '' )
					{
						$sub_title->color = $subtitle_color;
					}

					$opt_plugins->subtitle = $sub_title;
				}
			}

			$tooltip = new stdClass();

			$tt_pos = \AviaHelper::array_value( $atts, 'tt_pos' );
			if( 'disabled' == $tt_pos )
			{
				$tooltip->enabled = false;
			}
			else
			{
				$tooltip->enabled = true;
				$tooltip->position = ! empty( $tt_pos ) ? $tt_pos : 'average';

				$tt_color_box = \AviaHelper::array_value( $atts, 'tt_color_box' );
				$tooltip->displayColors = ( '' === $tt_color_box );

				$tt_align_x = \AviaHelper::array_value( $atts, 'tt_align_x' );
				if( '' != $tt_align_x )
				{
					$tooltip->xAlign = $tt_align_x;
				}

				$tt_align_y = \AviaHelper::array_value( $atts, 'tt_align_y' );
				if( '' != $tt_align_y )
				{
					$tooltip->yAlign = $tt_align_y;
				}

				$tt_bg_color = \AviaHelper::array_value( $atts, 'tt_bg_color' );
				if( '' != $tt_bg_color )
				{
					$tooltip->backgroundColor = $tt_bg_color;
				}

				$tt_title_color = \AviaHelper::array_value( $atts, 'tt_title_color' );
				if( '' != $tt_title_color )
				{
					$tooltip->titleColor = $tt_title_color;
				}

				$tt_body_color = \AviaHelper::array_value( $atts, 'tt_body_color' );
				if( '' != $tt_body_color )
				{
					$tooltip->bodyColor = $tt_body_color;
				}
			}

			if( ! \AviaHelper::is_empty( $tooltip ) )
			{
				$opt_plugins->tooltip = $tooltip;
			}

			$opt_legend = new stdClass();

			$legend_color = \AviaHelper::array_value( $atts, 'legend_color' );

			if( ! empty( $legend_color ) )
			{
				$opt_legend->display = true;
				$opt_legend->labels = new stdClass();
				$opt_legend->labels->color = $legend_color;

				$opt_plugins->legend = $opt_legend;
			}

			if( ! \AviaHelper::is_empty( $opt_plugins ) )
			{
				$options->plugins = $opt_plugins;
			}


			$opt_scales = new stdClass();

			$x_color = \AviaHelper::array_value( $atts, 'x_color' );

			if( ! empty( $x_color ) )
			{
				$opt_scales->x = new stdClass();
				$opt_scales->x->ticks = new stdClass();
				$opt_scales->x->ticks->color = $x_color;
			}

			$y_color = \AviaHelper::array_value( $atts, 'y_color' );

			if( ! empty( $x_color ) )
			{
				$opt_scales->y = new stdClass();
				$opt_scales->y->ticks = new stdClass();
				$opt_scales->y->ticks->color = $y_color;
			}

			if( ! \AviaHelper::is_empty( $opt_scales ) )
			{
				$options->scales = $opt_scales;
			}

			return $options;
		}

	}

}

