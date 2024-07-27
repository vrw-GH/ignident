<?php
/**
 * @since 6.0
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'aviaCustomLayout', false ) )
{
	class aviaCustomLayout
	{
		const POST_TYPE = 'alb_custom_layout';
		const TAXONOMY = 'alb_custom_layout_entries';
		const METABOX_ID = 'avia_custom_layout_settings';

		/**
		 * Holds the instance of this class
		 *
		 * @since 6.0
		 * @var aviaDynamicContent
		 */
		static private $_instance = null;

		/**
		 * Flag if this feature has been enabled
		 *
		 * @since 6.0
		 * @var boolean|null
		 */
		protected $enabled;

		/**
		 * Stores the filtered post type
		 *
		 * @since 6.0
		 * @var string
		 */
		protected $post_type;

		/**
		 * Stores the filtered taxonomy
		 *
		 * @since 6.0
		 * @var string
		 */
		protected $taxonomy;

		/**
		 * Flag if we are on cpt edit page
		 *
		 * @since 6.0
		 * @var boolean
		 */
		protected $is__edit_custom_layout_page;

		/**
		 * Backend Metabox elements
		 *
		 * @since 6.0
		 * @var array|null
		 */
		protected $box_elements;


		/**
		 * Return the instance of this class
		 *
		 * @since 6.0
		 * @return aviaCustomLayout
		 */
		static public function instance()
		{
			if( is_null( aviaCustomLayout::$_instance ) )
			{
				aviaCustomLayout::$_instance = new aviaCustomLayout();
			}

			return aviaCustomLayout::$_instance;
		}

		/**
		 *
		 * @since 6.0
		 */
		protected function __construct()
		{
			$this->enabled = null;
			$this->post_type = null;
			$this->taxonomy = null;
			$this->is__edit_custom_layout_page = null;
			$this->box_elements = null;

			if( $this->custom_layout_enabled() )
			{
				$this->register_post_types();
			}

			$this->activate_filters();
		}

		/**
		 *
		 * @since 6.0
		 */
		public function __destruct()
		{
			unset( $this->box_elements );
		}

		/**
		 * Returns the filtered post type for dynamic content
		 *
		 * @since 6.0
		 * @return string
		 */
		public function get_post_type()
		{
			if( is_null( $this->post_type ) )
			{
				/**
				 * @since 6.0
				 * @param string
				 * @return string
				 */
				$this->post_type = apply_filters( 'avf_custom_layout_post_type', aviaCustomLayout::POST_TYPE );
			}

			return $this->post_type;
		}

		/**
		 * Returns the filtered taxonomy for dynamic content
		 *
		 * @since 6.0
		 * @return string
		 */
		public function get_taxonomy()
		{
			if( is_null( $this->taxonomy ) )
			{
				/**
				 * @since 6.0
				 * @param string
				 * @return string
				 */
				$this->taxonomy = apply_filters( 'avf_custom_layout_taxonomy', aviaCustomLayout::TAXONOMY );
			}

			return $this->taxonomy;
		}

		/**
		 * Register Post Type and Categories
		 *
		 * @since 6.0
		 */
		public function register_post_types()
		{
			if( post_type_exists( $this->get_post_type() ) )
			{
				return;
			}

			$labels = [
						'name'					=> __( 'Custom Layouts', 'avia_framework' ),
						'singular_name'			=> __( 'Custom Layout', 'avia_framework' ),
						'all_items'				=> __( 'All Custom Layouts', 'avia_framework' ),
						'add_new'				=> __( 'Add New', 'avia_framework' ),
						'add_new_item'			=> __( 'Add New Custom Layout', 'avia_framework' ),
						'edit_item'				=> __( 'Edit Custom Layout', 'avia_framework' ),
						'new_item'				=> __( 'New Custom Layout', 'avia_framework' ),
						'view_item'				=> __( 'View Custom Layout', 'avia_framework' ),
						'search_items'			=> __( 'Search Custom Layouts', 'avia_framework' ),
						'not_found'				=> __( 'No Custom Layouts found', 'avia_framework' ),
						'not_found_in_trash'	=> __( 'No Custom Layouts found in Trash', 'avia_framework' ),
						'parent_item_colon'		=> ''
					];

			$args = [
						'labels'				=> $labels,
						'public'				=> true,
						'show_ui'				=> true,
						'show_in_menu'			=> false,
						'show_in_admin_bar'		=> true,
						'capability_type'		=> 'post',
						'hierarchical'			=> true,
						'rewrite'				=> false,
						'query_var'				=> true,
						'show_in_nav_menus'		=> false,
						'show_in_rest'			=> false,				//	set to false to disallow block editor
						'taxonomies'			=> [],
						'supports'				=> [ 'title', 'thumbnail', 'excerpt', 'editor', 'revisions', 'author', 'custom-fields' ],
						'menu_icon'				=> 'dashicons-edit-page',
						'can_export'			=> true,
						'has_archive'			=> false,
						'exclude_from_search'	=> true
					];

			/**
			 * Custom value added by Enfold:
			 *
			 *  - supress "Edit Table" selectbox in WP write settings screen and ignore modify of CPT edit table columns
			 *
			 * @since 6.0
			 */
			$args['cpt_edit_table_cols'] = false;


			/**
			 * @since 6.0
			 * @param array $args
			 * @return array
			 */
			$args = apply_filters( 'avf_custom_layout_cpt_args', $args );

			register_post_type( $this->get_post_type(), $args );


			$tax_args = [
							'hierarchical'			=> true,
							'label'					=> __( 'Categories', 'avia_framework' ),
							'singular_label'		=> __( 'Custom Layout Category', 'avia_framework' ),
							'rewrite'				=> false,
							'show_ui'				=> false,			//  true in real  	hide in admin menu and meta box
							'show_in_quick_edit'	=> false,
							'query_var'				=> true,
							'show_in_rest'			=> false			//	set to false to disallow block editor
						];

			/**
			 * @since 6.0
			 * @param array $tax_args
			 * @return array
			 */
			$tax_args = apply_filters( 'avf_custom_layout_cpt_tax_args', $tax_args );

			register_taxonomy( $this->get_taxonomy(), [ $this->get_post_type() ], $tax_args );
		}

		/**
		 * Attach to filters
		 *
		 * @since 6.0
		 */
		protected function activate_filters()
		{
			add_action( 'avia_import_hook', array( $this, 'handler_avia_import_hook' ), 10 );

			add_action( 'ava_menu_page_added', array( $this, 'handler_ava_menu_page_added' ), 10, 4 );

			add_filter( 'manage_edit-alb_custom_layout_columns', [ $this, 'handler_edit_alb_custom_layout_columns' ], 10 );
			add_action( 'manage_pages_custom_column', [ $this, 'handler_pages_custom_column' ], 10, 2 );
			add_action( 'manage_posts_custom_column', [ $this, 'handler_pages_custom_column' ], 10, 2 );

			add_filter( 'admin_body_class', array( $this, 'handler_admin_body_class' ) );

			add_filter( 'avf_force_alb_usage', [ $this, 'handler_avf_force_alb_usage' ], 500, 2 );
			add_filter( 'avf_shortcode_insert_button_backend_disabled', [ $this, 'handler_avf_shortcode_insert_button_backend_disabled' ], 10, 2 );
			add_action( 'avia_save_post_meta_box', [ $this, 'handler_save_post_meta_box' ], 10, 1 );
			add_filter( 'avf_builder_button_params', [ $this, 'handler_avf_builder_button_params' ], 10, 1 );

			add_filter( 'avf_custom_layout__post_types', [ $this, 'handler_avf_custom_layout__post_types' ], 10, 1 );
		}

		/**
		 * Returns, if the feature has been enabled in theme options
		 *
		 * @since 6.0
		 * @return boolean
		 */
		public function custom_layout_enabled()
		{
			if( is_null( $this->enabled ) )
			{
				$enabled = false !== strpos( avia_get_option( 'alb_dynamic_content' ), 'alb_custom_layout' );

				/**
				 * @used_by			might be avia_WPML ?????
				 * @since 6.0
				 * @param boolean
				 * @return boolean
				 */
				$this->enabled = apply_filters( 'avf_custom_layout_enabled', $enabled );
			}

			return $this->enabled;
		}

		/**
		 * Force register CPT when import a demo
		 *
		 * @since 6.0
		 */
		public function handler_avia_import_hook()
		{
			$this->register_post_types();
		}

		/**
		 * Add ALB Elements as submenu to Theme Options Page
		 *
		 * @since 6.0
		 * @param string $top_level
		 * @param avia_adminpages $adminpages
		 * @param string $the_title
		 * @param string $menu_title
		 */
		public function handler_ava_menu_page_added( $top_level, avia_adminpages $adminpages, $the_title, $menu_title )
		{
			if( ! $this->custom_layout_enabled() )
			{
				return;
			}

			$cap = false === strpos( avia_get_option( 'alb_dynamic_content' ), 'editors' ) ? 'manage_options' : 'edit_posts';

			/**
			 * @since 6.0.1
			 * @param boolean $show_menus
			 * @param string $cap
			 * @return boolean
			 */
			$show_menus = (bool) apply_filters( 'avf_custom_layout_show_wp_menus', current_user_can( $cap ), $cap );

			if( ! $show_menus )
			{
				return;
			}

			$obj = get_post_type_object( $this->get_post_type() );

			if( ! $obj instanceof WP_Post_Type )
			{
				return;
			}

			/**
			 * Possible WP Bug (WP 5.5.1 - Enfold 6.0)
			 *
			 * Main menu has capability 'manage_options'.
			 * If user has less capabilty than the added menus from here are shown but user cannot access the page because WP rechecks capability of main menu.
			 *
			 * In this case we have to add our own main menu with less cap.
			 */
			$cap_new = $this->get_capability( 'new' );
			$cap_edit = $this->get_capability( 'edit' );

			if( ! current_user_can( 'manage_options' ) && ( 'manage_options' != $cap_new || 'manage_options' != $cap_edit ) )
			{
				$top_level = 'edit.php?post_type=' . $this->get_post_type();
				$cap = 'manage_options' != $cap_new ? $cap_new : $cap_edit;

				add_menu_page(
							$the_title . __( ' Custom Layouts', 'avia_framework' ),		// page title
							$menu_title . __( ' Custom Layouts', 'avia_framework' ),	// menu title
							$cap,														// capability
							$top_level,													// menu slug (and later also database options key)
							'',															// executing function
							"dashicons-admin-home",
							26
						);
			}

			add_submenu_page(
							$top_level,										//$parent_slug
							$obj->label,									//$page_title
							$obj->labels->all_items,						//$menu_title
							$this->get_capability( 'new' ),					//$capability
							'edit.php?post_type=' . $this->get_post_type()
						);

			add_submenu_page(
							$top_level,											//$parent_slug
							$obj->label,										//$page_title
							$obj->labels->new_item,								//$menu_title
							$this->get_capability( 'edit' ),					//$capability
							'post-new.php?post_type=' . $this->get_post_type()
						);

		}

		/**
		 * Returns the filtered string for a capability to show menus to edit custom layouts.
		 *
		 * @since 6.0
		 * @param string $which				'new' | 'edit'
		 * @return string
		 */
		public function get_capability( $which = 'new' )
		{
			/**
			 * Filter the user capability to create and edit ALB Element Templates
			 * Make sure to return a valid capability.
			 *
			 * @since 4.8
			 * @param string $cap
			 * @param string $which				'new' | 'edit'
			 * @return string
			 */
			return apply_filters( 'avf_custom_layouts_user_capability', 'edit_posts', $which );
		}

		/**
		 * Add extra classes
		 *
		 * @since 6.0
		 * @param string $classes
		 * @return string
		 */
		public function handler_admin_body_class( $classes )
		{
			if( ! $this->custom_layout_enabled() )
			{
				$classes .= ' avia-custom-layout-disabled';
			}
			else
			{
				$classes .= ' avia-custom-layout-enabled';
			}

			return $classes;
		}

		/**
		 * Add a notice to backend for users
		 *
		 * @since 6.0
		 * @param array $params
		 * @return array
		 */
		public function handler_avf_builder_button_params( $params )
		{
			if( ! $this->custom_layout_enabled() )
			{
				return $params;
			}

			if( avia_backend_get_post_type() == $this->get_post_type() )
			{
				$params['noteclass'] = 'av-notice av-only-active';

				$params['note']  = '<ul>';
				$params['note'] .=		'<li>';
				$params['note'] .=			__( 'To see real custom field data during designing select an &quot;Underlying Entry ID&quot; in metabox &quot;Enfold Custom Layout Settings&quot;', 'avia_framework' );
				$params['note'] .=		'</li>';
				$params['note'] .=		'<li>';
				$params['note'] .=			__( 'Do not use fullwidth elements like &quot;Color Section&quot;, &quot;Grid Row&quot;, &quot;Tab Section&quot;,... when you want to use this layout in posts with sidebars - this might break layout', 'avia_framework' );
				$params['note'] .=		'</li>';
				$params['note'] .= '</ul>';
			}

			return $params;
		}

		/**
		 * Add custom columns
		 *
		 * @since 6.0
		 * @param array $columns
		 * @return array
		 */
		public function handler_edit_alb_custom_layout_columns( $columns )
		{
			$newcolumns = [
							'cb'				=> '',
							'content_image'		=> __( 'Image', 'avia_framework' ),
							'title'				=> __( 'Title', 'avia_framework' ),
							'content_excerpt'	=> __( 'Description (=Excerpt)', 'avia_framework' ),
//							'content_cats'		=> __( 'Categories', 'avia_framework' )
						];

			return array_merge( $newcolumns, $columns );
		}

		/**
		 * Add custom values to columns
		 *
		 * @since 6.0
		 * @param string $column
		 * @param int $post_id
		 */
		public function handler_pages_custom_column( $column, $post_id )
		{
			global $post;

			switch( $column )
			{
				case 'content_image':
					if( has_post_thumbnail( $post->ID ) )
					{
						echo get_the_post_thumbnail( $post->ID, 'widget' );
					}
					break;

				case 'content_cats':
					echo get_the_term_list( $post_id, $this->get_taxonomy(), '', ', ', '' );
					break;

				case 'content_excerpt':
					echo '<p class="avia-element-tooltip">' . esc_html( trim( $post->post_excerpt ) ) . '</p>';
					break;
			}
		}

		/**
		 * Filter to force usage of ALB for custom layout posts. This will also hide the switch button with CSS
		 *
		 * @since 6.0
		 * @param boolean $force_alb
		 * @param WP_Post $post
		 * @return boolean
		 */
		public function handler_avf_force_alb_usage( $force_alb, $post )
		{
			//	security check
			if( ! $post instanceof WP_Post )
			{
				return $force_alb;
			}

			/**
			 * e.g. force all posts with this posttype to use ALB
			 */
			if( $this->get_post_type() == $post->post_type )
			{
				$force_alb = true;
			}

			return $force_alb;
		}

		/**
		 * Enable all shortcode buttons on post edit screen for this post type except av_custom_layout and av_postcontent
		 *
		 * @since 6.0
		 * @param boolean $disabled
		 * @param array $shortcode
		 * @return boolean
		 */
		public function handler_avf_shortcode_insert_button_backend_disabled( $disabled, $shortcode )
		{
			if( $this->is_edit_custom_layout_page() )
			{
				if( in_array( $shortcode['shortcode'], [ 'av_custom_layout', 'av_postcontent', 'av_sc_page_split' ] ) )
				{
					$disabled = true;
				}
				else
				{
					//	force activation of all buttons !!!
					$disabled = false;
				}
			}

			return $disabled;
		}

		/**
		 * Add post type to display CPT in selectbox for ALB element
		 *
		 * @since 6.0
		 * @param array $post_types
		 * @return array
		 */
		public function handler_avf_custom_layout__post_types( array $post_types )
		{
			if( $this->custom_layout_enabled() )
			{
				/**
				 * hide in ALB modal popup when editing a custom layout page
				 * This is actually only a fallback in case user adds shortcode manually. By default the button is disabled.
				 */
				if( ! ( isset( $_REQUEST['avia_request'] ) && isset( $_REQUEST['post_type'] ) && 'true' == $_REQUEST['avia_request'] && $_REQUEST['post_type'] == $this->get_post_type() ) )
				{
					return $post_types;
				}
			}

			return array_diff( $post_types, [ $this->get_post_type() ] );
		}

		/**
		 * Save custom layout relevant data of the post in backend
		 *
		 * @since 6.0
		 * @param WP_Post $post
		 **/
		public function handler_save_post_meta_box( $post )
		{
//			global $post;

			if( ! $post instanceof WP_Post )
			{
				return;
			}

			$box_elements = $this->get_box_elements();

			foreach( $box_elements as $box )
			{
				if( ! isset( $box['id'] ) )
				{
					continue;
				}

				if( ! isset( $_POST[ $box['id'] ] ) )
				{
					$_POST[ $box['id'] ] = '';
				}

				//	check for multiple select box
				if( is_array( $_POST[ $box['id'] ] ) )
				{
					$_POST[ $box['id'] ] = implode( ',', $_POST[ $box['id'] ] );
				}

				update_post_meta( $post->ID , $box['id'], $_POST[ $box['id'] ] );
			}
		}

		/**
		 * Checks if we are on the edit screen for the element (new or edit).
		 *
		 * @since 6.0
		 * @return boolean
		 */
		public function is_edit_custom_layout_page()
		{
			if( is_bool( $this->is__edit_custom_layout_page ) )
			{
				return $this->is__edit_custom_layout_page;
			}

			$this->is__edit_custom_layout_page = false;

			if( ! is_admin() && ! wp_doing_ajax() )
			{
				return $this->is__edit_custom_layout_page;
			}

			if( function_exists( 'get_current_screen' ) )
			{
				$screen = get_current_screen();

				if( ! $screen instanceof WP_Screen )
				{
					return $this->is__edit_custom_layout_page;
				}

				if( $screen->base == 'post' && $screen->post_type == $this->get_post_type() )
				{
					$this->is__edit_custom_layout_page = true;
				}

				return $this->is__edit_custom_layout_page;
			}

			/**
			 * Fallback if called too early
			 * ============================
			 */
			$this->is__edit_custom_layout_page = null;

			if( strpos( $_SERVER['REQUEST_URI'], 'post-new.php' ) !== false )
			{
				if( isset( $_REQUEST['post_type'] ) && $this->get_post_type() == $_REQUEST['post_type'] )
				{
					return true;
				}
			}
			else if( strpos( $_SERVER['REQUEST_URI'], 'post.php' ) !== false )
			{
				if( isset( $_REQUEST['action'] ) && 'edit' == $_REQUEST['action'] && isset( $_REQUEST['post'] ) )
				{
					$post = $this->get_post( $_REQUEST['post'] );

					if( $post instanceof WP_Post && $this->get_post_type() == $post->post_type )
					{
						return true;
					}
				}
			}

			return false;
		}

		/**
		 * Wrapper function for default WP function get_post that is not hooked by e.g. WPML
		 *
		 * @since 6.0
		 * @param int $post_id
		 * @param boolean $force_original			force to load requested ID and not a translated
		 * @return WP_Post|false
		 */
		protected function get_post( $post_id, $force_original = false )
		{
			$args = array(
						'numberposts'		=> 1,
						'include'			=> array( $post_id ),
						'post_type'			=> $this->get_post_type(),
						'suppress_filters'	=> false
					);

			/**
			 * Allows e.g. WPML to reroute to translated object
			 */
			if( false === $force_original )
			{
				$posts = get_posts( $args );
				$post = is_array( $posts ) && count( $posts ) > 0 ? $posts[0] : false;
			}
			else
			{
				$post = get_post( $post_id );
			}

			return $post instanceof WP_Post ? $post : false;
		}

		/**
		 * Checks if post is a dynamic content post type
		 *
		 * @since 6.0
		 * @return boolean
		 */
		public function is_custom_layout( $post )
		{
			if( ! $post instanceof WP_Post )
			{
				return false;
			}

			return $this->get_post_type() == $post->post_type;
		}

		/**
		 * Get ALB Layout Builder Metabox title
		 *
		 * @since 6.0
		 * @return string
		 */
		public function alb_metabox_title()
		{
			return ' - ' . __( 'Custom Layout', 'avia_framework' );
		}

		/**
		 * Get Backend Metabox key - needed for input elements
		 *
		 * @since 6.0
		 * @return string
		 */
		public function alb_metabox_key()
		{
			return aviaCustomLayout::METABOX_ID;
		}

		/**
		 * Function called by the metabox class that creates the interface in your wordpress backend -
		 * Output the metabox below the normal Texteditor
		 *
		 * @since 6.0
		 * @param array $element
		 * @param array $box
		 * @return string
		 */
		public function custom_layout_settings_meta_panel( $element, $box )
		{
			global $post;

			if( ! $post instanceof WP_Post )
			{
				return '';
			}


			$output = '';

			$box_elements = $this->get_box_elements();

			/**
			 * calls the helping function based on value of 'type'
			 * based on ..\config-templatebuilder\avia-template-builder\php\class-meta-box.php create_meta_box()
			 */
			foreach( $box_elements as $element )
			{
				$content = '';

				if( $element['slug'] == $box['id'] )
				{
					$element['current_post'] = $post->ID;

					if( method_exists( 'AviaHtmlHelper', $element['type'] ) )
					{
						$content = AviaHtmlHelper::render_metabox( $element );
					}

					if( ! empty( $content ) )
					{
						if( ! empty( $element['nodescription'] ) )
						{
							$output .= $content;
						}
						else
						{
							$output .= '<div class="avia_scope avia_meta_box avia_meta_box_' . $element['type'] . ' meta_box_' . $box['context'] . '">';
							$output .=		$content;
							$output .= '</div>';
						}
					}
				}
			}

			return $output;
		}

		/**
		 * Initialise the array and return it
		 *
		 * @since 6.0
		 * @return array
		 */
		protected function get_box_elements()
		{
			if( is_array( $this->box_elements ) )
			{
				return $this->box_elements;
			}

			$this->box_elements = [];

			$name = __( 'Underlying Entry ID (Used Only For Preview !!)', 'avia_framework' );

			/**
			 * Filter to use an input box (recommended for very large sites)
			 *
			 * @since 6.0
			 * @param boolean $use_select
			 * @param string $context
			 * @return boolean
			 */
			if( apply_filters( 'avf_custom_layout_metabox_content', true, 'use_select' ) )
			{
				$post_types = AviaHelper::public_post_types();
				unset( $post_types[ $this->get_post_type() ] );

				$this->box_elements[] =

						[
							'slug'			=> $this->alb_metabox_key(),
							'name'			=> $name,
							'desc'			=> __( 'Select an underlying post to display real data. If nothing is selected, pseudo code will be displayed instead. Please save the post to update metabox content in database whenever you add or change a metabox content.', 'avia_framework' ),
							'id'			=> '_custom_layout_post_id',
							'type'			=> 'select',
							'std'			=> '',
							'class'			=> 'avia-style',
							'subtype'		=> 'grouped_posts',
							'post_status'	=> [ 'publish', 'draft' ],
							'post_types'	=> array_keys( $post_types ),
							'with_first'	=> true
						];
			}
			else
			{
				$this->box_elements[] =

						[
							'slug'		=> $this->alb_metabox_key(),
							'name'		=> $name,
							'desc'		=> __( 'Enter the ID of an underlying post to display real data. If left empty or ID does not exist pseudo code will be displayed instead. Please save the post to update metabox content in database whenever you add or change a metabox content.', 'avia_framework' ),
							'id'		=> '_custom_layout_post_id',
							'type'		=> 'input',
							'std'		=> '',
							'class'		=> 'avia-style'
						];
			}

			return $this->box_elements;
		}

	}

	/**
	 * Returns the main instance of aviaCustomLayout to prevent the need to use globals
	 *
	 * @since 6.0
	 * @return aviaCustomLayout
	 */
	function Avia_Custom_Layout()
	{
		return aviaCustomLayout::instance();
	}

	/**
	 * Activate filter and action hooks
	 *
	 * @since 6.0
	 */
	Avia_Custom_Layout();
}
