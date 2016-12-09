(function ($) {
jQuery(document).ready(function($){

JobEngine.Views.DashboardItem = Backbone.View.extend({
	tagName : 'li',

	className : 'acc-job-item',

	events : {
		'click .action-edit'	: 'edit',
		'click .action-archive' : 'archiveJob',
		'click .action-remove'	: 'removeJob'
	},

	initialize: function(){
		if(this.model instanceof JobEngine.Models.Job){
			this.model.on('change', this.render, this);
		}

		this.blockUi = new JobEngine.Views.BlockUi();
	},

	template : _.template($('#job_item_template').html()),

	render:function(){
		var status	= this.model.get('status');
		this.model.set('dashboardStatus', et_dashboard.statuses[status].toUpperCase());
		this.$el.addClass('job-item-' + status).html( this.template(this.model.toJSON()) );
		return this;
	},

	// edit button handler
	edit : function(event){
		event.preventDefault();
		if(!this.model.has('id')){
			this.model.set('id',this.model.get('ID'),{silent:true});
		}
		pubsub.trigger('je:job:onEdit',this.model);
	},

	// archive button handler
	archiveJob : function(event){
		var view = this;

		event.preventDefault();

		this.model.archive({
			beforeSend : function(){
				view.blockUi.block(view.$el);
			},
			success: function ( model, resp ){
				view.blockUi.unblock();
				view.$el.fadeOut('normal', function(){
					view.$el.remove();
					pubsub.trigger('je:job:afterHideArchivedJob', model);
				});
			},
			silent:true
		});
	}, 
	// delete job
	removeJob : function (event) {
		var view = this;
		event.preventDefault();
		this.model.remove({
			beforeSend : function(){
				view.blockUi.block(view.$el);
			},
			success: function ( resp ){
				view.blockUi.unblock();				
				if(resp.success) {
					view.$el.fadeOut('normal', function(){
						view.$el.remove();
					});
					pubsub.trigger('je:notification',{
						msg	: resp.msg,
						notice_type	: 'success'
					});
				} else {
					pubsub.trigger('je:notification',{
						msg	: resp.msg,
						notice_type	: 'error'
					});
				}

			},
			silent:true
		});
	}

});

// Dashboard View
JobEngine.Views.Dashboard = Backbone.View.extend({

	el : $('.account-jobs'),

	events : {
		'click  span.applier-more' : 'showApplicant'
	},

	initialize : function(){
		var that	= this,
			jobs	= this.$('#job_list_data'),
			$list	= this.$( '.job-account-list' ),
			i,item;

		jobs = (jobs.length>0) ? JSON.parse(jobs.html()) : null;

		if( jobs !== null ){
			for(i=0;i<jobs.length;i++){
				item		= new JobEngine.Models.Job(jobs[i]);
				item.author	= JobEngine.app.currentUser;
				new JobEngine.Views.DashboardItem({el:$list.find('li:eq(' + i + ')'),model:item});
			}
		}

		if( typeof this.editModalView === 'undefined' || !(this.editModalView instanceof JobEngine.Views.Modal_Edit_Job) ){
			this.editModalView	= new JobEngine.Views.Modal_Edit_Job();
		}

		// catch events
		pubsub.on('je:job:onEdit', this.onEditJob, this);
		pubsub.on('je:job:afterHideArchivedJob', this.afterHideArchivedJob, this);
	},

	afterHideArchivedJob : function(model){
		var view		= new JobEngine.Views.DashboardItem({model: model}),
			$ele		= view.render().$el.hide(),
			position	= this.$('li.job-item-archive:first');

		if(position.length > 0){
			$ele.insertBefore(position).fadeIn('slow');
		}
		else{
			$ele.prependTo(this.$('ul.job-account-list')).fadeIn('slow');
		}
	},

	onEditJob	: function(model){
		if( typeof this.editModalView === 'undefined' || !(this.editModalView instanceof JobEngine.Views.Modal_Edit_Job) ){
			this.editModalView	= new JobEngine.Views.Modal_Edit_Job();
		}

		this.editModalView.onEdit(model);
	},

	showApplicant : function (e) {
		e.preventDefault();
		var $target	=	$(e.currentTarget);
		$target.parents('.acc-job-item').find('.list-applier-more').toggle('slow');
	}
});

new JobEngine.Views.Dashboard();

});
})(jQuery);