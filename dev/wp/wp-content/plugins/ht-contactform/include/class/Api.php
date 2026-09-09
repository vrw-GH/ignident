<?php
namespace HtContactForm\Block\Api;

use Exception;
use WP_REST_Server;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load general WP action hook
 */
class Api {
    
    // Declare the property $namespace.
    private $namespace;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->namespace = 'htcontactform/v1';
	}

	/**
	 * Resgister routes
	 */
	public function register_routes() {

        register_rest_route(  $this->namespace, 'posts', 
            [
                'methods' => WP_REST_Server::READABLE,
                'args' => [
                    'wpnonce'    => []
                ],
                'callback'            => [ $this, 'get_contactform_post' ],
                'permission_callback' => [ $this, 'permission_check' ],
            ]
        );

        register_rest_route(  $this->namespace, 'posts/ht-form', 
            [
                'methods' => WP_REST_Server::READABLE,
                'args' => [
                    'wpnonce'    => []
                ],
                'callback'            => [ $this, 'get_ht_form_post' ],
                'permission_callback' => [ $this, 'permission_check' ],
            ]
        );

	}

    /**
     * Api permission check
     */
    public function permission_check() {
        if( current_user_can( 'edit_posts' ) ){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Get category data
     */
    public function get_contactform_post( $request ){
        
        if ( !wp_verify_nonce( $_REQUEST['wpnonce'], 'htcontactform-nonce') ){
            return rest_ensure_response([]);
        }

        $formlist = array();
        $forms_args = array( 'posts_per_page' => -1, 'post_type'=> 'wpcf7_contact_form' );
        $forms = get_posts( $forms_args );
        if( $forms ){
            foreach ( $forms as $form ){
                $formlist[$form->ID] = $form->post_title;
            }
        }else{
            $formlist['0'] = __('Form not found','ht-contactform');
        }
        return rest_ensure_response( $formlist );

    }

    /**
     * Get category data
     */
    public function get_ht_form_post( $request ){
        
        if ( !wp_verify_nonce( $_REQUEST['wpnonce'], 'htcontactform-nonce') ){
            return rest_ensure_response([]);
        }

        $formlist = array();
        $forms_args = array( 'posts_per_page' => -1, 'post_type'=> 'ht_form' );
        $forms = get_posts( $forms_args );
        if( $forms ){
            foreach ( $forms as $form ){
                $formlist[$form->ID] = $form->post_title;
            }
        }else{
            $formlist['0'] = __('Form not found','ht-contactform');
        }
        return rest_ensure_response( $formlist );
    }
	
}
