(function ($) {

	var signUp	=	{
		user : {
			action  				: 'et_register' ,
			user_name 				: '',
			display_name 			: '',
			et_profession_title 	: '',
			user_email				: '',
			et_location 			: '',
			description 			: '',
			user_pass				: '',
			password_again			: '',
			role					: 'jobseeker'
		},

		updateUserData : function ( name, value ) {
			this.user[name]	=	value;
		},

		sync : function () {

			var view = this;
			if(view.user.user_pass !== view.user.password_again) {
				alert('Password miss match');
			}

			if( view.user.action == 'et_register' ) {
				$.ajax ({
					url : et_globals.ajaxURL,
					type : 'post',
					data : view.user,
					beforeSend : function () {
						$.mobile.showPageLoadingMsg();
					},
					success : function (res) {
						$.mobile.hidePageLoadingMsg();
						if(res.status) {
							alert (res.msg);
							$('#step-1').remove();
							$('#step-2').show();
							view.user.ID = res.data.ID;
						} else {
							alert (res.msg);
							if(et_globals.use_captcha)
								Recaptcha.reload();
						}
					}
				});

			}else {
				view.user.id	=	view.user.ID;
				$.ajax ({
					url : et_globals.ajaxURL,
					type : 'post',
					data :  {
						content : {
							id							: view.user.ID,
							ID							: view.user.ID,
							display_name 				: view.user.display_name,
							et_profession_title 		: view.user.et_profession_title,
							//description 				: view.user.description,
							et_location 				: view.user.et_location,
						},
						action  : view.user.action,
						method  : 'update'
					},
					beforeSend : function () {
						$.mobile.showPageLoadingMsg();
					},
					success : function (res) {
						$.mobile.hidePageLoadingMsg();
						if(res.success) {
							alert (res.msg);
							// $('#step-1').remove();
							// $('#step-2').show();
							//view.user.ID = res.data.ID;
						} else {
							alert (res.msg);
						}
					}
				});
			}
		}

	}

	var resume = {

		data : {
			post_author 		: '' ,
			post_content 		: '',
			et_location 		: '',
			et_profession_title : '',
			et_experience		: [],
			et_education		: [],
			resume_category 	: [],
			available			: [],
			skill				: [],
			action 				: 'et_resume_sync'
		},

		sync 	: function () {
			var view = this;
			var method	=	'create';
			if( typeof this.data.ID  !== 'undefined' ) {
				var method	=	'update';
			}

			$.ajax ({
				url : et_globals.ajaxURL,
				type : 'post',
				data :  {
					content :  view.data,
					action  : 'et_resume_sync',
					method	: method
				},

				beforeSend : function () {
					$.mobile.showPageLoadingMsg();
				},
				success : function (res) {
					$.mobile.hidePageLoadingMsg();
					if( res.success )  {
						view.data.ID = res.data.resume.ID;
						$.mobile.changePage( res.data.resume.permalink );
					} else {
						alert ( res.msg );
					}
				}
			});
		},

		validate : function () {
			return true;
		},

		updateUserData : function  () {
			var view 						=	this;
			this.data.post_author			=	signUp.user.ID;

			if(signUp.user.et_profession_title != '' )	this.data.et_profession_title	=	signUp.user.et_profession_title;
			if(signUp.user.et_location != '' )			this.data.et_location			=	signUp.user.et_location;

			this.data.post_content			=	signUp.user.description;

			$('.textarea').each( function() {
				var element	=	$(this).find('textarea,input'),
					name	=	element.attr('data-resume'),
					value	=	element.val();

				view.data[name]	=	value;
			});
		},

		updateEdu : function () {
			var container	=	$('.et_education'),
				view		=	this,
				edu			=	[];

			container.find('.education').each(function () {
				var temp	=	{},
					element	=	$(this);
				temp.name 		= element.find('input.name').val();
				temp.degree 	= element.find('input.degree').val();
				temp.fromMonth  = element.find('select.fromMonth').val();
				temp.fromYear 	= element.find('select.fromYear').val();

				temp.from		= {},
				temp.to			= {},

				temp.from.month	=  temp.fromMonth;
				temp.from.year	=  temp.fromYear;

				temp.toMonth 	= element.find('select.toMonth').val();
				temp.toYear 	= element.find('select.toYear').val();

				temp.to.month	=  temp.toMonth
				temp.to.year	=  temp.toYear

				temp.current 	= element.find('input.curr').is(':checked') ? 1 : 0;
				edu.push(temp);
			});

			view.data['et_education']	=	_.clone(edu);

		},

		updateExp : function () {
			var container	=	$('.et_experience'),
				view		=	this,
				exp			=	[];

			container.find('.experience').each(function () {
				var temp	=	{},
					element	=	$(this);
				temp.from		= {},
				temp.to			= {},

				temp.name 		= element.find('input.name').val();
				temp.position 	= element.find('input.position').val();
				temp.fromMonth  = element.find('select.fromMonth').val();
				temp.fromYear 	= element.find('select.fromYear').val();
				temp.from.month	=  temp.fromMonth;
				temp.from.year	=  temp.fromYear;

				temp.toMonth 	= element.find('select.toMonth').val();
				temp.toYear 	= element.find('select.toYear').val();
				temp.to.month	=  temp.toMonth
				temp.to.year	=  temp.toYear

				temp.current 	= element.find('input.curr').is(':checked') ? 1 : 0;
				exp.push(temp);
			});

			view.data['et_experience']	=	_.clone(exp);

		},

		updateCat : function () {

			var view	=	this;
			$('.category').each(function () {
				var container	=	$(this),
					data_name	=	container.attr('data-resume'),
					select		=	container.find('select');
				view.data[data_name]	=	select.val();
			});

		},

		updateAvai : function () {
			var view	=	this;
			$('.available').each(function () {
				var container	=	$(this),
					data_name	=	container.attr('data-resume'),
					select		=	container.find('select');
				var available 	=	container.find('input:checked').map(function(){ return $(this).val(); } ).get();
				view.data[data_name]	=	_.clone(available)	;
			});
		},

		updateSkill : function () {
			var view	=	this;
			$('.skill-container').each(function () {
				var container	=	$(this),
					data_name	=	container.attr('data-resume'),
					select		=	container.find('select');
				var skill 	=	container.find('input.skill').map(function(){ return $(this).val(); } ).get();
				view.data[data_name]	=	_.clone(skill)	;
			});
		},

		validate	: function (container) {
			var a		=	false, errors = [];
			container.find('.element').each (function () {
				var element		=	$(this),
					fromYear	=	element.find('select.fromYear').val(),
					fromMonth	=	element.find('select.fromMonth').val(),

					toYear	=	element.find('select.toYear').val(),
					toMonth	=	element.find('select.toMonth').val(),

					fromDate 	= new Date(fromYear, fromMonth),
					toDate 		= new Date(toYear, toMonth),
					current 	= element.find('input.curr').is(':checked') ? 1 : 0;


				if(element.find('.name').val() == '' && element.hasClass('education')) {
					a = true;
					errors.push({ 'msg' :  et_resume.school_name_invalid  });
					return errors;
				}


				if(element.find('.name').val() == '' && element.hasClass('experience')) {
					a = true;
					errors.push({ 'msg' :  et_resume.company_name_invalid  });
					return errors;
				}

				if(element.find('.position').length > 0 && element.find('.position').val() == '') {
					a = true;
					errors.push({ 'msg' : et_resume.position_invalid });
					return errors;
				}

				// checking
				if ( fromYear == '' ) {
					a = true;
					errors.push({ 'msg' : et_resume.from_date_invalid });
					return errors;
				}
					//return this.errorResp("From year is missing");

				if ( toYear == '' && !current) {
					a = true;
					errors.push({  'msg' : et_resume.to_date_invalid });
					return errors;
				}

				if ( ( parseInt(toYear) < parseInt(fromYear) && !current ) || (toDate < fromDate && !current && toMonth != '' && toYear != '' ) ){
					errors.push({  'msg' : et_resume.date_range_invalid });
					a	=	true;
					return errors;
				}

			}) ;

			if(a) {
				return errors;
			}

			return false;
		}

	}
	$(document).on('click', '.icon-track', function(event){
		event.preventDefault;
	  	var $target		= $(event.currentTarget);
		$target.closest(".element").remove();

	});

	$(document).on('pageinit' , function () {
		_.templateSettings = {
		    evaluate    : /<#([\s\S]+?)#>/g,
			interpolate : /\{\{(.+?)\}\}/g,
			escape      : /<%-([\s\S]+?)%>/g
		};

		if( $('.post_author').length > 0 && $('.post_author').val() != '' ) {
			signUp.user.ID		=	$('.post_author').val();
			signUp.user.action  = 'et_jobseeker_sync'
			//resume.data.ID	=	967;
		}
		/**
		 * submit jobseeker signup
		*/
		$('#jobseeker_signup').on('submit' , function (e) {
			e.preventDefault();
			$('#jobseeker_signup input').each(function () {
				var $target = $(this),
					name	= $target.attr('name'),
					value	= $target.val();
				signUp.updateUserData (name, value);
			});
			signUp.sync();
		});


		/**
		 * add more school to jobseeker view
		*/
		$('.add_more_school').on('click' , function (e) {
			var $target 	= $(e.currentTarget),
				$container	= $target.parents('.et_education').find('.edu-container'),
				template	= _.template( $('#education_template').html() ),
				i			= $('.education').length + 1;

			var validate	= _.clone( resume.validate ($container) );
			if(!validate ) {
				$container.append(template({i : i}));
				$container.trigger('create');
			} else {
				alert (validate[0].msg);
			}

		});

		/**
		 * add more position to jobseeker view
		*/
		$('.add_more_exp').on('click' , function (e) {
			var $target 	= $(e.currentTarget),
				$container	= $target.parents('.et_experience').find('.exp-container'),
				template	= _.template( $('#exp_template').html() ),
				i			= $('.experience').length + 1;

			var validate	= _.clone( resume.validate ($container) );
			if(!validate ) {
				$container.append(template({i : i}));
				$container.trigger('create');
			} else {
				alert (validate[0].msg);
			}

		});


		$('.skill').on('keyup', function (event) {
			var $target		= $(event.currentTarget),
				val 		= $target.val(),
				container	= $target.parents('.skill-container');

			var duplicates 	= container.find('input[type=hidden][value="' + val + '"]');
			if ( event.which == 13  ){
				if(val != '' && duplicates.length == 0) {
					var taxView 	= _.template( $('#skill_template').html() );
					container.find('ul.skill-list').append( taxView({val : val }) );
					// view.skillViews.push( val );
				}

				$target.val('');
			}

			return event.which != 13;
		});

		$('.sign_up').on('click' , function (e) {
			e.preventDefault();
			var $target		= $(e.currentTarget);

			resume.updateUserData();
			resume.updateEdu();
			resume.updateExp();
			resume.updateCat();
			resume.updateAvai();
			resume.updateSkill();

			resume.sync();

		});

		$('.element select').on('change' , function (event) {
			var $target		= $(event.currentTarget),
				$container	= $target.parents('.content-info'),
				validate	= _.clone( resume.validate ($container) );

			if(!validate ) {
			} else {
				alert (validate[0].msg);
			}
		});

	});

})(jQuery);