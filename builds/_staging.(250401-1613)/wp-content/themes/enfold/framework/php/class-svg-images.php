<?php
/**
 * Base class to handle svg image support
 *
 *
 * @since 4.8.7
 * @added_by Günter
 */
if( ! defined( 'AVIA_FW' ) ) { exit( 'No direct script access allowed' ); }


if( ! class_exists( 'aviaSVGImages', false ) )
{
	class aviaSVGImages extends aviaFramework\base\object_properties
	{
		/**
		 *
		 * @since 4.8.7
		 * @var aviaSVGImages
		 */
		static protected $_instance = null;

		/**
		 * Cache loaded svg files content
		 *
		 * @since 4.8.7
		 * @var array
		 */
		protected $cache;

		/**
		 * Cache loaded svg metadata content for aria
		 *
		 * @since 5.6.5
		 * @var array
		 */
		protected $aria_cache;

		/**
		 * Holds the queried media library svg
		 *
		 *		'ID' =>  WP_Post
		 *
		 * @since 7.0
		 * @var array
		 */
		protected $library_cache;

		/**
		 * Return the instance of this class
		 *
		 * @since 4.8.7
		 * @return aviaSVGImages
		 */
		static public function instance()
		{
			if( is_null( aviaSVGImages::$_instance ) )
			{
				aviaSVGImages::$_instance = new aviaSVGImages();
			}

			return aviaSVGImages::$_instance;
		}

		/**
		 * @since 4.8.7
		 */
		protected function __construct()
		{
			$this->cache = [];
			$this->aria_cache = [];
			$this->library_cache = null;

			add_filter( 'upload_mimes', array( $this, 'handler_upload_mimes' ), 999 );

			//	WP 4.7.1 and 4.7.2 fix
			add_filter( 'wp_check_filetype_and_ext', array( $this, 'handler_wp_fix_check_filetype_and_ext' ), 10, 4 );
		}

		/**
		 * @since 4.8.7
		 */
		public function __destruct()
		{
			unset( $this->cache );
			unset( $this->aria_cache );
			unset( $this->library_cache );
		}

		/**
		 * Activate svg mime type.
		 * If a plugin activates it, we do not remove this setting.
		 *
		 * @since 4.8.7
		 * @param array $mimes
		 * @return array
		 */
		public function handler_upload_mimes( $mimes = array() )
		{
			/**
			 * Disallow upload of svg files for non admins
			 *
			 * @since 4.8.7
			 * @param boolean $allow_upload
			 * @return boolean            true to allow upload
			 */
			$allow_upload = apply_filters( 'avf_upload_svg_images', current_user_can( 'manage_options' ) );

			if( true === $allow_upload )
			{
				$mimes['svg'] = 'image/svg+xml';
				$mimes['svgz'] = 'image/svg+xml';
			}

			return $mimes;
		}

		/**
		 * Mime Check fix for WP 4.7.1 / 4.7.2
		 * Issue was fixed in 4.7.3 core.
		 *
		 * @since 4.8.7
		 * @param array $checked
		 * @param string $file
		 * @param string $filename
		 * @param array $mimes
		 * @return array
		 */
		public function handler_wp_fix_check_filetype_and_ext( $checked, $file, $filename, $mimes )
		{
			global $wp_version;

			if ( $wp_version !== '4.7.1' || $wp_version !== '4.7.2' )
			{
				return $checked;
			}

			$filetype = wp_check_filetype( $filename, $mimes );

			return array(
						'ext'				=> $filetype['ext'],
						'type'				=> $filetype['type'],
						'proper_filename'	=> $checked['proper_filename']
					);
		}

		/**
		 * Check if a filename is a svg file
		 *
		 * @since 4.8.7
		 * @param string $filename
		 * @return boolean
		 */
		public function is_svg( $filename )
		{
			$mimes = array(
						'svg'	=> 'image/svg+xml',
						'svgz'	=> 'image/svg+xml'
					);

			$filetype = wp_check_filetype( $filename, $mimes );

			return strpos( $filetype['ext'], 'svg' ) !== false;
		}

		/**
		 * Check if we have a svg file we can access and load into cache.
		 * If file is in media library we set the attachment_id.
		 * If not, we try to read the content of the file.
		 *
		 * @since 4.8.7
		 * @param string $url
		 * @param int $attachment_id			0 if file does not exist in media library
		 * @param string $filter_front			'filter' | 'raw'
		 * @return boolean
		 */
		public function exists_svg_file( $url, &$attachment_id, $filter_front = 'filter' )
		{
			$curlSession = false;

			/**
			 * Supress to load svg inline - return anything !== false
			 * Keep in mind, that custom CSS will not target svg content !!
			 *
			 * @since 4.8.7.1
			 * @param boolean $no_inline
			 * @param string $url
			 * @param int $attachment_id
			 * @return boolean
			 */
			$no_inline = apply_filters( 'avf_no_inline_svg', false, $url, $attachment_id );

			if( false !== $no_inline && 'filter' == $filter_front )
			{
				return false;
			}

			try
			{
				if( ! is_numeric( $attachment_id ) || $attachment_id <= 0 )
				{
					$attachment_id = Av_Responsive_Images()->attachment_url_to_postid( $url );
					if( ! is_numeric( $attachment_id ) )
					{
						$attachment_id = 0;
					}
				}

				if( false !== $this->get_raw_svg( $attachment_id, $url ) )
				{
					return true;
				}

				if( $attachment_id > 0 )
				{
					$filename = get_attached_file( $attachment_id );
					if( false === $filename || ! $this->is_svg( $filename ) )
					{
						throw new Exception();
					}

					$svg = ( file_exists( $filename ) ) ? file_get_contents( $filename ) : false;
					if( false === $svg )
					{
						throw new Exception();
					}

					$this->add_to_cache( $attachment_id, $svg );
				}
				else if( false !== $this->is_url( $url ) )
				{
					if( ! $this->is_svg( $url ) )
					{
						throw new Exception();
					}

					if( ! function_exists( 'curl_init' ) )
					{
						throw new Exception();
					}

					$curlSession = curl_init();

					if( false === $curlSession )
					{
						throw new Exception();
					}

					curl_setopt( $curlSession, CURLOPT_URL, $url );
					curl_setopt( $curlSession, CURLOPT_BINARYTRANSFER, true );
					curl_setopt( $curlSession, CURLOPT_RETURNTRANSFER, true );

					/**
					 * https://kriesi.at/support/topic/long-page-loading-ends-with-50x-due-to-false-svg-logo-url/
					 *
					 * @since 5.6.7
					 */
					curl_setopt( $curlSession, CURLOPT_CONNECTTIMEOUT, 1 );
					curl_setopt( $curlSession, CURLOPT_TIMEOUT, 1 );

					$svg = curl_exec( $curlSession );

					curl_close( $curlSession );
					$curlSession = false;

					if( false === $svg )
					{
						throw new Exception();
					}

					$this->add_to_cache( $url, $svg );
				}
				else
				{
					if( ! $this->is_svg( $url ) )
					{
						throw new Exception();
					}

					//	check if we can find the file in local file structure
					$new_file = $url;
					if( ! file_exists( $new_file ) )
					{
						$new_file = ABSPATH . ltrim( $url, '/\\' );
						if( ! file_exists( $new_file ) )
						{
							$new_file = false;
						}
					}

					if( false === $new_file )
					{
						throw new Exception();
					}

					$svg = file_get_contents( $new_file );
					if( false === $svg )
					{
						throw new Exception();
					}

					$this->add_to_cache( $url, $svg );
				}
			}
			catch( Exception $ex )
			{
				if( $curlSession !== false )
				{
					curl_close( $curlSession );
				}

				$attachment_id = 0;
				return false;
			}

			return true;
		}

		/**
		 * Adds content of an svg to cache
		 *
		 * @since 4.8.7
		 * @param int $key
		 * @param string $svg_content
		 */
		protected function add_to_cache( $key, $svg_content )
		{
			$this->cache[ $key ] = trim( $svg_content );
		}

		/**
		 * Checks id svg is in cache and returns raw <svg> element
		 *
		 * IMPORTANT: Make sure to call exists_svg_file() before calling this function
		 *
		 * @since 7.0
		 * @param int $attachment_id
		 * @param string $url
		 * @return bool|string
		 */
		protected function get_raw_svg( $attachment_id, $url )
		{
			$key = is_numeric( $attachment_id ) && $attachment_id > 0 ? $attachment_id : $url;

			if( isset( $this->cache[ $key ] ) )
			{
				return $this->cache[ $key ];
			}

			return false;
		}

		/**
		 * Returns the html content of a svg file
		 *
		 * @since 4.8.7
		 * @since 5.6.5						added $fallback_title
		 * @since 7.0						changed get_html to get_logo_html
		 * @param int $attachment_id
		 * @param string $url
		 * @param string $preserve_aspect_ratio
		 * @param string $fallback_title
		 * @return string
		 */
		public function get_logo_html( $attachment_id, $url, $preserve_aspect_ratio = '', $fallback_title = '' )
		{
			$svg_original = $this->get_raw_svg( $attachment_id, $url );

			if( empty( $svg_original ) )
			{
				return '';
			}

			$svg = $this->set_preserveAspectRatio( $svg_original, $preserve_aspect_ratio );

			if( is_numeric( $attachment_id ) && $attachment_id > 0 )
			{
				$this->set_aria_attributes_logo( $svg, $attachment_id, $fallback_title );
			}

			/**
			 * @since 4.8.7
			 * @since 4.8.8					added $svg_original
			 * @param string $svg
			 * @param int $attachment_id
			 * @param string $url
			 * @param string $preserve_aspect_ratio
			 * @param aviaSVGImages $this
			 * @param string $svg_original
			 * @return string
			 */
			return apply_filters( 'avf_svg_images_get_html', $svg, $attachment_id, $url, $preserve_aspect_ratio, $this, $svg_original );
		}

		/**
		 * Returns the inline svg tag for an svg icon
		 *
		 * @since 7.0
		 * @param string|int $key			attachment ID | filename
		 * @param array $icon_info
		 * @param string $icon_char
		 * @param string $font
		 * @param array $additional_atts
		 * @return string
		 */
		public function get_icon_html( $key, array $icon_info, $icon_char, $font, array $additional_atts = [] )
		{
			if( is_numeric( $key ) && $key > 0 )
			{
				$file = '';
				$attachment_id = $key;
			}
			else
			{
				$file = $key;
				$attachment_id = 0;
			}

			if( ! $this->exists_svg_file( $file, $attachment_id ) )
			{
				return '';
			}

			$svg_original = $this->get_raw_svg( $attachment_id, $file );

			if( $attachment_id > 0 )
			{
				$aria_atts = $this->get_aria_attributes( $attachment_id, $file, '' );
			}
			else
			{
				$aria_atts = [
						'title'	=> $icon_info['title'],
						'desc'	=> $icon_info['description'],
						'alt'	=> $icon_info['alt']
					];
			}

			$aria_atts['role'] = 'graphics-symbol';

			$isColoredSVG = $this->isColoredSVGWithGradients( $svg_original );
			if( $isColoredSVG )
			{
				$aria_atts['is-colored'] = 'true';
			}

			$aria_atts = array_merge( $aria_atts, $additional_atts );

			$preserve_aspect_ratio  = $this->get_alignment( 'center center' );
			$preserve_aspect_ratio .= ' ' . $this->get_display_mode();

			$svg = $this->set_preserveAspectRatio( $svg_original, $preserve_aspect_ratio );

			$svg = $this->set_svg_markup( $svg, $aria_atts );

			/**
			 * Filter to return svg in <img src=""> tag - could be needed for colored icons with same named classes
			 * in multiple svg
			 *
			 * @since 7.0
			 * @param boolean $svg_as_img
			 * @param string $icon_char
			 * @param string $font
			 * @param boolean $isColoredSVG
			 * @param string $svg
			 * @return boolean
			 */
			if( false !== apply_filters( 'avf_svg_images_icon_as_img', false, $icon_char, $font, $isColoredSVG, $svg ) )
			{
				if( is_numeric( $key ) && $key > 0 )
				{
					$url = wp_get_attachment_url( $key );
				}
				else
				{
					$url = $this->local_to_url( $key );
				}

				if( false !== $url )
				{
					$atts = [];

					if( isset( $aria_atts['alt'] ) )
					{
						$atts[] = 'alt="' . esc_attr( $aria_atts['alt'] ) . '"';
					}

					if( isset( $aria_atts['title'] ) )
					{
						$atts[] = 'title="' . esc_attr( $aria_atts['title'] ) . '"';
					}

					$atts = implode( ' ', $atts );

					$svg = "<img src='{$url}' is-svg-img='true' {$atts}>";
				}
			}

			/**
			 * @since 7.0
			 * @param string $svg
			 * @param string $svg_original
			 * @param string|int $key
			 * @param array $icon_info
			 * @param string $icon_char
			 * @param string $font
			 * @param boolean $isColoredSVG
			 * @return string
			 */
			return apply_filters( 'avf_svg_images_get_icon_html', $svg, $svg_original, $key, $icon_info, $icon_char, $font, $isColoredSVG );
		}

		/**
		 * Convert a local filename to a URL. File must be inside child theme or parent theme folder
		 *
		 * @since 7.0
		 * @param string $file
		 * @return string|false
		 */
		protected function local_to_url( $file )
		{
			static $wp_dir = '';
			static $wp_url = '';

			if( empty( $wp_dir ) )
			{
				$wp_dir = str_replace( '\\', '/', WP_CONTENT_DIR );
				$wp_url = str_replace( '\\', '/', WP_CONTENT_URL );
			}

			$check_file = str_replace( '\\', '/', $file );

			$url = false;

			if( false !== strpos( $check_file, $wp_dir ) )
			{
				$url = str_replace( $wp_dir, $wp_url, $check_file );
			}

			/**
			 * @since 7.0
			 * @param string|false $url
			 * @param string $file
			 * @return string|false
			 */
			return apply_filters( 'avf_svg_images_local_to_url', $url, $file );
		}

		/**
		 * Reads attachment metadata for uploaded svg file, fills an array with these values or with $fallback_title,
		 * adds to local cache and retuns it
		 *
		 * @since 7.0
		 * @param int|string $attachment_id
		 * @param string $fallback_title
		 * @return array
		 */
		protected function get_aria_attributes( $attachment_id, $url = '', $fallback_title = '' )
		{
			$key = ! is_numeric( $attachment_id ) || $attachment_id <= 0 ? $url : $attachment_id;

			if( isset( $this->aria_cache[ $key ] ) )
			{
				return $this->aria_cache[ $key ];
			}

			$default = [
					'role'	=> 'graphics-document',
					'title'	=> '',
					'desc'	=> '',
					'alt'	=> ''
				];

			if( ! is_numeric( $key ) )
			{
				$default['title'] = $fallback_title;
				$default['desc'] =  __( 'SVG Image: ', 'avia_framework' ) . $fallback_title;
				$default['alt'] = $default['desc'];

				$this->aria_cache[ $key ] = $default;

				return $default;
			}

			$title = get_the_title( $attachment_id );
			$alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
			$desc = get_the_content( null, false, $attachment_id );

			if( ! empty( $title ) )
			{
				$t = $title;
			}
			else if( ! empty( $alt ) )
			{
				$t = $alt;
			}
			else if( ! empty( $fallback_title ) )
			{
				$t = $fallback_title;
			}
			else
			{
				$t = __( 'SVG Image', 'avia_framework' );
			}

			$default['title'] = $t;

			if( ! empty( $alt ) )
			{
				$default['alt'] = $alt;
			}

			if( ! empty( $desc ) )
			{
				$default['desc'] = $desc;
			}

			$this->aria_cache[ $key ] = $default;
			return $default;
		}

		/**
		 * @since 4.8.7
		 * @param int $attachment_id
		 * @return bool|array
		 */
		public function get_meta_data( $attachment_id )
		{
			if( ! $this->exists_svg_file( '', $attachment_id, 'raw' ) )
			{
				return false;
			}

			if( ! isset( $this->cache[ $attachment_id ] ) )
			{
				return false;
			}

			$svg = trim( $this->cache[ $attachment_id ] );

			$matches = $this->extract_svg_attributes( $svg );
			if( ! is_array( $matches ) )
			{
				return false;
			}

			$atts = $matches[1][0];
			$start = $matches[1][1];
			$len = strlen( $atts );

			$match_size = array();

			preg_match( '#viewBox=(["\'])([a-zA-Z0-9 ]*)(["\'])#im', $atts, $match_size, PREG_OFFSET_CAPTURE );

			//	check if value remains unchanged
			if( ! empty( $match_size ) && isset( $match_size[2] ) && isset( $match_size[2][0] ) )
			{
				return array( 'viewbox' => $match_size[2][0] );
			}

			return false;
		}

		/**
		 * Prepare svg with rendered $preserveAspectRatio.
		 * Checks for a svg tag and returns starting with first tag.
		 * If this check fails an empty string is returned.
		 *
		 * @since 4.8.7
		 * @param string $svg
		 * @param string $preserveAspectRatio
		 * @return string
		 */
		protected function set_preserveAspectRatio( $svg, $preserveAspectRatio = '' )
		{
			$matches = $this->extract_svg_attributes( $svg );
			if( ! is_array( $matches ) )
			{
				return $svg;
			}

			$attributesString = $matches[1][0];
			$start = $matches[1][1];
			$len = strlen( $attributesString );
			$match_preserve = array();

			preg_match( '#preserveAspectRatio=(["\'])([a-zA-Z ]*)(["\'])#im', $attributesString, $match_preserve, PREG_OFFSET_CAPTURE );

			//	check if value remains unchanged
			if( ! empty( $match_preserve ) && isset( $match_preserve[2] ) && isset( $match_preserve[2][0] ) && $match_preserve[2][0] == $preserveAspectRatio )
			{
				return $svg;
			}

			//	no preserveAspectRatio needed
			if( empty( $match_preserve ) && empty( $preserveAspectRatio ) )
			{
				return $svg;
			}

			$ratio = ! empty( $preserveAspectRatio ) ? 'preserveAspectRatio="' . $preserveAspectRatio . '"' : '';

			if( empty( $match_preserve ) )
			{
				$new_atts = $attributesString . ' ' . $ratio;
			}
			else
			{
				$new_atts = str_replace( $match_preserve[0][0], $ratio, $attributesString );
			}

			$new_svg = substr_replace( $svg, $new_atts, $start, $len );

			return $new_svg;
		}

		/**
		 * Adds attributes to svg - including aria support
		 *
		 * If <title> exists as first child in <svg> or 'aria-label' exists we assume that markup is correct for aria support
		 *
		 * @link https://www.unimelb.edu.au/accessibility/techniques/accessible-svgs
		 * @since 7.0
		 * @param string $svg
		 * @param array $aria_atts
		 * @return string
		 */
		protected function set_svg_markup( $svg, array $aria_atts )
		{
			static $count = 0;

			$svg_matches = $this->extract_svg_attributes( $svg );
			if( ! is_array( $svg_matches ) )
			{
				return $svg;
			}

			$count++;
			$remove_atts_keys = [];
			$add_atts = [];
			$add_tags = [];

			$svg_string = $svg_matches[0][0];
			$svg_len = strlen( $svg_string );
			$after_svg_tag = substr( $svg, $svg_len );

			$attributesString = $svg_matches[1][0];

			// split attributes include attributes like aria-*, single and double quotes
			$attrMatches = [];
			$pattern = '/(\w[\w-]*)\s*=\s*["\']([^"\']*)["\']/';
			preg_match_all( $pattern, $attributesString, $attrMatches );

			$svg_atts_strings = $attrMatches[0];
			$svg_atts_keys = $attrMatches[1];

			/**
			 * Add title and desc
			 */
			try
			{
				// Correct structure - for older browsers
				if( in_array( 'aria-label', $svg_atts_keys ) )
				{
					throw new Exception();
				}

				if( 0 === strpos( trim( $after_svg_tag ), '<title' ) )
				{
					throw new Exception();
				}

				if( $aria_atts['title'] )
				{
					$id_title = "av-svg-title-{$count}";

					$add_tags[] = "<title id='{$id_title}'>" . esc_html( $aria_atts['title'] ) . '</title>';
					$add_atts[] = "aria-labelledby='{$id_title}'";
					$remove_atts_keys[] = 'aria-labelledby';

					if( $aria_atts['desc'] )
					{
						$id_desc = "av-svg-desc-{$count}";

						$add_tags[] = "<desc id='{$id_desc}'>" . esc_html( $aria_atts['desc'] ) . '</desc>';
						$add_atts[] = "aria-describedby='{$id_desc}'";
						$remove_atts_keys[] = 'aria-describedby';
					}
				}
			}
			catch( Exception $ex )
			{
			}

			if( in_array( 'role', $svg_atts_keys ) )
			{
				unset( $aria_atts['role'] );
			}

			if( in_array( 'is-colored', $svg_atts_keys ) )
			{
				unset( $aria_atts['is-colored'] );
			}

			unset( $aria_atts['title'] );
			unset( $aria_atts['desc'] );
			unset( $aria_atts['alt'] );		// not needed for svg

			foreach( $aria_atts as $attr => $value )
			{
				$remove_atts_keys[] = $attr;
			}

			//	insert tags
			$tag_string = implode( "\n", $add_tags );
			if( ! empty( $tag_string ) )
			{
				$svg = substr_replace( $svg, "\n" . $tag_string, $svg_len, 0 );
			}

			$svg_string_new = $svg_string;

			//	remove not needed svg attributes
			foreach( $remove_atts_keys as $remove_key )
			{
				//	we need to scan array to eliminate double same name attributes
				foreach( $svg_atts_keys as $index => $value )
				{
					if( $value == $remove_key )
					{
						$svg_string_new = str_replace( $svg_atts_strings[ $index ], '', $svg_string_new );
					}
				}
			}

			foreach( $aria_atts as $attr => $value )
			{
				$add_atts[] = $attr . '="' . esc_attr( $value ) . '"';
			}

			//	add new attributes
			$attr_string = implode( ' ', $add_atts );
			if( ! empty( $attr_string ) )
			{
				$svg_new_insert = strlen( $svg_string_new ) - 1;
				$svg_string_new = substr_replace( $svg_string_new, ' ' . $attr_string, $svg_new_insert, 0 );
			}

			$new_svg = substr_replace( $svg, $svg_string_new, 0, $svg_len );

			return $new_svg;
		}

		/**
		 * Add aria attributes to svg container for logo (only added when logo is in media gallery)
		 * https://accessibilityinsights.io/info-examples/web/svg-img-alt/
		 * https://dequeuniversity.com/rules/axe/4.1/svg-img-alt
		 *
		 * @since 5.6.5
		 * @param string $svg
		 * @param array $atts
		 */
		protected function set_aria_attributes_logo( &$svg, $attachment_id, $fallback_title = '' )
		{
			$pos = strpos( $svg, '<svg ' );

			if( false === $pos )
			{
				return;
			}

			/*
			 *
			 * @since 5.6.5
			 * @param boolean $ignore
			 * @param string $svg
			 * @param int $attachment_id
			 * @return boolean
			 */
			if( false !== apply_filters( 'avf_ignore_svg_aria_attributes', false, $svg, $attachment_id ) )
			{
				return;
			}

			if( ! is_numeric( $attachment_id ) || $attachment_id <= 0 )
			{
				return;
			}



//			if( ! isset( $this->aria_cache[ $attachment_id ] ) )
//			{
//				$this->aria_cache[ $attachment_id ]['role'] = 'graphics-document';
//
//				$title = get_the_title( $attachment_id );
//				$alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
//
//				if( ! empty( $title ) )
//				{
//					$t = $title;
//				}
//				else if( ! empty( $alt ) )
//				{
//					$t = $alt;
//				}
//				else if( ! empty( $fallback_title ) )
//				{
//					$t = $fallback_title;
//				}
//				else
//				{
//					$t = __( 'SVG Image', 'avia_framework' );
//				}
//
//				$this->aria_cache[ $attachment_id ]['title'] = esc_attr( $t );
//
//				if( ! empty( $alt ) )
//				{
//					$this->aria_cache[ $attachment_id ]['alt'] = esc_attr( $alt );
//				}
//			}
//
//			$atts = $this->aria_cache[ $attachment_id ];


			$atts = $this->get_aria_attributes( $attachment_id, '', $fallback_title );

			/**
			 * @since 5.6.5
			 * @param array $atts
			 * @param int $attachment_id
			 * @param string $svg
			 */
			$atts = apply_filters( 'avf_set_svg_aria_attributes', $atts, $attachment_id, $fallback_title );

			$matches = $this->extract_svg_attributes( $svg );
			if( is_array( $matches ) )
			{
				$svg_atts = $matches[1][0];

				foreach( $atts as $key => $value )
				{
					if( false === stripos( $svg_atts, " {$key}=" ) )
					{
						continue;
					}

					if( 0 === stripos( $svg_atts, "{$key}=" ) )
					{
						continue;
					}

					unset( $atts[ $key ] );
				}
			}

			$aria = '';

			foreach( $atts as $key => $value )
			{
				$aria .= $key . '="' . $value . '" ';
			}

			$svg = substr_replace( $svg, " {$aria} ", $pos + 4, 0 );
		}

		/**
		 * Split first svg and return the preg_match array and a modified svg string
		 *
		 * @since 4.8.8
		 * @param string $svg
		 * @return array|false
		 */
		protected function extract_svg_attributes( &$svg )
		{
			$svg = trim( $svg );

			$pos = strpos( $svg, '<svg ' );

			if( false === $pos )
			{
				return false;
			}

			//	remove everything before first svg tag
			if( $pos > 0 )
			{
				$svg = substr( $svg, $pos );
			}

			/**
			 * extract attributes of first svg - splitting tag in multipe lines is allowed
			 *
			 * https://kevin.deldycke.com/2007/03/ultimate-regular-expression-for-html-tag-parsing-with-php/
			 */
			$regex = "/<\/?\w+((\s+(\w|\w[\w-]*\w)(\s*=\s*(?:\".*?\"|'.*?'|[^'\">\s]+))?)+\s*|\s*)\/?>/i";
			$matches = array();
			if( ! preg_match( $regex, $svg, $matches, PREG_OFFSET_CAPTURE ) )
			{
				return false;
			}

			if( ! isset( $matches[1] ) )
			{
				return false;
			}

			return $matches;
		}

		/**
		 * Returns the alignment of the svg
		 *
		 * @since 4.8.7
		 * @param string $align
		 * @return string
		 */
		public function get_alignment( $align = 'center center' )
		{
			switch( $align )
			{
				case 'left top':
					$att = 'xMinYMin';
					break;
				case 'left center':
					$att = 'xMinYMid';
					break;
				case 'left bottom':
					$att = 'xMinYMax';
					break;
				case 'center top':
					$att = 'xMidYMin';
					break;
				case 'center center':
					$att = 'xMidYMid';
					break;
				case 'center bottom':
					$att = 'xMidYMax';
					break;
				case 'right top':
					$att = 'xMaxYMin';
					break;
				case 'right center':
					$att = 'xMaxYMid';
					break;
				case 'right bottom':
					$att = 'xMaxYMax';
					break;
				case 'none':
					$att = 'none';
					break;
				default:
					$att = 'xMidYMid';
					break;
			}

			return $att;
		}

		/**
		 * Get attribute to slice or scale image
		 *
		 * @since 4.8.7
		 * @param string $behaviour			'slice' | 'meet'
		 * @return string
		 */
		public function get_display_mode( $behaviour = 'meet' )
		{
			switch( $behaviour )
			{
				case 'slice':
					$att = 'slice';
					break;
				case 'meet':
				default:
					$att = 'meet';
					break;
			}

			return $att;
		}

		/**
		 * Return the preserveAspectRatio attribute value according to header settings
		 *
		 * @since 4.8.7
		 * @return string
		 */
		public function get_header_logo_aspect_ratio()
		{
			$header_pos = avia_get_option( 'header_layout' );
			$preserve = '';

			if( false !== strpos( $header_pos, 'logo_left' ) )
			{
				$preserve = $this->get_alignment( 'left center');
			}
			else if( false !== strpos( $header_pos, 'logo_right' ) )
			{
				$preserve = $this->get_alignment( 'right center');
			}
			else if( false !== strpos( $header_pos, 'logo_center' ) )
			{
				$preserve = $this->get_alignment( 'center center');
			}

			if( ! empty( $preserve ) )
			{
				$preserve .= ' ' . $this->get_display_mode();
			}

			/**
			 *
			 * @since 4.8.7
			 * @param string $preserve
			 * @param string $header_pos
			 * @return string
			 */
			return apply_filters( 'avf_svg_images_header_logo_aspect_ratio', $preserve, $header_pos );
		}

		/**
		 * Checks is a string starts with http://, https:// or localhost/
		 *
		 * @since 4.8.7
		 * @param string $test_url
		 * @return boolean
		 */
		protected function is_url( $test_url )
		{
			if( false !== stripos( $test_url, 'http://' ) || false !== stripos( $test_url, 'https://' ) || false !== stripos( $test_url, 'localhost/' ) )
			{
				return true;
			}

			return false;
		}

		/**
		 * Checks if we have a colored or monochrome svg
		 *
		 * @since 7.0
		 * @param string $svgContent
		 * @return boolean
		 * @throws Exception
		 */
		public function isColoredSVGWithGradients( $svgContent )
		{
			//	supress internal xml errors
			libxml_use_internal_errors( true );

			// Laden des SVG-Inhalts als XML
			$xml = simplexml_load_string( $svgContent );

			if( ! $xml )
			{
				libxml_clear_errors();
				return false;
			}

			$namespaces = $xml->getNamespaces( true );

			$namespaceUri = $namespaces[''] ?? 'http://www.w3.org/2000/svg';
			$xml->registerXPathNamespace( 'svg', $namespaceUri );

			$colors = [];

			// 1. Prüfen auf `fill` und `stroke` Attribute
			foreach( $xml->xpath( '//*[@fill] | //*[@stroke]' ) as $element)
			{
				if( isset( $element['fill'] ) && (string)$element['fill'] !== 'none' )
				{
					$colors[] = (string)$element['fill'];
				}

				if( isset($element['stroke'] ) && (string)$element['stroke'] !== 'none' )
				{
					$colors[] = (string)$element['stroke'];
				}
			}

			// 2. Prüfen auf Farbverläufe (`<linearGradient>` und `<radialGradient>`)
			foreach( $xml->xpath( '//svg:linearGradient | //svg:radialGradient' ) as $gradient )
			{
				$stops = $gradient->xpath('.//svg:stop');
				if( $stops === false )
				{
					continue;
				}

				foreach( $stops as $stop )
				{
					if( isset( $stop['stop-color'] ) && (string)$stop['stop-color'] !== 'none' )
					{
						$colors[] = (string)$stop['stop-color'];
					}
				}
			}

			// 3. Prüfen auf `style`-Attribute
			foreach( $xml->xpath('//*[@style]') as $element )
			{
				$style = (string)$element['style'];

				if( preg_match_all( '/(fill|stroke):\s*([^;]+)/', $style, $matches ) )
				{
					$colors = array_merge( $colors, $matches[2] );
				}
			}

			// 4. Prüfen auf eingebettete CSS-Styles
			foreach( $xml->xpath('//svg:style' ) as $styleBlock )
			{
				$styleContent = (string)$styleBlock;

				if ( preg_match_all( '/(fill|stroke):\s*([^;]+)/', $styleContent, $matches ) )
				{
					$colors = array_merge( $colors, $matches[2] );
				}
			}

			// Filterung der Farben: Entfernen von "none", leeren Werten und standardisierten Schwarz (#000000, black)
			$filteredColors = array_filter( array_unique( $colors ), function ( $color )
			{
				$color = strtolower( $color );
				return $color !== 'none' && $color !== '#000000' && $color !== 'black';
			} );

			libxml_clear_errors();

			// 5. Rückgabe: Wenn es nach dem Filtern Farben gibt, ist das SVG farbig
			return count( $filteredColors ) > 0;
		}

		/**
		 * Reads all svg from media library and stores the query result in $this->library_cache
		 *
		 * @since 7.0
		 * @return array[WP_Post]
		 */
		public function read_svg_from_media_library()
		{
			global $wpdb;

			if( is_array( $this->library_cache ) )
			{
				return $this->library_cache;
			}

			$this->library_cache = [];

			/**
			 * @since 7.0
			 * @param boolean $use_svg_from_media_library
			 * @return boolean
			 */
			if( true !== apply_filters( 'avf_use_svg_from_media_library', true ) )
			{
				return $this->library_cache;
			}

			$query = "
SELECT *
FROM {$wpdb->posts}
WHERE post_type = 'attachment'
  AND post_mime_type = 'image/svg+xml'
  AND post_status = 'inherit'
";

			$svg_files_found = $wpdb->get_results( $query );

			/**
			 * @since 7.0
			 * @param array $svg_files_found
			 * @return array
			 */
			$svg_files = apply_filters( 'avf_queried_svg_from_media_library', $svg_files_found );

			if( ! empty( $svg_files ) )
			{
				foreach( $svg_files as $file )
				{
					$this->library_cache[ $file->ID ] = $file;
				}
			}

			return $this->library_cache;
		}
	}

	/**
	 * Returns the main instance of aviaSVGImages to prevent the need to use globals.
	 *
	 * @since 4.8.7
	 * @return aviaSVGImages
	 */
	function avia_SVG()
	{
		return aviaSVGImages::instance();
	}

	//	activate class
	avia_SVG();
}
