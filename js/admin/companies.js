(function($){
	$(document).ready(function(){
		new JobEngine.Views.BackendCompanies();
	});

	JobEngine.Views.BackendCompanies = Backbone.View.extend({
		el : '#engine_setting_content',

		query_vars : {
			paged : 1
		},

		events : {
			'click .load-more' 			: 'loadMore',
			'keyup #search_company' 	: 'searchCompany'
		},

		initialize : function(){
			var view	=	this;
			$('.companies-list li.company-item').each(function(index){
				var $this = $(this);
				var company = {
					id : $this.attr('data-id'),
					display_name : $this.find('a.job').html(),
					count_text : $this.find('a.company').html()
				}

				var view = new JobEngine.Views.BackendCompany({
					model : new JobEngine.Models.Company( company )
				});
			});

			this.blockUI = new JobEngine.Views.BlockUi({
				image : et_globals.imgURL + '/loading_big.gif'
			});
			this.loadingBtn = new JobEngine.Views.LoadingButton({
				el : this.$el.find('button.load-more')
			}) ;

			var list	=	$('#pending_view_resume_companies').html();

			view.pending_list	=	[];
			if(typeof list !== 'undefined') {
				list	=	JSON.parse (list);
				_.each (list, function ( element, index ) {

					var el		= view.$( '.pending-view-resume li:eq(' + index + ')' ),
						model 	= new JobEngine.Models.Company( element );

					model.set('id' , element.ID);
					var itemView	=	new JobEngine.Views.BackendCompany({
						el : el , model : model
					});

					view.pending_list.push(itemView);
				});
			}
		},

		filter : function(args, add){
			var args = _.extend(this.query_vars, args);
			var view = this;

			if ( !add ) 
				add = false;

			$.ajax({
				url : et_globals.ajaxURL,
				data : {
					action : 'et_backend_fetch_companies',
					method : 'read',
					content : args
				},
				beforeSend : function(){
					// block the elements
					if (!add) view.blockUI.block($('.companies-list'))
					else view.loadingBtn.loading();
				},
				success: function(resp, status){
					// unblick elements
					if (!add) view.blockUI.unblock();
					else view.loadingBtn.finish();

					if ( resp.success ){
						if ( !add ){
							$('.companies-list').html('');
						}

						// render jobs
						if (resp.data.companies.length > 0){
							_.each( resp.data.companies, function(company){
								var view = new JobEngine.Views.BackendCompany({
									model : new JobEngine.Models.Company( company )
								});

								$('.companies-list').append( view.render().$el );

							});
						}

						// check pageination
						if ( resp.data.pagination.total_page <= resp.data.pagination.paged )
							$('button.load-more').hide();
						else 
							$('button.load-more').show();
					}
				}
			});
		},

		loadMore : function(event){
			event.preventDefault();

			this.query_vars.paged++;

			this.filter( this.query_vars, true );
			
		},

		searchCompany : function(event){
			event.preventDefault();

			var $this = $(event.currentTarget);
			var view = this;

			if ( $('#search_company').val() == this.query_vars.s ) {
				return false;
			}
			this.query_vars.paged = 1;
			this.query_vars.s = $('#search_company').val();

			this.timeout = null;

			this.timeout = setTimeout( function(){
				clearTimeout(this.timeout);

				view.filter( view.query_vars, false );
			}, 1000 );
		}
	});
})(jQuery);