<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://ays-pro.com/
 * @since      1.0.0
 *
 * @package    Popup_Box
 * @subpackage Popup_Box/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Popup_Box
 * @subpackage Popup_Box/public
 * @author     AYS Pro LLC <info@ays-pro.com>
 */
class Popup_Box_Popup_Category {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    protected $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    private $html_class_prefix = 'ays-pb-category-';
    private $html_name_prefix = 'ays-pb-';
    private $name_prefix = 'ays_pb_';
    private $unique_id;
    private $unique_id_in_class;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $plugin_name The name of the plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version){

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_shortcode('ays_pb_cat_title', array($this, 'ays_generate_pb_categories_title_method'));
        add_shortcode('ays_pb_cat_description', array($this, 'ays_generate_pb_categories_description_method'));
    }

    /*
    ==========================================
        Show pb category title | Start
    ==========================================
    */

    public function ays_generate_pb_categories_title_method( $attr ) {

        $id = (isset($attr['id']) && $attr['id'] != '') ? absint( sanitize_text_field($attr['id']) ) : null;

        if (is_null($id) || $id == 0 ) {
            $pb_category_title = "";
            return str_replace(array("\r\n", "\n", "\r"), "\n", $pb_category_title);
        }

        $unique_id = uniqid();
        $this->unique_id = $unique_id;
        $this->unique_id_in_class = $unique_id;

        $pb_category_title = $this->ays_generate_cat_title_html( $id );

        return str_replace(array("\r\n", "\n", "\r"), "\n", $pb_category_title);
    }

    public function ays_generate_cat_title_html( $id ) {

        $results = Ays_Pb_Data::get_category_by_id($id);

        $content_html = array();
        
        if( is_null( $results ) || empty( $results ) ){
            $content_html = "";
            return $content_html;
        }

        $category_title = (isset($results['title']) && $results['title'] != '') ? sanitize_text_field($results['title']) : "";

        if ( $category_title == "" ) {
            $content_html = "";
            return $content_html;
        }

        $content_html[] = "<span class='". $this->html_name_prefix ."category-title' id='". $this->html_name_prefix ."category-title-". $this->unique_id_in_class ."' data-id='". $this->unique_id ."'>";
            $content_html[] = $category_title;
        $content_html[] = "</span>";

        $content_html = implode( '' , $content_html);

        return $content_html;
    }

    /*
    ==========================================
        Show pb category title | End
    ==========================================
    */

    /*
    ==========================================
        Show pb category description | Start
    ==========================================
    */

    public function ays_generate_pb_categories_description_method( $attr ) {

        $id = (isset($attr['id']) && $attr['id'] != '') ? absint( sanitize_text_field($attr['id']) ) : null;

        if (is_null($id) || $id == 0 ) {
            $pb_category_description = "";
            return str_replace(array("\r\n", "\n", "\r"), "\n", $pb_category_description);
        }

        $unique_id = uniqid();
        $this->unique_id = $unique_id;
        $this->unique_id_in_class = $unique_id;

        $pb_category_description = $this->ays_generate_cat_description_html( $id );

        return str_replace(array("\r\n", "\n", "\r"), "\n", $pb_category_description);
    }

    public function ays_generate_cat_description_html( $id ) {

        $results = Ays_Pb_Data::get_category_by_id($id);

        $content_html = array();
        
        if( is_null( $results ) || empty( $results ) ){
            $content_html = "";
            return $content_html;
        }

        $category_description = (isset($results['description']) && $results['description'] != '') ? Ays_Pb_Public::ays_autoembed($results['description']) : "";

        if ( $category_description == "" ) {
            $content_html = "";
            return $content_html;
        }

        $content_html[] = "<div class='". $this->html_name_prefix ."category-description' id='". $this->html_name_prefix ."category-description-". $this->unique_id_in_class ."' data-id='". $this->unique_id ."'>";
            $content_html[] = $category_description;
        $content_html[] = "</div>";

        $content_html = implode( '' , $content_html);

        return $content_html;
    }

    /*
    ==========================================
        Show pb category description | End
    ==========================================
    */

}
