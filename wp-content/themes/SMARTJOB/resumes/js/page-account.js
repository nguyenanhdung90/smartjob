(function($){
	$(document).ready(function(){
		new accountView();
	});

	var accountView = Backbone.View.extend({
		el 		: 'body.page-template-page-jobseeker-account-php',
		events 	: {
			'submit #update_account' 				: 'updateAccount',
			'click  .tabs li' 						: 'changeTab',
			'click .confidential .toggle-button' 	: 'toggleConfidential',
			'click .jse-contact .toggle-button' 	: 'toggleContact',
			'click .jse-remove a ' 					: 'removeAccessible'
		},

		initialize: function(){

			this.current_user 	=	new JobEngine.Models.JobSeeker (JSON.parse ($('#current_user_data').html()));
			this.loading = new JobEngine.Views.BlockUi();
			$.validator.setDefaults({

				// prevent the form to submit automatically by this plugin
				// so we need to apply handler manually
				onsubmit		: false,
				onfocusout		: function(element, event){
					if( !this.checkable(element) && element.tagName.toLowerCase() === 'textarea' ){
						this.element(element);
					}
					else if ( !this.checkable(element) && (element.name in this.submitted || !this.optional(element)) ) {
						this.element(element);
					}
				},
				validClass		: "valid", // the classname for a valid element container
				errorClass		: "message", // the classname for the error message for any invalid element
				errorElement	: 'div', // the tagname for the error message append to an invalid element container

				// append the error message to the element container
				errorPlacement: function(error, element) {
					$(element).closest('div').append(error);
				},

				// error is detected, addClass 'error' to the container, remove validClass, add custom icon to the element
				highlight	: function(element, errorClass, validClass){
					var $container = $(element).closest('div');
					if ( !$container.hasClass('error') ){
						$container.addClass('error').removeClass(validClass)
							.append('<span class="icon" data-icon="!"></span>');
					}
				},

				// remove error when the element is valid, remove class error & add validClass to the container
				// remove the error message & the custom error icon in the element
				unhighlight	: function(element, errorClass, validClass){
					var $container = $(element).closest('div');
					if( $container.hasClass('error')){
						$container.removeClass('error').addClass(validClass);
					}
					$container.find('div.message').remove()
							.end()
							.find('span.icon').remove();
				}
			});

			// init validator
			this.validator = $('#update_account').validate({
				// submitHandler: function(form){
				// 	$(form).submit();
				// 	//return false;
				// },
				rules : {
					'user_email' : {
						'email' : true
					},
					'user_pass_again' : {
						required 	: function(){ return $('#user_pass').val() != '' } ,
						'equalTo'	: '#user_pass'
					},
					'current_pass' : {
						'required' : true
					}
				},
				messages: {
					'user_email' : {
						'email' : et_account.err_email_user_email
					},
					'user_pass_again' : {
						'required' : et_account.err_required_user_pass_again,
						'equalTo' : et_account.err_equalto_user_pass_again,
					},
					'current_pass' : {
						'required' : et_account.err_required_current_pass,
					}
				}
			});
		},

		updateAccount : function(event){
			event.preventDefault();
			// validate first
			if (!this.validator.form())  return false;

			//
			var form 	= $(event.currentTarget);
			var btn 	= new JobEngine.Views.LoadingButton({el : form.find('input[type=submit]')});
			var data 	= {};

			// generate data 
			if ( form.find('input[name=user_email]').val() != '' ) data.user_email = form.find('input[name=user_email]').val();
			if ( form.find('input[name=user_pass]').val() != '' ) data.user_pass = form.find('input[name=user_pass]').val();
			data.current_pass 	= form.find('input[name=current_pass]').val();
			data.ID 			= form.find('input[name=ID]').val();

			var params = {
				url 	: et_globals.ajaxURL,
				type 	: 'post',
				data 	: {
					action 	: 'et_jobseeker_update_account',
					method : 'update',
					content : data
				},
				beforeSend: function(){
					btn.loading();
				},
				success: function(resp){
					btn.finish();
					if (resp.success){
						pubsub.trigger('je:notification',{
							msg			: resp.msg,
							notice_type	: 'success'
						});

						// redirect if change pass
						if ( resp.redirect ){
							window.location = resp.redirect;
						}
					}else {
						pubsub.trigger('je:notification',{
							msg			: resp.msg,
							notice_type	: 'error'
						});
					}
				}
			}
			$.ajax(params);
		},

		changeTab : function (event) {
			var $target = $(event.currentTarget);

			$('.content-tab').removeClass('active');
			$('.tabs li').removeClass('active');

			var tab_id	=	$target.attr('rel');
			$('#'+tab_id).addClass('active');
			$target.addClass('active');
		},

		toggleConfidential : function (event) {
			event.preventDefault();

			var element 	= $(event.currentTarget);
			var container 	= element.parents('.confidential');
			var value 		= element.hasClass('jse-button-disable') ? 'public' : 'confidential';
			var view  		= this;

			if(value == 'public') {
				$('#list-accessible').fadeOut (500);
			} else {
				$('#list-accessible').fadeIn (500);
			}

			this.current_user.set('et_privacy' , value);
			this.current_user.save( 
				{'et_privacy' : value }, 
				{
					saveData : ['et_privacy', 'display_name'],
					beforeSend : function () {
						view.loading.block(element);
					},
					success : function (model, resp) {
						if(resp.success) {
							view.loading.unblock();
							container.find('.toggle-button').removeClass('active');
							element.addClass('active');
						}
					}
				}
			);
		},

		toggleContact : function (event) {
			event.preventDefault();

			var element 	= $(event.currentTarget);
			var container 	= element.parents('.jse-contact');
			var value 		= element.hasClass('jse-button-disable') ? 1 : 0;
			var view  		= this;

			this.current_user.set('et_contact' , value);
			this.current_user.save( 
				{'et_contact' : value }, 
				{
					saveData : ['et_contact', 'display_name'],
					beforeSend : function () {
						view.loading.block(element);
					},
					success : function (model, resp) {
						if(resp.success) {
							view.loading.unblock();
							container.find('.toggle-button').removeClass('active');
							element.addClass('active');
						}
					}
				}
			);
		},

		removeAccessible : function (event) {
			event.preventDefault();
			var $target 	=	$(event.currentTarget),
				$li			=	$target.parents('li'),
				remove_id	=	$li.attr('data-company'),
				view		=	this;

			var access_list	=	[];
			_.each($('.jse-list-company li') , function (element , index) {
				var id	=	$(element).attr ('data-company');
				if(id != remove_id) {
					access_list.push (id)	;
				}
			});

			if(access_list.length <= 0 ) {
				access_list	=	'empty';
			}

			this.current_user.set('et_accessible_companies', access_list);
			this.current_user.save ({'et_accessible_companies': access_list},
					{
						saveData : ['et_accessible_companies'],
						beforeSend : function () {
							view.loading.block($li);
						},
						success : function (model, resp) {
							view.loading.unblock();
							if(resp.success) {
								$li	.remove ();
							}
						}
					}
				);
		}
	})
})(jQuery);