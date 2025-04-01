<?php defined( 'LS_ROOT_FILE' ) || exit; ?>
<lse-b class="lse-dn">

	<lse-b id="tmpl-countdown-presets-left-sidebar">

		<kmw-navigation class="km-tabs-list" data-disable-auto-rename>

		</kmw-navigation>

	</lse-b>

	<lse-b id="tmpl-countdown-presets-right-sidebar" class="lse-modal-sidebar">

		<lse-grid class="lse-form-elements lse-floating-window-theme">

			<lse-col class="lse-full lse-dn">
				<lse-ib>
					<lse-text><?= __('Type', 'LayerSlider') ?></lse-text>
				</lse-ib>
				<lse-ib class="lse-jcc">
					<lse-fe-wrapper class="lse-select">
						<?php lsGetSelect( $lsDefaults['layers']['countdownType'] ) ?>
					</lse-fe-wrapper>
				</lse-ib>
			</lse-col>

			<lse-col class="lse-full">
				<lse-ib>
					<lse-text><?= __('Due Date', 'LayerSlider') ?></lse-text>
				</lse-ib>
				<lse-ib class="lse-jcc">
					<?php lsGetInput( $lsDefaults['layers']['countdownDueDate'], null, [
						'class' => 'lse-datepicker-input',
						'data-datepicker-classes' => 'lse-datepicker-floating',
						'data-lse-update-data-exclude' => 1
					] ) ?>
				</lse-ib>
			</lse-col>

			<lse-col class="lse-full">
				<lse-ib>
					<lse-text><?= __('Repeat', 'LayerSlider') ?></lse-text>
				</lse-ib>
				<lse-ib class="lse-jcc">
					<lse-fe-wrapper class="lse-select">
						<?php lsGetSelect( $lsDefaults['layers']['countdownRepeat'] ) ?>
					</lse-fe-wrapper>
				</lse-ib>
			</lse-col>

			<lse-col class="lse-full">
				<lse-ib>
					<lse-text>
						<?= __('Components', 'LayerSlider') ?>
					</lse-text>
				</lse-ib>
				<lse-ib>
					<lse-button-group class="lse-countdown-component-controls lse-min-one lse-toggle-all">
						<lse-button class="lse-active" data-component="days">
							<lse-text><?= __('D', 'LayerSlider') ?></lse-text>
						</lse-button>
						<lse-button class="lse-active" data-component="hours">
							<lse-text><?= __('H', 'LayerSlider') ?></lse-text>
						</lse-button>
						<lse-button class="lse-active" data-component="minutes">
							<lse-text><?= __('M', 'LayerSlider') ?></lse-text>
						</lse-button>
						<lse-button class="lse-active" data-component="seconds">
							<lse-text><?= __('S', 'LayerSlider') ?></lse-text>
						</lse-button>
					</lse-button-group>
				</lse-ib>
			</lse-col>

			<lse-col class="lse-full">
				<lse-ib>
					<lse-text>
						<?= __('Leading Zeros', 'LayerSlider') ?>
					</lse-text>
				</lse-ib>
				<lse-ib>
					<lse-button-group class="lse-countdown-leading-zeros-controls lse-toggle-all">
						<lse-button class="" data-component="days">
							<lse-text><?= __('D', 'LayerSlider') ?></lse-text>
						</lse-button>
						<lse-button class="lse-active" data-component="hours">
							<lse-text><?= __('H', 'LayerSlider') ?></lse-text>
						</lse-button>
						<lse-button class="lse-active" data-component="minutes">
							<lse-text><?= __('M', 'LayerSlider') ?></lse-text>
						</lse-button>
						<lse-button class="lse-active" data-component="seconds">
							<lse-text><?= __('S', 'LayerSlider') ?></lse-text>
						</lse-button>
					</lse-button-group>
				</lse-ib>
			</lse-col>

			<lse-col class="lse-full lse-countdown-theme-row">
				<lse-ib>
					<lse-text>
						<?= __('Theme', 'LayerSlider') ?>
					</lse-text>
				</lse-ib>
				<lse-ib>
					<lse-button-group class="lse-countdown-theme-controls lse-max-one lse-min-one lse-toggle-all">
						<lse-button class="lse-active" data-theme="light">
							<lse-text><?= __('Light', 'LayerSlider') ?></lse-text>
						</lse-button>
						<lse-button data-theme="dark">
							<lse-text><?= __('Dark', 'LayerSlider') ?></lse-text>
						</lse-button>

					</lse-button-group>
				</lse-ib>
			</lse-col>

			<lse-col class="lse-full">
				<lse-ib>
					<lse-text>
						<?= __('Orientation', 'LayerSlider') ?>
					</lse-text>
				</lse-ib>
				<lse-ib>
					<lse-button-group class="lse-countdown-orientation-controls lse-max-one lse-min-one lse-toggle-all">
						<lse-button class="lse-active" data-orientation="horizontal">
							<lse-text><?= __('Horizontal', 'LayerSlider') ?></lse-text>
						</lse-button>
						<lse-button class="" data-orientation="vertical">
							<lse-text><?= __('Vertical', 'LayerSlider') ?></lse-text>
						</lse-button>
					</lse-button-group>
				</lse-ib>
			</lse-col>

			<lse-col class="lse-full">
				<lse-ib>
					<lse-text><?= __('Spacing', 'LayerSlider') ?></lse-text>
				</lse-ib>
				<lse-ib class="lse-range-inputs lse-2-1">
					<input value="1" type="range" name="" data-prop="countdownSpacing" step="0.1" min="-5" max="20" class="lse-small" data-default="1" placeholder="1">
					<input value="1" type="number" name="countdownSpacing" data-prop="countdownSpacing" step="0.1" min="-5" max="20" data-default="1" placeholder="1">
				</lse-ib>
			</lse-col>

			<lse-col class="lse-full">
				<lse-ib>
					<lse-text><?= __('Size', 'LayerSlider') ?></lse-text>
				</lse-ib>
				<lse-ib class="lse-range-inputs lse-2-1">
					<input value="5" type="range" name="" data-prop="countdownSize" step="0.1" min="1" max="10" class="lse-small" data-default="5" placeholder="5">
					<input value="5" type="number" name="countdownSize" data-prop="countdownSize" step="0.1" min="1" max="10" data-default="5" placeholder="5">
				</lse-ib>
			</lse-col>

		</lse-grid>

		<lse-b id="lse-countdown-sidebar-bottom" class="lse-modal-sidebar-bottom">
			<lse-button class="lse-countdown-modal-insert <?= LS_Config::isActivatedSite() ? '' : 'lse-premium-lock' ?>">
				<?php if( ! LS_Config::isActivatedSite() ) : ?>
				<?= lsGetSVGIcon('lock', false, ['class' => 'lse-it-fix'] ) ?>
				<?php endif ?>
				<lse-text><?= __('Insert Countdown', 'LayerSlider') ?></lse-text>
			</lse-button>
			<lse-p class="lse-countdown-modal-advice"><?= __('You can further customize your countdown once it’s inserted into the editor.', 'LayerSlider') ?></lse-p>
		</lse-b>

	</lse-b>

	<lse-b id="tmpl-countdown-presets">

		<lse-b class="lse-countdown-presets-content">
			<lse-b id="lse-countdown-presets-preview-area"></lse-b>
		</lse-b>

	</lse-b>

</lse-b>