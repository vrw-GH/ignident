<?php
/**
 * Page / Post / Custom Layout / ....
 *
 * Display the content of another entry in a fullwidth area. Content of the selected entry is integrated in the content flow.
 *
 * LIMITATION: It is not allowed to use this element in the selected post - it will be removed there. This is to avoid circular references and endless loops.
 * ===========
 *
 * Element based on "Page Content" which was in Beta till 4.2.6 and by default disabled.
 *
 * Todo: test with layerslider elements. currently throws error bc layerslider is only included if layerslider element is detected which is not the case with the post/page element
 *
 *
 * This class itself does not support post css files, but handles them for selected posts
 * ======================================================================================
 *
 * @since 6.0
 * @modified_by Günter
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_sc_custom_layout', false ) )
{
	class avia_sc_custom_layout extends aviaShortcodeTemplate
	{
		/**
		 * Stores filterable post types to select content
		 *
		 * @since 6.0
		 * @var array
		 */
		protected $post_types;

		/**
		 * Stores filterable post status to select content
		 *
		 * @since 6.0
		 * @var array
		 */
		protected $post_status;

		/**
		 *
		 * @since 6.0
		 * @param \AviaBuilder $builder
		 */
		public function __construct( \AviaBuilder $builder )
		{
			$this->post_types = Avia_Builder()->get_supported_post_types();

			/**
			 * Filter custom post types that can be used to display content
			 *
			 * @used_by							aviaCustomLayout		10
			 * @since 6.0
			 * @param array $post_types
			 * @return array
			 */
			$this->post_types = (array) apply_filters( 'avf_custom_layout__post_types', $this->post_types );

			/**
			 * @since 6.0
			 * @param array $post_status
			 * @return array
			 */
			$this->post_status = (array) apply_filters( 'avf_custom_layout__post_status', array( 'publish', 'private' ) );

			parent::__construct( $builder );
		}

		/**
		 * @since 6.0
		 */
		public function __destruct()
		{
			parent::__destruct();

			unset( $this->post_types );
			unset( $this->post_status );
		}

		/**
		 * Create the config array for the shortcode button
		 *
		 * @since 6.0
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']				= '1.0';
			$this->config['is_fullwidth']			= 'yes';
			$this->config['self_closing']			= 'yes';
			$this->config['forced_load_objects']	= array( 'layerslider' );			//	we must load layerslider because content might contain one

			$this->config['name']					= __( 'Custom Layout', 'avia_framework' );
			$this->config['tab']					= __( 'Layout Elements', 'avia_framework' );
			$this->config['icon']					= AviaBuilder::$path['imagesURL'] . 'sc-custom-layout.png';
			$this->config['order']					= 2;
			$this->config['target']					= 'avia-target-insert';
			$this->config['shortcode']				= 'av_custom_layout';
//			$this->config['modal_data']				= array( 'modal_class' => 'flexscreen' );
			$this->config['tooltip']				= __( 'Display the content of a custom layout. Also page, posts, portfolio,... can be selected.', 'avia_framework' );
			$this->config['disabled']				= array(
														'condition'	=> ( false === strpos( avia_get_option( 'alb_dynamic_content' ), 'alb_custom_layout' ) ),
														'text'		=> __( 'This element is disabled in your theme options. You can enable it in Enfold &raquo; Layout Builder', 'avia_framework' )
													);
			$this->config['drag-level']				= 1;
			$this->config['drop-level']				= 1;
			$this->config['preview']				= false;
			$this->config['custom_css_show']		= 'never';
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
			$limit  = '<br />';
			$limit .= __( 'Your selected entry MUST not contain a &quot;Custom Layout&quot; element. To avoid endless loops and broken layout it will be ignored in the selected entry in frontend.', 'avia_framework' );
			$limit .= '<br /><br />';
			$limit .= __( 'Your selected entry should be built using ALB (Advanced Layout Builder). In active beta also non ALB entries can be used.', 'avia_framework' );


			$desc  = __( 'Select an entry whose content will be used to display in frontend. In active beta is to use classic editor content and block editor content.', 'avia_framework' );
			$desc .= '<br /><br />';
//			$desc .= __( 'To modify the select list of post types use filter &quot;avf_custom_layout__post_types&quot;. Make sure to register your custom post types for ALB using filter &quot;avf_alb_supported_post_types&quot; otherwise they will not be recognized.', 'avia_framework' );
//			$desc .= '<br /><br />';
			$desc .= __( 'In case a &quot;Custom Layout&quot; entry is not displayed in dropdown list check that it is published and reload this page to refill selectboxes.', 'avia_framework' );
			$desc .= '<br /><br />';
			$desc .= __( 'You are free to choose an entry of any post type.', 'avia_framework' );

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
							'name' 	=> __( 'LIMITATION:', 'avia_framework' ),
							'desc' 	=> $limit,
							'type' 	=> 'heading',
							'description_class' => 'av-builder-note av-notice'
						),

					array(
						'name'			=> __( 'Select Layout Entry', 'avia_framework' ),
						'desc'			=> $desc,
						'id'			=> 'link',
						'type'			=> 'linkpicker',
						'std'			=> 'page',
						'fetchTMPL'		=> true,
						'subtype'		=> array( __( 'Single Entry', 'avia_framework' ) => 'single' ),
						'posttype'		=> $this->post_types,
						'hierarchical'	=> 'yes',						//	'yes' ( =default) | 'no'
						'post_status'	=> $this->post_status,			//	array | string  (default = publish)
						'show_alb_info'	=> true
					),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

//				array(
//						'type' 	=> 'tab',
//						'name'  => __( 'Advanced', 'avia_framework' ),
//						'nodescription' => true
//					),
//
//				array(
//								'type'			=> 'template',
//								'template_id'	=> 'lazy_loading_toggle',
//								'no_toggle'		=> true
//							),
//
//				array(
//						'type' 	=> 'tab_close',
//						'nodescription' => true
//					),

				array(
						'type' 	=> 'tab_container_close',
						'nodescription' => true
					)


				);
		}

		/**
		 * @since 6.0
		 */
		protected function extra_assets()
		{
			add_filter( 'avia_builder_precompile', array( $this, 'handler_avia_builder_precompile' ), 1 );
		}

		/**
		 * Scan content for av_custom_layout and replace it with the content of the selected entry.
		 * Current limitation is that nesting of this element is not allowed. Will be removed in shortcode handler.
		 *
		 * @since 6.0
		 * @param string $content
		 * @return string
		 */
		public function handler_avia_builder_precompile( $content )
		{
			global $shortcode_tags;


			/**
			 * In case we have no av_custom_layout shortcode we can return
			 */
			if( strpos( $content, "[{$this->config['shortcode']}" ) === false )
			{
				return $content;
			}

			/**
			 * save the 'real' shortcode array and limit execution to the shortcode of this class only
			 */
			$old_sc = $shortcode_tags;
			$shortcode_tags = array( $this->config['shortcode'] => array( $this, 'shortcode_handler' ) );

			/**
			 * Add in while loop to support nested elements.
			 *
			 * This is only in theory as we currently do not allow this !!
			 * We ignore same named shortcode in shortcode handler
			 */
			while( false !== strpos( $content, "[{$this->config['shortcode']}" ) )
			{
				$content = do_shortcode( $content );
			}

			/**
			 * Restore the original shortcode pattern
			 */
			$shortcode_tags = $old_sc;

			/**
			 * Update the shortcode tree to reflect the current page structure.
			 * Prior make sure that shortcodes are balanced.
			 */
			Avia_Builder()->get_shortcode_parser()->set_builder_save_location( 'none' );
			$content = ShortcodeHelper::clean_up_shortcode( $content, 'balance_only' );
			ShortcodeHelper::$tree = ShortcodeHelper::build_shortcode_tree( $content );

			return $content;
		}

		/**
		 * Editor Element - this function defines the visual appearance of an element on the AviaBuilder Canvas
		 * Most common usage is to define some markup in the $params['innerHtml'] which is then inserted into the drag and drop container
		 * Less often used: $params['data'] to add data attributes, $params['class'] to modify the className
		 *
		 *
		 * @since 6.0
		 * @param array $params			holds the default values for $content and $args.
		 * @return array				usually holds an innerHtml key that holds item specific markup.
		 */
		public function editor_element( $params )
		{
			$link = isset( $params['args']['link'] ) ? $params['args']['link'] : '';
			$entry = AviaHelper::get_entry( $link, array( 'post_status' => $this->post_status ) );

			$title = '';
			if( $entry instanceof WP_Post )
			{
				$title = esc_html( avia_wp_get_the_title( $entry ) ) . " ({$entry->post_type}, {$entry->ID} )";
			}

			$update_template =	'<span class="av-postcontent-headline">{{link}}</span>';
			$update	= $this->update_template( 'link', $update_template );

			$template = str_replace( '{{link}}', $title, $update_template );


			$params = parent::editor_element( $params );

			if( ! $entry instanceof WP_Post )
			{
				$params['innerHtml'].= "<div class='avia-element-description'>" . __( 'Display a predefined Custom Layout - or the content of a different entry', 'avia_framework' ) . '</div>';
			}

			$params['innerHtml'].=	'<div class="av-postcontent" data-update_object="all-elements" ' . $update . '>';
			$params['innerHtml'].=		$template;
			$params['innerHtml'].=	'</div>';

			return $params;
		}

		/**
		 * Returns the post id selected in this element
		 *
		 * @since 6.0
		 * @param array $shortcode
		 * @param boolean $modal_item
		 * @return array
		 */
		public function includes_dynamic_posts( array $shortcode, $modal_item )
		{
			$link = isset( $shortcode['attr']['link'] ) ? $shortcode['attr']['link'] : '';

			$entry = explode( ',', $link );

			if( empty( $entry[1] ) || 'manually' == $entry[0] || ! post_type_exists( $entry[0] ) )
			{
				return array();
			}

			return array( $entry[1] );
		}

		/**
		 * Frontend Shortcode Handler
		 *
		 * This handler is called only within a precompile handler and returns the unmodified content (including shortcodes)
		 * of the requested entry
		 *
		 * @since 6.0
		 * @param array $atts array of attributes
		 * @param string $content text within enclosing form of shortcode element
		 * @param string $shortcodename the shortcode found, when == callback name
		 * @param array $meta
		 * @return string $output returns the modified html string
		 */
		public function shortcode_handler( $atts, $content = '', $shortcodename = '', $meta = array() )
		{
			global $shortcode_tags;

			$atts = shortcode_atts( array(
										'link'			=> '',
										'lazy_loading'	=> 'disabled'
									), $atts, $this->config['shortcode'] );

			extract( $atts );

			$post_id = function_exists( 'avia_get_the_id' ) ? avia_get_the_id() : get_the_ID();
			$entry = AviaHelper::get_entry( $link, array( 'post_status' => $this->post_status ) );

			$cm = isset( $meta['custom_markup'] ) ? $meta['custom_markup'] : '';


			$output = '';

			if( $entry instanceof WP_Post )
			{
				if( $entry->ID == $post_id )
				{
					$output .= '<article style="padding:1em 0; text-align:center; width:100%; clear:both;" class="entry-content main_color" ' . avia_markup_helper( array( 'context' => 'entry','echo' => false, 'id' => $entry->ID, 'custom_markup' => $cm ) ) . '>';
					$output .=		__( 'You added a Custom Layout Element to this entry that tries to display itself. This would result in an infinite loop. Please select a different entry or remove the element.', 'avia_framework' );
					$output .= '</article>';
				}
				else
				{
					/**
					 * Remove this shortcode - nesting of same named shortcode is not supported by WP. We must take care of this in a loop outside
					 */
					$old_tags = $shortcode_tags;
					$shortcode_tags = array();

					$builder_stat = Avia_Builder()->get_alb_builder_status( $entry->ID );

					if( 'active' == $builder_stat )
					{
						if( ! is_preview() )
						{
							$output .= Avia_Builder()->get_posts_alb_content( $entry->ID );
						}
						else
						{
							$output .= $entry->post_content;
						}
					}
					else
					{
						$output .=	'<div style="padding:1em 0; width:100%;clear:both;" class="entry-content" ' . avia_markup_helper( array( 'context' => 'entry', 'echo' => false, 'id' => $entry->ID, 'custom_markup' => $cm ) ) . '>';
						$output .=		Avia_Dynamic_Content()->replace_pseudo_shortcode( $entry->post_content );
						$output .=	'</div>';
					}

					/**
					 * nesting is not allowed to avoid circular reference and endless loops in custom layout pages
					 *
					 * @since 6.0
					 */
					$output = preg_replace( "!\[{$this->config['shortcode']}.*]!mi", '', $output );

					$shortcode_tags = $old_tags;
				}
			}
			else
			{
				$info = '';

				if( Avia_Custom_Layout()->custom_layout_enabled() )
				{
					$info =__( 'Custom Layout Element - You did not select an entry to display.', 'avia_framework' );
				}

				$output .= '<article style="padding:1em 0; text-align:center; width:100%; clear:both;" class="entry-content main_color" ' . avia_markup_helper( array( 'context' => 'entry','echo' => false, 'id' => 0, 'custom_markup' => $cm ) ) . '>';
				$output .=		$info;
				$output .= '</article>';
			}

			return $output;
		}

	}
}
