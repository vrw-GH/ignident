(function($) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */
	$(function() {
		var jqXHR;
		const controller = new AbortController();
		
		//** Storage Settings **//
		$('.pb-seting-link').click(function() {
			$('.pb-single-tab').hide();
			$('.pb-seting-link').removeClass('active');
			$(this).addClass('active');
			$('.setting' + $(this).attr('target')).fadeIn("fast");
		});
		
		//** Create Backup **//
		$('#pb-create-backup').click(function(){
			var bkpopt = [];
			var i =0;
			$('input[name="backupoptiopn"]:checked').each(function(){
				bkpopt[i] = $(this).val();
				i++;
			});
			if(bkpopt.length == 0){
				pb_alert_message("Please select backup option", "error");
				return false;
			}
			var obj = $(this);
			$(this).find('.pb-btn-loader').show();
			$('.pb-preogress-bar-wrap').show();
			
			var count = 1;
			var interval = setInterval(function () {
				if(count < 70){
					$('.pb-bar-filler').css('width', count+'%');
					$('.pb-bar-counter').text(count+'%');
				}
				count++;
			}, 1000);
			
			$('#pb-kill-request').removeClass('pb-btn-disable');
			
			//check for existing ajax request
			$.ajax({
				url: ajax_object.url,
				type: 'post',	
				data:  {
					'action': 'auto_backup_create_ajax',
					'nonce' : ajax_object.nonce, 
					'bkpopt': bkpopt,
					'cloud': $('#pb-cloud').val()
				},
				timeout: $('#pb-max-execution-time').val() * 1000,
				success: function(response) {
					console.log(response);
					if(response){
						var res = jQuery.parseJSON(response);
						if(res.status == 'error'){
							pb_alert_message(res.msg, 'error');
							obj.find('.pb-btn-loader').hide();
							clearInterval(interval);
							$('.pb-bar-filler').css('width', '0%');
							$('.pb-bar-counter').text('0%');
							$('.pb-preogress-bar-wrap').hide();
						}else{
							obj.find('.pb-btn-loader').hide();
							$('.pb-bar-filler').css('width', '100%');
							$('.pb-bar-counter').text('100%');
							clearInterval(interval);
							window.location.href = '';
						}
				    }
				},
				error: function(xhr, textStatus, errorThrown) {
					console.log(textStatus);
                    if (textStatus == 'timeout') {
						pb_alert_message("Timeout for this call!", "error");
						obj.find('.pb-btn-loader').hide();
						$('.pb-preogress-bar-wrap').hide();
						clearInterval(interval);
						setTimeout(function(){
							window.location.href = '';
						},5000);
                    }
                }
			});
		});
		
		/** Stop(Kill) Backup Creation **/
		$('#pb-kill-request').click(function () {
			$.ajax().abort();
		});
		
		/** Delete backup manually **/
		$('.pb-delete-backup').click(function(){
			if(confirm("Are you sure?")){
				
				var obj = $(this);
				var imgDir = obj.parents('table').attr('imgdir');
				obj.find('img').attr('src', imgDir+'/loader.svg');
				obj.find('img').addClass('pb-loader');
				
				var bkp = [];
				var bkpname = $(this).parents('tr').attr('bkpname');
				var location = $(this).parents('tr').attr('bklocation');
				bkp[0] = {
					'bkp_name': bkpname,
					'location': location
				};
				if(location == 'Google_Drive'){
					bkp[0]['fileid'] = $(this).parents('tr').attr('fileid');
				}
				$.ajax({
					url: ajax_object.url,
					type: 'post',	
					data:  {
						'action': 'auto_backup_delete_ajax',
						'nonce' : ajax_object.nonce, 
						'bkp_info': bkp
					},
					success: function(response) {
						console.log(response);
						if(response == 'success'){
							window.location.href = '';
						}else{
							pb_alert_message( response, 'error' );
							obj.find('img').attr('src', imgDir+'/delete.svg');
							obj.find('img').removeClass('pb-loader');
						}
					}
				});
			};
		});
		
		/** Add/Remove class from the delete button by selecting checkboxes **/
		//$('input[name="bkprow"]').change(function(){
		$(document).on('change','input[name="bkprow"]', function(){
			var bkpname = [];
			var i = 0;
			$('input[name="bkprow"]:checked').each(function() {
				bkpname[i] = $(this).parents('tr').attr('bkpname');
				i++;
			});
			if(bkpname.length > 0){
				$('#pb-delete-multi-bkp').removeClass('pb-btn-disable');
			}else{
				$('#pb-delete-multi-bkp').addClass('pb-btn-disable');
			}
		});
		
		$('#pb-select-all-bkp').click(function () {
			$('input[name="bkprow"]').each(function() {
				if($(this).prop('checked')){
					$(this).prop('checked', '');
				}else{
					$(this).prop('checked', 'checked');
				}
				
			});
			
			if($('#pb-delete-multi-bkp').hasClass('pb-btn-disable')){
				$('#pb-delete-multi-bkp').removeClass('pb-btn-disable');
			}else{
				$('#pb-delete-multi-bkp').addClass('pb-btn-disable');
			}
		});
		
		/** Delete backup manually by selecting checkboxes **/
		$('#pb-delete-multi-bkp').click(function(){
			if(confirm("Are you sure?")){
				
				var obj = $(this);
				$(this).find('.pb-btn-loader').show();
				
				var bkp = [];
				var i = 0;
				$('input[name="bkprow"]:checked').each(function() {
					var bkpname = $(this).parents('tr').attr('bkpname');
					var location = $(this).parents('tr').attr('bklocation');
					bkp[i] = {
						'bkp_name': bkpname,
						'location': location
					}
					if(location == 'Google_Drive'){
						bkp[i]['fileid'] = $(this).parents('tr').attr('fileid');
					}
					i++;
				});
				
				$.ajax({
					url: ajax_object.url,
					type: 'post',	
					data:  {
						'action': 'auto_backup_delete_ajax',
						'nonce' : ajax_object.nonce, 
						'bkp_info': bkp
					},
					success: function(response) {
						console.log(response);
						if(response == 'success'){
							window.location.href = '';
						}else{
							pb_alert_message( response, 'error' );
							obj.find('.pb-btn-loader').hide();
						}
					}
				});
			};
		});
		
		/** Restore backup manually **/
		$('.pb-restore-backup').click(function(){
			var bkp = $(this).parents('tr').attr('bkpname');
			var location = $(this).parents('tr').attr('bklocation');
			if(confirm("Are you sure?")){
				var obj = $(this);
				var imgDir = $(this).parents('table').attr('imgdir');
				obj.find('img').attr('src', imgDir+'/loader.svg');
				$.ajax({
					url: ajax_object.url,
					type: 'post',	
					data:  {
						'action': 'auto_backup_restore_ajax',
						'nonce' : ajax_object.nonce, 
						'bkp_name': bkp,
						'location': location,
						'fileid': $(this).parents('tr').attr('fileid')
					},
					success: function(response) {
						console.log(response);
						if(response == 'success'){
							pb_alert_message( "Backup has been restored.", 'success' );
							setTimeout(function(){
								window.location.href = '';
							}, 5000);
						}else{
							pb_alert_message( response, 'error' );
						}
						obj.find('img').attr('src', imgDir+'/restore.svg');
					}
				});
			};
		});
		
		/** Dropbox Connection **/
		$('#pb-dropbox-settings').on('submit', function(e) {
			e.preventDefault();
			
			var appKey = $('#dropbox').find('input[name="app_key"]').val();
			if(appKey.length == 0){
				$('#dropbox').find('input[name="app_key"]').addClass('pb-input-error');
				pb_alert_message('App key required.', 'error');
				return false;
			}
			
			var appSecret = $('#dropbox').find('input[name="app_secret"]').val();
			if(appSecret.length == 0){
				$('#dropbox').find('input[name="app_secret"]').addClass('pb-input-error');
				pb_alert_message('App Secret required.', 'error');
				return false;
			}
			
			var formdata = new FormData(this);
			formdata.append("action", 'auto_backup_save_storage_data');
			formdata.append("nonce", ajax_object.nonce);
			$.ajax({
				url: ajax_object.url,
				type: "post",
				data: formdata,
				processData: false,
				contentType: false,
				cache: false,
				async: false,
				success: function(response) {
					console.log(response);
					if (response == 'success') {
						window.location.href = 'https://www.dropbox.com/oauth2/authorize?client_id='+appKey+'&redirect_uri='+window.location.href+'&token_access_type=offline&response_type=code';
					}
				}
			});
		});
		
		/** Google Drive Connection **/
		$('#pb-gDrive-settings').on('submit', function(e) {
			e.preventDefault();
			
			var client_id = $('#gDrive').find('input[name="client_id"]').val();
			if(client_id.length == 0){
				$('#gDrive').find('input[name="client_id"]').addClass('pb-input-error');
				pb_alert_message('Client ID required.', 'error');
				return false;
			}
			
			var secretKey = $('#gDrive').find('input[name="client_secret"]').val();
			if(secretKey.length == 0){
				$('#gDrive').find('input[name="client_secret"]').addClass('pb-input-error');
				pb_alert_message('Client Secret required.', 'error');
				return false;
			}
			
			var formdata = new FormData(this);
			formdata.append("action", 'auto_backup_save_storage_data');
			formdata.append("nonce", ajax_object.nonce);
			$.ajax({
				url: ajax_object.url,
				type: "post",
				data: formdata,
				processData: false,
				contentType: false,
				cache: false,
				async: false,
				success: function(response) {
					console.log(response);
					if (response == 'success') {
						window.location.href = 'https://accounts.google.com/o/oauth2/auth?scope=' + encodeURI('https://www.googleapis.com/auth/drive') + '&redirect_uri='+window.location.href+'&response_type=code&client_id=' + client_id + '&access_type=offline';
					}
				}
			});
		});
		
		/** Google Drive Download File **/
		$('.pb-location-Google_Drive').on('click', function() {
			var obj = $(this);
			var imgDir = obj.parents('table').attr('imgdir');
			obj.find('img').attr('src', imgDir+'/loader.svg');
			obj.find('img').addClass('pb-loader');
			$.ajax({
				url: ajax_object.url,
				type: "post",
				data: {
					action: 'auto_backup_download_file',
					nonce: ajax_object.nonce,
					gd_file: $(this).find('a').attr('gdrivefile'),
					bkpname: $(this).parents('tr').attr('bkpname'),
					fileid: $(this).parents('tr').attr('fileid') // Tis is only for google drive
				},
				success: function(response) {
					console.log(response);
					var res = jQuery.parseJSON(response);
					if(res.status == 1){
						window.location.href = res.url;
					}else{
						pb_alert_message(res.status, "error");
					}
					obj.find('img').removeClass('pb-loader');
					obj.find('img').attr('src', imgDir+'/download.svg');
				}
			});
		});
		
		/** AWS S3 Connection **/
		$('#pb-aws-settings').on('submit', function(e) {
			e.preventDefault();
			
			var bucket_name = $('#awsS3Tab').find('input[name="bucket_name"]').val();
			if(bucket_name.length == 0){
				$('#awsS3Tab').find('input[name="bucket_name"]').addClass('pb-input-error');
				pb_alert_message('Bucket name required.', 'error');
				return false;
			}
			
			var accessKey = $('#awsS3Tab').find('input[name="access_key"]').val();
			if(accessKey.length == 0){
				$('#awsS3Tab').find('input[name="access_key"]').addClass('pb-input-error');
				pb_alert_message('Access key required.', 'error');
				return false;
			}
			
			var secretKey = $('#awsS3Tab').find('input[name="access_secret"]').val();
			if(secretKey.length == 0){
				$('#awsS3Tab').find('input[name="access_secret"]').addClass('pb-input-error');
				pb_alert_message('Secret key required.', 'error');
				return false;
			}
			
			var formdata = new FormData(this);
			formdata.append("action", 'auto_backup_save_storage_data');
			formdata.append("nonce", ajax_object.nonce);
		
			$.ajax({
				url: ajax_object.url,
				type: "post",
				data: formdata,
				processData: false,
				contentType: false,
				cache: false,
				//async: false,
				beforeSend: function() {
					$('.pb-btn-loader').show();
				},
				success: function(response) {
					console.log(response);
					if (response == 'success') {
						pb_alert_message('Settings have been saved successfully.', 'success');
					}else{
						pb_alert_message(response, 'error');
					}
					$('.pb-btn-loader').hide();
				}
			});
		});
		
		/** FTP Connection **/
		$('#pb-ftp-settings').on('submit', function(e) {
			e.preventDefault();
			
			var host = $('#ftp').find('input[name="host"]').val();
			if(host.length == 0){
				$('#ftp').find('input[name="host"]').addClass('pb-input-error');
				pb_alert_message('Server host required.', 'error');
				return false;
			}
			
			var ftp_username = $('#ftp').find('input[name="ftp_username"]').val();
			if(ftp_username.length == 0){
				$('#ftp').find('input[name="ftp_username"]').addClass('pb-input-error');
				pb_alert_message('Username required.', 'error');
				return false;
			}
			
			var ftp_password = $('#ftp').find('input[name="ftp_password"]').val();
			if(ftp_password.length == 0){
				$('#ftp').find('input[name="ftp_password"]').addClass('pb-input-error');
				pb_alert_message('Password required.', 'error');
				return false;
			}
			
			var formdata = new FormData(this);
			formdata.append("action", 'auto_backup_save_storage_data');
			formdata.append("nonce", ajax_object.nonce);
			$.ajax({
				url: ajax_object.url,
				type: "post",
				data: formdata,
				processData: false,
				contentType: false,
				cache: false,
				beforeSend: function() {
					$('.pb-btn-loader').show();
				},
				success: function(response) {
					console.log(response);
					if (response == 'success') {
						pb_alert_message('Settings have been saved successfully.', 'success');
					}else{
						pb_alert_message(response, 'error');
					}
					$('.pb-btn-loader').hide();
				}
			});
		});
		
		/** NeevCloud Connection **/
		$('#pb-neevcloud-settings').on('submit', function(e) {
			e.preventDefault();
			
			var bucket_name = $('#neevcloud').find('input[name="bucket_name"]').val();
			if(bucket_name.length == 0){
				$('#neevcloud').find('input[name="bucket_name"]').addClass('pb-input-error');
				pb_alert_message('Bucket name required.', 'error');
				return false;
			}
			
			var accessKey = $('#neevcloud').find('input[name="access_key"]').val();
			if(accessKey.length == 0){
				$('#neevcloud').find('input[name="access_key"]').addClass('pb-input-error');
				pb_alert_message('Access key required.', 'error');
				return false;
			}
			
			var secretKey = $('#neevcloud').find('input[name="access_secret"]').val();
			if(secretKey.length == 0){
				$('#neevcloud').find('input[name="access_secret"]').addClass('pb-input-error');
				pb_alert_message('Secret key required.', 'error');
				return false;
			}
			
			var formdata = new FormData(this);
			formdata.append("action", 'auto_backup_save_storage_data');
			formdata.append("nonce", ajax_object.nonce);
			$.ajax({
				url: ajax_object.url,
				type: "post",
				data: formdata,
				processData: false,
				contentType: false,
				cache: false,
				beforeSend: function() {
					$('.pb-btn-loader').show();
				},
				success: function(response) {
					console.log(response);
					if (response == 'success') {
						pb_alert_message('Settings have been saved successfully.', 'success');
					}else{
						pb_alert_message(response, 'error');
					}
					$('.pb-btn-loader').hide();
				}
			});
		});
		
		/** Save scheduled settings **/
		$('#pb-schedule-form').on('submit', function(e) {
			e.preventDefault();
			
			var formdata = new FormData(this);
			formdata.append("action", 'auto_backup_save_scheduled_data');
			formdata.append("nonce", ajax_object.nonce);
			$.ajax({
				url: ajax_object.url,
				type: "post",
				data: formdata,
				processData: false,
				contentType: false,
				cache: false,
				success: function(response) {
					if(response == 'invalid-nonce'){
						pb_alert_message("Invalid nonce.", 'error');
					}else{
						if (response) {
							pb_alert_message('Settings have been saved successfully.', 'success');
						}else{
							pb_alert_message("Something is going wrong.", 'error');
						}
					}
					$('.pb-btn-loader').hide();
				}
			});
		});
		
		/** Active Import button **/
		$('#pb-import-file').on('change', function () {
			$('#pb-import-form button').removeClass('btn-disabled');
		});
		
		/** Import Data **/
		$('#pb-import-form').on('submit', function(e) {
			e.preventDefault();
			
			var formdata = new FormData(this);
			formdata.append("action", 'auto_backup_import_data');
			formdata.append("nonce", ajax_object.nonce);
			$.ajax({
				url: ajax_object.url,
				type: "post",
				data: formdata,
				processData: false,
				contentType: false,
				cache: false,
				beforeSend: function() {
					$('.pb-btn-loader').show();
				},
				success: function(response) {
					console.log(response);
					if (response) {
						pb_alert_message('Settings have been saved successfully.', 'success');
						setTimeout( function () {
							window.location.href = '';
						},3000 );
					}else{
						pb_alert_message("Something is going wrong.", 'error');
					}
					$('.pb-btn-loader').hide();
				}
			});
		});
		
		/** Delete cron schedule **/
		
		$('.autobk-delete-schedule').on('click', function (e) {
			e.preventDefault();
			var hook = $(this).attr('hook');
			$.ajax({
				url: ajax_object.url,
				type: 'post',	
				data:  {
					'action': 'auto_backup_delete_schedule',
					'nonce' : ajax_object.nonce, 
					'hook': hook,
				},
				success: function(response) {
					console.log(response.data.message);
					if (response.success) {
						pb_alert_message(response.data.message, 'success');
						setTimeout( function () {
							window.location.href = '';
						},3000 );
					}else{
						pb_alert_message(response.data.message, 'error');
					}
				}
			});
		});
		
		/** Sorting JS **/
		$('.pb-backup-date').on('click', function (e) {
			e.preventDefault();
			var order = $(this).attr('data-attr');
			$.ajax({
				url: ajax_object.url,
				type: 'post',	
				data:  {
					'action': 'auto_backup_sortingbydate',
					'order' : order,
					'nonce' : ajax_object.nonce, 
				},
				success: function(response) {
					if (response.success) {
						$('.pb-sorting-table').html(response.data.message);
					}else{
						pb_alert_message(response.data.message, 'error');
					}
				}
			});
			
		});
		
		$('.pb-backup-size').on('click', function (e) {
			e.preventDefault();
			var order = $(this).attr('data-attr');
			$.ajax({
				url: ajax_object.url,
				type: 'post',	
				data:  {
					'action': 'auto_backup_sortingbysize',
					'order' : order,
					'nonce' : ajax_object.nonce, 
				},
				success: function(response) {
					console.log(response);
					if (response.success) {
						$('.pb-sorting-table').html(response.data.message);
					}else{
						pb_alert_message(response.data.message, 'error');
					}
				}
			});
			
		});
		
		/** Alert Function **/
		function pb_alert_message( msg, msg_status ){
			$('.pb-alert-wrap p').text(msg);
			$('.pb-alert-wrap').addClass(msg_status);
			
			$('.pb-alert-wrap').show();
			
			setTimeout(function(){
			 $('input').removeClass('pb-input-error');
			 $('.pb-alert-wrap').removeClass(msg_status);
			 $('.wpa_notification').hide();
			}, 5000);
		}
	});
	
	
	

})(jQuery);