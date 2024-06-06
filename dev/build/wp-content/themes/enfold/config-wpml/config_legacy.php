<?php
/**
 * Legacy functions - will be removed in future releases
 *
 *
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


//	deprecated filters
//add_action( 'init', 'avia_wpml_register_post_type_permalink', 20 );
//add_filter( 'icl_ls_languages', 'avia_wpml_url_filter' );
//add_action( 'avia_wpml_backend_language_switch', 'avia_default_dynamics' );
//add_filter( 'wp_nav_menu_items', 'avia_append_lang_flags', 9998, 2 );
//add_filter( 'avf_fallback_menu_items', 'avia_append_lang_flags', 9998, 2 );
//add_action( 'avia_meta_header', 'avia_wpml_language_switch', 10 );
//add_action( 'ava_main_header_sidebar', 'avia_wpml_language_switch', 10 );

/**
 * Deprecated functions with 4.8
 * =============================
 *
 * Moved to avia_WPML
 */
if( ! function_exists( 'avia_wpml_options_language' ) )
{
	/**
	 * @deprecated since version 4.8
	 * @param array $base_data
	 */
	function avia_wpml_options_language( $base_data )
	{
		_deprecated_function( 'avia_wpml_options_language', '4.8', 'Avia_WPML()->handler_avf_options_languages()' );

		return Avia_WPML()->handler_avf_options_languages( $base_data );
	}
}

if( ! function_exists( 'avia_wpml_is_default_language' ) )
{
	/**
	 * @deprecated since version 4.8
	 * @return boolean
	 */
	function avia_wpml_is_default_language()
	{
		_deprecated_function( 'avia_wpml_is_default_language', '4.8', 'Avia_WPML()->is_default_language()' );

		return Avia_WPML()->is_default_language();
	}
}

if( ! function_exists( 'avia_wpml_get_languages' ) )
{
	/**
	 * @deprecated since version 4.8
	 */
	function avia_wpml_get_languages()
	{
		_deprecated_function( 'avia_wpml_get_languages', '4.8', 'Avia_WPML()->handler_ava_get_languages()' );

		Avia_WPML()->handler_ava_get_languages();
	}
}

if( ! function_exists( 'avia_wpml_get_options' ) )
{
	/**
	 * @deprecated since version 4.8
	 * @param string $option_key
	 * @return array
	 */
	function avia_wpml_get_options( $option_key )
	{
		_deprecated_function( 'avia_wpml_get_options', '4.8', 'Avia_WPML()->wpml_get_options()' );

		return Avia_WPML()->wpml_get_options( $option_key );
	}
}

if( ! function_exists( 'avia_wpml_register_assets' ) )
{
	/**
	 * @deprecated since version 4.8
	 */
	function avia_wpml_register_assets()
	{
		_deprecated_function( 'avia_wpml_register_assets', '4.8', 'Avia_WPML()->handler_wp_enqueue_scripts()' );

		Avia_WPML()->handler_wp_enqueue_scripts( $option_key );
	}
}

if( ! function_exists( 'avia_wpml_copy_options' ) )
{
	/**
	 * @deprecated since version 4.8
	 */
	function avia_wpml_copy_options()
	{
		_deprecated_function( 'avia_wpml_copy_options', '4.8', 'Avia_WPML()->handler_ava_copy_options()' );

		Avia_WPML()->handler_ava_copy_options();
	}
}

if( ! function_exists( 'avia_wpml_filter_dropdown_post_query' ) )
{
	function avia_wpml_filter_dropdown_post_query( $prepare_sql, $table_name, $limit, $element )
	{
		_deprecated_function( 'avia_wpml_filter_dropdown_post_query', '4.8', 'Avia_WPML()->handler_avf_dropdown_post_query()' );

		return Avia_WPML()->handler_avf_dropdown_post_query( $prepare_sql, $table_name, $limit, $element );
	}
}

/**
 * End deprecated functions with 4.8
 * =================================
 *
 */

/**
 * Deprecated functions with 4.8.2
 * ===============================
 *
 * Moved to avia_WPML
 */

if( ! function_exists( 'avia_append_lang_flags' ) )
{
	function avia_append_lang_flags( $items, $args )
	{
		_deprecated_function( 'avia_append_lang_flags', '4.8.2', 'Avia_WPML()->handler_append_lang_flags()' );

		return Avia_WPML()->handler_append_lang_flags( $items, $args );
	}
}

/**
 * End deprecated functions with 4.8.2
 * ===================================
 *
 */

/**
 * Deprecated functions with 4.9.2.2
 * ==================================
 *
 * Moved to avia_WPML
 */
if( ! function_exists( 'avia_translate_check_by_tag_values' ) )
{
	/**
	 * Translate tag values for attachments (av-helper-masonry.php)
	 *
	 * @since < 4.0
	 * @param array $value
	 * @return array
	 */
	function avia_translate_check_by_tag_values( $value )
	{
		_deprecated_function( 'avia_translate_check_by_tag_values', '4.9.2.2', 'Avia_WPML()->handler_avf_ratio_check_by_tag_values()' );

		return Avia_WPML()->handler_avf_ratio_check_by_tag_values( $value );
	}
}

/*fix for: https://wpml.org/errata/translation-editor-support-avia-layout-builder-enfold/*/
/**
 * Removed with 4.2.6 by Günter   (3/2018)
 * Replaced by function avia_wpml_sync_avia_layout_builder_meta below
 *
 * The datastructire of $fields must have changed. On testing if was always false because $fields['body']['data'] does not exist any more
 */
if( ! function_exists( 'avia_wpml_sync_avia_layout_builder' ) )
{
	/**
	 * Ensure backwards comp - structure was checked with this version - might already have been changed earlier
	 */
	if ( defined( 'WPML_TM_VERSION' ) && version_compare( WPML_TM_VERSION, '2.5.2', '<' ) )
	{
		add_action( 'wpml_translation_job_saved', 'avia_wpml_sync_avia_layout_builder', 10, 3 );

		function avia_wpml_sync_avia_layout_builder( $new_post_id, $fields, $job )
		{
			_deprecated_function( 'avia_wpml_sync_avia_layout_builder', '4.9.2.2', 'no longer needed with newer versions of WPML' );

			if( isset( $fields['body']['data'] ) )
			{
				if ( 'active' === get_post_meta( $new_post_id, '_aviaLayoutBuilder_active', true ) )
				{
					update_post_meta(
						$new_post_id,
						'_aviaLayoutBuilderCleanData',
						$fields['body']['data']
					);
				}
			}
		}
	}
}

/**
 * End deprecated functions with 4.9.2.2
 * =====================================
 *
 */


/**
 * Deprecated functions with 5.3
 * =============================
 *
 * Moved to avia_WPML
 */

if( ! function_exists( 'avia_wpml_language_switch' ) )
{
	/**
	 * @since ????
	 * @deprecated since version 5.3
	 */
	function avia_wpml_language_switch()
	{
		_deprecated_function( 'avia_wpml_language_switch', '5.3', 'Avia_WPML()->handler_append_lang_flags_extra_menu()' );

		Avia_WPML()->handler_append_lang_flags_extra_menu();
	}
}

/**
 * End deprecated functions with 5.3
 * =================================
 *
 */
