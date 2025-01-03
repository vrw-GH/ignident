(function ($) {
    'use strict';

    $(document).on('click', '[data-slug="ays-popup-box"] .deactivate a', function (e) {
        e.preventDefault();
        swal({
            html:"<h2>Do you want to upgrade to Pro version or permanently delete the plugin?</h2><ul><li>Upgrade: Your data will be saved for upgrade.</li><li>Deactivate: Your data will be deleted completely.</li></ul>",
            footer: '<a href="javascript:void(0);" class="ays-pb-temporary-deactivation">Temporary deactivation</a>',
            type: 'question',
            showCancelButton: true,
            showCloseButton: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Upgrade',
            cancelButtonText: 'Deactivate',
            confirmButtonClass: 'ays-pb-upgrade-button',
            cancelButtonClass: 'ays-pb-cancel-button',
            customClass: 'ays-pb-deactivate-popup',
        }).then((result) => {

            if( result.dismiss && result.dismiss == 'close' ){
                return false;
            }

            var upgrade_plugin = false;
            if (result.value) upgrade_plugin = true;
            var data = {action: 'deactivate_plugin_option_pb', upgrade_plugin: upgrade_plugin};
            $.ajax({
                url: popup_box_ajax.ajax_url,
                method: 'post',
                dataType: 'json',
                data: data,
                success:function () {
                    window.location = $(document).find('[data-slug="ays-popup-box"]').find('.deactivate').find('a').attr('href');
                }
            });
        });
        return false;
    });

    $(document).on('click', '.ays-pb-temporary-deactivation', function (e) {
        e.preventDefault();

        $(document).find('.ays-pb-upgrade-button').trigger('click');
    });

})(jQuery);