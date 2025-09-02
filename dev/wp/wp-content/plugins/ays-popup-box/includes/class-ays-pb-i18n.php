<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://ays-pro.com/
 * @since      1.0.0
 *
 * @package    Ays_Pb
 * @subpackage Ays_Pb/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Ays_Pb
 * @subpackage Ays_Pb/includes
 * @author     AYS Pro LLC <info@ays-pro.com>
 */
class Ays_Pb_i18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		if ( version_compare( get_bloginfo( 'version' ), '6.7', '>=' ) ) {
            $plugin = 'ays-popup-box';
            
            if( is_admin() ){
                $locale = get_user_locale();
            } else {
                $locale = get_locale();
            }
            
            if ( is_textdomain_loaded( $plugin ) ) {
                unload_textdomain( $plugin );
            }
            $mofile = sprintf( '%s-%s.mo', $plugin, $locale );
            // check the plugin language folder.
            $domain_path = path_join( WP_PLUGIN_DIR, "{$plugin}/languages" );
            $loaded = load_textdomain( $plugin, path_join( $domain_path, $mofile ) );

            if ( ! $loaded ) { //else, check the installation language path first.
                $domain_path = path_join( WP_LANG_DIR, 'plugins' );
                load_textdomain( $plugin, path_join( $domain_path, $mofile ) );
            }
        } else {
            load_plugin_textdomain(
				'ays-popup-box',
				false,
				dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
			);

        }
	}

}
