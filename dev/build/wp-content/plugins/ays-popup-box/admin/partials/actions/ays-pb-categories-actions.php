<?php
$action = (isset($_GET['action'])) ? sanitize_text_field( $_GET['action'] ) : '';
$heading = '';
$loader_iamge = '';
$id = ( isset( $_GET['popup_category'] ) ) ? absint( intval( $_GET['popup_category'] ) ) : null;
$popup_category = array(
    'id'            => '',
    'title'         => '',
    'description'   => '',
    'published'     => ''
);
switch( $action ) {
    case 'add':
        $heading = __('Add new category', "ays-popup-box");
        $loader_iamge = "<span class='display_none'><img src=".AYS_PB_ADMIN_URL."/images/loaders/loading.gif></span>";
        break;
    case 'edit':
        $heading = __('Edit category', "ays-popup-box");
        $loader_iamge = "<span class='display_none'><img src=".AYS_PB_ADMIN_URL."/images/loaders/loading.gif></span>";
        $popup_category = $this->popup_categories_obj->get_popup_category( $id );
        break;
}
if( isset( $_POST['ays_submit'] ) ) {
    $_POST['id'] = $id;
    $result = $this->popup_categories_obj->add_edit_popup_category();
}
if(isset($_POST['ays_apply'])){
    $_POST["id"] = $id;
    $_POST['ays_change_type'] = 'apply';
    $this->popup_categories_obj->add_edit_popup_category();
}

// General Settings | options
$gen_options = ($this->settings_obj->ays_get_setting('options') === false) ? array() : json_decode( stripcslashes($this->settings_obj->ays_get_setting('options') ), true);

// WP Editor height
$pb_wp_editor_height = (isset($gen_options['pb_wp_editor_height']) && $gen_options['pb_wp_editor_height'] != '') ? absint( sanitize_text_field($gen_options['pb_wp_editor_height']) ) : 150 ;

//Category title
$categoty_title = ( isset( $popup_category['title'] ) && $popup_category['title'] != '' ) ? stripslashes( $popup_category['title'] ) : '';

//Category description
$category_description = ( isset( $popup_category['description'] ) && $popup_category['description'] != '' ) ? stripslashes( $popup_category['description'] ) : '';

//Published Category
$published_category = ( isset($popup_category['published'] ) && $popup_category['published'] != '' ) ? stripslashes($popup_category['published'] ) : '1';

$next_pb_cat_id = "";
if ( isset( $id ) && !is_null( $id ) ) {
    $next_pb_cat_data = $this->get_next_or_prev_row_by_id( $id, "next", "ays_pb_categories" );
    $next_pb_cat_id = (isset( $next_pb_cat_data['id'] ) && $next_pb_cat_data['id'] != "") ? absint( $next_pb_cat_data['id'] ) : null;
}
$prev_pb_cat_id = "";
if ( isset( $id ) && !is_null( $id ) ) {
    $prev_pb_cat_data = $this->get_next_or_prev_row_by_id( $id, "prev", "ays_pb_categories" );
    $prev_pb_cat_id = (isset( $prev_pb_cat_data['id'] ) && $prev_pb_cat_data['id'] != "") ? absint( $prev_pb_cat_data['id'] ) : null;
}

?>
<div class="wrap">
    <div class="container-fluid">
        <div class="ays-pb-heading-box">
            <div class="ays-pb-wordpress-user-manual-box">
                <a href="https://ays-pro.com/wordpress-popup-box-plugin-user-manual" target="_blank" style="text-decoration: none;font-size: 13px;">
                    <img src="<?php echo AYS_PB_ADMIN_URL . '/images/icons/text-file.svg' ?>">
                    <span style="text-decoration: underline;"><?php echo __("View Documentation", "ays-popup-box"); ?></span>
                </a>
            </div>
        </div>
        <h1><?php echo $heading; ?></h1>
        <hr/>
        <form class="ays-pb-category-form" id="ays-pb-category-form" method="post">
            <input type="hidden" class="pb_wp_editor_height" value="<?php echo $pb_wp_editor_height; ?>">
            <div class="form-group row">
                <div class="col-sm-2">
                    <label for='ays-title'>
                        <?php echo __('Category name', "ays-popup-box"); ?>
                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Define the category name.',"ays-popup-box")?>">
                            <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/info-circle.svg"?>">
                        </a>
                    </label>
                </div>
                <div class="col-sm-10">
                    <input class='ays-text-input' id='ays-title' name='ays_title' required type='text' value='<?php echo esc_attr($categoty_title); ?>'>
                </div>
            </div>

            <hr/>
            <div class='ays-field'>
                <label for='ays-description'>
                    <?php echo __('Description', "ays-popup-box"); ?>
                    <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Write category description if necessary.',"ays-popup-box")?>">
                        <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/info-circle.svg"?>">
                    </a>
                </label>
                <?php
                $content = $category_description;
                $editor_id = 'ays-description';
                $settings = array('editor_height'=>$pb_wp_editor_height,'textarea_name'=>'ays_description','editor_class'=>'ays-textarea');
                wp_editor($content, $editor_id, $settings);
                ?>
            </div>
            <hr>
            <div class="col-sm-12 ays-pro-features-v2-main-box">
                <div class="ays-pro-features-v2-small-buttons-box">
                    <a href="https://ays-pro.com/wordpress/popup-box" target="_blank" class="ays-pro-features-v2-upgrade-button">
                        <div class="ays-pro-features-v2-upgrade-icon" style="background-image: url('<?php echo esc_attr(AYS_PB_ADMIN_URL); ?>/images/icons/pro-features-icons/Locked_24x24.svg');" data-img-src="<?php echo esc_attr(AYS_PB_ADMIN_URL); ?>/images/icons/pro-features-icons/Locked_24x24.svg"></div>
                        <div class="ays-pro-features-v2-upgrade-text">
                            <?php echo __("Upgrade" , "ays-popup-box"); ?>
                        </div>
                    </a>
                </div>
                <div class="form-group row ays_toggle_parent" style="padding:10px;">
                    <div class="col-sm-3">
                        <label for="ays_show_random_posts_category">
                            <?php echo __('Show random popup by category', "ays-popup-box")?>
                            <a class="ays_help ays-pb-help-pro" data-toggle="tooltip" title="<?php echo __('If this option is enabled a random popup will be displayed from the selected category based on the chosen post.',"ays-popup-box"); ?>">
                                <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/info-circle.svg"?>">
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-1">
                        <input type="checkbox" name="ays_show_random_posts_category" class="onoffswitch-checkbox ays-enable-timer1 ays_toggle_checkbox" id="ays_show_random_posts_category" checked>
                    </div>
                    <div class="col-sm-8 ays_toggle_target ays_divider_left">
                        <div class="form-group row">                        
                            <div class="col-sm-12">
                                <label class="ays-pb-label-style" for="<?php echo $this->plugin_name; ?>-show_all_yes"><?php echo __("All pages", "ays-popup-box"); ?>
                                    <input type="radio" id="<?php echo $this->plugin_name; ?>-show_all_yes" checked> 
                                </label>
                                <label class="ays-pb-label-style" for="<?php echo $this->plugin_name; ?>-show_all_except"><?php echo __("Except", "ays-popup-box"); ?>
                                    <input type="radio" id="<?php echo $this->plugin_name; ?>-show_all_except"  class="" name="<?php echo $this->plugin_name; ?>[show_all]" value="except">
                                </label>
                                <label class="ays-pb-label-style" for="<?php echo $this->plugin_name; ?>-show_all_selected"><?php echo __("Include", "ays-popup-box"); ?>
                                    <input type="radio" id="<?php echo $this->plugin_name; ?>-show_all_selected"  class="" name="<?php echo $this->plugin_name; ?>[show_all]" value="selected">
                                </label>
                                <a class="ays_help ays-pb-help-pro" style="font-size:15px;" data-toggle="tooltip" data-html="true"
                                    title="<?php
                                        echo __('Choose the method of calculation.',"ays-popup-box") .
                                        "<ul style='list-style-type: circle;padding-left: 20px;'>".
                                            "<li>". __('All pages - The popup will display on all pages.',"ays-popup-box") ."</li>".
                                            "<li>". __('Except - Choose the post/page and post/page types excluding the popup.',"ays-popup-box") ."</li>".
                                            "<li>". __('Include - Choose the post/page and post/page types including the popup.',"ays-popup-box") ."</li>".
                                        "</ul>";
                                    ?>">
                                    <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/info-circle.svg"?>">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="form-group row">
                <div class="col-sm-2">
                    <label>
                        <?php echo __('Category status', "ays-popup-box"); ?>
                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Select whether or not to display the new category in the settings.',"ays-popup-box")?>">
                            <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/info-circle.svg"?>">
                        </a>
                    </label>
                </div>

                <div class="col-sm-3">
                    <div class="form-check form-check-inline">
                        <input type="radio" id="ays-publish" name="ays_publish" value="1" <?php echo ( $published_category == '' ) ? "checked" : ""; ?> <?php echo ( $published_category == '1') ? 'checked' : ''; ?> />
                        <label class="form-check-label" for="ays-publish"> <?php echo __('Published', "ays-popup-box"); ?> </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input type="radio" id="ays-unpublish" name="ays_publish" value="0" <?php echo ( $published_category  == '0' ) ? 'checked' : ''; ?> />
                        <label class="form-check-label" for="ays-unpublish"> <?php echo __('Unpublished', "ays-popup-box"); ?> </label>
                    </div>
                </div>
            </div>

            <hr/>
            <div class="form-group row ays-pb-button-box">
                <div class="col-sm-10 ays-pb-button-first-row" style="padding: 0;">
                <?php
                    wp_nonce_field('popup_category_action', 'popup_category_action');
                    $other_attributes = array( 'id' => 'ays-cat-button' );
                    $other_attributes_save = array(
                        'id' => 'ays-cat-button-apply',
                        'title' => 'Ctrl + s',
                        'data-toggle' => 'tooltip',
                        'data-delay'=> '{"show":"300"}'
                    );
                    submit_button( __( 'Save and close', "ays-popup-box" ), 'primary', 'ays_submit', false, $other_attributes );
                    submit_button( __( 'Save', "ays-popup-box"), '', 'ays_apply', false, $other_attributes_save);
                    echo $loader_iamge;
                ?>
                </div>
                <div class="col-sm-2 ays-pb-button-second-row">
                <?php
                    if ( $prev_pb_cat_id != "" && !is_null( $prev_pb_cat_id ) ) {
                        $other_attributes = array(
                            'id' => 'ays-pb-category-prev-button',
                            'data-message' => __( 'Are you sure you want to go to the previous popup category page?', "ays-popup-box"),
                            'href' => sprintf( '?page=%s&action=%s&popup_category=%d', esc_attr( $_REQUEST['page'] ), 'edit', absint( $prev_pb_cat_id ) )
                        );
                        submit_button(__('Previous Popup Category', "ays-popup-box"), 'button button-primary ays_default_btn ays-pb-next-prev-button-class ays-button', 'ays_pb_category_prev_button', false, $other_attributes);
                    }
                ?>
                <?php
                    if ( $next_pb_cat_id != "" && !is_null( $next_pb_cat_id ) ) {
                        $other_attributes = array(
                            'id' => 'ays-pb-category-next-button',
                            'data-message' => __( 'Are you sure you want to go to the next popup category page?', "ays-popup-box"),
                            'href' => sprintf( '?page=%s&action=%s&popup_category=%d', esc_attr( $_REQUEST['page'] ), 'edit', absint( $next_pb_cat_id ) )
                        );
                        submit_button(__('Next Popup Category', "ays-popup-box"), 'button button-primary ays_default_btn ays-pb-next-prev-button-class ays-button', 'ays_pb_category_next_button', false, $other_attributes);
                    }
                ?>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    jQuery(document).ready(function($){
        $('[data-toggle="tooltip"]').tooltip({
            template: '<div class="tooltip ays-pb-custom-class-tooltip" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
        });
    });

    var aysUnsavedChanges = false;
    jQuery(document).on('change input', '#ays-pb-category-form input, #ays-pb-category-form select, #ays-pb-category-form textarea', function() {
        aysUnsavedChanges = true;
    });

    jQuery(window).on('beforeunload', function(event) {
        var saveButtons = jQuery(document).find('.button#ays-cat-button-apply, .button#ays-cat-button')
        var savingButtonsClicked = saveButtons.filter('.ays-save-button-clicked').length > 0;

        if (aysUnsavedChanges && !savingButtonsClicked) {
            event.preventDefault();
            event.returnValue = true;
        }
    });
</script>