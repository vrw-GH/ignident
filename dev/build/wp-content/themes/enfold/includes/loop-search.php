<?php
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

global $avia_config;


// check if we got posts to display:
if( have_posts() )
{
	$first = true;

	$counterclass = '';
	$post_loop_count = 1;
	$page = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
	if( $page > 1 )
	{
		$post_loop_count = ( (int)( $page - 1 ) * (int) get_query_var( 'posts_per_page' ) ) + 1;
	}

	$blog_style = avia_get_option( 'blog_style', 'multi-big' );

	while( have_posts() )
	{
		the_post();

		$the_id = get_the_ID();
		$parity = $post_loop_count % 2 ? 'odd' : 'even';
		$last = count( $wp_query->posts ) == $post_loop_count ? ' post-entry-last ' : '';
		$post_class = "post-entry-{$the_id} post-loop-{$post_loop_count} post-parity-{$parity} {$last} {$blog_style}";
		$post_format = get_post_format() ? get_post_format() : 'standard';

	?>
		<article <?php post_class( "post-entry post-entry-type-{$post_format} {$post_class} " ); avia_markup_helper( array( 'context' => 'entry' ) ); ?>>
			<div class="entry-content-wrapper clearfix <?php echo $post_format; ?>-content">
				<header class="entry-content-header">
<?php
					echo "<span class='search-result-counter {$counterclass}'>{$post_loop_count}</span>";

					$default_heading = 'h2';
					$args = array(
								'heading'		=> $default_heading,
								'extra_class'	=> ''
							);

					/**
					 * @since 4.5.5
					 * @return array
					 */
					$args = apply_filters( 'avf_customize_heading_settings', $args, 'loop_search', array() );

					$heading = ! empty( $args['heading'] ) ? $args['heading'] : $default_heading;
					$css = ! empty( $args['extra_class'] ) ? $args['extra_class'] : '';

					//echo the post title
					$markup = avia_markup_helper(array('context' => 'entry_title','echo'=>false));

					echo "<{$heading} class='post-title entry-title {$css}'><a title='" . the_title_attribute( 'echo=0' ) . "' href='" . get_permalink() . "' {$markup}>" . get_the_title() . "</a></{$heading}>";

					echo '<span class="post-meta-infos">';

						$meta_info = array();

						/**
						 * @since 4.8.8
						 * @param string $hide_meta_only
						 * @param string $context
						 * @return string
						 */
						$meta_seperator = apply_filters( 'avf_post_metadata_seperator', '<span class="text-sep">/</span>', 'loop-search' );

						if( 'blog-meta-date' == avia_get_option( 'blog-meta-date' ) )
						{
							$meta_time  = '<time class="date-container minor-meta updated" ' . avia_markup_helper( array( 'context' => 'entry_time', 'echo' => false ) ) . '>';
							$meta_time .=		get_the_time( get_option( 'date_format' ) );
							$meta_time .= '</time>';

							$meta_info['date'] = $meta_time;
						}

						if( get_post_type() !== 'page' && 'blog-meta-comments' == avia_get_option( 'blog-meta-comments' ) )
						{
							if( get_comments_number() != '0' || comments_open() )
							{
								$meta_comment = '<span class="comment-container minor-meta">';

								ob_start();
								comments_popup_link(
												"0 " . __( 'Comments', 'avia_framework' ),
												"1 " . __( 'Comment' , 'avia_framework' ),
												"% " . __( 'Comments', 'avia_framework' ),
												'comments-link',
												__( 'Comments Disabled', 'avia_framework' )
											);

								$meta_comment .= ob_get_clean();
								$meta_comment .= '</span>';

								$meta_info['comment'] = $meta_comment;
							}
						}

						$taxonomies  = get_object_taxonomies(get_post_type($the_id));
						$cats = '';

						$excluded_taxonomies = array_merge( get_taxonomies( array( 'public' => false ) ), array( 'post_tag', 'post_format' ) );

						/**
						 *
						 * @since ????
						 * @since 4.8.8						added $context
						 * @param array $excluded_taxonomies
						 * @param string $post_type
						 * @param int $the_id
						 * @param string $context
						 * @return array
						 */
						$excluded_taxonomies = apply_filters( 'avf_exclude_taxonomies', $excluded_taxonomies, get_post_type( $the_id ), $the_id, 'loop-search' );

						if( ! empty( $taxonomies ) )
						{
							foreach( $taxonomies as $taxonomy )
							{
								if( ! in_array( $taxonomy, $excluded_taxonomies ) )
								{
									$cats .= get_the_term_list( $the_id, $taxonomy, '', ', ','' ) . ' ';
								}
							}
						}

						if( 'blog-meta-category' == avia_get_option( 'blog-meta-category' ) )
						{
							if( ! empty( $cats ) )
							{
								$meta_cats  = '<span class="blog-categories minor-meta">' . __( 'in', 'avia_framework') . ' ';
								$meta_cats .=	trim( $cats );
								$meta_cats .= '</span>';

								$meta_info['categories'] = $meta_cats;
							}
						}

						/**
						 * Allow to change theme options setting for certain posts
						 *
						 * @since 4.8.8
						 * @param boolean $show_author_meta
						 * @param string $context
						 * @return boolean
						 */
						if( true === apply_filters( 'avf_show_author_meta', 'blog-meta-author' == avia_get_option( 'blog-meta-author' ), 'loop-search' ) )
						{
							$meta_author  = '<span class="blog-author minor-meta">' . __( 'by', 'avia_framework' ) . ' ';
							$meta_author .=		'<span class="entry-author-link" ' . avia_markup_helper( array( 'context' => 'author_name', 'echo' => false ) ) . '>';
							$meta_author .=			'<span class="author">';
							$meta_author .=				'<span class="fn">';
							$meta_author .=					get_the_author_posts_link();
							$meta_author .=				'</span>';
							$meta_author .=			'</span>';
							$meta_author .=		'</span>';
							$meta_author .= '</span>';

							$meta_info['author'] = $meta_author;
						}

						/**
						 * Modify the post metadata array
						 *
						 * @since 4.8.8
						 * @param array $meta_info
						 * @param string $context
						 * @return array
						 */
						$meta_info = apply_filters( 'avf_post_metadata_array', $meta_info, 'loop-search' );

						echo implode( $meta_seperator, $meta_info );

					echo '</span>';
				echo '</header>';

				echo '<div class="entry-content" ' . avia_markup_helper( array( 'context' => 'entry_content','echo' => false ) ) . '>';

					$excerpt = trim( get_the_excerpt() );
					if( ! empty( $excerpt ) )
					{
						the_excerpt();
					}
					else
					{
						$excerpt = strip_shortcodes( get_the_content() );
						$excerpt = apply_filters( 'the_excerpt', $excerpt );
						$excerpt = str_replace( ']]>', ']]&gt;', $excerpt );
						echo $excerpt;
					}

				echo '</div>';
			echo '</div>';

			echo '<footer class="entry-footer"></footer>';

			do_action('ava_after_content', $the_id, 'loop-search' );

		echo '</article><!--end post-entry-->';

		$first = false;
		$post_loop_count++;

		if( $post_loop_count >= 100 )
		{
			$counterclass = 'nowidth';
		}
	}
}
else
{
?>
	<article class="entry entry-content-wrapper clearfix" id='search-fail'>
		<p class="entry-content" <?php avia_markup_helper( array( 'context' => 'entry_content' ) ); ?>>
			<strong><?php _e('Nothing Found', 'avia_framework'); ?></strong><br/>
<?php
				_e( 'Sorry, no posts matched your criteria. Please try another search', 'avia_framework' );
?>
		</p>
		<div class='hr_invisible'></div>
		<section class="search_not_found">
			<p><?php _e( 'You might want to consider some of our suggestions to get better results:', 'avia_framework' ); ?></p>
			<ul>
				<li><?php _e( 'Check your spelling.', 'avia_framework' ); ?></li>
				<li><?php _e( 'Try a similar keyword, for example: tablet instead of laptop.', 'avia_framework' ); ?></li>
				<li><?php _e( 'Try using more than one keyword.', 'avia_framework' ); ?></li>
			</ul>
<?php
		/**
		 * Additional output when nothing found in search
		 *
		 * @since 4.1.2
		 * @added_by günter
		 * @return string			cutom HTML escaped for echo | ''
		 */
		$custom_no_earch_result = apply_filters( 'avf_search_results_pagecontent', '' );
		echo $custom_no_earch_result;


		echo '</section>';
	echo '</article>';

}

echo avia_pagination( '', 'nav' );

