<?php

namespace HTContactForm;

use HTContactFormAdmin\Includes\Services\Webhook;
use HTContactForm\Integrations\Mailchimp;
use HTContactForm\Integrations\Slack;
use HTContactForm\Integrations\Discord;
use HTContactForm\Integrations\ActiveCampaign;
use HTContactForm\Integrations\MailerLite;
use HTContactForm\Integrations\Zapier;
use HTContactForm\Integrations\SupportGenix;
use HTContactForm\Integrations\ConstantContact;
use HTContactForm\Integrations\Brevo;
use HTContactForm\Integrations\Insightly;
use HTContactForm\Integrations\OnepageCRM;
use HTContactForm\Integrations\GetResponse;
use HTContactForm\Integrations\Drip;
use HTContactForm\Integrations\Moosend;
use HTContactForm\Integrations\iContact;
use HTContactForm\Integrations\MailPoet;
use HTContactForm\Integrations\Notion;
use HTContactForm\Integrations\Trello;
use HTContactForm\Integrations\HubSpot;
use HTContactForm\Integrations\ZohoCRM;
use HTContactForm\Integrations\TwentyCRM;

/**
 * Integrations Class
 * 
 * Handles integrations functionality for sending form data to external services 
 * like webhook, Mailchimp, Slack, etc.
 */
class Integrations {

    /** @var self|null Singleton instance */
    private static $instance = null;

    private $integrations_settings = [];
    
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
     * 
     * Initializes the class and sets up WordPress hooks
     */
    public function __construct() {
        $this->integrations_settings = get_option('ht_form_integrations', []);
        add_action('ht_form/after_submission', [$this, 'process_integrations'], 10, 3);
    }

    /**
     * Process all enabled integrations for a form submission
     * 
     * @param array $form Form configuration
     * @param array $form_data Submitted form data
     * @param array $meta Entry metadata
     * @return void
     */
    public function process_integrations($form, $form_data, $meta) {
        // Process each integration type
        $this->webhook($form, $form_data, $meta);
        $this->mailchimp($form, $form_data, $meta);
        $this->slack($form, $form_data, $meta);
        $this->discord($form, $form_data, $meta);
        $this->activecampaign($form, $form_data, $meta);
        $this->mailerlite($form, $form_data, $meta);
        $this->zapier($form, $form_data, $meta);
        if(class_exists('Apbd_wps_ht_contact_form')) {
            $this->supportgenix($form, $form_data, $meta);
        }
        $this->constantcontact($form, $form_data, $meta);
        $this->brevo($form, $form_data, $meta);
        $this->insightly($form, $form_data, $meta);
        $this->onepagecrm($form, $form_data, $meta);
        $this->getresponse($form, $form_data, $meta);
        $this->drip($form, $form_data, $meta);
        $this->moosend($form, $form_data, $meta);
        $this->icontact($form, $form_data, $meta);
        $this->mailpoet($form, $form_data, $meta);
        $this->notion($form, $form_data, $meta);
        $this->trello($form, $form_data, $meta);
        $this->hubspot($form, $form_data, $meta);
        $this->zohocrm($form, $form_data, $meta);
        $this->twentycrm($form, $form_data, $meta);
        // Allow other integrations to be processed
        do_action('ht_form/process_custom_integrations', $form, $form_data, $meta);
    }

    /**
     * Send data to webhook
     * 
     * @param array $form Form configuration
     * @param array $form_data Submitted form data
     * @param array $meta Entry metadata
     * @return void
     */
    public function webhook($form, $form_data, $meta) {
        try {
            if (empty($this->integrations_settings) || empty($form['id'])) {
                return;
            }
            
            // Check if webhook integration is enabled globally
            if (empty($this->integrations_settings['webhook']['enabled'])) {
                return;
            }
            
            // Get form-specific integrations
            $integration_list = $this->get_form_integrations($form['id'], 'webhook');
            if (empty($integration_list)) {
                return;
            }
            
            // Process each webhook integration
            $webhook = Webhook::get_instance($form, $form_data, $meta);
            foreach ($integration_list as $integration) {
                $result = $webhook->send((object) $integration);
                if (is_wp_error($result)) {
                    error_log(sprintf(
                        'HT Contact Form Webhook Error (Form ID: %s): %s',
                        $form['id'],
                        $result->get_error_message()
                    ));
                }
            }
        } catch (\Exception $e) {
            error_log(sprintf(
                'HT Contact Form Webhook Exception (Form ID: %s): %s',
                $form['id'] ?? 'unknown',
                $e->getMessage()
            ));
        }
    }

    /**
     * Send data to Mailchimp
     * 
     * @param array $form Form configuration
     * @param array $form_data Submitted form data
     * @param array $meta Entry metadata
     * @return void
     */
    public function mailchimp($form, $form_data, $meta) {
        try {
            if (empty($this->integrations_settings) || empty($form['id'])) {
                return;
            }
            
            // Check if Mailchimp integration is enabled globally and API key exists
            if (empty($this->integrations_settings['mailchimp']['enabled']) || 
                empty($this->integrations_settings['mailchimp']['api_key'])) {
                return;
            }
            
            // Get form-specific integrations
            $integration_list = $this->get_form_integrations($form['id'], 'mailchimp');
            if (empty($integration_list)) {
                return;
            }
            
            // Process each Mailchimp integration
            $mailchimp = Mailchimp::get_instance($this->integrations_settings['mailchimp']['api_key']);
            foreach ($integration_list as $integration) {
                $result = $mailchimp->subscribe((object) $integration, $form, $form_data, $meta);
                if (is_wp_error($result)) {
                    error_log(sprintf(
                        'HT Contact Form Mailchimp Error (Form ID: %s): %s',
                        $form['id'],
                        $result->get_error_message()
                    ));
                }
            }
        } catch (\Exception $e) {
            error_log(sprintf(
                'HT Contact Form Mailchimp Exception (Form ID: %s): %s',
                $form['id'] ?? 'unknown',
                $e->getMessage()
            ));
        }
    }

    /**
     * Send data to Slack
     * 
     * @param array $form Form configuration
     * @param array $form_data Submitted form data
     * @param array $meta Entry metadata
     * @return void
     */
    public function slack($form, $form_data, $meta) {
        try {
            if (empty($this->integrations_settings) || empty($form['id'])) {
                return;
            }
            
            // Check if Slack integration is enabled globally
            if (empty($this->integrations_settings['slack']['enabled'])) {
                return;
            }
            
            // Get form-specific integrations
            $integration_list = $this->get_form_integrations($form['id'], 'slack');
            if (empty($integration_list)) {
                return;
            }
            
            // Process each Slack integration
            $slack = Slack::get_instance($form, $form_data, $meta);
            foreach ($integration_list as $integration) {
                $result = $slack->send((object) $integration);
                if (is_wp_error($result)) {
                    error_log(sprintf(
                        'HT Contact Form Slack Error (Form ID: %s): %s',
                        $form['id'],
                        $result->get_error_message()
                    ));
                }
            }
        } catch (\Exception $e) {
            error_log(sprintf(
                'HT Contact Form Slack Exception (Form ID: %s): %s',
                $form['id'] ?? 'unknown',
                $e->getMessage()
            ));
        }
    }


    /**
     * Send data to Discord
     * 
     * @param array $form Form configuration
     * @param array $form_data Submitted form data
     * @param array $meta Entry metadata
     * @return void
     */
    public function discord($form, $form_data, $meta) {
        try {
            if (empty($this->integrations_settings) || empty($form['id'])) {
                return;
            }
            
            // Check if Discord integration is enabled globally
            if (empty($this->integrations_settings['discord']['enabled'])) {
                return;
            }
            
            // Get form-specific integrations
            $integration_list = $this->get_form_integrations($form['id'], 'discord');
            if (empty($integration_list)) {
                return;
            }
            
            // Process each Discord integration
            $discord = Discord::get_instance($form, $form_data, $meta);
            foreach ($integration_list as $integration) {
                $result = $discord->send((object) $integration);
                if (is_wp_error($result)) {
                    error_log(sprintf(
                        'HT Contact Form Discord Error (Form ID: %s): %s',
                        $form['id'],
                        $result->get_error_message()
                    ));
                }
            }
        } catch (\Exception $e) {
            error_log(sprintf(
                'HT Contact Form Discord Exception (Form ID: %s): %s',
                $form['id'] ?? 'unknown',
                $e->getMessage()
            ));
        }
    }

    /**
     * Send data to ActiveCampaign
     * 
     * @param array $form Form configuration
     * @param array $form_data Submitted form data
     * @param array $meta Entry metadata
     * @return void
     */
    public function activecampaign($form, $form_data, $meta) {
        try {
            if (empty($this->integrations_settings) || empty($form['id'])) {
                return;
            }
            
            // Check if ActiveCampaign integration is enabled globally and API key exists
            if (empty($this->integrations_settings['activecampaign']['enabled']) || 
                empty($this->integrations_settings['activecampaign']['api_key'])) {
                return;
            }
            
            // Get form-specific integrations
            $integration_list = $this->get_form_integrations($form['id'], 'activecampaign');
            if (empty($integration_list)) {
                return;
            }
            
            // Process each ActiveCampaign integration
            $activecampaign = ActiveCampaign::get_instance($this->integrations_settings['activecampaign']['api_key']);
            foreach ($integration_list as $integration) {
                $result = $activecampaign->subscribe((object) $integration, $form, $form_data, $meta);
                if (is_wp_error($result)) {
                    error_log(sprintf(
                        'HT Contact Form ActiveCampaign Error (Form ID: %s): %s',
                        $form['id'],
                        $result->get_error_message()
                    ));
                }
            }
        } catch (\Exception $e) {
            error_log(sprintf(
                'HT Contact Form ActiveCampaign Exception (Form ID: %s): %s',
                $form['id'] ?? 'unknown',
                $e->getMessage()
            ));
        }
    }

    /**
     * Send data to MailerLite
     * 
     * @param array $form Form configuration
     * @param array $form_data Submitted form data
     * @param array $meta Entry metadata
     * @return void
     */
    public function mailerlite($form, $form_data, $meta) {
        try {
            if (empty($this->integrations_settings) || empty($form['id'])) {
                return;
            }
            
            // Check if MailerLite integration is enabled globally and API key exists
            if (empty($this->integrations_settings['mailerlite']['enabled']) || 
                empty($this->integrations_settings['mailerlite']['api_key'])) {
                return;
            }
            
            // Get form-specific integrations
            $integration_list = $this->get_form_integrations($form['id'], 'mailerlite');
            if (empty($integration_list)) {
                return;
            }
            
            // Process each MailerLite integration
            $mailerlite = MailerLite::get_instance($this->integrations_settings['mailerlite']['api_key']);
            foreach ($integration_list as $integration) {
                $result = $mailerlite->subscribe((object) $integration, $form, $form_data, $meta);
                if (is_wp_error($result)) {
                    error_log(sprintf(
                        'HT Contact Form MailerLite Error (Form ID: %s): %s',
                        $form['id'],
                        $result->get_error_message()
                    ));
                }
            }
        } catch (\Exception $e) {
            error_log(sprintf(
                'HT Contact Form MailerLite Exception (Form ID: %s): %s',
                $form['id'] ?? 'unknown',
                $e->getMessage()
            ));
        }
    }

    /**
     * Send data to Zapier
     * 
     * @param array $form Form configuration
     * @param array $form_data Submitted form data
     * @param array $meta Entry metadata
     * @return void
     */
    public function zapier($form, $form_data, $meta) {
        try {
            if (empty($this->integrations_settings) || empty($form['id'])) {
                return;
            }
            
            // Check if Zapier integration is enabled globally
            if (empty($this->integrations_settings['zapier']['enabled'])) {
                return;
            }
            
            // Get form-specific integrations
            $integration_list = $this->get_form_integrations($form['id'], 'zapier');
            if (empty($integration_list)) {
                return;
            }
            
            // Process each Zapier integration
            $zapier = Zapier::get_instance($form, $form_data, $meta);
            foreach ($integration_list as $integration) {
                $result = $zapier->send((object) $integration);
                if (is_wp_error($result)) {
                    error_log(sprintf(
                        'HT Contact Form Zapier Error (Form ID: %s): %s',
                        $form['id'],
                        $result->get_error_message()
                    ));
                }
            }
        } catch (\Exception $e) {
            error_log(sprintf(
                'HT Contact Form Zapier Exception (Form ID: %s): %s',
                $form['id'] ?? 'unknown',
                $e->getMessage()
            ));
        }
    }

    /**
     * Send data to SupportGenix
     * 
     * @param array $form Form configuration
     * @param array $form_data Submitted form data
     * @param array $meta Entry metadata
     * @return void
     */
    public function supportgenix($form, $form_data, $meta) {
        try {
            if (empty($this->integrations_settings) || empty($form['id'])) {
                return;
            }
            
            // Check if SupportGenix integration is enabled globally
            if (empty($this->integrations_settings['supportgenix']['enabled'])) {
                return;
            }
            
            // Get form-specific integrations
            $integration_list = $this->get_form_integrations($form['id'], 'supportgenix');
            if (empty($integration_list)) {
                return;
            }
            
            // Process each SupportGenix integration
            $supportgenix = new SupportGenix($form, $form_data, $meta);
            foreach ($integration_list as $integration) {
                $result = $supportgenix->send((object) $integration);
                if (is_wp_error($result)) {
                    error_log(sprintf(
                        'HT Contact Form SupportGenix Error (Form ID: %s): %s',
                        $form['id'],
                        $result->get_error_message()
                    ));
                }
            }
        } catch (\Exception $e) {
            error_log(sprintf(
                'HT Contact Form SupportGenix Exception (Form ID: %s): %s',
                $form['id'] ?? 'unknown',
                $e->getMessage()
            ));
        }
    }

    /**
     * Send data to ConstantContact
     * 
     * @param array $form Form configuration
     * @param array $form_data Submitted form data
     * @param array $meta Entry metadata
     * @return void
     */
    public function constantcontact($form, $form_data, $meta) {
        try {
            if (empty($this->integrations_settings) || empty($form['id'])) {
                return;
            }
            
            // Check if ConstantContact integration is enabled globally
            if (empty($this->integrations_settings['constantcontact']['enabled'])) {
                return;
            }
            
            // Get form-specific integrations
            $integration_list = $this->get_form_integrations($form['id'], 'constantcontact');
            if (empty($integration_list)) {
                return;
            }
            
            // Process each ConstantContact integration
            $constantcontact = new ConstantContact($form, $form_data, $meta);
            foreach ($integration_list as $integration) {
                $result = $constantcontact->subscribe((object) $integration);
                if (is_wp_error($result)) {
                    error_log(sprintf(
                        'HT Contact Form ConstantContact Error (Form ID: %s): %s',
                        $form['id'],
                        $result->get_error_message()
                    ));
                }
            }
        } catch (\Exception $e) {
            error_log(sprintf(
                'HT Contact Form ConstantContact Exception (Form ID: %s): %s',
                $form['id'] ?? 'unknown',
                $e->getMessage()
            ));
        }
    }

    /**
     * Send data to Brevo
     * 
     * @param array $form Form configuration
     * @param array $form_data Submitted form data
     * @param array $meta Entry metadata
     * @return void
     */
    public function brevo($form, $form_data, $meta) {
        try {
            if (empty($this->integrations_settings) || empty($form['id'])) {
                return;
            }
            
            // Check if Brevo integration is enabled globally
            if (empty($this->integrations_settings['brevo']['enabled'])) {
                return;
            }
            
            // Get form-specific integrations
            $integration_list = $this->get_form_integrations($form['id'], 'brevo');
            if (empty($integration_list)) {
                return;
            }
            // Process each Brevo integration
            $brevo = new Brevo();
            foreach ($integration_list as $integration) {
                $result = $brevo->subscribe((object) $integration, $form, $form_data, $meta);
                if (is_wp_error($result)) {
                    error_log(sprintf(
                        'HT Contact Form Brevo Error (Form ID: %s): %s',
                        $form['id'],
                        $result->get_error_message()
                    ));
                }
            }
        } catch (\Exception $e) {
            error_log(sprintf(
                'HT Contact Form Brevo Exception (Form ID: %s): %s',
                $form['id'] ?? 'unknown',
                $e->getMessage()
            ));
        }
    }

    /**
     * Send data to Insightly
     * 
     * @param array $form Form configuration
     * @param array $form_data Submitted form data
     * @param array $meta Entry metadata
     * @return void
     */
    public function insightly($form, $form_data, $meta) {
        try {
            if (empty($this->integrations_settings) || empty($form['id'])) {
                return;
            }
            
            // Check if Insightly integration is enabled globally
            if (empty($this->integrations_settings['insightly']['enabled'])) {
                return;
            }
            
            // Get form-specific integrations
            $integration_list = $this->get_form_integrations($form['id'], 'insightly');
            if (empty($integration_list)) {
                return;
            }
            // Process each Insightly integration
            $insightly = new Insightly();
            foreach ($integration_list as $integration) {
                $result = $insightly->subscribe((object) $integration, $form, $form_data, $meta);
                if (is_wp_error($result)) {
                    error_log(sprintf(
                        'HT Contact Form Insightly Error (Form ID: %s): %s',
                        $form['id'],
                        $result->get_error_message()
                    ));
                }
            }
        } catch (\Exception $e) {
            error_log(sprintf(
                'HT Contact Form Insightly Exception (Form ID: %s): %s',
                $form['id'] ?? 'unknown',
                $e->getMessage()
            ));
        }
    }

    /**
     * Send data to OnepageCRM
     *
     * @param array $form Form configuration
     * @param array $form_data Submitted form data
     * @param array $meta Entry metadata
     * @return void
     */
    public function onepagecrm($form, $form_data, $meta) {
        try {
            if (empty($this->integrations_settings) || empty($form['id'])) {
                return;
            }

            // Check if OnepageCRM integration is enabled globally
            if (empty($this->integrations_settings['onepagecrm']['enabled']) ||
                empty($this->integrations_settings['onepagecrm']['api_key']) ||
                empty($this->integrations_settings['onepagecrm']['user_id'])) {
                return;
            }

            // Get form-specific integrations
            $integration_list = $this->get_form_integrations($form['id'], 'onepagecrm');
            if (empty($integration_list)) {
                return;
            }

            // Process each OnepageCRM integration
            $onepagecrm = OnepageCRM::get_instance(
                $this->integrations_settings['onepagecrm']['api_key'],
                $this->integrations_settings['onepagecrm']['user_id']
            );

            foreach ($integration_list as $integration) {
                if($integration['service'] === 'contact') {
                    $result = $onepagecrm->create_contact((object) $integration, $form, $form_data, $meta);
                }
                if($integration['service'] === 'deal') {
                    $result = $onepagecrm->create_deal((object) $integration, $form, $form_data);
                }
                if($integration['service'] === 'note') {
                    $result = $onepagecrm->add_note((object) $integration, $form, $form_data);
                }
                if($integration['service'] === 'action') {
                    $result = $onepagecrm->create_action((object) $integration, $form, $form_data);
                }
                if (is_wp_error($result)) {
                    error_log(sprintf(
                        'HT Contact Form OnepageCRM Error (Form ID: %s): %s',
                        $form['id'],
                        $result->get_error_message()
                    ));
                }
            }
        } catch (\Exception $e) {
            error_log(sprintf(
                'HT Contact Form OnepageCRM Exception (Form ID: %s): %s',
                $form['id'] ?? 'unknown',
                $e->getMessage()
            ));
        }
    }

    /**
     * Handle GetResponse integration
     *
     * @param array $form Form configuration
     * @param array $form_data Form submission data
     * @param array $meta Submission metadata
     * @return void
     */
    public function getresponse($form, $form_data, $meta) {
        try {
            if (empty($this->integrations_settings) || empty($form['id'])) {
                return;
            }

            if (empty($this->integrations_settings['getresponse']['enabled']) ||
                empty($this->integrations_settings['getresponse']['api_key'])) {
                return;
            }

            $integration_list = $this->get_form_integrations($form['id'], 'getresponse');

            if (empty($integration_list)) {
                return;
            }

            $getresponse = GetResponse::get_instance(
                $this->integrations_settings['getresponse']['api_key']
            );

            foreach ($integration_list as $integration) {
                $result = $getresponse->subscribe((object) $integration, $form, $form_data, $meta);
                if (is_wp_error($result)) {
                    error_log(sprintf(
                        'HT Contact Form GetResponse Error (Form ID: %s): %s',
                        $form['id'],
                        $result->get_error_message()
                    ));
                }
            }
        } catch (\Exception $e) {
            error_log(sprintf(
                'HT Contact Form GetResponse Exception (Form ID: %s): %s',
                $form['id'] ?? 'unknown',
                $e->getMessage()
            ));
        }
    }

    /**
     * Handle Drip integration
     *
     * @param array $form Form configuration
     * @param array $form_data Form submission data
     * @param array $meta Submission metadata
     * @return void
     */
    public function drip($form, $form_data, $meta) {
        try {
            if (empty($this->integrations_settings) || empty($form['id'])) {
                return;
            }

            if (empty($this->integrations_settings['drip']['enabled']) ||
                empty($this->integrations_settings['drip']['api_key'])) {
                return;
            }

            $integration_list = $this->get_form_integrations($form['id'], 'drip');

            if (empty($integration_list)) {
                return;
            }

            $drip = Drip::get_instance(
                $this->integrations_settings['drip']['api_key']
            );

            foreach ($integration_list as $integration) {
                $result = $drip->subscribe((object) $integration, $form, $form_data, $meta);
                if (is_wp_error($result)) {
                    error_log(sprintf(
                        'HT Contact Form Drip Error (Form ID: %s): %s',
                        $form['id'],
                        $result->get_error_message()
                    ));
                }
            }
        } catch (\Exception $e) {
            error_log(sprintf(
                'HT Contact Form Drip Exception (Form ID: %s): %s',
                $form['id'] ?? 'unknown',
                $e->getMessage()
            ));
        }
    }

    /**
     * Handle Moosend integration
     *
     * @param array $form Form configuration
     * @param array $form_data Form submission data
     * @param array $meta Submission metadata
     * @return void
     */
    public function moosend($form, $form_data, $meta) {
        try {
            if (empty($this->integrations_settings) || empty($form['id'])) {
                return;
            }

            if (empty($this->integrations_settings['moosend']['api_key'])) {
                return;
            }

            $integration_list = $this->get_form_integrations($form['id'], 'moosend');
            if (empty($integration_list)) {
                return;
            }

            $moosend = Moosend::get_instance(
                $this->integrations_settings['moosend']['api_key']
            );

            foreach ($integration_list as $integration) {
                $result = $moosend->subscribe((object) $integration, $form, $form_data, $meta);
                if (is_wp_error($result)) {
                    error_log(sprintf(
                        'HT Contact Form Moosend Error (Form ID: %s): %s',
                        $form['id'],
                        $result->get_error_message()
                    ));
                }
            }
        } catch (\Exception $e) {
            error_log(sprintf(
                'HT Contact Form Moosend Exception (Form ID: %s): %s',
                $form['id'] ?? 'unknown',
                $e->getMessage()
            ));
        }
    }

    /**
     * Process iContact integration
     *
     * @param array $form Form configuration
     * @param array $form_data Submitted form data
     * @param array $meta Entry metadata
     * @return void
     */
    public function icontact($form, $form_data, $meta) {
        try {
            if (empty($this->integrations_settings) || empty($form['id'])) {
                return;
            }

            // Check if iContact credentials are configured
            if (empty($this->integrations_settings['icontact']['app_id']) ||
                empty($this->integrations_settings['icontact']['username']) ||
                empty($this->integrations_settings['icontact']['password']) ||
                empty($this->integrations_settings['icontact']['account_id']) ||
                empty($this->integrations_settings['icontact']['client_folder_id'])) {
                return;
            }

            $integration_list = $this->get_form_integrations($form['id'], 'icontact');
            if (empty($integration_list)) {
                return;
            }

            $icontact = iContact::get_instance([
                'app_id'           => $this->integrations_settings['icontact']['app_id'],
                'username'         => $this->integrations_settings['icontact']['username'],
                'password'         => $this->integrations_settings['icontact']['password'],
                'account_id'       => $this->integrations_settings['icontact']['account_id'],
                'client_folder_id' => $this->integrations_settings['icontact']['client_folder_id'],
            ]);

            foreach ($integration_list as $integration) {
                $result = $icontact->subscribe((object) $integration, $form, $form_data, $meta);
                if (is_wp_error($result)) {
                    error_log(sprintf(
                        'HT Contact Form iContact Error (Form ID: %s): %s',
                        $form['id'],
                        $result->get_error_message()
                    ));
                }
            }
        } catch (\Exception $e) {
            error_log(sprintf(
                'HT Contact Form iContact Exception (Form ID: %s): %s',
                $form['id'] ?? 'unknown',
                $e->getMessage()
            ));
        }
    }

    /**
     * Send data to MailPoet
     *
     * @param array $form Form data
     * @param array $form_data Form submission data
     * @param array $meta Meta data
     */
    public function mailpoet($form, $form_data, $meta) {
        try {
            if (empty($this->integrations_settings) || empty($form['id'])) {
                return;
            }

            // Check if MailPoet is enabled in global settings
            if (empty($this->integrations_settings['mailpoet'])) {
                return;
            }

            $integration_list = $this->get_form_integrations($form['id'], 'mailpoet');
            if (empty($integration_list)) {
                return;
            }

            $mailpoet = MailPoet::get_instance();

            // Check if MailPoet plugin is active
            if (!$mailpoet->is_active()) {
                error_log('HT Contact Form: MailPoet plugin is not installed or activated.');
                return;
            }

            foreach ($integration_list as $integration) {
                $result = $mailpoet->subscribe((object) $integration, $form, $form_data, $meta);
                if (is_wp_error($result)) {
                    error_log(sprintf(
                        'HT Contact Form MailPoet Error (Form ID: %s): %s',
                        $form['id'],
                        $result->get_error_message()
                    ));
                }
            }
        } catch (\Exception $e) {
            error_log(sprintf(
                'HT Contact Form MailPoet Exception (Form ID: %s): %s',
                $form['id'] ?? 'unknown',
                $e->getMessage()
            ));
        }
    }

    /**
     * Handle Notion integration
     *
     * @param array $form Form configuration
     * @param array $form_data Form submission data
     * @param array $meta Meta data
     */
    public function notion($form, $form_data, $meta) {
        try {
            if (empty($this->integrations_settings) || empty($form['id'])) {
                return;
            }

            if (empty($this->integrations_settings['notion']['api_key'])) {
                return;
            }

            $integration_list = $this->get_form_integrations($form['id'], 'notion');
            if (empty($integration_list)) {
                return;
            }

            $notion = Notion::get_instance(
                $this->integrations_settings['notion']['api_key']
            );

            foreach ($integration_list as $integration) {
                $result = $notion->create_page((object) $integration, $form, $form_data, $meta);
                if (is_wp_error($result)) {
                    error_log(sprintf(
                        'HT Contact Form Notion Error (Form ID: %s): %s',
                        $form['id'],
                        $result->get_error_message()
                    ));
                }
            }
        } catch (\Exception $e) {
            error_log(sprintf(
                'HT Contact Form Notion Exception (Form ID: %s): %s',
                $form['id'] ?? 'unknown',
                $e->getMessage()
            ));
        }
    }

    /**
     * Handle Trello integration
     *
     * @param array $form Form configuration
     * @param array $form_data Form submission data
     * @param array $meta Meta data
     */
    public function trello($form, $form_data, $meta) {
        try {
            if (empty($this->integrations_settings) || empty($form['id'])) {
                return;
            }

            if (empty($this->integrations_settings['trello']['api_key']) ||
                empty($this->integrations_settings['trello']['api_token'])) {
                return;
            }

            $integration_list = $this->get_form_integrations($form['id'], 'trello');
            if (empty($integration_list)) {
                return;
            }

            $trello = Trello::get_instance(
                $this->integrations_settings['trello']['api_key'],
                $this->integrations_settings['trello']['api_token']
            );

            foreach ($integration_list as $integration) {
                $result = $trello->create_card((object) $integration, $form, $form_data, $meta);
                if (is_wp_error($result)) {
                    error_log(sprintf(
                        'HT Contact Form Trello Error (Form ID: %s): %s',
                        $form['id'],
                        $result->get_error_message()
                    ));
                }
            }
        } catch (\Exception $e) {
            error_log(sprintf(
                'HT Contact Form Trello Exception (Form ID: %s): %s',
                $form['id'] ?? 'unknown',
                $e->getMessage()
            ));
        }
    }

    /**
     * Handle HubSpot integration
     *
     * @param array $form Form configuration
     * @param array $form_data Form submission data
     * @param array $meta Submission metadata
     * @return void
     */
    public function hubspot($form, $form_data, $meta) {
        try {
            if (empty($this->integrations_settings) || empty($form['id'])) {
                return;
            }

            if (empty($this->integrations_settings['hubspot']['enabled']) ||
                empty($this->integrations_settings['hubspot']['access_token'])) {
                return;
            }

            $integration_list = $this->get_form_integrations($form['id'], 'hubspot');

            if (empty($integration_list)) {
                return;
            }

            $hubspot = HubSpot::get_instance(
                $this->integrations_settings['hubspot']['access_token']
            );

            foreach ($integration_list as $integration) {
                $result = $hubspot->subscribe((object) $integration, $form, $form_data, $meta);
                if (is_wp_error($result)) {
                    error_log(sprintf(
                        'HT Contact Form HubSpot Error (Form ID: %s): %s',
                        $form['id'],
                        $result->get_error_message()
                    ));
                }
            }
        } catch (\Exception $e) {
            error_log(sprintf(
                'HT Contact Form HubSpot Exception (Form ID: %s): %s',
                $form['id'] ?? 'unknown',
                $e->getMessage()
            ));
        }
    }

    /**
     * Handle Twenty CRM integration
     *
     * @param array $form Form configuration
     * @param array $form_data Form submission data
     * @param array $meta Submission metadata
     * @return void
     */
    public function twentycrm($form, $form_data, $meta) {
        try {
            if (empty($this->integrations_settings) || empty($form['id'])) {
                return;
            }

            if (empty($this->integrations_settings['twentycrm']['enabled']) ||
                empty($this->integrations_settings['twentycrm']['api_key']) ||
                empty($this->integrations_settings['twentycrm']['base_url'])) {
                return;
            }

            $integration_list = $this->get_form_integrations($form['id'], 'twentycrm');

            if (empty($integration_list)) {
                return;
            }

            $twentycrm = TwentyCRM::get_instance(
                $this->integrations_settings['twentycrm']['api_key'],
                $this->integrations_settings['twentycrm']['base_url']
            );

            foreach ($integration_list as $integration) {
                $result = $twentycrm->subscribe((object) $integration, $form, $form_data, $meta);
                if (is_wp_error($result)) {
                    error_log(sprintf(
                        'HT Contact Form Twenty CRM Error (Form ID: %s): %s',
                        $form['id'],
                        $result->get_error_message()
                    ));
                }
            }
        } catch (\Exception $e) {
            error_log(sprintf(
                'HT Contact Form Twenty CRM Exception (Form ID: %s): %s',
                $form['id'] ?? 'unknown',
                $e->getMessage()
            ));
        }
    }

    /**
     * Send data to Zoho Flow
     *
     * @param array $form Form configuration
     * @param array $form_data Submitted form data
     * @param array $meta Entry metadata
     * @return void
     */
    public function zohocrm($form, $form_data, $meta) {
        try {
            if (empty($this->integrations_settings) || empty($form['id'])) {
                return;
            }

            // Check if Zoho CRM integration is enabled globally
            if (empty($this->integrations_settings['zohocrm']['enabled']) ||
                empty($this->integrations_settings['zohocrm']['client_id']) ||
                empty($this->integrations_settings['zohocrm']['client_secret'])) {
                return;
            }

            // Get form-specific integrations
            $integration_list = $this->get_form_integrations($form['id'], 'zohocrm');
            if (empty($integration_list)) {
                return;
            }

            // Process each Zoho CRM integration
            $zohocrm = ZohoCRM::get_instance(
                $this->integrations_settings['zohocrm']['client_id'],
                $this->integrations_settings['zohocrm']['client_secret'],
                $this->integrations_settings['zohocrm']['data_center'] ?? 'com'
            );

            foreach ($integration_list as $integration) {
                $result = $zohocrm->subscribe((object) $integration, $form, $form_data, $meta);
                if (is_wp_error($result)) {
                    error_log(sprintf(
                        'HT Contact Form Zoho CRM Error (Form ID: %s): %s',
                        $form['id'],
                        $result->get_error_message()
                    ));
                }
            }
        } catch (\Exception $e) {
            error_log(sprintf(
                'HT Contact Form Zoho CRM Exception (Form ID: %s): %s',
                $form['id'] ?? 'unknown',
                $e->getMessage()
            ));
        }
    }

    /**
     * Get form-specific integrations of a specific type
     *
     * @param int $form_id Form ID
     * @param string $integration_type Integration type (webhook, mailchimp, slack)
     * @return array Array of enabled integrations of the specified type
     */
    private function get_form_integrations($form_id, $integration_type) {
        $integration_list = get_post_meta($form_id, 'integrations', true);
        if (empty($integration_list)) {
            return [];
        }

        $decoded_list = json_decode($integration_list, true);
        if (!is_array($decoded_list)) {
            return [];
        }

        return array_filter($decoded_list, function($integration) use ($integration_type) {
            return $integration['type'] === $integration_type && $integration['enabled'];
        });
    }
}