<?php
/**
 * Tabs
 *
 * Creates a tabbed content area
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_sc_tab', false ) )
{
	class avia_sc_tab extends aviaShortcodeTemplate
	{
		/**
		 *
		 * @var int
		 */
		static protected $tab_id = 1;

		/**
		 *
		 * @var int
		 */
		static protected $counter = 1;

		/**
		 *
		 * @var int
		 */
		static protected $initial = 1;

		/**
		 *
		 * @since 4.8.8
		 * @var boolean
		 */
		protected $in_sc_exec;

		/**
		 *
		 * @since 4.5.5
		 * @param AviaBuilder $builder
		 */
		public function __construct( $builder )
		{
			$this->in_sc_exec = false;

			parent::__construct( $builder );
		}

		/**
		 * @since 4.5.5
		 */
		public function __destruct()
		{
			parent::__destruct();
		}

		/**
		 * Create the config array for the shortcode button
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['self_closing']	= 'no';
			$this->config['base_element']	= 'yes';

			$this->config['name']			= __( 'Tabs', 'avia_framework' );
			$this->config['tab']			= __( 'Content Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-tabs.png';
			$this->config['order']			= 75;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode']		= 'av_tab_container';
			$this->config['shortcode_nested'] = array( 'av_tab' );
			$this->config['tooltip']		= __( 'Creates a tabbed content area', 'avia_framework' );
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
			$this->config['alb_desc_id']	= 'alb_description';
			$this->config['name_item']		= __( 'Tabs Item', 'avia_framework' );
			$this->config['tooltip_item']	= __( 'A single tabs item', 'avia_framework' );
		}


		protected function admin_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_js = avia_minify_extension( 'js' );

			wp_register_script( 'avia_tab_toggle_js', AviaBuilder::$path['assetsURL'] . "js/avia-tab-toggle{$min_js}.js", array( 'avia_modal_js' ), $ver, true );
			Avia_Builder()->add_registered_admin_script( 'avia_tab_toggle_js' );
		}

		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_js = avia_minify_extension( 'js' );
			$min_css = avia_minify_extension( 'css' );

			//load css
			wp_enqueue_style( 'avia-module-tabs', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/tabs/tabs{$min_css}.css", array( 'avia-layout' ), $ver );

			//load js
			wp_enqueue_script( 'avia-module-tabs', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/tabs/tabs{$min_js}.js", array( 'avia-shortcodes' ), $ver, true );
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
							'template_id'	=> $this->popup_key( 'content_tabs' )
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
							'template_id'	=> $this->popup_key( 'styling_tabs' )
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
							'name'			=> __( 'Add/Edit Tabs', 'avia_framework' ),
							'desc'			=> __( 'Here you can add, remove and edit the Tabs you want to display.', 'avia_framework' ),
							'type'			=> 'modal_group',
							'id'			=> 'content',
							'modal_title'	=> __( 'Edit Form Element', 'avia_framework' ),
							'container_class' => 'avia-element-fullwidth avia-tab-container',
							'editable_item'	=> true,
							'lockable'		=> true,
							'tmpl_set_default'	=> false,
							'std'			=> array(
													array( 'title' => __( 'Tab 1', 'avia_framework' ) ),
													array( 'title' => __( 'Tab 2', 'avia_framework' ) ),
												),
							'subelements'	=> $this->create_modal()
						),

						array(
							'name' 	=> __( 'Initial Open', 'avia_framework' ),
							'desc' 	=> __( 'Enter the Number of the Tab that should be open initially.', 'avia_framework' ),
							'id' 	=> 'initial',
							'type' 	=> 'input',
							'std' 	=> '1',
							'lockable'	=> true
						)
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_tabs' ), $c );

			/**
			 * Content Tab
			 * ===========
			 */

			$c = array(
						array(
							'name' 	=> __( 'Tab Position', 'avia_framework' ),
							'desc' 	=> __( 'Where should the tabs be displayed', 'avia_framework' ),
							'id' 	=> 'position',
							'type' 	=> 'select',
							'std' 	=> 'top_tab',
							'container_class' => 'avia-element-fullwidth',
							'target'	=> array( '#aviaTBcontent-form-container', 'class' ),
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Display tabs at the top', 'avia_framework' )	=> 'top_tab',
												__( 'Display Tabs on the left', 'avia_framework' )	=> 'sidebar_tab sidebar_tab_left',
												__( 'Display Tabs on the right', 'avia_framework' )	=> 'sidebar_tab sidebar_tab_right'
											)
						),

						array(
							'name' 	=> __( 'Boxed Tabs', 'avia_framework' ),
							'desc' 	=> __( 'Do you want to display a border around your tabs or without border', 'avia_framework' ),
							'id' 	=> 'boxed',
							'type' 	=> 'select',
							'std' 	=> 'border_tabs',
							'lockable'	=> true,
							'required'	=> array( 'position', 'contains', 'sidebar_tab' ),
							'subtype'	=> array(
												__( 'With border', 'avia_framework' )		=> 'border_tabs',
												__( 'Without border', 'avia_framework' )	=> 'noborder_tabs'
											)
						)

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_tabs' ), $c );

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
							'template_id'	=> $this->popup_key( 'modal_content_tab' )
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
								'template_id'	=> 'developer_options_toggle',
								'args'			=> array(
														'sc'		=> $this,
														'nested'	=> 'av_tab'
													)
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
							'name' 	=> __( 'Tab Title', 'avia_framework' ),
							'desc' 	=> __( 'Enter the tab title here (Better keep it short)', 'avia_framework' ),
							'id' 	=> 'title',
							'type' 	=> 'input',
							'std' 	=> 'Tab Title',
							'lockable'	=> true
						),

						array(
							'name' 	=> __( 'Tab Icon', 'avia_framework' ),
							'desc' 	=> __( 'Should an icon be displayed at the left side of the tab title?', 'avia_framework' ),
							'id' 	=> 'icon_select',
							'type' 	=> 'select',
							'std' 	=> 'no',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'No Icon', 'avia_framework' )			=> 'no',
												__( 'Yes, display Icon', 'avia_framework' )	=> 'yes'
											)
						),

						array(
							'name' 	=> __( 'Tab Icon','avia_framework' ),
							'desc' 	=> __( 'Select an icon for your tab title below', 'avia_framework' ),
							'id' 	=> 'icon',
							'type' 	=> 'iconfont',
							'std' 	=> '',
							'lockable'	=> true,
							'locked'	=> array( 'icon', 'font' ),
							'required'	=> array( 'icon_select', 'equals', 'yes' )
						),

						array(
							'name' 	=> __( 'Tab Content', 'avia_framework' ),
							'desc' 	=> __( 'Enter some content here', 'avia_framework' ),
							'id' 	=> 'content',
							'type' 	=> 'tiny_mce',
							'std' 	=> __( 'Tab Content goes here', 'avia_framework' ),
							'lockable'	=> true
						),

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_content_tab' ), $c );

		}

		/**
		 * Return a config array for the nested shortcde
		 *
		 * @since 4.6.4
		 * @param string $nested_shortcode
		 * @return array
		 */
		protected function get_nested_developer_elements( $nested_shortcode )
		{
			$config = array();

			if( 'av_tab' == $nested_shortcode )
			{
				$config['id_name'] = 'custom_id';
				$config['id_show'] = 'yes';
				$config['custom_css_show'] = 'never';
			}

			return $config;
		}

		/**
		 * Editor Sub Element - this function defines the visual appearance of an element that is displayed within a modal window and on click opens its own modal window
		 * Works in the same way as Editor Element
		 * @param array $params		holds the default values for $content and $args.
		 * @return array			usually holds an innerHtml key that holds item specific markup.
		 */
		public function editor_sub_element( $params )
		{
			$default = array();
			$locked = array();
			$attr = $params['args'];
			$content = $params['content'];
			Avia_Element_Templates()->set_locked_attributes( $attr, $this, $this->config['shortcode_nested'][0], $default, $locked, $content );

			$title_templ = $this->update_option_lockable( 'title', $locked );
			$content_templ = $this->update_option_lockable( 'content', $locked );

			extract( av_backend_icon( array( 'args' => $attr ) ) ); // creates $font and $display_char if the icon was passed as param 'icon' and the font as 'font'

			$params['innerHtml']  = '';
			$params['innerHtml'] .= "<div class='avia_title_container' data-update_element_template='yes'>";
			$params['innerHtml'] .=		'<span ' . $this->class_by_arguments_lockable( 'icon_select', $attr, $locked ) . '>';
			$params['innerHtml'] .=			'<span ' . $this->class_by_arguments_lockable( 'font', $font, $locked ) . '>';
			$params['innerHtml'] .=				'<span ' . $this->update_option_lockable( array( 'icon', 'icon_fakeArg' ), $locked ) . " class='avia_tab_icon' >{$display_char}</span>";
			$params['innerHtml'] .=			'</span>';
			$params['innerHtml'] .=		'</span>';
			$params['innerHtml'] .=		"<span class='avia_title_container_inner' {$title_templ} >{$attr['title']}</span>";
			$params['innerHtml'] .= '</div>';

			$params['innerHtml'] .= "<div class='avia_content_container' {$content_templ}>";
			$params['innerHtml'] .=		stripcslashes( $content );
			$params['innerHtml'] .= '</div>';

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
						'initial'	=> '1',
						'position'	=> 'top_tab',
						'boxed'		=> 'border_tabs'
					);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$this->in_sc_exec = true;

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			$tab_sc = ShortcodeHelper::shortcode2array( $content, 1 );

			if( ! is_numeric( $atts['initial'] ) || ( $atts['initial'] < 1 ) )
			{
				$atts['initial'] = 1;
			}
			if( $atts['initial'] > count( $tab_sc ) )
			{
				$atts['initial'] = count( $tab_sc );
			}

			avia_sc_tab::$counter = 1;
			avia_sc_tab::$initial = $atts['initial'];


			$classes = array(
						'tabcontainer',
						$element_id,
						$atts['position']
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $meta, 'el_class' );
			$element_styling->add_responsive_classes( 'container', 'hide_element', $atts );

			if( $atts['position'] != 'top_tab' )
			{
				$element_styling->add_classes( 'container', $atts['boxed'] );
			}


			$selectors = array(
						'container'	=> ".tabcontainer.{$element_id}"
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
		 *
		 * @since 4.8.4
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles_item( array $args )
		{
			$result = parent::get_element_styles_item( $args );

			/**
			 * Fixes a problem when 3-rd party plugins call nested shortcodes without executing main shortcode  (like YOAST in wpseo-filter-shortcodes)
			 */
			if( ! $this->in_sc_exec )
			{
				return $result;
			}

			extract( $result );

			$default = array(
						'title'			=> '',
						'icon_select'	=> 'no',
						'icon'			=> '',
						'custom_id'		=> '',
						'font'			=> '',
						'custom_markup'	=> '',
						'skip_markup'	=> ''			//	'yes' if markup should be skipped. Used e.g. for privacy modal popup throwing errors in check on blog posts
					);

			$default = $this->sync_sc_defaults_array( $default, 'modal_item', 'no_content' );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode_nested'][0] );

			$classes = array(
						'av_tab_section',
						$element_id
					);

			$element_styling->add_classes( 'container', $classes );

			$element_styling->add_classes( 'tab', 'tab' );
			$element_styling->add_classes( 'tab-content', 'tab_content' );

			if( is_numeric( avia_sc_tab::$initial ) && avia_sc_tab::$counter == avia_sc_tab::$initial )
			{
				$element_styling->add_classes( 'tab', 'active_tab' );
				$element_styling->add_classes( 'tab-content', 'active_tab_content' );
			}


			$selectors = array(
						'container'	=> ".tabcontainer .av_tab_section.{$element_id}"
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

			$content_html = ShortcodeHelper::avia_remove_autop( $content, true );

			$style_tag = $element_styling->get_style_tag( $element_id );
			$item_tag = $element_styling->style_tag_html( $this->subitem_inline_styles, 'sub-' . $element_id );
			$container_class = $element_styling->get_class_string( 'container' );

			$output  = '';
			$output .= $style_tag;
			$output .= $item_tag;
			$output .= "<div {$meta['custom_el_id']} class='{$container_class}' role='tablist'>";
			$output .=		$content_html;
			$output .= '</div>';

			$this->in_sc_exec = false;

			return $output;
		}

		/**
		 * Shortcode handler
		 *
		 * @param array $atts
		 * @param string $content
		 * @param string $shortcodename
		 * @return string
		 */
		public function av_tab( $atts, $content = '', $shortcodename = '' )
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

			$icon = '';
			if( $atts['icon_select'] == 'yes' )
			{
				$display_char = av_icon( $atts['icon'], $atts['font'] );
				$icon = "<span class='tab_icon' {$display_char}></span>";
			}

			$aria_content = 'aria-hidden="true"';

			if( is_numeric( avia_sc_tab::$initial ) && avia_sc_tab::$counter == avia_sc_tab::$initial )
			{
				$aria_content = 'aria-hidden="false"';
			}

			if( empty( $atts['title'] ) )
			{
				$atts['title'] = avia_sc_tab::$counter;
			}

			$setting_id = Avia_Builder()->get_developer_settings( 'custom_id' );
			if( empty( $atts['custom_id'] ) || in_array( $setting_id, array( 'deactivate' ) ) )
			{
				$atts['custom_id'] = 'tab-id-' . avia_sc_tab::$tab_id++;
			}
			else
			{
				$atts['custom_id'] = AviaHelper::save_string( $atts['custom_id'], '-' );
			}

			$markup_tab = '';
			$markup_title = '';
			$markup_text = '';

			if( 'yes' != $atts['skip_markup'] )
			{
				$markup_tab = avia_markup_helper( array( 'context' => 'entry', 'echo' => false, 'custom_markup' => $atts['custom_markup'] ) );
				$markup_title = avia_markup_helper( array( 'context' => 'entry_title', 'echo' => false, 'custom_markup' => $atts['custom_markup'] ) );
				$markup_text = avia_markup_helper( array( 'context' => 'entry_content', 'echo' => false, 'custom_markup' => $atts['custom_markup'] ) );
			}

			$this->subitem_inline_styles .= $element_styling->get_style_tag( $element_id, 'rules_only' );
			$container_class = $element_styling->get_class_string( 'container' );
			$tab_class = $element_styling->get_class_string( 'tab' );
			$tab_content_class = $element_styling->get_class_string( 'tab-content' );

			$output  = '';
			$output .= "<section class='av_tab_section $container_class' {$markup_tab}>";
			$output .=		"<div class='{$tab_class}' role='tab' tabindex='0' data-fake-id='#{$atts['custom_id']}' aria-controls='{$atts['custom_id']}-content' {$markup_title}>{$icon}{$atts['title']}</div>";
			$output .=		"<div id='{$atts['custom_id']}-content' class='{$tab_content_class}' {$aria_content}>";
			$output .=			"<div class='tab_inner_content invers-color' $markup_text>";
			$output .=				ShortcodeHelper::avia_apply_autop( ShortcodeHelper::avia_remove_autop( $content ) );
			$output .=			'</div>';
			$output .=		'</div>';
			$output .= '</section>';

			avia_sc_tab::$counter ++;

			return $output;
		}

	}
}
