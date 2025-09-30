'use strict';

jQuery(document).ready(function ($) {

    const selectors = {
        settings: '.wpie-settings__main',
        show: '[data-field="show"]',
        add_rules: '.wpie-add-rule',
        users: '[data-field="users"]',
        add_schedule: '.wpie-add-schedule',
        dates: '[data-field="dates"]',
        remove: '.wpie-remove',
        language_on: '[data-field="language_on"]',
        language: '[data-field="language"]',
    };

    function set_up() {
        $(selectors.users).each(change_users);
        $(selectors.show).each(change_show);
        $(selectors.dates).each(change_dates);
        $(selectors.language_on).each(language_on);
        $(selectors.language).each(language);
    }

    function initialize_events() {
        $(selectors.settings).on('click', selectors.add_rules, clone_rules);
        $(selectors.settings).on('change', selectors.show, change_show);
        $(selectors.settings).on('change', selectors.users, change_users);
        $(selectors.settings).on('click', selectors.add_schedule, clone_schedule);
        $(selectors.settings).on('change', selectors.dates, change_dates);
        $(selectors.settings).on('click', selectors.remove, remove_item);
        $(selectors.settings).on('change', selectors.language_on, language_on);
        $(selectors.settings).on('change', selectors.language, language);

    }

    function clone_rules(e) {
        e.preventDefault();
        const parent = get_parent_fields($(this), '.wpie-fieldset');
        const selector = $(parent).find('hr');
        const template = $('#template-rules').clone().html();
        $(template).insertBefore($(selector));
    }

    function change_show() {
        const $this = $(this);
        const parent = get_parent_fields($this);
        const box = get_field_box($this);

        // making direct jQuery object of parent fields
        const fields = $('[data-field-box]', parent).not(box);
        fields.addClass('is-hidden');

        let type = $this.val();

        if (type.includes('custom_post_selected')) {
            type = 'post_selected';
        } else if (type.includes('custom_post_tax')) {
            type = 'post_category';
        }

        let selectorsToUnhide = [];

        switch (type) {
            case 'post_selected':
            case 'post_category':
            case 'post_tag':
            case 'page_selected':
            case '_is_category':
            case '_is_tag':
            case '_is_author':
                selectorsToUnhide = ['operator', 'ids'];
                break;
            case 'page_type':
                selectorsToUnhide = ['operator', 'page_type'];
                break;
            case 'url_contains':
            case 'url_referrer':
                selectorsToUnhide = ['operator', 'custom_url'];
                break;
            default:
                // Do nothing
                return;
        }

        selectorsToUnhide.forEach(selector => {
            $(`[data-field-box="${selector}"]`, parent).removeClass('is-hidden');
        });
    }
    function change_users() {
        const type = $(this).val();
        const fieldset = get_parent_fields($(this), '.wpie-fieldset');
        const fields = fieldset.find('.wpie-fields').eq(1);
        $(fields).addClass('is-hidden');
        if (type === '2') {
            $(fields).removeClass('is-hidden');
        }
    }

    function clone_schedule(e) {
        e.preventDefault();
        const parent = get_parent_fields($(this), '.wpie-fieldset');
        const selector = $(parent).find('hr');
        const template = $('#template-schedule').clone().html();
        $(template).insertBefore($(selector));
        $(selectors.dates).each(change_dates);
    }

    function change_dates() {
        const type = $(this).val();
        const parent = get_parent_fields($(this));
        const box = get_field_box($(this));
        const fields = $(parent).find('[data-field-box="date_start"], [data-field-box="date_end"]');
        $(fields).addClass('is-hidden');
        if (type === 'enabled') {
            $(fields).removeClass('is-hidden');
        }
    }

    function remove_item() {
        const userConfirmed = confirm("Are you sure you want to remove this element?");
        if (userConfirmed) {
            const parent = get_parent_fields($(this));
            $(parent).remove();
        }
    }

    function language_on() {
        if($(this).is(':checked')) {
            $(selectors.language).each(language);
        } else {
            const locale = $('[data-field-box="locale"]');
            locale.addClass('is-hidden');
        }
    }
    function language() {
        const type = $(this).val();
        const locale = $('[data-field-box="locale"]');
        locale.addClass('is-hidden');
        if (type === 'custom' && $('[data-field="language_on"]').is(':checked')) {
            locale.removeClass('is-hidden');
        }
    }

    function initialize() {
        set_up();
        initialize_events();
    }

    function get_parent_fields($el, $class = '.wpie-fields') {
        return $el.closest($class);
    }

    function get_field_box($el, $class = '.wpie-field') {
        return $el.closest($class);
    }

    initialize();
});