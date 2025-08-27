<?php
if ( ! defined( 'ABSPATH' ) )
    exit;

add_action('wp_ajax_ips_backlink', 'xyz_ips_ajax_backlink');
add_action("wp_ajax_xyz_ips_execute_shortcode","xyz_ips_execute_shortcode");
function xyz_ips_execute_shortcode() {
    global $wpdb;

    $response = array(
        'status' => 0,
        'message' => 'Unexpected error',
        'data' => array()
    );
	

    try {
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'xyz_ips_execute_shortcode')) {
            $response['status'] = 0;
            $response['message'] = 'Invalid request. Please try again.';
        } else {

            $id =intval($_POST['id']);


            $shortcodeDetails = $wpdb->get_results($wpdb->prepare( "SELECT * FROM {$wpdb->prefix}xyz_ips_short_code WHERE id = %d AND insertionMethod = %d",$id, 3 ));
			if(!empty($shortcodeDetails))
			{
				$shortcode =$shortcodeDetails[0];
				$response['status'] = 1;
				$response['message'] = 'Success';
				$content_to_eval =xyz_ips_prepare_content_to_eval($shortcode->content);
				try {
				
					eval($content_to_eval);
					$response['message'] = 'You have successfully executed the code in background';
						} catch (Throwable $e) { 
							
							$response['status'] = 0;
							$response['message'] = 'Error: ' . $e->getMessage();
						}




			}
			else{
				$response['message'] ='Invalid snippet id';
			}
        }
    } catch (Exception $e) {
		
        $response['status'] = 0;
        $response['message'] = 'Error: ' . $e->getMessage();
    }

   

	
	wp_send_json($response);
    wp_die();
}
function xyz_ips_ajax_backlink() {

	check_ajax_referer('xyz-ips-blink','security');
    if(current_user_can('administrator')){
        global $wpdb;
        if(isset($_POST)){
            if(intval($_POST['enable'])==1){
                update_option('xyz_credit_link','ips');
                echo 1;
            }
            if(intval($_POST['enable'])==-1){
                update_option('xyz_ips_credit_dismiss','dis');
                echo -1;
            }
        }
    }die;
}

?>