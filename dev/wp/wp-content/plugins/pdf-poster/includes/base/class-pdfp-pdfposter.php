<?php

namespace PDFPro\Base;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('PDFPro\Base\PDFP_PDFPoster')) {
    class PDFP_PDFPoster
    {
        protected static $_instance = null;
        protected $post_type = 'pdfposter';

        /**
         * construct function
         */
        public function __construct()
        {
            add_action('init', [$this, 'init']);
            if (is_admin()) {
                add_action('admin_menu', [$this, 'arrowAddNewSubmenu'], 999);

                add_filter('post_row_actions', [$this, 'pdfp_remove_row_actions'], 10, 2);

                add_filter('manage_pdfposter_posts_columns', [$this, 'pdfp_columns_head_only_podcast'], 10);
                add_action('manage_pdfposter_posts_custom_column', [$this, 'pdfp_columns_content_only_podcast'], 10, 2);
                add_filter('post_updated_messages', [$this, 'pdfp_updated_messages']);

                add_action('admin_head-post.php', [$this, 'pdfp_hide_publishing_actions']);
                add_action('admin_head-post-new.php', [$this, 'pdfp_hide_publishing_actions']);
                add_filter('gettext', [$this, 'pdfp_change_publish_button'], 10, 2);

                add_filter('filter_block_editor_meta_boxes', [$this, 'remove_metabox']);
                add_action('use_block_editor_for_post', [$this, 'forceGutenberg'], 10, 2);


                add_action('edit_form_after_title', [$this, 'shortcode_area']);

                // add_action('add_meta_boxes', [$this, 'myplugin_add_meta_box']);
            }
        }

        /**
         * Create instance function
         */
        public static function instance()
        {
            if (self::$_instance === null) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * init
         */
        public function init()
        {

            register_post_type(
                'pdfposter',
                array(
                    'labels' => array(
                        'name' => __('PDF Poster', 'pdf-poster'),
                        'singular_name' => __('PDF Poster', 'pdf-poster'),
                        'add_new' => __('Add New PDF', 'pdf-poster'),
                        'add_new_item' => __('Add New', 'pdf-poster'),
                        'edit_item' => __('Edit', 'pdf-poster'),
                        'new_item' => __('New PDF', 'pdf-poster'),
                        'view_item' => __('View PDF', 'pdf-poster'),
                        'search_items' => __('Search PDF', 'pdf-poster'),
                        'all_items' => __('All PDF Posters', 'pdf-poster'),
                        'not_found' => __('Sorry, we couldn\'t find the PDF file you are looking for.', 'pdf-poster')
                    ),
                    'public' => false,
                    'show_ui' => true,
                    // 'publicly_queryable' => true,
                    // 'exclude_from_search' => true,
                    'show_in_rest' => true,
                    'menu_position' => 14,
                    'menu_icon' => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCEtLSBMaWNlbnNlOiBNSVQuIE1hZGUgYnkgR2FydWRhIFRlY2hub2xvZ3k6IGh0dHBzOi8vZ2l0aHViLmNvbS9nYXJ1ZGF0ZWNobm9sb2d5ZGV2ZWxvcGVycy9za2V0Y2gtaWNvbnMgLS0+Cjxzdmcgd2lkdGg9IjgwMHB4IiBoZWlnaHQ9IjgwMHB4IiB2aWV3Qm94PSItNCAwIDQwIDQwIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8cGF0aCBkPSJNMjUuNjY4NiAyNi4wOTYyQzI1LjE4MTIgMjYuMjQwMSAyNC40NjU2IDI2LjI1NjMgMjMuNjk4NCAyNi4xNDVDMjIuODc1IDI2LjAyNTYgMjIuMDM1MSAyNS43NzM5IDIxLjIwOTYgMjUuNDAzQzIyLjY4MTcgMjUuMTg4OCAyMy44MjM3IDI1LjI1NDggMjQuODAwNSAyNS42MDA5QzI1LjAzMTkgMjUuNjgyOSAyNS40MTIgMjUuOTAyMSAyNS42Njg2IDI2LjA5NjJaTTE3LjQ1NTIgMjQuNzQ1OUMxNy4zOTUzIDI0Ljc2MjIgMTcuMzM2MyAyNC43Nzc2IDE3LjI3NzYgMjQuNzkzOUMxNi44ODE1IDI0Ljc2MjIgMTYuNDk2MSAyNS4wMDY5IDE2LjEyNDcgMjUuMTAwNUwxNS42MjM5IDI1LjIyNzVDMTQuNjE2NSAyNS40ODI0IDEzLjU4NjUgMjUuNzQyOCAxMi41NjkyIDI2LjA1MjlDMTIuOTU1OCAyNS4xMjA2IDEzLjMxNSAyNC4xNzggMTMuNjY2NyAyMy4yNTY0QzEzLjkyNzEgMjIuNTc0MiAxNC4xOTMgMjEuODc3MyAxNC40NjggMjEuMTg5NEMxNC42MDc1IDIxLjQxOTggMTQuNzUzMSAyMS42NTAzIDE0LjkwNDYgMjEuODgxNEMxNS41OTQ4IDIyLjk3MzYgMTYuNDk2MiAyMy45MDQ1IDE3LjQ1NTIgMjQuNzQ1OVpNMTQuODkyNyAxNC4yMzI2Wk05LjYzMzQ3IDI4LjgwNTRDOS4zODE0OCAyOS4yNTYyIDkuMTI0MjYgMjkuNjc4MiA4Ljg2MDYzIDMwLjA3NjdDOC4yMjQ0MiAzMS4wMzU1IDcuMTgzOTMgMzIuMDYyMSA2LjY0OTQxIDMyLjA2MjFDNi41OTY4MSAzMi4wNjIxIDYuNTMzMTYgMzIuMDUzNiA2LjQ0MDE1IDMxLjk1NTRDNi4zODAyOCAzMS44OTI2IDYuMzcwNjkgMzEuODQ3NiA2LjM3MzU5IDMxLjc4NjJDNi4zOTE2MSAzMS40MzM3IDYuODU4NjcgMzAuODA1OSA3LjUzNTI3IDMwLjIyMzhDOC4xNDkzOSAyOS42OTU3IDguODQzNTIgMjkuMjI2MiA5LjYzMzQ3IDI4LjgwNTRaTTI3LjM3MDYgMjYuMTQ2MUMyNy4yODg5IDI0Ljk3MTkgMjUuMzEyMyAyNC4yMTg2IDI1LjI5MjggMjQuMjExNkMyNC41Mjg3IDIzLjk0MDcgMjMuNjk4NiAyMy44MDkxIDIyLjc1NTIgMjMuODA5MUMyMS43NDUzIDIzLjgwOTEgMjAuNjU2NSAyMy45NTUyIDE5LjI1ODIgMjQuMjgxOUMxOC4wMTQgMjMuMzk5OSAxNi45MzkyIDIyLjI5NTcgMTYuMTM2MiAyMS4wNzMzQzE1Ljc4MTYgMjAuNTMzMiAxNS40NjI4IDE5Ljk5NDEgMTUuMTg0OSAxOS40Njc1QzE1Ljg2MzMgMTcuODQ1NCAxNi40NzQyIDE2LjEwMTMgMTYuMzYzMiAxNC4xNDc5QzE2LjI3MzcgMTIuNTgxNiAxNS41NjcyIDExLjUyOTUgMTQuNjA2OSAxMS41Mjk1QzEzLjk0OCAxMS41Mjk1IDEzLjM4MDcgMTIuMDE3NSAxMi45MTk0IDEyLjk4MTNDMTIuMDk2NSAxNC42OTg3IDEyLjMxMjggMTYuODk2MiAxMy41NjIgMTkuNTE4NEMxMy4xMTIxIDIwLjU3NTEgMTIuNjk0MSAyMS42NzA2IDEyLjI4OTUgMjIuNzMxMUMxMS43ODYxIDI0LjA0OTggMTEuMjY3NCAyNS40MTAzIDEwLjY4MjggMjYuNzA0NUM5LjA0MzM0IDI3LjM1MzIgNy42OTY0OCAyOC4xMzk5IDYuNTc0MDIgMjkuMTA1N0M1LjgzODcgMjkuNzM3MyA0Ljk1MjIzIDMwLjcwMjggNC45MDE2MyAzMS43MTA3QzQuODc2OTMgMzIuMTg1NCA1LjAzOTY5IDMyLjYyMDcgNS4zNzA0NCAzMi45Njk1QzUuNzIxODMgMzMuMzM5OCA2LjE2MzI5IDMzLjUzNDggNi42NDg3IDMzLjUzNTRDOC4yNTE4OSAzMy41MzU0IDkuNzk0ODkgMzEuMzMyNyAxMC4wODc2IDMwLjg5MDlDMTAuNjc2NyAzMC4wMDI5IDExLjIyODEgMjkuMDEyNCAxMS43Njg0IDI3Ljg2OTlDMTMuMTI5MiAyNy4zNzgxIDE0LjU3OTQgMjcuMDExIDE1Ljk4NSAyNi42NTYyTDE2LjQ4ODQgMjYuNTI4M0MxNi44NjY4IDI2LjQzMjEgMTcuMjYwMSAyNi4zMjU3IDE3LjY2MzUgMjYuMjE1M0MxOC4wOTA0IDI2LjA5OTkgMTguNTI5NiAyNS45ODAyIDE4Ljk3NiAyNS44NjY1QzIwLjQxOTMgMjYuNzg0NCAyMS45NzE0IDI3LjM4MzEgMjMuNDg1MSAyNy42MDI4QzI0Ljc2MDEgMjcuNzg4MyAyNS44OTI0IDI3LjY4MDcgMjYuNjU4OSAyNy4yODExQzI3LjM0ODYgMjYuOTIxOSAyNy4zODY2IDI2LjM2NzYgMjcuMzcwNiAyNi4xNDYxWk0zMC40NzU1IDM2LjI0MjhDMzAuNDc1NSAzOC4zOTMyIDI4LjU4MDIgMzguNTI1OCAyOC4xOTc4IDM4LjUzMDFIMC43NDQ4NkMxLjYwMjI0IDM4LjUzMDEgMS40NzMyMiAzNi42MjE4IDEuNDY5MTMgMzYuMjQyOEwxLjQ2ODg0IDMuNzU2NDJDMS40Njg4NCAxLjYwMzkgMy4zNjc2MyAxLjQ3MzQgMy4zNjc2MyAxLjQ2OTA4SDIwLjI2M0wyMC4yNzE4IDEuNDc3OFY3LjkyMzk2QzIwLjI3MTggOS4yMTc2MyAyMS4wNTM5IDExLjY2NjkgMjQuMDE1OCAxMS42NjY5SDMwLjQyMDNMMzAuNDc1MyAxMS43MjE4TDMwLjQ3NTUgMzYuMjQyOFpNMjguOTU3MiAxMC4xOTc2SDI0LjAxNjlDMjEuODc0OSAxMC4xOTc2IDIxLjc0NTMgOC4yOTk2OSAyMS43NDI0IDcuOTI0MTdWMi45NTMwN0wyOC45NTcyIDEwLjE5NzZpTTMxLjk0NDcgMzYuMjQyOFYxMS4xMTU3TDIxLjc0MjQgMC44NzEwMjJWLjgyMzM1N0gyMS42OTM2TDIwLjg3NDIgMEgzLjc0NDkxQzIuNDQ5NTQgMCAwIDAuNzg1MzM2IDAgMy43NTcxMVYzNi4yNDM1QzAgMzcuNTQyNyAwLjc4Mjk1NiA0MCAzLjc0NDkxIDQwSDI4LjIwMDFDMjkuNDk1MiAzOS45OTk3IDMxLjk0NDcgMzkuMjE0MyAzMS45NDQ3IDM2LjI0MjhWIiBmaWxsPSIjRUI1NzU3Ii8+Cjwvc3ZnPg==',
                    'has_archive' => false,
                    'hierarchical' => false,
                    'capability_type' => 'post',
                    'rewrite' => array('slug' => 'pdfposter'),
                    'supports' => array('title', 'editor'),
                    'template' => [
                        ['pdfp/pdfposter']
                    ],
                    'template_lock' => 'all',
                )
            );
        }

        /**
         * Remove Row
         */
        function pdfp_remove_row_actions($idtions)
        {
            global $post;
            if (!$post) {
                return $idtions;
            }
            if ($post->post_type == $this->post_type) {
                unset($idtions['view']);
                unset($idtions['inline hide-if-no-js']);
            }
            return $idtions;
        }

        // CREATE TWO FUNCTIONS TO HANDLE THE COLUMN
        function pdfp_columns_head_only_podcast($defaults)
        {
            unset($defaults['date']);
            $defaults['shortcode'] = __('ShortCode', 'pdf-poster');
            $defaults['raw_shortCode'] = esc_html__('ShortCode For Raw PDF View', 'pdf-poster');
            $defaults['date'] = __('Date', 'pdf-poster');
            return $defaults;
        }

        function pdfp_columns_content_only_podcast($column_name, $post_ID)
        {
            if ($column_name == 'shortcode') {
                echo '<div class="pdfp_front_shortcode"><input class="pdfp_front_shortcode_input"  value="' . esc_attr__('Copy Shortcode', 'pdf-poster') . '" data-value="[pdf id=' . esc_attr($post_ID) . ']" ><span class="htooltip">' . esc_html__('Copy To Clipboard', 'pdf-poster') . '</span></div>';
            }
            if ($column_name == 'raw_shortCode') {
                // show content of 'directors_name' column
                echo '<div class="pdfp_front_shortcode"><input class="pdfp_front_shortcode_input"  value="' . esc_attr__('Copy Shortcode', 'pdf-poster') . '" data-value="[raw_pdf id=' . esc_attr($post_ID) . ']" ><span class="htooltip">' . esc_html__('Copy To Clipboard', 'pdf-poster') . '</span></div>';
            }
        }

        function pdfp_updated_messages($messages)
        {
            $messages[$this->post_type][1] = __('Player updated ', 'pdf-poster');
            return $messages;
        }

        public function pdfp_hide_publishing_actions()
        {
            global $post;
            if (!$post) {
                return;
            }
            if ($post->post_type == $this->post_type) {
                echo '
                <style type="text/css">
                    #misc-publishing-actions,
                    #minor-publishing-actions{
                        display:none;
                    }
                </style>
            ';
            }
        }

        function remove_metabox($metaboxs)
        {
            global $post;
            $screen = get_current_screen();

            if (!$screen) {
                return $metaboxs;
            }

            if ($screen->post_type === $this->post_type) {
                return false;
            }
            return $metaboxs;
        }

        public function forceGutenberg($use, $post)  {
            $option = get_option('fpdf_option', []);
            if (isset($option['pdfp_gutenberg_enable'])) {
                $gutenberg = (bool) $option['pdfp_gutenberg_enable'];
            } else {
                $gutenberg = (bool) get_option('pdfp_gutenberg_enable', false);
            }
            $isGutenberg = (bool) get_post_meta($post->ID, 'isGutenberg', true);
            $pluginUpdated = 1630223686;
            $publishDate = get_the_date('U', $post);
            $currentTime = current_time("U");

            if ($this->post_type === $post->post_type) {
                if ($gutenberg) {
                    if ($post->post_status == 'auto-draft') {
                        update_post_meta($post->ID, 'isGutenberg', true);
                        return true;
                    } else {
                        if ($isGutenberg) {
                            return true;
                        } else {
                            remove_post_type_support($this->post_type, 'editor');
                            return false;
                        }
                    }
                } else {
                    if ($isGutenberg) {
                        return true;
                    } else {
                        remove_post_type_support($this->post_type, 'editor');
                        return false;
                    }
                }
            }

            return $use;
        }

        function pdfp_change_publish_button($translation, $text)
        {
            if ($this->post_type == get_post_type())
                if ($text == 'Publish')
                    return 'Save';
            return $translation;
        }

        /**
         * register metabox
         */
        function myplugin_add_meta_box()
        {
            add_meta_box(
                'Shortcode',
                __('New Feature ! Quick Embed', 'pdf-poster'),
                [$this, 'pdfp_pro_shortcode_wid'],
                'pdfposter',
                'side',
                'default'
            );
        }

        function shortcode_area()
        {

            if ($this->post_type != get_post_type()) {
                return;
            }
            global $post;
            $id = $post->ID;

            $shortcode = "[pdf id='" . esc_attr($id) . "']";
            ?>
            <div class="pdfp_shortcode_area_after_title">
                <label><?php esc_html_e('Copy and paste this shortcode into your posts, pages and widget', 'pdf-poster'); ?></label>
                <div class="shortcode_area">
                    <button class="button button-bplugins button-large pdfp_shortcode_copy_btn"
                        data-clipboard-text="<?php echo esc_attr($shortcode) ?>"><?php echo esc_html($shortcode); ?></button>
                    <svg class='pdfp_shortcode_copy_btn' data-type="icon" data-clipboard-text='<?php echo esc_attr($shortcode) ?>'
                        width='22px' height='22px' viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'>
                        <path
                            d='M8 4V16C8 17.1046 8.89543 18 10 18L18 18C19.1046 18 20 17.1046 20 16V7.24162C20 6.7034 19.7831 6.18789 19.3982 5.81161L16.0829 2.56999C15.7092 2.2046 15.2074 2 14.6847 2H10C8.89543 2 8 2.89543 8 4Z'
                            stroke='#000000' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' />
                        <path d='M16 18V20C16 21.1046 15.1046 22 14 22H6C4.89543 22 4 21.1046 4 20V9C4 7.89543 4.89543 7 6 7H8'
                            stroke='#000000' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' />
                    </svg>
                </div>
            </div>
            <?php
        }

        function pdfp_pro_shortcode_wid()
        {
            $shortcode = "[pdf_embed url='your_file_url']";
            echo esc_html__('Now you can embed pdf without listing ! just use the Embed shortCode below, and start saving your time.', 'pdf-poster');
            echo '<br/><br/><input type="text" style="font-size: 12px; border: none; box-shadow: none; padding: 4px 8px; width:100%; background:#1e8cbe; color:white;"  onfocus="this.select();" readonly="readonly"  value="' . esc_attr($shortcode) . '" /><br/><br/>';
            echo '<p><a class="button button-primary button-large" href="admin.php?page=fpdf-settings" target="_blank">' . esc_html__('ShortCode Global Settings', 'pdf-poster') . '</a></p>';
        }


        /**
         * Add the ↳ arrow to the sidebar "Add New" submenu item only, not the page-title button.
         *
         * @return void
         */
        public function arrowAddNewSubmenu()
        {
            global $submenu;
            $menu_slug = 'edit.php?post_type=' . $this->post_type;
            if (empty($submenu[$menu_slug])) {
                return;
            }
            foreach ($submenu[$menu_slug] as &$item) {
                if (isset($item[2]) && 0 === strpos($item[2], 'post-new.php?post_type=' . $this->post_type)) {
                    $item[0] = '<span aria-hidden="true">&#8627;</span> ' . $item[0];
                    break;
                }
            }
            unset($item);
        }
    }
}
