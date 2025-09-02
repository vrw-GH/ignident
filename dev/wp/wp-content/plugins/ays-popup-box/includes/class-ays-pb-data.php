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
                        $this->ays_pb_new_banner_message($ays_popup_box_flag);
                    }
                }
            }
        }
    }

    public static function ays_pb_winter_bundle_message($ishmar){
        if($ishmar == 0 ){
            $content = array();

            $content[] = '<div id="ays-pb-dicount-month-main" class="notice notice-success is-dismissible ays_pb_dicount_info">';
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
    public static function ays_pb_black_friday_message($ishmar){
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


    // New Mega Bundle
    public function ays_pb_new_mega_bundle_message_2025($ishmar){
        if($ishmar == 0 ){
            $content = array();

            $pb_cta_button_link = sprintf('https://ays-pro.com/essential-bundle?utm_source=dashboard&utm_medium=popup-free&utm_campaign=sale-banner-%s', AYS_PB_NAME_VERSION);

            $content[] = '<div id="ays-pb-new-mega-bundle-2025-dicount-month-main" class="notice notice-success is-dismissible ays_pb_dicount_info">';
                $content[] = '<div id="ays-pb-dicount-month" class="ays_pb_dicount_month">';
                    $content[] = '<div class="ays-pb-dicount-wrap-box ays-pb-dicount-wrap-text-box">';
                        $content[] = '<div>';

                            $content[] = '<span class="ays-pb-new-mega-bundle-2025-title">';
                                /* translators: %s: link to Essential Bundle and %s: (Quiz + Form + Popup) */
                                $content[] = sprintf(' <a href="%s" target="_blank" style="color:#ffffff; text-decoration: underline;">Essential Bundle</a> ( %s )', esc_url($pb_cta_button_link), esc_html__( "Quiz + Form + Popup", "ays-popup-box" ));
                            $content[] = '</span>';
                            $content[] = '</br>';

                            $content[] = '<span class="ays-pb-new-mega-bundle-2025-desc">';
                                $content[] = __( "30 Day Money Back Guarantee", 'ays-popup-box' );
                            $content[] = '</span>';
                        $content[] = '</div>';
                        $content[] = '<div>';
                                $content[] = '<img class="ays-pb-new-mega-bundle-guaranteeicon" src="' . AYS_PB_ADMIN_URL . '/images/ays-pb-essential-bundle-2025-discount.svg" style="width: 80px;">';
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
            $content[] = '</div>';

            $content = implode( '', $content );
            echo wp_kses_post($content);
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
}
