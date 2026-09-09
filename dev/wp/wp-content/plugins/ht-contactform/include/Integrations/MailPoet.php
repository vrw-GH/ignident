<?php
/**
 * MailPoet Integration Class
 *
 * Handles all MailPoet interactions for form submissions including
 * list management and subscriber creation using MailPoet's PHP API.
 *
 * @package HTContactForm
 * @subpackage Integrations
 */

namespace HTContactForm\Integrations;

use HTContactFormAdmin\Includes\Services\Helper;
use WP_Error;
use WP_REST_Response;

/**
 * MailPoet Integration Handler
 *
 * Provides functionality to integrate form submissions with MailPoet
 * email marketing using their local PHP API.
 */
class MailPoet {
    /**
     * Helper class instance
     *
     * @var Helper|null
     */
    private $helper = null;

    /**
     * Singleton instance
     *
     * @var self|null
     */
    private static $instance = null;

    /**
     * Get singleton instance
     *
     * @return self Instance of the MailPoet class
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
        if (class_exists(Helper::class)) {
            $this->helper = Helper::get_instance();
        }
    }

    /**
     * Check if MailPoet plugin is active
     *
     * @return bool Whether MailPoet is installed and active
     */
    public function is_active() {
        return class_exists('\\MailPoet\\API\\API');
    }

    /**
     * Get MailPoet API instance
     *
     * @return object|WP_Error MailPoet API instance or error
     */
    private function get_api() {
        if (!$this->is_active()) {
            return new WP_Error(
                'mailpoet_not_active',
                __('MailPoet plugin is not installed or activated.', 'ht-contactform')
            );
        }

        try {
            return \MailPoet\API\API::MP('v1');
        } catch (\Exception $e) {
            return new WP_Error(
                'mailpoet_api_error',
                sprintf(
                    /* translators: %s: Error message */
                    __('MailPoet API error: %s', 'ht-contactform'),
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Get all mailing lists
     *
     * @return array|WP_Error Array of lists on success, WP_Error on failure
     */
    public function get_lists() {
        $api = $this->get_api();

        if (is_wp_error($api)) {
            return $api;
        }

        try {
            $lists = $api->getLists();
            return is_array($lists) ? $lists : [];
        } catch (\Exception $e) {
            return new WP_Error(
                'mailpoet_lists_error',
                sprintf(
                    /* translators: %s: Error message */
                    __('Failed to get MailPoet lists: %s', 'ht-contactform'),
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Get subscriber fields (custom fields)
     *
     * @return array|WP_Error Array of fields on success, WP_Error on failure
     */
    public function get_subscriber_fields() {
        $api = $this->get_api();

        if (is_wp_error($api)) {
            return $api;
        }

        try {
            $fields = $api->getSubscriberFields();
            return is_array($fields) ? $fields : [];
        } catch (\Exception $e) {
            return new WP_Error(
                'mailpoet_fields_error',
                sprintf(
                    /* translators: %s: Error message */
                    __('Failed to get MailPoet fields: %s', 'ht-contactform'),
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Subscribe to MailPoet list
     *
     * @param object $integration Integration settings
     * @param array $form Form data
     * @param array $form_data Form submission data
     * @param array $meta Meta data
     * @return WP_Error|WP_REST_Response Response
     */
    public function subscribe($integration, $form, $form_data, $meta) {
        // Validate integration object
        if (!is_object($integration)) {
            return new WP_Error('invalid_integration', __('Invalid integration configuration', 'ht-contactform'));
        }

        if (empty($integration->enabled) || empty($integration->list_id)) {
            return new WP_Error('integration_disabled', __('Integration is not enabled for this form', 'ht-contactform'));
        }

        $api = $this->get_api();

        if (is_wp_error($api)) {
            return $api;
        }

        // Process merge fields to get subscriber data
        $subscriber_data = [];

        if (!empty($integration->merge_fields) && is_array($integration->merge_fields)) {
            foreach ($integration->merge_fields as $key => $value) {
                if (empty($value)) {
                    continue;
                }

                $processed_value = $this->process_field_value($value, $form_data, $form);

                if ($key === 'email') {
                    $subscriber_data['email'] = sanitize_email($processed_value);
                } else {
                    $subscriber_data[$key] = sanitize_text_field($processed_value);
                }
            }
        }

        if (empty($subscriber_data['email']) || !is_email($subscriber_data['email'])) {
            return new WP_Error('invalid_email', __('Valid email address is required', 'ht-contactform'));
        }

        // Get list IDs (support multiple lists)
        $list_ids = [];
        if (is_array($integration->list_id)) {
            $list_ids = array_map('absint', $integration->list_id);
        } else {
            $list_ids = [absint($integration->list_id)];
        }

        try {
            // Determine status based on double_opt_in setting
            if (!empty($integration->double_opt_in)) {
                $subscriber_data['status'] = 'unconfirmed';
            } else {
                $subscriber_data['status'] = 'subscribed';
            }

            // Set options based on double_opt_in
            $options = [
                'send_confirmation_email' => !empty($integration->double_opt_in),
                'schedule_welcome_email' => true,
                'skip_subscriber_notification' => empty($integration->double_opt_in),
            ];

            $result = $api->addSubscriber($subscriber_data, $list_ids, $options);

            do_action('ht_form/mailpoet_integration_result', $result, 'success', __('MailPoet subscription successful', 'ht-contactform'));

            return new WP_REST_Response([
                'message' => __('MailPoet subscription successful', 'ht-contactform'),
                'subscriber_id' => $result['id'] ?? null,
            ], 200);

        } catch (\Exception $e) {
            $error_message = $e->getMessage();

            // Handle "subscriber already exists" gracefully
            if (strpos($error_message, 'already exists') !== false) {
                try {
                    // Try to update existing subscriber and add to list
                    $existing = $api->getSubscriber($subscriber_data['email']);
                    if ($existing) {
                        $api->subscribeToLists($existing['id'], $list_ids);

                        do_action('ht_form/mailpoet_integration_result', $existing, 'updated', __('Subscriber updated and added to list', 'ht-contactform'));

                        return new WP_REST_Response([
                            'message' => __('Subscriber added to list', 'ht-contactform'),
                            'subscriber_id' => $existing['id'],
                        ], 200);
                    }
                } catch (\Exception $update_error) {
                    // If update fails, return original error
                    do_action('ht_form/mailpoet_integration_result', null, 'failed', $update_error->getMessage());
                    return new WP_Error('mailpoet_subscribe_error', $update_error->getMessage());
                }
            }

            do_action('ht_form/mailpoet_integration_result', null, 'failed', $error_message);

            return new WP_Error(
                'mailpoet_subscribe_error',
                sprintf(
                    /* translators: %s: Error message */
                    __('MailPoet subscription failed: %s', 'ht-contactform'),
                    $error_message
                )
            );
        }
    }

    /**
     * Process field value with smart tags
     *
     * @param string $value The field value or smart tag
     * @param array $form_data Form submission data
     * @param array $form Form configuration
     * @return string Processed value
     */
    private function process_field_value($value, $form_data, $form) {
        return $this->helper->filter_vars($value, $form_data, $form);
    }
}
