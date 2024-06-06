<?php
namespace aviaBuilder\base;

use \AviaBuilder;
use \AviaHelper;
use \AviaHtmlHelper;
use \avia_font_manager;

/**
 * This class contains functions to create single elements for the modal popup window
 *
 * @author		Günter
 * @since 4.8.9		copied from config-templatebuilder\avia-template-builder\php\class-html-helper.php to allow extending this class by user and customization
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( __NAMESPACE__ . '\aviaModalElements', false ) )
{
	class aviaModalElements extends \aviaBuilder\base\aviaModalBase
	{
		/**
		 * Count all image elements to assign the right attachment id
		 *
		 * @since ???
		 * @var int
		 */
		static $imageCount = 0;

		/**
		 * cache for entries that are already fetched
		 *
		 * @since ???
		 * @var array
		 */
		static $cache = array();

		/**
		 * Empty Element - The heading method renders a text and description only that might allow to describe some following functionallity
		 *
		 * @param array $element		the array holds data like type, value, id, class, description which are necessary to render the whole option-section
		 * @return string				the string returned contains the html code generated within the method
		 */
		static public function heading( array $element )
		{
			return '';
		}

		/**
		 * Returns a single </div> tag
		 *
		 * @since < 4.0
		 * @param array $element
		 * @return string
		 */
		static public function close_div( array $element )
		{
			$output = '</div>';
			return $output;
		}

		/**
		 * Starts a tab container
		 *
		 * @since < 4.0
		 * @param array $element
		 * @return string
		 */
		static public function tab_container( array $element )
		{
			$class = ! empty( $element['class'] ) ? $element['class'] : '';

			$output = "<div class='avia-modal-tab-container {$class}'>";
			return $output;
		}

		/**
		 * Wrapper for better readability
		 *
		 * @since 4.6.4
		 * @param array $element
		 * @return string
		 */
		static public function tab_container_close( array $element )
		{
			return AviaHtmlHelper::close_div( $element );
		}

		/**
		 * Defines a single tab
		 *
		 * @since < 4.0
		 * @param array $element
		 * @return string
		 */
		static public function tab( array $element )
		{
			$output  = '';

			$output .= "<div class='avia-modal-tab-container-inner {$element['class']}' data-tab-name='{$element['name']}'>";
			return $output;
		}

		/**
		 * Wrapper for better readability
		 *
		 * @since 4.6.4
		 * @param array $element
		 * @return string
		 */
		static public function tab_close( array $element )
		{
			return AviaHtmlHelper::close_div( $element );
		}

		/**
		 * Starts a Toggle Container Element
		 *
		 * @since 4.6.4
		 * @param array $element
		 * @return string
		 */
		static public function toggle_container( array $element )
		{
			$all_closed = empty( $element['all_closed'] ) ? 'no' : 'yes';
			$alb_options_toggles = avia_get_option( 'alb_options_toggles', '' );
			$alb_options_toggles_class = ! empty( $alb_options_toggles ) ? 'avia-modal-'. $alb_options_toggles : '';

			$output = "<div class='avia-modal-toggle-container {$alb_options_toggles_class}' data-toggles-closed='{$all_closed}' data-toggles-layout={$alb_options_toggles}>";

			return $output;
		}

		/**
		 * Wrapper for better readability
		 *
		 * @since 4.6.4
		 * @param array $element
		 * @return string
		 */
		static public function toggle_container_close( array $element )
		{
			return AviaHtmlHelper::close_div( $element );
		}

		/**
		 * Defines a single toggle element
		 *
		 * @since 4.6.4
		 * @param array $element
		 * @return string
		 */
		static public function toggle( array $element )
		{
			$output = '';

			$class = ! empty( $element['container_class'] ) ? $element['container_class'] : '';
			$name = esc_attr( $element['name'] );
			$desc = esc_attr( $element['desc'] );

			$output  .=	"<div class='avia-modal-toggle-visibility-wrap {$class}'>";
			$output  .=		"<div class='avia-modal-toggle-container-inner' data-toggle-name='{$name}' data-toggle-desc='{$desc}'>";
			$output  .=			'<div class="avia-toggle-description avia-name-description av-builder-note av-neutral">';
			$output  .=				"<strong>{$element['name']}</strong>";
			$output  .=				"<div>{$element['desc']}</div>";
			$output  .=			'</div>';

			return $output;
		}

		/**
		 * Wrapper also for better readability
		 *
		 * @since 4.6.4
		 * @param array $element
		 * @return string
		 */
		static public function toggle_close( array $element )
		{
			$output = '';

			$output .=		AviaHtmlHelper::close_div( $element );
			$output .=	AviaHtmlHelper::close_div( $element );

			return $output;
		}

		/**
		 * Switcher Container Element
		 *
		 * @since 4.6.4
		 * @param array $element
		 * @param string $parent_class
		 * @param array $dependency
		 * @return string
		 */
		static public function icon_switcher_container( array $element, $parent_class, $dependency )
		{
			$data_string = '';
			$class_string = '';

			if( isset( $element['nodescription'] ) && ( false !== $element['nodescription'] ) )
			{
				extract( $dependency );
			}

			$output = "<div class='avia-modal-iconswitcher-container avia-form-element-container {$class_string}' {$data_string}>";

			if( empty( $element['name'] ) && empty( $element['desc'] ) && empty( $element['desc_html'] ) )
			{
				return $output;
			}

			$output .= '<div class="avia-name-description avia-iconswitcher-name-description">';

			if( ! empty( $element['name'] ) )
			{
				$output .= '<strong>' . esc_html( $element['name'] ) . '</strong>';
			}

			if( ! empty( $element['desc'] ) )
			{
				$output .= '<div>' . esc_html( $element['desc'] ) . '</div>';
			}

			if( ! empty( $element['desc_html'] ) )
			{
				$output .= '<div>' . $element['desc_html'] . '</div>';
			}

			$output .= '</div>';

			return $output;
		}

		/**
		 * Wrapper for better readability
		 *
		 * @since 4.6.4
		 * @param array $element
		 * @return string
		 */
		static public function icon_switcher_container_close( array $element )
		{
			return AviaHtmlHelper::close_div( $element );
		}

		/**
		 * Open container for an icon switcher
		 *
		 * @since 4.6.4
		 * @param array $element
		 * @return string
		 */
		static public function icon_switcher( array $element )
		{
			$output = '';
			$output .= "<div class='avia-modal-iconswitcher-container-inner' data-switcher-name='{$element['name']}' data-switcher-icon='" . AVIA_BASE_URL . "config-templatebuilder/avia-template-builder/images/iconswitcher/{$element['icon']}.png'>";

			return $output;
		}

		/**
		 * Wrapper for better readability
		 *
		 * @since 4.6.4
		 * @param array $element
		 * @return string
		 */
		static public function icon_switcher_close( array $element )
		{
			return AviaHtmlHelper::close_div( $element );
		}


		/**
		 * Empty Element - The heading method renders a text and description only that might allow to describe some following functionallity
		 *
		 * @param array $element		the array holds data like type, value, id, class, description which are necessary to render the whole option-section
		 * @return string				the string returned contains the html code generated within the method
		 */
		static public function hr( array $element )
		{
			$output = "<div class='avia-builder-hr'></div>";
			return $output;
		}

		/**
		 * Checks if a certain option has a required value and if not shows a message and makes options within unclickable
		 *
		 * @param array $element		the array holds data like type, value, id, class, description which are necessary to render the whole option-section
		 * @return string				the string returned contains the html code generated within the method
		 */
		static public function condition( array $element )
		{
			//array('option' => 'header_position', 'compare' => "equal_or_empty", "value"=>"header_top")

			if( isset( $element['condition']['option'] ) )
			{
				$option = avia_get_option( $element['condition']['option'] );
			}
			else
			{
				$key = $element['condition']['element'];
				$option = isset( $element['shortcode_data'][ $key ] ) ? $element['shortcode_data'][ $key ] : '';
			}

			$value = $element['condition']['value'];

			$result = false;
			$class = 'av_option_hidden';
			$overlay = "<div class='av_conditional_overlay_content'>{$element['notice']}</div>";

			switch( $element['condition']['compare'] )
			{
				case 'equals':
					if( $option === $value )
					{
						$result = true;
					}
					break;
				case 'equal_or_empty':
					if( $option === $value || $option == '' )
					{
						$result = true;
					}
					break;
				case 'contains':
					if( strpos( $option, $value ) === false )
					{
						$result = true;
					}
					break;
				case 'not':
					if( $option !== $value )
					{
						$result = true;
					}
					break;
			}

			if( $result )
			{
				$class = 'av_option_visible';
				$overlay = '';
			}

			$output  = "<div class='avia-conditional-elements {$class}'>";
			$output .=		'<div class="av_conditional_overlay"></div>';
			$output .=		$overlay;

			return $output;
		}

		/**
		 *
		 * @param array $element
		 * @return string
		 */
		static public function condition_end( array $element )
		{
			$output = '</div>';

			return $output;
		}

		/**
		 * Adds an action button.
		 * JS action scripts must be added to 'modal_on_load'
		 *
		 * @since 4.8
		 * @param array $element
		 * @return string
		 */
		static public function action_button( array $element )
		{
			static $btn_cnt = 0;

			$btn_cnt++;

			$text = isset( $element['title'] ) ? $element['title'] : sprintf( __( 'Actionbutton %d', ''), $btn_cnt );
			$text_active = isset( $element['title_active'] ) ? $element['title_active'] : '';
			$id = isset( $element['container_id'] ) ? $element['container_id'] : sprintf( __( 'avia-action-button-%d', ''), $btn_cnt );
			$class = isset( $element['class'] ) ? $element['class'] : '';

			$data = ' data-text="' . esc_attr( esc_html( $text ) ) . '"';
			if( ! empty( $text_active ) )
			{
				$data .= ' data-text_active="' . esc_attr( esc_html( $text_active ) ) . '"';
			}

			$post_id = isset( $_REQUEST['post_id'] ) ? $_REQUEST['post_id'] : 0;
			$data .= ' data-post_id="' . $post_id . '"';

			$output  = '';
			$output .= "<div class='button button-primary button-large {$class}' id='{$id}' {$data}>";
			$output .=		esc_html( $text );
			$output .= '</div>';

			return $output;
		}

		/**
		 * The tiny_mce method renders a tiny mce text field, also known as the wordpress visual editor
		 *
		 * @param array $element		the array holds data like type, value, id, class, description which are necessary to render the whole option-section
		 * @return string				the string returned contains the html code generated within the method
		 */
		static public function tiny_mce( array $element )
		{
			$output = '';

			//	is an array !!
			if( isset( $element['locked_value'] ) )
			{
				$val = is_array( $element['locked_value'] ) ? implode( '<br />', $element['locked_value'] ) : $element['locked_value'];

				$output .= '<div class="avia-locked-data-value avia_textblock avia_textblock_style">';
				$output .=		wpautop( trim( html_entity_decode( $val ) ) );
				$output .= '</div>';

				$output .= '<div class="avia-locked-data-hide">';
			}

			//tinymce only allows ids in the range of [a-z] so we need to filter them.
			$element['id']  = preg_replace( '![^a-zA-Z_]!', '', $element['id'] );


			/* monitor this: seems only ajax elements need the replacement */
			$user_ID = get_current_user_id();

			if( get_user_meta( $user_ID, 'rich_editing', true ) == 'true' && isset( $element['ajax'] ) )
			{
				global $wp_version;

				//replace new lines with brs, otherwise the editor will mess up. this was fixed with wp 4.3 so only do that in old versions
				if ( version_compare( $wp_version, '4.3', '<' ) )
				{
					$element['std'] = str_replace( "\n", '<br>', $element['std'] );
				}
			}

			ob_start();
			wp_editor( $element['std'], $element['id'], array( 'editor_class' => 'avia_advanced_textarea avia_tinymce', 'media_buttons' => true ) );
			$output .= ob_get_clean();

			if( isset( $element['locked_value'] ) )
			{
				$output .= '</div>  <!-- close locked data area -->';
			}

			return $output;
		}

		/**
		 * The input method renders one or more input type:text elements, based on the definition of the $elements array
		 *
		 * @param array $element		the array holds data like type, value, id, class, description which are necessary to render the whole option-section
		 * @return string				the string returned contains the html code generated within the method
		 */
		static public function input( array $element )
		{
			$locked_data = '';
			$data = '';

			if( isset( $element['locked_value'] ) )
			{
				$data .= ' data-locked_value="' . $element['locked_value'] . '"';

				$dummy = array(
								'id' 	=> $element['id'] . '_fakeArg',
								'type' 	=> 'input',
								'std'	=> $element['locked_value'],
								'class'	=> 'avia-locked-data-value avia-fake-input',
								'attr'	=> 'readonly="readonly"'
							);

				$locked_data = AviaHtmlHelper::input( $dummy );

				$element['class'] .= ' avia-locked-data-hide';
			}

			$attr = isset( $element['attr'] ) ? $element['attr'] : '';

			$output  = '';

			$output .= $locked_data;
			$output .= '<input type="text" class="' . $element['class'] . '" value="' . nl2br( $element['std'] ) . '" id="' . $element['id'] . '" name="' . $element['id'] . '" ' . $attr . $data . '/>';
			$output .= AviaHtmlHelper::critical_characters_message( $element );

			return $output;
		}

		/**
		 * The divider_preview method renders a div that can be filled with an ajax callback to show a rough svg divider preview
		 *
		 * @param array $element		the array holds data like type, value, id, class, description which are necessary to render the whole option-section
		 * @return string				the string returned contains the html code generated within the method
		 */
		static public function divider_preview( array $element )
		{
			$base_id = isset( $element['base_id'] ) ? $element['base_id'] : '';
			$location = isset( $element['location'] ) ? $element['location'] : 'top';

			if( empty( $base_id ) )
			{
				return '';
			}

			$output  = '';

			$output .= '<div class="avia-svg-divider-section" data-divider_id="' . esc_attr( $base_id ) . '" data-divider_location="' . $location . '">';
			$output .=		'<div class="avia-svg-divider-inner"></div>';
			$output .=		'<div class="avia_loading"></div>';
			$output .= '</div>';

			return $output;
		}

		/**
		 * Additional message to add below input field and textarea to inform user
		 *
		 * @since 4.7.6.4
		 * @param array $element
		 * @return string
		 */
		static protected function critical_characters_message( array $element )
		{
			$chars = Avia_Builder()->critical_modal_charecters();

			if( empty( $chars ) )
			{
				return '';
			}

			$output  = '';

			$output .= '<div class="avia-critical-char-msg">';
			$output .=		sprintf( __( 'Warning: Characters %s might break ALB backend or frontend. <a href="https://kriesi.at/documentation/enfold/intro-to-layout-builder/#using-special-characters" target="_blank" rel="noopener noreferrer">Read more</a> if you want to use them. You can use valid HTML markup.', 'avia_framework' ), implode( ',', Avia_Builder()->critical_modal_charecters() ) );
			$output .= '</div>';

			return $output;
		}

		/**
		 * Return an input field of type number
		 *
		 * @since 4.7.3.1
		 * @param array $element
		 * @return string
		 */
		static public function input_number( array $element )
		{
			$min = isset( $element['min'] ) ? ' min="' . $element['min'] . '"' : '';
			$max = isset( $element['max'] ) ? ' max="' . $element['max'] . '"' : '';
			$placeholder = isset( $element['placeholder'] ) ? ' placeholder="' . $element['placeholder'] . '"' : '';
			$readonly = isset( $element['readonly'] ) ? ' readonly="' . $element['readonly'] . '"' : '';
			$step = isset( $element['step'] ) ? ' step="' . $element['step'] . '"' : '';

			$locked_data = '';
			$data = '';

			if( isset( $element['locked_value'] ) )
			{
				$data .= ' data-locked_value="' . $element['locked_value'] . '"';

				$dummy = array(
								'id' 	=> $element['id'] . '_fakeArg',
								'type' 	=> 'input',
								'std'	=> $element['locked_value'],
								'class'	=> 'avia-locked-data-value avia-fake-input',
								'attr'	=> 'readonly="readonly"'
							);

				$locked_data = AviaHtmlHelper::input( $dummy );

				$element['class'] .= ' avia-locked-data-hide';
			}

			$output  = '';

			$output .= $locked_data;
			$output .= '<input type="number" class="' . $element['class'] . '" value="' . nl2br( $element['std'] ) . '" id="' . $element['id'] . '" name="' . $element['id'] . '" ' . $min . $max . $placeholder . $readonly . $step . $data . '/>';

			return $output;
		}

		/**
		 * The input method renders one or more input type:text elements, based on the definition of the $elements array
		 *
		 * @param array $element		the array holds data like type, value, id, class, description which are necessary to render the whole option-section
		 * @return string				contains the html code generated within the method
		 */
		static public function multi_input( array $element )
		{
			$output = '';
			$count = 0;
			$element['std'] = explode( ',', $element['std'] );
			$value = '';
			$checked = count( array_unique( $element['std'], SORT_STRING ) );

			if( isset( $element['locked_value'] ) )
			{
				$multi_value_result = AviaHelper::multi_value_result_lockable( $element['locked_value'], $element['id'], array_keys( $element['multi'] ) );
				$element['locked_value'] = $multi_value_result['locked_opt_info'];
			}

			$output .= '<div class="av-multi-input-table">';

			foreach( $element['multi'] as $multi => $label )
			{
				$output .= '<div class="av-multi-input-cell">';
				$value   = isset( $element['std'][ $count ] ) ? $element['std'][ $count ] : $value;
				$disable = ( $checked === 1 && $count !== 0 && ! empty( $element['sync'] ) ) ? 'disabled="disabled"' : '';
				$class = $element['class'];

				$output .= ! empty( $label ) ? "<div class='av-multi-input-label'>{$label}</div>" : '';

				$data = '';

				if( isset( $element['locked_value'] ) )
				{
					$locked_val = isset( $element['locked_value'][ $count ] ) ? $element['locked_value'][ $count ] : $value;
					$data .= ' data-locked_value="' . esc_attr( $locked_val ) . '"';

					$output .=	'<input readonly="readonly" type="text" class="avia-locked-data-value avia-fake-input" value="' . nl2br( $locked_val ) . '" id="' . $element['id'] . '_' . $multi . '_fakeArg" name="' . $element['id'] . '_fakeArg"/>';

					$class .= ' avia-locked-data-hide';
				}

				$output .=		'<input ' . $disable . $data . ' type="text" class="' . $class . '" value="' . nl2br( $value ) . '" id="' . $element['id'] . '_' . $multi . '" name="' . $element['id'] . '"/>';
				$output .= '</div>';

				$count ++;
			}

			$output .= '</div>';

			if( isset( $element['sync'] ) && false !== $element['sync'] && ! isset( $element['locked_value'] ) )
			{
				$checked = $checked === 1 ? 'checked="checked"' : '';
				$label	 = __( 'Apply the same value to all?', 'avia_framework' );

				$output .= '<label class="av-multi-input-label-sync">';
				$output .=		'<input ' . $checked . ' type="checkbox" class="' . $element['class'] . '_sync" value="true" id="' . $element['id'] . '_sync" name="' . $element['id'] . '_sync"/>';
				$output .=		$label;
				$output .= '</label>';
			}

			return $output;
		}

		/**
		 * The hidden method renders a single input type:hidden element
		 *
		 * @param array $element		the array holds data like type, value, id, class, description which are necessary to render the whole option-section
		 * @return string				the string returned contains the html code generated within the method
		 */
		static public function hidden( array $element )
		{
			$output = '<input type="hidden" value="' . $element['std'] . '" id="' . $element['id'] . '" name="' . $element['id'] . '"/>';

			return $output;
		}

		/**
		 * The checkbox method renders a single input type:checkbox element
		 *
		 * @todo: fix: checkboxes at metaboxes currently dont work
		 *
		 * @param array $element		the array holds data like type, value, id, class, description which are necessary to render the whole option-section
		 * @return string				the string returned contains the html code generated within the method
		 */
		static public function checkbox( array $element )
		{
			$checked = '';
			$data = '';

			if( $element['std'] != '' )
			{
				$checked = 'checked = "checked"';
			}

			if( isset( $element['std_fakeArg'] ) && $element['std_fakeArg'] != '' )
			{
				$checked = 'checked = "checked"';
			}

			$attr = isset( $element['attr'] ) ? $element['attr'] : '';
			$title = isset( $element['tooltip'] ) ? ' title="' . esc_attr( $element['tooltip'] ) . '" ' : '';

			$locked_data = '';

			if( isset( $element['locked_value'] ) )
			{
				$data .= ' data-locked_value="' . $element['locked_value'] . '"';

				$dummy = array(
								'id' 	=> $element['id'] . '_fakeArg',
								'type' 	=> 'checkbox',
								'std'	=> $element['locked_value'],
								'class'	=> 'avia-locked-data-value avia-fake-input',
								'attr'	=> 'disabled="disabled"'
							);

				$locked_data = AviaHtmlHelper::checkbox( $dummy );

				$element['class'] .= ' avia-locked-data-hide';
			}

			$output  = '';

			$output .= $locked_data;
			$output .= '<input '. $checked . ' type="checkbox" class="' . $element['class'] . '" value="' . $element['id'] . '" id="' . $element['id'] . '" name="' . $element['id'] . '" ' . $attr . $data . $title . '/>';

			return $output;
		}

		/**
		 * The textarea method renders a single textarea element
		 *
		 * @param array $element		the array holds data like type, value, id, class, description which are necessary to render the whole option-section
		 * @return string				the string returned contains the html code generated within the method
		 */
		static public function textarea( array $element )
		{
			$output  = '';
			$locked_data = '';

			if( isset( $element['locked_value'] ) )
			{
				$value = is_array( $element['locked_value'] ) ? implode( "\n", $element['locked_value'] ) : $element['locked_value'];

//				$data .= ' data-locked_value="' . esc_attr( $value ) . '"';

				$dummy = array(
								'id' 	=> $element['id'] . '_fakeArg',
								'type' 	=> 'textarea',
								'std'	=> $value,
								'class'	=> 'avia-locked-data-value avia-fake-input',
								'attr'	=> 'readonly="readonly"'
							);

				$locked_data = AviaHtmlHelper::textarea( $dummy );

				$element['class'] .= ' avia-locked-data-hide';
			}

			$attr = isset( $element['attr'] ) ? $element['attr'] : '';

			$output .= $locked_data;
			$output .= '<textarea rows="5" cols="30" class="' . $element['class'] . '" id="' . $element['id'] . '" name="' . $element['id'] . '" ' . $attr . '>';
			$output .=		rtrim( $element['std'] );
			$output .= '</textarea>';

			$output .= AviaHtmlHelper::critical_characters_message( $element );

			return $output;
		}

		/**
		 * The iconfont method renders a single icon-select element based on a font
		 *
		 * @param array $element		the array holds data like type, value, id, class, description which are necessary to render the whole option-section
		 * @return string				the string returned contains the html code generated within the method
		 */
		static public function iconfont( array $element )
		{
			static $count = 0;

			$count++;

			$data = '';
			$locked_data = '';
			$charcode_prefix = __( 'Charcode:', 'avia_framework' ) . ' \\';

			$output = '';

			if( isset( $element['locked_value'] ) )
			{
				$locked_value = $element['locked_value'];
				$data .= ' data-locked_value="' . esc_attr( $element['locked_value'] ) . '"';
				$element['class'] .= ' avia-locked-data-hide';

				$locked_data .= '<div class="avia_icon_select_container_locked">';

				if( ! empty( $locked_value ) )
				{
					$locked_value = json_decode( $locked_value );
					$locked_char = avia_font_manager::get_display_char( $locked_value[0], $locked_value[1] );

					$locked_data .=		"<span title='{$charcode_prefix}{$locked_value[0]}' class='avia-locked-data-value avia_icon_preview avia-font-{$locked_value[1]}'>{$locked_char}</span>";
				}

				$locked_data .= '</div>';
			}

			$output .= $locked_data;

			if( ! empty( $element['chars'] ) && is_array( $element['chars'] ) )
			{
				$chars = $element['chars'];
			}
			else
			{
				$chars = avia_font_manager::load_charlist();
			}

			/**
			 *
			 * @since 5.6.11
			 * @param boolean $supress_filter
			 * @param array $element
			 * @return boolean
			 */
			$supress_filter = apply_filters( 'avf_icon_font_filter_suppress', false, $element );

			if( false === $supress_filter )
			{
				$name = 'avia-icon-filter-' . $count;
				$placeholder = esc_attr( __( 'Enter string to filter icons', 'avia_framework' ) );

				$filter  = '<div class="av-icon-filter-container">';
				$filter .=		"<label for='{$name}'>" .  __( 'Filter Icons By Font Family And/Or Name/Charactercode:', 'avia_framework' ) . "</label>";
				$filter .=		"<select name='{$name}-select' id='{$name}-select' class='av-icon-filter-select avia_ignore_on_save'>";
				$filter .=			'<option value="" selected="selected">' . __( 'All iconfonts', 'avia_framework' ) . '</option>';

				foreach( $chars as $font => $charset )
				{
					$filter .=		"<option value='{$font}'>{$font}</option>";
				}

				$filter .=		'<select>';
				$filter .=		"<input type='text' class='av-icon-filter-input avia_ignore_on_save' value='' name='{$name}-input' id='{$name}-input' placeholder='{$placeholder}' />";
				$filter .= '</div>';

				$output .= $filter;
			}

			//get either the passed font or the default font
			$std_font = isset( $element['shortcode_data']['font'] ) ? $element['shortcode_data']['font'] : key( AviaBuilder::$default_iconfont );

			$output .= "<div class='avia_icon_select_container avia-attach-element-container {$element['class']}' {$data}>";

			$run = 0;
			$active_font = '';

			foreach( $chars as $font => $charset )
			{
				$run ++;
				asort( $charset );

				if( $run === 1 )
				{
					//if the el value is empty set it to the first char
					if( empty( $element['std'] ) )
					{
						$element['std'] = key( $charset );
					}

					$standard = avia_font_manager::get_display_char( $element['std'], $std_font );
				}

				$output .= "<div class='av-iconselect-heading' data-element-font='{$font}'>Font: {$font}</div>";

				foreach( $charset as $key => $char )
				{
					$char = avia_font_manager::try_decode_icon( $char );
					$char_name = avia_font_manager::get_char_name( $key, $font );

					$active_char = '';

					if( $char == $standard && ! empty( $std_font ) && $std_font == $font )
					{
						$active_char = 'avia-active-element';
						$active_font = $font;
					}

					$output .= "<span title='{$char_name}' data-element-nr='{$key}' data-element-name='{$char_name}' data-element-font='{$font}' class='avia-attach-element-select avia_icon_preview avia-font-{$font} {$active_char}'>{$char}</span>";
				}
			}

			//default icon value
			$output .= AviaHtmlHelper::hidden( $element );

			//fake character value needed for backend editor
			$element['id'] = $element['id'] . "_fakeArg";
			$element['std'] = empty( $standard ) ? '' : $standard;

			$output .= AviaHtmlHelper::hidden( $element );

			//font value needed for backend and editor
			$element['id'] = "font";
			$element['std'] = $active_font;
			$element = AviaHtmlHelper::ajax_modify_id( $element );

			$output .= AviaHtmlHelper::hidden( $element );

			$output .= '</div>';

			return $output;
		}

		/**
		 * The colorpicker method renders a colorpicker element that allows you to select a color of your choice
		 *
		 * @param array $element		the array holds data like type, value, id, class, description which are necessary to render the whole option-section
		 * @return string				the string returned contains the html code generated within the method
		 */
		static public function colorpicker( array $element )
		{
			$data = '';

			if( ! empty( $element['rgba'] ) )
			{
				$data .= "data-av-rgba='1'";
			}

			$locked_data = '';
			$container = '';

			if( isset( $element['locked_value'] ) )
			{
				$data .= ' data-locked_value="' . $element['locked_value'] . '"';

				$style = '';
				if( $element['locked_value'] != '' )
				{
					//	Create a font color to be visible on selected color
					$font_color = avia_backend_counter_color( $element[ 'locked_value'] );

					$style = ' style="background-color:' . $element['locked_value'] . '; color:' . $font_color . '; text-align: center;"';
				}

				$dummy = array(
								'id' 	=> $element['id'] . '_fakeArg',
								'type' 	=> 'input',
								'std'	=> $element['locked_value'],
								'class'	=> 'avia-locked-data-value avia-fake-input',
								'attr'	=> 'readonly="readonly"' . $style
							);

				$locked_data = AviaHtmlHelper::input( $dummy );

				$container = ' avia-locked-data-hide';
			}

			$output  = '';

			$output .= $locked_data;
			$output .= '<div class="' . $container . '">';
			$output .=		'<input type="text" class="av-colorpicker ' . $element['class'] . '" value="' . $element['std'] . '" id="' . $element['id'] . '" name="' . $element['id'] . '" ' . $data . ' />';
			$output .= '</div>';

			return $output;
		}

		/**
		 * The datepicker method renders a datepicker element that allows you to select a date of your choice.
		 * See http://api.jqueryui.com/datepicker/ for possible parameters and values. If you want to extend parmeters also adjust list in
		 * enfold\config-templatebuilder\avia-template-builder\assets\js\avia-modal.js function $.AviaModal.register_callback.modal_load_datepicker.
		 *
		 * Add parameters to modify to array 'dp_params' when defining the datepicker in popup editor.
		 * Values are rendered 1:1 with js. Arrays must be defined as array.
		 * Default parameters do not need to be set.
		 *
		 * @since < 4.0
		 * @modified 4.5.6.1  by Günter
		 * @param array $element			the array holds data like type, value, id, class, description which are necessary to render the whole option-section
		 * @return string					the string returned contains the html code generated within the method
		 */
		static public function datepicker( array $element )
		{
			global $wp_locale;

			/**
			 * Default values are set to be backwards comp. before
			 */
			$args = array(
						'showButtonPanel'	=> false,
						'closeText'         => __( 'Close', 'avia_framework' ),
						'currentText'       => __( 'Today', 'avia_framework' ),
						'nextText'			=> __( 'Next', 'avia_framework' ),
						'prevText'			=> __( 'Prev', 'avia_framework' ),
						'monthNames'        => array_values( $wp_locale->month ),
						'monthNamesShort'   => array_values( $wp_locale->month_abbrev ),
						'dayNames'          => array_values( $wp_locale->weekday ),
						'dayNamesShort'     => array_values( $wp_locale->weekday_abbrev ),
						'dayNamesMin'       => array_values( $wp_locale->weekday_initial ),
						'dateFormat'        => 'mm / dd / yy',
						'firstDay'          => get_option( 'start_of_week' ),
						'isRTL'             => $wp_locale->is_rtl(),
						'changeMonth'		=> false,
						'changeYear'		=> false,
//						'minDate'			=> -0,						//	'mm / dd / yy' | number
//						'maxDate'			=> '',						//
//						'yearRange'			=> "c-80:c+10",
//						'container_class'	=> ''						//	'select_dates_30' | ''
					);

			if( is_array( $element['dp_params'] ) )
			{
				$args = array_merge( $args, $element['dp_params'] );
			}

			$output = '';
			$locked_data = '';

			if( isset( $element['locked_value'] ) )
			{
				$args['data-locked_value'] = $element['locked_value'];

				$dummy = array(
								'id' 	=> $element['id'] . '_fakeArg',
								'type' 	=> 'input',
								'std'	=> $element['locked_value'],
								'class'	=> 'avia-locked-data-value avia-fake-input',
								'attr'	=> 'readonly="readonly"'
							);

				$locked_data = AviaHtmlHelper::input( $dummy );

				$element['class'] .= ' avia-locked-data-hide';
			}

			$data_params = AviaHelper::create_data_string( $args );

			$output .= $locked_data;
			$output .= '<input type="text" class="av-datepicker av-no-autoselect ' . $element['class'] . '" value="' . $element['std'] . '" id="' . $element['id'] . '" name="' . $element['id'] . '" ' . $data_params . ' />';

			return $output;
		}


		/**
		 * The linkpicker method renders a linkpicker element that allows you to select a link to a post type or taxonomy type of your choice
		 *
		 * Supported:
		 *			'' | 'manually' | 'single' | 'taxonomy' | 'lightbox'
		 *
		 * @since < 4.0
		 * @param array $element		the array holds data like type, value, id, class, description which are necessary to render the whole option-section
		 * @return string $output		the string returned contains the html code generated within the method
		 *
		 * @todo: currently only one linkpicker per modal window possible
		 */
		static public function linkpicker( array $element )
		{
			$output = '';
			$locked_data = '';

			if( isset( $element['locked_value'] ) )
			{
				$locked_value = AviaHelper::get_url_info( $element['locked_value'] );

				$dummy = array(
								'id' 	=> $element['id'] . '_fakeArg',
								'class'	=> 'avia-locked-data-value avia-fake-input',
								'attr'	=> 'readonly="readonly"',
								'required'	=> isset( $element['required'] ) ? $element['required'] : array()
							);

				if( is_array( $locked_value ) )
				{
					$dummy['type'] = 'textarea';
					$dummy['std'] = implode( "\n", $locked_value );

					$locked_data = AviaHtmlHelper::textarea( $dummy );
				}
				else
				{
					$dummy['type'] = 'input';
					$dummy['std'] = $locked_value;

					$locked_data = AviaHtmlHelper::input( $dummy );
				}

				$element['class'] .= ' avia-locked-data-hide';

				unset( $element['lockable'] );
				unset( $element['locked_value'] );
			}

			$output .= $locked_data;

			//fallback for previous default input link elements: convert a https://kriesi.at value to a manually entry
			if( strpos( $element['std'], 'http://' ) === 0 )
			{
				$element['std'] = 'manually,' . $element['std'];
			}

			/**
			 * necessary for installations with thousands of posts
			 *
			 * @since 4.8.6.3
			 * @param string $limit
			 * @return string
			 */
			@ini_set( 'memory_limit', apply_filters( 'avf_alb_linkpicker_memory_limit', '512M' ) );

			$original = $element;
			$new_std = explode( ',', $element['std'], 2 );

			$pt = array_flip( AviaHelper::public_post_types());
			$ta = array_flip( AviaHelper::public_taxonomies( false, true ) );

			if( isset( $new_std[1] ) )
			{
				$original['std'] = $new_std[1];
			}

			$allowed_pts = isset( $original['posttype'] ) ? $original['posttype'] : $pt;
			$allowed_tas = isset( $original['taxtype'] ) ? $original['taxtype'] : $ta;

			if( in_array( 'single', $element['subtype'] ) )
			{
				foreach( $pt as $key => $type )
				{
					if( in_array( $type, $allowed_pts ) )
					{
						$html = AviaHtmlHelper::select_hierarchical_post_types( $element, $type );

						if( false === $html )
						{
							$original['subtype'] = $type;
							$html = AviaHtmlHelper::select( $original );
						}

						if( ! empty( $html ) )
						{
							AviaHelper::register_template( $original['id'] . '-' . $type, $html );
						}
						else
						{
							unset( $pt[ $key ] );
						}
					}
					else
					{
						unset( $pt[ $key ] );
					}
				}
			}

			if( in_array( 'taxonomy', $element['subtype'] ) )
			{
				foreach( $ta as $key => $type )
				{
					if( in_array( $type, $allowed_tas ) )
					{
						$html = AviaHtmlHelper::select_hierarchical_taxonomy( $element, $type );

						if( false === $html )
						{
							$original['subtype'] = 'cat';
							$original['taxonomy'] = $type;

							$html = AviaHtmlHelper::select( $original );
						}

						if( ! empty( $html ) )
						{
							AviaHelper::register_template( $original['id'] . '-' . $type, $html );
						}
						else
						{
							unset( $ta[ $key ] );
						}
					}
					else
					{
						unset( $ta[ $key ] );
					}
				}
			}

			if( isset( $new_std[1] ) )
			{
				$element['std'] = $new_std[1];
			}

			$original['subtype'] = array();

			foreach( $element['subtype'] as $value => $key ) //register templates
			{
				switch( $key )
				{
					case 'manually':
						if( $new_std[0] != $key )
						{
							$element['std'] = 'http://';
						}
						$original['subtype'][ $value ] = $key;
						$html = AviaHtmlHelper::input( $element );
						AviaHelper::register_template( $original['id'] . '-' . $key, $html );
						break;
					case 'single':
						$original['subtype'][ $value ] = $pt;
						break;
					case 'taxonomy':
						$original['subtype'][ $value ] = $ta;
						break;
					default:
						$original['subtype'][ $value ] = $key;
						break;
				}
			}

			// if we got an ajax request we also need to call the printing since the default wordpress hook is already executed
			if( ! empty( $element['ajax'] ) )
			{
				AviaHelper::print_templates();
			}

			$original['std'] = $new_std[0];
			unset( $original['multiple'] );

			$output .= AviaHtmlHelper::select( $original );

			return $output;
		}

		/**
		 * The gallery method renders an image upload button that allows the user to select an image from the media uploader and insert it
		 *
		 * @since 5.5
		 * @param array $element		the array holds data like type, value, id, class, description which are necessary to render the whole option-section
		 * @return string				the string returned contains the html code generated within the method
		 */
		static public function lottie_animation( array $element )
		{
			if( empty( $element['data'] ) )
			{
				$element['data'] = array(
									'target'	=> $element['id'],
									'title'		=> $element['title'],
									'type'		=> $element['type'],
									'button'	=> $element['button'],
									'class'		=> 'media-frame avia-media-lottie-file ' . ' ' . $element['container_class'],
									'frame'		=> 'select',
									'state'		=> 'avia_insert_lottie',
//									'state_edit'	=> 'gallery-edit',
									'fetch'		=> 'url',
									'save_to'	=> 'hidden'
								);
			}

			return AviaHtmlHelper::image( $element );
		}

		/**
		 * The image method renders an image upload button that allows the user to select an image from the media uploader and insert it.
		 *
		 * Locking:
		 *
		 * @param array $element		the array holds data like type, value, id, class, description which are necessary to render the whole option-section
		 * @return string				the string returned contains the html code generated within the method
		 */
		static public function image( array $element )
		{
			$output = '';

			if( isset( $element['locked_value'] ) )
			{
				$dummy = $element;

				switch( $element['type'] )
				{
					case 'gallery':
						$dummy['std'] = $element['locked_value'];
						$dummy['is_locked_area'] = true;
						break;
					case 'video':
						$dummy['std'] = $element['locked_value'];
						$dummy['attr'] = 'readonly="readonly"';
						$dummy['is_locked_area'] = true;
						break;
					case 'image':
					default:
						if( isset( $element['fetch'] ) && $element['fetch'] == 'id' )
						{
							$dummy['std'] = $element['locked_value'];
							$dummy['shortcode_data']['id'] = $element['locked_value'];
						}
						else
						{
							$locked_value = ! empty( $element['locked_value'] ) ? json_decode( $element['locked_value'] ) : array( '', '', '' );
							$dummy['src'] = isset( $locked_value[0] ) ? $locked_value[0] : '';
							$dummy['attachment'] = isset( $locked_value[1] ) ? $locked_value[1] : '';
							$dummy['attachment_size'] = isset( $locked_value[2] ) ? $locked_value[2] : '';
							$dummy['shortcode_data']['attachment'] = $dummy['attachment'];
							$dummy['shortcode_data']['attachment_size'] = $dummy['attachment_size'];
							$dummy['is_locked_area'] = true;
						}
						break;
				}

				$dummy['id'] = $element['id'] . '_fakeArg';
				$dummy['class'] .= ' avia-locked-data-value avia-fake-input';

				unset( $dummy['locked_value'] );

				$save_imageCount = AviaHtmlHelper::$imageCount;

				$locked_data = AviaHtmlHelper::image( $dummy );

				AviaHtmlHelper::$imageCount = $save_imageCount;

				$output .= $locked_data;

				$element['container_div_class'] = ' avia-locked-data-hide';
			}

			//	wrap content in a div to hide it because images are replaced by locked ones
			if( isset( $element['container_div_class'] ) )
			{
				$output .= '<div class="' . $element['container_div_class'] . '">';
			}

			if( empty( $element['data'] ) )
			{
				$fetch = isset( $element['fetch'] ) ? $element['fetch'] : 'url';
				$state = isset( $element['state'] ) ? $element['state'] : 'avia_insert_single';

				if( empty( $element['show_options'] ) )
				{
					$class = $fetch == 'id' ? 'avia-media-img-only-no-sidebars' : 'avia-media-img-only';
				}
				else if( $element['show_options'] == true )
				{
					$class = 'avia-media-img-only';
				}

				$element['data'] = array(
										'target' => $element['id'],
										'title'  => $element['title'],
										'type'   => $element['type'],
										'button' => $element['button'],
										'class'  => 'media-frame ' . $class . ' ' . $element['container_class'],
										'frame'  => 'select',
										'state'  => $state,
										'fetch'  => $fetch,
										'save_to'=> 'hidden'
									);
			}
			else
			{
				$fetch = isset( $element['data']['fetch'] ) ? $element['data']['fetch'] : 'url';
			}

			if( isset( $element['modal_class'] ) )
			{
				$element['data']['class'] .= ' ' . $element['modal_class'];
			}

			$data 	= AviaHelper::create_data_string( $element['data'] );
			$class 	= 'button aviabuilder-image-upload avia-builder-image-insert ' . $element['class'];

			//	fallback prior 5.5 where button not used
			$btn_text = ! empty( $element['button'] ) ? $element['button'] : $element['title'];

			$output .= '<a href="#" class="' . $class . '" ' . $data . ' title="' . esc_attr( $btn_text ) . '">' . $btn_text . '</a>';

			if( isset( $element['delete'] ) )
			{
				$output .= '<a href="#" class="button avia-delete-gallery-button" title="' . esc_attr( $element['delete'] ) . '">' . $element['delete'] . '</a>';
			}

			$attachmentids = ! empty( $element['shortcode_data']['attachment'] ) ? explode( ',', $element['shortcode_data']['attachment'] ) : array();
			$attachmentid = ! empty( $attachmentids[ AviaHtmlHelper::$imageCount ] ) ? $attachmentids[ AviaHtmlHelper::$imageCount ] : '';

			$attachmentsizes = ! empty( $element['shortcode_data']['attachment_size'] ) ? explode( ',', $element['shortcode_data']['attachment_size'] ) : array();
			$attachmentsize = ! empty( $attachmentsizes[ AviaHtmlHelper::$imageCount ] ) ? $attachmentsizes[ AviaHtmlHelper::$imageCount ] : '';

			//get image based on id if possible - use the force_id_fetch param in conjunction with the secondary_img when you need a secondary image based on id and not on url like pattern overlay. size of a secondary image can not be stored. tab section element is a working example
			if( ! empty( $attachmentid ) && ! empty( $attachmentsize ) && empty( $element['force_id_fetch'] ) )
			{
				$fake_img = wp_get_attachment_image( $attachmentid, $attachmentsize );
				$url = wp_get_attachment_image_src( $attachmentid, $attachmentsize );
				$url = ! empty( $url[0] ) ? $url[0] : '';
			}
			else if( isset( $fetch ) && $fetch == 'id' )
			{
				$fake_img 	= wp_get_attachment_image( $element['std'], 'thumbnail' );

				if( false === strpos( $element['std'], ',' ) )
				{
					$url = wp_get_attachment_image_src( $element['std'], 'thumbnail' );
					$url = ! empty( $url[0] ) ? $url[0] : '';
				}
				else
				{
					$url = $element['std'];
				}
			}
			else
			{
				$fake_img = '<img src="' . $element['std'] . '" />';
				$url = $element['std'];
			}

			if( $element['type'] == 'lottie_animation' )
			{
//				$fake_img 	= wp_get_attachment_image( $attachmentid, 'full' );
//				$url		= wp_get_attachment_image_src( $attachmentid, 'full' );
//				$url		= ! empty( $url[0] ) ? $url[0] : '';

				$fake_img = '';
				$url = ! empty( $element['shortcode_data']['src'] ) ? $element['shortcode_data']['src'] : '';

				$output .= AviaHtmlHelper::display_lottie_preview( $url );
			}
			else if( $element['type'] != 'video' )
			{
				$output .= AviaHtmlHelper::display_image( $url );
			}

			//$output .= AviaHtmlHelper::$element['data']['save_to']($element);
			$output .= call_user_func( array( 'self', $element['data']['save_to'] ), $element );

			//fake img for multi_image element
			if( isset( $fetch ) && ! isset( $element['is_locked_area'] ) )
			{
				$fake_img_id = str_replace( str_replace( 'aviaTB', '', $element['id'] ), 'img_fakeArg', $element['id'] );
				$img_id_field = str_replace( str_replace( 'aviaTB', '', $element['id'] ), 'attachment', $element['id'] );
				$img_size_field = str_replace( str_replace( 'aviaTB', '', $element['id'] ), 'attachment_size', $element['id'] );

				$output .= '<input type="hidden" class="hidden-image-url ' . $element['class'] . '" value="' . htmlentities( $fake_img, ENT_QUOTES, get_bloginfo( 'charset' ) ) . '" id="' . $fake_img_id . '" name="' . $fake_img_id . '"/>';

				/**
				 * added 5.6 - broken mobile_image in video element because multiple attachment, attachment_size
				 * https://github.com/KriesiMedia/wp-themes/issues/4104
				 */
				if( $fetch == 'url' && empty( $element['secondary_img'] ) && $element['type'] != 'video' )
				{
					$output .= '<input type="hidden" class="hidden-attachment-id ' . $element['class'] . '" value="' . $attachmentid . '" id="' . $img_id_field . '" name="' . $img_id_field . '"/>';
					$output .= '<input type="hidden" class="hidden-attachment-size ' . $element['class'] . '" value="' . $attachmentsize . '" id="' . $img_size_field . '" name="' . $img_size_field . '"/>';
				}
			}

			if( empty( $element['force_id_fetch'] ) )
			{
				if( $element['type'] != 'video' )
				{
					AviaHtmlHelper::$imageCount++;
				}
			}

			if( isset( $element['container_div_class'] ) )
			{
				$output .= '</div>';
			}

			return $output;
		}

		/**
		 * The gallery method renders an image upload button that allows the user to select an image from the media uploader and insert it
		 *
		 * @param array $element		the array holds data like type, value, id, class, description which are necessary to render the whole option-section
		 * @return string				the string returned contains the html code generated within the method
		 */
		static public function gallery( array $element )
		{
			if( empty( $element['data'] ) )
			{
				$element['data'] = array(
									'target'	=> $element['id'],
									'title'		=> $element['title'],
									'type'		=> $element['type'],
									'button'	=> $element['button'],
									'class'		=> 'media-frame avia-media-gallery-insert ' . $element['container_class'],
									'frame'		=> 'post',
									'state'		=> 'gallery-library',
									'state_edit'	=> 'gallery-edit',
									'fetch'		=> 'id',
									'save_to'	=> 'hidden'
								);
			}

			return AviaHtmlHelper::image( $element );
		}

		/**
		 * The File Download method renders a file upload button that allows the user to select a file from the media uploader and insert it
		 *
		 * @since 5.7
		 * @param array $element		the array holds data like type, value, id, class, description which are necessary to render the whole option-section
		 * @return string				the string returned contains the html code generated within the method
		 */
		static public function file_download( array $element )
		{
			if( empty( $element['data'] ) )
			{
				$supported = [ 'image', 'video', 'audio', 'text/plain', 'text/csv', 'application/pdf', 'application/zip', 'application/lottiefiles' ];

				/**
				 * Filter supported file types to show in media gallery popup
				 *
				 * @since 5.7
				 * @param array $supported
				 * @param array $element
				 * @return array
				 */
				$supported = apply_filters( 'avf_file_download_supported_file_types', $supported, $element );

				$element['data'] = array(
									'target'	=> $element['id'],
									'title'		=> $element['title'],
									'type'		=> $element['type'],
									'button'	=> $element['button'],
									'class'		=> 'media-frame avia-file-download ' . $element['container_class'],
									'state'		=> 'avia_insert_file_download',
									'frame'		=> 'select',
									'fetch'		=> 'url',
									'save_to'	=> 'input',
									'download'	=> $supported
								);
			}

			return AviaHtmlHelper::image( $element );
		}

		/**
		 * The video method renders a video upload button that allows the user to select an video from the media uploader and insert it
		 *
		 * @param array $element		the array holds data like type, value, id, class, description which are necessary to render the whole option-section
		 * @return string				the string returned contains the html code generated within the method
		 */
		static public function video( array $element )
		{
			if( empty( $element['data'] ) )
			{
				$element['data'] = array(
									'target'	=> $element['id'],
									'title'		=> $element['title'],
									'type'		=> $element['type'],
									'button'	=> $element['button'],
									'class'		=> 'media-frame avia-blank-insert ' . $element['container_class'],
									'state'		=> 'avia_insert_video',
									'frame'		=> 'select',
									'fetch'		=> 'url',
									'save_to'	=> 'input'
								);
			}

			return AviaHtmlHelper::image( $element );
		}

		/**
		 * The audio method renders an audio upload button that allows the user to select one or more audio files from the media uploader
		 * and insert it
		 *
		 * @param array $element		the array holds data like type, value, id, class, description which are necessary to render the whole option-section
		 * @return string				the string returned contains the html code generated within the method
		 */
		static public function audio_player( array $element )
		{
			if( empty( $element['data'] ) )
			{
				$fetch = isset( $element['fetch'] ) ? $element['fetch'] : 'template_audio';
				$state = isset( $element['state'] ) ? $element['state'] : 'avia_insert_multi_audio';

				$class = $fetch == 'template' ? 'avia-media-img-only-no-sidebars' : 'avia-media-img-only';

				$element['data'] = array(
									'target'		=> $element['id'],
									'title'			=> $element['title'],
									'type'			=> $element['type'],
									'button'		=> $element['button'],
									'class'			=> 'media-frame avia-media-audio-insert ' . $element['container_class'],
									'frame'			=> 'post',
									'state'			=> 'playlist-library',
									'state_edit'	=> 'playlist-edit',
									'fetch'			=> $fetch,
									'save_to'		=> 'html',
									'media_type'	=> 'audio'
								);
			}

			$data = AviaHelper::create_data_string( $element['data'] );

			$class = 'button aviabuilder-image-upload avia-builder-image-insert avia-builder-audio-edit ' . $element['class'];

			//	fallback prior 5.5 where button not used
			$btn_text = ! empty( $element['button'] ) ? $element['button'] : $element['title'];

			$output	 = '';
			$output .= '<a href="#" class="' . $class . '" ' . $data . ' title="' . esc_attr( $btn_text ) . '">';
			$output .=		'<span class="wp-media-buttons-icon"></span>';
			$output .=		$btn_text;
			$output .= '</a>';

			return $output;
		}

		/**
		 * The multi_image method allows us to insert many images into a modal template at once
		 *
		 * @param array $element		the array holds data like type, value, id, class, description which are necessary to render the whole option-section
		 * @return string				the string returned contains the html code generated within the method
		 */
		static public function multi_image( array $element )
		{
			if( empty( $element['data'] ) )
			{
				$fetch = isset( $element['fetch'] ) ? $element['fetch'] : 'template';
				$state = isset( $element['state'] ) ? $element['state'] : 'avia_insert_multi';

				if( empty( $element['show_options'] ) )
				{
					$class = $fetch == 'template' ? 'avia-media-img-only-no-sidebars' : 'avia-media-img-only';
				}
				else if( $element['show_options'] == true )
				{
					$class = 'avia-media-img-only';
				}

				$element['data'] =  array(
										'target'	=> $element['id'],
										'title'		=> $element['title'],
										'type'		=> $element['type'],
										'button'	=> $element['button'],
										'class'		=> 'media-frame ' . $class . ' ' . $element['container_class'],
										'frame'		=> 'select',
										'state'		=> $state,
										'fetch'		=> $fetch,
									);
			}

			$data = AviaHelper::create_data_string( $element['data'] );

			$class = 'button aviabuilder-image-upload avia-builder-image-insert '.$element['class'];

			//	fallback prior 5.5 where button not used
			$btn_text = ! empty( $element['button'] ) ? $element['button'] : $element['title'];

			$output  = '';
			$output .= '<a href="#" class="' . $class . '" ' . $data . ' title="' . esc_attr( $btn_text ) . '">';
			$output .=		'<span class="wp-media-buttons-icon"></span>';
			$output .=		$btn_text;
			$output .= '</a>';

			return $output;
		}


		/**
		 * Return an indented dropdown list for hierarchical post types
		 *
		 * @since 4.2.7
		 * @added_by Günter
		 * @param array $element
		 * @param string $post_type
		 * @return string|false
		 */
		static public function select_hierarchical_post_types( array $element, $post_type = 'page' )
		{
			$defaults = array(
								'id'				=> '',
								'std'				=> array( '', 0 ),
								'label'				=> false,			//	for option group
								'class'				=> '',
								'hierarchical'		=> 'yes',			//	'yes' | 'no'
								'post_status'		=> 'publish',		//	array or separated by comma
								'option_none_text'	=> '',				//	text to display for "Nothing selected"
								'option_none_value'	=> '',				//	value for 'option_none_text'
								'option_no_change'	=> ''				//	value for 'no change' - set to -1 by WP default
							);

			$element = array_merge( $defaults, $element );


			/**
			 * return, if element should not display a hierarchical structure
			 */
			if( 'no' == $element['hierarchical'] )
			{
				return false;
			}

			/**
			 * wp_dropdown_pages() does not support multiple selection by default.
			 * Would need to overwrite Walker_PageDropdown to add this feature.
			 *
			 * Can be done in future if necessary.
			 */
			if( isset( $element['multiple'] ) )
			{
				return false;
			}

			$post_type_object = get_post_type_object( $post_type );

			if ( ! ( $post_type_object instanceof WP_Post_Type && $post_type_object->hierarchical ) )
			{
				return false;
			}

			/**
			 * If too many entries limit output and only show non hierarchical
			 *
			 * @since 4.2.7
			 */
			$limit = apply_filters( 'avf_dropdown_post_number', 4000, $post_type, $element, 'alb_select_hierarchical' );
			$count = wp_count_posts( $post_type );
			if( ! isset( $count->publish ) || ( $count->publish > $limit ) )
			{
				return false;
			}

			/**
			 * Make sure we have no spaces
			 */
			$post_status = is_array( $element['post_status'] ) ? $element['post_status'] : explode( ',', (string) $element['post_status'] );
			$element['post_status'] = array_map( function( $value ) { $value = trim($value); return $value;}, $post_status );


			$new_std = explode( ',', $element['std'], 2 );
			$selected = ( ( $new_std[0] == $post_type ) && isset( $new_std[1] ) ) ? $new_std[1] : 0;

			/**
			 * @used_by				config-wpml\config.php	avia_wpml_alb_options_select_hierarchical_post_type_id()					10
			 *
			 * @since 4.5.7.2
			 * @param int $selected
			 * @param string $post_type
			 * @param array $new_std
			 * @param array $element
			 * @return int
			 */
			$selected = apply_filters( 'avf_alb_options_select_hierarchical_post_type_id', $selected, $post_type, $new_std, $element );

			$data_string = '';
			if( isset( $element['data'] ) )
			{
				foreach( $element['data'] as $key => $data )
				{
					$data_string .= " data-{$key}='{$data}'";
				}
			}

			$multi = $multi_class = '';
			if( isset( $element['multiple'] ) )
			{
				$multi_class = ' avia_multiple_select';
				$multi = ' multiple="multiple" size="' . $element['multiple'] . '" ';
			}

			$dropdown_args = array(
							'post_type'				=> $post_type,
							'exclude_tree'			=> false,
							'selected'				=> $selected,
							'name'					=> $element['id'],
							'id'					=> $element['id'],
							'show_option_none'		=> $element['option_none_text'],
							'option_none_value'		=> $element['option_none_value'],
							'show_option_no_change' => $element['option_no_change'],
							'sort_column'			=> 'post_title',
							'echo'					=> 0,
							'class'					=> $element['class'] . $multi_class,
							'post_status'			=> $element['post_status']
					//		'depth'					=> 0,
					//		'child_of'				=> 0,
					//		'value_field'			=> 'ID',
							);

			/**
			 * Allow to add info for non public post status. We need to remove default WP filter as get_current_screen() returns null
			 */
			add_filter( 'list_pages', __CLASS__ . '::handler_wp_list_pages', 99, 2 );
			remove_filter( 'list_pages', '_wp_privacy_settings_filter_draft_page_titles', 10, 2 );

			$html = wp_dropdown_pages( $dropdown_args );

			remove_filter( 'list_pages', __CLASS__ . '::handler_wp_list_pages', 99, 2 );
			add_filter( 'list_pages', '_wp_privacy_settings_filter_draft_page_titles', 10, 2 );

			$html = str_replace( '<select', '<select ' . $multi . $data_string, $html );

			return $html;
		}


		/**
		 * Add post status in case of non public
		 * WP hooks into this filter with _wp_privacy_settings_filter_draft_page_titles since 4.9.8 with WP_Post as $page
		 * and adds (Draft)
		 *
		 * @since 4.2.7
		 * @added_by Günter
		 * @param string $title
		 * @param WP_Post $page
		 * @return string
		 */
		static public function handler_wp_list_pages( $title, $page )
		{
			if( ! $page instanceof WP_Post )
			{
				return $title;
			}

			if(  ! in_array( $page->post_status, array( 'publish' ) ) )
			{
				$title .= ' (' . ucfirst( $page->post_status ) . ')';
			}

			return $title;
		}

		/**
		 * Return an indented dropdown list of terms for hierarchical $taxonomy
		 *
		 * @since 4.2.7
		 * @added_by Günter
		 * @param array $element
		 * @param string $taxonomy
		 * @return string|false
		 */
		static public function select_hierarchical_taxonomy( array $element, $taxonomy = 'category' )
		{
			$defaults = array(
								'id'				=> '',
								'std'				=> array( '', 0 ),
								'label'				=> false,			//	for option group
								'class'				=> '',
								'hierarchical'		=> 'yes',			//	'yes' | 'no'
								'option_none_text'	=> '',				//	text to display for "Nothing selected"
								'option_none_value'	=> '',				//	value for 'option_none_text'
								'option_no_change'	=> ''				//	value for 'no change' - set to -1 by WP default
							);

			$element = array_merge( $defaults, $element );

			/**
			 * return, if element should not display a hierarchical structure
			 */
			if( 'no' == $element['hierarchical'] )
			{
				return false;
			}

			/**
			 * wp_dropdown_pages() does not support multiple selection by default.
			 * Would need to overwrite Walker_CategoryDropdown to add this feature.
			 *
			 * Can be done in future if necessary.
			 */
			if( isset( $element['multiple'] ) )
			{
				return false;
			}

			$obj_ta = get_taxonomy( $taxonomy );

			if ( ! $obj_ta instanceof WP_Taxonomy )
			{
				return false;
			}

			$new_std = explode( ',', $element['std'], 2 );
			$selected = ( ( $new_std[0] == $taxonomy ) && isset( $new_std[1] ) ) ? $new_std[1] : 0;

			/**
			 * @used_by				config-wpml\config.php	avia_wpml_alb_options_select_hierarchical_post_type_id()					10
			 *
			 * @since 4.5.7.2
			 * @param int $selected
			 * @param string $post_type
			 * @param array $new_std
			 * @param array $element
			 * @return int
			 */
			$selected = apply_filters( 'avf_alb_options_select_hierarchical_post_type_id', $selected, $taxonomy, $new_std, $element );

			$data_string = '';
			if( isset( $element['data'] ) )
			{
				foreach( $element['data'] as $key => $data )
				{
					$data_string .= " data-{$key}='{$data}'";
				}
			}

			$multi = $multi_class = '';
			if( isset( $element['multiple'] ) )
			{
				$multi_class = ' avia_multiple_select';
				$multi = ' multiple="multiple" size="' . $element['multiple'] . '" ';
			}

			$args = array(
						'taxonomy'				=> $taxonomy,
						'hierarchical'			=> true,
						'depth'					=> 20,
						'selected'				=> $selected,
						'name'					=> $element['id'],
						'id'					=> $element['id'],
						'show_option_none'		=> $element['option_none_text'],
						'option_none_value'		=> $element['option_none_value'],
						'show_option_no_change' => $element['option_no_change'],
						'orderby'				=> 'name',
						'order'					=> 'ASC',
						'echo'					=> false,
						'class'					=> $element['class'] . $multi_class,
						'hide_empty'			=> false,
						'show_count'			=> true,
						'hide_if_empty'			=> false,
//						'child_of'				=> 0,
//						'exclude'				=> '',
//						'include'				=> '',
//						'tab_index'				=> 0,
//						'value_field'			=> 'term_id',
					);

			$html = wp_dropdown_categories( $args );

			$html = str_replace( '<select', '<select ' . $multi . $data_string, $html );

			return $html;
		}

		/**
		 * The select method renders a single select element: it either lists
		 *		- custom values
		 *		- all wordpress pages
		 *		- all wordpress categories
		 *
		 * For custom value subtype array  ( display content = $key )  you can add a corresponding tooltip array ( $key => content ).
		 *
		 * @since 4.8 added tooltips array for options
		 *
		 * @param array $element		the array holds data like type, value, id, class, description which are necessary to render the whole option-section
		 * @return string				html code generated within the method
		 */
		static public function select( array $element )
		{
			$output = '';
			$locked_data = '';
			$data_string = '';

			if( isset( $element['locked_value'] ) )
			{
				$data_string .= ' data-locked_value="' . $element['locked_value'] . '"';

				$dummy = $element;
				unset( $dummy['locked_value'] );

				$dummy['id'] .= $dummy['id'] . '_fakeArg';
				$dummy['class'] .= ' avia-locked-data-value avia-fake-input';
				$dummy['attr'] = 'disabled="disabled"';
				$dummy['std'] = $element['locked_value'];

				if( is_array( $element['subtype'] ) )
				{
					$sel = explode( ',', $element['locked_value'] );

					if( count( $sel ) > 1 )
					{
						if( ! isset( $dummy['multiple'] ) || $dummy['multiple'] < count( $sel ) )
						{
							$dummy['multiple'] = count( $sel );
						}
					}
				}

				$locked_data = AviaHtmlHelper::select( $dummy );

				$element['class'] .= ' avia-locked-data-hide';
			}


			$select = __( 'Select', 'avia_framework' );
			$parents = array();
			$fake_val = '';
			$entries = array();

			//	query templates and restructure $element to allow default handling
			if( 'element_templates' == $element['subtype'] )
			{
				$shortcode = isset( $element['additional']['shortcode'] ) ? $element['additional']['shortcode'] : null;
				$modal_group = isset( $element['additional']['modal_group'] ) ? $element['additional']['modal_group'] : false;
				$result = Avia_Element_Templates()->get_extended_modal_subtypes_array( $shortcode, $modal_group );

				$element['subtype'] = $result['subtypes'];
				$element['tooltips'] = $result['tooltips'];
			}

			if( $element['subtype'] == 'cat' )
			{
				$add_taxonomy = ! empty( $element['taxonomy'] ) ? "&taxonomy={$element['taxonomy']}" : '';

				$entries = get_categories( 'title_li=&orderby=name&hide_empty=0' . $add_taxonomy );

				// sort entries so subentries are displayed with indentation
				foreach( $entries as $key => $entry )
				{
					if( ! empty( $entry->parent ) )
					{
						$parents[ $entry->parent ][ $entry->term_id ] = $entry;
						unset( $entries[ $key ] );
					}
				}
			}
			else if( ! is_array( $element['subtype'] ) )
			{
				/**
				 * $element['subtype'] contains post_type to query
				 */

				global $wpdb;

				$table_name = $wpdb->prefix . 'posts';

				/**
				 * @param int $max_posts
				 * @param string $element['subtype']
				 * @param array $element
				 * @param string $context
				 * @return int
				 */
	 			$limit = apply_filters( 'avf_dropdown_post_number', 4000, $element['subtype'], $element, 'alb_select' );

	    		if( isset( AviaHtmlHelper::$cache['entry_' . $limit] ) && isset( AviaHtmlHelper::$cache[ 'entry_' . $limit ][ $element['subtype'] ] ) )
	    		{
	    			$entries = AviaHtmlHelper::$cache[ 'entry_' . $limit ][ $element['subtype'] ];
	    		}
	    		else
	    		{
					$post_status = ( ! empty( $element['post_status'] ) ) ? $element['post_status'] : 'publish';

					if( ! is_array( $post_status ) )
					{
						$post_status = explode( ',', $post_status );
					}

					$post_status = array_map( function( $value ) { $value = trim( $value ); return "'{$value}'"; }, $post_status );
					$post_status = implode( ', ', $post_status );

					$prepare_sql = "SELECT distinct ID, post_title, post_status FROM {$table_name} WHERE post_status IN ( {$post_status} ) AND post_type = '{$element['subtype']}' ORDER BY post_title ASC LIMIT {$limit}";

					/**
					 * @used_by			avia_WPML::handler_avf_dropdown_post_query			10
					 */
					$prepare_sql = apply_filters( 'avf_dropdown_post_query', $prepare_sql, $table_name, $limit, $element );

					$entries = $wpdb->get_results( $prepare_sql );

					/**
					 * Allow to filter the page titles
					 *
					 * @since 4.2.7
					 */
					add_filter( 'list_pages', __CLASS__ . '::handler_wp_list_pages', 99, 2 );

					foreach( $entries as &$entry )
					{
						$p = get_post( $entry->ID );
						if( $p instanceof WP_Post && ( $p->ID == $entry->ID ) )
						{
							$entry->post_title = apply_filters( 'list_pages', avia_wp_get_the_title( $entry ), $p );
						}
					}

					unset( $entry );

					remove_filter( 'list_pages', __CLASS__ . '::handler_wp_list_pages', 99, 2 );

					AviaHtmlHelper::$cache['entry_' . $limit][$element['subtype']] = $entries;
	    		}
	    		//$entries 	= $wpdb->get_results( "SELECT ID, post_title FROM {$table_name} WHERE post_status = 'publish' AND post_type = '".$element['subtype']."' ORDER BY post_title ASC LIMIT {$limit}" );
				//$entries = get_posts(array('numberposts' => apply_filters( 'avf_dropdown_post_number', 200 ), 'post_type' => $element['subtype'], 'post_status'=> 'publish', 'orderby'=> 'post_date', 'order'=> 'ASC'));
			}
			else
			{
				$select = __( 'Select...', 'avia_framework' );
				$entries = $element['subtype'];
				$add_entries = array();

				if( isset( $element['folder'] ) )
				{
					$add_file_array = avia_backend_load_scripts_by_folder( AVIA_BASE . $element['folder'] );

					if( is_array( $add_file_array ) )
					{
						foreach( $add_file_array as $file )
						{
							$skip = false;

							if( ! empty( $element['exclude'] ) )
							{
								foreach( $element['exclude'] as $exclude )
								{
        							if( stripos( $file, $exclude ) !== false )
									{
										$skip = true;
									}
    							}
							}

							if( strpos( $file, '.' ) !== 0 && $skip == false )
							{
								$add_entries[ $element['folderlabel'] . $file ] = "{{AVIA_BASE_URL}}" . $element['folder'] . $file; 							}
							}


						if( isset( $element['group'] ) )
						{
							$entries[ $element['group'] ] = $add_entries;
						}
						else
						{
							$entries = array_merge( $entries, $add_entries );
						}
					}
				}
			}

			if( empty( $entries ) )
			{
				return '';
			}

			$data_string .= ' data-initial="' . esc_attr( $element['std'] ) . '"';

			if( isset( $element['data'] ) )
			{
				foreach( $element['data'] as $key => $data )
				{
					$data_string .= " data-{$key}='{$data}'";
				}
			}

			$multi = $multi_class = '';
			if( isset( $element['multiple'] ) )
			{
				$multi_class = ' avia_multiple_select';
				$multi = 'multiple="multiple" size="' . $element['multiple'] . '"';
				$element['std'] = explode( ',', $element['std'] );
			}

			$id_string = empty( $element['id'] ) ? '' : "id='" . $element['id'] . "'";
			$name_string = empty( $element['id'] ) ? '' : "name='" . $element['id'] . "'";
			$attr_string = isset( $element['attr'] ) ? $element['attr'] : '';


			$output .= $locked_data;
			$output .= '<select ' . $multi . ' class="' . $element['class'] . '" '. $id_string .' '. $name_string . ' ' . $data_string . ' ' . $attr_string . '> ';

			if( isset( $element['with_first'] ) )
			{
				$output .= '<option value="">' . $select . '</option>  ';
				$fake_val = $select;
			}

			$real_entries = array();
			foreach( $entries as $key => $entry )
			{
				if( ! is_array( $entry ) )
				{
					$real_entries[ $key ] = $entry;
				}
				else
				{
					$real_entries[ 'option_group_' . $key ] = $key;

					foreach( $entry as $subkey => $subentry )
					{
						$real_entries[ $subkey ] = $subentry;
					}

					$real_entries[ 'close_option_group_' . $key ] = 'close';
				}
			}

			$entries = $real_entries;
			$output .= AviaHtmlHelper::create_select_option( $element, $entries, $fake_val, $parents, 0 );

			$output .= '</select>';

			return $output;
		}

		/**
		 * Returns the options part of a select box
		 *
		 * @since < 4.0
		 * @param array $element
		 * @param array $entries
		 * @param string $fake_val
		 * @param array $parents
		 * @param int $level
		 * @return string
		 */
		static protected function create_select_option( $element, $entries, $fake_val, $parents, $level )
		{
			$output = '';

			$tooltips = isset( $element['tooltips'] ) && is_array( $element['tooltips'] ) ? $element['tooltips'] : array();

			foreach( $entries as $key => $entry )
			{
				$title_attr = '';

				if( $element['subtype'] == 'cat' )
				{
					if( isset( $entry->term_id ) )
					{
						$id = $entry->term_id;
						$title = $entry->name;
					}
				}
				else if( ! is_array( $element['subtype'] ) )
				{
					$id = $entry->ID;
					$title = avia_wp_get_the_title( $id );
				}
				else
				{
					$id = $entry;
					$title = $key;

					if( array_key_exists( $id, $tooltips ) && ! empty( $tooltips[ $id ] ) )
					{
						$title_attr = ' title="' . esc_attr( $tooltips[ $id ] ) . '"';
					}
				}

				if( ! empty( $title ) || ( isset( $title ) && $title === 0 ) )
				{
					if( empty( $fake_val ) )
					{
						$fake_val = $title;
					}

					$selected = '';
					if( $element['std'] == $id || ( is_array( $element['std'] ) && in_array( $id, $element['std'] ) ) )
					{
						$selected = "selected='selected'";
						$fake_val = $title;
					}

					$indent = '';
					for( $i = 0; $i < $level; $i++ )
					{
						$indent .= '- ';
					}

					if( strpos ( $title , 'option_group_' ) === 0 )
					{
						$output .= "<optgroup label='". $id."'>";
					}
					else if( strpos ( $title , 'close_option_group_' ) === 0 )
					{
						$output .= '</optgroup>';
					}
					else
					{
						$output .= "<option {$selected} value='{$id}' {$title_attr}>{$indent}{$title}</option>";
					}

					if( ! empty( $parents ) && ! empty( $parents[$id] ) )
					{
						$level ++;
						$output .= AviaHtmlHelper::create_select_option( $element, $parents[ $id ], $fake_val, $parents, $level );
						$level --;
					}
				}
			}

			return $output;
		}

		/**
		 * Based on wp_timezone_override_offset() and get_timezone_info()
		 * Returns the timezone offset from UTC. Defaults to UTC if not available
		 *
		 * @since 4.5.6
		 * @param string $timezone_string
		 * @return float						UTC offset in hours
		 */
		static public function get_timezone_offset( $timezone_string = '' )
		{
			$timezone_string = AviaHtmlHelper::default_wp_timezone_string( $timezone_string );

			if( false !== stripos( $timezone_string, 'UTC' ) )
			{
				$tz = trim( str_ireplace( 'UTC', '', $timezone_string ) );
				if( empty( $tz ) || ! is_numeric( $tz ) )
				{
					$tz = 0;
				}
				return (float) $tz;
			}

			$timezone_object = timezone_open( $timezone_string );
			$datetime_object = date_create();
			if ( false === $timezone_object || false === $datetime_object )
			{
				return 0.0;
			}

			return round( timezone_offset_get( $timezone_object, $datetime_object ) / HOUR_IN_SECONDS, 2 );
		}

		/**
		 * Returns a valid timezone_string for the select box.
		 * Checks for WP default setting if empty.
		 *
		 * @since 4.5.6
		 * @param string $timezone_string
		 * @return string
		 */
		static public function default_wp_timezone_string( $timezone_string = '' )
		{
			if( ! empty( $timezone_string ) )
			{
				return $timezone_string;
			}

			$timezone_string = get_option( 'timezone_string', '' );
			if( ! empty( $timezone_string ) )
			{
				return $timezone_string;
			}

			$offset = get_option( 'gmt_offset', '' );
			if( '' == trim( $offset ) || ! is_numeric( $offset ) || 0 == $offset )
			{
				return 'UTC';
			}

			return $offset <= 0 ? 'UTC' . $offset : 'UTC+' . $offset;
		}


		/**
		 * Returns a timezone select box and preselects the default WP timezone
		 *
		 * @since 4.5.6
		 * @param array $element
		 * @return string
		 */
		static public function timezone_choice( array $element )
		{
			$html = '';
			$data = '';

			if( isset( $element['locked_value'] ) )
			{
				$data .= ' data-locked_value="' . $element['locked_value'] . '"';

				$dummy = array(
								'id' 	=> $element['id'] . '_fakeArg',
								'type' 	=> 'input',
								'std'	=> $element['locked_value'],
								'class'	=> 'avia-locked-data-value avia-fake-input',
								'attr'	=> 'readonly="readonly"'
							);

				$html .= AviaHtmlHelper::input( $dummy );

				$element['class'] .= ' avia-locked-data-hide';
			}

			$id_string = empty( $element['id'] ) ? '' : "id='{$element['id']}'";
			$name_string = empty( $element['id'] ) ? '' : "name='{$element['id']}'";
			$timezone_string = AviaHtmlHelper::default_wp_timezone_string( $element['std'] );

			$html .=	'<select class="' . $element['class'] . '" ' . $id_string . ' ' . $name_string . ' ' . $data . '>';
			$html .=		wp_timezone_choice( $timezone_string );
			$html .=	'</select>';

			return $html;
		}

		/**
		 * The gmap_adress method renders an address input field that allows to fetch long/lat coordinates via google api
		 *
		 * @param array $element		the array holds data like type, value, id, class, description which are necessary to render the whole option-section
		 * @return string				the string returned contains the html code generated within the method
		 */
		static public function gmap_adress( array $element )
		{
			$defaults = array(
							'address'	=> '',
							'postcode'	=> '',
							'city'		=> '',
							'state'		=> '',
							'country'	=> '',
							'long'		=> '',
							'lat'		=> ''
						);

			$subvalues = isset( $element['shortcode_data'] ) ? $element['shortcode_data'] : array();
			$values = array_merge( $defaults, $subvalues );
			$visibility = '';

			if( ! empty( $subvalues['long'] ) || ! empty( $subvalues['lat'] ) )
			{
				$visibility = 'av-visible';
			}

			$output  = '';
			$output .= '<label class="av-gmap-field av-gmap-addres">' . __( 'Street address', 'avia_framework' ) . ' <input type="text" class="'.$element['class'].'" value="' . nl2br( $values['address'] ) . '" id="address" name="address"/></label>';

			$output .= '<label class="av-gmap-field av-gmap-addres av_half av_first">' . __( 'Postcode', 'avia_framework' ) . ' <input type="text" class="' . $element['class'] . '" value="' . nl2br($values['postcode'] ) . '" id="postcode" name="postcode"/></label>';
			$output .= '<label class="av-gmap-field av-gmap-addres av_half">' . __( 'City', 'avia_framework' ) . ' <input type="text" class="' . $element['class'] . '" value="' . nl2br( $values['city'] ) . '" id="city" name="city"/></label>';

			$output .= '<label class="av-gmap-field av-gmap-addres av_half av_first">' . __( 'State', 'avia_framework' ) . ' <input type="text" class="' . $element['class'] . '" value="' . nl2br( $values['state'] ) . '" id="state" name="state"/></label>';
			$output .= '<label class="av-gmap-field av-gmap-addres av_half">' . __( 'Country', 'avia_framework' ) . ' <input type="text" class="' . $element['class'] . '" value="' . nl2br( $values['country'] ) . '" id="country" name="country"/></label>';

			$class 	 = 'button button-primary avia-js-google-coordinates av-google-fetch-button' . $element['class'];
			$output .= '<a href="#" class="' . $class . '" title="">' . __( 'Enter Address, then fetch coordinates', 'avia_framework' ) . '</a>';
			$output .= '<div class="av-gmap-coordinates ' . $visibility . '">';

			$output .= '<label class="av-gmap-field av_half av_first">' . __( 'Longitude', 'avia_framework' ) . ' <input type="text" class="' . $element['class'] . '" value="' . nl2br( $values['long'] ) . '" id="long" name="long"/></label>';

			$output .= '<label class="av-gmap-field av_half">' . __( 'Latitude', 'avia_framework' ) . ' <input type="text" class="' . $element['class'] . '" value="' . nl2br($values['lat'] ) . '" id="lat" name="lat"/></label>';


			$output .= '</div>';

			return $output;
		}

		/**
		 * The radio method renders one or more input type:radio elements, based on the definition of the $elements array
		 *
		 * @param array $element		the array holds data like type, value, id, class, description which are necessary to render the whole option-section
		 * @return string				the string returned contains the html code generated within the method
		 */
		static public function radio( array $element )
		{
			$output = '';
			$data = '';
			$locked_data = '';

			$element['wrap_class'] = isset( $element['wrap_class'] ) ? $element['wrap_class'] : '';

			if( isset( $element['locked_value'] ) )
			{
				$data .= ' data-locked_value="' . $element['locked_value'] . '"';

				$dummy = array(
								'id'			=> $element['id'] . '_fakeArg',
								'type'			=> 'radio',
								'std'			=> $element['locked_value'],
								'class'			=> 'avia-locked-data-value avia-fake-input',
								'options'		=> $element['options'],
								'attr'			=> 'disabled="disabled"',
								'wrap_class'	=> 'avia-locked-data-value'
							);

				$locked_data = AviaHtmlHelper::radio( $dummy );

				$element['wrap_class'] .= ' avia-locked-data-hide';
			}

			$output .= $locked_data;

			$attr = isset( $element['attr'] ) ? $element['attr'] : '';

			$counter = 1;
			foreach( $element['options'] as $key => $radiobutton )
			{
				$checked = '';
				$extra_class = $element['wrap_class'];

				if( $element['std'] == $key )
				{
					$checked = 'checked = "checked"';
				}

				$fields  = '<input ' . $checked . ' type="radio" class="radio_' . $key . ' ' . $element['class'] . '" ';
				$fields .= 'value="' . $key . '" id="' . $element['id'] . $counter . '" name="' . $element['id'] . '" ' . $attr . $data . ' />';

				$fields .= '<label for="' . $element['id'] . $counter . '">';

				if( isset( $element['images'] ) && ! empty( $element['images'][ $key ] ) )
				{
					$fields .= "<img class='radio_image' src='{$element['images'][$key]}' />";
					$extra_class .= ' avia-image-radio';
				}

				$fields .= '<span class="labeltext">' . $radiobutton . '</span>';
				$fields .= '</label>';

				$output .= '<span class="avia_radio_wrap ' . $extra_class . '">';
				$output .=		$fields;
				$output .= '</span>';

				$counter++;
			}

			return $output;
		}

		/**
		 *
		 * @param array $element
		 * @return string
		 */
		static public function mailchimp_list( $element )
		{
			$api = $element['api'];
			$new_list = array();

			foreach( $api->fields as $list_id => $fields )
			{
				foreach( $fields as $field )
				{
					$list_item = array();
					$list_item['id'] = $field->merge_id;
					$list_item['label'] = $field->name;
					$list_item['type'] = $field->type;
					$list_item['value'] = $field->default_value;
					$list_item['disabled'] = isset( $field->public ) && $field->public === false  ? 'true' : '';
					$list_item['check'] = '';

					if( isset( $field->required ) &&  $field->required )
					{
						$list_item['check'] = 'is_empty';
						if( $field->type == 'email' )
						{
							$list_item['check'] = 'is_email';
							$list_item['type'] = 'text';
						}

						if( $field->type == 'number' )
						{
							$list_item['check'] = 'is_number';
						}
					}

					if( isset( $field->options->choices ) )
					{
						$list_item['options'] = implode( ',', $field->options->choices );
					}

					$new_list[ $list_id ][ $field->merge_id ] = $list_item;
				}

				//add the default subscribe button
				if( ! empty( $new_list[ $list_id ] ) )
				{
					$new_list[ $list_id ]['av-button'] = array(
												'id' 		=> 'av-button',
												'label' 	=> __( 'Subscribe' , 'avia_framework' ),
												'type'		=> 'button',
												'value' 	=> '',
												'check' 	=> '',
											);
				}

			}

			$output  = '';
			$output .= AviaHtmlHelper::select( $element );
			$output .= "<script type='text/javascript' > var av_mailchimp_list = " . json_encode( $new_list ) . '; ';
			$output .= '</script>';

			return $output;
		}

		/**
		 *
		 * @param array $element
		 * @param type $parent
		 * @return string
		 */
		static public function table ( array $element , $parent )
		{
			$values = ! empty( $_POST['extracted_shortcode'] ) ? $_POST['extracted_shortcode'] : false;

			$prepared = array();
			$rows = 3;
			$columns = 3;

			//prepare values based on the sc array
			if( $values )
			{
    			foreach( $values as $value )
    			{
    				switch( $value['tag'] )
    				{
    					case 'av_cell':
							$prepared['cells'][] = array( 'content' => stripslashes( $value['content'] ), 'col_style' => $value['attr']['col_style'] );
							break;
    					case 'av_row':
							$prepared['rows'][] = array( 'row_style' => $value['attr']['row_style'] );
							break;
    				}
    			}
			}

			if( $prepared )
			{
				$rows = count( $prepared['rows'] );
				$columns = count( $prepared['cells'] ) / $rows;
			}

			$params = array(
						'class'			=> '',
						'parent_class'	=> $parent
					);

			$output  = '';
			$output .= '<div class="avia-table-builder-wrapper">';

			$output .=		'<div class="avia-table-builder-add-buttons">';
			$output .=			'<a class="avia-attach-table-row button button-primary button-large">' . __( 'Add Table Row', 'avia_framework' ) . '</a>';
			$output .=			'<a class="avia-attach-table-col button button-primary button-large">' . __( 'Add Table Column', 'avia_framework' ) . '</a>';
			$output .=		'</div>';

			$output .=		'<div class="avia-table">';

			$row_args = array(
							'class'			=> 'avia-table-col-style avia-attach-table-col-style avia-noselect',
							'col_option'	=> true,
							'no-edit'		=> true
					);

			$output .= AviaHtmlHelper::table_row( false, $columns,  $row_args, $element, $prepared );

			for( $i = 1; $i <= $rows; $i++ )
			{
				if( $prepared )
				{
					$params['row_style'] = $prepared['rows'][ $i-1 ]['row_style'];
				}

				$output .= AviaHtmlHelper::table_row( $i, $columns, $params, $element, $prepared );
			}

			$output .= AviaHtmlHelper::table_row( false, $columns,  array( 'class' => 'avia-template-row'), $element , $prepared );
			$output .= AviaHtmlHelper::table_row( false, $columns,  array( 'class' => 'avia-delete-row avia-noselect', 'no-edit' => true ), $element );

			$output .=		'</div>';
			$output .= '</div>';

			return $output;
		}

		/**
		 *
		 * @param int $row
		 * @param int $columns
		 * @param array $params
		 * @param array $element
		 * @param array $prepared
		 * @return string
		 */
		static public function table_row( $row, $columns, $params, $element, $prepared = array() )
		{
			$up 	= __( 'move up', 'avia_framework' );
			$down 	= __( 'move down', 'avia_framework' );
			$left 	= __( 'move left', 'avia_framework' );
			$right 	= __( 'move right', 'avia_framework' );


			$defaults = array(
							'class'		=> '',
							'content'	=> '',
							'row_style'	=> ''
						);
			$params = array_merge( $defaults, $params );

			$extraclass = '';

			$output  = '';
			$output .= "<div class='avia-table-row  {$params['class']} {$params['row_style']}'>";
			$output .=		'<div class="avia-table-cell avia-table-cell-style avia-attach-table-row-style avia-noselect">';

			if( empty( $params['no-edit'] ) )
			{
				$output .=		'<div class="avia-move-table-row-container">';
				$output .=			'<div class="avia-move-table-row">';
				$output .=				"<a href='#' class='av-table-pos-button av-table-up' data-direction='up' title='{$up}'>{$up}</a>";
				$output .=				"<a href='#' class='av-table-pos-button av-table-down' data-direction='down' title='{$down}'>{$down}</a>";
				$output .=			'</div>';
				$output .=		'</div>';
			}

			$output .= AviaHtmlHelper::select( array( 'std' => $params['row_style'], 'subtype' => $element['row_style'], 'id' => 'row_style', 'class' => '' ) );
			$output .=		'</div>';

			for($j = 1; $j <= $columns; $j++ )
			{
				if( $prepared )
				{
					if( ! $row )
					{
						$row = 1;
					}

					$rows = count( $prepared['rows'] );
					$columns = count( $prepared['cells'] ) / $rows;
					$key = ( ( $row - 1 ) * $columns ) + ( $j -1 );

					if( $params['class'] == 'avia-template-row' )
					{
						$params['content'] = '';
					}
					else
					{
						$params['content'] = $prepared['cells'][ $key ]['content'];
					}
					$extraclass = $prepared['cells'][ $key ]['col_style'];
				}

				if( isset( $params['col_option'] ) )
				{
					$params['content']  = '<div class="avia-move-table-col">';
					$params['content'] .=	"<a href='#' class='av-table-pos-button av-table-left' data-direction='left' title='{$left}'>{$left}</a>";
					$params['content'] .=	"<a href='#' class='av-table-pos-button av-table-right' data-direction='right' title='{$right}'>{$right}</a>";
					$params['content'] .= '</div>';

					$params['content']  .= AviaHtmlHelper::select( array( 'std' => $extraclass, 'subtype' => $element['column_style'], 'id' => 'column_style', 'class' => '' ) );
				}

				if( isset( $params['parent_class'] ) && $params['row_style'] == 'avia-button-row' && strpos( $params['content'], '[' ) !== false )
				{

					$params['parent_class']->builder->text_to_interface( $params['content'] );
					$values = end( $_POST['extracted_shortcode'] );
					$params['content'] = $params['parent_class']->builder->shortcode_class[ $params['parent_class']->builder->shortcode[ $values['tag'] ] ]->prepare_editor_element( $values['content'], $values['attr'] );
				}

				$output .=		"<div class='avia-table-cell {$extraclass}'>";
				$output .=			'<div class="avia-table-content">';
				$output .=				stripslashes( $params['content'] );
				$output .=			'</div>';

				if( empty( $params['no-edit'] ) && empty( $values ) )
				{
					$output .=		'<textarea class="avia-table-data-container" name="content">';
					$output .=			stripslashes( $params['content'] );
					$output .=		'</textarea>';
				}
				$output .=		'</div>';
			}

			$output .=		'<div class="avia-table-cell avia-table-cell-delete avia-attach-delete-table-row avia-noselect">';
			$output .=		'</div>';

			$output .= '</div>';

			return $output;
		}

		/**
		 *
		 * @param string $img
		 * @return string
		 */
		static public function display_image( $img = '' )
		{
			$final = array();

			if( preg_match( '/^.*\.(jpg|jpeg|png|gif|svg|webp)$/i', $img ) )
			{
				$final[] = '<img src="' . $img . '" />';
			}
			else if( ! empty( $img ) )
			{
				$args = array(
							'post_type'		=> 'attachment',
							'numberposts'	=> -1,
							'include'		=> $img,
							'orderby'		=> 'post__in'
						);

				$attachments = get_posts( $args );

				foreach ( $attachments as $attachment )
				{
					$final[] = wp_get_attachment_link( $attachment->ID, 'thumbnail', false, false );
				}
			}

			$hidden = 'avia-hidden';

			$output  = '';
			$output .= '<div class="avia-builder-prev-img-container-wrap">';
			$output .=		'<div class="avia-builder-prev-img-container">';

			if( ! empty( $final ) )
			{
				if( count( $final) == 1 )
				{
					$hidden = '';
				}

				foreach ( $final as $img )
				{
					$output .= "<span class='avia-builder-prev-img'>{$img}</span>";
				}
			}

			$output .=		'</div>';
			$output .= '</div>';
			$output .= "<a href='#delete' class='avia-delete-image {$hidden}'>" . __( 'Remove Image', 'avia_framework' ) . '</a>';

			return $output;
		}

		/**
		 * Output a preview of a lottie animation or a simple image
		 *
		 * @since 5.5
		 * @param string $url
		 * @return string
		 */
		static public function display_lottie_preview( $url = '' )
		{
			if( ! preg_match( '/^.*\.(json|lottie)$/i', $url ) )
			{
				return AviaHtmlHelper::display_image( $url );
			}

			$hidden = empty( $url ) ? 'avia-hidden' : '';

			$output  = '';
			$output .= '<div class="avia-builder-prev-img-container-wrap prev-lottie-container-wrap">';
			$output .=		'<div class="avia-builder-prev-img-container">';
			$output .=			'<span class="avia-builder-prev-img">';
			$output .=				AviaLottieAnimations()->alb_backend_player( $url );
			$output .=			'</span>';
			$output .=		'</div>';
			$output .= '</div>';
			$output .= "<a href='#delete' class='avia-delete-image {$hidden}'>" . __( 'Remove Animation', 'avia_framework' ) . '</a>';

			return $output;
		}

		/**
		 * @since ????
		 * @since 5.0						added $post_array
		 * @param int $from
		 * @param int $to
		 * @param int $steps
		 * @param array $pre_array
		 * @param string $label
		 * @param string $value_prefix
		 * @param string $value_postfix
		 * @param array $post_array					$value_prefix and $value_postfix are added by default
		 * @return array
		 */
		static public function number_array( $from = 0, $to = 100, $steps = 1, $pre_array = array(), $label = '', $value_prefix = '', $value_postfix = '', array $post_array = array() )
		{
			for( $i = $from; $i <= $to; $i += $steps )
			{
			    $pre_array[ $i . $label ] = $value_prefix . $i . $value_postfix;
			}

			foreach( $post_array as $value )
			{
				$pre_array[ $value . $label ] = $value_prefix . $value . $value_postfix;
			}

			return $pre_array;
		}

		/**
		 *
		 * @since ???
		 * @return array
		 */
		static public function linking_options()
		{
		    if( 'alb_seo_qualify_links' == avia_get_option( 'alb_seo_qualify_links' ) )
			{
			    $linkoptions = array(
									__( 'Open in same window', 'avia_framework' )									=> '',
									__( 'Open in same window and use rel="nofollow"', 'avia_framework' )			=> 'nofollow',
									__( 'Open in same window and use rel="nofollow sponsored"', 'avia_framework' )	=> 'nofollow sponsored',
									__( 'Open in same window and use rel="nofollow ugc"', 'avia_framework' )		=> 'nofollow ugc',
									__( 'Open in new window', 'avia_framework' )									=> '_blank',
									__( 'Open in new window and use rel="nofollow"', 'avia_framework' )				=> '_blank nofollow',
									__( 'Open in new window and use rel="nofollow sponsored"', 'avia_framework' )	=> '_blank nofollow sponsored',
									__( 'Open in new window and use rel="nofollow ugc"', 'avia_framework' )			=> '_blank nofollow ugc'
								);
			}
			else
			{
			    $linkoptions = array(
									__( 'Open in same window', 'avia_framework' )	=> '',
									__( 'Open in new window', 'avia_framework' )	=> '_blank'
								);
			}

		    return $linkoptions;
		}

		/**
		 * Returns an option array of all registered post types
		 *
		 * @param array $args
		 * @return array
		 */
		static public function get_registered_post_type_array( $args = array() )
		{
			$post_types = get_post_types( $args, 'objects' );
			$post_type_option = array();

			if( ! empty( $post_types ) )
			{
				foreach( $post_types as $post_type )
				{
					/**
					 * Fixes a bug with non unique labels
					 */
					if( ! isset( $post_type_option[ $post_type->label ] ) )
					{
						$post_type_option[ $post_type->label ] = $post_type->name;
					}
					else
					{
						$post_type_option[ "{$post_type->label} ({$post_type->name})" ] = $post_type->name;
					}
				}
			}

			/**
			 *
			 * @since ???
			 * @param array $post_type_option
			 * @param array $args
			 * @return array
			 */
			$post_type_option = apply_filters( 'avf_registered_post_type_array', $post_type_option, $args );

			return $post_type_option;
		}

	}
}
