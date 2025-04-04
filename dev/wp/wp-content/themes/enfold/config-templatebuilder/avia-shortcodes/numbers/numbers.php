<?php
/**
 * Animated Numbers
 *
 * Display Numbers that count from 0 to the number you entered
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_sc_animated_numbers', false ) )
{
	class avia_sc_animated_numbers extends aviaShortcodeTemplate
	{
		use \aviaBuilder\traits\modalIconfontHelper;

		/**
		 * Create the config array for the shortcode button
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['self_closing']	= 'no';
			$this->config['base_element']	= 'yes';

			$this->config['name']			= __( 'Animated Numbers', 'avia_framework' );
			$this->config['tab']			= __( 'Content Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-numbers.png';
			$this->config['order']			= 15;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode']		= 'av_animated_numbers';
			$this->config['tooltip']		= __( 'Display an animated Number with subtitle', 'avia_framework' );
			$this->config['preview']		= true;
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
		}


		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_js = avia_minify_extension( 'js' );
			$min_css = avia_minify_extension( 'css' );

			//load css
			wp_enqueue_style( 'avia-module-numbers', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/numbers/numbers{$min_css}.css", array( 'avia-layout' ), $ver );

			//load js
			wp_enqueue_script( 'avia-module-numbers', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/numbers/numbers{$min_js}.js", array( 'avia-shortcodes' ), $ver, true );
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
								'template_id'	=> $this->popup_key( 'content_number' )
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
													$this->popup_key( 'styling_appearance' ),
													$this->popup_key( 'styling_circle' ),
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
								'template_id'	=> $this->popup_key( 'advanced_animation' )
							),

						array(
								'type'			=> 'template',
								'template_id'	=> $this->popup_key( 'advanced_link' )
							),

						array(
								'type'			=> 'template',
								'template_id'	=> 'effects_toggle',
								'lockable'		=> true,
								'subtypes'		=> array( 'shadow' ),
								'include'		=> array( 'sonar_effect' )
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
							'name'		=> __( 'Number', 'avia_framework' ),
							'desc'		=> __( 'Add a number here. It will be animated. You can also add non numerical characters. Valid examples: 24/7, 50.45, 99.9$, 90&percnt;, 35k, 200mm etc. Leading 0 will be kept, separated numbers will be animated individually.', 'avia_framework' ),
							'id'		=> 'number',
							'type'		=> 'input',
							'std'		=> __( '100', 'avia_framework' ),
							'lockable'	=> true,
							'dynamic'	=> []
						),

						array(
							'name'		=> __( 'Start animation value', 'avia_framework' ),
							'desc'		=> __( 'Add a number here to start the animation. Leave blank to start from 0. Only use numerical characters for a valid integer number.', 'avia_framework' ),
							'id'		=> 'start_from',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true,
							'dynamic'	=> []
						),

						array(
							'name'		=> __( 'Description', 'avia_framework' ),
							'desc'		=> __( 'Add some content to be displayed below the number', 'avia_framework' ),
							'id'		=> 'content',
							'type'		=> 'textarea',
							'std'		=> __( 'Add your own text', 'avia_framework' ),
							'lockable'	=> true,
							'dynamic'	=> []
						),

						array(
							'name'		=> __( 'Icon', 'avia_framework' ),
							'desc'		=> __( 'Add an icon to the element?', 'avia_framework' ),
							'id'		=> 'icon_select',
							'type'		=> 'select',
							'std'		=> 'no',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'No Icon', 'avia_framework' )	=> 'no',
												__( 'Yes, display an icon in front of number', 'avia_framework' )	=> 'av-icon-before',
												__( 'Yes, display an icon after the number', 'avia_framework' )		=> 'av-icon-after'
											)
						),

						array(
							'name'		=> __( 'Icon', 'avia_framework' ),
							'desc'		=> __( 'Select an icon for the number here', 'avia_framework' ),
							'id'		=> 'icon',
							'type'		=> 'iconfont',
							'std'		=> 'back-in-time',
							'std_font'	=> 'svg_entypo-fontello',
							'svg_sets'	=> 'yes',
							'lockable'	=> true,
							'locked'	=> array( 'icon', 'font' ),
							'required'	=> array( 'icon_select', 'not', 'no' )
						)
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_number' ), $c );

			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name' 	=> __( 'Format Number', 'avia_framework' ),
							'desc' 	=> __( 'Select the thousands separator', 'avia_framework' ),
							'id' 	=> 'number_format',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'No thousands separator', 'avia_framework' )	=> '',
												__( '123.350', 'avia_framework' )					=> '.',
												__( '123,350', 'avia_framework' )					=> ',',
												__( '123 350', 'avia_framework' )					=> ' '
											)
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Appearance', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_appearance' ), $template );


			$c = array(

						array(
							'name'		=> __( 'Display Circle', 'avia_framework' ),
							'desc'		=> __( 'Do you want to display a circle around the animated number?', 'avia_framework' ),
							'id'		=> 'circle',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'No', 'avia_framework' )	=> '',
												__( 'Yes', 'avia_framework' )	=> 'yes'
											)
						),

						array(
							'name'		=> __( 'Display Circle', 'avia_framework' ),
							'desc'		=> __( 'The circle may overlap other elements when not used inside column elements. This is a known restriction. Add spacing around the Animated Number element might help to prevent that depending on your layout.', 'avia_framework' ),
							'type'		=> 'heading',
							'required'	=> array( 'circle', 'not', '' ),
						),

						array(
							'name'		=> __( 'Circle Appearance', 'avia_framework' ),
							'desc'		=> __( 'Define the appearance of the circle here.', 'avia_framework' ),
							'id'		=> 'circle_custom',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'circle', 'not', '' ),
							'subtype'	=> array(
												__( 'Default', 'avia_framework' )	=> '',
												__( 'Custom', 'avia_framework' )	=> 'custom'
											)
						),

						array(
							'name'		=> __( 'Circle Border Style', 'avia_framework' ),
							'desc'		=> __( 'Choose the border style for your circle here', 'avia_framework' ),
							'id'		=> 'circle_border_style',
							'type'		=> 'select',
							'std'		=> 'none',
							'container_class' => 'av_third av_third_first',
							'lockable'	=> true,
							'required'	=> array( 'circle_custom', 'not', '' ),
							'subtype'	=> AviaPopupTemplates()->get_border_styles_options()
						),

						array(
							'name'		=> __( 'Circle Border Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom border color. Leave empty to use the default', 'avia_framework' ),
							'id'		=> 'circle_border_color',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'container_class' => 'av_third',
							'lockable'	=> true,
							'required'	=> array( 'circle_custom', 'not', '' )
						),

						array(
							'name'		=> __( 'Circle Backgound Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom background color. Leave empty to use the default', 'avia_framework' ),
							'id'		=> 'circle_bg_color',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'container_class' => 'av_third',
							'rgba'		=> true,
							'lockable'	=> true,
							'required'	=> array( 'circle_custom', 'not', '' )
						),

						array(
							'name'		=> __( 'Border Width', 'avia_framework' ),
							'desc'		=> __( 'Select a custom border width for the circle', 'avia_framework' ),
							'id'		=> 'circle_border_width',
							'type'		=> 'select',
							'std'		=> '',
							'container_class' => 'av_third av_third_first',
							'lockable'	=> true,
							'required' 	=> array( 'circle_custom', 'not', '' ),
							'subtype'	=> AviaHtmlHelper::number_array( 1, 30, 1, array( __( 'Default Width', 'avia_framework' ) => '' ), 'px'  ),
						),

						array(
							'name'		=> __( 'Circle Size', 'avia_framework' ),
							'desc'		=> __( 'Define the size of the circle', 'avia_framework' ),
							'id'		=> 'circle_size',
							'type'		=> 'select',
							'std'		=> '',
							'container_class' => 'av_third',
							'lockable'	=> true,
							'required'	=> array( 'circle_custom', 'not', '' ),
							'subtype'	=> AviaHtmlHelper::number_array( 10, 120, 5, array( __( 'Default Size', 'avia_framework' ) => '' ), '%' ),
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'box_shadow',
							'lockable'		=> true,
							'required'		=> array( 'circle_custom', 'not', '' ),
							'names'			=> array(
													__( 'Circle Shadow', 'avia_framework' ),
													__( 'Circle Shadow Styling', 'avia_framework' ),
													__( 'Circle Shadow Color', 'avia_framework' )
											)
						),

						array(
							'name'		=> __( 'Mobile Behaviour', 'avia_framework' ),
							'desc'		=> __( 'Select to hide circle on mobile devices (when colums switch to fullwidth)', 'avia_framework' ),
							'id'		=> 'circle_mobile',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'circle', 'not', '' ),
							'subtype'	=> array(
												__( 'Always show', 'avia_framework' )							=> '',
												__( 'Hide on screens smaller than 767px', 'avia_framework' )	=> 'small',
										)
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Circle', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_circle' ), $template );


			$c = array(
						array(
							'name'			=> __( 'Number Font Sizes', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the numbers.', 'avia_framework' ),
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'lockable'		=> true,
							'textfield'		=> true,
							'subtype'		=> array(
												'default'	=> AviaHtmlHelper::number_array( 10, 100, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'desktop'	=> AviaHtmlHelper::number_array( 10, 100, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'medium'	=> AviaHtmlHelper::number_array( 10, 60, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 10, 60, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 10, 60, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
											),
							'id_sizes'		=> array(
												'default'	=> 'font_size',
												'desktop'	=> 'av-desktop-font-size-number',
												'medium'	=> 'av-medium-font-size-number',
												'small'		=> 'av-small-font-size-number',
												'mini'		=> 'av-mini-font-size-number'
											)
						),

						array(
							'name'			=> __( 'Description Text Font Size', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the text.', 'avia_framework' ),
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'lockable'		=> true,
							'textfield'		=> true,
							'subtype'		=> array(
												'default'	=> AviaHtmlHelper::number_array( 10, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'desktop'	=> AviaHtmlHelper::number_array( 10, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'medium'	=> AviaHtmlHelper::number_array( 10, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 10, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 10, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
											),
							'id_sizes'		=> array(
												'default'	=> 'font_size_description',
												'desktop'	=> 'av-desktop-font-size-text',
												'medium'	=> 'av-medium-font-size-text',
												'small'		=> 'av-small-font-size-text',
												'mini'		=> 'av-mini-font-size-text'
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
							'name'		=> __( 'Font colors', 'avia_framework' ),
							'desc'		=> __( 'You can use the default font colors or use a custom font colors for the element (in case you use a background image for example)', 'avia_framework' ),
							'id'		=> 'color',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Default', 'avia_framework' )	=> '',
												__( 'Light', 'avia_framework' )		=> 'font-light',
												__( 'Dark', 'avia_framework' )		=> 'font-dark',
												__( 'Custom', 'avia_framework' )	=> 'font-custom'
											),
						),

						array(
							'name'		=> __( 'Numbers Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom color for your numbers here. Leave blank to use default.', 'avia_framework' ),
							'id'		=> 'custom_color',
							'type'		=> 'colorpicker',
							'std'		=> '#444444',
							'rgba'		=> true,
							'lockable'	=> true,
							'required'	=> array( 'color', 'equals', 'font-custom' )
						),

						array(
							'name'		=> __( 'Description Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom color for your description here. Leave blank to use default.', 'avia_framework' ),
							'id'		=> 'custom_color_desc',
							'type'		=> 'colorpicker',
							'std'		=> '#444444',
							'rgba'		=> true,
							'lockable'	=> true,
							'required'	=> array( 'color', 'equals', 'font-custom' )
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Colors', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_colors' ), $template );


			/**
			 * Advanced Tab
			 * ============
			 */

			$c = array(
						array(
							'name' 	=> __( 'Animation Duration', 'avia_framework' ),
							'desc' 	=> __( 'For large numbers higher values allow to slow down the animation from 0 to the given value. For smaller numbers minimum speed depends on the refresh cycle of the client screen.', 'avia_framework' ),
							'id' 	=> 'timer',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype' => AviaHtmlHelper::number_array( 1, 600, 1, array( 'Default (3)' => '' ) ),
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Animation', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_animation' ), $template );

			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'linkpicker_toggle',
							'name'			=> __( 'Link Setting', 'avia_framework' ),
							'desc'			=> __( 'Do you want to apply a link to the element?', 'avia_framework' ),
							'lockable'		=> true,
							'subtypes'		=> array( 'no', 'manually', 'single', 'taxonomy' ),
							'title_attr'	=> true,
							'dynamic'		=> [ 'wp_custom_field' ],
							'dynamic_clear'	=> true
						)
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_link' ), $c );

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

			extract( avia_font_manager::backend_icon( array( 'args' => $attr ) ) ); // creates $font and $display_char if the icon was passed as param 'icon' and the font as 'font'

			$char = '';
			$char .= '<span ' . $this->class_by_arguments_lockable( 'font', $font, $locked ) . '>';
			$char .=	'<span ' . $this->update_option_lockable( array( 'icon', 'icon_fakeArg' ), $locked ) . " class='avia_big_numbers_icon'>{$display_char}</span>";
			$char .= '</span>';

			$inner  = "<div class='avia_iconbox avia_big_numbers avia_textblock avia_textblock_style avia_center_text' data-update_element_template='yes'>";
			$inner .=		'<div ' . $this->class_by_arguments_lockable( 'icon_select', $attr, $locked ) . '>';
			$inner .=			'<h2>';
			$inner .=				"<span class='avia_big_numbers_icon_before'>{$char}</span> ";
			$inner .=				'<span ' . $this->update_option_lockable( 'number', $locked ) . '>' . html_entity_decode( $attr['number'] ) . '</span>';
			$inner .=				" <span class='avia_big_numbers_icon_after'>{$char}</span>";
			$inner .=			'</h2>';
			$inner .=			'<div class="" ' . $this->update_option_lockable( 'content', $locked ) . '>' . stripslashes( wpautop( trim( html_entity_decode( $content ) ) ) ) . '</div>';
			$inner .=		'</div>';
			$inner .= '</div>';

			$params['innerHtml'] = $inner;
			$params['class'] = '';

			return $params;
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
						'number'			=> '100',
						'start_from'		=> '',
						'number_format'		=> '',
						'timer'				=> '',			//	defaults to 3 - set in numbers.js
						'icon'				=> '1',
						'position'			=> 'left',
						'link'				=> '',
						'linktarget'		=> '',
						'color'				=> '',
						'custom_color'		=> '',
						'custom_color_desc'	=> '',
						'icon_select'		=> '',
						'icon'				=> 'no',
						'font'				=> '',
						'font_size'			=> '',
						'font_size_description'	=> '',
						'circle'			=> '',
						'circle_custom'		=> '',
						'circle_border_color'	=> '',
						'circle_bg_color'		=> '',
						'circle_border_width'	=> '',
						'circle_size'	=> '',
						'circle_mobile'	=> ''
					);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			Avia_Dynamic_Content()->read( $atts, $this, $shortcodename, $content );
			$atts['link'] = Avia_Dynamic_Content()->check_link( $atts['link_dynamic'], $atts['link'] );

			avia_font_manager::switch_to_svg( $atts['font'], $atts['icon'] );
			

			$atts['start_from'] = ! empty( $atts['start_from'] ) && is_numeric( $atts['start_from'] ) ? (int) $atts['start_from'] : 0;
			$atts['timer'] = ! empty( $atts['timer'] ) ? (int) $atts['timer'] * 1000 : 3000;

			//	backwards comp. - prepare responsive font sizes for media query
			$atts['size-number'] = $atts['font_size'];
			$atts['size-text'] = $atts['font_size_description'];

			/**
			 * @since 5.3
			 * @param string $class_animation
			 * @param array $atts
			 * @param aviaShortcodeTemplate $this
			 * @param string $shortcodename
			 * @return string
			 */
			$class_animation = apply_filters( 'avf_alb_element_animation', 'avia_animate_when_visible', $atts, $this, $shortcodename );

			$element_styling->create_callback_styles( $atts );

			extract( $atts );

			$classes = array(
						'avia-animated-number',
						$element_id,
						'av-force-default-color',
						$class_animation
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $meta, 'el_class' );
			$element_styling->add_responsive_classes( 'container', 'hide_element', $atts );
			$element_styling->add_responsive_font_sizes( 'number', 'size-number', $atts, $this );
			$element_styling->add_responsive_font_sizes( 'description', 'size-text', $atts, $this );

			$element_styling->add_classes( 'icon', avia_font_manager::get_frontend_icon_classes( $atts['font'] ) );

			if( ! empty( $color ) )
			{
				$element_styling->add_classes( 'container', 'avia-color-' . $color );
			}

			if( 'font-custom' == $color )
			{
				$element_styling->add_styles( 'number', array( 'color' => $custom_color ) );
				$element_styling->add_styles( 'description', array( 'color' => $custom_color_desc ) );

				$element_styling->add_styles( 'icon-svg', array(
													'fill'		=> $custom_color,
													'stroke'	=> $custom_color
												) );
			}

			if( ! empty( $circle ) )
			{
				$element_styling->add_classes( 'container', 'av-display-circle' );

				if( ! empty( $circle_mobile ) )
				{
					$element_styling->add_classes( 'circle', 'av-circle-hide-' . $circle_mobile );
				}
			}

			if( $circle_custom == 'custom' )
			{
				if( $circle_size !== '' )
				{
					$element_styling->add_styles( 'circle', array( 'width' => $circle_size . '%' ) );

					$margin = (int) $circle_size / 2;
					$element_styling->add_styles( 'container-circle', array( 'margin' => "{$margin}% 0 {$margin}% 0" ) );
				}

				$element_styling->add_styles( 'circle-inner', array( 'border-style' => $circle_border_style ) );

				if( $circle_border_color !== '' )
				{
					$element_styling->add_styles( 'circle-inner', array( 'border-color' => $circle_border_color ) );
				}
				if( $circle_bg_color !== '' )
				{
					$element_styling->add_styles( 'circle-inner', array( 'background-color' => $circle_bg_color ) );
				}
				if( $circle_border_width !== '' )
				{
					$element_styling->add_styles( 'circle-inner', array( 'border-width' => $circle_border_width . 'px' ) );
				}

				$element_styling->add_callback_styles( 'circle-inner', array( 'box_shadow' ) );

				if( ! empty( $atts['sonar_effect_effect'] ) )
				{
					$element_styling->add_classes( 'container', 'avia-sonar-shadow' );

					//	only shadow supported by span tag
					if( 'shadow_permanent' == $atts['sonar_effect_effect'] )
					{
						$element_styling->add_callback_styles( 'circle-inner-after', array( 'sonar_effect' ) );
					}
					else
					{
						$element_styling->add_callback_styles( 'circle-inner-after-hover', array( 'sonar_effect' ) );
					}
				}
			}

			$selectors = array(
						'container'					=> ".avia-animated-number.{$element_id}",
						'icon-svg'					=> ".avia-animated-number.{$element_id} .avia-animated-number-icon.avia-svg-icon svg:first-child",
						'container-circle'			=> "#top .avia-animated-number.{$element_id}.av-display-circle",
						'number'					=> "#top .avia-animated-number.{$element_id} .avia-animated-number-title",
						'description'				=> "#top .avia-animated-number.{$element_id} .avia-animated-number-content",
						'circle'					=> ".avia-animated-number.{$element_id} .avia-animated-number-circle",
						'circle-inner'				=> ".avia-animated-number.{$element_id} .avia-animated-number-circle-inner",
						'circle-inner-after'		=> ".avia-animated-number.{$element_id}.avia-sonar-shadow .avia-animated-number-circle-inner:after",
						'circle-inner-after-hover'	=> ".avia-animated-number.{$element_id}.avia-sonar-shadow:hover .avia-animated-number-circle-inner:after"
					);

			$element_styling->add_selectors( $selectors );


			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;

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

			extract( AviaHelper::av_mobile_sizes( $atts ) ); //return $av_font_classes, $av_title_font_classes and $av_display_classes

			extract( $atts );

			$tags = array( 'div', 'div' );
			$display_char = '';
			$before = '';
			$after = '';

			$linktarget = AviaHelper::get_link_target( $linktarget );
			$link = AviaHelper::get_url( $link );
			$title_attr_markup = AviaHelper::get_link_title_attr_markup( $title_attr );

			/**
			 * fallback to create same HTML structure prior to introducing get_link_title_attr_markup()
			 * Will be removed in future versions
			 *
			 * @since 5.6.4
			 */
			if( empty( $title_attr_markup ) )
			{
				$title_attr_markup = 'title=""';
			}

			if( ! empty( $link ) )
			{
				$tags[0] = "a href='{$link}' {$linktarget} {$title_attr_markup}";
				$tags[1] = 'a';
			}

			if( $icon_select !== 'no' )
			{
				$char = avia_font_manager::get_frontend_icon( $icon, $font );
				$icon_class = $element_styling->get_class_string( 'icon' );

				$display_char  = "<span class='avia-animated-number-icon {$icon_select}-number av-icon-char {$icon_class}' {$char['attr']}>";
				$display_char .=		$char['svg'];
				$display_char .= '</span>';

				if( $icon_select == 'av-icon-before' )
				{
					$before = $display_char;
				}
				else if( $icon_select == 'av-icon-after' )
				{
					$after  = $display_char;
				}
			}


			// add circle around animated number
			$circle_html = '';
			if( $circle !== '' )
			{
				$circle_class = $element_styling->get_class_string( 'circle' );
				$circle_html = "<span class='avia-animated-number-circle {$circle_class}'><span class='avia-animated-number-circle-inner'></span></span>";
			}

			$style_tag = $element_styling->get_style_tag( $element_id );
			$container_class = $element_styling->get_class_string( 'container' );

			$output  = '';
			$output .= $style_tag;
			$output .= '<' . $tags[0] . ' ' . $meta['custom_el_id'] . ' class="' . $container_class . ' ' . $av_display_classes . '" data-timer="' . $timer . '">';
			$output .=		$circle_html;

			$output .= 		'<strong class="heading avia-animated-number-title">';
			$output .=			$before . $this->extract_numbers( $number, $number_format, $atts ) . $after;
			$output .= 		'</strong>';
			$output .= 		'<div class="avia-animated-number-content">';
			$output .=			wpautop( ShortcodeHelper::avia_remove_autop( $content ) );
			$output .=		'</div>';
			$output .= '</' . $tags[1] . '>';

			return $output;
		}

		/**
		 * Split string into animatable numbers and fixed string
		 *
		 * @since < 4.0
		 * @param string $number
		 * @param string $number_format
		 * @param array $atts
		 * @return string
		 */
		protected function extract_numbers( $number, $number_format, &$atts )
		{
			$number = strip_tags( apply_filters( 'avf_big_number', $number ) );

			/**
			 * @used_by				currently unused
			 * @since 4.5.6
			 * @return string
			 */
			$number_format = apply_filters( 'avf_animated_numbers_separator', $number_format, $number, $atts );

			$replace = '<span class="avia-single-number __av-single-number" data-number_format="' . $number_format . '" data-number="$1" data-start_from="' . $atts['start_from'] . '">$1</span>';

			$number = preg_replace( '!(\D+)!', '<span class="avia-no-number">$1</span>', $number );

			/**
			 * In frontend we have to render unformatted to allow js work properly
			 */
			if( version_compare( phpversion(), '7.0', '<' ) || ! is_admin() )
			{
				$number = preg_replace( '!(\d+)!', $replace, $number );
			}
			else
			{
				$number = preg_replace_callback(
									'!(\d+)!',
									function ( $match ) use ( $number_format )
									{
										switch( $number_format )
										{
											case '.':
												$number = number_format( $match[0], 0, ',', $number_format );
												break;
											case ',':
												$number = number_format( $match[0], 0, '.', $number_format );
												break;
											case ' ':
												$number = number_format( $match[0], 0, ',', $number_format );
												break;
											default:
												$number = $match[0];
										}
										return $number;
									},
									$number );
			}

				return $number;
		}

	}
}
