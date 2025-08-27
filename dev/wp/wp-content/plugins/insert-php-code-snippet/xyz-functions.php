<?php
if ( ! defined( 'ABSPATH' ) )
	 exit;
	
if(!function_exists('xyz_ips_plugin_get_version'))
{
	function xyz_ips_plugin_get_version() 
	{
		if ( ! function_exists( 'get_plugins' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$plugin_folder = get_plugins( '/' . plugin_basename( dirname( XYZ_INSERT_PHP_PLUGIN_FILE ) ) );
		return $plugin_folder['insert-php-code-snippet.php']['Version'];
	}
}
if(!function_exists('xyz_ips_run_upgrade_routines'))
{
function xyz_ips_run_upgrade_routines() {
	global $wpdb;
	if (is_multisite()) {
		$blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		foreach ($blog_ids as $blog_id) {
			switch_to_blog($blog_id);
			xyz_ips_install();
			//wp_cache_flush();
			restore_current_blog();
		}
	} else {
		xyz_ips_install();
		//wp_cache_flush();
	}
}
}
if(!function_exists('xyz_ips_prepare_content_to_eval'))
{

    function xyz_ips_prepare_content_to_eval($content_to_eval){
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


return $content_to_eval='?>'.$content_to_eval;
}

}
if(!function_exists('xyz_trim_deep'))
{

	function xyz_trim_deep($value) {
		if ( is_array($value) ) {
			$value = array_map('xyz_trim_deep', $value);
		} elseif ( is_object($value) ) {
			$vars = get_object_vars( $value );
			foreach ($vars as $key=>$data) {
				$value->{$key} = xyz_trim_deep( $data );
			}
		} else {
			$value = trim($value);
		}

		return $value;
	}

}


if(!function_exists('xyz_ips_links')){
function xyz_ips_links($links, $file) {
	$base = plugin_basename(XYZ_INSERT_PHP_PLUGIN_FILE);
	if ($file == $base) {

		$links[] = '<a href="https://xyzscripts.com/support/" class="xyz_ips_support" title="Support"></a>';
		$links[] = '<a href="https://twitter.com/xyzscripts" class="xyz_ips_twitt" title="Follow us on Twitter"></a>';
		$links[] = '<a href="https://www.facebook.com/xyzscripts" class="xyz_ips_fbook" title="Like us on Facebook"></a>';
		$links[] = '<a href="https://www.instagram.com/xyz_scripts/" class="xyz_ips_insta" title="Follow us on Instagram"></a>';
		$links[] = '<a href="https://www.linkedin.com/company/xyzscripts" class="xyz_ips_linkedin" title="Follow us on LinkedIn"></a>';
	}
	return $links;
}
}
add_filter( 'plugin_row_meta','xyz_ips_links',10,2);

if(!function_exists('xyz_ips_page_exists_by_slug'))
{
function xyz_ips_page_exists_by_slug( $post_slug ) {
    $args_posts = array(
        'post_type'      => 'page',
        'post_status'    => 'any',
        'name'           => $post_slug,
        'posts_per_page' => 1,
    );
    $loop_posts = new WP_Query( $args_posts );
    if ( ! $loop_posts->have_posts() ) {
        return false;
    } else {
        $loop_posts->the_post();
        return $loop_posts->post->ID;
    }
}
}
if(!function_exists('xyz_ips_get_link_by_slug'))
{

function xyz_ips_get_link_by_slug($slug, $type = 'page'){
  $post = get_page_by_path($slug, OBJECT, $type);
  return get_permalink($post->ID);
}
}

function xyz_ips_preview_page( $content ) {
    if ( strcmp( 'xyz-ics-preview-page', get_post_field( 'post_name' ) ) === 0 ) {

			$xyz_ips_snippetId="";
			if(isset($_GET['snippetId']))
			$xyz_ips_snippetId=intval($_GET['snippetId']);

			if($xyz_ips_snippetId!="" && is_numeric($xyz_ips_snippetId))
			{
				global $wpdb;
				
					$snippetDetails = $wpdb->get_results($wpdb->prepare( 'SELECT * FROM '.$wpdb->prefix.'xyz_ips_short_code WHERE id=%d LIMIT 0,1',$xyz_ips_snippetId )) ;
					if(!empty($snippetDetails)) {
					$snippetDetails = $snippetDetails[0];

	      	$content = do_shortcode( '[xyz-ips snippet="'.esc_html($snippetDetails->title).'"]' );
				}
			}
    }

    return $content;
}

add_filter( 'the_content', 'xyz_ips_preview_page' );

?>
