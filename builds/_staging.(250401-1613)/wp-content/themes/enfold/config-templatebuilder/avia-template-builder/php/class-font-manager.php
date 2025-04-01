<?php
/**
 * Icon Font and SVG Icon Sets Management Class
 *
 * Should be changed to extend enfold\framework\php\font-management\class-avia-font-management-base.php in future - risk of breaking existing sites is big
 *
 * @since ????
 * @since 7.0					additional support for svg icon sets
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_font_manager', false ) )
{
	class avia_font_manager extends aviaBuilder\base\object_properties
	{
		/**
		 * @since ????
		 * @access public					called from avia_wp_import
		 * @var array
		 */
		public $paths;

		/**
		 * @since ????
		 * @var string
		 */
		protected $svg_file;

		/**
		 * @since ????
		 * @access public					called from avia_wp_import
		 * @var string
		 */
		public $font_name;

		/**
		 * @since ????
		 * @var string
		 */
		protected $origin_font_name;

		/**
		 * @since ????
		 * @var array
		 */
		protected $svg_config;

		/**
		 * Array to translate unicode to svg icon file
		 *
		 *			'icon_key'   =>  'icon.svg'
		 *
		 * Currently only needed to update entypo fontello default font hardcoded.
		 *
		 * @since 7.0
		 * @var array
		 */
		protected $svg_config_svg;

		/**
		 * Stores type of uploaded font file
		 *
		 * @since 7.0
		 * @var string					'iconfont' | 'svg_icons' | ''
		 */
		protected $font_type;

		/**
		 * Array containing characters for icon font
		 *
		 *		'iconfont_name'		=>  array(  $icon_key => $value   )
		 *
		 * @since ????
		 * @var array
		 */
		static protected $charlist = array();

		/**
		 * Array containing names for icon font characters or config info for svg icon set
		 *
		 *		'iconfont_name'		=>  array(  $icon_key => $icon_name|$config_info   )
		 *
		 * @since 5.6.11
		 * @var array
		 */
		static protected $charnames = array();

		/**
		 * Array containing svg files for icon font characters
		 *
		 *		'iconfont_name'		=>  array(  $icon_key => $iconfile.svg  )
		 *
		 * Currently not used - will be filled later in load_charlist() if ever needed
		 *
		 * @since 7.0
		 * @var array
		 */
		static protected $char_svgfiles = array();

		/**
		 * @since ????
		 * @deprecated 5.6.7
		 * @var array
		 */
		static protected $charlist_fallback = array();

		/**
		 * Array containing config info about icon fonts (defaults and uploaded)
		 *
		 *		'iconfont_name'		=>  array(  $config_info   )
		 *
		 * @since ????
		 * @var array
		 */
		static protected $iconlist = array();

		/**
		 * Allows theme or 3rd party to add icon fonts (e.g. ALB)
		 *
		 * @since 7.0
		 * @var array
		 */
		static protected $extra_iconfonts = [];

		/**
		 * Shortcuts for icons used for mapping
		 *
		 * e.g.
		 *		array(
		 *			'standard' => array( 'font' => 'entypo-fontello', 'icon' => 'ue836' )
		 *			....
		 *		)
		 *
		 * @since 7.0
		 * @var array
		 */
		static protected $icon_shortcuts = [];

		/**
		 *
		 * @since ????
		 */
		public function __construct()
		{
			global $avia_config;

			$this->paths = wp_upload_dir();
			$this->svg_file = '';
			$this->font_name = 'unknown';
			$this->origin_font_name = '';
			$this->svg_config = [];
			$this->svg_config_svg = [];
			$this->font_type = '';

			if( is_ssl() )
			{
				$this->paths['baseurl'] = str_replace( 'http://', 'https://', $this->paths['baseurl'] );
			}

			$icon_font_location = get_option( 'avia_icon_font_location', '' );

			/**
			 * Backwards compatibility because directory info is stored in db and risk to break existing sites is too big.
			 *
			 * Existing sites with already uploaded fonts:
			 *   - delete the fonts
			 *   - reupload the fonts again
			 *   - delete empty folder ../uploads/avia_fonts  (might contain custom type fonts - same procedure there)
			 *
			 * @since 5.3
			 */
			if( 'dynamic' != $icon_font_location )
			{
				$avia_builder_fonts = get_option( 'avia_builder_fonts', [] );
				if( empty( $avia_builder_fonts ) )
				{
					$icon_font_location = 'dynamic';
					update_option( 'avia_icon_font_location', $icon_font_location );
				}
			}

			if( 'dynamic' != $icon_font_location )
			{
				$dynamic_dir = 'avia_fonts';
			}
			else
			{
				$dynamic_dir = trailingslashit( ltrim( $avia_config['dynamic_files_upload_folder'], ' /\\' ) ) . 'avia_icon_fonts';
			}

			$this->paths['fonts']		= $dynamic_dir;
			$this->paths['temp']		= trailingslashit( $this->paths['fonts'] ) . 'avia_temp';
			$this->paths['fontdir']		= trailingslashit( $this->paths['basedir'] ) . $this->paths['fonts'];
			$this->paths['tempdir']		= trailingslashit( $this->paths['basedir'] ) . $this->paths['temp'];
			$this->paths['fonturl']		= trailingslashit( $this->paths['baseurl'] ) . $this->paths['fonts'];
			$this->paths['tempurl']		= trailingslashit( $this->paths['baseurl'] ) . trailingslashit( $this->paths['temp'] );
			$this->paths['config']		= 'charmap.php';
			$this->paths['json']		= 'config.json';
			$this->paths['svg_files']	= 'charmap-svg.php';

			//font file extract by ajax function
			add_action( 'wp_ajax_avia_ajax_add_zipped_font', array( $this, 'handler_add_zipped_font' ) );
			add_action( 'wp_ajax_avia_ajax_remove_zipped_font', array( $this, 'handler_remove_zipped_font' ) );

			add_filter( 'avf_file_upload_extra', array( $this, 'handler_add_font_manager_upload' ), 10, 2 );
		}

		/**
		 * @since 5.3
		 */
		public function __destruct()
		{
			unset( $this->paths );
			unset( $this->svg_config );
			unset( $this->svg_config_svg );
		}

		/**
		 * @since ????
		 */
		public function handler_add_zipped_font()
		{
			//check if referer is ok
			check_ajax_referer( 'avia_nonce_save_backend' );

			//check if capability is ok
			$cap = apply_filters( 'avf_file_upload_capability', 'update_plugins' );
			if( ! current_user_can( $cap) )
			{
				exit( __( "Using this feature is reserved for Super Admins. You unfortunately don't have the necessary permissions.", 'avia_framework' ) );
			}

			//get the file path of the zip file
			$attachment = $_POST['values'];
			$path = realpath( get_attached_file( $attachment['id'] ) );

			$unzipped = $this->extract_zip_file( $path );

			// if we were able to unzip the file and save it to our temp folder extract the svg file
			if( $unzipped )
			{
				if( ! avia_font_manager::are_svg_icons( $this->font_name ) )
				{
					$this->create_config();
				}
				else
				{
					$this->create_config_svg();
				}
			}

			//if we got no name for the font dont add it and delete the temp folder
			if( $this->font_name == 'unknown' )
			{
				$this->delete_folder( $this->paths['tempdir'] );
				exit( __( 'Was not able to retrieve the font name from your uploaded folder', 'avia_framework' ) );
			}

			exit( __( 'avia_font_added:', 'avia_framework' ) . $this->font_name );
		}

		/**
		 * @since ????
		 * @since 7.0			support to activate/deactivate a default font
		 */
		public function handler_remove_zipped_font()
		{
			//check if referer is ok
			check_ajax_referer( 'avia_nonce_save_backend' );

			//check if capability is ok
			$cap = apply_filters( 'avf_file_upload_capability', 'update_plugins' );
			if( ! current_user_can( $cap) )
			{
				exit( __( "Using this feature is reserved for Super Admins. You unfortunately don't have the necessary permissions.", 'avia_framework' ) );
			}

			//get the file path of the zip file
			$font = $_POST['del_font'];
			$list = self::load_iconfont_list();

			$font_file = isset( $list[ $font ] ) ? $list[ $font ] : false;

			if( false === $font_file )
			{
				exit( __( 'Was not able to remove font', 'avia_framework' ) );
			}

			if( ! isset( $font_file['full_path'] ) )
			{
				$this->delete_folder( $font_file['include'] );
				$this->remove_font( $font );
			}
			else
			{
				$this->toggle_font_activation( $font, $font_file, $list );
			}

			//	do not translate - is checked by regex
			exit( 'avia_font_removed' );
		}

		/**
  		 * extract the zip file to a flat folder and remove the files that are not needed
		 *
		 * @since 7.0
		 * @param string $zipfile
		 * @return boolean
		 */
		protected function extract_zip_file( $zipfile )
		{
			$filter = [ '\.eot', '\.svg', '\.ttf', '\.woff', '\.woff2', '\.json' ];
			$filter_svg = [ 'symbol-defs.svg', 'style.css' ];


			@ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', WP_MAX_MEMORY_LIMIT ) );

			//if a temp dir already exists remove it and create a new one
			if( is_dir( $this->paths['tempdir'] ) )
			{
				$this->delete_folder( $this->paths['tempdir'] );
			}

			//create a new
			$tempdir = avia_backend_create_folder( $this->paths['tempdir'], false );
			if( ! $tempdir )
			{
				exit( __( 'Was not able to create temp folder', 'avia_framework' ) );
			}

			$zip = new ZipArchive;

			if( ! $zip->open( $zipfile ) )
			{
				$this->delete_folder( $this->paths['tempdir'] );
				exit( __( 'Was not able to work with Zip Archive', 'avia_framework' ) );
			}

			$zip_paths = pathinfo( $zipfile );

			// check name scheme if user wants to rename the file
			if( isset( $zip_paths['filename'] ) && strpos( $zip_paths['filename'], 'iconset.' ) === 0 )
			{
				$this->font_name = str_replace( 'iconset.', '', $zip_paths['filename'] );
			}

			$this->font_type = 'iconfont';

			for( $i = 0; $i < $zip->numFiles; $i++ )
			{
				$entry = $zip->getNameIndex( $i );
				$entry_check = str_replace( '\\', '/', $entry );

				if( 0 === stripos( $entry_check, 'SVG/' ) )
				{
					$this->font_type = 'svg_icons';
					$this->font_name = 'svg_' .  AviaHelper::save_string( $zip_paths['filename'], '-' );
					$svg_folder = trailingslashit( $this->paths['tempdir'] ) . 'svg';

					if( ! avia_backend_create_folder( $svg_folder , false ) )
					{
						$this->delete_folder( $this->paths['tempdir'] );
						exit( __( 'Was not able to create ZIP subfolder', 'avia_framework' ) );
					}

					break;
				}
			}

			try
			{
				for( $i = 0; $i < $zip->numFiles; $i++ )
				{
					$entry = $zip->getNameIndex( $i );

					if( 'svg_icons' == $this->font_type )
					{
						$entry_check = str_replace( '\\', '/', $entry );
						if( 0 === stripos( $entry_check, 'SVG/' ) )
						{
							if( strlen( $entry_check ) == 4 )
							{
								continue;
							}

							$path = trailingslashit( $this->paths['tempdir'] ) . trailingslashit( 'svg' );
						}
						else
						{
							if( ! in_array( $entry_check, $filter_svg ) )
							{
								continue;
							}

							$path = trailingslashit( $this->paths['tempdir'] );
						}
					}
					else
					{
						if( ! empty( $filter ) )
						{
							$delete = true;
							$matches = array();

							foreach( $filter as $regex )
							{
								preg_match( '!' . $regex . '$!', $entry , $matches );

								if( ! empty( $matches ) )
								{
									if( strpos( $entry, '.php' ) === false )
									{
										$delete = false;
										break;
									}
								}
							}
						}

						// skip directories and non matching files
						if( substr( $entry, -1 ) == '/' || ! empty( $delete) )
						{
							continue;
						}

						$path = trailingslashit( $this->paths['tempdir'] );
					}

					$fp = $zip->getStream( $entry );
					if( ! $fp )
					{
						throw new Exception( __( 'Unable to extract the file', 'avia_framework' ) );
					}

					$ofp = fopen( $path . basename( $entry ), 'w' );
					while ( ! feof( $fp ) )
					{
						fwrite( $ofp, fread( $fp, 8192 ) );
					}

					fclose( $ofp );
					fclose( $fp );
				}

				$zip->close();
			}
			catch( Exception $ex )
			{
				$zip->close();

				exit( $ex->getMessage() );
			}

			return true;
		}

		/**
		 * iterate over xml file and extract the glyphs for the font
		 *
		 * @access public					called from avia_wp_import
		 * @since ????
		 * @param boolean $config_only
		 * @return boolean
		 */
		public function create_config( $config_only = false )
		{
			$this->svg_file = $this->find_svg();

			if( 'svg_icons' == $this->font_type )
			{
				return $this->create_config_svg();
			}

			if( empty( $this->svg_file ) )
			{
				$this->delete_folder( $this->paths['tempdir'] );
				exit( __( 'Found no SVG file with font information in your folder. Was not able to create the necessary config files', 'avia_framework' ) );
			}

			//fetch the svg files content
			$response = file_get_contents( trailingslashit( $this->paths['tempdir'] ) . $this->svg_file );

			//if we werent able to get the content try to fetch it by using wordpress
			if( empty( $response ) || trim( $response ) == '' || strpos( $response, '<svg' ) === false )
			{
				$response = wp_remote_fopen( trailingslashit( $this->paths['tempurl'] ) . $this->svg_file );
			}

			//filter the response
			$response = apply_filters( 'avf_icon_font_uploader_response', $response, $this->svg_file, $this->paths );

			if( is_wp_error( $response ) || empty( $response ) )
			{
				return false;
			}

			//$xml = simplexml_load_string( $response['body'] );
			$xml = simplexml_load_string( $response );

			$font_attr = $xml->defs->font->attributes();

			if( $this->font_name == 'unknown' )
			{
				$this->font_name = (string) $font_attr['id'];
			}

			//allow only basic characters within the font name
			$this->font_name = AviaHelper::save_string( $this->font_name, '-' );

			$glyphs = $xml->defs->font->children();
			foreach( $glyphs as $item => $glyph )
			{
				if( $item == 'glyph' )
				{
					$attributes = $glyph->attributes();
					$unicode = (string) $attributes['unicode'];
					$class = (string) $attributes['class'];
					$svg = (string) $attributes['glyph-name'];

					if( $class != 'hidden' )
					{
						$unicode_key = trim( json_encode( $unicode), '\\\"' );
						$unicode_key = AviaHelper::save_string( $unicode_key, '-' );

						if( $item == 'glyph' && ! empty( $unicode_key ) && trim( $unicode_key ) != '' )
						{
							$this->svg_config[ $this->font_name ][ $unicode_key ] = $unicode_key;
							$this->svg_config_svg[ $this->font_name ][ $unicode_key ] = $svg;
						}
					}
				}
			}

			if( ! empty( $this->svg_config ) && $this->font_name != 'unknown' )
			{
				$this->write_config();

				if( ! $config_only )
				{
					$this->rename_files();
					$this->rename_folder();
					$this->add_font();
				}
			}

			return true;
		}

		/**
		 * Scan the svg directory and create a json config that can be edited by user
		 *
		 * @since 7.0
		 * @return boolean
		 */
		protected function create_config_svg()
		{
			if( 'unknown' == $this->font_name )
			{
				$this->font_name = $this->create_default_svg_iconset_name();
			}

			$this->font_name = AviaHelper::save_string( $this->font_name, '-' );

			$files = scandir( trailingslashit( $this->paths['tempdir'] ) . 'svg' );

			if( false === $files )
			{
				$files = [];
			}

			$info = [];
			$charmap = [];

			foreach( $files as $file_name )
			{
				if( strtolower( pathinfo( $file_name, PATHINFO_EXTENSION ) ) != 'svg' )
				{
					continue;
				}

				$char = pathinfo( $file_name, PATHINFO_FILENAME );
				$text = ucfirst( $char );

				$info[ $char ] = [
									'file_name'		=> $file_name,
									'path'			=> "svg/{$file_name}",
									'title'			=> $text,
									'description'	=> $text,
									'alt'			=> $text,
									'search_text'	=> $text
				];

				$charmaps[ $char ] = "svg/{$file_name}";
			}

			if( empty( $info ) )
			{
				$this->delete_folder( $this->paths['tempdir'] );
				exit( __( 'Found no svg files in svg folder. Unable to add the svg icon set. Remove the svg folder if this is an icon font.', 'avia_framework' ) );
			}

			$handle = false;

			try
			{
				$this->svg_config[ $this->font_name ] = $info;

				$json = $this->paths['tempdir'] . '/' . $this->paths['json'];
				$handle = @fopen( $json, 'w' );

				if( false === $handle )
				{
					throw new Exception();
				}

				// JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
				$encoded = json_encode( $this->svg_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );

				if( false === fwrite( $handle, $encoded ) )
				{
					throw new Exception();
				}

				fclose( $handle );
				$handle = false;


				$charmap = $this->paths['tempdir'] . '/' . $this->paths['config'];
				$handle = @fopen( $charmap, 'w' );

				if( false === $handle )
				{
					throw new Exception();
				}

				if( false === fwrite( $handle, '<?php $chars = array();' ) )
				{
					throw new Exception();
				}

				foreach( $charmaps as $char => $file )
				{
					$delimiter = "'";

					if( false === fwrite( $handle, "\r\n" . '$chars[\'' . $this->font_name . '\'][' . $delimiter . $char . $delimiter . '] = ' . $delimiter . $file . $delimiter . ';' ) )
					{
						throw new Exception();
					}
				}

				fclose( $handle );
			}
			catch( Exception $ex )
			{
				if( false !== $handle )
				{
					fclose( $handle );
				}

				$this->delete_folder( $this->paths['tempdir'] );
				exit( __( 'Was not able to write a config file for svg icon set', 'avia_framework' ) );
			}

			$this->rename_folder();
			$this->add_font();

			return true;
		}

		/**
		 * Returns a unique default iconset name
		 *
		 * @since 7.0
		 * @return string
		 */
		protected function create_default_svg_iconset_name()
		{
			$fonts = get_option( 'avia_builder_fonts' );

			if( empty( $fonts ) )
			{
				$fonts = array();
			}

			$i = 0;
			$default_val = 'svg_iconset_';

			do
			{
				$new_default = "{$default_val}{$i}";

				if( ! isset( $fonts[ $new_default ] ) )
				{
					return $new_default;
				}

				$i++;
			}
			while( true );
		}

		/**
		 * writes the php config file for the font
		 *
		 * @since ????
		 * @return void			only returns when successfull  !!!!
		 */
		protected function write_config()
		{
			$charmap = $this->paths['tempdir'] . '/' . $this->paths['config'];
			$charmap_svg = $this->paths['tempdir'] . '/' . $this->paths['svg_files'];

			$handle = false;
			$handle_svg = false;

			try
			{
				$handle = @fopen( $charmap, 'w' );
				$handle_svg = @fopen( $charmap_svg, 'w' );

				if( false === $handle || false === $handle_svg )
				{
					throw new Exception();
				}

				if( false === fwrite( $handle, '<?php $chars = array();' ) )
				{
					throw new Exception();
				}

				foreach( $this->svg_config[ $this->font_name ] as $unicode )
				{
					if( ! empty( $unicode ) )
					{
						$delimiter = "'";
						if( strpos( $unicode, "'" ) !== false )
						{
							$delimiter = '"';
						}

						$value = $delimiter . $unicode . $delimiter;

						if( false === fwrite( $handle, "\r\n" . '$chars[\'' . $this->font_name . '\'][' . $value . '] = ' . $value . ';' ) )
						{
							throw new Exception();
						}
					}
				}

				if( false === fclose( $handle ) )
				{
					$handle = false;
					throw new Exception();
				}

				if( false === fwrite( $handle_svg, '<?php $chars_svg = array();' ) )
				{
					throw new Exception();
				}

				foreach( $this->svg_config_svg[ $this->font_name ] as $unicode => $svg_file )
				{
					if( ! empty( $unicode ) )
					{
						$delimiter = "'";
						if( strpos( $unicode, "'" ) !== false )
						{
							$delimiter = '"';
						}

						if( false === fwrite( $handle_svg, "\r\n" . '$chars_svg[\'' . $this->font_name . '\'][' . $delimiter . $unicode . $delimiter . '] = ' . $delimiter . $svg_file . $delimiter . ';' ) )
						{
							throw new Exception();
						}
					}
				}

				if( false === fclose( $handle_svg ) )
				{
					$handle_svg = false;
					throw new Exception();
				}
			}
			catch( Exception $ex )
			{
				if( false !== $handle )
				{
					fclose( $handle );
				}

				if( false !== $handle_svg )
				{
					fclose( $handle_svg );
				}

				$this->delete_folder( $this->paths['tempdir'] );
				exit( __( 'Was not able to write a config file', 'avia_framework' ) );
			}
		}

		/**
		 *
		 * @since ????
		 */
		protected function rename_files()
		{
			$extensions = array( 'eot', 'svg', 'ttf', 'woff', 'woff2' );
			$folder = trailingslashit( $this->paths['tempdir'] );

			foreach( glob( $folder.'*' ) as $file )
			{
				$path_parts = pathinfo( $file );

				if( strpos( $path_parts['filename'], '.dev' ) === false && in_array( $path_parts['extension'], $extensions ) )
				{
					if( ( ! empty( $this->origin_font_name ) && $this->origin_font_name == strtolower( $path_parts['filename'] ) ) || empty( $this->origin_font_name ) )
					{
						rename( $file, trailingslashit( $path_parts['dirname'] ) . $this->font_name . '.' . $path_parts['extension'] );
					}
					else
					{
						unlink( $file );
					}
				}
			}
		}

		/**
		 * rename the temp folder and all its font files
		 *
		 * @since ????
		 */
		protected function rename_folder()
		{
			$new_name = trailingslashit( $this->paths['fontdir'] ) . $this->font_name;

			//delete folder and contents if they already exist
			$this->delete_folder( $new_name );

			rename( $this->paths['tempdir'], $new_name );
		}

		/**
		 * delete a folder and contents if they already exist
		 *
		 * @since ????
		 * @access public					called from avia_wp_import
		 * @param string $new_name
		 */
		public function delete_folder( $new_name )
		{
			avia_backend_delete_folder( $new_name );
		}

		/**
		 *
		 * @since ????
		 */
		protected function add_font()
		{
			$fonts = get_option( 'avia_builder_fonts', [] );

			if( ! is_array( $fonts ) )
			{
				$fonts = array();
			}

			$fonts[ $this->font_name ] = array(
												'include' 		=> trailingslashit( $this->paths['fonts'] ) . $this->font_name,
												'folder' 		=> trailingslashit( $this->paths['fonts'] ) . $this->font_name,
												'config' 		=> $this->paths['config'],
												'json'			=> $this->paths['json'],
												'origin_folder'	=> trailingslashit( $this->paths['baseurl'] )
											);

			if( ! empty( $this->svg_config_svg ) )
			{
				$fonts[ $this->font_name ]['svg_files'] = $this->paths['svg_files'];
			}

			update_option( 'avia_builder_fonts', $fonts );
		}

		/**
		 *
		 * @since ????
		 * @param string $font
		 */
		protected function remove_font( $font )
		{
			$fonts = get_option( 'avia_builder_fonts', [] );

			if( ! is_array( $fonts ) )
			{
				$fonts = array();
			}

			if( isset( $fonts[ $font ] ) )
			{
				unset( $fonts[ $font ] );
				update_option( 'avia_builder_fonts', $fonts );
			}
		}

		/**
		 * @since 7.0
		 * @param string $font
		 * @param array $font_list
		 */
		protected function toggle_font_activation( $font, array $font_config, array $list )
		{
			$fonts_deactivated = get_option( 'avia_builder_fonts_deactivated', [] );

			if( 'no' == $font_config['is_activ'] )
			{
				$font_config['is_activ'] = 'yes';
				unset( $fonts_deactivated[ $font ] );
			}
			else
			{
				$font_config['is_activ'] = 'no';
				$fonts_deactivated[ $font ] = $font_config;
			}

			foreach( $fonts_deactivated as $deactivated => $config )
			{
				if( ! isset( $list[ $deactivated ] ) )
				{
					unset( $fonts_deactivated[ $deactivated ] );
				}
			}

			self::$iconlist[ $font ] = $font_config;

			update_option( 'avia_builder_fonts_deactivated', $fonts_deactivated );
		}

		/**
		 * finds the svg file we need to create the config
		 *
		 * @since ????
		 * @return string
		 */
		protected function find_svg()
		{
			if( 'svg_icons' == $this->font_type )
			{
				return '';
			}

			$files = scandir( $this->paths['tempdir'] );

			/**
			 * If directory svg exist we assume it is a svg icon set and not an iconfont
			 *
			 * @since 7.0
			 */
			foreach( $files as $file )
			{
				if( 'svg' == strtolower( $file ) )
				{
					$this->font_type = 'svg_icons';
					return '';
				}
			}

			/**
			 * fetch the eot file first so we know the acutal filename,
			 * in case there are multiple svg files, then based on that find the svg file
			 */
			$filename = '';
			foreach( $files as $file )
			{
				if( strpos( strtolower( $file ), '.eot' )  !== false && $file[0] != '.' )
				{
					$filename = strtolower( pathinfo( $file, PATHINFO_FILENAME ) );
					continue;
				}
			}

			$this->origin_font_name = $filename;

			foreach( $files as $file )
			{
				if( strpos( strtolower( $file ), $filename . '.svg' )  !== false && $file[0] != '.' )
				{
					return $file;
				}
			}

			return '';
		}

		/**
		 *
		 * @since ????
		 * @param string $output
		 * @param array $element
		 * @return string
		 */
		public function handler_add_font_manager_upload( $output, array $element )
		{
			if( $element['id'] != 'iconfont_upload' )
			{
				return $output;
			}

			$sorted_configs = [];
			$sorted_svg = [];
			$sorted_icons = [];

			foreach( self::load_iconfont_list() as $font_name => $font_file )
			{
				if( isset( $font_file['full_path'] ) )
				{
					$sorted_configs[ $font_name ] = $font_file;
				}
				else if( avia_font_manager::are_svg_icons( $font_name ) )
				{
					$sorted_svg[ $font_name ] = $font_file;
				}
				else
				{
					$sorted_icons[ $font_name ] = $font_file;
				}
			}

			ksort( $sorted_svg );
			ksort( $sorted_icons );

			$font_configs = array_merge( array( '{font_name}' => array() ), $sorted_configs, $sorted_svg, $sorted_icons );

			$output .= "<div class='avia_iconfont_manager' data-id='{$element['id']}'>";

			if( ! empty( $font_configs ) )
			{
				foreach( $font_configs as $font_name => $font_file )
				{
					if( ! avia_font_manager::are_svg_icons( $font_name ) )
					{
						$desc = 'Iconfont';
						$readable = ucwords( str_replace( [ '_', '-' ], ' ', $font_name ) );
					}
					else
					{
						$desc = 'SVG Iconset';
						$readable = ucwords( str_replace( [ '_', '-' ], ' ', substr( $font_name, 4 ) ) );
					}

					$is_default_font = isset( $font_file['full_path'] );
					$extra_class = '';

					if( $is_default_font )
					{
						$cl = [ 'av-default-font-activate' ];

						if( isset( $font_file['is_activ'] ) )
						{
							$cl[] = "av-is-active-{$font_file['is_activ']}";
						}

						$extra_class = implode( ' ', $cl );
					}

					$output .= "<div class='avia-available-font {$extra_class}' data-font='{$font_name}'>";
					$output .=		"<span class='avia-font-name'>{$desc}: {$readable} ({$font_name})</span>";

					if( ! $is_default_font )
					{
						$output .= "<a href='#delete-{$font_name}' data-delete='{$font_name}' class='avia-del-font'>" . __( 'Delete', 'avia_framework' ) . '</a>';
					}
					else
					{
						$output .= "<span class='avia-def-font' data-delete='{$font_name}'>(Default)</span>";

						if( isset( $font_file['is_activ'] ) )
						{
							$output .= "<a href='#toggle-{$font_name}' data-delete='{$font_name}' class='avia-default-toggle avia-deactived-font'>" . __( 'Click to activate', 'avia_framework' ) . '</a>';
							$output .= "<a href='#toggle-{$font_name}' data-delete='{$font_name}' class='avia-default-toggle avia-actived-font'>" . __( 'Click to deactivate', 'avia_framework' ) . '</a>';
						}
					}

					$output .= '</div>';
				}
			}

			$output .= '</div>';

			return $output;
		}

		/**
		 * Allows to add additional iconfonts used by 3-rd party like e.g. ALB
		 * Should be called very early when loading
		 *
		 * @since 7.0
		 * @param array $list
		 */
		static public function add_extra_iconfonts( array $list )
		{
			self::$extra_iconfonts = array_merge( self::$extra_iconfonts, $list );
		}

		/**
		 * Adds a shortcut mapping array
		 *
		 * e.g.
		 *		array(
		 *			'standard' => array( 'font' => 'entypo-fontello', 'icon' => 'ue836' )
		 *			....
		 *		)
		 *
		 * @since 7.0
		 * @param array $shortcuts
		 */
		static public function add_icon_shortcuts( array $shortcuts = [] )
		{
			avia_font_manager::$icon_shortcuts = array_merge( avia_font_manager::$icon_shortcuts, $shortcuts );
		}

		/**
		 * Returns array of shortcut icons
		 *
		 * @since 7.0
		 * @return array
		 */
		static public function get_icon_shortcuts()
		{
			return avia_font_manager::$icon_shortcuts;
		}

		/**
		 * Return the mapped values for a shortcode icon string
		 *
		 * @since 7.0
		 * @param string $icon
		 * @return bool|array
		 */
		static public function get_shortcut_icon( $icon )
		{
			if( ! isset( avia_font_manager::$icon_shortcuts[ $icon ] ) )
			{
				return false;
			}

			return avia_font_manager::$icon_shortcuts[ $icon ];
		}

		/**
		 *
		 * @since ????
		 * @since 7.0				added default fonts to option to allow deactivation/activation
		 * @return array
		 */
		static public function load_iconfont_list()
		{
			if( ! empty( self::$iconlist ) )
			{
				return self::$iconlist;
			}

			$extra_fonts = get_option( 'avia_builder_fonts', [] );
			$deactivated_fonts = get_option( 'avia_builder_fonts_deactivated', [] );

			if( ! is_array( $extra_fonts ) )
			{
				$extra_fonts = array();
			}

			$font_configs = array_merge( self::$extra_iconfonts, $extra_fonts );

			foreach( $deactivated_fonts as $font => $config )
			{
				if( isset( $font_configs[ $font ] ) )
				{
					$font_configs[ $font ]['is_activ'] = 'no';
				}
			}

			//if we got any include the charmaps and add the chars to an array
			$upload_dir = wp_upload_dir();
			$path = trailingslashit( $upload_dir['basedir'] );
			$url = trailingslashit( $upload_dir['baseurl'] );

			if( is_ssl() )
			{
				$url = str_replace( 'http://', 'https://', $url );
			}

			foreach( $font_configs as $font => $config )
			{
				if( empty( $config['full_path'] ) )
				{
					$font_configs[ $font ]['include'] = $path . $font_configs[ $font ]['include'];
					$font_configs[ $font ]['folder'] = $url . $font_configs[ $font ]['folder'];
				}
			}

			$library = avia_SVG()->read_svg_from_media_library();
			if( ! empty( $library ) )
			{
				$font_configs['svg_wp-media-library']['config'] = 'wp-media-library';
			}

			//cache the result
			self::$iconlist = $font_configs;

			return $font_configs;
		}

		/**
		 * Fetch default and extra iconfonts that were uploaded and merge them into an array
		 *
		 * @since ????
		 * @return array
		 */
		static public function load_charlist()
		{
			if( ! empty( self::$charlist ) )
			{
				return self::$charlist;
			}

			//	clear for a correct reload
			self::$charnames = array();

			$char_sets = array();
			$char_names = array();
			$char_svgfiles = array();
			$font_configs = self::load_iconfont_list();

			foreach( $font_configs as $font_name => $config )
			{
				$chars = array();
				$names = [];

				if( 'wp-media-library' == $config['config'] )
				{
					$library = avia_SVG()->read_svg_from_media_library();

					foreach( $library as $id => $post )
					{
						$title = trim( esc_html( $post->post_title ) );
						if( '' == $title )
						{
							$title = "SVG_ID_{$id}";
						}

						$chars[ $font_name ][ $id ] = $title;

						$name_info = [
								'attachment_id'	=> $id,
								'title'			=> $title,
								'description'	=> esc_html( $post->post_content ),
								'alt'			=> esc_html( $post->post_excerpt ),
								'search_text'	=> $title
							];

						$names[ $font_name ][ $id ] = $name_info;
					}
				}
				else
				{
					include( $config['include'] . '/' . $config['config'] );

					$names = self::load_char_names( $font_name, $config );
				}

				if( ! empty( $chars ) )
				{
					$char_sets = array_merge( $char_sets, $chars );
				}



				if( ! empty( $names ) )
				{
					$char_names = array_merge( $char_names, $names );
				}

				/**
				 * map iconfont to svg icons
				 * =========================
				 */
				if( ! empty( $config['svg_files'] ) )
				{
					$chars_svg = [];
					include( $config['include'] . '/' . $config['svg_files'] );

					if( ! empty( $chars_svg ) )
					{
						$char_svgfiles = array_merge( $char_svgfiles, $chars_svg );
					}
				}
			}

			//cache the results
			self::$charlist = $char_sets;
			self::$charnames = $char_names;
			self::$char_svgfiles = $char_svgfiles;

			return $char_sets;
		}

		/**
		 * Scans the json file and returns an array with
		 *
		 *		'iconfont_name'		=>  array(  character_key  =>  name   )
		 *    or
		 *		'iconfont_name'		=>  array(  svg icon name  =>  array( config info )   )
		 *
		 * @since 5.6.11
		 * @param string $font_name
		 * @param array $config
		 * @return array
		 */
		static protected function load_char_names( $font_name, $config )
		{
			$chars = [];
			$names = [ $font_name => $chars ];

			$json_file_name = ! empty( $config['json'] ) ? $config['json'] : 'config.json';
			$json_file = null;

			if( ! empty( $config['include'] ) )
			{
				$json_file = trailingslashit( $config['include'] ) . $json_file_name;
			}
			else if( ! empty( $config['folder'] ) )
			{
				$folder_path = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $config['folder'] );
				$json_file = trailingslashit( $folder_path ) . $json_file_name;
			}

			if( empty( $json_file ) || ! file_exists( $json_file ) )
			{
				if( defined( 'WP_DEBUG' ) && WP_DEBUG )
				{
					error_log( "******* Font config file not found (or empty) for font '{$font_name}' at expected path: {$json_file}");
				}

				return $names;
			}

			$config_content = file_get_contents( $json_file );

			if( false !== $config_content )
			{
				$config_json = json_decode( $config_content, true );

				if( ! avia_font_manager::are_svg_icons( $font_name ) )
				{
					if( ! empty( $config_json ) && ! empty( $config_json['glyphs'] ) && is_array( $config_json['glyphs'] ) )
					{
						foreach( $config_json['glyphs'] as $glyph )
						{
							if( ! isset( $glyph['code'] ) || ! isset( $glyph['css'] ) )
							{
								continue;
							}

							// Correctly format the character code to hex and ensure it matches CSS notation
							$css_code = 'u' . str_pad( dechex( $glyph['code'] ), 4, '0', STR_PAD_LEFT );
							$chars[ $css_code ] = $glyph['css'];
						}
					}
				}
				else
				{
					/**
					 * Customize meta values for svg icon set icons
					 *
					 * @link https://github.com/KriesiMedia/enfold-library/blob/master/actions%20and%20filters/Images%20and%20Lightbox/avf_svg_icon_set_config.php
					 * @since 7.0
					 * @param array $config_json
					 * @param string $font_name
					 * @return array
					 */
					$config_json = apply_filters( 'avf_svg_icon_set_config', $config_json, $font_name );

					if( ! empty( $config_json[ $font_name ] ) )
					{
						foreach( $config_json[ $font_name ] as $icon_name => $info )
						{
							$chars[ $icon_name ] = $info;
						}
					}
				}
			}

			$names[ $font_name ] = $chars;
			return $names;
		}

		/**
		 * Helper function that creates the necessary css code to include a custom font
		 *
		 * @since ????
		 * @return string
		 */
		static public function load_font()
		{
			$font_configs = self::load_iconfont_list();

			$output = '';

			if( ! empty( $font_configs ) )
			{
				$output .= '<style type="text/css">';

				foreach( $font_configs as $font_name => $font_list )
				{
					if( avia_font_manager::are_svg_icons( $font_name ) )
					{
						continue;
					}

					if( isset( $font_list['is_activ'] ) && 'no' == $font_list['is_activ'] )
					{
						continue;
					}

					$append = empty( $font_list['append'] ) ? '' : $font_list['append'];
					$qmark	= empty( $append ) ? '?' : $append;

					$fstring = $font_list['folder'] . '/' . $font_name;

					/**
					 * Allow to change default behaviour of browsers when loading external fonts
					 * https://developers.google.com/web/updates/2016/02/font-display
					 *
					 * @since 4.5.6
					 * @param string $font_display
					 * @param string $font_name
					 * @return string				auto | block | swap | fallback | optional
					 */
					$font_display = apply_filters( 'avf_font_display', avia_get_option( 'custom_font_display', '' ), $font_name );
					$font_display = empty( $font_display ) ? 'auto' : $font_display;

					$output .= "
		@font-face {font-family: '{$font_name}'; font-weight: normal; font-style: normal; font-display: {$font_display};
		src: url('{$fstring}.woff2{$append}') format('woff2'),
		url('{$fstring}.woff{$append}') format('woff'),
		url('{$fstring}.ttf{$append}') format('truetype'),
		url('{$fstring}.svg{$append}#{$font_name}') format('svg'),
		url('{$fstring}.eot{$append}'),
		url('{$fstring}.eot{$qmark}#iefix') format('embedded-opentype');
		}

		#top .avia-font-{$font_name}, body .avia-font-{$font_name}, html body [data-av_iconfont='{$font_name}']:before{ font-family: '{$font_name}'; }
		";
				}

				$output .= '</style>';
			}

			/**
			 * @since 4.5.5
			 * @param string $output
			 * @return string
			 */
			return apply_filters( 'avf_font_manager_load_font', $output );
		}

		/**
		 * Helper function that displays the icon symbol string in the frontend
		 *
		 * Kept for backwards compatibilty with icon font - to support inline svg icons
		 * use avia_font_manager::get_frontend_icon()
		 *
		 * @since ????
		 * @deprecated 7.0
		 * @param string $icon
		 * @param string|boolean $font
		 * @param string $return
		 * @param boolean $aria_hidden
		 * @return string|array
		 */
		static public function frontend_icon( $icon, $font = false, $return = 'string', $aria_hidden = true )
		{
			_deprecated_function( 'avia_font_manager::frontend_icon', '7.0', 'Use avia_font_manager::get_frontend_icon instead' );

			//if we got no font passed use the default font
			if( empty( $font ) )
			{
//				$font = key( self::$extra_iconfonts );
				$font = avia_font_manager::find_default_font( $icon );
			}

			//fetch the character to display
			$display_char = self::get_display_char( $icon, $font );

			$aria_hidden = true === $aria_hidden ? 'true' : 'false';

			if( ! avia_font_manager::are_svg_icons( $font ) )
			{
				//return the html string that gets attached to the element. CSS classes for font display are generated automatically
				if( $return == 'string' )
				{
					return "aria-hidden='{$aria_hidden}' data-av_icon='{$display_char}' data-av_iconfont='{$font}'";
				}

				return $display_char;
			}

			if( $return == 'string' )
			{
				$char = [
						'attr'	=> "aria-hidden='{$aria_hidden}' data-av_svg_icon='{$icon}' data-av_iconset='{$font}'",
						'svg'	=> $display_char
					];

				return $char;
			}

			// svg tag
			return $display_char;
		}

		/**
		 * Returns class string for an svg icon shortcode
		 *
		 * @since 7.0
		 * @param string $icon
		 * @return string
		 */
		static public function get_shortcut_icon_class( $icon )
		{
			$class = '';

			$shortcut = avia_font_manager::get_shortcut_icon( $icon );

			if( is_array( $shortcut ) && ! empty( $shortcut['font'] ) )
			{
				$class = "avia-svg-icon avia-font-{$shortcut['font']}";
			}

			return $class;
		}

		/**
		 * Returns the requested shortcut icon array or 'svg__standard' or 'standard'
		 *
		 * @since 7.0
		 * @param string $icon
		 * @param string $icon_type			'svg' | 'font'
		 * @return array
		 */
		static public function get_fallback_shortcut_icon( $icon, $icon_type = 'svg' )
		{
			$result = [
						'icon'	=> $icon,
						'font'	=> false
					];

			$shortcut = avia_font_manager::get_shortcut_icon( $icon );

			if( empty( $shortcut ) || ! is_array( $shortcut ) || empty( $shortcut['font'] ) )
			{
				$shortcut = avia_font_manager::get_shortcut_icon( 'svg__standard' );
			}

			if( is_array( $shortcut ) )
			{
				if( ! empty( $shortcut['icon'] ) )
				{
					$result['icon'] = $shortcut['icon'];
				}

				if( ! empty( $shortcut['font'] ) )
				{
					$result['font'] = $shortcut['font'];
				}
			}

			return $result;
		}

		/**
		 * Checks for a shortcut icon and returns 'svg__standard' if the shortcut does not exist
		 *
		 * @since 7.0
		 * @param string $icon
		 * @param array $additional_atts
		 * @return array
		 */
		static public function get_frontend_shortcut_icon( $icon, array $additional_atts = [] )
		{
			$shortcut = avia_font_manager::get_fallback_shortcut_icon( $icon );

			return avia_font_manager::get_frontend_icon( $shortcut['icon'], $shortcut['font'], $additional_atts );
		}

		/**
		 * Returns the code for an icon in frontend
		 *
		 *		- icon font returns attributes for icon container - CSS classes for font display are generated automatically
		 *		- svg icon returns both to be added
		 *
		 * @since 7.0
		 * @param string $icon
		 * @param string|false $font				false checks for shortcut icons
		 * @param array $additional_atts			are added to attributes of iconfont | to <svg> of svg icon
		 * @return array
		 */
		static public function get_frontend_icon( $icon, $font = false, array $additional_atts = [] )
		{
			$shortcut = false;

			if( false === $font )
			{
				$shortcut = avia_font_manager::get_shortcut_icon( $icon );
			}

			if( is_array( $shortcut ) )
			{
				$icon = $shortcut['icon'];
				$font = $shortcut['font'];
			}
			else
			{
				if( empty( $font ) )
				{
					$font = avia_font_manager::find_default_font( $icon );
				}
			}

			avia_font_manager::switch_to_svg( $font, $icon );

			$display_char = avia_font_manager::get_display_char( $icon, $font, '', $additional_atts );

			if( ! avia_font_manager::are_svg_icons( $font ) )
			{
				$atts = [];
				foreach( $additional_atts as $key => $value )
				{
					$atts[] = $key . '="' . esc_attr( $value ) . '"';
				}

				$char = [
						'attr'	=> "data-av_icon='{$display_char}' data-av_iconfont='{$font}' " . implode( ' ', $atts ),
						'svg'	=> '',
						'icon'	=> $icon,
						'font'	=> $font
					];
			}
			else
			{
				$char = [
						'attr'	=> "data-av_svg_icon='{$icon}' data-av_iconset='{$font}'",
						'svg'	=> $display_char,
						'icon'	=> $icon,
						'font'	=> $font
					];
			}

			return $char;
		}

		/**
		 * Returns HTML for Read More arrow that can be used in blog excerpts. RTL is supported.
		 *
		 * @since 7.0
		 * @param array $additional_atts
		 * @param string $tag
		 * @return string
		 */
		static public function html_more_link_arrow( array $additional_atts = [], $tag = 'span' )
		{
			$default_atts = [
						'title'			=> '',
						'desc'			=> '',
						'aria-hidden'	=> 'true'
					];

			$atts = array_merge( $default_atts, $additional_atts );

			$icon_value = ! is_rtl() ? 'right-open-big' : 'left-open-big';
			$display_char = avia_font_manager::get_frontend_icon( $icon_value, 'svg_entypo-fontello', $atts );
			$char_class = avia_font_manager::get_frontend_icon_classes( $display_char['font'], 'string' );

			$html  =	"<{$tag} class='more-link-arrow {$char_class}' {$display_char['attr']}>";
			$html .=		$display_char['svg'];
			$html .=	"</{$tag}>";

			return $html;
		}

		/**
		 * Returns HTML for a shortcut icon
		 *
		 * @since 7.0
		 * @param string $icon
		 * @param string $extra_class
		 * @param array $additional_atts
		 * @param string $tag
		 * @return string
		 */
		static public function html_frontend_shortcut_icon( $icon, $extra_class = '', array $additional_atts = [], $tag = 'span' )
		{
			$shortcut = avia_font_manager::get_fallback_shortcut_icon( $icon );

			return avia_font_manager::html_frontend_icon( $shortcut['icon'], $shortcut['font'], $extra_class, $additional_atts, $tag );
		}

		/**
		 * Returns HTML for an icon
		 *
		 * @since 7.0
		 * @param string $icon
		 * @param string $extra_class
		 * @param array $additional_atts
		 * @param string $tag
		 * @return string
		 */
		static public function html_frontend_icon( $icon, $font = false, $extra_class = '', array $additional_atts = [], $tag = 'span' )
		{
			$display_char = avia_font_manager::get_frontend_icon( $icon, $font, $additional_atts );
			$char_class = avia_font_manager::get_frontend_icon_classes( $display_char['font'], 'string' );

			$html  = "<{$tag} class='{$extra_class} {$char_class}' {$display_char['attr']}>";
			$html .=		$display_char['svg'];
			$html .= "</{$tag}>";

			return $html;
		}

		/**
		 * Should return a string to be added directly into CSS class.
		 * Suggested by ChatGTP - but does not work.
		 * Only left for testing purpose.
		 *
		 * @since 7.0
		 * @param type $icon
		 * @param array $additional_atts
		 * @return type
		 */
		static public function css_frontend_shortcut_icon( $icon, array $additional_atts = [] )
		{
			$shortcut = avia_font_manager::get_fallback_shortcut_icon( $icon );

			return avia_font_manager::css_frontend_icon( $shortcut['icon'], $shortcut['font'], $additional_atts );
		}

		/**
		 * Should return a string to be added directly into CSS class.
		 * Suggested by ChatGTP - but does not work.
		 * Only left for testing purpose.
		 *
		 * @since 7.0
		 * @param type $icon
		 * @param array $additional_atts
		 * @return type
		 */
		static public function css_frontend_icon( $icon, $font = false, array $additional_atts = [] )
		{
			if( ! isset( $additional_atts['fill'] ) )
			{
				$additional_atts['fill'] = 'currentColor';
			}

			$display_char = avia_font_manager::get_frontend_icon( $icon, $font, $additional_atts );
//			$char_class = avia_font_manager::get_frontend_icon_classes( $display_char['font'], 'string' );

			if( ! avia_font_manager::are_svg_icons( $display_char['font'] ) )
			{
				$c = "content:'\\" . str_replace( 'ue', 'E', $display_char['icon'] ) . "';";
				$f = "font-family:'". $display_char['font'] . "';";

				$html  = ":before{ {$c} {$f} }";
				return $html;
			}

			$svg_encoded = 'data:image/svg+xml;charset=UTF-8,' . rawurlencode( $display_char['svg'] );
//			$svg_encoded = 'data:image/svg+xml;charset=UTF-8,' .  $display_char['svg'];

			$html  = "{
					width: 1em;
					height: 1em;
					background-image: url('{$svg_encoded}');
					background-size: contain;
					background-repeat: no-repeat;
					background-position: center;
				}";

			return $html;
		}

		/**
		 * Helper function that displays the icon symbol in backend
		 *
		 * @since ????
		 * @param array $params
		 * @return array
		 */
		static public function backend_icon( $params )
		{
			$icon = ! empty( $params['args']['icon'] ) ? $params['args']['icon'] : '_new_';
			$font = isset( $params['args']['font'] ) ? $params['args']['font'] : '';

			if( '_new_' == $icon )
			{
				avia_font_manager::set_new_backend( $icon, $font );
			}
			else if( empty( $font ) )
			{
				$font = avia_font_manager::find_default_font( $icon );
			}

			$display_char = avia_font_manager::get_display_char( $icon, $font );

			return array( 'display_char' => $display_char, 'font' => $font );
		}

		/**
		 * Needed for backward fallback in case an icon is requested without rendering a font
		 * In this case we use 'entypo-fontello' if hex string
		 *
		 * @since 7.0
		 * @param string $icon
		 * @return string
		 */
		static public function find_default_font( $icon = '' )
		{
			if( ( strlen( $icon ) >= 4 ) && ( preg_match( '/[0-9a-f]{4}$/i', $icon ) === 1 ) )
			{
				return 'entypo-fontello';
			}

			return key( self::$extra_iconfonts );
		}

		/**
		 * Returns:
		 *		- charactercode of icon when iconfont
		 *		- <svg> including markup to add inline
		 *		- name of svg icon
		 *
		 * @since ????
		 * @since 7.0					added $svg_content, $additional_atts
		 * @param string $icon
		 * @param string $font
		 * @param string $svg_content				'' | 'svg_key'
		 * @param array $additional_atts			used for svg icons to add attributes to <svg> tag
		 * @return string
		 */
		static public function get_display_char( $icon, $font, $svg_content = '', array $additional_atts = [] )
		{
			//load a list of all fonts + characters that are used by the builder (includes default font and custom uploads merged into a single array)
			$chars = avia_font_manager::load_charlist();

			if( ! avia_font_manager::are_svg_icons( $font ) )
			{
				//set the display character if it exists
				$display_char = isset( $chars[ $font ][ $icon ] ) ? $chars[ $font ][ $icon ] : '';

				//json decode the character if necessary
				$display_char = avia_font_manager::try_decode_icon( $display_char );
			}
			else
			{
				if( 'svg_key' == $svg_content )
				{
					$display_char = $icon;
				}
				else
				{
					$display_char = avia_font_manager::get_raw_svg_icon_html( $icon, $font, $additional_atts );
				}
			}

			return $display_char;
		}

		/**
		 * Returns the icon font character name or the search text for svg icon
		 *
		 * @since 7.0
		 * @param string $char
		 * @param string $font
		 * @return string
		 */
		static public function get_char_search_text( $char, $font )
		{
			if( avia_font_manager::are_svg_icons( $font ) )
			{
				if( isset( avia_font_manager::$charnames[ $font ] ) && isset( avia_font_manager::$charnames[ $font ][ $char ] ) )
				{
					if( isset( avia_font_manager::$charnames[ $font ][ $char ]['search_text'] ) )
					{
						return avia_font_manager::$charnames[ $font ][ $char ]['search_text'];
					}
				}
			}

			return avia_font_manager::get_char_name( $char, $font );
		}

		/**
		 * Returns the icon font character name or the hex value
		 *
		 * @since 5.6.11
		 * @param string $char
		 * @param string $font
		 * @return string
		 */
		static public function get_char_name( $char, $font )
		{
			if( avia_font_manager::are_svg_icons( $font ) )
			{
				if( isset( avia_font_manager::$charnames[ $font ] ) && isset( avia_font_manager::$charnames[ $font ][ $char ] ) )
				{
					$name = avia_font_manager::$charnames[ $font ][ $char ]['title'];
				}
				else
				{
					$name = __( 'SVG Icon', 'avia_framework' ) . " {$char}" . ' ( ' .  __( 'missing svg file for this icon', 'avia_framework' ) . ' )';
				}

				return $name;
			}

			// Handle both string and integer character codes.
			$css_code = is_int( $char ) ? '\\' . strtoupper( dechex( $char ) ) : '\\' . $char;

			// Remove backslash to ensure we can find this as a key in the array.
			$css_code = ltrim( $css_code, '\\' );

			$name = '';

			if( isset( avia_font_manager::$charnames[ $font ] ) && isset( avia_font_manager::$charnames[ $font ][ $css_code ] ) )
			{
				$name = avia_font_manager::$charnames[ $font ][ $css_code ] . " (\\{$css_code})";
			}
			else
			{
				$name = __( 'Charcode', 'avia_framework' ) . " \\{$css_code}" . ' ( ' .  __( 'unknown iconname', 'avia_framework' ) . ' )';
			}

			return $name;
		}

		/**
		 * Returns the raw svg tag for a given svg icon - can be added directly into code
		 *
		 * @since 7.0
		 * @param string $icon_char
		 * @param string $font
		 * @param array $additional_atts
		 * @return string
		 */
		static public function get_raw_svg_icon_html( $icon_char, $font, array $additional_atts = [] )
		{
			$html = '';

			// makes sure to load all needed info
			$chars = avia_font_manager::load_charlist();

			$names = avia_font_manager::$charnames;
			$list = avia_font_manager::$iconlist;

			if( empty( $names[ $font ][ $icon_char ] )  )
			{
				return $html;
			}

			/**
			 * Modify svg icons meta data like title, description, alt, search text
			 * Allows to skip editing config.json file
			 *
			 * @since 7.0
			 * @param array $names[ $font ][ $icon_char ]
			 * @param string $icon_char
			 * @param string $font
			 * @param array $additional_atts
			 * @return array
			 */
			$icon_info = apply_filters( 'avf_svg_icon_info', $names[ $font ][ $icon_char ], $icon_char, $font, $additional_atts );

			if( isset( $icon_info['attachment_id'] ) )
			{
				$html .= avia_SVG()->get_icon_html( $icon_info['attachment_id'], [], $icon_char, $font, $additional_atts );
			}
			else
			{
				$path = trailingslashit( $list[ $font ]['include'] ) . $icon_info['path'];
				$html .= avia_SVG()->get_icon_html( $path, $icon_info, $icon_char, $font, $additional_atts );
			}

			return $html;
		}

		/**
		 * Sets first icon as default backend icon for selected icon font (or first icon font).
		 * $icon is expected to be unknown on entry
		 *
		 * @since ????
		 * @since 7.0					completly refactored
		 * @param string $icon
		 * @param string $font
		 * @return void
		 */
		static protected function set_new_backend( &$icon, &$font )
		{
			$chars = avia_font_manager::load_charlist();
			$new_font = '';

			if( empty( $font ) || empty( $chars[ $font ] ) )
			{
				foreach( $chars as $icon_font => $list )
				{
					if( ! empty( $list ) )
					{
						$new_font = $icon_font;
						break;
					}
				}

				// fallback if no icon fonts added
				if( empty( $new_font ) )
				{
					$icon = '';
					$font = '';
					return;
				}

				$font = $new_font;
			}

			$sorted = $chars[ $font ];
			if( ! avia_font_manager::are_svg_icons( $font ) )
			{
				asort( $sorted );
			}
			else
			{
				ksort( $sorted );
			}

			// array_key_first() PHP > 7.3
			foreach( $sorted as $key => $value )
			{
				$icon = $key;
				break;
			}
		}

		/**
		 * decode icon from \ueXXX; format to actual icon
		 *
		 * @since ????
		 * @param string $icon
		 * @return string
		 */
		static public function try_decode_icon( $icon )
		{
			if( strpos( $icon, 'u' ) === 0 )
			{
				$icon = json_decode( '"\\' . $icon . '"' );
			}

			return $icon;
		}

		/**
		 * modify icon if neccessary for compat reasons with special chars or older builder versions
		 *
		 * @since ????
		 * @deprecated 5.6.7
		 * @param int|string $key
		 * @return string
		 */
		static protected function try_modify_key( $key )
		{
			_deprecated_function( 'avia_font_manager::try_modify_key', '5.6.7', 'No longer needed' );

			return $key;

			//compatibility for the old iconfont that was based on numeric values
			if( is_numeric( $key ) )
			{
				$key = self::get_char_from_fallback( $key );
			}

			//chars that are based on multiple chars like \ueXXX\ueXXX; need to be modified before passed
			if( ! empty( $key ) && strpos( $key, 'u', 1 ) !== false )
			{
				$key = explode( 'u', $key );
				$key = implode( '\u', $key );
				$key = substr( $key, 1 );
			}

			return $key;
		}

		/**
		 *
		 * @since ????
		 * @deprecated 5.6.7
		 * @param string $key
		 * @return string
		 */
		static protected function get_char_from_fallback( $key )
		{
			_deprecated_function( 'avia_font_manager::get_char_from_fallback', '5.6.7', 'No longer supported' );

			return $key;

			$font = key( AviaBuilder::$default_iconfont );

			if( empty( self::$charlist_fallback ) )
			{
				$config = AviaBuilder::$default_iconfont[ $font ];
				$chars = array();

				@include( $config['include'] . '/' . $config['compat'] );
				self::$charlist_fallback = $chars;
			}

			$key = $key - 1;
			$key = self::$charlist_fallback[ $font ][ $key ];

			return $key;
		}

		/**
		 * Checks if the given font name are svg icons
		 *
		 * @since 7.0
		 * @param string $font_name
		 * @return boolean
		 */
		static public function are_svg_icons( $font_name )
		{
			return false !== strpos( $font_name, 'svg_' );
		}

		/**
		 * Get array of classes to identify svg icon or iconfont icon
		 *
		 * @since 7.0
		 * @param string $font_name
		 * @param string $return				'array' | 'string'
		 * @return array|string
		 */
		static public function get_frontend_icon_classes( $font_name, $return = 'array' )
		{
			$class = [];

			if( empty( $font_name ) )
			{
				$font_name = avia_font_manager::find_default_font();
			}

			if( avia_font_manager::are_svg_icons( $font_name ) )
			{
				$class[] = 'avia-svg-icon';
			}
			else
			{
				$class[] = 'avia-iconfont';
			}

			$class[] = "avia-font-{$font_name}";

			if( 'array' == $return )
			{
				return $class;
			}

			return implode( ' ', $class );
		}

		/**
		 * Checks if the given font name exists in font list
		 *
		 * @since 7.0
		 * @param string $font_name
		 * @return boolean
		 */
		static public function font_exists( $font_name )
		{
			return isset( avia_font_manager::$charlist[ $font_name ] );
		}

		/**
		 * Switches from a default iconfont to svg icons on the fly when the default font is deactivated
		 * Is intended for existing sites.
		 *
		 * @since 7.0
		 * @param string $font_name
		 * @param string $icon
		 */
		static public function switch_to_svg( &$font_name, &$icon )
		{
			if( avia_font_manager::are_svg_icons( $font_name ) )
			{
				return;
			}

			/**
			 * @since 7.0
			 * @param boolean $switch_iconfont_to_svg
			 * @param string $font_name
			 * @param string $icon
			 * @return boolean
			 */
			if( true !== apply_filters( 'avf_switch_iconfont_to_svg', true, $font_name, $icon ) )
			{
				return;
			}

			$list = avia_font_manager::load_iconfont_list();

			if( ! isset( $list[ $font_name ] ) || ! isset( $list[ 'svg_' . $font_name ] ) )
			{
				return;
			}

			$config = $list[ $font_name ];

			if( ! isset( $config['is_activ'] ) || $config['is_activ'] != 'no' )
			{
				return;
			}

			avia_font_manager::load_charlist();

			if( ! isset( avia_font_manager::$char_svgfiles[ $font_name ] ) || ! isset( avia_font_manager::$char_svgfiles[ $font_name ][ $icon ] ) )
			{
				return;
			}

			$icon = avia_font_manager::$char_svgfiles[ $font_name ][ $icon ];
			$font_name = 'svg_' . $font_name;
		}
	}
}


######################################################################
# Shortcut functions to display the iconfont icons in front and backend
######################################################################


/**
 * easily access the icon string, or char. you have to manually pass each param
 *
 * @since ????
 * @deprecated since 7.0
 * @param string $icon
 * @param string|false $font
 * @param string $string
 * @return string
 */
function av_icon( $icon, $font = false, $string = 'string' )
{
	_deprecated_function( 'av_icon', '7.0', 'Use avia_font_manager::get_frontend_icon() instead' );

	return avia_font_manager::frontend_icon( $icon, $font, $string );
}

/**
 * used for the backend. simply pass the $paramas array of the shortcode that contains font and icon value
 *
 * @since ????
 * @deprecated since 7.0
 * @param array $params
 * @return array
 */
function av_backend_icon( $params )
{
	_deprecated_function( 'av_backend_icon', '7.0', 'Use avia_font_manager::backend_icon() instead' );

	return avia_font_manager::backend_icon( $params );
}

/**
 * pass a string that matches one of the key of the global font_icons array to get the font class
 *
 * @since ????
 * @deprecated since 7.0
 * @param string $char
 * @return string
 */
function av_icon_class( $char )
{
	global $avia_config;

	_deprecated_function( 'av_icon_class', '7.0', 'No longer used by Enfold' );

	return 'avia-font-' . $avia_config['font_icons'][ $char ]['font'];
}

/**
 * pass a string that matches one of the key of the global font_icons array to get the encoded icon
 *
 * @since ????
 * @deprecated since 7.0
 * @param string $char
 * @return string
 */
function av_icon_char( $char )
{
	global $avia_config;

	_deprecated_function( 'av_icon_char', '7.0', 'No longer used by Enfold' );

	return avia_font_manager::frontend_icon( $avia_config['font_icons'][ $char ]['icon'], $avia_config['font_icons'][ $char ]['font'], false );
}

/**
 * Pass a string that matches one of the key of the global font_icons array to get the whole string
 *
 * @since ????
 * @deprecated since 7.0
 * @param string $char
 * @param boolean $aria_hidden
 * @return string
 */
function av_icon_string( $char, $aria_hidden = true )
{
	global $avia_config;

	_deprecated_function( 'av_icon_string', '7.0', 'Use avia_font_manager::html_frontend_shortcut_icon() or similar instead' );

	if( ! isset( $avia_config['font_icons'][ $char ]['icon'] ) )
	{
		$char = 'standard';
	}

	return avia_font_manager::frontend_icon( $avia_config['font_icons'][ $char ]['icon'], $avia_config['font_icons'][ $char ]['font'], 'string', $aria_hidden );
}

/**
 * pass a string that matches one of the key of the global font_icons array to get a css rule with content:$icon and font-family:$font
 *
 * @used_by						..\css\dynamic-css.php
 * @since ????
 * @param string $char
 * @return string
 */
function av_icon_css_string( $char )
{
	global $avia_config;

	return "content:'\\" . str_replace( 'ue', 'E', $avia_config['font_icons'][ $char ]['icon'] ) . "'; font-family:'". $avia_config['font_icons'][ $char ]['font'] . "';";
}

/**
 * pass a string that matches one of the key of the global font_icons array to get the whole icon in a neutral span
 *
 * @since ????
 * @deprecated since 7.0
 * @param string $char
 * @param string $extra_class
 * @return string
 */
function av_icon_display( $char, $extra_class = '' )
{
	_deprecated_function( 'av_icon_display', '7.0', 'Use avia_font_manager::html_frontend_shortcut_icon() or avia_font_manager::html_frontend_icon() instead' );

	return "<span class='av-icon-display {$extra_class}' " . av_icon_string( $char ) . '></span>';
}
