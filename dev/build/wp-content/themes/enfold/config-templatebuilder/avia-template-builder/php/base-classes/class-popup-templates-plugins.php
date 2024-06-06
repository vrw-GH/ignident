<?php
namespace aviaBuilder\base;

use AviaHelper;

/**
 * Base class implements modal popup templates related to integrated plugins.
 * Some functions have been moved from main class Avia_Popup_Templates
 *
 * @added_by Günter
 * @since 5.0
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

if( ! class_exists( __NAMESPACE__ . '\aviaPopupTemplatesPlugins', false ) )
{
	class aviaPopupTemplatesPlugins extends \aviaBuilder\base\aviaPopupTemplatesResponsive
	{

		/**
		 * @since 5.0
		 */
		protected function __construct()
		{
			parent::__construct();
		}

		/**
		 * @since 5.0
		 */
		public function __destruct()
		{
			parent::__destruct();
		}

		/**
		 * Geolocation Template - Creates a container to fetch long and lat from a given address
		 *
		 * @since 4.8.2
		 * @param array $element
		 * @return array
		 */
		protected function geolocation_toggle( array $element  )
		{
			$title = isset( $element['title'] ) ? $element['title'] : __( 'Marker Location', 'avia_framework' );

			$c = array(
						array(
							'name'		=> __( 'Street and Number', 'avia_framework' ),
							'desc'		=> __( 'Enter the street and streetnumber seperated by space, e.g. Teststreet 15.', 'avia_framework' ),
							'id'		=> 'geo_street',
							'type'		=> 'input',
							'std'		=> ''
						),

						array(
							'name'		=> __( 'Postalcode (Zip Code)', 'avia_framework' ),
							'desc'		=> __( 'Enter the postalcode (Zip code) for the city, e.g. 12454', 'avia_framework' ),
							'id'		=> 'geo_postalcode',
							'type'		=> 'input',
							'std'		=> ''
						),

						array(
							'name'		=> __( 'City', 'avia_framework' ),
							'desc'		=> __( 'Enter the city name, e.g. Denver', 'avia_framework' ),
							'id'		=> 'geo_city',
							'type'		=> 'input',
							'std'		=> ''
						),

						array(
							'name'		=> __( 'Country', 'avia_framework' ),
							'desc'		=> __( 'Enter the Country, e.g. Canada', 'avia_framework' ),
							'id'		=> 'geo_country',
							'type'		=> 'input',
							'std'		=> ''
						),

						array(
							'name'		=> __( 'Optional State', 'avia_framework' ),
							'desc'		=> __( 'Optionally enter State to identify the location', 'avia_framework' ),
							'id'		=> 'geo_state',
							'type'		=> 'input',
							'std'		=> ''
						),

						array(
							'name'		=> __( 'Optional County', 'avia_framework' ),
							'desc'		=> __( 'Optionally enter County to identify the location', 'avia_framework' ),
							'id'		=> 'geo_county',
							'type'		=> 'input',
							'std'		=> ''
						),

						array(
							'name'				=> __( 'Fetch coordinates', 'avia_framework' ),
							'desc'				=> __( 'Click button to fetch the coordinates for the address above to speed up loading of map in frontend.', 'avia_framework' ),
							'title'				=> __( 'Fetch coordinates for address above', 'avia_framework' ),
							'title_active'		=> __( 'Fetching address .......', 'avia_framework' ),
							'type'				=> 'action_button',
							'container_class'	=> 'avia-geolocation_get_coordinates',
							'modal_on_load'		=> 'modal_btn_geolocation_get_coordinates'
						),

						array(
							'name'		=> __( 'Longitude', 'avia_framework' ),
							'desc'		=> __( 'Enter the longitude of your adress, use "." for comma, e.g. 48.21475', 'avia_framework' ),
							'id'		=> 'geo_lng',
							'type'		=> 'input',
							'std'		=> ''
						),

						array(
							'name'		=> __( 'Latitude', 'avia_framework' ),
							'desc'		=> __( 'Enter the latitude of your adress, use "." for comma, e.g. 16.37366388888', 'avia_framework' ),
							'id'		=> 'geo_lat',
							'type'		=> 'input',
							'std'		=> ''
						),
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> $title,
								'content'		=> $c
							),
					);

			return $template;
		}

		/**
		 * Additional Leaflet Map and Leaflet Marker attributes
		 *
		 * @since 4.8.2
		 * @param array $element
		 * @return array
		 */
		protected function leaflet_attributes_toggle( array $element )
		{
			$id = isset( $element['id'] ) ? $element['id'] : 'add_attr';
			$name = isset( $element['name'] ) ? $element['name'] : __( 'Additional Attributes', 'avia_framework' );


			$desc  = __( 'Enter additional shortcode attributes here that are not available with predefined options.', 'avia_framework' ) . ' ';
			$desc .= __( 'We recommend to enter each option in a new line like:', 'avia_framework' ) . '<br /><br />';
			$desc .= __( 'option1="value1"', 'avia_framework' ) . '<br />';
			$desc .= __( 'option2="value2"', 'avia_framework' );

			$c = array(
						array(
							'name'		=> $name,
							'desc'		=> $desc,
							'id'		=> $id,
							'type'		=> 'textarea',
							'std'		=> '',
						)
				);

			$template = array(
								array(
									'type'			=> 'template',
									'template_id'	=> 'toggle',
									'title'			=> __( 'Additional Shortcode Attributes', 'avia_framework' ),
									'content'		=> $c
								),
					);

			return $template;
		}

		/**
		 * Returns the image size toggle for WooCommerce Sliders and Grid
		 *
		 * @since 4.8
		 * @param array $element
		 * @return array
		 */
		protected function wc_image_size_toggle( array $element )
		{
			global $_wp_additional_image_sizes;

			$id = isset( $element['id'] ) ? $element['id'] : 'image_size';
			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;

			$sizes = array();
			$std = '';

			if( is_array( $_wp_additional_image_sizes ) )
			{
				if( array_key_exists( 'woocommerce_thumbnail', $_wp_additional_image_sizes ) )
				{
					$std = 'woocommerce_thumbnail';
					$key = sprintf( __( 'Use default - WooCommerce Thumbnail (%d*%d)', 'avia_framework' ), $_wp_additional_image_sizes['woocommerce_thumbnail']['width'], $_wp_additional_image_sizes['woocommerce_thumbnail']['height'] );
					$sizes[ $key ] = 'woocommerce_thumbnail';
				}
				else if( array_key_exists( 'shop_catalog', $_wp_additional_image_sizes ) )
				{
					$std = 'shop_catalog';
					$key = sprintf( __( 'Use default - Shop Catalog (%d*%d)', 'avia_framework' ), $_wp_additional_image_sizes['shop_catalog']['width'], $_wp_additional_image_sizes['shop_catalog']['height'] );
					$sizes[ $key ] = 'shop_catalog';
				}
			}

			if( empty( $sizes ) )
			{
				$sizes[ __( 'Use default', 'avia_framework' ) ] = '';
			}

			$sizes = array_merge( $sizes, AviaHelper::get_registered_image_sizes( array( $std ), false, true ) );

			$c = array(

						array(
							'name'		=> __( 'Select image size', 'avia_framework' ),
							'desc'		=> __( 'Depending on your layout It might be better to select a larger image size for better image quality. Default size can be changed at Dashboard -&gt; Appearance -&gt; Customize -&gt; WooCommerce -&gt; Product Images -&gt; Thumbnail.', 'avia_framework' ),
							'id'		=> $id,
							'type'		=> 'select',
							'std'		=> $std,
							'lockable'	=> $lockable,
							'subtype'	=>  $sizes
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Image Size', 'avia_framework' ),
								'content'		=> $c
							),
					);


			return $template;
		}

		/**
		 *  Select boxes for WooCommerce Options for non product elements
		 *
		 * @since 4.5.7.1
		 * @param array $element
		 * @return array
		 */
		protected function wc_options_non_products( array $element )
		{
			$required = array( 'link', 'parent_in_array', implode( ',', get_object_taxonomies( 'product', 'names' ) ) );
			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;

			$sort = array(
							__( 'Use default (defined at Dashboard -&gt; Customize -&gt; WooCommerce)', 'avia_framework' )	=> '',
							__( 'Sort alphabetically', 'avia_framework' )			=> 'title',
							__( 'Sort by most recent', 'avia_framework' )			=> 'date',
							__( 'Sort by price', 'avia_framework' )					=> 'price',
							__( 'Sort by popularity', 'avia_framework' )			=> 'popularity',
							__( 'Sort randomly', 'avia_framework' )					=> 'rand',
							__( 'Sort by menu order and name', 'avia_framework' )	=> 'menu_order',
							__( 'Sort by average rating', 'avia_framework' )		=> 'rating',
							__( 'Sort by relevance', 'avia_framework' )				=> 'relevance',
							__( 'Sort by Product ID', 'avia_framework' )			=> 'id'
						);

			/**
			 * @since 4.5.7.1
			 * @param array $sort
			 * @param array $element
			 * @return array
			 */
			$sort = apply_filters( 'avf_alb_wc_options_non_products_sort', $sort, $element );


			$template = array();

			$template[] = array(
								'name'		=> __( 'WooCommerce Out of Stock Product visibility', 'avia_framework' ),
								'desc'		=> __( 'Select the visibility of WooCommerce products. Default setting can be set at Woocommerce -&gt Settings -&gt Products -&gt Inventory -&gt Out of stock visibility', 'avia_framework' ),
								'id'		=> 'wc_prod_visible',
								'type'		=> 'select',
								'std'		=> '',
								'lockable'	=> $lockable,
								'required'	=> $required,
								'subtype'	=> array(
													__( 'Use default WooCommerce Setting (Settings -&gt; Products -&gt; Out of stock visibility)', 'avia_framework' ) => '',
													__( 'Hide products out of stock', 'avia_framework' )	=> 'hide',
													__( 'Show products out of stock', 'avia_framework' )	=> 'show'
												)
							);

			$template[] = array(
								'name'		=> __( 'WooCommerce Hidden Products visibility', 'avia_framework' ),
								'desc'		=> __( 'Select the visibility of WooCommerce products depending on catalog visibility. Can be set independently for each product: Edit Product -&gt Publish panel -&gt Catalog visibility', 'avia_framework' ),
								'id'		=> 'wc_prod_hidden',
								'type'		=> 'select',
								'std'		=> 'hide',
								'lockable'	=> $lockable,
								'required'	=> $required,
								'subtype'	=> array(
													__( 'Show all products', 'avia_framework' )			=> '',
													__( 'Hide hidden products', 'avia_framework' )		=> 'hide',
													__( 'Show hidden products only', 'avia_framework' )	=> 'show'
												)
							);

			$template[] = array(
								'name'		=> __( 'WooCommerce Featured Products visibility', 'avia_framework' ),
								'desc'		=> __( 'Select the visibility of WooCommerce products depending on checkbox &quot;This is a featured product&quot; in catalog visibility. Can be set independently for each product: Edit Product -&gt Publish panel -&gt Catalog visibility', 'avia_framework' ),
								'id'		=> 'wc_prod_featured',
								'type'		=> 'select',
								'std'		=> '',
								'lockable'	=> $lockable,
								'required'	=> $required,
								'subtype'	=> array(
													__( 'Show all products', 'avia_framework' )				=> '',
													__( 'Hide featured products', 'avia_framework' )		=> 'hide',
													__( 'Show featured products only', 'avia_framework' )	=> 'show'
												)
							);

			$template[] = array(
								'name'		=> __( 'WooCommerce Sorting Options', 'avia_framework' ),
								'desc'		=> __( 'Here you can choose how to sort the products. Default setting can be set at Dashboard -&gt; Appearance -&gt; Customize -&gt; WooCommerce -&gt; Product Catalog -&gt; Default Product Sorting', 'avia_framework' ),
								'id'		=> 'prod_order_by',
								'type'		=> 'select',
								'std'		=> '',
								'lockable'	=> $lockable,
								'required'	=> $required,
								'subtype'	=> $sort
							);

			$template[] = array(
								'name'		=> __( 'WooCommerce Sorting Order', 'avia_framework' ),
								'desc'		=> __( 'Here you can choose the order of the result products. Default setting can be set at Dashboard -&gt; Appearance -&gt; Customize -&gt; WooCommerce -&gt; Product Catalog -&gt; Default Product Sorting', 'avia_framework' ),
								'id'		=> 'prod_order',
								'type'		=> 'select',
								'std'		=> '',
								'lockable'	=> $lockable,
								'required'	=> $required,
								'subtype'	=> array(
													__( 'Use default (defined at Dashboard -&gt; Customize -&gt; WooCommerce)', 'avia_framework' ) => '',
													__( 'Ascending', 'avia_framework' )			=> 'ASC',
													__( 'Descending', 'avia_framework' )		=> 'DESC'
												)
							);

			return $template;
		}


		/**
		 *  Select boxes for WooCommerce Options for product elements
		 *
		 * @since 4.5.7.1
		 * @param array $element
		 * @return array
		 */
		protected function wc_options_products( array $element )
		{
			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;

			$sort = array(
							__( 'Use default (defined at Dashboard -&gt; Customize -&gt; WooCommerce)', 'avia_framework' )	=> '0',
							__( 'Sort alphabetically', 'avia_framework' )			=> 'title',
							__( 'Sort by most recent', 'avia_framework' )			=> 'date',
							__( 'Sort by price', 'avia_framework' )					=> 'price',
							__( 'Sort by popularity', 'avia_framework' )			=> 'popularity',
							__( 'Sort randomly', 'avia_framework' )					=> 'rand',
							__( 'Sort by menu order and name', 'avia_framework' )	=> 'menu_order',
							__( 'Sort by average rating', 'avia_framework' )		=> 'rating',
							__( 'Sort by relevance', 'avia_framework' )				=> 'relevance',
							__( 'Sort by Product ID', 'avia_framework' )			=> 'id'
						);

			$sort_std = '0';

			if( ! empty( $element['sort_dropdown'] ) )
			{
				$sort = array_merge( array( __( 'Let user pick by displaying a dropdown with sort options (default value is defined at Default product sorting)', 'avia_framework' ) => 'dropdown' ), $sort );
				$sort_std = 'dropdown';
			}

			/**
			 * @since 4.5.7.1
			 * @param array $sort
			 * @param array $element
			 * @return array
			 */
			$sort = apply_filters( 'avf_alb_wc_options_non_products_sort', $sort, $element );

			$template = array();

			$template[] = array(
								'name'		=> __( 'WooCommerce Out of Stock Product visibility', 'avia_framework' ),
								'desc'		=> __( 'Select the visibility of WooCommerce products. Default setting can be set at Woocommerce -&gt Settings -&gt Products -&gt Inventory -&gt Out of stock visibility', 'avia_framework' ),
								'id'		=> 'wc_prod_visible',
								'type'		=> 'select',
								'std'		=> '',
								'lockable'	=> $lockable,
								'subtype'	=> array(
													__( 'Use default WooCommerce Setting (Settings -&gt; Products -&gt; Out of stock visibility)', 'avia_framework' ) => '',
													__( 'Hide products out of stock', 'avia_framework' )	=> 'hide',
													__( 'Show products out of stock', 'avia_framework' )	=> 'show'
												)
							);


			$template[] = array(
								'name'		=> __( 'WooCommerce Hidden Products visibility', 'avia_framework' ),
								'desc'		=> __( 'Select the visibility of WooCommerce products depending on catalog visibility. Can be set independently for each product: Edit Product -&gt Publish panel -&gt Catalog visibility', 'avia_framework' ),
								'id'		=> 'wc_prod_hidden',
								'type'		=> 'select',
								'std'		=> '',
								'lockable'	=> $lockable,
								'subtype'	=> array(
													__( 'Show all products', 'avia_framework' )			=> '',
													__( 'Hide hidden products', 'avia_framework' )		=> 'hide',
													__( 'Show hidden products only', 'avia_framework' )	=> 'show'
												)
							);

			$template[] = array(
								'name'		=> __( 'WooCommerce Featured Products visibility', 'avia_framework' ),
								'desc'		=> __( 'Select the visibility of WooCommerce products depending on checkbox &quot;This is a featured product&quot; in catalog visibility. Can be set independently for each product: Edit Product -&gt Publish panel -&gt Catalog visibility', 'avia_framework' ),
								'id'		=> 'wc_prod_featured',
								'type'		=> 'select',
								'std'		=> '',
								'lockable'	=> $lockable,
								'subtype'	=> array(
													__( 'Show all products', 'avia_framework' )				=> '',
													__( 'Hide featured products', 'avia_framework' )		=> 'hide',
													__( 'Show featured products only', 'avia_framework' )	=> 'show'
												)
							);

			$template[] = array(
								'name'		=> __( 'WooCommerce Sidebar Filters', 'avia_framework' ),
								'desc'		=> __( 'Allow to filter products for this element using the 3 WooCommerce sidebar filters: Filter Products by Price, Rating, Attribute. These filters are only shown on the selected WooCommerce Shop page (WooCommerce -&gt; Settings -&gt; Products -&gt; General -&gt; Shop Page) or on product category pages. You may also use a custom widget area for the sidebar.', 'avia_framework' ),
								'id'		=> 'wc_prod_additional_filter',
								'type'		=> 'select',
								'std'		=> '',
								'lockable'	=> $lockable,
								'subtype'	=> array(
													__( 'Ignore filters', 'avia_framework' )	=> '',
													__( 'Use filters', 'avia_framework' )		=> 'use_additional_filter'
												)
							);

			$template[] = array(
								'name'		=> __( 'WooCommerce Sorting Options', 'avia_framework' ),
								'desc'		=> __( 'Here you can choose how to sort the products. Default setting can be set at Dashboard -&gt; Appearance -&gt; Customize -&gt; WooCommerce -&gt; Product Catalog -&gt; Default Product Sorting', 'avia_framework' ),
								'id'		=> 'sort',
								'type'		=> 'select',
								'std'		=> $sort_std,
								'lockable'	=> $lockable,
								'subtype'	=> $sort
							);

			$template[] = array(
								'name'		=> __( 'WooCommerce Sorting Order', 'avia_framework' ),
								'desc'		=> __( 'Here you can choose the order of the result products. Default setting can be set at Dashboard -&gt; Appearance -&gt; Customize -&gt; WooCommerce -&gt; Product Catalog -&gt; Default Product Sorting', 'avia_framework' ),
								'id'		=> 'prod_order',
								'type'		=> 'select',
								'std'		=> '',
								'lockable'	=> $lockable,
								'subtype'	=> array(
													__( 'Use default (defined at Dashboard -&gt; Customize -&gt; WooCommerce)', 'avia_framework' ) => '',
													__( 'Ascending', 'avia_framework' )			=> 'ASC',
													__( 'Descending', 'avia_framework' )		=> 'DESC'
												)
							);

			return $template;
		}

		/**
		 * Select boxes for WooCommerce Options for product taxonomies selection
		 * Integrates a fallback for old elements
		 *
		 * @since 5.4.1
		 * @param array $element
		 * @return array
		 */
		protected function wc_select_product_taxonomies_toggle( array $element )
		{
			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;

			/**
			 * @since 5.4.1
			 * @param array $taxonomies
			 * @return array
			 */
			$taxonomies = apply_filters( 'avf_select_product_taxonomies', array( 'product_cat', 'product_tag' ) );

			$c = array(
						array(
							'name'		=> __( 'Selectbox For Entries', 'avia_framework' ),
							'desc'		=> __( 'Select where to choose the entries from', 'avia_framework' ),
							'id'		=> 'select_type',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> $lockable,
							'subtype'	=> array(
												__( 'Select from categories only', 'avia_framework' )	=> '',
												__( 'Extended selectbox', 'avia_framework' )			=> 'extended'
											)
						),

						array(
							'name'		=> __( 'Products To Display', 'avia_framework' ),
							'desc'		=> __( 'Select which products should be displayed by selecting a taxonomy', 'avia_framework' ),
							'id'		=> 'categories',
							'type'		=> 'select',
							'taxonomy'	=> 'product_cat',
							'subtype'	=> 'cat',
							'multiple'	=> 6,
							'std'		=> '',
							'lockable'	=> $lockable,
							'required' 	=> array( 'select_type', 'equals', '' )
						),

						array(
							'name'		=> __( 'Products To Display', 'avia_framework' ),
							'desc'		=> __( 'Select which products should be displayed by selecting a taxonomy', 'avia_framework' ),
							'id'		=> 'link',
							'type'		=> 'linkpicker',
							'fetchTMPL'	=> true,
							'multiple'	=> 6,
							'std'		=> '',
							'lockable'	=> $lockable,
							'posttype'	=> 'product',
							'taxtype'	=> $taxonomies,
							'subtype'	=> array( __( 'Display Entries from:', 'avia_framework' ) => 'taxonomy' ),
							'required' 	=> array( 'select_type', 'not', '' )
						),

						array(
							'name'		=> __( 'Multiple Categories/Terms Relation', 'avia_framework' ),
							'desc'		=> __( 'Select to use an OR or AND relation. In AND an entry must be in all selected categories/terms to be displayed. Defaults to OR', 'avia_framework' ),
							'id'		=> 'term_rel',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'OR', 'avia_framework' )	=> '',
												__( 'AND', 'avia_framework' )	=> 'AND'
											)
						)

				);

			if( isset( $element['show_images'] ) && true === $element['show_images'] )
			{
				$c[] = array(
							'name' 	=> __( 'Product Images', 'avia_framework' ),
							'desc' 	=> __( 'Should product image be displayed?', 'avia_framework' ),
							'id' 	=> 'show_images',
							'type' 	=> 'select',
							'std' 	=> 'yes',
							'lockable'	=> $lockable,
							'subtype'	=> array(
												__( 'yes', 'avia_framework' )	=> 'yes',
												__( 'no', 'avia_framework' )	=> 'no'
											)
						);
			}

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Products', 'avia_framework' ),
								'content'		=> $c
							),
					);

			return $template;
		}

	}
}
