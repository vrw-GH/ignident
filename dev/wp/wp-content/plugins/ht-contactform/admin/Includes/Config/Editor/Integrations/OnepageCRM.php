<?php
/**
 * OnepageCRM Integration Configuration Class
 *
 * Defines the UI fields and configuration structure for OnepageCRM integration
 *
 * @package HTContactFormAdmin
 * @subpackage Config\Editor\Integrations
 */

namespace HTContactFormAdmin\Includes\Config\Editor\Integrations;

use HTContactFormAdmin\Includes\Config\Field;

/**
 * OnepageCRM Configuration
 *
 * Provides configuration arrays for OnepageCRM integration settings
 */
class OnepageCRM {
    /**
     * Field instance
     *
     * @var Field|null
     */
    public static $field = null;

    /**
     * Singleton instance
     *
     * @var self|null
     */
    private static $instance = null;

    /**
     * Get singleton instance
     *
     * @return self
     */
    public static function get_instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        self::$field = Field::get_instance();
    }

    /**
     * Get integration configuration
     *
     * @return array Integration configuration array
     */
    public function get_configs() {
        return [
            'id' => 'onepagecrm',
            'label' => __('OnepageCRM', 'ht-contactform'),
            'value' => [
                'enabled' => true,
                'name' => 'OnepageCRM Integration Feed',
                'service' => 'contact',
            ],
            'fields' => [
                self::$field->create([
                    'id' => 'enabled',
                    'label' => __('Enable OnepageCRM', 'ht-contactform'),
                    'type' => 'switch',
                    'value' => true,
                    'callback' => 'rest_sanitize_boolean',
                ]),
                self::$field->create([
                    'id' => 'name',
                    'label' => __('Integration Name', 'ht-contactform'),
                    'info' => __('Enter a unique name to identify this integration.', 'ht-contactform'),
                    'value' => __('OnepageCRM Integration Feed', 'ht-contactform'),
                    'callback' => 'sanitize_text_field',
                    'required' => true,
                ]),
                self::$field->create([
                    'id' => 'service',
                    'label' => __('OnepageCRM Services', 'ht-contactform'),
                    'info' => __('Select the type of service you want to create.', 'ht-contactform'),
                    'type' => 'select',
                    'value' => 'contact',
                    'callback' => 'sanitize_text_field',
                    'required' => true,
                    'options' => [],
                ]),
            ]
        ];
    }

    /**
     * Get Contact service fields
     *
     * @return array Contact service field configurations
     */
    public function get_contact_service_fields() {
        return [
            self::$field->create([
                'id' => 'title',
                'label' => __('Title', 'ht-contactform'),
                'type' => 'select',
                'callback' => 'sanitize_text_field',
                'options' => [
                    ['value' => 'mr', 'label' => __('Mr', 'ht-contactform')],
                    ['value' => 'mrs', 'label' => __('Mrs', 'ht-contactform')],
                    ['value' => 'ms', 'label' => __('Ms', 'ht-contactform')],
                ],
            ]),
            self::$field->create([
                'id' => 'first_name',
                'label' => __('First Name', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'required' => true,
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id' => 'last_name',
                'label' => __('Last Name', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'required' => true,
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id' => 'job_title',
                'label' => __('Job Title', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id' => 'starred',
                'label' => __('Starred', 'ht-contactform'),
                'type' => 'radio',
                'value' => 'no',
                'callback' => 'sanitize_text_field',
                'options' => [
                    ['value' => 'yes', 'label' => __('Yes', 'ht-contactform')],
                    ['value' => 'no', 'label' => __('No', 'ht-contactform')],
                ],
            ]),
            self::$field->create([
                'id' => 'company_name',
                'label' => __('Company Name', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id' => 'url_type',
                'label' => __('Select URL Type', 'ht-contactform'),
                'type' => 'select',
                'value' => 'website',
                'callback' => 'sanitize_text_field',
                'options' => [
                    ['value' => 'website', 'label' => __('Website', 'ht-contactform')],
                    ['value' => 'blog', 'label' => __('Blog', 'ht-contactform')],
                    ['value' => 'linkedin', 'label' => __('LinkedIn', 'ht-contactform')],
                    ['value' => 'facebook', 'label' => __('Facebook', 'ht-contactform')],
                    ['value' => 'twitter', 'label' => __('Twitter', 'ht-contactform')],
                    ['value' => 'other', 'label' => __('Other', 'ht-contactform')],
                ],
            ]),
            self::$field->create([
                'id' => 'url',
                'label' => __('Enter URL', 'ht-contactform'),
                'callback' => 'esc_url',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id' => 'phone_type',
                'label' => __('Select Phone Type', 'ht-contactform'),
                'type' => 'select',
                'value' => 'work',
                'callback' => 'sanitize_text_field',
                'options' => [
                    ['value' => 'work', 'label' => __('Work', 'ht-contactform')],
                    ['value' => 'mobile', 'label' => __('Mobile', 'ht-contactform')],
                    ['value' => 'home', 'label' => __('Home', 'ht-contactform')],
                    ['value' => 'direct', 'label' => __('Direct', 'ht-contactform')],
                    ['value' => 'fax', 'label' => __('Fax', 'ht-contactform')],
                    ['value' => 'skype', 'label' => __('Skype', 'ht-contactform')],
                    ['value' => 'other', 'label' => __('Other', 'ht-contactform')],
                ],
            ]),
            self::$field->create([
                'id' => 'phone',
                'label' => __('Enter Phone', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id' => 'email_type',
                'label' => __('Select Email', 'ht-contactform'),
                'type' => 'select',
                'value' => 'work',
                'callback' => 'sanitize_text_field',
                'options' => [
                    ['value' => 'work', 'label' => __('Work', 'ht-contactform')],
                    ['value' => 'home', 'label' => __('Home', 'ht-contactform')],
                    ['value' => 'other', 'label' => __('Other', 'ht-contactform')],
                ],
            ]),
            self::$field->create([
                'id' => 'email',
                'label' => __('Enter Email', 'ht-contactform'),
                'callback' => 'sanitize_email',
                'required' => true,
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id' => 'address',
                'label' => __('Enter Address', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id' => 'city',
                'label' => __('Enter City', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id' => 'state',
                'label' => __('Enter State', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id' => 'zip_code',
                'label' => __('Enter Zip Code', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id' => 'country_code',
                'label' => __('Enter Country Code', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id' => 'address_type',
                'label' => __('Enter Address Type', 'ht-contactform'),
                'type' => 'select',
                'callback' => 'sanitize_text_field',
                'options' => [
                    ['value' => 'home', 'label' => __('Home', 'ht-contactform')],
                    ['value' => 'work', 'label' => __('Work', 'ht-contactform')],
                    ['value' => 'billing', 'label' => __('Billing', 'ht-contactform')],
                    ['value' => 'delivery', 'label' => __('Delivery', 'ht-contactform')],
                    ['value' => 'other', 'label' => __('Other', 'ht-contactform')],
                ],
            ]),
            self::$field->create([
                'id' => 'status',
                'label' => __('Select Status', 'ht-contactform'),
                'type' => 'select',
                'callback' => 'sanitize_text_field',
                'options' => [],
            ]),
            self::$field->create([
                'id' => 'lead_source',
                'label' => __('Select Lead Source', 'ht-contactform'),
                'type' => 'select',
                'callback' => 'sanitize_text_field',
                'options' => [
                    [
                        'value' => 'advertisement',
                        'label' => __('Advertisement', 'ht-contactform'),
                    ],
                    [
                        'value' => 'email_web',
                        'label' => __('Email or Web', 'ht-contactform'),
                    ],
                    [
                        'value' => 'list_generation',
                        'label' => __('List Generation', 'ht-contactform'),
                    ],
                    [
                        'value' => 'partner',
                        'label' => __('Partner', 'ht-contactform'),
                    ],
                    [
                        'value' => 'referral',
                        'label' => __('Affiliate', 'ht-contactform'),
                    ],
                    [
                        'value' => 'social',
                        'label' => __('Social', 'ht-contactform'),
                    ],
                    [
                        'value' => 'seminar',
                        'label' => __('Seminar', 'ht-contactform'),
                    ],
                    [
                        'value' => 'tradeshow',
                        'label' => __('Tradeshow', 'ht-contactform'),
                    ],
                    [
                        'value' => 'word_of_mouth',
                        'label' => __('Word of mouth', 'ht-contactform'),
                    ],
                    [
                        'value' => 'other',
                        'label' => __('Other', 'ht-contactform'),
                    ]
                ],
            ]),
            self::$field->create([
                'id' => 'background',
                'label' => __('Write Background', 'ht-contactform'),
                'callback' => 'sanitize_textarea_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id' => 'birthday',
                'label' => __('Birthday', 'ht-contactform'),
                'type' => 'date',
                'valueFormat' => 'YYYY-MM-DD',
                'callback' => 'sanitize_text_field',
            ]),
        ];
    }

    /**
     * Get Deal service fields
     *
     * @return array Deal service field configurations
     */
    public function get_deal_service_fields() {
        return [
            self::$field->create([
                'id' => 'deal_name',
                'label' => __('Deal Name', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'required' => true,
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id' => 'deal_details',
                'label' => __('Deal Details', 'ht-contactform'),
                'callback' => 'sanitize_textarea_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id' => 'stage_number',
                'label' => __('Stage Number', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id' => 'expected_close_date',
                'label' => __('Expected Close Date', 'ht-contactform'),
                'type' => 'date',
                'valueFormat' => 'YYYY-MM-DD',
                'callback' => 'sanitize_text_field',
            ]),
            self::$field->create([
                'id' => 'close_date',
                'label' => __('Close Date', 'ht-contactform'),
                'type' => 'date',
                'valueFormat' => 'YYYY-MM-DD',
                'callback' => 'sanitize_text_field',
            ]),
            self::$field->create([
                'id' => 'creation_date',
                'label' => __('Creation Date', 'ht-contactform'),
                'type' => 'date',
                'valueFormat' => 'YYYY-MM-DD',
                'callback' => 'sanitize_text_field',
            ]),
            self::$field->create([
                'id' => 'deal_status',
                'label' => __('Status', 'ht-contactform'),
                'type' => 'select',
                'value' => 'pending',
                'callback' => 'sanitize_text_field',
                'options' => [
                    ['value' => 'pending', 'label' => __('Pending', 'ht-contactform')],
                    ['value' => 'won', 'label' => __('Won', 'ht-contactform')],
                    ['value' => 'lost', 'label' => __('Lost', 'ht-contactform')],
                ],
            ]),
            self::$field->create([
                'id' => 'contact_name',
                'label' => __('Contact Name', 'ht-contactform'),
                'info' => __('Select the user from your contact list.', 'ht-contactform'),
                'type' => 'select',
                'callback' => 'sanitize_text_field',
                'required' => true,
                'options' => [],
            ]),
        ];
    }

    /**
     * Get Note service fields
     *
     * @return array Note service field configurations
     */
    public function get_note_service_fields() {
        return [
            self::$field->create([
                'id' => 'note_details',
                'label' => __('Note Details', 'ht-contactform'),
                'callback' => 'sanitize_textarea_field',
                'required' => true,
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id' => 'note_date',
                'label' => __('Date', 'ht-contactform'),
                'type' => 'date',
                'valueFormat' => 'YYYY-MM-DD',
                'callback' => 'sanitize_text_field',
            ]),
            self::$field->create([
                'id' => 'contact_name',
                'label' => __('Contact Name', 'ht-contactform'),
                'info' => __('Select the user from your contact list.', 'ht-contactform'),
                'type' => 'select',
                'callback' => 'sanitize_text_field',
                'required' => true,
                'options' => [],
            ]),
        ];
    }

    /**
     * Get Action service fields
     *
     * @return array Action service field configurations
     */
    public function get_action_service_fields() {
        return [
            self::$field->create([
                'id' => 'action_status',
                'label' => __('Action Status', 'ht-contactform'),
                'type' => 'select',
                'callback' => 'sanitize_text_field',
                'required' => true,
                'options' => [
                    ['value' => 'asap', 'label' => __('ASAP', 'ht-contactform')],
                    ['value' => 'date', 'label' => __('Date', 'ht-contactform')],
                    ['value' => 'waiting', 'label' => __('Waiting', 'ht-contactform')],
                    ['value' => 'queued', 'label' => __('Queued', 'ht-contactform')],
                    ['value' => 'done', 'label' => __('Done', 'ht-contactform')],
                ],
            ]),
            self::$field->create([
                'id' => 'action_details',
                'label' => __('Action Details', 'ht-contactform'),
                'callback' => 'sanitize_textarea_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id' => 'action_date',
                'label' => __('Action Date', 'ht-contactform'),
                'type' => 'date',
                'valueFormat' => 'YYYY-MM-DD',
                'callback' => 'sanitize_text_field',
            ]),
            self::$field->create([
                'id' => 'contact_name',
                'label' => __('Contact Name', 'ht-contactform'),
                'info' => __('Select the user from your contact list.', 'ht-contactform'),
                'type' => 'select',
                'callback' => 'sanitize_text_field',
                'required' => true,
                'options' => [],
            ]),
        ];
    }
}
