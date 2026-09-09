<?php
/**
 * Import/Export/... Tab
 * =====================
 *
 * @since 4.8.2
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

global $avia_config, $avia_pages, $avia_elements;



$warning  = '<br />';
$warning .= '<strong>';
$warning .= __( 'We strongly recommend to export your current settings now to have a fallback.', 'avia_framework' );
$warning .= '</strong>';


if( is_child_theme() )
{
	$avia_elements[] = array(
				'slug'	=> 'upload',
				'name' 	=> __( 'Import Settings From Your Parent Theme', 'avia_framework' ),
				'desc' 	=> __( "We have detected that you are using a Child Theme. That's Great!. If you want to, we can import the settings of your Parent theme to your Child theme. Please be aware that this will overwrite your current child theme settings.", 'avia_framework' ) . $warning,
				'id' 	=> 'parent_setting_import',
				'type' 	=> 'parent_setting_import'
			);
}

$desc  = __( 'Click the button to generate and download a config file which contains the theme settings. You can use the config file to import the theme settings on another sever.', 'avia_framework' );
$desc .= '<br />';
$desc .= __( 'For security, API keys and secret keys (e.g. Google Maps, reCAPTCHA, Cloudflare Turnstile, Mailchimp) and the Google Analytics tracking code are excluded from the export - re-enter them after importing.', 'avia_framework' );
$desc .= '<br /><strong>';
$desc .=	__( 'Restricted to admins only', 'avia_framework' );
$desc .= '</strong>';

$avia_elements[] = array(
			'slug'	=> 'upload',
			'name' 	=> __( 'Export Theme Settings File', 'avia_framework' ),
			'desc' 	=> $desc,
			'id' 	=> 'theme_settings_export',
			'type' 	=> 'theme_settings_export'
		);


$avia_elements[] = array(
			'slug'	=> 'upload',
			'name' 	=> __( 'Select Theme Options To Import', 'avia_framework' ),
			'desc' 	=> __( 'Check if you do not want to import all settings from an exported theme settings file.', 'avia_framework' ),
			'docu'		=> [
							'url'	=> 'https://kriesi.at/documentation/enfold/backup-theme-settings',
							'title'	=> __( 'Read our documentation for more information how to customize import', 'avia_framework' )
						],
			'id' 	=> 'upload_filter_checkbox',
			'type' 	=> 'checkbox',
			'std'	=> ''
		);

$avia_elements[] = array(
			'slug'		=> 'upload',
			'name'		=> __( 'Keep Quick CSS Content', 'avia_framework' ),
			'desc'		=> __( 'Keep your General Styling > Quick CSS when importing. It is preserved automatically unless you import the tab that contains it — in that case, check this to keep it.', 'avia_framework' ),
			'id'		=> 'upload_keep_quick_css',
			'type'		=> 'checkbox',
			'std'		=> '',
			'required'	=> array( 'upload_filter_checkbox', 'upload_filter_checkbox' ),
		);

$avia_elements[] = array(
			'slug'		=> 'upload',
			'name'		=> __( 'Select Theme Options Tabs For Import', 'avia_framework' ),
			'desc'		=> __( 'Leave empty to import everything, or pick specific tabs to import. Only the selected tabs are changed.', 'avia_framework' ),
			'id'		=> 'upload_filter_tabs',
			'type'		=> 'select',
			'multiple'	=> '6',
			'std'		=> '',
			'no_first'	=> true,
			'subtype'	=> 'option_page_tabs',
			'required'	=> array( 'upload_filter_checkbox', 'upload_filter_checkbox' )
		);

$avia_elements[] = array(
			'slug'				=> 'upload',
			'name'				=> __( 'Import Theme Settings File', 'avia_framework' ),
			'desc'				=> __( "Upload a theme configuration file here. Note that the configuration file settings will overwrite your current configuration and you can't restore the current configuration afterwards.", 'avia_framework' ) . $warning,
			'id'				=> 'config_file_upload',
			'type'				=> 'file_upload',
			'capability_context'=> 'theme_settings',
			'std'				=> '',
			'title'				=> __( 'Upload Theme Settings File', 'avia_framework' ),
			'button'			=> __( 'Insert Settings File', 'avia_framework' ),
			'trigger'			=> 'av_config_file_insert',
			// 'fopen_check' 	=> 'true',
			'file_extension'	=> 'txt',
			'file_type'			=> 'text/plain'
		);

if( ! current_theme_supports( 'avia_disable_reset_options' ) )
{
	$avia_elements[] = array(
				'slug'		=> 'upload',
				'name'		=> __( 'Theme Reset All Options Button', 'avia_framework' ),
				'desc'		=> __( 'Hide the reset button to prevent theme options from being reset. Activate it again before you can reset.', 'avia_framework' ),
				'id'		=> 'reset_options_button',
				'type'		=> 'select',
				'std'		=> '',
				'no_first'	=> true,
				'subtype'	=> array(
								__( 'Activate reset all options button', 'avia_framework' )			=> '',
								__( 'Block and hide reset all options button', 'avia_framework' )	=> 'block_hide',
							)
			);

	$avia_elements[] = array(
				'slug'			=> 'upload',
				'type'			=> 'visual_group_start',
				'id'			=> 'avia_upload_reset_button_group_start',
				'nodescription'	=> true,
				'required'		=> array( 'reset_options_button', '' ),
			);

	$avia_elements[] = array(
				'slug'	=> 'upload',
				'name' 	=> __( 'Select Theme Options To Reset', 'avia_framework' ),
				'desc' 	=> __( 'Check if you do not want to reset all options.', 'avia_framework' ),
				'docu'		=> [
							'url'	=> 'https://kriesi.at/documentation/enfold/backup-theme-settings',
							'title'	=> __( 'Read our documentation for more information how to customize resetting theme options', 'avia_framework' )
						],
				'id' 	=> 'reset_filter_checkbox',
				'type' 	=> 'checkbox',
				'std'	=> ''
			);

	$avia_elements[] = array(
				'slug'		=> 'upload',
				'name'		=> __( 'Keep Quick CSS Content', 'avia_framework' ),
				'desc'		=> __( 'Keep your General Styling > Quick CSS when resetting. It is preserved automatically unless you reset the tab that contains it — in that case, check this to keep it.', 'avia_framework' ),
				'id'		=> 'reset_keep_quick_css',
				'type'		=> 'checkbox',
				'std'		=> '',
				'required'	=> array( 'reset_filter_checkbox', 'reset_filter_checkbox' ),
			);

	$avia_elements[] = array(
				'slug'		=> 'upload',
				'name'		=> __( 'Select Theme Options Tabs To Reset', 'avia_framework' ),
				'desc'		=> __( 'Leave empty to reset everything, or pick specific tabs to reset. Only the selected tabs return to factory defaults.', 'avia_framework' ),
				'id'		=> 'reset_filter_tabs',
				'type'		=> 'select',
				'multiple'	=> '6',
				'std'		=> '',
				'no_first'	=> true,
				'subtype'	=> 'option_page_tabs',
				'required'	=> array( 'reset_filter_checkbox', 'reset_filter_checkbox' ),
			);

	$avia_elements[] = array(
				'slug'		=> 'upload',
				'name'		=> __( 'Reset Selected Options', 'avia_framework' ),
				'desc'		=> __( 'Reset the selected options to their factory defaults. This overwrites your current settings and cannot be undone.', 'avia_framework' ) . $warning,
				'id'		=> 'reset_selected_button',
				'type'		=> 'reset_selected_button',
				'required'	=> array( 'reset_filter_checkbox', 'reset_filter_checkbox' ),
			);

	$avia_elements[] = array(
				'slug'			=> 'upload',
				'type'			=> 'visual_group_end',
				'id'			=> 'avia_upload_reset_button_group_end',
				'nodescription'	=> true
		);

}

$avia_elements[] = array(
			'slug'	=> 'upload',
			'name' 	=> __( 'Export Layout Builder Templates', 'avia_framework' ),
			'desc' 	=> __( 'Download your saved Layout Builder templates as a file you can import on another site.', 'avia_framework' ),
			'id' 	=> 'alb_templates_export',
			'type' 	=> 'alb_templates_export'
		);

$avia_elements[] = array(
			'slug'				=> 'upload',
			'name'				=> __( 'Import Layout Builder Templates File', 'avia_framework' ),
			'desc'				=> __( 'Upload a Layout Builder templates file. Templates are added to your existing ones; templates with the same name are not overwritten.', 'avia_framework' ),
			'id'				=> 'alb_templates_upload',
			'type'				=> 'file_upload',
			'capability_context'=> 'alb_templates',
			'std'				=> '',
			'title'				=> __( 'Upload Layout Builder Templates File', 'avia_framework' ),
			'button'			=> __( 'Insert Layout Builder Templates File', 'avia_framework' ),
			'trigger'			=> 'av_alb_templates_file_insert',
			// 'fopen_check' 	=> 'true',
			'file_extension'	=> 'txt',
			'file_type'			=> 'text/plain',
		);


$desc  = __( 'You can upload additional SVG iconset zip packages (added with 7.0). Also colored svg can be used. For more detailed information check our', 'avia_framework' );
$desc .= ' <a href="https://kriesi.at/documentation/enfold/svg-icon-sets/" target="_blank" rel="noopener noreferrer">' . __( 'documentation', 'avia_framework' ) . '.</a><br/><br/>';
$desc .= __( 'You can also upload additional Iconfont packages generated with', 'avia_framework' ) . " <a href='http://fontello.com/' target='_blank' rel='noopener noreferrer'>Fontello</a>  ";
$desc .= __( 'or use monocolored icon sets from', 'avia_framework' ) . " <a href='http://www.flaticon.com/' target='_blank' rel='noopener noreferrer'>Flaticon</a>.<br/><br/>";
$desc .= __( 'Those icons can then be used in the &quot;Layout Builder&quot; elements.', 'avia_framework' ) . '<br/><br/>';
$desc .= __( 'Make sure to delete any iconfonts that you are not using, to keep the loading time low for your visitors.', 'avia_framework' ) . ' ';
$desc .= __( 'The &quot;Default&quot; icons cannot be deleted.', 'avia_framework' ) . ' ';
$desc .= __( 'If you deactivate the iconfont &quot;Entypo Fontello&quot;, all iconfont icons and social icons you have used will be replaced with svg icons on the fly during pageload and the font will not be loaded any longer. Please check the layout. We recommend to replace the icons with svg icons before doing this.', 'avia_framework' );


$avia_elements[] = array(
			'slug'				=> 'upload',
			'name'				=> __( 'SVG Iconset and Iconfont Manager', 'avia_framework' ),
			'desc'				=> $desc,
			'id'				=> 'iconfont_upload',
			'type'				=> 'file_upload',
			'std'				=> '',
			'title'				=> __( 'Upload/Select SVG or Fontello Font Zip', 'avia_framework' ),
			'button'			=> __( 'Insert Zip File', 'avia_framework' ),
			'trigger'			=> 'av_fontello_zip_insert',
			// 'fopen_check' 	=> 'true',
			'file_extension'	=> 'zip', //used to check if user can upload this file type
			'file_type'			=> 'application/octet-stream, application/zip', //used for javascript gallery to display file types
		);

$desc = sprintf( __( 'You can upload your custom type font zip files. Intended for %s.', 'avia_framework' ), sprintf( '<a href="https://fonts.google.com/" target="_blank" rel="noopener noreferrer">%s</a>', __( 'Google Webkit Fonts', 'avia_framework' ) ) ) . '<br/><br/>';
$desc .= sprintf( __( 'Variable fonts can be uploaded, but currently only %s are supported by default.', 'avia_framework' ), sprintf( '<a href="https://fonts.google.com/knowledge/glossary/instance/" target="_blank" rel="noopener noreferrer">%s</a>', __( 'named instances', 'avia_framework' ) ) ) . ' ';
$desc .= sprintf( __( 'Please read our %s to learn how to create the zip files and a fallback scenario.', 'avia_framework' ), sprintf( '<a href="https://kriesi.at/documentation/enfold/typography/#using-variable-fonts" target="_blank" rel="noopener noreferrer">%s</a>', __( 'documentation', 'avia_framework' ) ) ) . '<br/><br/>';
$desc .= __( 'Make sure to delete any fonts that you are not using, to keep the loading time for your visitors low', 'avia_framework' );

$avia_elements[] = array(
			'slug'				=> 'upload',
			'name'				=> __( 'Custom Type Fonts Manager', 'avia_framework' ),
			'desc'				=> $desc,
			'id'				=> 'typefont_upload',
			'type'				=> 'file_upload',
			'std'				=> '',
			'title'				=> __( 'Upload/Select Font Zip File', 'avia_framework' ),
			'button'			=> __( 'Insert Zip File', 'avia_framework' ),
			'trigger'			=> 'av_typefont_zip_insert',
			// 'fopen_check' 	=> 'true',
			'file_extension'	=> 'zip', //used to check if user can upload this file type
			'file_type'			=> 'application/octet-stream, application/zip', //used for javascript gallery to display file types
		);

