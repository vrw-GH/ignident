<?php

$allPages = get_pages();

$projects404 = LS_Sliders::find([
	'columns' => 'id,name',
	'limit' => 100,
	'data' => false,
	'where' => "name LIKE '%404%' OR keywords LIKE '%404%'"
]);

$recentProjects = LS_Sliders::find([
	'columns' => 'id,name',
	'limit' => 20,
	'data' => false,
]);

$allProjects = LS_Sliders::find([
	'columns' => 'id,name',
	'orderby' => 'name',
	'order' => 'ASC',
	'limit' => 1000,
	'data' => false
]);

$foundSelectedProject = false;

$addonData = [
	'enabled' 		=> get_option( 'ls-404-addon-enabled', false ),
	'type' 			=> get_option( 'ls-404-addon-type', 'project' ),
	'project' 		=> get_option( 'ls-404-addon-project', 0 ),
	'page' 			=> get_option( 'ls-404-addon-page', 0 ),
	'mode' 			=> get_option( 'ls-404-addon-mode', 'normal' ),
	'title' 		=> get_option( 'ls-404-addon-title', '' ),
	'background' 	=> get_option( 'ls-404-addon-background', '#ffffff' )
];

if( ! LS_Config::isActivatedSite() ) {
	$addonData['enabled'] = false;
}

?>

<lse-b id="ls-404-addon-settings" class="ls--addon-settings" data-enabled="<?= ! empty( $addonData['enabled'] ) ? 'true' : 'false' ?>" data-published="<?= ! empty( $addonData['enabled'] ) ? 'true' : 'false' ?>" data-type="<?= $addonData['type'] ?>">

	<ls-h5><?= __('Add-on Status', 'LayerSlider') ?></ls-h5>
	<ls-box class="ls-settings-table">
		<table>
			<tbody>

				<tr class="ls--404-type-tr ls--form-control">
					<td>
						<label>
							<?= lsGetSwitchControl([
								'name' => 'enabled',
								'checked' => ! empty( $addonData['enabled'] )
							]) ?>
						</label>
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
		<input type="hidden" name="action" value="ls_404_save_addon_settings">
		<table>
			<tbody>

				<tr class="ls--404-type-tr">
					<td>
						<?= __('Content', 'LayerSlider') ?>
					</td>
					<td>
						<select name="type">
							<option value="project" <?= ( $addonData['type'] === 'project') ? 'selected' : '' ?>><?= __('LayerSlider Project', 'LayerSlider') ?></option>
							<option value="page" <?= ( $addonData['type'] === 'page') ? 'selected' : '' ?>><?= __('WordPress Page', 'LayerSlider') ?></option>
						</select>
					</td>
				</tr>

				<tr class="ls--404-project-type-only">
					<td>
						<?= __('Project', 'LayerSlider') ?>
					</td>
					<td>
						<select name="project">

							<?php if( ! empty( $projects404 ) ) : ?>
							<optgroup label="<?= __('Your 404 Projects', 'LayerSlider') ?>">
								<?php
								foreach( $projects404 as $project ) {

									$selectAttr = '';
									if( ! $foundSelectedProject &&  $project['id'] === $addonData['project'] ) {
										$foundSelectedProject = true;
										$selectAttr = 'selected';
									}
								?>
								<option value="<?= $project['id'] ?>" <?= $selectAttr ?>><?= apply_filters('ls_slider_title', stripslashes( $project['name'] ), 40) ?> | #<?= $project['id'] ?></option>
								<?php } ?>
							</optgroup>
							<?php endif ?>

							<?php if( ! empty( $recentProjects ) ) : ?>
							<optgroup label="<?= __('Recent Projects', 'LayerSlider') ?>">
							<?php
								foreach( $recentProjects as $project ) {

									$selectAttr = '';
									if( ! $foundSelectedProject &&  $project['id'] === $addonData['project'] ) {
										$foundSelectedProject = true;
										$selectAttr = 'selected';
									}
								?>
								<option value="<?= $project['id'] ?>" <?= $selectAttr ?>><?= apply_filters('ls_slider_title', stripslashes( $project['name'] ), 40) ?> | #<?= $project['id'] ?></option>
								<?php } ?>
							</optgroup>
							<?php endif ?>

							<?php if( ! empty( $allProjects ) ) : ?>
							<optgroup label="<?= __('All Projects', 'LayerSlider') ?>">
							<?php
								foreach( $allProjects as $project ) {

									$selectAttr = '';
									if( ! $foundSelectedProject &&  $project['id'] === $addonData['project'] ) {
										$foundSelectedProject = true;
										$selectAttr = 'selected';
									}
								?>
								<option value="<?= $project['id'] ?>" <?= $selectAttr ?>><?= apply_filters('ls_slider_title', stripslashes( $project['name'] ), 40) ?> | #<?= $project['id'] ?></option>
								<?php } ?>
							</optgroup>
							<?php endif ?>
						</select>

					</td>
				</tr>

				<tr class="ls--404-project-type-only ls--form-control ls--no-space-on-top">
					<td>
					</td>
					<td>
						<a target="_blank" class="ls--404-open-project-editor-button ls--button ls--small ls--bg-light" data-href-base="<?= admin_url('admin.php?page=layerslider&action=edit&id=') ?>" href="<?= admin_url('admin.php?page=layerslider&action=edit&id='.$addonData['project']) ?>">
							<?= __('Open in Editor', 'LayerSlider') ?>
							<?= lsGetSVGIcon('arrow-right') ?>
						</a>
					</td>
				</tr>

				<tr class="ls--404-page-type-only">
					<td>
						<?= __('Page', 'LayerSlider') ?>
					</td>
					<td>
						<select name="page">
							<?php foreach( $allPages as $page ) : ?>
							<option value="<?= $page->ID ?>" <?= ( $page->ID === (int) $addonData['page'] ) ? 'selected' : '' ?>><?= ! empty( $page->post_title ) ? $page->post_title : __('(no title)', 'Layerslider') ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr class="ls--404-page-type-only ls--form-control ls--no-space-on-top">
					<td>
					</td>
					<td>
						<a target="_blank" class="ls--404-open-page-editor-button ls--button ls--small ls--bg-light" data-href-base="<?= admin_url('post.php?action=edit&post=') ?>" href="<?= admin_url('post.php?action=edit&post='.$addonData['page']) ?>">
							<?= __('Edit Page', 'LayerSlider') ?>
							<?= lsGetSVGIcon('arrow-right') ?>
						</a>
					</td>
				</tr>

				<tr class="ls--404-page-type-only">
					<td>
						<?= __('Mode', 'LayerSlider') ?>
					</td>
					<td>
						<select name="mode">
							<option value="normal" <?= ( $addonData['mode'] === 'normal' ) ? 'selected' : '' ?>><?= __('Normal (recommended)', 'LayerSlider') ?></option>
							<option value="redirect" <?= ( $addonData['mode'] === 'redirect' ) ? 'selected' : '' ?>><?= __('Redirect', 'LayerSlider') ?></option>
						</select>
					</td>
				</tr>

				<tr class="ls--404-project-type-only">
					<td>
						<?= __('Page Title', 'LayerSlider') ?>
					</td>
					<td>
						<input type="text" name="title" value="<?= $addonData['title'] ?>" placeholder="<?= __( 'Page not found' ) ?>">
					</td>
				</tr>

				<tr class="ls--404-project-type-only">
					<td>
						<?= __('Page Color', 'LayerSlider') ?>
					</td>
					<td>
						<input type="color" name="background" value="<?= $addonData['background'] ?>">
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

		<a href="<?= site_url('/layerslider-404-preview-'.time()) ?>" target="_blank" class="ls-404-preview-button ls--addon-published-only ls--button ls--small ls--bg-lightgray ls--white ls--ml-2">
			<?= __('Preview', 'LayerSlider') ?>
		</a>
	</ls-p>

</lse-b>