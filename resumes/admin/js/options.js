(function($){

	$(document).ready(function(){
		
	
	var SettingRouter = Backbone.Router.extend({
		routes :{	
			'section/:section' : 'openSection'
		},

		openSection: function(section){
			var target 	= $('a[href="#section/' + section + '"]'),
				content = $('#' + section);

			$('.inner-menu a.section-link').removeClass('active');
			$('.et-main-main').hide();
			target.addClass('active');
			content.show();
		}
	});


	OptionsView = Backbone.View.extend({
		el : '#resume_settings',
		initialize: function(){
			var view = this;

			this.loading = new JobEngine.Views.BlockUi();

			// setup router
			this.router  = new SettingRouter();
			Backbone.history.start();

			// setting up textarea auto resize
			this.$el.find('textarea').on('keyup', function (e){
			    $(this).css('height', 'auto' );
			    $(this).height( this.scrollHeight );
			});
			this.$el.find('textarea').keyup();

			
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
						if(parseInt(value) == 0)
							$('#wizard-settings-resume').hide();
						else 
							$('#wizard-settings-resume').show();
					}
				}
			})
		}
	});

	new OptionsView();
		
		
	});
	
}(jQuery));