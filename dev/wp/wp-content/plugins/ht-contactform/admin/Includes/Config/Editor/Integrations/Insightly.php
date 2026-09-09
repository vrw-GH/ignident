<?php
namespace HTContactFormAdmin\Includes\Config\Editor\Integrations;

use HTContactFormAdmin\Includes\Config\Field;
use HTContactForm\Integrations\Insightly as InsightlyIntegration;

class Insightly {
    /** @var Field Field instance */
    public static $field = null;

    /** @var InsightlyIntegration Insightly integration instance */
    private $insightly = null;

    /** @var self|null Singleton instance */
    private static $instance = null;
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct() {
        self::$field = Field::get_instance();
        $this->insightly = new InsightlyIntegration();
    }

    public function get_configs() {
        return [
            'id' => 'insightly',
            'label' => __('Insightly', 'ht-contactform'),
            'value' => [
                'enabled' => true,
                'name' => 'Insightly Integration Feed',
                'service' => 'contact',
            ],
            'fields' => [
                self::$field->create([
                    'id' => 'enabled',
                    'label' => __('Enable Insightly', 'ht-contactform'),
                    'type' => 'switch',
                    'value' => true,
                    'callback' => 'rest_sanitize_boolean',
                ]),
                self::$field->create([
                    'id' => 'name',
                    'label' => __('Integration Name', 'ht-contactform'),
                    'info' => __('Enter a unique name for the you to identify it.', 'ht-contactform'),
                    'value' => __('Insightly Integration Feed', 'ht-contactform'),
                    'callback' => 'sanitize_text_field',
                    'required' => true,
                ]),
                self::$field->create([
                    'id' => 'service',
                    'label' => __('Insightly Service', 'ht-contactform'),
                    'info' => __('Select the insightly service you like to add your contact to.', 'ht-contactform'),
                    'callback' => 'sanitize_text_field',
                    'required' => true,
                    "type" => "select",
                    "value" => "contact",
                    "options" => [
                        [
                            'label' => __('Contacts', 'ht-contactform'),
                            'value' => 'contacts'
                        ],
                        [
                            'label' => __('Organizations', 'ht-contactform'),
                            'value' => 'organizations'
                        ],
                        [
                            'label' => __('Leads', 'ht-contactform'),
                            'value' => 'leads'
                        ],
                        [
                            'label' => __('Opportunities', 'ht-contactform'),
                            'value' => 'opportunities'
                        ],
                        [
                            'label' => __('Projects', 'ht-contactform'),
                            'value' => 'projects'
                        ],
                        [
                            'label' => __('Tasks', 'ht-contactform'),
                            'value' => 'tasks'
                        ],
                    ],
                ]),
            ]
        ];
    }

    /**
     * Get fields
     * @param string $service
     * @return array[]
     */
    public function get_fields($service) {
        if ($service === 'contacts') {
            return $this->contact_fields();
        } elseif ($service === 'leads') {
            return $this->leads_fields();
        } elseif ($service === 'opportunities') {
            return $this->opportunities_fields();
        } elseif ($service === 'organizations') {
            return $this->organizations_fields();
        } elseif ($service === 'projects') {
            return $this->projects_fields();
        } elseif ($service === 'tasks') {
            return $this->tasks_fields();
        }
        return [];
    }

    /**
     * Get contact fields
     * @return array[]
     */
    private function contact_fields() {
        return [
            self::$field->create([
                'id' => 'EMAIL_ADDRESS',
                'label' => __('Email Address', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'type' => 'select',
                'required' => true,
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'SALUTATION',
                'label' => __('Salutation', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'FIRST_NAME',
                'label' => __('First Name', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
                'required' => true,
            ]),
            self::$field->create([
                'id'   => 'LAST_NAME',
                'label' => __('Last Name', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'ORGANISATION_ID',
                'label' => __('Organization', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'type' => 'select',
                'options' => $this->insightly->get_organisations(),
            ]),
            self::$field->create([
                'id'   => 'TITLE',
                'label' => __('Organization Title', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'SOCIAL_LINKEDIN',
                'label' => __('LinkedIn URL', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'SOCIAL_FACEBOOK',
                'label' => __('Facebook URL', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'SOCIAL_TWITTER',
                'label' => __('Twitter URL', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'DATE_OF_BIRTH',
                'label' => __('Date of Birth', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'PHONE',
                'label' => __('Phone', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'PHONE_HOME',
                'label' => __('Phone Home', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'PHONE_MOBILE',
                'label' => __('Phone Mobile', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'PHONE_OTHER',
                'label' => __('Phone Other', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'PHONE_FAX',
                'label' => __('Fax Phone', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'ASSISTANT_NAME',
                'label' => __('Assistant Name', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'PHONE_ASSISTANT',
                'label' => __('Assistant Phone', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'ADDRESS_MAIL_STREET',
                'label' => __('Street Address', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'ADDRESS_MAIL_CITY',
                'label' => __('City', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'ADDRESS_MAIL_STATE',
                'label' => __('State', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'ADDRESS_MAIL_POSTCODE',
                'label' => __('Postcode', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'ADDRESS_MAIL_COUNTRY',
                'label' => __('Country', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'ADDRESS_OTHER_STREET',
                'label' => __('Other Street', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'ADDRESS_OTHER_CITY',
                'label' => __('Other City', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'ADDRESS_OTHER_STATE',
                'label' => __('Other State', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'ADDRESS_OTHER_POSTCODE',
                'label' => __('Other Postcode', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'ADDRESS_OTHER_COUNTRY',
                'label' => __('Other Country', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
        ];
    }

    /**
     * Leads fields
     * @return array[]
     */
    private function leads_fields() {
        return [
            self::$field->create([
                'id'   => 'SALUTATION',
                'label' => __('Salutation', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'FIRST_NAME',
                'label' => __('First Name', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
                'required' => true,
            ]),
            self::$field->create([
                'id'   => 'LAST_NAME',
                'label' => __('Last Name', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'LEAD_STATUS_ID',
                'label' => __('Lead Status', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'type' => 'select',
                'options' => $this->insightly->get_lead_statuses(),
                'required' => true,
            ]),
            self::$field->create([
                'id'   => 'LEAD_SOURCE_ID',
                'label' => __('Lead Source', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'type' => 'select',
                'options' => $this->insightly->get_lead_sources(),
                'required' => true,
            ]),
            self::$field->create([
                'id'   => 'RESPONSIBLE_USER_ID',
                'label' => __('Responsible User', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'type' => 'select',
                'options' => $this->insightly->get_users(),
            ]),
            self::$field->create([
                'id'   => 'TITLE',
                'label' => __('Organization Title', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'ORGANISATION_NAME',
                'label' => __('Organization Name', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'EMAIL',
                'label' => __('Email', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'FAX',
                'label' => __('Fax', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'INDUSTRY',
                'label' => __('Industry', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'LEAD_DESCRIPTION',
                'label' => __('Lead Description', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'LEAD_RATING',
                'label' => __('Lead Rating', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'MOBILE',
                'label' => __('Mobile', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'PHONE',
                'label' => __('Phone', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'WEBSITE',
                'label' => __('Website', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'EMPLOYEE_COUNT',
                'label' => __('Employee Count', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'ADDRESS_STREET',
                'label' => __('Street Address', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'ADDRESS_CITY',
                'label' => __('City', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'ADDRESS_STATE',
                'label' => __('State', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'ADDRESS_POSTCODE',
                'label' => __('Postcode', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'ADDRESS_COUNTRY',
                'label' => __('Country', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
        ];
    }

    /**
     * Opportunities fields
     * @return array[]
     */
    private function opportunities_fields() {
        return [
            self::$field->create([
                'id' => 'OPPORTUNITY_NAME',
                'label' => __('Opportunity Name', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'required' => true,
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'OPPORTUNITY_DETAILS',
                'label' => __('Opportunity Details', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'ORGANISATION_ID',
                'label' => __('Organization', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'type' => 'select',
                'options' => $this->insightly->get_organisations(),
            ]),
            self::$field->create([
                'id'   => 'CATEGORY_ID',
                'label' => __('Project Category', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'type' => 'select',
                'options' => $this->insightly->get_project_categories(),
            ]),
            self::$field->create([
                'id'   => 'PROBABILITY',
                'label' => __('Probability of Winning', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'FORECAST_CLOSE_DATE',
                'label' => __('Forecast Close Date', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'ACTUAL_CLOSE_DATE',
                'label' => __('Actual Close Date', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'RESPONSIBLE_USER_ID',
                'label' => __('Responsible User', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'type' => 'select',
                'options' => $this->insightly->get_users(),
            ]),
            self::$field->create([
                'id'   => 'BID_CURRENCY',
                'label' => __('Bid Currency', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'BID_AMOUNT',
                'label' => __('Bid Amount', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'BID_TYPE',
                'label' => __('Bid Type', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'type' => 'select',
                'options' => [
                    [
                        'value' => 'Fixed Bid',
                        'label' => __('Fixed Bid', 'ht-contactform'),
                    ],
                    [
                        'value' => 'Per Hour',
                        'label' => __('Per Hour', 'ht-contactform'),
                    ],
                    [
                        'value' => 'Per Day',
                        'label' => __('Per Day', 'ht-contactform'),
                    ],
                    [
                        'value' => 'Per Week',
                        'label' => __('Per Week', 'ht-contactform'),
                    ],
                    [
                        'value' => 'Per Month',
                        'label' => __('Per Month', 'ht-contactform'),
                    ],
                    [
                        'value' => 'Per Year',
                        'label' => __('Per Year', 'ht-contactform'),
                    ],
                ],
            ]),
            self::$field->create([
                'id'   => 'PIPELINE_ID',
                'label' => __('Pipeline', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'type' => 'select',
                'options' => $this->insightly->get_pipelines(),
            ]),
            self::$field->create([
                'id'   => 'STAGE_ID',
                'label' => __('Pipeline Stage', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'type' => 'select',
                'options' => $this->insightly->get_pipeline_stages(),
            ]),
        ];
    }

    /**
     * Organizations fields
     * @return array[]
     */
    private function organizations_fields() {
        return [
            self::$field->create([
                'id' => 'ORGANISATION_NAME',
                'label' => __('Organization Name', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
                'required' => true,
            ]),
            self::$field->create([
                'id'   => 'BACKGROUND',
                'label' => __('Background', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'PHONE',
                'label' => __('Phone', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'PHONE_FAX',
                'label' => __('Fax', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'WEBSITE',
                'label' => __('Website', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),

            self::$field->create([
                'id'   => 'ADDRESS_BILLING_STREET',
                'label' => __('Billing Street', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'ADDRESS_BILLING_CITY',
                'label' => __('Billing City', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'ADDRESS_BILLING_STATE',
                'label' => __('Billing State', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'ADDRESS_BILLING_POSTCODE',
                'label' => __('Billing Postal Code', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'ADDRESS_BILLING_COUNTRY',
                'label' => __('Billing Country', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),

            self::$field->create([
                'id'   => 'ADDRESS_SHIP_STREET',
                'label' => __('Shipping Street', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'ADDRESS_SHIP_CITY',
                'label' => __('Shipping City', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'ADDRESS_SHIP_STATE',
                'label' => __('Shipping State', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'ADDRESS_SHIP_POSTCODE',
                'label' => __('Shipping Postal Code', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'ADDRESS_SHIP_COUNTRY',
                'label' => __('Shipping Country', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            
            self::$field->create([
                'id'   => 'SOCIAL_LINKEDIN',
                'label' => __('LinkedIn URL', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'SOCIAL_FACEBOOK',
                'label' => __('Facebook URL', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'SOCIAL_TWITTER',
                'label' => __('Twitter URL', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
        ];
    }

    /**
     * Project fields
     * @return array[]
     */
    private function projects_fields() {
        return [
            self::$field->create([
                'id' => 'PROJECT_NAME',
                'label' => __('Project Name', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'required' => true,
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'STATUS',
                'label' => __('Status', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'type' => 'select',
                'options' => [
                    [
                        "value" => 'IN PROGRESS',
                        "label" => 'In Progress',
                    ],
                    [
                        "value" => 'DEFERRED',
                        "label" => 'Deferred',
                    ],
                    [
                        "value" => 'CANCELLED',
                        "label" => 'Cancelled',
                    ],
                    [
                        "value" => 'ABANDONED',
                        "label" => 'Abandoned',
                    ],
                    [
                        "value" => 'COMPLETED',
                        "label" => 'Completed',
                    ],
                ],
                'required' => true,
            ]),
            self::$field->create([
                'id'   => 'PROJECT_DETAILS',
                'label' => __('Project Details', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'CATEGORY_ID',
                'label' => __('Category', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'type' => 'select',
                'options' => $this->insightly->get_project_categories(),
            ]),
            self::$field->create([
                'id'   => 'OPPORTUNITY_ID',
                'label' => __('Opportunity', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'type' => 'select',
                'options' => $this->insightly->get_opportunities(),
            ]),
            self::$field->create([
                'id'   => 'PIPELINE_ID',
                'label' => __('Pipeline', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'type' => 'select',
                'options' => $this->insightly->get_pipelines(),
            ]),
            self::$field->create([
                'id'   => 'STAGE_ID',
                'label' => __('Stage', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'type' => 'select',
                'options' => $this->insightly->get_pipeline_stages(),
            ]),
            self::$field->create([
                'id'   => 'RESPONSIBLE_USER_ID',
                'label' => __('Responsible User', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'type' => 'select',
                'options' => $this->insightly->get_users(),
            ]),
        ];
    }

    /**
     * Task fields
     * @return array[]
     */
    private function tasks_fields() {
        return [
            self::$field->create([
                'id' => 'TITLE',
                'label' => __('Title', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'required' => true,
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'CATEGORY_ID',
                'label' => __('Category', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'type' => 'select',
                'options' => $this->insightly->get_task_categories(),
            ]),
            self::$field->create([
                'id'   => 'RESPONSIBLE_USER_ID',
                'label' => __('Responsible User', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'type' => 'select',
                'options' => $this->insightly->get_users(),
            ]),
            self::$field->create([
                'id'   => 'DUE_DATE',
                'label' => __('Due Date', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'START_DATE',
                'label' => __('Start Date', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'REMINDER_DATE_UTC',
                'label' => __('Reminder Date', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'PERCENT_COMPLETE',
                'label' => __('Progress', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'PRIORITY',
                'label' => __('Priority', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'type' => 'select',
                'options' => [
                    [
                        "value" => '1',
                        "label" => 'Low',
                    ],
                    [
                        "value" => '2',
                        "label" => 'Medium',
                    ],
                    [
                        "value" => '3',
                        "label" => 'High',
                    ],
                ],
            ]),
            self::$field->create([
                'id'   => 'STATUS',
                'label' => __('Status', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'type' => 'select',
                'options' => [
                    [
                        "value" => 'IN PROGRESS',
                        "label" => 'In Progress',
                    ],
                    [
                        "value" => 'COMPLETED',
                        "label" => 'Completed',
                    ],
                    [
                        "value" => 'DEFERRED',
                        "label" => 'Deferred',
                    ],
                    [
                        "value" => 'WAITING',
                        "label" => 'Waiting',
                    ],
                ],
            ]),
            self::$field->create([
                'id'   => 'DETAILS',
                'label' => __('Details', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'support' => ['tags'],
            ]),
            self::$field->create([
                'id'   => 'OPPORTUNITY_ID',
                'label' => __('Opportunity', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'type' => 'select',
                'options' => $this->insightly->get_opportunities(),
            ]),
            self::$field->create([
                'id'   => 'OPPORTUNITY_ID',
                'label' => __('Opportunity', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'type' => 'select',
                'options' => $this->insightly->get_opportunities(),
            ]),
            self::$field->create([
                'id'   => 'PROJECT_ID',
                'label' => __('Project', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'type' => 'select',
                'options' => $this->insightly->get_projects(),
            ]),
            self::$field->create([
                'id'   => 'STAGE_ID',
                'label' => __('Stage', 'ht-contactform'),
                'callback' => 'sanitize_text_field',
                'type' => 'select',
                'options' => $this->insightly->get_pipeline_stages(),
                'dependency' => [
                    'relation' => 'OR',
                    'rules' => [
                        [
                            'id' => 'OPPORTUNITY_ID',
                            'value' => '',
                            'compare' => '!==',
                        ],
                        [
                            'id' => 'PROJECT_ID',
                            'value' => '',
                            'compare' => '!==',
                        ],
                    ]
                ]
            ]),
        ];
    }
}