<?php
	if( ! defined( 'ABSPATH' ) ){ die(); }

	global $avia_config, $more;

	/**
	 * get_header is a basic wordpress function, used to retrieve the header.php file in your theme directory.
	 */
	get_header();


	$header_settings = avia_header_setting();
	$no_title = $header_settings['header_title_bar'] == 'hidden_title_bar' || $header_settings['header_title_bar'] == 'breadcrumbs_only';


	echo avia_title( array( 'title' => avia_which_archive() ) );

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

			<div class='container template-blog '>

				<main class='content <?php avia_layout_class( 'content' ); ?> units <?php echo $main_class; ?>' <?php avia_markup_helper( array( 'context' => 'content' ) );?>>

					<div class="category-term-description">
						<?php echo term_description(); ?>
					</div>

					<?php
					global $wp_query, $posts;

					$backup_query = $wp_query;

					$sorted = array( 'post' => array() );
					$post_type_obj = array();

					foreach( $posts as $post )
					{
						$sorted[ $post->post_type ][] = $post;
						if( empty( $post_type_obj[ $post->post_type] ) )
						{
							$post_type_obj[ $post->post_type ] = get_post_type_object( $post->post_type );
						}
					}

					$avia_config['blog_style'] = apply_filters( 'avf_blog_style', avia_get_option( 'blog_style', 'multi-big' ), 'tag' );

					$default_heading = 'h3';
					$args = array(
								'heading'		=> $default_heading,
								'extra_class'	=> ''
							);

					/**
					 * @since 4.5.5
					 * @return array
					 */
					$args = apply_filters( 'avf_customize_heading_settings', $args, 'tag', array() );

					$heading = ! empty( $args['heading'] ) ? $args['heading'] : $default_heading;
					$css = ! empty( $args['extra_class'] ) ? $args['extra_class'] : '';

					if( $avia_config['blog_style'] == 'blog-grid' )
					{
						$output = '';
						$post_ids = array();
						foreach( $posts as $post )
						{
							$post_ids[] = $post->ID;
						}

						if( ! empty( $post_ids ) )
						{
							echo '<div class="entry-content-wrapper">';

							foreach( $sorted as $key => $post_type )
							{
								if( empty( $post_type ) )
								{
									continue;
								}

								if( isset( $post_type_obj[ $key ]->labels->name ) )
								{
									$label = $post_type_obj[ $key ]->labels->name;
									if( $no_title )
									{
										$label = __( 'Tag Archive for:', 'avia_framework' ) . '  <span>' . single_tag_title( '', false ) . '</span>';
									}

									/**
									 * @since ???
									 * @since 4.8.8			added $label, moved $post_type_obj[ $key ]->labels->name as second, $title
									 * @param string $label
									 * @param string $post_type_obj[ $key ]->labels->name
									 * @param string $title
									 * @return string
									 */
									$label = apply_filters( 'avf_tag_label_names',$label, $post_type_obj[ $key ]->labels->name, $title );

									$output .= "<{$heading} class='post-title tag-page-post-type-title {$css}'>{$label}</{$heading}>";
								}
								else
								{
									$output .= '<hr />';
								}

								$atts = array(
											'type'			=> 'grid',
											'items'			=> get_option( 'posts_per_page' ),
											'columns'		=> 3,
											'class'			=> 'avia-builder-el-no-sibling',
											'paginate'		=> 'yes',
											'use_main_query_pagination'	=> 'yes',
											'custom_query'	=> array(
																	'post__in'	=> $post_ids,
																	'post_type'	=> $key
																)
										);

								/**
								 * @since 4.5.5
								 * @return array
								 */
								$atts = apply_filters( 'avf_post_slider_args', $atts, 'tag' );

								$blog = new avia_post_slider( $atts );
								$blog->query_entries();

								$output .= $blog->html();
							}

							echo	$output;
							echo '</div>';
						}
						else
						{
							get_template_part( 'includes/loop', 'index' );
						}
					}
					else
					{
						foreach( $sorted as $key => $post_type )
						{
							if( empty( $post_type ) )
							{
								continue;
							}

							if( isset( $post_type_obj[ $key ]->labels->name ) )
							{
								$label = $post_type_obj[ $key ]->labels->name;
								if( $no_title )
								{
									$label = __( 'Tag Archive for:', 'avia_framework' ) . '  <span>' . single_tag_title( '', false ) . '</span>';
								}

								/**
								 * @since ???
								 * @since 4.8.8			added $label, moved $post_type_obj[ $key ]->labels->name as second, $title
								 * @param string $label
								 * @param string $post_type_obj[ $key ]->labels->name
								 * @param string $title
								 * @return string
								 */
								$label = apply_filters( 'avf_tag_label_names', $label, $post_type_obj[ $key ]->labels->name, $title );

								echo "<{$heading} class='post-title tag-page-post-type-title {$css}'>{$label}</{$heading}>";
							}
							else
							{
								echo '<hr />';
							}

							if( $key == 'portfolio' )
							{
								$args = array_merge( $wp_query->query_vars, array( 'post_type' => $key ) );
								query_posts( $args );

								$grid = new avia_post_grid( array(
												'linking'			=> '',
												'columns'			=> '3',
												'contents'			=> 'title',
												'sort'				=> 'no',
												'paginate'			=> 'yes',
												'set_breadcrumb'	=> false,
											));

								$grid->use_global_query();
								echo $grid->html( '' );
							}
							else
							{
								$args = array_merge( $wp_query->query_vars, array( 'post_type' => $key ) );
								query_posts( $args );

								$more = 0;
								get_template_part( 'includes/loop', 'index' );
							}

							$wp_query = $backup_query;
						}
					}
					?>

				<!--end content-->
				</main>

				<?php

				//get the sidebar
				$avia_config['currently_viewing'] = 'blog';
				get_sidebar();

				?>

			</div><!--end container-->

		</div><!-- close default .container_wrap element -->

<?php
		get_footer();
