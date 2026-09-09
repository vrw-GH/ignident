<?php

namespace HTContactFormAdmin\Includes\Api\Endpoints;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Utilities API Handler Class
 *
 * Handles utility REST API endpoints like fetching remote files
 */
class Utilities {
    /** @var string REST API namespace */
    private $namespace = 'ht-form/v1';

    /** @var self|null Singleton instance */
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
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register routes
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/utilities/fetch-csv',
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'fetch_csv'],
                'permission_callback' => [$this, 'permissions_check'],
                'args'                => [
                    'url' => [
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'esc_url_raw',
                    ],
                ],
            ]
        );
    }

    /**
     * Permission check for API endpoints
     *
     * @return bool
     */
    public function permissions_check() {
        return current_user_can('manage_options');
    }

    /**
     * Fetch CSV from remote URL
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function fetch_csv(WP_REST_Request $request) {
        $url = $request->get_param('url');

        if (empty($url)) {
            return new WP_Error(
                'invalid_url',
                __('Please provide a valid URL', 'ht-contactform'),
                ['status' => 400]
            );
        }

        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return new WP_Error(
                'invalid_url',
                __('Invalid URL format', 'ht-contactform'),
                ['status' => 400]
            );
        }

        // Fetch the CSV file
        $response = wp_remote_get($url, [
            'timeout'   => 30,
            'sslverify' => false,
        ]);

        if (is_wp_error($response)) {
            return new WP_Error(
                'fetch_failed',
                __('Failed to fetch CSV file: ', 'ht-contactform') . $response->get_error_message(),
                ['status' => 500]
            );
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            return new WP_Error(
                'fetch_failed',
                sprintf(__('Failed to fetch CSV file. Server returned status code: %d', 'ht-contactform'), $status_code),
                ['status' => 500]
            );
        }

        $csv_content = wp_remote_retrieve_body($response);

        if (empty($csv_content)) {
            return new WP_Error(
                'empty_response',
                __('The CSV file is empty', 'ht-contactform'),
                ['status' => 400]
            );
        }

        // Parse CSV content
        $result = $this->parse_csv($csv_content);

        if (is_wp_error($result)) {
            return $result;
        }

        return new WP_REST_Response([
            'success' => true,
            'data'    => $result,
        ], 200);
    }

    /**
     * Parse CSV content into structured data
     *
     * @param string $content CSV content
     * @return array|WP_Error Parsed data or error
     */
    private function parse_csv($content) {
        $lines = explode("\n", trim($content));

        if (count($lines) < 2) {
            return new WP_Error(
                'invalid_csv',
                __('CSV must have at least a header row and one data row', 'ht-contactform'),
                ['status' => 400]
            );
        }

        // Parse header
        $header = str_getcsv($lines[0]);
        $header = array_map('trim', $header);
        $level_count = count($header);

        if ($level_count < 2) {
            return new WP_Error(
                'invalid_csv',
                __('CSV must have at least 2 columns', 'ht-contactform'),
                ['status' => 400]
            );
        }

        // Parse data rows
        $data = [];
        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) {
                continue;
            }

            $row = str_getcsv($line);
            if (count($row) < 2 || empty(trim($row[0]))) {
                continue;
            }

            $row_data = [];
            for ($col = 0; $col < $level_count; $col++) {
                $row_data['level_' . ($col + 1)] = isset($row[$col]) ? sanitize_text_field(trim($row[$col])) : '';
            }
            $data[] = $row_data;
        }

        return [
            'chained_labels' => array_map('sanitize_text_field', $header),
            'chained_data'   => $data,
            'level_count'    => $level_count,
        ];
    }
}
