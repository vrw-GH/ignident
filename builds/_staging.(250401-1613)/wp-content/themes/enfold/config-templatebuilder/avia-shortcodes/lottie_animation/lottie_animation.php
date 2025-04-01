<?php
/**
 * Lottie Files
 *
 * https://lottiefiles.com/
 *
 * @since 5.5
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


/**
 * skip if disabled via functions.php:  current_theme_supports( 'avia_exclude_lottie-animations' )
 */
if( ! class_exists( 'avia_LottieAnimations', false ) )
{
	return;
}

if( ! class_exists( 'avia_sc_lottie_animation', false ) )
{
	class avia_sc_lottie_animation extends aviaShortcodeTemplate
	{
		/**
		 * Counter to create unique container id
		 *
		 * @since 5.5
		 * @var int
		 */
		protected $counter;

		/**
		 * @since 5.5
		 * @param \AviaBuilder $builder
		 */
		public function __construct( \AviaBuilder $builder )
		{
			parent::__construct( $builder );

			$this->counter = 0;

			add_filter( 'avf_enable_enqueue_dotlottie_script', array( $this, 'handler_avf_enable_enqueue_dotlottie_script' ), 10, 1 );
		}

		/**
		 * Create the config array for the shortcode button
		 *
		 * @since 5.5
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['self_closing']	= 'no';
			$this->config['base_element']	= 'yes';

			$this->config['name']			= __( 'Lottie Animation', 'avia_framework' );
			$this->config['tab']			= __( 'Media Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-lottie.png';
			$this->config['order']			= 100;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode'] 		= 'av_lottie';
			$this->config['tooltip'] 	    = __( 'Inserts a single lottie animation of your choice', 'avia_framework' );
			$this->config['preview'] 		= 1;
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
		}

		/**
		 * @since 5.5
		 */
		protected function admin_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
//			$min_css = avia_minify_extension( 'css' );
			$min_js = avia_minify_extension( 'js' );

			//load js
			wp_enqueue_script( 'avia-module-sc-lottie-animation', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/lottie_animation/lottie_animation_admin{$min_js}.js", array(  ), $ver, true );
		}

		/**
		 * @since 5.5
		 */
		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_css = avia_minify_extension( 'css' );
			$min_js = avia_minify_extension( 'js' );

			//load css
			wp_enqueue_style( 'avia-module-sc-lottie-animation', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/lottie_animation/lottie_animation{$min_css}.css", array( 'avia-layout' ), $ver );

			//load js
			wp_enqueue_script( 'avia-module-sc-lottie-animation', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/lottie_animation/lottie_animation{$min_js}.js", array( 'avia-shortcodes' ), $ver, true );
		}

		/**
		 * Allow loading of general frontend script when element is enabled
		 *
		 * @since 5.5
		 * @param boolean $enable
		 * @return boolean
		 */
		public function handler_avf_enable_enqueue_dotlottie_script( $enable )
		{
			//	if already enabled by other element, do not disable it
			if( false !== $enable )
			{
				return $enable;
			}

			return ! $this->is_sc_disabled();
		}

		/**
		 * Popup Elements
		 *
		 * If this function is defined in a child class the element automatically gets an edit button, that, when pressed
		 * opens a modal window that allows to edit the element properties
		 *
		 * @since 5.5
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
													$this->popup_key( 'content_lottie_animation' ),
													$this->popup_key( 'content_lottie_settings' ),
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
													$this->popup_key( 'styling_lottie_size' ),
													$this->popup_key( 'styling_lottie_alignment' ),
													$this->popup_key( 'styling_lottie_colors' ),
													$this->popup_key( 'styling_margin_padding' ),
													'border_toggle',
													'box_shadow_toggle'
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
								'template_id'	=> $this->popup_key( 'advanced_link' ),
							),

						array(
								'type'			=> 'template',
								'template_id'	=> $this->popup_key( 'advanced_animation' ),
							),

						array(
								'name'			=> __( 'Animation Position', 'avia_framework' ),
								'desc'			=> __( 'Set a position for the animation.', 'avia_framework' ),
								'type'			=> 'template',
								'template_id'	=> 'position',
								'toggle'		=> true,
								'lockable'		=> true
							),

						array(
								'type'			=> 'template',
								'template_id'	=> $this->popup_key( 'advanced_performance' ),
							),

						array(
								'type'			=> 'template',
								'template_id'	=> 'mask_overlay',
								'toggle'		=> true,
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
						'args'			=> array(
												'sc'	=> $this
											)
					),

				array(
						'type' 	=> 'tab_container_close',
						'nodescription' => true
					),

				array(
						'id'	=> 'av_element_hidden_in_editor',
						'type'	=> 'hidden',
						'std'	=> '0'
					)
			);

		}

		/**
		 * Create and register templates for easier maintainance
		 *
		 * @since 5.5
		 */
		protected function register_dynamic_templates()
		{
			/**
			 * Content Tab
			 * ===========
			 */

			$desc  = __( 'Either upload a new or choose an existing lottie animation from your media library. Supported filetype extensions are &quot;.lottie&quot; and &quot;.json&quot;.', 'avia_framework' ) . '<br /><br />';
			$desc .= sprintf( __( 'More information and free ready to use animations can be found at %s LottieFiles Homepage %s', 'avia_framework' ), '<a href="https://lottiefiles.com/" target="_blank" rel="noopener noreferrer">', '</a>' );

			$c = array(
						array(
							'name'		=> __( 'Choose A Lottie Animation', 'avia_framework' ),
							'desc'		=> $desc,
							'id'		=> 'src',
							'type'		=> 'lottie_animation',
							'title'		=> __( 'Insert A Lottie Animation', 'avia_framework' ),
							'button'	=> __( 'Insert Animation', 'avia_framework' ),
							'std'		=> AviaLottieAnimations()->placeholder_url(),					//AviaBuilder::$path['imagesURL'] . 'placeholder.jpg',
							'lockable'	=> true,
							'locked'	=> array( 'src', 'attachment', 'attachment_size' )
						),

						array(
							'name'		=> __( 'Direct Link', 'avia_framework' ),
							'desc'		=> __( 'Enter a hardcoded link to a lottie animation. If not empty this will override any selection from media library.', 'avia_framework' ),
							'id'		=> 'lottie_src',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true,
							'container_class'	=> 'avia-element-fullwidth'
						)
					);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Lottie Animation', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_lottie_animation' ), $template );


			$c = array(
						array(
							'name'		=> __( 'Playback Speed', 'avia_framework' ),
							'desc'		=> __( 'Select animation play speed', 'avia_framework' ),
							'id'		=> 'speed',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> AviaHtmlHelper::number_array( 0.1, 3, 0.1, array( 'Default' => '' ) )
						),

						array(
							'name'		=> __( 'Autoplay Animation', 'avia_framework' ),
							'desc'		=> __( 'Select when to start play animation', 'avia_framework' ),
							'id'		=> 'autoplay',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Start when it comes in viewport', 'avia_framework' )				=> '',
												__( 'Start when document is loaded', 'avia_framework' )					=> 'start_loaded',
												__( 'No autoplay (controls needed or on hover)', 'avia_framework' )		=> 'no_autoplay'
											)
						),

						array(
							'name'		=> __( 'Loop Animation', 'avia_framework' ),
							'desc'		=> __( 'Select to loop animation', 'avia_framework' ),
							'id'		=> 'loop',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Yes', 'avia_framework' )	=> '',
												__( 'No', 'avia_framework' )	=> 'no_loop'
											)
						),

						array(
							'name'		=> __( 'Play Animation On Hover', 'avia_framework' ),
							'desc'		=> __( 'Select to play animation on hover only. This will stop autoplay looping after first hover.', 'avia_framework' ),
							'id'		=> 'hover',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Play always', 'avia_framework' )	=> '',
												__( 'Play on hover', 'avia_framework' )	=> 'on_hover'
											)
						),

						array(
							'name'		=> __( 'Loop Count', 'avia_framework' ),
							'desc'		=> __( 'Enter the number of times to loop the animation. Leave empty for endless.', 'avia_framework' ),
							'id'		=> 'count',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'loop', 'equals', '' )
						),

						array(
							'name'		=> __( 'Playback Direction', 'avia_framework' ),
							'desc'		=> __( 'Select the direction of playback. Backwards is not supported for all animations.', 'avia_framework' ),
							'id'		=> 'direction',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Forward', 'avia_framework' )	=> '',
												__( 'Backwards', 'avia_framework' )	=> 'backwards'
											)
						),

						array(
							'name'		=> __( 'Playmode', 'avia_framework' ),
							'desc'		=> __( 'Select the playmode. Bounce will play animation forward and then backwards.', 'avia_framework' ),
							'id'		=> 'mode',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Normal', 'avia_framework' )	=> '',
												__( 'Bounce', 'avia_framework' )	=> 'bounce'
											)
						),

						array(
							'name'		=> __( 'Player Controls', 'avia_framework' ),
							'desc'		=> __( 'Select to show player controls. Will be hidden, when a link is selected.', 'avia_framework' ),
							'id'		=> 'controls',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'link', 'equals', '' ),
							'subtype'	=> array(
												__( 'Hide', 'avia_framework' )	=> '',
												__( 'Show', 'avia_framework' )	=> 'show'
											)
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

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_lottie_settings' ), $template );



			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(

						array(
							'name'		=> __( 'Animation Width', 'avia_framework' ),
							'desc'		=> __( 'Add the width of the animation, e.g. 180px, 50&percnt;. Leave empty for default. Both CSS units are allowed, px is default.', 'avia_framework' ),
							'id'		=> 'animation_width',
							'type'		=> 'input',
							'std'		=> '100%',
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Animation Height', 'avia_framework' ),
							'desc'		=> __( 'Add the width of the animation, e.g. 180px, 50&percnt;. Leave empty for default. Both CSS units are allowed, px is default.', 'avia_framework' ),
							'id'		=> 'animation_height',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Size', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_lottie_size' ), $template );



			$c = array(

					array(
							'name'		=> __( 'Animation Alignment', 'avia_framework' ),
							'desc'		=> __( 'Choose how to align your animation', 'avia_framework' ),
							'id'		=> 'align',
							'type'		=> 'select',
							'std'		=> 'center',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Center',  'avia_framework' )				=> 'center',
												__( 'Right',  'avia_framework' )				=> 'right',
												__( 'Left',  'avia_framework' )					=> 'left',
												__( 'No special alignment', 'avia_framework' )	=> '',
											)
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Alignment', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_lottie_alignment' ), $template );


			$c = array(

					array(
							'name'		=> __( 'Background Color', 'avia_framework' ),
							'desc'		=> __( 'Select your background color. Leave empty for transparent background', 'avia_framework' ),
							'id'		=> 'background',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'lockable'	=> true
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Colors', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_lottie_colors' ), $template );



			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'margin_padding',
								'toggle'		=> true,
								'lockable'		=> true
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_margin_padding' ), $template );


			/**
			 * Advanced Tab
			 * ============
			 */

			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'animation',
							'lockable'		=> true,
							'std'			=> 'no-animation',
							'std_none'		=> 'no-animation',
							'name'			=> __( 'Animation', 'avia_framework' ),
							'desc'			=> __( 'Add a small animation to the lottie image when the user first scrolls to the lottie image position. This is only to add some &quot;spice&quot; to the site.', 'avia_framework' ),
							'groups'		=> array( 'fade', 'slide', 'rotate', 'fade-adv', 'special' )
						),

						array(
							'name' 	=> __( 'Hover Effect', 'avia_framework' ),
							'desc' 	=> __( 'Add a mouse hover effect to the image.', 'avia_framework' ),
							'id' 	=> 'hover_effect',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'No effect', 'avia_framework' )							=> '',
												__( 'Smoothen image ( blur() )', 'avia_framework' )			=> 'av-hover-blur',
												__( 'Grayscale image', 'avia_framework' )					=> 'av-hover-grayscale',
											)
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'filter_blur',
							'id'			=> 'blur_image',
							'name'			=> __( 'Smoothen Animation On Hover', 'avia_framework' ),
							'desc'			=> __( 'Select a value to smoothen the animation (filter blur()). The bigger, the stronger.', 'avia_framework' ),
							'required'		=> array( 'hover_effect', 'equals', 'av-hover-blur' ),
							'lockable'		=> true
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'filter_grayscale',
							'id'			=> 'grayscale_image',
							'name'			=> __( 'Grayscale Animation On Hover', 'avia_framework' ),
							'desc'			=> __( 'Select a value to grayscale the animation (filter grayscale()). The bigger, the stronger.', 'avia_framework' ),
							'required'		=> array( 'hover_effect', 'equals', 'av-hover-grayscale' ),
							'lockable'		=> true
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

			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'linkpicker_toggle',
							'name'			=> __( 'Lottie Animation Link', 'avia_framework' ),
							'desc'			=> __( 'Where should your lottie animation link to. Selecting a link will hide the controls.', 'avia_framework' ),
							'subtypes'		=> array( 'no', 'manually', 'single', 'taxonomy' ),
							'target_id'		=> 'target',
							'lockable'		=> true
						)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_link' ), $c );


			$c = array(
						array(
							'name'		=> __( 'Lazy Loading Of Animation', 'avia_framework' ),
							'desc'		=> __( 'This will load animation file when element comes into viewport. This might cause the layout to shift down when the animation is displayed.', 'avia_framework' ),
							'id'		=> 'lazy_loading',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Do not use lazy loading', 'avia_framework' )	=> '',
												__( 'Enable lazy loading', 'avia_framework' )		=> 'enabled'
											)
						)
					);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Performance', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_performance' ), $template );

		}

		/**
		 * Editor Element - this function defines the visual appearance of an element on the AviaBuilder Canvas
		 * Most common usage is to define some markup in the $params['innerHtml'] which is then inserted into the drag and drop container
		 * Less often used: $params['data'] to add data attributes, $params['class'] to modify the className
		 *
		 * @since 5.5
		 * @param array $params			holds the default values for $content and $args.
		 * @return array				usually holds an innerHtml key that holds item specific markup.
		 */
		public function editor_element( $params )
		{
			$default = array();
			$locked = array();
			$attr = $params['args'];
			Avia_Element_Templates()->set_locked_attributes( $attr, $this, $this->config['shortcode'], $default, $locked );

			$template = $this->update_template_lockable( 'src', AviaLottieAnimations()->alb_backend_player( '{{src}}' ), $locked );
			$template1 = $this->update_template_lockable( 'lottie_src', AviaLottieAnimations()->alb_backend_player( '{{lottie_src}}' ), $locked );

			$player = AviaLottieAnimations()->alb_backend_player( $attr['src'] );
			$player1 = AviaLottieAnimations()->alb_backend_player( $attr['lottie_src'] );

			$params['innerHtml']  = "<div class='avia_lottie_player avia_lottie_player_wrap avia_hidden_bg_box' data-update_element_template='yes'>";
			$params['innerHtml'] .=		'<div ' . $this->class_by_arguments_lockable( 'align', $attr, $locked ) . '>';
			$params['innerHtml'] .=			"<div class='avia_lottie_player_container container1' {$template}>{$player}</div>";
			$params['innerHtml'] .=			"<div class='avia_lottie_player_container container2' {$template1}>{$player1}</div>";
			$params['innerHtml'] .=		'</div>';
			$params['innerHtml'] .= '</div>';

			$params['class'] = '';

			return $params;
		}

		/**
		 * Create custom stylings
		 *
		 * @since 5.5
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles( array $args )
		{
			$result = parent::get_element_styles( $args );

			extract( $result );

			$default = array(
							'src'				=> '',
							'attachment'		=> '',
							'attachment_size'	=> '',
							'lottie_src'		=> '',
							'link'				=> '',
							'target'			=> '',
							'animation'			=> 'no-animation',
						);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			//	user can enter shortcode and add attachment id to src
			if( is_numeric( $atts['src'] ) )
			{
				$atts['attachment'] = $atts['src'];
			}

			if( ! empty( $atts['lottie_src'] ) )
			{
				$atts['src'] = $atts['lottie_src'];
				$atts['attachment'] = false;
			}
			else if( ! empty( $atts['attachment'] ) )
			{
				/**
				 * Allows e.g. WPML to reroute to translated animation
				 */
				$posts = get_posts( array(
										'include'			=> $atts['attachment'],
										'post_status'		=> 'inherit',
										'post_type'			=> 'attachment',
										'post_mime_type'	=> AviaLottieAnimations()->lottie_mime_types(),
										'order'				=> 'ASC',
										'orderby'			=> 'post__in'
									)
								);

				if( is_array( $posts ) && ! empty( $posts ) )
				{
					$attachment_entry = $posts[0];
					$new_src = wp_get_attachment_url( $attachment_entry->ID );

					if( false !== $new_src )
					{
						$atts['src'] = $new_src;
					}
				}
				else
				{
					$atts['src'] = '';
					$atts['attachment'] = false;
				}
			}
			else
			{
				$atts['attachment'] = false;
			}

			if( empty( $atts['src'] ) )
			{
				$result['default'] = $default;
				$result['atts'] = $atts;
				$result['content'] = $content;

				return $result;
			}

			/**
			 * @since 5.5
			 * @param string $class_animation
			 * @param array $atts
			 * @param aviaShortcodeTemplate $this
			 * @param string $shortcodename
			 * @return string
			 */
			$class_animation = apply_filters( 'avf_alb_element_animation', 'av-animated-when-almost-visible', $atts, $this, $shortcodename );

			$element_styling->create_callback_styles( $atts );

			$classes = array(
							'av-lottie-animation-container',
							$element_id
					);

			if( ! empty( $atts['hover'] ) )
			{
				$classes[] = 'play-on-hover';
			}

			if( ! empty( $atts['hover_effect'] ) )
			{
				$classes[] = $atts['hover_effect'];
			}

			if( ! in_array( $atts['animation'], array( 'no-animation', '' ) ) )
			{
				$classes[] = 'avia_animated_lottie';
				$classes[] = $atts['animation'];
				$classes[] = $class_animation;

				if( is_admin() )
				{
					$classes[] = 'avia-animate-admin-preview';

					$element_styling->add_callback_styles( 'container', array( 'animation' ) );
				}
				else
				{
					$element_styling->add_callback_styles( 'container-animation', array( 'animation' ) );
				}
			}
			else
			{
				$classes[] = 'avia_not_animated_lottie';
				$classes[] = $class_animation;					//	needed to start player
			}

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes( 'container', $this->class_by_arguments( 'align', $atts, true, 'array' ) );
			$element_styling->add_classes_from_array( 'container', $meta, 'el_class' );
			$element_styling->add_responsive_classes( 'container', 'hide_element', $atts );

			$element_styling->add_responsive_styles( 'animation', 'margin', $atts, $this );
			$element_styling->add_responsive_styles( 'animation', 'padding', $atts, $this );

			if( $element_styling->add_responsive_styles( 'animation', 'css_position', $atts, $this ) > 0 )
			{
				$element_styling->add_classes( 'container', array( 'av-custom-positioned' ) );
			}

			$element_styling->add_callback_styles( 'animation', array( 'border', 'border_radius', 'box_shadow' ) );

			switch( $atts['hover_effect'] )
			{
				case 'av-hover-blur';
					$element_styling->add_callback_styles( 'animation-hover', array( 'blur_image' ) );
					break;
				case 'av-hover-grayscale':
					$element_styling->add_callback_styles( 'animation-hover', array( 'grayscale_image' ) );
					break;
			}

			if( '' != $atts['mask_overlay'] )
			{
				$element_styling->add_callback_styles( 'animation', array( 'mask_overlay' ) );
			}


			$selectors = array(
						'container'				=> ".av-lottie-animation-container.{$element_id}",
						'container-animation'	=> ".avia_transform .av-lottie-animation-container.{$element_id}.avia_start_delayed_animation",
						'animation'				=> ".av-lottie-animation-container.{$element_id} dotlottie-player",
						'animation-hover'		=> ".av-lottie-animation-container.{$element_id} dotlottie-player:hover"
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
		 * @since 5.5
		 * @param array $atts array of attributes
		 * @param string $content text within enclosing form of shortcode element
		 * @param string $shortcodename the shortcode found, when == callback name
		 * @return string $output returns the modified html string
		 */
		public function shortcode_handler( $atts, $content = '', $shortcodename = '', $meta = '' )
		{
			$result = $this->get_element_styles( compact( array( 'atts', 'content', 'shortcodename', 'meta' ) ) );

			extract( $result );

			if( empty( $atts['src'] ) )
			{
				return '';
			}

			extract( $atts );

			$link = AviaHelper::get_url( $link, $attachment, true );
			$blank = AviaHelper::get_link_target( $target );

			if( ! empty( $link ) )
			{
				$tag = 'a';
				$link = "href='{$link}'";

				$atts['controls'] = '';
			}
			else
			{
				$tag = 'div';
				$link = '';
				$blank = '';
			}

			$id = ! empty( $meta['custom_el_id'] ) ? $meta['custom_el_id'] : 'id="av-lottie-anim-' . ( ++ $this->counter ) . '"';


//			$markup_url = avia_markup_helper( array( 'context' => 'image_url', 'echo' => false, 'custom_markup' => $meta['custom_markup'] ) );
			$markup_img = avia_markup_helper( array( 'context' => 'image', 'echo' => false, 'custom_markup' => $meta['custom_markup'] ) );

			$args = array(
						'src'			=> $atts['src'],
						'autoplay'		=> $atts['autoplay'],
						'loop'			=> $atts['loop'],
						'hover'			=> $atts['hover'],
						'count'			=> $atts['count'],
						'direction'		=> $atts['direction'],
						'mode'			=> $atts['mode'],
						'speed'			=> $atts['speed'],
						'controls'		=> $atts['controls'],
						'width'			=> $atts['animation_width'],
						'height'		=> $atts['animation_height'],
						'background'	=> $atts['background'],
						'lazy_loading'	=> $atts['lazy_loading']
					);

			$style_tag = $element_styling->get_style_tag( $element_id );
			$container_class = $element_styling->get_class_string( 'container' );

			$output  = '';
			$output .= $style_tag;
			$output .= "<div {$id} class='{$container_class}' {$markup_img}>";
			$output .=		"<{$tag} class='av-lottie-animation' {$link} {$blank}>";
			$output .=			AviaLottieAnimations()->player( $args );

			$output .=		"</{$tag}>";
			$output .= '</div>';

			return $output;
		}

	}
}
