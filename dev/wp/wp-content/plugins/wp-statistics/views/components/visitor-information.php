<?php
namespace WP_STATISTICS;
?>

<ul class="wps-visitor__information--container">
    <li class="wps-visitor__information">
        <div class="wps-tooltip" title="<?php echo esc_attr("$visitor->agent v$visitor->version") ?>">
            <a href="<?php echo esc_url(Menus::admin_url('visitors', ['agent' => $visitor->agent])) ?>"><img src="<?php echo esc_url(UserAgent::getBrowserLogo($visitor->agent)) ?>" alt="<?php echo esc_attr($visitor->agent) ?>" width="15" height="15"></a>
        </div>
    </li>

    <li class="wps-visitor__information">
        <div class="wps-tooltip" title="<?php echo esc_attr($visitor->platform) ?>">
            <a href="<?php echo esc_url(Menus::admin_url('visitors', ['platform' => $visitor->platform])) ?>"><img src="<?php echo esc_url(UserAgent::getPlatformLogo($visitor->platform)) ?>" alt="<?php echo esc_attr($visitor->platform) ?>" width="15" height="15"></a>
        </div>
    </li>

    <?php if (!empty($visitor->user_id)) : ?>
        <li class="wps-visitor__information">
            <div>

                <?php if (Option::get('visitors_log')): ?>
                    <a href="<?php echo esc_url(Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->ID])); ?>">
                        <span class="wps-visitor__information__user-img"></span>
                    </a>
                    <a class="wps-visitor__information__user-text" href="<?php echo esc_url(Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->ID])); ?>">
                        <span><?php echo esc_html($visitor->display_name) ?></span>
                        <span>#<?php echo esc_html($visitor->user_id) ?></span>
                    </a>
                <?php else: ?>
                    <div class="wps-tooltip" data-tooltip-content="#tooltip_user_id">
                        <a href="<?php echo esc_url(Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->ID])); ?>"><span class="wps-visitor__information__user-img"></span></a>
                    </div>
                    <div class="wps-tooltip_templates">
                        <div id="tooltip_user_id">
                            <div><?php esc_html_e('ID', 'wp-statistics'); ?>:&nbsp;<?php echo esc_html($visitor->user_id); ?></div>
                            <div><?php esc_html_e('Name', 'wp-statistics'); ?>:&nbsp;<?php echo esc_html($visitor->display_name); ?></div>
                            <div><?php esc_html_e('Email', 'wp-statistics'); ?>:&nbsp;<?php echo esc_html($visitor->user_email); ?></div>
                            <div><?php echo IP::IsHashIP($visitor->ip) ? sprintf(esc_html__('Daily Visitor Hash: %s', 'wp-statistics'), substr($visitor->ip, 6, 10)) : sprintf(esc_html__('IP: %s', 'wp-statistics'), $visitor->ip) ?></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </li>
    <?php else : ?>
        <li class="wps-visitor__information">
            <div>
                <?php if (Option::get('visitors_log')): ?>
                    <a href="<?php echo esc_url(Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->ID])) ?>">
                        <span class="wps-visitor__information__incognito-img"></span>
                    </a>
                    <span class="wps-visitor__information__incognito-text">
                        <?php echo IP::IsHashIP($visitor->ip) ? '#' . substr($visitor->ip, 6, 8) : $visitor->ip; ?>
                    </span>
                <?php else: ?>
                    <div class="wps-tooltip" title="<?php echo IP::IsHashIP($visitor->ip) ? sprintf(esc_attr__('Daily Visitor Hash: %s', 'wp-statistics'), substr($visitor->ip, 6, 10)) : sprintf(esc_attr__('IP: %s', 'wp-statistics'), $visitor->ip) ?>">
                        <a href="<?php echo esc_url(Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->ID])) ?>"><span class="wps-visitor__information__incognito-img"></span></a>
                    </div>
                <?php endif; ?>
            </div>
        </li>
    <?php endif; ?>
</ul>
<?php if (!empty($visitor->user_id) && Option::get('visitors_log')) : ?>
    <div class="wps-visitor__information__user-more-info">
        <div><?php esc_html_e('Email', 'wp-statistics') ?>: <?php echo esc_html($visitor->user_email) ?></div>
        <div><?php esc_html_e('Role', 'wp-statistics') ?>: <span class="c-capitalize"><?php echo esc_html(User::get($visitor->user_id)['role'][0]) ?></span></div>
    </div>
<?php endif; ?>
