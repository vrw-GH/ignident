<?php
/**
 * Search form as Avia Layout Builder element
 *
 * Displays a search field that stretches across the available space
 * Option: select the post types that should be included in the search
 * Option: enable/disable ajax
 * Option: Display results either on a separate page (classic search page) or on the same page below the search form
 *
 *
 * @author tinabillinger
 * @since 4.4
 * @since 4.8.7		modified by Guenter
 */

// Don't load directly
if( ! defined( 'ABSPATH' ) ) {  exit;  }


if( ! class_exists( 'avia_sc_search', false ) )
{
	class avia_sc_search extends aviaShortcodeTemplate
	{

		/**
		 * Create the config array for the shortcode button
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['self_closing']	= 'yes';

			$this->config['name']			= __( 'Search', 'avia_framework' );
			$this->config['tab']			= __( 'Content Elements', 'avia_framework');
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-search.png';
			$this->config['order']			= 10;
			$this->config['shortcode']		= 'avia_sc_search';
			$this->config['tooltip']		= __( 'Displays a search form', 'avia_framework' );
			$this->config['target']			= 'avia-target-insert';
			$this->config['tinyMCE']		= array( 'disable' => true) ;
			$this->config['preview']		= 1;
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
		}

		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_css = avia_minify_extension( 'css' );

			wp_enqueue_style( 'avia-sc-search', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/search/search{$min_css}.css", array( 'avia-layout' ), $ver );
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
													$this->popup_key( 'content_form' ),
													$this->popup_key( 'content_filter' ),
													$this->popup_key( 'content_result' )
												),
							'nodescription' => true
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Layout', 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'type'			=> 'template',
							'template_id'	=> $this->popup_key( 'layout_result' )
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
													$this->popup_key( 'styling_fonts' ),
													$this->popup_key( 'styling_form' ),
													$this->popup_key( 'styling_colors_form' ),
													$this->popup_key( 'styling_result' ),
													$this->popup_key( 'styling_colors_result' )
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
								'template_id'	=> 'screen_options_toggle'
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

			// assemble available post types
            $pt_args = array(
							'public'				=> true,
							'exclude_from_search'	=> false,
						);

            $pt_list = get_post_types( $pt_args, 'objects' );
            $select_list = array();

            if( ! empty( $pt_list ) )
			{
                foreach( $pt_list as $pk => $pt )
				{
                    $exclude = array( 'avia_framework_post', 'attachment', 'tribe-ea-record' );
                    if( ! in_array( $pk, $exclude ) )
					{
                        $select_list[ $pt->labels->name ] = $pk;
                    }
                }
            }


			/**
			 * Content Tab
			 * ===========
			 */

			$c = array(
						 array(
							'name' 	=> __( 'Placeholder', 'avia_framework' ),
							'desc' 	=> __( 'Enter a placeholder text for the input field', 'avia_framework' ) ,
							'id' 	=> 'placeholder',
							'std' 	=> __( 'Search the site ...', 'avia_framework' ),
							'type' 	=> 'input'
						),

						array(
							'name' 	=> __( 'Label Text', 'avia_framework' ),
							'desc' 	=> __( 'Enter a label text for the button', 'avia_framework' ) ,
							'id' 	=> 'label_text',
							'std' 	=> __( 'Find', 'avia_framework' ),
							'type' 	=> 'input',
						),

						array(
							'name' => __( 'Icon Display', 'avia_framework'),
							'desc' => __( 'Where should the icon be displayed?', 'avia_framework' ),
							'id' => 'icon_display',
							'type' => 'select',
							'std' => '',
							'subtype'	=> array(
												__( 'No Icon', 'avia_framework' )		=> '',
												__( 'Input Field', 'avia_framework' )	=> 'input',
												__( 'Button', 'avia_framework' )		=> 'button',
											)
						),

						array(
							'name'  => __( 'Search Icon', 'avia_framework' ),
							'desc'  => __( 'Select an Icon below', 'avia_framework' ),
							'id'    => 'icon',
							'type'  => 'iconfont',
							'required'	=> array( 'icon_display', 'not', '' )
						),

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Search Form', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_form' ), $template );

			$desc  = __( 'Select post types to be included in the search.', 'avia_framework' ) . '<br /><br />';
			$desc .= __( 'Some plugins (e.g. WooCommerce) do not support multiple post types when redirecting result to Search Result page.', 'avia_framework' );

			$c = array(
						array(
							'name'	=> __( 'Post Types', 'avia_framework' ),
							'desc'	=> __( 'Which post types should be included in the search', 'avia_framework' ),
							'id'	=> 'post_types',
							'type'	=> 'select',
							'std'	=> '',
							'subtype'	=> array(
												__( 'All', 'avia_framework' )		=> '',
												__( 'Custom', 'avia_framework' )	=> 'custom',
											)
						),

						array(
							'name'	=> __( 'Post Types', 'avia_framework'),
							'desc'	=> $desc,
							'id'	=> 'post_types_custom',
							'type'	=> 'select',
							'std'	=> '',
							'multiple'	=> 5,
							'required'	=> array( 'post_types', 'equals', 'custom' ),
							'subtype'	=> $select_list
						),

						array(
							'name'	=> __( 'Number of results', 'avia_framework'),
							'desc'	=> __( 'How many results should be loaded via AJAX', 'avia_framework' ),
							'id'	=> 'numberposts',
							'type'	=> 'select',
							'std'	=> 5,
							'subtype'	=> AviaHtmlHelper::number_array( 1, 40, 1 ),
						),
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Filter', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_filter' ), $template );

			/**
			 * Layout Tab
			 * ===========
			 */

			$c = array(
						array(
							'name'		=> __( 'How to display the search results?', 'avia_framework' ),
							'desc'		=> __( 'Select to use AJAX callback or redirect to the default search results page', 'avia_framework' ),
							'id'		=> 'display',
							'type'		=> 'select',
							'std'		=> 'ajax',
							'subtype'	=> array(
												__( 'Display search results on the same page (AJAX)', 'avia_framework' )	=> 'ajax',
												__( 'Redirect to the Search Results page', 'avia_framework' )				=> 'classic',
											)
						),

						array(
							'name'		=> __( 'Where to display the search results?', 'avia_framework' ),
							'desc'		=> __( 'Define where the results are displayed when using AJAX', 'avia_framework' ),
							'id'		=> 'ajax_location',
							'type'		=> 'select',
							'std'		=> 'form',
							'required'	=> array( 'display', 'equals', 'ajax' ),
							'subtype'	=> array(
												__( 'Under the search form - push down other content', 'avia_framework' )	=> 'form',
												__( 'Under the search form - overlay other content', 'avia_framework' )		=> 'form_absolute',
												__( 'Display search results in a custom container', 'avia_framework' )		=> 'custom',
											)
						),

						array(
							'name'		=> __( 'Search results container', 'avia_framework' ),
							'desc'		=> __( 'Enter the ID of a container that will hold the search results.<br/>It has to be on the same page as this search form.', 'avia_framework' ),
							'id'		=> 'ajax_container',
							'std'		=> __( '#my_container', 'avia_framework' ),
							'type'		=> 'input',
							'required'	=> array( 'ajax_location', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Hide post type titles', 'avia_framework' ),
							'desc'		=> __( 'Check if you want to hide the post type titles in ajax search results', 'avia_framework' ) ,
							'id'		=> 'results_hide_titles',
							'type'		=> 'checkbox',
							'std'		=> '',
							'required'	=> array( 'display', 'equals', 'ajax' ),
						),

						array(
							'name'		=> __( 'Hide meta data', 'avia_framework' ),
							'desc'		=> __( 'Check if you want to hide the excerpt and date in ajax search results', 'avia_framework' ) ,
							'id'		=> 'results_hide_meta',
							'type'		=> 'checkbox',
							'std'		=> '',
							'required'	=> array( 'display', 'equals', 'ajax' ),
						),

						array(
							'name'		=> __( 'Hide preview image or icon', 'avia_framework' ),
							'desc'		=> __( 'Check if you want to hide the preview image or icon in ajax search results', 'avia_framework' ) ,
							'id'		=> 'results_hide_image',
							'type'		=> 'checkbox',
							'std'		=> '',
							'required'	=> array( 'display', 'equals', 'ajax' ),
						)

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'layout_result' ), $c );

			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name' 	=> __( 'Input Font Size', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font size for the input. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_input_size',
							'type' 	=> 'select',
							'std' 	=> '',
							'container_class' => 'av_half av_half_first',
							'subtype'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Default Size', 'avia_framework' ) => '' ), 'px' ),
						),

						array(
							'name' 	=> __( 'Button Font Size', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font size for the button. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_button_size',
							'type' 	=> 'select',
							'std' 	=> '',
							'container_class' => 'av_half',
							'subtype'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Default Size', 'avia_framework' ) => '' ), 'px' ),
						),

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Fonts', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_fonts' ), $template );

			$c = array(

						array(
							'name' 	=> __( 'Height', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom height for the search input and button', 'avia_framework' ),
							'id' 	=> 'custom_height',
							'type' 	=> 'select',
							'std' 	=> '',
							'subtype' => AviaHtmlHelper::number_array( 20, 100, 1 , array( __( 'Default Height', 'avia_framework' ) => '' ), 'px' ),
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'border_radius',
							'id'			=> 'radius',
							'name'			=> __( 'Inputfield And Button Border Radius', 'avia_framework' ),
							'desc'			=> __( 'Set the border radius of the input field and the button', 'avia_framework' )
						),

						array(
							'name' 	=> __( 'Border Width', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom border width for the search input and button', 'avia_framework' ),
							'id' 	=> 'border_width',
							'type' 	=> 'select',
							'std' 	=> '',
							'subtype' => AviaHtmlHelper::number_array( 0, 30, 1, array( __( 'Default Width', 'avia_framework' ) => '' ), 'px' ),
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Form Container', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_form' ), $template );



			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'margin_padding',
							'desc'			=> __( 'Set the spacing for the search results container', 'avia_framework' ),
							'name_margin'	=> __( 'Search Results Container Margin', 'avia_framework' ),
							'desc_margin'	=> __( 'Set the margin for the search results container. Valid CSS units are accepted, eg: 30px, 5&percnt;. px is used as default unit.', 'avia_framework' ),
							'name_padding'	=> __( 'Search Results Container Padding', 'avia_framework' ),
							'desc_padding'	=> __( 'Set the padding for the search results container. Valid CSS units are accepted, eg: 30px, 5&percnt;. px is used as default unit.', 'avia_framework' ),
							'id_margin'		=> 'results_margin',
							'id_padding'	=> 'results_padding',
							'lockable'		=> true
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Result Container', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_result' ), $template );


			$c = array(
						array(
							'name' 	=> __( 'Border Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a border color for the input and button', 'avia_framework' ),
							'id' 	=> 'border_color',
							'type' 	=> 'select',
							'std' 	=> '',
							'subtype' => array(
												__( 'Default', 'avia_framework' )				=> '',
												__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
											),
						),

						array(
							'name' 	=> __( 'Custom Border Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom border color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_border_color',
							'type' 	=> 'colorpicker',
							'rgba'  => true,
							'std' 	=> '',
							'required'	=> array( 'border_color', 'equals', 'custom' )
						),

						array(
							'name' 	=> __( 'Input Font Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a font color for the input', 'avia_framework' ),
							'id' 	=> 'input_color',
							'type' 	=> 'select',
							'std' 	=> '',
							'subtype'	=> array(
												__( 'Default', 'avia_framework' )				=> '',
												__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
											),
						),

						array(
							'name' 	=> __( 'Custom Input Font Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_input_color',
							'type' 	=> 'colorpicker',
							'rgba'  => true,
							'std' 	=> '',
							'required'	=> array( 'input_color', 'equals', 'custom' )
						),

						array(
							'name' 	=> __( 'Input Background Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a background color for the input', 'avia_framework' ),
							'id' 	=> 'input_bg',
							'type' 	=> 'select',
							'std' 	=> '',
							'subtype'	=> array(
												__( 'Default', 'avia_framework' )				=> '',
												__( 'Define Custom Colors', 'avia_framework'	)=> 'custom'
											),
						),

						array(
							'name' 	=> __( 'Custom Input Background Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom background color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_input_bg',
							'type' 	=> 'colorpicker',
							'rgba'  => true,
							'std' 	=> '',
							'required' => array( 'input_bg', 'equals', 'custom' )
						),

						array(
							'name' 	=> __( 'Button Font/Icon Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a font or icon color for the button', 'avia_framework' ),
							'id' 	=> 'button_color',
							'type' 	=> 'select',
							'std' 	=> '',
							'subtype'	=> array(
												__( 'Default', 'avia_framework' )				=> '',
												__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
											),
						),

						array(
							'name' 	=> __( 'Custom Button Font/Icon Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_button_color',
							'type' 	=> 'colorpicker',
							'rgba'  => true,
							'std' 	=> '',
							'required'	=> array( 'button_color', 'equals', 'custom' )
						),

						array(
							'name' 	=> __( 'Button Background Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a background color for the button', 'avia_framework' ),
							'id' 	=> 'button_bg',
							'type' 	=> 'select',
							'std' 	=> '',
							'subtype'	=> array(
												__( 'Default', 'avia_framework' )				=> '',
												__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
											),
						),

						array(
							'name' 	=> __( 'Custom Button Background Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom background color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_button_bg',
							'type' 	=> 'colorpicker',
							'rgba'  => true,
							'std' 	=> '',
							'required'	=> array( 'button_bg', 'equals', 'custom' )
						),
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Form Colors', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_colors_form' ), $template );

			$c = array(
						array(
							'name'		=> __( 'Search Results Font Colors', 'avia_framework' ),
							'desc'		=> __( 'Select font colors for the search results container', 'avia_framework' ),
							'id'		=> 'results_color',
							'type'		=> 'select',
							'std'		=> '',
							'required'	=> array( 'display', 'equals', 'ajax' ),
							'subtype'	=> array(
												__( 'Default', 'avia_framework' )				=> '',
												__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
											),
						),

						array(
							'name'		=> __( 'Search Results Title Font Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom results color for the title font. Leave empty to use the default', 'avia_framework' ),
							'id'		=> 'custom_results_color',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'required' => array( 'results_color', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Search Results Content Font Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom results color for the content font. Leave empty to use the default', 'avia_framework' ),
							'id'		=> 'custom_results_content',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'required' => array( 'results_color', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Search Results Icon Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom results color for the icon. Leave empty to use the default', 'avia_framework' ),
							'id'		=> 'custom_results_icon',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'required' => array( 'results_color', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Search Results Background Color', 'avia_framework' ),
							'desc'		=> __( 'Select a background color for the search results container', 'avia_framework' ),
							'id'		=> 'results_bg',
							'type'		=> 'select',
							'std'		=> '',
							'required'	=> array( 'display', 'equals', 'ajax' ),
							'subtype'	=> array(
												__( 'Default', 'avia_framework' )				=> '',
												__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
											),
						),

						array(
							'name'		=> __( 'Custom Search Results Background Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom background color. Leave empty to use the default', 'avia_framework' ),
							'id'		=> 'custom_results_bg',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'required'	=> array( 'results_bg', 'equals', 'custom' )
						),

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Result Colors', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_colors_result' ), $template );

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
			extract( av_backend_icon( $params ) ); // creates $font and $display_char if the icon was passed as param 'icon' and the font as 'font'

			$template  = $this->update_template( 'placeholder', '{{placeholder}}' );

			$params['content'] = null;

			$params['innerHtml']  = "<div class='avia_title_container'>";
			$params['innerHtml'] .= "<span class='avia-element-label avia-sc-search-fakeinput' {$template}>{$params['args']['placeholder']}</span>";

			if( ( isset( $params['args']['label'] ) ) && ( $params['args']['label'] == 'text') )
			{
				$template2  = $this->update_template( 'label_text', '{{label_text}}' );
				$params['innerHtml'] .= "<span class='button' {$template2}>{$params['args']['label_text']}</span>";
			}
			else {
				$params['innerHtml'] .= '<span ' . $this->class_by_arguments( 'font', $font ) . '>';
				$params['innerHtml'] .=		"<span class='button avia_icon_char' data-update_with='icon_fakeArg' >{$display_char}</span>";
				$params['innerHtml'] .= '</span>';
			}

			$params['innerHtml'] .= '</div>';

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
						'placeholder'			=> '',
						'label_text'			=> '',
						'font'					=> '',
						'icon_display'			=> '',
						'icon'					=> '',
						'post_types'			=> '',
						'post_types_custom'		=> '',
						'display'				=> '',
						'ajax_location'			=> '',
						'ajax_container'		=> '',
						'numberposts'			=> 5,
						'border_color'			=> '',
						'custom_border_color'	=> '',
						'input_bg'				=> '',
						'custom_input_bg'		=> '',
						'button_bg'				=> '',
						'custom_button_bg'		=> '',
						'results_bg'			=> '',
						'custom_results_bg'		=> '',
						'input_color'			=> '',
						'custom_input_color'	=> '',
						'button_color'			=> '',
						'custom_button_color'	=> '',
						'custom_results_content' => '',
						'custom_results_icon'	=> '',
						'results_color'			=> '',
						'custom_results_color'	=> '',
						'custom_input_size'		=> '',
						'custom_button_size'	=> '',
						'custom_height'			=> '',
						'radius'				=> '',
						'border_width'			=> '',
						'results_hide_titles'	=> '',
						'results_hide_meta'		=> '',
						'results_hide_image'	=> '',
						'results_padding'		=> '',
						'results_margin'		=> ''
					);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			$element_styling->create_callback_styles( $atts );

			$classes = array(
						'avia_search_element',
						$element_id
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $meta, 'el_class' );
			$element_styling->add_responsive_classes( 'container', 'hide_element', $atts );

			 // results location
            if( 'classic' == $atts['display'] )
			{
				$element_styling->add_classes( 'form', 'av_disable_ajax_search' );
            }
            else
			{
                if( 'custom' == $atts['ajax_location'] && $atts['ajax_container'] !== '' )
				{
                    $results_container_attr = " data-ajaxcontainer='{$ajax_container}'";
                }

                if( 'form_absolute' == $atts['ajax_location'] )
				{
					$element_styling->add_classes( 'form', 'av_results_container_fixed' );
                }
            }

			//	Form Toggles
			if( ! empty( $atts['custom_height'] ) )
			{
				$element_styling->add_styles( 'input', array(
												'line-height'	=> $atts['custom_height'] . 'px',
												'height'		=> $atts['custom_height'] . 'px'
											));
			}

			$element_styling->add_callback_styles( 'input', array( 'radius' ) );
			$element_styling->add_callback_styles( 'button-wrapper', array( 'radius' ) );
			$element_styling->add_callback_styles( 'button', array( 'radius' ) );
			$element_styling->add_callback_styles( 'form-wrapper', array( 'radius' ) );

			if( $atts['border_width'] !== '' )
			{
				$element_styling->add_styles( 'form-wrapper', array( 'border-width' => $atts['border_width'] . 'px' ) );
            }


			//	Form Colors Toggle
			if( 'custom' == $atts['border_color'] && ! empty( $atts['custom_border_color'] ) )
			{
				$element_styling->add_styles( 'form-wrapper', array(
												'border-color'		=> $atts['custom_border_color'],
												'background-color'	=> $atts['custom_border_color']
											));
			}

			if( 'custom' == $atts['input_color'] && ! empty( $atts['custom_input_color'] ) )
			{
				$element_styling->add_styles( 'input', array( 'color' => $atts['custom_input_color'] ) );
				$element_styling->add_styles( 'input-icon', array( 'color' => $atts['custom_input_color'] ) );
			}

			if( 'custom' == $atts['input_bg'] && ! empty( $atts['custom_input_bg'] ) )
			{
				$element_styling->add_styles( 'input', array( 'background-color' => $atts['custom_input_bg'] ) );
			}

			if( 'custom' == $atts['button_color'] && ! empty( $atts['custom_button_color'] ) )
			{
				$element_styling->add_styles( 'button', array( 'color' => $atts['custom_button_color'] ) );
				$element_styling->add_styles( 'button-wrapper', array( 'color' => $atts['custom_button_color'] ) );
			}

			if( 'custom' == $atts['button_bg'] && ! empty( $atts['custom_button_bg'] ) )
			{
				$element_styling->add_styles( 'button', array( 'background-color' => $atts['custom_button_bg'] ) );
				$element_styling->add_styles( 'button-wrapper', array( 'background-color' => $atts['custom_button_bg'] ) );
			}

			//	Font Toggle
			if( ! empty( $atts['custom_input_size'] ) )
			{
				$element_styling->add_styles( 'input', array( 'font-size' => $atts['custom_input_size'] . 'px' ) );
				$element_styling->add_styles( 'input-icon', array( 'font-size' => $atts['custom_input_size'] . 'px' ) );
			}

			if( ! empty( $atts['custom_button_size'] ) )
			{
				$element_styling->add_styles( 'button', array( 'font-size' => $atts['custom_button_size'] . 'px' ) );
				$element_styling->add_styles( 'button-icon', array( 'font-size' => $atts['custom_button_size'] . 'px' ) );
			}


			//	Results Toggle
			$element_styling->add_responsive_styles( 'results-container', 'results_padding', $atts, $this );
			$element_styling->add_responsive_styles( 'results-container', 'results_margin', $atts, $this );

			//	Result cOLORS Toggle
			if( 'custom' == $atts['results_bg'] && ! empty( $atts['custom_results_bg'] ) )
			{
				$element_styling->add_styles( 'results-container', array( 'background-color' => $atts['custom_results_bg'] ) );
			}

			if( 'custom' == $atts['results_color'] )
			{

				if( ! empty( $atts['custom_results_color'] ) )
				{
					$element_styling->add_styles( 'results-cont-title', array( 'color' => $atts['custom_results_color'] ) );
				}

				if( ! empty( $atts['custom_results_content'] ) )
				{
					$element_styling->add_styles( 'results-cont-excerpt', array( 'color' => $atts['custom_results_content'] ) );
				}

				if( ! empty( $atts['custom_results_icon'] ) )
				{
					$element_styling->add_styles( 'results-cont-icon', array( 'color' => $atts['custom_results_icon'] ) );
				}
			}


			//	#top needed due to settings in search.css
			$selectors = array(
						'container'				=> "#top .avia_search_element.{$element_id}",
						'form'					=> "#top .avia_search_element.{$element_id} form",
						'form-wrapper'			=> "#top .avia_search_element.{$element_id} .av_searchform_wrapper",
						'input'					=> "#top .avia_search_element.{$element_id} #s.av-input-field",
						'input-icon'			=> "#top .avia_search_element.{$element_id} .av-input-field-icon.av-search-icon",
						'button-wrapper'		=> "#top .avia_search_element.{$element_id} .av_searchsubmit_wrapper",
						'button-icon'			=> "#top .avia_search_element.{$element_id} .av-button-icon.av-search-icon",
						'button'				=> "#top .avia_search_element.{$element_id} #searchsubmit",

						'results-container'		=> ".ajax_search_response.{$element_id}",
						'results-cont-title'	=> ".ajax_search_response.{$element_id} .av_ajax_search_title",
						'results-cont-excerpt'	=> ".ajax_search_response.{$element_id} .ajax_search_excerpt",
						'results-cont-icon'		=> ".ajax_search_response.{$element_id} .av_ajax_search_image",
					);

			$element_styling->add_selectors( $selectors );


			$result['default'] = $default;
			$result['atts'] = $atts;
//			$result['content'] = $content;
			$result['element_styling'] = $element_styling;

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

			//	render element id to response container for styling
			$results_container_data = array( 'element_id' => $element_id );


			//	results location
			if( 'ajax' == $display && $ajax_location == 'custom' && ! empty( $ajax_container ) )
			{
				$results_container_data['ajaxcontainer'] = $ajax_container;
			}

			//	inform js about custom font color (to set a specific class to use inherit)
			if( 'custom' == $atts['results_color'] && ! empty( $atts['custom_results_color'] ) )
			{
				$results_container_data['custom_color'] = $atts['custom_results_color'];
			}


			$input_icon = false;
			$submit_icon = false;
			$spacer_img = '';

			$button_val = $label_text;
			$icon = av_icon( $icon, $font, false );


			if( 'button' == $icon_display && $icon != '' )
			{
				if( $label_text == '' )
				{
					// submit button with icon only
					$button_val = $icon;
					$spacer_img = '<img src="' . get_template_directory_uri() . '/images/layout/blank.png" />';

					$element_styling->add_classes( 'button', 'av-submit-hasicon avia-font-' . $font );
					$element_styling->add_classes( 'button-wrapper', 'av-submit-hasicon' );
				}
				else
				{
					// submit button with label and icon
					$submit_icon = true;
					$element_styling->add_classes( 'button-wrapper', 'av-submit-hasiconlabel' );
				}

			}
			else if( 'input' == $icon_display && $icon !== '' )
			{
				$input_icon = true;
				$element_styling->add_classes( 'input', 'av-input-hasicon' );
			}


			 // search params
			$form_action = home_url( '/' );
			$search_id = 's';
			$search_val = ! empty( $_GET['s'] ) ? get_search_query() : '';


			$results_container_data = AviaHelper::create_data_string( $results_container_data );

			$hidden_post_types_input = '';
			if( $post_types == 'custom' )
			{
				if( $post_types_custom )
				{
					$post_types_custom = explode( ',', $post_types_custom );

					foreach( $post_types_custom as $ptc )
					{
						$hidden_post_types_input .=	"<input type='hidden' name='post_type[]' value='{$ptc}' />";
					}

					/**
					 * Fixes a bug in plugins like WooCommerce that do not support post_type[] in search
					 *
					 * https://kriesi.at/support/topic/page-search-on-tags/#post-1334958
					 */
					if( count( $post_types_custom ) == 1 )
					{
						$hidden_post_types_input = str_replace( '[]', '', $hidden_post_types_input );
					}
				}
			}

			 // results display options
			$results_hide_fields = array();

			if( $results_hide_titles )
			{
				$results_hide_fields[] = 'post_titles';
			}

			if( $results_hide_meta )
			{
				$results_hide_fields[] = 'meta';
			}

			if( $results_hide_image )
			{
				$results_hide_fields[] = 'image';
			}

			$results_hide_value = ! empty( $results_hide_fields ) ? implode( ',', $results_hide_fields ) : '';

			$title = esc_attr( __( 'View results on search page', 'avia_framework' ) );


			$style_tag = $element_styling->get_style_tag( $element_id );
			$container_class = $element_styling->get_class_string( 'container' );
			$form_class = $element_styling->get_class_string( 'form' );
			$input_class = $element_styling->get_class_string( 'input' );
			$button_wrapper_class = $element_styling->get_class_string( 'button-wrapper' );
			$button_class = $element_styling->get_class_string( 'button' );

			$output  = '';
			$output .= $style_tag;
			$output .= "<div {$meta['custom_el_id']} class='{$container_class}'>";
			$output .=		'<search>';
			$output .=			"<form action='{$form_action}' id='searchform_element' method='get' class='{$form_class}'{$results_container_data}>";
			$output .=				"<div class='av_searchform_wrapper'>";
			$output .=					"<input type='search' value='{$search_val}' id='s' name='{$search_id}' placeholder='{$placeholder}' aria-label='{$placeholder}' class='av-input-field {$input_class}' required />";

			if( $input_icon )
			{
				$output .=				"<span class='av-input-field-icon av-search-icon avia-font-{$font}'>{$icon}</span>";
			}

			$output .=					"<div class='av_searchsubmit_wrapper {$button_wrapper_class}'>";

			if( $submit_icon )
			{
				$output .=				"<span class='av-button-icon av-search-icon avia-font-{$font}'>{$icon}</span>";
			}

			$output .=					"<input type='submit' value='{$button_val}' id='searchsubmit' class='button {$button_class}' title='{$title}' aria-label='{$title}' />";

			// layout helper IE
			$output .= $spacer_img;

			$output .=					'</div>';
			$output .=					"<input type='hidden' name='numberposts' value='{$numberposts}' />";
			$output .=					$hidden_post_types_input;
			$output .=					"<input type='hidden' name='results_hide_fields' value='{$results_hide_value}' />";
			$output .=				'</div>';
			$output .=			'</form>';
			$output .=		'</search>';
			$output .= '</div>';

			return $output;

		}

	}
}
