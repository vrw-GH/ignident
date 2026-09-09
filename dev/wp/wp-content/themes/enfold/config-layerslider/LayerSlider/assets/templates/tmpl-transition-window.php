<?php

defined( 'LS_ROOT_FILE' ) || exit;

wp_localize_script('ls-project-editor', 'LS_SlideFXNames', [

	'liquidmorph' => [
		'swirly'        => __( 'Swirly', 'LayerSlider' ),
		'soft'          => __( 'Soft', 'LayerSlider' ),
		'reveal'        => __( 'Reveal', 'LayerSlider' ),
		'intense'       => __( 'Intense', 'LayerSlider' ),
		'psychedelic'   => __( 'Psychedelic', 'LayerSlider' ),
		'texturedGlass' => __( 'Textured Glass', 'LayerSlider' ),
		'inkBleed'      => __( 'Ink Bleed', 'LayerSlider' ),
	],

	'storm' => [
		'windGust'     => __( 'Wind Gust', 'LayerSlider' ),
		'wavy'         => __( 'Wavy', 'LayerSlider' ),
		'signalGlitch' => __( 'Signal Glitch', 'LayerSlider' ),
		'dreamy'       => __( 'Dreamy', 'LayerSlider' ),
		'fragments'    => __( 'Fragments', 'LayerSlider' ),
	],

	'colorswipe' => [
		'sharp' => __( 'Sharp', 'LayerSlider' ),
		'sink'  => __( 'Sink', 'LayerSlider' ),
		'fall'  => __( 'Fall', 'LayerSlider' ),
		'warp'  => __( 'Warp', 'LayerSlider' ),
		'smoke' => __( 'Smoke', 'LayerSlider' ),
	],

	'displacementripple' => [
		'tender'   => __( 'Tender', 'LayerSlider' ),
		'ripple'   => __( 'Ripple', 'LayerSlider' ),
		'metallic' => __( 'Metallic', 'LayerSlider' ),
		'comb'     => __( 'Comb', 'LayerSlider' ),
	],

	'spiral' => [
		'quarterTurn' => __( 'Quarter Turn', 'LayerSlider' ),
		'fastZoom'    => __( 'Fast & Zoom', 'LayerSlider' ),
		'slowWobble'  => __( 'Slow & Wobble', 'LayerSlider' ),
		'vortex'      => __( 'Vortex', 'LayerSlider' ),
	],

	'vortexdistort' => [
		'zigzag'    => __( 'Zigzag', 'LayerSlider' ),
		'flowers'   => __( 'Flowers', 'LayerSlider' ),
		'swirlJump' => __( 'Swirl Jump', 'LayerSlider' ),
	],

	'glitch' => [
		'fracture'       => __( 'Fracture', 'LayerSlider' ),
		'fastSmooth'     => __( 'Fast & Smooth', 'LayerSlider' ),
		'verticalShred'  => __( 'Vertical Shred', 'LayerSlider' ),
		'chromaticStorm' => __( 'Chromatic Storm', 'LayerSlider' ),
		'morph'          => __( 'Morph', 'LayerSlider' ),
	],

	'liquidfade' => [
		'riverFlow'   => __( 'River Flow', 'LayerSlider' ),
		'softRipples' => __( 'Soft Ripples', 'LayerSlider' ),
		'slowWarp'    => __( 'Slow Warp', 'LayerSlider' ),
	],

	'circularreveal' => [
		'bubbleReveal'   => __( 'Bubble Reveal', 'LayerSlider' ),
		'dizzyZoom'      => __( 'Dizzy Zoom', 'LayerSlider' ),
		'circleCollapse' => __( 'Circle Collapse', 'LayerSlider' ),
	],

	'particledissolve' => [
		'tiles'     => __( 'Tiles', 'LayerSlider' ),
		'windblow'  => __( 'Windblow', 'LayerSlider' ),
		'pixelRain' => __( 'Pixel Rain', 'LayerSlider' ),
		'rowDrift'  => __( 'Row Drift', 'LayerSlider' ),
		'mosaic'    => __( 'Mosaic', 'LayerSlider' ),
		'quicksand' => __( 'Quicksand', 'LayerSlider' ),
	],

	'bloom' => [
		'burstEcho'    => __( 'Burst Echo', 'LayerSlider' ),
		'motionTrail'  => __( 'Motion Trail', 'LayerSlider' ),
		'darkCollapse' => __( 'Dark Collapse', 'LayerSlider' ),
	],

	'glow' => [
		'radiantBurst' => __( 'Radiant Burst', 'LayerSlider' ),
		'lightStreak'  => __( 'Light Streak', 'LayerSlider' ),
		'dreamHaze'    => __( 'Dream Haze', 'LayerSlider' ),
		'softFocus'    => __( 'Soft Focus', 'LayerSlider' ),
		'pixelated'	   => __( 'Pixelated', 'LayerSlider' ),
	],

	'origami' => [
		'darkFold'   => __( 'Dark Fold', 'LayerSlider' ),
		'brightFold' => __( 'Bright Fold', 'LayerSlider' ),
		'bouncy'     => __( 'Bouncy', 'LayerSlider' ),
	],
]);
?>

<lse-b class="lse-dn">

	<lse-b id="tmpl-slide-transition-modal-sidebar">

		<lse-b class="kmw-sidebar-title">
			<?= __('Slide Transitions', 'LayerSlider') ?>
		</lse-b>
		<lse-b id="transition-modal-sidebar">
			<kmw-navigation class="km-tabs-list" data-target="#lse-transitions-list">

				<kmw-menutitle>
					<kmw-menutext><?= __('Built-in', 'LayerSlider') ?></kmw-menutext>
				</kmw-menutitle>

				<kmw-menuitem data-tr-type="2d_transitions" class="kmw-active">
					<?= lsGetSVGIcon('grid-2', false, false, 'kmw-icon') ?>
					<kmw-menutext><?= __('2D Transitions', 'LayerSlider') ?></kmw-menutext>
					<lse-badge></lse-badge>
				</kmw-menuitem>
				<kmw-menuitem data-tr-type="3d_transitions">
					<?= lsGetSVGIcon('cube', false, false, 'kmw-icon') ?>
					<kmw-menutext><?= __('3D Transitions', 'LayerSlider') ?></kmw-menutext>
					<lse-badge></lse-badge>
				</kmw-menuitem>
				<kmw-menuitem data-tr-type="sfxTransitions" class="lse-transitions-special-effects" data-name="specialeffects" data-rename-from-sub-sidebar>
					<?= lsGetSVGIcon('star-christmas', 'duotone', false, 'kmw-icon') ?>
					<kmw-menutext><?= __('Special Effects', 'LayerSlider') ?></kmw-menutext>
					<lse-badge></lse-badge>
				</kmw-menuitem>

				<kmw-menutitle>
					<kmw-menutext><?= __('User Transitions', 'LayerSlider') ?></kmw-menutext>
				</kmw-menutitle>

				<kmw-menuitem data-tr-type="custom_2d_transitions">
					<?= lsGetSVGIcon('grid-2', false, false, 'kmw-icon') ?>
					<kmw-menutext><?= __('Custom 2D', 'LayerSlider') ?></kmw-menutext>
					<lse-badge></lse-badge>
				</kmw-menuitem>
				<kmw-menuitem data-tr-type="custom_3d_transitions">
					<?= lsGetSVGIcon('cube', false, false, 'kmw-icon') ?>
					<kmw-menutext><?= __('Custom 3D', 'LayerSlider') ?></kmw-menutext>
					<lse-badge></lse-badge>
				</kmw-menuitem>
			</kmw-navigation>
		</lse-b>

	</lse-b>

	<lse-b id="tmpl-slide-transition-modal-sidebar-se">

		<lse-b class="kmw-sidebar-title">
			<?= __('Special Effects', 'LayerSlider') ?>
		</lse-b>
		<kmw-navigation id="lse-special-transitions-nav" class="km-tabs-list" data-target="#lse-special-transitions" data-disable-data-name-change>

			<kmw-menuitem class="kmw-active">
				<kmw-menutext><?= __('Liquid Morph', 'LayerSlider') ?></kmw-menutext>
				<lse-badge></lse-badge>
			</kmw-menuitem>

			<kmw-menuitem>
				<kmw-menutext><?= __('Storm', 'LayerSlider') ?></kmw-menutext>
				<lse-badge></lse-badge>
			</kmw-menuitem>

			<kmw-menuitem>
				<kmw-menutext><?= __('Color Swipe', 'LayerSlider') ?></kmw-menutext>
				<lse-badge></lse-badge>
			</kmw-menuitem>

			<kmw-menuitem>
				<kmw-menutext><?= __('Displacement Ripple', 'LayerSlider') ?></kmw-menutext>
				<lse-badge></lse-badge>
			</kmw-menuitem>

			<kmw-menuitem>
				<kmw-menutext><?= __('Spiral', 'LayerSlider') ?></kmw-menutext>
				<lse-badge></lse-badge>
			</kmw-menuitem>

			<kmw-menuitem>
				<kmw-menutext><?= __('Vortex Distort', 'LayerSlider') ?></kmw-menutext>
				<lse-badge></lse-badge>
			</kmw-menuitem>

			<kmw-menuitem>
				<kmw-menutext><?= __('Glitch', 'LayerSlider') ?></kmw-menutext>
				<lse-badge></lse-badge>
			</kmw-menuitem>

			<kmw-menuitem>
				<kmw-menutext><?= __('Liquid Fade', 'LayerSlider') ?></kmw-menutext>
				<lse-badge></lse-badge>
			</kmw-menuitem>

			<kmw-menuitem>
				<kmw-menutext><?= __('Circular Reveal', 'LayerSlider') ?></kmw-menutext>
				<lse-badge></lse-badge>
			</kmw-menuitem>

			<kmw-menuitem>
				<kmw-menutext><?= __('Particle Dissolve', 'LayerSlider') ?></kmw-menutext>
				<lse-badge></lse-badge>
			</kmw-menuitem>

			<kmw-menuitem>
				<kmw-menutext><?= __('Bloom', 'LayerSlider') ?></kmw-menutext>
				<lse-badge></lse-badge>
			</kmw-menuitem>

			<kmw-menuitem>
				<kmw-menutext><?= __('Glow', 'LayerSlider') ?></kmw-menutext>
				<lse-badge></lse-badge>
			</kmw-menuitem>

			<kmw-menuitem>
				<kmw-menutext><?= __('Origami', 'LayerSlider') ?></kmw-menutext>
				<lse-badge></lse-badge>
			</kmw-menuitem>

		</kmw-navigation>

	</lse-b>

	<lse-b id="tmpl-slide-transition-modal-content">
		<div id="lse-transition-window">

			<lse-b class="kmw-modal-toolbar" style="margin-top: 10px;">
				<lse-button id="lse-transitions-modal-apply-button"><?= __('Apply to other slides', 'LayerSlider') ?></lse-button>
				<lse-button id="lse-transitions-modal-select-button"><?= __('Select all', 'LayerSlider') ?></lse-button>
			</lse-b>

			<kmw-h1 class="kmw-modal-title">
				<?= __('2D Transitions', 'LayerSlider') ?>
			</kmw-h1>

			<lse-b id="lse-transitions-list" class="km-tabs-content">

				<!-- 2D -->
				<lse-grid class="lse-transitions-section kmw-active" data-tr-type="2d_transitions" >
					<lse-row></lse-row>
				</lse-grid>

				<!-- 3D -->
				<lse-grid class="lse-transitions-section" data-tr-type="3d_transitions">
					<lse-row></lse-row>
				</lse-grid>

				<!-- Special Effects -->
				<lse-b class="lse-transitions-section" data-tr-type="sfxTransitions">

					<div id="lse-slidefx-preview"></div>

					<lse-b id="lse-special-transitions" class="km-tabs-content lse-form-elements">

						<lse-b class="kmw-active" data-name="liquidmorph">
							<lse-h3><?= __('Effects', 'LayerSlider') ?></lse-h3>
							<lse-button-group class="lse-max-one lse-min-one lse-toggle-all lse-special-transition-presets">
								<lse-button class="lse-add-new-effect lse-unselectable lse-it-fix" data-tt=".tt-custom-sfx-preset" data-tt-de="0"><?= lsGetSVGIcon('plus') ?></lse-button>
							</lse-button-group>

							<lse-b class="lse-effect-options">
								<lse-h3><?= __('Options', 'LayerSlider') ?></lse-h3>
								<lse-grid class="lse-form-elements">
									<lse-row>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Intensity', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['liquidmorph']['amplitude'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['liquidmorph']['amplitude'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Wave Speed', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['liquidmorph']['speed'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['liquidmorph']['speed'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Blot Amount', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['liquidmorph']['scale'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['liquidmorph']['scale'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Blot Softness', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['liquidmorph']['edge'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['liquidmorph']['edge'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Swirl', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['liquidmorph']['swirl'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['liquidmorph']['swirl'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib>
												<lse-text><?= __('Swirl Direction', 'LayerSlider') ?></lse-text>
											</lse-ib>
											<lse-ib>
												<lse-fe-wrapper class="lse-select">
													<?php lsGetSelect( $lsDefaults['slidefxtr']['liquidmorph']['direction'] ) ?>
												</lse-fe-wrapper>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Chromatic', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['liquidmorph']['chromatic'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['liquidmorph']['chromatic'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Auto Direction', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib>
												<?php lsGetCheckbox( $lsDefaults['slidefxtr']['liquidmorph']['autoDirection'], null, [], false, [
													'data-tt' => '.tt-sfx-auto-dir',
													'data-tt-de' => 0.1
												]) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full"></lse-col>
									</lse-row>
								</lse-grid>
							</lse-b>

							<lse-b class="lse-timing-options">
								<lse-h3><?= __('Timing', 'LayerSlider') ?></lse-h3>
								<lse-grid class="lse-form-elements">
									<lse-row>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Duration', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['liquidmorph']['duration'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<lse-fe-wrapper class="lse-smart-help" data-smart-help="duration" data-smart-help-title="<?= __('Duration', 'LayerSlider') ?>">
													<?php lsGetInput( $lsDefaults['slidefxtr']['liquidmorph']['duration'] ) ?>
												</lse-fe-wrapper>
												<lse-unit>ms</lse-unit>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Easing', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-jcc">
												<lse-fe-wrapper class="lse-select lse-smart-help" data-smart-help="easing" data-smart-help-title="<?= __('Easing', 'LayerSlider') ?>">
													<?php lsGetSelect( $lsDefaults['slidefxtr']['liquidmorph']['ease'], null, [
														'options'   => $lsDefaults['sfxeasings']
													]) ?>
												</lse-fe-wrapper>
											</lse-ib>
										</lse-col>
									</lse-row>
								</lse-grid>
							</lse-b>
						</lse-b>

						<lse-b data-name="storm">
							<lse-h3><?= __('Effects', 'LayerSlider') ?></lse-h3>
							<lse-button-group class="lse-max-one lse-min-one lse-toggle-all lse-special-transition-presets">
								<lse-button class="lse-add-new-effect lse-unselectable lse-it-fix" data-tt=".tt-custom-sfx-preset" data-tt-de="0"><?= lsGetSVGIcon('plus') ?></lse-button>
							</lse-button-group>

							<lse-b class="lse-effect-options">
								<lse-h3><?= __('Options', 'LayerSlider') ?></lse-h3>
								<lse-grid class="lse-form-elements">
									<lse-row>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Fragments', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['storm']['fragments'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['storm']['fragments'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Fragment Intensity', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['storm']['intensity'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['storm']['intensity'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Wave Strength', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['storm']['waveStrength'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['storm']['waveStrength'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Wave Frequency', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['storm']['waveFrequency'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['storm']['waveFrequency'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Blur', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['storm']['blur'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['storm']['blur'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full"></lse-col>
									</lse-row>
								</lse-grid>
							</lse-b>

							<lse-b class="lse-timing-options">
								<lse-h3><?= __('Timing', 'LayerSlider') ?></lse-h3>
								<lse-grid class="lse-form-elements">
									<lse-row>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Duration', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['storm']['duration'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<lse-fe-wrapper class="lse-smart-help" data-smart-help="duration" data-smart-help-title="<?= __('Duration', 'LayerSlider') ?>">
													<?php lsGetInput( $lsDefaults['slidefxtr']['storm']['duration'] ) ?>
												</lse-fe-wrapper>
												<lse-unit>ms</lse-unit>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Easing', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-jcc">
												<lse-fe-wrapper class="lse-select lse-smart-help" data-smart-help="easing" data-smart-help-title="<?= __('Easing', 'LayerSlider') ?>">
													<?php lsGetSelect( $lsDefaults['slidefxtr']['storm']['ease'], null, [
														'options' 	=> $lsDefaults['sfxeasings']
													]) ?>
												</lse-fe-wrapper>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full"></lse-col>
									</lse-row>
								</lse-grid>
							</lse-b>
						</lse-b>

						<lse-b data-name="colorswipe">
							<lse-h3><?= __('Effects', 'LayerSlider') ?></lse-h3>
							<lse-button-group class="lse-max-one lse-min-one lse-toggle-all lse-special-transition-presets">
								<lse-button class="lse-add-new-effect lse-unselectable lse-it-fix" data-tt=".tt-custom-sfx-preset" data-tt-de="0"><?= lsGetSVGIcon('plus') ?></lse-button>
							</lse-button-group>

							<lse-b class="lse-effect-options">
								<lse-h3><?= __('Options', 'LayerSlider') ?></lse-h3>
								<lse-grid class="lse-form-elements">
									<lse-row>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Intensity', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['colorswipe']['intensity'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['colorswipe']['intensity'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Blur', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['colorswipe']['blur'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['colorswipe']['blur'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Direction', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib>
												<lse-fe-wrapper class="lse-select"><?php lsGetSelect( $lsDefaults['slidefxtr']['colorswipe']['direction'] ) ?></lse-fe-wrapper>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Auto Direction', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib>
												<?php lsGetCheckbox( $lsDefaults['slidefxtr']['colorswipe']['autoDirection'], null, [], false, [
													'data-tt' => '.tt-sfx-auto-dir',
													'data-tt-de' => 0.1
												]) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full"></lse-col>
									</lse-row>
								</lse-grid>
							</lse-b>

							<lse-b class="lse-timing-options">
								<lse-h3><?= __('Timing', 'LayerSlider') ?></lse-h3>
								<lse-grid class="lse-form-elements">
									<lse-row>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Duration', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['colorswipe']['duration'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<lse-fe-wrapper class="lse-smart-help" data-smart-help="duration" data-smart-help-title="<?= __('Duration', 'LayerSlider') ?>">
													<?php lsGetInput( $lsDefaults['slidefxtr']['colorswipe']['duration'] ) ?>
												</lse-fe-wrapper>
												<lse-unit>ms</lse-unit>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Easing', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-jcc">
												<lse-fe-wrapper class="lse-select lse-smart-help" data-smart-help="easing" data-smart-help-title="<?= __('Easing', 'LayerSlider') ?>">
													<?php lsGetSelect( $lsDefaults['slidefxtr']['colorswipe']['ease'], null, [
														'options' 	=> $lsDefaults['sfxeasings']
													]) ?>
												</lse-fe-wrapper>
											</lse-ib>
										</lse-col>
									</lse-row>
								</lse-grid>
							</lse-b>
						</lse-b>

						<lse-b data-name="displacementripple">
							<lse-h3><?= __('Effects', 'LayerSlider') ?></lse-h3>
							<lse-button-group class="lse-max-one lse-min-one lse-toggle-all lse-special-transition-presets">
								<lse-button class="lse-add-new-effect lse-unselectable lse-it-fix" data-tt=".tt-custom-sfx-preset" data-tt-de="0"><?= lsGetSVGIcon('plus') ?></lse-button>
							</lse-button-group>

							<lse-b class="lse-effect-options">
								<lse-h3><?= __('Options', 'LayerSlider') ?></lse-h3>
								<lse-grid class="lse-form-elements">
									<lse-row>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Amplitude', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['displacementripple']['rippleAmplitude'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['displacementripple']['rippleAmplitude'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Frequency', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['displacementripple']['rippleFrequency'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['displacementripple']['rippleFrequency'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Match Waves', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib>
												<?php lsGetCheckbox( $lsDefaults['slidefxtr']['displacementripple']['matchWaves'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full"></lse-col>
									</lse-row>
								</lse-grid>
							</lse-b>

							<lse-b class="lse-timing-options">
								<lse-h3><?= __('Timing', 'LayerSlider') ?></lse-h3>
								<lse-grid class="lse-form-elements">
									<lse-row>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Duration', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['displacementripple']['duration'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<lse-fe-wrapper class="lse-smart-help" data-smart-help="duration" data-smart-help-title="<?= __('Duration', 'LayerSlider') ?>">
													<?php lsGetInput( $lsDefaults['slidefxtr']['displacementripple']['duration'] ) ?>
												</lse-fe-wrapper>
												<lse-unit>ms</lse-unit>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Easing', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-jcc">
												<lse-fe-wrapper class="lse-select lse-smart-help" data-smart-help="easing" data-smart-help-title="<?= __('Easing', 'LayerSlider') ?>">
													<?php lsGetSelect( $lsDefaults['slidefxtr']['displacementripple']['ease'], null, [
														'options' 	=> $lsDefaults['sfxeasings']
													]) ?>
												</lse-fe-wrapper>
											</lse-ib>
										</lse-col>
									</lse-row>
								</lse-grid>
							</lse-b>

						</lse-b>

						<lse-b data-name="spiral">
							<lse-h3><?= __('Effects', 'LayerSlider') ?></lse-h3>
							<lse-button-group class="lse-max-one lse-min-one lse-toggle-all lse-special-transition-presets">
								<lse-button class="lse-add-new-effect lse-unselectable lse-it-fix" data-tt=".tt-custom-sfx-preset" data-tt-de="0"><?= lsGetSVGIcon('plus') ?></lse-button>
							</lse-button-group>

							<lse-b class="lse-effect-options">
								<lse-h3><?= __('Options', 'LayerSlider') ?></lse-h3>
								<lse-grid class="lse-form-elements">
									<lse-row>

										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Count', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['spiral']['count'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['spiral']['count'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Angle', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['spiral']['angle'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['spiral']['angle'] ) ?>
												<lse-unit>deg</lse-unit>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Zoom', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['spiral']['zoom'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['spiral']['zoom'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Wobble', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['spiral']['wobble'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['spiral']['wobble'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Auto Direction', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib>
												<?php lsGetCheckbox( $lsDefaults['slidefxtr']['spiral']['autoDirection'], null, [], false, [
													'data-tt' => '.tt-sfx-auto-dir',
													'data-tt-de' => 0.1
												]) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full"></lse-col>
									</lse-row>
								</lse-grid>
							</lse-b>

							<lse-b class="lse-timing-options">
								<lse-h3><?= __('Timing', 'LayerSlider') ?></lse-h3>
								<lse-grid class="lse-form-elements">
									<lse-row>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Duration', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['spiral']['duration'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<lse-fe-wrapper class="lse-smart-help" data-smart-help="duration" data-smart-help-title="<?= __('Duration', 'LayerSlider') ?>">
													<?php lsGetInput( $lsDefaults['slidefxtr']['spiral']['duration'] ) ?>
												</lse-fe-wrapper>
												<lse-unit>ms</lse-unit>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Easing', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-jcc">
												<lse-fe-wrapper class="lse-select lse-smart-help" data-smart-help="easing" data-smart-help-title="<?= __('Easing', 'LayerSlider') ?>">
													<?php lsGetSelect( $lsDefaults['slidefxtr']['spiral']['ease'], null, [
														'options' 	=> $lsDefaults['sfxeasings']
													]) ?>
												</lse-fe-wrapper>
											</lse-ib>
										</lse-col>
									</lse-row>
								</lse-grid>
							</lse-b>

						</lse-b>

						<lse-b data-name="vortexdistort">
							<lse-h3><?= __('Effects', 'LayerSlider') ?></lse-h3>
							<lse-button-group class="lse-max-one lse-min-one lse-toggle-all lse-special-transition-presets">
								<lse-button class="lse-add-new-effect lse-unselectable lse-it-fix" data-tt=".tt-custom-sfx-preset" data-tt-de="0"><?= lsGetSVGIcon('plus') ?></lse-button>
							</lse-button-group>

							<lse-b class="lse-effect-options">
								<lse-h3><?= __('Options', 'LayerSlider') ?></lse-h3>
								<lse-grid class="lse-form-elements">
									<lse-row>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Distort Mode', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib>
												<lse-fe-wrapper class="lse-select"><?php lsGetSelect( $lsDefaults['slidefxtr']['vortexdistort']['distortMode'] ) ?></lse-fe-wrapper>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Strength', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['vortexdistort']['strength'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['vortexdistort']['strength'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Frequency', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['vortexdistort']['frequency'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['vortexdistort']['frequency'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Amplitude', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['vortexdistort']['amplitude'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['vortexdistort']['amplitude'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Count', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['vortexdistort']['count'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['vortexdistort']['count'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Auto Direction', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib>
												<?php lsGetCheckbox( $lsDefaults['slidefxtr']['vortexdistort']['autoDirection'], null, [], false, [
													'data-tt' => '.tt-sfx-auto-dir',
													'data-tt-de' => 0.1
												]) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full"></lse-col>
									</lse-row>
								</lse-grid>
							</lse-b>

							<lse-b class="lse-timing-options">
								<lse-h3><?= __('Timing', 'LayerSlider') ?></lse-h3>
								<lse-grid class="lse-form-elements">
									<lse-row>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Duration', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['vortexdistort']['duration'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<lse-fe-wrapper class="lse-smart-help" data-smart-help="duration" data-smart-help-title="<?= __('Duration', 'LayerSlider') ?>">
													<?php lsGetInput( $lsDefaults['slidefxtr']['vortexdistort']['duration'] ) ?>
												</lse-fe-wrapper>
												<lse-unit>ms</lse-unit>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Easing', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-jcc">
												<lse-fe-wrapper class="lse-select lse-smart-help" data-smart-help="easing" data-smart-help-title="<?= __('Easing', 'LayerSlider') ?>">
													<?php lsGetSelect( $lsDefaults['slidefxtr']['vortexdistort']['ease'], null, [
														'options' 	=> $lsDefaults['sfxeasings']
													]) ?>
												</lse-fe-wrapper>
											</lse-ib>
										</lse-col>
									</lse-row>
								</lse-grid>
							</lse-b>
						</lse-b>

						<lse-b data-name="glitch">
							<lse-h3><?= __('Effects', 'LayerSlider') ?></lse-h3>
							<lse-button-group class="lse-max-one lse-min-one lse-toggle-all lse-special-transition-presets">
								<lse-button class="lse-add-new-effect lse-unselectable lse-it-fix" data-tt=".tt-custom-sfx-preset" data-tt-de="0"><?= lsGetSVGIcon('plus') ?></lse-button>
							</lse-button-group>

							<lse-b class="lse-effect-options">
								<lse-h3><?= __('Options', 'LayerSlider') ?></lse-h3>
								<lse-grid class="lse-form-elements">
									<lse-row>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Tile Size Min', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['glitch']['tileSizeMin'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['glitch']['tileSizeMin'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Tile Size Max', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['glitch']['tileSizeMax'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['glitch']['tileSizeMax'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib>
												<lse-text><?= __('Tile Direction', 'LayerSlider') ?></lse-text>
											</lse-ib>
											<lse-ib>
												<lse-fe-wrapper class="lse-select">
													<?php lsGetSelect( $lsDefaults['slidefxtr']['glitch']['tileDirection'] ) ?>
												</lse-fe-wrapper>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Tile Shift', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['glitch']['tileShift'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['glitch']['tileShift'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Tile Speed', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['glitch']['tileSpeed'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['glitch']['tileSpeed'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full lse-split">
											<lse-b>
												<lse-ib><lse-text><?= __('Animate Tiles', 'LayerSlider') ?></lse-text></lse-ib>
												<lse-ib>
													<?php lsGetCheckbox( $lsDefaults['slidefxtr']['glitch']['animateTiles'] ) ?>
												</lse-ib>
											</lse-b>
											<lse-b lse-tgl-t="glitch-tileyoyo" tgl-on>
												<lse-ib><lse-text><?= __('Sync Tiles', 'LayerSlider') ?></lse-text></lse-ib>
												<lse-ib>
													<?php lsGetCheckbox( $lsDefaults['slidefxtr']['glitch']['tileYoyo'] ) ?>
												</lse-ib>
											</lse-b>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib>
												<lse-text><?= __('Chromatic Direction', 'LayerSlider') ?></lse-text>
											</lse-ib>
											<lse-ib>
												<lse-fe-wrapper class="lse-select">
													<?php lsGetSelect( $lsDefaults['slidefxtr']['glitch']['splitDirection'] ) ?>
												</lse-fe-wrapper>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Chromatic Intensity', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['glitch']['splitIntensity'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['glitch']['splitIntensity'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Auto Direction', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib>
												<?php lsGetCheckbox( $lsDefaults['slidefxtr']['glitch']['autoDirection'], null, [], false, [
													'data-tt' => '.tt-sfx-auto-dir',
													'data-tt-de' => 0.1
												]) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full"></lse-col>
									</lse-row>
								</lse-grid>
							</lse-b>

							<lse-b class="lse-timing-options">
								<lse-h3><?= __('Timing', 'LayerSlider') ?></lse-h3>
								<lse-grid class="lse-form-elements">
									<lse-row>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Duration', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['glitch']['duration'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<lse-fe-wrapper class="lse-smart-help" data-smart-help="duration" data-smart-help-title="<?= __('Duration', 'LayerSlider') ?>">
													<?php lsGetInput( $lsDefaults['slidefxtr']['glitch']['duration'] ) ?>
												</lse-fe-wrapper>
												<lse-unit>ms</lse-unit>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Easing', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-jcc">
												<lse-fe-wrapper class="lse-select lse-smart-help" data-smart-help="easing" data-smart-help-title="<?= __('Easing', 'LayerSlider') ?>">
													<?php lsGetSelect( $lsDefaults['slidefxtr']['glitch']['ease'], null, [
														'options'   => $lsDefaults['sfxeasings']
													]) ?>
												</lse-fe-wrapper>
											</lse-ib>
										</lse-col>
									</lse-row>
								</lse-grid>
							</lse-b>
						</lse-b>

						<lse-b data-name="liquidfade">
							<lse-h3><?= __('Effects', 'LayerSlider') ?></lse-h3>
							<lse-button-group class="lse-max-one lse-min-one lse-toggle-all lse-special-transition-presets">
								<lse-button class="lse-add-new-effect lse-unselectable lse-it-fix" data-tt=".tt-custom-sfx-preset" data-tt-de="0"><?= lsGetSVGIcon('plus') ?></lse-button>
							</lse-button-group>

							<lse-b class="lse-effect-options">
								<lse-h3><?= __('Options', 'LayerSlider') ?></lse-h3>
								<lse-grid class="lse-form-elements">
									<lse-row>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Distortion Strength', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['liquidfade']['distortionStrength'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['liquidfade']['distortionStrength'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Wave Speed', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['liquidfade']['waveSpeed'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['liquidfade']['waveSpeed'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full"></lse-col>
									</lse-row>
								</lse-grid>
							</lse-b>

							<lse-b class="lse-timing-options">
								<lse-h3><?= __('Timing', 'LayerSlider') ?></lse-h3>
								<lse-grid class="lse-form-elements">
									<lse-row>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Duration', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['liquidfade']['duration'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<lse-fe-wrapper class="lse-smart-help" data-smart-help="duration" data-smart-help-title="<?= __('Duration', 'LayerSlider') ?>">
													<?php lsGetInput( $lsDefaults['slidefxtr']['liquidfade']['duration'] ) ?>
												</lse-fe-wrapper>
												<lse-unit>ms</lse-unit>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Easing', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-jcc">
												<lse-fe-wrapper class="lse-select lse-smart-help" data-smart-help="easing" data-smart-help-title="<?= __('Easing', 'LayerSlider') ?>">
													<?php lsGetSelect( $lsDefaults['slidefxtr']['liquidfade']['ease'], null, [
														'options' 	=> $lsDefaults['sfxeasings']
													]) ?>
												</lse-fe-wrapper>
											</lse-ib>
										</lse-col>
									</lse-row>
								</lse-grid>
							</lse-b>
						</lse-b>

						<lse-b data-name="circularreveal">
							<lse-h3><?= __('Effects', 'LayerSlider') ?></lse-h3>
							<lse-button-group class="lse-max-one lse-min-one lse-toggle-all lse-special-transition-presets">
								<lse-button class="lse-add-new-effect lse-unselectable lse-it-fix" data-tt=".tt-custom-sfx-preset" data-tt-de="0"><?= lsGetSVGIcon('plus') ?></lse-button>
							</lse-button-group>

							<lse-b class="lse-effect-options">
								<lse-h3><?= __('Options', 'LayerSlider') ?></lse-h3>
								<lse-grid class="lse-form-elements">
									<lse-row>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Circle Count', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['circularreveal']['circleCount'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['circularreveal']['circleCount'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Stagger Time', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['circularreveal']['staggerTime'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['circularreveal']['staggerTime'] ) ?>
												<lse-unit>ms</lse-unit>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Min Radius', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['circularreveal']['minRadius'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['circularreveal']['minRadius'] ) ?>
												<lse-unit>%</lse-unit>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Max Radius', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['circularreveal']['maxRadius'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['circularreveal']['maxRadius'] ) ?>
												<lse-unit>%</lse-unit>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Min Dizziness', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['circularreveal']['dizMin'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['circularreveal']['dizMin'] ) ?>
												<lse-unit>%</lse-unit>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Max Dizziness', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['circularreveal']['dizMax'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['circularreveal']['dizMax'] ) ?>
												<lse-unit>%</lse-unit>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Threshold', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['circularreveal']['threshold'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['circularreveal']['threshold'] ) ?>
												<lse-unit>%</lse-unit>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Zoom Next', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['circularreveal']['scaleNext'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['circularreveal']['scaleNext'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Reverse', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib>
												<?php lsGetCheckbox( $lsDefaults['slidefxtr']['circularreveal']['reverse'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full"></lse-col>
									</lse-row>
								</lse-grid>
							</lse-b>

							<lse-b class="lse-timing-options">
								<lse-h3><?= __('Timing', 'LayerSlider') ?></lse-h3>
								<lse-grid class="lse-form-elements">
									<lse-row>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Duration', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['circularreveal']['duration'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<lse-fe-wrapper class="lse-smart-help" data-smart-help="duration" data-smart-help-title="<?= __('Duration', 'LayerSlider') ?>">
													<?php lsGetInput( $lsDefaults['slidefxtr']['circularreveal']['duration'] ) ?>
												</lse-fe-wrapper>
												<lse-unit>ms</lse-unit>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Easing', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-jcc">
												<lse-fe-wrapper class="lse-select lse-smart-help" data-smart-help="easing" data-smart-help-title="<?= __('Easing', 'LayerSlider') ?>">
													<?php lsGetSelect( $lsDefaults['slidefxtr']['circularreveal']['ease'], null, [
														'options' 	=> $lsDefaults['sfxeasings']
													]) ?>
												</lse-fe-wrapper>
											</lse-ib>
										</lse-col>
									</lse-row>
								</lse-grid>
							</lse-b>
						</lse-b>

						<lse-b data-name="particledissolve">
							<lse-h3><?= __('Effects', 'LayerSlider') ?></lse-h3>
							<lse-button-group class="lse-max-one lse-min-one lse-toggle-all lse-special-transition-presets">
								<lse-button class="lse-add-new-effect lse-unselectable lse-it-fix" data-tt=".tt-custom-sfx-preset" data-tt-de="0"><?= lsGetSVGIcon('plus') ?></lse-button>
							</lse-button-group>

							<lse-b class="lse-effect-options">
								<lse-h3><?= __('Options', 'LayerSlider') ?></lse-h3>
								<lse-grid class="lse-form-elements">
									<lse-row>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Grid X', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['particledissolve']['gridX'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['particledissolve']['gridX'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Grid Y', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['particledissolve']['gridY'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['particledissolve']['gridY'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Rotation', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['particledissolve']['rotation'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['particledissolve']['rotation'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Edge Softness', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['particledissolve']['edgeSoft'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['particledissolve']['edgeSoft'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Spread', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['particledissolve']['spread'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['particledissolve']['spread'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Scatter', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['particledissolve']['scatter'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['particledissolve']['scatter'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Scatter Movement', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['particledissolve']['movement'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['particledissolve']['movement'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib>
												<lse-text><?= __('Scatter Direction', 'LayerSlider') ?></lse-text>
											</lse-ib>
											<lse-ib>
												<lse-fe-wrapper class="lse-select">
													<?php lsGetSelect( $lsDefaults['slidefxtr']['particledissolve']['direction'] ) ?>
												</lse-fe-wrapper>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Auto Direction', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib>
												<?php lsGetCheckbox( $lsDefaults['slidefxtr']['particledissolve']['autoDirection'], null, [], false, [
													'data-tt' => '.tt-sfx-auto-dir',
													'data-tt-de' => 0.1
												]) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full"></lse-col>
									</lse-row>
								</lse-grid>
							</lse-b>

							<lse-b class="lse-timing-options">
								<lse-h3><?= __('Timing', 'LayerSlider') ?></lse-h3>
								<lse-grid class="lse-form-elements">
									<lse-row>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Duration', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['particledissolve']['duration'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<lse-fe-wrapper class="lse-smart-help" data-smart-help="duration" data-smart-help-title="<?= __('Duration', 'LayerSlider') ?>">
													<?php lsGetInput( $lsDefaults['slidefxtr']['particledissolve']['duration'] ) ?>
												</lse-fe-wrapper>
												<lse-unit>ms</lse-unit>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Easing', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-jcc">
												<lse-fe-wrapper class="lse-select lse-smart-help" data-smart-help="easing" data-smart-help-title="<?= __('Easing', 'LayerSlider') ?>">
													<?php lsGetSelect( $lsDefaults['slidefxtr']['particledissolve']['ease'], null, [
														'options'   => $lsDefaults['sfxeasings']
													]) ?>
												</lse-fe-wrapper>
											</lse-ib>
										</lse-col>
									</lse-row>
								</lse-grid>
							</lse-b>
						</lse-b>

						<lse-b data-name="bloom">
							<lse-h3><?= __('Effects', 'LayerSlider') ?></lse-h3>
							<lse-button-group class="lse-max-one lse-min-one lse-toggle-all lse-special-transition-presets">
								<lse-button class="lse-add-new-effect lse-unselectable lse-it-fix" data-tt=".tt-custom-sfx-preset" data-tt-de="0"><?= lsGetSVGIcon('plus') ?></lse-button>
							</lse-button-group>

							<lse-b class="lse-effect-options">
								<lse-h3><?= __('Options', 'LayerSlider') ?></lse-h3>
								<lse-grid class="lse-form-elements">
									<lse-row>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Samples', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['bloom']['samples'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['bloom']['samples'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Strength', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['bloom']['strength'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['bloom']['strength'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib>
												<lse-text><?= __('Direction', 'LayerSlider') ?></lse-text>
											</lse-ib>
											<lse-ib>
												<lse-fe-wrapper class="lse-select">
													<?php lsGetSelect( $lsDefaults['slidefxtr']['bloom']['direction'] ) ?>
												</lse-fe-wrapper>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-2-1">
											<lse-ib><lse-text><?= __('Background Color', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['bloom']['bgColor'], null, [
													'type'  => 'color'
												]) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Auto Direction', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib>
												<?php lsGetCheckbox( $lsDefaults['slidefxtr']['bloom']['autoDirection'], null, [], false, [
													'data-tt' => '.tt-sfx-auto-dir',
													'data-tt-de' => 0.1
												]) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full"></lse-col>
									</lse-row>
								</lse-grid>
							</lse-b>

							<lse-b class="lse-timing-options">
								<lse-h3><?= __('Timing', 'LayerSlider') ?></lse-h3>
								<lse-grid class="lse-form-elements">
									<lse-row>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Duration', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['bloom']['duration'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<lse-fe-wrapper class="lse-smart-help" data-smart-help="duration" data-smart-help-title="<?= __('Duration', 'LayerSlider') ?>">
													<?php lsGetInput( $lsDefaults['slidefxtr']['bloom']['duration'] ) ?>
												</lse-fe-wrapper>
												<lse-unit>ms</lse-unit>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Easing', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-jcc">
												<lse-fe-wrapper class="lse-select lse-smart-help" data-smart-help="easing" data-smart-help-title="<?= __('Easing', 'LayerSlider') ?>">
													<?php lsGetSelect( $lsDefaults['slidefxtr']['bloom']['ease'], null, [
														'options'   => $lsDefaults['sfxeasings']
													]) ?>
												</lse-fe-wrapper>
											</lse-ib>
										</lse-col>
									</lse-row>
								</lse-grid>
							</lse-b>
						</lse-b>

						<lse-b data-name="glow">
							<lse-h3><?= __('Effects', 'LayerSlider') ?></lse-h3>
							<lse-button-group class="lse-max-one lse-min-one lse-toggle-all lse-special-transition-presets">
								<lse-button class="lse-add-new-effect lse-unselectable lse-it-fix" data-tt=".tt-custom-sfx-preset" data-tt-de="0"><?= lsGetSVGIcon('plus') ?></lse-button>
							</lse-button-group>

							<lse-b class="lse-effect-options">
								<lse-h3><?= __('Options', 'LayerSlider') ?></lse-h3>
								<lse-grid class="lse-form-elements">
									<lse-row>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Max Blur', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['glow']['maxBlur'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['glow']['maxBlur'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Glow Detail', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['glow']['bokehSamples'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['glow']['bokehSamples'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Highlight Threshold', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['glow']['hiThreshold'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['glow']['hiThreshold'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Highlight Boost', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['glow']['hiBoost'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['glow']['hiBoost'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Peak Bias', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['glow']['peakBias'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['glow']['peakBias'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Move Amount', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['glow']['moveAmount'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['glow']['moveAmount'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib>
												<lse-text><?= __('Blur Mode', 'LayerSlider') ?></lse-text>
											</lse-ib>
											<lse-ib>
												<lse-fe-wrapper class="lse-select">
													<?php lsGetSelect( $lsDefaults['slidefxtr']['glow']['blurMode'] ) ?>
												</lse-fe-wrapper>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib>
												<lse-text><?= __('Direction', 'LayerSlider') ?></lse-text>
											</lse-ib>
											<lse-ib>
												<lse-fe-wrapper class="lse-select">
													<?php lsGetSelect( $lsDefaults['slidefxtr']['glow']['direction'] ) ?>
												</lse-fe-wrapper>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Auto Direction', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib>
												<?php lsGetCheckbox( $lsDefaults['slidefxtr']['glow']['autoDirection'], null, [], false, [
													'data-tt' => '.tt-sfx-auto-dir',
													'data-tt-de' => 0.1
												]) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full"></lse-col>
									</lse-row>
								</lse-grid>
							</lse-b>

							<lse-b class="lse-timing-options">
								<lse-h3><?= __('Timing', 'LayerSlider') ?></lse-h3>
								<lse-grid class="lse-form-elements">
									<lse-row>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Duration', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['glow']['duration'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<lse-fe-wrapper class="lse-smart-help" data-smart-help="duration" data-smart-help-title="<?= __('Duration', 'LayerSlider') ?>">
													<?php lsGetInput( $lsDefaults['slidefxtr']['glow']['duration'] ) ?>
												</lse-fe-wrapper>
												<lse-unit>ms</lse-unit>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Easing', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-jcc">
												<lse-fe-wrapper class="lse-select lse-smart-help" data-smart-help="easing" data-smart-help-title="<?= __('Easing', 'LayerSlider') ?>">
													<?php lsGetSelect( $lsDefaults['slidefxtr']['glow']['ease'], null, [
														'options'   => $lsDefaults['sfxeasings']
													]) ?>
												</lse-fe-wrapper>
											</lse-ib>
										</lse-col>
									</lse-row>
								</lse-grid>
							</lse-b>
						</lse-b>

						<lse-b data-name="origami">

							<lse-h3><?= __('Effects', 'LayerSlider') ?></lse-h3>
							<lse-button-group class="lse-max-one lse-min-one lse-toggle-all lse-special-transition-presets">
								<lse-button class="lse-add-new-effect lse-unselectable lse-it-fix" data-tt=".tt-custom-sfx-preset" data-tt-de="0"><?= lsGetSVGIcon('plus') ?></lse-button>
							</lse-button-group>

							<lse-b class="lse-effect-options">
								<lse-h3><?= __('Options', 'LayerSlider') ?></lse-h3>
								<lse-grid class="lse-form-elements">
									<lse-row>
										<lse-col class="lse-2-1">
											<lse-ib><lse-text><?= __('Fade Color', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['origami']['fadeColor'], null, [
													'type' 	=> 'color'
												]) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Fade Color Opacity', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['origami']['opacity'], null, [
													'type' 	=> 'range',
													'name' 	=> ''
												]) ?>
												<?php lsGetInput( $lsDefaults['slidefxtr']['origami']['opacity'] ) ?>
											</lse-ib>
										</lse-col>
										<lse-col class="lse-full"></lse-col>
									</lse-row>
								</lse-grid>
							</lse-b>

							<lse-b class="lse-timing-options">
								<lse-h3><?= __('Timing', 'LayerSlider') ?></lse-h3>
								<lse-grid class="lse-form-elements">
									<lse-row>
										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Duration', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-range-inputs lse-2-1">
												<?php lsGetInput( $lsDefaults['slidefxtr']['origami']['duration'], null, [
													'type'  => 'range',
													'name'  => ''
												]) ?>
												<lse-fe-wrapper class="lse-smart-help" data-smart-help="duration" data-smart-help-title="<?= __('Duration', 'LayerSlider') ?>">
													<?php lsGetInput( $lsDefaults['slidefxtr']['origami']['duration'] ) ?>
												</lse-fe-wrapper>
												<lse-unit>ms</lse-unit>
											</lse-ib>
										</lse-col>

										<lse-col class="lse-full">
											<lse-ib><lse-text><?= __('Easing', 'LayerSlider') ?></lse-text></lse-ib>
											<lse-ib class="lse-jcc">
												<lse-fe-wrapper class="lse-select lse-smart-help" data-smart-help="easing" data-smart-help-title="<?= __('Easing', 'LayerSlider') ?>">
													<?php lsGetSelect( $lsDefaults['slidefxtr']['origami']['ease'], null, [
														'options'   => $lsDefaults['sfxeasings']
													]) ?>
												</lse-fe-wrapper>
											</lse-ib>
										</lse-col>
									</lse-row>
								</lse-grid>
							</lse-b>
						</lse-b>

					</lse-b>

					<lse-b class="lse-notification lse-new-effect-notification">
						<lse-text><?= __('Built-in effects expose only their timing here. Make an editable copy to access every control, then customize this animation freely, or design something brand new from scratch.', 'LayerSlider' ) ?></lse-text>
						<lse-button class="lse-add-new-effect"><?= __('Make an Editable Copy', 'LayerSlider') ?></lse-button>
					</lse-b>

					<lse-b class="lse-notification lse-delete-effect-notification">
						<lse-button class="lse-remove-custom-effect"><?= __('Delete Effect', 'LayerSlider') ?></lse-button>
					</lse-b>

				</lse-b>

				<!-- Custom 2D -->
				<lse-grid class="lse-transitions-section" data-tr-type="custom_2d_transitions">
					<lse-row>
						<lse-p><?= __('You haven’t created any custom 2D transitions yet.', 'LayerSlider') ?></lse-p>
					</lse-row>
				</lse-grid>

				<!-- Custom 3D -->
				<lse-grid class="lse-transitions-section" data-tr-type="custom_3d_transitions">
					<lse-row>
						<lse-p><?= __('You haven’t created any custom 3D transitions yet.', 'LayerSlider') ?></lse-p>
					</lse-row>
				</lse-grid>

			</lse-b>
		</div>

		<lse-tt class="tt-apply-special-transition">
			<?= __('Apply or remove transition for the current slide.', 'LayerSlider') ?>
		</lse-tt>

	</lse-b>
</lse-b>