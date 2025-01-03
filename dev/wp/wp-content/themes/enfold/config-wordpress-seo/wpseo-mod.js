/* Push builder content to Yoast SEO Analysis Tool */

(function ($)
{
    "use strict";

    $.AviaYoastAnalysisTool = function ()
    {
        this.analytics = new $.AviaAnalytics();

        this.register_plugin();
    };

    $.AviaYoastAnalysisTool.prototype = {
        register_plugin()
        {
            // Ensure YoastSEO.js is present and can access the necessary features.
            if (
                typeof YoastSEO === "undefined" ||
                typeof YoastSEO.analysis === "undefined" ||
                typeof YoastSEO.analysis.worker === "undefined")
            {
                return;
            }

            YoastSEO.app.registerPlugin("AviaYoastAnalysisTool", { status: "ready" });

            this.register_modifications();
            this.register_listeners();
        },

        /**
         * Registers the retrieve_analytics_data modification.
         *
         * @returns {void}
         */
        register_modifications()
        {
            var self = this;

            // Ensure that the additional data is being seen as a modification to the content.
            YoastSEO.app.registerModification(
                "content",
                self.retrieve_analytics_data.bind(self),
                "AviaYoastAnalysisTool",
                10
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

            this.analytics.body.on(
                analytics_events,
                function ()
                {
                    setTimeout(function ()
                    {
                        YoastSEO.app.refresh();
                    }, 100);
                }
            );
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
        if (typeof YoastSEO !== "undefined" && typeof YoastSEO.app !== "undefined")
        {
            new $.AviaYoastAnalysisTool();
        }
        else
        {
            $(window).on("YoastSEO:ready", function ()
            {
                new $.AviaYoastAnalysisTool();
            });
        }
    });
})(jQuery);
