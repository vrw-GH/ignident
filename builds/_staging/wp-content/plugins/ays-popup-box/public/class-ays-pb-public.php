<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://ays-pro.com/
 * @since      1.0.0
 *
 * @package    Ays_Pb
 * @subpackage Ays_Pb/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Ays_Pb
 * @subpackage Ays_Pb/public
 * @author     AYS Pro LLC <info@ays-pro.com>
 */
class Ays_Pb_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    private $settings;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

        $this->settings = new Ays_PopupBox_Settings_Actions($this->plugin_name);
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
        $settings_options = $this->settings->ays_get_setting('options');
        if($settings_options) {
            $settings_options = json_decode(stripcslashes($settings_options), true);
        } else {
            $settings_options = array();
        }

        // Animation CSS File
        $settings_options['pb_exclude_animation_css'] = isset($settings_options['pb_exclude_animation_css']) ? esc_attr( $settings_options['pb_exclude_animation_css'] ) : 'off';
        $pb_exclude_animation_css = (isset($settings_options['pb_exclude_animation_css']) && esc_attr( $settings_options['pb_exclude_animation_css'] ) == 'on') ? true : false;

        if (!$pb_exclude_animation_css) {
            wp_enqueue_style( 'pb_animate', plugin_dir_url( __FILE__ ) . 'css/animate.css', array(), $this->version, 'all' );
        }
	}

    /**
     * Register style sheets for the public side of the site footer.
     *
     * @since    1.0.0
     */
    public function enqueue_styles_footer(){
        wp_enqueue_style( $this->plugin_name . '-min', plugin_dir_url( __FILE__ ) . 'css/ays-pb-public-min.css', array(), $this->version, 'all' );
    }

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ays_Pb_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ays_Pb_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ays-pb-public.js', array( 'jquery' ), $this->version, false );

         wp_localize_script($this->plugin_name, 'pbLocalizeObj', array(
            'ajax' => admin_url('admin-ajax.php'),
            'seconds' => 'seconds',
            'thisWillClose' => 'This will close in',
                'icons' => array(
                    'close_icon' => '<svg class="ays_pb_material_close_icon" xmlns="https://www.w3.org/2000/svg" height="36px" viewBox="0 0 24 24" width="36px" fill="#000000" alt="Pop-up Close"><path d="M0 0h24v24H0z" fill="none"/><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>',
                    'close_circle_icon' => '<svg class="ays_pb_material_close_circle_icon" xmlns="https://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="36" alt="Pop-up Close"><path d="M0 0h24v24H0z" fill="none"/><path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z"/></svg>',
                    'volume_up_icon' => '<svg class="ays_pb_fa_volume" xmlns="https://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="36"><path d="M0 0h24v24H0z" fill="none"/><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/></svg>',
                    'volume_mute_icon' => '<svg xmlns="https://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0z" fill="none"/><path d="M7 9v6h4l5 5V4l-5 5H7z"/></svg>',
                ),

            ) );
	}
	

	public function ays_generate_shortcode(){
        add_shortcode( 'ays_pb', array($this, 'ays_generate_popup') );
    }
	
	public function ays_set_cookie($attr){
		
        $cookie_time = (isset($attr['cookie']) && $attr['cookie'] != 0) ? absint(intval($attr['cookie'])) : -1;
        $cookie_name = 'ays_popup_cookie_'.$attr['id'];
        $cookie_value = $attr['title'];
        $cookie_expiration =  time() + ($cookie_time * 60);
        setcookie($cookie_name, $cookie_value, $cookie_expiration, '/');
    }

    public function ays_remove_cookie($attr){
        $cookie_name = 'ays_popup_cookie_'.$attr['id'];
        if(isset($_COOKIE[$cookie_name])){
            unset($_COOKIE[$cookie_name]);
            $cookie_expiration =  time() - 1;   
            setcookie($cookie_name, null, $cookie_expiration, '/');
        }
    }

    public function ays_pb_set_cookie_only_once($attr){

        if(isset($_REQUEST['id']) && $_REQUEST['id'] != ''){
             $id = $_REQUEST['id'];
         }else{
            $id = $attr['id'];
         }

        if(isset($_REQUEST['title']) && $_REQUEST['title'] != ''){
              $title = $_REQUEST['title'];
        }else{
            $title =  $attr['title'];
        }
        
        $cookie_name = 'ays_show_popup_only_once_'.$id;
        $cookie_value =  $title;
        $cookie_expiration = time() + (10 * 365 * 24 * 60 * 60);
        setcookie($cookie_name, $cookie_value, $cookie_expiration, '/');
    }

    public function ays_increment_pb_views() {
        global $wpdb;

        $pb_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

        if ($pb_id > 0) {
            // Increment views in the database
            $popups_table = $wpdb->prefix . 'ays_pb';
            $wpdb->query($wpdb->prepare("UPDATE $popups_table SET views = views + 1 WHERE id = %d", $pb_id));
        }

        wp_die();
    }

    public function ays_increment_pb_conversions() {
        global $wpdb;

        $pb_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

        if ($pb_id > 0) {
            // Increment conversions in the database
            $popups_table = $wpdb->prefix . 'ays_pb';
            $wpdb->query($wpdb->prepare("UPDATE $popups_table SET conversions = conversions + 1 WHERE id = %d", $pb_id));
        }

        wp_die();
    }

    public function ays_pb_remove_cookie_only_once($attr){
        $cookie_name = 'ays_show_popup_only_once_'.$attr['id'];
        if(isset($_COOKIE[$cookie_name])){
            unset($_COOKIE[$cookie_name]);
            $cookie_expiration =  time() - 1;   
            setcookie($cookie_name, '', $cookie_expiration, '/');
        }
    }
	

	public function ays_generate_popup( $attr ){

        $id = ( isset($attr['id']) ) ? absint( intval( $attr['id'] ) ) : null;
		$popupbox = $this->get_pb_by_id($id);
        $options = ( isset( $popupbox['options'] ) && $popupbox['options'] != '' ) ? json_decode($popupbox['options'], true) : array();
        $ays_popup_on = stripslashes( esc_attr($popupbox['onoffswitch']) );

        /*******************************************************************************************************/

        $settings_options = $this->settings->ays_get_setting('options');
        if($settings_options){
            $settings_options = json_decode($settings_options, true);
        }else{
            $settings_options = array();
        }

        /*******************************************************************************************************/

        if(isset($options['pb_mobile']) && $options['pb_mobile'] == "on"){
            $check_mobile_device = $this->ays_pb_detect_mobile_device();
            if ($check_mobile_device) {
                $popupbox['onoffswitch'] = 'Off';
            }
        }

        //Hide on desktop
        $options['hide_on_pc'] = ( isset( $options['hide_on_pc'] ) && $options['hide_on_pc'] == "on" ) ? "on" : "off";
        $ays_pb_hide_on_pc = ( isset( $options['hide_on_pc'] ) && $options['hide_on_pc'] == "on" ) ? true : false;

        if( $ays_pb_hide_on_pc ){
            $check_pc = $this->ays_pb_detect_mobile_device();
            if( !$check_pc ){
                $popupbox['onoffswitch'] = 'Off';
            }
        }

        //Hide on Tablets
        $options['hide_on_tablets'] = ( isset( $options['hide_on_tablets'] ) && $options['hide_on_tablets'] == "on" ) ? "on" : "off";
        $ays_pb_hide_on_tablets = ( isset( $options['hide_on_tablets'] ) && $options['hide_on_tablets'] == "on" ) ? true : false;

        if( $ays_pb_hide_on_tablets ){
            $check_tablets = $this->ays_pb_detect_tablet_device();
            if( $check_tablets ){
                $popupbox['onoffswitch'] = 'Off';
            }
        }

        if (isset($popupbox['active_date_check']) && $popupbox['active_date_check'] == "on"){
            if (isset($popupbox['activeInterval']) && isset($popupbox['deactiveInterval'])) {
                $current_time = strtotime(current_time("Y-m-d H:i:s"));
                $startDate    = strtotime($popupbox['activeInterval']);
                $endDate      = strtotime($popupbox['deactiveInterval']);

                if ($startDate >= $current_time || $endDate <= $current_time) {
                    $popupbox['onoffswitch'] = "Off";
                }
            }
        }

        if (isset($popupbox['active_time_check']) && $popupbox['active_time_check'] == "on") {
            if (isset($popupbox['active_time_start']) && isset($popupbox['active_time_end'])) {
                $current_time = strtotime(current_time("H:i:s"));
                $start_time = strtotime($popupbox['active_time_start']);
                $end_time = strtotime($popupbox['active_time_end']);

                if ($start_time >= $current_time || $end_time <= $current_time) {
                    $popupbox['onoffswitch'] = "Off";
                }
            }
        }

        /*******************************************************************************************************/
        // Roles limitations start
        global $wp_roles;
        $user = wp_get_current_user();
        $users_roles  = $wp_roles->role_names;

        $display_for_logged_in_users = isset($popupbox['log_user']) && $popupbox['log_user'] == "On" ? true : false;
        $display_for_current_users_role = (isset($popupbox['users_role']) && $popupbox['users_role'] != '' && $popupbox['users_role'] != '[]') ? true : false;
        $display_for_guests = isset($popupbox['guest']) && $popupbox['guest'] == 'On' ? true : false;

        $show_popup_logged_users = false;
        if ($display_for_logged_in_users && is_user_logged_in()) {
            
            if ($display_for_current_users_role) {
                $user_role_arr = json_decode($popupbox['users_role']);

                $users_role = [];
                if (isset($user_role_arr) && is_array($user_role_arr) ) {
                    $users_role = array_map('esc_attr', $user_role_arr);
                }

                $is_user_role = false;
                if(!empty($users_role)) {
                    if (is_array($users_role)) {
                        foreach($users_role as $key => $role){
                            if(in_array($role, $users_roles)){
                                $users_role[$key] = array_search($role, $users_roles);
                            }                        
                        }
                    }else{
                        if(in_array($users_role, $users_roles)){
                            $users_role = array_search($users_role, $users_roles);
                        }
                    }
                    if(is_array($users_role)){
                        foreach($users_role as $role){                        
                            if (in_array(strtolower($role), (array)$user->roles)) {
                                $is_user_role = true;
                                break;
                            }
                        }                    
                    }else{
                        if (in_array(strtolower($users_role), (array)$user->roles)) {
                            $is_user_role = true;
                        }
                    }
        
                    if ($is_user_role) {
                        $show_popup_logged_users = true;
                    }
                } else {
                    $show_popup_logged_users = true;
                }

            } else {
                $show_popup_logged_users = true;
            }
        }

        $show_popup_guests = false;
        if ($display_for_guests && !is_user_logged_in()) {
            $show_popup_guests = true;
        }

        if (!$show_popup_logged_users && !$show_popup_guests) {
            $popupbox['onoffswitch'] = 'Off';
        }
        // Roles limitations end

        if(isset($_COOKIE['ays_pb_dismiss_ad_'.$id])){
            $popupbox['onoffswitch'] = 'Off';
        }
        
        if(isset($options['enable_dismiss']) && $options['enable_dismiss'] == 'off'){
            if(isset($_COOKIE['ays_pb_dismiss_ad_'.$id])){
                unset($_COOKIE['ays_pb_dismiss_ad_'.$id]);
                $cookie_expiration =  time() - 1;   
                setcookie('ays_pb_dismiss_ad_'.$id, null, $cookie_expiration, '/');
            }
        }        

        //Show popup only for author
        $popupbox['show_only_for_author'] = ( isset( $popupbox['show_only_for_author'] ) && $popupbox['show_only_for_author'] == "on") ? stripslashes( esc_attr($popupbox['show_only_for_author']) ) : 'off';
        $show_only_for_author = ( isset( $popupbox['show_only_for_author']) && $popupbox['show_only_for_author'] == "on") ? true : false;

        $popup_author = ( isset( $options['create_author'] ) && $options['create_author'] != '' ) ? absint( $options['create_author'] ) : '';

        $super_admin = get_super_admins();

        if($show_only_for_author){
            if($popup_author == ''){
                if( ! in_array($user->user_login, $super_admin ) ){
                    $popupbox['onoffswitch'] = 'Off';
                }
            }else if($user->ID != $popup_author){
                $popupbox['onoffswitch'] = 'Off';
            }
        }

        //Tigran
        if(isset($popupbox['onoffswitch']) && $popupbox['onoffswitch'] == 'On'){
			
			if(!isset($_COOKIE['ays_popup_cookie_'.$id]) && isset($popupbox['cookie']) && $popupbox['cookie'] != 0){
				$this->ays_set_cookie($popupbox);
			}elseif(isset($popupbox['cookie']) && $popupbox['cookie'] == 0){
                $this->ays_remove_cookie($popupbox);
            }else{
				return;
			}
            
			$ays_pb_shortcode               = $popupbox["shortcode"];            
            $width = ( isset($attr['w']) ) ? absint( intval( $attr['w'] ) ) : $popupbox["width"];
            $height = ( isset($attr['h']) ) ? absint( intval( $attr['h'] ) ) : $popupbox["height"];
			$popupbox["width"] = $width;
			$popupbox["height"] = $height;

            //Show Popup Title
            $show_title = stripslashes( esc_attr($popupbox["show_popup_title"]) );

            //Show Popup Description
            $show_desc = stripslashes( esc_attr($popupbox["show_popup_desc"]) );

            //Enable Display Content Mobile
            $enable_display_content_mobile = ( isset($options["enable_display_content_mobile"]) && $options["enable_display_content_mobile"] == 'on' ) ? true : false;

			$ays_pb_template = ($popupbox["view_type"] == false || $popupbox["view_type"] == '') ? 'default' : stripslashes( esc_attr($popupbox["view_type"]) );

            //Show Popup Title Mobile
            //Show Popup Description Mobile
            if ($enable_display_content_mobile) {
                $show_title_mobile = ( isset($options["show_popup_title_mobile"]) && $options["show_popup_title_mobile"] == 'On' ) ? stripslashes( esc_attr($options["show_popup_title_mobile"]) ) : 'Off';
                $show_desc_mobile = ( isset($options["show_popup_desc_mobile"]) && $options["show_popup_desc_mobile"] == 'On' ) ? stripslashes( esc_attr($options["show_popup_desc_mobile"]) ) : 'Off';
            } else {
                $show_title_mobile = $show_title;
                $show_desc_mobile = $show_desc;
            }
            $popupbox['show_popup_title_mobile'] = $show_title_mobile;
            $popupbox['show_popup_desc_mobile'] = $show_desc_mobile;

            $closeButton = stripslashes( esc_attr($popupbox["close_button"]) );
			$ays_pb_autoclose = stripslashes( esc_attr($popupbox["autoclose"]) );
			$ays_pb_title = stripslashes( esc_attr($popupbox["title"]) );
			$ays_pb_description = $popupbox["description"];

            // Popup Background Color
			$ays_pb_bgcolor = stripslashes( esc_attr($popupbox["bgcolor"]) );

            // Enable Popup Background Color Mobile
            $enable_bgcolor_mobile = ( isset($options["enable_bgcolor_mobile"]) && $options["enable_bgcolor_mobile"] == 'on' ) ? true : false;

            // Popup Background Color Mobile
            if ($enable_bgcolor_mobile) {
                $ays_pb_bgcolor_mobile = (isset($options["bgcolor_mobile"]) && $options["bgcolor_mobile"] !== '') ? stripslashes( esc_attr($options["bgcolor_mobile"]) ) : '#ffffff';
            } else {
                $ays_pb_bgcolor_mobile = $ays_pb_bgcolor;
            }

            // Background Image
            $ays_pb_bg_image = ( isset($popupbox['bg_image']) && $popupbox['bg_image'] !== '' ) ? esc_url($popupbox['bg_image']) : '';

            // Background Image Position
            $pb_bg_image_position = isset($options["pb_bg_image_position"]) && $options["pb_bg_image_position"] != "" ? str_ireplace('-', ' ', esc_attr($options["pb_bg_image_position"])) : 'center center';

            // Background Image Sizing
            $pb_bg_image_sizing = isset($options["pb_bg_image_sizing"]) && $options["pb_bg_image_sizing"] != "" ? stripslashes( esc_attr($options["pb_bg_image_sizing"]) ) : 'cover';

            // Enable Popup Background Gradient
            $background_gradient = isset($options["enable_background_gradient"]) && $options["enable_background_gradient"] != '' ? stripslashes( esc_attr($options["enable_background_gradient"]) ) : 'off';

            // Background Gradient
            $pb_gradient_direction = (!isset($options->pb_gradient_direction)) ? 'horizontal' : stripslashes( esc_attr($options->pb_gradient_direction) );

            // Background Gradient Direction
            $pb_gradient_direction = isset($options["pb_gradient_direction"]) && $options["pb_gradient_direction"] != '' ? stripslashes( esc_attr($options["pb_gradient_direction"]) ) : 'horizontal';
            switch($pb_gradient_direction) {
                case "horizontal":
                    $pb_gradient_direction = "to right";
                    break;
                case "diagonal_left_to_right":
                    $pb_gradient_direction = "to bottom right";
                    break;
                case "diagonal_right_to_left":
                    $pb_gradient_direction = "to bottom left";
                    break;
                default:
                    $pb_gradient_direction = "to bottom";
            }

            // Background Gradient Color 1
            $background_gradient_color_1 = isset($options["background_gradient_color_1"]) && $options["background_gradient_color_1"] != '' ? stripslashes( esc_attr($options["background_gradient_color_1"]) ) : "#000000";

            // Popup Background Gradient Color 2
            $background_gradient_color_2 = isset($options["background_gradient_color_2"]) && $options["background_gradient_color_2"] != '' ? stripslashes( esc_attr($options["background_gradient_color_2"]) ) : "#fff";

            $ays_pb_bg_image_styles = '';
            $template_bg_gradient_styles = '';
            $ays_pb_bg_image_template_elefante_default = 'background-image: url("https://quiz-plugin.com/wp-content/uploads/2020/02/elefante.jpg");
                                                          background-repeat: no-repeat;
                                                          background-size: cover;';
            $ays_pb_bg_image_template_girl_scaled_default = 'background-image: url("https://quiz-plugin.com/wp-content/uploads/2020/02/girl-scaled.jpg");
                                                             background-repeat: no-repeat;
                                                             background-size: cover;';
            if($ays_pb_bg_image !== ''){
                $ays_pb_bg_image_styles = 'background-image: url(' . $ays_pb_bg_image . ') !important;
                                    background-repeat: no-repeat !important;
                                    background-size: ' . $pb_bg_image_sizing . ' !important;
                                    background-position: ' . $pb_bg_image_position . ' !important;';
            } elseif ($ays_pb_bg_image == '' && ($ays_pb_template == 'image' || $ays_pb_template == 'template')) {
                if ($ays_pb_template == 'image') {
                    $ays_pb_bg_image_styles = $ays_pb_bg_image_template_elefante_default;
                } else {
                    if ($background_gradient == 'on') {
                        $template_bg_gradient_styles = "background-image: linear-gradient(".$pb_gradient_direction.",".$background_gradient_color_1.",".$background_gradient_color_2.");";
                        $ays_pb_bgcolor = 'transparent';
                    } else {
                        $ays_pb_bg_image_styles = $ays_pb_bg_image_template_girl_scaled_default;
                        $template_bg_gradient_styles = "unset";
                    }
                }
            } elseif ($background_gradient == 'on' && $ays_pb_bg_image == '') {
                $ays_pb_bg_image_styles = "background-image: linear-gradient(" . $pb_gradient_direction . "," . $background_gradient_color_1 ." ," . $background_gradient_color_2 . ") !important;";
            }

            // Enable Popup Background Image Mobile
            $enable_bg_image_mobile = ( isset($options["enable_bg_image_mobile"]) && $options["enable_bg_image_mobile"] == 'on' ) ? true : false;

            // Popup Background Image Mobile
            if ($enable_bg_image_mobile) {
                $ays_pb_bg_image_mobile = (isset($options["bg_image_mobile"]) && $options["bg_image_mobile"] !== '') ? esc_url($options["bg_image_mobile"]) : '';
            } else {
                $ays_pb_bg_image_mobile = $ays_pb_bg_image;
            }

            // Enable Popup Background Image Position Mobile
            $enable_pb_bg_image_position_mobile = ( isset($options["enable_pb_bg_image_position_mobile"]) && $options["enable_pb_bg_image_position_mobile"] == 'on' ) ? true : false;

            // Popup Background Image Position Mobile
            if ($enable_pb_bg_image_position_mobile) {
                $pb_bg_image_position_mobile = (isset($options["pb_bg_image_position_mobile"]) && $options["pb_bg_image_position_mobile"] !== '') ? str_ireplace( '-', ' ', esc_attr($options["pb_bg_image_position_mobile"]) ) : 'center center';
            } else {
                $pb_bg_image_position_mobile = $pb_bg_image_position;
            }

            // Enable Popup Background Image Sizing Mobile
            $enable_pb_bg_image_sizing_mobile = ( isset($options["enable_pb_bg_image_sizing_mobile"]) && $options["enable_pb_bg_image_sizing_mobile"] == 'on' ) ? true : false;

            // Popup Background Image Sizing Mobile
            if ($enable_pb_bg_image_sizing_mobile) {
                $pb_bg_image_sizing_mobile = (isset($options["pb_bg_image_sizing_mobile"]) && $options["pb_bg_image_sizing_mobile"] !== '') ? stripslashes( esc_attr($options["pb_bg_image_sizing_mobile"]) ) : 'cover';
            } else {
                $pb_bg_image_sizing_mobile = $pb_bg_image_sizing;
            }

            $isset_background_gradient_mobile = false;
            if ( isset($options['enable_background_gradient_mobile']) ) {
                $isset_background_gradient_mobile = true;
                $background_gradient_mobile = $options['enable_background_gradient_mobile'] != '' ? stripslashes( esc_attr($options["enable_background_gradient_mobile"]) ) : 'off';
            } else {
                $background_gradient_mobile = $background_gradient;
            }

            // Popup Background Gradient Color 1 Mobile
            if ( isset($options["background_gradient_color_1_mobile"]) ) {
                $background_gradient_color_1_mobile = $options["background_gradient_color_1_mobile"] != '' ? stripslashes( esc_attr($options["background_gradient_color_1_mobile"]) ) : "#000000";
            } else {
                $background_gradient_color_1_mobile = $background_gradient_color_1;
            }

            // Popup Background Gradient Color 2 Mobile
            if ( isset($options["background_gradient_color_2_mobile"]) ) {
                $background_gradient_color_2_mobile = $options["background_gradient_color_2_mobile"] != '' ? stripslashes( esc_attr($options["background_gradient_color_2_mobile"]) ) : "#000000";
            } else {
                $background_gradient_color_2_mobile = $background_gradient_color_2;
            }

            // Popup Background Gradient Color 2 Mobile
            if ( isset($options["pb_gradient_direction_mobile"]) ) {
                $pb_gradient_direction_mobile = $options["pb_gradient_direction_mobile"] != '' ? stripslashes( esc_attr($options["pb_gradient_direction_mobile"]) ) : "#000000";
            } else {
                $pb_gradient_direction_mobile = $pb_gradient_direction;
            }

            switch($pb_gradient_direction_mobile) {
                case "horizontal":
                    $pb_gradient_direction_mobile = "to right";
                    break;
                case "diagonal_left_to_right":
                    $pb_gradient_direction_mobile = "to bottom right";
                    break;
                case "diagonal_right_to_left":
                    $pb_gradient_direction_mobile = "to bottom left";
                    break;
                default:
                    $pb_gradient_direction_mobile = "to bottom";
            }

            $ays_pb_bg_image_styles_mobile = '';
            $template_bg_gradient_styles_mobile = '';
            if ($ays_pb_bg_image_mobile !== '') {
                $ays_pb_bg_image_styles_mobile = 'background-image: url('.$ays_pb_bg_image_mobile.') !important;
                                    background-repeat: no-repeat !important;
                                    background-size: '.$pb_bg_image_sizing_mobile.' !important;
                                    background-position: '. $pb_bg_image_position_mobile .' !important;';
            } elseif ($ays_pb_bg_image_mobile == '' && ($ays_pb_template == 'image' || $ays_pb_template == 'template')) {
                if ($ays_pb_template == 'image') {
                    $ays_pb_bg_image_styles_mobile = $ays_pb_bg_image_template_elefante_default;
                } else {
                    if ($background_gradient_mobile == 'on') {
                        $template_bg_gradient_styles_mobile = "background-image: linear-gradient(".$pb_gradient_direction_mobile.",".$background_gradient_color_1_mobile.",".$background_gradient_color_2_mobile.");";
                        $ays_pb_bgcolor = 'transparent';
                    } else {
                        $ays_pb_bg_image_styles_mobile = $ays_pb_bg_image_template_girl_scaled_default;
                        if ($isset_background_gradient_mobile) {
                            $template_bg_gradient_styles_mobile = "unset";
                        }
                    }
                }
            } else {
                if ($background_gradient_mobile == 'on') {
                    $ays_pb_bg_image_styles_mobile = "background-image: linear-gradient(".$pb_gradient_direction_mobile.",".$background_gradient_color_1_mobile.",".$background_gradient_color_2_mobile.") !important;";
                } else {
                    if ($isset_background_gradient_mobile) {
                        $ays_pb_bg_image_styles_mobile = 'background-image: unset !important';
                    } else {
                        $ays_pb_bg_image_styles_mobile = "";
                    }
                }
            }

			$ays_pb_header_bgcolor = stripslashes( esc_attr($popupbox["header_bgcolor"]) );
			$ays_pb_animate_in = stripslashes( esc_attr($popupbox["animate_in"]) );
			$ays_pb_animate_out = stripslashes( esc_attr($popupbox["animate_out"]) );
			$ays_pb_custom_css = wp_unslash( stripslashes( htmlspecialchars_decode( $popupbox["custom_css"] ) ) );
			$ays_pb_custom_html = $popupbox["custom_html"];
			$ays_pb_delay = ($popupbox["delay"] == false) ? 0 : intval($popupbox["delay"]);
            $enable_open_delay_mobile = ( isset($options['enable_open_delay_mobile']) && $options['enable_open_delay_mobile'] == 'on' ) ? 1 : 0;
            $ays_pb_open_delay_mobile = isset($options['open_delay_mobile']) && $options['open_delay_mobile'] != '' ? intval($options['open_delay_mobile']) : 0;
			$ays_pb_scroll_top = ($popupbox["scroll_top"] == false) ? 0 : intval($popupbox["scroll_top"]);
            $enable_scroll_top_mobile = ( isset($options['enable_scroll_top_mobile']) && $options['enable_scroll_top_mobile'] == 'on' ) ? 1 : 0;
            $ays_pb_scroll_top_mobile = isset($options['scroll_top_mobile']) && $options['scroll_top_mobile'] != '' ? intval($options['scroll_top_mobile']) : 0;
			$ays_pb_show_all = stripslashes( esc_attr($popupbox["show_all"]) );
			$ays_pb_action_buttons = ($popupbox["action_button"] == false || $popupbox["action_button"] == '') ? "" : $popupbox["action_button"];
			$ays_pb_action_buttons_type = ($popupbox["action_button_type"] == false) ? "both" : stripslashes( esc_attr($popupbox["action_button_type"]) );
			$ays_pb_modal_content = ($popupbox["modal_content"] == false) ? "shortcode" : stripslashes( esc_attr($popupbox["modal_content"]) );

            //Enable Opening Animation Mobile
            $enable_animate_in_mobile = ( isset($options["enable_animate_in_mobile"]) && $options["enable_animate_in_mobile"] == 'on' ) ? true : false;

            //Opening Animation Mobile
            if ($enable_animate_in_mobile) {
                $ays_pb_animate_in_mobile = (isset($options["animate_in_mobile"]) && $options["animate_in_mobile"] !== '') ? stripslashes( esc_attr($options["animate_in_mobile"]) ) : 'fadeIn';
            } else {
                $ays_pb_animate_in_mobile = $ays_pb_animate_in;
            }

            //Enable Closing Animation Mobile
            $enable_animate_out_mobile = ( isset($options["enable_animate_out_mobile"]) && $options["enable_animate_out_mobile"] == 'on' ) ? true : false;

            //Closing Animation Mobile
            if ($enable_animate_out_mobile) {
                $ays_pb_animate_out_mobile = (isset($options["animate_out_mobile"]) && $options["animate_out_mobile"] !== '') ? stripslashes( esc_attr($options["animate_out_mobile"]) ) : 'fadeOut';
            } else {
                $ays_pb_animate_out_mobile = $ays_pb_animate_out;
            }

            $ays_pb_textcolor = (!isset($popupbox["textcolor"])) ? "#000000" : stripslashes( esc_attr($popupbox["textcolor"]) );

            //Popup Border Size
            $ays_pb_border_size = (isset($popupbox["bordersize"]) && $popupbox["bordersize"] != '') ? absint( intval($popupbox["bordersize"]) ) : 0 ;

            //Enable Border Size Mobile
            $enable_border_size_mobile = ( isset($options["enable_bordersize_mobile"]) && $options["enable_bordersize_mobile"] == 'on' ) ? true : false;

            //Border Size Mobile
            if ($enable_border_size_mobile) {
                $ays_pb_border_size_mobile = (isset($options["bordersize_mobile"]) && $options["bordersize_mobile"] !== '') ? absint( intval($options['bordersize_mobile']) ) : 0;
            } else {
                $ays_pb_border_size_mobile = $ays_pb_border_size;
            }

            // for hide timer description position compatibility with mobile border size
            if ( $ays_pb_template == 'image' || $ays_pb_template == 'minimal' ) {
                $hide_timer_desc_bottom_position_mobile = -30 - $ays_pb_border_size_mobile;
            } else if ($ays_pb_template == 'video') {
                $hide_timer_desc_bottom_position_mobile = $ays_pb_border_size_mobile - 50;
            } else {
                $hide_timer_desc_bottom_position_mobile = '';
            }

            //Popup Border Style
            $ays_pb_border_style = ( isset($options['border_style']) && $options['border_style'] != '' ) ? strtolower( stripslashes( esc_attr($options['border_style']) ) ) : 'solid';

            //Enable Border Style Mobile
            $enable_border_style_mobile = ( isset($options["enable_border_style_mobile"]) && $options["enable_border_style_mobile"] == 'on' ) ? true : false;

            //Border Style Mobile
            if ($enable_border_style_mobile) {
                $ays_pb_border_style_mobile = (isset($options["border_style_mobile"]) && $options["border_style_mobile"] !== '') ? strtolower( stripslashes( esc_attr($options['border_style_mobile']) ) ) : 'solid';
            } else {
                $ays_pb_border_style_mobile = $ays_pb_border_style;
            }

            // Popup Border Color
			$ays_pb_bordercolor = (!isset($popupbox["bordercolor"])) ? "#ffffff" : stripslashes( esc_attr($popupbox["bordercolor"]) );

            //Enable Border Color Mobile
            $enable_bordercolor_mobile = ( isset($options["enable_bordercolor_mobile"]) && $options["enable_bordercolor_mobile"] == 'on' ) ? true : false;

            //Border Color Mobile
            if ($enable_bordercolor_mobile) {
                $ays_pb_bordercolor_mobile = (isset($options["bordercolor_mobile"]) && $options["bordercolor_mobile"] !== '') ? stripslashes( esc_attr($options["bordercolor_mobile"]) ) : '#ffffff';
            } else {
                $ays_pb_bordercolor_mobile = $ays_pb_bordercolor;
            }

            $ays_pb_border_styles_mobile = $ays_pb_border_size_mobile."px ".$ays_pb_border_style_mobile." ".$ays_pb_bordercolor_mobile." !important";

            //Border Radius
            $ays_pb_border_radius = (!isset($popupbox["border_radius"])) ? 4 : absint( intval($popupbox["border_radius"]) );

            //Enable Border Radius Mobile
            $enable_border_radius_mobile = ( isset($options["enable_border_radius_mobile"]) && $options["enable_border_radius_mobile"] == 'on' ) ? true : false;

            //Border Size Mobile
            if ($enable_border_radius_mobile) {
                $ays_pb_border_radius_mobile = (isset($options["border_radius_mobile"]) && $options["border_radius_mobile"] !== '') ? absint( intval($options["border_radius_mobile"]) ) : 4;
            } else {
                $ays_pb_border_radius_mobile = $ays_pb_border_radius;
            }

            $custom_class  = (isset($popupbox['custom_class']) && $popupbox['custom_class'] != "") ? stripslashes( esc_attr($popupbox['custom_class']) ) : "";

            //popup box font-family
            $ays_pb_font_family  = (isset($options['pb_font_family']) && $options['pb_font_family'] != '') ? stripslashes( esc_attr($options['pb_font_family']) ) : 'inherit';

            // Enable title text shadow
            $options['enable_pb_title_text_shadow'] = (isset($options['enable_pb_title_text_shadow']) && $options['enable_pb_title_text_shadow'] == 'on') ? 'on' : 'off'; 

            // Enable title text shadow mobile
            $isset_box_shadow_mobile = false;
            if ( isset($options['enable_pb_title_text_shadow_mobile']) ) {
                $isset_box_shadow_mobile = true;
                $options['enable_pb_title_text_shadow_mobile'] = $options['enable_pb_title_text_shadow_mobile'] == 'on' ? 'on' : 'off';
            } else {
                $options['enable_pb_title_text_shadow_mobile'] = $options['enable_pb_title_text_shadow'];
            }
            $enable_pb_title_text_shadow_mobile = ( isset( $options['enable_pb_title_text_shadow_mobile'] ) && $options['enable_pb_title_text_shadow_mobile'] == 'on' ) ? true : false;

            // Title text shadow Color
            $pb_title_text_shadow_color = (isset($options['pb_title_text_shadow']) && $options['pb_title_text_shadow'] != '') ? stripslashes( esc_attr( $options['pb_title_text_shadow'] ) ) : 'rgba(255,255,255,0)';

            // Title text shadow Color Mobile
            if ( isset($options['pb_title_text_shadow_mobile']) ) {
                $pb_title_text_shadow_color_mobile = ($options['pb_title_text_shadow_mobile'] != '') ? stripslashes( esc_attr( $options['pb_title_text_shadow_mobile'] ) ) : 'rgba(255,255,255,0)';
            } else {
                $pb_title_text_shadow_color_mobile = $pb_title_text_shadow_color;
            }

            // Title text shadow X Offset
            $pb_title_text_shadow_x_offset = (isset($options['pb_title_text_shadow_x_offset']) && $options['pb_title_text_shadow_x_offset'] != '') ? intval($options['pb_title_text_shadow_x_offset'])  : 2;

            // Title text shadow X offset Mobile
            if ( isset($options['pb_title_text_shadow_x_offset_mobile']) ) {
                $pb_title_text_shadow_x_offset_mobile = ( $options['pb_title_text_shadow_x_offset_mobile'] != '' ) ? intval($options['pb_title_text_shadow_x_offset_mobile'])  : 2;
            } else {
                $pb_title_text_shadow_x_offset_mobile = $pb_title_text_shadow_x_offset;
            }

            // Title text shadow Y Offset
            $pb_title_text_shadow_y_offset = (isset($options['pb_title_text_shadow_y_offset']) && $options['pb_title_text_shadow_y_offset'] != '') ? intval($options['pb_title_text_shadow_y_offset'])  : 2;

            // Title text shadow Y offset Mobile
            if ( isset($options['pb_title_text_shadow_y_offset_mobile']) ) {
                $pb_title_text_shadow_y_offset_mobile = ( $options['pb_title_text_shadow_y_offset_mobile'] != '' ) ? intval($options['pb_title_text_shadow_y_offset_mobile'])  : 2;
            } else {
                $pb_title_text_shadow_y_offset_mobile = $pb_title_text_shadow_y_offset;
            }

            // Title text shadow Z Offset
            $pb_title_text_shadow_z_offset = (isset($options['pb_title_text_shadow_z_offset']) && $options['pb_title_text_shadow_z_offset'] != '') ? intval($options['pb_title_text_shadow_z_offset']) : 15;

            // Title text shadow Z offset Mobile
            if ( isset($options['pb_title_text_shadow_z_offset_mobile']) ) {
                $pb_title_text_shadow_z_offset_mobile = ( $options['pb_title_text_shadow_z_offset_mobile'] != '' ) ? intval($options['pb_title_text_shadow_z_offset_mobile'])  : 0;
            } else {
                $pb_title_text_shadow_z_offset_mobile = $pb_title_text_shadow_z_offset;
            }

            if( $enable_pb_title_text_shadow_mobile ){
                $title_text_shadow_mobile = 'text-shadow: ' . $pb_title_text_shadow_x_offset_mobile . 'px ' . $pb_title_text_shadow_y_offset_mobile . 'px ' . $pb_title_text_shadow_z_offset_mobile . 'px ' . $pb_title_text_shadow_color_mobile . ' !important';
            } else {
                if ($isset_box_shadow_mobile) {
                    $title_text_shadow_mobile = 'text-shadow: unset !important';
                } else {
                    $title_text_shadow_mobile = "";
                }
            }

            //Enable Box Shadow
            $options['enable_box_shadow'] = (isset($options['enable_box_shadow']) && $options['enable_box_shadow'] == 'on') ? 'on' : 'off'; 

            //Enable Box Shadow mobile
            $isset_box_shadow_mobile = false;
            if ( isset($options['enable_box_shadow_mobile']) ) {
                $isset_box_shadow_mobile = true;
                $options['enable_box_shadow_mobile'] = $options['enable_box_shadow_mobile'] == 'on' ? 'on' : 'off';
            } else {
                $options['enable_box_shadow_mobile'] = $options['enable_box_shadow'];
            }
            $enable_box_shadow_mobile = ( isset( $options['enable_box_shadow_mobile'] ) && $options['enable_box_shadow_mobile'] == 'on' ) ? true : false;

            //Box Shadow Color
            $pb_box_shadow = (isset($options['box_shadow_color']) && $options['box_shadow_color'] != '') ? stripslashes( esc_attr( $options['box_shadow_color'] ) ) : '#000';

            //Box Shadow Color Mobile
            if ( isset($options['box_shadow_color_mobile']) ) {
                $pb_box_shadow_mobile = ($options['box_shadow_color_mobile'] != '') ? stripslashes( esc_attr( $options['box_shadow_color_mobile'] ) ) : '#000';
            } else {
                $pb_box_shadow_mobile = $pb_box_shadow;
            }

            //Box Shadow X Offset
            $pb_box_shadow_x_offset = (isset($options['pb_box_shadow_x_offset']) && $options['pb_box_shadow_x_offset'] != '') ? intval($options['pb_box_shadow_x_offset'])  : 0;

            //Box Shadow X offset Mobile
            if ( isset($options['pb_box_shadow_x_offset_mobile']) ) {
                $pb_box_shadow_x_offset_mobile = ( $options['pb_box_shadow_x_offset_mobile'] != '' ) ? intval($options['pb_box_shadow_x_offset_mobile'])  : 0;
            } else {
                $pb_box_shadow_x_offset_mobile = $pb_box_shadow_x_offset;
            }

            //Box Shadow Y Offset
            $pb_box_shadow_y_offset = (isset($options['pb_box_shadow_y_offset']) && $options['pb_box_shadow_y_offset'] != '') ? intval($options['pb_box_shadow_y_offset'])  : 0;

            //Box Shadow Y offset Mobile
            if ( isset($options['pb_box_shadow_y_offset_mobile']) ) {
                $pb_box_shadow_y_offset_mobile = ( $options['pb_box_shadow_y_offset_mobile'] != '' ) ? intval($options['pb_box_shadow_y_offset_mobile'])  : 0;
            } else {
                $pb_box_shadow_y_offset_mobile = $pb_box_shadow_y_offset;
            }

            //Box Shadow Z Offset
            $pb_box_shadow_z_offset = (isset($options['pb_box_shadow_z_offset']) && $options['pb_box_shadow_z_offset'] != '') ? intval($options['pb_box_shadow_z_offset']) : 15;

            //Box Shadow Z offset Mobile
            if ( isset($options['pb_box_shadow_z_offset_mobile']) ) {
                $pb_box_shadow_z_offset_mobile = ( $options['pb_box_shadow_z_offset_mobile'] != '' ) ? intval($options['pb_box_shadow_z_offset_mobile'])  : 0;
            } else {
                $pb_box_shadow_z_offset_mobile = $pb_box_shadow_z_offset;
            }

            if( $enable_box_shadow_mobile ){
                $box_shadow_mobile = 'box-shadow: ' . $pb_box_shadow_x_offset_mobile . 'px ' . $pb_box_shadow_y_offset_mobile . 'px ' . $pb_box_shadow_z_offset_mobile . 'px ' . $pb_box_shadow_mobile . ' !important';
            }else{
                if ($isset_box_shadow_mobile) {
                    $box_shadow_mobile = 'box-shadow: unset !important';
                } else {
                    $box_shadow_mobile = "";
                }
            }

            //Close Button Size
            $close_button_size = (isset($options['close_button_size']) && $options['close_button_size'] != '') ? abs($options['close_button_size']) : '1';

            //Close button color
            $close_button_color = (isset($options['close_button_color']) && $options['close_button_color'] != "") ? esc_attr( stripslashes($options['close_button_color'])) : $ays_pb_textcolor;

            //Close button color on hover
            $close_button_hover_color = (isset($options['close_button_hover_color']) && $options['close_button_hover_color'] != "") ? esc_attr( stripslashes($options['close_button_hover_color'])) : $close_button_color;
            
            $modal_class = 'ays-pb-modal';

            $show_only_once =  isset($options['show_only_once']) && $options['show_only_once'] == 'on' ? 'on' : 'off';

            if(!isset($_COOKIE['ays_show_popup_only_once_'.$id]) && isset($options['show_only_once']) && $options['show_only_once'] == 'on' && $ays_pb_action_buttons_type != 'clickSelector'){
                $this->ays_pb_set_cookie_only_once($popupbox);
            }elseif(isset($options['show_only_once']) && $options['show_only_once'] == 'off'){
                $this->ays_pb_remove_cookie_only_once($popupbox);
            }elseif(!isset($options['show_only_once'])){

            }
            elseif(isset($_COOKIE['ays_show_popup_only_once_'.$id]) && isset($options['show_only_once']) && $options['show_only_once'] == 'on'){
                return;
            }else{

            }

            if($ays_pb_title != ''){
                $ays_pb_title = "<h2 style='color: $ays_pb_textcolor !important;font-family:$ays_pb_font_family'>$ays_pb_title</h2>";
            }
            if($ays_pb_description != ''){
                $ays_pb_description = "<p>$ays_pb_description</p>";
            }
            if($ays_pb_custom_css != '' || $ays_pb_custom_css != null){
                $ays_pb_custom_css = "<style>$ays_pb_custom_css</style>";
            }

            //Overlay Color
            $ays_pb_overlay_color = (isset($options["overlay_color"]) && $options["overlay_color"] != '') ? esc_attr( stripslashes($options["overlay_color"]) ) : "#000";

            //Enable Overlay Color Mobile
            $enable_overlay_color_mobile = ( isset($options["enable_overlay_color_mobile"]) && $options["enable_overlay_color_mobile"] == 'on' ) ? true : false;

            //Overlay Color Mobile
            if ($enable_overlay_color_mobile) {
                $ays_pb_overlay_color_mobile = (isset($options["overlay_color_mobile"]) && $options["overlay_color_mobile"] !== '') ? esc_attr( stripslashes($options["overlay_color_mobile"]) ) : "#000";
            } else {
                $ays_pb_overlay_color_mobile = $ays_pb_overlay_color;
            }

            //Close button Delay
            $close_button_delay   = (isset($options["close_button_delay"]) && $options["close_button_delay"] != '') ? absint( intval($options["close_button_delay"]) ) : 0;
            $close_button_delay_for_mobile = (isset($options["close_button_delay_for_mobile"]) && $options["close_button_delay_for_mobile"] != '') ? absint( intval($options["close_button_delay_for_mobile"]) ) : 0;
            $enable_close_button_delay_for_mobile = isset($options['enable_close_button_delay_for_mobile']) && $options['enable_close_button_delay_for_mobile'] == 'on' ? 'true' : 'false';

            //Animation Speed
            $ays_pb_animation_speed = (isset($options["animation_speed"]) && $options["animation_speed"] !== '') ? abs( $options["animation_speed"]) : 1;

            //Enable Animation Speed Mobile
            $enable_animation_speed_mobile = ( isset($options["enable_animation_speed_mobile"]) && $options["enable_animation_speed_mobile"] == 'on' ) ? true : false;

            //Animation Speed Mobile
            if ($enable_animation_speed_mobile) {
                $ays_pb_animation_speed_mobile = (isset($options["animation_speed_mobile"]) && $options["animation_speed_mobile"] !== '') ? abs( $options["animation_speed_mobile"]) : 1;
            } else {
                $ays_pb_animation_speed_mobile = $ays_pb_animation_speed;
            }

            //Close Animation Speed
            $ays_pb_close_animation_speed = (isset($options["close_animation_speed"]) && $options["close_animation_speed"] !== '') ? abs($options["close_animation_speed"]) : 1;

            //Enable Close Animation Speed Mobile
            $enable_close_animation_speed_mobile = ( isset($options["enable_close_animation_speed_mobile"]) && $options["enable_close_animation_speed_mobile"] == 'on' ) ? true : false;

            //Close Animation Speed Mobile
            if ($enable_close_animation_speed_mobile) {
                $ays_pb_close_animation_speed_mobile = (isset($options["close_animation_speed_mobile"]) && $options["close_animation_speed_mobile"] !== '') ? abs( $options["close_animation_speed_mobile"]) : 1;
            } else {
                $ays_pb_close_animation_speed_mobile = $ays_pb_close_animation_speed;
            }
            
            $ays_pb_animation_close_milleseconds = $ays_pb_close_animation_speed * 1000;
            $ays_pb_animation_close_milleseconds_mobile = $ays_pb_close_animation_speed_mobile * 1000;

            $ays_pb_position = isset($popupbox['pb_position']) && $popupbox['pb_position'] != '' ? esc_attr( stripslashes($popupbox['pb_position']) ) : 'center-center';
            $ays_pb_margin = isset($popupbox['pb_margin']) && $popupbox['pb_margin'] != '' ? intval( $popupbox['pb_margin'] ) : 0;

            $enable_pb_position_mobile = ( isset($options['enable_pb_position_mobile']) && $options['enable_pb_position_mobile'] == 'on' ) ? true : false;
            $ays_pb_position_mobile = isset($options['pb_position_mobile']) && $options['pb_position_mobile'] != '' ? esc_attr( stripslashes($options['pb_position_mobile']) ) : 'center-center';

            //close popup by ESC
            $close_popup_esc = (isset($options['close_popup_esc']) && $options['close_popup_esc'] == 'on') ? esc_attr( stripslashes($options['close_popup_esc']) ) : 'off';
            $close_popup_esc_flag = false;

            if($close_popup_esc == 'on' && $popupbox['view_type'] != 'notification'){
                $close_popup_esc_flag = true;
            }

            $close_popup_esc_class = $close_popup_esc_flag ? 'ays-pb-close-popup-with-esc' : '';

            //close popup my clicking outsite the box
            $close_popup_overlay = (isset($options['close_popup_overlay']) && $options['close_popup_overlay'] == 'on') ? esc_attr( stripslashes($options['close_popup_overlay']) ) : 'off';
            $close_popup_overlay_mobile = (isset($options['close_popup_overlay_mobile']) && $options['close_popup_overlay_mobile'] == 'on') ? esc_attr( stripslashes($options['close_popup_overlay_mobile']) ) : 'off';

            $close_popup_overlay_flag= 0;
            $close_popup_overlay_mobile_flag = 0;

            if($close_popup_overlay == 'on'){
                $close_popup_overlay_flag = 1;
            }

            if($close_popup_overlay_mobile == 'on'){
                $close_popup_overlay_mobile_flag = 1;
            }

            if(!isset($options["close_animation_speed"])){
                $ays_pb_close_animation_speed = $ays_pb_animation_speed;

                $ays_pb_animation_close_milleseconds = $ays_pb_close_animation_speed * 1000;

                if(!$enable_close_animation_speed_mobile) {
                    $ays_pb_close_animation_speed_mobile = $ays_pb_close_animation_speed;
                    $ays_pb_animation_close_milleseconds_mobile = $ays_pb_close_animation_speed_mobile * 1000;
                }
            }

            // Disable page scrolling
            $disable_scroll = (isset($options['disable_scroll']) && $options['disable_scroll'] == 'on') ? true : false;

            // Disable page scrolling mobile
            if (isset($options['disable_scroll_mobile'])) {
                $disable_scroll_mobile = $options['disable_scroll_mobile'] == 'on' ? true : false;
            } else {
                $disable_scroll_mobile = $disable_scroll;
            }

            $ays_pb_show_scrollbar_class = (isset($options['show_scrollbar']) && $options['show_scrollbar'] == 'on') ? 'ays-pb-show-scrollbar' : '';

            $enable_pb_fullscreen = (isset($options['enable_pb_fullscreen']) && $options['enable_pb_fullscreen'] == 'on') ? true : false;

            //autoclose on video completion
            $autoclose_on_video_completion = (isset($options['enable_autoclose_on_completion']) && $options['enable_autoclose_on_completion'] == 'on') ? 'on' : 'off';

            // Popup Max-Height
            $pb_max_height = (isset($options['pb_max_height']) && $options['pb_max_height'] != '' && $options['pb_max_height'] != 0 ) ? absint( intval($options['pb_max_height']) ) : '';

            // Max-Height Measurement Unit
            $popup_max_height_by_percentage_px = ( isset($options['popup_max_height_by_percentage_px']) && $options['popup_max_height_by_percentage_px'] != '' ) ? stripslashes( esc_attr($options['popup_max_height_by_percentage_px']) ) : 'pixels';

            // Popup Max-Height Mobile
            $pb_max_height_mobile = (isset($options['pb_max_height_mobile']) && $options['pb_max_height_mobile'] != '' && $options['pb_max_height_mobile'] != 0 ) ? absint( intval($options['pb_max_height_mobile']) ) : '';

            // Max-Height Measurement Unit Mobile
            $popup_max_height_by_percentage_px_mobile = ( isset($options['popup_max_height_by_percentage_px_mobile']) && $options['popup_max_height_by_percentage_px_mobile'] != '' ) ? stripslashes( esc_attr($options['popup_max_height_by_percentage_px_mobile']) ) : 'pixels';

            // if measurement unit is percentage than maximum value = 100
            if ($popup_max_height_by_percentage_px == 'percentage' && $pb_max_height > 100) {
                $pb_max_height = 100;
            }
            if ($popup_max_height_by_percentage_px_mobile == 'percentage' && $pb_max_height_mobile > 100) {
                $pb_max_height_mobile = 100;
            }

            if ($pb_max_height == '') {
                $max_height_styles = 'max-height: none;';
            } else {
                if ($popup_max_height_by_percentage_px == 'pixels') {
                    $max_height_styles = 'max-height: ' . $pb_max_height . 'px;';
                } else {
                    $max_height_styles = 'max-height: ' . $pb_max_height . '%;';
                }
            }

            if ($pb_max_height_mobile == '') {
                $max_height_styles_mobile = 'max-height: none;';
            } else {
                if ($popup_max_height_by_percentage_px_mobile == 'pixels') {
                    $max_height_styles_mobile = 'max-height: ' . $pb_max_height_mobile . 'px;';
                } else {
                    $max_height_styles_mobile = 'max-height: ' . $pb_max_height_mobile . '%;';
                }
            }

            // popup minimal height
            $pb_min_height_val = (isset($options['pb_min_height']) && $options['pb_min_height'] != '') ? absint(intval($options['pb_min_height'])) : 0;

            if ($pb_min_height_val == 0) {
                $pb_min_height = '';
            }else{
                $pb_min_height = "min-height: ".$pb_min_height_val."px;";
            }

            /* 
            * Popup Box container background gradient
            * 
            */
            
            // Checking exists background gradient option
                    
            $options['enable_background_gradient'] = (!isset($options['enable_background_gradient'])) ? "off" : esc_attr( stripslashes($options['enable_background_gradient']) );

            if(isset($options['background_gradient_color_1']) && $options['background_gradient_color_1'] != ''){
                $background_gradient_color_1 = $options['background_gradient_color_1'];
            }else{
                $background_gradient_color_1 = "#000";
            }

            if(isset($options['background_gradient_color_2']) && $options['background_gradient_color_2'] != ''){
                $background_gradient_color_2 = $options['background_gradient_color_2'];
            }else{
                $background_gradient_color_2 = "#fff";
            }

            if(isset($options['quiz_gradient_direction']) && $options['quiz_gradient_direction'] != ''){
                $pb_gradient_direction = $options['quiz_gradient_direction'];
            }else{
                $pb_gradient_direction = 'vertical';
            }
            switch($pb_gradient_direction) {
                case "horizontal":
                    $pb_gradient_direction = "to right";
                    break;
                case "diagonal_left_to_right":
                    $pb_gradient_direction = "to bottom right";
                    break;
                case "diagonal_right_to_left":
                    $pb_gradient_direction = "to bottom left";
                    break;
                default:
                    $pb_gradient_direction = "to bottom";
            }

            // Popup Box container background gradient enabled/disabled
        
            if(isset($options['enable_background_gradient']) && $options['enable_background_gradient'] == "on"){
                $enable_background_gradient = true;
            }else{
                $enable_background_gradient = false;
            }

            // PopupBox container width for mobile
            $popup_width_by_percentage_px_mobile = (isset($options['popup_width_by_percentage_px_mobile']) && $options['popup_width_by_percentage_px_mobile'] != '') ? stripslashes( esc_attr($options['popup_width_by_percentage_px_mobile']) ) : 'percentage';
            $mobile_width_unit = $popup_width_by_percentage_px_mobile == 'percentage' ?  '%' : 'px';

            if(isset($options['mobile_width']) && $options['mobile_width'] != ''){
                $mobile_width = $options['mobile_width'] . $mobile_width_unit;

                // if mobile width is 0 or > 100% than set 100%
                if ( $options['mobile_width'] == 0 || ($options['mobile_width'] > 100 && $mobile_width_unit == '%') ) {
                    $mobile_width = '100%';
                }
            }else{
                $mobile_width = '100%';
            }

            // PopupBox container max-width for mobile
            if(isset($options['mobile_max_width']) && $options['mobile_max_width'] != ''){
                $mobile_max_width = $options['mobile_max_width'] . '%';
            }else{
                $mobile_max_width = '100%';
            }

            //Font Size for mobile
            $pb_font_size_for_mobile = (isset($options['pb_font_size_for_mobile']) && $options['pb_font_size_for_mobile'] != '') ? absint($options['pb_font_size_for_mobile']) : 13;

            ///////////////////////////////////////////////////////////////////////////////////

            /*
            * PopupBox sound
            */

            $enable_pb_sound     = false;
            $ays_pb_sound_status = false;
            $ays_pb_sound        = "";
            $ays_pb_sound_html   = "";
            $ays_pb_check_sound  = (isset($options['enable_pb_sound']) && $options['enable_pb_sound'] != '') ? esc_attr( stripslashes($options['enable_pb_sound']) ) : 'off'; 
        
            if(isset($settings_options['ays_pb_sound']) && $settings_options['ays_pb_sound'] != ''){
                $ays_pb_sound_status = true;
                $ays_pb_sound = $settings_options['ays_pb_sound'];
            }
            
            if(isset($options['enable_pb_sound']) && $options['enable_pb_sound'] == "on"){
                if($ays_pb_sound_status){
                    $enable_pb_sound = true;
                }
            }
            
            if($enable_pb_sound){
                $ays_pb_sound_html = "<audio id='ays_pb_sound_".$id."' class='ays_pb_sound' src='".$ays_pb_sound."'></audio>";
            }
        
            //Popup box close sound

            $animation_pb = false;
            $ays_pb_close_sound_status = false;
            $ays_pb_close_sound = "";
            $ays_pb_close_sound_html = "";
            $ays_pb_check_anim_speed  = (isset($options['animation_speed']) && $options['animation_speed'] != '') ? $options['animation_speed'] : '1';
            $ays_pb_check_anim_speed_mobile  = $ays_pb_animation_speed_mobile;

            if(isset($settings_options['ays_pb_close_sound']) && $settings_options['ays_pb_close_sound'] != ""){
                $ays_pb_close_sound_status  = true;
                $ays_pb_close_sound = $settings_options['ays_pb_close_sound'];
            }

            if( isset($options['animation_speed']) || isset($options['animation_speed_mobile']) ){
                if($ays_pb_close_sound_status){
                    $animation_pb = true;
                }
            }

            if($animation_pb){
                $ays_pb_close_sound_html = "<audio id='ays_pb_close_sound_".$id."' class='ays_pb_close_sound' src='".$ays_pb_close_sound."'></audio>";
            }

            //ays_pb_hover_show_close_btn
            $options['ays_pb_hover_show_close_btn'] = (isset($options['ays_pb_hover_show_close_btn']) && $options['ays_pb_hover_show_close_btn'] == "on") ? "on" : "off";
            $ays_pb_hover_show_close_btn = (isset($options['ays_pb_hover_show_close_btn']) && $options['ays_pb_hover_show_close_btn'] == "on") ? true : false;

            $mobile_height = (isset($options['mobile_height']) && $options['mobile_height'] != "") ? $options['mobile_height'] : $popupbox["height"];

            if(isset($options['mobile_height']) && $options['mobile_height'] != ''){
                
                $mobile_height = $options['mobile_height'];
                if( $options['mobile_height'] == 0){
                    $mobile_height = $popupbox["height"];
                }
            }else{
                $mobile_height = $popupbox["height"];
            }

            $ays_pb_padding_mobile = (isset($options['popup_content_padding_mobile']) && $options['popup_content_padding_mobile'] != '') ? $options['popup_content_padding_mobile'] : '20';
            $enable_padding_mobile = (isset($options['enable_padding_mobile']) && $options['enable_padding_mobile'] == 'on') ? true : false;
            //popup padding percentage
            $popup_padding_by_percentage_px_mobile = (isset($options['popup_padding_by_percentage_px_mobile']) && $options['popup_padding_by_percentage_px_mobile'] != '') ? stripslashes( esc_attr($options['popup_padding_by_percentage_px_mobile']) ) : 'pixels';
            if(isset($ays_pb_padding_mobile) && $ays_pb_padding_mobile != ''){
                if ($popup_padding_by_percentage_px_mobile && $popup_padding_by_percentage_px_mobile == 'percentage') {
                    if (absint(intval($ays_pb_padding_mobile)) > 100 ) {
                        $pb_padding_mobile = '100%';
                    }else{
                        $pb_padding_mobile = $ays_pb_padding_mobile . '%';
                    }
                }else{
                    $pb_padding_mobile = $ays_pb_padding_mobile . 'px';
                }
            }else{
                $pb_padding_mobile = '20px';
            }

            //Overlay Opacity 
            $overlay_opacity = ($popupbox['onoffoverlay'] == 'On' && isset($popupbox['overlay_opacity'])) ? esc_attr($popupbox['overlay_opacity']) : 0.5;
            $enable_overlay_text_mobile = isset($options['enable_overlay_text_mobile']) && $options['enable_overlay_text_mobile'] == 'on' ? 'true' : 'false';
            $overlay_mobile_opacity = ($popupbox['onoffoverlay'] == 'On' && isset($options['overlay_mobile_opacity'])) ? esc_attr($options['overlay_mobile_opacity']) : $overlay_opacity;

            //Blured overlay
            $options['blured_overlay'] = ( isset( $options['blured_overlay'] ) && $options['blured_overlay'] != '' ) ? esc_attr( stripslashes($options['blured_overlay']) ) : 'off';
            $ays_pb_blured_overlay = ( isset( $options['blured_overlay'] ) && $options['blured_overlay'] == 'on' ) ? true : false;
            
            //Blured overlay mobile
            if ( isset( $options['blured_overlay_mobile']) ) {
                if ($options['blured_overlay_mobile'] == '') {
                    $options['blured_overlay_mobile'] = 'off';
                }
            } else {
                $options['blured_overlay_mobile'] = $options['blured_overlay'];
            }
            $ays_pb_blured_overlay_mobile = ( isset( $options['blured_overlay_mobile'] ) && $options['blured_overlay_mobile'] == 'on' ) ? true : false;

            $blured_overlay = '';
            if($ays_pb_blured_overlay && $popupbox['onoffoverlay'] == 'On'){
                $blured_overlay = '-webkit-backdrop-filter: blur(5px);
                backdrop-filter: blur(20px);
                opacity:unset !important;';
                $ays_pb_overlay_color = Ays_Pb_Data::hex2rgba( $ays_pb_overlay_color, 0.5 );
                $ays_pb_overlay_color_mobile = Ays_Pb_Data::hex2rgba( $ays_pb_overlay_color_mobile, 0.5 );
            }

            $blured_overlay_mobile = '';
            if($ays_pb_blured_overlay_mobile && $popupbox['onoffoverlay'] == 'On'){
                $blured_overlay_mobile = '-webkit-backdrop-filter: blur(5px);
                backdrop-filter: blur(20px);
                opacity:unset !important;';
                $ays_pb_overlay_color = Ays_Pb_Data::hex2rgba( $ays_pb_overlay_color, 0.5 );
                $ays_pb_overlay_color_mobile = Ays_Pb_Data::hex2rgba( $ays_pb_overlay_color_mobile, 0.5 );
            } else if(!$ays_pb_blured_overlay_mobile) {
                $blured_overlay_mobile = '-webkit-backdrop-filter: none;
                backdrop-filter: none;
                opacity:'.$overlay_mobile_opacity.' !important;';
                $ays_pb_overlay_color = Ays_Pb_Data::hex2rgba( $ays_pb_overlay_color, false );
                $ays_pb_overlay_color_mobile = Ays_Pb_Data::hex2rgba( $ays_pb_overlay_color_mobile, false );
            }

            //Disable scroll on popup
            $options['disable_scroll_on_popup'] = ( isset( $options['disable_scroll_on_popup'] ) && $options['disable_scroll_on_popup'] != '' ) ? esc_attr( stripslashes($options['disable_scroll_on_popup']) ) : 'off';
            $ays_pb_disable_scroll_on_popup = ( isset( $options['disable_scroll_on_popup'] ) && $options['disable_scroll_on_popup'] == 'on' ) ? true : false;

            //Disable scroll on popup mobile
            if ( isset( $options['disable_scroll_on_popup_mobile']) ) {
                if ($options['disable_scroll_on_popup_mobile'] == '') {
                    $options['disable_scroll_on_popup_mobile'] = 'off';
                }
            } else {
                $options['disable_scroll_on_popup_mobile'] = $options['disable_scroll_on_popup'];
            }
            $ays_pb_disable_scroll_on_popup_mobile = ( isset( $options['disable_scroll_on_popup_mobile'] ) && $options['disable_scroll_on_popup_mobile'] == 'on' ) ? true : false;

            $disable_scroll_on_popup = '';
            $disable_scroll_overflow_y = '';
            $disable_scroll_display_none = '';
            $position_absolute_popup_scroll = '';
            $padding_top_popup_scroll = '';
            $width_popup_scroll = '';
            $bottom_popup_scroll = '';
            $margin_top = '';
            if($ays_pb_disable_scroll_on_popup){
                $disable_scroll_on_popup = 'overflow:hidden !important;';
                $disable_scroll_overflow_y = 'overflow-y: hidden !important';
                $disable_scroll_display_none = 'display:none;';
                $position_absolute_popup_scroll = 'position:absolute;';
                $padding_top_popup_scroll = 'padding:65px 10px;';
                $width_popup_scroll = 'width:100%';
                $bottom_popup_scroll = 'bottom:unset';
                $margin_top = 'margin-top: 65px;';
            }

            if($ays_pb_disable_scroll_on_popup_mobile){
                $disable_scroll_on_popup_mobile = 'overflow:hidden !important;';
                $disable_scroll_overflow_y_mobile = 'overflow-y: hidden !important';
                $disable_scroll_display_none_mobile = 'display:none;';
                $position_absolute_popup_scroll_mobile = 'position:absolute;';
                $padding_top_popup_scroll_mobile = 'padding:65px 10px;';
                $width_popup_scroll_mobile = 'width:100%';
                $bottom_popup_scroll_mobile = 'bottom:unset';
                $margin_top_mobile = 'margin-top: 65px;';
            } else {
                $disable_scroll_on_popup_mobile = 'overflow:auto !important;';
                $disable_scroll_overflow_y_mobile = 'overflow-y: auto !important';
                $disable_scroll_display_none_mobile = 'display:block;';
                $position_absolute_popup_scroll_mobile = 'position:sticky;';
                $padding_top_popup_scroll_mobile = 'padding:0;';
                $width_popup_scroll_mobile = 'width:auto';
                $bottom_popup_scroll_mobile = 'bottom:6px';
                $margin_top_mobile = 'margin-top: 0;';
            }

            //Background image position for mobile
            $options['pb_bg_image_direction_on_mobile'] = ( isset( $options['pb_bg_image_direction_on_mobile'] ) && $options['pb_bg_image_direction_on_mobile'] != "" ) ? esc_attr( stripslashes($options['pb_bg_image_direction_on_mobile']) ) : "on";
            $pb_bg_image_direction_on_mobile = ( isset( $options['pb_bg_image_direction_on_mobile'] ) && $options['pb_bg_image_direction_on_mobile'] == "on" ) ? true : false;

            $ays_pb_image_direction_timer = '';
            $ays_pb_image_direction_content_alignment = '';
            $ays_pb_image_direction_footer_alignment = '';
            $ays_pb_image_direction_image = '';
            if($pb_bg_image_direction_on_mobile){
                $ays_pb_image_direction_timer = 'right: 20%;bottom:0;';
                $ays_pb_image_direction_content_alignment = 'align-items: center;';
                $ays_pb_image_direction_footer_alignment = 'flex-direction: column;align-items: center;justify-content: start;';
                $ays_pb_image_direction_image = 'width:100%; height:180px;';
            }

            /*******************************************************************************************************/


            $ays_pb_bgcolor_rgba = $this->hex2rgba($ays_pb_bgcolor, 0.85);
            $ays_pb_bgcolor_mobile_rgba = $this->hex2rgba($ays_pb_bgcolor_mobile, 0.85);

            $notification_type_class = $ays_pb_template == 'notification' ? 'ays-pb-notification-modal' : '';

            $popupbox_view = $ays_pb_custom_css."
					<div class='ays-pb-modals av_pop_modals_".$id." " . $close_popup_esc_class . " " . $notification_type_class . "' style='min-width: 100%;'>
                        <input type='hidden' value='".$ays_pb_animate_in."' id='ays_pb_modal_animate_in_".$id."'>
                        <input type='hidden' value='".$ays_pb_animate_in_mobile."' id='ays_pb_modal_animate_in_mobile_".$id."'>
                        <input type='hidden' value='".$ays_pb_animate_out."' id='ays_pb_modal_animate_out_".$id."'>
                        <input type='hidden' value='".$ays_pb_animate_out_mobile."' id='ays_pb_modal_animate_out_mobile_".$id."'>
                        <input type='hidden' value='".$ays_pb_animation_close_milleseconds."' id='ays_pb_animation_close_speed_".$id."'>
                        <input type='hidden' value='".$ays_pb_animation_close_milleseconds_mobile."' id='ays_pb_animation_close_speed_mobile_".$id."'>
                        <label for='ays-pb-modal-checkbox_".$id."' class='ays-pb-visually-hidden-label'>modal-check</label>
						<input id='ays-pb-modal-checkbox_".$id."' class='ays-pb-modal-check' type='checkbox'/>
                        {$ays_pb_sound_html}
                        {$ays_pb_close_sound_html}";

            switch($ays_pb_template){
                case 'mac':
                    include_once( 'partials/ays-pb-public-templates.php' );
                    $mac_template = new Ays_Pb_Public_Templates($this->plugin_name, $this->version);
                    $popupbox_view .= $mac_template->ays_pb_template_macos($popupbox);
                     
                    $modal_class = 'ays_window';
                    $modal_close_additional_js = "";
                    break;
                case 'cmd':
                    include_once( 'partials/ays-pb-public-templates.php' );
                    $cmd_template = new Ays_Pb_Public_Templates($this->plugin_name, $this->version);
                    $popupbox_view .= $cmd_template->ays_pb_template_cmd($popupbox);
                    $modal_class = 'ays_window';
                    $modal_close_additional_js = "";
                    break;
                case 'ubuntu':
                    include_once( 'partials/ays-pb-public-templates.php' );
                    $ubuntu_template = new Ays_Pb_Public_Templates($this->plugin_name, $this->version);
                    $popupbox_view .= $ubuntu_template->ays_pb_template_ubuntu($popupbox);
                    $modal_class = 'ays_ubuntu_window';
                    $modal_close_additional_js = "";
                    break;
                case 'winXP':
                    include_once( 'partials/ays-pb-public-templates.php' );
                    $winxp_template = new Ays_Pb_Public_Templates($this->plugin_name, $this->version);
                    $popupbox_view .= $winxp_template->ays_pb_template_winxp($popupbox);
                    $modal_class = 'ays_winxp_window';
                    $modal_close_additional_js = "";
                    break;
                case 'win98':
                    include_once( 'partials/ays-pb-public-templates.php' );
                    $win98_template = new Ays_Pb_Public_Templates($this->plugin_name, $this->version);
                    $popupbox_view .= $win98_template->ays_pb_template_win98($popupbox);
                    $modal_class = 'ays_win98_window';
                    $modal_close_additional_js = "";
                    break;
                case 'lil':
                    include_once( 'partials/ays-pb-public-templates.php' );
                    $lil_template = new Ays_Pb_Public_Templates($this->plugin_name, $this->version);
                    $popupbox_view .= $lil_template->ays_pb_template_lil($popupbox);
                    $modal_class = 'ays_lil_window';
                    $modal_close_additional_js = "";
                    break;
                case 'image':
                    include_once( 'partials/ays-pb-public-templates.php' );
                    $lil_template = new Ays_Pb_Public_Templates($this->plugin_name, $this->version);
                    $popupbox_view .= $lil_template->ays_pb_template_image($popupbox);
                    $modal_class = 'ays_image_window';
                    $modal_close_additional_js = "";
                    break;
                case 'minimal':
                    include_once( 'partials/ays-pb-public-templates.php' );
                    $minimal_template = new Ays_Pb_Public_Templates($this->plugin_name, $this->version);
                    $popupbox_view .= $minimal_template->ays_pb_template_minimal($popupbox);
                    $modal_class = 'ays_minimal_window';
                    $modal_close_additional_js = "";
                    break;
                case 'template':
                    include_once( 'partials/ays-pb-public-templates.php' );
                    $lil_template = new Ays_Pb_Public_Templates($this->plugin_name, $this->version);
                    $popupbox_view .= $lil_template->ays_pb_template_template($popupbox);
                    $modal_class = 'ays_template_window';
                    $modal_close_additional_js = "";
                    break;
                case 'video':
                    include_once( 'partials/ays-pb-public-templates.php' );
                    $video_template = new Ays_Pb_Public_Templates($this->plugin_name, $this->version);
                    $popupbox_view .= $video_template->ays_pb_template_video($popupbox);
                    $modal_class = 'ays_video_window';
                    $modal_close_additional_js = "";
                    break;
                case 'image_type_img_theme':
                    include_once( 'partials/ays-pb-public-templates.php' );
                    $image_type_img_template = new Ays_Pb_Public_Templates($this->plugin_name, $this->version);
                    $popupbox_view .= $image_type_img_template->ays_pb_template_image_type_img($popupbox);
                    $modal_class = 'ays-pb-modal ays-pb-modal-image-type-img';
                    $modal_close_additional_js = "";
                    break;
                case 'facebook':
                    include_once( 'partials/ays-pb-public-templates.php' );
                    $facebook_template = new Ays_Pb_Public_Templates($this->plugin_name, $this->version);
                    $popupbox_view .= $facebook_template->ays_pb_template_facebook($popupbox);
                    $modal_class = 'ays-pb-modal ays_facebook_window';
                    $modal_close_additional_js = "";
                    break;
                case 'notification':
                    include_once( 'partials/ays-pb-public-templates.php' );
                    $notification_template = new Ays_Pb_Public_Templates($this->plugin_name, $this->version);
                    $popupbox_view .= $notification_template->ays_pb_template_notification($popupbox);
                    $modal_class = 'ays_notification_window';
                    $modal_close_additional_js = "";
                    break;
                default:
                    include_once( 'partials/ays-pb-public-templates.php' );
                    $default_template = new Ays_Pb_Public_Templates($this->plugin_name, $this->version);
                    $popupbox_view .= $default_template->ays_pb_template_default($popupbox);
                    $modal_close_additional_js = "";
                    break;
            }

            if( !$enable_pb_fullscreen ){

                $iscloseButtonOff = $closeButton != 'on' ? true : false;

                $popupbox_view .= "<script>
                    document.addEventListener('DOMContentLoaded', function() {";
                if($enable_pb_position_mobile) {
                    $popupbox_view .= "if (window.innerWidth < 768) { "
                                            . $this->setPopupPosition($id, $ays_pb_position_mobile, $popupbox['view_type'], $iscloseButtonOff, $ays_pb_margin, $mobile_height) .
                                        " } else { "
                                            . $this->setPopupPosition($id, $ays_pb_position, $popupbox['view_type'], $iscloseButtonOff, $ays_pb_margin, $popupbox["height"]) .
                                        " }";
                } else {
                    $popupbox_view .= "if (window.innerWidth < 768) { "
                                            . $this->setPopupPosition($id, $ays_pb_position, $popupbox['view_type'], $iscloseButtonOff, $ays_pb_margin, $mobile_height) .
                                        " } else { "
                                            . $this->setPopupPosition($id, $ays_pb_position, $popupbox['view_type'], $iscloseButtonOff, $ays_pb_margin, $popupbox["height"]) .
                                        " }";
                }
                $popupbox_view .= "});
                </script>";

            }

            $screen_shade = $ays_pb_template == 'notification' ? '' : "<div id='ays-pb-screen-shade_" . $id . "' overlay='overlay_" . $id . "' data-mobile-overlay='" . $enable_overlay_text_mobile . "'></div>";

            // Notification type | Logo width | Measurement unit | On desktop
            $notification_logo_width_measurement_unit = (isset($options['notification_logo_width_measurement_unit']) && $options['notification_logo_width_measurement_unit'] == 'pixels') ? 'px' : '%';

            // Notification type | Logo width | On desktop
            $notification_logo_width = (isset($options['notification_logo_width']) && $options['notification_logo_width'] != '') ? absint( esc_attr($options['notification_logo_width']) ) . $notification_logo_width_measurement_unit : '100%';

            // Notification type | Logo width | Measurement unit | On mobile
            $notification_logo_width_measurement_unit_mobile = $notification_logo_width_measurement_unit;
            if (isset($options['notification_logo_width_measurement_unit_mobile'])) {
                $notification_logo_width_measurement_unit_mobile = ($options['notification_logo_width_measurement_unit_mobile'] == 'pixels') ? 'px' : '%';
            }

            // Notification type | Logo width | On mobile
            $notification_logo_width_mobile = (isset($options['notification_logo_width_mobile']) && $options['notification_logo_width_mobile'] != '') ? absint( esc_attr($options['notification_logo_width_mobile']) ) . $notification_logo_width_measurement_unit_mobile : $notification_logo_width;

            // Notification type | Logo max-width | Measurement unit | On desktop
            $notification_logo_max_width_measurement_unit = (isset($options['notification_logo_max_width_measurement_unit']) && $options['notification_logo_max_width_measurement_unit'] == 'percentage') ? '%' : 'px';

            // Notification type | Logo max-width | On desktop
            $notification_logo_max_width = (isset($options['notification_logo_max_width']) && $options['notification_logo_max_width'] != '') ? absint( esc_attr($options['notification_logo_max_width']) ) . $notification_logo_max_width_measurement_unit : '100px';

            // Notification type | Logo max-width | Measurement unit | On mobile
            $notification_logo_max_width_measurement_unit_mobile = $notification_logo_max_width_measurement_unit;
            if (isset($options['notification_logo_max_width_measurement_unit_mobile'])) {
                $notification_logo_max_width_measurement_unit_mobile = ($options['notification_logo_max_width_measurement_unit_mobile'] == 'percentage') ? '%' : 'px';
            }

            // Notification type | Logo max-width | On mobile
            $notification_logo_max_width_mobile = (isset($options['notification_logo_max_width_mobile']) && $options['notification_logo_max_width_mobile'] != '') ? absint( esc_attr($options['notification_logo_max_width_mobile']) ) . $notification_logo_max_width_measurement_unit_mobile : $notification_logo_max_width;

            // Notification type | Logo min-width | Measurement unit | On desktop
            $notification_logo_min_width_measurement_unit = (isset($options['notification_logo_min_width_measurement_unit']) && $options['notification_logo_min_width_measurement_unit'] == 'percentage') ? '%' : 'px';

            // Notification type | Logo min-width | On desktop
            $notification_logo_min_width = (isset($options['notification_logo_min_width']) && $options['notification_logo_min_width'] != '') ? absint( esc_attr($options['notification_logo_min_width']) ) . $notification_logo_min_width_measurement_unit : '50px';

            // Notification type | Logo min-width | Measurement unit | On mobile
            $notification_logo_min_width_measurement_unit_mobile = $notification_logo_min_width_measurement_unit;
            if (isset($options['notification_logo_min_width_measurement_unit_mobile'])) {
                $notification_logo_min_width_measurement_unit_mobile = ($options['notification_logo_min_width_measurement_unit_mobile'] == 'percentage') ? '%' : 'px';
            }

            // Notification type | Logo min-width | On mobile
            $notification_logo_min_width_mobile = (isset($options['notification_logo_min_width_mobile']) && $options['notification_logo_min_width_mobile'] != '') ? absint( esc_attr($options['notification_logo_min_width_mobile']) ) . $notification_logo_min_width_measurement_unit_mobile : $notification_logo_min_width;

            // Notification type | Logo max-height
            $notification_logo_max_height = (isset($options['notification_logo_max_height']) && $options['notification_logo_max_height'] != '') ? absint( esc_attr($options['notification_logo_max_height']) ) . 'px' : 'none';

            // Notification type | Logo min-height
            $notification_logo_min_height = (isset($options['notification_logo_min_height']) && $options['notification_logo_min_height'] != '') ? absint( esc_attr($options['notification_logo_min_height']) ) . 'px' : 'auto';

            // Notification type | Logo sizing
            $notification_logo_image_sizing = (isset($options['notification_logo_image_sizing']) && $options['notification_logo_image_sizing'] != '') ? stripslashes( esc_attr($options['notification_logo_image_sizing']) ) : 'cover';

            // Notification type | Logo shape
            $notification_logo_image_shape = (isset($options['notification_logo_image_shape']) && $options['notification_logo_image_shape'] == 'circle') ? '50%' : 'unset';

            // Notification type | Button 1 background color
            $notification_button_1_bg_color = (isset($options['notification_button_1_bg_color']) && $options['notification_button_1_bg_color'] != '') ? stripslashes( esc_attr($options['notification_button_1_bg_color']) ) : '#F66123';

            // Notification type | Button 1 background color
            $notification_button_1_bg_hover_color = (isset($options['notification_button_1_bg_hover_color']) && $options['notification_button_1_bg_hover_color'] != '') ? stripslashes( esc_attr($options['notification_button_1_bg_hover_color']) ) : $notification_button_1_bg_color;

            // Notification type | Button 1 text color
            $notification_button_1_text_color = (isset($options['notification_button_1_text_color']) && $options['notification_button_1_text_color'] != '') ? stripslashes( esc_attr($options['notification_button_1_text_color']) ) : '#FFFFFF';

            // Notification type | Button 1 text hover color
            $notification_button_1_text_hover_color = (isset($options['notification_button_1_text_hover_color']) && $options['notification_button_1_text_hover_color'] != '') ? stripslashes( esc_attr($options['notification_button_1_text_hover_color']) ) : $notification_button_1_text_color;

            // Notification type | Button 1 text transformation
            $notification_button_1_text_transformation = (isset($options['notification_button_1_text_transformation']) && $options['notification_button_1_text_transformation'] != '') ? stripslashes( esc_attr($options['notification_button_1_text_transformation']) ) : 'none';

            // Notification type | Button 1 text decoration
            $notification_button_1_text_decoration = (isset($options['notification_button_1_text_decoration']) && $options['notification_button_1_text_decoration'] != '') ? stripslashes( esc_attr($options['notification_button_1_text_decoration']) ) : 'none';

            // Notification type | Button 1 letter spacing | On desktop
            $notification_button_1_letter_spacing = (isset($options['notification_button_1_letter_spacing']) && $options['notification_button_1_letter_spacing'] != '') ? absint( esc_attr($options['notification_button_1_letter_spacing']) ) . 'px' : 0;

            // Notification type | Button 1 letter spacing | On mobile
            $notification_button_1_letter_spacing_mobile = (isset($options['notification_button_1_letter_spacing_mobile']) && $options['notification_button_1_letter_spacing_mobile'] != '') ? absint( esc_attr($options['notification_button_1_letter_spacing_mobile']) ) . 'px' : $notification_button_1_letter_spacing;

            // Notification type | Button 1 font size | On desktop
            $notification_button_1_font_size = (isset($options['notification_button_1_font_size']) && $options['notification_button_1_font_size'] != '') ? absint( esc_attr($options['notification_button_1_font_size']) ) . 'px' : '15px';

            // Notification type | Button 1 font size | On mobile
            $notification_button_1_font_size_mobile = (isset($options['notification_button_1_font_size_mobile']) && $options['notification_button_1_font_size_mobile'] != '') ? absint( esc_attr($options['notification_button_1_font_size_mobile']) ) . 'px' : $notification_button_1_font_size;

            // Notification type | Button 1 font weight | On desktop
            $notification_button_1_font_weight = (isset($options['notification_button_1_font_weight']) && $options['notification_button_1_font_weight'] != '') ? stripslashes( esc_attr($options['notification_button_1_font_weight']) ) : 'normal';

            // Notification type | Button 1 font weight | On mobile
            $notification_button_1_font_weight_mobile = (isset($options['notification_button_1_font_weight_mobile']) && $options['notification_button_1_font_weight_mobile'] != '') ? stripslashes( esc_attr($options['notification_button_1_font_weight_mobile']) ) : $notification_button_1_font_weight;

            // Notification type | Button 1 border radius
            $notification_button_1_border_radius = (isset($options['notification_button_1_border_radius']) && $options['notification_button_1_border_radius'] != '') ? absint( esc_attr($options['notification_button_1_border_radius']) ) . 'px' : '6px';

            // Notification type | Button 1 border width
            $notification_button_1_border_width = (isset($options['notification_button_1_border_width']) && $options['notification_button_1_border_width'] != '') ? absint( esc_attr($options['notification_button_1_border_width']) ) : 0;

            $notification_button_1_border = 'none';
            if ($notification_button_1_border_width > 0) {
                // Notification type | Button 1 border style
                $notification_button_1_border_style = (isset($options['notification_button_1_border_style']) && $options['notification_button_1_border_style'] != '') ? stripslashes( esc_attr($options['notification_button_1_border_style']) ) : 'solid';

                // Notification type | Button 1 border color
                $notification_button_1_border_color = (isset($options['notification_button_1_border_color']) && $options['notification_button_1_border_color'] != '') ? stripslashes( esc_attr($options['notification_button_1_border_color']) ) : '#FFFFFF';

                $notification_button_1_border = $notification_button_1_border_width . 'px ' . $notification_button_1_border_style . ' ' . $notification_button_1_border_color;
            }

            // Notification type | Button 1 padding left/right
            $notification_button_1_padding_left_right = (isset($options['notification_button_1_padding_left_right']) && $options['notification_button_1_padding_left_right'] !== '') ? absint( esc_attr($options['notification_button_1_padding_left_right']) ) . 'px' : '32px';

            // Notification type | Button 1 padding top/bottom
            $notification_button_1_padding_top_bottom = (isset($options['notification_button_1_padding_top_bottom']) && $options['notification_button_1_padding_top_bottom'] !== '') ? absint( esc_attr($options['notification_button_1_padding_top_bottom']) ) . 'px' : '12px';

            $notification_button_1_padding = $notification_button_1_padding_top_bottom . ' ' . $notification_button_1_padding_left_right;

            // Notification type | Button 1 transition
            $notification_button_1_transition = (isset($options['notification_button_1_transition']) && $options['notification_button_1_transition'] !== '') ? stripslashes( esc_attr($options['notification_button_1_transition']) ) . 's' : '0.3s';

            // Notification type | Button 1 box shadow
            $notification_button_1_enable_box_shadow = (isset($options['notification_button_1_enable_box_shadow']) && $options['notification_button_1_enable_box_shadow'] == 'on') ? true : false;

            $notification_button_1_box_shadow = 'none';
            if ($notification_button_1_enable_box_shadow) {
                // Notification type | Button 1 box shadow color
                $notification_button_1_box_shadow_color = (isset($options['notification_button_1_box_shadow_color']) && $options['notification_button_1_box_shadow_color'] != '') ? stripslashes( esc_attr($options['notification_button_1_box_shadow_color']) ) : '#FF8319';

                // Notification type | Button 1 box shadow X offset
                $notification_button_1_box_shadow_x_offset = (isset($options['notification_button_1_box_shadow_x_offset']) && $options['notification_button_1_box_shadow_x_offset'] != '') ? absint( intval($options['notification_button_1_box_shadow_x_offset']) ) : 0;

                // Notification type | Button 1 box shadow Y offset
                $notification_button_1_box_shadow_y_offset = (isset($options['notification_button_1_box_shadow_y_offset']) && $options['notification_button_1_box_shadow_y_offset'] != '') ? absint( intval($options['notification_button_1_box_shadow_y_offset']) ) : 0;

                // Notification type | Button 1 box shadow Z offset
                $notification_button_1_box_shadow_z_offset = (isset($options['notification_button_1_box_shadow_z_offset']) && $options['notification_button_1_box_shadow_z_offset'] != '') ? absint( intval($options['notification_button_1_box_shadow_z_offset']) ) : 10;

                $notification_button_1_box_shadow = $notification_button_1_box_shadow_x_offset . 'px ' . $notification_button_1_box_shadow_y_offset . 'px ' . $notification_button_1_box_shadow_z_offset . 'px ' . $notification_button_1_box_shadow_color;
            }

            // Header bg color mobile
            $options['header_bgcolor_mobile'] = isset($options['header_bgcolor_mobile']) ? $options['header_bgcolor_mobile'] : $ays_pb_header_bgcolor;
            $header_bgcolor_mobile = (isset($options['header_bgcolor_mobile']) && $options['header_bgcolor_mobile'] != '') ? stripslashes( esc_attr($options['header_bgcolor_mobile']) ) : '#ffffff';


            $popupbox_view .= "$screen_shade
                        <input type='hidden' class='ays_pb_delay_".$id."' value='".$ays_pb_delay."'/>
                        <input type='hidden' class='ays_pb_delay_mobile_".$id."' value='".$ays_pb_open_delay_mobile."'/>
                        <input type='hidden' class='ays_pb_scroll_".$id."' value='".$ays_pb_scroll_top."'/>
                        <input type='hidden' class='ays_pb_scroll_mobile_".$id."' value='".$ays_pb_scroll_top_mobile."'/>
                        <input type='hidden' class='ays_pb_abt_".$id."' value='".$ays_pb_action_buttons_type."'/>
					</div>                   
                    <style>
                        .ays-pb-modal_".$id."{
                            " . $pb_min_height . "
                            " . $max_height_styles . "
                        }

                        .ays-pb-modal_".$id.", .av_pop_modals_".$id." {
                            display:none;
                        }
                        .ays-pb-modal-check:checked ~ #ays-pb-screen-shade_".$id." {
                            opacity: 0.5;
                            pointer-events: auto;
                        }

                        .ays_notification_window.ays-pb-modal_".$id." .ays_pb_notification_logo img {
                            width: " . $notification_logo_width . ";
                            max-width: " . $notification_logo_max_width . ";
                            min-width: " . $notification_logo_min_width . ";
                            max-height: " . $notification_logo_max_height . ";
                            min-height: " . $notification_logo_min_height . ";
                            object-fit: " . $notification_logo_image_sizing . ";
                            border-radius: " . $notification_logo_image_shape . "
                        }

                        .ays_notification_window.ays-pb-modal_".$id." div.ays_pb_notification_button_1 button {
                            background: " . $notification_button_1_bg_color . ";
                            color: " . $notification_button_1_text_color . ";
                            font-size: " . $notification_button_1_font_size . ";
                            font-weight: " . $notification_button_1_font_weight . ";
                            border-radius: " . $notification_button_1_border_radius . ";
                            border: " . $notification_button_1_border . ";
                            padding: " . $notification_button_1_padding . ";
                            transition: " . $notification_button_1_transition . ";
                            box-shadow: " . $notification_button_1_box_shadow . ";
                            letter-spacing: " . $notification_button_1_letter_spacing . ";
                            text-transform: " . $notification_button_1_text_transformation . ";
                            text-decoration: " . $notification_button_1_text_decoration . ";
                        }

                        .ays_notification_window.ays-pb-modal_".$id." div.ays_pb_notification_button_1 button:hover {
                            background: " . $notification_button_1_bg_hover_color . ";
                            color: " . $notification_button_1_text_hover_color . ";
                        }

                        .ays-pb-modal_" . $id . ".ays-pb-bg-styles_".$id.":not(.ays_winxp_window, .ays_template_window),
                        .ays_winxp_content.ays-pb-bg-styles_".$id.",
                        footer.ays_template_footer.ays-pb-bg-styles_".$id." div.ays_bg_image_box {
                            " . $ays_pb_bg_image_styles . "
                        }

                        .ays-pb-modal_" . $id . ".ays_template_window {
                            " . $template_bg_gradient_styles ."
                        }

                        .ays_cmd_window {
                            background-color: ".$ays_pb_bgcolor_rgba.";
                        }
                        
                        .ays_cmd_window-cursor .ays_cmd_i-cursor-underscore {
                            background-color: black;
                        }
                        
                        .ays_cmd_window-cursor .ays_cmd_i-cursor-indicator {
                            background-color: transparent;
                        }

                        .ays-pb-modal_".$id." .ays_pb_description > *, 
                        .ays-pb-modal_".$id." .ays_pb_timer,
                        .ays-pb-modal_".$id." .ays_content_box p,
                        .ays-pb-modal_".$id." .ays-pb-dismiss-ad > button#ays_pb_dismiss_ad{
                            color: ".$ays_pb_textcolor.";
                            font-family: ".$ays_pb_font_family.";
                        }

                        .ays-pb-modal_".$id." .close-image-btn{
                            color: ".$close_button_color." !important;
                        }    

                        .ays-pb-modal_".$id." .close-image-btn:hover,
                        .ays-pb-modal_".$id." .close-template-btn:hover{
                            color: ".$close_button_hover_color." !important;
                        }    

                        .ays-pb-modal_".$id." .ays_pb_material_close_circle_icon{
                            fill: ".$close_button_color." !important;
                        }

                        .ays-pb-modal_".$id." .ays_pb_material_close_circle_icon:hover{
                            fill: ".$close_button_hover_color." !important;
                        }
                        
                        .ays-pb-modal_".$id." .ays_pb_material_close_icon{
                            fill: ".$close_button_color." !important;
                        }
                        
                        .ays-pb-modal_".$id." .ays_pb_material_close_icon:hover{
                            fill: ".$close_button_hover_color." !important;
                        }
                        
                        #ays-pb-screen-shade_".$id." {
                            opacity: 0;
                            background: ".$ays_pb_overlay_color.";
                            position: absolute;
                            left: 0;
                            right: 0;
                            top: 0;
                            bottom: 0;
                            pointer-events: none;
                            transition: opacity 0.8s;
                            ".$blured_overlay.";
                        }

                        .ays-pb-modal_".$id.".".$ays_pb_animate_in."{
                            animation-duration: ".$ays_pb_animation_speed."s !important;
                        }
                        .ays-pb-modal_".$id.".".$ays_pb_animate_out." {
                            animation-duration: ".$ays_pb_close_animation_speed."s !important;
                        }

                        .ays-pb-disable-scroll-on-popup{
                            ".$disable_scroll_on_popup." 
                            ".$disable_scroll_overflow_y."
                        }
                        .ays_lil_window .ays_lil_main,
                        .ays_window.ays-pb-modal_".$id." .ays_pb_description,
                        .ays_win98_window.ays-pb-modal_".$id." .ays_pb_description,
                        .ays_cmd_window.ays-pb-modal_".$id." .ays_pb_description,
                        .ays_winxp_window.ays-pb-modal_".$id." .ays_pb_description,
                        .ays_ubuntu_window.ays-pb-modal_".$id." .ays_pb_description{
                            ".$margin_top."
                        }
                        
                        .ays-pb-modals .ays-pb-modal_".$id." .ays_pb_description + hr{
                            ".$disable_scroll_display_none."
                        }

                        .ays-pb-modals.av_pop_modals_".$id." .ays-pb-modal_".$id." .ays_lil_head, .ays-pb-modals.av_pop_modals_".$id." .ays-pb-modal_".$id." .ays_topBar, .ays-pb-modals.av_pop_modals_".$id." .ays-pb-modal_".$id." .ays_cmd_window-header, .ays-pb-modals.av_pop_modals_".$id." .ays-pb-modal_".$id." .ays_ubuntu_topbar, .ays-pb-modals.av_pop_modals_".$id." .ays-pb-modal_".$id." .ays_ubuntu_tools, .ays-pb-modal_".$id." .ays_winxp_title-bar, .ays-pb-modals.av_pop_modals_".$id." .ays-pb-modal_".$id." .ays_win98_head, .ays-pb-modal_".$id." .ays_cmd_window-header, .ays-pb-modals.av_pop_modals_".$id." .ays-pb-modal_".$id." .ays_cmd_window-cursor, .ays-pb-modals.av_pop_modals_".$id." .ays-pb-modal_".$id." .ays_ubuntu_folder-info.ays_pb_timer_".$id.", .ays_cmd_window-content .ays_pb_timer.ays_pb_timer_".$id."{
                            ".$position_absolute_popup_scroll."
                            ".$width_popup_scroll."
                        }
                        .ays_cmd_window-content .ays_pb_timer.ays_pb_timer_".$id."{
                            ".$bottom_popup_scroll."
                        }
                        .ays-pb-modals.av_pop_modals_".$id." .ays-pb-modal_".$id." .ays_pb_description ~ ays-pb-modal .ays_pb_description{
                            ".$padding_top_popup_scroll."
                        }

                        .ays-pb-modal_".$id." .ays-pb-modal-close_".$id.":hover .close-lil-btn {
                            transform: rotate(180deg) scale(".$close_button_size.") !important;
                        }

                        .ays_pb_hide_timer_on_pc {
                            visibility: hidden;
                        }

                        @media screen and (max-width: 768px){";
                            if($enable_padding_mobile){
                                $popupbox_view .= " .ays_content_box{
                                    padding: ".$pb_padding_mobile." !important;
                                }";
                            }
                            $popupbox_view .= "
                            .ays-pb-modal_".$id."{
                                width: $mobile_width !important;
                                max-width: $mobile_max_width !important;
                                height : ".$mobile_height."px !important;
                                " . $box_shadow_mobile . ";
                                box-sizing: border-box;
                                " . $max_height_styles_mobile . "
                            }

                            .ays_notification_window.ays-pb-modal_".$id." .ays_pb_notification_logo img {
                                width: " . $notification_logo_width_mobile . ";
                                max-width: " . $notification_logo_max_width_mobile . ";
                                min-width: " . $notification_logo_min_width_mobile . ";
                            }

                            .ays_notification_window.ays-pb-modal_".$id." div.ays_pb_notification_button_1 button {
                                font-size: " . $notification_button_1_font_size_mobile . ";
                                font-weight: " . $notification_button_1_font_weight_mobile . ";
                                letter-spacing: " . $notification_button_1_letter_spacing_mobile . ";
                            }

                            .ays_template_head,.ays_lil_head{
                                background-color: " . $header_bgcolor_mobile . " !important;
                            }

                            .ays_cmd_window {
                                background-color: " . $ays_pb_bgcolor_mobile_rgba . ";
                            }

                            #ays-pb-screen-shade_" . $id . " {
                                background: ".$ays_pb_overlay_color_mobile.";
                            }

                            .ays-pb-modal_" . $id . ".ays-pb-bg-styles_".$id.":not(.ays_winxp_window, .ays_template_window),
                            .ays_winxp_content.ays-pb-bg-styles_".$id.",
                            footer.ays_template_footer.ays-pb-bg-styles_".$id." div.ays_bg_image_box {
                                " . $ays_pb_bg_image_styles_mobile . "
                            }

                            .ays-pb-modal_" . $id . ".ays_template_window {
                                " . $template_bg_gradient_styles ."
                            }

                            .ays-pb-bg-styles_" . $id . " {
                                background-color: " . $ays_pb_bgcolor_mobile . " !important;
                            }

                            .ays-pb-border-mobile_".$id." {
                                border : ".$ays_pb_border_styles_mobile.";
                                border-radius: ".$ays_pb_border_radius_mobile."px !important;
                            }

                            .ays_pb_title_styles_".$id." {
                                " . $title_text_shadow_mobile . ";
                            }

                            .ays-pb-modal_".$id."  .ays_pb_description > p{
                                font-size: {$pb_font_size_for_mobile}px !important;
                                word-break: break-word !important;
                                word-wrap: break-word;
                            }

                            .ays-pb-modal_".$id.".ays_template_window p.ays_pb_timer.ays_pb_timer_".$id."{
                                {$ays_pb_image_direction_timer}
                            }

                            .ays-pb-modal_".$id." div.ays_image_content p.ays_pb_timer.ays_pb_timer_".$id.",
                            .ays-pb-modal_".$id.".ays_minimal_window p.ays_pb_timer.ays_pb_timer_".$id.",
                            .ays-pb-modal_".$id.".ays_video_window p.ays_pb_timer.ays_pb_timer_".$id."{
                                bottom: {$hide_timer_desc_bottom_position_mobile}px !important;
                            }

                            .ays-pb-modal_".$id.".ays_template_window footer.ays_template_footer{
                                {$ays_pb_image_direction_footer_alignment}
                            }

                            .ays-pb-modal_".$id.".ays_template_window div.ays_bg_image_box{
                                {$ays_pb_image_direction_image}
                            }

                            #ays-pb-screen-shade_".$id." {
                                ".$blured_overlay_mobile.";
                            }

                            .ays-pb-modal_".$id.".".$ays_pb_animate_in_mobile."{
                                animation-duration: ".$ays_pb_animation_speed_mobile."s !important;
                            }
                            .ays-pb-modal_".$id.".".$ays_pb_animate_out_mobile." {
                                animation-duration: ".$ays_pb_close_animation_speed_mobile."s !important;
                            }

                            .ays-pb-disable-scroll-on-popup{
                                ".$disable_scroll_on_popup_mobile." 
                                ".$disable_scroll_overflow_y_mobile."
                            }

                            .ays-pb-modals .ays-pb-modal_".$id." .ays_pb_description + hr{
                                ".$disable_scroll_display_none_mobile."
                            }

                            .ays-pb-modals.av_pop_modals_".$id." .ays-pb-modal_".$id." .ays_lil_head, .ays-pb-modals.av_pop_modals_".$id." .ays-pb-modal_".$id." .ays_topBar, .ays-pb-modals.av_pop_modals_".$id." .ays-pb-modal_".$id." .ays_cmd_window-header, .ays-pb-modals.av_pop_modals_".$id." .ays-pb-modal_".$id." .ays_ubuntu_topbar, .ays-pb-modals.av_pop_modals_".$id." .ays-pb-modal_".$id." .ays_ubuntu_tools, .ays-pb-modal_".$id." .ays_winxp_title-bar, .ays-pb-modals.av_pop_modals_".$id." .ays-pb-modal_".$id." .ays_win98_head, .ays-pb-modal_".$id." .ays_cmd_window-header, .ays-pb-modals.av_pop_modals_".$id." .ays-pb-modal_".$id." .ays_cmd_window-cursor, .ays-pb-modals.av_pop_modals_".$id." .ays-pb-modal_".$id." .ays_ubuntu_folder-info.ays_pb_timer_".$id.", .ays_cmd_window-content .ays_pb_timer.ays_pb_timer_".$id."{
                                ".$position_absolute_popup_scroll_mobile."
                                ".$width_popup_scroll_mobile."
                            }

                            .ays-pb-modals.av_pop_modals_".$id." .ays-pb-modal_".$id." .ays_pb_description ~ ays-pb-modal .ays_pb_description{
                                ".$padding_top_popup_scroll_mobile."
                            }

                            .ays_cmd_window-content .ays_pb_timer.ays_pb_timer_".$id."{
                                ".$bottom_popup_scroll_mobile."
                            }

                            .ays_lil_window .ays_lil_main,
                            .ays_window.ays-pb-modal_".$id." .ays_pb_description,
                            .ays_win98_window.ays-pb-modal_".$id." .ays_pb_description,
                            .ays_cmd_window.ays-pb-modal_".$id." .ays_pb_description,
                            .ays_winxp_window.ays-pb-modal_".$id." .ays_pb_description,
                            .ays_ubuntu_window.ays-pb-modal_".$id." .ays_pb_description{
                                ".$margin_top_mobile."
                            }

                            .ays_pb_hide_timer_on_pc {
                                visibility: visible;
                            }

                            .ays_pb_hide_timer_on_mobile {
                                visibility: hidden !important;
                            }
                        }
                    </style>
                    ";


            if($ays_pb_action_buttons_type != 'clickSelector'){
                $popupbox_view .= "
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        (function( $ ) {
                            'use strict';
                            let pbViewsFlag_".$id." = true;
                            if ('" . $ays_pb_template . "' == 'notification') {
                                $(document).find('.ays-pb-modals').prependTo($(document.body));
                            } else {
                                $(document).find('.ays-pb-modals:not(.ays-pb-modals.ays-pb-notification-modal)').appendTo($(document.body));
                            }
                            let isMobile = false;
                            let closePopupOverlay = " . $close_popup_overlay_flag . ";
                            let isPageScrollDisabled = " . (int)$disable_scroll . ";
                            let checkAnimSpeed = " . $ays_pb_check_anim_speed . ";
                            let ays_pb_animation_close_speed = $(document).find('#ays_pb_animation_close_speed_".$id."').val();
                            let ays_pb_effectIn_".$id." = $(document).find('#ays_pb_modal_animate_in_".$id."').val();
                            let ays_pb_effectOut_".$id." = $(document).find('#ays_pb_modal_animate_out_".$id."').val();
                            if (window.innerWidth < 768) {
                                isMobile = true;
                                closePopupOverlay = " . $close_popup_overlay_mobile_flag . ";
                                isPageScrollDisabled = " . (int)$disable_scroll_mobile . ";
                                checkAnimSpeed = " . $ays_pb_check_anim_speed_mobile . ";
                                ays_pb_animation_close_speed = $(document).find('#ays_pb_animation_close_speed_mobile_".$id."').val();
                                ays_pb_effectIn_".$id." = $(document).find('#ays_pb_modal_animate_in_mobile_".$id."').val();
                                ays_pb_effectOut_".$id." = $(document).find('#ays_pb_modal_animate_out_mobile_".$id."').val();
                            }
                            let ays_pb_delayOpen_".$id." = parseInt($(document).find('.ays_pb_delay_".$id."').val());
                            let ays_pb_scrollTop_".$id." = parseInt($(document).find('.ays_pb_scroll_".$id."').val());
                            if (isMobile) {
                                if (" . $enable_scroll_top_mobile . ") {
                                    ays_pb_scrollTop_".$id."= parseInt($(document).find('.ays_pb_scroll_mobile_".$id."').val());
                                }

                                if (" . $enable_open_delay_mobile . ") {
                                    ays_pb_delayOpen_".$id." = parseInt($(document).find('.ays_pb_delay_mobile_".$id."').val());
                                }
                            }
                            let time_pb_".$id." = $(document).find('.ays_pb_timer_".$id." span').data('seconds'),
                                ays_pb_animation_close_seconds = (ays_pb_animation_close_speed / 1000);
                            if( ays_pb_delayOpen_".$id." == 0 &&  ays_pb_scrollTop_".$id." == 0){
                                $(document).find('.av_pop_modals_".$id."').css('display','block');
                            }

                            if (window.innerWidth < 768) {
                                var mobileTimer = +$(document).find('.ays_pb_timer_".$id." span').attr('data-ays-mobile-seconds');
                                $(document).find('.ays_pb_timer_".$id." span').html(mobileTimer);
                                time_pb_".$id." = mobileTimer;
                            }

                            ays_pb_animation_close_speed = parseFloat(ays_pb_animation_close_speed) - 50;

                            $(document).find('.ays_pb_music_sound').css({'display':'none'});
                            if(time_pb_".$id." !== undefined){
                                if(time_pb_".$id." !== 0){
                                    $(document).find('#ays-pb-modal-checkbox_".$id."').trigger('click');
                                    if(ays_pb_scrollTop_".$id." == 0){
                                        var ays_pb_flag =  true;
                                        $(document).find('.ays-pb-modal_".$id."').css({
                                            'animation-duration': ays_pb_animation_close_seconds + 's'
                                        });
                                        let timer_pb_".$id." = setInterval(function(){
                                            let newTime_pb_".$id." = time_pb_".$id."--;
                                            $(document).find('.ays_pb_timer_".$id." span').text(newTime_pb_".$id.");
                                            if(newTime_pb_".$id." <= 0){
                                                $(document).find('.ays-pb-modal-close_".$id."').trigger('click');
                                                $(document).find('.ays-pb-modal_".$id."').attr('class', '".$modal_class." ays-pb-modal_".$id." ".$custom_class." ays-pb-bg-styles_" . $id . " ays-pb-border-mobile_".$id." '+ays_pb_effectOut_".$id.");
                                                if(ays_pb_effectOut_".$id." != 'none'){
                                                    setTimeout(function(){ 
                                                        $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                        $(document).find('.ays-pb-modal_".$id."').attr('data-ays-flag','true');
                                                    }, ays_pb_animation_close_speed);
                                                }else{
                                                    $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                    $(document).find('.ays-pb-modal_".$id."').attr('data-ays-flag','true');
                                                }
                                                $modal_close_additional_js
                                                clearInterval(timer_pb_".$id.");
                                            }
                                            $(document).find('.ays-pb-modal-close_".$id."').one('click', function(){
                                                if (pbViewsFlag_".$id.") {
                                                    var pb_id = ".$id.";

                                                    $.ajax({
                                                        url: pbLocalizeObj.ajax,
                                                        method: 'POST',
                                                        dataType: 'text',
                                                        data: {
                                                            id: pb_id,
                                                            action: 'ays_increment_pb_views',
                                                        },
                                                    });

                                                    pbViewsFlag_".$id." = false;
                                                }
                                                $(document).find('.av_pop_modals_".$id."').css('pointer-events', 'none');
                                                $(document).find('.ays-pb-modal_".$id."').attr('class', '".$modal_class." ".$ays_pb_show_scrollbar_class." ays-pb-modal_".$id." ".$custom_class." ays-pb-bg-styles_" . $id . " ays-pb-border-mobile_".$id." '+ays_pb_effectOut_".$id.");
                                                $(this).parents('.ays-pb-modals').find('iframe').each(function(){
                                                    var key = /https:\/\/www.youtube.com/;
                                                    var src = $(this).attr('src');
                                                    $(this).attr('src', $(this).attr('src'));
                                                });
                                                $(this).parents('.ays-pb-modals').find('video.wp-video-shortcode').each(function(){
                                                    if(typeof $(this).get(0) != 'undefined'){
                                                        if ( ! $(this).get(0).paused ) {
                                                            $(this).get(0).pause();
                                                        }
                                                    }
                                                });
                                                $(this).parents('.ays-pb-modals').find('audio.wp-audio-shortcode').each(function(){
                                                    if(typeof $(this).get(0) != 'undefined'){
                                                        if ( ! $(this).get(0).paused ) {
                                                            $(this).get(0).pause();
                                                        }
                                                    }
                                                });
                                                var close_sound_src = $(document).find('#ays_pb_close_sound_".$id."').attr('src');
                                                if(checkAnimSpeed && typeof close_sound_src !== 'undefined' && '". $ays_pb_check_sound ."' === 'on'){
                                                    if(checkAnimSpeed !== 0){
                                                        var playPromise = $(document).find('#ays_pb_close_sound_".$id."').get(0).play();
                                                        if (playPromise !== undefined) {
                                                            playPromise.then(function() {
                                                                audio.pause();
                                                            }).catch(function(error) {
                                                                
                                                            });
                                                        }
                                                    }
                                                }
                                                if(ays_pb_effectOut_".$id." != 'none'){
                                                    setTimeout(function(){
                                                        $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                        $(document).find('.av_pop_modals_".$id."').css('display', 'none');
                                                        $(document).find('.ays-pb-modal_".$id."').attr('data-ays-flag','true');
                                                        if($('#ays_pb_close_sound_".$id."').get(0) != undefined){
                                                            if(!$('#ays_pb_close_sound_".$id."').get(0).paused){
                                                                $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                                var audio = $('#ays_pb_close_sound_".$id."').get(0);
                                                                audio.pause();
                                                                audio.currentTime = 0;
                                                            }
                                                        }    
                                                    }, ays_pb_animation_close_speed);  
                                                }else{
                                                    $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                    $(document).find('.av_pop_modals_".$id."').css('display', 'none');
                                                    $(document).find('.ays-pb-modal_".$id."').attr('data-ays-flag','true');
                                                    if($('#ays_pb_close_sound_".$id."').get(0) != undefined){
                                                        if(!$('#ays_pb_close_sound_".$id."').get(0).paused){
                                                            $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                            var audio = $('#ays_pb_close_sound_".$id."').get(0);
                                                            audio.pause();
                                                            audio.currentTime = 0;
                                                        }
                                                    }    
                                                }
                                                $modal_close_additional_js
                                                $(document).find('#ays-pb-screen-shade_".$id."').css({'opacity': '0', 'display': 'none'});
                                                clearInterval(timer_pb_".$id.");
                                            });
                                            $(document).on('keydown', function(event) { 
                                                if('".$close_popup_esc_flag."' && ays_pb_flag){
                                                    var escClosingPopups = $(document).find('.ays-pb-close-popup-with-esc:visible');
                                                    if (event.keyCode == 27) {
                                                        var topmostPopup = escClosingPopups.last();
                                                        topmostPopup.find('.ays-pb-modal-close_".$id."').trigger('click');
                                                    } 
                                                } else {
                                                    ays_pb_flag = true;
                                                }
                                                ays_pb_flag = false;
                                            });
                                        },1000); 
                                        if(closePopupOverlay && '".$popupbox['onoffoverlay']."' == 'On'){
                                            $(document).find('.av_pop_modals_".$id."').on('click', function(e) {
                                                var pb_parent = $(this);
                                                var pb_div = $(this).find('.ays-pb-modal_".$id."');
                                                if (!pb_div.is(e.target) && pb_div.has(e.target).length === 0){
                                                    $(document).find('.ays-pb-modal-close_".$id."').click();
                                                }
                                            });
                                        }
                                    }
                                } else {
                                     $(document).find('.ays_pb_timer_".$id."').css('display','none');
                                     $(document).find('.ays-pb-modal_".$id."').css({
                                        'animation-duration': ays_pb_animation_close_seconds + 's'
                                     }); 
                                     $(document).find('.ays-pb-modal-close_".$id."').one('click', function(){
                                        if (pbViewsFlag_".$id.") {
                                            var pb_id = ".$id.";

                                            $.ajax({
                                                url: pbLocalizeObj.ajax,
                                                method: 'POST',
                                                dataType: 'text',
                                                data: {
                                                    id: pb_id,
                                                    action: 'ays_increment_pb_views',
                                                },
                                            });

                                            pbViewsFlag_".$id." = false;
                                        }
                                        $(document).find('.av_pop_modals_".$id."').css('pointer-events', 'none');
                                        $(document).find('.ays-pb-modal_".$id."').attr('class', '".$modal_class." ".$ays_pb_show_scrollbar_class."  ays-pb-modal_".$id." ".$custom_class." ays-pb-bg-styles_" . $id . " ays-pb-border-mobile_".$id." '+ays_pb_effectOut_".$id.");
                                        $(this).parents('.ays-pb-modals').find('iframe').each(function(){
                                            var key = /https:\/\/www.youtube.com/;
                                            var src = $(this).attr('src');
                                            $(this).attr('src', $(this).attr('src'));
                                        });
                                        $(this).parents('.ays-pb-modals').find('video.wp-video-shortcode').each(function(){
                                            if(typeof $(this).get(0) != 'undefined'){
                                                if ( ! $(this).get(0).paused ) {
                                                    $(this).get(0).pause();
                                                }
                                            }
                                        });
                                        $(this).parents('.ays-pb-modals').find('audio.wp-audio-shortcode').each(function(){
                                            if(typeof $(this).get(0) != 'undefined'){
                                                if ( ! $(this).get(0).paused ) {
                                                    $(this).get(0).pause();
                                                }
                                            }
                                        });
                                        if(ays_pb_effectOut_".$id." != 'none'){
                                            setTimeout(function(){
                                                $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                $(document).find('.av_pop_modals_".$id."').css('display', 'none');
                                                $(document).find('.ays-pb-modal_".$id."').attr('data-ays-flag','true');
                                                if($('#ays_pb_close_sound_".$id."').get(0) != undefined){
                                                    if(!$('#ays_pb_close_sound_".$id."').get(0).paused){
                                                        $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                        var audio = $('#ays_pb_close_sound_".$id."').get(0);
                                                        audio.pause();
                                                        audio.currentTime = 0;
                                                    }
                                                }   
                                            }, ays_pb_animation_close_speed);  
                                        }else{
                                            $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                            $(document).find('.av_pop_modals_".$id."').css('display', 'none');
                                            $(document).find('.ays-pb-modal_".$id."').attr('data-ays-flag','true');
                                            if($('#ays_pb_close_sound_".$id."').get(0) != undefined){
                                                if(!$('#ays_pb_close_sound_".$id."').get(0).paused){
                                                    $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                    var audio = $('#ays_pb_close_sound_".$id."').get(0);
                                                    audio.pause();
                                                    audio.currentTime = 0;
                                                }
                                            }   
                                        }
                                        $modal_close_additional_js
                                        $(document).find('#ays-pb-screen-shade_".$id."').css({'opacity': '0', 'display': 'none'});
                                     });
                                }
                            }
                            let count = 0;
                            if( ays_pb_scrollTop_".$id." !== 0 ){
                                $(window).scroll(function() {
                                    if(($(this).scrollTop() >= ays_pb_scrollTop_".$id.") && (count === 0)) {
                                        count++;
                                        if( ays_pb_delayOpen_".$id." !== 0 ){                        
                                            $(document).find('.ays-pb-modal_".$id."').css('animation-delay', ays_pb_delayOpen_".$id."/1000);
                                            setTimeout(function(){
                                                $(document).find('.av_pop_modals_".$id."').css('display','block');
                                                $(document).find('.ays-pb-modal_".$id."').addClass(ays_pb_effectIn_".$id.");
                                                $(document).find('.ays-pb-modal_".$id."').css('display', 'block');
                                                if (window.innerWidth < 768 && $(document).find('#ays-pb-screen-shade_".$id."').attr('data-mobile-overlay') == 'true') {
                                                    $(document).find('#ays-pb-screen-shade_".$id."').css({'opacity': '".$overlay_mobile_opacity."'});
                                                }
                                                else{
                                                    $(document).find('#ays-pb-screen-shade_".$id."').css({'opacity': '".$overlay_opacity."'});
                                                }

                                                $(document).find('.ays-pb-modal-check_".$id."').prop('checked', true);
                                            }, ays_pb_delayOpen_".$id.");
                                        }else{
                                            $(document).find('.av_pop_modals_".$id."').css('display','block');
                                            $(document).find('.ays-pb-modal_".$id."').addClass(ays_pb_effectIn_".$id.");
                                            $(document).find('.ays-pb-modal_".$id."').css('display', 'block');
                                            
                                            if (window.innerWidth < 768 && $(document).find('#ays-pb-screen-shade_".$id."').attr('data-mobile-overlay') == 'true') {
                                                $(document).find('#ays-pb-screen-shade_".$id."').css({'opacity': '".$overlay_mobile_opacity."'});
                                            }
                                            else{
                                                $(document).find('#ays-pb-screen-shade_".$id."').css({'opacity': '".$overlay_opacity."'});
                                            }

                                            $(document).find('.ays-pb-modal-check_".$id."').prop('checked', true);
                                        }
                                        if('".$enable_close_button_delay_for_mobile."' == 'true' && window.innerWidth < 768){
                                            if(".$close_button_delay_for_mobile." != 0 && '".$closeButton."' != 'on'){
                                                let close_button_delay_for_mobile = ".$close_button_delay_for_mobile.";
                                                if (ays_pb_delayOpen_".$id." != 0) {
                                                    close_button_delay_for_mobile += Math.floor(ays_pb_delayOpen_".$id.");
                                                }
                                                $(document).find('.ays-pb-modal-close_".$id."').css({'display': 'none'});
                                                setTimeout(function(){ 
                                                    $(document).find('.ays-pb-modal-close_".$id."').css({'display': 'block'});
                                                }, close_button_delay_for_mobile );
                                            }
                                        }
                                        else  {
                                            if(".$close_button_delay." != 0 && '".$closeButton."' != 'on'){
                                                let close_button_delay = ".$close_button_delay.";
                                                if (ays_pb_delayOpen_".$id." != 0) {
                                                close_button_delay += Math.floor(ays_pb_delayOpen_".$id.");
                                                }
                                                $(document).find('.ays-pb-modal-close_".$id."').css({'display': 'none'});
                                                setTimeout(function(){ 
                                                    $(document).find('.ays-pb-modal-close_".$id."').css({'display': 'block'});
                                                }, close_button_delay );
                                            }
                                        }
                                        
                                        if(".$ays_pb_autoclose." != 0){
                                            $(document).find('.ays-pb-modal_".$id."').css({
                                                'animation-duration': ays_pb_animation_close_seconds + 's'
                                            });
                                            let timer_pb_".$id." = setInterval(function(){
                                                let newTime_pb_".$id." = time_pb_".$id."--;
                                                $(document).find('.ays_pb_timer_".$id." span').text(newTime_pb_".$id.");
                                                if(newTime_pb_".$id." <= 0){
                                                    $(document).find('.ays-pb-modal-close_".$id."').trigger('click');
                                                    $(document).find('.ays-pb-modal_".$id."').attr('class', '".$modal_class." ays-pb-modal_".$id."  ".$custom_class." '+ays_pb_effectOut_".$id.");
                                                    if(ays_pb_effectOut_".$id." != 'none'){
                                                        setTimeout(function(){
                                                            $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                            $(document).find('.ays-pb-modal_".$id."').attr('data-ays-flag','true');
                                                        }, ays_pb_animation_close_speed);
                                                    }else{
                                                        $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                        $(document).find('.ays-pb-modal_".$id."').attr('data-ays-flag','true');
                                                    }
                                                    $modal_close_additional_js
                                                    clearInterval(timer_pb_".$id.");
                                                }
                                                $(document).find('.ays-pb-modal-close_".$id."').one('click', function(){
                                                    if (pbViewsFlag_".$id.") {
                                                        var pb_id = ".$id.";

                                                        $.ajax({
                                                            url: pbLocalizeObj.ajax,
                                                            method: 'POST',
                                                            dataType: 'text',
                                                            data: {
                                                                id: pb_id,
                                                                action: 'ays_increment_pb_views',
                                                            },
                                                        });

                                                        pbViewsFlag_".$id." = false;
                                                    }
                                                    $(document).find('.av_pop_modals_".$id."').css('pointer-events', 'none');
                                                    $(document).find('.ays-pb-modal_".$id."').attr('class', '".$modal_class." ".$ays_pb_show_scrollbar_class." ays-pb-modal_".$id."  ".$custom_class." ays-pb-bg-styles_" . $id . " ays-pb-border-mobile_".$id." '+ays_pb_effectOut_".$id.");
                                                    $(this).parents('.ays-pb-modals').find('iframe').each(function(){
                                                        var key = /https:\/\/www.youtube.com/;
                                                        var src = $(this).attr('src');
                                                        $(this).attr('src', $(this).attr('src'));
                                                    });
                                                    $(this).parents('.ays-pb-modals').find('video.wp-video-shortcode').each(function(){
                                                        if(typeof $(this).get(0) != 'undefined'){
                                                            if ( ! $(this).get(0).paused ) {
                                                                $(this).get(0).pause();
                                                            }
                                                        }
                                                    });
                                                    $(this).parents('.ays-pb-modals').find('audio.wp-audio-shortcode').each(function(){
                                                        if(typeof $(this).get(0) != 'undefined'){
                                                            if ( ! $(this).get(0).paused ) {
                                                                $(this).get(0).pause();
                                                            }
                                                        }
                                                    });
                                                    if(ays_pb_effectOut_".$id." != 'none'){
                                                        setTimeout(function(){
                                                            $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                            $(document).find('.av_pop_modals_".$id."').css('display', 'none');
                                                            $(document).find('.ays-pb-modal_".$id."').attr('data-ays-flag','true');
                                                            if($('#ays_pb_close_sound_".$id."').get(0) != undefined){
                                                                if(!$('#ays_pb_close_sound_".$id."').get(0).paused){
                                                                    $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                                    var audio = $('#ays_pb_close_sound_".$id."').get(0);
                                                                    audio.pause();
                                                                    audio.currentTime = 0;
                                                                }
                                                            }   
                                                        }, ays_pb_animation_close_speed); 
                                                    }else{
                                                        $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                        $(document).find('.av_pop_modals_".$id."').css('display', 'none');
                                                        $(document).find('.ays-pb-modal_".$id."').attr('data-ays-flag','true');
                                                        if($('#ays_pb_close_sound_".$id."').get(0) != undefined){
                                                            if(!$('#ays_pb_close_sound_".$id."').get(0).paused){
                                                                $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                                var audio = $('#ays_pb_close_sound_".$id."').get(0);
                                                                audio.pause();
                                                                audio.currentTime = 0;
                                                            }
                                                        }
                                                    }
                                                    $modal_close_additional_js
                                                    $(document).find('#ays-pb-screen-shade_".$id."').css({'opacity': '0', 'display': 'none'});
                                                    clearInterval(timer_pb_".$id.");
                                                });
                                                var ays_pb_flag = true;
                                                $(document).on('keydown', function(event) { 
                                                    if('".$close_popup_esc_flag."' && ays_pb_flag){
                                                        var escClosingPopups = $(document).find('.ays-pb-close-popup-with-esc:visible');
                                                        if (event.keyCode == 27) {
                                                            var topmostPopup = escClosingPopups.last();
                                                            topmostPopup.find('.ays-pb-modal-close_".$id."').trigger('click');
                                                            ays_pb_flag = false;
                                                        } 
                                                    } else {
                                                        ays_pb_flag = true;
                                                    }
                                                });
                                            },1000);
                                        }
                                    }
                                });
                            }else{
                                if( ays_pb_delayOpen_".$id." !== 0 ){
                                    $(document).find('.ays-pb-modal_".$id."').css('animation-delay', ays_pb_delayOpen_".$id."/1000);
                                    setTimeout(function(){
                                        $(document).find('.av_pop_modals_".$id."').css('display','block');
                                        $(document).find('.ays-pb-modal_".$id."').addClass(ays_pb_effectIn_".$id.");
                                        $(document).find('.ays-pb-modal_".$id."').css('display', 'block');
                                        if (window.innerWidth < 768 && $(document).find('#ays-pb-screen-shade_".$id."').attr('data-mobile-overlay') == 'true') {
                                            $(document).find('#ays-pb-screen-shade_".$id."').css({'opacity': '".$overlay_mobile_opacity."'});
                                        }
                                        else{
                                            $(document).find('#ays-pb-screen-shade_".$id."').css({'opacity': '".$overlay_opacity."'});
                                        }
                                        $(document).find('.ays-pb-modal-check_".$id."').attr('checked', 'checked');

                                        if(isPageScrollDisabled){
                                            $(document).find('body').addClass('pb_disable_scroll_".$id."');
                                            $(document).find('html').removeClass('pb_enable_scroll');
                                        }

                                    }, ays_pb_delayOpen_".$id.");
                                } else {
                                    if($(document).find('.ays_pb_abt_".$id."').val() != 'clickSelector'){
                                        $(document).find('.av_pop_modals_".$id."').css('display','block');
                                        $(document).find('.ays-pb-modal_".$id."').addClass(ays_pb_effectIn_".$id.");
                                        $(document).find('.ays-pb-modal_".$id."').css('display', 'block');
                                        if (window.innerWidth < 768 && $(document).find('#ays-pb-screen-shade_".$id."').attr('data-mobile-overlay') == 'true') {
                                            $(document).find('#ays-pb-screen-shade_".$id."').css({'opacity': '".$overlay_mobile_opacity."'});
                                        }
                                        else{
                                            $(document).find('#ays-pb-screen-shade_".$id."').css({'opacity': '".$overlay_opacity."'});
                                        }
                                        $(document).find('.ays-pb-modal-check_".$id."').attr('checked', 'checked');

                                        if(isPageScrollDisabled){
                                            $(document).find('body').addClass('pb_disable_scroll_".$id."');
                                            $(document).find('html').addClass('pb_disable_scroll_".$id."');
                                        }
                                    }
                                }
                            }
                            if ('".$popupbox['onoffoverlay']."' != 'On'){
                                $(document).find('#ays-pb-screen-shade_".$id."').css({'opacity': '0', 'display': 'none !important', 'pointer-events': 'none', 'background': 'none'});
                                $(document).find('.ays-pb-modal_".$id."').css('pointer-events', 'auto');
                                $(document).find('.av_pop_modals_".$id."').css('pointer-events','none');
                            };
                            if($(document).find('.ays-pb-modals video').hasClass('wp-video-shortcode')){
                                var videoWidth  = $(document).find('.ays-pb-modals video.wp-video-shortcode').attr('width');
                                var videoHeight = $(document).find('.ays-pb-modals video.wp-video-shortcode').attr('height');
                                setTimeout(function(){
                                    $(document).find('.ays-pb-modals .wp-video').removeAttr('style');
                                    $(document).find('.ays-pb-modals .mejs-container').removeAttr('style');
                                    $(document).find('.ays-pb-modals video.wp-video-shortcode').removeAttr('style');

                                    $(document).find('.ays-pb-modals .wp-video').css({'width': '100%'});
                                    $(document).find('.ays-pb-modals .mejs-container').css({'width': '100%','height': videoHeight + 'px'});
                                    $(document).find('.ays-pb-modals video.wp-video-shortcode').css({'width': '100%','height': videoHeight + 'px'});
                                },1000);
                            }
                            if($(document).find('.ays-pb-modals iframe').attr('style') != ''){
                                setTimeout(function(){
                                    $(document).find('.ays-pb-modals iframe').removeAttr('style');
                                },500);
                            }
                            // if(".$ays_pb_autoclose." == 0){
                                if(closePopupOverlay && '".$popupbox['onoffoverlay']."' == 'On'){
                                    $(document).find('.av_pop_modals_".$id."').on('click', function(e) {
                                        var pb_parent = $(this);
                                        var pb_div = $(this).find('.ays-pb-modal_".$id."');
                                        if (!pb_div.is(e.target) && pb_div.has(e.target).length === 0){
                                            $(document).find('.ays-pb-modal-close_".$id."').click();
                                        }
                                    });
                                }
                                var ays_pb_flag = true;
                                $(document).on('keydown', function(event) { 
                                    if('".$close_popup_esc_flag."' && ays_pb_flag){
                                        var escClosingPopups = $(document).find('.ays-pb-close-popup-with-esc:visible');
                                        if (event.keyCode == 27) {
                                            var topmostPopup = escClosingPopups.last();
                                            topmostPopup.find('.ays-pb-modal-close_".$id."').trigger('click');
                                            ays_pb_flag = false;
                                        }
                                    } else {
                                       ays_pb_flag = true;
                                    }
                                });
                            // }
                            if('".$autoclose_on_video_completion."' == 'on') {
                                var video = $(document).find('video.wp-video-shortcode');
                                for (let i = 0; i < video.length; i++) {
                                    video[i].addEventListener('ended', function() {
                                        if ($(this).next().val() === 'on') {
                                            $(this).parents('.ays_video_window').find('.close-image-btn').trigger('click');
                                        }
                                    });
                                }
                            }

                            jQuery(document).on('click', '.ays-pb-modal-close_".$id."', function() {
                                $(document).find('body').removeClass('pb_disable_scroll_".$id."');
                                $(document).find('html').removeClass('pb_disable_scroll_".$id."');
                            });
                        })( jQuery );
                    });
                </script>";
            }

            if($ays_pb_action_buttons_type != 'pageLoaded'){
                if($show_only_once == 'on'){
                    $cl = 'one';
                }else{
                    $cl = 'on';
                }
                $popupbox_view .= "                
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            (function( $ ) {
                                'use strict';
                                let pbViewsFlag_".$id." = true;
                                var ays_flag = true;
                                var show_only_once = '{$show_only_once}';
                                let isMobile = false;
                                let closePopupOverlay = " . $close_popup_overlay_flag . ";
                                let isPageScrollDisabled = " . (int)$disable_scroll . ";
                                let checkAnimSpeed = " . $ays_pb_check_anim_speed . ";
                                let ays_pb_animation_close_speed = $(document).find('#ays_pb_animation_close_speed_".$id."').val();
                                let ays_pb_effectIn_".$id." = $(document).find('#ays_pb_modal_animate_in_".$id."').val();
                                let ays_pb_effectOut_".$id." = $(document).find('#ays_pb_modal_animate_out_".$id."').val();
                                if (window.innerWidth < 768) {
                                    isMobile = true;
                                    closePopupOverlay = " . $close_popup_overlay_mobile_flag . ";
                                    isPageScrollDisabled = " . (int)$disable_scroll_mobile . ";
                                    checkAnimSpeed = " . $ays_pb_check_anim_speed_mobile . ";
                                    ays_pb_animation_close_speed = $(document).find('#ays_pb_animation_close_speed_mobile_".$id."').val();
                                    ays_pb_effectIn_".$id." = $(document).find('#ays_pb_modal_animate_in_mobile_".$id."').val();
                                    ays_pb_effectOut_".$id." = $(document).find('#ays_pb_modal_animate_out_mobile_".$id."').val();
                                }
                                $(document).".$cl."('click', '".$ays_pb_action_buttons."', function(){
                                    var actionSelector = '".$ays_pb_action_buttons."';
                                    if(actionSelector.length === 0) {
                                        return;
                                    }

                                    $(document).find('.ays_pb_music_sound').css({'display':'block'});

                                    if(show_only_once == 'on'){
                                        $.ajax({
                                            url: '".admin_url('admin-ajax.php')."',
                                            method: 'post',
                                            dataType: 'json',
                                            data: {
                                                action: 'ays_pb_set_cookie_only_once',
                                                id: ".$popupbox['id'].",
                                                title: '".htmlentities($popupbox['title'],ENT_QUOTES)."',
                                            },
                                        });
                                    }
                             
                                    var dataAttr = $(document).find('.ays-pb-modal_".$id."').attr('data-ays-flag');
                                    if(ays_flag && dataAttr == 'true'){
                                        ays_flag = false;
                                        $(document).find('.av_pop_modals_".$id."').css('display','block');
                                        $(document).find('.av_pop_modals_".$id."').css('pointer-events', 'auto');

                                        if (window.innerWidth < 768) {
                                            $(document).find('.ays_pb_timer_".$id." span').html($(document).find('.ays_pb_timer_".$id." span').attr('data-ays-mobile-seconds'));
                                        } else {
                                            $(document).find('.ays_pb_timer_".$id." span').html($(document).find('.ays_pb_timer_".$id." span').attr('data-ays-seconds'));
                                        }
                                
                                        clearInterval(timer_pb_".$id.");
                                        timer_pb_".$id." = null;
                                        $(document).find('.ays-pb-modal_".$id."').removeClass(ays_pb_effectOut_".$id.");
                                        $(document).find('.ays-pb-modal_".$id."').addClass(ays_pb_effectIn_".$id.");
                                        $(document).find('.ays-pb-modal_".$id."').css('display', 'block');

                                        if (window.innerWidth < 768 && $(document).find('#ays-pb-screen-shade_".$id."').attr('data-mobile-overlay') == 'true') {
                                            $(document).find('#ays-pb-screen-shade_".$id."').css({'opacity': '".$overlay_mobile_opacity."', 'display': 'block'});
                                        } else {
                                            $(document).find('#ays-pb-screen-shade_".$id."').css({'opacity': '".$overlay_opacity."', 'display': 'block'});
                                        }
                                        $(document).find('.ays-pb-modal-check_".$id."').prop('checked', true);
                                        $(document).find('.ays-pb-modal-check_".$id."').attr('checked', true);
                                        var ays_pb_animation_close_seconds = (ays_pb_animation_close_speed / 1000);
                                        var sound_src = $(document).find('#ays_pb_sound_".$id."').attr('src');
                                        var close_sound_src = $(document).find('#ays_pb_close_sound_".$id."').attr('src');

                                        ays_pb_animation_close_speed = parseFloat(ays_pb_animation_close_speed) - 50;

                                        if ('". $ays_pb_check_sound ."' === 'on' && typeof sound_src !== 'undefined'){
                                            $('#ays_pb_sound_".$id."').get(0).play();
                                            $(document).find('.ays_pb_pause_sound_".$id."').on('click',function(){
                                                var audio = $('#ays_pb_sound_".$id."').get(0);
                                                audio.pause();
                                                audio.currentTime = 0;
                                            });
                                        }
                                        //close sound start
                                        if(checkAnimSpeed && typeof close_sound_src !== 'undefined' && '". $ays_pb_check_sound ."' === 'on'){
                                            if(checkAnimSpeed !== 0){
                                                $(document).find('.ays_pb_pause_sound_".$id."').on('click',function(){
                                                    $('#ays_pb_close_sound_".$id."').get(0).play();
                                                    if(ays_pb_effectOut_".$id." != 'none'){
                                                        setTimeout(function(){
                                                            $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                                var audio = $('#ays_pb_close_sound_".$id."').get(0);
                                                                audio.pause();
                                                                audio.currentTime = 0;
                                                                clearInterval(timer_pb_".$id.");
                                                        }, ays_pb_animation_close_speed);
                                                    }else{
                                                        $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                        var audio = $('#ays_pb_close_sound_".$id."').get(0);
                                                        audio.pause();
                                                        audio.currentTime = 0;
                                                    }
                                                });
                                            }
                                        }
                                        //close sound end

                                        var time_pb_str_".$id." = $(document).find('.ays_pb_timer_".$id." span').attr('data-ays-seconds');
                                        if (window.innerWidth < 768) {
                                            var mobileTimer = +$(document).find('.ays_pb_timer_".$id." span').attr('data-ays-mobile-seconds');
                                            $(document).find('.ays_pb_timer_".$id." span').html(mobileTimer);
                                            time_pb_str_".$id." = mobileTimer;
                                        }
                                        var time_pb_".$id." = parseInt(time_pb_str_".$id.");
                                        if(time_pb_".$id." !== undefined){ 
                                            if(time_pb_".$id." !== 0){
                                                var timer_pb_".$id." = setInterval(function(){
                                                    let newTime_pb_".$id." = time_pb_".$id."--;
                                                    $(document).find('.ays_pb_timer_".$id." span').text(newTime_pb_".$id.");
                                                    $(document).find('.ays-pb-modal_".$id."').css({
                                                        'animation-duration': ays_pb_animation_close_seconds + 's'
                                                    }); 
                                                    if(newTime_pb_".$id." <= 0){
                                                        $(document).find('.ays-pb-modal-close_".$id."').trigger('click');
                                                        $(document).find('.ays-pb-modal_".$id."').attr('class', '".$modal_class." ays-pb-modal_".$id." ".$custom_class." '+ays_pb_effectOut_".$id.");
                                                        $modal_close_additional_js
                                                        if(ays_pb_effectOut_".$id." != 'none'){
                                                            setTimeout(function(){
                                                                $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                                ays_flag = true;
                                                            }, ays_pb_animation_close_speed);
                                                        }else{
                                                            $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                            ays_flag = true;
                                                        }
                                                        if ('". $ays_pb_check_sound ."' === 'on' && typeof sound_src !== 'undefined'){
                                                            var audio = $('#ays_pb_sound_".$id."').get(0);
                                                            audio.pause();
                                                            audio.currentTime = 0;
                                                            clearInterval(timer_pb_".$id.");
                                                        }
                                                        if ('". $ays_pb_check_anim_speed ."' && typeof close_sound_src !== 'undefined' && '". $ays_pb_check_sound ."' === 'on'){
                                                            if(checkAnimSpeed !== 0){
                                                                $('#ays_pb_close_sound_".$id."').get(0).play();
                                                                if(ays_pb_effectOut_".$id." != 'none'){
                                                                    setTimeout(function(){
                                                                        $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                                        ays_flag = true;
                                                                            var audio = $('#ays_pb_close_sound_".$id."').get(0);
                                                                            audio.pause();
                                                                            audio.currentTime = 0;
                                                                            clearInterval(timer_pb_".$id.");
                                                                    }, ays_pb_animation_close_speed);
                                                                }else{
                                                                    $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                                        ays_flag = true;
                                                                        var audio = $('#ays_pb_close_sound_".$id."').get(0);
                                                                        audio.pause();
                                                                        audio.currentTime = 0;
                                                                }
                                                            }
                                                        }
                                                    }
                                                    $(document).find('.ays-pb-modal-close_".$id."').one('click', function(){
                                                        if (pbViewsFlag_".$id.") {
                                                            var pb_id = ".$id.";

                                                            $.ajax({
                                                                url: pbLocalizeObj.ajax,
                                                                method: 'POST',
                                                                dataType: 'text',
                                                                data: {
                                                                    id: pb_id,
                                                                    action: 'ays_increment_pb_views',
                                                                },
                                                            });

                                                            pbViewsFlag_".$id." = false;
                                                        }
                                                        $(document).find('.av_pop_modals_".$id."').css('pointer-events', 'none');
                                                        $(document).find('.ays-pb-modal_".$id."').attr('class', '".$modal_class." ays-pb-modal_".$id." ".$ays_pb_show_scrollbar_class."  ".$custom_class." ays-pb-bg-styles_" . $id . " ays-pb-border-mobile_".$id." '+ays_pb_effectOut_".$id.");
                                                        $(this).parents('.ays-pb-modals').find('iframe').each(function(){
                                                            var key = /https:\/\/www.youtube.com/;
                                                            var src = $(this).attr('src');
                                                            $(this).attr('src', $(this).attr('src'));
                                                        });
                                                        $(this).parents('.ays-pb-modals').find('video.wp-video-shortcode').each(function(){
                                                            if(typeof $(this).get(0) != 'undefined'){
                                                                if ( ! $(this).get(0).paused ) {
                                                                    $(this).get(0).pause();
                                                                }

                                                            }
                                                        });
                                                        $(this).parents('.ays-pb-modals').find('audio.wp-audio-shortcode').each(function(){
                                                            if(typeof $(this).get(0) != 'undefined'){
                                                                if ( ! $(this).get(0).paused ) {
                                                                    $(this).get(0).pause();
                                                                }

                                                            }
                                                        });
                                                        $modal_close_additional_js
                                                        if(ays_pb_effectOut_".$id." != 'none'){
                                                            setTimeout(function(){ 
                                                                $(document).find('.ays-pb-modal_".$id."').css('display', 'none'); 
                                                                ays_flag = true;
                                                            }, ays_pb_animation_close_speed);  
                                                        }else{
                                                            $(document).find('.ays-pb-modal_".$id."').css('display', 'none'); 
                                                            ays_flag = true;
                                                        }
                                                        $(document).find('#ays-pb-screen-shade_".$id."').css({'opacity': '0', 'display': 'none'});
                                                        clearInterval(timer_pb_".$id.");
                                                    });

                                                    var ays_pb_flag =  true;
                                                    $(document).on('keydown', function(event) { 
                                                        if('".$close_popup_esc_flag."' && ays_pb_flag){
                                                            var escClosingPopups = $(document).find('.ays-pb-close-popup-with-esc:visible');
                                                            if (event.keyCode == 27) {
                                                                var topmostPopup = escClosingPopups.last();
                                                                topmostPopup.find('.ays-pb-modal-close_".$id."').trigger('click');
                                                                ays_pb_flag = false;
                                                                if ('". $ays_pb_check_sound ."' === 'on' && typeof sound_src !== 'undefined'){
                                                                    var audio = $('#ays_pb_sound_".$id."').get(0);
                                                                    audio.pause();
                                                                    audio.currentTime = 0;
                                                                    clearInterval(timer_pb_".$id.");
                                                                }
                                                                if(checkAnimSpeed && typeof close_sound_src !== 'undefined' && '". $ays_pb_check_sound ."' === 'on'){
                                                                    if(checkAnimSpeed !== 0){
                                                                        $('#ays_pb_close_sound_".$id."').get(0).play();
                                                                        if(ays_pb_effectOut_".$id." != 'none'){
                                                                            setTimeout(function(){
                                                                                $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                                                    var audio = $('#ays_pb_close_sound_".$id."').get(0);
                                                                                    audio.pause();
                                                                                    audio.currentTime = 0;
                                                                                    clearInterval(timer_pb_".$id.");
                                                                            }, ays_pb_animation_close_speed);
                                                                        }else{
                                                                            $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                                            var audio = $('#ays_pb_close_sound_".$id."').get(0);
                                                                            audio.pause();
                                                                            audio.currentTime = 0;
                                                                            clearInterval(timer_pb_".$id.");
                                                                        }
                                                                    }
                                                                }
                                                            } 
                                                        } else {
                                                            ays_pb_flag = true;
                                                        }
                                                    });
                                                }, 1000);
                                                if('".$ays_pb_action_buttons_type."' != 'both'){
                                                    if(closePopupOverlay && '".$popupbox['onoffoverlay']."' == 'On'){
                                                        $(document).find('#ays-pb-screen-shade_".$id."').on('click', function() {
                                                            var pb_parent_div = $(this).find('.ays-pb-modals');
                                                            var pb_div = $(this).parents('.ays-pb-modals').find('.ays-pb-modal_".$id."');
                                                            if (!pb_parent_div.is(pb_div) && pb_parent_div.has(pb_div).length === 0){
                                                                $(document).find('.ays-pb-modal-close_".$id."').click();
                                                                if ('". $ays_pb_check_sound ."' === 'on' && typeof sound_src !== 'undefined'){
                                                                    var audio = $('#ays_pb_sound_".$id."').get(0);
                                                                    audio.pause();
                                                                    audio.currentTime = 0;
                                                                    clearInterval(timer_pb_".$id.");
                                                                }
                                                                if(checkAnimSpeed && typeof close_sound_src !== 'undefined' && '". $ays_pb_check_sound ."' === 'on'){
                                                                    if(checkAnimSpeed !== 0){
                                                                        $('#ays_pb_close_sound_".$id."').get(0).play();
                                                                        if(ays_pb_effectOut_".$id." != 'none'){
                                                                            setTimeout(function(){
                                                                                $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                                                    var audio = $('#ays_pb_close_sound_".$id."').get(0);
                                                                                    audio.pause();
                                                                                    audio.currentTime = 0;
                                                                            }, ays_pb_animation_close_speed);
                                                                        }else{
                                                                            $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                                            var audio = $('#ays_pb_close_sound_".$id."').get(0);
                                                                            audio.pause();
                                                                            audio.currentTime = 0;
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        });
                                                    }
                                                }else{
                                                    if(closePopupOverlay && '".$popupbox['onoffoverlay']."' == 'On'){
                                                        $(document).find('.av_pop_modals_".$id."').on('click', function(e) {
                                                            var pb_parent_div = $(this);
                                                            var pb_div = $(this).find('.ays-pb-modal_".$id."');
                                                            if (!pb_div.is(e.target) && pb_div.has(e.target).length === 0){
                                                                $(document).find('.ays-pb-modal-close_".$id."').click();
                                                                if ('". $ays_pb_check_sound ."' === 'on' && typeof sound_src !== 'undefined'){
                                                                    var audio = $('#ays_pb_sound_".$id."').get(0);
                                                                    audio.pause();
                                                                    audio.currentTime = 0;
                                                                    clearInterval(timer_pb_".$id.");
                                                                }
                                                                if(checkAnimSpeed && typeof close_sound_src !== 'undefined' && '". $ays_pb_check_sound ."' === 'on'){
                                                                    if(checkAnimSpeed !== 0){
                                                                        $('#ays_pb_close_sound_".$id."').get(0).play();
                                                                        if(ays_pb_effectOut_".$id." != 'none'){
                                                                            setTimeout(function(){
                                                                                $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                                                    var audio = $('#ays_pb_close_sound_".$id."').get(0);
                                                                                    audio.pause();
                                                                                    audio.currentTime = 0;
                                                                                    clearInterval(timer_pb_".$id.");
                                                                            }, ays_pb_animation_close_speed);
                                                                        }else{
                                                                            $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                                            var audio = $('#ays_pb_close_sound_".$id."').get(0);
                                                                            audio.pause();
                                                                            audio.currentTime = 0;
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        });
                                                    }
                                                }
                                            } else {
                                                $(document).find('.ays_pb_timer_".$id."').css('display','none');
                                                $(document).find('.ays-pb-modal_".$id."').css({
                                                    'animation-duration': ays_pb_animation_close_seconds + 's'
                                                }); 
                                                $(document).find('.ays-pb-modal-close_".$id."').one('click', function(){
                                                    if (pbViewsFlag_".$id.") {
                                                        var pb_id = ".$id.";

                                                        $.ajax({
                                                            url: pbLocalizeObj.ajax,
                                                            method: 'POST',
                                                            dataType: 'text',
                                                            data: {
                                                                id: pb_id,
                                                                action: 'ays_increment_pb_views',
                                                            },
                                                        });

                                                        pbViewsFlag_".$id." = false;
                                                    }
                                                    $(document).find('.av_pop_modals_".$id."').css('pointer-events', 'none');
                                                    $(document).find('.ays-pb-modal_".$id."').attr('class', '".$modal_class." ".$ays_pb_show_scrollbar_class." ays-pb-modal_".$id." ".$custom_class." ays-pb-bg-styles_" . $id . " ays-pb-border-mobile_".$id." '+ays_pb_effectOut_".$id.");
                                                    $(this).parents('.ays-pb-modals').find('iframe').each(function(){
                                                        var key = /https:\/\/www.youtube.com/;
                                                        var src = $(this).attr('src');
                                                        $(this).attr('src', $(this).attr('src'));
                                                    });
                                                    $(this).parents('.ays-pb-modals').find('video.wp-video-shortcode').each(function(){
                                                        if(typeof $(this).get(0) != 'undefined'){
                                                            if ( ! $(this).get(0).paused ) {
                                                                $(this).get(0).pause();
                                                            }

                                                        }
                                                    });
                                                    $(this).parents('.ays-pb-modals').find('audio.wp-audio-shortcode').each(function(){
                                                        if(typeof $(this).get(0) != 'undefined'){
                                                            if ( ! $(this).get(0).paused ) {
                                                                $(this).get(0).pause();
                                                            }

                                                        }
                                                    });
                                                    if(ays_pb_effectOut_".$id." != 'none'){
                                                        setTimeout(function(){
                                                            $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                            $(document).find('.av_pop_modals_".$id."').css('display', 'none');
                                                            ays_flag = true;
                                                        }, ays_pb_animation_close_speed);  
                                                    }else{
                                                        $(document).find('.ays-pb-modal_".$id."').css('display', 'none');
                                                        $(document).find('.av_pop_modals_".$id."').css('display', 'none');
                                                        ays_flag = true;
                                                    }
                                                    $modal_close_additional_js
                                                    $(document).find('#ays-pb-screen-shade_".$id."').css({'opacity': '0', 'display': 'none'});
                                                    });
                                                }
                                            }


                                            if(isPageScrollDisabled){
                                                $(document).find('body').addClass('pb_disable_scroll_".$id."');
                                                $(document).find('html').addClass('pb_disable_scroll_".$id."');

                                                jQuery(document).on('click', '.ays-pb-modal-close_".$id."', function() {
                                                    $(document).find('body').removeClass('pb_disable_scroll_".$id."');
                                                    $(document).find('html').removeClass('pb_disable_scroll_".$id."');
                                                });
                                            }

                                            if ('".$popupbox['onoffoverlay']."' != 'On'){
                                                $(document).find('#ays-pb-screen-shade_".$id."').css({'opacity': '0', 'display': 'none !important', 'pointer-events': 'none'});
                                                $(document).find('.ays-pb-modal_".$id."').css('pointer-events', 'auto');
                                                $(document).find('.av_pop_modals_".$id."').css('pointer-events','none');
                                            };
                                            if('".$enable_close_button_delay_for_mobile."' == 'true' && window.innerWidth < 768){
                                                if(".$close_button_delay_for_mobile." != 0 && '".$closeButton."' != 'on'){
                                                    $(document).find('.ays-pb-modal-close_".$id."').css({'display': 'none'});
                                                    setTimeout(function(){ 
                                                        $(document).find('.ays-pb-modal-close_".$id."').css({'display': 'block'});
                                                    },". $close_button_delay_for_mobile .");
                                                }
                                            }
                                            else{
                                                if(".$close_button_delay." != 0 && '".$closeButton."' != 'on'){
                                                    $(document).find('.ays-pb-modal-close_".$id."').css({'display': 'none'});
                                                    setTimeout(function(){ 
                                                        $(document).find('.ays-pb-modal-close_".$id."').css({'display': 'block'});
                                                    },". $close_button_delay .");
                                                }
                                            }
                                        }
                                    // if(".$ays_pb_autoclose." == 0){
                                        if(closePopupOverlay && '".$popupbox['onoffoverlay']."' == 'On'){
                                            $(document).find('.av_pop_modals_".$id."').on('click', function(e) {
                                                var pb_parent = $(this);
                                                var pb_div = $(this).find('.ays-pb-modal_".$id."');
                                                if (!pb_div.is(e.target) && pb_div.has(e.target).length === 0){
                                                    $(document).find('.ays-pb-modal-close_".$id."').click();
                                                }
                                            });
                                        }
                                        var ays_pb_flag = true;
                                        $(document).on('keydown', function(event) { 
                                            if('".$close_popup_esc_flag."' && ays_pb_flag){
                                                var escClosingPopups = $(document).find('.ays-pb-close-popup-with-esc:visible');
                                                if (event.keyCode == 27) {
                                                    var topmostPopup = escClosingPopups.last();
                                                    topmostPopup.find('.ays-pb-modal-close_".$id."').trigger('click');
                                                    ays_pb_flag = false;
                                                }
                                            } else {
                                                ays_pb_flag = true;
                                            }
                                        });
                                    // }
                                });
                                if ('".$popupbox['onoffoverlay']."' != 'On'){
                                    $(document).find('#ays-pb-screen-shade_".$id."').css({'opacity': '0', 'display': 'none !important', 'pointer-events': 'none'});
                                    $(document).find('.ays-pb-modal_".$id."').css('pointer-events', 'auto');
                                    $(document).find('.av_pop_modals_".$id."').css('pointer-events','none');
                                };
                                if('".$ays_pb_action_buttons_type."' != 'both'){
                                    if($(document).find('.ays-pb-modals video').hasClass('wp-video-shortcode')){
                                        var videoWidth  = $(document).find('.ays-pb-modals video.wp-video-shortcode').attr('width');
                                        var videoHeight = $(document).find('.ays-pb-modals video.wp-video-shortcode').attr('height');
                                        setTimeout(function(){
                                            $(document).find('.ays-pb-modals .wp-video').removeAttr('style');
                                            $(document).find('.ays-pb-modals .mejs-container').removeAttr('style');
                                            $(document).find('.ays-pb-modals video.wp-video-shortcode').removeAttr('style');

                                            $(document).find('.ays-pb-modals .wp-video').css({'width': '100%'});
                                            $(document).find('.ays-pb-modals .mejs-container').css({'width': '100%','height': videoHeight + 'px'});
                                            $(document).find('.ays-pb-modals video.wp-video-shortcode').css({'width': '100%','height': videoHeight + 'px'});
                                        },1000);
                                    }
                                    if($(document).find('.ays-pb-modals iframe').attr('style') != ''){
                                        setTimeout(function(){
                                            $(document).find('.ays-pb-modals iframe').removeAttr('style');
                                        },500);
                                    }
                                }
                                if('".$autoclose_on_video_completion."' == 'on') {
                                    var video = $(document).find('video.wp-video-shortcode');
                                    for (let i = 0; i < video.length; i++) {
                                        video[i].addEventListener('ended', function() {
                                            if ($(this).next().val() === 'on') {
                                                $(this).parents('.ays_video_window').find('.close-image-btn').trigger('click');
                                            }
                                        });
                                    }
                                }
                            })( jQuery );
                        });
                    </script>";
            }

            if ($popupbox['onoffoverlay'] != 'On'){
                $popupbox_view .= "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        jQuery(document).find('#ays-pb-screen-shade_".$id."').css({'opacity': '0', 'display': 'none !important', 'pointer-events': 'none'});
                        jQuery(document).find('.ays-pb-modal_".$id."').css('pointer-events', 'auto');
                        jQuery(document).find('.av_pop_modals_".$id."').css('pointer-events','none');
                    });
                </script>";
            }

            if (($close_button_delay != 0 || $close_button_delay_for_mobile != 0) && $closeButton != 'on') {
                
                $popupbox_view .= "
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var closeBtnDelay = ".$close_button_delay.";
                        var openDelay = ".$ays_pb_delay.";
                        var scrollTop = ".$ays_pb_scroll_top.";
                        if (window.innerWidth < 768) {
                            if (" . $enable_open_delay_mobile . ") {
                                openDelay = ".$ays_pb_open_delay_mobile.";
                            }
                            if (" . $enable_scroll_top_mobile . ") {
                                scrollTop = ".$ays_pb_scroll_top_mobile.";
                            }
                            if (" . $enable_close_button_delay_for_mobile . ".toString() == 'true') {
                                closeBtnDelay = ".$close_button_delay_for_mobile.";
                            }
                        }

                        closeBtnDelay += Math.floor(openDelay);
                        if (!scrollTop) {
                            jQuery(document).find('.ays-pb-modal-close_".$id."').css({'display': 'none'});
                            setTimeout(function(){ 
                                jQuery(document).find('.ays-pb-modal-close_".$id."').css({'display': 'block'});
                            }, closeBtnDelay);
                        }
                    });
                </script>";
            }

            if($ays_pb_hover_show_close_btn){
                $popupbox_view .= "
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        modernMinimal = jQuery(document).find('.ays-pb-modal_".$id."').data('name');
                        if(modernMinimal != 'modern_minimal'){
                            jQuery(document).find('.ays-pb-modal-close_".$id."').hide();
                                jQuery(document).find('.ays-pb-modal_".$id." .ays_win98_btn-close').hide();
                                jQuery(document).find('.ays-pb-modal_".$id." .ays_winxp_title-bar-close').hide();
                                jQuery(document).find('.ays-pb-modal_".$id." .ays_ubuntu_close').hide();
                            jQuery(document).find('.ays-pb-modal_".$id."').on('mouseover',function(){
                                jQuery(document).find('.ays-pb-modal-close_".$id."').show();
                                jQuery(document).find('.ays-pb-modal_".$id." .ays_win98_btn-close').show();
                                jQuery(document).find('.ays-pb-modal_".$id." .ays_winxp_title-bar-close').show();
                                jQuery(document).find('.ays-pb-modal_".$id." .ays_ubuntu_close').show();
                            });
                            jQuery(document).find('.ays-pb-modal_".$id."').on('mouseleave',function(){
                                jQuery(document).find('.ays-pb-modal-close_".$id."').hide();
                                jQuery(document).find('.ays-pb-modal_".$id." .ays_win98_btn-close').hide();
                                jQuery(document).find('.ays-pb-modal_".$id." .ays_winxp_title-bar-close').hide();
                                jQuery(document).find('.ays-pb-modal_".$id." .ays_ubuntu_close').hide();
                            });
                        }
                    });
                </script>";
            }

            $popupbox_view .= '
                <script>
                    if(typeof aysPopupOptions === "undefined"){
                        var aysPopupOptions = [];
                    }
                    aysPopupOptions["' . $id . '"]  = "' . base64_encode( json_encode( array(
                        "popupbox"       => $popupbox,
                        ) ) ) . '";
                </script>';

            return $popupbox_view;
        }

    }

    public function setPopupPosition( $id, $position, $view_type, $isCloseBtnOff, $pb_margin, $pb_height ) {
        $popupbox_view = "";
        switch ( $position){
                    
            case "center-center":
                $popupbox_view = "jQuery(document).find('.ays-pb-modal_".$id."').css({'top': '0', 'right': '0', 'bottom': '0', 'left': '0'});";
                break;
            case "left-top":
                if(($view_type === 'image' || $view_type === 'minimal')  && $isCloseBtnOff){
                    $ays_pb_conteiner_pos = "35px";
                }else{
                    $ays_pb_conteiner_pos = 0;
                }
                $popupbox_view  = "jQuery(document).find('.ays-pb-modal_".$id."').css({'top': '".$ays_pb_conteiner_pos."',  'left': '0','right': 'unset','bottom':'unset', 'margin': '".$pb_margin."px'});";
                break;
            case "top-center":
                if(($view_type === 'image' || $view_type === 'minimal') && $isCloseBtnOff){
                    $ays_pb_conteiner_pos = "35px";
                }else{
                    $ays_pb_conteiner_pos = 0;
                }
                $popupbox_view = "jQuery(document).find('.ays-pb-modal_".$id."').css({'top': '".$ays_pb_conteiner_pos."',  'left': '0','right': '0','bottom':'unset', 'margin': '".$pb_margin."px auto'});";
                break;    
            case "right-top":
                if(($view_type === 'image' || $view_type === 'minimal') && $isCloseBtnOff){
                    $ays_pb_conteiner_pos = "35px";
                }else{
                    $ays_pb_conteiner_pos = 0;
                }
                $popupbox_view = "jQuery(document).find('.ays-pb-modal_".$id."').css({'top': '".$ays_pb_conteiner_pos."', 'right': '0','left':'unset','bottom':'unset', 'margin': '".$pb_margin."px'});";
                break;
            case "left-center":
                $popupbox_view = "var popupHeight = ".($pb_height).";
                                    var userScreenHeight = (jQuery(window).height());
                                    var result = (userScreenHeight - popupHeight)/2 + 'px';
                                    jQuery(document).find('.ays-pb-modal_".$id."').css({'top': result,  'left': '0','right': 'unset','bottom':'unset', 'margin': '".$pb_margin."px'});";
                break; 
            case "right-center":
                $popupbox_view = "var popupHeight = ".($pb_height/2).";
                                    var userScreenHeight = (jQuery(window).height()/2);
                                    var result = (userScreenHeight - popupHeight) + 'px';
                                    jQuery(document).find('.ays-pb-modal_".$id."').css({'top': result,  'left': 'unset','right': '0','bottom':'unset', 'margin': '".$pb_margin."px'});";
                break;       
            case "right-bottom":
                if($view_type === 'image' || $view_type === 'minimal'){
                    $ays_pb_conteiner_pos = "35px";
                }else{
                    $ays_pb_conteiner_pos = 0;
                }
                $popupbox_view = "jQuery(document).find('.ays-pb-modal_".$id."').css({'right': '0', 'bottom': '".$ays_pb_conteiner_pos."', 'left': 'unset','top':'unset', 'margin': '".$pb_margin."px'});";
                break;
            case "center-bottom":
                if($view_type === 'image' || $view_type === 'minimal'){
                    $ays_pb_conteiner_pos = "35px";
                }else{
                    $ays_pb_conteiner_pos = 0;
                }
                $popupbox_view = "jQuery(document).find('.ays-pb-modal_".$id."').css({'top': 'unset',  'left': '0','right': '0','bottom':'".$ays_pb_conteiner_pos."', 'margin': '".$pb_margin."px auto'});";
                break;    
            case "left-bottom":
                if($view_type === 'image' || $view_type === 'minimal'){
                    $ays_pb_conteiner_pos = "35px";
                }else{
                    $ays_pb_conteiner_pos = 0;
                }
                $popupbox_view = "jQuery(document).find('.ays-pb-modal_".$id."').css({ 'bottom': '".$ays_pb_conteiner_pos."', 'left': '0', 'top':'unset','right':'unset', 'margin': '".$pb_margin."px'});";
                break;
            default:
                $popupbox_view = "jQuery(document).find('.ays-pb-modal_".$id."').css({'top': '0', 'right': '0', 'bottom': '0', 'left': '0'});";
                break;
        }

        return $popupbox_view;
    }


    public static function ays_autoembed( $content ) {
        global $wp_embed;
        $content = stripslashes( wpautop( $content ) );
        $content = $wp_embed->autoembed( $content );
        if ( strpos( $content, '[embed]' ) !== false ) {
            $content = $wp_embed->run_shortcode( $content );
        }
        $content = do_shortcode( $content );
        return $content;
    }

    public function ays_has_shortcode_in_posts($id){

	    if (isset(get_post()->post_content)) {
            $ays_has_shortcode = strpos(get_post()->post_content, '[ays_pb id=' . $id . '');
            if ($ays_has_shortcode !== false) {
                return true;
            }
        } else {
            return false;
        }
    }

    public function ays_shortcodes_show_all(){
        global $wpdb;
        global $wp;
        global $wp_query;

        $woo = in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
        if ($woo) {
            //For woocommerce shop page 
            if(is_shop()){
                $wc_version = intval(get_plugin_data( WP_PLUGIN_DIR . "/woocommerce/woocommerce.php" )['Version']);
                if ($wc_version >= 3) {
                    $post_id = wc_get_page_id('shop');
                } else {
                    $post_id = woocommerce_get_page_id('shop');
                }
            }else{
                $post_id = isset($wp_query->post->ID) && $wp_query->post->ID != '' ? $wp_query->post->ID : null;
            }
        }else{
            $post_id = isset($wp_query->post->ID) && $wp_query->post->ID != '' ? $wp_query->post->ID : null;
        }

        $page_url = home_url(add_query_arg(array($_GET), $wp->request));
        $home_url = get_site_url();

        $sql2 = "SELECT * FROM {$wpdb->prefix}ays_pb";
        $result2 = $wpdb->get_results($sql2, "ARRAY_A");

        if(!empty($result2)){
            foreach($result2 as $key => $i){
                $show_all = $i['show_all'];
                switch($show_all){
                    case 'no':
                        $show_popup = false;
                    break;
                    case 'yes':
                        $show_popup = true;
                    break;
                    case 'all':
                        $show_popup = true;
                    break;
                    case 'selected':
                        $show_popup = false;
                    break;
                    case 'except':
                        $show_popup = true;
                    break;
                    default:
                    $show_popup = true;
                    $show_all = 'all';
                }
                $show = array('no', 'selected');
                $options = array();
                if ($i['options'] != '' || $i['options'] != null) {
                    $options = json_decode($i['options'], true);
                }
                $ays_pb_view_place = array();
                if($show_all != 'all'){
                    if($post_id != false){
                        if (!empty($i["view_place"])) {
                            $ays_pb_view_place  = explode( '***', $i["view_place"] );
                            if(in_array($post_id."", $ays_pb_view_place)){
                                if(in_array($show_all, $show)){
                                    $show_popup = true;
                                }else{
                                    $show_popup = false;
                                }
                            }
                        }else{
                            $post = get_post($post_id);
                            $this_post_title = strval($post->post_title);
                            $except_posts = array();
                            $except_post_types = array();
                            $postType = $post->post_type;
                            
                            
                            
                            if (isset($options['except_posts']) && !empty($options['except_posts'])) {
                                $except_posts = $options['except_posts'];
                            }
                            if (isset($options['except_post_types']) && !empty($options['except_post_types'])) {
                                $except_post_types = $options['except_post_types'];
                            }
                            
                            $except_all_post_types  = ( isset( $options['all_posts'] ) && ! empty( $options['all_posts'] ) ) ?  $options['all_posts']  : array();
                            
                            if(in_array($post_id."", $except_posts)){
                                if(in_array($show_all, $show)){
                                    $show_popup = true;
                                }else{
                                    $show_popup = false;
                                }
                            }
                            elseif (!in_array( $this_post_title, $except_posts ) && in_array( $postType, $except_all_post_types )) {
                                if(in_array($show_all, $show)){
                                    $show_popup = true;
                                }else{
                                    $show_popup = false;
                                }
                            }

                            if ( $page_url == $home_url ) {
                                if(isset($options['show_on_home_page']) && $options['show_on_home_page'] == 'on'){
                                    $show_popup = true;
                                }else{
                                    $show_popup = false;
                                }
                            }
                        }

                        if( is_404() || is_category() || is_search() ) {
                            $show_popup = false;
                        }
                    }   
                }

                if ($show_popup) {
                    $is_elementor_editor_active = Ays_Pb_Data::ays_pb_is_elementor_editor_active();

                    if (!$is_elementor_editor_active) {
                        $shortcode2 = "[ays_pb id={$i['id']} w={$i['width']} h={$i['height']} ]";
                        $ays_search_shortcode = $this->ays_has_shortcode_in_posts($i['id']);
                        if ($ays_search_shortcode !== true){
                            echo do_shortcode($shortcode2);
                        }
                    }
                }
            }
        }
    }
	
	public function get_pb_by_id( $id ){
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}ays_pb WHERE id=" . absint( intval( $id ) );

        $result = $wpdb->get_row($sql, "ARRAY_A");

        return $result;
    }

    public function ays_pb_detect_mobile_device(){
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        $flag      = false;
        if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))){
            $flag = true;
        }
        return $flag;
    }

    public function ays_pb_detect_tablet_device(){
        $flag = false;
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $flag = true;
        }

        return $flag;
    }

    private function array_split($array, $pieces) {
        if ($pieces < 2)
            return array($array);
        $newCount = ceil(count($array)/$pieces);
        $a = array_slice($array, 0, $newCount);
        $b = $this->array_split(array_slice($array, $newCount), $pieces-1);
        return array_merge(array($a),$b);
    }
        
    private function hex2rgba( $color, $opacity = false ) {

        $default = 'rgba(39, 174, 96, 0.5)';
        /**
         * Return default if no color provided
         */
        if( empty( $color ) ) {
            return $default;
        }
        /**
         * Sanitize $color if "#" is provided
         */
        if ( $color[0] == '#' ) {
            $color = substr( $color, 1 );
        }

        /**
         * Check if color has 6 or 3 characters and get values
         */
        if ( strlen($color) == 6 ) {
            $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
        } elseif ( strlen( $color ) == 3 ) {
            $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
        } else {
            return $default;
        }

        /**
         * [$rgb description]
         * @var array
         */
        $rgb =  array_map( 'hexdec', $hex );
        /**
         * Check if opacity is set(rgba or rgb)
         */
        if( $opacity ) {
            if( abs( $opacity ) > 1 )
                $opacity = 1.0;
                $output = 'rgba( ' . implode( "," ,$rgb ) . ',' . $opacity . ' )';
        } else {
            $output = 'rgb( ' . implode( "," , $rgb ) . ' )';
        }
        /**
         * Return rgb(a) color string
         */
        return $output;
    }

}
