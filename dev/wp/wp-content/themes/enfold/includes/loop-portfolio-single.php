<?php
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


global $avia_config, $post_loop_count;

$post_loop_count = 1;
$post_class = 'post-entry-' . avia_get_the_id();

// check if we got posts to display:
if( have_posts() )
{
	while( have_posts() )
	{
		the_post();

		$aria_label = 'aria-label="' . __( 'Portfolio Content for:', 'avia_framework' ) . ' ' . esc_attr( get_the_title() ) . '"';

		/**
		 * @since 6.0.3
		 * @param string $aria_label
		 * @param string $context
		 * @param WP_Post|null $current_post
		 * @return string
		 */
		$aria_label = apply_filters( 'avf_aria_label_for_header', $aria_label, __FILE__, get_post() );

?>
		<article class='post-entry post-entry-type-page <?php echo $post_class; ?>' <?php avia_markup_helper( array( 'context' => 'entry' ) ); ?>>
			<div class="entry-content-wrapper clearfix">
				<header class="entry-content-header" <?php echo $aria_label; ?> >
<?php
					if( '1' != get_post_meta( get_the_ID(), '_avia_hide_featured_image', true ) )
					{
						$thumb = get_the_post_thumbnail( get_the_ID(), $avia_config['size'] );
						if( $thumb )
						{
							echo "<div class='page-thumb'>{$thumb}</div>";
						}
					}

				echo '</header>';

				//display the actual post content
				echo '<div class="entry-content" ' . avia_markup_helper( array( 'context' => 'entry_content', 'echo' => false ) ) . '>';
						the_content( __( 'Read more', 'avia_framework' ) . '<span class="more-link-arrow"></span>' );
				echo '</div>';

				echo '<footer class="entry-footer">';

					$avia_wp_link_pages_args = apply_filters( 'avf_wp_link_pages_args', array(
																		'before'	=> '<nav class="pagination_split_post">' . __( 'Pages:', 'avia_framework' ),
																		'after'		=> '</nav>',
																		'pagelink'	=> '<span>%</span>',
																		'separator'	=> ' ',
																) );
					wp_link_pages( $avia_wp_link_pages_args );

					if( is_single() && 'blog-meta-tag' == avia_get_option( 'blog-meta-tag' ) && has_tag() )
					{
						echo '<span class="blog-tags minor-meta">';
								the_tags( '<strong>' . __( 'Tags:','avia_framework' ) . '</strong><span> ' );
						echo '</span></span>';
					}

				echo '</footer>';
			echo '</div>';

			do_action('ava_after_content', get_the_ID(), 'single-portfolio');

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
	$args = apply_filters( 'avf_customize_heading_settings', $args, 'loop_portfolio::nothing_found', array() );

	$heading = ! empty( $args['heading'] ) ? $args['heading'] : $default_heading;
	$css = ! empty( $args['extra_class'] ) ? $args['extra_class'] : '';

	$aria_label = 'aria-label="' . __( 'No Portfolio Found', 'avia_framework' ) . '"';

	/**
	 * @since 6.0.3
	 * @param string $aria_label
	 * @param string $context
	 * @param array $nothing_found
	 * @return string
	 */
	$aria_label = apply_filters( 'avf_aria_label_for_header', $aria_label, __FILE__, [] );

?>
	<article class="entry">
		<header class="entry-content-header" <?php echo $aria_label; ?> >
			<?php echo "<{$heading} class='post-title entry-title {$css}'>" . __( 'Nothing Found', 'avia_framework' ) . "</{$heading}>"; ?>
		</header>

		<?php get_template_part( 'includes/error404' ); ?>

		<footer class="entry-footer"></footer>
	</article>
<?php

}