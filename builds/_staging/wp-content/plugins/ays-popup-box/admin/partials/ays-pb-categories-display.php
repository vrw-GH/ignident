<?php
/**
 * Created by PhpStorm.
 * User: biggie18
 * Date: 6/15/18
 * Time: 3:34 PM
 */

    $plus_icon_svg = "<span><img src='" . AYS_PB_ADMIN_URL . "/images/icons/plus-icon.svg'></span>";

?>

<div class="wrap ays-pb-categories-list-table">
    <div class="ays-pb-heading-box">
        <div class="ays-pb-wordpress-user-manual-box">
            <a href="https://ays-pro.com/wordpress-popup-box-plugin-user-manual" target="_blank">
                <img src="<?php echo AYS_PB_ADMIN_URL . '/images/icons/text-file.svg' ?>">
                <span><?php echo __("View Documentation", "ays-popup-box"); ?></span>
            </a>
        </div>
    </div>
    <h1 class="wp-heading-inline">
        <?php
            echo esc_html(get_admin_page_title());
        ?>
    </h1>
    <div class="ays-pb-add-new-button-box">
        <?php
            echo sprintf('<a href="?page=%s&action=%s" class="page-title-action button-primary ays-pb-add-new-button-new-design"> %s ' . __('Add New', "ays-popup-box") . '</a>', esc_attr($_REQUEST['page']), 'add', $plus_icon_svg);
        ?>
    </div>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder">
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <?php
                        $this->popup_categories_obj->views();
                    ?>
                    <form method="post">
                        <?php
                            $this->popup_categories_obj->prepare_items();
                            $this->popup_categories_obj->search_box('Search', "ays-popup-box");
                            $this->popup_categories_obj->display();
                        ?>
                    </form>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>
    <div class="ays-pb-add-new-button-box">
        <?php
            echo sprintf('<a href="?page=%s&action=%s" class="page-title-action button-primary ays-pb-add-new-button-new-design"> %s ' . __('Add New', "ays-popup-box") . '</a>', esc_attr($_REQUEST['page']), 'add', $plus_icon_svg);
        ?>
    </div>
</div>
