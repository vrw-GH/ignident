<?php
/**
 * Horizontal Gallery
 *
 * Creates a horizontal scrollable gallery
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_sc_gallery_horizontal', false ) )
{
	class avia_sc_gallery_horizontal extends aviaShortcodeTemplate
	{
		use \aviaBuilder\traits\scSlideshowUIControls;


		/**
		 *
		 * @var int
		 */
		static protected $hor_gallery = 0;

		/**
		 * Create the config array for the shortcode button
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['is_fullwidth']	= 'yes';
			$this->config['self_closing']	= 'no';
			$this->config['base_element']	= 'yes';

			$this->config['name']			= __( 'Horizontal Gallery', 'avia_framework' );
			$this->config['tab']			= __( 'Media Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-accordion-slider.png';
			$this->config['order']			= 6;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode'] 		= 'av_horizontal_gallery';
			$this->config['tooltip']        = __( 'Creates a horizontal scrollable gallery', 'avia_framework' );
			$this->config['preview'] 		= false;
			$this->config['drag-level'] 	= 3;
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'always';				//	we use original code - not $meta
			$this->config['alb_desc_id']	= 'alb_description';
		}


		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_js = avia_minify_extension( 'js' );
			$min_css = avia_minify_extension( 'css' );

			//load css
			wp_enqueue_style( 'avia-module-slideshow', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/slideshow/slideshow{$min_css}.css", array( 'avia-layout' ), $ver );
			wp_enqueue_style( 'avia-module-gallery-hor', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/gallery_horizontal/gallery_horizontal{$min_css}.css", array( 'avia-module-slideshow' ), $ver );

				//load js
			wp_enqueue_script( 'avia-module-gallery-hor', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/gallery_horizontal/gallery_horizontal{$min_js}.js", array( 'avia-shortcodes' ), $ver, true );
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
							'template_id'	=> 'toggle_container',
							'templates_include'	=> array(
													$this->popup_key( 'content_entries' ),
													$this->popup_key( 'content_copyright' )
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
							'template_id'	=> 'toggle_container',
							'templates_include'	=> array(
													$this->popup_key( 'styling_gallery' ),
													$this->popup_key( 'styling_controls' ),
													$this->popup_key( 'styling_nav_colors' ),
													$this->popup_key( 'styling_copyright' )
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
								'template_id'	=> $this->popup_key( 'advanced_animation' )
							),

						array(
								'type'			=> 'template',
								'template_id'	=> $this->popup_key( 'advanced_link' )
							),

						array(
								'type'			=> 'template',
								'template_id'	=> 'lazy_loading_toggle',
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
							'name' 	=> __( 'Initial Active Image', 'avia_framework' ),
							'desc' 	=> __( 'Enter the number of the image that should be focused initially. Leave empty for none, if invalid first image will be taken, if image number does not exist, the last image.', 'avia_framework' ),
							'id' 	=> 'initial',
							'type' 	=> 'input',
							'std' 	=> '',
							'lockable'	=> true
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Gallery Images', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_entries' ), $template );


			$c = array(
						array(
								'type'			=> 'template',
								'template_id'	=> 'image_copyright_toggle',
								'lockable'		=> true
						)

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_copyright' ), $c );

			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name' 	=> __( 'Gallery Height', 'avia_framework' ),
							'desc' 	=> __( 'Set the gallery height in relation to the gallery container width', 'avia_framework' ),
							'id' 	=> 'height',
							'type' 	=> 'select',
							'std' 	=> '25',
							'lockable'	=> true,
							'subtype'	=> AviaHtmlHelper::number_array( 0, 50, 5, array(),'%' )
						),

						array(
							'name' 	=> __( 'Image Size', 'avia_framework' ),
							'desc' 	=> __( 'Choose size for each image', 'avia_framework' ),
							'id' 	=> 'size',
							'type' 	=> 'select',
							'std' 	=> 'large',
							'lockable'	=> true,
							'subtype'	=> AviaHelper::get_registered_image_sizes( array( 'logo' ) )
						),

						array(
							'name' 	=> __( 'Gap between images', 'avia_framework' ),
							'desc' 	=> __( 'Select the gap between the images', 'avia_framework' ),
							'id' 	=> 'gap',
							'type' 	=> 'select',
							'std' 	=> 'large',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'No Gap', 'avia_framework' )		=> 'no',
												__( '1 Pixel Gap', 'avia_framework' )	=> '1px',
												__( 'Large Gap', 'avia_framework' )		=> 'large',
											)
						),

						array(
							'name' 	=> __( 'Active Image Style', 'avia_framework' ),
							'desc' 	=> __( 'How do you want to display the active image', 'avia_framework' ),
							'id' 	=> 'active',
							'type' 	=> 'select',
							'std' 	=> 'enlarge',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'No effect', 'avia_framework' )		=> '',
												__( 'Enlarge image', 'avia_framework' )	=> 'enlarge',
											)
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Gallery', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_gallery' ), $template );

			$c = array(

						array(
							'type'			=> 'template',
							'template_id'	=> 'slideshow_controls',
							'name'			=> __( 'Gallery Navigation Arrows Styling', 'avia_framework' ),
							'std_navs'		=> 'av-navigate-arrows',
							'lockable'		=> true
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Navigation Controls', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_controls' ), $template );


			$c = array(

						array(
							'type'			=> 'template',
							'template_id'	=> 'slideshow_navigation_colors',
							'lockable'		=> true
						),

						array(
							'name'		=> __( 'Lightbox Link Arrows Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom color for the lightbox link arrow. Enter no value if you want to use the default color.', 'avia_framework' ),
							'id'		=> 'lightbox_arrow_color',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'control_layout', 'doesnt_contain', 'av-control-minimal' )
						),

						array(
							'name'		=> __( 'Lightbox Link Arrows Background Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom background color for the lightbox link arrow. Enter no value if you want to use the default color.', 'avia_framework' ),
							'id'		=> 'lightbox_arrow_bg_color',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'control_layout', 'doesnt_contain', 'av-control-minimal' )
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


			$c = array(
						array(
								'type'			=> 'template',
								'template_id'	=> 'image_copyright_styling_toggle',
								'lockable'		=> true
						)

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_copyright' ), $c );


			/**
			 * Advanced Tab
			 * ===========
			 */

			$c = array(

						array(
							'type'			=> 'template',
							'template_id'	=> 'slideshow_transition',
							'select_trans'	=> false,
							'lockable'		=> true
						),

						array(
							'type'				=> 'template',
							'template_id'		=> 'slideshow_rotation',
							'name'				=> __( 'Gallery Image Rotation', 'avia_framework' ),
							'stop_id'			=> 'autoplay_stopper',
							'std_hide_arrows'	=> 'yes',
							'lockable'			=> true
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Animation', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_animation' ), $template );

			$c = array(
						array(
							'name' 	=> __( 'Image Link', 'avia_framework' ),
							'desc' 	=> __( 'By default images link to a larger image version in a lightbox. You can change this here. A custom link can be added when editing the images in the gallery.', 'avia_framework' ),
							'id' 	=> 'links',
							'type' 	=> 'select',
							'std' 	=> 'active',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Lightbox linking active', 'avia_framework' )						=> 'active',
												__( 'Use custom link (fallback is no link)', 'avia_framework' )			=> '',
												__( 'No, don\'t add a link to the images at all', 'avia_framework' )	=> 'no_links',
											)
						),

						array(
							'name'		=> __( 'Custom link destination', 'avia_framework' ),
							'desc'		=> __( 'Select where an existing custom link should be opened.', 'avia_framework' ),
							'id'		=> 'link_dest',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'links', 'equals', '' ),
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
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'links', 'equals', 'active' ),
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
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_link' ), $template );

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
			$params['innerHtml'] .=	AviaPopupTemplates()->get_html_template( 'alb_element_fullwidth_stretch' );

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

			$default = array(
						'height'				=> '25',
						'size'					=> 'large',
						'links'					=> 'active',
						'lightbox_text'			=> '',				//	default to title
						'link_dest'				=> '',
						'gap'					=> 'large',
						'ids'					=> '',
						'active'				=> 'enlarge',
						'control_layout'		=> 'av-control-default',
						'nav_arrow_color'		=> '',
						'nav_arrow_bg_color'	=> '',
						'lightbox_arrow_color'	=> '',
						'lightbox_arrow_bg_color'	=> '',
						'initial'				=> '',
						'id'					=> '',
						'lazy_loading'			=> 'enabled',
//						'attachments'			=> ''				// array of attachments to avoid double query in shortcode handler
					);

			$default = $this->sync_sc_defaults_array( $default );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			$element_styling->create_callback_styles( $atts );


			$classes = array(
						'av-horizontal-gallery',
						$element_id,
						"av-horizontal-gallery-{$atts['gap']}-gap",
						"av-horizontal-gallery-{$atts['active']}-effect",
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $meta, 'el_class' );
			$element_styling->add_responsive_classes( 'container', 'hide_element', $atts );

			if( 'enlarge' == $atts['active'] )
			{
				$enlarge_by = 1.3;
				$padding = ( ( $atts['height'] * $enlarge_by ) - $atts['height'] ) / 2;

				$element_styling->add_data_attributes( 'container', array( 'enlarge' => $enlarge_by ) );
				$element_styling->add_styles( 'container', array( 'padding' => "{$padding}% 0px;" ) );
			}

			$element_styling->add_styles( 'container-inner', array( 'padding-bottom' => "{$atts['height']}%" ) );

			if( 'av-control-default' == $atts['control_layout'] )
			{
				$element_styling->add_styles( 'gallery-link', array( 'color' => $atts['lightbox_arrow_color'] ), 'skip_empty' );
				$element_styling->add_styles( 'gallery-link', array( 'background-color' => $atts['lightbox_arrow_bg_color'] ), 'skip_empty' );
			}

			if( $atts['transition_speed'] != '' )
			{
				$duration = $element_styling->transition_duration_rules( $atts['transition_speed'] / 1000.0 );
				$element_styling->add_styles( 'slider', $duration );
			}

			//	prepare for slideshow options
			$atts['animation'] = 'slide';

			$ui_args = array(
						'element_id'		=> $element_id,
						'element_styling'	=> $element_styling,
						'atts'				=> $atts,
						'autoplay_option'	=> 'true',
						'context'			=> __CLASS__,
					);

			$this->addSlideshowAttributes( $ui_args );


			if( ! empty( $atts['img_copyright'] ) )
			{
				$element_styling->add_classes( 'copyright', array( "av-copyright-{$atts['img_copyright']}" ) );

				if( ! empty( $atts['img_copyright_color'] ) )
				{
					$element_styling->add_styles( 'copyright', array( 'color' => $atts['img_copyright_color'] ) );
				}

				if( ! empty( $atts['img_copyright_bg'] ) )
				{
					$element_styling->add_styles( 'copyright', array( 'background-color' => $atts['img_copyright_bg'] ) );
				}

				$element_styling->add_responsive_font_sizes( 'copyright', 'img_copyright_font', $atts, $this );
			}

			$selectors = array(
						'container'			=> ".av-horizontal-gallery.{$element_id}",
						'container-inner'	=> ".av-horizontal-gallery.{$element_id} .av-horizontal-gallery-inner",
						'slider'			=> ".av-horizontal-gallery.{$element_id} .av-horizontal-gallery-slider",
						'gallery-link'		=> "#top .av-horizontal-gallery.{$element_id} .av-horizontal-gallery-link",
						'copyright'			=> ".av-horizontal-gallery.{$element_id} .av-image-copyright"
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

			if( 'disabled' == $atts['img_scrset'] )
			{
				Av_Responsive_Images()->force_disable( 'disabled' );
			}

			$attachments = get_posts( array(
								'include'			=> $ids,
								'post_status'		=> 'inherit',
								'post_type'			=> 'attachment',
								'post_mime_type'	=> 'image',
								'order'				=> 'DESC',
								'orderby'			=> 'post__in'
							)
						);


			$display_char = av_icon( 'ue869', 'entypo-fontello' );

			$output = '';

			if( ! empty( $attachments ) && is_array( $attachments ) )
			{
				avia_sc_gallery_horizontal::$hor_gallery++;

				/**
				 * @since 4.8.2
				 * @param string $image_size
				 * @param string $shortcode
				 * @param array $atts
				 * @param string $content
				 * @return string
				 */
				$lightbox_img_size = apply_filters( 'avf_alb_lightbox_image_size', 'large', $this->config['shortcode'], $atts, $content );

				if( $initial !== '' )
				{
					if( ! is_numeric( $initial ) || ( (int) $initial <= 0 ) )
					{
						$initial = 1;
					}
					else
					{
						$initial = ( (int) $initial > count( $attachments ) ) ? count( $attachments ) : $initial;
					}

					$element_styling->add_data_attributes( 'container', array( 'initial' => $initial ) );
				}

				$counter = 0;
				$markup = avia_markup_helper( array( 'context' => 'image', 'echo' => false, 'custom_markup' => $meta['custom_markup'] ) );

				$add_id = ShortcodeHelper::is_top_level() ? '' : $meta['custom_el_id'];


				$style_tag = $element_styling->get_style_tag( $element_id );
				$container_class = $element_styling->get_class_string( 'container' );
				$container_data = $element_styling->get_data_attributes_json_string( 'container', 'slideshow-data' );

				$output .= $style_tag;
				$output .= "<div {$add_id} class='{$container_class} av-horizontal-gallery-" . self::$hor_gallery . "' {$container_data} {$markup}>";

				if( count( $attachments ) > 1 )
				{
					$output .=	$this->slide_navigation_arrows( $atts );

					if( false !== strpos( $atts['slider_navigation'], 'av-navigate-dots' ) )
					{
						$output .= $this->slide_navigation_dots( $atts, count( $attachments ) );
					}
				}

				$output .=		"<div class='av-horizontal-gallery-inner' data-av-height='{$height}'>";
				$output .=			'<div class="av-horizontal-gallery-slider">';

				foreach( $attachments as $attachment )
				{
					$counter ++;
					$img = wp_get_attachment_image_src( $attachment->ID, $size );
					$lightbox_img_src = Av_Responsive_Images()->responsive_image_src( $attachment->ID, $lightbox_img_size );

					$alt = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );
					$alt = ! empty( $alt ) ? esc_attr( $alt ) : '';

					$title = trim( $attachment->post_title ) ? esc_attr( $attachment->post_title ) : '';
					$description = trim( $attachment->post_content ) ? esc_attr( $attachment->post_content ) : '';
					$caption = trim( $attachment->post_excerpt ) ? esc_attr( $attachment->post_excerpt ) : '';

					$custom_link = get_post_meta( $attachment->ID, 'av-custom-link', true );
					$custom_link = ! empty( $custom_link ) ? esc_attr( $custom_link ) : '';

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

					if( $links != '' )		//	ignore custom link, if lightbox is active
					{
						$custom_link = '';
					}
					else if( $custom_link != '' )
					{
						if( '' != $title )
						{
							$title = ' - ' . $title;
						}
						$title = __( 'Click to show details', 'avia_framework' ) . $title;
					}

					$output .= '<div class="av-horizontal-gallery-wrap noHover">';

					if( ( '' == $links ) && ( $custom_link != '' ) )
					{
						$target = ( $link_dest != '' ) ?  ' target="' . $link_dest . '" rel="noopener noreferrer"' : '';
						$output .= '<a href="' . $custom_link . '"' . $target . '>';
					}

					$img_tag = "<img class='av-horizontal-gallery-img' width='{$img[1]}' height='{$img[2]}' src='{$img[0]}' title='{$title}' alt='{$alt}' />";
					$img_tag = Av_Responsive_Images()->prepare_single_image( $img_tag, $attachment->ID, $lazy_loading );

					$output .= $img_tag;

					if( ( '' == $links ) && ( $custom_link != '' ) )
					{
						$output .= '</a>';
					}
					else if( $links == 'active' )
					{
						$lightbox_attr = Av_Responsive_Images()->html_attr_image_src( $lightbox_img_src, false );
						$output .= "<a {$lightbox_attr} class='av-horizontal-gallery-link' {$display_char} title='{$lightbox_title}' alt='{$alt}'>";
						$output .= '</a>';
					}

					if( ! empty( $img_copyright ) )
					{
						$copyright_text = get_post_meta( $attachment->ID, '_avia_attachment_copyright', true );
						$copyright_class = $element_styling->get_class_string( 'copyright' );

						if( ! empty( $copyright_text ) )
						{
							$output .= "<div class='av-image-copyright {$copyright_class}'>";
							$output .=		"<span>{$copyright_text}</span>";
							$output .= '</div>';
						}
					}

					$output .= '</div>';
				}

				$output .=			'</div>';
				$output .=		'</div>';
				$output .= '</div>';
			}

			$output = Av_Responsive_Images()->make_content_images_responsive( $output );

			Av_Responsive_Images()->force_disable( 'reset' );

			if( ! ShortcodeHelper::is_top_level() )
			{
				return $output;
			}

			$av_display_classes = $element_styling->responsive_classes_string( 'hide_element', $atts );

			$params = array();
			$params['class'] = "main_color av-horizontal-gallery-fullwidth avia-no-border-styling {$av_display_classes} {$meta['el_class']}";
			$params['open_structure'] = false;
			$params['id'] = AviaHelper::save_string( $meta['custom_id_val'] , '-', 'av-horizontal-gallery-' . avia_sc_gallery_horizontal::$hor_gallery );
			$params['custom_markup'] = $meta['custom_markup'];

			if( $meta['index'] == 0 )
			{
				$params['class'] .= ' avia-no-border-styling';
			}

			//we dont need a closing structure if the element is the first one or if a previous fullwidth element was displayed before
			if( $meta['index'] == 0 )
			{
				$params['close'] = false;
			}

			if( ! empty( $meta['siblings']['prev']['tag'] ) && in_array( $meta['siblings']['prev']['tag'], AviaBuilder::$full_el_no_section ) )
			{
				$params['close'] = false;
			}

			$html = $output;

			$output  = avia_new_section( $params );
			$output .= $html;
			$output .= '</div><!-- close section -->'; //close section

			//if the next tag is a section dont create a new section from this shortcode
			if( ! empty( $meta['siblings']['next']['tag']) && in_array( $meta['siblings']['next']['tag'], AviaBuilder::$full_el ) )
			{
				$skipSecond = true;
			}

			//if there is no next element dont create a new section.
			if( empty( $meta['siblings']['next']['tag'] ) )
			{
				$skipSecond = true;
			}

			if( empty( $skipSecond ) )
			{
				$output .= avia_new_section( array( 'close' => false, 'id' => 'after_horizontal_gallery' ) );
			}

			return $output;
		}

		/**
		 * Create arrows to scroll image slides
		 *
		 * @since 4.8.3			reroute to aviaFrontTemplates
		 * @param array $atts
		 * @return string
		 */
		protected function slide_navigation_arrows( array $atts )
		{
			$args = array(
						'class_prev'	=> 'av-horizontal-gallery-prev',
						'class_next'	=> 'av-horizontal-gallery-next',
						'context'		=> get_class( $this ),
						'params'		=> $atts
					);

			return aviaFrontTemplates::slide_navigation_arrows( $args );
		}

		/**
		 * Create dots to scroll tabs
		 *
		 * @since 5.0
		 * @param array $atts
		 * @param int $total_entries
		 * @return string
		 */
		protected function slide_navigation_dots( array $atts, $total_entries )
		{
			$args = array(
						'class_main'		=> 'avia-slideshow-dots avia-slideshow-controls av-horizontal-gallery-dots fade-in',
						'total_entries'		=> $total_entries,
						'container_entries'	=> 1,
						'context'			=> get_class( $this ),
						'params'			=> $atts
					);

			return aviaFrontTemplates::slide_navigation_dots( $args );
		}

	}
}
