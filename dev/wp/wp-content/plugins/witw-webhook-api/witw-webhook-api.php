<?php
/**
 * Plugin Name: WITW Webhook API
 * Description: Webhook endpoint for HT Contact Form to send admin + user emails with attachment.
 * Version: 1.1
 * Author: Wright IT Works
 * Author URI: https://www.wrightsdesk.com/
 */

if (!defined('ABSPATH')) exit;

add_action('rest_api_init', function () {
    register_rest_route('witw-webhook/htcf-ignident-lm1', '/v1', [
        'methods'  => 'POST',
        'callback' => 'handle_htcf_ignident_lm1',
        'permission_callback' => '__return_true'
    ]);
});

function handle_htcf_ignident_lm1(WP_REST_Request $request)
{
    $data = $request->get_json_params();

    if (!$data || empty($data['form_data']['email'])) {
        return new WP_REST_Response(['error' => 'Missing email field'], 400);
    }

    // ---- Extract fields ----
    $fields = $data['form_data'];
    $user_email = sanitize_email($fields['email']);
    $admin_email = get_option('admin_email');

    // Convert fields to readable text
    $fields_string = "";
    foreach ($fields as $key => $value) {
        $fields_string .= ucfirst(str_replace('_', ' ', $key)) . ": " . print_r($value,true) . "\n";
    }

    // ---- Admin notification ---- (no need - HT forms sends own notice!)
#    $result = wp_mail(
#        $admin_email,
#        'Test2 New Submission ' . $data['form_title'] ,
#        "A new form was submitted:\n\n" . $fields_string
#    );

    // ---- Confirmation email to user ----
    $attachment_id = isset($fields['attachment_id']) ? intval($fields['attachment_id']) : 0;
    $attachment_path = $attachment_id ? get_attached_file($attachment_id) : '';

    $headers = [
                'From: Ignident® <info@ignident.com>',
                'Content-Type: text/html; charset=UTF-8'
               ];

    $subject = 'Beitrag >>'. $fields['doc_title'] . '<< ist beigefügt.';

    $message = "<p>Vielen Dank für Ihr Interesse!</p>
                <p>Die von Ihnen angeforderte PDF-Datei des Artikels ist beigefügt.</p>";

    $result = wp_mail(
        $user_email, $subject, $message, $headers,
        $attachment_path ? [$attachment_path] : []
    );

# vw testing!-----------
if (WP_DEBUG_LOG) {
   file_put_contents(
       WP_CONTENT_DIR . '/debug-witw-webhook.log',
       'Debug:' . WP_DEBUG_LOG . 'Email sending (WITW-webhook-api) - final result =[' .$result . ']*',
       FILE_APPEND
   );
}
# ----------------------

    return new WP_REST_Response(['success' => true], 200);
}
