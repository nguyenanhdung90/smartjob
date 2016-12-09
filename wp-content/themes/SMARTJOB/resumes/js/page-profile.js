(function($){
JobEngine.Views.PageProfile = Backbone.View.extend({
	el : '#profile',
	events: {
		'submit #jobseeker_send_message' : 'onSendingMessage'
	},

	initialize: function(){
		this.loading 	= new JobEngine.Views.BlockUi();

		// validator
		this.formValidator = $('#jobseeker_send_message').validate({
			rules: {
				'sender_name' : 'required',
				'sender_email' : 'required',
				'message' 	: 'required'
			}
		});
	},

	onSendingMessage: function(event){
		event.preventDefault();

		if ( !this.formValidator.form() ) {
			if(et_globals.use_captcha) {
				Recaptcha.reload();
			}
			return false;
		};

		var form = $(event.currentTarget);
		var view = this;

		$.ajax({
			url 		: et_globals.ajaxURL,
			type 		: 'post',
			data 		: {
				action	: 'et_contact_jobseeker',
				content	: form.serialize()
			},
			beforeSend: function(){
				view.loading.block( form.find('.jse-submit input') );
			},
			success: function(data){
				view.loading.unblock();
				if (data.success){
					var msg = $('<div>').addClass('alert alert-warning').html(data.msg);
					form.find('.jse-submit').hide().after(msg);
					// redirect to profile page
					var location = form.find('#cancel_form').attr('href');
					setTimeout(function(){
						window.location = location;
					}, 3000);

				} else {
					var msg = $('<div>').addClass('alert alert-error').html(data.msg);
					form.find('.jse-submit').after(msg);
				}

			}
		});

	}
});

$(document).ready(function(){
	new JobEngine.Views.PageProfile();
});

})(jQuery);