<?php
namespace aviaFramework\widgets;

use aviaFramework\widgets\base\Avia_Widget;

/**
 * AVIA COMBO WIDGET
 *
 * Widget that displays your popular posts, recent posts, recent comments and a tagcloud in a tabbed section
 *
 * @package AviaFramework
 * @since ???
 * @since 4.4.2			extended and modified by günter
 * @since 4.9			Code was moved from class-framework-widgets.php
 * @since 5.6			extended with options to show/hide blog meta data
 */
if( ! defined( 'AVIA_FW' ) ) {  exit( 'No direct script access allowed' );  }


if( ! class_exists( __NAMESPACE__ . '\avia_combo_widget', false ) )
{
	class avia_combo_widget extends \aviaFramework\widgets\base\Avia_Widget
	{
		/**
		 * Array of tabs for form and block preview
		 *
		 * @var array
		 */
		protected $form_tabs;

		/**
		 *
		 */
		public function __construct()
		{
			$id_base = 'avia_combo_widget';
			$name = THEMENAME . ' ' . __( 'Combo Widget', 'avia_framework' );

			$widget_options = array(
						'classname'				=> 'avia_combo_widget avia_no_block_preview',
						'description'			=> __( 'A widget that displays your popular posts, recent posts, recent comments and a tagcloud', 'avia_framework' ),
						'show_instance_in_rest' => true,
						'customize_selective_refresh' => false
					);

			parent::__construct( $id_base, $name, $widget_options );

			$this->form_tabs = array(
								0				=> __( 'No content', 'avia_framework' ),
								'popular'		=> __( 'Popular posts', 'avia_framework' ),
								'recent'		=> __( 'Recent posts', 'avia_framework' ),
								'comments'		=> __( 'Newest comments', 'avia_framework' ),
								'tagcloud'		=> __( 'Tag cloud', 'avia_framework' )
							);

			$this->defaults = array(
								'show_popular'		=> 4,
								'show_recent'		=> 4,
								'show_comments'		=> 4,
								'show_tags'			=> 45,
								'tab_1'				=> 'popular',
								'tab_2'				=> 'recent',
								'tab_3'				=> 'comments',
								'tab_4'				=> 'tagcloud',
								'show_time'			=> 1,
								'show_author'		=> 0,
								'show_cat'			=> 0,
								'use_options'		=> 0
							);

			/**
			 * Hook to enable
			 */
			add_filter( 'avf_disable_frontend_assets', array( $this, 'handler_enable_shortcodes' ), 50, 1 );
		}

		/**
		 *
		 * @since 4.4.2
		 */
		public function __destruct()
		{
			parent::__destruct();

			unset( $this->form_tabs );
		}

		/**
		 *
		 * @since 4.4.2
		 * @param array $instance
		 * @return array
		 */
		protected function parse_args_instance( array $instance )
		{
			/**
			 * Backwards comp. only
			 *
			 * @since 4.4.2 'count' was removed
			 */
			$fallback = isset( $instance['count'] ) ? $instance['count'] : false;

			$new_instance = parent::parse_args_instance( $instance );

			if( false !== $fallback )
			{
				$new_instance['show_popular'] = $instance['count'];
				$new_instance['show_recent'] = $instance['count'];
				$new_instance['show_comments'] = $instance['count'];
				unset( $new_instance['count'] );
			}

			return $new_instance;
		}

		/**
		 * Output the widget in frontend
		 *
		 * @param array $args
		 * @param array $instance
		 */
		public function widget( $args, $instance )
		{
			$widget_args = $args;
			$instance = $this->parse_args_instance( $instance );

			if( $this->in_block_editor_preview( $args, $instance ) )
			{
				return;
			}

			/**
			 * Filter $instance values on page basis
			 *
			 * @since 5.6
			 * @param array $instance
			 * @param array $args
			 * @param string $context
			 * @return array
			 */
			$instance = apply_filters( 'avf_widget_front_instance', $instance, $args, get_called_class() );

			extract( $args );

			echo $before_widget;

			$used_tabs = 0;

			for( $tab_nr = 1; $tab_nr < 5; $tab_nr++ )
			{
				$key = 'tab_' . $tab_nr;

				if( empty( $instance[ $key ] ) )
				{
					continue;
				}

				if( ! in_array( $instance[ $key ], array( 'popular', 'recent', 'comments', 'tagcloud' ) ) )
				{
					continue;
				}

				$used_tabs++;
				$add_class = '';
				$add_class2 = '';

				if( 1 == $used_tabs )
				{
					echo '<div class="tabcontainer border_tabs top_tab tab_initial_open tab_initial_open__1">';
					$add_class = ' first_tab active_tab ';
					$add_class2 = 'active_tab_content';
				}

				switch( $instance[ $key ] )
				{
					case 'popular':
							$args = array(
												'posts_per_page'	=> $instance['show_popular'],
												'orderby'			=> 'comment_count',
												'order'				=> 'desc'
											);

							echo '<div class="tab widget_tab_popular' . $add_class . '"><span>' . __( 'Popular', 'avia_framework' ) . '</span></div>';
							echo "<div class='tab_content {$add_class2}'>";
									avia_combo_widget::get_post_list( $args, false, $instance, $widget_args );
							echo '</div>';
							break;
					case 'recent':
							$args = array(
												'posts_per_page'	=> $instance['show_recent'],
												'orderby'			=> 'post_date',
												'order'				=> 'desc'
											);
							echo '<div class="tab widget_tab_recent' . $add_class . '"><span>' . __( 'Recent', 'avia_framework' ) . '</span></div>';
							echo "<div class='tab_content {$add_class2}'>";
									avia_combo_widget::get_post_list( $args, false, $instance, $widget_args );
							echo '</div>';
							break;
					case 'comments':
							$args = array(
												'number'	=> $instance['show_comments'],
												'status'	=> 'approve',
												'order'		=> 'DESC'
											);
							echo '<div class="tab widget_tab_comments' . $add_class . '"><span>' . __( 'Comments', 'avia_framework' ) . '</span></div>';
							echo "<div class='tab_content {$add_class2}'>";
									avia_combo_widget::get_comment_list( $args, $widget_args );
							echo '</div>';
							break;
					case 'tagcloud':
							$args = array(
												'number'	=> $instance['show_tags'],
												'smallest'	=> 12,
												'largest'	=> 12,
												'unit'		=> 'px'
											);
							echo '<div class="tab last_tab widget_tab_tags' . $add_class . '"><span>' . __( 'Tags', 'avia_framework' ) . '</span></div>';
							echo "<div class='tab_content tagcloud {$add_class2}'>";
										wp_tag_cloud( $args );
							echo '</div>';
							break;
				}
			}

			if( $used_tabs > 0 )
			{
				echo '</div>';
			}

			echo $after_widget;
		}

		/**
		 * Callback to output a custom block preview
		 *
		 * @since 4.9
		 * @param array $args
		 * @param array $instance
		 * @return boolean					true if output processed
		 */
		protected function widget_block_preview( array $args, array $instance = array() )
		{
			echo isset( $args['before_widget'] ) ? $args['before_widget'] : '';


			echo '<div class="avia-preview-headline">' . $this->name . '</div>';

			$tabs = array();

			for( $i = 1; $i <= 4; $i++ )
			{
				$key = "tab_{$i}";

				if( isset( $instance[ $key ] ) && isset( $this->form_tabs[ $instance[ $key ] ] ) )
				{
					//	0 for "no content" or fallback
					if( ! empty( $instance[ $key ] ) )
					{
						$tabs[] = $this->form_tabs[ $instance[ $key ] ];
					}
				}
			}

			echo '<div class="avia-preview-info">' . __( 'Tabs:', 'avia_framework' ) . ' ' . implode( ', ', $tabs ) .  '</div>';

			echo '<div class="avia-preview-in-front">' . __( 'Tabs are only rendered in frontend.', 'avia_framework' ) . '</div>';

			echo isset( $args['after_widget'] ) ? $args['after_widget'] : '';

			return true;
		}

		/**
		 * Update widget options
		 *
		 * @param array $new_instance
		 * @param array $old_instance
		 * @return array
		 */
		public function update( $new_instance, $old_instance )
		{
			$instance = $this->parse_args_instance( $old_instance );
			$fields = $this->get_field_names();

			foreach( $new_instance as $key => $value )
			{
				if( in_array( $key, $fields ) )
				{
					$instance[ $key ] = strip_tags( $value );
				}
			}

			$instance['show_time'] = isset( $new_instance['show_time'] ) ? 1 : 0;
			$instance['show_author'] = isset( $new_instance['show_author'] ) ? 1 : 0;
			$instance['show_cat'] = isset( $new_instance['show_cat'] ) ? 1 : 0;
			$instance['use_options'] = isset( $new_instance['use_options'] ) ? 1 : 0;

			return $instance;
		}

		/**
		 * Output the form in backend
		 *
		 * @param array $instance
		 */
		public function form( $instance )
		{
			$instance = $this->parse_args_instance( $instance );

			$show_time = isset( $instance['show_time'] ) ? (bool) $instance['show_time'] : true;
			$show_author = isset( $instance['show_author'] ) ? (bool) $instance['show_author'] : true;
			$show_cat = isset( $instance['show_cat'] ) ? (bool) $instance['show_cat'] : true;
			$use_options = isset( $instance['use_options'] ) ? (bool) $instance['use_options'] : true;


			extract( $instance );

			$tab_content = $this->form_tabs;
	?>
			<p><label for="<?php echo $this->get_field_id( 'show_popular' ); ?>"><?php _e( 'Number of popular posts', 'avia_framework' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'show_popular' ); ?>" name="<?php echo $this->get_field_name( 'show_popular' ); ?>" class="widefat">
	<?php
					echo Avia_Widget::number_options( 1, 30, $show_popular );
	?>
				</select>
			</p>
			<p><label for="<?php echo $this->get_field_id( 'show_recent' ); ?>"><?php _e( 'Number of recent posts', 'avia_framework' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'show_recent' ); ?>" name="<?php echo $this->get_field_name( 'show_recent' ); ?>" class="widefat">
	<?php
					echo Avia_Widget::number_options( 1, 30, $show_recent );
	?>
				</select>
			</p>
			<p><label for="<?php echo $this->get_field_id( 'show_comments' ); ?>"><?php _e( 'Number of newest comments', 'avia_framework' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'show_comments' ); ?>" name="<?php echo $this->get_field_name( 'show_comments' ); ?>" class="widefat">
	<?php
					echo Avia_Widget::number_options( 1, 30, $show_comments );
	?>
				</select>
			</p>
			<p><label for="<?php echo $this->get_field_id( 'show_tags' ); ?>"><?php _e( 'Number of tags for tag cloud', 'avia_framework' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'show_tags' ); ?>" name="<?php echo $this->get_field_name( 'show_tags' ); ?>" class="widefat">
	<?php
					echo Avia_Widget::number_options( 1, 100, $show_tags );
	?>
				</select>
			</p>
			<p><label for="<?php echo $this->get_field_id( 'tab_1' ); ?>"><?php _e( 'Content of first tab', 'avia_framework' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'tab_1' ); ?>" name="<?php echo $this->get_field_name( 'tab_1' ); ?>" class="widefat">
	<?php
					$tab_content_first = $tab_content;
					unset( $tab_content_first[0] );
					echo Avia_Widget::options_from_array( $tab_content_first, $tab_1 );
	?>
				</select>
			</p>
			<p><label for="<?php echo $this->get_field_id( 'tab_2' ); ?>"><?php _e( 'Content of next tab', 'avia_framework' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'tab_2' ); ?>" name="<?php echo $this->get_field_name( 'tab_2' ); ?>" class="widefat">
	<?php
					echo Avia_Widget::options_from_array( $tab_content, $tab_2 );
	?>
				</select>
			</p>
			<p><label for="<?php echo $this->get_field_id( 'tab_3' ); ?>"><?php _e( 'Content of next tab', 'avia_framework' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'tab_3' ); ?>" name="<?php echo $this->get_field_name( 'tab_3' ); ?>" class="widefat">
	<?php
					echo Avia_Widget::options_from_array( $tab_content, $tab_3 );
	?>
				</select>
			</p>
			<p><label for="<?php echo $this->get_field_id( 'tab_4' ); ?>"><?php _e( 'Content of next tab', 'avia_framework' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'tab_4' ); ?>" name="<?php echo $this->get_field_name( 'tab_4' ); ?>" class="widefat">
	<?php
					echo Avia_Widget::options_from_array( $tab_content, $tab_4 );
	?>
				</select>
			</p>
			<p>
				<input class="checkbox" id="<?php echo $this->get_field_id( 'show_time' ); ?>" name="<?php echo $this->get_field_name( 'show_time' ); ?>" type="checkbox" <?php checked( $show_time ); ?> />
				<label for="<?php echo $this->get_field_id( 'show_time' ); ?>"><?php _e( 'Show date and time', 'avia_framework' ); ?></label>
            </br>
				<input class="checkbox" id="<?php echo $this->get_field_id( 'show_author' ); ?>" name="<?php echo $this->get_field_name( 'show_author' ); ?>" type="checkbox" <?php checked( $show_author ); ?> />
				<label for="<?php echo $this->get_field_id( 'show_author' ); ?>"><?php _e( 'Show author', 'avia_framework' ); ?></label>
            </br>
				<input class="checkbox" id="<?php echo $this->get_field_id( 'show_cat' ); ?>" name="<?php echo $this->get_field_name( 'show_cat' ); ?>" type="checkbox" <?php checked( $show_cat ); ?> />
				<label for="<?php echo $this->get_field_id( 'show_cat' ); ?>"><?php _e( 'Show categories', 'avia_framework' ); ?></label>
            </br>
				<input class="checkbox" id="<?php echo $this->get_field_id( 'use_options' ); ?>" name="<?php echo $this->get_field_name( 'use_options' ); ?>" type="checkbox" <?php checked( $use_options ); ?> />
				<label for="<?php echo $this->get_field_id( 'use_options' ); ?>"><?php _e( 'Use Blog Metadata theme options (overrides above settings)', 'avia_framework' ); ?></label>
			</p>
	<?php
		}

		/**
		 * This widget needs tab.css and tab.js to work properly.
		 *
		 * @since 4.4.2
		 * @added_by Günter
		 * @param array $disabled
		 * @return array
		 */
		public function handler_enable_shortcodes( array $disabled )
		{
			$settings = $this->get_settings();

			/**
			 * Search page might lead to no result and in this case we activate this widget manually
			 */
			if( ( count( $settings ) > 0 ) || is_search() )
			{
				unset( $disabled['av_tab_container'] );
			}

			return $disabled;
		}

		/**
		 * Get postlist by query args
		 * (up to 4.4.2 this was function avia_get_post_list( $avia_new_query , $excerpt = false)
		 *
		 * @since 4.4.2
		 * @since 5.6				added $instance
		 * @since 5.6.1				added $widget_args
		 * @added_by Günter
		 * @param array $args
		 * @param boolean $excerpt
		 * @param array $instance
		 * @param array $widget_args
		 */
		static public function get_post_list( array $args, $excerpt = false, array $instance = [], array $widget_args = [] )
		{
			global $avia_config;

			$show_time = ! empty( $instance['show_time'] ) ? (bool) $instance['show_time'] : false;
			$show_author = ! empty( $instance['show_author'] ) ? (bool) $instance['show_author'] : false;
			$show_cat = ! empty( $instance['show_cat'] ) ? (bool) $instance['show_cat'] : false;
			$use_options = ! empty( $instance['use_options'] ) ? (bool) $instance['use_options'] : false;

			/**
			 * @since 4.5.4
			 * @since 5.6.1				added $widget_args, $instance
			 * @param string $image_size
			 * @param array $args
			 * @param array $widget_args
			 * @param array $instance
			 * @return string
			 */
			$image_size = apply_filters( 'avf_combo_box_image_size', 'widget', $args, $widget_args, $instance );

			$additional_loop = new \WP_Query( $args );

			if( $additional_loop->have_posts() )
			{
				echo '<ul class="news-wrap">';

				while ( $additional_loop->have_posts() )
				{
					$additional_loop->the_post();

					$the_id = get_the_ID();
					$format = '';
					if( get_post_type() != 'post' )
					{
						$format = get_post_type();
					}

					if( empty( $format ) )
					{
						$format = get_post_format();
					}
					if( empty( $format ) )
					{
						$format = 'standard';
					}

					echo '<li class="news-content post-format-' . $format . '">';

					//check for preview images:
					$image = '';

					if( ! current_theme_supports( 'force-post-thumbnails-in-widget' ) )
					{
						$slides = avia_post_meta( $the_id, 'slideshow' );

						if( $slides != '' && ! empty( $slides[0]['slideshow_image'] ) )
						{
							$image = avia_image_by_id( $slides[0]['slideshow_image'], $image_size, 'image' );
						}
					}

					if( ! $image && current_theme_supports( 'post-thumbnails' ) )
					{
						$image = get_the_post_thumbnail( $the_id, $image_size );
					}

					$nothumb = ( ! $image) ? 'no-news-thumb' : '';

					/**
					 * Filter time format for display
					 *
					 * @param string $time_format
					 * @param string $context
					 * @return string
					 */
					$time_format = apply_filters( 'avia_widget_time', get_option( 'date_format' ) . ' - ' . get_option( 'time_format' ), 'avia_get_post_list' );

					$link_attr = 'title="' . __( 'Read:', 'avia_framework' ) . ' ' . get_the_title() . '" href="' . get_permalink() . '"';

					echo '<div class="news-link">';

					echo	"<a class='news-thumb {$nothumb}' {$link_attr}>";
					echo		$image;
					echo	'</a>';


					echo	'<div class="news-headline">';
					echo		"<a class='news-title' {$link_attr}>" . avia_backend_truncate( get_the_title(), 55, ' ' ) . '</a>';

					if( $time_format )
					{
						if( $use_options && 'blog-meta-date' == avia_get_option( 'blog-meta-date' ) || ! $use_options && $show_time )
						{
							echo '<span class="news-time">' . get_the_time( $time_format ) . '</span>';
						}
					}

					if( $use_options && 'blog-meta-author' == avia_get_option( 'blog-meta-author' ) || ! $use_options && $show_author )
					{
						echo	'<span class="news-time news-author">' . __( 'by:', 'avia_framework' ) . ' ' . get_the_author_posts_link() . '</span>';
					}

					$names = [];

					$terms = get_the_terms( $the_id, 'category' );

					if( is_array( $terms ) && count( $terms ) > 0 )
					{
						$names = [];

						foreach ( $terms as $term )
						{
							$term_link = get_term_link( $term );

							if ( is_wp_error( $term_link ) )
							{
								$names[] = $term->name;
							}
							else
							{
								$cat_title = sprintf( __( 'Read more in: %s', 'avia_framework' ), $term->name );
								$names[] = '<a title="' . $cat_title . '" href="' . esc_url( $term_link ) . '">' . $term->name . '</a>';
							}
						}
					}

					if( ! empty( $names ) )
					{
						if( $use_options && 'blog-meta-category' == avia_get_option( 'blog-meta-category' ) || ! $use_options && $show_cat )
						{
							echo '<span class="news-time news-cats">' . __( 'in:', 'avia_framework' ) . ' ' . implode( ', ', $names ) . '</span>';
						}
					}

					echo	'</div>';
					echo '</div>';

					if( 'display title and excerpt' == $excerpt )
					{
						echo '<div class="news-excerpt">';
								the_excerpt();
						echo '</div>';
					}

					echo '</li>';
				}

				echo '</ul>';

				wp_reset_postdata();
			}
		}

		/**
		 * Get commentlist by query args
		 * (up to 4.4.2 this was function avia_get_comment_list( $avia_new_query )
		 *
		 * @since 4.4.2
		 * @since 5.6.1					added $widget_args
		 * @added_by Günter
		 * @param array $args
		 * @param array $widget_args
		 */
		static public function get_comment_list( array $args, array $widget_args )
		{
			/**
			 * Filter time format for display
			 *
			 * @param string $time_format
			 * @param string $context
			 * @return string
			 */
			$time_format = apply_filters( 'avia_widget_time', get_option( 'date_format' ) . ' - ' . get_option( 'time_format' ), 'avia_get_comment_list' );

			$comments = get_comments( $args );

			if( ! empty( $comments ) )
			{
				echo '<ul class="news-wrap">';

				foreach( $comments as $comment )
				{
					if( $comment->comment_author != 'ActionScheduler' )
					{
						$gravatar_alt = esc_html( $comment->comment_author );

						echo '<li class="news-content">';
						echo	'<a class="news-link" title="' . get_the_title( $comment->comment_post_ID ) . '" href="' . get_comment_link($comment) . '">';
						echo		'<span class="news-thumb">';
						echo			get_avatar( $comment, '48', '', $gravatar_alt );
						echo		'</span>';
						echo		'<strong class="news-headline">' . avia_backend_truncate( $comment->comment_content, 55, ' ' );

						if( $time_format )
						{
							echo		'<span class="news-time">' . get_comment_date( $time_format, $comment->comment_ID ) . ' ' . __( 'by', 'avia_framework' ) . ' ' . $comment->comment_author . '</span>';
						}

						echo		'</strong>';
						echo	'</a>';
						echo '</li>';
					}
				}

				echo '</ul>';
				wp_reset_postdata();
			}
		}
	}
}

