<?php

namespace PDFPro;

if (!defined('ABSPATH'))
    exit;

use stdClass;

if (!class_exists('PDFPro\PDFP_Init')) {
    class PDFP_Init
    {

        public static function get_services()
        {
            return [
                Database\PDFP_Init::class,
                Model\PDFP_AjaxCall::class,
                Base\PDFP_EnqueueAssets::class, 
                Admin\PDFP_ProModal::class,
                Base\PDFP_Shortcodes::class,
                Base\PDFP_PDFPoster::class,
                Base\PDFP_RegisterBlock::class,
                Admin\PDFP_Settings::class,
                Admin\PDFP_MetaBox::class,
                Rest\PDFP_AjaxCall::class,
                Rest\PDFP_GetMeta::class,
                Admin\PDFP_Chatbot::class,
                Admin\PDFP_SidebarCards::class,
            ];
        }

        public static function register_post_type()
        {
            self::instantiate(Base\PDFP_PDFPoster::class);
        }

        public static function register_services()
        {
            foreach (self::get_services() as $class) {
                $services = self::instantiate($class);
                if (method_exists($services, 'register')) {
                    $services->register();
                }
            }
        }

        private static function instantiate($class)
        {
            if (class_exists($class)) {
                return new $class();
            }
            return new stdClass();
        }
    }
}
