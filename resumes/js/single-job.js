(function($){
	$(document).ready(function(){
		new singleJobPage();
	});

	var singleJobPage = Backbone.View.extend({
		el : '#apply_form',
		events : {
			'submit form#jobseeker_auth' : 'login',
			'submit form#auth_application_form' : 'apply'
		},
		initialize: function(){
			var view = this;
			this.jobseeker = new JobEngine.Models.JobSeeker();
			this.use_captcha	= et_globals.use_captcha;

			if(typeof view.attach_id == 'undefined')
				view.attach_id = null;
			/* upload cv file for resumer */
			view.resume_loading 	= null;
			$cv = $('#resume_upload_container');
			if($cv.length > 0 )
			this.cv_uploader	= new JobEngine.Views.File_Uploader({
				el					: '#resume_upload_container',
				uploaderID			: 'resume_upload',
				thumbsize			: 'thumbnail',
				filters				: [
					{ title : 'Resume Upload', extensions : et_globals.plupload_config.cv_files_allow }
				],			
				multipart_params	: {
					// _ajax_nonce	: $user_logo.find('.et_ajaxnonce').attr('id'),
					action		: 'et_jobseeker_upload_resume',
					job_id 	: $cv.find('input[name=job_id]').val(),
				},
				cbUploaded : function(up, file, res){
					view.resume_loading.unblock();
					if (res.success){
						view.attach_id = res.data.ID;
						$(".resume-show").html(res.data.post_title);
					}else {
						pubsub.trigger('je:notification', {
							msg			: res.msg,
							notice_type	: 'error'
						});
					}
				},
				beforeSend		: function(element){
					view.resume_loading 	= new JobEngine.Views.BlockUi();
					view.resume_loading.block($('#resume_upload_container').find('.thumb'));
				}
			});

			/*end upload cv */

			// event handler for when receiving response from server after requesting login/register
			pubsub.on('je:response:auth', this.handleAuth, this);
		},

		handleAuth: function(resp, status, jqXHR){
			if (resp.status) {
				// show the form
				var authForm = $('#apply_form').find('.form-login'),
					applyForm = $('#apply_form').find('.form-apply');

				authForm.fadeOut('normal', function(){
					var display_name	=	resp.data.display_name;
					if(display_name == '') {
						display_name	=	resp.data.user_name;
					}
					applyForm.fadeIn();
					applyForm.find('.logged-name').find('span').html(display_name);
					applyForm.find('input[name=jobseeker_id]').val(resp.data.id);
					applyForm.find('input[name=emp_name]').val(display_name);
					applyForm.find('input[name=emp_email]').val(resp.data.apply_email);
				});
				$('#apply_form').find('.unauth-apply').hide();
			}
		},

		login :function(event){
			event.preventDefault();
			// get data
			var element = $(event.currentTarget),
				params = {
					user_name: element.find('input[name=user_email]').val(),
					user_email: element.find('input[name=user_email]').val(),
					user_pass: element.find('input[name=user_pass]').val(),
				}

			var loadingBtn	= new JobEngine.Views.LoadingButton ( {el : $(event.currentTarget).find('.jse-btn-login') });
			// do login
			// setting up params
			this.jobseeker.set(params)
			this.jobseeker.doAuth('login', {
				beforeSend: function(){
					loadingBtn.loading();
				},
				success: function(){
					loadingBtn.finish();
				}
			})
		},

		apply: function(event){			
			
			event.preventDefault();
			var form = $(event.currentTarget),
				loadingBtn = new JobEngine.Views.LoadingButton({el : form.find('.btn-apply')}),
				self = this;
			var params = {
				url : et_globals.ajaxURL,
				type : 'post',
				data: {
					action : 'et_jobseeker_apply_job',
					content: form.serialize(),
					attach_id : self.attach_id,
				},
				beforeSend: function(){
					if ( !loadingBtn.isLoading )
						loadingBtn.loading();
				},
				success: function(resp){
					loadingBtn.finish();
					if ( resp.success ){
						$('#apply_form').fadeOut('normal', function(){
							$(this).addClass('hide');
							$('#success-msg').fadeIn();
						});
						
					}
					else {
						pubsub.trigger('je:notification',{
							msg	: resp.msg,
							notice_type	: 'error'
						});
						
					}
					if(self.use_captcha) {
						if(typeof Recaptcha != 'undefined')
							Recaptcha.reload();
					}
				}
			}
			$.ajax(params);
		}

	});
})(jQuery);