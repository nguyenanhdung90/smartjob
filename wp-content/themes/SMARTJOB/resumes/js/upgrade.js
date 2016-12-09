(function ($) {
$(document).ready(function () {
	JobEngine.Models.Upgrade = Backbone.Model.extend ({
		default : {
			job_package : ''
		},
		initialize : function () {

		}
	});

	JobEngine.Views.AccountUpgrade = Backbone.View.extend({
		el	: 'div#upgrade_account',
		job	: {},
		events : {
			'click div.toggle-title'				: 'selectStep',
			// step: auth
			'click div#step_auth .tab-title > div'	: 'selectTab',
			'submit div#step_auth form'				: 'submitAuth' ,

			// step: package
			'click button.select_plan'				: 'selectPlan' ,

			// step: payment
			'click button.select_payment'			: 'selectPayment'
		},

		initialize : function () {
			_.bindAll(this, 'waiting', 'endWaiting' , 'updateAuthor');
			var job_data	= this.$('#job_data').html();
			if (!!job_data){
				job_data	= JSON.parse(job_data);
			}
			this.job			=	new JobEngine.Models.Upgrade (job_data);
			this.currentUser	= 	JobEngine.app.currentUser;

			this.currentUser.on('change:id', this.updateAuthor, this);

			// get plans
			this.plans 		= JSON.parse($('#package_plans').html());

			this.setupView();

			this.bind('waiting', this.waiting, this);
			this.bind('endWaiting', this.endWaiting, this);

			// setup the template login success for step 2
			this.tpl_login_success	= new _.template(
				'<div class="login_success">' +
					et_globals.msg_login_ok +
					'<span><a id="logout_link" href="#">' + et_globals.msg_logout + '</a></span>' +
				'</div>');
		},

		setupView : function () {
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
						username	: et_upgrade.reg_user_name
					}
				}

			});

			this.login_validator	= this.$('form#login').validate({
				rules	: {
					log_email	: "required",
					log_pass	: "required"
				}
			});
		},

		selectPlan	: function(event){
			var $target		= $(event.currentTarget),
				$container	= $target.closest('li'),
				$step		= $container.closest('div.step'),
				nextStep	= $step.nextAll().not(".completed").first(),
				amount		= $target.attr('data-price'),
				planID		= $target.attr('data-package'); // get the selected plan (need to update)
				plan 		= this.plans[planID];


				$container.addClass('selected')
					.siblings().removeClass('selected');

				// set job package 
				this.job.set({ job_package:planID });

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

		// STEP: PAYMENT //////////////////////////////////////////////////////////////
		// send a request to payment process in back-end
		// receive response and redirect to the returned URL
		selectPayment	: function(event){
			event.preventDefault();
			var view = this;
			var paymentType	=	this.$(event.currentTarget).attr('data-gateway');
			var loading	=	new JobEngine.Views.LoadingButton( {	el: $(event.currentTarget) } );
			var params	= {
				type        : 'POST',
				dataType    : 'json',
				url         : et_globals.ajaxURL,
				contentType	: 'application/x-www-form-urlencoded;charset=UTF-8',
				data		: {
					action		: 'resume_view_setup_payment',
					jobID		: this.job.get('ID'),
					//authorID	: this.job.get('author_id'),
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
						msg			: et_upgrade.notice_step_not_allowed,
						notice_type	: 'error'
					});
				}
			}
		},

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
						//$('.et_ajaxnonce').attr('id', res.data.logo_nonce);
						//view.trigger('endWaitingAuth', $container.find('button[type=submit]'));
					}
				});
			}		
		} , 

		// step: AUTH
		// this function the handler for when the currentUser (this job author) is changed
		updateAuthor	: function(){
			
			var authStep	= this.$('div#step_auth'),
				jobStep		= this.$('div#step_job'),
				prevSteps	= authStep.prevAll(),
				stepToShow	= null;

			if (authStep.length > 0){
				prevSteps.each(function(i,ele){
					var $ele	= jQuery(ele);
					if(!$ele.hasClass('completed') && stepToShow === null){
						stepToShow	= $ele;
						return false;
					}
				});
				// if this currentUser has an id && the auth step existed, the user has just been logged in
				if( !this.currentUser.isNew()){
					// change the view in auth step
					if(!authStep.hasClass('completed')){

						this.$('div#step_auth .toggle-content').children('div').hide()
							.end().append(this.tpl_login_success({ company : this.currentUser.get('display_name') }));

						this.markStepCompleted(authStep);
						if(stepToShow === null){
							this.showStep(authStep.nextAll().not('.completed').first());
						}
					}
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
				if( this.currentUser.isNew() ){
					window.location.reload();
				}
			}

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
				this.removePaymentStep();
			} else {
				this.showPaymentStep ();
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
		} ,

		showPaymentStep	: function(){
			this.$('#step_payment').show();
			this.$('#step_job #submit_job').html(et_upgrade.button_continue);
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
		} ,

		waiting : function(e){
			this.loadingBtn = new JobEngine.Views.LoadingButton( {el: $(e)} );
			this.loadingBtn.loading();
			// this.paymentButton = $(e).html();
			// $(e).html( et_globals.loading );
		},

		endWaiting: function(){
			this.loadingBtn.finish();
		}

	});

	
	// modify validator: add new rule for username
	jQuery.validator.addMethod("username", function(value, element) {
		var ck_username = /^[A-Za-z0-9_]{1,20}$/;
		return ck_username.test(value);
	});

	JobEngine.post_job	=	new JobEngine.Views.AccountUpgrade();
});

})(jQuery);