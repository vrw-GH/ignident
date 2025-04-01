<?php

if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! function_exists( 'avia_woocommerce_general_settings_filter' ) )
{
	/**
	 * remove backend options by removing them from the config array
	 *
	 * @since ????
	 * @param array $options
	 * @return array
	 */
	function avia_woocommerce_general_settings_filter( $options )
	{
		$remove = array( 'woocommerce_enable_lightbox', 'woocommerce_frontend_css' );
		//$remove = array( 'image_options', 'woocommerce_enable_lightbox', 'woocommerce_catalog_image', 'woocommerce_single_image', 'woocommerce_thumbnail_image', 'woocommerce_frontend_css' );

		foreach( $options as $key => $option )
		{
			if( isset( $option['id'] ) && in_array( $option['id'], $remove ) )
			{
				unset( $options[$key] );
			}
		}

		return $options;
	}

	add_filter( 'woocommerce_general_settings', 'avia_woocommerce_general_settings_filter', 10, 1 );
	add_filter( 'woocommerce_page_settings', 'avia_woocommerce_general_settings_filter', 10, 1 );
	add_filter( 'woocommerce_catalog_settings', 'avia_woocommerce_general_settings_filter', 10, 1 );
	add_filter( 'woocommerce_inventory_settings', 'avia_woocommerce_general_settings_filter', 10, 1 );
	add_filter( 'woocommerce_shipping_settings', 'avia_woocommerce_general_settings_filter', 10, 1 );
	add_filter( 'woocommerce_tax_settings', 'avia_woocommerce_general_settings_filter', 10, 1 );
	add_filter( 'woocommerce_product_settings', 'avia_woocommerce_general_settings_filter', 10, 1 );
}


if( ! function_exists( 'avia_woocommerce_set_defaults' ) )
{
	/**
	 * on theme activation set default image size, disable woo lightbox and woo stylesheet. options are already hidden by previous filter function
	 *
	 * @since ????
	 */
	function avia_woocommerce_set_defaults()
	{
		global $avia_config;

		update_option( 'shop_catalog_image_size', $avia_config['imgSize']['shop_catalog'] );
		update_option( 'shop_single_image_size', $avia_config['imgSize']['shop_single'] );
		update_option( 'shop_thumbnail_image_size', $avia_config['imgSize']['shop_thumbnail'] );

		//set custom

		update_option( 'avia_woocommerce_column_count', 3 );
		update_option( 'avia_woocommerce_product_count', 15 );

		//set blank
		$set_false = array( 'woocommerce_enable_lightbox', 'woocommerce_frontend_css' );
		foreach( $set_false as $option )
		{
			update_option( $option, false );
		}

		//set blank
		$set_no = array( 'woocommerce_single_image_crop' );
		foreach( $set_no as $option )
		{
			update_option( $option, 'no' );
		}

	}

	add_action( 'avia_backend_theme_activation', 'avia_woocommerce_set_defaults', 10 );
}

if( ! function_exists( 'avia_woocommerce_first_activation' ) )
{
	/**
	 * activate the plugin options when this file is included for the first time
	 *
	 * @since ????
	 */
	function avia_woocommerce_first_activation()
	{
		if( ! is_admin() )
		{
			return;
		}

		$themeNice = avia_backend_safe_string( THEMENAME );

		if( get_option( "{$themeNice}_woo_settings_enabled" ) )
		{
			return;
		}

		update_option( "{$themeNice}_woo_settings_enabled", '1' );

		avia_woocommerce_set_defaults();
	}

	add_action( 'admin_init', 'avia_woocommerce_first_activation' , 45 );
}

if( ! function_exists( 'avia_please_install_woo' ) )
{
	/**
	 * Helper function returns an info message instead executing a shortcode
	 *
	 * @since ????
	 * @return string
	 */
	function avia_please_install_woo()
	{
		$url = network_site_url( 'wp-admin/plugin-install.php?tab=search&type=term&s=WooCommerce&plugin-search-input=Search+Plugins' );

		$output = "<p class='please-install-woo' style='display:block; text-align:center; clear:both;'><strong>You need to install and activate the <a href='$url' style='text-decoration:underline;'>WooCommerce Shop Plugin</a> to display WooCommerce Products</strong></p>";

		return $output;
	}
}


if( ! function_exists( 'avia_woocommerce_page_settings_filter' ) )
{
	/**
	 * add new options to the catalog settings
	 *
	 * @since ????
	 * @param array $options
	 * @return array
	 */
	function avia_woocommerce_page_settings_filter( $options )
	{

		$options[] = array(
						'name'	=> __( 'Column and Product Count', 'avia_framework' ),
						'desc'	=> __( 'The following settings allow you to choose how many columns and items should appear on your default shop overview page and your product archive pages.<br/><small>Notice: These options are added by the <strong>' . THEMENAME . ' Theme</strong> and wont appear on other themes</small>', 'avia_framework' ),
						'id'	=> 'column_options',
						'type'	=> 'title'
					);

		$options[] = array(
						'name'		=> __( 'Column Count', 'avia_framework' ),
						'desc'		=> '',
						'desc_tip'	=> __( 'This controls how many columns should appear on overview pages', 'avia_framework' ),
						'id'		=> 'avia_woocommerce_column_count',
						'type'		=> 'select',
						'css'		=> 'min-width:175px;',
						'std'		=> '3',
						'options'	=> array(
											'2'	=> '2',
											'3'	=> '3',
											'4'	=> '4',
											'5'	=> '5'
										)
					);

		$itemcount = array(
						'-1'	=> __( 'All', 'avia_framework' )
					);

		for( $i = 3; $i < 101; $i++ )
		{
			$itemcount [ $i ] = $i;
		}

		$options[] = array(
						'name'		=> __( 'Product Count', 'avia_framework' ),
						'desc'		=> '',
						'desc_tip'	=> __( 'This controls how many products should appear on overview pages.', 'avia_framework' ),
						'id'		=> 'avia_woocommerce_product_count',
						'type'		=> 'select',
						'css'		=> 'min-width:175px;',
						'std'		=> '24',
						'options'	=> $itemcount
					);

		$options[] = array(
						'type'	=> 'sectionend',
						'id'	=> 'column_options'
					);

		return $options;
	}

	add_filter( 'woocommerce_catalog_settings', 'avia_woocommerce_page_settings_filter', 10, 1 );
	add_filter( 'woocommerce_product_settings', 'avia_woocommerce_page_settings_filter', 10, 1 );
}


######################################################################
# add custom product page meta boxes
######################################################################
if( ! function_exists( 'avia_woocommerce_product_options' ) )
{
	/**
	 * Metabox Container
	 *
	 * @since ????
	 * @param array $boxes
	 * @return array
	 */
	function avia_woocommerce_product_options( $boxes )
	{
		$boxes[] = array(
						'title'		=> __( 'Product Hover', 'avia_framework' ),
						'id'		=> 'avia_product_hover',
						'page'		=> array( 'product' ),
						'context'	=> 'side',
						'priority'	=> 'low'
					);

		$counter = 0;
		foreach( $boxes as $box )
		{
			if( $box['id'] == 'layout' )
			{
				$boxes[ $counter ]['page'][] = 'product';
			}

			$counter++;
		}

		return $boxes;
	}


	/**
	 * Metabox Content
	 *
	 * @since ????
	 * @param array $elements
	 * @return array
	 */
	function avia_woocommerce_product_elements( $elements )
	{
		$posttype = avia_backend_get_post_type();

		/**
		 * Change default selected option
		 *
		 * @since 5.1.2
		 * @param string $default_value
		 * @return string						'' | 'hover_active'
		 */
		$std_hover = apply_filters( 'avf_wc_product_hover_default', '' );

		if( ! empty( $posttype ) && $posttype == 'product' )
		{
			$elements[] = array(
								'slug'		=> 'avia_product_hover',
								'name'		=> __( 'Hover effect on <strong>Overview Pages</strong>', 'avia_framework' ),
								'desc'		=> __( 'Do you want a hover effect on overview pages and replace the default thumbnail with the first image of the gallery?', 'avia_framework' ),
								'id'		=> '_product_hover',
								'type'		=> 'select',
								'std'		=> $std_hover,
								'class'		=> 'avia-style',
								'subtype'	=> array(
													__( 'No hover effect', 'avia_framework' )					=> '',
													__( 'Show first gallery image on hover', 'avia_framework' )	=> 'hover_active'
												)
						);

			$counter = 0;
			foreach( $elements as $element )
			{
				if( $element['id'] == 'sidebar' )
				{
					$elements[ $counter ]['required'] = '';
				}
				else if( $element['id'] == 'layout' )
				{
					$elements[ $counter ]['builder_active'] = true;
				   // unset( $elements[ $counter ] );
				}

				$counter++;
			}
		}

		return $elements;
	}

	add_filter( 'avf_builder_boxes', 'avia_woocommerce_product_options', 10, 1 );
	add_filter( 'avf_builder_elements', 'avia_woocommerce_product_elements', 500, 1 );
}

if( ! function_exists( 'avia_woocommerce_product_metabox_layout' ) )
{
	/**
	 * Add Product post type to layout metabox array
	 *
	 * @since 6.0
	 * @param array $post_layout_types
	 * @return array
	 */
	function avia_woocommerce_product_metabox_layout( $post_layout_types )
	{
		$post_layout_types[] = 'product';

		return $post_layout_types;
	}

	add_filter( 'avf_metabox_layout_post_types', 'avia_woocommerce_product_metabox_layout', 20, 1 );
}


######################################################################
# add extra fields to product category
######################################################################

if( ! function_exists( 'avia_woo_save_category_fields' ) )
{
	/**
	 * @since ????
	 * @param int $term_id
	 */
	function avia_woo_save_category_fields( $term_id )
	{
		/**
		 * WC tables for storing term meta are deprecated from WordPress 4.4 since 4.4 has its own table.
		 * This is a wrapper, using the new table if present, or falling back to the WC table.
		 * see woocommerce\includes\wc-deprecated-functions.php
		 *
		 * @since WC 3.6
		 * @since 4.6.4
		 */
		if( isset( $_POST['av_cat_styling'] ) )
		{
			if( function_exists( 'update_term_meta' ) )
			{
				update_term_meta( $term_id, 'av_cat_styling', esc_attr( $_POST['av_cat_styling'] ) );
			}
			else
			{
				update_woocommerce_term_meta( $term_id, 'av_cat_styling', esc_attr( $_POST['av_cat_styling'] ) );
			}
		}

		if( isset( $_POST['av-banner-font'] ) )
		{
			if( function_exists( 'update_term_meta' ) )
			{
				update_term_meta( $term_id, 'av-banner-font', esc_attr( $_POST['av-banner-font'] ) );
			}
			else
			{
				update_woocommerce_term_meta( $term_id, 'av-banner-font', esc_attr( $_POST['av-banner-font'] ) );
			}
		}

		if( isset( $_POST['av-banner-overlay'] ) )
		{
			if( function_exists( 'update_term_meta' ) )
			{
				update_term_meta( $term_id, 'av-banner-overlay', esc_attr( $_POST['av-banner-overlay'] ) );
			}
			else
			{
				update_woocommerce_term_meta( $term_id, 'av-banner-overlay', esc_attr( $_POST['av-banner-overlay'] ) );
			}
		}

		if( isset( $_POST['av_cat_styling'] ) )
		{
			if( function_exists( 'update_term_meta' ) )
			{
				update_term_meta( $term_id, 'av-banner-overlay-opacity', esc_attr( $_POST['av-banner-overlay-opacity'] ) );
			}
			else
			{
				update_woocommerce_term_meta( $term_id, 'av-banner-overlay-opacity', esc_attr( $_POST['av-banner-overlay-opacity'] ) );
			}
		}
	}

	add_action( 'created_term', 'avia_woo_save_category_fields' , 10 );
	add_action( 'edit_term', 'avia_woo_save_category_fields' , 10 );
}

if( ! function_exists( 'av_woo_enqueue_color_picker' ) )
{
	/**
	 * @since ????
	 * @param string $hook_suffix
	 */
	function av_woo_enqueue_color_picker( $hook_suffix )
	{
		// first check that $hook_suffix is appropriate for your admin page
		if( ( $hook_suffix == 'edit-tags.php' || $hook_suffix == 'term.php' ) && isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] == 'product_cat' )
		{
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
		}
	}

	add_action( 'admin_enqueue_scripts', 'av_woo_enqueue_color_picker', 10, 1 );
}

if( ! function_exists( 'avia_woo_add_category_fields' ) )
{
	/**
	 * @since ????
	 * @param WP_Term $term
	 */
	function avia_woo_add_category_fields( $term )
	{
		?>
			<div class="form-field" >
				<label for="av_cat_styling"> <?php echo THEMENAME." "; _e( 'Category Styling', 'avia_framework' ); ?></label>
				<?php avia_woo_styling_select( $term ); ?>
			</div>

			<div class="form-field dependant_on_av_cat_styling hidden" >
				<h3> <?php _e( 'Banner Options', 'avia_framework' ); ?></h3>
				<?php avia_woo_banner_options( $term ); ?>
			</div>
		<?php
	}

	add_action( 'product_cat_add_form_fields', 'avia_woo_add_category_fields', 1000, 1 );
}

if( ! function_exists( 'avia_woo_edit_category_fields' ) )
{
	/**
	 * @since ????
	 * @param WP_Term $term
	 */
	function avia_woo_edit_category_fields( $term )
	{
		$styling = is_object( $term) ? avia_get_woocommerce_term_meta( $term->term_id, 'av_cat_styling', true ) : '';
		$hidden  = empty( $styling) ?  'dependant_on_av_cat_styling hidden' : 'dependant_on_av_cat_styling';

		?>
			<tr class="form-field">
				<th scope="row" valign="top"><label><?php echo THEMENAME." "; _e( 'Category Styling', 'avia_framework' ); ?></label></th>
				<td>
					<?php avia_woo_styling_select( $term ); ?>
				</td>
			</tr>

			<tr class="form-field <?php echo $hidden; ?> ">
				<th scope="row" valign="top"><label><?php _e( 'Banner Options', 'avia_framework' ); ?></label></th>
				<td>
					<?php avia_woo_banner_options( $term ); ?>
				</td>
			</tr>

		<?php
	}

	add_action( 'product_cat_edit_form_fields', 'avia_woo_edit_category_fields', 1000, 1 );
}

if( ! function_exists( 'avia_woo_styling_select' ) )
{
	/**
	 *
	 * @since ????
	 * @param WP_Term $term
	 */
	function avia_woo_styling_select( $term )
	{
		$styling = is_object( $term ) ? avia_get_woocommerce_term_meta( $term->term_id, 'av_cat_styling', true ) : '';

		/**
		 * IMPORTANT:
		 * ==========
		 *
		 * "header_dark" is kept for backwards compatibility as changing might break existing sites.
		 *
		 */

		?>
			<select id="av_cat_styling" name="av_cat_styling" class="postform" style="max-width: 100%; " >
					<option value=""><?php _e( 'Default', 'avia_framework' ); ?></option>
					<option value="header_dark" <?php selected( 'header_dark', $styling ); ?>><?php _e( 'Display thumbnail and description as fullwidth banner with parallax effect below header', 'avia_framework' ); ?></option>
					<option value="cat_banner_below" <?php selected( 'cat_banner_below', $styling ); ?>><?php _e( 'Display thumbnail and description as fullwidth banner with parallax effect below title/breadcrumb', 'avia_framework' ); ?></option>
					<option value="header_dark av-scroll" <?php selected( 'header_dark av-scroll', $styling ); ?>><?php _e( 'Display thumbnail and description as fullwidth background banner image with scroll below header', 'avia_framework' ); ?></option>
					<option value="cat_banner_below av-scroll" <?php selected( 'cat_banner_below av-scroll', $styling ); ?>><?php _e( 'Display thumbnail and description as fullwidth background banner image with scroll below title/breadcrumb', 'avia_framework' ); ?></option>
					<option value="header_dark av-responsive" <?php selected( 'header_dark av-responsive', $styling ); ?>><?php _e( 'Display thumbnail and description as responsive banner image with description below image below header', 'avia_framework' ); ?></option>
					<option value="cat_banner_below av-responsive" <?php selected( 'cat_banner_below av-responsive', $styling ); ?>><?php _e( 'Display thumbnail and description as responsive banner image with description below image below title/breadcrumb', 'avia_framework' ); ?></option>
			</select>
			<script type="text/javascript">

					var target_id = "av_cat_styling";

					jQuery( function($){
						jQuery('.av-woo-colorpicker').wpColorPicker();
					});

					jQuery( document ).on( 'change', '#'+target_id, function( event ) {

						var dependent = jQuery(".dependant_on_"+target_id),
							display_val = dependent.is('tr') ? "table-row" : "block";

						if(this.value == '')
						{
							dependent.css({display:'none'});
						}
						else
						{
							dependent.css({display:display_val});
						}
					});
			</script>
		<?php
	}
}

if( ! function_exists( 'avia_woo_banner_options' ) )
{
	/**
	 *
	 * @since ????
	 * @param WP_Term $term
	 */
	function avia_woo_banner_options( $term )
	{
		$font 		= is_object( $term) ? avia_get_woocommerce_term_meta( $term->term_id, 'av-banner-font', true ) : '';
		$overlay 	= is_object( $term) ? avia_get_woocommerce_term_meta( $term->term_id, 'av-banner-overlay', true ) : '';
		$opacity 	= is_object( $term) ? avia_get_woocommerce_term_meta( $term->term_id, 'av-banner-overlay-opacity', true ) : '';

		if( empty( $opacity ) )
		{
			$opacity = "0.5";
		}

		?>
			<div class="av-woo-wp-picker-container" >
				<label><strong><?php _e( 'Description Font Color', 'avia_framework' ); ?></strong></label>
				<div><input type="text" name="av-banner-font" id="av-banner-font" class='av-woo-colorpicker' value='<?php echo $font; ?>' /></div>
			</div>

			<div class="av-woo-wp-picker-container" >
				<label><strong><?php _e( 'Banner Color Overlay (leave empty for no overlay), only for background image', 'avia_framework' ); ?></strong></label>
				<div><input type="text" name="av-banner-overlay" id="av-banner-overlay" class='av-woo-colorpicker' value='<?php echo $overlay; ?>' /></div>
			</div>

			<div class="av-woo-wp-picker-container" >
				<label><strong><?php _e( 'Set Opacity For Color Overlay', 'avia_framework' ); ?></strong></label>
				<div>
					<select id="av-banner-overlay-opacity" name="av-banner-overlay-opacity" class="postform">
						<option value="0.1" <?php selected( '0.1', $opacity ); ?>>0.1</option>
						<option value="0.2" <?php selected( '0.2', $opacity ); ?>>0.2</option>
						<option value="0.3" <?php selected( '0.3', $opacity ); ?>>0.3</option>
						<option value="0.4" <?php selected( '0.4', $opacity ); ?>>0.4</option>
						<option value="0.5" <?php selected( '0.5', $opacity ); ?>>0.5</option>
						<option value="0.6" <?php selected( '0.6', $opacity ); ?>>0.6</option>
						<option value="0.7" <?php selected( '0.7', $opacity ); ?>>0.7</option>
						<option value="0.8" <?php selected( '0.8', $opacity ); ?>>0.8</option>
						<option value="0.9" <?php selected( '0.9', $opacity ); ?>>0.9</option>
						<option value="1" <?php selected( '1', $opacity ); ?>>1</option>
					</select>
				</div>
			</div>
		<?php
	}
}
