(function ($) {
$(document).ready(function () {
	
JobEngine.Views.Category	=	Backbone.View.extend({
	el		: $('#entry-list'),
	events	: {
		'click #load-more-post' : 'loadMorePost'
	},

	page	: 1,

	initialize : function () {
		this.page =	1;
		var view = this;
		if( et_globals.use_infinite_scroll ) {
			$(window).scroll(function(){
                if  ($(window).scrollTop() == $(document).height() - $(window).height()){
                   view.scrollLoadMore();
                }
            }); 
        }
	},

	scrollLoadMore : function () {
		this.loadMorePost ( $('#load-more-post') );
	},

	clickLoadMore : function (event) {
		var $target	=	$(event.currentTarget);
		this.loadMorePost ( $target );
	},

	loadMorePost	: function (target) {
		
		event.preventDefault ();
		var $target			=	target;
			$template		=	this.$el.find('input#template'),
			$list_payment	=	this.$el.find('ul'),
			appView			=	this,
			page			=	this.page+1;
		var loadingBtn = new JobEngine.Views.LoadingButton({el : $('#load-more-post')});
		$.ajax ({
			url : et_globals.ajaxURL,
			type : 'post',
			data : {
				page			: page,
				action			: 'et-load-more-post',
				template_value	: $template.val(),
				template		: $template.attr('name')
			},
			beforeSend : function () {
				appView.page ++ ;
				loadingBtn.loading();
			},
			success : function (response) {
				if(response.success) {
					$list_payment.append (response.data);
					loadingBtn.finish();
					if( appView.page >= response.total ){
						$target.hide ();
					}

				}else {
					loadingBtn.finish();
					$target.hide ();
					appView.page --;

				}
			}
		});
	}
	 
});

new JobEngine.Views.Category ();

} );
})(jQuery);