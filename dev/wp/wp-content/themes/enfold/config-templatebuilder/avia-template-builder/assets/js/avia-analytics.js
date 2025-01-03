(function ($)
{
    "use strict";

    $.AviaAnalytics = function ()
    {
        this.body = $('body').eq(0);
        this.alb = $('#avia_builder');
        this.builder = $('#aviaLayoutBuilder');
        this.shortcodes = $('#_aviaLayoutBuilderCleanData');
        this.classic = null;
        this.gutenberg = null;
        this.blocks = null;
        this.rawdata = '';
        this.data = '';
        this.delay = $('body').is('.avia-seo-analysis-no-delay') ? 1000 : 5000;
        this.unsubscribe = null;

        this.get_tinymce_editor();
        this.register_tiny_events();
        this.register_gutenberg_events();
        this.register_listeners();
    }

    $.AviaAnalytics.prototype = {
        /**
        * Register builder and other events.
        */
        register_listeners: function ()
        {
            var obj = this;

            this.body.on('AviaBuilder_after_switch_layout_mode', function (event, button)
            {

                if (obj.is_block_editor() == false) return;

                var builder_active = $(button).is('.avia-builder-active');

                if (builder_active) 
                {
                    obj.builder.off('avia-analytics-for-blocks-ready');

                    obj.unsubscribe();
                } else 
                {
                    obj.register_gutenberg_events();
                }
            });
        },

        /** 
        * Register WP classic editor events.
        */
        register_tiny_events: function ()
        {
            var obj = this;

            if (
                typeof tinyMCE === "undefined" ||
                typeof tinyMCE.editors === "undefined" ||
                typeof tinyMCE.activeEditor === "undefined")
            {
                return;
            }

            $('#content-tmce').on('click', function () 
            {
                if (obj.classic) return;

                setTimeout(function () 
                {
                    obj.get_tinymce_editor();
                    obj.register_tiny_events();
                }, 100);
            });

            /**
            * Cannot load tinymce editor because text mode is enabled,
            * manually switch to visual mode, then immediately switch back 
            * to initialize tinyMCE, might require a different solution.
            */
            if (this.classic == null) 
            {
                $('#content-tmce').trigger('click');

                setTimeout(function ()
                {
                    $('#content-html').trigger('click');
                }, 200);

                return;
            }

            this.classic.onChange.add(
                _.debounce(function (ed, e)
                {
                    obj.convert_shortcodes();
                }, obj.delay));

            this.classic.onInit.add(
                _.debounce(function (ed, e)
                {
                    obj.convert_shortcodes();
                }, 1000));
        },

        /**
        * Register block editor events.
        */
        register_gutenberg_events: async function ()
        {
            if (
                this.is_block_editor() == false ||
                this.is_alb_active() == true)
            {
                return;
            }

            var obj = this,
                get_blocks = () => this.get_gutenberg_blocks(),
                blocks = get_blocks();

            this.body.on('avia-analytics-for-blocks-ready', function ()
            {
                var get_raw = () => obj.get_raw_data(),
                    raw = get_raw();

                obj.convert_shortcodes(raw);
            });

            this.unsubscribe = wp.data.subscribe(_.debounce(function ()
            {
                var newblocks = get_blocks(),
                    changed = newblocks !== blocks;

                blocks = newblocks;

                if (changed)
                {
                    obj.body.trigger('avia-analytics-for-blocks-ready');
                }
            }, obj.delay));
        },

        /**
        * Get the unprocessed data.
        */
        get_raw_data: function ()
        {
            this.rawdata = wp.data.select('core/editor').getEditedPostContent();

            return this.rawdata;
        },

        /**
        * Retrieve the default tinymce editor with the ID content.
        */
        get_tinymce_editor()
        {
            this.classic = tinyMCE.get('content');
        },

        /**
        * Retrieve the content of the active editor.
        */
        get_editor_content: function ()
        {
            var content = this.is_alb_active() ? this.shortcodes.val() : this.classic.getContent();

            return content;
        },

        /**
        * Get editor blocks.
        */
        get_gutenberg_blocks: function ()
        {
            return wp.data.select('core/editor').getBlocks();
        },

        /**
        * Convert clean data field shortcodes to actual html.
        */
        convert_shortcodes: function (content = null)
        {
            var obj = this;

            $.ajax({
                type: "POST",
                url: ajaxurl,
                data:
                {
                    action: 'avia_ajax_text_to_preview',
                    text: content == null ? obj.get_editor_content() : content,
                    avia_request: true,
                    post_type: $('.avia-builder-main-wrap').data('post_type'),
                    _ajax_nonce: $('#avia-loader-nonce').val()
                },
                success: function (response) 
                {
                    obj.data = response;

                    setTimeout(function () 
                    {
                        obj.body.trigger('avia-analytics-ready');
                    }, 100);
                }
            });
        },

        /**
        * Retrieve the html of the advance layout builder shortcodes.
        */
        retrieve_data: function () 
        {
            return this.data;
        },

        /**
        * Check whether the advance layout builder is active or not.
        */
        is_alb_active: function () 
        {
            return this.alb.length !== 0 && this.alb.is('.avia-hidden') == false;
        },

        /**
        * Check if the block editor is active.
        */
        is_block_editor: function () 
        {
            return $('body').hasClass('block-editor-page');
        }
    }
})(jQuery);
