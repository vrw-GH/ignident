<?php
namespace aviaFramework\widgets;

use WP_Query;

/**
 * AVIA NEWSBOX
 *
 * Widget that creates a list of latest news entries
 *
 * @package AviaFramework
 * @since ???
 * @since 4.9			Code was moved from class-framework-widgets.php
 * @since 5.6			extended with options to show/hide blog meta data
 */
if( ! defined( 'AVIA_FW' ) ) {  exit( 'No direct script access allowed' );  }

if( ! class_exists( __NAMESPACE__ . '\avia_newsbox', false ) )
{
	class avia_newsbox extends \aviaFramework\widgets\base\Avia_Widget
	{
		/**
		 *
		 * @var string
		 */
		protected $avia_term;

		/**
		 *
		 * @var string
		 */
		protected $avia_post_type;

		/**
		 *
		 * @var string
		 */
		protected $avia_new_query;


		/**
		 * @since 4.9						added parameters $id_base, ... $control_options
		 * @param string $id_base
		 * @param string $name
		 * @param array $widget_options
		 * @param array $control_options
		 */
		public function __construct( $id_base = '', $name = '', $widget_options = array(), $control_options = array() )
		{
			if( empty( $id_base ) )
			{
				$id_base = 'newsbox';
			}

			if( empty( $name ) )
			{
				$name = THEMENAME . ' ' . __( 'Latest News', 'avia_framework' );
			}

			if( empty( $widget_options ) )
			{
				$widget_options = array(
								'classname'				=> 'newsbox',
								'description'			=> __( 'A Sidebar widget to display latest post entries in your sidebar.', 'avia_framework' ),
								'show_instance_in_rest'	=> true,
								'customize_selective_refresh' => false
							);
			}

			parent::__construct( $id_base, $name, $widget_options, $control_options );

			$this->defaults = array(
								'title'			=> '',
								'count'			=> '',
								'cat'			=> '',
								'excerpt'		=> '',
								'show_time'		=> 1,
								'show_author'	=> 0,
								'show_cat'		=> 0,
								'use_options'	=> 0

							);

			$this->avia_term = '';
			$this->avia_post_type = '';
			$this->avia_new_query = '';
		}

		/**
		 * Output the widget in frontend
		 *
		 * @since 4.9
		 * @param array $args
		 * @param array $instance
		 */
		public function widget( $args, $instance )
		{
			$instance = $this->parse_args_instance( $instance );

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


			extract( $args, EXTR_SKIP );

			echo $before_widget;


			$title = empty( $instance['title'] ) ? '' : apply_filters( 'widget_title', $instance['title'] );
			$count = empty( $instance['count'] ) ? '' : $instance['count'];
			$cat = empty( $instance['cat'] ) ? '' : $instance['cat'];
			$excerpt = empty( $instance['excerpt'] ) ? '' : $instance['excerpt'];

			$show_time = ! empty( $instance['show_time'] ) ? (bool) $instance['show_time'] : false;
			$show_author = ! empty( $instance['show_author'] ) ? (bool) $instance['show_author'] : false;
			$show_cat = ! empty( $instance['show_cat'] ) ? (bool) $instance['show_cat'] : false;
			$use_options = ! empty( $instance['use_options'] ) ? (bool) $instance['use_options'] : false;

			/**
			 * @since 4.5.4
			 * @param string $image_size
			 * @param array $args
			 * @param array $instance
			 * @return string
			 */
			$image_size = apply_filters( 'avf_newsbox_image_size', 'widget', $args, $instance );

			if( ! empty( $title ) )
			{
				echo $before_title . $title . $after_title;
			}

			if( empty( $this->avia_term ) )
			{
				$additional_loop = new WP_Query( "cat={$cat}&posts_per_page={$count}" );
			}
			else
			{
				$catarray = explode( ',', $cat );

				if( empty( $catarray[0] ) )
				{
					$new_query = array(
									'posts_per_page'	=> $count,
									'post_type'			=> $this->avia_post_type
								);
				}
				else
				{
					if( $this->avia_new_query )
					{
						$new_query = $this->avia_new_query;
					}
					else
					{
						$new_query = array(
										'posts_per_page'	=> $count,
										'tax_query'			=> array(
																array(
																	'taxonomy'	=> $this->avia_term,
																	'field'		=> 'id',
																	'terms'		=> explode( ',', $cat ),
																	'operator'	=> 'IN'
																)
															)
														);
					}
				}

				$additional_loop = new WP_Query( $new_query );
			}

			if( $additional_loop->have_posts() )
			{
				echo '<ul class="news-wrap image_size_' . $image_size . '">';

				while( $additional_loop->have_posts() )
				{
					$additional_loop->the_post();

					$format = '';

					if( empty( $this->avia_post_type ) )
					{
						$format = $this->avia_post_type;
					}

					if( empty( $format ) )
					{
						$format = get_post_format();
					}

					if( empty( $format ) )
					{
						$format = 'standard';
					}

					$the_id = get_the_ID();
					$link = get_post_meta( $the_id , '_portfolio_custom_link', true ) != '' ? get_post_meta( $the_id ,'_portfolio_custom_link_url', true ) : get_permalink();

					echo '<li class="news-content post-format-' . $format . '">';

					//check for preview images:
					$image = '';

					if( ! current_theme_supports( 'force-post-thumbnails-in-widget' ) )
					{
						$slides = avia_post_meta( get_the_ID(), 'slideshow', true );

						if( $slides != '' && ! empty( $slides[0]['slideshow_image'] ) )
						{
							$image = avia_image_by_id( $slides[0]['slideshow_image'], $image_size, 'image' );
						}
					}

					if( current_theme_supports( 'post-thumbnails' ) && ! $image )
					{
						$image = get_the_post_thumbnail( $the_id, $image_size );
					}

					$nothumb = ( ! $image ) ? 'no-news-thumb' : '';

					/**
					 * Filter time format for display
					 *
					 * @param string $time_format
					 * @param string $context
					 * @return string
					 */
					$time_format = apply_filters( 'avia_widget_time', get_option( 'date_format' ) . ' - ' . get_option( 'time_format' ), 'avia_newsbox' );

					$link_attr = 'title="' . __( 'Read:', 'avia_framework' ) . ' ' . get_the_title() . '" href="' . $link . '"';

					echo '<div class="news-link">';

					echo	"<a class='news-thumb {$nothumb}' {$link_attr}>";
					echo		$image;
					echo	'</a>';

					echo	'<div class="news-headline">';
					echo		"<a class='news-title' {$link_attr}>" . get_the_title() . '</a>';

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
					$query_term = ! empty( $this->avia_term ) ? $this->avia_term : 'category';

					$terms = get_the_terms( $the_id, $query_term );

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

			echo $after_widget;
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

			$instance['title'] = strip_tags( $new_instance['title'] );
			$instance['count'] = strip_tags( $new_instance['count'] );
			$instance['excerpt'] = strip_tags( $new_instance['excerpt'] );

			if( ! empty( $new_instance['cat'] ) )
			{
				$instance['cat'] = is_array( $new_instance['cat'] ) ? implode( ',', $new_instance['cat'] ) : strip_tags( $new_instance['cat'] );
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

			$title = strip_tags( $instance['title'] );
			$count = strip_tags( $instance['count'] );
			$excerpt = strip_tags( $instance['excerpt'] );
			$show_time = isset( $instance['show_time'] ) ? (bool) $instance['show_time'] : true;
			$show_author = isset( $instance['show_author'] ) ? (bool) $instance['show_author'] : true;
			$show_cat = isset( $instance['show_cat'] ) ? (bool) $instance['show_cat'] : true;
			$use_options = isset( $instance['use_options'] ) ? (bool) $instance['use_options'] : true;

			$elementCat = array(
						'name'		=> __( 'Which categories should be used for the portfolio?', 'avia_framework' ),
						'desc'		=> __( 'You can select multiple categories here', 'avia_framework' ),
						'id'		=> $this->get_field_name( 'cat' ) . '[]',
						'type'		=> 'select',
						'std'		=> strip_tags( $instance['cat'] ),
						'class'		=> '',
						'multiple'	=> 6,
						'subtype'	=> 'cat'
					);

			//check if a different taxonomy than the default is set
			if( ! empty( $this->avia_term ) )
			{
				$elementCat['taxonomy'] = $this->avia_term;
			}

			$html = new \avia_htmlhelper();
	?>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'avia_framework' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></label>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'How many entries do you want to display: ', 'avia_framework' ); ?></label>
				<select class="widefat" id="<?php echo $this->get_field_id( 'count ' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>">
					<?php
					$list = '';
					for ($i = 1; $i <= 20; $i++ )
					{
						$selected = '';
						if( $count == $i )
						{
							$selected = 'selected="selected"';
						}

						$list .= "<option {$selected} value='{$i}'>{$i}</option>";
					}
					$list .= '</select>';
					echo $list;
					?>
			</p>

			<p><label for="<?php echo $this->get_field_id( 'cat' ); ?>"><?php _e( 'Choose the categories you want to display (multiple selection possible):', 'avia_framework' ); ?>
				<?php echo $html->select( $elementCat ); ?>
				</label>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'excerpt' ); ?>"><?php _e( 'Display title or title &amp; excerpt', 'avia_framework' ); ?></label>
				<select class="widefat" id="<?php echo $this->get_field_id( 'excerpt' ); ?>" name="<?php echo $this->get_field_name( 'excerpt' ); ?>">
				<?php
					$list = '';
					$answers = array(
								'show title only'			=>	__( 'Display title only', 'avia_framework' ),
								'display title and excerpt'	=>	__( 'Display title and excerpt', 'avia_framework' )
								);

					foreach ( $answers as $key => $answer )
					{
						$selected = '';
						if( $key == $excerpt )
						{
							$selected = 'selected="selected"';
						}

						$list .= "<option {$selected} value='{$key}'>{$answer}</option>";
					}
					$list .= '</select>';
					echo $list;
				?>
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
	}
}
