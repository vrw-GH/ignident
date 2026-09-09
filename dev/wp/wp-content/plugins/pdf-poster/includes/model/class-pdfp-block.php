<?php
namespace PDFPro\Model;

if (!defined('ABSPATH')) exit;


if ( ! class_exists( 'PDFPro\Model\PDFP_Block' ) ) {
    class PDFP_Block{

    public static function get($id){
        $content_post = get_post($id);
        if(isset($content_post->post_content)){
            return $content_post->post_content;
        }
        return false;
    }

    public static function getBlock($id){
        $blocks = parse_blocks(self::get($id));
        $out = [];
        
        foreach ($blocks as $block) {
            if($block['blockName'] === 'pdfp/pdfposter'){
                $out[] = $block['attrs'];
            }else {
                $out[] = $block;
            }
        }

        return $out;
    }
}
}