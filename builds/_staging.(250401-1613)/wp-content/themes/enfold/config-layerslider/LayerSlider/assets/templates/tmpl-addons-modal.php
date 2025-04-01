<?php defined( 'LS_ROOT_FILE' ) || exit; ?>

<ls-b class="ls-hidden">

	<ls-b id="ls-addons-modal-sidebar">

		<ls-b class="km-tabs-content" id="ls-addons-content">

			<!-- Templates -->
			<ls-b data-tab="template-store">

				<ls-box class="ls--show-if-not-registered ls-show-activation-box">
					<ls-b>
					<?= __('Register license to access premium templates.', 'LayerSlider') ?>
					</ls-b>
				</ls-box>

				<ls-b class="ls--addon-desc">
					<ls-p>
						<?= sprintf(__('%s You’re just one click away from the web design that will blow your visitors’ mind. %s Browse a vast collection of highly customizable slider, popup, hero scene, scroll scene, and complete website templates. Pre-made with care and attention to detail. They are an ideal starting point for new projects and they cover every common use case from personal to corporate business.', 'LayerSlider'), '<b class="ls--text-highlight">', '</b>' ) ?>
					</ls-p>
				</ls-b>
				<ls-p class="ls--form-control ls--text-center">
					<ls-button-group class="ls--vertical">
						<a href="#" data-category="discover" class="ls-open-template-store ls--button">
							<?= __('Discover Templates', 'LayerSlider') ?>
						</a>
						<a href="#" data-category="sliders" class="ls-open-template-store ls--button">
							<?= __('Browse Sliders', 'LayerSlider') ?>
						</a>
						<a href="#" data-category="kreatura-popups" class="ls-open-template-store ls--button">
							<?= __('Browse Popups', 'LayerSlider') ?>
						</a>
						<a href="#" data-category="webshopworks-popups" class="ls-open-template-store ls--button">
							<?= __('Browse WebshopWorks Popups', 'LayerSlider') ?>
						</a>
					</ls-button-group>
				</ls-p>
			</ls-b>


			<!-- Counter -->
			<ls-b data-tab="counter">

				<ls-box class="ls--show-if-not-registered ls-show-activation-box">
					<ls-b>
					<?= __('Register license to use this feature.', 'LayerSlider') ?>
					</ls-b>
				</ls-box>

				<ls-b class="ls--addon-desc">
					<ls-p>
						<?= sprintf(__('%sBring your numbers to life with the Counter Add-On%s. Whether tracking progress, highlighting statistics, or counting down to key events, this feature adds dynamic animation that’s sure to capture your visitors’ attention. Effortlessly customize the design and watch your numbers stand out in style.', 'LayerSlider'), '<b class="ls--text-highlight">', '</b>' ) ?>
					</ls-p>
				</ls-b>

				<ls-p class="ls--form-control ls--text-center">
					<ls-button-group class="ls--vertical">

						<a target="_blank" href="https://layerslider.com/sliders/ionara-counters/" class="ls--button">
							<?= __('See It In Action', 'LayerSlider') ?>
						</a>

					</ls-button-group>
				</ls-p>

			</ls-b>


			<!-- Countdown -->
			<ls-b data-tab="countdown">

				<ls-box class="ls--show-if-not-registered ls-show-activation-box">
					<ls-b>
					<?= __('Register license to use this feature.', 'LayerSlider') ?>
					</ls-b>
				</ls-box>

				<ls-b class="ls--addon-desc">
					<ls-p>
						<?= sprintf(__('Elevate your website with alluring countdowns that engage your audience. With the %sCountdown Add-On%s, you can effortlessly create stunning timers to build anticipation for events, product launches, or special offers. Customize with ease, choose from a variety of styles, and get your visitors’ attention with urgency and style.', 'LayerSlider'), '<b class="ls--text-highlight">', '</b>' ) ?>
					</ls-p>
				</ls-b>

				<ls-h5><?= __('Examples', 'LayerSlider') ?></ls-h5>
				<ls-p class="ls--form-control ls--text-center">
					<ls-button-group class="ls--vertical">

						<a target="_blank" href="https://layerslider.com/sliders/ride-with-us/" class="ls--button">
							<?= __('RIDE with US', 'LayerSlider') ?>
						</a>
						<a target="_blank" href="https://layerslider.com/popups/#big-sale-countdown" class="ls--button">
							<?= __('Big Sale Countdown', 'LayerSlider') ?>
						</a>
						<a target="_blank" href="https://layerslider.com/popups/webshopworks/#black-friday-premium" class="ls--button">
							<?= __('Black Friday Premium', 'LayerSlider') ?>
						</a>
						<a target="_blank" href="https://layerslider.com/popups/webshopworks/#sidebar-countdown-sale" class="ls--button">
							<?= __('Sidebar Countdown Sale', 'LayerSlider') ?>
						</a>
						<a target="_blank" href="https://layerslider.com/popups/webshopworks/#christmas-sidebar-countdown" class="ls--button">
							<?= __('Christmas Sidebar Countdown', 'LayerSlider') ?>
						</a>
					</ls-button-group>
				</ls-p>

			</ls-b>

			<!-- Maintenance & Coming Soon -->
			<ls-b data-tab="maintenance">

				<ls-box class="ls--show-if-not-registered ls-show-activation-box">
					<ls-b>
					<?= __('Register license to use this feature.', 'LayerSlider') ?>
					</ls-b>
				</ls-box>

				<ls-b class="ls--addon-desc">
					<ls-p>
						<?= sprintf(__('%sDon’t let maintenance work or site launches be a roadblock for your visitors.%s Our Maintenance & Coming Soon add-on allows you to turn those static, inactive pages into engaging and informative experiences. Keep your audience excited while your site gets ready behind the scenes.', 'LayerSlider'), '<b class="ls--text-highlight">', '</b>') ?>
					</ls-p>
				</ls-b>

				<ls-p class="ls--form-control ls--text-center">
					<ls-button-group class="ls--vertical">
						<a href="#" data-category="maintenance" class="ls-open-template-store ls--button">
							<?= __('Discover Templates', 'LayerSlider') ?>
						</a>
					</ls-button-group>
				</ls-p>

				<ls-b class="ls--addon-settings-container" id="ls--addon-maintenance-ajax-container"></ls-b>
			</ls-b>


			<!-- 404 -->
			<ls-b data-tab="404">

				<ls-box class="ls--show-if-not-registered ls-show-activation-box">
					<ls-b>
					<?= __('Register license to use this feature.', 'LayerSlider') ?>
					</ls-b>
				</ls-box>

				<ls-b class="ls--addon-desc">
					<ls-p>
						<?= sprintf(__('%sRevolutionize Your 404 Pages with LayerSlider.%s Don’t let those dull 404 “Not Found” error messages be a dead-end for your visitors. LayerSlider’s 404 add-on empowers you to transform those boring and frustrating error pages into captivating experiences.', 'LayerSlider'), '<b class="ls--text-highlight">', '</b>' ) ?>
					</ls-p>
				</ls-b>

				<ls-p class="ls--form-control ls--text-center">
					<ls-button-group class="ls--vertical">
						<a href="#" data-category="404" class="ls-open-template-store ls--button">
							<?= __('Discover 404 Templates', 'LayerSlider') ?>
						</a>
						</ls-button-group>
				</ls-p>

				<ls-b class="ls--addon-settings-container" id="ls--addon-404-ajax-container"></ls-b>
			</ls-b>


			<!-- Shape Editor -->
			<ls-b data-tab="shape-editor">

				<ls-box class="ls--show-if-not-registered ls-show-activation-box">
					<ls-b>
					<?= __('Register license to use this feature.', 'LayerSlider') ?>
					</ls-b>
				</ls-box>

				<ls-b class="ls--addon-desc">
					<ls-p>
						<?= sprintf(__('%sLayerSlider lets you easily generate the perfect vector-based graphics%s for your needs from rectangles and ovals to polygons and complex shapes. Precisely controlled or randomized results ensure unique shapes. Waves and Blobs with optional multi-layered variations can add striking design features to any project.', 'LayerSlider'), '<b class="ls--text-highlight">', '</b>' ) ?>
					</ls-p>
				</ls-b>
				<!-- <ls-grid class="ls--thumbs-grid">
					<a href="" style="background-image: url();" target="_blank"></a>
					<a href="" style="background-image: url();" target="_blank"></a>
					<a href="" style="background-image: url();" target="_blank"></a>
				</ls-grid> -->
			</ls-b>


			<!-- Origami -->
			<ls-b data-tab="origami">

				<ls-box class="ls--show-if-not-registered ls-show-activation-box">
					<ls-b>
					<?= __('Register license to use this feature.', 'LayerSlider') ?>
					</ls-b>
				</ls-box>

				<ls-b class="ls--addon-desc">
					<ls-p>
						<?= sprintf(__('%sFold your users’ expectations.%s Origami slide transition is the perfect solution to share your gorgeous photos with the world or your loved ones in a truly inspirational way and create sliders with stunning effects.', 'LayerSlider'), '<b class="ls--text-highlight">', '</b>' ) ?>
					</ls-p>
				</ls-b>
				<ls-p class="ls--form-control ls--text-center">
					<ls-button-group class="ls--vertical">
						<a href="https://layerslider.com/sliders/origami/" target="_blank" class="ls--button">
							<?= __('Origami Demo', 'LayerSlider') ?>
						</a>
						<a href="https://layerslider.com/sliders/origami-buildings/" target="_blank" class="ls--button">
							<?= __('Origami Buildings Demo', 'LayerSlider') ?>
						</a>
					</ls-button-group>
				</ls-p>
			</ls-b>


			<!-- Assets Library -->
			<ls-b data-tab="assets-library">

				<ls-box class="ls--show-if-not-registered ls-show-activation-box">
					<ls-b>
					<?= __('Register license to access millions of photos & videos.', 'LayerSlider') ?>
					</ls-b>
				</ls-box>

				<ls-b class="ls--addon-desc">
					<ls-p>
						<?= sprintf(__('Ready to make your designs stand out? LayerSlider’s Assets Library offers thousands of objects and %smillions of royalty-free stock photos & videos%s to choose from. Save time and impress your audience with stunning graphics in just a few clicks.', 'LayerSlider'), '<b class="ls--text-highlight">', '</b>' ) ?>
					</ls-p>
				</ls-b>
			</ls-b>


			<!-- Revisions -->
			<ls-b data-tab="revisions">

				<ls-box class="ls--show-if-not-registered ls-show-activation-box">
					<ls-b>
					<?= __('Register license to use this feature.', 'LayerSlider') ?>
					</ls-b>
				</ls-box>

				<ls-b class="ls--addon-desc">
					<ls-p>
						<?= sprintf(__('%sYou can go back in time.%s Have a peace of mind knowing that your slider edits are always safe and you can revert back unwanted changes or faulty saves at any time. Revisions serves not just as a backup solution, but a complete version control system where you can visually compare the changes you have made along the way.', 'LayerSlider'), '<b class="ls--text-highlight">', '</b>' ) ?>
					</ls-p>
				</ls-b>
				<ls-p class="ls--form-control ls--text-center">
					<ls-button-group>
						<a target="_blank" href="https://layerslider.com/features/revisions/" class="ls--button">
							<?= __('Learn More', 'LayerSlider') ?>
						</a>
					</ls-button-group>
				</ls-p>

				<?php

				?>

				<ls-b class="ls--addon-settings-container">
					<ls-b id="lse-revisions-settings" class="ls--addon-settings"  data-enabled="<?= LS_Revisions::$active ? 'true' : 'false' ?>">

						<ls-h5><?= __('Add-on Status', 'LayerSlider') ?></ls-h5>
						<ls-box class="ls-settings-table">
							<input type="hidden" name="action" value="ls_save_revisions_options">
							<?php wp_nonce_field('ls-save-revisions-options'); ?>
							<table>
								<tbody>

									<tr class="ls--form-control">
										<td>
											<?= lsGetSwitchControl([
												'name' => 'enabled',
												'checked' => LS_Revisions::$active ? 'checked' : ''
											]) ?>
										</td>
										<td>
											<?= __('Enable Add-On', 'LayerSlider') ?>
										</td>
									</tr>
								</tbody>
							</table>
						</ls-box>

						<ls-h5 class="ls--addon-enabled-only"><?= __('Add-on Settings', 'LayerSlider') ?></ls-h5>
						<ls-box class="ls--addon-enabled-only ls-settings-table">
							<?php wp_nonce_field('ls-save-addon-setting', 'nonce'); ?>
							<table class="ls-widest">
								<tbody>
									<tr>
										<td>
											<?= __('Revisions Per Project', 'LayerSlider') ?>
										</td>
										<td>
											<input type="number" name="limit" value="<?= LS_Revisions::$limit ?>">
										</td>
									</tr>

									<tr>
										<td>
											<?= __('Create Revisions After', 'LayerSlider') ?>
										</td>
										<td>
											<ls-unit-wrapper>
												<input type="number" name="interval" value="<?= LS_Revisions::$interval ?>">
												<ls-unit><?= __('min', 'LayerSlider') ?></ls-unit>
											</ls-unit-wrapper>
										</td>
									</tr>

								</tbody>
							</table>
						</ls-box>

						<ls-p class="ls--form-control ls--text-center">
							<a href="#" data-state="" class="ls-addon-save-button ls--button ls--small ls--bg-lightgray ls--white">
								<ls-ib><?= __('Save Changes', 'LayerSlider') ?></ls-ib>
								<ls-ib><?= __('Saving ...', 'LayerSlider') ?></ls-ib>
							</a>
						</ls-p>
					</ls-b>
				</ls-b>
			</ls-b>


			<!-- Icon Library -->
			<ls-b data-tab="icon-library">

				<ls-box class="ls--show-if-not-registered ls-show-activation-box">
					<ls-b>
					<?= __('Register license to access thousands of icons.', 'LayerSlider') ?>
					</ls-b>
				</ls-box>

				<ls-b class="ls--addon-desc">
					<ls-p>
						<?= sprintf(__('%sA subtle touch of adding icons to your content can make all the difference%s between outdated and elegant design, and you don’t have to settle for generic ones, either. Choose from our extensive and diverse collection of %smore than 16,000 icons%s with unique styles to make your project stand out.', 'LayerSlider'), '<b class="ls--text-highlight">', '</b>', '<b class="ls--text-highlight">', '</b>' ) ?>
					</ls-p>
				</ls-b>
			</ls-b>


			<!-- Text Mask Effects -->
			<ls-b data-tab="text-mask-effects">

				<ls-box class="ls--show-if-not-registered ls-show-activation-box">
					<ls-b>
					<?= __('Register license to use this feature.', 'LayerSlider') ?>
					</ls-b>
				</ls-box>

				<ls-b class="ls--addon-desc">
					<ls-p>
						<?= sprintf(__('%sElevate your website’s design with text mask effects%s - the perfect way to create eye-catching and unforgettable fonts easier than ever before. Apply gradients or texture on your fonts with just a few clicks to make them look cinematic and inspiring that your visitors will remember.', 'LayerSlider'), '<b class="ls--text-highlight">', '</b>' ) ?>
					</ls-p>
				</ls-b>
			</ls-b>


			<!-- Support -->
			<ls-b data-tab="support">

				<ls-box class="ls--show-if-not-registered ls-show-activation-box">
					<ls-b>
					<?= __('Register license to get 1-on-1 priority support.', 'LayerSlider') ?>
					</ls-b>
				</ls-box>

				<ls-b class="ls--addon-desc">
					<ls-p>
						<?= sprintf(__('%sReceive help directly from the team behind LayerSlider.%s With our expert assistance, you can trust that your needs will be met, questions answered, and problems solved, leaving you with more time and peace of mind to focus on what truly matters to you.', 'LayerSlider'), '<b class="ls--text-highlight">', '</b>' ) ?>
					</ls-p>
				</ls-b>
				<ls-p class="ls--form-control ls--text-center">
					<ls-button-group>
						<a target="_blank" href="https://layerslider.com/help/" class="ls--button">
							<?= __('Get Help', 'LayerSlider') ?>
						</a>
					</ls-button-group>
				</ls-p>
			</ls-b>


			<!-- Instant Updates -->
			<ls-b data-tab="updates">

				<ls-box class="ls--show-if-not-registered ls-show-activation-box">
					<ls-b>
					<?= __('Register license to get instant updates.', 'LayerSlider') ?>
					</ls-b>
				</ls-box>

				<ls-b class="ls--addon-desc">
					<ls-p>
						<?= sprintf(__('Effortlessly stay in the loop and receive all the new features and content updates without a hitch. %sOne-click installation%s of the latest LayerSlider version or opt for automatic background updates for a hassle-free maintenance experience. Plus, enjoy %searly-access releases%s and safeguard your website with the latest %ssecurity patches%s. Keep your website in peak condition with ease.', 'LayerSlider'), '<b class="ls--text-highlight">', '</b>', '<b class="ls--text-highlight">', '</b>', '<b class="ls--text-highlight">', '</b>' ) ?>
					</ls-p>
				</ls-b>
			</ls-b>


			<!-- Scroll Effects -->
			<ls-b data-tab="scroll-effects">

				<ls-box class="ls--show-if-not-registered ls-show-activation-box">
					<ls-b>
					<?= __('Register license to use this feature.', 'LayerSlider') ?>
					</ls-b>
				</ls-box>

				<ls-b class="ls--addon-desc">
					<ls-p>
						<?= sprintf( __('Create captivating interactions between your website and visitors. You can add all sorts of scroll effects with %sScroll Transition%s, while %sScroll Scene%s keeps your content on the screen that visitors can play back and forth by scrolling the page. A %sSticky Scene%s plays animations normally, but also sticks to the center of the screen for a given time, thus further maintaining your users’ attention.', 'LayerSlider'), '<b class="ls--text-highlight">', '</b>', '<b class="ls--text-highlight">', '</b>', '<b class="ls--text-highlight">', '</b>' ) ?>
					</ls-p>
				</ls-b>

				<ls-h5><?= __('Examples', 'LayerSlider') ?></ls-h5>
				<ls-p class="ls--form-control ls--text-center">
					<ls-button-group class="ls--vertical">
						<a target="_blank" href="https://layerslider.com/sliders/the-web-company/" class="ls--button">
							<?= __('The Web Company', 'LayerSlider') ?>
						</a>
						<a target="_blank" href="https://layerslider.com/sliders/lsvr-tech/" class="ls--button">
							<?= __('LSVR-Tech', 'LayerSlider') ?>
						</a>
						<a target="_blank" href="https://layerslider.com/sliders/flavor-factory/" class="ls--button">
							<?= __('Flavor Factory', 'LayerSlider') ?>
						</a>
						<a target="_blank" href="https://layerslider.com/sliders/sweet-candies/" class="ls--button">
							<?= __('Sweet Candies', 'LayerSlider') ?>
						</a>
						<a target="_blank" href="https://layerslider.com/sliders/scrolling-christmas-2022/" class="ls--button">
							<?= __('Scrolling Christmas', 'LayerSlider') ?>
						</a>
						<a target="_blank" href="https://layerslider.com/sliders/fairy/" class="ls--button">
							<?= __('FAIRY – a magical company', 'LayerSlider') ?>
						</a>
					</ls-button-group>
				</ls-p>
			</ls-b>


			<!-- Popups -->
			<ls-b data-tab="popups">

				<ls-box class="ls--show-if-not-registered ls-show-activation-box">
					<ls-b>
					<?= __('Register license to use this feature.', 'LayerSlider') ?>
					</ls-b>
				</ls-box>

				<ls-b class="ls--addon-desc">
					<ls-p>
						<?= sprintf(__('%sPopups is a completely different way of using LayerSlider%s and it greatly extends its capabilities and what you can build with the plugin. Combining our strong foundation and the vast number of features we already have, the Popup feature makes LayerSlider one of the best choice among popup plugins.', 'LayerSlider'), '<b class="ls--text-highlight">', '</b>' ) ?>
					</ls-p>
				</ls-b>
				<ls-p class="ls--form-control ls--text-center">
					<ls-button-group class="ls--vertical">
						<a href="#" data-category="kreatura-popups" class="ls-open-template-store ls--button">
							<?= __('Browse Popup Templates', 'LayerSlider') ?>
						</a>
						<a href="#" data-category="webshopworks-popups" class="ls-open-template-store ls--button">
							<?= __('Browse WebshopWorks Templates', 'LayerSlider') ?>
						</a>
					</ls-button-group>
				</ls-p>
			</ls-b>


			<!-- Image Editor -->
			<ls-b data-tab="image-editor">
				<ls-b class="ls--addon-desc">
					<ls-p>
						<?= sprintf(__('%sElevate your visuals and make every image shine with our integrated Image Editor%s – where creativity meets simplicity. Effortlessly resize, crop, and rotate images, then add your personal touch with filters, frames, text, stickers, and many exciting features. It’s akin to having your own pocket-sized Photoshop.', 'LayerSlider'), '<b class="ls--text-highlight">', '</b>' ) ?>
					</ls-p>
				</ls-b>
			</ls-b>
		</ls-b>
	</ls-b>

	<ls-b id="ls-addons-modal-content">
		<kmw-h1 class="kmw-modal-title"><?= __('Add-Ons & Premium Benefits', 'LayerSlider') ?></kmw-h1>

		<ls-grid id="ls-addons-grid" class="ls--h-2 ls--v-1">

			<ls-row class="km-tabs-list" data-target="#ls-addons-content" data-disable-auto-rename>

				<ls-col class="kmw-menuitem ls--col1-3"  data-tab-target="counter">
					<ls-box>
						<ls-b class="ls--container">
							<video class="ls--video" muted src="https://layerslider.com/media/premium/counter.mp4"></video>
						</ls-b>
					</ls-box>
					<ls-b class="ls--title">
						<?= __('Counter', 'LayerSlider') ?>
					</ls-b>
					<lse-badge class="ls--show-if-registered"><?= __('NEW', 'LayerSlider') ?></lse-badge>
					<lse-badge class="ls--show-if-not-registered ls-show-activation-box"><?= lsGetSVGIcon('lock-keyhole') ?></lse-badge>
				</ls-col>

				<ls-col class="kmw-menuitem ls--col1-3"  data-tab-target="maintenance">
					<ls-box>
						<ls-b class="ls--container">
							<video class="ls--video" muted src="https://layerslider.com/media/premium/under-maintenance.mp4"></video>
						</ls-b>
					</ls-box>
					<ls-b class="ls--title">
						<?= __('Maintenance & Coming Soon', 'LayerSlider') ?>
					</ls-b>
					<lse-badge class="ls--show-if-registered"><?= __('NEW', 'LayerSlider') ?></lse-badge>
					<lse-badge class="ls--show-if-not-registered ls-show-activation-box"><?= lsGetSVGIcon('lock-keyhole') ?></lse-badge>
				</ls-col>

				<ls-col class="kmw-menuitem ls--col1-3"  data-tab-target="countdown">
					<ls-box>
						<ls-b class="ls--container">
							<video class="ls--video" muted src="https://layerslider.com/media/premium/countdown.mp4"></video>
						</ls-b>
					</ls-box>
					<ls-b class="ls--title">
						<?= __('Countdown', 'LayerSlider') ?>
					</ls-b>
					<lse-badge class="ls--show-if-registered"><?= __('NEW', 'LayerSlider') ?></lse-badge>
					<lse-badge class="ls--show-if-not-registered ls-show-activation-box"><?= lsGetSVGIcon('lock-keyhole') ?></lse-badge>
				</ls-col>

				<ls-col class="kmw-menuitem ls--col1-4"  data-tab-target="404">
					<ls-box>
						<ls-b class="ls--container">
							<video class="ls--video ls--allowstop" muted src="https://layerslider.com/media/premium/404.mp4"></video>
						</ls-b>
					</ls-box>
					<ls-b class="ls--title">
						<?= __('404 Page', 'LayerSlider') ?>
					</ls-b>
					<lse-badge class="ls--show-if-not-registered ls-show-activation-box"><?= lsGetSVGIcon('lock-keyhole') ?></lse-badge>
				</ls-col>

				<ls-col class="kmw-menuitem ls--col1-4" data-tab-target="template-store">
					<ls-box>
						<ls-b class="ls--container">
							<!-- <ls-b class="ls--tn" id="p-template-store"></ls-b> -->
							<video class="ls--video" muted src="https://layerslider.com/media/premium/template-store.mp4"></video>
						</ls-b>
					</ls-box>
					<ls-b class="ls--title">
						<?= __('Premium Templates', 'LayerSlider') ?>
					</ls-b>
					<lse-badge class="ls--show-if-not-registered ls-show-activation-box"><?= lsGetSVGIcon('lock-keyhole') ?></lse-badge>
				</ls-col>

				<ls-col class="kmw-menuitem ls--col1-4"  data-tab-target="revisions">
					<ls-box>
					<ls-b class="ls--container">
							<video class="ls--video" muted src="https://layerslider.com/media/premium/revisions.mp4"></video>
						</ls-b>
					</ls-box>
					<ls-b class="ls--title">
						<?= __('Revisions', 'LayerSlider') ?>
					</ls-b>
					<lse-badge class="ls--show-if-not-registered ls-show-activation-box"><?= lsGetSVGIcon('lock-keyhole') ?></lse-badge>
				</ls-col>

				<ls-col class="kmw-menuitem ls--col1-4"  data-tab-target="scroll-effects">
					<ls-box>
						<ls-b class="ls--container">
							<video class="ls--video ls--allowstop" muted src="https://layerslider.com/media/premium/scroll-effects.mp4"></video>
						</ls-b>
					</ls-box>
					<ls-b class="ls--title">
						<?= __('Scroll Effects', 'LayerSlider') ?>
					</ls-b>
					<lse-badge class="ls--show-if-not-registered ls-show-activation-box"><?= lsGetSVGIcon('lock-keyhole') ?></lse-badge>
				</ls-col>

				<ls-col class="kmw-menuitem ls--col1-4"  data-tab-target="assets-library">
					<ls-box>
						<ls-b class="ls--container">
							<video class="ls--video" muted src="https://layerslider.com/media/premium/assets-library.mp4"></video>
						</ls-b>
					</ls-box>
					<ls-b class="ls--title">
						<?= __('Assets Library', 'LayerSlider') ?>
					</ls-b>
					<lse-badge class="ls--show-if-not-registered ls-show-activation-box"><?= lsGetSVGIcon('lock-keyhole') ?></lse-badge>
				</ls-col>

				<ls-col class="kmw-menuitem ls--col1-4"  data-tab-target="popups">
					<ls-box>
						<ls-b class="ls--container ls--nozoom">
							<video class="ls--video" muted src="https://layerslider.com/media/premium/popups.mp4"></video>
						</ls-b>
					</ls-box>
					<ls-b class="ls--title">
						<?= __('Popups', 'LayerSlider') ?>
					</ls-b>
					<lse-badge class="ls--show-if-not-registered ls-show-activation-box"><?= lsGetSVGIcon('lock-keyhole') ?></lse-badge>
				</ls-col>

				<ls-col class="kmw-menuitem ls--col1-4"  data-tab-target="origami">
					<ls-box>
						<ls-b class="ls--container ls--nozoom">
							<video class="ls--video" muted src="https://layerslider.com/media/premium/origami.mp4"></video>
						</ls-b>
					</ls-box>
					<ls-b class="ls--title">
						<?= __('Origami Slide Transition', 'LayerSlider') ?>
					</ls-b>
					<lse-badge class="ls--show-if-not-registered ls-show-activation-box"><?= lsGetSVGIcon('lock-keyhole') ?></lse-badge>
				</ls-col>

				<ls-col class="kmw-menuitem ls--col1-4"  data-tab-target="shape-editor">
					<ls-box>
						<ls-b class="ls--container">
							<video class="ls--video ls--allowstop" muted src="https://layerslider.com/media/premium/shape-editor.mp4"></video>
						</ls-b>
					</ls-box>
					<ls-b class="ls--title">
						<?= __('Shape Editor', 'LayerSlider') ?>
					</ls-b>
					<lse-badge class="ls--show-if-not-registered ls-show-activation-box"><?= lsGetSVGIcon('lock-keyhole') ?></lse-badge>
				</ls-col>

				<ls-col class="kmw-menuitem ls--col1-4"  data-tab-target="icon-library">
					<ls-box>
						<ls-b class="ls--container">
							<video class="ls--video" muted src="https://layerslider.com/media/premium/icon-library.mp4"></video>
						</ls-b>
					</ls-box>
					<ls-b class="ls--title">
						<?= __('Icon Library', 'LayerSlider') ?>
					</ls-b>
					<lse-badge class="ls--show-if-not-registered ls-show-activation-box"><?= lsGetSVGIcon('lock-keyhole') ?></lse-badge>
				</ls-col>

				<ls-col class="kmw-menuitem ls--col1-4"  data-tab-target="text-mask-effects">
					<ls-box>
						<ls-b class="ls--container">
							<video class="ls--video" muted src="https://layerslider.com/media/premium/text-mask-effects.mp4"></video>
						</ls-b>
					</ls-box>
					<ls-b class="ls--title">
						<?= __('Text Mask Effects', 'LayerSlider') ?>
					</ls-b>
					<lse-badge class="ls--show-if-not-registered ls-show-activation-box"><?= lsGetSVGIcon('lock-keyhole') ?></lse-badge>
				</ls-col>

				<ls-col class="kmw-menuitem ls--col1-4"  data-tab-target="support">
					<ls-box>
						<ls-b class="ls--container">
							<video class="ls--video" muted src="https://layerslider.com/media/premium/support.mp4"></video>
						</ls-b>
					</ls-box>
					<ls-b class="ls--title">
						<?= __('Premium Support', 'LayerSlider') ?>
					</ls-b>
					<lse-badge class="ls--show-if-not-registered ls-show-activation-box"><?= lsGetSVGIcon('lock-keyhole') ?></lse-badge>
				</ls-col>

				<ls-col class="kmw-menuitem ls--col1-4"  data-tab-target="updates">
					<ls-box>
						<ls-b class="ls--container">
							<video class="ls--video" muted src="https://layerslider.com/media/premium/updates.mp4"></video>
						</ls-b>
					</ls-box>
					<ls-b class="ls--title">
						<?= __('Instant Updates', 'LayerSlider') ?>
					</ls-b>
					<lse-badge class="ls--show-if-not-registered ls-show-activation-box"><?= lsGetSVGIcon('lock-keyhole') ?></lse-badge>
				</ls-col>

				<ls-col class="kmw-menuitem ls--col1-4"  data-tab-target="image-editor">
					<ls-box>
						<ls-b class="ls--container ls--nozoom">
							<video class="ls--video" muted src="https://layerslider.com/media/premium/image-editor.mp4"></video>
						</ls-b>
					</ls-box>
					<ls-b class="ls--title">
						<?= __('Image Editor', 'LayerSlider') ?>
					</ls-b>
				</ls-col>

				<ls-col class="ls--col-placeholder" data-exclude>
				</ls-col>

			</ls-row>

		</ls-grid>

	</ls-b>

</ls-b>