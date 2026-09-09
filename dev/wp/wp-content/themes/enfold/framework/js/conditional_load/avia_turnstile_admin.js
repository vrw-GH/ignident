/*
 * AVIA CLOUDFLARE TURNSTILE ADMIN
 * ================================
 *
 * Registers the js callback used by the "Check API Keys" verification button
 * on the Security theme options tab. Loads the Turnstile widget once, lets the
 * admin solve it, then hands the resulting token back to the generic
 * avia_verify_input() ajax flow (see avia_advanced_form_elements.js) which
 * posts it to av_turnstile_api_check() on the server.
 *
 * @since 7.1.7
 */
var avia_callback = avia_callback || {};

(function($)
{
	avia_callback.av_turnstile_values = {
									callback: false,
									sitekey: '',
									secretkey: '',
									container: false,
									current_id: false,
									widget_id: false
								};

	avia_callback.av_turnstile_js_api_check = function( value, callback )
	{
		var clicked      = $(this),
			container    = clicked.closest('.av-verify-button-container'),
			input_fields = clicked.data('av-verify-fields'),
			src          = '',
			sitekey      = '',
			secretkey    = '';

		input_fields = input_fields.split(',');

		//	remove any existing error widgets
		$('.av-turnstile-callback-error').hide();

		src = 'https://challenges.cloudflare.com/turnstile/v0/api.js';

		if( 'undefined' != typeof AviaTurnstileData.api && '' != AviaTurnstileData.api )
		{
			src = AviaTurnstileData.api;
		}

		sitekey = 'undefined' != typeof input_fields[0] ? container.find('input[name="' + input_fields[0] + '"]').val().trim() : '';
		secretkey = 'undefined' != typeof input_fields[1] ? container.find('input[name="' + input_fields[1] + '"]').val().trim() : '';

		if( '' == sitekey || '' == secretkey )
		{
			var msg = AviaTurnstileData.invalid_keys;

			msg = '<div class="av-notice-error">' + msg + '</div>';
			container.find('.av-verification-result').html( msg );
			return false;
		}

		avia_callback.av_turnstile_values.callback = callback;
		avia_callback.av_turnstile_values.sitekey = sitekey;
		avia_callback.av_turnstile_values.secretkey = secretkey;
		avia_callback.av_turnstile_values.container = container;
		avia_callback.av_turnstile_values.current_id = false;
		avia_callback.av_turnstile_values.widget_id = false;

		//	find a current turnstile api link and remove it, then append the new one
		$('script[src*="turnstile/v0/api.js"]').remove();
		$('#av-turnstile-api-script').remove();

		src += '?onload=av_turnstile_api_loaded&render=explicit';

		var	script 		= document.createElement('script');
			script.id	= 'av-turnstile-api-script';
			script.type = 'text/javascript';
			script.src 	= src;
			script.defer = true;
			script.onerror = av_turnstile_api_load_error;

		document.body.appendChild(script);
	};

	av_turnstile_api_load_error = function()
	{
		var msg = 'Cloudflare Turnstile API could not be loaded. We are not able to verify keys. Check your internet connection and try again.';

		if( 'undefined' != typeof AviaTurnstileData.api_load_error && '' != AviaTurnstileData.api_load_error )
		{
			msg = AviaTurnstileData.api_load_error;
		}

		avia_callback.av_turnstile_values.callback.call(this, 'error');

		alert( msg );
	};

	av_turnstile_api_loaded = function()
	{
		var unique_id = av_turnstile_unique_id();
		var div = '<div id="' + unique_id + '" class="av-turnstile-verify"></div>';
		avia_callback.av_turnstile_values.container.find('.av-verification-result').first().before( div );

		avia_callback.av_turnstile_values.current_id = unique_id;

		avia_callback.av_turnstile_values.widget_id = turnstile.render( '#' + unique_id, {
																sitekey: avia_callback.av_turnstile_values.sitekey,
																callback: av_turnstile_verify_VerifyCallback,
																'error-callback': av_turnstile_verify_ErrorCallback
															});
	};

	av_turnstile_verify_VerifyCallback = function( token )
	{
		var params = {
							token: token,
							sitekey: avia_callback.av_turnstile_values.sitekey,
							secretkey: avia_callback.av_turnstile_values.secretkey,
							current_id: avia_callback.av_turnstile_values.current_id
						};

		$('#av-turnstile-api-script').remove();

		if( false !== avia_callback.av_turnstile_values.widget_id && 'undefined' != typeof turnstile )
		{
			turnstile.remove( avia_callback.av_turnstile_values.widget_id );
		}

		$( '#' + avia_callback.av_turnstile_values.current_id ).remove();

		avia_callback.av_turnstile_values.callback.call(this, params);
	};

	av_turnstile_verify_ErrorCallback = function()
	{
		$( '#' + avia_callback.av_turnstile_values.current_id ).addClass('av-turnstile-callback-error');
		avia_callback.av_turnstile_values.callback.call(this, 'error');
	};

	av_turnstile_unique_id = function()
	{
		var body = $('body');
		var id = 'av-verify-turnstile-';
		var cnt = 0;

		do
		{
			var unique = id + cnt;
			if( 0 == body.find( '#' + unique ).length )
			{
				return unique;
			}
			cnt ++;
		}while( true )
	};

})(jQuery);
