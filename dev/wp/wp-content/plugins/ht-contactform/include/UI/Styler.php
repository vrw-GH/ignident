<?php
/**
 * Frontend Form Fields Handler
 */

namespace HTContactForm\UI;

// If this file is accessed directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fields class
 * Handles field rendering functionality for displaying forms on the frontend
 */
class Styler {
    /**
     * Singleton instance
     *
     * @var object
     */
    private static $instance = null;

    /**
     * Get instance of this class
     *
     * @return object
     */
    public static function get_instance($form_id, $styler) {
        if (null === self::$instance) {
            self::$instance = new self($form_id, $styler);
        }
        return self::$instance;
    }

    protected $form_id;
    protected $styler;

    /**
     * Constructor
     */
    public function __construct($form_id, $styler) {
        $this->form_id = $form_id;
        $this->styler = $styler;
    }

    public function get_style() {

        $style = "#{$this->form_id} .ht-form-elem {";
        $style .= $this->get_label_style();
        $style .= $this->get_help_text_style();
        $style .= $this->get_help_tooltip_style();
        $style .= $this->get_field_style();
        $style .= $this->get_checkbox_style();
        $style .= $this->get_radio_style();
        $style .= $this->get_range_style();
        $style .= $this->get_select_style();
        $style .= $this->get_gdpr_style();
        $style .= $this->get_terms_conditions_style();
        $style .= $this->get_date_time_style();
        $style .= $this->get_submit_button_style();
        $style .= $this->get_signature_style();
        $style .= $this->get_ratings_style();
        $style .= $this->get_file_upload_style();
        $style .= $this->get_section_break_style();
        $style .= $this->get_nps_style();
        $style .= "}";
        return $style;
    }

    /**
     * Check if value is set and not empty
     *
     * @param mixed $value Value to check
     * @return bool
     */
    public function check_value($value) {
        return isset($value) && $value !== '';
    }

    /**
     * Get label style
     *
     * @return string
     */
    public function get_label_style() {
        $style = '';
        if(isset($this->styler['label_color']) && $this->styler['label_color'] !== '') {
            $style .= "--ht-label-color: {$this->styler['label_color']};";
        }
        if(isset($this->styler['label_size']) && $this->styler['label_size'] !== '') {
            $style .= "--ht-label-fz: {$this->styler['label_size']}px;";
        }
        if(isset($this->styler['label_size_sm']) && $this->styler['label_size_sm'] !== '') {
            $style .= "--ht-label-fz-sm: {$this->styler['label_size_sm']}px;";
        }
        if(isset($this->styler['label_size_lg']) && $this->styler['label_size_lg'] !== '') {
            $style .= "--ht-label-fz-lg: {$this->styler['label_size_lg']}px;";
        }
        if(isset($this->styler['label_weight']) && $this->styler['label_weight'] !== '') {
            $style .= "--ht-label-fw: {$this->styler['label_weight']};";
        }
        if(isset($this->styler['label_line_height']) && $this->styler['label_line_height'] !== '') {
            $style .= "--ht-label-lh: {$this->styler['label_line_height']};";
        }
        return $style;
    }

    /**
     * Get help text style
     *
     * @return string
     */
    public function get_help_text_style() {
        $style = '';
        if(isset($this->styler['help_text_color']) && $this->styler['help_text_color'] !== '') {
            $style .= "--ht-field-help-color: {$this->styler['help_text_color']};";
        }
        if(isset($this->styler['help_text_size']) && $this->styler['help_text_size'] !== '') {
            $style .= "--ht-field-help-fz: {$this->styler['help_text_size']}px;";
        }
        if(isset($this->styler['help_text_weight']) && $this->styler['help_text_weight'] !== '') {
            $style .= "--ht-field-help-fw: {$this->styler['help_text_weight']};";
        }
        if(isset($this->styler['help_text_line_height']) && $this->styler['help_text_line_height'] !== '') {
            $style .= "--ht-field-help-lh: {$this->styler['help_text_line_height']};";
        }
        return $style;
    }

    /**
     * Get help tooltip style
     *
     * @return string
     */
    public function get_help_tooltip_style() {
        $style = '';
        if(isset($this->styler['help_tooltip_color']) && $this->styler['help_tooltip_color'] !== '') {
            $style .= "--ht-field-help-tooltip-color: {$this->styler['help_tooltip_color']};";
        }
        if(isset($this->styler['help_tooltip_bg_color']) && $this->styler['help_tooltip_bg_color'] !== '') {
            $style .= "--ht-field-help-tooltip-bg-color: {$this->styler['help_tooltip_bg_color']};";
        }
        if(isset($this->styler['help_tooltip_radius']) && $this->styler['help_tooltip_radius'] !== '') {
            $style .= "--ht-field-help-tooltip-radius: {$this->styler['help_tooltip_radius']}px;";
        }
        if(isset($this->styler['help_tooltip_icon_size']) && $this->styler['help_tooltip_icon_size'] !== '') {
            $style .= "--ht-field-help-tooltip-icon-size: {$this->styler['help_tooltip_icon_size']}px;";
        }
        if(isset($this->styler['help_tooltip_icon_color']) && $this->styler['help_tooltip_icon_color'] !== '') {
            $style .= "--ht-field-help-tooltip-icon-color: {$this->styler['help_tooltip_icon_color']};";
        }
        if(isset($this->styler['help_tooltip_size']) && $this->styler['help_tooltip_size'] !== '') {
            $style .= "--ht-field-help-tooltip-fz: {$this->styler['help_tooltip_size']}px;";
        }
        if(isset($this->styler['help_tooltip_weight']) && $this->styler['help_tooltip_weight'] !== '') {
            $style .= "--ht-field-help-tooltip-fw: {$this->styler['help_tooltip_weight']};";
        }
        if(isset($this->styler['help_tooltip_line_height']) && $this->styler['help_tooltip_line_height'] !== '') {
            $style .= "--ht-field-help-tooltip-lh: {$this->styler['help_tooltip_line_height']};";
        }
        return $style;
    }

    /**
     * Get field style
     *
     * @return string
     */
    public function get_field_style() {
        $style = '';
        if(isset($this->styler['field_color']) && $this->styler['field_color'] !== '') {
            $style .= "--ht-field-color: {$this->styler['field_color']};";
        }
        if(isset($this->styler['field_bg_color']) && $this->styler['field_bg_color'] !== '') {
            $style .= "--ht-field-bg-color: {$this->styler['field_bg_color']};";
        }
        if(isset($this->styler['field_radius']) && $this->styler['field_radius'] !== '') {
            $style .= "--ht-field-radius: {$this->styler['field_radius']}px;";
        }
        if(isset($this->styler['field_size']) && $this->styler['field_size'] !== '') {
            $style .= "--ht-field-fz: {$this->styler['field_size']}px;";
        }
        if(isset($this->styler['field_size_sm']) && $this->styler['field_size_sm'] !== '') {
            $style .= "--ht-field-fz-sm: {$this->styler['field_size_sm']}px;";
        }
        if(isset($this->styler['field_size_lg']) && $this->styler['field_size_lg'] !== '') {
            $style .= "--ht-field-fz-lg: {$this->styler['field_size_lg']}px;";
        }
        if(isset($this->styler['field_weight']) && $this->styler['field_weight'] !== '') {
            $style .= "--ht-field-fw: {$this->styler['field_weight']};";
        }
        if(isset($this->styler['field_line_height']) && $this->styler['field_line_height'] !== '') {
            $style .= "--ht-field-lh: {$this->styler['field_line_height']};";
        }
        if(isset($this->styler['field_border_style']) && $this->styler['field_border_style'] !== '') {
            $style .= "--ht-field-border-style: {$this->styler['field_border_style']};";
        }
        if(isset($this->styler['field_border_width']) && $this->styler['field_border_width'] !== '') {
            $style .= "--ht-field-border-width: {$this->styler['field_border_width']}px;";
        }
        if(isset($this->styler['field_border_color']) && $this->styler['field_border_color'] !== '') {
            $style .= "--ht-field-border-color: {$this->styler['field_border_color']};";
        }
        if(isset($this->styler['field_border_color_focus']) && $this->styler['field_border_color_focus'] !== '') {
            $style .= "--ht-field-border-color-focus: {$this->styler['field_border_color_focus']};";
        }
        if(isset($this->styler['field_error_color']) && $this->styler['field_error_color'] !== '') {
            $style .= "--ht-field-error-color: {$this->styler['field_error_color']};";
        }
        if(isset($this->styler['field_error_border_color']) && $this->styler['field_error_border_color'] !== '') {
            $style .= "--ht-field-error-border-color: {$this->styler['field_error_border_color']};";
        }
        if(isset($this->styler['field_pf_sf_color']) && $this->styler['field_pf_sf_color'] !== '') {
            $style .= "--ht-field-prefix-suffix-color: {$this->styler['field_pf_sf_color']};";
        }
        if(isset($this->styler['field_pf_sf_bg_color']) && $this->styler['field_pf_sf_bg_color'] !== '') {
            $style .= "--ht-field-prefix-suffix-bg: {$this->styler['field_pf_sf_bg_color']};";
        }
        return $style;
    }

    /**
     * Get checkbox style
     *
     * @return string
     */
    public function get_checkbox_style() {
        $style = '';
        if(isset($this->styler['checkbox_color']) && $this->styler['checkbox_color'] !== '') {
            $style .= "--ht-checkbox-label-color: {$this->styler['checkbox_color']};";
        }
        if(isset($this->styler['checkbox_font_size']) && $this->styler['checkbox_font_size'] !== '') {
            $style .= "--ht-checkbox-label-fz: {$this->styler['checkbox_font_size']}px;";
        }
        if(isset($this->styler['checkbox_font_size_sm']) && $this->styler['checkbox_font_size_sm'] !== '') {
            $style .= "--ht-checkbox-label-fz-sm: {$this->styler['checkbox_font_size_sm']}px;";
        }
        if(isset($this->styler['checkbox_font_size_lg']) && $this->styler['checkbox_font_size_lg'] !== '') {
            $style .= "--ht-checkbox-label-fz-lg: {$this->styler['checkbox_font_size_lg']}px;";
        }
        if(isset($this->styler['checkbox_font_weight']) && $this->styler['checkbox_font_weight'] !== '') {
            $style .= "--ht-checkbox-label-fw: {$this->styler['checkbox_font_weight']};";
        }
        if(isset($this->styler['checkbox_line_height']) && $this->styler['checkbox_line_height'] !== '') {
            $style .= "--ht-checkbox-label-lh: {$this->styler['checkbox_line_height']};";
        }
        if(isset($this->styler['checkbox_size']) && $this->styler['checkbox_size'] !== '') {
            $style .= "--ht-checkbox-size: {$this->styler['checkbox_size']}px;";
        }
        if(isset($this->styler['checkbox_size_sm']) && $this->styler['checkbox_size_sm'] !== '') {
            $style .= "--ht-checkbox-size-sm: {$this->styler['checkbox_size_sm']}px;";
        }
        if(isset($this->styler['checkbox_size_lg']) && $this->styler['checkbox_size_lg'] !== '') {
            $style .= "--ht-checkbox-size-lg: {$this->styler['checkbox_size_lg']}px;";
        }
        if(isset($this->styler['checkbox_radius']) && $this->styler['checkbox_radius'] !== '') {
            $style .= "--ht-checkbox-radius: {$this->styler['checkbox_radius']}px;";
        }
        if(isset($this->styler['checkbox_gap']) && $this->styler['checkbox_gap'] !== '') {
            $style .= "--ht-checkbox-gap: {$this->styler['checkbox_gap']}px;";
        }
        if(isset($this->styler['checkbox_border_style']) && $this->styler['checkbox_border_style'] !== '') {
            $style .= "--ht-checkbox-border-style: {$this->styler['checkbox_border_style']};";
        }
        if(isset($this->styler['checkbox_border_width']) && $this->styler['checkbox_border_width'] !== '') {
            $style .= "--ht-checkbox-border-width: {$this->styler['checkbox_border_width']}px;";
        }
        if(isset($this->styler['checkbox_border_color']) && $this->styler['checkbox_border_color'] !== '') {
            $style .= "--ht-checkbox-border-color: {$this->styler['checkbox_border_color']};";
        }
        if(isset($this->styler['checkbox_border_color_active']) && $this->styler['checkbox_border_color_active'] !== '') {
            $style .= "--ht-checkbox-border-color-active: {$this->styler['checkbox_border_color_active']};";
        }
        if(isset($this->styler['checkbox_bg_color']) && $this->styler['checkbox_bg_color'] !== '') {
            $style .= "--ht-checkbox-bg-color: {$this->styler['checkbox_bg_color']};";
        }
        if(isset($this->styler['checkbox_bg_color_active']) && $this->styler['checkbox_bg_color_active'] !== '') {
            $style .= "--ht-checkbox-bg-color-active: {$this->styler['checkbox_bg_color_active']};";
        }
        if(isset($this->styler['checkbox_check_color']) && $this->styler['checkbox_check_color'] !== '') {
            $style .= "--ht-checkbox-check-color: {$this->styler['checkbox_check_color']};";
        }
        return $style;
    }

    /**
     * Get radio style
     *
     * @return string
     */
    public function get_radio_style() {
        $style = '';
        if(isset($this->styler['radio_color']) && $this->styler['radio_color'] !== '') {
            $style .= "--ht-radio-label-color: {$this->styler['radio_color']};";
        }
        if(isset($this->styler['radio_font_size']) && $this->styler['radio_font_size'] !== '') {
            $style .= "--ht-radio-label-fz: {$this->styler['radio_font_size']}px;";
        }
        if(isset($this->styler['radio_font_size_sm']) && $this->styler['radio_font_size_sm'] !== '') {
            $style .= "--ht-radio-label-fz-sm: {$this->styler['radio_font_size_sm']}px;";
        }
        if(isset($this->styler['radio_font_size_lg']) && $this->styler['radio_font_size_lg'] !== '') {
            $style .= "--ht-radio-label-fz-lg: {$this->styler['radio_font_size_lg']}px;";
        }
        if(isset($this->styler['radio_font_weight']) && $this->styler['radio_font_weight'] !== '') {
            $style .= "--ht-radio-label-fw: {$this->styler['radio_font_weight']};";
        }
        if(isset($this->styler['radio_line_height']) && $this->styler['radio_line_height'] !== '') {
            $style .= "--ht-radio-label-lh: {$this->styler['radio_line_height']};";
        }
        if(isset($this->styler['radio_size']) && $this->styler['radio_size'] !== '') {
            $style .= "--ht-radio-size: {$this->styler['radio_size']}px;";
        }
        if(isset($this->styler['radio_size_sm']) && $this->styler['radio_size_sm'] !== '') {
            $style .= "--ht-radio-size-sm: {$this->styler['radio_size_sm']}px;";
        }
        if(isset($this->styler['radio_size_lg']) && $this->styler['radio_size_lg'] !== '') {
            $style .= "--ht-radio-size-lg: {$this->styler['radio_size_lg']}px;";
        }
        if(isset($this->styler['radio_radius']) && $this->styler['radio_radius'] !== '') {
            $style .= "--ht-radio-radius: {$this->styler['radio_radius']}px;";
        }
        if(isset($this->styler['radio_gap']) && $this->styler['radio_gap'] !== '') {
            $style .= "--ht-radio-gap: {$this->styler['radio_gap']}px;";
        }
        if(isset($this->styler['radio_border_style']) && $this->styler['radio_border_style'] !== '') {
            $style .= "--ht-radio-border-style: {$this->styler['radio_border_style']};";
        }
        if(isset($this->styler['radio_border_width']) && $this->styler['radio_border_width'] !== '') {
            $style .= "--ht-radio-border-width: {$this->styler['radio_border_width']}px;";
        }
        if(isset($this->styler['radio_border_color']) && $this->styler['radio_border_color'] !== '') {
            $style .= "--ht-radio-border-color: {$this->styler['radio_border_color']};";
        }
        if(isset($this->styler['radio_border_color_active']) && $this->styler['radio_border_color_active'] !== '') {
            $style .= "--ht-radio-border-color-active: {$this->styler['radio_border_color_active']};";
        }
        if(isset($this->styler['radio_bg_color']) && $this->styler['radio_bg_color'] !== '') {
            $style .= "--ht-radio-bg-color: {$this->styler['radio_bg_color']};";
        }
        if(isset($this->styler['radio_bg_color_active']) && $this->styler['radio_bg_color_active'] !== '') {
            $style .= "--ht-radio-bg-color-active: {$this->styler['radio_bg_color_active']};";
        }
        if(isset($this->styler['radio_check_color']) && $this->styler['radio_check_color'] !== '') {
            $style .= "--ht-radio-check-color: {$this->styler['radio_check_color']};";
        }
        return $style;
    }

    /**
     * Get range style
     *
     * @return string
     */
    public function get_range_style() {
        $style = '';
        if(isset($this->styler['range_bg_color']) && $this->styler['range_bg_color'] !== '') {
            $style .= "--ht-range-track-bg: {$this->styler['range_bg_color']};";
        }
        if(isset($this->styler['range_bg_color_active']) && $this->styler['range_bg_color_active'] !== '') {
            $style .= "--ht-range-track-bg-active: {$this->styler['range_bg_color_active']};";
        }
        if(isset($this->styler['range_thumb_bg']) && $this->styler['range_thumb_bg'] !== '') {
            $style .= "--ht-range-thumb-bg: {$this->styler['range_thumb_bg']};";
        }
        if(isset($this->styler['range_thumb_radius']) && $this->styler['range_thumb_radius'] !== '') {
            $style .= "--ht-range-thumb-radius: {$this->styler['range_thumb_radius']}px;";
        }
        if(isset($this->styler['range_value_color']) && $this->styler['range_value_color'] !== '') {
            $style .= "--ht-range-value-color: {$this->styler['range_value_color']};";
        }
        if(isset($this->styler['range_value_font_size']) && $this->styler['range_value_font_size'] !== '') {
            $style .= "--ht-range-value-fz: {$this->styler['range_value_font_size']}px;";
        }
        if(isset($this->styler['range_value_font_size_sm']) && $this->styler['range_value_font_size_sm'] !== '') {
            $style .= "--ht-range-value-fz-sm: {$this->styler['range_value_font_size_sm']}px;";
        }
        if(isset($this->styler['range_value_font_size_lg']) && $this->styler['range_value_font_size_lg'] !== '') {
            $style .= "--ht-range-value-fz-lg: {$this->styler['range_value_font_size_lg']}px;";
        }
        if(isset($this->styler['range_value_font_weight']) && $this->styler['range_value_font_weight'] !== '') {
            $style .= "--ht-range-value-fw: {$this->styler['range_value_font_weight']};";
        }
        if(isset($this->styler['range_value_line_height']) && $this->styler['range_value_line_height'] !== '') {
            $style .= "--ht-range-value-lh: {$this->styler['range_value_line_height']};";
        }
        return $style;
    }

    /**
     * Get select style
     *
     * @return string
     */
    public function get_select_style() {
        $style = '';
        if(isset($this->styler['select_dp_selected_color']) && $this->styler['select_dp_selected_color'] !== '') {
            $style .= "--ht-select-dp-selected-color: {$this->styler['select_dp_selected_color']};";
        }
        if(isset($this->styler['select_dp_selected_bg']) && $this->styler['select_dp_selected_bg'] !== '') {
            $style .= "--ht-select-dp-selected-bg-color: {$this->styler['select_dp_selected_bg']};";
        }
        if(isset($this->styler['select_dp_highlighted_bg']) && $this->styler['select_dp_highlighted_bg'] !== '') {
            $style .= "--ht-select-dp-highlighted-bg-color: {$this->styler['select_dp_highlighted_bg']};";
        }
        if(isset($this->styler['select_dp_highlighted_color']) && $this->styler['select_dp_highlighted_color'] !== '') {
            $style .= "--ht-select-dp-highlighted-color: {$this->styler['select_dp_highlighted_color']};";
        }
        if(isset($this->styler['select_pill_color']) && $this->styler['select_pill_color'] !== '') {
            $style .= "--ht-select-selected-color: {$this->styler['select_pill_color']};";
        }
        if(isset($this->styler['select_pill_bg']) && $this->styler['select_pill_bg'] !== '') {
            $style .= "--ht-select-selected-bg-color: {$this->styler['select_pill_bg']};";
        }
        return $style;
    }

    /**
     * Get GDPR style
     *
     * @return string
     */
    public function get_gdpr_style() {
        $style = '';
        if(isset($this->styler['gdpr_label_color']) && $this->styler['gdpr_label_color'] !== '') {
            $style .= "--ht-gdpr-label-color: {$this->styler['gdpr_label_color']};";
        }
        if(isset($this->styler['gdpr_label_size']) && $this->styler['gdpr_label_size'] !== '') {
            $style .= "--ht-gdpr-label-fz: {$this->styler['gdpr_label_size']}px;";
        }
        if(isset($this->styler['gdpr_label_size_sm']) && $this->styler['gdpr_label_size_sm'] !== '') {
            $style .= "--ht-gdpr-label-fz-sm: {$this->styler['gdpr_label_size_sm']}px;";
        }
        if(isset($this->styler['gdpr_label_size_lg']) && $this->styler['gdpr_label_size_lg'] !== '') {
            $style .= "--ht-gdpr-label-fz-lg: {$this->styler['gdpr_label_size_lg']}px;";
        }
        if(isset($this->styler['gdpr_label_weight']) && $this->styler['gdpr_label_weight'] !== '') {
            $style .= "--ht-gdpr-label-fw: {$this->styler['gdpr_label_weight']};";
        }
        if(isset($this->styler['gdpr_label_line_height']) && $this->styler['gdpr_label_line_height'] !== '') {
            $style .= "--ht-gdpr-label-lh: {$this->styler['gdpr_label_line_height']};";
        }
        if(isset($this->styler['gdpr_size']) && $this->styler['gdpr_size'] !== '') {
            $style .= "--ht-gdpr-size: {$this->styler['gdpr_size']}px;";
        }
        if(isset($this->styler['gdpr_size_sm']) && $this->styler['gdpr_size_sm'] !== '') {
            $style .= "--ht-gdpr-size-sm: {$this->styler['gdpr_size_sm']}px;";
        }
        if(isset($this->styler['gdpr_size_lg']) && $this->styler['gdpr_size_lg'] !== '') {
            $style .= "--ht-gdpr-size-lg: {$this->styler['gdpr_size_lg']}px;";
        }
        if(isset($this->styler['gdpr_gap']) && $this->styler['gdpr_gap'] !== '') {
            $style .= "--ht-gdpr-gap: {$this->styler['gdpr_gap']}px;";
        }
        if(isset($this->styler['gdpr_border_width']) && $this->styler['gdpr_border_width'] !== '') {
            $style .= "--ht-gdpr-border-width: {$this->styler['gdpr_border_width']}px;";
        }
        if(isset($this->styler['gdpr_border_style']) && $this->styler['gdpr_border_style'] !== '') {
            $style .= "--ht-gdpr-border-style: {$this->styler['gdpr_border_style']};";
        }
        if(isset($this->styler['gdpr_border_color']) && $this->styler['gdpr_border_color'] !== '') {
            $style .= "--ht-gdpr-border-color: {$this->styler['gdpr_border_color']};";
        }
        if(isset($this->styler['gdpr_border_color_active']) && $this->styler['gdpr_border_color_active'] !== '') {
            $style .= "--ht-gdpr-border-color-active: {$this->styler['gdpr_border_color_active']};";
        }
        if(isset($this->styler['gdpr_bg_color']) && $this->styler['gdpr_bg_color'] !== '') {
            $style .= "--ht-gdpr-bg-color: {$this->styler['gdpr_bg_color']};";
        }
        if(isset($this->styler['gdpr_bg_color_active']) && $this->styler['gdpr_bg_color_active'] !== '') {
            $style .= "--ht-gdpr-bg-color-active: {$this->styler['gdpr_bg_color_active']};";
        }
        if(isset($this->styler['gdpr_check_color']) && $this->styler['gdpr_check_color'] !== '') {
            $style .= "--ht-gdpr-check-color: {$this->styler['gdpr_check_color']};";
        }
        if(isset($this->styler['gdpr_radius']) && $this->styler['gdpr_radius'] !== '') {
            $style .= "--ht-gdpr-radius: {$this->styler['gdpr_radius']}px;";
        }
        return $style;
    }

    /**
     * Get Terms & Conditions style
     *
     * @return string
     */
    public function get_terms_conditions_style() {
        $style = '';
        if(isset($this->styler['terms_conditions_label_color']) && $this->styler['terms_conditions_label_color'] !== '') {
            $style .= "--ht-terms-conditions-label-color: {$this->styler['terms_conditions_label_color']};";
        }
        if(isset($this->styler['terms_conditions_label_size']) && $this->styler['terms_conditions_label_size'] !== '') {
            $style .= "--ht-terms-conditions-label-fz: {$this->styler['terms_conditions_label_size']}px;";
        }
        if(isset($this->styler['terms_conditions_label_size_sm']) && $this->styler['terms_conditions_label_size_sm'] !== '') {
            $style .= "--ht-terms-conditions-label-fz-sm: {$this->styler['terms_conditions_label_size_sm']}px;";
        }
        if(isset($this->styler['terms_conditions_label_size_lg']) && $this->styler['terms_conditions_label_size_lg'] !== '') {
            $style .= "--ht-terms-conditions-label-fz-lg: {$this->styler['terms_conditions_label_size_lg']}px;";
        }
        if(isset($this->styler['terms_conditions_label_weight']) && $this->styler['terms_conditions_label_weight'] !== '') {
            $style .= "--ht-terms-conditions-label-fw: {$this->styler['terms_conditions_label_weight']};";
        }
        if(isset($this->styler['terms_conditions_label_line_height']) && $this->styler['terms_conditions_label_line_height'] !== '') {
            $style .= "--ht-terms-conditions-label-lh: {$this->styler['terms_conditions_label_line_height']};";
        }
        if(isset($this->styler['terms_conditions_size']) && $this->styler['terms_conditions_size'] !== '') {
            $style .= "--ht-terms-conditions-size: {$this->styler['terms_conditions_size']}px;";
        }
        if(isset($this->styler['terms_conditions_size_sm']) && $this->styler['terms_conditions_size_sm'] !== '') {
            $style .= "--ht-terms-conditions-size-sm: {$this->styler['terms_conditions_size_sm']}px;";
        }
        if(isset($this->styler['terms_conditions_size_lg']) && $this->styler['terms_conditions_size_lg'] !== '') {
            $style .= "--ht-terms-conditions-size-lg: {$this->styler['terms_conditions_size_lg']}px;";
        }
        if(isset($this->styler['terms_conditions_link_color']) && $this->styler['terms_conditions_link_color'] !== '') {
            $style .= "--ht-terms-conditions-link-color: {$this->styler['terms_conditions_link_color']};";
        }
        if(isset($this->styler['terms_conditions_link_hover_color']) && $this->styler['terms_conditions_link_hover_color'] !== '') {
            $style .= "--ht-terms-conditions-link-hover-color: {$this->styler['terms_conditions_link_hover_color']};";
        }
        if(isset($this->styler['terms_conditions_gap']) && $this->styler['terms_conditions_gap'] !== '') {
            $style .= "--ht-terms-conditions-gap: {$this->styler['terms_conditions_gap']}px;";
        }
        if(isset($this->styler['terms_conditions_border_width']) && $this->styler['terms_conditions_border_width'] !== '') {
            $style .= "--ht-terms-conditions-border-width: {$this->styler['terms_conditions_border_width']}px;";
        }
        if(isset($this->styler['terms_conditions_border_style']) && $this->styler['terms_conditions_border_style'] !== '') {
            $style .= "--ht-terms-conditions-border-style: {$this->styler['terms_conditions_border_style']};";
        }
        if(isset($this->styler['terms_conditions_border_color']) && $this->styler['terms_conditions_border_color'] !== '') {
            $style .= "--ht-terms-conditions-border-color: {$this->styler['terms_conditions_border_color']};";
        }
        if(isset($this->styler['terms_conditions_border_color_active']) && $this->styler['terms_conditions_border_color_active'] !== '') {
            $style .= "--ht-terms-conditions-border-color-active: {$this->styler['terms_conditions_border_color_active']};";
        }
        if(isset($this->styler['terms_conditions_bg_color']) && $this->styler['terms_conditions_bg_color'] !== '') {
            $style .= "--ht-terms-conditions-bg-color: {$this->styler['terms_conditions_bg_color']};";
        }
        if(isset($this->styler['terms_conditions_bg_color_active']) && $this->styler['terms_conditions_bg_color_active'] !== '') {
            $style .= "--ht-terms-conditions-bg-color-active: {$this->styler['terms_conditions_bg_color_active']};";
        }
        if(isset($this->styler['terms_conditions_check_color']) && $this->styler['terms_conditions_check_color'] !== '') {
            $style .= "--ht-terms-conditions-check-color: {$this->styler['terms_conditions_check_color']};";
        }
        if(isset($this->styler['terms_conditions_radius']) && $this->styler['terms_conditions_radius'] !== '') {
            $style .= "--ht-terms-conditions-radius: {$this->styler['terms_conditions_radius']}px;";
        }
        return $style;
    }

    /**
     * Get date time style
     *
     * @return string
     */
    public function get_date_time_style() {
        $style = '';
        if(isset($this->styler['date_time_selected_color']) && $this->styler['date_time_selected_color'] !== '') {
            $style .= "--ht-date-time-selected-color: {$this->styler['date_time_selected_color']};";
        }
        if(isset($this->styler['date_time_selected_bg_color']) && $this->styler['date_time_selected_bg_color'] !== '') {
            $style .= "--ht-date-time-selected-bg-color: {$this->styler['date_time_selected_bg_color']};";
        }
        if(isset($this->styler['date_time_selected_border_color']) && $this->styler['date_time_selected_border_color'] !== '') {
            $style .= "--ht-date-time-selected-border-color: {$this->styler['date_time_selected_border_color']};";
        }
        return $style;
    }

    /**
     * Get submit button style
     *
     * @return string
     */
    public function get_submit_button_style() {
        $style = '';
        if(isset($this->styler['submit_button_color']) && $this->styler['submit_button_color'] !== '') {
            $style .= "--ht-submit-button-color: {$this->styler['submit_button_color']};";
        }
        if(isset($this->styler['submit_button_color_hover']) && $this->styler['submit_button_color_hover'] !== '') {
            $style .= "--ht-submit-button-color-hover: {$this->styler['submit_button_color_hover']};";
        }
        if(isset($this->styler['submit_button_bg']) && $this->styler['submit_button_bg'] !== '') {
            $style .= "--ht-submit-button-bg: {$this->styler['submit_button_bg']};";
        }
        if(isset($this->styler['submit_button_bg_hover']) && $this->styler['submit_button_bg_hover'] !== '') {
            $style .= "--ht-submit-button-hover-bg: {$this->styler['submit_button_bg_hover']};";
        }
        if(isset($this->styler['submit_button_radius']) && $this->styler['submit_button_radius'] !== '') {
            $style .= "--ht-submit-button-radius: {$this->styler['submit_button_radius']}px;";
        }
        if(isset($this->styler['submit_button_border_width']) && $this->styler['submit_button_border_width'] !== '') {
            $style .= "--ht-submit-button-border-width: {$this->styler['submit_button_border_width']}px;";
        }
        if(isset($this->styler['submit_button_border_style']) && $this->styler['submit_button_border_style'] !== '') {
            $style .= "--ht-submit-button-border-style: {$this->styler['submit_button_border_style']};";
        }
        if(isset($this->styler['submit_button_border_color']) && $this->styler['submit_button_border_color'] !== '') {
            $style .= "--ht-submit-button-border-color: {$this->styler['submit_button_border_color']};";
        }
        if(isset($this->styler['submit_button_border_color_hover']) && $this->styler['submit_button_border_color_hover'] !== '') {
            $style .= "--ht-submit-button-border-color-hover: {$this->styler['submit_button_border_color_hover']};";
        }
        if(isset($this->styler['submit_button_size']) && $this->styler['submit_button_size'] !== '') {
            $style .= "--ht-submit-button-fz: {$this->styler['submit_button_size']}px;";
        }
        if(isset($this->styler['submit_button_size_sm']) && $this->styler['submit_button_size_sm'] !== '') {
            $style .= "--ht-submit-button-fz-sm: {$this->styler['submit_button_size_sm']}px;";
        }
        if(isset($this->styler['submit_button_size_lg']) && $this->styler['submit_button_size_lg'] !== '') {
            $style .= "--ht-submit-button-fz-lg: {$this->styler['submit_button_size_lg']}px;";
        }
        if(isset($this->styler['submit_button_weight']) && $this->styler['submit_button_weight'] !== '') {
            $style .= "--ht-submit-button-fw: {$this->styler['submit_button_weight']};";
        }
        if(isset($this->styler['submit_button_line_height']) && $this->styler['submit_button_line_height'] !== '') {
            $style .= "--ht-submit-button-lh: {$this->styler['submit_button_line_height']};";
        }
        return $style;
    }

    /**
     * Get signature style
     *
     * @return string
     */
    public function get_signature_style() {
        $style = '';
        if(isset($this->styler['signature_border_color']) && $this->styler['signature_border_color'] !== '') {
            $style .= "--ht-signature-border-color: {$this->styler['signature_border_color']};";
        }
        if(isset($this->styler['signature_bg_color']) && $this->styler['signature_bg_color'] !== '') {
            $style .= "--ht-signature-bg-color: {$this->styler['signature_bg_color']};";
        }
        if(isset($this->styler['signature_pen_color']) && $this->styler['signature_pen_color'] !== '') {
            $style .= "--ht-signature-pen-color: {$this->styler['signature_pen_color']};";
        }
        if(isset($this->styler['signature_clear_btn_color']) && $this->styler['signature_clear_btn_color'] !== '') {
            $style .= "--ht-signature-clear-btn-color: {$this->styler['signature_clear_btn_color']};";
        }
        if(isset($this->styler['signature_clear_btn_bg']) && $this->styler['signature_clear_btn_bg'] !== '') {
            $style .= "--ht-signature-clear-btn-bg: {$this->styler['signature_clear_btn_bg']};";
        }
        if(isset($this->styler['signature_radius']) && $this->styler['signature_radius'] !== '') {
            $style .= "--ht-signature-radius: {$this->styler['signature_radius']}px;";
        }
        return $style;
    }

    /**
     * Get ratings style
     *
     * @return string
     */
    public function get_ratings_style() {
        $style = '';
        if(isset($this->styler['ratings_color']) && $this->styler['ratings_color'] !== '') {
            $style .= "--ht-ratings-color: {$this->styler['ratings_color']};";
        }
        if(isset($this->styler['ratings_color_active']) && $this->styler['ratings_color_active'] !== '') {
            $style .= "--ht-ratings-color-active: {$this->styler['ratings_color_active']};";
        }
        if(isset($this->styler['ratings_size']) && $this->styler['ratings_size'] !== '') {
            $style .= "--ht-ratings-size: {$this->styler['ratings_size']}px;";
        }
        if(isset($this->styler['ratings_size_sm']) && $this->styler['ratings_size_sm'] !== '') {
            $style .= "--ht-ratings-size-sm: {$this->styler['ratings_size_sm']}px;";
        }
        if(isset($this->styler['ratings_size_lg']) && $this->styler['ratings_size_lg'] !== '') {
            $style .= "--ht-ratings-size-lg: {$this->styler['ratings_size_lg']}px;";
        }
        if(isset($this->styler['ratings_gap']) && $this->styler['ratings_gap'] !== '') {
            $style .= "--ht-ratings-gap: {$this->styler['ratings_gap']}px;";
        }
        return $style;
    }

    /**
     * Get file upload style
     *
     * @return string
     */
    public function get_file_upload_style() {
        $style = '';
        if(isset($this->styler['file_upload_border_color']) && $this->styler['file_upload_border_color'] !== '') {
            $style .= "--ht-file-upload-border-color: {$this->styler['file_upload_border_color']};";
        }
        if(isset($this->styler['file_upload_border_style']) && $this->styler['file_upload_border_style'] !== '') {
            $style .= "--ht-file-upload-border-style: {$this->styler['file_upload_border_style']};";
        }
        if(isset($this->styler['file_upload_bg_color']) && $this->styler['file_upload_bg_color'] !== '') {
            $style .= "--ht-file-upload-bg-color: {$this->styler['file_upload_bg_color']};";
        }
        if(isset($this->styler['file_upload_btn_color']) && $this->styler['file_upload_btn_color'] !== '') {
            $style .= "--ht-file-upload-btn-color: {$this->styler['file_upload_btn_color']};";
        }
        if(isset($this->styler['file_upload_btn_bg']) && $this->styler['file_upload_btn_bg'] !== '') {
            $style .= "--ht-file-upload-btn-bg: {$this->styler['file_upload_btn_bg']};";
        }
        if(isset($this->styler['file_upload_radius']) && $this->styler['file_upload_radius'] !== '') {
            $style .= "--ht-file-upload-radius: {$this->styler['file_upload_radius']}px;";
        }
        return $style;
    }

    /**
     * Get section break style
     *
     * @return string
     */
    public function get_section_break_style() {
        $style = '';
        if(isset($this->styler['section_break_heading_color']) && $this->styler['section_break_heading_color'] !== '') {
            $style .= "--ht-section-break-heading-color: {$this->styler['section_break_heading_color']};";
        }
        if(isset($this->styler['section_break_heading_size']) && $this->styler['section_break_heading_size'] !== '') {
            $style .= "--ht-section-break-heading-fz: {$this->styler['section_break_heading_size']}px;";
        }
        if(isset($this->styler['section_break_heading_weight']) && $this->styler['section_break_heading_weight'] !== '') {
            $style .= "--ht-section-break-heading-fw: {$this->styler['section_break_heading_weight']};";
        }
        if(isset($this->styler['section_break_divider_color']) && $this->styler['section_break_divider_color'] !== '') {
            $style .= "--ht-section-break-divider-color: {$this->styler['section_break_divider_color']};";
        }
        if(isset($this->styler['section_break_divider_width']) && $this->styler['section_break_divider_width'] !== '') {
            $style .= "--ht-section-break-divider-width: {$this->styler['section_break_divider_width']}px;";
        }
        if(isset($this->styler['section_break_divider_style']) && $this->styler['section_break_divider_style'] !== '') {
            $style .= "--ht-section-break-divider-style: {$this->styler['section_break_divider_style']};";
        }
        return $style;
    }

    /**
     * Get NPS style
     *
     * @return string
     */
    public function get_nps_style() {
        $style = '';
        if(isset($this->styler['nps_color']) && $this->styler['nps_color'] !== '') {
            $style .= "--ht-nps-color: {$this->styler['nps_color']};";
        }
        if(isset($this->styler['nps_color_active']) && $this->styler['nps_color_active'] !== '') {
            $style .= "--ht-nps-color-active: {$this->styler['nps_color_active']};";
        }
        if(isset($this->styler['nps_bg_color']) && $this->styler['nps_bg_color'] !== '') {
            $style .= "--ht-nps-bg-color: {$this->styler['nps_bg_color']};";
        }
        if(isset($this->styler['nps_bg_color_active']) && $this->styler['nps_bg_color_active'] !== '') {
            $style .= "--ht-nps-bg-color-active: {$this->styler['nps_bg_color_active']};";
        }
        if(isset($this->styler['nps_size']) && $this->styler['nps_size'] !== '') {
            $style .= "--ht-nps-fz: {$this->styler['nps_size']}px;";
        }
        if(isset($this->styler['nps_radius']) && $this->styler['nps_radius'] !== '') {
            $style .= "--ht-nps-radius: {$this->styler['nps_radius']}px;";
        }
        return $style;
    }
}