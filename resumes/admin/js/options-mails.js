(function($){

$(document).ready(function(){
	new optionMail();
});

var optionMail = Backbone.View.extend({
	el: '#setting-mails',
	events: {
		'click .mail-template .trigger-editor' 	: 'triggerEditor',
		//'focusout .mail-template'				: 'onChangeMailTemplate',
		//'change .mail-template textarea' 		: 'onChangeMailTemplate',
		'click .mail-template .reset-default' 	: 'onResetMail',
		'click .desc .item .payment'			: 'togglePaymentSetting',
		'click .toggle-button' 					: 'onToggleFeature',

		'focusout .mail-template textarea' 		: 'changeMail'
		//'click .mail-template  .reset-default'	: 'resetDefaultMailTemplate',
	},
	initialize: function(){
		var view = this;
		$('.payment-setting').toggle(); 
		this.loading = new JobEngine.Views.BlockUi();
		$('.mail-control-btn').toggle();
		$('.btn-template-help').click(function(){
			$('.mail-control-btn').slideToggle(300);
			return false;
		});

	},

	changeMail : function (event) {
		var view	=	this,
			id 		= 	$(event.currentTarget).attr('id'),
			ed 		=	tinyMCE.get(id),

			new_value	=	ed.getContent (),
			name 	= $(event.currentTarget).attr('name');

		view.updateMail(name, new_value, {
			beforeSend: function(){
				view.loading.block($(event.currentTarget).parents ('.mail-template'));
			}, success: function(){
				view.loading.unblock();
			}
		}); 

		$(event.currentTarget).closest('.mail-template').find('a.trigger-editor').addClass ('activated');
	} ,

	onToggleFeature: function(event){
		event.preventDefault();
		var element 	= $(event.currentTarget);
		var container 	= element.parent();
		var name 		= element.attr('data');
		var value 		= element.hasClass('deactive') ? 0 : 1;
		var view  		= this;

		this.updateOption(name, value, {
			beforeSend: function(){
				view.loading.block(container);
			},
			success: function(resp){
				view.loading.unblock();

				if (resp.success){

					container.children('a').removeClass('selected');
					element.addClass('selected');
				}
			}
		});
		return false;
	},
	updateMail : function(name, value, params){

		var params = $.extend( {
			url: ajaxurl,
			type: 'post',
			data: {
				action: et_options.ajax_mail_action,
				content: {
					name: name,
					value: value,
				}
			},
			beforeSend: function(){},
			success: function(){}
		}, params );

		$.ajax(params);
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
	//danng add
	togglePaymentSetting : function (event) {
		event.preventDefault();
		var $target	=	$(event.currentTarget);
		$target.parents('.item').find('.payment-setting').slideToggle();
	},

	updateOption: function(name, value, params){

			var params = $.extend( {
				url: ajaxurl,
				type: 'post',
				data: {
					action: et_options.ajax_action,//et_update_option
					content: {
						name: name,
						value: value,
					}
				},
				beforeSend: function(){},
				success: function(){

				}
			}, params );

			$.ajax(params);
	},
	resetEmail : function(name, params){
		var params = $.extend( {
			url: ajaxurl,
			type: 'post',
			data: {
				action: 'et_reset_mail',
				content: {
					mail: name
				}
			},
			beforeSend: function(){},
			success: function(){}
		}, params );

		$.ajax(params);

	},

	onResetMail : function (event) {
		event.preventDefault ();
		var $target 	=	$(event.currentTarget),
			$textarea	=	$target.parents('.mail-template').find('textarea'),
			mail_type	=	$textarea.attr ('name');
		this.resetEmail(mail_type, {
			beforeSend: function(){
			},
			success: function(resp){

				if (resp.success){
					 var ed 			=	tinyMCE.get($textarea.attr('id'));
					 ed.setContent(resp.data.template);
				}
			}
		});

	}
});
})(jQuery);