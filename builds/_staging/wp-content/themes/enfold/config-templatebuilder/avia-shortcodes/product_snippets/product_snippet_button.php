<?php
/**
 * Product Purchase Button
 *
 * Display the "Add to cart" button for the current product
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'woocommerce', false ) )
{
	add_shortcode( 'av_product_button', 'avia_please_install_woo' );
	return;
}

if( ! class_exists( 'avia_sc_produc_button', false ) )
{
	class avia_sc_produc_button extends aviaShortcodeTemplate
	{
		/**
		 * Create the config array for the shortcode button
		 */
		protected function shortcode_insert_button()
		{
			$this->config['self_closing']	= 'yes';

			$this->config['name']			= __( 'Product Purchase Button', 'avia_framework' );
			$this->config['tab']			= __( 'Plugin Additions', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-button.png';
			$this->config['order']			= 20;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode']		= 'av_product_button';
			$this->config['tooltip']		= __( 'Display the "Add to cart" button for the current product', 'avia_framework' );
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
			$params['innerHtml'] .= 		__( 'Display the &quot;Add to cart&quot; button including prices and variations but no product description.', 'avia_framework' );
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
						'av-woo-purchase-button',
						$element_id
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $meta, 'el_class' );

			$selectors = array(
						'container'		=> ".av-woo-purchase-button.{$element_id}"
					);

			/**
			 * Fix for plugins (not a clean solution but easier to maintain). Could also be placed in shortcode.css.
			 */
			if( class_exists( 'Woocommerce_German_Market', false ) )
			{
				/**
				 * German Market also outputs the price
				 */
				$selectors['price'] = "#top .av-woo-purchase-button.{$element_id} > div > p.price";

				$element_styling->add_styles( 'price', array( 'display' => 'none' ) );
			}
			else if( class_exists( 'WooCommerce_Germanized', false ) )
			{
				/**
				 * Hides variation price with js
				 */
				$selectors['price'] = "#top form.variations_form.cart .woocommerce-variation-price > .price";

				$element_styling->add_styles( 'price', array( 'display' => 'block !important;' ) );
			}


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

			// fix for seo plugins which execute the do_shortcode() function before everything is loaded
			if( ! function_exists( 'WC' ) || ! WC() instanceof WooCommerce || ! is_object( WC()->query ) || ! $product instanceof WC_Product )
			{
				return '';
			}

			/**
			 * Fix for seo plugins which execute shortcode() function when saving product
			 *
			 * @since 5.6.4
			 */
			if( is_admin() && isset( $_REQUEST['action'] ) && 'editpost' == $_REQUEST['action'] )
			{
				return '';
			}

			$result = $this->get_element_styles( compact( array( 'atts', 'content', 'shortcodename', 'meta' ) ) );

			extract( $result );

			/**
			 * @since WC 3.0
			 */
			$wc_structured_data = isset( WC()->structured_data ) ? WC()->structured_data : null;

			/**
			 *	Remove default WC actions
			 */
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );

			// produces an endless loop because $wc_structured_data uses 'content' filter and do_shortcode !!
			if( ! is_null( $wc_structured_data ) )
			{
				remove_action( 'woocommerce_single_product_summary', array( $wc_structured_data, 'generate_product_data' ), 60 );
			}

			// $product = wc_get_product();

			$style_tag = $element_styling->get_style_tag( $element_id );
			$container_class = $element_styling->get_class_string( 'container' );

			$output  = '';
			$output .= $style_tag;
			$output .= "<div class='{$container_class}'>";

			if ( $product->is_type( 'variable' ) )
			{
				wp_enqueue_script( 'wc-add-to-cart-variation' );

				add_action( 'woocommerce_after_variations_form', 'woocommerce_template_single_meta', 10 );

				ob_start();

				$output .= woocommerce_variable_add_to_cart();
			}
			else
			{
				// fix a problem with SEO plugin
				if( function_exists( 'wc_clear_notices' ) )
				{
					wc_clear_notices();
				}

				ob_start();

				$output .=		'<p class="price">' . $product->get_price_html() . '</p>';
				/**
				 * hooked by: add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
				 */
				do_action( 'woocommerce_single_product_summary' );
			}

			$output .=		ob_get_clean();
			$output .= '</div>';

			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );

			if( ! is_null( $wc_structured_data ) )
			{
				add_action( 'woocommerce_single_product_summary', array( $wc_structured_data, 'generate_product_data' ), 60 );
			}

			return $output;
		}
	}
}

