<div class="wrap">
    <div class="ays-pb-heading-box">
        <div class="ays-pb-wordpress-user-manual-box">
            <a href="https://popup-plugin.com/docs" target="_blank">
                <img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . '/images/icons/text-file.svg' ?>">
                <span><?php echo esc_html__("View Documentation", "ays-popup-box"); ?></span>
            </a>
        </div>
    </div>
    <h1 class="wp-heading-inline">
		<?php
            echo esc_html(get_admin_page_title());
        ?>
    </h1>
    <div class="ays-pb-features-wrap">
        <div class="ays-pb-features-toggle ays-pb-features-toggle-mobile">
            <label for="ays_pb_pricing_period_mobile">
                <?php echo esc_html__( "Annual", 'ays-popup-box' ); ?>
            </label>
            <div class="ays-pb-toggle-switch-container">
                <label class="ays-pb-toggle-switch" for="ays_pb_pricing_period_mobile">
                    <input type="checkbox"  id="ays_pb_pricing_period_mobile" value="on" >
                    <span class="slider round"></span>
                </label>
            </div>
            <label for="ays_pb_pricing_period_mobile">
                <?php echo esc_html__( "Lifetime", 'ays-popup-box' ); ?>
            </label>
        </div>
        <div class="comparison">
            <table>
                <thead>
                    <tr>
                        <th class="tl tl2" style="width:350px;"></th>
                        <th class="product" style="background:#69C7F1;border-top-left-radius:5px;border-left:0px;">
                            <span style="display:block"><?php echo esc_html__('Personal', "ays-popup-box"); ?></span>
                            <img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . '/images/avatars/personal_avatar.png'; ?>" alt="Free" title="Free" width="100"/>
                        </th>
                        <th class="product" style="background:#69C7F1;">
                            <span style="display:block"><?php echo esc_html__('Business', "ays-popup-box"); ?></span>
                            <img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . '/images/avatars/business_avatar.png'; ?>" alt="Business" title="Business" width="100"/>
                        </th>
                        <th class="product" style="background:#69C7F1;border-top-right-radius:5px;border-right:0px;">
                            <span style="display:block"><?php echo esc_html__('Developer', "ays-popup-box"); ?></span>
                            <img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . '/images/avatars/pro_avatar.png'; ?>" alt="Developer" title="Developer" width="100"/>
                        </th>
                    </tr>
                    <tr>
                        <th>
                            <div class="ays-pb-features-toggle ays-pb-features-toggle-pc">
                                <label for="ays_pb_pricing_period">
                                    <?php echo esc_html__( "Annual", 'ays-popup-box' ); ?>
                                </label>
                                <div class="ays-pb-toggle-switch-container">
                                    <label class="ays-pb-toggle-switch" for="ays_pb_pricing_period">
                                        <input type="checkbox"  id="ays_pb_pricing_period" value="on" >
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                                <label for="ays_pb_pricing_period">
                                    <?php echo esc_html__( "Lifetime", 'ays-popup-box' ); ?>
                                </label>
                            </div>
                        </th>
                        <th class="price-info">
                            <div class="price-now">
                                <span><?php echo esc_html__('Free', "ays-popup-box"); ?></span>
                            </div>
                        </th>
                        <th class="price-info">
                            <div class="price-now">
                                <span style="text-decoration:line-through;color:red;" class="features-lifetime display_none">$99</span>
                                <span style="text-decoration:line-through;color:red;" class="features-annual">$49</span>
                            </div>
                            <div class="price-now features-lifetime display_none"><span>$69</span></div>
                            <div class="price-now features-annual"><span>$29</span></div>
                            <a href="https://popup-plugin.com/pricing/" class="price-buy">
                                <?php echo esc_html__('Buy now', "ays-popup-box"); ?><span class="hide-mobile"></span>
                            </a>
                            <div style="font-size:12px;" class="features-lifetime display_none"> (<?php echo esc_html__('One-time payment', "ays-popup-box"); ?>)</div>
                        </th>
                        <th class="price-info">
                            <div class="price-now">
                                <span style="text-decoration:line-through;color:red;" class="features-lifetime display_none">$250</span>
                                <span style="text-decoration:line-through;color:red;" class="features-annual">$99</span>
                            </div>
                            <div class="price-now features-lifetime display_none"><span>$149</span></div>
                            <div class="price-now features-annual"><span>$59</span></div>
                            <a href="https://popup-plugin.com/pricing/" class="price-buy">
                                <?php echo esc_html__('Buy now', "ays-popup-box"); ?><span class="hide-mobile"></span>
                            </a>
                            <div style="font-size:12px;" class="features-lifetime display_none"> (<?php echo esc_html__('One-time payment', "ays-popup-box"); ?>)</div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Can Be Used On', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Can Be Used On', "ays-popup-box"); ?>
                        </td>
                        <td>
                            <?php echo esc_html__('1 site', "ays-popup-box"); ?>
                        </td>
                        <td>
                            <?php echo esc_html__('5 sites', "ays-popup-box"); ?>
                        </td>
                        <td>
                            <?php echo esc_html__('50 sites', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr>
                        <td> </td>
                        <td colspan="3">
                            <?php echo esc_html__('Upgrade for', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Upgrade for', "ays-popup-box"); ?>
                        </td>
                        <td>
                            <?php echo esc_html__('1 months', "ays-popup-box"); ?>
                        </td>
                        <td>
                            <p class="features-annual">
                                <?php echo esc_html__('12 months', 'ays-popup-box'); ?>
                            </p>
                            <p class="features-lifetime display_none">
                                <?php echo esc_html__('Lifetime', 'ays-popup-box'); ?>
                            </p>
                        </td>
                        <td>
                            <p class="features-annual">
                                <?php echo esc_html__('12 months', 'ays-popup-box'); ?>
                            </p>
                            <p class="features-lifetime display_none">
                                <?php echo esc_html__('Lifetime', 'ays-popup-box'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Support for', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Support for', "ays-popup-box"); ?>
                        </td>
                        <td>
                            <?php echo esc_html__('1 months', "ays-popup-box"); ?>
                        </td>
                        <td>
                            <p class="features-annual">
                                <?php echo esc_html__('12 months', 'ays-popup-box'); ?>
                            </p>
                            <p class="features-lifetime display_none">
                                <?php echo esc_html__('Lifetime', 'ays-popup-box'); ?>
                            </p>
                        </td>
                        <td>
                            <p class="features-annual">
                                <?php echo esc_html__('12 months', 'ays-popup-box'); ?>
                            </p>
                            <p class="features-lifetime display_none">
                                <?php echo esc_html__('Lifetime', 'ays-popup-box'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td> </td>
                        <td colspan="3">
                            <?php echo esc_html__('Usage for', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo esc_html__('Usage for', "ays-popup-box"); ?>
                        </td>
                        <td><?php echo esc_html__('Lifetime', "ays-popup-box"); ?></td>
                        <td><?php echo esc_html__('Lifetime', "ays-popup-box"); ?></td>
                        <td><?php echo esc_html__('Lifetime', "ays-popup-box"); ?></td>
                    </tr>
                    <tr>
                        <td> </td>
                        <td colspan="3">
                            <?php echo esc_html__('Session time option', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo esc_html__('Session time option', "ays-popup-box"); ?>
                        </td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td> </td>
                        <td colspan="3">
                            <?php echo esc_html__('Responsive design', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo esc_html__('Responsive design', "ays-popup-box"); ?>
                        </td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td> </td>
                        <td colspan="3">
                            <?php echo esc_html__('Custom content popup', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo esc_html__('Custom content popup', "ays-popup-box"); ?>
                        </td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td> </td>
                        <td colspan="3">
                            <?php echo esc_html__('Shortcode popup', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo esc_html__('Shortcode popup', "ays-popup-box"); ?>
                        </td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="3">
                            <?php echo esc_html__('Video popup', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo esc_html__('Video popup', "ays-popup-box"); ?>
                        </td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="3">
                            <?php echo esc_html__('Image popup', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo esc_html__('Image popup', "ays-popup-box"); ?>
                        </td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="3">
                            <?php echo esc_html__('Facebook popup', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo esc_html__('Facebook popup', "ays-popup-box"); ?>
                        </td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="3">
                            <?php echo esc_html__('Notification popup', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo esc_html__('Notification popup', "ays-popup-box"); ?>
                        </td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td> </td>
                        <td colspan="3">
                            <?php echo esc_html__('Scroll from top', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo esc_html__('Scroll from top', "ays-popup-box"); ?>
                        </td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td> </td>
                        <td colspan="3">
                            <?php echo esc_html__('Styles', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo esc_html__('Styles', "ays-popup-box"); ?>
                        </td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="3">
                            <?php echo esc_html__('Display on pages', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo esc_html__('Display on pages', "ays-popup-box"); ?>
                        </td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="3">
                            <?php echo esc_html__('Delay', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo esc_html__('Delay', "ays-popup-box"); ?>
                        </td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="3">
                            <?php echo esc_html__('Open with click', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo esc_html__('Open with click', "ays-popup-box"); ?>
                        </td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="3">
                            <?php echo esc_html__('Popup position', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo esc_html__('Popup position', "ays-popup-box"); ?>
                        </td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Multiple scheduling', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Multiple scheduling', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Popup Reports', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Popup Reports', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Enable for selected user OS', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Enable for selected user OS', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Enable for selected browsers', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Enable for selected browser', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Limitation count', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Limitation count', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Export/Import', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Export/Import', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('User role permission', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('User role permission', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Limit by country', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Limit by country', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('MailChimp integration', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('MailChimp integration', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Campaign Monitor integration', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Campaign Monitor integration', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('ActiveCampaign integration', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('ActiveCampaign integration', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('GetResponse integration', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('GetResponse integration', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('ConvertKit integration', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('ConvertKit integration', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Brevo integration', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Brevo integration', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('MailerLite integration', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('MailerLite integration', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('reCAPTCHA integration', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('reCAPTCHA integration', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Yes or No popup', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Yes or No popup', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Iframe popup', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Iframe popup', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Subscription popup', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Subscription popup', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Contact form popup', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Contact form popup', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Send file after subscription popup', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Send file after subscription popup', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Coupon popup', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Coupon popup', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Countdown popup', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Countdown popup', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Cookie popup', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Cookie popup', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Download popup', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Download popup', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('WooCommerce popup', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('WooCommerce popup', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Login form popup', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Login form popup', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Google map popup', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Google map popup', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Close by scrolling down', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Close by scrolling down', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Close by classname', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Close by classname', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Actions while clicking on the popup', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Actions while clicking on the popup', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Title style', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Title style', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Peachy theme', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Peachy theme', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Yellowish theme', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Yellowish theme', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Coral theme', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Coral theme', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Frozen theme', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Frozen theme', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Food theme', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Food theme', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Forest theme', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Forest theme', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Exit Intent Popup', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Exit Intent Popup', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('On Hover Trigger', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('On Hover Trigger', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('After visiting X pages Trigger', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('After visiting X pages Trigger', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Inactivity Trigger', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Inactivity Trigger', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <?php echo esc_html__('Scrolling to Element Trigger', "ays-popup-box"); ?>
                        </td>
                    </tr>
                    <tr class="compare-row">
                        <td>
                            <?php echo esc_html__('Scrolling to Element Trigger', "ays-popup-box"); ?>
                        </td>
                        <td><span>-</span></td>
                        <td><span>-</span></td>
                        <td><img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . "/images/icons/check-mark.svg"?>"></td>
                    </tr>
                    <tr>
                        <td> </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td>
                            <a href="https://popup-plugin.com/pricing/" class="price-buy">
                                <?php echo esc_html__('Buy now', "ays-popup-box"); ?><span class="hide-mobile"></span>
                            </a>
                            <div style="font-size:12px;" class="features-lifetime display_none"> (<?php echo esc_html__('One-time payment', "ays-popup-box"); ?>)</div>
                        </td>
                        <td>
                            <a href="https://popup-plugin.com/pricing/" class="price-buy">
                                <?php echo esc_html__('Buy now', "ays-popup-box"); ?><span class="hide-mobile"></span>
                            </a>
                            <div style="font-size:12px;" class="features-lifetime display_none"> (<?php echo esc_html__('One-time payment', "ays-popup-box"); ?>)</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="ays-pb-guarantee-container-main">
            <div class="ays-pb-guarantee-container">
                <div>
                    <img src="<?php echo esc_url(AYS_PB_ADMIN_URL) . '/images/features/money_back_logo.webp' ?>" loading="lazy" alt="Best money-back guarantee logo">
                </div>
                <div class="ays-pb-guarantee-text-container">
                    <h3 ><?php echo esc_html__('30 day money back guarantee !!!', "ays-popup-box"); ?></h3>
                    <p>
                        <?php echo esc_html__("We're sure that you'll love our Popup Box plugin, but, if for some reason, you're not satisfied in the first 30 days of using our product, there is a money-back guarantee and we'll issue a refund.", "ays-popup-box"); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
