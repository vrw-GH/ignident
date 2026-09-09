<?php
namespace HTContactForm;

use HTContactFormAdmin\Includes\Services\Helper;
use WP_Error;
/**
 * Submission class
 */
class SubmissionHelper {

    /**
     * [$_instance]
     * @var self
     */
    private static $_instance = null;

    /**
     * [instance] Initializes a singleton instance
     * @return self
     */
    public static function get_instance() {
        if ( ! isset( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    /**
     * Initialize the class
     */
    private function __construct() {
        add_filter('ht_form_submission_ip_restrictions_check', [$this, 'check_submission_ip_restrictions'], 10, 2);
        add_filter('ht_form_submission_country_restrictions_check', [$this, 'check_submission_country_restrictions'], 10, 2);
    }

    /**
     * Check IP restrictions
     * @param mixed $value
     * @param mixed $settings
     */
    public function check_submission_ip_restrictions($value, $settings) {
        if (!empty($settings['enable_ip_restriction'])) {
            $ip = Helper::get_ip();
            $restricted_ips = $settings['restrict_ip_address'] ?? '';
            if (!empty($restricted_ips)) {
                $restricted_ips = explode(',', $restricted_ips);
                $restricted_ips = array_map('trim', $restricted_ips);
                if (in_array($ip, $restricted_ips)) {
                    return new WP_Error(
                        'ip_restricted',
                        $settings['restrict_ip_message'],
                        ['status' => 400]
                    );
                }
            }
        }
        return $value;        
    }

    /**
     * Check country restrictions
     * @param mixed $value
     * @param mixed $settings
     */
    public function check_submission_country_restrictions($value, $settings) {
        if (!empty($settings['enable_country_restriction'])) {
            $ip = Helper::get_ip();
            $geo_data = Helper::get_geolocation_data($ip);
            $restricted_countries = $settings['restrict_country'] ?? [];
            if (!empty($geo_data['country']) && in_array(strtolower($geo_data['country']), $restricted_countries)) {
                return new WP_Error(
                    'country_restricted',
                    $settings['restrict_country_message'],
                    ['status' => 400]
                );
            }
        }
        return $value;
    }
}