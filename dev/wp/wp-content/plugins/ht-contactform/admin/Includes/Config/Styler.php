<?php
namespace HTContactFormAdmin\Includes\Config;

use HTContactFormAdmin\Includes\Config\Field;

class Styler {

    private static $instance = null;
    private static $field = null;

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct() {
        self::$field = Field::get_instance();
    }

    /**
     * Get available form settings
     * 
     * @return array Array of form settings
     */
    public function get_settings(): array {
        return apply_filters('ht_form_styler_settings', array_merge(
            [
                self::$field->create([
                    'id' => 'enable_styler',
                    'label' => __('Enable Styler', 'ht-contactform'),
                    'type' => 'switch',
                    'value' => false,
                ]),
            ],
            $this->get_label_settings(),
            $this->get_help_text_settings(),
            $this->get_help_tooltip_settings(),
            $this->get_field_settings(),
            $this->get_checkbox_settings(),
            $this->get_radio_settings(),
            $this->get_range_settings(),
            $this->get_select_settings(),
            $this->get_gdpr_settings(),
            $this->get_terms_conditions_settings(),
            $this->get_date_time_settings(),
            $this->get_submit_button_settings(),
            $this->get_signature_settings(),
            $this->get_ratings_settings(),
            $this->get_file_upload_settings(),
            $this->get_section_break_settings(),
            $this->get_nps_settings(),
        ));
    }

    public function get_label_settings(): array {
        return apply_filters('ht_form_styler_label_settings', [
            self::$field->create([
                'id' => 'label_color',
                'label' => __('Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#111827',
                'group' => 'label',
            ]),
            self::$field->create([
                'id' => 'label_size',
                'label' => __('Font Size', 'ht-contactform'),
                'type' => 'number',
                'value' => '14',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'label',
            ]),
            self::$field->create([
                'id' => 'label_size_sm',
                'label' => __('Font Size (Small)', 'ht-contactform'),
                'type' => 'number',
                'value' => '14',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'label',
            ]),
            self::$field->create([
                'id' => 'label_size_lg',
                'label' => __('Font Size (Large)', 'ht-contactform'),
                'type' => 'number',
                'value' => '14',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'label',
            ]),
            self::$field->create([
                'id' => 'label_weight',
                'label' => __('Font Weight', 'ht-contactform'),
                'type' => 'select',
                'value' => '500',
                'options' => [
                    [
                        'label' => __('100', 'ht-contactform'),
                        'value' => '100',
                    ],
                    [
                        'label' => __('200', 'ht-contactform'),
                        'value' => '200',
                    ],
                    [
                        'label' => __('300', 'ht-contactform'),
                        'value' => '300',
                    ],
                    [
                        'label' => __('400', 'ht-contactform'),
                        'value' => '400',
                    ],
                    [
                        'label' => __('500', 'ht-contactform'),
                        'value' => '500',
                    ],
                    [
                        'label' => __('600', 'ht-contactform'),
                        'value' => '600',
                    ],
                    [
                        'label' => __('700', 'ht-contactform'),
                        'value' => '700',
                    ],
                    [
                        'label' => __('800', 'ht-contactform'),
                        'value' => '800',
                    ],
                    [
                        'label' => __('900', 'ht-contactform'),
                        'value' => '900',
                    ],
                ],
                'group' => 'label',
            ]),
            self::$field->create([
                'id' => 'label_line_height',
                'label' => __('Line Height', 'ht-contactform'),
                'type' => 'number',
                'value' => '1.5',
                'suffix' => '',
                'min' => '1',
                'step' => '0.1',
                'group' => 'label',
            ]),
        ]);
    }

    public function get_help_text_settings(): array {
        return apply_filters('ht_form_styler_help_text_settings', [
            self::$field->create([
                'id' => 'help_text_color',
                'label' => __('Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#868e96',
                'group' => 'help_text',
            ]),
            self::$field->create([
                'id' => 'help_text_size',
                'label' => __('Font Size', 'ht-contactform'),
                'type' => 'number',
                'value' => '12',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'help_text',
            ]),
            self::$field->create([
                'id' => 'help_text_weight',
                'label' => __('Font Weight', 'ht-contactform'),
                'type' => 'select',
                'value' => '400',
                'options' => [
                    [
                        'label' => __('100', 'ht-contactform'),
                        'value' => '100',
                    ],
                    [
                        'label' => __('200', 'ht-contactform'),
                        'value' => '200',
                    ],
                    [
                        'label' => __('300', 'ht-contactform'),
                        'value' => '300',
                    ],
                    [
                        'label' => __('400', 'ht-contactform'),
                        'value' => '400',
                    ],
                    [
                        'label' => __('500', 'ht-contactform'),
                        'value' => '500',
                    ],
                    [
                        'label' => __('600', 'ht-contactform'),
                        'value' => '600',
                    ],
                    [
                        'label' => __('700', 'ht-contactform'),
                        'value' => '700',
                    ],
                    [
                        'label' => __('800', 'ht-contactform'),
                        'value' => '800',
                    ],
                    [
                        'label' => __('900', 'ht-contactform'),
                        'value' => '900',
                    ],
                ],
                'group' => 'help_text',
            ]),
            self::$field->create([
                'id' => 'help_text_line_height',
                'label' => __('Line Height', 'ht-contactform'),
                'type' => 'number',
                'value' => '1.5',
                'suffix' => '',
                'min' => '1',
                'step' => '0.1',
                'group' => 'help_text',
            ]),
        ]);
    }

    public function get_help_tooltip_settings(): array {
        return apply_filters('ht_form_styler_help_tooltip_settings', [
            self::$field->create([
                'id' => 'help_tooltip_color',
                'label' => __('Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#ffffff',
                'group' => 'help_tooltip',
            ]),
            self::$field->create([
                'id' => 'help_tooltip_bg_color',
                'label' => __('Background', 'ht-contactform'),
                'type' => 'color',
                'value' => '#323232',
                'group' => 'help_tooltip',
            ]),
            self::$field->create([
                'id' => 'help_tooltip_radius',
                'label' => __('Radius', 'ht-contactform'),
                'type' => 'number',
                'value' => '4',
                'suffix' => 'px',
                'min' => '0',
                'group' => 'help_tooltip',
            ]),
            self::$field->create([
                'id' => 'help_tooltip_size',
                'label' => __('Font Size', 'ht-contactform'),
                'type' => 'number',
                'value' => '12',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'help_tooltip',
            ]),
            self::$field->create([
                'id' => 'help_tooltip_weight',
                'label' => __('Font Weight', 'ht-contactform'),
                'type' => 'select',
                'value' => '400',
                'options' => [
                    [
                        'label' => __('100', 'ht-contactform'),
                        'value' => '100',
                    ],
                    [
                        'label' => __('200', 'ht-contactform'),
                        'value' => '200',
                    ],
                    [
                        'label' => __('300', 'ht-contactform'),
                        'value' => '300',
                    ],
                    [
                        'label' => __('400', 'ht-contactform'),
                        'value' => '400',
                    ],
                    [
                        'label' => __('500', 'ht-contactform'),
                        'value' => '500',
                    ],
                    [
                        'label' => __('600', 'ht-contactform'),
                        'value' => '600',
                    ],
                    [
                        'label' => __('700', 'ht-contactform'),
                        'value' => '700',
                    ],
                    [
                        'label' => __('800', 'ht-contactform'),
                        'value' => '800',
                    ],
                    [
                        'label' => __('900', 'ht-contactform'),
                        'value' => '900',
                    ],
                ],
                'group' => 'help_tooltip',
            ]),
            self::$field->create([
                'id' => 'help_tooltip_line_height',
                'label' => __('Line Height', 'ht-contactform'),
                'type' => 'number',
                'value' => '1.5',
                'suffix' => '',
                'min' => '1',
                'step' => '0.1',
                'group' => 'help_tooltip',
            ]),
            self::$field->create([
                'id' => 'help_tooltip_icon_color',
                'label' => __('Icon Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#323232',
                'group' => 'help_tooltip',
            ]),
            self::$field->create([
                'id' => 'help_tooltip_icon_size',
                'label' => __('Icon Size', 'ht-contactform'),
                'type' => 'number',
                'value' => '14',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'help_tooltip',
            ]),
        ]);
    }

    public function get_field_settings(): array {
        return apply_filters('ht_form_styler_field_settings', [
            self::$field->create([
                'id' => 'field_color',
                'label' => __('Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#000000',
                'group' => 'field',
            ]),
            self::$field->create([
                'id' => 'field_bg_color',
                'label' => __('Background', 'ht-contactform'),
                'type' => 'color',
                'value' => '#ffffff',
                'group' => 'field',
            ]),
            self::$field->create([
                'id' => 'field_radius',
                'label' => __('Radius', 'ht-contactform'),
                'type' => 'number',
                'value' => '4',
                'suffix' => 'px',
                'min' => '0',
                'group' => 'field',
            ]),
            self::$field->create([
                'id' => 'field_size',
                'label' => __('Font Size', 'ht-contactform'),
                'type' => 'number',
                'value' => '14',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'field',
            ]),
            self::$field->create([
                'id' => 'field_size_sm',
                'label' => __('Font Size (Small)', 'ht-contactform'),
                'type' => 'number',
                'value' => '16',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'field',
            ]),
            self::$field->create([
                'id' => 'field_size_lg',
                'label' => __('Font Size (Large)', 'ht-contactform'),
                'type' => 'number',
                'value' => '18',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'field',
            ]),
            self::$field->create([
                'id' => 'field_weight',
                'label' => __('Font Weight', 'ht-contactform'),
                'type' => 'select',
                'value' => '400',
                'options' => [
                    [
                        'label' => __('100', 'ht-contactform'),
                        'value' => '100',
                    ],
                    [
                        'label' => __('200', 'ht-contactform'),
                        'value' => '200',
                    ],
                    [
                        'label' => __('300', 'ht-contactform'),
                        'value' => '300',
                    ],
                    [
                        'label' => __('400', 'ht-contactform'),
                        'value' => '400',
                    ],
                    [
                        'label' => __('500', 'ht-contactform'),
                        'value' => '500',
                    ],
                    [
                        'label' => __('600', 'ht-contactform'),
                        'value' => '600',
                    ],
                    [
                        'label' => __('700', 'ht-contactform'),
                        'value' => '700',
                    ],
                    [
                        'label' => __('800', 'ht-contactform'),
                        'value' => '800',
                    ],
                    [
                        'label' => __('900', 'ht-contactform'),
                        'value' => '900',
                    ],
                ],
                'group' => 'field',
            ]),
            self::$field->create([
                'id' => 'field_line_height',
                'label' => __('Line Height', 'ht-contactform'),
                'type' => 'number',
                'value' => '1.5',
                'suffix' => '',
                'min' => '1',
                'step' => '0.1',
                'group' => 'field',
            ]),
            self::$field->create([
                'id' => 'field_border_width',
                'label' => __('Border Width', 'ht-contactform'),
                'type' => 'number',
                'value' => '1',
                'suffix' => 'px',
                'min' => '0',
                'group' => 'field',
            ]),
            self::$field->create([
                'id' => 'field_border_style',
                'label' => __('Border Style', 'ht-contactform'),
                'type' => 'select',
                'value' => 'solid',
                'options' => [
                    [
                        'value' => 'solid',
                        'label' => __('Solid', 'ht-contactform'),
                    ],
                    [
                        'value' => 'dashed',
                        'label' => __('Dashed', 'ht-contactform'),
                    ],
                    [
                        'value' => 'dotted',
                        'label' => __('Dotted', 'ht-contactform'),
                    ],
                ],
                'group' => 'field',
            ]),
            self::$field->create([
                'id' => 'field_border_color',
                'label' => __('Border Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#dddddd',
                'group' => 'field',
            ]),
            self::$field->create([
                'id' => 'field_border_color_focus',
                'label' => __('Border Color (Focus)', 'ht-contactform'),
                'type' => 'color',
                'value' => '#2563eb',
                'group' => 'field',
            ]),
            self::$field->create([
                'id' => 'field_error_color',
                'label' => __('Error Text Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#ef4444',
                'group' => 'field',
            ]),
            self::$field->create([
                'id' => 'field_error_border_color',
                'label' => __('Error Border Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#ef4444',
                'group' => 'field',
            ]),
            self::$field->create([
                'id' => 'field_pf_sf_color',
                'label' => __('Prefix/Suffix Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#000000',
                'group' => 'field',
            ]),
            self::$field->create([
                'id' => 'field_pf_sf_bg_color',
                'label' => __('Prefix/Suffix Background Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#eeeeee',
                'group' => 'field',
            ]),
        ]);
    }

    public function get_checkbox_settings(): array {
        return apply_filters('ht_form_styler_checkbox_settings', [
            self::$field->create([
                'id' => 'checkbox_color',
                'label' => __('Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#000000',
                'group' => 'checkbox',
            ]),
            self::$field->create([
                'id' => 'checkbox_font_size',
                'label' => __('Font Size', 'ht-contactform'),
                'type' => 'number',
                'value' => '14',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'checkbox',
            ]),
            self::$field->create([
                'id' => 'checkbox_font_size_sm',
                'label' => __('Font Size (Small)', 'ht-contactform'),
                'type' => 'number',
                'value' => '16',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'checkbox',
            ]),
            self::$field->create([
                'id' => 'checkbox_font_size_lg',
                'label' => __('Font Size (Large)', 'ht-contactform'),
                'type' => 'number',
                'value' => '18',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'checkbox',
            ]),
            self::$field->create([
                'id' => 'checkbox_font_weight',
                'label' => __('Font Weight', 'ht-contactform'),
                'type' => 'select',
                'value' => '400',
                'options' => [
                    [
                        'label' => __('100', 'ht-contactform'),
                        'value' => '100',
                    ],
                    [
                        'label' => __('200', 'ht-contactform'),
                        'value' => '200',
                    ],
                    [
                        'label' => __('300', 'ht-contactform'),
                        'value' => '300',
                    ],
                    [
                        'label' => __('400', 'ht-contactform'),
                        'value' => '400',
                    ],
                    [
                        'label' => __('500', 'ht-contactform'),
                        'value' => '500',
                    ],
                    [
                        'label' => __('600', 'ht-contactform'),
                        'value' => '600',
                    ],
                    [
                        'label' => __('700', 'ht-contactform'),
                        'value' => '700',
                    ],
                    [
                        'label' => __('800', 'ht-contactform'),
                        'value' => '800',
                    ],
                    [
                        'label' => __('900', 'ht-contactform'),
                        'value' => '900',
                    ],
                ],
                'group' => 'checkbox',
            ]),
            self::$field->create([
                'id' => 'checkbox_line_height',
                'label' => __('Line Height', 'ht-contactform'),
                'type' => 'number',
                'value' => '1.5',
                'suffix' => '',
                'min' => '1',
                'step' => '0.1',
                'group' => 'checkbox',
            ]),
            self::$field->create([
                'id' => 'checkbox_size',
                'label' => __('Checkbox Size', 'ht-contactform'),
                'type' => 'number',
                'value' => '18',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'checkbox',
            ]),
            self::$field->create([
                'id' => 'checkbox_size_sm',
                'label' => __('Checkbox Size (Small)', 'ht-contactform'),
                'type' => 'number',
                'value' => '16',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'checkbox',
            ]),
            self::$field->create([
                'id' => 'checkbox_size_lg',
                'label' => __('Checkbox Size (Large)', 'ht-contactform'),
                'type' => 'number',
                'value' => '22',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'checkbox',
            ]),
            self::$field->create([
                'id' => 'checkbox_radius',
                'label' => __('Radius', 'ht-contactform'),
                'type' => 'number',
                'value' => '4',
                'suffix' => 'px',
                'min' => '0',
                'group' => 'checkbox',
            ]),
            self::$field->create([
                'id' => 'checkbox_gap',
                'label' => __('Checkbox & Label Gap', 'ht-contactform'),
                'type' => 'number',
                'value' => '10',
                'suffix' => 'px',
                'min' => '0',
                'group' => 'checkbox',
            ]),

            self::$field->create([
                'id' => 'checkbox_border_width',
                'label' => __('Border Width', 'ht-contactform'),
                'type' => 'number',
                'value' => '1',
                'suffix' => 'px',
                'min' => '0',
                'group' => 'checkbox',
            ]),
            self::$field->create([
                'id' => 'checkbox_border_style',
                'label' => __('Border Style', 'ht-contactform'),
                'type' => 'select',
                'value' => 'solid',
                'options' => [
                    [
                        'value' => 'solid',
                        'label' => __('Solid', 'ht-contactform'),
                    ],
                    [
                        'value' => 'dashed',
                        'label' => __('Dashed', 'ht-contactform'),
                    ],
                    [
                        'value' => 'dotted',
                        'label' => __('Dotted', 'ht-contactform'),
                    ],
                ],
                'group' => 'checkbox',
            ]),
            self::$field->create([
                'id' => 'checkbox_border_color',
                'label' => __('Border Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#dddddd',
                'group' => 'checkbox',
            ]),
            self::$field->create([
                'id' => 'checkbox_border_color_active',
                'label' => __('Border Color (Active)', 'ht-contactform'),
                'type' => 'color',
                'value' => '#2563eb',
                'group' => 'checkbox',
            ]),
            self::$field->create([
                'id' => 'checkbox_bg_color',
                'label' => __('Background', 'ht-contactform'),
                'type' => 'color',
                'value' => '#ffffff',
                'group' => 'checkbox',
            ]),
            self::$field->create([
                'id' => 'checkbox_bg_color_active',
                'label' => __('Background (Active)', 'ht-contactform'),
                'type' => 'color',
                'value' => '#2563eb',
                'group' => 'checkbox',
            ]), self::$field->create([
                'id' => 'checkbox_check_color',
                'label' => __('Check Icon Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#ffffff',
                'group' => 'checkbox',
            ]),
        ]);
    }

    public function get_radio_settings(): array {
        return apply_filters('ht_form_styler_radio_settings', [
            self::$field->create([
                'id' => 'radio_color',
                'label' => __('Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#000000',
                'group' => 'radio',
            ]),
            self::$field->create([
                'id' => 'radio_font_size',
                'label' => __('Font Size', 'ht-contactform'),
                'type' => 'number',
                'value' => '14',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'radio',
            ]),
            self::$field->create([
                'id' => 'radio_font_size_sm',
                'label' => __('Font Size (Small)', 'ht-contactform'),
                'type' => 'number',
                'value' => '16',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'radio',
            ]),
            self::$field->create([
                'id' => 'radio_font_size_lg',
                'label' => __('Font Size (Large)', 'ht-contactform'),
                'type' => 'number',
                'value' => '18',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'radio',
            ]),
            self::$field->create([
                'id' => 'radio_font_weight',
                'label' => __('Font Weight', 'ht-contactform'),
                'type' => 'select',
                'value' => '400',
                'options' => [
                    [
                        'label' => __('100', 'ht-contactform'),
                        'value' => '100',
                    ],
                    [
                        'label' => __('200', 'ht-contactform'),
                        'value' => '200',
                    ],
                    [
                        'label' => __('300', 'ht-contactform'),
                        'value' => '300',
                    ],
                    [
                        'label' => __('400', 'ht-contactform'),
                        'value' => '400',
                    ],
                    [
                        'label' => __('500', 'ht-contactform'),
                        'value' => '500',
                    ],
                    [
                        'label' => __('600', 'ht-contactform'),
                        'value' => '600',
                    ],
                    [
                        'label' => __('700', 'ht-contactform'),
                        'value' => '700',
                    ],
                    [
                        'label' => __('800', 'ht-contactform'),
                        'value' => '800',
                    ],
                    [
                        'label' => __('900', 'ht-contactform'),
                        'value' => '900',
                    ],
                ],
                'group' => 'radio',
            ]),
            self::$field->create([
                'id' => 'radio_line_height',
                'label' => __('Line Height', 'ht-contactform'),
                'type' => 'number',
                'value' => '1.5',
                'suffix' => '',
                'min' => '1',
                'step' => '0.1',
                'group' => 'radio',
            ]),
            self::$field->create([
                'id' => 'radio_size',
                'label' => __('Radio Size', 'ht-contactform'),
                'type' => 'number',
                'value' => '18',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'radio',
            ]),
            self::$field->create([
                'id' => 'radio_size_sm',
                'label' => __('Radio Size (Small)', 'ht-contactform'),
                'type' => 'number',
                'value' => '16',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'radio',
            ]),
            self::$field->create([
                'id' => 'radio_size_lg',
                'label' => __('Radio Size (Large)', 'ht-contactform'),
                'type' => 'number',
                'value' => '22',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'radio',
            ]),
            self::$field->create([
                'id' => 'radio_radius',
                'label' => __('Radius', 'ht-contactform'),
                'type' => 'number',
                'value' => '4',
                'suffix' => 'px',
                'min' => '0',
                'group' => 'radio',
            ]),
            self::$field->create([
                'id' => 'radio_gap',
                'label' => __('Radio & Label Gap', 'ht-contactform'),
                'type' => 'number',
                'value' => '10',
                'suffix' => 'px',
                'min' => '0',
                'group' => 'radio',
            ]),

            self::$field->create([
                'id' => 'radio_border_width',
                'label' => __('Border Width', 'ht-contactform'),
                'type' => 'number',
                'value' => '1',
                'suffix' => 'px',
                'min' => '0',
                'group' => 'radio',
            ]),
            self::$field->create([
                'id' => 'radio_border_style',
                'label' => __('Border Style', 'ht-contactform'),
                'type' => 'select',
                'value' => 'solid',
                'options' => [
                    [
                        'value' => 'solid',
                        'label' => __('Solid', 'ht-contactform'),
                    ],
                    [
                        'value' => 'dashed',
                        'label' => __('Dashed', 'ht-contactform'),
                    ],
                    [
                        'value' => 'dotted',
                        'label' => __('Dotted', 'ht-contactform'),
                    ],
                ],
                'group' => 'radio',
            ]),
            self::$field->create([
                'id' => 'radio_border_color',
                'label' => __('Border Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#dddddd',
                'group' => 'radio',
            ]),
            self::$field->create([
                'id' => 'radio_border_color_active',
                'label' => __('Border Color (Active)', 'ht-contactform'),
                'type' => 'color',
                'value' => '#2563eb',
                'group' => 'radio',
            ]),
            self::$field->create([
                'id' => 'radio_bg_color',
                'label' => __('Background', 'ht-contactform'),
                'type' => 'color',
                'value' => '#ffffff',
                'group' => 'radio',
            ]),
            self::$field->create([
                'id' => 'radio_bg_color_active',
                'label' => __('Background (Active)', 'ht-contactform'),
                'type' => 'color',
                'value' => '#2563eb',
                'group' => 'radio',
            ]), self::$field->create([
                'id' => 'radio_check_color',
                'label' => __('Check Icon Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#ffffff',
                'group' => 'radio',
            ]),
        ]);
    }

    public function get_range_settings(): array {
        return apply_filters('ht_form_styler_range_settings', [
            self::$field->create([
                'id' => 'range_bg_color',
                'label' => __('Background', 'ht-contactform'),
                'type' => 'color',
                'value' => '#e5e7eb',
                'group' => 'range',
            ]),
            self::$field->create([
                'id' => 'range_bg_color_active',
                'label' => __('Background (Selected)', 'ht-contactform'),
                'type' => 'color',
                'value' => '#2563eb',
                'group' => 'range',
            ]),
            self::$field->create([
                'id' => 'range_thumb_bg',
                'label' => __('Thumb Background', 'ht-contactform'),
                'type' => 'color',
                'value' => '#2563eb',
                'group' => 'range',
            ]),
            self::$field->create([
                'id' => 'range_thumb_radius',
                'label' => __('Thumb Radius', 'ht-contactform'),
                'type' => 'number',
                'value' => '50',
                'suffix' => 'px',
                'min' => '0',
                'max' => '100',
                'group' => 'range',
            ]),
            self::$field->create([
                'id' => 'range_value_color',
                'label' => __('Text Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#868e96',
                'group' => 'range',
            ]),
            self::$field->create([
                'id' => 'range_value_font_size',
                'label' => __('Text Font Size', 'ht-contactform'),
                'type' => 'number',
                'value' => '14',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'range',
            ]),
            self::$field->create([
                'id' => 'range_value_font_size_sm',
                'label' => __('Text Font Size (Small)', 'ht-contactform'),
                'type' => 'number',
                'value' => '12',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'range',
            ]),
            self::$field->create([
                'id' => 'range_value_font_size_lg',
                'label' => __('Text Font Size (Large)', 'ht-contactform'),
                'type' => 'number',
                'value' => '16',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'range',
            ]),
            self::$field->create([
                'id' => 'range_value_font_weight',
                'label' => __('Text Font Weight', 'ht-contactform'),
                'type' => 'select',
                'value' => '400',
                'options' => [
                    [
                        'label' => __('100', 'ht-contactform'),
                        'value' => '100',
                    ],
                    [
                        'label' => __('200', 'ht-contactform'),
                        'value' => '200',
                    ],
                    [
                        'label' => __('300', 'ht-contactform'),
                        'value' => '300',
                    ],
                    [
                        'label' => __('400', 'ht-contactform'),
                        'value' => '400',
                    ],
                    [
                        'label' => __('500', 'ht-contactform'),
                        'value' => '500',
                    ],
                    [
                        'label' => __('600', 'ht-contactform'),
                        'value' => '600',
                    ],
                    [
                        'label' => __('700', 'ht-contactform'),
                        'value' => '700',
                    ],
                    [
                        'label' => __('800', 'ht-contactform'),
                        'value' => '800',
                    ],
                    [
                        'label' => __('900', 'ht-contactform'),
                        'value' => '900',
                    ],
                ],
                'group' => 'range',
            ]),
            self::$field->create([
                'id' => 'range_value_line_height',
                'label' => __('Line Height', 'ht-contactform'),
                'type' => 'number',
                'value' => '1.5',
                'suffix' => '',
                'min' => '1',
                'step' => '0.1',
                'group' => 'range',
            ]),
        ]);
    }

    public function get_select_settings(): array {
        return apply_filters('ht_form_styler_select_settings', [
            self::$field->create([
                'id' => 'select_dp_selected_color',
                'label' => __('Option Selected Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#ffffff',
                'group' => 'select',
            ]),
            self::$field->create([
                'id' => 'select_dp_selected_bg',
                'label' => __('Option Selected Background', 'ht-contactform'),
                'type' => 'color',
                'value' => '#2563eb',
                'group' => 'select',
            ]),
            self::$field->create([
                'id' => 'select_dp_highlighted_color',
                'label' => __('Option Highlighted Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#ffffff',
                'group' => 'select',
            ]),
            self::$field->create([
                'id' => 'select_dp_highlighted_bg',
                'label' => __('Option Highlighted Background', 'ht-contactform'),
                'type' => 'color',
                'value' => '#0d55f1',
                'group' => 'select',
            ]),
            self::$field->create([
                'id' => 'select_pill_color',
                'label' => __('Pill Color', 'ht-contactform'),
                'info'  => __('For Multiselect Only', 'ht-contactform'),
                'type' => 'color',
                'value' => '#ffffff',
                'group' => 'select',
            ]),
            self::$field->create([
                'id' => 'select_pill_bg',
                'label' => __('Pill Background', 'ht-contactform'),
                'info'  => __('For Multiselect Only', 'ht-contactform'),
                'type' => 'color',
                'value' => '#2563eb',
                'group' => 'select',
            ]),
        ]);
    }

    public function get_gdpr_settings(): array {
        return apply_filters('ht_form_styler_gdpr_settings', [
            self::$field->create([
                'id' => 'gdpr_label_color',
                'label' => __('Text Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#111827',
                'group' => 'gdpr',
            ]),
            self::$field->create([
                'id' => 'gdpr_label_size',
                'label' => __('Text Font Size', 'ht-contactform'),
                'type' => 'number',
                'value' => '15',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'gdpr',
            ]),
            self::$field->create([
                'id' => 'gdpr_label_size_sm',
                'label' => __('Text Font Size (Small)', 'ht-contactform'),
                'type' => 'number',
                'value' => '13',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'gdpr',
            ]),
            self::$field->create([
                'id' => 'gdpr_label_size_lg',
                'label' => __('Text Font Size (Large)', 'ht-contactform'),
                'type' => 'number',
                'value' => '17',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'gdpr',
            ]),
            self::$field->create([
                'id' => 'gdpr_label_weight',
                'label' => __('Text Font Weight', 'ht-contactform'),
                'type' => 'select',
                'value' => '400',
                'options' => [
                    [
                        'label' => __('100', 'ht-contactform'),
                        'value' => '100',
                    ],
                    [
                        'label' => __('200', 'ht-contactform'),
                        'value' => '200',
                    ],
                    [
                        'label' => __('300', 'ht-contactform'),
                        'value' => '300',
                    ],
                    [
                        'label' => __('400', 'ht-contactform'),
                        'value' => '400',
                    ],
                    [
                        'label' => __('500', 'ht-contactform'),
                        'value' => '500',
                    ],
                    [
                        'label' => __('600', 'ht-contactform'),
                        'value' => '600',
                    ],
                    [
                        'label' => __('700', 'ht-contactform'),
                        'value' => '700',
                    ],
                    [
                        'label' => __('800', 'ht-contactform'),
                        'value' => '800',
                    ],
                    [
                        'label' => __('900', 'ht-contactform'),
                        'value' => '900',
                    ],
                ],
                'group' => 'gdpr',
            ]),
            self::$field->create([
                'id' => 'gdpr_label_line_height',
                'label' => __('Text Line Height', 'ht-contactform'),
                'type' => 'number',
                'value' => '1.5',
                'suffix' => '',
                'min' => '1',
                'step' => '0.1',
                'group' => 'gdpr',
            ]),
            self::$field->create([
                'id' => 'gdpr_size',
                'label' => __('Checkbox Size', 'ht-contactform'),
                'type' => 'number',
                'value' => '18',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'gdpr',
            ]),
            self::$field->create([
                'id' => 'gdpr_size_sm',
                'label' => __('Checkbox Size (Small)', 'ht-contactform'),
                'type' => 'number',
                'value' => '16',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'gdpr',
            ]),
            self::$field->create([
                'id' => 'gdpr_size_lg',
                'label' => __('Checkbox Size (Large)', 'ht-contactform'),
                'type' => 'number',
                'value' => '22',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'gdpr',
            ]),
            self::$field->create([
                'id' => 'gdpr_gap',
                'label' => __('Gap Between Checkbox & Text', 'ht-contactform'),
                'type' => 'number',
                'value' => '10',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'gdpr',
            ]),
            self::$field->create([
                'id' => 'gdpr_border_width',
                'label' => __('Checkbox Border Width', 'ht-contactform'),
                'type' => 'number',
                'value' => '1',
                'suffix' => 'px',
                'min' => '1',
                'group' => 'gdpr',
            ]),
            self::$field->create([
                'id' => 'gdpr_border_style',
                'label' => __('Checkbox Border Style', 'ht-contactform'),
                'type' => 'select',
                'value' => 'solid',
                'options' => [
                    [
                        'value' => 'solid',
                        'label' => __('Solid', 'ht-contactform'),
                    ],
                    [
                        'value' => 'dashed',
                        'label' => __('Dashed', 'ht-contactform'),
                    ],
                    [
                        'value' => 'dotted',
                        'label' => __('Dotted', 'ht-contactform'),
                    ],
                ],
                'group' => 'gdpr',
            ]),
            self::$field->create([
                'id' => 'gdpr_border_color',
                'label' => __('Checkbox Border Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#dddddd',
                'group' => 'gdpr',
            ]),
            self::$field->create([
                'id' => 'gdpr_border_color_active',
                'label' => __('Checkbox Border Color (Active)', 'ht-contactform'),
                'type' => 'color',
                'value' => '#2563eb',
                'group' => 'gdpr',
            ]),
            self::$field->create([
                'id' => 'gdpr_bg_color',
                'label' => __('Checkbox Background', 'ht-contactform'),
                'type' => 'color',
                'value' => '#2563eb',
                'group' => 'gdpr',
            ]),
            self::$field->create([
                'id' => 'gdpr_bg_color_active',
                'label' => __('Checkbox Background (Active)', 'ht-contactform'),
                'type' => 'color',
                'value' => '#2563eb',
                'group' => 'gdpr',
            ]),
            self::$field->create([
                'id' => 'gdpr_check_color',
                'label' => __('Checkbox Check Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#ffffff',
                'group' => 'gdpr',
            ]),
            self::$field->create([
                'id' => 'gdpr_radius',
                'label' => __('Checkbox Radius', 'ht-contactform'),
                'type' => 'number',
                'value' => '4',
                'suffix' => 'px',
                'min' => '0',
                'group' => 'gdpr',
            ]),
        ]);
    }

    public function get_terms_conditions_settings(): array {
        return apply_filters('ht_form_styler_terms_conditions_settings', [
            self::$field->create([
                'id' => 'terms_conditions_label_color',
                'label' => __('Text Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#111827',
                'group' => 'terms_conditions',
            ]),
            self::$field->create([
                'id' => 'terms_conditions_label_size',
                'label' => __('Text Font Size', 'ht-contactform'),
                'type' => 'number',
                'value' => '15',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'terms_conditions',
            ]),
            self::$field->create([
                'id' => 'terms_conditions_label_size_sm',
                'label' => __('Text Font Size (Small)', 'ht-contactform'),
                'type' => 'number',
                'value' => '13',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'terms_conditions',
            ]),
            self::$field->create([
                'id' => 'terms_conditions_label_size_lg',
                'label' => __('Text Font Size (Large)', 'ht-contactform'),
                'type' => 'number',
                'value' => '17',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'terms_conditions',
            ]),
            self::$field->create([
                'id' => 'terms_conditions_label_weight',
                'label' => __('Text Font Weight', 'ht-contactform'),
                'type' => 'select',
                'value' => '400',
                'options' => [
                    [
                        'label' => __('100', 'ht-contactform'),
                        'value' => '100',
                    ],
                    [
                        'label' => __('200', 'ht-contactform'),
                        'value' => '200',
                    ],
                    [
                        'label' => __('300', 'ht-contactform'),
                        'value' => '300',
                    ],
                    [
                        'label' => __('400', 'ht-contactform'),
                        'value' => '400',
                    ],
                    [
                        'label' => __('500', 'ht-contactform'),
                        'value' => '500',
                    ],
                    [
                        'label' => __('600', 'ht-contactform'),
                        'value' => '600',
                    ],
                    [
                        'label' => __('700', 'ht-contactform'),
                        'value' => '700',
                    ],
                    [
                        'label' => __('800', 'ht-contactform'),
                        'value' => '800',
                    ],
                    [
                        'label' => __('900', 'ht-contactform'),
                        'value' => '900',
                    ],
                ],
                'group' => 'terms_conditions',
            ]),
            self::$field->create([
                'id' => 'terms_conditions_label_line_height',
                'label' => __('Text Line Height', 'ht-contactform'),
                'type' => 'number',
                'value' => '1.5',
                'suffix' => '',
                'min' => '1',
                'step' => '0.1',
                'group' => 'terms_conditions',
            ]),
            self::$field->create([
                'id' => 'terms_conditions_size',
                'label' => __('Checkbox Size', 'ht-contactform'),
                'type' => 'number',
                'value' => '18',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'terms_conditions',
            ]),
            self::$field->create([
                'id' => 'terms_conditions_size_sm',
                'label' => __('Checkbox Size (Small)', 'ht-contactform'),
                'type' => 'number',
                'value' => '16',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'terms_conditions',
            ]),
            self::$field->create([
                'id' => 'terms_conditions_size_lg',
                'label' => __('Checkbox Size (Large)', 'ht-contactform'),
                'type' => 'number',
                'value' => '22',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'terms_conditions',
            ]),
            self::$field->create([
                'id' => 'terms_conditions_link_color',
                'label' => __('Link Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#2563eb',
                'group' => 'terms_conditions',
            ]),
            self::$field->create([
                'id' => 'terms_conditions_link_hover_color',
                'label' => __('Link Hover Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#2563eb',
                'group' => 'terms_conditions',
            ]),
            self::$field->create([
                'id' => 'terms_conditions_gap',
                'label' => __('Gap Between Checkbox & Text', 'ht-contactform'),
                'type' => 'number',
                'value' => '10',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'terms_conditions',
            ]),
            self::$field->create([
                'id' => 'terms_conditions_border_width',
                'label' => __('Checkbox Border Width', 'ht-contactform'),
                'type' => 'number',
                'value' => '1',
                'suffix' => 'px',
                'min' => '1',
                'group' => 'terms_conditions',
            ]),
            self::$field->create([
                'id' => 'terms_conditions_border_style',
                'label' => __('Checkbox Border Style', 'ht-contactform'),
                'type' => 'select',
                'value' => 'solid',
                'options' => [
                    [
                        'value' => 'solid',
                        'label' => __('Solid', 'ht-contactform'),
                    ],
                    [
                        'value' => 'dashed',
                        'label' => __('Dashed', 'ht-contactform'),
                    ],
                    [
                        'value' => 'dotted',
                        'label' => __('Dotted', 'ht-contactform'),
                    ],
                ],
                'group' => 'terms_conditions',
            ]),
            self::$field->create([
                'id' => 'terms_conditions_border_color',
                'label' => __('Checkbox Border Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#dddddd',
                'group' => 'terms_conditions',
            ]),
            self::$field->create([
                'id' => 'terms_conditions_border_color_active',
                'label' => __('Checkbox Border Color (Active)', 'ht-contactform'),
                'type' => 'color',
                'value' => '#2563eb',
                'group' => 'terms_conditions',
            ]),
            self::$field->create([
                'id' => 'terms_conditions_bg_color',
                'label' => __('Checkbox Background', 'ht-contactform'),
                'type' => 'color',
                'value' => '#2563eb',
                'group' => 'terms_conditions',
            ]),
            self::$field->create([
                'id' => 'terms_conditions_bg_color_active',
                'label' => __('Checkbox Background (Active)', 'ht-contactform'),
                'type' => 'color',
                'value' => '#2563eb',
                'group' => 'terms_conditions',
            ]),
            self::$field->create([
                'id' => 'terms_conditions_check_color',
                'label' => __('Checkbox Check Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#ffffff',
                'group' => 'terms_conditions',
            ]),
            self::$field->create([
                'id' => 'terms_conditions_radius',
                'label' => __('Checkbox Radius', 'ht-contactform'),
                'type' => 'number',
                'value' => '4',
                'suffix' => 'px',
                'min' => '0',
                'group' => 'terms_conditions',
            ]),
        ]);
    }

    public function get_date_time_settings(): array {
        return apply_filters('ht_form_styler_date_time_settings', [
            self::$field->create([
                'id' => 'date_time_selected_color',
                'label' => __('Date/Time Selected Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#ffffff',
                'group' => 'date_time',
            ]),
            self::$field->create([
                'id' => 'date_time_selected_bg_color',
                'label' => __('Date/Time Selected Background', 'ht-contactform'),
                'type' => 'color',
                'value' => '#2563eb',
                'group' => 'date_time',
            ]),
            self::$field->create([
                'id' => 'date_time_selected_border_color',
                'label' => __('Date/Time Selected Border Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#2563eb',
                'group' => 'date_time',
            ]),
        ]);
    }

    public function get_submit_button_settings(): array {
        return apply_filters('ht_form_styler_submit_button_settings', [
            self::$field->create([
                'id' => 'submit_button_color',
                'label' => __('Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#ffffff',
                'group' => 'submit_button',
            ]),
            self::$field->create([
                'id' => 'submit_button_color_hover',
                'label' => __('Color (Hover)', 'ht-contactform'),
                'type' => 'color',
                'value' => '#ffffff',
                'group' => 'submit_button',
            ]),
            self::$field->create([
                'id' => 'submit_button_bg',
                'label' => __('Background', 'ht-contactform'),
                'type' => 'color',
                'value' => '#2563eb',
                'group' => 'submit_button',
            ]),
            self::$field->create([
                'id' => 'submit_button_bg_hover',
                'label' => __('Background (Hover)', 'ht-contactform'),
                'type' => 'color',
                'value' => '#2563eb',
                'group' => 'submit_button',
            ]),
            self::$field->create([
                'id' => 'submit_button_radius',
                'label' => __('Radius', 'ht-contactform'),
                'type' => 'number',
                'value' => '4',
                'suffix' => 'px',
                'min' => '0',
                'group' => 'submit_button',
            ]),
            self::$field->create([
                'id' => 'submit_button_size',
                'label' => __('Font Size', 'ht-contactform'),
                'type' => 'number',
                'value' => '14',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'submit_button',
            ]),
            self::$field->create([
                'id' => 'submit_button_size_sm',
                'label' => __('Font Size (Small)', 'ht-contactform'),
                'type' => 'number',
                'value' => '16',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'submit_button',
            ]),
            self::$field->create([
                'id' => 'submit_button_size_lg',
                'label' => __('Font Size (Large)', 'ht-contactform'),
                'type' => 'number',
                'value' => '18',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'submit_button',
            ]),
            self::$field->create([
                'id' => 'submit_button_weight',
                'label' => __('Font Weight', 'ht-contactform'),
                'type' => 'select',
                'value' => '400',
                'options' => [
                    [
                        'label' => __('100', 'ht-contactform'),
                        'value' => '100',
                    ],
                    [
                        'label' => __('200', 'ht-contactform'),
                        'value' => '200',
                    ],
                    [
                        'label' => __('300', 'ht-contactform'),
                        'value' => '300',
                    ],
                    [
                        'label' => __('400', 'ht-contactform'),
                        'value' => '400',
                    ],
                    [
                        'label' => __('500', 'ht-contactform'),
                        'value' => '500',
                    ],
                    [
                        'label' => __('600', 'ht-contactform'),
                        'value' => '600',
                    ],
                    [
                        'label' => __('700', 'ht-contactform'),
                        'value' => '700',
                    ],
                    [
                        'label' => __('800', 'ht-contactform'),
                        'value' => '800',
                    ],
                    [
                        'label' => __('900', 'ht-contactform'),
                        'value' => '900',
                    ],
                ],
                'group' => 'submit_button',
            ]),
            self::$field->create([
                'id' => 'submit_button_line_height',
                'label' => __('Line Height', 'ht-contactform'),
                'type' => 'number',
                'value' => '1.5',
                'suffix' => '',
                'min' => '1',
                'step' => '0.1',
                'group' => 'submit_button',
            ]),
            self::$field->create([
                'id' => 'submit_button_border_width',
                'label' => __('Border Width', 'ht-contactform'),
                'type' => 'number',
                'value' => '1',
                'suffix' => 'px',
                'min' => '0',
                'group' => 'submit_button',
            ]),
            self::$field->create([
                'id' => 'submit_button_border_style',
                'label' => __('Border Style', 'ht-contactform'),
                'type' => 'select',
                'value' => 'solid',
                'options' => [
                    [
                        'value' => 'solid',
                        'label' => __('Solid', 'ht-contactform'),
                    ],
                    [
                        'value' => 'dashed',
                        'label' => __('Dashed', 'ht-contactform'),
                    ],
                    [
                        'value' => 'dotted',
                        'label' => __('Dotted', 'ht-contactform'),
                    ],
                ],
                'group' => 'submit_button',
            ]),
            self::$field->create([
                'id' => 'submit_button_border_color',
                'label' => __('Border Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#2563eb',
                'group' => 'submit_button',
            ]),
            self::$field->create([
                'id' => 'submit_button_border_color_hover',
                'label' => __('Border Color (Hover)', 'ht-contactform'),
                'type' => 'color',
                'value' => '#2563eb',
                'group' => 'submit_button',
            ]),
        ]);
    }

    public function get_signature_settings(): array {
        return apply_filters('ht_form_styler_signature_settings', [
            self::$field->create([
                'id' => 'signature_border_color',
                'label' => __('Border Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#dddddd',
                'group' => 'signature',
            ]),
            self::$field->create([
                'id' => 'signature_bg_color',
                'label' => __('Background', 'ht-contactform'),
                'type' => 'color',
                'value' => '#ffffff',
                'group' => 'signature',
            ]),
            self::$field->create([
                'id' => 'signature_pen_color',
                'label' => __('Pen Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#000000',
                'group' => 'signature',
            ]),
            self::$field->create([
                'id' => 'signature_clear_btn_color',
                'label' => __('Clear Button Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#ffffff',
                'group' => 'signature',
            ]),
            self::$field->create([
                'id' => 'signature_clear_btn_bg',
                'label' => __('Clear Button Background', 'ht-contactform'),
                'type' => 'color',
                'value' => '#ef4444',
                'group' => 'signature',
            ]),
            self::$field->create([
                'id' => 'signature_radius',
                'label' => __('Radius', 'ht-contactform'),
                'type' => 'number',
                'value' => '4',
                'suffix' => 'px',
                'min' => '0',
                'group' => 'signature',
            ]),
        ]);
    }

    public function get_ratings_settings(): array {
        return apply_filters('ht_form_styler_ratings_settings', [
            self::$field->create([
                'id' => 'ratings_color',
                'label' => __('Star Color (Inactive)', 'ht-contactform'),
                'type' => 'color',
                'value' => '#d1d5db',
                'group' => 'ratings',
            ]),
            self::$field->create([
                'id' => 'ratings_color_active',
                'label' => __('Star Color (Active)', 'ht-contactform'),
                'type' => 'color',
                'value' => '#fbbf24',
                'group' => 'ratings',
            ]),
            self::$field->create([
                'id' => 'ratings_size',
                'label' => __('Star Size', 'ht-contactform'),
                'type' => 'number',
                'value' => '24',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'ratings',
            ]),
            self::$field->create([
                'id' => 'ratings_size_sm',
                'label' => __('Star Size (Small)', 'ht-contactform'),
                'type' => 'number',
                'value' => '20',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'ratings',
            ]),
            self::$field->create([
                'id' => 'ratings_size_lg',
                'label' => __('Star Size (Large)', 'ht-contactform'),
                'type' => 'number',
                'value' => '28',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'ratings',
            ]),
            self::$field->create([
                'id' => 'ratings_gap',
                'label' => __('Gap Between Stars', 'ht-contactform'),
                'type' => 'number',
                'value' => '4',
                'suffix' => 'px',
                'min' => '0',
                'group' => 'ratings',
            ]),
        ]);
    }

    public function get_file_upload_settings(): array {
        return apply_filters('ht_form_styler_file_upload_settings', [
            self::$field->create([
                'id' => 'file_upload_border_color',
                'label' => __('Dropzone Border Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#dddddd',
                'group' => 'file_upload',
            ]),
            self::$field->create([
                'id' => 'file_upload_border_style',
                'label' => __('Dropzone Border Style', 'ht-contactform'),
                'type' => 'select',
                'value' => 'dashed',
                'options' => [
                    [
                        'value' => 'solid',
                        'label' => __('Solid', 'ht-contactform'),
                    ],
                    [
                        'value' => 'dashed',
                        'label' => __('Dashed', 'ht-contactform'),
                    ],
                    [
                        'value' => 'dotted',
                        'label' => __('Dotted', 'ht-contactform'),
                    ],
                ],
                'group' => 'file_upload',
            ]),
            self::$field->create([
                'id' => 'file_upload_bg_color',
                'label' => __('Dropzone Background', 'ht-contactform'),
                'type' => 'color',
                'value' => '#f9fafb',
                'group' => 'file_upload',
            ]),
            self::$field->create([
                'id' => 'file_upload_btn_color',
                'label' => __('Button Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#ffffff',
                'group' => 'file_upload',
            ]),
            self::$field->create([
                'id' => 'file_upload_btn_bg',
                'label' => __('Button Background', 'ht-contactform'),
                'type' => 'color',
                'value' => '#2563eb',
                'group' => 'file_upload',
            ]),
            self::$field->create([
                'id' => 'file_upload_radius',
                'label' => __('Radius', 'ht-contactform'),
                'type' => 'number',
                'value' => '4',
                'suffix' => 'px',
                'min' => '0',
                'group' => 'file_upload',
            ]),
        ]);
    }

    public function get_section_break_settings(): array {
        return apply_filters('ht_form_styler_section_break_settings', [
            self::$field->create([
                'id' => 'section_break_heading_color',
                'label' => __('Heading Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#111827',
                'group' => 'section_break',
            ]),
            self::$field->create([
                'id' => 'section_break_heading_size',
                'label' => __('Heading Font Size', 'ht-contactform'),
                'type' => 'number',
                'value' => '18',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'section_break',
            ]),
            self::$field->create([
                'id' => 'section_break_heading_weight',
                'label' => __('Heading Font Weight', 'ht-contactform'),
                'type' => 'select',
                'value' => '600',
                'options' => [
                    [
                        'label' => __('100', 'ht-contactform'),
                        'value' => '100',
                    ],
                    [
                        'label' => __('200', 'ht-contactform'),
                        'value' => '200',
                    ],
                    [
                        'label' => __('300', 'ht-contactform'),
                        'value' => '300',
                    ],
                    [
                        'label' => __('400', 'ht-contactform'),
                        'value' => '400',
                    ],
                    [
                        'label' => __('500', 'ht-contactform'),
                        'value' => '500',
                    ],
                    [
                        'label' => __('600', 'ht-contactform'),
                        'value' => '600',
                    ],
                    [
                        'label' => __('700', 'ht-contactform'),
                        'value' => '700',
                    ],
                    [
                        'label' => __('800', 'ht-contactform'),
                        'value' => '800',
                    ],
                    [
                        'label' => __('900', 'ht-contactform'),
                        'value' => '900',
                    ],
                ],
                'group' => 'section_break',
            ]),
            self::$field->create([
                'id' => 'section_break_divider_color',
                'label' => __('Divider Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#e5e7eb',
                'group' => 'section_break',
            ]),
            self::$field->create([
                'id' => 'section_break_divider_width',
                'label' => __('Divider Width', 'ht-contactform'),
                'type' => 'number',
                'value' => '1',
                'suffix' => 'px',
                'min' => '0',
                'group' => 'section_break',
            ]),
            self::$field->create([
                'id' => 'section_break_divider_style',
                'label' => __('Divider Style', 'ht-contactform'),
                'type' => 'select',
                'value' => 'solid',
                'options' => [
                    [
                        'value' => 'solid',
                        'label' => __('Solid', 'ht-contactform'),
                    ],
                    [
                        'value' => 'dashed',
                        'label' => __('Dashed', 'ht-contactform'),
                    ],
                    [
                        'value' => 'dotted',
                        'label' => __('Dotted', 'ht-contactform'),
                    ],
                ],
                'group' => 'section_break',
            ]),
        ]);
    }

    public function get_nps_settings(): array {
        return apply_filters('ht_form_styler_nps_settings', [
            self::$field->create([
                'id' => 'nps_color',
                'label' => __('Number Color', 'ht-contactform'),
                'type' => 'color',
                'value' => '#374151',
                'group' => 'nps',
            ]),
            self::$field->create([
                'id' => 'nps_color_active',
                'label' => __('Number Color (Active)', 'ht-contactform'),
                'type' => 'color',
                'value' => '#ffffff',
                'group' => 'nps',
            ]),
            self::$field->create([
                'id' => 'nps_bg_color',
                'label' => __('Background', 'ht-contactform'),
                'type' => 'color',
                'value' => '#f3f4f6',
                'group' => 'nps',
            ]),
            self::$field->create([
                'id' => 'nps_bg_color_active',
                'label' => __('Background (Active)', 'ht-contactform'),
                'type' => 'color',
                'value' => '#2563eb',
                'group' => 'nps',
            ]),
            self::$field->create([
                'id' => 'nps_size',
                'label' => __('Number Size', 'ht-contactform'),
                'type' => 'number',
                'value' => '14',
                'suffix' => 'px',
                'min' => '10',
                'group' => 'nps',
            ]),
            self::$field->create([
                'id' => 'nps_radius',
                'label' => __('Radius', 'ht-contactform'),
                'type' => 'number',
                'value' => '4',
                'suffix' => 'px',
                'min' => '0',
                'group' => 'nps',
            ]),
        ]);
    }
}