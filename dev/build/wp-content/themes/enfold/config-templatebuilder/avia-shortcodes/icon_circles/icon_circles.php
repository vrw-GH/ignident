<?php
/**
 * Several icons in circle placed on a big circle showing info when hovered
 *
 * @since 5.1
 * @added_by Günter
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_sc_icon_circles', false ) )
{

	class avia_sc_icon_circles extends aviaShortcodeTemplate
	{
		/**
		 *
		 * @since 5.1
		 * @var int
		 */
		protected $item_count;

		/**
		 * @since 5.1
		 * @param \AviaBuilder $builder
		 */
		public function __construct( \AviaBuilder $builder )
		{
			parent::__construct( $builder );

			$this->item_count = 0;
		}

		/**
		 * @since 5.1
		 */
		public function __destruct()
		{
			parent::__destruct();
		}

		/**
		 * Create the config array for the shortcode button
		 *
		 * @since 5.1
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['self_closing']	= 'no';
			$this->config['base_element']	= 'yes';

			$this->config['name']			= __( 'Icon Circles', 'avia_framework' );
			$this->config['tab']			= __( 'Content Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-icon_circles.png';
			$this->config['order']			= 90;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode']		= 'av_icon_circles';
			$this->config['shortcode_nested'] = array( 'av_icon_circle_item' );
			$this->config['tooltip']		= __( 'Display a big circle with several icons displaying an info note on hover in center', 'avia_framework' );
			$this->config['tinyMCE']		= array( 'tiny_always' => true );
			$this->config['preview']		= 'large';
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
			$this->config['alb_desc_id']	= 'alb_description';
			$this->config['name_item']		= __( 'Icon Circles Item', 'avia_framework' );
			$this->config['tooltip_item']	= __( 'An Icon Circles Element Item', 'avia_framework' );
		}

		/**
		 * @since 5.1
		 */
		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_js = avia_minify_extension( 'js' );
			$min_css = avia_minify_extension( 'css' );

			//load css
			wp_enqueue_style( 'avia-module-icon-circles', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/icon_circles/icon_circles{$min_css}.css", array( 'avia-layout' ), $ver );

			//load js
			wp_enqueue_script( 'avia-module-icon-circles', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/icon_circles/icon_circles{$min_js}.js", array( 'avia-shortcodes' ), $ver, true );
		}

		/**
		 * Popup Elements
		 *
		 * If this function is defined in a child class the element automatically gets an edit button, that, when pressed
		 * opens a modal window that allows to edit the element properties
		 *
		 * @since 5.1
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
								'template_id'	=> $this->popup_key( 'content_icons' )
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
								'template_id'	=> $this->popup_key( 'advanced_transform' ),
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
		 * @since 5.1
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
							'name'			=> __( 'Add/Edit Icon items', 'avia_framework' ),
							'desc'			=> __( 'Here you can add, remove and edit the items of your icon circle. We recommend between 3 and 12.', 'avia_framework' ),
							'type'			=> 'modal_group',
							'id'			=> 'content',
							'modal_title'	=> __( 'Edit Icon Circle Item', 'avia_framework' ),
							'editable_item'	=> true,
							'lockable'		=> true,
							'std'			=> array(
													array(
														'title'			=> __( 'Title 1', 'avia_framework' ),
														'icon'			=> 'ue856',
														'font'			=> 'entypo-fontello',
														'description'	=> __( 'Enter some description content for title 1 here', 'avia_framework' )
													),
													array(
														'title'		=> __( 'Title 2', 'avia_framework' ),
														'icon'		=> 'ue8c0',
														'font'			=> 'entypo-fontello',
														'description'	=> __( 'Enter some description content for title 2 here', 'avia_framework' )
													),
													array(
														'title'			=> __( 'Title 3', 'avia_framework' ),
														'icon'			=> 'ue8b9',
														'font'			=> 'entypo-fontello',
														'description'	=> __( 'Enter some description content for title 3 here', 'avia_framework' )
													),
												),
							'subelements'	=> $this->create_modal()
						),

						array(
								'name'		=> __( 'First Active Icon', 'avia_framework' ),
								'desc'		=> __( 'Enter the number of the first icon to be active when user scrolls to the element after pageload. Starts with 1. Leave empty if not needed.', 'avia_framework' ) ,
								'id'		=> 'first_active',
								'type'		=> 'input',
								'std'		=> '',
								'lockable'	=> true
							),

						array(
							'name'		=> __( 'Center Logo/Image', 'avia_framework' ),
							'desc'		=> __( 'Select an image to be shown in the center when no icon is active. Either upload a new, or choose an existing image from your media library.', 'avia_framework' ),
							'id'		=> 'src',
							'type'		=> 'image',
							'title'		=> __( 'Insert Image', 'avia_framework' ),
							'button'	=> __( 'Insert', 'avia_framework' ),
							'std'		=> '',
							'lockable'	=> true,
							'locked'	=> array( 'src', 'attachment', 'attachment_size' )
						)
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_icons' ), $c );

			/**
			 * Styling Tab
			 * ===========
			 */

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
												'default'	=> 'size-title',
												'desktop'	=> 'av-desktop-font-size-title',
												'medium'	=> 'av-medium-font-size-title',
												'small'		=> 'av-small-font-size-title',
												'mini'		=> 'av-mini-font-size-title'
											)
						),

						array(
							'name'			=> __( 'Description Text Font Sizes', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the dexscription text.', 'avia_framework' ),
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'lockable'		=> true,
							'textfield'		=> true,
							'subtype'		=> array(
												'default'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'desktop'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'medium'	=> AviaHtmlHelper::number_array( 10, 60, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 10, 60, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 10, 60, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
											),
							'id_sizes'		=> array(
												'default'	=> 'size-desc',
												'medium'	=> 'av-desktop-font-size-desc',
												'medium'	=> 'av-medium-font-size-desc',
												'small'		=> 'av-small-font-size-desc',
												'mini'		=> 'av-mini-font-size-desc'
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
							'name'		=> __( 'Icons Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom color for your icons here. Leave blank to use default.', 'avia_framework' ),
							'id'		=> 'color_icons',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Icons Background Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom background color for your icons here. Leave blank to use default.', 'avia_framework' ),
							'id'		=> 'color_icons_bgr',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Icons Color On Hover', 'avia_framework' ),
							'desc'		=> __( 'Select a custom color for your icons here on hover. Leave blank to use default.', 'avia_framework' ),
							'id'		=> 'color_icons_hover',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Icons Background Color On Hover', 'avia_framework' ),
							'desc'		=> __( 'Select a custom background color for your icons here. Leave blank to use default.', 'avia_framework' ),
							'id'		=> 'color_icons_bgr_hover',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Circle Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom circle color. Leave blank to use default.', 'avia_framework' ),
							'id'		=> 'color_circle',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Text Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom color for your title and description here. Leave blank to use default.', 'avia_framework' ),
							'id'		=> 'color_text',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Text Background Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom color for your text background here. Leave blank to use default.', 'avia_framework' ),
							'id'		=> 'color_text_bgr',
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
							'desc_margin'	=> __( 'Set a margin for the circle to the surrounding container. Due to the layout of the element only px will work for left and right to keep circle inside the container.', 'avia_framework' ),
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


			/**
			 * Advanced Tab
			 * ============
			 */

			$c = array(

						array(
							'name'		=> __( 'Rotate', 'avia_framework' ),
							'desc'		=> __( 'Select to rotate the element clockwise', 'avia_framework' ),
							'id'		=> 'rotate',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> AviaHtmlHelper::number_array( 1, 359, 1, array( __( 'No Rotation', 'avia_framework' ) => '' ), '°', '', '', array(  ) )
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Transform', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_transform' ), $template );

		}

		/**
		 * Creates the modal popup for a single entry
		 *
		 * @since 5.1
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
							'template_id'	=> $this->popup_key( 'modal_content_content' )
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
													$this->popup_key( 'modal_styling_colors' )
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
								'template_id'	=> $this->popup_key( 'advanced_link' ),
								'nodescription' => true
							),

						array(
								'type'			=> 'template',
								'template_id'	=> 'responsive_visibility',
								'name'			=> __( 'Mobile Display Of Description', 'avia_framework' ),
								'desc'			=> __( 'Select to hide description text when element is viewed on different devices. Recommended for larger text to avoid overlapping the circle.', 'avia_framework' ),
								'id'			=> 'hide-desc',
								'lockable'		=> true
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
		 * @since 5.1
		 */
		protected function register_modal_group_templates()
		{
			/**
			 * Content Tab
			 * ===========
			 */

			$desc  = __( 'Enter some descriptive content here. Keep it short.', 'avia_framework' ) . '<br /><br />';
			$desc .= '<strong>' . __( 'You can hide it depending on the device: Tab Advanced -&gt; Responsive', 'avia_framework' ) . '</strong>';

			$c = array(

						array(
							'name'		=> __( 'Circle Icon', 'avia_framework' ),
							'desc'		=> __( 'Select an icon for your circle icon entry', 'avia_framework' ),
							'id'		=> 'icon',
							'type'		=> 'iconfont',
							'std'		=> '',
							'lockable'	=> true,
							'locked'	=> array( 'icon', 'font' )
						),

						array(
								'name'		=> __( 'Title', 'avia_framework' ),
								'desc'		=> __( 'Enter the title for your icon here. Keep it short.', 'avia_framework' ),
								'id'		=> 'title',
								'type'		=> 'input',
								'std'		=> 'List Title',
								'lockable'	=> true
						),

						array(
								'name'		=> __( 'Description', 'avia_framework' ),
								'desc'		=> $desc,
								'id'		=> 'description',
								'type'		=> 'textarea',
								'std'		=> '',
								'lockable'	=> true,
								'tmpl_set_default'	=> false
						)
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_content_content' ), $c );

			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name'		=> __( 'Icon Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom color for the icon here. Leave blank to use default.', 'avia_framework' ),
							'id'		=> 'color_icon',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Icon Background Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom background color for the icon here. Leave blank to use default.', 'avia_framework' ),
							'id'		=> 'color_icon_bgr',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Icon Border Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom border color for the icon here. Leave blank to use default.', 'avia_framework' ),
							'id'		=> 'color_icon_border',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Icon Color On Hover', 'avia_framework' ),
							'desc'		=> __( 'Select a custom color for the icon here on hover. Leave blank to use default.', 'avia_framework' ),
							'id'		=> 'color_icon_hover',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Icon Background Color On Hover', 'avia_framework' ),
							'desc'		=> __( 'Select a custom background color for the icon here on hover. Leave blank to use default.', 'avia_framework' ),
							'id'		=> 'color_icon_bgr_hover',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Icon Border Color On Hover', 'avia_framework' ),
							'desc'		=> __( 'Select a custom border color for the icon here on hover. Leave blank to use default.', 'avia_framework' ),
							'id'		=> 'color_icon_border_hover',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true
						),
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Colors', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_styling_colors' ), $template );


			/**
			 * Advanced Tab
			 * ============
			 */

			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'linkpicker_toggle',
							'name'			=> __( 'Icon Link', 'avia_framework' ),
							'desc'			=> __( 'Where should the icon link to?', 'avia_framework' ),
							'lockable'		=> true,
							'subtypes'		=> array( 'no', 'manually', 'single', 'taxonomy' ),
							'title_attr'	=> true
						)

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_link' ), $c );
		}

		/**
		 * Editor Sub Element - this function defines the visual appearance of an element that is displayed within a modal window and on click opens its own modal window
		 * Works in the same way as Editor Element
		 *
		 * @since 5.1
		 * @param array $params			this array holds the default values for $content and $args.
		 * @return array				holds an innerHtml key that holds item specific markup.
		 */
		public function editor_sub_element( $params )
		{
			$default = array();
			$locked = array();
			$attr = $params['args'];
			Avia_Element_Templates()->set_locked_attributes( $attr, $this, $this->config['shortcode_nested'][0], $default, $locked );

			$template = $this->update_template_lockable( 'title', __( 'Element', 'avia_framework' ) . ': {{title}}', $locked );

			extract( av_backend_icon( array( 'args' => $attr ) ) ); // creates $font and $display_char if the icon was passed as param 'icon' and the font as 'font'

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
		 *
		 * @since 5.1
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
		 * @since 5.1
		 * @return boolean
		 */
		public function has_global_attributes()
		{
			return true;
		}

		/**
		 * Create custom stylings
		 *
		 * @since 5.1
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
							'attachment_size'	=> ''
						);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$icons = ShortcodeHelper::shortcode2array( $content );

			foreach( $icons as &$icon )
			{
				$item_def = $this->get_default_modal_group_args();
				Avia_Element_Templates()->set_locked_attributes( $icon['attr'], $this, $this->config['shortcode_nested'][0], $item_def, $locked, $icon['content'] );
			}

			unset( $icon );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			$atts['icon_list'] = $icons;

			//	set first active icon - make sure to have a correct integer
			$first = '';

			if( is_numeric( $atts['first_active'] ) )
			{
				$check = (int)$atts['first_active'];
				if( $check > 0 && $check <= count( $icons ) )
				{
					$first = $check;
				}
			}

			$atts['first_active'] = $first;

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

			$classes = array(
						'av-icon-circles-container',
						$element_id,
						$atts['first_active'] ? 'avia-active-icon' : '',
						$class_animation
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $meta, 'el_class' );
			$element_styling->add_responsive_classes( 'container', 'hide_element', $atts );

			$element_styling->add_responsive_font_sizes( 'title', 'size-title', $atts, $this );
			$element_styling->add_responsive_font_sizes( 'description', 'size-desc', $atts, $this );

			$element_styling->add_styles( 'icon', array(
													'color'			=> $atts['color_icons'],
													'background'	=> $atts['color_icons_bgr'],
													'border-color'	=> $atts['color_icons_bgr']
												) );

			$element_styling->add_styles( 'icon_hover', array(
													'color'			=> $atts['color_icons_hover'],
													'background'	=> $atts['color_icons_bgr_hover'],
													'border-color'	=> $atts['color_icons_bgr_hover']
												) );

			$element_styling->add_styles( 'text', array(
													'color'			=> $atts['color_text'],
													'background'	=> $atts['color_text_bgr']
												) );

			$element_styling->add_styles( 'circle', array(
													'border-color'	=> $atts['color_circle']
												) );

			if( ! empty( $atts['rotate'] ) && is_numeric( $atts['rotate'] ) )
			{
				$element_styling->add_styles( 'circle', array(
													'transform'	=> "rotate({$atts['rotate']}deg)"
												) );

				$element_styling->add_styles( 'icon-animated', array(
													'transform'	=> "rotate(-{$atts['rotate']}deg) !important"
												) );

				$element_styling->add_styles( 'text', array(
													'transform'	=> "rotate(-{$atts['rotate']}deg)"
												) );
			}



			//	we need to calculate width and bottom if we have a margin
			$prefixes = $element_styling->get_media_prefixes();
			$default_left = 0;
			$default_right = 0;

			foreach( $prefixes as $prefix )
			{
				$margin = \AviaHelper::array_value( $atts, $prefix . 'margin' );
				if( ! \AviaHelper::empty_multi_input( $margin ) )
				{
					$margin_css = AviaHelper::multi_value_result_lockable( $margin, 'margin' );
					$rules = $margin_css['set_values_rules'];
					$left = $this->get_margin_value( $rules, 'margin-left' );
					$right = $this->get_margin_value( $rules, 'margin-right' );

					if( '' == $left && '' == $right )
					{
						continue;
					}

					if( '' == $left )
					{
						$left = $default_left;
					}

					if( '' == $right )
					{
						$right = $default_right;
					}

					if( '' == $prefix )
					{
						$default_left = $left;
						$default_right = $right;
					}

					$reduce = $left + $right;

					$element_styling->add_callback_settings_style( $prefix . 'margin', array(
												'width'				=> "calc(100% - {$reduce}px)",
												'padding-bottom'	=> "calc(100% - {$reduce}px)"
											) );
				}
			}

			$element_styling->add_responsive_styles( 'container-margin', 'margin', $atts, $this );


			$selectors = array(
						'container'				=> ".av-icon-circles-container.{$element_id}",
						'container-margin'		=> "#top .av-icon-circles-container.{$element_id}",
						'circle'				=> ".av-icon-circles-container.{$element_id} .avia-icon-circles-inner",
						'title'					=> ".av-icon-circles-container.{$element_id} .icon-title",
						'description'			=> ".av-icon-circles-container.{$element_id} .icon-description",
						'icon'					=> ".av-icon-circles-container.{$element_id} .avia-icon-circles-icon",
						'icon-animated'			=> ".av-icon-circles-container.{$element_id}.avia_start_animation .avia-icon-circles-icon",
						'text'					=> ".av-icon-circles-container.{$element_id} .avia-icon-circles-icon-text",
						'icon_hover'			=> ".av-icon-circles-container.{$element_id} .avia-icon-circles-icon.active"
					);


			/*
			 * Calculate Positions of circles on outer circle
			 */
			$cnt_icons = count( $icons );
			$degree = $cnt_icons > 0 ? 360.0 / (float) $cnt_icons : 360.0;

			$current = 0.0;
			$delay = 1.0;

			/**
			 * We have a container of 100% width a circle r=50%  rm = ( 50%, 50% )
			 * We use top to place the circles.
			 * We start animation from center of a 250px circle to outer border.
			 */
			for( $i = 1; $i <= $cnt_icons; $i++ )
			{
				$radiant = ( $current * M_PI / 180.0 );

				$x = (int) ( 50.0 * sin( $radiant ) + 50.0 );
				$y = (int) ( 100.0 - ( 50.0 * cos( $radiant ) + 50.0 ) );

				$x_trans = (int) ( -250.0 * sin( $radiant ) );
				$y_trans = (int) ( 250.0 * cos( $radiant ) );


				$container = "container-$i";
				$selectors[ $container ] = ".av-icon-circles-container.{$element_id} .avia-icon-circles-icon-{$i}";

				$styles = array_merge( $element_styling->transform_rules( "translate({$x_trans}px,{$y_trans}px)" ),
										array(
											'left'	=> "{$x}%",
											'top'	=> "{$y}%",
										) );

				$element_styling->add_styles( $container, $styles );


				$selectors[ "{$container}-animate" ] = ".av-icon-circles-container.{$element_id}.avia_start_animation .avia-icon-circles-icon-{$i}";


				$styles = array_merge( $element_styling->transform_rules( "translate(0px,0px)" ),
										array(
													'transform'			=> "translate(0px,0px)",
													'opacity'			=> 1,
													'transition-delay'	=> "{$delay}s"
										) );

				$element_styling->add_styles( "{$container}-animate", $styles );

				$current += $degree;
				$delay += 0.2;
			}

			$selectors[ 'animate-finished' ] = ".av-icon-circles-container.{$element_id}.avia_start_animation.avia_animation_finished .avia-icon-circles-icon";

			$element_styling->add_styles( 'animate-finished', array(
													'transition-delay'	=> '0s'
												) );

			$element_styling->add_selectors( $selectors );


			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['meta'] = $meta;
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
							'icon'			=> 'ue856',
							'font'			=> 'entypo-fontello',
							'link'			=> '',
							'linktarget'	=> ''
						);

			$default = $this->sync_sc_defaults_array( $default, 'modal_item', 'no_content' );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode_nested'][0] );

			$element_styling->create_callback_styles( $atts, true );

			$classes = array(
						'avia-icon-circles-icon',
						$element_id,
						'iconfont',
						empty( $atts['link'] ) ? 'av-no-link' : 'av-linked-icon'
					);

			if( is_numeric( $parent_atts['first_active'] ) && $this->item_count == $parent_atts['first_active'] )
			{
				$classes[] = 'av-first-active';
			}

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $meta, 'el_class' );

			$classes = array(
							'avia-icon-circles-icon-text',
							$element_id,
							'avia-icon-circles-icon-text-' . $this->item_count
						);

			$element_styling->add_classes( 'container-text', $classes );


			$element_styling->add_styles( 'container', array(
													'color'			=> $atts['color_icon'],
													'background'	=> $atts['color_icon_bgr'],
													'border-color'	=> ! empty( $atts['color_icon_border'] ) ? $atts['color_icon_border'] : $atts['color_icon_bgr']
												) );

			$element_styling->add_styles( 'container-active', array(
													'color'			=> $atts['color_icon_hover'],
													'background'	=> $atts['color_icon_bgr_hover'],
													'border-color'	=> ! empty( $atts['color_icon_border_hover'] ) ? $atts['color_icon_border_hover'] : $atts['color_icon_bgr_hover']
												) );

			$element_styling->add_responsive_styles( 'description', 'hide-desc', $atts, $this );

			$selectors = array(
						'container'				=> ".av-icon-circles-container .avia-icon-circles-icon.{$element_id}",
						'container-active'		=> ".av-icon-circles-container .avia-icon-circles-icon.{$element_id}.active",
						'container-text'		=> ".av-icon-circles-container .avia-icon-circles-icon-text.{$element_id}",
						'description'			=> ".av-icon-circles-container .avia-icon-circles-icon-text.{$element_id} .icon-description",
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
		 * @since 5.1
		 * @param array $atts array of attributes
		 * @param string $content text within enclosing form of shortcode element
		 * @param string $shortcodename the shortcode found, when == callback name
		 * @return string $output returns the modified html string
		 */
		public function shortcode_handler( $atts, $content = '', $shortcodename = '', $meta = '' )
		{
			$result = $this->get_element_styles( compact( array( 'atts', 'content', 'shortcodename', 'meta' ) ) );

			if( empty( $result['atts']['icon_list'] ) )
			{
				return '';
			}

			extract( $result );
			extract( $atts );
			$this->parent_atts = $atts;

			$icons_html = '';

			foreach( $icon_list as $index => $icon )
			{
				$icons_html .= $this->build_icons_html( $icon, $index );
			}

			$alt = '';
			$title = '';

			if( ! empty( $attachment ) )
			{
				/**
				 * Allows e.g. WPML to reroute to translated image
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
					$attachment_entry = $posts[0];
					$attachment = $attachment_entry->ID;
					$alt = get_post_meta( $attachment, '_wp_attachment_image_alt', true );
					$title = $attachment_entry->post_title;

					$img_src = wp_get_attachment_image_src( $attachment, $atts['attachment_size'] );

					if( is_array( $img_src ) && ! empty( $img_src[0] ) )
					{
						$src = $img_src[0];
					}
				}
			}

			$style_tag = $element_styling->get_style_tag( $element_id );
			$item_tag = $element_styling->style_tag_html( $this->subitem_inline_styles, 'sub-' . $element_id );
			$container_class = $element_styling->get_class_string( 'container' );

			$markup_url = avia_markup_helper( array( 'context' => 'image_url', 'echo' => false, 'custom_markup' => $meta['custom_markup'] ) );
			$markup_img = avia_markup_helper( array( 'context' => 'image', 'echo' => false, 'custom_markup' => $meta['custom_markup'] ) );

			$output  = '';
			$output .= $style_tag;
			$output .= $item_tag;
			$output .= "<div class='{$container_class}' {$meta['custom_el_id']}>";
			$output .= 		"<div class='avia-icon-circles-main-logo' {$markup_img}>";
			$output .=			"<img src='{$src}' alt='{$alt}' title='{$title}' {$markup_url} />";
			$output .=		'</div>';
			$output .=		'<div class="avia-icon-circles-inner">';
			$output .=			$icons_html;
			$output .=		'</div>';
			$output .= '</div>';

			return $output;
		}

		/**
		 * Create a single icon HTML and set stylings
		 *
		 * @since 5.1
		 * @param array $icon
		 * @param int $index
		 * @return string
		 */
		protected function build_icons_html( array $icon, $index )
		{
			//	init parameters for normal shortcode handler
			$atts = $icon['attr'];
			$content = $icon['content'];
			$shortcodename = $this->config['shortcode_nested'][0];

			$index++;
			$this->item_count = $index;

			$result = $this->get_element_styles_item( compact( array( 'atts', 'content', 'shortcodename' ) ) );

			extract( $result );
			extract( $atts );


			$display_char = av_icon( $atts['icon'], $atts['font'] );

			$link = AviaHelper::get_url( $link );
			$blank = AviaHelper::get_link_target( $linktarget );
			$title_attr_markup = AviaHelper::get_link_title_attr_markup( $title_attr );

			$tags = ! empty( $link ) ? array( "a href='{$link}' {$blank} {$title_attr_markup}", 'a' ) : array( 'div', 'div' );


			$element_styling->add_data_attributes( 'container-icon', array( 'icon-circles-icon' => $index ) );
			$element_styling->add_data_attributes( 'container-text', array( 'icon-circles-text' => $index ) );

			$this->subitem_inline_styles .= $element_styling->get_style_tag( $element_id, 'rules_only' );
			$container_class = $element_styling->get_class_string( 'container' );
			$container_text_class = $element_styling->get_class_string( 'container-text' );
			$data_container_icon = $element_styling->get_data_attributes_string( 'container-icon' );
			$data_container_text = $element_styling->get_data_attributes_string( 'container-text' );

			$html  = '';
			$html .= "<{$tags[0]} class='{$container_class} avia-icon-circles-icon-{$index}' {$data_container_icon} {$display_char}></{$tags[1]}>";
			$html .= "<div class='{$container_text_class}' {$data_container_text}>";
			$html .=	'<div class="avia-icon-circles-icon-text-inner">';
			$html .=		'<div class="icon-title">';
			$html .=			esc_html( $title );
			$html .=		'</div>';
			$html .=		'<div class="icon-description">';
			$html .=			ShortcodeHelper::avia_apply_autop( ShortcodeHelper::avia_remove_autop( $description ) );
			$html .=		'</div>';
			$html .=	'</div>';
			$html .= '</div>';

			return $html;
		}

		/**
		 * Convert to integer that can be interpreted as px.
		 * Returns '' of not.
		 *
		 * @since 5.1
		 * @param array $rules
		 * @param string $key
		 * @return int|''
		 */
		protected function get_margin_value( array $rules, $key = '' )
		{
			if( ! isset( $rules[ $key ] ) )
			{
				return '';
			}

			$value = trim( $rules[ $key ] );

			if( $value == '' )
			{
				return $value;
			}

			if( false !== strpos( $value, 'px' ) )
			{
				$value = str_replace( 'px', '', $value );
			}

			//	px is default
			if( is_numeric( $value ) )
			{
				return (int) $value;
			}

			return '';
		}
	}
}
