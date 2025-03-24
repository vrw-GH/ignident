<?php
/*
 * This helper file holds filters, actions and functions concerning WP core
 * Moved from functions.php
 *
 * @since 7.0
 * @added_by Günter
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


/*
 * deactivates the default mega menu and allows us to pass individual menu walkers when calling a menu
 */
add_filter( 'avia_mega_menu_walker', '__return_false' );



/*
 * Register theme text domain
 */
if( ! function_exists( 'avia_lang_setup' ) )
{
	/**
	 * WP 6.1 introduced class WP_Textdomain_Registry
	 * Since this when accessing a non existing language file object cache hits get very high
	 * Only way to avoid this is to check if file exists and do not call load_theme_textdomain()
	 * And we also need to check that we do not load it twice.
	 *
	 * @link https://kriesi.at/support/topic/5-6-vs-5-5-upgrade-breaks-site-functions-php-avia_lang_setup/
	 *
	 * @since 5.6.1
	 */
	function avia_lang_setup()
	{
		static $loaded = false;

		if( $loaded )
		{
			return;
		}

		 $loaded = true;

		/**
		 * Use WP filter
		 */
		$local = apply_filters( 'theme_locale', determine_locale(), 'avia_framework' );

		/**
		 * @since 5.6.1
		 * @param string $language_path
		 * @return string
		 */
		$language_path = apply_filters( 'ava_theme_textdomain_path', get_template_directory()  . '/lang' );
		$language_file = trailingslashit( $language_path ) . "{$local}.mo";

		if( ! is_readable( $language_file ) )
		{
			return;
		}

		/*
		 * @since 6.0.9 removed because WP throws _load_textdomain_just_in_time error but framework needs translations for admin bar and backend
		 */
//		load_theme_textdomain( 'avia_framework', $language_path );

		load_textdomain( 'avia_framework', $language_file, $local );
	}

	add_action( 'after_setup_theme', 'avia_lang_setup' );

	//	must be called to support admin bar in frontend !!!
	avia_lang_setup();
}


if( ! function_exists( 'avia_nav_menus' ) )
{
	/**
	 * Activate native wordpress navigation menu and register a menu location
	 *
	 */
	function avia_nav_menus()
	{
		global $avia_config, $wp_customize;

		foreach( $avia_config['nav_menus'] as $key => $value )
		{
			//wp-admin\customize.php does not support html code in the menu description - thus we need to strip it
			$name = ( ! empty( $value['plain'] ) && ! empty( $wp_customize ) ) ? $value['plain'] : $value['html'];
			register_nav_menu( $key, THEMENAME . ' ' . $name );
		}
	}

	if( current_theme_supports( 'nav_menus') )
	{
		add_action( 'after_setup_theme', 'avia_nav_menus' );
	}
}


if( ! function_exists( 'avia_custom_styles' ) )
{
	/**
	 * dynamic styles for front and backend
	 */
	function avia_custom_styles()
	{
//		require_once( 'includes/admin/register-dynamic-styles.php' );	// register the styles for dynamic frontend styling

		$file = dirname ( dirname(__FILE__ ) ) . '/admin/register-dynamic-styles.php';

		require_once( $file );	// register the styles for dynamic frontend styling

		avia_prepare_dynamic_styles();
	}

	add_action( 'init', 'avia_custom_styles', 20 );
	add_action( 'admin_init', 'avia_custom_styles', 20 );
}


if ( ! function_exists( '_wp_render_title_tag' ) )
{
	/**
	 * title fallback (up to WP 4.1)
	 */
    function av_theme_slug_render_title()
    {
	    echo '<title>' . avia_set_title_tag() . '</title>';
	}
	add_action( 'wp_head', 'av_theme_slug_render_title' );
}


if( ! function_exists( 'avia_add_admin_body_class' ) )
{
	/**
	 * Add a class to identify that enfold is used
	 *
	 * @since 4.7.4.1
	 * @param string $classes
	 * @return string
	 */
	function avia_add_admin_body_class( $classes )
	{
		$classes .= ' enfold-active';

		return $classes;
	}
	add_filter( 'admin_body_class', 'avia_add_admin_body_class', 10, 1 );
}


if( ! function_exists( 'avia_disable_seo_analysis_delay_cb' ) )
{
	/**
	 * Add a unique name to the body class attribute,
	 * which can be checked to adjust the analysis tool delay.
	 *
	 * @since 5.0
	 * @param string $classes
	 * @return string
	 */
	function avia_disable_seo_analysis_delay_cb( $classes )
	{
        $classes .= ' avia-seo-analysis-no-delay';

		return $classes;
	}

	//	must be set by user
	if( current_theme_supports( 'avia_disable_seo_analysis_delay' ) )
    {
        add_filter( 'admin_body_class', 'avia_disable_seo_analysis_delay_cb', 10, 1 );
    }
}

if( ! function_exists( 'avia_iframe_proportion_wrap' ) )
{
	/**
	 * wrap embeds into a proportion containing div
	 *
	 * @param string $html
	 * @param string $url
	 * @param array $attr
	 * @param int $post_ID
	 * @return string
	 */
	function avia_iframe_proportion_wrap ( $html, $url, $attr, $post_ID  )
	{
		if( strpos( $html, '<iframe' ) !== false )
		{
			$html = "<div class='avia-iframe-wrap'>{$html}</div>";
		}

	    return $html;
	}

	add_filter( 'embed_oembed_html', 'avia_iframe_proportion_wrap', 10, 4 );
}

if( ! function_exists( 'avia_upload_mimes' ) )
{
	/**
	 * allow additional file type uploads
	 *
	 * @param array $mimes
	 * @return array
	 */
	function avia_upload_mimes( $mimes )
	{
		return array_merge( $mimes, array( 'mp4' => 'video/mp4', 'ogv' => 'video/ogg', 'webm' => 'video/webm', 'txt' => 'text/plain' ) );
	}

	add_filter( 'upload_mimes', 'avia_upload_mimes' );
}

if( ! function_exists( 'avia_fix_tag_archive_page' ) )
{
	/**
	 * show tag archive page for post type - without this code you'll get 404 errors:
	 * http://wordpress.org/support/topic/custom-post-type-tagscategories-archive-page
	 *
	 * @param WP_Query $query
	 * @return WP_Query
	 */
	function avia_fix_tag_archive_page( $query )
	{
		$post_types = get_post_types();

		if( is_category() || is_tag() )
		{
			if( ! is_admin() && $query->is_main_query() )
			{
				$post_type = get_query_var( get_post_type() );

				if( $post_type )
				{
					$post_type = $post_type;
				}
				else
				{
					$post_type = $post_types;
				}

				$query->set( 'post_type', $post_type );
			}
		}

		return $query;
	}

	add_filter( 'pre_get_posts', 'avia_fix_tag_archive_page' );
}


if( ! function_exists( 'avia_add_compat_header' ) )
{
	/**
	 * IE compatibility
	 *
	 * @param array $headers
	 * @return array
	 */
	function avia_add_compat_header( $headers )
	{
		if( isset( $_SERVER['HTTP_USER_AGENT'] ) && strpos( $_SERVER['HTTP_USER_AGENT'], 'MSIE' ) !== false )
		{
			$headers['X-UA-Compatible'] = 'IE=edge,chrome=1';
		}

		return $headers;
	}

	add_filter( 'wp_headers', 'avia_add_compat_header' );
}


if( ! function_exists( 'avia_add_favicon' ) )
{
	/**
	 *
	 */
	function avia_add_favicon()
	{
		echo "\n" . avia_favicon( avia_get_option( 'favicon' ) ) . "\n";
	}

	/*favicon in front and backend*/
	add_action( 'wp_head', 'avia_add_favicon' );
	add_action( 'admin_head', 'avia_add_favicon' );
}


if( ! function_exists( 'avia_wp_cpt_request_redirect_fix' ) )
{
	/**
	 * WP core hack see https://core.trac.wordpress.org/ticket/15551
	 *
	 * Paging does not work on single custom post type pages - always a redirect to page 1 by WP
	 *
	 * @since 4.0.6
	 * @param object $request
	 * @return object
	 */
	function avia_wp_cpt_request_redirect_fix( $request )
	{
		$args = array(
					'public'	=>	true,
					'_builtin'	=>	false
				);

		$cpts = get_post_types( $args, 'names', 'and' );

		if( isset( $request->query_vars['post_type'] ) &&
			in_array( $request->query_vars['post_type'], $cpts ) &&
			true === $request->is_singular &&
			- 1 == $request->current_post &&
			true === $request->is_paged
			)
		{
			add_filter( 'redirect_canonical', '__return_false' );
		}

		return $request;
	}

	add_action( 'parse_query', 'avia_wp_cpt_request_redirect_fix' );
}

if( ! function_exists( 'avia_remove_query_strings' ) )
{
	/**
	 * Remove query strings (like version) from static resources in production.
	 *
	 * @since 4.7.4.1
	 * @param string $src
	 * @param string $handle
	 * @return string
	 */
	function avia_remove_query_strings( $src, $handle = '' )
	{
		if( defined( 'WP_DEBUG' ) && WP_DEBUG )
		{
			return $src;
		}

		if( avia_get_option( 'remove_query_string_from_resources', '' ) != 'remove_query_string_from_resources' )
		{
			return $src;
		}

		//	Ignore option for our post css files - we need ver= to invalidate browser cache !!
		if( false !== strpos( $handle, 'avia-single-post-' ) )
		{
			return $src;
		}

		$source = preg_split( "/(&ver|\?ver)/", $src );
		return $source[0];
	}

	if( ! is_admin() )
	{
		add_filter( 'script_loader_src', 'avia_remove_query_strings', 15, 2 );
		add_filter( 'style_loader_src', 'avia_remove_query_strings', 15, 2 );
	}
}


if( ! function_exists( 'avia_maps_key_for_plugins' ) )
{
	/**
	 *
	 * @param string $url
	 * @param string $handle
	 * @return string
	 */
	function avia_maps_key_for_plugins ( $url, $handle )
	{
		$key = get_option( 'gmap_api' );

		if( ! $key )
		{
			return $url;
		}

		if( strpos( $url, 'maps.google.com/maps/api/js' ) !== false || strpos( $url, 'maps.googleapis.com/maps/api/js' ) !== false )
		{
			//	if no key, we can generate a new link with our key
			if( strpos( $url, 'key=' ) === false )
			{
				$url = av_google_maps::api_url( $key );
			}
		}

		return $url;
	}

	add_filter( 'script_loader_src', 'avia_maps_key_for_plugins', 10, 2 );
}


if( ! function_exists( 'avia_print_html5_js_script' ) )
{
	/**
	 * add html5.js script to head section - required for IE compatibility
	 */
	function avia_print_html5_js_script()
	{
		$template_url = get_template_directory_uri();

		$output  = '';
		$output .= '<!--[if lt IE 9]>';
		$output .= '<script src="' . $template_url . '/js/html5shiv.js"></script>';
		$output .= '<![endif]-->';

		echo $output;
	}

	add_action( 'wp_head', 'avia_print_html5_js_script' );
}


if( ! function_exists( 'av_comment_field_order_reset' ) )
{
	/**
	 * Comment form order
	 * Restore comment form order to look like previous versions were comment field is below name/mail/website
	 *
	 * @author Kriesi
	 * @since 4.5
	 * @param array $fields
	 * @return array
	 */
	function av_comment_field_order_reset( $fields )
	{
		$comment_field = $fields['comment'];
		unset( $fields['comment'] );
		$fields['comment'] = $comment_field;
		return $fields;
	}

	add_filter( 'comment_form_fields', 'av_comment_field_order_reset', 10, 1 );
}

