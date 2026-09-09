<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Constructor Parameters
 *
 * @param string    $text_domain your plugin text domain.
 * @param string    $parent_menu_slug the menu slug name where the "Recommendations" submenu will appear.
 * @param string    $submenu_label To change the submenu name.
 * @param string    $submenu_page_name an unique page name for the submenu.
 * @param int       $priority Submenu priority adjust.
 * @param string    $hook_suffix use it to load this library assets only to the recommedded plugins page. Not into the whol admin area.
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( class_exists('Hasthemes\HTContact_Form\Recommended_Plugins') ){
    $recommendations = new Hasthemes\HTContact_Form\Recommended_Plugins(
        array(
            'text_domain'       => 'ht-contactform',
            'parent_menu_slug'  => 'htcontact-form',
            'menu_type'         => 'submenu',
            'menu_icon'         => 'dashicons-email-alt',
            'menu_capability'   => 'manage_options',
            'menu_page_slug'    => '',
            'priority'          => 300,
            'assets_url'        => HTCONTACTFORM_PL_URL.'/assets',
            'hook_suffix'       => 'ht-contact-form_page_ht-contactform_extensions',
        )
    );

    // ShopLentor is shown in "Recommended Plugins" on stores that already run
    // WooCommerce, and in the "WooCommerce" tab on sites that do not.
    $woocommerce_active = class_exists( 'WooCommerce' );

    $shoplentor = array(
        'slug'      => 'woolentor-addons',
        'location'  => 'woolentor_addons_elementor.php',
        'name'      => esc_html__( 'ShopLentor – All-in-One WooCommerce Growth & Store Enhancement Plugin', 'ht-contactform' )
    );

    $recommendations->add_new_tab( array(

        'title' => esc_html__( 'Recommended Plugins', 'ht-contactform' ),
        'active' => true,
        'plugins' => array_merge(

            $woocommerce_active ? array( $shoplentor ) : array(),

            array(

                array(
                    'slug'      => 'support-genix-lite',
                    'location'  => 'support-genix-lite.php',
                    'name'      => esc_html__( 'Support Genix – Helpdesk, AI Chatbot, Knowledge Base & Customer Support Ticketing System', 'ht-contactform' )
                ),

                array(
                    'slug'      => 'kelune-crm',
                    'location'  => 'kelune-crm.php',
                    'name'      => esc_html__( 'Kelune CRM – Contact Management, Email Marketing, Newsletter & Marketing Automation', 'ht-contactform' )
                ),

                array(
                    'slug'      => 'hashbar-wp-notification-bar',
                    'location'  => 'init.php',
                    'name'      => esc_html__( 'HashBar – Announcement, Notification Bar & Popup Campaign', 'ht-contactform' )
                ),

                array(
                    'slug'      => 'ht-mega-for-elementor',
                    'location'  => 'htmega_addons_elementor.php',
                    'name'      => esc_html__( 'HT Mega Addons for Elementor – Elementor Widgets & Template Builder', 'ht-contactform' )
                ),

                array(
                    'slug'      => 'insert-headers-and-footers-script',
                    'location'  => 'init.php',
                    'name'      => esc_html__( 'Insert Headers and Footers Code – HT Script', 'ht-contactform' )
                ),

                array(
                    'slug'      => 'wp-plugin-manager',
                    'location'  => 'plugin-main.php',
                    'name'      => esc_html__( 'WP Plugin Manager – Deactivate plugins per page', 'ht-contactform' )
                ),

                array(
                    'slug'      => 'cookieray',
                    'location'  => 'cookieray.php',
                    'name'      => esc_html__( 'CookieRay – Cookie Banner for Cookie Consent (GDPR/CCPA Compliant)', 'ht-contactform' )
                ),

                array(
                    'slug'      => 'cf7-extensions-pro',
                    'location'  => 'cf7-extensions-pro.php',
                    'name'      => esc_html__( 'Extensions For CF7 Pro', 'ht-contactform' ),
                    'link'      => 'https://hasthemes.com/plugins/cf7-extensions/',
                    'author_link'=> 'https://hasthemes.com/',
                    'description'=> esc_html__( 'Contact Form7 Extensions plugin is a fantastic WordPress plugin that enriches the functionalities of Contact Form 7.This all-in-one WordPress plugin will help you turn any contact page into a well-organized, engaging tool for communicating with your website visitors by providing tons of advanced features like drag and drop file upload, repeater field, trigger error for already submitted forms, popup form response, country flags and dial codes with a telephone input field and acceptance field, etc. in addition to its basic features.', 'ht-contactform' ),
                ),

                array(
                    'slug'      => 'htmega-pro',
                    'location'  => 'htmega_pro.php',
                    'name'      => esc_html__( 'HT Mega Pro', 'ht-contactform' ),
                    'link'      => 'https://hasthemes.com/plugins/ht-mega-pro/',
                    'author_link'=> 'https://hasthemes.com/',
                    'description'=> esc_html__( 'HTMega is an absolute addon for elementor that includes 80+ elements & 360 Blocks with unlimited variations. HT Mega brings limitless possibilities. Embellish your site with the elements of HT Mega.', 'ht-contactform' ),
                ),

            )
        )

    ) );

    $recommendations->add_new_tab( array(

        'title' => esc_html__( 'WooCommerce', 'ht-contactform' ),
        'plugins' => array_merge(

            $woocommerce_active ? array() : array( $shoplentor ),

            array(

                array(
                    'slug'      => 'recurio',
                    'location'  => 'recurio.php',
                    'name'      => esc_html__( 'Recurio – Ultimate Subscription for WooCommerce', 'ht-contactform' )
                ),

                array(
                    'slug'      => 'whols-pro',
                    'location'  => 'whols-pro.php',
                    'name'      => esc_html__( 'Whols Pro', 'ht-contactform' ),
                    'link'      => 'https://hasthemes.com/plugins/whols-woocommerce-wholesale-prices/',
                    'author_link'=> 'https://hasthemes.com/',
                    'description'=> esc_html__( 'Whols is an outstanding WordPress plugin for WooCommerce that allows store owners to set wholesale prices for the products of their online stores. This plugin enables you to show special wholesale prices to the wholesaler. Users can easily request to become a wholesale customer by filling out a simple online registration form. Once the registration is complete, the owner of the store will be able to review the request and approve the request either manually or automatically.', 'ht-contactform' ),
                ),

            )
        )

    ) );

    $recommendations->add_new_tab( array(

        'title' => esc_html__( 'Popular', 'ht-contactform' ),
        'plugins' => array(

            array(
                'slug'      => 'wp-plugin-manager',
                'location'  => 'plugin-main.php',
                'name'      => esc_html__( 'WP Plugin Manager – Deactivate plugins per page', 'ht-contactform' )
            ),
            array(
                'slug'      => 'ht-easy-google-analytics',
                'location'  => 'ht-easy-google-analytics.php',
                'name'      => esc_html__( 'HT Easy GA4 – Google Analytics WordPress Plugin', 'ht-contactform' )
            ),
            array(
                'slug'      => 'cookieray',
                'location'  => 'cookieray.php',
                'name'      => esc_html__( 'CookieRay – Cookie Banner for Cookie Consent (GDPR/CCPA Compliant)', 'ht-contactform' )
            ),
            array(
                'slug'      => 'insert-headers-and-footers-script',
                'location'  => 'init.php',
                'name'      => esc_html__( 'Insert Headers and Footers Code – HT Script', 'ht-contactform' )
            ),
            array(
                'slug'      => 'pixelavo',
                'location'  => 'pixelavo.php',
                'name'      => esc_html__( 'Pixelavo – Server Side Tracking & Pixel + AI Ads Tools', 'ht-contactform' )
            ),
            array(
                'slug'      => 'courseglade-lms',
                'location'  => 'courseglade-lms.php',
                'name'      => esc_html__( 'CourseGlade LMS – Online Course & eLearning Platform', 'ht-contactform' )
            ),
        )
    ) );
}
