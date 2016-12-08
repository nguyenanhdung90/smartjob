var	backendRoute = {};
_.extend(backendRoute, Backbone.Events);

(function($){

$('document').ready(function(){
	$('a.engine-menu').bind('click', function(event){
		var container = $('#engine_setting_content');
		var current = $(this);
		var elements = $('a.engine-menu');
		var target = $(this).attr('href').substr(9);

		if ( !current.hasClass('disabled') ){
			$.changeSection( target );
			// navigate route
			Backbone.history.navigate('#section/' + target);
		}
		event.preventDefault();
	});

	backendRoute.on('et:removeBackendEvents', removeBackendEvents);
});

function removeBackendEvents(){
	// custom close method for some specific view to unbind children view
	if(typeof backendView.close === 'function'){
		backendView.close();
	}
	// destroy curent view
	backendView.unbind();
	backendView.undelegateEvents();
}

var blockUi = Backbone.View.extend({
	defaults : {
		image : et_backend.imgURL + '/loading.gif',
		opacity : '0.5',
		background_position : 'center center',
		background_color : '#ffffff'
	},

	isLoading : false,

	initialize : function(options){
		//var defaults = _.clone(this.defaults);
		options = _.extend( _.clone(this.defaults), options );

		var loadingImg = options.image;
		this.overlay = $('<div class="loading-blur loading"><div class="loading-overlay"></div><div class="loading-img"></div></div>');
		this.overlay.find('.loading-img').css({
			'background-image' : 'url(' + options.image + ')',
			'background-position' : options.background_position
			});

		this.overlay.find('.loading-overlay').css({
			'opacity'			: options.opacity,
			'filter'			: 'alpha(opacity=' + options.opacity*100 + ')',
			'background-color'	: options.background_color
			});
		this.$el.html( this.overlay );

		this.isLoading = false;
	},

	render : function(){
		this.$el.html( this.overlay );
		return this;
	},

	block: function(element){
		var $ele = $(element);
		if ( $ele.css('position') !== 'absolute' || $ele.css('position') !== 'relative'){
			$ele.css('position', 'relative');
		}

		this.isLoading = true;

		this.render().$el.show().appendTo( $ele );
	},

	unblock: function(){
		this.$el.remove();
		this.isLoading = false;
	},

	finish : function(){
		this.$el.fadeOut(500, function(){ $(this).remove();});
		this.isLoading = false;
	}
});

$.changeSection = function(target, subSection){
	var subSection	= subSection || '';
	var container 	= $('#engine_setting_content');
	var elements 	= $('a.engine-menu');
	var current 	= $('a[href="#section/' + target + '"]');
	var pageBlockUi 	= new blockUi({image: et_backend.imgURL + 'loading_big.gif'});

	var target 	= target || 'ET_MenuOverview';

	if ( !current.hasClass('disabled') || !current.hasClass('active') ){
		// call ajax request
		$.ajax({
			url : et_backend.ajaxURL,
			type : 'get',
			data : {
				action : 'change_admin_page',
				target : target,
				subSection : subSection
			},
			beforeSend : function(){
				// disable click event on all menu items
				elements.addClass('disabled');

				//backendRoute.trigger('et:loadingMenu', current);
				var li 			= current.parent();
				var content 	= $('#engine_setting_content');
				pageBlockUi.block(content);
			},
			success : function(html){
				elements.removeClass('disabled');
				if ( html == '-1' )
					console.log('finish error...');
				else {
					// change the link state to activated
					elements.removeClass('active');
					current.addClass('active');
					container.html( html );
				}
				var li 		= current.parent();
				var content 	= $('#engine_setting_content');
				pageBlockUi.unblock();

				backendRoute.trigger('et:removeBackendEvents', target);
				backendRoute.trigger('et:changeMenu', target);
			}
		});
	}
}

$.fn.engine_form = function( beforeSend, success){
	$(this).each(function(){
		var container = $(this);
		var button = container.find('engine-submit-btn');

		$.ajax({
			url : et_backend.ajaxURL,
			type : 'get',
			data : container.serialize(),
			beforeSend : function(){
				beforeSend();
			},
			success : function(data) {
				success(data);
			}
		}).done( function(data){ success(data) } );
	});	
}

jQuery(document).ready(function($){	
	//JobEngine.adminView = new JobEngine.Views.BackbendOverview();
	backendView = null;

	// set a subscription when page changed
	//backendRoute.on('et:changeMenu', JobEngine.on_change_menu_page_callback);

	// Router
	var adminRoute = Backbone.Router.extend({
		firstStart : true,
		routes : {
			'section/:name' : 'section',
			'section/:name/:item' : 'section'
		},
		section : function(name, subSection){
			subSection = subSection || '';
			if ( name ) {
				$.changeSection(name, subSection);
			}
			else if ( this.firstStart === true ) {
				$.changeSection(name, subSection);
			}
			this.firstStart = false;
		}
	});

	// create new route instance for change url
	new adminRoute();

	// start route
	Backbone.history.start({ root: et_backend.routerRoot });
});

})(jQuery);