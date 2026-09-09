<?php
/**
 * Trello REST API Endpoints
 *
 * @package HTContactFormAdmin
 * @subpackage Api\Endpoints\Integrations
 */

namespace HTContactFormAdmin\Includes\Api\Endpoints\Integrations;

use HTContactForm\Integrations\Trello as TrelloIntegration;
use HTContactFormAdmin\Includes\Api\Endpoints\Integrations as IntegrationsEndpoint;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Trello API Endpoints Handler
 */
class Trello {
    /**
     * REST API namespace
     *
     * @var string
     */
    private const NAMESPACE = 'ht-form/v1';

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
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes
     */
    public function register_routes() {
        $routes = [
            [
                'endpoint' => 'trello/verify',
                'methods'  => 'POST',
                'callback' => [$this, 'verify'],
                'args'     => [
                    'api_key' => [
                        'required' => true,
                        'type'     => 'string',
                    ],
                    'api_token' => [
                        'required' => true,
                        'type'     => 'string',
                    ],
                ],
            ],
            [
                'endpoint' => 'trello/boards',
                'methods'  => 'GET',
                'callback' => [$this, 'get_boards'],
            ],
            [
                'endpoint' => 'trello/lists',
                'methods'  => 'GET',
                'callback' => [$this, 'get_lists'],
                'args'     => [
                    'board_id' => [
                        'required' => true,
                        'type'     => 'string',
                    ],
                ],
            ],
            [
                'endpoint' => 'trello/labels',
                'methods'  => 'GET',
                'callback' => [$this, 'get_labels'],
                'args'     => [
                    'board_id' => [
                        'required' => true,
                        'type'     => 'string',
                    ],
                ],
            ],
        ];

        foreach ($routes as $route) {
            register_rest_route(self::NAMESPACE, $route['endpoint'], [
                'methods'             => $route['methods'],
                'callback'            => $route['callback'],
                'permission_callback' => [$this, 'permissions_check'],
                'args'                => $route['args'] ?? [],
            ]);
        }
    }

    /**
     * Check permissions
     *
     * @return bool|WP_Error
     */
    public function permissions_check() {
        if (!current_user_can('manage_options')) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You do not have permission to access this endpoint.', 'ht-contactform'),
                ['status' => 403]
            );
        }
        return true;
    }

    /**
     * Get Trello credentials from settings
     *
     * @return array
     */
    private function get_credentials() {
        $settings = get_option(IntegrationsEndpoint::OPTION_NAME, []);
        return [
            'api_key' => $settings['trello']['api_key'] ?? '',
            'api_token' => $settings['trello']['api_token'] ?? '',
        ];
    }

    /**
     * Verify Trello credentials
     *
     * @param WP_REST_Request|string $request_or_api_key Request object or API key string
     * @param string|null $api_token API token (when called directly, not from REST)
     * @return WP_REST_Response|WP_Error
     */
    public function verify($request_or_api_key, $api_token = null) {
        // Support both REST request and direct call
        if ($request_or_api_key instanceof WP_REST_Request) {
            $api_key = sanitize_text_field($request_or_api_key->get_param('api_key'));
            $api_token = sanitize_text_field($request_or_api_key->get_param('api_token'));
        } else {
            $api_key = sanitize_text_field($request_or_api_key);
            $api_token = sanitize_text_field($api_token);
        }

        if (empty($api_key) || empty($api_token)) {
            return new WP_Error(
                'missing_credentials',
                esc_html__('API key and token are required.', 'ht-contactform'),
                ['status' => 400]
            );
        }

        $trello = TrelloIntegration::get_instance($api_key, $api_token);
        $result = $trello->verify();

        if (is_wp_error($result)) {
            return $result;
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => esc_html__('Trello credentials verified successfully.', 'ht-contactform'),
            'user' => $result,
        ], 200);
    }

    /**
     * Get boards for the authenticated user
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function get_boards($request) {
        $credentials = $this->get_credentials();

        if (empty($credentials['api_key']) || empty($credentials['api_token'])) {
            return new WP_Error(
                'missing_credentials',
                esc_html__('Trello credentials not configured.', 'ht-contactform'),
                ['status' => 400]
            );
        }

        $trello = TrelloIntegration::get_instance($credentials['api_key'], $credentials['api_token']);
        $boards = $trello->get_boards();

        if (is_wp_error($boards)) {
            return $boards;
        }

        return new WP_REST_Response($boards, 200);
    }

    /**
     * Get lists for a board
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function get_lists($request) {
        $credentials = $this->get_credentials();
        $board_id = sanitize_text_field($request->get_param('board_id'));

        if (empty($credentials['api_key']) || empty($credentials['api_token'])) {
            return new WP_Error(
                'missing_credentials',
                esc_html__('Trello credentials not configured.', 'ht-contactform'),
                ['status' => 400]
            );
        }

        if (empty($board_id)) {
            return new WP_Error(
                'missing_board_id',
                esc_html__('Board ID is required.', 'ht-contactform'),
                ['status' => 400]
            );
        }

        $trello = TrelloIntegration::get_instance($credentials['api_key'], $credentials['api_token']);
        $lists = $trello->get_lists($board_id);

        if (is_wp_error($lists)) {
            return $lists;
        }

        return new WP_REST_Response($lists, 200);
    }

    /**
     * Get labels for a board
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function get_labels($request) {
        $credentials = $this->get_credentials();
        $board_id = sanitize_text_field($request->get_param('board_id'));

        if (empty($credentials['api_key']) || empty($credentials['api_token'])) {
            return new WP_Error(
                'missing_credentials',
                esc_html__('Trello credentials not configured.', 'ht-contactform'),
                ['status' => 400]
            );
        }

        if (empty($board_id)) {
            return new WP_Error(
                'missing_board_id',
                esc_html__('Board ID is required.', 'ht-contactform'),
                ['status' => 400]
            );
        }

        $trello = TrelloIntegration::get_instance($credentials['api_key'], $credentials['api_token']);
        $labels = $trello->get_labels($board_id);

        if (is_wp_error($labels)) {
            return $labels;
        }

        return new WP_REST_Response($labels, 200);
    }
}
