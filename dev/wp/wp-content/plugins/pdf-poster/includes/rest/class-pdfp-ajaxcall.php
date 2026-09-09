<?php
namespace PDFPro\Rest;

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'PDFPro\Rest\PDFP_AjaxCall' ) ) {
    class PDFP_AjaxCall {
        protected static $_instance = null;

        public function __construct() {}

        /**
         * Create Instance
         */
        public static function instance()
        {
            if (self::$_instance === null) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        public function register()
        {
            add_action('rest_api_init', [$this, 'register_routes']);
        }

        public function register_routes()
        {
            register_rest_route('pdfp/v1', '/ajax', [
                'methods' => 'POST',
                'callback' => [$this, 'proceedRequest'],
                'permission_callback' => [$this, 'get_permission']
            ]);
        }

        public function get_permission()
        {
            return current_user_can('edit_posts');
        }

        public function proceedRequest($request)
        {
            $data = $request->get_params();
            $requestModel = isset($data['model']) ? sanitize_text_field($data['model']) : 'Model';
            $requestMethod = isset($data['method']) ? sanitize_text_field($data['method']) : 'invalid';
            $namespace = 'PDFPro\Model\\';
            
            $class_name = $namespace . 'PDFP_' . $requestModel;

            if (!class_exists($class_name)) {
                return new \WP_REST_Response('invalid request', 400);
            }

            $model = new $class_name();

            // Security: Allowlist specific methods for dynamic execution to prevent arbitrary method calls
            $allowed_methods = ['get', 'getBlock'];

            if (in_array($requestMethod, $allowed_methods) && method_exists($model, $requestMethod)) {
                return $model->{$requestMethod}($data);
            } else {
                return new \WP_REST_Response('invalid request', 400);
            }
        }
    }
}
