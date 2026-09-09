<?php
namespace HTContactFormAdmin\Includes\Api;

use HTContactFormAdmin\Includes\Api\Endpoints\Form;
use HTContactFormAdmin\Includes\Api\Endpoints\Entry;
use HTContactFormAdmin\Includes\Api\Endpoints\Submission;
use HTContactFormAdmin\Includes\Api\Endpoints\Settings;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\Mailchimp;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\ActiveCampaign;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\MailerLite;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\ConstantContact;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\OnepageCRM;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\GetResponse;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\Drip;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\Moosend;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\iContact;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\MailPoet;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\Notion;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\Trello;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\HubSpot;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\ZohoCRM;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations\TwentyCRM;
use HTContactFormAdmin\Includes\Api\Endpoints\Utilities;
use HTContactFormAdmin\Includes\Api\Endpoints\Draft;
use HTContactFormAdmin\Includes\Api\Endpoints\Templates;

class ApiRegistry {
    private static $instance = null;

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    public function __construct() {
        Form::get_instance();
        Entry::get_instance();
        Submission::get_instance();
        Settings::get_instance();
        Integrations::get_instance();
        Mailchimp::get_instance();
        ActiveCampaign::get_instance();
        MailerLite::get_instance();
        OnepageCRM::get_instance();
        GetResponse::get_instance();
        Drip::get_instance();
        Moosend::get_instance();
        iContact::get_instance();
        MailPoet::get_instance();
        Notion::get_instance();
        Trello::get_instance();
        HubSpot::get_instance();
        ZohoCRM::get_instance();
        TwentyCRM::get_instance();
        Utilities::get_instance();
        Draft::get_instance();
        Templates::get_instance();
    }
}