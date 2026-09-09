<?php defined( 'LS_ROOT_FILE' ) || exit; ?>
<script type="text/html" id="tmpl-add-new-slider">
	<form method="post" id="add-new-slider-modal" class="ls--form-control">
		<?php wp_nonce_field('add-slider'); ?>
		<input type="hidden" name="ls-add-new-slider" value="1">

		<kmw-h1 class="kmw-modal-title"><?= __('Add New Project', 'LayerSlider') ?></kmw-h1>

		<label class="ls-field-label" for="ls-new-project-name"><?= __('Project Name', 'LayerSlider') ?></label>
		<input type="text" id="ls-new-project-name" name="title" placeholder="<?= __('e.g., Homepage Slider', 'LayerSlider') ?>" autocomplete="off">

		<button type="submit" class="ls--button ls--bg-blue ls--white">
			<?= lsGetSVGIcon('plus', false, [ 'class' => 'lse-it-fix' ]) ?>
			<?= __('Create Blank Project', 'LayerSlider') ?>
		</button>

		<div class="ls-or-divider"><span><?= __('or', 'LayerSlider') ?></span></div>

		<button type="button" class="ls--button ls--bg-light ls-open-template-store">
			<?= lsGetSVGIcon('layer-group', false, [ 'class' => 'lse-it-fix' ]) ?>
			<?= __('Browse Templates', 'LayerSlider') ?>
		</button>
	</form>
</script>
