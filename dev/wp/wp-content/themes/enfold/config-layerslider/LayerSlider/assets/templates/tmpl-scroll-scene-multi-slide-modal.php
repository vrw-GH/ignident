<?php defined( 'LS_ROOT_FILE' ) || exit; ?>

<lse-b class="lse-dn">

	<lse-b id="scroll-scene-modal-sidebar">

		<lse-b class="kmw-sidebar-title">
			<?= __('Using Scroll Scenes', 'LayerSlider') ?>
		</lse-b>

		<kmw-navigation class="km-tabs-list" data-target="#lse-scroll-scene-modal-tabs">

			<kmw-menuitem class="kmw-active">
				<?= lsGetSVGIcon('image-landscape', 'regular', false, 'kmw-icon') ?>
				<kmw-menutext><?= __('With Single Slide', 'LayerSlider') ?></kmw-menutext>
			</kmw-menuitem>

			<kmw-menuitem>
				<?= lsGetSVGIcon('image-stack', false, false, 'kmw-icon') ?>
				<kmw-menutext><?= __('With Multiple Slides', 'LayerSlider') ?></kmw-menutext>
			</kmw-menuitem>

		</kmw-navigation>

	</lse-b>

	<lse-b id="scroll-scene-modal-content" class="lse-common-modal-style">

		<kmw-h1 class="kmw-modal-title">
			<?= __('with Single Slide (default)', 'LayerSlider') ?>
		</kmw-h1>

		<lse-b id="lse-scroll-scene-modal-tabs" class="km-tabs-content">

			<lse-b class="kmw-active">

				<lse-b><?= __('Scroll Scenes can animate only a single slide at a time. There are, however, options for working with multiple slides if your project needs more complex setups.', 'LayerSlider') ?></lse-b>
				<lse-b><?= __('By default, due to this restriction, a Scroll Scene embeds just the first published slide from the project and animates it using the scene settings configured in Project Settings → Layout. You can choose which slide this should be by using the Start With Slide option under Project Settings → Slideshow.', 'LayerSlider') ?></lse-b>
				<lse-anim-screen>
					<lse-anim-scene>
						<lse-anim-viewport class="lse-anim-scroll-scene-single-slide">
							<lse-anim-block>
								<lse-anim-layer>
									<?= __('SINGLE SLIDE', 'LayerSlider') ?>
								</lse-anim-layer>
							</lse-anim-block>
						</lse-anim-viewport>
					</lse-anim-scene>
				</lse-anim-screen>

				<lse-b class="lse-tac">
					<lse-button data-multi-embed="false">
						<lse-text>
							<?= __('I prefer this option', 'LayerSlider') ?>
						</lse-text>
					</lse-button>
				</lse-b>
				<lse-ib class="lse-font-s lse-lh-15"><?= __('You can change this option at any time in Project Settings → Layout, after enabling Advanced Settings, by using the Create Per-Slide Embeds setting.', 'LayerSlider') ?></lse-ib>


			</lse-b>

			<lse-b>

				<?= __('You can also use a mode where all published slides are automatically embedded one after another, so each slide behaves like its own scroll-animated section. In this mode, embedding the project to a page outputs all slides in order, similar to inserting multiple separate projects but without manually adding them one by one. This mode also respects the Slideshow → Start With Slide setting, so if you choose a slide other than the first one, the earlier slides will be skipped and will not be embedded.', 'LayerSlider') ?>
				<lse-anim-screen>
					<lse-anim-scene>
						<lse-anim-viewport class="lse-anim-scroll-scene-multi-slides">
							<lse-anim-block>
								<lse-anim-layer>
									<?= __('FIRST SLIDE', 'LayerSlider') ?>
								</lse-anim-layer>
							</lse-anim-block>
							<lse-anim-block>
								<lse-anim-layer>
									<?= __('SECOND SLIDE', 'LayerSlider') ?>
								</lse-anim-layer>
							</lse-anim-block>
						</lse-anim-viewport>
					</lse-anim-scene>
				</lse-anim-screen>

				<lse-b class="lse-tac">
					<lse-button data-multi-embed="true">
						<lse-text>
							<?= __('I prefer this option', 'LayerSlider') ?>
						</lse-text>
					</lse-button>
				</lse-b>
				<lse-ib class="lse-font-s lse-lh-15"><?= __('You can change this option at any time in Project Settings → Layout, after enabling Advanced Settings, by using the Create Per-Slide Embeds setting.', 'LayerSlider') ?></lse-ib>

			</lse-b>
		</lse-b>
	</lse-b>
</lse-b>