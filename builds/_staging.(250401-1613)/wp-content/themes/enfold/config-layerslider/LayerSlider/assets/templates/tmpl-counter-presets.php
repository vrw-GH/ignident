<?php defined( 'LS_ROOT_FILE' ) || exit; ?>
<lse-b class="lse-dn">

	<lse-b id="tmpl-counter-presets-left-sidebar">

		<kmw-navigation class="km-tabs-list" data-disable-auto-rename>

		</kmw-navigation>

	</lse-b>

	<lse-b id="tmpl-counter-presets-right-sidebar" class="lse-modal-sidebar">

		<lse-grid class="lse-form-elements lse-floating-window-theme">

			<lse-col class="lse-full lse-counter-theme-row">
				<lse-ib>
					<lse-text>
						<?= __('Theme', 'LayerSlider') ?>
					</lse-text>
				</lse-ib>
				<lse-ib>
					<lse-button-group class="lse-counter-theme-controls lse-max-one lse-min-one lse-toggle-all">
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
					<lse-text><?= __('Size', 'LayerSlider') ?></lse-text>
				</lse-ib>
				<lse-ib class="lse-range-inputs lse-2-1">
					<input value="5" type="range" name="" data-prop="counterSize" step="0.1" min="1" max="20" class="lse-small" data-default="5" placeholder="5">
					<input value="5" type="number" name="counterSize" data-prop="counterSize" step="0.1" min="1" max="10" data-default="5" placeholder="5">
				</lse-ib>
			</lse-col>

			<lse-separator></lse-separator>

			<lse-col class="lse-full">
				<lse-ib>
					<lse-text><?= __('Starting Number', 'LayerSlider') ?></lse-text>
				</lse-ib>
				<lse-ib class="lse-jcc">
					<?php lsGetInput( $lsDefaults['layers']['counterStart'] ) ?>
				</lse-ib>
			</lse-col>
			<lse-col class="lse-full">
				<lse-ib>
					<lse-text><?= __('Ending Number', 'LayerSlider') ?></lse-text>
				</lse-ib>
				<lse-ib class="lse-jcc">
					<?php lsGetInput( $lsDefaults['layers']['counterEnd'] ) ?>
				</lse-ib>
			</lse-col>

			<lse-separator></lse-separator>

			<lse-col class="lse-full">
				<lse-ib>
					<lse-text><?= __('Decimal Places', 'LayerSlider') ?></lse-text>
				</lse-ib>
				<lse-ib class="lse-jcc">
					<?php lsGetInput( $lsDefaults['layers']['counterDecimals'] ) ?>
				</lse-ib>
			</lse-col>

			<lse-col class="lse-full">
				<lse-ib>
					<lse-text><?= __('Decimal Separator', 'LayerSlider') ?></lse-text>
				</lse-ib>
				<lse-ib class="lse-jcc">
					<lse-fe-wrapper class="lse-select">
						<?php lsGetSelect( $lsDefaults['layers']['counterDecimalSeparator'] ) ?>
					</lse-fe-wrapper>
				</lse-ib>
			</lse-col>

			<lse-col class="lse-full">
				<lse-ib>
					<lse-text><?= __('Thousand Separator', 'LayerSlider') ?></lse-text>
				</lse-ib>
				<lse-ib class="lse-jcc">
					<lse-fe-wrapper class="lse-select">
						<?php lsGetSelect( $lsDefaults['layers']['counterThousandsSeparator'] ) ?>
					</lse-fe-wrapper>
				</lse-ib>
			</lse-col>

			<lse-col class="lse-2-1">
				<lse-ib>
					<lse-text><?= __('Leading Zeros', 'LayerSlider') ?></lse-text>
				</lse-ib>
				<lse-ib class="lse-jcc">
					<?php lsGetCheckbox( $lsDefaults['layers']['counterLeadingZeros'] ) ?>
				</lse-ib>
			</lse-col>

			<lse-separator></lse-separator>

			<lse-col class="lse-full">
				<lse-ib>
					<lse-text><?= __('Animation Type', 'LayerSlider') ?></lse-text>
				</lse-ib>
				<lse-ib class="lse-jcc">
					<lse-fe-wrapper class="lse-select">
						<?php lsGetSelect( $lsDefaults['layers']['counterAnimationType']) ?>
					</lse-fe-wrapper>
				</lse-ib>
			</lse-col>

			<lse-col class="lse-full lse-show-on-time-based-only">
				<lse-ib>
					<lse-text><?= __('Easing', 'LayerSlider') ?></lse-text>
				</lse-ib>
				<lse-ib class="lse-jcc">
					<lse-fe-wrapper class="lse-select">
						<?php lsGetSelect( $lsDefaults['layers']['counterEasing'], null, [
							'options' 	=> $lsDefaults['easings']
						]) ?>
					</lse-fe-wrapper>
				</lse-ib>
			</lse-col>

			<lse-col class="lse-full lse-show-on-time-based-only">
				<lse-ib>
					<lse-text><?= __('Duration', 'LayerSlider') ?></lse-text>
				</lse-ib>
				<lse-ib class="lse-jcc">
					<?php lsGetInput( $lsDefaults['layers']['counterDuration']) ?>
					<lse-unit>ms</lse-unit>
				</lse-ib>
			</lse-col>

			<lse-col class="lse-full lse-show-on-step-based-only">
				<lse-ib>
					<lse-text><?= __('Step', 'LayerSlider') ?></lse-text>
				</lse-ib>
				<lse-ib class="lse-jcc">
					<?php lsGetInput( $lsDefaults['layers']['counterStep'], null, [
						'options' 	=> $lsDefaults['easings']
					]) ?>
				</lse-ib>
			</lse-col>

			<lse-col class="lse-full lse-show-on-step-based-only">
				<lse-ib>
					<lse-text><?= __('Step Delay', 'LayerSlider') ?></lse-text>
				</lse-ib>
				<lse-ib class="lse-jcc">
					<?php lsGetInput( $lsDefaults['layers']['counterStepDelay'], null, [
						'options' 	=> $lsDefaults['easings']
					]) ?>
					<lse-unit>ms</lse-unit>
				</lse-ib>
			</lse-col>

		</lse-grid>

		<lse-b id="lse-counter-sidebar-bottom" class="lse-modal-sidebar-bottom">
			<lse-button class="lse-counter-modal-insert <?= LS_Config::isActivatedSite() ? '' : 'lse-premium-lock' ?>">
				<?php if( ! LS_Config::isActivatedSite() ) : ?>
				<?= lsGetSVGIcon('lock', false, ['class' => 'lse-it-fix'] ) ?>
				<?php endif ?>
				<lse-text><?= __('Insert Counter', 'LayerSlider') ?></lse-text>
			</lse-button>
			<lse-p class="lse-counter-modal-advice"><?= __('You can further customize your counter once it’s inserted into the editor.', 'LayerSlider') ?></lse-p>
		</lse-b>

	</lse-b>

	<lse-b id="tmpl-counter-presets">

		<lse-b class="lse-counter-presets-content">
			<lse-b id="lse-counter-presets-preview-area"></lse-b>
		</lse-b>

	</lse-b>

</lse-b>