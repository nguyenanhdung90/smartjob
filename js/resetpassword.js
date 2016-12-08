(function ($) {
jQuery(document).ready(function($){

JobEngine.Views.ResetPassword = Backbone.View.extend({
	el : $('#page_reset_password'),

	events : {
		'submit form#reset_password' : 'resetPassword'
	},

	initialize: function(){
		this.validator = this.$('form#reset_password').validate({
			rules : {
				user_new_pass : "required",
				user_pass_again : {
					required : true,
					equalTo : '#user_new_pass'
				}
			}
		});

		pubsub.on('je:response:reset_password', this.afterResetPassword, this);
	},

	resetPassword : function(e){
		e.preventDefault();
		var form = this.$('form#reset_password');
		var loadingBtn = new JobEngine.Views.LoadingButton({el : form.find('#submit_profile')});
		if ( this.validator.form() ){
			JobEngine.app.auth.setUserName(form.find('input[name=user_login]').val());
			JobEngine.app.auth.setUserKey(form.find('input[name=user_key]').val());
			JobEngine.app.auth.setPass(form.find('input[name=user_new_pass]').val());
			JobEngine.app.auth.doResetPassword({
				beforeSend : function(){
					loadingBtn.loading();
				},
				success : function(){
					loadingBtn.finish();
				}
			});
		}
	},

	afterResetPassword : function(resp){
		if ( resp.success && resp.data.redirect_url )
			window.location = resp.data.redirect_url;
	}

});


new JobEngine.Views.ResetPassword();

});
})(jQuery);