<?php
if( ! defined( 'ABSPATH' ) )	{	exit;	}	// Exit if accessed directly

/*
 * Rank Math SEO Integration
 * =========================
 *
 * @since 5.0
 */

if( ! defined('RANK_MATH_VERSION') && ! class_exists( 'RankMath', false ) )
{
	return;
}

if( ! function_exists('avia_rank_math_register_assets' ) )
{
	/**
	 *
	 * @since 5.0
	 */
	function avia_rank_math_register_assets()
	{
		$screen = get_current_screen();
		$vn = avia_get_theme_version();
		$min_js = avia_minify_extension('js');

		if( is_null( $screen ) || $screen->post_type == '')
		{
			return;
		}

		wp_enqueue_script(
			'avia_analytics_js',
			AVIA_BASE_URL . "config-templatebuilder/avia-template-builder/assets/js/avia-analytics{$min_js}.js",
			['avia_builder_js'],
			$vn,
			true
		);

		wp_enqueue_script(
			'avia_rank_math_js',
			AVIA_BASE_URL . "config-rank-math/rank-math-mod{$min_js}.js",
			['wp-hooks', 'wp-shortcode', 'rank-math-analyzer', 'avia_analytics_js'],
			$vn,
			true
		);
	}

	if( is_admin() )
	{
		add_action( 'admin_enqueue_scripts', 'avia_rank_math_register_assets' );
	}
}

if( ! function_exists( 'avia_rank_math_register_toc_widget' ) )
{
	/**
	 * Notifies Rank Math that the theme contains a TOC widget or element.
	 * https://rankmath.com/kb/table-of-contents-not-detected/
	 *
	 * @since 5.0
	 * @param array $toc_plugins
	 * @return array
	 */
	function avia_rank_math_register_toc_widget( $toc_plugins )
	{
		$toc_plugins['seo-by-rank-math/rank-math.php'] = 'Rank Math';

		return $toc_plugins;
	}

	add_filter( 'rank_math/researches/toc_plugins', 'avia_rank_math_register_toc_widget', 10, 1 );
}

if( ! function_exists( 'avia_rank_math_extract_shortcodes_attachment_ids' ) )
{
	/**
	 *
	 * Enable Rank Math to index ALB elements containing images.
	 * https://kriesi.at/support/topic/images-not-exist-in-rankmath-sitemap/
	 *
	 * @since 5.7
	 * @param array $elements
	 * @param string $content
	 * @return array
	 */
	function avia_rank_math_extract_shortcodes_attachment_ids( $elements, $content )
	{
		$container = array();

		if( ! empty( $elements ) )
		{
			foreach( $elements as $key => $element )
			{
				$shortcodes = array();
				preg_match_all( $element['pattern'], $content, $shortcodes );

				foreach( $shortcodes[0] as $shortcode )
				{
					switch( $element['source'] )
					{
						case 'ids':
							$src = '/ids=\\\'(\d+(,\d+)*)\\\'/';
							break;
						case 'attachment':
							$src = '/attachment=\\\'(\d+)\\\'/';
							break;
						case 'sid':
							$src = '/id=\\\'(\d+)\\\'/sim';
							break;
						case 'tab':
							$src = '/tab_image=\\\'(\d+)\\\'/sim';
							break;
						case 'front':
							$src = '/front_bg_image=\\\'(\d+)\\\'/sim';
							break;
						case 'back':
							$src = '/back_bg_image=\\\'(\d+)\\\'/sim';
							break;
						default:
							continue 2;
					}

					$id = array();
					preg_match_all( $src, $shortcode, $id );

					foreach( $id[1] as $key => $value )
					{
						if( empty( $value ) )
						{
							continue;
						}

						$img_ids = explode( ',', $value );

						$container = array_merge( $container, $img_ids );
					}
				}
			}
		}

		return array_unique( $container, SORT_NUMERIC );
	}
}

if( ! function_exists( 'avia_rank_math_sitemap_url_images' ) )
{
	/**
	 *
	 * Filter images to be included for the post in XML sitemap.
	 *
	 * @param array $images  Array of image items.
	 * @param int   $post_id ID of the post.
	 */
	function avia_rank_math_sitemap_url_images( $images, $post_id )
	{
		$is_alb = get_post_meta( $post_id, '_aviaLayoutBuilder_active', true );

		if( $is_alb !== 'active' )
		{
			return $images;
		}

		$post = get_post($post_id);

		if( $post instanceof WP_Post)
		{
			$content = $post->post_content;

			$posts_elements = array(
								'av_masonry_entries',
								'av_blog',
								'av_postslider',
								'av_magazine',
								'av_portfolio',
								'av_postslider'
							);

			$posts_images = array();

			foreach( $posts_elements as $post_element )
			{
				$shortcode_contents = avia_rank_math_get_shortcodes_by_name( $post_element, $content );

				foreach( $shortcode_contents as $shortcode_content )
				{
					if( $shortcode_content)
					{
						$thumbnails = avia_rank_math_process_shortcode_for_thumbnails( $post_element, $shortcode_content );

						if( is_array( $thumbnails ) && ! empty( $thumbnails ) )
						{
							$posts_images = array_merge( $posts_images, $thumbnails );
						}
					}
				}
			}

			$image_elements = array(
								'av_image_src'				=> array( 'pattern' => '/\[av_image [^]]*]/', 'source' => 'src' ),
								'av_image_attachment'		=> array( 'pattern' => '/\[av_image [^]]*]/', 'source' => 'attachment' ),
								'av_partner_logo'			=> array( 'pattern' => '/\[av_partner_logo [^]]*]/', 'source' => 'sid' ),
								'av_masonry'				=> array( 'source' => 'ids' ),
								'av_gallery'				=> array( 'source' => 'ids' ),
								'av_horizontal'				=> array( 'source' => 'ids' ),
								'av_accordion'				=> array( 'source' => 'sid' ),
								'av_slideshow'				=> array( 'source' => 'sid' ),
								'av_slideshow_full'			=> array( 'source' => 'sid' ),
								'av_slideshow_fullscreen'	=> array( 'source' => 'sid' ),
								'av_color_section'			=> array( 'source' => 'attachment' ),
								'av_team_member'			=> array( 'source' => 'attachment' ),
								'av_image_hotspot'			=> array( 'source' => 'attachment' ),
								'av_tab_sub_section'		=> array( 'source' => 'attachment' ),
								'av_tab_sub_section_tab'	=> array( 'source' => 'tab' ),
								'av_icongrid_item_front'	=> array( 'source' => 'front' ),
								'av_icongrid_item_back'		=> array( 'source' => 'back' )
							);

			foreach( $image_elements as $key => $image )
			{
				$pattern = '';

				if( array_key_exists( 'pattern', $image ) )
				{
					$pattern = $image['pattern'];
				}
				else
				{
					$pattern = '/\\[' . preg_quote($key) . '(.+?)?\\](?:(.+?)?\\[\\/' . preg_quote($key) . '\\])?/sim';
				}

				$elements[str_replace('av_', '', $key)] = array(
					'pattern' => $pattern,
					'source' => $image['source']
				);
			}

			$column_elements = array(
									'av_one_full',
									'av_one_half',
									'av_one_third',
									'av_one_fourth',
									'av_one_fifth',
									'av_two_third',
									'av_three_fourth',
									'av_two_fifth',
									'av_three_fifth',
									'av_four_fifth',
									'av_cell_one_full',
									'av_cell_one_half',
									'av_cell_one_third',
									'av_cell_one_fourth',
									'av_cell_one_fifth',
									'av_cell_two_third',
									'av_cell_three_fourth',
									'av_cell_two_fifth',
									'av_cell_three_fifth',
									'av_cell_four_fifth'
								);

			foreach( $column_elements as $column )
			{
				$pattern = '/\\[' . preg_quote($column) . '(.+?)?\\](?:(.+?)?\\[\\/' . preg_quote($column) . '\\])?/sim';

				$elements[ str_replace( 'av_', '', $column ) ] = array(
														'pattern'	=> $pattern,
														'source'	=> 'attachment'
													);
			}

			/**
			 *
			 * @param array $elements
			 * @param int $post_id
			 * @return array
			 */
			$elements = apply_filters( 'avf_add_elements_rank_math_sitemap', $elements, $post_id );

			$ids = avia_rank_math_extract_shortcodes_attachment_ids( $elements, $content );

			foreach( $ids as $id )
			{
				$images[] = array(
								'src'	=> wp_get_attachment_url($id),
								'title'	=> get_the_title($id),
								'alt'	=> get_post_meta( $id, '_wp_attachment_image_alt', true )
							);
			}
		}

		return array_merge( $images, $posts_images );
	}

	add_filter( 'rank_math/sitemap/urlimages', 'avia_rank_math_sitemap_url_images', 10, 2 );
}

if( ! function_exists( 'avia_rank_math_get_shortcodes_by_name' ) )
{
	/**
	 *
	 * @since 5.7
	 *
	 * @param string $shortcode_name
	 * @param string $content
	 * @return bool|string
	 */
	function avia_rank_math_get_shortcodes_by_name( $shortcode_name, $content )
	{
		$matches = array();

		preg_match_all( "/\[{$shortcode_name}(.*?)\]/", $content, $matches );

		if( isset($matches[0] ) )
		{
			return $matches[0];
		}

		return false;
	}
}

if( ! function_exists( 'avia_rank_math_process_shortcode_for_thumbnails' ) )
{
	/**
	 *
	 * @since 5.7
	 *
	 * @param string $shortcode_name
	 * @param string $shortcode_content
	 * @return array
	 */
	function avia_rank_math_process_shortcode_for_thumbnails( $shortcode_name, $shortcode_content )
	{
		$thumbnails = array();
		$blog_type = '';
		$shortcode_content = stripslashes($shortcode_content);
		$term_ids = '';
		$count = 10;
		$blog_count_matches = array();
		$blog_type_matches = array();
		$term_matches = array();
		$taxonomy_matches = array();

		preg_match( '/items=["\'](\d+)["\']/', $shortcode_content, $blog_count_matches );
		preg_match( '/blog_type=["\']([^"\']+)["\']/', $shortcode_content, $blog_type_matches );

		if( isset( $blog_count_matches[1] ) )
		{
			$count = $blog_count_matches[1];
		}

		if( isset($blog_type_matches[1]))
		{
			$blog_type = $blog_type_matches[1];
		}

		if( $blog_type === 'posts' || $shortcode_name == 'av_portfolio')
		{
			preg_match( '/categories=\'([\d,]+)\'/', $shortcode_content, $term_matches );
			$taxonomy = $shortcode_name == 'av_portfolio' ? 'portfolio_entries' : 'category';
		}

		if( $blog_type === 'taxonomy' || ( empty( $blog_type ) && in_array( $shortcode_name, array('av_magazine', 'av_postslider', 'av_masonry_entries' ) ) ) )
		{
			preg_match('/link=[\'"]([^\'"]+)[\'"]/', $shortcode_content, $taxonomy_matches);

			if( isset( $taxonomy_matches[1] ) )
			{
				$taxonomy_parts = explode(',', $taxonomy_matches[1]);
				$taxonomy = isset($taxonomy_parts[0]) ? $taxonomy_parts[0] : '';

				preg_match( '/(\d+(,\d+)*)/', $taxonomy_matches[1], $term_matches );
			}
		}

		$query_args = array(
						'post_type'			=> array( 'portfolio', 'post', 'product' ),
						'posts_per_page'	=> $count
					);

		if( isset( $term_matches[1] ) && $taxonomy )
		{
			$term_ids = explode( ',', $term_matches[1] );
		}

		$tax_query = array(
				'taxonomy'	=> $taxonomy,
				'field'		=> 'term_id'
			);

		if( ! empty( $term_ids ))
		{
			$tax_query['terms'] = $term_ids;
		}
		else
		{
			$terms = get_terms(
				array(
					'taxonomy'	=> $taxonomy,
					'fields'	=> 'ids'
				)
			);

			if( ! is_wp_error( $terms ) && ! empty( $terms ) )
			{
				$tax_query['terms'] = $terms;
			}
		}

		$query_args['tax_query'] = array( $tax_query );

		$query = new WP_Query($query_args);

		if( $query->have_posts())
		{
			while( $query->have_posts() )
			{
				$query->the_post();
				$url = get_the_post_thumbnail_url( get_the_ID() );
				$title = get_the_title( get_the_ID() );
				$alt = get_post_meta( get_the_ID(), '_wp_attachment_image_alt', true );

				$thumbnail = array(
					'src'		=> $url,
					'title'		=> $title,
					'alt'		=> $alt
				);

				if( $thumbnail )
				{
					$thumbnails[] = $thumbnail;
				}
			}

			wp_reset_postdata();
		}

		return $thumbnails;
	}
}
