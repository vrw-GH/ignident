<?php
/*
 * Page Name: Add New
 */

use FloatingButton\Dashboard\Field;
use FloatingButton\Dashboard\Settings;
use FloatingButton\WOW_Plugin;

defined( 'ABSPATH' ) || exit;

$default = Field::getDefault();

?>

    <form action="" id="wowp-settings" class="wowp-settings" method="post">

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="post-body-content">

                    <div id="titlediv">

                        <div id="titlewrap">
                            <label>
                                <span class="screen-reader-text"><?php esc_html_e( 'Enter title here', 'floating-button' ); ?></span>
                                <?php Field::text( 'title' ); ?>
                            </label>
                        </div>

                    </div>
                </div>

                <!--      Sidebar with the setting-->
                <div id="postbox-container-1" class="postbox-container">
                    <?php require_once plugin_dir_path(__FILE__) . 'sidebar.php';?>
                </div>

                <div id="postoptions">
                    <div id="postbox-container-2" class="postbox-container wowp-settings-wrapper">
						<?php Settings::init(); ?>
                    </div>
                </div>
            </div>

        </div>

        <input type="hidden" name="tool_id" value="<?php echo absint( $default['id'] ); ?>" id="tool_id"/>
        <input type="hidden" name="param[time]" value="<?php echo esc_attr( time() ); ?>"/>
		<?php wp_nonce_field( WOW_Plugin::PREFIX . '_nonce', WOW_Plugin::PREFIX . '_settings' ); ?>

    </form>


<?php
