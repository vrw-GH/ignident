<?php
/**
 * Element icons
 *
 * Turns the icon an element declares in config['icon'] into markup for the builder.
 *
 * Our own icons are SVG files under avia-template-builder/icons/. They are read from
 * disk and written into the page as real <svg>, which is what lets the stylesheet
 * colour them - an <img> cannot be styled. The file itself stays where it is and keeps
 * its own URL, so it can still be opened, inspected or swapped like any other asset.
 *
 * Anything that does not resolve inside that folder - a bitmap, or an icon registered
 * by a plugin - is returned as an <img> exactly as before. That fallback is the
 * compatibility path and the security boundary at once: file contents are inlined
 * unescaped, so only files we ship may be inlined.
 *
 * @since 8.0
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'aviaElementIcons', false ) )
{
	class aviaElementIcons
	{
		/**
		 * @since 8.0
		 * @var aviaElementIcons
		 */
		static private $instance = null;

		/**
		 * File contents, keyed by absolute path. An element icon is drawn once per element
		 * in the picker and once per instance on the canvas, so a page with many elements
		 * would otherwise read the same handful of files over and over.
		 *
		 * @since 8.0
		 * @var array
		 */
		protected $cache;

		/**
		 * Resolved real path of the icons folder, or false when it is missing.
		 *
		 * @since 8.0
		 * @var string|false
		 */
		protected $base;

		/**
		 * @since 8.0
		 * @return aviaElementIcons
		 */
		static public function instance()
		{
			if( is_null( aviaElementIcons::$instance ) )
			{
				aviaElementIcons::$instance = new aviaElementIcons();
			}

			return aviaElementIcons::$instance;
		}

		protected function __construct()
		{
			$this->cache = array();
			$this->base = false;
		}

		public function __destruct()
		{
			unset( $this->cache );
		}

		/**
		 * Markup for an element icon.
		 *
		 * @since 8.0
		 * @param string $icon			value of config['icon'] - a URL, or a bare icon name
		 * @param string $name			element name, used for the title and alt text
		 * @param string $extra_class	added to the wrapping <svg> or <img>
		 * @return string
		 */
		public function get_html( $icon, $name = '', $extra_class = '' )
		{
			if( ! is_string( $icon ) || '' === trim( $icon ) )
			{
				return '';
			}

			$class = 'avia-element-icon';
			$class .= ! empty( $extra_class ) ? ' ' . $extra_class : '';

			$svg = $this->get_svg( $icon );

			if( false !== $svg )
			{
				$title = '' !== $name ? '<title>' . esc_html( $name ) . '</title>' : '';

				//	the file is authored without a class or a role, both are added here
				$svg = preg_replace( '/<svg /', '<svg class="' . esc_attr( $class ) . '" role="img" aria-hidden="true" ', $svg, 1 );

				return preg_replace( '/(<svg[^>]*>)/', '$1' . $title, $svg, 1 );
			}

			return '<img class="' . esc_attr( $class ) . '" src="' . esc_url( $icon ) . '" title="' . esc_attr( $name ) . '" alt="" />';
		}

		/**
		 * File contents of an icon, or false when it is not one of ours.
		 *
		 * @since 8.0
		 * @param string $icon
		 * @return string|false
		 */
		public function get_svg( $icon )
		{
			$file = $this->resolve( $icon );

			if( false === $file )
			{
				return false;
			}

			if( ! array_key_exists( $file, $this->cache ) )
			{
				$svg = file_get_contents( $file );
				$this->cache[ $file ] = ( false === $svg || '' === trim( $svg ) ) ? false : trim( $svg );
			}

			return $this->cache[ $file ];
		}

		/**
		 * Maps an icon to a readable file inside the icons folder.
		 *
		 * Accepts the URL an element stores, or a bare name like 'sc-button'. Everything
		 * else - and anything that resolves outside the folder - returns false.
		 *
		 * @since 8.0
		 * @param string $icon
		 * @return string|false
		 */
		protected function resolve( $icon )
		{
			$base = $this->get_base_path();

			if( false === $base )
			{
				return false;
			}

			$name = basename( parse_url( $icon, PHP_URL_PATH ) ?: $icon );

			if( '' === $name || '.svg' !== strtolower( substr( $name, -4 ) ) )
			{
				return false;
			}

			/**
			 * Filter the file an element icon is loaded from, so a child theme can replace a
			 * single icon by pointing at its own file. Return a path outside the icons folder
			 * and it is used as given - the caller is trusted, unlike a bare config['icon'].
			 *
			 * @since 8.0
			 * @param string $file			absolute path, may not exist yet
			 * @param string $name			file name, e.g. 'sc-button.svg'
			 * @param string $icon			the original config['icon'] value
			 * @return string
			 */
			$file = apply_filters( 'avf_builder_element_icon_file', $base . $name, $name, $icon );

			if( ! is_string( $file ) || ! is_readable( $file ) || ! is_file( $file ) )
			{
				return false;
			}

			$real = realpath( $file );

			if( false === $real )
			{
				return false;
			}

			//	an unfiltered icon must sit inside the folder - a filtered one has been vouched for
			if( $file === $base . $name && 0 !== strpos( $real, $base ) )
			{
				return false;
			}

			return $real;
		}

		/**
		 * @since 8.0
		 * @return string|false			trailing slash included
		 */
		protected function get_base_path()
		{
			if( false === $this->base )
			{
				$path = isset( AviaBuilder::$path['iconsPath'] ) ? AviaBuilder::$path['iconsPath'] : '';
				$real = '' !== $path ? realpath( $path ) : false;

				$this->base = ( false !== $real ) ? trailingslashit( $real ) : null;
			}

			return is_null( $this->base ) ? false : $this->base;
		}
	}
}

if( ! function_exists( 'Avia_Element_Icons' ) )
{
	/**
	 * @since 8.0
	 * @return aviaElementIcons
	 */
	function Avia_Element_Icons()
	{
		return aviaElementIcons::instance();
	}
}
