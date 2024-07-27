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

    public static function get_popups(){
        global $wpdb;
        $popups_table = esc_sql( $wpdb->prefix . 'ays_pb' );

        $sql = "SELECT id,title
                FROM {$popups_table}";

        $popups = $wpdb->get_results( $sql , "ARRAY_A" );

        return $popups;
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
                    $this->ays_pb_new_banner_message_2024($ays_popup_box_flag);
                }
            }
        }
    }

    // public static function ays_pb_sale_message($ishmar){
    //     if($ishmar == 0 ){
    //         $content = array();

    //         $content[] = '<div id="ays-pb-dicount-month-main" class="notice notice-success is-dismissible ays_pb_dicount_info">';
    //             $content[] = '<div id="ays-pb-dicount-month" class="ays_pb_dicount_month">';
    //                 $content[] = '<a href="https://ays-pro.com/wordpress/popup-box" target="_blank" class="ays-pb-sale-banner-link"><img src="' . AYS_PB_ADMIN_URL . '/images/helloween_sale.png"></a>';

    //                 $content[] = '<div class="ays-pb-dicount-wrap-box">';

    //                     $content[] = '<strong>';
    //                         $content[] = __( "Pre-Halloween big sale on Popup Box plugin to spice up your website and prepare for the spooky season!<br><span style='color:#E85011;'>31%</span> SALE on <span style='color:#E85011;'>Popup Box</span> PRO!", AYS_PB_NAME );
    //                     $content[] = '</strong>';

    //                     $content[] = '<br>';

    //                     $content[] = '<strong>';
    //                             $content[] = __( "Hurry up! Ends on October 31. <a href='https://ays-pro.com/wordpress/popup-box' target='_blank'>Check it out!</a>", AYS_PB_NAME );
    //                     $content[] = '</strong>';
                        
    //                     $content[] = '<form action="" method="POST">';
    //                         $content[] = '<button class="btn btn-link ays-button" name="ays_pb_sale_btn" style="height: 32px; margin-left: 0;padding-left: 0">Dismiss ad</button>';
    //                     $content[] = '</form>';
                            
    //                 $content[] = '</div>';

    //                 $content[] = '<div class="ays-pb-dicount-wrap-box">';

    //                     $content[] = '<div id="ays-pb-countdown-main-container">';
    //                         $content[] = '<div class="ays-pb-countdown-container">';

    //                             $content[] = '<div id="ays-pb-countdown">';
    //                                 $content[] = '<ul>';
    //                                     $content[] = '<li><span id="ays-pb-countdown-days"></span>days</li>';
    //                                     $content[] = '<li><span id="ays-pb-countdown-hours"></span>Hours</li>';
    //                                     $content[] = '<li><span id="ays-pb-countdown-minutes"></span>Minutes</li>';
    //                                     $content[] = '<li><span id="ays-pb-countdown-seconds"></span>Seconds</li>';
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

    //                 $content[] = '<a href="https://ays-pro.com/wordpress/popup-box" class="button button-primary ays-button" id="ays-button-top-buy-now" target="_blank" style="height: 32px; display: flex; align-items: center; font-weight: 500; " >' . __( 'Buy Now !', AYS_PB_NAME ) . '</a>';
    //             $content[] = '</div>';
    //         $content[] = '</div>';

    //         $content = implode( '', $content );
    //         echo $content;
    //     }
    // }

    public static function ays_pb_winter_bundle_message($ishmar){
        if($ishmar == 0 ){
            $content = array();

            $content[] = '<div id="ays-pb-dicount-month-main" class="notice notice-success is-dismissible ays_pb_dicount_info">';
                $content[] = '<div id="ays-pb-dicount-month" class="ays_pb_dicount_month">';
                    $content[] = '<a href="https://ays-pro.com/winter-bundle" target="_blank" class="ays-pb-sale-banner-link"><img src="' . AYS_PB_ADMIN_URL . '/images/winter_bundle_logo.png"></a>';

                    $content[] = '<div class="ays-pb-dicount-wrap-box">';

                        $content[] = '<strong>';
                            $content[] = __( "Limited Time <span class='ays-pb-dicount-wrap-color'>50%</span> SALE on <br><span><a href='https://ays-pro.com/winter-bundle' target='_blank' class='ays-pb-dicount-wrap-color ays-pb-dicount-wrap-text-decoration' style='display:block;'>Winter Bundle</a></span> (Copy + Popup + Survey)!", "ays-popup-box" );
                        $content[] = '</strong>';

                        $content[] = '<br>';

                        $content[] = '<strong>';
                                $content[] = __( "Hurry up! Ending on. <a href='https://ays-pro.com/winter-bundle' target='_blank'>Check it out!</a>", "ays-popup-box" );
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

                    $content[] = '<a href="https://ays-pro.com/winter-bundle" class="button button-primary ays-button" id="ays-button-top-buy-now" target="_blank">' . __( 'Buy Now !', "ays-popup-box" ) . '</a>';
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
                                $content[] = __( "Spring is here! 
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
                            //         $content[] = __( "Hurry up! Ending on. <a href='https://ays-pro.com/spring-bundle' target='_blank'>Check it out!</a>", "ays-popup-box" );
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

                    $content[] = '<a href="https://ays-pro.com/spring-bundle" class="button button-primary ays-button" id="ays-button-top-buy-now" target="_blank">' . __( 'Buy Now !', "ays-popup-box" ) . '</a>';
                $content[] = '</div>';
            $content[] = '</div>';

            $content = implode( '', $content );
            echo $content;
        }
    }

    // public static function ays_pb_sale_message($ishmar){
    //     if($ishmar == 0 ){
    //         $content = array();

    //         $content[] = '<div id="ays-pb-dicount-month-main" class="notice notice-success is-dismissible ays_pb_dicount_info">';
    //             $content[] = '<div id="ays-pb-dicount-month" class="ays_pb_dicount_month">';
    //                 $content[] = '<a href="https://ays-pro.com/wordpress/popup-box" target="_blank" class="ays-pb-sale-banner-link" ><img src="' . AYS_PB_ADMIN_URL . '/images/icons/icon-popup-128x128.png"></a>';

    //                 $content[] = '<div class="ays-pb-dicount-wrap-box">';

    //                     $content[] = '<strong style="font-weight: bold;">';
    //                         $content[] = __( "Limited Time <span class='ays-pb-dicount-wrap-color'>20%</span> SALE on <br><span><a href='https://ays-pro.com/wordpress/popup-box' target='_blank' class='ays-pb-dicount-wrap-color ays-pb-dicount-wrap-text-decoration' style='display:block;'>Popup Box Premium Versions</a></span>", AYS_PB_NAME );
    //                     $content[] = '</strong>';

    //                     // $content[] = '<br>';

    //                     $content[] = '<strong>';
    //                             $content[] = __( "Hurry up! <a href='https://ays-pro.com/wordpress/popup-box' target='_blank'>Check it out!</a>", AYS_PB_NAME );
    //                     $content[] = '</strong>';
                            
    //                 $content[] = '</div>';

    //                 $content[] = '<div class="ays-pb-dicount-wrap-box">';

    //                     $content[] = '<div id="ays-pb-countdown-main-container">';
    //                         $content[] = '<div class="ays-pb-countdown-container">';

    //                             $content[] = '<div id="ays-pb-countdown">';
    //                                 $content[] = '<ul>';
    //                                     $content[] = '<li><span id="ays-pb-countdown-days"></span>days</li>';
    //                                     $content[] = '<li><span id="ays-pb-countdown-hours"></span>Hours</li>';
    //                                     $content[] = '<li><span id="ays-pb-countdown-minutes"></span>Minutes</li>';
    //                                     $content[] = '<li><span id="ays-pb-countdown-seconds"></span>Seconds</li>';
    //                                 $content[] = '</ul>';
    //                             $content[] = '</div>';

    //                             $content[] = '<div id="ays-pb-countdown-content" class="emoji">';
    //                                 $content[] = '<span>🚀</span>';
    //                                 $content[] = '<span>⌛</span>';
    //                                 $content[] = '<span>🔥</span>';
    //                                 $content[] = '<span>💣</span>';
    //                             $content[] = '</div>';

    //                         $content[] = '</div>';

    //                         // $content[] = '<form action="" method="POST">';
    //                         //     $content[] = '<div id="ays-pb-dismiss-buttons-content">';
    //                         //         $content[] = '<button class="btn btn-link ays-button" name="ays_pb_sale_btn" style="height: 32px; margin-left: 0;padding-left: 0">Dismiss ad</button>';
    //                         //         $content[] = '<button class="btn btn-link ays-button" name="ays_pb_sale_btn_for_two_months" style="height: 32px; padding-left: 0">Dismiss ad for 2 months</button>';
    //                         //     $content[] = '</div>';
    //                         // $content[] = '</form>';

    //                     $content[] = '</div>';
                            
    //                 $content[] = '</div>';

    //                 $content[] = '<div class="ays-pb-dicount-wrap-box ays-buy-now-button-box">';
    //                     $content[] = '<a href="https://ays-pro.com/wordpress/popup-box" class="button button-primary ays-buy-now-button" id="ays-button-top-buy-now" target="_blank" style="" >' . __( 'Buy Now !', AYS_PB_NAME ) . '</a>';
    //                 $content[] = '</div>';

    //                 // $content[] = '<div class="ays-pb-dicount-wrap-box ays-pb-dicount-wrap-opacity-box">';
    //                 //     $content[] = '<a href="https://ays-pro.com/great-bundle" class="ays-buy-now-opacity-button" target="_blank">' . __( 'link', AYS_pb_NAME ) . '</a>';
    //                 // $content[] = '</div>';

    //             $content[] = '</div>';

    //             $content[] = '<div style="position: absolute;right: 0;bottom: 1px;" class="ays-pb-dismiss-buttons-container-for-form">';
    //                 $content[] = '<form action="" method="POST">';
    //                     $content[] = '<div id="ays-pb-dismiss-buttons-content">';
    //                         $content[] = '<button class="btn btn-link ays-button" name="ays_pb_sale_btn" style="height: 32px; margin-left: 0;padding-left: 0; color: #979797;">Dismiss ad</button>';
    //                     $content[] = '</div>';
    //                 $content[] = '</form>';
    //             $content[] = '</div>';

    //         $content[] = '</div>';

    //         $content = implode( '', $content );
    //         echo $content;
    //     }
    // }

    public static function ays_pb_helloween_message($ishmar){
        if($ishmar == 0 ){
            $content = array();

            $content[] = '<div id="ays-pb-dicount-month-main-helloween" class="notice notice-success is-dismissible ays_pb_dicount_info">';
                $content[] = '<div id="ays-pb-dicount-month-helloween" class="ays_pb_dicount_month_helloween">';
                    $content[] = '<div class="ays-pb-dicount-wrap-box-helloween-limited">';

                        $content[] = '<p>';
                            $content[] = __( "Limited Time 
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
                                $content[] = __( "Hurry up! 
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
                            $content[] = '<a href="https://ays-pro.com/wordpress/popup-box?utm_source=dashboard&utm_medium=popup-free&utm_campaign=helloween-sale-banner" class="button button-primary ays-buy-now-button-helloween" id="ays-button-top-buy-now-helloween" target="_blank" style="" >' . __( 'Buy Now !', "ays-popup-box" ) . '</a>';
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
                            $content[] = '<div class="ays-pb-dicount-black-friday-title-row">' . __( 'Limited Time', "ays-popup-box" ) .' '. '<a href="https://ays-pro.com/essential-bundle?utm_source=dashboard&utm_medium=popup-free&utm_campaign=black-friday-sale-banner" class="ays-pb-dicount-black-friday-button-sale" target="_blank">' . __( 'Sale', "ays-popup-box" ) . '</a>' . '</div>';
                            $content[] = '<div class="ays-pb-dicount-black-friday-title-row ays-pb-dicount-black-friday-title-row-product"><span>' . __( 'Essential Bundle', "ays-popup-box" ) . '</span><span>' . __( '( Quiz + Form + Popup )', "ays-popup-box" ) .'</span></div>';
                        $content[] = '</div>';

                        $content[] = '<div class="ays-pb-dicount-black-friday-wrap-box ays-pb-dicount-black-friday-wrap-text-box">';
                            $content[] = '<div class="ays-pb-dicount-black-friday-text-row">' . __( '50% off', "ays-popup-box" ) . '</div>';
                        $content[] = '</div>';

                        $content[] = '<div class="ays-pb-dicount-black-friday-wrap-box" style="width: 25%;">';
                            $content[] = '<div id="ays-pb-countdown-main-container">';
                                $content[] = '<div class="ays-pb-countdown-container">';
                                    $content[] = '<div id="ays-pb-countdown" style="display: block;">';
                                        $content[] = '<ul>';
                                            $content[] = '<li><span id="ays-pb-countdown-days">0</span>' . __( 'Days', "ays-popup-box" ) . '</li>';
                                            $content[] = '<li><span id="ays-pb-countdown-hours">0</span>' . __( 'Hours', "ays-popup-box" ) . '</li>';
                                            $content[] = '<li><span id="ays-pb-countdown-minutes">0</span>' . __( 'Minutes', "ays-popup-box" ) . '</li>';
                                            $content[] = '<li><span id="ays-pb-countdown-seconds">0</span>' . __( 'Seconds', "ays-popup-box" ) . '</li>';
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
                            $content[] = '<a href="https://ays-pro.com/essential-bundle?utm_source=dashboard&utm_medium=popup-free&utm_campaign=black-friday-sale-banner" class="ays-pb-dicount-black-friday-button-buy-now" target="_blank">' . __( 'Get Your Deal', "ays-popup-box" ) . '</a>';
                        $content[] = '</div>';
                    $content[] = '</div>';
                $content[] = '</div>';

                $content[] = '<div style="position: absolute;right: 0;bottom: 1px;"  class="ays-pb-dismiss-buttons-container-for-form-black-friday">';
                    $content[] = '<form action="" method="POST">';
                        $content[] = '<div id="ays-pb-dismiss-buttons-content-black-friday">';
                            if( current_user_can( 'manage_options' ) ){
                                $content[] = '<button class="btn btn-link ays-button-black-friday" name="ays_pb_sale_btn" style="">' . __( 'Dismiss ad', "ays-popup-box" ) . '</button>';
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
                    $content[] = '<a href="https://ays-pro.com/wordpress/popup-box" target="_blank" class="ays-pb-engagement-sale-banner-link"><img src="' . AYS_PB_ADMIN_URL . '/images/icons/icon-popup-128x128.png"></a>';

                    $content[] = '<div class="ays-pb-engagement-dicount-wrap-box">';

                        $content[] = '<strong style="font-weight: bold;">';
                            $content[] = __( "Limited Time <span style='color:#E85011;'>20%</span> SALE on <span><a href='https://ays-pro.com/wordpress/popup-box' target='_blank' style='color:#E85011; text-decoration: underline;'>Popup Box</a></span>", "ays-popup-box" );
                        $content[] = '</strong>';

                        $content[] = '<br>';

                        $content[] = '<strong>';
                                $content[] = __( "Hurry up! <a href='https://ays-pro.com/wordpress/popup-box' target='_blank'>Check it out!</a>", "ays-popup-box" );
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

                    $content[] = '<a href="https://ays-pro.com/wordpress/popup-box" class="button button-primary ays-button" id="ays-button-top-buy-now" target="_blank" style="height: 32px; display: flex; align-items: center; font-weight: 500; " >' . __( 'Buy Now !', "ays-popup-box" ) . '</a>';
                $content[] = '</div>';
            $content[] = '</div>';

            $content = implode( '', $content );
            echo $content;
        }
    } 
    
    // New Sale banner
    public function ays_pb_new_banner_message($ishmar){
        if($ishmar == 0 ){
            $content = array();

            $content[] = '<div id="ays-pb-new-mega-bundle-dicount-month-main" class="notice notice-success is-dismissible ays_pb_dicount_info">';
                $content[] = '<div id="ays-pb-dicount-month" class="ays_pb_dicount_month">';
                    // $content[] = '<a href="https://ays-pro.com/mega-bundle" target="_blank" class="ays-pb-sale-banner-link"><img src="' . AYS_pb_ADMIN_URL . '/images/mega_bundle_logo_box.png"></a>';

                    $content[] = '<div class="ays-pb-dicount-wrap-box ays-pb-dicount-wrap-text-box">';
                        $content[] = '<div>';

                            // $content[] = '<span class="ays-pb-new-mega-bundle-title">';
                            //     $content[] = __( "<span><a href='https://ays-pro.com/mega-bundle' target='_blank' style='color:#ffffff; text-decoration: underline;'>Mega Bundle</a></span> (pb + Survey + Poll)", AYS_PB_NAME );
                            // $content[] = '</span>';
                            $content[] = '<span class="ays-pb-new-mega-bundle-title">';
                            $content[] = __( "Limited Time <span style='color:#ffffff;'>20%</span> SALE on <span><a href='https://ays-pro.com/wordpress/popup-box?utm_source=dashboard&utm_medium=popup-free&utm_campaign=sale-banner' target='_blank' style='color:#ffffff; text-decoration: underline;'>Popup Box</a></span>", "ays-popup-box" );
                        $content[] = '</span>';
                            $content[] = '</br>';
                            $content[] = '<div class="ays-pb-new-mega-bundle-mobile-image-display-block display_none">';
                                $content[] = '<img src="' . AYS_PB_ADMIN_URL . '/images/icons/pb-20-guaranteeicon.svg" style="width: 70px;">';
                            $content[] = '</div>';

                            $content[] = '<span class="ays-pb-new-mega-bundle-desc">';
                                $content[] = '<img class="ays-pb-new-mega-bundle-guaranteeicon" src="' . AYS_PB_ADMIN_URL . '/images/icons/pb-maker-guaranteeicon.svg" style="width: 30px;">';
                                $content[] = __( "30 Day Money Back Guarantee", "ays-popup-box" );
                            $content[] = '</span>';
                        $content[] = '</div>';

                        $content[] = '<div>';
                            $content[] = '<img src="' . AYS_PB_ADMIN_URL . '/images/icons/pb-20-guaranteeicon.svg" class="ays-pb-new-mega-bundle-mobile-image-display-none" style="width: 70px;">';
                        $content[] = '</div>';

                        // $content[] = '<br>';

                        // $content[] = '<strong>';
                        //         $content[] = __( "Hurry up! <a href='https://ays-pro.com/mega-bundle' target='_blank'>Check it out!</a>", "ays-popup-box" );
                        // $content[] = '</strong>';

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

                        $content[] = '<div id="ays-pb-maker-countdown-main-container">';
                            $content[] = '<div class="ays-pb-maker-countdown-container">';

                                $content[] = '<div id="ays-pb-countdown">';

                                    $content[] = '<div>';
                                        $content[] = __( "Offer ends in:", "ays-popup-box" );
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
                        $content[] = '<a href="https://ays-pro.com/wordpress/popup-box?utm_source=dashboard&utm_medium=popup-free&utm_campaign=sale-banner" class="button button-primary ays-button" id="ays-button-top-buy-now" target="_blank">' . __( 'Learn More', "ays-popup-box" ) . '</a>';
                        $content[] = '<span class="ays-pb-dicount-one-time-text">';
                            $content[] = __( "One-time payment", "ays-popup-box" );
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
                                        $content[] = __( "Offer ends in:", "ays-popup-box" );
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
                        $content[] = '<a href="https://ays-pro.com/wordpress/popup-box?utm_source=dashboard&utm_medium=popup-free&utm_campaign=sale-banner-30" class="button button-primary ays-button" id="ays-button-top-buy-now" target="_blank">' . __( 'Buy Now !', "ays-popup-box" ) . '</a>';
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
