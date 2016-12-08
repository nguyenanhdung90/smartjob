(function($){
	var signUp	=	{
		user : {
			action  				: 'et_register' ,
			user_name				: '',
			display_name 			: '',
			user_email				: '',
			user_pass				: '',
			password_again			: '',
			role					: 'company'
		},

		updateUserData : function ( name, value ) {
			this.user[name]	=	value;
		},

		sync : function () {

			var view = this;

			if( view.user.action == 'et_login' ) {
				view.user.user_email	=	view.user.user_name;

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
							//$('.authentication-form').remove();
							window.location.reload();
							view.user.ID = res.data.ID;
						} else {
							alert (res.msg);
						}
					}
				});

			}else {

				view.user.id	=	view.user.ID;
				if(view.user.user_pass !== view.user.password_again) {
					alert('Password miss match');
					return;
				}
				$.ajax ({
					url : et_globals.ajaxURL,
					type : 'post',
					data :  view.user,
					beforeSend : function () {
						$.mobile.showPageLoadingMsg();
					},
					success : function (res) {
						$.mobile.hidePageLoadingMsg();
						if(res.status) {
							//$('.authentication-form').remove();
							window.location.reload();
						} else {
							alert (res.msg);
							if(typeof reCaptcha != 'undefined')
								Recaptcha.reload();
						}
					}
				});
			}
		}
	}

	var job  = {
		reloadMap	: function(args){
			var that	= this,
				$locationtxt	=	$('#location');

			args.callback	= function(results,status){
				var latlng, location_lat, location_lng;

				if (status == 'OK') {

					location_lat	= $('#location_lat'),
					location_lng	= $('#location_lng');
					latlng			= results[0].geometry.location;
			
					location_lat.val(latlng.lat());
					location_lng.val(latlng.lng());

				}	

			};

			GMaps.geocode(args);
		}
	}

	

	$(document).on('pageinit' , function () {

		$('#full_location').on('change' , function () {
			job.reloadMap ({address : $(this).val()});
		});

		if( $('.post_author').length > 0 && $('.post_author').val() != '' ) {
			signUp.user.ID		=	$('.post_author').val();
			//signUp.user.action  = 	'et_register'
		}
		
		/**
		 * toggle login form
		*/
		$('.open-login').on('tap', function (){
			$('form.register').slideUp();
			$('form.login').slideDown("slow");
		});

		/**
		 * submit register form
		*/
		$('form.register').on ('submit' , function (e) {
			e.preventDefault();
			$(this).find('input').each(function () {
				var $target = $(this),
					name	= $target.attr('name'),
					value	= $target.val();
				signUp.updateUserData (name, value);
			});
			signUp.sync();
		});

		$('form.forgot-password').on ('submit' , function (e) {
			e.preventDefault();
			var email = $(e.currentTarget).find('input[name=user_login]').val();
			$.ajax({
					url 	: et_globals.ajaxURL,
					type 	: 'post',
					contentType	: 'application/x-www-form-urlencoded;charset=UTF-8',
					data	: {
						action		: 'et_request_reset_password',
						user_login : email,
					},
					beforeSend : function() {
						$.mobile.showPageLoadingMsg();
					},
					success : function (response) {
						$.mobile.hidePageLoadingMsg();
						if(response.success){
							alert(response.msg);
							setTimeout(function() {window.location.reload();}, 1000);
						} else {
							var msg = response.msg.replace(/<(?:.|\n)*?>/gm, '');


							alert(msg);
						}

					}
				});
		});

		/**
		 * submit login form
		*/
		$('form.login').on ('submit' , function (e) {
			e.preventDefault();
			$(this).find('input').each(function () {
				var $target = $(this),
					name	= $target.attr('name'),
					value	= $target.val();
				signUp.user.action = 'et_login';
				signUp.updateUserData (name, value);
			});
			signUp.sync();
		});

		$('.main-payment').on('click' , function (e) {

			e.preventDefault();

			var $target		=	$(e.currentTarget),
				$container	=	$target.parents('.payment-form'),
				paymentType		=	$target.attr('data-payment');

			if( paymentType != '' ) {
				$.ajax({
					url 	: et_globals.ajaxURL,
					type 	: 'post',
					contentType	: 'application/x-www-form-urlencoded;charset=UTF-8',
					data	: {
						action		: 'et_payment_process',
						jobID		: $container.find('input[name="ad_id"]').val(),
						authorID	: $container.find('input[name="post_author"]').val(),
						packageID	: $container.find('input[name="et_payment_package"]').val(),
						paymentType	: paymentType,
						coupon_code	: $('#coupon_code').val()
					},
					beforeSend : function() {
						$.mobile.showPageLoadingMsg();
					},
					success : function (response) {

						$.mobile.hidePageLoadingMsg();

						if(response.success) {
							if( response.data.ACK ) {

								$('#checkout_form').attr('action',response.data.url);
								if(typeof response.data.extend !== "undefined") {
									$('#checkout_form .payment_info').html('').append(response.data.extend.extend_fields);
									$('#checkout_form .payment_info').trigger('create');
								}
								$('#payment_submit').click();
							}

						} else {
							alert (response.errors[0]);
						}
					}
				});
			}else {
				alert('Please select a payment processor.');
			}
		});

	});
})(jQuery);