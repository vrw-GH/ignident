<?php
/**
 * Widget Area
 *
 * Displays one of the registered Widget Areas of the theme
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_sc_widgetarea', false ) )
{
	class avia_sc_widgetarea extends aviaShortcodeTemplate
	{
		/**
		 * Create the config array for the shortcode button
		 */
		protected function shortcode_insert_button()
		{
			$this->config['self_closing']	= 'yes';

			$this->config['name']		= __( 'Widget Area', 'avia_framework' );
			$this->config['tab']		= __( 'Content Elements', 'avia_framework' );
			$this->config['icon']		= AviaBuilder::$path['imagesURL'] . 'sc-sidebar.png';
			$this->config['order']		= 10;
			$this->config['target']		= 'avia-target-insert';
			$this->config['shortcode'] 	= 'av_sidebar';
			$this->config['tinyMCE'] 	= array( 'instantInsert' => '[av_sidebar widget_area="Displayed Everywhere"]' );
			$this->config['tooltip'] 	= __( 'Display one of the themes widget areas', 'avia_framework' );
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
			//fetch all registered sidebars
			$sidebars = AviaHelper::get_registered_sidebars();

			if( empty( $params['args']['widget_area'] ) )
			{
				$params['args']['widget_area'] = reset( $sidebars );
			}

			//	Fallback because changed to Footer - Column 1 since 4.9
			$sidebars['Footer - column1'] = 'Footer - column1';
			$sidebars['Footer - column2'] = 'Footer - column2';
			$sidebars['Footer - column3'] = 'Footer - column3';
			$sidebars['Footer - column4'] = 'Footer - column4';
			$sidebars['Footer - column5'] = 'Footer - column5';

			$element = array(
							'type'		=>'select',
							'std'		=> htmlspecialchars_decode( $params['args']['widget_area'] ),
							'class'		=> 'avia-recalc-shortcode',
							'subtype'	=> $sidebars,
							'data'		=> array( 'attr' => 'widget_area' )
						);

			$params = parent::editor_element( $params );

			$params['innerHtml'] .= AviaHtmlHelper::render_element( $element );
			$params['class'] = '';
			$params['content'] = null;

			return $params;
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
			$output = '';

			if( ! isset( $atts['widget_area'] ) )
			{
				return $output;
			}

			$area = $atts['widget_area'];

			//	Fallback because changed to Footer - Column 1 since 4.9
			$area = str_replace( 'Footer - column', 'Footer - Column ', $area );

			if( is_dynamic_sidebar( $area ) )
			{
				ob_start();
				dynamic_sidebar( $area );
				$content = ShortcodeHelper::avia_remove_autop( ob_get_clean(), true );

				if( $content )
				{
					$output .= "<div {$meta['custom_el_id']} class='avia-builder-widget-area clearfix {$meta['el_class']}'>{$content}</div>";
				}
			}

			return $output;
		}

	}
}
