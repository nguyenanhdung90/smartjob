(function ($) {
jQuery(document).ready (function ($) {

JobEngine.Views.Post_Job_Sidebar = Backbone.View.extend({
	
	el : $('div#static-text-sidebar'),

	events : {
		'click a.add-more' : 'add_more'
	},

	initialize : function () {		
		that	=	this;
		$('.view').each ( function () {
			var container	=	$(this).parents('.widget'),
				widget	=	new JobEngine.Views.JobStaticTextWidget({el : container , sidebar : 'post-job-sidebar' });
		});
	},
	
	add_more : function ( event ) {
		event.preventDefault ();
		this.addOne ();
	},

	addOne : function () {
		
		var widget	=	new JobEngine.Views.JobStaticTextWidget ();
		this.$('#sidebar').append (widget.render('').el);
		tinyMCE.execCommand('mceAddControl', false,'static-text');
	}

});

JobEngine.Views.JobStaticTextWidget = Backbone.View.extend ({
	
	tagName : 'div',
	className : 'widget widget-contact bg-grey-widget',

	events	: {
		'dblclick  .view'	: 'editWidget',
		'click a.edit'		: 'editWidget',
		'click a.apply'		: 'saveWidget',
		'click a.cancel'	: 'cancelWidget',
		'click a.remove'	: 'removeWidget'
	},

	sidebar : 'user-dashboard-sidebar',
	text : false,
	template : 	_.templateSettings = {
	    evaluate    : /<#([\s\S]+?)#>/g,
		interpolate : /\{\{(.+?)\}\}/g,
		escape      : /<%-([\s\S]+?)%>/g
	},

	template : _.template(
			'<textarea class="textarea" id="static-text">{{content}}</textarea>' +
			'<div class="btn-widget">' +
				'<a href="#" class="bg-btn-action border-radius apply">' + et_globals.txt_ok + '</a>' +
				'<a href="#" class="bg-btn-action border-radius cancel">' + et_globals.txt_cancel + '</a>' +
			'</div>'
		),
	initialize : function  () {
		this.sidebar =	$('#sidebar').attr('class');
		return this;
	},

	saveWidget : function  (event) {

		event.preventDefault ();

		var $target =	$(event.currentTarget),
			$widget =	$target.parents('.widget'),
			textarea=   $widget.find('textarea').val(),
			sidebar =   this.sidebar,
			id		=	$widget.attr('id');
		var block	=	new JobEngine.Views.BlockUi();
		$.ajax ({
			url : et_globals.ajaxURL,
			type : 'post',
			data : {
				action : 'et-save-job-static-text',
				html   : textarea,
				id     :  id,
				sidebar:  sidebar
			},
			beforeSend : function () {
				block.block($target);
			},
			success : function (response) {
				block.unblock();
				if(response.success) {
					$widget.html (
						'<div class="view" >'+response.msg+'</div>' +
						'<div class="btn-widget edit-remove">' +
							'<a href="#" class="bg-btn-action border-radius edit"><span class="icon" data-icon="p"></span></a>' +
							'<a href="#" class="bg-btn-action border-radius remove"><span class="icon" data-icon="#"></span></a>' +
						'</div>'
					);
					$widget.attr('id', response.id);
					$widget.removeClass('editting');
				} else {
					$target.html ('OK');
				}
			}

		});
		
	},

	editWidget : function ( event ) {
		event.preventDefault ();

		var $target =	$(event.currentTarget),
			$widget =	$target.parents('.widget');

		this.render($widget.find('.view').html());

		tinyMCE.execCommand('mceAddControl', false,'static-text');
		$widget.addClass('editting');
	},

	cancelWidget : function  ( event ) {

		event.preventDefault ();

		var $target =	$(event.currentTarget),
			$widget =	$target.parents('.widget');
		$widget.removeClass('editting');

		if( this.text ) {
			$widget.html (
				'<div class="view">'+this.text+'</div>' +
				'<div class="btn-widget edit-remove">' +
					'<a href="#" class="bg-btn-action border-radius edit"><span class="icon" data-icon="p"></span></a>' +
					'<a href="#" class="bg-btn-action border-radius remove"><span class="icon" data-icon="#"></span></a>' +
				'</div>'
				);
			this.text = false;
		} else {
			$widget.remove ();
		}

	},

	removeWidget : function (event) {
		event.preventDefault ();

		var $target =	$(event.currentTarget),
			$widget =	$target.parents('.widget'),
			sidebar =   this.sidebar,
			id		=	$widget.attr('id');

		$.ajax ({
			url : et_globals.ajaxURL,
			type : 'post',
			data : {
				action : 'et-remove-job-static-text',
				id     :  id,
				sidebar : sidebar
			},
			beforeSend : function () {
				$target.html ('<img src="'+et_globals.imgURL+'/loading.gif" />');
			},
			success : function (response) {
				if(response.success) {
					$widget.remove ();
				} else {
					$target.html ('<span class="icon" data-icon="#"></span>');
				}
			}
		});
	},

	render : function  (text) {
		this.text =	text;
		this.destroyEditing ();

		this.$el.html(this.template({content : text}));
		if(this.text === '') {
			this.$el.attr ('id', 0);
			this.$el.addClass ('editting');
		}
		
		return this;
	},

	destroyEditing : function () {
		$('.editting').html(
					'<div class="view">'+$('.editting').find ('textarea').val()+'</div>' +
					'<div class="btn-widget edit-remove">' +
						'<a href="#" class="bg-btn-action border-radius edit"><span class="icon" data-icon="p"></span></a>' +
						'<a href="#" class="bg-btn-action border-radius remove"><span class="icon" data-icon="#"></span></a>' +
					'</div>'
				);

		tinyMCE.execCommand('mceRemoveControl', false,'static-text');
		$('.editting').removeClass('editting');

	}

} );

new JobEngine.Views.Post_Job_Sidebar();
	
});
})(jQuery);