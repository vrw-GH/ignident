<?php
$actions = $this->settings_obj;

if (isset($_REQUEST['ays_submit'])) {
    $actions->store_data($_REQUEST);
}

if (isset($_GET['ays_pb_tab'])) {
    $ays_pb_tab = sanitize_text_field($_GET['ays_pb_tab']);
} else {
    $ays_pb_tab = 'tab1';
}

if (isset($_GET['action']) && $_GET['action'] == 'update_duration') {
    $actions->update_duration_data();
}

$loader_iamge = "<span class='display_none ays_quiz_loader_box'><img src=". AYS_PB_ADMIN_URL ."/images/loaders/loading.gif></span>";
$db_data = $actions->get_db_data();

$options = ($actions->ays_get_setting('options') === false) ? array() : json_decode($actions->ays_get_setting('options'), true);

$ays_pb_sound = (isset($options['ays_pb_sound']) && $options['ays_pb_sound'] != '') ? esc_attr($options['ays_pb_sound']) : '';
$ays_pb_close_sound = (isset($options['ays_pb_close_sound']) && $options['ays_pb_close_sound'] != '') ? esc_attr($options['ays_pb_close_sound']) : '';

// Animation CSS File
$options['pb_exclude_animation_css'] = isset($options['pb_exclude_animation_css']) ? esc_attr( $options['pb_exclude_animation_css'] ) : 'off';
$pb_exclude_animation_css = (isset($options['pb_exclude_animation_css']) && esc_attr( $options['pb_exclude_animation_css'] ) == "on") ? true : false;

global $wpdb;

//opening src from wp posts
$sound_src = "SELECT guid FROM {$wpdb->posts} WHERE guid='$ays_pb_sound'";
$sound_src_result = $wpdb->get_results($sound_src, "ARRAY_A");

//closing src from wp posts
$sound_closing_src = "SELECT guid FROM {$wpdb->posts} WHERE guid='$ays_pb_close_sound'";
$closing_sound_src_result = $wpdb->get_results($sound_closing_src, "ARRAY_A");

//delete ays pb close sound
if($closing_sound_src_result == null){
    $ays_pb_close_sound = '';
}

//delete ays pb opening sound
if($sound_src_result == null){
    $ays_pb_sound = ''; 
}


// WP Editor height
$pb_wp_editor_height = (isset($options['pb_wp_editor_height']) && $options['pb_wp_editor_height'] != '') ? absint( sanitize_text_field($options['pb_wp_editor_height']) ) : 150 ;

//Popups title length
$popup_title_length = (isset($options['popup_title_length']) && intval($options['popup_title_length']) != 0) ? absint(intval($options['popup_title_length'])) : 5;

//Categories title length
$categories_title_length = (isset($options['categories_title_length']) && intval($options['categories_title_length']) != 0) ? absint(intval($options['categories_title_length'])) : 5;


?>
<div class="wrap" style="position:relative;">
    <div class="container-fluid">
        <form method="post" id="ays-pb-settings-form">
            <input type="hidden" name="ays_pb_tab" value="<?php echo esc_attr($ays_pb_tab); ?>">
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
            <?php
            if (isset($_REQUEST['status'])) {
                $actions->pb_settings_notices($_REQUEST['status']);
            }
            ?>
            <hr/>
            <div class="ays-settings-wrapper">
                <div>
                    <div class="nav-tab-wrapper" style="position:sticky; top:35px;">
                        <a href="#tab1" data-tab="tab1"
                           class="nav-tab <?php echo ($ays_pb_tab == 'tab1') ? 'nav-tab-active' : ''; ?>">
                            <?php echo __("General", "ays-popup-box"); ?>
                        </a>
                        <a href="#tab2" data-tab="tab2" class="nav-tab <?php echo ($ays_pb_tab == 'tab2') ? 'nav-tab-active' : ''; ?>">
                            <?php echo __("Integrations", "ays-popup-box");?>
                        </a>
                        <a href="#tab3" data-tab="tab3"
                           class="nav-tab <?php echo ($ays_pb_tab == 'tab3') ? 'nav-tab-active' : ''; ?>">
                            <?php echo __("Shortcodes", "ays-popup-box"); ?>
                        </a>
                        <a href="#tab4" data-tab="tab4" class="nav-tab <?php echo ($ays_pb_tab == 'tab4') ? 'nav-tab-active' : ''; ?>">
                            <?php echo __("Message variables", "ays-popup-box");?>
                        </a>
                    </div>
                </div>
                <div class="ays-pb-tabs-wrapper">
                    <div id="tab1" class="ays-pb-tab-content <?php echo ($ays_pb_tab == 'tab1') ? 'ays-pb-tab-content-active' : ''; ?>">
                        <p class="ays-pb-subtitle"><?php echo __('General Settings', "ays-popup-box") ?></p>
                        <hr/>
                        <div class="" style="padding:15px;">
                            <fieldset>
                                <legend>
                                    <strong style="font-size:30px;"><img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/question-circle.svg"?>"></strong>
                                    <h5><?php echo __('Default popup parameters',"ays-popup-box")?></h5>
                                </legend>
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label for="ays_pb_wp_editor_height">
                                            <?php echo __( "WP Editor height", "ays-popup-box" ); ?>
                                            <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Give the default height value to the WP Editor. It will apply to all WP Editors within the plugin on the dashboard.',"ays-popup-box"); ?>">
                                                <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/info-circle.svg"?>">
                                            </a>
                                        </label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="number" name="ays_pb_wp_editor_height" id="ays_pb_wp_editor_height" class="ays-text-input" value="<?php echo $pb_wp_editor_height; ?>">
                                    </div>
                                </div>
                            </fieldset>
                            <hr>
                            <fieldset>
                                <legend>
                                    <strong style="font-size:30px;"><img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/text.svg"?>"></strong>
                                    <h5><?php echo __('Excerpt words count in list tables',"ays-popup-box")?></h5>
                                </legend>
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label for="ays_popup_title_length">
                                            <?php echo __( "Popup list table", "ays-popup-box" ); ?>
                                            <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Determine the length of the Popups to be shown in the Popup List Table by putting your preferred count of words in the following field. (E.g., if you put 10,  you will see the first 10 words of each Popup Title on the Popups page of your dashboard).', "ays-popup-box"); ?>">
                                                    <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/info-circle.svg"?>">
                                            </a>
                                        </label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="number" name="ays_popup_title_length" id="ays_popup_title_length" class="ays-text-input" value="<?php echo $popup_title_length; ?>">
                                    </div>
                                </div> 

                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label for="ays_categories_title_length">
                                            <?php echo __( "Popup categories list table", "ays-popup-box" ); ?>
                                            <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Determine the length of the results to be shown in the Popup categories List Table by putting your preferred count of words in the following field. (For example: if you put 10,  you will see the first 10 words of each result in the Popup categories page of your dashboard).', "ays-popup-box"); ?>">
                                                <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/info-circle.svg"?>">
                                            </a>
                                        </label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="number" name="ays_categories_title_length" id="ays_categories_title_length" class="ays-text-input" value="<?php echo $categories_title_length; ?>">
                                    </div>
                                </div>
                            </fieldset> <!-- Excerpt words count in list tables -->
                            <hr>
                            <fieldset>
                                <legend>
                                    <strong style="font-size:30px;"><img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/music.svg"?>"></strong>
                                    <h5><?php echo __('Popup sound',"ays-popup-box")?></h5>
                                </legend>
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label for="">
                                            <span>
                                                <?php echo  __('Opening and closing sounds',"ays-popup-box") ?>
                                                <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Insert popup opening and closing sound by clicking on “Select sound”.', "ays-popup-box"); ?>">
                                                    <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/info-circle.svg"?>">
                                                </a>
                                            </span>
                                        </label>
                                    </div>
                                    <div class="col-sm-8">
                                        <div class="form-group row">
                                            <div class="col-sm-12">
                                                <label for="ays_pb_opening_sound">
                                                    <?php echo __( "Opening sound", "ays-popup-box" ); ?>
                                                </label>
                                                <div class="ays-bg-music-container">
                                                    <a class="add-pb-bg-music" href="javascript:void(0);"><?php echo __("Select sound", "ays-popup-box"); ?></a>
                                                    <audio controls src="<?php echo $ays_pb_sound; ?>" class="ays-bg-opening-music-audio"></audio>
                                                    <input type="hidden" name="ays_pb_sound" class="ays_pb_bg_music ays_pb_bg_music_opening_input" value="<?php echo $ays_pb_sound; ?>" id="ays_pb_opening_sound">
                                                    <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/times.svg"?>" class="ays_pb_sound_close_btn ays_pb_sound_opening_btn" style="<?php echo ($ays_pb_sound == '') ? 'display:none' : 'display:block'; ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <!-- close sound start -->
                                        <div class="form-group row">
                                            <div class="col-sm-12">
                                                <label for="ays_pb_closing_sound">
                                                    <?php echo __( "Closing sound", "ays-popup-box" ); ?>
                                                </label>
                                                <div class="ays-bg-music-container">
                                                    <a class="add-pb-bg-music" href="javascript:void(0);"><?php echo __("Select sound", "ays-popup-box"); ?></a>
                                                    <audio controls src="<?php echo $ays_pb_close_sound; ?>" class="ays-bg-closing-music-audio"></audio>
                                                    <input type="hidden" name="ays_pb_close_sound" class="ays_pb_bg_music ays_pb_bg_music_closing_input" value="<?php echo $ays_pb_close_sound; ?>" id="ays_pb_closing_sound">
                                                    <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/times.svg"?>" class="ays_pb_sound_close_btn ays_pb_sound_closing_btn" style="<?php echo ($ays_pb_close_sound == '') ? 'display:none' : 'display:block'; ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <!-- close sound end -->
                                    </div>
                                </div>
                            </fieldset>
                            <hr>
                            <fieldset>
                                <legend>
                                    <strong style="font-size:30px;"><img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/code-file.svg"?>"></strong>
                                    <h5><?php echo __('Animation CSS File',"ays-popup-box")?></h5>
                                </legend>
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label for="ays_pb_exclude_animation_css">
                                            <span>
                                                <?php echo  __('Exclude the Animation CSS file',"ays-popup-box") ?>
                                                <a class="ays_help" data-toggle="tooltip" title="<?php echo __('If the option is enabled, then, the Animation CSS (given by the plugin) will not be applied to the website.', "ays-popup-box"); ?>">
                                                    <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/info-circle.svg"?>">
                                                </a>
                                            </span>
                                        </label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="checkbox" name="ays_pb_exclude_animation_css" id="ays_pb_exclude_animation_css" value="on" <?php echo $pb_exclude_animation_css ? 'checked' : ''; ?>>
                                    </div>
                                </div>
                            </fieldset>
                            <hr>
                            <fieldset> 
                                <legend>
                                    <strong style="font-size:30px;"><img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/globe.svg"?>"></strong>
                                    <h5><?php echo __('Who will have permission to Popup menu',"ays-popup-box")?></h5>
                                </legend>
                                <div class="col-sm-12 ays-pro-features-v2-main-box">
                                    <div class="ays-pro-features-v2-small-buttons-box">
                                        <div>
                                            <a href="https://youtu.be/Hl5i52g5lNo" target="_blank" class="ays-pro-features-v2-video-button">
                                                <div>
                                                    <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/pro-features-icons/Video_24x24.svg" ?>">
                                                    <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/pro-features-icons/Video_24x24_Hover.svg" ?>" class="ays-pb-new-video-button-hover">
                                                </div>
                                                <div class="ays-pro-features-v2-video-text">
                                                    <?php echo __("Watch video" , "ays-popup-box"); ?>
                                                </div>
                                            </a>
                                        </div>
                                        <a href="https://ays-pro.com/wordpress/popup-box" target="_blank" class="ays-pro-features-v2-upgrade-button">
                                            <div class="ays-pro-features-v2-upgrade-icon" style="background-image: url('<?php echo esc_attr(AYS_PB_ADMIN_URL); ?>/images/icons/pro-features-icons/Locked_24x24.svg');" data-img-src="<?php echo esc_attr(AYS_PB_ADMIN_URL); ?>/images/icons/pro-features-icons/Locked_24x24.svg"></div>
                                            <div class="ays-pro-features-v2-upgrade-text">
                                                <?php echo __("Upgrade" , "ays-popup-box"); ?>
                                            </div>
                                        </a>
                                    </div>
                                    
                                    <div class="form-group row">
                                        <div class="col-sm-4">
                                            <label for="ays_user_roles">
                                                <?php echo __( "Select user role", "ays-popup-box" ); ?>
                                                <a class="ays_help ays-pb-help-pro" data-toggle="tooltip" title="<?php echo __('Select user roles allowed to see the plugin on their WP dashboard and make changes in the plugins settings.',"ays-popup-box")?>">
                                                        <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/info-circle.svg"?>">
                                                </a>
                                            </label>
                                        </div>
                                        <div class="col-sm-8">
                                            <select name="ays_pb_user_roles[]" id="ays_pb_user_roles" multiple>
                                            
                                            </select>
                                        </div>
                                    </div>
                                    <blockquote>
                                        <?php echo __( "Ability to manage Popup Box plugin only for selected user roles.", "ays-popup-box" ); ?>
                                    </blockquote>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                    <div id="tab2" class="ays-pb-tab-content <?php echo ($ays_pb_tab == 'tab2') ? 'ays-pb-tab-content-active' : ''; ?>">
                        <p class="ays-subtitle">
                            <?php echo __('Integrations',"ays-popup-box");?>
                        </p>
                        <blockquote class="ays-pb-integration-tab-note">
                            <p><?php echo __('The Integrations tab works only with Contact Form, Subscription and Send File after subscription types',"ays-popup-box");?>
                        </blockquote>
                        <?php
                            do_action( 'ays_pb_settings_page_integrations' );
                        ?>
                    </div>
                    <div id="tab3" class="ays-pb-tab-content <?php echo ($ays_pb_tab == 'tab3') ? 'ays-pb-tab-content-active' : ''; ?>">
                        <p class="ays-pb-subtitle"><?php echo __('Shortcodes', "ays-popup-box") ?></p>
                        <hr/>
                        <div class="" style="padding:15px;">
                            <fieldset>
                                <legend>
                                    <strong style="font-size:30px;"><img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/users-black.svg"?>"></strong>
                                    <h5><?php echo __('User Information',"ays-popup-box")?></h5>
                                </legend>
                                <div class="form-group row" style="padding:0px;margin:0;">
                                    <div class="col-sm-12" style="padding:20px;">
                                        <div class="form-group row">
                                            <div class="col-sm-4">
                                                <label for="ays_pb_user_first_name">
                                                    <?php echo __( "User first name", "ays-popup-box" ); ?>
                                                    <a class="ays_help" data-toggle="tooltip" title="<?php echo esc_attr( __("Shows the logged-in user's First Name. If the user is not logged-in, the shortcode will be empty.","ays-popup-box") ); ?>">
                                                        <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/info-circle.svg"?>">
                                                    </a>
                                                </label>
                                            </div>
                                            <div class="col-sm-8">
                                                <input type="text" id="ays_pb_user_first_name" class="ays-text-input" onclick="this.setSelectionRange(0, this.value.length)" readonly="" value='[ays_pb_user_first_name]'>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12" style="padding:20px;">
                                        <div class="form-group row">
                                            <div class="col-sm-4">
                                                <label for="ays_pb_user_last_name">
                                                    <?php echo __( "User last name", "ays-popup-box" ); ?>
                                                    <a class="ays_help" data-toggle="tooltip" title="<?php echo esc_attr( __("Shows the logged-in user's Last Name. If the user is not logged-in, the shortcode will be empty.","ays-popup-box") ); ?>">
                                                            <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/info-circle.svg"?>">
                                                    </a>
                                                </label>
                                            </div>
                                            <div class="col-sm-8">
                                                <input type="text" id="ays_pb_user_last_name" class="ays-text-input" onclick="this.setSelectionRange(0, this.value.length)" readonly="" value='[ays_pb_user_last_name]'>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="col-sm-12" style="padding:20px;">
                                        <div class="form-group row">
                                            <div class="col-sm-4">
                                                <label for="ays_pb_user_display_name">
                                                    <?php echo __( "User display name", "ays-popup-box" ); ?>
                                                    <a class="ays_help" data-toggle="tooltip" title="<?php echo esc_attr( __("Shows the logged-in user's Display name. If the user is not logged-in, the shortcode will be empty.","ays-popup-box") ); ?>">
                                                            <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/info-circle.svg"?>">
                                                    </a>
                                                </label>
                                            </div>
                                            <div class="col-sm-8">
                                                <input type="text" id="ays_pb_user_display_name" class="ays-text-input" onclick="this.setSelectionRange(0, this.value.length)" readonly="" value='[ays_pb_user_display_name]'>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="col-sm-12" style="padding:20px;">
                                        <div class="form-group row">
                                            <div class="col-sm-4">
                                                <label for="ays_pb_user_nickname">
                                                    <?php echo __( "User nickname", "ays-popup-box" ); ?>
                                                    <a class="ays_help" data-toggle="tooltip" title="<?php echo esc_attr( __("Shows the logged-in user's nickname. If the user is not logged-in, the shortcode will be empty.","ays-popup-box") ); ?>">
                                                            <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/info-circle.svg"?>">
                                                    </a>
                                                </label>
                                            </div>
                                            <div class="col-sm-8">
                                                <input type="text" id="ays_pb_user_nickname" class="ays-text-input" onclick="this.setSelectionRange(0, this.value.length)" readonly="" value='[ays_pb_user_nickname]'>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="col-sm-12" style="padding:20px;">
                                        <div class="form-group row">
                                            <div class="col-sm-4">
                                                <label for="ays_pb_user_email">
                                                    <?php echo __( "User email", "ays-popup-box" ); ?>
                                                    <a class="ays_help" data-toggle="tooltip" title="<?php echo esc_attr( __("Shows the logged-in user's email. If the user is not logged-in, the shortcode will be empty.","ays-popup-box") ); ?>">
                                                            <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/info-circle.svg"?>">
                                                    </a>
                                                </label>
                                            </div>
                                            <div class="col-sm-8">
                                                <input type="text" id="ays_pb_user_email" class="ays-text-input" onclick="this.setSelectionRange(0, this.value.length)" readonly="" value='[ays_pb_user_email]'>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="col-sm-12" style="padding:20px;">
                                        <div class="form-group row">
                                            <div class="col-sm-4">
                                                <label for="ays_pb_current_author">
                                                    <?php echo __( "Show current popup author", "ays-popup-box" ); ?>
                                                    <a class="ays_help" data-toggle="tooltip" title="<?php echo esc_attr( __("It will show the current author of the particular popup.","ays-popup-box") ); ?>">
                                                            <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/info-circle.svg"?>">
                                                    </a>
                                                </label>
                                            </div>
                                            <div class="col-sm-8">
                                                <input type="text" id="ays_pb_current_author" class="ays-text-input" onclick="this.setSelectionRange(0, this.value.length)" readonly="" value='[ays_pb_current_author id="YOUR_PB_ID"]'>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12" style="padding:20px;">
                                        <div class="form-group row">
                                            <div class="col-sm-4">
                                                <label for="ays_pb_category_description">
                                                    <?php echo __( "Show user roles", "ays-popup-box" ); ?>
                                                    <a class="ays_help" data-toggle="tooltip" title="<?php echo esc_attr( __("Shows the logged-in user's role(s). If the user is not logged-in, the shortcode will be empty.","ays-popup-box") ); ?>">
                                                        <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/info-circle.svg"?>">
                                                    </a>
                                                </label>
                                            </div>
                                            <div class="col-sm-8">
                                                <input type="text" id="ays_pb_category_description" class="ays-text-input" onclick="this.setSelectionRange(0, this.value.length)" readonly="" value='[ays_pb_user_roles]'>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                </div>
                        </fieldset>
                        <hr>
                        <fieldset>
                            <legend>
                                <strong style="font-size:30px;">[ ]</strong>
                                <h5><?php echo __('Popup categories',"ays-popup-box"); ?></h5>
                            </legend>
                            <div class="form-group row" style="padding:0px;margin:0;">
                                <div class="col-sm-12" style="padding:20px;">
                                    <div class="form-group row">
                                        <div class="col-sm-4">
                                            <label for="ays_pb_cat_title">
                                                <?php echo __( "Shortcode", "ays-popup-box" ); ?>
                                                <a class="ays_help" data-toggle="tooltip" title="<?php echo __('You need to insert Your Popup Category ID in the shortcode. It will show the category title. If there is no popup category available/unavailable with that particular Popup Box Category ID, the shortcode will stay empty.',"ays-popup-box"); ?>">
                                                    <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/info-circle.svg"?>">
                                                </a>
                                            </label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="text" id="ays_pb_cat_title" class="ays-text-input" onclick="this.setSelectionRange(0, this.value.length)" readonly="" value='[ays_pb_cat_title id="Your_PB_Category_ID"]'>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row" style="padding:0px;margin:0;">
                                <div class="col-sm-12" style="padding:20px;">
                                    <div class="form-group row">
                                        <div class="col-sm-4">
                                            <label for="ays_pb_cat_description">
                                                <?php echo __( "Shortcode", "ays-popup-box" ); ?>
                                                <a class="ays_help" data-toggle="tooltip" title="<?php echo __('You need to insert Your Popup Category ID in the shortcode. It will show the category description. If there is no popup category available/unavailable with that particular Popup Box Category ID, the shortcode will stay empty.',"ays-popup-box"); ?>">
                                                    <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/info-circle.svg"?>">
                                                </a>
                                            </label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="text" id="ays_pb_cat_description" class="ays-text-input" onclick="this.setSelectionRange(0, this.value.length)" readonly="" value='[ays_pb_cat_description id="Your_PB_Category_ID"]'>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset> <!-- Popup categories -->
                        <hr/>
                        </div>
                    </div>
                    <div id="tab4" class="ays-pb-tab-content <?php echo ($ays_pb_tab == 'tab4') ? 'ays-pb-tab-content-active' : ''; ?>">
                        <p class="ays-subtitle">
                            <?php echo __('Message variables',"ays-popup-box")?>
                            <a class="ays_help" data-toggle="tooltip" data-html="true" title="<p style='margin-bottom:3px;'><?php echo __( 'You can copy these variables and paste them in the following options from the popup settings', "ays-popup-box" ); ?>:</p>
                                <p style='padding-left:10px;margin:0;'>- <?php echo __( 'Custom Content', "ays-popup-box" ); ?></p> ">
                                <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/info-circle.svg"?>">
                            </a>
                        </p>
                        <blockquote>
                            <p><?php echo __( "You can copy these variables and paste them in the following options from the popup settings", "ays-popup-box" ); ?>:</p>
                            <p style="text-indent:10px;margin:0;">- <?php echo __( "Custom Content", "ays-popup-box" ); ?></p>
                        </blockquote>
                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-12">        
                                <p class="vmessage">
                                    <strong>
                                        <input type="text" onClick="this.setSelectionRange(0, this.value.length)" readonly value="%%popup_title%%"/>
                                    </strong>
                                    <span> - </span>
                                    <span style="font-size:18px;">
                                        <?php echo __( "The title of the popup", "ays-popup-box"); ?>
                                    </span>
                                </p>
                                <p class="vmessage">
                                    <strong>
                                        <input type="text" onClick="this.setSelectionRange(0, this.value.length)" readonly value="%%user_name%%"/>
                                    </strong>
                                    <span> - </span>
                                    <span style="font-size:18px;">
                                        <?php echo __( "The user's display name that was filled in their WordPress site during registration.", "ays-popup-box"); ?>
                                    </span>
                                </p>
                                <p class="vmessage">
                                    <strong>
                                        <input type="text" onClick="this.setSelectionRange(0, this.value.length)" readonly value="%%user_email%%" />
                                    </strong>
                                    <span> - </span>
                                    <span style="font-size:18px;">
                                        <?php echo __( "The user's email that was filled in their WordPress site during registration.", "ays-popup-box"); ?>
                                    </span>
                                </p>
                                <p class="vmessage">
                                    <strong>
                                        <input type="text" onClick="this.setSelectionRange(0, this.value.length)" readonly value="%%user_first_name%%" class='ays-popup-message-variables-inputs'/>
                                    </strong>
                                    <span> - </span>
                                    <span style="font-size:18px;">
                                        <?php echo __( "The user's first name that was filled in their WordPress site during registration.", "ays-popup-box"); ?>
                                    </span>
                                </p>
                                <p class="vmessage">
                                    <strong>
                                        <input type="text" onClick="this.setSelectionRange(0, this.value.length)" readonly value="%%user_last_name%%" class='ays-popup-message-variables-inputs'/>
                                    </strong>
                                    <span> - </span>
                                    <span style="font-size:18px;">
                                        <?php echo __( "The user's last name that was filled in their WordPress site during registration.", "ays-popup-box"); ?>
                                    </span>
                                </p>
                                <p class="vmessage">
                                    <strong>
                                        <input type="text" onClick="this.setSelectionRange(0, this.value.length)" readonly value="%%current_popup_author%%" class='ays-popup-message-variables-inputs'/>
                                    </strong>
                                    <span> - </span>
                                    <span style="font-size:18px;">
                                        <?php echo __( "It will show the author of the current popup.", "ays-popup-box"); ?>
                                    </span>
                                </p>
                                <p class="vmessage">
                                    <strong>
                                        <input type="text" onClick="this.setSelectionRange(0, this.value.length)" readonly value="%%current_popup_author_email%%" class='ays-popup-message-variables-inputs'/>
                                    </strong>
                                    <span> - </span>
                                    <span style="font-size:18px;">
                                        <?php echo __( "It will show the author email of the current form.", "ays-popup-box"); ?>
                                    </span>
                                </p>
                                <p class="vmessage">
                                    <strong>
                                        <input type="text" onClick="this.setSelectionRange(0, this.value.length)" readonly value="%%current_popup_page_link%%" class='ays-popup-message-variables-inputs'/>
                                    </strong>
                                    <span> - </span>
                                    <span style="font-size:18px;">
                                        <?php echo __( "Prints the webpage link where the current popup is displayed.", "ays-popup-box"); ?>
                                    </span>
                                </p>
                                <p class="vmessage">
                                    <strong>
                                        <input type="text" onClick="this.setSelectionRange(0, this.value.length)" readonly value="%%user_wordpress_roles%%" class='ays-popup-message-variables-inputs'/>
                                    </strong>
                                    <span> - </span>
                                    <span style="font-size:18px;">
                                        <?php echo __( "The user's role(s) when logged-in. In case the user is not logged-in, the field will be empty.", "ays-popup-box"); ?>
                                    </span>
                                </p>
                                <p class="vmessage">
                                    <strong>
                                        <input type="text" onClick="this.setSelectionRange(0, this.value.length)" readonly value="%%user_nickname%%" class='ays-popup-message-variables-inputs'/>
                                    </strong>
                                    <span> - </span>
                                    <span style="font-size:18px;">
                                        <?php echo __( "The user's nickname that was filled in their WordPress profile.", "ays-popup-box"); ?>
                                    </span>
                                </p>
                                <p class="vmessage">
                                    <strong>
                                        <input type="text" onClick="this.setSelectionRange(0, this.value.length)" readonly value="%%creation_date%%" class='ays-pb-message-variables-inputs'/>
                                    </strong>
                                    <span> - </span>
                                    <span style="font-size:18px;">
                                        <?php echo __( "The creation date of the popup.", "ays-popup-box"); ?>
                                    </span>
                                </p>
                                <p class="vmessage">
                                    <strong>
                                        <input type="text" onClick="this.setSelectionRange(0, this.value.length)" readonly value="%%current_date%%" class='ays-pb-message-variables-inputs'/>
                                    </strong>
                                    <span> - </span>
                                    <span style="font-size:18px;">
                                        <?php echo __( "It will show the current date upon opening a popup.", "ays-popup-box"); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr/>
            <h1>
            <?php
            wp_nonce_field('settings_action', 'settings_action');
            // $other_attributes = array("id" => 'ays_submit_settings');
            $other_attributes = array(
                'id' => 'ays_submit_settings',
                'title' => 'Ctrl + s',
                'data-toggle' => 'tooltip',
                'data-delay'=> '{"show":"300"}'
            );
            submit_button(__('Save changes', "ays-popup-box"), 'primary ays-button', 'ays_submit', false, $other_attributes);
            echo $loader_iamge;
            ?>
            </h1>
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
    jQuery(document).on('change input', '#ays-pb-settings-form input, #ays-pb-settings-form select, #ays-pb-settings-form textarea', function() {
        aysUnsavedChanges = true;
    });

    jQuery(window).on('beforeunload', function(event) {
        var saveButtons = jQuery(document).find('.button#ays_submit_settings')
        var savingButtonsClicked = saveButtons.filter('.ays-save-button-clicked').length > 0;

        if (aysUnsavedChanges && !savingButtonsClicked) {
            event.preventDefault();
            event.returnValue = true;
        }
    });
</script>