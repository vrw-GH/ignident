<?php
namespace aviaFramework\widgets;

/**
 * Base class for managing widgets
 *
 * @author guenter
 * @since 4.9
 */
if( ! defined( 'AVIA_FW' ) ) {  exit( 'No direct script access allowed' );  }

if( ! class_exists( __NAMESPACE__ . '\avia_widget_loader', false ) )
{
	class avia_widget_loader
	{
		/**
		 * Holds the instance of this class
		 *
		 * @since 4.9
		 * @var avia_widget_loader
		 */
		static private $_instance = null;

		/**
		 * Contains the full path to this file
		 *
		 * @since 4.9
		 * @var string
		 */
		protected $widget_path;

		/**
		 * Base namespace including "\"
		 *
		 * @since 4.9
		 * @var string
		 */
		protected $namespace;

		/**
		 * Array of base files to be loaded before widgets (e.g. abstract classes)
		 *
		 *		'name'	=> full path to file
		 *
		 * @since 4.9
		 * @var array
		 */
		protected $base_classes;

		/**
		 * Array of default widgets that is filtered before loading files
		 *
		 *		'name' => array(
		 *						'class'		=> classname (must include namespace !!!)
		 *						'file'		=> full path to file to load
		 *					)
		 *
		 * @since 4.9
		 * @var array
		 */
		protected $default_widgets;

		/**
		 * Return the instance of this class
		 *
		 * @since 4.9
		 * @return avia_widget_loader
		 */
		static public function instance()
		{
			if( is_null( avia_widget_loader::$_instance ) )
			{
				avia_widget_loader::$_instance = new avia_widget_loader();
			}

			return avia_widget_loader::$_instance;
		}

		/**
		 * @since 4.9
		 */
		protected function __construct()
		{
			$pathinfo = pathinfo( __FILE__ );

			$this->widget_path = trailingslashit( $pathinfo['dirname'] );
			$this->namespace = '\\' . __NAMESPACE__ . '\\';

			$this->register_files();

			add_action( 'widgets_init', array( $this, 'handler_widgets_init' ) );

			add_filter( 'body_class', array( $this, 'handler_wp_body_class' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'handler_wp_enqueue_scripts' ) );
		}

		/**
		 * @since 4.9
		 */
		public function __destruct()
		{
			unset( $this->default_widgets );
			unset( $this->base_classes );
		}

		/**
		 * Add specific classes to style with CSS
		 *
		 * @since 4.9
		 * @param array $classes
		 * @return string
		 */
		public function handler_wp_body_class( array $classes )
		{
			if( defined( 'REST_REQUEST' ) )
			{
				$classes[] = 'avia-rest-request';
			}

			if( defined( 'IFRAME_REQUEST' ) )
			{
				$classes[] = 'avia-iframe-request';
			}

			return $classes;
		}

		/**
		 * @since 4.9
		 */
		public function handler_wp_enqueue_scripts()
		{
			if( ! defined( 'IFRAME_REQUEST' ) )
			{
				return;
			}

			$vn = avia_get_theme_version();
			$min_css = avia_minify_extension( 'css' );

			wp_enqueue_style( 'avia-block-widgets', AVIA_CSS_URL . "/conditional_load/avia_block_widgets{$min_css}.css", array( 'avia-layout' ), $vn, 'screen' );
		}

		/**
		 * @since 4.9
		 */
		public function handler_widgets_init()
		{
			/**
			 * Add additional base classes to load
			 * Only remove classes if you are sure you do not break anything
			 *
			 * @since 4.9
			 * @param array $this->base_classes
			 * @return array
			 */
			$base_classes = apply_filters( 'avf_widget_loader_base_classes', $this->base_classes );

			/**
			 * Remove widgets you do not want to load or add custom widgets to autoload
			 *
			 * @used_by						Avia_Instagram_Feed			10
			 *
			 * @since 4.9
			 * @param array $this->default_widgets
			 * @return array
			 */
			$widgets = apply_filters( 'avf_widget_loader_widget_classes', $this->default_widgets );


			foreach( $base_classes as $name => $path )
			{
				require_once( $path );
			}

			foreach( $widgets as $key => $widget_info )
			{
				if( ! empty( $widget_info['file'] ) )
				{
					require_once( $widget_info['file'] );
				}

				if( ! empty( $widget_info['class'] ) )
				{
					register_widget( $widget_info['class'] );
				}
			}
		}

		/**
		 * Returns the namespace for theme widgets
		 *
		 * @since 5.6
		 * @return string
		 */
		public function get_default_namespace()
		{
			return $this->namespace;
		}

		/**
		 * Returns array of all registered widgets by theme
		 *
		 * @since 5.6
		 * @return array
		 */
		public function get_registered_widgets()
		{
			return $this->default_widgets;
		}

		/**
		 * @since 4.9
		 */
		protected function register_files()
		{
			$path = trailingslashit( $this->widget_path . 'base-classes' );

			$this->base_classes = array(
							'avia-widget'	=> $path . 'class-avia-widget.php'
						);


			$path = trailingslashit( $this->widget_path . 'widget-classes' );

			$this->default_widgets = array(

					'fb_likebox'	=> array(
												'class'	=> $this->namespace . 'avia_fb_likebox',
												'file'	=> $path . 'class-avia-fb-likebox.php'
											),

					'newsbox'		=> array(
												'class'	=> $this->namespace . 'avia_newsbox',
												'file'	=> $path . 'class-avia-newsbox.php'
											),

					'portfoliobox'	=> array(
												'class'	=> $this->namespace . 'avia_portfoliobox',
												'file'	=> $path . 'class-avia-portfoliobox.php'
											),

					'socialcount'	=> array(
												'class'	=> $this->namespace . 'avia_socialcount',
												'file'	=> $path . 'class-avia-socialcount.php'
											),
					'partner_widget'	=> array(
												'class'	=> $this->namespace . 'avia_partner_widget',
												'file'	=> $path . 'class-avia-partner.php'
											),

					'one_partner_widget'	=> array(
												'class'	=> $this->namespace . 'avia_one_partner_widget',
												'file'	=> $path . 'class-avia-one-partner.php'
											),

					'combo_widget'	=> array(
												'class'	=> $this->namespace . 'avia_combo_widget',
												'file'	=> $path . 'class-avia-combo.php'
											),

					'google_maps'	=> array(
												'class'	=> $this->namespace . 'avia_google_maps',
												'file'	=> $path . 'class-avia-google-maps.php'
											),

					'instagram'		=> array(
												'class'	=> $this->namespace . 'avia_instagram_widget',
												'file'	=> $path . 'class-avia-instagram.php'
											),

					'auto_toc'		=> array(
												'class'	=> $this->namespace . 'avia_auto_toc',
												'file'	=> $path . 'class-avia-auto-toc.php'
											),

					'mailchimp'		=> array(
												'class'	=> $this->namespace . 'avia_mailchimp_widget',
												'file'	=> $path . 'class-avia-mailchimp.php'
											)

				);

		}
	}

	/**
	 * Returns the main instance of AviaBuilder to prevent the need to use globals
	 *
	 * @since 4.9
	 * @return avia_widget_loader
	 */
	function AviaWidgetLoader()
	{
		return avia_widget_loader::instance();
	}

	//	load class
	AviaWidgetLoader();
}


