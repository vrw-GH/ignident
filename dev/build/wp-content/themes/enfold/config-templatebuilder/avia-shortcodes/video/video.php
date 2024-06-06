<?php
/**
 * Video
 *
 * Shortcode which display a video
 *
 * @since ????
 * @since 4.8.9 - complete refactored code
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_sc_video', false ) )
{
	class avia_sc_video extends aviaShortcodeTemplate
	{
		/**
		 * Create the config array for the shortcode button
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']			= '1.0';
			$this->config['self_closing']		= 'yes';
			$this->config['base_element']		= 'yes';

			$this->config['name']				= __( 'Video', 'avia_framework' );
			$this->config['tab']				= __( 'Media Elements', 'avia_framework' );
			$this->config['icon']				= AviaBuilder::$path['imagesURL'] . 'sc-video.png';
			$this->config['order']				= 90;
			$this->config['target']				= 'avia-target-insert';
			$this->config['shortcode']			= 'av_video';
		//				$this->config['modal_data']     = array( 'modal_class' => 'mediumscreen' );
			$this->config['tooltip']			= __( 'Display a video', 'avia_framework' );
			$this->config['disabling_allowed']	= false; //only allowed to be disabled by extra options
			$this->config['disabled']			= array(
													'condition'	=> ( avia_get_option( 'disable_mediaelement' ) == 'disable_mediaelement' && avia_get_option( 'disable_video' ) == 'disable_video' ),
													'text'		=> __( 'This element is disabled in your theme options. You can enable it in Enfold &raquo; Performance', 'avia_framework' )
												);
			$this->config['id_name']			= 'id';
			$this->config['id_show']			= 'yes';
		}

		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_js = avia_minify_extension( 'js' );
			$min_css = avia_minify_extension( 'css' );

			//load css
			wp_enqueue_style( 'avia-module-video', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/video/video{$min_css}.css", array( 'avia-layout' ), $ver );

			wp_enqueue_script( 'avia-module-slideshow-video', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/slideshow/slideshow-video{$min_js}.js", array( 'avia-shortcodes' ), $ver, true );
			wp_enqueue_script( 'avia-module-video', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/video/video{$min_js}.js", array( 'avia-shortcodes' ), $ver, true );
		}

		/**
		 * Popup Elements
		 *
		 * If this function is defined in a child class the element automatically gets an edit button, that, when pressed
		 * opens a modal window that allows to edit the element properties
		 *
		 * @return void
		 */
		protected function popup_elements()
		{

			//if the element is disabled
			if( true === $this->config['disabled']['condition'] )
			{
				$this->elements = array(

					array(
								'type'			=> 'template',
								'template_id'	=> 'element_disabled',
								'args'			=> array(
														'desc'	=> $this->config['disabled']['text']
													)
							),
						);

				return;
			}


			$this->elements = array(

				array(
						'type' 	=> 'tab_container',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Content', 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'type'			=> 'template',
							'template_id'	=> 'toggle_container',
							'templates_include'	=> array(
													$this->popup_key( 'content_video' ),
													$this->popup_key( 'content_player' )
												),
							'nodescription' => true
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Styling', 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'type'			=> 'template',
							'template_id'	=> $this->popup_key( 'styling_format' )
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Advanced', 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'type' 	=> 'toggle_container',
							'nodescription' => true
						),

						array(
								'type'			=> 'template',
								'template_id'	=> $this->popup_key( 'advanced_privacy' )
							),

						array(
								'type'			=> 'template',
								'template_id'	=> 'screen_options_toggle',
								'lockable'		=> true
							),

						array(
								'type'			=> 'template',
								'template_id'	=> 'developer_options_toggle',
								'args'			=> array( 'sc' => $this )
							),

					array(
							'type' 	=> 'toggle_container_close',
							'nodescription' => true
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type'			=> 'template',
						'template_id'	=> 'element_template_selection_tab',
						'args'			=> array( 'sc' => $this )
					),

				array(
						'type' 	=> 'tab_container_close',
						'nodescription' => true
					)

				);


		}

		/**
		 * Create and register templates for easier maintainance
		 *
		 * @since 4.6.4
		 */
		protected function register_dynamic_templates()
		{
			/**
			 * Content Tab
			 * ===========
			 */

			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'video',
							'id'			=> 'src',
							'args'			=> array(
													'sc'			=> $this,
													'html_5_urls'	=> current_theme_supports( 'avia_template_builder_custom_html5_video_urls' )
												),
							'lockable'		=> true
						),

						array(
							'name'		=> __( 'Save Video Thumbnail Locally', 'avia_framework' ),
							'desc'		=> __( 'Download the video preview thumbnail and save it in the media library', 'avia_framework' ),
							'title'		=> __( 'Download video thumbnail', 'avia_framework' ),
							'id'		=> 'video_thumbnails',
							'type'		=> 'action_button',
							'container_class' => 'avia-save_video_thumbnails'
						),

						array(
							'name'		=> __( 'Choose A Preview/Fallback Image', 'avia_framework' ),
							'desc'		=> __( 'Either upload a new, or choose an existing image from your media library', 'avia_framework' ) . '<br/><small>' . __( "Video on most mobile devices can't be controlled properly with JavaScript, so you can upload a fallback image which will be displayed instead. This image is also used if lazy loading is active.", 'avia_framework' ) . '</small>',
							'id'		=> 'mobile_image',
							'type'		=> 'image',
							'title'		=> __( 'Choose Image', 'avia_framework' ),
							'button'	=> __( 'Choose Image', 'avia_framework' ),
							'std'		=> '',
							'lockable'	=> true,
							'locked'	=> array( 'mobile_image', 'attachment', 'attachment_size' )
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Select Video', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_video' ), $template );

			$c = array(
						array(
							'name'		=> __( 'Enable Autoplay', 'avia_framework' ),
							'desc'		=> __( 'Check if you want to enable video autoplay when page is loaded. Videos will be muted by default.', 'avia_framework' ),
							'id'		=> 'video_autoplay_enabled',
							'type'		=> 'checkbox',
							'std'		=> '',
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Mute Video Player', 'avia_framework' ),
							'desc'		=> __( 'Check if you want to mute the video.', 'avia_framework' ),
							'id'		=> 'video_mute',
							'type'		=> 'checkbox',
							'std'		=> '',
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Loop Video Player', 'avia_framework' ),
							'desc'		=> __( 'Check if you want to loop the video and play it from the beginning again', 'avia_framework' ),
							'id'		=> 'video_loop',
							'type'		=> 'checkbox',
							'std'		=> '',
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Hide Video Controls', 'avia_framework' ),
							'desc'		=> __( 'Check if you want to hide the controls (works for youtube and self hosted videos)', 'avia_framework' ),
							'id'		=> 'video_controls',
							'type'		=> 'checkbox',
							'std'		=> '',
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Show Fullscreen Button (HTML5 videos)', 'avia_framework' ),
							'desc'		=> __( 'Check if you want to show the fullscreen button for HTML5 videos (setting is ignored for other type of videos)', 'avia_framework' ),
							'id'		=> 'html5_fullscreen',
							'type'		=> 'checkbox',
							'std'		=> '',
							'lockable'	=> true
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Player Settings', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_player' ), $template );


			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name'		=> __( 'Video Format', 'avia_framework' ),
							'desc'		=> __( 'Choose if you want to display a modern 16:9 or classic 4:3 Video, or use a custom ratio', 'avia_framework' ),
							'id'		=> 'format',
							'type'		=> 'select',
							'std'		=> '16:9',
							'lockable'	=> true,
							'subtype'	=> array(
												__( '16:9', 'avia_framework' )			=> '16-9',
												__( '4:3', 'avia_framework' )			=> '4-3',
												__( 'Custom Ratio', 'avia_framework' )	=> 'custom',
											)
						),

						array(
							'name'		=> __( 'Video width', 'avia_framework' ),
							'desc'		=> __( 'Enter a value for the width', 'avia_framework' ),
							'id'		=> 'width',
							'type'		=> 'input',
							'std'		=> '16',
							'lockable'	=> true,
							'required'	=> array( 'format', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Video height', 'avia_framework' ),
							'desc'		=> __( 'Enter a value for the height', 'avia_framework' ),
							'id'		=> 'height',
							'type'		=> 'input',
							'std'		=> '9',
							'lockable'	=> true,
							'required'	=> array( 'format', 'equals', 'custom' )
						)
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_format' ), $c );


			/**
			 * Advanced Tab
			 * ===========
			 */

			$c = array(
						array(
							'name'		=> __( 'Lazy Load Videos', 'avia_framework' ),
							'desc'		=> __( 'Option to only load the preview image. The actual video will only be fetched once the user clicks on the image (Waiting for user interaction speeds up the inital pageload).', 'avia_framework' ),
							'id'		=> 'conditional_play',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Always load videos', 'avia_framework' )							=> '',
												__( 'Wait for user interaction to load the video', 'avia_framework' )	=> 'confirm_all',
												__( 'Show in lightbox - loads after user interaction - preview image recommended', 'avia_framework' )	=> 'lightbox'
											),
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Privacy Settings', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_privacy' ), $template );
		}

		/**
		 * Editor Element - this function defines the visual appearance of an element on the AviaBuilder Canvas
		 * Most common usage is to define some markup in the $params['innerHtml'] which is then inserted into the drag and drop container
		 * Less often used: $params['data'] to add data attributes, $params['class'] to modify the className
		 *
		 * @param array $params			holds the default values for $content and $args.
		 * @return array				usually holds an innerHtml key that holds item specific markup.
		 */
		public function editor_element( $params )
		{
			$default = array();
			$locked = array();
			$attr = $params['args'];
			Avia_Element_Templates()->set_locked_attributes( $attr, $this, $this->config['shortcode'], $default, $locked );

			$template = $this->update_template_lockable( 'src', 'URL: {{src}}', $locked );
			$url = isset( $attr['src'] ) ? $attr['src'] : '';

			$params = parent::editor_element( $params );
			$params['innerHtml'].= "<div class='avia-element-url' data-update_element_template='yes' {$template}> URL: {$url}</div>";

			$params['content'] = null;
			$params['class'] = 'avia-video-element';

			return $params;
		}

		/**
		 * Create custom stylings
		 *
		 * @since 4.8.7
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles( array $args )
		{
			$result = parent::get_element_styles( $args );

			extract( $result );

			$default = array(
						'src'				=> '',
						'src_1'				=> '',
						'src_2'				=> '',
						'mobile_image'		=> '',
						'fallback_link'		=> '',
						'format'			=> '16:9',
						'height'			=> '9',
						'width'				=> '16',
						'conditional_play'	=> '',
						'video_controls'	=> '',
						'video_mute'		=> '',
						'video_loop'		=> '',
						'html5_fullscreen'	=> '',
						'video_autoplay_enabled'	=> '',
						'attachment'		=> '',
						'attachment_size'	=> '',

						'fallback_img'		=> ''				//	save info to avoid double query
					);

			$default = $this->sync_sc_defaults_array( $default );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			/**
			 * Bugfix: value is stored with leading , - https://github.com/KriesiMedia/wp-themes/issues/4104
			 * Caused because the video buttons also have hidden fields with same name for src, attachment, attachment_size and
			 * these are stored in an array in js on modal close -> ,,,x.
			 *
			 * Hackish fix is to take the last entry.
			 *
			 * Fixed with 5.6 by removing the double attributes in modal popup. It needs to upload the image again !!!
			 *
			 * @since 5.6
			 */
			$tmp_val = explode( ',', $atts['attachment'] );
			if( count( $tmp_val ) )
			{
				$atts['attachment'] = end( $tmp_val );
			}

			$tmp_val = explode( ',', $atts['attachment_size'] );
			if( count( $tmp_val ) )
			{
				$atts['attachment_size'] = end( $tmp_val );
			}


			$classes = array(
						'avia-video',
						$element_id,
						'avia-video-' . $atts['format']
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $meta, 'custom_class' );
			$element_styling->add_responsive_classes( 'container', 'hide_element', $atts );

			if( ! empty( $atts['html5_fullscreen'] ) )
			{
				$element_styling->add_classes( 'container', 'av-html5-fullscreen-btn' );
			}

			$image_class = 'av-no-preview-image';


			//	fallback to mobile_image for backwards comp. prior 5.6 (attachment was empty after save when not selected during editing)
			if( ! empty( $atts['attachment'] ) || ! empty( $atts['mobile_image'] ) )
			{
				if( ! empty( $atts['attachment'] ) )
				{
					$fallback = wp_get_attachment_image_src( $atts['attachment'], $atts['attachment_size'] );
					if( is_array( $fallback ) )
					{
						$atts['fallback_img'] = $fallback[0];
					}
				}
				else
				{
					$atts['fallback_img'] = $atts['mobile_image'];
				}

				if( ! empty( $atts['fallback_img'] ) )
				{
					$image_class = 'av-preview-image';
					$element_styling->add_styles( 'container', array( 'background-image' => $atts['fallback_img'] ) );
				}
			}

			$element_styling->add_classes( 'container', $image_class );

			if( 'custom' == $atts['format'] )
			{
				$height = is_numeric( $atts['height'] ) ? abs( intval( $atts['height'] ) ) : 9;
				$width = is_numeric( $atts['width'] ) ? abs( intval( $atts['width'] ) ) : 16;

				//	fallback to 16 : 9
				if( 0 == $height || 0 == $width )
				{
					$height = 9;
					$width = 16;
				}

				$ratio = round( ( 100 / $width ) * $height, 0 );

				$element_styling->add_styles( 'container', array( 'padding-bottom' => $ratio . '%' ) );
			}


			$selectors = array(
						'container'	=> ".avia-video.{$element_id}"
					);

			$element_styling->add_selectors( $selectors );


			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['element_styling'] = $element_styling;

			return $result;
		}

		/**
		 * Frontend Shortcode Handler
		 *
		 * @param array $atts array of attributes
		 * @param string $content text within enclosing form of shortcode element
		 * @param string $shortcodename the shortcode found, when == callback name
		 * @param array $meta
		 * @return string $output returns the modified html string
		 */
		public function shortcode_handler( $atts, $content = '', $shortcodename = '', $meta = '' )
		{
			$result = $this->get_element_styles( compact( array( 'atts', 'content', 'shortcodename', 'meta' ) ) );

			extract( $result );
			extract( $atts );

			$output_html = '';
			$video = '';
			/**
			 * Autoplay videos must be muted
			 */
			if( ! empty( $video_autoplay_enabled ) )
			{
				$video_mute = 1;
			}

			if( 'lightbox_active' != avia_get_option( 'lightbox_active', '' ) && 'lightbox' == $conditional_play  )
			{
				/**
				 * Activate a custom lightbox to show video.
				 * In frontend hook into trigger 'avia-open-video-in-lightbox' (<a> tag containing link to video) to load video in your lightbox
				 *
				 * @since 4.6.3
				 * @param array $atts array of attributes
				 * @param string $content text within enclosing form of shortcode element
				 * @param string $shortcodename the shortcode found, when == callback name
				 * @param array $meta
				 * @return boolean
				 */
				if( false === apply_filters( 'avf_show_video_in_custom_lightbox', false, $atts, $content, $shortcodename, $meta ) )
				{
					$conditional_play = 'confirm_all';
				}
			}

			switch( $conditional_play )
			{
				case 'lightbox':
				case 'confirm_all':
					$element_styling->add_classes( 'container', 'avia-video-' . $conditional_play );
					break;
				default:
					$element_styling->add_classes( 'container', 'avia-video-load-always' );
			}

			$video_html_raw = '';
			$video_attributes = array(
									'autoplay'	=> empty( $video_autoplay_enabled ) ? 0 : 1,
									'loop'		=> empty( $video_loop ) ? 0 : 1,
									'preload'	=> '',
									'muted'		=> empty( $video_mute ) ? 0 : 1,
									'controls'	=> empty( $video_controls ) ? 1 : 0
								);


			$html5_sources = $this->get_html5_sources( array( $src, $src_1, $src_2 ) );


			if( false !== $html5_sources && $conditional_play != 'lightbox' )
			{
				$html5_files = isset( $html5_sources['files'] ) ? $html5_sources['files'] : array();
				$html5_types = isset( $html5_sources['types'] ) ? $html5_sources['types'] : array();

				$video_html_raw = avia_html5_video_embed( $html5_files, $fallback_img, $html5_types, $video_attributes );
				$output_html = $video_html_raw;

				$element_styling->add_classes( 'container', 'avia-video-html5' );
			}
			else if( $conditional_play != 'lightbox' )
			{

				global $wp_embed;

				$video_html_raw = $wp_embed->run_shortcode( '[embed]' . trim( $src ) . '[/embed]' );
				$output_html = $video_html_raw;

				if( ! empty( $conditional_play ) )
				{
					//append autoplay so the user does not need to click 2 times
					$video_attributes['autoplay'] = 1;
				}
				else
				{
					$element_styling->add_classes( 'container', 'av-lazyload-immediate' );
				}

				/**
				 * Add selected video player params to url, does not remove any manually set parameters
				 */
				$match = array();
				preg_match( '!src="(.*?)"!', $output_html, $match );
				if( isset( $match[1] ) && ( ( false !== strpos( $match[1], 'www.youtube.com/' ) ) || ( false !== strpos( $match[1], 'player.vimeo.com/' ) ) ) )
				{
					$params = array();
					$youtube = false !== strpos( $match[1], 'www.youtube.com/' ) ? true : false;

					$params[] = 'autoplay=' . $video_attributes['autoplay'];
					$params[] = 'loop=' . $video_attributes['loop'];
					$params[] = 'controls=' . $video_attributes['controls'];
					$params[] = $youtube ? 'mute=' . $video_attributes['muted'] : 'muted=' . $video_attributes['muted'];

					if( $youtube && $video_attributes['loop'] && false !== strpos( $src, 'v=' ) )
					{
						//	https://developers.google.com/youtube/player_parameters#loop
						$list = explode( 'v=', $src );
						if( isset( $list[1] ) && ! empty( $list[1] ) )
						{
							$params[] = 'playlist=' .  $list[1];
						}
					}

					if( ! empty( $params ) )
					{
						$params = implode( '&', $params );

						if( strpos( $match[1], '?' ) === false )
						{
							$output_html = str_replace( $match[1], $match[1] . '?' . $params, $output_html );
						}
						else
						{
							$output_html = str_replace( $match[1], $match[1] . '&' . $params, $output_html );
						}
					}
				}

				$output_html =	"<script type='text/html' class='av-video-tmpl'>{$output_html}</script>";
				$output_html .=	"<div class='av-click-to-play-overlay'>";
				$output_html .=		'<div class="avia_playpause_icon">';
				$output_html .=		'</div>';
				$output_html .=	'</div>';

				$element_styling->add_classes( 'container', 'av-lazyload-video-embed' );
			}
			else
			{
				$element_styling->add_classes( 'container', array( 'av-lazyload-video-embed' ) );

				$overlay  =	'<div class="av-click-to-play-overlay play-lightbox">';
				$overlay .=		'<div class="avia_playpause_icon">';
				$overlay .=		'</div>';
				$overlay .=	'</div>';

				if( ( false !== stripos( $src, 'youtube.com/watch' ) ) || ( false !== stripos( $src, 'vimeo.com/' ) ) )
				{
					$element_styling->add_classes( 'container', 'avia-video-external-service' );

					$src .= ( strpos( $src, '?' ) === false ) ? '?autoplay=1' : '&autoplay=1';
					$output_html = "<a href='{$src}' class='mfp-iframe lightbox-link'></a>";
				}
				else if( ! empty( $src ) )
				{
					$element_styling->add_classes( 'container', 'avia-video-standard-html' );

					$output_html = "<a href='{$src}' rel='lightbox' class='mfp-iframe lightbox-link'></a>";
				}

				if( ! empty( $output_html ) )
				{
					$output_html = "<script type='text/html' class='av-video-tmpl'>{$output_html}</script>";
					$output_html .= $overlay;
				}
			}

			if( ! empty( $output_html ) )
			{
				$markup_video = avia_markup_helper( array( 'context' => 'video', 'echo' => false, 'custom_markup' => $meta['custom_markup'] ) );

				$style_tag = $element_styling->get_style_tag( $element_id );
				$container_class = $element_styling->get_class_string( 'container' );

				$output = '';
				$output .= $style_tag;
				$output .= "<div {$meta['custom_el_id']} class='{$container_class}' {$markup_video} data-original_url='{$src}'>";
				$output .=		$output_html;
				$output .= '</div>';
			}

			/**
			 * Allow plugins to change output in case they want to handle it by themself.
			 * They must return the complete HTML structure.
			 *
			 * @since 4.5.7.2
			 * @since 4.8.7							added $element_styling
			 * @param string $output
			 * @param array $atts
			 * @param string $content
			 * @param string $shortcodename
			 * @param array|string $meta
			 * @param string $video_html_raw
			 * @param aviaElementStyling $element_styling
			 * @return string
			 */
			$output = apply_filters( 'avf_sc_video_output', $output, $atts, $content, $shortcodename, $meta, $video_html_raw, $element_styling );

			return $output;
		}

		/**
		 * Check for valid HTML5 videos and return split info to build html.
		 * Only given HTML 5 video sources are returned.
		 * If no one found return false
		 *
		 * @since 4.8
		 * @since 4.8.9						modified to return false if no HTML5 found
		 * @param array $source_files
		 * @return array|false
		 */
		protected function get_html5_sources( array $source_files )
		{
			$found = false;

			$sources = array(
					'files'	=> array(),
					'types'	=> array()
			);

			foreach( $source_files as $source_file )
			{
				$ext = substr( $source_file, strrpos( $source_file, '.' ) + 1 );
				if( in_array( $ext, array( 'ogv', 'webm', 'mp4' ) ) )
				{
					$found = true;
					$sources['files'][ $ext ] = $source_file;
					$sources['types'][ $ext ] = "type='video/{$ext}'";
				}
			}

			if( ! $found )
			{
				return false;
			}

			return $sources;
		}
	}
}
