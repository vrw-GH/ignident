<?php
    $pb_page_url = sprintf('?page=%s', 'ays-pb');
    $add_new_url = sprintf('?page=%s&action=%s', 'ays-pb', 'add');
?>

<div class="wrap">
    <div class="ays-pb-heart-beat-main-heading ays-pb-heart-beat-main-heading-container">
        <h1 class="ays-popup-box-wrapper ays_heart_beat">
            <?php echo esc_html(get_admin_page_title()); ?> <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/hearth-black.svg"?>">
        </h1>
    </div>
    <div class="ays-pb-faq-main">
        <h2>
            <?php echo __("How to create a simple popup in 4 steps with the", "ays-popup-box") .
            ' <strong>'. __("Popup Box", "ays-popup-box") . '</strong> ' .
            __("plugin.", "ays-popup-box"); ?>
        </h2>
        <fieldset>
            <div class="ays-pb-ol-container">
                <ol>
                    <li>
                        <?php echo __("Go to the", "ays-popup-box") . ' <a href="' . $pb_page_url . '" target="_blank">' . __("Popups", "ays-popup-box") . '</a> ' . __("page and click on the", "ays-popup-box") . ' <a href="' . $add_new_url . '" target="_blank">' . __("Add New", "ays-popup-box") . '</a> ' . __("button", "ays-popup-box"); ?>,
                    </li>
                    <li>
                        <?php echo __("Select the popup type.", "ays-popup-box"); ?>
                        <ul>
                            <li><?php echo '<strong>' . __("Custom Content", "ays-popup-box") . '</strong> '; ?></li>
                            <li><?php echo '<strong>' . __("Shortcode", "ays-popup-box") . '</strong> '; ?></li>
                            <li><?php echo '<strong>' . __("Video", "ays-popup-box") . '</strong> '; ?></li>
                            <li><?php echo '<strong>' . __("Image", "ays-popup-box") . '</strong> '; ?></li>
                            <li><?php echo '<strong>' . __("Facebook", "ays-popup-box") . '</strong> '; ?></li>
                            <li><?php echo '<strong>' . __("Notification", "ays-popup-box") . '</strong> '; ?></li>
                        </ul>
                    </li>
                    <li>
                        <?php echo __("Choose when to show the popup with the", "ays-popup-box") . ' <strong>' . __("Popup trigger", "ays-popup-box") . '</strong> ' . __("option.", "ays-popup-box"); ?>
                        <ul>
                            <li><?php echo '<strong>' . __("On page load:", "ays-popup-box") . '</strong> ' . __("Choose to show the popup as soon as the page is loaded.", "ays-popup-box") ; ?></li>
                            <li><?php echo '<strong>' . __("On click:", "ays-popup-box") . '</strong> ' . __("Choose to show the popup as soon as the user clicks on the assigned CSS element(s). You can assign CSS elements with the", "ays-popup-box") . ' <strong>' . __("CSS selector(s) for trigger click", "ays-popup-box") . '</strong> ' . __("option.", "ays-popup-box"); ?></li>
                        </ul>
                    </li>
                    <li>
                        <?php echo __("In the end, click on the", "ays-popup-box") . ' <strong>' . __("Save Changes", "ays-popup-box") . '</strong> ' . __("button.", "ays-popup-box"); ?>
                    </li>
                </ol>
            </div>
            <div class="ays-pb-p-container">
                <p><?php echo __("That's it! Your popup is ready to be displayed!", "ays-popup-box"); ?></p>
            </div>
        </fieldset>
    </div>
    <br>
    <div class="ays-pb-community-wrap">
        <div>
            <h4><?php echo __("Community", "ays-popup-box"); ?></h4>
        </div>
        <div class="ays-pb-community-container">
            <div class="ays-pb-community-item">
                <a href="https://www.youtube.com/channel/UC-1vioc90xaKjE7stq30wmA" target="_blank" class="ays-pb-community-item-cover" style="display:flex;align-items:center;justify-content:center;">
                    <img src="<?php echo AYS_PB_ADMIN_URL . '/images/icons/youtube-community.svg'; ?>">
                </a>
                <h3 class="ays-pb-community-item-title"><?php echo __("YouTube community", "ays-popup-box"); ?></h3>
                <p class="ays-pb-community-item-desc"><?php echo __("Our YouTube community guides you to step by step tutorials about our products and not only...", "ays-popup-box"); ?></p>
                <div class="ays-pb-community-item-footer">
                    <a href="https://www.youtube.com/channel/UC-1vioc90xaKjE7stq30wmA" target="_blank" class="button"><?php echo __("Subscribe", "ays-popup-box"); ?></a>
                </div>
            </div>
            <div class="ays-pb-community-item">
                <a href="https://wordpress.org/support/plugin/ays-popup-box/" target="_blank" class="ays-pb-community-item-cover" style="display:flex;align-items:center;justify-content:center;">
                    <img src="<?php echo AYS_PB_ADMIN_URL . '/images/icons/wordpress-community.svg'; ?>">
                </a>
                <h3 class="ays-pb-community-item-title"><?php echo __("Best Free Support", "ays-popup-box"); ?></h3>
                <p class="ays-pb-community-item-desc"><?php echo __("With the Free version, you get a lifetime usage for the plugin, however, you will get new updates and support for only 1 month.", "ays-popup-box"); ?></p>
                <div class="ays-pb-community-item-footer">
                    <a href="https://wordpress.org/support/plugin/ays-popup-box/" target="_blank" class="button"><?php echo __("Join", "ays-popup-box"); ?></a>
                </div>
            </div>
            <div class="ays-pb-community-item">
                <a href="https://ays-pro.com/contact" target="_blank" class="ays-pb-community-item-cover" style="display:flex;align-items:center;justify-content:center;">
                    <img src="<?php echo AYS_PB_ADMIN_URL . "/images/icons/users.svg"?>">
                </a>
                <h3 class="ays-pb-community-item-title"><?php echo __("Premium support", "ays-popup-box"); ?></h3>
                <p class="ays-pb-community-item-desc"><?php echo __("Get 12 months updates and support for the Business package and lifetime updates and support for the Developer package.", "ays-popup-box"); ?></p>
                <div class="ays-pb-community-item-footer">
                    <a href="https://ays-pro.com/contact" target="_blank" class="button"><?php echo __("Contact", "ays-popup-box"); ?></a>
                </div>
            </div>
        </div>
    </div>
</div>
