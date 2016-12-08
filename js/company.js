(function ($) {
jQuery(document).ready(function($){

JobEngine.Views.CompanyListItem = Backbone.View.extend({
	tagName : 'li',
	template : _.template('<a class="company-item" href="{{ post_url }}">{{ display_name }}</a>'),
	render : function(company){
		this.$el.html( this.template(this.model.toJSON()));
		return this;
	}
});

JobEngine.Views.CompaniesView = Backbone.View.extend({
	el : $('body'),
	conpanies : null,
	events : {
		'keyup input#companies_filter' 	: 'search_company',
		'click ul.list-alphabet li a' 	: 'filter_company'
		// 'keypress document'		 	: 'keyFilter'
	},
	initialize: function(options){
		var view	= this;
		// fill in companies collection
		this.companies_list = _.map( $('.company-item'), function(company){
			return new JobEngine.Models.Company ({
				display_name : $.trim( $(company).html() ),
				post_url : $(company).attr('href'),
				element : $(company)
			});
		});
		this.companies = new JobEngine.Collections.Companies(this.companies_list);

		$(document).keypress(function(e) {
			view.keyFilter(e);
		});
	},

	render : function(companies){
		// clear
		$('.company-section ul').html('');
		// insert new
		_.each(companies, function(company){
			var first_char = $.trim(company.get('display_name')).substring(0,1).toUpperCase();
			var char_int	=	parseInt(first_char);
			// filter list
			var viewItem = new JobEngine.Views.CompanyListItem({model : company});
			if(char_int) {
				$('.company-section[data-char=numbers] ul').append( viewItem.render().el );
			} else if( !first_char.match(/[^a-zA-Z]/g)  ) {
				$('.company-section[data-char='+ first_char +'] ul').append( viewItem.render().el );
			} else {
				$('.company-section[data-char=Others] ul').append( viewItem.render().el );
			}

		});

		// find empty list and hide them
		_.each($('.company-section'), function(list){
			var $ele = $(list);
			if ( $.trim( $ele.find('ul').html()) === '' ){
				$ele.hide();
			}
			else{
				$ele.show();
			}
		});

		// enable wookmark to soft companies items
		$('#list_company > li:not(:hidden)').wookmark({container: $('.companies-container'), offset: 28});
	},

	// events
	search_company : function(event){
		event.preventDefault();
		var term = $(event.target).val();

		var filtered = this.companies.filter(function(company){
			var pattern = new RegExp( term , 'i');
			return pattern.test(company.get('display_name'));
		});

		// re render
		this.render(filtered);
	},

	keyFilter : function (event) {

		var key	=	String.fromCharCode(event.keyCode),
			term = '^'+ key.toUpperCase();

		if( key == '' || key == '`') {
			filtered	=	this.companies_list;
			$('.list-alphabet li').removeClass('active');
		} else {

			filtered = this.companies.filter(function(company){
				var pattern = new RegExp( term , 'i');
				return pattern.test(company.get('display_name'));
			});

			Backbone.history.navigate("filter/" + key.toUpperCase() );

			// re render
			$('.list-alphabet li').removeClass('active');
			$('ul.list-alphabet li a[data=' + key.toUpperCase() + ']').parents('li').addClass('active');

		}

		this.render(filtered);
	},

	filter_company : function(event){

		var $this	= $(event.currentTarget),
			term	= '^'+ $this.attr('data').toUpperCase(),
			filtered;

		if($this.attr('data') == '') {
			filtered	=	this.companies_list;
		} else {

			filtered = this.companies.filter(function(company){
				var pattern = new RegExp( term , 'i');
				return pattern.test(company.get('display_name'));
			});

		}

		$('.list-alphabet li').removeClass('active');
		$this.parent().addClass('active');
		this.render(filtered);

	}
});

new JobEngine.Views.CompaniesView();

JobEngine.Routers.Company = Backbone.Router.extend({
	routes : {
		"filter/:value" : 'filterCompany'
	},
	initialize: function(){},
	filterCompany : function(value){
		$('ul.list-alphabet li a[data=' + value.toUpperCase() + ']').trigger('click');
	}
});

new JobEngine.Routers.Company();

Backbone.history.start({pushState: false, root : et_globals.routerRootCompanies});

// enable wookmark to soft companies items
$('#list_company > li').wookmark({container: $('.companies-container'), offset: 28});
});

})(jQuery);