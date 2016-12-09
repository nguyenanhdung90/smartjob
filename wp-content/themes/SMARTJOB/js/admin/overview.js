(function ($){
JobEngine.Views.BackbendOverview = Backbone.View.extend({
	el		: '#engine_setting_content',

	events	: {
		'change select#time_limit' : 'changeTimeLimit',
		'click a#archive' : 'archived_job'
	},

	initialize: function(){
		var that = this,
			pending_jobs_data	= this.$('#pending_jobs_data'),
			comparator;

		if (pending_jobs_data.length !== 0){
			// setup comparator function for pending job collection
			comparator	= function(job){
				var jobDate	= new Date(job.get('post_date'));
				return -(parseInt(job.get('job_paid') + "" + jobDate.getTime(),10));
			};

			pending_jobs_data	= JSON.parse( pending_jobs_data.html() );

			// init pending job collection & matching pending job view
			this.collection	= new JobEngine.Collections.Jobs( pending_jobs_data, {comparator:comparator} );
			this.jobViews	= new JobEngine.Views.BackbendJobListView( {el: $('ul.pending-jobs'), collection: this.collection} );

			if( typeof this.rejectModalView === 'undefined' || !(this.rejectModalView instanceof JobEngine.Views.ModalReject) ){
				this.rejectModalView = new JobEngine.Views.ModalReject({el:$('#modal-reject-job')});
			}

			pubsub.off(null,this.onRejectJob);
			pubsub.off(null,this.removeJob);
			pubsub.off(null,this.afterRemoveJobView);

			pubsub.on('je:job:onReject', this.onRejectJob, this);
			pubsub.on('je:job:afterRejectJob', this.removeJob, this );
			pubsub.on('je:job:afterApproveJob', this.removeJob, this );
			pubsub.on('je:job:afterRemoveJobView', this.afterRemoveJobView, this);
		}
	},

	changeTimeLimit: function(event){
		var $this = $(event.target);
		var value = $this.val();
		var view = this;
		var params = _.extend( ajaxParams, {
			data : {
				within : value,
				action : 'et_change_stats_time_limit'
			},
			beforeSend : function(){
				$this.parent().after(et_globals.loadingImg);
			},
			success : function(resp){
				stats = resp.data.statistic;
				$('.et-main-header').find('img.loading').remove();
				if ( resp.success ){
					var symbol = $('#stats_revenue sup').clone();
					// change stats
					$('#stats_pending_jobs').html( stats.pending_jobs );
					$('#stats_active_jobs').html( stats.active_jobs );
					$('#stats_revenue').html( symbol[0].outerHTML + stats.revenue );
					$('#stats_applications').html( stats.applications );
				}
			}
		} );

		return $.ajax(params);
	},

	archived_job: function(event){
		event.preventDefault();
		if (!$(event.currentTarget).hasClass('disabled')){
			var blockUi = new JobEngine.Views.BlockUi();
			var num	=	parseInt( $('#expired_jobs .number').html() );
			var j = 0;
			for (var i = 0; i < num; i += 10 ) {
				j	=	j+1;
				var params = {
					url : et_globals.ajaxURL,
					type : 'post',
					data : {
						action : 'et_archive_expired_jobs',
						paged : j
					},
					beforeSend: function(){
						blockUi.block($('#expired_jobs'));
					},
					success : function(resp){
						if (resp.success){
							blockUi.unblock();
							$('#expired_jobs').fadeOut('normal', function(){ $(this).remove() });
						}
						else 
							alert(resp.msg);
					}
				}
				$.ajax(params);
			};
			
			
		}
	},

	// open the modal Reject job, init it if not having any instance yet
	onRejectJob	: function( args ){		
		this.rejectModalView.onReject(args);
	},

	removeJob : function(model, resp){
		this.collection.remove( model, resp );
	},

	afterRemoveJobView : function(model, resp){

		if (typeof resp === 'undefined' ||  typeof resp.data === 'undefined' ||  !resp.data.payment || typeof model.id == 'undefined' || $('.payment-item-' + resp.data.payment.id).length > 0 ){
			return false;
		}

		var template = _.template('<li><div class="content">' +
						'<span class="price font-quicksand">{{ price_format }}</span>' +
						'<a href="{{ job_permalink }}" target="_blank" class="job job-name">{{ job_name }}</a> at' +
						'<a href="{{ company_permalink }}" target="_blank" class="company">{{ company_name }}</a>' +
					'</div></li>');
		
		$(template( resp.data.payment )).hide().prependTo( $('.overview-payments') ).fadeIn('normal');
	},

	close : function(){
		if(this.jobViews && typeof this.jobViews.close === 'function'){
			this.jobViews.close();
		}
	}
});

JobEngine.Views.BackbendJobListView = Backbone.View.extend({
	initialize: function(){
		var that = this;

		this.listView	= []; // this array contain all the item views

		if( !!this.collection && !!this.el ){
			this.collection.each( function( model, index, coll ){
				var el	= $( 'ul.pending-jobs li:eq(' + index + ')' );
				if( el.length !== 0 ){
					// use the index of the collection to generate the matching item view
					that.listView[index] = new JobEngine.Views.BackendJob({el:el, model:model});
				}
			});
		}

		this.collection.on('remove', this.removeJob, this);
	},

	// after a job is removed from the collection, remove its view
	removeJob	: function(job,col,options){

		// remove the job item view from the array listView
		var itemView = this.listView.splice( options.index, 1 );

		if( itemView.length > 0 ){
			itemView[0].$el.fadeOut('normal',function(){
				itemView[0].remove().undelegateEvents();

				// after hiding the removed job, publish this event to add the job to the correct collection
				pubsub.trigger('je:job:afterRemoveJobView', job, options);
			});
		}
	},

	close : function(){

		// empty the list
		_.each( this.listView, function(item){
			item.remove();
			item.unbind();
		});
	}
});

$(document).ready(function(){
	var overview = new JobEngine.Views.BackbendOverview();
});

}) (jQuery);