<?php
/**
 * Base class to handle duplicate post functionality
 *
 * @based on woocommerce\includes\admin\class-wc-admin-duplicate-product.php
 *
 * @since 5.6.9
 * @added_by Günter
 */
if( ! defined( 'AVIA_FW' ) ) { exit( 'No direct script access allowed' ); }


if( ! class_exists( 'aviaDuplicatePost', false ) )
{
	class aviaDuplicatePost
	{
		/**
		 *
		 * @since 5.6.9
		 * @var aviaDuplicatePost
		 */
		static protected $_instance = null;

		/**
		 * List of post types that should not get a duplicate link
		 * (e.g. WooCommerce provides an own logic)
		 *
		 * @since 5.6.9
		 * @var array
		 */
		protected $ignore_post_types;

		/**
		 * Return the instance of this class
		 *
		 * @since 5.6.9
		 * @return aviaDuplicatePost
		 */
		static public function instance()
		{
			if( is_null( aviaDuplicatePost::$_instance ) )
			{
				aviaDuplicatePost::$_instance = new aviaDuplicatePost();
			}

			return aviaDuplicatePost::$_instance;
		}

		/**
		 * @since 5.6.9
		 */
		public function __construct()
		{
			$this->ignore_post_types = [];

			add_action( 'init', [ $this, 'handler_wp_init' ], 1000 );

			//	attach to pages, posts and custom post types
			add_filter( 'page_row_actions', [ $this, 'handler_wp_add_duplicate_link' ], 10000, 2 );
			add_filter( 'post_row_actions', [ $this, 'handler_wp_add_duplicate_link' ], 10000, 2 );

			//	attach below submit in classic editor
			add_action( 'post_submitbox_start', [ $this, 'handler_wp_add_duplicate_link_edit_post' ] );

			add_action( 'admin_action_avia_duplicate_post', [ $this, 'handler_admin_action_avia_duplicate_post' ], 10 );
		}

		/**
		 * @since 5.6.9
		 */
		public function __destruct()
		{
			unset( $this->ignore_post_types );
		}

		/**
		 * @since 5.6.9
		 */
		public function handler_wp_init()
		{
			/**
			 *
			 * @used_by								aviaElementTemplates		10
			 * @used_by								config-woocommerce\config.php - avia_woocommerce_ignore_duplicate_post_types()		10
			 * @since 5.6.9
			 * @param array $this->ignore_post_types
			 * @return array
			 */
			$this->ignore_post_types = apply_filters( 'avf_ignore_duplicate_post_types', $this->ignore_post_types );
		}

		/**
		 * Add the duplicate link
		 *
		 * @since 5.6.9
		 * @param array $actions		Array of actions.
		 * @param WP_Post $post			Post object.
		 * @return array
		 */
		public function handler_wp_add_duplicate_link( $actions, $post )
		{
			/**
			 * @since 5.6.9
			 * @param string $capability
			 * @param WP_Post $post
			 * @return boolean
			 */
			if( ! current_user_can( apply_filters( 'avf_duplicate_post_capability', 'edit_posts', $post ) ) )
			{
				return $actions;
			}

			if( in_array( $post->post_type, $this->ignore_post_types ) )
			{
				return $actions;
			}

			/**
			 * Do not add our link, if we find 'duplicate'
			 */
			$keys = array_keys( $actions );

			foreach( $keys as $key )
			{
				if( false !== strpos( $key, 'duplicate' ) )
				{
					return $actions;
				}
			}

			if( 'post' == $post->post_type )
			{
				$admin_url = admin_url( "edit.php?action=avia_duplicate_post&amp;post_id={$post->ID}" );
			}
			else
			{
				$admin_url = admin_url( "edit.php?post_type={$post->post_type}&action=avia_duplicate_post&amp;post_id={$post->ID}" );
			}

			$url = wp_nonce_url( $admin_url, 'avia-duplicate-post_' . $post->ID );
			$aria = esc_attr__( 'Make a duplicate from this post', 'avia_framework' );
			$desc = esc_html__( 'Duplicate', 'avia_framework' );

			$actions['avia_duplicate'] = '<a href="' . $url . '" aria-label="' . $aria . '" rel="permalink">' . $desc . '</a>';

			return $actions;
		}

		/**
		 * Output the duplicate link
		 *
		 * @since 5.6.9
		 */
		public function handler_wp_add_duplicate_link_edit_post()
		{
			global $post;

			/**
			 * @since 5.6.9
			 * @param string $capability
			 * @param WP_Post $post
			 * @return boolean
			 */
			if( ! current_user_can( apply_filters( 'avf_duplicate_post_capability', 'edit_posts', $post ) ) )
			{
				return;
			}

			if( ! $post instanceof WP_Post )
			{
				return;
			}

			if( in_array( $post->post_type, $this->ignore_post_types ) )
			{
				return;
			}

			if( 'post' == $post->post_type )
			{
				$admin_url = admin_url( "edit.php?action=avia_duplicate_post&amp;post_id={$post->ID}" );
			}
			else
			{
				$admin_url = admin_url( "edit.php?post_type={$post->post_type}&action=avia_duplicate_post&amp;post_id={$post->ID}" );
			}

			$url = wp_nonce_url( $admin_url, 'avia-duplicate-post_' . $post->ID );

			$out  = '<div id="duplicate-action">';
			$out .=		'<a class="submitduplicate duplication" href="' . esc_url( $url ) . '">';
			$out .=			esc_html( 'Copy to a new draft', 'avia_framework' );
			$out .=		'</a>';
			$out .= '</div>';

			echo $out;
		}

		/**
		 * Create duplicate post
		 *
		 * @since 5.6.9
		 */
		public function handler_admin_action_avia_duplicate_post()
		{
			if( empty( $_REQUEST['post_id'] ) )
			{
				wp_die( esc_html__( 'No post ID to duplicate has been supplied!', 'avia_framework' ) );
			}

			$post_id = isset( $_REQUEST['post_id'] ) ? absint( $_REQUEST['post_id'] ) : '';

			check_admin_referer( 'avia-duplicate-post_' . $post_id );

			$post = get_post( $post_id );

			if( ! $post instanceof WP_Post )
			{
				wp_die( sprintf( esc_html__( 'Post creation failed, could not find original post: %s', 'avia_framework' ), esc_html( $post_id ) ) );
			}

			$current_user = wp_get_current_user();

			/**
			 * @since 5.6.9
			 * @param int $new_post_author
			 * @param WP_Post $post
			 * @return int
			 */
			$new_post_author = apply_filters( 'avf_duplicate_post_new_post_author', $current_user->ID, $post );

			$args = [
					'post_title'		=> $post->post_title . ' ' . esc_html__( '(copy)', 'avia_framework' ),
					'post_author'		=> $new_post_author,
					'post_content'		=> $post->post_content,
					'post_excerpt'		=> $post->post_excerpt,
					'post_type'			=> $post->post_type,
					'post_name'			=> $post->post_name,
					'post_status'		=> 'draft',
					'post_parent'		=> $post->post_parent,
					'post_password'		=> $post->post_password,
					'comment_status'	=> $post->comment_status,
					'ping_status'		=> $post->ping_status,
					'to_ping'			=> $post->to_ping,
					'menu_order'		=> $post->menu_order
				];

			$new_post_id = wp_insert_post( $args );


			if( $new_post_id instanceof WP_Error || $new_post_id == 0 )
			{
				wp_die( esc_html__( 'Creating a copy of post has failed. Please try again', 'avia_framework' ) );
			}

			/**
			 * Filter array of taxonomy names for post type, ex array('category', 'post_tag')
			 *
			 * @since 5.6.9
			 * @param array $taxonomies
			 * @param WP_Post $post
			 * @retrun array
			 */
			$taxonomies = apply_filters( 'avf_duplicate_post_new_post_taxonomies', get_object_taxonomies( get_post_type( $post ) ), $post );

			if( is_array( $taxonomies ) )
			{
				foreach( $taxonomies as $taxonomy )
				{
					$post_terms = wp_get_object_terms( $post_id, $taxonomy, [ 'fields' => 'slugs' ] );

					wp_set_object_terms( $new_post_id, $post_terms, $taxonomy, false );
				}
			}

			/**
			 * Filter the post meta fields for post to duplicate
			 *
			 * @since 5.6.9
			 * @param array $post_meta
			 * @param WP_Post $post
			 * @retrun array
			 */
			$post_meta = apply_filters( 'avf_duplicate_post_new_post_meta', get_post_meta( $post_id ), $post );

			if( is_array( $post_meta ) )
			{
				foreach( $post_meta as $meta_key => $meta_values )
				{
					if( '_wp_old_slug' == $meta_key )
					{
						// do nothing for this meta key
						continue;
					}

					foreach( $meta_values as $meta_value )
					{
						add_post_meta( $new_post_id, $meta_key, $meta_value );
					}
				}
			}

			/**
			 *
			 * @since 5.6.9
			 * @param int $new_post_id
			 * @param WP_Post $post
			 */
			do_action( 'avf_duplicate_post_added', $new_post_id, $post );

			// Redirect to the edit screen for the new draft post.
			wp_redirect( admin_url( "post.php?action=edit&post={$new_post_id}" ) );

			exit;
		}
	}

	/**
	 * Returns the main instance of aviaSVGImages to prevent the need to use globals.
	 *
	 * @since 5.6.9
	 * @return aviaDuplicatePost
	 */
	function avia_DuplicatePost()
	{
		return aviaDuplicatePost::instance();
	}

	//	activate class
	avia_DuplicatePost();

}
