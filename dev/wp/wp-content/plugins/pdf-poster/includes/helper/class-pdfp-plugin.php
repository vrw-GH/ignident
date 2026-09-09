<?php
namespace PDFPro\Helper;

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'PDFPro\Helper\PDFP_Plugin' ) ) {
    class PDFP_Plugin{

    public static $version = PDFPRO_VER;
    public static $latestVersion = null;

    public static function dir(){
        return plugin_dir_url(__FILE__);
    }

    public static function path(){
        return plugin_dir_path(__FILE__);
    }

    public static function version(){
        return self::$version;
    }

   
}
}