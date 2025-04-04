<?php
/**
 * Magazine
 *
 * Display entries in a magazine like fashion
 * Element is in Beta and by default disabled. Todo: test with layerslider elements. currently throws error bc layerslider is only included if layerslider element is detected which is not the case with the post/page element
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_sc_magazine', false ) )
{
	class avia_sc_magazine extends aviaShortcodeTemplate
	{
		/**
		 * Save avia_magazine objects for reuse. As we need to access the same object when creating the post css file in header,
		 * create the styles and HTML creation. Makes sure to get the same id.
		 *
		 *			$element_id	=> avia_magazine
		 *
		 * @since 4.8.8
		 * @var array
		 */
		protected $obj_magazines;

		/**
		 * @since 4.8.8
		 * @param AviaBuilder $builder
		 */
		public function __construct( AviaBuilder $builder )
		{
			parent::__construct( $builder );

			$this->obj_magazines = array();
		}

		/**
		 * @since 4.8.8
		 */
		public function __destruct()
		{
			unset( $this->obj_magazines );

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

			$this->config['name']			= __( 'Magazine', 'avia_framework' );
			$this->config['tab']			= __( 'Content Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-magazine.png';
			$this->config['order']			= 39;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode']		= 'av_magazine';
			$this->config['tooltip']		= __( 'Display entries in a magazine like fashion', 'avia_framework' );
			$this->config['drag-level']		= 3;
			$this->config['preview']		= 1;
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
			$this->config['alb_desc_id']	= 'alb_description';
		}

		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_js = avia_minify_extension( 'js' );
			$min_css = avia_minify_extension( 'css' );

			//load css
			wp_enqueue_style( 'avia-module-magazine', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/magazine/magazine{$min_css}.css", array( 'avia-layout' ), $ver );

			//load js
			wp_enqueue_script( 'avia-module-magazine', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/magazine/magazine{$min_js}.js", array( 'avia-shortcodes' ), $ver, true );
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
													$this->popup_key( 'content_entries' ),
													$this->popup_key( 'content_filter' )
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
													$this->popup_key( 'styling_general' ),
													$this->popup_key( 'styling_pagination' ),
													$this->popup_key( 'styling_colors' ),
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
								'template_id'	=> $this->popup_key( 'advanced_heading' ),
								'nodescription' => true
							),

						array(
								'type'			=> 'template',
								'template_id'	=> $this->popup_key( 'advanced_link' ),
								'nodescription' => true
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
							'name' 	=> __( 'Which Entries?', 'avia_framework' ),
							'desc' 	=> __( 'Select which entries should be displayed by selecting a taxonomy', 'avia_framework' ),
							'id' 	=> 'link',
							'type' 	=> 'linkpicker',
							'multiple'	=> 6,
							'std'		=> 'category',
							'fetchTMPL'	=> true,
							'lockable'	=> true,
							'subtype'	=> array( __( 'Display Entries from:', 'avia_framework' ) => 'taxonomy' )
						),

						array(
							'name' 	=> __( 'Display Tabs for each category selected above?', 'avia_framework' ),
							'desc' 	=> __( 'If checked and you have selected more than one taxonomy above, a tab will be displayed for each of them. Will be ignored when using Pagination.', 'avia_framework' ),
							'id' 	=> 'tabs',
							'type' 	=> 'checkbox',
							'std' 	=> 'true',
							'lockable'	=> true
						),

						array(
							'name' 	=> __( 'Display Thumbnails?', 'avia_framework' ),
							'desc' 	=> __( 'If checked all entries that got a feature image will show it', 'avia_framework' ),
							'id' 	=> 'thumbnails',
							'type' 	=> 'checkbox',
							'std' 	=> 'true',
							'container_class'	=> 'av_half av_half_first',
							'lockable'	=> true
						),

						array(
							'name' 	=> __( 'Display Author?', 'avia_framework' ),
							'desc' 	=> __( 'If checked author of this entry will be shown', 'avia_framework' ),
							'id' 	=> 'meta_author',
							'type' 	=> 'checkbox',
							'std' 	=> 'true',
							'container_class'	=> 'av_half',
							'lockable'	=> true
						),

						array(
							'name' 	=> __( 'Display Categories?', 'avia_framework' ),
							'desc' 	=> __( 'If checked categories of this entry will be shown', 'avia_framework' ),
							'id' 	=> 'meta_cats',
							'type' 	=> 'checkbox',
							'std' 	=> 'true',
							'container_class'	=> 'av_half av_half_first',
							'lockable'	=> true
						),

						array(
							'name' 	=> __( 'Display Tags?', 'avia_framework' ),
							'desc' 	=> __( 'If checked tags of this entry will be shown', 'avia_framework' ),
							'id' 	=> 'meta_tags',
							'type' 	=> 'checkbox',
							'std' 	=> 'true',
							'container_class'	=> 'av_half',
							'lockable'	=> true
						),

						array(
							'name' 	=> __( 'Display Element Heading?', 'avia_framework' ),
							'desc' 	=> __( 'If checked you can enter a heading title with a link for this element', 'avia_framework' ),
							'id' 	=> 'heading_active',
							'type' 	=> 'checkbox',
							'std' 	=> '',
							'lockable'	=> true
						),

						array(
							'name' 	=> __( 'Heading Text', 'avia_framework' ),
							'desc' 	=> __( 'Enter a custom heading text here. Options to customize heading tag and link are located in the &quot;Advanced&quot; tab.', 'avia_framework' ),
							'id' 	=> 'heading',
							'type' 	=> 'input',
							'std' 	=> '',
							'required'	=> array( 'heading_active', 'not', '' ),
							'lockable'	=> true,
							'tmpl_set_default'	=> false
						)
				);

			if( current_theme_supports( 'add_avia_builder_post_type_option' ) )
			{
				$element = array(
								'type'			=> 'template',
								'template_id'	=> 'avia_builder_post_type_option',
								'lockable'		=> true
							);

				array_unshift( $c, $element );
			}

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Select Entries', 'avia_framework' ),
								'content'		=> $c
							),
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_entries' ), $template );

			$c = array(
						array(
							'type'			=> 'template',
							'template_id' 	=> 'date_query',
							'lockable'		=> true,
							'period'		=> true
						),

						array(
							'type'			=> 'template',
							'template_id' 	=> 'page_element_filter',
							'lockable'		=> true
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Filter', 'avia_framework' ),
								'content'		=> $c
							),
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_filter' ), $template );

			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name'		=> __( 'Number of entries per page', 'avia_framework' ),
							'desc'		=> __( 'How many entries should be displayed?', 'avia_framework' ),
							'id'		=> 'items',
							'type'		=> 'select',
							'std'		=> '5',
							'lockable'	=> true,
							'subtype'	=> AviaHtmlHelper::number_array( 1, 100, 1, array( 'All' => '-1' ) ) ),

						array(
							'name'		=> __( 'Pagination', 'avia_framework' ),
							'desc'		=> __( 'Should a pagination be displayed to view additional entries? This disables &quot;Display Tabs for each category&quot;.', 'avia_framework' ),
							'id'		=> 'paginate',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'items', 'not', '-1' ),
							'subtype'	=> array(
												__( 'Display Pagination', 'avia_framework' )	=> 'pagination',
												__( 'No Pagination', 'avia_framework' )			=> ''
											)
						),

						array(
							'name' 	=> __( 'Offset Number', 'avia_framework' ),
							'desc' 	=> __( 'The offset determines where the query begins pulling posts. Useful if you want to remove a certain number of posts because you already query them with another blog or magazine element.', 'avia_framework' ),
							'id' 	=> 'offset',
							'type' 	=> 'select',
							'std' 	=> '0',
							'lockable'	=> true,
							'required'	=> array( 'paginate', 'equals', '' ),
							'subtype'	=> AviaHtmlHelper::number_array( 1, 100, 1, array( __( 'Deactivate offset', 'avia_framework' ) => '0', __( 'Do not allow duplicate posts on the entire page (set offset automatically)', 'avia_framework' ) => 'no_duplicates' ) )
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Pagination', 'avia_framework' ),
								'content'		=> $c
							),
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_pagination' ), $template );

			$c = array(
						array(
							'name' 	=> __( 'Should the first entry be displayed bigger?', 'avia_framework' ),
							'desc' 	=> __( 'If checked the first entry will stand out with big image', 'avia_framework' ),
							'id' 	=> 'first_big',
							'type' 	=> 'checkbox',
							'std' 	=> '',
							'lockable'	=> true
						),

						array(
							'name' 	=> __( 'First entry position', 'avia_framework' ),
							'desc' 	=> __( 'Where do you want to display the first entry?', 'avia_framework' ),
							'id' 	=> 'first_big_pos',
							'type' 	=> 'select',
							'std' 	=> 'top',
							'lockable'	=> true,
							'required'	=> array( 'first_big', 'not', '' ),
							'subtype'	=> array(
												__( 'Display the first entry at the top of the others', 'avia_framework' )	=> 'top',
												__( 'Display the first entry beside the others', 'avia_framework' )			=> 'left'
											)
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'image_size_select',
							'name'			=> __( 'Big Image Size', 'avia_framework' ),
							'id'			=> 'image_big',
							'std'			=> 'magazine',
							'lockable'		=> true,
							'required'		=> array( 'first_big', 'not', '' )
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
							'type'			=> 'template',
							'template_id'	=> 'named_colors',
							'name'			=> __( 'Heading Area Color', 'avia_framework' ),
							'desc'			=> __( 'Choose a color for your heading area here', 'avia_framework' ),
							'id'			=> 'heading_color',
							'std'			=> 'theme-color',
							'lockable'		=> true,
							'required'		=> array( 'heading_active', 'not','' ),
							'custom'		=> true,
							'translucent'	=> array()
						),

						array(
							'name'		=> __( 'Custom Font Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom font color for your Heading area here', 'avia_framework' ),
							'id'		=> 'heading_custom_color',
							'type'		=> 'colorpicker',
							'std'		=> '#ffffff',
							'rgba'		=> true,
							'lockable'	=> true,
							'required'	=> array( 'heading_color', 'equals', 'custom' )
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


			/**
			 * Advanced Tab
			 * ===========
			 */

			$c = array(
						array(
							'type'				=> 'template',
							'template_id'		=> 'heading_tag',
							'theme_default'		=> 'a',
							'context'			=> __CLASS__,
							'lockable'			=> true,
							'required'			=> array( 'heading_active', 'not', '' )
						),

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Heading Tag', 'avia_framework' ),
								'content'		=> $c
							),
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_heading' ), $template );

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'linkpicker_toggle',
								'name'			=> __( 'Heading Text Link', 'avia_framework' ),
								'desc'			=> __( 'Where should the heading text link to?', 'avia_framework' ),
								'id'			=> 'heading_link',
								'subtypes'		=> array( 'no', 'manually', 'single', 'taxonomy' ),
								'target_id'		=> 'link_target',
								'lockable'		=> true,
								'title_attr'	=> true,
								'required'		=> array( 'heading_active', 'not', '' )
							)
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_link' ), $template );

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
			$params = parent::editor_element( $params );
			$params['content'] = null; //remove to allow content elements

			return $params;
		}

		/**
		 * Create custom stylings
		 *
		 * @since 4.8.8
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles( array $args )
		{
			$result = parent::get_element_styles( $args );

			extract( $result );

			$default = avia_magazine::default_args( $this->get_default_sc_args() );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );
			$meta = aviaShortcodeTemplate::set_frontend_developer_heading_tag( $atts, $meta );

			$add = array(
					'handle'			=> $shortcodename,
					'class'				=> '',
					'custom_markup'		=> '',
					'custom_el_id'		=> '',
					'heading_tag'		=> '',
					'heading_class'		=> '',
					'caller'			=> $this
				);

			$default = array_merge( $default, $add );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			if( ! isset( $this->obj_magazines[ $element_id ] ) )
			{
				$this->obj_magazines[ $element_id ] = new avia_magazine( $atts, $this );
			}

			$magazine = $this->obj_magazines[ $element_id ];

			$update = array(
							'class'				=> ! empty( $meta['el_class'] ) ? $meta['el_class'] : '',
							'custom_markup'		=> ! empty( $meta['custom_markup'] ) ? $meta['custom_markup'] : '',
							'custom_el_id'		=> ! empty( $meta['custom_el_id'] ) ? $meta['custom_el_id'] : '',
							'heading_tag'		=> ! empty( $meta['heading_tag'] ) ? $meta['heading_tag'] : '',
							'heading_class'		=> ! empty( $meta['heading_class'] ) ? $meta['heading_class'] : '',
						);

			$atts = $magazine->update_config( $update );

			$magazine->query_entries();

			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['element_styling'] = $element_styling;
			$result['meta'] = $meta;

			$result = $magazine->get_element_styles( $result );

			return $result;
		}

		/**
		 * Callback to return basic data that can be extended by magazine object
		 *
		 * @since 4.8.8.1
		 * @param array $args
		 * @return array
		 */
		public function get_element_styles_magazine_sub_item( array $args )
		{
			$result = parent::get_element_styles_item( $args );

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

			if( isset( $atts['img_scrset'] ) && 'disabled' == $atts['img_scrset'] )
			{
				Av_Responsive_Images()->force_disable( 'disabled' );
			}

			$magazine = $this->obj_magazines[ $element_id ];
			$html = $magazine->html();

			Av_Responsive_Images()->force_disable( 'reset' );

			return $html;
		}
	}
}


if( ! class_exists( 'avia_magazine', false ) )
{
	class avia_magazine extends \aviaBuilder\base\aviaSubItemQueryBase
	{
		/**
		 * magazine count for the current page
		 *
		 * @since < 4.0
		 * @var int
		 */
		static protected $magazine = 0;

		/**
		 * @since < 4.0
		 * @var WP_Query
		 */
		protected $entries;

		/**
		 *
		 * @since 4.7.6.4
		 * @var int
		 */
		protected $current_page;

		/**
		 * @since < 4.0
		 * @since 4.8.8.1						added $sc_context
		 * @param array $atts
		 * @param aviaShortcodeTemplate $sc_context
		 */
		public function __construct( $atts = array(), aviaShortcodeTemplate $sc_context = null )
		{
			parent::__construct( $atts, $sc_context, avia_magazine::default_args() );

//			$this->config = shortcode_atts( avia_magazine::get_defaults( $atts ), $atts, 'av_magazine' );


			// @since 4.8.6.3
			if( 'no scaling' == $this->config['image_big'] )
			{
				$this->config['image_big'] = 'full';
			}

			if( ! empty( $this->config['image_big'] ) )
			{
				$this->config['image_size']['big'] = $this->config['image_big'];
			}

			$this->entries = null;
			$this->current_page = 1;

			if( $this->config['items'] == -1 )
			{
				$this->config['paginate'] = '';
			}

			/**
			 * When pagination, tabs are not possible
			 */
			if( ! empty( $this->config['paginate'] ) )
			{
				$this->config['tabs'] = false;
				$this->config['offset'] = 0;
			}

			// fetch the taxonomy and the taxonomy ids
			$this->extract_terms();

			//convert checkbox to true/false
			$this->config['tabs'] = $this->config['tabs'] === 'aviaTBtabs' ? true : false;
			$this->config['thumbnails'] = $this->config['thumbnails'] === 'aviaTBthumbnails' ? true : false;
			$this->config['meta_author'] = $this->config['meta_author'] === 'aviaTBmeta_author' ? true : false;
			$this->config['meta_cats'] = $this->config['meta_cats'] === 'aviaTBmeta_cats' ? true : false;
			$this->config['meta_tags'] = $this->config['meta_tags'] === 'aviaTBmeta_tags' ? true : false;


			/**
			 * Filter the attributes
			 *
			 * @since ????
			 * @param array $this->config
			 * @return array
			 */
			$this->config = apply_filters( 'avf_magazine_settings', $this->config, self::$magazine );

			//set small or big
			if( empty( $this->config['first_big'] ) )
			{
				$this->config['first_big_pos'] = '';
			}

			//set heading text
			if( empty( $this->config['heading_active'] ) )
			{
				$this->config['heading'] = '';
			}

			//set if top bar is active
			$this->config['top_bar'] = ! empty( $this->config['heading'] ) || ! empty( $this->config['tabs'] )  ? 'av-magazine-top-bar-active' : '';
		}

		/**
		 *
		 * @since 4.5.6
		 */
		public function __destruct()
		{
			unset( $this->entries );

			parent::__destruct();
		}

		/**
		 * Returns the defaults array for this class
		 *
		 * @since 4.8.8.1
		 * @return array
		 */
		static public function default_args( array $args = array() )
		{
			$default = array(
						'class'					=> '',
						'custom_markup' 		=> '',
						'items' 				=> '16',
						'paginate'				=> '',
						'tabs' 					=> true,
						'thumbnails'			=> true,
						'meta_author'			=> true,
						'meta_cats'				=> true,
						'meta_tags'				=> true,
						'heading_active'		=> false,
						'heading'				=> '',
						'heading_link'			=> '',
						'link_target'			=> '',
						'heading_color'			=> '',
						'heading_custom_color'	=> '',
						'first_big'				=> false,
						'first_big_pos'			=> 'top',
						'taxonomy'  			=> 'category',
						'link'					=> '',
						'categories'			=> array(),
						'extra_categories'		=> array(),
						'post_type'				=> array(),
						'offset'				=> 0,
						'image_size'			=> array( 'small' => 'thumbnail', 'big' => 'magazine' ),
						'image_big'				=> 'magazine',
						'date_filter'			=> '',
						'date_filter_start'		=> '',
						'date_filter_end'		=> '',
						'date_filter_format'	=> 'yy/mm/dd',		//	'yy/mm/dd' | 'dd-mm-yy'	| yyyymmdd
						'period_filter_unit_1'	=> '',
						'period_filter_unit_2'	=> '',
						'page_element_filter'	=> '',
						'custom_el_id'			=> '',
						'heading_tag'			=> '',
						'heading_class'			=> '',
						'lazy_loading'			=> 'enabled',
						'img_scrset'			=> ''
					);

			$default = array_merge( $default, $args );

			/**
			 * @since 4.8.8.1
			 * @param array $default
			 * @return array
			 */
			return apply_filters( 'avf_magazine_defaults', $default );
		}

		/**
		 * Returns the defaults array for this class
		 *
		 * @deprecated since version 4.8.8.1
		 * @since 4.8
		 * @since 4.8.8				added $defaults
		 * @param array $defaults
		 * @return array
		 */
		static public function get_defaults( array $defaults = array() )
		{
			_deprecated_function( 'avia_magazine::get_defaults()', '4.8.8.1', 'Use avia_magazine::default_args() instead.' );

			return avia_magazine::default_args( $defaults );
		}

		/**
		 * Create custom stylings
		 *
		 * Attention: Due to paging we cannot add any backgrouund images to selectors !!!!
		 * =========
		 *
		 * @since 4.8.8
		 * @param array $result
		 * @return array
		 */
		public function get_element_styles( array $result )
		{
			extract( $result );

			$classes = array(
						'av-magazine',
						$element_id,
						$this->config['top_bar'],
					);

			if( ! empty( $this->config['first_big_pos'] ) )
			{
				$classes[] = 'av-magazine-hero-' . $this->config['first_big_pos'];
			}

			if( ! empty( $this->config['tabs'] ) )
			{
				$classes[] = 'av-magazine-tabs-active';
			}

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes( 'container', $this->config['class'] );

			$element_styling->add_responsive_classes( 'container', 'hide_element', $atts );

			if( $this->config['heading_color'] != 'theme-color' )
			{
				if( $this->config['heading_color'] == 'custom' )
				{
					$element_styling->add_styles( 'heading', array( 'color' => $this->config['heading_custom_color'] ) );
				}
			}


			$selectors = array(
							'container'		=> ".av-magazine.{$element_id}",
							'heading'		=> "#top #wrap_all .av-magazine.{$element_id} .av-magazine-top-heading",
				);

			$element_styling->add_selectors( $selectors );

			/**
			 * Add loop for styling of subitems here - keep in mind, that category tabs create hidden containers !!!!
			 */

			//	save data for later HTML output
			$this->element_id = $element_id;
			$this->element_styles = $element_styling;

			$result['element_styling'] = $element_styling;

			return $result;
		}


		/**
		 *
		 * @since < 4.0
		 */
		protected function extract_terms()
		{
			if( isset( $this->config['link'] ) )
			{
				$this->config['link'] = explode( ',', $this->config['link'], 2 );
				$this->config['taxonomy'] = $this->config['link'][0];

				if( isset( $this->config['link'][1] ) )
				{
					$this->config['categories'] = $this->config['link'][1];
				}
				else
				{
					$this->config['categories'] = array();
				}
			}
		}

		/**
		 *
		 * @since < 4.0
		 * @return string
		 */
		protected function sort_buttons()
		{
			$term_args = array(
								'taxonomy'		=> $this->config['taxonomy'],
								'hide_empty'	=> true
							);
			/**
			 * To display private posts you need to set 'hide_empty' to false,
			 * otherwise a category with ONLY private posts will not be returned !!
			 *
			 * You also need to add post_status 'private' to the query params with filter avf_magazine_entries_query.
			 *
			 * @since 4.4.2
			 * @added_by Günter
			 * @param array $term_args
			 * @param string $context
			 * @return array
			 */
			$term_args = apply_filters( 'avf_av_magazine_term_args', $term_args, 'sort_button' );

			$sort_terms = AviaHelper::get_terms( $term_args );

			$current_page_terms = array();
			$term_count = array();
			$display_terms = is_array( $this->config['categories'] ) ? $this->config['categories'] : array_filter( explode( ',', $this->config['categories'] ) );

			$output = '<div class="av-magazine-sort" data-magazine-id="' . self::$magazine . '" >';

			$first_item_name = apply_filters( 'avf_magazine_sort_first_label', __( 'All', 'avia_framework' ), $this->config );

			$output .= '<div class="av-sort-by-term">';
			$output .= '<a href="#" data-filter="sort_all" class="all_sort_button active_sort"><span class="inner_sort_button"><span>' . $first_item_name . '</span></span></a>';

			foreach( $sort_terms as $term )
			{
				if ( ! in_array( $term->term_id, $display_terms ) )
				{
					continue;
				}

				if( ! isset( $term_count[ $term->term_id ] ) )
				{
					$term_count[ $term->term_id ] = 0;
				}

				$term->slug = str_replace( '%', '', $term->slug );

				$output .= 	"<span class='text-sep {$term->slug}_sort_sep'>/</span>";
				$output .= 	'<a href="#" data-filter="sort_' . $term->term_id . '" class="' . $term->slug . '_sort_button " ><span class="inner_sort_button">';
				$output .= 		'<span>' . esc_html( trim( $term->name ) ) . '</span>';
				$output .= 		'</span>';
				$output .= 	'</a>';

				$this->config['extra_categories'][] = $term->term_id;
			}

			$output .= '</div></div>';

			if( count( $this->config['extra_categories'] ) <= 1 )
			{
				return '';
			}

			return $output;
		}

		/**
		 * Fetch new entries.
		 * Entries for category tabs placed in hidden containers are queried in a loop from html()
		 *
		 * @since < 4.0
		 * @param array $params
		 * @param boolean $return
		 * @return WP_Query|void
		 */
		public function query_entries( $params = array(), $return = false )
		{
			global $avia_config;

			//	avoid double query of main content
			if( false === $return && $this->entries instanceof WP_Query )
			{
				return;
			}

			if( empty( $params ) )
			{
				$params = $this->config;
			}

			if( empty( $params['custom_query'] ) )
			{
				$query = array();

				if( ! empty( $params['categories'] ) )
				{
					//get the portfolio categories
					$terms 	= explode( ',', $params['categories'] );
				}

				$this->current_page = ( $params['paginate'] != '' ) ? avia_get_current_pagination_number( 'avia-element-paging' ) : 1;

				//if we find no terms for the taxonomy fetch all taxonomy terms
				if( empty( $terms[0] ) || is_null( $terms[0] ) || $terms[0] == 'null' )
				{
					$term_args = array(
								'taxonomy'		=> $params['taxonomy'],
								'hide_empty'	=> true
							);
					/**
					 * To display private posts you need to set 'hide_empty' to false,
					 * otherwise a category with ONLY private posts will not be returned !!
					 *
					 * You also need to add post_status 'private' to the query params with filter avf_magazine_entries_query.
					 *
					 * @since 4.4.2
					 * @added_by Günter
					 * @param array $term_args
					 * @param string $context
					 * @return array
					 */
					$term_args = apply_filters( 'avf_av_magazine_term_args', $term_args, 'query_entries' );

					$allTax = AviaHelper::get_terms( $term_args );

					$terms = array();
					foreach( $allTax as $tax )
					{
						$terms[] = $tax->term_id;
					}
				}

				if( $params['offset'] == 'no_duplicates' )
				{
					$params['offset'] = 0;
					if( empty( $params['ignore_duplicate_rule'] ) )
					{
						$no_duplicates = true;
					}
				}

				if( empty( $params['post_type'] ) )
				{
					$params['post_type'] = get_post_types();
				}

				if( is_string( $params['post_type'] ) )
				{
					$params['post_type'] = explode( ',', $params['post_type'] );
				}

				$date_query = AviaHelper::date_query( array(), $params );

				$query = array(
								'orderby'		=> 'date',
								'order'			=> 'DESC',
								'paged'			=> $this->current_page,
								'post_type'		=> $params['post_type'],
								'post__not_in'	=> ( ! empty( $no_duplicates ) ) ? $avia_config['posts_on_current_page'] : array(),
								'offset'		=> $params['offset'] != 0 ? $params['offset'] : false,
								'posts_per_page' => $params['items'],
								'date_query'	=> $date_query,
								'tax_query'		=> array( array(
																'taxonomy' 	=> $params['taxonomy'],
																'field' 	=> 'id',
																'terms' 	=> $terms,
																'operator' 	=> 'IN'
																)
															)
							);

			}
			else
			{
				$query = $params['custom_query'];
			}

			if( 'skip_current' == $params['page_element_filter'] )
			{
				$query['post__not_in'] = isset( $query['post__not_in'] ) ? $query['post__not_in'] : [];
				$query['post__not_in'][] = get_the_ID();
			}

			/**
			 *
			 * @since < 4.0
			 * @param array $query
			 * @param array $params
			 * @return array
			 */
			$query = apply_filters( 'avf_magazine_entries_query', $query, $params );

			$entries = new WP_Query( $query );

			if( ( $entries->post_count > 0 ) && empty( $params['ignore_duplicate_rule'] ) )
			{
				foreach( $entries->posts as $entry )
				{
					 $avia_config['posts_on_current_page'][] = $entry->ID;
				}
			}

			if( $return )
			{
				return $entries;
			}

			$this->entries = $entries;
		}

		/**
		 * Creates the HTML for the magazine.
		 * Provides a fallback in case $element_styling is not rendered
		 *
		 * @since < 4.0
		 * @return string
		 */
		public function html()
		{
			self::$magazine++;

			if( empty( $this->entries->posts ) )
			{
				return '';
			}

			//	fallback - code no longer supported since 4.8.8
			if( is_null( $this->element_styles ) )
			{
				_deprecated_function( 'avia_magazine::html()', '4.8.8', 'Calling this function without post css support does not work any longer.' );

				return '';
			}


			$id = ! empty( $this->config['custom_el_id'] ) ? $this->config['custom_el_id'] : ' id="avia-magazine-' . self::$magazine . '" ';
			$style_tag = $this->element_styles->get_style_tag( $this->element_id );
			$container_class = $this->element_styles->get_class_string( 'container' );

			$output  = '';
			$output .= $style_tag;
			$output .= "<div {$id} class='{$container_class} {$this->config['class']}' >";

			if( $this->config['top_bar'] )
			{
				$link = AviaHelper::get_url( $this->config['heading_link'] );
				$blank = AviaHelper::get_link_target( $this->config['link_target'] );
				$title_attr_markup = AviaHelper::get_link_title_attr_markup( $this->config['title_attr'] );

				$heading = $this->config['heading'];
				$b_class = '';

				if( $this->config['heading_color'] != 'theme-color' )
				{
					$b_class .= "avia-font-color-{$this->config['heading_color']} avia-inherit-font-color";
				}

				$output .= "<div class='av-magazine-top-bar {$b_class}'>";

				if( $this->config['heading_active'] && $heading )
				{
					$heading_tag = ( ! in_array( $this->config['heading_tag'], array( '', 'a' ) ) ) ? $this->config['heading_tag'] : '';
					$heading_class = $this->config['heading_class'];

					if( ! empty( $heading_tag ) )
					{
						$output .= "<{$heading_tag} class='{$heading_class}'>";
						$heading_class = '';
					}

					if( empty( $link ) )
					{
						$output .= "<span class='av-magazine-top-heading {$heading_class}'>{$heading}</span>";
					}
					else
					{
						$output .= "<a href='{$link}' class='av-magazine-top-heading {$heading_class}' {$blank} {$title_attr_markup}>{$heading}</a>";
					}

					if( ! empty( $heading_tag ) )
					{
						$output .= "</{$heading_tag}>";
						$heading_class = '';
					}
				}

				if( ! empty( $this->config['tabs'] ) )
				{
					$output .= $this->sort_buttons();
				}

				$output .= '</div>';
			}


			//magazine main loop
			$output .= $this->magazine_loop( $this->entries->posts );


			//magazine sub loops - add hidden containers for category tabs
			$output .= $this->magazine_sub_loop();

			//append pagination
			if( $this->config['paginate'] == 'pagination' && $avia_pagination = avia_pagination( $this->entries->max_num_pages, 'nav', 'avia-element-paging', $this->current_page ) )
			{
				$output .= "<div class='av-masonry-pagination av-masonry-pagination-{$this->config['paginate']}'>{$avia_pagination}</div>";
			}

			$output .= '</div>';
			return $output;
		}

		/**
		 * Read entries that are added to hidden containers for sort button
		 *
		 * @since < 4.0
		 * @return string
		 */
		protected function magazine_sub_loop()
		{
			$output = '';

			if( ! empty( $this->config['extra_categories'] ) && count( $this->config['extra_categories'] ) > 1 )
			{
				foreach( $this->config['extra_categories'] as $category )
				{
					$params = $this->config;
					$params['ignore_duplicate_rule'] = true;
					$params['categories'] = $category;
					$params['sort_var'] = $category;

					$entries = $this->query_entries( $params, true );
					$output .= $this->magazine_loop( $entries->posts, $params );
				}
			}

			return $output;
		}

		/**
		 *
		 * @since < 4.0
		 * @param array $entries		WP_Post objects
		 * @param array $params
		 * @return string
		 */
		protected function magazine_loop( array $entries, $params = array() )
		{
			$output = '';

			$loop = 0;
			$grid = $this->config['first_big_pos'] == 'left' ? 'flex_column av_one_half ' : '';
			$html = ! empty( $this->config['first_big_pos'] ) ? array( 'before' => "<div class='av-magazine-hero first {$grid}'>", 'after' => '</div>' ) : array( 'before' => '', 'after' => '' );
			$css = empty( $params['sort_var'] ) ? 'sort_all' : 'av-hidden-mag sort_' . $params['sort_var'];

			if( ! empty( $entries ) )
			{
				$output .= "<div class='av-magazine-group {$css}'>";

				foreach( $entries as $entry )
				{
					$loop ++;
					$entry->loop = $loop;

					$style = ( $loop == 1 && ! empty( $this->config['first_big'] ) ) ? 'big' : 'small';

					if( $loop == 2 && ! empty( $html['before'] ) )
					{
						$html = array( 'before' => "<div class='av-magazine-sideshow {$grid}'>" , 'after' => '' );
					}

					if( $loop == 3 )
					{
						$html = array( 'before' => '', 'after' => '' );
					}

					$output .= $html['before'];
					$output .= $this->render_entry( $entry, $style );
					$output .= $html['after'];
				}

				if( $loop != 1 && ! empty( $this->config['first_big_pos'] ) )
				{
					$output .= '</div>';
				}

				$output .= '</div>';
			}
			else
			{
				//	output empty container - otherwise frontend sort breaks
				$output .= "<div class='av-magazine-group {$css}'></div>";
			}

			return $output;
		}


		/**
		 *
		 * @since < 4.0
		 * @param WP_Post $entry
		 * @param string $style
		 * @return string
		 */
		protected function render_entry( WP_Post $entry, $style )
		{
			$output = '';

			$post_thumbnail_id = get_post_thumbnail_id( $entry->ID );

			if( $this->config['lazy_loading'] != 'enabled' )
			{
				Av_Responsive_Images()->add_attachment_id_to_not_lazy_loading( $post_thumbnail_id );
			}

			$image = get_the_post_thumbnail( $entry->ID, $this->config['image_size'][ $style ] );

			$link = get_permalink( $entry->ID );
			$titleAttr = the_title_attribute( array( 'echo' => false, 'post' => $entry->ID ) );

			/**
			 * Allow post-format link same behaviour as on single post
			 *
			 * @since 4.7.3
			 */
			if( get_post_meta( $entry->ID , '_portfolio_custom_link', true ) != '' )
			{
				$link = get_post_meta( $entry->ID ,'_portfolio_custom_link_url', true );
			}
			else
			{
				$post_format = get_post_format( $entry );
				if( ( $post_format == 'link' ) && function_exists( 'avia_link_content_filter' ) )
				{
					$post['title'] = $entry->post_title;
					$post['content'] = $entry->post_content;
					$post = avia_link_content_filter( $post );
					if( ! empty( $post['url'] ) )
					{
						$link = $post['url'];
					}
				}
			}

			$titleAttr = "title='" . __( 'Link to:', 'avia_framework' ) . " {$titleAttr}'";
			$title = "<a href='{$link}' {$titleAttr}>". apply_filters( 'avf_magazine_title', get_the_title( $entry->ID ), $entry ) . '</a>';
			$excerpt = '';
			$time = get_the_time( get_option('date_format'), $entry->ID );
			$separator = '';

			$default_heading = 'h3';
			$args = array(
						'heading'		=> $default_heading,
						'extra_class'	=> ''
					);

			$extra_args = array( $this, $entry, $style );

			/**
			 * @since 4.5.7.1
			 * @return array
			 */
			$args = apply_filters( 'avf_customize_heading_settings', $args, __CLASS__, $extra_args );

			$titleTag = ! empty( $args['heading'] ) ? $args['heading'] : $default_heading;
			$titleCss = ! empty( $args['extra_class'] ) ? $args['extra_class'] : '';

			$author = '';
			if( ! empty( $this->config['meta_author'] ) )
			{
				$author_link = get_author_posts_url( $entry->post_author );
				$author_name = apply_filters( 'avf_author_name', get_the_author_meta( 'display_name', $entry->post_author ), $entry->post_author );
				$author_link = '<a href="' . $author_link . '" title="' . __( 'by', 'avia_framework' ) . ' ' . $author_name . '" rel="author">' . $author_name . '</a>';

				$author_output  =	'<span class="av-magazine-author minor-meta">' . __( 'by', 'avia_framework' ) . ' ';
				$author_output .=		'<span class="av-magazine-author-link" ' . avia_markup_helper( array( 'context' => 'author_name', 'echo' => false ) ) . '>';
				$author_output .=			'<span class="av-magazine-author meta-color author">';
				$author_output .=				'<span class="fn">';
				$author_output .=					$author_link;
				$author_output .=				'</span>';
				$author_output .=			'</span>';
				$author_output .=		'</span>';
				$author_output .=	'</span>';

				$author .=	'<span class="av-magazine-author-wrap">';
				$author .=		'<span class="av-magazine-text-sep text-sep-date">/</span>';
				$author .=		$author_output;
				$author .=	'</span>';
			}

			$cats = '';
			if( ! empty( $this->config['meta_cats'] ) )
			{
				$taxonomies = get_object_taxonomies( $entry->post_type );

				$excluded_taxonomies = array_merge( get_taxonomies( array( 'public' => false ) ), array( 'post_tag', 'post_format' ) );

				/**
				 * @since 4.5.7.1
				 * @param array
				 * @param string $entry->post_type
				 * @param int $entry->ID
				 * @return array
				 */
				$excluded_taxonomies = apply_filters( 'avf_exclude_taxonomies_magazine', $excluded_taxonomies, $entry->post_type, $entry->ID );

				if( ! empty( $taxonomies ) )
				{
					foreach( $taxonomies as $taxonomy )
					{
						if( ! in_array( $taxonomy, $excluded_taxonomies ) )
						{
							$cats .= get_the_term_list( $entry->ID, $taxonomy, '', ', ','') . ' ';
						}
					}
				}

				if( ! empty( $cats ) )
				{
					$cats_html =	'<span class="av-magazine-cats-wrap">';
					$cats_html .=		'<span class="av-magazine-text-sep text-sep-cats">In</span>';
					$cats_html .=		'<span class="av-magazine-cats minor-meta">';
					$cats_html .=			$cats;
					$cats_html .=		'</span>';
					$cats_html .=	'</span>';

					$cats = $cats_html;
				}
			}

			$tags = '';
			if( ! empty( $this->config['meta_tags'] ) )
			{
				$tag_list = get_the_term_list( $entry->ID, 'post_tag', '<span class="av-magazine-text-sep text-sep-tags">' . __( 'Tags:', 'avia_framework' ) . '</span><span class="av-magazine-tags minor-meta">', ', ', '' );

				if( ! empty( $tag_list ) )
				{
					$tags_html =	'<span class="av-magazine-tags-wrap">';
					$tags_html .=		$tag_list;
					$tags_html .=	'</span></span>';

					$tags = $tags_html;
				}
			}


			$markupEntry = avia_markup_helper( array( 'context' => 'entry', 'echo' => false, 'id' => $entry->ID, 'custom_markup' => $this->config['custom_markup'] ) );
			$markupTitle = avia_markup_helper( array( 'context' => 'entry_title', 'echo' => false, 'id' => $entry->ID, 'custom_markup' => $this->config['custom_markup'] ) );
			$markupContent = avia_markup_helper( array( 'context' => 'entry_content', 'echo' => false, 'id' => $entry->ID, 'custom_markup' => $this->config['custom_markup'] ) );
			$markupTime = avia_markup_helper( array( 'context' => 'entry_time', 'echo' => false, 'id' => $entry->ID, 'custom_markup' => $this->config['custom_markup'] ) );

			$post_format = get_post_format( $entry->ID ) ? get_post_format( $entry->ID ) : 'standard';
			$post_type = get_post_type( $entry->ID );
			$extraClass = '';

			if( $style == 'small' )
			{
				if( empty( $this->config['thumbnails'] ) )
				{
					 $image = '';
					 $extraClass = 'av-magazine-no-thumb';
				}
			}
			else
			{
				$excerpt = ! empty( $entry->post_excerpt ) ? $entry->post_excerpt : avia_backend_truncate( $entry->post_content, apply_filters( 'avf_magazine_excerpt_length', 60 ), apply_filters( 'avf_magazine_excerpt_delimiter', ' ' ), '…', true, '' );

				/**
				 * @since 4.8.8
				 * @param boolean $skip
				 * @param string $excerpt
				 * @param WP_Post $entry
				 * @return boolean
				 */
				if( false === apply_filters( 'avf_magazine_skip_excerpt_content_filter', false, $excerpt, $entry ) )
				{
					$excerpt = str_replace( ']]>', ']]&gt;', apply_filters( 'the_content', $excerpt ) );
				}
			}


			$output .= "<article class='av-magazine-entry av-magazine-entry-id-{$entry->ID} av-magazine-format-{$post_format} av-magazine-type-{$post_type} av-magazine-entry-{$entry->loop} av-magazine-entry-{$style} {$extraClass}' {$markupEntry}>";

			if( $this->config['thumbnails'] || ( $style == 'big' && $image ) )
			{
				$output .= '<div class="av-magazine-thumbnail">';

				if( $image )
				{
					$output .= "<a href='{$link}' {$titleAttr} class='av-magazine-thumbnail-link'>{$image}</a>";
				}
				else
				{
					$icontype = $post_type == 'post' ? $post_format : $post_type;
					$display_char = avia_font_manager::get_frontend_shortcut_icon( "svg__{$icontype}", [ 'title' => '', 'desc' => '', 'aria-hidden' => 'true' ] );
					$char_class = avia_font_manager::get_frontend_icon_classes( $display_char['font'], 'string' );

					$output .= "<a href='{$link}' {$titleAttr} class='av-magazine-entry-icon {$char_class}' {$display_char['attr']}>";
					$output .=		$display_char['svg'];
					$output .= '</a>';
				}

				$output .="</div>";
			}

			$header_content = array(
								'time'		=> "<time class='av-magazine-time updated' {$markupTime}>{$time}</time>",
								'author'	=> $author,
								'cats'		=> $cats,
								'tags'		=> $tags,
								'title'		=> "<{$titleTag} class='av-magazine-title entry-title {$titleCss}' {$markupTitle}>{$title}</{$titleTag}>"
							);

			/**
			 * @since 4.8.8
			 * @param array $header_content
			 * @param WP_Post $entry
			 * @return array
			 */
			$header_content = apply_filters( 'avf_magazine_header_content', $header_content, $entry );


			$aria_label = 'aria-label="' . __( 'Post:', 'avia_framework' ) . ' ' . esc_attr( $entry->post_title ) . '"';

			/**
			 * @since 6.0.3
			 * @param string $aria_label
			 * @param string $context
			 * @param WP_Post $entry
			 * @return string
			 */
			$aria_label = apply_filters( 'avf_aria_label_for_header', $aria_label, __CLASS__, $entry );


			$output .= 		'<div class="av-magazine-content-wrap">';
			$output .=			'<header class="entry-content-header" ' . $aria_label . '>';
			$output .=				implode( '', $header_content );
			$output .=			'</header>';
			if( $excerpt )
			{
				$output .=		"<div class='av-magazine-content entry-content' {$markupContent}>{$excerpt}</div>";
			}
			$output .= 		'</div>';
			$output .= 		'<footer class="entry-footer"></footer>';
			$output .= '</article>';

			return $output;
		}
	}
}

