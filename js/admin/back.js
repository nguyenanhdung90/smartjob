/*************************************************
//                                              //
//              BACK END MODELS					//
//                                              //
*************************************************/
/**
 * Model Payment Plan
 * used in backend
 */
// JobEngine.Models.PaymentPlan = Backbone.Model.extend({
//	initialize: function(){}
// });

/**
 * Model Job Type
 */
 (function ($) {
JobEngine.Models.JobType = Backbone.Model.extend({
	initialize	: function(){},
	parse		: function(resp){
		if ( resp.data )
			return resp.data;
	},
	remove		: function(options){
		var params		= _.extend(ajaxParams, options);

		var action	= 'et_sync_jobtype';

		params.data = jQuery.extend(this.toJSON(), options.data);

		params.data = jQuery.param( {method : 'delete', action : action, content : params.data });

		return jQuery.ajax(params);
	},
	sync		: function(method, model, options) {
		var params		= _.extend(ajaxParams, options);

		var action	= 'et_sync_jobtype';

		params.data	= model.toJSON();
		//params.method	= method;
		params.data = jQuery.param( {method : method, action : action, content : params.data });

		return jQuery.ajax(params);
	}
});

/**
 * Model Job Category
 */
JobEngine.Models.JobCategory = Backbone.Model.extend({
	initialize	: function(){},
	parse		: function(resp){
		if ( resp.data )
			return resp.data;
	},
	remove: function(options){
		var params		= _.extend(ajaxParams, options);

		var action	= 'et_sync_jobcategory';

		params.data = jQuery.extend(this.toJSON(), options.data);

		params.data = jQuery.param( {method : 'delete', action : action, content : params.data });

		return jQuery.ajax(params);
	},

	sync		: function(method, model, options) {
		var params		= _.extend(ajaxParams, options);

		var action	= 'et_sync_jobcategory';

		params.data	= model.toJSON();
		//params.method	= method;
		params.data = jQuery.param( {method : method, action : action, content : params.data });

		return jQuery.ajax(params);
	}
});

JobEngine.Models.PaymentPlan = Backbone.Model.extend({
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
		var action	= 'et_sync_paymentplan';

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
					pubsub.trigger('je:setting:paymentPlanAdded', thisModel, resp);
					break;
				case 'delete':
					pubsub.trigger('je:setting:paymentPlanRemoved', thisModel, resp);
					thisModel.trigger('remove');
					//thisModel.destroy();
					break;
				case 'update':
					thisModel.trigger('updated');
					pubsub.trigger('je:setting:paymentPlanUpdated', thisModel, resp);
					break;
				default :
					pubsub.trigger('je:setting:paymentPlanSynced', thisModel, resp);
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

/*************************************************
//                                              //
//              BACK END COLLECTIONS			//
//                                              //
*************************************************/
JobEngine.Collections.Payments = Backbone.Collection.extend({
	model: JobEngine.Models.PaymentPlan,
	initialize: function(){ }
});

/*************************************************
//                                              //
//              BACK END VIEWS					//
//                                              //
*************************************************/

//  View Payment Item
JobEngine.Views.PaymentItem = Backbone.View.extend({
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

	template : _.template("<div class='sort-handle'></div><span>{{ title }}<# if( featured == 1) { #> <em class='icon-text'>^</em><# } #></span> {{ backend_text }}" +
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

//	=============================================
//	View Payment Edit Form
//	=============================================
JobEngine.Views.PaymentEditForm = Backbone.View.extend({
	tagName : 'div',
	events : {
		'submit form.edit-plan' : 'savePlan',
		'click .cancel-edit' : 'cancel'
	},
	template : _.templateSettings = {
		    evaluate    : /<#([\s\S]+?)#>/g,
			interpolate : /\{\{(.+?)\}\}/g,
			escape      : /<%-([\s\S]+?)%>/g
	},

	template : '', //_.template( $('#template_edit_form').html() ),
	render : function(){
		this.$el.html( this.template( this.model.toJSON() ) );
		return this;
	},
	initialize : function(options){
		// apply template for view
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
			featured: form.find('input[type=checkbox][name=featured]').is(':checked') ? 1 : 0,
			quantity: form.find('input[name=quantity]').val()
		});
		this.model.save(this.model.toJSON(), {
			beforeSend : function(){
				view.loading = new JobEngine.Views.LoadingButton({el : form.find('#save_playment_plan') });
				view.loading.loading();
			},
			success : function(){
				view.loading.finish();
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

/**
 * Job Type item view in backend
 * events: delete, change color
 */
JobEngine.Views.JobTypeItem = Backbone.View.extend({
	tagName : 'li',

	events : {
		'click a.act-del'			: 'displayConfirm',
		'change input.type-name'	: 'updateName',
		'click .input-form .cursor' : 'changeColor'
	},

	template: _.template('<div class="container"><div class="sort-handle"></div><div class="controls controls-1"><a class="button act-del" rel="{{id }}">' +
					'<span class="icon" data-icon="*"></span>' +
				'</a> </div>'+
				'<div class="input-form input-form-1">' +
					'<div class="cursor <# if ( color ) { #>color-{{color }}<# } #>"><span class="flag"></span></div>' +
					'<input class="bg-grey-input type-name <# if ( color ) { #>color-{{color}}<# } #>" rel="{{id}}" type="text" value="{{name}}">' +
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

// ===============================
// Backend Job Category Item View
// ===============================
JobEngine.Views.JobCategoryItem = Backbone.View.extend({
	tagName : 'li',
	events : {
		'click a.act-del'					: 'displayReplaceList',
		'click a.act-open-form'				: 'openForm',
		'submit .form-sub-cat' 				: 'addSubCat',
		'click .form-sub-cat a.act-add-sub' : 'addSubCat',
		'keyup .form-sub-cat a.act-add-sub' : 'keyupSubCat',
		'change input.cat-name'				: 'updateName',
		'keyup .new-category'				: 'cancelAddition'
	},

	template: _.template('<div class="container"> \
						<div class="sort-handle"></div> \
					<div class="controls controls-2"> \
						<a class="button act-open-form" rel="{{ id }}" title=""> \
							<span class="icon" data-icon="+"></span> \
						</a> \
						<a class="button act-del" rel="{{ id }}"> \
							<span class="icon" data-icon="*"></span> \
						</a> \
					</div> \
					<div class="input-form input-form-2"> \
						<input class="bg-grey-input cat-name" rel="{{id}}" type="text" value="{{name}}"> \
					</div> \
				</div>'),

	sub_template : _.template('<li class="form-sub-cat disable-sort" id="cat_{{id}}"> \
					<div class="container">\
						<!--	<div class="sort-handle"></div>  --> \
						<div class="controls controls-2">\
							<a class="button act-add-sub" title=""> \
								<span class="icon" data-icon="+"></span> \
							</a>\
						</div>\
						<div class="input-form input-form-2"> \
							<form action="" class="">\
								<input type="hidden" name="parent" value="{{id}}">\
								<input class="bg-grey-input new-category" name="name" type="text" placeholder="Enter category name"> \
							</form> \
						</div> \
					</div>\
				</li>'),

	initialize: function(){},

	render : function(){
		this.$el.append( this.template(this.model.toJSON()) ).addClass('category-item').attr('id', 'cat_' + this.model.get('id'));
		return this;
	},

	openForm : function(event){
		var view = this;
		var id = $(event.currentTarget).attr('rel');

		if ( this.model.get('id') == id ){
			$html = this.sub_template({id : id});
			if (view.$el.find('ul').length == 0)
				view.$el.append('<ul>');
			view.$el.children('ul').append($html);
		}
	},

	keyupSubCat: function(event){
		event.preventDefault();
		if (keyup.which == 13)
			this.addSubCat(event);
		return false;
	},

	addSubCat : function(event){
		event.stopPropagation();
		event.preventDefault();
		var view = this;
		var formContainer = view.$el.children('ul').children('li.form-sub-cat');
		var form = formContainer.find('form'),
			loadingView = new JobEngine.Views.LoadingEffect();

		if (form.find('input[name=name]').val() == '') return false;

		var model = new JobEngine.Models.JobCategory({
			parent	: form.find('input[name=parent]').val(),
			name	: form.find('input[name=name]').val()
		});

		model.save(model.toJSON(), {
			beforeSend : function(){
				loadingView.render().$el.appendTo( formContainer.find('.controls') );
			},
			success: function(model, resp){
				if (resp.success){
					loadingView.finish();
					subView = new JobEngine.Views.JobCategoryItem({model: model});
					$(subView.render().el).insertBefore(view.$el.children('ul').find('li:last'));
					formContainer.remove();
				}
		}});
	},

	updateName: function(event){
		var current = $(event.currentTarget),
			id		= current.attr('rel'),
			val		= current.val(),
			loadingView = new JobEngine.Views.LoadingEffect(),
			view = this;

		if ( id == this.model.get('id') ){
			this.model.set('name', val);
			this.model.save(this.model.toJSON(), {
				beforeSend : function(){
					loadingView.render().$el.appendTo( view.$el.children('.container').find('.controls') );
				},
				success: function(model, resp){
					loadingView.finish();
				}
			});
		}
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

	displayReplaceList: function(event){
		event.stopPropagation();

		var $html 		= $($('#temp_jobcat_delete_confirm').html()),
			container  	= this.$el.children('.container'),
			view 		= this;

		if (this.$el.find('ul > li').length > 0){
			alert(et_setting.del_parent_cat_msg);
			return false;
		}
		// hide the container 
		container.fadeOut('normal', function(){			
			$html.insertAfter(container).hide().fadeIn('normal', function(){
				$html.find('button.accept-btn').bind('click', function(event){
					var def = $html.find('select').val();
					view.deleteCat(def);
				});
				$html.find('a.cancel-del').bind('click', function(event){
					$html.fadeOut('normal', function(){
						container.fadeIn();
					});
				});
				$html.bind('keyup', function(event){
					if (event.which == 27)
						$html.fadeOut('normal', function(){
							container.fadeIn();
						});
				});
			});
			// apply styling
			$html.find('option[value=' + view.model.get('id') + ']').remove();
			view.styleSelect();
		});
	},

	// perform delete action
	deleteCat : function(def){
		//event.stopPropagation();
		//if ( !confirm(et_globals.confirm_delete_category) ) return false;
		//if (!def) return false;
		
		var view = this,
			blockUi = new JobEngine.Views.BlockUi(),
			loadingView = new JobEngine.Views.LoadingEffect();
		//var id = $(event.currentTarget).attr('rel');

		//if ( id == view.model.get('id') ){
			this.model.remove({
				data : {
					default_cat : def
				},
				beforeSend : function(){
					blockUi.block(view.$el)
					//loadingView.render().$el.appendTo( view.$el.find('.controls') );
				},
				success : function(data){
					blockUi.unblock();
					//loadingView.finish();
					if ( data.success )
						view.$el.fadeOut('normal', function(){ $(this).remove(); });
				}
			});
		//}
	},

	cancelAddition : function(event){
		if (event.keyCode == 27) {
			this.closeForm();
		}
	},

	closeForm : function(event){
		this.$el.children('ul').children('li.form-sub-cat').remove();
		if (this.$el.children('ul').find('li').length == 0)
			this.$el.children('ul').remove();
	}
});

// ===============================
// Backend Job Item View
// ===============================
JobEngine.Views.BackendJob = Backbone.View.extend({
	tagName : 'li',
	events : {
		'click .act-approve' : 'approveJob',
		'click .act-reject' : 'rejectJob'
	},

	template : _.template('<div class="method">' +
			'<a class="color-active act-approve" href="#"><span class="icon" data-icon="3"></span></a>' +
			'<a class="color-orange act-reject" href="#"><span class="icon" data-icon="*"></span></a>' +
		'</div>' +
		'<# if ( status == "pending" ) { #>' +
			'<a class="color-red error" href="#"><span class="icon" data-icon="!"></span></a>' +
		'<# } #>' +
		'<div class="content" data-id="{{id }}">' +
			'<a target="_blank" href="{{ permalink }}" class="job job-name" target="_blank">{{ title }}</a> at <a target="_blank" href="{{ author_url }}" target="_blank" class="company">{{ author }}</a>' +
		'</div>'),

	initialize: function(){
		//_.bindAll(this);
	},

	render : function(){
		this.$el.append( this.template(this.model.toJSON()) );
		return this;
	},

	approveJob : function(event){
		event.preventDefault();

		var view = this;
		var blockUi = new JobEngine.Views.BlockUi();		
		this.model.approve({
			beforeSend: function(){
				blockUi.block(view.$el);
			},
			success : function(model, resp){
				blockUi.unblock();
			}
		});
	},

	rejectJob : function(event){
		event.preventDefault();
		pubsub.trigger('je:job:onReject', {model : this.model});

	}
});

// ===============================
// Backend Company Item View
// ===============================
JobEngine.Views.BackendCompany = Backbone.View.extend({
	tagName : "li",

	events :  {
		'click a.approve_view'	 	: 'approveView',
		'click a.reject_view' 		: 'rejectView'
	},

	className : 'company-item',


	template : _.template('<div class="content">' +
			'<a href="{{ permalink }}" class="job">{{ display_name }}</a> <a href="{{ permalink }}" class="company">{{count_text}}</a>' +
		'</div>'),
	initialize: function(){
		this.blockUi = new JobEngine.Views.BlockUi();
	},

	render : function(){
		this.$el.append( this.template( this.model.toJSON() ) ).addClass(this.className).attr('data-id', this.model.get('id'));
		return this;
	},

	blockItem : function(){
		this.blockUi.block(this.$el);
	},

	unblockItem : function(){
		this.blockUi.unblock();
	},
	/**
	 * approve user view resume
	*/
	approveView : function (e) {
		e.preventDefault();
		var view = this;
		this.model.set('view_resume_status', 'publish');

		this.model.save('view_resume_status', 'publish', {
			beforeSend : function () {
				view.blockItem();
			},
			success : function (model, resp ) {
				view.unblockItem();
				if(resp.success)
					view.$el.remove();
			}
		});
	},
	/**
	 * reject user view reusme
	*/
	rejectView : function (e) {
		e.preventDefault();
		var view = this;

		this.model.set('view_resume_status', 'reject');
		this.model.save('view_resume_status', 'reject' ,{
			beforeSend : function () {
				view.blockItem();
			},
			success : function (model, resp ) {
				view.unblockItem();
				if(resp.success)
					view.$el.remove();
			}
		});
	}

});

//backendView = null;

JobEngine.on_change_menu_page_callback = function(target){
	// tinyMCE.execCommand('mceRemoveControl', false, 'site_demon');
	// tinyMCE.execCommand('mceRemoveControl', false, 'cash-message');
	// tinyMCE.execCommand('mceRemoveControl', false, 'register-mail');
	// tinyMCE.execCommand('mceRemoveControl', false, 'forgot-pass-mail');
	// tinyMCE.execCommand('mceRemoveControl', false, 'reset-pass-mail');
	// tinyMCE.execCommand('mceRemoveControl', false, 'apply-mail');
	// tinyMCE.execCommand('mceRemoveControl', false, 'remind-mail');
	// tinyMCE.execCommand('mceRemoveControl', false, 'reject-mail');
	// tinyMCE.execCommand('mceRemoveControl', false, 'approve-mail');
	// tinyMCE.execCommand('mceRemoveControl', false, 'archive-mail');

	$('trigger-editor a').each (function () {
		$(this).removeClass ('activated');
	});
	
	switch (target) {
		case 'ET_MenuSettings':
			backendView = new JobEngine.Views.BackendSetting();
			//tinyMCE.execCommand('mceAddControl', false, 'site_demon');
			break;
			
		case 'ET_MenuPayment' :
			backendView =	new JobEngine.Views.PaymentManager ();
			break;

		case 'ET_MenuCompanies' :
			backendView =	new JobEngine.Views.BackendCompanies ();
			break;

		case 'ET_MenuCustomization' :
			backendView =	new JobEngine.Views.BackendCustomization ();
			break;

		case 'ET_MenuWizard' :
			backendView =	new JobEngine.Views.Wizard ();
			// remove the notice message
			$('#notice_wizard').fadeOut(1000, function(){ $(this).remove(); });
			break;
		case 'ET_MenuOverview':
		default:
			backendView = new JobEngine.Views.BackbendOverview();
			break;
	}
};

jQuery(document).ready(function($){
 	//backendView = new JobEngine.Views.BackbendOverview();

	// set a subscription when page changed
	// backendRoute.on('et:changeMenu', JobEngine.on_change_menu_page_callback);

// 	// Router
// 	var adminRoute = Backbone.Router.extend({
// 		firstStart : true,
// 		routes : {
// 			'section/:name' : 'section',
// 			'section/:name/:item' : 'section'
// 		},
// 		section : function(name, subSection){
// 			subSection = subSection || '';
// 			if ( name ) {
// 				$.changeSection(name, subSection);
// 			}
// 			else if ( this.firstStart === true ) {
// 				$.changeSection(name, subSection);
// 			}
// 			this.firstStart = false;
// 		}
// 	});

// 	// create new route instance for change url
// 	new adminRoute();

// 	// start route
// 	Backbone.history.start({ root: et_globals.routerRoot });
 });
}) (jQuery);