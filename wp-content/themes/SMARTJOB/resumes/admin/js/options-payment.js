(function($){

$(document).ready(function(){
	new optionMail();
});

JobEngine.Models.ResumePlan = Backbone.Model.extend({
	initialize : function(){},
	parse : function(resp){			
		if ( resp.data ){
			return resp.data.paymentPlan;
		}
	},
	remove : function(options){
		
		this.sync('delete', this, options);
	},
	add : function(options){
		this.sync('add', this, options);
	},

	sync	: function(method, model, options) {
		options	= options || {};
		var success	= options.success || function(resp){ };
		var beforeSend	= options.beforeSend || function(){ };
		var params		= _.extend(ajaxParams, options);
		var thisModel	= this;
		var action	= 'et_sync_resume_plan';

		if ( options.data ){
			params.data = options.data;
		}
		else {
			params.data = model.toJSON();
		}

		params.success = function(resp) {
			
			thisModel.set( thisModel.parse(resp) );
			switch( method ){
				case 'add':
					if( resp.success ) {
						pubsub.trigger('je:setting:paymentPlanAdded', thisModel, resp);
					} else {
						alert(resp.msg);
					}
					
					break;
				case 'delete':
					pubsub.trigger('je:setting:resumePlanRemoved', thisModel, resp);
					thisModel.trigger('remove');
					//thisModel.destroy();
					break;
				case 'update':
					if( resp.success ) {
						thisModel.trigger('updated');
						pubsub.trigger('je:setting:resumetPlanUpdated', thisModel, resp);
					} else {
						alert(resp.msg);
					}
					break;
				default :
					pubsub.trigger('je:setting:resumePlanSynced', thisModel, resp);
					break;
			}
			success(resp);
		};

		params.beforeSend = function(){
			beforeSend();
		};
		//params.method	= method;
		params.data = jQuery.param( {method : method, action : action, content : params.data });

		return jQuery.ajax(params);
	}
});


var optionMail = Backbone.View.extend({
	el: '#setting-payment',
	events: {
		
		'click .toggle-button' 					: 'onToggleFeature',
		'submit form#resume_plans_form'			: 'submitFormAddPlan'
		
		//'click .mail-template  .reset-default'	: 'resetDefaultMailTemplate',
	},
	initialize: function(){		
		var view = this;
		var appView =	this;
		this.initPaymentPlans();	
		this.loading = new JobEngine.Views.BlockUi();
		$('.sortable').sortable({
			axis: 'y',
			handle: 'div.sort-handle'
		});
		$('ul.pay-plans-list').bind('sortupdate', function(e, ui){
			appView.updatePaymentOrder();
		});
		
	},
	initPaymentPlans : function(){
		// initilize payment plans
		var planCollection = new JobEngine.Views.PaymentPlanCollection({el : 'ul.pay-plans-list' });
	},

	submitFormAddPlan : function(event){
			
		event.preventDefault();
		var form = $(event.target);
		var name='abc';
		var element 	= $(event.currentTarget);
		var container 	= element.parent();
		var button = form.find('.engine-submit-btn');		
		
		var model = new JobEngine.Models.ResumePlan({
			title: form.find('input[name=payment_name]').val(),
			price: form.find('input[name=payment_price]').val(),
			duration: form.find('input[name=payment_duration]').val(),
			featured: form.find('input[type=checkbox][name=payment_featured]').is(':checked') ? 1 : 0,
			description: form.find('input[name=description]').val()
		}),
			loading = new JobEngine.Views.LoadingButton({el : button});
		
		model.add({
			beforeSend : function() {
				loading.loading();
			},
			success : function( resp ){
				loading.finish();
				if(resp.success)
					form.find('input').val('');

			}
		});
		
		return false;
	},

	onToggleFeature: function(event){		
		event.preventDefault();
		var element 	= $(event.currentTarget);
		var container 	= element.parent();
		//alert(container);		
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
	updatePaymentOrder : function(){
		var order = $('ul.pay-plans-list').sortable('serialize');
		//var params = ajaxParams;
		params.data = {
			action: 'et_sort_resume_plan',
			content : {
				order: order
			}
		};
		params.before = function(){	}
		params.success = function(data){
		}
		$.ajax(params);
	},
});


JobEngine.Views.PaymentPlanCollection = Backbone.View.extend({
	el : 'ul.pay-plans-list',
	initialize: function(){
		var view = this;
		view.views = [];
		view.collection = new JobEngine.Collections.Payments( JSON.parse( $('#payment_plans_data').html() ) );		
		view.$el.find('li').each(function(index){
			var $this = $(this);
			view.views.push( new JobEngine.Views.ResumeItem({
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
		alert('remove View');
		var thisView = this;
		var viewToRemove = _.filter( thisView.views, function(vi){ 
			return vi.model.get('id') == model.get('id');
		})[0];

		_.without(thisView.views, viewToRemove);

		viewToRemove.fadeOut();
	},
	addView : function(model){

		var view = new JobEngine.Views.ResumeItem({model: model});
		this.views.unshift( view );

		view.render().$el.hide().prependTo( this.$el ).fadeIn();
	}
});

JobEngine.Collections.Payments = Backbone.Collection.extend({
	//model: JobEngine.Models.PaymentPlan,
	model: JobEngine.Models.ResumePlan,
	initialize: function(){ }
});

//	=============================================
//	View Payment Edit Form
//	=============================================
JobEngine.Views.PaymentEditForm = Backbone.View.extend({
	tagName : 'div',
	events : {
		'submit form.edit-plan' : 'savePlan',
		'click .cancel-edit' : 'cancel'
	},
	template : '', //_.template( $('#template_edit_form').html() ),
	render : function(){
		this.$el.html( this.template( this.model.toJSON() ) );
		return this;
	},
	initialize : function(options){
		// apply template for view
		_.templateSettings = {
		    evaluate    : /<#([\s\S]+?)#>/g,
			interpolate : /\{\{(.+?)\}\}/g,
			escape      : /<%-([\s\S]+?)%>/g
		};
		if ( $('#template_edit_form').length > 0 )
			this.template = _.template( $('#template_edit_form').html() );

		this.model.bind('update', this.closeForm, this);
		this.appear();
	},

	appear : function(){
		this.render().$el.hide().appendTo( this.options.parent ).slideDown();
	},

	savePlan : function(event){
		event.preventDefault();
		var form = this.$el.find('form');
		var view = this;
		this.model.set({
			title: form.find('input[name=title]').val(),
			price: form.find('input[name=price]').val(),
			duration: form.find('input[name=duration]').val(),
			// featured: form.find('input[type=checkbox][name=featured]').is(':checked') ? 1 : 0,
			// quantity: form.find('input[name=quantity]').val() ,
			description: form.find('input[name=description]').val()
		});
		
		this.model.save(this.model.toJSON(), {
			beforeSend : function(){
				view.loading = new JobEngine.Views.LoadingButton({el : form.find('#save_resume_playment_plan') });
				view.loading.loading();
			},
			success : function(model, resp){
				view.loading.finish();
				if(resp.success)
					view.closeForm(); 

			}
		});

	},
	cancel : function(event){
		event.preventDefault();
		this.closeForm();
	},
	closeForm : function(){
		this.$el.slideUp( 500, function(){ $(this).remove(); });
	}
});

JobEngine.Views.ResumeItem = Backbone.View.extend({
	tagName : 'li',
	className : 'item',
	events : {
		'click a.act-edit' : 'editPlan',
		'click a.act-del' : 'removePlan'
	},
	initialize: function(){
		this.model.bind('updated', this.render, this );
		this.model.bind('detroy', this.fadeOut, this);
		this.model.bind('remove', this.fadeOut, this);
	},
	template : _.templateSettings = {
				    evaluate    : /<#([\s\S]+?)#>/g,
					interpolate : /\{\{(.+?)\}\}/g,
					escape      : /<%-([\s\S]+?)%>/g
	},
	template : _.template("<div class='sort-handle'></div><span>{{title}}</span> {{ backend_text }}" +
		"<div class='actions'>" +
			"<a href='#' title='Edit' class='icon act-edit' rel='id' data-icon='p'></a> " +
			"<a href='#' title='Delete' class='icon act-del' rel='id' data-icon='D'></a>" +
		"</div>"),

	render : function(){
		this.$el.html( this.template(this.model.toJSON()) ).attr('data', this.model.id ).attr('id', 'payment_' + this.model.id);
		return this;
	},

	blockItem : function(){
		this.blockUi = new JobEngine.Views.BlockUi();
		this.blockUi.block(this.$el);
	},

	unblockItem: function(){
		this.blockUi.unblock();
	},

	editPlan : function(event){
		event.preventDefault();

		if ( this.editForm && this.$el.find('.engine-payment-form').length > 0 ){
			this.editForm.closeForm(event);
		}
		else{
			this.editForm = new JobEngine.Views.PaymentEditForm({ model: this.model, parent: this.$el });
		}

	},

	removePlan : function(event){
		
		
		// ask user if he really want to delete
		if ( !confirm(et_globals.confirm_delete_plan) ) return false;
		
		event.preventDefault();
		var view = this;

		// call delete request
		this.model.remove({
			beforeSend: function(){
				view.blockItem();
			},
			success: function(resp){
				view.unblockItem();
			}
		});
	},

	fadeOut : function(){
		this.$el.fadeOut(function(){ $(this).remove(); });
	}
});
// for delete a resume plan

JobEngine.Views.JobTypeItem = Backbone.View.extend({
	tagName : 'li',

	events : {
		'click a.act-del'			: 'displayConfirm',
		'change input.type-name'	: 'updateName',
		'click .input-form .cursor' : 'changeColor'
	},
	template : _.templateSettings = {
	    evaluate    : /<#([\s\S]+?)#>/g,
		interpolate : /\{\{(.+?)\}\}/g,
		escape      : /<%-([\s\S]+?)%>/g
	},

	template: _.template('<div class="container"><div class="sort-handle"></div><div class="controls controls-1"><a class="button act-del" rel="{{ id }}">' +
					'<span class="icon" data-icon="*"></span>' +
				'</a> </div>'+
				'<div class="input-form input-form-1">' +
					'<div class="cursor <# if ( color ) { #>color-{{color }}<# } #>"><span class="flag"></span></div>' +
					'<input class="bg-grey-input type-name <# if ( color ) { #>color-{{ color }}<# } #>" rel="{{ id }}" type="text" value="{{ name }}">' +
				'</div></div>' ),

	initialize: function(){
	},

	styleSelect : function(){
		this.$(".select-style select").each(function(){
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
	},

	render : function(){
		this.$el.append( this.template(this.model.toJSON()) ).addClass('job-type').addClass('job-type-' + this.model.get('id')).attr('data',  this.model.get('id')).attr('id', 'jobtype_' + this.model.get('id'));
		return this;
	},

	displayConfirm : function(event){
		var view 				= this,
			$html 	= $($('#temp_jobtype_delete_confirm').html()),
			container = this.$('.container');

		$html.find('option[value=' + view.model.get('id') + ']').remove();

		container.fadeOut('normal', function(){
			view.$el.append( $html.hide().fadeIn('normal', function(){
				$html.find('button.accept-btn').bind('click', function(){
					view.confirmDelete($html.find('option:selected').val());
				});

				$html.find('a.cancel-del').bind('click', function(event){
					$html.fadeOut('normal', function(){
						container.fadeIn();
					});
				});
			}));
			view.styleSelect();
		});
	},

	updateName : function(event){
		var $this = $(event.currentTarget),
			loadingView = new JobEngine.Views.LoadingEffect(),
			view = this;

		this.model.set('name', $this.val());
		this.model.save(this.model.toJSON(), {
			beforeSend : function(){
				loadingView.render().$el.appendTo( view.$el.find('.controls') );
			},
			success: function(){
				loadingView.finish();
			}
		});
	},

	confirmDelete : function(default_type){
		if (!default_type) return false;

		var view = this,
			loadingView = new JobEngine.Views.LoadingEffect(),
			blockUi = new JobEngine.Views.BlockUi();

		this.model.remove({
			data: {
				default_type : default_type
			},
			beforeSend : function(){
				//loadingView.render().$el.appendTo( view.$el );
				blockUi.block(view.$el);
			},
			success : function(data){
				//loadingView.finish();
				blockUi.unblock();
				if ( data.success )
					view.$el.fadeOut('normal', function(){ $(this).remove(); });
			}
		});
	},

	changeColor: function(event){
		var $this = $(event.currentTarget),
			container = $this.closest('li');

		event.stopPropagation();
		$('.job-type').removeClass('current-job-type');
		if ( !container.hasClass('new-job-type') )
			container.addClass('current-job-type');
		jQuery(".input-form .bar-flag").remove();

		flag_current = $this.attr("class");
		flag_current = flag_current.replace("cursor","");
		flag_current = flag_current.replace(" color-","");


		flag_choose = "";
		for(i_color=1; i_color<40; i_color++) {
			
			flag_choose += "<div class=\"color-" + i_color;

			if (i_color == flag_current) flag_choose += " active";

			flag_choose += "\" data='" + i_color + "'>" +
							"<span class=\"flag\"></span>" +
							"</div>";
		}

		var popup = $("<div class=\"bar-flag\">" + flag_choose + "</div>");

		$this.parent().append(popup);

		// remove tooltip when click outside tooltip
		jQuery('body').unbind('click');
		jQuery('body').bind('click', function(e){
			var tooptip = $('.bar-flag');
			if ( !tooptip.is(':hidden') && tooptip.has( e.target ).length === 0 ){
				tooptip.remove();
			}
			
		});
	}
});
// end delete a re



})(jQuery);