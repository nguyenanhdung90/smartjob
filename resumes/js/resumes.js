(function ($) {
// Model: Resume
JobEngine.Models.Resume  = Backbone.Model.extend({

// when having the id, fetch into this object as a company model
	jobseeker	: {},
	
	action      : 'et_resume_sync',

	initialize  : function(data, params){
		var params = params || {};

		if ( typeof params.jobseeker != 'undefined' ){
			var jobseekerInfo 		= _.extend( params.jobseeker, {id : params.jobseeker.ID} );
			this.jobseeker_id 		= params.jobseeker.ID
			this.jobseeker 			= new JobEngine.Models.JobSeeker(params.jobseeker); 
		} else {
			if( this.has('jobseeker_id') ){
				this.jobseeker	= new JobEngine.Models.JobSeeker({id:this.get('jobseeker_id')});
			}
			else {
				this.jobseeker	= new JobEngine.Models.JobSeeker();
			}
		}
		this.set('jobseeker_data', this.jobseeker.attributes);
		this.jobseeker.on('change', this.updateJobSeeker, this);
	},
	
	updateJobSeeker	: function(){
		if (this.jobseeker.hasChanged('id') || !this.has('jobseeker_id')){
			this.set('jobseeker_id',this.jobseeker.id,{silent:true});
		}
		this.set('jobseeker_data', {
			'user_url'			: this.jobseeker.get('user_url'),
			'display_name'		: this.jobseeker.get('display_name'),
			'user_logo'			: this.jobseeker.get('et_avatar'),
			'description'		: this.jobseeker.get('description')
		},{silent:true});
	},

	parse       : function(res){
		if(!res.success){
			pubsub.trigger('je:notification',{
				msg			: res.msg,
				notice_type	: 'error'
			});
			return {};
		}
		else{
			pubsub.trigger('je:notification', {
					msg			: res.msg,
					notice_type	: 'success'
				});

			if(res.method === 'create'){
				this.set('id', res.data.resume.ID,{silent:true});
			}
			else{
				if ('jobseeker' in res.data){
					this.jobseeker.set(res.data.jobseeker,{silent:true});
					this.set('jobseeker_data', {
						'et_avatar'		: this.jobseeker.get('et_avatar'),
						'display_name'	: this.jobseeker.get('display_name'),
						'user_logo'		: this.jobseeker.get('user_logo'),
						'post_url'		: this.jobseeker.get('post_url'),
						'et_location' 	: this.jobseeker.get('et_location'),
						'et_profession_title' : this.jobseeker.get('et_profession_title'),
						'et_url'		:  this.jobseeker.get('et_url')
					},{silent:true});
				}
			}
			return res.data.resume;
		}
	},

	sync	: function(method, model, options) {
		var params = _.extend({
				type        : 'POST',
				dataType    : 'json',
				url         : et_globals.ajaxURL,
				contentType : 'application/x-www-form-urlencoded;charset=UTF-8',
				jobseeker_sync	: false
			}, options || {}),
			attrs;
		method = (options && options.method) ? options.method : method;

		if (method === 'read') {
			params.type = 'GET';
			params.data	= {id : model.id};
		} else {

			if (!params.data && model && (method === 'create' || method === 'update' || method === 'delete')) {
				/**
				 * get change attributes
				*/
				attrs	= _.clone(model.attributes);
				if (options && options.saveData && method !== 'create') {
					attrs	=	{};
					_.each(options.saveData, function(element, index){
						// render education html
						attrs[element]	=	model.attributes[element];

					});
					attrs['ID']	=	model.attributes['ID'];
					attrs['id']	=	model.attributes['id'];
				}

				if(!params.jobseeker_sync){
					delete attrs.jobseeker_data;
				}
				if('jobseeker_data' in attrs && 'user_logo' in attrs.jobseeker_data){
					delete attrs.jobseeker_data.user_logo;
				}
				params.data     = attrs;
			} 
		}
		params.data = jQuery.param ({action:model.action,method:method, content: params.data});
		//params.data = {action:model.action,method:method, content: params.data};
		// Make the request.
		return jQuery.ajax(params);
	},

	reviewResume	: function(status,options){
		options	= options || {};
		this.set('status', status,{silent:true});

		options.data = _.extend(
			('data' in options) ? options.data : {},
			{ id : this.get('ID'), status : status, method : 'reviewResume' }
		);

		options.method	= 'reviewResume';

		this.save({status:status, method:'reviewResume'}, options);
	},

	republishResume	: function(status,options){
		options	= options || {};
		this.set('status', status,{silent:true});

		options.data = _.extend(
			('data' in options) ? options.data : {},
			{ ID : this.get('ID'), status : status, method : 'update',post_status : 'pending' }
		);

		options.method	= 'update';

		this.save({status:status, method:'update'}, options);
	},


	remove : function (options) {
		options = options || {};
		var prevStatus	= this.get('status'),
			success		= (typeof options.success === 'function') ? options.success : false;

		/*options.success = function(model, resp){
			pubsub.trigger('je:job:afterRemoveJob', model, resp, prevStatus);
			if(success){success(model, resp);}
		};*/
		this.sync('delete', this, options);
	},

	approve : function( options ){
		options = options || {};
		var	success	= (options && typeof options.success === 'function') ? options.success : false;

		options.success = function(model, resp){
			pubsub.trigger('je:resume:afterApproveResume', model, resp);
			if(success){success(model, resp);}
		};

		options.wait = true;

		this.reviewResume('publish', options);
	},

	pending : function( options ){
		options = options || {};
		var	success	= (options && typeof options.success === 'function') ? options.success : false;

		options.success = function(model, resp){
			//pubsub.trigger('je:resume:afterApproveResume', model, resp);
			if(success){success(model, resp);window.location = resp.data.resume.permalink;
			}
		};

		options.wait = true;

		this.republishResume('pending', options);
	},

	reject : function (options) {
		options = options || {};
		var beforeSend	= (options && typeof options.beforeSend === 'function') ? options.beforeSend : false,
			success		= (options && typeof options.success === 'function') ? options.success : false;

		// override the success callback
		options.beforeSend = function(){
			pubsub.trigger('je:request:waiting');
			if (beforeSend){beforeSend();}
		};

		options.success = function(model, resp){
			pubsub.trigger('je:resume:afterRejectResume', model, resp);
			if(success){success(model, resp);}
		};

		this.reviewResume('reject', options);
	}

});

JobEngine.Collections.Resumes = Backbone.Collection.extend({
	model		: JobEngine.Models.Resume,
	list_title	: '',
	fetchData	: { paged: 1, position : '', rq : '', available: '', 'et_location' : '' },
	action		: 'et_fetch_resumes',
	paginateData	: {},


	/*comparator	: function(model){
		var date	= new Date(model.get('post_date'));

		// turn the whole things into a string & turn back into a negative number
		return -(parseInt(date.getTime(),10));
	},*/

	setData: function(args){
		this.fetchData = args;
		if (!('paged' in this.fetchData)){
			this.fetchData.paged = 1;
		}

		return this;
	},

	nextPage : function(options){
		var collection = this,
			params = {
				reset: false,
				add: true,
				success: function(data, resp){}
			}

		params = _.extend(params, options);

		//collection.fetchData.paged++;
		collection.fetchData.paged++;

		var listView = $('#resumes');
		var i =0;

		this.filter(collection.fetchData, params );
	},

	filter : function(data, options) {

		this.fetchData = _.extend({ paged : 1}, data  );

		var collection	= this,
			params	= _.extend( {
				data : this.fetchData
			}, options || {} );

		params.beforeSend = function (){
			collection.trigger('filterBeforeSend');
			if (options && typeof options.beforeSend === 'function'){
				options.beforeSend();
			}
		};

		params.success = function(data, resp){
			collection.trigger('filterSuccess');
			if (options && typeof options.success === 'function'){

				// call callback
				options.success(data, resp);
			}
		};

		// fetch for new jobs
		this.fetch(params);
	},

	parse: function(resp){
		if (typeof resp.data.resumes != 'undefined'){
			return _.map( resp.data.resumes, function (data){
				var resume = false;
				if ( typeof resp.data.jobseekers != 'undefined' ){
					var jobseeker = false;

					_.each( resp.data.jobseekers, function(element, i){
						if (element.ID == data.post_author){
							resume = new JobEngine.Models.Resume(data, {jobseeker: element});
						}
					} );
				} else {
					resume = new JobEngine.Models.Resume(data);
				}
				return resume;
			} );
		} else {
			return [];
		}
	},

	sync	: function(method, model, options) {
		var params = _.extend({
				type        : 'POST',
				dataType    : 'json',
				url         : et_globals.ajaxURL,
				contentType : 'application/x-www-form-urlencoded;charset=UTF-8'
			}, options || {});

		if (method == 'read') {
			params.type = 'GET';
			params.data = _.extend(params.data, model.JSON);
		}

		if (!params.data && model && (method == 'create' || method == 'update' || method == 'delete')) {
			params.data = model.toJSON();
		}

		if (params.type !== 'GET') {
			params.processData = false;
		}

		params.action = model.action;
		params.data = jQuery.param({action:params.action,method:method,content:params.data});

		// Make the request.
		return jQuery.ajax(params);
	}
});

JobEngine.Views.ResumeListView = Backbone.View.extend({
	//el: '#resumes',
	initialize: function(){
		var view = this;

		this.loading = new JobEngine.Views.BlockUi();
		//this.resumes = new JobEngine.Collections.Resumes();

		this.listView = [];
		if( !!this.collection && !!this.el ){
			this.collection.each( function( model, index, coll ){
				var el	= view.$( 'li:eq(' + index + ')' );
				if( el.length !== 0 ){
					// use the index of the collection to generate the matching item view
					view.listView[index] = new JobEngine.Views.ResumeListItem({el:el, model:model});

				}
			});
		} else {
			this.collection = new JobEngine.Collections.Resumes();
		}

		//this.collection.on('unshift', this.addJob, this);
		this.collection.on('remove', this.removeResume, this);

		// hook events
		this.collection.on('reset', this.onChangeResumes, this);
		this.collection.on('add', this.onAddResumes, this);
	},

	listLoading: function(bool){
		if (bool){
			this.loading.block($('#resumes'));
		}else {
			this.loading.unblock();
		}
	},

	test: function(){
		this.collection.filter({
			skill: 'HTML'
		}, {
			success: function(data, resp){
				this.collection.trigger('change');
			}
		});
	},

	setFilterParams: function(data){
		this.collection.setData(data);
	},

	nextPage: function(params){
		view = this;

		// custom param
		success = params.success || function(data, resp){};


		params.success = function(data,resp){			

			if (resp.data.total_pages <= resp.data.paged){
				view.trigger('endOfPages', resp.data.paged);
			}else {
				view.trigger('notEndOfPages', resp.data.paged);
			}

			success(data, resp)
		};

		this.collection.nextPage(params);
	},

	filter: function(data, options){
		var options = options || {beforeSend : function(){}, success: function(){}};
		var success = options.success || function(){};
		var beforeSend = options.beforeSend || function(){};
		var view = this;
		var $list	= this.$el.find('ul');
		var options = _.extend( options, {
			reset: true,
			beforeSend: function(data, resp){
				view.listLoading(true);
				beforeSend();
			},
			success: function(data, resp){
				view.listLoading(false);
				// display message if no resumes found
				if ( resp.data.resumes.length == 0 ){
					$list.html('').append('<li class="no-job-found">' + et_resume.no_resume_found + '</li>');
					view.$('div.button-more').hide();
				} else {
					//$('#resumes_empty').hide();
					//$list.html('');
					//view.$el.show();
				}

				if (resp.data.total_pages <= resp.data.paged){
					view.trigger('endOfPages', resp.data.paged);
				}else {
					view.trigger('notEndOfPages', resp.data.paged);
				}
				success();
			}
		} );

		this.collection.filter(data, options);
	},
	// after a job is removed from the collection, remove its view
	removeResume	: function(resume,col,options){

		// remove the job item view from the array listView
		var itemView = this.listView.splice( options.index, 1 );
		if( itemView.length > 0 ){
			itemView[0].$el.fadeOut('slow',function(){
				itemView[0].remove().undelegateEvents();

				// after hiding the removed job, publish this event to add the job to the correct collection
				pubsub.trigger('je:resume:afterRemoveResumeView', resume);
			});
		}
	},

	onChangeResumes: function(){
		this.renderResumes();
	},


	comparator	: function(model){
		var date	= new Date(model.get('post_date'));

		// turn the whole things into a string & turn back into a negative number
		return -(parseInt(date.getTime(),10));
	},

	onAddResumes: function(resume, col,options ){		
		var listView = this.$el.find('ul');
		var item 		= new JobEngine.Views.ResumeListItem({model: resume}),
			$itemEl		= item.render().$el.hide() ,
			$existingItems	= this.$('li.resume-item');

		var index		= (options && 'index' in options) ? options.index : $existingItems.length;
		var	position	=  $existingItems.eq(index);

		// insert the view at the correct position, same index in collection
		if ( this.collection.length === 0 || position.length === 0 ){
			$itemEl.appendTo(listView).fadeIn('slow');
		}
		else{
			$itemEl.insertBefore(position).fadeIn('slow');
		}


		this.listView.splice( index, 0, item );

	},
	renderResumes : function(){
		var listView = this.$el.find('ul');

		// clear html
		listView.html('');

		// pushing data
		if (this.collection.models.length > 0){
			_.each(this.collection.models, function(resume, index, list){
				var item = new JobEngine.Views.ResumeListItem({model: resume});

				item.render().$el.appendTo(listView).hide().fadeIn();
			});
		}
	}
});

/**
 * Backbone view for a Resume item
 */
JobEngine.Views.ResumeListItem = Backbone.View.extend({
	tagName: 	'li',
	className: 	'resume-item',
	events : {
		'click .actions .action-approve'	: 'approveResume',
		'click .actions .action-reject'		: 'rejectResume'
	},
	initialize: function(params){
		// build template
		_.templateSettings = {
		    evaluate    : /<#([\s\S]+?)#>/g,
			interpolate : /\{\{(.+?)\}\}/g,
			escape      : /<%-([\s\S]+?)%>/g
		};
		this.template = _.template($('#resume_list_item').html() );

		// build model
		if ( typeof params.model != 'undefined')
			this.model = params.model;

		this.blockUi = new JobEngine.Views.BlockUi();
	},

	render: function(){

		if (typeof this.model != 'undefined'){
			this.$el.html( this.template(this.model.toJSON()) );
		}
		return this;
	},

	renderAprove: function(method){

		if(typeof this.model.get('post_date') != 'undefined'){

			var time = this.convertTimeToID(this.model.get('post_date'));
			this.$el.attr( "id",time);
		}

		if ( typeof this.model.get('ID') != 'undefined' && method == 1){
			this.$el.addClass('post-approve ' + this.model.get('ID'));
		}

		if (typeof this.model != 'undefined'){
			this.$el.html( this.template(this.model.toJSON()) );
		}
		return this;
	},
	convertTimeToID :function(date){

		var reggie = /(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/;
		var dateArray = reggie.exec(date);

		var dateObject = new Date(
		    (+dateArray[1]),
		    (+dateArray[2])-1, // Careful, month starts at 0!
		    (+dateArray[3]),
		    (+dateArray[4]),
		    (+dateArray[5]),
		    (+dateArray[6])
		);
		var offet = dateObject.getTimezoneOffset();
		var time = dateObject.getTime() - offet*60*1000;
		return time/1000;
	},

	// publish the event to call the modal edit job
	rejectResume : function(event){
		event.preventDefault();
		pubsub.trigger('je:resume:onReject', {model : this.model, itemView : this});
	},

	// approve this job, when successful, publish an event to modify collection and view,
	approveResume : function(event){
		event.preventDefault();
		var view = this;
		this.model.approve({
			silent:true,
			beforeSend: function(){
				view.blockItem();
			},
			success: function(){
				view.unblockItem();
			}
		});
	},

	blockItem : function(){
		this.blockUi.block(this.$el);
	},
	unblockItem : function(){
		this.blockUi.unblock();
	}

});

JobEngine.Views.EditedTaxonomyItem = Backbone.View.extend({
	'tagName'	: 'li',
	events 		: {
		'click a.delete' : 'deleteItem'
	},
	template 	: null,
	initialize: function(data, template){
		this.data = data;
		_.templateSettings = {
		    evaluate    : /<#([\s\S]+?)#>/g,
			interpolate : /\{\{(.+?)\}\}/g,
			escape      : /<%-([\s\S]+?)%>/g
		};

		this.template = _.template(template);
	},
	render : function(){
		this.$el.html(this.template(this.data));
		return this;
	},
	deleteItem: function(event){
		event.preventDefault();
		this.$el.fadeOut('normal', function(){
			$(this).remove();
		});
	}
});

JobEngine.Views.ResumeItem = Backbone.View.extend ({

});
/**
 * Education edit view
 */

JobEngine.Views.EducationView = Backbone.View.extend({
	template : '',
	data : {
		fromMonth: '',
		fromYear: '',
		toMonth: '',
		toYear: '',
		name: '',
		current: '',
		degree: '',
		from: { month: '', year: '' },
		to: { month: '', year: '' }
	},
	className : 'edu-form',
	events : {
		'click .delete-item' 	: 'deleteView',
		'change input.curr' 	: 'toggleCurrent',
		'click .check-current'  : 'checkedCurrent',
		'change input'			: 'validate',
		'change select'			: 'validate'
	},
	initialize: function(params){
		this.data = _.extend( this.data, params.data );
		_.templateSettings = {
		    evaluate    : /<#([\s\S]+?)#>/g,
			interpolate : /\{\{(.+?)\}\}/g,
			escape      : /<%-([\s\S]+?)%>/g
		};
		this.template = _.template($('#education_view').html());

		this.onDelete = params.onDelete || function(){ };
	},
	toObject: function(){
		var element = this.$el;

		this.data.name 		= element.find('input.name').val();
		this.data.degree 	= element.find('input.degree').val();
		this.data.fromMonth = element.find('select.fromMonth').val();
		this.data.fromYear 	= element.find('select.fromYear').val();
		this.data.toMonth 	= element.find('select.toMonth').val();
		this.data.toYear 	= element.find('select.toYear').val();
		this.data.current 	= element.find('input.curr').is(':checked') ? 1 : 0;

		this.data.from 		= { month : this.data.fromMonth, year : this.data.fromYear }
		this.data.to 		= { month : this.data.toMonth, year : this.data.toYear }

		this.data.fromText 	= this.data.fromMonth + '-' + this.data.fromYear;
		this.data.toText 	= this.data.toMonth + '-' + this.data.toYear;

		return this.data;
	},
	errorResp : function(msg, ele){
		return {
				success : false,
				msg : msg
			}
	},

	validate: function(){
		var data = this.toObject();
		var errors = [];
		var view = this;

		if (data.name == '')
			errors.push({ element: this.$el.find('input.name'), 'msg' : et_resume.school_name_invalid });
			//return this.errorResp("School name is empty", this.$el.find('input.name'));

		// checking
		if ( data.fromYear == '' )
			errors.push({ element: this.$el.find('select.fromYear'), 'msg' : et_resume.from_date_invalid });

		if (data.toYear == '' && !data.current)
			errors.push({ element: this.$el.find('select.toYear'), 'msg' : et_resume.to_date_invalid });
			//return this.errorResp('To year is missing');


		var fromDate = new Date(data.fromYear, data.fromMonth);
		var toDate = new Date(data.toYear, data.toMonth);

		if ( ( parseInt(data.toYear) < parseInt(data.fromYear) ) || (toDate < fromDate && !data.current && data.toMonth != '' && data.toYear != '' ) ){
			errors.push({ element: this.$el.find('select.fromMonth'), 'msg' : et_resume.date_range_invalid });
			errors.push({ element: this.$el.find('select.fromYear'), 'msg' : et_resume.date_range_invalid });
			errors.push({ element: this.$el.find('select.toMonth'), 'msg' : et_resume.date_range_invalid });
			errors.push({ element: this.$el.find('select.toYear'), 'msg' : et_resume.date_range_invalid });
		}

		// clear all error input
		_.each(this.$el.find('input[type=text],select'), function(element, index){
			if ( $(element).is('INPUT') )
				$(element).removeClass('error');
			else if ( $(element).is('SELECT') ){
				var parent = $(element).parent();
				$(parent).removeClass('error')
			}
		});
		view.$el.find('.sub-row .msg-container .error').html('').hide();

		// add error classname into errors fields
		if (errors.length > 0){
			_.each(errors, function(error, index){
				var msg = view.$el.find('.sub-row .msg-container .error').show();
				if ( $(error.element).is('INPUT') )
					error.element.addClass('error');
				else if ( $(error.element).is('SELECT') ){
					var parent = $(error.element).parent();
					parent.addClass('error')
				}

				if ( $.trim(msg.html()) == '')
					msg.html( error.msg );
			});

			return false;
		}else {
			return true;
		}
	},
	deleteView : function(event){
		event.preventDefault();
		this.onDelete(this);
		this.$el.fadeOut();
	},

	checkedCurrent : function (event) {
		var $target	 =	$(event.currentTarget),
			$checkbox =  this.$el.find('input.curr');

		if( $checkbox.is(':checked') ) {
			$checkbox.attr('checked', false);
		} else {
			$checkbox.attr('checked', true);
		}
		$checkbox.trigger('change');

	},

	toggleCurrent: function(event){
		var selectboxes = this.$el.find('.select-to-month, .select-to-year');

		// if current checkbox is checked, disable select box
		if ($(event.currentTarget).is(':checked') ){
			$(selectboxes).addClass('disabled');
			$(selectboxes).children('select').attr('disabled','disabled');
		} // if current checkbox isn't checked, enable select box
		else {
			$(selectboxes).removeClass('disabled');
			$(selectboxes).children('select').removeAttr('disabled','disabled');
		}
	},
	render: function(){
		html 	= this.template(this.data);
		this.$el.html(html).addClass(this.className).find('input.curr');//.trigger('change');

		var selectboxes = this.$el.find('.select-to-month, .select-to-year');
		// if current checkbox is checked, disable select box
		if (this.data.current != 0 ){

			$(selectboxes).addClass('disabled');
			$(selectboxes).children('select').attr('disabled','disabled');
		}

		return this;
	}
});

/**
 * Education edit view
 */
JobEngine.Views.ExperienceView = Backbone.View.extend({
	template : '',
	data : {
		fromMonth: '',
		fromYear: '',
		toMonth: '',
		toYear: '',
		name: '',
		position: '',
		from : '',
		to : '', 
		current : ''
	},
	className : 'edu-form',
	events : {
		'change input.curr' 	: 'toggleCurrent',
		'click .check-current'  : 'checkedCurrent',
		'click .delete-item' 	: 'deleteView',
		'change input'			: 'validate',
		'change select'			: 'validate'
	},
	initialize: function(params){
		this.data = $.extend(this.data, params.data);
		_.templateSettings = {
		    evaluate    : /<#([\s\S]+?)#>/g,
			interpolate : /\{\{(.+?)\}\}/g,
			escape      : /<%-([\s\S]+?)%>/g
		};
		this.template = _.template($('#experience_view').html());

		this.onDelete = params.onDelete || function(){};

	},

	toObject: function(){

		var element = this.$el;

		this.data.name 		= element.find('input.name').val();
		this.data.position 	= element.find('input.position').val();
		this.data.fromMonth = element.find('select.fromMonth').val();
		this.data.fromYear 	= element.find('select.fromYear').val();
		this.data.toMonth 	= element.find('select.toMonth').val();
		this.data.toYear 	= element.find('select.toYear').val();
		this.data.current 	= element.find('input.curr').is(':checked') ? 1 : 0;

		this.data.from 		= { month : this.data.fromMonth, year : this.data.fromYear }
		this.data.to 		= { month : this.data.toMonth, year : this.data.toYear }

		this.data.fromText 	= this.data.fromMonth + '-' + this.data.fromYear;
		this.data.toText 	= this.data.toMonth + '-' + this.data.toYear;

		return this.data;
	},

	errorResp : function(msg){
		return {
				success : false,
				msg : msg
			}
	},

	validate: function(){
		var data = this.toObject();
		var errors = [];
		var view = this;

		if (data.name == '')
			errors.push({ element: this.$el.find('input.name'), 'msg' : et_resume.company_name_invalid });
			//return this.errorResp("School name is empty", this.$el.find('input.name'));

		if (data.position == '')
			errors.push({ element: this.$el.find('input.position'), 'msg' : et_resume.position_invalid });

		// checking
		if ( data.fromYear == '' )
			errors.push({ element: this.$el.find('select.fromYear'), 'msg' : et_resume.from_date_invalid });
			//return this.errorResp("From year is missing");
		
		if (data.toYear == '' && !data.current)
			errors.push({ element: this.$el.find('select.toYear'), 'msg' : et_resume.to_date_invalid });
			//return this.errorResp('To year is missing');

		var fromDate = new Date(data.fromYear, data.fromMonth);
		var toDate = new Date(data.toYear, data.toMonth);

		if ( ( parseInt(data.toYear) < parseInt(data.fromYear) ) || (toDate < fromDate && !data.current && data.toMonth != '' && data.toYear != '' ) ){
			errors.push({ element: this.$el.find('select.fromMonth'), 'msg' : et_resume.date_range_invalid });
			errors.push({ element: this.$el.find('select.fromYear'), 'msg' : et_resume.date_range_invalid });
			errors.push({ element: this.$el.find('select.toMonth'), 'msg' : et_resume.date_range_invalid });
			errors.push({ element: this.$el.find('select.toYear'), 'msg' : et_resume.date_range_invalid });
		}

		// clear all error input
		_.each(this.$el.find('input[type=text],select'), function(element, index){
			if ( $(element).is('INPUT') ) {
				$(element).removeClass('error');
			}
			else if ( $(element).is('SELECT') ){
				var parent = $(element).parent();
				$(parent).removeClass('error');
			}
		});

		view.$el.find('.sub-row .msg-container .error').html('').hide();

		// add error classname into errors fields
		if (errors.length > 0){
			_.each(errors, function(error, index){
				var msg = view.$el.find('.sub-row .msg-container .error').show();
				if ( $(error.element).is('INPUT') )
					error.element.addClass('error');
				else if ( $(error.element).is('SELECT') ){
					var parent = $(error.element).parent();
					parent.addClass('error');
				}

				if ( $.trim(msg.html()) == '') {
					msg.html(error.msg);
				}
			});
			return false;
		} else {
			return true;
		}
	},
	deleteView : function(event){
		event.preventDefault();
		this.onDelete(this);
		this.$el.fadeOut();
	},

	checkedCurrent : function (event) {
		var $target	 =	$(event.currentTarget),
			$checkbox =  this.$el.find('input.curr');

		if( $checkbox.is(':checked') ) {
			$checkbox.attr('checked', false);
		} else {
			$checkbox.attr('checked', true);
		}
		$checkbox.trigger('change');

	},

	toggleCurrent: function(event){
		var selectboxes = this.$el.find('.select-to-month, .select-to-year');

		// if current checkbox is checked, disable select box
		if ($(event.currentTarget).is(':checked') ){
			$(selectboxes).addClass('disabled');
			$(selectboxes).children('select').attr('disabled','disabled');
		} // if current checkbox isn't checked, enable select box
		else {
			$(selectboxes).removeClass('disabled');
			$(selectboxes).children('select').removeAttr('disabled','disabled');
		}
	},

	render: function(){
		var html 		= this.template(this.data);	
		this.$el.html(this.template(this.data)).addClass( this.className );

		var selectboxes = this.$el.find('.select-to-month, .select-to-year');
		// if current checkbox is checked, disable select box
		if (this.data.current != 0 ){

			$(selectboxes).addClass('disabled');
			$(selectboxes).children('select').attr('disabled','disabled');
		}
		return this;
	}
});

$.fn.styleSelect = function(event){
	this.each(function(){
		//var title = $(this).attr('title') || $(this).html;
		var title 		= $(this).find('option:selected').html();
		var arrow 		= "";

		if ( $(this).parent().hasClass('styled-select') ){
			return false;
		}

		$(this).wrap('div.styled-select');
		var container 	= $(this).parent();

		// if ($(".select-style select").attr('arrow') !== undefined) 
		// 	arrow = " " + $(".select-style select").attr('arrow');

		// if( $('option:selected', this).val() != ''  ) title = $('option:selected',this).text() + arrow ;

		$(this)
			.css({'z-index':10,'opacity':0,'-khtml-appearance':'none'})
			.after('<span class="select">' + title + arrow + '</span>')
			.change(function(){
				val = $('option:selected',this).text() + arrow;
				container.find('span.select').text(val);
				// $(this).next().text(val);
			});
	});
}
}) (jQuery);