<?php
/**
 * Gallery
 *
 * Shortcode that allows to create a gallery based on images selected from the media library
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if ( ! class_exists( 'avia_sc_gallery', false ) )
{
	class avia_sc_gallery extends aviaShortcodeTemplate
	{
		use \aviaBuilder\traits\scSlideshowUIControls;


		/**
		 *
		 * @var int
		 */
		static protected $gallery = 0;

		/**
		 * Array of WP_Post attachments
		 *
		 * @since 4.8.4
		 * @var array
		 */
		protected $attachments;

		/**
		 * @since 4.8.4
		 * @param AviaBuilder $builder
		 */
		public function __construct(AviaBuilder $builder)
		{
			parent::__construct($builder);

			$this->attachments = array();
		}

		/**
		 * @since 4.8.4
		 */
		public function __destruct()
		{
			parent::__destruct();

			unset( $this->attachments );
		}

		/**
		 * Create the config array for the shortcode button
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['self_closing']	= 'yes';
			$this->config['base_element']	= 'yes';

			$this->config['name']			= __( 'Gallery', 'avia_framework' );
			$this->config['tab']			= __( 'Media Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-gallery.png';
			$this->config['order']			= 6;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode'] 		= 'av_gallery';
			$this->config['modal_data']     = array( 'modal_class' => 'mediumscreen' );
			$this->config['tooltip']        = __( 'Creates a custom gallery', 'avia_framework' );
			$this->config['preview'] 		= 1;
			$this->config['disabling_allowed'] = 'manually'; //only allowed manually since the default [gallery shortcode] is also affected
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
			$this->config['alb_desc_id']	= 'alb_description';
		}

		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_js = avia_minify_extension( 'js' );
			$min_css = avia_minify_extension( 'css' );

			//load css
			wp_enqueue_style( 'avia-module-slideshow', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/slideshow/slideshow{$min_css}.css", array( 'avia-layout' ), $ver );
			wp_enqueue_style( 'avia-module-gallery', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/gallery/gallery{$min_css}.css", array( 'avia-layout' ), $ver );

			wp_enqueue_script( 'avia-module-gallery', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/gallery/gallery{$min_js}.js", array( 'avia-shortcodes' ), $ver, true );
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
								'template_id'	=> $this->popup_key( 'content_entries' )
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
							'template_id'	=> 'toggle_container',
							'templates_include'	=> array(
													$this->popup_key( 'styling_gallery' ),
													$this->popup_key( 'styling_controls' ),
													$this->popup_key( 'styling_nav_colors' )
												),
							'nodescription' => true
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
								'template_id'	=> $this->popup_key( 'advanced_link' )
							),

						array(
								'type'			=> 'template',
								'template_id'	=> $this->popup_key( 'advanced_animation' )
							),

						array(
								'type'			=> 'template',
								'template_id'	=> 'lazy_loading_toggle',
								'id'			=> 'html_lazy_loading',
								'lockable'		=> true
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
							'name'		=> __( 'Edit Gallery', 'avia_framework' ),
							'desc'		=> __( 'Create a new Gallery by selecting existing or uploading new images', 'avia_framework' ),
							'id'		=> 'ids',
							'type'		=> 'gallery',
							'title'		=> __( 'Add/Edit Gallery', 'avia_framework' ),
							'button'	=> __( 'Insert Images', 'avia_framework' ),
							'std'		=> '',
							'modal_class' => 'av-show-image-custom-link',
							'lockable'	=> true
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'custom_field_image',
							'id'			=> 'ids_dynamic',
							'lockable'		=> true
						)

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_entries' ), $c );

			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name'		=> __( 'Gallery Style', 'avia_framework' ),
							'desc'		=> __( 'Choose the layout of your Gallery', 'avia_framework' ),
							'id'		=> 'style',
							'type'		=> 'select',
							'std'		=> 'thumbnails',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Small Thumbnails', 'avia_framework' )					=> 'thumbnails',
												__( 'Big image with thumbnails below', 'avia_framework' )	=> 'big_thumb',
												__( 'Big image only, other images can be accessed via lightbox', 'avia_framework' ) => 'big_thumb lightbox_gallery',
											)
						),

						array(
							'name'		=> __( 'Gallery Big Preview Image Size', 'avia_framework' ),
							'desc'		=> __( 'Choose image size for the Big Preview Image', 'avia_framework' ),
							'id'		=> 'preview_size',
							'type'		=> 'select',
							'std'		=> 'portfolio',
							'lockable'	=> true,
							'required'	=> array( 'style', 'contains', 'big_thumb' ),
							'subtype'	=> AviaHelper::get_registered_image_sizes( array( 'logo' ) )
						),

						array(
							'name'		=> __( 'Force same size for all big preview images?', 'avia_framework' ),
							'desc'		=> __( 'Depending on the size you selected above, preview images might differ in size. Should the theme force them to display at exactly the same size?', 'avia_framework' ),
							'id'		=> 'crop_big_preview_thumbnail',
							'type'		=> 'select',
							'std'		=> 'avia-gallery-big-crop-thumb',
							'lockable'	=> true,
							'required'	=> array( 'style', 'equals', 'big_thumb' ),
							'subtype'	=> array(
												__( 'Yes, force same size on all Big Preview images, even if they use a different aspect ratio', 'avia_framework' ) => 'avia-gallery-big-crop-thumb',
												__( 'No, do not force the same size', 'avia_framework' ) => 'avia-gallery-big-no-crop-thumb'
											)
						),

						array(
							'name'		=> __( 'Gallery Preview Image Size', 'avia_framework' ),
							'desc'		=> __( 'Choose image size for the small preview thumbnails', 'avia_framework' ),
							'id'		=> 'thumb_size',
							'type'		=> 'select',
							'std'		=> 'portfolio',
							'lockable'	=> true,
							'required' 	=> array( 'style', 'not', 'big_thumb lightbox_gallery' ),
							'subtype'	=>  AviaHelper::get_registered_image_sizes( array( 'logo' ) )
						),

						array(
							'name'		=> __('Thumbnail Columns', 'avia_framework' ),
							'desc'		=> __('Choose the column count of your Gallery', 'avia_framework' ),
							'id'		=> 'columns',
							'type'		=> 'select',
							'std'		=> '5',
							'lockable'	=> true,
							'required'	=> array( 'style', 'not', 'big_thumb lightbox_gallery' ),
							'subtype'	=> AviaHtmlHelper::number_array( 1, 12, 1 )
						),

						array(
							'name'		=> __('Hover Effect', 'avia_framework' ),
							'desc'		=> __('Select to change the big preview image to thumbnail when user hovers over small thumbnails below.', 'avia_framework' ),
							'id'		=> 'thumbs_hover',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'style', 'equals', 'big_thumb' ),
							'subtype'	=> array(
												__( 'Change big preview image on hover (= default behaviour)', 'avia_framework' )	=> '',
												__( 'Do not change big preview image', 'avia_framework' )							=> 'no-hover-effect'
											)
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Gallery', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_gallery' ), $template );


			$c = array(

						array(
							'type'			=> 'template',
							'template_id'	=> 'slideshow_controls',
							'name'			=> __( 'Gallery Navigation Arrows Styling', 'avia_framework' ),
							'desc'			=> __( 'Select styling for the navigation arrows. These can be used to scroll through the small thumbnails below.', 'avia_framework' ),
							'include'		=> array( 'arrows' ),
							'std_navs'		=> 'av-navigate-arrows',
							'std_style'		=> 'av-control-hidden',
							'required'		=> array( 'style', 'contains', 'big_thumb' ),
							'lockable'		=> true
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Navigation Controls', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_controls' ), $template );


			$c = array(

						array(
							'type'			=> 'template',
							'template_id'	=> 'slideshow_navigation_colors',
							'include'		=> array( 'arrows' ),
							'lockable'		=> true
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Navigation Control Colors', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_nav_colors' ), $template );


			/**
			 * Advanced Tab
			 * ============
			 */

			$c = array(
						array(
							'name'		=> __( 'Image Link', 'avia_framework' ),
							'desc'		=> __( 'By default images link to a larger image version in a lightbox. You can change this here. A custom link can be added when editing the images in the gallery.', 'avia_framework' ),
							'id'		=> 'imagelink',
							'type'		=> 'select',
							'std'		=> 'lightbox',
							'lockable'	=> true,
							'required'	=> array( 'style', 'not', 'big_thumb lightbox_gallery' ),
							'subtype'	=> array(
												__( 'Lightbox linking active', 'avia_framework' )						=> 'lightbox',
												__( 'Use custom link (fallback is image link)', 'avia_framework' )		=> 'custom_link',
												__( 'Open the images in the browser window', 'avia_framework' )			=> 'aviaopeninbrowser noLightbox',
												__( 'Open the images in a new browser window/tab', 'avia_framework' )	=> 'aviaopeninbrowser aviablank noLightbox',
												__( 'No, don\'t add a link to the images at all', 'avia_framework' )	=> 'avianolink noLightbox'
											)
						),

						array(
							'name'		=> __( 'Custom link destination', 'avia_framework' ),
							'desc'		=> __( 'Select where an existing custom link should be opened.', 'avia_framework' ),
							'id'		=> 'link_dest',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'imagelink', 'equals', 'custom_link' ),
							'subtype'	=> array(
												__( 'Open in same window', 'avia_framework' )		=> '',
												__( 'Open in a new window', 'avia_framework' )		=> '_blank'
											)
						),

						array(
							'name'		=> __( 'Lightbox image description text', 'avia_framework' ),
							'desc'		=> __( 'Select which text defined in the media gallery is displayed below the lightbox image.', 'avia_framework' ),
							'id'		=> 'lightbox_text',
							'type'		=> 'select',
							'std'		=> 'caption',
							'lockable'	=> true,
							'required'	=> array( 'imagelink', 'equals', 'lightbox' ),
							'subtype'	=> array(
												__( 'No text', 'avia_framework' )										=> 'no_text',
												__( 'Image title', 'avia_framework' )									=> '',
												__ ('Image description (or image title if empty)', 'avia_framework' )	=> 'description',
												__( 'Image caption (or image title if empty)', 'avia_framework' )		=> 'caption'
											)
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Link Settings', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_link' ), $template );

			$c = array(
						array(
							'name'		=> __( 'Thumbnail fade in effect', 'avia_framework' ),
							'desc'		=> __( 'You can set when the gallery thumbnail animation starts', 'avia_framework' ),
							'id'		=> 'lazyload',
							'type'		=> 'select',
							'std'		=> 'avia_lazyload',
							'lockable'	=> true,
							'required'	=> array( 'style', 'not', 'big_thumb lightbox_gallery' ),
							'subtype'	=> array(
												__( 'Disable all animations', 'avia_framework' )								=> 'animations_off',
												__( 'Show the animation when user scrolls to the gallery', 'avia_framework' )	=> 'avia_lazyload',
												__( 'Activate animation on page load (might be preferable on large galleries)', 'avia_framework' ) => 'deactivate_avia_lazyload'
											)
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Animation', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_animation' ), $template );

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
			$params = parent::editor_element( $params );
			$params['content'] = null; //remove to allow content elements

			return $params;
		}

		/**
		 * Create custom stylings
		 *
		 * @since 4.8.4
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles( array $args )
		{
			$result = parent::get_element_styles( $args );

			extract( $result );

			//	make sure to have a value - fallback situation only
			if( empty( $atts['columns'] ) && isset( $atts['ids'] ) )
			{
				$atts['columns'] = count( explode( ',', $atts['ids'] ) );
				if( $atts['columns'] == 0 )
				{
					$atts['columns'] = 5;
				}
				else if( $atts['columns'] > 10 )
				{
					$atts['columns'] = 10;
				}
			}


			$default = array(
						'order'      	=> 'ASC',
						'thumb_size' 	=> 'thumbnail',
						'size' 			=> '',
						'preview_size'	=> 'portfolio',
						'ids'    	 	=> '',
						'imagelink'     => 'lightbox',
						'link_dest'		=> '',
						'lightbox_text'	=> 'caption',
						'style'			=> 'thumbnails',
						'columns'		=> 5,
						'lazyload'      => 'avia_lazyload',
						'html_lazy_loading'				=> 'disabled',
						'crop_big_preview_thumbnail'	=> 'avia-gallery-big-crop-thumb',

						'ajax_request'	=> false
					);

			$default = $this->sync_sc_defaults_array( $default );


			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			Avia_Dynamic_Content()->read( $atts, $this, $shortcodename, $content );

			$atts['ids'] = Avia_Dynamic_Content()->check_id_list( $atts['ids_dynamic'], $atts['ids'] );



			$this->attachments = get_posts( array(
									'include'		=> $atts['ids'],
									'post_status'	=> 'inherit',
									'post_type'		=> 'attachment',
									'post_mime_type' => 'image',
									'order'			=> $atts['order'],
									'orderby'		=> 'post__in'
								)
						);

			if( empty( $this->attachments ) || ! is_array( $this->attachments ) )
			{
				return $result;
			}

			//compatibility mode for default wp galleries - used e.g. by post type gallery posts
			if( ! empty( $atts['size'] ) )
			{
				$atts['thumb_size'] = $atts['size'];
			}

			/**
			 * Backwards comp. for old elements
			 *
			 * @since 5.5
			 */
			if( empty( $atts['control_layout'] ) )
			{
				$atts['control_layout'] = 'av-control-hidden';
			}

			/**
			 * @since 5.3
			 * @param string $class_animation
			 * @param array $atts
			 * @param aviaShortcodeTemplate $this
			 * @param string $shortcodename
			 * @return string
			 */
			$class_animation = apply_filters( 'avf_alb_element_animation', 'avia_animate_when_visible', $atts, $this, $shortcodename );

			$classes = array(
						'avia-gallery',
						$element_id,
						$class_animation
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $meta, 'el_class' );
			$element_styling->add_responsive_classes( 'container', 'hide_element', $atts );

			if( 'big_thumb lightbox_gallery' == $atts['style']  )
			{
				$atts['imagelink'] = 'lightbox';
				$element_styling->add_classes( 'big-thumb-link', 'lightbox' );
				$element_styling->add_classes( 'thumb-link', 'lightbox' );
				$element_styling->add_classes( 'container', array( 'av-hide-gallery-thumbs', 'deactivate_avia_lazyload' ) );

				$atts['lazyload'] = 'deactivate_avia_lazyload';
			}
			else
			{
				$element_styling->add_classes( 'big-thumb-link', $atts['imagelink'] );
				$element_styling->add_classes( 'thumb-link', $atts['imagelink'] );
				$element_styling->add_classes( 'container', $atts['lazyload'] );

				if( 'custom_link' == $atts['imagelink'] )
				{
					$element_styling->add_classes( 'big-thumb-link', array( 'aviaopeninbrowser', 'noLightbox' ) );
					$element_styling->add_classes( 'thumb-link', 'lightbox' );

					if( '_blank' == $atts['link_dest']  )
					{
						$element_styling->add_classes( 'big-thumb-link', 'aviablank' );
						$element_styling->add_classes( 'thumb-link', 'aviablank' );
					}
				}
			}

			if( false !== strpos( $atts['style'], 'big_thumb' ) )
			{
				$classes = array(
								'av-slideshow-ui',
								'av-loop-manual-endless',
								$atts['control_layout'],
								$atts['slider_navigation'],
								$atts['nav_visibility_desktop'],
								! empty( $atts['thumbs_hover'] ) ? $atts['thumbs_hover'] : 'hover-effect',
								$atts['control_layout'] == 'av-control-hidden' ?  $atts['control_layout'] : 'av-control-visible'
							);

				$element_styling->add_classes( 'container', $classes );

				if( 'av-control-default' == $atts['control_layout'] )
				{
					$element_styling->add_styles( 'slide-arrows', array( 'color' => $atts['nav_arrow_color'] ), 'skip_empty' );
					$element_styling->add_styles( 'slide-arrows', array( 'background-color' => $atts['nav_arrow_bg_color'] ), 'skip_empty' );

					$element_styling->add_styles( 'slide-arrows-svg', array(
																'stroke'	=> $atts['nav_arrow_color'],
																'fill'		=> $atts['nav_arrow_color']
														), 'skip_empty' );
				}
			}

			// animation
			if( $atts['lazyload'] != 'animations_off' )
			{
				$element_styling->add_classes( 'container', 'avia-gallery-animate' );
			}

			$thumb_width = round( 100 / $atts['columns'], 4 );
			$element_styling->add_styles( 'thumb-link', array( 'width' => $thumb_width . '%' ) );

			$selectors = array(
						'container'			=> ".avia-gallery.{$element_id}",
						'thumb-link'		=> "#top .avia-gallery.{$element_id} .avia-gallery-thumb a",
						'slide-arrows'		=> "#top .avia-gallery.{$element_id} .avia-slideshow-controls a",
						'slide-arrows-svg'	=> "#top .avia-gallery.{$element_id} .avia-slideshow-controls a.avia-svg-icon svg:first-child"
					);

			$element_styling->add_selectors( $selectors );


			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['meta'] = $meta;

			return $result;
		}

		/**
		 * Frontend Shortcode Handler
		 *
		 * @param array $atts array of attributes
		 * @param string $content text within enclosing form of shortcode element
		 * @param string $shortcodename the shortcode found, when == callback name
		 * @return string $output returns the modified html string
		 */
		public function shortcode_handler( $atts, $content = '', $shortcodename = '', $meta = '' )
		{
			$result = $this->get_element_styles( compact( array( 'atts', 'content', 'shortcodename', 'meta' ) ) );

			extract( $result );
			extract( $atts );

			if( empty( $this->attachments ) || ! is_array( $this->attachments ) )
			{
				return '';
			}

			if( 'disabled' == $atts['img_scrset'] )
			{
				Av_Responsive_Images()->force_disable( 'disabled' );
			}

			//	must be done here to avoid duplicate count on first page load building the CSS file
			self::$gallery++;

			$rel = '';
			if( 'big_thumb lightbox_gallery' != $style && 'custom_link' == $imagelink && '_blank' == $link_dest )
			{
				$rel .= 'rel="noopener noreferrer" target="_blank"';
			}

			$big_thumb = '';
			$thumbs = '';
			$counter = 0;

			/**
			 * @since 4.8.2
			 * @param string $image_size
			 * @param string $shortcode
			 * @param array $atts
			 * @param string $content
			 * @return string
			 */
			$lightbox_img_size = apply_filters( 'avf_alb_lightbox_image_size', 'large', $this->config['shortcode'], $atts, $content );

			foreach( $this->attachments as $attachment )
			{
				$lightbox_img_src = Av_Responsive_Images()->responsive_image_src( $attachment->ID, $lightbox_img_size );

				if( false !== strpos( $imagelink, 'custom_link') )
				{
					$c_link = $custom_url = get_post_meta( $attachment->ID, 'av-custom-link', true );
					if( ! empty( $c_link ) )
					{
						$lightbox_img_src[0] = $c_link;
					}
				}

				/**
				 * Allows to add a custom link class.
				 * To change the default lightbox image size use above filter avf_alb_lightbox_image_size (added 4.8.2).
				 *
				 * @since ????
				 * @param array $link
				 * @param WP_Post $attachment
				 * @param array $atts
				 * @param array $meta
				 * @return array
				 */
				$lightbox_img_src = apply_filters( 'avf_avia_builder_gallery_image_link', $lightbox_img_src, $attachment, $atts, $meta );

				$custom_link_class = ! empty( $lightbox_img_src['custom_link_class'] ) ? $lightbox_img_src['custom_link_class'] : '';
				$class = $counter++ % $columns ? "class='$imagelink $custom_link_class'" : "class='first_thumb $imagelink $custom_link_class'";

				$img = wp_get_attachment_image_src( $attachment->ID, $thumb_size );
				$prev = wp_get_attachment_image_src( $attachment->ID, $preview_size );

				$caption = trim( $attachment->post_excerpt ) ? wptexturize( $attachment->post_excerpt ) : '';
				$tooltip = $caption ? "data-avia-tooltip='{$caption}'" : '';

				$alt = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );
				$alt = ! empty( $alt ) ? esc_attr( $alt ) : '';

				$title = trim( $attachment->post_title ) ? esc_attr( $attachment->post_title ) : '';
				$description = trim( $attachment->post_content ) ? esc_attr( $attachment->post_content ) : '';

				$lightbox_title = $title;
				switch( $lightbox_text )
				{
					case 'caption':
						$lightbox_title = ( '' != $caption ) ? $caption : $title;
						break;
					case 'description':
						$lightbox_title = ( '' != $description ) ? $description : $title;
						break;
					case 'no_text':
						$lightbox_title = '';
				}

				$markup_url = avia_markup_helper( array( 'context' => 'image_url', 'echo' => false, 'id' => $attachment->ID, 'custom_markup' => $meta['custom_markup'] ) );

				if( strpos( $style, 'big_thumb' ) !== false && 1 == $counter )
				{
					$img_tag = "<img width='{$prev[1]}' height='{$prev[2]}' src='{$prev[0]}' title='{$title}' alt='{$alt}' />";
					$img_tag = Av_Responsive_Images()->prepare_single_image( $img_tag, $attachment->ID, $html_lazy_loading );
					$lightbox_attr = Av_Responsive_Images()->html_attr_image_src( $lightbox_img_src, false );

					$big_thumb .= "<a class='avia-gallery-big fakeLightbox {$imagelink} {$crop_big_preview_thumbnail} {$custom_link_class}' {$lightbox_attr}  data-onclick='1' title='{$lightbox_title}' {$rel}>";
					$big_thumb .=		"<span class='avia-gallery-big-inner' {$markup_url}>";
					$big_thumb .=			$img_tag;

					if( $caption )
					{
						$big_thumb .=		"<span class='avia-gallery-caption'>{$caption}</span>";
					}

					$big_thumb .=		'</span>';
					$big_thumb .= '</a>';

					if( $control_layout != 'av-control-hidden' )
					{
						$big_thumb .= $this->slide_navigation_arrows( $atts );
					}
				}

				$img_tag = "<img {$tooltip} src='{$img[0]}' width='{$img[1]}' height='{$img[2]}'  title='{$title}' alt='{$alt}' />";
				$img_tag = Av_Responsive_Images()->prepare_single_image( $img_tag, $attachment->ID, $html_lazy_loading );
				$lightbox_attr = Av_Responsive_Images()->html_attr_image_src( $lightbox_img_src, false );

				// @since 4.8.8.2 support for responsive images:  https://kriesi.at/support/topic/missing-scrset-in-alb-gallery/
				$prev_img_tag = "<img width='{$prev[1]}' height='{$prev[2]}' src='{$prev[0]}' title='{$title}' alt='{$alt}' />";
				$prev_img_tag = Av_Responsive_Images()->prepare_single_image( $prev_img_tag, $attachment->ID, 'enabled' );

				$thumbs .= "<a {$lightbox_attr} data-rel='gallery-" . self::$gallery . "' data-prev-img='{$prev[0]}' {$class} data-onclick='{$counter}' title='{$lightbox_title}' {$markup_url} {$rel}>";
				$thumbs .=		$img_tag;
				$thumbs .=		"<div class='big-prev-fake'>{$prev_img_tag}</div>";
				$thumbs .= '</a>';
			}

			$markup_gallery = avia_markup_helper( array( 'context' => 'image', 'echo' => false, 'custom_markup' => $meta['custom_markup'] ) );

			/**
			 * https://kriesi.at/support/topic/contenturl-or-url-missing-from-rich-snippets/
			 *
			 * @since 4.8.9.1
			 */
			$post_link = trim( get_the_permalink( get_the_ID() ) );
			$markup_meta = '<meta itemprop="contentURL" content="' . esc_attr( $post_link ) . '">';

			$style_tag = $element_styling->get_style_tag( $element_id );
			$container_class = $element_styling->get_class_string( 'container' );

			$output  = '';
			$output .= $style_tag;
			$output .= "<div {$meta['custom_el_id']} class='{$container_class} avia-gallery-" . self::$gallery . "' {$markup_gallery}>";
			$output .=		$markup_meta;
			$output .=		$big_thumb;
			$output .=		"<div class='avia-gallery-thumb'>{$thumbs}</div>";
			$output .= '</div>';

			$html = Av_Responsive_Images()->make_content_images_responsive( $output );

			Av_Responsive_Images()->force_disable( 'reset' );

			return $html;
		}

		/**
		 * Create arrows to scroll image slides
		 *
		 * @since 5.5			reroute to aviaFrontTemplates
		 * @param array $atts
		 * @return string
		 */
		protected function slide_navigation_arrows( array $atts )
		{
			$args = array(
						'class_prev'	=> 'av-gallery-prev',
						'class_next'	=> 'av-gallery-next',
						'context'		=> get_class( $this ),
						'params'		=> $atts,
						'svg_icon'		=> true
					);

			return aviaFrontTemplates::slide_navigation_arrows( $args );
		}
	}
}

