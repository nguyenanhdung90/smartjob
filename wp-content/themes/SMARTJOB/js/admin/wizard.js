(function($){
	$(document).ready(function(){
		new JobEngine.Views.Wizard();
	});

	JobEngine.Views.Wizard = Backbone.View.extend({
		el : '#engine_setting_content',
		events : {
			'click .inner-menu li a' 					: 'eventGoToStep',
			//'click .inner-menu li a' 					: '',
			'change #wizard-branding input[type=text]' 	: 'updateGeneralSetting',
			
			'click ul.menu-currency a'  				: 'changeCurrency',
			'click .next-step' 							: 'goNextStep',
			'click .finish-wizard' 						: 'goNextStep',
			'click .options a.deactive' 				: 'deactiveOption',
			'click .options a.active'   				: 'activeOption',
			'change .payment-setting input'				: 'updatePaymentSetting',
			'focusout  .cash-message' 					: 'updateCashPaymentSetting',
			'submit form#payment_plans_form' 			: 'submitPaymentForm',
			'click button#install_sample_data' 			: 'installSampleData',
			'click button#delete_sample_data'			: 'deleteSampleData'
		},

		initialize : function(){
			// init tinyMCE
			appView =	this;
			this.initPaymentPlans();

			// create an array of targets for generating uploaders
			this.uploaderIDs	= ['website_logo','mobile_icon'];
			this.uploaderThumbs	= ['large','thumbnail','company-logo'];
			this.uploaders		= [];
			var blockUis = []; //new JobEngine.Views.BlockUi();

			tinyMCE.execCommand('mceAddControl', false, 'cash-message');
			// loop through the array to init uploaders
			for( i=0; i<this.uploaderIDs.length; i++ ){
				// get the container of the target
				$container	= this.$('#' + this.uploaderIDs[i] + '_container');
				blockUi = new JobEngine.Views.BlockUi();

				this.uploaders[this.uploaderIDs[i]]	= new JobEngine.Views.File_Uploader({
					el					: $container,
					uploaderID			: this.uploaderIDs[i],
					thumbsize			: this.uploaderThumbs[i],
					multipart_params	: {
						_ajax_nonce	: $container.find('.et_ajaxnonce').attr('id'),
						action		: 'et-change-branding',
						imgType		: this.uploaderIDs[i]
					},
					cbUploaded			: function(up,file,res){
						if(res.success){
							$('#'+this.container).parents('.desc').find('.error').remove();
						} else {
							$('#'+this.container).parents('.desc').append('<div class="error">'+res.msg+'</div>');
						}
					},
					beforeSend 			: function(element){
						button = $(element).find('.image');
						blockUi.block(button);
					},
					success : function(){
						blockUi.unblock();
					}
				});
			}

			
		
			// flag color chooser
			jQuery(".input-form .bar-flag div").live("click" , function(){
				var color 		= jQuery(this).attr("class");
				color 			= color.replace(" active","");
				var code 		= $(this).attr('data');

				jQuery(this).parent().parent().find(".cursor").removeClass().addClass("cursor").addClass(color).attr('data', code);
				jQuery(this).parent().parent().find("input").removeClass().addClass("bg-grey-input").addClass(color);
				
				jQuery(this).parent().remove();

				// send color data via ajax
				if ( $('.current-job-type').length > 0 ){
					var term_id 	= $('.current-job-type').attr('data');
					appView.changeJobTypeColor(term_id, code);
				}
			});

		},

		initPaymentPlans : function(){
			// initilize payment plans
			var planCollection = new JobEngine.Views.PaymentPlanCollection({el : $('ul.pay-plans-list') });
		},

		goNextStep: function(event){
			event.preventDefault();
			var $this 	= $(event.currentTarget);
			var target 	= $this.attr('href') ? $this.attr('href') : '';
			var params 	= ajaxParams;
			var title 	= $this.html();
			var method 	=  $this.hasClass('next-step') ? 'next-step' : 'finish';
			var view = this;
			var currentStep = $('.wizard-steps li a.active').parent().index();

			params.data = {
				action : 'et_verify_setup_process',
				method : method,
				content : {
					step : target
				}
			};
			params.beforeSend = function(){
				$this.html('Loading...');
			};
			params.success = function(data){
				$this.html(title);
				if (method == 'next-step' ) {
					if ( data.success ){
						view.chooseSection( $('.inner-menu a[menu-data=' + $this.attr('href') + ']') );
						$('body').animate( { scrollTop: $('.et-main-content').offset().top }, 500 );		
					} else {
						alert ( data.msg || et_wizard.alert_msg);
					}
				}else{
					alert ( data.msg );
				}
				if ( data.data.finishNumber )
					$('.et-main-header .wizards').removeClass('wizard-step1 wizard-step2 wizard-step3').addClass('wizard-step' + data.data.finishNumber);
			};
			$.ajax(params);
		},

		goToStep : function(target){
			var params 	= ajaxParams;
			var view = this;
			//var title 	= $this.html();
			params.data = {
				action : 'et_verify_setup_process',
				method : 'next-step',
				content : {
					step : target
				}
			};
			params.beforeSend = function(){
				// $this.html('Loading...');
			};
			params.success = function(data){
				// $this.html(title);
				if ( data.success ){
					view.chooseSection($('a[menu-data='+target+']'));
					//$('.inner-menu a[menu-data=' + $this.attr('href') + ']').trigger('click');	
					//$('body').animate( { scrollTop: $('.et-main-content').offset().top }, 500 );		
				} else {
					alert ( data.msg || et_wizard.alert_msg );
				}
			};
			$.ajax(params);
		},

		eventGoToStep : function(event){
			event.preventDefault();
			var $this 	= $(event.currentTarget);
			var target 	= $this.attr('menu-data') ? $this.attr('menu-data') : '';
			this.goToStep(target);
		},

		chooseSection : function(section){
			var current = $(section);
			$('.inner-content').hide();
			$('.inner-menu li a.active').removeClass('active');
			$(current.attr('href')).show();
			current.addClass('active');

			// route url
			// Backbone.history.navigate('section/ET_MenuWizard/' + current.attr('menu-data'));
		},

		updateGeneralSetting : function ( event ) {
			var $new_value	=	$(event.currentTarget);
			var $container	=	$new_value.parent('div');
			var $icon	=	$container.find('.icon');
			if ($new_value.val() != '' ) {
				$.ajax ({
					url  : et_globals.ajaxURL,
					type : 'post',
					data : { 
						action 	   : 'et-update-general-setting',
						new_value  : $new_value.val(),
						option_name: $new_value.attr('name'),
						type 		: $new_value.attr ('type')
					},
					beforeSend : function(){
						
						$icon.attr('data-icon', '');
						$icon.html('<img src="'+et_globals.imgURL+'/loading.gif" />');
					},
					success : function( response ){					
						if(response.success) {
							$icon.html('');
							$new_value.removeClass('color-error');
							$icon.removeClass('color-error');
							$icon.attr('data-icon', '3');
							
						} else {
							$icon.html('');
							$new_value.addClass('color-error');
							$icon.addClass('color-error');
							$icon.attr('data-icon', '!');
						}
					}
				});		
			} else {
				$icon.html('');
				$new_value.addClass('color-error');
				$icon.addClass('color-error');
				$icon.attr('data-icon', '!');
			}
			return false;
		},

		updateSiteDemon : function  (  ) {
			var ed 			=	tinyMCE.get('site_demon');
			var new_value	=	ed.getContent ();
			
			var $container	=	$('div.site_demon');
			$.ajax ({
				url  : et_globals.ajaxURL,
				type : 'post',
				data : { 
					action 	   : 'et-update-general-setting',
					new_value  : new_value,
					option_name: 'site_demon'
				},
				beforeSend : function(){
					$container.find('.icon').attr('data-icon', '');
					$container.find('.icon').html('<img src="'+et_globals.imgURL+'/engine-logo.png" />');
				},
				success : function(reponse){
					if(reponse && ed.getContent() != '' ) {
						$container.find('.icon').html('');
						$container.find('input').removeClass('color-error');
						$container.find('.icon').removeClass('color-error');
						$container.find('.icon').attr('data-icon', '3');
					} else {
						$container.find('.icon').html('');
						$container.find('input').addClass('color-error');
						$container.find('.icon').addClass('color-error');
						$container.find('.icon').attr('data-icon', '!');
					}
				}
			});		
		},
		
		changeCurrency 	: function (event) {
			
			var $li 	=	$(event.currentTarget);
			var action		=	$li.attr('href');
			action			=	action.replace ('#', '');
			$.ajax ({
				url  : et_globals.ajaxURL,
				type : 'post',
				data : { 
					action 	  :  action,
					new_value :  $li.attr('rel')
				},
				beforeSend : function(){
					
				},
				success : function(response){
					// update view
					$container	=	$li.parents('ul');
					$container.find('.active').removeClass('active');
					$li.addClass('active');
					
				}
			});		
			return false;
		},
		updatePaymentSetting	: function (event) {
			var data	=	$(event.currentTarget),
				icon	=	data.next (),
				payment	=	data.parents('.payment-setting'),
				button	=	payment.prev ('.payment');
			
			$.ajax ( {
				url  : et_globals.ajaxURL,
				type : 'post',
				data : { 
					action 	: 'et-save-payment-setting',
					name 	:  data.attr('name'),
					value	:  data.val ()
				},
				beforeSend : function(){
					icon.attr ('data-icon','');
					icon.html ('<img src="'+et_globals.imgURL+'/loading.gif" />');
				},
				success : function(response){
					if( response.success ) {
						icon.html ('');
						icon.removeClass('color-error')
						icon.attr ('data-icon','3');
					} else {
						icon.html ('');
						icon.addClass('color-error')
						icon.attr ('data-icon','!');
						button.find('a.active').removeClass ('selected');
						button.find('.message').html (response.msg);
						button.find('.message').show ();
					}
				}
			});
		},
		deactiveOption	: function (event) {
			event.preventDefault ();
			var payment	=	$(event.currentTarget),
				icon	=	payment.parents('.payment').find('a.icon'),
				view 	= this,
				loadingView = new JobEngine.Views.LoadingEffect(),
				blockUI = new JobEngine.Views.BlockUi(),
				container 	= $(event.currentTarget).parent(),
				enableBtn = container.children('a.active');


			$.ajax ( {
				url  : et_globals.ajaxURL,
				type : 'post',
				data : { 
					action 	 : 'et-disable-option',
					gateway :  payment.attr('rel')
				},
				beforeSend : function(){
					blockUI.block(payment);
					payment.addClass('selected');
					enableBtn.removeClass('selected');
				},
				success : function(response){
					blockUI.unblock();
					if( response.success == true) {
						//change display
					} else {
						enableBtn.addClass('selected');
						payment.removeClass('selected');
					}
				}
			});
			return false;
		},
		
		activeOption 	: function  (event) {
			event.preventDefault();
			var payment	=	$(event.currentTarget),
				icon_container	=	payment.parents('.payment'),
				icon 			= 	icon_container.find('a.icon'),
				view 	= this,
				loadingView = new JobEngine.Views.LoadingEffect(),
				container 	= $(event.currentTarget).parent(),
				blockUI = new JobEngine.Views.BlockUi(),
				disableBtn = container.children('a.deactive');
			
			$.ajax ( {
				url  : et_globals.ajaxURL,
				type : 'post',
				data : { 
					action 	: 'et-enable-option',
					gateway :  payment.attr('rel'),
					label	:  payment.attr ('title')
				},
				beforeSend : function(){
					blockUI.block(payment);
					payment.addClass('selected');
					disableBtn.removeClass('selected');
				},
				success : function(response){
					blockUI.unblock();
					if( response.success == true) {
						icon_container.find('.message').hide ();
					} else {
						disableBtn.addClass('selected');
						payment.removeClass('selected');
					}
				}
			});
			return false;
		},

		updateCashPaymentSetting : function (event) {
			var data 	=	$(event.currentTarget),
				name 	=	'cash-message',
				value 	=	tinyMCE.get('cash-message').getContent (),
				icon	=	data.next ();
			$.ajax ( {
				url  : et_globals.ajaxURL,
				type : 'post',
				data : { 
					action 	: 'et-save-payment-setting',
					name 	:  name,
					value	:  value
				},
				beforeSend : function(){
					icon.attr ('data-icon','');
					icon.html ('<img src="'+et_globals.imgURL+'/loading.gif" />');
				},
				success : function(response){
					if( response.success ) {
						icon.html ('');
						icon.removeClass('color-error')
						icon.attr ('data-icon','3');
					} else {
						icon.html ('');
						icon.addClass('color-error')
						icon.attr ('data-icon','!');
						button.find('a.active').html ('');
					}
				}
			});
		},
		updateCashPaymentSetting : function (event) {
			var data 	=	$(event.currentTarget),
				name 	=	'cash-message',
				value 	=	tinyMCE.get('cash-message').getContent (),
				icon	=	data.next ();
			$.ajax ( {
				url  : et_globals.ajaxURL,
				type : 'post',
				data : { 
					action 	: 'et-save-payment-setting',
					name 	:  name,
					value	:  value
				},
				beforeSend : function(){
					icon.attr ('data-icon','');
					icon.html ('<img src="'+et_globals.imgURL+'/loading.gif" />');
				},
				success : function(response){
					if( response.success ) {
						icon.html ('');
						icon.removeClass('color-error')
						icon.attr ('data-icon','3');
					} else {
						icon.html ('');
						icon.addClass('color-error')
						icon.attr ('data-icon','!');
						button.find('a.active').html ('');
					}
				}
			});
		},
		// event handle: Submit Payment form
		submitPaymentForm : function(event){
		event.preventDefault();
		var form = $(event.target);
		var button = form.find('.engine-submit-btn');
		var buttonTitle = button.find('span:not(.icon)');
		var elements = form.find('input,select,textarea');
		var epattern = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
		var npattern = /^[0-9]*(.[0-9]*)*$/;
		var list_container = $('#payment_lists');
		var view = this;


			var hasError = false;
			// small validate
			elements.each(function(){
				var e = $(this);
				e.removeClass('field-error');

				if (e.hasClass('not-empty') && e.val() == '' ){
					e.addClass('field-error');
					hasError = true;
				}
				else if ( e.hasClass('is-email') && !epattern.test(e.val()) ){
					e.addClass('field-error');
					hasError = true;
				}else if ( e.hasClass('is-number') && !npattern.test(e.val()) ) {
					e.addClass('field-error');
					hasError = true;
				}
			});

			if ( !hasError ){
				// processing
				var model = new JobEngine.Models.PaymentPlan({
					title: form.find('input[name=payment_name]').val(),
					price: form.find('input[name=payment_price]').val(),
					duration: form.find('input[name=payment_duration]').val(),
					featured: form.find('input[type=checkbox][name=payment_featured]').is(':checked') ? 1 : 0,
					quantity: form.find('input[name=payment_quantity]').val()
				}),
					loading = new JobEngine.Views.LoadingButton({el : button});

				model.add({
					beforeSend : function(){
						loading.loading();
					},
					success : function(){
						loading.finish();
						form.find('input').val('');
					}
				});
			}else {
				alert('Input is invalid. Please recheck again');
			}
		}, 

		updatePaymentOrder : function(){
			var order = $('ul.pay-plans-list').sortable('serialize');
			var params = ajaxParams;
			params.data = {
				action: 'et_sort_payment_plan',
				content : {
					order: order
				}
			};
			params.before = function(){	}
			params.success = function(data){
			}
			$.ajax(params);
		},

		installSampleData : function(event){
			event.preventDefault();
			if ( $(event.currentTarget).hasClass('disabled') ) return false;

			var block = new JobEngine.Views.BlockUi();
			
			$.ajax ({
				url  : et_globals.ajaxURL,
				type : 'post',
				data : { 
					action 	: 'et-insert-sample-data'
				},
				beforeSend: function(){
					block.block($(event.currentTarget));
				},
				success : function(response){
					block.unblock ();
					if( response.success ) {
						$(event.target).after(
							$('<button>').text(et_wizard.delete_sample_data).attr({
								'id'	: 'delete_sample_data',
								'type'	: 'button',
								'class'	: 'primary-button'
							})
						);
						$(event.target).remove();
					}
					else {
						alert(et_wizard.insert_fail);
					}
				},
				error: function(jqXHR, textStatus, errorThrown){
				}
			});
		},

		deleteSampleData : function(event){
			event.preventDefault();
			if ( $(event.currentTarget).hasClass('disabled') ) return false;

			var block = new JobEngine.Views.BlockUi();
			$.ajax ({
				url  : et_globals.ajaxURL,
				type : 'post',
				data : {
					action 	: 'et-delete-sample-data'
				},
				beforeSend: function(){
					block.block($(event.currentTarget));
				},
				success : function(response){
					block.unblock ();
					if( response.success ) {
						$(event.target).after(
							$('<button>').text(et_wizard.insert_sample_data).attr({
								'id'	: 'install_sample_data',
								'type'	: 'button',
								'class'	: 'primary-button'
							})
						);
						$(event.target).remove();
					}
					else{
						alert(et_wizard.delete_fail);
					}
				}
			});
		}
	});
	JobEngine.Views.PaymentPlanCollection = Backbone.View.extend({
		el : 'ul.pay-plans-list',
		initialize: function(){
			var view = this;
			view.views = [];
			view.collection = new JobEngine.Collections.Payments( JSON.parse( $('#payment_plans_data').html() ) );
			view.$el.find('li').each(function(index){
				var $this = $(this);
				view.views.push( new JobEngine.Views.PaymentItem({
					model : view.collection.models[index],
					el : $this
				}) );
			});

			this.collection.bind('remove', this.removeView, this );
			this.collection.bind('add', this.addView, this );

			pubsub.on('je:setting:paymentPlanAdded', this.addView, this);

		},
		add : function(model){
			this.collection.add(model);
		},
		removeView : function(model){
			var thisView = this;
			var viewToRemove = _.filter( thisView.views, function(vi){ 
				return vi.model.get('id') == model.get('id');
			})[0];

			_.without(thisView.views, viewToRemove);

			viewToRemove.fadeOut();
		},
		addView : function(model){

			var view = new JobEngine.Views.PaymentItem({model: model});
			this.views.unshift( view );

			view.render().$el.hide().prependTo( this.$el ).fadeIn();
		}
	});

})(jQuery);