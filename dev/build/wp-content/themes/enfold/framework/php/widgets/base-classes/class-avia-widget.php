<?php
namespace aviaFramework\widgets\base;

/**
 * Base class for widgets
 *
 * @author guenter
 * @since 4.9				Code was moved from class-framework-widgets.php
 */
if( ! defined( 'AVIA_FW' ) ) {  exit( 'No direct script access allowed' );  }


if( ! class_exists( __NAMESPACE__ . '\Avia_Widget', false ) )
{
	abstract class Avia_Widget extends \WP_Widget
	{
		/**
		 *
		 * @since 4.3.2
		 * @var array
		 */
		protected $field_names;

		/**
		 * Default values for the widget
		 *
		 * @since 4.9
		 * @var array
		 */
		protected $defaults;

		/**
		 * @since 4.3.2
		 * @param string $id_base
		 * @param string $name
		 * @param array $widget_options
		 * @param array $control_options
		 */
		public function __construct( $id_base, $name, array $widget_options = array(), array $control_options = array() )
		{
			if( ! isset( $widget_options['classname'] ) )
			{
				$widget_options['classname'] = '';
			}

			$widget_options['classname'] = trim( 'avia-widget-container ' . $widget_options['classname'] );

			parent::__construct( $id_base, $name, $widget_options, $control_options );

			$this->field_names = array();
			$this->defaults = array();
		}

		/**
		 * @since 4.3.2
		 */
		public function __destruct()
		{
			if( method_exists( $this, 'parent::__destruct' ) )
			{
				parent::__destruct();
			}

			unset( $this->field_names );
			unset( $this->defaults );
		}

		/**
		 * Returns an array that contains all default instance members filled with default values
		 *
		 * @since 4.3.2				implemengted as abstract function
		 * @since 4.9				added with logic
		 * @param array $instance
		 * @return array
		 */
		protected function parse_args_instance( array $instance )
		{
			$new_instance = wp_parse_args( $instance, $this->defaults );

			return $new_instance;
		}

		/**
		 * Only display a short default message or a custom preview
		 *
		 * @since 4.9
		 * @param array $args
		 * @param array $instance
		 * @return boolean
		 */
		protected function in_block_editor_preview( array $args, array $instance = array() )
		{
			if( isset( $_REQUEST['legacy-widget-preview'] ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) )
			{
				if( false === $this->widget_block_preview( $args, $instance) )
				{
					echo isset( $args['before_widget'] ) ? $args['before_widget'] : '';

					echo '<h2>' . $this->name . '</h2>';
					echo '<p>' . __( 'Widget content only rendered in frontend.', 'avia_framework' ) . '</p>';

					echo isset( $args['after_widget'] ) ? $args['after_widget'] : '';
				}

				return true;
			}

			return false;
		}

		/**
		 * Callback to output a custom block preview
		 *
		 * @since 4.9
		 * @param array $args
		 * @param array $instance
		 * @return boolean					true if output processed
		 */
		protected function widget_block_preview( array $args, array $instance = array() )
		{
			return false;
		}

		/**
		 * Returns an array of all default fields
		 *
		 * @since 4.3.2
		 * @return array
		 */
		protected function get_field_names()
		{
			if( empty( $this->field_names ) )
			{
				$fields = $this->parse_args_instance( array() );
				$this->field_names = array_keys( $fields );
			}

			return $this->field_names;
		}

		/**
		 * Iterates over form elements (uses structure as in admin options page)
		 *
		 * @since 4.7.3.1
		 * @param array $elements
		 * @return string
		 */
		protected function render_form_elements( array $elements )
		{
			$html = new \avia_htmlhelper();
			$output = '';

			foreach( $elements as $element )
			{
				$output .= $html->render_single_element( $element );
			}

			return $output;
		}

		/**
		 * Output the <option> tag for a series of numbers and set the selected attribute
		 *
		 * @since 4.3.2
		 * @added_by günter
		 * @param int $start
		 * @param int $end
		 * @param string $selected
		 */
		static public function number_options( $start = 1, $end = 50, $selected = 1 )
		{
			$options = array();

			for( $i = $start; $i <= $end; $i++ )
			{
				$options[ $i ] = $i;
			}

			return Avia_Widget::options_from_array( $options, $selected );
		}

		/**
		 * Output the <option> tag for a key - value array and set the selected attribute
		 *
		 * @since 4.3.2
		 * @added_by günter
		 * @param array $options
		 * @param type $selected
		 * @return string
		 */
		static public function options_from_array( array $options, $selected )
		{
			$out = '';

			foreach( $options as $key => $value )
			{
				$out .= '<option value="' . $key . '" ' . selected( $key, $selected ) . '>' . esc_html( $value ) . '</option>';
			}
			return $out;
		}

	}
}
