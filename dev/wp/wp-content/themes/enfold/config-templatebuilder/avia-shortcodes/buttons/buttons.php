<?php
/**
 * Button
 *
 * Displays a colored button that links to any url of your choice
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if ( ! class_exists( 'avia_sc_button', false ) )
{
	class avia_sc_button extends aviaShortcodeTemplate
	{
		use \aviaBuilder\traits\scNamedColors;
		use \aviaBuilder\traits\scButtonStyles;
		use \aviaBuilder\traits\modalIconfontHelper;

		/**
		 * @since 4.8.4
		 * @param AviaBuilder $builder
		 */
		public function __construct( AviaBuilder $builder )
		{
			parent::__construct( $builder );

			$this->_construct_scNamedColors();
			$this->_construct_scButtonStyles();
		}

		/**
		 * @since 4.8.4
		 */
		public function __destruct()
		{
			$this->_destruct_scNamedColors();
			$this->_destruct_scButtonStyles();

			parent::__destruct();
		}

		/**
		 * Create the config array for the shortcode button
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['self_closing']	= 'yes';
			$this->config['base_element']	= 'yes';

			$this->config['name']			= __( 'Button', 'avia_framework' );
			$this->config['tab']			= __( 'Content Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-button.png';
			$this->config['order']			= 85;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode']		= 'av_button';
			$this->config['tooltip']		= __( 'Creates a colored button', 'avia_framework' );
			$this->config['tinyMCE']		= array( 'tiny_always' => true );
			$this->config['preview']		= true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
		}


		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_css = avia_minify_extension( 'css' );

			//load css
			wp_enqueue_style( 'avia-module-button', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/buttons/buttons{$min_css}.css", array( 'avia-layout' ), $ver );
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
													$this->popup_key( 'content_button' ),
													$this->popup_key( 'content_link' )
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
													$this->popup_key( 'styling_appearance' ),
													$this->popup_key( 'styling_fonts' ),
													$this->popup_key( 'styling_margin_padding' ),
													$this->popup_key( 'styling_colors' ),
													'border_toggle',
													'box_shadow_toggle'
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
								'type'			=> 'template',
								'template_id'	=> 'effects_toggle',
								'lockable'		=> true,
								'include'		=> array( 'sonar_effect', 'hover_opacity' )
							),

						array(
								'name'			=> __( 'Button Position', 'avia_framework' ),
								'desc'			=> __( 'Set a position for the button', 'avia_framework' ),
								'type'			=> 'template',
								'template_id'	=> 'position',
								'toggle'		=> true,
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

			/**
			 * Content Tab
			 * ===========
			 */

			$c = array(
						array(
							'name'		=> __( 'Button Label', 'avia_framework' ),
							'desc'		=> __( 'This is the text that appears on your button.', 'avia_framework' ),
							'id'		=> 'label',
							'type'		=> 'input',
							'std'		=> __( 'Click me', 'avia_framework' ),
							'lockable'	=> true,
							'dynamic'	=> []
						),

						array(
							'name'		=> __( 'Show Button Icon', 'avia_framework' ),
							'desc'		=> __( 'Should an icon be displayed at the left or right side of the button', 'avia_framework' ),
							'id'		=> 'icon_select',
							'type'		=> 'select',
							'std'		=> 'yes',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'No Icon', 'avia_framework' )					=> 'no',
												__( 'Display icon to the left', 'avia_framework' )	=> 'yes' ,
												__( 'Display icon to the right', 'avia_framework' )	=> 'yes-right-icon',
											)
						),

						array(
							'name'		=> __( 'Button Icon', 'avia_framework' ),
							'desc'		=> __( 'Select an icon for your Button below', 'avia_framework' ),
							'id'		=> 'icon',
							'type'		=> 'iconfont',
							'std'		=> 'note',
							'std_font'	=> 'svg_entypo-fontello',
							'svg_sets'	=> 'yes',
							'lockable'	=> true,
							'locked'	=> array( 'icon', 'font' ),
							'required'	=> array( 'icon_select', 'not_empty_and', 'no' )
						),

						array(
							'name'		=> __( 'Icon Visibility', 'avia_framework' ),
							'desc'		=> __( 'Check to only display icon on hover', 'avia_framework' ),
							'id'		=> 'icon_hover',
							'type'		=> 'checkbox',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'icon_select', 'not_empty_and', 'no' )
						),

						array(
							'name'		=> __( 'Button Title Attribute', 'avia_framework' ),
							'desc'		=> __( 'Add a title attribute for this button. This is shown by most browsers as tooltip popup and is also used for aria-label.', 'avia_framework' ),
							'id'		=> 'title_attr',
							'type'		=> 'input',
							'std'		=> '',
							'dynamic'	=> []
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Button', 'avia_framework' ),
								'content'		=> $c
							)
					);


			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_button' ), $template );

			$c = array(

						array(
							'name' 	=> __( 'Button Type', 'avia_framework' ),
							'desc' 	=> __( 'Select to use button as a normal link button - default behaviour - or for a file download', 'avia_framework' ),
							'id' 	=> 'button_type',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Link button', 'avia_framework' )			=> '',
												__( 'File download button', 'avia_framework' )	=> 'file_download'
											)
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'linkpicker_toggle',
							'name'			=> __( 'Button Link', 'avia_framework' ),
							'desc'			=> __( 'Where should your button link to?', 'avia_framework' ),
							'subtypes'		=> array( 'manually', 'single', 'taxonomy' ),
							'target_id'		=> 'link_target',
							'lockable'		=> true,
							'dynamic'		=> [ 'wp_custom_field' ],
							'dynamic_clear'	=> true,
							'no_toggle'		=> true,
							'required'		=> array( 'button_type', 'equals', '' )
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'file_download',
							'id'			=> 'download_file',
							'lockable'		=> true,
							'required'		=> array( 'button_type', 'not', '' )
						),

						array(
							'name' 	=> __( 'Downloaded File Name', 'avia_framework' ),
							'desc' 	=> __( 'Enter the name for the downloaded file on client device. Leave empty to use orginal file name.', 'avia_framework' ),
							'id' 	=> 'downloaded_file_name',
							'type' 	=> 'input',
							'std' 	=> '',
							'lockable'	=> true,
							'required'	=> array( 'button_type', 'not', '' )
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Link Settings Or File Download', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_link' ), $template );

			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name' 	=> __( 'Button Size', 'avia_framework' ),
							'desc' 	=> __( 'Choose the size of your button here.', 'avia_framework' ),
							'id' 	=> 'size',
							'type' 	=> 'select',
							'std' 	=> 'small',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Small', 'avia_framework' )		=> 'small',
												__( 'Medium', 'avia_framework' )	=> 'medium',
												__( 'Large', 'avia_framework' )		=> 'large',
												__( 'X Large', 'avia_framework' )	=> 'x-large'
											)
						),

						array(
							'name' 	=> __( 'Button Position', 'avia_framework' ),
							'desc' 	=> __( 'Choose the alignment of your button here', 'avia_framework' ),
							'id' 	=> 'position',
							'type' 	=> 'select',
							'std' 	=> 'center',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Align Left', 'avia_framework' )	=> 'left',
												__( 'Align Center', 'avia_framework' )	=> 'center',
												__( 'Align Right', 'avia_framework' )	=> 'right',
											),
							'required'	=> array( 'size', 'not', 'fullwidth' )
						),

						array(
							'name' 	=> __( 'Button Label Display', 'avia_framework' ),
							'desc' 	=> __( 'Select how to display the label', 'avia_framework' ),
							'id' 	=> 'label_display',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Always display', 'avia_framework' )	=> '',
												__( 'Display on hover', 'avia_framework' )	=> 'av-button-label-on-hover',
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
							'name'			=> __( 'Button Text Font Sizes', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the button text.', 'avia_framework' ),
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'textfield'		=> true,
							'lockable'		=> true,
							'subtype'		=> array(
												'default'	=> AviaHtmlHelper::number_array( 10, 90, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'desktop'	=> AviaHtmlHelper::number_array( 10, 90, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'medium'	=> AviaHtmlHelper::number_array( 10, 90, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 10, 90, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 10, 90, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
											),
							'id_sizes'		=> array(
												'default'	=> 'size-text',
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
								'title'			=> __( 'Font Sizes', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_fonts' ), $template );


			$c = array(
						array(
								'type'			=> 'template',
								'template_id'	=> 'margin_padding',
								'toggle'		=> true,
								'name'			=> __( 'Button Margin And Padding', 'avia_framework' ),
								'desc'			=> __( 'Set a responsive margin and a padding to text for the button.', 'avia_framework' ),
								'lockable'		=> true,
							)
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_margin_padding' ), $c );

			$c = array(

						array(
							'name'		=> __( 'Button Colors Selection', 'avia_framework' ),
							'desc'		=> __( 'Select the available options for button colors. Switching to advanced options for already existing buttons you need to set all options (color settings from basic options are ignored).', 'avia_framework' ),
							'id'		=> 'color_options',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Basic options only', 'avia_framework' )	=> '',
												__( 'Advanced options', 'avia_framework' )		=> 'color_options_advanced',
											)
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'named_colors',
							'custom'		=> true,
							'lockable'		=> true,
							'required'		=> array( 'color_options', 'equals', '' )
						),

						array(
							'name'		=> __( 'Custom Background Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom background color for your button here', 'avia_framework' ),
							'id'		=> 'custom_bg',
							'type'		=> 'colorpicker',
							'std'		=> '#444444',
							'rgba'		=> true,
							'lockable'	=> true,
							'required'	=> array( 'color', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Custom Font Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom font color for your button here', 'avia_framework' ),
							'id'		=> 'custom_font',
							'type'		=> 'colorpicker',
							'std'		=> '#ffffff',
							'rgba'		=> true,
							'lockable'	=> true,
							'required'	=> array( 'color', 'equals', 'custom')
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'button_colors',
							'color_id'		=> 'btn_color',
							'custom_id'		=> 'btn_custom',
							'lockable'		=> true,
							'required'		=> array( 'color_options', 'not', '' )
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
			 * Adcanced Tab
			 * ============
			 */

			$c = array(

						array(
							'type'			=> 'template',
							'template_id'	=> 'animation',
							'lockable'		=> true,
							'std_none'		=> '',
							'name'			=> __( 'Button Animation', 'avia_framework' ),
							'desc'			=> __( 'Add a small animation to the button when the user first scrolls to the button position. This is only to add some &quot;spice&quot; to the site and only works in modern browsers and only on desktop computers to keep page rendering as fast as possible.', 'avia_framework' ),
							'groups'		=> array( 'fade', 'slide', 'rotate', 'fade-adv', 'special' )
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

		}

		/**
		 * Editor Element - this function defines the visual appearance of an element on the AviaBuilder Canvas
		 * Most common usage is to define some markup in the $params['innerHtml'] which is then inserted into the drag and drop container
		 * Less often used: $params['data'] to add data attributes, $params['class'] to modify the className
		 *
		 * @param array $params			this array holds the default values for $content and $args.
		 * @return array				the return array usually holds an innerHtml key that holds item specific markup.
		 */
		public function editor_element( $params )
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
			Avia_Element_Templates()->set_locked_attributes( $attr, $this, $this->config['shortcode'], $default, $locked );

			extract( avia_font_manager::backend_icon( array( 'args' => $attr ) ) ); // creates $font and $display_char if the icon was passed as param 'icon' and the font as 'font'

			$inner  = "<div class='avia_button_box avia_hidden_bg_box avia_textblock avia_textblock_style' data-update_element_template='yes'>";
			$inner .=		'<div ' . $this->class_by_arguments_lockable( 'icon_select, color, size, position', $attr, $locked ) . '>';
			$inner .=			'<span ' . $this->class_by_arguments_lockable( 'font', $font, $locked ) . '>';
			$inner .=				'<span ' . $this->update_option_lockable( array( 'icon', 'icon_fakeArg' ), $locked ) . " class='avia_button_icon avia_button_icon_left'>{$display_char}</span>";
			$inner .=			'</span> ';
			$inner .=			'<span ' . $this->update_option_lockable( 'label', $locked ) . " class='avia_iconbox_title' >{$attr['label']}</span> ";
			$inner .=			'<span ' . $this->class_by_arguments_lockable( 'font', $font, $locked ) . '>';
			$inner .=				'<span ' . $this->update_option_lockable( array( 'icon', 'icon_fakeArg' ), $locked ) . " class='avia_button_icon avia_button_icon_right'>{$display_char}</span>";
			$inner .=			'</span>';
			$inner .=		'</div>';
			$inner .= '</div>';

			$params['innerHtml'] = $inner;
			$params['content'] = null;
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

			/**
			 * Fix a bug in 4.7 and 4.7.1 renaming option id (no longer backwards comp.) - can be removed in a future version again
			 */
			if( isset( $atts['linktarget'] ) )
			{
				$atts['link_target'] = $atts['linktarget'];
			}

			$default = $this->get_default_btn_atts();
			$default = $this->sync_sc_defaults_array( $default );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			if( $atts['icon_select'] == 'yes' )
			{
				$atts['icon_select'] = 'yes-left-icon';
			}

			Avia_Dynamic_Content()->read( $atts, $this, $shortcodename, $content );
			$atts['link'] = Avia_Dynamic_Content()->check_link( $atts['link_dynamic'], $atts['link'], [ 'manually', 'single', 'taxonomy' ] );

			avia_font_manager::switch_to_svg( $atts['font'], $atts['icon'] );

			$classes = array(
						'avia-button',
						$element_id,
						empty( $atts['button_type'] ) ? 'av-link-btn' : 'av-download-btn noLightbox'
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes( 'container', $this->class_by_arguments( 'icon_select, size, position', $atts, true, 'array' ) );
			$element_styling->add_responsive_classes( 'container', 'hide_element', $atts );
			$element_styling->add_responsive_font_sizes( 'container', 'size-text', $atts, $this );

			$element_styling->add_classes( 'icon', avia_font_manager::get_frontend_icon_classes( $atts['font'] ) );
			$element_styling->add_classes( 'wrap', $element_id . '-wrap' );

			$this->set_button_styes( $element_styling, $atts );

			if( ! empty( $atts['css_position'] ) )
			{
				$element_styling->add_responsive_styles( 'wrap', 'css_position', $atts, $this );
			}

			$element_styling->add_responsive_styles( 'container', 'margin', $atts, $this );
			$element_styling->add_responsive_styles( 'container', 'padding', $atts, $this );


			if( ! in_array( $atts['animation'], array( 'no-animation', '' ) ) )
			{
				if( false !== strpos( $atts['animation'], 'curtain-reveal-' ) )
				{
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
					$wrap_classes = array(
										'avia_animated_button',
										'av-animated-when-visible-95',
//										'animate-all-devices',
										$atts['animation']
									);

					if( is_admin() )
					{
						$wrap_classes[] = 'avia-animate-admin-preview';

						$element_styling->add_callback_styles( 'wrap', array( 'animation' ) );
					}
					else
					{
						$element_styling->add_callback_styles( 'wrap-animation', array( 'animation' ) );
					}

					$element_styling->add_classes( 'wrap', $wrap_classes );
				}
			}


			$selectors = array(
						'wrap'						=> ".avia-button-wrap.{$element_id}-wrap",
						'wrap-animation'			=> ".avia_transform  .avia-button-wrap.{$element_id}-wrap",
						'container'					=> "#top #wrap_all .avia-button.{$element_id}",
						'container-hover'			=> "#top #wrap_all .avia-button.{$element_id}:hover",
						'container-hover-overlay'	=> "#top #wrap_all.avia-button.{$element_id}:hover .avia_button_background",
						'container-after'			=> ".avia-button.{$element_id}.avia-sonar-shadow:after",
						'container-after-hover'		=> ".avia-button.{$element_id}.avia-sonar-shadow:hover:after",
						'curtain'					=> ".avia-button-wrap.{$element_id}-wrap .avia-curtain-reveal-overlay",
						'icon-svg'					=> "#top #wrap_all .avia-button.{$element_id} .avia-svg-icon svg:first-child",
						'icon-svg-hover'			=> "#top #wrap_all .avia-button.{$element_id}:hover .avia-svg-icon svg:first-child"
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

			$data = '';
			$background_hover = '';
			$style_hover = '';


			$display_char = avia_font_manager::get_frontend_icon( $atts['icon'], $atts['font'] );

			if( '' != $atts['color_options'] )
			{
				if( 'custom' != $atts['btn_color_bg_hover'] && 'btn_custom_grad' != $atts['btn_color_bg'] )
				{
					//	must be added otherwise we get a bottom border !!!
//					$style_hover = "style='background-color:{$atts['btn_color_bg_hover']};'";

					if( $this->is_special_button_color( $atts['btn_color_bg_hover'] ) )
					{
						$background_hover = "<span class='avia_button_background avia-button avia-color-{$atts['btn_color_bg_hover']}' {$style_hover}></span>";
					}
				}
			}

			if( ! empty( $atts['label_display'] ) && $atts['label_display'] == 'av-button-label-on-hover' )
			{
				$data .= 'data-avia-tooltip="' . htmlspecialchars( $atts['label'] ) . '"';
				$atts['label'] = '';
			}

			$blank = '';

			if( empty( $atts['button_type'] ) )
			{
				$blank = AviaHelper::get_link_target( $atts['link_target'] );
				$link = AviaHelper::get_url( $atts['link'] );
				$link = ( ( $link == 'http://' ) || ( $link == 'manually' ) ) ? '' : $link;
			}
			else
			{
				$link = esc_url( $atts['download_file'] );
				$downloaded_file_name = trim( $atts['downloaded_file_name'] );
				$blank = ( $downloaded_file_name == '' ) ? 'download' : 'download="' . esc_html( $downloaded_file_name ) . '"';
			}

			$title_attr = ! empty( $atts['title_attr'] ) && empty( $atts['label_display'] ) ? 'title="' . esc_attr( $atts['title_attr'] ) . '"' : '';

			$aria_label = '';
			if( ! empty( $atts['title_attr'] ) )
			{
				$aria_label = 'aria-label="' . esc_attr( $atts['title_attr'] ) . '"';
			}
			else if( ! empty( $atts['label'] ) )
			{
				$aria_label = 'aria-label="' . esc_attr( $atts['label'] ) . '"';
			}


			$style_tag = $element_styling->get_style_tag( $element_id );
			$wrap_class = $element_styling->get_class_string( 'wrap' );
			$container_class = $element_styling->get_class_string( 'container' );
			$icon_class = $element_styling->get_class_string( 'icon' );

			$content_html = '';

			if( 'yes-left-icon' == $atts['icon_select'] )
			{
				$content_html .= "<span class='avia_button_icon avia_button_icon_left {$icon_class}' {$display_char['attr']}>";
				$content_html .=		$display_char['svg'];
				$content_html .= '</span>';
			}

			$content_html .= "<span class='avia_iconbox_title' >{$atts['label']}</span>";

			if( 'yes-right-icon' == $atts['icon_select'] )
			{
				$content_html .= "<span class='avia_button_icon avia_button_icon_right {$icon_class}' {$display_char['attr']}>";
				$content_html .=		$display_char['svg'];
				$content_html .= '</span>';
			}

			$curtain_reveal_overlay = '';

			if( false !== strpos( $atts['animation'], 'curtain-reveal-' ) )
			{
				$curtain_class = $element_styling->get_class_string( 'curtain' );
				$curtain_reveal_overlay = "<div class='{$curtain_class}'></div>";
			}

			$html  = '';
			$html .= $style_tag;

			$html .=	"<a href='{$link}' {$data} class='{$container_class}' {$blank} {$title_attr} {$aria_label}>";
			$html .=		$curtain_reveal_overlay;
			$html .=		$content_html;
			$html .=		$background_hover;
			$html .=	'</a>';

			$output  = "<div {$meta['custom_el_id']} class='avia-button-wrap {$wrap_class} avia-button-{$atts['position']} {$meta['el_class']}'>";
//			$output .=		$curtain_reveal_overlay;
			$output .=		$html;
			$output .= '</div>';

			return $output;
		}

	}
}
