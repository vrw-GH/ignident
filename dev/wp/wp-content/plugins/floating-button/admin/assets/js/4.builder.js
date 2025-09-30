'use strict';
(function ($) {

    $.fn.wowpLiveBuilder = function () {
        this.each(function (index, element) {
            const labelText = $(this).find('[data-field*="item_tooltip"]').val();
            const linkType = $(this).find('[data-field*="item_type"]').val();
            let typeText = $(this).find('[data-field*="item_type"] option:selected').text();

            if(linkType === 'share') {
                const text = $(this).find('[data-field*="item_share"] option:selected').text();
                typeText = typeText + ': '+text;
            }

            if(linkType === 'translate') {
                const text = $(this).find('[data-field*="gtranslate"] option:selected').text();
                typeText = typeText + ': '+text;
            }

            if(linkType === 'smoothscroll') {
                const text = $(this).find('[data-field*="item_link"]').val();
                typeText = typeText + ': '+text;
            }

            const iconValue = getIcon(this);

            const sub = $(element).find('.wpie-item__parent');

            if ($(element).hasClass('shifted-right')) {
                sub.val(1);
            }  else {
                sub.val(0);
            }


            const icon = $(this).find('.wpie-item_heading_icon');
            const label = $(this).find('.wpie-item_heading_label');
            const type = $(this).find('.wpie-item_heading_type');

            const color = $(this).find('[data-field*="icon_color"]').val();
            const hcolor = $(this).find('[data-field*="icon_hcolor"]').val();
            const bcolor = $(this).find('[data-field*="button_color"]').val();
            const hbcolor = $(this).find('[data-field*="button_hcolor"]').val();

            icon.css({'color': color, 'background': bcolor});

            icon.add(label).hover(
                function () { // This runs when the mouse enters either the icon or label
                    icon.css({'color': hcolor, 'background': hbcolor});
                },
                function () { // This runs when the mouse leaves either the icon or label
                    icon.css({'color': color, 'background': bcolor});
                }
            );


            label.text(labelText);
            type.text(typeText);
            icon.html(iconValue);
        });

        function getIcon(element) {
            const iconRotate = $(element).find('[data-field*="icon_rotate"]').val();
            const iconFlip = $(element).find('[data-field*="icon_flip"]').val();

            let style = ' style="';
            if (iconRotate !== '' || iconRotate !== '0') {
                style += `rotate: ${iconRotate}deg;`;
            }

            if (iconFlip !== '') {
                if (iconFlip === '-flip-horizontal') {
                    style += `scale: -1 1;`;
                }
                if (iconFlip === '-flip-vertical') {
                    style += `scale: 1 -1;`;
                }
                if (iconFlip === '-flip-both') {
                    style += `scale: -1 -1;`;
                }
            }

            style += '"';

            const type = $(element).find('[data-field*="icon_type"]').val();

            if (type === 'default') {
                let icon = $(element).find('.selected-icon').html();
                if (icon === undefined || $.trim(icon) === '<i class="fip-icon-block"></i>') {
                    icon = $(element).find('[data-field*="item_icon"]').val();
                    icon = `<i class="${icon}"></i>`;
                }
                icon = icon.replace('class=', style + ' class=');
                return icon;
            }

            if (type === 'img') {
                let icon = $(element).find('[data-field*="custom_icon_url"]').val();
                return `<img src="${icon}" ${style}>`;
            }

            if (type === 'class') {
                let icon = $(element).find('[data-field*="custom_icon_class"]').val();
                return `<i class="dashicons dashicons-camera-alt" ${style}></i>`;
            }

            if (type === 'emoji') {
                let icon = $(element).find('[data-field*="custom_icon_emoji"]').val();
                return `<span ${style}>${icon}</span>`;
            }

            if (type === 'text') {
                let icon = $(element).find('[data-field|="menu_1-item_custom_text"]').val();
                return `<span ${style}>${icon}</span>`;
            }

            return '';

        }

        function isValidURL(string) {
            var regex = new RegExp(
                '^(https?:\\/\\/)?' + // protocol
                '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|' + // domain name
                '((\\d{1,3}\\.){3}\\d{1,3}))' + // OR ip (v4) address
                '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*' + // port and path
                '(\\?[;&a-z\\d%_.~+=-]*)?' + // query string
                '(\\#[-a-z\\d_]*)?$', 'i'); // fragment locator
            return !!regex.test(string);
        }
    }

}(jQuery));