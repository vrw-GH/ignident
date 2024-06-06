<?php
/**
 * Product Meta
 *
 * Display the product meta for the current product
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'woocommerce', false ) )
{
	add_shortcode( 'avia_sc_product_meta', 'avia_please_install_woo' );
	return;
}

if( ! class_exists( 'avia_sc_product_meta', false ) )
{
	class avia_sc_product_meta extends aviaShortcodeTemplate
	{
		/**
		 * Create the config array for the shortcode button
		 */
		protected function shortcode_insert_button()
		{
			$this->config['self_closing']	= 'yes';

			$this->config['name']			= __( 'Product Meta Info', 'avia_framework' );
			$this->config['tab']			= __( 'Plugin Additions', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-contentslider.png';
			$this->config['order']			= 9;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode']		= 'av_product_meta';
			$this->config['tooltip']		= __( 'Display the product meta for the current product', 'avia_framework' );
			$this->config['drag-level']		= 3;
			$this->config['tinyMCE']		= array( 'disable' => 'true' );
			$this->config['posttype']		= array( 'product', __( 'This element can only be used on single product pages', 'avia_framework' ) );
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

			$params['innerHtml'] .= "<div class='avia-flex-element'>";
			$params['innerHtml'] .= 		__( 'Display the product meta info such as categories, tags and sku.', 'avia_framework' );
			$params['innerHtml'] .= '</div>';

			return $params;
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



			$classes = array(
						'product_meta',
						$element_id
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $meta, 'el_class' );

			$selectors = array(
						'container'		=> ".product_meta.{$element_id}"
					);


			$element_styling->add_selectors( $selectors );


			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['meta'] = $meta;

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
			global $product;

			//	fix for seo plugins which execute the do_shortcode() function before everything is loaded
			if( ! function_exists( 'WC' ) || ! WC() instanceof WooCommerce || ! is_object( WC()->query ) || ! $product instanceof WC_Product )
			{
				return '';
			}

			$result = $this->get_element_styles( compact( array( 'atts', 'content', 'shortcodename', 'meta' ) ) );

			extract( $result );

			$style_tag = $element_styling->get_style_tag( $element_id );
			$container_class = $element_styling->get_class_string( 'container' );

			$output  = '';
			$output .= $style_tag;
			$output .= "<div class='{$container_class}'>";

			ob_start();

				if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) )
				{
					$sku = $product->get_sku() ? $product->get_sku() : __( 'N/A', 'avia_framework' );
					$output .= '<span class="sku_wrapper">' . __( 'SKU:', 'avia_framework' ) . '<span class="sku"> ' . $sku . '</span></span>';
				}

			$output .=		ob_get_clean();
			$output .=		wc_get_product_category_list( $product->get_id(), ', ', '<span class="posted_in">' . _n( 'Category:', 'Categories:', count( $product->get_category_ids() ), 'woocommerce' ) . ' ', '</span>' );
			$output .=		wc_get_product_tag_list( $product->get_id(), ', ', '<span class="tagged_as">' . _n( 'Tag:', 'Tags:', count( $product->get_tag_ids() ), 'woocommerce' ) . ' ', '</span>' );
			$output .= '</div>';

			return $output;
		}
	}
}

