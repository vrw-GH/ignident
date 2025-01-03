<?php
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

global $avia_config, $post_loop_count;


if( empty( $post_loop_count ) )
{
	$post_loop_count = 1;
}

$blog_style = avia_get_option( 'blog_style', 'multi-big' );
$blog_global_style = avia_get_option( 'blog_global_style', '' ); //alt: elegant-blog

// check if we got posts to display:
if( have_posts() )
{
	while( have_posts() )
	{
		the_post();

		/*
		 * get the current post id, the current post class and current post format
		 */

		$current_post = array();
		$current_post['post_loop_count'] = $post_loop_count;
		$current_post['the_id'] = get_the_ID();
		$current_post['parity'] = $post_loop_count % 2 ? 'odd' : 'even';
		$current_post['last'] = count( $wp_query->posts ) == $post_loop_count ? ' post-entry-last ' : '';
		$current_post['post_class'] = "post-entry-{$current_post['the_id']} post-loop-{$post_loop_count} post-parity-{$current_post['parity']} {$current_post['last']} {$blog_style}";
		$current_post['post_format'] = get_post_format() ? get_post_format() : 'standard';
		$current_post['post_layout'] = avia_layout_class( 'main', false );

		/*
		 * retrieve slider, title and content for this post,...
		 */
		$size = strpos( $blog_style, 'big' ) ? ( strpos( $current_post['post_layout'], 'sidebar' ) !== false ) ? 'entry_with_sidebar' : 'entry_without_sidebar' : 'square';

		$current_post['slider'] = get_the_post_thumbnail($current_post['the_id'], $size);
		$current_post['title'] = get_the_title();
		$current_post['content'] = apply_filters( 'avf_loop_author_content', get_the_excerpt() );

		$with_slider = empty( $current_post['slider'] ) ? '' : 'with-slider';


		/*
		 * ...now apply a filter, based on the post type... (filter function is located in includes/helper-post-format.php)
		 */
		$current_post = apply_filters( 'post-format-'.$current_post['post_format'], $current_post );

		/*
		 * ... last apply the default wordpress filters to the content
		 */
		$current_post['content'] = str_replace( ']]>', ']]&gt;', apply_filters( 'the_content', $current_post['content'] ) );

		/*
		 * Now extract the variables so that $current_post['slider'] becomes $slider, $current_post['title'] becomes $title, etc
		 */
		extract( $current_post );

		/*
		 * render the html:
		 */
?>
		<article <?php post_class( "'post-entry post-entry-type-{$post_format} {$post_class} {$with_slider}" ); ?>' <?php avia_markup_helper( array( 'context' => 'entry' ) ); ?>>

			<div class="entry-content-wrapper clearfix <?php echo $post_format; ?>-content">
				<header class="entry-content-header">
<?php

					$content_output  =  '<div class="entry-content" ' . avia_markup_helper( array( 'context' => 'entry_content', 'echo' => false ) ) . '>';
					$content_output .=		wpautop( $content );
					$content_output .=  '</div>';

					$taxonomies = get_object_taxonomies( get_post_type( $the_id ) );
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
					$excluded_taxonomies = apply_filters( 'avf_exclude_taxonomies', $excluded_taxonomies, get_post_type( $the_id ), $the_id, 'loop-author' );

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

					//elegant blog
					if( strpos( $blog_global_style, 'elegant-blog' ) !== false )
					{
						if( ! empty( $cats ) )
						{
							echo '<span class="blog-categories minor-meta">';
							echo	trim( $cats );
							echo '</span>';
							$cats = '';
						}

						echo $title;

						echo '<span class="av-vertical-delimiter"></span>';

						echo $content_output;

						$cats = '';
						$title = '';
						$content_output = '';
					}

					//echo the post title
					echo $title;
?>
					<span class='post-meta-infos'>
<?php
						$meta_info = array();

						/**
						 * @since 4.8.8
						 * @param string $hide_meta_only
						 * @param string $context
						 * @return string
						 */
						$meta_separator = apply_filters( 'avf_post_metadata_seperator', '<span class="text-sep">/</span>', 'loop-author' );

						if( 'blog-meta-date' == avia_get_option( 'blog-meta-date' ) )
						{
							$meta_time  = '<time class="date-container minor-meta updated" ' . avia_markup_helper( array( 'context' => 'entry_time', 'echo' => false ) ) . '>';
							$meta_time .=		get_the_time( get_option( 'date_format' ) );
							$meta_time .= '</time>';

							$meta_info['date'] = $meta_time;
						}

						if( 'blog-meta-comments' == avia_get_option( 'blog-meta-comments' ) )
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
						if( true === apply_filters( 'avf_show_author_meta', 'blog-meta-author' == avia_get_option( 'blog-meta-author' ), 'loop-author' ) )
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
						$meta_info = apply_filters( 'avf_post_metadata_array', $meta_info, 'loop-author' );

						echo implode( $meta_separator, $meta_info );

					echo '</span>';
				echo '</header>';

				// echo the post content
				echo $content_output;

			echo '</div>';
			echo '<footer class="entry-footer"></footer>';

			do_action( 'ava_after_content', $the_id, 'loop-author' );

		echo '</article><!--end post-entry-->';

		$post_loop_count++;
	}
}
else
{
	$default_heading = 'h1';
	$args = array(
				'heading'		=> $default_heading,
				'extra_class'	=> ''
			);

	/**
	 * @since 4.5.5
	 * @return array
	 */
	$args = apply_filters( 'avf_customize_heading_settings', $args, 'loop_author::nothing_found', array() );

	$heading = ! empty( $args['heading'] ) ? $args['heading'] : $default_heading;
	$css = ! empty( $args['extra_class'] ) ? $args['extra_class'] : '';
?>

	<article class="entry">
		<header class="entry-content-header">
			<?php echo "<{$heading} class='post-title entry-title {$css}'>" . __( 'Nothing Found', 'avia_framework' ) . "</{$heading}>"; ?>
		</header>

		<p class="entry-content" <?php avia_markup_helper( array( 'context' => 'entry_content' ) ); ?>><?php _e( 'Sorry, no posts matched your criteria', 'avia_framework' ); ?></p>

		<footer class="entry-footer"></footer>
	</article>

<?php
}

if( ! isset( $avia_config['remove_pagination'] ) )
{
	echo avia_pagination( '', 'nav' );
	// paginate_links(); posts_nav_link(); next_posts_link(); previous_posts_link();
}
