<?php
namespace aviaFramework\widgets;

use \WP_Error;
use \AviaHelper;

/**
 * AVIA INSTAGRAM WIDGET
 *
 * Widget that displays your latest Instagram Photos
 *
 * Modified vesion:
 * ================
 *
 * Adds a background caching of images on own server to avoid to access instagram to display the images
 *
 * @package AviaFramework
 * @since ???
 * @since 4.3.1			extended, improved and modified by günter
 * @since 4.9			Code was moved from class-framework-widgets.php
 */
if( ! defined( 'AVIA_FW' ) ) {  exit( 'No direct script access allowed' );  }


if( ! class_exists( __NAMESPACE__ . '\avia_instagram_widget', false ) )
{
	class avia_instagram_widget extends \aviaFramework\widgets\base\Avia_Widget
	{
		/**
		 *
		 * @since 4.3.1
		 * @var array
		 */
		protected $upload_folders;

		/**
		 * Stores the expire time for cached images in seconds.
		 * Do not make intervall too short to avoid unnecessary requests.
		 * Also make it large enough to allow a complete update of all instances in that period.
		 *
		 * @since 4.3.1
		 * @var int
		 */
		protected $expire_time;

		/**
		 *
		 * @since 4.3.1
		 * @var boolean
		 */
		protected $activate_cron;

		/**
		 * Holds all caching info for each widget instance.
		 *
		 * @since 4.3.1
		 * @var array
		 */
		protected $cache;

		/**
		 *
		 * @since 4.3.1
		 * @var array
		 */
		protected $cached_file_sizes;

		/**
		 *
		 */
		public function __construct()
		{
			$id_base = 'avia-instagram-feed';
			$name = THEMENAME . ' ' . __( 'Instagram', 'avia_framework' );

			$widget_options = array(
							'classname'				=> 'avia-instagram-feed avia_no_block_preview',
							'description'			=> __( 'Displays your latest Instagram photos', 'avia_framework' ),
							'show_instance_in_rest' => true,
							'customize_selective_refresh' => false
						);

			parent::__construct( $id_base, $name, $widget_options );

			$this->defaults = array(
								'title'			=> __( 'Instagram', 'avia_framework' ),
								'username'		=> '',
								'cache'			=> apply_filters( 'avf_instagram_default_cache_location', '' ),		//	'' | 'server'
								'number'		=> 9,
								'columns'		=> 3,
								'size'			=> 'thumbnail',
								'target'		=> 'lightbox' ,
								'link'			=> __( 'Follow Me!', 'avia_framework' ),
								'avia_key'		=> ''
							);

			$this->upload_folders = wp_upload_dir();

			if( is_ssl() )
			{
				$this->upload_folders['baseurl'] = str_replace( 'http://', 'https://', $this->upload_folders['baseurl'] );
			}

			$folder = apply_filters( 'avf_instagram_cache_folder_name', 'avia_instagram_cache' );

			$this->upload_folders['instagram_dir'] = trailingslashit( trailingslashit( $this->upload_folders['basedir'] ) . $folder );
			$this->upload_folders['instagram_url'] = trailingslashit( trailingslashit( $this->upload_folders['baseurl'] ) . $folder );

			$this->expire_time = HOUR_IN_SECONDS * 2;

			$this->expire_time = apply_filters_deprecated( 'null_instagram_cache_time', array( $this->expire_time ), '4.3.1', 'avf_instagram_file_cache_time', __( 'Adding possible file caching on server might need a longer period of time to invalidate cache.', 'avia_framework' ) );
			$this->expire_time = apply_filters( 'avf_instagram_file_cache_time', $this->expire_time );

			$this->activate_cron = ! ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON );
			$this->activate_cron = apply_filters( 'avf_instagram_activate_cron', $this->activate_cron );

			$this->cache = $this->get_cache();

			$this->cached_file_sizes = array( 'thumbnail', 'small', 'large', 'original' );

			/**
			 * WP Cron job events
			 */
			if( $this->activate_cron )
			{
				add_action( 'av_instagram_scheduled_filecheck', array( $this, 'handler_scheduled_filecheck' ), 10 );
			}

			/**
			 * Makes sure to keep cron job alive as fallback
			 */
			if( is_admin() )
			{
				add_action( 'admin_init', array( $this, 'handler_init_filecheck' ), 99999 );
				add_action( 'delete_widget', array( $this, 'handler_delete_widget' ), 10, 3 );
			}
			else
			{
				add_action( 'init', array( $this, 'handler_init_filecheck' ), 99999 );
			}

		}

		/**
		 *
		 * @since 4.3.1
		 */
		public function __destruct()
		{
			parent::__destruct();

			unset( $this->upload_folders );
			unset( $this->cache );
			unset( $this->cached_file_sizes );
		}

		/**
		 * Returns the cache info array
		 *
		 * @since 4.3.1
		 * @return array
		 */
		public function get_cache()
		{
			if( is_null( $this->cache ) )
			{
				$cache = get_option( 'avia_instagram_widgets_cache', '' );

				/**
				 * backwards comp only
				 */
				if( is_array( $cache ) )
				{
					$this->cache = $cache;
				}
				else if( ! is_string( $cache ) || empty( $cache ) )
				{
					$this->cache = null;
				}
				else
				{
					$cache = json_decode( $cache, true );
					$this->cache = is_array( $cache ) ? $cache : null;
				}

				if( empty( $this->cache ) )
				{
					$this->cache = array(
							'last_updated'		=> 0,			//	time() when last complete check has run
							'instances'			=> array()
						);
				}
			}

			return $this->cache;
		}

		/**
		 * Update the cache array in DB
		 *
		 * @since 4.3.1
		 * @param array|null $cache
		 */
		public function update_cache( array $cache = null )
		{
			if( ! is_null( $cache) )
			{
				$this->cache = $cache;
			}

			$save = json_encode( $this->cache );
			update_option( 'avia_instagram_widgets_cache', $save );
		}

		/**
		 * Ensure a valid instance array filled with defaults
		 *
		 * @since 4.3.1
		 * @param array $instance_cache
		 * @return array
		 */
		protected function parse_args_instance_cache( array $instance_cache )
		{
			$instance_cache = wp_parse_args( (array) $instance_cache, array(
											'upload_folder'		=> '',				//	not the complete path, only the last folder name
											'path_error'		=> '',				//	Error message if upload_folder could not be created
											'instagram_error'	=> '',
											'upload_errors'		=> false,			//	number of errors found when caching files to show
											'last_update'		=> 0,				//	time() of last update
											'cached_list'		=> array(),			//	in the order how to display the files and file info on server
											'instagram_list'	=> array()			//	returned info from instagramm
										));

			return $instance_cache;
		}

		/**
		 * Creates a unique key for the given instance for our cache array
		 *
		 * @since 4.3.1
		 * @param array $instance
		 * @param string $id_widget
		 * @return string
		 */
		protected function create_avia_key( array $instance, $id_widget )
		{
			$k = 0;
			$key = str_replace( $this->id_base . '-', '', $id_widget ) . '-' . AviaHelper::save_string( $instance['title'], '-' );

			$orig_key = $key;
			while( array_key_exists( $key, $this->cache['instances'] ) )
			{
				$key = $orig_key . "-{$k}";
				$k++;
			}

			return $key;
		}

		/**
		 * Output the widget in frontend
		 *
		 * @param array $args
		 * @param array $instance
		 */
		public function widget( $args, $instance )
		{
			$instance = $this->parse_args_instance( $instance );

			$fields = $this->get_field_names();

			foreach( $instance as $key => $value )
			{
				if( in_array( $key, $fields ) )
				{
					$instance[ $key ] = esc_attr( $value );
				}
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


			if( $this->in_block_editor_preview( $args, $instance ) )
			{
				return;
			}

			extract( $args, EXTR_SKIP );
			extract( $instance, EXTR_SKIP );

			/**
			 * Allow to change the conditional display setting - e.g. if user is opt in and allows to connect directly
			 *
			 * @since 4.4
			 * @param string $google_link			'' | 'server'
			 * @param string $context
			 * @param mixed $object
			 * @param array $args
			 * @param array $instance
			 * @return string
			 */
			$original_cache = $cache;
			$cache = apply_filters( 'avf_conditional_setting_external_links', $cache, __CLASS__, $this, $args, $instance );
			if( ! in_array( $cache, array( '', 'server' ) ) )
			{
			   $cache = $original_cache;
			}

			$title = apply_filters( 'widget_title', $title, $args );

			/**
			 * Skip for non logged in users in frontend
			 */
			if( ( trim( $username ) == '' ) && ! is_user_logged_in() && ! current_user_can( 'edit_posts' ) )
			{
				return;
			}

			echo $before_widget;

			if ( ! empty( $title ) )
			{
				echo $before_title . $title . $after_title;
			}

			do_action( 'aviw_before_widget', $instance );

			if( $username != '' )
			{
				$errors = array();
				$media_array = array();

				$instance_cache = isset( $this->cache['instances'][ $instance['avia_key'] ] ) ? $this->cache['instances'][ $instance['avia_key'] ] : null;

				if( ! is_null( $instance_cache ) )
				{
					if( ! empty( $instance_cache['instagram_error'] ) )
					{
						$errors =  array( $instance_cache['instagram_error'] );
					}
					if( ! empty( $instance_cache['upload_errors'] ) && ( 'server' == $instance['cache'] ) )
					{
						foreach( $instance_cache['cached_list'] as $img )
						{
							if( ! empty( $img['errors'] ) )
							{
								$errors = array_merge( $errors, $img['errors'] );
							}
						}
					}

					if( 'server' == $instance['cache'] )
					{
						$media_array = $instance_cache['cached_list'];

						$url = trailingslashit( trailingslashit( $this->upload_folders['instagram_url'] ) . $instance_cache['upload_folder'] );

						foreach( $media_array as $key => $media )
						{
							if( ! empty( $media['errors'] ) )
							{
								$errors = array_merge( $errors, $media['errors'] );
							}

							if( ! empty( $media[ $size ] ) )
							{
								$media_array[ $key ][ $size ] = $url . $media[ $size ];
							}
							if( ! empty( $media[ 'original' ] ) )
							{
								$media_array[ $key ]['original'] = $url . $media['original'];
							}
						}
					}
					else
					{
						$media_array = $instance_cache['instagram_list'];
					}
				}

				/**
				 * Only show error messages to admins and authors
				 */
				if( ! empty( $errors ) && is_user_logged_in() && current_user_can( 'edit_posts' ) )
				{
					$errors = array_map( 'esc_html__', $errors );

					$out = '';
					$out .= '<div class="av-instagram-errors">';

					$out .=		'<p class="av-instagram-errors-msg av-instagram-admin">' . esc_html__( 'Only visible for admins:', 'avia_framework' ) . '</p>';

					$out .=		'<p class="av-instagram-errors-msg av-instagram-admin">';
					$out .=			implode( '<br />', $errors );
					$out .=		'</p>';

					$out .= '</div>';

					echo $out;
				}

				if( count( $media_array ) > 0 )
				{
					// filters for custom classes
					$ulclass = esc_attr( apply_filters( 'aviw_list_class', 'av-instagram-pics av-instagram-size-' . $size ) );
					$rowclass = esc_attr( apply_filters( 'aviw_row_class', 'av-instagram-row' ) );
					$liclass = esc_attr( apply_filters( 'aviw_item_class', 'av-instagram-item' ) );
					$aclass = esc_attr( apply_filters( 'aviw_a_class', '' ) );
					$imgclass = esc_attr( apply_filters( 'aviw_img_class', '' ) );

					echo '<div class="' . esc_attr( $ulclass ) . '">';

					$last_id = end( $media_array );
					$last_id = $last_id['id'];

					$rowcount = 0;
					$itemcount = 0;
					foreach ( $media_array as $item )
					{
						if( empty( $item[ $size ] ) )
						{
							continue;
						}

						if( $rowcount == 0 )
						{
							echo "<div class='{$rowclass}'>";
						}

						$rowcount ++;
						$itemcount ++;

						$targeting = $target;
						if( $target == "lightbox" )
						{
							$targeting = "";
							$item['link'] = ! empty( $item['original'] ) ? $item['original'] : $item[ $size ];
						}

						echo '<div class="' . $liclass . '">';
						echo	'<a href="' . esc_url( $item['link'] ) . '" target="' . esc_attr( $targeting ) . '"  class="' . $aclass . ' ' . $imgclass . '" title="' . esc_attr( $item['description'] ) . '" style="background-image:url(' . esc_url( $item[ $size ] ) . ');">';
						echo	'</a>';
						echo '</div>';

						if( $rowcount % $columns == 0 || $last_id == $item['id'] || ( $itemcount >= $number ) )
						{
							echo '</div>';
							$rowcount = 0;

							if( $itemcount >= $number )
							{
								break;
							}
						}
					}
					echo '</div>';
				}
				else
				{
					echo '<p class="av-instagram-errors-msg">' . esc_html__( 'No images available at the moment', 'avia_framework' ) . '</p>';
				}
			}
			else
			{
				echo '<p class="av-instagram-errors-msg av-instagram-admin">' . esc_html__( 'For admins only: Missing Instagram user name !!', 'avia_framework' ) . '</p>';
			}

			if ( $link != '' )
			{
				echo '<a class="av-instagram-follow avia-button" href="https://instagram.com/' . esc_attr( trim( $username ) ) . '" rel="me" target="' . esc_attr( $target ) . '">' . $link . '</a>';
			}

			do_action( 'aviw_after_widget', $instance );

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
			echo '<div class="avia-preview-info">' . __( 'Title:', 'avia_framework' ) . ' ' . $instance['title'] .  '</div>';
			echo '<div class="avia-preview-info">' . __( 'Username:', 'avia_framework' ) . ' ' . $instance['username'] .  '</div>';
			echo '<div class="avia-preview-in-front">' . __( 'Content is only rendered in frontend.', 'avia_framework' ) . '</div>';

			echo isset( $args['after_widget'] ) ? $args['after_widget'] : '';

			return true;
		}

		/**
		 * Output the form in backend
		 *
		 * @param array $instance
		 */
		public function form( $instance )
		{
			$instance = $this->parse_args_instance( $instance );
			$fields = $this->get_field_names();

			foreach( $instance as $key => $value )
			{
				if( in_array( $key, $fields ) )
				{
					switch( $key )
					{
						case 'number':
						case 'columns':
							$instance[ $key ] = absint( $value );
							break;
						default:
							$instance[ $key ] = esc_attr( $value );
							break;
					}
				}
			}

			extract( $instance );

			?>
			<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'avia_framework' ); ?>: <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id( 'username' ); ?>"><?php _e( 'Username', 'avia_framework' ); ?>: <input class="widefat" id="<?php echo $this->get_field_id( 'username' ); ?>" name="<?php echo $this->get_field_name( 'username' ); ?>" type="text" value="<?php echo $username; ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id( 'cache' ); ?>"><?php _e( 'Location of your photos or videos', 'avia_framework' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'cache' ); ?>" name="<?php echo $this->get_field_name( 'cache' ); ?>" class="widefat">
					<option value="" <?php selected( '', $cache ) ?>><?php _e( 'Get from your instagram account (instagram server connection needed)', 'avia_framework' ); ?></option>
					<option value="server" <?php selected( 'server', $cache ) ?>><?php _e( 'Cache on your server - no instagram connection needed on pageload', 'avia_framework' ); ?></option>
				</select>
			</p>
			<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of photos', 'avia_framework' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" class="widefat">
					<option value="1" <?php selected( 1, $number ) ?>>1</option>
					<option value="2" <?php selected( 2, $number ) ?>>2</option>
					<option value="3" <?php selected( 3, $number ) ?>>3</option>
					<option value="4" <?php selected( 4, $number ) ?>>4</option>
					<option value="5" <?php selected( 5, $number ) ?>>5</option>
					<option value="6" <?php selected( 6, $number ) ?>>6</option>
					<option value="7" <?php selected( 7, $number ) ?>>7</option>
					<option value="8" <?php selected( 8, $number ) ?>>8</option>
					<option value="9" <?php selected( 9, $number ) ?>>9</option>
					<option value="10" <?php selected( 10, $number ) ?>>10</option>
					<option value="11" <?php selected( 11, $number ) ?>>11</option>
					<option value="12" <?php selected( 12, $number ) ?>>12</option>
				</select>
			</p>
			<p><label for="<?php echo $this->get_field_id( 'columns' ); ?>"><?php _e( 'Number of columns', 'avia_framework' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'columns' ); ?>" name="<?php echo $this->get_field_name( 'columns' ); ?>" class="widefat">
					<option value="1" <?php selected( 1, $columns ) ?>>1</option>
					<option value="2" <?php selected( 2, $columns ) ?>>2</option>
					<option value="3" <?php selected( 3, $columns ) ?>>3</option>
					<option value="4" <?php selected( 4, $columns ) ?>>4</option>
					<option value="5" <?php selected( 5, $columns ) ?>>5</option>
					<option value="6" <?php selected( 6, $columns ) ?>>6</option>
				</select>
			</p>
			<p><label for="<?php echo $this->get_field_id( 'size' ); ?>"><?php _e( 'Thumbnail size', 'avia_framework' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'size' ); ?>" name="<?php echo $this->get_field_name( 'size' ); ?>" class="widefat">
					<option value="thumbnail" <?php selected( 'thumbnail', $size ) ?>><?php _e( 'Thumbnail', 'avia_framework' ); ?></option>
					<option value="small" <?php selected( 'small', $size ) ?>><?php _e( 'Small', 'avia_framework' ); ?></option>
					<option value="large" <?php selected( 'large', $size ) ?>><?php _e( 'Large', 'avia_framework' ); ?></option>
					<option value="original" <?php selected( 'original', $size ) ?>><?php _e( 'Original', 'avia_framework' ); ?></option>
				</select>
			</p>
			<p><label for="<?php echo $this->get_field_id( 'target' ); ?>"><?php _e( 'Open links in', 'avia_framework' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'target' ); ?>" name="<?php echo $this->get_field_name( 'target' ); ?>" class="widefat">
					<option value="lightbox" <?php selected( 'lightbox', $target ) ?>><?php _e( 'Lightbox', 'avia_framework' ); ?></option>
					<option value="_self" <?php selected( '_self', $target ) ?>><?php _e( 'Current window (_self)', 'avia_framework' ); ?></option>
					<option value="_blank" <?php selected( '_blank', $target ) ?>><?php _e( 'New window (_blank)', 'avia_framework' ); ?></option>
				</select>
			</p>
			<p><label for="<?php echo $this->get_field_id( 'link' ); ?>"><?php _e( 'Link text', 'avia_framework' ); ?>: <input class="widefat" id="<?php echo $this->get_field_id( 'link' ); ?>" name="<?php echo $this->get_field_name( 'link' ); ?>" type="text" value="<?php echo $link; ?>" /></label></p>

			<?php

			if( ! $this->activate_cron )
			{
				echo '<p class="av-instagram-no-cron">';
				echo	__( 'WP Cron jobs are disabled. To assure a regular update of cached data and an optimal pageload in frontend and backend we recommend to activate this.', 'avia_framework' );
				echo '</p>';

				$timestamp = ( $this->cache['last_updated'] != 0 ) ? $this->cache['last_updated'] + $this->expire_time : false;
				$time = ( false !== $timestamp ) ? date( 'Y/m/d H:i a', $timestamp ) .  __( ' UTC', 'avia_framework' ) : __( 'No time available', 'avia_framework' );

				echo '<p class="av-instagram-next-update">';
				echo	__( 'The widget preloads and caches Instagram data for better performance.', 'avia_framework' )." ";
				echo	sprintf( __( 'Next update: %s', 'avia_framework' ), $time );
				echo '</p>';
			}
			else
			{
				$timestamp = wp_next_scheduled( 'av_instagram_scheduled_filecheck' );
				$time = ( false !== $timestamp ) ? date( "Y/m/d H:i", $timestamp ) .  __( ' UTC', 'avia_framework' ) : __( 'No time available', 'avia_framework' );

				echo '<p class="av-instagram-next-update">';
				echo	__( 'The widget preloads and caches Instagram data for better performance.', 'avia_framework' )." ";
				echo	sprintf( __( 'Next update: %s', 'avia_framework' ), $time );
				echo '</p>';
			}

			if( empty( $instance['avia_key'] ) )
			{
				return;
			}

			if( empty( $this->cache['instances'][ $instance['avia_key'] ] ) )
			{
				return;
			}

			$instance_cache = $this->cache['instances'][ $instance['avia_key'] ];
			$errors = array();

			if( ! empty( $instance_cache['instagram_error'] ) )
			{
				$errors = (array) $instance_cache['instagram_error'];
			}

			if( 'server' == $instance['cache'] )
			{
				foreach( $instance_cache['cached_list'] as $image )
				{
					if( ! empty( $image['errors'] ) )
					{
						$errors = array_merge( $errors, $image['errors'] );
					}
				}
			}

			if( ! empty( $errors ) )
			{
				$errors = array_map( 'esc_html__', $errors );

				$out  = '<div class="av-instagram-errors">';

				$out .=		'<p class="av-instagram-errors-msg av-instagram-error-headline">' . esc_html__( 'Errors found:', 'avia_framework' ) . '</p>';

				$out .=		'<p class="av-instagram-errors-msg">';
				$out .=			implode( '<br />', $errors );
				$out .=		'</p>';

				$out .= '</div>';

				echo $out;
			}

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
			$instance['username'] = trim( strip_tags( $new_instance['username'] ) );
			$instance['cache'] = ( $new_instance['cache'] == 'server' || $new_instance['cache'] == '' ) ? $new_instance['cache'] : apply_filters( 'avf_instagram_default_cache_location', 'server' );
			$instance['number'] = ! absint( $new_instance['number'] ) ? 9 : $new_instance['number'];
			$instance['columns'] = ! absint( $new_instance['columns'] ) ? 3 : $new_instance['columns'];
			$instance['size'] = ( $new_instance['size'] == 'thumbnail' || $new_instance['size'] == 'large' || $new_instance['size'] == 'small' || $new_instance['size'] == 'original' ) ? $new_instance['size'] : 'large';
			$instance['target'] = ( $new_instance['target'] == '_self' || $new_instance['target'] == '_blank'|| $new_instance['target'] == 'lightbox' ) ? $new_instance['target'] : '_self';
			$instance['link'] = strip_tags( $new_instance['link'] );


			/**
			 * We have a new widget (or an existing from an older theme version)
			 */
			if( empty( $instance['avia_key'] ) )
			{
				$key = $this->create_avia_key( $instance, $this->id );
				$instance['avia_key'] = $key;
				$this->cache['instances'][ $key ] = array();
				$this->update_cache();
			}

			$this->update_single_instance( $instance, $this->id );

			if( $this->activate_cron )
			{
				$this->restart_cron_job();
			}

			return $instance;
		}

		/**
		 * Get info from instagram
		 * based on https://gist.github.com/cosmocatalano/4544576
		 *
		 * @param string $username
		 *
		 * @return array|\WP_Error
		 */
		protected function scrape_instagram( $username )
		{
			$username = strtolower( $username );
			$username = str_replace( '@', '', $username );

			$remote = wp_remote_get( 'https://www.instagram.com/' . trim( $username ), array( 'sslverify' => false, 'timeout' => 60 ) );

			if ( is_wp_error( $remote ) )
			{
				return new WP_Error( 'site_down', __( 'Unable to communicate with Instagram.', 'avia_framework' ) );
			}

			$code = wp_remote_retrieve_response_code( $remote );
			if ( 200 != $code )
			{
				$msg = wp_remote_retrieve_response_message( $remote );
				if( empty( $msg ) )
				{
					$msg = __( 'Unknown error code', 'avia_framework' );
				}
				return new WP_Error( 'invalid_response', sprintf( __( 'Instagram returned error %d (= %s).', 'avia_framework' ), $code, $msg ) );
			}

			$shards = explode( 'window._sharedData = ', $remote['body'] );
			$insta_json = explode( ';</script>', $shards[1] );
			$insta_array = json_decode( $insta_json[0], true );

			if ( ! $insta_array )
			{
				return new WP_Error( 'bad_json', __( 'Instagram has returned invalid data.', 'avia_framework' ) );
			}

			if ( isset( $insta_array['entry_data']['ProfilePage'][0]['graphql']['user']['edge_owner_to_timeline_media']['edges'] ) )
			{
				$images = $insta_array['entry_data']['ProfilePage'][0]['graphql']['user']['edge_owner_to_timeline_media']['edges'];
			}
			else
			{
				return new WP_Error( 'bad_json_2', __( 'Instagram has returned invalid data.', 'avia_framework' ) );
			}

			if ( ! is_array( $images ) )
			{
				return new WP_Error( 'bad_array', __( 'Instagram has returned invalid data.', 'avia_framework' ) );
			}

			$instagram = array();

			foreach ( $images as $image )
			{
				// see https://github.com/stevenschobert/instafeed.js/issues/549
				if ( $image['node']['is_video'] == true )
				{
					$type = 'video';
				}
				else
				{
					$type = 'image';
				}

				$caption = __( 'Instagram Image', 'avia_framework' );

				if ( ! empty( $image['node']['edge_media_to_caption']['edges'][0]['node']['text'] ) )
				{
					$caption = wp_kses( $image['node']['edge_media_to_caption']['edges'][0]['node']['text'], array() );
				}

				$instagram[] = array(
						'description'   => $caption,
						'link'		  	=> trailingslashit( '//instagram.com/p/' . $image['node']['shortcode'] ),
						'time'		  	=> $image['node']['taken_at_timestamp'],
						'comments'	  	=> $image['node']['edge_media_to_comment']['count'],
						'likes'		 	=> $image['node']['edge_liked_by']['count'],
						'thumbnail'	 	=> preg_replace( '/^https?\:/i', '', $image['node']['thumbnail_resources'][0]['src'] ),
						'small'			=> preg_replace( '/^https?\:/i', '', $image['node']['thumbnail_resources'][2]['src'] ),
						'large'			=> preg_replace( '/^https?\:/i', '', $image['node']['thumbnail_resources'][4]['src'] ),
						'original'		=> preg_replace( '/^https?\:/i', '', $image['node']['display_url'] ),
						'type'		  	=> $type,
						'id'			=> $image['node']['id']
					);
			}

			$aviw_images_only = false;
			$aviw_images_only = apply_filters_deprecated( 'aviw_images_only', array( $aviw_images_only ), '4.3.1', 'avf_instagram_filter_files', __( 'Filter extended to filter images or videos', 'avia_framework' ) );

			/**
			 * Filter which type of elements will be displayed.
			 * Return an empty array to show all files.
			 *
			 * Possible values:   'video' | 'image'
			 *
			 * @since 4.3.1
			 * @return array
			 */
			$show = $aviw_images_only ? array( 'image' ) : array();
			$show = apply_filters( 'avf_instagram_filter_files', $show, $username );

			if( ! empty( $show ) )
			{
				foreach( $instagram as $key => $media_item )
				{
					if( ! in_array( $media_item['type'], $show ) )
					{
						unset( $instagram[ $key ] );
					}
				}

				$instagram = array_merge( $instagram );
			}

			if ( empty( $instagram ) )
			{
				return new WP_Error( 'no_images', __( 'Instagram did not return any images.', 'avia_framework' ) );
			}

			return $instagram;
		}

		/**
		 * WP Cron handler for background uploads
		 *
		 * @since 4.3.1
		 */
		public function handler_scheduled_filecheck()
		{
			if( defined( 'WP_DEBUG ' ) && WP_DEBUG )
			{
				error_log( '******************  In avia_instagram_widget::handler_scheduled_filecheck started' );
			}

			/**
			 * Create a scheduled event to prevent double checks running on parallel pageloads
			 */
			$this->schedule_cron_job( $this->expire_time * 2 );

			$settings = $this->get_settings();
			if( ! empty( $settings ) )
			{
				$this->check_all_instances();
			}

			$this->schedule_cron_job( $this->expire_time * 2 );

			$this->sync_data();

			$this->schedule_cron_job( $this->expire_time );

			if( defined( 'WP_DEBUG ' ) && WP_DEBUG )
			{
				error_log( '******************  In avia_instagram_widget::handler_scheduled_filecheck ended' );
			}
		}

		/**
		 * Synchronises directory and cache data structure.
		 * It might happen, that the update cronjob is running and user removes the last widget.
		 * This leads to an inconsistent cache and directory structure.
		 *
		 * As user might have added new widgets again we have to sync cache with latest settings
		 *
		 * @since 4.3.1
		 */
		public function sync_data()
		{
			if( defined( 'WP_DEBUG ' ) && WP_DEBUG )
			{
				error_log( '******************  In avia_instagram_widget::sync_data started' );
			}

			$settings = $this->get_settings();

			if( empty( $settings ) && empty( $this->cache['instances'] ) )
			{
				if( is_dir( $this->upload_folders['instagram_dir'] ) )
				{
					avia_backend_delete_folder( $this->upload_folders['instagram_dir'] );
					$this->cache['last_updated'] = time();
					$this->update_cache();
				}
				return;
			}

			$instance_infos = (array) $this->cache['instances'];

			/**
			 * Remove all entries from cache that have no more entry in settings
			 */
			$keys = array_keys( $instance_infos );
			$keys_to_keep = array();

			foreach ( $settings as $index => $setting )
			{
				if( in_array( $setting['avia_key'], $keys ) )
				{
					$keys_to_keep[] = $setting['avia_key'];
				}
			}

			$keys_to_remove = array_diff( $keys, $keys_to_keep );

			foreach( $keys_to_remove as $key )
			{
				$folder = $this->upload_folders['instagram_dir'] . $instance_infos[ $key ]['upload_folder'];
				avia_backend_delete_folder( $folder );
				unset( $this->cache['instances'][ $key ] );
			}

			/**
			 * Now we check that all directories belong to a cache entry
			 */
			$cache_dirs = is_dir( $this->upload_folders['instagram_dir'] ) ? scandir( $this->upload_folders['instagram_dir'] ) : false;
			if( ! is_array( $cache_dirs ) )
			{
				/**
				 * Something went wrong reading directory - folder does not exist, access denied, .....
				 * There is nothing we can do.
				 */
				return;
			}

			$cache_dirs = array_diff( $cache_dirs, array( '.', '..' ) );

			$ref_dirs = array();
			foreach( $this->cache['instances'] as $key => $instance_info )
			{
				if( isset( $instance_info['upload_folder'] ) )
				{
					$ref_dirs[] = $instance_info['upload_folder'];
				}
			}

			$remove_dirs = array_diff( $cache_dirs, $ref_dirs );

			foreach( $remove_dirs as $remove_dir )
			{
				avia_backend_delete_folder( $this->upload_folders['instagram_dir'] . $remove_dir );
			}


			if( empty( $this->cache['instances'] ) )
			{
				avia_backend_delete_folder( $this->upload_folders['instagram_dir'] );
			}

			$this->cache['last_updated'] = time();
			$this->update_cache();
		}

		/**
		 * WP Cron is disabled - we have to load files during pageload in admin area
		 *
		 * @since 4.3.1
		 */
		public function handler_init_filecheck()
		{
			$settings = $this->get_settings();
			if( empty( $settings ) )
			{
				/**
				 * Keep alive to allow to clean up in case when deleting a widget and check_all_instances() have run at same time.
				 * Due to internal WP caching this might have lead to inconsistent data structure.
				 */
				if( $this->activate_cron  )
				{
					$this->restart_cron_job();
				}
				return;
			}

			/**
			 * Fallback on version update - we need to switch to new data structure
			 * Can be removed in very very future versions.
			 *
			 * @since 4.3.1
			 */
			$instance = array_shift( $settings );
			if( ! isset( $instance['avia_key'] ) || empty( $instance['avia_key'] ) )
			{
				$instances = $this->get_settings();
				foreach( $instances as $key => &$instance )
				{
					$instance = $this->parse_args_instance( $instance );
					$key = $this->create_avia_key( $instance, $this->id_base . "-{$key}" );
					$instance['avia_key'] = $key;
					$this->cache['instances'][ $key ] = array();
				}
				unset( $instance );
				$this->save_settings( $instances );

				$this->cache['last_updated'] = 0;
				$this->update_cache();

				$this->check_all_instances();
			}

			if( $this->activate_cron  )
			{
				$this->restart_cron_job();
				return;
			}

			/**
			 * Check if we need to run an update
			 */
			if( $this->cache['last_updated'] + $this->expire_time > time() )
			{
				return;
			}

			/**
			 * Only run update in backend
			 */
			if( is_admin() )
			{
				$this->check_all_instances();
			}
		}

		/**
		 * Is called, when an instance of a widget is deleted - Both from active sidebars or inactive widget area.
		 *
		 * @since 4.3.1
		 * @param string $widget_id
		 * @param string $sidebar_id
		 * @param string $id_base
		 */
		public function handler_delete_widget( $widget_id, $sidebar_id, $id_base )
		{
			$id = str_replace( $id_base . '-', '', $widget_id );

			$settings = $this->get_settings();
			if( empty( $settings ) || empty( $settings[ $id ] ) )
			{
				return;
			}

			$instance = $settings[ $id ];

			$instance_info = isset( $this->cache['instances'][ $instance['avia_key'] ] ) ? $this->cache['instances'][ $instance['avia_key'] ] : array();
			if( empty( $instance_info ) )
			{
				return;
			}

			$instance = $this->parse_args_instance( $instance );
			$instance_info = $this->parse_args_instance_cache( $instance_info );

			if( count( $settings ) <= 1 )
			{
				avia_backend_delete_folder( $this->upload_folders['instagram_dir'] );
				$this->cache['instances'] = array();
			}
			else
			{
				$folder = $this->upload_folders['instagram_dir'] . $instance_info['upload_folder'];
				avia_backend_delete_folder( $folder );
				unset( $this->cache['instances'][ $instance['avia_key'] ] );
			}

			$this->update_cache();
		}

		/**
		 * This is a fallback function to ensure that the cron job is running
		 *
		 * @since 4.3.1
		 */
		protected function restart_cron_job()
		{
		   $timestamp = wp_next_scheduled( 'av_instagram_scheduled_filecheck' );
		   if( false === $timestamp )
		   {
			   $this->schedule_cron_job( $this->expire_time );
			   return;
		   }

		   /**
			* This is a fallback to prevent a blocking of updates
			*/
		   if( $timestamp > ( time() + $this->expire_time * 2 ) )
		   {
			   $this->schedule_cron_job( $this->expire_time * 2 );
		   }
		}

		/**
		 * Removes an existing cron job and creates a new one
		 *
		 * @since 4.3.1
		 * @param int $delay_seconds
		 * @return boolean
		 */
		protected function schedule_cron_job( $delay_seconds = 0 )
		{
			$timestamp = wp_next_scheduled( 'av_instagram_scheduled_filecheck' );
			if( false !== $timestamp )
			{
				wp_unschedule_hook( 'av_instagram_scheduled_filecheck' );
			}

			$timestamp = time() + $delay_seconds;

			$scheduled = wp_schedule_single_event( $timestamp, 'av_instagram_scheduled_filecheck' );

			return false !== $scheduled;
		}

		/**
		 * Scan all instances of this widget and update cache data
		 *
		 * @since 4.3.1
		 */
		protected function check_all_instances()
		{
			$settings = $this->get_settings();

			foreach ( $settings as $key => $instance )
			{
				$id_widget = $this->id_base . "-{$key}";

				if( false === is_active_widget( false, $id_widget, $this->id_base, false ) )
				{
					continue;
				}

				$this->update_single_instance( $instance, $id_widget );
			}

			$this->cache['last_updated'] = time();
			$this->update_cache();
		}

		/**
		 * Updates the cache for the given instance.
		 * As a fallback for older versions the instance is updated and returned.
		 *
		 * @since 4.3.1
		 * @param array $instance
		 * @param string $id_widget
		 * @return array
		 */
		protected function update_single_instance( array $instance, $id_widget )
		{
			set_time_limit( 0 );

			$instance = $this->parse_args_instance( $instance );

			/**
			 * Fallback for old versions - update to new datastructure
			 */
			if( empty( $instance['avia_key'] ) )
			{
				$key = $this->create_avia_key( $instance, $id_widget );
				$instance['avia_key'] = $key;
				$this->cache['instances'][ $key ] = array();
			}

			$instance_cache = isset( $this->cache['instances'][ $instance['avia_key'] ] ) ? $this->cache['instances'][ $instance['avia_key'] ] : array();
			$instance_cache = $this->parse_args_instance_cache( $instance_cache );

			/**
			 * Create upload directory if not exist. Upload directory will be deleted when widget instance is removed.
			 */
			if( ( 'server' == $instance['cache'] ) && empty( $instance_cache['upload_folder'] ) && ! empty( $instance['username'] ) )
			{
				$id = str_replace( $this->id_base . '-', '', $id_widget );
				$f = empty( $instance['title'] ) ? $instance['username'] : $instance['title'];
				$folder_name = substr( AviaHelper::save_string( $id . '-' . $f, '-' ), 0, 30 );
				$folder = $this->upload_folders['instagram_dir'] . $folder_name;

				$created = avia_backend_create_folder( $folder, false, 'unique' );
				if( $created )
				{
					$split = pathinfo( $folder );
					$instance_cache['upload_folder'] = $split['filename'];
					$instance_cache['path_error'] = '';
					$instance_cache['cached_list'] = array();
				}
				else
				{
					$instance_cache['path_error'] = sprintf( __( 'Unable to create cache folder "%s". Files will be loaded directly from instagram', 'avia_framework' ), $folder );
				}
			}

			$username = $instance['username'];
			$number = $instance['number'];

			if( ! empty( $username) )
			{
				$media_array = $this->scrape_instagram( $username );

				if ( ! is_wp_error( $media_array ) )
				{
					$instance_cache['instagram_error'] = '';
					$instance_cache['instagram_list'] = array_slice( $media_array, 0, $number );

					if( 'server' == $instance['cache'] )
					{
						$instance_cache = $this->cache_files_in_upload_directory( $media_array, $instance, $instance_cache );
					}
				}
				else
				{
					/**
					 * We only store error message but keep existing files for fallback so we do not break widget
					 */
					$instance_cache['instagram_error'] = $media_array->get_error_message();
				}
			}
			else
			{
				$instance_cache['instagram_error'] = __( 'You need to specify an Instagram username.', 'avia_framework' );
				$instance_cache['instagram_list'] = array();
				$instance_cache['cached_list'] = array();
			}

			$instance_cache['last_update'] = time();

			$this->cache['instances'][ $instance['avia_key'] ] = $instance_cache;
			$this->update_cache();

			return $instance;
		}

		/**
		 * Updates the local stored files in upload directory
		 * Already downloaded files are not updated.
		 * If an error occurs, we try to download more files as fallback to provide requested number of files
		 * in frontend.
		 *
		 * No longer needed files are removed from cache.
		 *
		 * @since 4.3.1
		 * @param array $instagram_files
		 * @param array $instance
		 * @param array $instance_cache
		 * @return array
		 */
		protected function cache_files_in_upload_directory( array $instagram_files, array $instance, array $instance_cache )
		{
			set_time_limit( 0 );

			$cached_files = $instance_cache['cached_list'];

			$new_cached_files = array();
			$no_errors = 0;

			foreach( $instagram_files as $instagram_file )
			{
				$id = $instagram_file['id'];

				$found = false;
				foreach( $cached_files as $key_cache => $cached_file )
				{
					if( $id == $cached_file['id'] )
					{
						/**
						 * If an error occurred in a previous file load we try to reload all files again
						 */
						if( ! empty( $cached_file['errors'] ) )
						{
							$this->remove_single_cached_files( $cached_file, $instance_cache );
							unset( $cached_files[ $key_cache ] );
							break;
						}

						/**
						 * As a fallback (or if other sizes were added later) we check if the cached files exist
						 */
						$path = trailingslashit( $this->upload_folders['instagram_dir'] . $instance_cache['upload_folder'] );
						foreach( $this->cached_file_sizes as $size )
						{
							if( empty( $cached_file[ $size ] ) || ! file_exists( $path . $cached_file[ $size ] ) )
							{
								$this->remove_single_cached_files( $cached_file, $instance_cache );
								unset( $cached_files[ $key_cache ] );
								break;
							}
						}

						if( ! isset( $cached_files[ $key_cache ] ) )
						{
							break;
						}

						$ncf = $cached_file;

						$ncf['description'] = $instagram_file['description'];
						$ncf['link'] = $instagram_file['link'];
						$ncf['time'] = $instagram_file['time'];
						$ncf['comments'] = $instagram_file['comments'];
						$ncf['likes'] = $instagram_file['likes'];
						$ncf['type'] = $instagram_file['type'];

						$new_cached_files[] = $ncf;

						unset( $cached_files[ $key_cache ] );
						$found = true;
						break;
					}
				}

				if( ! $found )
				{
					$new_cached_files[] = $this->download_from_instagram( $instagram_file, $instance, $instance_cache );
				}

				$last = $new_cached_files[ count( $new_cached_files ) - 1 ];

				/**
				 * Check if we could cache the file in requested size - we might have got a warning from chmod
				 */
				if( empty( $last['errors'] ) || ! empty( $last[ $instance['size'] ] ) )
				{
					$no_errors++;
				}

				/**
				 * Also break if we get too many errors
				 */
				if( $no_errors >= $instance['number'] || count( $new_cached_files ) > ( $instance['number'] * 2 ) )
				{
					break;
				}
			}

			/**
			 * Now we add all remaining cached files to fill up requested number of files
			 */
			if( $no_errors < $instance['number'] )
			{
				foreach( $cached_files as $key_cache => $cached_file )
				{
					$new_cached_files[] = $cached_file;
					if( empty( $cached_file['errors'] ) )
					{
						$no_errors++;
					}

					unset( $cached_files[ $key_cache ] );

					if( $no_errors >= $instance['number'] )
					{
						break;
					}
				}
			}

			/**
			 * Now we delete no longer needed files
			 */
			foreach( $cached_files as $key_cache => $cached_file )
			{
				$this->remove_single_cached_files( $cached_file, $instance_cache );
				unset( $cached_files[ $key_cache ] );
			}

			/**
			 * Save results and count errors
			 */
			$err_cnt = 0;
			$count = 1;

			foreach( $new_cached_files as $new_file )
			{
				if( ! empty( $new_file['errors'] ) )
				{
					$err_cnt++;
				}
				$count++;

				if( $count > $instance['number'] )
				{
					break;
				}
			}

			$instance_cache['upload_errors'] = ( 0 == $err_cnt ) ? false : $err_cnt;
			$instance_cache['cached_list'] = $new_cached_files;

			return $instance_cache;
		}

		/**
		 * Downloads the files from instagram and stores them in local cache
		 *
		 * @since 4.3.1
		 * @param array $instagram_file
		 * @param array $instance
		 * @param array $instance_cache
		 * @return array
		 */
		protected function download_from_instagram( array $instagram_file, array $instance, array $instance_cache )
		{
			$new_cached_file = $instagram_file;
			$new_cached_file['errors'] = array();
			$instagram_schema = 'https:';

			$cache_path = trailingslashit( $this->upload_folders['instagram_dir'] . $instance_cache['upload_folder'] );

			foreach( $this->cached_file_sizes as $size )
			{
				$file_array = array();

				//	instagram returns link to file with ?......
				$fn = explode( '?', basename( $instagram_file[ $size ] ) );
				$file_array['name'] = $fn[0];

				// Download file to temp location - include file if called from frontend.
				if( ! function_exists( 'download_url' ) )
				{
					$s = trailingslashit( ABSPATH ) . 'wp-admin/includes/file.php';
					require_once $s;
				}

				$file_array['tmp_name'] = download_url( $instagram_schema . $instagram_file[ $size ] );

				// If error storing temporarily, return the error.
				if( is_wp_error( $file_array['tmp_name'] ) )
				{
					$new_cached_file[ $size ] = '';
					$new_cached_file['errors'] = array_merge( $new_cached_file['errors'], $file_array['tmp_name']->get_error_messages() );
					continue;
				}

				$new_file_name = $size . '_' . $file_array['name'];
				$new_name = $cache_path . $new_file_name;

				$moved = avia_backend_rename_file( $file_array['tmp_name'], $new_name );
				if( is_wp_error( $moved ) )
				{
					$new_cached_file[ $size ] = '';
					$new_cached_file['errors'] = array_merge( $new_cached_file['errors'], $moved->get_error_messages() );
					continue;
				}

				/**
				 * Try to change accessability of file
				 */
				if( ! chmod( $new_name, 0777 ) )
				{
					$new_cached_file['errors'][] = sprintf( __( 'Could not change user rights of file %s to 777 - file might not be visible in frontend.', 'avia_framework' ), $new_name );
				}

				$new_cached_file[ $size ] = $new_file_name;
			}

			return $new_cached_file;
		}

		/**
		 * Removes all cashed files from $cached_file_info
		 *
		 * @since 4.3.1
		 * @param array $cached_file_info
		 * @param array $instance_cache
		 * @return array
		 */
		protected function remove_single_cached_files( array $cached_file_info, array $instance_cache )
		{
			$cache_path = trailingslashit( $this->upload_folders['instagram_dir'] . $instance_cache['upload_folder'] );

			foreach( $this->cached_file_sizes as $size )
			{
				if( ! empty( $cached_file_info[ $size ] ) )
				{
					$file = $cache_path . $cached_file_info[ $size ];

					if( file_exists( $file ) )
					{
						unlink( $file );
					}
					$cached_file_info[ $size ] = '';
				}
			}

			return $cached_file_info;
		}
	}
}
