'use strict'

jQuery(document).ready(function ($) {

    $('.wpie-tabs').on('click', '.wpie-tab-label', function () {
        $('.wpie-tabs .wpie-tab-label').removeClass('selected');
        $(this).addClass('selected');
    });

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('notice')) {
        const notice = $('.wpie-notice');
        $(notice).addClass('is-active');
        setTimeout(function () {
            $(notice).removeClass('is-active');
        }, 5000);
    }

    $('.wpie-settings__main').on('click', '.wpie-tab__link', function () {
        const parent = $(this).closest('.wpie-tabs-wrapper');
        const links = $(parent).find('.wpie-tab__link');
        const settings = $(parent).find('.wpie-tab-settings');
        const index = $(links).index(this);

        $(links).removeClass('is-active');
        $(this).addClass('is-active');
        $(settings).removeClass('is-active');
        $(settings).eq(index).addClass('is-active');
    });

    $('.can-copy').on('click', function () {
        const parent = $(this).parent();
        const input = $(parent).find('input');
        const originalTooltip = $(this).attr("data-tooltip");
        const currentElement = $(this);

        navigator.clipboard.writeText(input.val()).then(() => {
            currentElement.attr("data-tooltip", "Copied");
            setTimeout(function () {
                currentElement.attr("data-tooltip", originalTooltip);
            }, 1000);
        });
    });

    // Post options
    const form = document.getElementById('wpie-settings');

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            getTinymceContent();

            const formData = new FormData(form);
            let jsonObject = {param: {}};

            formData.forEach((value, key) => {

                if (!key.includes('param')) {
                    jsonObject[key] = value;
                    return;
                }
                let newKey = key.replace("param", "");
                let keys = newKey.match(/[^\[\]]+/g);
                let count = keys.length;

                if (count === 1) {
                    if (!newKey.includes('[]')) {
                        jsonObject.param[`${keys[0]}`] = value;
                    } else {
                        if (!jsonObject.param.hasOwnProperty(`${keys[0]}`)) {
                            jsonObject.param[`${keys[0]}`] = {};
                        }
                        const nextKey = Object.keys(jsonObject.param[`${keys[0]}`]).length;
                        jsonObject.param[`${keys[0]}`][nextKey] = value;
                    }
                }

                if (count === 2) {
                    if (!newKey.includes('[]')) {
                        if (!jsonObject.param.hasOwnProperty(`${keys[0]}`)) {
                            jsonObject.param[`${keys[0]}`] = {};
                        }
                        jsonObject.param[`${keys[0]}`][`${keys[1]}`] = value;
                    } else {
                        if (!jsonObject.param.hasOwnProperty(`${keys[0]}`)) {
                            jsonObject.param[`${keys[0]}`] = {};
                        }
                        if (!jsonObject.param[`${keys[0]}`].hasOwnProperty(`${keys[1]}`)) {
                            jsonObject.param[`${keys[0]}`][`${keys[1]}`] = {};
                        }
                        const nextKey = Object.keys(jsonObject.param[`${keys[0]}`][`${keys[1]}`]).length;
                        jsonObject.param[`${keys[0]}`][`${keys[1]}`][nextKey] = value;
                    }
                }
            });

            const submit = document.getElementById('submit_settings');
            submit.setAttribute('disabled', 'disabled');

            const spinner = document.querySelector('.wpie-action__btn .spinner');
            spinner.classList.add('is-active');

            fetch(wowp_ajax_object.url + '?action=' + wowp_ajax_object.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    security: wowp_ajax_object.security,
                    info: jsonObject,
                }),

            })
                .then(response => response.json())
                .then(data => {

                    if (data.success) {

                        submit.removeAttribute('disabled');
                        spinner.classList.remove('is-active');
                        const url = new URL(window.location);
                        const params = url.searchParams;

                        if (params.get('action') === 'new') {
                            params.set('action', 'update');
                            params.set('id', data.data.id);
                            $('#tool_id').val(data.data.id);
                            const newUrl = `${window.location.pathname}?${params.toString()}`;
                            history.pushState(null, '', newUrl);
                        }

                        const notice = $('.wpie-notice');
                        $(notice).addClass('is-active');

                        setTimeout(function () {
                            $(notice).removeClass('is-active');
                        }, 2000);


                    }
                });

        });
    }

    function getTinymceContent() {
        $('[id^="wp-wpie-fulleditor-"]').each(function () {
            let editorWrapper = $(this);
            if (editorWrapper.hasClass('tmce-active')) {
                let editorId = editorWrapper.attr('id').replace(/^wp-/, '').replace(/-wrap$/, '');
                let content = tinyMCE.get(editorId)?.getContent();
                if (content !== undefined) {
                    $('#' + editorId).val(content);
                }
            }
        });
    }

    $('#demoUpload button[data-menu]').on('click', function () {
        const menu = $(this).data('menu');

        fetch(wowp_ajax_object.url, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                action: wowp_ajax_object.prefix + '_upload_demo',
                security: wowp_ajax_object.security,
                menu: menu
            })
        }).then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
    });

});