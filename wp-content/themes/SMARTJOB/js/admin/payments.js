(function($){
	$(document).ready(function(){
		new JobEngine.Views.PaymentManager();
	});

JobEngine.Views.PaymentManager = Backbone.View.extend({
	el : '#engine_setting_content',

	events : {
		'click ul.processor-list a' : 'filterPaymentGateway',
		'click #load-more'		  :	'loadMorePayment',
		'keyup input.search-jobs' : 'searchJob'
	},
	initialize : function () {		
		this.page = 1;
		this.blockUI = new JobEngine.Views.BlockUi({
			image : et_globals.imgURL + '/loading_big.gif'
		});
		this.loadingBtn = new JobEngine.Views.LoadingButton({
			el : this.$el.find('#load-more')
		});
	},

	page : null,
	

	filterPaymentGateway : function (target) {
		// set current paged
		this.page = 1;
		target.preventDefault ();

		var $current	=	$(target.currentTarget);
		var payment_gateway	=	$current.attr('href'),
			$list_payment	=	$('ul.list-payment');
		
		payment_gateway	=	payment_gateway.replace ('#','');

		$current.parents('ul').find('.active').removeClass('active');
		$current.addClass('active');

		var search = $('input.search-jobs').val();
		
		this.filter({'target': target ,'s' : search, 'payment' : payment_gateway});
	},
	
	searchJob : function (event) {
		//set current paged
		this.page = 1;
		event.preventDefault();
		
		var payment = $('ul.processor-list').find('.active');

		if( payment.length !== 0 ){
			payment = payment.attr('href');
		} else {
			payment  = '';
		}
		payment	=	payment.replace ('#','');
		appView	=	this;
		
		var name	=	$(event.currentTarget).val();

		if ( typeof this.t != 'undefined'){
			clearTimeout( this.t );
		}

		this.t = setTimeout(function(){
			appView.filter({'target' : event, 's' : name, 'payment' : payment});
		}, 500);
	},

	loadMorePayment	: function(target) {
		target.preventDefault ();
		var appView	=	this;
		
		var a	=	$('ul.processor-list').find('.active'),
			page = this.page +1,
			$list_payment	=	$('ul.list-payment'),
			// search job string
			search = $('input.search-jobs').val(),
			// get payment filter
			payment = $('ul.processor-list').find('.active');
		
		if( payment.length != 0 ) {
			payment = payment.attr('href');
			payment 	=	payment.replace ('#','');
		} else {
			payment  = '';
		}	
		
		$.ajax ({
			url : et_globals.ajaxURL,
			type : 'post',
			data : {
				page : page,
				job : search, 
				payment : payment,
				action  : 'et-filter-job-processor'
			},
			beforeSend : function () {
				appView.page ++ ;
				appView.loadingBtn.loading();
			},
			success : function (reponse) {
				appView.loadingBtn.finish();
				if(reponse.success) {
					$list_payment.append (reponse.data);

					if( appView.page >= reponse.total ) 
						$(target.currentTarget).hide ();

				}else {

					$(target.currentTarget).hide ();
					appView.page --;

				}
			}
		});	
	},
	

	filter : function ( data) {
		var $list_payment	=	$('ul.list-payment'),
			target			=	$(data.target.currentTarget);
			appView			=	this,
			blockUI = new JobEngine.Views.BlockUi();
		$.ajax ({
			url  : et_globals.ajaxURL,
			type : 'post',
			data : {
				job : data.s,
				payment : data.payment,
				action : 'et-filter-job-processor'
			},
			beforeSend : function () {
				appView.blockUI.block( $('#payments_list ul') );
			},
			success : function (reponse) {
				appView.blockUI.unblock();
				if(reponse.success) {					
					$list_payment.html(reponse.data);
					target.parents('li').find('img').remove ();
					if(appView.page >= reponse.total) 
						$('#load-more').hide ();
					else $('#load-more').show ();
				} else {
					$list_payment.html (reponse.msg);
					$('#load-more').hide ();
				}

			}
		});
	}

});

})(jQuery);