<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Image Watermark settings class.
 *
 * @class Image_Watermark_Settings
 */
class Image_Watermark_Settings {
	private $plugin;

	/**
	 * Class constructor.
	 *
	 * @return void
	 */
	public function __construct( $plugin )	{
		$this->plugin = $plugin;

		// filters
		add_filter( 'wp_redirect', [ $this, 'preserve_tab_on_redirect' ], 10, 2 );
		
		// Initialize Settings API
		add_filter( 'iw_settings_pages', [ $this, 'settings_pages' ] );
		add_filter( 'iw_settings_data', [ $this, 'settings_data' ] );
		add_action( 'iw_settings_form', [ $this, 'settings_form' ], 10, 4 );
		add_action( 'iw_settings_sidebar', [ $this, 'render_settings_sidebar' ], 10, 4 );
		
		new Image_Watermark_Settings_API( [
			'domain'     => 'image-watermark',
			'prefix'     => 'iw',
			'slug'       => 'image-watermark',
			'plugin'     => 'Image Watermark',
			'plugin_url' => IMAGE_WATERMARK_URL,
			'object'     => $this->plugin,
			'nested'     => true
		] );
	}

	/**
	 * Settings pages configuration.
	 * 
	 * @param array $pages
	 * @return array
	 */
	public function settings_pages( $pages ) {
		$pages['image-watermark'] = [
			'menu_slug'  => 'image-watermark',
			'page_title' => __( 'Image Watermark Options', 'image-watermark' ),
			'menu_title' => __( 'Watermark', 'image-watermark' ),
			'capability' => 'manage_options',
			'type'       => 'settings_page',
			'tabs'       => $this->get_settings_data()
		];
		
		return $pages;
	}

	/**
	 * Settings data configuration.
	 * 
	 * @param array $settings
	 * @return array
	 */
	public function settings_data( $settings ) {
		return $this->get_settings_data();
	}
	
	/**
	 * Render hidden inputs for settings form.
	 */
	public function settings_form( $setting, $page_type, $url_page, $tab_key ) {
		echo '<input type="hidden" name="iw_current_tab" value="' . esc_attr( $tab_key ) . '" />';
	}

	/**
	 * Render settings sidebar.
	 *
	 * @param string $setting
	 * @param string $page_type
	 * @param string $url_page
	 * @param string $tab_key
	 * @return void
	 */
	public function render_settings_sidebar( $setting, $page_type, $url_page, $tab_key ) {
		if ( $page_type !== 'settings_page' ) {
			return;
		}

		$version = isset( $this->plugin->defaults['version'] ) ? $this->plugin->defaults['version'] : '';
		$docs_link = sprintf(
			'<a href="%s" target="_blank">%s</a>',
			esc_url( 'http://www.dfactory.co/docs/image-watermark/?utm_source=image-watermark-settings&utm_medium=link&utm_campaign=docs' ),
			esc_html__( 'Documentation', 'image-watermark' )
		);
		$support_link = sprintf(
			'<a href="%s" target="_blank">%s</a>',
			esc_url( 'http://www.dfactory.co/support/?utm_source=image-watermark-settings&utm_medium=link&utm_campaign=support' ),
			esc_html__( 'Support forum', 'image-watermark' )
		);
		$rate_link = sprintf(
			'<a href="%s" target="_blank">%s</a>',
			esc_url( 'https://wordpress.org/support/plugin/image-watermark/reviews/?filter=5' ),
			esc_html__( 'Rate it 5 stars', 'image-watermark' )
		);
		$plugin_link = sprintf(
			'<a href="%s" target="_blank">%s</a>',
			esc_url( 'http://www.dfactory.co/products/image-watermark/?utm_source=image-watermark-settings&utm_medium=link&utm_campaign=blog-about' ),
			esc_html__( 'plugin page', 'image-watermark' )
		);
		$other_link = sprintf(
			'<a href="%s" target="_blank">%s</a>',
			esc_url( 'http://www.dfactory.co/products/?utm_source=image-watermark-settings&utm_medium=link&utm_campaign=other-plugins' ),
			esc_html__( 'WordPress plugins', 'image-watermark' )
		);
		?>
		<div class="df-credits">
			<h3 class="hndle"><?php echo esc_html__( 'Image Watermark', 'image-watermark' ) . ' ' . esc_html( $version ); ?></h3>
			<div class="inside">
				<h4 class="inner"><?php esc_html_e( 'Need support?', 'image-watermark' ); ?></h4>
				<p class="inner">
					<?php
					printf(
						wp_kses_post( __( 'If you are having problems with this plugin, please browse its %s or ask in the %s.', 'image-watermark' ) ),
						$docs_link,
						$support_link
					);
					?>
				</p>
				<hr />
				<h4 class="inner"><?php esc_html_e( 'Do you like this plugin?', 'image-watermark' ); ?></h4>
				<p class="inner">
					<?php
					printf(
						wp_kses_post( __( '%s on WordPress.org', 'image-watermark' ) ),
						$rate_link
					);
					echo '<br />';
					printf(
						wp_kses_post( __( 'Blog about it and link to the %s.', 'image-watermark' ) ),
						$plugin_link
					);
					echo '<br />';
					printf(
						wp_kses_post( __( 'Check out our other %s.', 'image-watermark' ) ),
						$other_link
					);
					?>
				</p>
				<hr />
				<p class="df-link inner">
					<a href="<?php echo esc_url( 'http://www.dfactory.co/?utm_source=image-watermark-settings&utm_medium=link&utm_campaign=created-by' ); ?>" target="_blank" title="<?php esc_attr_e( 'Digital Factory', 'image-watermark' ); ?>">
						<img src="<?php echo esc_url( IMAGE_WATERMARK_URL . '/images/df-black-sm.png' ); ?>" alt="<?php esc_attr_e( 'Digital Factory', 'image-watermark' ); ?>" />
					</a>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Preserve tab parameter in redirect after saving settings.
	 *
	 * @param string $location
	 * @param int $status
	 * @return string
	 */
	public function preserve_tab_on_redirect( $location, $status ) {
		// Only on settings update
		if ( strpos( $location, 'page=image-watermark' ) === false )
			return $location;

		// Get the tab from POST or current
		$tab = isset( $_POST['iw_current_tab'] ) ? sanitize_key( $_POST['iw_current_tab'] ) : '';

		if ( empty( $tab ) )
			$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'watermark';

		// Add or update tab parameter
		$location = add_query_arg( 'tab', $tab, $location );

		return $location;
	}

	/**
	 * Get settings data.
	 *
	 * @return array
	 */
	public function get_settings_data() {
		$page_heading = __( 'Image Watermark', 'image-watermark' );
		$image_sizes = get_intermediate_image_sizes();
		$image_sizes[] = 'full';
		sort( $image_sizes, SORT_STRING );
		$image_sizes_options = array_combine( $image_sizes, $image_sizes );
		$post_types = array_merge( [ 'post', 'page' ], get_post_types( [ '_builtin' => false ], 'names' ) );
		sort( $post_types, SORT_STRING );
		$post_type_options = array_combine( $post_types, $post_types );
		$watermark_on_value = [];
		if ( ! empty( $this->plugin->options['watermark_on'] ) && is_array( $this->plugin->options['watermark_on'] ) ) {
			$watermark_on_value = array_keys( $this->plugin->options['watermark_on'] );
		}
		$watermark_apply_on_value = 'everywhere';
		if ( ! empty( $this->plugin->options['watermark_apply_on'] ) && in_array( $this->plugin->options['watermark_apply_on'], [ 'everywhere', 'post_types' ], true ) ) {
			$watermark_apply_on_value = $this->plugin->options['watermark_apply_on'];
		} elseif ( ! empty( $this->plugin->options['watermark_cpt_on'] ) && is_array( $this->plugin->options['watermark_cpt_on'] ) ) {
			$cpt_on = $this->plugin->options['watermark_cpt_on'];
			$is_list = array_values( $cpt_on ) === $cpt_on;
			$has_everywhere = $is_list ? in_array( 'everywhere', $cpt_on, true ) : array_key_exists( 'everywhere', $cpt_on );
			$watermark_apply_on_value = $has_everywhere ? 'everywhere' : 'post_types';
		}
		$watermark_cpt_value = [];
		if ( ! empty( $this->plugin->options['watermark_cpt_on'] ) && is_array( $this->plugin->options['watermark_cpt_on'] ) ) {
			$cpt_on = $this->plugin->options['watermark_cpt_on'];
			$is_list = array_values( $cpt_on ) === $cpt_on;
			$watermark_cpt_value = $is_list ? $cpt_on : array_keys( $cpt_on );
			$watermark_cpt_value = array_values( array_diff( $watermark_cpt_value, [ 'everywhere' ] ) );
		}
		$settings = [
			'watermark' => [
				'option_name' => 'image_watermark_options',
				'validate'    => [ $this, 'validate_settings' ],
				'label'       => __( 'Watermark', 'image-watermark' ),
				'heading'     => $page_heading,
				'sections'    => [
					'image_watermark_general' => [
						'title' => __( 'Applying Watermark', 'image-watermark' ),
					],
					'image_watermark_position' => [
						'title' => __( 'Watermark Position', 'image-watermark' ),
					],
					'image_watermark_image' => [
						'title' => __( 'Watermark Settings', 'image-watermark' ),
					],
				],
				'fields'      => [
					// General Section
					'extension' => [
						'title'       => __( 'Image Processor', 'image-watermark' ),
						'section'     => 'image_watermark_general',
						'type'        => 'select',
						'parent'      => 'watermark_image',
						'options'     => $this->plugin->extensions,
						'description' => __( 'Select the image processing extension.', 'image-watermark' ),
					],
					'plugin_off' => [
						'title'   => __( 'Automatic Watermarking', 'image-watermark' ),
						'section' => 'image_watermark_general',
						'type'    => 'boolean',
						'parent'  => 'watermark_image',
						'label'   => __( 'Enable watermark for uploaded images.', 'image-watermark' ),
					],
					'manual_watermarking' => [
						'title'   => __( 'Manual Watermarking', 'image-watermark' ),
						'section' => 'image_watermark_general',
						'type'    => 'boolean',
						'parent'  => 'watermark_image',
						'label'   => __( 'Enable Apply Watermark option for Media Library images.', 'image-watermark' ),
					],
					'frontend_active' => [
						'title'   => __( 'Front-end Watermarking', 'image-watermark' ),
						'section' => 'image_watermark_general',
						'type'    => 'boolean',
						'parent'  => 'watermark_image',
						'label'   => __( 'Enable watermark for front-end image uploads (AJAX).', 'image-watermark' ),
					],
					'watermark_on' => [
						'title'    => __( 'Image Sizes', 'image-watermark' ),
						'section'  => 'image_watermark_general',
						'type'     => 'checkbox',
						'options'  => $image_sizes_options,
						'name' => 'image_watermark_options[watermark_on]',
						'value' => $watermark_on_value,
						'skip_saving' => true,
						'description' => wp_kses_post( __( 'Select the image sizes watermark will be applied to.', 'image-watermark' ) ),
					],
					'watermark_apply_on' => [
						'title'    => __( 'Apply Watermark To', 'image-watermark' ),
						'section'  => 'image_watermark_general',
						'type'     => 'radio',
						'options'  => [
							'everywhere' => __( 'Everywhere', 'image-watermark' ),
							'post_types' => __( 'Selected Post Types', 'image-watermark' ),
						],
						'name' => 'image_watermark_options[watermark_apply_on]',
						'value' => $watermark_apply_on_value,
						'description' => __( 'Choose whether watermarks apply to all uploads or only selected post types.', 'image-watermark' ),
					],
					'watermark_cpt_on' => [
						'title'    => __( 'Post Types', 'image-watermark' ),
						'section'  => 'image_watermark_general',
						'type'     => 'checkbox',
						'options'  => $post_type_options,
						'name' => 'image_watermark_options[watermark_cpt_on]',
						'value' => $watermark_cpt_value,
						'skip_saving' => true,
						'description' => __( 'Select post types on which watermark should be applied to uploaded images.', 'image-watermark' ),
						'condition'   => [
							'field'    => 'watermark_apply_on',
							'operator' => 'is',
							'value'    => 'post_types',
						],
						'animation'   => 'slide',
					],

					// Position Section
					'alignment' => [
						'title'    => __( 'Watermark Alignment', 'image-watermark' ),
						'section'  => 'image_watermark_position',
						'type'     => 'custom',
						'callback' => [ $this, 'render_alignment' ],
						'description' => __( 'Select the watermark alignment.', 'image-watermark' ),
						'name' => 'image_watermark_options[watermark_image][position]',
					],
					'offset' => [
						'title'    => __( 'Watermark Offset', 'image-watermark' ),
						'section'  => 'image_watermark_position',
						'type'     => 'custom',
						'callback' => [ $this, 'render_offset' ],
						'description' => __( 'Enter watermark offset value.', 'image-watermark' ),
						'callback_args' => [
							'x' => [
								'name' => 'image_watermark_options[watermark_image][offset_width]',
								'value' => $this->plugin->options['watermark_image']['offset_width'],
							],
							'y' => [
								'name' => 'image_watermark_options[watermark_image][offset_height]',
								'value' => $this->plugin->options['watermark_image']['offset_height'],
							],
						],
					],
					'offset_unit' => [
						'title'   => __( 'Offset Unit', 'image-watermark' ),
						'description' => __( 'Select the watermark offset unit.', 'image-watermark' ),
						'section' => 'image_watermark_position',
						'type'    => 'radio',
						'parent'  => 'watermark_image',
						'options' => [
							'pixels'      => __( 'pixels', 'image-watermark' ),
							'percentages' => __( 'percentages', 'image-watermark' ),
						],
					],

					// Image Section
					'type' => [
						'title'   => __( 'Watermark Type', 'image-watermark' ),
						'description' => __( 'Select the type of watermark to apply.', 'image-watermark' ),
						'section' => 'image_watermark_image',
						'type'    => 'radio',
						'parent'  => 'watermark_image',
						'options' => [
							'image' => __( 'Image', 'image-watermark' ),
							'text'  => __( 'Text', 'image-watermark' ),
						],
					],
					'preview' => [
						'title'    => __( 'Watermark Preview', 'image-watermark' ),
						'section'  => 'image_watermark_image',
						'type'     => 'custom',
						'callback' => [ $this, 'render_preview' ],
						'description' => __( 'Preview uses a 600 x 400 px stage and mirrors your size, alignment and offset settings.', 'image-watermark' ),
					],
					'image_ui' => [
						'title'    => __( 'Watermark Image', 'image-watermark' ),
						'section'  => 'image_watermark_image',
						'type'     => 'custom',
						'callback' => [ $this, 'render_watermark_image' ],
						'name' => 'image_watermark_options[watermark_image][url]',
						'description' => __( 'Save changes after selecting or removing the image.', 'image-watermark' ),
						'condition'   => [
							'field'    => 'type',
							'operator' => 'is',
							'value'    => 'image',
						],
						'animation'   => 'slide',
					],
					'text_string' => [
						'title'    => __( 'Watermark Text', 'image-watermark' ),
						'section'  => 'image_watermark_image',
						'type'     => 'text',
						'parent'   => 'watermark_image',
						'subclass' => 'regular-text',
						'description' => __( 'Enter the text to use as watermark.', 'image-watermark' ),
						'condition'   => [
							'field'    => 'type',
							'operator' => 'is',
							'value'    => 'text',
						],
						'animation'   => 'slide',
					],
					'text_font' => [
						'title'    => __( 'Font', 'image-watermark' ),
						'section'  => 'image_watermark_image',
						'type'     => 'select',
						'parent'   => 'watermark_image',
						'options'  => $this->plugin->get_allowed_fonts(),
						'description' => __( 'Select the font for the watermark text.', 'image-watermark' ),
						'condition'   => [
							'field'    => 'type',
							'operator' => 'is',
							'value'    => 'text',
						],
						'animation'   => 'slide',
					],
					'text_color' => [
						'title'       => __( 'Text Color', 'image-watermark' ),
						'section'     => 'image_watermark_image',
						'type'        => 'color',
						'parent'      => 'watermark_image',
						'subclass'    => 'iw-color-picker',
						'description' => __( 'Select the text color.', 'image-watermark' ),
						'condition'   => [
							'field'    => 'type',
							'operator' => 'is',
							'value'    => 'text',
						],
						'animation'   => 'slide',
					],
					'text_size' => [
						'title'       => __( 'Text Size', 'image-watermark' ),
						'section'     => 'image_watermark_image',
						'type'        => 'number',
						'parent'      => 'watermark_image',
						'min'         => 0,
						'max'         => 1000,
						'description' => __( 'Enter the text size in pixels.', 'image-watermark' ),
						'condition'   => [
							'field'    => 'type',
							'operator' => 'is',
							'value'    => 'text',
						],
						'animation'   => 'slide',
					],
					'size' => [
						'title'    => __( 'Watermark Size', 'image-watermark' ),
						'section'  => 'image_watermark_image',
						'type'     => 'radio',
						'parent'   => 'watermark_image',
						'name'     => 'image_watermark_options[watermark_image][watermark_size_type]',
						'value'    => $this->plugin->options['watermark_image']['watermark_size_type'],
						'options'  => [
							'0' => __( 'Original', 'image-watermark' ),
							'1' => __( 'Custom', 'image-watermark' ),
							'2' => __( 'Scaled', 'image-watermark' ),
						],
						'description' => __( 'Select how the watermark size is calculated.', 'image-watermark' ),
					],
					'size_custom' => [
						'title'    => '',
						'section'  => 'image_watermark_image',
						'type'     => 'custom',
						'callback' => [ $this, 'render_watermark_size_custom' ],
						'description' => __( 'These dimensions are used when the "Custom" method is selected above.', 'image-watermark' ),
						'callback_args' => [
							'width' => [
								'name' => 'image_watermark_options[watermark_image][absolute_width]',
								'value' => $this->plugin->options['watermark_image']['absolute_width'],
							],
							'height' => [
								'name' => 'image_watermark_options[watermark_image][absolute_height]',
								'value' => $this->plugin->options['watermark_image']['absolute_height'],
							],
						],
						'condition'   => [
							'field'    => 'size',
							'operator' => 'is',
							'value'    => '1',
						],
						'animation'   => 'slide',
					],
					'size_scaled' => [
						'title'    => '',
						'section'  => 'image_watermark_image',
						'type'     => 'range',
						'parent'   => 'watermark_image',
						'name'     => 'image_watermark_options[watermark_image][width]',
						'value'    => $this->plugin->options['watermark_image']['width'],
						'min'      => 0,
						'max'      => 100,
						'step'     => 1,
						'before_field' => '<div class="iw-range-field">',
						'after_field'  => '</div>',
						'description'  => __( 'Enter a number from 0 to 100. 100 makes the watermark image as wide as the image it is applied to.', 'image-watermark' ),
						'condition'   => [
							'field'    => 'size',
							'operator' => 'is',
							'value'    => '2',
						],
						'animation'   => 'slide',
					],
					'opacity' => [
						'title'    => __( 'Watermark Opacity', 'image-watermark' ),
						'section'  => 'image_watermark_image',
						'type'     => 'range',
						'parent'   => 'watermark_image',
						'name'     => 'image_watermark_options[watermark_image][transparent]',
						'value'    => $this->plugin->options['watermark_image']['transparent'],
						'min'      => 0,
						'max'      => 100,
						'step'     => 1,
						'before_field' => '<div class="iw-range-field">',
						'after_field'  => '</div>',
						'description'  => __( 'Adjust watermark opacity (0-100).', 'image-watermark' ),
					],
					'quality' => [
						'title'       => __( 'Image Quality', 'image-watermark' ),
						'section'     => 'image_watermark_image',
						'type'        => 'number',
						'parent'      => 'watermark_image',
						'min'         => 0,
						'max'         => 100,
						'description' => __( 'Set output image quality (0-100).', 'image-watermark' ),
						'condition'   => [
							'field'    => 'type',
							'operator' => 'is',
							'value'    => 'image',
						],
						'animation'   => 'slide',
					],
					'jpeg_format' => [
						'title'   => __( 'Image Format', 'image-watermark' ),
						'section' => 'image_watermark_image',
						'type'    => 'radio',
						'parent'  => 'watermark_image',
						'options' => [
							'baseline'    => __( 'Baseline', 'image-watermark' ),
							'progressive' => __( 'Progressive', 'image-watermark' ),
						],
						'description' => __( 'Select the image format.', 'image-watermark' ),
						'condition'   => [
							'field'    => 'type',
							'operator' => 'is',
							'value'    => 'image',
						],
						'animation'   => 'slide',
					],
				],
			],
			'protection' => [
				'option_name' => 'image_watermark_options',
				'validate'    => [ $this, 'validate_settings' ],
				'label'       => __( 'Protection', 'image-watermark' ),
				'heading'     => $page_heading,
				'sections'    => [
					'image_watermark_protection' => [
						'title' => __( 'Image Protection', 'image-watermark' ),
					],
				],
				'fields'      => [
					'rightclick' => [
						'title'   => __( 'Right Click', 'image-watermark' ),
						'section' => 'image_watermark_protection',
						'type'    => 'boolean',
						'parent'  => 'image_protection',
						'label'   => __( 'Disable right mouse click on images', 'image-watermark' ),
					],
					'draganddrop' => [
						'title'   => __( 'Drag and Drop', 'image-watermark' ),
						'section' => 'image_watermark_protection',
						'type'    => 'boolean',
						'parent'  => 'image_protection',
						'label'   => __( 'Prevent drag and drop', 'image-watermark' ),
					],
					'devtools' => [
						'title'   => __( 'Developer Tools', 'image-watermark' ),
						'section' => 'image_watermark_protection',
						'type'    => 'boolean',
						'parent'  => 'image_protection',
						'label'   => __( 'Disable developer tools', 'image-watermark' ),
					],
					'enable_toast' => [
						'title'   => __( 'Protection Notification', 'image-watermark' ),
						'section' => 'image_watermark_protection',
						'type'    => 'boolean',
						'parent'  => 'image_protection',
						'label'   => __( 'Show notification when right-click is disabled', 'image-watermark' ),
					],
					'toast_message' => [
						'title'    => '',
						'section'  => 'image_watermark_protection',
						'type'     => 'text',
						'parent'   => 'image_protection',
						'subclass' => 'regular-text',
						'description' => __( 'Enter image protection notification message.', 'image-watermark' ),
					],
					'forlogged' => [
						'title'   => __( 'Logged-in Users', 'image-watermark' ),
						'section' => 'image_watermark_protection',
						'type'    => 'boolean',
						'parent'  => 'image_protection',
						'label'   => __( 'Enable protection for logged-in users', 'image-watermark' ),
					],
				],
			],
			'status' => [
				'option_name' => 'image_watermark_options',
				'validate'    => [ $this, 'validate_settings' ],
				'label'       => __( 'Status', 'image-watermark' ),
				'heading'     => $page_heading,
				'sections'    => [
					'image_watermark_status' => [
						'title' => __( 'System Status', 'image-watermark' ),
					],
					'image_watermark_backup' => [
						'title' => __( 'Image Backup', 'image-watermark' ),
					],
					'image_watermark_other' => [
						'title' => __( 'Other', 'image-watermark' ),
					],
				],
				'fields'      => [
					'iw_status' => [
						'title'    => __( 'Current Status', 'image-watermark' ),
						'section'  => 'image_watermark_status',
						'type'     => 'custom',
						'callback' => [ $this, 'render_status' ],
					],
					'backup_image' => [
						'title'   => __( 'Backup Images', 'image-watermark' ),
						'section' => 'image_watermark_backup',
						'type'    => 'boolean',
						'parent'  => 'backup',
						'label'   => __( 'Backup original images', 'image-watermark' ),
						'description' => __( 'If enabled, original images are backed up before watermarking, allowing watermarks to be removed and originals restored.', 'image-watermark' ),
					],
					'preserve_timestamps' => [
						'title'	  => __( 'Preserve File Dates', 'image-watermark' ),
						'section' => 'image_watermark_backup',
						'type'	  => 'boolean',
						'parent'	  => 'backup',
						'label'	  => __( 'Preserve original file dates when copying or restoring', 'image-watermark' ),
						'description' => __( 'If enabled, backup and restore operations keep the original file timestamps (when supported by the server).', 'image-watermark' ),
					],
					'backup_folder' => [
						'title'    => __( 'Backup Location', 'image-watermark' ),
						'section'  => 'image_watermark_backup',
						'type'     => 'custom',
						'callback' => [ $this, 'render_backup_folder' ],
						'description' => __( 'Location where original images are stored when backups are enabled.', 'image-watermark' ),
					],
					'deactivation_delete' => [
						'title'   => __( 'Deactivation', 'image-watermark' ),
						'section' => 'image_watermark_other',
						'type'    => 'boolean',
						'parent'  => 'watermark_image',
						'label'   => __( 'Delete all database settings on plugin deactivation', 'image-watermark' ),
					],
				],
			],
		];

		return $settings;
	}

	/**
	 * Validate settings.
	 *
	 * @param array $input
	 * @return array
	 */
	public function validate_settings( $input ) {
		if ( ! current_user_can( 'manage_options' ) )
			return $input;

		// Load existing options
		$existing = get_option( 'image_watermark_options', $this->plugin->defaults['options'] );

		// If this is a reset, return defaults
		if ( isset( $_POST['reset_image_watermark_options'] ) ) {
			$defaults = $this->plugin->defaults['options'];
			// Reset review notice to prevent it from showing after reset
			$defaults['watermark_image']['review_notice'] = false;
			$defaults['watermark_image']['review_delay_date'] = 0;
			
			add_settings_error( 'image_watermark_options', 'settings_restored', __( 'Settings restored to defaults.', 'image-watermark' ), 'updated' );
			return $defaults;
		}

		// Get current tab - try POST first, then fallback to GET (for programmatic saves)
	$current_tab = isset( $_POST['iw_current_tab'] ) ? sanitize_key( $_POST['iw_current_tab'] ) : '';
	
	if ( empty( $current_tab ) ) {
		// Fallback to GET parameter if present (programmatic saves or redirects)
		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : '';
	}
	
	// Validate current_tab against known tabs to prevent malformed requests
	$config = $this->get_settings_data();
	
	if ( ! empty( $current_tab ) ) {
		if ( ! isset( $config[$current_tab] ) ) {
			// Invalid tab - reject to prevent data loss
			add_settings_error( 'image_watermark_options', 'invalid_tab', __( 'Invalid settings tab. Please try again.', 'image-watermark' ), 'error' );
			return $existing;
		}
	} else {
		// Missing tab identifier - this should not happen in normal settings page flow
		// but we'll allow it for programmatic saves by defaulting to watermark tab
		$current_tab = 'watermark';
	}

	// Start with existing
	$output = $existing;
	
	// Allow programmatic updates for review notice flags.
	if ( isset( $input['watermark_image'] ) && is_array( $input['watermark_image'] ) ) {
		if ( array_key_exists( 'review_notice', $input['watermark_image'] ) ) {
			$output['watermark_image']['review_notice'] = ( $input['watermark_image']['review_notice'] === 'true' || $input['watermark_image']['review_notice'] === true || $input['watermark_image']['review_notice'] === '1' || $input['watermark_image']['review_notice'] === 1 );
		}

		if ( array_key_exists( 'review_delay_date', $input['watermark_image'] ) ) {
			$output['watermark_image']['review_delay_date'] = max( 0, (int) $input['watermark_image']['review_delay_date'] );
		}
	}

	if ( isset( $config[$current_tab] ) ) {
			$fields = $config[$current_tab]['fields'];

			foreach ( $fields as $field_key => $field ) {
				// Skip if skip_saving is true
				if ( ! empty( $field['skip_saving'] ) )
					continue;

				// Handle nested
				$parent = isset( $field['parent'] ) ? $field['parent'] : null;

				if ( $parent ) {
					// Nested logic
					if ( isset( $input[$parent][$field_key] ) ) {
						$value = $input[$parent][$field_key];
						// Basic sanitization
						if ( $field['type'] === 'boolean' ) {
							$value = ( $value === 'true' || $value === true || $value === '1' || $value === 1 );
						} elseif ( $field['type'] === 'number' ) {
							$value = (int) $value;
						} else {
							$value = sanitize_text_field( $value );
						}
						$output[$parent][$field_key] = $value;
					} elseif ( $field['type'] === 'boolean' || $field['type'] === 'checkbox' ) {
						// Unchecked boolean/checkbox
						$output[$parent][$field_key] = false;
					}
				} else {
					// Flat logic
					if ( isset( $input[$field_key] ) ) {
						$value = $input[$field_key];
						// Basic sanitization
						if ( $field['type'] === 'boolean' ) {
							$value = ( $value === 'true' || $value === true || $value === '1' || $value === 1 );
						} elseif ( $field['type'] === 'number' ) {
							$value = (int) $value;
						} else {
							$value = sanitize_text_field( $value );
						}
						$output[$field_key] = $value;
					} elseif ( $field['type'] === 'boolean' || $field['type'] === 'checkbox' ) {
						// Unchecked boolean/checkbox
						$output[$field_key] = false;
					}
				}
			}
			
			// Handle custom fields that might not be in the standard loop
			if ( $current_tab === 'watermark' ) {
				// Watermark On (Image Sizes)
				// Only update when the field is actually posted to prevent cross-tab clearing
				if ( isset( $_POST['image_watermark_options']['watermark_on'] ) ) {
					$selected_sizes = $_POST['image_watermark_options']['watermark_on'];

					// Handle explicit empty marker (allows deliberate clearing)
					if ( $selected_sizes === 'empty' ) {
						$output['watermark_on'] = [];
					} else {
						if ( ! is_array( $selected_sizes ) ) {
							$selected_sizes = [ $selected_sizes ];
						}

						$selected_sizes = array_map( 'sanitize_key', $selected_sizes );
						$image_sizes = get_intermediate_image_sizes();
						$image_sizes[] = 'full';

						$selected_sizes = array_values( array_intersect( $selected_sizes, $image_sizes ) );
						$output['watermark_on'] = [];

						foreach ( $selected_sizes as $size ) {
							$output['watermark_on'][$size] = 1;
						}
					}
				}
				// else: preserve existing value when field not posted

				// Apply On
				$apply_on = isset( $_POST['image_watermark_options']['watermark_apply_on'] )
					? sanitize_key( $_POST['image_watermark_options']['watermark_apply_on'] )
					: ( isset( $output['watermark_apply_on'] ) ? $output['watermark_apply_on'] : '' );

				if ( ! in_array( $apply_on, [ 'everywhere', 'post_types' ], true ) ) {
					$apply_on = ( isset( $existing['watermark_cpt_on'][0] ) && $existing['watermark_cpt_on'][0] === 'everywhere' ) ? 'everywhere' : 'post_types';
				}

				$output['watermark_apply_on'] = $apply_on;

				// CPT On
				// Only update when the field is actually posted to prevent cross-tab clearing
				if ( $apply_on === 'post_types' && isset( $_POST['image_watermark_options']['watermark_cpt_on'] ) ) {
					$selected_post_types = $_POST['image_watermark_options']['watermark_cpt_on'];

					// Handle explicit empty marker (allows deliberate clearing)
					if ( $selected_post_types === 'empty' ) {
						$output['watermark_cpt_on'] = [];
					} else {
						if ( ! is_array( $selected_post_types ) ) {
							$selected_post_types = [ $selected_post_types ];
						}

						$selected_post_types = array_map( 'sanitize_key', $selected_post_types );
						$post_types = array_merge( [ 'post', 'page' ], get_post_types( [ '_builtin' => false ], 'names' ) );
						$allowed_post_types = $post_types;
						$selected_post_types = array_values( array_intersect( $selected_post_types, $allowed_post_types ) );

						$tmp = [];
						foreach ( $selected_post_types as $cpt ) {
							$tmp[$cpt] = 1;
						}
						$output['watermark_cpt_on'] = $tmp;
					}
				}
				// else: preserve existing value when field not posted
				
				// Position
				if ( isset( $_POST['image_watermark_options']['watermark_image']['position'] ) ) {
					$output['watermark_image']['position'] = sanitize_text_field( $_POST['image_watermark_options']['watermark_image']['position'] );
				}
				
				// Offsets
				if ( isset( $_POST['image_watermark_options']['watermark_image']['offset_width'] ) ) {
					$output['watermark_image']['offset_width'] = (int) $_POST['image_watermark_options']['watermark_image']['offset_width'];
				}
				if ( isset( $_POST['image_watermark_options']['watermark_image']['offset_height'] ) ) {
					$output['watermark_image']['offset_height'] = (int) $_POST['image_watermark_options']['watermark_image']['offset_height'];
				}

				// Watermark URL (attachment ID)
				if ( isset( $_POST['image_watermark_options']['watermark_image']['url'] ) ) {
					$output['watermark_image']['url'] = (int) $_POST['image_watermark_options']['watermark_image']['url'];
				}

				// Watermark Type
				if ( isset( $_POST['image_watermark_options']['watermark_image']['type'] ) && in_array( $_POST['image_watermark_options']['watermark_image']['type'], [ 'image', 'text' ], true ) ) {
					$output['watermark_image']['type'] = $_POST['image_watermark_options']['watermark_image']['type'];
				}

				// Text String
				if ( isset( $_POST['image_watermark_options']['watermark_image']['text_string'] ) ) {
					$output['watermark_image']['text_string'] = sanitize_text_field( $_POST['image_watermark_options']['watermark_image']['text_string'] );
				}

				// Text Font
				$allowed_fonts = $this->plugin->get_allowed_fonts();
				if ( isset( $_POST['image_watermark_options']['watermark_image']['text_font'] ) && array_key_exists( $_POST['image_watermark_options']['watermark_image']['text_font'], $allowed_fonts ) ) {
					$output['watermark_image']['text_font'] = $_POST['image_watermark_options']['watermark_image']['text_font'];
				}

				// Text Color
				if ( isset( $_POST['image_watermark_options']['watermark_image']['text_color'] ) && preg_match( '/^#[a-f0-9]{6}$/i', $_POST['image_watermark_options']['watermark_image']['text_color'] ) ) {
					$output['watermark_image']['text_color'] = $_POST['image_watermark_options']['watermark_image']['text_color'];
				}

				// Text Size
				if ( isset( $_POST['image_watermark_options']['watermark_image']['text_size'] ) ) {
					$output['watermark_image']['text_size'] = max( 6, min( 400, (int) $_POST['image_watermark_options']['watermark_image']['text_size'] ) );
				}

				// Watermark Size Type
				if ( isset( $_POST['image_watermark_options']['watermark_image']['watermark_size_type'] ) && in_array( (int) $_POST['image_watermark_options']['watermark_image']['watermark_size_type'], [ 0, 1, 2 ], true ) ) {
					$output['watermark_image']['watermark_size_type'] = (int) $_POST['image_watermark_options']['watermark_image']['watermark_size_type'];
				}

				// Absolute Width
				if ( isset( $_POST['image_watermark_options']['watermark_image']['absolute_width'] ) ) {
					$output['watermark_image']['absolute_width'] = max( 0, (int) $_POST['image_watermark_options']['watermark_image']['absolute_width'] );
				}

				// Absolute Height
				if ( isset( $_POST['image_watermark_options']['watermark_image']['absolute_height'] ) ) {
					$output['watermark_image']['absolute_height'] = max( 0, (int) $_POST['image_watermark_options']['watermark_image']['absolute_height'] );
				}

				// Width (scale percentage)
				if ( isset( $_POST['image_watermark_options']['watermark_image']['width'] ) ) {
					$output['watermark_image']['width'] = max( 0, min( 100, (int) $_POST['image_watermark_options']['watermark_image']['width'] ) );
				}

				// Transparent (opacity)
				if ( isset( $_POST['image_watermark_options']['watermark_image']['transparent'] ) ) {
					$output['watermark_image']['transparent'] = max( 0, min( 100, (int) $_POST['image_watermark_options']['watermark_image']['transparent'] ) );
				}

				// Quality
				if ( isset( $_POST['image_watermark_options']['watermark_image']['quality'] ) ) {
					$output['watermark_image']['quality'] = max( 0, min( 100, (int) $_POST['image_watermark_options']['watermark_image']['quality'] ) );
				}

				// JPEG Format
				if ( isset( $_POST['image_watermark_options']['watermark_image']['jpeg_format'] ) && in_array( $_POST['image_watermark_options']['watermark_image']['jpeg_format'], [ 'baseline', 'progressive' ], true ) ) {
					$output['watermark_image']['jpeg_format'] = $_POST['image_watermark_options']['watermark_image']['jpeg_format'];
				}

				// Extension
				if ( isset( $_POST['image_watermark_options']['watermark_image']['extension'] ) && isset( $this->plugin->extensions[$_POST['image_watermark_options']['watermark_image']['extension']] ) ) {
					$output['watermark_image']['extension'] = $_POST['image_watermark_options']['watermark_image']['extension'];
				}

				// Offset Unit
				if ( isset( $_POST['image_watermark_options']['watermark_image']['offset_unit'] ) && in_array( $_POST['image_watermark_options']['watermark_image']['offset_unit'], [ 'pixels', 'percentages' ], true ) ) {
					$output['watermark_image']['offset_unit'] = $_POST['image_watermark_options']['watermark_image']['offset_unit'];
				}
			}

			// Handle protection tab custom fields
			if ( $current_tab === 'protection' ) {
				// Toast Message
				if ( isset( $_POST['image_watermark_options']['image_protection']['toast_message'] ) ) {
					$output['image_protection']['toast_message'] = sanitize_text_field( $_POST['image_watermark_options']['image_protection']['toast_message'] );
				}
			}
		}
		
		add_settings_error( 'image_watermark_options', 'settings_saved', __( 'Settings saved.', 'image-watermark' ), 'updated' );

		return $output;
	}

	/**
	 * Render Alignment field.
	 */
	public function render_alignment( $args ) {
		$base_id = ! empty( $args['html_id'] ) ? $args['html_id'] : 'iw-watermark-alignment';
		$options = $this->plugin->options;
		$position = $options['watermark_image']['position'];
		$positions = [
			'top_left', 'top_center', 'top_right',
			'middle_left', 'middle_center', 'middle_right',
			'bottom_left', 'bottom_center', 'bottom_right',
		];
		?>
		<div class="iw-alignment-grid" id="<?php echo esc_attr( $base_id ); ?>" role="radiogroup" aria-label="<?php esc_attr_e( 'Watermark Alignment', 'image-watermark' ); ?>">
			<?php foreach ( $positions as $pos ) : ?>
				<div class="iw-alignment-cell">
					<input type="radio" id="iw-alignment-<?php echo esc_attr( $pos ); ?>" name="<?php echo esc_attr( $args['name'] ); ?>" value="<?php echo esc_attr( $pos ); ?>" <?php checked( $position, $pos ); ?> />
					<label for="iw-alignment-<?php echo esc_attr( $pos ); ?>" title="<?php echo esc_attr( ucwords( str_replace( '_', ' ', $pos ) ) ); ?>">
						<span class="screen-reader-text"><?php echo esc_html( str_replace( '_', ' ', $pos ) ); ?></span>
					</label>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Render Offset field.
	 */
	public function render_offset( $args ) {
		$base_id = ! empty( $args['html_id'] ) ? $args['html_id'] : 'iw-watermark-offset';
		$offset_x_id = $base_id . '-x';
		$offset_y_id = $base_id . '-y';
		?>
		<div class="iw-field-group iw-offset-group">
			<label for="<?php echo esc_attr( $offset_x_id ); ?>"><?php esc_html_e( 'x:', 'image-watermark' ); ?> <input type="number" id="<?php echo esc_attr( $offset_x_id ); ?>" name="<?php echo esc_attr( $args['callback_args']['x']['name'] ); ?>" value="<?php echo esc_attr( $args['callback_args']['x']['value'] ); ?>" min="0" max="100" /></label>

			<label for="<?php echo esc_attr( $offset_y_id ); ?>"><?php esc_html_e( 'y:', 'image-watermark' ); ?> <input type="number" id="<?php echo esc_attr( $offset_y_id ); ?>" name="<?php echo esc_attr( $args['callback_args']['y']['name'] ); ?>" value="<?php echo esc_attr( $args['callback_args']['y']['value'] ); ?>" min="0" max="100" /></label>
		</div>
		<?php
	}

	/**
	 * Render Status field.
	 */
	public function render_status( $args ) {
		$gd_available = $this->plugin->check_gd();
		$imagick_available = $this->plugin->check_imagick();
		$active_engine = $this->plugin->get_extension();
		$backup_enabled = ! empty( $this->plugin->options['backup']['backup_image'] );
		$backup_dir = defined( 'IMAGE_WATERMARK_BACKUP_DIR' ) ? IMAGE_WATERMARK_BACKUP_DIR : '';
		$backup_exists = $backup_dir ? is_dir( $backup_dir ) : false;
		$backup_writable = $backup_dir ? is_writable( $backup_dir ) : false;
		$engine_labels = [
			'gd'      => $this->plugin->extensions['gd'] ?? 'GD',
			'imagick' => $this->plugin->extensions['imagick'] ?? 'ImageMagick',
		];

		// PHP version check
		$php_version = phpversion();
		$php_ok = version_compare( $php_version, '7.2', '>=' );

		$statuses = [
			'version' => [
				'label'   => __( 'Plugin version', 'image-watermark' ),
				'status'  => 'info',
				'message' => sprintf( __( 'Image Watermark %s', 'image-watermark' ), $this->plugin->defaults['version'] ),
			],
			'php' => [
				'label'   => __( 'PHP version', 'image-watermark' ),
				'status'  => $php_ok ? 'ok' : 'error',
				'message' => sprintf( __( 'PHP %s', 'image-watermark' ), $php_version ) . ( ! $php_ok ? ' ' . __( '(7.2+ recommended)', 'image-watermark' ) : '' ),
			],
			'gd' => [
				'label'   => $engine_labels['gd'],
				'status'  => $gd_available ? 'ok' : 'error',
				'message' => $gd_available ? __( 'Available', 'image-watermark' ) : __( 'Not available', 'image-watermark' ),
			],
			'imagick' => [
				'label'   => $engine_labels['imagick'],
				'status'  => $imagick_available ? 'ok' : 'error',
				'message' => $imagick_available ? __( 'Available', 'image-watermark' ) : __( 'Not available', 'image-watermark' ),
			],
			'active' => [
				'label'   => __( 'Active engine', 'image-watermark' ),
				'status'  => $active_engine ? 'ok' : 'error',
				'message' => $active_engine && isset( $engine_labels[$active_engine] )
					? $engine_labels[$active_engine]
					: __( 'None selected', 'image-watermark' ),
			],
		];

		// Backup status
		$backup_message = '';
		$backup_status = 'error';

		if ( $backup_dir ) {
			if ( $backup_exists && $backup_writable ) {
				$backup_status = 'ok';
				$backup_message = __( 'Ready and writable.', 'image-watermark' );
			} elseif ( ! $backup_enabled ) {
				$backup_status = 'info';
				$backup_message = __( 'Backups disabled.', 'image-watermark' );
			} elseif ( ! $backup_exists ) {
				$backup_message = __( 'Not created.', 'image-watermark' );
			} else {
				$backup_message = __( 'Exists but not writable.', 'image-watermark' );
			}
		} else {
			$backup_status = 'info';
			$backup_message = __( 'Path not defined.', 'image-watermark' );
		}

		$statuses['backup'] = [
			'label'   => __( 'Backup folder', 'image-watermark' ),
			'status'  => $backup_status,
			'message' => $backup_message,
		];
		?>
		<ul class="iw-status-list">
			<?php foreach ( $statuses as $status ) : ?>
				<li class="iw-status-item">
					<span class="iw-status-dot <?php echo esc_attr( $status['status'] ); ?>"></span>
					<span class="iw-status-text">
						<strong><?php echo esc_html( $status['label'] ); ?>:</strong> <?php echo wp_kses_post( $status['message'] ); ?>
					</span>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}

	/**
	 * Render Backup Folder field.
	 */
	public function render_backup_folder( $args ) {
		$backup_dir = defined( 'IMAGE_WATERMARK_BACKUP_DIR' ) ? IMAGE_WATERMARK_BACKUP_DIR : '';
		?>
		<code><?php echo esc_html( $backup_dir ? $backup_dir : __( 'Not defined', 'image-watermark' ) ); ?></code>
		<?php
	}

	/**
	 * Render Watermark Preview.
	 */
	public function render_preview( $args ) {
		$base_id = ! empty( $args['html_id'] ) ? $args['html_id'] : 'iw-watermark-preview';
		$stage_id = $base_id . '-stage';
		$placeholder_id = $base_id . '-placeholder';
		$image_id = $base_id . '-image';
		$text_id = $base_id . '-text';
		$origin_label_id = $base_id . '-origin-label';
		$origin_size_id = $base_id . '-origin-size';
		$options = $this->plugin->options;
		$type = $options['watermark_image']['type'];
		$watermark_id = isset( $options['watermark_image']['url'] ) ? (int) $options['watermark_image']['url'] : 0;
		$image_data = $watermark_id ? wp_get_attachment_image_src( $watermark_id, 'full', false ) : false;
		$image_url = $image_data ? $image_data[0] : '';
		$image_width = $image_data ? (int) $image_data[1] : 0;
		$image_height = $image_data ? (int) $image_data[2] : 0;

		$text = isset( $options['watermark_image']['text_string'] ) ? $options['watermark_image']['text_string'] : '';
		$font = isset( $options['watermark_image']['text_font'] ) ? $options['watermark_image']['text_font'] : 'Lato-Regular.ttf';
		$text_size = isset( $options['watermark_image']['text_size'] ) ? (int) $options['watermark_image']['text_size'] : 20;
		$text_color = isset( $options['watermark_image']['text_color'] ) ? $options['watermark_image']['text_color'] : '#ffffff';
		?>
		<div id="<?php echo esc_attr( $base_id ); ?>">
			<div id="<?php echo esc_attr( $stage_id ); ?>" data-stage-width="600" data-stage-height="400">
				<div class="iw-preview-stage-inner">
					<div id="<?php echo esc_attr( $placeholder_id ); ?>" class="iw-preview-placeholder"<?php echo ( $type === 'image' && $image_url ) ? ' style="display: none;"' : ''; ?>>
								<?php echo ( $type === 'text' ) ? esc_html__( 'Enter watermark text to preview.', 'image-watermark' ) : esc_html__( 'No watermark image has been selected yet.', 'image-watermark' ); ?>
					</div>
					<img id="<?php echo esc_attr( $image_id ); ?>" class="iw-preview-watermark" src="<?php echo esc_url( $image_url ); ?>" data-natural-width="<?php echo esc_attr( $image_width ); ?>" data-natural-height="<?php echo esc_attr( $image_height ); ?>" alt="<?php esc_attr_e( 'Watermark image preview', 'image-watermark' ); ?>" />
					<div id="<?php echo esc_attr( $text_id ); ?>" class="iw-preview-watermark iw-preview-watermark-text" data-font="<?php echo esc_attr( $font ); ?>" data-size="<?php echo esc_attr( $text_size ); ?>" data-color="<?php echo esc_attr( $text_color ); ?>"><?php echo esc_html( $text ); ?></div>
				</div>
			</div>
			<p class="iw-preview-origin">
				<span id="<?php echo esc_attr( $origin_label_id ); ?>"><?php echo ( $type === 'text' ) ? esc_html__( 'Original text size:', 'image-watermark' ) : esc_html__( 'Original watermark image:', 'image-watermark' ); ?></span>
				<span id="<?php echo esc_attr( $origin_size_id ); ?>">
					<?php
					if ( $type === 'image' ) {
						echo ( $image_width && $image_height )
							? esc_html( $image_width . ' x ' . $image_height . ' px' )
							: esc_html__( 'Not available.', 'image-watermark' );
					} else {
						echo esc_html__( 'Will update as you type.', 'image-watermark' );
					}
					?>
				</span>
			</p>
		</div>
		<?php
	}

	/**
	 * Render Watermark Image Selection.
	 */
	public function render_watermark_image( $args ) {
		$base_id = ! empty( $args['html_id'] ) ? $args['html_id'] : 'iw-watermark-image-ui';
		$input_id = $base_id . '-input';
		$select_id = $base_id . '-select';
		$remove_id = $base_id . '-remove';
		$options = $this->plugin->options;

		if ( $options['watermark_image']['url'] !== null && $options['watermark_image']['url'] != 0 ) {
			$image = wp_get_attachment_image_src( $options['watermark_image']['url'], [ 300, 300 ], false );
			$image_selected = true;
		} else {
			$image_selected = false;
		}
		?>

		<input id="<?php echo esc_attr( $input_id ); ?>" type="hidden" name="<?php echo esc_attr( $args['name'] ); ?>" value="<?php echo (int) $options['watermark_image']['url']; ?>" />

		<div id="<?php echo esc_attr( $base_id ); ?>" class="iw-field-group iw-image-ui iw-buttons-group horizontal">
			<input id="<?php echo esc_attr( $select_id ); ?>" type="button" class="button outline" value="<?php echo esc_attr__( 'Select image', 'image-watermark' ); ?>" />
			<input id="<?php echo esc_attr( $remove_id ); ?>" type="button" class="button outline" value="<?php echo esc_attr__( 'Remove image', 'image-watermark' ); ?>" <?php if ( $image_selected === false ) echo 'disabled="disabled"'; ?>/>
		</div>
		<?php
	}


	/**
	 * Render Watermark Custom Size.
	 */
	public function render_watermark_size_custom( $args ) {
		$base_id = ! empty( $args['html_id'] ) ? $args['html_id'] : 'iw-watermark-size-custom';
		$width_id = $base_id . '-width';
		$height_id = $base_id . '-height';
		?>
		<div class="iw-field-group iw-size-custom-group">
			<label>
				<span><?php esc_html_e( 'x:', 'image-watermark' ); ?></span> <input id="<?php echo esc_attr( $width_id ); ?>" type="text" size="5" name="<?php echo esc_attr( $args['callback_args']['width']['name'] ); ?>" value="<?php echo esc_attr( $args['callback_args']['width']['value'] ); ?>"> <span><?php esc_html_e( 'px', 'image-watermark' ); ?></span>
			</label>
			<label>
				<span><?php esc_html_e( 'y:', 'image-watermark' ); ?></span> <input id="<?php echo esc_attr( $height_id ); ?>" type="text" size="5" name="<?php echo esc_attr( $args['callback_args']['height']['name'] ); ?>" value="<?php echo esc_attr( $args['callback_args']['height']['value'] ); ?>"> <span><?php esc_html_e( 'px', 'image-watermark' ); ?></span>
			</label>
		</div>
		<?php
	}











}
