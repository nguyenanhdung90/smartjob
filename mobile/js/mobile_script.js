// initialize variables
var query_default = {
	action		: 'et_fetch_jobs',
	method		: 'read',
	content : {
		paged		: 1,
		author		: '',
		job_type	: '',
		job_category : '',
		s			: '',
		location	: ''
	}
};
var blog_page	=	1;
var resume_params = {
	paged 		: 1,
	rq 			: '',
	et_location : '',
	available 	: ''
};
var enable_ajax = true;
var flag_lm = 0;



(function($){

	$(document).on('pageinit',function() {

		$('div[data-role="page"]').on('pagehide', function (event, ui) {
		    $(event.currentTarget).remove();
		});
		
		$('#jobseeker_apply_form').on('submit', function (e) {
			e.preventDefault();
			var $apply_form	=	$(e.currentTarget);

			if( $apply_form.find('#jobseeker_apply').length > 0 ) {
				//alert(1);
				var form_data	= {
						action		: 'et_jobseeker_apply_job',
						job_id		: $('#job_id').val(),
						apply_name	: $apply_form.find('input#emp_name').val(),
						apply_email	: $apply_form.find('input#emp_email').val(),
						apply_note	: $apply_form.find('textarea#apply_note').val(),
						jobseeker_id : $('#jobseeker_id').val(),
						//attachments	: this.fileIDs,
						_ajax_nonce	: $apply_form.find('#_ajax_nonce').val()
					}
					;
				
				if(et_globals.use_captcha) {
					form_data['recaptcha_response_field'] 	= Recaptcha.get_response();
					form_data['recaptcha_challenge_field'] 	= Recaptcha.get_challenge();
				}

				$.ajax({
					type		: 'post',
					url			: et_globals.ajaxURL,
					data		: { action : 'et_jobseeker_apply_job' , content : $apply_form.serialize() },
					//contentType	: 'application/x-www-form-urlencoded;charset=UTF-8',
					beforeSend: function(){
						$.mobile.showPageLoadingMsg();
					},
					success	:  function(res){
						$.mobile.hidePageLoadingMsg();
						alert(res.msg);
						if(et_globals.use_captcha)
							Recaptcha.reload();
					}
				});
			}else {
				var form_data	= {
						action		: 'et_apply_job',
						job_id		: $('#job_id').val(),
						apply_name	: $apply_form.find('input#emp_name').val(),
						apply_email	: $apply_form.find('input#emp_email').val(),
						apply_note	: $apply_form.find('textarea#apply_note').val(),
						//jobseeker_id : $('#jobseeker_id').val(),
						//attachments	: this.fileIDs,
						_ajax_nonce	: $apply_form.find('#_ajax_nonce').val()
					}
					;
				if(et_globals.use_captcha) {
					form_data['recaptcha_response_field'] 	= Recaptcha.get_response();
					form_data['recaptcha_challenge_field'] 	= Recaptcha.get_challenge();
				}
					// if( $apply_form.valid() )
				$.ajax({
					type		: 'post',
					url			: et_globals.ajaxURL,
					data		: form_data,
					//contentType	: 'application/x-www-form-urlencoded;charset=UTF-8',
					beforeSend: function(){
						$.mobile.showPageLoadingMsg();
					},
					success	:  function(res){
						$.mobile.hidePageLoadingMsg();
						if(res.success)
							alert(res.mobile_msg);
						else 
							alert(res.msg);

						if(et_globals.use_captcha)
							Recaptcha.reload();
					}
				});
			}
			return false;

		});


		/**
		 * Add function auto hint search in input search location   
		 */                                                                  
	    $('#load-more-post').on('click', function (event) {
	    	event.preventDefault ();
			var $target			=	$(event.currentTarget),
				$template		=	$target.parents('.button-more').find('input#template'),
				$list_payment	=	$('.list-blog ul'),
				page			=	blog_page + 1;

			
			$.ajax ({
				url : et_globals.ajaxURL,
				type : 'post',
				data : {
					page			: page,
					action			: 'et-mobile-load-more-post',
					template_value	: $template.val(),
					template		: $template.attr('name')
				},
				beforeSend : function () {
					blog_page ++ ;
					$.mobile.showPageLoadingMsg();
				},
				success : function (response) {
					$.mobile.hidePageLoadingMsg();
					if(response.success) {
						$list_payment.append (response.data);
						
						if( blog_page >= response.total ){
							$target.parents('.load-blog').hide ();
						}

					}else {
						
						$target.parents('.load-blog').hide ();
						blog_page--;

					}
				}
			});
	    });

		var current_url = window.location;
		var location_url = $.mobile.path.parseUrl(current_url).search;
		if ( location_url.indexOf('location') >= 0) {
			location_url = decodeURIComponent(location_url).replace("?location=","");
		 	$('.ui-page-active').find('#search_location').val(location_url);
		 	query_default.content.location = location_url;
		}
		
		var job_type_url =  $.mobile.path.parseUrl(current_url).pathname;
		if ( job_type_url.indexOf('job-type') >= 0) {
			var temp = job_type_url.split("/");
			job_type_url = temp[temp.length - 2];
			$('.ui-page-active').find('.contact-type a[data='+job_type_url+']').addClass('ui-list-active');
			query_default.content.job_type = job_type_url;
		}
		/**
		 * Search by keyup event - delay time 1s
		 */
		var timer = null;
		$('.txt_search').on('keyup',function(){
			if ( timer !== null ){
				clearTimeout(timer);
			}
			timer = setTimeout(function(){
				var temp = $('.ui-page-active').find('#txt_search').val();
				query_default.content.s = temp.replace(/[`~!\^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, '');
				query_default.content.paged = 1;
				$.queryItems_default(true);
			}, 500);
		});


		/**
		 * Touch login button
		 */
		$('.et_login').on('tap',function(){
			$.loginUser();
		});
		$(".forgot-password").on('tap',function(){
			$('form.login').slideUp();
			$('form.forgot-password').slideDown();
		});


		/**
		 * Touch logout button
		 */
		$('.et_logout').on('tap',function(){
			$.logoutUser();
		});

		/**
		 * Touch Load more button in homepage
		 */
		$('.ui-page-active #et_loadmore').on('tap',function(){
			if(enable_ajax ){
				enable_ajax  = false;
				var temp     = $('ui-page-active li.list-divider').length;
				var cur_page = parseInt($('.ui-page-active').find('#cur_page_index').val());
				//alert(temp);
				var txt_search = $('.ui-page-active').find('#txt_search');
				var temp       = txt_search.length > 0 ? txt_search.val() : "";
				var out_string = temp.replace(/[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, '');
				query_default.content.paged = cur_page + 1 ;
				if( out_string != ''){
					query_default.content.s = out_string;
				}
				$.queryItems_default(false);
				$('.ui-page-active').find('#cur_page_index').val(query_default.content.paged);
			}
		});

		$('.ui-page-active #lm_com_job').on('tap',function(){
			if( enable_ajax ){
				enable_ajax = false;
				var max_page_com = $('.ui-page-active').find('#max_page_com').val();
				var cur_page     = parseInt($('.ui-page-active').find('#cur_page').val());
				if( cur_page < max_page_com){
					query_default.content.author       = $('.ui-page-active').find('#company').val();
					query_default.content.paged        = cur_page + 1 ;
					query_default.content.job_type     = '';
					query_default.content.job_category = '';
					query_default.content.s            = '';
					query_default.content.location     = '';
					$.queryItems_default (false);
					$('.ui-page-active').find('#cur_page').val(query_default.content.paged);
				}
				else return false;
			}
			//}else return false;
		});

		/**
		 * Resume homepage
		 */ 

		 // load more resumes
		$('.ui-page-active #loadmore_resume').on('tap', function(){
			if(enable_ajax ){

				resume_params.paged++;
				$.searchResumes(resume_params, true);
			}
		});

		// change available, job position
		$('.pick-param').on('tap', function(event){
			var element = event.currentTarget;
			var name 	= $(element).attr('data-name');
			var value 	= $(element).attr('data');
			var param 	= {};
			param[name] = value
			$.changeParam(param);
		});

		// apply search data
		$('#apply_search_resume').on('tap', function(event){
			$.changeParam( { 'et_location':  $('input#et_location').val() });
			$.changeParam( { 'paged': 1 });

			$.searchResumes(resume_params);
		});

		var searchRQ = _.debounce(function(){
			$.changeParam( { 'rq': $('input#resume_search').val() });

			$.searchResumes(resume_params, false);
		}, 700);

		$('#resume_search').keyup(searchRQ);

		$.searchResumes = function(params, loadmore){
			if(enable_ajax ){
				enable_ajax  = false;

				params = {
					type 	: 'post',
					url 	: et_globals.ajaxURL,
					data 	: { 
						action 	: 'et_fetch_resumes',
						content : resume_params
					},
					beforeSend: function(){
						enable_ajax  = false;
						$.mobile.showPageLoadingMsg();
					},
					success: function(resp){
						// build data
						var data = [];
						_.each(resp.data.resumes, function(element, index){
							var item = element;
							var jobseeker = _.find(resp.data.jobseekers, function(jobseeker){
								return jobseeker.ID == item.post_author;
							});

							item.jobseeker_data = jobseeker;
							data.push(item);
						});

						// check if 
						if ( resume_params.paged >= resp.data.total_pages )
							$('#loadmore_resume').hide();

						// 
						$.renderResumes(data, loadmore);
					},
					complete: function(){
						enable_ajax = true;
						$.mobile.hidePageLoadingMsg();
					}
				};

				$.ajax(params);
			}
		};

		$.changeParam = function(params){
			var params = _.extend( resume_params, params );
			return params;
		}

		// add more resume items into list
		$.renderResumes = function(data, loadmore){
			var $loadmore = loadmore ? true : false;
			_.templateSettings = {
			    evaluate    : /<#([\s\S]+?)#>/g,
				interpolate : /\{\{(.+?)\}\}/g,
				escape      : /<%-([\s\S]+?)%>/g
			};
			var template = _.template( $('#template_resume').html() );

			if ( !$loadmore )
				$('.resume-listview').html('');

			_.each(data, function(element, index){
				var dom = template(element);

				$('.resume-listview').append(dom);
			});
			$('.resume-listview').listview('refresh');
		}

		/**
		 * Touch "Remind To Apply" button in single-job
		 */
		$('#et_remind_email').on('tap',function(){
			var email = $('#remind_email').val();
			if (!validateEmail(email)) {
				alert('Invalid email format');
			}
			else{
				$.remindEmailUser();
			}
		});



		/**
		 * Seperate the content of post job into 2 part : Description Job and How to apply
		 */
		$('div.content-padding').find('h6').wrap('<h3></h3>');


		/**
		 * Touch button Search to filter categories, contract type and location 
		 */ 
		$('#et_search_cat').tap(function(){
			query_default.content.paged = 1;
			// location
			var location = $('.ui-page-active').find('#search_location');
			if( location.val() != location.attr('title') )
				query_default.content.location =  location.val();
			else  query_default.content.location =  '';

			// array of job categories
			var categories_arr = [];
			$('.ui-page-active').find('div.list-categories a.ui-list-active').each(function(){
				categories_arr.push($(this).attr('data'));
			});
			query_default.content.job_category = categories_arr;

			// array of job contract types
			var contract_types_arr = [];
			$('.ui-page-active').find('div.contact-type a.ui-list-active').each(function(){
				contract_types_arr.push($(this).attr('data'));
			});
			query_default.content.job_type = contract_types_arr;
			$.queryItems_default (true);
		});

		// save location of company into cookie
		$('.ui-page-active #com_location').on('tap',function(){
			// var location = $(this).text();
			// //$.cookie("ck_location", location);
			// $.mobile.changePage( et_globals.homeURL, {
			// 	type: "post",
			// 	data: location,
			// 	reload : "true"
			// });
		});
	});


	/**
	 * delay time out for event keyup
	 */
	$.fn.delayKeyup = function(callback, ms){
		var timer = 0;
		$(this).keyup(function(){
			clearTimeout (timer);
			timer = setTimeout(callback, ms);
		});
		return $(this);
	};


	/**
	 * Handle event login feature
	 */
	$.loginUser = function(){
		var login_vars = {
			action : 'et_login',
			user_name : $('#login_username').val(),
			user_pass : $('#login_pass').val(),
			user_email : $('#login_username').val()
		};
		$.ajax({
			url  : et_globals.ajaxURL,
			type : 'post',
			data : login_vars,
			beforeSend : function(){
				$.mobile.showPageLoadingMsg();
			},
			error: function(request){
				$.mobile.hidePageLoadingMsg();
			},
			success : function(response){
				$.mobile.hidePageLoadingMsg();
				if ( response.status ){
					//$.mobile.changePage(response.data.post_url);
					if($('#redirect_url').length > 0 && $('#redirect_url').val() != '' ) {
						window.location = $('#redirect_url').val();
					} else {
						window.location = response.data.profile_url;
					}						
				}else {
					alert(response.msg);
				}
			}
		});
	};


	/**
	 * Handle event logout feature
	 */
	$.logoutUser = function(){
		$.ajax({
			url  : et_globals.ajaxURL,
			type : 'post',
			data : {
				action : 'et_logout'
			},
			beforeSend : function(){
				$.mobile.showPageLoadingMsg();
			},
			error: function(request){
				$.mobile.hidePageLoadingMsg();
			},
			success : function(response){
				$.mobile.hidePageLoadingMsg();
				if ( response.status === 200 ){
					//$.mobile.changePage(et_globals.homeURL);
					window.location = et_globals.homeURL;
				}else {
					alert(response.msg);
				}
			}
		});
	};


	/**
	 * Handle event register feature
	 */
	$.registerUser = function(){
		var register_vars = {
			action		: 'et_register',
			username	: $('#reg_username').val(),
			email		: $('#reg_email').val(),
			password	: $('#reg_pass').val(),
			retype_pass : $('#reg_retype_pass').val()
		};
		$.ajax({
			url  : et_globals.ajaxURL,
			type : 'post',
			data : register_vars,
			beforeSend : function(){
				$.mobile.showPageLoadingMsg();
			},
			error: function(request){
				$.mobile.hidePageLoadingMsg();
			},
			success : function(response){
				$.mobile.hidePageLoadingMsg();
				if ( response.success ){
					$.mobile.changePage(response.redirect_url);
				}else {
					alert(response.message);
				}
			}
		});
	};



	// $('#attachments').fileupload({
 //        dataType: 'json',
 //        done: function (e, data) {
 //            $.each(data.result.files, function (index, file) {
 //                alert(file.name);
 //            });
 //        }
 //    });

	/**
	 * Handle event remind user email
	 */
	$.remindEmailUser = function(){
		var remind_email_vars = {
			action : 'et_remind_job',
			share_email : $('#remind_email').val(),
			job_id : $('#current_job_id').val()
		};
		$.ajax({
			url  : et_globals.ajaxURL,
			type : 'post',
			data : remind_email_vars,
			beforeSend : function(){
				$.mobile.showPageLoadingMsg();
			},
			error: function(request){
				$.mobile.hidePageLoadingMsg();
			},
			success : function(response){
				$.mobile.hidePageLoadingMsg();
				if ( response.success ){
					$.modal.close.call($('#modal_remind'));
					alert($(response.msg).html());
				}else {
					alert(response.msg);
				}
			}
		});
	};
	

	/**
	 * Handle event remind user email
	 */
	$.applyJobs = function(e){
		// event.preventDefault();
		
	};


	/**
	 * query default for searching jobs and loading more jobs
	 */
	$.queryItems_default = function(flag){
		$.ajax({
			url : et_globals.ajaxURL,
			type : 'post',
			data : query_default,
			beforeSend : function(){
				$.mobile.showPageLoadingMsg();
			},
			error : function(request){
				$.mobile.hidePageLoadingMsg();
			},
			success : function(response){
				$.mobile.hidePageLoadingMsg();
				current_page = response.data.paged;
				max_page_query = response.data.total_pages;
				if(response.status){
					var count_title = 0;
					if($('.ui-page-active').find('.list-divider').length == 1  ){
						count_title = 1;
					}
					if($('.ui-page-active').find('.list-divider').length == 2 ){
						count_title = 2;
					}
					if(!flag){
						flag_lm = 0 ;
						$.render_LoadMoreJob(response.data.jobs, count_title);
						if( current_page >= max_page_query ){
							$('.ui-page-active').find('#lm_com_job').hide();
							$('.ui-page-active').find('#et_loadmore').hide();
							$(document).trigger('je_job_search_last');
						}
						else{
							$('.ui-page-active').find('#lm_com_job').show();
							$('.ui-page-active').find('#et_loadmore').show();
						}
					}
					else{
						$('.ui-page-active').find('#cur_page_index').val(query_default.content.paged);
						$('.ui-page-active').find('#et_loadmore').hide();
						var ul_html = $('.ui-page-active').find('.listview');
						ul_html.html('');
						$.render_LoadSearchJob(response.data.jobs);
						if( current_page < max_page_query && max_page_query != 0){
							$('.ui-page-active').find('#et_loadmore').show();
						}else {
							$(document).trigger('je_job_search_last');
						}
						
					}
				}
				else alert('Query error');
			}
		});
	};


	/**
	 * Render load more jobs
	 */
	$.render_LoadMoreJob = function(contents, count_title){
		var container = $('div.ui-page-active ul.listview');
		for (value in contents){
			var item = contents[value];
			if( item.featured == 0 && flag_lm == 0 && count_title == 1 ){
				var txt	=	$('.list-divider:first-child').text();
				if(txt != 'Jobs' && txt != 'JobsJobs') {
					container.append(
		 				$('<li class="list-divider">').text('Jobs')
			 		);
			 		flag_lm = 1;
			 	}
			}
			_.templateSettings = {
			    evaluate    : /<#([\s\S]+?)#>/g,
				interpolate : /\{\{(.+?)\}\}/g,
				escape      : /<%-([\s\S]+?)%>/g
			};
			var template = _.template( $('#template_job').html() );

			if ( $('#template_mobile_' + item.template_id).length > 0 ){
				template = _.template($('#template_mobile_' + item.template_id).html());
			}else {
				template = _.template($('#template_job').html())
			}

			container.append(
				template(item)
			);
		}

		// refresh listview
		$('div.ui-page-active ul.listview').listview('refresh');

		enable_ajax = true;
	};


	/**
	 * Render search item
	 */
	$.render_LoadSearchJob = function(contents){

		var flag = 0,
			flag_title = 0,
			container = $('ul.listview'),
			item,i;
		if(contents != ''){
			for(i=0; i<contents.length; i++){
				item = contents[i];
				if(item.featured == 1 && flag_title == 0){
					container.append(
						$('<li class="list-divider">').text('Featured Jobs')
					);
					flag_title = 1;
				}

				if (item.featured == flag ) {
					container.append(
						$('<li class="list-divider">').text('Jobs')
					);
					flag = 1;
				}
				_.templateSettings = {
				    evaluate    : /<#([\s\S]+?)#>/g,
					interpolate : /\{\{(.+?)\}\}/g,
					escape      : /<%-([\s\S]+?)%>/g
				};
			
				var template = _.template( $('#template_job').html() );

				if ( $('#template_mobile_' + item.template_id).length > 0 ){
					template = _.template($('#template_mobile_' + item.template_id).html());
				}else {
					template = _.template($('#template_job').html())
				}

				container.append(
					template(item)
				);
			}
		}else {
			if ( query_default.content.s == '' ){
				container.append(
					$('<li class="no-result">').text('There is no result for your search.')
				);
			}else {
				container.append(
					$('<li class="no-result">').text('There is no result for keyword ').append(
						$('<strong>').text('"' + query_default.content.s + '"')
					)
				);
			}
		}

		// refresh listview
		$('div.ui-page-active ul.listview').listview('refresh');
	};

	/**
	 * Check format of email address
	 */
	function validateEmail(email_address){
		var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
		return emailPattern.test(email_address);
	}

})(jQuery);