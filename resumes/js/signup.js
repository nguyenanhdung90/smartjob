(function($){
	JobEngine.Views.SignUp = Backbone.View.extend({
		el		: '#page-signup',
		resume	: [],
		events	: {
			'click .signup-step1 .signup' 			: 'signup_step1',
			'submit form#signup'					: 'signup_step1',
			'click .signup-step1 .signup-update' 	: 'signup_update',
			'click .signup-step2 .btn-signup' 		: 'signup_step2',
			'click .step-1'		  			 		: 'return_step1',

			'click #add_more_school'    			: 'saveEducation',
			'click #add_more_experience'			: 'saveExperience',

			'submit #form_education' 				: 'saveEducation',
			'submit #form_experience' 				: 'saveExperience',
			'change .position select'				: 'saveJobPosition',
			'click .available input'				: 'saveAvailable',
			'submit .skill form '					: 'saveSkills',
			'keyup 	.skill input'					: 'addSkill',
			'click 	#add_skill '					: 'addSkill',
			'click #linkleIn'						: 'importFromLinkleIn',
			'click .forgot-pass-link'				: 'triggerForgotPass' ,

			'change #et_profession_title'			: 'jobTitleLength',
			'keyup #et_profession_title'			: 'jobTitleLength',
			'change .custom-fields input,textarea'	: 'saveCustomFiedls' ,
			'click .skill-list .delete'				: 'updateResumeData'

		},

		initialize : function() {
			_.bindAll(this , 'responseDoAuthLogin', 'jobseekerChange' , 'responseDoAuth', 'signup_step2');

			this.use_captcha	=	et_globals.use_captcha;

			//JobEngine.app.currentUser	=	new JobEngine.Models.JobSeeker();
			if(JobEngine.app.currentUser.get('id') === 'undefined')
				JobEngine.app.auth			=	new JobEngine.Models.JobSeeker();
			else 
				JobEngine.app.auth			=	new JobEngine.Models.JobSeeker(JobEngine.app.currentUser.attributes);

			JobEngine.app.currentUser.on('change', this.jobseekerChange(), this);

			// pubsub.on ('je:request:waiting', this.waitingDoAuth);
			pubsub.on ('je:response:auth_et_login', this.responseDoAuthLogin ,this);
			pubsub.on ('je:response:auth', this.responseDoAuth ,this);

			this.educationViews 	= [];
			this.experienceViews 	= [];
			this.jobPositionViews 	= [];
			this.availableViews		= [];
			this.skillViews 		= [];

			this.jobPositionSize =	$('#form_resume_category select option').size();

			this.resume	=	new JobEngine.Models.Resume();

			this.renderView ();

			/**
			 * form validate
			*/
			$("form#signup").validate({
			 	rules: {
			 		user_name : {
			 			required: true,
			 			usernameCheck: true,
			 			remote : et_globals.ajaxURL+'?action=et_username_check_used'
			 		},
				    user_pass: "required",
				    password_again: {
	      				equalTo: "#user_pass"
	    			},
	    			user_email : {
	    				required: true,
			 			remote : et_globals.ajaxURL+'?action=et_email_check_used'
	    			}
	    		},
	    		messages : {
					user_name : {
						usernameCheck : et_globals.err_invalid_username,
						remote : et_signup.username_exist
					},
					user_email : {
						remote :  et_signup.email_exist +''	
					}
				}
		    });
			this.data_skills	=	JSON.parse($('#data_skills').html());
		    // autocomplete
			$('.skill-input').autocomplete({source: this.data_skills });

		},

		renderView : function () {
			var view	=	this;
			this.addMoreSchool ();
			this.addMoreExperience();
			if( typeof IN !== 'undefined')
				IN.Event.on(IN, "auth", function() {view.onLinkedInLogin();});
	  		//IN.Event.on(IN, "logout", function() {view.onLinkedInLogout();});

	  		view.skill_list = $('#form_skills > ul.skill-list');
	  		view.positions_list = $('#form_resume_categories ul.skill-list');
		},

		styleSelect : function(){
			this.$(".select-style select").each(function(){
				var title = $(this).find('option:selected').html();
				var arrow = "";
				var container = $(this).parent();

				container.children('span.select').remove();

				$(this)
					.css({'z-index':10,'opacity':0,'-khtml-appearance':'none'})
					.after('<span class="select">' + title + arrow + '</span>')
					.change(function(){
						val = $('option:selected',this).text() + arrow;
						$(this).next().text(val);
						});

				$(this).parent().addClass('styled');
			});
		},

		importFromLinkleIn : function (e) {
			IN.User.authorize(); return false;
		},

		onLinkedInLogin : function () {
			var view	=	this;
			IN.API.Profile("me")
		    .fields(["id","headline", "firstName", "lastName", "pictureUrl", "publicProfileUrl","location", "summary","email-address", "positions", "educations", "skills"])
		    .result(function(result) {
		    	var blockUI	=	new JobEngine.Views.BlockUi();
		    	blockUI.block($('form#signup'));
		        view.pickUpLinkleIn(result.values[0]);
		        blockUI.unblock();
		    })
		    .error(function(err) {
		       alert(err);
		    });
		},

		pickUpLinkleIn : function (data) {
			var view =	this,
				startDate	=	'',
				endDate		=	'';

			if(data.hasOwnProperty('publicProfileUrl') ) {
				JobEngine.app.auth.setLinkleInProfileUrl(data.publicProfileUrl);
			}

			if(data.hasOwnProperty('pictureUrl') ) { 
				JobEngine.app.auth.setAvatar(data.pictureUrl);
			}

			if(data.hasOwnProperty('location') ) {
				$('#et_location').val(data.location.name);
			}
			if(data.hasOwnProperty('emailAddress') ) {
				$('#user_email').val(data.emailAddress);
			}
			if(data.hasOwnProperty('summary') && typeof data.summary !== 'undefined' ) {
				$('#description').val(data.summary);
			}
			if(data.headline !== 'undefined') {
				$('#et_profession_title').val(data.headline).change();
			}
			var name	=	'';
			if(data.hasOwnProperty('firstName') ) name	+=	data.firstName;
			if(data.hasOwnProperty('lastName') ) name	+=	' '+data.lastName;
			$('#display_name').val(name);
			/**
			 * educations from linkleIn
			*/
			if(data.hasOwnProperty('educations') && data.educations.hasOwnProperty('values') ) {
				view.educationViews	=	[];
				$('#inline_edu').html('');
				_.each(data.educations.values, function (element, index) {
					if(element.hasOwnProperty('startDate')) {
						startDate =	element.startDate.year;
					}
					if(element.hasOwnProperty('endDate') ) {
						endDate =	element.endDate.year;
					}
					var s_view = new JobEngine.Views.EducationView({data: {
						fromMonth : '', fromYear : startDate,
						toMonth: '', toYear : endDate, to : { year : endDate} , from : { year : startDate} ,
						name: element.schoolName , current : ''
					}});

					view.addSchool (s_view);
					s_view.validate ();
					startDate	=	'',
					endDate		=	'';
					//view.saveEducation ();
				});
			}

			/**
			 * experience from linkleIn
			*/
			if( data.hasOwnProperty('positions') && data.positions.hasOwnProperty('values') ) {
				view.experienceViews	=	[];
				$('#inline_exper').html('');
				_.each(data.positions.values, function (element, index) {
					if(element.hasOwnProperty('startDate')) {
						startDate =	element.startDate.year;
					}
					if(element.hasOwnProperty('endDate') ) {
						endDate =	element.endDate.year;
					}
					var exp_view = new JobEngine.Views.ExperienceView({data: {
						fromMonth : '', fromYear : startDate,
						toMonth: '', toYear : endDate, to : { year : endDate} , from : { year : startDate} ,
						name: element.company.name , position : element.title , current : ''
					}});
					view.addExperience(exp_view);
					exp_view.validate ();
					//view.saveExperience ();
					startDate	=	'',
					endDate		=	'';
				});
			}

			if(data.hasOwnProperty('skills') && data.skills.hasOwnProperty('values') ) {

				$('form#skill').html('');
				_.each(data.skills.values, function (element, index) { 
					var taxView = new JobEngine.Views.EditedTaxonomyItem({name : element.skill.name }, $('#edit_skill_item').html());
					view.skill_list.append( taxView.render().$el );
				});

			}

		},

		setJobseekerData : function () {
			var $form 	= $('form#signup');
			$form.find('input,select,textarea').each (function (){
				JobEngine.app.auth.set($(this).attr('name'), $(this).val() );
				if( $(this).attr('type') == 'checkbox' ) {
					JobEngine.app.auth.set($(this).attr('name'), [] );
				}
			});

			if(this.useCaptcha) {
				JobEngine.app.auth.set('recaptcha_response_field' , Recaptcha.get_response() );
				JobEngine.app.auth.set('recaptcha_challenge_field', Recaptcha.get_challenge() );
			}

			$form.find('input[type="checkbox"]:checked').each (function (){
				var a	=	JobEngine.app.auth.get( $(this).attr('name') );
				a.push($(this).val());
				JobEngine.app.auth.set( $(this).attr('name'), a );
			});

		},

		jobseekerChange : function () {

			// JobEngine.app.auth			=	_.clone (this.jobseeker);
			// JobEngine.app.currentUser	=   _.clone (this.jobseeker);
		},

		jobTitleLength : function (e) {
			var $target	=	$(e.currentTarget),
				str		=	$target.val();
			if(str.length  > 150) {
				$target.val (str.substring(0,149));
				return false;
			}
			$target.parents('.input-area').find('span').html(150 - str.length);
		},

		signup_step1 : function (e) {

			var view 	= this,
				$target	= $(e.currentTarget),
				$form 	= $('form#signup');

			if( $form.valid() ) {

				JobEngine.app.auth.setUserName($form.find('#user_name').val());
				this.setJobseekerData ();

				JobEngine.app.auth.doAuth('register', {
					// renew_logo_nonce:true,
					beforeSend	: function(){
						view.loadingBtn = new JobEngine.Views.LoadingButton({el : $target});
						view.loadingBtn.loading();
						//view.trigger('waitingAuth', $container.find('button[type=submit]'));
					},
					success	: function(data,status,jqXHR){
						view.loadingBtn.finish();
						if(data.status) {
							//view.return_step2 ();
							$('#user_name').attr('disabled', 'disabled').css('background', '#F7EFEF');
							$('.step-1').removeClass ('active');
							JobEngine.app.auth.set('id', data.data.ID);
							JobEngine.app.auth.set('ID', data.data.ID);
							JobEngine.app.currentUser	=	new JobEngine.Models.JobSeeker(JobEngine.app.auth.attributes);
						}else {
							if(view.use_captcha) {
								if(typeof  Recaptcha != 'undefined')
									Recaptcha.reload();
							}
						}
					}
				} );

			} else {
				if(this.use_captcha) {
					if(typeof  Recaptcha != 'undefined')
					Recaptcha.reload();
				}
			}
		},

		signup_update : function (e) {

			var view 	= this,
				$target	= $(e.currentTarget),
				$form 	= $('form#signup');

			if($form.valid()) {
				this.setJobseekerData ();
				JobEngine.app.auth.sync('update', JobEngine.app.auth, {
					// renew_logo_nonce:true,
					beforeSend	: function(){
						view.loadingBtn = new JobEngine.Views.LoadingButton({el : $target});
						view.loadingBtn.loading();
						//view.trigger('waitingAuth', $container.find('button[type=submit]'));
					},
					success	: function(data,status,jqXHR){
						view.loadingBtn.finish();
						if(data.success) {
							//view.return_step2 ();
							$('#user_name').attr('disabled', 'disabled').css('background', '#F7EFEF');
							JobEngine.app.currentUser	=	new JobEngine.Models.JobSeeker(JobEngine.app.auth.attributes);
							view.return_step2();
							window.scrollTo (0,$('.jse-title').offset().top-74 );
						}
					}
				});
			}

		},

		signup_step2 : function (e) {
			var $target			=	$(e.currentTarget),
				data	=	[],
				view			=	this;
			view.loadingBtn	=	new JobEngine.Views.LoadingButton({el : $target});

			view.skill_list.find('input[type=hidden]').each (function () {
				view.skillViews.push($(this).val());
			});
			this.resume.set('skill', view.skillViews);

			view.positions_list.find('input').each (function () {
				view.jobPositionViews.push($(this).val());
			});
			this.resume.set('resume_category', view.jobPositionViews);

			_.each(this.experienceViews, function(elements, index){
				var object 		= _.clone(elements.toObject());
				if (!elements.validate())
					validate = false;
				else {
					data.push(object);
				}
			});

			this.resume.set ('et_experience', data);

			data = [];
			_.each(this.educationViews, function(elements, index){
				var object 		= _.clone(elements.toObject());
				if (!elements.validate())
					validate = false;
				else {
					data.push(object);
				}

			});

			this.resume.set ('et_education', data);

			//this.resume.set('title', JobEngine.app.auth.getProTitle());
			this.resume.set('post_author', JobEngine.app.currentUser.get('ID'));
			this.resume.set('post_content', JobEngine.app.auth.get('description'));
			this.resume.set('et_location', JobEngine.app.auth.get('et_location'));
			this.resume.set('et_profession_title', JobEngine.app.auth.get('et_profession_title'));
			this.resume.save(this.resume.attributes,  {
				beforeSend : function () {
					view.loadingBtn.loading();
				},
				success : function (res) {
					view.loadingBtn.finish();
					window.location.reload();
				}
			});
		},

		addMoreSchool : function(event){
			var that	=	this;
			var view = new JobEngine.Views.EducationView({data: {
					fromMonth : '', fromYear : '',
					toMonth: '', toYear : '',
					name: '', current : '' ,degree: '',
					from: { month: '', year: '' },
					to: { month: '', year: '' }
							}
						});
			this.addSchool (view);
		},

		removeSchool : function (view) {
			var that =  this;
			_.each(that.educationViews, function(element, index){
				if(view.cid == element.cid) {
					element.remove();
					that.educationViews.splice(index, 1);
				}
			});
		},

		addSchool : function (view) {
			view.onDelete = this.removeSchool ;
			this.educationViews.push(view);

			$('#inline_edu').append( view.render().$el );

			this.styleSelect();
		},

		saveEducation : function(event){
			event.preventDefault();
			 var data = [], view = this, valid= true;

			// $('#school_list').html ('').removeClass ('edu-module clearfix');
			_.each(this.educationViews, function(element, index){
				// push education data in object
				var object		=	_.clone (element);
				var validate	=	object.validate();
				if( validate ){
					//valid = true;
				} else {
					valid	=	false;
				}
			});

			if(valid) {
				view.addMoreSchool();
			}

		},

		addMoreExperience : function (event) {
			var that	=	this;
			var exp_view = new JobEngine.Views.ExperienceView({data: {
				fromMonth : '', fromYear : '', from : '',
				toMonth: '', toYear : '',
				name: '' , position : '',  current : '' , to : ''
			} /*, onDelete : that.removeExperience*/
			 });
			this.addExperience (exp_view);
		},

		removeExperience : function (view) {
			var that =  this;
			_.each(that.experienceViews, function(element, index){
				if(view.cid == element.cid) {
					element.remove();
					that.experienceViews.splice(index, 1);
				}
			});
		},
		addExperience : function (exp_view) {
			exp_view.onDelete = this.removeExperience ;
			this.experienceViews.push(exp_view);
			$('#inline_exper').append( exp_view.render().$el );
			// $('#form_experience .edu-form.save-btn').show();
			this.styleSelect();
		},

		saveExperience	  : function (event) {
			event.preventDefault();
			var data = [], view = this, valid	=	true;

			_.each(this.experienceViews, function(elements, index){
				var validate	=	elements.validate();
				if(validate) {
				} else {
					valid = false;
				}

			});
			if(valid) view.addMoreExperience();
		},

		saveJobPosition : function (event) {
			var $target		= $(event.currentTarget),
				val 		= $(event.currentTarget).val(),
				container	= $target.parents('.module');

			if(val == '') return;
			var duplicates 	= container.find('input[type=hidden][value="' + val + '"]');

			if (duplicates.length > 0){ alert(et_resume.duplicate_resume_category); };

			if ( duplicates.length == 0 ){
				var tempName = $(event.currentTarget).find('option:selected').text(),
					data = { 'term_id' : val, 'name' : $.trim(tempName)  },
					taxView = new JobEngine.Views.EditedTaxonomyItem(data, $('#edit_position_item').html());

				container.find('ul.skill-list').append( taxView.render().$el );

				var positions = container.find('input[type=hidden]').filter(function(){
					return $(this).val() != '';
				}).map(function(){
					return $(this).val();
				}).get();

				this.resume.set( container.attr('data-resume') , positions);
			}
		},

		saveAvailable : function (e) {
			var view		=	this,
				$target		=	$(e.currentTarget),
				container	=	$target.parents('.module');
			view.availableViews	=	[];
			container.find('input[type="checkbox"]:checked').each (function () {
				view.availableViews.push($(this).val());
			});

			this.resume.set( container.attr('data-resume'), view.availableViews,  {silent: true});

		},

		updateResumeData : function (event) {
			var $target		= $(event.currentTarget),
				container	= $target.parents('.module');

			$target.parents('li').remove();

			var positions = container.find('input[type=hidden]').filter(function(){
					return $(this).val() != '';
				}).map(function(){
					return $(this).val();
				}).get();

			this.resume.set( container.attr('data-resume') , positions );
		},

		saveCustomFiedls : function (event) {
			var $target	=	$(event.currentTarget);
			this.resume.set( $target.attr('data-resume'), $target.val(),  {silent: true});
		},

		saveSkills : function (e) {
			e.preventDefault ();
		},

		addSkill : function (event) {

			var $target		= $(event.currentTarget),
				val 		= $target.val(),
				view		= this,
				container	= $target.parents('.module');

			var duplicates 	= container.find('input[type=hidden][value="' + val + '"]');
			view.skillViews	=	view.resume.get ( container.attr('data-resume') );
			if ( event.which == 13  ){
				if(val != '' && duplicates.length == 0) {
					var data = { 'name' : val };
					var taxView = new JobEngine.Views.EditedTaxonomyItem(data, $('#edit_skill_item').html());
					container.find('ul.skill-list').append( taxView.render().$el );
					// view.skillViews.push( val );

					var positions = container.find('input[type=hidden]').filter(function(){
						return $(this).val() != '';
					}).map(function(){
						return $(this).val();
					}).get();

					this.resume.set( container.attr('data-resume') , positions);

				}

				$target.val('');
			}

			return event.which != 13;
		},

		revertPreviousJobPosition : function (e) {
			var $target	=	$(e.currentTarget);
			if(this.jobPositionViews.indexOf($target.val()) > 0 )  {
				$target.val($target.attr('data')).change();

			}
		},

		return_step1 : function (e) {
			if($('.signup-step1').hasClass('hidden')) {
				$('.signup-step1').toggleClass('hidden');
				$('.step-1').toggleClass('active');
				$('.signup-step2').toggleClass('hidden');
				$('.step-2').toggleClass('active');
			}
		},

		return_step2 : function (e) {
			$('.signup-step1').toggleClass('hidden');
			$('.step-1').toggleClass('active');
			$('.signup-step2').toggleClass('hidden');
			$('.step-2').toggleClass('active');
		},

		responseDoAuthLogin : function (data, status, jqXHR) {
			if(et_globals.page_template == 'page-jobseeker-signup.php') {
				window.location.reload();
			}
		},

		responseDoAuth : function (data, status, jqXHR) {
			var view 	=	this,
				$form 	= $('form#signup');

			if(data.status) {
				this.return_step2();
				window.scrollTo(0, $('.jse-title').offset().top-74 );
				view.$el.find('button.signup').addClass('signup-update').removeClass('signup');
			}
		},

		triggerForgotPass : function (e) {
			e.preventDefault();
			pubsub.trigger('je:request:forgot_pass');
		}

	});

	$(document).ready (function() {
		JobEngine.Resume_signup	=	new JobEngine.Views.SignUp ();
	});

})(jQuery);

