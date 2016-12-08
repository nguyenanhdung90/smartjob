(function($){

/**
 * Initialize page 
 */
$(document).on('pageinit', function(){
	// send message
	$('#jobseeker_message').on('submit', function(event){
		event.preventDefault();

		var params = {
			data	:  {
				action: 'et_contact_jobseeker',
				content : $(this).serialize()
			},
			success : function(resp){
				if (resp.success){
					$('#msg_pop').html('<p>' + resp.msg + '</p>').popup('open');
					setTimeout(function(){
						window.location.href = $('#cancel_url').val();
					}, 3000);
				}
				else{
					$('#msg_pop').html('<p>' + resp.msg + '</p>').popup('open');
					setTimeout(function(){
						$('#msg_pop').popup('close');
					}, 3000);

					// setTimeout(function(){
					// 	window.location.href = $('#cancel_url').val();
					// }, 3000);

					if(et_globals.use_captcha)
						Recaptcha.reload();

				}
			}
		};

		params = $.ajaxParams( params );
		$.ajax( params );
	});

	$.ajaxParams = function( params ){
		var beforeSend 	= params.beforeSend || function(){};
		var complete 	= params.complete || function(){};
		var def = {
			type 		: 'post',
			url 		: et_globals.ajaxURL,
			beforeSend	: function(){
				$.mobile.showPageLoadingMsg();
				beforeSend();
			}, 
			complete : function(){
				$.mobile.hidePageLoadingMsg();
				complete();
			}
		}

		return $.extend( params, def );
	};
});

})(jQuery);