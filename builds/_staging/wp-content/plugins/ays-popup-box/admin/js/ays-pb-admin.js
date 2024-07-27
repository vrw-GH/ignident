(function($) {
	'use strict'

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

    $.fn.scrollToElementTop = function() {
        var offsetTop = this.offset().top;

        $('html, body').animate({
            scrollTop: offsetTop
        }, 'slow');
    };

    $(document).ready(function() {
        // Fix list table pagination issue start
        if ($('.pagination-links:visible').length) {
            if ($('#ays-popup-box-search-input').length) {
                var search_string = $('#ays-popup-box-search-input').val();
                if (search_string != '') {
                    $('.pagination-links a').each(function() {
                        this.href = this.href + '&s=' + search_string;
                    });
                }
            }
        }
        // Fix list table pagination issue end

        // Create color pickers start
        let ays_pb_box_gradient_color1_picker = {
            change: function(e) {
                setTimeout(function() {
                    toggleBackgrounGradient();
                }, 1);
            }
        };
        let ays_pb_box_gradient_color2_picker = {
            change: function(e) {
                setTimeout(function() {
                    toggleBackgrounGradient();
                }, 1);
            }
        };

        $(document).find('.ays_pb_color_input').wpColorPicker();
        $(document).find('#ays_pb_button_text_color').wpColorPicker();
        $(document).find('#ays_pb_close_button_color').wpColorPicker();
        $(document).find('#ays_pb_close_button_hover_color').wpColorPicker();
        $(document).find('#ays_title_text_shadow_color_mobile').wpColorPicker();
        $(document).find('#ays_pb_box_shadow_color').wpColorPicker();
        $(document).find('#ays_pb_box_shadow_color_mobile').wpColorPicker();
        $(document).find('#ays_pb_button_background_color').wpColorPicker();
        $(document).find('#ays-background-gradient-color-1').wpColorPicker(ays_pb_box_gradient_color1_picker);
        $(document).find('#ays-background-gradient-color-2').wpColorPicker(ays_pb_box_gradient_color2_picker);
        $(document).find('#ays-background-gradient-color-1-mobile').wpColorPicker();
        $(document).find('#ays-background-gradient-color-2-mobile').wpColorPicker();
        // Create color pickers end

        // Create select2 start
        $(document).find('#ays_users_roles').select2();
        var ays_pb_view_place = $(document).find('#ays-pb-ays_pb_view_place').select2({
            placeholder: 'Select page',
            multiple: true,
            matcher: searchForPage
        });

        $(document).find('#ays_pb_posts').select2({
            placeholder: 'Select page',
            multiple: true,
            matcher: searchForPage
        });

        $(document).find('#ays_pb_create_author').select2({
            placeholder: 'Select users',
            minimumInputLength: 1,
            allowClear: true,
            language: {
                inputTooShort: function() {
                    return pb.pleaseEnterMore;
                }
            },
            ajax: {
                url: pb.ajax,
                dataType: 'json',
                data: function(response) {
                    var checkedUsers = $(document).find('#ays_pb_create_author').val();

                    return {
                        action: 'ays_pb_create_author',
                        search: response.term,
                        val: checkedUsers,
                    };
                },
            }
        });

        $(document).find('#ays_pb_post_types').select2({
            placeholder: 'Select page',
            multiple: true,
            matcher: searchForPage
        });
        // Create select2 end

        // Sortable start
        $(document).find('ul.ays_notification_type_components_sortable').sortable({
            cursor: 'move',
            opacity: 0.8,
            tolerance: 'pointer',
            helper: 'clone',
            placeholder: 'ays_notification_type_components_sortable_placeholder',
            revert: true,
            forcePlaceholderSize: true,
            forceHelperSize: true,
        });
        // Sortable end

        // Datetimepicker start
        $(document).find('.ays_pb_act_dect, #ays_pb_change_creation_date').datetimepicker({
            controlType: 'select',
            oneLine: true,
            dateFormat: 'yy-mm-dd',
            timeFormat: 'HH:mm:ss',
            afterInject: function() {
                $(document).find('.ui-datepicker-buttonpane button.ui-state-default').addClass('button');
                $(document).find('.ui-datepicker-buttonpane button.ui-state-default.ui-priority-primary').addClass('button-primary').css('float', 'right');
            }
        });
        // Datetimepicker end

        // Dropdown start
        $(document).find('.ays_pb_aysDropdown').aysDropdown();
        $(document).find('[data-toggle="dropdown"]').dropdown();
        // Dropdown end

        // Code Mirror start
        setTimeout(function() {
            if ($(document).find('#ays-pb-custom-css').length > 0) {
                let CodeEditor = null;

                if (wp.codeEditor) {
                    CodeEditor = wp.codeEditor.initialize($(document).find('#ays-pb-custom-css'), cm_settings);
                }

                if (CodeEditor !== null) {
                    CodeEditor.codemirror.on('change', function(e, ev) {
                        $(CodeEditor.codemirror.display.input.div).find('.CodeMirror-linenumber').remove();
                        $(document).find('#ays-pb-custom-css').val(CodeEditor.codemirror.display.input.div.innerText);
                    });
                }
            }
        }, 500);

        $(document).find('a[href="#tab3"]').on('click', function(e) {
            setTimeout(function() {
                if ($(document).find('#ays-pb-custom-css').length > 0) {
                    var ays_pb_custom_css = $(document).find('#ays-pb-custom-css').html();

                    if (wp.codeEditor) {
                        $(document).find('#ays-pb-custom-css').next('.CodeMirror').remove();
                        var CodeEditor = wp.codeEditor.initialize($(document).find('#ays-pb-custom-css'), cm_settings);

                        CodeEditor.codemirror.on('change', function(e, ev) {
                            $(CodeEditor.codemirror.display.input.div).find('.CodeMirror-linenumber').remove();
                            $(document).find('#ays-pb-custom-css').val(CodeEditor.codemirror.display.input.div.innerText).trigger('change');
                        });

                        ays_pb_custom_css = CodeEditor.codemirror.getValue();
                        $(document).find('#ays-pb-custom-css').html(ays_pb_custom_css);
                    }
                }
            }, 500);
        });
        // Code Mirror end

        // Starter variables declare start
        var toggle_ddmenu = $(document).find('.toggle_ddmenu');
        var ays_pb_overlay_color = $(document).find('#ays-pb-overlay_color').val();
        var wp_editor_height = $(document).find('.quiz_wp_editor_height');
        var menuItemWidths0 = [];
        var menuItemWidths = [];
        var menuItemWidth = 0;
        var subButtons = '.button#ays-button-top, .button#ays-button-top-apply, .button#ays-button, .button#ays-button-apply, .button#ays_submit_settings';
        // Starter variables declare end

        // Set wpeditor height start
        if (wp_editor_height.length > 0) {
            var wp_editor_height_val = wp_editor_height.val();

            if (wp_editor_height_val != '' && wp_editor_height_val != 0) {
                var ays_pb_wp_editor = setInterval(function() {
                    if (document.readyState === 'complete') {
                        $(document).find('.wp-editor-wrap .wp-editor-container iframe , .wp-editor-container textarea.wp-editor-area').css({
                            'height': wp_editor_height_val + 'px'
                        });
                        clearInterval(ays_pb_wp_editor);
                    }
                }, 500);
            }
        }
        // Set wpeditor height end

        // Live preview first load styles start
        $(document).find('.ays-pb-modals').css('background-color', ays_pb_overlay_color);
        toggleBackgrounGradient();
        aysCheckPopupPosition();
        aysCheckBgImagePosition();

        if ($('input#ays_popup_content_padding').val() == 20 && $('#ays_popup_padding_by_percentage_px').val() == 'pixels') {
            if ($('input[name="ays-pb[view_type]"]:checked').val() != 'minimal') {
                if (!$('.ays-pb-padding-content').hasClass('ays-pb-padding-content-default')) {
                    $('.ays-pb-padding-content').addClass('ays-pb-padding-content-default');
                }
            } else {
                if ($('.ays-pb-padding-content').hasClass('ays-pb-padding-content-default')) {
                    $('.ays-pb-padding-content').removeClass('ays-pb-padding-content-default');
                }
            }
        } else if ($('input#ays_popup_content_padding').val() == 0 && $('#ays_popup_padding_by_percentage_px').val() == 'pixels') {
            if ($('input[name="ays-pb[view_type]"]:checked').val() == 'minimal') {
                if (!$('.ays-pb-padding-content').hasClass('ays-pb-padding-content-default')) {
                    $('.ays-pb-padding-content').addClass('ays-pb-padding-content-default');
                }
            } else {
                if ($('.ays-pb-padding-content').hasClass('ays-pb-padding-content-default')) {
                    $('.ays-pb-padding-content').removeClass('ays-pb-padding-content-default');
                }
            }
        } else {
            if ($('.ays-pb-padding-content').hasClass('ays-pb-padding-content-default')) {
                $('.ays-pb-padding-content').removeClass('ays-pb-padding-content-default');
            }
        }
        // Live preview first load styles end

        // Popup inputs toggles start
        $(document).on('change', '.ays_toggle', function(e) {
            let state = $(this).prop('checked');

            if ($(this).hasClass('ays_toggle_slide_mobile_option')) {
                switch (state) {
                    case true:
                        $(this).parents('.ays_toggle_slide_mobile_option_container').find('.ays_toggle_target').slideDown(250);
                        break;
                    case false:
                        $(this).parents('.ays_toggle_slide_mobile_option_container').find('.ays_toggle_target').slideUp(250);
                        break;
                }
            } else if ($(this).hasClass('ays_toggle_slide')) {
                switch (state) {
                    case true:
                        $(this).parent().find('.ays_toggle_target').slideDown(250);
                        break;
                    case false:
                        $(this).parent().find('.ays_toggle_target').slideUp(250);
                        break;
                }
            } else {
                switch (state) {
                    case true:
                        $(this).parent().find('.ays_toggle_target').show(250);
                        break;
                    case false:
                        $(this).parent().find('.ays_toggle_target').hide(250);
                        break;
                }
            }
        });

        $(document).on('change', '.ays_toggle_checkbox', function(e) {
            let state = $(this).prop('checked');
            let parent = $(this).parents('.ays_toggle_parent');

            if ($(this).hasClass('ays_toggle_slide')) {
                switch (state) {
                    case true:
                        parent.find('.ays_toggle_target').slideDown(250);
                        break;
                    case false:
                        parent.find('.ays_toggle_target').slideUp(250);
                        break;
                }
            } else {
                switch (state) {
                    case true:
                        parent.find('.ays_toggle_target').show(250);
                        break;
                    case false:
                        parent.find('.ays_toggle_target').hide(250);
                        break;
                }
            }
        });

        $(document).on('input', '.ays_toggle_input', function(e) {
            let state = $(this).val() > 0;
            let parent = $(this).parents('.ays_toggle_parent');

            switch (state) {
                case true:
                    parent.find('.ays_toggle_target').show(250);
                    break;
                case false:
                    parent.find('.ays_toggle_target').hide(250);
                    break;
            }
        });

        $(document).on('change', '.ays_toggle_checkbox_dismiss_option', function(e) {
            let state = $(this).prop('checked');
            let parent = $(this).closest('.ays_toggle_parent_dismiss_option');

            switch (state) {
                case true:
                    parent.find('.ays_toggle_target_dismiss_option').show(250);
                    break;
                case false:
                    parent.find('.ays_toggle_target_dismiss_option').hide(250);
                    break;
            }
        });
        // Popup inputs toggles end

        // General notice menu toggle start
        toggle_ddmenu.on('click', function() {
            var ddmenu = $(this).next();
            var state = ddmenu.attr('data-expanded');
            switch (state) {
                case 'true':
                    $(this).find('img').css({
                        transform: 'rotate(0deg)'
                    });
                    ddmenu.attr('data-expanded', 'false');
                    break;
                case 'false':
                    $(this).find('img').css({
                        transform: 'rotate(90deg)'
                    });
                    ddmenu.attr('data-expanded', 'true');
                    break;
            }
        });
        // General notice menu toggle end

        // List table | Filter start
        $(document).find('.ays-popup-question-tab-all-filter-button-top, .ays-popup-question-tab-all-filter-button-bottom').on('click', function(e) {
            e.preventDefault();
            var $this = $(this);
            var parent = $this.parents('.tablenav');

            var html_name = '';
            var top_or_bottom = 'top';

            if (parent.hasClass('bottom')) {
                top_or_bottom = 'bottom';
            }

            var catFilter = $(document).find('select[name="filterby-'+ top_or_bottom +'"]').val();
            var authorFilter = $(document).find('select[name="filterbyAuthor-'+ top_or_bottom +'"]').val();
            var typeFilter = $(document).find('select[name="filterbyType-'+ top_or_bottom +'"]').val();
            var link = location.href;

            if (typeof catFilter != 'undefined') {
                link = catFilterForListTable(link, {
                    what: 'filterby',
                    value: catFilter
                });
            }
            if (typeof authorFilter != 'undefined') {
                link = catFilterForListTable(link, {
                    what: 'filterbyAuthor',
                    value: authorFilter
                });
            }
            if (typeof typeFilter != 'undefined') {
                link = catFilterForListTable(link, {
                    what: 'filterbyType',
                    value: typeFilter
                });
            }

            document.location.href = link;
        })
        // List table | Filter end

        // List table | Delete confiramtions start
        $(document).find('input[type="submit"]#doaction, input[type="submit"]#doaction2').on('click', function(e) {
            showConfirmationIfDelete(e);
        })

        $(document).on('click', '.ays_pb_confirm_del', function(e) {
            e.preventDefault();

            var message = $(this).attr('data-message');
            var confirm = window.confirm('Are you sure you want to delete ' + message + '?');

            if (confirm === true) {
                window.location.replace($(this).attr('href'));
            }
        });
        // List table | Delete confiramtions end

        // Choose popup type start
        $(document).find('.ays_pb_layer_box_blocks .ays-pb-dblclick-layer').on('click',function(e) {
            if ( !($(e.target).hasClass('ays-pb-view-demo-content')) ) {
                $(this).parents('.ays_pb_layer_container').find('.ays_pb_select_button_layer input.ays_pb_layer_button').trigger('click');
            }
        });

        $(document).find('.ays_pb_layer_button').on('click', function() {
            $('.ays_pb_layer_container').css({'position':'unset' , 'display':'none'});

            var checkedInp = $('.ays_pb_layer_box input:checked').val();
            if (!checkedInp) return;

            let typeText = 'Custom Content';
            let typeVideoLink = '';
            switch (checkedInp) {
                case 'shortcode':
                    typeText = 'Shortcode';
                    typeVideoLink = '<a href="https://www.youtube.com/watch?v=q6ai1WhpLfc">Watch how to add a shortcode popup</a>';

                    $('#ays_shortcode').removeClass('display_none');
                    $('.ays_shortcode_hr').removeClass('display_none');
                    break;
                case 'custom_html':
                    $('#ays_custom_html').removeClass('display_none');
                    $('.ays_custom_html_hr').removeClass('display_none');
                    break;
                case 'video_type':
                    typeText = 'Video';
                    typeVideoLink = '<a href="https://www.youtube.com/watch?v=oOvHTcePpys">Watch how to add a video popup</a>';
                    
                    $(document).find('#ays_video_type').removeClass('display_none');
                    $(document).find('.ays_video_type_hr').removeClass('display_none');
                    $(document).find('.ays_pb_hide_for_video_type').addClass('display_none');                    

                    $(document).find('.ays-pb-live-container').addClass('display_none');
                    $(document).find('.ays-pb-live-container').removeClass('ays_active');
                    $(document).find('.ays-pb-live-container.ays_video_window').removeClass('display_none');
                    $(document).find('.ays-pb-live-container.ays_video_window').addClass('ays_active');

                    $(document).find('input#video_theme_view_type').prop('checked',true);
                    $(document).find('.ays_pb_autoclose_on_completion_container').removeClass('display_none');
                break;
                case 'image_type':
                    typeText = 'Image';

                    $(document).find('#ays_image_type').removeClass('display_none');
                    $(document).find('.ays_image_type_hr').removeClass('display_none');
                    $(document).find('.ays_pb_hide_for_image_type').addClass('display_none');
                    
                    $(document).find('.ays-pb-live-container').addClass('display_none');
                    $(document).find('.ays-pb-live-container').removeClass('ays_active');
                    $(document).find('.ays-pb-live-container.ays_image_type_img_window').removeClass('display_none');
                    $(document).find('.ays-pb-live-container.ays_image_type_img_window').addClass('ays_active');

                    $(document).find('.ays-pb-live-container.ays_image_type_img_window .ays_pb_timer').css({'visibility':'hidden' });

                    $(document).find('input#ays-pb-autoclose').val(0);
                    $(document).find('input#ays-pb-autoclose-mobile').val(0);
                    $(document).find('.ays-pb-hide-timer-hr').hide(250);
                    $(document).find('#ays_pb_hide_timer_popup').hide(250);

                    $(document).find('input#ays_popup_content_padding').val(0);
                    $(document).find('span.ays-pb-padding-default-value').text(0);

                    $(document).find('input#image_type_img_theme_view_type').prop('checked',true);
                    break;
                case 'facebook_type':
                    typeText = 'Facebook';

                    $(document).find('.ays_facebook_type_option').removeClass('display_none');
                    $(document).find('.ays_facebook_hr').removeClass('display_none');
                    $(document).find('.ays_pb_hide_for_facebook_type').addClass('display_none');

                    $(document).find('.ays-pb-live-container').addClass('display_none');
                    $(document).find('.ays-pb-live-container').removeClass('ays_active');
                    $(document).find('.ays-pb-live-container.ays_facebook_window').removeClass('display_none');
                    $(document).find('.ays-pb-live-container.ays_facebook_window').addClass('ays_active');

                    $(document).find('div.ays_pb_styles_tab_options').removeClass('col-md-6');
                    $(document).find('div.ays_pb_styles_tab_options').addClass('col-md-12');

                    $(document).find('input#facebook_theme_view_type').prop('checked',true);
                    break;
                case 'notification_type':
                    typeText = 'Notification';

                    $(document).find('.ays_notification_type_option').removeClass('display_none');
                    $(document).find('.ays_notification_type_hr').removeClass('display_none');
                    $(document).find('.ays_pb_hide_for_notification_type').addClass('display_none');

                    $(document).find('input#ays-pb-close-button').prop('checked',true);
                    $(document).find('.ays_pb_close_bttn_option').addClass('display_none');

                    $(document).find('div.ays_pb_styles_tab_options').removeClass('col-md-6');
                    $(document).find('div.ays_pb_styles_tab_options').addClass('col-md-12');

                    $(document).find('input#ays-pb-height').val(100);
                    $(document).find('input#ays-pb-ays_pb_bordersize').val(0);
                    $(document).find('input#ays-pb-ays_pb_bordersize_mobile').val(0);
                    $(document).find('input#ays-pb-ays_pb_border_radius').val(0);
                    $(document).find('input#ays-pb-ays_pb_border_radius_mobile').val(0);

                    $(document).find('input#notification_theme_view_type').prop('checked',true);
                    break;
                default:
                    $('#ays_custom_html').removeClass('display_none');
                    $('#ays_custom_html_hr').removeClass('display_none');
                    break;
            }

            $(document).find('span.ays-pb-type-name').text(typeText);
            $(document).find('p.ays-pb-type-video').append(typeVideoLink)
        });
        // Choose popup type end

        // Open popups list start
        $(document).find('.ays-pb-open-popups-list').on('click', function(e) {
            $(this).parents('.ays-pb-subtitle-main-box').find('.ays-pb-popups-data').toggle('fast');
        });
        // Open popups list end

        // Close popup list start
        $(document).on('click', function(e) {
            if ($(e.target).closest('.ays-pb-subtitle-main-box').length != 0) return;

            $(document).find('.ays-pb-subtitle-main-box .ays-pb-popups-data').hide('fast');
        });
        // Close popup list end

        // Ctrl + S save start
        $(document).keydown(function(event) {
            if (!(event.which == 83 && event.ctrlKey) && !(event.which == 19)) {
                return true;
            }
    
            var editButton = $(document).find('input#ays-button-top-apply , input#ays-cat-button-apply , input#ays-button-apply, input#ays_submit_settings');
            editButton.trigger('click');
            event.preventDefault();
            return false;
        });
        // Ctrl + S save end

        // Redirect to another popup start
        $(document).find('.ays-pb-go-to-popups').on('click' , function(e) {
            e.preventDefault();

            var confirmRedirect = window.confirm('Are you sure you want to redirect to another popup? Note that the changes made in this popup will not be saved.');

            if (confirmRedirect) {
                window.location = $(this).attr('href');
            }
        });
        // Redirect to another popup end

        // Nav tab start
        if ($(document).find('.ays-pb-top-menu').width() <= $(document).find('div.ays-pb-top-tab-wrapper').width()) {
            $(document).find('.ays_pb_menu_left').css('display', 'flex');
            $(document).find('.ays_pb_menu_right').css('display', 'flex');
        }

        $(window).resize(function() {
            if ($(document).find('.ays-pb-top-menu').width() < $(document).find('div.ays-pb-top-tab-wrapper').width()) {
                $(document).find('.ays_pb_menu_left').css('display', 'flex');
                $(document).find('.ays_pb_menu_right').css('display', 'flex');
            } else {
                $(document).find('.ays_pb_menu_left').css('display', 'none');
                $(document).find('.ays_pb_menu_right').css('display', 'none');
                $(document).find('div.ays-pb-top-tab-wrapper').css('transform', 'translate(0px)');
            }
        });

        $(document).find('.ays-pb-top-tab-wrapper').each(function() {
            var $this = $(this);
            menuItemWidths0.push($this.outerWidth());
        });

        for (var i = 0; i < menuItemWidths0.length; i+=2) {
            if (menuItemWidths0.length <= i+1) {
                menuItemWidths.push(menuItemWidths0[i]);
            } else {
                menuItemWidths.push(menuItemWidths0[i] + menuItemWidths0[i+1]);
            }
        }

        for (var i = 0; i < menuItemWidths.length; i++) {
            menuItemWidth += menuItemWidths[i];
        }

        menuItemWidth = menuItemWidth / menuItemWidths.length;

        $(document).find('.nav-tab-wrapper a.nav-tab').on('click', function(e) {
            let elemenetID = $(this).attr('href');
            let active_tab = $(this).attr('data-tab');
            $(document).find('.nav-tab-wrapper a.nav-tab').each(function() {
                if ($(this).hasClass('nav-tab-active')) {
                    $(this).removeClass('nav-tab-active');
                }
            });

            $(this).addClass('nav-tab-active');
            $(document).find('.ays-pb-tab-content').each(function() {
                if ($(this).hasClass('ays-pb-tab-content-active')) {
                    $(this).removeClass('ays-pb-tab-content-active');
                }
            });

            $(document).find('[name="ays_pb_tab"]').val(active_tab);
            $('.ays-pb-tab-content' + elemenetID).addClass('ays-pb-tab-content-active');
            e.preventDefault();
        });

        $(document).on('click', '.ays_pb_menu_left', function() {
            var scroll = parseInt($(this).attr('data-scroll'));

            scroll -= menuItemWidth;
            if (scroll < 0) {
                scroll = 0;
            }

            $(document).find('div.ays-pb-top-tab-wrapper').css('transform', 'translate(-' + scroll + 'px)');
            $(this).attr('data-scroll', scroll);
            $(document).find('.ays_pb_menu_right').attr('data-scroll', scroll);
        });

        $(document).on('click', '.ays_pb_menu_right', function() {
            var scroll = parseInt($(this).attr('data-scroll'));
            var howTranslate = $(document).find('div.ays-pb-top-tab-wrapper').width() - $(document).find('.ays-pb-top-menu').width();

            howTranslate += 7;
            if (scroll == -1) {
                scroll = menuItemWidth;
            }

            scroll += menuItemWidth;
            if (scroll > howTranslate) {
                scroll = Math.abs(howTranslate);
            }

            $(document).find('div.ays-pb-top-tab-wrapper').css('transform', 'translate(-' + scroll + 'px)');
            $(this).attr('data-scroll', scroll);
            $(document).find('.ays_pb_menu_left').attr('data-scroll', scroll);
        });
        // Nav tab end

        // Close cache note start
        $(document).on('click', '.ays-pb-cache-warning-note-close-container img.ays-pb-cache-warning-note-close', function() {
            closeWarningNotePermanently(this);
        });
        // Close cache note start

        // Toggle mobile settings start
        $(document).find('.ays_pb_different_settings_for_mobile').on('change', toggleMobileSettings);
        $(document).find('.ays_pb_option_for_desktop, .ays_pb_option_for_mobile_device_cb').on('click', toggleMobileSettingsCb);
        // Toggle mobile settings end

        // Live update popup title start
        $(document).find('#ays-pb-popup_title').on('input', function(e) {
            var pbTitleVal = $(this).val();
            var pbTitle = aysPopupstripHTML(pbTitleVal);
            $(document).find('.ays_pb_title_in_top').html(pbTitle);
        });
        // Live update popup title end

        // Position tables start
        $(document).find('table#ays-pb-position-table tr td, table#ays_pb_bg_image_position_table tr td, table#ays-pb-position-table-mobile tr td, table#ays_pb_bg_image_position_table_mobile tr td').on('click', function(e) {
            var val = $(this).data('value');
            var flag = $(this).parents('table').data('flag');

            if (flag == 'popup_position' || flag == 'bg_image_position') {
                $(this).parents('.pb_position_block').find('.ays-pb-position-val-class').val(val).trigger('change');
            } else {
                $(this).parents('.pb_position_block').find('.ays-pb-position-val-class-mobile').val(val).trigger('change');
            }

            if (flag == 'popup_position' || flag == 'popup_position_mobile') {
                aysCheckPopupPosition();
            } else if (flag == 'bg_image_position' || flag == 'bg_image_position_mobile') {
                aysCheckBgImagePosition();
            }
        });
        // Position tables end

        // Add video start
        $(document).on('click', 'a.ays-pb-add-bg-video', function(e) {
            openMediaUploaderVideo(e, $(this));
        });
        // Add video end

        // Remove video start
        $(document).on('click','.ays-remove-bg-video', function() {
            var bg_video_default = $(document).find('.ays_video_window > input').val();

            $('video#ays_pb_video_theme_video').attr('src', '');
            $('input#ays_pb_video_theme').val('');
            $('.ays-pb-bg-video-container-main').fadeOut();
            $('a.ays-pb-add-bg-video').text(pb.addVideo);

            if ($(document).find('.ays_video_window').hasClass('ays_active')) {
                $(document).find('.video_theme').attr('src', bg_video_default);
            }
        });
        // Remove video end

        // Image type | Add popup image start
        $(document).on('click', 'a.ays-pb-image-type-add-img', function(e) {
            openMediaUploaderImageTypeImg(e, $(this));
        });
        // Image type | Add popup image end

        // Image type | Remove popup image start
        $(document).on('click','.ays-remove-image-type-img', function() {
            $('img#ays_pb_image_type_img').attr('src', '');
            $('input#ays_pb_image_type_img_src').val('');
            $('.ays-pb-image-type-img-container-main').fadeOut();
            $('.ays-pb-image-type-img-settings-container').addClass('display_none');
            $('a.ays-pb-image-type-add-img').text(pb.addImage);
            $('img.image_type_img_live').attr('src', '');
        });
        // Image type | Remove popup image end

        // Notification type components section start
        $(document).on('click', 'div.open_component_options', openComponentOptions);
        $(document).on('click', 'div.close_component_options', closeComponentOptions);
        // Notification type components section end

        // Change post taype start
        $(document).on('change', '#ays_pb_post_types', function() {
            var selected = $('.select2-selection__choice');
            var arr = pb.post_types;

            var types_arr = [];
            for (var i = 0; i < selected.length; i++) {
                var name = selected[i].innerText;
                name = name.substring(1, name.length);
                for (var j = 0; j < arr.length; j++) {
                    if (name == arr[j][1]) {
                        types_arr.push(arr[j][0]);
                    }
                }
            }

            var get_hidden_val = $('#ays_pb_except_posts_id');
            var posts = $(document).find('#ays_pb_posts option:selected');
            var posts_ids = [];
            posts.each(function() {
                posts_ids.push($(this).attr('value'));
            });
            posts_ids = posts_ids.join(',');
            get_hidden_val.val(posts_ids);

            $.ajax({
                url: pb.ajax,
                method: 'post',
                dataType: 'text',
                data: {
                    action: 'get_selected_options_pb',
                    data: types_arr,
                },
                success: function(resp) {
                    var inp = $('#ays_pb_posts');
                    var data = JSON.parse(resp);
                    inp.html('');
                    inp.val(null).trigger('change');

                    var new_hidden_val = get_hidden_val.val();
                    var get_hidden_val_arr = new_hidden_val.split(',');

                    for (var i = 0; i < data.length; i++) {
                        inp.append('<option value="' + data[i][0] + '">' + data[i][1] + '</option>');
                    }
                   
                    for (var k = 0; k < get_hidden_val_arr.length; k++) {
                        inp.select2( 'val', get_hidden_val_arr );
                    }
                },
            });
        });
        // Change post taype end

        // Change popup trigger start
        $(document).on('change', '#ays-pb-action_button_type', function() {
            var thisVal = $(this).val();
            var showPopupTriggersTooltip = {
                pageLoaded: 'On page load - Trigger displays the popup automatically on the page load. Define the time delay of the popup in Open Delay option.',
                clickSelector: 'On click - Trigger displays a popup on your site when the user clicks on a targeted CSS element(s). Define the CSS element in the CSS selector(s) option.',
                both: 'Both (On page load & On click) - Popup will be shown both on page load and click.',
            }

            $(document).find('.ays-pb-triggers-tooltip').attr('data-original-title', showPopupTriggersTooltip[thisVal]);

            if (thisVal == 'clickSelector' || thisVal == 'both') {
                $(document).find('.ays-pb-open-click-hover').show(250);
                $(document).find('.ays-pb-open-click-hover').css('display', 'flex');
                $(document).find('.ays-pb-open-click-hover').prev('hr').css('display', 'block');
            } else {
                $(document).find('.ays-pb-open-click-hover').hide(250);
                $(document).find('.ays-pb-open-click-hover').prev('hr').css('display', 'none');
            }
        });
        // Change popup trigger end

        // Toggle hide close button cb start
        $(document).find('#ays-pb-close-button').on('change', function() {
            var closeBttnOptions = $(document).find('.ays_pb_close_bttn_option');
            var livePreviewCloseBttns = $(document).find('.ays-close-button-on-off');

            closeBttnOptions.toggleClass('display_none');
            livePreviewCloseBttns.toggleClass('display_none_important');
        });
        // Toggle hide close button cb end

        // Toggle autoclose delay start
        $(document).find('#ays-pb-autoclose, #ays-pb-autoclose-mobile').on('input', function() {
            var optionContainer = $(this).parents('#ays_pb_close_autoclose');
            var autocloseCount = optionContainer.find('#ays-pb-autoclose').val();
            var autocloseCountMobile = optionContainer.find('#ays-pb-autoclose-mobile').val();
            var isDifferentForMobileOn = optionContainer.find('#ays_pb_enable_autoclose_delay_text_mobile').prop('checked');
            var hideTimerHr = $(this).parents('.ays-pb-tab-content').find('.ays-pb-hide-timer-hr');
            var hideTimerContainer = $(this).parents('.ays-pb-tab-content').find('#ays_pb_hide_timer_popup');
            var isHideTimerOn = hideTimerContainer.find('input#ays_pb_hide_timer').prop('checked');

            if (autocloseCount == 0) {
                if (!isDifferentForMobileOn || (isDifferentForMobileOn && autocloseCountMobile == 0)) {
                    hideTimerHr.hide(250);
                    hideTimerContainer.hide(250);
                } else {
                    hideTimerHr.show(250);
                    hideTimerContainer.css('display', 'flex');
                }

                $(document).find('.ays-pb-live-container .ays_pb_timer').css({'visibility':'hidden'});
            } else {
                $(this).parents('.ays-pb-tab-content').find('.ays-pb-hide-timer-hr').show(250);
                $(this).parents('.ays-pb-tab-content').find('#ays_pb_hide_timer_popup').css('display', 'flex');

                if (!isHideTimerOn) {
                    $(document).find('.ays-pb-live-container .ays_pb_timer').css({'visibility':'visible'});
                }
            }
        });

        $(document).find('.ays-pb-autoclose-mobile-toggle').on('change', function() {
            var optionContainer = $(this).parents('#ays_pb_close_autoclose');
            var autocloseCount = optionContainer.find('#ays-pb-autoclose').val();
            var autocloseCountMobile = optionContainer.find('#ays-pb-autoclose-mobile').val();
            var isDifferentForMobileOn = optionContainer.find('#ays_pb_enable_autoclose_delay_text_mobile').prop('checked');

            if (isDifferentForMobileOn) {
                if (autocloseCountMobile > 0) {
                    $(this).parents('.ays-pb-tab-content').find('.ays-pb-hide-timer-hr').show(250);
                    $(this).parents('.ays-pb-tab-content').find('#ays_pb_hide_timer_popup').css('display', 'flex');
                }
            } else {
                if (autocloseCount <= 0) {
                    $(this).parents('.ays-pb-tab-content').find('.ays-pb-hide-timer-hr').hide(250);
                    $(this).parents('.ays-pb-tab-content').find('#ays_pb_hide_timer_popup').hide(250);
                }
            }
        });
        // Toggle autoclose delay end

        // Toggle hide timer cb start
        $(document).find('.ays_pb_hide_timer').on('click', function() {
            var inpHideTimer = $(document).find('.ays_pb_hide_timer').prop('checked');

            if (inpHideTimer) {
                $(document).find('.ays_pb_timer').css({'visibility':'hidden'});
            } else {
                $(document).find('.ays_pb_timer').css({'visibility':'visible'});
            }
        });
        // Toggle hide timer cb end

        // Toggle enable overlay cb start
        $(document).on('change', '#ays-pb-onoffoverlay', function() {
            var checked = $(this).prop('checked');

            if (checked) {
                $(document).find('.ays-pb-blured-overlay').css('display', 'flex');
                $(document).find('.ays-pb-blured-overlay').prev('hr').css('display', 'flex');
            } else {
                $(document).find('.ays-pb-blured-overlay').css('display', 'none');
                $(document).find('.ays-pb-blured-overlay').prev('hr').css('display', 'none');
            }
        });
        // Toggle enable overlay cb end

        // Templates start
        $(document).on('click', 'button.ays-pb-template-themes-view-more-btn', function() {
            $(this).next().css('display', 'block');
            $(this).css('display', 'none');
            $(document).find('div.ays-pb-template-themes-view-more').css('animation', '5s ease 0s 1 normal none running fadeIntDown');
            $(document).find('div.ays-pb-template-themes-view-more').css('display', 'flex');
        });

        $(document).on('click', 'button.ays-pb-template-themes-hide-btn', function() {
            $(this).prev().css('display', 'block');
            $(this).css('display', 'none');
            $(document).find('div.ays-pb-template-themes-view-more').css('animation', '5s ease 0s 1 normal none running fadeOutUp');
            $(document).find('div.ays-pb-template-themes-view-more').css('display', 'none');
        });

        $('.ays-pb-template-overlay-preview').mouseover(function() {
            $(this).find('div.ays-pb-choose-template-div').css('display','block');
        });

        $('.ays-pb-template-overlay-preview').mouseout(function() {
            var checkedTheme = $(this).find('.ays-pb-choose-template-div').find('.ays-pb-template-checkbox-container > input').prop('checked');
            if (!checkedTheme) {
                $(this).find('div.ays-pb-choose-template-div').css('display','none');
            }
        });

        $(document).on('click', '.ays-pb-template-choose-template-btn, .ays-pb-template-checkbox input', function() {
            var checked = $(this).parents('.ays-pb-choose-template-div').find('.ays-pb-template-checkbox input').prop('checked', true);

            if (checked) {
                var checkedTheme = $('input[name="ays-pb[view_type]"]:checked').val();
                var backroundImageTag = $(document).find('#ays-pb-bg-img');
                var backroundImageInput = $(document).find('#ays-pb-bg-image');
                var backroundImageContent = $(document).find('.ays-pb-bg-image-container').parent();
                var addImage = $(document).find('.ays-pb-add-bg-image');

                var girlScaledImg = pb.AYS_PB_ADMIN_URL + '/images/girl-scaled.jpg';
                var elefanteImg = pb.AYS_PB_ADMIN_URL + '/images/elefante.jpg';
                var isBgImgNotExist = $('a.ays-pb-add-bg-image').attr('data-add') == 'true';
                var changeBgImg = backroundImageInput.val() == girlScaledImg || backroundImageInput.val() == elefanteImg || backroundImageInput.val() == '';

                if (!isBgImgNotExist || changeBgImg) {
                    if (checkedTheme == 'template') {
                        backroundImageTag.attr('src', girlScaledImg);
                        backroundImageInput.val(girlScaledImg);
                        backroundImageContent.css('display', 'flex');
                        addImage.html(pb.editImage);
                    } else if (checkedTheme == 'image') {
                        backroundImageTag.attr('src', elefanteImg);
                        backroundImageInput.val(elefanteImg);
                        backroundImageContent.css('display', 'flex');
                        addImage.html(pb.editImage);
                    } else {
                        backroundImageTag.attr('src', '');
                        backroundImageInput.val('');
                        backroundImageContent.css('display', 'none');
                        addImage.html(pb.addImage);
                    }
                }

                if (checkedTheme == 'minimal') {
                    $('.ays-pb-padding-content').find('p.ays-pb-small-hint-text span').text(0);

                    if ($('.ays-pb-padding-content').hasClass('ays-pb-padding-content-default')) {
                        $('.ays-pb-padding-content').find('input.ays_pb_padding').val('0');
                    }
                } else {
                    $('.ays-pb-padding-content').find('p.ays-pb-small-hint-text span').text(20);

                    if ($('.ays-pb-padding-content').hasClass('ays-pb-padding-content-default')) {
                        $('.ays-pb-padding-content').find('input.ays_pb_padding').val('20');
                    }
                }

                $('div.ays-pb-choose-template-div').not($(this).find('.ays-pb-choose-template-div')).css('display', 'none');
            }
        });
        // Templates end

        // Toggle show title start
        $(document).find('.ays_pb_title').on('change', function() {
            var inpHideTitle = $(document).find('.ays_pb_title').prop('checked');

            if (inpHideTitle) {
                $(document).find('.ays_title').css({'display':'block'});
                $(document).find('.ays_template_head').css({'height':'15%','display':'flex', 'justify-content':'center','align-items':'center'});
                $(document).find('.ays_template_footer').css({'height':'100%'});
                $(document).find('.title_hr').css({'display':'block'});

               $(document).find('.ays-pb-title-shadow-small-hint').css('display', 'none');
            } else {
                $(document).find('.ays_title').css({'display':'none'});
                $(document).find('.ays_template_head').css({'height':'0'});
                $(document).find('.ays_template_footer').css({'height':'85%'});
                $(document).find('.title_hr').css({'display':'none'});

                $(document).find('.ays-pb-title-shadow-small-hint').css('display', 'block');
            }
        });
        // Toggle show title end

        // Toggle show description start
        $(document).find('.ays_pb_desc').on('change', function() {
            var inpHideDesc = $(document).find('.ays_pb_desc').prop('checked');

            if (inpHideDesc) {
                $(document).find('.ays-pb-description-small-hint').addClass('display_none');
                $(document).find('.desc').css({'display':'block'});
            } else {
                $(document).find('.ays-pb-description-small-hint').removeClass('display_none');
                $(document).find('.desc').css({'display':'none'});
            }
        });
        // Toggle show description end

        // Popup fullscreen cb toggle start
        $(document).find('#open_pb_fullscreen').on('click', function() {
            var inpFullScreenChecked = $(document).find('#open_pb_fullscreen').prop('checked');

            if (inpFullScreenChecked) {
                $(document).find('.ays_pb_width').prop('readonly', true);
                $(document).find('.ays_pb_height').prop('readonly', true);
            } else {
                $(document).find('.ays_pb_width').prop('readonly', false);
                $(document).find('.ays_pb_height').prop('readonly', false);
            }
        });
        // Popup fullscreen cb toggle end

        // Popop padding start
        $(document).on('change', 'input#ays_popup_content_padding, #ays_popup_padding_by_percentage_px', function() {
            var paddingContent = $(this).parents('.ays-pb-padding-content');

            if (paddingContent.hasClass('ays-pb-padding-content-default')) {
                paddingContent.removeClass('ays-pb-padding-content-default');
            }
        });
        // Popop padding end

        // Toggle title text shadow start
        $(document).find('#ays_enable_title_text_shadow').on('change', function() {
            var textShadowColor = $('#ays_title_text_shadow_color').val();
            var textShadowX = $('#ays_pb_title_text_shadow_x_offset').val();
            var textShadowY = $('#ays_pb_title_text_shadow_y_offset').val();
            var textShadowZ = $('#ays_pb_title_text_shadow_z_offset').val();

            if ($(this).prop('checked')) {
                $(document).find('h2.ays_title').css('text-shadow', textShadowX + 'px ' + textShadowY + 'px ' + textShadowZ + 'px ' + textShadowColor);
            } else {
                $(document).find('h2.ays_title').css('text-shadow', 'unset');
            }
        });
        // Toggle title text shadow end

        // Background image | Add start
        $(document).on('click', 'a.ays-pb-add-bg-image, a.ays-pb-add-bg-image-mobile', function(e) {
            openMediaUploaderBg(e, $(this));
        });
        // Background image | Add end

        // Background image | Remove start
        $(document).on('click', '.ays-remove-bg-img, .ays-remove-bg-img-mobile', function() {
            var bgImageTag = $('img#ays-pb-bg-img-mobile');
            var bgImageInp = $('input#ays-pb-bg-image-mobile');
            var bgImageContainer = $('.ays-pb-bg-image-container-mobile');
            var addBgImageBttn = $('a.ays-pb-add-bg-image-mobile');

            if ($(this).hasClass('ays-remove-bg-img')) {
                bgImageTag = $('img#ays-pb-bg-img');
                bgImageInp = $('input#ays-pb-bg-image');
                bgImageContainer = $('.ays-pb-bg-image-container');
                addBgImageBttn = $('a.ays-pb-add-bg-image');

                $('.box-apm').css('background-image', 'unset');
                $('.ays_bg_image_box').css('background-image', 'unset');
                $('.ays_lil_window').css('background-image', 'unset');
                if ($(document).find('#ays-enable-background-gradient').prop('checked')) {
                    toggleBackgrounGradient();
                }

                if ($(document).find('.ays_template_window').is(':visible')) {
                    var bg_img_default = 'https://quiz-plugin.com/wp-content/uploads/2020/02/girl-scaled.jpg';
                    $(document).find('.ays_bg_image_box').css({
                        'background-image' : 'url(' + bg_img_default + ')',
                        'background-repeat' : 'no-repeat',
                        'background-size' : 'cover',
                        'background-position' : 'center center'
                    });
                }

                if ($(document).find('.ays_image_window').is(':visible')) {
                    var bg_img_default = 'https://quiz-plugin.com/wp-content/uploads/2020/02/elefante.jpg';
                    $(document).find('.ays_bg_image_box').css({
                        'background-image' : 'url(' + bg_img_default + ')',
                        'background-repeat' : 'no-repeat',
                        'background-size' : 'cover',
                        'background-position' : 'center center'
                    });
                }
            }

            bgImageTag.attr('src', '');
            bgImageInp.val('');
            bgImageContainer.parent().fadeOut();
            addBgImageBttn.text('Add Image');
            addBgImageBttn.attr('data-add', false);
        });
        // Background image | Remove end

        // Toggle background image options start
        $(document).on('change', '#ays_pb_bg_image_position', function() {
            $(document).find('.ays-pb-live-container').css('background-position', $(this).val());
            $(document).find('.ays-pb-live-container .ays_bg_image_box').css('background-position', $(this).val());
        });

        $(document).on('change', '#ays_pb_bg_image_sizing', function() {
            $(document).find('.ays-pb-live-container').css('background-size', $(this).val());
            $(document).find('.ays-pb-live-container .ays_bg_image_box').css('background-size', $(this).val());
        });
        // Toggle background image options end

        // Toggle background gradient start
        $(document).find('#ays_pb_gradient_direction').on('change', function() {
            toggleBackgrounGradient();
        });
        $(document).find('input#ays-enable-background-gradient').on('change', function() {
            toggleBackgrounGradient()
        });
        // Toggle background gradient end

        // Toggle border styles start
        $(document).find('#ays_pb_border_style').on('change', function() {
            var borderStyle = $(document).find('#ays_pb_border_style').val();
            $(document).find('.ays-pb-live-container').css('border-style', borderStyle);
        })
        // Toggle border styles end

        // Toggle box shadow start
        $(document).find('#ays_pb_enable_box_shadow').on('change', function() {
            var boxShadowColor = $('#ays_pb_box_shadow_color').val();
            var boxShadowX = $('#ays_pb_box_shadow_x_offset').val();
            var boxShadowY = $('#ays_pb_box_shadow_y_offset').val();
            var boxShadowZ = $('#ays_pb_box_shadow_z_offset').val();

            if ($(this).prop('checked')) {
                $(document).find('div.ays-pb-live-container').css('box-shadow', boxShadowX + 'px ' + boxShadowY + 'px ' + boxShadowZ + 'px ' + boxShadowColor);
            } else {
                $(document).find('div.ays-pb-live-container').css('box-shadow', 'unset');
            }
        });
        // Toggle box shadow end

        // Close button image | Add start
        $(document).on('click', 'a.ays_pb_add_close_btn_bg_image', function(e) {
            openMediaUploaderCloseBtn(e, $(this));
        });
        // Close button image | Add end

        // Close button image | Remove start
        $(document).on('click', '.ays_remove_bg_img', function() {
            $('img#ays_close_btn_bg_img').attr('src', '');
            $('input#close_btn_bg_img').val('');
            $('.ays_pb_close_btn_bg_img').parent().fadeOut();
            $('a.ays_pb_add_close_btn_bg_image').text('Add Image');

            $(document).find('img.close_btn_img').css('display','none');
            $(document).find('label.close_btn_label > .close_btn_text').css('display','block');
        });
        // Close button image | Remove end

        // Reset styles start
        $(document).on('click', '.ays-pb-reset-styles', function() {
            var defaultValues = {
                displayTitle: false,
                displayTitleMobile: false,
                displayDesc: false,
                displayDescMobile: false,
                width: '400',
                percentPixel: 'px',
                percentPixelMobile: 'percentage',
                percentPixelMobileText: '%',
                mobileWidth: '',
                maxWidthMobile: '',
                height: '500',
                maxHeight: '',
                maxHeightMobile: '',
                mobileHeight: '',
                popupMinHeight: '',
                fullScreen: false,
                padding: '20',
                textColor: '#000',
                fontFamily: 'Inherit',
                descPC: '13',
                descMobile: '13',
                textShadow: false,
                textShColor: 'rgba(255,255,255,0)',
                textShX: '2',
                textShY: '2',
                textShZ: '0',
                textShadowMobile: false,
                textShColorMobile: 'rgba(255,255,255,0)',
                textShXMobile: '2',
                textShYMobile: '2',
                textShZMobile: '0',
                openAnimSpeed: '1',
                enableAnimSpeedMobile: false,
                openAnimSpeedMobile: '1',
                closeAnimSpeed: '1',
                enableCloseAnimSpeedMobile: false,
                closeAnimSpeedMobile: '1',
                closeAnim: 'fadeOut',
                closeAnimText: 'Fade Out',
                enableCloseAnimMobile: false,
                closeAnimMobile: 'fadeOut',
                closeAnimTextMobile: 'Fade Out',
                openAnim: 'fadeIn',
                openAnimText: 'Fade In',
                enableOpenAnimMobile: false,
                openAnimMobile: 'fadeIn',
                openAnimTextMobile: 'Fade In',
                bgColor: '#fff',
                enablebgColorMobile: false,
                bgColorMobile: '#fff',
                bgImg: '',
                bgImgAddBttnText: pb.addImage,
                bgImgRemoveBttnDisplay: 'none',
                bgImgMobile: '',
                bgImgAddBttnTextMobile: pb.addImage,
                bgImgRemoveBttnDisplayMobile: 'none',
                bgImgSizing: 'cover',
                bgImgSizingText: 'Cover',
                bgImgSizingMobile: 'cover',
                bgImgSizingTextMobile: 'Cover',
                bgGrad: false,
                bgGradC1: '#000',
                bgGradC2: '#fff',
                bgGradDir: 'vertical',
                bgGradDirText: 'Vertical',
                bgGradMobile: false,
                bgGradC1Mobile: '#000',
                bgGradC2Mobile: '#fff',
                bgGradDirMobile: 'vertical',
                bgGradDirTextMobile: 'Vertical',
                headerBgColor: '#fff',
                overlayColor: '#000',
                enableOverlayColorMobile: false,
                overlayColorMobile: '#000',
                borderWidth: '1',
                enableBorderWidthMobile: false,
                borderWidthMobile: '1',
                borderStyle: 'Solid',
                enableBorderStyleMobile: false,
                borderStyleMobile: 'Solid',
                borderColor: '#fff',
                enableBorderColorMobile: false,
                borderColorMobile: '#fff',
                borderRadius: '4',
                enableBorderRadiusMobile: false,
                borderRadiusMobile: '4',
                closeBtnImg: '',
                closeBtnColor: '#000',
                closeBtnColorHover: '#000',
                closeBtnSize: '1',
                boxShadow: false,
                boxShadowColor: '#000',
                boxShadowX: '0',
                boxShadowY: '0',
                boxShadowZ: '15',
                boxShadowMobile: false,
                boxShadowColorMobile: '#000',
                boxShadowXMobile: '0',
                boxShadowYMobile: '0',
                boxShadowZMobile: '15',
                bgImgStleOnMobile: true,
                bgImgPosition: 'center-center',
                bgImgPositionMobile: 'center-center',
            }

            var $form = $(this).parents('form#ays_pb_form');

            var checkedTheme = $('input[name="ays-pb[view_type]"]:checked').val();
            if (checkedTheme == 'image' || checkedTheme == 'template') {
                var templateDefaultBgImg = 'https://quiz-plugin.com/wp-content/uploads/2020/02/girl-scaled.jpg';
                var imageDefaultBgImg = 'https://quiz-plugin.com/wp-content/uploads/2020/02/elefante.jpg';
                $form.find('footer.ays_template_footer .ays_bg_image_box').css({'background-image': 'url("' + templateDefaultBgImg + '")'});
                $form.find('div#ays-image-window.ays-pb-live-container').css({'background-image': 'url("' + imageDefaultBgImg + '")'});

                defaultValues.bgImgAddBttnText = pb.editImage;
                defaultValues.bgImgAddBttnTextMobile = pb.editImage;
                defaultValues.bgImgRemoveBttnDisplay = 'block';
                defaultValues.bgImgRemoveBttnDisplayMobile = 'block';
                defaultValues.bgImg = imageDefaultBgImg;

                if (checkedTheme == 'template') {
                    defaultValues.bgImg = templateDefaultBgImg;
                }
            }

            $form.find('input.ays_pb_title').prop('checked', defaultValues.displayTitle).change();
            $form.find('input.ays_pb_title_mobile').prop('checked', defaultValues.displayTitleMobile).change();
            $form.find('input.ays_pb_desc').prop('checked', defaultValues.displayDesc).change();
            $form.find('input.ays_pb_desc_mobile').prop('checked', defaultValues.displayDescMobile).change();
            $form.find('input#ays_pb_enable_display_content_mobile').prop('checked', defaultValues.displayDescMobile).change();
            $form.find('div.ays_pb_display_content_mobile_container').css('display', 'none').change();

            $form.find('input#ays-pb-width').val(defaultValues.width).change();
            $form.find('input#ays-pb-width').prop('readonly', false).change();

            $form.find('select#ays_popup_width_by_percentage_px').val(defaultValues.percentPixel).change();
            $form.find('select#ays_popup_width_by_percentage_px').parent('div.ays_pb_aysDropdown').find('div.text').text(defaultValues.percentPixel).change();
            $form.find('select#ays_popup_width_by_percentage_px_mobile').val(defaultValues.percentPixelMobile).change();
            $form.find('select#ays_popup_width_by_percentage_px_mobile').parent('div.ays_pb_aysDropdown').find('div.text').text(defaultValues.percentPixelMobileText).change();

            $form.find('input#ays-pb-mobile-width').val(defaultValues.mobileWidth).change();

            $form.find('input#ays-pb-mobile-max-width').val(defaultValues.maxWidthMobile).change();

            $form.find('input#ays-pb-height').val(defaultValues.height).change();
            $form.find('input#ays-pb-height').prop('readonly', false).change();

            $form.find('input#ays_pb_mobile_height').val(defaultValues.mobileHeight).change();

            $form.find('input#ays-pb-max-height').val(defaultValues.maxHeight).change();
            $form.find('input#ays-pb-max-height-mobile').val(defaultValues.maxHeightMobile).change();
            $form.find('select#ays_popup_max_height_by_percentage_px').val(defaultValues.percentPixel).change();
            $form.find('select#ays_popup_max_height_by_percentage_px_mobile').val(defaultValues.percentPixel).change();
            $form.find('select#ays_popup_max_height_by_percentage_px').parent('div.ays_pb_aysDropdown').find('div.text').text(defaultValues.percentPixel).change();
            $form.find('select#ays_popup_max_height_by_percentage_px_mobile').parent('div.ays_pb_aysDropdown').find('div.text').text(defaultValues.percentPixel).change();

            $form.find('input#ays_pb_min_height').val(defaultValues.popupMinHeight).change();

            $form.find('input#open_pb_fullscreen').prop('checked', defaultValues.fullScreen).change();

            $form.find('input#ays_popup_content_padding').val(defaultValues.padding).change();
            $form.find('div.ays-pb-padding-content').addClass('ays-pb-padding-content-default').change();
            $form.find('select#ays_popup_padding_by_percentage_px').val(defaultValues.percentPixel).change();
            $form.find('select#ays_popup_padding_by_percentage_px').parent('div.ays_pb_aysDropdown').find('div.text').text(defaultValues.percentPixel).change();

            $form.find('input#ays-pb-ays_pb_textcolor').wpColorPicker('color', defaultValues.textColor).change();

            $form.find('select#ays_pb_font_family').val(defaultValues.fontFamily).change();
            $form.find('select#ays_pb_font_family').parent('div.ays_pb_aysDropdown').find('div.text').text(defaultValues.fontFamily).change();

            $form.find('input#ays_pb_font_size_for_pc').val(defaultValues.descPC).change();

            $form.find('input#ays_pb_font_size_for_mobile').val(defaultValues.descMobile).change();

            $form.find('input#ays_enable_title_text_shadow').prop('checked', defaultValues.textShadow).change();
            $form.find('input#ays_title_text_shadow_color').wpColorPicker('color', defaultValues.textShColor).change();
            $form.find('input#ays_pb_title_text_shadow_x_offset').val(defaultValues.textShX).change();
            $form.find('input#ays_pb_title_text_shadow_y_offset').val(defaultValues.textShY).change();
            $form.find('input#ays_pb_title_text_shadow_z_offset').val(defaultValues.textShZ).change();

            $form.find('input#ays_enable_title_text_shadow_mobile').prop('checked', defaultValues.textShadowMobile).change();
            $form.find('input#ays_title_text_shadow_color_mobile').wpColorPicker('color', defaultValues.textShColorMobile).change();
            $form.find('input#ays_pb_title_text_shadow_x_offset_mobile').val(defaultValues.textShXMobile).change();
            $form.find('input#ays_pb_title_text_shadow_y_offset_mobile').val(defaultValues.textShYMobile).change();
            $form.find('input#ays_pb_title_text_shadow_z_offset_mobile').val(defaultValues.textShZMobile).change();

            $form.find('div.ays-pb-title-shadow div.ays_toggle_target').css('display', 'none').change();

            $form.find('input#ays_pb_animation_speed').val(defaultValues.openAnimSpeed).prop('disabled', false).change();
            $form.find('input#ays_pb_enable_animation_speed_mobile').prop('checked', defaultValues.enableAnimSpeedMobile).change();
            $form.find('input#ays_pb_animation_speed_mobile').val(defaultValues.openAnimSpeedMobile).prop('disabled', false).change();
            $form.find('div.ays_pb_animation_speed_mobile_container').css('display', 'none').change();

            $form.find('input#ays_pb_close_animation_speed').val(defaultValues.closeAnimSpeed).prop('disabled', false).change();
            $form.find('input#ays_pb_enable_close_animation_speed_mobile').prop('checked', defaultValues.enableCloseAnimSpeedMobile).change();
            $form.find('input#ays_pb_close_animation_speed_mobile').val(defaultValues.closeAnimSpeedMobile).prop('disabled', false).change();
            $form.find('div.ays_pb_close_animation_speed_mobile_container').css('display', 'none').change();

            $form.find('select#ays-pb-animate_out').val(defaultValues.closeAnim).change();
            $form.find('select#ays-pb-animate_out').parent('div.ays_pb_aysDropdown').find('div.text').text(defaultValues.closeAnimText).change();

            $form.find('select#ays-pb-animate_out_mobile').val(defaultValues.closeAnimMobile).change();
            $form.find('input#ays_pb_enable_animate_out_mobile').prop('checked', defaultValues.enableCloseAnimMobile).change();
            $form.find('select#ays-pb-animate_out_mobile').parent('div.ays_pb_aysDropdown').find('div.text').text(defaultValues.closeAnimTextMobile).change();
            $form.find('div.ays_pb_animate_out_mobile_container').css('display', 'none').change();

            $form.find('select#ays-pb-animate_in').val(defaultValues.openAnim).change();
            $form.find('select#ays-pb-animate_in').parent('div.ays_pb_aysDropdown').find('div.text').text(defaultValues.openAnimText).change();

            $form.find('select#ays-pb-animate_in_mobile').val(defaultValues.openAnimMobile).change();
            $form.find('input#ays_pb_enable_animate_in_mobile').prop('checked', defaultValues.enableOpenAnimMobile).change();
            $form.find('select#ays-pb-animate_in_mobile').parent('div.ays_pb_aysDropdown').find('div.text').text(defaultValues.openAnimTextMobile).change();
            $form.find('div.ays_pb_animate_in_mobile_container').css('display', 'none').change();

            $form.find('input#ays-pb-bgcolor').wpColorPicker('color', defaultValues.bgColor).change();
            $form.find('input#ays_pb_enable_bgcolor_mobile').prop('checked', defaultValues.enablebgColorMobile).change();
            $form.find('input#ays-pb-bgcolor-mobile').wpColorPicker('color', defaultValues.bgColorMobile).change();
            $form.find('div.ays_pb_bgcolor_mobile_container').css('display', 'none').change();

            $form.find('img#ays-pb-bg-img').attr('src', defaultValues.bgImg).change();
            $form.find('input#ays-pb-bg-image').val(defaultValues.bgImg).change();
            $form.find('a.ays-pb-add-bg-image').text(defaultValues.bgImgAddBttnText).change();
            $form.find('span.ays-remove-bg-img').css('display', defaultValues.bgImgRemoveBttnDisplay).change();
            $form.find('img#ays-pb-bg-img-mobile').attr('src', defaultValues.bgImg).change();
            $form.find('input#ays-pb-bg-image-mobile').val(defaultValues.bgImg).change();
            $form.find('a.ays-pb-add-bg-image-mobile').text(defaultValues.bgImgAddBttnTextMobile).change();
            $form.find('span.ays-remove-bg-img-mobile').css('display', defaultValues.bgImgRemoveBttnDisplayMobile).change();
            $form.find('a.ays-pb-add-bg-image').attr('data-add', false).change();
            $form.find('a.ays-pb-add-bg-image-mobile').attr('data-add', false).change();
            $form.find('input#ays_pb_enable_bg_image_mobile').prop('checked', false).change();
            $form.find('div.ays_pb_bg_image_mobile_container').css('display', 'none').change();

            $form.find('.pb_position_block').find('.ays-pb-position-val-class').val(defaultValues.bgImgPosition).change();
            $form.find('.pb_position_block').find('.ays-pb-position-val-class-mobile').val(defaultValues.bgImgPositionMobile).change();
            $(document).find('table#ays_pb_bg_image_position_table td').removeAttr('style').change();
            $(document).find('table#ays_pb_bg_image_position_table tr td[data-id=5]').css('background-color', '#3d89e0').change();
            $(document).find('table#ays_pb_bg_image_position_table_mobile td').removeAttr('style').change();
            $(document).find('table#ays_pb_bg_image_position_table_mobile tr td[data-id=5]').css('background-color', '#9964b3').change();
            $form.find('input#ays_pb_enable_bg_image_position_mobile').prop('checked', false).change();
            $form.find('div.ays_pb_bg_image_position_mobile_container').css('display', 'none').change();

            $form.find('select#ays_pb_bg_image_sizing').val(defaultValues.bgImgSizing).change();
            $form.find('select#ays_pb_bg_image_sizing').parent('div.ays_pb_aysDropdown').find('div.text').text(defaultValues.bgImgSizingText).change();
            $form.find('select#ays_pb_bg_image_sizing_mobile').val(defaultValues.bgImgSizing).change();
            $form.find('select#ays_pb_bg_image_sizing_mobile').parent('div.ays_pb_aysDropdown').find('div.text').text(defaultValues.bgImgSizingText).change();
            $form.find('input#ays_pb_enable_bg_image_sizing_mobile').prop('checked', false).change();
            $form.find('div.ays_pb_bg_image_sizing_mobile_container').css('display', 'none').change();

            $form.find('input#ays-enable-background-gradient').prop('checked', defaultValues.bgGrad).change();
            $form.find('input#ays-enable-background-gradient-mobile').prop('checked', defaultValues.bgGradMobile).change();
            $form.find('div.ayspb-enable-background-gradient div.ays_toggle_target').css('display', 'none').change();
            $form.find('input#ays-background-gradient-color-1').wpColorPicker('color', defaultValues.bgGradC1).change();
            $form.find('input#ays-background-gradient-color-2').wpColorPicker('color', defaultValues.bgGradC2).change();
            $form.find('input#ays-background-gradient-color-1-mobile').wpColorPicker('color', defaultValues.bgGradC1Mobile).change();
            $form.find('input#ays-background-gradient-color-2-mobile').wpColorPicker('color', defaultValues.bgGradC2Mobile).change();
            $form.find('select#ays_pb_gradient_direction').val(defaultValues.bgGradDir).change();
            $form.find('select#ays_pb_gradient_direction').parent('div.ays_pb_aysDropdown').find('div.text').text(defaultValues.bgGradDirText).change();
            $form.find('select#ays_pb_gradient_direction_mobile').val(defaultValues.bgGradDirMobile).change();
            $form.find('select#ays_pb_gradient_direction_mobile').parent('div.ays_pb_aysDropdown').find('div.text').text(defaultValues.bgGradDirTextMobile).change();

            $form.find('input#ays-pb-header_bgcolor').wpColorPicker('color', defaultValues.headerBgColor).change();

            $form.find('input.ays_pb_overlay_color_change').wpColorPicker('color', defaultValues.overlayColor).change();
            $form.find('input#ays_pb_enable_overlay_color_mobile').prop('checked', defaultValues.enableOverlayColorMobile).change();
            $form.find('input#ays-pb-overlay_color_mobile').wpColorPicker('color', defaultValues.overlayColorMobile).change();
            $form.find('div.ays_pb_overlay_color_mobile_container').css('display', 'none').change();

            $form.find('input#ays-pb-ays_pb_bordersize').val(defaultValues.borderWidth).change();
            $form.find('input#ays_pb_enable_bordersize_mobile').prop('checked', defaultValues.enableBorderWidthMobile).change();
            $form.find('input#ays-pb-ays_pb_bordersize_mobile').val(defaultValues.borderWidthMobile).change();
            $form.find('div.ays_pb_bordersize_mobile_container').css('display', 'none').change();

            $form.find('select#ays_pb_border_style').val(defaultValues.borderStyle).change();
            $form.find('select#ays_pb_border_style').parent('div.ays_pb_aysDropdown').find('div.text').text(defaultValues.borderStyle).change();
            $form.find('input#ays_pb_enable_border_style_mobile').prop('checked', defaultValues.enableBorderStyleMobile).change();
            $form.find('select#ays_pb_border_style_mobile').val(defaultValues.borderStyleMobile).change();
            $form.find('select#ays_pb_border_style_mobile').parent('div.ays_pb_aysDropdown').find('div.text').text(defaultValues.borderStyleMobile).change();
            $form.find('div.ays_pb_border_style_mobile_container').css('display', 'none').change();

            $form.find('input#ays-pb-bordercolor').wpColorPicker('color', defaultValues.borderColor).change();
            $form.find('input#ays_pb_enable_bordercolor_mobile').prop('checked', defaultValues.enableBorderColorMobile).change();
            $form.find('input#ays-pb-bordercolor-mobile').wpColorPicker('color', defaultValues.borderColorMobile).change();
            $form.find('div.ays_pb_bordercolor_mobile_container').css('display', 'none').change();

            $form.find('input#ays-pb-ays_pb_border_radius').val(defaultValues.borderRadius).change();
            $form.find('input#ays_pb_enable_border_radius_mobile').prop('checked', defaultValues.enableBorderRadiusMobile).change();
            $form.find('input#ays-pb-ays_pb_border_radius_mobile').val(defaultValues.borderRadiusMobile).change();
            $form.find('div.ays_pb_border_radius_mobile_container').css('display', 'none').change();

            $form.find('img#ays_close_btn_bg_img').attr('src', '').change();
            $form.find('input#close_btn_bg_img').val('').change();
            $form.find('a.ays_pb_add_close_btn_bg_image').text('Add Image').change();
            $form.find('div.ays_pb_close_btn_bg_img span.ays_remove_bg_img').css('display', 'none').change();
            $form.find('div.ays_pb_close_btn_bg_img_container').css('display', 'none').change();

            $form.find('input#ays_pb_close_button_color').wpColorPicker('color', defaultValues.closeBtnColor).change();

            $form.find('input#ays_pb_close_button_hover_color').wpColorPicker('color', defaultValues.closeBtnColorHover).change();

            $form.find('#ays_pb_close_button_size').val(defaultValues.closeBtnSize).change();

            $form.find('input#ays_pb_enable_box_shadow').prop('checked', defaultValues.boxShadow).change();
            $form.find('input#ays_pb_box_shadow_color').wpColorPicker('color', defaultValues.boxShadowColor).change();
            $form.find('input#ays_pb_box_shadow_x_offset').val(defaultValues.boxShadowX).change();
            $form.find('input#ays_pb_box_shadow_y_offset').val(defaultValues.boxShadowY).change();
            $form.find('input#ays_pb_box_shadow_z_offset').val(defaultValues.boxShadowZ).change();

            $form.find('input#ays_pb_enable_box_shadow_mobile').prop('checked', defaultValues.boxShadowMobile).change();
            $form.find('input#ays_pb_box_shadow_color_mobile').wpColorPicker('color', defaultValues.boxShadowColorMobile).change();
            $form.find('input#ays_pb_box_shadow_x_offset_mobile').val(defaultValues.boxShadowXMobile).change();
            $form.find('input#ays_pb_box_shadow_y_offset_mobile').val(defaultValues.boxShadowYMobile).change();
            $form.find('input#ays_pb_box_shadow_z_offset_mobile').val(defaultValues.boxShadowZMobile).change();

            $form.find('div.ays-pb-box-shadow div.ays_toggle_target').css('display', 'none').change();

            $form.find('#ays_pb_bg_image_direction_on_mobile').prop('checked', defaultValues.bgImgStleOnMobile).change();

            $form.find('div.ays_bg_image_box').css({'background-size': defaultValues.bgImgSizing}).change();
            $form.find('div.ays_bg_image_box label.ays-pb-modal-close img.close_btn_img').css('display', 'none').change();
            $form.find('div.ays_bg_image_box label.ays-pb-modal-close img.close_btn_text').css('display', 'block').change();
            $form.find('div.ays-pb-live-container').css({'font-family': defaultValues.fontFamily}).change();

            $form.find('div.ays_pb_aysDropdown div.menu div.item').removeClass('active selected').change();
            $form.find('div.ays_pb_aysDropdown div.menu').each(function() {
                $(this).find('div.item:first-child').addClass('active selected').change();
            }).change();

            $form.find('div.ays_pb_current_device_name_pc').fadeOut().change();

            setTimeout(function() {
                $(document).find('div.ays-pb-top-menu').scrollToElementTop();
            }, 100);
        });
        // Reset styles end

        // Add default title to popup if not exist on save start
        $(document).find('#ays_pb_form').on('submit', function(e) {
            if ($(document).find('#ays-pb-popup_title').val() == '') {
                $(document).find('#ays-pb-popup_title').val('Demo Title').trigger('input');
            }

            var $this = $(this)[0];
            if ($(document).find('#ays-pb-popup_title').val() != '') {
                $this.submit();
            } else {
                e.preventDefault();
                $this.submit();
            }
        });
        // Add default title to popup if not exist on save end

        // Background music | Add music start
        $(document).on('click', 'a.add-pb-bg-music', function(e) {
            openMusicMediaUploader(e, $(this));
        });
        // Background music | Add music end

        // Background music | Edit music start
        $(document).find('.ays_pb_sound_opening_btn').on('click', function() {
            var pb_opening_audio = $('.ays-bg-opening-music-audio');
            var pb_opening_audio_src = pb_opening_audio.prop('src','');
            $('input.ays_pb_bg_music_opening_input').val('').trigger('change');
            $('.ays_pb_sound_opening_btn').hide();
        });
        // Background music | Edit music end

        // Background music | Remove music start
        $(document).find('.ays_pb_sound_closing_btn').on('click', function() {
            var pb_opening_audio = $('.ays-bg-closing-music-audio');
            var pb_opening_audio_src = pb_opening_audio.prop('src','');
            $('input.ays_pb_bg_music_closing_input').val('').trigger('change');
            $('.ays_pb_sound_closing_btn').hide();
        });
        // Background music | Remove music end

        // Pro features start
        $(document).find('.ays-pro-features-v2-upgrade-button:not(.ays-pro-features-v2-upgrade-button-view-demo)').hover(
            function() {
                var unlockedImg = 'Unlocked_24_24.svg';
                var imgBox = $(this).find('.ays-pro-features-v2-upgrade-icon');
                var imgUrl = imgBox.attr('data-img-src');
                var newString = imgUrl.replace('Locked_24x24.svg', unlockedImg);

                imgBox.css('background-image', 'url(' + newString + ')');
                imgBox.attr('data-img-src', newString);
            },

            function() {
                var lockedImg = 'Locked_24x24.svg';
                var imgBox = $(this).find('.ays-pro-features-v2-upgrade-icon');
                var imgUrl = imgBox.attr('data-img-src');
                var newString = imgUrl.replace('Unlocked_24_24.svg', lockedImg);

                imgBox.css('background-image', 'url(' + newString + ')');
                imgBox.attr('data-img-src', newString);
            }
        );
        // Pro features end

        // Footer start
        $(document).on('mouseover', '.ays-dashicons', function() {
            var allRateStars = $(document).find('.ays-dashicons');
            var index = allRateStars.index(this);

            allRateStars.removeClass('ays-dashicons-star-filled').addClass('ays-dashicons-star-empty');

            for (var i = 0; i <= index; i++) {
                allRateStars.eq(i).removeClass('ays-dashicons-star-empty').addClass('ays-dashicons-star-filled');
            }
        });

        $(document).on('mouseleave', '.ays-rated-link', function() {
            $(document).find('.ays-dashicons').removeClass('ays-dashicons-star-filled').addClass('ays-dashicons-star-empty');
        });
        // Footer end

        // Popup save start
        $(document).on('click', subButtons, function() {
            var $this = $(this);
    
            $this.addClass('ays-save-button-clicked');
            submitOnce($this);
        });
        // Popup save end

        // Popup category save start
        $(document).on('click', '.button#ays-cat-button-apply, .button#ays-cat-button', function() {
            var catTitle = $(document).find('#ays-title').val();
    
            if (catTitle != '') {
                var $this = $(this);
                subButtons += ', .button#ays-cat-button-apply';
    
                $this.addClass('ays-save-button-clicked');
                submitOnce($this);
            }
        });
        // Popup category save end

        // Go to next/prev popup confirmation end
        $(document).on('click', '#ays-popups-next-button, #ays-popups-prev-button, .ays-pb-next-prev-button-class', function(e) {
            e.preventDefault();
    
            var message = $(this).attr('data-message');
            var confirm = window.confirm(message);
    
            if (confirm === true) {
                window.location.replace($(this).attr('href'));
            }
        });
        // Go to next/prev popup confirmation end

        function submitOnce(subButton) {
            var subLoader = subButton.siblings('.display_none');
    
            subLoader.removeClass('display_none');
            subLoader.css('padding-left', '8px');
            subLoader.css('display', 'inline-flex');
    
            setTimeout(function() {
                $(subButtons).attr('disabled', true);
            }, 50);
            setTimeout(function() {
                $(subButtons).attr('disabled', false);
                subLoader.addClass('display_none');
            }, 5000);
        }
    });

    function aysPopupstripHTML(dirtyString) {
        var container = document.createElement('div');
        var text = document.createTextNode(dirtyString);
        container.appendChild(text);

        return container.innerHTML; // innerHTML will be a xss safe string
    }

    function catFilterForListTable(link, options) {
        if (options.value != '') {
            options.value = '&' + options.what + '=' + options.value;
            var linkModifiedStart = link.split('?')[0];
            var linkModified = link.split('?')[1].split('&');

            for (var i = 0; i < linkModified.length; i++) {
                if (linkModified[i].split('=')[0] == 'ays_result_tab' ) {
                    linkModified.splice(i, 1, 'ays_result_tab=poststuff');
                }
                if (linkModified[i].split('=')[0] == options.what) {
                    linkModified.splice(i, 1);
                }
            }

            linkModified = linkModified.join('&');
            return linkModifiedStart + '?' + linkModified + options.value;
        } else {
            var linkModifiedStart = link.split('?')[0];
            var linkModified = link.split('?')[1].split('&');

            for (var i = 0; i < linkModified.length; i++) {
                if (linkModified[i].split('=')[0] == options.what) {
                    linkModified.splice(i, 1);
                }
            }

            linkModified = linkModified.join('&');
            return linkModifiedStart + '?' + linkModified;
        }
    }

    function showConfirmationIfDelete(e) {
        var $el = $(e.target);
        var elParent = $el.parent();
        var actionSelect = elParent.find('select[name="action"]');
        var action = actionSelect.val();

        if (action === 'bulk-delete') {
            e.preventDefault();
            var confirmDelete = confirm('Are you sure you want to delete?');

            if (confirmDelete) {
                var form = $el.closest('form');
                form.submit();
            }
        }
    }

    function searchForPage(params, data) {
        // If there are no search terms, return all of the data
        if ($.trim(params.term) === '') {
          return data;
        }

        // Do not display the item if there is no 'text' property
        if (typeof data.text === 'undefined') {
          return null;
        }
        var searchText = data.text.toLowerCase();
        // `params.term` should be the term that is used for searching
        // `data.text` is the text that is displayed for the data object
        if (searchText.indexOf(params.term) > -1) {
          var modifiedData = $.extend({}, data, true);
          modifiedData.text += ' (matched)';

          // You can return modified objects from here
          // This includes matching the `children` how you want in nested data sets
          return modifiedData;
        }

        // Return `null` if the term should not be displayed
        return null;
    }

    function closeWarningNotePermanently(el) {
        $.ajax({
            url: pb.ajax,
            type: 'POST',
            data: {action: 'close_warning_note_permanently'},
            success: function(response) {
                var warningNoteContainer = $(el).parents('.ays-pb-cache-warning-note-container');
                warningNoteContainer.fadeOut('slow');
            }
        });
    }

    function toggleMobileSettings() {
        var optionDiv = $(this).parents('.ays_pb_pc_and_mobile_container');
        var deviceNames = optionDiv.find('.ays_pb_current_device_name');
        var mobileOptionDiv = optionDiv.find('.ays_pb_option_for_mobile_device');
        var cbLabel = optionDiv.find('.ays_pb_mobile_settings_container label');

        if ($(this).prop('checked')) {
            deviceNames.addClass('show');
            mobileOptionDiv.addClass('show');
            cbLabel.addClass('active');
        } else {
            deviceNames.removeClass('show');
            mobileOptionDiv.removeClass('show');
            cbLabel.removeClass('active');
        }
    }

    function toggleMobileSettingsCb() {
        var mainContainer = $(this).parent();
        var desktopContainer = mainContainer.find('.ays_pb_option_for_desktop');
        var mobileContainer = mainContainer.find('.ays_pb_option_for_mobile_device');
        var desktopCb = desktopContainer.find('.ays-pb-onoffswitch-checkbox');
        var mobileDeviceCb = mobileContainer.find('.ays-pb-onoffswitch-checkbox');
        var deviceNames = mainContainer.find('.ays_pb_current_device_name');

        if (desktopCb.is(':checked')) {
            if (!mobileContainer.hasClass('show')) {
                mobileContainer.addClass('show');
                mobileDeviceCb.prop('checked', true);
                deviceNames.show().fadeIn('300');
            }
        } else {
            if (!mobileDeviceCb.is(':checked')) {
                mobileContainer.removeClass('show');
                deviceNames.hide().fadeOut('300');
            }
        }
    }

    function openComponentOptions(e) {
        let el = $(e.target.closest('div.open_component_options'));
        let optionsWrapper = el.parents('div.ays_notification_type_components_sortable_wrap');
        let component = el.attr('data-open');

        optionsWrapper.find('.toggle_component_options').addClass('open_component_options');
        optionsWrapper.find('.toggle_component_options').removeClass('close_component_options');
        el.removeClass('open_component_options');
        el.addClass('close_component_options');

        optionsWrapper.find('img.open_component_img').removeClass('display_none');
        optionsWrapper.find('img.close_component_img').addClass('display_none');
        el.find('img.open_component_img').addClass('display_none');
        el.find('img.close_component_img').removeClass('display_none');

        optionsWrapper.find('div.ays_pb_component_option').slideUp();
        optionsWrapper.find('div.ays_pb_component_option[data-window="' + component + '"]').slideDown();
    }

    function closeComponentOptions(e) {
        let el = $(e.target.closest('div.close_component_options'));
        let optionsWrapper = el.parents('div.ays_notification_type_components_sortable_wrap');

        el.removeClass('close_component_options');
        el.addClass('open_component_options');
        el.find('img.close_component_img').addClass('display_none');
        el.find('img.open_component_img').removeClass('display_none');

        optionsWrapper.find('div.ays_pb_component_option').slideUp();
    }

    function aysCheckPopupPosition() {
        var hiddenVal = $(document).find('.pb_position_block #ays-pb-position-val').val();
        var hiddenValMobile = $(document).find('.pb_position_block #ays-pb-position-val-mobile').val();

        if (hiddenVal == '' || hiddenVal == 0) {
            var $this = $(document).find('table#ays-pb-position-table tr td[data-value="center-center"]');
        } else {
            var $this = $(document).find('table#ays-pb-position-table tr td[data-value="' + hiddenVal + '"]');
        }

        if (hiddenValMobile == '' || hiddenValMobile == 0) {
            var $thisMobile = $(document).find('table#ays-pb-position-table-mobile tr td[data-value="center-center"]');
        } else {
            var $thisMobile = $(document).find('table#ays-pb-position-table-mobile tr td[data-value="' + hiddenValMobile + '"]');
        }

        if (hiddenVal == 'center-center' || hiddenVal == '') {
            $(document).find('#popupMargin').hide(500);
            $(document).find('.ays_pb_hr_hide').hide(500);
        } else {
            $(document).find('#popupMargin').show(500);
            $(document).find('.ays_pb_hr_hide').show(500);
        }

        $(document).find('table#ays-pb-position-table td').removeAttr('style');
        $(document).find('table#ays-pb-position-table-mobile td').removeAttr('style');
        $this.css('background-color', '#3d89e0');
        $thisMobile.css('background-color', '#9964b3');
    }

    function aysCheckBgImagePosition() {
        var hiddenVal = $(document).find('.pb_position_block #ays_pb_bg_image_position').val();
        var hiddenValMobile = $(document).find('.pb_position_block #ays_pb_bg_image_position_mobile').val();

        if (hiddenVal == '') {
            var $this = $(document).find('table#ays_pb_bg_image_position_table tr td[data-value="center-center"]');
        }else{
            var $this = $(document).find('table#ays_pb_bg_image_position_table tr td[data-value="' + hiddenVal + '"]');
        }

        if (hiddenValMobile == '') {
            var $thisMobile = $(document).find('table#ays_pb_bg_image_position_table_mobile tr td[data-value="center-center"]');
        }else{
            var $thisMobile = $(document).find('table#ays_pb_bg_image_position_table_mobile tr td[data-value="' + hiddenValMobile + '"]');
        }

        $(document).find('table#ays_pb_bg_image_position_table td').removeAttr('style');
        $(document).find('table#ays_pb_bg_image_position_table_mobile td').removeAttr('style');
        $this.css('background-color','#3d89e0');
        $thisMobile.css('background-color','#9964b3');
    }

    function toggleBackgrounGradient() {
        var pb_gradient_direction = $(document).find('#ays_pb_gradient_direction').val();
        var checked = $(document).find('input#ays-enable-background-gradient').prop('checked');

        switch(pb_gradient_direction) {
            case 'horizontal':
                pb_gradient_direction = 'to right';
                break;
            case 'diagonal_left_to_right':
                pb_gradient_direction = 'to bottom right';
                break;
            case 'diagonal_right_to_left':
                pb_gradient_direction = 'to bottom left';
                break;
            default:
                pb_gradient_direction = 'to bottom';
        }

        if ($(document).find('input#ays-pb-bg-image').val() == '') {
            if (checked) {
                $(document).find('.ays-pb-live-container').css({'background-image': 'linear-gradient(' + pb_gradient_direction + ', ' + $(document).find('input#ays-background-gradient-color-1').val() + ', ' + $(document).find('input#ays-background-gradient-color-2').val() + ')'});
                $(document).find('#ays-image-window').css({'background-image': 'url("https://quiz-plugin.com/wp-content/uploads/2020/02/elefante.jpg"','background-size': 'cover','background-repeat': 'no-repeat','background-position': 'center'});
            } else {
                $(document).find('.ays-pb-live-container').css({'background-image': 'none'});
                $(document).find('#ays-image-window').css({'background-image': 'url("https://quiz-plugin.com/wp-content/uploads/2020/02/elefante.jpg"','background-size': 'cover','background-repeat': 'no-repeat','background-position': 'center'});
            }
        }
    }

    // Media uploaders start
    function openMediaUploaderVideo(e, element) {
        e.preventDefault();

        let aysUploader = wp.media({
            title: 'Upload',
            button: {
                text: 'Upload'
            },
            library: {
                type: 'video'
            },
            multiple: false
        }).on('select', function() {
            let attachment = aysUploader.state().get('selection').first().toJSON();

            element.text(pb.editVideo);

            $('.ays-pb-bg-video-container-main').fadeIn();
            $('video#ays_pb_video_theme_video').attr('src', attachment.url);
            $('input#ays_pb_video_theme').val(attachment.url);
            $(document).find('video.video_theme').attr('src',attachment.url);
        }).open();

        return false;
    }

    function openMediaUploaderImageTypeImg(e, element) {
        e.preventDefault();

        let aysUploader = wp.media({
            title: 'Upload',
            button: {
                text: 'Upload'
            },
            library: {
                type: 'image'
            },
            multiple: false
        }).on('select', function () {
            let attachment = aysUploader.state().get('selection').first().toJSON();

            element.text(pb.editImage);

            $('.ays-pb-image-type-img-container-main').fadeIn();
            $('img#ays_pb_image_type_img').attr('src', attachment.url);
            $('.ays-pb-image-type-img-settings-container').removeClass('display_none');
            $('input#ays_pb_image_type_img_src').val(attachment.url);
            $('img.image_type_img_live').attr('src', attachment.url);
        }).open();

        return false;
    }

    function openMediaUploaderBg(e, element) {
        e.preventDefault();

        let aysUploader = wp.media({
            title: 'Upload',
            button: {
                text: 'Upload'
            },
            library: {
                type: 'image'
            },
            multiple: false
        }).on('select', function() {
            let attachment = aysUploader.state().get('selection').first().toJSON();
            element.text(pb.editImage);
            element.attr('data-add', true);

            var bgImageContainer = $('.ays-pb-bg-image-container-mobile')
            var bgImageTag = $('img#ays-pb-bg-img-mobile');
            var bgImageInp = $('input#ays-pb-bg-image-mobile');

            if (!element.hasClass('ays-pb-add-bg-image-mobile')) {
                bgImageContainer = $('.ays-pb-bg-image-container');
                bgImageTag = $('img#ays-pb-bg-img');
                bgImageInp = $('input#ays-pb-bg-image');

                $('.box-apm').css('background-image', `url('${attachment.url}')`);
                $('.ays_bg_image_box').css({
                    'background-image': `url('${attachment.url}')`,
                    'background-repeat': 'no-repeat',
                    'background-size': 'cover',
                });
            }

            bgImageContainer.parent().fadeIn();
            bgImageTag.attr('src', attachment.url);
            bgImageInp.val(attachment.url).trigger('change');
        }).open();

        return false;
    }

    function openMediaUploaderCloseBtn(e, element) {
        e.preventDefault();

        let aysUploader = wp.media({
            title: 'Upload',
            button: {
                text: 'Upload'
            },
            library: {
                type: 'image'
            },
            multiple: false
        }).on('select', function() {
            let attachment = aysUploader.state().get('selection').first().toJSON();

            element.text(pb.editImage);

            $('.ays_pb_close_btn_bg_img').parent().fadeIn();
            $('img#ays_close_btn_bg_img').attr('src', attachment.url);
            $('input#close_btn_bg_img').val(attachment.url).trigger('change');
            $('img.close_btn_img').attr('src', attachment.url);
            $(document).find('img.close_btn_img').css('display','block');
            $(document).find('label.close_btn_label > .close_btn_text').css('display','none');
        }).open();

        return false;
    }

    function openMusicMediaUploader(e, element) {
        e.preventDefault();

        let aysUploader = wp.media({
            title: 'Upload music',
            button: {
                text: 'Upload'
            },
            library: {
                type: 'audio'
            },
            multiple: false
        }).on('select', function() {
            let attachment = aysUploader.state().get('selection').first().toJSON();

            element.next().attr('src', attachment.url);
            element.parent().find('input.ays_pb_bg_music').val(attachment.url).trigger('change');
            element.parent().find('.ays_pb_sound_close_btn').show();
        }).open();

        return false;
    }
    // Media uploaders start

})(jQuery);
