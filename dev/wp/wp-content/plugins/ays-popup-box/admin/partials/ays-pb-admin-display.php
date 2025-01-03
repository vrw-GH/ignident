<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://ays-pro.com/
 * @since      1.0.0
 *
 * @package    Ays_Pb
 * @subpackage Ays_Pb/admin/partials
 */
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
$id = isset($_GET['popupbox']) ? absint( intval($_GET['popupbox']) ) : null;
$popup_max_id = Ays_Pb_Data::get_max_id();

if ($action == 'duplicate') {
    $this->popupbox_obj->duplicate_popupbox($id);
}

if ($action == 'unpublish' || $action == 'publish') {
    $this->popupbox_obj->publish_unpublish_popupbox($id, $action);
}

$plus_icon_svg = "<span><img src='" . AYS_PB_ADMIN_URL . "/images/icons/plus-icon.svg'></span>";
$youtube_icon_svg = "<span><img src='" . AYS_PB_ADMIN_URL . "/images/icons/youtube-video-icon.svg'></span>";

?>

<div class="wrap ays-pb-list-table">
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
            echo sprintf( '<a href="?page=%s&action=%s" class="page-title-action button-primary ays-pb-add-new-button-new-design"> %s ' . __( "Add New", "ays-popup-box" ) . '</a>', esc_attr( $_REQUEST['page'] ), 'add', $plus_icon_svg );
        ?>
    </div>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder">
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <?php
                        $this->popupbox_obj->views();
                    ?>
                    <form method="post">
                        <?php
                            $this->popupbox_obj->prepare_items();
                            $search = __("Search", "ays-popup-box");
                            $this->popupbox_obj->search_box($search, "ays-popup-box");
                            $this->popupbox_obj->display();
                        ?>
                    </form>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>
    <div class="ays-pb-add-new-button-box">
        <?php
            echo sprintf( '<a href="?page=%s&action=%s" class="page-title-action button-primary ays-pb-add-new-button-new-design"> %s '. __( "Add New", "ays-popup-box" ) .'</a>', esc_attr( $_REQUEST['page'] ), 'add', $plus_icon_svg );
        ?>
    </div>
    <?php if ($popup_max_id <= 3): ?>
        <div class="ays-pb-create-pb-video-box">
            <div class="ays-pb-create-pb-title">
                <h4><?php echo __( "Create Your First Popup in Under One Minute", "ays-popup-box" ); ?></h4>
            </div>
            <div class="ays-pb-create-pb-youtube-video">
                <iframe width="560" height="315" src="https://www.youtube.com/embed/_VEAGGzKe_g" loading="lazy" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
            <div class="ays_pb_small_hint_text_video">
                <?php echo __( 'Please note that this video will disappear once you created 4 popups.', "ays-popup-box" ); ?>
            </div>
            <div class="ays-pb-create-pb-youtube-video-button-box">
                <?php echo sprintf( '<a href="?page=%s&action=%s" class="ays-pb-add-new-button-video ays-pb-add-new-button-new-design"> %s '. __( "Add New", "ays-popup-box" ) .'</a>', esc_attr( $_REQUEST['page'] ), 'add', $plus_icon_svg );?>
            </div>
        </div>
    <?php else: ?>
        <div class="ays-pb-create-pb-video-box">
            <div class="ays-pb-create-pb-youtube-video">
                <?php echo $youtube_icon_svg; ?>
                <a href="https://www.youtube.com/watch?v=_VEAGGzKe_g" target="_blank" title="YouTube video player" ><?php echo __("How to create a popup in one minute?", "ays-popup-box"); ?></a>
            </div>
        </div>
    <?php endif ?>
</div>