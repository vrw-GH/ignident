<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Extract variables
$subject = $args['subject'] ?? '';
$form = $args['form'] ?? [];
$data = $args['data'] ?? [];
$meta = $args['meta'] ?? [];
$footer_text = $args['footer_text'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?php echo esc_html($subject); ?></title>
</head>
<body style="margin: 0; padding: 0; background-color: #f5f5f5; font-family: Arial, sans-serif; color: #333333; line-height: 1.6;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 20px 0;">
                <!-- Email Container -->
                <table role="presentation" style="max-width: 600px; width: 100%; margin: 0 auto; background-color: #ffffff; border-radius: 6px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 20px; text-align: center; border-bottom: 1px solid #eeeeee;">
                            <h1 style="margin: 0; color: #444444; font-size: 24px;"><?php echo esc_html($form['title']); ?></h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 20px;">
                            <!-- Form Data Table -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                            <?php
                            foreach ($data as $key => $value) {
                                if(is_numeric($key) || empty($key)) {
                                    ?>
                                        <tr>
                                            <td style="padding: 12px; border: 1px solid #dddddd; vertical-align: top;" colspan="2">
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
                                        $display_value = '<img src="' . esc_url($value) . '" alt="' . esc_attr__('Signature', 'ht-contactform') . '" style="max-width: 300px; height: auto; border: 1px solid #dddddd; border-radius: 4px;" />';
                                    }

                                    if(($field_type === 'file_upload' || $field_type === 'image_upload') && !empty($value)) {
                                        // Render file/image uploads as links with filename as text
                                        if(is_array($value)) {
                                            $links = array_map(function($url) {
                                                $filename = basename(wp_parse_url($url, PHP_URL_PATH));
                                                return '<a href="' . esc_url($url) . '" target="_blank" style="color: #0073aa; text-decoration: none;">' . esc_html($filename) . '</a>';
                                            }, $value);
                                            $display_value = implode('<br>', $links);
                                        } else {
                                            $filename = basename(wp_parse_url($value, PHP_URL_PATH));
                                            $display_value = '<a href="' . esc_url($value) . '" target="_blank" style="color: #0073aa; text-decoration: none;">' . esc_html($filename) . '</a>';
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
                                            <th style="text-align: left; padding: 12px; border: 1px solid #dddddd; background-color: #f9f9f9; min-width: 120px; width: 120px; vertical-align: top;">
                                                <?php echo esc_html($field_admin_label); ?>
                                            </th>
                                            <td style="padding: 12px; border: 1px solid #dddddd; vertical-align: top;">
                                                <?php
                                                if ($is_pre_sanitized) {
                                                    // Already sanitized content - output directly
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
                            </table>
                            
                            <!-- Metadata -->
                            <div style="font-size: 12px; color: #777777; border-top: 1px solid #eeeeee; padding-top: 15px; margin-top: 20px;">
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
                        <td style="padding: 20px; text-align: center; font-size: 12px; color: #777777; border-top: 1px solid #eeeeee;">
                            <p style="margin: 0;">
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