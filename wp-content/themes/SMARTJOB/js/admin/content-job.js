(function($){

	JobEngine.Models.JobCat	=	JobEngine.Models.Tax.extend ({
		initialize : function () {
			this.action = 'et_sync_job_category';
		}
	});

	JobEngine.Models.JobTypeTax	=	JobEngine.Models.Tax.extend ({
		initialize : function () {
			this.action = 'et_sync_job_type';
		}
	});
	/**
	 * Resume Category view
	*/
	JobEngine.Views.JobCatItem =  JobEngine.Views.TaxItem.extend ({
		initialize : function (){
			JobEngine.Views.TaxItem.prototype.initialize.call();
			this.confirm_html = 'temp_job_category_delete_confirm';
			this.tax_name	  = 'job_category';			
		},
		render : function(){
			this.$el.append( this.template(this.model.toJSON()) ).addClass('category-item tax-item').attr('id', 'cat_' + this.model.get('id'));
			return this;
		},
		template: _.templateSettings = {
		    evaluate    : /<#([\s\S]+?)#>/g,
			interpolate : /\{\{(.+?)\}\}/g,
			escape      : /<%-([\s\S]+?)%>/g
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
							<input class="bg-grey-input tax-name" rel="{{ id }}" type="text" value="{{ name }}"> \
						</div> \
					</div>'),

		sub_template : _.template('<li class="form-sub-tax disable-sort" id="tax_{{ id }}"> \
						<div class="container">\
							<!--	<div class="sort-handle"></div>  --> \
							<div class="controls controls-2">\
								<a class="button act-add-sub" title=""> \
									<span class="icon" data-icon="+"></span> \
								</a>\
							</div>\
							<div class="input-form input-form-2"> \
								<form action="" class="" data-tax="job_category">\
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
	JobEngine.Views.JobtypeItem =  JobEngine.Views.TaxItem.extend ({
		events :  function(){
	      	return _.extend({},JobEngine.Views.TaxItem.prototype.events,{
	          	'click .input-form .cursor' : 'changeColor',
	      	});
	    },

		initialize : function (){
			JobEngine.Views.TaxItem.prototype.initialize.call();
			this.confirm_html = 'temp_job_type_delete_confirm';			
		},
		render : function(){
			this.$el.append( this.template(this.model.toJSON()) ).addClass('job-type').addClass('job-type-' + this.model.get('id')).attr('data',  this.model.get('id')).attr('id', 'jobtype_' + this.model.get('id'));
			return this;
		},
		template: _.templateSettings = {
		    evaluate    : /<#([\s\S]+?)#>/g,
			interpolate : /\{\{(.+?)\}\}/g,
			escape      : /<%-([\s\S]+?)%>/g
		},

		template: _.template('<div class="container"><div class="sort-handle"></div><div class="controls controls-1"><a class="button act-del" rel="{{id}}">' +
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
	CategoryView	=	JobEngine.Views.BackendTax.extend({
		initialize: function(){
			this.initTax ();
			this.initView ();			
		},	

		initView : function () {
			var appView =	this,
				tax_type	=	this.$el.find('.list-job-input').attr('data-tax');
			$('div#job-categories .tax-sortable').nestedSortable({
				handle: '.sort-handle',
				items: 'li',
				toleranceElement: '.sort-handle',
				listType : 'ul',
				placeholder : 'ui-sortable-placeholder',
				dropOnEmpty : false,
				cancel : '.disable-sort',
				update : function(event, ui){
	            	appView.sortTax(event, ui, 'et_sort_job_category');
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
				var itemView = new JobEngine.Views.JobCatItem( {model : new JobEngine.Models.JobCat(jobLoc), el : item } );
			} );
		},
	});

	JobtypeView	=	JobEngine.Views.BackendTax.extend({
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


		},	

		initView : function () {
			var appView =	this,
				tax_type	=	this.$el.find('.list-job-input').attr('data-tax');
			$('div#job-types .tax-sortable').nestedSortable({
				handle: '.sort-handle',
				items: 'li',
				toleranceElement: '.sort-handle',
				listType : 'ul',
				placeholder : 'ui-sortable-placeholder',
				dropOnEmpty : false,
				cancel : '.disable-sort',
				update : function(event, ui){
	            	appView.sortTax(event, ui, 'et_sort_job_type');
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
				var itemView = new JobEngine.Views.JobtypeItem( {model : new JobEngine.Models.JobTypeTax(jobLoc), el : item } );
			} );
		},
	});

	JobEngine.Views.JobTax = Backbone.View.extend ({
		el : 'div#job-content',
		
		initialize : function () {
		
			var position =	new CategoryView({el : $('div#job-categories')});	
			var available =	new JobtypeView({el : $('div#job-types')});

			JobEngine.TaxFactory.registerTaxModel('job_category', JobEngine.Models.JobCat);
			JobEngine.TaxFactory.registerTaxModel('job_type', JobEngine.Models.JobTypeTax);

			JobEngine.TaxFactory.registerTaxItem('job_type', JobEngine.Views.JobtypeItem);
			JobEngine.TaxFactory.registerTaxItem('job_category', JobEngine.Views.JobCatItem);

		}
		
	});

	$(document).ready(function() {
		var jobTax = new JobEngine.Views.JobTax ();	
	});
}(jQuery));