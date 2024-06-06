<?php
/**
* Create a hidden post type that allows us to save the template snippets to a post that cant be deleted.
* Prevents accidental removal and also helps when exporting/importing data since post meta data is stored in the wordpress xml, other than the options table
*/

// Don't load directly
if( ! defined( 'ABSPATH' ) ) { exit; }

if( ! class_exists( 'AviaStoragePost', false ) )
{
	class AviaStoragePost
	{
		/**
		 * The generate_post_type function builds the hidden posts necessary for image saving on options pages
		 */
		public static function generate_post_type()
		{
			register_post_type( 'avia_framework_post', array(
							'labels'			=> array( 'name' => 'Avia Framework' ),
							'show_ui'			=> false,
							'query_var'			=> true,
							'capability_type'	=> 'post',
							'hierarchical'		=> false,
							'rewrite'			=> false,
							'supports'			=> array( 'editor', 'title' ),
							'can_export'		=> true,
							'public'			=> true,
							'show_in_nav_menus'	=> false
						) );
		}


		/**
		 * The get_custom_post function gets a custom post based on a post title. if no post cold be found it creates one
		 *
		 * @param string $post_title		the title of the post
		 * @return int
		 * @package 	AviaFramework
		 */
		public static function get_custom_post( $post_title )
		{
			$save_title = AviaHelper::save_string( $post_title );

			$qa = [
					'post_type'					=> 'avia_framework_post',
					'title'						=> 'avia_' . $save_title,
					'post_status'				=> 'draft',
					'posts_per_page'			=> 1,
					'no_found_rows'				=> true,
					'update_post_term_cache'	=> false,
					'update_post_meta_cache'	=> false,
					'orderby'					=> 'post_date ID',
					'order'						=> 'ASC'
				];

			$query = new WP_Query( $qa );

			if( empty( $query->post ) )
			{
				$args = [
						'post_type'			=> 'avia_framework_post',
						'post_title'		=> 'avia_' . $save_title,
						'post_status'		=> 'draft',
						'comment_status'	=> 'closed',
						'ping_status'		=> 'closed'
					];

				$avia_post_id = wp_insert_post( $args );
			}
			else
			{
				$avia_post_id = $query->post->ID;
			}

			return $avia_post_id;
		}
	}
}
