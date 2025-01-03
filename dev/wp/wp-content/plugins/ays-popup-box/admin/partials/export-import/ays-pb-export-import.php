<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php
            echo esc_html(get_admin_page_title());
        ?>
    </h1>
    <div style="display:flex;justify-content:center;align-items:center;">
        <iframe width="560" height="315" src="https://www.youtube.com/embed/Rx5RHzmRtCM" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>
    </div>
    <div class="nav-tab-wrapper">
        <a href="#tab1" data-tab="tab1" class="nav-tab nav-tab-active"><?php echo __('Export', "ays-popup-box"); ?></a>
        <a href="#tab2" data-tab="tab2" class="nav-tab"><?php echo __('Import', "ays-popup-box"); ?></a>
    </div>
    <div id="tab1" class="ays-pb-tab-content ays-pb-tab-content-active" style="margin-top:15px">
        <div class="col-sm-12 ays-pro-features-v2-main-box">
            <div class="ays-pro-features-v2-big-buttons-box">
                <a href="https://ays-pro.com/wordpress/popup-box" target="_blank" class="ays-pro-features-v2-upgrade-button">
                    <div class="ays-pro-features-v2-upgrade-icon" style="background-image:url('<?php echo esc_attr(AYS_PB_ADMIN_URL); ?>/images/icons/pro-features-icons/Locked_24x24.svg');" data-img-src="<?php echo esc_attr(AYS_PB_ADMIN_URL); ?>/images/icons/pro-features-icons/Locked_24x24.svg"></div>
                    <div class="ays-pro-features-v2-upgrade-text">
                        <?php echo __("Upgrade", "ays-popup-box"); ?>
                    </div>
                </a>
            </div>
            <div class="ays-pro-features-v2-small-buttons-box">
                <a href="https://ays-pro.com/wordpress/popup-box" target="_blank" class="ays-pro-features-v2-upgrade-button">
                    <div class="ays-pro-features-v2-upgrade-icon" style="background-image:url('<?php echo esc_attr(AYS_PB_ADMIN_URL); ?>/images/icons/pro-features-icons/Locked_24x24.svg');" data-img-src="<?php echo esc_attr(AYS_PB_ADMIN_URL); ?>/images/icons/pro-features-icons/Locked_24x24.svg"></div>
                    <div class="ays-pro-features-v2-upgrade-text">
                        <?php echo __("Upgrade", "ays-popup-box"); ?>
                    </div>
                </a>
            </div>
            <p style="font-size:23px;font-weight:400;"><?php echo __('Export popups', "ays-popup-box")?></p>
            <hr>
            <div style="padding-bottom:10px;">
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label>
                            <?php echo __('Select popups', "ays-popup-box"); ?>
                            <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Specify the popup boxes which must be exported. If you want to export all popup boxes just leave blank.',"ays-popup-box")?>">
                                <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/info-circle.svg"?>">
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-9">
                        <select></select>
                    </div>
                </div>
                <hr>
                <button type="button" class="button">
                    <?php echo __("Export to JSON", "ays-popup-box"); ?>
                </button>
            </div>
        </div>
    </div>
    <div id="tab2" class="ays-pb-tab-content" style="margin-top:15px">
        <div class="col-sm-12 ays-pro-features-v2-main-box">
            <div class="ays-pro-features-v2-big-buttons-box">
                <a href="https://ays-pro.com/wordpress/popup-box" target="_blank" class="ays-pro-features-v2-upgrade-button">
                    <div class="ays-pro-features-v2-upgrade-icon" style="background-image:url('<?php echo esc_attr(AYS_PB_ADMIN_URL); ?>/images/icons/pro-features-icons/Locked_24x24.svg');" data-img-src="<?php echo esc_attr(AYS_PB_ADMIN_URL); ?>/images/icons/pro-features-icons/Locked_24x24.svg"></div>
                    <div class="ays-pro-features-v2-upgrade-text">
                        <?php echo __("Upgrade", "ays-popup-box"); ?>
                    </div>
                </a>
            </div>
            <div class="ays-pro-features-v2-small-buttons-box">
                <a href="https://ays-pro.com/wordpress/popup-box" target="_blank" class="ays-pro-features-v2-upgrade-button">
                    <div class="ays-pro-features-v2-upgrade-icon" style="background-image:url('<?php echo esc_attr(AYS_PB_ADMIN_URL); ?>/images/icons/pro-features-icons/Locked_24x24.svg');" data-img-src="<?php echo esc_attr(AYS_PB_ADMIN_URL); ?>/images/icons/pro-features-icons/Locked_24x24.svg"></div>
                    <div class="ays-pro-features-v2-upgrade-text">
                        <?php echo __("Upgrade", "ays-popup-box"); ?>
                    </div>
                </a>
            </div>
            <p style="font-size:23px;font-weight:400;"><?php echo __('Import popups', "ays-popup-box")?></p>
            <hr>
            <div class="ays-pb-tab-content-area">
                <div>
                    <p class="import-help"><?php echo __("Please upload the popup file in a .json format here.", "ays-popup-box"); ?></p>
                    <div class="ays-pb-import-form">
                        <input type="file"/>
                        <label><?php echo __("Import file", "ays-popup-box"); ?></label>
                        <input type="submit" class="button" value="<?php echo __("Import now", "ays-popup-box"); ?>" disabled>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>