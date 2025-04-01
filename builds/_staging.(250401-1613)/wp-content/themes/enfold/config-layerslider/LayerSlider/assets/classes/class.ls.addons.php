<?php

class LS_Addons {

	private function __construct() {}


	public static function init() {
		self::loadAddons( LS_ROOT_PATH.'/addons/' );
	}


	public static function loadAddons( $path ) {
		$addonDirs = glob( $path.'/*', GLOB_ONLYDIR);

		foreach( $addonDirs as $addonDir ) {
			self::loadAddon( $addonDir );
		}
	}


	public static function loadAddon( $addonDir ) {

		// Addon info.json file
		$addonInfoFile = $addonDir.'/info.json';

		// Check if addon info file exists
		if( ! file_exists( $addonInfoFile ) ) {
			error_log( 'LayerSlider: Addon info.json file not found in '.$addonDir );
			return false;
		}

		// Get addon info
		$addonInfo = json_decode( file_get_contents( $addonInfoFile ), true );

		// Check if addon info is valid
		if( empty( $addonInfo ) || empty( $addonInfo['name'] ) || empty( $addonInfo['version'] ) || empty( $addonInfo['requires'] ) ) {
			error_log( 'LayerSlider: Invalid addon info.json file in '.$addonDir );
			return false;
		}

		// Check if addon is compatible with the current version of LayerSlider
		if( version_compare( $addonInfo['requires'], LS_PLUGIN_VERSION, '>' ) ) {
			error_log( 'LayerSlider: Addon '.$addonInfo['name'].' requires at least LayerSlider '.$addonInfo['requires'].'. Current version is '.LS_PLUGIN_VERSION.'.');
			return false;
		}

		// Load addon index.php file
		if( file_exists( $addonDir.'/index.php' ) ) {
			require_once $addonDir.'/index.php';
		}
	}

}

LS_Addons::init();