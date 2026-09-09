<?php
/*
 * Plugin Name:         PDF Poster – Display PDF Files with Custom Viewer
 * Plugin URI:          https://bplugins.com/products/pdf-poster/
 * Description:         You can easily embed and display PDF files in your WordPress website using this plugin.
 * Version:             2.5.6
 * Author:              bPlugins
 * Author URI:          https://profiles.wordpress.org/abuhayat
 * License:             GPLv2 or later
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:         pdf-poster
 * Domain Path:         /languages
 * Requires at least:     5.0.3
 * Requires PHP:          7.1
 */

if (!defined('ABSPATH')) { exit; }

if (function_exists('pdfp_fs')) {
  pdfp_fs()->set_basename(true, __FILE__);
}
else {
  /*Some Set-up*/
  define('PDFPRO_PLUGIN_DIR', plugin_dir_url(__FILE__));
  define('PDFPRO_PATH', plugin_dir_path(__FILE__));
  define('PDFPRO_VER',  '2.5.6');
  define('PDFPRO_IMPORT_VER', '1.0.0');

  // Load Autoloader
  if (file_exists(PDFPRO_PATH . 'includes/class-pdfp-autoloader.php')) {
    require_once PDFPRO_PATH . 'includes/class-pdfp-autoloader.php';
  }

  if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
    require_once(dirname(__FILE__) . '/vendor/autoload.php');
  }

  if ( ! class_exists( 'CSF' ) ) {
    if ( file_exists( PDFPRO_PATH . 'vendor/codestar-framework/codestar-framework.php' ) ) {
      require_once PDFPRO_PATH . 'vendor/codestar-framework/codestar-framework.php';
    }
  }

  if (!function_exists('pdfp_fs')) { 
    function pdfp_fs()  {
      global $pdfp_fs;
      
      if (!isset($pdfp_fs)) { 
        $pdfp_fs = fs_dynamic_init(array(
          'id' => '14261',
          'slug' => 'pdf-poster',
          'premium_slug' => 'pdf-poster-pro',
          'type' => 'plugin',
          'public_key' => 'pk_6e833032174d131283193892a44a2',
          'is_premium' => false, 
          'menu' => array(
            'slug' => 'edit.php?post_type=pdfposter',
            'first-path' => 'edit.php?post_type=pdfposter&page=pdf-poster#/welcome',
            'support' => false,
            'contact' => false, 
          ),
        ));
      }

      return $pdfp_fs;
    }

    // Init Freemius.
    pdfp_fs();
    // Signal that SDK was initiated.
    do_action('pdfp_fs_loaded');
  }

  if (file_exists(PDFPRO_PATH . 'includes/database/upgrade.php')) {
    require_once PDFPRO_PATH . 'includes/database/upgrade.php';
  }

  if (class_exists('PDFPro\PDFP_Init')) {
    \PDFPro\PDFP_Init::register_services();
  }

  function pdfp_get_p_option($array, $key = array(), $default = null) {
    if (is_array($array) && array_key_exists($key, $array)) {
      return $array[$key];
    }
    return $default;
  }

  add_action('media_buttons', 'pdfp_my_media_button', 3);
  function pdfp_my_media_button() {
    echo wp_kses_post('<a href="#" id="insert-pdf" class="button pdfp_insert_pdf_btn">
        <img src="' . PDFPRO_PLUGIN_DIR . '/assets/images/icn.png' . '" alt="" width="20" height="20" style="position:relative; top:-1px">
        ' . esc_html__('Add PDF', 'pdf-poster') . '</a>');
  }

  add_action('admin_init', 'pdfp_admin_init');
  function pdfp_admin_init() {
    // Import logic removed (premium only)
  }

  add_action('wp_head', function () {
    $option = get_option('fpdf_option');
?>
<style>
  <?php echo esc_html($option['custom_css'] ?? '')?>
</style>
<?php
  });
}



