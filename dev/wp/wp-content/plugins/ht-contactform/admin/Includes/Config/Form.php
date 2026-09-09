<?php
namespace HTContactFormAdmin\Includes\Config;

use HTContactFormAdmin\Includes\Config\Field;
use HTContactFormAdmin\Includes\Config\Styler;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\Mailchimp;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\ActiveCampaign;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\MailerLite;
use HTContactFormAdmin\Includes\Config\Countries;

use HTContactFormAdmin\Includes\Config\Editor\Integrations\Insightly;
use HTContactFormAdmin\Includes\Config\Editor\Integrations\OnepageCRM;

class Form {

    private $global_settings = [];
    private $integrations = [];
    private $mailchimp = null;
    private $activeCampaign = null;
    private $mailerlite = null;

    private static $instance = null;

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static $field = null;
    public static $countries = null;
    public static $styler = null;

    public static $is_pro_active = false;

    public function __construct() {
        self::$field = Field::get_instance();
        self::$countries = Countries::get_instance();
        self::$styler = Styler::get_instance();
        self::$is_pro_active = is_plugin_active('ht-contactform-pro/ht-contactform-pro.php');
        $this->global_settings = get_option('ht_form_global_settings', []);
        $this->integrations = get_option('ht_form_integrations', []);
        $this->mailchimp = Mailchimp::get_instance();
        $this->activeCampaign = ActiveCampaign::get_instance();
        $this->mailerlite = MailerLite::get_instance();
    }

    /**
     * Check if reCAPTCHA field should be disabled in form builder
     *
     * @return bool True if disabled, false if enabled
     */
    private function is_recaptcha_disabled(): bool {
        $active = $this->global_settings['captcha']['recaptcha_active_version'] ?? '';

        if ($active === 'v2') {
            return empty($this->global_settings['captcha']['recaptcha_v2_site_key']) ||
                   empty($this->global_settings['captcha']['recaptcha_v2_secret_key']);
        } elseif ($active === 'v3') {
            return empty($this->global_settings['captcha']['recaptcha_v3_site_key']) ||
                   empty($this->global_settings['captcha']['recaptcha_v3_secret_key']);
        }

        // Disabled if no version selected
        return true;
    }

    /**
     * Get available form fields
     *
     * @return array Array of form fields
     */
    public function fields(): array {
        return apply_filters('ht_form_fields', [
            [
                'id' => 'input',
                'type' => 'input',
                'label' => __('Simple Text', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(),
                    self::$field->label(),
                    self::$field->label_position(),
                    self::$field->label_hide(),
                    self::$field->placeholder(),
                    self::$field->required(),
                    self::$field->required_message(),
                    self::$field->value(),
                    self::$field->size(),
                    self::$field->class(),
                    self::$field->message(),
                    self::$field->message_position(),
                    self::$field->prefix_label(),
                    self::$field->suffix_label(),
                    self::$field->name_attribute(),
                    self::$field->max_length(),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'name',
                'type' => 'name',
                'label' => __('Name', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('Name', 'ht-contactform')]),
                    self::$field->name_format(),
                    self::$field->names(),
                    self::$field->label_position(),
                    self::$field->label_hide(),
                    self::$field->size(),
                    self::$field->class(),
                    self::$field->message_position(),
                    self::$field->name_attribute(['value' => 'name']),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'textarea',
                'type' => 'textarea',
                'label' => __('Textarea', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('Message', 'ht-contactform')]),
                    self::$field->label(['value' => __('Message', 'ht-contactform')]),
                    self::$field->label_position(),
                    self::$field->label_hide(),
                    self::$field->placeholder(),
                    self::$field->required(),
                    self::$field->required_message(),
                    self::$field->value(),
                    self::$field->size(),
                    self::$field->class(),
                    self::$field->message(),
                    self::$field->message_position(),
                    self::$field->name_attribute(['value' => 'message']),
                    self::$field->max_length(),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'mask_input',
                'type' => 'mask_input',
                'label' => __('Mask Input', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('Mask', 'ht-contactform')]),
                    self::$field->label(['value' => __('Mask Input', 'ht-contactform')]),
                    self::$field->label_position(),
                    self::$field->label_hide(),
                    self::$field->placeholder(),
                    self::$field->mask(),
                    self::$field->required(),
                    self::$field->required_message(),
                    self::$field->value(),
                    self::$field->size(),
                    self::$field->class(),
                    self::$field->message(),
                    self::$field->message_position(),
                    self::$field->prefix_label(),
                    self::$field->suffix_label(),
                    self::$field->name_attribute(['value' => 'input_mask']),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'dropdown',
                'type' => 'dropdown',
                'label' => __('Dropdown', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('Dropdown', 'ht-contactform')]),
                    self::$field->label(['value' => __('Dropdown', 'ht-contactform')]),
                    self::$field->label_position(),
                    self::$field->label_hide(),
                    self::$field->placeholder(['value' => 'Select an option']),
                    self::$field->options(),
                    self::$field->searchable(),
                    self::$field->required(),
                    self::$field->required_message(),
                    self::$field->size(),
                    self::$field->class(),
                    self::$field->message(),
                    self::$field->message_position(),
                    self::$field->name_attribute(['value' => 'dropdown']),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'multiple_choices',
                'type' => 'multiple_choices',
                'label' => __('Multiple Choices', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('Multiple Choices', 'ht-contactform')]),
                    self::$field->label(['value' => __('Multiple Choices', 'ht-contactform')]),
                    self::$field->label_position(),
                    self::$field->label_hide(),
                    self::$field->placeholder(),
                    self::$field->options([
                        'option_type' => 'checkbox',
                    ]),
                    self::$field->searchable(),
                    self::$field->max_selection(),
                    self::$field->required(),
                    self::$field->required_message(),
                    self::$field->size(),
                    self::$field->class(),
                    self::$field->message(),
                    self::$field->message_position(),
                    self::$field->name_attribute(['value' => 'multi_select']),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'checkboxes',
                'type' => 'checkboxes',
                'label' => __('Checkboxes', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('Checkboxes', 'ht-contactform')]),
                    self::$field->label(['value' => __('Checkboxes', 'ht-contactform')]),
                    self::$field->label_position(),
                    self::$field->label_hide(),
                    self::$field->options([
                        'option_type' => 'checkbox',
                    ]),
                    self::$field->layout(),
                    self::$field->required(),
                    self::$field->required_message(),
                    self::$field->size(),
                    self::$field->class(),
                    self::$field->message(),
                    self::$field->message_position(),
                    self::$field->name_attribute(['value' => 'checkboxes']),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'radio',
                'type' => 'radio',
                'label' => __('Radio', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('Radio', 'ht-contactform')]),
                    self::$field->label(['value' => __('Radio', 'ht-contactform')]),
                    self::$field->label_position(),
                    self::$field->label_hide(),
                    self::$field->options([
                        'option_type' => 'radio',
                    ]),
                    self::$field->layout(),
                    self::$field->required(),
                    self::$field->required_message(),
                    self::$field->size(),
                    self::$field->class(),
                    self::$field->message(),
                    self::$field->message_position(),
                    self::$field->name_attribute(['value' => 'radio']),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'phone',
                'type' => 'phone',
                'label' => __('Phone', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('Phone', 'ht-contactform')]),
                    self::$field->label(['value' => __('Phone', 'ht-contactform')]),
                    self::$field->label_position(),
                    self::$field->label_hide(),
                    self::$field->placeholder(['value' => __('Mobile Number', 'ht-contactform')]),
                    self::$field->required(),
                    self::$field->required_message(),
                    self::$field->create([
                        'id' => 'validate',
                        'label' => __('Validate Phone Number', 'ht-contactform'),
                        'type' => 'switch',
                        'info' => __('Toggle to enable phone number validation.', 'ht-contactform'),
                        'value' => false,
                    ]),
                    self::$field->create([
                        'id' => 'validate_message',
                        'label' => __('Validation Error Message', 'ht-contactform'),
                        'info' => __('Message will be shown if validation fails for phone number. Leave empty to use global message. Configure global message from: Global Settings -> Validation Messages.', 'ht-contactform'),
                        'value' => 'Invalid phone number.',
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'validate',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'auto_country_select',
                        'label' => __('Enable Auto Country Select', 'ht-contactform'),
                        'type' => 'switch',
                        'info' => __('If enable auto country select, it will automatically select the country based on the user IP address.', 'ht-contactform'),
                        'value' => false,
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'validate',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'default_country',
                        'label' => __('Default Country', 'ht-contactform'),
                        'type' => 'select',
                        'searchable' => true,
                        'value' => 'us',
                        'options' => self::$countries->get_all(),
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'validate',
                                    'value' => true,
                                    'compare' => '==',
                                ],
                                [
                                    'id' => 'auto_country_select',
                                    'value' => false,
                                    'compare' => '==',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'country_list_type',
                        'label' => __('Country List', 'ht-contactform'),
                        'type' => 'radio_button',
                        'value' => 'all',
                        'options' => [
                            [
                                'value' => 'all',
                                'label' => __('Show All', 'ht-contactform'),
                            ],
                            [
                                'value' => 'include',
                                'label' => __('Show Selected', 'ht-contactform'),
                            ],
                            [
                                'value' => 'exclude',
                                'label' => __('Hide Selected', 'ht-contactform'),
                            ],
                        ],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'validate',
                                    'value' => true,
                                    'compare' => '==',
                                ],
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'country_list',
                        'type' => 'select',
                        'multiple' => true,
                        'searchable' => true,
                        'value' => [],
                        'options' => self::$countries->get_all(),
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'validate',
                                    'value' => true,
                                    'compare' => '==',
                                ],
                                [
                                    'id' => 'country_list_type',
                                    'value' => 'all',
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->value(),
                    self::$field->size(),
                    self::$field->class(),
                    self::$field->message(),
                    self::$field->message_position(),
                    self::$field->prefix_label(),
                    self::$field->suffix_label(),
                    self::$field->name_attribute(['value' => 'phone']),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'number',
                'type' => 'number',
                'label' => __('Number', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('Number', 'ht-contactform')]),
                    self::$field->label(['value' => __('Number', 'ht-contactform')]),
                    self::$field->label_position(),
                    self::$field->label_hide(),
                    self::$field->placeholder(),
                    self::$field->required(),
                    self::$field->required_message(),
                    self::$field->min(),
                    self::$field->max(),
                    self::$field->step(),
                    self::$field->value(),
                    self::$field->size(),
                    self::$field->class(),
                    self::$field->message(),
                    self::$field->message_position(),
                    self::$field->prefix_label(),
                    self::$field->suffix_label(),
                    self::$field->name_attribute(['value' => 'number']),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'gdpr',
                'type' => 'gdpr',
                'label' => __('GDPR Agreement', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('GDPR Agreement', 'ht-contactform')]),
                    self::$field->create([
                        'id' => 'required_message',
                        'label' => __('Required Error Message', 'ht-contactform'),
                        'info' => __('This message will be shown if validation fails for Required. Leave empty to use global message. Configure Global Message from: Global settings > Validation Messages', 'ht-contactform'),
                        'value' => __('This field is required', 'ht-contactform'),
                    ]),
                    self::$field->create([
                        'id' => 'description',
                        'label' => __('Description', 'ht-contactform'),
                        'type' => 'textarea',
                        'info' => __('This message will be shown with GDPR agreement checkbox.', 'ht-contactform'),
                        'value' => __('I agree to allow this website to store my submitted information in order to respond to my inquiry.', 'ht-contactform'),
                    ]),
                    self::$field->size(),
                    self::$field->class(),
                    self::$field->name_attribute(['value' => 'gdpr_agreement']),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'recaptcha',
                'type' => 'recaptcha',
                'label' => __('reCAPTCHA', 'ht-contactform'),
                'disabled' => $this->is_recaptcha_disabled(),
                'disabled_data' => [
                    'title' => __('Configuration Required', 'ht-contactform'),
                    'message' => __('reCAPTCHA is not configured, please configure it in Global Settings > Captcha', 'ht-contactform'),
                ],
                'settings' => [
                    self::$field->name_attribute(['value' => 'g-recaptcha-response', 'disabled' => true]),
                ],
            ],
            [
                'id' => 'hcaptcha',
                'type' => 'hcaptcha',
                'label' => __('hCaptcha', 'ht-contactform'),
                'disabled' => empty($this->global_settings['captcha']['hcaptcha_secret_key']) || empty($this->global_settings['captcha']['hcaptcha_site_key']),
                'disabled_data' => [
                    'title' => __('Configuration Required', 'ht-contactform'),
                    'message' => __('hCaptcha is not configured, please configure it in Global Settings > Captcha', 'ht-contactform'),
                ],
                'settings' => [
                    self::$field->name_attribute(['value' => 'h-captcha-response', 'disabled' => true]),
                ],
            ],
            [
                'id' => 'email',
                'type' => 'email',
                'label' => __('Email', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('Email', 'ht-contactform')]),
                    self::$field->label(['value' => __('Email', 'ht-contactform')]),
                    self::$field->label_position(),
                    self::$field->label_hide(),
                    self::$field->placeholder(),
                    self::$field->required(),
                    self::$field->required_message(),
                    self::$field->email_validation(),
                    self::$field->create([
                        'id' => 'email_validation_message',
                        'label' => __('Email Validation Error Message', 'ht-contactform'),
                        'info' => __('This message will be shown if validation fails for Email. Leave empty to use global message. Configure Global Message from: Global settings > Validation Messages', 'ht-contactform'),
                        'value' => __('This field must contain a valid email', 'ht-contactform'),
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'email_validation',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ],
                    ]),
                    // self::$field->email_unique(),
                    // self::$field->create([
                    //     'id' => 'email_unique_message',
                    //     'label' => __('Validation Message for Duplicate Email', 'ht-contactform'),
                    //     'info' => __('This message will be shown if validation fails for Email. Leave empty to use global message. Configure Global Message from: Global settings > Validation Messages', 'ht-contactform'),
                    //     'value' => __('Email address need to be unique.', 'ht-contactform'),
                    //     'dependency' => [
                    //         'relation' => 'AND',
                    //         'rules' => [
                    //             [
                    //                 'id' => 'email_unique',
                    //                 'value' => true,
                    //                 'compare' => '==',
                    //             ]
                    //         ]
                    //     ],
                    // ]),
                    self::$field->value(),
                    self::$field->size(),
                    self::$field->class(),
                    self::$field->message(),
                    self::$field->message_position(),
                    self::$field->prefix_label(),
                    self::$field->suffix_label(),
                    self::$field->name_attribute(['value' => 'email']),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'password',
                'type' => 'password',
                'label' => __('Password', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('Password', 'ht-contactform')]),
                    self::$field->label(['value' => __('Password', 'ht-contactform')]),
                    self::$field->label_position(),
                    self::$field->label_hide(),
                    self::$field->placeholder(['value' => __('Password', 'ht-contactform')]),
                    self::$field->required(),
                    self::$field->required_message(),
                    self::$field->value(),
                    self::$field->size(),
                    self::$field->class(),
                    self::$field->message(),
                    self::$field->message_position(),
                    self::$field->name_attribute(['value' => 'password']),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'url',
                'type' => 'url',
                'label' => __('Website URL', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('URL', 'ht-contactform')]),
                    self::$field->label(['value' => __('URL', 'ht-contactform')]),
                    self::$field->label_position(),
                    self::$field->label_hide(),
                    self::$field->placeholder(),
                    self::$field->required(),
                    self::$field->required_message(),
                    self::$field->create([
                        'id' => 'validate_url',
                        'label' => __('Validate URL', 'ht-contactform'),
                        'info' => __('Select whether to validate this field as URL or not', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => true,
                    ]),
                    self::$field->create([
                        'id' => 'validate_url_message',
                        'label' => __('URL Validation Error Message', 'ht-contactform'),
                        'info' => __('This message will be shown if validation fails for URL. Leave empty to use global message. Configure Global Message from: Global settings > Validation Messages', 'ht-contactform'),
                        'value' => __('This field must contain a valid URL', 'ht-contactform'),
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'validate_url',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->value(),
                    self::$field->size(),
                    self::$field->class(),
                    self::$field->message(),
                    self::$field->message_position(),
                    self::$field->prefix_label(),
                    self::$field->suffix_label(),
                    self::$field->name_attribute(['value' => 'url']),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'hidden',
                'type' => 'hidden',
                'label' => __('Hidden Field', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('Hidden', 'ht-contactform')]),
                    self::$field->value(),
                    self::$field->name_attribute(['value' => 'hidden']),
                ],
            ],
            [
                'id' => 'custom_html',
                'type' => 'custom_html',
                'label' => __('Custom HTML', 'ht-contactform'),
                'settings' => [
                    self::$field->create([
                        'id' => 'html',
                        'label' => __('HTML', 'ht-contactform'),
                        'type' => 'richtext',
                        'value' => '<p>Some description about this section</p>',
                        'support' => ['tags'],
                    ]),
                    self::$field->class(),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'terms_conditions',
                'type' => 'terms_conditions',
                'label' => __('Terms & Conditions', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('Terms & Conditions', 'ht-contactform')]),
                    self::$field->required(),
                    self::$field->required_message(),
                    self::$field->create([
                        'id' => 'content',
                        'label' => __('Content', 'ht-contactform'),
                        'type' => 'richtext',
                        'value' => '<p>By checking this box, you agree to our terms and conditions.</p>',
                        'support' => ['tags'],
                    ]),
                    self::$field->size(),
                    self::$field->class(),
                    self::$field->name_attribute(['value' => 'terms_and_conditions']),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'date_time',
                'type' => 'date_time',
                'label' => __('Date & Time', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('Date & Time', 'ht-contactform')]),
                    self::$field->label(['value' => __('Date & Time', 'ht-contactform')]),
                    self::$field->label_position(),
                    self::$field->label_hide(),
                    self::$field->placeholder(['value' => __('Select Date & Time', 'ht-contactform')]),
                    self::$field->create([
                        'id' => 'format',
                        'label' => __('Format', 'ht-contactform'),
                        'value' => 'Y-m-d H:i',
                        'info' => __('Select the format for the date and time.', 'ht-contactform'),
                        'type' => 'select',
                        'options' => [
                            [
                                'value' => 'Y-m-d',
                                'label' => 'Y-m-d (2025-05-18, ISO standard format)',
                            ],
                            [
                                'value' => 'd-m-Y',
                                'label' => 'd-m-Y (18-05-2025, European format)',
                            ],
                            [
                                'value' => 'm-d-Y',
                                'label' => 'm-d-Y (05-18-2025, US format)',
                            ],
                            [
                                'value' => 'Y/m/d',
                                'label' => 'Y/m/d (2025/05/18, ISO format with slashes)',
                            ],
                            [
                                'value' => 'd/m/Y',
                                'label' => 'd/m/Y (18/05/2025, European format with slashes)',
                            ],
                            [
                                'value' => 'm/d/Y',
                                'label' => 'm/d/Y (05/18/2025, US format with slashes)',
                            ],
                            [
                                'value' => 'F j, Y',
                                'label' => 'F j, Y (May 18, 2025, Full month with day)',
                            ],
                            [
                                'value' => 'j F Y',
                                'label' => 'j F Y (18 May 2025, Day with full month)',
                            ],
                            [
                                'value' => 'M j, Y',
                                'label' => 'M j, Y (May 18, 2025, Abbreviated month)',
                            ],
                            [
                                'value' => 'Y-m-d H:i',
                                'label' => 'Y-m-d H:i (2025-05-18 12:42, ISO with time)',
                            ],
                            [
                                'value' => 'd-m-Y H:i',
                                'label' => 'd-m-Y H:i (18-05-2025 12:42, European with time)',
                            ],
                            [
                                'value' => 'm-d-Y H:i',
                                'label' => 'm-d-Y H:i (05-18-2025 12:42, US with time)',
                            ],
                            [
                                'value' => 'F j, Y H:i',
                                'label' => 'F j, Y H:i (May 18, 2025 12:42, Full date with time)',
                            ],
                            [
                                'value' => 'Y-m-d H:i:s',
                                'label' => 'Y-m-d H:i:s (2025-05-18 12:42:31, ISO with seconds)',
                            ],
                            [
                                'value' => 'd-m-Y H:i:s',
                                'label' => 'd-m-Y H:i:s (18-05-2025 12:42:31, European with seconds)',
                            ],
                            [
                                'value' => 'm-d-Y H:i:s',
                                'label' => 'm-d-Y H:i:s (05-18-2025 12:42:31, US with seconds)',
                            ],
                            [
                                'value' => 'H:i',
                                'label' => 'H:i (12:42, 24-hour format)',
                            ],
                            [
                                'value' => 'h:i K',
                                'label' => 'h:i K (12:42 PM, 12-hour format with AM/PM)',
                            ],
                            [
                                'value' => 'H:i:s',
                                'label' => 'H:i:s (12:42:31, 24-hour format with seconds)',
                            ],
                            [
                                'value' => 'h:i:s K',
                                'label' => 'h:i:s K (12:42:31 PM, 12-hour format with seconds)',
                            ],
                            [
                                'value' => 'l, F j, Y',
                                'label' => 'l, F j, Y (Sunday, May 18, 2025, Full day and month)',
                            ],
                            [
                                'value' => 'D, M j, Y',
                                'label' => 'D, M j, Y (Sun, May 18, 2025, Abbreviated day and month)',
                            ],
                            [
                                'value' => 'j. F Y',
                                'label' => 'j. F Y (18. May 2025, European style with dot)',
                            ],
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'multiple',
                        'label' => __('Enable Multiple Selection', 'ht-contactform'),
                        'info' => __('Enabling multiple selection will disable the time picker and allow users to select multiple dates.', 'ht-contactform'),
                        'value' => false,
                        'type' => 'switch',
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'format',
                                    'value' => 'H:i',
                                    'compare' => 'not_contains',
                                ],
                                [
                                    'id' => 'format',
                                    'value' => 'h:i K',
                                    'compare' => 'not_contains',
                                ],
                                [
                                    'id' => 'format',
                                    'value' => 'H:i:s',
                                    'compare' => 'not_contains',
                                ],
                                [
                                    'id' => 'format',
                                    'value' => 'h:i:s A',
                                    'compare' => 'not_contains',
                                ],
                                [
                                    'id' => 'range',
                                    'value' => true,
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'range',
                        'label' => __('Enable Range Selection', 'ht-contactform'),
                        'info' => __('Enabling range selection will disable the time picker and allow users to select a range of dates.', 'ht-contactform'),
                        'value' => false,
                        'type' => 'switch',
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'format',
                                    'value' => 'H:i',
                                    'compare' => 'not_contains',
                                ],
                                [
                                    'id' => 'format',
                                    'value' => 'h:i A',
                                    'compare' => 'not_contains',
                                ],
                                [
                                    'id' => 'format',
                                    'value' => 'H:i:s',
                                    'compare' => 'not_contains',
                                ],
                                [
                                    'id' => 'format',
                                    'value' => 'h:i:s A',
                                    'compare' => 'not_contains',
                                ],
                                [
                                    'id' => 'multiple',
                                    'value' => true,
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->required(),
                    self::$field->required_message(),
                    self::$field->value(),
                    self::$field->size(),
                    self::$field->class(),
                    self::$field->message(),
                    self::$field->message_position(),
                    self::$field->name_attribute(['value' => 'date_time']),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'country',
                'type' => 'country',
                'label' => __('Country List', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('Country', 'ht-contactform')]),
                    self::$field->label(['value' => __('Country', 'ht-contactform')]),
                    self::$field->label_position(),
                    self::$field->label_hide(),
                    self::$field->placeholder(['value' => __('Select Country', 'ht-contactform')]),
                    self::$field->required(),
                    self::$field->required_message(),
                    self::$field->create([
                        'id' => 'auto_country_select',
                        'label' => __('Enable Auto Country Select', 'ht-contactform'),
                        'type' => 'switch',
                        'info' => __('If enable auto country select, it will automatically select the country based on the user IP address.', 'ht-contactform'),
                        'value' => false,
                    ]),
                    self::$field->create([
                        'id' => 'default_country',
                        'label' => __('Default Country', 'ht-contactform'),
                        'type' => 'select',
                        'searchable' => true,
                        'value' => 'us',
                        'options' => self::$countries->get_all(),
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'auto_country_select',
                                    'value' => false,
                                    'compare' => '==',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'country_list_type',
                        'label' => __('Country List', 'ht-contactform'),
                        'type' => 'radio_button',
                        'value' => 'all',
                        'options' => [
                            [
                                'value' => 'all',
                                'label' => __('Show All', 'ht-contactform'),
                            ],
                            [
                                'value' => 'include',
                                'label' => __('Show Selected', 'ht-contactform'),
                            ],
                            [
                                'value' => 'exclude',
                                'label' => __('Hide Selected', 'ht-contactform'),
                            ],
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'country_list',
                        'type' => 'select',
                        'multiple' => true,
                        'searchable' => true,
                        'value' => [],
                        'options' => self::$countries->get_all(),
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'country_list_type',
                                    'value' => 'all',
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->value(),
                    self::$field->size(),
                    self::$field->class(),
                    self::$field->message(),
                    self::$field->message_position(),
                    self::$field->name_attribute(['value' => 'country']),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'address',
                'type' => 'address',
                'label' => __('Address', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('Address', 'ht-contactform')]),
                    self::$field->label_position(),
                    self::$field->label_hide(),
                    self::$field->create([
                        'id' => 'enable_address_line_1',
                        'type' => 'switch',
                        'label' => __('Enable Address Line 1', 'ht-contactform'),
                        'value' => true,
                    ]),
                    self::$field->create([
                        'id' => 'address_line_1',
                        'type' => 'collapse',
                        'label' => __('Address Line 1', 'ht-contactform'),
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enable_address_line_1',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ],
                        'value' => [
                                'label' => 'Address Line 1',
                                'placeholder' => 'Address Line 1',
                                'value' => '',
                                'required' => false,
                                'required_message' => '',
                        ],
                        'fields' => [
                            self::$field->create([
                                'id' => 'label',
                                'label' => __('Label', 'ht-contactform'),
                            ]),
                            self::$field->create([
                                'id' => 'placeholder',
                                'label' => __('Placeholder', 'ht-contactform'),
                            ]),
                            self::$field->create([
                                'id' => 'help_message',
                                'label' => __('Help Message', 'ht-contactform'),
                                'type' => 'textarea',
                            ]),
                            self::$field->create([
                                'id' => 'default_value',
                                'label' => __('Default Value', 'ht-contactform'),
                            ]),
                            self::$field->create([
                                'id' => 'required',
                                'label' => __('Required', 'ht-contactform'),
                                'type' => 'switch',
                            ]),
                            self::$field->create([
                                'id' => 'required_message',
                                'label' => __('Required Message', 'ht-contactform'),
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
                            ]),
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'enable_address_line_2',
                        'type' => 'switch',
                        'label' => __('Enable Address Line 2', 'ht-contactform'),
                        'value' => true,
                    ]),
                    self::$field->create([
                        'id' => 'address_line_2',
                        'type' => 'collapse',
                        'label' => __('Address Line 2', 'ht-contactform'),
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enable_address_line_2',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ],
                        'value' => [
                                'label' => 'Address Line 2',
                                'placeholder' => 'Address Line 2',
                                'value' => '',
                                'required' => false,
                                'required_message' => '',
                        ],
                        'fields' => [
                            self::$field->create([
                                'id' => 'label',
                                'label' => __('Label', 'ht-contactform'),
                            ]),
                            self::$field->create([
                                'id' => 'placeholder',
                                'label' => __('Placeholder', 'ht-contactform'),
                            ]),
                            self::$field->create([
                                'id' => 'help_message',
                                'label' => __('Help Message', 'ht-contactform'),
                                'type' => 'textarea',
                            ]),
                            self::$field->create([
                                'id' => 'default_value',
                                'label' => __('Default Value', 'ht-contactform'),
                            ]),
                            self::$field->create([
                                'id' => 'required',
                                'label' => __('Required', 'ht-contactform'),
                                'type' => 'switch',
                            ]),
                            self::$field->create([
                                'id' => 'required_message',
                                'label' => __('Required Message', 'ht-contactform'),
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
                            ]),
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'enable_city',
                        'type' => 'switch',
                        'label' => __('Enable City', 'ht-contactform'),
                        'value' => true,
                    ]),
                    self::$field->create([
                        'id' => 'city',
                        'type' => 'collapse',
                        'label' => __('City', 'ht-contactform'),
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enable_city',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ],
                        'value' => [
                                'label' => 'City',
                                'placeholder' => 'City',
                                'value' => '',
                                'required' => false,
                                'required_message' => '',
                        ],
                        'fields' => [
                            self::$field->create([
                                'id' => 'label',
                                'label' => __('Label', 'ht-contactform'),
                            ]),
                            self::$field->create([
                                'id' => 'placeholder',
                                'label' => __('Placeholder', 'ht-contactform'),
                            ]),
                            self::$field->create([
                                'id' => 'help_message',
                                'label' => __('Help Message', 'ht-contactform'),
                                'type' => 'textarea',
                            ]),
                            self::$field->create([
                                'id' => 'default_value',
                                'label' => __('Default Value', 'ht-contactform'),
                            ]),
                            self::$field->create([
                                'id' => 'auto_fill',
                                'label' => __('Auto Fill', 'ht-contactform'),
                                'type' => 'switch',
                                'info' => __('If enable, it will automatically fill the city based on the user IP address.', 'ht-contactform'),
                                'value' => false,
                            ]),
                            self::$field->create([
                                'id' => 'required',
                                'label' => __('Required', 'ht-contactform'),
                                'type' => 'switch',
                            ]),
                            self::$field->create([
                                'id' => 'required_message',
                                'label' => __('Required Message', 'ht-contactform'),
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
                            ]),
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'enable_state',
                        'type' => 'switch',
                        'label' => __('Enable State', 'ht-contactform'),
                        'value' => true,
                    ]),
                    self::$field->create([
                        'id' => 'state',
                        'type' => 'collapse',
                        'label' => __('State', 'ht-contactform'),
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enable_state',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ],
                        'value' => [
                                'label' => 'State',
                                'placeholder' => 'State',
                                'value' => '',
                                'required' => false,
                                'required_message' => '',
                        ],
                        'fields' => [
                            self::$field->create([
                                'id' => 'label',
                                'label' => __('Label', 'ht-contactform'),
                            ]),
                            self::$field->create([
                                'id' => 'placeholder',
                                'label' => __('Placeholder', 'ht-contactform'),
                            ]),
                            self::$field->create([
                                'id' => 'help_message',
                                'label' => __('Help Message', 'ht-contactform'),
                                'type' => 'textarea',
                            ]),
                            self::$field->create([
                                'id' => 'default_value',
                                'label' => __('Default Value', 'ht-contactform'),
                            ]),
                            self::$field->create([
                                'id' => 'auto_fill',
                                'label' => __('Auto Fill', 'ht-contactform'),
                                'type' => 'switch',
                                'info' => __('If enable, it will automatically fill the state based on the user IP address.', 'ht-contactform'),
                                'value' => false,
                            ]),
                            self::$field->create([
                                'id' => 'required',
                                'label' => __('Required', 'ht-contactform'),
                                'type' => 'switch',
                            ]),
                            self::$field->create([
                                'id' => 'required_message',
                                'label' => __('Required Message', 'ht-contactform'),
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
                            ]),
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'enable_zip',
                        'type' => 'switch',
                        'label' => __('Enable Zip Code', 'ht-contactform'),
                        'value' => true,
                    ]),
                    self::$field->create([
                        'id' => 'zip',
                        'type' => 'collapse',
                        'label' => __('Zip Code', 'ht-contactform'),
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enable_zip',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ],
                        'value' => [
                                'label' => 'Zip Code',
                                'placeholder' => 'Zip Code',
                                'value' => '',
                                'required' => false,
                                'required_message' => '',
                        ],
                        'fields' => [
                            self::$field->create([
                                'id' => 'label',
                                'label' => __('Label', 'ht-contactform'),
                            ]),
                            self::$field->create([
                                'id' => 'placeholder',
                                'label' => __('Placeholder', 'ht-contactform'),
                            ]),
                            self::$field->create([
                                'id' => 'help_message',
                                'label' => __('Help Message', 'ht-contactform'),
                                'type' => 'textarea',
                            ]),
                            self::$field->create([
                                'id' => 'default_value',
                                'label' => __('Default Value', 'ht-contactform'),
                            ]),
                            self::$field->create([
                                'id' => 'auto_fill',
                                'label' => __('Auto Fill', 'ht-contactform'),
                                'type' => 'switch',
                                'info' => __('If enable, it will automatically fill the zip code based on the user IP address.', 'ht-contactform'),
                                'value' => false,
                            ]),
                            self::$field->create([
                                'id' => 'required',
                                'label' => __('Required', 'ht-contactform'),
                                'type' => 'switch',
                            ]),
                            self::$field->create([
                                'id' => 'required_message',
                                'label' => __('Required Message', 'ht-contactform'),
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
                            ]),
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'enable_country',
                        'type' => 'switch',
                        'label' => __('Enable Country', 'ht-contactform'),
                        'value' => true,
                    ]),
                    self::$field->create([
                        'id' => 'country',
                        'type' => 'collapse',
                        'label' => __('Country', 'ht-contactform'),
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enable_country',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ],
                        'value' => [
                                'label' => 'Country',
                                'placeholder' => 'Country',
                                'value' => '',
                                'required' => false,
                                'required_message' => '',
                        ],
                        'fields' => [
                            self::$field->create([
                                'id' => 'label',
                                'label' => __('Label', 'ht-contactform'),
                            ]),
                            self::$field->create([
                                'id' => 'placeholder',
                                'label' => __('Placeholder', 'ht-contactform'),
                            ]),
                            self::$field->create([
                                'id' => 'help_message',
                                'label' => __('Help Message', 'ht-contactform'),
                                'type' => 'textarea',
                            ]),
                            self::$field->create([
                                'id' => 'default_value',
                                'label' => __('Default Value', 'ht-contactform'),
                            ]),
                            self::$field->create([
                                'id' => 'auto_fill',
                                'label' => __('Auto Fill', 'ht-contactform'),
                                'type' => 'switch',
                                'info' => __('If enable, it will automatically fill the country based on the user IP address.', 'ht-contactform'),
                                'value' => false,
                            ]),
                            self::$field->create([
                                'id' => 'default_country',
                                'label' => __('Default Country', 'ht-contactform'),
                                'type' => 'select',
                                'searchable' => true,
                                'allowDeselect' => true,
                                'value' => 'us',
                                'options' => self::$countries->get_all(),
                                'dependency' => [
                                    'relation' => 'AND',
                                    'rules' => [
                                        [
                                            'id' => 'auto_fill',
                                            'value' => false,
                                            'compare' => '==',
                                        ]
                                    ]
                                ],
                            ]),
                            self::$field->create([
                                'id' => 'country_list_type',
                                'label' => __('Country List', 'ht-contactform'),
                                'type' => 'radio_button',
                                'value' => 'all',
                                'options' => [
                                    [
                                        'value' => 'all',
                                        'label' => __('Show All', 'ht-contactform'),
                                    ],
                                    [
                                        'value' => 'include',
                                        'label' => __('Show Selected', 'ht-contactform'),
                                    ],
                                    [
                                        'value' => 'exclude',
                                        'label' => __('Hide Selected', 'ht-contactform'),
                                    ],
                                ],
                            ]),
                            self::$field->create([
                                'id' => 'country_list',
                                'type' => 'select',
                                'multiple' => true,
                                'searchable' => true,
                                'value' => [],
                                'options' => self::$countries->get_all(),
                                'dependency' => [
                                    'relation' => 'AND',
                                    'rules' => [
                                        [
                                            'id' => 'country_list_type',
                                            'value' => 'all',
                                            'compare' => '!=',
                                        ]
                                    ]
                                ],
                            ]),
                            self::$field->create([
                                'id' => 'required',
                                'label' => __('Required', 'ht-contactform'),
                                'type' => 'switch',
                            ]),
                            self::$field->create([
                                'id' => 'required_message',
                                'label' => __('Required Message', 'ht-contactform'),
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
                            ]),
                        ],
                    ]),
                    self::$field->size(),
                    self::$field->class(),
                    self::$field->message_position(),
                    self::$field->name_attribute(['value' => 'address']),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'file_upload',
                'type' => 'file_upload',
                'label' => __('File Upload', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('File Upload', 'ht-contactform')]),
                    self::$field->label(['value' => __('File Upload', 'ht-contactform')]),
                    self::$field->label_position(),
                    self::$field->label_hide(),
                    self::$field->required(),
                    self::$field->required_message(),
                    self::$field->create([
                        'id' => 'max_file_size',
                        'label' => __('Max File Size', 'ht-contactform'),
                        'info' => __('Max file size upload limit by user.', 'ht-contactform'),
                        'type' => 'number',
                        'value' => 2,
                        'min' => 1,
                        'suffix' => 'MB',
                    ]),
                    self::$field->create([
                        'id' => 'max_file_size_message',
                        'label' => __('Max File Size Error Message', 'ht-contactform'),
                        'value' => __('Max file size is 2MB', 'ht-contactform'),
                    ]),
                    self::$field->create([
                        'id' => 'max_files',
                        'label' => __('Max Files Count', 'ht-contactform'),
                        'info' => __('Max files count upload limit by user.', 'ht-contactform'),
                        'type' => 'number',
                        'value' => 1,
                        'min' => 1,
                    ]),
                    self::$field->create([
                        'id' => 'allow_types',
                        'label' => __('Allow File Types', 'ht-contactform'),
                        'type' => 'checkbox',
                        'value' => ['image', 'pdf', 'doc'],
                        'options' => [
                            [
                                'value' => 'image',
                                'label' => __('Image (jpg, png, jpeg, gif)', 'ht-contactform'),
                            ],
                            [
                                'value' => 'audio',
                                'label' => __('Audio (mp3, wav, ogg, oga, wma, mka, m4a, ra, mid, midi)', 'ht-contactform'),
                            ],
                            [
                                'value' => 'video',
                                'label' => __('Video (avi, divx, flv, mov, ogv, mkv, mp4, m4v, divx, mpg, mpeg, mpe)', 'ht-contactform'),
                            ],
                            [
                                'value' => 'pdf',
                                'label' => __('PDF (pdf)', 'ht-contactform'),
                            ],
                            [
                                'value' => 'doc',
                                'label' => __('Documents (doc, docx, ppt, pptx, xls, xlsx, txt)', 'ht-contactform'),
                            ],
                            [
                                'value' => 'zip',
                                'label' => __('Zip Archives (zip, rar, 7z, gz)', 'ht-contactform'),
                            ],
                            [
                                'value' => 'exe',
                                'label' => __('Executable (exe)', 'ht-contactform'),
                            ],
                            [
                                'value' => 'csv',
                                'label' => __('CSV (csv)', 'ht-contactform'),
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'allow_types_message',
                        'label' => __('Allow Types Error Message', 'ht-contactform'),
                        'value' => __('Validation fails for allow file types', 'ht-contactform'),
                    ]),
                    self::$field->create([
                        'id' => 'upload_location',
                        'label' => __('Upload Location', 'ht-contactform'),
                        'info' => __('Upload location.', 'ht-contactform'),
                        'type' => 'select',
                        'value' => 'ht_form_default',
                        'options' => [
                            [
                                'value' => 'ht_form_default',
                                'label' => __('HT Form Default', 'ht-contactform'),
                            ],
                            [
                                'value' => 'media_library',
                                'label' => __('Media Library', 'ht-contactform'),
                            ]
                        ],
                    ]),
                    // self::$field->size(),
                    self::$field->class(),
                    self::$field->message(),
                    self::$field->message_position(),
                    self::$field->name_attribute(['value' => 'file-upload']),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'slider',
                'type' => 'slider',
                'label' => __('Slider', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('Slider', 'ht-contactform')]),
                    self::$field->label(['value' => __('Slider', 'ht-contactform')]),
                    self::$field->label_position(),
                    self::$field->label_hide(),
                    self::$field->min([
                        'value' => 0,
                    ]),
                    self::$field->max([
                        'value' => 100,
                    ]),
                    self::$field->step([
                        'value' => 1,
                    ]),
                    self::$field->slider_display_value(),
                    self::$field->value([
                        'type' => 'number',
                        'value' => 25,
                    ]),
                    self::$field->size(),
                    self::$field->class(),
                    self::$field->message(),
                    self::$field->message_position(),
                    self::$field->name_attribute(['value' => 'slider']),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'image_upload',
                'type' => 'image_upload',
                'label' => __('Image Upload', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('Image Upload', 'ht-contactform')]),
                    self::$field->label(['value' => __('Image Upload', 'ht-contactform')]),
                    self::$field->label_position(),
                    self::$field->label_hide(),
                    self::$field->required(),
                    self::$field->required_message(),
                    self::$field->create([
                        'id' => 'max_file_size',
                        'label' => __('Max File Size', 'ht-contactform'),
                        'info' => __('Max file size upload limit by user.', 'ht-contactform'),
                        'type' => 'number',
                        'value' => 2,
                        'min' => 1,
                        'suffix' => 'MB',
                    ]),
                    self::$field->create([
                        'id' => 'max_file_size_message',
                        'label' => __('Max File Size Error Message', 'ht-contactform'),
                        'value' => __('Max file size is 2MB', 'ht-contactform'),
                    ]),
                    self::$field->create([
                        'id' => 'max_files',
                        'label' => __('Max Files Count', 'ht-contactform'),
                        'info' => __('Max files count upload limit by user.', 'ht-contactform'),
                        'type' => 'number',
                        'value' => 1,
                        'min' => 1,
                    ]),
                    self::$field->create([
                        'id' => 'allow_types',
                        'label' => __('Allow File Types', 'ht-contactform'),
                        'type' => 'checkbox',
                        'value' => ['image/jpeg'],
                        'options' => [
                            [
                                'value' => 'image/jpeg',
                                'label' => __('JPG', 'ht-contactform'),
                            ],
                            [
                                'value' => 'image/png',
                                'label' => __('PNG', 'ht-contactform'),
                            ],
                            [
                                'value' => 'image/gif',
                                'label' => __('GIF', 'ht-contactform'),
                            ],
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'allow_types_message',
                        'label' => __('Allow Types Error Message', 'ht-contactform'),
                        'value' => __('Validation fails for allow file types', 'ht-contactform'),
                    ]),
                    self::$field->create([
                        'id' => 'upload_location',
                        'label' => __('Upload Location', 'ht-contactform'),
                        'info' => __('Upload location.', 'ht-contactform'),
                        'type' => 'select',
                        'value' => 'ht_form_default',
                        'options' => [
                            [
                                'value' => 'ht_form_default',
                                'label' => __('HT Form Default', 'ht-contactform'),
                            ],
                            [
                                'value' => 'media_library',
                                'label' => __('Media Library', 'ht-contactform'),
                            ]
                        ],
                    ]),
                    // self::$field->size(),
                    self::$field->class(),
                    self::$field->message(),
                    self::$field->message_position(),
                    self::$field->name_attribute(['value' => 'image-upload']),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'shortcode',
                'type' => 'shortcode',
                'label' => __('Shortcode', 'ht-contactform'),
                'settings' => [
                    self::$field->create([
                        'id' => 'shortcode',
                        'label' => __('Shortcode', 'ht-contactform'),
                        'info' => __('Paste your shortcode to render desired content in the current place.', 'ht-contactform'),
                        'type' => 'input',
                        'value' => '[sample_shortcode]',
                    ]),
                    self::$field->class(),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'ratings',
                'type' => 'ratings',
                'label' => __('Ratings', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('Ratings', 'ht-contactform')]),
                    self::$field->label(['value' => __('Ratings', 'ht-contactform')]),
                    self::$field->label_position(),
                    self::$field->label_hide(),
                    self::$field->options([
                        'option_type' => 'radio',
                        'value' => [
                            [ 'label' => "Nice", 'value' => 1, 'selected' => false ],
                            [ 'label' => "Good", 'value' => 2, 'selected' => false ],
                            [ 'label' => "Very Good", 'value' => 3, 'selected' => false ],
                            [ 'label' => "Excellent", 'value' => 4, 'selected' => true ],
                            [ 'label' => "Outstanding", 'value' => 5, 'selected' => false ],
                        ]
                    ]),
                    self::$field->create([
                        'id' => 'show_text_on_hover',
                        'label' => __('Show Text', 'ht-contactform'),
                        'info' => __('Show text value on hover', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => false,
                    ]),
                    self::$field->required(),
                    self::$field->required_message(),
                    self::$field->size(),
                    self::$field->class(),
                    self::$field->message(),
                    self::$field->message_position(),
                    self::$field->name_attribute(['value' => 'ratings']),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'repeater',
                'type' => 'repeater',
                'label' => __('Repeater', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('Repeater', 'ht-contactform')]),
                    self::$field->sub_fields([
                        'value' => [
                            [
                                'id' => 'sub_field_default',
                                'type' => 'input',
                                'label' => '',
                                'name_attribute' => '',
                                'default_value' => '',
                                'placeholder' => '',
                                'required' => false,
                                'email_validation' => false,
                                'options' => [
                                    ['id' => uniqid(), 'label' => 'First Option', 'value' => 'first_option', 'selected' => false],
                                    ['id' => uniqid(), 'label' => 'Second Option', 'value' => 'second_option', 'selected' => false],
                                ],
                                'settings' => [
                                    'name_attribute' => 'sub_field_default',
                                    'label' => '',
                                    'options' => [
                                        ['id' => uniqid(), 'label' => 'First Option', 'value' => 'first_option', 'selected' => false],
                                        ['id' => uniqid(), 'label' => 'Second Option', 'value' => 'second_option', 'selected' => false],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                    self::$field->add_button_text(),
                    self::$field->remove_button_text(),
                    self::$field->row_label(),
                    self::$field->label(['value' => __('Repeater', 'ht-contactform')]),
                    self::$field->label_position(),
                    self::$field->label_hide(),
                    self::$field->class(),
                    self::$field->message(),
                    self::$field->message_position(),
                    self::$field->name_attribute(['value' => 'repeater']),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'post_select',
                'type' => 'post_select',
                'label' => __('Post Selection', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('Post Selection', 'ht-contactform')]),
                    self::$field->label(['value' => __('Select a Post', 'ht-contactform')]),
                    self::$field->label_position(),
                    self::$field->label_hide(),
                    self::$field->placeholder(['value' => __('Select a post', 'ht-contactform')]),
                    self::$field->post_type(),
                    self::$field->post_status(),
                    self::$field->posts_per_page(),
                    self::$field->searchable(['value' => true]),
                    self::$field->required(),
                    self::$field->required_message(),
                    self::$field->size(),
                    self::$field->class(),
                    self::$field->message(),
                    self::$field->message_position(),
                    self::$field->name_attribute(['value' => 'post_select']),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'section_break',
                'type' => 'section_break',
                'label' => __('Section Break', 'ht-contactform'),
                'settings' => [
                    self::$field->create([
                        'id' => 'heading',
                        'label' => __('Heading', 'ht-contactform'),
                        'type' => 'input',
                        'value' => __('Section Break', 'ht-contactform'),
                    ]),
                    self::$field->create([
                        'id' => 'description',
                        'label' => __('Description', 'ht-contactform'),
                        'type' => 'richtext',
                        'value' => __('Some description about this section break field.', 'ht-contactform'),
                    ]),
                    self::$field->create([
                        'id' => 'divider_style',
                        'label' => __('Divider Style', 'ht-contactform'),
                        'type' => 'select',
                        'value' => 'solid',
                        'options' => [
                            ['value' => 'none', 'label' => __('None', 'ht-contactform')],
                            ['value' => 'solid', 'label' => __('Solid', 'ht-contactform')],
                            ['value' => 'dashed', 'label' => __('Dashed', 'ht-contactform')],
                            ['value' => 'dotted', 'label' => __('Dotted', 'ht-contactform')],
                            ['value' => 'double', 'label' => __('Double', 'ht-contactform')],
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'divider_color',
                        'label' => __('Divider Color', 'ht-contactform'),
                        'type' => 'color',
                        'value' => '#dddddd',
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'divider_style',
                                    'value' => 'none',
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'divider_width',
                        'label' => __('Divider Width (px)', 'ht-contactform'),
                        'type' => 'number',
                        'value' => 3,
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'divider_style',
                                    'value' => 'none',
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'heading_alignment',
                        'label' => __('Alignment', 'ht-contactform'),
                        'type' => 'radio',
                        'value' => 'left',
                        'options' => [
                            ['value' => 'left', 'label' => __('Left', 'ht-contactform')],
                            ['value' => 'center', 'label' => __('Center', 'ht-contactform')],
                            ['value' => 'right', 'label' => __('Right', 'ht-contactform')],
                        ],
                    ]),
                    self::$field->class(),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'action_hook',
                'type' => 'action_hook',
                'label' => __('Action Hook', 'ht-contactform'),
                'settings' => [
                    self::$field->create([
                        'id' => 'hook_name',
                        'label' => __('Hook Name', 'ht-contactform'),
                        'info' => __('The prefix "ht_form_" will be automatically added.', 'ht-contactform'),
                        'type' => 'input',
                        'value' => 'custom_hook',
                    ]),
                    self::$field->create([
                        'id' => 'hook_instructions',
                        'label' => __('Developer Instructions', 'ht-contactform'),
                        'type' => 'html',
                        'content' => '<p style="margin-bottom: 8px;">' . __('Use this hook to add dynamic content or execute custom code at this position in the form.', 'ht-contactform') . '</p>' .
                            '<pre style="background: #f4f4f5; padding: 12px; border-radius: 4px; overflow-x: auto; font-size: 11px; line-height: 1.5;"><code>' .
                            "add_action('ht_form_{hook_name}', 'my_custom_callback', 10, 2);\n\n" .
                            "function my_custom_callback(\$field_id, \$settings) {\n" .
                            "    // Your custom code here\n" .
                            "    echo '&lt;div&gt;Custom content&lt;/div&gt;';\n" .
                            "}</code></pre>" .
                            '<p style="margin-top: 8px; font-style: italic;">' . __('Replace {hook_name} with your actual hook name.', 'ht-contactform') . '</p>',
                    ]),
                    self::$field->class(),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'color',
                'type' => 'color',
                'label' => __('Color Picker', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('Color', 'ht-contactform')]),
                    self::$field->label(['value' => __('Select Color', 'ht-contactform')]),
                    self::$field->label_position(),
                    self::$field->label_hide(),
                    self::$field->required(),
                    self::$field->required_message(),
                    self::$field->create([
                        'id' => 'default_color',
                        'label' => __('Default Color', 'ht-contactform'),
                        'type' => 'color',
                        'value' => '#000000',
                    ]),
                    self::$field->size(),
                    self::$field->class(),
                    self::$field->message(),
                    self::$field->message_position(),
                    self::$field->name_attribute(['value' => 'color']),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'nps',
                'type' => 'nps',
                'label' => __('Net Promoter Score', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('Net Promoter Score', 'ht-contactform')]),
                    self::$field->label(['value' => __('How likely are you to recommend us?', 'ht-contactform')]),
                    self::$field->label_position(),
                    self::$field->label_hide(),
                    self::$field->required(),
                    self::$field->required_message(),
                    self::$field->create([
                        'id' => 'show_labels',
                        'label' => __('Show Scale Labels', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => true,
                    ]),
                    self::$field->create([
                        'id' => 'low_label',
                        'label' => __('Low Label', 'ht-contactform'),
                        'type' => 'input',
                        'value' => __('Not at all likely', 'ht-contactform'),
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'show_labels',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'high_label',
                        'label' => __('High Label', 'ht-contactform'),
                        'type' => 'input',
                        'value' => __('Extremely likely', 'ht-contactform'),
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'show_labels',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'color_coding',
                        'label' => __('Color Coding', 'ht-contactform'),
                        'info' => __('Red (0-6 detractors), Yellow (7-8 passives), Green (9-10 promoters)', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => true,
                    ]),
                    self::$field->size(),
                    self::$field->class(),
                    self::$field->message(),
                    self::$field->message_position(),
                    self::$field->name_attribute(['value' => 'nps_score']),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'richtext',
                'type' => 'richtext',
                'label' => __('Rich Text Editor', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('Rich Text', 'ht-contactform')]),
                    self::$field->label(['value' => __('Content', 'ht-contactform')]),
                    self::$field->label_position(),
                    self::$field->label_hide(),
                    self::$field->placeholder(['value' => __('Enter your content here...', 'ht-contactform')]),
                    self::$field->required(),
                    self::$field->required_message(),
                    self::$field->create([
                        'id' => 'toolbar',
                        'label' => __('Toolbar Options', 'ht-contactform'),
                        'type' => 'select',
                        'value' => 'basic',
                        'options' => [
                            ['value' => 'minimal', 'label' => __('Minimal (Bold, Italic, Link)', 'ht-contactform')],
                            ['value' => 'basic', 'label' => __('Basic (+ Lists, Headings)', 'ht-contactform')],
                            ['value' => 'full', 'label' => __('Full (All formatting)', 'ht-contactform')],
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'min_height',
                        'label' => __('Min Height (px)', 'ht-contactform'),
                        'type' => 'number',
                        'value' => 150,
                    ]),
                    self::$field->create([
                        'id' => 'max_length',
                        'label' => __('Max Characters', 'ht-contactform'),
                        'info' => __('Leave empty for unlimited', 'ht-contactform'),
                        'type' => 'number',
                        'value' => '',
                    ]),
                    self::$field->size(),
                    self::$field->class(),
                    self::$field->message(),
                    self::$field->message_position(),
                    self::$field->name_attribute(['value' => 'richtext_content']),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'signature',
                'type' => 'signature',
                'label' => __('Signature', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('Signature', 'ht-contactform')]),
                    self::$field->label(['value' => __('Your Signature', 'ht-contactform')]),
                    self::$field->label_position(),
                    self::$field->label_hide(),
                    self::$field->required(),
                    self::$field->required_message(),
                    self::$field->create([
                        'id' => 'canvas_width',
                        'label' => __('Width', 'ht-contactform'),
                        'info' => __('Use 0 for 100% width', 'ht-contactform'),
                        'type' => 'number',
                        'value' => 0,
                    ]),
                    self::$field->create([
                        'id' => 'canvas_height',
                        'label' => __('Height (px)', 'ht-contactform'),
                        'type' => 'number',
                        'value' => 200,
                    ]),
                    self::$field->create([
                        'id' => 'pen_color',
                        'label' => __('Pen Color', 'ht-contactform'),
                        'type' => 'color',
                        'value' => '#000000',
                    ]),
                    self::$field->create([
                        'id' => 'background_color',
                        'label' => __('Background Color', 'ht-contactform'),
                        'type' => 'color',
                        'value' => '#ffffff',
                    ]),
                    self::$field->create([
                        'id' => 'pen_width',
                        'label' => __('Pen Width', 'ht-contactform'),
                        'type' => 'number',
                        'value' => 2,
                    ]),
                    self::$field->create([
                        'id' => 'show_clear_button',
                        'label' => __('Show Clear Button', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => true,
                    ]),
                    self::$field->create([
                        'id' => 'clear_button_text',
                        'label' => __('Clear Button Text', 'ht-contactform'),
                        'type' => 'input',
                        'value' => __('Clear', 'ht-contactform'),
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                ['id' => 'show_clear_button', 'value' => true, 'compare' => '=='],
                            ],
                        ],
                    ]),
                    self::$field->class(),
                    self::$field->message(),
                    self::$field->message_position(),
                    self::$field->name_attribute(['value' => 'signature']),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'chained_select',
                'type' => 'chained_select',
                'label' => __('Chained Select', 'ht-contactform'),
                'settings' => [
                    self::$field->admin_label(['value' => __('Chained Select', 'ht-contactform')]),
                    self::$field->label(['value' => __('Select Options', 'ht-contactform')]),
                    self::$field->label_position(),
                    self::$field->label_hide(),
                    self::$field->required(),
                    self::$field->required_message(),
                    self::$field->placeholder(['value' => __('Select...', 'ht-contactform')]),
                    self::$field->create([
                        'id' => 'chained_data_input',
                        'label' => __('CSV Data', 'ht-contactform'),
                        'info' => __('Upload a CSV file. First row = dropdown labels, data rows = option values. Supports unlimited columns (Year,Make,Model,Trim...)', 'ht-contactform'),
                        'type' => 'chained_data',
                        'value' => [],
                    ]),
                    self::$field->create([
                        'id' => 'layout',
                        'label' => __('Layout', 'ht-contactform'),
                        'type' => 'select',
                        'value' => 'vertical',
                        'options' => [
                            ['value' => 'vertical', 'label' => __('Stacked', 'ht-contactform')],
                            ['value' => 'horizontal', 'label' => __('Side by Side', 'ht-contactform')],
                        ],
                    ]),
                    self::$field->size(),
                    self::$field->class(),
                    self::$field->message(),
                    self::$field->message_position(),
                    self::$field->name_attribute(['value' => 'chained_select']),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'save_resume',
                'type' => 'save_resume',
                'label' => __('Save & Resume', 'ht-contactform'),
                'settings' => [
                    self::$field->create([
                        'id' => 'save_button_text',
                        'label' => __('Save Button Text', 'ht-contactform'),
                        'type' => 'input',
                        'value' => __('Save Progress', 'ht-contactform'),
                    ]),
                    self::$field->create([
                        'id' => 'save_button_style',
                        'label' => __('Button Style', 'ht-contactform'),
                        'type' => 'select',
                        'value' => 'default',
                        'options' => [
                            ['value' => 'default', 'label' => __('Default', 'ht-contactform')],
                            ['value' => 'outline', 'label' => __('Outline', 'ht-contactform')],
                            ['value' => 'subtle', 'label' => __('Subtle', 'ht-contactform')],
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'save_button_align',
                        'label' => __('Button Alignment', 'ht-contactform'),
                        'type' => 'radio',
                        'value' => 'left',
                        'options' => [
                            ['value' => 'left', 'label' => __('Left', 'ht-contactform')],
                            ['value' => 'center', 'label' => __('Center', 'ht-contactform')],
                            ['value' => 'right', 'label' => __('Right', 'ht-contactform')],
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'draft_expiry_days',
                        'label' => __('Draft Expiry (Days)', 'ht-contactform'),
                        'info' => __('Number of days before saved drafts expire. Default: 30 days.', 'ht-contactform'),
                        'type' => 'number',
                        'value' => 30,
                    ]),
                    self::$field->create([
                        'id' => 'show_email_option',
                        'label' => __('Show Email Option', 'ht-contactform'),
                        'info' => __('Allow users to email the resume link to themselves.', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => true,
                    ]),
                    self::$field->create([
                        'id' => 'email_button_text',
                        'label' => __('Email Button Text', 'ht-contactform'),
                        'type' => 'input',
                        'value' => __('Email me the link', 'ht-contactform'),
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'show_email_option',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'success_message',
                        'label' => __('Success Message', 'ht-contactform'),
                        'type' => 'input',
                        'value' => __('Your progress has been saved!', 'ht-contactform'),
                    ]),
                    self::$field->create([
                        'id' => 'copy_link_text',
                        'label' => __('Copy Link Button Text', 'ht-contactform'),
                        'type' => 'input',
                        'value' => __('Copy Resume Link', 'ht-contactform'),
                    ]),
                    self::$field->create([
                        'id' => 'resume_notice_text',
                        'label' => __('Resume Notice Text', 'ht-contactform'),
                        'info' => __('Message shown when user resumes a saved form.', 'ht-contactform'),
                        'type' => 'input',
                        'value' => __('Resuming your saved progress...', 'ht-contactform'),
                    ]),
                    self::$field->class(),
                    self::$field->enable_condition(),
                    self::$field->conditional_match(),
                    self::$field->conditional_logic(),
                ],
            ],
            [
                'id' => 'submit',
                'type' => 'submit',
                'label' => __('Submit Button', 'ht-contactform'),
                'settings' => [
                    self::$field->value([
                        'label' => __('Button Text', 'ht-contactform'),
                        'value' => __('Submit', 'ht-contactform'),
                    ]),
                    self::$field->size(),
                    self::$field->class(),
                    self::$field->create([
                        'id' => 'style',
                        'label' => __('Button Style', 'ht-contactform'),
                        'info' => __('Select a button style from the dropdown', 'ht-contactform'),
                        'type' => 'select',
                        'value' => 'default',
                        'options' => [
                            ['value' => 'default', 'label' => __('Default', 'ht-contactform')],
                            ['value' => 'red', 'label' => __('Red', 'ht-contactform')],
                            ['value' => 'green', 'label' => __('Green', 'ht-contactform')],
                            ['value' => 'orange', 'label' => __('Orange', 'ht-contactform')],
                            ['value' => 'gray', 'label' => __('Gray', 'ht-contactform')],
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'align',
                        'label' => __('Button Alignment', 'ht-contactform'),
                        'type' => 'radio',
                        'value' => 'left',
                        'options' => [
                            ['value' => 'left', 'label' => __('Left', 'ht-contactform')],
                            ['value' => 'center', 'label' => __('Center', 'ht-contactform')],
                            ['value' => 'right', 'label' => __('Right', 'ht-contactform')],
                        ],
                    ]),
                ],
            ],
        ]);
    }

    /**
     * Get available form settings
     *
     * @return array Array of form settings
     */
    public function form_settings(): array {
        return apply_filters('ht_form_settings', [
            'general' => [
                'id' => 'general',
                'label' => __('General', 'ht-contactform'),
                'settings' => [
                    self::$field->create([
                        'id' => 'enable_ajax',
                        'label' => __('Enable AJAX', 'ht-contactform'),
                        'info' => __('Enable AJAX submission for the contact form.', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => false,
                    ]),
                    self::$field->create([
                        'id' => 'store_submissions',
                        'label' => __('Store Submissions', 'ht-contactform'),
                        'info' => __('Save all form submission data to the database for later reference.', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => true,
                    ]),
                    self::$field->create([
                        'id' => 'delete_old_entries',
                        'label' => __('Delete Old Entries', 'ht-contactform'),
                        'info' => __('Delete old form submission data after a certain period of time.', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => false,
                    ]),
                    self::$field->create([
                        'id' => 'delete_old_entries_after',
                        'label' => __('Delete Old Entries After', 'ht-contactform'),
                        // 'info' => __('Delete old form submission data after a certain period of time.', 'ht-contactform'),
                        'type' => 'number',
                        'value' => 30,
                        'min' => 1,
                        'max' => 365,
                        'suffix' => ' days',
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'delete_old_entries',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ]
                    ]),
                    self::$field->create([
                        'id' => 'class',
                        'label' => __('Form Class', 'ht-contactform'),
                        'info' => __('Add a class to the contact form.', 'ht-contactform'),
                    ]),
                ]
            ],
            'spam_protection' => [
                'id' => 'spam_protection',
                'label' => __('Spam Protection', 'ht-contactform'),
                'settings' => [
                    self::$field->create([
                        'id' => 'enable_spam_protection',
                        'label' => __('Enable Anti Spam Protection', 'ht-contactform'),
                        'info' => __('Enable anti spam protection for the contact form.', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => false,
                    ]),
                    self::$field->create([
                        'id' => 'enable_minimum_time_to_submit',
                        'label' => __('Enable minimum time to submit', 'ht-contactform'),
                        'info' => __('Set a minimum amount of time a user must spend on a form before submitting.', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => false,
                    ]),
                    self::$field->create([
                        'id' => 'minimum_time_to_submit',
                        'label' => __('Minimum Time to Submit (Seconds)', 'ht-contactform'),
                        'info' => __('Set a minimum amount of time a user must spend on a form before submitting.', 'ht-contactform'),
                        'type' => 'number',
                        'value' => 2,
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enable_minimum_time_to_submit',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ]
                    ]),
                ]
            ],
            'form_restriction' => [
                'id' => 'form_restriction',
                'label' => __('Form Restriction', 'ht-contactform'),
                'settings' => [
                    self::$field->create([
                        'id' => 'enable_ip_restriction',
                        'label' => __('Enable IP Based Restriction', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => false,
                    ]),
                    self::$field->create([
                        'id' => 'restrict_ip_address',
                        'label' => __('Restrict IP Address', 'ht-contactform'),
                        'info' => __('Add multiple IP addresses separated by commas to restrict submission.', 'ht-contactform'),
                        'placeholder' => '192.168.1.1, 192.168.1.2',
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enable_ip_restriction',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ]
                    ]),
                    self::$field->create([
                        'id' => 'restrict_ip_message',
                        'label' => __('Restrict IP Address Error Message', 'ht-contactform'),
                        'value' => __('Sorry! You can\'t submit a form from your IP address.', 'ht-contactform'),
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enable_ip_restriction',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ]
                    ]),
                    self::$field->create([
                        'id' => 'enable_country_restriction',
                        'label' => __('Enable Country Based Restriction', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => false,
                    ]),
                    self::$field->create([
                        'id' => 'restrict_country',
                        'label' => __('Restrict Country', 'ht-contactform'),
                        'info' => __('Select countries to restrict submission.', 'ht-contactform'),
                        'type' => 'select',
                        'multiple' => true,
                        'searchable' => true,
                        'value' => [],
                        'options' => self::$countries->get_all(),
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enable_country_restriction',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ]
                    ]),
                    self::$field->create([
                        'id' => 'restrict_country_message',
                        'label' => __('Restrict Country Error Message', 'ht-contactform'),
                        'value' => __('Sorry! You can\'t submit a form the country you are residing.', 'ht-contactform'),
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enable_country_restriction',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ]
                    ]),
                ]
            ],
            'notification' => [
                'id' => 'notification',
                'label' => __('Notification', 'ht-contactform'),
                'settings' => [
                    self::$field->create([
                        'id' => 'enable_notification',
                        'label' => __('Enable Notification', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => true,
                    ]),
                    self::$field->create([
                        'id' => 'form_send_to_email',
                        'label' => __('Send To Email', 'ht-contactform'),
                        'info' => __('Enter the email address to receive form entry notifications. For multiple notifications, separate email addresses with a comma and space.', 'ht-contactform'),
                        'value' => '{admin_email}',
                        'support' => ['tags'],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enable_notification',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ]
                    ]),
                    self::$field->create([
                        'id' => 'form_subject',
                        'label' => __('Email Subject', 'ht-contactform'),
                        'value' => __('New Form Entry - {form_title}', 'ht-contactform'),
                        'support' => ['tags'],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enable_notification',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ]
                    ]),
                    self::$field->create([
                        'id' => 'form_name',
                        'label' => __('Form Name', 'ht-contactform'),
                        'value' => get_bloginfo('name'),
                        'support' => ['tags'],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enable_notification',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ]
                    ]),
                    self::$field->create([
                        'id' => 'form_email',
                        'label' => __('Form Email', 'ht-contactform'),
                        'info' => __('Notifications can only use 1 From Email. Please do not enter multiple addresses.', 'ht-contactform'),
                        'value' => '{admin_email}',
                        'support' => ['tags'],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enable_notification',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ]
                    ]),
                    self::$field->create([
                        'id' => 'form_reply_to',
                        'label' => __('Reply To', 'ht-contactform'),
                        'info' => __('Enter the email address you would like to be used as the reply to address for the notification email.', 'ht-contactform'),
                        'support' => ['tags'],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enable_notification',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ]
                    ]),
                    self::$field->create([
                        'id' => 'form_email_body',
                        'label' => __('Email Body', 'ht-contactform'),
                        'type' => 'richtext',
                        'value' => __('{all_fields}', 'ht-contactform'),
                        'info' => __('For every tag use new line.', 'ht-contactform'),
                        'support' => ['tags'],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enable_notification',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ]
                    ]),
                    self::$field->create([
                        'id' => 'template',
                        'label' => __('Template', 'ht-contactform'),
                        'type' => 'select',
                        'value' => '',
                        'options' => [
                            ["value" => "", "label" => __('Default', 'ht-contactform')],
                            ["value" => "1", "label" => __('Template 1', 'ht-contactform')],
                            ["value" => "2", "label" => __('Template 2', 'ht-contactform')],
                            ["value" => "3", "label" => __('Template 3', 'ht-contactform')],
                            ["value" => "4", "label" => __('Template 4', 'ht-contactform')],
                            ["value" => "5", "label" => __('Template 5', 'ht-contactform')],
                        ],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enable_notification',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ]
                    ]),
                ]
            ],
            'confirmation' => [
                'id' => 'confirmation',
                'label' => __('Confirmation', 'ht-contactform'),
                'settings' => [
                    self::$field->create([
                        'id' => 'confirmation_type',
                        'label' => __('Confirmation Type', 'ht-contactform'),
                        'type' => 'select',
                        'value' => 'message',
                        'options' => [
                            [
                                'value' => 'message',
                                'label' => __('Message', 'ht-contactform'),
                            ],
                            [
                                'value' => 'redirect',
                                'label' => __('Redirect', 'ht-contactform'),
                            ],
                            [
                                'value' => 'page',
                                'label' => __('Show Page', 'ht-contactform'),
                            ],
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'confirmation_message',
                        'label' => __('Confirmation Message', 'ht-contactform'),
                        'type' => 'textarea',
                        'value' => __('Thanks for contacting us! We will be in touch with you shortly.', 'ht-contactform'),
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'confirmation_type',
                                    'value' => 'message',
                                    'compare' => '==',
                                ]
                            ]
                        ]
                    ]),
                    self::$field->create([
                        'id' => 'confirmation_page',
                        'label' => __('Confirmation Page', 'ht-contactform'),
                        'type' => 'select',
                        'options' => $this->get_pages(),
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'confirmation_type',
                                    'value' => 'page',
                                    'compare' => '==',
                                ]
                            ]
                        ]
                    ]),
                    self::$field->create([
                        'id' => 'confirmation_redirect',
                        'label' => __('Confirmation Redirect', 'ht-contactform'),
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'confirmation_type',
                                    'value' => 'redirect',
                                    'compare' => '==',
                                ]
                            ]
                        ]
                    ]),
                    self::$field->create([
                        'id' => 'confirmation_new_tab',
                        'label' => __('Open confirmation in new tab', 'ht-contactform'),
                        'type' => 'switch',
                        'dependency' => [
                            'relation' => 'OR',
                            'rules' => [
                                [
                                    'id' => 'confirmation_type',
                                    'value' => 'page',
                                    'compare' => '==',
                                ],
                                [
                                    'id' => 'confirmation_type',
                                    'value' => 'redirect',
                                    'compare' => '==',
                                ]
                            ]
                        ]
                    ]),
                ]
            ],
            'styler' => [
                'id' => 'styler',
                'label' => __('Form Styler', 'ht-contactform'),
                'settings' => self::$styler->get_settings(),
            ],
        ]);
    }

    public function form_editor_integrations(): array {
        return apply_filters('ht_form_editor_integrations', [
            'webhook' => [
                'id' => 'webhook',
                'label' => __('Webhook', 'ht-contactform'),
                'value' => [
                    'enabled' => false,
                    'name' => '',
                    'url' => '',
                    'method' => 'POST',
                    'header_type' => 'no_headers',
                    'header' => '',
                    'body_type' => 'json',
                    'body' => '',
                ],
                'fields' => [
                    self::$field->create([
                        'id' => 'enabled',
                        'label' => __('Enable Webhook', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => true,
                        'callback' => 'rest_sanitize_boolean',
                    ]),
                    self::$field->create([
                        'id' => 'name',
                        'label' => __('Integration Name', 'ht-contactform'),
                        'info' => __('Enter a unique name for the webhook to identify it.', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                    ]),
                    self::$field->create([
                        'id' => 'url',
                        'label' => __('Webhook URL', 'ht-contactform'),
                        'info' => __('Enter the webhook request URL.', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                        ]),
                    self::$field->create([
                        'id' => 'method',
                        'label' => __('Method', 'ht-contactform'),
                        'info' => __('Select the HTTP method for the webhook request.', 'ht-contactform'),
                        'type' => 'select',
                        'value' => 'POST',
                        'callback' => 'sanitize_text_field',
                        'options' => [
                            [
                                'value' => 'POST',
                                'label' => __('POST', 'ht-contactform'),
                            ],
                            // [
                            //     'value' => 'GET',
                            //     'label' => __('GET', 'ht-contactform'),
                            // ],
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'header_type',
                        'label' => __('Request Header', 'ht-contactform'),
                        'info' => __('Select the header for the webhook request.', 'ht-contactform'),
                        'type' => 'radio',
                        'value' => 'no_headers',
                        'callback' => 'sanitize_text_field',
                        'options' => [
                            [
                                'value' => 'no_headers',
                                'label' => __('No Headers', 'ht-contactform'),
                            ],
                            [
                                'value' => 'with_headers',
                                'label' => __('With Headers', 'ht-contactform'),
                            ],
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'headers',
                        'label' => __('Headers', 'ht-contactform'),
                        'info' => __('Setup headers to send with webhook request.', 'ht-contactform'),
                        'type' => 'custom_repeater',
                        'required' => true,
                        'value' => [],
                        'fields' => [
                            self::$field->create([
                                'id' => 'key',
                                'placeholder' => __('Key', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                            ]),
                            self::$field->create([
                                'id' => 'value',
                                'placeholder' => __('Value', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                            ]),
                        ],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'header_type',
                                    'value' => 'with_headers',
                                    'compare' => '==',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'body_fields',
                        'label' => __('Request Body', 'ht-contactform'),
                        'info' => __('Select if all fields or selected fields to send with webhook request.', 'ht-contactform'),
                        'type' => 'radio',
                        'required' => true,
                        'value' => 'all_fields',
                        'callback' => 'sanitize_text_field',
                        'options' => [
                            [
                                'value' => 'all_fields',
                                'label' => __('All Fields', 'ht-contactform'),
                            ],
                            [
                                'value' => 'selected_fields',
                                'label' => __('Selected Fields', 'ht-contactform'),
                            ],
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'fields',
                        'label' => __('Fields', 'ht-contactform'),
                        'info' => __('Select the fields to send with webhook request.', 'ht-contactform'),
                        'type' => 'custom_repeater',
                        'required' => true,
                        'value' => [],
                        'fields' => [
                            self::$field->create([
                                'id' => 'key',
                                'placeholder' => __('Field Name', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                            ]),
                            self::$field->create([
                                'id' => 'value',
                                'placeholder' => __('Type or select smart tags.', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                                'support' => ['tags'],
                            ]),
                        ],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'body_fields',
                                    'value' => 'selected_fields',
                                    'compare' => '==',
                                ]
                            ]
                        ],
                    ]),
                ]
            ],
            'mailchimp' => [
                'id' => 'mailchimp',
                'label' => __('Mailchimp', 'ht-contactform'),
                'value' => [
                    'enabled' => false,
                    'name' => '',
                    'list_id' => '',
                    'merge_fields' => [],
                    'tags' => [],
                    'double_opt_in' => false,
                    'vip' => false,
                    'resubscribe' => false,
                    'note' => '',
                ],
                'fields' => [
                    self::$field->create([
                        'id' => 'enabled',
                        'label' => __('Enable Mailchimp', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => true,
                        'callback' => 'rest_sanitize_boolean',
                    ]),
                    self::$field->create([
                        'id' => 'name',
                        'label' => __('Integration Name', 'ht-contactform'),
                        'info' => __('Enter a unique name for the mailchimp to identify it.', 'ht-contactform'),
                        'value' => __('Mailchimp Integration Feed', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                    ]),
                    self::$field->create([
                        'id' => 'list_id',
                        'label' => __('Mailchimp List', 'ht-contactform'),
                        'info' => __('Select the mailchimp list to which the form data will be sent.', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                        "type" => "select",
                        "options" => [],
                    ]),
                    self::$field->create([
                        'id' => 'merge_fields',
                        'label' => __('Map Fields', 'ht-contactform'),
                        'info' => __('Select the fields to map with mailchimp list.', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                        "type" => "custom",
                        "value" => [],
                        "fields" => [
                            self::$field->create([
                                'id' => 'value',
                                'placeholder' => __('Type or select smart tags.', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                                'support' => ['tags'],
                            ]),
                        ],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'list_id',
                                    'value' => '',
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'tags',
                        'label' => __('Tags', 'ht-contactform'),
                        'info' => __('Select the tags to associate with your Mailchimp contacts.', 'ht-contactform'),
                        "type" => "select",
                        "multiple" => true,
                        "value" => [],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'list_id',
                                    'value' => '',
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'resubscribe',
                        'label' => __('Resubscribe', 'ht-contactform'),
                        'info' => __('Enable this option to automatically resubscribe inactive or previously unsubscribed contacts. Use with caution.', 'ht-contactform'),
                        "type" => "switch",
                        "value" => false,
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'list_id',
                                    'value' => '',
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'double_opt_in',
                        'label' => __('Double Opt-in', 'ht-contactform'),
                        'info' => __('When enabled, Mailchimp will send a confirmation email to the user and will only add them to your list after they confirm their subscription.', 'ht-contactform'),
                        "type" => "switch",
                        "value" => false,
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'list_id',
                                    'value' => '',
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'vip',
                        'label' => __('VIP', 'ht-contactform'),
                        'info' => __('When enabled, This contact will be marked as VIP.', 'ht-contactform'),
                        "type" => "switch",
                        "value" => false,
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'list_id',
                                    'value' => '',
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                ]
            ],
            'slack' => [
                'id' => 'slack',
                'label' => __('Slack', 'ht-contactform'),
                'value' => [
                    'enabled' => false,
                    'name' => '',
                    'url' => '',
                ],
                'fields' => [
                    self::$field->create([
                        'id' => 'enabled',
                        'label' => __('Enable Slack', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => true,
                        'callback' => 'rest_sanitize_boolean',
                    ]),
                    self::$field->create([
                        'id' => 'name',
                        'label' => __('Integration Name', 'ht-contactform'),
                        'info' => __('Enter a unique name for the slack to identify it.', 'ht-contactform'),
                        'value' => __('Slack Integration Feed', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                    ]),
                    self::$field->create([
                        'id' => 'url',
                        'label' => __('Webhook URL', 'ht-contactform'),
                        'info' => __('Enter the slack incoming webhook URL.', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                    ]),
                    self::$field->create([
                        'id' => 'fields',
                        'label' => __('Fields', 'ht-contactform'),
                        'info' => __('Select the fields to send with discord request.', 'ht-contactform'),
                        'type' => 'field_checkbox',
                        'required' => true,
                        'value' => [],
                        'options' => [],
                    ]),
                    self::$field->create([
                        'id' => 'footer',
                        'label' => __('Slack Footer message', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                    ]),
                ]
            ],
            'discord' => [
                'id' => 'discord',
                'label' => __('Discord', 'ht-contactform'),
                'value' => [
                    'enabled' => false,
                    'name' => '',
                    'url' => '',
                ],
                'fields' => [
                    self::$field->create([
                        'id' => 'enabled',
                        'label' => __('Enable Discord', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => true,
                        'callback' => 'rest_sanitize_boolean',
                    ]),
                    self::$field->create([
                        'id' => 'name',
                        'label' => __('Integration Name', 'ht-contactform'),
                        'info' => __('Enter a unique name for the discord to identify it.', 'ht-contactform'),
                        'value' => __('Discord Integration Feed', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                    ]),
                    self::$field->create([
                        'id' => 'url',
                        'label' => __('Webhook URL', 'ht-contactform'),
                        'info' => __('Enter the discord incoming webhook URL.', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                    ]),
                    self::$field->create([
                        'id' => 'fields',
                        'label' => __('Fields', 'ht-contactform'),
                        'info' => __('Select the fields to send with discord request.', 'ht-contactform'),
                        'type' => 'field_checkbox',
                        'required' => true,
                        'value' => [],
                        'options' => [],
                    ]),
                    self::$field->create([
                        'id' => 'footer',
                        'label' => __('Discord Footer message', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                    ]),
                ]
            ],
            'activecampaign' => [
                'id' => 'activecampaign',
                'label' => __('ActiveCampaign', 'ht-contactform'),
                'value' => [
                    'enabled' => false,
                    'name' => '',
                    'list_id' => '',
                    'merge_fields' => [],
                    'tags' => [],
                    'double_opt_in' => '',
                    'note' => '',
                ],
                'fields' => [
                    self::$field->create([
                        'id' => 'enabled',
                        'label' => __('Enable ActiveCampaign', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => true,
                        'callback' => 'rest_sanitize_boolean',
                    ]),
                    self::$field->create([
                        'id' => 'name',
                        'label' => __('Integration Name', 'ht-contactform'),
                        'info' => __('Enter a unique name for the activecampaign to identify it.', 'ht-contactform'),
                        'value' => __('ActiveCampaign Integration Feed', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                    ]),
                    self::$field->create([
                        'id' => 'list_id',
                        'label' => __('ActiveCampaign List', 'ht-contactform'),
                        'info' => __('Select the activecampaign list to which the form data will be sent.', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                        "type" => "select",
                        "options" => [],
                    ]),
                    self::$field->create([
                        'id' => 'merge_fields',
                        'label' => __('Map Fields', 'ht-contactform'),
                        'info' => __('Select the fields to map with activecampaign list.', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                        "type" => "custom",
                        "value" => [],
                        "options" => [
                            [
                                'name' => __('Email Address', 'ht-contactform'),
                                'tag' => 'email',
                                'required' => true,
                            ],
                            [
                                'name' => __('First Name', 'ht-contactform'),
                                'tag' => 'firstName',
                            ],
                            [
                                'name' => __('Last Name', 'ht-contactform'),
                                'tag' => 'lastName',
                            ],
                            [
                                'name' => __('Phone Number', 'ht-contactform'),
                                'tag' => 'phone',
                            ],
                        ],
                        "fields" => [
                            self::$field->create([
                                'id' => 'value',
                                'placeholder' => __('Type or select smart tags.', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                                'support' => ['tags'],
                            ]),
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'tags',
                        'label' => __('Tags', 'ht-contactform'),
                        'info' => __('Select the tags to associate with your ActiveCampaign contacts.', 'ht-contactform'),
                        "type" => "select",
                        "multiple" => true,
                        "value" => [],
                        "options" => [],
                    ]),
                    self::$field->create([
                        'id' => 'note',
                        'label' => __('Note', 'ht-contactform'),
                        'info' => __('Enter any additional notes or instructions for the integration.', 'ht-contactform'),
                        'callback' => 'sanitize_textarea_field',
                        "type" => "textarea",
                    ]),
                ]
            ],
            'mailerlite' => [
                'id' => 'mailerlite',
                'label' => __('MailerLite', 'ht-contactform'),
                'value' => [
                    'enabled' => false,
                    'name' => '',
                    'group' => '',
                    'merge_fields' => [],
                ],
                'fields' => [
                    self::$field->create([
                        'id' => 'enabled',
                        'label' => __('Enable MailerLite', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => true,
                        'callback' => 'rest_sanitize_boolean',
                    ]),
                    self::$field->create([
                        'id' => 'name',
                        'label' => __('Integration Name', 'ht-contactform'),
                        'info' => __('Enter a unique name for the mailerlite to identify it.', 'ht-contactform'),
                        'value' => __('MailerLite Integration Feed', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                    ]),
                    self::$field->create([
                        'id' => 'group',
                        'label' => __('MailerLite Group', 'ht-contactform'),
                        'info' => __('Select the mailerlite group to which the form data will be sent.', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                        "type" => "select",
                        "options" => [],
                    ]),
                    self::$field->create([
                        'id' => 'merge_fields',
                        'label' => __('Map Fields', 'ht-contactform'),
                        'info' => __('Select the fields to map with mailerlite group.', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                        "type" => "custom",
                        "value" => [],
                        "options" => [
                            [
                                'name' => __('Email Address', 'ht-contactform'),
                                'key' => 'email_address',
                                'required' => true,
                            ],
                        ],
                        "fields" => [
                            self::$field->create([
                                'id' => 'value',
                                'placeholder' => __('Type or select smart tags', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                                'support' => ['tags'],
                            ]),
                        ],
                    ]),
                ]
            ],
            'zapier' => [
                'id' => 'zapier',
                'label' => __('Zapier', 'ht-contactform'),
                'value' => [
                    'enabled' => false,
                    'name' => '',
                    'url' => '',
                ],
                'fields' => [
                    self::$field->create([
                        'id' => 'enabled',
                        'label' => __('Enable Zapier', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => true,
                        'callback' => 'rest_sanitize_boolean',
                    ]),
                    self::$field->create([
                        'id' => 'name',
                        'label' => __('Integration Name', 'ht-contactform'),
                        'info' => __('Enter a unique name for the zapier to identify it.', 'ht-contactform'),
                        'value' => __('Zapier Integration Feed', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                    ]),
                    self::$field->create([
                        'id' => 'url',
                        'label' => __('Webhook URL', 'ht-contactform'),
                        'info' => __('Enter the zapier webhook URL.', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                    ]),
                    self::$field->create([
                        'id' => 'fields',
                        'label' => __('Fields', 'ht-contactform'),
                        'info' => __('Select the fields to send with zapier request.', 'ht-contactform'),
                        'type' => 'field_checkbox',
                        'required' => true,
                        'value' => [],
                        'options' => [],
                    ]),
                ]
            ],
            'supportgenix' => [
                'id' => 'supportgenix',
                'label' => __('Support Genix', 'ht-contactform'),
                'value' => [
                    'enabled' => false,
                    'name' => '',
                    'ticket_subject' => '',
                    'ticket_description' => '',
                    'ticket_attachments' => '',
                    'ticket_priority' => '',
                    'ticket_email' => '',
                    'ticket_f_name' => '',
                    'ticket_l_name' => '',
                    'ticket_customer_fields' => [],
                ],
                'fields' => [
                    self::$field->create([
                        'id' => 'enabled',
                        'label' => __('Enable Support Genix', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => true,
                        'callback' => 'rest_sanitize_boolean',
                    ]),
                    self::$field->create([
                        'id' => 'name',
                        'label' => __('Integration Name', 'ht-contactform'),
                        'info' => __('Enter a unique name for you to identify it later.', 'ht-contactform'),
                        'placeholder' => __('Ex: Support Genix Ticket', 'ht-contactform'),
                        'value' => __('Support Genix Ticket', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                    ]),
                    self::$field->create([
                        'id' => 'ticket_subject',
                        'label' => __('Ticket Title / Subject', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'placeholder' => __('Select a field or type custom value', 'ht-contactform'),
                        'required' => true,
                        'support' => ['tags']
                    ]),
                    self::$field->create([
                        'id' => 'ticket_description',
                        'label' => __('Ticket Content', 'ht-contactform'),
                        'placeholder' => __('Select a field or type custom value. Ex: {input.message}', 'ht-contactform'),
                        'type' => 'textarea',
                        'required' => true,
                        'callback' => 'sanitize_textarea_field',
                        'support' => ['tags']
                    ]),
                    self::$field->create([
                        'id' => 'ticket_attachments',
                        'label' => __('Ticket Attachments', 'ht-contactform'),
                        'placeholder' => __('Select a field or type custom value', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'support' => ['tags']
                    ]),
                    // self::$field->create([
                    //     'id' => 'ticket_custom_fields__priority',
                    //     'label' => __('Ticket Priority', 'ht-contactform'),
                    //     'placeholder' => __('Ex: High', 'ht-contactform'),
                    //     'callback' => 'sanitize_text_field',
                    // ]),
                    self::$field->create([
                        'id' => 'custom_fields',
                        'label' => __('Custom Fields', 'ht-contactform'),
                        'desc' => __('Please map you ticket custom fields data with this form.', 'ht-contactform'),
                        'type' => 'custom_fields',
                        'value' => [],
                        'options' => $this->get_support_genix_custom_fields(),
                        'fields' => [
                            self::$field->create([
                                'id' => 'value',
                                'callback' => 'sanitize_text_field',
                                'support' => ['tags']
                            ]),
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'customer_headings',
                        'label' => __('Customer Information', 'ht-contactform'),
                        'desc' => __('Configure how customer details are captured for support tickets. When users are logged in, their profile information will be automatically populated. For guest users submitting tickets, you can specify which customer fields to collect.', 'ht-contactform'),
                        'type' => 'heading',
                    ]),
                    self::$field->create([
                        'id' => 'user_email',
                        'label' => __('Email Address', 'ht-contactform'),
                        'placeholder' => __('Select a field', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                        'support' => ['tags']
                    ]),
                    self::$field->create([
                        'id' => 'user_first_name',
                        'label' => __('First Name', 'ht-contactform'),
                        'placeholder' => __('Select a field or type custom value', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                        'support' => ['tags']
                    ]),
                    self::$field->create([
                        'id' => 'user_last_name',
                        'label' => __('Last Name', 'ht-contactform'),
                        'placeholder' => __('Select a field or type custom value', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'support' => ['tags']
                    ]),
                    self::$field->create([
                        'id' => 'ticket_customer_fields',
                        'label' => __('Additional Customer Information', 'ht-contactform'),
                        'desc' => __('Map your HT Contact Forms fields to their corresponding Support Genix fields to collect additional customer information beyond name and email.', 'ht-contactform'),
                        'type' => 'custom_repeater',
                        'value' => [],
                        'fields' => [
                            self::$field->create([
                                'id' => 'key',
                                'placeholder' => __('Field Name', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                            ]),
                            self::$field->create([
                                'id' => 'value',
                                'placeholder' => __('Field Value', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                                'support' => ['tags']
                            ]),
                        ],
                    ]),
                ]
            ],
            'constantcontact' => [
                'id' => 'constantcontact',
                'label' => __('Constant Contact', 'ht-contactform'),
                'value' => [
                    'enabled' => true,
                    'name' => '',
                    'list_id' => '',
                ],
                'fields' => [
                    self::$field->create([
                        'id' => 'enabled',
                        'label' => __('Enable Constant Contact', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => true,
                        'callback' => 'rest_sanitize_boolean',
                    ]),
                    self::$field->create([
                        'id' => 'name',
                        'label' => __('Integration Name', 'ht-contactform'),
                        'info' => __('Enter a unique name for the you to identify it.', 'ht-contactform'),
                        'value' => __('Constant Contact Integration Feed', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                    ]),
                    self::$field->create([
                        'id' => 'list_id',
                        'label' => __('Constant Contact List', 'ht-contactform'),
                        'info' => __('Select the constant contact list you like to add your contact to.', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                        "type" => "select",
                        "options" => [],
                    ]),
                    self::$field->create([
                        'id' => 'tags',
                        'label' => __('Constant Contact Tags', 'ht-contactform'),
                        'info' => __('Select the constant contact tags you like to add your contact to.', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                        "type" => "multiselect",
                        'value' => [],
                        "options" => [],
                    ]),
                    self::$field->create([
                        'id' => 'fields',
                        'label' => __('Constant Contact Fields', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        "type" => "map_fields",
                        "options" => [],
                        'fields' => [
                            self::$field->create([
                                'id' => 'email_address',
                                'label' => __('Email Address', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                                'type' => 'select',
                                'required' => true,
                                'support' => ['tags'],
                            ]),
                            self::$field->create([
                                'id' => 'permission_to_send',
                                'label' => __('Permission To Send', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                                'type' => 'select',
                                'required' => true,
                                'options' => [
                                    [
                                        'value' => 'explicit',
                                        'label' => __('Explicit', 'ht-contactform'),
                                    ],
                                    [
                                        'value' => 'implicit',
                                        'label' => __('Implicit', 'ht-contactform'),
                                    ],
                                    [
                                        'value' => 'not_set',
                                        'label' => __('Not Set', 'ht-contactform'),
                                    ],
                                    [
                                        'value' => 'pending_confirmation',
                                        'label' => __('Pending Confirmation', 'ht-contactform'),
                                    ],
                                    [
                                        'value' => 'temp_hold',
                                        'label' => __('Temporary Hold', 'ht-contactform'),
                                    ],
                                    [
                                        'value' => 'unsubscribed',
                                        'label' => __('Unsubscribed', 'ht-contactform'),
                                    ],
                                ]
                            ]),
                            self::$field->create([
                                'id'   => 'first_name',
                                'label' => __('First Name', 'ht-contactform'),
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'last_name',
                                'label' => __('Last Name', 'ht-contactform'),
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'job_title',
                                'label' => __('Job Title', 'ht-contactform'),
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'company_name',
                                'label' => __('Company Name', 'ht-contactform'),
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'birthday_month',
                                'label' => __('Birthday Month', 'ht-contactform'),
                                'tips' => __('The month value for the contact\'s birthday. Valid values are from 1 through 12.', 'ht-contactform'),
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'birthday_day',
                                'label' => __('Birth Day', 'ht-contactform'),
                                'tips' => __('The day value for the contact\'s birthday. Valid values are from 1 through 31.', 'ht-contactform'),
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'anniversary',
                                'label' => __('Anniversary', 'ht-contactform'),
                                'tips' => __('this value could be the date when the contact first became a customer of an organization in Constant Contact. Valid date formats are MM/DD/YYYY, DD/MM/YYYY, YYYY-MM-DD.', 'ht-contactform'),
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'home_phone',
                                'label' => __('Home Phone Number', 'ht-contactform'),
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'work_phone',
                                'label' => __('Work Phone Number', 'ht-contactform'),
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'mobile_phone',
                                'label' => __('Mobile Phone Number', 'ht-contactform'),
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'home_address_street',
                                'label' => __('Home Street Address', 'ht-contactform'),
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'home_address_city',
                                'label' => __('Home City Address', 'ht-contactform'),
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'home_address_state',
                                'label' => __('Home State', 'ht-contactform'),
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'home_address_postal_code',
                                'label' => __('Home Postal Code', 'ht-contactform'),
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'home_address_country',
                                'label' => __('Home Country', 'ht-contactform'),
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'work_address_street',
                                'label' => __('Work Street Address', 'ht-contactform'),
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'work_address_city',
                                'label' => __('Work City Address', 'ht-contactform'),
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'work_address_state',
                                'label' => __('Work State', 'ht-contactform'),
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'work_address_postal_code',
                                'label' => __('Work Postal Code', 'ht-contactform'),
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'work_address_country',
                                'label' => __('Work Country', 'ht-contactform'),
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'other_address_street',
                                'label' => __('Other Street Address', 'ht-contactform'),
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'other_address_city',
                                'label' => __('Other City Address', 'ht-contactform'),
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'other_address_state',
                                'label' => __('Other State', 'ht-contactform'),
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'other_address_postal_code',
                                'label' => __('Other Postal Code', 'ht-contactform'),
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'other_address_country',
                                'label' => __('Other Country', 'ht-contactform'),
                                'support' => ['tags']
                            ])
                        ]
                    ]),
                    self::$field->create([
                        'id' => 'custom_fields',
                        'require_list' => false,
                        'label' => __('Custom Fields', 'ht-contactform'),
                        'info' => __('Select which Fluent Forms fields pair with their respective Constant Contact fields. Custom Date Fields supports only MM/DD/YYYY format', 'ht-contactform'),
                        'type' => 'select',
                        'options' => [],
                        'support' => ['tags'],
                        'fields' => [
                            self::$field->create([
                                'id'   => 'custom_field_id',
                                'label' => __('Custom Field ID', 'ht-contactform'),
                                'support' => ['tags']
                            ]),
                        ]
                    ]),
                ]
            ],
            'brevo' => [
                'id' => 'brevo',
                'label' => __('Brevo', 'ht-contactform'),
                'value' => [
                    'enabled' => true,
                    'name' => 'Brevo Integration Feed',
                    'list_id' => '',
                ],
                'fields' => [
                    self::$field->create([
                        'id' => 'enabled',
                        'label' => __('Enable Brevo', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => true,
                        'callback' => 'rest_sanitize_boolean',
                    ]),
                    self::$field->create([
                        'id' => 'name',
                        'label' => __('Integration Name', 'ht-contactform'),
                        'info' => __('Enter a unique name for the you to identify it.', 'ht-contactform'),
                        'value' => __('Brevo Integration Feed', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                    ]),
                    self::$field->create([
                        'id' => 'list_id',
                        'label' => __('Brevo List', 'ht-contactform'),
                        'info' => __('Select the brevo list you like to add your contact to.', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                        "type" => "select",
                        "options" => [],
                    ]),
                    self::$field->create([
                        'id' => 'fields',
                        'label' => __('Brevo Fields', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        "type" => "map_fields",
                        "options" => [],
                        'fields' => [
                            self::$field->create([
                                'id' => 'email',
                                'label' => __('Email Address', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                                'type' => 'select',
                                'required' => true,
                                'support' => ['tags'],
                            ]),
                            self::$field->create([
                                'id'   => 'FIRSTNAME',
                                'label' => __('First Name', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'LASTNAME',
                                'label' => __('Last Name', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'SMS',
                                'label' => __('SMS', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'EXT_ID',
                                'label' => __('External ID', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'LANDLINE_NUMBER',
                                'label' => __('Landline Number', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'CONTACT_TIMEZONE',
                                'label' => __('Contact Timezone', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'JOB_TITLE',
                                'label' => __('Job Title', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                                'support' => ['tags']
                            ]),
                            self::$field->create([
                                'id'   => 'LINKEDIN',
                                'label' => __('LinkedIn', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                                'support' => ['tags']
                            ]),
                        ]
                    ]),
                ]
            ],
            'insightly' => Insightly::get_instance()->get_configs(),
            'onepagecrm' => OnepageCRM::get_instance()->get_configs(),
            'getresponse' => [
                'id' => 'getresponse',
                'label' => __('GetResponse', 'ht-contactform'),
                'value' => [
                    'enabled' => false,
                    'name' => '',
                    'campaign_id' => '',
                    'merge_fields' => [],
                    'day_of_cycle' => '',
                    'tags' => [],
                ],
                'fields' => [
                    self::$field->create([
                        'id' => 'enabled',
                        'label' => __('Enable GetResponse', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => true,
                        'callback' => 'rest_sanitize_boolean',
                    ]),
                    self::$field->create([
                        'id' => 'name',
                        'label' => __('Integration Name', 'ht-contactform'),
                        'info' => __('Enter a unique name for the GetResponse integration to identify it.', 'ht-contactform'),
                        'value' => __('GetResponse Integration Feed', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                    ]),
                    self::$field->create([
                        'id' => 'campaign_id',
                        'label' => __('GetResponse Campaign', 'ht-contactform'),
                        'info' => __('Select the GetResponse campaign (list) to which contacts will be added.', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                        "type" => "select",
                        "options" => [],
                    ]),
                    self::$field->create([
                        'id' => 'merge_fields',
                        'label' => __('Map Fields', 'ht-contactform'),
                        'info' => __('Map your form fields to GetResponse contact fields.', 'ht-contactform'),
                        'required' => true,
                        "type" => "custom",
                        "value" => [],
                        "fields" => [
                            self::$field->create([
                                'id' => 'value',
                                'placeholder' => __('Type or select smart tags.', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                                'support' => ['tags'],
                            ]),
                        ],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enabled',
                                    'value' => true,
                                    'compare' => '==',
                                ],
                                [
                                    'id' => 'campaign_id',
                                    'value' => '',
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'day_of_cycle',
                        'label' => __('Day of Cycle', 'ht-contactform'),
                        'info' => __('Set the autoresponder day of cycle for the contact (0-1000, optional).', 'ht-contactform'),
                        'callback' => 'absint',
                        "type" => "number",
                        "value" => '',
                        "min" => 0,
                        "max" => 1000,
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enabled',
                                    'value' => true,
                                    'compare' => '==',
                                ],
                                [
                                    'id' => 'campaign_id',
                                    'value' => '',
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'tags',
                        'label' => __('Tags', 'ht-contactform'),
                        'info' => __('Select tags to apply to contacts added via this integration.', 'ht-contactform'),
                        "type" => "multiselect",
                        "multiple" => true,
                        "options" => [],
                        "value" => [],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enabled',
                                    'value' => true,
                                    'compare' => '==',
                                ],
                                [
                                    'id' => 'campaign_id',
                                    'value' => '',
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                ]
            ],
            'drip' => [
                'id' => 'drip',
                'label' => __('Drip', 'ht-contactform'),
                'value' => [
                    'enabled' => false,
                    'name' => '',
                    'account_id' => '',
                    'merge_fields' => [],
                    'tags' => '',
                ],
                'fields' => [
                    self::$field->create([
                        'id' => 'enabled',
                        'label' => __('Enable Drip', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => true,
                        'callback' => 'rest_sanitize_boolean',
                    ]),
                    self::$field->create([
                        'id' => 'name',
                        'label' => __('Integration Name', 'ht-contactform'),
                        'info' => __('Enter a unique name for the Drip integration to identify it.', 'ht-contactform'),
                        'value' => __('Drip Integration Feed', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                    ]),
                    self::$field->create([
                        'id' => 'account_id',
                        'label' => __('Drip Account', 'ht-contactform'),
                        'info' => __('Select the Drip account to use.', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                        "type" => "select",
                        "options" => [],
                    ]),
                    self::$field->create([
                        'id' => 'merge_fields',
                        'label' => __('Map Fields', 'ht-contactform'),
                        'info' => __('Map your form fields to Drip subscriber fields.', 'ht-contactform'),
                        'required' => true,
                        "type" => "custom",
                        "value" => [],
                        "fields" => [
                            self::$field->create([
                                'id' => 'value',
                                'placeholder' => __('Type or select smart tags.', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                                'support' => ['tags'],
                            ]),
                        ],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enabled',
                                    'value' => true,
                                    'compare' => '==',
                                ],
                                [
                                    'id' => 'account_id',
                                    'value' => '',
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'tags',
                        'label' => __('Tags', 'ht-contactform'),
                        'info' => __('Comma-separated tags. Use smart tags like {input.fieldname}', 'ht-contactform'),
                        'type' => 'input',
                        'placeholder' => 'newsletter, vip, {input.interest}',
                        'value' => '',
                        'callback' => 'sanitize_text_field',
                        'support' => ['tags'],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enabled',
                                    'value' => true,
                                    'compare' => '==',
                                ],
                                [
                                    'id' => 'account_id',
                                    'value' => '',
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                ]
            ],
            'moosend' => [
                'id' => 'moosend',
                'label' => __('Moosend', 'ht-contactform'),
                'value' => [
                    'enabled' => false,
                    'name' => '',
                    'list_id' => '',
                    'double_opt_in' => false,
                    'merge_fields' => [],
                ],
                'fields' => [
                    self::$field->create([
                        'id' => 'enabled',
                        'label' => __('Enable Moosend', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => true,
                        'callback' => 'rest_sanitize_boolean',
                    ]),
                    self::$field->create([
                        'id' => 'name',
                        'label' => __('Integration Name', 'ht-contactform'),
                        'info' => __('Enter a unique name for the Moosend integration to identify it.', 'ht-contactform'),
                        'value' => __('Moosend Integration Feed', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                    ]),
                    self::$field->create([
                        'id' => 'list_id',
                        'label' => __('Mailing List', 'ht-contactform'),
                        'info' => __('Select the Moosend mailing list to which contacts will be added.', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                        "type" => "select",
                        "options" => [],
                    ]),
                    self::$field->create([
                        'id' => 'double_opt_in',
                        'label' => __('Double Opt-In', 'ht-contactform'),
                        'info' => __('When enabled, new subscribers will receive a confirmation email. Note: Double opt-in must also be enabled in your Moosend mailing list settings.', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => false,
                        'callback' => 'rest_sanitize_boolean',
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enabled',
                                    'value' => true,
                                    'compare' => '==',
                                ],
                                [
                                    'id' => 'list_id',
                                    'value' => '',
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'merge_fields',
                        'label' => __('Map Fields', 'ht-contactform'),
                        'info' => __('Map your form fields to Moosend subscriber fields.', 'ht-contactform'),
                        'required' => true,
                        "type" => "custom",
                        "value" => [],
                        "fields" => [
                            self::$field->create([
                                'id' => 'value',
                                'placeholder' => __('Type or select smart tags.', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                                'support' => ['tags'],
                            ]),
                        ],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enabled',
                                    'value' => true,
                                    'compare' => '==',
                                ],
                                [
                                    'id' => 'list_id',
                                    'value' => '',
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                ]
            ],
            'icontact' => [
                'id' => 'icontact',
                'label' => __('iContact', 'ht-contactform'),
                'value' => [
                    'enabled' => false,
                    'name' => '',
                    'list_id' => '',
                    'merge_fields' => [],
                ],
                'fields' => [
                    self::$field->create([
                        'id' => 'enabled',
                        'label' => __('Enable iContact', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => true,
                        'callback' => 'rest_sanitize_boolean',
                    ]),
                    self::$field->create([
                        'id' => 'name',
                        'label' => __('Integration Name', 'ht-contactform'),
                        'info' => __('Enter a unique name for the iContact integration to identify it.', 'ht-contactform'),
                        'value' => __('iContact Integration Feed', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                    ]),
                    self::$field->create([
                        'id' => 'list_id',
                        'label' => __('Contact List', 'ht-contactform'),
                        'info' => __('Select the iContact list to which contacts will be added.', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                        "type" => "select",
                        "options" => [],
                    ]),
                    self::$field->create([
                        'id' => 'merge_fields',
                        'label' => __('Map Fields', 'ht-contactform'),
                        'info' => __('Map your form fields to iContact contact fields.', 'ht-contactform'),
                        'required' => true,
                        "type" => "custom",
                        "value" => [],
                        "fields" => [
                            self::$field->create([
                                'id' => 'value',
                                'placeholder' => __('Type or select smart tags.', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                                'support' => ['tags'],
                            ]),
                        ],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enabled',
                                    'value' => true,
                                    'compare' => '==',
                                ],
                                [
                                    'id' => 'list_id',
                                    'value' => '',
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                ]
            ],
            'mailpoet' => [
                'id' => 'mailpoet',
                'label' => __('MailPoet', 'ht-contactform'),
                'value' => [
                    'enabled' => false,
                    'name' => '',
                    'list_id' => '',
                    'double_opt_in' => false,
                    'merge_fields' => [],
                ],
                'fields' => [
                    self::$field->create([
                        'id' => 'enabled',
                        'label' => __('Enable MailPoet', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => true,
                        'callback' => 'rest_sanitize_boolean',
                    ]),
                    self::$field->create([
                        'id' => 'name',
                        'label' => __('Integration Name', 'ht-contactform'),
                        'info' => __('Enter a unique name for the MailPoet integration to identify it.', 'ht-contactform'),
                        'value' => __('MailPoet Integration Feed', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                    ]),
                    self::$field->create([
                        'id' => 'list_id',
                        'label' => __('Mailing List', 'ht-contactform'),
                        'info' => __('Select the MailPoet list to which subscribers will be added.', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                        "type" => "select",
                        "options" => [],
                    ]),
                    self::$field->create([
                        'id' => 'double_opt_in',
                        'label' => __('Enable Double Opt-In', 'ht-contactform'),
                        'info' => __('Send a confirmation email to subscribers before adding them to the list.', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => false,
                        'callback' => 'rest_sanitize_boolean',
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enabled',
                                    'value' => true,
                                    'compare' => '==',
                                ],
                                [
                                    'id' => 'list_id',
                                    'value' => '',
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'merge_fields',
                        'label' => __('Map Fields', 'ht-contactform'),
                        'info' => __('Map your form fields to MailPoet subscriber fields.', 'ht-contactform'),
                        'required' => true,
                        "type" => "custom",
                        "value" => [],
                        "fields" => [
                            self::$field->create([
                                'id' => 'value',
                                'placeholder' => __('Type or select smart tags.', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                                'support' => ['tags'],
                            ]),
                        ],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enabled',
                                    'value' => true,
                                    'compare' => '==',
                                ],
                                [
                                    'id' => 'list_id',
                                    'value' => '',
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                ]
            ],
            'notion' => [
                'id' => 'notion',
                'label' => __('Notion', 'ht-contactform'),
                'value' => [
                    'enabled' => false,
                    'name' => '',
                    'database_id' => '',
                    'merge_fields' => [],
                    'property_types' => [],
                ],
                'fields' => [
                    self::$field->create([
                        'id' => 'enabled',
                        'label' => __('Enable Notion', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => true,
                        'callback' => 'rest_sanitize_boolean',
                    ]),
                    self::$field->create([
                        'id' => 'name',
                        'label' => __('Integration Name', 'ht-contactform'),
                        'info' => __('Enter a unique name for the Notion integration to identify it.', 'ht-contactform'),
                        'value' => __('Notion Integration Feed', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                    ]),
                    self::$field->create([
                        'id' => 'database_id',
                        'label' => __('Database', 'ht-contactform'),
                        'info' => __('Select the Notion database to add entries to. Make sure the database is shared with your integration.', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                        "type" => "select",
                        "options" => [],
                    ]),
                    self::$field->create([
                        'id' => 'merge_fields',
                        'label' => __('Map Fields', 'ht-contactform'),
                        'info' => __('Map your form fields to Notion database properties.', 'ht-contactform'),
                        'required' => true,
                        "type" => "custom",
                        "value" => [],
                        "fields" => [
                            self::$field->create([
                                'id' => 'value',
                                'placeholder' => __('Type or select smart tags.', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                                'support' => ['tags'],
                            ]),
                        ],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enabled',
                                    'value' => true,
                                    'compare' => '==',
                                ],
                                [
                                    'id' => 'database_id',
                                    'value' => '',
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'property_types',
                        'type' => 'hidden',
                        'value' => [],
                    ]),
                ]
            ],
            'trello' => [
                'id' => 'trello',
                'label' => __('Trello', 'ht-contactform'),
                'value' => [
                    'enabled' => false,
                    'name' => '',
                    'board_id' => '',
                    'list_id' => '',
                    'card_name' => '',
                    'card_desc' => '',
                    'card_due' => '',
                    'card_labels' => [],
                    'card_position' => 'bottom',
                ],
                'fields' => [
                    self::$field->create([
                        'id' => 'enabled',
                        'label' => __('Enable Trello', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => true,
                        'callback' => 'rest_sanitize_boolean',
                    ]),
                    self::$field->create([
                        'id' => 'name',
                        'label' => __('Integration Name', 'ht-contactform'),
                        'info' => __('Enter a unique name for the Trello integration to identify it.', 'ht-contactform'),
                        'value' => __('Trello Integration Feed', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                    ]),
                    self::$field->create([
                        'id' => 'board_id',
                        'label' => __('Board', 'ht-contactform'),
                        'info' => __('Select the Trello board where cards will be created.', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                        'type' => 'select',
                        'options' => [],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enabled',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'list_id',
                        'label' => __('List', 'ht-contactform'),
                        'info' => __('Select the list within the board where cards will be added.', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                        'type' => 'select',
                        'options' => [],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enabled',
                                    'value' => true,
                                    'compare' => '==',
                                ],
                                [
                                    'id' => 'board_id',
                                    'value' => '',
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'card_name',
                        'label' => __('Card Name', 'ht-contactform'),
                        'info' => __('Enter the card title. You can use smart tags.', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                        'support' => ['tags'],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enabled',
                                    'value' => true,
                                    'compare' => '==',
                                ],
                                [
                                    'id' => 'list_id',
                                    'value' => '',
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'card_desc',
                        'label' => __('Card Description', 'ht-contactform'),
                        'info' => __('Enter the card description. You can use smart tags.', 'ht-contactform'),
                        'callback' => 'sanitize_textarea_field',
                        'type' => 'textarea',
                        'support' => ['tags'],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enabled',
                                    'value' => true,
                                    'compare' => '==',
                                ],
                                [
                                    'id' => 'list_id',
                                    'value' => '',
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'card_due',
                        'label' => __('Due Date', 'ht-contactform'),
                        'info' => __('Set a due date for the card. Use a date smart tag or enter a date format like "+7 days".', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'support' => ['tags'],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enabled',
                                    'value' => true,
                                    'compare' => '==',
                                ],
                                [
                                    'id' => 'list_id',
                                    'value' => '',
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'card_labels',
                        'label' => __('Labels', 'ht-contactform'),
                        'info' => __('Select labels to apply to the card.', 'ht-contactform'),
                        'type' => 'multiselect',
                        'multiple' => true,
                        'options' => [],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enabled',
                                    'value' => true,
                                    'compare' => '==',
                                ],
                                [
                                    'id' => 'board_id',
                                    'value' => '',
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'card_position',
                        'label' => __('Card Position', 'ht-contactform'),
                        'info' => __('Choose where the card should appear in the list.', 'ht-contactform'),
                        'type' => 'select',
                        'value' => 'bottom',
                        'options' => [
                            ['value' => 'top', 'label' => __('Top', 'ht-contactform')],
                            ['value' => 'bottom', 'label' => __('Bottom', 'ht-contactform')],
                        ],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enabled',
                                    'value' => true,
                                    'compare' => '==',
                                ],
                                [
                                    'id' => 'list_id',
                                    'value' => '',
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                ]
            ],
            'hubspot' => [
                'id' => 'hubspot',
                'label' => __('HubSpot', 'ht-contactform'),
                'value' => [
                    'enabled' => false,
                    'name' => '',
                    'list_id' => '',
                    'merge_fields' => [],
                ],
                'fields' => [
                    self::$field->create([
                        'id' => 'enabled',
                        'label' => __('Enable HubSpot', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => true,
                        'callback' => 'rest_sanitize_boolean',
                    ]),
                    self::$field->create([
                        'id' => 'name',
                        'label' => __('Integration Name', 'ht-contactform'),
                        'info' => __('Enter a unique name for the HubSpot integration to identify it.', 'ht-contactform'),
                        'value' => __('HubSpot Integration Feed', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                    ]),
                    self::$field->create([
                        'id' => 'list_id',
                        'label' => __('Contact List (Optional)', 'ht-contactform'),
                        'info' => __('Select a static list to add the contact to.', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        "type" => "select",
                        "options" => [],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enabled',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'merge_fields',
                        'label' => __('Map Fields', 'ht-contactform'),
                        'info' => __('Map your form fields to HubSpot contact properties.', 'ht-contactform'),
                        'required' => true,
                        "type" => "custom",
                        "value" => [],
                        "fields" => [
                            self::$field->create([
                                'id' => 'value',
                                'placeholder' => __('Type or select smart tags.', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                                'support' => ['tags'],
                            ]),
                        ],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enabled',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ],
                    ]),
                ]
            ],
            'twentycrm' => [
                'id' => 'twentycrm',
                'label' => __('Twenty CRM', 'ht-contactform'),
                'value' => [
                    'enabled' => false,
                    'name' => '',
                    'field_mapping' => [],
                    'update_existing' => true,
                    'create_company' => false,
                    'company_mapping' => [],
                ],
                'fields' => [
                    self::$field->create([
                        'id' => 'enabled',
                        'label' => __('Enable Twenty CRM', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => true,
                        'callback' => 'rest_sanitize_boolean',
                    ]),
                    self::$field->create([
                        'id' => 'name',
                        'label' => __('Integration Name', 'ht-contactform'),
                        'info' => __('Enter a unique name for the Twenty CRM integration to identify it.', 'ht-contactform'),
                        'value' => __('Twenty CRM Integration Feed', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                    ]),
                    self::$field->create([
                        'id' => 'field_mapping',
                        'label' => __('Map Person Fields', 'ht-contactform'),
                        'info' => __('Map your form fields to Twenty CRM person fields. Email is required.', 'ht-contactform'),
                        'required' => true,
                        'type' => 'custom',
                        'value' => [],
                        'fields' => [
                            self::$field->create([
                                'id' => 'value',
                                'placeholder' => __('Type or select smart tags.', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                                'support' => ['tags'],
                            ]),
                        ],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enabled',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'update_existing',
                        'label' => __('Update Existing Person', 'ht-contactform'),
                        'info' => __('When a person with the same email already exists, update that record instead of creating a duplicate.', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => true,
                        'callback' => 'rest_sanitize_boolean',
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enabled',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'create_company',
                        'label' => __('Create / Link Company', 'ht-contactform'),
                        'info' => __('Find or create a company record and link the person to it.', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => false,
                        'callback' => 'rest_sanitize_boolean',
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enabled',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'company_mapping',
                        'label' => __('Map Company Fields', 'ht-contactform'),
                        'info' => __('Map your form fields to Twenty CRM company fields. Name or domain is required.', 'ht-contactform'),
                        'type' => 'custom',
                        'value' => [],
                        'fields' => [
                            self::$field->create([
                                'id' => 'value',
                                'placeholder' => __('Type or select smart tags.', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                                'support' => ['tags'],
                            ]),
                        ],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enabled',
                                    'value' => true,
                                    'compare' => '==',
                                ],
                                [
                                    'id' => 'create_company',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ],
                    ]),
                ]
            ],
            'zohocrm' => [
                'id' => 'zohocrm',
                'label' => __('Zoho CRM', 'ht-contactform'),
                'value' => [
                    'enabled' => false,
                    'name' => '',
                    'module' => '',
                    'field_mapping' => [],
                    'update_existing' => false,
                ],
                'fields' => [
                    self::$field->create([
                        'id' => 'enabled',
                        'label' => __('Enable Zoho CRM', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => true,
                        'callback' => 'rest_sanitize_boolean',
                    ]),
                    self::$field->create([
                        'id' => 'name',
                        'label' => __('Integration Name', 'ht-contactform'),
                        'info' => __('Enter a unique name for the Zoho CRM integration to identify it.', 'ht-contactform'),
                        'value' => __('Zoho CRM Integration Feed', 'ht-contactform'),
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                    ]),
                    self::$field->create([
                        'id' => 'module',
                        'label' => __('Services', 'ht-contactform'),
                        'info' => __('Select the Zoho CRM service to create records in.', 'ht-contactform'),
                        'type' => 'select',
                        'options' => [],
                        'callback' => 'sanitize_text_field',
                        'required' => true,
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enabled',
                                    'value' => true,
                                    'compare' => '==',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'field_mapping',
                        'label' => __('Map Fields', 'ht-contactform'),
                        'info' => __('Map your form fields to Zoho CRM fields.', 'ht-contactform'),
                        'required' => true,
                        'type' => 'custom',
                        'value' => [],
                        'fields' => [
                            self::$field->create([
                                'id' => 'value',
                                'placeholder' => __('Type or select smart tags.', 'ht-contactform'),
                                'callback' => 'sanitize_text_field',
                                'support' => ['tags'],
                            ]),
                        ],
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enabled',
                                    'value' => true,
                                    'compare' => '==',
                                ],
                                [
                                    'id' => 'module',
                                    'value' => '',
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'update_existing',
                        'label' => __('Update Existing Records', 'ht-contactform'),
                        'info' => __('If a record with the same email exists, update it instead of creating a new one.', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => false,
                        'callback' => 'rest_sanitize_boolean',
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                [
                                    'id' => 'enabled',
                                    'value' => true,
                                    'compare' => '==',
                                ],
                                [
                                    'id' => 'module',
                                    'value' => '',
                                    'compare' => '!=',
                                ]
                            ]
                        ],
                    ]),
                ]
            ],
        ]);
    }

    public function get_support_genix_custom_fields() {
        // Now we can safely use the class
        if (class_exists('Mapbd_wps_custom_field')) {
            $custom_fields = \Mapbd_wps_custom_field::getCustomFieldForAPI();
            return $custom_fields->ticket_form;
        }
    }

    /**
     * Get available form Global settings
     * 
     * @return array Array of form settings
     */
    public function form_global_settings(): array {
        return apply_filters('ht_form_global_settings', [
            // 'general' => [
            //     'id' => 'general',
            //     'label' => __('General', 'ht-contactform'),
            //     'settings' => [
            //         self::$field->create([
            //             'id' => 'load_assets_globally',
            //             'label' => __('Load Assets Globally', 'ht-contactform'),
            //             'info' => __('Load assets globally for all forms.', 'ht-contactform'),
            //             'type' => 'switch',
            //             'value' => false,
            //         ]),
            //     ]
            // ],
            'layout' => [
                'id' => 'layout',
                'label' => __('Layout', 'ht-contactform'),
                'settings' => [
                    self::$field->label_position([
                        'value' => 'top',
                    ]),
                    self::$field->create([
                        'id' => 'help_message_placement',
                        'label' => __('Help Message Placement', 'ht-contactform'),
                        'info' => __('Set the placement of help messages for form fields.', 'ht-contactform'),
                        'type' => 'select',
                        'value' => 'next_to_label',
                        'options' => [
                            [
                                'value' => 'next_to_label',
                                'label' => __('Next to Label as Tooltip', 'ht-contactform'),
                            ],
                            [
                                'value' => 'below_input_element',
                                'label' => __('Below Input Element', 'ht-contactform'),
                            ],
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'error_message_placement',
                        'label' => __('Error Message Placement', 'ht-contactform'),
                        'info' => __('Set the placement of error messages for form fields.', 'ht-contactform'),
                        'type' => 'select',
                        'value' => 'below_input_element',
                        'options' => [
                            [
                                'value' => 'below_label',
                                'label' => __('Below Label', 'ht-contactform'),
                            ],
                            [
                                'value' => 'below_input_element',
                                'label' => __('Below Input Element', 'ht-contactform'),
                            ],
                        ],
                    ]),
                ]
            ],
            'email' => [
                'id' => 'email',
                'label' => __('Email', 'ht-contactform'),
                'settings' => [
                    self::$field->create([
                        'id' => 'template',
                        'label' => __('Select Template', 'ht-contactform'),
                        'type' => 'select',
                        'value' => '1',
                        'options' => [
                            ["value" => "1", "label" => __('Template 1', 'ht-contactform')],
                            ["value" => "2", "label" => __('Template 2', 'ht-contactform')],
                            ["value" => "3", "label" => __('Template 3', 'ht-contactform')],
                            ["value" => "4", "label" => __('Template 4', 'ht-contactform')],
                            ["value" => "5", "label" => __('Template 5', 'ht-contactform')],
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'footer_text',
                        'label' => __('Email Footer Text', 'ht-contactform'),
                        'info' => __('This text will be added at the end of the email content.', 'ht-contactform'),
                        'type' => 'textarea',
                        'value' => '',
                    ]),
                ]
            ],
            'captcha' => [
                'id' => 'captcha',
                'label' => __('Captcha', 'ht-contactform'),
                'settings' => [
                    // reCAPTCHA Section Title
                    self::$field->create([
                        'id' => 'recaptcha_title',
                        'type' => 'title',
                        'label' => __('Google reCAPTCHA', 'ht-contactform'),
                    ]),
                    // Default Version Selector (Button style)
                    self::$field->create([
                        'id' => 'recaptcha_active_version',
                        'label' => __('Default Version', 'ht-contactform'),
                        'type' => 'radio_card',
                        'info' => __('Select which reCAPTCHA version to use on forms.', 'ht-contactform'),
                        'options' => [
                            [
                                'value' => 'v2',
                                'label' => __('reCAPTCHA v2', 'ht-contactform'),
                                'description' => __('Checkbox challenge', 'ht-contactform'),
                            ],
                            [
                                'value' => 'v3',
                                'label' => __('reCAPTCHA v3', 'ht-contactform'),
                                'description' => __('Invisible verification', 'ht-contactform'),
                            ]
                        ],
                        'value' => 'v2',
                    ]),
                    // V2 Section (show only when v2 is active)
                    // self::$field->create([
                    //     'id' => 'recaptcha_v2_title',
                    //     'type' => 'title',
                    //     'label' => __('reCAPTCHA v2 (Checkbox)', 'ht-contactform'),
                    //     'dependency' => [
                    //         'relation' => 'AND',
                    //         'rules' => [
                    //             ['id' => 'recaptcha_active_version', 'value' => 'v2', 'compare' => '==']
                    //         ]
                    //     ],
                    // ]),
                    self::$field->create([
                        'id' => 'recaptcha_v2_site_key',
                        'label' => __('Site Key', 'ht-contactform'),
                        'value' => '',
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                ['id' => 'recaptcha_active_version', 'value' => 'v2', 'compare' => '==']
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'recaptcha_v2_secret_key',
                        'label' => __('Secret Key', 'ht-contactform'),
                        'type' => 'password',
                        'value' => '',
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                ['id' => 'recaptcha_active_version', 'value' => 'v2', 'compare' => '==']
                            ]
                        ],
                    ]),
                    // V3 Section (show only when v3 is active)
                    // self::$field->create([
                    //     'id' => 'recaptcha_v3_title',
                    //     'type' => 'title',
                    //     'label' => __('reCAPTCHA v3 (Invisible)', 'ht-contactform'),
                    //     'dependency' => [
                    //         'relation' => 'AND',
                    //         'rules' => [
                    //             ['id' => 'recaptcha_active_version', 'value' => 'v3', 'compare' => '==']
                    //         ]
                    //     ],
                    // ]),
                    self::$field->create([
                        'id' => 'recaptcha_v3_site_key',
                        'label' => __('Site Key', 'ht-contactform'),
                        'value' => '',
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                ['id' => 'recaptcha_active_version', 'value' => 'v3', 'compare' => '==']
                            ]
                        ],
                    ]),
                    self::$field->create([
                        'id' => 'recaptcha_v3_secret_key',
                        'label' => __('Secret Key', 'ht-contactform'),
                        'type' => 'password',
                        'value' => '',
                        'dependency' => [
                            'relation' => 'AND',
                            'rules' => [
                                ['id' => 'recaptcha_active_version', 'value' => 'v3', 'compare' => '==']
                            ]
                        ],
                    ]),
                    // hCaptcha Section
                    self::$field->create([
                        'id' => 'hcaptcha_title',
                        'type' => 'title',
                        'label' => __('hCaptcha', 'ht-contactform'),
                    ]),
                    self::$field->create([
                        'id' => 'hcaptcha_site_key',
                        'label' => __('Site Key', 'ht-contactform'),
                        'value' => '',
                    ]),
                    self::$field->create([
                        'id' => 'hcaptcha_secret_key',
                        'label' => __('Secret Key', 'ht-contactform'),
                        'type' => 'password',
                        'value' => '',
                    ]),
                ]
            ],
            'validation_messages' => [
                'id' => 'validation_messages',
                'label' => __('Validation Messages', 'ht-contactform'),
                'settings' => [
                    self::$field->create([
                        'id' => 'required',
                        'label' => __('Required', 'ht-contactform'),
                        'info' => __('This message will be shown if validation fails for required field.', 'ht-contactform'),
                        'value' => __('This field is required.', 'ht-contactform'),
                    ]),
                    self::$field->create([
                        'id' => 'email',
                        'label' => __('Email', 'ht-contactform'),
                        'info' => __('This message will be shown if validation fails for email field.', 'ht-contactform'),
                        'value' => __('Please enter a valid email address.', 'ht-contactform'),
                    ]),
                    self::$field->create([
                        'id' => 'url',
                        'label' => __('URL', 'ht-contactform'),
                        'info' => __('This message will be shown if validation fails for URL field.', 'ht-contactform'),
                        'value' => __('Please enter a valid URL.', 'ht-contactform'),
                    ]),
                    self::$field->create([
                        'id' => 'number',
                        'label' => __('Number', 'ht-contactform'),
                        'info' => __('This message will be shown if validation fails for number field.', 'ht-contactform'),
                        'value' => __('Please enter a valid number.', 'ht-contactform'),
                    ]),
                    self::$field->create([
                        'id' => 'input_mask',
                        'label' => __('Input Mask Incomplete', 'ht-contactform'),
                        'info' => __('This message will be shown if validation fails for input mask field. {format} will be replaced with the actual format.', 'ht-contactform'),
                        'value' => __('Please enter a valid {format} format', 'ht-contactform'),
                    ]),
                    self::$field->create([
                        'id' => 'minimum_number',
                        'label' => __('Minimum Number', 'ht-contactform'),
                        'info' => __('This message will be shown if validation fails for number field minimum value.', 'ht-contactform'),
                        'value' => __('You have exceeded the number of allowed {min}. {min} will be replaced with the field minimum value.', 'ht-contactform'),
                    ]),
                    self::$field->create([
                        'id' => 'maximum_number',
                        'label' => __('Maximum Number', 'ht-contactform'),
                        'info' => __('This message will be shown if validation fails for number field maximum value.', 'ht-contactform'),
                        'value' => __('You have exceeded the number of allowed {max}. {max} will be replaced with the field maximum value.', 'ht-contactform'),
                    ]),
                ]
            ],
            'miscellaneous' => [
                'id' => 'miscellaneous',
                'label' => __('Miscellaneous', 'ht-contactform'),
                'settings' => [
                    self::$field->create([
                        'id' => 'disable_ip_logging',
                        'label' => __('Disable IP Logging', 'ht-contactform'),
                        'info' => __('If this option is turned on, the user\'s IP address will not be saved with the form data.', 'ht-contactform'),
                        'type' => 'switch',
                        'value' => false,
                    ]),
                ]
            ],
        ]);
    }

    /**
     * Get available form Integrations
     * 
     * @return array Array of form settings
     */
    public function form_integrations(): array {
        return apply_filters('ht_form_integrations', [
            'id' => 'integration',
            'label' => __('Integrations', 'ht-contactform'),
            'settings' => [
                self::$field->create([
                    'id' => 'webhook',
                    'icon' => 'webhook',
                    'label' => __('Webhook', 'ht-contactform'),
                    'info' => __('This option allows you to integrate with external services using webhooks.', 'ht-contactform'),
                    'type' => 'integration',
                    'value' => false,
                    'callback' => 'switch',
                ]),
                self::$field->create([
                    'id' => 'mailchimp',
                    'icon' => 'mailchimp',
                    'label' => __('Mailchimp', 'ht-contactform'),
                    'info' => __('This option allows you to integrate with Mailchimp.', 'ht-contactform'),
                    'type' => 'integration',
                    'value' => false,
                    'callback' => 'switch',
                    'options' => [
                        self::$field->create([
                            'id' => 'api_key',
                            'label' => __('API Key', 'ht-contactform'),
                            'info' => __('Enter your Mailchimp API Key. If you don\'t have one, please log in to your Mailchimp account and go to Account > Extras > API keys.', 'ht-contactform'),
                            'callback' => 'api_key',
                            'required' => true,
                        ]),
                    ]
                ]),
                self::$field->create([
                    'id' => 'slack',
                    'icon' => 'slack',
                    'label' => __('Slack', 'ht-contactform'),
                    'info' => __('Get instant notifications in your Slack channel whenever a new submission is received.', 'ht-contactform'),
                    'type' => 'integration',
                    'value' => false,
                    'callback' => 'switch',
                ]),
                self::$field->create([
                    'id' => 'discord',
                    'icon' => 'discord',
                    'label' => __('Discord', 'ht-contactform'),
                    'info' => __('Get instant notifications in your Discord channel whenever a new submission is received.', 'ht-contactform'),
                    'type' => 'integration',
                    'value' => false,
                    'callback' => 'switch',
                ]),
                self::$field->create([
                    'id' => 'activecampaign',
                    'icon' => 'activecampaign',
                    'label' => __('ActiveCampaign', 'ht-contactform'),
                    'info' => __('This option allows you to integrate with ActiveCampaign.', 'ht-contactform'),
                    'type' => 'integration',
                    'value' => false,
                    'callback' => 'switch',
                    'options' => [
                        self::$field->create([
                            'id' => 'api_url',
                            'label' => __('API URL', 'ht-contactform'),
                            'info' => __('Enter your ActiveCampaign API URL.', 'ht-contactform'),
                            'callback' => 'api_url',
                            'required' => true,
                        ]),
                        self::$field->create([
                            'id' => 'api_key',
                            'label' => __('API Key', 'ht-contactform'),
                            'info' => __('Enter your ActiveCampaign API Key.', 'ht-contactform'),
                            'callback' => 'api_key',
                            'required' => true,
                        ]),
                    ]
                ]),
                self::$field->create([
                    'id' => 'mailerlite',
                    'icon' => 'mailerlite',
                    'label' => __('MailerLite', 'ht-contactform'),
                    'info' => __('This option allows you to integrate with MailerLite.', 'ht-contactform'),
                    'type' => 'integration',
                    'value' => false,
                    'callback' => 'switch',
                    'options' => [
                        self::$field->create([
                            'id' => 'api_key',
                            'label' => __('API Token', 'ht-contactform'),
                            'info' => __('Enter your MailerLite API Token.', 'ht-contactform'),
                            'callback' => 'api_key',
                            'required' => true,
                        ]),
                    ]
                ]),
                self::$field->create([
                    'id' => 'zapier',
                    'icon' => 'zapier',
                    'label' => __('Zapier', 'ht-contactform'),
                    'info' => __('This option allows you to integrate with Zapier.', 'ht-contactform'),
                    'type' => 'integration',
                    'value' => false,
                    'callback' => 'switch',
                ]),
                self::$field->create([
                    'id' => 'supportgenix',
                    'icon' => 'supportgenix',
                    'label' => __('Support Genix', 'ht-contactform'),
                    'info' => __('This option allows you to integrate with Support Genix.', 'ht-contactform'),
                    'disabled_info' => __('The Support Genix plugin is not active or needs to be updated. Please activate or update the plugin.', 'ht-contactform'),
                    'type' => 'integration',
                    'value' => false,
                    'disabled' => !class_exists('Apbd_wps_ht_contact_form'),
                    'callback' => 'switch',
                ]),
                self::$field->create([
                    'id' => 'constantcontact',
                    'icon' => 'constantcontact',
                    'label' => __('ConstantContact', 'ht-contactform'),
                    'info' => __('This option allows you to integrate with ConstantContact.', 'ht-contactform'),
                    'type' => 'integration',
                    'value' => false,
                    'callback' => 'switch',
                    'options' => [
                        self::$field->create([
                            'id' => 'client_id',
                            'label' => __('Client ID', 'ht-contactform'),
                            'info' => __('Enter your ConstantContact Client ID.', 'ht-contactform'),
                            'callback' => 'client_id',
                            'required' => true,
                        ]),
                        self::$field->create([
                            'id' => 'client_secret',
                            'label' => __('Client Secret', 'ht-contactform'),
                            'info' => __('Enter your ConstantContact Client Secret.', 'ht-contactform'),
                            'callback' => 'client_secret',
                            'required' => true,
                        ]),
                        self::$field->create([
                            'id' => 'redirect_uri',
                            'type' => 'text',
                            'content' => [
                                __('Set your Redirect Url as: ', 'ht-contactform') .'<strong>'. rest_url('ht-form/v1/constantcontact/callback'). '</strong>'
                            ],
                        ]),
                    ]
                ]),
                self::$field->create([
                    'id' => 'brevo',
                    'icon' => 'brevo',
                    'label' => __('Brevo', 'ht-contactform'),
                    'info' => __('This option allows you to integrate with Brevo.', 'ht-contactform'),
                    'type' => 'integration',
                    'value' => false,
                    'callback' => 'switch',
                    'options' => [
                        self::$field->create([
                            'id' => 'api_key',
                            'label' => __('API Token', 'ht-contactform'),
                            'info' => __('Enter your Brevo API Token.', 'ht-contactform'),
                            'callback' => 'api_key',
                            'required' => true,
                        ]),
                    ]
                ]),
                self::$field->create([
                    'id' => 'insightly',
                    'icon' => 'insightly',
                    'label' => __('Insightly', 'ht-contactform'),
                    'info' => __('This option allows you to integrate with Insightly.', 'ht-contactform'),
                    'type' => 'integration',
                    'value' => false,
                    'callback' => 'switch',
                    'options' => [
                        self::$field->create([
                            'id' => 'api_key',
                            'label' => __('API Key', 'ht-contactform'),
                            'info' => __('Enter your Insightly API Key.', 'ht-contactform'),
                            'callback' => 'api_key',
                            'required' => true,
                        ]),
                        self::$field->create([
                            'id' => 'api_url',
                            'label' => __('API URL', 'ht-contactform'),
                            'info' => __('Enter your Insightly API URL.', 'ht-contactform'),
                            'callback' => 'api_url',
                            'required' => true,
                        ]),
                    ]
                ]),
                self::$field->create([
                    'id' => 'onepagecrm',
                    'icon' => 'onepagecrm',
                    'label' => __('OnepageCRM', 'ht-contactform'),
                    'info' => __('This option allows you to integrate with OnepageCRM.', 'ht-contactform'),
                    'type' => 'integration',
                    'value' => false,
                    'callback' => 'switch',
                    'options' => [
                        self::$field->create([
                            'id' => 'user_id',
                            'label' => __('User ID', 'ht-contactform'),
                            'info' => __('Enter your OnepageCRM User ID.', 'ht-contactform'),
                            'callback' => 'user_id',
                            'required' => true,
                        ]),
                        self::$field->create([
                            'id' => 'api_key',
                            'label' => __('API Key', 'ht-contactform'),
                            'info' => __('Enter your OnepageCRM API Key.', 'ht-contactform'),
                            'callback' => 'api_key',
                            'required' => true,
                        ]),
                    ]
                ]),
                self::$field->create([
                    'id' => 'getresponse',
                    'icon' => 'getresponse',
                    'label' => __('GetResponse', 'ht-contactform'),
                    'info' => __('This option allows you to integrate with GetResponse.', 'ht-contactform'),
                    'type' => 'integration',
                    'value' => false,
                    'callback' => 'switch',
                    'options' => [
                        self::$field->create([
                            'id' => 'api_key',
                            'label' => __('API Key', 'ht-contactform'),
                            'info' => __('Enter your GetResponse API Key. You can find it in your GetResponse account settings.', 'ht-contactform'),
                            'callback' => 'api_key',
                            'required' => true,
                        ]),
                    ]
                ]),
                self::$field->create([
                    'id' => 'drip',
                    'icon' => 'drip',
                    'label' => __('Drip', 'ht-contactform'),
                    'info' => __('This option allows you to integrate with Drip email marketing automation.', 'ht-contactform'),
                    'type' => 'integration',
                    'value' => false,
                    'callback' => 'switch',
                    'options' => [
                        self::$field->create([
                            'id' => 'api_key',
                            'label' => __('API Key', 'ht-contactform'),
                            'info' => __('Enter your Drip API Key. You can find it in your Drip account under Settings > User Settings > API Token.', 'ht-contactform'),
                            'callback' => 'api_key',
                            'required' => true,
                        ]),
                    ]
                ]),
                self::$field->create([
                    'id' => 'moosend',
                    'icon' => 'moosend',
                    'label' => __('Moosend', 'ht-contactform'),
                    'info' => __('This option allows you to integrate with Moosend email marketing.', 'ht-contactform'),
                    'type' => 'integration',
                    'value' => false,
                    'callback' => 'switch',
                    'options' => [
                        self::$field->create([
                            'id' => 'api_key',
                            'label' => __('API Key', 'ht-contactform'),
                            'info' => __('Enter your Moosend API Key. You can find it in your Moosend account under Settings > API Key.', 'ht-contactform'),
                            'callback' => 'api_key',
                            'required' => true,
                        ]),
                    ]
                ]),
                self::$field->create([
                    'id' => 'icontact',
                    'icon' => 'icontact',
                    'label' => __('iContact', 'ht-contactform'),
                    'info' => __('This option allows you to integrate with iContact email marketing.', 'ht-contactform'),
                    'type' => 'integration',
                    'value' => false,
                    'callback' => 'switch',
                    'options' => [
                        self::$field->create([
                            'id' => 'app_id',
                            'label' => __('App ID', 'ht-contactform'),
                            'info' => __('Enter your iContact Application ID. You can get it from the iContact API settings.', 'ht-contactform'),
                            'callback' => 'sanitize_text_field',
                            'required' => true,
                        ]),
                        self::$field->create([
                            'id' => 'username',
                            'label' => __('API Username', 'ht-contactform'),
                            'info' => __('Enter your iContact API Username.', 'ht-contactform'),
                            'callback' => 'sanitize_text_field',
                            'required' => true,
                        ]),
                        self::$field->create([
                            'id' => 'password',
                            'label' => __('API Password', 'ht-contactform'),
                            'info' => __('Enter your iContact API Password.', 'ht-contactform'),
                            'callback' => 'api_key',
                            'required' => true,
                        ]),
                        self::$field->create([
                            'id' => 'account_id',
                            'label' => __('Account ID', 'ht-contactform'),
                            'info' => __('Enter your iContact Account ID. You can find it in your API URL.', 'ht-contactform'),
                            'callback' => 'sanitize_text_field',
                            'required' => true,
                        ]),
                        self::$field->create([
                            'id' => 'client_folder_id',
                            'label' => __('Client Folder ID', 'ht-contactform'),
                            'info' => __('Enter your iContact Client Folder ID. You can find it in your API URL.', 'ht-contactform'),
                            'callback' => 'sanitize_text_field',
                            'required' => true,
                        ]),
                    ]
                ]),
                self::$field->create([
                    'id' => 'mailpoet',
                    'icon' => 'mailpoet',
                    'label' => __('MailPoet', 'ht-contactform'),
                    'info' => __('This option allows you to integrate with MailPoet - your local WordPress email marketing plugin. No API key required.', 'ht-contactform'),
                    'type' => 'integration',
                    'value' => false,
                    'callback' => 'switch',
                ]),
                self::$field->create([
                    'id' => 'notion',
                    'icon' => 'notion',
                    'label' => __('Notion', 'ht-contactform'),
                    'info' => __('This option allows you to integrate with Notion to add form submissions to your databases.', 'ht-contactform'),
                    'type' => 'integration',
                    'value' => false,
                    'callback' => 'switch',
                    'options' => [
                        self::$field->create([
                            'id' => 'api_key',
                            'label' => __('Internal Integration Token', 'ht-contactform'),
                            'info' => __('Enter your Notion Internal Integration Token. Create an integration at notion.so/my-integrations and copy the secret.', 'ht-contactform'),
                            'callback' => 'api_key',
                            'required' => true,
                        ]),
                    ]
                ]),
                self::$field->create([
                    'id' => 'trello',
                    'icon' => 'trello',
                    'label' => __('Trello', 'ht-contactform'),
                    'info' => __('This option allows you to integrate with Trello to create cards from form submissions.', 'ht-contactform'),
                    'type' => 'integration',
                    'value' => false,
                    'callback' => 'switch',
                    'options' => [
                        self::$field->create([
                            'id' => 'api_key',
                            'label' => __('API Key', 'ht-contactform'),
                            'info' => __('Enter your Trello API Key. Get it from trello.com/power-ups/admin', 'ht-contactform'),
                            'callback' => 'api_key',
                            'required' => true,
                        ]),
                        self::$field->create([
                            'id' => 'api_token',
                            'label' => __('API Token', 'ht-contactform'),
                            'info' => __('Enter your Trello API Token. Generate it by authorizing your API key.', 'ht-contactform'),
                            'callback' => 'api_key',
                            'required' => true,
                        ]),
                    ]
                ]),
                self::$field->create([
                    'id' => 'hubspot',
                    'icon' => 'hubspot',
                    'label' => __('HubSpot', 'ht-contactform'),
                    'info' => __('This option allows you to integrate with HubSpot CRM to create contacts from form submissions.', 'ht-contactform'),
                    'type' => 'integration',
                    'value' => false,
                    'callback' => 'switch',
                    'options' => [
                        self::$field->create([
                            'id' => 'access_token',
                            'label' => __('Private App Access Token', 'ht-contactform'),
                            'info' => __('Enter your HubSpot Private App Access Token. Create one at Settings > Integrations > Private Apps in HubSpot.', 'ht-contactform'),
                            'callback' => 'api_key',
                            'required' => true,
                        ]),
                    ]
                ]),
                self::$field->create([
                    'id' => 'twentycrm',
                    'icon' => 'twentycrm',
                    'label' => __('Twenty CRM', 'ht-contactform'),
                    'info' => __('This option allows you to integrate with Twenty CRM to create people, companies, and notes from form submissions.', 'ht-contactform'),
                    'type' => 'integration',
                    'value' => false,
                    'callback' => 'switch',
                    'options' => [
                        self::$field->create([
                            'id' => 'base_url',
                            'label' => __('Instance URL', 'ht-contactform'),
                            'info' => __('Use https://api.twenty.com for Twenty Cloud, or your own domain for a self-hosted instance.', 'ht-contactform'),
                            'value' => 'https://api.twenty.com',
                            'callback' => 'esc_url_raw',
                            'required' => true,
                        ]),
                        self::$field->create([
                            'id' => 'api_key',
                            'label' => __('API Key', 'ht-contactform'),
                            'info' => __('Create one in Twenty under Settings > API & Webhooks > Create key. It is shown only once.', 'ht-contactform'),
                            'callback' => 'api_key',
                            'required' => true,
                        ]),
                    ]
                ]),
                self::$field->create([
                    'id' => 'zohocrm',
                    'icon' => 'zohocrm',
                    'label' => __('Zoho CRM', 'ht-contactform'),
                    'info' => __('This option allows you to integrate with Zoho CRM to create Leads, Contacts, and other records from form submissions.', 'ht-contactform'),
                    'type' => 'integration',
                    'value' => false,
                    'callback' => 'switch',
                    'options' => [
                        self::$field->create([
                            'id' => 'zohocrm_urls',
                            'type' => 'text',
                            'content' => [
                                '<strong>' . __('Homepage URL:', 'ht-contactform') . '</strong> ' . home_url(),
                                '<strong>' . __('Redirect URL:', 'ht-contactform') . '</strong> ' . admin_url('admin.php?page=htcontact-form&path=integrations&zohocrm_auth=1'),
                            ],
                        ]),
                        self::$field->create([
                            'id' => 'data_center',
                            'label' => __('Account URL', 'ht-contactform'),
                            'info' => __('Select your Zoho data center region.', 'ht-contactform'),
                            'type' => 'select',
                            'value' => 'com',
                            'options' => [
                                ['value' => 'com', 'label' => __('United States (zoho.com)', 'ht-contactform')],
                                ['value' => 'eu', 'label' => __('Europe (zoho.eu)', 'ht-contactform')],
                                ['value' => 'in', 'label' => __('India (zoho.in)', 'ht-contactform')],
                                ['value' => 'com.au', 'label' => __('Australia (zoho.com.au)', 'ht-contactform')],
                                ['value' => 'com.cn', 'label' => __('China (zoho.com.cn)', 'ht-contactform')],
                                ['value' => 'jp', 'label' => __('Japan (zoho.jp)', 'ht-contactform')],
                            ],
                            'callback' => 'sanitize_text_field',
                            'required' => true,
                        ]),
                        self::$field->create([
                            'id' => 'client_id',
                            'label' => __('Zoho CRM Client ID', 'ht-contactform'),
                            'info' => __('Enter your Zoho CRM Client ID from Zoho API Console.', 'ht-contactform'),
                            'callback' => 'sanitize_text_field',
                            'required' => true,
                        ]),
                        self::$field->create([
                            'id' => 'client_secret',
                            'label' => __('Zoho CRM Client Secret', 'ht-contactform'),
                            'info' => __('Enter your Zoho CRM Client Secret from Zoho API Console.', 'ht-contactform'),
                            'callback' => 'api_key',
                            'required' => true,
                        ]),
                    ]
                ]),
            ]
        ]);
    }

    public function get_pages()
    {
        $pages = get_pages();
        $options = [];
        foreach ($pages as $page) {
            $item = [];
            $item['value'] = (string) $page->ID;
            $item['label'] = $page->post_title;
            $options[] = $item;
        }
        return $options;
    }
}