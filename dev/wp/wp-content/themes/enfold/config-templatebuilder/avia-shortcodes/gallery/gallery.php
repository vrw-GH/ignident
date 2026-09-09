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
			$this->config['icon']			= AviaBuilder::$path['iconsURL'] . 'sc-gallery.svg';
			$this->config['order']			= 100;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode'] 		= 'av_gallery';
			//	the canvas shows the pictures it holds - see editor_element_images()
			$this->config['alb_items']		= array( 'images' => 'ids' );
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
							'delete'	=> __( 'Clear Gallery', 'avia_framework' ),
							'delete_class' => 'avia-delete-gallery-button',
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

			$edit_post_type = '';
			if( isset( $_GET['post'] ) )
			{
				$edit_post_type = get_post_type( (int) $_GET['post'] );
			}
			else if( isset( $_POST['post_id'] ) )
			{
				$edit_post_type = get_post_type( (int) $_POST['post_id'] );
			}
			else if( isset( $_GET['post_type'] ) )
			{
				$edit_post_type = sanitize_key( $_GET['post_type'] );
			}

			$show_woo_fields = class_exists( 'WooCommerce' ) && in_array( $edit_post_type, array( 'product', 'alb_custom_layout' ), true );

			if( $show_woo_fields )
			{
				$c[] = array(
					'name'		=> __( 'Use WooCommerce Product Images', 'avia_framework' ),
					'desc'		=> __( 'On a product page, automatically uses the product\'s WooCommerce gallery images instead of the selection above. Falls back to the manual gallery if no product images are found.', 'avia_framework' ),
					'id'		=> 'woo_product_gallery',
					'type'		=> 'checkbox',
					'std'		=> '',
					'lockable'	=> true
				);

				$c[] = array(
					'name'		=> __( 'Exclude Featured Image', 'avia_framework' ),
					'desc'		=> __( 'By default the product\'s featured image is shown first. Check this to use only the WooCommerce gallery images.', 'avia_framework' ),
					'id'		=> 'woo_exclude_featured',
					'type'		=> 'checkbox',
					'std'		=> '',
					'lockable'	=> true,
					'required'	=> array( 'woo_product_gallery', 'not', '' )
				);

				$c[] = array(
					'name'		=> __( 'Product Images To Show', 'avia_framework' ),
					'desc'		=> __( 'How many of the product images to display. Choose &quot;All images&quot; to show every gallery image.', 'avia_framework' ),
					'id'		=> 'woo_image_count',
					'type'		=> 'select',
					'std'		=> 'all',
					'lockable'	=> true,
					'required'	=> array( 'woo_product_gallery', 'not', '' ),
					'subtype'	=> array_merge(
										array( __( 'All images', 'avia_framework' ) => 'all' ),
										AviaHtmlHelper::number_array( 1, 12, 1 )
									)
				);

				$c[] = array(
					'name'		=> __( 'Product Images Order', 'avia_framework' ),
					'desc'		=> __( 'The order the product images are displayed in.', 'avia_framework' ),
					'id'		=> 'woo_image_order',
					'type'		=> 'select',
					'std'		=> 'gallery',
					'lockable'	=> true,
					'required'	=> array( 'woo_product_gallery', 'not', '' ),
					'subtype'	=> array(
										__( 'Product gallery order (default)', 'avia_framework' )	=> 'gallery',
										__( 'Reversed', 'avia_framework' )							=> 'reverse',
										__( 'Random', 'avia_framework' )							=> 'random',
										__( 'Newest first', 'avia_framework' )						=> 'date_desc',
										__( 'Oldest first', 'avia_framework' )						=> 'date',
										__( 'Title A to Z', 'avia_framework' )						=> 'title',
										__( 'Title Z to A', 'avia_framework' )						=> 'title_desc'
									)
				);
			}

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
												__( 'Mosaic Grid', 'avia_framework' )						=> 'mosaic',
												__( 'Filmstrip', 'avia_framework' )							=> 'filmstrip',
												__( 'Editorial Split', 'avia_framework' )					=> 'editorial',
												__( 'Spotlight', 'avia_framework' )							=> 'spotlight',
											)
						),

						array(
							'name'		=> __( 'Frame Style', 'avia_framework' ),
							'desc'		=> __( 'Default adds a border and padding around each image. Plain removes all decoration — images sit with a 2px gap only.', 'avia_framework' ),
							'id'		=> 'frame',
							'type'		=> 'select',
							'std'		=> 'default',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Default (bordered)', 'avia_framework' )	=> 'default',
												__( 'Plain (frameless)', 'avia_framework' )		=> 'plain',
											)
						),

						array(
							'name'		=> __( 'Layout Orientation', 'avia_framework' ),
							'desc'		=> __( 'Applies to Mosaic, Editorial and Filmstrip. Landscape uses wide cells; Portrait uses tall (3:4) cells that suit clothing/product images.', 'avia_framework' ),
							'id'		=> 'layout_orientation',
							'type'		=> 'select',
							'std'		=> 'landscape',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Landscape cells', 'avia_framework' )		=> 'landscape',
												__( 'Portrait cells (3:4)', 'avia_framework' )	=> 'portrait',
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
							'desc'		=> __('Number of columns for the Small Thumbnails layout (and the thumbnail strip beneath the Big-image layout). The Mosaic, Filmstrip, Editorial and Spotlight layouts ignore this — they use their own fixed grid/scroll structure.', 'avia_framework' ),
							'id'		=> 'columns',
							'type'		=> 'select',
							'std'		=> '5',
							'lockable'	=> true,
							'required'	=> array( 'style', 'contains', 'thumb' ),
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
						'frame'			=> 'default',
						'layout_orientation'	=> 'landscape',
						'columns'		=> 5,
						'lazyload'      => 'avia_lazyload',
						'html_lazy_loading'				=> 'disabled',
						'crop_big_preview_thumbnail'	=> 'avia-gallery-big-crop-thumb',
						'woo_product_gallery'			=> '',
						'woo_exclude_featured'			=> '',
						'woo_image_count'				=> 'all',
						'woo_image_order'				=> 'gallery',

						'ajax_request'	=> false
					);

			$default = $this->sync_sc_defaults_array( $default );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			Avia_Dynamic_Content()->read( $atts, $this, $shortcodename, $content );

			$atts['ids'] = Avia_Dynamic_Content()->check_id_list( $atts['ids_dynamic'], $atts['ids'] );

			if( ! empty( $atts['woo_product_gallery'] ) && class_exists( 'WooCommerce' ) )
			{
				$post_id = 0;

				if( is_product() )
				{
					$post_id = get_the_ID();
				}
				else if( is_admin() )
				{
					$post_id = $this->woo_sample_product_id();
				}
			}

			if( ! empty( $post_id ) )
			{
				$featured_id  = get_post_thumbnail_id( $post_id );
				$gallery_meta = get_post_meta( $post_id, '_product_image_gallery', true );
				$gallery_ids  = ! empty( $gallery_meta ) ? explode( ',', $gallery_meta ) : array();

				if( ! empty( $featured_id ) && empty( $atts['woo_exclude_featured'] ) )
				{
					array_unshift( $gallery_ids, $featured_id );
				}

				if( ! empty( $gallery_ids ) )
				{
					$gallery_ids = array_map( 'absint', $gallery_ids );

					switch( $atts['woo_image_order'] )
					{
						case 'reverse':
							$gallery_ids = array_reverse( $gallery_ids );
							break;
						case 'random':
							shuffle( $gallery_ids );
							break;
						case 'date':
						case 'date_desc':
						case 'title':
						case 'title_desc':
							$orderby = ( false !== strpos( $atts['woo_image_order'], 'title' ) ) ? 'title' : 'date';
							$order = ( false !== strpos( $atts['woo_image_order'], 'desc' ) ) ? 'DESC' : 'ASC';
							$sorted = get_posts( array(
											'post_type'		=> 'attachment',
											'post__in'		=> $gallery_ids,
											'orderby'		=> $orderby,
											'order'			=> $order,
											'numberposts'	=> -1,
											'fields'		=> 'ids',
											'post_status'	=> 'inherit'
										) );
							if( ! empty( $sorted ) )
							{
								$gallery_ids = $sorted;
							}
							break;
					}

					if( 'all' !== $atts['woo_image_count'] )
					{
						$limit = (int) $atts['woo_image_count'];
						if( $limit > 0 )
						{
							$gallery_ids = array_slice( $gallery_ids, 0, $limit );
						}
					}

					$atts['ids'] = implode( ',', $gallery_ids );
				}
			}

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

			$new_layout_styles = array( 'mosaic', 'filmstrip', 'editorial', 'spotlight' );
			$is_new_layout = in_array( $atts['style'], $new_layout_styles );

			$classes = array(
						'avia-gallery',
						$element_id,
						$class_animation
					);

			if( $is_new_layout )
			{
				$classes[] = 'avia-gallery-' . $atts['style'];
			}

			if( 'plain' === $atts['frame'] )
			{
				$classes[] = 'avia-gallery-plain';
			}

			if( 'portrait' === $atts['layout_orientation'] && in_array( $atts['style'], array( 'mosaic', 'filmstrip', 'editorial' ) ) )
			{
				$classes[] = 'av-orient-portrait';
			}

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

			// animation — skip the fade/scale-in for the grid/scroll layouts, render immediately
			if( $atts['lazyload'] != 'animations_off' && ! $is_new_layout )
			{
				$element_styling->add_classes( 'container', 'avia-gallery-animate' );
			}

			if( ! $is_new_layout )
			{
				$thumb_width = round( 100 / $atts['columns'], 4 );
				$element_styling->add_styles( 'thumb-link', array( 'width' => $thumb_width . '%' ) );
			}

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

			$big_thumb       = '';
			$thumbs          = '';
			$stack_thumbs    = '';
			$overflow_thumbs = '';
			$counter         = 0;
			$is_spotlight    = ( 'spotlight' === $style );

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

				$orient_class = '';

				if( in_array( $atts['style'], array( 'mosaic', 'filmstrip', 'editorial', 'spotlight' ), true ) )
				{
					$dims = wp_get_attachment_metadata( $attachment->ID );

					if( ! empty( $dims['width'] ) && ! empty( $dims['height'] ) )
					{
						$ratio = $dims['width'] / $dims['height'];

						if( $ratio > 1.2 )
						{
							$orient_class = 'av-item-landscape';
						}
						else if( $ratio < 0.833 )
						{
							$orient_class = 'av-item-portrait';
						}
						else
						{
							$orient_class = 'av-item-square';
						}
					}
				}

				$class = $counter++ % $columns ? "class='$imagelink $custom_link_class $orient_class'" : "class='first_thumb $imagelink $custom_link_class $orient_class'";

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
				}

				if( $is_spotlight && 1 == $counter )
				{
					$img_tag = "<img width='{$prev[1]}' height='{$prev[2]}' src='{$prev[0]}' title='{$title}' alt='{$alt}' />";
					$img_tag = Av_Responsive_Images()->prepare_single_image( $img_tag, $attachment->ID, $html_lazy_loading );
					$lightbox_attr = Av_Responsive_Images()->html_attr_image_src( $lightbox_img_src, false );

					$big_thumb  = "<a class='avia-gallery-big fakeLightbox {$imagelink} {$custom_link_class}' {$lightbox_attr} data-onclick='1' title='{$lightbox_title}' {$rel}>";
					$big_thumb .=		"<span class='avia-gallery-big-inner' {$markup_url}>";
					$big_thumb .=			$img_tag;
					$big_thumb .=		'</span>';
					$big_thumb .= '</a>';
				}

				$img_tag = "<img {$tooltip} src='{$img[0]}' width='{$img[1]}' height='{$img[2]}'  title='{$title}' alt='{$alt}' />";
				$img_tag = Av_Responsive_Images()->prepare_single_image( $img_tag, $attachment->ID, $html_lazy_loading );
				$lightbox_attr = Av_Responsive_Images()->html_attr_image_src( $lightbox_img_src, false );

				// @since 4.8.8.2 support for responsive images:  https://kriesi.at/support/topic/missing-scrset-in-alb-gallery/
				$prev_img_tag = "<img width='{$prev[1]}' height='{$prev[2]}' src='{$prev[0]}' title='{$title}' alt='{$alt}' />";
				$prev_img_tag = Av_Responsive_Images()->prepare_single_image( $prev_img_tag, $attachment->ID, 'enabled' );

				$thumb_html  = "<a {$lightbox_attr} data-rel='gallery-" . self::$gallery . "' data-prev-img='{$prev[0]}' {$class} data-onclick='{$counter}' title='{$lightbox_title}' {$markup_url} {$rel}>";
				$thumb_html .=		$img_tag;
				$thumb_html .=		"<div class='big-prev-fake'>{$prev_img_tag}</div>";
				$thumb_html .= '</a>';

				if( $is_spotlight )
				{
					if( $counter > 1 && $counter <= 4 )
					{
						$stack_thumbs .= $thumb_html;
					}
					else if( $counter > 4 )
					{
						$overflow_thumbs .= $thumb_html;
					}
				}
				else
				{
					$thumbs .= $thumb_html;
				}
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
			$output .=     $markup_meta;

			if( $is_spotlight )
			{
				$output .= "<div class='av-spotlight-main-wrap'>";
				$output .=		"<div class='av-spotlight-main'>{$big_thumb}</div>";
				$output .=		"<div class='av-spotlight-stack'>{$stack_thumbs}</div>";
				$output .= "</div>";

				if( ! empty( $overflow_thumbs ) )
				{
					$output .= "<div class='avia-gallery-thumb av-spotlight-overflow'>{$overflow_thumbs}</div>";
				}
			}
			else if ( 'thumbnails' !== $style ) {
			    $output .= "<div class='avia-gallery-big-wrapper'>";
			    $output .=     $big_thumb;
			    if ( $control_layout !== 'av-control-hidden' ) {
			        $output .= $this->slide_navigation_arrows( $atts );
			    }

			    $output .= "</div>";
			    $output .= "<div class='avia-gallery-thumb'>{$thumbs}</div>";
			} else {
			    $output .= $big_thumb;
			    $output .= "<div class='avia-gallery-thumb'>{$thumbs}</div>";
			}

			$output .= '</div>';


			$html = Av_Responsive_Images()->make_content_images_responsive( $output );

			Av_Responsive_Images()->force_disable( 'reset' );

			return $html;
		}

		/**
		 * Sample product to source images from in the builder preview / admin, where there is no
		 * current product. Prefers a published product that actually has gallery images.
		 *
		 * @return int
		 */
		protected function woo_sample_product_id()
		{
			$sample = get_posts( array(
							'post_type'			=> 'product',
							'post_status'		=> 'publish',
							'posts_per_page'	=> 1,
							'fields'			=> 'ids',
							'meta_query'		=> array(
													array(
														'key'		=> '_product_image_gallery',
														'value'		=> '',
														'compare'	=> '!='
													)
												)
						) );

			if( empty( $sample ) )
			{
				$sample = get_posts( array(
								'post_type'			=> 'product',
								'post_status'		=> 'publish',
								'posts_per_page'	=> 1,
								'fields'			=> 'ids'
							) );
			}

			return ! empty( $sample ) ? (int) $sample[0] : 0;
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

