(function ($) {
$(document).ready(function(){
	//backendRoute.on('et:changeMenu', on_menu_change);

	var ET_Update = Backbone.View.extend({
		el : '#et_update',
		events : {
			'keyup #license_key' : 'keyupLicenseKey',
			'change #license_key' : 'changeLicenseKey'
		},
		initialize: function(){
			this.previousValue = $('input#license_key').val();
		},
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
		}
	});
	
	new ET_Update();
});
})(jQuery);