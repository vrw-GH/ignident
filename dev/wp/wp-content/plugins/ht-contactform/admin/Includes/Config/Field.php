<?php
namespace HTContactFormAdmin\Includes\Config;
class Field {
    private static $instance = null;

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct() {
        
    }

    /**
     * Create Field Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, options: array|null}
     */
    public function create(array $args = []) {
        $args = array_merge([
            'id' => '',
            'label' => '',
            'type' => 'input',
            'info' => '',
            'desc' => '',
            'option_type' =>  '',
            'value' => '',
            'options' => '',
            'disabled' => false,
            'dependency' => null,
        ], $args);
        return $args;
    }

    /**
     * Field Admin Label Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function admin_label(array $args = []) {
        return $this->create([
            'id' => 'admin_label',
            'label' => __('Admin Label', 'ht-contactform'),
            'info' => __('Admin label is field title which will be used for admin field title.', 'ht-contactform'),
            'value' => $args['value'] ?? 'Text',
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Label Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function label(array $args = []) {
        return $this->create([
            'id' => 'label',
            'label' => __('Field Label', 'ht-contactform'),
            'info' => __('This is the field title the user will see when filling out the form.', 'ht-contactform'),
            'value' => $args['value'] ?? 'Text Input',
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Label Hide Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function label_hide(array $args = []) {
        return $this->create([
            'id' => 'hide_label',
            'label' => __('Hide Label', 'ht-contactform'),
            'type' => 'switch',
            'info' => __('Toggle this switch to hide the field label from display.', 'ht-contactform'),
            'value' => false,
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Label Position Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function label_position(array $args = []) {
        return $this->create([
            'id' => 'label_position',
            'label' => __('Label Position', 'ht-contactform'),
            'type' => 'select',
            'info' => __('Specify the position for the field label relative to the input area.', 'ht-contactform'),
            'value' => '',
            'options' => [
                [
                    'value' => '',
                    'label' => __('Default', 'ht-contactform'),
                ],
                [
                    'value' => 'top',
                    'label' => __('Top', 'ht-contactform'),
                ],
                [
                    'value' => 'right',
                    'label' => __('Right', 'ht-contactform'),
                ],
                [
                    'value' => 'bottom',
                    'label' => __('Bottom', 'ht-contactform'),
                ],
                [
                    'value' => 'left',
                    'label' => __('Left', 'ht-contactform'),
                ],
            ],
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Placeholder Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function placeholder(array $args = []) {
        return $this->create([
            'id' => 'placeholder',
            'label' => __('Placeholder Text', 'ht-contactform'),
            'info' => __('Provide placeholder text that is visible when the field is empty.', 'ht-contactform'),
            'value' => $args['value'] ?? '',
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Required Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function required(array $args = []) {
        return $this->create([
            'id' => 'required',
            'label' => __('Required', 'ht-contactform'),
            'type' => 'switch',
            'info' => __('Toggle to mark the field as mandatory for form submission.', 'ht-contactform'),
            'value' => false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Required Error Message Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function required_message(array $args = []) {
        return $this->create([
            'id' => 'required_message',
            'label' => __('Required Error Message', 'ht-contactform'),
            'info' => __('This message will be shown if validation fails for Required. Leave empty to use global message. Configure Global Message from: Global settings > Validation Messages', 'ht-contactform'),
            'value' => __('This field is required', 'ht-contactform'),
            'disabled' => $args['disabled'] ?? false,
            'dependency' => [
                'relation' => 'AND',
                'rules' => [
                    [
                        'id' => 'required',
                        'value' => true,
                        'compare' => '==',
                    ]
                ]
            ],
        ]);
    }

    /**
     * Field Size Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, options: array, type: string, dependency: array|null}
     */
    public function size(array $args = []) {
        return $this->create([
            'id' => 'field_size',
            'label' => __('Field Size', 'ht-contactform'),
            'type' => 'select',
            'info' => __('Choose the display size for the form field.', 'ht-contactform'),
            'value' => 'medium',
            'disabled' => $args['disabled'] ?? false,
            'options' => [
                [
                    'value' => 'small',
                    'label' => __('Small', 'ht-contactform'),
                ],
                [
                    'value' => 'medium',
                    'label' => __('Medium', 'ht-contactform'),
                ],
                [
                    'value' => 'large',
                    'label' => __('Large', 'ht-contactform'),
                ],
            ],
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Default Value Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function value(array $args = []) {
        return $this->create([
            'id' => 'default_value',    
            'label' => __('Default Value', 'ht-contactform'),
            'type' => $args['type'] ?? 'input',
            'value' => $args['value'] ?? '',
            'info' => __('Define a default value to pre-populate the field upon form load.', 'ht-contactform'),
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Class Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function class(array $args = []) {
        return $this->create([
            'id' => 'field_class',
            'label' => __('Field Class', 'ht-contactform'),
            'info' => __('Specify a CSS class for custom styling of the field element.', 'ht-contactform'),
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Help Message Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function message(array $args = []) {
        return $this->create([
            'id' => 'help_message',    
            'label' => __('Help Message', 'ht-contactform'),
            'type' => 'textarea',
            'info' => __('Provide additional guidance that is displayed as a tooltip or below the field.', 'ht-contactform'),
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }


    /**
     * Field Message Position Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function message_position(array $args = []) {
        return $this->create([
            'id' => 'help_message_position',
            'label' => __('Message Position', 'ht-contactform'),
            'type' => 'select',
            'info' => __('Specify the position for the field help message.', 'ht-contactform'),
            'value' => '',
            'options' => [
                [
                    'value' => '',
                    'label' => __('Default', 'ht-contactform'),
                ],
                [
                    'value' => 'next_to_label',
                    'label' => __('Next to Label as Tooltip', 'ht-contactform'),
                ],
                [
                    'value' => 'below_input_element',
                    'label' => __('Below Input Element', 'ht-contactform'),
                ],
            ],
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Prefix Label Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function prefix_label(array $args = []) {
        return $this->create([
            'id' => 'prefix_label',    
            'label' => __('Prefix Label', 'ht-contactform'),
            'info' => __('Text to display before the field input, serving as a prefix.', 'ht-contactform'),
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Suffix Label Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function suffix_label(array $args = []) {
        return $this->create([
            'id' => 'suffix_label',    
            'label' => __('Suffix Label', 'ht-contactform'),
            'info' => __('Text to display after the field input, serving as a suffix.', 'ht-contactform'),
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Name Attribute Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function name_attribute(array $args = []) {
        return $this->create([
            'id' => 'name_attribute',    
            'label' => __('Name Attribute', 'ht-contactform'),
            'info' => __('Set a unique name attribute for the field to be used during form submission.', 'ht-contactform'),
            'value' => $args['value'] ?? 'text',
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Max Length Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function max_length(array $args = []) {
        return $this->create([
            'id' => 'max_length',
            'type' => 'input',
            'label' => __('Max text length', 'ht-contactform'),
            'info' => __('Define the maximum number of characters allowed for the text input.', 'ht-contactform'),
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Rows Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function rows(array $args = []) {
        return $this->create([
            'id' => 'rows',    
            'label' => __('Rows', 'ht-contactform'),
            'info' => __('Set the number of rows for the textarea element to control its height.', 'ht-contactform'),
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Columns Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function columns(array $args = []) {
        return $this->create([
            'id' => 'columns',    
            'label' => __('Columns', 'ht-contactform'),
            'info' => __('Specify the number of columns for the textarea element to determine its width.', 'ht-contactform'),
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Mask Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string}
     */
    public function mask(array $args = []) {
        return $this->create([
            'id' => 'mask',    
            'label' => __('Mask', 'ht-contactform'),
            'type' => 'select',
            'info' => __('Choose an input mask to format user input consistently.', 'ht-contactform'),
            'options' =>  [
                [
                    'value' => '',
                    'label' => __('None', 'ht-contactform'),
                ],
                [
                    'value' => '(999) 999-9999',
                    'label' => __('(999) 999-9999', 'ht-contactform'),
                ],
                [
                    'value' => 'MM/DD/YYYY',
                    'label' => __('MM/DD/YYYY', 'ht-contactform'),
                ],
                [
                    'value' => '999-99-9999',
                    'label' => __('999-99-9999', 'ht-contactform'),
                ],
                [
                    'value' => '9999 9999 9999 9999',
                    'label' => __('9999 9999 9999 9999', 'ht-contactform'),
                ],
                [
                    'value' => '99999-9999',
                    'label' => __('99999-9999', 'ht-contactform'),
                ],
                [
                    'value' => 'HH:MM',
                    'label' => __('HH:MM', 'ht-contactform'),
                ],
                [
                    'value' => '$999.99',
                    'label' => __('$999.99', 'ht-contactform'),
                ],
            ],
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Options for select/choices
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function options(array $args = []) {
        return $this->create([
            'id' => 'options',
            'label' => __('Options', 'ht-contactform'),
            'type' => 'repeater',
            'info' => __('Define selectable options with visual cues for default selection.', 'ht-contactform'),
            'option_type' =>  $args['option_type'] ?? 'radio',
            'value' => $args['value'] ?? [
                [ 'label' => "First Option", 'value' => "first_option", 'selected' => false ],
                [ 'label' => "Second Option", 'value' => "second_option", 'selected' => false ]
            ],
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }
    
    /**
     * Field Searchable Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function searchable(array $args = []) {
        return $this->create([
            'id' => 'searchable',    
            'label' => __('Searchable', 'ht-contactform'),
            'type' => 'switch',
            'info' => __('Toggle to enable search functionality within this field.', 'ht-contactform'),
            'value' => false,
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }
    
    /**
     * Field Multiple Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function multiple(array $args = []) {
        return $this->create([
            'id' => 'multiple',    
            'label' => __('Multiple', 'ht-contactform'),
            'type' => 'switch',
            'info' => __('Toggle to allow multiple selections within the field.', 'ht-contactform'),
            'value' => false,
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Max Selection Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function max_selection(array $args = []) {
        return $this->create([
            'id' => 'max_selection',    
            'label' => __('Max Selection', 'ht-contactform'),
            'info' => __('Limit the number of selectable options to prevent excessive choices.', 'ht-contactform'),
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Choices Layout
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function layout(array $args = []) {
        return $this->create([
            'id' => 'layout',    
            'label' => __('Layout', 'ht-contactform'),
            'type' => 'select',
            'info' => __('Specify the layout style for arranging the field options or appearance.', 'ht-contactform'),
            'value' => 'col1',
            'options' => [
                [
                    'value' => 'col1',
                    'label' => __('One Column', 'ht-contactform'),
                ],
                [
                    'value' => 'col2',
                    'label' => __('Two Columns', 'ht-contactform'),
                ],
                [
                    'value' => 'col3',
                    'label' => __('Three Columns', 'ht-contactform'),
                ],
                [
                    'value' => 'col4',
                    'label' => __('Four Columns', 'ht-contactform'),
                ],
                [
                    'value' => 'inline',
                    'label' => __('Inline Layout', 'ht-contactform'),
                ],
                [
                    'value' => 'button',
                    'label' => __('Button Type Layout', 'ht-contactform'),
                ],
            ],
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Min Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function min(array $args = []) {
        return $this->create([
            'id' => 'min',    
            'label' => __('Min Value', 'ht-contactform'),
            'type' => 'number',
            'value' => 0,
            'info' => __('Define the minimum value for numerical input.', 'ht-contactform'),
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Max Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function max(array $args = []) {
        return $this->create([
            'id' => 'max',    
            'label' => __('Max Value', 'ht-contactform'),
            'type' => 'number',
            'value' => 100,
            'info' => __('Define the maximum value for numerical input.', 'ht-contactform'),
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Step Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function step(array $args = []) {
        return $this->create([
            'id' => 'step',    
            'label' => __('Step', 'ht-contactform'),
            'type' => 'number',
            'value' => 1,
            'info' => __('Set the step increment for numerical input control.', 'ht-contactform'),
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Email Confirmation Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function email_confirmation(array $args = []) {
        return $this->create([
            'id' => 'email_confirmation',    
            'label' => __('Enable Email Confirmation', 'ht-contactform'),
            'type' => 'switch',
            'info' => __('Enable email confirmation by requiring the email address to be entered twice.', 'ht-contactform'),
            'value' => false,
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Email Validation Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function email_validation(array $args = []) {
        return $this->create([
            'id' => 'email_validation',    
            'label' => __('Validate Email', 'ht-contactform'),
            'type' => 'switch',
            'info' => __('Activate email validation to verify the format and integrity of the provided address.', 'ht-contactform'),
            'value' => false,
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Email Unique Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function email_unique(array $args = []) {
        return $this->create([
            'id' => 'email_unique',    
            'label' => __('Validate as unique', 'ht-contactform'),
            'type' => 'switch',
            'info' => __('Ensure the email address is unique by comparing against previous submissions', 'ht-contactform'),
            'value' => false,
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Slider Display Value Field
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string}
     */
    public function slider_display_value(array $args = []) {
        return $this->create([
            'id' => 'value_display',    
            'label' => __('Value Display', 'ht-contactform'),
            'type' => 'input',
            'info' => __('Show the current slider value in real-time beneath the slider control.', 'ht-contactform'),
            'value' => 'Selected Value: {value}',
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Name Format
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function name_format(array $args = []) {
        return $this->create([
            'id' => 'name_format',    
            'label' => __('Format', 'ht-contactform'),
            'type' => 'select',
            'info' => __('Select format to use for the name form field.', 'ht-contactform'),
            'value' => 'first_last',
            'options' => [
                [
                    'value' => 'simple',
                    'label' => __('Simple', 'ht-contactform'),
                ],
                [
                    'value' => 'first_last',
                    'label' => __('First and Last Name', 'ht-contactform'),
                ],
                [
                    'value' => 'first_middle_last',
                    'label' => __('First, Middle, and Last Name', 'ht-contactform'),
                ],
            ],
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Names
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string, dependency: array|null}
     */
    public function names(array $args = []) {
        return $this->create([
            'id' => 'names',    
            'label' => __('Names', 'ht-contactform'),
            'type' => 'names',
            'info' => __('Select format to use for the name form field.', 'ht-contactform'),
            'value' => [
                'simple' => [
                    "label" => __('Name', 'ht-contactform'),
                    "placeholder" => '',
                    "value" => '',
                    "help_message" => '',
                    "required" => false,
                    "required_message" => __('This field is required', 'ht-contactform'),
                ],
                'first_name' => [
                    "label" => __('First Name', 'ht-contactform'),
                    "placeholder" => '',
                    "value" => '',
                    "help_message" => '',
                    "required" => false,
                    "required_message" => __('This field is required', 'ht-contactform'),
                ],
                'middle_name' => [
                    "label" => __('Middle Name', 'ht-contactform'),
                    "placeholder" => '',
                    "value" => '',
                    "help_message" => '',
                    "required" => false,
                    "required_message" => __('This field is required', 'ht-contactform'),
                ],
                'last_name' => [
                    "label" => __('Last Name', 'ht-contactform'),
                    "placeholder" => '',
                    "value" => '',
                    "help_message" => '',
                    "required" => false,
                    "required_message" => __('This field is required', 'ht-contactform'),
                ],
            ],
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Enable Condition Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string}
     */
    public function enable_condition(array $args = []) {
        return $this->create([
            'id' => 'enable_condition',    
            'label' => __('Enable Condition', 'ht-contactform'),
            'type' => 'switch',
            'info' => __('Enable condition to control field visibility based on other field values.', 'ht-contactform'),
            'value' => false,
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Field Conditional Match Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string}
     */
    public function conditional_match(array $args = []) {
        return $this->create([
            'id' => 'conditional_match',    
            'label' => __('Conditional Match', 'ht-contactform'),
            'type' => 'radio',
            'info' => __('Select the type of match to apply for conditional logic.', 'ht-contactform'),
            'value' => 'all',
            'options' => [
                [
                    'value' => 'all',
                    'label' => __('All', 'ht-contactform'),
                ],
                [
                    'value' => 'any',
                    'label' => __('Any', 'ht-contactform'),
                ],
            ],
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? [
                'relation' => 'AND',
                'rules' => [
                    [
                        'id' => 'enable_condition',
                        'compare' => '==',
                        'value' => true,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Field Conditional Logic Option
     * @param array $args
     * @return array{value: mixed, id: string, info: string, label: string, type: string}
     */
    public function conditional_logic(array $args = []) {
        return $this->create([
            'id' => 'conditional_logic',
            // 'type' => 'select',
            'type' => 'cl_repeater',
            'value' => [
                [
                    'field' => '',
                    'operator' => '==',
                    'value' => '',
                ]
            ],
            'disabled' => $args['disabled'] ?? false,
            'dependency' => $args['dependency'] ?? [
                'relation' => 'AND',
                'rules' => [
                    [
                        'id' => 'enable_condition',
                        'value' => true,
                        'compare' => '==',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Sub Fields Option - Fields that can be repeated
     * @param array $args
     * @return array{value: mixed, id: string, label: string, type: string, info: string}
     */
    public function sub_fields(array $args = []) {
        return $this->create([
            'id' => 'sub_fields',
            'label' => __('Sub Fields', 'ht-contactform'),
            'type' => 'repeater_sub_fields',
            'info' => __('Select which fields to include in each repeater row.', 'ht-contactform'),
            'value' => $args['value'] ?? [],
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Add Button Text Option
     * @param array $args
     * @return array{value: mixed, id: string, label: string, type: string, info: string}
     */
    public function add_button_text(array $args = []) {
        return $this->create([
            'id' => 'add_button_text',
            'label' => __('Add Button Text', 'ht-contactform'),
            'type' => 'text',
            'info' => __('Text for the "Add Row" button.', 'ht-contactform'),
            'value' => $args['value'] ?? __('Add Row', 'ht-contactform'),
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Remove Button Text Option
     * @param array $args
     * @return array{value: mixed, id: string, label: string, type: string, info: string}
     */
    public function remove_button_text(array $args = []) {
        return $this->create([
            'id' => 'remove_button_text',
            'label' => __('Remove Button Text', 'ht-contactform'),
            'type' => 'text',
            'info' => __('Text for the "Remove Row" button.', 'ht-contactform'),
            'value' => $args['value'] ?? __('Remove', 'ht-contactform'),
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Row Label Option
     * @param array $args
     * @return array{value: mixed, id: string, label: string, type: string, info: string}
     */
    public function row_label(array $args = []) {
        return $this->create([
            'id' => 'row_label',
            'label' => __('Row Label', 'ht-contactform'),
            'type' => 'text',
            'info' => __('Label template for each row. Use {n} for row number. Example: "Item {n}"', 'ht-contactform'),
            'value' => $args['value'] ?? __('Row {n}', 'ht-contactform'),
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Post Type Option
     * @param array $args
     * @return array
     */
    public function post_type(array $args = []) {
        // Get all public post types
        $post_types = get_post_types(['public' => true], 'objects');
        $options = [];

        foreach ($post_types as $post_type) {
            // Skip attachments
            if ($post_type->name === 'attachment') {
                continue;
            }

            // Get post count
            $count = wp_count_posts($post_type->name);
            $publish_count = isset($count->publish) ? (int) $count->publish : 0;

            $options[] = [
                'value' => $post_type->name,
                'label' => sprintf('%s (%d)', $post_type->labels->name, $publish_count),
            ];
        }

        return $this->create([
            'id' => 'post_type',
            'label' => __('Post Type', 'ht-contactform'),
            'type' => 'select',
            'info' => __('Select which post type to query.', 'ht-contactform'),
            'value' => $args['value'] ?? 'post',
            'options' => $options,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Post Status Option
     * @param array $args
     * @return array
     */
    public function post_status(array $args = []) {
        return $this->create([
            'id' => 'post_status',
            'label' => __('Post Status', 'ht-contactform'),
            'type' => 'select',
            'info' => __('Filter posts by status.', 'ht-contactform'),
            'value' => $args['value'] ?? 'publish',
            'options' => [
                ['value' => 'publish', 'label' => __('Published', 'ht-contactform')],
                ['value' => 'draft', 'label' => __('Draft', 'ht-contactform')],
                ['value' => 'pending', 'label' => __('Pending Review', 'ht-contactform')],
                ['value' => 'any', 'label' => __('Any', 'ht-contactform')],
            ],
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

    /**
     * Posts Per Page Option
     * @param array $args
     * @return array
     */
    public function posts_per_page(array $args = []) {
        return $this->create([
            'id' => 'posts_per_page',
            'label' => __('Posts Limit', 'ht-contactform'),
            'type' => 'number',
            'info' => __('Maximum number of posts to show. Default: 100', 'ht-contactform'),
            'value' => $args['value'] ?? 100,
            'dependency' => $args['dependency'] ?? null,
        ]);
    }

}