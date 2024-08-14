jQuery(document).ready(function () {
    const datePickerBtn = jQuery('.js-date-range-picker-btn');
    const datePickerElement = jQuery('.js-date-range-picker-input');
    const datePickerForm = jQuery('.js-date-range-picker-form');

    // Update the week start day based on WordPress setting
    if (datePickerBtn.length) {
        moment.locale('en', {
            week: {
                dow: parseInt(wps_js._('start_of_week'))
            }
        });
    }



    function phpToMomentFormat(phpFormat) {
        const formatMap = {
            'd': 'DD',
            'j': 'D',
            'S': 'Do',
            'n': 'M',
            'm': 'MM',
            'F': 'MMMM',
            'M': 'MMM',
            'y': 'YY',
            'Y': 'YYYY'
        };

        return phpFormat.replace(/([a-zA-Z])/g, (match) => formatMap[match] || match);
    }

    if (datePickerBtn.length && datePickerElement.length && datePickerForm.length) {
        datePickerBtn.on('click', function () {
            datePickerElement.trigger('click');
        });

        let ranges = {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'This Week': [moment().startOf('week'), moment().endOf('week')],
            'Last Week': [moment().subtract(1, 'week').startOf('week'), moment().subtract(1, 'week').endOf('week')],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'Last 90 Days': [moment().subtract(89, 'days'), moment()],
            'Last 6 Months': [moment().subtract(6, 'month'), moment()],
            'This Year': [moment().startOf('year'), moment().endOf('year')]
        };

        if (datePickerBtn.hasClass('js-date-range-picker-all-time')) {
            ranges['All Time'] = [moment(0), moment()];
        }

        const phpDateFormat = datePickerBtn.attr('data-date-format') ? datePickerBtn.attr('data-date-format') : 'MM/DD/YYYY';
        let momentDateFormat = phpToMomentFormat(phpDateFormat);
        // Default dates for the date picker
        let defaultStartDate = moment().subtract(29, 'days');
        let defaultEndDate = moment();

        datePickerElement.daterangepicker({
            "autoApply": true,
            "ranges": ranges,
            startDate: defaultStartDate,
            endDate: defaultEndDate
        });

        if (wps_js.isset(wps_js.global, 'request_params', 'from') && wps_js.isset(wps_js.global, 'request_params', 'to')) {
            const requestFromDate = wps_js.global.request_params.from;
            const requestToDate = wps_js.global.request_params.to;
            datePickerElement.data('daterangepicker').setStartDate(moment(requestFromDate).format('MM/DD/YYYY'));
            datePickerElement.data('daterangepicker').setEndDate(moment(requestToDate).format('MM/DD/YYYY'));
            datePickerElement.data('daterangepicker').updateCalendars();
            const activeText = datePickerElement.data('daterangepicker').container.find('.ranges li.active').text();
            const startMoment = moment(requestFromDate);
            const endMoment = moment(requestToDate);
            let activeRangeText;
            if (startMoment.year() === endMoment.year() ) {
                const startDateFormat = momentDateFormat.replace(/,?\s?(YYYY|YY)[-/\s]?,?|[-/\s]?(YYYY|YY)[-/\s]?,?/g, "");
                activeRangeText = `${startMoment.format(startDateFormat)} - ${endMoment.format(momentDateFormat)}`;
            } else {
                activeRangeText = `${startMoment.format(momentDateFormat)} - ${endMoment.format(momentDateFormat)}`;
            }
            if (activeText !== 'Custom Range') {
                if ( activeText !== 'All Time') {
                    activeRangeText = `<span class="wps-date-range">${activeText}</span>${activeRangeText}`;
                    document.querySelector('.js-date-range-picker-btn').classList.add('custom-range')
                } else {
                    activeRangeText = activeText
                }
            }
            datePickerBtn.find('span').html(activeRangeText);
        } else {
            let defaultRange = datePickerBtn.find('span').text();
            datePickerElement.data('daterangepicker').container.find('.ranges li.active').removeClass('active');
            datePickerElement.data('daterangepicker').container.find('.ranges li[data-range-key="' + defaultRange + '"]').addClass('active');
            const defaultStartMoment = moment(defaultStartDate);
            const defaultEndMoment = moment(defaultEndDate);
            let defaultActiveRangeText;
            if (defaultStartMoment.year() === defaultEndMoment.year() ) {
                const startDateFormat = momentDateFormat.replace(/,?\s?(YYYY|YY)[-/\s]?,?|[-/\s]?(YYYY|YY)[-/\s]?,?/g, "");
                defaultActiveRangeText = `${defaultStartMoment.format(startDateFormat)} - ${defaultEndMoment.format(momentDateFormat)}`;
            } else {
                defaultActiveRangeText = `${defaultStartMoment.format(momentDateFormat)} - ${defaultEndMoment.format(momentDateFormat)}`;
            }
            if (defaultRange !== 'Custom Range') {
                if ( defaultRange !== 'All Time') {
                    defaultActiveRangeText = `<span class="wps-date-range">${defaultRange}</span>${defaultActiveRangeText}`;
                    document.querySelector('.js-date-range-picker-btn').classList.add('custom-range')
                } else {
                    defaultActiveRangeText = defaultActiveRangeText
                }
            }
            datePickerBtn.find('span').html(defaultActiveRangeText);
            datePickerElement.on('show.daterangepicker', function (ev, picker) {
                datePickerElement.data('daterangepicker').container.find('.ranges li.active').removeClass('active');
                datePickerElement.data('daterangepicker').container.find('.ranges li[data-range-key="' + defaultRange + '"]').addClass('active');
            });
        }

        datePickerElement.on('show.daterangepicker', function (ev, picker) {
            const correspondingPicker = picker.container;
            jQuery(correspondingPicker).addClass(ev.target.className);
        });
        datePickerElement.on('apply.daterangepicker', function (ev, picker) {
            const inputFrom = datePickerForm.find('.js-date-range-picker-input-from').first();
            const inputTo = datePickerForm.find('.js-date-range-picker-input-to').first();
            inputFrom.val(picker.startDate.format('YYYY-MM-DD'));
            inputTo.val(picker.endDate.format('YYYY-MM-DD'));
            datePickerBtn.find('span').html(datePickerElement.data('daterangepicker').chosenLabel);
            datePickerForm.submit();
        });
    }

    // Single Calendar
    const datePickerField = jQuery('.wps-js-calendar-field');
    if (datePickerField.length) {
        datePickerField.daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            minYear: 1998,
            maxYear: parseInt(new Date().getFullYear() + 1),
            locale: {
                format: 'YYYY-MM-DD'
            }
        });
        datePickerField.on('show.daterangepicker', function (ev, picker) {
            const correspondingPicker = picker.container;
            jQuery(correspondingPicker).addClass(ev.target.className);
        });
        datePickerField.on('apply.daterangepicker', function (ev, picker) {
            jQuery('.wps-today-datepicker').submit();
        });
    }
});