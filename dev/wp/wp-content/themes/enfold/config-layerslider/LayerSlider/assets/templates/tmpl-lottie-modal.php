<?php

defined( 'LS_ROOT_FILE' ) || exit;

?>
<div class="ls-d-none">
	<div id="lse-lottie-modal-sidebar">
		<div class="kmw-sidebar-title">
			<?= __('Lottie Library', 'LayerSlider') ?>
		</div>
		<kmw-navigation class="km-tabs-list" data-target="#lse-lottie-modal-tabs-content">

			<kmw-menutitle>
				<kmw-menutext><?= __('Collections', 'LayerSlider') ?></kmw-menutext>
			</kmw-menutitle>
			<kmw-menuitem class="lse-load-user-animations">
				<?= lsGetSVGIcon('rectangle-history-circle-user', 'solid', false, 'kmw-icon') ?>
				<kmw-menutext><?= __('My Animations', 'LayerSlider') ?></kmw-menutext>
			</kmw-menuitem>

			<kmw-menutitle id="lse-lottie-modal-sidebar-library-title">
				<kmw-menutext><?= __('Library', 'LayerSlider') ?></kmw-menutext>
			</kmw-menutitle>

		</kmw-navigation>

		<lse-b id="lse-lottie-sidebar-bottom" class="lse-modal-sidebar-bottom">
			<?= sprintf(__('Assets provided by %slottiefiles.com%s', 'LayerSlider'), '<a href="https://lottiefiles.com" target="_blank">', '</a>') ?>
		</lse-b>
	</div>

	<lse-b id="lse-lottie-modal-content">
		<kmw-h1 class="kmw-modal-title"><?= __('Built-In Examples', 'LayerSlider') ?></kmw-h1>
		<lse-b class="kmw-modal-toolbar lse-tar lse-common-modal-style lse-light-theme-alternate">
			<form id="lse-lottie-upload-form" class="lse-d-none" enctype="multipart/form-data" method="post" action="">
				<input id="lse-lottie-upload-input" type="file" name="lottie-file" multiple accept=".json,.lottie,application/json,application/x-lottie">
			</form>
			<lse-button class="lse-large lse-upload-lottie-button"><?= __('Add New', 'LayerSlider') ?></lse-button>
			<lse-button class="lse-large lse-enter-lottie-url"><?= __('Enter from URL', 'LayerSlider') ?></lse-button>
		</lse-b>
		<lse-b id="lse-lottie-modal-tabs-content">
			<lse-b id="lse-lottie-modal-user-animations">
				<lse-b class="lse-lottie-animations-grid"></lse-b>

				<lse-b id="lse-lottie-not-found" class="lse-not-found">
					<div class="not-found-icon">
						<?= lsGetSVGIcon( 'clapperboard-play', 'duotone' ) ?>
					</div>
					<div class="not-found-main-text">
						<?= __('Your Lottie Animations Appear Here', 'LayerSlider') ?>
					</div>
					<div class="not-found-sub-text">
						<?= __('Click the “Add New” button above to upload.', 'LayerSlider') ?>
					</div>
				</lse-b>
			</lse-b>
		</lse-b>
	</lse-b>
</div>