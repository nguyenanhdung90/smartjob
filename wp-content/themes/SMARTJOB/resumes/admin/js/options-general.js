(function($){

	$(document).ready(function(){
		new optionGeneralView();
	});

	var optionGeneralView = Backbone.View.extend({
		el: '#setting-general',
		events: {
			'click .toggle-button' 	: 'onToggleFeature',
			'change .option-item' 	: 'onChangeOption',
			'focusout textarea' 		: 'updateHeadLine'
		},
		
		initialize: function(){	
			this.loading = new JobEngine.Views.BlockUi();
		},

		updateHeadLine : function (event) {
			var view	=	this,
			id 		= 	$(event.currentTarget).attr('id'),
			ed 		=	tinyMCE.get(id),
			new_value	=	ed.getContent (),
			name 	= $(event.currentTarget).attr('name');	
			
			view.updateOption (name, new_value , { 
				beforeSend: function(){
					view.loading.block($(event.currentTarget).parents('.form-item'));
				}, success: function(){
					view.loading.unblock();
				} }
			 );
		},

		updateOption: function(name, value, params){
			var params = $.extend( {
				url: ajaxurl,
				type: 'post',
				data: {
					action: et_options.ajax_action,
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
			})
		},

		onChangeOption: function(event){
			var name 	= $(event.currentTarget).attr('name'),
				value 	= $(event.currentTarget).val(),
				view 	= this;

			this.updateOption(name, value, {
				beforeSend: function(){
					view.loading.block($(event.currentTarget));
				},
				success: function(resp){
					view.loading.unblock();
				}
			});
		}
	})

	//JobEngine.Models.User.prototype.initialize.call();
})(jQuery);