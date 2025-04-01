<?php
/**
 * Extends built in CPT functionality - allows better integration of ACF plugin CPT's
 *
 * @since 6.0
 * @added_by Günter
 */
if( ! defined( 'AVIA_FW' ) ) { exit( 'No direct script access allowed' ); }


if( ! class_exists( 'aviaCPT', false ) )
{
	class aviaCPT
	{
		const WP_OPTIONS = 'avia-cpt-support';

		/**
		 *
		 * @since 6.0
		 * @var aviaCPT
		 */
		static protected $_instance = null;

		/**
		 * @since 6.0
		 * @var WP_Post_Type[]|null
		 */
		protected $pt_objects;

		/**
		 * Options array:
		 *
		 *			'default_terms'		=>		array (   [cpt_slug][tax_slug] => term_id   )
		 *
		 *			'columns_show'		=>		array (   [cpt_slug] => ['show' | '']    )
		 *
		 * @since 6.0
		 * @var array
		 */
		protected $cpt_options;

		/**
		 *
		 * @since 6.0
		 * @var array
		 */
		protected $metabox_layout_cpts;

		/**
		 * Return the instance of this class
		 *
		 * @since 6.0
		 * @return aviaCPT
		 */
		static public function instance()
		{
			if( is_null( aviaCPT::$_instance ) )
			{
				aviaCPT::$_instance = new aviaCPT();
			}

			return aviaCPT::$_instance;
		}

		/**
		 * @since 6.0
		 */
		public function __construct()
		{
			$this->pt_objects = null;

			$this->cpt_options = get_option( aviaCPT::WP_OPTIONS );
			if( ! is_array( $this->cpt_options ) )
			{
				$this->cpt_options = [];
			}

			$this->metabox_layout_cpts = [];

			add_action( 'admin_init', [ $this, 'handler_wp_admin_init'], 5000 );
			add_action( 'save_post', [ $this, 'handler_wp_save_post'], 5000, 3 );

			add_filter( 'avf_metabox_layout_post_types', [ $this, 'handler_avf_metabox_layout_post_types' ], 1000, 1 );
		}

		/**
		 * @since 6.0
		 */
		public function __destruct()
		{
			unset( $this->pt_objects );
			unset( $this->cpt_options );
			unset( $this->metabox_layout_cpts );
		}

		/**
		 * @since 6.0
		 */
		protected function activate_table_filters()
		{
			/**
			 * Allows to skip modifications of cpt tables
			 *
			 * @since 6.0
			 * @param boolean $no_filters
			 * @param boolean
			 */
			if( false !== apply_filters( 'avf_cpt_support_no_table_filters', false ) )
			{
				return;
			}

			$pts = $this->get_cpt_support_post_types();

			foreach( $pts as $pt_name => $pt_obj )
			{
				$modify_table = ! isset( $pt_obj->cpt_edit_table_cols ) || false !== $pt_obj->cpt_edit_table_cols;

				if( ! $modify_table )
				{
					continue;
				}

				add_filter( "manage_edit-{$pt_name}_columns", [ $this, 'handler_wp_table_columns' ], 5000, 1 );
				add_action( "manage_{$pt_name}_posts_custom_column", [ $this, 'handler_wp_custom_column' ], 5000, 2 );
			}
		}

		/**
		 * Handles saving of setting page and output of selectboxes
		 *
		 * @since 6.0
		 */
		public function handler_wp_admin_init()
		{
			if( empty( $this->get_cpt_support_post_types() ) )
			{
				return;
			}

			$this->activate_table_filters();
			$this->save_settings_page();

			//	only add, if we have CPT's otherwise we get a headline only
			if( ! empty( $this->get_cpt_support_post_types() ) )
			{
				add_settings_section( 'avia-settings-writing-cpts', __( 'Custom Post Type Settings', 'avia_framework' ), [ $this, 'handler_wp_settings_writing'], 'writing' );
			}
		}

		/**
		 * Add layout metabox to backend editor
		 * For non ACF post types add 'avia_layout_settings' to supported features.
		 *
		 * @since 6.0
		 * @param array $post_layout_types
		 * @return array
		 */
		public function handler_avf_metabox_layout_post_types( $post_layout_types )
		{
			$cpts = $this->get_cpt_support_post_types();

			foreach( $cpts as $pt_name => $cpt_obj )
			{
				if( post_type_supports( $pt_name, 'avia_layout_settings' ) )
				{
					$post_layout_types[] = $pt_name;
				}
			}

			$this->metabox_layout_cpts = $post_layout_types;

			return $post_layout_types;
		}

		/**
		 * Returns all post types that have been registered with filter 'avf_metabox_layout_post_types'
		 * Mainly intended for ACF post types which have a selectbox in advanced options.
		 *
		 * @since 6.0
		 * @return array
		 */
		public function get_registered_metabox_layout_post_types()
		{
			return $this->metabox_layout_cpts;
		}

		/**
		 * Save our selectbox values to DB
		 *
		 * @since 6.0
		 */
		protected function save_settings_page()
		{
			if( defined( 'DOING_AJAX' ) && DOING_AJAX )
			{
				return;
			}

			//	security vulnerabilities check: avoid to change settings by non authenticated users
			if( ! current_user_can( 'manage_options' ) )
			{
				return;
			}

			//	to avoid breaking 3rd party plugins like WooCommerce we need to check if we are on an options page
			global $pagenow;

			if( ! isset( $_REQUEST['avia-options-writing-nonce'] ) )
			{
				return;
			}

			if( ! in_array( $pagenow, [ 'options.php', 'options-writing.php' ] ) )
			{
				return;
			}

			if( $pagenow == 'options.php' )
			{
				if( ! isset( $_REQUEST['option_page'] ) || $_REQUEST['option_page'] != 'writing' )
				{
					return;
				}

				if( ! isset( $_REQUEST['action'] ) || $_REQUEST['action'] != 'update' )
				{
					return;
				}
			}

			//	this check will break WooCommerce Product Table, if checks before are not done !!!
			if( false === check_ajax_referer( 'avia-options-writing-nonce', 'avia-options-writing-nonce', false ) )
			{
				return;
			}

			//	we rebuild the array
			$defaults = [
							'default_terms'	=> [],
							'columns_show'	=> []
						];

			if( isset( $_REQUEST['avia-cpts-tax-default-terms'] ) && is_array( $_REQUEST['avia-cpts-tax-default-terms'] ) )
			{
				$defaults['default_terms'] = $_REQUEST['avia-cpts-tax-default-terms'];
			}

			if( isset( $_REQUEST['avia-cpts-tax-show'] ) && is_array( $_REQUEST['avia-cpts-tax-show'] ) )
			{
				$defaults['columns_show'] = $_REQUEST['avia-cpts-tax-show'];
			}

			$this->cpt_options = $defaults;

			update_option( aviaCPT::WP_OPTIONS, $defaults );
		}

		/**
		 * Handler to output WP option settings page
		 *
		 * @since 6.0
		 * @param array $section
		 */
		public function handler_wp_settings_writing( $section )
		{
			if( $section['id'] != 'avia-settings-writing-cpts' )
			{
				return;
			}

			$pts = $this->get_cpt_support_post_types();

			//	fallback only - handler should not be called to avoid dispay only a headline
			if( empty( $pts ) )
			{
				return;
			}

			/**
			 * Allows to create custom backend output
			 *
			 * @since 6.0
			 * @param boolean $shortcircut
			 * @param array $section
			 * @param aviaCPT $this
			 * @return boolean
			 */
			if( false !== apply_filters( 'avf_cpt_wp_settings_writing', false, $section, $this ) )
			{
				return;
			}

			$intro  = '<p class="av-cpt-intro">';
			$intro .=	__( 'Select to extend CPTs (Custom Post Types): show taxonomies and thumbnail in post type edit table, select a default term for CPT posts.', 'avia_framework' ) . ' ';
			$intro .=	__( 'This default term will always be set when you save a post without manually selecting a term.', 'avia_framework' ) . '<br />';
			$intro .=	'<strong>';
			$intro .=		__( 'We recommend to make changes here only for CPTs that you add e.g. with ACF plugin to get a WP similar behaviour that you are used from WP posts. Making changes to CPTs from other plugins might lead to unexpected results.', 'avia_framework' );
			$intro .=	'</strong>';
			$intro .= '</p>';

			$intro_printed = false;

			foreach( $pts as $pt_name => $pt_obj )
			{
				$modify_table = ! isset( $pt_obj->cpt_edit_table_cols ) || false !== $pt_obj->cpt_edit_table_cols;
				$tax_objs = get_object_taxonomies( $pt_name, 'objects' );

				if( empty( $tax_objs ) && ! $modify_table )
				{
					continue;
				}

				$name_show = "avia-cpts-tax-show[{$pt_name}]";

				$term_rows = '';

				foreach( $tax_objs as $tax_name => $tax_obj )
				{
					$args = [
								'taxonomy' => $tax_name,
								'hide_empty' => false
						];

					$terms = get_terms( $args );

					if( empty( $terms ) )
					{
						continue;
					}

					$name_default = "avia-cpts-tax-default-terms[{$pt_name}][{$tax_name}]";

					$value = isset( $this->cpt_options['default_terms'][ $pt_name ][ $tax_name ] ) ? $this->cpt_options['default_terms'][ $pt_name ][ $tax_name ] : '';

					$options = '<option class="level-0" value="" ' . selected( '', $value, false ) . '>' . __( 'No default', 'avia_framework' ) . '</option>';

					foreach( $terms as $term )
					{
						$options .= '<option class="level-0" value="' . $term->term_id . '" ' . selected( $term->term_id, $value, false ) . '>' . $term->name . '</option>';
					}

					$term_rows .=	'<tr>';
					$term_rows .=		'<th scope="row">';
					$term_rows .=			'<label for="' . $name_default . '">' . __( 'Default', 'avia_framework' ) . ' ' . $tax_obj->label . '</label>';
					$term_rows .=		'</th>';
					$term_rows .=		'<td>';
					$term_rows .=			'<select id="' . $name_default . '" class="postform" name="' . $name_default . '">';
					$term_rows .=				$options;
					$term_rows .=			'</select>';
					$term_rows .=		'<td>';
					$term_rows .=	'</tr>';
				}

				if( '' == $term_rows && ! $modify_table )
				{
					continue;
				}

				if( ! $intro_printed )
				{
					echo $intro;
					$intro_printed = true;
				}

				echo '<h3 class="title av-cpt-label cpt-' . $pt_name . '">' . __( '***  Custom Post Type:', 'avia_framework' ) . ' ' . $pt_obj->label . '</h3>';

				echo '<table class="form-table" role="presentation">';
				echo	$term_rows;

				if( $modify_table )
				{
					$value = isset( $this->cpt_options['columns_show'][ $pt_name ] ) ? $this->cpt_options['columns_show'][ $pt_name ] : '';

					echo	'<tr>';
					echo		'<th scope="row">' . $pt_obj->label . ' ' . __( 'Edit Table', 'avia_framework' ) . '</th>';
					echo		'<td>';
					echo			'<select id="' . $name_show . '" class="postform" name="' . $name_show . '">';
					echo				'<option class="level-0" value="" ' . selected( '', $value, false ) . '>' . __( 'Do not change table columns', 'avia_framework' ) . '</option>';
					echo				'<option class="level-0" value="change" ' . selected( 'change', $value, false ) . '>' . __( 'Add columns (taxonomy with terms, thumbnail)', 'avia_framework' ) . '</option>';
					echo			'</select>';
					echo		'<td>';
					echo	'</tr>';
				}

				echo '</table>';
			}

			if( $intro_printed )
			{
				echo '<input type="hidden" name="avia-options-writing-nonce" value="' . wp_create_nonce ( 'avia-options-writing-nonce' ) . '" />';
			}
		}

		/**
		 * We check if we need to set default term for a CPT.
		 * This is similar to post category handling that a "default" category is set if no category is selected for a post.
		 *
		 * @since 6.0
		 * @param int $post_id
		 * @param WP_Post $post_saved
		 * @param boolean $update
		 */
		public function handler_wp_save_post( $post_id, $post_saved, $update )
		{
			global $post;

			//	skip revisions
			if( ! $post instanceof WP_Post )
			{
				return;
			}

			if( $post->ID != $post_id )
			{
				return;
			}

			if( ! isset( $this->cpt_options['default_terms'][ $post->post_type ] ) )
			{
				return;
			}

			$default_terms = $this->cpt_options['default_terms'][ $post->post_type ];

			$tax_objs = get_object_taxonomies( $post->post_type, 'objects' );
			if( empty( $tax_objs ) )
			{
				return;
			}

			foreach( $tax_objs as $tax_name => $tax_obj )
			{
				if( ! isset( $default_terms[ $tax_name ] ) || empty( $default_terms[ $tax_name ] ) )
				{
					continue;
				}

				$terms = wp_get_post_terms( $post_id, $tax_name );
				if( ! empty( $terms ) )
				{
					continue;
				}

				if( term_exists( (int) $default_terms[ $tax_name ] ) )
				{
					wp_set_object_terms( $post_id, (int) $default_terms[ $tax_name ], $tax_name, true );
				}
			}
		}

		/**
		 * Add thumbnail column and taxonomie columns sorted by label
		 *
		 * @since 6.0
		 * @param array $columns
		 * @return array
		 */
		public function handler_wp_table_columns( $columns )
		{
			$post_type = str_replace( [ 'manage_edit-', '_columns' ], [ '', '' ], current_filter() );

			$pts = $this->get_cpt_support_post_types();
			if( empty( $pts ) || ! isset( $pts[ $post_type ] ) )
			{
				return $columns;
			}

			$pt_obj = $pts[ $post_type ];
			if( isset( $pt_obj->cpt_edit_table_cols ) && false === $pt_obj->cpt_edit_table_cols )
			{
				return $columns;
			}

			if( ! isset( $this->cpt_options['columns_show'][ $post_type ] ) || $this->cpt_options['columns_show'][ $post_type ] != 'change' )
			{
				return $columns;
			}

			$new_columns = [];
			$new_columns['cb'] = '<input type="checkbox" />';

			if( post_type_supports( $post_type, 'thumbnail' ) )
			{
				$new_columns['avia-cpt-image'] = __( 'Image', 'avia_framework' );
			}

			$new_columns['title'] = __( 'Title', 'avia_framework' );

			$tax_objs = get_object_taxonomies( $post_type, 'objects' );
			if( ! empty( $tax_objs ) )
			{
				$sort_tax = [];
				foreach( $tax_objs as $tax_name => $tax_obj )
				{
					$sort_tax[ "avia-tax-{$tax_name}" ] = $tax_obj->label;
				}

				asort( $sort_tax, SORT_STRING );

				foreach( $sort_tax as $col_id => $col_val )
				{
					$new_columns[ $col_id ] = $col_val;
				}
			}

			add_action( 'admin_footer', [ $this, 'handler_wp_admin_footer' ] );

			return array_merge( $new_columns, $columns );
		}

		/**
		 * Add thumbnails and taxonomie terms in columns
		 *
		 * @since 6.0
		 * @param string $column_name
		 * @param int $post_id
		 */
		public function handler_wp_custom_column( $column_name, $post_id )
		{
//			global $post;

			if( 'avia-cpt-image' == $column_name )
			{
				if( has_post_thumbnail( $post_id ) )
				{
					echo get_the_post_thumbnail( $post_id, 'widget' );
				}

				return;
			}

			$pos = strpos( $column_name, 'avia-tax-' );
			if( false === $pos || 0 != $pos )
			{
				return;
			}

			$tax_slug = str_replace( 'avia-tax-', '', $column_name );

			echo get_the_term_list( $post_id, $tax_slug, '', ', ','' );

			return;
		}

		/**
		 * Add a custom style for featured images in admin list table
		 *
		 * @since 6.0
		 */
		public function handler_wp_admin_footer()
		{
			?>
				<style>
					.widefat thead tr th#avia-cpt-image {
						width: 45px;
					}
				</style>
			<?php
		}

		/**
		 * Returns public non built in post types that are supported by our class
		 * (remove cpt's that handle their own taxonomies in edit post type page).
		 * Main usage is for CPT's created with e.g. ACF plugin by user.
		 *
		 * @since 6.0
		 * @return WP_Post_Type[]
		 */
		public function get_cpt_support_post_types()
		{
			if( ! is_array( $this->pt_objects ) )
			{
				$args = [
						'public'	=> true,
						'_builtin'	=> false
					];

				$pt_objs = get_post_types( $args, 'objects' );

				/**
				 * Filter supported post types
				 *
				 * @used_by				..\config-woocommerce\config.php avia_woocommerce_cpt_support_post_types()		10
				 * @used_by				..\config-events-calendar\config.php  avia_events_cpt_support_post_types()		10
				 * @used_by				aviaElementTemplates				10
				 *
				 * @since 6.0
				 * @param array			WP_Post_Type
				 * @return array
				 */
				$this->pt_objects = apply_filters( 'avf_cpt_support_post_types', $pt_objs );
			}

			return $this->pt_objects;
		}
	}

	/**
	 * Returns the main instance of aviaSVGImages to prevent the need to use globals.
	 *
	 * @since 4.8.7
	 * @return aviaCPT
	 */
	function avia_CPT()
	{
		return aviaCPT::instance();
	}

	//	activate class
	avia_CPT();

}
