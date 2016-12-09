(function ($) {
jQuery(document).ready(function($){

JobEngine.Views.ChangePassword = Backbone.View.extend({
	el : $('#page_change_password'),

	events : {
		'submit form#change_password' : 'updatePassword'
	},

	initialize : function(){
		this.auth	= new JobEngine.Models.Auth();
		this.$form	= this.$('form#change_password');

		this.validator	= this.$form.validate({
			rules	: {
				user_old_pass	: 'required',
				user_pass		: 'required',
				user_pass_again	: {
					required	: true,
					equalTo		: "#user_pass"
				}
			}
		});
	},

	updatePassword : function(event){
		event.preventDefault();
		var formData	= {},
			auth		= this.auth;

		if( this.validator.form() ){
			this.$form.find('input[type="password"]').each(function(){
				var $this	= $(this);
				auth.set( $this.attr('id'), $this.val(), {silent:true});
			});

			this.auth.changePassword({
				success	: function(resp){
					pubsub.trigger('je:notification', {
						msg: resp.msg,
						notice_type : resp.success ? "success" : "error"
					});
				}
			});
		}
	}
});

new JobEngine.Views.ChangePassword();

});
})(jQuery);