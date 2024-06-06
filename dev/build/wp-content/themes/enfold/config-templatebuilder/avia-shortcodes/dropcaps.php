<?php
/**
 * DROPCAPS
 *
 * Shortcode which creates dropcaps
 */

 // Don't load directly
if( ! defined( 'ABSPATH' ) ) { die( '-1' ); }


if( ! class_exists( 'av_dropcap1', false ) )
{
	class av_dropcap1 extends aviaShortcodeTemplate
	{

		/**
		 * Create the config array for the shortcode button
		 */
		protected function shortcode_insert_button()
		{
			$this->config['self_closing']	= 'no';

			$this->config['name']			= 'Dropcap 1 (Big Letter)';
			$this->config['order']			= 100;
			$this->config['shortcode']		= 'av_dropcap1';
			$this->config['inline']			= true;
			$this->config['html_renderer']	= false;
			$this->config['tinyMCE']		= array(
													'tiny_only'		=> true,
													'instantInsert'	=> '[av_dropcap1]H[/av_dropcap1]ello'
												);

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
						'color'		=> '',
						'custom_bg'	=> '#444444'
					);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			//	background-color: default; is not a valid CSS
			if( 'default' == $atts['color'] )
			{
				$atts['color'] = '';
			}


			$classes = array(
						$shortcodename,
						$element_id
					);

			$element_styling->add_classes( 'container', $classes );

			$element_styling->add_classes_from_array( 'container', $meta, 'custom_class' );

			if( ! empty( $atts['color'] ) )
			{
				$color = ( $atts['color'] == 'custom' && ! empty( $atts['custom_bg'] ) ) ? $atts['custom_bg'] : $atts['color'];
				$element_styling->add_styles( 'container', array( 'background-color' => $color ) );
			}


			$selectors = array(
							'container'		=> ".{$shortcodename}.{$element_id}",
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
			//this is a fix that solves the false paragraph removal by wordpress if the dropcaps shortcode is used at the beginning of the content of single posts/pages
			global $post, $avia_add_p;

			$add_p = '';
			if( isset( $post->post_content ) && strpos( $post->post_content, '[dropcap' ) === 0 && $avia_add_p == false && is_singular() )
			{
				$add_p = '<p>';
				$avia_add_p = true;
			}


			$result = $this->get_element_styles( compact( array( 'atts', 'content', 'shortcodename', 'meta' ) ) );

			extract( $result );

			$style_tag = $element_styling->get_style_tag( $element_id );
			$container_class = $element_styling->get_class_string( 'container' );

			$output  = '';
			$output .= $style_tag;
			$output .= $add_p;
			$output .= "<span class='{$container_class}'>";
			$output .=		$content;
			$output .= '</span>';

			return $output;
		}
	}
}


if( ! class_exists( 'av_dropcap2', false ) )
{
	class av_dropcap2 extends av_dropcap1
	{

		/**
		 * Create the config array for the shortcode button
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['self_closing']	= 'no';

			$this->config['name']			= 'Dropcap 2 (Colored Background)';
			$this->config['order']			= 90;
			$this->config['shortcode']		= 'av_dropcap2';
			$this->config['html_renderer']	= false;
			$this->config['inline']			= true;
			$this->config['tinyMCE']		= array(
													'tiny_only'			=> true,
													'templateInsert'	=> '[av_dropcap2 color="{{color}}" custom_bg="{{custom_bg}}"]H[/av_dropcap2]ello'
												);
			//$this->config['modal_data']	= array( 'modal_class' => 'smallscreen' );
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
						'name'  => __( 'Styling', 'avia_framework' ),
						'nodescription' => true
					),
						array(
								'type'			=> 'template',
								'template_id'	=> $this->popup_key( 'styling_dropcap2' )
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

			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'named_colors',
							'name'			=> __( 'Dropcap Styling', 'avia_framework' ),
							'desc'			=> __( 'Here you can set the background color of your Dropcap', 'avia_framework' ),
							'id'			=> 'color',
							'std'			=> 'default',
							'custom'		=> true,
							'no_alternate'	=> true,
							'theme-col-key' => 'default',
							'translucent'	=> array()
						),

						array(
							'name' 	=> __( 'Custom Background Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom background color for your dropcap here', 'avia_framework' ),
							'id' 	=> 'custom_bg',
							'type' 	=> 'colorpicker',
							'std' 	=> '#444444',
							'rgba'	=> true,
							'required'	=> array( 'color', 'equals', 'custom' )
						),

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_dropcap2' ), $c );
		}
	}

}

