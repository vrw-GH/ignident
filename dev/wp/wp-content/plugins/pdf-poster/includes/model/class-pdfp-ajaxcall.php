<?php

namespace PDFPro\Model;

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'PDFPro\Model\PDFP_AjaxCall' ) ) {
    class PDFP_AjaxCall
{

    protected static $_instance = null;
    private $params = [];
    private $requestType;
    private $requestMethod;
    private $requestModel;
    private $namespace = 'PDFPro\Model\\';
    private $model;

    public function register()
    {
        add_action('wp_ajax_pdf_poster_ajax', [$this, 'prepareAjax']);
    }

    public static function instance()
    {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function isset($array, $key, $default = false)
    {
        if (isset($array[$key])) {
            return $array[$key];
        }
        return $default;
    }

    public function prepareAjax() {
        check_ajax_referer('wp_ajax', 'nonce');

        if ( isset( $_GET['nonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            // GET request — sanitize each field individually
            $this->params      = array_map( 'sanitize_text_field', wp_unslash( $_GET ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $this->requestType = 'GET';
        } else {
            // POST request — sanitize each field individually
            $this->params      = array_map( 'sanitize_text_field', wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $this->requestType = 'POST';
        }

        echo wp_json_encode( $this->proceedRequest() );
        wp_die();
    }

    public function proceedRequest()
    {
        $data   = $this->params;
        $nonce  = $this->isset( $data, 'nonce' );

        $this->requestModel  = sanitize_text_field( $this->isset( $data, 'model', 'Model' ) );
        $this->requestMethod = sanitize_text_field( $this->isset( $data, 'method', 'invalid' ) );
        if (!class_exists($this->namespace . $this->requestModel)) {
            return $this->invalid();
        }
        $this->model = $this->namespace . $this->requestModel;
        $model = new $this->model();

        // Security: Allowlist specific methods for dynamic execution to prevent arbitrary method calls
        $allowed_methods = ['get', 'getBlock'];

        if (wp_verify_nonce($nonce, 'wp_ajax') && in_array($this->requestMethod, $allowed_methods) && method_exists($model, $this->requestMethod) && current_user_can('edit_others_pages')) {
            unset($this->params['method']);
            unset($this->params['action']);
            unset($this->params['nonce']);
            unset($this->params['model']);
            return $model->{$this->requestMethod}($this->params);
        } else {
            return $this->invalid();
        }
    }

    public function invalid()
    {
        return new \WP_REST_Response('invalid request', 400);
    }
}

}