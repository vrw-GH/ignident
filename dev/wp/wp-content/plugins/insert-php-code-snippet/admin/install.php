<?php
if ( ! defined( 'ABSPATH' ) )
    exit;


function xyz_ips_network_install($networkwide) {
    global $wpdb;
    if (function_exists('is_multisite') && is_multisite()) {
        // check if it is a network activation - if so, run the activation function for each blog id
        if ($networkwide) {
            $old_blog = $wpdb->blogid;
            // Get all blog ids
            $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
            foreach ($blogids as $blog_id) {
                switch_to_blog($blog_id);
                xyz_ips_install();
            }
            switch_to_blog($old_blog);
            return;
        }
    }
    xyz_ips_install();
}


function xyz_ips_install(){
    global $wpdb;

    $plugin_name = 'xyz-wp-insert-code-snippet/xyz-wp-insert-code-snippet.php';
    if ( is_plugin_active( $plugin_name ) ) {
    
        wp_die(
            sprintf(
                /* translators: 1: Plugin name, 2: Deactivate target, 3: Link to plugins page */
                esc_html__( 'The plugin %1$s cannot be activated unless the %2$s is deactivated. Back to %3$s.', 'insert-php-code-snippet' ),
                '<strong>Insert PHP Code Snippet</strong>',
                '<strong>premium version</strong>',
                sprintf(
                    '<a href="%s">%s</a>',
                    esc_url( admin_url( 'plugins.php' ) ),
                    esc_html__( 'Plugin Installation', 'insert-php-code-snippet' )
                )
            )
        );
    }
if ( version_compare( PHP_VERSION, '7.0.0', '<' ) ) {
    wp_die(
        sprintf(
            'This plugin requires PHP version 7.0 or higher. You are using PHP %s. <a href="%s">Go back to Plugins page</a>.',
            PHP_VERSION,
            esc_url( admin_url( 'plugins.php' ) )
        )
    );
}
    if(get_option('xyz_ips_sort_order')==''){
        add_option('xyz_ips_sort_order','desc');
    }

    if(get_option('xyz_ips_sort_field_name')==''){
        add_option('xyz_ips_sort_field_name','id');
    }

    if(get_option('xyz_credit_link') == ""){
        add_option("xyz_credit_link",0);
    }

    if(get_option('xyz_ips_credit_dismiss') == ""){
        add_option("xyz_ips_credit_dismiss",0);
    }

    if(get_option('xyz_ips_premium_version_ads')==""){
        add_option('xyz_ips_premium_version_ads',1);
    }

    if(get_option('xyz_ips_auto_insert')==""){
        add_option('xyz_ips_auto_insert',1);
    }

    if(get_option('xyz_ips_auto_exception')==""){
        add_option('xyz_ips_auto_exception',1);
    }

    $xyz_ips_installed_date = get_option('xyz_ips_installed_date');
    if ($xyz_ips_installed_date=="") {
        $xyz_ips_installed_date = time();
        update_option('xyz_ips_installed_date', $xyz_ips_installed_date);
    }
    add_option('xyz_ips_limit',20);
    add_option('xyz_ips_exception_email',"0");
    add_option('xyz_ips_exec_in_editor','0');
    $charset_collate = $wpdb->get_charset_collate();
    $queryInsertPhp = "CREATE TABLE IF NOT EXISTS  ".$wpdb->prefix."xyz_ips_short_code (
`id` int NOT NULL AUTO_INCREMENT,
`title` varchar(1000) NOT NULL,
        `description` TEXT NULL ,
`content` longtext  NOT NULL,
`short_code` varchar(2000) NOT NULL,
`status` int NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB ".$charset_collate." AUTO_INCREMENT=1";
    $wpdb->query($queryInsertPhp);


	$tblcolums = $wpdb->get_col("SHOW COLUMNS FROM  ".$wpdb->prefix."xyz_ips_short_code");
    if(!(in_array("insertionMethod", $tblcolums)))
	$wpdb->query("ALTER TABLE ".$wpdb->prefix."xyz_ips_short_code ADD insertionMethod int NOT NULL default 2");
    if(!(in_array("insertionLocation", $tblcolums)))
	$wpdb->query("ALTER TABLE ".$wpdb->prefix."xyz_ips_short_code ADD insertionLocation int NOT NULL default 0");
	if(!(in_array("insertionLocationType", $tblcolums)))
	$wpdb->query("ALTER TABLE ".$wpdb->prefix."xyz_ips_short_code ADD insertionLocationType int NOT NULL default 0");
    if(!(in_array("description", $tblcolums)))
	$wpdb->query("ALTER TABLE ".$wpdb->prefix."xyz_ips_short_code ADD description TEXT NULL ");
      $table_name      = $wpdb->prefix . 'xyz_ips_usage';
      $charset_collate = $wpdb->get_charset_collate();
      $sql = "CREATE TABLE {$table_name} (
          post_id BIGINT(20) UNSIGNED NOT NULL,
          snippet_id BIGINT(20) UNSIGNED NOT NULL,
          post_type VARCHAR(20) NOT NULL,
          PRIMARY KEY  (post_id, snippet_id),
          KEY post_id (post_id),
          KEY snippet_id (snippet_id)
      ) {$charset_collate};";
      require_once ABSPATH . 'wp-admin/includes/upgrade.php';
      dbDelta($sql);
      // Set sync flag only if not already set
      if (get_option('xyz_ips_sync_needed') === false) {
          add_option('xyz_ips_sync_needed', 1);
      }
    add_option('xyz_ips_show_snippet_usage',1);//default enable 
    //preview page
  	$slug = 'xyz-ics-preview-page';
  	$title = 'Snippet Preview';
     // Use an option to store the ID
      $preview_page_id = get_option('xyz_ips_preview_page_id');  
      // Verify the stored ID actually exists in the DB
      if (!$preview_page_id || get_post_status($preview_page_id) === false) {
          // Final safety check: see if a page with this slug exists but isn't in our options
          $existing_id = xyz_ips_page_exists_by_slug($slug);
          if (!$existing_id) {
              $post_id = wp_insert_post(array(
                  'post_author'  => get_current_user_id() ?: 1, // Avoid author 0
  											'post_name'         =>   $slug,
                  'post_title'   => 'Snippet Preview',
                  'post_content' => '',
  											'post_status'       =>   'draft',
  											'post_type'         =>   'page'
              ));
              update_option('xyz_ips_preview_page_id', $post_id);
          } else {
              // Page exists but we didn't have the ID saved; save it now.
              update_option('xyz_ips_preview_page_id', $existing_id);
          }
  	}
}
register_activation_hook( XYZ_INSERT_PHP_PLUGIN_FILE ,'xyz_ips_network_install');
?>
