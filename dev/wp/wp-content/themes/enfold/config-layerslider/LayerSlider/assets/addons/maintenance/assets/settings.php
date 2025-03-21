<?php

$allPages = get_pages();

$projectsMaintenance = LS_Sliders::find([
	'columns' => 'id,name',
	'limit' => 100,
	'data' => false,
	'where' => "name LIKE '%maintenance%' OR keywords LIKE '%maintenance%' OR name LIKE '%coming soon%' OR keywords LIKE '%coming soon%' OR name LIKE '%under construction%' OR keywords LIKE '%under construction%'"
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
	'enabled' 		=> get_option( 'ls-maintenance-addon-enabled', false ),
	'capability' 	=> get_option( 'ls-maintenance-addon-capability', 'manage_options' ),
	'type' 			=> get_option( 'ls-maintenance-addon-type', 'maintenance' ),
	'content' 		=> get_option( 'ls-maintenance-addon-content', 'project' ),
	'project' 		=> get_option( 'ls-maintenance-addon-project', 0 ),
	'page' 			=> get_option( 'ls-maintenance-addon-page', 0 ),
	'mode' 			=> get_option( 'ls-maintenance-addon-mode', 'normal' ),
	'title' 		=> get_option( 'ls-maintenance-addon-title', '' ),
	'background' 	=> get_option( 'ls-maintenance-addon-background', '#ffffff' )
];

if( ! LS_Config::isActivatedSite() ) {
	$addonData['enabled'] = false;
}

?>

<lse-b id="ls-maintenance-addon-settings" class="ls--addon-settings" data-enabled="<?= ! empty( $addonData['enabled'] ) ? 'true' : 'false' ?>" data-published="<?= ! empty( $addonData['enabled'] ) ? 'true' : 'false' ?>" data-content="<?= $addonData['content'] ?>">

	<ls-h5><?= __('Add-on Status', 'LayerSlider') ?></ls-h5>
	<ls-box class="ls-settings-table">
		<table>
			<tbody>

				<tr class="ls--maintenance-content-tr ls--form-control">
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
		<input type="hidden" name="action" value="ls_maintenance_save_addon_settings">
		<table>
			<tbody>

				<tr class="ls--maintenance-content-tr">
					<td>
						<?= __('Site Access', 'LayerSlider') ?>
					</td>
					<td>
						<select name="capability">
							<?php if( is_multisite() ) : ?>
							<option value="manage_network" <?= ($addonData['capability'] == 'manage_network') ? 'selected="selected"' : '' ?>> <?= __('Super Admins', 'LayerSlider') ?></option>
							<?php endif; ?>
							<option value="manage_options" <?= ($addonData['capability'] == 'manage_options') ? 'selected="selected"' : '' ?>> <?= __('Admins', 'LayerSlider') ?></option>
							<option value="publish_pages" <?= ($addonData['capability'] == 'publish_pages') ? 'selected="selected"' : '' ?>> <?= __('Editors, Admins', 'LayerSlider') ?></option>
							<option value="publish_posts" <?= ($addonData['capability'] == 'publish_posts') ? 'selected="selected"' : '' ?>> <?= __('Authors, Editors, Admins', 'LayerSlider') ?></option>
							<option value="edit_posts" <?= ($addonData['capability'] == 'edit_posts') ? 'selected="selected"' : '' ?>> <?= __('Contributors, Authors, Editors, Admins', 'LayerSlider') ?></option>
						</select>
					</td>
				</tr>

				<tr class="ls--maintenance-content-tr">
					<td>
						<?= __('Type', 'LayerSlider') ?>
					</td>
					<td>
						<select name="type">
							<option value="maintenance" <?= ($addonData['type'] == 'maintenance') ? 'selected="selected"' : '' ?>> <?= __('Maintenance (HTTP 503 – Temporarily Unavailable, No Indexing)', 'LayerSlider') ?></option>
							<option value="comingsoon" <?= ($addonData['type'] == 'comingsoon') ? 'selected="selected"' : '' ?>> <?= __('Coming Soon (HTTP 200 – Accessible, Indexed)', 'LayerSlider') ?></option>
						</select>
					</td>
				</tr>

				<tr class="ls--maintenance-content-tr">
					<td>
						<?= __('Content', 'LayerSlider') ?>
					</td>
					<td>
						<select name="content">
							<option value="project" <?= ( $addonData['content'] === 'project') ? 'selected' : '' ?>><?= __('LayerSlider Project', 'LayerSlider') ?></option>
							<option value="page" <?= ( $addonData['content'] === 'page') ? 'selected' : '' ?>><?= __('WordPress Page', 'LayerSlider') ?></option>
						</select>
					</td>
				</tr>

				<tr class="ls--maintenance-project-content-only">
					<td>
						<?= __('Project', 'LayerSlider') ?>
					</td>
					<td>
						<select name="project">

							<?php if( ! empty( $projectsMaintenance ) ) : ?>
							<optgroup label="<?= __('Your maintenance Projects', 'LayerSlider') ?>">
								<?php
								foreach( $projectsMaintenance as $project ) {

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

				<tr class="ls--maintenance-project-content-only ls--form-control ls--no-space-on-top">
					<td>
					</td>
					<td>
						<a target="_blank" class="ls--maintenance-open-project-editor-button ls--button ls--small ls--bg-light" data-href-base="<?= admin_url('admin.php?page=layerslider&action=edit&id=') ?>" href="<?= admin_url('admin.php?page=layerslider&action=edit&id='.$addonData['project']) ?>">
							<?= __('Open in Editor', 'LayerSlider') ?>
							<?= lsGetSVGIcon('arrow-right') ?>
						</a>
					</td>
				</tr>

				<tr class="ls--maintenance-page-content-only">
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
				<tr class="ls--maintenance-page-content-only ls--form-control ls--no-space-on-top">
					<td>
					</td>
					<td>
						<a target="_blank" class="ls--maintenance-open-page-editor-button ls--button ls--small ls--bg-light" data-href-base="<?= admin_url('post.php?action=edit&post=') ?>" href="<?= admin_url('post.php?action=edit&post='.$addonData['page']) ?>">
							<?= __('Edit Page', 'LayerSlider') ?>
							<?= lsGetSVGIcon('arrow-right') ?>
						</a>
					</td>
				</tr>

				<tr class="ls--maintenance-page-content-only">
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

				<tr class="ls--maintenance-project-content-only">
					<td>
						<?= __('Page Title', 'LayerSlider') ?>
					</td>
					<td>
						<input type="text" name="title" value="<?= $addonData['title'] ?>" placeholder="<?= __( 'Maintenance' ) ?>">
					</td>
				</tr>

				<tr class="ls--maintenance-project-content-only">
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
		<a href="#" data-state="" data-before-publish-confirmation="1" data-before-publish-title="<?= __('Are you sure you want to activate this add-on?', 'LayerSlider') ?>" data-before-publish-text="<?= __('Once activated, your visitors will only see the selected WordPress page or LayerSlider project until you turn off the Maintenance & Coming Soon add-on.', 'LayerSlider') ?>" data-before-publish-button="<?= __('Activate Add-On', 'LayerSlider') ?>" class="ls-addon-save-button ls--button ls--small ls--bg-lightgray ls--white">
			<ls-ib><?= __('Save Changes', 'LayerSlider') ?></ls-ib>
			<ls-ib><?= __('Saving ...', 'LayerSlider') ?></ls-ib>
		</a>

		<a href="<?= site_url('/layerslider-maintenance-preview-'.time()) ?>" target="_blank" class="ls-maintenance-preview-button ls--addon-published-only ls--button ls--small ls--bg-lightgray ls--white ls--ml-2">
			<?= __('Preview', 'LayerSlider') ?>
		</a>
	</ls-p>

</lse-b>