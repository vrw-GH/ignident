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
 * @subpackage Ays_Pb/public/partials
 * @author     AYS Pro LLC <info@ays-pro.com>
 */
class Ays_Pb_Public_Templates {

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

    private $close_circle_icon='<svg class="ays_pb_material_close_circle_icon" xmlns="https://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24" alt="Pop-up Close"><path d="M0 0h24v24H0V0z" fill="none" opacity=".87"/><path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.59-13L12 10.59 8.41 7 7 8.41 10.59 12 7 15.59 8.41 17 12 13.41 15.59 17 17 15.59 13.41 12 17 8.41z"/></svg>';
    private $volume_up_icon='<svg class="ays_pb_fa_volume" xmlns="https://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="36"><path d="M0 0h24v24H0z" fill="none"/><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/></svg>';
    private static $facebook_scripts_enqueued = false;

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

	}

	
	public function ays_pb_template_default( $attr ){
        $popup = $this->ays_pb_set_popup_options($attr);
        $options = $popup['options'];

        $message_data = $this->ays_pb_generate_message_variables_arr($popup['ays_pb_title'], $options);

        $popup['ays_pb_custom_html'] = Ays_Pb_Data::replace_message_variables( $popup['ays_pb_custom_html'], $message_data );

        // Title text shadow
        $title_text_shadow = $this->ays_pb_generate_title_text_shadow_styles($options);

        // Box shadow
        $box_shadow = $this->ays_pb_generate_box_shadow_styles($options);

        //popup box font-family
        $ays_pb_font_family  = (isset($options->pb_font_family) && $options->pb_font_family != '') ? stripslashes( esc_attr($options->pb_font_family) ) : '';

        // Font Size 
        $pb_font_size = (isset($options->pb_font_size) && $options->pb_font_size != '') ? absint($options->pb_font_size) : 13;

        //close button hover text
        $ays_pb_close_button_hover_text = (isset($options->close_button_hover_text) && $options->close_button_hover_text != '') ? stripslashes( esc_attr($options->close_button_hover_text) ) : "";

        //Bg image position
        $pb_bg_image_position = (isset($options->pb_bg_image_position) && $options->pb_bg_image_position != "") ? str_ireplace('-', ' ', $options->pb_bg_image_position) : 'center center';

        $pb_bg_image_sizing = (isset($options->pb_bg_image_sizing) && $options->pb_bg_image_sizing != "") ? stripslashes( esc_attr($options->pb_bg_image_sizing) ) : 'cover';

        //Background Gradient
        $background_gradient = (!isset($options->enable_background_gradient)) ? 'off' : stripslashes( esc_attr($options->enable_background_gradient) );
        $pb_gradient_direction = (!isset($options->pb_gradient_direction)) ? 'horizontal' : stripslashes( esc_attr($options->pb_gradient_direction) );
        $background_gradient_color_1 = (!isset($options->background_gradient_color_1)) ? "#000000" : stripslashes( esc_attr($options->background_gradient_color_1) );
        $background_gradient_color_2 = (!isset($options->background_gradient_color_2)) ? "#fff" : stripslashes( esc_attr($options->background_gradient_color_2) );
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
        if($popup['ays_pb_bg_image'] !== ''){
            $popup['ays_pb_bg_image'] = 'background-image: url('.$popup['ays_pb_bg_image'].');
                                background-repeat: no-repeat;
                                background-size: '.$pb_bg_image_sizing.';
                                background-position: '. $pb_bg_image_position .';';
        } elseif ($background_gradient == 'on' && $popup['ays_pb_bg_image'] == '') {
            $popup['ays_pb_bg_image'] = "background-image: linear-gradient(".$pb_gradient_direction.",".$background_gradient_color_1.",".$background_gradient_color_2.");";
        }

        //popup full screen
        $ays_pb_full_screen  = (isset($options->enable_pb_fullscreen) && $options->enable_pb_fullscreen == 'on') ? 'on' : 'off';

        //Show Popup Title
        $show_popup_title = $popup['show_title'] == "On" ? 'block' : 'none';

        //Show Popup Descirtion
        $show_popup_desc = $popup['show_desc'] == "On" ? 'block' : 'none';

        //Show Popup Title Mobile
        $show_title_mobile_class = $popup['show_title_mobile'] == 'On' ? 'ays_pb_show_title_on_mobile' : 'ays_pb_hide_title_on_mobile';
        
        //Show Popup Description Mobile
        $show_desc_mobile_class = $popup['show_desc_mobile']  == 'On' ? 'ays_pb_show_desc_on_mobile' : 'ays_pb_hide_desc_on_mobile';

        if ($popup['ays_pb_title'] != '') {
            $popup['ays_pb_title'] = "<h2 class='" . $show_title_mobile_class . " ays_pb_title_styles_" . $popup['id'] . "' style='color:" . $popup['ays_pb_textcolor'] . " !important; font-family:$ays_pb_font_family; {$title_text_shadow}; font-size: 24px; margin: 0; font-weight: normal; display: " . $show_popup_title . "'>" . $popup['ays_pb_title'] . "</h2>";
        }

        if ($popup['ays_pb_autoclose'] > 0) {
            if ($popup['ays_pb_delay'] != 0 && ($popup['ays_pb_autoclose'] < $popup['ays_pb_delay_second'] || $popup['ays_pb_autoclose'] >= $popup['ays_pb_delay_second']) ) {
                $popup['ays_pb_autoclose'] += floor($popup['ays_pb_delay_second']);
            }
        }

        if ($popup['ays_pb_description'] != '') {
            $content_desktop = Ays_Pb_Public::ays_autoembed( $popup['ays_pb_description'] );
            $popup['ays_pb_description'] = "<div class='ays_pb_description " . $show_desc_mobile_class . "' style='font-size:{$pb_font_size}px; display:" . $show_popup_desc . "'>".$content_desktop."</div>";
        }

        if($popup['ays_pb_action_buttons_type'] == 'both' || $popup['ays_pb_action_buttons_type'] == 'pageLoaded'){
            $ays_pb_flag = "data-ays-flag='false'";
        }
        if($popup['ays_pb_action_buttons_type'] == 'clickSelector'){
            $ays_pb_flag = "data-ays-flag='true'";
        }
        if ( $popup['closeButton'] == "on" ){
            $popup['closeButton'] = "ays-close-button-on-off";
        } else { $popup['closeButton'] = ""; }

        //popup width percentage
        $popup_width_by_percentage_px = (isset($options->popup_width_by_percentage_px) && $options->popup_width_by_percentage_px != '') ? stripslashes( esc_attr($options->popup_width_by_percentage_px) ) : 'pixels';
        if(isset($popup['ays_pb_width']) && $popup['ays_pb_width'] != ''){
            if ($popup_width_by_percentage_px && $popup_width_by_percentage_px == 'percentage') {
                if (absint(intval($popup['ays_pb_width'])) > 100 ) {
                    $pb_width = '100%';
                }else{
                    $pb_width = $popup['ays_pb_width'] . '%';
                }
            }else{
                $pb_width = $popup['ays_pb_width'] . 'px';
            }
        }else{
            $pb_width = '100%';
        }

        //pb full screen
        $pb_height = '';
        if($ays_pb_full_screen == 'on'){
            $pb_width = '100%';
            $popup['ays_pb_height'] = 'auto';
        }else{
            $pb_width  = $popup_width_by_percentage_px == 'percentage' ? $popup['ays_pb_width'] . '%' : $popup['ays_pb_width'] . 'px';
            $pb_height = $popup['ays_pb_height'] . 'px';
        }

        if($pb_width == '0px' ||  $pb_width == '0%'){       
            $pb_width = '100%';
        }

        if($pb_height == '0px'){       
            $pb_height = '500px';
        }

        //popup padding percentage
        $ays_pb_padding = (isset($options->popup_content_padding) && $options->popup_content_padding != '') ? $options->popup_content_padding : '20';
        $popup_padding_by_percentage_px = (isset($options->popup_padding_by_percentage_px) && $options->popup_padding_by_percentage_px != '') ? stripslashes( esc_attr($options->popup_padding_by_percentage_px) ) : 'pixels';
        if(isset($ays_pb_padding) && $ays_pb_padding != ''){
            if ($popup_padding_by_percentage_px && $popup_padding_by_percentage_px == 'percentage') {
                if (absint(intval($ays_pb_padding)) > 100 ) {
                    $pb_padding = '100%';
                }else{
                    $pb_padding = $ays_pb_padding . '%';
                }
            }else{
                $pb_padding = $ays_pb_padding . 'px';
            }
        }else{
            $pb_padding = '20px';
        }

        $ays_pb_timer_desc = $this->ays_pb_generate_hide_timer_text($popup, $options, $attr);

        // Social Media links
        $enable_social_links = (isset($options->enable_social_links) && $options->enable_social_links == "on") ? true : false;
        $social_links = (isset($options->social_links)) ? $options->social_links : array(
            'linkedin_link' => '',
            'facebook_link' => '',
            'twitter_link' => '',
            'vkontakte_link' => '',
            'youtube_link' => '',
        );
        $ays_social_links_array = array();
        
        if($social_links != ''){
            $social_link_arr = (array)$social_links;
        }else{
            $social_link_arr = $social_links;
        }

        $linkedin_link = isset($social_link_arr['linkedin_link']) && $social_link_arr['linkedin_link'] != '' ? $social_link_arr['linkedin_link'] : '';
        $facebook_link = isset($social_link_arr['facebook_link']) && $social_link_arr['facebook_link'] != '' ? $social_link_arr['facebook_link'] : '';
        $twitter_link = isset($social_link_arr['twitter_link']) && $social_link_arr['twitter_link'] != '' ? $social_link_arr['twitter_link'] : '';
        $vkontakte_link = isset($social_link_arr['vkontakte_link']) && $social_link_arr['vkontakte_link'] != '' ? $social_link_arr['vkontakte_link'] : '';
        $youtube_link = isset($social_link_arr['youtube_link']) && $social_link_arr['youtube_link'] != '' ? $social_link_arr['youtube_link'] : '';
        $instagram_link = isset($social_link_arr['instagram_link']) && $social_link_arr['instagram_link'] != '' ? $social_link_arr['instagram_link'] : '';
        $behance_link = isset($social_link_arr['behance_link']) && $social_link_arr['behance_link'] != '' ? $social_link_arr['behance_link'] : '';

        if($linkedin_link != ''){
            $ays_social_links_array['Linkedin']['link'] = $linkedin_link;
            $ays_social_links_array['Linkedin']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/linkedin.svg">';
        }
        if($facebook_link != ''){
            $ays_social_links_array['Facebook']['link'] = $facebook_link;
            $ays_social_links_array['Facebook']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/facebook.svg">';
        }
        if($twitter_link != ''){
            $ays_social_links_array['Twitter']['link'] = $twitter_link;
            $ays_social_links_array['Twitter']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/twitter-x.svg">';
        }
        if($vkontakte_link != ''){
            $ays_social_links_array['VKontakte']['link'] = $vkontakte_link;
            $ays_social_links_array['VKontakte']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/vk.svg">';
        }

        if($youtube_link != ''){
            $ays_social_links_array['Youtube']['link'] = $youtube_link;
            $ays_social_links_array['Youtube']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/youtube.svg">';
        }

        if($instagram_link != ''){
            $ays_social_links_array['Instagram']['link'] = $instagram_link;
            $ays_social_links_array['Instagram']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/instagram.svg">';
        }

        if($behance_link != ''){
            $ays_social_links_array['Behance']['link'] = $behance_link;
            $ays_social_links_array['Behance']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/behance.svg">';
        }

        // Heading for social buttons
        $social_buttons_heading = (isset($options->social_buttons_heading) && $options->social_buttons_heading != '') ? stripslashes( Ays_Pb_Public::ays_autoembed( $options->social_buttons_heading ) ) : "";

        $ays_social_links = '';

        if($enable_social_links){
            $ays_social_links .= "<div class='ays-pb-social-buttons-content'>";
                $ays_social_links .= "<div class='ays-pb-social-buttons-heading'>".$social_buttons_heading."</div>";
                $ays_social_links .= "<div class='ays-pb-social-shares'>";
                    foreach($ays_social_links_array as $media => $link){
                        $ays_social_links .= "<!-- Branded " . $media . " button -->
                            <a  href='" . $link['link'] . "'
                                target='_blank'
                                title='" . $media . " link'>
                                <div class='ays-pb-share-btn-icon'>".$link['img']."</div>
                            </a>";
                    }
                $ays_social_links .= "</div>";
            $ays_social_links .= "</div>";
        }

        //close button size 
        $close_btn_size = (isset($options->close_button_size) && $options->close_button_size != '') ? abs($options->close_button_size) : '1';

        //border style 
        $border_style = (isset($options->border_style) && $options->border_style != '') ? $options->border_style : 'solid';

        $ays_pb_sound_mute = '';

        if($popup['ays_pb_action_buttons_type'] == 'clickSelector' || $popup['ays_pb_action_buttons_type'] == 'both'){
            if(isset($options->enable_pb_sound) && $options->enable_pb_sound == "on"){
                $ays_pb_sound_mute .= "<span class='ays_pb_music_sound ays_sound_active'>
                                            ".$this->volume_up_icon."
                                    </span>";
            }else{
                $ays_pb_sound_mute = '';
            }
        }else{
            $ays_pb_sound_mute = '';
        }

        //Enable dismiss
        $enable_dismiss = ( isset($options->enable_dismiss) && $options->enable_dismiss == "on" ) ? true : false;
        $show_dismiss = 'ays_pb_display_none';
        if( $enable_dismiss ){
            $show_dismiss = '';
        }

        //Dismiss ad text
        $enable_dismiss_text = (isset($options->enable_dismiss_text) && $options->enable_dismiss_text != "") ? esc_html( stripslashes($options->enable_dismiss_text) ) : __("Dismiss ad", "ays-popup-box");
        
        //Dismiss ad text mobile
        if ( ( !isset($options->enable_dismiss_mobile) ) || (isset($options->enable_dismiss_mobile) && $options->enable_dismiss_mobile == 'off' ) ) {
            $enable_dismiss_text_mobile = $enable_dismiss_text;
        } else {
            $enable_dismiss_text_mobile = (isset($options->enable_dismiss_text_mobile) && $options->enable_dismiss_text_mobile != "") ? esc_html( stripslashes($options->enable_dismiss_text_mobile) ) : __("Dismiss ad", "ays-popup-box");
        }

        //Close button color
        $close_button_color = (isset($options->close_button_color) && $options->close_button_color != "") ? esc_attr( stripslashes( $options->close_button_color ) ) : $popup['ays_pb_textcolor'];

        //Close button hover color
        $close_button_color = (isset($options->close_button_color) && $options->close_button_color != "") ? esc_attr( stripslashes( $options->close_button_color ) ) : $popup['ays_pb_textcolor'];

        //Show scrollbar
        $options->show_scrollbar = ( isset( $options->show_scrollbar ) && $options->show_scrollbar != '' ) ? stripslashes( esc_attr($options->show_scrollbar) ) : 'off';
        $ays_pb_show_scrollbar = ( isset( $options->show_scrollbar ) && $options->show_scrollbar == 'on' ) ? true : false;

        $ays_pb_disable_scroll_on_popup_class = $this->ays_pb_generate_disable_popup_class($options);

        $ays_pb_show_scrollbar_class = '';
        if($ays_pb_show_scrollbar){
            $ays_pb_show_scrollbar_class = 'ays-pb-show-scrollbar';
        }

        $popupbox_view = "
                <div class='ays-pb-modal ays-pb-modal_".$popup['id']." ".$popup['custom_class']." ".$ays_pb_disable_scroll_on_popup_class." ".$ays_pb_show_scrollbar_class." ays-popup-box-main-box ays-pb-bg-styles-mobile_".$popup['id']." ays-pb-border-mobile_".$popup['id']."' {$ays_pb_flag} style='".$popup['ays_pb_bg_image'].";width: {$pb_width}; height: {$pb_height}; background-color:" .  $popup['ays_pb_bgcolor'] . "; color: " . $popup['ays_pb_textcolor'] . " !important; border: ".$popup['ays_pb_bordersize']."px  $border_style " .$popup['ays_pb_bordercolor']. "; border-radius: ".$popup['ays_pb_border_radius']."px;font-family:{$ays_pb_font_family};{$box_shadow};' >
                    $ays_pb_sound_mute
                    " . $popup['ays_pb_title'] . "
                    " . $popup['ays_pb_description'] . "
                " . (($popup['show_desc'] !== "On" && $popup['show_title'] !== "On") ?  '' :  '<hr class="ays-popup-hrs-default"/>')
                    
                    ."<div class='ays_content_box' style='padding: {$pb_padding}'>".
                        (($popup['ays_pb_modal_content'] == 'shortcode') ? do_shortcode($popup['ays_pb_shortcode']) : Ays_Pb_Public::ays_autoembed($popup['ays_pb_custom_html']))
                    ."</div>
                    {$ays_social_links}
                    <div class='ays-pb-dismiss-ad {$show_dismiss}' data-dismiss='' data-id='{$popup['id']}'>
                        <button id='ays_pb_dismiss_ad'>
                            <span class='ays_pb_dismiss_ad_text_pc'>".$enable_dismiss_text."</span>
                            <span class='ays_pb_dismiss_ad_text_mobile'>".$enable_dismiss_text_mobile."</span>
                        </button>
                    </div>
                    $ays_pb_timer_desc
                    <div class='ays-pb-modal-close ".$popup['closeButton']." ays-pb-modal-close_".$popup['id']." ays-pb-close-button-delay ays_pb_pause_sound_".$popup['id']."' style='color: $close_button_color !important; font-family:$ays_pb_font_family;transform:scale({$close_btn_size})' data-toggle='tooltip' title='$ays_pb_close_button_hover_text'></div>
                </div>";

		return $popupbox_view;
	}

    public function ays_pb_template_macos($attr){
        $popup = $this->ays_pb_set_popup_options($attr);
        $options = $popup['options'];

        $message_data = $this->ays_pb_generate_message_variables_arr($popup['ays_pb_title'], $options);

        $popup['ays_pb_custom_html'] = Ays_Pb_Data::replace_message_variables( $popup['ays_pb_custom_html'], $message_data );

        // Title text shadow
        $title_text_shadow = $this->ays_pb_generate_title_text_shadow_styles($options);

        //close button hover text
        $ays_pb_close_button_hover_text = (isset($options->close_button_hover_text) && $options->close_button_hover_text != '') ? stripslashes( esc_attr($options->close_button_hover_text) ) : "";
        
        // Box shadow
        $box_shadow = $this->ays_pb_generate_box_shadow_styles($options);

        //popup box font-family
        $ays_pb_font_family  = (isset($options->pb_font_family) && $options->pb_font_family != '') ? stripslashes( esc_attr($options->pb_font_family) ) : '';

        // Font Size 
        $pb_font_size = (isset($options->pb_font_size) && $options->pb_font_size != '') ? absint($options->pb_font_size) : 13;
        
        //Bg image position
        $pb_bg_image_position = (isset($options->pb_bg_image_position) && $options->pb_bg_image_position != "") ? str_ireplace('-', ' ', $options->pb_bg_image_position) : 'center center';

        $pb_bg_image_sizing = (isset($options->pb_bg_image_sizing) && $options->pb_bg_image_sizing != "") ? stripslashes( esc_attr($options->pb_bg_image_sizing) ) : 'cover';

        //Background Gradient
        $background_gradient = (!isset($options->enable_background_gradient)) ? 'off' : stripslashes( esc_attr($options->enable_background_gradient) );
        $pb_gradient_direction = (!isset($options->pb_gradient_direction)) ? 'horizontal' : stripslashes( esc_attr($options->pb_gradient_direction) );
        $background_gradient_color_1 = (!isset($options->background_gradient_color_1)) ? "#000000" : stripslashes( esc_attr($options->background_gradient_color_1) );
        $background_gradient_color_2 = (!isset($options->background_gradient_color_2)) ? "#fff" : stripslashes( esc_attr($options->background_gradient_color_2) );
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
        if($popup['ays_pb_bg_image'] !== ''){
            $popup['ays_pb_bg_image'] = 'background-image: url('.$popup['ays_pb_bg_image'].');
                                background-repeat: no-repeat;
                                background-size: '.$pb_bg_image_sizing.';
                                background-position: '. $pb_bg_image_position .';';
        } elseif ($background_gradient == 'on' && $popup['ays_pb_bg_image'] == '') {
            $popup['ays_pb_bg_image'] = "background-image: linear-gradient(".$pb_gradient_direction.",".$background_gradient_color_1.",".$background_gradient_color_2.");";
        }

        //Show Popup Title
        $show_popup_title = $popup['show_title'] == "On" ? 'block' : 'none';

        //Show Popup Descirtion
        $show_popup_desc = $popup['show_desc'] == "On" ? 'block' : 'none';

        //Show Popup Title Mobile
        $show_title_mobile_class = $popup['show_title_mobile'] == 'On' ? 'ays_pb_show_title_on_mobile' : 'ays_pb_hide_title_on_mobile';
        
        //Show Popup Description Mobile
        $show_desc_mobile_class = $popup['show_desc_mobile']  == 'On' ? 'ays_pb_show_desc_on_mobile' : 'ays_pb_hide_desc_on_mobile';

        if ($popup['ays_pb_title'] != '') {
            $popup['ays_pb_title'] = "<h2 class='" . $show_title_mobile_class . " ays_pb_title_styles_" . $popup['id'] . "' style='color:" . $popup['ays_pb_textcolor'] . " !important; font-family:$ays_pb_font_family; {$title_text_shadow}; display: " . $show_popup_title . "'>" . $popup['ays_pb_title'] . "</h2>";
        }

        if ($popup['ays_pb_autoclose'] > 0) {
            if ($popup['ays_pb_delay'] != 0 && ($popup['ays_pb_autoclose'] < $popup['ays_pb_delay_second'] || $popup['ays_pb_autoclose'] >= $popup['ays_pb_delay_second']) ) {
                $popup['ays_pb_autoclose'] += floor($popup['ays_pb_delay_second']);
            }
        }

        if ($popup['ays_pb_description'] != '') {
            $content_desktop = Ays_Pb_Public::ays_autoembed( $popup['ays_pb_description'] );
            $popup['ays_pb_description'] = "<div class='ays_pb_description " . $show_desc_mobile_class . "' style='font-size:{$pb_font_size}px; display:" . $show_popup_desc . "'>".$content_desktop."</div>";
        }

        if($popup['ays_pb_action_buttons_type'] == 'both' || $popup['ays_pb_action_buttons_type'] == 'pageLoaded'){
            $ays_pb_flag = "data-ays-flag='false'";
        }
        if($popup['ays_pb_action_buttons_type'] == 'clickSelector'){
            $ays_pb_flag = "data-ays-flag='true'";
        }

        if ( $popup['closeButton'] == "on" ){
            $popup['closeButton'] = "ays-close-button-on-off";
        } else { $popup['closeButton'] = ""; }
        
        //popup width percentage
        $popup_width_by_percentage_px = (isset($options->popup_width_by_percentage_px) && $options->popup_width_by_percentage_px != '') ? stripslashes( esc_attr($options->popup_width_by_percentage_px) ) : 'pixels';
        if(isset($popup['ays_pb_width']) && $popup['ays_pb_width'] != ''){
            if ($popup_width_by_percentage_px && $popup_width_by_percentage_px == 'percentage') {
                if (absint(intval($popup['ays_pb_width'])) > 100 ) {
                    $pb_width = '100%';
                }else{
                    $pb_width = $popup['ays_pb_width'] . '%';
                }
            }else{
                $pb_width = $popup['ays_pb_width'] . 'px';
            }
        }else{
            $pb_width = '100%';
        }

        //pb full screen
        $ays_pb_full_screen  = (isset($options->enable_pb_fullscreen) && $options->enable_pb_fullscreen == 'on') ? 'on' : 'off';
        $pb_height = '';
        if($ays_pb_full_screen == 'on'){
           $pb_width = '100%';
           $popup['ays_pb_height'] = 'auto';
        }else{
           $pb_width  = $popup_width_by_percentage_px == 'percentage' ? $popup['ays_pb_width'] . '%' : $popup['ays_pb_width'] . 'px';
           $pb_height = $popup['ays_pb_height'] . 'px';
        }

        if($pb_width == '0px' ||  $pb_width == '0%'){       
            $pb_width = '100%';
        }

        if($pb_height == '0px'){       
            $pb_height = '500px';
        }

        $ays_pb_padding = (isset($options->popup_content_padding) && $options->popup_content_padding != '') ? $options->popup_content_padding : '20';
        //popup padding percentage
        $popup_padding_by_percentage_px = (isset($options->popup_padding_by_percentage_px) && $options->popup_padding_by_percentage_px != '') ? stripslashes( esc_attr($options->popup_padding_by_percentage_px) ) : 'pixels';
        if(isset($ays_pb_padding) && $ays_pb_padding != ''){
            if ($popup_padding_by_percentage_px && $popup_padding_by_percentage_px == 'percentage') {
                if (absint(intval($ays_pb_padding)) > 100 ) {
                    $pb_padding = '100%';
                }else{
                    $pb_padding = $ays_pb_padding . '%';
                }
            }else{
                $pb_padding = $ays_pb_padding . 'px';
            }
        }else{
            $pb_padding = '20px';
        }

        $ays_pb_timer_desc = $this->ays_pb_generate_hide_timer_text($popup, $options, $attr);

        // Social Media links
        $enable_social_links = (isset($options->enable_social_links) && $options->enable_social_links == "on") ? true : false;
        $social_links = (isset($options->social_links)) ? $options->social_links : array(
            'linkedin_link' => '',
            'facebook_link' => '',
            'twitter_link' => '',
            'vkontakte_link' => '',
            'youtube_link' => '',
            'instagram_link' => '',
            'behance_link' => '',
        );
        $ays_social_links_array = array();
        
        if($social_links != ''){
            $social_link_arr = (array)$social_links;
        }else{
            $social_link_arr = $social_links;
        }

        $linkedin_link = isset($social_link_arr['linkedin_link']) && $social_link_arr['linkedin_link'] != '' ? $social_link_arr['linkedin_link'] : '';
        $facebook_link = isset($social_link_arr['facebook_link']) && $social_link_arr['facebook_link'] != '' ? $social_link_arr['facebook_link'] : '';
        $twitter_link = isset($social_link_arr['twitter_link']) && $social_link_arr['twitter_link'] != '' ? $social_link_arr['twitter_link'] : '';
        $vkontakte_link = isset($social_link_arr['vkontakte_link']) && $social_link_arr['vkontakte_link'] != '' ? $social_link_arr['vkontakte_link'] : '';
        $youtube_link = isset($social_link_arr['youtube_link']) && $social_link_arr['youtube_link'] != '' ? $social_link_arr['youtube_link'] : '';
        $instagram_link = isset($social_link_arr['instagram_link']) && $social_link_arr['instagram_link'] != '' ? $social_link_arr['instagram_link'] : '';
        $behance_link = isset($social_link_arr['behance_link']) && $social_link_arr['behance_link'] != '' ? $social_link_arr['behance_link'] : '';
        
        if($linkedin_link != ''){
            $ays_social_links_array['Linkedin']['link'] = $linkedin_link;
            $ays_social_links_array['Linkedin']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/linkedin.svg">';
        }
        if($facebook_link != ''){
            $ays_social_links_array['Facebook']['link'] = $facebook_link;
            $ays_social_links_array['Facebook']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/facebook.svg">';
        }
        if($twitter_link != ''){
            $ays_social_links_array['Twitter']['link'] = $twitter_link;
            $ays_social_links_array['Twitter']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/twitter-x.svg">';
        }
        if($vkontakte_link != ''){
            $ays_social_links_array['VKontakte']['link'] = $vkontakte_link;
            $ays_social_links_array['VKontakte']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/vk.svg">';
        }
        if($youtube_link != ''){
            $ays_social_links_array['Youtube']['link'] = $youtube_link;
            $ays_social_links_array['Youtube']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/youtube.svg">';
        }
        
        if($instagram_link != ''){
            $ays_social_links_array['Instagram']['link'] = $instagram_link;
            $ays_social_links_array['Instagram']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/instagram.svg">';
        }

        if($behance_link != ''){
            $ays_social_links_array['Behance']['link'] = $behance_link;
            $ays_social_links_array['Behance']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/behance.svg">';
        }

        $ays_social_links = '';

        // Heading for social buttons
        $social_buttons_heading = (isset($options->social_buttons_heading) && $options->social_buttons_heading != '') ? stripslashes( Ays_Pb_Public::ays_autoembed( $options->social_buttons_heading ) ) : "";

        if($enable_social_links){
            $ays_social_links .= "<div class='ays-pb-social-buttons-content'>";
                $ays_social_links .= "<div class='ays-pb-social-buttons-heading'>".$social_buttons_heading."</div>";
                $ays_social_links .= "<div class='ays-pb-social-shares'>";
                    foreach($ays_social_links_array as $media => $link){
                        $ays_social_links .= "<!-- Branded " . $media . " button -->
                            <a  href='" . $link['link'] . "'
                                target='_blank'
                                title='" . $media . " link'>
                                <div class='ays-pb-share-btn-icon'>".$link['img']."</div>
                            </a>";
                    }
                $ays_social_links .= "</div>";
            $ays_social_links .= "</div>";
        }

        $close_btn_size = (isset($options->close_button_size) && $options->close_button_size != '') ? abs($options->close_button_size): '1';

        //border style 
        $border_style = (isset($options->border_style) && $options->border_style != '') ? $options->border_style : 'solid';

        $ays_pb_sound_mute = '';

        if($popup['ays_pb_action_buttons_type'] == 'clickSelector' || $popup['ays_pb_action_buttons_type'] == 'both'){
            if(isset($options->enable_pb_sound) && $options->enable_pb_sound == "on"){
                $ays_pb_sound_mute .= "<span class='ays_pb_music_sound ays_sound_active'>
                                        ".$this->volume_up_icon."
                                    </span>";
            }else{
                $ays_pb_sound_mute = '';
            }
        }else{
            $ays_pb_sound_mute = '';
        }

        //Enable dismiss
        $enable_dismiss = ( isset($options->enable_dismiss) && $options->enable_dismiss == "on" ) ? true : false;
        $show_dismiss = 'ays_pb_display_none';
        if( $enable_dismiss ){
            $show_dismiss = '';
        }

        //Dismiss ad text
        $enable_dismiss_text = (isset($options->enable_dismiss_text) && $options->enable_dismiss_text != "") ? esc_html( stripslashes($options->enable_dismiss_text) ) : __("Dismiss ad", "ays-popup-box");
        
        //Dismiss ad text mobile
        if ( ( !isset($options->enable_dismiss_mobile) ) || (isset($options->enable_dismiss_mobile) && $options->enable_dismiss_mobile == 'off' ) ) {
            $enable_dismiss_text_mobile = $enable_dismiss_text;
        } else {
            $enable_dismiss_text_mobile = (isset($options->enable_dismiss_text_mobile) && $options->enable_dismiss_text_mobile != "") ? esc_html( stripslashes($options->enable_dismiss_text_mobile) ) : __("Dismiss ad", "ays-popup-box");
        }

        //Show scrollbar
        $options->show_scrollbar = ( isset( $options->show_scrollbar ) && $options->show_scrollbar != '' ) ? stripslashes( esc_attr($options->show_scrollbar) ) : 'off';
        $ays_pb_show_scrollbar = ( isset( $options->show_scrollbar ) && $options->show_scrollbar == 'on' ) ? true : false;

        $ays_pb_disable_scroll_on_popup_class = $this->ays_pb_generate_disable_popup_class($options);

        $ays_pb_show_scrollbar_class = '';
        if($ays_pb_show_scrollbar){
            $ays_pb_show_scrollbar_class = 'ays-pb-show-scrollbar';
        }

        $mac_view = "<div class='ays_window ays-pb-modal_".$popup['id']." ".$popup['custom_class']." ".$ays_pb_disable_scroll_on_popup_class." ".$ays_pb_show_scrollbar_class." ays-pb-bg-styles-mobile_".$popup['id']." ays-pb-border-mobile_".$popup['id']."' {$ays_pb_flag} style='width: {$pb_width}; height: {$pb_height}; ".$popup['ays_pb_bg_image']."; background-color: ".$popup['ays_pb_bgcolor']."; color: ".$popup['ays_pb_textcolor']." !important; border: ".$popup['ays_pb_bordersize']."px $border_style ".$popup['ays_pb_bordercolor']."; border-radius: ".$popup['ays_pb_border_radius']."px;font-family:{$ays_pb_font_family};{$box_shadow}'>
                         <div class='ays_topBar'>
                            <div class='".$popup['closeButton']."' data-toggle='tooltip' title='" . $ays_pb_close_button_hover_text . "'>
                                <div class='ays-pb-modal-close ays_close ays-pb-modal-close_".$popup['id']." ays-pb-close-button-delay ays_pb_pause_sound_".$popup['id']."'></div>
                            </div>
                            <div>
                            <a class='ays_hide'></a>
                            </div>
                            <div>
                            <a class='ays_fullScreen'></a>
                            </div>
                            ".$popup['ays_pb_title']."
                         </div> 
                            ".$popup['ays_pb_description']."
                         <hr/>
                         <div class='ays_text'>
                         $ays_pb_sound_mute
                            <div class='ays_text-inner'>
                                <div class='ays_content_box' style='padding: {$pb_padding}'>".
                                    (($popup['ays_pb_modal_content'] == 'shortcode') ? do_shortcode($popup['ays_pb_shortcode']) : Ays_Pb_Public::ays_autoembed($popup['ays_pb_custom_html']))
                                ."</div>
                            </div>
                         </div>  
                         {$ays_social_links}  
                         <div class='ays-pb-dismiss-ad {$show_dismiss}' data-dismiss='' data-id='".$popup['id']."'>
                            <button id='ays_pb_dismiss_ad'>
                                <span class='ays_pb_dismiss_ad_text_pc'>".$enable_dismiss_text."</span>
                                <span class='ays_pb_dismiss_ad_text_mobile'>".$enable_dismiss_text_mobile."</span>
                            </button>
                        </div>            
                         $ays_pb_timer_desc
                    </div>
                <script>
                (function($){
                    $('.ays_hide').on('click', function() {
                      $('.ays_window').css({
                        height: '".$popup['ays_pb_height']."px',
                        width: '{$pb_width}',
                        padding: '{$pb_padding}'
                      });
                    });

                    $('.ays_fullScreen').on('click', function() {
                      $('.ays_window').css({
                        height: '100vh',
                        width: '100vw',
                      });
                    });
                })(jQuery);
                </script>";
        return $mac_view;
    }
    
    public function ays_pb_template_cmd($attr){      
        $popup = $this->ays_pb_set_popup_options($attr);
        $options = $popup['options'];

        $message_data = $this->ays_pb_generate_message_variables_arr($popup['ays_pb_title'], $options);

        $popup['ays_pb_custom_html'] = Ays_Pb_Data::replace_message_variables( $popup['ays_pb_custom_html'], $message_data );

        // Title text shadow
        $title_text_shadow = $this->ays_pb_generate_title_text_shadow_styles($options);

        //close button hover text
        $ays_pb_close_button_hover_text = (isset($options->close_button_hover_text) && $options->close_button_hover_text != '') ? stripslashes( esc_attr($options->close_button_hover_text) ) : "";
        
        // Box shadow
        $box_shadow = $this->ays_pb_generate_box_shadow_styles($options);
        if ($box_shadow == '') {
            $box_shadow = 'box-shadow:4px 4px 0 rgba(0,0,0,.2)';
        }

        //popup box font-family
        $ays_pb_font_family  = (isset($options->pb_font_family) && $options->pb_font_family != '') ? stripslashes( esc_attr($options->pb_font_family) ) : '';

        // Font Size 
        $pb_font_size = (isset($options->pb_font_size) && $options->pb_font_size != '') ? absint($options->pb_font_size) : 13;
        //Bg image position
        $pb_bg_image_position = (isset($options->pb_bg_image_position) && $options->pb_bg_image_position != "") ? str_ireplace('-', ' ', $options->pb_bg_image_position) : 'center center';

        $pb_bg_image_sizing = (isset($options->pb_bg_image_sizing) && $options->pb_bg_image_sizing != "") ? stripslashes( esc_attr($options->pb_bg_image_sizing) ) : 'cover';

        //Background Gradient
        $background_gradient = (!isset($options->enable_background_gradient)) ? 'off' : stripslashes( esc_attr($options->enable_background_gradient) );
        $pb_gradient_direction = (!isset($options->pb_gradient_direction)) ? 'horizontal' : stripslashes( esc_attr($options->pb_gradient_direction) );
        $background_gradient_color_1 = (!isset($options->background_gradient_color_1)) ? "#000000" : stripslashes( esc_attr($options->background_gradient_color_1) );
        $background_gradient_color_2 = (!isset($options->background_gradient_color_2)) ? "#fff" : stripslashes( esc_attr($options->background_gradient_color_2) );
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
        if($popup['ays_pb_bg_image'] !== ''){
            $popup['ays_pb_bg_image'] = 'background-image: url('.$popup['ays_pb_bg_image'].');
                                background-repeat: no-repeat;
                                background-size: '.$pb_bg_image_sizing.';
                                background-position: '. $pb_bg_image_position .';';
        } elseif ($background_gradient == 'on' && $popup['ays_pb_bg_image'] == '') {
            $popup['ays_pb_bg_image'] = "background-image: linear-gradient(".$pb_gradient_direction.",".$background_gradient_color_1.",".$background_gradient_color_2.");";
        }

        //Show Popup Title
        $show_popup_title = $popup['show_title'] == "On" ? 'block' : 'none';

        //Show Popup Descirtion
        $show_popup_desc = $popup['show_desc'] == "On" ? 'block' : 'none';

        //Show Popup Title Mobile
        $show_title_mobile_class = $popup['show_title_mobile'] == 'On' ? 'ays_pb_show_title_on_mobile' : 'ays_pb_hide_title_on_mobile';
        
        //Show Popup Description Mobile
        $show_desc_mobile_class = $popup['show_desc_mobile']  == 'On' ? 'ays_pb_show_desc_on_mobile' : 'ays_pb_hide_desc_on_mobile';

        if ($popup['ays_pb_title'] != '') {
            $popup['ays_pb_title'] = "<h2 class='" . $show_title_mobile_class . " ays_pb_title_styles_" . $popup['id'] . "' style='color:" . $popup['ays_pb_textcolor'] . " !important; font-family:$ays_pb_font_family; {$title_text_shadow}; display: " . $show_popup_title . "'>" . $popup['ays_pb_title'] . "</h2>";
        }

        if ($popup['ays_pb_autoclose'] > 0) {
            if ($popup['ays_pb_delay'] != 0 && ($popup['ays_pb_autoclose'] < $popup['ays_pb_delay_second'] || $popup['ays_pb_autoclose'] >= $popup['ays_pb_delay_second']) ) {
                $popup['ays_pb_autoclose'] += floor($popup['ays_pb_delay_second']);
            }
        }

        if ($popup['ays_pb_description'] != '') {
            $content_desktop = Ays_Pb_Public::ays_autoembed( $popup['ays_pb_description'] );
            $popup['ays_pb_description'] = "<div class='ays_pb_description " . $show_desc_mobile_class . "' style='font-size:{$pb_font_size}px; display:" . $show_popup_desc . "'>".$content_desktop."</div>";
        }

        if($popup['ays_pb_action_buttons_type'] == 'both' || $popup['ays_pb_action_buttons_type'] == 'pageLoaded'){
            $ays_pb_flag = "data-ays-flag='false'";
        }
        if($popup['ays_pb_action_buttons_type'] == 'clickSelector'){
            $ays_pb_flag = "data-ays-flag='true'";
        }
        if ( $popup['closeButton'] == "on" ){
            $popup['closeButton'] = "ays-close-button-on-off";
        } else { $popup['closeButton'] = ""; }
        
        //popup width percentage

        $popup_width_by_percentage_px = (isset($options->popup_width_by_percentage_px) && $options->popup_width_by_percentage_px != '') ? stripslashes( esc_attr($options->popup_width_by_percentage_px) ) : 'pixels';
        if(isset($popup['ays_pb_width']) && $popup['ays_pb_width'] != ''){
            if ($popup_width_by_percentage_px && $popup_width_by_percentage_px == 'percentage') {
                if (absint(intval($popup['ays_pb_width'])) > 100 ) {
                    $pb_width = '100%';
                }else{
                    $pb_width = $popup['ays_pb_width'] . '%';
                }
            }else{
                $pb_width = $popup['ays_pb_width'] . 'px';
            }
        }else{
            $pb_width = '100%';
        }

        //pb full screen
        $ays_pb_full_screen  = (isset($options->enable_pb_fullscreen) && $options->enable_pb_fullscreen == 'on') ? 'on' : 'off';
        $pb_height = '';
        if($ays_pb_full_screen == 'on'){
           $pb_width = '100%';
           $popup['ays_pb_height'] = 'auto';
        }else{
           $pb_width  = $popup_width_by_percentage_px == 'percentage' ? $popup['ays_pb_width'] . '%' : $popup['ays_pb_width'] . 'px';
           $pb_height = $popup['ays_pb_height'] . 'px';
        }

        if($pb_width == '0px' ||  $pb_width == '0%'){       
            $pb_width = '100%';
        }

        if($pb_height == '0px'){       
            $pb_height = '500px';
        }

        $ays_pb_padding = (isset($options->popup_content_padding) && $options->popup_content_padding != '') ? $options->popup_content_padding : '20';
        //popup padding percentage
        $popup_padding_by_percentage_px = (isset($options->popup_padding_by_percentage_px) && $options->popup_padding_by_percentage_px != '') ? stripslashes( esc_attr($options->popup_padding_by_percentage_px) ) : 'pixels';
        if(isset($ays_pb_padding) && $ays_pb_padding != ''){
            if ($popup_padding_by_percentage_px && $popup_padding_by_percentage_px == 'percentage') {
                if (absint(intval($ays_pb_padding)) > 100 ) {
                    $pb_padding = '100%';
                }else{
                    $pb_padding = $ays_pb_padding . '%';
                }
            }else{
                $pb_padding = $ays_pb_padding . 'px';
            }
        }else{
            $pb_padding = '20px';
        }

        $ays_pb_timer_desc = $this->ays_pb_generate_hide_timer_text($popup, $options, $attr);

        // Social Media links
        $enable_social_links = (isset($options->enable_social_links) && $options->enable_social_links == "on") ? true : false;
        $social_links = (isset($options->social_links)) ? $options->social_links : array(
            'linkedin_link' => '',
            'facebook_link' => '',
            'twitter_link' => '',
            'vkontakte_link' => '',
            'youtube_link' => '',
            'instagram_link' => '',
            'behance_link' => '',
        );
        $ays_social_links_array = array();
        
        if($social_links != ''){
            $social_link_arr = (array)$social_links;
        }else{
            $social_link_arr = $social_links;
        }

        $linkedin_link = isset($social_link_arr['linkedin_link']) && $social_link_arr['linkedin_link'] != '' ? $social_link_arr['linkedin_link'] : '';
        $facebook_link = isset($social_link_arr['facebook_link']) && $social_link_arr['facebook_link'] != '' ? $social_link_arr['facebook_link'] : '';
        $twitter_link = isset($social_link_arr['twitter_link']) && $social_link_arr['twitter_link'] != '' ? $social_link_arr['twitter_link'] : '';
        $vkontakte_link = isset($social_link_arr['vkontakte_link']) && $social_link_arr['vkontakte_link'] != '' ? $social_link_arr['vkontakte_link'] : '';
        $youtube_link = isset($social_link_arr['youtube_link']) && $social_link_arr['youtube_link'] != '' ? $social_link_arr['youtube_link'] : '';
        $instagram_link = isset($social_link_arr['instagram_link']) && $social_link_arr['instagram_link'] != '' ? $social_link_arr['instagram_link'] : '';
        $behance_link = isset($social_link_arr['behance_link']) && $social_link_arr['behance_link'] != '' ? $social_link_arr['behance_link'] : '';
        
        if($linkedin_link != ''){
            $ays_social_links_array['Linkedin']['link'] = $linkedin_link;
            $ays_social_links_array['Linkedin']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/linkedin.svg">';
        }

        if($facebook_link != ''){
            $ays_social_links_array['Facebook']['link'] = $facebook_link;
            $ays_social_links_array['Facebook']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/facebook.svg">';
        }

        if($twitter_link != ''){
            $ays_social_links_array['Twitter']['link'] = $twitter_link;
            $ays_social_links_array['Twitter']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/twitter-x.svg">';
        }

        if($vkontakte_link != ''){
            $ays_social_links_array['VKontakte']['link'] = $vkontakte_link;
            $ays_social_links_array['VKontakte']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/vk.svg">';
        }

        if($youtube_link != ''){
            $ays_social_links_array['Youtube']['link'] = $youtube_link;
            $ays_social_links_array['Youtube']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/youtube.svg">';
        }
        
        if($instagram_link != ''){
            $ays_social_links_array['Instagram']['link'] = $instagram_link;
            $ays_social_links_array['Instagram']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/instagram.svg">';
        }

        if($behance_link != ''){
            $ays_social_links_array['Behance']['link'] = $behance_link;
            $ays_social_links_array['Behance']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/behance.svg">';
        }

        $ays_social_links = '';

        // Heading for social buttons
        $social_buttons_heading = (isset($options->social_buttons_heading) && $options->social_buttons_heading != '') ? stripslashes( Ays_Pb_Public::ays_autoembed( $options->social_buttons_heading ) ) : "";

        if($enable_social_links){
            $ays_social_links .= "<div class='ays-pb-social-buttons-content'>";
                $ays_social_links .= "<div class='ays-pb-social-buttons-heading'>".$social_buttons_heading."</div>";
                $ays_social_links .= "<div class='ays-pb-social-shares'>";
                    foreach($ays_social_links_array as $media => $link){
                        $ays_social_links .= "<!-- Branded " . $media . " button -->
                            <a  href='" . $link['link'] . "'
                                target='_blank'
                                title='" . $media . " link'>
                                <div class='ays-pb-share-btn-icon'>".$link['img']."</div>
                            </a>";
                    }
                $ays_social_links .= "</div>";
            $ays_social_links .= "</div>";
        }

        $close_btn_size = (isset($options->close_button_size) && $options->close_button_size != '') ? abs($options->close_button_size) : '1';

        //border style 
        $border_style = (isset($options->border_style) && $options->border_style != '') ? $options->border_style : 'solid';
       
        $ays_pb_sound_mute = '';

        if($popup['ays_pb_action_buttons_type'] == 'clickSelector' || $popup['ays_pb_action_buttons_type'] == 'both'){
            if(isset($options->enable_pb_sound) && $options->enable_pb_sound == "on"){
                $ays_pb_sound_mute .= "<span class='ays_pb_music_sound ays_sound_active'>
                                        ".$this->volume_up_icon."
                                    </span>";
            }else{
                $ays_pb_sound_mute = '';
            }
        }else{
            $ays_pb_sound_mute = '';
        }

        //Enable dismiss
        $enable_dismiss = ( isset($options->enable_dismiss) && $options->enable_dismiss == "on" ) ? true : false;
        $show_dismiss = 'ays_pb_display_none';
        if( $enable_dismiss ){
            $show_dismiss = '';
        }

        //Dismiss ad text
        $enable_dismiss_text = (isset($options->enable_dismiss_text) && $options->enable_dismiss_text != "") ? esc_html( stripslashes($options->enable_dismiss_text) ) : __("Dismiss ad", "ays-popup-box");
                
        //Dismiss ad text mobile
        if ( ( !isset($options->enable_dismiss_mobile) ) || (isset($options->enable_dismiss_mobile) && $options->enable_dismiss_mobile == 'off' ) ) {
            $enable_dismiss_text_mobile = $enable_dismiss_text;
        } else {
            $enable_dismiss_text_mobile = (isset($options->enable_dismiss_text_mobile) && $options->enable_dismiss_text_mobile != "") ? esc_html( stripslashes($options->enable_dismiss_text_mobile) ) : __("Dismiss ad", "ays-popup-box");
        }

        //Show scrollbar
        $options->show_scrollbar = ( isset( $options->show_scrollbar ) && $options->show_scrollbar != '' ) ? stripslashes( esc_attr($options->show_scrollbar) ) : 'off';
        $ays_pb_show_scrollbar = ( isset( $options->show_scrollbar ) && $options->show_scrollbar == 'on' ) ? true : false;

        $ays_pb_disable_scroll_on_popup_class = $this->ays_pb_generate_disable_popup_class($options);

        $ays_pb_show_scrollbar_class = '';
        if($ays_pb_show_scrollbar){
            $ays_pb_show_scrollbar_class = 'ays-pb-show-scrollbar';
        }

        $cmd_view = "<div class='ays_cmd_window ays-pb-modal_".$popup['id']." ".$popup['custom_class']." ".$ays_pb_disable_scroll_on_popup_class." ".$ays_pb_show_scrollbar_class." ays-pb-bg-styles-mobile_".$popup['id']." ays-pb-border-mobile_".$popup['id']."' {$ays_pb_flag} style='width: {$pb_width}; height: {$pb_height}; background-color: ".$popup['ays_pb_bgcolor']."; ".$popup['ays_pb_bg_image'].";  color: ".$popup['ays_pb_textcolor']." !important; border: ".$popup['ays_pb_bordersize']."px $border_style ".$popup['ays_pb_bordercolor']."; border-radius: ".$popup['ays_pb_border_radius']."px;font-family:{$ays_pb_font_family};{$box_shadow}'>
                        <header class='ays_cmd_window-header'>
                            <div class='ays_cmd_window_title'>".$popup['ays_pb_title']."</div>
                            <nav class='ays_cmd_window-controls'>
                                <ul class='ays_cmd_window-controls-ul'>
                                    <li><span class='ays_cmd_control-item ays_cmd_control-minimize ays_cmd_js-minimize'>-</span></li>
                                    <li><span class='ays_cmd_control-item ays_cmd_control-maximize ays_cmd_js-maximize'>□</span></li>
                                    <li><div class='ays_cmd_control-item ".$popup['closeButton']." ays_cmd_control-close ays-pb-modal-close_".$popup['id']." ays-pb-close-button-delay' data-toggle='tooltip' title='" . $ays_pb_close_button_hover_text . "'><span class='ays_cmd_control-item ays_cmd_control-close ays_cmd_js-close ays_pb_pause_sound_".$popup['id']."'>x</span></div></li>
                                </ul>
                            </nav>
                        </header>
                        <div class='ays_cmd_window-cursor'>
                            <span class='ays_cmd_i-cursor-indicator'>></span>
                            <span class='ays_cmd_i-cursor-underscore'></span>
                            <input type='text' disabled class='ays_cmd_window-input ays_cmd_js-prompt-input' />
                        </div>
                        ".$popup['ays_pb_description']."
                        $ays_pb_sound_mute
                        <main class='ays_cmd_window-content'>
                            <div class='ays_text'>
                                <div class='ays_text-inner'>
                                <div class='ays_content_box' style='padding: {$pb_padding}'>".
                                    (($popup['ays_pb_modal_content'] == 'shortcode') ? do_shortcode($popup['ays_pb_shortcode']) : Ays_Pb_Public::ays_autoembed($popup['ays_pb_custom_html']))
                                ."</div>
                                </div>
                             </div>             
                             {$ays_social_links} 
                            <div class='ays-pb-dismiss-ad {$show_dismiss}' data-dismiss='' data-id='".$popup['id']."'>
                                <button id='ays_pb_dismiss_ad'>
                                    <span class='ays_pb_dismiss_ad_text_pc'>".$enable_dismiss_text."</span>
                                    <span class='ays_pb_dismiss_ad_text_mobile'>".$enable_dismiss_text_mobile."</span>
                                </button>
                            </div>           
                             $ays_pb_timer_desc
                        </main>
                    </div>
                    <script>
                        (function($){
                            var prompt = {
                                window: $('.ays_cmd_window,.ays_window'),
                                shortcut: $('.ays_cmd_prompt-shortcut'),
                                input: $('.ays_cmd_js-prompt-input'),

                                init: function() {
                                    $('.ays_cmd_js-minimize').click(prompt.minimize);
                                    $('.ays_cmd_window_title').click(prompt.minimize);
                                    $('.ays_cmd_js-maximize').click(prompt.maximize);
                                    $('.ays_cmd_js-close').click(prompt.close);
                                    $('.ays_cmd_js-open').click(prompt.open);
                                    prompt.input.focus();
                                    prompt.input.blur(prompt.focus);
                                },
                                    focus: function() {
                                    prompt.input.focus();
                                },
                                minimize: function() {        
                                    prompt.window.removeClass('ays_cmd_window--maximized');
                                    prompt.window.toggleClass('ays_cmd_window--minimized');
                                },
                                maximize: function() {
                                    prompt.window.removeClass('ays_cmd_window--minimized');
                                    prompt.window.toggleClass('ays_cmd_window--maximized');
                                    prompt.focus();
                                    $(document).find('.ays_cmd_window,.ays_window').css('bottom', 0);
                                },
                                close: function() {
                                    prompt.window.addClass('ays_cmd_window--destroyed');
                                    prompt.window.removeClass('ays_cmd_window--maximized ays_cmd_window--minimized');
                                    prompt.shortcut.removeClass('ays_cmd_hidden');
                                    prompt.input.val('');
                                },
                                open: function() {
                                    prompt.window.removeClass('ays_cmd_window--destroyed');
                                    prompt.shortcut.addClass('ays_cmd_hidden');
                                    prompt.focus();
                                }
                            };
                            $(document).ready(prompt.init);
                        })(jQuery);
                    </script>";
        return $cmd_view;
    }   

    public function ays_pb_template_ubuntu($attr){     
        $popup = $this->ays_pb_set_popup_options($attr);
        $options = $popup['options'];

        $message_data = $this->ays_pb_generate_message_variables_arr($popup['ays_pb_title'], $options);

        $popup['ays_pb_custom_html'] = Ays_Pb_Data::replace_message_variables( $popup['ays_pb_custom_html'], $message_data );

        // Title text shadow
        $title_text_shadow = $this->ays_pb_generate_title_text_shadow_styles($options);

        //close button hover text
        $ays_pb_close_button_hover_text = (isset($options->close_button_hover_text) && $options->close_button_hover_text != '') ? stripslashes( esc_attr($options->close_button_hover_text) ) : "";

        // Box shadow
        $box_shadow = $this->ays_pb_generate_box_shadow_styles($options);

        //popup box font-family
        $ays_pb_font_family  = (isset($options->pb_font_family) && $options->pb_font_family != '') ? stripslashes( esc_attr($options->pb_font_family) ) : '';

        // Font Size 
        $pb_font_size = (isset($options->pb_font_size) && $options->pb_font_size != '') ? absint($options->pb_font_size) : 13;
        //Bg image position
        $pb_bg_image_position = (isset($options->pb_bg_image_position) && $options->pb_bg_image_position != "") ? str_ireplace('-', ' ', $options->pb_bg_image_position) : 'center center';

        $pb_bg_image_sizing = (isset($options->pb_bg_image_sizing) && $options->pb_bg_image_sizing != "") ? stripslashes( esc_attr($options->pb_bg_image_sizing) ) : 'cover';

        //Background Gradient
        $background_gradient = (!isset($options->enable_background_gradient)) ? 'off' : stripslashes( esc_attr($options->enable_background_gradient) );
        $pb_gradient_direction = (!isset($options->pb_gradient_direction)) ? 'horizontal' : stripslashes( esc_attr($options->pb_gradient_direction) );
        $background_gradient_color_1 = (!isset($options->background_gradient_color_1)) ? "#000000" : stripslashes( esc_attr($options->background_gradient_color_1) );
        $background_gradient_color_2 = (!isset($options->background_gradient_color_2)) ? "#fff" : stripslashes( esc_attr($options->background_gradient_color_2) );
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
        if($popup['ays_pb_bg_image'] !== ''){
            $popup['ays_pb_bg_image'] = 'background-image: url('.$popup['ays_pb_bg_image'].');
                                background-repeat: no-repeat;
                                background-size: '.$pb_bg_image_sizing.';
                                background-position: '. $pb_bg_image_position .';';
        } elseif ($background_gradient == 'on' && $popup['ays_pb_bg_image'] == '') {
            $popup['ays_pb_bg_image'] = "background-image: linear-gradient(".$pb_gradient_direction.",".$background_gradient_color_1.",".$background_gradient_color_2.");";
        }

        //Show Popup Title
        $show_popup_title = $popup['show_title'] == "On" ? 'block' : 'none';

        //Show Popup Descirtion
        $show_popup_desc = $popup['show_desc'] == "On" ? 'block' : 'none';

        //Show Popup Title Mobile
        $show_title_mobile_class = $popup['show_title_mobile'] == 'On' ? 'ays_pb_show_title_on_mobile' : 'ays_pb_hide_title_on_mobile';
        
        //Show Popup Description Mobile
        $show_desc_mobile_class = $popup['show_desc_mobile']  == 'On' ? 'ays_pb_show_desc_on_mobile' : 'ays_pb_hide_desc_on_mobile';

        if ($popup['ays_pb_title'] != '') {
            $popup['ays_pb_title'] = "<h2 class='" . $show_title_mobile_class . " ays_pb_title_styles_" . $popup['id'] . "' style='color:" . $popup['ays_pb_textcolor'] . " !important; font-family:$ays_pb_font_family; {$title_text_shadow}; display: " . $show_popup_title . "'>" . $popup['ays_pb_title'] . "</h2>";
        }

        if ($popup['ays_pb_autoclose'] > 0) {
            if ($popup['ays_pb_delay'] != 0 && ($popup['ays_pb_autoclose'] < $popup['ays_pb_delay_second'] || $popup['ays_pb_autoclose'] >= $popup['ays_pb_delay_second']) ) {
                $popup['ays_pb_autoclose'] += floor($popup['ays_pb_delay_second']);
            }
        }

        if ($popup['ays_pb_description'] != '') {
            $content_desktop = Ays_Pb_Public::ays_autoembed( $popup['ays_pb_description'] );
            $popup['ays_pb_description'] = "<div class='ays_pb_description " . $show_desc_mobile_class . "' style='font-size:{$pb_font_size}px; display:" . $show_popup_desc . "'>".$content_desktop."</div>";
        }

        if($popup['ays_pb_action_buttons_type'] == 'both' || $popup['ays_pb_action_buttons_type'] == 'pageLoaded'){
            $ays_pb_flag = "data-ays-flag='false'";
        }
        if($popup['ays_pb_action_buttons_type'] == 'clickSelector'){
            $ays_pb_flag = "data-ays-flag='true'";
        }
        if ( $popup['closeButton'] == "on" ){
            $popup['closeButton'] = "ays-close-button-on-off";
        } else { $popup['closeButton'] = ""; }

        $popup_width_by_percentage_px = (isset($options->popup_width_by_percentage_px) && $options->popup_width_by_percentage_px != '') ? stripslashes( esc_attr($options->popup_width_by_percentage_px) ) : 'pixels';
        if(isset($popup['ays_pb_width']) && $popup['ays_pb_width'] != ''){
            if ($popup_width_by_percentage_px && $popup_width_by_percentage_px == 'percentage') {
                if (absint(intval($popup['ays_pb_width'])) > 100 ) {
                    $pb_width = '100%';
                }else{
                    $pb_width = $popup['ays_pb_width'] . '%';
                }
            }else{
                $pb_width = $popup['ays_pb_width'] . 'px';
            }
        }else{
            $pb_width = '100%';
        }

        //pb full screen
        $ays_pb_full_screen  = (isset($options->enable_pb_fullscreen) && $options->enable_pb_fullscreen == 'on') ? 'on' : 'off';
        $pb_height = '';
        if($ays_pb_full_screen == 'on'){
           $pb_width = '100%';
           $popup['ays_pb_height'] = 'auto';
        }else{
           $pb_width  = $popup_width_by_percentage_px == 'percentage' ? $popup['ays_pb_width'] . '%' : $popup['ays_pb_width'] . 'px';
           $pb_height = $popup['ays_pb_height'] . 'px';
        }

        if($pb_width == '0px' ||  $pb_width == '0%'){       
            $pb_width = '100%';
        }

        if($pb_height == '0px'){       
            $pb_height = '500px';
        }

        $ays_pb_padding = (isset($options->popup_content_padding) && $options->popup_content_padding != '') ? $options->popup_content_padding : '20';
        //popup padding percentage
        $popup_padding_by_percentage_px = (isset($options->popup_padding_by_percentage_px) && $options->popup_padding_by_percentage_px != '') ? stripslashes( esc_attr($options->popup_padding_by_percentage_px) ) : 'pixels';
        if(isset($ays_pb_padding) && $ays_pb_padding != ''){
            if ($popup_padding_by_percentage_px && $popup_padding_by_percentage_px == 'percentage') {
                if (absint(intval($ays_pb_padding)) > 100 ) {
                    $pb_padding = '100%';
                }else{
                    $pb_padding = $ays_pb_padding . '%';
                }
            }else{
                $pb_padding = $ays_pb_padding . 'px';
            }
        }else{
            $pb_padding = '20px';
        }

        $ays_pb_timer_desc = $this->ays_pb_generate_hide_timer_text($popup, $options, $attr);

        // Social Media links
        $enable_social_links = (isset($options->enable_social_links) && $options->enable_social_links == "on") ? true : false;
        $social_links = (isset($options->social_links)) ? $options->social_links : array(
            'linkedin_link' => '',
            'facebook_link' => '',
            'twitter_link' => '',
            'vkontakte_link' => '',
            'youtube_link' => '',
            'instagram_link' => '',
            'behance_link' => '',
        );
        $ays_social_links_array = array();
        
        if($social_links != ''){
            $social_link_arr = (array)$social_links;
        }else{
            $social_link_arr = $social_links;
        }

        $linkedin_link = isset($social_link_arr['linkedin_link']) && $social_link_arr['linkedin_link'] != '' ? $social_link_arr['linkedin_link'] : '';
        $facebook_link = isset($social_link_arr['facebook_link']) && $social_link_arr['facebook_link'] != '' ? $social_link_arr['facebook_link'] : '';
        $twitter_link = isset($social_link_arr['twitter_link']) && $social_link_arr['twitter_link'] != '' ? $social_link_arr['twitter_link'] : '';
        $vkontakte_link = isset($social_link_arr['vkontakte_link']) && $social_link_arr['vkontakte_link'] != '' ? $social_link_arr['vkontakte_link'] : '';
        $youtube_link = isset($social_link_arr['youtube_link']) && $social_link_arr['youtube_link'] != '' ? $social_link_arr['youtube_link'] : '';
        $instagram_link = isset($social_link_arr['instagram_link']) && $social_link_arr['instagram_link'] != '' ? $social_link_arr['instagram_link'] : '';
        $behance_link = isset($social_link_arr['behance_link']) && $social_link_arr['behance_link'] != '' ? $social_link_arr['behance_link'] : '';
        
        if($linkedin_link != ''){
            $ays_social_links_array['Linkedin']['link'] = $linkedin_link;
            $ays_social_links_array['Linkedin']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/linkedin.svg">';
        }
        if($facebook_link != ''){
            $ays_social_links_array['Facebook']['link'] = $facebook_link;
            $ays_social_links_array['Facebook']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/facebook.svg">';
        }
        if($twitter_link != ''){
            $ays_social_links_array['Twitter']['link'] = $twitter_link;
            $ays_social_links_array['Twitter']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/twitter-x.svg">';
        }
        if($vkontakte_link != ''){
            $ays_social_links_array['VKontakte']['link'] = $vkontakte_link;
            $ays_social_links_array['VKontakte']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/vk.svg">';
        }
        if($youtube_link != ''){
            $ays_social_links_array['Youtube']['link'] = $youtube_link;
            $ays_social_links_array['Youtube']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/youtube.svg">';
        }

        if($instagram_link != ''){
            $ays_social_links_array['Instagram']['link'] = $instagram_link;
            $ays_social_links_array['Instagram']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/instagram.svg">';
        }

        if($behance_link != ''){
            $ays_social_links_array['Behance']['link'] = $behance_link;
            $ays_social_links_array['Behance']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/behance.svg">';
        }
        

        $ays_social_links = '';

        // Heading for social buttons
        $social_buttons_heading = (isset($options->social_buttons_heading) && $options->social_buttons_heading != '') ? stripslashes( Ays_Pb_Public::ays_autoembed( $options->social_buttons_heading ) ) : "";

        if($enable_social_links){
            $ays_social_links .= "<div class='ays-pb-social-buttons-content'>";
                $ays_social_links .= "<div class='ays-pb-social-buttons-heading'>".$social_buttons_heading."</div>";
                $ays_social_links .= "<div class='ays-pb-social-shares'>";
                    foreach($ays_social_links_array as $media => $link){
                        $ays_social_links .= "<!-- Branded " . $media . " button -->
                            <a  href='" . $link['link'] . "'
                                target='_blank'
                                title='" . $media . " link'>
                                <div class='ays-pb-share-btn-icon'>".$link['img']."</div>
                            </a>";
                    }
                $ays_social_links .= "</div>";
            $ays_social_links .= "</div>";
        }

        $close_btn_size = (isset($options->close_button_size) && $options->close_button_size != '') ? abs($options->close_button_size): '1';
        
        //border style 
        $border_style = (isset($options->border_style) && $options->border_style != '') ? $options->border_style : 'solid';

        $ays_pb_sound_mute = '';

        if($popup['ays_pb_action_buttons_type'] == 'clickSelector' || $popup['ays_pb_action_buttons_type'] == 'both'){
            if(isset($options->enable_pb_sound) && $options->enable_pb_sound == "on"){
                $ays_pb_sound_mute .= "<span class='ays_pb_music_sound ays_sound_active'>
                                        ".$this->volume_up_icon."
                                    </span>";
            }else{
                $ays_pb_sound_mute = '';
            }
        }else{
            $ays_pb_sound_mute = '';
        }
        
        //Enable dismiss
        $enable_dismiss = ( isset($options->enable_dismiss) && $options->enable_dismiss == "on" ) ? true : false;
        $show_dismiss = 'ays_pb_display_none';
        if( $enable_dismiss ){
            $show_dismiss = '';
        }

        //Dismiss ad text
        $enable_dismiss_text = (isset($options->enable_dismiss_text) && $options->enable_dismiss_text != "") ? esc_html( stripslashes($options->enable_dismiss_text) ) : __("Dismiss ad", "ays-popup-box");
        
        //Dismiss ad text mobile
        if ( ( !isset($options->enable_dismiss_mobile) ) || (isset($options->enable_dismiss_mobile) && $options->enable_dismiss_mobile == 'off' ) ) {
            $enable_dismiss_text_mobile = $enable_dismiss_text;
        } else {
            $enable_dismiss_text_mobile = (isset($options->enable_dismiss_text_mobile) && $options->enable_dismiss_text_mobile != "") ? esc_html( stripslashes($options->enable_dismiss_text_mobile) ) : __("Dismiss ad", "ays-popup-box");
        }

        //Show scrollbar
        $options->show_scrollbar = ( isset( $options->show_scrollbar ) && $options->show_scrollbar != '' ) ? stripslashes( esc_attr($options->show_scrollbar) ) : 'off';
        $ays_pb_show_scrollbar = ( isset( $options->show_scrollbar ) && $options->show_scrollbar == 'on' ) ? true : false;

        $ays_pb_disable_scroll_on_popup_class = $this->ays_pb_generate_disable_popup_class($options);

        $ays_pb_show_scrollbar_class = '';
        if($ays_pb_show_scrollbar){
            $ays_pb_show_scrollbar_class = 'ays-pb-show-scrollbar';
        }

        $ubuntu_view = "<div class='ays_ubuntu_window ays-pb-modal_".$popup['id']." ".$popup['custom_class']." ".$ays_pb_disable_scroll_on_popup_class." ".$ays_pb_show_scrollbar_class." ays-pb-bg-styles-mobile_".$popup['id']." ays-pb-border-mobile_".$popup['id']."' {$ays_pb_flag} style='width: {$pb_width}; height: {$pb_height}; ".$popup['ays_pb_bg_image'].";  background-color: ".$popup['ays_pb_bgcolor']."; color: ".$popup['ays_pb_textcolor']." !important; border: ".$popup['ays_pb_bordersize']."px $border_style ".$popup['ays_pb_bordercolor']."; border-radius: ".$popup['ays_pb_border_radius']."px;font-family:{$ays_pb_font_family};{$box_shadow}'>
                      <div class='ays_ubuntu_topbar'>
                        <div class='ays_ubuntu_icons'>
                            <div class='ays_ubuntu_close  ".$popup['closeButton']." ays-pb-close-button-delay' data-toggle='tooltip' title='" . $ays_pb_close_button_hover_text . "'>
                                <div class='ays_ubuntu_close ays-pb-modal-close_".$popup['id']." ays_pb_pause_sound_".$popup['id']."'></div>
                            </div>
                          <div class='ays_ubuntu_hide'></div>
                          <div class='ays_ubuntu_maximize'></div>
                        </div>
                        ".$popup['ays_pb_title']."
                      </div>
                      <div class='ays_ubuntu_tools'>
                        <ul>
                            <li>".__("File")."</li>
                            <li>".__("Edit", "ays-popup-box")."</li>
                            <li>".__("Go", "ays-popup-box")."</li>
                            <li>".__("Bookmarks", "ays-popup-box")."</li>
                            <li>".__("Tools", "ays-popup-box")."</li>
                            <li>".__("Help", "ays-popup-box")."</li>
                        </ul>
                      </div>
                      $ays_pb_sound_mute
                      
                      <div class='ays_ubuntu_window_content'>
                            ".$popup['ays_pb_description']."
                            ".(($popup['show_desc'] !== "On") ?  '' :  '<hr class="ays-popup-hrs-default"/>')."
                            <div class='ays_content_box' style='padding: {$pb_padding}';>".
                                (($popup['ays_pb_modal_content'] == 'shortcode') ? do_shortcode($popup['ays_pb_shortcode']) : Ays_Pb_Public::ays_autoembed($popup['ays_pb_custom_html']))
                            ."</div>
                            {$ays_social_links}
                            <div class='ays-pb-dismiss-ad {$show_dismiss}' data-dismiss='' data-id='".$popup['id']."'>
                                <button id='ays_pb_dismiss_ad'>
                                    <span class='ays_pb_dismiss_ad_text_pc'>".$enable_dismiss_text."</span>
                                    <span class='ays_pb_dismiss_ad_text_mobile'>".$enable_dismiss_text_mobile."</span>
                                </button>
                            </div>
                      </div>
                      <div class='ays_ubuntu_folder-info ays_pb_timer_".$popup['id']."'>
                      $ays_pb_timer_desc
                      </div>
                    </div>
                    <script>
                        (function($){
                            var prompt = {
                                window: $('.ays_ubuntu_window'),

                                init: function() {
                                    $('.ays_ubuntu_hide').click(prompt.minimize);
                                    $('.ays_ubuntu_maximize').click(prompt.maximize);
                                },
                                minimize: function() {        
                                    prompt.window.removeClass('ays_ubuntu_window--maximized');
                                    prompt.window.toggleClass('ays_ubuntu_window--minimized');
                                },
                                maximize: function() {
                                    prompt.window.removeClass('ays_ubuntu_window--minimized');
                                    prompt.window.toggleClass('ays_ubuntu_window--maximized');
                                    $(document).find('.ays_ubuntu_window').css('bottom', 0);
                                }
                            };
                            $(document).ready(prompt.init);
                        })(jQuery);
                    </script>";
        return $ubuntu_view;
    }   

    public function ays_pb_template_winxp($attr){
        $popup = $this->ays_pb_set_popup_options($attr);
        $options = $popup['options'];

        $message_data = $this->ays_pb_generate_message_variables_arr($popup['ays_pb_title'], $options);

        $popup['ays_pb_custom_html'] = Ays_Pb_Data::replace_message_variables( $popup['ays_pb_custom_html'], $message_data );

        // Title text shadow
        $title_text_shadow = $this->ays_pb_generate_title_text_shadow_styles($options);

        //close button hover text
        $ays_pb_close_button_hover_text = (isset($options->close_button_hover_text) && $options->close_button_hover_text != '') ? stripslashes( esc_attr($options->close_button_hover_text) ) : "";

        // Box shadow
        $box_shadow = $this->ays_pb_generate_box_shadow_styles($options);
        
        //popup box font-family
        $ays_pb_font_family  = (isset($options->pb_font_family) && $options->pb_font_family != '') ? stripslashes( esc_attr($options->pb_font_family) ) : '';

        // Font Size 
        $pb_font_size = (isset($options->pb_font_size) && $options->pb_font_size != '') ? absint($options->pb_font_size) : 13;
        //Bg image position
        $pb_bg_image_position = (isset($options->pb_bg_image_position) && $options->pb_bg_image_position != "") ? str_ireplace('-', ' ', $options->pb_bg_image_position) : 'center center';

        $pb_bg_image_sizing = (isset($options->pb_bg_image_sizing) && $options->pb_bg_image_sizing != "") ? stripslashes( esc_attr($options->pb_bg_image_sizing) ) : 'cover';

        //Background Gradient
        $background_gradient = (!isset($options->enable_background_gradient)) ? 'off' : stripslashes( esc_attr($options->enable_background_gradient) );
        $pb_gradient_direction = (!isset($options->pb_gradient_direction)) ? 'horizontal' : stripslashes( esc_attr($options->pb_gradient_direction) );
        $background_gradient_color_1 = (!isset($options->background_gradient_color_1)) ? "#000000" : stripslashes( esc_attr($options->background_gradient_color_1) );
        $background_gradient_color_2 = (!isset($options->background_gradient_color_2)) ? "#fff" : stripslashes( esc_attr($options->background_gradient_color_2) );
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
        if($popup['ays_pb_bg_image'] !== ''){
            $popup['ays_pb_bg_image'] = 'background-image: url('.$popup['ays_pb_bg_image'].');
                                background-repeat: no-repeat;
                                background-size: '.$pb_bg_image_sizing.';
                                background-position: '. $pb_bg_image_position .';';
        } elseif ($background_gradient == 'on' && $popup['ays_pb_bg_image'] == '') {
            $popup['ays_pb_bg_image'] = "background-image: linear-gradient(".$pb_gradient_direction.",".$background_gradient_color_1.",".$background_gradient_color_2.");";
        }

        //Show Popup Title
        $show_popup_title = $popup['show_title'] == "On" ? 'block' : 'none';

        //Show Popup Descirtion
        $show_popup_desc = $popup['show_desc'] == "On" ? 'block' : 'none';

        //Show Popup Title Mobile
        $show_title_mobile_class = $popup['show_title_mobile'] == 'On' ? 'ays_pb_show_title_on_mobile' : 'ays_pb_hide_title_on_mobile';
        
        //Show Popup Description Mobile
        $show_desc_mobile_class = $popup['show_desc_mobile']  == 'On' ? 'ays_pb_show_desc_on_mobile' : 'ays_pb_hide_desc_on_mobile';

        if ($popup['ays_pb_title'] != '') {
            $popup['ays_pb_title'] = "<h2 class='" . $show_title_mobile_class . " ays_pb_title_styles_" . $popup['id'] . "' style='color: white !important; font-family:$ays_pb_font_family; {$title_text_shadow}; display: " . $show_popup_title . "'>" . $popup['ays_pb_title'] . "</h2>";
        }

        if ($popup['ays_pb_autoclose'] > 0) {
            if ($popup['ays_pb_delay'] != 0 && ($popup['ays_pb_autoclose'] < $popup['ays_pb_delay_second'] || $popup['ays_pb_autoclose'] >= $popup['ays_pb_delay_second']) ) {
                $popup['ays_pb_autoclose'] += floor($popup['ays_pb_delay_second']);
            }
        }

        if ($popup['ays_pb_description'] != '') {
            $content_desktop = Ays_Pb_Public::ays_autoembed( $popup['ays_pb_description'] );
            $popup['ays_pb_description'] = "<div class='ays_pb_description " . $show_desc_mobile_class . "' style='font-size:{$pb_font_size}px; display:" . $show_popup_desc . "'>".$content_desktop."</div>";
        }

        if($popup['ays_pb_action_buttons_type'] == 'both' || $popup['ays_pb_action_buttons_type'] == 'pageLoaded'){
            $ays_pb_flag = "data-ays-flag='false'";
        }
        if($popup['ays_pb_action_buttons_type'] == 'clickSelector'){
            $ays_pb_flag = "data-ays-flag='true'";
        }
        if ( $popup['closeButton'] == "on" ){
            $popup['closeButton'] = "ays-close-button-on-off";
        } else { $popup['closeButton'] = ""; }

        //popup width percentage

        $popup_width_by_percentage_px = (isset($options->popup_width_by_percentage_px) && $options->popup_width_by_percentage_px != '') ? stripslashes( esc_attr($options->popup_width_by_percentage_px) ) : 'pixels';
        if(isset($popup['ays_pb_width']) && $popup['ays_pb_width'] != ''){
            if ($popup_width_by_percentage_px && $popup_width_by_percentage_px == 'percentage') {
                if (absint(intval($popup['ays_pb_width'])) > 100 ) {
                    $pb_width = '100%';
                }else{
                    $pb_width = $popup['ays_pb_width'] . '%';
                }
            }else{
                $pb_width = $popup['ays_pb_width'] . 'px';
            }
        }else{
            $pb_width = '100%';
        }

        //pb full screen
        $ays_pb_full_screen  = (isset($options->enable_pb_fullscreen) && $options->enable_pb_fullscreen == 'on') ? 'on' : 'off';
        $pb_height = '';
        if($ays_pb_full_screen == 'on'){
           $pb_width = '100%';
           $popup['ays_pb_height'] = 'auto';
        }else{
           $pb_width  = $popup_width_by_percentage_px == 'percentage' ? $popup['ays_pb_width'] . '%' : $popup['ays_pb_width'] . 'px';
           $pb_height = $popup['ays_pb_height'] . 'px';
        }

        if($pb_width == '0px' ||  $pb_width == '0%'){       
            $pb_width = '100%';
        }

        if($pb_height == '0px'){       
            $pb_height = '500px';
        }

        $ays_pb_padding = (isset($options->popup_content_padding) && $options->popup_content_padding != '') ? $options->popup_content_padding : '20';
        //popup padding percentage
        $popup_padding_by_percentage_px = (isset($options->popup_padding_by_percentage_px) && $options->popup_padding_by_percentage_px != '') ? stripslashes( esc_attr($options->popup_padding_by_percentage_px) ) : 'pixels';
        if(isset($ays_pb_padding) && $ays_pb_padding != ''){
            if ($popup_padding_by_percentage_px && $popup_padding_by_percentage_px == 'percentage') {
                if (absint(intval($ays_pb_padding)) > 100 ) {
                    $pb_padding = '100%';
                }else{
                    $pb_padding = $ays_pb_padding . '%';
                }
            }else{
                $pb_padding = $ays_pb_padding . 'px';
            }
        }else{
            $pb_padding = '20px';
        }

        $ays_pb_timer_desc = $this->ays_pb_generate_hide_timer_text($popup, $options, $attr);

        // Social Media links
        $enable_social_links = (isset($options->enable_social_links) && $options->enable_social_links == "on") ? true : false;
        $social_links = (isset($options->social_links)) ? $options->social_links : array(
            'linkedin_link' => '',
            'facebook_link' => '',
            'twitter_link' => '',
            'vkontakte_link' => '',
            'youtube_link' => '',
        );
        $ays_social_links_array = array();
        
        if($social_links != ''){
            $social_link_arr = (array)$social_links;
        }else{
            $social_link_arr = $social_links;
        }

        $linkedin_link = isset($social_link_arr['linkedin_link']) && $social_link_arr['linkedin_link'] != '' ? $social_link_arr['linkedin_link'] : '';
        $facebook_link = isset($social_link_arr['facebook_link']) && $social_link_arr['facebook_link'] != '' ? $social_link_arr['facebook_link'] : '';
        $twitter_link = isset($social_link_arr['twitter_link']) && $social_link_arr['twitter_link'] != '' ? $social_link_arr['twitter_link'] : '';
        $vkontakte_link = isset($social_link_arr['vkontakte_link']) && $social_link_arr['vkontakte_link'] != '' ? $social_link_arr['vkontakte_link'] : '';
        $youtube_link = isset($social_link_arr['youtube_link']) && $social_link_arr['youtube_link'] != '' ? $social_link_arr['youtube_link'] : '';
        $instagram_link = isset($social_link_arr['instagram_link']) && $social_link_arr['instagram_link'] != '' ? $social_link_arr['instagram_link'] : '';
        $behance_link = isset($social_link_arr['behance_link']) && $social_link_arr['behance_link'] != '' ? $social_link_arr['behance_link'] : '';
        
        if($linkedin_link != ''){
            $ays_social_links_array['Linkedin']['link'] = $linkedin_link;
            $ays_social_links_array['Linkedin']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/linkedin.svg">';
        }
        if($facebook_link != ''){
            $ays_social_links_array['Facebook']['link'] = $facebook_link;
            $ays_social_links_array['Facebook']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/facebook.svg">';
        }
        if($twitter_link != ''){
            $ays_social_links_array['Twitter']['link'] = $twitter_link;
            $ays_social_links_array['Twitter']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/twitter-x.svg">';
        }
        if($vkontakte_link != ''){
            $ays_social_links_array['VKontakte']['link'] = $vkontakte_link;
            $ays_social_links_array['VKontakte']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/vk.svg">';
        }
        if($youtube_link != ''){
            $ays_social_links_array['Youtube']['link'] = $youtube_link;
            $ays_social_links_array['Youtube']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/youtube.svg">';
        }
        
        if($instagram_link != ''){
            $ays_social_links_array['Instagram']['link'] = $instagram_link;
            $ays_social_links_array['Instagram']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/instagram.svg">';
        }

        if($behance_link != ''){
            $ays_social_links_array['Behance']['link'] = $behance_link;
            $ays_social_links_array['Behance']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/behance.svg">';
        }

        $ays_social_links = '';

        // Heading for social buttons
        $social_buttons_heading = (isset($options->social_buttons_heading) && $options->social_buttons_heading != '') ? stripslashes( Ays_Pb_Public::ays_autoembed( $options->social_buttons_heading ) ) : "";

        if($enable_social_links){
            $ays_social_links .= "<div class='ays-pb-social-buttons-content'>";
                $ays_social_links .= "<div class='ays-pb-social-buttons-heading'>".$social_buttons_heading."</div>";
                $ays_social_links .= "<div class='ays-pb-social-shares'>";
                    foreach($ays_social_links_array as $media => $link){
                        $ays_social_links .= "<!-- Branded " . $media . " button -->
                            <a  href='" . $link['link'] . "'
                                target='_blank'
                                title='" . $media . " link'>
                                <div class='ays-pb-share-btn-icon'>".$link['img']."</div>
                            </a>";
                    }
                $ays_social_links .= "</div>";
            $ays_social_links .= "</div>";
        }

        $close_btn_size = (isset($options->close_button_size) && $options->close_button_size != '') ? abs($options->close_button_size) : '1';

        //border style 
        $border_style = (isset($options->border_style) && $options->border_style != '') ? $options->border_style : 'solid';

        $ays_pb_sound_mute = '';

        if($popup['ays_pb_action_buttons_type'] == 'clickSelector' || $popup['ays_pb_action_buttons_type'] == 'both'){
            if(isset($options->enable_pb_sound) && $options->enable_pb_sound == "on"){
                $ays_pb_sound_mute .= "<span class='ays_pb_music_sound ays_sound_active'>
                                        ".$this->volume_up_icon."
                                    </span>";
            }else{
                $ays_pb_sound_mute = '';
            }
        }else{
            $ays_pb_sound_mute = '';
        }
        
        //Enable dismiss
        $enable_dismiss = ( isset($options->enable_dismiss) && $options->enable_dismiss == "on" ) ? true : false;
        $show_dismiss = 'ays_pb_display_none';
        if( $enable_dismiss ){
            $show_dismiss = '';
        }

        //Dismiss ad text
        $enable_dismiss_text = (isset($options->enable_dismiss_text) && $options->enable_dismiss_text != "") ? esc_html( stripslashes($options->enable_dismiss_text) ) : __("Dismiss ad", "ays-popup-box");
        
        //Dismiss ad text mobile
        if ( ( !isset($options->enable_dismiss_mobile) ) || (isset($options->enable_dismiss_mobile) && $options->enable_dismiss_mobile == 'off' ) ) {
            $enable_dismiss_text_mobile = $enable_dismiss_text;
        } else {
            $enable_dismiss_text_mobile = (isset($options->enable_dismiss_text_mobile) && $options->enable_dismiss_text_mobile != "") ? esc_html( stripslashes($options->enable_dismiss_text_mobile) ) : __("Dismiss ad", "ays-popup-box");
        }

        $x_close_button = '<svg xmlns="https://www.w3.org/2000/svg" height="24px" viewBox="0 0 32 32" width="24px" fill="#ffffff" alt="Pop-up Close"><path d="M0 0h24v24H0z" fill="none"/><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>';

        //Show scrollbar
        $options->show_scrollbar = ( isset( $options->show_scrollbar ) && $options->show_scrollbar != '' ) ? stripslashes( esc_attr($options->show_scrollbar) ) : 'off';
        $ays_pb_show_scrollbar = ( isset( $options->show_scrollbar ) && $options->show_scrollbar == 'on' ) ? true : false;

        $ays_pb_disable_scroll_on_popup_class = $this->ays_pb_generate_disable_popup_class($options);

        $ays_pb_show_scrollbar_class = '';
        if($ays_pb_show_scrollbar){
            $ays_pb_show_scrollbar_class = 'ays-pb-show-scrollbar';
        }

        $ubuntu_view = "<div class='ays_winxp_window ays-pb-modal_".$popup['id']." ".$popup['custom_class']." ".$ays_pb_disable_scroll_on_popup_class." ".$ays_pb_show_scrollbar_class." ays-pb-border-mobile_".$popup['id']."' {$ays_pb_flag} style='width: {$pb_width}; height: {$pb_height}; color: ".$popup['ays_pb_textcolor']." !important; border: ".$popup['ays_pb_bordersize']."px $border_style ".$popup['ays_pb_bordercolor']."; border-radius: ".$popup['ays_pb_border_radius']."px;font-family:{$ays_pb_font_family};{$box_shadow};'>
                            <div class='ays_winxp_title-bar'>
                                <div class='ays_winxp_title-bar-title'>
                                    ".$popup['ays_pb_title']."
                                </div>
                                <div class='ays_winxp_title-bar-close ".$popup['closeButton']." ays-pb-close-button-delay' data-toggle='tooltip' title='" . $ays_pb_close_button_hover_text . "'>
                                    <div class='ays_winxp_close  ays-pb-modal-close_".$popup['id']." ays_pb_pause_sound_".$popup['id']."'>".$x_close_button."</div>
                                </div>
                                <div class='ays_winxp_title-bar-max ays_pb_fa ays_pb_far far' aria-hidden='true'>
                                    <img src='" .  AYS_PB_ADMIN_URL . "/images/icons/window-maximize.svg'>
                                </div>
                                <div class='ays_winxp_title-bar-min'></div>
                            </div>
                            <div class='ays_winxp_content ays-pb-bg-styles-mobile_".$popup['id']."' style='background-color: ".$popup['ays_pb_bgcolor']."; ".$popup['ays_pb_bg_image']."; '>
                                $ays_pb_sound_mute
                                <div>
                                    ".$popup['ays_pb_description']."
                                    ".(($popup['show_title'] !== "On") ?  '' :  '<hr/>')."
                                </div>
                                <div class='ays_content_box' style='padding: {$pb_padding}'>".
                                    (($popup['ays_pb_modal_content'] == 'shortcode') ? do_shortcode($popup['ays_pb_shortcode']) : Ays_Pb_Public::ays_autoembed($popup['ays_pb_custom_html']))
                                ."</div>
                                {$ays_social_links}
                                <div class='ays-pb-dismiss-ad {$show_dismiss}' data-dismiss='' data-id='".$popup['id']."'>
                                    <button id='ays_pb_dismiss_ad'>
                                        <span class='ays_pb_dismiss_ad_text_pc'>".$enable_dismiss_text."</span>
                                        <span class='ays_pb_dismiss_ad_text_mobile'>".$enable_dismiss_text_mobile."</span>
                                    </button>
                                </div>
                                $ays_pb_timer_desc
                            </div>
                      </div>
                    <script>
                        (function($){
                            var prompt = {
                                window: $('.ays_winxp_window'),

                                init: function() {
                                    $('.ays_winxp_title-bar-min').click(prompt.minimize);
                                    $('.ays_winxp_title-bar-max').click(prompt.maximize);
                                },
                                minimize: function() {        
                                    prompt.window.removeClass('ays_winxp_window--maximized');
                                    prompt.window.toggleClass('ays_winxp_window--minimized');
                                },
                                maximize: function() {
                                    prompt.window.removeClass('ays_winxp_window--minimized');
                                    prompt.window.toggleClass('ays_winxp_window--maximized');
                                    $(document).find('.ays_winxp_window').css('bottom', 0);
                                }
                            };
                            $(document).ready(prompt.init);
                        })(jQuery);
                    </script>";
        return $ubuntu_view;
    }  

    public function ays_pb_template_win98($attr){
        $popup = $this->ays_pb_set_popup_options($attr);
        $options = $popup['options'];

        $message_data = $this->ays_pb_generate_message_variables_arr($popup['ays_pb_title'], $options);

        $popup['ays_pb_custom_html'] = Ays_Pb_Data::replace_message_variables( $popup['ays_pb_custom_html'], $message_data );

        // Title text shadow
        $title_text_shadow = $this->ays_pb_generate_title_text_shadow_styles($options);

        //close button hover text
        $ays_pb_close_button_hover_text = (isset($options->close_button_hover_text) && $options->close_button_hover_text != '') ? stripslashes( esc_attr($options->close_button_hover_text) ) : "";
        
        // Box shadow
        $box_shadow = $this->ays_pb_generate_box_shadow_styles($options);

        //popup box font-family
        $ays_pb_font_family  = (isset($options->pb_font_family) && $options->pb_font_family != '') ? stripslashes( esc_attr($options->pb_font_family) ) : '';

        // Font Size 
        $pb_font_size = (isset($options->pb_font_size) && $options->pb_font_size != '') ? absint($options->pb_font_size) : 13;

        //Bg image position
        $pb_bg_image_position = (isset($options->pb_bg_image_position) && $options->pb_bg_image_position != "") ? str_ireplace('-', ' ', $options->pb_bg_image_position) : 'center center';

        $pb_bg_image_sizing = (isset($options->pb_bg_image_sizing) && $options->pb_bg_image_sizing != "") ? stripslashes( esc_attr($options->pb_bg_image_sizing) ) : 'cover';

        //Background Gradient
        $background_gradient = (!isset($options->enable_background_gradient)) ? 'off' : stripslashes( esc_attr($options->enable_background_gradient) );
        $pb_gradient_direction = (!isset($options->pb_gradient_direction)) ? 'horizontal' : stripslashes( esc_attr($options->pb_gradient_direction) );
        $background_gradient_color_1 = (!isset($options->background_gradient_color_1)) ? "#000000" : stripslashes( esc_attr($options->background_gradient_color_1) );
        $background_gradient_color_2 = (!isset($options->background_gradient_color_2)) ? "#fff" : stripslashes( esc_attr($options->background_gradient_color_2) );
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
        if($popup['ays_pb_bg_image'] !== ''){
            $popup['ays_pb_bg_image'] = 'background-image: url('.$popup['ays_pb_bg_image'].');
                                background-repeat: no-repeat;
                                background-size: '.$pb_bg_image_sizing.';
                                background-position: '. $pb_bg_image_position .';';
        } elseif ($background_gradient == 'on' && $popup['ays_pb_bg_image'] == '') {
            $popup['ays_pb_bg_image'] = "background-image: linear-gradient(".$pb_gradient_direction.",".$background_gradient_color_1.",".$background_gradient_color_2.");";
        }

        //Show Popup Title
        $show_popup_title = $popup['show_title'] == "On" ? 'block' : 'none';

        //Show Popup Descirtion
        $show_popup_desc = $popup['show_desc'] == "On" ? 'block' : 'none';

        //Show Popup Title Mobile
        $show_title_mobile_class = $popup['show_title_mobile'] == 'On' ? 'ays_pb_show_title_on_mobile' : 'ays_pb_hide_title_on_mobile';
        
        //Show Popup Description Mobile
        $show_desc_mobile_class = $popup['show_desc_mobile']  == 'On' ? 'ays_pb_show_desc_on_mobile' : 'ays_pb_hide_desc_on_mobile';

        if ($popup['ays_pb_title'] != '') {
            $popup['ays_pb_title'] = "<h2 class='" . $show_title_mobile_class . " ays_pb_title_styles_" . $popup['id'] . "' style='color: white !important; {$title_text_shadow}; display: " . $show_popup_title . "'>" . $popup['ays_pb_title'] . "</h2>";
        }

        if ($popup['ays_pb_autoclose'] > 0) {
            if ($popup['ays_pb_delay'] != 0 && ($popup['ays_pb_autoclose'] < $popup['ays_pb_delay_second'] || $popup['ays_pb_autoclose'] >= $popup['ays_pb_delay_second']) ) {
                $popup['ays_pb_autoclose'] += floor($popup['ays_pb_delay_second']);
            }
        }

        if ($popup['ays_pb_description'] != '') {
            $content_desktop = Ays_Pb_Public::ays_autoembed( $popup['ays_pb_description'] );
            $popup['ays_pb_description'] = "<div class='ays_pb_description " . $show_desc_mobile_class . "' style='font-size:{$pb_font_size}px; display:" . $show_popup_desc . "'>".$content_desktop."</div>";
        }

        if($popup['ays_pb_action_buttons_type'] == 'both' || $popup['ays_pb_action_buttons_type'] == 'pageLoaded'){
            $ays_pb_flag = "data-ays-flag='false'";
        }
        if($popup['ays_pb_action_buttons_type'] == 'clickSelector'){
            $ays_pb_flag = "data-ays-flag='true'";
        }
        if ( $popup['closeButton'] == "on" ){
            $popup['closeButton'] = "ays-close-button-on-off";
        } else { $popup['closeButton'] = ""; }
        
        //popup width percentage

        $popup_width_by_percentage_px = (isset($options->popup_width_by_percentage_px) && $options->popup_width_by_percentage_px != '') ? stripslashes( esc_attr($options->popup_width_by_percentage_px) ) : 'pixels';
        if(isset($popup['ays_pb_width']) && $popup['ays_pb_width'] != ''){
            if ($popup_width_by_percentage_px && $popup_width_by_percentage_px == 'percentage') {
                if (absint(intval($popup['ays_pb_width'])) > 100 ) {
                    $pb_width = '100%';
                }else{
                    $pb_width = $popup['ays_pb_width'] . '%';
                }
            }else{
                $pb_width = $popup['ays_pb_width'] . 'px';
            }
        }else{
            $pb_width = '100%';
        }

        //pb full screen
        $ays_pb_full_screen  = (isset($options->enable_pb_fullscreen) && $options->enable_pb_fullscreen == 'on') ? 'on' : 'off';
        $pb_height = '';
        if($ays_pb_full_screen == 'on'){
           $pb_width = '100%';
           $popup['ays_pb_height'] = 'auto';
        }else{
           $pb_width  = $popup_width_by_percentage_px == 'percentage' ? $popup['ays_pb_width'] . '%' : $popup['ays_pb_width'] . 'px';
           $pb_height = $popup['ays_pb_height'] . 'px';
        }

        if($pb_width == '0px' ||  $pb_width == '0%'){       
            $pb_width = '100%';
        }

        if($pb_height == '0px'){       
            $pb_height = '500px';
        }

        $ays_pb_padding = (isset($options->popup_content_padding) && $options->popup_content_padding != '') ? $options->popup_content_padding : '20';
        //popup padding percentage
        $popup_padding_by_percentage_px = (isset($options->popup_padding_by_percentage_px) && $options->popup_padding_by_percentage_px != '') ? stripslashes( esc_attr($options->popup_padding_by_percentage_px) ) : 'pixels';
        if(isset($ays_pb_padding) && $ays_pb_padding != ''){
            if ($popup_padding_by_percentage_px && $popup_padding_by_percentage_px == 'percentage') {
                if (absint(intval($ays_pb_padding)) > 100 ) {
                    $pb_padding = '100%';
                }else{
                    $pb_padding = $ays_pb_padding . '%';
                }
            }else{
                $pb_padding = $ays_pb_padding . 'px';
            }
        }else{
            $pb_padding = '20px';
        }

        $ays_pb_timer_desc = $this->ays_pb_generate_hide_timer_text($popup, $options, $attr);

        // Social Media links
        $enable_social_links = (isset($options->enable_social_links) && $options->enable_social_links == "on") ? true : false;
        $social_links = (isset($options->social_links)) ? $options->social_links : array(
            'linkedin_link' => '',
            'facebook_link' => '',
            'twitter_link' => '',
            'vkontakte_link' => '',
            'youtube_link' => '',
            'instagram_link' => '',
            'behance_link' => '',
        );
        $ays_social_links_array = array();
        
        if($social_links != ''){
            $social_link_arr = (array)$social_links;
        }else{
            $social_link_arr = $social_links;
        }

        $linkedin_link = isset($social_link_arr['linkedin_link']) && $social_link_arr['linkedin_link'] != '' ? $social_link_arr['linkedin_link'] : '';
        $facebook_link = isset($social_link_arr['facebook_link']) && $social_link_arr['facebook_link'] != '' ? $social_link_arr['facebook_link'] : '';
        $twitter_link = isset($social_link_arr['twitter_link']) && $social_link_arr['twitter_link'] != '' ? $social_link_arr['twitter_link'] : '';
        $vkontakte_link = isset($social_link_arr['vkontakte_link']) && $social_link_arr['vkontakte_link'] != '' ? $social_link_arr['vkontakte_link'] : '';
        $youtube_link = isset($social_link_arr['youtube_link']) && $social_link_arr['youtube_link'] != '' ? $social_link_arr['youtube_link'] : '';
        $instagram_link = isset($social_link_arr['instagram_link']) && $social_link_arr['instagram_link'] != '' ? $social_link_arr['instagram_link'] : '';
        $behance_link = isset($social_link_arr['behance_link']) && $social_link_arr['behance_link'] != '' ? $social_link_arr['behance_link'] : '';
        
        if($linkedin_link != ''){
            $ays_social_links_array['Linkedin']['link'] = $linkedin_link;
            $ays_social_links_array['Linkedin']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/linkedin.svg">';
        }
        if($facebook_link != ''){
            $ays_social_links_array['Facebook']['link'] = $facebook_link;
            $ays_social_links_array['Facebook']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/facebook.svg">';
        }
        if($twitter_link != ''){
            $ays_social_links_array['Twitter']['link'] = $twitter_link;
            $ays_social_links_array['Twitter']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/twitter-x.svg">';
        }
        if($vkontakte_link != ''){
            $ays_social_links_array['VKontakte']['link'] = $vkontakte_link;
            $ays_social_links_array['VKontakte']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/vk.svg">';
        }
        if($youtube_link != ''){
            $ays_social_links_array['Youtube']['link'] = $youtube_link;
            $ays_social_links_array['Youtube']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/youtube.svg">';
        }
        if($instagram_link != ''){
            $ays_social_links_array['Instagram']['link'] = $instagram_link;
            $ays_social_links_array['Instagram']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/instagram.svg">';
        }

        if($behance_link != ''){
            $ays_social_links_array['Behance']['link'] = $behance_link;
            $ays_social_links_array['Behance']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/behance.svg">';
        }

        $ays_social_links = '';

        // Heading for social buttons
        $social_buttons_heading = (isset($options->social_buttons_heading) && $options->social_buttons_heading != '') ? stripslashes( Ays_Pb_Public::ays_autoembed( $options->social_buttons_heading ) ) : "";

        if($enable_social_links){
            $ays_social_links .= "<div class='ays-pb-social-buttons-content'>";
                $ays_social_links .= "<div class='ays-pb-social-buttons-heading'>".$social_buttons_heading."</div>";
                $ays_social_links .= "<div class='ays-pb-social-shares'>";
                    foreach($ays_social_links_array as $media => $link){
                        $ays_social_links .= "<!-- Branded " . $media . " button -->
                            <a  href='" . $link['link'] . "'
                                target='_blank'
                                title='" . $media . " link'>
                                <div class='ays-pb-share-btn-icon'>".$link['img']."</div>
                            </a>";
                    }
                $ays_social_links .= "</div>";
            $ays_social_links .= "</div>";
        }

        $close_btn_size = (isset($options->close_button_size) && $options->close_button_size != '') ? abs($options->close_button_size) : '1';

        //border style 
        $border_style = (isset($options->border_style) && $options->border_style != '') ? $options->border_style : 'solid';

        $ays_pb_sound_mute = '';

        if($popup['ays_pb_action_buttons_type'] == 'clickSelector' || $popup['ays_pb_action_buttons_type'] == 'both'){
            if(isset($options->enable_pb_sound) && $options->enable_pb_sound == "on"){
                $ays_pb_sound_mute .= "<span class='ays_pb_music_sound ays_sound_active'>
                                        ".$this->volume_up_icon."
                                    </span>";
            }else{
                $ays_pb_sound_mute = '';
            }
        }else{
            $ays_pb_sound_mute = '';
        }
        
        //Enable dismiss
        $enable_dismiss = ( isset($options->enable_dismiss) && $options->enable_dismiss == "on" ) ? true : false;
        $show_dismiss = 'ays_pb_display_none';
        if( $enable_dismiss ){
            $show_dismiss = '';
        }

        //Dismiss ad text
        $enable_dismiss_text = (isset($options->enable_dismiss_text) && $options->enable_dismiss_text != "") ? esc_html( stripslashes($options->enable_dismiss_text) ) : __("Dismiss ad", "ays-popup-box");
        
        //Dismiss ad text mobile
        if ( ( !isset($options->enable_dismiss_mobile) ) || (isset($options->enable_dismiss_mobile) && $options->enable_dismiss_mobile == 'off' ) ) {
            $enable_dismiss_text_mobile = $enable_dismiss_text;
        } else {
            $enable_dismiss_text_mobile = (isset($options->enable_dismiss_text_mobile) && $options->enable_dismiss_text_mobile != "") ? esc_html( stripslashes($options->enable_dismiss_text_mobile) ) : __("Dismiss ad", "ays-popup-box");
        }

        //Show scrollbar
        $options->show_scrollbar = ( isset( $options->show_scrollbar ) && $options->show_scrollbar != '' ) ? stripslashes( esc_attr($options->show_scrollbar) ) : 'off';
        $ays_pb_show_scrollbar = ( isset( $options->show_scrollbar ) && $options->show_scrollbar == 'on' ) ? true : false;

        $ays_pb_disable_scroll_on_popup_class = $this->ays_pb_generate_disable_popup_class($options);

        $ays_pb_show_scrollbar_class = '';
        if($ays_pb_show_scrollbar){
            $ays_pb_show_scrollbar_class = 'ays-pb-show-scrollbar';
        }

        $ubuntu_view = "<div class='ays_win98_window ays-pb-modal_".$popup['id']." ".$popup['custom_class']." ".$ays_pb_disable_scroll_on_popup_class." ".$ays_pb_show_scrollbar_class." ays-pb-bg-styles-mobile_".$popup['id']." ays-pb-border-mobile_".$popup['id']."' {$ays_pb_flag} style='width: {$pb_width}; height: {$pb_height}; padding: {$pb_padding}; background-color: ".$popup['ays_pb_bgcolor']."; ".$popup['ays_pb_bg_image'].";  color: ".$popup['ays_pb_textcolor']." !important; border: ".$popup['ays_pb_bordersize']."px $border_style ".$popup['ays_pb_bordercolor']."; border-radius: ".$popup['ays_pb_border_radius']."px;font-family:{$ays_pb_font_family};{$box_shadow};'>
                            <header class='ays_win98_head ays-pb-bg-styles-mobile_".$popup['id']."' style='background-color: ".$popup['ays_pb_bgcolor'].";'>
                                <div class='ays_win98_header'>
                                    <div class='ays_win98_title'>
                                        ".$popup['ays_pb_title']."
                                    </div>
                                    <div class='ays_win98_btn-close ".$popup['closeButton']." ays-pb-close-button-delay'  data-toggle='tooltip' title='" . $ays_pb_close_button_hover_text . "'><div class='ays-pb-modal-close_".$popup['id']." ays_pb_pause_sound_".$popup['id']."'><span></span></div></div>
                                </div>
                            </header>
                            <div class='ays_win98_main'>
                                $ays_pb_sound_mute
                                <div class='ays_win98_content'>
                                    ".$popup['ays_pb_description']."
                                    ".(($popup['show_title'] !== "On") ?  '' :  '<hr/>')."                               
                                    <div class='ays_content_box' style='padding: {$pb_padding}'>".
                                        (($popup['ays_pb_modal_content'] == 'shortcode') ? do_shortcode($popup['ays_pb_shortcode']) : Ays_Pb_Public::ays_autoembed($popup['ays_pb_custom_html']))
                                    ."</div>
                                    {$ays_social_links}
                                    <div class='ays-pb-dismiss-ad {$show_dismiss}' data-dismiss='' data-id='".$popup['id']."'>
                                        <button id='ays_pb_dismiss_ad'>
                                            <span class='ays_pb_dismiss_ad_text_pc'>".$enable_dismiss_text."</span>
                                            <span class='ays_pb_dismiss_ad_text_mobile'>".$enable_dismiss_text_mobile."</span>
                                        </button>
                                    </div>
                                    $ays_pb_timer_desc
                                </div>
                            </div>
                        </div>";
        return $ubuntu_view;
    }

    public function ays_pb_template_lil($attr){
        $popup = $this->ays_pb_set_popup_options($attr);
        $options = $popup['options'];

        $message_data = $this->ays_pb_generate_message_variables_arr($popup['ays_pb_title'], $options);

        $popup['ays_pb_custom_html'] = Ays_Pb_Data::replace_message_variables( $popup['ays_pb_custom_html'], $message_data );

        // Title text shadow
        $title_text_shadow = $this->ays_pb_generate_title_text_shadow_styles($options);

        // Box shadow
        $box_shadow = $this->ays_pb_generate_box_shadow_styles($options);

        //popup box font-family
        $ays_pb_font_family  = (isset($options->pb_font_family) && $options->pb_font_family != '') ? stripslashes( esc_attr($options->pb_font_family) ) : '';

        // Font Size 
        $pb_font_size = (isset($options->pb_font_size) && $options->pb_font_size != '') ? absint($options->pb_font_size) : 13;

        //close button hover text
        $ays_pb_close_button_hover_text = (isset($options->close_button_hover_text) && $options->close_button_hover_text != '') ? stripslashes( esc_attr($options->close_button_hover_text) ) : "";

        //Bg image position
        $pb_bg_image_position = (isset($options->pb_bg_image_position) && $options->pb_bg_image_position != "") ? str_ireplace('-', ' ', $options->pb_bg_image_position) : 'center center';

        $pb_bg_image_sizing = (isset($options->pb_bg_image_sizing) && $options->pb_bg_image_sizing != "") ? stripslashes( esc_attr($options->pb_bg_image_sizing) ) : 'cover';

        //Background Gradient
        $background_gradient = (!isset($options->enable_background_gradient)) ? 'off' : stripslashes( esc_attr($options->enable_background_gradient) );
        $pb_gradient_direction = (!isset($options->pb_gradient_direction)) ? 'horizontal' : stripslashes( esc_attr($options->pb_gradient_direction) );
        $background_gradient_color_1 = (!isset($options->background_gradient_color_1)) ? "#000000" : stripslashes( esc_attr($options->background_gradient_color_1) );
        $background_gradient_color_2 = (!isset($options->background_gradient_color_2)) ? "#fff" : stripslashes( esc_attr($options->background_gradient_color_2) );
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
        if($popup['ays_pb_bg_image'] !== ''){
            $popup['ays_pb_bg_image'] = 'background-image: url('.$popup['ays_pb_bg_image'].');
                                background-repeat: no-repeat;
                                background-size: '.$pb_bg_image_sizing.';
                                background-position: '. $pb_bg_image_position .';';
        }elseif ($background_gradient == 'on' && $popup['ays_pb_bg_image'] == '') {
            $popup['ays_pb_bg_image'] = "background-image: linear-gradient(".$pb_gradient_direction.",".$background_gradient_color_1.",".$background_gradient_color_2.");";
        }
        else{
            $popup['ays_pb_bg_image'] = '';
        }

        //popup full screen 
        $ays_pb_full_screen  = (isset($options->enable_pb_fullscreen) && $options->enable_pb_fullscreen == 'on') ? 'on' : 'off';

        //Show Popup Title
        $show_popup_title = $popup['show_title'] == "On" ? 'block' : 'none';

        //Show Popup Descirtion
        $show_popup_desc = $popup['show_desc'] == "On" ? 'block' : 'none';

        //Show Popup Title Mobile
        $show_title_mobile_class = $popup['show_title_mobile'] == 'On' ? 'ays_pb_show_title_on_mobile' : 'ays_pb_hide_title_on_mobile';
        
        //Show Popup Description Mobile
        $show_desc_mobile_class = $popup['show_desc_mobile']  == 'On' ? 'ays_pb_show_desc_on_mobile' : 'ays_pb_hide_desc_on_mobile';

        if ($popup['ays_pb_title'] != '') {
            $popup['ays_pb_title'] = "<h2 class='" . $show_title_mobile_class . " ays_pb_title_styles_" . $popup['id'] . "' style='color:" . $popup['ays_pb_textcolor'] . " !important; font-family:$ays_pb_font_family; {$title_text_shadow}; display: " . $show_popup_title . "'>" . $popup['ays_pb_title'] . "</h2>";
        }

        if ($popup['ays_pb_autoclose'] > 0) {
            if ($popup['ays_pb_delay'] != 0 && ($popup['ays_pb_autoclose'] < $popup['ays_pb_delay_second'] || $popup['ays_pb_autoclose'] >= $popup['ays_pb_delay_second']) ) {
                $popup['ays_pb_autoclose'] += floor($popup['ays_pb_delay_second']);
            }
        }

        if ($popup['ays_pb_description'] != '') {
            $content_desktop = Ays_Pb_Public::ays_autoembed( $popup['ays_pb_description'] );
            $popup['ays_pb_description'] = "<div class='ays_pb_description " . $show_desc_mobile_class . "' style='font-size:{$pb_font_size}px; display:" . $show_popup_desc . "'>".$content_desktop."</div>";
        }

        if($popup['ays_pb_action_buttons_type'] == 'both' || $popup['ays_pb_action_buttons_type'] == 'pageLoaded'){
            $ays_pb_flag = "data-ays-flag='false'";
        }
        if($popup['ays_pb_action_buttons_type'] == 'clickSelector'){
            $ays_pb_flag = "data-ays-flag='true'";
        }
        if ( $popup['closeButton'] == "on" ){
            $popup['closeButton'] = "ays-close-button-on-off";
        } else { $popup['closeButton'] = ""; }


        //popup width percentage

        $popup_width_by_percentage_px = (isset($options->popup_width_by_percentage_px) && $options->popup_width_by_percentage_px != '') ? stripslashes( esc_attr($options->popup_width_by_percentage_px) ) : 'pixels';
        if(isset($popup['ays_pb_width']) && $popup['ays_pb_width'] != ''){
            if ($popup_width_by_percentage_px && $popup_width_by_percentage_px == 'percentage') {
                if (absint(intval($popup['ays_pb_width'])) > 100 ) {
                    $pb_width = '100%';
                }else{
                    $pb_width = $popup['ays_pb_width'] . '%';
                }
            }else{
                $pb_width = $popup['ays_pb_width'] . 'px';
            }
        }else{
            $pb_width = '100%';
        }

        //pb full screen
        
        $pb_height = '';
        if($ays_pb_full_screen == 'on'){
           $pb_width = '100%';
           $popup['ays_pb_height'] = 'auto';
        }else{
           $pb_width  = $popup_width_by_percentage_px == 'percentage' ? $popup['ays_pb_width'] . '%' : $popup['ays_pb_width'] . 'px';
           $pb_height = $popup['ays_pb_height'] . 'px';
        }

        if($pb_width == '0px' ||  $pb_width == '0%'){       
            $pb_width = '100%';
        }

        if($pb_height == '0px'){       
            $pb_height = '500px';
        }

        $ays_pb_padding = (isset($options->popup_content_padding) && $options->popup_content_padding != '') ? $options->popup_content_padding : '20';
        //popup padding percentage
        $popup_padding_by_percentage_px = (isset($options->popup_padding_by_percentage_px) && $options->popup_padding_by_percentage_px != '') ? stripslashes( esc_attr($options->popup_padding_by_percentage_px) ) : 'pixels';
        if(isset($ays_pb_padding) && $ays_pb_padding != ''){
            if ($popup_padding_by_percentage_px && $popup_padding_by_percentage_px == 'percentage') {
                if (absint(intval($ays_pb_padding)) > 100 ) {
                    $pb_padding = '100%';
                }else{
                    $pb_padding = $ays_pb_padding . '%';
                }
            }else{
                $pb_padding = $ays_pb_padding . 'px';
            }
        }else{
            $pb_padding = '20px';
        }

        $ays_pb_timer_desc = $this->ays_pb_generate_hide_timer_text($popup, $options, $attr);

        // Social Media links
        $enable_social_links = (isset($options->enable_social_links) && $options->enable_social_links == "on") ? true : false;
        $social_links = (isset($options->social_links)) ? $options->social_links : array(
            'linkedin_link' => '',
            'facebook_link' => '',
            'twitter_link' => '',
            'vkontakte_link' => '',
            'youtube_link' => '',
            'instagram_link' => '',
            'behance_link' => '',
        );
        $ays_social_links_array = array();
        
        if($social_links != ''){
            $social_link_arr = (array)$social_links;
        }else{
            $social_link_arr = $social_links;
        }

        $linkedin_link = isset($social_link_arr['linkedin_link']) && $social_link_arr['linkedin_link'] != '' ? $social_link_arr['linkedin_link'] : '';
        $facebook_link = isset($social_link_arr['facebook_link']) && $social_link_arr['facebook_link'] != '' ? $social_link_arr['facebook_link'] : '';
        $twitter_link = isset($social_link_arr['twitter_link']) && $social_link_arr['twitter_link'] != '' ? $social_link_arr['twitter_link'] : '';
        $vkontakte_link = isset($social_link_arr['vkontakte_link']) && $social_link_arr['vkontakte_link'] != '' ? $social_link_arr['vkontakte_link'] : '';
        $youtube_link = isset($social_link_arr['youtube_link']) && $social_link_arr['youtube_link'] != '' ? $social_link_arr['youtube_link'] : '';
        $instagram_link = isset($social_link_arr['instagram_link']) && $social_link_arr['instagram_link'] != '' ? $social_link_arr['instagram_link'] : '';
        $behance_link = isset($social_link_arr['behance_link']) && $social_link_arr['behance_link'] != '' ? $social_link_arr['behance_link'] : '';
        
        if($linkedin_link != ''){
            $ays_social_links_array['Linkedin']['link'] = $linkedin_link;
            $ays_social_links_array['Linkedin']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/linkedin.svg">';
        }
        if($facebook_link != ''){
            $ays_social_links_array['Facebook']['link'] = $facebook_link;
            $ays_social_links_array['Facebook']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/facebook.svg">';
        }
        if($twitter_link != ''){
            $ays_social_links_array['Twitter']['link'] = $twitter_link;
            $ays_social_links_array['Twitter']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/twitter-x.svg">';
        }
        if($vkontakte_link != ''){
            $ays_social_links_array['VKontakte']['link'] = $vkontakte_link;
            $ays_social_links_array['VKontakte']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/vk.svg">';
        }
        if($youtube_link != ''){
            $ays_social_links_array['Youtube']['link'] = $youtube_link;
            $ays_social_links_array['Youtube']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/youtube.svg">';
        }
        if($instagram_link != ''){
            $ays_social_links_array['Instagram']['link'] = $instagram_link;
            $ays_social_links_array['Instagram']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/instagram.svg">';
        }

        if($behance_link != ''){
            $ays_social_links_array['Behance']['link'] = $behance_link;
            $ays_social_links_array['Behance']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/behance.svg">';
        }

        $ays_social_links = '';

        // Heading for social buttons
        $social_buttons_heading = (isset($options->social_buttons_heading) && $options->social_buttons_heading != '') ? stripslashes( Ays_Pb_Public::ays_autoembed( $options->social_buttons_heading ) ) : "";

        if($enable_social_links){
            $ays_social_links .= "<div class='ays-pb-social-buttons-content'>";
                $ays_social_links .= "<div class='ays-pb-social-buttons-heading'>".$social_buttons_heading."</div>";
                $ays_social_links .= "<div class='ays-pb-social-shares'>";
                    foreach($ays_social_links_array as $media => $link){
                        $ays_social_links .= "<!-- Branded " . $media . " button -->
                            <a  href='" . $link['link'] . "'
                                target='_blank'
                                title='" . $media . " link'>
                                <div class='ays-pb-share-btn-icon'>".$link['img']."</div>
                            </a>";
                    }
                $ays_social_links .= "</div>";
            $ays_social_links .= "</div>";
        }

        $close_btn_size = (isset($options->close_button_size) && $options->close_button_size != '') ? abs($options->close_button_size) : '1';

        //border style 
        $border_style = (isset($options->border_style) && $options->border_style != '') ? $options->border_style : 'solid';

        $ays_pb_sound_mute = '';

        if($popup['ays_pb_action_buttons_type'] == 'clickSelector' || $popup['ays_pb_action_buttons_type'] == 'both'){
            if(isset($options->enable_pb_sound) && $options->enable_pb_sound == "on"){
                $ays_pb_sound_mute .= "<span class='ays_pb_music_sound ays_sound_active'>
                                        ".$this->volume_up_icon."
                                    </span>";
            }else{
                $ays_pb_sound_mute = '';
            }
        }else{
            $ays_pb_sound_mute = '';
        }
        
        //Enable dismiss
        $enable_dismiss = ( isset($options->enable_dismiss) && $options->enable_dismiss == "on" ) ? true : false;
        $show_dismiss = 'ays_pb_display_none';
        if( $enable_dismiss ){
            $show_dismiss = '';
        }

        //Dismiss ad text
        $enable_dismiss_text = (isset($options->enable_dismiss_text) && $options->enable_dismiss_text != "") ? esc_html( stripslashes($options->enable_dismiss_text) ) : __("Dismiss ad", "ays-popup-box");
        
        //Dismiss ad text mobile
        if ( ( !isset($options->enable_dismiss_mobile) ) || (isset($options->enable_dismiss_mobile) && $options->enable_dismiss_mobile == 'off' ) ) {
            $enable_dismiss_text_mobile = $enable_dismiss_text;
        } else {
            $enable_dismiss_text_mobile = (isset($options->enable_dismiss_text_mobile) && $options->enable_dismiss_text_mobile != "") ? esc_html( stripslashes($options->enable_dismiss_text_mobile) ) : __("Dismiss ad", "ays-popup-box");
        }
        
        //Close button color
        $close_button_color = (isset($options->close_button_color) && $options->close_button_color != "") ? esc_attr( stripslashes( $options->close_button_color ) ) : $popup['ays_pb_textcolor'];

        //Show scrollbar
        $options->show_scrollbar = ( isset( $options->show_scrollbar ) && $options->show_scrollbar != '' ) ? stripslashes( esc_attr($options->show_scrollbar) ) : 'off';
        $ays_pb_show_scrollbar = ( isset( $options->show_scrollbar ) && $options->show_scrollbar == 'on' ) ? true : false;

        $ays_pb_disable_scroll_on_popup_class = $this->ays_pb_generate_disable_popup_class($options);

        $ays_pb_show_scrollbar_class = '';
        if($ays_pb_show_scrollbar){
            $ays_pb_show_scrollbar_class = 'ays-pb-show-scrollbar';
        }

        $ubuntu_view = "    <div class='ays_lil_window ays-pb-modal_".$popup['id']." ".$popup['custom_class']." ".$ays_pb_disable_scroll_on_popup_class." ".$ays_pb_show_scrollbar_class." ays-pb-bg-styles-mobile_".$popup['id']." ays-pb-border-mobile_".$popup['id']."' {$ays_pb_flag} style='width: {$pb_width}; height: {$pb_height}; background-color: ".$popup['ays_pb_bgcolor']."; color: ".$popup['ays_pb_textcolor']." !important; border: ".$popup['ays_pb_bordersize']."px $border_style ".$popup['ays_pb_bordercolor']."; border-radius: ".$popup['ays_pb_border_radius']."px;font-family:{$ays_pb_font_family};".$popup['ays_pb_bg_image'].";{$box_shadow};'>
                                 <header class='ays_lil_head' style='background-color: ".(($popup['show_title'] !== "On") ?  "" :  $popup['ays_pb_header_bgcolor']).";'>
                                    $ays_pb_sound_mute
                                    <div class='ays_lil_header'>
                                        <div class='ays_lil_title'>
                                            ".$popup['ays_pb_title']."
                                        </div>
                                        <div class='ays_lil_btn-close ".$popup['closeButton']." ays-pb-close-button-delay'>
                                            <div class='ays-pb-modal-close_".$popup['id']."' >
                                                <a class='close-lil-btn ays_pb_pause_sound_".$popup['id']."' style='background-color:".$popup['ays_pb_textcolor']." !important; color: ".$close_button_color." ; font-family:{$ays_pb_font_family};transform:scale({$close_btn_size})'  data-toggle='tooltip' title='" . $ays_pb_close_button_hover_text . "'></a>
                                            </div>
                                        </div>
                                    </div>
                                </header>
                                <div class='ays_lil_main'>
                                    <div class='ays_lil_content'>
                                        ".$popup['ays_pb_description']."
                                        <div class='ays_content_box' style='padding: {$pb_padding};'>".
                                        (($popup['ays_pb_modal_content'] == 'shortcode') ? do_shortcode($popup['ays_pb_shortcode']) : Ays_Pb_Public::ays_autoembed($popup['ays_pb_custom_html']))
                                        ."</div>
                                        {$ays_social_links}
                                        <div class='ays-pb-dismiss-ad {$show_dismiss}' data-dismiss='' data-id='".$popup['id']."'>
                                            <button id='ays_pb_dismiss_ad'>
                                                <span class='ays_pb_dismiss_ad_text_pc'>".$enable_dismiss_text."</span>
                                                <span class='ays_pb_dismiss_ad_text_mobile'>".$enable_dismiss_text_mobile."</span>
                                            </button>
                                        </div>
                                        $ays_pb_timer_desc
                                    </div>
                                </div>
                            </div>";
        return $ubuntu_view;
    }

    public function ays_pb_template_image($attr){
        $popup = $this->ays_pb_set_popup_options($attr);
        $options = $popup['options'];
        $ays_pb_bg_image_template_default = 'background-image: url("https://quiz-plugin.com/wp-content/uploads/2020/02/elefante.jpg");
                                          background-repeat: no-repeat;
                                          background-size: cover;';

        $message_data = $this->ays_pb_generate_message_variables_arr($popup['ays_pb_title'], $options);

        $popup['ays_pb_custom_html'] = Ays_Pb_Data::replace_message_variables( $popup['ays_pb_custom_html'], $message_data );

        // Title text shadow
        $title_text_shadow = $this->ays_pb_generate_title_text_shadow_styles($options);

        // Box shadow
        $box_shadow = $this->ays_pb_generate_box_shadow_styles($options);

        //popup box font-family
        $ays_pb_font_family  = (isset($options->pb_font_family) && $options->pb_font_family != '') ? stripslashes( esc_attr($options->pb_font_family) ) : '';

        // Font Size 
        $pb_font_size = (isset($options->pb_font_size) && $options->pb_font_size != '') ? absint($options->pb_font_size) : 13;
        
        //close button hover text
        $ays_pb_close_button_hover_text = (isset($options->close_button_hover_text) && $options->close_button_hover_text != '') ? stripslashes( esc_attr($options->close_button_hover_text) ) : "";

        //Bg image position
        $pb_bg_image_position = (isset($options->pb_bg_image_position) && $options->pb_bg_image_position != "") ? str_ireplace('-', ' ', $options->pb_bg_image_position) : 'center center';

        $pb_bg_image_sizing = (isset($options->pb_bg_image_sizing) && $options->pb_bg_image_sizing != "") ? stripslashes( esc_attr($options->pb_bg_image_sizing) ) : 'cover';

        //Background Gradient
        $background_gradient = (!isset($options->enable_background_gradient)) ? 'off' : stripslashes( esc_attr($options->enable_background_gradient) );
        $pb_gradient_direction = (!isset($options->pb_gradient_direction)) ? 'horizontal' : stripslashes( esc_attr($options->pb_gradient_direction) );
        $background_gradient_color_1 = (!isset($options->background_gradient_color_1)) ? "#000000" : stripslashes( esc_attr($options->background_gradient_color_1) );
        $background_gradient_color_2 = (!isset($options->background_gradient_color_2)) ? "#fff" : stripslashes( esc_attr($options->background_gradient_color_2) );
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
        if($popup['ays_pb_bg_image'] !== '' && $popup['ays_pb_bg_image'] != $ays_pb_bg_image_template_default){
            $popup['ays_pb_bg_image'] = 'background-image: url('.$popup['ays_pb_bg_image'].');
                                background-repeat: no-repeat;';
        }else{
            $popup['ays_pb_bg_image'] = $ays_pb_bg_image_template_default;
        }

        //popup full screen
        $ays_pb_full_screen  = (isset($options->enable_pb_fullscreen) && $options->enable_pb_fullscreen == 'on') ? 'on' : 'off';

        //Show Popup Title
        $show_popup_title = $popup['show_title'] == "On" ? 'block' : 'none';

        //Show Popup Descirtion
        $show_popup_desc = $popup['show_desc'] == "On" ? 'block' : 'none';

        //Show Popup Title Mobile
        $show_title_mobile_class = $popup['show_title_mobile'] == 'On' ? 'ays_pb_show_title_on_mobile' : 'ays_pb_hide_title_on_mobile';
        
        //Show Popup Description Mobile
        $show_desc_mobile_class = $popup['show_desc_mobile']  == 'On' ? 'ays_pb_show_desc_on_mobile' : 'ays_pb_hide_desc_on_mobile';

        if ($popup['ays_pb_title'] != '') {
            $popup['ays_pb_title'] = "<h2 class='" . $show_title_mobile_class . " ays_pb_title_styles_" . $popup['id'] . "' style='color:" . $popup['ays_pb_textcolor'] . " !important; font-family:$ays_pb_font_family; {$title_text_shadow}; display: " . $show_popup_title . "'>" . $popup['ays_pb_title'] . "</h2>";
        }

        if ($popup['ays_pb_autoclose'] > 0) {
            if ($popup['ays_pb_delay'] != 0 && ($popup['ays_pb_autoclose'] < $popup['ays_pb_delay_second'] || $popup['ays_pb_autoclose'] >= $popup['ays_pb_delay_second']) ) {
                $popup['ays_pb_autoclose'] += floor($popup['ays_pb_delay_second']);
            }
        }

        if ($popup['ays_pb_description'] != '') {
            $content_desktop = Ays_Pb_Public::ays_autoembed( $popup['ays_pb_description'] );
            $popup['ays_pb_description'] = "<div class='ays_pb_description " . $show_desc_mobile_class . "' style='font-size:{$pb_font_size}px; display:" . $show_popup_desc . "'>".$content_desktop."</div>";
        }

        if($popup['ays_pb_action_buttons_type'] == 'both' || $popup['ays_pb_action_buttons_type'] == 'pageLoaded'){
            $ays_pb_flag = "data-ays-flag='false'";
        }
        if($popup['ays_pb_action_buttons_type'] == 'clickSelector'){
            $ays_pb_flag = "data-ays-flag='true'";
        }
        if ( $popup['closeButton'] == "on" ){
            $popup['closeButton'] = "ays-close-button-on-off";
        } else { $popup['closeButton'] = ""; }

        $image_header_height = (($popup['show_title'] !== "On") ?  "height: 0% !important" :  "");
        $image_content_height = (($image_header_height !== "") ?  "max-height: 98% !important" :  "");

        //popup width percentage

        $popup_width_by_percentage_px = (isset($options->popup_width_by_percentage_px) && $options->popup_width_by_percentage_px != '') ? stripslashes( esc_attr($options->popup_width_by_percentage_px) ) : 'pixels';
        if(isset($popup['ays_pb_width']) && $popup['ays_pb_width'] != ''){
            if ($popup_width_by_percentage_px && $popup_width_by_percentage_px == 'percentage') {
                if (absint(intval($popup['ays_pb_width'])) > 100 ) {
                    $pb_width = '100%';
                }else{
                    $pb_width = $popup['ays_pb_width'] . '%';
                }
            }else{
                $pb_width = $popup['ays_pb_width'] . 'px';
            }
        }else{
            $pb_width = '100%';
        }
        $ubuntu_view = "";
        
        //pb full screen
        $pb_height = '';
        if($ays_pb_full_screen == 'on'){
            $pb_width = '100%';
            $popup['ays_pb_height'] = 'auto';
            $ubuntu_view .= "
                <style>
                    .ays_image_window .ays_image_main .ays_image_content>p:last-child {
                        position: unset !important;
                    }
                </style>
           ";
        }else{
           $pb_width  = $popup_width_by_percentage_px == 'percentage' ? $popup['ays_pb_width'] . '%' : $popup['ays_pb_width'] . 'px';
           $pb_height = $popup['ays_pb_height'] . 'px';
        }

        if($pb_width == '0px' ||  $pb_width == '0%'){       
            $pb_width = '100%';
        }

        if($pb_height == '0px'){       
            $pb_height = '500px';
        }

        $ays_pb_padding = (isset($options->popup_content_padding) && $options->popup_content_padding != '') ? $options->popup_content_padding : '20';
        //popup padding percentage
        $popup_padding_by_percentage_px = (isset($options->popup_padding_by_percentage_px) && $options->popup_padding_by_percentage_px != '') ? stripslashes( esc_attr($options->popup_padding_by_percentage_px) ) : 'pixels';
        if(isset($ays_pb_padding) && $ays_pb_padding != ''){
            if ($popup_padding_by_percentage_px && $popup_padding_by_percentage_px == 'percentage') {
                if (absint(intval($ays_pb_padding)) > 100 ) {
                    $pb_padding = '100%';
                }else{
                    $pb_padding = $ays_pb_padding . '%';
                }
            }else{
                $pb_padding = $ays_pb_padding . 'px';
            }
        }else{
            $pb_padding = '20px';
        }

        $ays_pb_timer_desc = $this->ays_pb_generate_hide_timer_text($popup, $options, $attr);

        // Social Media links
        $enable_social_links = (isset($options->enable_social_links) && $options->enable_social_links == "on") ? true : false;
        $social_links = (isset($options->social_links)) ? $options->social_links : array(
            'linkedin_link' => '',
            'facebook_link' => '',
            'twitter_link' => '',
            'vkontakte_link' => '',
            'youtube_link' => '',
            'instagram_link' => '',
            'behance_link' => '',
        );
        $ays_social_links_array = array();
        
        if($social_links != ''){
            $social_link_arr = (array)$social_links;
        }else{
            $social_link_arr = $social_links;
        }

        $linkedin_link = isset($social_link_arr['linkedin_link']) && $social_link_arr['linkedin_link'] != '' ? $social_link_arr['linkedin_link'] : '';
        $facebook_link = isset($social_link_arr['facebook_link']) && $social_link_arr['facebook_link'] != '' ? $social_link_arr['facebook_link'] : '';
        $twitter_link = isset($social_link_arr['twitter_link']) && $social_link_arr['twitter_link'] != '' ? $social_link_arr['twitter_link'] : '';
        $vkontakte_link = isset($social_link_arr['vkontakte_link']) && $social_link_arr['vkontakte_link'] != '' ? $social_link_arr['vkontakte_link'] : '';
        $youtube_link = isset($social_link_arr['youtube_link']) && $social_link_arr['youtube_link'] != '' ? $social_link_arr['youtube_link'] : '';
        $instagram_link = isset($social_link_arr['instagram_link']) && $social_link_arr['instagram_link'] != '' ? $social_link_arr['instagram_link'] : '';
        $behance_link = isset($social_link_arr['behance_link']) && $social_link_arr['behance_link'] != '' ? $social_link_arr['behance_link'] : '';
        
        if($linkedin_link != ''){
            $ays_social_links_array['Linkedin']['link'] = $linkedin_link;
            $ays_social_links_array['Linkedin']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/linkedin.svg">';
        }
        if($facebook_link != ''){
            $ays_social_links_array['Facebook']['link'] = $facebook_link;
            $ays_social_links_array['Facebook']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/facebook.svg">';
        }
        if($twitter_link != ''){
            $ays_social_links_array['Twitter']['link'] = $twitter_link;
            $ays_social_links_array['Twitter']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/twitter-x.svg">';
        }
        if($vkontakte_link != ''){
            $ays_social_links_array['VKontakte']['link'] = $vkontakte_link;
            $ays_social_links_array['VKontakte']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/vk.svg">';
        }
        if($youtube_link != ''){
            $ays_social_links_array['Youtube']['link'] = $youtube_link;
            $ays_social_links_array['Youtube']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/youtube.svg">';
        }
        
        if($instagram_link != ''){
            $ays_social_links_array['Instagram']['link'] = $instagram_link;
            $ays_social_links_array['Instagram']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/instagram.svg">';
        }

        if($behance_link != ''){
            $ays_social_links_array['Behance']['link'] = $behance_link;
            $ays_social_links_array['Behance']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/behance.svg">';
        }
        $ays_social_links = '';

        // Heading for social buttons
        $social_buttons_heading = (isset($options->social_buttons_heading) && $options->social_buttons_heading != '') ? stripslashes( Ays_Pb_Public::ays_autoembed( $options->social_buttons_heading ) ) : "";

        if($enable_social_links){
            $ays_social_links .= "<div class='ays-pb-social-buttons-content'>";
                $ays_social_links .= "<div class='ays-pb-social-buttons-heading'>".$social_buttons_heading."</div>";
                $ays_social_links .= "<div class='ays-pb-social-shares'>";
                    foreach($ays_social_links_array as $media => $link){
                        $ays_social_links .= "<!-- Branded " . $media . " button -->
                            <a  href='" . $link['link'] . "'
                                target='_blank'
                                title='" . $media . " link'>
                                <div class='ays-pb-share-btn-icon'>".$link['img']."</div>
                            </a>";
                    }
                $ays_social_links .= "</div>";
            $ays_social_links .= "</div>";
        }

        $close_btn_size = (isset($options->close_button_size) && $options->close_button_size != '') ? abs($options->close_button_size) : '1';

        //border style 
        $border_style = (isset($options->border_style) && $options->border_style != '') ? $options->border_style : 'solid';

        $ays_pb_sound_mute = '';

        if($popup['ays_pb_action_buttons_type'] == 'clickSelector' || $popup['ays_pb_action_buttons_type'] == 'both'){
            if(isset($options->enable_pb_sound) && $options->enable_pb_sound == "on"){
                $ays_pb_sound_mute .= "<span class='ays_pb_music_sound ays_sound_active'>
                                        ".$this->volume_up_icon."
                                    </span>";
            }else{
                $ays_pb_sound_mute = '';
            }
        }else{
            $ays_pb_sound_mute = '';
        }
        
        //Enable dismiss
        $enable_dismiss = ( isset($options->enable_dismiss) && $options->enable_dismiss == "on" ) ? true : false;
        $show_dismiss = 'ays_pb_display_none';
        if( $enable_dismiss ){
            $show_dismiss = '';
        }

        //Dismiss ad text
        $enable_dismiss_text = (isset($options->enable_dismiss_text) && $options->enable_dismiss_text != "") ? esc_html( stripslashes($options->enable_dismiss_text) ) : __("Dismiss ad", "ays-popup-box");
        
        //Dismiss ad text mobile
        if ( ( !isset($options->enable_dismiss_mobile) ) || (isset($options->enable_dismiss_mobile) && $options->enable_dismiss_mobile == 'off' ) ) {
            $enable_dismiss_text_mobile = $enable_dismiss_text;
        } else {
            $enable_dismiss_text_mobile = (isset($options->enable_dismiss_text_mobile) && $options->enable_dismiss_text_mobile != "") ? esc_html( stripslashes($options->enable_dismiss_text_mobile) ) : __("Dismiss ad", "ays-popup-box");
        }

        //Show scrollbar
        $options->show_scrollbar = ( isset( $options->show_scrollbar ) && $options->show_scrollbar != '' ) ? stripslashes( esc_attr($options->show_scrollbar) ) : 'off';
        $ays_pb_show_scrollbar = ( isset( $options->show_scrollbar ) && $options->show_scrollbar == 'on' ) ? true : false;

        $ays_pb_disable_scroll_on_popup_class = $this->ays_pb_generate_disable_popup_class($options);

        $ays_pb_show_scrollbar_class = '';
        if($ays_pb_show_scrollbar){
            $ays_pb_show_scrollbar_class = 'ays-pb-show-scrollbar';
        }

        $ubuntu_view .= "   <div class='ays_image_window ays-pb-modal_".$popup['id']." ".$popup['custom_class']." ays-pb-bg-styles-mobile_".$popup['id']." ays-pb-border-mobile_".$popup['id']."' {$ays_pb_flag} style='width: {$pb_width}; height: {$pb_height}; background-color: ".$popup['ays_pb_bgcolor']."; color: ".$popup['ays_pb_textcolor']." !important;font-family:{$ays_pb_font_family}; border: ".$popup['ays_pb_bordersize']."px $border_style ".$popup['ays_pb_bordercolor']."; border-radius: ".$popup['ays_pb_border_radius']."px; ".$popup['ays_pb_bg_image']."; background-size: {$pb_bg_image_sizing}; background-position: {$pb_bg_image_position};{$box_shadow}; animation-fill-mode: forwards;' data-name='modern_minimal'>
                                <header class='ays_image_head' style='{$image_header_height}'>
                                    <div class='ays_image_header'>
                                        $ays_pb_sound_mute
                                        <div class='ays_popup_image_title'>
                                            ".$popup['ays_pb_title']."
                                        </div>
                                        <div class='ays_image_btn-close ".$popup['closeButton']."'>
                                            <div class='ays-pb-modal-close_".$popup['id']." ays-pb-close-button-delay' >
                                                <div class='close-image-btn ays_pb_pause_sound_".$popup['id']."' style='color: ".$popup['ays_pb_textcolor']." ; font-family:{$ays_pb_font_family};transform:scale({$close_btn_size})'  data-toggle='tooltip' title='" . $ays_pb_close_button_hover_text . "'></div>
                                            </div>
                                        </div>
                                    </div>
                                </header>
                                <div class='ays_image_main ".$ays_pb_disable_scroll_on_popup_class." ".$ays_pb_show_scrollbar_class."' style='{$image_content_height}' >
                                    <div class='ays_image_content'>
                                        ".$popup['ays_pb_description']."
                                        <div class='ays_content_box' style='padding: {$pb_padding};'>".
                                        (($popup['ays_pb_modal_content'] == 'shortcode') ? do_shortcode($popup['ays_pb_shortcode']) : Ays_Pb_Public::ays_autoembed($popup['ays_pb_custom_html']))
                                        ."</div>
                                        {$ays_social_links}
                                        <div class='ays-pb-dismiss-ad {$show_dismiss}' data-dismiss='' data-id='".$popup['id']."'>
                                            <button id='ays_pb_dismiss_ad'>
                                                <span class='ays_pb_dismiss_ad_text_pc'>".$enable_dismiss_text."</span>
                                                <span class='ays_pb_dismiss_ad_text_mobile'>".$enable_dismiss_text_mobile."</span>
                                            </button>
                                        </div>
                                        $ays_pb_timer_desc
                                    </div>
                                </div>
                            </div>";
        return $ubuntu_view;
    }

    public function ays_pb_template_template($attr){
        $popup = $this->ays_pb_set_popup_options($attr);
        $options = $popup['options'];

        $ays_pb_bg_image_template_default = 'background-image: url("https://quiz-plugin.com/wp-content/uploads/2020/02/girl-scaled.jpg");
                                             background-repeat: no-repeat;
                                             background-size: cover;';

        $message_data = $this->ays_pb_generate_message_variables_arr($popup['ays_pb_title'], $options);

        $popup['ays_pb_custom_html'] = Ays_Pb_Data::replace_message_variables( $popup['ays_pb_custom_html'], $message_data );

        // Title text shadow
        $title_text_shadow = $this->ays_pb_generate_title_text_shadow_styles($options);

        // Box shadow
        $box_shadow = $this->ays_pb_generate_box_shadow_styles($options);

        //popup box font-family
        $ays_pb_font_family  = (isset($options->pb_font_family) && $options->pb_font_family != '') ? stripslashes( esc_attr($options->pb_font_family) ) : '';

        // Font Size 
        $pb_font_size = (isset($options->pb_font_size) && $options->pb_font_size != '') ? absint($options->pb_font_size) : 13;

        //Bg image position
        $pb_bg_image_position = (isset($options->pb_bg_image_position) && $options->pb_bg_image_position != "") ? str_ireplace('-', ' ', $options->pb_bg_image_position) : 'center center';
        
        $pb_bg_image_sizing = (isset($options->pb_bg_image_sizing) && $options->pb_bg_image_sizing != "") ? stripslashes( esc_attr($options->pb_bg_image_sizing) ) : 'cover';

        //Background Gradient
        $background_gradient = (!isset($options->enable_background_gradient)) ? 'off' : stripslashes( esc_attr($options->enable_background_gradient) );
        $pb_gradient_direction = (!isset($options->pb_gradient_direction)) ? 'horizontal' : stripslashes( esc_attr($options->pb_gradient_direction) );
        $background_gradient_color_1 = (!isset($options->background_gradient_color_1)) ? "#000000" : stripslashes( esc_attr($options->background_gradient_color_1) );
        $background_gradient_color_2 = (!isset($options->background_gradient_color_2)) ? "#fff" : stripslashes( esc_attr($options->background_gradient_color_2) );
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
        if($popup['ays_pb_bg_image'] !== '' && $popup['ays_pb_bg_image'] != $ays_pb_bg_image_template_default){
            $popup['ays_pb_bg_image'] = 'background-image: url('.$popup['ays_pb_bg_image'].');
                                background-repeat: no-repeat;
                                background-size: cover;';
        }

        //close button hover text
        $ays_pb_close_button_hover_text = (isset($options->close_button_hover_text) && $options->close_button_hover_text != '') ? stripslashes( esc_attr($options->close_button_hover_text) ) : "";


        if ($background_gradient == 'on') {
            $bg_gradient_container = "background-image: linear-gradient(".$pb_gradient_direction.",".$background_gradient_color_1.",".$background_gradient_color_2.");";
            $popup['ays_pb_bgcolor'] = "transparent";
        } else {
            $bg_gradient_container = "unset";
        }

        //Show Popup Title
        $show_popup_title = $popup['show_title'] == "On" ? 'block' : 'none';

        //Show Popup Descirtion
        $show_popup_desc = $popup['show_desc'] == "On" ? 'block' : 'none';

        //Show Popup Title Mobile
        $show_title_mobile_class = $popup['show_title_mobile'] == 'On' ? 'ays_pb_show_title_on_mobile' : 'ays_pb_hide_title_on_mobile';
        
        //Show Popup Description Mobile
        $show_desc_mobile_class = $popup['show_desc_mobile']  == 'On' ? 'ays_pb_show_desc_on_mobile' : 'ays_pb_hide_desc_on_mobile';

        if ($popup['ays_pb_title'] != '') {
            $popup['ays_pb_title'] = "<h2 class='" . $show_title_mobile_class . " ays_pb_title_styles_" . $popup['id'] . "' style='color:" . $popup['ays_pb_textcolor'] . " !important; font-family:$ays_pb_font_family; {$title_text_shadow}; display: " . $show_popup_title . "'>" . $popup['ays_pb_title'] . "</h2>";
        }

        if ($popup['ays_pb_autoclose'] > 0) {
            if ($popup['ays_pb_delay'] != 0 && ($popup['ays_pb_autoclose'] < $popup['ays_pb_delay_second'] || $popup['ays_pb_autoclose'] >= $popup['ays_pb_delay_second']) ) {
                $popup['ays_pb_autoclose'] += floor($popup['ays_pb_delay_second']);
            }
        }
        
        if ($popup['ays_pb_description'] != '') {
            $content_desktop = Ays_Pb_Public::ays_autoembed( $popup['ays_pb_description'] );
            $popup['ays_pb_description'] = "<div class='ays_pb_description " . $show_desc_mobile_class . "' style='font-size:{$pb_font_size}px; display:" . $show_popup_desc . "'>".$content_desktop."</div>";
        }

        if($popup['ays_pb_action_buttons_type'] == 'both' || $popup['ays_pb_action_buttons_type'] == 'pageLoaded'){
            $ays_pb_flag = "data-ays-flag='false'";
        }
        if($popup['ays_pb_action_buttons_type'] == 'clickSelector'){
            $ays_pb_flag = "data-ays-flag='true'";
        }
        if ( $popup['closeButton'] == "on" ){
            $popup['closeButton'] = "ays-close-button-on-off";
        } else { $popup['closeButton'] = ""; }

        $header_height = (($popup['show_title'] !== "On") ?  "height: 0px !important" :  "");
        $calck_template_fotter = (($popup['show_title'] !== "On") ? "height: 100%;" :  "");

        //popup width percentage

        $popup_width_by_percentage_px = (isset($options->popup_width_by_percentage_px) && $options->popup_width_by_percentage_px != '') ? stripslashes( esc_attr($options->popup_width_by_percentage_px) ) : 'pixels';
        if(isset($popup['ays_pb_width']) && $popup['ays_pb_width'] != ''){
            if ($popup_width_by_percentage_px && $popup_width_by_percentage_px == 'percentage') {
                if (absint(intval($popup['ays_pb_width'])) > 100 ) {
                    $pb_width = '100%';
                }else{
                    $pb_width = $popup['ays_pb_width'] . '%';
                }
            }else{
                $pb_width = $popup['ays_pb_width'] . 'px';
            }
        }else{
            $pb_width = '100%';
        }

        //pb full screen
        $ays_pb_full_screen  = (isset($options->enable_pb_fullscreen) && $options->enable_pb_fullscreen == 'on') ? 'on' : 'off';
        $pb_height = '';
        if($ays_pb_full_screen == 'on'){
           $pb_width = '100%';
           $popup['ays_pb_height'] = 'auto';
        }else{
           $pb_width  = $popup_width_by_percentage_px == 'percentage' ? $popup['ays_pb_width'] . '%' : $popup['ays_pb_width'] . 'px';
           $pb_height = $popup['ays_pb_height'] . 'px';
        }

        if($pb_width == '0px' ||  $pb_width == '0%'){       
            $pb_width = '100%';
        }

        if($pb_height == '0px'){       
            $pb_height = '500px';
        }

        $ays_pb_padding = (isset($options->popup_content_padding) && $options->popup_content_padding != '') ? $options->popup_content_padding : '20';
        //popup padding percentage
        $popup_padding_by_percentage_px = (isset($options->popup_padding_by_percentage_px) && $options->popup_padding_by_percentage_px != '') ? stripslashes( esc_attr($options->popup_padding_by_percentage_px) ) : 'pixels';
        if(isset($ays_pb_padding) && $ays_pb_padding != ''){
            if ($popup_padding_by_percentage_px && $popup_padding_by_percentage_px == 'percentage') {
                if (absint(intval($ays_pb_padding)) > 100 ) {
                    $pb_padding = '100%';
                }else{
                    $pb_padding = $ays_pb_padding . '%';
                }
            }else{
                $pb_padding = $ays_pb_padding . 'px';
            }
        }else{
            $pb_padding = '20px';
        }

        $ays_pb_timer_desc = $this->ays_pb_generate_hide_timer_text($popup, $options, $attr);

        // Social Media links
        $enable_social_links = (isset($options->enable_social_links) && $options->enable_social_links == "on") ? true : false;
        $social_links = (isset($options->social_links)) ? $options->social_links : array(
            'linkedin_link' => '',
            'facebook_link' => '',
            'twitter_link' => '',
            'vkontakte_link' => '',
            'youtube_link' => '',
            'instagram_link' => '',
            'behance_link' => '',
        );
        $ays_social_links_array = array();
        
        if($social_links != ''){
            $social_link_arr = (array)$social_links;
        }else{
            $social_link_arr = $social_links;
        }

        $linkedin_link = isset($social_link_arr['linkedin_link']) && $social_link_arr['linkedin_link'] != '' ? $social_link_arr['linkedin_link'] : '';
        $facebook_link = isset($social_link_arr['facebook_link']) && $social_link_arr['facebook_link'] != '' ? $social_link_arr['facebook_link'] : '';
        $twitter_link = isset($social_link_arr['twitter_link']) && $social_link_arr['twitter_link'] != '' ? $social_link_arr['twitter_link'] : '';
        $vkontakte_link = isset($social_link_arr['vkontakte_link']) && $social_link_arr['vkontakte_link'] != '' ? $social_link_arr['vkontakte_link'] : '';
        $youtube_link = isset($social_link_arr['youtube_link']) && $social_link_arr['youtube_link'] != '' ? $social_link_arr['youtube_link'] : '';
        $behance_link = isset($social_link_arr['behance_link']) && $social_link_arr['behance_link'] != '' ? $social_link_arr['behance_link'] : '';
        $instagram_link = isset($social_link_arr['instagram_link']) && $social_link_arr['instagram_link'] != '' ? $social_link_arr['instagram_link'] : '';
        
        if($linkedin_link != ''){
            $ays_social_links_array['Linkedin']['link'] = $linkedin_link;
            $ays_social_links_array['Linkedin']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/linkedin.svg">';
        }
        if($facebook_link != ''){
            $ays_social_links_array['Facebook']['link'] = $facebook_link;
            $ays_social_links_array['Facebook']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/facebook.svg">';
        }
        if($twitter_link != ''){
            $ays_social_links_array['Twitter']['link'] = $twitter_link;
            $ays_social_links_array['Twitter']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/twitter-x.svg">';
        }
        if($vkontakte_link != ''){
            $ays_social_links_array['VKontakte']['link'] = $vkontakte_link;
            $ays_social_links_array['VKontakte']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/vk.svg">';
        }
        if($youtube_link != ''){
            $ays_social_links_array['Youtube']['link'] = $youtube_link;
            $ays_social_links_array['Youtube']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/youtube.svg">';
        }
        
        if($instagram_link != ''){
            $ays_social_links_array['Instagram']['link'] = $instagram_link;
            $ays_social_links_array['Instagram']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/instagram.svg">';
        }

        if($behance_link != ''){
            $ays_social_links_array['Behance']['link'] = $behance_link;
            $ays_social_links_array['Behance']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/behance.svg">';
        }

        $ays_social_links = '';

        // Heading for social buttons
        $social_buttons_heading = (isset($options->social_buttons_heading) && $options->social_buttons_heading != '') ? stripslashes( Ays_Pb_Public::ays_autoembed( $options->social_buttons_heading ) ) : "";

        if($enable_social_links){
            $ays_social_links .= "<div class='ays-pb-social-buttons-content'>";
                $ays_social_links .= "<div class='ays-pb-social-buttons-heading'>".$social_buttons_heading."</div>";
                $ays_social_links .= "<div class='ays-pb-social-shares'>";
                    foreach($ays_social_links_array as $media => $link){
                        $ays_social_links .= "<!-- Branded " . $media . " button -->
                            <a  href='" . $link['link'] . "'
                                target='_blank'
                                title='" . $media . " link'>
                                <div class='ays-pb-share-btn-icon'>".$link['img']."</div>
                            </a>";
                    }
                $ays_social_links .= "</div>";
            $ays_social_links .= "</div>";
        }

        $close_btn_size = (isset($options->close_button_size) && $options->close_button_size != '') ? abs($options->close_button_size) : '1';

        //border style 
        $border_style = (isset($options->border_style) && $options->border_style != '') ? $options->border_style : 'solid';

        $ays_template_header_bgcolor = '';
        
        if(substr($popup['ays_pb_header_bgcolor'],-2, 1) == '0' && substr($popup['ays_pb_header_bgcolor'],-15,4) == 'rgba'){
            $ays_template_header_bgcolor = $popup['ays_pb_bgcolor'];
        }else{
            $ays_template_header_bgcolor = $popup['ays_pb_header_bgcolor'];
        } 

        $ays_pb_sound_mute = '';

        if($popup['ays_pb_action_buttons_type'] == 'clickSelector' || $popup['ays_pb_action_buttons_type'] == 'both'){
            if(isset($options->enable_pb_sound) && $options->enable_pb_sound == "on"){
                $ays_pb_sound_mute .= "<span class='ays_pb_music_sound ays_sound_active'>
                                        ".$this->volume_up_icon."
                                    </span>";
            }else{
                $ays_pb_sound_mute = '';
            }
        }else{
            $ays_pb_sound_mute = '';
        }
        
        //Enable dismiss
        $enable_dismiss = ( isset($options->enable_dismiss) && $options->enable_dismiss == "on" ) ? true : false;
        $show_dismiss = 'ays_pb_display_none';
        if( $enable_dismiss ){
            $show_dismiss = '';
        }

        //Dismiss ad text
        $enable_dismiss_text = (isset($options->enable_dismiss_text) && $options->enable_dismiss_text != "") ? esc_html( stripslashes($options->enable_dismiss_text) ) : __("Dismiss ad", "ays-popup-box");
        
        //Dismiss ad text mobile
        if ( ( !isset($options->enable_dismiss_mobile) ) || (isset($options->enable_dismiss_mobile) && $options->enable_dismiss_mobile == 'off' ) ) {
            $enable_dismiss_text_mobile = $enable_dismiss_text;
        } else {
            $enable_dismiss_text_mobile = (isset($options->enable_dismiss_text_mobile) && $options->enable_dismiss_text_mobile != "") ? esc_html( stripslashes($options->enable_dismiss_text_mobile) ) : __("Dismiss ad", "ays-popup-box");
        }
        
        //Close button color
        $close_button_color = (isset($options->close_button_color) && $options->close_button_color != "") ? esc_attr( stripslashes( $options->close_button_color ) ) : $popup['ays_pb_textcolor'];

        //Show scrollbar
        $options->show_scrollbar = ( isset( $options->show_scrollbar ) && $options->show_scrollbar != '' ) ? stripslashes( esc_attr($options->show_scrollbar) ) : 'off';
        $ays_pb_show_scrollbar = ( isset( $options->show_scrollbar ) && $options->show_scrollbar == 'on' ) ? true : false;

        $ays_pb_disable_scroll_on_popup_class = $this->ays_pb_generate_disable_popup_class($options);

        $ays_pb_show_scrollbar_class = '';
        if($ays_pb_show_scrollbar){
            $ays_pb_show_scrollbar_class = 'ays-pb-show-scrollbar';
        }

        $ubuntu_view = "   <div class='ays_template_window ".$ays_pb_disable_scroll_on_popup_class." ays-pb-modal_".$popup['id']." ".$popup['custom_class']." ".$ays_pb_show_scrollbar_class." ays-pb-border-mobile_".$popup['id']."' {$ays_pb_flag} style='width: {$pb_width};  height: {$pb_height}; color: ".$popup['ays_pb_textcolor']." !important; font-family:{$ays_pb_font_family}; border: ".$popup['ays_pb_bordersize']."px $border_style ".$popup['ays_pb_bordercolor'].";{$bg_gradient_container}; border-radius: ".$popup['ays_pb_border_radius']."px; {$box_shadow};'>
                                 <header class='ays_template_head' style='{$header_height};background-color: {$ays_template_header_bgcolor}'>
                                    <div class='ays_template_header'>
                                        <div class='ays_template_title'>
                                            ".$popup['ays_pb_title']."
                                        </div>
                                        <div class='ays_template_btn-close ".$popup['closeButton']." '>
                                            <div class='close-template-btn-container ays-pb-modal-close_".$popup['id']." ays-pb-close-button-delay' >
                                                <div class='close-template-btn ays_pb_pause_sound_".$popup['id']."' style='color: ".$close_button_color." ;font-family:{$ays_pb_font_family};transform:scale({$close_btn_size})' data-toggle='tooltip' title='" . $ays_pb_close_button_hover_text . "'></div>
                                            </div>
                                        </div>
                                    </div>
                                </header>
                                <footer class='ays_template_footer ays-pb-bg-styles-mobile_".$popup['id']."' style='background-color: ".$popup['ays_pb_bgcolor']."; {$calck_template_fotter} '>
                                    <div class='ays_bg_image_box' style='".$popup['ays_pb_bg_image']." background-size: {$pb_bg_image_sizing}; background-position: {$pb_bg_image_position}'></div>
                                    <div class='ays_template_content ' style=''>
                                        $ays_pb_sound_mute
                                        ".$popup['ays_pb_description']."
                                        <div class='ays_content_box ays_template_main ".$ays_pb_disable_scroll_on_popup_class."' style='padding: {$pb_padding};'>".
                                        (($popup['ays_pb_modal_content'] == 'shortcode') ? do_shortcode($popup['ays_pb_shortcode']) : Ays_Pb_Public::ays_autoembed($popup['ays_pb_custom_html']))
                                        ."</div>
                                        {$ays_social_links}
                                        <div class='ays-pb-dismiss-ad {$show_dismiss}' data-dismiss='' data-id='".$popup['id']."'>
                                            <button id='ays_pb_dismiss_ad'>
                                                <span class='ays_pb_dismiss_ad_text_pc'>".$enable_dismiss_text."</span>
                                                <span class='ays_pb_dismiss_ad_text_mobile'>".$enable_dismiss_text_mobile."</span>
                                            </button>
                                        </div>
                                        $ays_pb_timer_desc
                                        </div>
                                </footer>
                            </div>";
        return $ubuntu_view;
    }

    public function ays_pb_template_minimal($attr){
        $popup = $this->ays_pb_set_popup_options($attr);
        $options = $popup['options'];

        $message_data = $this->ays_pb_generate_message_variables_arr($popup['ays_pb_title'], $options);

        $popup['ays_pb_custom_html'] = Ays_Pb_Data::replace_message_variables( $popup['ays_pb_custom_html'], $message_data );

        // Title text shadow
        $title_text_shadow = $this->ays_pb_generate_title_text_shadow_styles($options);

        // Box shadow
        $box_shadow = $this->ays_pb_generate_box_shadow_styles($options);
        
        //popup box font-family
        $ays_pb_font_family  = (isset($options->pb_font_family) && $options->pb_font_family != '') ? stripslashes( esc_attr($options->pb_font_family) ) : '';

        // Font Size 
        $pb_font_size = (isset($options->pb_font_size) && $options->pb_font_size != '') ? absint($options->pb_font_size) : 13;

        //close button hover text
        $ays_pb_close_button_hover_text = (isset($options->close_button_hover_text) && $options->close_button_hover_text != '') ? stripslashes( esc_attr($options->close_button_hover_text) ) : "";

        //Bg image position
        $pb_bg_image_position = (isset($options->pb_bg_image_position) && $options->pb_bg_image_position != "") ? str_ireplace('-', ' ', $options->pb_bg_image_position) : 'center center';

        $pb_bg_image_sizing = (isset($options->pb_bg_image_sizing) && $options->pb_bg_image_sizing != "") ? stripslashes( esc_attr($options->pb_bg_image_sizing) ) : 'cover';
        

        //Background Gradient
        $background_gradient = (!isset($options->enable_background_gradient)) ? 'off' : stripslashes( esc_attr($options->enable_background_gradient) );
        $pb_gradient_direction = (!isset($options->pb_gradient_direction)) ? 'horizontal' : stripslashes( esc_attr($options->pb_gradient_direction) );
        $background_gradient_color_1 = (!isset($options->background_gradient_color_1)) ? "#000000" : stripslashes( esc_attr($options->background_gradient_color_1) );
        $background_gradient_color_2 = (!isset($options->background_gradient_color_2)) ? "#fff" : stripslashes( esc_attr($options->background_gradient_color_2) );
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
        if($popup['ays_pb_bg_image'] !== ''){
            $popup['ays_pb_bg_image'] = 'background-image: url('.$popup['ays_pb_bg_image'].');
                                background-repeat: no-repeat;
                                background-size: '. $pb_bg_image_sizing .';
                                background-position: '. $pb_bg_image_position .';';
        }elseif ($background_gradient == 'on' && $popup['ays_pb_bg_image'] == '') {
            $popup['ays_pb_bg_image'] = "background-image: linear-gradient(".$pb_gradient_direction.",".$background_gradient_color_1.",".$background_gradient_color_2.");";
        } else {
            $popup['ays_pb_bg_image'] = '';
        }

        // Popup full screen
        $ays_pb_full_screen  = (isset($options->enable_pb_fullscreen) && $options->enable_pb_fullscreen == 'on') ? 'on' : 'off';

        //Show Popup Title
        $show_popup_title = $popup['show_title'] == "On" ? 'block' : 'none';

        //Show Popup Descirtion
        $show_popup_desc = $popup['show_desc'] == "On" ? 'block' : 'none';

        //Show Popup Title Mobile
        $show_title_mobile_class = $popup['show_title_mobile'] == 'On' ? 'ays_pb_show_title_on_mobile' : 'ays_pb_hide_title_on_mobile';
        
        //Show Popup Description Mobile
        $show_desc_mobile_class = $popup['show_desc_mobile']  == 'On' ? 'ays_pb_show_desc_on_mobile' : 'ays_pb_hide_desc_on_mobile';

        if ($popup['ays_pb_title'] != '') {
            $popup['ays_pb_title'] = "<h2 class='" . $show_title_mobile_class . " ays_pb_title_styles_" . $popup['id'] . "' style='color:" . $popup['ays_pb_textcolor'] . " !important; font-family:$ays_pb_font_family; {$title_text_shadow}; display: " . $show_popup_title . "'>" . $popup['ays_pb_title'] . "</h2>";
        }

        if ($popup['ays_pb_autoclose'] > 0) {
            if ($popup['ays_pb_delay'] != 0 && ($popup['ays_pb_autoclose'] < $popup['ays_pb_delay_second'] || $popup['ays_pb_autoclose'] >= $popup['ays_pb_delay_second']) ) {
                $popup['ays_pb_autoclose'] += floor($popup['ays_pb_delay_second']);
            }
        }

        if ($popup['ays_pb_description'] != '') {
            $content_desktop = Ays_Pb_Public::ays_autoembed( $popup['ays_pb_description'] );
            $popup['ays_pb_description'] = "<div class='ays_pb_description " . $show_desc_mobile_class . "' style='font-size:{$pb_font_size}px; display:" . $show_popup_desc . "'>".$content_desktop."</div>";
        }

        if($popup['ays_pb_action_buttons_type'] == 'both' || $popup['ays_pb_action_buttons_type'] == 'pageLoaded'){
            $ays_pb_flag = "data-ays-flag='false'";
        }
        if($popup['ays_pb_action_buttons_type'] == 'clickSelector'){
            $ays_pb_flag = "data-ays-flag='true'";
        }
        if ( $popup['closeButton'] == "on" ){
            $popup['closeButton'] = "ays-close-button-on-off";
        } else { $popup['closeButton'] = ""; }

        $image_header_height = (($popup['show_title'] !== "On") ?  "height: 0% !important" :  "");
        $image_content_height = (($image_header_height !== "") ?  "max-height: 100% !important" :  "");

        //popup width percentage

        $popup_width_by_percentage_px = (isset($options->popup_width_by_percentage_px) && $options->popup_width_by_percentage_px != '') ? stripslashes( esc_attr($options->popup_width_by_percentage_px) ) : 'pixels';
        if(isset($popup['ays_pb_width']) && $popup['ays_pb_width'] != ''){
            if ($popup_width_by_percentage_px && $popup_width_by_percentage_px == 'percentage') {
                if (absint(intval($popup['ays_pb_width'])) > 100 ) {
                    $pb_width = '100%';
                }else{
                    $pb_width = $popup['ays_pb_width'] . '%';
                }
            }else{
                $pb_width = $popup['ays_pb_width'] . 'px';
            }
        }else{
            $pb_width = '100%';
        }
        $ubuntu_view = "";
        
        //pb full screen
        $pb_height = '';
        if($ays_pb_full_screen == 'on'){
            $pb_width = '100%';
            $popup['ays_pb_height'] = 'auto';
            $ubuntu_view .= "
                <style>
                    .ays_minimal_window .ays_minimal_main .ays_minimal_content>p:last-child {
                        position: unset !important;
                    }
                </style>
           ";
        }else{
           $pb_width  = $popup_width_by_percentage_px == 'percentage' ? $popup['ays_pb_width'] . '%' : $popup['ays_pb_width'] . 'px';
           $pb_height = $popup['ays_pb_height'] . 'px';
        }

        if($pb_width == '0px' ||  $pb_width == '0%'){       
            $pb_width = '100%';
        }

        if($pb_height == '0px'){       
            $pb_height = '500px';
        }

        $ays_pb_padding = (isset($options->popup_content_padding) && $options->popup_content_padding != '') ? $options->popup_content_padding : '0';
        //popup padding percentage
        $popup_padding_by_percentage_px = (isset($options->popup_padding_by_percentage_px) && $options->popup_padding_by_percentage_px != '') ? stripslashes( esc_attr($options->popup_padding_by_percentage_px) ) : 'pixels';
        if(isset($ays_pb_padding) && $ays_pb_padding != ''){
            if ($popup_padding_by_percentage_px && $popup_padding_by_percentage_px == 'percentage') {
                if (absint(intval($ays_pb_padding)) > 100 ) {
                    $pb_padding = '100%';
                }else{
                    $pb_padding = $ays_pb_padding . '%';
                }
            }else{
                $pb_padding = $ays_pb_padding . 'px';
            }
        }else{
            $pb_padding = '0';
        }

        $ays_pb_timer_desc = $this->ays_pb_generate_hide_timer_text($popup, $options, $attr);

        // Social Media links
        $enable_social_links = (isset($options->enable_social_links) && $options->enable_social_links == "on") ? true : false;
        $social_links = (isset($options->social_links)) ? $options->social_links : array(
            'linkedin_link' => '',
            'facebook_link' => '',
            'twitter_link' => '',
            'vkontakte_link' => '',
            'youtube_link' => '',
            'instagram_link' => '',
            'behance_link' => '',
        );
        $ays_social_links_array = array();
        
        if($social_links != ''){
            $social_link_arr = (array)$social_links;
        }else{
            $social_link_arr = $social_links;
        }

        $linkedin_link = isset($social_link_arr['linkedin_link']) && $social_link_arr['linkedin_link'] != '' ? $social_link_arr['linkedin_link'] : '';
        $facebook_link = isset($social_link_arr['facebook_link']) && $social_link_arr['facebook_link'] != '' ? $social_link_arr['facebook_link'] : '';
        $twitter_link = isset($social_link_arr['twitter_link']) && $social_link_arr['twitter_link'] != '' ? $social_link_arr['twitter_link'] : '';
        $vkontakte_link = isset($social_link_arr['vkontakte_link']) && $social_link_arr['vkontakte_link'] != '' ? $social_link_arr['vkontakte_link'] : '';
        $youtube_link = isset($social_link_arr['youtube_link']) && $social_link_arr['youtube_link'] != '' ? $social_link_arr['youtube_link'] : '';
        $instagram_link = isset($social_link_arr['instagram_link']) && $social_link_arr['instagram_link'] != '' ? $social_link_arr['instagram_link'] : '';
        $behance_link = isset($social_link_arr['behance_link']) && $social_link_arr['behance_link'] != '' ? $social_link_arr['behance_link'] : '';
        
        if($linkedin_link != ''){
            $ays_social_links_array['Linkedin']['link'] = $linkedin_link;
            $ays_social_links_array['Linkedin']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/linkedin.svg">';
        }
        if($facebook_link != ''){
            $ays_social_links_array['Facebook']['link'] = $facebook_link;
            $ays_social_links_array['Facebook']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/facebook.svg">';
        }
        if($twitter_link != ''){
            $ays_social_links_array['Twitter']['link'] = $twitter_link;
            $ays_social_links_array['Twitter']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/twitter-x.svg">';
        }
        if($vkontakte_link != ''){
            $ays_social_links_array['VKontakte']['link'] = $vkontakte_link;
            $ays_social_links_array['VKontakte']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/vk.svg">';
        }
        if($youtube_link != ''){
            $ays_social_links_array['Youtube']['link'] = $youtube_link;
            $ays_social_links_array['Youtube']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/youtube.svg">';
        }
        if($instagram_link != ''){
            $ays_social_links_array['Instagram']['link'] = $instagram_link;
            $ays_social_links_array['Instagram']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/instagram.svg">';
        }

        if($behance_link != ''){
            $ays_social_links_array['Behance']['link'] = $behance_link;
            $ays_social_links_array['Behance']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/behance.svg">';
        }

        $ays_social_links = '';

        // Heading for social buttons
        $social_buttons_heading = (isset($options->social_buttons_heading) && $options->social_buttons_heading != '') ? stripslashes( Ays_Pb_Public::ays_autoembed( $options->social_buttons_heading ) ) : "";

        if($enable_social_links){
            $ays_social_links .= "<div class='ays-pb-social-buttons-content'>";
                $ays_social_links .= "<div class='ays-pb-social-buttons-heading'>".$social_buttons_heading."</div>";
                $ays_social_links .= "<div class='ays-pb-social-shares'>";
                    foreach($ays_social_links_array as $media => $link){
                        $ays_social_links .= "<!-- Branded " . $media . " button -->
                            <a  href='" . $link['link'] . "'
                                target='_blank'
                                title='" . $media . " link'>
                                <div class='ays-pb-share-btn-icon'>".$link['img']."</div>
                            </a>";
                    }
                $ays_social_links .= "</div>";
            $ays_social_links .= "</div>";
        }

        $close_btn_size = (isset($options->close_button_size) && $options->close_button_size != '') ? abs($options->close_button_size) : '1';

        //border style 
        $border_style = (isset($options->border_style) && $options->border_style != '') ? $options->border_style : 'solid';

        $ays_pb_sound_mute = '';

        if($popup['ays_pb_action_buttons_type'] == 'clickSelector' || $popup['ays_pb_action_buttons_type'] == 'both'){
            if(isset($options->enable_pb_sound) && $options->enable_pb_sound == "on"){
                $ays_pb_sound_mute .= "<span class='ays_pb_music_sound ays_sound_active'>
                                        ".$this->volume_up_icon."
                                    </span>";
            }else{
                $ays_pb_sound_mute = '';
            }
        }else{
            $ays_pb_sound_mute = '';
        }
        
        //Enable dismiss
        $enable_dismiss = ( isset($options->enable_dismiss) && $options->enable_dismiss == "on" ) ? true : false;
        $show_dismiss = 'ays_pb_display_none';
        if( $enable_dismiss ){
            $show_dismiss = '';
        }

        //Dismiss ad text
        $enable_dismiss_text = (isset($options->enable_dismiss_text) && $options->enable_dismiss_text != "") ? esc_html( stripslashes($options->enable_dismiss_text) ) : __("Dismiss ad", "ays-popup-box");
        
        //Dismiss ad text mobile
        if ( ( !isset($options->enable_dismiss_mobile) ) || (isset($options->enable_dismiss_mobile) && $options->enable_dismiss_mobile == 'off' ) ) {
            $enable_dismiss_text_mobile = $enable_dismiss_text;
        } else {
            $enable_dismiss_text_mobile = (isset($options->enable_dismiss_text_mobile) && $options->enable_dismiss_text_mobile != "") ? esc_html( stripslashes($options->enable_dismiss_text_mobile) ) : __("Dismiss ad", "ays-popup-box");
        }

        //Show scrollbar
        $options->show_scrollbar = ( isset( $options->show_scrollbar ) && $options->show_scrollbar != '' ) ? stripslashes( esc_attr($options->show_scrollbar) ) : 'off';
        $ays_pb_show_scrollbar = ( isset( $options->show_scrollbar ) && $options->show_scrollbar == 'on' ) ? true : false;

        $ays_pb_disable_scroll_on_popup_class = $this->ays_pb_generate_disable_popup_class($options);

        $ays_pb_show_scrollbar_class = '';
        if($ays_pb_show_scrollbar){
            $ays_pb_show_scrollbar_class = 'ays-pb-show-scrollbar';
        }

        $ubuntu_view .= "   <div class='ays_minimal_window ays-pb-modal_".$popup['id']." ".$popup['custom_class']." ays-pb-bg-styles-mobile_".$popup['id']." ays-pb-border-mobile_".$popup['id']."' {$ays_pb_flag} style='width: {$pb_width}; height: {$pb_height}; background-color: ".$popup['ays_pb_bgcolor']."; color: ".$popup['ays_pb_textcolor']." !important;font-family:{$ays_pb_font_family}; border: ".$popup['ays_pb_bordersize']."px $border_style ".$popup['ays_pb_bordercolor']."; border-radius: ".$popup['ays_pb_border_radius']."px; ".$popup['ays_pb_bg_image'].";{$box_shadow};' data-name='modern_minimal'>
                                <header class='ays_minimal_head' style='{$image_header_height}'>
                                    <div class='ays_minimal_header'>
                                        $ays_pb_sound_mute
                                        <div class='ays_popup_minimal_title'>
                                            ".$popup['ays_pb_title']."
                                        </div>
                                        <div class='ays_minimal_btn-close ".$popup['closeButton']."'>
                                            <div class='ays-pb-modal-close_".$popup['id']." ays-pb-close-button-delay' >
                                                <div class='close-minimal-btn ays_pb_pause_sound_".$popup['id']."' style='color: ".$popup['ays_pb_textcolor']." ; font-family:{$ays_pb_font_family};transform:scale({$close_btn_size})'  data-toggle='tooltip' title='" . $ays_pb_close_button_hover_text . "'></div>
                                            </div>
                                        </div>
                                    </div>
                                </header>
                                <div class='ays_minimal_main ".$ays_pb_disable_scroll_on_popup_class." ".$ays_pb_show_scrollbar_class."' style='{$image_content_height}' >
                                    <div class='ays_minimal_content'>
                                        ".$popup['ays_pb_description']."
                                        <div class='ays_content_box' style='padding: {$pb_padding};'>".
                                        (($popup['ays_pb_modal_content'] == 'shortcode') ? do_shortcode($popup['ays_pb_shortcode']) : Ays_Pb_Public::ays_autoembed($popup['ays_pb_custom_html']))
                                        ."</div>
                                        {$ays_social_links}
                                        <div class='ays-pb-dismiss-ad {$show_dismiss}' data-dismiss='' data-id='".$popup['id']."'>
                                            <button id='ays_pb_dismiss_ad'>
                                                <span class='ays_pb_dismiss_ad_text_pc'>".$enable_dismiss_text."</span>
                                                <span class='ays_pb_dismiss_ad_text_mobile'>".$enable_dismiss_text_mobile."</span>
                                            </button>
                                        </div>
                                        $ays_pb_timer_desc
                                    </div>
                                </div>
                            </div>";
        return $ubuntu_view;
    }

    public function ays_pb_template_video($attr){
        $popup = $this->ays_pb_set_popup_options($attr);
        $options = $popup['options'];

        $message_data = $this->ays_pb_generate_message_variables_arr($popup['ays_pb_title'], $options);

        $popup['ays_pb_custom_html'] = Ays_Pb_Data::replace_message_variables( $popup['ays_pb_custom_html'], $message_data );

        // Title text shadow
        $title_text_shadow = $this->ays_pb_generate_title_text_shadow_styles($options);

        // Box shadow
        $box_shadow = $this->ays_pb_generate_box_shadow_styles($options);

        //popup box font-family
        $ays_pb_font_family  = (isset($options->pb_font_family) && $options->pb_font_family != '') ? stripslashes( esc_attr($options->pb_font_family) ) : '';

        // Font Size 
        $pb_font_size = (isset($options->pb_font_size) && $options->pb_font_size != '') ? absint($options->pb_font_size) : 13;

        //close button image
        $autoclose_on_video_completion = (isset($options->enable_autoclose_on_completion) && $options->enable_autoclose_on_completion == 'on') ? 'on' : 'off';

        //close button hover text
        $ays_pb_close_button_hover_text = (isset($options->close_button_hover_text) && $options->close_button_hover_text != '') ? stripslashes( esc_attr($options->close_button_hover_text) ) : "";
        
        //Bg image position
        $pb_bg_image_position = (isset($options->pb_bg_image_position) && $options->pb_bg_image_position != "") ? str_ireplace('-', ' ', $options->pb_bg_image_position) : 'center center';

        $pb_bg_image_sizing = (isset($options->pb_bg_image_sizing) && $options->pb_bg_image_sizing != "") ? stripslashes( esc_attr($options->pb_bg_image_sizing) ) : 'cover';

        //Background Gradient
        $background_gradient = (!isset($options->enable_background_gradient)) ? 'off' : stripslashes( esc_attr($options->enable_background_gradient) );
        $pb_gradient_direction = (!isset($options->pb_gradient_direction)) ? 'horizontal' : stripslashes( esc_attr($options->pb_gradient_direction) );
        $background_gradient_color_1 = (!isset($options->background_gradient_color_1)) ? "#000000" : stripslashes( esc_attr($options->background_gradient_color_1) );
        $background_gradient_color_2 = (!isset($options->background_gradient_color_2)) ? "#fff" : stripslashes( esc_attr($options->background_gradient_color_2) );
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

        //popup full screen
        $ays_pb_full_screen  = (isset($options->enable_pb_fullscreen) && $options->enable_pb_fullscreen == 'on') ? 'on' : 'off';

        //Show Popup Title
        $show_popup_title = $popup['show_title'] == "On" ? 'block' : 'none';

        //Show Popup Descirtion
        $show_popup_desc = $popup['show_desc'] == "On" ? 'block' : 'none';

        //Show Popup Title Mobile
        $show_title_mobile_class = $popup['show_title_mobile'] == 'On' ? 'ays_pb_show_title_on_mobile' : 'ays_pb_hide_title_on_mobile';
        
        //Show Popup Description Mobile
        $show_desc_mobile_class = $popup['show_desc_mobile']  == 'On' ? 'ays_pb_show_desc_on_mobile' : 'ays_pb_hide_desc_on_mobile';

        if ($popup['ays_pb_title'] != '') {
            $popup['ays_pb_title'] = "<h2 class='" . $show_title_mobile_class . " ays_pb_title_styles_" . $popup['id'] . "' style='color:" . $popup['ays_pb_textcolor'] . " !important; font-family:$ays_pb_font_family; {$title_text_shadow}; display: " . $show_popup_title . "'>" . $popup['ays_pb_title'] . "</h2>";
        }

        if ($popup['ays_pb_autoclose'] > 0) {
            if ($popup['ays_pb_delay'] != 0 && ($popup['ays_pb_autoclose'] < $popup['ays_pb_delay_second'] || $popup['ays_pb_autoclose'] >= $popup['ays_pb_delay_second']) ) {
                $popup['ays_pb_autoclose'] += floor($popup['ays_pb_delay_second']);
            }
        }

        if ($popup['ays_pb_description'] != '') {
            $content_desktop = Ays_Pb_Public::ays_autoembed( $popup['ays_pb_description'] );
            $popup['ays_pb_description'] = "<div class='ays_pb_description " . $show_desc_mobile_class . "' style='font-size:{$pb_font_size}px; display:" . $show_popup_desc . "'>".$content_desktop."</div>";
        }

        if($popup['ays_pb_action_buttons_type'] == 'both' || $popup['ays_pb_action_buttons_type'] == 'pageLoaded'){
            $ays_pb_flag = "data-ays-flag='false'";
        }
        if($popup['ays_pb_action_buttons_type'] == 'clickSelector'){
            $ays_pb_flag = "data-ays-flag='true'";
        }
        if ( $popup['closeButton'] == "on" ){
            $popup['closeButton'] = "ays-close-button-on-off";
        } else { $popup['closeButton'] = ""; }

        $image_header_height = (($popup['show_title'] !== "On") ?  "height: 0% !important" :  "");
        $image_content_height = (($image_header_height !== "") ?  "max-height: 98% !important" :  "");

        //popup width percentage

        $popup_width_by_percentage_px = (isset($options->popup_width_by_percentage_px) && $options->popup_width_by_percentage_px != '') ? stripslashes( esc_attr($options->popup_width_by_percentage_px) ) : 'pixels';
        if(isset($popup['ays_pb_width']) && $popup['ays_pb_width'] != ''){
            if ($popup_width_by_percentage_px && $popup_width_by_percentage_px == 'percentage') {
                if (absint(intval($popup['ays_pb_width'])) > 100 ) {
                    $pb_width = '100%';
                }else{
                    $pb_width = $popup['ays_pb_width'] . '%';
                }
            }else{
                $pb_width = $popup['ays_pb_width'] . 'px';
            }
        }else{
            $pb_width = '100%';
        }
        $ubuntu_view = "";
        
        //pb full screen
        $pb_height = '';
        if($ays_pb_full_screen == 'on'){
            $pb_width = '100%';
            $popup['ays_pb_height'] = 'auto';
            $ubuntu_view .= "
                <style>
                    .ays_video_window .ays_video_main .ays_video_content>p:last-child {
                        position: unset !important;
                    }
                    .close-video-btn {
                        top: 9px !important;
                        right: 20px !important;
                    }
                </style>
           ";
        }else{
           $pb_width  = $popup_width_by_percentage_px == 'percentage' ? $popup['ays_pb_width'] . '%' : $popup['ays_pb_width'] . 'px';
           $pb_height = $popup['ays_pb_height'] . 'px';
        }

        if($pb_width == '0px' ||  $pb_width == '0%'){       
            $pb_width = '100%';
        }

        if($pb_height == '0px'){       
            $pb_height = '500px';
        }

        $ays_pb_timer_desc = $this->ays_pb_generate_hide_timer_text($popup, $options, $attr);

        // Social Media links
        $enable_social_links = (isset($options->enable_social_links) && $options->enable_social_links == "on") ? true : false;
        $social_links = (isset($options->social_links)) ? $options->social_links : array(
            'linkedin_link' => '',
            'facebook_link' => '',
            'twitter_link' => '',
            'vkontakte_link' => '',
            'youtube_link' => '',
            'instagram_link' => '',
            'behance_link' => '',
        );
        $ays_social_links_array = array();
        
        if($social_links != ''){
            $social_link_arr = (array)$social_links;
        }else{
            $social_link_arr = $social_links;
        }

        $linkedin_link = isset($social_link_arr['linkedin_link']) && $social_link_arr['linkedin_link'] != '' ? $social_link_arr['linkedin_link'] : '';
        $facebook_link = isset($social_link_arr['facebook_link']) && $social_link_arr['facebook_link'] != '' ? $social_link_arr['facebook_link'] : '';
        $twitter_link = isset($social_link_arr['twitter_link']) && $social_link_arr['twitter_link'] != '' ? $social_link_arr['twitter_link'] : '';
        $vkontakte_link = isset($social_link_arr['vkontakte_link']) && $social_link_arr['vkontakte_link'] != '' ? $social_link_arr['vkontakte_link'] : '';
        $youtube_link = isset($social_link_arr['youtube_link']) && $social_link_arr['youtube_link'] != '' ? $social_link_arr['youtube_link'] : '';
        $instagram_link = isset($social_link_arr['instagram_link']) && $social_link_arr['instagram_link'] != '' ? $social_link_arr['instagram_link'] : '';
        $behance_link = isset($social_link_arr['behance_link']) && $social_link_arr['behance_link'] != '' ? $social_link_arr['behance_link'] : '';
        
        if($linkedin_link != ''){
            $ays_social_links_array['Linkedin']['link'] = $linkedin_link;
            $ays_social_links_array['Linkedin']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/linkedin.svg">';
        }
        if($facebook_link != ''){
            $ays_social_links_array['Facebook']['link'] = $facebook_link;
            $ays_social_links_array['Facebook']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/facebook.svg">';
        }
        if($twitter_link != ''){
            $ays_social_links_array['Twitter']['link'] = $twitter_link;
            $ays_social_links_array['Twitter']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/twitter-x.svg">';
        }
        if($vkontakte_link != ''){
            $ays_social_links_array['VKontakte']['link'] = $vkontakte_link;
            $ays_social_links_array['VKontakte']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/vk.svg">';
        }
        if($youtube_link != ''){
            $ays_social_links_array['Youtube']['link'] = $youtube_link;
            $ays_social_links_array['Youtube']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/youtube.svg">';
        }
        
        if($instagram_link != ''){
            $ays_social_links_array['Instagram']['link'] = $instagram_link;
            $ays_social_links_array['Instagram']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/instagram.svg">';
        }

        if($behance_link != ''){
            $ays_social_links_array['Behance']['link'] = $behance_link;
            $ays_social_links_array['Behance']['img'] = '<img src="'.AYS_PB_PUBLIC_URL.'/images/icons/behance.svg">';
        }

        $ays_social_links = '';

        // Heading for social buttons
        $social_buttons_heading = (isset($options->social_buttons_heading) && $options->social_buttons_heading != '') ? stripslashes( Ays_Pb_Public::ays_autoembed( $options->social_buttons_heading ) ) : "";

        if($enable_social_links){
            $ays_social_links .= "<div class='ays-pb-social-buttons-content'>";
                $ays_social_links .= "<div class='ays-pb-social-buttons-heading'>".$social_buttons_heading."</div>";
                $ays_social_links .= "<div class='ays-pb-social-shares'>";
                    foreach($ays_social_links_array as $media => $link){
                        $ays_social_links .= "<!-- Branded " . $media . " button -->
                            <a  href='" . $link['link'] . "'
                                target='_blank'
                                title='" . $media . " link'>
                                <div class='ays-pb-share-btn-icon'>".$link['img']."</div>
                            </a>";
                    }
                $ays_social_links .= "</div>";
            $ays_social_links .= "</div>";
        }
        
        $close_btn_size = (isset($options->close_button_size) && $options->close_button_size != '') ? abs($options->close_button_size) : '1';

        //border style 
        $border_style = (isset($options->border_style) && $options->border_style != '') ? $options->border_style : 'solid';

        $ays_pb_sound_mute = '';

        if($popup['ays_pb_action_buttons_type'] == 'clickSelector' || $popup['ays_pb_action_buttons_type'] == 'both'){
            if(isset($options->enable_pb_sound) && $options->enable_pb_sound == "on"){
                $ays_pb_sound_mute .= "<span class='ays_pb_music_sound ays_sound_active'>
                                        ".$this->volume_up_icon."
                                    </span>";
            }else{
                $ays_pb_sound_mute = '';
            }
        }else{
            $ays_pb_sound_mute = '';
        }

        if(isset($options->video_theme_url) && !empty($options->video_theme_url)){
            $ays_pb_video_src = $options->video_theme_url;
        }else{
            $ays_pb_video_src = AYS_PB_ADMIN_URL.'/videos/video_theme.mp4';
        }

        $ubuntu_view .= "   <div class='ays_video_window ays-pb-modal_".$popup['id']." ".$popup['custom_class']." ays-pb-bg-styles-mobile_".$popup['id']." ays-pb-border-mobile_".$popup['id']."' {$ays_pb_flag} style='width: {$pb_width}; height: {$pb_height}; background-color: ".$popup['ays_pb_bgcolor']."; color: ".$popup['ays_pb_textcolor']." !important;font-family:{$ays_pb_font_family}; border: ".$popup['ays_pb_bordersize']."px $border_style ".$popup['ays_pb_bordercolor']."; border-radius: ".$popup['ays_pb_border_radius']."px; {$box_shadow}; ' data-name='modern_video'>
                                 <header class='ays_video_head'>
                                    <div class='ays_video_header'>
                                        $ays_pb_sound_mute
                                        <div class='ays_video_btn-close ".$popup['closeButton']."'>
                                            <div for='ays-pb-modal-checkbox_".$popup['id']."' class='ays-pb-modal-close_".$popup['id']." ays-pb-close-button-delay' >
                                                <div class='close-image-btn ays_pb_pause_sound_".$popup['id']."' style='color: ".$popup['ays_pb_textcolor']." ; font-family:{$ays_pb_font_family};transform:scale({$close_btn_size})'  data-toggle='tooltip' title='" . $ays_pb_close_button_hover_text . "'></div>
                                            </div>
                                        </div>
                                    </div>
                                </header>
                                <div class='ays_video_main' >
                                     <div class='ays_video_content'>
                                        <video controls src='".$ays_pb_video_src."' class='wp-video-shortcode' style='border-radius:".$attr['border_radius']."px'></video>
                                        <input type='hidden' class='autoclose_on_video_completion_check' value='".$autoclose_on_video_completion."'>
                                    </div>
                                </div>
                                $ays_pb_timer_desc
                            </div>";
        return $ubuntu_view;
    }

    public function ays_pb_template_image_type_img($attr){
        $popup = $this->ays_pb_set_popup_options($attr);
        $options = $popup['options'];

        // Box shadow
        $box_shadow = $this->ays_pb_generate_box_shadow_styles($options);

        //close button hover text
        $ays_pb_close_button_hover_text = (isset($options->close_button_hover_text) && $options->close_button_hover_text != '') ? stripslashes( esc_attr($options->close_button_hover_text) ) : "";

        //Bg image position
        $pb_bg_image_position = (isset($options->pb_bg_image_position) && $options->pb_bg_image_position != "") ? str_ireplace('-', ' ', $options->pb_bg_image_position) : 'center center';

        $pb_bg_image_sizing = (isset($options->pb_bg_image_sizing) && $options->pb_bg_image_sizing != "") ? stripslashes( esc_attr($options->pb_bg_image_sizing) ) : 'cover';

        //Background Gradient
        $background_gradient = (!isset($options->enable_background_gradient)) ? 'off' : stripslashes( esc_attr($options->enable_background_gradient) );
        $pb_gradient_direction = (!isset($options->pb_gradient_direction)) ? 'horizontal' : stripslashes( esc_attr($options->pb_gradient_direction) );
        $background_gradient_color_1 = (!isset($options->background_gradient_color_1)) ? "#000000" : stripslashes( esc_attr($options->background_gradient_color_1) );
        $background_gradient_color_2 = (!isset($options->background_gradient_color_2)) ? "#fff" : stripslashes( esc_attr($options->background_gradient_color_2) );
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
        if($popup['ays_pb_bg_image'] !== ''){
            $popup['ays_pb_bg_image'] = 'background-image: url('.$popup['ays_pb_bg_image'].');
                                background-repeat: no-repeat;
                                background-size: '.$pb_bg_image_sizing.';
                                background-position: '. $pb_bg_image_position .';';
        } elseif ($background_gradient == 'on' && $popup['ays_pb_bg_image'] == '') {
            $popup['ays_pb_bg_image'] = "background-image: linear-gradient(".$pb_gradient_direction.",".$background_gradient_color_1.",".$background_gradient_color_2.");";
        }

        //popup full screen
        $ays_pb_full_screen  = (isset($options->enable_pb_fullscreen) && $options->enable_pb_fullscreen == 'on') ? 'on' : 'off';

        if ($popup['ays_pb_autoclose'] > 0) {
            if ($popup['ays_pb_delay'] != 0 && ($popup['ays_pb_autoclose'] < $popup['ays_pb_delay_second'] || $popup['ays_pb_autoclose'] >= $popup['ays_pb_delay_second']) ) {
                $popup['ays_pb_autoclose'] += floor($popup['ays_pb_delay_second']);
            }
        }

        if($popup['ays_pb_action_buttons_type'] == 'both' || $popup['ays_pb_action_buttons_type'] == 'pageLoaded'){
            $ays_pb_flag = "data-ays-flag='false'";
        }
        if($popup['ays_pb_action_buttons_type'] == 'clickSelector'){
            $ays_pb_flag = "data-ays-flag='true'";
        }
        if ( $popup['closeButton'] == "on" ){
            $popup['closeButton'] = "ays-close-button-on-off";
        } else { $popup['closeButton'] = ""; }

        //popup width percentage
        $popup_width_by_percentage_px = (isset($options->popup_width_by_percentage_px) && $options->popup_width_by_percentage_px != '') ? stripslashes( esc_attr($options->popup_width_by_percentage_px) ) : 'pixels';
        if(isset($popup['ays_pb_width']) && $popup['ays_pb_width'] != ''){
            if ($popup_width_by_percentage_px && $popup_width_by_percentage_px == 'percentage') {
                if (absint(intval($popup['ays_pb_width'])) > 100 ) {
                    $pb_width = '100%';
                }else{
                    $pb_width = $popup['ays_pb_width'] . '%';
                }
            }else{
                $pb_width = $popup['ays_pb_width'] . 'px';
            }
        }else{
            $pb_width = '100%';
        }

        //pb full screen
        $pb_height = '';
        if($ays_pb_full_screen == 'on'){
            $pb_width = '100%';
            $popup['ays_pb_height'] = 'auto';
        }else{
            $pb_width  = $popup_width_by_percentage_px == 'percentage' ? $popup['ays_pb_width'] . '%' : $popup['ays_pb_width'] . 'px';
            $pb_height = $popup['ays_pb_height'] . 'px';
        }

        if($pb_width == '0px' ||  $pb_width == '0%'){       
            $pb_width = '100%';
        }

        if($pb_height == '0px'){       
            $pb_height = '500px';
        }

        //popup padding percentage
        $ays_pb_padding = (isset($options->popup_content_padding) && $options->popup_content_padding != 0) ? $options->popup_content_padding : '0';
        $popup_padding_by_percentage_px = (isset($options->popup_padding_by_percentage_px) && $options->popup_padding_by_percentage_px != '') ? stripslashes( esc_attr($options->popup_padding_by_percentage_px) ) : 'pixels';
        if(isset($ays_pb_padding) && $ays_pb_padding != ''){
            if ($popup_padding_by_percentage_px && $popup_padding_by_percentage_px == 'percentage') {
                if (absint(intval($ays_pb_padding)) > 100 ) {
                    $pb_padding = '100%';
                }else{
                    $pb_padding = $ays_pb_padding . '%';
                }
            }else{
                $pb_padding = $ays_pb_padding . 'px';
            }
        }else{
            $pb_padding = '0';
        }

        $ays_pb_timer_desc = $this->ays_pb_generate_hide_timer_text($popup, $options, $attr);

        //close button size 
        $close_btn_size = (isset($options->close_button_size) && $options->close_button_size != '') ? abs($options->close_button_size) : '1';

        //border style 
        $border_style = (isset($options->border_style) && $options->border_style != '') ? $options->border_style : 'solid';

        $ays_pb_sound_mute = '';

        if($popup['ays_pb_action_buttons_type'] == 'clickSelector' || $popup['ays_pb_action_buttons_type'] == 'both'){
            if(isset($options->enable_pb_sound) && $options->enable_pb_sound == "on"){
                $ays_pb_sound_mute .= "<span class='ays_pb_music_sound ays_sound_active'>
                                            ".$this->volume_up_icon."
                                    </span>";
            }else{
                $ays_pb_sound_mute = '';
            }
        }else{
            $ays_pb_sound_mute = '';
        }

        //Enable dismiss
        $enable_dismiss = ( isset($options->enable_dismiss) && $options->enable_dismiss == "on" ) ? true : false;
        $show_dismiss = 'ays_pb_display_none';
        if( $enable_dismiss ){
            $show_dismiss = '';
        }

        //Dismiss ad text
        $enable_dismiss_text = (isset($options->enable_dismiss_text) && $options->enable_dismiss_text != "") ? esc_html( stripslashes($options->enable_dismiss_text) ) : __("Dismiss ad", "ays-popup-box");
        
        //Dismiss ad text mobile
        if ( ( !isset($options->enable_dismiss_mobile) ) || (isset($options->enable_dismiss_mobile) && $options->enable_dismiss_mobile == 'off' ) ) {
            $enable_dismiss_text_mobile = $enable_dismiss_text;
        } else {
            $enable_dismiss_text_mobile = (isset($options->enable_dismiss_text_mobile) && $options->enable_dismiss_text_mobile != "") ? esc_html( stripslashes($options->enable_dismiss_text_mobile) ) : __("Dismiss ad", "ays-popup-box");
        }

        //Close button color
        $close_button_color = (isset($options->close_button_color) && $options->close_button_color != "") ? esc_attr( stripslashes( $options->close_button_color ) ) : $popup['ays_pb_textcolor'];

        //Close button hover color
        $close_button_color = (isset($options->close_button_color) && $options->close_button_color != "") ? esc_attr( stripslashes( $options->close_button_color ) ) : $popup['ays_pb_textcolor'];

        //Show scrollbar
        $options->show_scrollbar = ( isset( $options->show_scrollbar ) && $options->show_scrollbar != '' ) ? stripslashes( esc_attr($options->show_scrollbar) ) : 'off';
        $ays_pb_show_scrollbar = ( isset( $options->show_scrollbar ) && $options->show_scrollbar == 'on' ) ? true : false;

        $ays_pb_disable_scroll_on_popup_class = $this->ays_pb_generate_disable_popup_class($options);

        $ays_pb_show_scrollbar_class = '';
        if($ays_pb_show_scrollbar){
            $ays_pb_show_scrollbar_class = 'ays-pb-show-scrollbar';
        }

        // Main image src
        $image_type_img_src = (isset($options->image_type_img_src) && $options->image_type_img_src != '') ? stripslashes( esc_url($options->image_type_img_src) ) : "";

        // Main image redirect url
        $image_type_img_redirect_url = (isset($options->image_type_img_redirect_url) && $options->image_type_img_redirect_url != '') ? stripslashes( esc_url($options->image_type_img_redirect_url) ) : "";

        // Notification button 1 redirect to the new tab
        $image_type_img_redirect_to_new_tab = (isset($options->image_type_img_redirect_to_new_tab) && $options->image_type_img_redirect_to_new_tab == 'on') ? true : false;

        $main_image = "<img src='" . $image_type_img_src . "'>";

        if ($image_type_img_redirect_url != '') {
            $main_image = $this->ays_pb_wrap_into_link($image_type_img_redirect_url, $main_image, $image_type_img_redirect_to_new_tab);
        }

        $popupbox_view = "
                <div class='ays-pb-modal ays-pb-modal-image-type-img ays-pb-modal_".$popup['id']." ".$popup['custom_class']." ".$ays_pb_disable_scroll_on_popup_class." ".$ays_pb_show_scrollbar_class." ays-popup-box-main-box ays-pb-bg-styles-mobile_".$popup['id']." ays-pb-border-mobile_".$popup['id']."' {$ays_pb_flag} style='".$popup['ays_pb_bg_image'].";width: {$pb_width}; height: {$pb_height}; background-color:" .  $popup['ays_pb_bgcolor'] . "; color: " . $popup['ays_pb_textcolor'] . " !important; border: ".$popup['ays_pb_bordersize']."px  $border_style " .$popup['ays_pb_bordercolor']. "; border-radius: ".$popup['ays_pb_border_radius']."px;{$box_shadow};' >
                    " . $ays_pb_sound_mute . "
                    <div class='ays_content_box' style='padding: {$pb_padding}'>
                        " . $main_image . "
                    </div>
                    <div class='ays-pb-dismiss-ad {$show_dismiss}' data-dismiss='' data-id='{$popup['id']}'>
                        <button id='ays_pb_dismiss_ad'>
                            <span class='ays_pb_dismiss_ad_text_pc'>".$enable_dismiss_text."</span>
                            <span class='ays_pb_dismiss_ad_text_mobile'>".$enable_dismiss_text_mobile."</span>
                        </button>
                    </div>
                    $ays_pb_timer_desc
                    <div class='ays-pb-modal-close ".$popup['closeButton']." ays-pb-modal-close_".$popup['id']." ays-pb-close-button-delay ays_pb_pause_sound_".$popup['id']."' style='color: $close_button_color !important;transform:scale({$close_btn_size})' data-toggle='tooltip' title='$ays_pb_close_button_hover_text'></div>
                </div>";

        return $popupbox_view;
    }

    public function ays_pb_template_facebook($attr) {
        // check for enqueuing only one time if there is more than one fb type popups
        if (!self::$facebook_scripts_enqueued) {
            wp_enqueue_script( $this->plugin_name . '-facebook-type', AYS_PB_PUBLIC_URL . '/js/partials/ays-pb-public-facebook-type.js', array( 'jquery' ), $this->version, false );

            self::$facebook_scripts_enqueued = true;
        }

        $popup = $this->ays_pb_set_popup_options($attr);
        $options = $popup['options'];

        $message_data = $this->ays_pb_generate_message_variables_arr($popup['ays_pb_title'], $options);

        // Title text shadow
        $title_text_shadow = $this->ays_pb_generate_title_text_shadow_styles($options);

        // Box shadow
        $box_shadow = $this->ays_pb_generate_box_shadow_styles($options);

        //popup box font-family
        $ays_pb_font_family  = (isset($options->pb_font_family) && $options->pb_font_family != '') ? stripslashes( esc_attr($options->pb_font_family) ) : '';

        // Font Size 
        $pb_font_size = (isset($options->pb_font_size) && $options->pb_font_size != '') ? absint($options->pb_font_size) : 13;

        //close button hover text
        $ays_pb_close_button_hover_text = (isset($options->close_button_hover_text) && $options->close_button_hover_text != '') ? stripslashes( esc_attr($options->close_button_hover_text) ) : "";

        //Bg image position
        $pb_bg_image_position = (isset($options->pb_bg_image_position) && $options->pb_bg_image_position != "") ? str_ireplace('-', ' ', $options->pb_bg_image_position) : 'center center';

        $pb_bg_image_sizing = (isset($options->pb_bg_image_sizing) && $options->pb_bg_image_sizing != "") ? stripslashes( esc_attr($options->pb_bg_image_sizing) ) : 'cover';

        //Background Gradient
        $background_gradient = (!isset($options->enable_background_gradient)) ? 'off' : stripslashes( esc_attr($options->enable_background_gradient) );
        $pb_gradient_direction = (!isset($options->pb_gradient_direction)) ? 'horizontal' : stripslashes( esc_attr($options->pb_gradient_direction) );
        $background_gradient_color_1 = (!isset($options->background_gradient_color_1)) ? "#000000" : stripslashes( esc_attr($options->background_gradient_color_1) );
        $background_gradient_color_2 = (!isset($options->background_gradient_color_2)) ? "#fff" : stripslashes( esc_attr($options->background_gradient_color_2) );
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
        if($popup['ays_pb_bg_image'] !== ''){
            $popup['ays_pb_bg_image'] = 'background-image: url('.$popup['ays_pb_bg_image'].');
                                background-repeat: no-repeat;
                                background-size: '.$pb_bg_image_sizing.';
                                background-position: '. $pb_bg_image_position .';';
        } elseif ($background_gradient == 'on' && $popup['ays_pb_bg_image'] == '') {
            $popup['ays_pb_bg_image'] = "background-image: linear-gradient(".$pb_gradient_direction.",".$background_gradient_color_1.",".$background_gradient_color_2.");";
        }

        //popup full screen
        $ays_pb_full_screen  = (isset($options->enable_pb_fullscreen) && $options->enable_pb_fullscreen == 'on') ? 'on' : 'off';

        //Show Popup Title
        $show_popup_title = $popup['show_title'] == "On" ? 'block' : 'none';

        //Show Popup Descirtion
        $show_popup_desc = $popup['show_desc'] == "On" ? 'block' : 'none';

        //Show Popup Title Mobile
        $show_title_mobile_class = $popup['show_title_mobile'] == 'On' ? 'ays_pb_show_title_on_mobile' : 'ays_pb_hide_title_on_mobile';
        
        //Show Popup Description Mobile
        $show_desc_mobile_class = $popup['show_desc_mobile']  == 'On' ? 'ays_pb_show_desc_on_mobile' : 'ays_pb_hide_desc_on_mobile';

        if ($popup['ays_pb_title'] != '') {
            $popup['ays_pb_title'] = "<h2 class='" . $show_title_mobile_class . " ays_pb_title_styles_" . $popup['id'] . "' style='color:" . $popup['ays_pb_textcolor'] . " !important; font-family:$ays_pb_font_family; {$title_text_shadow}; font-size: 24px; margin: 0; font-weight: normal; display: " . $show_popup_title . "'>" . $popup['ays_pb_title'] . "</h2>";
        }

        if ($popup['ays_pb_autoclose'] > 0) {
            if ($popup['ays_pb_delay'] != 0 && ($popup['ays_pb_autoclose'] < $popup['ays_pb_delay_second'] || $popup['ays_pb_autoclose'] >= $popup['ays_pb_delay_second']) ) {
                $popup['ays_pb_autoclose'] += floor($popup['ays_pb_delay_second']);
            }
        }

        if ($popup['ays_pb_description'] != '') {
            $content_desktop = Ays_Pb_Public::ays_autoembed( $popup['ays_pb_description'] );
            $popup['ays_pb_description'] = "<div class='ays_pb_description " . $show_desc_mobile_class . "' style='font-size:{$pb_font_size}px; display:" . $show_popup_desc . "'>".$content_desktop."</div>";
        }

        if($popup['ays_pb_action_buttons_type'] == 'both' || $popup['ays_pb_action_buttons_type'] == 'pageLoaded'){
            $ays_pb_flag = "data-ays-flag='false'";
        }
        if($popup['ays_pb_action_buttons_type'] == 'clickSelector'){
            $ays_pb_flag = "data-ays-flag='true'";
        }
        if ( $popup['closeButton'] == "on" ){
            $popup['closeButton'] = "ays-close-button-on-off";
        } else { $popup['closeButton'] = ""; }

        //popup width percentage
        $popup_width_by_percentage_px = (isset($options->popup_width_by_percentage_px) && $options->popup_width_by_percentage_px != '') ? stripslashes( esc_attr($options->popup_width_by_percentage_px) ) : 'pixels';
        if(isset($popup['ays_pb_width']) && $popup['ays_pb_width'] != ''){
            if ($popup_width_by_percentage_px && $popup_width_by_percentage_px == 'percentage') {
                if (absint(intval($popup['ays_pb_width'])) > 100 ) {
                    $pb_width = '100%';
                }else{
                    $pb_width = $popup['ays_pb_width'] . '%';
                }
            }else{
                $pb_width = $popup['ays_pb_width'] . 'px';
            }
        }else{
            $pb_width = '100%';
        }

        //pb full screen
        $pb_height = '';
        if($ays_pb_full_screen == 'on'){
            $pb_width = '100%';
            $popup['ays_pb_height'] = 'auto';
        }else{
            $pb_width  = $popup_width_by_percentage_px == 'percentage' ? $popup['ays_pb_width'] . '%' : $popup['ays_pb_width'] . 'px';
            $pb_height = $popup['ays_pb_height'] . 'px';
        }

        if($pb_width == '0px' ||  $pb_width == '0%'){       
            $pb_width = '100%';
        }

        if($pb_height == '0px'){       
            $pb_height = '500px';
        }

        //popup padding percentage
        $ays_pb_padding = (isset($options->popup_content_padding) && $options->popup_content_padding != '') ? $options->popup_content_padding : '20';
        $popup_padding_by_percentage_px = (isset($options->popup_padding_by_percentage_px) && $options->popup_padding_by_percentage_px != '') ? stripslashes( esc_attr($options->popup_padding_by_percentage_px) ) : 'pixels';
        if(isset($ays_pb_padding) && $ays_pb_padding != ''){
            if ($popup_padding_by_percentage_px && $popup_padding_by_percentage_px == 'percentage') {
                if (absint(intval($ays_pb_padding)) > 100 ) {
                    $pb_padding = '100%';
                }else{
                    $pb_padding = $ays_pb_padding . '%';
                }
            }else{
                $pb_padding = $ays_pb_padding . 'px';
            }
        }else{
            $pb_padding = '20px';
        }

        $ays_pb_timer_desc = $this->ays_pb_generate_hide_timer_text($popup, $options, $attr);

        //close button size 
        $close_btn_size = (isset($options->close_button_size) && $options->close_button_size != '') ? abs($options->close_button_size) : '1';

        //border style 
        $border_style = (isset($options->border_style) && $options->border_style != '') ? $options->border_style : 'solid';

        $ays_pb_sound_mute = '';

        if($popup['ays_pb_action_buttons_type'] == 'clickSelector' || $popup['ays_pb_action_buttons_type'] == 'both'){
            if(isset($options->enable_pb_sound) && $options->enable_pb_sound == "on"){
                $ays_pb_sound_mute .= "<span class='ays_pb_music_sound ays_sound_active'>
                                            ".$this->volume_up_icon."
                                    </span>";
            }else{
                $ays_pb_sound_mute = '';
            }
        }else{
            $ays_pb_sound_mute = '';
        }

        //Enable dismiss
        $enable_dismiss = ( isset($options->enable_dismiss) && $options->enable_dismiss == "on" ) ? true : false;
        $show_dismiss = 'ays_pb_display_none';
        if( $enable_dismiss ){
            $show_dismiss = '';
        }

        //Dismiss ad text
        $enable_dismiss_text = (isset($options->enable_dismiss_text) && $options->enable_dismiss_text != "") ? esc_html( stripslashes($options->enable_dismiss_text) ) : __("Dismiss ad", "ays-popup-box");
        
        //Dismiss ad text mobile
        if ( ( !isset($options->enable_dismiss_mobile) ) || (isset($options->enable_dismiss_mobile) && $options->enable_dismiss_mobile == 'off' ) ) {
            $enable_dismiss_text_mobile = $enable_dismiss_text;
        } else {
            $enable_dismiss_text_mobile = (isset($options->enable_dismiss_text_mobile) && $options->enable_dismiss_text_mobile != "") ? esc_html( stripslashes($options->enable_dismiss_text_mobile) ) : __("Dismiss ad", "ays-popup-box");
        }

        //Close button color
        $close_button_color = (isset($options->close_button_color) && $options->close_button_color != "") ? esc_attr( stripslashes( $options->close_button_color ) ) : $popup['ays_pb_textcolor'];

        //Close button hover color
        $close_button_color = (isset($options->close_button_color) && $options->close_button_color != "") ? esc_attr( stripslashes( $options->close_button_color ) ) : $popup['ays_pb_textcolor'];

        //Show scrollbar
        $options->show_scrollbar = ( isset( $options->show_scrollbar ) && $options->show_scrollbar != '' ) ? stripslashes( esc_attr($options->show_scrollbar) ) : 'off';
        $ays_pb_show_scrollbar = ( isset( $options->show_scrollbar ) && $options->show_scrollbar == 'on' ) ? true : false;

        $ays_pb_disable_scroll_on_popup_class = $this->ays_pb_generate_disable_popup_class($options);

        $ays_pb_show_scrollbar_class = '';
        if($ays_pb_show_scrollbar){
            $ays_pb_show_scrollbar_class = 'ays-pb-show-scrollbar';
        }

        // Facebook page url
        $facebook_page_url = (isset($options->facebook_page_url) && $options->facebook_page_url != '') ? stripslashes( esc_url($options->facebook_page_url) ) : "";

        // Hide FB page cover photo
        $hide_fb_page_cover_photo = (isset($options->hide_fb_page_cover_photo) && $options->hide_fb_page_cover_photo == 'on') ? true : false;

        // Use small FB header
        $use_small_fb_header = (isset($options->use_small_fb_header) && $options->use_small_fb_header == 'on') ? true : false;

        $facebook_page_content = '';
        if ($facebook_page_url != '') {
            $facebook_page_content = "
            <div class='fb-page'
                data-href='" . $facebook_page_url . "'
                data-tabs='timeline'
                data-height='" . ( intval($popup['ays_pb_height']) - 200 ) . "'
                data-width='" . ($popup['ays_pb_width'] - 2 * $ays_pb_padding) . "'
                data-small-header='" . $use_small_fb_header . "'
                data-show-facepile='true'
                data-adapt-container-width='false'
                data-hide-cover=" . $hide_fb_page_cover_photo . ">
                <blockquote cite='" . $facebook_page_url . "' class='fb-xfbml-parse-ignore'>
                    <a href='" . $facebook_page_url . "' target='_top'></a>
                </blockquote>
            </div>";
        }

        $popupbox_view = "
                <div class='ays-pb-modal ays_facebook_window ays-pb-modal_".$popup['id']." ".$popup['custom_class']." ".$ays_pb_disable_scroll_on_popup_class." ".$ays_pb_show_scrollbar_class." ays-popup-box-main-box ays-pb-bg-styles-mobile_".$popup['id']." ays-pb-border-mobile_".$popup['id']."' {$ays_pb_flag} style='".$popup['ays_pb_bg_image'].";width: {$pb_width}; height: {$pb_height}; background-color:" .  $popup['ays_pb_bgcolor'] . "; color: " . $popup['ays_pb_textcolor'] . " !important; border: ".$popup['ays_pb_bordersize']."px  $border_style " .$popup['ays_pb_bordercolor']. "; border-radius: ".$popup['ays_pb_border_radius']."px;font-family:{$ays_pb_font_family};{$box_shadow};' >
                    $ays_pb_sound_mute
                    " . $popup['ays_pb_title'] . "
                    " . $popup['ays_pb_description'] . "
                " . (($popup['show_desc'] !== "On" && $popup['show_title'] !== "On") ?  '' :  '<hr class="ays-popup-hrs-default"/>')
                    
                    ."<div class='ays_content_box ays_pb_facebook_theme_container' style='padding: {$pb_padding}'>
                        " . $facebook_page_content . "
                    </div>
                    <div class='ays-pb-dismiss-ad {$show_dismiss}' data-dismiss='' data-id='{$popup['id']}'>
                        <button id='ays_pb_dismiss_ad'>
                            <span class='ays_pb_dismiss_ad_text_pc'>".$enable_dismiss_text."</span>
                            <span class='ays_pb_dismiss_ad_text_mobile'>".$enable_dismiss_text_mobile."</span>
                        </button>
                    </div>
                    $ays_pb_timer_desc
                    <div class='ays-pb-modal-close ".$popup['closeButton']." ays-pb-modal-close_".$popup['id']." ays-pb-close-button-delay ays_pb_pause_sound_".$popup['id']."' style='color: $close_button_color !important; font-family:$ays_pb_font_family;transform:scale({$close_btn_size})' data-toggle='tooltip' title='$ays_pb_close_button_hover_text'></div>
                </div>";

		return $popupbox_view;
	}

    public function ays_pb_template_notification($attr) {
        $popup = $this->ays_pb_set_popup_options($attr);
        $options = $popup['options'];

        $message_data = $this->ays_pb_generate_message_variables_arr($popup['ays_pb_title'], $options);

        $default_notification_type_components = array(
            'main_content' => 'main_content',
            'button_1' => 'button_1',
        );
        
        $default_notification_type_component_names = array(
            'main_content' => __( 'Content', "ays-popup-box" ),
            'button_1' => __( 'Button', "ays-popup-box" ),
        );

        // Height
        $pb_height = $popup['ays_pb_height'] . 'px';

        // Border style
        $border_style = (isset($options->border_style) && $options->border_style != '') ? $options->border_style : 'solid';

        // Popup box font-family
        $ays_pb_font_family  = (isset($options->pb_font_family) && $options->pb_font_family != '') ? stripslashes( esc_attr($options->pb_font_family) ) : '';

        //Bg image position
        $pb_bg_image_position = (isset($options->pb_bg_image_position) && $options->pb_bg_image_position != "") ? str_ireplace('-', ' ', $options->pb_bg_image_position) : 'center center';

        $pb_bg_image_sizing = (isset($options->pb_bg_image_sizing) && $options->pb_bg_image_sizing != "") ? stripslashes( esc_attr($options->pb_bg_image_sizing) ) : 'cover';

        //Background Gradient
        $background_gradient = (!isset($options->enable_background_gradient)) ? 'off' : stripslashes( esc_attr($options->enable_background_gradient) );
        $pb_gradient_direction = (!isset($options->pb_gradient_direction)) ? 'horizontal' : stripslashes( esc_attr($options->pb_gradient_direction) );
        $background_gradient_color_1 = (!isset($options->background_gradient_color_1)) ? "#000000" : stripslashes( esc_attr($options->background_gradient_color_1) );
        $background_gradient_color_2 = (!isset($options->background_gradient_color_2)) ? "#fff" : stripslashes( esc_attr($options->background_gradient_color_2) );
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
        if($popup['ays_pb_bg_image'] !== ''){
            $popup['ays_pb_bg_image'] = 'background-image: url('.$popup['ays_pb_bg_image'].');
                                background-repeat: no-repeat;
                                background-size: '.$pb_bg_image_sizing.';
                                background-position: '. $pb_bg_image_position .';';
        } elseif ($background_gradient == 'on' && $popup['ays_pb_bg_image'] == '') {
            $popup['ays_pb_bg_image'] = "background-image: linear-gradient(".$pb_gradient_direction.",".$background_gradient_color_1.",".$background_gradient_color_2.");";
        }

        // Box shadow
        $box_shadow = $this->ays_pb_generate_box_shadow_styles($options);

        $ays_pb_timer_desc = $this->ays_pb_generate_hide_timer_text($popup, $options, $attr);

        //popup padding percentage
        $ays_pb_padding = (isset($options->popup_content_padding) && $options->popup_content_padding != '') ? $options->popup_content_padding : '20';
        $popup_padding_by_percentage_px = (isset($options->popup_padding_by_percentage_px) && $options->popup_padding_by_percentage_px != '') ? stripslashes( esc_attr($options->popup_padding_by_percentage_px) ) : 'pixels';
        if(isset($ays_pb_padding) && $ays_pb_padding != ''){
            if ($popup_padding_by_percentage_px && $popup_padding_by_percentage_px == 'percentage') {
                if (absint(intval($ays_pb_padding)) > 100 ) {
                    $pb_padding = '100%';
                }else{
                    $pb_padding = $ays_pb_padding . '%';
                }
            }else{
                $pb_padding = $ays_pb_padding . 'px';
            }
        }else{
            $pb_padding = '20px';
        }

        //Enable dismiss
        $enable_dismiss = ( isset($options->enable_dismiss) && $options->enable_dismiss == "on" ) ? true : false;
        $show_dismiss = 'ays_pb_display_none';
        if( $enable_dismiss ){
            $show_dismiss = '';
        }

        //Dismiss ad text
        $enable_dismiss_text = (isset($options->enable_dismiss_text) && $options->enable_dismiss_text != "") ? esc_html( stripslashes($options->enable_dismiss_text) ) : __("Dismiss ad", "ays-popup-box");
        
        //Dismiss ad text mobile
        if ( ( !isset($options->enable_dismiss_mobile) ) || (isset($options->enable_dismiss_mobile) && $options->enable_dismiss_mobile == 'off' ) ) {
            $enable_dismiss_text_mobile = $enable_dismiss_text;
        } else {
            $enable_dismiss_text_mobile = (isset($options->enable_dismiss_text_mobile) && $options->enable_dismiss_text_mobile != "") ? esc_html( stripslashes($options->enable_dismiss_text_mobile) ) : __("Dismiss ad", "ays-popup-box");
        }

        if ( $popup['closeButton'] == "on" ){
            $popup['closeButton'] = "ays-close-button-on-off";
        } else { $popup['closeButton'] = ""; }

        //Close button color
        $close_button_color = (isset($options->close_button_color) && $options->close_button_color != "") ? esc_attr( stripslashes( $options->close_button_color ) ) : $popup['ays_pb_textcolor'];

        //Close button hover color
        $close_button_color = (isset($options->close_button_color) && $options->close_button_color != "") ? esc_attr( stripslashes( $options->close_button_color ) ) : $popup['ays_pb_textcolor'];

        //Close button size 
        $close_btn_size = (isset($options->close_button_size) && $options->close_button_size != '') ? abs($options->close_button_size) : '1';

        //Close button hover text
        $ays_pb_close_button_hover_text = (isset($options->close_button_hover_text) && $options->close_button_hover_text != '') ? stripslashes( esc_attr($options->close_button_hover_text) ) : "";

        // Notification type | Components, Components order
        $options->notification_type_components = (isset($options->notification_type_components) && !empty($options->notification_type_components))  ? $options->notification_type_components : $default_notification_type_components;
        $notification_type_components = (isset($options->notification_type_components) && !empty($options->notification_type_components)) ? $options->notification_type_components : array();
        $notification_type_components_order = (isset($options->notification_type_components_order) && !empty($options->notification_type_components_order)) ? $options->notification_type_components_order : $default_notification_type_components;

        if (is_object($notification_type_components) && $notification_type_components) {
            $notification_type_components = (array) $options->notification_type_components;
        }
        
        if (is_object($notification_type_components_order) && $notification_type_components_order) {
            $notification_type_components_order = (array) $options->notification_type_components_order;
        }

        foreach ($default_notification_type_components as $key => $value) {
            if ( !isset($notification_type_components[$key]) ) {
                $notification_type_components[$key] = '';
            }

            if ( !isset($notification_type_components_order[$key]) ) {
                $notification_type_components_order[$key] = $key;
            }
        }

        foreach ($notification_type_components_order as $key => $value) {
            if ( !isset($notification_type_components[$key]) ) {
                if ( isset($notification_type_components[$value]) ) {
                    $notification_type_components_order[$value] = $value;
                }
                unset($notification_type_components_order[$key]);
            }
        }

        // Notification main content
        $main_content = (isset($options->notification_main_content) && $options->notification_main_content != '') ? stripslashes($options->notification_main_content) : 'Write the custom notification banner text here.';
        $main_content = Ays_Pb_Data::replace_message_variables( $main_content, $message_data );

        // Notification button 1 text
        $notification_button_1_text = (isset($options->notification_button_1_text) && $options->notification_button_1_text != '') ? stripslashes( esc_attr($options->notification_button_1_text) ) : 'Click!';

        //Notification button 1
        $notification_button_1 = "<button>" . $notification_button_1_text . "</button>";

        // Notification button 1 redirect URL
        $notification_button_1_redirect_url = (isset($options->notification_button_1_redirect_url) && $options->notification_button_1_redirect_url != '') ? esc_url($options->notification_button_1_redirect_url) : '';

        // Notification button 1 redirect to the new tab
        $notification_button_1_redirect_to_new_tab = (isset($options->notification_button_1_redirect_to_new_tab) && $options->notification_button_1_redirect_to_new_tab == 'on') ? true : false;

        if ($notification_button_1_redirect_url != '') {
            $notification_button_1 = $this->ays_pb_wrap_into_link($notification_button_1_redirect_url, $notification_button_1, $notification_button_1_redirect_to_new_tab);
        }

        $notification_components = array(
            'main_content' => "
                <div class='ays_pb_notification_main_content'>
                    " . $main_content . "
                </div>",
            'button_1' => "
                <div class='ays_pb_notification_button_1'>
                    " . $notification_button_1 . "
                </div>",
        );

        $popupbox_view = "<div class='ays_notification_window ays-pb-modal_" . $popup['id'] . " " . $popup['custom_class'] . "ays-pb-bg-styles-mobile_" . $popup['id'] . " ays-pb-border-mobile_" . $popup['id'] . "' data-ays-flag='false' style='" . $popup['ays_pb_bg_image']."; height: {$pb_height}; background-color:" .  $popup['ays_pb_bgcolor'] . "; color: " . $popup['ays_pb_textcolor'] . " !important; border: ".$popup['ays_pb_bordersize']."px  $border_style " . $popup['ays_pb_bordercolor'] . "; border-radius: " . $popup['ays_pb_border_radius'] . "px;font-family:{$ays_pb_font_family};{$box_shadow};' >
            <div class='ays_notification_content_box ays_content_box' style='padding: {$pb_padding}'>";

        foreach($notification_type_components_order as $key) {
            $popupbox_view .= $notification_components[$key];
        }

        $popupbox_view .= "
            <div class='ays-pb-dismiss-ad {$show_dismiss}' data-dismiss='' data-id='{$popup['id']}'>
                <button id='ays_pb_dismiss_ad'>
                    <span class='ays_pb_dismiss_ad_text_pc'>".$enable_dismiss_text."</span>
                    <span class='ays_pb_dismiss_ad_text_mobile'>".$enable_dismiss_text_mobile."</span>
                </button>
            </div>
            " . $ays_pb_timer_desc . "
            <div class='ays-pb-modal-close ".$popup['closeButton']." ays-pb-modal-close_".$popup['id']." ays-pb-close-button-delay' style='color: $close_button_color !important; font-family:$ays_pb_font_family;transform:scale({$close_btn_size})' data-toggle='tooltip' title='$ays_pb_close_button_hover_text'></div>
        </div>";

        return $popupbox_view;
    }

    public function ays_pb_set_popup_options($popup_options) {

        $ays_pb_delay = intval($popup_options["delay"]);
        $options = array(
            'id' => absint( intval($popup_options["id"]) ),
            'ays_pb_shortcode' => $popup_options["shortcode"],
            'ays_pb_width' => absint( intval($popup_options["width"]) ),
            'ays_pb_height' => absint( intval($popup_options["height"]) ),
            'ays_pb_autoclose' => stripslashes( esc_attr($popup_options["autoclose"]) ),
            'ays_pb_title' => stripslashes(esc_attr( $popup_options["title"] )),
            'ays_pb_description' => $popup_options["description"],
            'ays_pb_bgcolor' => stripslashes(esc_attr( $popup_options["bgcolor"] )),
            'ays_pb_header_bgcolor' => stripslashes( esc_attr($popup_options["header_bgcolor"]) ),
            'show_desc' => stripslashes( esc_attr($popup_options["show_popup_desc"]) ),
            'show_title' => stripslashes( esc_attr($popup_options["show_popup_title"]) ),
            'show_desc_mobile' => $popup_options["show_popup_desc_mobile"],
            'show_title_mobile' => $popup_options["show_popup_title_mobile"],
            'closeButton' => stripslashes( esc_attr($popup_options["close_button"]) ),
            'ays_pb_custom_html' => $popup_options["custom_html"],
            'ays_pb_action_buttons_type' => stripslashes( esc_attr($popup_options["action_button_type"]) ),
            'ays_pb_modal_content' => stripslashes( esc_attr($popup_options["modal_content"]) ),
            'ays_pb_delay' => $ays_pb_delay,
            'ays_pb_scroll_top' => intval($popup_options["scroll_top"]),
            'ays_pb_textcolor' => (!isset($popup_options["textcolor"])) ? "#000000" : stripslashes(esc_attr($popup_options["textcolor"])),
            'ays_pb_bordersize' => (!isset($popup_options["bordersize"])) ? 0 : stripslashes( esc_attr($popup_options["bordersize"]) ),
            'ays_pb_bordercolor' => (!isset($popup_options["bordercolor"])) ? "#000000" : stripslashes(esc_attr( $popup_options["bordercolor"] )),
            'ays_pb_border_radius' => (!isset($popup_options["border_radius"])) ? "4" : stripslashes( esc_attr($popup_options["border_radius"]) ),
            'custom_class' => (isset($popup_options["custom_class"]) && $popup_options["custom_class"] != "") ? stripslashes( esc_attr($popup_options["custom_class"]) ) : "",
            'ays_pb_bg_image' => (isset($popup_options["bg_image"]) && $popup_options['bg_image'] != "" ) ? esc_url($popup_options["bg_image"]) : "",
            'ays_pb_delay_second' => (isset($ays_pb_delay) && ! empty($ays_pb_delay) && intval($ays_pb_delay) > 0) ? (($ays_pb_delay) / 1000) : 0,
            'options' => (object)array(),
        );

        if ($popup_options['options'] != '' || $popup_options['options'] != null) {
            $options['options'] = json_decode($popup_options['options']);
        }

        return $options;
    }

    public function ays_pb_generate_message_variables_arr($popup_title, $popup_options) {
        $user_data = wp_get_current_user();

        $user_display_name = ( isset( $user_data->display_name ) && $user_data->display_name != '' ) ? stripslashes( $user_data->display_name ) : '';

        $user_email = ( isset( $user_data->user_email ) && $user_data->user_email != '' ) ? stripslashes( $user_data->user_email ) : '';

        $pb_user_information = Ays_Pb_Data::get_user_profile_data();
		$user_first_name = (isset( $pb_user_information['user_first_name'] ) && $pb_user_information['user_first_name']  != "") ? $pb_user_information['user_first_name'] : '';
		$user_last_name = (isset( $pb_user_information['user_last_name'] ) && $pb_user_information['user_last_name']  != "") ? $pb_user_information['user_last_name'] : '';
        $user_wordpress_roles = (isset( $pb_user_information['user_wordpress_roles'] ) && $pb_user_information['user_wordpress_roles']  != "") ? $pb_user_information['user_wordpress_roles'] : '';
        $user_nickname = (isset( $pb_user_information['user_nickname'] ) && $pb_user_information['user_nickname']  != "") ? $pb_user_information['user_nickname'] : '';

        $author = ( isset( $popup_options->author ) && $popup_options->author != "" ) ? json_decode( $popup_options->author ) : '';
        $current_popup_author = ( isset( $author->name ) && $author->name != "" ) ? $author->name : '';

        $current_popup_author_email = "";
        if( isset($author) && !empty($author) && isset($author->id) && intval($author->id) > 0 ){
            $current_popup_author_data = get_userdata( $author->id );
            if ( isset( $current_popup_author_data ) && $current_popup_author_data ) {
                // Get popup author email
                $current_popup_author_email = ( isset( $current_popup_author_data->data->user_email ) && $current_popup_author_data->data->user_email != '' ) ? sanitize_text_field( $current_popup_author_data->data->user_email ) : '';
            } else {
                $current_popup_author_email = '';
            }
        }

        $ays_pb_protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";         
        $current_popup_page_link = esc_url( $ays_pb_protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
        $popup_current_page_link_html = "<a href='" . esc_sql( $current_popup_page_link ) . "' target='_blank'>". __( "Popup link", "ays-popup-box" ) ."</a>";

        $creation_date = ( isset( $popup_options->create_date ) && $popup_options->create_date != "" ) ? date_i18n( get_option( 'date_format' ), strtotime( $popup_options->create_date ) ) : '';

        // Current date
        $current_date = date_i18n( 'M d, Y', current_time('timestamp') );

        $message_variables_data = array(
            'popup_title' => $popup_title,
            'user_name' => $user_display_name,
            'user_email' => $user_email,
            'user_first_name' => $user_first_name,
            'user_last_name' => $user_last_name,
            'current_popup_author' => $current_popup_author,
            'current_popup_author_email' => $current_popup_author_email,
            'current_popup_page_link' => $popup_current_page_link_html,
            'user_wordpress_roles' => $user_wordpress_roles,
            'creation_date' => $creation_date,
            'current_date' => $current_date,
            'user_nickname' => $user_nickname,
        );

        return $message_variables_data;
    }

    public function ays_pb_generate_disable_popup_class($popup_options) {
        //Disable scroll on popup
        $popup_options->disable_scroll_on_popup = ( isset( $popup_options->disable_scroll_on_popup ) && $popup_options->disable_scroll_on_popup != '' ) ? $popup_options->disable_scroll_on_popup : 'off';
        $ays_pb_disable_scroll_on_popup = ( isset( $popup_options->disable_scroll_on_popup ) && $popup_options->disable_scroll_on_popup == 'on' ) ? true : false;

        //Disable scroll on popup mobile
        if ( isset( $popup_options->disable_scroll_on_popup_mobile) ) {
            if ($popup_options->disable_scroll_on_popup_mobile == '') {
                $popup_options->disable_scroll_on_popup_mobile = 'off';
            }
        } else {
            $popup_options->disable_scroll_on_popup_mobile = $popup_options->disable_scroll_on_popup;
        }
        $ays_pb_disable_scroll_on_popup_mobile = ( isset( $popup_options->disable_scroll_on_popup_mobile ) && $popup_options->disable_scroll_on_popup_mobile == 'on' ) ? true : false;

        $class_name = '';
        if($ays_pb_disable_scroll_on_popup || $ays_pb_disable_scroll_on_popup_mobile){
            $class_name = 'ays-pb-disable-scroll-on-popup';
        }

        return $class_name;
    }

    public function ays_pb_generate_hide_timer_text($popup, $popup_options, $attr) {
        //template
        $template = (isset($attr['view_type']) && $attr['view_type'] != '') ? stripslashes( esc_attr($attr['view_type']) ) : 'default';

        //hide timer
        $enable_hide_timer = (isset($popup_options->enable_hide_timer) && $popup_options->enable_hide_timer == 'on') ? 'on' : 'off';
        $hide_timer_pc_class = $enable_hide_timer == 'on' ? 'ays_pb_hide_timer_on_pc' : '';

        //hide timer mobile
        if ( isset( $popup_options->enable_hide_timer_mobile) ) {
                $enable_hide_timer_mobile = $popup_options->enable_hide_timer_mobile == 'on' ? 'on' : 'off';
        } else {
            $enable_hide_timer_mobile = $enable_hide_timer;
        }
        $ays_pb_hide_timer_mobile_class = $enable_hide_timer_mobile == 'on' ? 'ays_pb_hide_timer_on_mobile' : '';

        //autoclose mobile
        $enable_autoclose_delay_text_mobile = isset($popup_options->enable_autoclose_delay_text_mobile) && $popup_options->enable_autoclose_delay_text_mobile == 'on' ? true : false;
        $pb_autoclose_mobile = ( isset($popup_options->pb_autoclose_mobile) && $popup_options->pb_autoclose_mobile != '' && $enable_autoclose_delay_text_mobile ) ? esc_attr($popup_options->pb_autoclose_mobile) : $popup['ays_pb_autoclose'];
        if(isset($popup_options->enable_open_delay_mobile) && $popup_options->enable_open_delay_mobile == 'on'){
            if($pb_autoclose_mobile > 0){
                $popup_mobile_delay_in_seconds = ($popup_options->open_delay_mobile > 0) ? intval($popup_options->open_delay_mobile) / 1000 : 0;
                if ($popup_options->open_delay_mobile != 0 && ($pb_autoclose_mobile < $popup_mobile_delay_in_seconds || $pb_autoclose_mobile >= $popup_mobile_delay_in_seconds) ) {
                    $pb_autoclose_mobile += (floor($popup_mobile_delay_in_seconds) - 1);
                }
            }
        }
        else{
            if($pb_autoclose_mobile > 0){
                if ($popup['ays_pb_delay'] != 0 && ($pb_autoclose_mobile < $popup['ays_pb_delay_second'] || $pb_autoclose_mobile >= $popup['ays_pb_delay_second']) ) {
                    $pb_autoclose_mobile += (floor($popup['ays_pb_delay_second']) - 1);
                }
            }
        }

        if ($template == 'image' || $template == 'minimal') {
            $ays_pb_timer_desc = "<p class='ays_pb_timer " . $ays_pb_hide_timer_mobile_class . " " . $hide_timer_pc_class . " ays_pb_timer_".$popup['id']."' style='bottom:". (-30 - $popup['ays_pb_bordersize']) ."px'>".__("This will close in ", "ays-popup-box")." <span data-seconds='".$popup['ays_pb_autoclose']."' data-ays-seconds='{$attr["autoclose"]}' data-ays-mobile-seconds='{$pb_autoclose_mobile}'>".$popup['ays_pb_autoclose']."</span>".__(" seconds", "ays-popup-box")."</p>";
        } else if ($template == 'video') {
            $ays_pb_timer_desc = "<p class='ays_pb_timer " . $ays_pb_hide_timer_mobile_class . " " . $hide_timer_pc_class . " ays_pb_timer_".$popup['id']."' style=' position: absolute; right: 0; left: 0; margin: auto; bottom:". ($popup['ays_pb_bordersize'] - 50) ."px'>".__("This will close in ", "ays-popup-box")." <span data-seconds='".$popup['ays_pb_autoclose']."' data-ays-seconds='{$attr["autoclose"]}' data-ays-mobile-seconds='{$pb_autoclose_mobile}'>".$popup['ays_pb_autoclose']."</span>".__(" seconds", "ays-popup-box")."</p>";
        } else if ($template == 'notification') {
            $ays_pb_timer_desc = "<p class='ays_pb_timer ays_pb_hide_timer_on_pc ays_pb_hide_timer_on_mobile ays_pb_timer_".$popup['id']."' style='display:none'>".__("This will close in ", "ays-popup-box")." <span data-seconds='0' data-ays-seconds='0' data-ays-mobile-seconds='0'>0</span>".__(" seconds", "ays-popup-box")."</p>";
        } else {
            $ays_pb_timer_desc = "<p class='ays_pb_timer " . $ays_pb_hide_timer_mobile_class . " " . $hide_timer_pc_class . " ays_pb_timer_".$popup['id']."'>".__("This will close in ", "ays-popup-box")." <span data-seconds='".$popup['ays_pb_autoclose']."' data-ays-seconds='{$attr["autoclose"]}' data-ays-mobile-seconds='{$pb_autoclose_mobile}'>".$popup['ays_pb_autoclose']."</span>".__(" seconds", "ays-popup-box")."</p>";
        }

        return $ays_pb_timer_desc;
    }

    public function ays_pb_generate_title_text_shadow_styles($options) {
        $options->enable_pb_title_text_shadow = (isset($options->enable_pb_title_text_shadow) && $options->enable_pb_title_text_shadow == 'on') ? 'on' : 'off'; 
        $enable_pb_title_text_shadow = (isset($options->enable_pb_title_text_shadow) && $options->enable_pb_title_text_shadow == 'on') ? true : false; 
        $pb_title_text_shadow = (isset($options->pb_title_text_shadow) && $options->pb_title_text_shadow != '') ? stripslashes( esc_attr( $options->pb_title_text_shadow ) ) : 'rgba(255,255,255,0)';

        $pb_title_text_shadow_x_offset = (isset($options->pb_title_text_shadow_x_offset) && $options->pb_title_text_shadow_x_offset != '') ? stripslashes( esc_attr( $options->pb_title_text_shadow_x_offset ) ) : 2;

        $pb_title_text_shadow_y_offset = (isset($options->pb_title_text_shadow_y_offset) && $options->pb_title_text_shadow_y_offset != '') ? stripslashes( esc_attr( $options->pb_title_text_shadow_y_offset ) ) : 2;

        $pb_title_text_shadow_z_offset = (isset($options->pb_title_text_shadow_z_offset) && $options->pb_title_text_shadow_z_offset != '') ? stripslashes( esc_attr( $options->pb_title_text_shadow_z_offset ) ) : 0;
        if( $enable_pb_title_text_shadow ){
            $title_text_shadow = 'text-shadow: '.$pb_title_text_shadow_x_offset.'px '.$pb_title_text_shadow_y_offset.'px '.$pb_title_text_shadow_z_offset.'px '.$pb_title_text_shadow;
        }else{
            $title_text_shadow = "";
        }

        return $title_text_shadow;
    }

    public function ays_pb_generate_box_shadow_styles($options) {
        $options->enable_box_shadow = (isset($options->enable_box_shadow) && $options->enable_box_shadow == 'on') ? 'on' : 'off'; 
        $enable_box_shadow = (isset($options->enable_box_shadow) && $options->enable_box_shadow == 'on') ? true : false; 
        $pb_box_shadow = (isset($options->box_shadow_color) && $options->box_shadow_color != '') ? stripslashes( esc_attr( $options->box_shadow_color ) ) : '#000';

        $pb_box_shadow_x_offset = (isset($options->pb_box_shadow_x_offset) && $options->pb_box_shadow_x_offset != '') ? stripslashes( esc_attr( $options->pb_box_shadow_x_offset ) ) : 0;

        $pb_box_shadow_y_offset = (isset($options->pb_box_shadow_y_offset) && $options->pb_box_shadow_y_offset != '') ? stripslashes( esc_attr( $options->pb_box_shadow_y_offset ) ) : 0;

        $pb_box_shadow_z_offset = (isset($options->pb_box_shadow_z_offset) && $options->pb_box_shadow_z_offset != '') ? stripslashes( esc_attr( $options->pb_box_shadow_z_offset ) ) : 15;
        if( $enable_box_shadow ){
            $box_shadow = 'box-shadow: '.$pb_box_shadow_x_offset.'px '.$pb_box_shadow_y_offset.'px '.$pb_box_shadow_z_offset.'px '.$pb_box_shadow;
        }else{
            $box_shadow = "";
        }

        return $box_shadow;
    }

    public function ays_pb_wrap_into_link($href, $element, $target = false) {
        $link = array();
        $target_attr = $target ? 'target="_blank"' : '';

        $link[] = '<a href="'. $href .'" ' . $target_attr . '>';
            $link[] = $element;
        $link[] = '</a>';
        
        return implode('', $link);
    }
}
