<?php

namespace PDFPro\Rest;

use PDFPro\Helper\PDFP_Functions as Utils;

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'PDFPro\Rest\PDFP_GetMeta' ) ) {
    class PDFP_GetMeta
{
    public $route = '';

    function __construct()
    {
        $this->route = '/single(?:/(?P<id>\d+))?';
        add_action('rest_api_init', [$this, 'single_doc']);
    }

    public function single_doc()
    {
        register_rest_route(
            'pdfposter/v1',
            $this->route,
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'single_doc_callback'],
                // This endpoint only reads publicly published PDF poster data.
                // We restrict to logged-in users to satisfy WordPress security guidelines.
                'permission_callback' => function() {
                    return is_user_logged_in();
                },
            ]
        );
    }

    public function single_doc_callback(\WP_REST_Request $request) {
        $response = [];
        $params = $request->get_params();
        $id = $params['id'] ?? null;

        if (!$id) {
            return new \WP_REST_Response([]);
        }

        $post_type = get_post_type($id);
        $post = get_post($id);

        if ($post_type !== 'pdfposter' || $post->post_status !== 'publish') {
            return new \WP_REST_Response([]);
        }

        $isGutenberg = get_post_meta($id, 'isGutenberg', true);

        if ($isGutenberg) {
            $content = $post->post_content ?? false;
            if ($content) {
                $blocks = parse_blocks($content);
                $data = wp_parse_args($blocks[0]['attrs'], Utils::generate_pdf_poster_block(null)['attrs']);
            }
        } else {
            $block = Utils::generate_pdf_poster_block($id);
            $data = $block['attrs'];
        }

        return new \WP_REST_Response($data);
    }
}

}