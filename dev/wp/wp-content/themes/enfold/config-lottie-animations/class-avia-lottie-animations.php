<?php
/**
 * This class handles integration of animations with LottiFiles https://lottiefiles.com/
 * See readme.txt how to update the bundled js files.
 *
 * @author guenter
 * @since 5.5
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_LottieAnimations', false ) )
{
	class avia_LottieAnimations
	{

		/**
		 * Holds the instance of this class
		 *
		 * @since 5.5
		 * @var avia_LottieAnimations
		 */
		static private $_instance = null;

		/**
		 * Return the instance of this class
		 *
		 * @since 5.5
		 * @return avia_LottieAnimations
		 */
		static public function instance()
		{
			if( is_null( avia_LottieAnimations::$_instance ) )
			{
				avia_LottieAnimations::$_instance = new avia_LottieAnimations();
			}

			return avia_LottieAnimations::$_instance;
		}

		/**
		 * @since 5.5
		 */
		protected function __construct()
		{

			add_filter( 'upload_mimes', array( $this, 'handler_upload_mimes' ), 999 );
			add_action( 'init', array( $this, 'handler_wp_register_scripts' ) );
			add_action( 'wp_footer', array( $this, 'handler_wp_enqueue_footer_scripts' ) );
		}

		/**
		 * @since 5.5
		 */
		public function __destruct()
		{

		}

		/**
		 * Activate svg mime type.
		 * If a plugin activates it, we do not remove this setting.
		 *
		 * @since 5.5
		 * @param array $mimes
		 * @return array
		 */
		public function handler_upload_mimes( $mimes = array() )
		{
			/**
			 * Disallow upload of svg files for non admins
			 *
			 * @since 5.5
			 * @param boolean $allow_upload
			 * @return boolean            true to allow upload
			 */
			$allow_upload = apply_filters( 'avf_upload_lottie_images', current_user_can( 'manage_options' ) );

			if( true === $allow_upload )
			{
				$mimes['json'] = 'text/plain';		//	Use 'text/plain' instead of 'application/json' for JSON because of a current Wordpress core bug
				$mimes['lottie'] = 'application/lottiefiles';		//	image/lottiefiles does not work !!!
			}

			return $mimes;
		}

		/**
		 * Returns the allowed lottie mime types to be used for WP_Query
		 *
		 * @since 5.5
		 * @return array
		 */
		public function lottie_mime_types()
		{
			$mime_types = array( 'text/plain', 'application/lottiefiles' );

			return $mime_types;
		}

		/**
		 * @since 5.5
		 */
		public function handler_wp_register_scripts()
		{
			$version = avia_get_theme_version();

			wp_register_script( 'avia-dotlottie-script', AVIA_BASE_URL . "config-lottie-animations/assets/lottie-player/dotlottie-player.js", [], $version );
			Avia_Builder()->add_registered_admin_script( 'avia-dotlottie-script' );
		}

		/**
		 * Conditionally load scripts only on pages that need this animation
		 *
		 * @since 5.5
		 */
		public function handler_wp_enqueue_footer_scripts()
		{
			if( is_admin() )
			{
				return;
			}

			/**
			 * Enable loading of frontend script
			 *
			 * @used_by				avia_sc_lottie_animation			10
			 * @since 5.5
			 * @param boolean $enable
			 * @return boolean					false | anything else to enable
			 */
			$enabled = apply_filters( 'avf_enable_enqueue_dotlottie_script', false );

			if( false !== $enabled )
			{
				wp_enqueue_script( 'avia-dotlottie-script' );
			}
		}

		/**
		 * Returns an url to a placeholder animation for new added ALB elements
		 *
		 * @since 5.5
		 * @return string
		 */
		public function placeholder_url()
		{
			return apply_filters( 'avf_lottie_placeholder_url', AVIA_BASE_URL . 'config-lottie-animations/assets/133140-birthday-gifts.lottie' );
		}

		/**
		 * Returns a standard player template for ALB editor canvas
		 *
		 * @since 5.5
		 * @param string $url
		 * @return string
		 */
		public function alb_backend_player( $url = '' )
		{
			/**
			 * @since 5.5
			 * @param string $player
			 * @param string $url
			 * @return string
			 */
			return apply_filters( 'avf_lottie_alb_backend_player', '<dotlottie-player autoplay loop mode="normal" style="width: 160px" src="' . esc_attr( $url ) . '"></dotlottie-player>', $url );
		}

		/**
		 * Returns HTML for a frontend player
		 *
		 * @since 5.5
		 * @param array $args
		 * @return string
		 */
		public function player( array $args = [] )
		{
			$default = [
					'src'			=> '',
					'speed'			=> '',
					'autoplay'		=> '',
					'loop'			=> '',
					'hover'			=> '',
					'count'			=> '',
					'direction'		=> '',
					'mode'			=> '',
					'controls'		=> '',
					'width'			=> '',
					'height'		=> '',
					'background'	=> '',
					'lazy_loading'	=> ''
				];

			$params = array_merge( $default, $args );

			if( empty( $params['src'] ) )
			{
				return '';
			}

			$prop = [];

			if( ! empty( $params['count'] ) && is_numeric( $params['count'] ) )
			{
				$prop[] = 'count="' . (int)$params['count'] . '"';
			}

			$styles = [
					'width: 100%',
					'height: auto'
				];

			$params['width'] = $this->valid_size( $params['width'] );
			if( ! empty( $params['width'] ) )
			{
				$styles[] = "max-width: {$params['width']}";
			}

			$params['height'] = $this->valid_size( $params['height'] );
			if( ! empty( $params['height'] ) )
			{
				$styles[] = "max-height: {$params['height']}";
			}

			if( ! empty( $styles ) )
			{
				$prop[] = 'style="' . implode( '; ', $styles ) . ';"';
			}

			$data = json_encode( $params );


			$html = '<dotlottie-player ' . implode( ' ', $prop ) . ' data-av_lottie=\'' . $data . '\'></dotlottie-player>';

			return $html;
		}

		/**
		 * Checks for a valid css unit % or px.
		 * Defaults to px.
		 *
		 * @since 5.5
		 * @param string $value
		 * @return string
		 */
		protected function valid_size( $value )
		{
			$value = trim( $value );

			if( '' == $value )
			{
				return $value;
			}

			if( is_numeric( $value ) )
			{
				return "{$value}px";
			}

			$matches = [];
			$found = preg_match( '/(\d*px)|(\d*\%)/i', $value, $matches, PREG_OFFSET_CAPTURE );

			if( $found && ! empty( $matches ) )
			{
				return $matches[0][0];
			}

			return '';
		}
	}

	/**
	 * Returns the main instance of avia_LottieAnimations to prevent the need to use globals
	 *
	 * @since 5.5
	 * @return avia_LottieAnimations
	 */
	function AviaLottieAnimations()
	{
		return avia_LottieAnimations::instance();
	}

	/**
	 * Activate filter and action hooks
	 */
	AviaLottieAnimations();

}
