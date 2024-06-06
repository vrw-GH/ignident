<?php
/**
 * Product Slider
 *
 * Display a Slideshow of Product Entries
 *
 * Original version that does not support post css files.
 * Can be used in addition to class avia_product_slider. Kept to allow backwards compatibility.
 * Use action 'ava_builder_core_files_loaded' or 'ava_builder_shortcode_files_loaded' to load if needed.
 *
 * @since 4.8.9		class avia_product_slider renamed to avia_product_slider_old (code based on version 4.8.9)
 * @deprecated since 4.8.9
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if ( ! class_exists( 'avia_product_slider_old', false ) )
{
	/**
	 * @deprecated since 4.8.9
	 */
	class avia_product_slider_old
	{
		/**
		 *
		 * @var int
		 */
		static protected $slide = 0;

		/**
		 *
		 * @var array
		 */
		protected $atts;

		/**
		 *
		 * @since 4.7.6.4
		 * @var int
		 */
		protected $current_page;


		public function __construct( $atts = array() )
		{
			$this->current_page = 1;

			$this->atts = shortcode_atts( avia_product_slider_old::get_defaults(), $atts, 'av_productslider' );

			if( $this->atts['items'] < 0 )
			{
				$this->atts['paginate'] = 'no';
			}
		}

		/**
		 * @since 4.5.7.2
		 */
		public function __destruct()
		{
			unset( $this->atts );
		}

		/**
		 * Return defaults array
		 *
		 * @since 4.8
		 * @return array
		 */
		static public function get_defaults()
		{
			$defaults = array(
						'type'				=> 'slider', // can also be used as grid
						'style'				=> '', //no_margin
						'columns'			=> '4',
						'image_size'		=> '',
						'items'				=> '16',
						'wc_prod_visible'	=>	'',
						'wc_prod_hidden'	=>	'',
						'wc_prod_featured'	=>	'',
						'wc_prod_additional_filter'		=> '',
						'taxonomy'			=> 'product_cat',
						'post_type'			=> 'product',
						'contents'			=> 'excerpt',
						'autoplay'			=> 'no',
						'animation'			=> 'fade',
						'paginate'			=> 'no',
						'interval'			=> 5,
						'class'				=> '',
						'sort'				=> '',
						'prod_order'		=> '',
						'offset'			=> 0,
						'link_behavior'		=> '',
						'show_images'		=> 'yes',
						'categories'		=> array(),
						'av_display_classes'	=> '',
						'el_id'				=> '',			//	must contain id="...."
						'custom_class'		=> ''
					);

			return $defaults;
		}


		/**
		 *
		 * @return string
		 */
		public function html()
		{
			global $woocommerce, $woocommerce_loop, $avia_config;

			$output = '';

			avia_product_slider_old::$slide ++;

			extract( $this->atts );

			$extraClass 		= 'first';
			$post_loop_count 	= 1;
			$loop_counter		= 1;
			$autoplay 			= $autoplay == 'no' ? false : true;
			$total				= $columns % 2 ? 'odd' : 'even';
			$woocommerce_loop['columns'] = $columns;

			switch( $columns )
			{
				case '1':
					$grid = 'av_fullwidth';
					break;
				case '2':
					$grid = 'av_one_half';
					break;
				case '3':
					$grid = 'av_one_third';
					break;
				case '4':
					$grid = 'av_one_fourth';
					break;
				case '5':
					$grid = 'av_one_fifth';
					break;
				default:
					$grid = 'av_one_third';
					break;
			}

			//	Add filter to change default WC image size
			add_filter( 'avf_wc_before_shop_loop_item_title_img_size', array( $this, 'handler_wc_image_size_slider' ), 1000, 1 );

			$data = AviaHelper::create_data_string( array( 'autoplay' => $autoplay, 'interval' => $interval, 'animation' => $animation, 'hoverpause' => 1 ) );

			ob_start();

			if ( have_posts() )
			{
				echo "<div {$el_id} {$data} class='template-shop avia-content-slider avia-content-{$type}-active avia-product-slider" . avia_product_slider_old::$slide . " avia-content-slider-{$total} {$class} {$av_display_classes} shop_columns_{$columns}' >";

				if( $sort == 'dropdown' )
				{
					avia_woocommerce_frontend_search_params();
				}

				echo 	"<div class='avia-content-slider-inner'>";

				if( $type == 'grid' )
				{
					echo '<ul class="products">';
				}

				while ( have_posts() ) : the_post();

					if( $loop_counter == 1 && $type == 'slider' )
					{
						echo '<ul class="products slide-entry-wrap">';
					}


					if( function_exists( 'wc_get_template_part' ) )
					{
						wc_get_template_part( 'content', 'product'  );
					}
					else
					{
						woocommerce_get_template_part( 'content', 'product' );
					}

					$loop_counter ++;
					$post_loop_count ++;

					if( $loop_counter > $columns )
					{
						$loop_counter = 1;
					}

					if( $loop_counter == 1 && $type == 'slider' )
					{
						echo '</ul>';
					}

				endwhile; // end of the loop.

				if( $loop_counter != 1 || $type == 'grid' )
				{
					echo '</ul>';
				}

				echo 	'</div>';

				if( $post_loop_count -1 > $columns && $type == 'slider' )
				{
					echo $this->slide_navigation_arrows();
				}

				echo '</div>';
			}
			else
			{
				if( function_exists( 'woocommerce_product_subcategories' ) )
				{
					if ( ! woocommerce_product_subcategories( array( 'before' => '<ul class="products">', 'after' => '</ul>' ) ) )
					{
						echo '<p>' . __( 'No products found which match your selection.', 'avia_framework' ) . '</p>';
					}
				}
			}

			echo '<div class="clear"></div>';

			$products = ob_get_clean();

			remove_filter( 'avf_wc_before_shop_loop_item_title_img_size', array( $this, 'handler_wc_image_size_slider' ), 1000 );

			$output .= $products;

			if( $paginate == 'yes' && $avia_pagination = avia_pagination( '', 'nav', 'avia-element-paging', $this->current_page ) )
			{
				$output .= "<div class='pagination-wrap pagination-slider {$av_display_classes}'>{$avia_pagination}</div>";
			}

			/**
			 * @since WC 3.3.0 we have to reset WC loop counter otherwise layout might break
			 */
			if( function_exists( 'wc_reset_loop' ) )
			{
				wc_reset_loop();
			}

			wp_reset_query();
			return $output;
		}

		/**
		 *
		 * @return string
		 */
		public function html_list()
		{
			global $woocommerce, $avia_config, $wp_query;

			$output = '';

			extract( $this->atts );

			$extraClass 		= 'first';
			$post_loop_count 	= 0;
			$loop_counter		= 0;
			$total				= $columns % 2 ? 'odd' : 'even';
			$posts_per_col		= ceil( $wp_query->post_count / $columns );

			switch( $columns )
			{
				case '1':
					$grid = 'av_fullwidth';
					break;
				case '2':
					$grid = 'av_one_half';
					break;
				case '3':
					$grid = 'av_one_third';
					break;
				case '4':
					$grid = 'av_one_fourth';
					break;
				case '5':
					$grid = 'av_one_fifth';
					break;
				default:
					$grid = 'av_fullwidth';
					break;
			}

			ob_start();

			if ( have_posts() )
			{
				while ( have_posts() ) : the_post();

					avia_product_slider_old::$slide ++;
					$post_loop_count ++;
					$loop_counter ++;
					if( $loop_counter === 1 )
					{
						echo "<div {$el_id} class='{$grid} {$extraClass} {$custom_class} flex_column av-catalogue-column {$av_display_classes} '>";
						echo "<div class='av-catalogue-container av-catalogue-container-woo' >";
						echo "<ul class='av-catalogue-list'>";
						$extraClass = '';
					}

					global $product;

					$link 	= 	$product->add_to_cart_url();
					$ajax_class = 'add_to_cart_button product_type_simple';
					$text	= '';
					$title 	= 	get_the_title();
					$content = 	strip_tags(get_the_excerpt());
					$price = 	$product->get_price_html();
					$rel   = '';
					$product_type = $product->get_type();

					/**
					 * Choose product types that link to single product pages when clicked and not ajax add to cart
					 * (currently only class avia_sc_productlist supports this option)
					 *
					 * @since 4.5.4
					 * @return array
					 */
					$force_product_page_array = apply_filters( 'avf_slider_add_to_cart_via_product_page', array( 'variable' ), $this );

					if( empty( $link_behavior ) || in_array( $product_type, $force_product_page_array ) )
					{
						$cart_url = get_the_permalink();
						$ajax_class = '';
					}
					else
					{
						$cart_url = $product->add_to_cart_url();
						$ajax_class = $product->is_purchasable() ? 'add_to_cart_button ajax_add_to_cart' : '';
						$rel = $product->is_purchasable() ? "rel='nofollow'" : '';
					}

					$product_id = method_exists( $product , 'get_id' ) ? $product->get_id() : $product->id;
					$product_type = method_exists( $product , 'get_type' ) ? $product->get_type() : $product->product_type;

					$image = get_the_post_thumbnail( $product_id, 'square', array( 'class' => "av-catalogue-image av-cart-update-image av-catalogue-image-{$show_images}" ) );

					$text .= $image;
					$text .= "<div class='av-catalogue-item-inner'>";
					$text .=	"<div class='av-catalogue-title-container'><div class='av-catalogue-title av-cart-update-title'>{$title}</div><div class='av-catalogue-price av-cart-update-price'>{$price}</div></div>";
					$text .=	"<div class='av-catalogue-content'>{$content}</div>";
					$text .= '</div>';

					echo '<li>';

					//copied from templates/loop/add-to-cart.php - class and rel attr changed, as well as text

					echo apply_filters( 'woocommerce_loop_add_to_cart_link',
								sprintf( '<a %s href="%s" data-product_id="%s" data-product_sku="%s" class="av-catalogue-item %s product_type_%s product-nr-%d">%s</a>',
									$rel,
									esc_url( $cart_url ),
									esc_attr( $product_id ),
									esc_attr( $product->get_sku() ),
									$ajax_class,
									esc_attr( $product_type ),
									avia_product_slider_old::$slide,
									$text
								),
							$product );

					echo '</li>';

					if( $loop_counter == $posts_per_col || $post_loop_count == $wp_query->post_count )
					{
						echo '</ul>';
						echo '</div>';
						echo '</div>';
						$loop_counter = 0;
					}

				endwhile; // end of the loop.

			}

			$products = ob_get_clean();

			$output .= $products;

			if( $paginate == 'yes' && $avia_pagination = avia_pagination( '', 'nav', 'avia-element-paging', $this->current_page ) )
			{
				$output .= "<div class='pagination-wrap pagination-slider {$av_display_classes} '>{$avia_pagination}</div>";
			}

			/**
			 * @since WC 3.3.0 we have to reset WC loop counter otherwise layout might break
			 */
			if( function_exists( 'wc_reset_loop' ) )
			{
				wc_reset_loop();
			}

			wp_reset_query();

			return $output;
		}

		/**
		 * Create arrows to scroll slides
		 *
		 * @since 4.8.3			reroute to aviaFrontTemplates
		 * @return string
		 */
		protected function slide_navigation_arrows()
		{
			$args = array(
						'context'		=> get_class( $this ),
						'params'		=> $this->atts
					);

			return aviaFrontTemplates::slide_navigation_arrows( $args );
		}

		/**
		 * Fetch new entries
		 *
		 * @param array $params
		 */
		public function query_entries( $params = array() )
		{
			global $woocommerce, $avia_config;

			$query = array();
			if( empty( $params ) )
			{
				$params = $this->atts;
			}

			if( ! empty( $params['categories'] ) )
			{
				//get the product categories
				$terms 	= explode( ',', $params['categories'] );
			}

			$this->current_page = ( $params['paginate'] == 'no' || $params['type'] == 'slider' ) ? 1:  avia_get_current_pagination_number( 'avia-element-paging' );

			//if we find no terms for the taxonomy fetch all taxonomy terms
			if( empty($terms[0]) || is_null( $terms[0] ) || $terms[0] === 'null' )
			{
				$term_args = array(
								'taxonomy'		=> $params['taxonomy'],
								'hide_empty'	=> true
							);
				/**
				 * To display private posts you need to set 'hide_empty' to false,
				 * otherwise a category with ONLY private posts will not be returned !!
				 *
				 * You also need to add post_status 'private' to the query params with filter avia_product_slide_query.
				 *
				 * @since 4.4.2
				 * @added_by Günter
				 * @param array $term_args
				 * @param array $params
				 * @return array
				 */
				$term_args = apply_filters( 'avf_av_productslider_term_args', $term_args, $params );

				$allTax = AviaHelper::get_terms( $term_args );

				$terms = array();
				foreach( $allTax as $tax )
				{
					$terms[] = $tax->term_id;
				}
			}

			if( $params['sort'] == 'dropdown' )
			{
				$avia_config['woocommerce']['default_posts_per_page'] = $params['items'];
				$ordering 	= $woocommerce->query->get_catalog_ordering_args();
				$order 		= $ordering['order'];
				$orderBY 	= $ordering['orderby'];

				if( ! empty( $avia_config['shop_overview_products_overwritten'] ) && $params['items'] != -1 )
				{
					$params['items'] = $avia_config['shop_overview_products'];
				}
			}
			else
			{
				$avia_config['woocommerce']['disable_sorting_options'] = true;

				$chk_sort = ( empty( $params['sort'] ) || $params['sort'] == '0' ) ? '' : $params['sort'];
				$ordering 	= avia_wc_get_product_query_order_args( $chk_sort, $params['prod_order'] );

				$order 		= $ordering['order'];
				$orderBY 	= $ordering['orderby'];
			}


            if( $params['offset'] == 'no_duplicates' )
            {
                $params['offset'] = 0;
                $no_duplicates = true;
            }

            if( $params['offset'] == 0 )
			{
				$params['offset'] = false;
			}


			// Meta query - replaced by Tax query in WC 3.0.0
			$meta_query = array();
			$tax_query = array();

			avia_wc_set_out_of_stock_query_params( $meta_query, $tax_query, $params['wc_prod_visible'] );
			avia_wc_set_hidden_prod_query_params( $meta_query, $tax_query, $params['wc_prod_hidden'] );
			avia_wc_set_featured_prod_query_params( $meta_query, $tax_query, $params['wc_prod_featured'] );

			if( 'use_additional_filter' == $params['wc_prod_additional_filter'] )
			{
				avia_wc_set_additional_filter_args( $meta_query, $tax_query );
			}

			$avia_config['woocommerce']['disable_sorting_options'] = true;

			//	sets filter hooks !!
			$ordering_args = avia_wc_get_product_query_order_args( $orderBY, $order );

			if( ! empty( $terms ) )
			{
				$tax_query[] =  array(
									'taxonomy' 	=>	$params['taxonomy'],
									'field' 	=>	'id',
									'terms' 	=>	$terms,
									'operator' 	=>	'IN'
							);
			}

			$query = array(
							'post_type'				=>	$params['post_type'],
							'post_status'			=>	'publish',
							'ignore_sticky_posts'	=>	1,
							'paged'					=>	$this->current_page,
							'offset'            	=>	$params['offset'],
							'post__not_in'			=>	( !empty( $no_duplicates ) ) ? $avia_config['posts_on_current_page'] : array(),
							'posts_per_page'		=>	$params['items'],
							'orderby'				=>	$ordering_args['orderby'],
							'order'					=>	$ordering_args['order'],
							'meta_query'			=>	$meta_query,
							'tax_query'				=>	$tax_query
												);


			if ( ! empty( $ordering_args['meta_key'] ) )
			{
	 			$query['meta_key'] = $ordering_args['meta_key'];
	 		}

			/**
			 * @used_by			currently unused
			 *
			 * @since < 4.0
			 * @param array $query
			 * @param array $params
			 * @param array $ordering_args
			 * @return array
			 */
			$query = apply_filters( 'avia_product_slide_query', $query, $params, $ordering_args );
			query_posts( $query );

		    // store the queried post ids in
            if( have_posts() )
            {
                while( have_posts() )
                {
                    the_post();
                    $avia_config['posts_on_current_page'][] = get_the_ID();
                }
            }

				//	remove all filters
			avia_wc_clear_catalog_ordering_args_filters();
			$avia_config['woocommerce']['disable_sorting_options'] = false;
		}

		/**
		 * Returns the selected image size
		 *
		 * @since 4.8
		 * @param string $size
		 * @return string
		 */
		public function handler_wc_image_size_slider( $size )
		{
			return ! empty( $this->atts['image_size'] ) ? $this->atts['image_size'] : $size;
		}
	}
}
