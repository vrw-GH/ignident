/**
 * Global namespace
 *
 * @since 5.6
 */
var aviaJS = aviaJS || {};

(function()
{
	"use strict";

	if( ! aviaJS.aviaCookieBot )
	{
		class aviaCookieBot
		{
			constructor()
			{
				if( window['wp'] && wp.hooks && window['Cookiebot'] )
				{
					wp.hooks.addFilter( 'aviaCookieConsent_allow_continue', 'avia-cookiebot', this.cookiebot );
				}
			}

			cookiebot( allow_continue )
			{
				/**
				 * Solution provided by Jan Thiel
				 * see https://kriesi.at/support/topic/cookiebot-support-feature-request-with-patch/
				 * see https://github.com/KriesiMedia/Enfold-Feature-Requests/issues/91
				 *
				 * Check if user has accepted marketing cookies in Cookiebot
				 */
				var cookiebot_consent = Cookiebot.consent.marketing;
				if( cookiebot_consent !== true )
				{
					allow_continue = false;

					// Reload page if user accepts marketing cookies to allow script to load
					window.addEventListener('CookiebotOnAccept', function (e)
					{
						if( Cookiebot.consent.marketing )
						{
							location.reload();
						}
					}, false );
				}

				return allow_continue;
			}

		}

		aviaJS.aviaCookieBot = new aviaCookieBot();

	}

})();

