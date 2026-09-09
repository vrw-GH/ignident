<?php

namespace PDFPro\Admin;

if (!defined('ABSPATH')) exit;

if (!class_exists('PDFPro\Admin\PDFP_Chatbot')) {
    class PDFP_Chatbot {

        public function register() {
            add_action('admin_enqueue_scripts', [$this, 'enqueue'], 20);
            add_action('admin_footer', [$this, 'render_html']);
        }

        /**
         * Whether the current admin screen belongs to PDF Poster.
         * Used by BOTH enqueue() and render_html() so the styles always load
         * on the same pages the markup is printed on.
         *
         * NOTE: the $hook passed to admin_enqueue_scripts is "edit.php",
         * "post.php" or "post-new.php" on the list/editor screens and does NOT
         * contain the post type, so we must check the screen object instead.
         */
        private function is_pdfp_screen() {
            $screen = get_current_screen();
            if (!$screen) return false;

            if (!empty($screen->post_type) && $screen->post_type === 'pdfposter') {
                return true;
            }

            return strpos($screen->id, 'pdfposter') !== false
                || strpos($screen->id, 'pdf-poster') !== false;
        }

        public function enqueue($hook) {
            if (!$this->is_pdfp_screen()) {
                return;
            }

            wp_enqueue_script(
                'fuse-js',
                PDFPRO_PLUGIN_DIR . 'assets/admin/js/fuse.min.js',
                [],
                '7.0.0',
                true
            );

            wp_enqueue_script(
                'pdfp-chatbot',
                PDFPRO_PLUGIN_DIR . 'assets/admin/js/pdfp-chatbot.js',
                ['fuse-js'],
                PDFPRO_VER,
                true
            );

            wp_enqueue_style(
                'pdfp-chatbot-style',
                PDFPRO_PLUGIN_DIR . 'assets/admin/css/pdfp-chatbot.css',
                [],
                PDFPRO_VER
            );

            wp_localize_script('pdfp-chatbot', 'pdfpChatbot', [
                'pluginName'  => 'PDF Poster',
                'version'     => PDFPRO_VER,
                'docsUrl'     => 'https://bplugins.com/docs/pdf-poster/',
                'pricingUrl'  => 'https://bplugins.com/products/pdf-poster/pricing',
                'productUrl'  => 'https://bplugins.com/products/pdf-poster/',
                'supportUrl'  => 'https://bplugins.com/support/',
                'demoUrl'     => 'https://bplugins.com/products/pdf-poster/#demos',
                'suggestions' => $this->get_suggestions(),
                'kb'          => $this->get_knowledge_base(),
            ]);
        }

        public function render_html() {
            if (!$this->is_pdfp_screen()) {
                return;
            }
            ?>
            <div id="pdfp-chatbot-wrapper">
                <div id="pdfp-chatbot-window">
                    <div id="pdfp-chatbot-header">
                        <div id="pdfp-chatbot-header-info"> 
                            <div>
                                <div id="pdfp-chatbot-title">PDF Poster Assistant</div>
                                <div id="pdfp-chatbot-subtitle">Ask me anything about the plugin</div>
                            </div>
                        </div>
                        <button id="pdfp-chatbot-close" title="Close">✕</button>
                    </div>
                    <div id="pdfp-chatbot-messages"></div>
                    <div id="pdfp-chatbot-suggestions"></div>
                    <div id="pdfp-chatbot-input-area">
                        <input
                            type="text"
                            id="pdfp-chatbot-input"
                            placeholder="Type your question…"
                            autocomplete="off"
                        />
                        <button id="pdfp-chatbot-send" title="Send">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        </button>
                    </div>
                </div>
                <button id="pdfp-chatbot-toggle" title="PDF Poster Help">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </button>
            </div>
            <?php
        }

        private function get_suggestions() {
            // These appear as clickable chips when the chat opens.
            // Keep them short — max 5 words each.
            return [
                'What is PDF Poster?',
                'How do I embed a PDF?',
                'What shortcode do I use?',
                'What are the Pro features?',
                'Does it work on mobile?',
                'What is FlipBook mode?',
            ];
        }

        private function get_knowledge_base() {
            // ---------------------------------------------------------------
            // KNOWLEDGE BASE
            // Add or edit Q&A pairs here freely.
            //  - "q"    : the question Fuse.js searches against.
            //  - "a"    : the answer (basic HTML allowed).
            //  - "tags" : synonyms/keywords that improve fuzzy matching.
            //  - "pro"  : set to true for Pro-only features. The chatbot
            //             automatically appends an "Upgrade to Pro" link
            //             (pricingUrl) to every answer flagged Pro.
            // ---------------------------------------------------------------
            return [

                // ── About / Overview ────────────────────────────────────────
                [
                    'q'    => 'What is PDF Poster?',
                    'tags' => 'what is about overview plugin purpose use case pdf embedder viewer tell me explain describe intro introduction details information',
                    'a'    => '<strong>PDF Poster</strong> is a WordPress plugin by bPlugins for embedding and displaying PDF files on your site with a clean, responsive viewer — trusted by over <strong>20,000+ users</strong>. You can embed PDFs in posts, pages, widgets, or templates using a Gutenberg block or shortcodes. It is great for brochures, eBooks, portfolios, reports, manuals, menus, and business documents.',
                ],
                [
                    'q'    => 'What can I use PDF Poster for?',
                    'tags' => 'use case usecase scenario when why brochure ebook portfolio report manual menu document',
                    'a'    => 'Common uses: sharing <strong>brochures, eBooks, business reports, portfolios, restaurant menus, manuals, catalogs, and forms</strong>. The responsive viewer works on desktop, tablet, and mobile, with download, print, full-screen, and filename display options built in.',
                ],
                [
					'q'    => 'What features does the free version include?',
					'tags' => 'free features included list what do i get version chatbot',
					'a'    => 'The <strong>Free</strong> version includes: interactive PDF Chatbot assistant, responsive Width &amp; Height (separate Desktop / Tablet / Mobile values), Download button with custom label, Display Filename, Allow Printing, Full-screen button, Social Share (Facebook, X/Twitter, LinkedIn, Pinterest), a Gutenberg block, the Quick Embedder, and shortcode embedding.',
				],

                // ── Installation ────────────────────────────────────────────
                [
                    'q'    => 'How do I install PDF Poster?',
                    'tags' => 'install setup activate plugin add new',
                    'a'    => 'Go to <strong>WordPress Dashboard → Plugins → Add New</strong> → search for <em>PDF Poster</em> → click <strong>Install Now</strong> → then <strong>Activate</strong>.',
                ],
                [
                    'q'    => 'How do I activate the plugin?',
                    'tags' => 'activate enable turn on plugin',
                    'a'    => 'After installing, go to <strong>Plugins → Installed Plugins</strong> → find PDF Poster → click <strong>Activate</strong>. A new <strong>PDF Poster</strong> menu appears in the left admin sidebar.',
                ],
                [
                    'q'    => 'How do I update PDF Poster?',
                    'tags' => 'update upgrade new version plugin',
                    'a'    => 'Go to <strong>Dashboard → Updates</strong>, or <strong>Plugins → Installed Plugins</strong>. If a new version is available it will show an update notice — click <strong>Update Now</strong>.',
                ],

                // ── Uploading & Adding a PDF ────────────────────────────────
                [
                    'q'    => 'How do I add or upload a PDF?',
                    'tags' => 'upload add pdf file media library new create source',
                    'a'    => 'Go to <strong>PDF Poster → Add New</strong>, then in the <strong>PDF Poster Configuration → General</strong> tab use the <strong>PDF Source</strong> field to upload a PDF or pick one from your Media Library. Give it a title and click <strong>Publish</strong>.',
                ],
                [
                    'q'    => 'How do I embed a PDF? (overview)',
                    'tags' => 'embed display show pdf how methods ways add to page',
                    'a'    => 'Three ways: (1) <strong>Gutenberg block</strong> — add the <em>PDF Poster</em> block to any page; (2) <strong>Shortcode</strong> — create a PDF Poster post and paste its <code>[pdf id="123"]</code> shortcode; (3) <strong>Quick Embedder</strong> — under <strong>PDF Poster → Settings → Quick Embedder</strong> use <code>[pdf_embed url="your_file_url"]</code> for a direct URL.',
                ],
                [
                    'q'    => 'How do I add a PDF from an external URL?',
                    'tags' => 'external url link remote pdf http quick embedder',
                    'a'    => 'Use the Quick Embedder shortcode: <code>[pdf_embed url="https://example.com/file.pdf"]</code>. The hosting server must allow cross-origin access (CORS), otherwise the PDF will fail to load.',
                ],
                [
                    'q'    => 'What file types does PDF Poster support?',
                    'tags' => 'file type format supported pdf only word image',
                    'a'    => 'PDF Poster supports <strong>PDF files only</strong> (.pdf). It does not display Word documents, images, or other file types directly.',
                ],

                // ── Shortcodes ──────────────────────────────────────────────
                [
                    'q'    => 'What shortcode do I use to embed a PDF?',
                    'tags' => 'shortcode embed insert pdf page post which tag',
                    'a'    => 'For a PDF Poster post, use <code>[pdf id="123"]</code> — replace <code>123</code> with the post ID. For a direct file URL (Quick Embedder), use <code>[pdf_embed url="your_file_url"]</code>.',
                ],
                [
                    'q'    => 'The shortcode is not working',
                    'tags' => 'shortcode broken not working error blank invalid id',
                    'a'    => 'Check: (1) the format is <code>[pdf id="POST_ID"]</code> with the real post ID, (2) the PDF Poster post exists and is published, (3) the plugin is activated, (4) the PDF source is valid and accessible. An "Invalid PDF Poster ID" message means the ID is wrong.',
                ],
                [
                    'q'    => 'How do I find the post ID for the shortcode?',
                    'tags' => 'post id shortcode find number where',
                    'a'    => 'Go to <strong>PDF Poster → All PDF Posters</strong> and hover over a post title — the ID appears in the URL (e.g. <code>post=123</code>). The shortcode is also shown directly in the post editor.',
                ],
                [
                    'q'    => 'What is the Quick Embedder?',
                    'tags' => 'quick embedder settings shortcode generate url pdf_embed',
                    'a'    => 'Quick Embedder is under <strong>PDF Poster → Settings → Quick Embedder</strong>. It lets you embed a PDF by URL without creating a full post, using <code>[pdf_embed url="your_file_url"]</code>. You can set default viewer height, width, filename display, and download button there.',
                ],

                // ── Gutenberg Block ─────────────────────────────────────────
                [
                    'q'    => 'How do I embed a PDF using the Gutenberg block?',
                    'tags' => 'gutenberg block editor insert pdf layout elements slash command',
                    'a'    => 'In the block editor click the <strong>+</strong> button (or type <code>/pdf</code>) → find <strong>PDF Poster</strong> under the <em>Layout Elements</em> category → insert it → choose your PDF from the Media Library and configure options in the block inspector on the right.',
                ],
                [
                    'q'    => 'The PDF Poster block is not showing in Gutenberg',
                    'tags' => 'block not found gutenberg missing block editor not appearing',
                    'a'    => 'Make sure the plugin is active and that <strong>Gutenberg Integration</strong> is enabled under <strong>PDF Poster → Settings → Shortcode</strong>. Try searching "PDF" or typing <code>/pdf</code> in the inserter. If it is still missing, deactivate and reactivate the plugin to refresh block registration.',
                ],

                // ── Display / Size Settings ─────────────────────────────────
                [
                    'q'    => 'How do I change the PDF width or height?',
                    'tags' => 'width height size dimensions change resize responsive',
                    'a'    => 'Open the PDF Poster post → <strong>General</strong> tab. Set <strong>Width</strong> and <strong>Height</strong> in px or %. There are separate fields for <strong>Desktop, Tablet, and Mobile</strong>, so the viewer is fully responsive.',
                ],
                [
                    'q'    => 'Does PDF Poster work on mobile / is it responsive?',
                    'tags' => 'responsive mobile tablet phone fluid device preview ios android iphone ipad smartphone fit screen small device',
                    'a'    => 'Yes. The viewer is responsive and you can set separate Width/Height for <strong>Desktop, Tablet, and Mobile</strong>. A <strong>Preview Device</strong> switcher in the General tab lets you check each layout before publishing.',
                ],
                [
                    'q'    => 'How do I display or hide the filename?',
                    'tags' => 'filename file name title show hide display top',
                    'a'    => 'In the post editor open the <strong>Controls</strong> tab and toggle <strong>Display Filename</strong> to show or hide the PDF file name at the top of the viewer.',
                ],

                // ── Actions: Download / Print / Fullscreen ──────────────────
                [
                    'q'    => 'How do I show or hide the download button?',
                    'tags' => 'download button enable disable hide remove show',
                    'a'    => 'In the post editor open the <strong>Actions</strong> tab and toggle <strong>Download Button</strong> on or off.',
                ],
                [
                    'q'    => 'How do I allow or disable printing?',
                    'tags' => 'print printing button enable disable allow',
                    'a'    => 'In the <strong>Actions</strong> tab, toggle <strong>Allow Printing</strong> to let visitors print the PDF, or turn it off to disable printing.',
                ],
                [
                    'q'    => 'How do I change the full-screen button label?',
                    'tags' => 'fullscreen full screen label text button rename',
                    'a'    => 'In the <strong>Actions</strong> tab, edit the <strong>Fullscreen Label</strong> field (default is "View Fullscreen").',
                ],
                [
                    'q'    => 'Can I customize the download button label?',
                    'tags' => 'download label text custom rename button',
                    'pro'  => true,
                    'a'    => 'Customizing the download button label is a <strong>Pro</strong> feature. Pro also adds full-screen button control, opening full-screen in a new tab, and custom action positions (top/bottom).',
                ],

                // ── Social Share ────────────────────────────────────────────
                [
                    'q'    => 'How do I enable social sharing?',
                    'tags' => 'social share facebook twitter linkedin pinterest buttons enable',
                    'a'    => 'Open the <strong>Social Share</strong> tab in the post editor, toggle <strong>Enable Sharing</strong>, choose the position, and turn on the networks you want: <strong>Facebook, X/Twitter, LinkedIn, and Pinterest</strong>.',
                ],

                // ── Pro: Viewer Modes ───────────────────────────────────────
                [
					'q'    => 'What is FlipBook mode / viewer?',
					'tags' => 'flipbook flip book 3d page turn mode realistic viewer pro',
					'pro'  => true,
					'a'    => 'The <strong>Interactive FlipBook Viewer</strong> displays your PDF as a realistic page-flipping book, like turning real pages. This is a <strong>Pro</strong> feature.',
				],
				[
					'q'    => 'What is Slider or Scroll mode / viewer?',
					'tags' => 'slider scroll viewer layout options mode pro',
					'pro'  => true,
					'a'    => 'The <strong>Slider</strong> and <strong>Scroll</strong> viewer layout choices let you display documents page-by-page as a slide/swipe gallery or as a continuous scrolling reader. These are <strong>Pro</strong> features.',
				],
				[
					'q'    => 'Is there an interactive PDF Chatbot assistant?',
					'tags' => 'chatbot assistant support help ai chat question answer instant query',
					'a'    => 'Yes, PDF Poster includes an interactive <strong>PDF Chatbot assistant</strong> right in your admin dashboard. It helps answer setup, usage, and configuration questions instantly. This chatbot is available in the <strong>Free</strong> version.',
				],
                [
                    'q'    => 'What is the Raw PDF viewer?',
                    'tags' => 'raw pdf clean no frame no toolbar raw_pdf shortcode pro',
                    'pro'  => true,
                    'a'    => 'The <strong>Raw PDF Viewer</strong> embeds a clean PDF with no black frame or toolbar, using the <code>[raw_pdf id="123"]</code> shortcode. This is a <strong>Pro</strong> feature.',
                ],
                [
                    'q'    => 'What is the Adobe PDF viewer?',
                    'tags' => 'adobe pdf embed api viewer industry leading pro',
                    'pro'  => true,
                    'a'    => 'PDF Poster Pro includes the <strong>Adobe PDF Embed API</strong> viewer for a polished, industry-standard reading experience. This is a <strong>Pro</strong> feature.',
                ],
                [
                    'q'    => 'What is Reader Mode / Show Only PDF?',
                    'tags' => 'reader mode distraction free show only pdf clean minimal pro',
                    'pro'  => true,
                    'a'    => 'A distraction-free <strong>Reader Mode</strong> hides the background and viewer menu so only the PDF shows. This is a <strong>Pro</strong> feature.',
                ],

                // ── Pro: Navigation & Controls ──────────────────────────────
                [
                    'q'    => 'Can I show thumbnails or a sidebar navigation?',
                    'tags' => 'thumbnails sidebar navigation toggle panel auto open pro',
                    'pro'  => true,
                    'a'    => 'Toggling <strong>Thumbnails navigation</strong> and auto-opening the <strong>sidebar</strong> by default are <strong>Pro</strong> features.',
                ],
                [
                    'q'    => 'How do I set the initial page or zoom level?',
                    'tags' => 'initial page jump to page start zoom level scale default pro',
                    'pro'  => true,
                    'a'    => 'Setting a custom <strong>Initial Page</strong> (jump to page) and a default <strong>Zoom Level</strong> are <strong>Pro</strong> features.',
                ],
                [
					'q'    => 'Can I enable a horizontal scrollbar or load the latest version?',
					'tags' => 'horizontal scrollbar wide pdf load latest version cache pro',
					'pro'  => true,
					'a'    => '<strong>Horizontal scrollbar</strong> support and "always load the latest document version" are <strong>Pro</strong> features.',
				],
				[
					'q'    => 'Can I configure Annotation Mode or open PDF links in a new tab?',
					'tags' => 'annotation mode drawing comments notes open link new tab window default viewer pro',
					'pro'  => true,
					'a'    => '<strong>Annotation Mode Configuration</strong> (to show notes, drawings, and comments saved inside the PDF) and <strong>Open PDF links in new tab</strong> are <strong>Pro</strong> features available under the Controls tab for the default viewer.',
				],

                // ── Pro: Popup ──────────────────────────────────────────────
                [
                    'q'    => 'Can I open a PDF in a popup / modal?',
                    'tags' => 'popup modal lightbox trigger button image overlay pro',
                    'pro'  => true,
                    'a'    => 'The <strong>Popup</strong> feature opens PDFs in a modal window, triggered by a button or image, with custom alignment and a PDF icon overlay. This is a <strong>Pro</strong> feature.',
                ],

                // ── Pro: Content Protection ─────────────────────────────────
                [
                    'q'    => 'Can I protect the PDF content (disable right-click / copy)?',
                    'tags' => 'protect content security disable right click copy text selection lock password pro',
                    'pro'  => true,
                    'a'    => '<strong>Content Protection</strong> lets you disable right-click, disable text selection, and suppress blocked-action warnings to safeguard your documents. This is a <strong>Pro</strong> feature.',
                ],

                // ── Pro: Cloud Integration ──────────────────────────────────
                [
                    'q'    => 'Can I use a PDF from Google Drive or Dropbox?',
                    'tags' => 'google drive dropbox cloud storage sync integration picker pro',
                    'pro'  => true,
                    'a'    => 'Direct <strong>Dropbox</strong> connectivity and a <strong>Google Drive</strong> cloud picker are part of Pro\'s Cloud Integration, for seamless external hosting. This is a <strong>Pro</strong> feature.',
                ],
                [
                    'q'    => 'What is the Google Doc Viewer fallback?',
                    'tags' => 'google doc viewer fallback browser compatibility bypass pro',
                    'pro'  => true,
                    'a'    => 'The <strong>Google Doc Viewer</strong> fallback renders PDFs through Google\'s viewer to bypass display issues in some browsers. This is a <strong>Pro</strong> feature.',
                ],

                // ── Pro: Custom CSS & Presets ───────────────────────────────
                [
                    'q'    => 'How do I add custom CSS to the viewer?',
                    'tags' => 'custom css style design branding appearance override pro',
                    'pro'  => true,
                    'a'    => '<strong>Custom CSS</strong> (full design control, override viewer styles, applied globally) is a <strong>Pro</strong> feature found under <strong>Settings → Custom CSS</strong>.',
                ],
                [
                    'q'    => 'What are Presets and how do I use them?',
                    'tags' => 'preset presets global defaults reuse template site wide settings pro',
                    'pro'  => true,
                    'a'    => '<strong>Presets</strong> let you set site-wide default viewer settings that apply automatically to every embed for a consistent experience. This is a <strong>Pro</strong> feature under <strong>Settings → Presets</strong>.',
                ],

                // ── Pro overview & upgrade ──────────────────────────────────
                [
					'q'    => 'What are the Pro features?',
					'tags' => 'pro premium features upgrade paid list what extra',
					'pro'  => true,
					'a'    => 'PDF Poster <strong>Pro</strong> adds: Slider and Scroll viewer options, Annotation Mode configuration, Open PDF links in new tab, FlipBook viewer, Adobe viewer, Raw PDF viewer, Reader Mode, thumbnails &amp; sidebar navigation, custom initial page &amp; zoom, horizontal scrollbar, popup/modal viewer, content protection (disable right-click/copy), Dropbox &amp; Google Drive cloud sync, Google Doc Viewer fallback, Custom CSS, Presets, button translation, and extra Gutenberg blocks.',
				],
                [
                    'q'    => 'How do I upgrade to Pro / where is pricing?',
                    'tags' => 'upgrade pro buy purchase premium price pricing cost plan',
                    'pro'  => true,
                    'a'    => 'You can upgrade from <strong>PDF Poster → Upgrade to PRO</strong> in the admin menu, or see all plans on the pricing page below.',
                ],

                // ── Compatibility & Requirements ────────────────────────────
                [
                    'q'    => 'What PHP and WordPress versions are required?',
                    'tags' => 'php wordpress version requirement minimum wp compatibility',
                    'a'    => 'PDF Poster requires <strong>WordPress 5.0.3+</strong> and <strong>PHP 7.1+</strong>.',
                ],
                [
                    'q'    => 'Does it work with Elementor or other page builders?',
                    'tags' => 'elementor divi beaver builder page builder compatible works',
                    'a'    => 'Yes. Add a <strong>Shortcode</strong> widget in your page builder and paste <code>[pdf id="POST_ID"]</code> (or <code>[pdf_embed url="..."]</code>). This works in Elementor, Divi, Beaver Builder, and similar builders.',
                ],
                [
                    'q'    => 'Does it work with WooCommerce?',
                    'tags' => 'woocommerce product page compatible shop store',
                    'a'    => 'Yes. Paste the PDF Poster shortcode into a WooCommerce product description or any product page area.',
                ],
                [
                    'q'    => 'Can I embed a PDF in a widget or sidebar?',
                    'tags' => 'widget sidebar embed pdf footer block widget area',
                    'a'    => 'Yes. Use a <strong>Shortcode</strong> or <strong>Text</strong> widget in your widget/sidebar area and paste the PDF Poster shortcode.',
                ],
                [
                    'q'    => 'Can I display multiple PDFs on one page?',
                    'tags' => 'multiple pdf same page several many embeds',
                    'a'    => 'Yes. Add several blocks or paste multiple shortcodes (each with its own ID/URL) on the same page — each embed works independently.',
                ],

                // ── Troubleshooting ─────────────────────────────────────────
                [
                    'q'    => 'My PDF is not showing / blank white screen',
                    'tags' => 'pdf not showing blank white screen not loading not displaying empty',
                    'a'    => 'Check: (1) the PDF source/URL is correct and publicly accessible, (2) for external files the server allows CORS, (3) try another browser, (4) clear your site/caching-plugin cache. Re-selecting the file in the post\'s <strong>PDF Source</strong> field often fixes it.',
                ],
                [
                    'q'    => 'The PDF loads very slowly',
                    'tags' => 'slow loading performance speed large file size',
                    'a'    => 'Large PDFs take longer to load. Compress the file first (e.g. with ilovepdf.com) and make sure your hosting has good performance.',
                ],
                [
                    'q'    => 'CORS error with an external PDF',
                    'tags' => 'cors error external url cross origin blocked',
                    'a'    => 'CORS errors come from the server hosting the PDF, not from PDF Poster. Either host the PDF on your own site, or use a host/server that sends the proper CORS headers.',
                ],
                [
                    'q'    => 'PDF not updating after I replaced the file (caching)',
                    'tags' => 'cache clear not updating old version caching plugin wp rocket',
                    'a'    => 'Purge your caching plugin (WP Rocket, W3 Total Cache, LiteSpeed, etc.) and your browser cache after changing a PDF. You can also exclude the page from caching.',
                ],

                // ── Support & Docs ──────────────────────────────────────────
                [
                    'q'    => 'Where is the documentation?',
                    'tags' => 'docs documentation help guide manual tutorial',
                    'a'    => 'Full documentation is at <a href="https://bplugins.com/docs/pdf-poster/" target="_blank">bplugins.com/docs/pdf-poster</a>.',
                ],
                [
                    'q'    => 'How do I contact support or report a bug?',
                    'tags' => 'support contact help ticket email bug report issue problem',
                    'a'    => 'Open a ticket at <a href="https://bplugins.com/support/" target="_blank">bplugins.com/support</a>. Free users can also use the <a href="https://wordpress.org/support/plugin/pdf-poster/" target="_blank">WordPress.org support forum</a>.',
                ],
                [
                    'q'    => 'Where can I see a demo?',
                    'tags' => 'demo example preview try test sample',
                    'a'    => 'See live demos at <a href="https://bplugins.com/products/pdf-poster/#demos" target="_blank">bplugins.com/products/pdf-poster</a>.',
                ],
            ];
        }
    }

    new PDFP_Chatbot();
}
