<?php
/**
 * Custom Field Element
 *
 * @since 6.0
 * @added_by Günter
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_sc_dynamic_field', false ) )
{

	class avia_sc_dynamic_field extends aviaShortcodeTemplate
	{
		/**
		 * @since 6.0
		 * @param \AviaBuilder $builder
		 */
		public function __construct( \AviaBuilder $builder )
		{
			parent::__construct( $builder );
		}

		/**
		 * @since 6.0
		 */
		public function __destruct()
		{
			parent::__destruct();
		}

		/**
		 * Create the config array for the shortcode button
		 *
		 * @since 6.0
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['self_closing']	= 'no';
			$this->config['base_element']	= 'yes';

			$this->config['name']			= __( 'Dynamic Data', 'avia_framework' );
			$this->config['tab']			= __( 'Content Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-dynamic-field.png';
			$this->config['order']			= 90;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode']		= 'av_dynamic_field';
			$this->config['tooltip']		= __( 'Displays formatted content of a custom field or post data', 'avia_framework' );
			$this->config['tinyMCE']		= array( 'disable' => 'true' );
			$this->config['preview']		= true;
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
			$this->config['alb_desc_id']	= 'alb_description';
		}

		/**
		 * @since 6.0
		 */
		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_js = avia_minify_extension( 'js' );
			$min_css = avia_minify_extension( 'css' );

			//load css
			wp_enqueue_style( 'avia-module-dynamic-field', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/dynamic_field/dynamic_field{$min_css}.css", array( 'avia-layout' ), $ver );

//			//load js
//			wp_enqueue_script( 'avia-module-dynamic-field', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/icon_circles/icon_circles{$min_js}.js", array( 'avia-shortcodes' ), $ver, true );
		}

		/**
		 * Popup Elements
		 *
		 * If this function is defined in a child class the element automatically gets an edit button, that, when pressed
		 * opens a modal window that allows to edit the element properties
		 *
		 * @since 6.0
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
								'template_id'	=> $this->popup_key( 'content_dynamic_field' )
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
													$this->popup_key( 'styling_fonts' ),
													$this->popup_key( 'styling_colors' ),
													$this->popup_key( 'styling_spacing' )
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
		 * @since 6.0
		 */
		protected function register_dynamic_templates()
		{
			/**
			 * Content Tab
			 * ===========
			 */

			$desc  = __( 'Add content to display using custom fields content combined with static content or only static content (e.g. for descriptive text in an ALB table element column)', 'avia_framework' ) . '<br /><br />';
			$desc .=__( 'Please note: keep content simple to avoid breaking of layout.', 'avia_framework' );

			$c = array(

						array(
								'name'				=> __( 'Dynamic Field Content', 'avia_framework' ),
								'desc'				=> $desc,
								'id'				=> 'dynamic_content',
								'type'				=> 'textarea',
								'std'				=> '',
								'lockable'			=> true,
								'tmpl_set_default'	=> false,
								'dynamic'			=> []
						)

				);

			if( class_exists( 'ACF', false ) )
			{
				$c[] = array(
							'name'			=> __( 'Format ACF field content', 'avia_framework' ),
							'desc'			=> __( 'When using ACF fields select &quot;Format&quot; to display e.g. links, images, ... and not only text or attachment ids.', 'avia_framework' ),
							'id'			=> 'format',
							'type'			=> 'select',
							'std'			=> 'auto',
							'lockable'		=> true,
							'subtype'		=> array(
													__( 'No formatting', 'avia_framework' )	=> '',
													__( 'Format', 'avia_framework' )		=> 'auto'
												)
						);

			}

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_dynamic_field' ), $c );

			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name'			=> __( 'Font Sizes', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the dynamic field content', 'avia_framework' ),
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
												'default'	=> 'size-dynamic',
												'desktop'	=> 'av-desktop-font-size-dynamic',
												'medium'	=> 'av-medium-font-size-dynamic',
												'small'		=> 'av-small-font-size-dynamic',
												'mini'		=> 'av-mini-font-size-dynamic'
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
							'name'		=> __( 'Text Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom color for the text. Leave blank to use default.', 'avia_framework' ),
							'id'		=> 'color_text',
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
							'content'		=> 'margin',
							'name'			=> '',
							'desc_margin'	=> __( 'Set a margin to the surrounding container.', 'avia_framework' ),
							'lockable'		=> true
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Margin', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_spacing' ), $template );

		}

		/**
		 * Create custom stylings
		 *
		 * @since 6.0
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles( array $args )
		{
			$result = parent::get_element_styles( $args );

			extract( $result );

			$default = array(
							'dynamic_content'	=> '',
							'format'			=> 'auto'
						);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			Avia_Dynamic_Content()->read( $atts, $this, $shortcodename, $content );


			$element_styling->create_callback_styles( $atts );

			$classes = array(
						'av-dynamic-field-container',
						$element_id
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $meta, 'el_class' );
			$element_styling->add_responsive_classes( 'container', 'hide_element', $atts );

			$element_styling->add_responsive_font_sizes( 'container', 'size-dynamic', $atts, $this );


			$element_styling->add_styles( 'container', array(
													'color'		=> $atts['color_text']
												) );


			$element_styling->add_responsive_styles( 'container', 'margin', $atts, $this );


			$selectors = array(
						'container'		=> ".av-dynamic-field-container.{$element_id}"
					);


			$element_styling->add_selectors( $selectors );


			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['meta'] = $meta;
			$result['element_styling'] = $element_styling;

			return $result;
		}

		/**
		 * Frontend Shortcode Handler
		 *
		 * @since 6.0
		 * @param array $atts					array of attributes
		 * @param string $content				text within enclosing form of shortcode element
		 * @param string $shortcodename			the shortcode found, when == callback name
		 * @param array $meta
		 * @return string						returns the modified html string
		 */
		public function shortcode_handler( $atts, $content = '', $shortcodename = '', $meta = [] )
		{
			$result = $this->get_element_styles( compact( array( 'atts', 'content', 'shortcodename', 'meta' ) ) );

			extract( $result );

			$style_tag = $element_styling->get_style_tag( $element_id );
			$container_class = $element_styling->get_class_string( 'container' );

			$output  = '';
			$output .= $style_tag;
			$output .= "<div class='{$container_class}' {$meta['custom_el_id']}>";
			$output .=		'<div class="av-dynamic-field-inner">';
			$output .=			ShortcodeHelper::avia_apply_autop( ShortcodeHelper::avia_remove_autop( $atts['dynamic_content'] ) );
			$output .=		'</div>';
			$output .= '</div>';

			return $output;
		}

	}
}