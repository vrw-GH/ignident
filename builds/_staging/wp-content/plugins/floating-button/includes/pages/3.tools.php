<?php
/**
 * Page Name: Import/Export
 *
 */

use FloatingButton\Dashboard\ImporterExporter;
use FloatingButton\WOW_Plugin;

defined( 'ABSPATH' ) || exit;

?>

    <div class="w_block w_has-border">

                <div class="inside">
                    <h3><span><?php esc_attr_e( 'Export Settings', 'floating-button' ); ?></span></h3>
                    <div class="inside">
                        <p><?php
	                        /* translators: %s: plugin name */
							printf( esc_html__( 'Export the  settings for %s as a .json file. This allows you to easily import the configuration into another site.', 'floating-button' ), '<b>' . esc_attr( WOW_Plugin::info('name') ) . '</b>' ); ?></p>
						<?php ImporterExporter::form_export(); ?>
                    </div>
                </div>
                <hr>

                <div class="inside">
                    <h3><span><?php esc_attr_e( 'Import Settings', 'floating-button' ); ?></span></h3>
                    <div class="inside">
                        <p><?php
	                        /* translators: %s: plugin name */
							printf( esc_html__( 'Import the %s settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.', 'floating-button' ), '<b>' . esc_attr( WOW_Plugin::info('name') ) . '</b>    ' ); ?></p>
						<?php ImporterExporter::form_import(); ?>
                    </div>
                </div>



    </div>

<?php
