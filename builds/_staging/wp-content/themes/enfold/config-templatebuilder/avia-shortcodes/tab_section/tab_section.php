<?php
/**
 * Tab Section
 *
 * Add a fullwidth section with tabs that can contain any content like columns and other elements
 *
 * @since ????
 */
if( ! defined( 'ABSPATH' ) ) { exit; }			// Don't load directly


if( ! class_exists( 'avia_sc_tab_section', false ) )
{
	if( ! class_exists( 'avia_sc_tab_sub_section', false ) )
	{
		//load the subsection shhortcode
		include_once( 'tab_sub_section.php' );
	}

	class avia_sc_tab_section extends aviaShortcodeTemplate
	{
		/**
		 * @since ???
		 * @var int
		 */
		static public $count = 0;

		/**
		 * Counter for tabs (= index)
		 *
		 * @since ???
		 * @var int
		 */
		static public $tab = 0;

		/**
		 * @since ???
		 * @var int
		 */
		static public $admin_active = 1;

		/**
		 * Single tab titles
		 *
		 *		'index'		=> tab title
		 *
		 * @since ???
		 * @var array
		 */
		static public $tab_titles = array();

		/**
		 * HTML for tab icons
		 *
		 *		'index'		=> html code
		 *
		 * @since ???
		 * @var array
		 */
		static public $tab_icons = array();

		/**
		 * HTML for tab images
		 *
		 *		'index'		=> html code
		 *
		 * @since ???
		 * @var array
		 */
		static public $tab_images = array();

		/**
		 * @since ???
		 * @var array
		 */
		static public $tab_atts = array();

		/**
		 * Holds the element id for the current tab section
		 *
		 * @since 4.8.9
		 * @var string
		 */
		static public $tab_element_id = '';

		/**
		 * Hold the element id's for the tabs
		 *
		 *		'index'		=> element_id
		 *
		 * @since 4.8.9
		 * @var array
		 */
		static public $sub_tab_element_id = array();


		/**
		 * Create the config array for the tab section
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']			= '1.0';
			$this->config['is_fullwidth']		= 'yes';
			$this->config['type']				= 'layout';
			$this->config['self_closing']		= 'no';
			$this->config['contains_text']		= 'no';
			$this->config['layout_children']	= array( 'av_tab_sub_section' );

			$this->config['name']				= __( 'Tab Section', 'avia_framework' );
			$this->config['icon']				= AviaBuilder::$path['imagesURL'] . 'sc-tabsection.png';
			$this->config['tab']				= __( 'Layout Elements', 'avia_framework' );
			$this->config['order']				= 13;
			$this->config['shortcode']			= 'av_tab_section';
			$this->config['html_renderer']		= false;
			$this->config['tinyMCE']			= array( 'disable' => 'true' );
			$this->config['tooltip']			= __( 'Add a fullwidth section with tabs that can contain columns and other elements', 'avia_framework' );
			$this->config['drag-level']			= 1;
			$this->config['drop-level']			= 100;
			$this->config['disabling_allowed']	= true;

			$this->config['id_name']			= 'id';
			$this->config['id_show']			= 'always';				//	we use original code - not $meta
			$this->config['aria_label']			= 'yes';
		}

		protected function admin_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_js = avia_minify_extension( 'js' );

			wp_register_script( 'avia_tab_section_js', AviaBuilder::$path['assetsURL'] . "js/avia-tab-section{$min_js}.js", array( 'avia_builder_js', 'avia_modal_js' ), $ver, true );
			Avia_Builder()->add_registered_admin_script( 'avia_tab_section_js' );
		}


		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_js = avia_minify_extension( 'js' );
			$min_css = avia_minify_extension( 'css' );

			//load css
			wp_enqueue_style( 'avia-module-tabsection', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/tab_section/tab_section{$min_css}.css", array( 'avia-layout' ), $ver );

			//load js
			wp_enqueue_script( 'avia-module-tabsection', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/tab_section/tab_section{$min_js}.js", array( 'avia-shortcodes' ), $ver, true );
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
						'name'  => __( 'Layout' , 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'type'			=> 'template',
							'template_id'	=> 'toggle_container',
							'templates_include'	=> array(
													$this->popup_key( 'layout_general' ),
													$this->popup_key( 'layout_content_height' ),
													$this->popup_key( 'layout_margin_padding' )
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
													$this->popup_key( 'styling_padding' ),
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
								'template_id'	=> 'screen_options_toggle'
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
						'id'	=> 'av_element_hidden_in_editor',
						'type'	=> 'hidden',
						'std'	=> '0'
					),

				array(
						'id'	=> 'av_admin_tab_active',
						'type'	=> 'hidden',
						'std'	=> '1'
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
			 * Layout Tab
			 * ===========
			 */

			$c = array(

						array(
							'name' 	=> __( 'Tab Position', 'avia_framework' ),
							'desc'  => __( 'Define the position of the tab buttons', 'avia_framework' ),
							'id' 	=> 'tab_pos',
							'type' 	=> 'select',
							'std' 	=> 'av-tab-above-content',
							'subtype'	=> array(
												__( 'Display Tabs above content', 'avia_framework' )	=> 'av-tab-above-content',
												__( 'Display Tabs below content', 'avia_framework' )	=> 'av-tab-below-content',
											)
						),

						array(
							'name' 	=> __( 'Tab Buttons Out Of Screen Behaviour', 'avia_framework' ),
							'desc'  => __( 'Select to display arrows in tab button area if tab buttons are out of screens viewport to draw visitors attention that there are more buttons available.', 'avia_framework' ),
							'id' 	=> 'tab_arrows',
							'type' 	=> 'select',
							'std' 	=> '',
							'subtype'	=> array(
												__( 'Display arrows', 'avia_framework' )		=> '',
												__( 'Do not display arrows', 'avia_framework' )	=> 'av-tab-arrows-hide',
											)
						),

						array(
							'name' 	=> __( 'Initial Open', 'avia_framework' ),
							'desc' 	=> __( 'Enter the number of the tab that should be open initially (starting with 1). If tab number does not exist the first tab is taken.', 'avia_framework' ),
							'id' 	=> 'initial',
							'type' 	=> 'input_number',
							'min'	=> 1,
							'step'	=> 1,
							'std' 	=> '1'
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'General', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'layout_general' ), $template );

			$c = array(

						array(
							'name'		=> __( 'Content height', 'avia_framework' ),
							'id'		=> 'content_height',
							'desc'		=> __( 'Define the behaviour for the size of the content tabs when switching between the tabs. Content alignment can be set for each tab when &quot;Same Height&quot; is selected.', 'avia_framework' ),
							'type'		=> 'select',
							'std'		=> '',
							'required'	=> array( 'tab_pos', 'contains', 'av-tab-above-content' ),
							'subtype'	=> array(
												__( 'Same height for all tabs', 'avia_framework' )	=> '',
												__( 'Auto adjust to content', 'avia_framework' )	=> 'av-tab-content-auto'
											)
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

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'layout_content_height' ), $template );


			$c = array(
						array(
							'name' 	=> __( 'Tab Padding', 'avia_framework' ),
							'id' 	=> 'tab_padding',
							'desc'  => __( 'Define the tab titles top and bottom padding (only works if no icon is displayed at the top of the tab title)', 'avia_framework' ),
							'type' 	=> 'select',
							'std' 	=> 'default',
							'subtype'	=> array(
												__( 'No Padding', 'avia_framework' )		=> 'none',
												__( 'Small Padding', 'avia_framework' )		=> 'small',
												__( 'Default Padding', 'avia_framework' )	=> 'default',
												__( 'Large Padding', 'avia_framework' )		=> 'large',
											)
						),

						array(
							'name' 	=> __( 'Content Padding', 'avia_framework' ),
							'desc'  => __( 'Define the sections top and bottom padding', 'avia_framework' ),
							'id' 	=> 'padding',
							'type' 	=> 'select',
							'std' 	=> 'default',
							'subtype'	=> array(
												__( 'No Padding', 'avia_framework' )		=> 'no-padding',
												__( 'Small Padding', 'avia_framework' )		=> 'small',
												__( 'Default Padding', 'avia_framework' )	=> 'default',
												__( 'Large Padding', 'avia_framework' )		=> 'large',
												__( 'Huge Padding', 'avia_framework' )		=> 'huge',
											)
						),

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Padding', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'layout_margin_padding' ), $template );

			/**
			 * Styling Tab
			 * ===========
			 */



			$c = array(
						array(
							'name'  => __( 'Tab Title Background Color', 'avia_framework' ),
							'desc'  => __( 'Select a custom background color of the tab title bar. Enter no value if you want to use the standard color.', 'avia_framework' ),
							'id'    => 'bg_color',
							'type'  => 'colorpicker',
							'rgba'	=> true,
							'std' 	=> ''
						),

						array(
							'name'  => __( 'Tab Font Color', 'avia_framework' ),
							'desc'  => __( 'Select a custom text color for all tabs. Enter no value if you want to use the standard font color.', 'avia_framework' ),
							'id'    => 'color',
							'type'  => 'colorpicker',
							'rgba'	=> true,
							'std' 	=> ''
						),

						array(
							'name'  => __( 'Active Tab Font Color', 'avia_framework' ),
							'desc'  => __( 'Select a custom text color for the active tab. Enter no value if you want to use the standard font color.', 'avia_framework' ),
							'id'    => 'active_color',
							'type'  => 'colorpicker',
							'rgba'	=> true,
							'std' 	=> ''
						),

						array(
							'name'  => __( 'Tab Font Color On Hover', 'avia_framework' ),
							'desc'  => __( 'Select a custom text color for all tabs on hover. Enter no value if you want to use the standard font color.', 'avia_framework' ),
							'id'    => 'color_hover',
							'type'  => 'colorpicker',
							'rgba'	=> true,
							'std' 	=> ''
						),

						array(
							'name'  => __( 'Active Tab Font Color On Hover', 'avia_framework' ),
							'desc'  => __( 'Select a custom text color for the active tab on hover. Enter no value if you want to use the standard font color.', 'avia_framework' ),
							'id'    => 'active_color_hover',
							'type'  => 'colorpicker',
							'rgba'	=> true,
							'std' 	=> ''
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
							'name' 	=> __( 'Content transition', 'avia_framework' ),
							'desc'  => __( 'Define the transition between tab content', 'avia_framework' ),
							'id' 	=> 'transition',
							'type' 	=> 'select',
							'std' 	=> 'av-tab-no-transition',
							'subtype'	=> array(
												__( 'None', 'avia_framework' )	=> 'av-tab-no-transition',
												__( 'Slide', 'avia_framework' )	=> 'av-tab-slide-transition',
											)
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
		 * @param array $params			holds the default values for $content and $args.
		 * @return array				usually holds an innerHtml key that holds item specific markup.
		 */
		public function editor_element( $params )
		{
			extract( $params );

			avia_sc_tab_section::$tab = 0;
			avia_sc_tab_section::$tab_titles = array();
			avia_sc_tab_section::$admin_active = ! empty( $args['av_admin_tab_active'] ) ? $args['av_admin_tab_active'] : 1;


			$name = $this->config['shortcode'];
			$data['shortcodehandler'] 	= $this->config['shortcode'];
			$data['modal_title'] 		= $this->config['name'];
			$data['modal_ajax_hook'] 	= $this->config['shortcode'];
			$data['dragdrop-level'] 	= $this->config['drag-level'];
			$data['allowed-shortcodes']	= $this->config['shortcode'];

			if( ! empty( $this->config['modal_on_load'] ) )
			{
				$data['modal_on_load'] 	= $this->config['modal_on_load'];
			}

			$dataString  = AviaHelper::create_data_string( $data );


			if( $content )
			{
				$final_content = $this->builder->do_shortcode_backend( $content );
				$text_area = ShortcodeHelper::create_shortcode_by_array( $name, $content, $args );
			}
			else
			{
				$tab = new avia_sc_tab_sub_section( $this->builder );
				$params = array(
								'content'	=> '',
								'args'		=> array(),
								'data'		=> ''
							);

				$final_content  = '';
				$final_content .= $tab->editor_element( $params );
				$final_content .= $tab->editor_element( $params );
				$final_content .= $tab->editor_element( $params );
				$final_content .= $tab->editor_element( $params );
				$text_area = ShortcodeHelper::create_shortcode_by_array( $name, '[av_tab_sub_section][/av_tab_sub_section][av_tab_sub_section][/av_tab_sub_section][av_tab_sub_section][/av_tab_sub_section][av_tab_sub_section][/av_tab_sub_section]', $args );

			}

			$title_id = ! empty( $args['id'] ) ? ': ' . ucfirst( $args['id'] ) : '';
			$hidden_el_active = ! empty( $args['av_element_hidden_in_editor'] ) ? 'av-layout-element-closed' : '';



			$output  = "<div class='avia_tab_section {$hidden_el_active} avia_layout_section avia_pop_class avia-no-visual-updates {$name} av_drag' {$dataString}>";

			$output .=		"<div class='avia_sorthandle menu-item-handle'>";
			$output .=			"<span class='avia-element-title'>{$this->config['name']}<span class='avia-element-title-id'>{$title_id}</span></span>";
			$output .=			"<a class='avia-delete'  href='#delete' title='" . __( 'Delete Tab Section', 'avia_framework' ) . "'>x</a>";
			$output .=			"<a class='avia-toggle-visibility'  href='#toggle' title='" . __( 'Show/Hide Tab Section', 'avia_framework' ) . "'></a>";

			if( ! empty( $this->config['popup_editor'] ) )
			{
				$output .=		"<a class='avia-edit-element'  href='#edit-element' title='" . __( 'Edit Tab Section', 'avia_framework' ) . "'>" . __( 'edit', 'avia_framework' ) . '</a>';
			}

			$output .=			"<a class='avia-save-element'  href='#save-element' title='" . __( 'Save Element as Template', 'avia_framework' ) . "'>+</a>";
			$output .=			"<a class='avia-clone'  href='#clone' title='" . __( 'Clone Tab Section', 'avia_framework' ) . "' >" . __( 'Clone Tab Section', 'avia_framework' ) . '</a>';
			$output .=		'</div>';

			$output .=		"<div class='avia_inner_shortcode avia_connect_sort av_drop' data-dragdrop-level='{$this->config['drop-level']}'>";

			$output  .=			"<div class='avia_tab_section_titles'>";

			//create tabs
			for( $i = 1; $i <= avia_sc_tab_section::$tab; $i ++ )
			{
				$active_tab = $i == avia_sc_tab_section::$admin_active ? 'av-admin-section-tab-active' : '';
				$tab_title = isset( avia_sc_tab_section::$tab_titles[$i] ) ? avia_sc_tab_section::$tab_titles[$i] : '';

				$output  .=			"<a href='#' data-av-tab-section-title='{$i}' class='av-admin-section-tab {$active_tab}'><span class='av-admin-section-tab-move-handle'></span><span class='av-tab-title-text-wrap-full'>" . __( 'Tab', 'avia_framework' ) . " <span class='av-tab-nr'>{$i}</span><span class='av-tab-custom-title'>{$tab_title}</span></span></a>";
			}

			//$output .=			"<a class='avia-clone-tab avia-add'  href='#clone-tab' title='".__('Clone Last Tab', 'avia_framework' )."'>".__('Clone Last Tab', 'avia_framework' )."</a>";
			$output .=				"<a class='avia-add-tab avia-add'  href='#add-tab' title='" . __( 'Add Tab', 'avia_framework' ) . "'>" . __( 'Add Tab', 'avia_framework' ) . '</a>';
			$output .=			'</div>';

			$output .=			"<textarea data-name='text-shortcode' cols='20' rows='4'>{$text_area}</textarea>";
			$output .=			$final_content;

			$output .=		'</div>';

			$output .=		"<a class='avia-layout-element-hidden' href='#'>" . __( 'Tab Section content hidden. Click here to show it', 'avia_framework' ) . '</a>';

			$output .= '</div>';

			return $output;
		}

		/**
		 * Create custom stylings
		 *
		 * @since 4.8.9
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles( array $args )
		{
			$result = parent::get_element_styles( $args );

			extract( $result );

			$default = array(
						'initial'				=> 1,
						'tab_pos'				=> 'av-tab-above-content',
						'content_height'		=> '',
						'tab_arrows'			=> '',
						'padding'				=> 'default',
						'tab_padding'			=> 'default',
						'bg_color'				=> '',
						'color'					=> '',
						'active_color'			=> '',
						'color_hover'			=> '',
						'active_color_hover'	=> '',
						'transition'			=> 'av-tab-no-transition',
						'id'					=> ''
					);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			//	store atts to be accessible by tab subsections
			avia_sc_tab_section::$tab_element_id = $element_id;
			avia_sc_tab_section::$tab = 0;
			avia_sc_tab_sub_section::$attr = $atts;

			avia_sc_tab_section::$tab_atts = array();
			avia_sc_tab_section::$sub_tab_element_id = array();


			$classes = array(
						'av-tab-section-outer-container',
						$element_id
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_responsive_classes( 'container', 'hide_element', $atts );

			$classes = array(
						'av-tab-section-inner-container',
						'avia-section-' . $atts['padding']
					);

			$element_styling->add_classes( 'inner-container', $classes );


			$classes = array(
						'av-tab-section-tab-title-container',
						'avia-tab-title-padding-' . $atts['tab_padding']
					);

			$element_styling->add_classes( 'tab-title-container', $classes );

			$element_styling->add_styles( 'tab-title-container', array( 'background-color' => $atts['bg_color'] ) );

			if( ! empty( $atts['color'] ) )
			{
				$element_styling->add_styles( 'tab-title', array( 'color' => $atts['color'] ) );
				$element_styling->add_classes( 'tab-title-container', 'av-custom-tab-color' );
			}

			$element_styling->add_styles( 'tab-title-active', array( 'color' => $atts['active_color'] ) );
			$element_styling->add_styles( 'tab-title-hover', array( 'color' => $atts['color_hover'] ) );
			$element_styling->add_styles( 'tab-title-active-hover', array( 'color' => $atts['active_color_hover'] ) );



			$selectors = array(
						'container'					=> ".av-tab-section-outer-container.{$element_id}",
						'inner-container'			=> ".av-tab-section-outer-container.{$element_id} .av-tab-section-inner-container",
						'tab-title-container'		=> ".av-tab-section-outer-container.{$element_id} .av-tab-section-tab-title-container",
						'tab-title'					=> "#top .av-tab-section-outer-container.{$element_id} .av-section-tab-title",
						'tab-title-active'			=> "#top .av-tab-section-outer-container.{$element_id} .av-active-tab-title.av-section-tab-title",
						'tab-title-hover'			=> "#top .av-tab-section-outer-container.{$element_id} .av-section-tab-title:hover",
						'tab-title-active-hover'	=> "#top .av-tab-section-outer-container.{$element_id} .av-active-tab-title.av-section-tab-title:hover"
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


			//	are filled in avia_sc_tab_sub_section
			avia_sc_tab_section::$tab = 0;
			avia_sc_tab_section::$tab_titles = array();
			avia_sc_tab_section::$tab_icons = array();
			avia_sc_tab_section::$tab_images = array();
			avia_sc_tab_section::$count++;

			$final_content = ShortcodeHelper::avia_remove_autop( $content, true ) ;

			$width = avia_sc_tab_section::$tab * 100;
			$tabs = '';
			$arrow = '<span class="av-tab-arrow-container"><span></span></span>';

			if( $atts['initial'] <= 0 )
			{
				$atts['initial'] = 1;
			}
			else if( $atts['initial'] > avia_sc_tab_section::$tab )
			{
				$atts['initial'] = avia_sc_tab_section::$tab;
			}

			for( $i = 1; $i <= avia_sc_tab_section::$tab; $i ++ )
			{
				$icon 	= ! empty( avia_sc_tab_section::$tab_icons[ $i ] ) ? avia_sc_tab_section::$tab_icons[ $i ] : '';
				$image  = ! empty( avia_sc_tab_section::$tab_images[ $i ] ) ? avia_sc_tab_section::$tab_images[ $i ] : '';

				$extraClass  = avia_sc_tab_section::$sub_tab_element_id[ $i - 1 ] . ' ';
				$extraClass .= ! empty( $icon ) ? 'av-tab-with-icon ' : 'av-tab-no-icon ';
				$extraClass .= ! empty( $image ) ? 'av-tab-with-image noHover ' : 'av-tab-no-image ';
				$extraClass .= avia_sc_tab_section::$tab_atts[ $i ]['tab_image_style'];

				/**
				 * Bugfix: Set no-scroll to avoid auto smooth scroll when initialising tab section and multiple tab sections are on a page - removed in js.
				 */
				$active_tab = $i == $atts['initial'] ? 'av-active-tab-title no-scroll' : '';

				$tab_title = ! empty( avia_sc_tab_section::$tab_titles[ $i ] ) ? avia_sc_tab_section::$tab_titles[ $i ] : '';
				if( $tab_title == '' && empty( $image ) && empty( $icon ) )
				{
					$tab_title = __( 'Tab', 'avia_framework' ) . ' ' . $i;
				}

				$tab_link = AviaHelper::valid_href( $tab_title, '-', 'av-tab-section-' . avia_sc_tab_section::$count . '-' . $i );
				$tab_id = 'av-tab-section-' . avia_sc_tab_section::$count . '-' . $i;

				/**
				 * layout is broken since adding aria-controls $tab_id with 4.7.6
				 * Fixes problem with non latin letters like greek
				 */
				if( $tab_id == $tab_link )
				{
					$tab_link .= '-link';
				}

				if( $tab_title == '' )
				{
					$extraClass .= ' av-tab-without-text ';
				}

				/**
				 * @since 4.8
				 * @param string $tab_link
				 * @param string $tab_title
				 * @return string
				 */
				$tab_link = apply_filters( 'avf_tab_section_link_hash', $tab_link, $tab_title );

				$tabs .= "<a href='#{$tab_link}' data-av-tab-section-title='{$i}' class='av-section-tab-title {$active_tab} {$extraClass}' role='tab' tabindex='0' aria-controls='{$tab_id}'>";
				$tabs .=	$icon;
				$tabs .=	$image;
				$tabs .=	"<span class='av-outer-tab-title'>";
				$tabs .=		"<span class='av-inner-tab-title'>{$tab_title}</span>";
				$tabs .=	'</span>';
				$tabs .=	$arrow;
				$tabs .= '</a>';
			}


			$style_tag = $element_styling->get_style_tag( $element_id );
			$av_display_classes = $element_styling->responsive_classes_string( 'hide_element', $atts );
			$container_class = $element_styling->get_class_string( 'container' );
			$inner_container_class = $element_styling->get_class_string( 'inner-container' );
			$title_container_class = $element_styling->get_class_string( 'tab-title-container' );


			$params['class'] = "av-tab-section-container entry-content-wrapper main_color {$transition} {$content_height} {$av_display_classes} {$tab_pos} {$meta['el_class']}";
			$params['open_structure'] = false;
			$params['id'] = AviaHelper::save_string( $id, '-', 'av-tab-section-' . avia_sc_tab_section::$count );
			$params['custom_markup'] = $meta['custom_markup'];
			$params['aria_label'] = $meta['aria_label'];

			//we dont need a closing structure if the element is the first one or if a previous fullwidth element was displayed before
			if( isset( $meta['index'] ) && $meta['index'] == 0 )
			{
				$params['close'] = false;
			}
			if( ! empty( $meta['siblings']['prev']['tag'] ) && in_array( $meta['siblings']['prev']['tag'], AviaBuilder::$full_el_no_section ) )
			{
				$params['close'] = false;
			}

			if( isset( $meta['index'] ) && $meta['index'] > 0 )
			{
				$params['class'] .= ' tab-section-not-first';
			}


			$tabs_final  = "<div class='{$title_container_class}' role='tablist'>{$tabs}</div>";
			$tabs_final .= $this->slide_navigation_arrows( $atts );

			$output  = '';
			$output .= $style_tag;
			$output .= avia_new_section( $params );
			$output .= "<div class='{$container_class}'>";

			if( $tab_pos == 'av-tab-above-content' )
			{
				$output .=  $tabs_final;
			}

			$output .=		"<div class='{$inner_container_class}' style='width:{$width}vw; left:" . ( ( $atts['initial'] -1 ) * -100 ) . "%;'>";

			//	dummy structure to implement swipe action
			$output .=			'<span class="av_prev_tab_section av_tab_navigation"></span>';
			$output .=			'<span class="av_next_tab_section av_tab_navigation"></span>';
			$output .=			$final_content;
			$output .=		'</div>';

			if( $tab_pos == 'av-tab-below-content' )
			{
				$output .=  $tabs_final;
			}

			$output .= '</div>';
			$output .= avia_section_after_element_content( $meta , 'after_tab_section_' . avia_sc_tab_section::$count, false );

			// added to fix https://kriesi.at/support/topic/footer-disseapearing/#post-427764
			avia_sc_section::$close_overlay = '';


			return $output;
		}

		/**
		 * Create arrows to scroll slides
		 *
		 * @since 4.8.3			reroute to aviaFrontTemplates
		 * @param array $atts
		 * @return string
		 */
		protected function slide_navigation_arrows( array $atts )
		{
			if( $atts['tab_arrows'] != '' )
			{
				return '';
			}

			$args = array(
						'class_main'	=> 'avia-slideshow-arrows av-tabsection-arrow',
						'class_prev'	=> 'av_prev_tab_section av-tab-section-slide',
						'class_next'	=> 'av_next_tab_section av-tab-section-slide',
						'context'		=> get_class( $this ),
						'params'		=> $atts
					);

			return aviaFrontTemplates::slide_navigation_arrows( $args );
		}
	}
}

