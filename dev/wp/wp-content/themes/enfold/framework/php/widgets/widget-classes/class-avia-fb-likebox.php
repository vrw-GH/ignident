<?php
namespace aviaFramework\widgets;

/**
 * AVIA FACEBOOK WIDGET
 *
 * @since ???
 * @since 4.9			Code was moved from class-framework-widgets.php
 */
if( ! defined( 'AVIA_FW' ) ) {  exit( 'No direct script access allowed' );  }


if ( ! class_exists( __NAMESPACE__ . '\avia_fb_likebox', false ) )
{
	class avia_fb_likebox extends \aviaFramework\widgets\base\Avia_Widget
	{
		const AJAX_NONCE = 'avia_fb_likebox_nonce';
		const FB_SCRIPT_ID = 'facebook-jssdk';

		/**
		 *
		 * @var int
		 */
		static protected $script_loaded = 0;

		/**
		 *
		 */
		public function __construct()
		{
			$id_base = 'avia_fb_likebox';
			$name = THEMENAME . ' ' . __( 'Facebook Likebox', 'avia_framework' );

			$widget_options = array(
						'classname'				=> 'avia_fb_likebox avia_no_block_preview',
						'description'			=> __( 'A widget that displays a Facebook Likebox to a Facebook page of your choice', 'avia_framework' ),
						'show_instance_in_rest' => true,
						'customize_selective_refresh' => false
					);

			parent::__construct( $id_base, $name, $widget_options );

			$this->defaults = array(
								'url'				=> 'https://www.facebook.com/kriesi.at',
								'title'				=> __( 'Follow us on Facebook', 'avia_framework' ),
								'fb_link'			=> '',
								'fb_banner'			=> '',
								'page_title'		=> '',
								'fb_logo'			=> '',
								'content'			=> '',
								'add_info'			=> __( 'Join our Facebook community', 'avia_framework' ),
								'confirm_button'	=> __( 'Click to load facebook widget', 'avia_framework' ),
								'page_link_text'	=> __( 'Open Facebook page now', 'avia_framework' )
							);

			add_action( 'init', array( $this, 'handler_wp_register_scripts' ), 500 );
			add_action( 'wp_enqueue_scripts', array( $this, 'handler_wp_enqueue_scripts' ), 500 );
		}

		/**
		 * @since 4.3.2
		 */
		public function __destruct()
		{
			parent::__destruct();
		}

		/**
		 *
		 * @since 4.3.2
		 */
		public function handler_wp_register_scripts()
		{
			$vn = avia_get_theme_version();
			$min_js = avia_minify_extension( 'js' );

			wp_register_script( 'avia_facebook_front_script' , AVIA_JS_URL . "conditional_load/avia_facebook_front{$min_js}.js", array( 'jquery' ), $vn, true );
		}

		/**
		 * @since 4.3.2
		 */
		public function handler_wp_enqueue_scripts()
		{
			$instances = $this->get_settings();
			if( count( $instances ) > 0 )
			{
				$need_js = array( 'confirm_link' );

				foreach( $instances as $instance )
				{
					if( isset( $instance['fb_link'] ) && in_array( $instance['fb_link'], $need_js ) )
					{
						wp_enqueue_script( 'avia_facebook_front_script' );
						break;
					}
				}
			}
		}

		/**
		 * Output the widget in frontend
		 *
		 * @param array $args
		 * @param array $instance
		 */
		public function widget( $args, $instance )
		{
			$instance = $this->parse_args_instance( $instance );

			/**
			 * Filter $instance values on page basis
			 *
			 * @since 5.6
			 * @param array $instance
			 * @param array $args
			 * @param string $context
			 * @return array
			 */
			$instance = apply_filters( 'avf_widget_front_instance', $instance, $args, get_called_class() );


			if( $this->in_block_editor_preview( $args, $instance ) )
			{
				return;
			}


			extract( $args, EXTR_SKIP );
			extract( $instance, EXTR_SKIP );

			if( empty( $url ) )
			{
				return;
			}

			/**
			 * Allow to change the conditional display setting - e.g. if user is opt in and allows to connect directly
			 *
			 * @since 4.4
			 * @param string $google_link			'' | 'confirm_link' | 'page_only'
			 * @param string $context
			 * @param mixed $object
			 * @param array $args
			 * @param array $instance
			 * @return string
			 */

			$original_fb_link = $fb_link;
			$fb_link = apply_filters( 'avf_conditional_setting_external_links', $fb_link, __CLASS__, $this, $args, $instance );
			if( ! in_array( $fb_link, array( '', 'confirm_link', 'page_only' ) ) )
			{
			   $fb_link = $original_fb_link;
			}

			$title = apply_filters( 'widget_title', $title );

			echo $before_widget;

			if ( ! empty( $title ) )
			{
				echo $before_title . $title . $after_title;
			};

			$banner_bg = "";

			if( ! empty( $fb_link ) )
			{
				if( ! empty( $fb_banner ) && ! empty( $fb_link ) )
				{
					$banner_bg = 'style="background-image:url(' . $fb_banner . ');"';
				}

				$link_title = avia_targeted_link_rel( '<a href="' . $url . '" target="_blank" title="' . esc_html( $page_title ) . '">' . esc_html( $page_title ) . '</a>' );

				echo '<div class="av_facebook_widget_main_wrap" ' . $banner_bg . '>';

				echo	'<div class="av_facebook_widget_page_title_container">';
				echo		'<span class="av_facebook_widget_title">';
				echo			$link_title;
				echo		'</span>';
				echo		'<span class="av_facebook_widget_content">';
				echo			esc_html( $content );
				echo		'</span>';
				echo	'</div>';


				$html_logo = '';

				if( ! empty( $fb_logo ) )
				{
					$html_logo .=		'<div class="av_facebook_widget_logo_image">';
					$html_logo .=			'<img src="' . $fb_logo . '" alt="' . __( 'Logo image', 'avia_framework' ) . '">';
					$html_logo .=		'</div>';
				}

				echo '<div class="av_facebook_widget_main_wrap_shadow"></div>';
				echo	'<div class="av_facebook_widget_logo av_widget_img_text_confirm">';

				echo $html_logo;

				echo	'</div>';

				$data = "";
				if( 'confirm_link' == $fb_link )
				{
					$data  = ' data-fbhtml="' . htmlentities( $this->html_facebook_page( $url ), ENT_QUOTES, get_bloginfo( 'charset' ) ) . '"';
					$data .= ' data-fbscript="' . htmlentities( $this->get_fb_page_js_src(), ENT_QUOTES, get_bloginfo( 'charset' ) ) . '"';
					$data .= ' data-fbscript_id="' . avia_fb_likebox::FB_SCRIPT_ID . '"';
				}

				$btn_text = ( 'confirm_link' == $fb_link ) ? $confirm_button : $page_link_text;
				$icon = '<span class="av_facebook_widget_icon" ' . av_icon_string( 'facebook' ) . '></span>';
				echo	avia_targeted_link_rel( '<a href="' . $url . '" target="_blank" class="av_facebook_widget_button av_facebook_widget_' . $fb_link . '"' . $data . '>' .$icon . esc_html( $btn_text ) . '</a>' );

				if( ! empty( $fb_link ) )
				{
					echo	'<div class="av_facebook_widget_add_info">';
					echo		'<div class="av_facebook_widget_add_info_inner">';
					echo			'<span class="av_facebook_widget_add_info_inner_wrap">';
					echo				esc_html( $add_info );
					echo			'</span>';
					echo			'<div class="av_facebook_widget_imagebar">';
					echo			'</div>';
					echo		'</div>';
					echo	'</div>';
				}

				echo '</div>';		//	class="av_facebook_widget_main_wrap"
			}

			if( empty( $fb_link ) )
			{
					echo $this->html_facebook_page( $url );
					add_action( 'wp_footer', array( $this,'handler_output_fb_page_script' ), 10 );
			}

			echo $after_widget;
		}

		/**
		 * Callback to output a custom block preview
		 *
		 * @since 4.9
		 * @param array $args
		 * @param array $instance
		 * @return boolean					true if output processed
		 */
		protected function widget_block_preview( array $args, array $instance = array() )
		{
			echo isset( $args['before_widget'] ) ? $args['before_widget'] : '';

			echo '<div class="avia-preview-headline">' . $this->name . '</div>';
			echo '<div class="avia-preview-info">' . __( 'Title:', 'avia_framework' ) . ' ' . $instance['title'] .  '</div>';
			echo '<div class="avia-preview-info">' . __( 'URL:', 'avia_framework' ) . ' ' . $instance['url'] .  '</div>';
			echo '<div class="avia-preview-in-front">' . __( 'Content is only rendered in frontend.', 'avia_framework' ) . '</div>';

			echo isset( $args['after_widget'] ) ? $args['after_widget'] : '';

			return true;
		}

		/**
		 * Create the HTML for the facebook page widget
		 *
		 * @since 4.3.2
		 * @param string $url
		 * @return string
		 */
		protected function html_facebook_page( $url )
		{
			$extraClass = '';
			$style = '';

//			$height 	= 151;						//	remainings from original widget ?????
//			$faces 		= "true";
//			$extraClass = "";
//			$style 		= "";
//
//
//			if( strpos( $height, "%" ) !== false )
//			{
//				$extraClass = "av_facebook_widget_wrap_positioner";
//				$style		= "style='padding-bottom:{$height}%'";
//				$height		= "100%";
//			}

			$html = '';
			$html .=	"<div class='av_facebook_widget_wrap {$extraClass}' {$style}>";
			$html .=		'<div class="fb-page" data-width="500" data-href="' . $url . '" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true" data-show-posts="false">';
			$html .=			'<div class="fb-xfbml-parse-ignore"></div>';
			$html .=		'</div>';
			$html .=	"</div>";

			return $html;
		}

		/**
		 *
		 * @since 4.3.2
		 */
		public function handler_output_fb_page_script()
		{
			if( self::$script_loaded >= 1 )
			{
				return;
			}

			self::$script_loaded = 1;

			$script = '
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "' . $this->get_fb_page_js_src() . '";
  fjs.parentNode.insertBefore(js, fjs);
}(document, "script", "' . avia_fb_likebox::FB_SCRIPT_ID . '"));</script>';

			echo $script;
		}


		/**
		 * Return the js function
		 * @since 4.3.2
		 * @return string
		 */
		protected function get_fb_page_js_src()
		{
			$langcode = get_locale();

			/**
			 * Change language code for facebook page widget
			 *
			 * @used_by		enfold\config-wpml\config.php				10
			 * @since 4.3.2
			 */
			$langcode = apply_filters( 'avf_fb_widget_lang_code', $langcode, 'fb-page' );

			$src = '//connect.facebook.net/'. $langcode .'/sdk.js#xfbml=1&version=v2.7';

			return $src;
		}


		/**
		 * Update widget options
		 *
		 * @param array $new_instance
		 * @param array $old_instance
		 * @return array
		 */
		public function update( $new_instance, $old_instance )
		{
			$instance = $this->parse_args_instance( $old_instance );
			$fields = $this->get_field_names();

			foreach( $new_instance as $key => $value )
			{
				if( in_array( $key, $fields ) )
				{
					$instance[ $key ] = strip_tags( $value );
				}
			}

			return $instance;
		}


		/**
		 * Output the form in backend
		 *
		 * @param array $instance
		 */
		public function form( $instance )
		{
			$instance = $this->parse_args_instance( $instance );
			$fields = $this->get_field_names();

			foreach( $instance as $key => $value )
			{
				if( in_array( $key, $fields ) )
				{
					$instance[ $key ] = esc_attr( $value );
				}
			}

			extract( $instance );

			$html = new \avia_htmlhelper();

			$banner_element = array(
								'name'		=> __( 'Banner image', 'avia_framework' ),
								'desc'		=> __( 'Upload a banner image or enter the URL', 'avia_framework' ),
								'id'		=> $this->get_field_id( 'fb_banner' ),
								'id_name'	=> $this->get_field_name( 'fb_banner' ),
								'std'		=> $fb_banner,
								'type'		=> 'upload',
								'label'		=> __( 'Use image as banner', 'avia_framework' )
							);

			$logo_element = array(
								'name'		=> __( 'Logo', 'avia_framework' ),
								'desc'		=> __( 'Upload a logo or enter the URL', 'avia_framework' ),
								'id'		=> $this->get_field_id( 'fb_logo' ),
								'id_name'	=> $this->get_field_name( 'fb_logo' ),
								'std'		=> $fb_logo,
								'type'		=> 'upload',
								'label'		=> __( 'Use image as logo', 'avia_framework' )
							);

	?>
		<div class="avia_widget_form avia_widget_conditional_form avia_fb_likebox_form <?php echo $fb_link;?>">
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'avia_framework' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></label>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'url' ); ?>"><?php _e( 'Enter the url to the Page. Please note that it needs to be a link to a <strong>facebook fanpage</strong>. Personal profiles are not allowed!', 'avia_framework' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'url' ); ?>" name="<?php echo $this->get_field_name( 'url' ); ?>" type="text" value="<?php echo $url; ?>" /></label>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'fb_link' ); ?>"><?php _e( 'Link to Facebook', 'avia_framework' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'fb_link' ); ?>" name="<?php echo $this->get_field_name( 'fb_link' ); ?>" class="widefat avia-coditional-widget-select">
					<option value="" <?php selected( '', $fb_link ) ?>><?php _e( 'Show Facebook page widget &quot;Share/Like&quot; directly', 'avia_framework' ); ?></option>
					<option value="confirm_link" <?php selected( 'confirm_link', $fb_link ) ?>><?php _e( 'User must accept to show Facebook page widget &quot;Share/Like&quot;', 'avia_framework' ); ?></option>
					<option value="page_only" <?php selected( 'page_only', $fb_link ) ?>><?php _e( 'Only open the Facebook page - no data are sent', 'avia_framework' ); ?></option>
				</select>
			</p>

			<p class="av-confirm_link">
				<label for="<?php echo $this->get_field_id( 'confirm_button' ); ?>"><?php _e( 'Button text confirm link to Facebook:', 'avia_framework' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'confirm_button' ); ?>" name="<?php echo $this->get_field_name( 'confirm_button' ); ?>" type="text" value="<?php echo $confirm_button; ?>" /></label>
			</p>

			<p class="av-page_only">
				<label for="<?php echo $this->get_field_id( 'page_link_text' ); ?>"><?php _e( 'Direct link to FB-page text:', 'avia_framework' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'page_link_text' ); ?>" name="<?php echo $this->get_field_name( 'page_link_text' ); ?>" type="text" value="<?php echo $page_link_text; ?>" /></label>
			</p>

			<div class="avia_fb_likebox_upload avia-fb-banner av-widgets-upload">
				<?php echo $html->render_single_element( $banner_element );?>
			</div>

			<p  class="av-page-title">
				<label for="<?php echo $this->get_field_id( 'page_title' ); ?>"><?php _e( 'Facebook Page Title:', 'avia_framework' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'page_title' ); ?>" name="<?php echo $this->get_field_name( 'page_title' ); ?>" type="text" value="<?php echo $page_title; ?>" placeholder="<?php _e( 'Enter some info to the page', 'avia_framework' ); ?>" /></label>
			</p>

			<div class="avia_fb_likebox_upload avia-fb-logo av-widgets-upload">
				<?php echo $html->render_single_element( $logo_element );?>
			</div>

			<p class="av-content">
				<label for="<?php echo $this->get_field_id( 'content' ); ?>"><?php _e( 'Static like count:', 'avia_framework' ); ?>
					<input class="widefat" id="<?php echo $this->get_field_id( 'content' ); ?>" name="<?php echo $this->get_field_name( 'content' ); ?>" rows="5" placeholder="<?php _e( '2k+ likes', 'avia_framework' ); ?>" value='<?php echo $content; ?>' />
				</label>
			</p>

			<p class="av-add_info">
				<label for="<?php echo $this->get_field_id( 'add_info' ); ?>"><?php _e( 'Additional Information:', 'avia_framework' ); ?>
					<input class="widefat" id="<?php echo $this->get_field_id( 'add_info' ); ?>" name="<?php echo $this->get_field_name( 'add_info' ); ?>" rows="5" placeholder="<?php _e( 'Info displayed above the fake user profiles', 'avia_framework' ); ?>" value='<?php echo $add_info; ?>' />
				</label>
			</p>
		</div>
	<?php

		}
	}
}
