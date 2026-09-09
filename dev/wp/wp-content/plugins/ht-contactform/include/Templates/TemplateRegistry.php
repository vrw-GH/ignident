<?php
namespace HTContactForm\Templates;

if (!defined('ABSPATH')) exit;

/**
 * Template Registry
 * All form templates bundled with the plugin.
 * Each template has: id, title, description, category, fields[]
 */
class TemplateRegistry {

    private static $instance = null;

    public static function get_instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Return all templates
     */
    public function get_all(): array {
        return array_merge($this->general_templates(), $this->industry_templates());
    }

    /**
     * Get a single template by ID
     */
    public function get_by_id(string $id): ?array {
        foreach ($this->get_all() as $template) {
            if ($template['id'] === $id) {
                return $template;
            }
        }
        return null;
    }

    // -------------------------------------------------------------------------
    // GENERAL TEMPLATES (10)
    // -------------------------------------------------------------------------

    private function general_templates(): array {
        return [
            [
                'id'          => 'contact_us',
                'title'       => 'Contact Us',
                'description' => 'A simple, general-purpose contact form for any website.',
                'category'    => 'contact',
                'fields'      => [
                    $this->field('name_1', 'name', [
                        'admin_label'  => 'Name',
                        'name_format'  => 'first_last',
                        'label_position' => 'top',
                        'required'     => true,
                        'name_attribute' => 'name',
                    ]),
                    $this->field('email_1', 'email', [
                        'admin_label'  => 'Email',
                        'label'        => 'Email Address',
                        'placeholder'  => 'your@email.com',
                        'required'     => true,
                        'name_attribute' => 'email',
                    ]),
                    $this->field('input_1', 'input', [
                        'admin_label'  => 'Subject',
                        'label'        => 'Subject',
                        'placeholder'  => 'How can we help?',
                        'required'     => true,
                        'name_attribute' => 'subject',
                    ]),
                    $this->field('textarea_1', 'textarea', [
                        'admin_label'  => 'Message',
                        'label'        => 'Message',
                        'placeholder'  => 'Tell us more…',
                        'required'     => true,
                        'name_attribute' => 'message',
                    ]),
                    $this->submit('Submit', 'large'),
                ],
            ],

            [
                'id'          => 'customer_feedback',
                'title'       => 'Customer Feedback',
                'description' => 'Collect star ratings and written feedback from your customers.',
                'category'    => 'survey',
                'fields'      => [
                    $this->field('name_1', 'name', [
                        'admin_label'  => 'Name',
                        'name_format'  => 'first_last',
                        'required'     => true,
                        'name_attribute' => 'name',
                    ]),
                    $this->field('email_1', 'email', [
                        'admin_label'  => 'Email',
                        'label'        => 'Email Address',
                        'placeholder'  => 'your@email.com',
                        'required'     => true,
                        'name_attribute' => 'email',
                    ]),
                    $this->field('ratings_1', 'ratings', [
                        'admin_label'  => 'Overall Rating',
                        'label'        => 'Overall Rating',
                        'required'     => true,
                        'name_attribute' => 'rating',
                    ]),
                    $this->field('textarea_1', 'textarea', [
                        'admin_label'  => 'Feedback',
                        'label'        => 'Your Feedback',
                        'placeholder'  => 'Share your experience…',
                        'required'     => true,
                        'name_attribute' => 'feedback',
                    ]),
                    $this->submit('Send Feedback', 'large'),
                ],
            ],

            [
                'id'          => 'newsletter_signup',
                'title'       => 'Newsletter Signup',
                'description' => 'Grow your mailing list with a clean signup form.',
                'category'    => 'marketing',
                'fields'      => [
                    $this->field('name_1', 'name', [
                        'admin_label'  => 'Name',
                        'name_format'  => 'simple',
                        'required'     => true,
                        'name_attribute' => 'name',
                    ]),
                    $this->field('email_1', 'email', [
                        'admin_label'  => 'Email',
                        'label'        => 'Email Address',
                        'placeholder'  => 'your@email.com',
                        'required'     => true,
                        'name_attribute' => 'email',
                    ]),
                    $this->field('checkboxes_1', 'checkboxes', [
                        'admin_label'  => 'Interests',
                        'label'        => 'Topics you\'re interested in',
                        'required'     => false,
                        'layout'       => 'col2',
                        'name_attribute' => 'interests',
                        'options'      => [
                            ['label' => 'News & Updates',   'value' => 'news',      'selected' => false],
                            ['label' => 'Product Releases', 'value' => 'products',  'selected' => false],
                            ['label' => 'Tips & Tutorials', 'value' => 'tutorials', 'selected' => false],
                            ['label' => 'Promotions',       'value' => 'promos',    'selected' => false],
                        ],
                    ]),
                    $this->field('gdpr_1', 'gdpr', [
                        'admin_label'  => 'GDPR Consent',
                        'label'        => 'I agree to receive marketing emails and accept the privacy policy.',
                        'required'     => true,
                        'name_attribute' => 'gdpr_consent',
                    ]),
                    $this->submit('Subscribe', 'large'),
                ],
            ],

            [
                'id'          => 'support_request',
                'title'       => 'Support Request',
                'description' => 'Let users submit support tickets with priority and details.',
                'category'    => 'support',
                'fields'      => [
                    $this->field('name_1', 'name', [
                        'admin_label'  => 'Name',
                        'name_format'  => 'first_last',
                        'required'     => true,
                        'name_attribute' => 'name',
                    ]),
                    $this->field('email_1', 'email', [
                        'admin_label'  => 'Email',
                        'label'        => 'Email Address',
                        'placeholder'  => 'your@email.com',
                        'required'     => true,
                        'name_attribute' => 'email',
                    ]),
                    $this->field('input_1', 'input', [
                        'admin_label'  => 'Subject',
                        'label'        => 'Issue Subject',
                        'placeholder'  => 'Brief description of your issue',
                        'required'     => true,
                        'name_attribute' => 'subject',
                    ]),
                    $this->field('dropdown_1', 'dropdown', [
                        'admin_label'  => 'Priority',
                        'label'        => 'Priority Level',
                        'required'     => true,
                        'name_attribute' => 'priority',
                        'options'      => [
                            ['label' => 'Low',      'value' => 'low',      'selected' => true],
                            ['label' => 'Medium',   'value' => 'medium',   'selected' => false],
                            ['label' => 'High',     'value' => 'high',     'selected' => false],
                            ['label' => 'Critical', 'value' => 'critical', 'selected' => false],
                        ],
                    ]),
                    $this->field('textarea_1', 'textarea', [
                        'admin_label'  => 'Description',
                        'label'        => 'Describe your issue',
                        'placeholder'  => 'Please provide as much detail as possible…',
                        'required'     => true,
                        'name_attribute' => 'description',
                    ]),
                    $this->submit('Submit Ticket', 'large'),
                ],
            ],

            [
                'id'          => 'quote_request',
                'title'       => 'Quote Request',
                'description' => 'Allow potential clients to request a price quote for your services.',
                'category'    => 'business',
                'fields'      => [
                    $this->field('name_1', 'name', [
                        'admin_label'  => 'Name',
                        'name_format'  => 'first_last',
                        'required'     => true,
                        'name_attribute' => 'name',
                    ]),
                    $this->field('email_1', 'email', [
                        'admin_label'  => 'Email',
                        'label'        => 'Email Address',
                        'placeholder'  => 'your@email.com',
                        'required'     => true,
                        'name_attribute' => 'email',
                    ]),
                    $this->field('phone_1', 'phone', [
                        'admin_label'  => 'Phone',
                        'label'        => 'Phone Number',
                        'required'     => false,
                        'name_attribute' => 'phone',
                    ]),
                    $this->field('dropdown_1', 'dropdown', [
                        'admin_label'  => 'Service',
                        'label'        => 'Service Required',
                        'required'     => true,
                        'name_attribute' => 'service',
                        'options'      => [
                            ['label' => 'Web Design',      'value' => 'web_design',      'selected' => false],
                            ['label' => 'Development',     'value' => 'development',     'selected' => false],
                            ['label' => 'SEO',             'value' => 'seo',             'selected' => false],
                            ['label' => 'Digital Marketing','value' => 'digital_marketing','selected' => false],
                            ['label' => 'Other',           'value' => 'other',           'selected' => false],
                        ],
                    ]),
                    $this->field('textarea_1', 'textarea', [
                        'admin_label'  => 'Project Details',
                        'label'        => 'Tell us about your project',
                        'placeholder'  => 'Describe your project, goals, and timeline…',
                        'required'     => true,
                        'name_attribute' => 'project_details',
                    ]),
                    $this->submit('Request Quote', 'large'),
                ],
            ],

            [
                'id'          => 'event_registration',
                'title'       => 'Event Registration',
                'description' => 'Register attendees for your events, workshops, or conferences.',
                'category'    => 'events',
                'fields'      => [
                    $this->field('name_1', 'name', [
                        'admin_label'  => 'Full Name',
                        'name_format'  => 'first_last',
                        'required'     => true,
                        'name_attribute' => 'name',
                    ]),
                    $this->field('email_1', 'email', [
                        'admin_label'  => 'Email',
                        'label'        => 'Email Address',
                        'placeholder'  => 'your@email.com',
                        'required'     => true,
                        'name_attribute' => 'email',
                    ]),
                    $this->field('phone_1', 'phone', [
                        'admin_label'  => 'Phone',
                        'label'        => 'Phone Number',
                        'required'     => false,
                        'name_attribute' => 'phone',
                    ]),
                    $this->field('number_1', 'number', [
                        'admin_label'  => 'Attendees',
                        'label'        => 'Number of Attendees',
                        'placeholder'  => '1',
                        'required'     => true,
                        'name_attribute' => 'attendees',
                    ]),
                    $this->field('dropdown_1', 'dropdown', [
                        'admin_label'  => 'Dietary Requirements',
                        'label'        => 'Dietary Requirements',
                        'required'     => false,
                        'name_attribute' => 'dietary',
                        'options'      => [
                            ['label' => 'None',          'value' => 'none',        'selected' => true],
                            ['label' => 'Vegetarian',    'value' => 'vegetarian',  'selected' => false],
                            ['label' => 'Vegan',         'value' => 'vegan',       'selected' => false],
                            ['label' => 'Gluten-Free',   'value' => 'gluten_free', 'selected' => false],
                            ['label' => 'Halal',         'value' => 'halal',       'selected' => false],
                            ['label' => 'Kosher',        'value' => 'kosher',      'selected' => false],
                        ],
                    ]),
                    $this->submit('Register Now', 'large'),
                ],
            ],

            [
                'id'          => 'appointment_request',
                'title'       => 'Appointment Request',
                'description' => 'Let clients request an appointment with a preferred date and service.',
                'category'    => 'booking',
                'fields'      => [
                    $this->field('name_1', 'name', [
                        'admin_label'  => 'Name',
                        'name_format'  => 'first_last',
                        'required'     => true,
                        'name_attribute' => 'name',
                    ]),
                    $this->field('email_1', 'email', [
                        'admin_label'  => 'Email',
                        'label'        => 'Email Address',
                        'placeholder'  => 'your@email.com',
                        'required'     => true,
                        'name_attribute' => 'email',
                    ]),
                    $this->field('phone_1', 'phone', [
                        'admin_label'  => 'Phone',
                        'label'        => 'Phone Number',
                        'required'     => true,
                        'name_attribute' => 'phone',
                    ]),
                    $this->field('dropdown_1', 'dropdown', [
                        'admin_label'  => 'Service',
                        'label'        => 'Service Type',
                        'required'     => true,
                        'name_attribute' => 'service',
                        'options'      => [
                            ['label' => 'Consultation',  'value' => 'consultation', 'selected' => false],
                            ['label' => 'Follow-up',     'value' => 'followup',     'selected' => false],
                            ['label' => 'Treatment',     'value' => 'treatment',    'selected' => false],
                            ['label' => 'Other',         'value' => 'other',        'selected' => false],
                        ],
                    ]),
                    $this->field('date_1', 'date_time', [
                        'admin_label'  => 'Preferred Date',
                        'label'        => 'Preferred Date & Time',
                        'required'     => true,
                        'name_attribute' => 'preferred_date',
                    ]),
                    $this->field('textarea_1', 'textarea', [
                        'admin_label'  => 'Notes',
                        'label'        => 'Additional Notes',
                        'placeholder'  => 'Anything we should know beforehand?',
                        'required'     => false,
                        'name_attribute' => 'notes',
                    ]),
                    $this->submit('Book Appointment', 'large'),
                ],
            ],

            [
                'id'          => 'lead_generation',
                'title'       => 'Lead Generation',
                'description' => 'Capture qualified leads with contact info and area of interest.',
                'category'    => 'marketing',
                'fields'      => [
                    $this->field('name_1', 'name', [
                        'admin_label'  => 'Name',
                        'name_format'  => 'first_last',
                        'required'     => true,
                        'name_attribute' => 'name',
                    ]),
                    $this->field('email_1', 'email', [
                        'admin_label'  => 'Email',
                        'label'        => 'Work Email',
                        'placeholder'  => 'you@company.com',
                        'required'     => true,
                        'name_attribute' => 'email',
                    ]),
                    $this->field('phone_1', 'phone', [
                        'admin_label'  => 'Phone',
                        'label'        => 'Phone Number',
                        'required'     => false,
                        'name_attribute' => 'phone',
                    ]),
                    $this->field('input_1', 'input', [
                        'admin_label'  => 'Company',
                        'label'        => 'Company Name',
                        'placeholder'  => 'Your company',
                        'required'     => false,
                        'name_attribute' => 'company',
                    ]),
                    $this->field('dropdown_1', 'dropdown', [
                        'admin_label'  => 'Interest',
                        'label'        => 'I\'m interested in',
                        'required'     => true,
                        'name_attribute' => 'interest',
                        'options'      => [
                            ['label' => 'Product Demo',      'value' => 'demo',       'selected' => false],
                            ['label' => 'Pricing Info',      'value' => 'pricing',    'selected' => false],
                            ['label' => 'Partnership',       'value' => 'partnership','selected' => false],
                            ['label' => 'General Inquiry',   'value' => 'general',    'selected' => false],
                        ],
                    ]),
                    $this->submit('Get in Touch', 'large'),
                ],
            ],

            [
                'id'          => 'rsvp',
                'title'       => 'RSVP Form',
                'description' => 'Collect RSVPs for weddings, parties, or private events.',
                'category'    => 'events',
                'fields'      => [
                    $this->field('name_1', 'name', [
                        'admin_label'  => 'Full Name',
                        'name_format'  => 'first_last',
                        'required'     => true,
                        'name_attribute' => 'name',
                    ]),
                    $this->field('email_1', 'email', [
                        'admin_label'  => 'Email',
                        'label'        => 'Email Address',
                        'placeholder'  => 'your@email.com',
                        'required'     => true,
                        'name_attribute' => 'email',
                    ]),
                    $this->field('radio_1', 'radio', [
                        'admin_label'  => 'Attending',
                        'label'        => 'Will you be attending?',
                        'required'     => true,
                        'layout'       => 'inline',
                        'name_attribute' => 'attending',
                        'options'      => [
                            ['label' => 'Yes, I\'ll be there!', 'value' => 'yes', 'selected' => false],
                            ['label' => 'No, I can\'t make it', 'value' => 'no',  'selected' => false],
                        ],
                    ]),
                    $this->field('number_1', 'number', [
                        'admin_label'  => 'Guests',
                        'label'        => 'Number of Guests (including yourself)',
                        'placeholder'  => '1',
                        'required'     => false,
                        'name_attribute' => 'guests',
                    ]),
                    $this->field('checkboxes_1', 'checkboxes', [
                        'admin_label'  => 'Dietary Preferences',
                        'label'        => 'Dietary Preferences',
                        'required'     => false,
                        'layout'       => 'col2',
                        'name_attribute' => 'dietary',
                        'options'      => [
                            ['label' => 'Vegetarian',  'value' => 'vegetarian', 'selected' => false],
                            ['label' => 'Vegan',       'value' => 'vegan',      'selected' => false],
                            ['label' => 'Gluten-Free', 'value' => 'gluten_free','selected' => false],
                            ['label' => 'Halal',       'value' => 'halal',      'selected' => false],
                        ],
                    ]),
                    $this->submit('Send RSVP', 'large'),
                ],
            ],

            [
                'id'          => 'volunteer_application',
                'title'       => 'Volunteer Application',
                'description' => 'Recruit volunteers with a form covering availability and skills.',
                'category'    => 'nonprofit',
                'fields'      => [
                    $this->field('name_1', 'name', [
                        'admin_label'  => 'Full Name',
                        'name_format'  => 'first_last',
                        'required'     => true,
                        'name_attribute' => 'name',
                    ]),
                    $this->field('email_1', 'email', [
                        'admin_label'  => 'Email',
                        'label'        => 'Email Address',
                        'placeholder'  => 'your@email.com',
                        'required'     => true,
                        'name_attribute' => 'email',
                    ]),
                    $this->field('phone_1', 'phone', [
                        'admin_label'  => 'Phone',
                        'label'        => 'Phone Number',
                        'required'     => false,
                        'name_attribute' => 'phone',
                    ]),
                    $this->field('checkboxes_1', 'checkboxes', [
                        'admin_label'  => 'Availability',
                        'label'        => 'Days Available',
                        'required'     => true,
                        'layout'       => 'col3',
                        'name_attribute' => 'availability',
                        'options'      => [
                            ['label' => 'Monday',    'value' => 'mon', 'selected' => false],
                            ['label' => 'Tuesday',   'value' => 'tue', 'selected' => false],
                            ['label' => 'Wednesday', 'value' => 'wed', 'selected' => false],
                            ['label' => 'Thursday',  'value' => 'thu', 'selected' => false],
                            ['label' => 'Friday',    'value' => 'fri', 'selected' => false],
                            ['label' => 'Weekend',   'value' => 'wknd','selected' => false],
                        ],
                    ]),
                    $this->field('textarea_1', 'textarea', [
                        'admin_label'  => 'Skills',
                        'label'        => 'Relevant Skills & Experience',
                        'placeholder'  => 'Tell us about your skills and why you want to volunteer…',
                        'required'     => true,
                        'name_attribute' => 'skills',
                    ]),
                    $this->submit('Apply Now', 'large'),
                ],
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // INDUSTRY TEMPLATES (20)
    // -------------------------------------------------------------------------

    private function industry_templates(): array {
        return [
            [
                'id'          => 'job_application',
                'title'       => 'Job Application',
                'description' => 'A professional job application form with resume upload.',
                'category'    => 'hr',
                'fields'      => [
                    $this->field('name_1', 'name', ['admin_label' => 'Name', 'name_format' => 'first_last', 'required' => true, 'name_attribute' => 'name']),
                    $this->field('email_1', 'email', ['admin_label' => 'Email', 'label' => 'Email Address', 'placeholder' => 'your@email.com', 'required' => true, 'name_attribute' => 'email']),
                    $this->field('phone_1', 'phone', ['admin_label' => 'Phone', 'label' => 'Phone Number', 'required' => true, 'name_attribute' => 'phone']),
                    $this->field('dropdown_1', 'dropdown', ['admin_label' => 'Position', 'label' => 'Position Applied For', 'required' => true, 'name_attribute' => 'position', 'options' => [
                        ['label' => 'Software Engineer',  'value' => 'software_engineer',  'selected' => false],
                        ['label' => 'Product Manager',    'value' => 'product_manager',    'selected' => false],
                        ['label' => 'Designer',           'value' => 'designer',           'selected' => false],
                        ['label' => 'Marketing Manager',  'value' => 'marketing_manager',  'selected' => false],
                        ['label' => 'Other',              'value' => 'other',              'selected' => false],
                    ]]),
                    $this->field('dropdown_2', 'dropdown', ['admin_label' => 'Experience', 'label' => 'Years of Experience', 'required' => true, 'name_attribute' => 'experience', 'options' => [
                        ['label' => 'Less than 1 year', 'value' => 'lt1',  'selected' => false],
                        ['label' => '1–3 years',        'value' => '1_3',  'selected' => false],
                        ['label' => '3–5 years',        'value' => '3_5',  'selected' => false],
                        ['label' => '5–10 years',       'value' => '5_10', 'selected' => false],
                        ['label' => '10+ years',        'value' => 'gt10', 'selected' => false],
                    ]]),
                    $this->field('textarea_1', 'textarea', ['admin_label' => 'Cover Letter', 'label' => 'Cover Letter', 'placeholder' => 'Tell us why you\'re a great fit…', 'required' => true, 'name_attribute' => 'cover_letter']),
                    $this->field('file_1', 'file_upload', ['admin_label' => 'Resume', 'label' => 'Upload Your Resume', 'required' => true, 'name_attribute' => 'resume']),
                    $this->submit('Submit Application', 'large'),
                ],
            ],

            [
                'id'          => 'patient_intake',
                'title'       => 'Patient Intake Form',
                'description' => 'Collect patient information before a medical appointment.',
                'category'    => 'healthcare',
                'fields'      => [
                    $this->field('name_1', 'name', ['admin_label' => 'Patient Name', 'name_format' => 'first_last', 'required' => true, 'name_attribute' => 'name']),
                    $this->field('date_1', 'date_time', ['admin_label' => 'Date of Birth', 'label' => 'Date of Birth', 'required' => true, 'name_attribute' => 'dob']),
                    $this->field('email_1', 'email', ['admin_label' => 'Email', 'label' => 'Email Address', 'placeholder' => 'your@email.com', 'required' => true, 'name_attribute' => 'email']),
                    $this->field('phone_1', 'phone', ['admin_label' => 'Phone', 'label' => 'Phone Number', 'required' => true, 'name_attribute' => 'phone']),
                    $this->field('input_1', 'input', ['admin_label' => 'Insurance Provider', 'label' => 'Insurance Provider', 'placeholder' => 'e.g. Blue Cross Blue Shield', 'required' => false, 'name_attribute' => 'insurance']),
                    $this->field('dropdown_1', 'dropdown', ['admin_label' => 'Visit Reason', 'label' => 'Reason for Visit', 'required' => true, 'name_attribute' => 'visit_reason', 'options' => [
                        ['label' => 'New Patient',       'value' => 'new_patient',  'selected' => false],
                        ['label' => 'Follow-up',         'value' => 'followup',     'selected' => false],
                        ['label' => 'Routine Check-up',  'value' => 'routine',      'selected' => false],
                        ['label' => 'Urgent Care',       'value' => 'urgent',       'selected' => false],
                    ]]),
                    $this->field('textarea_1', 'textarea', ['admin_label' => 'Symptoms', 'label' => 'Current Symptoms or Concerns', 'placeholder' => 'Describe your symptoms…', 'required' => true, 'name_attribute' => 'symptoms']),
                    $this->submit('Submit Form', 'large'),
                ],
            ],

            [
                'id'          => 'hotel_booking',
                'title'       => 'Hotel Booking Request',
                'description' => 'Capture hotel reservation requests with dates and room preferences.',
                'category'    => 'booking',
                'fields'      => [
                    $this->field('name_1', 'name', ['admin_label' => 'Guest Name', 'name_format' => 'first_last', 'required' => true, 'name_attribute' => 'name']),
                    $this->field('email_1', 'email', ['admin_label' => 'Email', 'label' => 'Email Address', 'placeholder' => 'your@email.com', 'required' => true, 'name_attribute' => 'email']),
                    $this->field('phone_1', 'phone', ['admin_label' => 'Phone', 'label' => 'Phone Number', 'required' => true, 'name_attribute' => 'phone']),
                    $this->field('date_1', 'date_time', ['admin_label' => 'Check-in', 'label' => 'Check-in Date', 'required' => true, 'name_attribute' => 'checkin']),
                    $this->field('date_2', 'date_time', ['admin_label' => 'Check-out', 'label' => 'Check-out Date', 'required' => true, 'name_attribute' => 'checkout']),
                    $this->field('dropdown_1', 'dropdown', ['admin_label' => 'Room Type', 'label' => 'Room Type', 'required' => true, 'name_attribute' => 'room_type', 'options' => [
                        ['label' => 'Standard Room',   'value' => 'standard',  'selected' => false],
                        ['label' => 'Deluxe Room',     'value' => 'deluxe',    'selected' => false],
                        ['label' => 'Suite',           'value' => 'suite',     'selected' => false],
                        ['label' => 'Family Room',     'value' => 'family',    'selected' => false],
                    ]]),
                    $this->field('number_1', 'number', ['admin_label' => 'Guests', 'label' => 'Number of Guests', 'placeholder' => '1', 'required' => true, 'name_attribute' => 'guests']),
                    $this->field('textarea_1', 'textarea', ['admin_label' => 'Special Requests', 'label' => 'Special Requests', 'placeholder' => 'Any special requirements…', 'required' => false, 'name_attribute' => 'special_requests']),
                    $this->submit('Book Now', 'large'),
                ],
            ],

            [
                'id'          => 'real_estate_inquiry',
                'title'       => 'Real Estate Inquiry',
                'description' => 'Capture buyer or renter inquiries with property preferences.',
                'category'    => 'real_estate',
                'fields'      => [
                    $this->field('name_1', 'name', ['admin_label' => 'Name', 'name_format' => 'first_last', 'required' => true, 'name_attribute' => 'name']),
                    $this->field('email_1', 'email', ['admin_label' => 'Email', 'label' => 'Email Address', 'placeholder' => 'your@email.com', 'required' => true, 'name_attribute' => 'email']),
                    $this->field('phone_1', 'phone', ['admin_label' => 'Phone', 'label' => 'Phone Number', 'required' => true, 'name_attribute' => 'phone']),
                    $this->field('radio_1', 'radio', ['admin_label' => 'Inquiry Type', 'label' => 'I am looking to', 'required' => true, 'layout' => 'inline', 'name_attribute' => 'inquiry_type', 'options' => [
                        ['label' => 'Buy',  'value' => 'buy',  'selected' => false],
                        ['label' => 'Rent', 'value' => 'rent', 'selected' => false],
                    ]]),
                    $this->field('dropdown_1', 'dropdown', ['admin_label' => 'Property Type', 'label' => 'Property Type', 'required' => true, 'name_attribute' => 'property_type', 'options' => [
                        ['label' => 'Apartment',     'value' => 'apartment',  'selected' => false],
                        ['label' => 'House',         'value' => 'house',      'selected' => false],
                        ['label' => 'Condo',         'value' => 'condo',      'selected' => false],
                        ['label' => 'Commercial',    'value' => 'commercial', 'selected' => false],
                        ['label' => 'Land',          'value' => 'land',       'selected' => false],
                    ]]),
                    $this->field('dropdown_2', 'dropdown', ['admin_label' => 'Budget', 'label' => 'Budget Range', 'required' => true, 'name_attribute' => 'budget', 'options' => [
                        ['label' => 'Under $100K',       'value' => 'lt100k',  'selected' => false],
                        ['label' => '$100K – $300K',     'value' => '100_300', 'selected' => false],
                        ['label' => '$300K – $600K',     'value' => '300_600', 'selected' => false],
                        ['label' => '$600K – $1M',       'value' => '600k_1m', 'selected' => false],
                        ['label' => 'Over $1M',          'value' => 'gt1m',    'selected' => false],
                    ]]),
                    $this->field('input_1', 'input', ['admin_label' => 'Location', 'label' => 'Preferred Location / Area', 'placeholder' => 'City, neighborhood, or zip code', 'required' => false, 'name_attribute' => 'location']),
                    $this->field('textarea_1', 'textarea', ['admin_label' => 'Message', 'label' => 'Additional Requirements', 'placeholder' => 'Tell us more about what you\'re looking for…', 'required' => false, 'name_attribute' => 'message']),
                    $this->submit('Send Inquiry', 'large'),
                ],
            ],

            [
                'id'          => 'course_registration',
                'title'       => 'Course Registration',
                'description' => 'Register students for online or in-person courses.',
                'category'    => 'education',
                'fields'      => [
                    $this->field('name_1', 'name', ['admin_label' => 'Name', 'name_format' => 'first_last', 'required' => true, 'name_attribute' => 'name']),
                    $this->field('email_1', 'email', ['admin_label' => 'Email', 'label' => 'Email Address', 'placeholder' => 'your@email.com', 'required' => true, 'name_attribute' => 'email']),
                    $this->field('phone_1', 'phone', ['admin_label' => 'Phone', 'label' => 'Phone Number', 'required' => false, 'name_attribute' => 'phone']),
                    $this->field('dropdown_1', 'dropdown', ['admin_label' => 'Course', 'label' => 'Select Course', 'required' => true, 'name_attribute' => 'course', 'options' => [
                        ['label' => 'Web Development',   'value' => 'web_dev',    'selected' => false],
                        ['label' => 'Data Science',      'value' => 'data_sci',   'selected' => false],
                        ['label' => 'Digital Marketing', 'value' => 'dig_market', 'selected' => false],
                        ['label' => 'Graphic Design',    'value' => 'design',     'selected' => false],
                        ['label' => 'Business Strategy', 'value' => 'business',   'selected' => false],
                    ]]),
                    $this->field('radio_1', 'radio', ['admin_label' => 'Experience Level', 'label' => 'Your Experience Level', 'required' => true, 'layout' => 'inline', 'name_attribute' => 'experience', 'options' => [
                        ['label' => 'Beginner',     'value' => 'beginner',     'selected' => false],
                        ['label' => 'Intermediate', 'value' => 'intermediate', 'selected' => false],
                        ['label' => 'Advanced',     'value' => 'advanced',     'selected' => false],
                    ]]),
                    $this->field('dropdown_2', 'dropdown', ['admin_label' => 'How did you hear', 'label' => 'How did you hear about us?', 'required' => false, 'name_attribute' => 'referral', 'options' => [
                        ['label' => 'Google Search', 'value' => 'google',   'selected' => false],
                        ['label' => 'Social Media',  'value' => 'social',   'selected' => false],
                        ['label' => 'Friend/Family', 'value' => 'referral', 'selected' => false],
                        ['label' => 'Advertisement', 'value' => 'ad',       'selected' => false],
                        ['label' => 'Other',         'value' => 'other',    'selected' => false],
                    ]]),
                    $this->submit('Register Now', 'large'),
                ],
            ],

            [
                'id'          => 'membership_application',
                'title'       => 'Membership Application',
                'description' => 'Process membership sign-ups with plan selection and consent.',
                'category'    => 'business',
                'fields'      => [
                    $this->field('name_1', 'name', ['admin_label' => 'Name', 'name_format' => 'first_last', 'required' => true, 'name_attribute' => 'name']),
                    $this->field('email_1', 'email', ['admin_label' => 'Email', 'label' => 'Email Address', 'placeholder' => 'your@email.com', 'required' => true, 'name_attribute' => 'email']),
                    $this->field('phone_1', 'phone', ['admin_label' => 'Phone', 'label' => 'Phone Number', 'required' => true, 'name_attribute' => 'phone']),
                    $this->field('radio_1', 'radio', ['admin_label' => 'Membership Plan', 'label' => 'Select Membership Plan', 'required' => true, 'layout' => 'col1', 'name_attribute' => 'plan', 'options' => [
                        ['label' => 'Basic – Free',        'value' => 'basic',    'selected' => false],
                        ['label' => 'Pro – $19/month',     'value' => 'pro',      'selected' => false],
                        ['label' => 'Business – $49/month','value' => 'business', 'selected' => false],
                    ]]),
                    $this->field('date_1', 'date_time', ['admin_label' => 'Date of Birth', 'label' => 'Date of Birth', 'required' => false, 'name_attribute' => 'dob']),
                    $this->field('gdpr_1', 'gdpr', ['admin_label' => 'Terms Agreement', 'label' => 'I agree to the Terms & Conditions and Privacy Policy.', 'required' => true, 'name_attribute' => 'terms']),
                    $this->submit('Apply for Membership', 'large'),
                ],
            ],

            [
                'id'          => 'conference_registration',
                'title'       => 'Conference Registration',
                'description' => 'Register attendees for conferences with session selection.',
                'category'    => 'events',
                'fields'      => [
                    $this->field('name_1', 'name', ['admin_label' => 'Name', 'name_format' => 'first_last', 'required' => true, 'name_attribute' => 'name']),
                    $this->field('email_1', 'email', ['admin_label' => 'Email', 'label' => 'Email Address', 'placeholder' => 'your@email.com', 'required' => true, 'name_attribute' => 'email']),
                    $this->field('input_1', 'input', ['admin_label' => 'Organization', 'label' => 'Company / Organization', 'placeholder' => 'Your organization', 'required' => false, 'name_attribute' => 'organization']),
                    $this->field('input_2', 'input', ['admin_label' => 'Job Title', 'label' => 'Job Title', 'placeholder' => 'e.g. Senior Developer', 'required' => false, 'name_attribute' => 'job_title']),
                    $this->field('checkboxes_1', 'checkboxes', ['admin_label' => 'Sessions', 'label' => 'Sessions to Attend', 'required' => true, 'layout' => 'col2', 'name_attribute' => 'sessions', 'options' => [
                        ['label' => 'Opening Keynote',  'value' => 'keynote',   'selected' => false],
                        ['label' => 'Workshop A',       'value' => 'workshop_a','selected' => false],
                        ['label' => 'Workshop B',       'value' => 'workshop_b','selected' => false],
                        ['label' => 'Panel Discussion', 'value' => 'panel',     'selected' => false],
                        ['label' => 'Networking Lunch', 'value' => 'lunch',     'selected' => false],
                        ['label' => 'Closing Ceremony', 'value' => 'closing',   'selected' => false],
                    ]]),
                    $this->field('dropdown_1', 'dropdown', ['admin_label' => 'Dietary', 'label' => 'Dietary Requirements', 'required' => false, 'name_attribute' => 'dietary', 'options' => [
                        ['label' => 'None',         'value' => 'none',        'selected' => true],
                        ['label' => 'Vegetarian',   'value' => 'vegetarian',  'selected' => false],
                        ['label' => 'Vegan',        'value' => 'vegan',       'selected' => false],
                        ['label' => 'Gluten-Free',  'value' => 'gluten_free', 'selected' => false],
                        ['label' => 'Halal',        'value' => 'halal',       'selected' => false],
                    ]]),
                    $this->submit('Complete Registration', 'large'),
                ],
            ],

            [
                'id'          => 'partnership_inquiry',
                'title'       => 'Partnership Inquiry',
                'description' => 'Collect partnership and collaboration proposals from businesses.',
                'category'    => 'business',
                'fields'      => [
                    $this->field('name_1', 'name', ['admin_label' => 'Name', 'name_format' => 'first_last', 'required' => true, 'name_attribute' => 'name']),
                    $this->field('email_1', 'email', ['admin_label' => 'Email', 'label' => 'Business Email', 'placeholder' => 'you@company.com', 'required' => true, 'name_attribute' => 'email']),
                    $this->field('input_1', 'input', ['admin_label' => 'Company', 'label' => 'Company Name', 'placeholder' => 'Your company', 'required' => true, 'name_attribute' => 'company']),
                    $this->field('website_url_1', 'website_url', ['admin_label' => 'Website', 'label' => 'Company Website', 'placeholder' => 'https://yourwebsite.com', 'required' => false, 'name_attribute' => 'website']),
                    $this->field('dropdown_1', 'dropdown', ['admin_label' => 'Partnership Type', 'label' => 'Type of Partnership', 'required' => true, 'name_attribute' => 'partnership_type', 'options' => [
                        ['label' => 'Reseller / Affiliate',  'value' => 'reseller',    'selected' => false],
                        ['label' => 'Technology Integration','value' => 'tech',         'selected' => false],
                        ['label' => 'Co-Marketing',          'value' => 'co_marketing', 'selected' => false],
                        ['label' => 'Distribution',          'value' => 'distribution', 'selected' => false],
                        ['label' => 'Other',                 'value' => 'other',        'selected' => false],
                    ]]),
                    $this->field('textarea_1', 'textarea', ['admin_label' => 'Proposal', 'label' => 'Partnership Proposal', 'placeholder' => 'Describe the partnership opportunity and how we can collaborate…', 'required' => true, 'name_attribute' => 'proposal']),
                    $this->submit('Submit Inquiry', 'large'),
                ],
            ],

            [
                'id'          => 'bug_report',
                'title'       => 'Bug Report',
                'description' => 'Structured form for users to report bugs with severity levels.',
                'category'    => 'support',
                'fields'      => [
                    $this->field('name_1', 'name', ['admin_label' => 'Name', 'name_format' => 'first_last', 'required' => true, 'name_attribute' => 'name']),
                    $this->field('email_1', 'email', ['admin_label' => 'Email', 'label' => 'Email Address', 'placeholder' => 'your@email.com', 'required' => true, 'name_attribute' => 'email']),
                    $this->field('dropdown_1', 'dropdown', ['admin_label' => 'Product', 'label' => 'Product / Version', 'required' => true, 'name_attribute' => 'product', 'options' => [
                        ['label' => 'Version 1.x', 'value' => 'v1', 'selected' => false],
                        ['label' => 'Version 2.x', 'value' => 'v2', 'selected' => false],
                        ['label' => 'Version 3.x', 'value' => 'v3', 'selected' => false],
                    ]]),
                    $this->field('input_1', 'input', ['admin_label' => 'Bug Title', 'label' => 'Bug Title', 'placeholder' => 'Short description of the bug', 'required' => true, 'name_attribute' => 'bug_title']),
                    $this->field('textarea_1', 'textarea', ['admin_label' => 'Steps', 'label' => 'Steps to Reproduce', 'placeholder' => "1. Go to...\n2. Click on...\n3. See error", 'required' => true, 'name_attribute' => 'steps']),
                    $this->field('radio_1', 'radio', ['admin_label' => 'Severity', 'label' => 'Severity', 'required' => true, 'layout' => 'inline', 'name_attribute' => 'severity', 'options' => [
                        ['label' => 'Low',      'value' => 'low',      'selected' => false],
                        ['label' => 'Medium',   'value' => 'medium',   'selected' => false],
                        ['label' => 'High',     'value' => 'high',     'selected' => false],
                        ['label' => 'Critical', 'value' => 'critical', 'selected' => false],
                    ]]),
                    $this->field('file_1', 'file_upload', ['admin_label' => 'Screenshot', 'label' => 'Screenshot (optional)', 'required' => false, 'name_attribute' => 'screenshot']),
                    $this->submit('Report Bug', 'large'),
                ],
            ],

            [
                'id'          => 'feature_request',
                'title'       => 'Feature Request',
                'description' => 'Let users suggest new features with priority and use case.',
                'category'    => 'support',
                'fields'      => [
                    $this->field('name_1', 'name', ['admin_label' => 'Name', 'name_format' => 'first_last', 'required' => true, 'name_attribute' => 'name']),
                    $this->field('email_1', 'email', ['admin_label' => 'Email', 'label' => 'Email Address', 'placeholder' => 'your@email.com', 'required' => true, 'name_attribute' => 'email']),
                    $this->field('dropdown_1', 'dropdown', ['admin_label' => 'Product', 'label' => 'Which product?', 'required' => true, 'name_attribute' => 'product', 'options' => [
                        ['label' => 'Web App',    'value' => 'web',    'selected' => false],
                        ['label' => 'Mobile App', 'value' => 'mobile', 'selected' => false],
                        ['label' => 'API',        'value' => 'api',    'selected' => false],
                        ['label' => 'Other',      'value' => 'other',  'selected' => false],
                    ]]),
                    $this->field('input_1', 'input', ['admin_label' => 'Feature Title', 'label' => 'Feature Title', 'placeholder' => 'What feature would you like?', 'required' => true, 'name_attribute' => 'feature_title']),
                    $this->field('textarea_1', 'textarea', ['admin_label' => 'Description', 'label' => 'Describe the Feature', 'placeholder' => 'What problem does this solve? How would it work?', 'required' => true, 'name_attribute' => 'description']),
                    $this->field('radio_1', 'radio', ['admin_label' => 'Priority', 'label' => 'How important is this to you?', 'required' => true, 'layout' => 'inline', 'name_attribute' => 'priority', 'options' => [
                        ['label' => 'Nice to have', 'value' => 'low',    'selected' => false],
                        ['label' => 'Important',    'value' => 'medium', 'selected' => false],
                        ['label' => 'Critical',     'value' => 'high',   'selected' => false],
                    ]]),
                    $this->submit('Submit Request', 'large'),
                ],
            ],

            [
                'id'          => 'restaurant_reservation',
                'title'       => 'Restaurant Reservation',
                'description' => 'Accept dining reservations with party size and occasion details.',
                'category'    => 'booking',
                'fields'      => [
                    $this->field('name_1', 'name', ['admin_label' => 'Name', 'name_format' => 'first_last', 'required' => true, 'name_attribute' => 'name']),
                    $this->field('email_1', 'email', ['admin_label' => 'Email', 'label' => 'Email Address', 'placeholder' => 'your@email.com', 'required' => true, 'name_attribute' => 'email']),
                    $this->field('phone_1', 'phone', ['admin_label' => 'Phone', 'label' => 'Phone Number', 'required' => true, 'name_attribute' => 'phone']),
                    $this->field('date_1', 'date_time', ['admin_label' => 'Date & Time', 'label' => 'Preferred Date & Time', 'required' => true, 'name_attribute' => 'reservation_date']),
                    $this->field('number_1', 'number', ['admin_label' => 'Party Size', 'label' => 'Party Size', 'placeholder' => '2', 'required' => true, 'name_attribute' => 'party_size']),
                    $this->field('dropdown_1', 'dropdown', ['admin_label' => 'Occasion', 'label' => 'Special Occasion', 'required' => false, 'name_attribute' => 'occasion', 'options' => [
                        ['label' => 'None',          'value' => 'none',        'selected' => true],
                        ['label' => 'Birthday',      'value' => 'birthday',    'selected' => false],
                        ['label' => 'Anniversary',   'value' => 'anniversary', 'selected' => false],
                        ['label' => 'Business Meal', 'value' => 'business',    'selected' => false],
                        ['label' => 'Date Night',    'value' => 'date',        'selected' => false],
                    ]]),
                    $this->field('checkboxes_1', 'checkboxes', ['admin_label' => 'Dietary Restrictions', 'label' => 'Dietary Restrictions', 'required' => false, 'layout' => 'col2', 'name_attribute' => 'dietary', 'options' => [
                        ['label' => 'Vegetarian',  'value' => 'vegetarian', 'selected' => false],
                        ['label' => 'Vegan',       'value' => 'vegan',      'selected' => false],
                        ['label' => 'Gluten-Free', 'value' => 'gf',         'selected' => false],
                        ['label' => 'Nut Allergy', 'value' => 'nut',        'selected' => false],
                    ]]),
                    $this->submit('Reserve Table', 'large'),
                ],
            ],

            [
                'id'          => 'scholarship_application',
                'title'       => 'Scholarship Application',
                'description' => 'Accept scholarship applications with academic and financial details.',
                'category'    => 'education',
                'fields'      => [
                    $this->field('name_1', 'name', ['admin_label' => 'Name', 'name_format' => 'first_last', 'required' => true, 'name_attribute' => 'name']),
                    $this->field('email_1', 'email', ['admin_label' => 'Email', 'label' => 'Email Address', 'placeholder' => 'your@email.com', 'required' => true, 'name_attribute' => 'email']),
                    $this->field('phone_1', 'phone', ['admin_label' => 'Phone', 'label' => 'Phone Number', 'required' => false, 'name_attribute' => 'phone']),
                    $this->field('dropdown_1', 'dropdown', ['admin_label' => 'Program', 'label' => 'Program of Study', 'required' => true, 'name_attribute' => 'program', 'options' => [
                        ['label' => 'Business Administration', 'value' => 'business',    'selected' => false],
                        ['label' => 'Computer Science',        'value' => 'cs',          'selected' => false],
                        ['label' => 'Engineering',             'value' => 'engineering', 'selected' => false],
                        ['label' => 'Medicine',                'value' => 'medicine',    'selected' => false],
                        ['label' => 'Arts & Humanities',       'value' => 'arts',        'selected' => false],
                        ['label' => 'Other',                   'value' => 'other',       'selected' => false],
                    ]]),
                    $this->field('input_1', 'input', ['admin_label' => 'GPA', 'label' => 'Current GPA', 'placeholder' => 'e.g. 3.8', 'required' => true, 'name_attribute' => 'gpa']),
                    $this->field('textarea_1', 'textarea', ['admin_label' => 'Financial Need', 'label' => 'Financial Need Statement', 'placeholder' => 'Describe your financial situation and why you need this scholarship…', 'required' => true, 'name_attribute' => 'financial_need']),
                    $this->field('textarea_2', 'textarea', ['admin_label' => 'Activities', 'label' => 'Extracurricular Activities & Achievements', 'placeholder' => 'List clubs, sports, volunteering, awards…', 'required' => false, 'name_attribute' => 'activities']),
                    $this->submit('Submit Application', 'large'),
                ],
            ],

            [
                'id'          => 'employee_onboarding',
                'title'       => 'Employee Onboarding',
                'description' => 'Collect new hire details including emergency contact information.',
                'category'    => 'hr',
                'fields'      => [
                    $this->field('name_1', 'name', ['admin_label' => 'Employee Name', 'name_format' => 'first_last', 'required' => true, 'name_attribute' => 'name']),
                    $this->field('email_1', 'email', ['admin_label' => 'Personal Email', 'label' => 'Personal Email Address', 'placeholder' => 'your@email.com', 'required' => true, 'name_attribute' => 'email']),
                    $this->field('phone_1', 'phone', ['admin_label' => 'Phone', 'label' => 'Phone Number', 'required' => true, 'name_attribute' => 'phone']),
                    $this->field('date_1', 'date_time', ['admin_label' => 'Start Date', 'label' => 'Start Date', 'required' => true, 'name_attribute' => 'start_date']),
                    $this->field('dropdown_1', 'dropdown', ['admin_label' => 'Department', 'label' => 'Department', 'required' => true, 'name_attribute' => 'department', 'options' => [
                        ['label' => 'Engineering',  'value' => 'engineering', 'selected' => false],
                        ['label' => 'Marketing',    'value' => 'marketing',   'selected' => false],
                        ['label' => 'Sales',        'value' => 'sales',       'selected' => false],
                        ['label' => 'HR',           'value' => 'hr',          'selected' => false],
                        ['label' => 'Finance',      'value' => 'finance',     'selected' => false],
                        ['label' => 'Operations',   'value' => 'operations',  'selected' => false],
                    ]]),
                    $this->field('input_1', 'input', ['admin_label' => 'Emergency Contact Name', 'label' => 'Emergency Contact Name', 'placeholder' => 'Full name', 'required' => true, 'name_attribute' => 'emergency_name']),
                    $this->field('phone_2', 'phone', ['admin_label' => 'Emergency Contact Phone', 'label' => 'Emergency Contact Phone', 'required' => true, 'name_attribute' => 'emergency_phone']),
                    $this->submit('Submit Details', 'large'),
                ],
            ],

            [
                'id'          => 'vendor_registration',
                'title'       => 'Vendor Registration',
                'description' => 'Onboard new vendors or suppliers with business details.',
                'category'    => 'business',
                'fields'      => [
                    $this->field('name_1', 'name', ['admin_label' => 'Contact Name', 'name_format' => 'first_last', 'required' => true, 'name_attribute' => 'name']),
                    $this->field('email_1', 'email', ['admin_label' => 'Email', 'label' => 'Business Email', 'placeholder' => 'you@company.com', 'required' => true, 'name_attribute' => 'email']),
                    $this->field('input_1', 'input', ['admin_label' => 'Company Name', 'label' => 'Company / Business Name', 'placeholder' => 'Your company', 'required' => true, 'name_attribute' => 'company']),
                    $this->field('website_url_1', 'website_url', ['admin_label' => 'Website', 'label' => 'Company Website', 'placeholder' => 'https://yourwebsite.com', 'required' => false, 'name_attribute' => 'website']),
                    $this->field('dropdown_1', 'dropdown', ['admin_label' => 'Business Type', 'label' => 'Business Type', 'required' => true, 'name_attribute' => 'business_type', 'options' => [
                        ['label' => 'Manufacturer',  'value' => 'manufacturer', 'selected' => false],
                        ['label' => 'Distributor',   'value' => 'distributor',  'selected' => false],
                        ['label' => 'Wholesaler',    'value' => 'wholesaler',   'selected' => false],
                        ['label' => 'Retailer',      'value' => 'retailer',     'selected' => false],
                        ['label' => 'Service Provider', 'value' => 'service',   'selected' => false],
                    ]]),
                    $this->field('textarea_1', 'textarea', ['admin_label' => 'Products/Services', 'label' => 'Products / Services Offered', 'placeholder' => 'Describe what you offer…', 'required' => true, 'name_attribute' => 'products_services']),
                    $this->submit('Register as Vendor', 'large'),
                ],
            ],

            [
                'id'          => 'satisfaction_survey',
                'title'       => 'Satisfaction Survey',
                'description' => 'Measure customer satisfaction with ratings and NPS scoring.',
                'category'    => 'survey',
                'fields'      => [
                    $this->field('name_1', 'name', ['admin_label' => 'Name', 'name_format' => 'first_last', 'required' => false, 'name_attribute' => 'name']),
                    $this->field('email_1', 'email', ['admin_label' => 'Email', 'label' => 'Email Address', 'placeholder' => 'your@email.com', 'required' => false, 'name_attribute' => 'email']),
                    $this->field('ratings_1', 'ratings', ['admin_label' => 'Overall Rating', 'label' => 'Overall Satisfaction', 'required' => true, 'name_attribute' => 'overall_rating']),
                    $this->field('radio_1', 'radio', ['admin_label' => 'Would Recommend', 'label' => 'Would you recommend us to a friend?', 'required' => true, 'layout' => 'inline', 'name_attribute' => 'would_recommend', 'options' => [
                        ['label' => 'Definitely',     'value' => 'definitely',     'selected' => false],
                        ['label' => 'Probably',       'value' => 'probably',       'selected' => false],
                        ['label' => 'Probably Not',   'value' => 'probably_not',   'selected' => false],
                        ['label' => 'Definitely Not', 'value' => 'definitely_not', 'selected' => false],
                    ]]),
                    $this->field('nps_1', 'nps', ['admin_label' => 'NPS Score', 'label' => 'How likely are you to recommend us? (0–10)', 'required' => true, 'name_attribute' => 'nps']),
                    $this->field('textarea_1', 'textarea', ['admin_label' => 'Improvements', 'label' => 'What can we improve?', 'placeholder' => 'Share your thoughts…', 'required' => false, 'name_attribute' => 'improvements']),
                    $this->submit('Submit Survey', 'large'),
                ],
            ],

            [
                'id'          => 'complaint_form',
                'title'       => 'Complaint Form',
                'description' => 'Allow customers to formally lodge complaints with resolution requests.',
                'category'    => 'support',
                'fields'      => [
                    $this->field('name_1', 'name', ['admin_label' => 'Name', 'name_format' => 'first_last', 'required' => true, 'name_attribute' => 'name']),
                    $this->field('email_1', 'email', ['admin_label' => 'Email', 'label' => 'Email Address', 'placeholder' => 'your@email.com', 'required' => true, 'name_attribute' => 'email']),
                    $this->field('dropdown_1', 'dropdown', ['admin_label' => 'Complaint Type', 'label' => 'Type of Complaint', 'required' => true, 'name_attribute' => 'complaint_type', 'options' => [
                        ['label' => 'Product Quality',     'value' => 'product',   'selected' => false],
                        ['label' => 'Customer Service',    'value' => 'service',   'selected' => false],
                        ['label' => 'Delivery / Shipping', 'value' => 'delivery',  'selected' => false],
                        ['label' => 'Billing / Payment',   'value' => 'billing',   'selected' => false],
                        ['label' => 'Website / Technical', 'value' => 'technical', 'selected' => false],
                        ['label' => 'Other',               'value' => 'other',     'selected' => false],
                    ]]),
                    $this->field('date_1', 'date_time', ['admin_label' => 'Incident Date', 'label' => 'Date of Incident', 'required' => true, 'name_attribute' => 'incident_date']),
                    $this->field('textarea_1', 'textarea', ['admin_label' => 'Description', 'label' => 'Describe Your Complaint', 'placeholder' => 'Please provide a detailed description of the issue…', 'required' => true, 'name_attribute' => 'description']),
                    $this->field('textarea_2', 'textarea', ['admin_label' => 'Desired Resolution', 'label' => 'Desired Resolution', 'placeholder' => 'What outcome would resolve this for you?', 'required' => false, 'name_attribute' => 'resolution']),
                    $this->submit('Submit Complaint', 'large'),
                ],
            ],

            [
                'id'          => 'pet_adoption',
                'title'       => 'Pet Adoption Application',
                'description' => 'Screen potential pet adopters with home and experience questions.',
                'category'    => 'nonprofit',
                'fields'      => [
                    $this->field('name_1', 'name', ['admin_label' => 'Name', 'name_format' => 'first_last', 'required' => true, 'name_attribute' => 'name']),
                    $this->field('email_1', 'email', ['admin_label' => 'Email', 'label' => 'Email Address', 'placeholder' => 'your@email.com', 'required' => true, 'name_attribute' => 'email']),
                    $this->field('phone_1', 'phone', ['admin_label' => 'Phone', 'label' => 'Phone Number', 'required' => true, 'name_attribute' => 'phone']),
                    $this->field('radio_1', 'radio', ['admin_label' => 'Home Type', 'label' => 'Type of Home', 'required' => true, 'layout' => 'inline', 'name_attribute' => 'home_type', 'options' => [
                        ['label' => 'House with yard', 'value' => 'house_yard',  'selected' => false],
                        ['label' => 'House no yard',   'value' => 'house',       'selected' => false],
                        ['label' => 'Apartment',       'value' => 'apartment',   'selected' => false],
                    ]]),
                    $this->field('dropdown_1', 'dropdown', ['admin_label' => 'Pet Experience', 'label' => 'Experience with Pets', 'required' => true, 'name_attribute' => 'experience', 'options' => [
                        ['label' => 'First-time owner',    'value' => 'first_time', 'selected' => false],
                        ['label' => 'Previous owner',      'value' => 'previous',   'selected' => false],
                        ['label' => 'Current pet owner',   'value' => 'current',    'selected' => false],
                        ['label' => 'Professional/Trainer','value' => 'pro',        'selected' => false],
                    ]]),
                    $this->field('textarea_1', 'textarea', ['admin_label' => 'Why Adopt', 'label' => 'Why do you want to adopt?', 'placeholder' => 'Tell us about your lifestyle and why you\'re a great fit…', 'required' => true, 'name_attribute' => 'why_adopt']),
                    $this->submit('Submit Application', 'large'),
                ],
            ],

            [
                'id'          => 'donation_form',
                'title'       => 'Donation Form',
                'description' => 'Accept charitable donations with preset and custom amount options.',
                'category'    => 'nonprofit',
                'fields'      => [
                    $this->field('name_1', 'name', ['admin_label' => 'Name', 'name_format' => 'first_last', 'required' => true, 'name_attribute' => 'name']),
                    $this->field('email_1', 'email', ['admin_label' => 'Email', 'label' => 'Email Address', 'placeholder' => 'your@email.com', 'required' => true, 'name_attribute' => 'email']),
                    $this->field('radio_1', 'radio', ['admin_label' => 'Donation Amount', 'label' => 'Select Donation Amount', 'required' => true, 'layout' => 'button', 'name_attribute' => 'donation_amount', 'options' => [
                        ['label' => '$10',        'value' => '10',     'selected' => false],
                        ['label' => '$25',        'value' => '25',     'selected' => false],
                        ['label' => '$50',        'value' => '50',     'selected' => false],
                        ['label' => '$100',       'value' => '100',    'selected' => false],
                        ['label' => 'Custom',     'value' => 'custom', 'selected' => false],
                    ]]),
                    $this->field('number_1', 'number', ['admin_label' => 'Custom Amount', 'label' => 'Custom Amount ($)', 'placeholder' => 'Enter amount', 'required' => false, 'name_attribute' => 'custom_amount']),
                    $this->field('textarea_1', 'textarea', ['admin_label' => 'Dedication', 'label' => 'Dedicate this donation (optional)', 'placeholder' => 'In honor of…', 'required' => false, 'name_attribute' => 'dedication']),
                    $this->field('gdpr_1', 'gdpr', ['admin_label' => 'Consent', 'label' => 'I agree to the terms and conditions of this donation.', 'required' => true, 'name_attribute' => 'consent']),
                    $this->submit('Donate Now', 'large'),
                ],
            ],

            [
                'id'          => 'wedding_rsvp',
                'title'       => 'Wedding RSVP',
                'description' => 'Elegant wedding RSVP form with meal choice and song request.',
                'category'    => 'events',
                'fields'      => [
                    $this->field('name_1', 'name', ['admin_label' => 'Guest Name', 'name_format' => 'first_last', 'required' => true, 'name_attribute' => 'name']),
                    $this->field('email_1', 'email', ['admin_label' => 'Email', 'label' => 'Email Address', 'placeholder' => 'your@email.com', 'required' => true, 'name_attribute' => 'email']),
                    $this->field('radio_1', 'radio', ['admin_label' => 'Attending', 'label' => 'Will you be joining us?', 'required' => true, 'layout' => 'inline', 'name_attribute' => 'attending', 'options' => [
                        ['label' => 'Joyfully Accepts',  'value' => 'yes', 'selected' => false],
                        ['label' => 'Regretfully Declines','value' => 'no','selected' => false],
                    ]]),
                    $this->field('radio_2', 'radio', ['admin_label' => 'Plus One', 'label' => 'Will you be bringing a plus one?', 'required' => false, 'layout' => 'inline', 'name_attribute' => 'plus_one', 'options' => [
                        ['label' => 'Yes', 'value' => 'yes', 'selected' => false],
                        ['label' => 'No',  'value' => 'no',  'selected' => false],
                    ]]),
                    $this->field('dropdown_1', 'dropdown', ['admin_label' => 'Meal Choice', 'label' => 'Meal Preference', 'required' => false, 'name_attribute' => 'meal_choice', 'options' => [
                        ['label' => 'Chicken',      'value' => 'chicken',   'selected' => false],
                        ['label' => 'Beef',         'value' => 'beef',      'selected' => false],
                        ['label' => 'Fish',         'value' => 'fish',      'selected' => false],
                        ['label' => 'Vegetarian',   'value' => 'vegetarian','selected' => false],
                    ]]),
                    $this->field('input_1', 'input', ['admin_label' => 'Song Request', 'label' => 'Song Request', 'placeholder' => 'What song gets you on the dance floor?', 'required' => false, 'name_attribute' => 'song_request']),
                    $this->submit('Send RSVP', 'large'),
                ],
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------------------------

    /**
     * Build a field array
     */
    private function field(string $id, string $type, array $settings): array {
        if ($type === 'name' && !isset($settings['names'])) {
            $settings['names'] = $this->default_names($settings['required'] ?? false);
        }

        if ($type === 'date_time' && !isset($settings['format'])) {
            $settings['format'] = 'Y-m-d';
        }

        return [
            'id'       => $id,
            'type'     => $type,
            'settings' => $settings,
        ];
    }

    /**
     * Default per-subfield labels and placeholders for a name field.
     * Without these the First/Last sub-inputs render with no label or placeholder.
     */
    private function default_names(bool $required): array {
        $required_message = __('This field is required', 'ht-contactform');

        return [
            'simple' => [
                'label'            => __('Name', 'ht-contactform'),
                'placeholder'      => __('Your Name', 'ht-contactform'),
                'value'            => '',
                'help_message'     => '',
                'required'         => $required,
                'required_message' => $required_message,
            ],
            'first_name' => [
                'label'            => __('First Name', 'ht-contactform'),
                'placeholder'      => __('First Name', 'ht-contactform'),
                'value'            => '',
                'help_message'     => '',
                'required'         => $required,
                'required_message' => $required_message,
            ],
            'middle_name' => [
                'label'            => __('Middle Name', 'ht-contactform'),
                'placeholder'      => __('Middle Name', 'ht-contactform'),
                'value'            => '',
                'help_message'     => '',
                'required'         => false,
                'required_message' => $required_message,
            ],
            'last_name' => [
                'label'            => __('Last Name', 'ht-contactform'),
                'placeholder'      => __('Last Name', 'ht-contactform'),
                'value'            => '',
                'help_message'     => '',
                'required'         => $required,
                'required_message' => $required_message,
            ],
        ];
    }

    /**
     * Build a submit button field
     */
    private function submit(string $label = 'Submit'): array {
        return $this->field('submit_1', 'submit', [
            'admin_label'  => 'Submit Button',
            'default_value' => $label,
            'style'        => 'default',
            'align'        => 'left',
        ]);
    }
}
