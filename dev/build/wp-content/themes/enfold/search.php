<?php
	if( ! defined( 'ABSPATH' ) ){ die(); }

	global $avia_config;


	/**
	 * get_header is a basic wordpress function, used to retrieve the header.php file in your theme directory.
	 */
	get_header();

	//	allows to customize the layout
	do_action( 'ava_search_after_get_header' );


	$results = avia_which_archive();
	echo avia_title( array( 'title' => $results ) );

	do_action( 'ava_after_main_title' );

	/**
	 * @since 5.6.7
	 * @param string $main_class
	 * @param string $context					file name
	 * @return string
	 */
	$main_class = apply_filters( 'avf_custom_main_classes', 'av-main-' . basename( __FILE__, '.php' ), basename( __FILE__ ) );

	?>

		<div class='container_wrap container_wrap_first main_color <?php avia_layout_class( 'main' ); ?>'>

			<div class='container'>

				<main class='content template-search <?php avia_layout_class( 'content' ); ?> units <?php echo $main_class; ?>' <?php avia_markup_helper( array( 'context' => 'content' ) );?>>

					<div class='page-heading-container clearfix'>
						<section class="search_form_field">
							<?php
							echo '<h4>' . __( 'New Search', 'avia_framework' ) . '</h4>';
							echo '<p>' . __( 'If you are not happy with the results below please do another search', 'avia_framework' ) . '</p>';

							get_search_form();
							echo '<span class="author-extra-border"></span>';
							?>
						</section>
					</div>

					<?php
					if( ! empty( $_GET['s'] ) || have_posts() )
					{
						echo "<h4 class='extra-mini-title widgettitle'>{$results}</h4>";

						/**
						 * @since 5.6
						 * @param string $search_result_layout
						 * @return string							'' | 'grid'
						 */
						if( 'grid' != apply_filters( 'avf_search_result_layout', '' ) )
						{
							/* Run the loop to output the posts.
							* If you want to overload this in a child theme then include a file
							* called loop-search.php and that will be used instead.
							*/
							$more = 0;
							get_template_part( 'includes/loop', 'search' );
						}
						else
						{
							global $wp_query;

/**
 * If you want to change the grid 'items' you also have to change the result items
 * for search query to same value.
 * Put following code to your functions.php and grid items will also use the new value.
 *
function custom_change_posts_per_page( $query )
{
	if( is_admin() || ! $query->is_main_query() )
	{
		return;
	}

	if( is_search() )
	{
        	$query->set( 'posts_per_page', 12 );		// change value to desired amount you want to have for grid items
	}
}

add_filter( 'pre_get_posts', 'custom_change_posts_per_page' );

 *
 */

							$atts = array(
										'type'			=> 'grid',
										'wp_query'		=> $wp_query,
										'items'			=> $wp_query->get( 'posts_per_page', get_option( 'posts_per_page' ) ),
										'columns'		=> 3,
										'class'			=> 'avia-builder-el-no-sibling',
										'paginate'		=> 'yes',
										'use_main_query_pagination'	=> 'yes'
									);

							/**
							 * @since 4.5.5
							 * @param array $atts
							 * @param string $context
							 * @return array
							 */
							$atts = apply_filters( 'avf_post_slider_args', $atts, 'search' );

							$grid = new avia_post_slider( $atts );

							echo '<div class="entry-content-wrapper">' . $grid->html() . '</div>';
						}
					}

					?>

				<!--end content-->
				</main>

				<?php

				//get the sidebar
				$avia_config['currently_viewing'] = 'page';

				get_sidebar();

				?>

			</div><!--end container-->

		</div><!-- close default .container_wrap element -->

<?php
		get_footer();
