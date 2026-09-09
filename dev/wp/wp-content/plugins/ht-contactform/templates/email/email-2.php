<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Extract variables
$subject = $args['subject'] ?? '';
$form = $args['form'] ?? [];
$data = $args['data'] ?? []; // Changed from form_data to data for consistency
$meta = $args['meta'] ?? [];
$footer_text = $args['footer_text'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?php echo esc_html($subject); ?></title>
</head>
<body style="margin: 0; padding: 0; background-color: #f7f7f7; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333333; line-height: 1.6;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 30px 0;">
                <!-- Email Container -->
                <table role="presentation" style="max-width: 600px; width: 100%; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);">
                    <!-- Header Bar -->
                    <tr>
                        <td style="background-color: #4F46E5; height: 6px; line-height: 0; font-size: 0;">&nbsp;</td>
                    </tr>
                    <!-- Header -->
                    <tr>
                        <td style="padding: 30px 30px 20px; text-align: center;">
                            <h1 style="margin: 0; color: #1F2937; font-size: 24px; font-weight: 600;"><?php echo esc_html($form['title']); ?></h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 0 30px 30px;">
                            <!-- Form Data Table -->
                            <table role="presentation" style="width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 20px; border-radius: 6px; overflow: hidden; border: 1px solid #E5E7EB;">
                                <tbody>
                                <?php
                                foreach ($data as $key => $value) {
                                    if(is_numeric($key) || empty($key)) {
                                        // If key is numeric or empty, display the value without a label
                                        ?>
                                        <tr>
                                            <td style="padding: 12px 16px; border-bottom: 1px solid #E5E7EB; vertical-align: top; color: #1F2937; font-size: 14px;" colspan="2">
                                                <?php echo wp_kses_post($value); ?>
                                            </td>
                                        </tr>
                                        <?php
                                    } else {
                                        // Get current field using key
                                        $current_field = null;
                                        foreach ($form['fields'] as $field) {
                                            if($field['type'] !== 'submit' && $field['settings']['name_attribute'] === $key) {
                                                $current_field = $field;
                                                break;
                                            }
                                        }
                                        
                                        // Use current field variable for operations
                                        $field_type = $current_field ? $current_field['type'] : '';
                                        $field_admin_label = $current_field ? $current_field['settings']['admin_label'] : '';
                                        $display_value = $value;

                                        if(is_array($value) && !empty($value)) {
                                            // Handle chained_select - join level values with " > "
                                            if($field_type === 'chained_select') {
                                                $level_values = array_filter(array_values($value));
                                                $display_value = implode(' > ', $level_values);
                                            }
                                            // Check if it's a repeater field (array of objects)
                                            else if($field_type === 'repeater' && isset($value[0]) && is_array($value[0])) {
                                                $repeater_html = '<table style="width: 100%; border-collapse: collapse; margin-top: 5px;">';
                                                $repeater_html .= '<thead><tr style="background-color: #f0f0f0;">';

                                                // Add column headers from first row
                                                $first_row = $value[0];
                                                foreach (array_keys($first_row) as $col_key) {
                                                    $repeater_html .= sprintf('<th style="padding: 6px; border: 1px solid #ddd; font-size: 11px; text-align: left;">%s</th>', esc_html(ucfirst(str_replace('_', ' ', $col_key))));
                                                }
                                                $repeater_html .= '</tr></thead><tbody>';

                                                // Add data rows
                                                foreach ($value as $row_index => $row_data) {
                                                    $repeater_html .= '<tr>';
                                                    foreach ($row_data as $cell_value) {
                                                        if (is_array($cell_value)) {
                                                            $cell_value = implode(', ', $cell_value);
                                                        }
                                                        $repeater_html .= sprintf('<td style="padding: 6px; border: 1px solid #ddd; font-size: 12px;">%s</td>', esc_html($cell_value));
                                                    }
                                                    $repeater_html .= '</tr>';
                                                }
                                                $repeater_html .= '</tbody></table>';
                                                $display_value = $repeater_html;
                                            } else if($field_type === 'name' || $field_type === 'address') {
                                                $display_value = implode(' ', $value);
                                            } else {
                                                $display_value = sprintf('<ul style="margin: 0; padding: 0;"><li>%s</li></ul>', implode('</li><li>', $value));
                                            }
                                        }

                                        if($field_type === 'textarea') {
                                            $display_value = nl2br(esc_html($value));
                                        }

                                        // Flag for pre-sanitized content (richtext)
                                        $is_pre_sanitized = false;

                                        if($field_type === 'richtext') {
                                            // Rich text is already sanitized in Submission.php, output as-is
                                            $display_value = $value;
                                            $is_pre_sanitized = true;
                                        }

                                        if($field_type === 'signature' && !empty($value)) {
                                            // Render signature as image
                                            $display_value = '<img src="' . esc_url($value) . '" alt="' . esc_attr__('Signature', 'ht-contactform') . '" style="max-width: 300px; height: auto; border: 1px solid #E5E7EB; border-radius: 4px;" />';
                                        }

                                        if(($field_type === 'file_upload' || $field_type === 'image_upload') && !empty($value)) {
                                            // Render file/image uploads as links with filename as text
                                            if(is_array($value)) {
                                                $links = array_map(function($url) {
                                                    $filename = basename(wp_parse_url($url, PHP_URL_PATH));
                                                    return '<a href="' . esc_url($url) . '" target="_blank" style="color: #4F46E5; text-decoration: none;">' . esc_html($filename) . '</a>';
                                                }, $value);
                                                $display_value = implode('<br>', $links);
                                            } else {
                                                $filename = basename(wp_parse_url($value, PHP_URL_PATH));
                                                $display_value = '<a href="' . esc_url($value) . '" target="_blank" style="color: #4F46E5; text-decoration: none;">' . esc_html($filename) . '</a>';
                                            }
                                        }

                                        if($field_type === 'ratings') {
                                            $options = $current_field['settings']['options'] ?? [];
                                            $rating_label = '';
                                            foreach ($options as $option) {
                                                if($option['value'] === $value) {
                                                    $rating_label = $option['label'];
                                                    break;
                                                }
                                            }
                                            $display_value = implode(' ', [$rating_label, "({$value})"]) ;
                                        }
                                        ?>
                                        <tr>
                                            <th style="text-align: left; padding: 12px 16px; border-bottom: 1px solid #E5E7EB; width: 140px; vertical-align: top; color: #374151; font-weight: 600; font-size: 14px; background-color: #F9FAFB;">
                                                <?php echo esc_html($field_admin_label); ?>
                                            </th>
                                            <td style="padding: 12px 16px; border-bottom: 1px solid #E5E7EB; vertical-align: top; color: #1F2937; font-size: 14px;">
                                                <?php
                                                if ($is_pre_sanitized) {
                                                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                                    echo $display_value;
                                                } else {
                                                    echo wp_kses_post($display_value);
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                }
                                ?>
                                </tbody>
                            </table>
                            
                            <!-- Metadata -->
                            <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #E5E7EB; font-size: 12px; color: #6B7280;">
                                <?php
                                    if(!empty($meta['created_at'])) {
                                        printf('<p style="margin: 5px 0;">%s: %s</p>', 
                                            esc_html__('Submitted on', 'ht-contactform'),
                                            esc_html($meta['created_at'])
                                        );
                                    }
                                    if(!empty($meta['ip_address'])) {
                                        printf('<p style="margin: 5px 0;">%s: %s</p>', 
                                            esc_html__('IP Address', 'ht-contactform'),
                                            esc_html($meta['ip_address'])
                                        );
                                    }
                                ?>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #F9FAFB; padding: 20px 30px; text-align: center; border-top: 1px solid #E5E7EB;">
                            <p style="margin: 0; font-size: 13px; color: #6B7280;">
                                <?php if(!empty($footer_text)) {
                                    echo esc_html($footer_text);
                                } else {
                                    echo sprintf(
                                        /* translators: %1$s: Year, %2$s: Site Name, %3$s: Plugin Name */
                                        esc_html__('&copy; %1$s %2$s. All rights reserved. Powered by %3$s', 'ht-contactform'),
                                        esc_html(gmdate('Y')),
                                        esc_html(get_bloginfo('name')),
                                        esc_html('HT Contact Form')
                                    ); 
                                } ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>