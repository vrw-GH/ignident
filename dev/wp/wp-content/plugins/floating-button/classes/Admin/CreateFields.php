<?php

namespace FloatingButton\Admin;

class CreateFields {
	/**
	 * @var mixed|string
	 */
	private $order;
	/**
	 * @var mixed
	 */
	private $options;
	/**
	 * @var mixed
	 */
	private $page_options;

	public function __construct( $options, $page_options ) {
		$this->options      = $options;
		$this->page_options = $page_options;
	}

	public function create( $name, ...$order ): bool {
		$this->order = $order;
		if ( ! isset( $this->page_options[ $name ] ) ) {
			return false;
		}
		$args       = $this->page_options[ $name ];
		$key        = $this->get_key( $name );
		$data_field = $name;

		$opt_val = $args['val'] ?? $args['value'] ?? '';
		$default = $opt_val;

		if (empty($this->options['tool_id'])) {
			$default = $opt_val;
		}

		$opt_key     = $this->get_opt_key( $name );
		$value       = $this->get_the_value( $name, $default );
		$class       = ! empty( $args['class'] ) ? ' ' . $args['class'] : '';
		$type        = $args['type'] ?? 'text';
		$label_class = isset( $args['label'] ) ? '' : 'screen-reader-text';
		$label       = ! empty( $args['label'] ) ? $args['label'] : '';
		$atts        = $this->get_attributes( $args, $value );
		$addon       = $this->get_addon( $args );
		$checked     = checked( "1", $value, false );

		$template = '<div class="wpie-field{{class}}" data-field-box="' . esc_attr( $name ) . '">';
		$template .= $this->get_title( $args );
		if ( ! empty( $addon ) ) {
			$template .= '<div class="wpie-field__group">';
			$template .= $this->get_template( $args );
			$template .= '</div>';
		} else {
			$template .= $this->get_template( $args );
		}
		$template .= '</div>';

		$content = str_replace( [
			'{{class}}',
			'{{type}}',
			'{{name}}',
			'{{value}}',
			'{{atts}}',
			'{{label_class}}',
			'{{label}}',
			'{{addon}}',
			'{{checked}}',
			'{{data_field}}',

		], [
			$class,
			esc_attr( $type ),
			esc_attr( $key ),
			$value,
			$atts,
			esc_attr( $label_class ),
			esc_html( $label ),
			$addon,
			$checked,
			$data_field

		], $template );

		$this->output( $content );

		return true;
	}

	private function output( $content ): void {
		$allowed_post_tags   = wp_kses_allowed_html( 'post' );
		$allowed_html        = array(
			'input'    => array(
				'type'               => [],
				'data-field'         => [],
				'name'               => [],
				'value'              => [],
				'placeholder'        => [],
				'class'              => [],
				'checked'            => [],
				'readonly'           => [],
				'disabled'           => [],
				'min'                => [],
				'max'                => [],
				'step'               => [],
				'data-alpha-enabled' => [],
			),
			'textarea' => [
				'placeholder' => [],
				'data-field'  => [],
				'name'        => [],
				'class'       => [],
			],
			'select'   => [
				'name'       => [],
				'data-field' => [],

			],
			'option'   => [
				'value'    => [],
				'selected' => [],
			],
			'optgroup' => [
				'label' => [],
			],
			'div' => [
				'class' => [],
				'data-field-box' => [],
			],

		);
		$allowed_post_tags[] = $allowed_html;
		$merged_allowed_tags = array_merge( $allowed_post_tags, $allowed_html );

		echo wp_kses( $content, $merged_allowed_tags );
	}

	private function get_template( $args ): string {
		if ( $args['type'] === 'select' ) {
			return $this->select_template();
		}

		if ( $args['type'] === 'checkbox' ) {
			return $this->checkbox_template();
		}

		if ( $args['type'] === 'textarea' ) {
			return $this->textarea_template();
		}

		if ( $args['type'] === 'editor' ) {
			return $this->editor_template();
		}

		return $this->text_template();
	}

	private function get_title( $args ) {
		if ( empty( $args['title'] ) ) {
			return '';
		}

		if ( is_string( $args['title'] ) ) {
			$title = '<div class="wpie-field__title">';
			$title .= esc_html( $args['title'] );
			if ( isset( $args['tooltip'] ) ) {
				$title .= '<sup class="has-tooltip wpie-color-dark" data-tooltip="' . esc_attr( $args['tooltip'] ) . '">ℹ</sup>';
			}
			$title .= '</div>';

			return $title;
		}

		if ( is_array( $args['title'] ) ) {
			return $this->get_checkbox_title( $args['title'] );
		}

		return '';
	}

	private function get_checkbox_title( $args ) {
		$key        = $this->get_key( $args['name'] );
		$data_field = $args['name'] ?? 'wpie-field';

		$opt_val = $args['val'] ?? $args['value'] ?? '';
		$default = $opt_val;

		if (empty($this->options['tool_id'])) {
			$default = $opt_val;
		}

//		$value       = $this->options[ $key ] ?? $default;
		$value       = $this->get_the_value( $args['name'], $default );
		$label_class = isset( $args['label'] ) ? '' : 'screen-reader-text';
		$label       = ! empty( $args['label'] ) ? $args['label'] : '';
		$checked     = checked( "1", $value, false );
		$toogle      = isset( $args['toggle'] ) ? ' has-checked' : '';
		$tooltip     = isset( $args['tooltip'] ) ? '<sup class="has-tooltip wpie-color-dark" data-tooltip="' . esc_attr( $args['tooltip'] ) . '">ℹ</sup>' : '';
		$template    = '<div class="wpie-field__title' . esc_attr( $toogle ) . '">
					<label class="wpie-field__title-label">
					<input type="checkbox" data-field="{{data_field}}" {{checked}}>
					<input type="hidden" name="{{name}}" value="' . esc_attr( $value ) . '"> 
                    <span class="{{label_class}}">{{label}} {{tooltip}}</span></label></div>';

		return str_replace( [
			'{{name}}',
			'{{checked}}',
			'{{label_class}}',
			'{{label}}',
			'{{data_field}}',
			'{{tooltip}}',

		], [
			esc_attr( $key ),
			$checked,
			esc_attr( $label_class ),
			esc_html( $label ),
			$data_field,
			$tooltip

		], $template );
	}


	private function get_addon( $args ) {
		if ( empty( $args['addon'] ) ) {
			return '';
		}

		if ( is_string( $args['addon'] ) ) {
			return '<span class="wpie-field__label is-addon">' . esc_html( $args['addon'] ) . '</span>';
		}

		if ( is_array( $args['addon'] ) ) {
			return $this->get_addon_select( $args['addon'] );
		}

		return '';
	}

	private function get_addon_select( $args ) {
		$label_class = isset( $args['label'] ) ? '' : 'screen-reader-text';
		$label       = ! empty( $args['label'] ) ? $args['label'] : '';
		$key         = $this->get_key( $args['name'] );
		$data_field  = $args['name'] ?? 'wpie-field';
		$default     = $args['val'] ?? '';
		$value       = $this->get_the_value( $args['name'], $default );
		$atts        = $this->get_attributes( $args, $value );
		$template    = '<label class="wpie-field__label">
                    <span class="{{label_class}}">{{label}}</span>
                    <select name="{{name}}" data-field="{{data_field}}">
                       {{atts}}
                    </select>
                </label>';

		return str_replace( [
			'{{name}}',
			'{{atts}}',
			'{{label_class}}',
			'{{label}}',
			'{{data_field}}',

		], [
			esc_attr( $key ),
			$atts,
			esc_attr( $label_class ),
			esc_html( $label ),
			$data_field

		], $template );
	}


	private function get_attributes( $args, $value ) {
		$arr = $args['atts'] ?? $args['options'] ?? '';

		if (empty($arr) || !is_array($arr)) {
			return false;
		}
		$atts = '';

		foreach ($arr as $key => $val) {
			if ( $args['type'] === 'select' ) {
				if ( strrpos( $key, '_start' ) ) {
					$atts .= '<optgroup label="' . esc_attr( $val ) . '">';
				} elseif ( strrpos( $key, '_end' ) ) {
					$atts .= '</optgroup>';
				} else {
					$atts .= '<option value="' . esc_attr( $key ) . '"' . selected( $value, $key,false ) . '>' . esc_html( $val ) . '</option>';
				}
			} else {
				$atts .= ' ' . esc_attr( $key ) . '="' . esc_attr( $val ) . '"';
			}
		}


		return $atts;
	}

	private function get_the_value( $name, $default = '' ) {

		if ( empty( $this->order ) || ! is_array( $this->order ) ) {
			return $this->options[ $name ] ?? $default;
		}

		if ( strpos( $name, '-' ) !== false ) {
			$parts = explode( '-', $name );
			if ( empty( $this->options[ $parts[0] ][ $parts[1] ] ) ) {
				$value = [];
			} else {
				$value = $this->options[ $parts[0] ][ $parts[1] ];
			}
		} else {
			if ( empty( $this->options[ $name ] ) ) {
				$value = [];
			} else {
				$value = $this->options[ $name ];
			}
		}

		foreach ( $this->order as $order ) {
			if ( is_numeric( $order ) ) {
				if ( isset( $value[ $order ] ) && is_array( $value ) ) {
					$value = &$value[ $order ];
				} else {
					return $default;
				}
			}
		}

		return $value ?? $default;
	}

	private function get_opt_key( $name ) {
		if ( empty( $this->order ) ) {
			return $name;
		}

		if ( ! is_array( $this->order ) ) {
			return $name;
		}

		$key = $name;

		foreach ( $this->order as $order ) {
			if ( is_numeric( $order ) && $order >= 0 ) {
				$key .= '[' . $order . ']';
			} elseif ( is_string( $order ) ) {
				$key .= '[' . $order . ']';
			} else {
				$key .= '[]';
			}
		}

		return $key;
	}

	private function get_key( $name ) {

		if ( strpos( $name, '-' ) !== false ) {
			$parts    = explode( '-', $name );
			$arr_name = '';
			foreach ( $parts as $partkey => $part ) {
				if ( $partkey === 0 ) {
					$arr_name .= 'param[' . $part . ']';
					continue;
				}
				$arr_name .= '[' . esc_attr( $part ) . ']';
			}
			$name = $arr_name;
		}


		if ( empty( $this->order ) ) {
			if ( strpos( $name, 'param[' ) !== false ) {
				return $name;
			}

			return 'param[' . $name . ']';
		}

		if ( ! is_array( $this->order ) ) {
			if ( strpos( $name, 'param[' ) !== false ) {
				return $name;
			}

			return 'param[' . $name . ']';
		}

		$key = ( strpos( $name, 'param[' ) !== false ) ? $name : 'param[' . $name . ']';

		foreach ( $this->order as $order ) {
			if ( is_string( $order ) ) {
				$key .= '[' . esc_attr( $order ) . ']';
			} else {
				$key .= '[]';
			}
		}

		return $key;
	}

	private function text_template(): string {
		return '
                <label class="wpie-field__label">
                    <input type="{{type}}" name="{{name}}" value="{{value}}" data-field="{{data_field}}" {{atts}}>
                    <span class="{{label_class}}">{{label}}</span>
                </label>
                {{addon}}
		';
	}

	private function select_template(): string {
		return '
                <label class="wpie-field__label">
                    <span class="{{label_class}}">{{label}}</span>
                    <select name="{{name}}" data-field="{{data_field}}">
                       {{atts}}
                    </select>
                </label>
		';
	}

	private function checkbox_template(): string {
		return '<label class="wpie-field__label">
                    <input type="checkbox" data-field="{{data_field}}" {{checked}}>
					<input type="hidden" name="{{name}}" value="{{value}}"> 
                    <span class="{{label_class}}">{{label}}</span>
                </label>';
	}

	private function textarea_template(): string {
		return '
		<label class="wpie-field__label">
			<textarea name="{{name}}" data-field="{{data_field}}" {{atts}}>{{value}}</textarea>
			<span class="{{label_class}}">{{label}}</span>
		</label>
		';
	}

	private function editor_template(): string {
		return '
		<div class="wpie-field__label">
			<textarea name="{{name}}" data-field="{{data_field}}" {{atts}}>{{value}}</textarea>
			<span class="{{label_class}}">{{label}}</span>
		</div>
		';
	}
}