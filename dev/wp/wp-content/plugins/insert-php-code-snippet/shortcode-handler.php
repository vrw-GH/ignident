<?php
if ( ! defined( 'ABSPATH' ) )
    exit;
global $wpdb;
add_shortcode('xyz-ips','xyz_ips_display_content');
include_once  'admin/constants.php';


/* customization starts: Execute on demand and run now */

$table_name = $wpdb->prefix . 'xyz_ips_short_code';
$snippets = $wpdb->get_results($wpdb->prepare( "SELECT * FROM {$table_name} WHERE insertionMethod = %d AND status = %d",1, 1 ));
foreach ($snippets as $snippet) {
	
	switch ($snippet->insertionLocation) {
		

		case XYZ_IPS_INSERTION_LOCATION['ADMIN_RUN_ON_HEADER']:
			if (is_admin()) {
				add_action('admin_head', function() use ($snippet) {
					echo xyz_execute_ips_snippet($snippet);
				}, 10);
			}
			
			break;	
			case XYZ_IPS_INSERTION_LOCATION['ADMIN_RUN_ON_FOOTER']:
				if (is_admin()) {
				add_action('admin_footer', function() use ($snippet) {
					echo xyz_execute_ips_snippet($snippet);
				}, 10);
			}
			
			break;	

			
		
			case XYZ_IPS_INSERTION_LOCATION['FRONTEND_RUN_ON_HEADER']:
				if (!is_admin()) {
				add_action('wp_head', function() use ($snippet) {
				echo xyz_execute_ips_snippet($snippet);
				}, 10);
			}
			
			break;	
		
			case XYZ_IPS_INSERTION_LOCATION['FRONTEND_RUN_ON_FOOTER']:
				if (!is_admin()) {
			
					
				add_action('wp_footer', function() use ($snippet) {
					echo xyz_execute_ips_snippet($snippet);
					
				}, 10);
			}
			
			break;	
		
			  
			
				
				

	}
}





function xyz_execute_ips_snippet($sippetdetails)
{


	if($sippetdetails->status==1){
		/*  if(is_numeric(ini_get('output_buffering'))){
			  $tmp=ob_get_contents();
			  if(strlen($tmp)>0)
				ob_clean();*/
			  ob_start();
			  $content_to_eval=$sippetdetails->content;

/***** to handle old codes : start *****/

if(get_option('xyz_ips_auto_insert')==1){
$content_to_eval =xyz_ips_prepare_content_to_eval($content_to_eval);
}

/***** to handle old codes : end *****/
else{
if(substr(trim($content_to_eval), 0,5)=='<?php')
$content_to_eval='?>'.$content_to_eval;
}


$exception_occur=0;
$exception_msg="";

try {


eval($content_to_eval);
} catch (Throwable $e) { // For PHP 7 and later
$exception_occur = 1;
$exception_msg = $e->getMessage();
}
catch (Exception $e) { // For PHP 5
$exception_occur = 1;
$exception_msg = $e->getMessage();
}



			  if($exception_occur==1) {

					  //	global $post;
							//  $post_slug = $post->post_name;

						  //if($post_slug!="xyz-ics-preview-page" && !is_customize_preview())
			{

							  if(get_option('xyz_ips_exception_email')!="0" && get_option('xyz_ips_exception_email')!="" && get_option('xyz_ips_auto_exception')==1)
			  {
									  $email=get_option('xyz_ips_exception_email');
									  $headers= "Content-type: text/html";
									  $subject="Exception Report";
								  $message="Hi,<br>An exception occurred while running one of the snippet.The snippet name is ".$snippet_name;
								  $message.=".<br>Exception details are given below : <br>";
									  $message.=$exception_msg;
									  wp_mail($email, $subject, $message,$headers);
								  }
							  }
						  }

			  $xyz_em_content = ob_get_contents();
			 // ob_clean();
			  ob_end_clean();
			   return $xyz_em_content;
		/*  }
		  else{
			  eval($sippetdetails->content);
		  }*/
	  }
	  else{
		  return '';
	  }


}
/* customization ends */
function xyz_ips_display_content($xyz_snippet_name){
    global $wpdb;
    $xyz_ips_exec_in_editor = get_option('xyz_ips_exec_in_editor');
    if ( $xyz_ips_exec_in_editor ) {
        // Page Builder checks (Elementor, WPBakery, Divi, Beaver Builder)
        if ( wp_doing_ajax() ) {
            $builder_actions = ['elementor_preview', 'wpb_pb_preview', 'et_pb_preview'];
            // Check for Elementor, WPBakery, Divi actions
            if ( isset( $_REQUEST['action'] ) && in_array( $_REQUEST['action'], $builder_actions, true ) ) {
                // Allow shortcode execution in page builder previews
            }
        // Beaver Builder detection using URL parameters
        if ( isset( $_REQUEST['fl_builder'] ) && isset( $_REQUEST['fl_builder'] ) ) {
            // Allow execution for Beaver Builder preview
            }
        }
        // Classic Editor or Gutenberg Editor check (editing posts)
        if ( is_admin() && isset( $_GET['post'] ) && 'edit' === $_GET['action'] ) {
            // Allow shortcode execution in Classic Editor or Gutenberg (when editing posts)
        }
    } elseif ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
        return ''; // Do not execute shortcode in other admin areas or REST API requests
    }
    if(is_array($xyz_snippet_name)&& isset($xyz_snippet_name['snippet'])){
        $snippet_name = $xyz_snippet_name['snippet'];
        $query = $wpdb->get_results($wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."xyz_ips_short_code WHERE title=%s" ,$snippet_name));
        if(!empty($query))//if(count($query)>0)
        {
            foreach ($query as $sippetdetails){
                if($sippetdetails->status==1){
                  /*  if(is_numeric(ini_get('output_buffering'))){
                        $tmp=ob_get_contents();
                        if(strlen($tmp)>0)
                          ob_clean();*/
                        ob_start();
                        $content_to_eval=$sippetdetails->content;

/***** to handle old codes : start *****/

if(get_option('xyz_ips_auto_insert')==1){
    $xyz_ips_content_start='<?php';
    $new_line="\r\n";
    $xyz_ips_content_end='?>';

    if (stripos($content_to_eval, '<?php') !== false)
        $tag_start_position=stripos($content_to_eval,'<?php');
    else
        $tag_start_position="-1";

    if (stripos($content_to_eval, '?>') !== false)
        $tag_end_position=stripos($content_to_eval,'?>');
    else
        $tag_end_position="-1";

    if(stripos($content_to_eval, '<?php') === false && stripos($content_to_eval, '?>') === false)
    {
        $content_to_eval=$xyz_ips_content_start.$new_line.$content_to_eval;
    }
    else if(stripos($content_to_eval, '<?php') !== false)
    {
        if($tag_start_position>=0 && $tag_end_position>=0 && $tag_start_position>$tag_end_position)
        {
            $content_to_eval=$xyz_ips_content_start.$new_line.$content_to_eval;
        }
    }
    else if(stripos($content_to_eval, '<?php') === false)
    {
        if (stripos($content_to_eval, '?>') !== false)
        {
            $content_to_eval=$xyz_ips_content_start.$new_line.$content_to_eval;
        }
    }
    $content_to_eval='?>'.$content_to_eval;
}

/***** to handle old codes : end *****/
else{
    if(substr(trim($content_to_eval), 0,5)=='<?php')
        $content_to_eval='?>'.$content_to_eval;
}


$exception_occur=0;
$exception_msg="";

      try {


          eval($content_to_eval);
      } catch (Throwable $e) { // For PHP 7 and later
          $exception_occur = 1;
          $exception_msg = $e->getMessage();
    }
      catch (Exception $e) { // For PHP 5
        $exception_occur = 1;
        $exception_msg = $e->getMessage();
    }



                        if($exception_occur==1) {

        						//	global $post;
        	         				 //  $post_slug = $post->post_name;

        							//if($post_slug!="xyz-ics-preview-page" && !is_customize_preview())
                      {

        								if(get_option('xyz_ips_exception_email')!="0" && get_option('xyz_ips_exception_email')!="" && get_option('xyz_ips_auto_exception')==1)
                        {
            									$email=get_option('xyz_ips_exception_email');
            									$headers= "Content-type: text/html";
            									$subject="Exception Report";
        									$message="Hi,<br>An exception occurred while running one of the snippet.The snippet name is ".$snippet_name;
        									$message.=".<br>Exception details are given below : <br>";
            									$message.=$exception_msg;
            									wp_mail($email, $subject, $message,$headers);
            								}
            							}
            						}

                        $xyz_em_content = ob_get_contents();
                       // ob_clean();
                        ob_end_clean();
                         return $xyz_em_content;
                  /*  }
                    else{
                        eval($sippetdetails->content);
                    }*/
                }
                else{
                    return '';
                }
                break;
            }
        }
        else{
            return '';
        }
    }
}
add_filter('widget_text', 'do_shortcode'); // to run shortcodes in text widgets
