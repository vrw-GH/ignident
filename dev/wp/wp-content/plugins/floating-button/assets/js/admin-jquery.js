'use strict';


jQuery(function ($) {

    extaStyle();

    $('#wowp-settings').on('submit', function (e) {

        const id = $('#tool_id').val();
        let activeTab = {
            [id]: {
                tabs: {},
                item: {}
            }
        };

        $('.wowp-tabs-link').each(function (index, element) {
            activeTab[id].tabs[index] = $(element).find('a.is-active').index();
        });

        $('.wowp-settings details.wowp-item').each(function (index, element) {
            activeTab[id].item[index] = $(element).attr('open') ? 1 : 0;
        });

        sessionStorage.setItem("wowpActiveTab", JSON.stringify(activeTab));
    })

    $('#settings-tab').on('click', 'a', chooseTabs);

    function chooseTabs() {
        const attr = $(this).attr('data-tab');
        $('#settings-tab a').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        $('#settings-content .tab-content').removeClass('tab-content-active');
        $('[data-content=' + attr + ']').addClass('tab-content-active');
        sessionStorage.setItem("wowpTab", attr);

    }

    setTabs();

    function setTabs() {
        const id = $('#tool_id').val();
        if (id === "0") {
            sessionStorage.removeItem('wowpTab');
        }
        const attr = sessionStorage.getItem("wowpTab");
        if (attr === null) {
            return false;
        }
        $('#settings-tab a').removeClass('nav-tab-active');
        $('a[data-tab="' + attr + '"]').addClass('nav-tab-active');
        $('#settings-content .tab-content').removeClass('tab-content-active');
        $('[data-content=' + attr + ']').addClass('tab-content-active');
    }

    setActiveTabs();

    function setActiveTabs() {
        const tool_id = $('#tool_id').val();
        const url_id = getUrlParameter('id');
        const store = JSON.parse(sessionStorage.getItem("wowpActiveTab"));
        if (store === null) {
            return false;
        }
        if (url_id === null) {
            return false;
        }

        let obj = store[tool_id];

        if(obj === undefined) {
            return false;
        }

        $('.wowp-tabs-link').each(function (index, element) {
            const linkIndex = obj.tabs[index];
            $(element).find('a').removeClass('is-active');
            $(element).find('a').eq(linkIndex).addClass('is-active');
            const parent = $(element).closest('.wowp-tabs');
            $(parent).find('.wowp-tabs-content').removeClass('is-active');
            $(parent).find('.wowp-tabs-content').eq(linkIndex).addClass('is-active');
        });


        $('.wowp-settings details.wowp-item').each(function (index, element) {
            const att = obj.item[index];
            if (att === 1) {
                $(element).attr('open', '');
            }
        });
    }

    function getUrlParameter(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        const regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        const results = regex.exec(location.search);
        return results === null ? null : decodeURIComponent(results[1].replace(/\+/g, ' '));
    };


    $(".wowp-settings").on("click", '.wowp-tabs .wowp-tabs-link a', function (e) {
        e.preventDefault();
        const index = $(this).index();
        const parent = $(this).closest(".wowp-tabs");
        $(parent).find('.wowp-tabs-link a').removeClass('is-active');
        $(this).addClass('is-active');
        $(parent).find('.wowp-tabs-content').removeClass('is-active');
        $(parent).find('.wowp-tabs-content').eq(index).addClass('is-active');
    });


    // Set value in input hidden for checkbox
    $('.checkbox-helper').each(function() {
        const check = $(this).val();
        if(check == '1') {
            $(this).prev('input:checkbox').prop('checked', true);
        } else {
            $(this).prev('input:checkbox').prop('checked', false);
        }
    });

    $('.wowp-settings').on('click', 'input:checkbox', function() {
        checkboxchecked(this);
    });

    function checkboxchecked(el) {
        if ($(el).prop('checked')) {
            $(el).next('input[type="hidden"]').val('1');
        } else {
            $(el).next('input[type="hidden"]').val('');
        }
    }


    // Pro page
    $('.w_card-description').on('click', function () {
        $(this).toggleClass('is-open');
    })

    $('.menu-items').sortable();

    $('.choose-icon select[name^="param["]').each(setIcons);

    function setIcons() {
        $(this).fontIconPicker({
            emptyIcon: false,
            allCategoryText: 'Show all',
        });
    }


    $(".wowp-settings").on("change", '.choose-icon select', chooseIcon);
    $('.choose-icon select').each(chooseIcon);

    function chooseIcon() {
        const item = $(this).parents('.wowp-item');
        const icon = $(item).find('.wowp-item_heading_icon');
        const choose = $(this).val();
        if (choose !== '') {
            $(icon).html(`<i class="${choose}"></i>`);
        }
    }


    $('[data-option="close_button_icon"] select').fontIconPicker({
        emptyIcon: false,
        allCategoryText: 'Show all',
    });

    $('.wowp-field-color').wpColorPicker({
        change: function (event, ui) {
            changeIconColor(this, ui.color.toString());
        },
    });

    $(".item-icon-bg input, .item-icon-color input").each(function (index, element) {
        changeIconColor(element);
    });

    function changeIconColor(element, color = null) {
        if (color === null) {
            color = $(element).val();
        }
        const parent = $(element).parents('.wowp-field');
        const item = $(element).parents('.wowp-item');
        const icon = $(item).find('.wowp-item_heading_icon');

        if ($(parent).hasClass("item-icon-bg")) {
            $(icon).css('background-color', color);
        }

        if ($(parent).hasClass("item-icon-color")) {
            $(icon).css('color', color);
        }
    }

    $('.label-text input').each(label);
    $(".wowp-settings").on("keyup", '.label-text input', label);

    function label() {
        const item = $(this).parents('.wowp-item');
        const label = $(item).find('.wowp-item_heading_label');
        let text = $(this).val();
        if (text === '') {
            text = '(no label)';
        }
        $(label).text(text);
    }

    // Tooltip options
    $('.tooltip-checkbox input').each(tooltip);
    $(".wowp-settings").on("click", '.tooltip-checkbox input', tooltip);

    function tooltip() {
        const chekbox = $(this);
        const parent = $(this).parent();
        const sibling = $(parent).siblings(".tooltip-open");
        if ($(chekbox).is(':checked')) {
            $(sibling).removeClass('is-hidden');
        } else {
            $(sibling).addClass('is-hidden');
        }
    }

    customSize();
    $('[name="param[size]"]').on('change', customSize);

    function customSize() {
        const type = $('[name="param[size]"]').val();
        const custom = $('.custom-font-size');
        $(custom).fadeOut();
        if (type === 'flBtn-custom') {
            $(custom).fadeIn();
        }
    }

    tooltipSize();
    $('[name="param[tooltip_size_check]"]').on('change', tooltipSize);

    function tooltipSize() {
        const type = $('[name="param[tooltip_size_check]"]').val();
        const custom = $('.tooltip-size');
        $(custom).fadeOut();
        if (type === 'custom') {
            $(custom).fadeIn();
        }
    }

    // Button type
    $('.button-type select').each(buttonType);
    $(".wowp-settings").on("change", '.button-type select', buttonType);

    function buttonType() {
        const type = $(this).val();
        const text = $(this).find('option:selected').text();
        const parent = $(this).closest('.wowp-tabs-content');
        $(parent).find('div:not(.button-type)').addClass('is-hidden');

        switch (type) {
            case 'main':
                $(parent).find('.button-type-main').removeClass('is-hidden');
                break;
            case 'link':
                $(parent).find('.button-type-link').removeClass('is-hidden');
                $(parent).find('.button-type-link-open').removeClass('is-hidden');
                break;
            case 'share':
                $(parent).find('.button-type-share').removeClass('is-hidden');
                break;
            case 'smoothscroll':
            case 'login':
            case 'logout':
            case 'lostpassword':
                $(parent).find('.button-type-link').removeClass('is-hidden');
                break;
            case 'translate':
                $(parent).find('.button-type-gtranslate').removeClass('is-hidden');
                break;
            case 'menu':
                $(parent).find('.button-type-menus').removeClass('is-hidden');
                break;

        }

        const item = $(this).closest('.wowp-item');
        $(item).find('.wowp-item_heading_type').text(text);
    }

    // Check Icon type
    $('.icon-type select').each(iconType);
    $(".wowp-settings").on("change", '.icon-type select', iconType);

    function iconType() {
        const type = $(this).val();
        const parent = $(this).closest('.wowp-tabs-content');
        $(parent).find('.icon-type-default, .icon-type-img, .icon-type-emoji, .icon-type-class').addClass('is-hidden');
        switch (type) {
            case 'default':
                $(parent).find('.icon-type-default').removeClass('is-hidden');
                selectIcons(type, parent);
                break;
            case 'img':
                $(parent).find('.icon-type-img').removeClass('is-hidden');
                selectIcons(type, parent);
                break;
            case 'emoji':
                $(parent).find('.icon-type-emoji').removeClass('is-hidden');
                selectIcons(type, parent);
                break;
            case 'class':
                $(parent).find('.icon-type-class').removeClass('is-hidden');
                selectIcons(type, parent);
                break;
        }

    }

    $(".wowp-settings").on("keyup input", '.icon-type-emoji input', chooseEmoji);

    function chooseEmoji() {
        const emoji = $(this).val();
        changeIconPreview($(this), emoji);
    }

    $(".wowp-settings").on("keyup input", '.icon-type-img-url input', chooseImg);

    function chooseImg() {
        const imgVal = $(this).val();
        const img = `<img src="${imgVal}">`;
        changeIconPreview($(this), img);
    }

    function selectIcons(type, parent) {
        let icon;
        switch (type) {
            case 'default':
                icon = $(parent).find('.selected-icon').html();
                break;
            case 'img':
                const img_url = $(parent).find('.icon-type-img-url input').val();
                icon = `<img src="${img_url}">`;
                break;
            case 'emoji':
                const emoji = $(parent).find('.icon-type-emoji input').val();
                icon = emoji;
                break;
            case 'class':
                icon = '<span class="dashicons dashicons-format-image"></span>';
                break;
        }
        changeIconPreview(parent, icon);
    }

    function changeIconPreview(child, icon) {
        const parent = $(child).closest('.wowp-item');
        const iconBox = $(parent).find('.wowp-item_heading_icon');
        $(iconBox).html(icon);
    }


    //Check Icon close
    // Tooltip options
    $('.icon-type-close input').each(iconClose);
    $(".wowp-settings").on("click", '.icon-type-close input', iconClose);

    function iconClose() {
        const chekbox = $(this);
        const parent = $(this).parent();
        const sibling = $(parent).siblings(".icon-type-close-choose");
        if ($(chekbox).is(':checked')) {
            $(sibling).removeClass('is-hidden');
        } else {
            $(sibling).addClass('is-hidden');
        }
    }

    // Upload the image
    let upload_button;

    $(".wowp-settings").on("click", '.icon-type-img-url label', uploadImage);

    function uploadImage(event) {
        upload_button = $(this);
        let frame;
        event.preventDefault();
        if (frame) {
            frame.open();
            return;
        }
        const parent = $(this).closest('.wowp-tabs-content');
        const imgUrl = $(parent).find('.icon-type-img-url input');
        const imgAlt = $(parent).find('.icon-type-img-alt input');

        frame = wp.media();
        frame.on('select', function () {
            // Grab the selected attachment.
            const attachment = frame.state().get('selection').first();
            let attachmentUrl = attachment.attributes.url;
            attachmentUrl = attachmentUrl.replace('-scaled.', '.');
            const altText = attachment.attributes.alt;

            frame.close();

            $(imgUrl).val(attachmentUrl);
            $(imgAlt).val(altText);
            const img = `<img src="${attachmentUrl}">`;
            changeIconPreview($(imgUrl), img);
        });
        frame.open();

    }

    // Delete Item
    $(".wowp-settings").on("click", '.wowp-item_heading .dashicons-trash', removeItem);

    function removeItem() {
        const userConfirmed = confirm("Are you sure you want to remove this element?");
        if (userConfirmed) {
            const parent = $(this).closest('.wowp-item');
            $(parent).remove();
        }
    }

    function isNumber(value) {
        return !isNaN(parseFloat(value)) && isFinite(value);
    }

    function refreashel() {
        $('.choose-icon select[name^="param["]').each(setIcons);

        $('.wowp-field-color').wpColorPicker({
            change: function (event, ui) {
                changeIconColor(this, ui.color.toString());
            },
        });

        $(".item-icon-bg input, .item-icon-color input").each(function (index, element) {
            changeIconColor(element);
        });

        $('.tooltip-checkbox input').each(tooltip);
        $('.button-type select').each(buttonType);
        $('.icon-type select').each(iconType);
        $('.icon-type-close input').each(iconClose);
    }

    $('#add_menu_1, #add_menu_2').on('click', function (e) {
        e.preventDefault();
        const id = $(this).attr('id');
        if (id === 'add_menu_1') {
            const temlate = $('#clone-menu-1').clone().html();
            $('#wowp-menu-1').append(temlate);
        }

        if (id === 'add_menu_2') {
            const temlate = $('#clone-menu-2').clone().html();
            $('#wowp-menu-2').append(temlate);
        }

        refreashel();
    });

    $('#add_display').on('click', function (e) {
        e.preventDefault();

        const temlate = $('#template-display').clone().html();

        $(temlate).insertBefore('#display-rules .btn-add-display');
        $('#display-rules .display-option select').each(displayRules);
    });

    $('#display-rules').on('click', '.dashicons-trash', function () {
        const parent = $(this).closest('.wowp-fields-group');
        $(parent).remove();
    });

    $('#display-rules .display-option select').each(displayRules);
    $('#display-rules').on('change', '.display-option select', displayRules);

    function displayRules() {
        let type = $(this).val();
        const parent = $(this).closest('.wowp-fields-group');
        $(parent).find('.display-operator, .display-ids, .display-pages').addClass('is-hidden');
        if(type.indexOf('custom_post_selected') !== -1) {
            type = 'post_selected';
        }
        if(type.indexOf('custom_post_tax') !== -1) {
            type = 'post_category';
        }
        switch (type) {
            case 'post_selected':
            case 'post_category':
            case 'page_selected':
                $(parent).find('.display-operator, .display-ids').removeClass('is-hidden');
                break;
            case 'page_type':
                $(parent).find('.display-operator, .display-pages').removeClass('is-hidden');
                break;

        }

    }

    $('[data-option="include_more_screen"] input[type="checkbox"], [data-option="include_mobile"] input[type="checkbox"]').each(devicesRules);
    $('[data-option="include_more_screen"] input[type="checkbox"], [data-option="include_mobile"] input[type="checkbox"]').on('click', devicesRules);

    function devicesRules() {
        const parent = $(this).parents('.wowp-field');
        const sibling = $(parent).siblings(".wowp-field");
        if ($(this).is(':checked')) {
            $(sibling).removeClass('is-hidden');
        } else {
            $(sibling).addClass('is-hidden');
        }
    }

    $('[name="param[item_user]"]').each(usersRule);
    $('[name="param[item_user]"]').on('change', usersRule);

    function usersRule() {
        const user = $(this).val();
        const parent = $(this).closest('fieldset');
        const boxRoles = $(parent).find('.wowp-users-roles');
        $(boxRoles).addClass('is-hidden');
        if (user === '2') {
            $(boxRoles).removeClass('is-hidden');
        }
    }

    $('.wowp-users-roles .wowp-field:first input:first').each(checkAllRoles);
    $('.wowp-users-roles .wowp-field:first input:first').on('change', checkAllRoles);

    function checkAllRoles() {
        const checkboxes = $('.wowp-users-roles input[type="checkbox"]');
        if ($(this).is(':checked')) {
            $(checkboxes).prop('checked', true);
        }
    }

    // Schedule options
    $('.wowp-dates input[type="checkbox"]').each(datesSchedule);
    $('#schedule').on('click', '.wowp-dates input', datesSchedule);

    function datesSchedule() {
        const parent = $(this).closest('.wowp-fields-group');
        if ($(this).is(':checked')) {
            $(parent).find('.wowp-date-input').removeClass('is-hidden');
        } else {
            $(parent).find('.wowp-date-input').addClass('is-hidden');
        }
    }

    $('#add-schedule').on('click', function (e) {
        e.preventDefault();

        const temlate = $('#clone-schedule').clone().html();

        $(temlate).insertBefore('#schedule .wowp-btn-actions');
        $('.wowp-dates input[type="checkbox"]').each(datesSchedule);
    });

    $('#schedule').on('click', '.dashicons-trash', function () {
        const parent = $(this).closest('.wowp-fields-group');
        $(parent).remove();
    });

    // Main button animation
    $('#button_animation').on('change', mainBtnAnim);
    mainBtnAnim();

    function mainBtnAnim() {
        const type = $('#button_animation').val();
        $('.btn-animation').addClass('is-hidden');
        if (type !== '') {
            $('.btn-animation').removeClass('is-hidden');
        }
    }


    // Languages options
    $('[data-option="depending_language"] input[type="checkbox"]').each(languageOn);
    $('[data-option="depending_language"] input[type="checkbox"]').on('click', languageOn);

    function languageOn() {
        const languages = $(this).parents('.wowp-field').siblings('.wowp-field');
        if ($(this).is(':checked')) {
            $(languages).removeClass('is-hidden');
        } else {
            $(languages).addClass('is-hidden');
        }
    }

    function extaStyle() {


        if(!$('#paramextra_style').length) {
            return false;
        }

        const editorSettings = wp.codeEditor.defaultSettings ? _.clone(wp.codeEditor.defaultSettings) : {};
        const codemirror_gen =
            {
                "mode": 'css',
                "indentUnit": 2,
                "indentWithTabs": true,
                "inputStyle": "contenteditable",
                "lineNumbers": true,
                "lineWrapping": true,
                "styleActiveLine": true,
                "continueComments": true,
                "extraKeys": {
                    "Ctrl-Space": "autocomplete",
                    "Ctrl-\/": "toggleComment",
                    "Cmd-\/": "toggleComment",
                    "Alt-F": "findPersistent",
                    "Ctrl-F": "findPersistent",
                    "Cmd-F": "findPersistent"
                },
                "direction": "ltr",
                "gutters": ["CodeMirror-lint-markers"],
                "lint": true,
                "autoCloseBrackets": true,
                "autoCloseTags": true,
                "matchTags": {
                    "bothTags": true
                },
                "tabSize": 2,

            };

        const css_code = $('#paramextra_style');
        editorSettings.codemirror = _.extend({}, editorSettings.codemirror, codemirror_gen);
        const tabStyle = $('.tab-content[data-content="style"]');
        $(tabStyle).addClass('tab-content-active');
        wp.codeEditor.initialize($(css_code), editorSettings);
        $(tabStyle).removeClass('tab-content-active');
    }

});
