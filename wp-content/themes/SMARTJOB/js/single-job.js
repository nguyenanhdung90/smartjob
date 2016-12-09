(function ($) {
var map;

jQuery(document).ready(function ($) {

JobEngine.Views.Single_Job	= Backbone.View.extend({
	el	: $('div#single-job'),

	events	: {
		'click a[rel=modal-box]'	: 'requestModal',
		'click a#approveJob'		: 'approveJob',
		'click span.removeFile'		: 'removeFile',
		'click button#apply2'		: 'showApplyForm',
		'click button#apply3'		: 'showHowToApply',
		'click a.reminder'			: 'showRemindForm',
		'click button.cancel'		: 'hideForm',
		'click .back-step'			: 'hideForm',
		'submit form#applicationForm': 'submitApplication',
		'submit form#reminderForm'	: 'submitReminder',
		'click .job-location' 		: 'toggleMap'
	},

	initialize	: function(){
		_.bindAll(this , 'handleAuth' , 'initApplyFormValidator', 'showApplyForm');

		var $job_title		= this.$('.job-title'),
			$job_info		= this.$('span.job-info'),
			$company_info	= this.$('div.company-profile'),

			job_data		= JSON.parse(this.$('#job_data').html()),
			company_data	= JSON.parse(this.$('#company_data').html()),

			$apply_docs		= this.$('#apply_docs_container'),
			self			= this;

		this.use_captcha	= parseInt(et_globals.use_captcha);

		this.loadingBtn	= new JobEngine.Views.LoadingButton({ el : this.$el.find('form#applicationForm button#apply')});

		this.model	= new JobEngine.Models.Job(job_data);
		this.model.author.set(company_data);

		if( $('#modal_edit_job').length > 0 && ( typeof this.editModalView === 'undefined' || !(this.editModalView instanceof JobEngine.Views.Modal_Edit_Job) ) ){
			this.editModalView	= new JobEngine.Views.Modal_Edit_Job();
		}

		if( $('#modal-reject-job').length > 0 && ( typeof this.rejectModalView === 'undefined' || !(this.rejectModalView instanceof JobEngine.Views.ModalReject) ) ){
			this.rejectModalView = new JobEngine.Views.ModalReject();
		}

		this.fileIDs	= [];
		//if(job_data.apply_method == 'isapplywithprofile') {
			this.docs_uploader	= new JobEngine.Views.File_Uploader({
				el					: $apply_docs,
				uploaderID			: 'apply_docs',
				multi_selection		: true,
				unique_names		: true,
				upload_later		: true,
				filters				: [
					{title:"Compressed Files",extensions:'zip,rar'},{title:'Documents',extensions:'pdf,doc,docx'}
				],
				multipart_params	: {
					_ajax_nonce	: $apply_docs.find('.et_ajaxnonce').attr('id'),
					action		: 'et_upload_files'
				},
				cbAdded			: function(up,files){
					var $file_list	= self.$('#apply_docs_file_list'),
						i;

					// Check if the size of the queue is over MAX_FILE_COUNT
					if(up.files.length > self.docs_uploader.MAX_FILE_COUNT){

						// Removing the extra files
						while(up.files.length > self.docs_uploader.MAX_FILE_COUNT){
							up.removeFile(up.files[up.files.length-1]);
						}
						pubsub.trigger('je:notification',{
							msg	: et_single_job.upload_file_notice + self.docs_uploader.MAX_FILE_COUNT + ' files.',
							notice_type	: 'error'
						});
					}

					// render the file list again
					$file_list.empty();
					for( i=0; i < up.files.length; i++ ){
						$(self.fileTemplate({
							id			: up.files[i].id,
							filename	: up.files[i].name,
							filesize	: plupload.formatSize(up.files[i].size),
							percent		: up.files[i].percent
							})
						).appendTo($file_list);
					}
				},

				cbRemoved	: function(up, files){
					for(var i=0; i<files.length; i++ ){
						self.$('#' + files[i].id).remove();
					}
				},

				onProgress	: function(up, file) {
					self.$('#' + file.id + " .percent").html(file.percent + "%");
				},

				cbUploaded		: function(up,file,res){
					if(res.success){
						self.fileIDs.push(res.data);
					}
					else {
						// assign a flag to know that we are having errors
						self.hasUploadError	= true;
						pubsub.trigger('je:notification',{
							msg	: res.msg,
							notice_type	: 'error'
						});
					}
				},

				onError		: function(up, err){
					pubsub.trigger('je:notification',{
						msg	: err.message,
						notice_type	: 'error'
					});
				},
				beforeSend: function(){
					self.loadingBtn.loading();
				},
				success : function(){
				}
			});
			// setup the maximum files allowed to attach in an application
			this.docs_uploader.MAX_FILE_COUNT	= 3;


		//}

		// init map
		if ( typeof GMaps !== 'undefined' && $('#jmap').length > 0 ){ // if map is exist
			if (typeof this.map == 'undefined' && typeof $('input[name=jobLocLat]').val() != 'undefined' && typeof $('input[name=jobLocLng]').val() != 'undefined'
				&& $('input[name=jobLocLat]').val() != '' && $('input[name=jobLocLng]').val() != '' ){
				$('#jmap').css('width', $('.heading-info > .main-center').width());
				this.map   =  new GMaps({
					div				: '#jmap',
					lat				: $('input[name=jobLocLat]').val() ,
					lng				: $('input[name=jobLocLng]').val() ,
					zoom			: 12,
					panControl		: false,
					zoomControl		: false,
					mapTypeControl	: false
				});
				var address = $('input[name=jobFullLocation]').val() != '' ? $('input[name=jobFullLocation]').val() : $.trim($('#job_location').html());
				var marker = this.map.addMarker({
					lat : $('input[name=jobLocLat]').val(),
					lng : $('input[name=jobLocLng]').val(),
					infoWindow : {content: address , disableAutoPan : true}
				});

				this.map.setCenter(parseFloat($('input[name=jobLocLat]').val()) + 0.02,parseFloat ($('input[name=jobLocLng]').val()) + 0.01);
				marker.infoWindow.open(this.map, marker);
			}
		}

		if(typeof GMaps === 'undefined') {
			$('#job_location').attr('title', '');
		}

		//pubsub.on('je:job:afterEditJob', this.render, this);
		pubsub.on('je:job:afterEditJob', function(model){ window.location.href = model.get('permalink');  }, this);

		// event handler for when receiving response from server after requesting login/register
		pubsub.on('je:response:auth', this.handleAuth, this);
	},

	fileTemplate	: _.template('<li id="{{id}}"><span class="icon removeFile" data-icon="D"></span>' +
		'<span class="name">{{filename }}</span>' +
		'<span class="size">{{filesize}}</span>' +
		'<span class="percent">{{ percent }}%</span></li>'),

	removeFile	: function(e){
		e.preventDefault();
		var fileID	= $(e.currentTarget).closest('li').attr("id");
		for( i=0; i < this.docs_uploader.controller.files.length; i++ ){
			if(this.docs_uploader.controller.files[i].id === fileID){
				this.docs_uploader.controller.removeFile(this.docs_uploader.controller.files[i]);
			}
		}
	},

	requestModal	: function(e){
		// prevent default behavior
		e.preventDefault();
		var modal_id = $(e.currentTarget).attr('href');

		switch(modal_id){
			case '#modal_edit_job':
				this.editModalView.onEdit(this.model);
				break;

			case '#modal_reject_job':
				this.rejectModalView.onReject({model: this.model});
				break;
		}
	},

	handleAuth: function(data, status, jqXHR){
		if ( $('#apply_form').is(':visible') ){
		} else {
			if(data.status){
				$(".form-login").hide();
				if(data.data.is_admin)
					window.location.reload();
			}
		}
	},

	render	: function(model){
		var $job_cats	= this.$el.find('span#job_cat'),
			$job_types	= this.$el.find('span#job_type'),
			user_logo	= model.author.get('user_logo'),
			cats		= model.get('categories'),
			cur_cats	= $.map(cats,function(cur,i){
				return cur.slug;
			}), // get the array of new category slugs
			prev_cats	= model.get('prev_cats'),
			diff_cats	= _.difference(cur_cats,prev_cats),
			$breadcrumb	= $('.breadcrumb'),
			$homelink	= $breadcrumb.find('a.home').clone(),
			status		= model.get('status'),
			prev_status	= model.get('prev_status');

		var apply_method	=	model.get('apply_method');
		if(apply_method	== 'ishowtoapply') {
			var button 			=	_.template ($('#how_to_apply_button').html());
			var apply_template	=	_.template ($('#apply_detail').html());
			var apply_detail		=	apply_template ({ applicant_detail : model.get('applicant_detail') });
			this.$el.find('#job_howtoapply').html(apply_detail);
		} else {
			var button	=	_.template ($('#apply_button').html());

		}
		this.$el.find('#job_howtoapply').hide();
		this.$el.find('#apply_form').hide();
		this.$el.find('#remind_form').hide();
		this.$el.find('#job_action').show();

		this.$el.find('#how_to_apply').html(button);

		this.$el
			.find('#job_title')
				.attr('data', model.id)
				.text(model.get('title'))
			.end()
			.find('#job_location')
				.text(model.get('location'))
			.end()
			.find('#job_description')
				.html(model.get('content'))
			.end()
			.find('.job_author_link')
				.attr({
					href	: model.author.get('post_url'),
					title	: model.author.get('display_name')
				})
			.end()
			.find('#job_author_name')
				.text(model.author.get('display_name'))
			.end()
			.find('#job_author_thumb img')
				.attr({
					src		: user_logo.thumbnail[0],
					data	: user_logo.attach_id
				})
			.end()
				.find('#job_author_url')
					.attr({
						href	: model.author.get('user_url'),
						title	: model.author.get('display_name')
					}).html (model.author.get('user_url'))
					.find('span:first')
						.text(model.author.get('user_url'))
					.end()
				.end()
				.find('#job_author_logo img')
					.attr({
						src		: user_logo['company-logo'][0],
						data	: user_logo.attach_id
					});

		// map
		if (model.get('location') == et_globals.anywhere){
			$('#jmap').addClass('anywhere');
			$('#job_location').removeAttr('title');
		}
		else {
			$('#jmap').removeClass('anywhere');
			$('#job_location').attr('title',et_globals.view_map);
		}

		// remove marker and add new marker
		if(typeof GMaps !== 'undefined') {
			this.map.removeMarkers();
			var marker = this.map.addMarker({
				lat : model.get('location_lat'),
				lng : model.get('location_lng'),
				infoWindow : {content: model.get('full_location') , disableAutoPan : true}
			});
		}
		$('input[name=jobLocLat]').val(model.get('location_lat'));
		$('input[name=jobLocLng]').val(model.get('location_lng'));

		if(typeof GMaps !== 'undefined') {
			this.map.setCenter(parseFloat(model.get('location_lat')) + 0.02,parseFloat (model.get('location_lng')) + 0.01);
			marker.infoWindow.open(this.map, marker);
		}

		//update breadcrumb if there is a change in job categories
		if(diff_cats && diff_cats.length>0){
			$breadcrumb.empty().append($homelink).append(' &raquo; ')
				.append('<a href="' + cats[0].url + '">' + cats[0].name + '</a>');
		}

		$.each(model.get('job_types'), function(){
			$job_types
				.empty()
				.append('<input class="job-type-slug" type="hidden" value="' + this.slug + '"/>' +
					'<a class="color-' + this.color + '" href="' + this.url + '" title="' + this.name + '"><span class="flag"></span>' + this.name + '</a>'
					);
		});

		if(!!prev_status && prev_status !== status){
			var $adminAction	= this.$el.find('#adminAction'),
				$message		= this.$el.find('.message');

			if( $adminAction.length > 0 ){
				if ( $adminAction.is(":hidden") ){
					if(status === 'pending' ){
						$adminAction.fadeIn('normal');
					}
				}
				else if(status!== 'pending'){
					$adminAction.fadeOut('normal');
				}
			}

			if( $message.length > 0 ){
				if(status!=='publish'){
					if($message.is(':hidden')){
						$message.slideDown('normal');
					}
				}
				else{
					if($message.is(':visible')){
						$message.slideUp('normal');
					}
				}

				if ( $adminAction.length === 0 && status === 'pending' ){
					status = 'pending2';
				}
				this.$el.find('.message .text').html(et_single_job.info_job_statuses[status]);
			}
		}
	},

	approveJob : function(event){
		var view = this;
		event.preventDefault();

		var blockUi = new JobEngine.Views.BlockUi();
		this.model.approve({
			beforeSend: function(){
				blockUi.block(view.$el.find('.job-controls'));
			},
			success :function(model,res){
				blockUi.unblock();
				view.$el
					.find('.message').slideUp('normal')
					.end()
					.find('#adminAction').fadeOut('normal');
			}
		});
	},

	showApplyForm	: function(e){
		e.preventDefault();
		this.$('div.form_container').children('div').hide()
			.end().find('div#apply_form').fadeIn(200,this.initApplyFormValidator);
		$(window).scrollTop( $('#apply_form').offset().top - 70 - $('#wpadminbar').height());
	},

	showHowToApply : function (e) {
		e.preventDefault();
		this.$('div.form_container').children('div').hide()
		.end().find('div#job_howtoapply').fadeIn(200,this.initApplyFormValidator);
		$(window).scrollTop( $('#job_howtoapply').offset().top - 70 - $('#wpadminbar').height());
	}
	,
	initApplyFormValidator	: function(){
		if (typeof this.application_validator === 'undefined'){
			this.application_validator	= this.$('form#applicationForm').validate({
				rules	: {
					apply_name : {
						required	: true
					},
					apply_email	: {
						required	: true,
						email		: true
					}
				}
			});
		}
	},

	showRemindForm	: function(e){
		e.preventDefault();
		this.$('div.form_container').children('div').hide()
			.end().find('div#remind_form').fadeIn(200,this.initRemindFormValidator);
	},

	initRemindFormValidator	: function(){
		if (typeof this.remind_validator === 'undefined'){
			this.remind_validator =	$('form#reminderForm').validate ({
				rules : {
					share_email : {
						required : true,
						email : true
					}
				}
			});
		}
	},

	hideForm	: function(e){
		e.preventDefault();
		this.$('div.form_container').children('div').hide()
			.end().find('div#job_action').fadeIn(200);
	},

	// event handler for when the form is submited
	submitApplication	: function(e){
		var self = this;
		var uploaded	=	false;
		e.preventDefault();


		if(this.docs_uploader.controller.files.length > 0 ) {

			this.docs_uploader.controller.bind('StateChanged', function(up) {

				if ( up.files.length === up.total.uploaded ) {
					// if no errors, post the form

					if( !self.hasUploadError ){
						self.postApplication();
					}
				}
			});


			this.hasUploadError	= false; // reset the flag before re-upload
			this.docs_uploader.controller.start();
		}
		else{
			if (this.application_validator.form()){
				this.postApplication();
			}
		}

	},

	// helper function to really post the form after having uploaded all the attachments
	postApplication	: function(){
		var that = this,
			$apply_form	= this.$('#applicationForm');

			form_data	= {
				action		: 'et_apply_job',
				job_id		: this.model.id,
				apply_name	: $apply_form.find('input#apply_name').val(),
				apply_email	: $apply_form.find('input#apply_email').val(),
				apply_note	: $apply_form.find('textarea#apply_note').val(),
				attachments	: this.fileIDs,
				_ajax_nonce	: $apply_form.find('.et_ajaxnonce').attr('id')
			},
			loadingBtn = new JobEngine.Views.LoadingButton({el : $apply_form.find('button#apply')});

		if(this.use_captcha) {
			form_data['recaptcha_response_field'] 	= Recaptcha.get_response();
			form_data['recaptcha_challenge_field'] 	= Recaptcha.get_challenge();
		}

		$.ajax({
			type		: 'POST',
			url			: et_globals.ajaxURL,
			data		: form_data,
			contentType	: 'application/x-www-form-urlencoded;charset=UTF-8',
			beforeSend: function(){
				if (!that.loadingBtn.isLoading)
					that.loadingBtn.loading();
			},
			success	:  function(data, textStatus, jqXHR){
				that.loadingBtn.finish();
				that.cbApplied(data);
				if(that.use_captcha) {
					if(typeof reCaptcha != 'undefined')
						Recaptcha.reload();
				}
			}
		});

	},

	// after the form is posted, callback this function to view success message
	cbApplied	: function(res){
		if( res.success) {
			this.$('#apply_form').hide();
			this.$('#success-msg').html(res.msg).show();
		}
		else {
			pubsub.trigger('je:notification',{
				msg	: res.msg,
				notice_type	: 'error'
			});
		}


	},

	submitReminder	: function(e){
		e.preventDefault();
		var loadingBtn = new JobEngine.Views.LoadingButton({el : $('button#remind')});
		var share_form	= $(e.currentTarget),
			self		= this,
			param		= {
				type		: 'POST',
				url			: et_globals.ajaxURL,
				data		: share_form.serialize(),
				contentType	: 'application/x-www-form-urlencoded;charset=UTF-8',
				beforeSend: function(){
					loadingBtn.loading();
				},
				success	: function (response) {
					loadingBtn.finish();
					if( response.success) {
						self.$('#remind_form').hide();
						self.$('#success-msg').html(response.msg).show();
					}
					else {
						pubsub.trigger('je:notification',{
							msg	: response.msg,
							notice_type	: 'error'
						});
					}
				}
			};
		$.ajax(param);
	},

	toggleMap : function(event){
		event.preventDefault();
		if ($('#jmap').hasClass('anywhere')) return;
		if(typeof GMaps === 'undefined') {
			$('#job_location').attr('title', '');
			return;
		}
		var view = this;

		if($('input[name=jobLocLat]').val() == '' ||  $('input[name=jobLocLng]').val() == '' )
			return false;

		if ($('.heading-info').hasClass('mapoff') && typeof GMaps !== 'undefined'){
			$('.heading-info').animate({
				'height' : 430
			}, 'normal', function(){
				// get company address
				$(this).removeClass('mapoff');
				$('#jmap').removeClass('hide');
				view.map.refresh();
				view.map.setCenter(parseFloat($('input[name=jobLocLat]').val()) + 0.02,parseFloat ($('input[name=jobLocLng]').val()) + 0.01) ;
			});
		}
		else {
			$('.heading-info').animate({
				height: 60
			},'normal', function(){
				$(this).addClass('mapoff');
				$('#jmap').addClass('hide');
			});
		}
	}

});

new JobEngine.Views.Single_Job();

});
})(jQuery);