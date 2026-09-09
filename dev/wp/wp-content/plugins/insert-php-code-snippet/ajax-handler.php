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

add_action('wp_ajax_xyz_ips_sync_usage', function() {
    global $wpdb;
    $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
    $batch_size = 100; // adjust as needed
    $table_name = $wpdb->prefix . 'xyz_ips_usage';
    // Get posts starting from $offset
    $posts = $wpdb->get_results($wpdb->prepare(
        "SELECT ID, post_content, post_type FROM {$wpdb->posts} 
         WHERE post_status = 'publish' 
         ORDER BY ID ASC 
         LIMIT %d, %d", 
         $offset, $batch_size
    ));
    if (empty($posts)) {
        // Finished
        update_option('xyz_ips_sync_needed', 0); // reset
        wp_send_json_success(['status' => 'complete']);
    }
    foreach ($posts as $post) {
        // Run your usage update logic
        xyz_ips_update_usage_for_post($post->ID, $post->post_content, $post->post_type);
    }
    $new_offset = $offset + count($posts);
    // Store the new offset
    update_option('xyz_ips_sync_needed', $new_offset);
    wp_send_json_success([
        'status' => 'processing',
        'new_offset' => $new_offset
    ]);
});
?>