jQuery(function ($) {
    $(document).ready(function () {
        // $('.pdfp_insert_pdf_btn').click(open_media_window);
        setTimeout(() => {
            document.querySelectorAll('.pdfp_insert_pdf_btn').forEach(btn => {
                btn.addEventListener('click', open_media_window);
            }) // end fo
        }, 1000);

    });


    function open_media_window() {
        if (this.window === undefined) {
            this.window = wp.media({
                title: 'Choose PDF File',
                library: { type: 'application/pdf' },
                multiple: false,
                button: { text: 'Embed This File' }
            });

            var self = this; // Needed to retrieve our variable in the anonymous function below
            this.window.on('select', function () {
                var first = self.window.state().get('selection').first().toJSON();
                wp.media.editor.insert('[pdf_embed url="' + first.url + '"]');
            });
        }

        this.window.open();
        return false;
    }


});