<?php
namespace HTContactFormAdmin\Includes\Api\Endpoints;

use HTContactForm\Templates\TemplateRegistry;
use HTContactFormAdmin\Includes\Models\Form as FormModel;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Templates API Endpoint
 *
 * GET  /ht-form/v1/templates        — list all templates with their fields
 * POST /ht-form/v1/templates/create — create a new form from a template ID
 */
class Templates {

    private $namespace = 'ht-form/v1';
    private static $instance = null;

    public static function get_instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes() {
        // List all templates
        register_rest_route($this->namespace, '/templates', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_templates'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        // Create a form from a template
        register_rest_route($this->namespace, '/templates/create', [
            'methods'             => 'POST',
            'callback'            => [$this, 'create_from_template'],
            'permission_callback' => [$this, 'check_permission'],
        ]);
    }

    public function check_permission() {
        return current_user_can('manage_options');
    }

    /**
     * GET /ht-form/v1/templates
     * Returns all templates with their fields.
     */
    public function get_templates(WP_REST_Request $request) {
        $registry  = TemplateRegistry::get_instance();
        $templates = $registry->get_all();

        $response = array_map(function ($template) {
            $input_fields = array_filter($template['fields'], function ($field) {
                return ($field['type'] ?? '') !== 'submit';
            });

            return [
                'id'          => $template['id'],
                'title'       => $template['title'],
                'description' => $template['description'],
                'category'    => $template['category'],
                'field_count' => count($input_fields),
                'fields'      => $template['fields'],
            ];
        }, $templates);

        return new WP_REST_Response($response, 200);
    }

    /**
     * POST /ht-form/v1/templates/create
     * Body: { "template_id": "contact_us", "title": "My Contact Form" }
     * Creates a real form from the template and returns the new form object.
     */
    public function create_from_template(WP_REST_Request $request) {
        $params      = $request->get_json_params();
        $template_id = sanitize_key($params['template_id'] ?? '');
        $title       = sanitize_text_field($params['title'] ?? '');

        if (empty($template_id)) {
            return new WP_Error('missing_template_id', __('template_id is required.', 'ht-contactform'), ['status' => 400]);
        }

        $registry = TemplateRegistry::get_instance();
        $template = $registry->get_by_id($template_id);

        if (!$template) {
            return new WP_Error('template_not_found', __('Template not found.', 'ht-contactform'), ['status' => 404]);
        }

        $form_title = !empty($title) ? $title : $template['title'];
        $form_model = FormModel::get_instance();

        $settings = $template['settings'] ?? $form_model->get_default_settings();

        $new_form = $form_model->create([
            'title'    => $form_title,
            'fields'   => $template['fields'],
            'settings' => $settings,
        ]);

        if (is_wp_error($new_form)) {
            return $new_form;
        }

        return new WP_REST_Response($new_form, 201);
    }

}
