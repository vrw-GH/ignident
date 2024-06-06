<?php
namespace aviaBuilder\base;

use \AviaHelper;
use \AviaHtmlHelper;
use \ShortcodeHelper;

/**
 * This class contains base functions to create the modal popup window and add the elements
 *
 * @author		Günter
 * @since 4.8.9		copied from config-templatebuilder\avia-template-builder\php\class-html-helper.php to allow extending this class by user and customization
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( __NAMESPACE__ . '\aviaModalBase', false ) )
{
	class aviaModalBase
	{
		/**
		 * Required for meta key storing when creating a metabox. Necessary to set already stored std values
		 *
		 * @since ???
		 * @var array
		 */
		static $metaData = array();

		/**
		 * All element values in an id=>value array so we can check dependencies
		 *
		 * @since ???
		 * @var array
		 */
		static $elementValues = array();

		/**
		 * All elements that didnt pass the dependency test and are hidden
		 *
		 * @since ???
		 * @var array
		 */
		static $elementHidden = array();

		/**
		 *
		 * @param array $element
		 * @return string
		 */
		static public function render_metabox( array $element )
		{
			//query the metadata of the current post and check if a key is set, if not set the default value to the standard value, otherwise to the key value
			if( ! isset( AviaHtmlHelper::$metaData[ $element['current_post'] ] ) )
			{
				AviaHtmlHelper::$metaData[ $element['current_post'] ] = get_post_custom( $element['current_post'] );
			}

			if( isset( AviaHtmlHelper::$metaData[ $element['current_post'] ][ $element['id'] ] ) )
			{
				$element['std'] = AviaHtmlHelper::$metaData[ $element['current_post'] ][ $element['id'] ][0];
			}

			return AviaHtmlHelper::render_element( $element );
		}

		/**
		 *
		 * @param array $elements
		 * @param aviaShortcodeTemplate|false $parent_class
		 * @return string
		 */
		static public function render_multiple_elements( array $elements, $parent_class = false )
		{
			$output = '';

			//	if the ajax request told us that we are fetching the subfunction iterate over the array elements and extract the subelements
			if( empty( $_POST['params']['subelement'] ) && false !== $parent_class )
			{
				//	prescan as $elementValues is set in following loop and modal
				Avia_Element_Templates()->prepare_popup_subitem_elements( $elements, AviaHtmlHelper::$elementValues, $parent_class );
			}

			foreach( $elements as $element )
			{
				$output .= AviaHtmlHelper::render_element( $element, $parent_class );
			}

			return $output;
		}

		/**
		 *
		 * @param array $element
		 * @return array
		 */
		static public function ajax_modify_id( array $element )
		{
			// check if its an ajax request. if so prepend a string to ensure that the ids are unique.
			// If there are multiple modal windows called prepend the string multiple times
			if( isset( $_POST['ajax_fetch'] ) )
			{
				$prepends = isset( $_POST['instance'] ) ? $_POST['instance'] : 0;
				$element['ajax'] = true;

				for( $i = 0; $i < $prepends; $i++ )
				{
					$element['id'] = 'aviaTB' . $element['id'];
				}
			}

			return $element;
		}

		/**
		 * Returns an element with all needed default keys
		 *
		 * @since 4.5.7.2
		 * @param array $element
		 * @return array
		 */
		static public function validate_element( array $element = array() )
		{
			$defaults = array(
								'id'				=> '',
								'name'				=> '',
								'label'				=> '',
								'std'				=> '',
								'class'				=> '',
								'container_class'	=> '',
								'desc'				=> '',
								'required'			=> array(),
								'target'			=> array(),
								'shortcode_data'	=> array(),
								'builder_active'	=> ''
							);

			$element = array_merge( $defaults, $element );

			return $element;
		}

		/**
		 *
		 * @since < 4.0
		 * @param array $element
		 * @param aviaShortcodeTemplate|false $parent_class
		 * @return string
		 */
		static public function render_element( array $element, $parent_class = false )
		{
			$element = AviaHtmlHelper::validate_element( $element );

			$output	= '';

			if( $element['builder_active'] )
			{
				$output .= '<div class="avia-conditional-elements avia-conditional-elements-builder-active">';
			}

			AviaHtmlHelper::$elementValues[ $element['id'] ] = $element['std']; // save the values into a unique array in case we need it for dependencies

			// override it with locked value
			if( isset( $element['lockable'] ) && true === $element['lockable'] )
			{
				if( isset( $element['locked_value'] ) )
				{
					AviaHtmlHelper::$elementValues[ $element['id'] ] = $element['locked_value'];
				}
			}

			// create default $data_string und $class_string and checks the dependencies of an object
			$dependency = AviaHtmlHelper::check_dependencies( $element );
			extract( $dependency );


			// check if its an ajax request. if so prepend a string to ensure that the ids are unique.
			// If there are multiple modal windows called prepend the string multiple times
			$element = AviaHtmlHelper::ajax_modify_id( $element );


			$id_string = empty( $element['id'] ) ? '' : "id='{$element['id']}-form-container'";
			$class_string .= empty( $element['container_class'] ) ? '' : $element['container_class'];
			$description_class = empty( $element['description_class'] ) ? '' : $element['description_class'];

			$target_string = '';
			if( ! empty( $element['target'] ) )
			{
				$data['target-element'] = $element['target'][0];
				$data['target-property'] = $element['target'][1];
				$target_string = AviaHelper::create_data_string( $data );
				$class_string .= ' avia-attach-targeting ';
			}

			if( ! empty( $element['fetchTMPL'] ) )
			{
				$class_string .= ' avia-attach-templating ';
			}

			if( empty( $element['nodescription'] ) )
			{
				$locked_info = '';

				if( isset( $element['locked_value'] ) )
				{
					$class_string .= ' avia-locked-input-element';

					if( strpos( $element['container_class'], 'av-lock-element-checkbox' ) === false )
					{
						$locked_info .= '<span class="avia-lock-sympol"></span>';
					}
				}

				//	currently only checkboxes support this
				if( is_array( $element['desc'] ) )
				{
					$class_string .= ' avia-checkbox-label-change';
					$data_string .= ' data-checkbox-checked="' . esc_attr( $element['desc']['checked'] ) . '" data-checkbox-unchecked="' . esc_attr( $element['desc']['unchecked'] ) . '" ';
				}

				$output .= "<div class='avia_clearfix avia-form-element-container {$class_string} avia-element-{$element['type']}' {$id_string} {$data_string} {$target_string}>";

				if( ! empty( $element['name'] ) || ! empty( $element['desc'] ) )
				{
					$output .= "<div class='avia-name-description {$description_class}'>";

					if( ! empty( $element['name'] ) )
					{
						$output .= '<strong>' . $locked_info . $element['name'] . '</strong>';
						$locked_info = '';
					}

					if( ! empty( $element['desc'] ) )
					{
						if( ! empty( $element['type'] ) && $element['type'] != 'checkbox' )
						{
							$output .= '<div>' . $locked_info . $element['desc'] . '</div>';
						}
						else
						{
							$desc = $element['desc'];
							if( is_array( $element['desc'] ) )
							{
								$desc = $element['std'] != '' ? $element['desc']['checked'] : $element['desc']['unchecked'];
							}

							$output .= "<label for='{$element['id']}'>{$locked_info}{$desc}</label>";
						}
					}

					$output .= '</div>';
				}

				$output .= "<div class='avia-form-element {$element['class']}'>";
				//$output .= AviaHtmlHelper::{$element['type']}($element, $parent_class);

				if( method_exists( get_called_class(), $element['type'] ) )
				{
					$output .= call_user_func( array( get_called_class(), $element['type'] ), $element, $parent_class, $dependency );
				}

				if( ! empty( $element['fetchTMPL'] ) )
				{
					$output .= '<div class="template-container"></div>';
				}

				$output .= '</div>';
				$output .= '</div>';
			}
			else
			{
				//$output .= AviaHtmlHelper::{$element['type']}($element, $parent_class);
				if( method_exists( get_called_class(), $element['type'] ) )
				{
					$output .= call_user_func( array( get_called_class(), $element['type'] ), $element, $parent_class, $dependency );
				}
			}

			if( $element['builder_active'] )
			{
				$output .=		'<div class="av-builder-active-overlay"></div>';
				$output .=		'<div class="av-builder-active-overlay-content">';
				$output .=			__( 'This element only works with activated advanced layout builder', 'avia_framework' );
				$output .=		'</div>';
				$output .= '</div>';
			}

			return $output;
		}

		/*
		 * Helper function that checks dependencies between objects based on the $element['required'] array
		 *
		 * If the array is set it needs to have exactly 3 entries.
		 * The first entry describes which element should be monitored by the current element. eg: "content"
		 * The second entry describes the comparison parameter. eg: "equals, not, is_larger, is_smaller ,contains"
		 * The third entry describes the value that we are comparing against.
		 *
		 * Example: if the required array is set to array('content','equals','Hello World'); then the current
		 * element will only be displayed if the element with id "content" has exactly the value "Hello World"
		 *
		 * @param array $element
		 * @return array
		 */
		static public function check_dependencies( array $element )
		{
			$params = array(
						'data_string'	=> '',
						'class_string'	=> ''
					);

			if( ! empty( $element['required'] ) )
			{
				$data['check-element'] = $element['required'][0];
				$data['check-comparison'] = $element['required'][1];
				$data['check-value'] = $element['required'][2];
				$params['data_string'] = AviaHelper::create_data_string( $data );

				$return = false;

				// required element must not be hidden. otherwise hide this one by default
				if( ! isset( AviaHtmlHelper::$elementHidden[ $data['check-element'] ] ) )
				{
					if( isset( AviaHtmlHelper::$elementValues[ $data['check-element'] ] ) )
					{
						$value1 = AviaHtmlHelper::$elementValues[ $data['check-element'] ];
						$value2 = $data['check-value'];

						switch( $data['check-comparison'] )
						{
							case 'equals':
								if( $value1 == $value2 )
								{
									$return = true;
								}
								break;
							case 'not':
								if( $value1 != $value2 )
								{
									$return = true;
								}
								break;
							case 'is_larger':
								if( $value1 > $value2 )
								{
									$return = true;
								}
								break;
							case 'is_smaller':
								if( $value1 < $value2 )
								{
									$return = true;
								}
								break;
							case 'contains':
								if( strpos( $value1, $value2 ) !== false )
								{
									$return = true;
								}
								break;
							case 'doesnt_contain':
								if(strpos( $value1, $value2 ) === false )
								{
									$return = true;
								}
								break;
							case 'is_empty_or':
								if( empty( $value1 ) || $value1 == $value2 )
								{
									$return = true;
								}
								break;
							case 'not_empty_and':
								if( ! empty( $value1 ) && $value1 != $value2 )
								{
									$return = true;
								}
								break;
							case 'parent_in_array':			//	$value1 = 'value,id' or 'value'; $value2 = 'val1,val2,....'
								$sep = strpos( $value1, ',' );
								$val = ( false !== $sep ) ? substr( $value1, 0, $sep ) : $value1;
								$return = in_array( $val, explode( ',', $value2 ) );
								break;
							case 'parent_not_in_array';		//	$value1 = 'value,id' or 'value'; $value2 = 'val1,val2,....'
								$sep = strpos( $value1, ',' );
								$val = ( false !== $sep ) ? substr( $value1, 0, $sep ) : $value1;
								$return = ! in_array( $val, explode( ',', $value2 ) );
								break;
						}
					}
				}

				if( ! $return )
				{
					$params['class_string'] = ' avia-hidden ';
					AviaHtmlHelper::$elementHidden[ $element['id'] ] = true;
				}
			}

			return $params;
		}

		/**
		 * Creates a wrapper around a set of elements. This set can be cloned with javascript
		 *
		 * @param array $element		the array holds data like id, class and some js settings
		 * @param aviaShortcodeTemplate	$parent_class
		 * @return string				the string returned contains the html code generated within the method
		 */
		static public function modal_group( array $element, $parent_class )
		{
			$iterations = count( $element['std'] );
			$class = isset( $element['class'] ) ? $element['class'] : '';
			$label_class = '';
			$group_extra = '';

			if( isset( $element['locked_value'] ) )
			{
				$class .= ' avia-modal-locked';
				$label_class .= ' avia-locked-data-hide';
				$group_extra .= ' avia-locked-data-hide';
			}

			$output = '';
			$output .= "<div class='avia-modal-group-wrapper {$class}'>";

			if( ! isset( $element['locked_value'] ) )
			{
				if( ! empty( $element['creator'] ) )
				{
					$output .= AviaHtmlHelper::render_element( $element['creator'] );
				}
			}

			if( isset( $element['locked_value'] ) )
			{
				$locked = $element;

				$locked['id'] = $element['id'] . '_fakeArg';
				$locked['std'] = array();

				foreach( $element['locked_value'] as $locked_value )
				{
					$locked_value_attr = $locked_value['attr'];
					if( ! empty( $locked_value['raw_content'] ) )
					{
						$locked_value_attr['content'] = $locked_value['raw_content'];
					}

					$locked['std'][] = $locked_value_attr;
				}

				$output .= "<div class='avia-modal-group avia-locked-data-value avia-fake-input' id='{$locked['id']}' >";

				$locked_count = count( $element['locked_value'] );

				for( $i = 0; $i < $locked_count; $i++ )
				{
					$locked['shortcode_data'] = $locked['std'][ $i ];
					$output .= AviaHtmlHelper::modal_group_sub( $locked, $parent_class, $i, true );
				}

				$output .= '</div>';
			}

			$output .= "<div class='avia-modal-group {$group_extra}' id='{$element['id']}' >";

			for( $i = 0; $i < $iterations; $i++ )
			{
				if( isset( $_POST['extracted_shortcode'] ) && isset( $_POST['extracted_shortcode'][ $i ] ) )
				{
					$element['shortcode_data'] = $_POST['extracted_shortcode'][ $i ]['attr'];
				}

				$output .= AviaHtmlHelper::modal_group_sub( $element, $parent_class, $i );
			}


			$label = isset( $element['add_label'] ) ? $element['add_label'] : __( 'Add', 'avia_framework' );


			//since adding the clone event we display only custom labels and not only the '+' symbol
			//$label_class = isset($element['add_label']) ? 'avia-custom-label' : '';
			$label_class .= ' avia-custom-label';


			$output .= '</div>';

			if( ! isset( $element['disable_manual'] ) )
			{
				$output .= "<a class='avia-attach-modal-element-add avia-add {$label_class}'>{$label}</a>";

				if( ! isset( $element['disable_cloning'] ) )
				{
					$clone_label = isset( $element['clone_label'] ) ? $element['clone_label'] : __( 'Copy and add last entry', 'avia_framework' );

					$output .= "<a class='avia-attach-modal-element-clone avia-clone {$label_class}'>{$clone_label}</a>";
				}
			}

			$std_index = false;
			$script_class = 'avia-tmpl-modal-element';

			/*
			 * Check for special case in subitem handling
			 */
			if( ! Avia_Element_Templates()->popup_editor_needs_template_options() )
			{
				if( in_array( Avia_Element_Templates()->subitem_custom_element_handling(), array( 'first' ) ) )
				{
					$element['subelements'] = Avia_Element_Templates()->add_subitem_element_template_options( $element['subelements'] );
					$std_index = 0;
					$script_class .= ' avia-copy-element-template';
				}
			}

			/**
			 * Go the new wordpress way and instead of ajax-loading new items, prepare an empty js template
			 */
			$output .= '	<script type="text/html" class="' . $script_class . '">';
			$output .=			AviaHtmlHelper::modal_group_sub( $element, $parent_class, $std_index );
			$output .= '	</script>';

			$output .= '</div>';

			return $output;
		}

		/**
		 *
		 * @param array	$element
		 * @param aviaShortcodeTemplate	$parent_class
		 * @param int|false	$i									false, if we need a new empty template to clone if user clicks 'Add New'
		 * @param boolean $locked
		 * @return string
		 */
		static public function modal_group_sub( array $element, $parent_class, $i = false, $locked = false )
		{
			$output = '';

			$args = array();
			$content = null;

			// iterate over the subelements and set user selected values or leave the predefined default values
			foreach( $element['subelements'] as $key => $subelement )
			{
				/**
				 * New WP way: we add an 'empty' template filled with predefined default values that we can clone if user wants to add a new item,
				 * if we have already existing items overwrite default values with user selected values
				 */
				if( false !== $i )
				{
					if( isset( $element['std'] ) && isset( $subelement['id'] ) && is_array( $element['std'] ) && isset( $element['std'][ $i ][ $subelement['id'] ] ) )
					{
						$subelement['std'] = $element['std'][ $i ][ $subelement['id'] ];
					}
				}

				if( isset( $subelement['id'] ) )
				{
					if( $subelement['id'] == 'content' )
					{
						$content = $subelement['std'];
					}
					else
					{
						$args[ $subelement['id'] ] = $subelement['std'];
					}
				}
			}

			if( $i !== false && is_array( $element['shortcode_data'] ) )
			{
				$args = array_merge( $element['shortcode_data'], $args );
			}

			$params['args'] = $args;
			$params['content'] = $content;


			$defaults = array(
							'class'		=> '',
							'innerHtml'	=> ''
						);

			$params = array_merge( $defaults, $params );
			$params = $parent_class->editor_sub_element( $params );
			extract( $params );

			$dataString = '';

			if( $locked !== true )
			{
				$data = array();

				$data['modal_title'] 		= $element['modal_title'];
				$data['modal_open']			= isset( $element['modal_open'] ) ? $element['modal_open'] : 'yes';
				$data['trigger_button']		= isset( $element['trigger_button'] ) ? $element['trigger_button'] : '';
				$data['shortcodehandler'] 	= $parent_class->config['shortcode_nested'][0];
				$data['closing_tag']		= $parent_class->is_nested_self_closing( $parent_class->config['shortcode_nested'][0] ) ? 'no' : 'yes';
				$data['base_shortcode']		= $parent_class->config['shortcode'];
				$data['item_shortcode']		= $parent_class->config['shortcode_nested'][0];
				$data['element_title']		= isset( $parent_class->config['name_item'] ) ? $parent_class->config['name_item'] : sprintf( __( 'Item: %s', 'avia_framework' ), $parent_class->config['name'] );
				$data['element_tooltip']	= isset( $parent_class->config['tooltip_item'] ) ? $parent_class->config['tooltip_item'] : sprintf( __( 'Item: %s', 'avia_framework' ), $parent_class->config['tooltip'] );
				$data['modal_ajax_hook'] 	= $parent_class->config['shortcode_nested'][0];
				$data['modal_on_load'] 		= array();

				if( ! empty( $element['modal_on_load'] ) )
				{
					$data['modal_on_load'] 	= array_merge( $data['modal_on_load'], $element['modal_on_load'] );
				}

				if( ! empty( $parent_class->config['modal_on_load'] ) )
				{
					$data['modal_on_load'] = array_merge( $data['modal_on_load'], $parent_class->config['modal_on_load'] );
				}

				$dataString = AviaHelper::create_data_string( $data );
			}


			$output .= "<div class='avia-modal-group-element' {$dataString}>";

			if( $locked !== true )
			{
				$output .=	'<a class="avia-attach-modal-element-move avia-move-handle">' . __( 'Move', 'avia_framework' ) . '</a>';
				$output .=	'<a class="avia-attach-modal-element-delete avia-delete">' . __( 'Delete','avia_framework' ) . '</a>';
			}

			$output .=		'<div class="avia-modal-group-element-inner">';
			$output .=			$params['innerHtml'];
			$output .=		'</div>';

			if( $locked !== true )
			{
				$output .=	"<textarea data-name='text-shortcode' cols='20' rows='4' name='{$element['id']}'>";
				$output .=		ShortcodeHelper::create_shortcode_by_array( $parent_class->config['shortcode_nested'][0], $content, $args );
				$output .=	'</textarea>';
			}

			$output .= '</div>';

			return $output;
		}
	}
}
