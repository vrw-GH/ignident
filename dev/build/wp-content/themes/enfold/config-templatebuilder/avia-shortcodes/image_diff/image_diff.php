<?php
/**
 * Image Before - After
 *
 * @since 5.5
 * @added_by Guenter
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_sc_image_diff', false ) )
{
	class avia_sc_image_diff extends aviaShortcodeTemplate
	{
		/**
		 * Counter to create unique container id
		 *
		 * @since 5.5
		 * @var int
		 */
		protected $counter;

		/**
		 * @since 5.5
		 * @param \AviaBuilder $builder
		 */
		public function __construct( \AviaBuilder $builder )
		{
			parent::__construct( $builder );

			$this->counter = 0;
		}

		/**
		 * Create the config array for the shortcode button
		 *
		 * 5.5
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['self_closing']	= 'no';
			$this->config['base_element']	= 'yes';

			$this->config['name']			= __( 'Before-After Images', 'avia_framework' );
			$this->config['tab']			= __( 'Media Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-image-before-after.png';
			$this->config['order']			= 105;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode'] 		= 'av_image_diff';
//			$this->config['modal_data']     = array( 'modal_class' => 'mediumscreen' );
			$this->config['tooltip'] 	    = __( 'Shows 2 overlayed images with a moveable divider to show difference between images.', 'avia_framework' );
			$this->config['preview'] 		= 1;
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
		}

		/**
		 * 5.5
		 */
		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_css = avia_minify_extension( 'css' );
			$min_js = avia_minify_extension( 'js' );

			//load css
			wp_enqueue_style( 'avia-module-image-diff', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/image_diff/image_diff{$min_css}.css", array( 'avia-layout' ), $ver );

			//load js ( see 'underscore' dependency !!! )
			wp_enqueue_script( 'avia-module-image-diff', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/image_diff/image_diff{$min_js}.js", array( 'avia-shortcodes', 'underscore' ), $ver, true );
		}

		/**
		 * Popup Elements
		 *
		 * If this function is defined in a child class the element automatically gets an edit button, that, when pressed
		 * opens a modal window that allows to edit the element properties
		 *
		 * 5.5
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
													$this->popup_key( 'content_images' ),
													$this->popup_key( 'content_diff_buttons' ),
													$this->popup_key( 'content_drag_line' ),
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
													$this->popup_key( 'styling_image_size' ),
													$this->popup_key( 'styling_image_alignment' ),
													$this->popup_key( 'styling_color_drag' ),
													$this->popup_key( 'styling_color_button' ),
													$this->popup_key( 'styling_margin_padding' ),
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
								'template_id'	=> $this->popup_key( 'advanced_seo' ),
							),

						array(
								'type'			=> 'template',
								'template_id'	=> 'lazy_loading_toggle',
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
						'args'			=> array(
												'sc'	=> $this
											)
					),

				array(
						'type' 	=> 'tab_container_close',
						'nodescription' => true
					),

				array(
						'id'	=> 'av_element_hidden_in_editor',
						'type'	=> 'hidden',
						'std'	=> '0'
					)
			);

		}

		/**
		 * Create and register templates for easier maintainance
		 *
		 * @since 5.5
		 */
		protected function register_dynamic_templates()
		{
			/**
			 * Content Tab
			 * ===========
			 */

			$c = array(
						array(
							'name'		=> __( 'Before Image', 'avia_framework' ),
							'desc'		=> __( 'Either upload a new, or choose an existing image from your media library.', 'avia_framework' ),
							'id'		=> 'src',
							'type'		=> 'image',
							'title'		=> __( 'Insert Before Image', 'avia_framework' ),
							'button'	=> __( 'Insert', 'avia_framework' ),
							'std'		=> AviaBuilder::$path['imagesURL'] . 'placeholder.jpg',
							'lockable'	=> true,
							'locked'	=> array( 'src', 'attachment', 'attachment_size' )
						),

						array(
							'name'			=> __( 'After Image', 'avia_framework' ),
							'desc'			=> __( 'Either upload a new, or choose an existing image from your media library.', 'avia_framework' ),
							'id'			=> 'after_image',
							'type'			=> 'image',
							'fetch'			=> 'id',
//							'secondary_img'	=> true,
							'force_id_fetch' => true,
							'title'			=>  __( 'Insert After Image', 'avia_framework' ),
							'button'		=> __( 'Insert', 'avia_framework' ),
							'std'			=> '',
							'lockable'		=> true
						)

					);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Images', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_images' ), $template );


			$c = array(

						array(
							'name'		=> __( 'Button Appearance', 'avia_framework' ),
							'desc'		=> __( 'Select when to display the before and after buttons', 'avia_framework' ),
							'id'		=> 'btn_appearance',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Always display buttons', 'avia_framework' )	=> '',
												__( 'Only display on hover', 'avia_framework' )		=> 'btn-on-hover',
												__( 'Never show buttons', 'avia_framework' )		=> 'btn-always-hide',
											)
						),

						array(
							'name'		=> __( 'Before Button Text', 'avia_framework' ),
							'desc'		=> __( 'Enter a text that is displayed in the button for the before image.', 'avia_framework' ),
							'id'		=> 'btn_before',
							'type'		=> 'input',
							'std'		=> __( 'Before', 'avia_framework' ),
							'lockable'	=> true,
							'required'	=> array( 'btn_appearance', 'not', 'btn-always-hide' )
						),

						array(
							'name'		=> __( 'After Button Text', 'avia_framework' ),
							'desc'		=> __( 'Enter a text that is displayed in the button for the after image.', 'avia_framework' ),
							'id'		=> 'btn_after',
							'type'		=> 'input',
							'std'		=> __( 'After', 'avia_framework' ),
							'lockable'	=> true,
							'required'	=> array( 'btn_appearance', 'not', 'btn-always-hide' )
						)

					);


			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Buttons', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_diff_buttons' ), $template );


			$c = array(
						array(
							'name'		=> __( 'Drag Line Direction', 'avia_framework' ),
							'desc'		=> __( 'Select the direction of the drag line. Can be moved by dragging, swipe on touch devices, a click in image or with buttons.', 'avia_framework' ),
							'id'		=> 'drag_direction',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Left to right', 'avia_framework' )	=> '',
												__( 'Top to bottom', 'avia_framework' )	=> 'av-handle-horizontal'
											)
						),

						array(
							'name'		=> __( 'Initial Drag Start Point', 'avia_framework' ),
							'desc'		=> __( 'Select the initial visibility start point of the drag line for the before image on page load.', 'avia_framework' ),
							'id'		=> 'drag_start',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> AviaHtmlHelper::number_array( 0, 100, 1, array( __( 'Default (= 50%)', 'avia_framework' ) => '' ), '%' )
						),

						array(
							'name'		=> __( 'Drag Line Layout', 'avia_framework' ),
							'desc'		=> __( 'Select the layout of the drag line', 'avia_framework' ),
							'id'		=> 'drag_layout',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Circle border only', 'avia_framework' )	=> '',
												__( 'Circle filled', 'avia_framework' )			=> 'av-handle-circle av-handle-filled',
												__( 'Oval border only', 'avia_framework' )		=> 'av-handle-oval av-handle-border',
												__( 'Oval filled', 'avia_framework' )			=> 'av-handle-oval av-handle-filled',
												__( 'Arrows only', 'avia_framework' )			=> 'av-handle-arrows'
											)
						),

						array(
							'name'		=> __( 'Drag Line Styling', 'avia_framework' ),
							'desc'		=> __( 'Select the styling of the drag line and circle', 'avia_framework' ),
							'id'		=> 'drag_line_style',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Lines', 'avia_framework' )		=> '',
												__( 'Dotted lines, dashed circle', 'avia_framework' )	=> 'av-line-dotted av-circle-dashed',
												__( 'Dotted lines, dotted circle', 'avia_framework' )	=> 'av-line-dotted av-circle-dotted',
											)
						),

						array(
							'name'		=> __( 'Drag Line Arrows Behaviour', 'avia_framework' ),
							'desc'		=> __( 'Select the behaviour of the drag line arrows on hover', 'avia_framework' ),
							'id'		=> 'drag_arrows_hover',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'No animation', 'avia_framework' )		=> '',
												__( 'Expand slightly ', 'avia_framework' )	=> 'av-handle-arrows-expand'
											)
						)

					);


			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Drag Line', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_drag_line' ), $template );

			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'image_size_select',
							'lockable'		=> true,
							'method'		=> 'fallback_media'
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Size', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_image_size' ), $template );


			$c = array(

						array(
							'name'		=> __( 'Image Alignment', 'avia_framework' ),
							'desc'		=> __( 'Choose here, how to align your image', 'avia_framework' ),
							'id'		=> 'align',
							'type'		=> 'select',
							'std'		=> 'center',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Center',  'avia_framework' )				=> 'center',
												__( 'Right',  'avia_framework' )				=> 'right',
												__( 'Left',  'avia_framework' )					=> 'left',
												__( 'No special alignment', 'avia_framework' )	=> '',
											)
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Alignment', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_image_alignment' ), $template );


			$c = array(

						array(
							'name'		=> __( 'Arrows Color', 'avia_framework' ),
							'desc'		=> __( 'Select a color for the arrows. Leave empty for theme default.', 'avia_framework' ),
							'id'		=> 'color_arrows',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'container_class' => 'av_half av_half_first',
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Background Color', 'avia_framework' ),
							'desc'		=> __( 'Select a background color for the circle or oval. Leave empty for theme default.', 'avia_framework' ),
							'id'		=> 'background_circle',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'container_class' => 'av_half',
							'lockable'	=> true,
							'required'	=> array( 'drag_layout', 'contains', 'av-handle-filled' )
						),

						array(
							'name'		=> __( 'Line Color', 'avia_framework' ),
							'desc'		=> __( 'Select a color for the drag lines. Leave empty for theme default.', 'avia_framework' ),
							'id'		=> 'color_drag_line',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'container_class' => 'av_half av_half_first',
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Shadow Line Color', 'avia_framework' ),
							'desc'		=> __( 'Select a color for the small shadow of the drag lines. Leave empty for theme default.', 'avia_framework' ),
							'id'		=> 'shadow_drag_line',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'container_class' => 'av_half',
							'lockable'	=> true,
							'required'	=> array( 'drag_layout', 'not', 'av-handle-arrows' )
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Drag Line Colors', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_color_drag' ), $template );

			$c = array(

						array(
							'name'		=> __( 'Button Style', 'avia_framework' ),
							'desc'		=> __( 'Select the layout of the buttons', 'avia_framework' ),
							'id'		=> 'btn_style',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'btn_appearance', 'not', 'btn-always-hide' ),
							'subtype'	=> array(
												__( 'Small rounded borders', 'avia_framework' )	=> '',
												__( 'Square', 'avia_framework' )				=> 'btn-style-square',
												__( 'Oval', 'avia_framework' )					=> 'btn-style-oval'
											)
						),

						array(
							'name'		=> __( 'Font Color', 'avia_framework' ),
							'desc'		=> __( 'Select a color for button font. Leave empty for theme default.', 'avia_framework' ),
							'id'		=> 'color_btn_font',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'container_class' => 'av_half av_half_first',
							'lockable'	=> true,
							'required'	=> array( 'btn_appearance', 'not', 'btn-always-hide' )
						),

						array(
							'name'		=> __( 'Border Color', 'avia_framework' ),
							'desc'		=> __( 'Select a color for button border. Leave empty for theme default.', 'avia_framework' ),
							'id'		=> 'color_btn_border',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'container_class' => 'av_half',
							'lockable'	=> true,
							'required'	=> array( 'btn_appearance', 'not', 'btn-always-hide' )
						),

						array(
							'name'		=> __( 'Background Color', 'avia_framework' ),
							'desc'		=> __( 'Select a color for button background. Leave empty for theme default.', 'avia_framework' ),
							'id'		=> 'color_btn_background',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'container_class' => 'av_half av_half_first',
							'lockable'	=> true,
							'required'	=> array( 'btn_appearance', 'not', 'btn-always-hide' )
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Buttons', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_color_button' ), $template );


			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'margin_padding',
								'name_toggle'	=> __( 'Margin', 'avia_framework' ),
								'content'		=> 'margin',
								'toggle'		=> true,
								'lockable'		=> true
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_margin_padding' ), $template );


			/**
			 * Advanced Tab
			 * ============
			 */

			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'animation',
							'lockable'		=> true,
							'std'			=> 'no-animation',
							'std_none'		=> 'no-animation',
							'name'			=> __( 'Animation', 'avia_framework' ),
							'desc'			=> __( 'Add a small animation to the image when the user first scrolls to the image position. This is to add some &quot;spice&quot; to the site.', 'avia_framework' ),
							'groups'		=> array( 'fade', 'slide', 'rotate', 'fade-adv', 'special' )
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
							'name' 			=> __( 'Custom Title Attribute Before Image', 'avia_framework' ),
							'desc' 			=> __( 'Add a custom title attribute limited to this instance, replaces media gallery settings.', 'avia_framework' ),
							'id' 			=> 'before_title_attr',
							'type' 			=> 'input',
							'std' 			=> '',
							'lockable'		=> true,
						),

						array(
							'name' 			=> __( 'Custom Alt Attribute Before Image', 'avia_framework' ),
							'desc' 			=> __( 'Add a custom alt attribute limited to this instance, replaces media gallery settings.', 'avia_framework' ),
							'id' 			=> 'before_alt_attr',
							'type' 			=> 'input',
							'std' 			=> '',
							'lockable'		=> true,
						),

						array(
							'name' 			=> __( 'Custom Title Attribute After Image', 'avia_framework' ),
							'desc' 			=> __( 'Add a custom title attribute limited to this instance, replaces media gallery settings.', 'avia_framework' ),
							'id' 			=> 'after_title_attr',
							'type' 			=> 'input',
							'std' 			=> '',
							'lockable'		=> true,
						),

						array(
							'name' 			=> __( 'Custom Alt Attribute After Image', 'avia_framework' ),
							'desc' 			=> __( 'Add a custom alt attribute limited to this instance, replaces media gallery settings.', 'avia_framework' ),
							'id' 			=> 'after_alt_attr',
							'type' 			=> 'input',
							'std' 			=> '',
							'lockable'		=> true,
						)
					);


			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'SEO improvements', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_seo' ), $template );
		}

		/**
		 * Editor Element - this function defines the visual appearance of an element on the AviaBuilder Canvas
		 * Most common usage is to define some markup in the $params['innerHtml'] which is then inserted into the drag and drop container
		 * Less often used: $params['data'] to add data attributes, $params['class'] to modify the className
		 *
		 * 5.5
		 * @param array $params			holds the default values for $content and $args.
		 * @return array				usually holds an innerHtml key that holds item specific markup.
		 */
		public function editor_element( $params )
		{
			$default = array();
			$locked = array();
			$attr = $params['args'];
			Avia_Element_Templates()->set_locked_attributes( $attr, $this, $this->config['shortcode'], $default, $locked );

			$template = $this->update_template_lockable( 'src', "<img src='{{src}}' alt=''/>", $locked );
			$img = '';

			if( ! empty( $attr['attachment'] ) && ! empty( $attr['attachment_size'] ) )
			{
				$img = wp_get_attachment_image( $attr['attachment'], $attr['attachment_size'] );
			}
			else if( isset( $attr['src'] ) && is_numeric( $attr['src'] ) )
			{
				$img = wp_get_attachment_image( $attr['src'], 'large' );
			}
			else if( ! empty( $attr['src'] ) )
			{
				$img = "<img src='" . esc_attr( $attr['src'] ) . "' alt=''  />";
			}


			$params['innerHtml']  = "<div class='avia-image-diff-container avia_hidden_bg_box' data-update_element_template='yes'>";
			$params['innerHtml'] .=		'<div ' . $this->class_by_arguments_lockable( 'align, drag_direction, btn_style', $attr, $locked ) . '>';
			$params['innerHtml'] .=			'<div class="av-image-diff-wrapper">';
			$params['innerHtml'] .=				"<div class='avia_image_container' {$template}>{$img}</div>";
			$params['innerHtml'] .=				'<div class="av-image-diff-overlay">';
			$params['innerHtml'] .=					'<div class="av-img-diff-label label-before">';
			$params['innerHtml'] .=						'<span ' . $this->update_option_lockable( 'btn_before', $locked ) . " class='avia_img_diff_btn' >{$attr['btn_before']}</span> ";
			$params['innerHtml'] .=					'</div>';
			$params['innerHtml'] .=					'<div class="av-img-diff-label label-after">';
			$params['innerHtml'] .=						'<span ' . $this->update_option_lockable( 'btn_after', $locked ) . " class='avia_img_diff_btn' >{$attr['btn_after']}</span> ";
			$params['innerHtml'] .=					'</div>';
			$params['innerHtml'] .=				'</div>';
			$params['innerHtml'] .=			'</div>';
			$params['innerHtml'] .=		'</div>';
			$params['innerHtml'] .= '</div>';

			$params['class'] = '';

			return $params;
		}

		/**
		 * Create custom stylings
		 *
		 * @since 5.5
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles( array $args )
		{
			$result = parent::get_element_styles( $args );

			extract( $result );

			$default = array(
							'src'				=> '',
							'attachment'		=> '',
							'attachment_size'	=> 'full',

							'after_src'			=> '',		//	save for HTML output
							'img_h'				=> '',
							'img_w'				=> ''
						);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			//	user can enter shortcode and add attachment id to src or maybe link to a fallback image only
			if( is_numeric( $atts['src'] ) )
			{
				$atts['attachment'] = $atts['src'];
				$atts['src'] = '';
			}

			if( ! empty( $atts['image_size'] ) )
			{
				if( 'no scaling' == $atts['image_size'] )
				{
					$atts['image_size'] = 'full';
				}

				$atts['attachment_size'] = $atts['image_size'];
			}

			if( empty( $atts['attachment_size'] ) )
			{
				$atts['attachment_size'] = 'full';
			}

			if( ! empty( $atts['attachment'] ) )
			{
				/**
				 * Allows e.g. WPML to reroute to translated animation
				 */
				$posts = get_posts( array(
										'include'			=> $atts['attachment'],
										'post_status'		=> 'inherit',
										'post_type'			=> 'attachment',
										'post_mime_type'	=> 'image',
										'order'				=> 'ASC',
										'orderby'			=> 'post__in'
									)
								);

				if( is_array( $posts ) && ! empty( $posts ) )
				{
					$new_src = wp_get_attachment_image_src( $posts[0]->ID, $atts['attachment_size'] );

					if( false !== $new_src )
					{
						$atts['attachment'] = $posts[0]->ID;
						$atts['src'] = ! empty( $new_src[0] ) ? $new_src[0] : '';
						$atts['img_h'] = ! empty( $new_src[2] ) ? $new_src[2] : '';
						$atts['img_w'] = ! empty( $new_src[1] ) ? $new_src[1] : '';

						$atts = $this->image_attributes( $posts[0], $atts, 'before' );
					}
					else
					{
						$atts['src'] = '';
						$atts['attachment'] = false;
					}
				}
				else
				{
					$atts['src'] = '';
					$atts['attachment'] = false;
				}
			}
			else
			{
				$atts['attachment'] = false;
				$atts['attachment_size'] = 'full';
			}

			if( empty( $atts['src'] ) )
			{
				$result['default'] = $default;
				$result['atts'] = $atts;
				$result['content'] = $content;

				return $result;
			}

			//	create after image
			if( ! empty( $atts['after_image'] ) )
			{
				/**
				 * Allows e.g. WPML to reroute to translated image
				 */
				$posts = get_posts( array(
										'include'			=> $atts['after_image'],
										'post_status'		=> 'inherit',
										'post_type'			=> 'attachment',
										'post_mime_type'	=> 'image',
										'order'				=> 'ASC',
										'orderby'			=> 'post__in'
									)
								);

				if( is_array( $posts ) && ! empty( $posts ) )
				{
					$new_src = wp_get_attachment_image_src( $atts['after_image'], $atts['attachment_size'] );

					if( false !== $new_src )
					{
						$atts['after_image'] = $posts[0]->ID;
						$atts['after_src'] = ! empty( $new_src[0] ) ? $new_src[0] : $atts['src'];

						$atts = $this->image_attributes( $posts[0], $atts, 'after' );
					}
					else
					{
						$atts['after_image'] = $atts['attachment'];
						$atts['after_src'] = $atts['src'];
					}
				}
			}
			else
			{
				$atts['after_image'] = $atts['attachment'];
				$atts['after_src'] = $atts['src'];
			}


			/**
			 * @since 5.5
			 * @param string $class_animation
			 * @param array $atts
			 * @param aviaShortcodeTemplate $this
			 * @param string $shortcodename
			 * @return string
			 */
			$class_animation = apply_filters( 'avf_alb_element_animation', 'av-animated-when-almost-visible', $atts, $this, $shortcodename );


			$element_styling->create_callback_styles( $atts );

			if( empty( $atts['drag_layout'] ) )
			{
				$atts['drag_layout'] = 'av-handle-circle av-handle-border';
			}

			if( empty( $atts['drag_direction'] ) )
			{
				$atts['drag_direction'] = 'av-handle-vertical';
			}

			if( empty( $atts['btn_style'] ) )
			{
				$atts['btn_style'] = 'btn-style-rounded';
			}

			if( empty( $atts['drag_line_style'] ) )
			{
				$atts['drag_line_style'] = 'av-line-default';
			}

			$classes = array(
							'avia-image-diff-container',
							$element_id,
							$atts['drag_direction'],
							$atts['drag_layout'],
							$atts['btn_appearance'],
							$atts['drag_arrows_hover'],
							$atts['btn_style'],
							$atts['drag_line_style'],
					);


			if( ! in_array( $atts['animation'], array( 'no-animation', '' ) ) )
			{
				$classes[] = 'av-animated-diff-img';
				$classes[] = $atts['animation'];
				$classes[] = $class_animation;

				if( is_admin() )
				{
					$classes[] = 'avia-animate-admin-preview';

					$element_styling->add_callback_styles( 'container', array( 'animation' ) );
				}
				else
				{
					$element_styling->add_callback_styles( 'container-animation', array( 'animation' ) );
				}
			}

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes( 'container', $this->class_by_arguments( 'align', $atts, true, 'array' ) );
			$element_styling->add_classes_from_array( 'container', $meta, 'el_class' );
			$element_styling->add_responsive_classes( 'container', 'hide_element', $atts );


			$shadow1 = empty( $atts['color_drag_line'] ) ? '#fff' : $atts['color_drag_line'];
			$shadow2 = empty( $atts['shadow_drag_line'] ) ? 'rgba(51, 51, 51, 0.5)' : $atts['shadow_drag_line'];

			if( false !== strpos( $atts['drag_layout'], 'av-handle-filled' ) )
			{
				$element_styling->add_styles( 'handle', array(
												'background-color'	=> $atts['background_circle']
											) );
			}

			if( false === strpos( $atts['drag_layout'], 'av-handle-arrows' ) )
			{
				$element_styling->add_styles( 'handle', array(
												'box-shadow'			=> "0 0 12px {$shadow2}"
											) );

				$element_styling->add_styles( 'handle-before', array(
												'box-shadow'			=> "0 3px 0 {$shadow1}, 0px 0px 12px {$shadow2}"
											) );

				$element_styling->add_styles( 'handle-after', array(
												'box-shadow'			=> "0 3px 0 {$shadow1}, 0px 0px 12px {$shadow2}"
											) );
			}

			$element_styling->add_styles( 'handle', array(
												'border-color'		=> $atts['color_drag_line'],
											) );

			if( false === strpos( $atts['drag_line_style'], 'av-line-dotted' ) )
			{
				$element_styling->add_styles( 'handle-before', array(
													'background-color'		=> $atts['color_drag_line'],
												) );

				$element_styling->add_styles( 'handle-after', array(
													'background-color'		=> $atts['color_drag_line'],
												) );
			}
			else
			{
				$element_styling->add_styles( 'handle-before', array(
													'border-left-color'		=> $atts['color_drag_line'],
												) );

				$element_styling->add_styles( 'handle-after', array(
													'border-left-color'		=> $atts['color_drag_line'],
												) );
			}

			$element_styling->add_styles( 'left-arrow', array( 'border-right-color' => $atts['color_arrows'] ) );
			$element_styling->add_styles( 'right-arrow', array( 'border-left-color' => $atts['color_arrows'] ) );


			$element_styling->add_styles( 'buttons', array(
												'color'				=> $atts['color_btn_font'],
												'border-color'		=> $atts['color_btn_border'],
												'background-color'	=> $atts['color_btn_background'],
											) );


			$element_styling->add_callback_styles( 'container', array( 'animation' ) );
			$element_styling->add_callback_styles( 'img-wrapper', array( 'border', 'border_radius', 'box_shadow' ) );

			$element_styling->add_responsive_styles( 'img-wrapper', 'margin', $atts, $this );

			$element_styling->add_data_attributes( 'container', array(
															'drag_start'	=> $atts['drag_start']
														) );


			$selectors = array(
						'container'				=> ".avia-image-diff-container.{$element_id}",
						'container-animation'	=> ".avia_transform .avia-image-diff-container.{$element_id}.avia_start_delayed_animation",
						'img-wrapper'			=> ".avia-image-diff-container.{$element_id} .av-image-diff-wrapper",
						'handle'				=> ".avia-image-diff-container.{$element_id} .av-image-diff-handle",
						'handle-before'			=> ".avia-image-diff-container.{$element_id} .av-image-diff-handle:before",
						'handle-after'			=> ".avia-image-diff-container.{$element_id} .av-image-diff-handle:after",
						'left-arrow'			=> ".avia-image-diff-container.{$element_id} .av-image-diff-handle .av-handle-left-arrow",
						'right-arrow'			=> ".avia-image-diff-container.{$element_id} .av-image-diff-handle .av-handle-right-arrow",
						'buttons'				=> ".avia-image-diff-container.{$element_id} .av-img-diff-label",



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
		 * @since 5.5
		 * @param array $atts array of attributes
		 * @param string $content text within enclosing form of shortcode element
		 * @param string $shortcodename the shortcode found, when == callback name
		 * @return string $output returns the modified html string
		 */
		public function shortcode_handler( $atts, $content = '', $shortcodename = '', $meta = '' )
		{
			$result = $this->get_element_styles( compact( array( 'atts', 'content', 'shortcodename', 'meta' ) ) );

			extract( $result );

			if( empty( $atts['src'] ) )
			{
				return '';
			}

			if( empty( $atts['after_src'] ) )
			{
				$atts['after_src'] = $atts['src'];
			}

			if( 'disabled' == $atts['img_scrset'] )
			{
				Av_Responsive_Images()->force_disable( 'disabled' );
			}

			extract( $atts );

			$output = '';

			$markup_url = avia_markup_helper( array( 'context' => 'image_url', 'echo' => false, 'custom_markup' => $meta['custom_markup'] ) );
			$markup_img = avia_markup_helper( array( 'context' => 'image', 'echo' => false, 'custom_markup' => $meta['custom_markup'] ) );

			$hw = '';
			if( ! empty( $img_h ) )
			{
				$hw .= ' height="' . $img_h . '"';
			}

			if( ! empty( $img_w ) )
			{
				$hw .= ' width="' . $img_w . '"';
			}

			$img_class = $element_styling->get_class_string( 'container-img' );

			$img_tag = "<img class='avia_image av-img-before {$img_class}' src='{$src}' alt='{$before_alt_attr}' title='{$before_title_attr}' {$hw} {$markup_url} />";
			$img_tag = Av_Responsive_Images()->prepare_single_image( $img_tag, $attachment, $lazy_loading );

			$img_tag2 = "<img class='avia_image av-img-after' src='{$after_src}' alt='{$after_alt_attr}' title='{$after_title_attr}' {$hw} {$markup_url} />";
			$img_tag2 = Av_Responsive_Images()->prepare_single_image( $img_tag2, $after_image, $lazy_loading );


			$style_tag = $element_styling->get_style_tag( $element_id );
			$container_class = $element_styling->get_class_string( 'container' );
			$container_data = $element_styling->get_data_attributes_json_string( 'container', 'image_diff' );

			$output .= $style_tag;
			$output .= "<div {$meta['custom_el_id']} class='{$container_class}' {$container_data} {$markup_img}>";
			$output .=		'<div class="av-image-diff-wrapper">';
			$output .=			$img_tag;
			$output .=			$img_tag2;
			$output .=			'<div class="av-image-diff-overlay">';
			$output .=				'<span class="av-img-diff-label label-before">';
			$output .=					$btn_before;
			$output .=				'</span>';
			$output .=				'<span class="av-img-diff-label label-after">';
			$output .=					$btn_after;
			$output .=				'</span>';
			$output .=			'</div>';
			$output .=			'<div class="av-image-diff-handle">';
			$output .=				'<span class="av-handle-arrow av-handle-left-arrow"></span>';
			$output .=				'<span class="av-handle-arrow av-handle-right-arrow"></span>';
			$output .=			'</div>';
			$output .=		'</div>';
			$output .= '</div>';


			$html = Av_Responsive_Images()->make_content_images_responsive( $output );

			Av_Responsive_Images()->force_disable( 'reset' );

			return $html;
		}

		/**
		 * @since 5.5
		 * @param WP_Post $attachment
		 * @param array $atts
		 * @param string $img_type				'before' | 'after'
		 * @return array
		 */
		protected function image_attributes( WP_Post $attachment, array $atts, $img_type = 'before' )
		{
			switch( $img_type )
			{
				case 'after':
					$title_attr = 'after_title_attr';
					$alt_attr = 'after_ alt_attr';
					break;
				case 'before':
				default:
					$title_attr = 'before_title_attr';
					$alt_attr = 'before_ alt_attr';
					break;
			}

			if( ! empty( $atts[ $alt_attr ] ) )
			{
				$alt = $atts[ $alt_attr ];
			}
			else
			{
				$alt = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );
			}

			$atts[ $alt_attr ] = ! empty( $alt ) ? esc_attr( trim( $alt ) ) : '';

			if( ! empty( $atts[ $title_attr ] ) )
			{
				$title = $atts[ $title_attr ];
			}
			else
			{
				$title = $attachment->post_title;
			}

			$atts[ $title_attr ] = ! empty( $title ) ? esc_attr( trim( $title ) ) : '';

			return $atts;
		}
	}
}
