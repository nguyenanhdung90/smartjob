(function($){
$(document).ready(function(){
	var view = new CS.SocialAuth();

});
var CS = [];
CS.SocialAuth = Backbone.View.extend({
	el: 'body.page-template-page-social-connect-php',
	events: {
		'submit #form_auth' 	: 'authenticate',
		'submit #form_username' : 'confirm_username'
	},
	initialize: function(){

	},

	authenticate: function(event){
		event.preventDefault();
		var form = $(event.currentTarget);
		var view = this;

		var params = {
			url: 	et_globals.ajaxURL,
			type: 	'post',
			data: {
				action: fe_auth.action_auth,
				content: form.serializeObject()
			},
			beforeSend: function(){
				//submit
				//form.find('input[type=submit]').loader('load');
			}, 
			success: function(resp){
				if ( resp.success ){
					if ( resp.data.status == 'wait' ){
						view.$('.social-auth-step1').fadeOut('fast', function(){
							view.$('.social-auth-step2').fadeIn();	
						});
					} else if ( resp.data.status == 'linked' ){
						window.location = et_globals.homeURL;
					}
				} else {
					alert(resp.msg);
				}
			}, 
			complete: function(){
				//form.find('input[type=submit]').loader('unload');
			}
		}
		$.ajax(params);
	},
	
	confirm_username: function(event){
		event.preventDefault();
		var form = $(event.currentTarget);
		var view = this;

		var params = {
			url: 	et_globals.ajaxURL,
			type: 	'post',
			data: {
				action: fe_auth.action_confirm,
				content: form.serializeObject()
			},
			beforeSend: function(){
				//form.find('input[type=submit]').loader('load');
			}, 
			success: function(resp){				
				if ( resp.success == true ){
					window.location = et_globals.homeURL;
				} else {
					alert(resp.msg);
				}
			}, 
			complete: function(){
				//form.find('input[type=submit]').loader('unload');
			}
		}
		$.ajax(params);
	}
})
})(jQuery);