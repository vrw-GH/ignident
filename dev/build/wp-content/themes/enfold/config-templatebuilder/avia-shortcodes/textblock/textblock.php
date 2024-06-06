<?php
/**
 * Textblock
 *
 * Shortcode which creates a text element wrapped in a div
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_sc_text', false ) )
{
	class avia_sc_text extends aviaShortcodeTemplate
	{
		/**
		 * @since 5.6
		 * @var int
		 */
		protected $textblock_count;

		/**
		 * @since 5.6
		 * @param \AviaBuilder $builder
		 */
		public function __construct( \AviaBuilder $builder )
		{
			parent::__construct( $builder );

			$this->textblock_count = 0;
		}

		/**
		 * Create the config array for the shortcode button
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['self_closing']	= 'no';
			$this->config['base_element']	= 'yes';

			$this->config['name']			= __( 'Text Block', 'avia_framework' );
			$this->config['tab']			= __( 'Content Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-text_block.png';
			$this->config['order']			= 100;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode'] 		= 'av_textblock';
			$this->config['tinyMCE'] 	    = array('disable' => true);
			$this->config['tooltip'] 	    = __( 'Creates a simple text block with collapse support', 'avia_framework' );
			$this->config['preview'] 		= 'large';
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
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
													$this->popup_key( 'content_content' ),
													'fold_unfold_container_toggle'
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
													$this->popup_key( 'styling_column_toggle' ),
													$this->popup_key( 'styling_font_sizes' ),
													$this->popup_key( 'styling_font_colors' ),
													'fold_styling_toggle'
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
								'template_id'	=> 'fold_animation_toggle',
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
						'args'			=> array( 'sc' => $this )
					),

				array(
						'type' 	=> 'tab_container_close',
						'nodescription' => true
					),


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
							'name'		=> __( 'Content','avia_framework' ),
							'desc'		=> __( 'Enter some content for this textblock', 'avia_framework' ),
							'id'		=> 'content',
							'type'		=> 'tiny_mce',
							'std'		=> __( 'Click here to add your own text', 'avia_framework' ),
							'lockable'	=> true,
							'tmpl_set_default'	=> false
						)

					);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Textblock', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_content' ), $template );


			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'textblock_column_toggle',
							'lockable'		=> true
						)

					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_column_toggle' ), $c );


			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'name'			=> __( 'Textblock Font Sizes', 'avia_framework' ),
							'lockable'		=> true,
							'textfield'		=> true,
							'subtype'		=> array(
												'default'	=> AviaHtmlHelper::number_array( 8, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'desktop'	=> AviaHtmlHelper::number_array( 8, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'medium'	=> AviaHtmlHelper::number_array( 8, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 8, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 8, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
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

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_font_sizes' ), $template );


			$c = array(
						array(
							'name'		=> __( 'Textblock Font Colors', 'avia_framework' ),
							'desc'		=> __( 'Either use the themes default colors or apply some custom ones', 'avia_framework' ),
							'id'		=> 'font_color',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
											__( 'Default', 'avia_framework' )	=> '',
											__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
										),
						),

						array(
							'name'		=> __( 'Custom Font Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id'		=> 'color',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'font_color', 'equals', 'custom' )
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

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_font_colors' ), $template );

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

			$template = $this->update_option_lockable( 'content', $locked );

			$params['class'] = '';
			$params['innerHtml'] = "<div class='avia_textblock avia_textblock_style' {$template} data-update_element_template='yes'>" . stripslashes( wpautop( trim( html_entity_decode( $content ) ) ) ) . '</div>';

			return $params;
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
						'font_color'	=> '',
						'color'			=> '',
						'size'			=> '',
					);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] ) ;


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

			$atts['fold_element_class'] = "av-fold-textblock-{$element_id}";

			$element_styling->create_callback_styles( $atts );

			$classes = array(
						'av_textblock_section',
						$element_id
					);

			$element_styling->add_classes( 'section', $classes );
			$element_styling->add_responsive_classes( 'container', 'hide_element', $atts );
			$element_styling->add_responsive_font_sizes( 'container', 'size', $atts, $this );

			$classes = array(
						'avia_textblock'
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $meta, 'custom_class' );
			$element_styling->add_classes_from_array( 'container', $atts, 'template_class' );

			if( $atts['textblock_styling'] != '' )
			{
				$element_styling->add_classes( 'container', 'av_multi_colums' );
			}

			if( 'custom' == $atts['font_color'] )
			{
				$element_styling->add_classes( 'container', 'av_inherit_color' );
				$element_styling->add_styles( 'container', array( 'color' => $atts['color'] ) );
			}

			if( ! empty( $atts['fold_type'] ) )
			{
				$f_classes = array(
							$atts['fold_type'],
							'avia-fold-textblock-wrap',
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
				$element_styling->add_data_attributes( 'section', array(
												'type'		=> $atts['fold_type'],
												'height'	=> $atts['fold_height'],
												'more'		=> $atts['fold_more'],
												'less'		=> $atts['fold_less'],
												'context'	=> __CLASS__
											) );

			}

			$element_styling->add_callback_styles( 'container', array( 'textblock_styling' ) );

			//	add columns media queries
			$element_styling->add_callback_media_queries( 'container', array( 'textblock_styling' ) );
			$element_styling->add_callback_media_queries( 'container-p', array( 'textblock_styling_first_p' ) );


			$selectors = array(
						'section'					=> ".av_textblock_section.{$element_id}",
						'container'					=> "#top .av_textblock_section.{$element_id} .avia_textblock",
						'container-p'				=> ".av_textblock_section.{$element_id} .avia_textblock.av_multi_colums > p:first-child",

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

			$this->textblock_count++;

			$custom_el_id = $meta['custom_el_id'];

			//	we need an id to allow smoothscroll when folding the section
			if( empty( $meta['custom_el_id'] ) && $fold_type != '' )
			{
				$custom_el_id = 'id="avia_sc_text_' . $this->textblock_count . '"';
			}

			$fold_container = '';

			$markup_entry = avia_markup_helper( array( 'context' => 'entry', 'echo' => false, 'custom_markup' => $meta['custom_markup'] ) );
			$markup_text = avia_markup_helper( array( 'context' => 'entry_content', 'echo' => false, 'custom_markup' => $meta['custom_markup'] ) );

			$style_tag = $element_styling->get_style_tag( $element_id );
			$section_class = $element_styling->get_class_string( 'section' );
			$fold_section_class = $element_styling->get_class_string( 'fold-section' );
			$section_data = $element_styling->get_data_attributes_json_string( 'section', 'fold_unfold' );
			$container_class = $element_styling->get_class_string( 'container' );

			if( $fold_type != '' )
			{
				$args = [
						'atts'			=> $atts,
						'wrapper_class'	=> 'av-textblock-btn-wrap',
						'context'		=> __CLASS__
					];

				$fold_container .= '<div class="av-fold-unfold-container folded"></div>';
				$fold_container .= aviaFrontTemplates::fold_unfold_button( $args );
			}

			$output  = '';
			$output .= $style_tag;
			$output .= "<section {$custom_el_id} class='{$section_class} {$fold_section_class}' {$section_data} {$markup_entry}>";
			$output .=		$fold_container;
			$output .=		"<div class='{$container_class}' {$markup_text}>";
			$output .=			ShortcodeHelper::avia_apply_autop( ShortcodeHelper::avia_remove_autop( $content ) );
			$output .=		'</div>';
			$output .= '</section>';

			return $output;

		}
	}
}

