<?php
/**
 * List Menu
 *
 * Displays an existing WordPress menu or a small custom list of links as a plain
 * vertical or horizontal list.
 *
 * This is the column placeable counterpart to the Fullwidth Sub Menu. That element
 * is a navbar - it is fullwidth only, sticks, collapses into a burger button and
 * carries a colour section of its own. None of that works inside a column, which is
 * why link lists in columns used to be written as raw HTML. This element covers that
 * case instead: no navbar behaviour, no javascript, and links that are stored as
 * post ids so they survive a demo import into any install.
 *
 * @since 8.0
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_sc_list_menu', false ) )
{
	class avia_sc_list_menu extends aviaShortcodeTemplate
	{
		/**
		 *
		 * @var int
		 */
		static protected $count = 0;

		/**
		 *
		 * @var int
		 */
		static protected $custom_items = 0;

		/**
		 *
		 * @var boolean
		 */
		protected $in_sc_exec;

		/**
		 *
		 * @param AviaBuilder $builder
		 */
		public function __construct( $builder )
		{
			$this->in_sc_exec = false;

			parent::__construct( $builder );
		}

		public function __destruct()
		{
			parent::__destruct();
		}

		/**
		 * Create the config array for the shortcode button
		 *
		 * Deliberately no 'is_fullwidth' and no 'drag-level': both are what confine the
		 * Fullwidth Sub Menu to the top level. Leaving them out gives the default drag
		 * level 3, which is what makes this droppable into a column.
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['self_closing']	= 'no';
			$this->config['base_element']	= 'yes';

			$this->config['name']			= __( 'List Menu', 'avia_framework' );
			$this->config['tab']			= __( 'Content Elements', 'avia_framework' );

			/**
			 * The svg icon set arrives with 8.0. Falling back to the bitmap keeps this
			 * element usable when it is deployed onto a 7.x install ahead of the rest of
			 * the theme, where iconsURL does not exist and would warn on every admin page.
			 */
			$this->config['icon']			= isset( AviaBuilder::$path['iconsURL'] )
													? AviaBuilder::$path['iconsURL'] . 'sc-list-menu.svg'
													: AviaBuilder::$path['imagesURL'] . 'sc-submenu.png';
			$this->config['order']			= 28;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode']		= 'av_list_menu';
			$this->config['shortcode_nested'] = array( 'av_list_menu_item' );
			//	the canvas names the entries so one is told from the next - see editor_element_items()
			$this->config['alb_items']		= array( 'tag' => 'av_list_menu_item', 'attr' => 'title', 'toggle' => 'which_menu' );
			$this->config['tooltip']		= __( 'Display a menu or a custom list of links', 'avia_framework' );
			$this->config['preview']		= true;
			$this->config['disabling_allowed'] = true;

			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
			$this->config['alb_desc_id']	= 'alb_description';
			$this->config['name_item']		= __( 'List Menu Item', 'avia_framework' );
			$this->config['tooltip_item']	= __( 'A single link of the list', 'avia_framework' );
		}

		/**
		 * No javascript: the element has no sticky, mobile or submenu behaviour to drive.
		 */
		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_css = avia_minify_extension( 'css' );

			wp_enqueue_style( 'avia-module-list-menu', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/list_menu/list_menu{$min_css}.css", array( 'avia-layout' ), $ver );
		}

		/**
		 * Both of the things this element can be, with the setting deciding which is seen.
		 *
		 * It either lists entries of its own or points at a menu built under Appearance, and the
		 * canvas should say which - a menu element that only says "List Menu" tells you nothing you
		 * did not already know from the icon.
		 *
		 * @since 8.0
		 * @param array $params
		 * @return array
		 */
		public function editor_element( $params )
		{
			//	list_menus() is keyed by name, and here the id is what is stored - so it is turned around
			$menus = array_flip( AviaHelper::list_menus() );
			$named = $this->editor_element_option_label( 'menu', $params, $menus, 'avia-element-items-alt' );

			$params['innerHtml'] = $this->editor_element_items( $params, $named );

			return $params;
		}

		/**
		 * Popup Elements
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
							'template_id'	=> $this->popup_key( 'content_menus' ),
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
													$this->popup_key( 'styling_layout' ),
													$this->popup_key( 'styling_colors' ),
													$this->popup_key( 'styling_fonts' ),
													$this->popup_key( 'styling_text' ),
													$this->popup_key( 'styling_link_padding' ),
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
		 * Register the dynamic templates for the element popup
		 */
		protected function register_dynamic_templates()
		{
			$this->register_modal_group_templates();

			/**
			 * Content Tab
			 * ===========
			 */

			$menus = array();
			if( ! empty( $_POST ) && ! empty( $_POST['action'] ) && $_POST['action'] == 'avia_ajax_av_list_menu' )
			{
				$menus = AviaHelper::list_menus();
			}

			$c = array(
						array(
							'name' 	=> __( 'Which kind of menu do you want to display', 'avia_framework' ),
							'desc' 	=> __( 'Either use an existing menu, built in Appearance -> Menus, or create a simple custom list here', 'avia_framework' ),
							'id' 	=> 'which_menu',
							'type' 	=> 'select',
							'std' 	=> '',
							'subtype'	=> array(
												__( 'Use existing menu', 'avia_framework' )			=> '',
												__( 'Build simple custom list', 'avia_framework' )	=> 'custom',
											)
						),

						array(
							'name' 	=> __( 'Select menu to display', 'avia_framework' ),
							'desc' 	=> __( 'You can create new menus in ', 'avia_framework' ) . "<a target='_blank' href='" . admin_url( 'nav-menus.php?action=edit&menu=0' ) . "'>" . __( 'Appearance -> Menus', 'avia_framework' ) . '</a><br/>' . __( 'Please note that Mega Menus are not supported for this element ', 'avia_framework' ),
							'id' 	=> 'menu',
							'type' 	=> 'select',
							'std' 	=> '',
							'required'	=> array( 'which_menu', 'not', 'custom' ),
							'subtype'	=> $menus
						),

						array(
							'name'			=> __( 'Add/Edit list entries', 'avia_framework' ),
							'desc'			=> __( 'Here you can add, remove and edit the entries of your list', 'avia_framework' ),
							'type'			=> 'modal_group',
							'id'			=> 'content',
							'required'		=> array( 'which_menu', 'equals', 'custom' ),
							'modal_title'	=> __( 'Edit List Entry', 'avia_framework' ),
							'std'			=> array(
													array( 'title' => __( 'List Item 1', 'avia_framework' ) ),
													array( 'title' => __( 'List Item 2', 'avia_framework' ) ),
												),
							'subelements'	=> $this->create_modal()
						),

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_menus' ), $c );

			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name' 	=> __( 'List Direction', 'avia_framework' ),
							'desc' 	=> __( 'Stack the entries below each other or place them beside each other. A horizontal list wraps into a new line when it runs out of space.', 'avia_framework' ),
							'id' 	=> 'direction',
							'type' 	=> 'select',
							'std' 	=> 'vertical',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Vertical', 'avia_framework' )		=> 'vertical',
												__( 'Horizontal', 'avia_framework' )	=> 'horizontal',
											)
						),

						array(
							'name' 	=> __( 'List Alignment', 'avia_framework' ),
							'desc' 	=> __( 'Aligns the entries within the element', 'avia_framework' ),
							'id' 	=> 'alignment',
							'type' 	=> 'select',
							'std' 	=> 'left',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Left', 'avia_framework' )		=> 'left',
												__( 'Center', 'avia_framework' )	=> 'center',
												__( 'Right', 'avia_framework' )		=> 'right',
											)
						),

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Layout', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_layout' ), $template );

			/**
			 * An empty colour means "leave it to the theme", the same way the padding option
			 * does. That keeps a list that was never styled following the colour section it
			 * sits in, which is what most footers want.
			 */
			$c = array(
						array(
							'name'		=> __( 'Link Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom color for the entries of the list. Leave empty to use the color of the section the list sits in.', 'avia_framework' ),
							'id'		=> 'link_color',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Link Color on Hover', 'avia_framework' ),
							'desc'		=> __( 'Select a custom color for an entry the visitor points at', 'avia_framework' ),
							'id'		=> 'link_color_hover',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Link Background Color', 'avia_framework' ),
							'desc'		=> __( 'Select a background color for the entries. Together with the link padding below this turns every entry into a visible bar.', 'avia_framework' ),
							'id'		=> 'link_bg',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Link Background Color on Hover', 'avia_framework' ),
							'desc'		=> __( 'Select a background color for an entry the visitor points at', 'avia_framework' ),
							'id'		=> 'link_bg_hover',
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

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_colors' ), $template );

			$c = array(
						array(
							'name'			=> __( 'List Font Size', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the entries of the list', 'avia_framework' ),
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
												'default'	=> 'size-link-text',
												'desktop'	=> 'av-desktop-font-size-link-text',
												'medium'	=> 'av-medium-font-size-link-text',
												'small'		=> 'av-small-font-size-link-text',
												'mini'		=> 'av-mini-font-size-link-text'
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

			/**
			 * The hover decoration is separate because the common case is exactly that: no
			 * underline while resting, underline once the visitor points at an entry.
			 */
			$decoration = array(
							__( 'Default', 'avia_framework' )		=> '',
							__( 'None', 'avia_framework' )			=> 'none',
							__( 'Underline', 'avia_framework' )		=> 'underline',
							__( 'Overline', 'avia_framework' )		=> 'overline',
							__( 'Line Through', 'avia_framework' )	=> 'line-through',
						);

			$c = array(
						array(
							'name' 	=> __( 'Text Transform', 'avia_framework' ),
							'desc' 	=> __( 'Change the capitalisation of the entries without having to retype them', 'avia_framework' ),
							'id' 	=> 'text_transform',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Default', 'avia_framework' )		=> '',
												__( 'None', 'avia_framework' )			=> 'none',
												__( 'Uppercase', 'avia_framework' )		=> 'uppercase',
												__( 'Lowercase', 'avia_framework' )		=> 'lowercase',
												__( 'Capitalize', 'avia_framework' )	=> 'capitalize',
											)
						),

						array(
							'name' 	=> __( 'Text Decoration', 'avia_framework' ),
							'desc' 	=> __( 'Underline or strike through the entries of the list', 'avia_framework' ),
							'id' 	=> 'text_decoration',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> $decoration
						),

						array(
							'name' 	=> __( 'Text Decoration on Hover', 'avia_framework' ),
							'desc' 	=> __( 'Decoration for an entry the visitor points at', 'avia_framework' ),
							'id' 	=> 'text_decoration_hover',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> $decoration
						),

						array(
							'name' 	=> __( 'Letter Spacing', 'avia_framework' ),
							'desc' 	=> __( 'Space between the letters of an entry. Both em and pixel values are accepted, eg 0.06em or 1px. Leave empty to use the theme default.', 'avia_framework' ),
							'id' 	=> 'letter_spacing',
							'type' 	=> 'input',
							'std' 	=> '',
							'lockable'	=> true
						),

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Text Style', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_text' ), $template );

			/**
			 * The padding is set on the link itself, not on the list entry, so the space it
			 * adds is part of the click target. That is the difference to a line height: a
			 * taller line only moves the text apart, this makes the whole row clickable.
			 */
			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'padding',
							'name'			=> __( 'Link Padding', 'avia_framework' ),
							'desc'			=> __( 'Padding that is added to every link of the list. Because it sits on the link itself it enlarges the clickable area instead of only spacing the text apart. Both pixel and &percnt; based values are accepted. eg: 10px, 5&percnt;. Leave empty to use theme default.', 'avia_framework' ),
							'id'			=> 'link_padding',
							'std'			=> '',
							'lockable'		=> true
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Link Padding', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_link_padding' ), $template );
		}

		/**
		 * Creates the modal popup for a single entry
		 *
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
							'template_id'	=> $this->popup_key( 'modal_content_menu' )
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Link', 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'type'			=> 'template',
							'template_id'	=> $this->popup_key( 'modal_advanced_link' )
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
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
		 */
		protected function register_modal_group_templates()
		{
			/**
			 * Content Tab
			 * ===========
			 */
			$c = array(
						array(
							'name' 	=> __( 'List Entry Text', 'avia_framework' ),
							'desc' 	=> __( 'Enter the text of this list entry here', 'avia_framework' ),
							'id' 	=> 'title',
							'std' 	=> '',
							'type' 	=> 'input'
						),

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_content_menu' ), $c );

			/**
			 * Link Tab
			 * ========
			 *
			 * Picking a page here stores its id, not its address, so the link keeps working
			 * after a demo import or a move to another domain. An entry without a link stays
			 * in the list as plain text, which is what makes headings inside a list possible.
			 */
			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'linkpicker_toggle',
							'name'			=> __( 'List Entry Link', 'avia_framework' ),
							'desc'			=> __( 'Where should this entry link to? Leave it on "No Link" to display the text without a link.', 'avia_framework' ),
							'subtypes'		=> array( 'no', 'manually', 'single', 'taxonomy' ),
							'no_toggle'		=> true,
							'title_attr'	=> true
						),
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_advanced_link' ), $c );
		}

		/**
		 * Editor Sub Element - defines the appearance of a single entry inside the modal group
		 *
		 * @param array $params
		 * @return array
		 */
		public function editor_sub_element( $params )
		{
			$template = $this->update_template( 'title', '{{title}}' );

			$params['innerHtml']  = '';
			$params['innerHtml'] .= "<div class='avia_title_container'>";
			$params['innerHtml'] .= "<span {$template} >{$params['args']['title']}</span></div>";

			return $params;
		}

		/**
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
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles( array $args )
		{
			$result = parent::get_element_styles( $args );

			extract( $result );

			$default = array(
							'which_menu'			=> '',
							'menu'					=> '',
							'direction'				=> 'vertical',
							'alignment'				=> 'left',
							'link_padding'			=> '',
							'link_color'			=> '',
							'link_color_hover'		=> '',
							'link_bg'				=> '',
							'link_bg_hover'			=> '',
							'text_transform'		=> '',
							'text_decoration'		=> '',
							'text_decoration_hover'	=> '',
							'letter_spacing'		=> ''
					);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			$this->in_sc_exec = true;

			$classes = array(
						'av-list-menu-wrap',
						$element_id,
						'av-list-menu-' . $atts['direction'],
						'av-list-menu-align-' . $atts['alignment']
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $meta, 'el_class' );
			$element_styling->add_responsive_classes( 'container', 'hide_element', $atts );

			$element_styling->create_callback_styles( $atts );

			if( '' != $atts['link_padding'] )
			{
				$element_styling->add_callback_styles( 'container-links', array( 'link_padding' ) );
			}

			$element_styling->add_styles( 'container-links', array(
										'color'				=> $atts['link_color'],
										'background-color'	=> $atts['link_bg'],
										'text-transform'	=> $atts['text_transform'],
										'text-decoration'	=> $atts['text_decoration'],
										'letter-spacing'	=> $atts['letter_spacing']
								) );

			$element_styling->add_styles( 'container-links-hover', array(
										'color'				=> $atts['link_color_hover'],
										'background-color'	=> $atts['link_bg_hover'],
										'text-decoration'	=> $atts['text_decoration_hover']
								) );

			$element_styling->add_responsive_font_sizes( 'container-links', 'size-link-text', $atts, $this );

			/**
			 * The resting selector has to cover the entry without a link as well, or a plain
			 * text entry would sit flush against the links above and below it and keep the
			 * theme colour while everything around it changed. The hover selector stays on
			 * the anchor - an entry that does not link anywhere must not react to a pointer.
			 *
			 * Both are prefixed with #top #wrap_all because the theme colours links from an
			 * id selector, and a rule made of classes alone would never reach them. The
			 * builder renders its element preview into the same two ids, so the preview shows
			 * what the page will show.
			 */
			$links = ".av-list-menu-wrap.{$element_id} .av-list-menu-list";

			$selectors = array(
						'container'				=> ".av-list-menu-wrap.{$element_id}",
						'container-links'		=> "#top #wrap_all {$links} a, #top #wrap_all {$links} .av-list-menu-no-link",
						'container-links-hover'	=> "#top #wrap_all {$links} a:hover"
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
		 * Create custom stylings for items
		 *
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles_item( array $args )
		{
			$result = parent::get_element_styles_item( $args );

			extract( $result );

			$default = array(
						'title' 		=> '',
						'link' 			=> '',
						'linktarget' 	=> '',
					);

			$default = $this->sync_sc_defaults_array( $default, 'modal_item', 'no_content' );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode_nested'][0] );

			$classes = array(
						'menu-item',
						$element_id
					);

			$element_styling->add_classes( 'container', $classes );

			$selectors = array(
						'container'		=> ".menu-item.{$element_id}"
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
		 * @param array $meta
		 * @return string $output returns the modified html string
		 */
		public function shortcode_handler( $atts, $content = '', $shortcodename = '', $meta = '' )
		{
			$result = $this->get_element_styles( compact( array( 'atts', 'content', 'shortcodename', 'meta' ) ) );

			extract( $result );
			extract( $atts );

			avia_sc_list_menu::$count ++;
			avia_sc_list_menu::$custom_items = 0;

			$element = '';

			if( 'custom' == $which_menu )
			{
				$custom_menu = ShortcodeHelper::avia_remove_autop( $content, true );

				if( ! empty( $custom_menu ) )
				{
					$element .= "<ul id='av-list-menu-" . avia_sc_list_menu::$count . "' class='av-list-menu-list'>";
					$element .=		$custom_menu;
					$element .= '</ul>';
				}
			}
			else
			{
				/**
				 * The default walker on purpose - the mega menu walker of the header brings
				 * markup and behaviour a plain list has no use for.
				 */
				$element .= wp_nav_menu(
					array(
						'menu' 			=> wp_get_nav_menu_object( $menu ),
						'menu_class' 	=> 'av-list-menu-list',
						'fallback_cb' 	=> '',
						'container'		=> false,
						'echo' 			=> false,
						'items_wrap'	=> '<ul id="%1$s" class="%2$s">%3$s</ul>'
					)
				);
			}

			$this->in_sc_exec = false;

			if( '' == trim( (string) $element ) )
			{
				return '';
			}

			$style_tag = $element_styling->get_style_tag( $element_id );
			$container_class = $element_styling->get_class_string( 'container' );

			$output  = '';
			$output .= $style_tag;
			$output .= "<div {$meta['custom_el_id']} class='{$container_class}'>";
			$output .=		$element;
			$output .= '</div>';

			return $output;
		}

		/**
		 * Shortcode handler for a single custom list entry
		 *
		 * @param array $atts
		 * @param string $content
		 * @param string $shortcodename
		 * @param array $meta
		 * @return string
		 */
		public function av_list_menu_item( $atts, $content = '', $shortcodename = '', $meta = '' )
		{
			/**
			 * Fixes a problem when 3-rd party plugins call nested shortcodes without executing main shortcode  (like YOAST in wpseo-filter-shortcodes)
			 */
			if( ! $this->in_sc_exec )
			{
				return '';
			}

			$result = $this->get_element_styles_item( compact( array( 'atts', 'content', 'shortcodename' ) ) );

			extract( $result );
			extract( $atts );

			if( empty( $title ) )
			{
				return '';
			}

			avia_sc_list_menu::$custom_items ++;

			$link = AviaHelper::get_url( $link );

			$element_styling->add_classes( 'container', 'menu-item-' . avia_sc_list_menu::$custom_items );

			$style_tag = $element_styling->get_style_tag( $element_id );
			$container_class = $element_styling->get_class_string( 'container' );

			if( '' != $link )
			{
				$blank = AviaHelper::get_link_target( $linktarget );
				$title_attr_markup = AviaHelper::get_link_title_attr_markup( $title_attr );

				$entry  = "<a href='{$link}' {$blank} {$title_attr_markup}>";
				$entry .=		"<span class='avia-menu-text'>{$title}</span>";
				$entry .= '</a>';
			}
			else
			{
				$entry  = "<span class='av-list-menu-no-link'>";
				$entry .=		"<span class='avia-menu-text'>{$title}</span>";
				$entry .= '</span>';
			}

			$output  = '';
			$output .= $style_tag;
			$output .= "<li class='{$container_class}'>";
			$output .=		$entry;
			$output .= '</li>';

			return $output;
		}
	}

}
