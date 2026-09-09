<?php

namespace PDFPro\Admin;

use PDFPro\Helper\PDFP_Functions as Utils;

if (!defined('ABSPATH'))
	exit;

if (!class_exists('PDFPro\Admin\PDFP_SidebarCards')) {
	/**
	 * The two panels in the side column of the pdfposter edit screen: a compatibility
	 * note, and the list of settings Pro unlocks.
	 *
	 * This is the free build, so the Pro card always renders -- there is no licence
	 * to check.
	 *
	 * Both draw their own surface, so the postbox chrome around them is stripped in
	 * CSS. They only appear on the classic version of the screen: when the block
	 * editor is in use, PDFP_PDFPoster::remove_metabox() drops every meta box for
	 * this post type, this pair included.
	 */
	class PDFP_SidebarCards {
		const POST_TYPE = 'pdfposter';
		const COMPAT_ID = 'pdfp_compatibility';
		const PRO_ID = 'pdfp_pro_ledger';

		public function register() {
			if (!is_admin()) {
				return;
			}

			add_action('add_meta_boxes', array($this, 'add_cards'));
			add_filter('get_user_option_meta-box-order_' . self::POST_TYPE, array($this, 'release_cards'));
		}

		/**
		 * The side column is ordered by priority: submitdiv registers at 'core', so
		 * 'default' puts the compatibility card under Publish and 'low' closes the
		 * column out with the Pro card.
		 */
		public function add_cards() {
			add_meta_box(
				self::COMPAT_ID,
				esc_html__('Page Builder Support', 'pdf-poster'),
				array($this, 'render_compatibility'),
				self::POST_TYPE,
				'side',
				'default'
			);

			add_filter('postbox_classes_' . self::POST_TYPE . '_' . self::COMPAT_ID, array($this, 'flat_postbox_class'));

			add_meta_box(
				self::PRO_ID,
				esc_html__('PDF Poster Pro', 'pdf-poster'),
				array($this, 'render_pro'),
				self::POST_TYPE,
				'side',
				'low'
			);

			add_filter('postbox_classes_' . self::POST_TYPE . '_' . self::PRO_ID, array($this, 'flat_postbox_class'));
		}

		/**
		 * A box named in the user's stored meta-box order is rendered from the 'sorted'
		 * bucket, which WordPress draws ahead of 'core' -- that is how a card like this
		 * ends up above Publish once a layout has been saved, and hiding the drag handle
		 * means it cannot be dragged back. Dropping our IDs out of the stored string
		 * hands them back to their priorities and leaves every other box the user
		 * arranged exactly where they put it.
		 */
		public function release_cards($order) {
			if (!is_array($order) || empty($order['side'])) {
				return $order;
			}

			$ours = array(self::COMPAT_ID, self::PRO_ID);
			$kept = array_diff(explode(',', $order['side']), $ours);

			$order['side'] = implode(',', $kept);

			return $order;
		}

		/**
		 * The panel draws its own surface, so the postbox chrome around it is stripped
		 * in CSS. Hiding .postbox-header also removes the toggle and the drag handle,
		 * which is what keeps the card open and in place.
		 */
		public function flat_postbox_class($classes) {
			$classes[] = 'pdfp-flat-box';

			return $classes;
		}

		/**
		 * Short label for the ruled cell, full name for its tooltip -- "Beaver Builder"
		 * does not fit a 10px cell in a 280px column without wrapping the row.
		 */
		private function builders() {
			return array(
				__('Gutenberg', 'pdf-poster') => __('Block editor (Gutenberg)', 'pdf-poster'),
				__('Elementor', 'pdf-poster') => __('Elementor', 'pdf-poster'),
				__('Divi', 'pdf-poster') => __('Divi Builder', 'pdf-poster'),
				__('Bricks', 'pdf-poster') => __('Bricks Builder', 'pdf-poster'),
				__('WPBakery', 'pdf-poster') => __('WPBakery Page Builder', 'pdf-poster'),
				__('Beaver', 'pdf-poster') => __('Beaver Builder', 'pdf-poster'),
				__('Oxygen', 'pdf-poster') => __('Oxygen Builder', 'pdf-poster'),
				__('Breakdance', 'pdf-poster') => __('Breakdance', 'pdf-poster'),
			);
		}

		/**
		 * The settings the free build gates. Kept in step with the pro_title() calls
		 * and locked sections in PDFP_MetaBox.
		 */
		private function locked_features() {
			return array(
				__('Scroll & Adobe viewers', 'pdf-poster'),
				__('Watermark & branding', 'pdf-poster'),
				__('Right-click & copy protection', 'pdf-poster'),
				__('Popup (lightbox) viewer', 'pdf-poster'),
				__('Zoom, thumbnails, reader mode', 'pdf-poster'),
				__('Custom button labels', 'pdf-poster'),
			);
		}

		public function render_compatibility() {
			$builders = $this->builders();
			$total = count($builders);
			?>
			<div class="pdfp-ledger">
				<div class="pdfp-ledger__rule">
					<span><?php esc_html_e('Compatibility', 'pdf-poster'); ?></span>
					<span><?php echo absint($total) . ' / ' . absint($total); ?></span>
				</div>

				<div class="pdfp-ledger__pad">
					<h4 class="pdfp-ledger__title"><?php esc_html_e('Every builder, one shortcode.', 'pdf-poster'); ?></h4>

					<p class="pdfp-ledger__note">
						<?php esc_html_e('Paste the shortcode above into any of these — nothing else to install.', 'pdf-poster'); ?>
					</p>

					<div class="pdfp-ledger__grid">
						<?php foreach ($builders as $short => $full) { ?>
							<span title="<?php echo esc_attr($full); ?>"><?php echo esc_html($short); ?></span>
						<?php } ?>
					</div>
				</div>
			</div>
			<?php
		}

		public function render_pro() {
			$features = $this->locked_features();
			$pricing_url = Utils::pricing_url();
			?>
			<div class="pdfp-ledger">
				<div class="pdfp-ledger__rule">
					<span><?php esc_html_e('Pro version', 'pdf-poster'); ?></span>
					<span><?php esc_html_e('Locked', 'pdf-poster'); ?></span>
				</div>

				<div class="pdfp-ledger__pad">
					<h4 class="pdfp-ledger__title">
						<?php
						printf(
							/* translators: %d: number of settings available only in the Pro version. */
							esc_html__('%d settings the free build keeps shut.', 'pdf-poster'),
							absint(count($features))
						);
						?>
					</h4>

					<ul class="pdfp-ledger__rows">
						<?php foreach ($features as $feature) { ?>
							<li>
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static inline icon markup.
								echo Utils::lock_icon();
								echo esc_html($feature);
								?>
							</li>
						<?php } ?>
					</ul>

					<a class="pdfp-ledger__cta" href="<?php echo esc_url($pricing_url); ?>">
						<?php esc_html_e('See Pro pricing', 'pdf-poster'); ?>
					</a>

					<p class="pdfp-ledger__foot"><?php esc_html_e('14-day refund policy', 'pdf-poster'); ?></p>
				</div>
			</div>
			<?php
		}
	}
}
