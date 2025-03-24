<?php
global $ays_pb_db_version;
$ays_pb_db_version = '1.6.3';
/**
 * Fired during plugin activation
 *
 * @link       http://ays-pro.com/
 * @since      1.0.0
 *
 * @package    Ays_Pb
 * @subpackage Ays_Pb/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Ays_Pb
 * @subpackage Ays_Pb/includes
 * @author     AYS Pro LLC <info@ays-pro.com>
 */
class Ays_Pb_Activator {
    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        global $wpdb;
        global $ays_pb_db_version;

        $installed_ver = get_option("ays_pb_db_version");
        $table = $wpdb->prefix . 'ays_pb';
        $categories_table = $wpdb->prefix . 'ays_pb_categories';
        $settings_table = $wpdb->prefix . 'ays_pb_settings';
        $charset_collate = $wpdb->get_charset_collate();

        if ($installed_ver != $ays_pb_db_version) {
            $sql = "CREATE TABLE `" . $table . "` (
                      `id` INT(16) UNSIGNED NOT NULL AUTO_INCREMENT,
                      `title` VARCHAR(256) NOT NULL,
                      `popup_name` VARCHAR(256) NOT NULL,
                      `description` TEXT NOT NULL,
                      `category_id` INT(16) UNSIGNED NOT NULL ,
                      `autoclose` INT NOT NULL,
                      `cookie` INT NOT NULL,
                      `width` INT(16) NOT NULL,
                      `height` INT NOT NULL,
                      `bgcolor` VARCHAR(30) NOT NULL,
                      `textcolor` VARCHAR(30) NOT NULL,
                      `bordersize` INT NOT NULL,
                      `bordercolor` VARCHAR(30) NOT NULL,
                      `border_radius` INT NOT NULL,
                      `shortcode` TEXT NOT NULL,
                      `users_role` TEXT NOT NULL,
                      `custom_class` TEXT NOT NULL,
                      `custom_css` TEXT NOT NULL,
                      `custom_html` TEXT NOT NULL,
                      `onoffswitch` VARCHAR(20) NOT NULL,
                      `show_only_for_author` VARCHAR(20) DEFAULT NULL,
                      `show_all` VARCHAR(20) NOT NULL,
                      `delay` INT NOT NULL, 
                      `scroll_top` INT NOT NULL,
                      `animate_in` VARCHAR(20) NOT NULL,
                      `animate_out` VARCHAR(20) NOT NULL,
                      `action_button` TEXT NOT NULL,
                      `view_place` TEXT NOT NULL,
                      `action_button_type` VARCHAR(20) NOT NULL,
                      `modal_content` VARCHAR(20) NOT NULL,
                      `view_type` VARCHAR(20) NOT NULL,
                      `onoffoverlay` VARCHAR(20) DEFAULT 'On',
                      `overlay_opacity` VARCHAR(20) NOT NULL,
                      `show_popup_title` VARCHAR(20) DEFAULT 'On',
                      `show_popup_desc` VARCHAR(20) DEFAULT 'On',
                      `close_button` VARCHAR(20) DEFAULT 'off',
                      `header_bgcolor` VARCHAR(30) NOT NULL,
                      `bg_image` VARCHAR(256)  DEFAULT '',
                      `log_user` VARCHAR(20) DEFAULT 'On',
                      `guest` VARCHAR(20) DEFAULT 'On',
                      `active_date_check` VARCHAR(20) DEFAULT 'off',
                      `activeInterval` VARCHAR(20) DEFAULT '',
                      `deactiveInterval` VARCHAR(20) DEFAULT '',
                      `active_time_check` VARCHAR(20) DEFAULT 'off',
                      `active_time_start` VARCHAR(20) DEFAULT '',
                      `active_time_end` VARCHAR(20) DEFAULT '',
                      `pb_position` VARCHAR(30) NOT NULL,
                      `pb_margin` INT NOT NULL,
                      `views` INT NOT NULL,
                      `conversions` INT NOT NULL,
                      `options` TEXT DEFAULT '',
                      PRIMARY KEY (`id`)
                    )$charset_collate;";

            $sql_schema = "SELECT *
                    FROM INFORMATION_SCHEMA.TABLES
                    WHERE table_schema = '" . DB_NAME . "'
                    AND table_name = '" . $table . "' ";
            $pb_const = $wpdb->get_results($sql_schema);

            if (empty($pb_const)) {
                $wpdb->query($sql);
            } else {
                dbDelta($sql);
            }

            $sql = "CREATE TABLE `" . $categories_table . "` (
                `id` INT(16) UNSIGNED NOT NULL AUTO_INCREMENT,
                `title` VARCHAR(256) NOT NULL,
                `description` TEXT NOT NULL,
                `published` TINYINT UNSIGNED NOT NULL,
                PRIMARY KEY (`id`)
            )$charset_collate;";

            $sql_schema = "SELECT *
                    FROM INFORMATION_SCHEMA.TABLES
                    WHERE table_schema = '" . DB_NAME . "'
                    AND table_name = '" . $categories_table . "'";
            $pb_cat_const = $wpdb->get_results($sql_schema);

            if (empty($pb_cat_const)) {
                $wpdb->query($sql);
            } else {
                dbDelta($sql);
            }

            $sql = "CREATE TABLE `" . $settings_table . "` (
                      `id` INT(11) NOT NULL AUTO_INCREMENT,
                      `meta_key` TEXT NULL DEFAULT NULL,
                      `meta_value` TEXT NULL DEFAULT NULL,
                      `note` TEXT NULL DEFAULT NULL,
                      `options` TEXT NULL DEFAULT NULL,
                      PRIMARY KEY (`id`)
                    )$charset_collate;";

            $sql_schema = "SELECT *
                    FROM INFORMATION_SCHEMA.TABLES
                    WHERE table_schema = '" . DB_NAME . "'
                    AND table_name = '" . $settings_table . "' ";
            $pb_settings_const = $wpdb->get_results($sql_schema);

            if (empty($pb_settings_const)) {
                $wpdb->query($sql);
            } else {
                dbDelta($sql);
            }

            update_site_option('ays_pb_db_version', $ays_pb_db_version);

            $popup_categories = $wpdb->get_var("SELECT COUNT(*) FROM " . $categories_table . " WHERE `title`='Uncategorized'");
            if ($popup_categories == 0) {
                $wpdb->insert($categories_table, array(
                    'title' => 'Uncategorized',
                    'description' => '',
                    'published' => 1
                ));
            }
        }

        $metas = array(
            "options",
        );

        foreach ($metas as $meta_key) {
            $meta_val = "";

            $sql = "SELECT COUNT(*) FROM `" . $settings_table . "` WHERE `meta_key` = '" . $meta_key . "'";
            $result = $wpdb->get_var($sql);
            if (intval($result) == 0) {
                $result = $wpdb->insert(
                    $settings_table,
                    array(
                        'meta_key' => $meta_key,
                        'meta_value' => $meta_val,
                        'note' => "",
                        'options' => ""
                    ),
                    array('%s', '%s', '%s', '%s')
                );
            }
        }
    }

    public static function ays_pb_db_check() {
        global $ays_pb_db_version;

        if (is_multisite()) {
            global $wpdb;
            $popups_table = $wpdb->prefix . 'ays_pb';
            $network_id = get_current_network_id();

            if ($wpdb->get_var("SHOW TABLES LIKE '$popups_table'") != $popups_table) {
                delete_network_option($network_id, 'ays_pb_db_version');
            }

            if ( get_network_option($network_id, 'ays_pb_db_version') != $ays_pb_db_version ) {
                self::activate();
                self::alter_tables();
            }
        } else {
            if ( get_site_option('ays_pb_db_version') != $ays_pb_db_version ) {
                self::activate();
                self::alter_tables();
            }
        }
    }

    private static function alter_tables() {
        global $wpdb;
        $table = $wpdb->prefix . 'ays_pb';

        $query = "SELECT * FROM " . $table;
        $ays_pb_infos = $wpdb->query($query);

        if ($ays_pb_infos == 0) {
            $options = self::get_default_otions();
            $options = json_encode($options);
            $custom_html = 'Introducing your <strong>First Popup</strong>.<br> Customize text and design to <em>perfectly suit</em> your needs and preferences.';
            $custom_html_sanitized = wp_kses_post($custom_html);

            $query = "INSERT INTO $table (
                        title,
                        popup_name,
                        description,
                        category_id,
                        autoclose,
                        cookie,
                        width,
                        height,
                        bgcolor,
                        textcolor,
                        bordersize,
                        bordercolor,
                        border_radius,
                        shortcode,
                        users_role,
                        custom_class,
                        custom_css,
                        custom_html,
                        onoffswitch,
                        show_only_for_author,
                        show_all,
                        delay,
                        scroll_top,
                        animate_in,
                        animate_out,
                        action_button,
                        view_place,
                        action_button_type,
                        modal_content,
                        view_type,
                        onoffoverlay,
                        overlay_opacity,
                        show_popup_title,
                        show_popup_desc,
                        close_button,
                        header_bgcolor,
                        bg_image,
                        log_user,
                        guest,
                        active_date_check,
                        activeInterval,
                        deactiveInterval,
                        pb_position,
                        pb_margin,
                        views,
                        conversions,
                        options
                      )
                      VALUES (
                        'Demo Title',
                        '',
                        'Demo Description',
                        1,
                        20,
                        0,
                        700,
                        400,
                        '#ffffff',
                        '#000000',
                        1,
                        '#ffffff',
                        7,
                        '',
                        '',
                        '',
                        '',
                        %s,
                        'On',
                        'off',
                        'all',
                        0,
                        0,
                        'fadeIn',
                        'fadeOutUpBig',
                        '',
                        '',
                        'pageLoaded',
                        'custom_html',
                        'default',
                        'On',
                        '0.5',
                        'On',
                        'On',
                        'off',
                        '#ffffff',
                        '',
                        'On',
                        'On',
                        'off',
                        '',
                        '',
                        'center-center',
                        0,
                        0,
                        0,
                        %s
                    )";

            $query = $wpdb->prepare($query, $custom_html_sanitized, $options);
            $wpdb->query($query);
        }
    }

    public static function get_default_otions() {
        $pb_create_author = get_current_user_id();
        $user = get_userdata($pb_create_author);
        $pb_author = array();
        if ( !is_null($user) && $user ) {
            $pb_author = array(
                'id' => $user->ID."",
                'name' => $user->data->display_name
            );
        }

        $author = json_encode($pb_author, JSON_UNESCAPED_SLASHES);

        $x = '✕';
        $default_options = array(
            // General
            'author' => $author,
            'video_theme_url' => '',
            'image_type_img_src' => '',
            'image_type_img_redirect_url' => '',
            'image_type_img_redirect_to_new_tab' => 'off',
            'facebook_page_url' => '',
            'hide_fb_page_cover_photo' => 'off',
            'use_small_fb_header' => 'on',
            'notification_type_components' => array(),
            'notification_type_components_order' => array(),
            'notification_logo_image' => '',
            'notification_logo_redirect_url' => '',
            'notification_logo_redirect_to_new_tab' => 'off',
            'notification_logo_width' => 100,
            'notification_logo_width_measurement_unit' => 'percentage',
            'notification_logo_width_mobile' => 100,
            'notification_logo_width_measurement_unit_mobile' => 'percentage',
            'notification_logo_max_width' => 100,
            'notification_logo_max_width_measurement_unit' => 'pixels',
            'notification_logo_max_width_mobile' => 100,
            'notification_logo_max_width_measurement_unit_mobile' => 'pixels',
            'notification_logo_min_width' => 50,
            'notification_logo_min_width_measurement_unit' => 'pixels',
            'notification_logo_min_width_mobile' => 50,
            'notification_logo_min_width_measurement_unit_mobile' => 'pixels',
            'notification_logo_max_height' => '',
            'notification_logo_min_height' => '',
            'notification_logo_image_sizing' => 'cover',
            'notification_logo_image_shape' => 'rectangle',
            'notification_main_content' => '',
            'notification_button_1_text' => 'Click!',
            'notification_button_1_hover_text' => '',
            'notification_button_1_redirect_url' => '',
            'notification_button_1_redirect_to_new_tab' => 'off',
            'notification_button_1_bg_color' => '',
            'notification_button_1_bg_hover_color' => '',
            'notification_button_1_text_color' => '',
            'notification_button_1_text_hover_color' => '',
            'notification_button_1_text_transformation' => '',
            'notification_button_1_text_decoration' => '',
            'notification_button_1_letter_spacing' => 0,
            'notification_button_1_letter_spacing_mobile' => 0,
            'notification_button_1_font_size' => 15,
            'notification_button_1_font_size_mobile' => 15,
            'notification_button_1_font_weight' => 'normal',
            'notification_button_1_font_weight_mobile' => 'normal',
            'notification_button_1_border_radius' => 6,
            'notification_button_1_border_width' => 0,
            'notification_button_1_border_color' => '',
            'notification_button_1_border_style' => '',
            'notification_button_1_padding_left_right' => 32,
            'notification_button_1_padding_top_bottom' => 16,
            'notification_button_1_transition' => '0.3',
            'notification_button_1_enable_box_shadow' => 'off',
            'notification_button_1_box_shadow_color' => '#FF8319',
            'notification_button_1_box_shadow_x_offset' => 0,
            'notification_button_1_box_shadow_y_offset' => 0,
            'notification_button_1_box_shadow_z_offset' => 10,
            'except_post_types' => array(),
            'except_posts' => array(),
            'show_on_home_page' => 'off',
            'all_posts' => '',
            'enable_pb_position_mobile' => 'off',
            'pb_position_mobile' => 'center-center',
            // Settings
            'enable_open_delay_mobile' => 'off',
            'open_delay_mobile' => 0,
            'enable_scroll_top_mobile' => 'off',
            'scroll_top_mobile' => 0,
            'close_popup_esc' => 'on',
            'close_popup_overlay' => 'off',
            'close_popup_overlay_mobile' => 'off',
            'ays_pb_hover_show_close_btn' => 'off',
            'close_button_position' => 'right-top',
            'enable_close_button_position_mobile' => 'off',
            'close_button_position_mobile' => 'right-top',
            'close_button_text' => $x,
            'enable_close_button_text_mobile' => 'off',
            'close_button_text_mobile' => $x,
            'close_button_hover_text' => '',
            'enable_autoclose_delay_text_mobile' => 'off',
            'pb_autoclose_mobile' => 20,
            'enable_hide_timer' => 'off',
            'enable_hide_timer_mobile' => 'off',
            'enable_autoclose_on_completion' => 'off',
            'close_button_delay' => 0,
            'enable_close_button_delay_for_mobile' => 'off',
            'close_button_delay_for_mobile' => 0,
            'enable_overlay_text_mobile' => 'off',
            'overlay_mobile_opacity' => '0.5',
            'blured_overlay' => 'off',
            'blured_overlay_mobile' => 'off',
            'enable_pb_sound' => 'off',
            'enable_social_links' => 'off',
            'social_buttons_heading' => '',
            'social_links' => array(
                'linkedin_link' => '',
                'facebook_link' => '',
                'twitter_link' => '',
                'vkontakte_link' => '',
                'youtube_link' => '',
                'instagram_link' => '',
                'behance_link' => '',
            ),
            'create_date' => current_time('mysql'),
            'create_author' => $pb_create_author,
            'enable_dismiss' => 'off',
            'enable_dismiss_text' => 'Dismiss ad',
            'enable_dismiss_mobile' => 'off',
            'enable_dismiss_text_mobile' => 'Dismiss ad',
            'disable_scroll' => 'off',
            'disable_scroll_mobile' => 'off',
            'disable_scroll_on_popup' => 'off',
            'disable_scroll_on_popup_mobile' => 'off',
            'show_scrollbar' => 'off',
            // Styles
            'enable_display_content_mobile' => 'off',
            'show_popup_title_mobile' => 'off',
            'show_popup_desc_mobile' => 'off',
            'popup_width_by_percentage_px' => 'pixels',
            'popup_width_by_percentage_px_mobile' => 'percentage',
            'mobile_width' => '',
            'mobile_max_width' => '',
            'mobile_height' => '',
            'pb_max_height' => '',
            'popup_max_height_by_percentage_px' => 'pixels',
            'pb_max_height_mobile' => '',
            'popup_max_height_by_percentage_px_mobile' => 'pixels',
            'pb_min_height' => '',
            'enable_pb_fullscreen' => 'off',
            'popup_content_padding' => 20,
            'popup_content_padding_mobile' => 20,
            'popup_padding_by_percentage_px' => 'pixels',
            'pb_font_family' => 'inherit',
            'pb_font_size' => 13,
            'pb_font_size_for_mobile' => 13,
            'enable_pb_title_text_shadow' => 'off',
            'pb_title_text_shadow' => 'rgba(255,255,255,0)',
            'pb_title_text_shadow_x_offset' => 2,
            'pb_title_text_shadow_y_offset' => 2,
            'pb_title_text_shadow_z_offset' => 0,
            'enable_pb_title_text_shadow_mobile' => 'off',
            'pb_title_text_shadow_mobile' => 'rgba(255,255,255,0)',
            'pb_title_text_shadow_x_offset_mobile' => 2,
            'pb_title_text_shadow_y_offset_mobile' => 2,
            'pb_title_text_shadow_z_offset_mobile' => 0,
            'enable_animate_in_mobile' => 'off',
            'animate_in_mobile' => 'fadeIn',
            'enable_animate_out_mobile' => 'off',
            'animate_out_mobile' => 'fadeOutUpBig',
            'animation_speed' => 1,
            'enable_animation_speed_mobile' => 'off',
            'animation_speed_mobile' => 1,
            'close_animation_speed' => 1,
            'enable_close_animation_speed_mobile' => 'off',
            'close_animation_speed_mobile' => 1,
            'enable_bgcolor_mobile' => 'off',
            'bgcolor_mobile' => '#ffffff',
            'enable_bg_image_mobile' => 'off',
            'bg_image_mobile' => '',
            'pb_bg_image_position' => 'center-center',
            'enable_pb_bg_image_position_mobile' => 'off',
            'pb_bg_image_position_mobile' => 'center-center',
            'pb_bg_image_sizing' => 'cover',
            'enable_pb_bg_image_sizing_mobile' => 'off',
            'pb_bg_image_sizing_mobile' => 'cover',
            'enable_background_gradient' => 'off',
            'background_gradient_color_1' => '#000',
            'background_gradient_color_2' => '#fff',
            'pb_gradient_direction' => 'vertical',
            'enable_background_gradient_mobile' => 'off',
            'background_gradient_color_1_mobile' => '#000',
            'background_gradient_color_2_mobile' => '#fff',
            'pb_gradient_direction_mobile' => 'vertical',
            'overlay_color' => '#000',
            'enable_overlay_color_mobile' => 'off',
            'overlay_color_mobile' => '#000',
            'enable_bordersize_mobile' => 'off',
            'bordersize_mobile' => 1,
            'border_style' => 'solid',
            'enable_border_style_mobile' => 'off',
            'border_style_mobile' => 'solid',
            'enable_bordercolor_mobile' => 'off',
            'bordercolor_mobile' => '#ffffff',
            'enable_border_radius_mobile' => 'off',
            'border_radius_mobile' => 7,
            'close_button_image' => '',
            'close_button_color' => '#000000',
            'close_button_hover_color' => '#000000',
            'close_button_size' => 1,
            'enable_box_shadow' => 'off',
            'box_shadow_color' => '#000',
            'pb_box_shadow_x_offset' => 0,
            'pb_box_shadow_y_offset' => 0,
            'pb_box_shadow_z_offset' => 15,
            'enable_box_shadow_mobile' => 'off',
            'box_shadow_color_mobile' => '#000',
            'pb_box_shadow_x_offset_mobile' => 0,
            'pb_box_shadow_y_offset_mobile' => 0,
            'pb_box_shadow_z_offset_mobile' => 15,
            'pb_bg_image_direction_on_mobile' => 'on',
            // Limitation Users
            'show_only_once' => 'off',
            'pb_mobile' => 'off',
            'hide_on_pc' => 'off',
            'hide_on_tablets' => 'off',
        );

        return $default_options;
    }
}
