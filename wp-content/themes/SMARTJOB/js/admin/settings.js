(function($){
$(document).ready(function(){
	new JobEngine.Views.BackendSetting();
});

JobEngine.Views.BackendSetting = Backbone.View.extend({
	el : '#engine_setting_content',

	events : {
		'submit form#payment_plans_form' 	: 'submitPaymentForm',
		//'click #add_playment_plan' 			: 'clickPaymentButton',
		// change currency and language
		'click ul.menu-currency a'  		: 'changeCurrency'	,
		'click ul.list-language a'  		: 'changeLanguage',
		'click .payment a.deactive' 		: 'deactiveOption',
		'click .payment a.active'   		: 'activeOption',
		'change .payment-setting input'		: 'updatePaymentSetting',
		'focusout #setting-payment .cash-message'	: 'updateCashPaymentSetting',
		'click #setting-payment .add-new-currency'		: 'showAddNewCurrencyForm',
		'click #add-new-currency'						:	'addNewCurrency',
		'click #engine-currency-form .button-enable a'	:	'currencyAlignChange',
		'change #engine-currency-form #currency_icon' 	: 	'currencyIconChange',

		'click .engine-currency-form .symbol-pos a' 	: 'changeSymbolPos',
		'click .inner-menu li a' 				: 'chooseSection',
		
		'change #setting-general input'			:	'updateGeneralSetting',
		'change #setting-general textarea'		:	'updateGeneralSetting',
		'focusout #setting-general .site_demon' : 	'updateSiteDemon',
		'click #setting-language .add-lang'			: 'showAddnewLangForm',
		'keyup #setting-language #new-language-name'	: 'handleNewLangForm',
		// 'click #setting-language button#add-new-language' 	: 'addNewLanguage',
		'change #language-list textarea'					: 'markLanguageChange',
		'click #setting-language button#save-language' 		: 'saveLanguage',
		'change #setting-language select#base-language'	    : 'loadTranslationForm'	,
		// 'click .mail-template-title'						: 'triggerMailTemplateContent',
		'focusout .mail-template'							: 'updateMailTemplate',
		'click #setting-mail-template .trigger-editor'		: 'triggerEditor',
		'click #setting-mail-template .reset-default'		: 'resetDefaultMailTemplate',
		'click .et-main-main .desc .payment'				: 'togglePaymentSetting',		
		'keyup #license_key' 								: 'keyupLicenseKey',
		'change #license_key' 								: 'changeLicenseKey',
		'change #job_notification_email' 					: 'changeNotificationMail',
		'change #limit-free-plan'							: 'updateLimitFreePlan',

		'change input.social-input' 			: 'updateSocialValues',
	},
	
	initialize: function(){
		// initialize small views
		this.initPaymentPlans();
		
		// update varialbe
		this.previousLicenseKey = $('input#license_key').val();
		
		// init tinyMCE
		appView =	this;
		
		// tinyMCE.execCommand('mceAddControl', true, 'site_demon');
		// tinyMCE.execCommand('mceAddControl', true, 'cash-message');
		// $('#setting-mail-template textarea').each (function () {
		// 	tinyMCE.execCommand('mceAddControl', true, $(this).attr('id'));		
		// 	$(this).next ('a').addClass ('activated');
		// });
		
		// keep language translate bar
		$.event.add(window, "scroll", function() {
			var p 	 	= $(window).scrollTop(),
				lang_bar 	= $('.language-translate-bar');
				// location_url= $(location).attr("href")
			
			var	sheight	= 0, swidth = 0;
			// var n = location_url.search("language");			
			var dlenght = $("#language-list").height();

			if (lang_bar.length) {
				sheight  = lang_bar.offset().top;
				swidth   = lang_bar.width();
			}
			
			if ( (p>sheight-38) && (dlenght>0) ) {
				
				if ( !$('#language-bar').length ) {
					lang_bar.append(
						'<div id="language-bar">'
						+ lang_bar.html()
						+'</div>'
					);
		
					lang_bar.find('#language-bar').css({ "width" : swidth+1 });	
				}
		
			} else  {
				lang_bar.find('#language-bar').remove();
			}
			
		} );
		
		// select element styling
		$(".select-style select").each(function(){
			//var title = $(this).attr('title') || $(this).html;
			var title = $(this).find('option:selected').html();
			var arrow = "";
			if ($(".select-style select").attr('arrow') !== undefined) 
				arrow = " " + $(".select-style select").attr('arrow');

			if( $('option:selected', this).val() != ''  ) title = $('option:selected',this).text() + arrow ;
			$(this)
				.css({'z-index':10,'opacity':0,'-khtml-appearance':'none'})
				.after('<span class="select">' + title + arrow + '</span>')
				.change(function(){
					val = $('option:selected',this).text() + arrow;
					$(this).next().text(val);
					})
		});

		// show template help content
		$('.btn-template-help').click(function(){
			$('.cont-template-help').slideToggle(300);
			return false;
		});

		// sort payment plan
		$('.sortable').sortable({
			axis: 'y',
			handle: 'div.sort-handle'
		});
		
		
		// payment plan sorting
		$('ul.pay-plans-list').bind('sortupdate', function(e, ui){
			appView.updatePaymentOrder();
		});

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

		// enable autosize
		$('textarea.autosize').attr('row',1).autosize();

		// create an array of targets for generating uploaders
		this.uploaderIDs	= ['website_logo','mobile_icon','default_logo'];
		this.uploaderThumbs	= ['large','thumbnail','company-logo'];
		this.uploaders		= [];

		var blockUi = new JobEngine.Views.BlockUi(),
			cbBeforeSend = function(ele){
				button = $(ele).find('.image');
				blockUi.block(button);
			},
			cbSuccess = function(){
				blockUi.unblock();
			};

		// loop through the array to init uploaders
		for( i=0; i<this.uploaderIDs.length; i++ ){
			// get the container of the target
			$container	= this.$('#' + this.uploaderIDs[i] + '_container');

			this.uploaders[this.uploaderIDs[i]]	= new JobEngine.Views.File_Uploader({
				el					: $container,
				uploaderID			: this.uploaderIDs[i],
				thumbsize			: this.uploaderThumbs[i],
				multipart_params	: {
					_ajax_nonce	: $container.find('.et_ajaxnonce').attr('id'),
					action		: 'et-change-branding',
					imgType		: this.uploaderIDs[i]
				},
				cbUploaded	: function(up,file,res){
					if(res.success){
						$('#'+this.container).parents('.desc').find('.error').remove();
					} else {
						$('#'+this.container).parents('.desc').append('<div class="error">'+res.msg+'</div>');
					}
				},
				beforeSend	: cbBeforeSend,
				success		: cbSuccess
			});
		}
	},

	initPaymentPlans : function(){
		// initilize payment plans
		var planCollection = new JobEngine.Views.PaymentPlanCollection({el : 'ul.pay-plans-list' });
	},


	chooseSection : function(event){
		event.preventDefault();

		var current = $(event.target);
		$('.inner-content').hide();
		$('.inner-menu li a.active').removeClass('active');
		$(current.attr('href')).show();
		current.addClass('active');

		// route url
		// Backbone.history.navigate('section/ET_MenuSettings/' + current.attr('menu-data'));
	},
	
	updatePaymentSetting	: function (event) {
		var data	=	$(event.currentTarget),
			icon	=	data.next ('span'),
			payment	=	data.parents('.payment-setting'),
			button	=	payment.prev('.payment');
			val		=	data.val();
		if(data.attr('type') == 'checkbox') {
			if( !data.is(':checked') ) val = 0;
			else val = 1;
		}

		$.ajax ( {
			url  : et_globals.ajaxURL,
			type : 'post',
			data : { 
				action 	: 'et-save-payment-setting',
				name 	:  data.attr('name'),
				value	:  val
			},
			beforeSend : function(){
				icon.attr ('data-icon','');
				icon.html ('<img src="'+et_globals.imgURL+'/loading.gif" />');
			},
			success : function(response){
				icon.html ('');
				button.find('.message').html ('');
				if( data.val () == '') {
					icon.addClass('color-error');
					data.addClass('color-error');
					icon.attr ('data-icon','!');
				} else {
					icon.removeClass('color-error');
					data.removeClass('color-error');
					icon.attr ('data-icon','3');
				}
				if( !response.success ) {
					button.find('a.active').removeClass ('selected');
					button.find('.message').html (response.msg);
					button.find('.message').show ();
					//icon.addClass('color-error');
					//data.addClass('color-error');
					//icon.attr ('data-icon','!');
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
	showAddNewCurrencyForm : function  (event) {
		$('.show-new-currency').show ();
		$('#currency_name').focus ();
	},
	addNewCurrency	:	function (event) {
		event.preventDefault ();
		var $form	=	$(event.currentTarget).parents('form#engine-currency-form');
		$form.find('input').each (function (event) {
			if($(this).val() === '') {
				$(this).addClass('color-error');
				$(this).focus ();
			} 
		});
		var text	=	$form.find('#currency_name').val(),
			icon	=	$form.find('#currency_icon').val(),
			code	=	$form.find('#currency_code').val(),
			align	=	$form.find('a.selected').attr('rel');
			
		$.ajax ({
			url	:	et_globals.ajaxURL,
			type:	'post',
			data:	{
				'code' : code,
				'text' : text,
				'icon' : icon,
				'align': align,
				'action' : 'et-add-new-currency'
			},
			beforeSend: function () {

			},
			success : function (response) {
				if(response.success) {
					var li	=	document.createElement('li');
					if(align == 'right') {
						var money =	code+' '+icon;
					} else {
						var money =	icon+' '+code;
					}
					$('.menu-currency').find ('a.active').removeClass('active');
					li.innerHTML = '<a href="#et-change-currency" class="select-currency  active" title="'+text+'" rel="'+code+'">'+money+' </a>';
					$('.menu-currency').append (li);
				}
			}
		});
	},
	currencyAlignChange	: function (event) {
		event.preventDefault ();
		var $target	=	$(event.currentTarget),
			icon = '$';
		$('form#engine-currency-form').find('a.selected').removeClass('selected');
		$target.addClass('selected');
		if($('#currency_icon').val() !== '') {
			icon 	=	$('#currency_icon').val();
 		} 
 		if($target.attr('rel') == 'left') {
			$('.currency_text').html ('<sup>'+icon+'</sup>1000,000');
		} else {
			$('.currency_text').html ('1000,000<sup>'+icon+'</sup>');
		}
	},
	currencyIconChange : function (event) {
		event.preventDefault ();
		var currencyIcon	=	$(event.currentTarget).val();
		$('.currency_text').find('sup').html (currencyIcon);
	},
	validateCurrencyFormInput	: function (event) {
		var $target	=	$(event.currentTarget);
		if($target.val() === '') {
			$target.addClass('color-error');
			$target.focus ();
		} else {
			$target.removeClass('color-error');
		}
	}
	,
	changeSymbolPos:function(event){
		event.preventDefault();

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

		if (container.hasClass('disabled')) return false;

		$.ajax ( {
			url  : et_globals.ajaxURL,
			type : 'post',
			data : { 
				action 	 : 'et-disable-option',
				gateway :  payment.attr('rel')
			},
			beforeSend : function(){
				blockUI.block(payment);
				container.addClass('disabled');
				payment.addClass('selected');
				enableBtn.removeClass('selected');
			},
			success : function(response){
				blockUI.unblock();
				container.removeClass('disabled');
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

		if (container.hasClass('disabled')) return false;
		
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
				container.addClass('disabled');
				payment.addClass('selected');
				disableBtn.removeClass('selected');
			},
			success : function(response){
				blockUI.unblock();
				container.removeClass('disabled');
				if( response.success == true) {
					icon_container.find('.message').hide ();
				} else {
					disableBtn.addClass('selected');
					payment.removeClass('selected');
					icon_container.find('.message').html (response.msg);
					payment.parents('.item').find('.payment-setting').show();
				}
			}
		});
		return false;
	},

	toogleOption : function(event){

	},
	
	changeCurrency 	: function (event) {
		
		var $li 		=	$(event.currentTarget),
			action		=	$li.attr('href'),
			container 	= $li.parent(),
			blockUI 	= new JobEngine.Views.BlockUi(),
			view 		= this;
		action			=	action.replace ('#', '');

		$.ajax ({
			url  : et_globals.ajaxURL,
			type : 'post',
			data : { 
				action 	  :  action,
				new_value :  $li.attr('rel')
			},
			beforeSend : function(){
				blockUI.block(container);
				// update view
				$container	=	$li.parents('ul');
				$container.find('.active').removeClass('active');
				$li.addClass('active');
			},
			success : function(response){
				blockUI.finish();
			}
		});		
		return false;
	},

	changeLanguage : function (event) {
		event.preventDefault();
		var $li 	=	$(event.currentTarget);
		var action		=	$li.attr('href');
		action			=	action.replace ('#', '');
		var blockUi 	= new JobEngine.Views.BlockUi();
		$.ajax ({
			url  : et_globals.ajaxURL,
			type : 'post',
			data : { 
				action 	  :  action,
				new_value :  $li.attr('rel')
			},
			beforeSend : function(){
				//blockUi.block($(event.currentTarget));
			},
			success : function(response){
				//blockUi.unblock();
				// update view
				if( response.success) {
					$container	=	$li.parents('ul');
					$container.find('.active').removeClass('active');
					$li.addClass('active');
					window.location.reload();
				}				
			}
		});
		
	},

	markLanguageChange : function (event) {
		event.preventDefault ();
		var target	=	$(event.currentTarget);

		target.addClass('changed');

		var container	=	target.parents('.form-item');

		container.find('input').addClass('changed');

	},
	// event save all translations
	saveLanguage : function (event) {
		event.preventDefault ();

		// prevent sending if button are disable
		if ( this.isSaveingLanguage ) return false;
		var button 		= 	$(event.currentTarget);

		var form 		=	$('#setting-language').find ('form#language-list'),
			lang_name	=	$('#setting-language').find('select#base-language').val (),
			view 		= 	this;
		
		var title 		= 	button.html(),
			data		= 	'',
			loadingBtn 	= new JobEngine.Views.LoadingButton({ el : $(button)});

		form.find('.changed').each (function () {			
			data 	= 	data + $(this).attr('name')+'='+encodeURIComponent($(this).val())+'&';			
		});

		$.ajax ({
			url  : et_globals.ajaxURL,
			type : 'post',
			data : 
				data + 'action=et-save-language&lang_name='+lang_name				
			,
			beforeSend : function(){
				this.isSaveingLanguage = true;
				loadingBtn.loading();
			},
			success : function(reponse){
				loadingBtn.finish();
				// update view
				this.isSaveingLanguage = false;	
			}
		});		
	},
	showAddnewLangForm : function  (event) {
		var $current	=	$(event.currentTarget);
		var container 	= $current.parent();
		$current.fadeOut(300, function(){
			container.find('.input-new-lang').fadeIn(300).focus();
		});
	},
	handleNewLangForm: function(e){
		var input = $(e.currentTarget),
			containter = input.parent(),
			button = containter.find('button');

		if ( e.which == 13 ){ // save the new lang
			this.addNewLanguage(input.val());
		} else if ( e.which == 27){ // escape, cancel the new lang form
			this.closeAddLang();
		}
	},
	closeAddLang : function(){
		$('.list-language li.new-language input').val('').fadeOut(300, function(){
			$('.list-language li.new-language button').fadeIn(300);
		});
	},
	// event add new language 
	addNewLanguage : function(name) {
		if ( name == '' ) return false;

		var $container	=	$('#setting-language').find('ul.list-language'),
			lang_name	=	name,
			blockUi 	= new JobEngine.Views.BlockUi(),
			view 		= this;

		$.ajax ({
			url  : et_globals.ajaxURL,
			type : 'post',
			data : {
				lang_name : lang_name,
				action    : 'et-add-new-lang'
			},
			beforeSend : function(){
				blockUi.block($('.list-language li.new-language div.lang-field-wrap')); // block the input field
			},
			success : function(reponse){
				blockUi.unblock();
				// update view translation
				if(reponse.success) {					
					$container.find('.active').removeClass('active');
					$container.find('li.new-language').before('<li><a class="active" href="et-change-language" rel="' + reponse.lang_name + '">'+reponse.lang_name+'</a></li>');
					$('#base-language').append('<option value="'+reponse.lang_name+'">'+reponse.lang_name+'</option>');
					// hide the form
					$('.list-language li.new-language button').show();
					$('.list-language li.new-language input').val('').hide();
				}
			}
		});
		
	},
	loadTranslationForm : function (event) {
		if ( $(event.currentTarget).val() == '' ) return false;
		var $form 	=	$('#setting-language').find('form#language-list');
		var lang_name	=	$(event.currentTarget).val();
		$.ajax ({
			url  : et_globals.ajaxURL,
			type : 'post',
			data : {
				lang_name : lang_name,
				action    : 'et-load-translation-form'
			}
			,
			beforeSend : function(){
				$form.html('<img src="'+et_globals.imgURL+'/loading.gif" />');
			},
			success : function(reponse){
				// update view translation
				if(reponse.success) {
					$form.html (reponse.data);
				}else {

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
			alert(et_setting.payment_plan_error_msg);
		}
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
				$container.find('.icon').html('<img src="'+et_globals.imgURL+'/loading.gif" />');
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

	updateGeneralSetting : function ( event ) {
		var $new_value	=	$(event.currentTarget);
		
		if($new_value.hasClass('url')) {
			var val		=	$new_value.val();
			// if (val.length == 0) { return true; }
 
		    // if user has not entered http:// https:// or ftp:// assume they mean http://
		    if(!/^(https?|ftp):\/\//i.test(val)) {
		    	if (val.length != 0) {
			        val = 'http://'+val; // set both the value
			        $new_value.val(val); // also update the form element
			    }
		        //$(event.currentTarget).focus();
		    }
		}

		var $container	=	$new_value.parent('div');
		var $icon	=	$container.find('.icon');
		$.ajax ({
			url  : et_globals.ajaxURL,
			type : 'post',
			data : { 
				action 	   : 'et-update-general-setting',
				new_value  : $new_value.val(),
				option_name: $new_value.attr('name'),
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
				if ($new_value.val() == '' ){
					$icon.html('');
					$new_value.addClass('color-error');
					$icon.addClass('color-error');
					$icon.attr('data-icon', '!');
				}
			}
		});
		return false;
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
		params.beforeSend = function(){

		}
		params.success = function(data){
		}
		$.ajax(params);
	},

	updateMailTemplate : function (event) {
			var $target 	=	$(event.currentTarget),
				$textarea	=	$target.find('textarea'),
				mail_type	=	$textarea.attr ('name'),
				action 		=	'et-update-mail-template',
				content 	=	$textarea.val ();
			var $container	=	$target.parent('div');
			var $icon	=	$container.find('.icon');

			$.ajax ({
				url : et_globals.ajaxURL,
				type : 'post',
				data : {
					type : mail_type,
					data : content,
					action : action
				},
				beforeSend : function () {
					$icon.attr('data-icon', '');
					$icon.html('<img src="'+et_globals.imgURL+'/loading.gif" />');
				},
				success : function ( response) {
					if(response.success) {
						$icon.html('');
						$target.removeClass('color-error');
						$icon.removeClass('color-error');
						$icon.attr('data-icon', '3');
					} else {
						$icon.html('');
						$target.addClass('color-error');
						$icon.addClass('color-error');
						$icon.attr('data-icon', '!');
					}
				}
			});
		},

	triggerEditor : function (event) {
		event.preventDefault ();
		var $target 	=	$(event.currentTarget),
			$textarea 	=	$target.parents('.item').find('textarea');
		if($target.hasClass('activated')) {
			tinyMCE.execCommand('mceRemoveControl', false, $textarea.attr('id'));
			$target.removeClass('activated');
		}else {
			tinyMCE.execCommand('mceAddControl', false, $textarea.attr('id'));
			$target.addClass('activated');
		}
	},
	resetDefaultMailTemplate : function (event) {
		event.preventDefault ();
		var $target 	=	$(event.currentTarget),
			$textarea	=	$target.parents('.mail-template').find('textarea'),
			mail_type	=	$textarea.attr ('name'),
			action 		=	'et-set-default-mail-template';

		$.ajax ({
			url : et_globals.ajaxURL,
			type : 'post',
			data : {
				type : mail_type,
				action : action
			},
			beforeSend : function () {

			},
			success : function ( response) {
				$textarea.val (response.msg);
				var ed 			=	tinyMCE.get($textarea.attr('id'));
				ed.setContent (response.msg);
			}
		});
	},
	togglePaymentSetting : function (event) {
		event.preventDefault();
		var $target	=	$(event.currentTarget);
		$target.parents('.item').find('.payment-setting').slideToggle();
	},

	// update menu
	keyupLicenseKey: function(e){
		var view = this,
			input = $(e.currentTarget),
			value = input.val();
		if (this.previousValue != value){
			this.previousValue = value;
			if (this.timing) clearTimeout(this.timing);
			this.timing = setTimeout(function(){ view.update_license(value) }, 3000);
		}
	},
	changeLicenseKey : function(e){
		var view = this,
			input = $(e.currentTarget),
			value = input.val();
		if (this.timing) clearTimeout(this.timing);
		this.update_license(value);
	},

	update_license : function(value){
		var loading 		= $('.license-field'),
			loading_url 	= et_globals.imgURL + '/loading.gif',
			icon			= $('<span class="icon"></span>').append( $('<img src="' + loading_url + '">') );
		$.ajax({
			url: et_globals.ajaxURL,
			type: 'POST',
			data: {
				action 	: 'et-update-license-key',
				key 	: value
			},
			beforeSend: function(){
				loading.append(icon);
				// show the loading image
			},
			success: function(resp){
				// receive response from server
				icon.find('img').remove();
				icon.attr('data-icon', '3');
				setTimeout(function(){ $(icon).fadeOut('normal', function(){ $(this).remove(); }) }, 2000);
			}
		})
	},

	changeNotificationMail : function (e) {
		var view = this,
			input = $(e.currentTarget),
			value = input.val();
		if (this.timing) clearTimeout(this.timing);
		this.update_noficationMail(value);
	},

	update_noficationMail : function (value) {
		var loading 		= $('.notification-field'),
			loading_url 	= et_globals.imgURL + '/loading.gif',
			icon			= $('<span class="icon"></span>').append( $('<img src="' + loading_url + '">') );
		$.ajax({
			url: et_globals.ajaxURL,
			type: 'POST',
			data: {
				action 	: 'et-update-nofication-mail',
				key 	: value
			},
			beforeSend: function(){
				loading.append(icon);
				// show the loading image
			},
			success: function(resp){
				// receive response from server
				icon.find('img').remove();
				icon.attr('data-icon', '3');
				setTimeout(function(){ $(icon).fadeOut('normal', function(){ $(this).remove(); }) }, 2000);
			}
		});
	} ,

	updateLimitFreePlan : function ( event ) {
		var $target	=	$(event.currentTarget),
			value	=	$target.val();
		$.ajax({
			url: et_globals.ajaxURL,
			type: 'POST',
			data: {
				action 	: 'je-update-limit-free-plan',
				key 	: value
			},
			beforeSend: function(){
				// show the loading image
			},
			success: function(resp){
				// receive response from server
				
			}
		});
	},
	updateSocialValues : function(event){
		var $target	=	$(event.currentTarget),
			type 	=   $target.attr('name'),
			value	=	$target.val();
		$.ajax({
			url: et_globals.ajaxURL,
			type: 'POST',
			data: {
				action 	: 'et-social-save',
				value 	: value,
				name 	: type,
			},
			beforeSend: function(){
				// show the loading image
			},
			success: function(resp){
				// receive response from server
				
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

