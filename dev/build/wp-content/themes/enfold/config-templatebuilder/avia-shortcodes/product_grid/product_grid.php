<?php
/**
 * Product Grid
 *
 * Display a Grid of Product Entries
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'woocommerce', false ) )
{
	add_shortcode( 'av_productgrid', 'avia_please_install_woo' );
	return;
}

if( ! class_exists( 'avia_sc_productgrid', false ) )
{
	class avia_sc_productgrid extends aviaShortcodeTemplate
	{
		/**
		 * Save avia_product_slider objects for reuse. As we need to access the same object when creating the post css file in header,
		 * create the styles and HTML creation. Makes sure to get the same id.
		 *
		 *			$element_id	=> avia_product_slider
		 *
		 * @since 4.8.9
		 * @var array
		 */
		protected $obj_product_slider;

		/**
		 * @since 4.8.9
		 * @param AviaBuilder $builder
		 */
		public function __construct( AviaBuilder $builder )
		{
			parent::__construct( $builder );

			$this->obj_product_slider = array();
		}

		/**
		 * @since 4.8.9
		 */
		public function __destruct()
		{
			unset( $this->obj_product_slider );

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

			$this->config['name']			= __( 'Product Grid', 'avia_framework' );
			$this->config['tab']			= __( 'Plugin Additions', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-portfolio.png';
			$this->config['order']			= 30;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode']		= 'av_productgrid';
			$this->config['tooltip']		= __( 'Display a Grid of Product Entries', 'avia_framework' );
			$this->config['drag-level']		= 3;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
			$this->config['alb_desc_id']	= 'alb_description';
		}

		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_css = avia_minify_extension( 'css' );

			//load css
			wp_enqueue_style( 'avia-module-catalogue', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/catalogue/catalogue{$min_css}.css", array( 'avia-layout' ), $ver );
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
													$this->popup_key( 'styling_columns' ),
													$this->popup_key( 'styling_image_size' ),
													$this->popup_key( 'styling_pagination' )
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
		 * @since 4.6.4
		 */
		protected function register_dynamic_templates()
		{
			/**
			 * Content Tab
			 * ===========
			 */

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'wc_select_product_taxonomies_toggle',
								'lockable'		=> true
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_entries' ), $template );


			$c = array(
						array(
							'type'			=> 'template',
							'template_id' 	=> 'wc_options_products',
							'sort_dropdown'	=> true,
							'lockable'		=> true
						),

						array(
							'name'		=> __( 'Offset Number', 'avia_framework' ),
							'desc'		=> __( 'The offset determines where the query begins pulling products. Useful if you want to remove a certain number of products because you already query them with another product grid. Attention: Use this option only if the product sorting of the product grids match and do not allow the user to pick the sort order!', 'avia_framework' ),
							'id'		=> 'offset',
							'type'		=> 'select',
							'std'		=> '0',
							'lockable'	=> true,
							'subtype'	=> AviaHtmlHelper::number_array( 1, 100, 1, array( __( 'Deactivate offset', 'avia_framework' ) => '0', __( 'Do not allow duplicate posts on the entire page (set offset automatically)', 'avia_framework' ) => 'no_duplicates' ) )
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
							'name' 	=> __( 'Columns', 'avia_framework' ),
							'desc' 	=> __( 'How many columns should be displayed?', 'avia_framework' ),
							'id' 	=> 'columns',
							'type' 	=> 'select',
							'std' 	=> '3',
							'lockable'	=> true,
							'subtype'	=> array(
												__( '2 Columns', 'avia_framework' )	=> '2',
												__( '3 Columns', 'avia_framework' )	=> '3',
												__( '4 Columns', 'avia_framework' )	=> '4',
												__( '5 Columns', 'avia_framework' )	=> '5'
											)
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Columns', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_columns' ), $template );


			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'wc_image_size_toggle',
								'lockable'		=> true
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_image_size' ), $template );


			$c = array(
						array(
							'name' 	=> __( 'Entry Number', 'avia_framework' ),
							'desc' 	=> __( 'How many items should be displayed?', 'avia_framework' ),
							'id' 	=> 'items',
							'type' 	=> 'select',
							'std' 	=> '9',
							'lockable'	=> true,
							'subtype'	=> AviaHtmlHelper::number_array( 1, 100, 1, array( 'All' => '-1' ) )
						),

						array(
							'name' 	=> __( 'Pagination', 'avia_framework' ),
							'desc' 	=> __( 'Should a pagination be displayed?', 'avia_framework' ),
							'id' 	=> 'paginate',
							'type' 	=> 'select',
							'std' 	=> 'yes',
							'lockable'	=> true,
							'required'	=> array( 'items', 'not', '-1' ),
							'subtype'	=> array(
												__( 'yes', 'avia_framework' )	=> 'yes',
												__( 'no', 'avia_framework' )	=> 'no'
											)
						),

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
			return $params;
		}

		/**
		 *
		 * @since 4.8.9
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles( array $args )
		{
			$result = parent::get_element_styles( $args );

			extract( $result );

			$default = $this->sync_sc_defaults_array( avia_product_slider::default_args(), 'no_modal_item', 'no_content' );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			//	set static slider parameters
			$atts['type'] = 'grid';
			$atts['autoplay'] = 'no';

			$add = array(
						'class'			=> '',
						'el_id'			=> '',
						'custom_class'	=> ''
				);

			$defaults = array_merge( $default, $add );

			$atts = shortcode_atts( $defaults, $atts, $this->config['shortcode'] );

			if( ! isset( $this->obj_product_slider[ $element_id ] ) )
			{
				$this->obj_product_slider[ $element_id ] = new avia_product_slider( $atts, $this );
			}

			$product_slider = $this->obj_product_slider[ $element_id ];

			$update = array(
							'class'				=> ! empty( $meta['el_class'] ) ? $meta['el_class'] : '',
							'el_id'				=> ! empty( $meta['custom_el_id'] ) ? $meta['custom_el_id'] : '',
							'custom_class'		=> ! empty( $meta['custom_class'] ) ? $meta['custom_class'] : ''
						);

			$atts = $product_slider->update_config( $update );

			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['element_styling'] = $element_styling;
			$result['meta'] = $meta;

			$result = $product_slider->get_element_styles( $result );

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
			global $avia_config;

			//fix for seo plugins which execute the do_shortcode() function before the WooCommerce plugin is loaded
			if( ! function_exists( 'WC' ) || ! WC() instanceof WooCommerce || ! is_object( WC()->query ) )
			{
				return '';
			}

			$result = $this->get_element_styles( compact( array( 'atts', 'content', 'shortcodename', 'meta' ) ) );

			extract( $result );

			$product_slider = $this->obj_product_slider[ $element_id ];

			//	html() uses main WP query with WC templates
			$product_slider->query_entries();

			//	force to ignore WC default setting - see hooked function avia_wc_product_is_visible
			$avia_config['woocommerce']['catalog_product_visibility'] = 'show_all';

			$html = $product_slider->html();

			//	reset again
			$avia_config['woocommerce']['catalog_product_visibility'] = 'use_default';

			return $html;
		}
	}
}



