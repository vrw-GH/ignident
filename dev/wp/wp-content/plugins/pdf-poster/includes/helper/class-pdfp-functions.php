<?php
namespace PDFPro\Helper;

if ( ! defined( 'ABSPATH' ) ) { exit; }

if (!class_exists('PDFPro\Helper\PDFP_Functions')) {
    class PDFP_Functions {

        protected static $meta = null;

        public static function i($array, $key1, $key2 = '', $default = false) {
            if (isset($array[$key1][$key2])) {
                return $array[$key1][$key2];
            } else if (isset($array[$key1])) {
                return $array[$key1];
            }
            return $default;
        }

        public static function isset($array, $key1, $default = false) {
            if (isset($array[$key1])) {
                return $array[$key1];
            }
            return $default;
        }

        public static function meta($id, $key, $default = null, $true = false) {
            $meta = metadata_exists('post', $id, '_fpdf') ? get_post_meta($id, '_fpdf', true) : '';
            if (isset($meta[$key]) && $meta != '') {
                if ($true == true) {
                    if ($meta[$key] == '1') {
                        return true;
                    } else if ($meta[$key] == '0') {
                        return false;
                    }
                } else {
                    return $meta[$key];
                }
            } else {
                return $default;
            }
        }

        /**
         * scrambel data removed (premium only)
         */
        public static function scramble__premium_only($do = 'encode', $data = '') {
            return $data;
        }

        /**
         * Detect Browser
         */
        public static function getBrowser() {
            $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
            $browser = "N/A";
            $browsers = array(
                '/msie/i' => 'Internet explorer',
                '/firefox/i' => 'Firefox',
                '/safari/i' => 'Safari',
                '/chrome/i' => 'Chrome',
                '/edge/i' => 'Edge',
                '/Edg/i' => 'Edge',
                '/opera/i' => 'Opera',
                '/mobile/i' => 'Mobile browser'
            );

            foreach ($browsers as $regex => $value) {
                if (preg_match($regex, $user_agent)) {
                    $browser = $value;
                }
            }

            return $browser;
        }

        public static function generate_pdf_poster_block($id) {

            if (!function_exists('pdfp__get_post_meta')) {
                return [
                    'blockName' => 'pdfp/pdfposter',
                ];
            }

            $meta = pdfp__get_post_meta($id, '_fpdf', true);

            $height = $meta('height', ['height' => 1122, 'unit' => 'px']);
            $width = $meta('width', ['width' => 100, 'unit' => '%']);
            $height_tablet = $meta('height_tablet', ['height' => 700, 'unit' => 'px']);
            $height_mobile = $meta('height_mobile', ['height' => 400, 'unit' => 'px']);
            $width_tablet = $meta('width_tablet', ['width' => 100, 'unit' => '%']);
            $width_mobile = $meta('width_mobile', ['width' => 100, 'unit' => '%']);

            $responsive_height = [
                'desktop' => $height['height'] . $height['unit'],
                'tablet' => $height_tablet['height'] . $height_tablet['unit'],
                'mobile' => $height_mobile['height'] . $height_mobile['unit'],
            ];

            $responsive_width = [
                'desktop' => $width['width'] . $width['unit'],
                'tablet' => $width_tablet['width'] . $width_tablet['unit'],
                'mobile' => $width_mobile['width'] . $width_mobile['unit'],
            ];

            $attrs = [
                'uniqueId' => wp_unique_id('pdf-poster'),
                'file' => $meta('source', ''),
                'title' => get_the_title($id),
                'height' => $responsive_height,
                'width' => $responsive_width,
                'print' => $meta('print', false, true),
                'showName' => $meta('show_filename', '1', true),
                'downloadButton' => $meta('show_download_btn', false, true),
                'downloadButtonText' => $meta('download_btn_text', 'Download File'),
                'fullscreenButton' => $meta('show_fullscreen_btn', '1', true),
                'fullscreenButtonText' => $meta('fullscreen_btn_text', 'View Fullscreen'),
                'newWindow' => $meta('new_window', false, true),
                'actionsPosition' => $meta('actions_position', 'top'),
                'protect' => $meta('protect', false, true),
                'keyboardNav' => $meta('keyboard_nav', false, true),
                'rtlMode' => $meta('rtl_mode', 'off'),
                'themeMode' => $meta('theme_mode', 'light'),
                'annotationMode' => $meta('annotation_mode', true, true),
                'openLinksInNewTab' => $meta('open_links_in_new_tab', false, true),
                'progressiveLoading' => $meta('progressive_loading', true, true),
                'defaultBrowser' => $meta('default_browser', false, true),
                'adobeEmbedder' => self::pdfp_resolve_viewer($meta('viewer', 'default', false)),
                'flipbookSourceType' => $meta('flipbook_source_type', 'pdf', false),
                'flipbookSound' => $meta('flipbook_sound', true, true),
            ];

            // CSF gallery stores comma-separated attachment IDs; convert to URLs for the flipbook image pages.
            $fb_images_raw = $meta('flipbook_images', '', false);
            $fb_image_urls = [];
            if (!empty($fb_images_raw)) {
                $fb_ids = is_array($fb_images_raw) ? $fb_images_raw : explode(',', $fb_images_raw);
                foreach ($fb_ids as $fb_img_id) {
                    $fb_img_id = (int) trim($fb_img_id);
                    if (!$fb_img_id) {
                        continue;
                    }
                    $fb_img_url = wp_get_attachment_image_url($fb_img_id, 'full');
                    if ($fb_img_url) {
                        $fb_image_urls[] = $fb_img_url;
                    }
                }
            }
            $attrs['flipbookImages'] = $fb_image_urls;

            $popupBtnPadding = $meta('popup_btn_padding', ["top" => 10, "right" => 20, "bottom" => 10, "left" => 20]);
            $attrs['btnStyles'] = [
                "background" => $meta('popup_btn_bg', '#1e73be'),
                "color" => $meta('popup_btn_color', '#ffffff'),
                "fontSize" => $meta('popup_btn_font_size', 1) . 'rem',
                "padding" => $popupBtnPadding
            ];

            $attrs['socialShare'] = [
                'enabled' => $meta('social_share', false, true),
                'facebook' => $meta('social_share_facebook', true, true),
                'twitter' => $meta('social_share_twitter', true, true),
                'linkedin' => $meta('social_share_linkedin', true, true),
                'pinterest' => $meta('social_share_pinterest', true, true),
                'mailto' => $meta('social_share_mailto', true, true),
                'position' => $meta('social_share_position', 'top', false),
            ];

            return [
                "blockName" => "pdfp/pdfposter",
                "attrs" => $attrs
            ];
        }

        public function isUnsupportedDevice() {
            $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';

            // Detect iPad
            $isIPad = stripos($userAgent, 'iPad') !== false;

            // Detect iPhone 6
            $isIPhone6 = stripos($userAgent, 'iPhone') !== false &&
                isset($_SERVER['HTTP_USER_AGENT']) &&
                preg_match('/iPhone OS [0-10]\/', $userAgent) && // Adjust for iOS versions
                stripos($userAgent, '375x667') !== false;

            if ($isIPad) {
                return true;
            } elseif ($isIPhone6) {
                return true;
            } else {
                return false;
            }
        }

        public static function pdfp_pro_title($title, $badge = 'PRO') {

            if ($badge == 'New') {
                return '
                <div class="pdfp-field-title">
                    <h4>' . esc_html($title) . '</h4>
                    <span class="pdfp-new-badge">' . esc_html($badge) . '</span>
                </div>
            ';
            } else {
                return '
                <div class="pdfp-field-title">
                    <h4>' . esc_html($title) . '</h4>
                    <span class="pdfp-pro-badge">' . esc_html($badge) . '</span>
                </div>
            ';
            }
        }

        public static function pdfp_new_badge($label = 'NEW') {
            return '<span class="pdfp-new-badge">' . esc_html($label) . '</span>';
        }

        /**
         * Standalone PRO badge for a single locked option inside an otherwise usable field.
         */
        public static function pdfp_pro_badge($label = 'PRO') {
            return '<span class="pdfp-pro-badge">' . esc_html($label) . '</span>';
        }

        /**
         * Is the dFlip flipbook engine available in this build?
         *
         * The FlipBook / Slider / Scroll viewers all depend on assets/dflip. Kept as a
         * capability check rather than a hard-coded true so a package built without the
         * engine degrades to the bundled PDF.js viewer instead of rendering nothing.
         */
        public static function pdfp_has_flipbook_engine() {
            static $has = null;

            if ($has === null) {
                $has = file_exists(PDFPRO_PATH . 'assets/dflip/js/dflip.min.js');
            }

            return $has;
        }

        /**
         * Resolve the viewer engine, enforcing capability server-side.
         *
         * FlipBook and Slider are free, but they render through dFlip, so they are only
         * honoured when that engine is on disk. Adobe needs the premium PDF Embed bridge
         * and Scroll is premium-only; both fall back to the bundled PDF.js viewer so a
         * value carried over from a Pro export can never render an empty container.
         */
        public static function pdfp_resolve_viewer($viewer) {
            if ($viewer === true) {
                $viewer = 'adobe';
            } elseif ($viewer === false || $viewer === '' || $viewer === null) {
                $viewer = 'default';
            }

            if (in_array($viewer, array('flipbook', 'slider'), true) && self::pdfp_has_flipbook_engine()) {
                return $viewer;
            }

            return 'default';
        }

        public static function pdfp_lock_field($field, $is_section = false) {

            // Lock the UI
            $field['class'] = 'pdfp-lock-field ' . ($is_section ? 'section' : '');


            // Force safe default (prevents DB pollution)
            if (isset($field['default'])) {
                $field['value'] = $field['default'];
            }

            return $field;
        }

        /**
         * Where every "go Pro" link in the admin points.
         *
         * Pricing is a route inside the dashboard SPA, not a menu page of its own, so the
         * link is the dashboard screen plus a hash -- admin.php?page=pdf-poster-pricing
         * is not a registered page and lands on "You do not have sufficient permissions".
         * One accessor so the route only has to be corrected in one place if it moves
         * again; the JS side's copy lives in src/blocks/pdf-poster/utils.js.
         */
        public static function pricing_url() {
            return admin_url('edit.php?post_type=pdfposter&page=pdf-poster#/pricing');
        }

        public static function upgrade_section() {
            return array(
                'type' => 'content',
                'content' => '<div class="pdfp-metabox-upgrade-section">' . esc_html__('The Ultimate PDF Embedder Plugin for WordPress, Loved by Over 20,000+ Users.', 'pdf-poster') . ' <a class="button button-bplugins" href="' . esc_url(self::pricing_url()) . '">' . esc_html__('Upgrade to PRO', 'pdf-poster') . '</a></div>'
            );
        }

        /**
         * The small lock used on every gated row, inline so it needs no asset and
         * inherits the row colour. Shared with PDFP_SidebarCards so the metabox card
         * and the side-column card mark locked settings the same way.
         */
        public static function lock_icon() {
            return '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true" focusable="false"><rect x="4" y="11" width="16" height="10" rx="1.5"></rect><path d="M8 11V7a4 4 0 0 1 8 0v4"></path></svg>';
        }

        /**
         * The card that lists the settings a section keeps shut on the free build.
         *
         * Same ledger surface as the cards in the side column (PDFP_SidebarCards):
         * ink border, a ruled header strip naming what it reports, hairline-ruled
         * feature cells and a single blue CTA. Styles live in src/admin.scss under
         * .pdfp-ledger--wide -- build/admin.css is enqueued on every admin screen, so
         * the metabox and the settings pages both pick them up.
         *
         * @param array $features Labels of the gated settings, in the order shown.
         * @return array CSF 'content' field.
         */
        public static function pro_feature_list($features) {
            $features = array_filter((array) $features);
            $total = count($features);

            if (!$total) {
                return array('type' => 'content', 'content' => '');
            }

            $html = '<div class="pdfp-ledger pdfp-ledger--wide">
                <div class="pdfp-ledger__rule">
                    <span>' . esc_html__('Pro version', 'pdf-poster') . '</span>
                    <span>' . esc_html__('Locked', 'pdf-poster') . '</span>
                </div>

                <div class="pdfp-ledger__pad">
                    <h4 class="pdfp-ledger__title">' . sprintf(
                        /* translators: %d: number of settings in this section available only in the Pro version. */
                        esc_html(_n('%d setting the free build keeps shut.', '%d settings the free build keeps shut.', $total, 'pdf-poster')),
                        absint($total)
                    ) . '</h4>

                    <ul class="pdfp-ledger__rows">';

            foreach ($features as $feature) {
                $html .= '<li>' . self::lock_icon() . esc_html($feature) . '</li>';
            }

            $html .= '</ul>

                    <div class="pdfp-ledger__actions">
                        <a class="pdfp-ledger__cta" href="' . esc_url(self::pricing_url()) . '">'
                            . esc_html__('See Pro pricing', 'pdf-poster') .
                        '</a>
                        <p class="pdfp-ledger__foot">' . esc_html__('14-day refund policy', 'pdf-poster') . '</p>
                    </div>
                </div>
            </div>';

            return array(
                'type' => 'content',
                'content' => $html
            );
        }

        /**
         * The Watermark section's opening card.
         *
         * The ledger lists what the free build keeps shut; this shows it. The six
         * shipped looks are dealt out like a hand of cards -- the same SVGs the Pro
         * Theme row uses, so the pitch can never advertise something the renderer does
         * not produce -- over the line that makes the feature safe to buy: the mark is
         * drawn as the document displays, the file on the server is untouched.
         *
         * Pro flips a switcher from here; the free build has no switcher to flip, so the
         * button goes to pricing instead, beside the same refund line the ledger cards
         * carry. Styles live in src/admin.scss under .pdfp-wm-intro.
         *
         * @return array CSF 'subheading' field.
         */
        public static function pdfp_watermark_intro() {
            $themes = array(
                'confidential' => __('Confidential', 'pdf-poster'),
                'draft'        => __('Draft Stamp', 'pdf-poster'),
                'wash'         => __('Sample Wash', 'pdf-poster'),
                'brand-corner' => __('Brand Corner', 'pdf-poster'),
                'logo-wash'    => __('Logo Wash', 'pdf-poster'),
                'logo-caption' => __('Logo + Caption', 'pdf-poster'),
            );

            // The fan: rotation, horizontal offset and scale per card, dealt left to right.
            $fan = array(
                array(-16, -30, 0.90), array(-10, -18, 0.94), array(-4, -6, 0.97),
                array(3, 6, 1.00), array(9, 18, 0.97), array(15, 30, 0.94),
            );

            $cards = '';
            $i = 0;
            foreach ($themes as $key => $label) {
                list($rot, $dx, $scale) = $fan[$i];
                $cards .= '<img class="pdfp-wm-intro__card" alt="' . esc_attr($label) . '" title="' . esc_attr($label) . '"'
                    . ' src="' . esc_url(PDFPRO_PLUGIN_DIR . 'assets/admin/img/watermark/' . $key . '.svg') . '"'
                    . ' style="--r:' . $rot . 'deg;--x:' . $dx . 'px;--s:' . $scale . ';z-index:' . $i . '">';
                $i++;
            }

            $chips = array(
                __('Text, a logo, or both', 'pdf-poster'),
                __('Tiled or one corner mark', 'pdf-poster'),
                __('Pick the pages', 'pdf-poster'),
                __('Pick who sees it', 'pdf-poster'),
            );

            $chiphtml = '';
            foreach ($chips as $chip) {
                $chiphtml .= '<li>' . esc_html($chip) . '</li>';
            }

            $content =
                '<div class="pdfp-wm-intro">'
                . '<div class="pdfp-wm-intro__fan">' . $cards . '</div>'
                . '<div class="pdfp-wm-intro__say">'
                . '<p class="pdfp-wm-intro__count"><b>' . esc_html(count($themes)) . '</b> '
                . esc_html__('looks, ready to go', 'pdf-poster') . '</p>'
                . '<h4>' . esc_html__('Stamp every page — the file is never touched.', 'pdf-poster') . '</h4>'
                . '<p class="pdfp-wm-intro__sub">'
                . esc_html__('The mark is drawn over the document as it is displayed, so the PDF on your server stays exactly as you uploaded it. Pick one of these and you are done, or take the Custom theme and set the colour, angle, size and tiling yourself.', 'pdf-poster')
                . '</p>'
                . '<ul class="pdfp-wm-intro__chips">' . $chiphtml . '</ul>'
                . '<div class="pdfp-wm-intro__actions">'
                . '<a class="button button-primary pdfp-wm-intro__go" href="' . esc_url(self::pricing_url()) . '">'
                . esc_html__('Upgrade to Pro', 'pdf-poster') . ' <span aria-hidden="true">&rarr;</span>'
                . '</a>'
                . '<p class="pdfp-wm-intro__foot">' . esc_html__('14-day refund policy', 'pdf-poster') . '</p>'
                . '</div>'
                . '</div>'
                . '</div>';

            return array(
                'type' => 'subheading',
                'class' => 'pdfp-wm-intro-row',
                'content' => $content,
            );
        }

        public static function quick_embed_shortcode() {
            return [
                'type' => 'content',
                'content' => '
                <div class="pdfp-quick-embed-shortcode-wrapper">
                    <div class="shortcode-container">
                        <code id="pdfp-shortcode-text">[pdf_embed url="your_file_url"]</code>
                        <button type="button" class="pdfp-copy-shortcode" data-shortcode=\'[pdf_embed url="your_file_url"]\'>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="copy-icon"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            <span class="copy-text">' . __('Copy', 'pdf-poster') . '</span>
                        </button>
                    </div>
                    <p class="description">' . __('Copy and paste this shortcode into any page or post. Replace <code>your_file_url</code> with your actual PDF link.', 'pdf-poster') . '</p>
                </div>
            '
            ];
        }

        public static function upcoming_section() {
            return array(
                'type' => 'content',
                'content' => '<div class="pdfp-metabox-upcoming-section">' . esc_html__('This feature is coming soon. Stay tuned for updates!', 'pdf-poster') . '</div>'
            );
        }

        public static function pdfp_preset($key, $default = false) {
            $settings = get_option('fpdf_option');
            return $settings[$key] ?? $default;
        }
    }
}