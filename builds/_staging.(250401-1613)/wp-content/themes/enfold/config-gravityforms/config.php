<?php
/**
 * @since ???
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'GFForms', false ) )
{
	return;
}


if( ! function_exists( 'avia_add_gravity_scripts' ) )
{
	function avia_add_gravity_scripts()
	{
		$vn = avia_get_theme_version();
		$min_css = avia_minify_extension( 'css' );

		wp_register_style( 'avia-gravity', get_template_directory_uri() . "/config-gravityforms/gravity-mod{$min_css}.css", array(), $vn, 'screen' );

		wp_enqueue_style( 'avia-gravity' );
	}

	add_action( 'wp_enqueue_scripts', 'avia_add_gravity_scripts', 500 );
}


if( ! function_exists( 'avia_add_gf_button_to_editor' ) )
{
	/**
	 * add the gravityforms button to the ajax popup editor
	 *
	 * @param boolean $is_post_edit_page
	 * @return boolean
	 */
	function avia_add_gf_button_to_editor( $is_post_edit_page )
	{
		if( ! empty( $_POST['ajax_fetch'] ) )
		{
			$is_post_edit_page = true;
		}

		if( $is_post_edit_page )
		{
			return $is_post_edit_page;
		}

		$curr = basename( $_SERVER['PHP_SELF'] );

		return in_array( $curr, array(
								'post.php',
								'page.php',
								'page-new.php',
								'post-new.php',
								'customize.php',
							) );

	}

	add_filter( 'gform_display_add_form_button', 'avia_add_gf_button_to_editor', 10, 1 );
}

