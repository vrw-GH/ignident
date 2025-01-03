<?php
/**
 * Icon Font Management Class
 *
 * Should be changed to extend enfold\framework\php\font-management\class-avia-font-management-base.php in future
 *
 * @since ????
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
		 * Array containing characters for icon font
		 *
		 *		'iconfont_name'		=>  array(  $key => $value   )
		 *
		 * @since ????
		 * @var array
		 */
		static protected $charlist = array();

		/**
		 * Array containing names for icon font characters
		 *
		 *		'iconfont_name'		=>  array(  $key => $icon_name   )
		 *
		 * @since 5.6.11
		 * @var array
		 */
		static protected $charnames = array();

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
		 *
		 * @since ????
		 */
		public function __construct()
		{
			global $avia_config;

			$this->paths = wp_upload_dir();
			$this->svg_file = '';
			$this->font_name  = 'unknown';
			$this->origin_font_name = '';
			$this->svg_config = array();

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

			$this->paths['fonts'] 	= $dynamic_dir;
			$this->paths['temp']  	= trailingslashit( $this->paths['fonts'] ) .'avia_temp';
			$this->paths['fontdir'] = trailingslashit( $this->paths['basedir'] ) . $this->paths['fonts'];
			$this->paths['tempdir'] = trailingslashit( $this->paths['basedir'] ) . $this->paths['temp'];
			$this->paths['fonturl'] = trailingslashit( $this->paths['baseurl'] ) . $this->paths['fonts'];
			$this->paths['tempurl'] = trailingslashit( $this->paths['baseurl'] ) . trailingslashit( $this->paths['temp'] );
			$this->paths['config']	= 'charmap.php';
			$this->paths['json']	= 'config.json';

			//font file extract by ajax function
			add_action( 'wp_ajax_avia_ajax_add_zipped_font', array( $this, 'handler_add_zipped_font' ) );
			add_action( 'wp_ajax_avia_ajax_remove_zipped_font', array( $this, 'handler_remove_zipped_font' ) );
		}

		/**
		 * @since 5.3
		 */
		public function __destruct()
		{
			unset( $this->paths );
			unset( $this->svg_config );
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
			$unzipped = $this->zip_flatten( $path , array( '\.eot', '\.svg', '\.ttf', '\.woff', '\.woff2', '\.json' ) );

			// if we were able to unzip the file and save it to our temp folder extract the svg file
			if( $unzipped )
			{
				$this->create_config();
			}

			//if we got no name for the font dont add it and delete the temp folder
			if( $this->font_name == 'unknown' )
			{
				$this->delete_folder( $this->paths['tempdir'] );
				exit( __( 'Was not able to retrieve the Font name from your Uploaded Folder', 'avia_framework' ) );
			}

			exit( 'avia_font_added:' . $this->font_name );
		}

		/**
		 * @since ????
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
			$font 		= $_POST['del_font'];
			$list 		= self::load_iconfont_list();
			$delete		= isset( $list[ $font ] ) ? $list[ $font ] : false;

			if( $delete )
			{
				$this->delete_folder( $delete['include'] );
				$this->remove_font( $font);

				exit( 'avia_font_removed' );
			}

			exit( __( 'Was not able to remove Font', 'avia_framework' ) );
		}

		/**
  		 * extract the zip file to a flat folder and remove the files that are not needed
     		 *
		 * @since ????
		 * @param string $zipfile
		 * @param array $filter
		 * @return boolean
		 */
		public function zip_flatten( $zipfile, $filter = array() )
		{
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
				exit( __( 'Wasn\'t able to create temp folder', 'avia_framework' ) );
			}

			$zip = new ZipArchive;

			if( $zip->open( $zipfile ) )
			{
				$zip_paths = pathinfo( $zipfile );

				//check name scheme if user wants to rename the file
				if( isset( $zip_paths['filename'] ) && strpos( $zip_paths['filename'], 'iconset.' ) === 0 )
				{
					$this->font_name = str_replace( 'iconset.', '', $zip_paths['filename'] );
				}

				for( $i = 0; $i < $zip->numFiles; $i++ )
				{
					$entry = $zip->getNameIndex( $i );

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

					$fp = $zip->getStream( $entry );
					$ofp = fopen( $this->paths['tempdir'] . '/' . basename( $entry), 'w' );

					if( ! $fp )
					{
						exit( __( 'Unable to extract the file.', 'avia_framework' ) );
					}

					while ( ! feof( $fp ) )
					{
						fwrite( $ofp, fread( $fp, 8192 ) );
					}

					fclose( $fp );
					fclose( $ofp );
				}

				$zip->close();
			}
			else
			{
				exit( __( 'Wasn\'t able to work with Zip Archive', 'avia_framework' ) );
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


			if( ! is_wp_error( $response ) && ! empty( $response ) )
			{
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
						$unicode	=  (string) $attributes['unicode'];
						$class		=  (string) $attributes['class'];

						if( $class != 'hidden' )
						{
							$unicode_key = trim( json_encode( $unicode), '\\\"' );
							$unicode_key = AviaHelper::save_string( $unicode_key, '-' );

							if( $item == 'glyph' && ! empty( $unicode_key) && trim( $unicode_key ) != '' )
							{
								$this->svg_config[ $this->font_name ][ $unicode_key ] = $unicode_key;
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
			}

			return false;
		}

		/**
		 * writes the php config file for the font
		 *
		 * @since ????
		 */
		protected function write_config()
		{
			$charmap = $this->paths['tempdir'] . '/' . $this->paths['config'];
			$handle = @fopen( $charmap, 'w' );

			if( $handle )
			{
				fwrite( $handle, '<?php $chars = array();' );

				foreach( $this->svg_config[ $this->font_name ] as $unicode )
				{
					if( ! empty( $unicode ) )
					{
						$delimiter = "'";
						if( strpos( $unicode, "'" ) !== false )
						{
							$delimiter = '"';
						}

						fwrite( $handle, "\r\n" . '$chars[\'' . $this->font_name . '\'][' . $delimiter . $unicode . $delimiter . '] = ' . $delimiter . $unicode . $delimiter . ';' );
					}
				}

				fclose( $handle );
			}
			else
			{
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
			$fonts = get_option( 'avia_builder_fonts' );

			if( empty( $fonts ) )
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

			update_option( 'avia_builder_fonts', $fonts );
		}

		/**
		 *
		 * @since ????
		 * @param string $font
		 */
		protected function remove_font( $font )
		{
			$fonts = get_option( 'avia_builder_fonts' );

			if( isset( $fonts[ $font ] ) )
			{
				unset( $fonts[ $font ] );
				update_option( 'avia_builder_fonts', $fonts );
			}
		}

		/**
		 * finds the svg file we need to create the config
		 *
		 * @since ????
		 * @return string
		 */
		protected function find_svg()
		{
			$files = scandir( $this->paths['tempdir'] );

			//fetch the eot file first so we know the acutal filename, in case there are multiple svg files, then based on that find the svg file
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
				if( strpos( strtolower( $file), $filename . '.svg' )  !== false && $file[0] != '.' )
				{
					return $file;
				}
			}
		}

		/**
		 *
		 * @since ????
		 * @param string $output
		 * @param array $element
		 * @return string
		 */
		static public function add_font_manager( $output, array $element )
		{
			if( $element['id'] != 'iconfont_upload' )
			{
				return $output;
			}

			$font_configs = array_merge( array( '{font_name}' => array() ), self::load_iconfont_list() );

			$output .= "<div class='avia_iconfont_manager' data-id='{$element['id']}'>";

			$fonts = get_option( 'avia_builder_fonts' );

			if( ! empty( $font_configs ) )
			{
				foreach( $font_configs as $font_name => $font_file )
				{
					$output .= "<div class='avia-available-font' data-font='{$font_name}'><span class='avia-font-name'>Font: {$font_name}</span>";

					if( ! isset( $font_file['full_path'] ) )
					{
						$output .= "<a href='#delete-{$font_name}' data-delete='{$font_name}' class='avia-del-font'>Delete</a>";
					}
					else
					{
						$output .= "<span class='avia-def-font' data-delete='{$font_name}'>(Default Font)</span>";
					}

					$output .= '</div>';
				}
			}
			$output .= '</div>';

			return $output;
		}

		/**
		 *
		 * @since ????
		 * @return array
		 */
		static protected function load_iconfont_list()
		{
			if( ! empty( self::$iconlist ) )
			{
				return self::$iconlist;
			}

			$extra_fonts = get_option( 'avia_builder_fonts' );

			if( empty( $extra_fonts) )
			{
				$extra_fonts = array();
			}

			$font_configs = array_merge( AviaBuilder::$default_iconfont, $extra_fonts );

			//if we got any include the charmaps and add the chars to an array
			$upload_dir = wp_upload_dir();
			$path = trailingslashit( $upload_dir['basedir'] );
			$url = trailingslashit( $upload_dir['baseurl'] );

			if( is_ssl() )
			{
				$url = str_replace( 'http://', 'https://', $url );
			}

			foreach( $font_configs as $key => $config )
			{
				if( empty( $config['full_path'] ) )
				{
					$font_configs[ $key ]['include'] = $path . $font_configs[ $key ]['include'];
					$font_configs[ $key ]['folder'] = $url . $font_configs[ $key ]['folder'];
				}
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
			$font_configs = self::load_iconfont_list();

			foreach( $font_configs as $font_name => $config )
			{
				$chars = array();
				include( $config['include'] . '/' . $config['config'] );

				if( ! empty( $chars ) )
				{
					$char_sets = array_merge( $char_sets, $chars );
				}

				$names = self::load_char_names( $font_name, $config );

				if( ! empty( $names ) )
				{
					$char_names = array_merge( $char_names, $names );
				}
			}

			//cache the results
			self::$charlist = $char_sets;
			self::$charnames = $char_names;

			return $char_sets;
		}

		/**
		 * Scans the json file and returns an array with the names of the icon or ''
		 *
		 *		'iconfont_name'		=>  array(  character_key  =>  name   )
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
		} #top .avia-font-{$font_name}, body .avia-font-{$font_name}, html body [data-av_iconfont='{$font_name}']:before{ font-family: '{$font_name}'; }
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
		 * @since ????
		 * @param string $icon
		 * @param string|boolean $font
		 * @param string $return
		 * @param boolean $aria_hidden
		 * @return string
		 */
		static public function frontend_icon( $icon, $font = false, $return = 'string', $aria_hidden = true )
		{
			//if we got no font passed use the default font
			if( empty( $font ) )
			{
				$font = key( AviaBuilder::$default_iconfont );
			}

			//fetch the character to display
			$display_char = self::get_display_char( $icon, $font );

			//return the html string that gets attached to the element. css classes for font display are generated automatically
			if( $return == 'string' )
			{
				$aria_hidden = true === $aria_hidden ? 'true' : 'false';
				return "aria-hidden='{$aria_hidden}' data-av_icon='{$display_char}' data-av_iconfont='{$font}'";
			}

			return $display_char;
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
			$font = isset( $params['args']['font'] ) ? $params['args']['font'] : key( AviaBuilder::$default_iconfont );
			$icon = ! empty( $params['args']['icon'] ) ? $params['args']['icon'] : 'new';

			$display_char = self::get_display_char( $icon, $font );

			return array( 'display_char' => $display_char, 'font' => $font );
		}

		/**
		 *
		 * @since ????
		 * @param string $icon
		 * @param string $font
		 * @return string
		 */
		static public function get_display_char( $icon, $font )
		{
			//load a list of all fonts + characters that are used by the builder (includes default font and custom uploads merged into a single array)
			$chars = self::load_charlist();

			//if this function is called by the backend on a new element use the first icon in the list
			$icon = self::set_new_backend( $icon, $chars );

			//check if we need to modify the $icon value (which represents the array key)
//			$icon  = self::try_modify_key( $icon );

			//set the display character if it exists
			$display_char = isset( $chars[ $font ][ $icon ] ) ? $chars[ $font ][ $icon ] : '';

			//json decode the character if necessary
			$display_char = self::try_decode_icon( $display_char );

			return $display_char;
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
			// Handle both string and integer character codes.
			$css_code = is_int( $char ) ? '\\' . strtoupper( dechex( $char ) ) : '\\' . $char;

			// Remove backslash to ensure we can find this as a key in the array.
			$css_code = ltrim( $css_code, '\\' );

			$name = '';

			if( isset( self::$charnames[ $font ] ) && isset( self::$charnames[ $font ][ $css_code ] ) )
			{
				$name = self::$charnames[ $font ][ $css_code ] . " (\\{$css_code})";
			}
			else
			{
				$name = __( 'Charcode', 'avia_framework' ) . " \\{$css_code}" . ' ( ' .  __( 'unknown iconname', 'avia_framework' ) . ' )';
			}

			return $name;
		}

		/**
		 * sets a default backend icon
		 *
		 * @since ????
		 * @param string $icon
		 * @param array $chars
		 * @return string
		 */
		static protected function set_new_backend( $icon, $chars )
		{
			if( $icon == 'new' )
			{
				$char_list = key( $chars );
				asort( $chars[ $char_list ] );
				$icon = key( $chars[ $char_list ] );
			}

			return $icon;
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
	}
}


######################################################################
# Shortcut functions to display the iconfont icons in front and backend
######################################################################


/**
 * easily access the icon string, or char. you have to manually pass each param
 *
 * @since ????
 * @param string $icon
 * @param string|false $font
 * @param string $string
 * @return string
 */
function av_icon( $icon, $font = false, $string = 'string' )
{
	return avia_font_manager::frontend_icon( $icon, $font, $string );
}

/**
 * used for the backend. simply pass the $paramas array of the shortcode that contains font and icon value
 *
 * @since ????
 * @param array $params
 * @return array
 */
function av_backend_icon( $params )
{
	return avia_font_manager::backend_icon( $params );
}

/**
 * pass a string that matches one of the key of the global font_icons array to get the font class
 *
 * @since ????
 * @param string $font
 * @return string
 */
function av_icon_class( $font )
{
	global $avia_config;

	return 'avia-font-' . $avia_config['font_icons'][ $font ]['font'];
}

/**
 * pass a string that matches one of the key of the global font_icons array to get the encoded icon
 *
 * @since ????
 * @param string $char
 * @return string
 */
function av_icon_char( $char )
{
	global $avia_config;

	return avia_font_manager::frontend_icon( $avia_config['font_icons'][ $char]['icon'], $avia_config['font_icons'][ $char]['font'], false );
}

/**
 * Pass a string that matches one of the key of the global font_icons array to get the whole string
 *
 * @since ????
 * @param string $char
 * @param boolean $aria_hidden
 * @return string
 */
function av_icon_string( $char, $aria_hidden = true )
{
	global $avia_config;

	if( ! isset( $avia_config['font_icons'][ $char ]['icon'] ) )
	{
		$char = 'standard';
	}

	return avia_font_manager::frontend_icon( $avia_config['font_icons'][ $char]['icon'], $avia_config['font_icons'][ $char]['font'], 'string', $aria_hidden );
}

/**
 * pass a string that matches one of the key of the global font_icons array to get a css rule with content:$icon and font-family:$font
 *
 * @since ????
 * @param string $char
 * @return string
 */
function av_icon_css_string( $char )
{
	global $avia_config;

	return "content:'\\" . str_replace( 'ue', 'E', $avia_config['font_icons'][ $char]['icon'] ) . "'; font-family:'". $avia_config['font_icons'][ $char]['font'] . "';";
}

/**
 * pass a string that matches one of the key of the global font_icons array to get the whole icon in a neutral span
 *
 * @since ????
 * @param string $char
 * @param string $extra_class
 * @return string
 */
function av_icon_display( $char, $extra_class = '' )
{
	return "<span class='av-icon-display {$extra_class}' " . av_icon_string( $char ) . '></span>';
}

/**
 * add font select ui after file upload button.
 * Need to add it outside of class because of old framework code that executes isntantly instead of hook
 *
 * @since ????
 */
add_filter( 'avf_file_upload_extra', array( 'avia_font_manager', 'add_font_manager' ), 10, 2 );

