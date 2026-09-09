<?php
if( ! defined( 'AVIA_FW' ) )	{	exit( 'No direct script access allowed' );	}

if( ! class_exists( 'avia_wp_import', false ) )
{

	class avia_wp_import extends WP_Import
	{
	//	var $preStringOption;
	//	var $results;
	//	var $getOptions;
	//	var $saveOptions;
	//	var $termNames;

		/**
		 * Menus assigned to the theme locations of the demo:
		 *
		 *		array( location => array( 'term_id' => .., 'slug' => .., 'name' => .. ) )
		 *
		 * Empty for demos exported prior to 8.0 - those rely on the menu naming convention.
		 *
		 * @since 8.0
		 * @var array
		 */
		protected $demo_nav_menu_locations = array();

		/**
		 * Ids of the demo posts that have been inserted by this import:
		 *
		 *		array( demo_post_id => true )
		 *
		 * Posts the importer matched to already existing posts of the user are not in here -
		 * we must not modify content the demo did not create.
		 *
		 * @since 8.0
		 * @var array
		 */
		protected $inserted_posts = array();

		/**
		 * @since 8.0
		 */
		public function __construct()
		{
			parent::__construct();

			add_filter( 'wp_import_post_data_processed', array( $this, 'handler_wp_import_post_data_processed' ), 10, 2 );
		}

		/**
		 * Filter is called for posts that are inserted only - posts matched to existing posts
		 * of the user do not pass here (see WP_Import::process_posts()).
		 *
		 * @since 8.0
		 * @param array $postdata
		 * @param array $post
		 * @return array
		 */
		public function handler_wp_import_post_data_processed( $postdata, $post )
		{
			if( ! empty( $post['post_id'] ) )
			{
				$this->inserted_posts[ (int) $post['post_id'] ] = true;
			}

			return $postdata;
		}

		/**
		 * Registers the other scheme for every remapped url before the parent rewrites
		 * post content.
		 *
		 * The remap is a literal string replace, so an attachment exported as
		 * http://example.com/x.jpg never matches content that links to
		 * https://example.com/x.jpg. In the Studio demo that mismatch is exactly why
		 * three element templates kept loading their images from kriesi.at although the
		 * files were shipped in the package and imported correctly - a customer's site
		 * silently hotlinked ours, and broke whenever those files moved.
		 *
		 * Only adds the counterpart of urls we already remap, so this can never touch a
		 * url the import did not create.
		 *
		 * @since 8.0
		 */
		public function backfill_attachment_urls()
		{
			if( ! empty( $this->url_remap ) && is_array( $this->url_remap ) )
			{
				foreach( $this->url_remap as $from => $to )
				{
					$other = ( 0 === strpos( $from, 'https://' ) )
								? 'http://' . substr( $from, 8 )
								: ( ( 0 === strpos( $from, 'http://' ) ) ? 'https://' . substr( $from, 7 ) : '' );

					if( '' !== $other && ! isset( $this->url_remap[ $other ] ) )
					{
						$this->url_remap[ $other ] = $to;
					}
				}
			}

			parent::backfill_attachment_urls();
		}

		/**
		 * Repoints asset urls in the imported theme options at the files this import
		 * actually created.
		 *
		 * Nothing rewrote urls inside the options before, only inside content - so a demo
		 * exported with an absolute url in a setting shipped that url to every customer.
		 * The Studio demo carried a logo pointing at the machine it was authored on,
		 * which meant a broken logo on every site that imported it, plus three
		 * background images pointing at the same dead host.
		 *
		 * Matching is by file name against the attachments of this import, because the
		 * stale url does not have to be the demo's own address - it can be anything the
		 * person exporting happened to have in the field.
		 *
		 * Urls already pointing at this site are left alone, and a name we did not import
		 * is left alone too: a wrong url is better than a wrong picture.
		 *
		 * Attachments are imported before the options are saved (inc-avia-importer.php),
		 * so url_remap is populated by the time this runs.
		 *
		 * @since 8.0
		 * @param array $options
		 * @return array
		 */
		protected function remap_option_urls( array $options )
		{
			if( empty( $this->url_remap ) || ! is_array( $this->url_remap ) )
			{
				return $options;
			}

			$by_name = array();

			foreach( $this->url_remap as $old_url => $new_url )
			{
				$name = strtolower( basename( (string) parse_url( (string) $old_url, PHP_URL_PATH ) ) );

				if( '' !== $name && ! isset( $by_name[ $name ] ) )
				{
					$by_name[ $name ] = $new_url;
				}
			}

			if( empty( $by_name ) )
			{
				return $options;
			}

			$host = parse_url( home_url(), PHP_URL_HOST );
			$replaced = 0;

			array_walk_recursive( $options, function( &$value ) use ( $by_name, $host, &$replaced )
			{
				if( ! is_string( $value ) || '' === $value || ! preg_match( '#^https?://#i', $value ) )
				{
					return;
				}

				$value_host = parse_url( $value, PHP_URL_HOST );

				//	already ours - the import rewrote it, or the user set it
				if( ! $value_host || strtolower( $value_host ) === strtolower( (string) $host ) )
				{
					return;
				}

				$name = strtolower( basename( (string) parse_url( $value, PHP_URL_PATH ) ) );

				if( '' !== $name && isset( $by_name[ $name ] ) )
				{
					$value = $by_name[ $name ];
					$replaced ++;
				}
			} );

			if( $replaced > 0 && defined( 'WP_DEBUG' ) && WP_DEBUG )
			{
				error_log( "avia demo import: repointed {$replaced} option url(s) at imported files" );
			}

			return $options;
		}

		/**
		 * Handles import of options saved from a demo
		 *
		 * @since 4.8.2							support fot txt files including options similar to php file
		 * @param string $option_file				full path to php or txt file
		 * @param boolean $import_only
		 * @return boolean
		 */
		public function saveOptions( $option_file, $import_only = false )
		{
			//	Only the .txt options format is supported. Legacy .php option files are no longer
			//	included - the theme must never execute php that arrived inside a demo package.
			if( false !== strpos( $option_file, '.txt' ) )
			{
				$file_content = $this->read_txt_file_contents( $option_file );
				extract( $file_content );
			}

//			switch( $import_only )
//			{
//				case 'options':
//					$dynamic_pages = $dynamic_elements = false;
//					break;
//				case 'dynamic_pages':
//					$options = $dynamic_elements = false;
//					break;
//				case 'dynamic_elements':
//					$options = $dynamic_pages = false;
//					break;
//			}

			/**
			 * Demos exported with 8.0 and above ship the menus assigned to the theme locations.
			 * Stored for avia_wp_import::set_menus() which is called after the options are saved.
			 *
			 * @since 8.0
			 */
			if( ! empty( $nav_menu_locations ) )
			{
				$demo_locations = unserialize( base64_decode( $nav_menu_locations ), array( 'allowed_classes' => false ) );

				if( is_array( $demo_locations ) )
				{
					$this->demo_nav_menu_locations = $demo_locations;
				}
			}

//			if( ! isset( $options ) && ! isset( $dynamic_pages ) && ! isset( $dynamic_elements ) )
			if( ! isset( $options ) )
			{
				return false;
			}

			$options = unserialize( base64_decode( $options ), array( 'allowed_classes' => false ) );
//			$dynamic_pages = unserialize( base64_decode( $dynamic_pages ) );
//			$dynamic_elements = unserialize( base64_decode( $dynamic_elements ) );

			global $avia;

			if( ! isset( $database_option ) || ! is_array( $database_option ) )
			{
				$database_option = array();
			}

			if( is_array( $options ) )
			{
				foreach( $avia->option_pages as $page )
				{
					if( ! array_key_exists( $page['parent'], $options ) )
					{
						$database_option[ $page['parent'] ] = array();
					}
					else
					{
						$database_option[ $page['parent'] ] = $this->extract_default_values( $options[ $page['parent'] ], $page, $avia->subpages ) ;
					}
				}
			}

			if( ! empty( $database_option ) )
			{
				$database_option = $this->remap_option_urls( $database_option );

				update_option( $avia->option_prefix, $database_option );
			}

//			if( ! empty( $dynamic_pages ) )
//			{
//				update_option($avia->option_prefix.'_dynamic_pages', $dynamic_pages );
//			}
//
//			if( ! empty( $dynamic_elements ) )
//			{
//				update_option($avia->option_prefix.'_dynamic_elements', $dynamic_elements );
//			}

			if( ! empty( $fonts ) )
			{
				$this->import_iconfont( $fonts );
			}

			if( ! empty( $layerslider ) )
			{
				$this->import_layerslides( $layerslider );
			}


			if( ! empty( $widget_settings ) )
			{
				$widget_settings = unserialize( base64_decode( $widget_settings ), array( 'allowed_classes' => false ) );
				if( ! empty( $widget_settings ) )
				{
					foreach( $widget_settings as $key => $setting )
					{
						update_option( $key, $setting );
					}
				}
			}

		}

		/**
		 * As we now support txt for demo options file we have to extract the content and return the array
		 * To be backwards comp with already existing demos we support the old syntax:
		 *		$key = '......';
		 *
		 * @since 4.8.2
		 * @param string $file
		 * @return array
		 */
		protected function read_txt_file_contents( $file )
		{
			$content = @file_get_contents( $file );
			$content = trim( $content );

			if( empty( $content ) )
			{
				return array();
			}

			$content = str_replace( "\r", "\n", $content );
			$lines = explode( "\n", $content );

			$options = array();

			foreach( $lines as $key => $line )
			{
				$sep = strpos( $line, '=' );
				if( false ===  $sep )
				{
					continue;
				}

				$opt_key = trim( substr( $line, 0, $sep ) );
				$opt_val = trim( substr( $line, $sep + 1 ) );

				$opt_key = str_replace( '$', '', $opt_key );

				$len = strlen( $opt_val );

				if( strpos( $opt_val, '"' ) !== 0 )
				{
					continue;
				}
				if( strrpos( $opt_val, '";' ) !== ( $len - 2 ) )
				{
					continue;
				}

				$options[ $opt_key ] = substr( $opt_val, 1, $len - 3 );
			}

			if( empty( $options ) && false !== base64_decode( $content, true ) )
			{
				$options['options'] = $content;
			}

			return $options;
		}


		/**
		 *
		 * @param string $layerslider
		 */
		protected function import_layerslides( $layerslider )
		{
			@ini_set( 'max_execution_time', 300 );

			$slider = urlencode( $layerslider );
			$remoteURL = 'https://kriesi.at/themes/wp-content/uploads/avia-sample-layerslides/' . $slider . '.zip';

			$uploads = wp_upload_dir();
			$downloadPath = $uploads['basedir'] . '/lsimport.zip';

			// Download package
			$request = wp_remote_post( $remoteURL, array(
							'method'	=> 'POST',
							'timeout'	=> 300,
							'body'		=> array()
						));

			$zip = wp_remote_retrieve_body( $request );

			if( ! $zip )
			{
				die( __( "LayerSlider couldn't download your selected slider. Please check LayerSlider -> System Status for potential issues. The WP Remote functions may be unavailable or your web hosting provider has to allow external connections to our domain.", 'avia_framework' ) );
			}

			// Save package
			if( ! file_put_contents( $downloadPath, $zip ) )
			{
				die( __( "LayerSlider couldn't save the downloaded slider on your server. Please check LayerSlider -> System Status for potential issues. The most common reason for this issue is the lack of write permission on the /wp-content/uploads/ directory.", 'avia_framework' ) );
			}

			// Load importUtil & import the slider
			include LS_ROOT_PATH . '/classes/class.ls.importutil.php';
			$import = new LS_ImportUtil( $downloadPath );

			// Remove package
			if( file_exists( $downloadPath ) )
			{
				unlink( $downloadPath );
			}
		}


		/**
		 *
		 * @param string $new_fonts
		 */
		protected function import_iconfont( $new_fonts )
		{
			@ini_set( 'max_execution_time', 300 );

			//update iconfont option
			$key = 'avia_builder_fonts';
			$fonts_old = get_option( $key );

			if( empty( $fonts_old ) )
			{
				$fonts_old = array();
			}

			$new_fonts = unserialize( base64_decode( $new_fonts ), array( 'allowed_classes' => false ) );
			$merged_fonts = array_merge( $new_fonts , $fonts_old );
			$files_to_copy = array( 'config.json', 'FONTNAME.svg', 'FONTNAME.ttf', 'FONTNAME.eot', 'FONTNAME.woff', 'FONTNAME.woff2' );
			update_option( $key, $merged_fonts );

			$http 			= new WP_Http();
			$font_uploader 	= new avia_font_manager();
			$paths			= $font_uploader->paths;

			//if a temp dir already exists remove it and create a new one
			if( ! is_dir( $paths['tempdir'] ) )
			{
				$fontdir = avia_backend_create_folder( $paths['tempdir'], false );
				if( ! $fontdir )
				{
					echo __( 'Wasn\'t able to create the folder for font files', 'avia_framework' );
				}
			}

			//download iconfont files into uploadsfolder
			foreach( $new_fonts as $font_name => $font )
			{
				if( empty( $fonts_old[ $font_name ] ) )
				{
					//folder name
					$new_font_folder = trailingslashit( $paths['tempdir'] );

					//if a sub dir already exists remove it and create a new one
					if( is_dir( $new_font_folder ) )
					{
						$font_uploader->delete_folder( $new_font_folder );
					}

					$subpdir = avia_backend_create_folder( $new_font_folder, false );
					if( ! $subpdir )
					{
						echo __( 'Wasn\'t able to create sub-folder for font files', 'avia_framework' );
					}

					//iterate over files on remote server and create the same ones on this server
					foreach( $files_to_copy as $file_to_copy )
					{
						$file_to_copy 	= str_replace( 'FONTNAME', $font_name, $file_to_copy );
						$origin_url 	= $font['origin_folder'] . trailingslashit( $font['folder'] ) . $file_to_copy;
						$new_path		= trailingslashit( $new_font_folder ) . $file_to_copy;
						$headers 		= $http->request( $origin_url, array( 'stream' => true, 'filename' => $new_path ) );
					}

					//create a config file
					$font_uploader->font_name = $font_name;
					$font_uploader->create_config();
				}
			}
		}

		/**
		 *  Extracts the default values from the option_page_data array in case no database savings were done yet
		 *  The functions calls itself recursive with a subset of elements if groups are encountered within that array
		 *
		 * @param array $elements
		 * @param array $page
		 * @param array $subpages
		 * @return array
		 */
		public function extract_default_values( array $elements, array $page, array $subpages )
		{

			$values = array();

			foreach( $elements as $element )
			{
					if( isset( $element['type'] ) && ( $element['type'] == 'group') )
				{
					if( ! is_array( $element['std'] ) )
					{
						//	Fallback situation in case theme option std value is not an array
						$values[ $element['id'] ][0] = array();
					}
					else
					{
						$iterations = count( $element['std'] );

						for( $i = 0; $i < $iterations; $i++ )
						{
							$values[ $element['id'] ][ $i ] = $this->extract_default_values( $element['std'][ $i ], $page, $subpages );
						}
					}
				}
				else if( isset( $element['id'] ) )
				{
					if( ! isset($element['std'] ) )
					{
						$element['std'] = '';
					}

					if( $element['type'] == 'select' && ! is_array( $element['subtype'] ) )
					{
						if( ! isset( $element['taxonomy'] ) )
						{
							$element['taxonomy'] = 'category';
						}

						$values[ $element['id'] ] = $this->getSelectValues( $element['subtype'], $element['std'], $element['taxonomy'] );
					}
					else
					{
						$values[ $element['id'] ] = $element['std'];
					}
				}
			}

			return $values;
		}

		/**
		 * Filters the imported options:
		 *
		 * If filter_xxx is used the original options are kept and only options set in filter_xxx are copied.
		 * If skip_xxx is used the imported option values are replaced by the old ones.
		 *
		 * Filter has priority to skip.
		 *
		 * If no filter the original array is returned.
		 *
		 *
		 * $filter = array(
		 *			filter_tabs		=> parent:slug,parent:slug
		 *			filter_values	=> parent:option_name,parent:option_name
		 *			skip_tabs		=> parent:slug,parent:slug
		 *			skip_values		=> parent:option_name,parent:option_name
		 *		)
		 *
		 * @since 4.6.4
		 * @param array $database_option
		 * @param array $imported_options
		 * @param array $filter
		 * @return array
		 */
		public function filter_imported_options( array $database_option, array $imported_options, array $filter )
		{
			global $avia;

			if( empty( $filter ) )
			{
				return $database_option;
			}

			$filter_keys = array( 'filter_tabs', 'filter_values', 'skip_tabs', 'skip_values' );

			$filter_stat = false;

			foreach( $filter_keys as $key )
			{
				if( isset( $filter[ $key ] ) && trim( $filter[ $key ] ) != '' )
				{
					$filter_stat = false !== strpos( $key, 'filter' ) ? 'filter' : 'skip';
					break;
				}
			}

			if( false === $filter_stat )
			{
				return $database_option;
			}

			//	Cleanup array
			foreach( $filter_keys as $key )
			{
				if( isset( $filter[ $key ] ) && trim( $filter[ $key ] ) == '' )
				{
					unset( $filter[ $key ] );
				}
			}

			$avia_options = is_array( $avia->options ) ? $avia->options : array();
			if( 'filter' == $filter_stat )
			{
				$new_options = $avia_options;
			}
			else
			{
				$new_options = $database_option;
			}

			foreach( $filter_keys as $operation )
			{
				if( ! isset( $filter[ $operation ] ) )
				{
					continue;
				}

				$sources = explode( ',', $filter[ $operation ] );

				foreach( $sources as $source )
				{
					if( false === strpos( $source, ':' ) )
					{
						$source = ':' . $source;
					}

					$source = explode( ':', $source, 2 );

					$parent = trim( $source[0] );

					if( false !== strpos( $operation, 'value' ) )
					{
						$id = trim( $source[1] );

						$default = $this->get_std_value( $parent, $id );

						if( false !== strpos( $operation, 'filter' ) )
						{
							$new_options[ $parent ][ $id ] = isset( $database_option[ $parent ][ $id ] ) ? $database_option[ $parent ][ $id ] : $default;
						}
						else
						{
							$new_options[ $parent ][ $id ] = isset( $avia_options[ $parent ][ $id ] ) ? $avia_options[ $parent ][ $id ] : $default;
						}
						continue;
					}

					if( ! isset( $imported_options[ $parent ] ) || ! is_array( $imported_options[ $parent ] ) )
					{
						continue;
					}

					$subpage = trim( $source[1] );

					foreach( $imported_options[ $parent ] as $element )
					{
						if( isset( $element['id'] ) && isset( $element['slug'] ) && $element['slug'] == $subpage )
						{
							$id = $element['id'];
							$default = $this->get_std_value( $parent, $id );

							if( false !== strpos( $operation, 'filter' ) )
							{
								$new_options[ $parent ][ $id ] = isset( $database_option[ $parent ][ $id ] ) ? $database_option[ $parent ][ $id ] : $default;
							}
							else
							{
								$new_options[ $parent ][ $id ] = isset( $avia_options[ $parent ][ $id ] ) ? $avia_options[ $parent ][ $id ] : $default;
							}
						}
					}
				}
			}

			return $new_options;
		}

		/**
		 * Get the std value for an option as fallback in case element is missing
		 *
		 * @since 4.6.4
		 * @param string $parent
		 * @param string $id
		 * @return string
		 */
		protected function get_std_value( $parent, $id )
		{
			global $avia;

			if( ! isset( $avia->subpages[ $parent ] ) || ! is_array( $avia->subpages[ $parent ] ) )
			{
				return '';
			}

			$subpages = $avia->subpages[ $parent ];

			foreach( $avia->option_page_data as $key => $element )
			{
				if( isset( $element['slug'] ) && in_array( $element['slug'], $subpages ) )
				{
					if( isset( $element['id'] ) && $element['id'] == $id )
					{
						return ( isset( $element['std'] ) ) ? $element['std'] : '';
					}
				}
			}

			return '';
		}

		/**
		 *
		 * @param string $type
		 * @param string $name
		 * @param string $taxonomy
		 * @return string|int
		 */
		protected function getSelectValues( $type, $name, $taxonomy )
		{
			switch( $type )
			{
				case 'page':
				case 'post':
					/**
					 * An empty option value must not be looked up:
					 * WP_Query ignores an empty 'title' and would return the first page/post of the site
					 * (e.g. the privacy policy page becomes the blog page of the imported demo).
					 *
					 * @since 8.0
					 */
					if( ! is_scalar( $name ) || '' === trim( (string) $name ) )
					{
						return '';
					}

					$qa = [
							'post_type'					=> $type,
							'title'						=> $name,
							'post_status'				=> 'all',
							'posts_per_page'			=> 1,
							'no_found_rows'				=> true,
							'update_post_term_cache'	=> false,
							'update_post_meta_cache'	=> false,
							'orderby'					=> 'post_date ID',
							'order'						=> 'ASC'
						];

					$query = new WP_Query( $qa );

					if( ! empty( $query->post ) )
					{
						return $query->post->ID;
					}
					break;

				case 'cat':
					if( ! empty( $name ) )
					{
						$return = array();

						foreach( $name as $cat_name )
						{
							if( $cat_name )
							{
								if( ! $taxonomy )
								{
									$taxonomy = 'category';
								}
								$the_category = get_term_by( 'name', $cat_name, $taxonomy );

								if( $the_category )
								{
									$return[] = $the_category->term_id;
								}
							}
						}
						if( ! empty( $return ) )
						{
							if( ! isset( $return[1] ) )
							{
								 $return = $return[0];
							}
							else
							{
								$return = implode( ',', $return );
							}
						}
						return $return;
					}
					break;
			}
		}

		/*

		/**
		 * Renames existing menus so that newly added menu items are not appended
		 */
		public function rename_existing_menus()
		{
			$menus = wp_get_nav_menus();

			if( ! empty( $menus) )
			{
				//wp_delete_nav_menu($menu->slug);

				foreach( $menus as $menu )
				{
					$updated = false;
					$i = 0;

					while( ! is_numeric( $updated ) ) //try to update the menu name. if it exists increment the number and thereby change the name
					{
						$i++;
						$args['menu-name'] = __( 'Previously used menu','avia_framework' ) . ' ' . $i;
						$args['description'] = $menu->description;
						$args['parent'] = $menu->parent;

						$updated = wp_update_nav_menu_object( $menu->term_id, $args ); //return a number on success or wp_error object if menu name exists

						//fallback, prevents infinite loop if something weird happens
						if( $i > 100 )
						{
							$updated = 1;
						}
					}
				}
			}
		}

		/**
		 * Assigns the imported menus to the theme locations.
		 *
		 * Demos exported with 8.0 and above tell us the theme locations they use.
		 * For all other locations - and for demos exported prior to 8.0 - we fall back
		 * to the menu naming convention (see avia_wp_import::guessed_menu_locations()).
		 *
		 * @since 8.0							theme locations of a demo have priority over the naming convention
		 */
		public function set_menus()
		{
			//get all menu locations currently assigned on the users site
			$locations = get_theme_mod( 'nav_menu_locations' );

			if( ! is_array( $locations ) )
			{
				$locations = array();
			}

			$exported = $this->exported_menu_locations();
			$guessed = $this->guessed_menu_locations( array_keys( $exported ) );

			//update the theme
			set_theme_mod( 'nav_menu_locations', array_merge( $locations, $guessed, $exported ) );
		}

		/**
		 * Returns the theme locations the demo has exported, mapped to the menus on the users site.
		 *
		 * Menus not found and locations not registered by the current theme are skipped.
		 *
		 * @since 8.0
		 * @return array				array( location => term_id )
		 */
		protected function exported_menu_locations()
		{
			if( empty( $this->demo_nav_menu_locations ) )
			{
				return array();
			}

			$registered = get_registered_nav_menus();
			$locations = array();

			foreach( $this->demo_nav_menu_locations as $location => $menu )
			{
				if( ! isset( $registered[ $location ] ) )
				{
					continue;
				}

				$term_id = $this->find_imported_menu( $menu );

				if( 0 != $term_id )
				{
					$locations[ $location ] = $term_id;
				}
			}

			return $locations;
		}

		/**
		 * Finds a menu of the demo on the users site.
		 *
		 * Term ids change during import, therefore we first ask the importer for the new id
		 * and fall back to the slug and the name of the menu.
		 *
		 * @since 8.0
		 * @param array|string $menu			array( 'term_id' => .., 'slug' => .., 'name' => .. )
		 * @return int							0 if not found
		 */
		protected function find_imported_menu( $menu )
		{
			if( ! is_array( $menu ) )
			{
				$menu = array( 'name' => $menu );
			}

			$identifiers = array();

			//	the importer maps the term ids of the demo to the ids on the users site
			$term_id = isset( $menu['term_id'] ) ? (int) $menu['term_id'] : 0;

			if( 0 != $term_id && isset( $this->processed_terms[ $term_id ] ) )
			{
				$identifiers[] = $this->processed_terms[ $term_id ];
			}

			foreach( array( 'slug', 'name' ) as $key )
			{
				if( ! empty( $menu[ $key ] ) && is_string( $menu[ $key ] ) )
				{
					$identifiers[] = $menu[ $key ];
				}
			}

			foreach( $identifiers as $identifier )
			{
				$nav_menu = wp_get_nav_menu_object( $identifier );

				if( ! empty( $nav_menu->term_id ) )
				{
					return (int) $nav_menu->term_id;
				}
			}

			return 0;
		}

		/**
		 * Returns the theme locations we can identify by the name of the imported menus.
		 *
		 * A partial match like 'Main Menu', 'Main' or 'Secondary' is enough - the name of the menu
		 * must be contained in the name of the theme location ($avia_config['nav_menus']).
		 *
		 * @since 8.0							extracted from avia_wp_import::set_menus()
		 * @param array $skip_locations			locations the demo has exported
		 * @return array						array( location => term_id )
		 */
		protected function guessed_menu_locations( array $skip_locations )
		{
			global $avia_config;

			//get all created menus
			$avia_menus = wp_get_nav_menus();

			if( empty( $avia_menus ) || empty( $avia_config['nav_menus'] ) )
			{
				return array();
			}

			/**
			 * Menus with no items are not candidates while a filled one exists.
			 *
			 * A demo that ships an empty "Main Menu" placeholder next to the real
			 * "Main Navigation" used to lose its navigation here: the theme registers the
			 * location as "Main Menu", the name matches the placeholder exactly, and the
			 * site came out with no menu at all. An empty menu is never what a demo meant
			 * to assign.
			 */
			$candidates = array();

			foreach( $avia_menus as $avia_menu )
			{
				if( is_object( $avia_menu ) && ! empty( $avia_menu->count ) )
				{
					$candidates[] = $avia_menu;
				}
			}

			if( empty( $candidates ) )
			{
				//	nothing has items - fall back to the full list rather than assign nothing
				$candidates = array_filter( $avia_menus, 'is_object' );
			}

			$locations = array();

			//	the first registered location is the theme's main menu slot
			$keys = array_keys( $avia_config['nav_menus'] );
			$primary_location = reset( $keys );

			foreach( $avia_config['nav_menus'] as $key => $nav_menu )
			{
				if( in_array( $key, $skip_locations ) )
				{
					continue;
				}

				$location_name = isset( $nav_menu['html'] ) ? $nav_menu['html'] : $nav_menu;
				$location_name = strtolower( $location_name );

				foreach( $candidates as $avia_menu )
				{
					if( false !== strpos( $location_name, strtolower( $avia_menu->name ) ) )
					{
						$locations[ $key ] = $avia_menu->term_id;
					}
				}

				/**
				 * Nothing matched by name. With exactly one menu carrying items there is
				 * no ambiguity about what the demo meant, so use it instead of leaving
				 * the location empty - which is how "Main Navigation" is picked up for
				 * demos exported before the locations were part of the package.
				 *
				 * Primary location only. Falling back for every location would drop the
				 * same menu into the secondary and footer slots as well, which no demo
				 * asks for - they leave those empty unless they say otherwise.
				 */
				if( ! isset( $locations[ $key ] ) && $key === $primary_location && 1 === count( $candidates ) )
				{
					$menu = reset( $candidates );
					$locations[ $key ] = $menu->term_id;
				}
			}

			return $locations;
		}

		/**
		 * ALB elements store term and post ids in their shortcode attributes (e.g. categories='26').
		 * WP assigns new ids to the imported terms - and to posts when the id of the demo is taken -
		 * so these references point to the wrong object after the import.
		 *
		 * The importer knows both id mappings, we only have to apply them to the imported content.
		 * Must be called after all posts, terms and menus are imported.
		 *
		 * @since 8.0
		 * @return void
		 */
		public function remap_imported_ids()
		{
			global $wpdb;

			/**
			 * Allows to skip the id mapping of imported content
			 *
			 * @since 8.0
			 * @param boolean $remap
			 * @param avia_wp_import $import
			 * @return boolean
			 */
			if( true !== apply_filters( 'avf_demo_import_remap_ids', true, $this ) )
			{
				return;
			}

			$post_ids = array();

			foreach( $this->processed_posts as $demo_id => $new_id )
			{
				//	posts matched to existing posts of the user must not be modified
				if( isset( $this->inserted_posts[ (int) $demo_id ] ) && $new_id > 0 )
				{
					$post_ids[] = (int) $new_id;
				}
			}

			if( empty( $post_ids ) )
			{
				return;
			}

			$updated = array(
						'content'	=> 0,
						'meta'		=> 0
					);

			foreach( array_chunk( $post_ids, 100 ) as $chunk )
			{
				$in = implode( ',', $chunk );

				$rows = $wpdb->get_results( "SELECT ID, post_content FROM {$wpdb->posts} WHERE ID IN ({$in}) AND post_content LIKE '%=%'" );

				foreach( $rows as $row )
				{
					$content = $this->remap_ids_in_content( $row->post_content );

					if( is_null( $content ) || $content === $row->post_content )
					{
						continue;
					}

					$wpdb->update( $wpdb->posts, array( 'post_content' => $content ), array( 'ID' => $row->ID ) );
					clean_post_cache( $row->ID );
					$updated['content'] ++;
				}

				//	the builder keeps a copy of the content - without updating it the ALB would restore the old ids
				$metas = $wpdb->get_results( "SELECT meta_id, post_id, meta_value FROM {$wpdb->postmeta} WHERE post_id IN ({$in}) AND meta_key = '_aviaLayoutBuilderCleanData'" );

				foreach( $metas as $meta )
				{
					$value = $this->remap_ids_in_content( $meta->meta_value );

					if( is_null( $value ) || $value === $meta->meta_value )
					{
						continue;
					}

					$wpdb->update( $wpdb->postmeta, array( 'meta_value' => $value ), array( 'meta_id' => $meta->meta_id ) );
					clean_post_cache( $meta->post_id );
					$updated['meta'] ++;
				}
			}

			if( defined( 'WP_DEBUG' ) && WP_DEBUG )
			{
				error_log( sprintf( __( 'Demo Importer: remapped ids in %1$d posts and %2$d builder records', 'avia_framework' ), $updated['content'], $updated['meta'] ) );
			}
		}

		/**
		 * Replaces the term and post ids of the demo by the ids on the users site.
		 *
		 * Attribute names are matched with a prefix guard, so similar names like data-categories,
		 * imagelink, ids_dynamic or attachment_size can not match.
		 *
		 * @since 8.0
		 * @param string $content
		 * @return string|null					null if the content could not be parsed
		 */
		protected function remap_ids_in_content( $content )
		{
			if( ! is_string( $content ) || '' === $content )
			{
				return $content;
			}

			/**
			 * Attributes containing a comma separated list of term ids
			 *
			 * @since 8.0
			 * @param array $attributes
			 * @return array
			 */
			$term_attributes = apply_filters( 'avf_demo_import_remap_term_attributes', array( 'categories', 'menu' ) );

			/**
			 * Attributes containing a comma separated list of post ids
			 *
			 * @since 8.0
			 * @param array $attributes
			 * @return array
			 */
			$post_attributes = apply_filters( 'avf_demo_import_remap_post_attributes', array( 'attachment', 'ids' ) );

			$patterns = array(
						$this->attribute_pattern( $term_attributes )	=> $this->processed_terms,
						$this->attribute_pattern( $post_attributes )	=> $this->processed_posts
					);

			foreach( $patterns as $pattern => $map )
			{
				if( '' == $pattern || empty( $map ) )
				{
					continue;
				}

				$replaced = preg_replace_callback(
									$pattern,
									function( array $match ) use ( $map )
									{
										return $match[1] . $this->remap_id_list( $match[3], $map ) . $match[2];
									},
									$content
								);

				if( is_null( $replaced ) )
				{
					return $this->log_preg_error( 'attributes' );
				}

				$content = $replaced;
			}

			/**
			 * link='post_type|taxonomy,id[,id]' - same format and same order of checks
			 * as in AviaHelper::get_url()
			 *
			 * link\d* because an element can carry more than one link: a fullscreen slide
			 * stores its own target in link and the targets of its buttons in link1 and
			 * link2. Matching only link left those behind, and because the id was kept
			 * verbatim the button did not visibly fail - it pointed at whatever post or
			 * term happened to hold that number on the importing site.
			 *
			 * The trailing \d* cannot reach link_target1 or link_dynamic: after link only
			 * digits are allowed before the '='. The lookbehind keeps imagelink out.
			 */
			$replaced = preg_replace_callback(
								'/((?<![-\w])link\d*\s*=\s*([\'"]))([a-z][a-z0-9_-]*),([^\'"]*)\2/i',
								array( $this, 'callback_remap_link_attribute' ),
								$content
							);

			if( is_null( $replaced ) )
			{
				return $this->log_preg_error( 'link' );
			}

			return $replaced;
		}

		/**
		 * @since 8.0
		 * @param array $match
		 * @return string
		 */
		protected function callback_remap_link_attribute( array $match )
		{
			$name = strtolower( $match[3] );

			if( post_type_exists( $name ) )
			{
				$map = $this->processed_posts;
			}
			else if( taxonomy_exists( $name ) )
			{
				$map = $this->processed_terms;
			}
			else
			{
				//	manually, lightbox, mailto, ... - nothing to map
				return $match[0];
			}

			return $match[1] . $match[3] . ',' . $this->remap_id_list( $match[4], $map ) . $match[2];
		}

		/**
		 * Maps a comma separated list of ids. Values that are not numeric or unknown to the
		 * importer are kept - we never guess an id.
		 *
		 * @since 8.0
		 * @param string $list
		 * @param array $map					array( demo id => id on users site )
		 * @return string
		 */
		protected function remap_id_list( $list, array $map )
		{
			$ids = explode( ',', $list );
			$unmapped = array();

			foreach( $ids as $key => $id )
			{
				$id = trim( $id );

				if( '' === $id || ! ctype_digit( $id ) )
				{
					$ids[ $key ] = $id;
					continue;
				}

				$demo_id = (int) $id;

				if( isset( $map[ $demo_id ] ) && (int) $map[ $demo_id ] > 0 )
				{
					$ids[ $key ] = (string) (int) $map[ $demo_id ];
				}
				else
				{
					$ids[ $key ] = $id;
					$unmapped[] = $id;
				}
			}

			if( ! empty( $unmapped ) && defined( 'WP_DEBUG' ) && WP_DEBUG )
			{
				error_log( sprintf( __( 'Demo Importer: no imported object found for id(s) %s - value kept unchanged', 'avia_framework' ), implode( ', ', $unmapped ) ) );
			}

			return implode( ',', $ids );
		}

		/**
		 * Builds the regex for a list of attribute names.
		 *
		 * @since 8.0
		 * @param array $attributes
		 * @return string						empty string if no valid attribute name is left
		 */
		protected function attribute_pattern( $attributes )
		{
			if( ! is_array( $attributes ) )
			{
				return '';
			}

			$names = array();

			foreach( $attributes as $attribute )
			{
				if( ! is_string( $attribute ) || '' === trim( $attribute ) )
				{
					continue;
				}

				$names[] = preg_quote( trim( $attribute ), '/' );
			}

			if( empty( $names ) )
			{
				return '';
			}

			return '/((?<![-\w])(?:' . implode( '|', $names ) . ')\s*=\s*([\'"]))([^\'"]*)\2/';
		}

		/**
		 * A failed regex must never overwrite the content of the user with an empty string.
		 *
		 * @since 8.0
		 * @param string $which
		 * @return null
		 */
		protected function log_preg_error( $which )
		{
			if( defined( 'WP_DEBUG' ) && WP_DEBUG )
			{
				error_log( sprintf( __( 'Demo Importer: could not remap the %1$s of a post (preg error %2$s) - content left unchanged', 'avia_framework' ), $which, preg_last_error() ) );
			}

			return null;
		}


		/**
		 *
		 * @param array $item
		 * @return void
		 */
		function process_menu_item( $item )
		{
			@ini_set('max_execution_time', 300);

			// skip draft, orphaned menu items
			if ( 'draft' == $item['status'] )
			{
				return;
			}

			$menu_slug = false;
			if ( isset( $item['terms'] ) )
			{
				// loop through terms, assume first nav_menu term is correct menu
				foreach ( $item['terms'] as $term )
				{
					if ( 'nav_menu' == $term['domain'] )
					{
						$menu_slug = $term['slug'];
						break;
					}
				}
			}

			// no nav_menu term associated with this menu item
			if ( ! $menu_slug )
			{
				_e( 'Menu item skipped due to missing menu slug', 'wordpress-importer' );
				echo '<br />';
				return;
			}

			$menu_id = term_exists( $menu_slug, 'nav_menu' );
			if ( ! $menu_id )
			{
				printf( __( 'Menu item skipped due to invalid menu slug: %s', 'wordpress-importer' ), esc_html( $menu_slug ) );
				echo '<br />';
				return;
			}
			else
			{
				$menu_id = is_array( $menu_id ) ? $menu_id['term_id'] : $menu_id;
			}

			foreach( $item['postmeta'] as $meta )
			{
				${$meta['key']} = $meta['value']; //kriesi mod: php 7 fix - added braces
			}

			if( 'taxonomy' == $_menu_item_type && isset( $this->processed_terms[ intval( $_menu_item_object_id ) ] ) )
			{
				$_menu_item_object_id = $this->processed_terms[ intval( $_menu_item_object_id ) ];
			}
			else if( 'post_type' == $_menu_item_type && isset( $this->processed_posts[ intval( $_menu_item_object_id ) ] ) )
			{
				$_menu_item_object_id = $this->processed_posts[ intval( $_menu_item_object_id ) ];
			}
			else if( 'custom' != $_menu_item_type )
			{
				// associated object is missing or not imported yet, we'll retry later
				$this->missing_menu_items[] = $item;
				return;
			}

			if( isset( $this->processed_menu_items[ intval( $_menu_item_menu_item_parent ) ] ) )
			{
				$_menu_item_menu_item_parent = $this->processed_menu_items[ intval( $_menu_item_menu_item_parent ) ];
			}
			else if( $_menu_item_menu_item_parent )
			{
				$this->menu_item_orphans[intval($item['post_id'])] = (int) $_menu_item_menu_item_parent;
				$_menu_item_menu_item_parent = 0;
			}

			// wp_update_nav_menu_item expects CSS classes as a space separated string
			$_menu_item_classes = maybe_unserialize( $_menu_item_classes );

			if( is_array( $_menu_item_classes ) )
			{
				$_menu_item_classes = implode( ' ', $_menu_item_classes );
			}

			$args = array(
					'menu-item-object-id'	=> $_menu_item_object_id,
					'menu-item-object'		=> $_menu_item_object,
					'menu-item-parent-id'	=> $_menu_item_menu_item_parent,
					'menu-item-position'	=> intval( $item['menu_order'] ),
					'menu-item-type'		=> $_menu_item_type,
					'menu-item-title'		=> $item['post_title'],
					'menu-item-url'			=> $_menu_item_url,
					'menu-item-description'	=> $item['post_content'],
					'menu-item-attr-title'	=> $item['post_excerpt'],
					'menu-item-target'		=> $_menu_item_target,
					'menu-item-classes'		=> $_menu_item_classes,
					'menu-item-xfn'			=> $_menu_item_xfn,
					'menu-item-status'		=> $item['status']
				);

			$id = wp_update_nav_menu_item( $menu_id, 0, $args );
			if ( $id && ! is_wp_error( $id ) )
				$this->processed_menu_items[intval($item['post_id'])] = (int) $id;

			/*kriesi mod: necessary to add custom post meta to the import*/
			if ( $id && ! is_wp_error( $id ) )
			{
				foreach( $item['postmeta'] as $itemkey => $meta )
				{
					$key = str_replace( '_', '-', ltrim( $meta['key'], '_' ) );

					/*do a check: only add keys that do not exist - parent menu item is a special case that must be checked as well*/
					if( ! array_key_exists( $key, $args ) && $key != 'menu-item-menu-item-parent' )
					{
						if( ! empty( $meta['value'] ) )
						{
							update_post_meta( $id, $meta['key'], $meta['value'] );
						}
					}
				}
			}
			/*end mod*/

		}
	}

}	//	end class_exists

