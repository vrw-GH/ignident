<?php
/**
 * Progress Bars
 *
 * Creates some progress bars
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_sc_progressbar', false ) )
{
	class avia_sc_progressbar extends aviaShortcodeTemplate
	{
		/**
		 * Stores the values for the default color sets for the progress bars
		 *
		 * @since 5.1
		 * @var array
		 */
		protected $color_sets;
		/**
		 *
		 * @since 4.8.4
		 * @param \AviaBuilder $builder
		 */
		public function __construct( \AviaBuilder $builder )
		{
			parent::__construct( $builder );

			$this->color_sets = array(
						'white'		=> array( '#fff', '#fff', '#ededed' ),
						'grey'		=> array( '#555', '#666', '#444' ),
						'blue'		=> array( '#555', '#96C8FB', '#74a6d9' ),
						'green'		=> array( '#83a846', '#A5CA68', '#83a846' ),
						'orange'	=> array( '#EDAE44', '#EDAE44', '#FFCF66' ),
						'black'		=> array( '#222', '#222', '#444' ),
						'red'		=> array( '#B02B2C', '#B02B2C', '#D24D4E' ),
						'teal'		=> array( '#5F8789', '#5F8789', '#7FA9AB' ),
						'aqua'		=> array( '#4ECAC2', '#4ECAC2', '#6FECE4' ),
						'purple'	=> array( '#745F7E', '#745F7E', '#967F9F' ),
						'pink'		=> array( '#D65799', '#D65799', '#F879BB' ),
						'silver'	=> array( '#D7D7D7', '#D7D7D7', '#F9F9F9' ),
					);
		}

		/**
		 * @since 4.8.4
		 */
		public function __destruct()
		{
			parent::__destruct();

			unset( $this->color_sets );
		}

		/**
		 * Create the config array for the shortcode button
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['self_closing']	= 'no';
			$this->config['base_element']	= 'yes';

			$this->config['name']			= __( 'Progress Bars', 'avia_framework' );
			$this->config['tab']			= __( 'Content Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-progressbar.png';
			$this->config['order']			= 30;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode']	 	= 'av_progress';
			$this->config['shortcode_nested'] = array( 'av_progress_bar' );
			$this->config['tooltip']	 	= __( 'Create some progress bars', 'avia_framework' );
			$this->config['preview']	 	= true;
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
			$this->config['alb_desc_id']	= 'alb_description';
			$this->config['name_item']		= __( 'Progress Bar Item', 'avia_framework' );
			$this->config['tooltip_item']	= __( 'A Progress Bars Element Item', 'avia_framework' );
		}

		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_js = avia_minify_extension( 'js' );
			$min_css = avia_minify_extension( 'css' );

			//load css
			wp_enqueue_style( 'avia-module-progress-bar', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/progressbar/progressbar{$min_css}.css", array( 'avia-layout' ), $ver );

			//load js
			wp_enqueue_script( 'avia-module-numbers', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/numbers/numbers{$min_js}.js", array( 'avia-shortcodes' ), $ver, true );
			wp_enqueue_script( 'avia-module-progress-bar', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/progressbar/progressbar{$min_js}.js", array( 'avia-shortcodes' ), $ver, true );
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
							'template_id'	=> $this->popup_key( 'content_bars' )
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
								'template_id'	=> $this->popup_key( 'advanced_animation' )
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
							'name'			=> __( 'Add/Edit Progress Bars', 'avia_framework' ),
							'desc'			=> __( 'Here you can add, remove and edit the various progress bars.', 'avia_framework' ),
							'type'			=> 'modal_group',
							'id'			=> 'content',
							'modal_title'	=> __( 'Edit Progress Bars', 'avia_framework' ),
							'editable_item'	=> true,
							'lockable'		=> true,
							'tmpl_set_default'	=> false,
							'std'			=> array(
													array(
														'title'			=> __( 'Skill or Task', 'avia_framework' ),
														'icon'			=> '43',
														'progress'		=> '100',
														'icon_select'	=> 'no'
													),
												),
							'subelements'	=> $this->create_modal()
						),

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_bars' ), $c );

			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name' 	=> __( 'Progress Bar Style', 'avia_framework' ),
							'desc' 	=> __( 'Choose the styling of the progress bar here', 'avia_framework' ),
							'id' 	=> 'bar_styling_secondary',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Rounded Big Bars', 'avia_framework' )	=> '',
												__( 'Minimal Bars', 'avia_framework' )		=> 'av-small-bar'
											)
						),

						array(
							'name' 	=> __( 'Show Progress Bar percentage?', 'avia_framework' ),
							'desc' 	=> __( 'Choose if you want to show the numeric percentage of the progress bar', 'avia_framework' ),
							'id' 	=> 'show_percentage',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'required'	=> array( 'bar_styling_secondary', 'equals', 'av-small-bar' ),
							'subtype'	=> array(
												__( 'Hide', 'avia_framework' )	=> '',
												__( 'Show', 'avia_framework' )	=> 'av-show-bar-percentage'
											)
						),

						array(
							'name' 	=> __( 'Progress Bar Height?', 'avia_framework' ),
							'desc' 	=> __( 'Set the height of the progress bar', 'avia_framework' ),
							'id' 	=> 'bar_height',
							'type' 	=> 'select',
							'std' 	=> '10',
							'lockable'	=> true,
							'required'	=> array( 'bar_styling_secondary', 'equals',  'av-small-bar' ),
							'subtype'	=> AviaHtmlHelper::number_array( 1, 50, 1, array(), 'px' )
						),

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
							'name' 	=> __( 'Progress Bar Coloring', 'avia_framework' ),
							'desc' 	=> __( 'Choose the coloring of the progress bar here', 'avia_framework' ),
							'id' 	=> 'bar_styling',
							'type' 	=> 'select',
							'std' 	=> 'av-striped-bar',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Striped', 'avia_framework' )		=> 'av-striped-bar',
												__( 'Single Color', 'avia_framework' )	=> 'av-flat-bar'
											)
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
							'name' 	=> __( 'Progress Bar Animation enabled?', 'avia_framework' ),
							'desc' 	=> __( 'Choose if you want to enable the continuous animation of the progress bar', 'avia_framework' ),
							'id' 	=> 'bar_animation',
							'type' 	=> 'select',
							'std' 	=> 'av-animated-bar',
							'lockable'	=> true,
							'required'	=> array( 'bar_styling', 'not', 'av-flat-bar' ),
							'subtype'	=> array(
												__( 'Enabled', 'avia_framework' )	=> 'av-animated-bar',
												__( 'Disabled', 'avia_framework' )	=> 'av-fixed-bar'
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
							'template_id'	=> $this->popup_key( 'modal_content_bar' )
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
							'template_id'	=> $this->popup_key( 'modal_styling_colors' )
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
							'name'		=> __( 'Progress Bars Title', 'avia_framework' ),
							'desc'		=> __( 'Enter the Progress Bars title here', 'avia_framework' ) ,
							'id'		=> 'title',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Progress in &percnt;', 'avia_framework' ),
							'desc'		=> __( 'Select a number between 0 and 100', 'avia_framework' ),
							'id'		=> 'progress',
							'type'		=> 'select',
							'std'		=> '100',
							'lockable'	=> true,
							'subtype'	=> AviaHtmlHelper::number_array( 0, 100, 1, array(), '%' )
						),

						array(
							'name'		=> __( 'Icon', 'avia_framework' ),
							'desc'		=> __( 'Should an icon be displayed at the left side of the progress bar', 'avia_framework' ),
							'id'		=> 'icon_select',
							'type'		=> 'select',
							'std'		=> 'no',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'No Icon', 'avia_framework' )			=> 'no',
												__( 'Yes, display Icon', 'avia_framework' )	=> 'yes'
											)
						),

						array(
							'name'		=> __( 'List Item Icon','avia_framework' ),
							'desc'		=> __( 'Select an icon for your list item below','avia_framework' ),
							'id'		=> 'icon',
							'type'		=> 'iconfont',
							'required'	=> array( 'icon_select', 'equals', 'yes' ),
							'std'		=> '',
							'lockable'	=> true,
							'locked'	=> array( 'icon', 'font' )
						),

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_content_bar' ), $c );

			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'named_colors',
							'name'			=> __( 'Progress Bar Color', 'avia_framework' ),
							'desc'			=> __( 'Choose a color for your progress bar here', 'avia_framework' ),
							'id'			=> 'color',
							'std'			=> 'theme-color',
							'custom'		=> true,
							'lockable'		=> true,
							'translucent'	=> array(),
							'no_alternate'	=> true
						),

						array(
							'name' 	=> __( 'Custom Progress Bar Background Color', 'avia_framework' ),
							'desc' 	=> __( 'This color is used as the main background color for the progress bar.', 'avia_framework' ),
							'id' 	=> 'color1',
							'type' 	=> 'colorpicker',
							'std' 	=> '',
							'rgba' 	=> true,
							'lockable'	=> true,
							'required'	=> array( 'color', 'equals', 'custom' )
						),

						array(
							'name' 	=> __( 'Custom Progress Bar Striped Color', 'avia_framework' ),
							'desc' 	=> __( 'This color is used as the striped color for the progress bar. Only used when option striped bars is selected.', 'avia_framework' ),
							'id' 	=> 'color2',
							'type' 	=> 'colorpicker',
							'std' 	=> '',
							'rgba' 	=> true,
							'lockable'	=> true,
							'required'	=> array( 'color', 'equals', 'custom' )
						),

						array(
							'name' 	=> __( 'Progress Container Background Color', 'avia_framework' ),
							'desc' 	=> __( 'This color fills up the progress container to 100 &percnt;. Leave empty for theme default.', 'avia_framework' ),
							'id' 	=> 'color3',
							'type' 	=> 'colorpicker',
							'std' 	=> '',
							'rgba' 	=> true,
							'lockable'	=> true
						),

						array(
							'name' 	=> __( 'Title Font Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font color for title, icon and progress bar percentage. Leave empty for theme default.', 'avia_framework' ),
							'id' 	=> 'color_title',
							'type' 	=> 'colorpicker',
							'std' 	=> '',
							'rgba' 	=> true,
							'lockable'	=> true
						),

						array(
							'name' 	=> __( 'Title Background Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom color for background of the title, icon and progress bar percentage. Leave empty for theme default.', 'avia_framework' ),
							'id' 	=> 'color_title_back',
							'type' 	=> 'colorpicker',
							'std' 	=> '',
							'rgba' 	=> true,
							'lockable'	=> true
						)

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_styling_colors' ), $c );

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


			$template = $this->update_template_lockable( 'title', '{{title}}: ', $locked );
			$template_percent = $this->update_template_lockable( 'progress', '{{progress}}%', $locked );

			extract( av_backend_icon( array( 'args' => $attr ) ) ); // creates $font and $display_char if the icon was passed as param 'icon' and the font as 'font'

			if( empty( $attr['icon_select'] ) )
			{
				$params['args']['icon_select'] = 'no';
				$attr['icon_select'] = 'no';
			}

			$params['innerHtml']  = '';
			$params['innerHtml'] .= "<div class='avia_title_container' data-update_element_template='yes'>";
			$params['innerHtml'] .=		'<span ' . $this->class_by_arguments_lockable( 'icon_select', $attr, $locked ) . '>';
			$params['innerHtml'] .=			'<span ' . $this->class_by_arguments_lockable( 'font', $font, $locked ) . '>';
			$params['innerHtml'] .=				'<span ' . $this->update_option_lockable( array( 'icon', 'icon_fakeArg' ), $locked ) . " class='avia_tab_icon'>{$display_char}</span>";
			$params['innerHtml'] .=			'</span>';
			$params['innerHtml'] .=			"<span {$template} >{$attr['title']}: </span>";
			$params['innerHtml'] .=			"<span {$template_percent} >{$attr['progress']}%</span>";
			$params['innerHtml'] .=		'</span>';
			$params['innerHtml'] .= '</div>';

			return $params;
		}


		/**
		 * Returns false by default.
		 * Override in a child class if you need to change this behaviour.
		 *
		 * @since 4.2.1
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
		 * Override base class - we have global attributes here
		 *
		 * @since 4.8.4
		 * @return boolean
		 */
		public function has_global_attributes()
		{
			return true;
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
						'position'				=> 'left',
						'bar_styling'			=> 'av-striped-bar',
						'bar_styling_secondary'	=> '',
						'show_percentage'		=> '',
						'bar_height'			=> 10,
						'bar_animation'			=> 'av-animated-bar',

						'bars'					=> array()			//	prepared bars - updated with CET content
					);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$bars = ShortcodeHelper::shortcode2array( $content );

			foreach( $bars as &$bar )
			{
				$item_def = $this->get_default_modal_group_args();
				Avia_Element_Templates()->set_locked_attributes( $bar['attr'], $this, $this->config['shortcode_nested'][0], $item_def, $locked, $bar['content'] );
			}

			unset( $bar );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			$atts['bars'] = $bars;

			/**
			 * @since 5.3
			 * @param string $class_animation
			 * @param array $atts
			 * @param aviaShortcodeTemplate $this
			 * @param string $shortcodename
			 * @return string
			 */
			$class_animation = apply_filters( 'avf_alb_element_animation', 'avia_animate_when_almost_visible', $atts, $this, $shortcodename );


			$classes = array(
						'avia-progress-bar-container',
						$element_id,
						$atts['bar_styling'],
						$atts['bar_animation'],
						$atts['bar_styling_secondary'],
						$class_animation
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $meta, 'el_class' );
			$element_styling->add_responsive_classes( 'container', 'hide_element', $atts );

			if( ! empty( $atts['bar_styling_secondary'] ) )
			{
				$element_styling->add_styles( 'container-bar', array( 'height' => $atts['bar_height'] . 'px' ) );
			}


			$selectors = array(
							'container'		=> ".avia-progress-bar-container.{$element_id}",
							'container-bar'	=> ".avia-progress-bar-container.{$element_id} .progress",
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
		 * @since 4.8.4
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles_item( array $args )
		{
			//	get settings from container element and remove to get correct element id (override self::has_global_attributes() to activate)
			$parent_atts = isset( $args['atts']['parent_atts'] ) ? $args['atts']['parent_atts'] : null;
			unset( $args['atts']['parent_atts'] );

			$result = parent::get_element_styles_item( $args );

			extract( $result );

			if( is_null( $parent_atts ) )
			{
				$parent_atts = $this->parent_atts;
			}

			$default = array(
						'color'			=> 'theme-color',
						'progress'		=> '100',
						'title'			=> '',
						'icon'			=> '',
						'font'			=> '',
						'icon_select'	=> 'no'
					);


			$default = $this->sync_sc_defaults_array( $default, 'modal_item', 'no_content' );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode_nested'][0] );

			$classes = array(
						'avia-progress-bar',
						$element_id,
						'icon-bar-' . $atts['icon_select']
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $meta, 'el_class' );

			$element_styling->add_styles( 'progress-bar', array( 'width' => $atts['progress'] . '%' ) );

			$colors = array();

			if( isset( $this->color_sets[ $atts['color'] ] ) )
			{
				$colors = $this->color_sets[ $atts['color'] ];
			}
			else if( 'custom' == $atts['color'] )
			{
				if( ! empty( $atts['color1'] ) )
				{
					$colors[] = $atts['color1'];
					$colors[] = $atts['color1'];
					$colors[] = ! empty( $atts['color2'] ) ? $atts['color2'] : '#444';
				}
			}

			if( ! empty( $colors ) && ! empty( $atts['color'] ) && 'theme-color' != $atts['color'] )
			{
				if( 'av-striped-bar' != $parent_atts['bar_styling'] )
				{
					$element_styling->add_styles( 'progress-bar', array( 'background' => $colors[0] ) );
				}
				else
				{
					$rule = "( -45deg, {$colors[1]} 25%, {$colors[2]} 25%, {$colors[2]} 50%, {$colors[1]} 50%, {$colors[1]} 75%, {$colors[2]} 75%, {$colors[2]} )";

//					$element_styling->add_styles( 'progress-bar', array( 'background-image{{prefix:-webkit-;-moz-}}' => "linear-gradient{$rule}" ) );
					$element_styling->add_styles( 'progress-bar', array( 'background-image{{prefix:}}' => "linear-gradient{$rule}" ) );
					$element_styling->add_styles( 'progress-bar', array( 'background-color' => $colors[1] ) );
				}
			}
			else
			{
				$element_styling->add_classes( 'container', 'theme-color-bar' );
			}

			$element_styling->add_styles( 'progress', array( 'background-color' => $atts['color3'] ) );

			$element_styling->add_styles( 'title-wrap', array( 'color' => $atts['color_title'] ) );
			$element_styling->add_styles( 'percent-wrap', array( 'color' => $atts['color_title'] ) );

			if( empty( $parent_atts['bar_styling_secondary'] ) )
			{
				$element_styling->add_styles( 'title-wrap', array( 'background-color' => $atts['color_title_back'] ) );
			}
			else
			{
				$element_styling->add_styles( 'container', array( 'background-color' => $atts['color_title_back'] ) );
			}

			$selectors = array(
						'container'		=> ".avia-progress-bar-container .avia-progress-bar.{$element_id}",
						'progress'		=> ".avia-progress-bar-container .avia-progress-bar.{$element_id} .progress",
						'progress-bar'	=> "#top .avia-progress-bar-container .avia-progress-bar.{$element_id} .bar",
						'title-wrap'	=> "#top .avia-progress-bar-container .avia-progress-bar.{$element_id} .progressbar-title-wrap",
						'percent-wrap'	=> "#top .avia-progress-bar-container .avia-progress-bar.{$element_id} .progressbar-percent",
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

			if( empty( $result['atts']['bars'] ) )
			{
				return '';
			}

			extract( $result );
			extract( $atts );
			$this->parent_atts = $atts;


			$bar_html = '';

			foreach( $bars as $bar )
			{
				$bar_html .= $this->build_bar_html( $bar );
			}


			$style_tag = $element_styling->get_style_tag( $element_id );
			$item_tag = $element_styling->style_tag_html( $this->subitem_inline_styles, 'sub-' . $element_id );
			$container_class = $element_styling->get_class_string( 'container' );

			$output  = '';
			$output .= $style_tag;
			$output .= $item_tag;
			$output .= "<div {$meta['custom_el_id']} class='{$container_class}'>";
			$output .=		$bar_html;
			$output .= '</div>';

			return $output;
		}

		/**
		 * Create a single progress bar HTML and set stylings
		 *
		 * @since 4.8.4
		 * @param array $bar
		 * @return string
		 */
		protected function build_bar_html( array $bar )
		{
			//	init parameters for normal shortcode handler
			$atts = $bar['attr'];
			$content = $bar['content'];
			$shortcodename = $this->config['shortcode_nested'][0];


			$result = $this->get_element_styles_item( compact( array( 'atts', 'content', 'shortcodename' ) ) );

			extract( $result );

			extract( $atts );



			$display_char = av_icon( $atts['icon'], $atts['font'] );


			$this->subitem_inline_styles .= $element_styling->get_style_tag( $element_id, 'rules_only' );
			$container_class = $element_styling->get_class_string( 'container' );

			$bar_html = '';
			$bar_html .= "<div class='{$container_class} {$atts['color']}-bar icon-bar-{$atts['icon_select']}'>";

			if( $atts['icon_select'] == 'yes' || $atts['title'] )
			{
				$bar_html .= '<div class="progressbar-title-wrap">';
				$bar_html .=		"<div class='progressbar-icon'><span class='progressbar-char' {$display_char}></span></div>";
				$bar_html .=		"<div class='progressbar-title'>{$atts['title']}</div>";
				$bar_html .= '</div>';
			}

			if( ! empty( $this->parent_atts['bar_styling_secondary']) && ! empty( $this->parent_atts['show_percentage'] ) )
			{
				$bar_html .= '<div class="progressbar-percent" data-timer="2200">';
				$bar_html .=	"<span class='av-bar-counter __av-single-number' data-number='{$atts['progress']}'>0</span>%";
				$bar_html .= '</div>';
			}

			$bar_html .= 	'<div class="progress">';
			$bar_html .=		'<div class="bar-outer">';
			$bar_html .=			"<div class='bar' data-progress='{$atts['progress']}'></div>";
			$bar_html .=		'</div>';
			$bar_html .=	'</div>';

			$bar_html .= '</div>';

			return $bar_html;
		}
	}
}
