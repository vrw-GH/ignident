<?php
/**
 * PDF Poster Autoloader
 * 
 * Maps PDFPro namespace to the new includes structure.
 * Example: PDFPro\Database\Init -> includes/database/class-pdfp-init.php
 */

namespace PDFPro;

if (!defined('ABSPATH')) exit;

class Autoloader {

    /**
     * Register the autoloader
     */
    public static function register() {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    /**
     * Autoload classes
     */
    public static function autoload($class) {
        // Only autoload classes from our namespace
        if (0 !== strpos($class, 'PDFPro\\')) {
            return;
        }

        // Remove the namespace prefix
        $relative_class = substr($class, 7);

        // Map the RegisterBlock class to class-pdfp-blocks.php
        if ( 'Base\PDFP_RegisterBlock' === $relative_class ) {
            $relative_class = 'Base\PDFP_Blocks';
        }

        // Explode into parts (e.g. Database\Init -> ['Database', 'Init'])
        $parts = explode('\\', $relative_class);
        $class_name = array_pop($parts);
        $subfolder = !empty($parts) ? strtolower(implode('/', $parts)) : '';

        // Prepare filename (lowercase, class-pdfp- prefix)
        $file_name = 'class-' . str_replace('_', '-', strtolower($class_name)) . '.php';

        // Construct full path
        $base_dir = plugin_dir_path(__FILE__);
        
        if ($subfolder) {
            $full_path = $base_dir . $subfolder . '/' . $file_name;
        } else {
            $full_path = $base_dir . $file_name;
        }

        // Fallback for non-class files if any (though we aim for all classes)
        if (!file_exists($full_path)) {
            // Try without class-pdfp- prefix just in case
            if ($subfolder) {
                $full_path = $base_dir . $subfolder . '/' . $class_name . '.php';
            } else {
                $full_path = $base_dir . $class_name . '.php';
            }
        }

        if (file_exists($full_path)) {
            require_once $full_path;
        }
    }
}

Autoloader::register();
