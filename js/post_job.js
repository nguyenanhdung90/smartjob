(function ($) {
jQuery(document).ready(function($) {

	// VIEW: POST JOB //////////////////////////////////////////////////////////////
	// define the view for this post_job page
	JobEngine.Views.Post_Job	= Backbone.View.extend({
		el	: $('div#post_job'),
		job	: {},

		tpl_login_success : null,

		// event handlers for user interactions
		events	: {
			// general
			'click div.toggle-title'				: 'selectStep',
			// step: auth
			'click div#step_auth .tab-title > div'	: 'selectTab',
			'submit div#step_auth form'				: 'submitAuth',
			'click div.login_success a#logout_link'	: 'logoutCompany',
			// step: job
			'keyup input#full_location'				: 'geocoding',
			'blur input#full_location'				: 'resetLocation',
			'submit form#job_form'					: 'submitJob',
			// step: package
			'click button.select_plan'				: 'selectPlan',
			// step: payment
			'click button.select_payment'			: 'selectPayment',

			// 'click #add_sample' 					: 'editSample',
			// 'change #add_sample_input' 				: 'changeAddress',
			// 'keyup #add_sample_input' 				: 'keyupAddress',
			'click .apply input:radio'				: 'switchApplyMethod',
			'change #user_url'						: 'autoCompleteUrl',
			'click #login .forgot-pass-link'		: 'requestForgotPass'
		},


		// run once when initialize the view
		initialize	: function(){
			var view 	=	this;

			_.bindAll(this , 'updateAuthor' , 'updateProcess' , 'updateLogoNonce');

			// setup the post-job view
			this.setupView();

			this.useCaptcha	=	parseInt(et_globals.use_captcha);

			var job_data	= this.$('#job_data').html();
			if (!!job_data){
				job_data	= JSON.parse(job_data);
			}

			// get plans
			this.plans 		= JSON.parse($('#package_plans').html());

			// initialize the job model
			this.job		= new JobEngine.Models.Job(job_data);
			this.job.author	= JobEngine.app.currentUser;

			// bind the event again because we assign this.job.author to current user
			this.job.author.on('change', this.job.updateJobAuthor, this.job);

			// if the current user model of the app has changed, update it to the view
			this.job.author.on('change:id', this.updateAuthor, this);

			this.job.on('change:is_use_package', this.updateProcess, this);

			this.job.on('change:is_free', this.updateProcess, this);

			// hide step 3 if free plan is selected
			if(this.job.get('job_paid') === "2"){
				this.removePaymentStep();
			}

			var amount	=	$('#step_package').find('.selected').find('button').attr('data-price');
			if(parseFloat(amount) === 0){ // is free remmove payment process
				this.job.set({is_free:1});
				this.removePaymentStep();
			} else {
				this.showPaymentStep ();
			}
			// set author param for uploader
			if(this.job.author.has('id')){
				this.logo_uploader.updateConfig({
					'multipart_params'	: {
						'author'	: this.job.author.get('id')
					}
				});
			}
			// if job package is changed and it is not a new job, sync to server
			this.job.on("change:job_package", function(){
				if( !this.isNew() ){
					this.save();
					//this.updateProcess();
				}
				view.updateProcess();
			});

			//
			// this.loadingPostJob = new JobEngine.Views.LoadingButton({el : '#submit_job'});
			// this.loadingLogin = new JobEngine.Views.LoadingButton({el : '#submit_login'});
			// this.loadingRegister = new JobEngine.Views.LoadingButton({el : '#submit_register'});
			this.loadingBtn = null;

			// reset nonce for user logo upload
			pubsub.on('je:response:auth', this.updateLogoNonce, this);

			this.bind('waitingPostJob', this.waitingPostJob, this);
			this.bind('endWaitingPostJob', this.endWaitingPostJob, this);
			this.bind('waitingAuth', this.waitingAuth, this);
			this.bind('endWaitingAuth', this.endWaitingAuth, this);
			this.bind('waitingPayment', this.waitingPayment, this);
			this.bind('endwaitingPayment', this.endWaitingPayment, this);


			if($('.mark-step').length > 0 ) {
				this.initJobPlan();
			}
		},

	// PAGE BEHAVIORS //////////////////////////////////////////////////////////////
		// helper function: setup the view on 1st load
		setupView	: function(){

			// init logo upload
			var that		= this,
				$user_logo	= this.$('#user_logo_container');

			$('#step_package').find('.toggle-title').addClass('bg-toggle-active');
			// init the map for location input only when it is not initialized
			if( typeof this.map === 'undefined' && typeof GMaps !== 'undefined' ){
				this.map   =  new GMaps({
					div				: '#map',
					lat				: 73.96487765189111,
					lng				: -133.6312064,
					zoom			: 1,
					panControl		: false,
					zoomControl		: false,
					mapTypeControl	: false
				});
				if($('#location_lat').val() !== '' && $('#location_lng').val() !== '') {
					var lat	=	$('#location_lat').val(),
						lng	=	$('#location_lng').val();
					this.map.setCenter (lat, lng);
					this.map.addMarker ({
						lat: lat,
						lng: lng,
						draggable : true,
						dragend : function (e) {
							that.$('#location_lat').val (e.position.$a);
							that.$('#location_lng').val(e.position.ab);
						}
					});
				}
			}

			var blockUi = new JobEngine.Views.BlockUi();
			this.logo_uploader	= new JobEngine.Views.File_Uploader({
				el					: $user_logo,
				uploaderID			: 'user_logo',
				thumbsize			: 'company-logo',
				multipart_params	: {
					_ajax_nonce	: $user_logo.find('.et_ajaxnonce').attr('id'),
					action		: 'et_logo_upload'
				},
				cbUploaded		: function(up,file,res){
					if(res.success){
						//that.job.author.set('user_logo',res.data,{silent:true});
					} else {
						pubsub.trigger('je:notification',{
							msg	: res.msg,
							notice_type	: 'error'
						});
					}
				},
				beforeSend	: function(element){
					blockUi.block($user_logo.find('.company-thumbs'));
				},
				success : function(){
					blockUi.unblock();
				}
			});

			this.register_validator	= this.$('form#register').validate({
				rules	: {
					reg_user_name : {
						required	: true,
						username	: true
					},
					reg_email	: {
						required	: true,
						email		: true
					},
					reg_pass		: 'required',
					reg_pass_again	: {
						required	: true,
						equalTo		: "#reg_pass"
					}
				},
				messages: {
					reg_user_name: {
						username	: et_post_job.reg_user_name
					}
				}

			});

			this.login_validator	= this.$('form#login').validate({
				rules	: {
					log_email	: "required",
					log_pass	: "required"
				}
			});

			var job_require_fields  = et_globals.job_require_fields,
                required_user_url   = $.inArray('user_url',job_require_fields) == -1 ? false : true;

			this.job_form_validator		= this.$('form#job_form').validate({
				ignore : "",
				rules	: {
					title			: "required",
					//full_location	: "required",
					display_name	: "required",
					content			: "required", 
					user_url		: {
						required	: required_user_url,
						url			: true
					}
				},
				errorPlacement: function(label, element) {
					// position error label after generated textarea
					if (element.is("textarea")) {
						label.insertAfter(element.next());
					} else {
						label.insertAfter(element)
					}
				}
			});

			// init tinyMCE
			tinymce.EditorManager.execCommand('mceAddControl', true, 'content');
			// tinyMCE.execCommand('mceAddControl', false, 'applicant_detail');

			// setup the template login success for step 2
			this.tpl_login_success	= new _.template(
				'<div class="login_success">' +
					et_globals.msg_login_ok +
					'<span><a id="logout_link" href="#">' + et_globals.msg_logout + '</a></span>' +
				'</div>');

			// hide all tabs except the active one
			this.$('div#step_auth')
				.find('.tab-content > div:not(.current)').hide();

			// collapse all step & show the active step
			this.$('div.step:not(.current)')
				.find('.toggle-content').hide();

			// don't break the chain
			return this;
		},

		// event handler: run when a user click on a step
		selectStep	: function(event){
			var step		= this.$(event.currentTarget).closest('div.step'),
				curStepIndex= this.$('div.step.current').index(),
				flag		= true; // flag all previous steps having been completed

			if ( !step.hasClass('current')){

				// check if all previous steps having been completed or not
				step.prevAll().each(function(i,ele){
					if(!jQuery(ele).hasClass('completed')){
						flag	= false;
						return false;
					}
				});
				if ( flag || step.index() < curStepIndex ){
					this.showStep( step );
				}
				else {
					pubsub.trigger('je:notification', {
						msg			: et_post_job.notice_step_not_allowed,
						notice_type	: 'error'
					});
				}
			}
		},

		reloadMap	: function(args){
			var that	= this,
				params	= _.extend({},args),
				$locationtxt	=	this.$('#location');

			params.callback	= function(results,status){
				var latlng, location_lat, location_lng;

				if (status == 'OK') {
					location_lat	= that.$('#location_lat'),
					location_lng	= that.$('#location_lng');
					latlng			= results[0].geometry.location;
					that.map.setZoom (12);
					that.map.setCenter(latlng.lat(), latlng.lng());
					that.map.removeMarkers() ;
					that.map.addMarker({
						lat: latlng.lat(),
						lng: latlng.lng(),
						draggable : true,
						dragend : function (e) {
							location_lat.val (e.position.$a);
							location_lng.val(e.position.ab);
						}
					});
					location_lat.val(latlng.lat());
					location_lng.val(latlng.lng());

				}
				if (typeof args.callback === 'function'){args.callback();}
			};

			GMaps.geocode(params);
		},

		// helper function: show the needed step id
		showStep	: function(step){
			var that = this;

			// close all contents & remove active class of current title
			this.$('div.step')
				.removeClass('current')
				.find('.toggle-content').slideUp(200)
				.end().find('.toggle-title').removeClass("bg-toggle-active");

			// show the selected step
			step.addClass('current')
				.find('.toggle-title').addClass('bg-toggle-active')
				.next().slideToggle(300);

			// show step check amount of job package to specify free
			var amount	=	$('#step_package').find('.selected').find('button').attr('data-price');
			if(parseFloat(amount) === 0){ // is free remmove payment process
				this.job.set({is_free:1});
				//this.removePaymentStep();
			} else {
				//this.showPaymentStep ();
			}
			// refresh the map to fix its wrong display when we init the map in a hidden div
			if( step.attr('id') === 'step_job' && typeof GMaps !== 'undefined' && typeof this.map.refresh === 'function' ){
				// refresh map
				this.map.refresh();

				//reset center map if have lat and lng
				if($('#location_lat').val() !== '' && $('#location_lng').val() !== ''){

					this.map.setZoom(12);
					this.map.setCenter ($('#location_lat').val(), $('#location_lng').val());

				}

				if ( this.job.has('location_lat') && this.job.has('location_lng') ){

					this.map.setZoom(12);
					this.reloadMap({lat:this.job.get('location_lat'),lng:this.job.get('location_lng')});

				}

			}

			// don't break the chain
			return this;
		},

		geocoding : function (event) {
			var that		= this,
				$location	= $(event.currentTarget);

			if ( typeof this.t !== 'undefined'){
				clearTimeout( this.t );
			}

			this.t = setTimeout(function(){
				that.reloadMap({address: $.trim($location.val())});
			}, 500);
		},

		resetLocation : function (event) {
			var $full		=	$(event.currentTarget),
				$lat		=	this.$('#location_lat'),
				$lng		=	this.$('#location_lng'),
				$location	= 	this.$('#location'),
				$locationtxt	=	this.$('.address-note span');

			// prevent loading geocode when user have just deleted full location
			if ($.trim($full.val()) === ''){
				$lat.val('');
				$lng.val('');
				$location.val('');
			}
			else{
				GMaps.geocode({
					lat: $lat.val(),
					lng: $lng.val(),
					callback: function(results, status) {
						var length, address, full_address, district, city, i;
						if (status == 'OK') {
						}
					}
				});
			}
		},

		markStepCompleted	: function(step){
			if (!step.hasClass('completed')){
				step.addClass('completed')
					.find('.toggle-title').addClass('toggle-complete');
			}
			return this;
		},

		markStepIncompleted	: function(step){
			if (step.hasClass('completed')){
				step.removeClass('completed')
					.find('.toggle-title').removeClass('toggle-complete');
			}
			return this;
		},

		/**
		 * Display input field for company to change their address
		 */
		editSample: function(event){
			event.preventDefault();
			var target 		= $(event.currentTarget),
				container 	= target.parent(),
				content 	= target.html().substring(1, target.html().length - 1),
				input 		= container.find('input').hide().val(content);

			target.hide();
			container.append(input.show()).addClass('editing');
		},

		changeAddress: function(event){
			$('#location').val($(event.currentTarget).val());
		},

		keyupAddress: function(event){
			var input = $('#add_sample_input'),
				label = $('#add_sample'),
				container = $('#add_sample_input').parent();
			// if trigger escape key
			if (event.which == 27){
				input.hide();
				label.html('"' + input.val() + '"').show();
				container.removeClass('editing');
			} else if(event.which == 13){ // if trigger enter key
				input.hide();
				label.html('"' + input.val() + '"').show();
				container.removeClass('editing');
			}
		},

		initJobPlan	 : function () {
			var $target		= $('.mark-step'),
				$container	= $target.closest('li'),
				$step		= $container.closest('div.step'),
				nextStep	= $step.nextAll().not(".completed").first(),
				amount		= $target.attr('data-price'),
				planID		= $target.attr('data-package'); // get the selected plan (need to update)
				plan 		= this.plans[planID];


			//if ( !($container.hasClass('selected') ) ){
			// mark the selected plan as selected
			$container.addClass('selected')
				.siblings().removeClass('selected');

			// set job package
			this.job.set({job_package:planID, featured: plan.featured});

			// set the job package of job model & free status
			if(parseFloat(amount) === 0){
				this.job.set({is_free:1});
			}
			else{
				this.job.set({is_free:0});
			}

			if (typeof(this.job.author.get('payment_plans')) != 'undefined' &&
				typeof(this.job.author.get('payment_plans')[planID]) != 'undefined' &&
				this.job.author.get('payment_plans')[planID] > 0){
				this.job.set({is_use_package:1});
			}else {
				this.job.set( {is_use_package:0} );
			}

			$step.find('span.step-plan-label').html($target.attr('data-label'));
			this.removePaymentStep();
			// if a plan has been selected
			// change the step status to "completed"
			if (!!this.job.get('job_package')){
				this.markStepCompleted($step);
			}

			if(nextStep.is(':visible')){
				this.showStep(nextStep);
			}
			else{
				this.showStep($step.next());
			}
		},

	// END PAGE BEHAVIORS //////////////////////////////////////////////////////////////

	// STEP: PACKAGE //////////////////////////////////////////////////////////////
		// run when a user select a plan
		// set the job model to selected plan
		// change the step status to completed
		// change to step 2
		selectPlan	: function(event){
			var $target		= $(event.currentTarget),
				$container	= $target.closest('li'),
				$step		= $container.closest('div.step'),
				nextStep	= $step.nextAll().not(".completed").first(),
				amount		= $target.attr('data-price'),
				planID		= $target.attr('data-package'); // get the selected plan (need to update)
				plan 		= this.plans[planID];



			// set the job package of job model & free status
			if(parseFloat(amount) === 0){
				if( parseInt(et_post_job.limit_free_plan) > 0  ) {
					if( typeof(this.job.author.get('used_free')) != 'undefined') {

						var used_free = this.job.author.get('used_free');
						if(parseInt(used_free) >= parseInt(et_post_job.limit_free_plan)  ) {
							pubsub.trigger('je:notification',{
								msg	: et_post_job.limit_free_msg,
								notice_type	: 'error'
							});
							return false;
						}

					}
				}
				this.job.set({is_free:1});
			}
			else{
				this.job.set({is_free:0});
			}

			//if ( !($container.hasClass('selected') ) ){
			// mark the selected plan as selected
			$container.addClass('selected')
				.siblings().removeClass('selected');

			// set job package
			this.job.set({job_package:planID, featured: plan.featured});

			this.job.set({is_use_package:0});

			if (typeof(this.job.author.get('payment_plans')) != 'undefined' &&
				typeof(this.job.author.get('payment_plans')[planID]) != 'undefined' &&
				this.job.author.get('payment_plans')[planID] > 0 ) {
				this.job.set( {is_use_package:1} );

			}
			$step.find('span.step-plan-label').html($target.attr('data-label'));

			// if a plan has been selected
			// change the step status to "completed"

			if (!!this.job.get('job_package')){
				this.markStepCompleted($step);
				$target.html(et_post_job.txt_selected);
			}

			if(nextStep.is(':visible')){
				this.showStep(nextStep);
			}
			else{
				this.showStep($step.next());
			}
		},

		updateProcess	: function(){
			var isFree = this.job.get('is_free');
			var is_use_package = this.job.get('is_use_package');

			if( isFree === 1 || is_use_package === 1 ){
				this.removePaymentStep();
			}
			else{
				this.showPaymentStep();
			}
		},

		removePaymentStep	: function(){
			this.$('#step_payment').hide();
			this.$('#step_job #submit_job').html(et_post_job.button_submit);
		},

		showPaymentStep	: function(){
			this.$('#step_payment').show();
			this.$('#step_job #submit_job').html(et_post_job.button_continue);
		},

	// END STEP: PACKAGE //////////////////////////////////////////////////////////////

	// STEP: AUTH //////////////////////////////////////////////////////////////
		// event handler: run when a user select a tab
		selectTab	: function(event){
			var $target = this.$(event.currentTarget),
				index	= $target.index();

			if ( !$target.hasClass('active') ){
				// change style for tab title
				$target.siblings().removeClass('active')
					.end().addClass('active');

				// show the selected tab & focus to the first input
				this.$(".tab-content > div").hide()
					.eq(index).fadeIn(200)
					.find('input:first').focus();
			}
			return false;
		},

		// step: AUTH
		// event handler for when user submit the form
		submitAuth		: function(event){
			event.preventDefault();
			// get the submitted form & its id
			var $target		= this.$(event.currentTarget),
				$container	= $target.closest('div.form'),
				form_type	= $target.attr('id'),
				view		= this,
				result;

			if( this[form_type + '_validator'].form() ){
				// update the auth model before submiting form

				$target.find("input,textarea,select").each(function(index, value){
				      var name = $(this).attr('name');
				      JobEngine.app.auth.set(name, $(this).val());
				});

				JobEngine.app.auth.setUserName($container.find('input.is_user_name').val());
				JobEngine.app.auth.setEmail($container.find('input.is_email').val());
				JobEngine.app.auth.setPass($container.find('input.is_pass').val());
				result	= JobEngine.app.auth.doAuth(form_type,{
					renew_logo_nonce:true,
					beforeSend	: function(){
						view.loadingBtn = new JobEngine.Views.LoadingButton({el : $target.find('button[type=submit]')});
						view.loadingBtn.loading();
						//view.trigger('waitingAuth', $container.find('button[type=submit]'));
					},
					success	: function(res){
						view.loadingBtn.finish();
						if(JobEngine.app.currentUser.get('role') == 'jobseeker') {
							window.location.reload();
						}

						if(typeof res != 'undefined' && !res.status){
							if(typeof Recaptcha != 'undefined'){
								Recaptcha.reload();
							}
						} else if(typeof res != 'undefined' && res.status){
							if(typeof Recaptcha != 'undefined'){
								Recaptcha.reload();
							}
						}
					}
				});

			}
		},

		waitingAuth : function(e){
		},
		endWaitingAuth : function(e){
			this.loadingBtn.finish();
			//$(e).html(this.authButton);
		},

		// step: AUTH
		// event handler
		// when user click log out in step authentication
		logoutCompany	: function(e){
			e.preventDefault();
			var image = $('<img>').attr({
						'alt'	: 'loading',
						'src'	: et_globals.imgURL + '/loading.gif',
						'class' : 'loading'
					});
			JobEngine.app.auth.doLogout({
				beforeSend: function(){
					image.insertAfter($(e.currentTarget));
				},
				success : function(){
					image.remove();
				}
			});
		},

		// step: AUTH
		// this function the handler for when the currentUser (this job author) is changed
		updateAuthor	: function(){

			var authStep	= this.$('div#step_auth'),
				jobStep		= this.$('div#step_job'),
				prevSteps	= authStep.prevAll(),
				stepToShow	= null;

			if(JobEngine.app.currentUser.get('role') == 'jobseeker') {
				pubsub.trigger('je:notification',{
					msg	: et_post_job.log_seeker,
					notice_type	: 'error'
				});

				setTimeout (function () {
					window.location.reload();
				}, 2000 );

			}

			if (authStep.length > 0){
				prevSteps.each(function(i,ele){
					var $ele	= jQuery(ele);
					if(!$ele.hasClass('completed') && stepToShow === null){
						stepToShow	= $ele;
						return false;
					}
				});
				// if this currentUser has an id && the auth step existed, the user has just been logged in
				if( !this.job.author.isNew()){
					// change the view in auth step
					if(!authStep.hasClass('completed')){

						this.$('div#step_auth .toggle-content').children('div').hide()
							.end().append(this.tpl_login_success({ company : this.job.author.get('display_name') }));

						this.markStepCompleted(authStep);
						if(stepToShow === null){
							this.showStep(authStep.nextAll().not('.completed').first());
						}
					}
					// update the author param for logo uploader
					this.logo_uploader.updateConfig({
						'multipart_params':	{
							'author'	: this.job.author.id
						},
						'updateThumbnail'	: true,
						'data'				: this.job.author.get('user_logo')
					});
				}
				else{
					// mark both steps Auth & Job incompleted
					this.markStepIncompleted(authStep)
						.markStepIncompleted(jobStep);
					if(stepToShow === null){
						this.showStep(authStep);
					}

					this.$('div#step_auth .toggle-content').children('div.login_success')
						.fadeOut(200,function(){
							$(this).remove();
						})
						.end().children('div').fadeIn(400);
				}

			}
			// the user has already logged in at the beginning, so there is no auth step
			else{
				// if the user login & then log out, there is no auth step, so reload the page
				if( this.job.author.isNew() ){
					window.location.reload();
				}
			}

			// if company has purchase current plan before, set it free
			var purchase = this.job.author.get('payment_plans');
			if (!!purchase && purchase[this.job.get('job_package')] && purchase[this.job.get('job_package')] > 0){
				this.removePaymentStep();
				this.job.set({is_free:1});
			}

			// update or clear the company fields
			this.updateCompany();
		},

		updateLogoNonce	: function(data,status,jqXHR){
			if(data.logo_nonce){
				this.logo_uploader.updateConfig({
					'multipart_params':	{
						'_ajax_nonce'	: data.logo_nonce
					}
				});
			}
		},

		updateCompany	: function(){
			if(!this.job.author.get('ID')) return ;
			var user_logo	= this.job.author.get('user_logo'),
				$form		= this.$('form#job_form'),
				location	= this.job.author.getLocation();

			// update the job form with new value of currentUser
			if(user_logo)
			$form
				.find('input#display_name').val(this.job.author.getName())
				.end()
				.find('input#user_url').val(this.job.author.getUrl())
				.end()
				.find('input#full_location').val(location.full_location)
				.end()
				.find('input#location').val(location.location)
				.end()
				.find('input#location_lat').val(location.location_lat)
				.end()
				.find('input#location_lng').val(location.location_lng)
				.end()
				.find('input#apply_email').val(this.job.author.getApplyEmail())
				.end()
				.find('textarea#applicant_detail').val(this.job.author.getApplicantDetail())
				;

			if ( !!user_logo && 'company_logo' in user_logo && !!user_logo['company_logo'] ){
				$form.find('img#user_logo_thumb').attr({
					src	: user_logo['company_logo'][0]
				});
			}
			if(this.job.author.getApplyMethod() == 'ishowtoapply') {
				$('#ishowtoapply').attr('checked', true);
				$('#applicant_detail').addClass('required');
			} else {
				$('#isapplywithprofile').attr('checked', true);
				$('#apply_email').addClass('required email');
			}
			if(this.job.author.getApplicantDetail() && $('#applicant_detail').length > 0 )
				tinymce.EditorManager.get('applicant_detail').setContent(this.job.author.getApplicantDetail());

		},

	// END STEP: AUTH //////////////////////////////////////////////////////////////

	// STEP: JOB //////////////////////////////////////////////////////////////
		// event handler for when user submit step 3 form
		// submit the job form to create DRAFT post
		submitJob		: function(event){
			event.preventDefault();

			var job_require_fields  = et_globals.job_require_fields,
                required_display    = $.inArray('display_name',job_require_fields)== -1 ? false : true,
                required_user_url   = $.inArray('user_url',job_require_fields) == -1 ? false : true;


			this.job_form_validator		= this.$('form#job_form').validate({
				ignore : "",
				rules	: {
					title			: "required",
					//full_location	: "required",
					display_name	: { required : required_display},
					content			: "required",
					user_url		: {
						required	: required_user_url,
						url			: true
					}
				},
				errorPlacement: function(label, element) {
					// position error label after generated textarea
					if (element.is("textarea")) {
						label.insertAfter(element.next());
					} else {
						label.insertAfter(element)
					}
				}
			});

			// get the submitted form & its id
			var that		= this,
				$container	= this.$(event.currentTarget).closest('div.form'),
				$jobinfo	= $container.find('div#job_info'),
				jobStep		= $container.closest('div.step'),
				companyData	= {},
				jobData		= {};

			var applicant_detail_ok	=	true;
			$('.applicant_detail').removeClass('error');
			if($('#ishowtoapply').attr('checked')) {
				var applicant_detail	=	tinymce.EditorManager.get('applicant_detail').getContent();
				$('#applicant_detail').val(applicant_detail);
			}

			//tinyMCE.triggerSave();


			// validate the job before submiting
			if( this.$('form#job_form').valid() ) {
				// validate other form
				pubsub.trigger('je:post:validate');

				// get all input value in the company form to generate an array
				$container.find('div#company_info input').each(function(){
					var $this	= $(this);
					companyData[$this.attr('id')]	= $this.val();
				});


				// use that array to set data for the author
				this.job.author.set(companyData, {silent: true});
				this.job.author.trigger('change');
				//this.job.author.save();
				// get all input value in job form to generate an array
				$jobinfo.find('input,textarea,select').each(function(){
					var $this	= $(this);
					jobData[$this.attr('id')]	= $this.val();
				});

				// get the job type & category & add to the array
				jobData['job_types']	= [{slug: $jobinfo.find('select#job_types').val()}];
				jobData['categories']	= [{slug: $jobinfo.find('select#categories').val()}];

				jobData['raw'] = $('#job_form').serialize();

				if(this.useCaptcha) {
					//jobData['recaptcha_response_field'] = Recaptcha.get_response();
					jobData['recaptcha_challenge_field'] = Recaptcha.get_challenge();
					jobData['recaptcha_response_field']  = $('form#job_form').find("input[name='recaptcha_response_field']").val();

				}


				this.job.set(jobData,{silent:true})
					.save({},{
						author_sync	:true,
						beforeSend	: function(){
							that.loadingBtn = new JobEngine.Views.LoadingButton( {el: $('button#submit_job')} );
							that.loadingBtn.loading();
						},
						success		: function(model,resp){
							that.loadingBtn.finish();

							if(resp.success){
								if(resp.success_url){
									window.location	= resp.success_url;
								}
								else{
									that.markStepCompleted(jobStep)
										.showStep(jobStep.nextAll().not('.completed').first());
								}
							}else {
								if(that.useCaptcha) {
									if(typeof reCaptcha != 'undefined'){
										Recaptcha.reload();
									}
								}
							}
						}
					});
			} else { // trigger event to show error message
				pubsub.trigger('je:notification',{
					msg	: et_post_job.error_msg,
					notice_type	: 'error'
				});

			}

		},



		switchApplyMethod : function (event) {
			//event.preventDefault();
			var apply_method	=	$(event.currentTarget).val();
			if(apply_method == 'isapplywithprofile') {
				$('#apply_email').addClass('required email');
				$('#applicant_detail').removeClass('required');
				$('.applicant_detail').removeClass('error');
			}

			if(apply_method == 'ishowtoapply') {
				$('#applicant_detail').addClass('required');
				$('#apply_email').removeClass('required');
				$('.email_apply').removeClass('error');
			}
			$('.apply').find('.icon').remove();
			$('.apply').find('.message').remove();
			$('#apply_method').val(apply_method);
		},
		waitingPostJob : function(){

			// this.continueButton = this.$el.find('button#submit_job').html();
			// this.$el.find('button#submit_job').html(et_globals.loading);
		},
		endWaitingPostJob : function(){
			this.loadingBtn.finish();
			// this.$el.find('button#submit_job').html(this.continueButton);
		},

	// END STEP: JOB //////////////////////////////////////////////////////////////

	// STEP: PAYMENT //////////////////////////////////////////////////////////////
		// send a request to payment process in back-end
		// receive response and redirect to the returned URL
		selectPayment	: function(event){
			event.preventDefault();
			var view = this;
			var paymentType	=	this.$(event.currentTarget).attr('data-gateway');
			var loading	=	new JobEngine.Views.LoadingButton( { el : $(event.currentTarget)} );
			var params	= {
				type        : 'POST',
				dataType    : 'json',
				url         : et_globals.ajaxURL,
				contentType	: 'application/x-www-form-urlencoded;charset=UTF-8',
				data		: {
					action		: 'et_payment_process',
					jobID		: this.job.id,
					authorID	: this.job.get('author_id'),
					packageID	: this.job.get('job_package'),
					paymentType	: paymentType,
					coupon_code	: $('#coupon_code').val()
				},
				beforeSend: function(){
					loading.loading();
				},
				success : function (response) {
					//alert (response.data.url);
					loading.finish();
					if(response.success) {
						if(response.data.ACK ) {
								/**
								 * process for another payment gateway
								*/
								$('#checkout_form').attr('action',response.data.url);
								if(typeof response.data.extend !== "undefined") {
									$('#checkout_form .payment_info').html('').append(response.data.extend.extend_fields);
								}
								$('#payment_submit').click();
							//}

						}
					} else {
						pubsub.trigger('je:notification',{
							msg	: response.errors[0],
							notice_type	: 'error'
						});
					}
				}
			};
			jQuery.ajax(params);
		},
		waitingPayment : function(e){
			this.loadingBtn = new JobEngine.Views.LoadingButton( {el: $(e)} );
			this.loadingBtn.loading();
		},

		endWaitingPayment: function(){
			this.loadingBtn.finish();
		},

		autoCompleteUrl : function (event) {
			var val		=	$(event.currentTarget).val();
			if (val.length == 0) { return true; }

		    // if user has not entered http:// https:// or ftp:// assume they mean http://
		    if(!/^(https?|ftp):\/\//i.test(val)) {
		        val = 'http://'+val; // set both the value
		        $(event.currentTarget).val(val); // also update the form element
		        $(event.currentTarget).focus();
		    }
		},

		requestForgotPass : function () {
			pubsub.trigger('je:request:forgot_pass');
		}
	// END STEP: PAYMENT //////////////////////////////////////////////////////////////

	});

	// initialize the job posting view
	JobEngine.post_job =	new JobEngine.Views.Post_Job();

});
})(jQuery);