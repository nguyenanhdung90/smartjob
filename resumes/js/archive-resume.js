(function($){
$(document).ready(function(){

// Modal Login
JobEngine.Views.Modal_PopUp = JobEngine.Views.Modal_Box.extend({
	el : '#modal_popup',
	events : {
		'click div.modal-close'		: 'closeModal',
		'click a.cancel-modal'		: 'closeModal',
		'click a.open-login'		: 'openLogin',
		'click li.create-one'		: 'gotoUpgrade'
	},
	initialize : function(){
		JobEngine.Views.Modal_Box.prototype.initialize.apply(this, arguments );
		this.options = _.extend( this.options, this.defaults );
	},
	openLogin : function  (e) {
		e.preventDefault();
		this.closeModal(200, function(){
			JobEngine.app.header.modal_login.setOptions  ({ redirect_url : this.redirect_url }  );
			JobEngine.app.header.modal_login.openModalAuth();
		});

	},

	gotoUpgrade : function () {

	}

});

JobEngine.Views.PageProfile = Backbone.View.extend({
	el : '#archive_resumes',
	events: {
		'click .button-more button' : 'loadMore',
		'keyup input[type=text].resume-input-query' : 'filterQuery',
		'keyup input[type=text].resume-input-location' : 'filterLocation',
		'click a.available-input' : 'filterTax',
		'click a.position-input' : 'filterTax' ,
		'click .list-jobs a.resume-title' : 'requestModalLogin'
	},

	initialize: function(){
		var view = this;
		/**
		 * pending resume view
		*/
		var pending_resume_data	=	$('#pending_resume_data').html();
		this.pendingResume		=	new JobEngine.Collections.Resumes ();
		if(pending_resume_data) {
			this.pendingResume		=	new JobEngine.Collections.Resumes (JSON.parse (pending_resume_data) );
			this.pendinglistView 	= 	new JobEngine.Views.ResumeListView({ collection : this.pendingResume , el : '#pending_resumes' });
		}

		this.current_user	=	JobEngine.app.currentUser;
		/**
		 * active resume view
		*/
		this.activeResume	=	new JobEngine.Collections.Resumes (JSON.parse ($('#latest_resume_data').html()) );
		this.listView 		= 	new JobEngine.Views.ResumeListView({ collection : this.activeResume , el : '#resumes' });

		this.listView.on('endOfPages', this.hidePaginator);
		this.listView.on('notEndOfPages', this.showPaginator);

		this.queries = {};

		this.router = new ResumeRouter();
		this.router.on('search', this.initSearch, this);
		if ( typeof Modernizr !== 'undefined' && Modernizr.history === true ){
			Backbone.history.start();
		}

		pubsub.on('je:resume:afterRemoveResumeView',  this.afterRemoveResumeView, this);
		pubsub.on('je:resume:afterApproveResume', this.afterApproveResume, this);
		pubsub.on('je:resume:onReject', this.onRejectResume, this);
		pubsub.on('je:resume:afterRejectResume', this.afterRejectResume, this);
	},

	requestModalLogin : function (event) {
		if(typeof this.current_user.get('ID') == 'undefined' && !et_resume.is_free_view ) {
			event.preventDefault();
			if(typeof this.modal === 'undefined')
				this.modal	=	new JobEngine.Views.Modal_PopUp ({ redirect_url : $(event.currentTarget).attr('href') });
			this.modal.openModal();
		}
	},

	// after a job is approved, we remove it from the pending job collection
	afterApproveResume	: function(model,res){
		var cats	= model.get('resume_category'),
			self	= this,
			$status_con	= this.$('ul.filter-jobcat'),
			i;

		this.pendingResume.remove(model);

		if(_.isArray(cats)){
			for(i=0;i<cats.length;i++){
				if('term_id' in cats[i]) {
					$status_con.find('li.position-' + cats[i].term_id + ' > a span.count').each(self.countUp);
				}
			}
		}
		this.$('.je-resume-count').find('.impress').each(self.countUp);
	},

	afterRemoveResumeView : function (resume) {
		if(resume.get('post_status') == 'publish')
			this.activeResume.add(resume);

	},


	onRejectResume : function (args) {
		if( typeof this.rejectModalView === 'undefined' || !(this.rejectModalView instanceof JobEngine.Views.ModalReject) ){
			this.rejectModalView = new JobEngine.Views.ModalReject();
		}
		this.rejectModalView.onReject(args);
	},

	// after a job is rejected, remove it from the pending job collection & count up the number of rejected jobs
	afterRejectResume : function(model,res){

		this.pendingResume.remove(model);
		this.activeResume.remove(model);

		if(this.activeResume.length == 0)
			this.activeResume.nextPage ({
				beforeSend: function(){
					//loadBtn.loading();
				},success : function(data, resp){
					//loadBtn.finish();
				}
			});
	},

	loadMore: function(event){
		loadingBtn = new JobEngine.Views.LoadingButton({el : $(event.currentTarget)});
		this.listView.setFilterParams(this.queries);

		this.listView.nextPage({
			beforeSend: function(){
				loadingBtn.loading();
			},success : function(data, resp){
				loadingBtn.finish();
			}
		});
	},

	// hide button load more if all the resumes are fetched
	hidePaginator: function(pages){
		// hide load more button
		$('.button-more').hide();
	},

	showPaginator: function(pages){
		// hide load more button
		$('.button-more').show();
	},

	makeQuery: function(query){
		var result = {};
		_.each( query, function( value, key ){
			if (value != '' && key != 'paged' ) result[key] = value
		} );

		return result;
	},

	refreshFilter : function(data){
		this.queries = $.extend(this.queries, data, {paged: 1});
		return this.queries;
	},

	filterQuery: function(event){
		var value = $(event.currentTarget).val();
		var view = this;

		view.queries = this.refreshFilter({rq: value});
		this.applyFilter(view.queries);
	},

	filterLocation: function(event){
		var value = $(event.currentTarget).val();
		var view 	= this;

		view.queries = this.refreshFilter({et_location: value});
		this.applyFilter(view.queries);
	},

	filterTax: function(event){
		event.preventDefault();
		var element 	= $(event.currentTarget),
			value 		= element.attr('data'),
			container 	= element.parents('.filter-joblist'),
			isActive 	= element.hasClass('active'),
			view 		= this;

		container.find('li > a').removeClass('active');

		if ( isActive ){
			element.removeClass('active');
			value = '';
		} else {
			element.addClass('active');
		}

		if ( $(event.currentTarget).hasClass('available-input') ){
			view.queries = this.refreshFilter({available: value});
			//this.queries = $.extend( this.queries, {available: value} );
			this.filterNow(this.queries);
		} else if ($(event.currentTarget).hasClass('position-input')){
			view.queries = this.refreshFilter({resume_category: value});
			//this.queries = $.extend( this.queries, {resume_category: value} );
			this.filterNow(this.queries);
		}
	},

	initSearch: function(params){
		// setup fields
		$('.filter-input').each(function(){
			var e = $(this);
			var key = e.attr('data-filter')
			if ( typeof params[key] != 'undefined' ){
				e.val(params[key]);
			}
		});

		if (params.available){
			$('.available-lists a[data='+ params.available + ']').addClass('active');
		}

		if (params.resume_category){
			$('.resume_category_list a[data='+ params.resume_category + ']').addClass('active');
		}

		_.each (this.$('ul.resume-filter'), function (tax) {
			var etax		=	$(tax).attr('data-tax');
			if(etax) {
				var $activeTax 	=	 $(tax).find('a.active');
				var arg	=	$.map( $activeTax, function(item){
					return $(item).attr('data');
				});
				arg	= arg.join(',');
				params[etax] = arg;
			}
		});

		this.queries = params;
		this.filterNow(params);
	},

	countUp	: function(index,element){
		var $this	= $(element),
			count	= parseInt($this.html(),10);
		count++;
		$this.html(count);
	},

	countDown	: function(index,element){
		var $this	= $(element),
			count	= parseInt($this.html(),10);
		count--;
		$this.html(count);
	},

	filterNow: function(params){
		// route
		displayParams = this.makeQuery(params);
		this.router.navigate('!search/' + $.param(displayParams), {trigger: false} );

		return this.listView.filter(params);
	},

	applyFilter: _.debounce(function(params){
		// route
		displayParams = this.makeQuery(params);
		this.router.navigate('!search/' + $.param(displayParams), {trigger: false} );

		return this.listView.filter(params);
	}, 1000)
});

var ResumeRouter = Backbone.Router.extend({
	routes: {
		'!search/:query' : "search"
	},

	search: function(query){
		var params = $.parseParams(query);
		if (typeof params == 'object'){
			this.trigger('search', params);
		}
	}
});

$.parseParams = function(query, exclude) {
	var re = /([^&=]+)=?([^&]*)/g;
	var decodeRE = /\+/g;  // Regex for replacing addition symbol with a space
	var decode = function (str) {return decodeURIComponent( str.replace(decodeRE, " ") );};

    var params = {}, e;
    while ( e = re.exec(query) ) {
        var k = decode( e[1] ), v = decode( e[2] );
        if (k.substring(k.length - 2) === '[]') {
            k = k.substring(0, k.length - 2);
            (params[k] || (params[k] = [])).push(v);
        }
        else params[k] = v;
    }

    // exclude
    var exclude = exclude || {};
    _.each( exclude, function(element){
    	_.each( params, function(value, key) {
    		if (key == element)
    			delete params[key];
    	});
    } );
    return params;
};

	new JobEngine.Views.PageProfile();
});

})(jQuery);