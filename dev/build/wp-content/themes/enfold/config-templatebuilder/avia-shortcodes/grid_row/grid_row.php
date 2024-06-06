<?php
/**
 * Grid Row
 *
 * Shortcode which adds multiple Grid Rows below each other to create advanced grid layouts. Cells can be styled individually
 */

 // Don't load directly
if( ! defined( 'ABSPATH' ) ) { die( '-1' ); }


if( ! class_exists( 'avia_sc_grid_row', false ) )
{
	include_once( 'cell.php' );

	class avia_sc_grid_row extends aviaShortcodeTemplate
	{

		/**
		 *
		 * @var int
		 */
		static $count = 0;

		/**
		 * Create the config array for the shortcode grid row
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']			= '1.0';
			$this->config['is_fullwidth']		= 'yes';
//			$this->config['base_element']		= 'yes';
			$this->config['type']				= 'layout';
			$this->config['self_closing']		= 'no';
			$this->config['contains_text']		= 'no';
			$this->config['layout_children']	= array(
														'av_cell_one_full',
														'av_cell_one_half',
														'av_cell_one_third',
														'av_cell_one_fourth',
														'av_cell_one_fifth',
														'av_cell_two_third',
														'av_cell_three_fourth',
														'av_cell_two_fifth',
														'av_cell_three_fifth',
														'av_cell_four_fifth'
													);


			$this->config['name']				= __( 'Grid Row', 'avia_framework' );
			$this->config['icon']				= AviaBuilder::$path['imagesURL'] . 'sc-layout_row.png';
			$this->config['tab']				= __( 'Layout Elements', 'avia_framework' );
			$this->config['order']				= 15;
			$this->config['shortcode']			= 'av_layout_row';
			$this->config['html_renderer']		= false;
			$this->config['tinyMCE']			= array( 'disable' => 'true' );
			$this->config['tooltip']			= __( 'Add multiple Grid Rows below each other to create advanced grid layouts. Cells can be styled individually', 'avia_framework' );
			$this->config['drag-level']			= 1;
			$this->config['drop-level']			= 100;
			$this->config['disabling_allowed']	= false;

			$this->config['id_name']			= 'id';
			$this->config['id_show']			= 'always';				//	we use original code - not $meta
			$this->config['aria_label']			= 'yes';
		}

		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_css = avia_minify_extension( 'css' );

			//load css
			wp_enqueue_style( 'avia-module-gridrow', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/grid_row/grid_row{$min_css}.css", array( 'avia-layout' ), $ver );
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
						'name'  => __( 'Layout', 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'type'			=> 'template',
							'template_id'	=> 'toggle_container',
							'templates_include'	=> array(
													$this->popup_key( 'layout_height' ),
													$this->popup_key( 'layout_borders' ),
													'fold_unfold_container_toggle'
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
													$this->popup_key( 'styling_colors' ),
													'fold_styling_toggle'
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
								'template_id'	=> 'fold_animation_toggle',
								'lockable'		=> true
							),

						array(
								'type'				=> 'template',
								'template_id'		=> 'screen_options_toggle',
								'lockable'			=> true,
								'templates_include'	=> array(
													$this->popup_key( 'advanced_mobile' ),
													'screen_options_visibility'
												)
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
						'args'			=> array( 'sc'	=> $this )
					),

				array(
						'id'	=> 'av_element_hidden_in_editor',
						'type'	=> 'hidden',
						'std'	=> '0'
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
			global $avia_config;

			/**
			 * Layout Tab
			 * ===========
			 */

			$c = array(
						 array(
							'name'		=> __( 'Grid Row Minimum Height', 'avia_framework' ),
							'desc'		=> __( 'Define a minimum height for the grid row.', 'avia_framework' ),
							'id'		=> 'min_height_percent',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'At least 100&percnt; of browser window height', 'avia_framework' )						=> '100',
												__( 'At least 75&percnt; of browser window height', 'avia_framework' )						=> '75',
												__( 'At least 50&percnt; of browser window height', 'avia_framework' )						=> '50',
												__( 'At least 25&percnt; of browser window height', 'avia_framework' )						=> '25',
												__( 'Minimum custom height in &percnt; based on browser windows height', 'avia_framework' )	=> 'percent',
												__( 'Minimum custom height in pixel', 'avia_framework' )									=> '',
											)
						),

						array(
							'name'		=> __( 'Grid Row Minimum Custom Height In &percnt;', 'avia_framework' ),
							'desc'		=> __( 'Define a minimum height for the gridrow in &percnt; based on the browser windows height', 'avia_framework' ),
							'id'		=> 'min_height_pc',
							'type'		=> 'select',
							'std'		=> '25',
							'lockable'	=> true,
							'required'	=> array( 'min_height_percent', 'equals', 'percent' ),
							'subtype'	=> AviaHtmlHelper::number_array( 1, 99, 1 )
						),

						array(
							'name'		=> __( 'Grid Row Minimum Custom Height In px', 'avia_framework' ),
							'desc'		=> __( 'Set the minimum height of all the cells in pixel. eg:400px', 'avia_framework' ),
							'id'		=> 'min_height',
							'type'		=> 'input',
							'std'		=> '0',
							'lockable'	=> true,
							'required'	=> array( 'min_height_percent', 'equals', '' )
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Grid Row Height', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'layout_height' ), $template, true );

			$c = array(
						array(
							'name' 	=> __( 'Grid Borders', 'avia_framework' ),
							'desc'  => __( 'Choose if your layout grid should display any border', 'avia_framework' ),
							'id' 	=> 'border',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'No Borders', 'avia_framework' )				=> '',
												__( 'Borders on top and bottom', 'avia_framework' )	=> 'av-border-top-bottom',
												__( 'Borders between cells', 'avia_framework' )		=> 'av-border-cells',
												__( 'Borders on top and bottom and between cells', 'avia_framework' )	=> 'av-border-top-bottom av-border-cells',
											)
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Borders', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'layout_borders' ), $template, true );

			/**
			 * Styling Tab
			 * ===========
			 */

			$desc  = __( 'The section will use the color scheme you select. Color schemes are defined on your styling page', 'avia_framework' );
			$desc .= '<br/><a target="_blank" href="' . admin_url( 'admin.php?page=avia#goto_styling' ) . '">';
			$desc .= __( '(Show Styling Page)', 'avia_framework' ) . '</a>';

			$c = array(
						array(
							'name' 	=> __( 'Section Colors', 'avia_framework' ),
							'desc'  => $desc,
							'id' 	=> 'color',
							'type' 	=> 'select',
							'std' 	=> 'main_color',
							'lockable'	=> true,
							'subtype'	=>  array_flip( $avia_config['color_sets'] )
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

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_colors' ), $template, true );

			/**
			 * Advanced Tab
			 * =============
			 */

			$desc  = __( 'Select order of cells when switched to fullwidth. Individual position must be set in &quot;Advanced Tab -&gt; Responsive Toggle&quot;.', 'avia_framework' ) . '<br /><br />';
			$desc .= '<strong>' . __( ' This is currently a beta feature (added 4.8.7)', 'avia_framework' ) . '</strong>';

			$c = array(
						array(
							'name'		=> __( 'Mobile Behaviour', 'avia_framework' ),
							'desc'		=> __( 'Choose how the cells inside the grid should behave on mobile devices and small screens', 'avia_framework' ),
							'id'		=> 'mobile',
							'type'		=> 'select',
							'std'		=> 'av-flex-cells',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Default: Each cell is displayed on its own', 'avia_framework' )					=> 'av-flex-cells',
												__( 'Cells appear beside each other, just like on large screens', 'avia_framework' )	=> 'av-fixed-cells',
											)
						),

						array(
							'name'		=> __( 'Mobile Breaking Point', 'avia_framework' ),
							'desc'		=> __( 'Set the screen width when cells in this row should switch to full width', 'avia_framework' ),
							'type'		=> 'heading',
							'required'	=> array( 'mobile', 'not', 'av-fixed-cells' ),
							'description_class'	=> 'av-builder-note av-neutral'
						),

						array(
							'name'		=> __( 'Fullwidth Break Point', 'avia_framework' ),
							'desc'		=> __( 'The cells in this row will switch to fullwidth at this screen width ', 'avia_framework' ),
							'id'		=> 'mobile_breaking',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'mobile', 'not', 'av-fixed-cells' ),
							'subtype'	=> array(
												__( 'On mobile devices (at a screen width of 767px or lower)', 'avia_framework' )	=> '',
												__( 'On tablets (at a screen width of 989px or lower)', 'avia_framework' )			=> 'av-break-at-tablet',
											)
						),

						array(
							'name'		=> __( 'Column Behaviour When Fullwidth', 'avia_framework' ),
							'desc'		=> $desc,
							'id'		=> 'mobile_column_order',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'mobile', 'not', 'av-fixed-cells' ),
							'subtype'	=> array(
												__( 'Same order as defined for desktop', 'avia_framework' )				=> '',
												__( 'Reverse order', 'avia_framework' )									=> 'reverse',
												__( 'Individually select position for each column', 'avia_framework' )	=> 'individual',
											)
						)

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_mobile' ), $c, true );

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
/*
			$params['content'] = trim($params['content']);
			if(empty($params['content'])) $params['content'] = '[av_cell_one_half first][/av_cell_one_half] [av_cell_one_half][/av_cell_one_half]';
*/

			$default = array();
			$locked = array();
			$attr = $params['args'];
			$content = $params['content'];

			Avia_Element_Templates()->set_locked_attributes( $attr, $this, $this->config['shortcode'], $default, $locked, $content );

			$args = $attr;		//	=> extract( $params );

			$name = $this->config['shortcode'];
			$data['shortcodehandler'] = $this->config['shortcode'];
			$data['modal_title'] = $this->config['name'];
			$data['modal_ajax_hook'] = $this->config['shortcode'];
			$data['dragdrop-level'] = $this->config['drag-level'];
			$data['allowed-shortcodes'] = $this->config['shortcode'];

			if( ! empty( $this->config['modal_on_load'] ) )
			{
				$data['modal_on_load'] 	= $this->config['modal_on_load'];
			}

			$dataString  = AviaHelper::create_data_string( $data );

			if( $content )
			{
				$final_content = $this->builder->do_shortcode_backend( $content );
				$text_area = ShortcodeHelper::create_shortcode_by_array( $name, $content, $args );
			}
			else if( Avia_Element_Templates()->is_edit_element_page() )
			{
				$cell = new avia_sc_cell( $this->builder );

				$params = array(
							'content' => '',
							'args' => array(),
							'data' => ''
						);

				$final_content = $cell->editor_element( $params );

				$text_area = ShortcodeHelper::create_shortcode_by_array( $name, '[av_cell_one_full][/av_cell_one_full]', $args );
			}
			else
			{
				$cell = new avia_sc_cell_one_half( $this->builder );

				$params = array(
							'content' => '',
							'args' => array(),
							'data' => ''
						);

				$final_content  = $cell->editor_element( $params );
				$final_content .= $cell->editor_element( $params );

				$text_area = ShortcodeHelper::create_shortcode_by_array( $name, '[av_cell_one_half][/av_cell_one_half] [av_cell_one_half][/av_cell_one_half]', $args );
			}

			$title_id = ! empty( $args['id'] ) ? ': ' . ucfirst( $args['id'] ) : '';
			$hidden_el_active = ! empty( $args['av_element_hidden_in_editor'] ) ? 'av-layout-element-closed' : '';


			$output  = "<div class='avia_layout_row {$hidden_el_active} avia_layout_section avia_pop_class avia-no-visual-updates {$name} av_drag' {$dataString}>";
			$output .=		"<div class='avia_sorthandle menu-item-handle'>";
			$output .=			"<span class='avia-element-title'>{$this->config['name']}<span class='avia-element-title-id'>{$title_id}</span></span>";
			$output .=			"<a class='avia-delete'  href='#delete' title='" . __( 'Delete Row', 'avia_framework' ) . "'>x</a>";
			$output .=			"<a class='avia-toggle-visibility'  href='#toggle' title='" . __( 'Show/Hide Section', 'avia_framework' ) . "'></a>";

			if( ! empty( $this->config['popup_editor'] ) )
			{
				$output .=		"<a class='avia-edit-element'  href='#edit-element' title='" . __( 'Edit Row', 'avia_framework' ) . "'>" . __( 'edit', 'avia_framework' ) . '</a>';
			}

			$output .=			"<a class='avia-save-element'  href='#save-element' title='" . __( 'Save Element as Template', 'avia_framework' ) . "'>+</a>";
			$output .=			"<a class='avia-clone'  href='#clone' title='" . __( 'Clone Row', 'avia_framework' ) . "' >" . __( 'Clone Row', 'avia_framework' ) . '</a>';
			$output .=		'</div>';
			$output .=		"<div class='avia_inner_shortcode avia_connect_sort av_drop' data-dragdrop-level='{$this->config['drop-level']}'>";
			$output .=			"<textarea data-name='text-shortcode' cols='20' rows='4'>{$text_area}</textarea>";
			$output .=			$final_content;
			$output .=		'</div>';
			$output .=		"<a class='avia-layout-element-hidden' href='#'>" . __( 'Grid Row content hidden. Click here to show it', 'avia_framework' ) . '</a>';
			$output .=		'<div class="avia-cell-layout-buttons">';
			$output .=			"<a class='avia-set-cell-size avia-add'  href='#set-size' title='" . __( 'Set Cell Size', 'avia_framework' ) . "'>" . __( 'Set Cell Size', 'avia_framework' ) . '</a>';
			$output .=			"<a class='avia-add-cell avia-add'  href='#add-cell' title='" . __( 'Add Cell', 'avia_framework' ) . "'>" . __( 'Add Cell', 'avia_framework' ) . '</a>';
			$output .=		'</div>';
			$output .= '</div>';

			return $output;
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
						'color'					=> 'main_color',
						'border'				=> '',
						'min_height'			=> '0',
						'min_height_percent'	=> '',
						'min_height_pc'			=> 25,
						'mobile'				=> 'av-flex-cells',
						'mobile_breaking'		=> '',
						'mobile_column_order'	=> '',
						'id'					=> ''
					);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			if( 'percent' == $atts['min_height_percent'] )
			{
				$atts['min_height_percent'] = $atts['min_height_pc'];
			}

			if( empty( $atts['fold_height'] ) )
			{
				$atts['fold_height'] = 80;
			}

			/**
			 * Removed option, allows to place top of folded container from screen top when top of container unvisible
			 *
			 * @since 5.6
			 * @param int $avf_fold_top_offset
			 * @param array $atts
			 * @param aviaShortcodeTemplate $this
			 * @return int
			 */
			$atts['fold_top_offset'] = apply_filters( 'avf_fold_top_offset', 50, $atts, $this );

			$atts['fold_element_class'] = "av-fold-grid-{$element_id}";

			//	make global for content inside
			avia_sc_cell::$attr = $atts;

			$element_styling->create_callback_styles( $atts );


			$classes = array(
						'av-layout-grid-container',
						$element_id,
						'entry-content-wrapper',
						$atts['color'],
						$atts['border'],
						$atts['mobile'],
						$atts['mobile_breaking'],
					);

			$element_styling->add_classes( 'section', $classes );
			$element_styling->add_classes_from_array( 'section', $meta, 'el_class' );

			if( $atts['mobile_column_order'] != '' )
			{
				$element_styling->add_classes( 'section', 'av-grid-order-' . $atts['mobile_column_order'] );
			}

			if( $atts['min_height_percent'] != '' )
			{
				$element_styling->add_classes( 'section', array(
													'av-cell-min-height',
													'av-cell-min-height-' . $atts['min_height_percent']
												) );
			}

			if( isset( $meta['index'] ) && $meta['index'] > 0 )
			{
				$element_styling->add_classes( 'section', 'grid-row-not-first' );
			}


			if( ! empty( $atts['fold_type'] ) )
			{
				$f_classes = array(
							$atts['fold_type'],
							'avia-fold-grid-row-wrap',
							'avia-fold-init',
							$atts['fold_element_class'],
							$atts['fold_text_style'],
							empty( $atts['fold_btn_align'] ) ? 'align-left' : $atts['fold_btn_align']
						);

				$element_styling->add_classes( 'fold-section', $f_classes );

				if( $atts['fold_text_style'] == '' )
				{
					$element_styling->add_styles( 'fold-button', array( 'color' => $atts['fold_text_color'] ) );
				}

				if( $atts['fold_text_style'] != '' && $atts['fold_btn_color'] == 'custom' )
				{
					$element_styling->add_styles( 'fold-button', array(
													'background-color'	=> $atts['fold_btn_bg_color'],
													'color'				=> $atts['fold_btn_font_color'],
												) );
				}

				$element_styling->add_responsive_font_sizes( 'fold-button', 'size-btn-text', $atts, $this );

				if( ! empty( $atts['fold_overlay_color'] ) )
				{
					$bg_rgb = avia_backend_hex_to_rgb_array( $atts['fold_overlay_color'] );

					$element_styling->add_styles( 'fold-unfold-after', array(
													'background'	=> "linear-gradient( to bottom, rgba({$bg_rgb[0]},{$bg_rgb[1]},{$bg_rgb[2]},0), rgba({$bg_rgb[0]},{$bg_rgb[1]},{$bg_rgb[2]},1) )"
												) );
				}

				$element_styling->add_styles( 'fold-unfold', array( 'max-height' => $atts['fold_height'] . 'px' ) );

				if( ! empty( $atts['fold_timer'] ) )
				{
					$rules = $element_styling->transition_duration_rules( $atts['fold_timer'] );

					$element_styling->add_styles( 'fold-unfold', $rules );
					$element_styling->add_styles( 'fold-unfold-after', $rules );
				}

				$element_styling->add_styles( 'fold-unfold-folded-after', array( 'z-index' => $atts['z_index_fold'] ) );

				//	prepare attributes for frontend
				$element_styling->add_data_attributes( 'fold-section', array(
												'type'		=> $atts['fold_type'],
												'height'	=> $atts['fold_height'],
												'more'		=> $atts['fold_more'],
												'less'		=> $atts['fold_less'],
												'context'	=> __CLASS__
											) );
			}


			$selectors = array(
						'section'					=> ".av-layout-grid-container.{$element_id}",
						'fold-section'				=> ".avia-fold-unfold-section.{$atts['fold_element_class']}",
						'fold-unfold'				=> ".avia-fold-unfold-section.{$atts['fold_element_class']} .av-fold-unfold-container",
						'fold-unfold-after'			=> "#top .avia-fold-unfold-section.{$atts['fold_element_class']} .av-fold-unfold-container:after",
						'fold-unfold-folded-after'	=> ".avia-fold-unfold-section.{$atts['fold_element_class']} .av-fold-unfold-container.folded::after",
						'fold-button'				=> "#top .avia-fold-unfold-section.{$atts['fold_element_class']} .av-fold-button-container"
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
		 * @return string $output returns the modified html string
		 */
		public function shortcode_handler( $atts, $content = '', $shortcodename = '', $meta = '' )
		{
			$result = $this->get_element_styles( compact( array( 'atts', 'content', 'shortcodename', 'meta' ) ) );

			extract( $result );
			extract( $atts );

			avia_sc_grid_row::$count++;

			$params = array();

			$params['open_structure'] = false;
			$params['id'] = AviaHelper::save_string( $id, '-', 'av-layout-grid-' . avia_sc_grid_row::$count );
			$params['custom_markup'] = $meta['custom_markup'];
			$params['aria_label'] = $meta['aria_label'];
			$params['data'] = '';

			if( $min_height_percent != '' )
			{
				$params['data'] .= " data-av_minimum_height_pc='{$min_height_percent}'";
			}

			//we dont need a closing structure if the element is the first one or if a previous fullwidth element was displayed before
			if( isset($meta['index'] ) && $meta['index'] == 0)
			{
				$params['close'] = false;
			}

			if( ! empty( $meta['siblings']['prev']['tag'] ) && in_array( $meta['siblings']['prev']['tag'], AviaBuilder::$full_el_no_section ) )
			{
				$params['close'] = false;
			}

			$params['class']  = $element_styling->get_class_string( 'section' );
			$params['class'] .= ' ' . $element_styling->responsive_classes_string( 'hide_element', $atts );

			//	execute content
			$cells_content = ShortcodeHelper::avia_remove_autop( $content, true );

			if( $fold_type != '' )
			{
				$fold_section_class = $element_styling->get_class_string( 'fold-section' );
				$fold_section_data = $element_styling->get_data_attributes_json_string( 'fold-section', 'fold_unfold' );
				$args = [
						'atts'			=> $atts,
						'wrapper_class'	=> 'av-grid-row-fold-btn-wrap av-fold-btn-padding',
						'context'		=> __CLASS__
					];

				$params['before_new']  =	"<div id='{$params['id']}-fold-unfold' class='avia-fold-unfold-section {$fold_section_class} {$atts['color']}' {$fold_section_data}>";
				$params['before_new'] .=		'<div class="av-fold-unfold-container folded"></div>';
				$params['before_new'] .=		aviaFrontTemplates::fold_unfold_button( $args );
				$params['before_new'] .=	'</div>';
			}

			$style_tag = $element_styling->get_style_tag( $element_id );

			$output  = '';
			$output .= $style_tag;

			$output .= avia_new_section( $params );
			$output .=		$cells_content;
			$output .= avia_section_after_element_content( $meta , 'after_grid_row_' . avia_sc_grid_row::$count, false );


			// added to fix https://kriesi.at/support/topic/footer-disseapearing/#post-427764
			avia_sc_section::$close_overlay = '';

			return $output;
		}
	}
}

