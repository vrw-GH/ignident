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
    
            foreach (get_object_vars($value) as $key => $data) {
				$value->{$key} = xyz_trim_deep( $data );
			}
    
        } elseif (is_string($value)) {
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

if (!function_exists('xyz_ips_page_exists_by_slug')) {
    function xyz_ips_page_exists_by_slug($slug) {
        global $wpdb;
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} 
             WHERE post_name = %s 
             AND post_type = 'page' 
             LIMIT 1", 
            $slug
        ));
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

if(!function_exists('xyz_ips_get_insertion_location_label')){
function xyz_ips_get_insertion_location_label($value) {
    $map = array_flip(XYZ_IPS_INSERTION_LOCATION);
    if (!isset($map[$value])) {
        return '';
    }
    // Convert constant-style key to readable text
    return ucwords(strtolower(str_replace('_', ' ', $map[$value])));
}
}
if(!function_exists('xyz_ips_get_shortcode_usage_count')){
// Get shortcode usage count (admin-only)
function xyz_ips_get_shortcode_usage_count($snippet_title) {
    global $wpdb;
    $shortcode = '[xyz-ips snippet="' . esc_sql($snippet_title) . '"';
    $query = $wpdb->prepare(
        "SELECT COUNT(ID)
         FROM {$wpdb->posts}
         WHERE post_status = 'publish'
         AND post_content LIKE %s",
        '%' . $wpdb->esc_like($shortcode) . '%'
    );
    return (int) $wpdb->get_var($query);
}
}
if(!function_exists('xyz_ips_update_usage_for_post')){
// --- Tracking Logic ---
function xyz_ips_update_usage_for_post($post_id, $content, $post_type = 'post') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'xyz_ips_usage';
    if (strpos($content, '[xyz-ips') === false) {
        $wpdb->delete($table_name, ['post_id' => $post_id]);
        return;
    }
    $wpdb->delete($table_name, ['post_id' => $post_id]);
    preg_match_all('/\[xyz-ips([^\]]*)\]/', $content, $shortcodes);
    $titles = [];
    if (!empty($shortcodes[1])) {
        foreach ($shortcodes[1] as $attr_string) {
            $atts = shortcode_parse_atts($attr_string);
           // if (!empty($atts['snippet'])) 
            // PHP 8.3 Compliance Fix: Ensure $atts is an array before checking the 'snippet' key
            if (is_array($atts) && !empty($atts['snippet']))
            {
                $titles[] = $atts['snippet'];
            }
        }
    }
    $unique_snippets = array_unique($titles);
    foreach ($unique_snippets as $title) {
        $s_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}xyz_ips_short_code WHERE title = %s",
            $title
        ));
        if ($s_id) {
            $wpdb->insert($table_name, [
                'post_id'    => $post_id,
                'snippet_id' => $s_id,
                'post_type'  => $post_type
            ]);
        }
    }
}
}
?>
