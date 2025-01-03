<?php

defined( 'ABSPATH' ) || exit;

defined( 'ABSPATH' ) || exit;
$logo    = plugin_dir_url( __FILE__ ) . 'assets/img/Wow-Company.png';
$img_url = plugin_dir_url( __FILE__ ) . 'assets/img/';

$wp_plugins = [
	[
		'free'    => 'https://wordpress.org/plugins/wp-coder/',
		'pro'     => 'https://wpcoder.pro/',
		'icon'    => 'wp-coder.png',
		'title'   => 'WP Coder',
		'content' => 'A profound WordPress plugin for directly incorporating custom HTML, CSS, and JavaScript codes into your WordPress pages.'
	],
	[
		'free'    => 'https://wordpress.org/plugins/float-menu/',
		'pro'     => 'https://wow-estore.com/item/float-menu-pro/',
		'icon'    => 'float-menu.png',
		'title'   => 'Float menu',
		'content' => 'The functional tool for the easy creation the attractive floating menus.'
	],
	[
		'free'    => 'https://wordpress.org/plugins/floating-button/',
		'pro'     => 'https://wow-estore.com/item/floating-button-pro/',
		'icon'    => 'floting-button.png',
		'title'   => 'Floating Button',
		'content' => 'WordPress plugin designed to generate and manage sticky Floating Buttons, capable of performing any defined actions on your website. '
	],
	[
		'free'    => 'https://wordpress.org/plugins/side-menu-lite/',
		'pro'     => 'https://wow-estore.com/item/sticky-buttons-pro/',
		'icon'    => 'side-menu.png',
		'title'   => 'Side Menu',
		'content' => 'Create compact navigation panels with attractive designs.'
	],
	[
		'free'    => 'https://wordpress.org/plugins/sticky-buttons/',
		'pro'     => 'https://wow-estore.com/item/sticky-buttons-pro/',
		'icon'    => 'sticky-buttons.png',
		'title'   => 'Sticky Buttons',
		'content' => 'Quickly create informative floating buttons that will always be within the user’s visibility, increasing his attention and providing quick access to the necessary pages of the site or action.'
	],
	[
		'free'    => 'https://wordpress.org/plugins/calculator-builder/',
		'pro'     => 'https://calchub.xyz/downloads/calculator-builder-add-on/',
		'icon'    => 'calchub.png',
		'title'   => 'Calculator Builder',
		'content' => 'A simple way to create an online calculator.'
	],
	[
		'free'    => 'https://wordpress.org/plugins/bubble-menu/',
		'pro'     => 'https://wow-estore.com/item/sticky-buttons-pro/',
		'icon'    => 'bubble-menu.png',
		'title'   => 'Bubble Menu ',
		'content' => 'Create a bright bubble menu on your WordPress site.'
	],
	[
		'free'    => 'https://wordpress.org/plugins/popup-box/',
		'pro'     => 'https://wow-estore.com/item/popup-box-pro/',
		'icon'    => 'popup-box.png',
		'title'   => 'Popup Box',
		'content' => 'Popup Box Pro – the WordPress plugin for creating awesome popups with any content.'
	],
	[
		'free'    => 'https://wordpress.org/plugins/modal-window/',
		'pro'     => 'https://wow-estore.com/item/wow-modal-windows-pro/',
		'icon'    => 'modal-windows.png',
		'title'   => 'Modal Windows',
		'content' => 'Designed to ease the process of creating and setting the modal windows on the WordPress site.'
	],
	[
		'free'    => 'https://wordpress.org/plugins/mwp-herd-effect/',
		'pro'     => 'https://wow-estore.com/item/wow-herd-effects-pro/',
		'icon'    => 'herd-effects.jpg',
		'title'   => 'Herd Effects',
		'content' => 'Designed to create a “sense of queue” or “herd effect”, motivating the visitors of the page to perform any actions.'
	],
	[
		'free'    => 'https://wordpress.org/plugins/counter-box/',
		'pro'     => 'https://wow-estore.com/item/counter-box-pro/',
		'icon'    => 'counter-box.png',
		'title'   => 'Counter Box',
		'content' => 'Quickly and easily create countdowns, counters, and timers with a live preview.'
	],
	[
		'free'    => 'https://wordpress.org/plugins/button-generation/',
		'pro'     => 'https://wow-estore.com/item/button-generator-pro/',
		'icon'    => 'button-generator.png',
		'title'   => 'Button Generator',
		'content' => 'The Button Generator plugin allows you to create stylish responsive buttons with a variety of CSS effects and informational badges.'
	],
	[
		'free'    => '',
		'pro'     => 'https://codecanyon.net/item/viral-subscription-wordpress-plugin-for-creating-a-viral-optin-form/29897766',
		'icon'    => 'viral-subscription.jpg',
		'title'   => 'Viral Subscription',
		'content' => 'The WordPress plugin Viral Subscriptions allows you to create unlimited subscription forms with a viral effect.'
	],
	[
		'free'    => '',
		'pro'     => 'https://codecanyon.net/item/docsy-wordpress-plugin-for-online-documentation/37768067',
		'icon'    => 'docsy.png',
		'title'   => 'Docsy',
		'content' => 'WordPress plugin Docsy helps you create and manage the online documentation for your products, services, and projects.'
	],
	[
		'free'    => 'https://wordpress.org/plugins/cf7-coder/',
		'pro'     => '',
		'icon'    => 'HTML-Editor-CF7.png',
		'title'   => 'HTML Editor for Contact Form 7',
		'content' => 'Add HTML editor to Contact Form 7 with code highlighter.'
	],
	[
		'free'    => 'https://wordpress.org/plugins/markdown-comment/',
		'pro'     => '',
		'icon'    => 'markdown-comment.png',
		'title'   => 'Markdown Comment',
		'content' => 'Adds the ability to use Markdown formatting in comment.'
	],
	[
		'free'    => 'https://wordpress.org/plugins/mwp-skype/',
		'pro'     => 'https://wow-estore.com/item/wow-skype-buttons-pro/',
		'icon'    => 'skype.png',
		'title'   => 'Skype Buttons',
		'content' => 'Add on a site the Skype buttons with adjustable functionality and display options.'
	],
];

$wp_themes = [
	[
		'free'    => '',
		'pro'     => 'https://wow-estore.com/item/doxy-wordpress-theme/',
		'icon'    => 'doxy.png',
		'title'   => 'Doxy',
		'content' => 'Doxy is a WordPress theme designed for creating a knowledge base, help desk website, and more.'
	],
	[
		'free'    => 'https://wordpress.org/themes/iknowledgebase/',
		'pro'     => 'https://wow-estore.com/item/iknowledgebase-pro/',
		'icon'    => 'IknowledgeBase.png',
		'title'   => 'IKnowledgeBase',
		'content' => 'WordPress Knowledge Base Theme.'
	],
	[
		'free'    => 'https://wordpress.org/themes/knowledgecenter/',
		'pro'     => 'https://wow-estore.com/item/knowledgecenter-pro/',
		'icon'    => 'KnowledgeCenter.jpg',
		'title'   => 'KnowledgeCenter',
		'content' => 'WordPress Theme for easily create a knowledge base, help desk, support, wiki or site with products documentations.'
	],
];

$sites = [
	[
		'link'    => 'https://calchub.xyz/',
		'icon'    => 'calchub.png',
		'title'   => 'CalcHub',
		'content' => 'Make it easy for you to navigate and find the right online calculator for your needs. We offer a wide variety of calculators in categories such as finance, health, math, and more.'
	],
	[
		'link'    => 'https://wpcalc.com/en/home/',
		'icon'    => 'wpcalc.png',
		'title'   => 'WPCalc',
		'content' => 'Collection of online calculators and converters for various applications.'
	],
];

?>

<div class="wrap full-width-layout wpie-page">

    <header class="wpie-page__header">

        <div class="wpie-badge">
            <img src="<?php
			echo esc_url( $logo ); ?>" alt="Wow-Company Logo">
        </div>

        <h1>Wow-Company</h1>

        <p class="about-text">
            Wow-Company is not a company in the literal sense. These different projects and WordPress Plugins are
            collected under one umbrella, which decided to call Wow-Company. At first, it all started as a hobby, and
            then it turned into an interesting daily job. </p>

    </header>

    <div class="wpie-page__content">
        <h2>WordPress plugins</h2>
        <div class="item-cards">
			<?php
			foreach ( $wp_plugins as $plugin ): ?>
                <div class="item-card">
                    <div class="item-img">
                        <img src="<?php
						echo esc_url( $img_url . '/' . $plugin['icon'] ); ?>">
                    </div>
                    <div class="item-content">
                        <div class="item-title"><?php
							echo esc_html( $plugin['title'] ); ?></div>
                        <div class="item-links">
							<?php
							if ( ! empty( $plugin['free'] ) ) : ?>
                                <a href="<?php echo esc_url( $plugin['free'] ); ?>">Free</a>
							<?php
							endif; ?>
							<?php
							if ( ! empty( $plugin['pro'] ) ) : ?>
                                <a href="<?php echo esc_url( $plugin['pro'] ); ?>" target="_blank">Pro</a>
							<?php
							endif; ?>
                        </div>
                    </div>
                    <div class="item-description">
						<?php
						echo esc_html( $plugin['content'] ); ?>
                    </div>

                </div>

			<?php
			endforeach; ?>

        </div>

        <h2>WordPress themes</h2>
        <div class="item-cards">
			<?php
			foreach ( $wp_themes as $theme ): ?>
                <div class="item-card">
                    <div class="item-img">
                        <img src="<?php
						echo esc_url( $img_url . '/' . $theme['icon'] ); ?>">
                    </div>
                    <div class="item-content">
                        <div class="item-title"><?php
							echo esc_html( $theme['title'] ); ?></div>
                        <div class="item-links">
							<?php
							if ( ! empty( $theme['free'] ) ) : ?>
                                <a href="<?php
								echo esc_url( $theme['free'] ); ?>">Free</a>
							<?php
							endif; ?>
							<?php
							if ( ! empty( $theme['pro'] ) ) : ?>
                                <a href="#">Pro</a>
							<?php
							endif; ?>
                        </div>
                    </div>
                    <div class="item-description">
						<?php
						echo esc_html( $theme['content'] ); ?>
                    </div>

                </div>

			<?php
			endforeach; ?>

        </div>

        <h2>WebSites</h2>
        <div class="item-cards">
			<?php
			foreach ( $sites as $site ): ?>
                <div class="item-card">
                    <div class="item-img">
                        <img src="<?php
						echo esc_url( $img_url . '/' . $site['icon'] ); ?>">
                    </div>
                    <div class="item-content">
                        <div class="item-title"><?php
							echo esc_html( $site['title'] ); ?></div>
                        <div class="item-links">
							<?php
							if ( ! empty( $site['link'] ) ) : ?>
                                <a href="<?php
								echo esc_url( $site['link'] ); ?>">Go to Site</a>
							<?php
							endif; ?>

                        </div>
                    </div>
                    <div class="item-description">
						<?php
						echo esc_html( $site['content'] ); ?>
                    </div>

                </div>

			<?php
			endforeach; ?>

        </div>

    </div>
</div>