<?php

class Ays_Pb_Ays_Welcome {

    /**
     * Hidden welcome page slug.
     *
     * @since 4.6.4
     */
    const SLUG = 'ays-pb-getting-started';

    /**
     * Primary class constructor.
     *
     * @since 4.6.4
     */
    public function __construct() {
        add_action( 'plugins_loaded', [ $this, 'hooks' ] );
    }

    public function hooks() {
		add_action( 'admin_menu', [ $this, 'register' ] );
		add_action( 'admin_head', [ $this, 'hide_menu' ] );
		add_action( 'admin_init', [ $this, 'redirect' ], 9999 );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
        // add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

	/**
	 * Register the pages to be used for the Welcome screen (and tabs).
	 *
	 * These pages will be removed from the Dashboard menu, so they will
	 * not actually show. Sneaky, sneaky.
	 *
	 * @since 1.0.0
	 */
	public function register() {
        add_dashboard_page(
			esc_html__( 'Welcome to Popup Box', "ays-popup-box" ),
			esc_html__( 'Welcome to Popup Box', "ays-popup-box" ),
			'manage_options',
			self::SLUG,
			[ $this, 'output' ]
		);
	}

    /**
     * Removed the dashboard pages from the admin menu.
     *
     * This means the pages are still available to us, but hidden.
     *
     * @since 4.6.4
     */
    public function hide_menu() {

        remove_submenu_page( 'index.php', self::SLUG );
    }

    /**
     * Welcome screen redirect.
     *
     * This function checks if a new install or update has just occurred. If so,
     * then we redirect the user to the appropriate page.
     *
     * @since 4.6.4
     */
    public function redirect() {

        $current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';

        // Check if we are already on the welcome page.
        // if ( $current_page === self::SLUG ) {
        //     return;
        // }

        $terms_activation = get_option('ays_pb_show_agree_terms');
        $first_activation = get_option('ays_pb_first_time_activation_page', false);

        if($current_page === self::SLUG && $terms_activation && $terms_activation == 'hide'){
            wp_safe_redirect( admin_url( 'admin.php?page=ays-pb' ) );
        }

        if(isset($_POST['ays_pb_agree_terms']) && $_POST['ays_pb_agree_terms'] === 'agree'){
            $this->ays_pb_request( 'agree' );
            update_option('ays_pb_agree_terms', 'true');
            update_option('ays_pb_show_agree_terms', 'hide');
            wp_safe_redirect( admin_url( 'admin.php?page=ays-pb' ) );
        }

        if(isset($_POST['ays_pb_cancel_terms']) && $_POST['ays_pb_cancel_terms'] === 'cancel'){
            $this->ays_pb_request( 'cancel' );
            update_option('ays_pb_agree_terms', 'false');
            update_option('ays_pb_show_agree_terms', 'hide');
            wp_safe_redirect( admin_url( 'admin.php?page=ays-pb' ) );
        }

        if($current_page === self::SLUG){
            return;
        }
        if ( isset($_GET['page']) && strpos($_GET['page'], AYS_PB_NAME) !== false && !$terms_activation && $first_activation) {
            wp_safe_redirect( admin_url( 'index.php?page=' . self::SLUG ) );
            exit;
        }
        
    }

    /**
     * Enqueue custom CSS styles for the welcome page.
     *
     * @since 4.6.4
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'ays-pb-welcome-css', 
            esc_url(AYS_PB_ADMIN_URL) . '/css/ays-pb-welcome.css',
            array(), false, 'all');
    }

    /**
     * Getting Started screen. Shows after first install.
     *
     * @since 1.0.0
     */
    public function output() {
        ?>
            <style>
                #wpcontent  {
                    padding-left: 0 !important;
                    position: relative;
                }

                .notice,
                .ays-notice-banner,
                #wpfooter,
                #wpbody-content .ays_ask_question_content{
                    display: none !important;
                }
            </style>
            <form method="POST">
                <div id="ays-pb-welcome">        
                    <div class="ays-pb-welcome-container">        
                        <div class="ays-pb-welcome-intro">        
                            <div class="ays-pb-welcome-logo">
                                <img src="<?php echo esc_url(AYS_PB_ADMIN_URL); ?>/images/icons/icon-popup-128x128.png" alt="<?php echo esc_html__( 'Popup Box Logo', "ays-popup-box" ); ?>">
                            </div>
                            <div class="ays-pb-welcome-block">
                                <h1><?php echo esc_html__( 'Thank you for using Popup Box plugin !', "ays-popup-box" ); ?></h1>
                                <h6><?php echo esc_html__( 'To enhance the user experience of our product, we would like to request permission to track certain user interactions. Please rest assured that this tracking will be minimal and will only involve non-sensitive actions, such as specific clicks within the interface. No personal or sensitive data will be collected.', "ays-popup-box" ); ?></h6>
                                <h6 style="margin-top: 20px;"><?php echo esc_html__( 'Our goal is solely to improve the functionality and usability of the product based on user behavior.', "ays-popup-box" ); ?></h6>
                            </div>        
                            <div class="ays-pb-welcome-block-buttons">        
                                <div class="ays-pb-welcome-button-wrap ays-pb-clear">
                                    <div class="ays-pb-welcome-left">
                                        <button 
                                        class="ays-pb-tu-cancel" 
                                        type="submit"
                                         name="ays_pb_cancel_terms"
                                          value="cancel">
                                            <?php echo esc_html__( 'Cancel', "ays-popup-box" ); ?>
                                        </button>
                                    </div>
                                    <div class="ays-pb-welcome-right">
                                        <button class="ays-pb-tu-agree" target="_blank" type="submit" name="ays_pb_agree_terms" value="agree">
                                            <?php echo esc_html__( 'Agree & continue', "ays-popup-box" ); ?>
                                        </button>
                                    </div>
                                </div>        
                            </div>        
                        </div>
                    </div>
                </div>
            </form>
        <?php
    }

    public function ays_pb_request($cta){
        $curl = curl_init();

        $api_url = "https://poll-plugin.com/popup-box/";

        // $data = array(
        //     'type'  => 'popup-box',
        //     'cta'   => $cta,
        // );

        wp_remote_post( $api_url, array(
            'timeout' => 30,
            'body' => wp_json_encode(array(
                'type'  => 'popup-box',
                'cta'   => $cta,
            )),
        ) );
    }
}
//new Ays_Pb_Ays_Welcome();