<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://ays-pro.com/
 * @since      1.0.0
 *
 * @package    Popup_Box
 * @subpackage Popup_Box/includes
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Popup_Box
 * @subpackage Popup_Box/includes
 * @author     AYS Pro LLC <info@ays-pro.com>
 */
class Popup_Box_Integrations {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    private $capability;

    /**
     * The settings object of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      object    $settings_obj    The settings object of this plugin.
     */
    private $settings_obj;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $plugin_name The name of this plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version){

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->settings_obj = new Ays_PopupBox_Settings_Actions($this->plugin_name);
    }

    // ===== INTEGRATIONS HOOKS =====

    // Integrations popup page action hook
    public function ays_popup_page_integrations_content( $args ){

        $integrations_contents = apply_filters( 'ays_pb_popup_page_integrations_contents', array(), $args );
        
        $integrations = array();

        foreach ($integrations_contents as $key => $integrations_content) {
            $content = '<fieldset>';
            if(isset($integrations_content['title'])){
                $content .= '<legend>';
                if(isset($integrations_content['icon'])){
                    $content .= '<img class="ays_integration_logo" src="'. $integrations_content['icon'] .'" alt="">';
                }
                $content .= '<h5>'. $integrations_content['title'] .'</h5></legend>';
            }
            $content .= $integrations_content['content'];

            $content .= '</fieldset>';

            $integrations[] = $content;
        }

        echo implode('<hr/>', $integrations);
    }

    // Integrations settings page action hook
    public function ays_settings_page_integrations_content( $args ){

        $integrations_contents = apply_filters( 'ays_pb_settings_page_integrations_contents', array(), $args );
        
        $integrations = array();

        foreach ($integrations_contents as $key => $integrations_content) {
            $content = '<fieldset>';
            if(isset($integrations_content['title'])){
                $content .= '<legend>';
                if(isset($integrations_content['icon'])){
                    $content .= '<img class="ays_integration_logo" src="'. $integrations_content['icon'] .'" alt="">';
                }
                $content .= '<h5>'. $integrations_content['title'] .'</h5></legend>';
            }
            if(isset($integrations_content['content'])){
                $content .= $integrations_content['content'];
            }

            $content .= '</fieldset>';

            $integrations[] = $content;
        }

        echo implode('<hr/>', $integrations);
    }

    ////////////////////////////////////////////////////////////////////////////////////////
    //====================================================================================//
    ////////////////////////////////////////////////////////////////////////////////////////

    // ===== MailChimp integration start =====

        // MailChimp integration / popup page

        // MailChimp integration in popup page content
        public function ays_popup_page_mailchimp_content( $integrations, $args ){

            $icon = AYS_PB_ADMIN_URL .'/images/integrations/mailchimp_logo.png';
            $title = __('MailChimp Settings',"ays-popup-box");

            $content = '';

            $content .= '<div class="form-group row" style="margin:0px;">';
                $content .= '<div class="col-sm-12 ays-pro-features-v2-main-box">';
                    $content .= '<div class="ays-pro-features-v2-small-buttons-box">';
                        $content .= '';
                            $content .= '<a href="https://ays-pro.com/wordpress/popup-box" target="_blank" class="ays-pro-features-v2-upgrade-button">';
                                $content .= '<div class="ays-pro-features-v2-upgrade-icon" style="background-image: url('.esc_attr(AYS_PB_ADMIN_URL).'/images/icons/pro-features-icons/Locked_24x24.svg);" data-img-src="'.esc_attr(AYS_PB_ADMIN_URL).'/images/icons/pro-features-icons/Locked_24x24.svg"></div>';
                                $content .= '<div class="ays-pro-features-v2-upgrade-text">';
                                    $content .= __("Upgrade" , "ays-popup-box");
                                $content .= '</div>';
                            $content .= '</a>';
                        $content .= '</div>';
            $content .= '<hr>';
            $content .= '<div class="form-group row">
                <div class="col-sm-4">
                    <label for="ays_enable_mailchimp">'. __('Enable MailChimp',"ays-popup-box") .'</label>
                </div>
                <div class="col-sm-1">
                    <input type="checkbox" class="ays-enable-timer1" id="ays_enable_mailchimp" value="on" >';
            $content .= '
                </div>
            </div>
            <hr>
            <div class="form-group row">
                <div class="col-sm-4">
                    <label for="ays_mailchimp_list">'. __('MailChimp list',"ays-popup-box") .'</label>
                </div>
                <div class="col-sm-8">';
            $content .= '<select id="ays_mailchimp_list">';
            $content .= '<option value="" disabled selected>'. __( "Select list", "ays-popup-box" ) .'</option>';
            $content .= '</select>';
            $content .= '</div>
            </div>
            </div>
            </div>';

            $integrations['mailchimp'] = array(
                'content' => $content,
                'icon' => $icon,
                'title' => $title,
            );

            return $integrations;
        }

        // MailChimp integration / settings page

        // MailChimp integration in General settings page content
        public function ays_settings_page_mailchimp_content( $integrations, $args ){

            $actions = $this->settings_obj;

            $mailchimp_res = ($actions->ays_get_setting('mailchimp') === false) ? json_encode(array()) : $actions->ays_get_setting('mailchimp');
            $mailchimp = json_decode($mailchimp_res, true);
            $mailchimp_username = isset($mailchimp['username']) ? $mailchimp['username'] : '' ;
            $mailchimp_api_key = isset($mailchimp['apiKey']) ? $mailchimp['apiKey'] : '' ;

            $icon  = AYS_PB_ADMIN_URL . '/images/integrations/mailchimp_logo.png';
            $title = __( 'MailChimp', "ays-popup-box" );

            $content = '';
            $content .= '<div class="form-group row" style="margin:0px;">';
            $content .= '<div class="col-sm-12 ays-pro-features-v2-main-box">';
            $content .= '<div class="ays-pro-features-v2-small-buttons-box">';
                $content .= '';
                    $content .= '<a href="https://ays-pro.com/wordpress/popup-box" target="_blank" class="ays-pro-features-v2-upgrade-button">';
                        $content .= '<div class="ays-pro-features-v2-upgrade-icon" style="background-image: url('.esc_attr(AYS_PB_ADMIN_URL).'/images/icons/pro-features-icons/Locked_24x24.svg);" data-img-src="'.esc_attr(AYS_PB_ADMIN_URL).'/images/icons/pro-features-icons/Locked_24x24.svg"></div>';
                        $content .= '<div class="ays-pro-features-v2-upgrade-text">';
                            $content .= __("Upgrade" , "ays-popup-box");
                        $content .= '</div>';
                    $content .= '</a>';
                $content .= '</div>';
            $content .= '<div class="form-group row">
                <div class="col-sm-12">
                    <div class="form-group row" aria-describedby="aaa">
                        <div class="col-sm-3">
                            <label for="ays_mailchimp_username">'. __( 'MailChimp Username', "ays-popup-box" ) .'</label>
                        </div>
                        <div class="col-sm-9">
                            <input type="text"
                                class="ays-text-input"
                                id="ays_mailchimp_username"
                                name="ays_mailchimp_username"
                                value="'. $mailchimp_username .'"
                            />
                        </div>
                    </div>
                    <hr/>
                    <div class="form-group row" aria-describedby="aaa">
                        <div class="col-sm-3">
                            <label for="ays_mailchimp_api_key">'. __( 'MailChimp API Key', "ays-popup-box" ) .'</label>
                        </div>
                        <div class="col-sm-9">
                            <input type="text"
                                class="ays-text-input"
                                id="ays_mailchimp_api_key"
                                name="ays_mailchimp_api_key"
                                value="'. $mailchimp_api_key .'"
                            />
                        </div>
                    </div>
                    <blockquote>';
            $content .= sprintf( __( "You can get your API key from your ", "ays-popup-box" ) . "<a href='%s' target='_blank'> %s.</a>", "https://us20.admin.mailchimp.com/account/api/", "Account Extras menu" );
            $content .= '</blockquote>
                </div>
            </div>
            </div>
            </div>';

            $integrations['mailchimp'] = array(
                'content' => $content,
                'icon' => $icon,
                'title' => $title,
            );

            return $integrations;
        }

    // ===== MailChimp integration end =====

    ////////////////////////////////////////////////////////////////////////////////////////
    //====================================================================================//
    ////////////////////////////////////////////////////////////////////////////////////////

    // ===== Campaign Monitor start =====    
        // Campaign Monitor integration / popup page

        // Campaign Monitor integration in popup page content
        public function ays_popup_page_camp_monitor_content($integrations, $args){

            $icon = AYS_PB_ADMIN_URL .'/images/integrations/campaignmonitor_logo.png';
            $title = __('Campaign Monitor Settings',"ays-popup-box");
            $content = '';

            $content .= '<div class="form-group row" style="margin:0px;">';
            $content .= '<div class="col-sm-12 ays-pro-features-v2-main-box">';
            $content .= '<div class="ays-pro-features-v2-small-buttons-box">';
                $content .= '';
                    $content .= '<a href="https://ays-pro.com/wordpress/popup-box" target="_blank" class="ays-pro-features-v2-upgrade-button">';
                        $content .= '<div class="ays-pro-features-v2-upgrade-icon" style="background-image: url('.esc_attr(AYS_PB_ADMIN_URL).'/images/icons/pro-features-icons/Locked_24x24.svg);" data-img-src="'.esc_attr(AYS_PB_ADMIN_URL).'/images/icons/pro-features-icons/Locked_24x24.svg"></div>';
                        $content .= '<div class="ays-pro-features-v2-upgrade-text">';
                            $content .= __("Upgrade" , "ays-popup-box");
                        $content .= '</div>';
                    $content .= '</a>';
                $content .= '</div>';
            $content .= '<hr/>
                <div class="form-group row">
                    <div class="col-sm-4">
                        <label for="ays_enable_monitor">'.__('Enable Campaign Monitor', "ays-popup-box").'</label>
                    </div>
                    <div class="col-sm-1">
                        <input type="checkbox" class="ays-enable-timer1" id="ays_enable_monitor" value="on" />
                    </div>
                </div>
                <hr>
                <div class="form-group row">
                    <div class="col-sm-4">
                        <label for="ays_monitor_list">'.__('Campaign Monitor list', "ays-popup-box").'</label>
                    </div>
                    <div class="col-sm-8">';
                $content .= '<select id="ays_monitor_list">
                    <option disabled selected>'.__("Select List", "ays-popup-box").'</option>';
                $content .= '</select>';
            $content .= '
                    </div>
                </div>
            </div>';

            $integrations['monitor'] = array(
                'content' => $content,
                'icon'    => $icon,
                'title'   => $title,
            );

            return $integrations;
        }

        // Campaign Monitor integration / settings page

        // Campaign Monitor integration in General settings page
        public function ays_settings_page_campaign_monitor_content( $integrations, $args ){
            $actions = $this->settings_obj;
            
            $monitor_res     = ($actions->ays_get_setting('monitor') === false) ? json_encode(array()) : $actions->ays_get_setting('monitor');
            $monitor         = json_decode($monitor_res, true);
            $monitor_client  = isset($monitor['client']) ? $monitor['client'] : '';
            $monitor_api_key = isset($monitor['apiKey']) ? $monitor['apiKey'] : '';
            
            $icon  = AYS_PB_ADMIN_URL . '/images/integrations/campaignmonitor_logo.png';
            $title = __( 'Campaign Monitor', "ays-popup-box" );

            $content = '';
            $content .= '<div class="form-group row" style="margin:0px;">';
            $content .= '<div class="col-sm-12 ays-pro-features-v2-main-box">';
            $content .= '<div class="ays-pro-features-v2-small-buttons-box">';
                $content .= '';
                    $content .= '<a href="https://ays-pro.com/wordpress/popup-box" target="_blank" class="ays-pro-features-v2-upgrade-button">';
                        $content .= '<div class="ays-pro-features-v2-upgrade-icon" style="background-image: url('.esc_attr(AYS_PB_ADMIN_URL).'/images/icons/pro-features-icons/Locked_24x24.svg);" data-img-src="'.esc_attr(AYS_PB_ADMIN_URL).'/images/icons/pro-features-icons/Locked_24x24.svg"></div>';
                        $content .= '<div class="ays-pro-features-v2-upgrade-text">';
                            $content .= __("Upgrade" , "ays-popup-box");
                        $content .= '</div>';
                    $content .= '</a>';
                $content .= '</div>';
            $content .= '<div class="form-group row">
                <div class="col-sm-12">
                    <div class="form-group row" aria-describedby="aaa">
                        <div class="col-sm-3">
                            <label for="ays_monitor_client">'. __( 'Campaign Monitor Client ID', "ays-popup-box" ) .'</label>
                        </div>
                        <div class="col-sm-9">
                            <input type="text" 
                                class="ays-text-input" 
                                id="ays_monitor_client" 
                                name="ays_monitor_client"
                                value="'. $monitor_client .'"
                            />
                        </div>
                    </div>
                    <hr/>
                    <div class="form-group row" aria-describedby="aaa">
                        <div class="col-sm-3">
                            <label for="ays_monitor_api_key">'. __( 'Campaign Monitor API Key', "ays-popup-box" ) .'</label>
                        </div>
                        <div class="col-sm-9">
                            <input type="text" 
                                class="ays-text-input" 
                                id="ays_monitor_api_key" 
                                name="ays_monitor_api_key"
                                value="'. $monitor_api_key .'"
                            />
                        </div>
                    </div>
                    <blockquote>';
            $content .= __( "You can get your API key and Client ID from your Account Settings page.");
            $content .= '</blockquote>
                </div>
            </div>
            </div>
            </div>';

            $integrations['monitor'] = array(
                'content' => $content,
                'icon' => $icon,
                'title' => $title
            );

        return $integrations;
    }


    // ===== Campaign Monitor end =====

    ////////////////////////////////////////////////////////////////////////////////////////
    //====================================================================================//
    ////////////////////////////////////////////////////////////////////////////////////////

    // ===== Active Campaign start =====

        // Active Campaign integration / popup page

        // Active Campaign integration in popup page content
        public function ays_popup_page_active_camp_content($integrations, $args){

            $icon = AYS_PB_ADMIN_URL .'/images/integrations/activecampaign_logo.png';
            $title = __('ActiveCampaign Settings', "ays-popup-box");

            $content = '';
            $content .= '<div class="form-group row" style="margin:0px;">';
            $content .= '<div class="col-sm-12 ays-pro-features-v2-main-box">';
            $content .= '<div class="ays-pro-features-v2-small-buttons-box">';
                $content .= '';
                    $content .= '<a href="https://ays-pro.com/wordpress/popup-box" target="_blank" class="ays-pro-features-v2-upgrade-button">';
                        $content .= '<div class="ays-pro-features-v2-upgrade-icon" style="background-image: url('.esc_attr(AYS_PB_ADMIN_URL).'/images/icons/pro-features-icons/Locked_24x24.svg);" data-img-src="'.esc_attr(AYS_PB_ADMIN_URL).'/images/icons/pro-features-icons/Locked_24x24.svg"></div>';
                        $content .= '<div class="ays-pro-features-v2-upgrade-text">';
                            $content .= __("Upgrade" , "ays-popup-box");
                        $content .= '</div>';
                    $content .= '</a>';
                $content .= '</div>';
                    $content .= '<hr/>
                    <div class="form-group row">
                        <div class="col-sm-4">
                            <label for="ays_enable_active_camp">'. __('Enable ActiveCampaign', "ays-popup-box") .'</label>
                        </div>
                        <div class="col-sm-1">
                            <input type="checkbox" class="ays-enable-timer1" id="ays_enable_active_camp" value="on">
                        </div>
                    </div>
                    <hr/>
                    <div class="form-group row">
                        <div class="col-sm-4">
                            <label for="ays_active_camp_list">'.__('ActiveCampaign list', "ays-popup-box").'</label>
                        </div>
                        <div class="col-sm-8">';
                $content .= '<select id="ays_active_camp_list">
                    <option value="" disabled selected>'. __("Select List", "ays-popup-box") .'</option>
                    <option value="">'.__("Just create contact", "ays-popup-box").'</option>';
                $content .= '</select></div>';
            $content .= '</div><hr>';
            $content .= '
            <div class="form-group row">
                <div class="col-sm-4">
                    <label for="ays_active_camp_automation">'.__("ActiveCampaign automation", "ays-popup-box").'</label>
                </div>
                <div class="col-sm-8">';

            $content .= '<select id="ays_active_camp_automation">
                <option value="" disabled selected>'.__("Select List", "ays-popup-box").'</option>
                <option value="">'.__("Just create contact", "ays-popup-box").'</option>';
            $content .= '</select></div>';
            $content .= '</div></div>';

            $integrations['active_camp'] = array(
                'content' => $content,
                'icon'    => $icon,
                'title'   => $title,
            );

            return $integrations;
        }


        // Active Campaign integration / settings page

        // Active Campaign integration in Gengeral settings page content
        public function ays_settings_page_active_camp_content( $integrations, $args ){
            $actions = $this->settings_obj;
            
            $active_camp_res     = ($actions->ays_get_setting('active_camp') === false) ? json_encode(array()) : $actions->ays_get_setting('active_camp');
            $active_camp         = json_decode($active_camp_res, true);
            $active_camp_url     = isset($active_camp['url']) ? $active_camp['url'] : '';
            $active_camp_api_key = isset($active_camp['apiKey']) ? $active_camp['apiKey'] : '';
            
            $icon  = AYS_PB_ADMIN_URL . '/images/integrations/activecampaign_logo.png';
            $title = __( 'ActiveCampaign', "ays-popup-box" );

            $content = '';
            $content .= '<div class="form-group row" style="margin:0px;">';
            $content .= '<div class="col-sm-12 ays-pro-features-v2-main-box">';
            $content .= '<div class="ays-pro-features-v2-small-buttons-box">';
                $content .= '';
                    $content .= '<a href="https://ays-pro.com/wordpress/popup-box" target="_blank" class="ays-pro-features-v2-upgrade-button">';
                        $content .= '<div class="ays-pro-features-v2-upgrade-icon" style="background-image: url('.esc_attr(AYS_PB_ADMIN_URL).'/images/icons/pro-features-icons/Locked_24x24.svg);" data-img-src="'.esc_attr(AYS_PB_ADMIN_URL).'/images/icons/pro-features-icons/Locked_24x24.svg"></div>';
                        $content .= '<div class="ays-pro-features-v2-upgrade-text">';
                            $content .= __("Upgrade" , "ays-popup-box");
                        $content .= '</div>';
                    $content .= '</a>';
                $content .= '</div>';
            $content .= '<div class="form-group row">
                            <div class="col-sm-12">
                            <div class="form-group row" aria-describedby="aaa">
                                <div class="col-sm-3">
                                    <label for="ays_active_camp_url">'. __( 'API Access URL', "ays-popup-box" ) .'</label>
                                </div>
                                <div class="col-sm-9">
                                    <input type="text" 
                                        class="ays-text-input" 
                                        id="ays_active_camp_url" 
                                        name="ays_active_camp_url"
                                        value="'. $active_camp_url .'"
                                    />
                                </div>
                            </div>
                            <hr/>
                            <div class="form-group row" aria-describedby="aaa">
                                <div class="col-sm-3">
                                    <label for="ays_active_camp_api_key">'. __( 'API Access Key', "ays-popup-box" ) .'</label>
                                </div>
                                <div class="col-sm-9">
                                    <input type="text" 
                                        class="ays-text-input" 
                                        id="ays_active_camp_api_key" 
                                        name="ays_active_camp_api_key"
                                        value="'. $active_camp_api_key .'"
                                    />
                                </div>
                            </div>
                    <blockquote>';
            $content .= __( "Your API URL and Key can be found in your account on the My Settings page under the “Developer” tab.");
            $content .= '</blockquote>
                </div>
            </div>
            </div>
            </div>';

            $integrations['active_camp'] = array(
                'content' => $content,
                'icon' => $icon,
                'title' => $title
            );

            return $integrations;
        }

    // ===== Active Campaign end =====
    
    ////////////////////////////////////////////////////////////////////////////////////////
    //====================================================================================//
    ////////////////////////////////////////////////////////////////////////////////////////

    // ===== GetResponse start =====

        // GetResponse integration / settings page

        // GetResponse integration in General settings page content
        public function ays_settings_page_get_response_content( $integrations, $args ){

            $icon  = AYS_PB_ADMIN_URL . '/images/integrations/get_response.png';
            $title = __( 'GetResponse', "ays-popup-box" );

            $content = '';
            $content .= '<div class="form-group row" style="margin:0px;">';
            $content .= '<div class="col-sm-12 ays-pro-features-v2-main-box">';
            $content .= '<div class="ays-pro-features-v2-small-buttons-box">';
                $content .= '';
                    $content .= '<a href="https://ays-pro.com/wordpress/popup-box" target="_blank" class="ays-pro-features-v2-upgrade-button">';
                        $content .= '<div class="ays-pro-features-v2-upgrade-icon" style="background-image: url('.esc_attr(AYS_PB_ADMIN_URL).'/images/icons/pro-features-icons/Locked_24x24.svg);" data-img-src="'.esc_attr(AYS_PB_ADMIN_URL).'/images/icons/pro-features-icons/Locked_24x24.svg"></div>';
                        $content .= '<div class="ays-pro-features-v2-upgrade-text">';
                            $content .= __("Upgrade" , "ays-popup-box");
                        $content .= '</div>';
                    $content .= '</a>';
                $content .= '</div>';
                    $content .= '<div class="form-group row">
                                    <div class="col-sm-12">
                                        <div class="form-group row">
                                            <div class="col-sm-3">
                                                <label for="ays_popup_getresponse_api_key">'. __('GetResponse API Key', "ays-popup-box") .'</label>
                                            </div>
                                            <div class="col-sm-9">
                                                <input type="text" class="ays-text-input">
                                            </div>
                                        </div>';
                            $content .= '<blockquote>';
                            $content .= sprintf( __( "You can get your API key from your ", "ays-popup-box" ) . "<a href='%s' target='_blank'> %s.</a>", "https://app.getresponse.com/api", "account" );
                            $content .= '</blockquote>';
                            $content .= '<blockquote>';
                            $content .= __( "For security reasons, unused API keys expire after 90 days. When that happens, you'll need to generate a new key.", "ays-popup-box" );
                            $content .= '</blockquote>';
                            $content .= '
                                    </div>
                                </div>';
                        $content .= '
                            </div>
                        </div>';

            $integrations['get_response'] = array(
                'content' => $content,
                'icon' => $icon,
                'title' => $title,
            );

            return $integrations;
        }

        // GetResponse integration in popup page content
        public function ays_popup_page_get_response_content( $integrations, $args ){

            $icon = AYS_PB_ADMIN_URL .'/images/integrations/get_response.png';
            $title = __('GetResponse Settings',"ays-popup-box");
            $content = '';
            $content .= '<div class="form-group row" style="margin:0px;">';
            $content .= '<div class="col-sm-12 ays-pro-features-v2-main-box">';
            $content .= '<div class="ays-pro-features-v2-small-buttons-box">';
                $content .= '';
                    $content .= '<a href="https://ays-pro.com/wordpress/popup-box" target="_blank" class="ays-pro-features-v2-upgrade-button">';
                        $content .= '<div class="ays-pro-features-v2-upgrade-icon" style="background-image: url('.esc_attr(AYS_PB_ADMIN_URL).'/images/icons/pro-features-icons/Locked_24x24.svg);" data-img-src="'.esc_attr(AYS_PB_ADMIN_URL).'/images/icons/pro-features-icons/Locked_24x24.svg"></div>';
                        $content .= '<div class="ays-pro-features-v2-upgrade-text">';
                            $content .= __("Upgrade" , "ays-popup-box");
                        $content .= '</div>';
                    $content .= '</a>';
                $content .= '</div>';
                    $content .= '<hr/>';
                    $content .= '<div class="form-group row">
                                    <div class="col-sm-4">
                                        <label for="ays_popup_enable_getResponse">'. __('Enable GetResponse', "ays-popup-box") .'</label>
                                    </div>
                                    <div class="col-sm-1">
                                        <input type="checkbox" class="ays-enable-timer1">
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label>'. __('GetResponse List', "ays-popup-box") .'</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <select >
                                            <option selected disabled>Select list</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>';

            $integrations['get_response'] = array(
                'content' => $content,
                'icon' => $icon,
                'title' => $title,
            );

            return $integrations;
        }

    // ===== GetResponse end =====

    ////////////////////////////////////////////////////////////////////////////////////////
    //====================================================================================//
    ////////////////////////////////////////////////////////////////////////////////////////

    // ===== ConvertKit start =====

        // ConvertKit integration / settings page

        // ConvertKit Settings integration in General settings page content
        public function ays_settings_page_convert_kit_content( $integrations, $args ){

            $icon  = AYS_PB_ADMIN_URL . '/images/integrations/convertkit_logo.png';
            $title = __( 'ConvertKit', "ays-popup-box" );

            $content = '';
            $content .= '<div class="form-group row" style="margin:0px;">';
            $content .= '<div class="col-sm-12 ays-pro-features-v2-main-box">';
            $content .= '<div class="ays-pro-features-v2-small-buttons-box">';
                $content .= '';
                    $content .= '<a href="https://ays-pro.com/wordpress/popup-box" target="_blank" class="ays-pro-features-v2-upgrade-button">';
                        $content .= '<div class="ays-pro-features-v2-upgrade-icon" style="background-image: url('.esc_attr(AYS_PB_ADMIN_URL).'/images/icons/pro-features-icons/Locked_24x24.svg);" data-img-src="'.esc_attr(AYS_PB_ADMIN_URL).'/images/icons/pro-features-icons/Locked_24x24.svg"></div>';
                        $content .= '<div class="ays-pro-features-v2-upgrade-text">';
                            $content .= __("Upgrade" , "ays-popup-box");
                        $content .= '</div>';
                    $content .= '</a>';
                $content .= '</div>';
                    $content .= '<div class="form-group row">
                                    <div class="col-sm-12">
                                        <div class="form-group row">
                                            <div class="col-sm-3">
                                                <label for="ays_popup_convert_kit">'. __('API Key', "ays-popup-box") .'</label>
                                            </div>
                                            <div class="col-sm-9">
                                                <input type="text" class="ays-text-input">
                                            </div>
                                        </div>';
                            $content .= '<blockquote>';
                            $content .= sprintf( __( "You can get your API key from your ", "ays-popup-box" ) . "<a href='%s' target='_blank'> %s.</a>", "https://app.convertkit.com/account/edit", "Account" );
                            $content .= '</blockquote>';
                            $content .= '
                                    </div>
                                </div>';
                    $content .= '
                            </div>
                        </div>';

            $integrations['convertKit'] = array(
                'content' => $content,
                'icon' => $icon,
                'title' => $title,
            );

            return $integrations;
        }

        // ConvertKit Settings integration in popup page content
        public function ays_popup_page_convert_kit_content( $integrations, $args ){

            $icon  = AYS_PB_ADMIN_URL .'/images/integrations/convertkit_logo.png';
            $title = __('ConvertKit Settings',"ays-popup-box");

            $content = '';
            $content .= '<div class="form-group row" style="margin:0px;">';
            $content .= '<div class="col-sm-12 ays-pro-features-v2-main-box">';
            $content .= '<div class="ays-pro-features-v2-small-buttons-box">';
                $content .= '';
                    $content .= '<a href="https://ays-pro.com/wordpress/popup-box" target="_blank" class="ays-pro-features-v2-upgrade-button">';
                        $content .= '<div class="ays-pro-features-v2-upgrade-icon" style="background-image: url('.esc_attr(AYS_PB_ADMIN_URL).'/images/icons/pro-features-icons/Locked_24x24.svg);" data-img-src="'.esc_attr(AYS_PB_ADMIN_URL).'/images/icons/pro-features-icons/Locked_24x24.svg"></div>';
                        $content .= '<div class="ays-pro-features-v2-upgrade-text">';
                            $content .= __("Upgrade" , "ays-popup-box");
                        $content .= '</div>';
                    $content .= '</a>';
                $content .= '</div>';
                    $content .= '<hr/>';
                    $content .= '<div class="form-group row">
                                    <div class="col-sm-4">
                                        <label for="ays_popup_enable_convertkit">'. __('Enable ConvertKit', "ays-popup-box") .'</label>
                                    </div>
                                    <div class="col-sm-1">
                                        <input type="checkbox" class="ays-enable-timer1">
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label for="ays_popup_convertKit_list">'. __('ConvertKit List', "ays-popup-box") .'</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <select>
                                            <option selected disabled>Select list</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>';

            $integrations['convertKit'] = array(
                'content' => $content,
                'icon' => $icon,
                'title' => $title,
            );

            return $integrations;
        }

    // ===== ConvertKit end =====

    ////////////////////////////////////////////////////////////////////////////////////////
    //====================================================================================//
    ////////////////////////////////////////////////////////////////////////////////////////

        ////////////////////////////////////////////////////////////////////////////////////////
    //====================================================================================//
    ////////////////////////////////////////////////////////////////////////////////////////

    // ===== Sendinblue Settings start =====

        // Sendinblue Settings integration

        // Sendinblue Settings integration in popup page content
        public function ays_popup_page_sendinblue_content( $integrations, $args ){

            $icon  = AYS_PB_ADMIN_URL .'/images/integrations/brevo-logo.png';
            $title = __('Brevo Settings',"ays-popup-box");

            $content = '';
            $content .= '<div class="form-group row" style="margin:0px;">';
            $content .= '<div class="col-sm-12 ays-pro-features-v2-main-box">';
            $content .= '<div class="ays-pro-features-v2-small-buttons-box">';
                $content .= '';
                    $content .= '<a href="https://ays-pro.com/wordpress/popup-box" target="_blank" class="ays-pro-features-v2-upgrade-button">';
                        $content .= '<div class="ays-pro-features-v2-upgrade-icon" style="background-image: url('.esc_attr(AYS_PB_ADMIN_URL).'/images/icons/pro-features-icons/Locked_24x24.svg);" data-img-src="'.esc_attr(AYS_PB_ADMIN_URL).'/images/icons/pro-features-icons/Locked_24x24.svg"></div>';
                        $content .= '<div class="ays-pro-features-v2-upgrade-text">';
                            $content .= __("Upgrade" , "ays-popup-box");
                        $content .= '</div>';
                    $content .= '</a>';
                $content .= '</div>';
                    $content .= '<hr/>';
                    $content .= '<div class="form-group row">
                                    <div class="col-sm-4">
                                        <label for="ays_popup_enable_sendinblue">'. __('Enable Brevo', "ays-popup-box") .'</label>
                                    </div>
                                    <div class="col-sm-1">
                                        <input type="checkbox" class="ays-enable-timer1">
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label for="ays_popup_sendinblue_list">'. __('Brevo List', "ays-popup-box") .'</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <select>
                                            <option selected disabled>Select list</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>';

            $integrations['sendinblue'] = array(
                'content' => $content,
                'icon' => $icon,
                'title' => $title,
            );

            return $integrations;
        }

        // Sendinblue Settings integration / settings page

        // Sendinblue Settings integration in General settings page content
        public function ays_settings_page_sendinblue_content( $integrations, $args ){
            $icon  = AYS_PB_ADMIN_URL . '/images/integrations/brevo-logo.png';
            $title = __( 'Brevo', "ays-popup-box" );

            $content = '';
            $content .= '<div class="form-group row" style="margin:0px;">';
            $content .= '<div class="col-sm-12 ays-pro-features-v2-main-box">';
            $content .= '<div class="ays-pro-features-v2-small-buttons-box">';
                $content .= '';
                    $content .= '<a href="https://ays-pro.com/wordpress/popup-box" target="_blank" class="ays-pro-features-v2-upgrade-button">';
                        $content .= '<div class="ays-pro-features-v2-upgrade-icon" style="background-image: url('.esc_attr(AYS_PB_ADMIN_URL).'/images/icons/pro-features-icons/Locked_24x24.svg);" data-img-src="'.esc_attr(AYS_PB_ADMIN_URL).'/images/icons/pro-features-icons/Locked_24x24.svg"></div>';
                        $content .= '<div class="ays-pro-features-v2-upgrade-text">';
                            $content .= __("Upgrade" , "ays-popup-box");
                        $content .= '</div>';
                    $content .= '</a>';
                $content .= '</div>';
                    $content .= '<div class="form-group row">
                                    <div class="col-sm-12">
                                        <div class="form-group row">
                                            <div class="col-sm-3">
                                                <label for="ays_popup_sendinblue">'. __('API Key', "ays-popup-box") .'</label>
                                            </div>
                                            <div class="col-sm-9">
                                                <input type="text" class="ays-text-input">
                                            </div>
                                        </div>';
                            $content .= '<blockquote>';
                            $content .= sprintf( __( "You can get your API key from your ", "ays-popup-box" ) . "<a href='%s' target='_blank'> %s.</a>", "https://account.sendinblue.com/advanced/api", "Account" );
                            $content .= '</blockquote>';
                            $content .= '
                                    </div>
                                </div>';
                    $content .= '
                            </div>
                        </div>';

            $integrations['sendinblue'] = array(
                'content' => $content,
                'icon' => $icon,
                'title' => $title,
            );

            return $integrations;
        }

    // ===== Sendinblue Settings end =====

    ////////////////////////////////////////////////////////////////////////////////////////
    //====================================================================================//
    ////////////////////////////////////////////////////////////////////////////////////////

    // ===== MailerLite Settings start =====

        // MailerLite Settings integration

        // MailerLite Settings integration in popup page content
        public function ays_popup_page_mailerLite_content( $integrations, $args ){

            $icon  = AYS_PB_ADMIN_URL .'/images/integrations/mailerlite.png';
            $title = __('MailerLite Settings',"ays-popup-box");

            $content = '';
            $content .= '<div class="form-group row" style="margin:0px;">';
            $content .= '<div class="col-sm-12 ays-pro-features-v2-main-box">';
            $content .= '<div class="ays-pro-features-v2-small-buttons-box">';
                $content .= '';
                    $content .= '<a href="https://ays-pro.com/wordpress/popup-box" target="_blank" class="ays-pro-features-v2-upgrade-button">';
                        $content .= '<div class="ays-pro-features-v2-upgrade-icon" style="background-image: url('.esc_attr(AYS_PB_ADMIN_URL).'/images/icons/pro-features-icons/Locked_24x24.svg);" data-img-src="'.esc_attr(AYS_PB_ADMIN_URL).'/images/icons/pro-features-icons/Locked_24x24.svg"></div>';
                        $content .= '<div class="ays-pro-features-v2-upgrade-text">';
                            $content .= __("Upgrade" , "ays-popup-box");
                        $content .= '</div>';
                    $content .= '</a>';
                $content .= '</div>';
                    $content .= '<hr/>';
                    $content .= '<div class="form-group row">
                                    <div class="col-sm-4">
                                        <label for="ays_popup_enable_mailerlite">'. __('Enable MailerLite', "ays-popup-box") .'</label>
                                    </div>
                                    <div class="col-sm-1">
                                        <input type="checkbox" class="ays-enable-timer1">
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label for="ays_popup_mailerlite_list">'. __('MailerLite List', "ays-popup-box") .'</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <select>
                                            <option selected disabled>Select list</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>';

            $integrations['mailerLite'] = array(
                'content' => $content,
                'icon' => $icon,
                'title' => $title,
            );

            return $integrations;
        }

        // MailerLite Settings integration / settings page

        // MailerLite Settings integration in General settings page content
        public function ays_settings_page_mailerLite_content( $integrations, $args ){

            $actions = $this->settings_obj;

            // MailerLite Settings
            $mailerLite_res     = ($actions->ays_get_setting('mailerLite') === false) ? json_encode(array()) : $actions->ays_get_setting('mailerLite');
            $mailerLite         = json_decode($mailerLite_res, true);
            $mailerLite_api_key = isset($mailerLite['api_key']) && $mailerLite['api_key'] != "" ? esc_attr($mailerLite['api_key']) : '';

            $icon  = AYS_PB_ADMIN_URL . '/images/integrations/mailerlite.png';
            $title = __( 'MailerLite', "ays-popup-box" );

            $content = '';
            $content .= '<div class="form-group row" style="margin:0px;">';
            $content .= '<div class="col-sm-12 ays-pro-features-v2-main-box">';
            $content .= '<div class="ays-pro-features-v2-small-buttons-box">';
                $content .= '';
                    $content .= '<a href="https://ays-pro.com/wordpress/popup-box" target="_blank" class="ays-pro-features-v2-upgrade-button">';
                        $content .= '<div class="ays-pro-features-v2-upgrade-icon" style="background-image: url('.esc_attr(AYS_PB_ADMIN_URL).'/images/icons/pro-features-icons/Locked_24x24.svg);" data-img-src="'.esc_attr(AYS_PB_ADMIN_URL).'/images/icons/pro-features-icons/Locked_24x24.svg"></div>';
                        $content .= '<div class="ays-pro-features-v2-upgrade-text">';
                            $content .= __("Upgrade" , "ays-popup-box");
                        $content .= '</div>';
                    $content .= '</a>';
                $content .= '</div>';
                $content .= '<div class="form-group row">
                                <div class="col-sm-12">
                                    <div class="form-group row">
                                        <div class="col-sm-3">
                                            <label for="ays_popup_mailerlite">'. __('API Key', "ays-popup-box") .'</label>
                                        </div>
                                        <div class="col-sm-9">
                                            <input type="text" class="ays-text-input">
                                        </div>
                                    </div>';
                        $content .= '<blockquote>';
                        $content .= sprintf( __( "You can get your API key from your ", "ays-popup-box" ) . "<a href='%s' target='_blank'> %s.</a>", "https://app.mailerlite.com/integrations/api", "Account" );
                        $content .= '</blockquote>';
                        $content .= '
                                </div>
                            </div>';
                $content .= '
                        </div>
                    </div>';

            $integrations['mailerLite'] = array(
                'content' => $content,
                'icon'    => $icon,
                'title'   => $title
            );

            return $integrations;
        }

    // ===== MailerLite Settings end =====

    ////////////////////////////////////////////////////////////////////////////////////////
    //====================================================================================//
    ////////////////////////////////////////////////////////////////////////////////////////
}
