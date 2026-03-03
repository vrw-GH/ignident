<?php

class Ays_Pb_Data {

    public static function ays_version_compare($version1, $operator, $version2) {
        $_fv = intval ( trim ( str_replace ( '.', '', $version1 ) ) );
        $_sv = intval ( trim ( str_replace ( '.', '', $version2 ) ) );

        if (strlen ( $_fv ) > strlen ( $_sv )) {
            $_sv = str_pad ( $_sv, strlen ( $_fv ), 0 );
        }

        if (strlen ( $_fv ) < strlen ( $_sv )) {
            $_fv = str_pad ( $_fv, strlen ( $_sv ), 0 );
        }

        return version_compare ( ( string ) $_fv, ( string ) $_sv, $operator );
    }

    public static function get_max_id() {
        global $wpdb;
        $pb_table = $wpdb->prefix . 'ays_pb';

        $sql = "SELECT max(id) FROM {$pb_table}";

        $result = $wpdb->get_var($sql);

        return $result;
    }

    public static function get_popups() {
        global $wpdb;
        $popups_table = esc_sql($wpdb->prefix . 'ays_pb');

        $sql = "SELECT id, title
                FROM {$popups_table}";

        $popups = $wpdb->get_results($sql , "ARRAY_A");

        return $popups;
    }

    public static function get_pb_by_id( $id ){
        global $wpdb;

        $ays_pb_table = $wpdb->prefix . 'ays_pb';

        $results = '';
        if($id != null){
            $sql = "SELECT * FROM {$ays_pb_table} WHERE id =".$id;
            $results = $wpdb->get_results( $sql, 'ARRAY_A' );
        }

        return $results;
    }

    public static function get_pb_options_by_id( $id ){
        global $wpdb;
        $ays_pb_table = $wpdb->prefix .'ays_pb';

        $options = '';
        if($id != null){
            $sql = "SELECT options FROM {$ays_pb_table} WHERE id =".$id;
            $results = $wpdb->get_row( $sql, 'ARRAY_A' );

            $options = ( json_decode($results['options'], true) != null ) ? json_decode($results['options'], true) : array();
        }

        return $options;
    }

    public static function replace_message_variables($content, $data){
        foreach($data as $variable => $value){
            $content = str_replace("%%".$variable."%%", $value, $content);
        }
        return $content;
    }

    public static function get_category_by_id($id){
        global $wpdb;

        $ays_pb_category_table = $wpdb->prefix .'ays_pb_categories';

        $results = '';
        if($id != null){
            $sql = "SELECT * FROM {$ays_pb_category_table} WHERE id =".$id;
            $results = $wpdb->get_row( $sql, 'ARRAY_A' );
        }

        return $results;
    }

    public static function get_user_profile_data(){

        $user_first_name = '';
        $user_last_name  = '';
        $user_nickname   = '';
        $user_wordpress_roles = '';
        $user_id = get_current_user_id();
        if($user_id != 0){
            $usermeta = get_user_meta( $user_id );
            if($usermeta !== null){
                $user_first_name = (isset($usermeta['first_name'][0]) && $usermeta['first_name'][0] != '' ) ? sanitize_text_field( $usermeta['first_name'][0] ) : '';
                $user_last_name  = (isset($usermeta['last_name'][0]) && $usermeta['last_name'][0] != '' ) ? sanitize_text_field( $usermeta['last_name'][0] ) : '';
                $user_nickname   = (isset($usermeta['nickname'][0]) &&  $usermeta['nickname'][0] != '' ) ? sanitize_text_field( $usermeta['nickname'][0] ) : '';
            }
        }
        $current_user_data = get_userdata( $user_id );
        if ( ! is_null( $current_user_data ) && $current_user_data ) {
            $user_display_name    = ( isset( $current_user_data->data->display_name ) && $current_user_data->data->display_name != '' ) ? sanitize_text_field( $current_user_data->data->display_name ) : "";
            $user_wordpress_email = ( isset( $current_user_data->data->user_email ) && $current_user_data->data->user_email != '' ) ? sanitize_text_field( $current_user_data->data->user_email ) : "";

            $user_wordpress_roles = ( isset( $current_user_data->roles ) && ! empty( $current_user_data->roles ) ) ? $current_user_data->roles : "";

            if ( !empty( $user_wordpress_roles ) && $user_wordpress_roles != "" ) {
                if ( is_array( $user_wordpress_roles ) ) {
                    $user_wordpress_roles = implode(",", $user_wordpress_roles);
                }
            }
        }

        $message_data = array(
            'user_first_name'       => $user_first_name,
            'user_last_name'        => $user_last_name,
            'user_nickname'         => $user_nickname,
            'user_wordpress_roles'  => $user_wordpress_roles,
        );
		
        return $message_data;
    }

    public static function hex2rgba($color, $opacity = false){

        $default = 'rgb(0,0,0)';

        //Return default if no color provided
        if (empty($color))
            return $default;

        //Sanitize $color if "#" is provided
        if ($color[0] == '#') {
            $color = substr($color, 1);
        }else{
            return $color;
        }

        //Check if color has 6 or 3 characters and get values
        if (strlen($color) == 6) {
            $hex = array($color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]);
        } elseif (strlen($color) == 3) {
            $hex = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
        } else {
            return $default;
        }

        //Convert hexadec to rgb
        $rgb = array_map('hexdec', $hex);

        //Check if opacity is set(rgba or rgb)
        if ($opacity) {
            if (abs($opacity) > 1)
                $opacity = 1.0;
            $output = 'rgba(' . implode(",", $rgb) . ',' . $opacity . ')';
        } else {
            $output = 'rgb(' . implode(",", $rgb) . ')';
        }

        //Return rgb(a) color string
        return $output;
    }

    /*
    ==========================================
        Sale Banner | Start
    ==========================================
    */

    public function ays_pb_sale_baner() {
        // Check for permissions.
        if (current_user_can('manage_options')) {
            $ays_pb_sale_date = get_option('ays_pb_sale_date');

            $val = 60*60*24*5;

            $current_date = current_time( 'mysql' );
            $date_diff = strtotime($current_date) - intval(strtotime($ays_pb_sale_date));
            $days_diff = $date_diff / $val;

            if (intval($days_diff) > 0) {
                update_option('ays_pb_sale_btn', 0);
            }

            $ays_popup_box_flag = intval(get_option('ays_pb_sale_btn'));
            if ($ays_popup_box_flag == 0 ) {
                if (isset($_GET['page']) && strpos($_GET['page'], AYS_PB_NAME) !== false) {
                    if($this->get_max_id() > 1){
                        // $this->ays_pb_new_halloween_bundle_message_2025($ays_popup_box_flag);
                        // $this->ays_pb_black_friday_message($ays_popup_box_flag);
                        // $this->ays_pb_christmas_banner_message_2025($ays_popup_box_flag);
                        $this->ays_pb_new_mega_bundle_message_2026($ays_popup_box_flag);
                    }
                }
            }
        }
    }

    public static function ays_pb_winter_bundle_message($ishmar){
        if($ishmar == 0 ){
            $content = array();

            $content[] = '<div id="ays-pb-dicount-month-main" class="ays-pb notice notice-success is-dismissible ays_pb_dicount_info">';
                $content[] = '<div id="ays-pb-dicount-month" class="ays_pb_dicount_month">';
                    $content[] = '<a href="https://ays-pro.com/winter-bundle" target="_blank" class="ays-pb-sale-banner-link"><img src="' . AYS_PB_ADMIN_URL . '/images/winter_bundle_logo.png"></a>';

                    $content[] = '<div class="ays-pb-dicount-wrap-box">';

                        $content[] = '<strong>';
                            $content[] = esc_html__( "Limited Time <span class='ays-pb-dicount-wrap-color'>50%</span> SALE on <br><span><a href='https://ays-pro.com/winter-bundle' target='_blank' class='ays-pb-dicount-wrap-color ays-pb-dicount-wrap-text-decoration' style='display:block;'>Winter Bundle</a></span> (Copy + Popup + Survey)!", "ays-popup-box" );
                        $content[] = '</strong>';

                        $content[] = '<br>';

                        $content[] = '<strong>';
                                $content[] = esc_html__( "Hurry up! Ending on. <a href='https://ays-pro.com/winter-bundle' target='_blank'>Check it out!</a>", "ays-popup-box" );
                        $content[] = '</strong>';
                            
                    $content[] = '</div>';

                    $content[] = '<div class="ays-pb-dicount-wrap-box">';

                        $content[] = '<div id="ays-pb-countdown-main-container">';
                            $content[] = '<div class="ays-pb-countdown-container">';

                                $content[] = '<div id="ays-pb-countdown">';
                                    $content[] = '<ul>';
                                        $content[] = '<li><span id="ays-pb-countdown-days"></span>days</li>';
                                        $content[] = '<li><span id="ays-pb-countdown-hours"></span>Hours</li>';
                                        $content[] = '<li><span id="ays-pb-countdown-minutes"></span>Minutes</li>';
                                        $content[] = '<li><span id="ays-pb-countdown-seconds"></span>Seconds</li>';
                                    $content[] = '</ul>';
                                $content[] = '</div>';

                                $content[] = '<div id="ays-pb-countdown-content" class="emoji">';
                                    $content[] = '<span>🚀</span>';
                                    $content[] = '<span>⌛</span>';
                                    $content[] = '<span>🔥</span>';
                                    $content[] = '<span>💣</span>';
                                $content[] = '</div>';

                            $content[] = '</div>';

                            $content[] = '<form action="" method="POST">';
                                $content[] = '<button class="btn btn-link ays-button" name="ays_pb_sale_btn_winter" style="height: 32px; margin-left: 0;padding-left: 0">Dismiss ad</button>';
                                $content[] = '<button class="btn btn-link ays-button" name="ays_pb_sale_btn_winter_for_two_months" style="height: 32px; padding-left: 0">Dismiss ad for 2 months</button>';
                            $content[] = '</form>';

                        $content[] = '</div>';
                            
                    $content[] = '</div>';

                    $content[] = '<a href="https://ays-pro.com/winter-bundle" class="button button-primary ays-button" id="ays-button-top-buy-now" target="_blank">' . esc_html__( 'Buy Now !', "ays-popup-box" ) . '</a>';
                $content[] = '</div>';
            $content[] = '</div>';

            $content = implode( '', $content );
            echo $content;
        }
    }

    public static function ays_pb_spring_bundle_message($ishmar){
        $max_id = self::get_max_id();
        if($ishmar == 0 && $max_id > 1){
            $content = array();

            $content[] = '<div id="ays-pb-dicount-month-main" class="notice notice-success is-dismissible ays_pb_dicount_info">';
                $content[] = '<div id="ays-pb-dicount-month" class="ays_pb_dicount_month">';
                    $content[] = '<a href="https://ays-pro.com/spring-bundle" target="_blank" class="ays-pb-sale-banner-link"><img src="' . AYS_PB_ADMIN_URL . '/images/spring_bundle_logo_box.png"></a>';

                    $content[] = '<div class="ays-pb-dicount-wrap-box">';
                        $content[] = '<p style="margin: 0;">';
                            $content[] = '<strong>';
                                $content[] = esc_html__( "Spring is here! 
                                                    <span class='ays-pb-dicount-wrap-color'>50%</span> 
                                                        SALE on 
                                                    <span>
                                                        <a href='https://ays-pro.com/spring-bundle' target='_blank' class='ays-pb-dicount-wrap-color ays-pb-dicount-wrap-text-decoration'>
                                                            Spring Bundle
                                                        </a>
                                                    </span>
                                                    <span style='display: block;'>
                                                        pb + Popup + Copy
                                                    </span>", "ays-popup-box" );
                            $content[] = '</strong>';
                            $content[] = '<br>';
                            // $content[] = '<strong>';
                            //         $content[] = esc_html__( "Hurry up! Ending on. <a href='https://ays-pro.com/spring-bundle' target='_blank'>Check it out!</a>", "ays-popup-box" );
                            // $content[] = '</strong>';
                        $content[] = '</p>';
                    $content[] = '</div>';

                    $content[] = '<div class="ays-pb-dicount-wrap-box">';

                        $content[] = '<div id="ays-pb-countdown-main-container">';
                            // $content[] = '<div class="ays-pb-countdown-container">';

                            //     $content[] = '<div id="ays-pb-countdown">';
                            //         $content[] = '<ul>';
                            //             $content[] = '<li><span id="ays-pb-countdown-days"></span>days</li>';
                            //             $content[] = '<li><span id="ays-pb-countdown-hours"></span>Hours</li>';
                            //             $content[] = '<li><span id="ays-pb-countdown-minutes"></span>Minutes</li>';
                            //             $content[] = '<li><span id="ays-pb-countdown-seconds"></span>Seconds</li>';
                            //         $content[] = '</ul>';
                            //     $content[] = '</div>';

                            //     $content[] = '<div id="ays-pb-countdown-content" class="emoji">';
                            //         $content[] = '<span>🚀</span>';
                            //         $content[] = '<span>⌛</span>';
                            //         $content[] = '<span>🔥</span>';
                            //         $content[] = '<span>💣</span>';
                            //     $content[] = '</div>';

                            // $content[] = '</div>';

                            $content[] = '<form action="" method="POST">';
                                $content[] = '<button class="btn btn-link ays-button" name="ays_pb_sale_btn_spring" style="height: 32px; margin-left: 0;padding-left: 0">Dismiss ad</button>';
                                $content[] = '<button class="btn btn-link ays-button" name="ays_pb_sale_btn_spring_for_two_months" style="height: 32px; padding-left: 0">Dismiss ad for 2 months</button>';
                            $content[] = '</form>';

                        $content[] = '</div>';
                            
                    $content[] = '</div>';

                    $content[] = '<a href="https://ays-pro.com/spring-bundle" class="button button-primary ays-button" id="ays-button-top-buy-now" target="_blank">' . esc_html__( 'Buy Now !', "ays-popup-box" ) . '</a>';
                $content[] = '</div>';
            $content[] = '</div>';

            $content = implode( '', $content );
            echo $content;
        }
    }

    public static function ays_pb_helloween_message($ishmar){
        if($ishmar == 0 ){
            $content = array();

            $content[] = '<div id="ays-pb-dicount-month-main-helloween" class="notice notice-success is-dismissible ays_pb_dicount_info">';
                $content[] = '<div id="ays-pb-dicount-month-helloween" class="ays_pb_dicount_month_helloween">';
                    $content[] = '<div class="ays-pb-dicount-wrap-box-helloween-limited">';

                        $content[] = '<p>';
                            $content[] = esc_html__( "Limited Time 
                            <span class='ays-pb-dicount-wrap-color-helloween' style='color:#b2ff00;'>20%</span> 
                            <span>
                                SALE on
                            </span> 
                            <br>
                            <span style='' class='ays-pb-helloween-bundle'>
                                <a href='https://ays-pro.com/wordpress/popup-box?utm_source=dashboard&utm_medium=popup-free&utm_campaign=helloween-sale-banner' target='_blank' class='ays-pb-dicount-wrap-color-helloween ays-pb-dicount-wrap-text-decoration-helloween' style='display:block; color:#b2ff00;margin-right:6px;'>
                                    Popup Box
                                </a>
                            </span>", "ays-popup-box" );
                        $content[] = '</p>';
                        $content[] = '<p>';
                                $content[] = esc_html__( "Hurry up! 
                                                <a href='https://ays-pro.com/wordpress/popup-box?utm_source=dashboard&utm_medium=popup-free&utm_campaign=helloween-sale-banner' target='_blank' style='color:#ffc700;'>
                                                    Check it out!
                                                </a>", "ays-popup-box" );
                        $content[] = '</p>';
                            
                    $content[] = '</div>';

                    
                    $content[] = '<div class="ays-pb-helloween-bundle-buy-now-timer">';
                        $content[] = '<div class="ays-pb-dicount-wrap-box-helloween-timer">';
                            $content[] = '<div id="ays-pb-countdown-main-container" class="ays-pb-countdown-main-container-helloween">';
                                $content[] = '<div class="ays-pb-countdown-container-helloween">';
                                    $content[] = '<div id="ays-pb-countdown">';
                                        $content[] = '<ul>';
                                            $content[] = '<li><p><span id="ays-pb-countdown-days"></span><span>days</span></p></li>';
                                            $content[] = '<li><p><span id="ays-pb-countdown-hours"></span><span>Hours</span></p></li>';
                                            $content[] = '<li><p><span id="ays-pb-countdown-minutes"></span><span>Mins</span></p></li>';
                                            $content[] = '<li><p><span id="ays-pb-countdown-seconds"></span><span>Secs</span></p></li>';
                                        $content[] = '</ul>';
                                    $content[] = '</div>';

                                    $content[] = '<div id="ays-pb-countdown-content" class="emoji">';
                                        $content[] = '<span>🚀</span>';
                                        $content[] = '<span>⌛</span>';
                                        $content[] = '<span>🔥</span>';
                                        $content[] = '<span>💣</span>';
                                    $content[] = '</div>';

                                $content[] = '</div>';

                            $content[] = '</div>';
                                
                        $content[] = '</div>';
                        $content[] = '<div class="ays-pb-dicount-wrap-box ays-buy-now-button-box-helloween">';
                            $content[] = '<a href="https://ays-pro.com/wordpress/popup-box?utm_source=dashboard&utm_medium=popup-free&utm_campaign=helloween-sale-banner" class="button button-primary ays-buy-now-button-helloween" id="ays-button-top-buy-now-helloween" target="_blank" style="" >' . esc_html__( 'Buy Now !', "ays-popup-box" ) . '</a>';
                        $content[] = '</div>';
                    $content[] = '</div>';

                $content[] = '</div>';

                $content[] = '<div style="position: absolute;right: 0;bottom: 1px;"  class="ays-pb-dismiss-buttons-container-for-form-helloween">';
                    $content[] = '<form action="" method="POST">';
                        $content[] = '<div id="ays-pb-dismiss-buttons-content-helloween">';
                            if( current_user_can( 'manage_options' ) ){
                                $content[] = '<button class="btn btn-link ays-button-helloween" name="ays_pb_sale_btn" style="height: 32px; margin-left: 0;padding-left: 0">Dismiss ad</button>';
                                $content[] = wp_nonce_field( AYS_PB_NAME . '-sale-banner' ,  AYS_PB_NAME . '-sale-banner' );
                            }
                        $content[] = '</div>';
                    $content[] = '</form>';
                $content[] = '</div>';
                // $content[] = '<button type="button" class="notice-dismiss">';
                // $content[] = '</button>';
            $content[] = '</div>';

            $content = implode( '', $content );

            echo $content;
        }
    }

    // Black Friday banner
    public static function ays_pb_black_friday_message2($ishmar){
        if($ishmar == 0 ){
            $content = array();

            $content[] = '<div id="ays-pb-dicount-black-friday-month-main" class="notice notice-success is-dismissible ays_pb_dicount_info">';
                $content[] = '<div id="ays-pb-dicount-black-friday-month" class="ays_pb_dicount_month">';
                    $content[] = '<div class="ays-pb-dicount-black-friday-box">';
                        $content[] = '<div class="ays-pb-dicount-black-friday-wrap-box ays-pb-dicount-black-friday-wrap-box-80" style="width: 70%;">';
                            $content[] = '<div class="ays-pb-dicount-black-friday-title-row">' . esc_html__( 'Limited Time', "ays-popup-box" ) .' '. '<a href="https://ays-pro.com/essential-bundle?utm_source=dashboard&utm_medium=popup-free&utm_campaign=black-friday-sale-banner" class="ays-pb-dicount-black-friday-button-sale" target="_blank">' . esc_html__( 'Sale', "ays-popup-box" ) . '</a>' . '</div>';
                            $content[] = '<div class="ays-pb-dicount-black-friday-title-row ays-pb-dicount-black-friday-title-row-product"><span>' . esc_html__( 'Essential Bundle', "ays-popup-box" ) . '</span><span>' . esc_html__( '( Quiz + Form + Popup )', "ays-popup-box" ) .'</span></div>';
                        $content[] = '</div>';

                        $content[] = '<div class="ays-pb-dicount-black-friday-wrap-box ays-pb-dicount-black-friday-wrap-text-box">';
                            $content[] = '<div class="ays-pb-dicount-black-friday-text-row">' . esc_html__( '50% off', "ays-popup-box" ) . '</div>';
                        $content[] = '</div>';

                        $content[] = '<div class="ays-pb-dicount-black-friday-wrap-box" style="width: 25%;">';
                            $content[] = '<div id="ays-pb-countdown-main-container">';
                                $content[] = '<div class="ays-pb-countdown-container">';
                                    $content[] = '<div id="ays-pb-countdown" style="display: block;">';
                                        $content[] = '<ul>';
                                            $content[] = '<li><span id="ays-pb-countdown-days">0</span>' . esc_html__( 'Days', "ays-popup-box" ) . '</li>';
                                            $content[] = '<li><span id="ays-pb-countdown-hours">0</span>' . esc_html__( 'Hours', "ays-popup-box" ) . '</li>';
                                            $content[] = '<li><span id="ays-pb-countdown-minutes">0</span>' . esc_html__( 'Minutes', "ays-popup-box" ) . '</li>';
                                            $content[] = '<li><span id="ays-pb-countdown-seconds">0</span>' . esc_html__( 'Seconds', "ays-popup-box" ) . '</li>';
                                        $content[] = '</ul>';
                                    $content[] = '</div>';
                                    $content[] = '<div id="ays-pb-countdown-content" class="emoji" style="display: none;">';
                                        $content[] = '<span>🚀</span>';
                                        $content[] = '<span>⌛</span>';
                                        $content[] = '<span>🔥</span>';
                                        $content[] = '<span>💣</span>';
                                    $content[] = '</div>';
                                $content[] = '</div>';
                            $content[] = '</div>';
                        $content[] = '</div>';

                        $content[] = '<div class="ays-pb-dicount-black-friday-wrap-box" style="width: 25%;">';
                            $content[] = '<a href="https://ays-pro.com/essential-bundle?utm_source=dashboard&utm_medium=popup-free&utm_campaign=black-friday-sale-banner" class="ays-pb-dicount-black-friday-button-buy-now" target="_blank">' . esc_html__( 'Get Your Deal', "ays-popup-box" ) . '</a>';
                        $content[] = '</div>';
                    $content[] = '</div>';
                $content[] = '</div>';

                $content[] = '<div style="position: absolute;right: 0;bottom: 1px;"  class="ays-pb-dismiss-buttons-container-for-form-black-friday">';
                    $content[] = '<form action="" method="POST">';
                        $content[] = '<div id="ays-pb-dismiss-buttons-content-black-friday">';
                            if( current_user_can( 'manage_options' ) ){
                                $content[] = '<button class="btn btn-link ays-button-black-friday" name="ays_pb_sale_btn" style="">' . esc_html__( 'Dismiss ad', "ays-popup-box" ) . '</button>';
                                $content[] = wp_nonce_field( AYS_PB_NAME . '-sale-banner' ,  AYS_PB_NAME . '-sale-banner' );
                            }
                        $content[] = '</div>';
                    $content[] = '</form>';
                $content[] = '</div>';
            $content[] = '</div>';

            $content = implode( '', $content );

            echo $content;
        }
    }

    // Black Friday 2024
    // public function ays_pb_black_friday_message_2024($ishmar){
    //     if($ishmar == 0 ){
    //         $content = array();

    //         $content[] = '<div id="ays-pb-black-friday-bundle-dicount-month-main" class="notice notice-success is-dismissible ays_pb_dicount_info">';
    //             $content[] = '<div id="ays-pb-dicount-month" class="ays_pb_dicount_month">';

    //                 $content[] = '<div class="ays-pb-dicount-wrap-box ays-pb-dicount-wrap-countdown-box">';

    //                     $content[] = '<div id="ays-pb-countdown-main-container">';
    //                         $content[] = '<div class="ays-pb-countdown-container">';

    //                             $content[] = '<div id="ays-pb-countdown">';

    //                                 $content[] = '<div>';
    //                                     $content[] = esc_html__( "Offer ends in:", "ays-popup-box" );
    //                                 $content[] = '</div>';

    //                                 $content[] = '<ul>';
    //                                     $content[] = '<li><span id="ays-pb-countdown-days"></span>'. esc_html__( "Days", "ays-popup-box" ) .'</li>';
    //                                     $content[] = '<li><span id="ays-pb-countdown-hours"></span>'. esc_html__( "Hours", "ays-popup-box" ) .'</li>';
    //                                     $content[] = '<li><span id="ays-pb-countdown-minutes"></span>'. esc_html__( "Minutes", "ays-popup-box" ) .'</li>';
    //                                     $content[] = '<li><span id="ays-pb-countdown-seconds"></span>'. esc_html__( "Seconds", "ays-popup-box" ) .'</li>';
    //                                 $content[] = '</ul>';
    //                             $content[] = '</div>';

    //                             $content[] = '<div id="ays-pb-countdown-content" class="emoji">';
    //                                 $content[] = '<span>🚀</span>';
    //                                 $content[] = '<span>⌛</span>';
    //                                 $content[] = '<span>🔥</span>';
    //                                 $content[] = '<span>💣</span>';
    //                             $content[] = '</div>';

    //                         $content[] = '</div>';
    //                     $content[] = '</div>';
                            
    //                 $content[] = '</div>';

    //                 $content[] = '<div class="ays-pb-dicount-wrap-box ays-pb-dicount-wrap-text-box">';
    //                     $content[] = '<div>';

    //                         $content[] = '<span class="ays-pb-black-friday-bundle-title">';
    //                             $content[] = esc_html__( "<span><a href='https://ays-pro.com/christmas-bundle?utm_source=dashboard&utm_medium=popup-free&utm_campaign=black-friday-engagement-bundle-sale-banner' class='ays-pb-black-friday-bundle-title-link' target='_blank'>Black Friday Sale</a></span>", "ays-popup-box" );
    //                         $content[] = '</span>';

    //                         $content[] = '</br>';

    //                         $content[] = '<span class="ays-pb-black-friday-bundle-desc">';
    //                             $content[] = '<a class="ays-pb-black-friday-bundle-desc" href="https://ays-pro.com/christmas-bundle?utm_source=dashboard&utm_medium=popup-free&utm_campaign=black-friday-engagement-bundle-sale-banner" class="ays-pb-black-friday-bundle-title-link" target="_blank">';
    //                                 $content[] = esc_html__( "50% OFF", "ays-popup-box" );
    //                             $content[] = '</a>';
    //                         $content[] = '</span>';
    //                     $content[] = '</div>';

    //                     $content[] = '<div style="position: absolute;right: 10px;bottom: 1px;" class="ays-pb-dismiss-buttons-container-for-form">';

    //                         $content[] = '<form action="" method="POST">';
    //                             $content[] = '<div id="ays-pb-dismiss-buttons-content">';
    //                             if( current_user_can( 'manage_options' ) ){
    //                                 $content[] = '<button class="btn btn-link ays-button" name="ays_pb_sale_btn" style="height: 32px; margin-left: 0;padding-left: 0">'. esc_html__( "Dismiss ad", "ays-popup-box" ) .'</button>';
    //                                 $content[] = wp_nonce_field( AYS_PB_NAME . '-sale-banner' ,  AYS_PB_NAME . '-sale-banner' );
    //                             }
    //                             $content[] = '</div>';
    //                         $content[] = '</form>';
                            
    //                     $content[] = '</div>';

    //                 $content[] = '</div>';

    //                 $content[] = '<div class="ays-pb-dicount-wrap-box ays-pb-dicount-wrap-text-box">';
    //                     $content[] = '<span class="ays-pb-black-friday-bundle-title">';
    //                         $content[] = '<a class="ays-pb-black-friday-bundle-title-link" href="https://ays-pro.com/christmas-bundle?utm_source=dashboard&utm_medium=popup-free&utm_campaign=black-friday-engagement-bundle-sale-banner" target="_blank">';
    //                             $content[] = esc_html__( 'Engagement Bundle', "ays-popup-box" );
    //                         $content[] = '</a>';
    //                     $content[] = '</span>';
    //                 $content[] = '</div>';

    //                 $content[] = '<div class="ays-pb-dicount-wrap-box ays-pb-dicount-wrap-button-box">';
    //                     $content[] = '<a href="https://ays-pro.com/christmas-bundle?utm_source=dashboard&utm_medium=popup-free&utm_campaign=black-friday-engagement-bundle-sale-banner" class="button button-primary ays-button" id="ays-button-top-buy-now" target="_blank">' . esc_html__( 'Get Your Deal', "ays-popup-box" ) . '</a>';
    //                     $content[] = '<span class="ays-pb-dicount-one-time-text">';
    //                         $content[] = esc_html__( "One-time payment", "ays-popup-box" );
    //                     $content[] = '</span>';
    //                 $content[] = '</div>';
    //             $content[] = '</div>';
    //         $content[] = '</div>';

    //         $content = implode( '', $content );
    //         echo $content;
    //     }
    // }

    /*
    ==========================================
        Sale Banner | End
    ==========================================
    */

    // Engagement Bundle
    public function ays_pb_engagement_sale_message($ishmar){
        if($ishmar == 0 ){
            $content = array();

            $content[] = '<div id="ays-pb-engagement-dicount-month-main" class="notice notice-success is-dismissible ays_pb-engagement_dicount_info">';
                $content[] = '<div id="ays-pb-engagement-dicount-month" class="ays_pb-engagement_dicount_month">';
                    $content[] = '<a href="https://popup-plugin.com" target="_blank" class="ays-pb-engagement-sale-banner-link"><img src="' . AYS_PB_ADMIN_URL . '/images/icons/icon-popup-128x128.png"></a>';

                    $content[] = '<div class="ays-pb-engagement-dicount-wrap-box">';

                        $content[] = '<strong style="font-weight: bold;">';
                            $content[] = esc_html__( "Limited Time <span style='color:#E85011;'>20%</span> SALE on <span><a href='https://popup-plugin.com' target='_blank' style='color:#E85011; text-decoration: underline;'>Popup Box</a></span>", "ays-popup-box" );
                        $content[] = '</strong>';

                        $content[] = '<br>';

                        $content[] = '<strong>';
                                $content[] = esc_html__( "Hurry up! <a href='https://popup-plugin.com' target='_blank'>Check it out!</a>", "ays-popup-box" );
                        $content[] = '</strong>';

                        $content[] = '<div style="position: absolute;right: 10px;bottom: 1px;" class="ays-pb-engagement-dismiss-buttons-container-for-form">';

                            $content[] = '<form action="" method="POST">';
                                $content[] = '<div id="ays-pb-engagement-dismiss-buttons-content">';
                                    $content[] = '<button class="btn btn-link ays-button" name="ays_pb_sale_btn" style="height: 32px; margin-left: 0;padding-left: 0">Dismiss ad</button>';
                                $content[] = '</div>';
                            $content[] = '</form>';
                            
                        $content[] = '</div>';

                    $content[] = '</div>';

                    $content[] = '<div class="ays-pb-engagement-dicount-wrap-box">';

                        $content[] = '<div id="ays-pb-countdown-main-container">';
                            $content[] = '<div class="ays-pb-countdown-container">';

                                $content[] = '<div id="ays-pb-countdown">';
                                    $content[] = '<ul>';
                                        $content[] = '<li><span id="ays-pb-countdown-days"></span>days</li>';
                                        $content[] = '<li><span id="ays-pb-countdown-hours"></span>Hours</li>';
                                        $content[] = '<li><span id="ays-pb-countdown-minutes"></span>Minutes</li>';
                                        $content[] = '<li><span id="ays-pb-countdown-seconds"></span>Seconds</li>';
                                    $content[] = '</ul>';
                                $content[] = '</div>';

                                $content[] = '<div id="ays-pb-countdown-content" class="emoji">';
                                    $content[] = '<span>🚀</span>';
                                    $content[] = '<span>⌛</span>';
                                    $content[] = '<span>🔥</span>';
                                    $content[] = '<span>💣</span>';
                                $content[] = '</div>';

                            $content[] = '</div>';
                        $content[] = '</div>';
                            
                    $content[] = '</div>';

                    $content[] = '<a href="https://popup-plugin.com" class="button button-primary ays-button" id="ays-button-top-buy-now" target="_blank" style="height: 32px; display: flex; align-items: center; font-weight: 500; " >' . esc_html__( 'Buy Now !', "ays-popup-box" ) . '</a>';
                $content[] = '</div>';
            $content[] = '</div>';

            $content = implode( '', $content );
            echo $content;
        }
    } 

    // Main banner
    public function ays_pb_new_banner_message($ishmar){
        if($ishmar == 0 ){

            $ays_pb_sale_date = get_option('ays_pb_sale_date');

            $val = 60*60*24*5;

            $current_date = current_time( 'mysql' );
            $date_diff = strtotime($current_date) - intval(strtotime($ays_pb_sale_date));
            $days_diff = $date_diff / $val;

            $style_attr = '';
            if( $days_diff < 0 ){
                $style_attr = 'style="display:none;"';
            }

            $content = array();
            $pb_cta_button_link = sprintf('https://popup-plugin.com?utm_source=dashboard&utm_medium=popup-free&utm_campaign=sale-banner-%s', AYS_PB_NAME_VERSION);

            $content[] = '<div id="ays-pb-new-mega-bundle-dicount-month-main" class="ays-pb-admin-notice notice notice-success is-dismissible ays_pb_dicount_info">';
                $content[] = '<div id="ays-pb-dicount-month" class="ays_pb_dicount_month">';
                    // $content[] = '<a href="https://ays-pro.com/mega-bundle" target="_blank" class="ays-pb-sale-banner-link"><img src="' . AYS_pb_ADMIN_URL . '/images/mega_bundle_logo_box.png"></a>';
                    $content[] = '<div class="ays-pb-dicount-wrap-box ays-pb-dicount-wrap-text-box">';
                        $content[] = '<div>';
                            $content[] = '<span class="ays-pb-new-mega-bundle-title">';
                                $content[] = sprintf('Get the Pro Version of <a href="%s" target="_blank" style="color:#ffffff; text-decoration: underline;">%s</a>', esc_url($pb_cta_button_link),esc_html__( "Popup Box", "ays-popup-box" ));
                            $content[] = '</span>';
                            $content[] = '</br>';
                            $content[] = '<div class="ays-pb-new-mega-bundle-mobile-image-display-block display_none">';
                                $content[] = '<img src="' . AYS_PB_ADMIN_URL . '/images/icons/pb-30-guaranteeicon.svg" style="width: 70px;">';
                            $content[] = '</div>';
                            $content[] = '<span class="ays-pb-new-mega-bundle-desc">';
                                $content[] = '<img class="ays-pb-new-mega-bundle-guaranteeicon" src="' . AYS_PB_ADMIN_URL . '/images/icons/pb-guaranteeicon.svg" style="width: 30px;">';
                                $content[] = esc_html__( "30 Day Money Back Guarantee", "ays-popup-box" );
                            $content[] = '</span>';
                        $content[] = '</div>';
                        $content[] = '<div>';
                            $content[] = '<img src="' . AYS_PB_ADMIN_URL . '/images/ays-pb-banner-sale-20.svg" class="ays-pb-new-mega-bundle-mobile-image-display-none" style="width: 70px;">';
                        $content[] = '</div>';
                        $content[] = '<div style="position: absolute;right: 10px;bottom: 1px;" class="ays-pb-dismiss-buttons-container-for-form">';
                            $content[] = '<form action="" method="POST">';
                                $content[] = '<div id="ays-pb-dismiss-buttons-content">';
                                if( current_user_can( 'manage_options' ) ){
                                    $content[] = '<button class="btn btn-link ays-button" name="ays_pb_sale_btn" style="height: 32px; margin-left: 0;padding-left: 0">Dismiss ad</button>';
                                    $content[] = wp_nonce_field( AYS_PB_NAME . '-sale-banner' ,  AYS_PB_NAME . '-sale-banner' );
                                }
                                $content[] = '</div>';
                            $content[] = '</form>';
                            
                        $content[] = '</div>';

                    $content[] = '</div>';

                    $content[] = '<div class="ays-pb-dicount-wrap-box ays-pb-dicount-wrap-countdown-box">';

                        $content[] = '<div id="ays-pb-countdown-main-container">';
                            $content[] = '<div class="ays-pb-countdown-container">';

                                $content[] = '<div ' . $style_attr . ' id="ays-pb-countdown">';

                                    $content[] = '<div>';
                                        $content[] = esc_html__( "Offer ends in:", "ays-popup-box" );
                                    $content[] = '</div>';

                                    $content[] = '<ul>';
                                        $content[] = '<li><span id="ays-pb-countdown-days"></span>days</li>';
                                        $content[] = '<li><span id="ays-pb-countdown-hours"></span>Hours</li>';
                                        $content[] = '<li><span id="ays-pb-countdown-minutes"></span>Minutes</li>';
                                        $content[] = '<li><span id="ays-pb-countdown-seconds"></span>Seconds</li>';
                                    $content[] = '</ul>';
                                $content[] = '</div>';

                                $content[] = '<div id="ays-pb-countdown-content" class="emoji">';
                                    $content[] = '<span>🚀</span>';
                                    $content[] = '<span>⌛</span>';
                                    $content[] = '<span>🔥</span>';
                                    $content[] = '<span>💣</span>';
                                $content[] = '</div>';

                            $content[] = '</div>';
                        $content[] = '</div>';
                            
                    $content[] = '</div>';

                    // $content[] = '<div class="ays-pb-dicount-wrap-box ays-pb-coupon-wrap-button-box">';
                    //     $content[] = '<div class="ays-pb-coupon-container">';
                    //         $content[] = '<div class="ays-pb-coupon-row ays-pb-shortcode-box" onClick="selectAndCopyElementContents(this)" class="ays-pb-copy-element-box" data-toggle="tooltip" title="'. esc_html__('Click for copy.','ays-pb') .'">';
                    //             $content[] = 'summer2025';
                    //         $content[] = '</div>';
                    //         $content[] = '<div class="ays-pb-coupon-text-row">';
                    //             $content[] = __( "20% Extra Discount", 'ays-pb' );
                    //         $content[] = '</div>';
                    //     $content[] = '</div>';
                    // $content[] = '</div>';

                    $content[] = '<div class="ays-pb-dicount-wrap-box ays-pb-dicount-wrap-button-box">';
                        $content[] = sprintf('<a href="%s" class="button button-primary ays-button" id="ays-button-top-buy-now" target="_blank">%s</a>', esc_url("https://popup-plugin.com/pricing?utm_source=dashboard&utm_medium=popup-free&utm_campaign=sale-banner-".AYS_PB_NAME_VERSION), esc_html__( 'Buy Now', "ays-popup-box" ));
                        $content[] = '<span class="ays-pb-dicount-one-time-text">';
                            $content[] = esc_html__( "One-time payment", "ays-popup-box" );
                        $content[] = '</span>';
                    $content[] = '</div>';
                $content[] = '</div>';
            $content[] = '</div>';

            $banner_bg_image =  AYS_PB_ADMIN_URL . '/images/ays-pb-banner-background-20.svg';

            $content[] = '<style>';
                $content[] = 'div#ays-pb-new-mega-bundle-dicount-month-main{border:0;background:#fff;border-radius:20px;box-shadow:unset;position:relative;z-index:1;min-height:80px}div#ays-pb-new-mega-bundle-dicount-month-main.ays_pb_dicount_info button{display:flex;align-items:center}div#ays-pb-new-mega-bundle-dicount-month-main div#ays-pb-dicount-month a.ays-pb-sale-banner-link:focus{outline:0;box-shadow:0}div#ays-pb-new-mega-bundle-dicount-month-main .btn-link{color:#007bff;background-color:transparent;display:inline-block;font-weight:400;text-align:center;white-space:nowrap;vertical-align:middle;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;border:1px solid transparent;padding:.375rem .75rem;font-size:1rem;line-height:1.5;border-radius:.25rem}div#ays-pb-new-mega-bundle-dicount-month-main.ays_pb_dicount_info{background-image:url("'.$banner_bg_image.'");background-position:center right;background-repeat:no-repeat;background-size:cover;background-color:#5551ff}#ays-pb-new-mega-bundle-dicount-month-main .ays_pb_dicount_month{display:flex;align-items:center;justify-content:space-between;color:#fff}#ays-pb-new-mega-bundle-dicount-month-main .ays_pb_dicount_month img{width:80px}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-sale-banner-link{display:flex;justify-content:center;align-items:center;width:200px}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box{font-size:14px;padding:12px;text-align:center}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box{text-align:left;width:23%;display:flex;justify-content:space-around;align-items:flex-start}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-countdown-box{width:30%;display:flex;justify-content:center;align-items:center}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-button-box{width:20%;display:flex;justify-content:center;align-items:center;flex-direction:column}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box .ays-pb-new-mega-bundle-title{color:#fdfdfd;font-size:16.8px;font-style:normal;font-weight:600;line-height:normal}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box .ays-pb-new-mega-bundle-title-icon-row{display:inline-block}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box .ays-pb-new-mega-bundle-desc{display:inline-block;color:#fff;font-size:15px;font-style:normal;font-weight:400;line-height:normal;margin-top:10px}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box strong{font-size:17px;font-weight:700;letter-spacing:.8px}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-color{color:#971821}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-text-decoration{text-decoration:underline}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-buy-now-button-box{display:flex;justify-content:flex-end;align-items:center;width:30%}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box .ays-button,#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box .ays-buy-now-button{align-items:center;font-weight:500}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box .ays-buy-now-button{background:#971821;border-color:#fff;display:flex;justify-content:center;align-items:center;padding:5px 15px;font-size:16px;border-radius:5px}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box .ays-buy-now-button:hover{background:#7d161d;border-color:#971821}#ays-pb-new-mega-bundle-dicount-month-main #ays-pb-dismiss-buttons-content{display:flex;justify-content:center}#ays-pb-new-mega-bundle-dicount-month-main #ays-pb-dismiss-buttons-content .ays-button{margin:0!important;font-size:13px;color:#fff}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-opacity-box{width:19%}#ays-pb-new-mega-bundle-dicount-month-main .ays-buy-now-opacity-button{padding:40px 15px;display:flex;justify-content:center;align-items:center;opacity:0}#ays-pb-countdown-main-container .ays-pb-countdown-container{margin:0 auto;text-align:center}#ays-pb-countdown-main-container #ays-pb-countdown-headline{letter-spacing:.125rem;text-transform:uppercase;font-size:18px;font-weight:400;margin:0;padding:9px 0 4px;line-height:1.3}#ays-pb-countdown-main-container li,#ays-pb-countdown-main-container ul{margin:0}#ays-pb-countdown-main-container li{display:inline-block;font-size:14px;list-style-type:none;padding:14px;text-transform:lowercase}#ays-pb-countdown-main-container li span{display:flex;justify-content:center;align-items:center;font-size:40px;min-height:62px;min-width:62px;border-radius:4.273px;border:.534px solid #f4f4f4;background:#9896ed}#ays-pb-countdown-main-container .emoji{display:none;padding:1rem}#ays-pb-countdown-main-container .emoji span{font-size:30px;padding:0 .5rem}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box li{position:relative}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box li span:after{content:":";color:#fff;position:absolute;top:10px;right:-5px;font-size:40px}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box li span#ays-pb-countdown-seconds:after{content:unset}#ays-pb-new-mega-bundle-dicount-month-main #ays-button-top-buy-now{display:flex;align-items:center;border-radius:6.409px;background:#f66123;padding:12px 32px;color:#fff;font-size:15px;font-style:normal;line-height:normal;margin:0!important}div#ays-pb-new-mega-bundle-dicount-month-main button.notice-dismiss:before{color:#fff;content:"X";font-family:cursive;font-size:22px}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-new-mega-bundle-guaranteeicon{width:30px;margin-right:5px}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-one-time-text{color:#fff;font-size:12px;font-style:normal;font-weight:600;line-height:normal}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-coupon-row{display:flex;flex-direction:row;justify-content:center;align-items:center;padding:6px;width:200px;background-color:#776dd9;border:2px dashed orange;border-radius:18px;font-size:22px;cursor:pointer}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-coupon-text-row{font-weight:700;font-size:14px;text-align:center}@media all and (max-width:1024px){#ays-pb-new-mega-bundle-dicount-month-main{display:none!important}}@media all and (max-width:768px){div#ays-pb-new-mega-bundle-dicount-month-main.ays_pb_dicount_info.notice{display:none!important;background-position:bottom right;background-repeat:no-repeat;background-size:cover;border-radius:32px}div#ays-pb-new-mega-bundle-dicount-month-main{padding-right:0}div#ays-pb-new-mega-bundle-dicount-month-main .ays_pb_dicount_month{display:flex;align-items:center;justify-content:space-between;align-content:center;flex-wrap:wrap;flex-direction:column;padding:10px 0}div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box{width:100%!important;text-align:center}#ays-pb-countdown-main-container #ays-pb-countdown-headline{font-size:15px;font-weight:600}#ays-pb-countdown-main-container ul{font-weight:500}#ays-pb-countdown-main-container li span{font-size:35px;min-height:57px;min-width:55px}div#ays-pb-countdown-main-container li{padding:10px}div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-new-mega-bundle-mobile-image-display-none{display:none!important}div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-new-mega-bundle-mobile-image-display-block{display:block!important;margin-top:5px}div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box{width:100%!important;text-align:center;flex-direction:column;margin-top:20px;justify-content:center;align-items:center}div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box li span:after{top:unset}div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-countdown-box{width:100%;display:flex;justify-content:center;align-items:center}#ays-pb-new-mega-bundle-dicount-month-main .ays-button{margin:0 auto!important}#ays-pb-new-mega-bundle-dicount-month-main #ays-pb-dismiss-buttons-content .ays-button{padding-left:unset!important}div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-buy-now-button-box{justify-content:center}div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box .ays-buy-now-button{font-size:14px;padding:5px 10px}div#ays-pb-new-mega-bundle-dicount-month-main .ays-buy-now-opacity-button{display:none}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dismiss-buttons-container-for-form{position:static!important}.comparison .product img{width:70px}.ays-pb-features-wrap .comparison a.price-buy{padding:8px 5px;font-size:11px}}@media screen and (max-width:1350px) and (min-width:768px){div#ays-pb-new-mega-bundle-dicount-month-main.ays_pb_dicount_info.notice{background-position:bottom right;background-repeat:no-repeat;background-size:cover}div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box strong{font-size:15px}#ays-pb-countdown-main-container li{font-size:11px}div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-opacity-box{display:none}}@media screen and (max-width:1680px){#ays-pb-countdown-main-container li span{font-size:30px;min-height:50px;min-width:50px}}@media screen and (max-width:1680px) and (min-width:1551px){div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box{width:29%}div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-countdown-box{width:30%}}@media screen and (max-width:1410px){#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-coupon-row{width:150px}}@media screen and (max-width:1550px) and (min-width:1400px){div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box{width:31%}div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-countdown-box{width:35%}}@media screen and (max-width:1400px) and (min-width:1250px){div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-countdown-box{width:35%}div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box{width:40%}div#ays-pb-countdown-main-container li span{font-size:30px;min-height:50px;min-width:50px}}@media screen and (max-width:1274px){div#ays-pb-countdown-main-container li span{font-size:25px;min-height:40px;min-width:40px}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box .ays-pb-new-mega-bundle-title{font-size:15px}}@media screen and (max-width:1200px){#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-button-box{margin-bottom:16px}#ays-pb-countdown-main-container ul{padding-left:0}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-coupon-row{width:120px;font-size:18px}#ays-pb-new-mega-bundle-dicount-month-main #ays-button-top-buy-now{padding:12px 20px}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box{font-size:12px}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box .ays-pb-new-mega-bundle-desc{font-size:13px}}@media screen and (max-width:1076px) and (min-width:769px){#ays-pb-countdown-main-container li{padding:10px}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-coupon-row{width:100px;font-size:16px}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-button-box{margin-bottom:16px}#ays-pb-new-mega-bundle-dicount-month-main #ays-button-top-buy-now{padding:12px 15px}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box{font-size:11px;padding:12px 0}}@media screen and (max-width:1250px) and (min-width:769px){div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-countdown-box{width:45%}div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box{width:35%}div#ays-pb-countdown-main-container li span{font-size:30px;min-height:50px;min-width:50px}}';
            $content[] = '</style>';

            $content = implode( '', $content );
            echo $content;
        }
    }

    // New Mega Bundle 2026
    public static function ays_pb_new_mega_bundle_message_2026($ishmar){
        if( $ishmar == 0 ){
            $content = array();

            $date = time() + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS);
            $now_date = date('M d, Y H:i:s', $date);

            $pb_banner_date = strtotime( Ays_Pb_Admin::ays_pb_update_banner_time() );

            $diff = $pb_banner_date - $date;

            $style_attr = '';
            if( $diff < 0 ){
                $style_attr = 'style="display:none;"';
            }

            $pb_cta_button_link = esc_url( 'https://popup-plugin.com/pricing/?utm_source=dashboard&utm_medium=popup-free&utm_campaign=20-sale-banner-' . AYS_PB_NAME_VERSION );

            $content[] = '<div id="ays-pb-new-mega-bundle-dicount-month-main" class="ays-pb-admin-notice notice notice-success is-dismissible ays_pb_dicount_info">';
                $content[] = '<div id="ays-pb-dicount-month" class="ays_pb_dicount_month">';

                    $content[] = '<div class="ays-pb-dicount-wrap-box ays-pb-dicount-wrap-text-box">';
                        $content[] = '<div>';
                            $content[] = '<div class="ays-pb-dicount-logo-box">';
                                $content[] = '<a href="' . $pb_cta_button_link . '" target="_blank" class="ays-pb-sale-banner-link"><img src="' . AYS_PB_ADMIN_URL . '/images/ays-pb-and-lms-popup-icon.svg" style="filter: drop-shadow(1px 2px 3px #141414);"></a>';

                                $content[] = '<div>';
                                    $content[] = '<span class="ays-pb-new-mega-bundle-title">';
                                        $content[] = sprintf(
                                        /* translators: 1: opening link wrapper with <a> tag, 2: closing </a> tag */
                                        __( 'Upgrade to %1$s Popup Box Pro %2$s', 'ays-popup-box' ),
                                        '<span style="display:inline-block; margin-right:5px;"><a href="' . esc_url( $pb_cta_button_link ) . '" target="_blank" rel="noopener noreferrer" style="color:#ffffff !important; text-decoration: underline;">',
                                        '</a></span>'
                                    );
                                    $content[] = '</span>';
                                    $content[] = '</br>';
                                    $content[] = '<span class="ays-pb-new-mega-bundle-desc">';
                                        $content[] = __( "30 Day Money Back Guarantee", 'ays-popup-box' );
                                    $content[] = '</span>';
                                $content[] = '</div>';

                                $content[] = '<div class="ays-pb-new-mega-bundle-title-icon-row" style="display: inline-block;">';
                                    $content[] = '<img src="' . AYS_PB_ADMIN_URL . '/images/ays-pb-banner-sale-20.svg" class="ays-pb-new-mega-bundle-mobile-image-display-none" style="width: 70px;">';
                                $content[] = '</div>';

                            $content[] = '</div>';

                        $content[] = '</div>';

                        $content[] = '<div style="position: absolute;right: 10px;bottom: 1px;" class="ays-pb-dismiss-buttons-container-for-form">';

                            $content[] = '<form action="" method="POST">';
                                $content[] = '<div id="ays-pb-dismiss-buttons-content">';
                                if( current_user_can( 'manage_options' ) ){
                                    $content[] = '<button class="btn btn-link ays-button" name="ays_pb_sale_btn" style="height: 32px; margin-left: 0;padding-left: 0">'. __( "Dismiss ad", 'ays-popup-box' ) .'</button>';
                                    $content[] = wp_nonce_field( AYS_PB_NAME . '-sale-banner' ,  AYS_PB_NAME . '-sale-banner' );
                                }
                                $content[] = '</div>';
                            $content[] = '</form>';
                            
                        $content[] = '</div>';

                    $content[] = '</div>';

                    $content[] = '<div class="ays-pb-dicount-wrap-box ays-pb-dicount-wrap-countdown-box">';

                        $content[] = '<div id="ays-pb-countdown-main-container">';
                            $content[] = '<div class="ays-pb-countdown-container">';

                                $content[] = '<div ' . $style_attr . ' id="ays-pb-countdown">';

                                    $content[] = '<ul>';

                                    $content[] = '<li><span id="ays-pb-countdown-days"></span></li>';
                                        $content[] = '<li><span id="ays-pb-countdown-hours"></span></li>';
                                        $content[] = '<li><span id="ays-pb-countdown-minutes"></span></li>';
                                        $content[] = '<li><span id="ays-pb-countdown-seconds"></span></li>';
                                    $content[] = '</ul>';
                                $content[] = '</div>';

                                $content[] = '<div id="ays-pb-countdown-content" class="emoji">';
                                $content[] = '</div>';

                            $content[] = '</div>';
                        $content[] = '</div>';
                            
                    $content[] = '</div>';

                    $content[] = '<div class="ays-pb-dicount-wrap-box ays-pb-dicount-wrap-button-box">';
                        $content[] = '<div class="ays-pb-dicount-banner-coupon-box" onclick="aysPbBundleCopyToClipboard(\'POPUP20\')" title="' . __( 'Click to copy', "ays-popup-box" ) . '">';
                            $content[] = '<span class="ays-pb-dicount-banner-coupon-text">POPUP20</span>';
                            $content[] = '<svg class="ays-pb-dicount-banner-copy-icon" width="16" height="16" viewBox="0 0 16 16" fill="none">';
                                $content[] = '<path d="M13.5 2.5H6.5C5.67 2.5 5 3.17 5 4V10C5 10.83 5.67 11.5 6.5 11.5H13.5C14.33 11.5 15 10.83 15 10V4C15 3.17 14.33 2.5 13.5 2.5ZM13.5 10H6.5V4H13.5V10ZM2.5 6.5V12.5C2.5 13.33 3.17 14 4 14H10V12.5H4V6.5H2.5Z" fill="white"/>';
                            $content[] = '</svg>';
                        $content[] = '</div>';
                    $content[] = '</div>';

                    $content[] = '<div class="ays-pb-dicount-wrap-box ays-pb-dicount-wrap-button-box">';
                        $content[] = '<a href="'. $pb_cta_button_link .'" class="button button-primary ays-button" id="ays-button-top-buy-now" target="_blank">' . __( 'Buy Now', 'ays-popup-box' ) . '</a>';
                        $content[] = '<span class="ays-pb-dicount-one-time-text">';
                            $content[] = __( "One-time payment", 'ays-popup-box' );
                        $content[] = '</span>';
                    $content[] = '</div>';
                $content[] = '</div>';
            $content[] = '</div>';

            $content[] = '<script>';
            $content[] = "
                function aysPbBundleCopyToClipboard(text) {
                    var textarea = document.createElement('textarea');
                    textarea.value = text;
                    textarea.style.position = 'fixed';
                    textarea.style.opacity = '0';
                    document.body.appendChild(textarea);
                    
                    textarea.select();
                    textarea.setSelectionRange(0, 99999);
                    
                    try {
                        document.execCommand('copy');
                        aysPbBundleShowCopyNotification('" . __( 'Coupon code copied!', "ays-popup-box" ) . "');
                    } catch (err) {
                        console.error('Failed to copy text: ', err);
                    }
                    
                    document.body.removeChild(textarea);
                }

                function aysPbBundleShowCopyNotification(message) {
                    var existingNotification = document.querySelector('.ays-pb-dicount-banner-copy-notification');
                    if (existingNotification) {
                        document.body.removeChild(existingNotification);
                    }
                    
                    var notification = document.createElement('div');
                    notification.className = 'ays-pb-dicount-banner-copy-notification';
                    notification.textContent = message;
                    document.body.appendChild(notification);
                    
                    setTimeout(function() {
                        notification.classList.add('show');
                    }, 10);
                    
                    setTimeout(function() {
                        notification.classList.remove('show');
                        setTimeout(function() {
                            if (notification.parentNode) {
                                document.body.removeChild(notification);
                            }
                        }, 300);
                    }, 2000);
                }";
            $content[] = '</script>';   

            // /* New Mega Bundle Banner | Start */
            $content[] = '<style id="ays-pb-mega-bundle-styles-inline-css">';
            $content[] = '
            div#ays-pb-new-mega-bundle-dicount-month-main{border:0;background:#fff;border-radius:20px;box-shadow:unset;position:relative;z-index:1;min-height:80px}div#ays-pb-new-mega-bundle-dicount-month-main.ays_pb_dicount_info button{display:flex;align-items:center}div#ays-pb-new-mega-bundle-dicount-month-main div#ays-pb-dicount-month a.ays-pb-sale-banner-link:focus{outline:0;box-shadow:0}div#ays-pb-new-mega-bundle-dicount-month-main .btn-link{color:#007bff;background-color:transparent;display:inline-block;font-weight:400;text-align:center;white-space:nowrap;vertical-align:middle;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;border:1px solid transparent;padding:.375rem .75rem;font-size:1rem;line-height:1.5;border-radius:.25rem}div#ays-pb-new-mega-bundle-dicount-month-main.ays_pb_dicount_info{background-image:url("'. AYS_PB_ADMIN_URL .'/images/ays-pb-banner-background-20.svg");background-position:center right;background-repeat:no-repeat;background-size:cover;background-color:#5551ff;padding:1px 38px 1px 12px}#ays-pb-new-mega-bundle-dicount-month-main .ays_pb_dicount_month{display:flex;align-items:center;justify-content:space-between;color:#fff}#ays-pb-new-mega-bundle-dicount-month-main .ays_pb_dicount_month img{width:60px}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-sale-banner-link{display:flex;justify-content:center;align-items:center;width:60px}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box{font-size:14px;padding:12px;text-align:center}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box{text-align:left;width:auto;display:flex;justify-content:space-around;align-items:flex-start}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-countdown-box{width:30%;display:flex;justify-content:center;align-items:center}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-button-box{width:20%;display:flex;justify-content:center;align-items:center;flex-direction:column}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box .ays-pb-dicount-logo-box{display:flex;justify-content:flex-start;align-items:center;gap:20px}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box .ays-pb-new-mega-bundle-title{color:#fdfdfd;font-size:19px;font-style:normal;font-weight:600;line-height:normal}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box .ays-pb-new-mega-bundle-title-icon-row{display:inline-block}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box .ays-pb-new-mega-bundle-desc{display:inline-block;color:#fff;font-size:15px;font-style:normal;font-weight:400;line-height:normal;margin-top:10px}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box strong{font-size:17px;font-weight:700;letter-spacing:.8px}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-color{color:#971821}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-text-decoration{text-decoration:underline}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-buy-now-button-box{display:flex;justify-content:flex-end;align-items:center;width:30%}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box .ays-button,#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box .ays-buy-now-button{align-items:center;font-weight:500}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box .ays-buy-now-button{background:#971821;border-color:#fff;display:flex;justify-content:center;align-items:center;padding:5px 15px;font-size:16px;border-radius:5px}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box .ays-buy-now-button:hover{background:#7d161d;border-color:#971821}#ays-pb-new-mega-bundle-dicount-month-main #ays-pb-dismiss-buttons-content{display:flex;justify-content:center}#ays-pb-new-mega-bundle-dicount-month-main #ays-pb-dismiss-buttons-content .ays-button{margin:0!important;font-size:13px;color:#fff}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-opacity-box{width:19%}#ays-pb-new-mega-bundle-dicount-month-main .ays-buy-now-opacity-button{padding:40px 15px;display:flex;justify-content:center;align-items:center;opacity:0}#ays-pb-countdown-main-container .ays-pb-countdown-container{margin:0 auto;text-align:center}#ays-pb-countdown-main-container #ays-pb-countdown-headline{letter-spacing:.125rem;text-transform:uppercase;font-size:18px;font-weight:400;margin:0;padding:9px 0 4px;line-height:1.3}#ays-pb-countdown-main-container li,#ays-pb-countdown-main-container ul{margin:0}#ays-pb-countdown-main-container li{display:inline-block;font-size:14px;list-style-type:none;padding:14px;text-transform:lowercase}#ays-pb-countdown-main-container li span{display:flex;justify-content:center;align-items:center;font-size:22px;min-height:40px;min-width:40px;border-radius:4.273px;border:.534px solid #f4f4f4;background:#9896ed;color:#fff}#ays-pb-countdown-main-container .emoji{display:none;padding:1rem}#ays-pb-countdown-main-container .emoji span{font-size:30px;padding:0 .5rem}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box li{position:relative}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box li span:after{content:":";color:#fff;position:absolute;top:0;right:-5px;font-size:40px}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box li span#ays-pb-countdown-seconds:after{content:unset}#ays-pb-new-mega-bundle-dicount-month-main #ays-button-top-buy-now{display:flex;align-items:center;border-radius:6.409px;background:#f66123;padding:12px 32px;color:#fff;font-size:15px;font-style:normal;line-height:normal;margin:0!important}div#ays-pb-new-mega-bundle-dicount-month-main button.notice-dismiss:before{color:#fff;content:"x";font-family:sans-serif;font-size:22px}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-new-mega-bundle-guaranteeicon{width:30px;margin-right:5px}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-one-time-text{color:#fff;font-size:12px;font-style:normal;font-weight:600;line-height:normal}@media all and (max-width:768px){div#ays-pb-new-mega-bundle-dicount-month-main.ays_pb_dicount_info.notice{display:none!important;background-position:bottom right;background-repeat:no-repeat;background-size:cover;border-radius:32px}div#ays-pb-new-mega-bundle-dicount-month-main{padding-right:0}div#ays-pb-new-mega-bundle-dicount-month-main .ays_pb_dicount_month{display:flex;align-items:center;justify-content:space-between;align-content:center;flex-wrap:wrap;flex-direction:column;padding:10px 0}div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box{width:100%!important;text-align:center}#ays-pb-countdown-main-container #ays-pb-countdown-headline{font-size:15px;font-weight:600}#ays-pb-countdown-main-container ul{font-weight:500}div#ays-pb-countdown-main-container li{padding:10px}div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-new-mega-bundle-mobile-image-display-none{display:none!important}div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-new-mega-bundle-mobile-image-display-block{display:block!important;margin-top:5px}div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box{width:100%!important;text-align:center;flex-direction:column;margin-top:20px;justify-content:center;align-items:center}div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box li span:after{top:unset}div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-countdown-box{width:100%;display:flex;justify-content:center;align-items:center}#ays-pb-new-mega-bundle-dicount-month-main .ays-button{margin:0 auto!important}#ays-pb-new-mega-bundle-dicount-month-main #ays-pb-dismiss-buttons-content .ays-button{padding-left:unset!important}div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-buy-now-button-box{justify-content:center}div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box .ays-buy-now-button{font-size:14px;padding:5px 10px}div#ays-pb-new-mega-bundle-dicount-month-main .ays-buy-now-opacity-button{display:none}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dismiss-buttons-container-for-form{position:static!important}.comparison .product img{width:70px}.ays-pb-features-wrap .comparison a.price-buy{padding:8px 5px;font-size:11px}}@media screen and (max-width:1350px) and (min-width:768px){div#ays-pb-new-mega-bundle-dicount-month-main.ays_pb_dicount_info.notice{background-position:bottom right;background-repeat:no-repeat;background-size:cover}div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box strong{font-size:15px}#ays-pb-countdown-main-container li{font-size:11px}div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-opacity-box{display:none}}@media screen and (max-width:1680px) and (min-width:1551px){div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box{width:29%}div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-countdown-box{width:30%}}@media screen and (max-width:1410px){#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-coupon-row{width:150px}}@media screen and (max-width:1550px) and (min-width:1400px){div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-countdown-box{width:35%}}@media screen and (max-width:1400px) and (min-width:1250px){div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-countdown-box{width:35%}div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box{width:40%}}@media screen and (max-width:1274px){#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box .ays-pb-new-mega-bundle-title{font-size:15px}}@media screen and (max-width:1200px){#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-button-box{margin-bottom:16px}#ays-pb-countdown-main-container ul{padding-left:0}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-coupon-row{width:120px;font-size:18px}#ays-pb-new-mega-bundle-dicount-month-main #ays-button-top-buy-now{padding:12px 20px}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box{font-size:12px}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box .ays-pb-new-mega-bundle-desc{font-size:13px}}@media screen and (max-width:1076px) and (min-width:769px){#ays-pb-countdown-main-container li{padding:10px}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-coupon-row{width:100px;font-size:16px}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-button-box{margin-bottom:16px}#ays-pb-new-mega-bundle-dicount-month-main #ays-button-top-buy-now{padding:12px 15px}#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box{font-size:11px;padding:12px 0}}@media screen and (max-width:1250px) and (min-width:769px){div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-countdown-box{width:45%}div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box{width:35%}}';
            $content[] = '
                /* Right section */
                .ays-pb-dicount-banner-right {
                    display: flex;
                    align-items: center;
                    gap: 20px;
                }

                .ays-pb-dicount-banner-coupon-box {
                    border: 2px dashed rgba(255, 255, 255, 0.4);
                    padding: 8px 16px;
                    border-radius: 6px;
                    background: #9896ed;
                    cursor: pointer;
                    transition: all 0.3s;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    backdrop-filter: blur(10px);
                }

                .ays-pb-dicount-banner-coupon-box:hover {
                    background: rgba(255, 255, 255, 0.2);
                    border-color: rgba(255, 255, 255, 0.6);
                    transform: translateY(-1px);
                }

                .ays-pb-dicount-banner-coupon-text {
                    font-size: 16px;
                    font-weight: 700;
                    letter-spacing: 1px;
                    color: #fff;
                    font-family: monospace;
                }

                .ays-pb-dicount-banner-copy-icon {
                    opacity: 0.8;
                    transition: opacity 0.3s;
                }

                .ays-pb-dicount-banner-coupon-box:hover .ays-pb-dicount-banner-copy-icon {
                    opacity: 1;
                }

                .ays-pb-dicount-banner-btn-arrow {
                    display: inline-block;
                    transition: transform 0.3s;
                }

                .ays-pb-dicount-banner-buy-now-btn:hover .ays-pb-dicount-banner-btn-arrow {
                    transform: translateX(4px);
                }

                /* Notification */
                .ays-pb-dicount-banner-copy-notification {
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: rgba(0, 0, 0, 0.8);
                    color: #fff;
                    padding: 12px 24px;
                    border-radius: 8px;
                    font-size: 14px;
                    z-index: 10000;
                    opacity: 0;
                    transition: opacity 0.3s;
                }

                .ays-pb-dicount-banner-copy-notification.show {
                    opacity: 1;
                }

                @media screen and (max-width: 1280px) {
                    div#ays-pb-new-mega-bundle-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-countdown-box {
                        display: none;
                    }
                }
            ';
            $content[] = '</style>';
            // /* New Mega Bundle Banner | End */

            $content = implode( '', $content );
            echo ($content);        
        }
    }

    // Christmas Banner 2025
    public static function ays_pb_christmas_banner_message_2025($ishmar){
        if($ishmar == 0 ){
            $content = array();

           $svg_icon = '<svg width="21" height="22" viewBox="0 0 21 22" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M10.0445 0C10.4587 0 10.7945 0.33579 10.7945 0.75V2.93934L12.5141 1.21967C12.807 0.92678 13.2819 0.92678 13.5748 1.21967C13.8677 1.51256 13.8677 1.98744 13.5748 2.28033L10.7945 5.06066V9.451L14.5965 7.25588L15.6142 3.45788C15.7214 3.05778 16.1326 2.82034 16.5327 2.92755C16.9328 3.03475 17.1703 3.44601 17.0631 3.84611L16.4336 6.19522L18.3296 5.10055C18.6884 4.89344 19.147 5.01635 19.3542 5.37507C19.5613 5.73379 19.4384 6.19248 19.0796 6.39959L17.1836 7.49426L19.5327 8.1237C19.9328 8.23091 20.1703 8.64216 20.0631 9.0423C19.9558 9.4424 19.5446 9.6798 19.1445 9.5726L15.3465 8.55492L11.5445 10.75L15.3467 12.9452L19.1447 11.9275C19.5448 11.8203 19.956 12.0578 20.0633 12.4579C20.1705 12.858 19.933 13.2692 19.5329 13.3764L17.1838 14.0059L19.0798 15.1005C19.4386 15.3077 19.5615 15.7663 19.3544 16.1251C19.1472 16.4838 18.6886 16.6067 18.3298 16.3996L16.4338 15.3049L17.0633 17.654C17.1705 18.0541 16.933 18.4654 16.5329 18.5726C16.1328 18.6798 15.7216 18.4424 15.6144 18.0423L14.5967 14.2443L10.7945 12.049V16.4393L13.5748 19.2197C13.8677 19.5126 13.8677 19.9874 13.5748 20.2803C13.2819 20.5732 12.807 20.5732 12.5141 20.2803L10.7945 18.5607V20.75C10.7945 21.1642 10.4587 21.5 10.0445 21.5C9.63033 21.5 9.29453 21.1642 9.29453 20.75V18.5607L7.57484 20.2803C7.28195 20.5732 6.80707 20.5732 6.51418 20.2803C6.22129 19.9874 6.22129 19.5126 6.51418 19.2197L9.29453 16.4393V12.049L5.4923 14.2443L4.47463 18.0423C4.36742 18.4424 3.95617 18.6798 3.55607 18.5726C3.15597 18.4654 2.91853 18.0541 3.02574 17.654L3.65518 15.3049L1.75916 16.3996C1.40044 16.6067 0.941743 16.4838 0.734643 16.1251C0.527533 15.7663 0.650443 15.3077 1.00916 15.1005L2.90518 14.0059L0.556073 13.3764C0.155973 13.2692 -0.081467 12.858 0.0257431 12.4579C0.132943 12.0578 0.544203 11.8203 0.944303 11.9275L4.7423 12.9452L8.54453 10.75L4.74249 8.55492L0.944493 9.5726C0.544393 9.6798 0.133143 9.4424 0.0259331 9.0423C-0.0812669 8.64216 0.156163 8.23091 0.556263 8.1237L2.90538 7.49426L1.00935 6.39959C0.650633 6.19248 0.527733 5.73379 0.734833 5.37507C0.941943 5.01635 1.40063 4.89344 1.75935 5.10055L3.65538 6.19522L3.02593 3.84611C2.91873 3.44601 3.15616 3.03475 3.55626 2.92755C3.95636 2.82034 4.36762 3.05778 4.47482 3.45788L5.49249 7.25588L9.29453 9.451V5.06066L6.51418 2.28033C6.22129 1.98744 6.22129 1.51256 6.51418 1.21967C6.80707 0.92678 7.28195 0.92678 7.57484 1.21967L9.29453 2.93934V0.75C9.29453 0.33579 9.63033 0 10.0445 0Z" fill="white" fill-opacity="0.2"/>
            </svg>
            ';

            $ays_pb_cta_button_link = esc_url('https://popup-plugin.com/pricing?utm_source=dashboard&utm_medium=popup-free&utm_campaign=christmas-sale-banner-' . AYS_PB_NAME_VERSION);

            $content[] = '<div id="ays-pb-christmas-banner-main" class="notice notice-success is-dismissible ays-pb-christmas-banner-info ays_pb_dicount_info">';
                $content[] = '<div id="ays-pb-christmas-banner-month" class="ays-pb-christmas-banner-month">';
                    
                    // Background effects
                    $content[] = '<div class="ays-pb-christmas-banner-bg-effects">';
                        $content[] = '<div class="ays-pb-christmas-banner-bg-gradient-1"></div>';
                        $content[] = '<div class="ays-pb-christmas-banner-bg-gradient-2"></div>';
                        
                        // Snowflakes
                        $content[] = '<div class="ays-pb-christmas-banner-snowflake" style="left: 5%; animation-delay: 0s; animation-duration: 8s;">'. $svg_icon .'</div>';
                        $content[] = '<div class="ays-pb-christmas-banner-snowflake" style="left: 15%; animation-delay: 2s; animation-duration: 10s;">'. $svg_icon .'</div>';
                        $content[] = '<div class="ays-pb-christmas-banner-snowflake" style="left: 25%; animation-delay: 4s; animation-duration: 9s;">'. $svg_icon .'</div>';
                        $content[] = '<div class="ays-pb-christmas-banner-snowflake" style="left: 75%; animation-delay: 1s; animation-duration: 11s;">'. $svg_icon .'</div>';
                        $content[] = '<div class="ays-pb-christmas-banner-snowflake" style="left: 85%; animation-delay: 3s; animation-duration: 8s;">'. $svg_icon .'</div>';
                        $content[] = '<div class="ays-pb-christmas-banner-snowflake" style="left: 92%; animation-delay: 5s; animation-duration: 10s;">'. $svg_icon .'</div>';
                        
                        // Sparkles
                        $content[] = '<svg class="ays-pb-christmas-banner-sparkle" style="top: 20%; left: 8%; animation-delay: 0s; width: 14px; height: 14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">';
                            $content[] = '<path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/>';
                        $content[] = '</svg>';
                        $content[] = '<svg class="ays-pb-christmas-banner-sparkle" style="top: 60%; left: 3%; animation-delay: 0.5s; width: 10px; height: 10px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">';
                            $content[] = '<path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/>';
                        $content[] = '</svg>';
                        $content[] = '<svg class="ays-pb-christmas-banner-sparkle" style="top: 30%; right: 12%; animation-delay: 1s; width: 12px; height: 12px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">';
                            $content[] = '<path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/>';
                        $content[] = '</svg>';
                    $content[] = '</div>';

                    // Main content
                    $content[] = '<div class="ays-pb-christmas-banner-content">';
                        $content[] = '<div class="ays-pb-christmas-banner-left">';
                            // Gift icon with hat
                            $content[] = '<div class="ays-pb-christmas-banner-gift-wrapper">';
                                $content[] = '<svg class="ays-pb-christmas-banner-gift-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">';
                                    $content[] = '<rect x="3" y="8" width="18" height="4" rx="1"></rect>';
                                    $content[] = '<path d="M12 8v13"></path>';
                                    $content[] = '<path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"></path>';
                                    $content[] = '<path d="M7.5 8a2.5 2.5 0 0 1 0-5A4.8 8 0 0 1 12 8a4.8 8 0 0 1 4.5-5 2.5 2.5 0 0 1 0 5"></path>';
                                $content[] = '</svg>';
                                $content[] = '<div class="ays-pb-christmas-banner-hat">';
                                    $content[] = '<svg viewBox="0 0 24 24" fill="none" class="ays-pb-christmas-banner-hat-svg">';
                                        $content[] = '<path d="M12 2L4 14h16L12 2z" fill="hsl(0 80% 45%)"></path>';
                                        $content[] = '<path d="M4 14c0 2 3.5 3 8 3s8-1 8-3" fill="hsl(0 0% 100%)"></path>';
                                        $content[] = '<circle cx="12" cy="3" r="2" fill="hsl(0 0% 100%)"></circle>';
                                    $content[] = '</svg>';
                                $content[] = '</div>';
                            $content[] = '</div>';

                            $content[] = '<div class="ays-pb-christmas-banner-special-label">';
                                $content[] = '<div class="ays-pb-christmas-banner-special-label-name">';
                                    $content[] = '<a href="'. $ays_pb_cta_button_link .'" class="ays-pb-christmas-banner-special-label-name-link" target="_blank">';
                                        $content[] = __( 'Popup Box', "ays-popup-box" );
                                    $content[] = '</a>';
                                $content[] = '</div>';

                                $content[] = '<div>✦ ' . __( 'CHRISTMAS SPECIAL', "ays-popup-box" ) . ' ✦</div>';
                            $content[] = '</div>';
                        $content[] = '</div>';

                        $content[] = '<div class="ays-pb-christmas-banner-center">';
                            $content[] = '<div class="ays-pb-christmas-banner-discount-text">25% OFF</div>';
                            $content[] = '<div class="ays-pb-christmas-banner-limited-offer">' . __( 'Limited time offer', "ays-popup-box" ) . '</div>';
                        $content[] = '</div>';

                        $content[] = '<div class="ays-pb-christmas-banner-right">';
                            $content[] = '<div class="ays-pb-christmas-banner-coupon-box" onclick="aysPbChristmasCopyToClipboard(\'XMAS25\')" title="' . __( 'Click to copy', "ays-popup-box" ) . '">';
                                $content[] = '<span class="ays-pb-christmas-banner-coupon-text">XMAS25</span>';
                                $content[] = '<svg class="ays-pb-christmas-banner-copy-icon" width="16" height="16" viewBox="0 0 16 16" fill="none">';
                                    $content[] = '<path d="M13.5 2.5H6.5C5.67 2.5 5 3.17 5 4V10C5 10.83 5.67 11.5 6.5 11.5H13.5C14.33 11.5 15 10.83 15 10V4C15 3.17 14.33 2.5 13.5 2.5ZM13.5 10H6.5V4H13.5V10ZM2.5 6.5V12.5C2.5 13.33 3.17 14 4 14H10V12.5H4V6.5H2.5Z" fill="white"/>';
                                $content[] = '</svg>';
                            $content[] = '</div>';

                            $content[] = '<a href="'. $ays_pb_cta_button_link .'" class="ays-pb-christmas-banner-buy-now-btn" target="_blank">';
                                $content[] = __( 'Buy Now', "ays-popup-box" );
                            $content[] = '</a>';
                        $content[] = '</div>';
                    $content[] = '</div>';

                $content[] = '</div>';

                if( current_user_can( 'manage_options' ) ){
                $content[] = '<div id="ays-pb-dismiss-buttons-content">';
                    $content[] = '<form action="" method="POST" style="position: absolute; bottom: 0; right: 0; color: #fff;">';
                            $content[] = '<button class="btn btn-link ays-button" name="ays_pb_sale_btn" style="color: darkgrey; font-size: 11px; padding: 0 .75rem;">'. __( "Dismiss ad", 'ays-popup-box' ) .'</button>';
                            $content[] = wp_nonce_field( AYS_PB_NAME . '-sale-banner' ,  AYS_PB_NAME . '-sale-banner' );
                    $content[] = '</form>';
                $content[] = '</div>';
                }

            $content[] = '</div>';

            $content[] = '<script>';
            $content[] = "
                function aysPbChristmasCopyToClipboard(text) {
                    var textarea = document.createElement('textarea');
                    textarea.value = text;
                    textarea.style.position = 'fixed';
                    textarea.style.opacity = '0';
                    document.body.appendChild(textarea);
                    
                    textarea.select();
                    textarea.setSelectionRange(0, 99999);
                    
                    try {
                        document.execCommand('copy');
                        aysPbChristmasShowCopyNotification('" . __( 'Coupon code copied!', "ays-popup-box" ) . "');
                    } catch (err) {
                        console.error('Failed to copy text: ', err);
                    }
                    
                    document.body.removeChild(textarea);
                }

                function aysPbChristmasShowCopyNotification(message) {
                    var existingNotification = document.querySelector('.ays-pb-christmas-banner-copy-notification');
                    if (existingNotification) {
                        document.body.removeChild(existingNotification);
                    }
                    
                    var notification = document.createElement('div');
                    notification.className = 'ays-pb-christmas-banner-copy-notification';
                    notification.textContent = message;
                    document.body.appendChild(notification);
                    
                    setTimeout(function() {
                        notification.classList.add('show');
                    }, 10);
                    
                    setTimeout(function() {
                        notification.classList.remove('show');
                        setTimeout(function() {
                            if (notification.parentNode) {
                                document.body.removeChild(notification);
                            }
                        }, 300);
                    }, 2000);
                }";
            $content[] = '</script>';                

            $content[] = '<style>';
            $content[] = '
                /* Christmas banner start */

                div#ays-pb-christmas-banner-main .btn-link {
                    background-color: transparent;
                    display: inline-block;
                    font-weight: 400;
                    text-align: center;
                    white-space: nowrap;
                    vertical-align: middle;
                    -webkit-user-select: none;
                    -moz-user-select: none;
                    -ms-user-select: none;
                    user-select: none;
                    border: 1px solid transparent;
                    padding: .375rem .75rem;
                    font-size: 12px;
                    line-height: 1.5;
                    border-radius: .25rem;
                    color: rgba(255, 255, 255, .6);
                }
                
                div#ays-pb-christmas-banner-main.ays-pb-christmas-banner-info {
                    background: linear-gradient(to right, hsl(0, 70%, 28%), hsl(0, 65%, 38%), hsl(0, 70%, 28%));
                    padding: unset;
                    border-left: 0;
                    position: relative;
                }
                
                #ays-pb-christmas-banner-main .ays-pb-christmas-banner-month {
                    position: relative;
                    padding: 15px 40px;
                    overflow: hidden;
                }
                
                /* Background effects */
                .ays-pb-christmas-banner-bg-effects {
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    pointer-events: none;
                    z-index: 1;
                }
                
                .ays-pb-christmas-banner-bg-gradient-1 {
                    position: absolute;
                    top: 0;
                    left: 30%;
                    width: 40%;
                    height: 100%;
                    background: radial-gradient(circle, rgba(202, 43, 43, 0.5) 0%, transparent 60%);
                    opacity: 0.4;
                }
                
                .ays-pb-christmas-banner-bg-gradient-2 {
                    position: absolute;
                    top: 0;
                    right: 15%;
                    width: 35%;
                    height: 100%;
                    background: radial-gradient(circle, rgba(246, 201, 85, 0.15) 0%, transparent 50%);
                    opacity: 0.3;
                }
                
                .ays-pb-christmas-banner-snowflake {
                    position: absolute;
                    color: rgba(255, 255, 255, 0.2);
                    font-size: 20px;
                    animation: ays-pb-christmas-snowfall linear infinite;
                    top: -10px;
                }
                
                @keyframes ays-pb-christmas-snowfall {
                    0% {
                        transform: translateY(-10px) rotate(0deg);
                        opacity: 0;
                    }
                    10% {
                        opacity: 0.8;
                    }
                    90% {
                        opacity: 0.8;
                    }
                    100% {
                        transform: translateY(100%) rotate(360deg);
                        opacity: 0;
                    }
                }
                
                .ays-pb-christmas-banner-sparkle {
                    position: absolute;
                    color: hsl(43, 90%, 65%);
                    animation: ays-pb-christmas-twinkle 2s ease-in-out infinite;
                }
                
                @keyframes ays-pb-christmas-twinkle {
                    0%, 100% {
                        opacity: 0.3;
                        transform: scale(0.8);
                    }
                    50% {
                        opacity: 1;
                        transform: scale(1.2);
                    }
                }
                
                /* Main content */
                .ays-pb-christmas-banner-content {
                    position: relative;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    z-index: 2;
                }
                
                /* Left section */
                .ays-pb-christmas-banner-left {
                    display: flex;
                    align-items: center;
                    gap: 50px;
                }
                
                .ays-pb-christmas-banner-gift-wrapper {
                    position: relative;
                    animation: ays-pb-christmas-float 3s ease-in-out infinite;
                }
                
                .ays-pb-christmas-banner-gift-icon {
                    width: 48px;
                    height: 48px;
                    color: rgba(255, 247, 237, 0.9);
                }
                
                @keyframes ays-pb-christmas-float {
                    0%, 100% {
                        transform: translateY(0);
                    }
                    50% {
                        transform: translateY(-5px);
                    }
                }
                
                .ays-pb-christmas-banner-hat {
                    position: absolute;
                    top: -12px;
                    right: -4px;
                    width: 24px;
                    height: 24px;
                }
                
                .ays-pb-christmas-banner-hat-svg {
                    width: 100%;
                    height: 100%;
                }
                
                .ays-pb-christmas-banner-special-label {
                    color: hsl(43, 90%, 65%);
                    font-size: 14px;
                    font-weight: 500;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    /* font-family: "Outfit", sans-serif; */
                }

                .ays-pb-christmas-banner-special-label-name {
                    color: #fffaf0;
                    text-align: center;
                }

                div#ays-pb-christmas-banner-main .ays-pb-christmas-banner-special-label-name-link {
                    color: #fffaf0;
                    box-shadow: unset;
                }
                
                /* Center section */
                .ays-pb-christmas-banner-center {
                    display: flex;
                    flex-direction: row;
                    text-align: center;
                    justify-content: center;
                    align-items: center;
                    gap: 30px;
                }
                
                .ays-pb-christmas-banner-discount-text {
                    font-family: "Outfit", sans-serif;
                    font-weight: 800;
                    font-size: 30px;
                    color: hsl(40, 100%, 97%);
                    letter-spacing: -1px;
                    line-height: 1;
                }
                
                .ays-pb-christmas-banner-limited-offer {
                    color: rgba(255, 247, 237, 0.7);
                    font-size: 13px;
                    font-weight: 500;
                }
                
                /* Right section */
                .ays-pb-christmas-banner-right {
                    display: flex;
                    align-items: center;
                    gap: 20px;
                }
                
                .ays-pb-christmas-banner-coupon-box {
                    border: 2px dashed rgba(255, 255, 255, 0.4);
                    padding: 8px 16px;
                    border-radius: 6px;
                    background: rgba(255, 255, 255, 0.1);
                    cursor: pointer;
                    transition: all 0.3s;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    backdrop-filter: blur(10px);
                }
                
                .ays-pb-christmas-banner-coupon-box:hover {
                    background: rgba(255, 255, 255, 0.2);
                    border-color: rgba(255, 255, 255, 0.6);
                    transform: translateY(-1px);
                }
                
                .ays-pb-christmas-banner-coupon-text {
                    font-size: 16px;
                    font-weight: 700;
                    letter-spacing: 1px;
                    color: #fff;
                    font-family: monospace;
                }
                
                .ays-pb-christmas-banner-copy-icon {
                    opacity: 0.8;
                    transition: opacity 0.3s;
                }
                
                .ays-pb-christmas-banner-coupon-box:hover .ays-pb-christmas-banner-copy-icon {
                    opacity: 1;
                }
                
                #ays-pb-christmas-banner-main .ays-pb-christmas-banner-buy-now-btn {
                    background-color: #fbe19f;
                    color: hsl(0, 72%, 35%);
                    padding: 10px 30px;
                    border-radius: 9999px;
                    font-size: 16px;
                    font-weight: 600;
                    font-family: "Outfit", sans-serif;
                    border: none;
                    cursor: pointer;
                    transition: all 0.3s;
                    text-decoration: none;
                    display: flex;
                    align-items: center;
                    gap: 6px;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15), 0 0 30px rgba(246, 201, 85, 0.2);
                }
                
                #ays-pb-christmas-banner-main .ays-pb-christmas-banner-buy-now-btn:hover {
                    background-color: #f2d58c;
                    transform: scale(1.05);
                    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2), 0 0 40px rgba(246, 201, 85, 0.3);
                }
                
                .ays-pb-christmas-banner-btn-arrow {
                    display: inline-block;
                    transition: transform 0.3s;
                }
                
                .ays-pb-christmas-banner-buy-now-btn:hover .ays-pb-christmas-banner-btn-arrow {
                    transform: translateX(4px);
                }
                
                /* Notification */
                .ays-pb-christmas-banner-copy-notification {
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: rgba(0, 0, 0, 0.8);
                    color: #fff;
                    padding: 12px 24px;
                    border-radius: 8px;
                    font-size: 14px;
                    z-index: 10000;
                    opacity: 0;
                    transition: opacity 0.3s;
                }
                
                .ays-pb-christmas-banner-copy-notification.show {
                    opacity: 1;
                }
                
                /* Dismiss button */
                #ays-pb-christmas-banner-main #ays-pb-christmas-banner-dismiss-content {
                    display: flex;
                    justify-content: center;
                }
                
                #ays-pb-christmas-banner-main #ays-pb-christmas-banner-dismiss-content .ays-button {
                    margin: 0 !important;
                    font-size: 13px;
                    color: rgba(150, 147, 147, 0.69);
                }
                
                /* Responsive */
                @media (max-width: 1024px) {
                    .ays-pb-christmas-banner-discount-text {
                        font-size: 40px;
                    }
                    .ays-pb-christmas-banner-content {
                        flex-wrap: wrap;
                    }
                }
                
                @media (max-width: 768px) {
                    #ays-pb-christmas-banner-main {
                        display: none !important;
                    }
                }
                /* Christmas banner end */
            ';
            $content[] = '</style>';

            $content = implode( '', $content );

            echo $content;
        }
    }


    // Black Friday
    public static function ays_pb_black_friday_message($ishmar){
        if($ishmar == 0 ){
            $content = array();

            $ays_pb_cta_button_link = esc_url('https://popup-plugin.com/pricing?utm_source=dashboard&utm_medium=popup-free&utm_campaign=black-friday-sale-banner-' . AYS_PB_NAME_VERSION);

            $content[] = '<div id="ays-pb-dicount-black-friday-month-main" class="ays-pb notice notice-success is-dismissible ays_pb_dicount_info">';
                $content[] = '<div id="ays-pb-dicount-black-friday-month" class="ays_pb_dicount_month">';
                    $content[] = '<div class="ays-pb-dicount-black-friday-box">';
                        $content[] = '<div class="ays-pb-dicount-black-friday-wrap-box ays-pb-dicount-black-friday-wrap-box-80" style="width: 70%;">';
                            $content[] = '<div class="">';
                                $content[] = '<div class="ays-pb-dicount-black-friday-title-row" >' . __( 'Coupon Code', "ays-popup-box" ) .' ' . '</div>';
                                $content[] = '<div class="ays-pb-dicount-black-friday-title-row">';

                                $content[] = '
                                    <span class="ays-pb-dicount-black-friday-banner-2025-coupon-wrapper">
                                        <span class="ays-pb-dicount-black-friday-banner-2025-coupon-box" onclick="aysPbHalloweenCopyToClipboard(\'FREE2PROBF\')" title="Click to copy">
                                            <span class="ays-pb-dicount-black-friday-banner-2025-coupon-text">FREE2PROBF</span>
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="ays-pb-dicount-black-friday-banner-2025-copy-icon">
                                                <path d="M13.5 2.5H6.5C5.67 2.5 5 3.17 5 4V10C5 10.83 5.67 11.5 6.5 11.5H13.5C14.33 11.5 15 10.83 15 10V4C15 3.17 14.33 2.5 13.5 2.5ZM13.5 10H6.5V4H13.5V10ZM2.5 6.5V12.5C2.5 13.33 3.17 14 4 14H10V12.5H4V6.5H2.5Z" fill="white"/>
                                            </svg>
                                        </span>
                                    </span>';
                                $content[] = '</div> ';
                            $content[] = '</div>';

                        $content[] = '</div>';

                        $content[] = '<div class="ays-pb-dicount-black-friday-wrap-box ays-pb-dicount-black-friday-wrap-text-box">';
                            $content[] = '<div class="ays-pb-dicount-black-friday-text-row">' . '30% off' . '</div>';
                        $content[] = '</div>';

                        $content[] = '<div class="ays-pb-dicount-black-friday-wrap-box" style="width: 25%;">';
                            $content[] = '<div id="ays-pb-countdown-main-container">';
                                $content[] = '<div class="ays-pb-countdown-container">';
                                    $content[] = '<div id="ays-pb-countdown" style="display: block;">';
                                        $content[] = '<ul>';
                                            $content[] = '<li><span id="ays-pb-countdown-days"></span>' . __( 'Days', "ays-popup-box" ) . '</li>';
                                            $content[] = '<li><span id="ays-pb-countdown-hours"></span>' . __( 'Hours', "ays-popup-box" ) . '</li>';
                                            $content[] = '<li><span id="ays-pb-countdown-minutes"></span>' . __( 'Minutes', "ays-popup-box" ) . '</li>';
                                            $content[] = '<li><span id="ays-pb-countdown-seconds"></span>' . __( 'Seconds', "ays-popup-box" ) . '</li>';
                                        $content[] = '</ul>';
                                    $content[] = '</div>';
                                    $content[] = '<div id="ays-pb-countdown-content" class="emoji" style="display: none;">';
                                        $content[] = '<span></span>';
                                        $content[] = '<span></span>';
                                        $content[] = '<span></span>';
                                        $content[] = '<span></span>';
                                    $content[] = '</div>';
                                $content[] = '</div>';
                            $content[] = '</div>';
                        $content[] = '</div>';

                        $content[] = '<div class="ays-pb-dicount-black-friday-wrap-box" style="width: 25%;">';
                            $content[] = '<a href="'. $ays_pb_cta_button_link .'" class="ays-pb-dicount-black-friday-button-buy-now" target="_blank">' . __( 'Get Your Deal', "ays-popup-box" ) . '</a>';
                        $content[] = '</div>';
                    $content[] = '</div>';
                $content[] = '</div>';

                $content[] = '<div style="position: absolute;right: 0;bottom: 1px;"  class="ays-pb-dismiss-buttons-container-for-form-black-friday">';
                    $content[] = '<form action="" method="POST">';
                        $content[] = '<div id="ays-pb-dismiss-buttons-content">';
                            if( current_user_can( 'manage_options' ) ){
                                $content[] = '<button class="btn btn-link ays-button" name="ays_pb_sale_btn" style="height: 32px; margin-left: 0;padding-left: 0">Dismiss ad</button>';
                                $content[] = wp_nonce_field( AYS_PB_NAME . '-sale-banner' ,  AYS_PB_NAME . '-sale-banner' );
                            }
                        $content[] = '</div>';
                    $content[] = '</form>';
                $content[] = '</div>';
            $content[] = '</div>';

            $content[] = '<script>';
            $content[] = "
                    function aysPbHalloweenCopyToClipboard(text) {
                        // Create a temporary textarea element
                        var textarea = document.createElement('textarea');
                        textarea.value = text;
                        textarea.style.position = 'fixed';
                        textarea.style.opacity = '0';
                        document.body.appendChild(textarea);
                        
                        // Select and copy the text
                        textarea.select();
                        textarea.setSelectionRange(0, 99999); // For mobile devices
                        
                        try {
                            document.execCommand('copy');
                            aysPbHalloweenShowCopyNotification('Coupon code copied!');
                        } catch (err) {
                            console.error('Failed to copy text: ', err);
                        }
                        
                        // Remove the temporary textarea
                        document.body.removeChild(textarea);
                    }

                    function aysPbHalloweenShowCopyNotification(message) {
                        // Check if notification already exists
                        var existingNotification = document.querySelector('.ays-pb-discount-black-friday-banner-2025-copy-notification');
                        if (existingNotification) {
                            document.body.removeChild(existingNotification);
                        }
                        
                        // Create notification element
                        var notification = document.createElement('div');
                        notification.className = 'ays-pb-discount-black-friday-banner-2025-copy-notification';
                        notification.textContent = message;
                        document.body.appendChild(notification);
                        
                        // Show notification with animation
                        setTimeout(function() {
                            notification.classList.add('show');
                        }, 10);
                        
                        // Hide and remove notification after 2 seconds
                        setTimeout(function() {
                            notification.classList.remove('show');
                            setTimeout(function() {
                                if (notification.parentNode) {
                                    document.body.removeChild(notification);
                                }
                            }, 300);
                        }, 2000);
                    }";
            $content[] = '</script>';

            $content[] = '<style>';
            $content[] = '
                /* Black friday banner start */
                div#ays-pb-dicount-black-friday-month-main *{color:#fff}div#ays-pb-dicount-black-friday-month-main div#ays-pb-dicount-black-friday-month a.ays-pb-sale-banner-link:focus{outline:0;box-shadow:0}div#ays-pb-dicount-black-friday-month-main .btn-link{background-color:transparent;display:inline-block;font-weight:400;text-align:center;white-space:nowrap;vertical-align:middle;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;border:1px solid transparent;padding:.375rem .75rem;font-size:12px;line-height:1.5;border-radius:.25rem;color:rgba(255,255,255,.6)}div#ays-pb-dicount-black-friday-month-main.ays_pb_dicount_info{background-image:linear-gradient(45deg,#1e101d,#c60af4);padding:unset;border-left:0}#ays-pb-dicount-black-friday-month-main .ays_pb_dicount_month{position:relative;background-image:url("'. esc_attr(AYS_PB_ADMIN_URL) .'/images/bundles/black-friday-plugins-background-image.webp");background-position:center right;background-repeat:no-repeat;background-size:100% 100%}#ays-pb-dicount-black-friday-month-main .ays_pb_dicount_month img{width:80px}#ays-pb-dicount-black-friday-month-main .ays-pb-sale-banner-link{display:flex;justify-content:center;align-items:center;width:200px}#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-button-sale{font-style:normal;font-weight:600;font-size:24px;text-align:center;color:#b2ff00;text-transform:uppercase}#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-wrap-box{font-size:14px;padding:12px;text-align:center;width:50%;white-space:nowrap}#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-wrap-box strong{font-size:17px;font-weight:700;letter-spacing:.8px}#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-wrap-color{color:#971821}#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-wrap-text-decoration{text-decoration:underline}#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-wrap-box.ays-buy-now-button-box{display:flex;justify-content:flex-end;align-items:center;width:30%}#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-wrap-box .ays-button,#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-wrap-box .ays-buy-now-button{align-items:center;font-weight:500}#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-wrap-box .ays-buy-now-button{background:#971821;border-color:#fff;display:flex;justify-content:center;align-items:center;padding:5px 15px;font-size:16px;border-radius:5px}#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-wrap-box .ays-buy-now-button:hover{background:#7d161d;border-color:#971821}#ays-pb-dicount-black-friday-month-main #ays-pb-dismiss-buttons-content{display:flex;justify-content:center}#ays-pb-dicount-black-friday-month-main #ays-pb-dismiss-buttons-content .ays-button{margin:0!important;font-size:13px;color:#969393b0}#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-wrap-opacity-box{width:19%}#ays-pb-dicount-black-friday-month-main .ays-buy-now-opacity-button{padding:40px 15px;display:flex;justify-content:center;align-items:center;opacity:0}#ays-pb-countdown-main-container .ays-pb-countdown-container{margin:0 auto;text-align:center}#ays-pb-countdown-main-container #ays-pb-countdown-headline{letter-spacing:.125rem;text-transform:uppercase;font-size:18px;font-weight:400;margin:0;padding:9px 0 4px;line-height:1.3}#ays-pb-countdown-main-container li,#ays-pb-countdown-main-container ul{margin:0;font-weight:600}#ays-pb-countdown-main-container li{display:inline-block;font-size:10px;list-style-type:none;padding:10px;text-transform:uppercase}#ays-pb-countdown-main-container li span{display:block;font-size:22px;min-height:33px}#ays-pb-countdown-main-container .emoji{display:none;padding:1rem}#ays-pb-countdown-main-container .emoji span{font-size:25px;padding:0 .5rem}#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-box{display:flex;justify-content:space-between;align-items:center;width:95%;margin:auto}#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-title-row{text-align:center;padding-right:50px;font-style:normal;font-weight:900;font-size:19px;color:#fff;}#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-button-buy-now{border:none;outline:0;padding:10px 20px;font-size:22px;text-transform:uppercase;font-weight:700;text-decoration:none;background:linear-gradient(180deg,#dd0bef 0,#82008d 100%);border-radius:16px}#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-text-row{text-transform:uppercase;text-shadow:-1.5px 0 #dd0bef,0 1.5px #dd0bef,1.5px 0 #dd0bef,0 -1.5px #dd0bef;font-weight:900;font-style:normal;font-size:40px;line-height:40px;color:#fff}#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-wrap-text-box{position:absolute;width:25%;top:10px;bottom:0;right:0;left:0;margin:0 auto}#ays-pb-countdown ul{padding:0}#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-banner-2025-coupon-box{border:2px dashed rgba(255,255,255,.4);padding:0 12px;border-radius:6px;background:rgba(255,255,255,.1);cursor:pointer;transition:.3s;display:flex;align-items:center;justify-content:center;gap:6px;backdrop-filter:blur(10px);width:fit-content;margin:0 auto}#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-banner-2025-coupon-box:hover{background:rgba(255,255,255,.2);border-color:rgba(255,255,255,.6);transform:translateY(-1px)}#ays-pb-dicount-black-friday-month-main .ays-pb-discount-black-friday-banner-2025-coupon-text{font-size:14px;font-weight:700;letter-spacing:1px;color:#fff;font-family:monospace}#ays-pb-dicount-black-friday-month-main .ays-pb-discount-black-friday-banner-2025-copy-icon{opacity:.8;transition:opacity .3s}#ays-pb-dicount-black-friday-month-main .ays-pb-discount-black-friday-banner-banner-2025-coupon-box:hover .ays-pb-discount-black-friday-banner-2025-copy-icon,.ays-pb-discount-black-friday-banner-2025-copy-notification.show{opacity:1}.ays-pb-discount-black-friday-banner-2025-copy-notification{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:rgba(0,0,0,.8);color:#fff;padding:12px 24px;border-radius:8px;font-size:14px;z-index:10000;opacity:0;transition:opacity .3s}@media screen and (max-width:1400px) and (min-width:1200px){div#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-title-row{font-size:15px}div#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-text-row{font-size:27px}div#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-box{width:100%}div#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-button-buy-now{font-size:13px}div#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-wrap-box-80{width:80%!important}}@media all and (max-width:1200px){div#ays-pb-dicount-black-friday-month-main .ays_pb_dicount_month{background:unset}div#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-text-row{font-size:30px}div#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-box{width:100%}div#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-button-buy-now{font-size:15px}div#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-wrap-box-80{width:80%!important}}@media all and (max-width:1200px) and (min-width:1150px){div#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-title-row{font-size:15px}div#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-button-buy-now{font-size:10px}}@media all and (max-width:1150px){div#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-wrap-box-80{width:80%!important}div#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-box{flex-direction:column}div#ays-pb-dicount-black-friday-month-main{padding-right:0}div#ays-pb-dicount-black-friday-month-main .ays_pb_dicount_month{display:flex;align-items:center;justify-content:space-between;align-content:center;flex-wrap:wrap;flex-direction:column;padding:10px 0}div#ays-pb-dicount-black-friday-month-main div.ays-pb-dicount-black-friday-wrap-box{width:100%!important;text-align:center}#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-title-row,div#ays-pb-dicount-black-friday-month-main #ays-pb-countdown-main-container ul{padding:0;font-size:13px}#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-button-sale,div#ays-pb-countdown-main-container li{font-size:15px}div#ays-pb-countdown-main-container li span{font-size:25px}div#ays-pb-dicount-black-friday-month-main div.ays-pb-dicount-black-friday-wrap-text-box{position:unset}#ays-pb-countdown-main-container #ays-pb-countdown-headline{font-size:15px;font-weight:600}#ays-pb-countdown-main-container ul{font-weight:500}#ays-pb-countdown-main-container li span{font-size:20px}#ays-pb-dicount-black-friday-month-main .ays-button{margin:0 auto!important}div#ays-pb-dicount-black-friday-month-main.ays_pb_dicount_info.notice{background-position:bottom right;background-repeat:no-repeat;background-size:contain;display:flex;justify-content:center;background-image:linear-gradient(45deg,#1e101d,#c60af4)}div#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-wrap-box.ays-buy-now-button-box{justify-content:center}div#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-wrap-box .ays-buy-now-button{font-size:14px;padding:5px 10px}div#ays-pb-dicount-black-friday-month-main .ays-pb-dicount-black-friday-button-buy-now{padding:10px 18px;font-size:15px}}@media all and (max-width:768px){#ays-pb-dicount-black-friday-month-main{display:none!important}}
                ';
            $content[] = '</style>';

            $content = implode( '', $content );

            echo $content;
        }
    }

    // Halloween Bundle 2025
    public function ays_pb_new_halloween_bundle_message_2025($ishmar){
        if($ishmar == 0 ){
            $content = array();

            $date = time() + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS);
            $now_date = date('M d, Y H:i:s', $date);

            $start_date = strtotime('2025-09-08 00:00:01');
            $end_date = strtotime('2025-11-07 23:59:59');
            $diff_end = $end_date - $date;

            $style_attr = '';
            if( $diff_end < 0 ){
                $style_attr = 'style="display:none;"';
            }

            $ays_pb_cta_button_link = esc_url('https://popup-plugin.com/?utm_source=dashboard&utm_medium=pb-free&utm_campaign=pb-halloween-banner-' . AYS_PB_NAME_VERSION);

            $content[] = '
                <div id="ays-pb-halloween-banner-2025-main" class="ays-pb-halloween-banner-2025-main ays_pb_dicount_info notice notice-success is-dismissible">
                    <div class="ays-pb-halloween-banner-2025-content">
                        <div class="ays-pb-halloween-banner-2025-left">
                            <div class="ays-pb-halloween-banner-2025-text">
                                <h2 class="ays-pb-halloween-banner-2025-title">Boo! Grab Your <a href="'. $ays_pb_cta_button_link .'" class="" target="_blank">Halloween Deal</a> <br/> Before It Vanishes!</h2>
                                <p class="ays-pb-halloween-banner-2025-subtitle">Don’t get spooked by missing out!<br/> Get 25% discount using the coupon code while the magic lasts!</p>
                            </div>
                        </div>

                        <div class="ays-pb-halloween-banner-2025-center">';

                        $content[] = '<div id="ays-pb-halloween-banner-2025-countdown" class="ays-pb-halloween-banner-2025-countdown" ' . $style_attr . '>';
                            $content[] = '<div class="ays-pb-halloween-banner-2025-countdown-timer">';
                                $content[] = '<div class="ays-pb-halloween-banner-2025-countdown-item">';
                                    $content[] = '<div class="ays-pb-halloween-banner-2025-countdown-value" id="ays-pb-halloween-banner-2025-days">00</div>';
                                    $content[] = '<div class="ays-pb-halloween-banner-2025-countdown-label">' . __('days', 'ays-popup-box') . '</div>';
                                $content[] = '</div>';
                                $content[] = '<div class="ays-pb-halloween-banner-2025-countdown-separator">:</div>';
                                $content[] = '<div class="ays-pb-halloween-banner-2025-countdown-item">';
                                    $content[] = '<div class="ays-pb-halloween-banner-2025-countdown-value" id="ays-pb-halloween-banner-2025-hours">00</div>';
                                    $content[] = '<div class="ays-pb-halloween-banner-2025-countdown-label">' . __('hours', 'ays-popup-box') . '</div>';
                                $content[] = '</div>';
                                $content[] = '<div class="ays-pb-halloween-banner-2025-countdown-separator">:</div>';
                                $content[] = '<div class="ays-pb-halloween-banner-2025-countdown-item">';
                                    $content[] = '<div class="ays-pb-halloween-banner-2025-countdown-value" id="ays-pb-halloween-banner-2025-minutes">00</div>';
                                    $content[] = '<div class="ays-pb-halloween-banner-2025-countdown-label">' . __('minutes', 'ays-popup-box') . '</div>';
                                $content[] = '</div>';
                                $content[] = '<div class="ays-pb-halloween-banner-2025-countdown-separator">:</div>';
                                $content[] = '<div class="ays-pb-halloween-banner-2025-countdown-item">';
                                    $content[] = '<div class="ays-pb-halloween-banner-2025-countdown-value" id="ays-pb-halloween-banner-2025-seconds">00</div>';
                                    $content[] = '<div class="ays-pb-halloween-banner-2025-countdown-label">' . __('seconds', 'ays-popup-box') . '</div>';
                                $content[] = '</div>';
                            $content[] = '</div>';
                        $content[] = '</div>';

                        $content[] = '</div>
                                                
                        <div class="ays-pb-halloween-banner-2025-right">
                            <div class="ays-pb-halloween-banner-2025-pumpkin">
                                <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_177_40)">
                                    <path d="M32.664 8.519C29.364 5.134 23.42 4.75 18 4.75C12.58 4.75 6.636 5.134 3.336 8.519C0.582 11.344 0 15.751 0 19.791C0 25.054 1.982 31.102 6.357 34.035C9.364 36.051 13.95 35.871 18 35.871C22.05 35.871 26.636 36.051 29.643 34.035C34.018 31.101 36 25.054 36 19.791C36 15.751 35.418 11.344 32.664 8.519Z" fill="#F4900C"/>
                                    <path d="M20.783 5.44401C20.852 5.86401 20.561 6.20801 20.136 6.20801H15.685C15.259 6.20801 14.968 5.86401 15.038 5.44401L15.783 0.972008C15.853 0.551008 16.259 0.208008 16.685 0.208008H19.136C19.562 0.208008 19.968 0.552008 20.037 0.972008L20.783 5.44401Z" fill="#3F7123"/>
                                    <path d="M20.6541 21.159L19.0561 18.563C18.7651 18.021 18.3831 17.75 17.9991 17.746C17.6161 17.75 17.2331 18.021 16.9421 18.563L15.3441 21.159C14.7571 22.252 16.2171 22.875 17.9981 22.875C19.7791 22.875 21.2411 22.251 20.6541 21.159ZM30.1621 24.351C30.1171 24.276 30.0361 24.23 29.9481 24.23H29.1071C29.0391 24.23 28.9731 24.258 28.9261 24.307L26.6951 26.641L23.9971 24.472C23.9461 24.431 23.8801 24.414 23.8121 24.419C23.7461 24.426 23.6851 24.46 23.6441 24.513L21.2361 27.575L18.1821 24.309C18.1691 24.295 18.1491 24.292 18.1341 24.281C18.1191 24.271 18.1091 24.254 18.0911 24.247C18.0851 24.245 18.0781 24.247 18.0721 24.245C18.0481 24.238 18.0251 24.24 18.0001 24.24C17.9751 24.24 17.9521 24.238 17.9281 24.246C17.9221 24.248 17.9151 24.245 17.9081 24.248C17.8901 24.255 17.8811 24.272 17.8651 24.282C17.8491 24.292 17.8301 24.295 17.8171 24.309L14.7641 27.575L12.3551 24.513C12.3141 24.46 12.2531 24.426 12.1871 24.419C12.1211 24.413 12.0541 24.431 12.0021 24.472L9.30411 26.641L7.07411 24.307C7.02711 24.258 6.96211 24.23 6.89311 24.23H6.05211C5.96511 24.23 5.88311 24.276 5.83811 24.351C5.79311 24.426 5.79011 24.519 5.83111 24.596L8.58511 29.815C8.61911 29.879 8.6781 29.925 8.7491 29.942C8.8201 29.959 8.8941 29.944 8.9521 29.902L10.9861 28.444L13.9901 32.077C14.0331 32.13 14.0961 32.162 14.1641 32.167L14.1831 32.168C14.2451 32.168 14.3041 32.146 14.3501 32.105L18.0001 28.836L21.6501 32.104C21.6961 32.145 21.7551 32.167 21.8171 32.167L21.8361 32.166C21.9041 32.161 21.9671 32.129 22.0101 32.076L25.0151 28.443L27.0491 29.901C27.1091 29.944 27.1821 29.961 27.2521 29.941C27.3221 29.924 27.3821 29.879 27.4151 29.815L30.1701 24.596C30.2101 24.519 30.2081 24.426 30.1621 24.351ZM27.9761 15.421C28.1051 17.548 27.1921 19.227 24.7711 19.374C22.3511 19.52 21.2421 17.963 21.1131 15.837C20.9841 13.711 22.3451 10.717 24.2401 10.603C26.1361 10.487 27.8481 13.294 27.9761 15.421ZM8.02411 15.421C7.89511 17.548 8.80811 19.227 11.2291 19.374C13.6491 19.52 14.7581 17.963 14.8871 15.837C15.0161 13.711 13.6551 10.717 11.7601 10.603C9.86511 10.489 8.15211 13.294 8.02411 15.421Z" fill="#642116"/>
                                    </g>
                                    <defs>
                                    <clipPath id="clip0_177_40">
                                    <rect width="36" height="36" fill="white"/>
                                    </clipPath>
                                    </defs>
                                </svg>
                            </div>



                            <div class="ays-pb-halloween-banner-2025-discount-section">
                                <div class="ays-pb-halloween-banner-2025-discount">25% OFF</div>
                                <div class="ays-pb-halloween-banner-2025-coupon-wrapper">
                                    <div class="ays-pb-halloween-banner-2025-coupon-box" onclick="aysPbHalloweenCopyToClipboard(\'HALLOWEEN25\')" title="Click to copy">
                                        <span class="ays-pb-halloween-banner-2025-coupon-text">HALLOWEEN25</span>
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="ays-pb-halloween-banner-2025-copy-icon">
                                            <path d="M13.5 2.5H6.5C5.67 2.5 5 3.17 5 4V10C5 10.83 5.67 11.5 6.5 11.5H13.5C14.33 11.5 15 10.83 15 10V4C15 3.17 14.33 2.5 13.5 2.5ZM13.5 10H6.5V4H13.5V10ZM2.5 6.5V12.5C2.5 13.33 3.17 14 4 14H10V12.5H4V6.5H2.5Z" fill="white"/>
                                        </svg>
                                    </div>
                                </div>
                                <a href="'. $ays_pb_cta_button_link .'" class="ays-pb-halloween-banner-2025-upgrade" target="_blank">Upgrade Now</a>
                            </div>';

                            if( current_user_can( 'manage_options' ) ){
                                $content[] = '<div id="ays-pb-dismiss-buttons-content">';
                                    $content[] = '<form action="" method="POST" style="position: absolute; bottom: -5px; right: 0; color: #fff;">';
                                            $content[] = '<button class="btn btn-link ays-button" name="ays_pb_sale_btn" style="color: darkgrey; font-size: 11px;">'. __( "Dismiss ad", 'ays-popup-box' ) .'</button>';
                                            $content[] = wp_nonce_field( AYS_PB_NAME . '-sale-banner' ,  AYS_PB_NAME . '-sale-banner' );
                                    $content[] = '</form>';
                                $content[] = '</div>';
                            }

                            $content[] = '
                        </div>
                    </div>
                </div>';

            $content[] = '<style id="ays-pb-progress-banner-styles-inline-css">';
            $content[] = '
                .ays-pb-halloween-banner-2025-main {
                    background: linear-gradient(135deg, #1A0F2E 100%, #2D1B4E 0%);
                    background-image: url("' . esc_attr( AYS_PB_ADMIN_URL ) . '/images/halloween-banner-background-image-remove.png"), linear-gradient(135deg, #2D1B4E 0%, #1A0F2E 100%);
                    background-position: left center, center;
                    background-repeat: no-repeat, no-repeat;
                    background-size: auto 100%, cover;
                    padding: 20px 30px 20px 130px;
                    border-radius: 12px;
                    color: white;
                    margin: 20px 0;
                    border: 0;
                    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
                    overflow: hidden;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-content {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 30px;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-left {
                    display: flex;
                    align-items: center;
                    gap: 20px;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-center {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    flex: 1;
                    max-width: 350px;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-right {
                    display: flex;
                    align-items: center;
                    gap: 15px;
                    flex-shrink: 0;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-icon {
                    flex-shrink: 0;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-pumpkin svg {
                    display: inline !important;
                    border: none !important;
                    box-shadow: none !important;
                    height: 1em !important;
                    width: 1em !important;
                    margin: 0 0.07em !important;
                    vertical-align: -0.1em !important;
                    background: none !important;
                    padding: 0 !important;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-orb {
                    width: 80px;
                    height: 80px;
                    background: linear-gradient(135deg, #8B5CF6 0%, #6D28D9 100%);
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    box-shadow: 0 0 30px rgba(139, 92, 246, 0.6);
                    border: 3px solid rgba(168, 85, 247, 0.4);
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-question {
                    font-size: 48px;
                    font-weight: 700;
                    color: #E9D5FF;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-bat {
                    font-size: 20px;
                    opacity: 0.8;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-bat-1 {
                    margin-left: -100px;
                    margin-top: -40px;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-bat-2 {
                    margin-left: -110px;
                    margin-top: 35px;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-sparkle {
                    font-size: 12px;
                    opacity: 0.7;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-sparkle-1 {
                    margin-left: -95px;
                    margin-top: -60px;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-sparkle-2 {
                    margin-left: -70px;
                    margin-top: -15px;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-sparkle-3 {
                    margin-left: -120px;
                    margin-top: 10px;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-text {
                    flex: 1;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-title {
                    font-size: 24px;
                    font-weight: 700;
                    margin: 0 0 8px 0;
                    line-height: 1.2;
                    color: #fff;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-title a {
                    color: #FB923C;
                    text-decoration: underline;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-subtitle {
                    font-size: 16px;
                    margin: 0;
                    opacity: 0.9;
                    font-weight: 400;
                    color: #E9D5FF;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-description {
                    font-size: 14px;
                    margin: 0;
                    opacity: 0.85;
                    line-height: 1.5;
                    color: #D8B4FE;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-pumpkin {
                    font-size: 64px;
                    filter: drop-shadow(0 0 20px rgba(251, 146, 60, 0.8));
                    flex-shrink: 0;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-discount-section {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    gap: 10px;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-discount {
                    font-size: 36px;
                    font-weight: 700;
                    color: #FB923C;
                    text-shadow: 0 0 20px rgba(251, 146, 60, 0.6);
                    margin: 0;
                    line-height: 1;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-coupon-wrapper {
                    margin-bottom: 5px;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-coupon-box {
                    border: 2px dashed rgba(255, 255, 255, 0.4);
                    padding: 6px 12px;
                    border-radius: 6px;
                    background: rgba(255, 255, 255, 0.1);
                    cursor: pointer;
                    transition: all 0.3s ease;
                    display: flex;
                    align-items: center;
                    gap: 6px;
                    backdrop-filter: blur(10px);
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-coupon-box:hover {
                    background: rgba(255, 255, 255, 0.2);
                    border-color: rgba(255, 255, 255, 0.6);
                    transform: translateY(-1px);
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-coupon-text {
                    font-size: 14px;
                    font-weight: 700;
                    letter-spacing: 1px;
                    color: #fff;
                    font-family: monospace;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-copy-icon {
                    opacity: 0.8;
                    transition: opacity 0.3s ease;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-coupon-box:hover .ays-pb-halloween-banner-2025-copy-icon {
                    opacity: 1;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-upgrade {
                    background: linear-gradient(135deg, #FB923C 0%, #F97316 100%);
                    color: white;
                    border: none;
                    padding: 12px 28px;
                    border-radius: 8px;
                    font-size: 16px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    box-shadow: 0 4px 16px rgba(251, 146, 60, 0.5);
                    text-decoration: none;
                    display: inline-flex;
                    align-items: center;
                    text-align: center;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-upgrade:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 6px 20px rgba(251, 146, 60, 0.7);
                    text-decoration: none;
                    color: white;
                }

                .ays-pb-halloween-banner-2025-main .notice-dismiss:before {
                    color: #fff;
                }

                .ays-pb-halloween-banner-2025-copy-notification {
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: rgba(0, 0, 0, 0.8);
                    color: white;
                    padding: 12px 24px;
                    border-radius: 8px;
                    font-size: 14px;
                    z-index: 10000;
                    opacity: 0;
                    transition: opacity 0.3s ease;
                }

                .ays-pb-halloween-banner-2025-copy-notification.show {
                    opacity: 1;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-countdown-timer {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-countdown-item {
                    background: rgba(255, 255, 255, 0.15);
                    border-radius: 8px;
                    padding: 8px 12px;
                    min-width: 60px;
                    backdrop-filter: blur(10px);
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-countdown-value {
                    font-size: 24px;
                    font-weight: 700;
                    line-height: 1;
                    margin-bottom: 4px;
                    color: #fff;
                    text-align: center;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-countdown-label {
                    font-size: 11px;
                    opacity: 0.8;
                    text-transform: lowercase;
                    text-align: center;
                }

                .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-countdown-separator {
                    font-size: 24px;
                    font-weight: 700;
                    opacity: 0.6;
                    margin: 0 4px;
                }

                @media (min-width: 1200px) {
                    .ays-pb-halloween-banner-2025-main .wp-core-ui .notice.is-dismissible {
                        padding-right: 60px;
                    }
                }

                @media (max-width: 1200px) {

                    div.ays-pb-halloween-banner-2025-main {
                        padding: 20px 30px;
                    }

                    div.ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-pumpkin {
                        display: none;
                    }

                    .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-subtitle,
                    .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-title {
                        text-align: center;
                    }

                    .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-content {
                        flex-wrap: wrap;
                        gap: 20px;
                    }

                    .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-left {
                        width: 100%;
                    }

                    .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-center {
                        width: 100%;
                        max-width: 100%;
                        text-align: center;
                    }

                    .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-right {
                        width: 100%;
                        justify-content: center;
                    }
                }

                @media (max-width: 786px) {
                    #ays-pb-halloween-banner-2025-main {
                        display: none !important;
                    }
                }

                @media (max-width: 768px) {
                    .ays-pb-halloween-banner-2025-main {
                        padding: 15px 20px;
                        margin: 15px 0;
                    }

                    .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-title {
                        font-size: 20px;
                    }

                    .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-subtitle {
                        font-size: 14px;
                    }

                    .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-description {
                        font-size: 13px;
                    }

                    .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-pumpkin {
                        font-size: 48px;
                    }

                    .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-discount {
                        font-size: 28px;
                    }

                    .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-upgrade {
                        padding: 10px 20px;
                        font-size: 14px;
                    }
                }

                @media (max-width: 480px) {
                    .ays-pb-halloween-banner-2025-main {
                        padding: 12px 15px;
                    }

                    .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-orb {
                        width: 60px;
                        height: 60px;
                    }

                    .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-question {
                        font-size: 36px;
                    }

                    .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-title {
                        font-size: 18px;
                    }

                    .ays-pb-halloween-banner-2025-main .ays-pb-halloween-banner-2025-coupon-text {
                        font-size: 12px;
                    }
                }
            ';

            $content[] = '</style>';

            $content[] = '<script>';
            $content[] = "
                    function aysPbHalloweenCopyToClipboard(text) {
                        // Create a temporary textarea element
                        var textarea = document.createElement('textarea');
                        textarea.value = text;
                        textarea.style.position = 'fixed';
                        textarea.style.opacity = '0';
                        document.body.appendChild(textarea);
                        
                        // Select and copy the text
                        textarea.select();
                        textarea.setSelectionRange(0, 99999); // For mobile devices
                        
                        try {
                            document.execCommand('copy');
                            aysPbHalloweenShowCopyNotification('Coupon code copied!');
                        } catch (err) {
                            console.error('Failed to copy text: ', err);
                        }
                        
                        // Remove the temporary textarea
                        document.body.removeChild(textarea);
                    }

                    function aysPbHalloweenShowCopyNotification(message) {
                        // Check if notification already exists
                        var existingNotification = document.querySelector('.ays-pb-halloween-banner-2025-copy-notification');
                        if (existingNotification) {
                            document.body.removeChild(existingNotification);
                        }
                        
                        // Create notification element
                        var notification = document.createElement('div');
                        notification.className = 'ays-pb-halloween-banner-2025-copy-notification';
                        notification.textContent = message;
                        document.body.appendChild(notification);
                        
                        // Show notification with animation
                        setTimeout(function() {
                            notification.classList.add('show');
                        }, 10);
                        
                        // Hide and remove notification after 2 seconds
                        setTimeout(function() {
                            notification.classList.remove('show');
                            setTimeout(function() {
                                if (notification.parentNode) {
                                    document.body.removeChild(notification);
                                }
                            }, 300);
                        }, 2000);
                    }

                    (function() {
                        var endDate = new Date('". date('Y-m-d H:i:s', $end_date) ."').getTime();
                    
                        function updateCountdown() {
                            var now = new Date().getTime();
                            var distance = endDate - now;
                            
                            if (distance < 0) {
                                clearInterval(updateCountdown);
                                document.getElementById('ays-pb-halloween-banner-2025-progress-banner-countdown').style.display = 'none';
                                return;
                            }
                            
                            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                            var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                            
                            function padZero(num) {
                                return num < 10 ? '0' + num : num;
                            }
                            
                            document.getElementById('ays-pb-halloween-banner-2025-days').textContent = padZero(days);
                            document.getElementById('ays-pb-halloween-banner-2025-hours').textContent = padZero(hours);
                            document.getElementById('ays-pb-halloween-banner-2025-minutes').textContent = padZero(minutes);
                            document.getElementById('ays-pb-halloween-banner-2025-seconds').textContent = padZero(seconds);
                        }
                        
                        updateCountdown();
                        setInterval(updateCountdown, 1000);
                    })()";
            $content[] = '</script>';

            $content = implode( '', $content );
            echo ($content);
        }
    }

    // New Mega Bundle
    public function ays_pb_new_mega_bundle_message_2025($ishmar){
        if($ishmar == 0 ){
            $content = array();

            $pb_cta_button_link = sprintf('https://ays-pro.com/essential-bundle/?utm_source=dashboard&utm_medium=popup-free&utm_campaign=essential-sale-banner-%s', AYS_PB_NAME_VERSION);

            $content[] = '<div id="ays-pb-new-mega-bundle-2025-dicount-month-main" class="ays-pb-notice notice notice-success is-dismissible ays_pb_dicount_info">';
                $content[] = '<div id="ays-pb-dicount-month" class="ays_pb_dicount_month">';
                    $content[] = '<div class="ays-pb-dicount-wrap-box ays-pb-dicount-wrap-text-box">';
                        $content[] = '<div>';

                            $content[] = '<span class="ays-pb-new-mega-bundle-2025-title">';
                                /* translators: %s: link to Essential Bundle and %s: (Quiz + Form + Popup) */
                                $content[] = sprintf(' <a href="%s" target="_blank" style="color:#ffffff; text-decoration: underline;">Essential Bundle</a> ( %s )', esc_url($pb_cta_button_link), esc_html__( "Quiz + Form + Popup", "ays-popup-box" ));
                            $content[] = '</span>';
                            $content[] = '</br>';

                            $content[] = '<span class="ays-pb-new-mega-bundle-2025-desc">';
                                $content[] = '<img style="width: 30px;height: 30px;margin-right: 5px;" src="' . esc_attr(AYS_PB_ADMIN_URL) . '/images/icons/pb-guaranteeicon.svg" width="30" height="30">';
                                $content[] = __( "30 Day Money Back Guarantee", 'ays-popup-box' );
                            $content[] = '</span>';
                        $content[] = '</div>';
                        $content[] = '<div>';
                                $content[] = '<img class="ays-pb-new-mega-bundle-guaranteeicon" src="' . AYS_PB_ADMIN_URL . '/images/ays-pb-banner-50.svg" style="width: 80px;">';
                        $content[] = '</div>';

                        $content[] = '<div style="position: absolute;right: 10px;bottom: 1px;" class="ays-pb-dismiss-buttons-container-for-form">';

                            $content[] = '<form action="" method="POST">';
                                $content[] = '<div id="ays-pb-dismiss-buttons-content">';
                                if( current_user_can( 'manage_options' ) ){
                                    $content[] = '<button class="btn btn-link ays-button" name="ays_pb_sale_btn" style="height: 32px; margin-left: 0;padding-left: 0">'. __( "Dismiss ad", 'ays-popup-box' ) .'</button>';
                                    $content[] = wp_nonce_field( AYS_PB_NAME . '-sale-banner' ,  AYS_PB_NAME . '-sale-banner' );
                                }
                                $content[] = '</div>';
                            $content[] = '</form>';
                            
                        $content[] = '</div>';

                    $content[] = '</div>';

                    $content[] = '<div class="ays-pb-dicount-wrap-box ays-pb-dicount-wrap-countdown-box">';

                        $content[] = '<div id="ays-pb-countdown-main-container">';
                            $content[] = '<div class="ays-pb-countdown-container">';

                                $content[] = '<div id="ays-pb-countdown">';

                                    $content[] = '<ul>';
                                        $content[] = '<li><span id="ays-pb-countdown-days"></span>'. __( "Days", 'ays-popup-box' ) .'</li>';
                                        $content[] = '<li><span id="ays-pb-countdown-hours"></span>'. __( "Hours", 'ays-popup-box' ) .'</li>';
                                        $content[] = '<li><span id="ays-pb-countdown-minutes"></span>'. __( "Minutes", 'ays-popup-box' ) .'</li>';
                                        $content[] = '<li><span id="ays-pb-countdown-seconds"></span>'. __( "Seconds", 'ays-popup-box' ) .'</li>';
                                    $content[] = '</ul>';
                                $content[] = '</div>';

                                $content[] = '<div id="ays-pb-countdown-content" class="emoji">';
                                    $content[] = '<span></span>';
                                    $content[] = '<span></span>';
                                    $content[] = '<span></span>';
                                    $content[] = '<span></span>';
                                $content[] = '</div>';

                            $content[] = '</div>';
                        $content[] = '</div>';
                            
                    $content[] = '</div>';

                    $content[] = '<div class="ays-pb-dicount-wrap-box ays-pb-dicount-wrap-button-box">';
                        $content[] = '<a href="'. $pb_cta_button_link .'" class="button button-primary ays-button" id="ays-button-top-buy-now" target="_blank">' . __( 'Buy Now', 'ays-popup-box' ) . '</a>';
                        $content[] = '<span class="ays-pb-dicount-one-time-text">';
                            $content[] = __( "One-time payment", 'ays-popup-box' );
                        $content[] = '</span>';
                    $content[] = '</div>';
                $content[] = '</div>';

                $content[] = '<style>';
                $content[] = 'div#ays-pb-new-mega-bundle-2025-dicount-month-main{border:0;background:#5551ff;border-radius:20px;box-shadow:unset;position:relative;z-index:1;min-height:80px}div#ays-pb-new-mega-bundle-2025-dicount-month-main.ays_pb_dicount_info button{display:flex;align-items:center}div#ays-pb-new-mega-bundle-2025-dicount-month-main div#ays-pb-dicount-month a.ays-pb-sale-banner-link:focus{outline:0;box-shadow:0}div#ays-pb-new-mega-bundle-2025-dicount-month-main .btn-link{color:#007bff;background-color:transparent;display:inline-block;font-weight:400;text-align:center;white-space:nowrap;vertical-align:middle;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;border:1px solid transparent;padding:.375rem .75rem;font-size:1rem;line-height:1.5;border-radius:.25rem}div#ays-pb-new-mega-bundle-2025-dicount-month-main.ays_pb_dicount_info{background-image:url("' . esc_attr(AYS_PB_ADMIN_URL) . '/images/new-mega-bundle-logo-background.svg");background-position:center right;background-repeat:no-repeat;background-size:cover}#ays-pb-new-mega-bundle-2025-dicount-month-main .ays_pb_dicount_month{display:flex;align-items:center;justify-content:space-between;color:#fff}#ays-pb-new-mega-bundle-2025-dicount-month-main .ays_pb_dicount_month img{width:80px}#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-sale-banner-link{display:flex;justify-content:center;align-items:center;width:200px}#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box{font-size:14px;padding:12px;text-align:center}#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box{text-align:left;width:30%;display:flex;justify-content:space-between;align-items:center}#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-countdown-box{width:30%;display:flex;justify-content:flex-start;align-items:center}#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-button-box{width:20%;display:flex;justify-content:center;align-items:center;flex-direction:column}#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box .ays-pb-new-mega-bundle-2025-title{color:#fdfdfd;font-size:18px;font-style:normal;font-weight:600;line-height:normal}#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box .ays-pb-new-mega-bundle-2025-title-icon-row{display:inline-block}#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box .ays-pb-new-mega-bundle-2025-desc{display:inline-block;color:#fff;font-size:16px;font-style:normal;font-weight:400;line-height:normal;margin-top:10px}#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box strong{font-size:17px;font-weight:700;letter-spacing:.8px}#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-color{color:#971821}#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-text-decoration{text-decoration:underline}#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box.ays-buy-now-button-box{display:flex;justify-content:flex-end;align-items:center;width:30%}#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box .ays-button,#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box .ays-buy-now-button{align-items:center;font-weight:500}#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box .ays-buy-now-button{background:#971821;border-color:#fff;display:flex;justify-content:center;align-items:center;padding:5px 15px;font-size:16px;border-radius:5px}#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box .ays-buy-now-button:hover{background:#7d161d;border-color:#971821}#ays-pb-new-mega-bundle-2025-dicount-month-main #ays-pb-dismiss-buttons-content{display:flex;justify-content:center}#ays-pb-new-mega-bundle-2025-dicount-month-main #ays-pb-dismiss-buttons-content .ays-button{margin:0!important;font-size:13px;color:#fff}#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-opacity-box{width:19%}#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-buy-now-opacity-button{padding:40px 15px;display:flex;justify-content:center;align-items:center;opacity:0}#ays-pb-countdown-main-container .ays-pb-countdown-container{margin:0 auto;text-align:center}#ays-pb-countdown-main-container #ays-pb-countdown-headline{letter-spacing:.125rem;text-transform:uppercase;font-size:18px;font-weight:400;margin:0;padding:9px 0 4px;line-height:1.3}#ays-pb-countdown-main-container li,#ays-pb-countdown-main-container ul{margin:0}#ays-pb-countdown-main-container li{display:inline-block;font-size:14px;list-style-type:none;padding:14px;text-transform:capitalize}#ays-pb-countdown-main-container li span{display:flex;justify-content:center;align-items:center;font-size:40px;min-height:62px;min-width:62px;border-radius:4.273px;border:.534px solid #f4f4f4;background:#9896ed}#ays-pb-countdown-main-container .emoji{display:none;padding:1rem}#ays-pb-countdown-main-container .emoji span{font-size:30px;padding:0 .5rem}#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box li{position:relative}#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box li span:after{content:":";color:#fff;position:absolute;top:10px;right:-5px;font-size:40px}#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box li span#ays-pb-countdown-seconds:after{content:unset}#ays-pb-new-mega-bundle-2025-dicount-month-main #ays-button-top-buy-now{display:flex;align-items:center;border-radius:6.409px;background:#f56123;padding:12px 32px;color:#fff;font-size:15px;font-style:normal;line-height:normal;margin:0!important;border-color:#f56123}#ays-pb-new-mega-bundle-2025-dicount-month-main #ays-button-top-buy-now:hover{background:#ff8653;border-color:#ff8653}#ays-pb-new-mega-bundle-2025-dicount-month-main #ays-button-top-buy-now:active{background:#bd4b1c;border-color:#bd4b1c}div#ays-pb-new-mega-bundle-2025-dicount-month-main button.notice-dismiss:before{color:#fff;content:"X";font-family:sans-serif;font-size:22px;transition:.2s ease-out}div#ays-pb-new-mega-bundle-2025-dicount-month-main button.notice-dismiss:hover:before{color:#edc9b8}#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-new-mega-bundle-2025-guaranteeicon{width:30px;margin-right:5px}#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-one-time-text{color:#fff;font-size:12px;font-style:normal;font-weight:600;line-height:normal}@media all and (max-width:768px){div#ays-pb-new-mega-bundle-2025-dicount-month-main{padding-right:0}div#ays-pb-new-mega-bundle-2025-dicount-month-main .ays_pb_dicount_month{display:flex;align-items:center;justify-content:space-between;align-content:center;flex-wrap:wrap;flex-direction:column;padding:10px 0}div#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box{width:100%!important;text-align:center}#ays-pb-countdown-main-container #ays-pb-countdown-headline{font-size:15px;font-weight:600}#ays-pb-countdown-main-container ul{font-weight:500}#ays-pb-countdown-main-container li span{font-size:35px;min-height:57px;min-width:55px}div#ays-pb-countdown-main-container li{padding:10px}div#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-new-mega-bundle-2025-mobile-image-display-none{display:none!important}div#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-new-mega-bundle-2025-mobile-image-display-block{display:block!important;margin-top:5px}div#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box{width:100%!important;text-align:center;flex-direction:column;margin-top:20px;justify-content:center;align-items:center;gap:10px}div#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box li span:after{top:unset}div#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-countdown-box{width:100%;display:flex;justify-content:center;align-items:center}#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-button{margin:0 auto!important}div#ays-pb-new-mega-bundle-2025-dicount-month-main.ays_pb_dicount_info.notice{background-position:bottom right;background-repeat:no-repeat;background-size:cover;border-radius:32px}#ays-pb-new-mega-bundle-2025-dicount-month-main #ays-pb-dismiss-buttons-content .ays-button{padding-left:unset!important}div#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box.ays-buy-now-button-box{justify-content:center}div#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box .ays-buy-now-button{font-size:14px;padding:5px 10px}div#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-buy-now-opacity-button{display:none}#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dismiss-buttons-container-for-form{position:static!important}.comparison .product img{width:70px}.ays-pb-features-wrap .comparison a.price-buy{padding:8px 5px;font-size:11px}}@media screen and (max-width:1305px) and (min-width:768px){div#ays-pb-new-mega-bundle-2025-dicount-month-main.ays_pb_dicount_info.notice{background-position:bottom right;background-repeat:no-repeat;background-size:cover}div#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box strong{font-size:15px}#ays-pb-countdown-main-container li{font-size:11px}div#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-opacity-box{display:none}}@media screen and (max-width:1680px) and (min-width:1551px){div#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box{width:29%}div#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-countdown-box{width:30%}}@media screen and (max-width:1550px) and (min-width:1400px){div#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box{width:31%}div#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-countdown-box{width:35%}}@media screen and (max-width:1400px) and (min-width:1200px){div#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-countdown-box{width:35%}div#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box{width:40%}div#ays-pb-countdown-main-container li span{font-size:30px;min-height:50px;min-width:50px}}@media screen and (max-width:1200px) and (min-width:769px){div#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-countdown-box{width:45%}div#ays-pb-new-mega-bundle-2025-dicount-month-main .ays-pb-dicount-wrap-box.ays-pb-dicount-wrap-text-box{width:35%}div#ays-pb-countdown-main-container li span{font-size:30px;min-height:50px;min-width:50px}}

                    @media all and (max-width: 768px) {
                        div#ays-pb-new-mega-bundle-2025-dicount-month-main.ays_pb_dicount_info.notice {
                            display: none !important;
                        }
                    }

                ';
                $content[] = '</style>';
            $content[] = '</div>';

            $content = implode( '', $content );
            // echo wp_kses_post($content);
            echo ($content);
        }
    }


    // Christmas Top Banner 2024
    public function ays_pb_christmas_top_message_2024($ishmar){
        if($ishmar == 0 ){
            $content = array();

            $content[] = '<div id="ays-pb-christmas-top-bundle-dicount-month-main" class="notice notice-success is-dismissible ays_pb_dicount_info">';
                $content[] = '<div id="ays-pb-dicount-month" class="ays_pb_dicount_month">';

                    $content[] = '<div class="ays-pb-dicount-wrap-box ays-pb-dicount-wrap-countdown-box">';

                        $content[] = '<div id="ays-pb-countdown-main-container">';
                            $content[] = '<div class="ays-pb-countdown-container">';

                                $content[] = '<div id="ays-pb-countdown">';

                                    $content[] = '<div>';
                                        $content[] = esc_html__( "Offer ends in:", "ays-popup-box" );
                                    $content[] = '</div>';

                                    $content[] = '<ul>';
                                        $content[] = '<li><span id="ays-pb-countdown-days"></span>'. esc_html__( "Days", "ays-popup-box" ) .'</li>';
                                        $content[] = '<li><span id="ays-pb-countdown-hours"></span>'. esc_html__( "Hours", "ays-popup-box" ) .'</li>';
                                        $content[] = '<li><span id="ays-pb-countdown-minutes"></span>'. esc_html__( "Minutes", "ays-popup-box" ) .'</li>';
                                        $content[] = '<li><span id="ays-pb-countdown-seconds"></span>'. esc_html__( "Seconds", "ays-popup-box" ) .'</li>';
                                    $content[] = '</ul>';
                                $content[] = '</div>';

                                $content[] = '<div id="ays-pb-countdown-content" class="emoji">';
                                    $content[] = '<span>🚀</span>';
                                    $content[] = '<span>⌛</span>';
                                    $content[] = '<span>🔥</span>';
                                    $content[] = '<span>💣</span>';
                                $content[] = '</div>';

                            $content[] = '</div>';
                        $content[] = '</div>';
                            
                    $content[] = '</div>';

                    $content[] = '<div class="ays-pb-dicount-wrap-box ays-pb-dicount-wrap-text-box">';
                        $content[] = '<div>';

                            $content[] = '<span class="ays-pb-christmas-top-bundle-title">';
                                $content[] = '<span>';
                                    $content[] = sprintf('<a href="https://ays-pro.com/wordpress/popup-box?utm_source=dashboard&utm_medium=popup-free&utm_campaign=christmas-sale-banner%s" class="ays-pb-christmas-top-bundle-title-link" target="_blank">', AYS_PB_NAME_VERSION);
                                        $content[] = esc_html__( "Christmas Sale", "ays-popup-box" );
                                    $content[] = '</a>';
                                $content[] = '</span>';
                            $content[] = '</span>';

                            $content[] = '</br>';

                            $content[] = '<span class="ays-pb-christmas-top-bundle-desc">';
                                $content[] = sprintf('<a class="ays-pb-christmas-top-bundle-desc" href="https://ays-pro.com/wordpress/popup-box?utm_source=dashboard&utm_medium=popup-free&utm_campaign=christmas-sale-banner%s" class="ays-pb-christmas-top-bundle-title-link" target="_blank">', AYS_PB_NAME_VERSION);
                                    $content[] = esc_html__( "20% Extra OFF", "ays-popup-box" );
                                $content[] = '</a>';
                            $content[] = '</span>';
                        $content[] = '</div>';

                        $content[] = '<div style="position: absolute;right: 10px;bottom: 1px;" class="ays-pb-dismiss-buttons-container-for-form">';

                            $content[] = '<form action="" method="POST">';
                                $content[] = '<div id="ays-pb-dismiss-buttons-content">';
                                if( current_user_can( 'manage_options' ) ){
                                    $content[] = '<button class="btn btn-link ays-button" name="ays_pb_sale_btn" style="height: 32px; margin-left: 0;padding-left: 0">'. esc_html__( "Dismiss ad", "ays-popup-box" ) .'</button>';
                                    $content[] = wp_nonce_field( AYS_PB_NAME . '-sale-banner' ,  AYS_PB_NAME . '-sale-banner' );
                                }
                                $content[] = '</div>';
                            $content[] = '</form>';
                            
                        $content[] = '</div>';

                    $content[] = '</div>';

                    $content[] = '<div class="ays-pb-dicount-wrap-box ays-pb-christmas-top-bundle-coupon-text-box">';
                        $content[] = '<div class="ays-pb-christmas-top-bundle-coupon-row">';
                            $content[] = 'xmas20off';
                        $content[] = '</div>';

                        $content[] = '<div class="ays-pb-christmas-top-bundle-text-row">';
                            $content[] = esc_html__( '20% Extra Discount Coupon', "ays-popup-box" );
                        $content[] = '</div>';
                    $content[] = '</div>';

                    $content[] = '<div class="ays-pb-dicount-wrap-box ays-pb-dicount-wrap-button-box">';
                        $content[] = sprintf('<a href="https://ays-pro.com/wordpress/popup-box?utm_source=dashboard&utm_medium=popup-free&utm_campaign=christmas-sale-banner%s" class="button button-primary ays-button" id="ays-button-top-buy-now" target="_blank">', AYS_PB_NAME_VERSION);
                            $content[] =  esc_html__( 'Get Your Deal', "ays-popup-box" );
                        $content[] =  '</a>';
                        $content[] = '<span class="ays-pb-dicount-one-time-text">';
                            $content[] = esc_html__( "One-time payment", "ays-popup-box" );
                        $content[] = '</span>';
                    $content[] = '</div>';
                $content[] = '</div>';
            $content[] = '</div>';

            $content = implode( '', $content );
            echo $content;
        }
    }

    public function ays_pb_new_banner_message_2024($ishmar) {
        if ($ishmar == 0) {
            $content = array();

            $content[] = '<div id="ays-pb-new-pb-banner-dicount-month-main-2024" class="notice notice-success is-dismissible ays_pb_dicount_info">';
                $content[] = '<div id="ays-pb-dicount-month" class="ays_pb_dicount_month">';

                    $content[] = '<div class="ays-pb-discount-box-sale-image"></div>';
                    $content[] = '<div class="ays-pb-dicount-wrap-box ays-pb-dicount-wrap-text-box">';

                        $content[] = '<div class="ays-pb-dicount-wrap-text-box-texts">';
                            $content[] = '<div>
                                            <a href="https://ays-pro.com/wordpress/popup-box?utm_source=dashboard&utm_medium=popup-free&utm_campaign=sale-banner-30" target="_blank" style="color:#30499B;">
                                            <span class="ays-pb-new-pb-banner-limited-text">Limited</span> Offer for Popup Box</a><br>
                                          </div>';
                        $content[] = '</div>';

                        $content[] = '<div style="font-size: 17px;">';
                            $content[] = '<img style="width: 24px;height: 24px;" src="' . esc_attr(AYS_PB_ADMIN_URL) . '/images/icons/guarantee-new.png">';
                            $content[] = '<span style="padding-left: 4px; font-size: 14px; font-weight: 600;"> 30 Day Money Back Guarantee</span>';
                            
                        $content[] = '</div>';

                       

                        $content[] = '<div style="position: absolute;right: 10px;bottom: 1px;" class="ays-pb-dismiss-buttons-container-for-pb">';

                            $content[] = '<form action="" method="POST">';
                                $content[] = '<div id="ays-pb-dismiss-buttons-content">';
                                    if( current_user_can( 'manage_options' ) ){
                                        $content[] = '<button class="btn btn-link ays-button" name="ays_pb_sale_btn" style="height: 32px; margin-left: 0;padding-left: 0; color: #30499B;
                                        ">Dismiss ad</button>';
                                        $content[] = wp_nonce_field( AYS_PB_NAME . '-sale-banner' ,  AYS_PB_NAME . '-sale-banner' );
                                    }
                                $content[] = '</div>';
                            $content[] = '</form>';
                            
                        $content[] = '</div>';

                    $content[] = '</div>';

                    $content[] = '<div class="ays-pb-dicount-wrap-box ays-pb-dicount-wrap-countdown-box">';

                        $content[] = '<div id="ays-pb-countdown-main-container">';
                            $content[] = '<div class="ays-pb-countdown-container">';

                                $content[] = '<div id="ays-pb-countdown">';

                                    $content[] = '<div style="font-weight: 500;">';
                                        $content[] = esc_html__( "Offer ends in:", "ays-popup-box" );
                                    $content[] = '</div>';

                                    $content[] = '<ul>';
                                        $content[] = '<li><span id="ays-pb-countdown-days"></span>days</li>';
                                        $content[] = '<li><span id="ays-pb-countdown-hours"></span>Hours</li>';
                                        $content[] = '<li><span id="ays-pb-countdown-minutes"></span>Minutes</li>';
                                        $content[] = '<li><span id="ays-pb-countdown-seconds"></span>Seconds</li>';
                                    $content[] = '</ul>';
                                $content[] = '</div>';

                                $content[] = '<div id="ays-pb-countdown-content" class="emoji">';
                                    $content[] = '<span>🚀</span>';
                                    $content[] = '<span>⌛</span>';
                                    $content[] = '<span>🔥</span>';
                                    $content[] = '<span>💣</span>';
                                $content[] = '</div>';

                            $content[] = '</div>';
                        $content[] = '</div>';
                            
                    $content[] = '</div>';

                    $content[] = '<div class="ays-pb-dicount-wrap-box ays-pb-dicount-wrap-button-box">';
                        $content[] = '<a href="https://ays-pro.com/wordpress/popup-box?utm_source=dashboard&utm_medium=popup-free&utm_campaign=sale-banner-30" class="button button-primary ays-button" id="ays-button-top-buy-now" target="_blank">' . esc_html__( 'Buy Now !', "ays-popup-box" ) . '</a>';
                        $content[] = '<span >One-time payment</span>';
                    $content[] = '</div>';
                $content[] = '</div>';
            $content[] = '</div>';

            $content = implode('', $content);
            echo html_entity_decode( esc_html($content) );
        }
    }

    public static function check_user_capability(){
        return current_user_can( 'manage_options' ) && is_user_logged_in();
    }

    public static function ays_pb_is_elementor_editor_active() {
        if ( isset($_GET['action']) && $_GET['action'] == 'elementor' ) {
            $is_elementor = true;
        } elseif ( isset($_REQUEST['elementor-preview']) && $_REQUEST['elementor-preview'] != '' ) {
            $is_elementor = true;
        } else {
            $is_elementor = false;
        }

        if (!$is_elementor) {
            $is_elementor = ( isset($_REQUEST['action']) && $_REQUEST['action'] == 'elementor_ajax' ) ? true : false;
        }

        return $is_elementor;
    }

      // AYS Popup Box License Banner
    public function ays_pb_discounted_licenses_banner_message($ishmar){
        if($ishmar == 0 ){
            $content = array();

            $date = time() + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS);
            $now_date = date('M d, Y H:i:s', $date);

            $start_date = strtotime('2025-09-08');
            $end_date = strtotime('2025-09-30');
            $diff_end = $end_date - $date;

            $style_attr = '';
            if( $diff_end < 0 ){
                $style_attr = 'style="display:none;"';
            }

            $total_licenses = 50;
            $progression_pattern = array(2, 3, 1, 4, 2, 3, 1, 3, 2, 4, 1, 2, 3, 1, 2, 3, 4, 1, 2, 1, 2, 3);
            $days_passed = floor(($date - $start_date) / (24 * 60 * 60));
            $used_licenses = 0;

            for ($i = 0; $i < min($days_passed, count($progression_pattern)); $i++) {
                $used_licenses += $progression_pattern[$i];
            }
            $used_licenses = min($used_licenses, $total_licenses);
            $remaining_licenses = $total_licenses - $used_licenses;
            $progress_percentage = ($used_licenses / $total_licenses) * 100;

            $cta_button_link = esc_url('https://popup-plugin.com/pricing/?utm_source=dashboard&utm_medium=popup-free&utm_campaign=ays-pb-license-banner-' . AYS_PB_NAME_VERSION);

            $content[] = '<div id="ays-pb-progress-banner-main" class="ays-pb-progress-banner-main ays_quiz_dicount_info ays-pb-admin-notice notice notice-success is-dismissible" >';
                $content[] = '<div class="ays-pb-progress-banner-content">';
                    $content[] = '<div class="ays-pb-progress-banner-left">';
                        $content[] = '<div class="ays-pb-progress-banner-icon">';
                            $content[] = '<svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1.33325 22.6668L11.9999 13.3335L33.3333 14.6668L34.6666 36.0002L25.3333 46.6668C25.3333 46.6668 25.3346 38.6682 17.3333 30.6668C9.33192 22.6655 1.33325 22.6668 1.33325 22.6668Z" fill="#A0041E"/>
                                            <path d="M1.29739 46.6665C1.29739 46.6665 1.24939 36.0278 5.27739 31.9998C9.30539 27.9718 20.0001 28.2492 20.0001 28.2492C20.0001 28.2492 19.9987 38.6665 15.9987 42.6665C11.9987 46.6665 1.29739 46.6665 1.29739 46.6665Z" fill="#FFAC33"/>
                                            <path d="M11.9986 41.3332C14.9441 41.3332 17.3319 38.9454 17.3319 35.9998C17.3319 33.0543 14.9441 30.6665 11.9986 30.6665C9.0531 30.6665 6.66528 33.0543 6.66528 35.9998C6.66528 38.9454 9.0531 41.3332 11.9986 41.3332Z" fill="#FFCC4D"/>
                                            <path d="M47.9986 0C47.9986 0 34.6653 0 18.6653 13.3333C10.6653 20 10.6653 32 13.3319 34.6667C15.9986 37.3333 27.9986 37.3333 34.6653 29.3333C47.9986 13.3333 47.9986 0 47.9986 0Z" fill="#55ACEE"/>
                                            <path d="M35.9987 6.6665C33.8347 6.6665 31.9814 7.96117 31.144 9.81317C31.8134 9.5105 32.5507 9.33317 33.332 9.33317C36.2774 9.33317 38.6654 11.7212 38.6654 14.6665C38.6654 15.4478 38.488 16.1852 38.1867 16.8532C40.0387 16.0172 41.332 14.1638 41.332 11.9998C41.332 9.0545 38.944 6.6665 35.9987 6.6665Z" fill="black"/>
                                            <path d="M10.6667 37.3332C10.6667 37.3332 10.6667 31.9998 12.0001 30.6665C13.3334 29.3332 29.3347 16.0012 30.6667 17.3332C31.9987 18.6652 18.6654 34.6665 17.3321 35.9998C15.9987 37.3332 10.6667 37.3332 10.6667 37.3332Z" fill="#A0041E"/>
                                            </svg>';
                        $content[] = '</div>';
                        $content[] = '<div class="ays-pb-progress-banner-text">';
                            $content[] = '<h2 class="ays-pb-progress-banner-title">' . sprintf( __('Get the Pro Version of %s Popup Box%s – 20%% OFF', 'ays-popup-box'), '<a href="'. $cta_button_link .'" target="_blank">', '</a>' ) . '</h2>';
                            $content[] = '<p class="ays-pb-progress-banner-subtitle">' . __('Unlock advanced features + 7-Day Free Trial', 'ays-popup-box') . '</p>';
                        $content[] = '</div>';
                    $content[] = '</div>';
                    
                    $content[] = '<div class="ays-pb-progress-banner-center">';
                        $content[] = '<div class="ays-pb-progress-banner-coupon">';
                            $content[] = '<div class="ays-pb-progress-banner-coupon-box" onclick="pbCopyToClipboard(\'FREE2PRO20\')" title="' . __('Click to copy', 'ays-popup-box') . '">';
                                $content[] = '<span class="ays-pb-progress-banner-coupon-text">FREE2PRO20</span>';
                                $content[] = '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="ays-pb-progress-banner-copy-icon">';
                                    $content[] = '<path d="M13.5 2.5H6.5C5.67 2.5 5 3.17 5 4V10C5 10.83 5.67 11.5 6.5 11.5H13.5C14.33 11.5 15 10.83 15 10V4C15 3.17 14.33 2.5 13.5 2.5ZM13.5 10H6.5V4H13.5V10ZM2.5 6.5V12.5C2.5 13.33 3.17 14 4 14H10V12.5H4V6.5H2.5Z" fill="white"/>';
                                $content[] = '</svg>';
                            $content[] = '</div>';
                        $content[] = '</div>';
                        
                        $content[] = '<div class="ays-pb-progress-banner-progress" ' . $style_attr . '>';
                            $content[] = '<p class="ays-pb-progress-banner-progress-text">' . __('Only', 'ays-popup-box') . ' <span id="pb-remaining-licenses">' . $remaining_licenses . '</span> ' . __('of 50 discounted licenses left', 'ays-popup-box') . '</p>';
                            $content[] = '<div class="ays-pb-progress-banner-progress-bar" >';
                                $content[] = '<div class="ays-pb-progress-banner-progress-fill" id="pb-progress-fill" style="width: ' . $progress_percentage . '%;"></div>';
                            $content[] = '</div>';
                        $content[] = '</div>';
                    $content[] = '</div>';
                    
                    $content[] = '<div class="ays-pb-progress-banner-right">';
                        $content[] = '<a href="'. $cta_button_link .'" class="ays-pb-progress-banner-upgrade" target="_blank">';
                        $content[] = '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">';
                            $content[] = '<path d="M14.6392 6.956C14.5743 6.78222 14.4081 6.66667 14.2223 6.66667H8.85565L11.9512 0.648C12.0485 0.458667 11.9983 0.227111 11.8308 0.0955556C11.7499 0.0315556 11.6525 0 11.5556 0C11.4521 0 11.3485 0.0364444 11.2654 0.108L8.00009 2.928L1.48765 8.55244C1.3472 8.67378 1.29653 8.86978 1.36142 9.04356C1.42631 9.21733 1.59209 9.33333 1.77787 9.33333H7.14454L4.04898 15.352C3.95165 15.5413 4.00187 15.7729 4.16942 15.9044C4.25031 15.9684 4.34765 16 4.44453 16C4.54809 16 4.65165 15.9636 4.73476 15.892L8.00009 13.072L14.5125 7.44756C14.6534 7.32622 14.7036 7.13022 14.6392 6.956Z" fill="white"/>';
                        $content[] = '</svg>';
                         $content[] = ' ' . __('Upgrade Now', 'ays-popup-box');
                        $content[] = '</a>';
                    $content[] = '</div>';
                $content[] = '</div>';
                
                if( current_user_can( 'manage_options' ) ){
                $content[] = '<div id="ays-pb-dismiss-buttons-content">';
                    $content[] = '<form action="" method="POST" style="position: absolute; bottom: 0; right: 0; color: #fff;">';
                            $content[] = '<button class="btn btn-link ays-button" name="ays_quiz_sale_btn" style="color: darkgrey; font-size: 11px;">'. __( "Dismiss ad", 'ays-popup-box' ) .'</button>';
                            $content[] = wp_nonce_field( AYS_PB_NAME . '-sale-banner' ,  AYS_PB_NAME . '-sale-banner' );
                    $content[] = '</form>';
                $content[] = '</div>';
                }
            $content[] = '</div>';

            // AYS Popup Box Pro Banner Styles
            $content[] = '<style id="ays-pb-progress-banner-styles-inline-css">';
            $content[] = '
                .ays-pb-progress-banner-main {
                    background: linear-gradient(135deg, #6344ED 0%, #8C2ABE 100%);
                    padding: 20px 30px;
                    border-radius: 16px;
                    color: white;
                    position: relative;
                    margin: 20px 0;
                    box-shadow: 0 8px 32px rgba(99, 68, 237, 0.3);
                    border: 0;
                }

                .ays-pb-progress-banner-main .ays-pb-progress-banner-content {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 30px;
                }

                .ays-pb-progress-banner-main .ays-pb-progress-banner-left {
                    display: flex;
                    align-items: center;
                    gap: 20px;
                    flex: 1;
                }

                .ays-pb-progress-banner-main .ays-pb-progress-banner-center {
                    display: flex;
                    align-items: center;
                    gap: 15px;
                    flex: 1;
                }

                .ays-pb-progress-banner-main .ays-pb-progress-banner-right {
                    display: flex;
                    align-items: center;
                    gap: 20px;
                    flex-shrink: 0;
                }

                .ays-pb-progress-banner-main .ays-pb-progress-banner-icon {
                    font-size: 32px;
                    filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.2));
                }

                .ays-pb-progress-banner-main .ays-pb-progress-banner-title {
                    font-size: 21px;
                    font-weight: 700;
                    margin: 0 0 8px 0;
                    line-height: 1.2;
                    color: #fff;
                }

                .ays-pb-progress-banner-main .ays-pb-progress-banner-title a {
                    text-decoration: underline;
                    color: #fff;
                }

                .ays-pb-progress-banner-main .ays-pb-progress-banner-subtitle {
                    font-size: 16px;
                    margin: 0;
                    opacity: 0.9;
                    font-weight: 400;
                }

                .ays-pb-progress-banner-main .ays-pb-progress-banner-coupon {
                    margin-bottom: 5px;
                }

                .ays-pb-progress-banner-main .ays-pb-progress-banner-coupon-box {
                    border: 2px dotted rgba(255, 255, 255, 0.6);
                    padding: 8px 16px;
                    border-radius: 8px;
                    background: rgba(255, 255, 255, 0.1);
                    cursor: pointer;
                    transition: all 0.3s ease;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    backdrop-filter: blur(10px);
                }

                .ays-pb-progress-banner-main .ays-pb-progress-banner-coupon-box:hover {
                    background: rgba(255, 255, 255, 0.2);
                    border-color: rgba(255, 255, 255, 0.8);
                    transform: translateY(-1px);
                }

                .ays-pb-progress-banner-main .ays-pb-progress-banner-coupon-text {
                    font-size: 16px;
                    font-weight: 700;
                    letter-spacing: 1px;
                    color: #fff;
                    font-family: monospace;
                }

                .ays-pb-progress-banner-main .ays-pb-progress-banner-copy-icon {
                    opacity: 0.8;
                    transition: opacity 0.3s ease;
                }

                .ays-pb-progress-banner-main .ays-pb-progress-banner-coupon-box:hover .ays-pb-progress-banner-copy-icon {
                    opacity: 1;
                }

                .ays-pb-progress-banner-main .ays-pb-progress-banner-progress {
                    text-align: center;
                    width: 100%;
                }

                .ays-pb-progress-banner-main .ays-pb-progress-banner-progress-text {
                    font-size: 14px;
                    margin: 0 0 10px 0;
                    opacity: 0.9;
                }

                .ays-pb-progress-banner-main .ays-pb-progress-banner-progress-bar {
                    width: 300px;
                    height: 10px;
                    background: rgba(255, 255, 255, 0.2);
                    border-radius: 4px;
                    overflow: hidden;
                    margin: 0 auto;
                }

                .ays-pb-progress-banner-main .ays-pb-progress-banner-progress-fill {
                    height: 100%;
                    background: linear-gradient(90deg, #4ADE80 0%, #22C55E 100%);
                    border-radius: 4px;
                    transition: width 0.8s ease;
                    width: 70%;
                }

                .ays-pb-progress-banner-main .ays-pb-progress-banner-upgrade {
                    background: linear-gradient(135deg, #F59E0B 0%, #F97316 100%);
                    color: white;
                    border: none;
                    padding: 12px 24px;
                    border-radius: 8px;
                    font-size: 16px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    box-shadow: 0 4px 16px rgba(245, 158, 11, 0.4);
                    text-decoration: none;
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                }

                .ays-pb-progress-banner-main .ays-pb-progress-banner-upgrade:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 6px 20px rgba(245, 158, 11, 0.6);
                    text-decoration: none;
                    color: white;
                }

                .ays-pb-progress-banner-main .notice-dismiss:before {
                    color: #fff;
                }

                /* Copy notification */
                .ays-pb-copy-notification {
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: rgba(0, 0, 0, 0.8);
                    color: white;
                    padding: 12px 24px;
                    border-radius: 8px;
                    font-size: 14px;
                    z-index: 10000;
                    opacity: 0;
                    transition: opacity 0.3s ease;
                }

                .ays-pb-copy-notification.show {
                    opacity: 1;
                }

                @media (max-width: 1400px) {
                    .ays-pb-progress-banner-main .ays-pb-progress-banner-center {
                        flex-direction: column;
                    }
                }

                @media (max-width: 1200px) {
                    .ays-pb-progress-banner-main .ays-pb-progress-banner-content {
                        flex-direction: column;
                        gap: 20px;
                    }

                    .ays-pb-progress-banner-main .ays-pb-progress-banner-left {
                        width: 100%;
                        justify-content: center;
                        text-align: center;
                        flex-direction: column;
                    }

                    .ays-pb-progress-banner-main .ays-pb-progress-banner-center {
                        width: 100%;
                    }

                    .ays-pb-progress-banner-main .ays-pb-progress-banner-right {
                        width: 100%;
                        justify-content: center;
                    }
                }

                @media (max-width: 768px) {
                    #ays-pb-progress-banner-main {
                        display: none !important;
                    }

                    .ays-pb-progress-banner-main {
                        padding: 15px 20px;
                        margin: 15px 0;
                    }
                    
                    .ays-pb-progress-banner-main .ays-pb-progress-banner-title {
                        font-size: 18px;
                    }
                    
                    .ays-pb-progress-banner-main .ays-pb-progress-banner-subtitle {
                        font-size: 14px;
                    }
                    
                    .ays-pb-progress-banner-main .ays-pb-progress-banner-progress-bar {
                        width: 100%;
                        max-width: 280px;
                    }
                    
                    .ays-pb-progress-banner-main .ays-pb-progress-banner-upgrade {
                        padding: 10px 20px;
                        font-size: 14px;
                    }
                }

                @media (max-width: 480px) {
                    .ays-pb-progress-banner-main {
                        padding: 12px 15px;
                    }
                    
                    .ays-pb-progress-banner-main .ays-pb-progress-banner-coupon-text {
                        font-size: 14px;
                    }
                    
                    .ays-pb-progress-banner-main .ays-pb-progress-banner-progress-bar {
                        max-width: 250px;
                    }
                }
            ';

            $content[] = '</style>';

            $content = implode( '', $content );
            echo ($content);
        }
    }
}
