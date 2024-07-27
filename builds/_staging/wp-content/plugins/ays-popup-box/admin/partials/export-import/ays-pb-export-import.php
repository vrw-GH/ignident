<?php    
?>
<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php
            echo esc_html(get_admin_page_title());
        ?>
    </h1>
    <div style="display: flex;justify-content: center; align-items: center;">
        <iframe width="560" height="315" src="https://www.youtube.com/embed/Rx5RHzmRtCM" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>
    </div>
    <div class="nav-tab-wrapper">
        <a href="#tab1" data-tab="tab1" class="nav-tab nav-tab-active"><?php echo __('Export',"ays-popup-box"); ?></a>
        <a href="#tab2" data-tab="tab2" class="nav-tab"><?php echo __("Import", "ays-popup-box"); ?></a>
    </div>
    
    <div id="tab1" class="ays-pb-tab-content ays-pb-tab-content-active" style="margin-top:15px">
        <div class="col-sm-12 ays-pro-features-v2-main-box">
            <div class="ays-pro-features-v2-big-buttons-box">                                
                
                <a href="https://ays-pro.com/wordpress/popup-box" target="_blank" class="ays-pro-features-v2-upgrade-button">
                    <div class="ays-pro-features-v2-upgrade-icon" style="background-image: url('<?php echo esc_attr(AYS_PB_ADMIN_URL); ?>/images/icons/pro-features-icons/Locked_24x24.svg');" data-img-src="<?php echo esc_attr(AYS_PB_ADMIN_URL); ?>/images/icons/pro-features-icons/Locked_24x24.svg"></div>
                    <div class="ays-pro-features-v2-upgrade-text">
                        <?php echo __("Upgrade" , "ays-popup-box"); ?>
                    </div>
                </a>
            </div>
            <div class="ays-pro-features-v2-small-buttons-box">
                
                <a href="https://ays-pro.com/wordpress/popup-box" target="_blank" class="ays-pro-features-v2-upgrade-button">
                    <div class="ays-pro-features-v2-upgrade-icon" style="background-image: url('<?php echo esc_attr(AYS_PB_ADMIN_URL); ?>/images/icons/pro-features-icons/Locked_24x24.svg');" data-img-src="<?php echo esc_attr(AYS_PB_ADMIN_URL); ?>/images/icons/pro-features-icons/Locked_24x24.svg"></div>
                    <div class="ays-pro-features-v2-upgrade-text">
                        <?php echo __("Upgrade" , "ays-popup-box"); ?>
                    </div>
                </a>
            </div>
            <h1 class="ays-subtitle"><?php echo __('Export popups',"ays-popup-box")?></h1><hr>
            <div id="export" style="padding-bottom: 10px;">
                <form action="post" id="ays-export-form">
                    <div class="form-group row">
                        <div class="col-sm-3">
                            <label for="ays_pb_export_import">
                                <?php echo __('Select popups', "ays-popup-box"); ?>
                                <a class="ays_help ays-pb-help-pro" data-toggle="tooltip" title="<?php echo __('Specify the popup boxes which must be exported. If you want to export all popup boxes just leave blank.',"ays-popup-box")?>">
                                    <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/info-circle.svg"?>">
                                </a>
                            </label>
                        </div>
                        <div class="col-sm-9 ays-pb-export-conteiner">
                            <select class="ays-pb-export" id="ays_pb_export_import">
                        
                            </select>
                        </div>    
                    </div><hr>   
                    <button type="button" class="button ays_export_pb" id="ays_export_popup_box" name="ays_pb_export_to_json">
                        <?php echo __("Export to JSON", "ays-popup-box"); ?>
                    </button>
                    <a download="" id="downloadFile" hidden href=""></a>
                </form>
            </div>
        </div>     
    </div>
    <div id="tab2" class="ays-pb-tab-content" style="margin-top:15px">
        <div class="col-sm-12 ays-pro-features-v2-main-box">
            <div class="ays-pro-features-v2-big-buttons-box">                                
                
                <a href="https://ays-pro.com/wordpress/popup-box" target="_blank" class="ays-pro-features-v2-upgrade-button">
                    <div class="ays-pro-features-v2-upgrade-icon" style="background-image: url('<?php echo esc_attr(AYS_PB_ADMIN_URL); ?>/images/icons/pro-features-icons/Locked_24x24.svg');" data-img-src="<?php echo esc_attr(AYS_PB_ADMIN_URL); ?>/images/icons/pro-features-icons/Locked_24x24.svg"></div>
                    <div class="ays-pro-features-v2-upgrade-text">
                        <?php echo __("Upgrade" , "ays-popup-box"); ?>
                    </div>
                </a>
            </div>
            <div class="ays-pro-features-v2-small-buttons-box">
                
                <a href="https://ays-pro.com/wordpress/popup-box" target="_blank" class="ays-pro-features-v2-upgrade-button">
                    <div class="ays-pro-features-v2-upgrade-icon" style="background-image: url('<?php echo esc_attr(AYS_PB_ADMIN_URL); ?>/images/icons/pro-features-icons/Locked_24x24.svg');" data-img-src="<?php echo esc_attr(AYS_PB_ADMIN_URL); ?>/images/icons/pro-features-icons/Locked_24x24.svg"></div>
                    <div class="ays-pro-features-v2-upgrade-text">
                        <?php echo __("Upgrade" , "ays-popup-box"); ?>
                    </div>
                </a>
            </div>
            <h1 class="ays-subtitle"><?php echo __('Import popups', "ays-popup-box")?></h1><hr>
            <div class="upload-import-file-wrap show-upload-view ays-pb-tab-content-area">
                <div class="upload-import-file">
                    <p class="import-help"><?php echo __( "Please upload the popup file in a .json format here.", "ays-popup-box" ); ?></p>
                    <form method="post" enctype="multipart/form-data">
                        <div class="ays-pb-import-form">
                            <input type="file" accept=".json" name="ays_pb_import_file" id="import_file"/>
                            <label class="" for="import_file"><?php echo __( "Import file", "ays-popup-box" ); ?></label>
                            <input type="submit" name="ays_pb_import" class="button" id="ays_pb_import" value="<?php echo __( "Import now", "ays-popup-box" ); ?>" disabled="">
                        </div>
                    </form>
                </div>
            </div>
        </div>    
</div>