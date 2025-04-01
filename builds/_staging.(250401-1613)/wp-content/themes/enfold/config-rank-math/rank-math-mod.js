/* Push builder content to RankMath Analysis Tool */

(function ($)
{
    "use strict";

    $.AviaRankMathAnalysisTool = function ()
    {
        this.analytics = new $.AviaAnalytics();

        this.register_plugin();
    };

    $.AviaRankMathAnalysisTool.prototype = {
        register_plugin()
        {
            this.register_filters();
            this.register_listeners();
        },

        /**
         * Regiter new function to rank_math_content filter.
         *
         * @returns {void}
         */
        register_filters()
        {
            var self = this;

            wp.hooks.addFilter(
                'rank_math_content',
                'rank-math',
                self.retrieve_analytics_data.bind(self)
            );
        },

        /**
         * Register listeners for advance layout builder events.
         *
         * @returns {void}
         */
        register_listeners()
        {
            var self = this,
                builder_events = 'avia-storage-update avia-history-update',
                analytics_events = 'avia-analytics-for-blocks-ready avia-analytics-ready';

            this.analytics.body.add(this.analytics.shortcodes).on(
                builder_events,
                _.debounce(function ()
                {
                    self.analytics.convert_shortcodes();
                }, 1000)
            );

            this.analytics.body.on(analytics_events, function ()
            {
                setTimeout(function ()
                {
                    window.rankMathEditor.refresh('content');
                }, 100 );
            });
        },

        /**
         * Adds to the content to be analyzed by the analyzer.
         *
         * @param {string} data The current data string.
         *
         * @returns {string} The data string parameter with the added content.
         */
        retrieve_analytics_data(data)
        {
            data = this.analytics.retrieve_data();

            return data;
        }
    };

    $(function()
    {
        if( typeof rankMath !== "undefined" && typeof rankMathAnalyzer !== "undefined" )
        {
            new $.AviaRankMathAnalysisTool();
        }
    });

})(jQuery);
