(function($){

	JobEngine.Models.PositionTax	=	JobEngine.Models.Tax.extend ({
		initialize : function () {
			this.action = 'et_sync_resume_category';
		}
	});

	JobEngine.Models.AvailableTax	=	JobEngine.Models.Tax.extend ({
		initialize : function () {
			this.action = 'et_sync_availale';
		}
	});
	/**
	 * Resume Category view
	*/
	JobEngine.Views.PositionTaxItem =  JobEngine.Views.TaxItem.extend ({
		initialize : function (){
			_.templateSettings = {
			    evaluate    : /<#([\s\S]+?)#>/g,
				interpolate : /\{\{(.+?)\}\}/g,
				escape      : /<%-([\s\S]+?)%>/g
			};

			JobEngine.Views.TaxItem.prototype.initialize.call();
			this.confirm_html = 'temp_resume_category_delete_confirm';
			this.tax_name	  = 'resume_category';
			
		},
		render : function(){
			_.templateSettings = {
		    	evaluate    : /<#([\s\S]+?)#>/g,
				interpolate : /\{\{(.+?)\}\}/g,
				escape      : /<%-([\s\S]+?)%>/g
			};

			this.$el.append( this.template(this.model.toJSON()) ).addClass('category-item tax-item').attr('id', 'cat_' + this.model.get('id'));
			return this;
		},
		template : _.templateSettings = {
		    evaluate    : /<#([\s\S]+?)#>/g,
			interpolate : /\{\{(.+?)\}\}/g,
			escape      : /<%-([\s\S]+?)%>/g
		},

		template: _.template('<div class="container"> \
							<div class="sort-handle"></div> \
						<div class="controls controls-2"> \
							<a class="button act-open-form" rel="{{id}}" title=""> \
								<span class="icon" data-icon="+"></span> \
							</a> \
							<a class="button act-del" rel="{{ id }}"> \
								<span class="icon" data-icon="*"></span> \
							</a> \
						</div> \
						<div class="input-form input-form-2"> \
							<input class="bg-grey-input tax-name" rel="{{id}}" type="text" value="{{name}}"> \
						</div> \
					</div>'),

		sub_template : _.templateSettings = {
		    evaluate    : /<#([\s\S]+?)#>/g,
			interpolate : /\{\{(.+?)\}\}/g,
			escape      : /<%-([\s\S]+?)%>/g
		},

		sub_template : _.template('<li class="form-sub-tax disable-sort" id="tax_{{id}}"> \
						<div class="container">\
							<!--	<div class="sort-handle"></div>  --> \
							<div class="controls controls-2">\
								<a class="button act-add-sub" title=""> \
									<span class="icon" data-icon="+"></span> \
								</a>\
							</div>\
							<div class="input-form input-form-2"> \
								<form action="" class="" data-tax="resume_category">\
									<input type="hidden" name="parent" value="{{id}}">\
									<input class="bg-grey-input new-tax" name="name" type="text" placeholder="Enter category name"> \
								</form> \
							</div> \
						</div>\
					</li>'),
	});

	/**
	 * job available view
	*/
	JobEngine.Views.AvailableTaxItem =  JobEngine.Views.TaxItem.extend ({
		events :  function(){
	      	return _.extend({},JobEngine.Views.TaxItem.prototype.events,{
	          	'click .input-form .cursor' : 'changeColor',
	      	});
	    },

		initialize : function (){
			JobEngine.Views.TaxItem.prototype.initialize.call();
			this.confirm_html = 'temp_available_delete_confirm';
			//console.log('AvailableTaxItem');
		},
		render : function(){
			this.$el.append( this.template(this.model.toJSON()) ).addClass('job-type').addClass('job-type-' + this.model.get('id')).attr('data',  this.model.get('id')).attr('id', 'jobtype_' + this.model.get('id'));
			return this;
		},
		template : _.templateSettings = {
		    evaluate    : /<#([\s\S]+?)#>/g,
			interpolate : /\{\{(.+?)\}\}/g,
			escape      : /<%-([\s\S]+?)%>/g
		},

		template: _.template('<div class="container"><div class="sort-handle"></div><div class="controls controls-1"><a class="button act-del" rel="{{ id }}">' +
					'<span class="icon" data-icon="*"></span>' +
				'</a> </div>'+
				'<div class="input-form input-form-1" data-action="et_update_available_color">' +
					'<div class="cursor <# if ( color ) { #>color-{{ color }}<# } #>"><span class="flag"></span></div>' +
					'<input class="tax-name bg-grey-input type-name <# if ( color ) { #>color-{{ color }}<# } #>" rel="{{ id }}" type="text" value="{{ name }}">' +
				'</div></div>'
		),

		changeColor: function(event){
			var $this = $(event.currentTarget),
				container = $this.closest('li');

			event.stopPropagation();
			$('.tax-item').removeClass('current-job-type');
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
			$('body').unbind('click');
			$('body').bind('click', function(e){
				var tooptip = $('.bar-flag');
				if ( !tooptip.is(':hidden') && tooptip.has( e.target ).length === 0 ){
					tooptip.remove();
				}
			});
		}
	});
	/**
	 * backend tax view extended
	*/
	PositionView	=	JobEngine.Views.BackendTax.extend({
		initialize: function(){
			this.initTax ();
			this.initView ();
		},	

		initView : function () {
			var appView =	this,
				tax_type	=	this.$el.find('.list-job-input').attr('data-tax');
			// $('.sortable').sortable({
			// 	axis: 'y',
			// 	handle: 'div.sort-handle'
			// });

			$('div#job-position .tax-sortable').nestedSortable({
				handle: '.sort-handle',
				items: 'li',
				toleranceElement: '.sort-handle',
				listType : 'ul',
				placeholder : 'ui-sortable-placeholder',
				dropOnEmpty : false,
				cancel : '.disable-sort',
				update : function(event, ui){
	            	appView.sortTax(event, ui, 'et_sort_resume_category');
	            }
			});
		},

		initTax : function () {
			// this function should be override by children classs
			var tax_type	=	this.$el.find('.list-job-input').attr('data-tax'), view = this;
			_.each( this.$el.find('.list-job-input li.tax-item'), function(item){
				var $this = $(item);
				var jobLoc = {
					id : $this.find('.act-del').attr('rel'),
					name : $this.find('input[type=text]').val()
				}
				//var itemView	=	view.factory(tax_type,jobLoc, item) ;
				var itemView = new JobEngine.Views.PositionTaxItem( {model : new JobEngine.Models.PositionTax(jobLoc), el : item, confirm_html : 'temp_jobpos_delete_confirm' } );
			} );
		},
	});

	AvailableView	=	JobEngine.Views.BackendTax.extend({
		initialize: function(){
			this.initTax ();
			this.initView ();
			$('.new-job-type').each(function(){
				var $this 		= $(this)
				var id = $this.attr('data');
				var colorPicker = $this.find('.input-form .cursor');

				colorPicker.bind('click', function(e){
					e.stopPropagation();
					$('.job-type').removeClass('current-job-type');
					if ( !$this.hasClass('new-job-type') )
						$this.addClass('current-job-type');
					jQuery(".input-form .bar-flag").remove();

					flag_current = jQuery(this).attr("class");
					flag_current = flag_current.replace("cursor","");
					flag_current = flag_current.replace(" color-","");


					flag_choose = "";
					for(i_color=1; i_color<40; i_color++) {

						flag_choose += "<div class=\"color-" + i_color;

						if (i_color == flag_current) flag_choose += " active";

						flag_choose += "\" data='" + i_color + "'>"
										+ "<span class=\"flag\"></span>" 
				        				+ "</div>";
				    }	

					jQuery(this).parent().append("<div class=\"bar-flag\">" + flag_choose + "</div>");

					// remove tooltip when click outside tooltip
					jQuery('body').unbind('click');
					jQuery('body').bind('click', function(e){
						var tooptip = $('.bar-flag');
						if ( !tooptip.is(':hidden') && tooptip.has( e.target ).length == 0 ){
							tooptip.remove();
						}
					});
				});
			});

			jQuery(".input-form .bar-flag div").live("click" , function(){

			});

		},	

		initView : function () {
			var appView =	this,
				tax_type	=	this.$el.find('.list-job-input').attr('data-tax');

			// $('.sortable').sortable({
			// 	axis: 'y',
			// 	handle: 'div.sort-handle'
			// });

			$('div#job-available .tax-sortable').nestedSortable({
				handle: '.sort-handle',
				items: 'li',
				toleranceElement: '.sort-handle',
				listType : 'ul',
				placeholder : 'ui-sortable-placeholder',
				dropOnEmpty : false,
				cancel : '.disable-sort',
				update : function(event, ui){
	            	appView.sortTax(event, ui, 'et_sort_available');
	            }
			});
		},

		initTax : function () {
			// this function should be override by children classs
			var tax_type	=	this.$el.find('.list-job-input').attr('data-tax'), view = this;
			_.each( this.$el.find('.list-job-input li.tax-item'), function(item){
				var $this = $(item);
				var jobLoc = {
					id : $this.find('.act-del').attr('rel'),
					name : $this.find('input[type=text]').val()
				}
				// var itemView	=	view.factory(tax_type,jobLoc, item) ;
				var itemView = new JobEngine.Views.AvailableTaxItem( {model : new JobEngine.Models.AvailableTax(jobLoc), el : item } );
			} );
		},
	});

	JobEngine.Views.ResumeTax = Backbone.View.extend ({
		el : 'div#resume_content',
		events: {
			'click 	.toggle-button' 		: 'onToggleFeature',
			'click .title .btn-edit'		: 'onToggleInput',
			'dblclick  .title-main'			: 'onToggleInput',
			'keyup .title input'			: 'onKeypressUpdateTitle',
			'change .title input'			: 'onChangeUpdateTitle'
		},
		
		initialize : function () {
			var view = this;
			this.loading = new JobEngine.Views.BlockUi();
			var position =	new PositionView({el : $('div#job-position')});	
			var available =	new AvailableView({el : $('div#job-available')});

			JobEngine.TaxFactory.registerTaxModel('resume_category', JobEngine.Models.PositionTax);
			JobEngine.TaxFactory.registerTaxModel('available', JobEngine.Models.AvailableTax);

			JobEngine.TaxFactory.registerTaxItem('available', JobEngine.Views.AvailableTaxItem);
			JobEngine.TaxFactory.registerTaxItem('resume_category', JobEngine.Views.PositionTaxItem);

		},

		onToggleInput: function(event){
			event.preventDefault();
			var element 	= $(event.currentTarget),
				container 	= element.parents('.title');

			if(!container.hasClass ('editing')) {
				$(container).toggleClass ('editing');
			}
			
			//container.children('input').val(cur_title);					
		},
		onKeypressUpdateTitle : function (event) {
			event.preventDefault();
			if(event.which == 13 ) { 
				this.onUpdateTitle (event);
			}
		},

		onChangeUpdateTitle : function (event) {
			this.onUpdateTitle (event);
		}, 

		onUpdateTitle: function(event){
			
			var element 	= $(event.currentTarget);
			var container 	= element.parents('.title');			
			var tax_name 	= element.attr('data-tax');
			var value 		= container.children('input').val();
			var view  		= this;
			
			this.updateOption( tax_name, value, {
				action  : 'je_change_resume_tax_title',
				beforeSend: function(){
					view.loading.block(element);
				},
				success: function(resp){
					view.loading.unblock();

					if (resp.success){
						if(value == '') {
							container.children('.title-main').text(et_options.empty_title);
						} else {
							container.children('.title-main').text(value);
						}
						container.removeClass('editing');
					}
				}
			});

		},

		onToggleFeature: function(event){

			event.preventDefault();
			var element 	= $(event.currentTarget);
			var container 	= element.parent();

			var name 		= element.attr('data');
			var value 		= element.hasClass('deactive') ? 0 : 1;
			var view  		= this;

			this.updateOption(name, value, {
				action  : et_options.ajax_action,
				beforeSend: function(){
					view.loading.block(container);
				},
				success: function(resp){
					view.loading.unblock();

					if (resp.success){

						container.children('a').removeClass('selected');
						element.addClass('selected');

						if(parseInt(value) == 0)
							$('#wizard-settings-resume').hide ();
						else 
							$('#wizard-settings-resume').show();
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
					action: params.action,//et_update_option
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
		}
	});

	$(document).ready(function() {
		var resumeTax = new JobEngine.Views.ResumeTax ();
	});
}(jQuery));