<?php
namespace PDFPro\Helper;

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'PDFPro\Helper\PDFP_DefaultArgs' ) ) {
    class PDFP_DefaultArgs{

    public static function parseArgs($data){
        $default = self::get();
        $data = wp_parse_args( $data, $default );
        $data['options'] = wp_parse_args( $data['options'], $default['options'] );
        $data['infos'] = wp_parse_args( $data['infos'], $default['infos'] );
        $data['template'] = wp_parse_args( $data['template'], $default['template'] );

		return $data;
    }

    public static function get(){
        $options = [];

        $infos = [
            'protect' => false,
            'alert' => false,
            'adobeOptions' => []
        ];

        $template = array(
            'classes' => '',
            'title' => '',
            'titleFontSize' => '16px',
            'file' => '',
            'height' => '1122px',
            'width' => '100%',
            'onlyPDF' => false,
            'print' => false,
            'defaultBrowser' => false,
            'adobeEmbedder' => false,
            'showName' => false,
            'raw' => false,
            'downloadButton' => false,
            'downloadButtonText' => 'Download File',
            'fullscreenButton' => false,
            'fullscreenButtonText' => 'View Fullscreen',
            'newWindow' => false,
            'protect' => false,
            'thumbMenu' => false,
            'sidebarOpen' => false,
            'initialPage' => 0,
            'download' => 'false',
            'lastVersion' => false,
            'hrscroll' => false,
            'zoomLevel' => 'auto',
            'align' => '',
            'CSS' => '',
            'uniqueId' => '',
            'popupBtnText' => '',
            'embedMode' => false
        );

        $default = [
            'options' => $options,
            'infos' => $infos,
            'template' => $template
        ];
        
        return $default;
    }

}
}