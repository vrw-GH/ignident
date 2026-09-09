<?php
namespace HTContactForm;

use HTContactForm\UI\ShortCode;
use HTContactForm\Integrations;
use HTContactForm\CronJob;
use HTContactForm\SubmissionHelper;
use HTContactForm\Ajax;


// If this file is accessed directly, abort.
if (!defined('ABSPATH')) {
    exit;
}
class Base {

    /**
     * Instance of this class
     *
     * @var object
     */
    protected static $instance = null;

    /**
     * Get instance of this class
     *
     * @return object
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        ShortCode::get_instance();
        Integrations::get_instance();
        CronJob::get_instance();
        SubmissionHelper::get_instance();
        Ajax::get_instance();
    }    
}
