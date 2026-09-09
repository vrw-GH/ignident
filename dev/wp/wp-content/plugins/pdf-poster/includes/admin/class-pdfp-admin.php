<?php

namespace PDFPro\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

if (!class_exists('PDFPro\Admin\PDFP_AdminLoader')) {
	class PDFP_AdminLoader {
		public function __construct() {
			add_action('admin_enqueue_scripts', [$this, 'adminEnqueueScripts']);
			add_action('admin_menu', [$this, 'adminMenu'], 15);
			
		}

		public function adminEnqueueScripts($hook) {
			if (strpos($hook, 'pdf-poster') !== false) {
				$asset_file = file_exists(PDFPRO_PATH . 'build/dashboard.asset.php') 
					? include(PDFPRO_PATH . 'build/dashboard.asset.php') 
					: ['dependencies' => ['react', 'react-dom', 'wp-components', 'wp-api-fetch', 'wp-data'], 'version' => PDFPRO_VER];

				wp_enqueue_style('pdfp-dashboard-style', PDFPRO_PLUGIN_DIR . 'build/dashboard.css', [], $asset_file['version']);
				
				if (file_exists(PDFPRO_PATH . 'build/dashboard.css')) {
					wp_enqueue_style('pdfp-dashboard-extra-style', PDFPRO_PLUGIN_DIR . 'build/style-dashboard.css', [], $asset_file['version']);
				}

				wp_enqueue_script('pdfp-dashboard-script', PDFPRO_PLUGIN_DIR . 'build/dashboard.js', array_merge($asset_file['dependencies'], ['react-dom']), $asset_file['version'], true);
				
				wp_localize_script('pdfp-dashboard-script', 'pdfpDashboard', [
					'dir' => PDFPRO_PLUGIN_DIR,
				]);
			}
		}

		public function adminMenu() {
			add_submenu_page(
				'edit.php?post_type=pdfposter',
				__('Demo and Help', 'pdf-poster'),
				'<span style="color: #f18500;">' . __('Demo and Help', 'pdf-poster') . '</span>',
				'edit_others_posts',
				'pdf-poster',
				[$this, 'dashboardPage'],
				15
			);
		}

		public function dashboardPage() { 
			?>
			<div id='pdfpAdminDashboard' data-info='<?php echo esc_attr(wp_json_encode([
														'version' => PDFPRO_VER,
														'isPremium' => false,
														'hasPro' => false,
														'adminUrl' => admin_url(),
														'licenseActiveNonce' => wp_create_nonce('bPlLicenseActivation')
													])); ?>'></div>
			<?php
		}	

		public function upgradePage() { 
			?>
			<div id='pdfpAdminUpgrade' data-info='<?php echo esc_attr(wp_json_encode([
														'version' => PDFPRO_VER,
														'isPremium' => false,
														'hasPro' => false
													])); ?>'>Coming soon...</div>
			<?php
		}

	}
    new PDFP_AdminLoader();
}
