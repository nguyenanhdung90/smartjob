// declare everything inside this object
var JobEngine = JobEngine || {};

JobEngine.Models        = JobEngine.Models || {};
JobEngine.Collections   = JobEngine.Collections || {};
JobEngine.Views         = JobEngine.Views || {};
JobEngine.Routers       = JobEngine.Routers || {};

// the pub/sub object for managing event throughout the app
JobEngine.pubsub	= JobEngine.pubsub || {};
_.extend(JobEngine.pubsub, Backbone.Events);

// create a shorthand for our pubsub
var pubsub  = pubsub || JobEngine.pubsub;

// create a shorthand for the params used in most ajax request
JobEngine.ajaxParams = {
	type        : 'POST',
	dataType    : 'json',
	url         : et_globals.ajaxURL,
	contentType : 'application/x-www-form-urlencoded;charset=UTF-8'
};
var ajaxParams = JobEngine.ajaxParams;

/*************************************************
//                                              //
//              JOB ENGINE MODELS				//
//                                              //
*************************************************/
// Model: Job
(function ($) {
	  $.fn.serializeObject = function(){

    var self = this,
        json = {},
        push_counters = {},
        patterns = {
            "validate": /^[a-zA-Z][a-zA-Z0-9_]*(?:\[(?:\d*|[a-zA-Z0-9_]+)\])*$/,
            "key":      /[a-zA-Z0-9_]+|(?=\[\])/g,
            "push":     /^$/,
            "fixed":    /^\d+$/,
            "named":    /^[a-zA-Z0-9_]+$/
        };


    this.build = function(base, key, value){
        base[key] = value;
        return base;
    };

    this.push_counter = function(key){
        if(push_counters[key] === undefined){
            push_counters[key] = 0;
        }
        return push_counters[key]++;
    };

    $.each($(this).serializeArray(), function(){

        // skip invalid keys
        if(!patterns.validate.test(this.name)){
            return;
        }

        var k,
            keys = this.name.match(patterns.key),
            merge = this.value,
            reverse_key = this.name;

        while((k = keys.pop()) !== undefined){

            // adjust reverse_key
            reverse_key = reverse_key.replace(new RegExp("\\[" + k + "\\]$"), '');

            // push
            if(k.match(patterns.push)){
                merge = self.build([], self.push_counter(reverse_key), merge);
            }

            // fixed
            else if(k.match(patterns.fixed)){
                merge = self.build([], k, merge);
            }

            // named
            else if(k.match(patterns.named)){
                merge = self.build({}, k, merge);
            }
        }

        json = $.extend(true, json, merge);
    });

    return json;
};

JobEngine.Models.Job  = Backbone.Model.extend({

// when having the id, fetch into this object as a company model
	author	: {},
	action      : 'et_job_sync',

	initialize  : function(){
		// bind all functions to this object
		_.bindAll(this, 'updateJobAuthor');

		if( this.has('author_id') ){
			this.author	= new JobEngine.Models.Company({id:this.get('author_id')});
		}
		else {
			this.author	= new JobEngine.Models.Company();
		}
		this.author.on('change', this.updateJobAuthor, this);
	},

	updateJobAuthor	: function(){

		if (this.author.hasChanged('id') || !this.has('author_id')){
			this.set('author_id',this.author.id,{silent:true});
		}
		this.set('author_data', {
			'user_url'		: this.author.get('user_url'),
			'display_name'	: this.author.get('display_name'),
			'user_logo'		: this.author.get('user_logo'),
			'post_url'		: this.author.get('post_url')
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
			if(res.method === 'create'){
				this.set('id', res.data.job.id,{silent:true});
				return {};
			}
			else{
				pubsub.trigger('je:notification', {
					msg			: res.msg,
					notice_type	: 'success'
				});

				if ('author' in res.data){
					this.author.set(res.data.author,{silent:true});
					this.set('author_data', {
						'user_url'		: this.author.get('user_url'),
						'display_name'	: this.author.get('display_name'),
						'user_logo'		: this.author.get('user_logo'),
						'post_url'		: this.author.get('post_url'),
						'recent_location' : this.author.get('recent_location')
					},{silent:true});
				}
				return res.data.job;
			}
		}
	},

	sync	: function(method, model, options) {

		var params = _.extend({
				type        : 'POST',
				dataType    : 'json',
				url         : et_globals.ajaxURL,
				contentType : 'application/x-www-form-urlencoded;charset=UTF-8',
				author_sync	: false

			}, options || {}),
			attrs;
		method = (options && options.method) ? options.method : method;

		if (method === 'read') {
			params.type = 'GET';
			params.data	= {id : model.id};
		}

		if (!params.data && model && (method === 'create' || method === 'update' || method === 'delete' )) {
			attrs	= _.clone(model.attributes);
			if(!params.author_sync){
				delete attrs.author_data;
			}
			if('author_data' in attrs && 'user_logo' in attrs.author_data){
				delete attrs.author_data.user_logo;
			}
			params.data     = attrs;
		}

		params.data = jQuery.param({action:model.action,method:method,content:params.data});
		// Make the request.
		return jQuery.ajax(params);
	},

	reviewJob	: function(status,options){
		options	= options || {};

		this.set('status', status,{silent:true});

		options.data = _.extend(
			('data' in options) ? options.data : {},
			{ id : this.get('id'), status : status, method : 'reviewJob' }
		);

		options.method	= 'reviewJob';

		this.save({status:status, method:'reviewJob'}, options);
	},

	approve : function( options ){
		options = options || {};
		var	success	= (options && typeof options.success === 'function') ? options.success : false;

		options.success = function(model, resp){
			pubsub.trigger('je:job:afterApproveJob', model, resp);
			if(success){success(model, resp);}
		};

		options.wait = true;

		this.reviewJob('publish', options);
	},

	reject : function( options ){
		options = options || {};
		var beforeSend	= (options && typeof options.beforeSend === 'function') ? options.beforeSend : false,
			success		= (options && typeof options.success === 'function') ? options.success : false;
		// override the success callback
		options.beforeSend = function(){
			pubsub.trigger('je:request:waiting');
			if (beforeSend){beforeSend();}
		};

		options.success = function(model, resp){
			pubsub.trigger('je:job:afterRejectJob', model, resp);
			if(success){success(model, resp);}
		};
		this.reviewJob('reject', options);
	},

	archive: function ( options ) {
		options = options || {};
		var prevStatus	= this.get('status'),
			success		= (typeof options.success === 'function') ? options.success : false;

		// override the success callback
		options.success = function(model, resp){
			pubsub.trigger('je:job:afterArchiveJob', model, resp, prevStatus);
			if(success){success(model, resp);}
		};

		this.reviewJob('trash', options);
	},

	remove : function (options) {
		options = options || {};
		var prevStatus	= this.get('status'),
			success		= (typeof options.success === 'function') ? options.success : false;

		this.sync('delete', this, options);
	}
});

// Model: Authentication
// used for authenticate & authorize the user
JobEngine.Models.Auth     = Backbone.Model.extend({

	params	: {
		type		: 'POST',
		dataType	: 'json',
		url			: et_globals.ajaxURL,
		contentType	: 'application/x-www-form-urlencoded;charset=UTF-8'
	},

	setUserName	: function ( value ) {
		this.set({ user_name : value }, {silent: true});
	},

	setEmail    : function( value ){
		this.set({ user_email : value }, {silent: true});
	},

	setPass : function(value){
		this.set({user_pass : value}, {silent: true});
	},

	setUserKey : function(value){
		this.set({user_key : value}, {silent : true});
	},

	changePassword : function(options){

		var params		= _.extend({
				data	: {
					action			: 'et_change_pass',
					user_old_pass	: this.get('user_old_pass'),
					user_pass		: this.get('user_pass'),
					user_pass_again	: this.get('user_pass_again')
				}
			},this.params,options || {});

		params.beforeSend	= function(){
			pubsub.trigger('je:request:waiting');
			if(options && typeof options.beforeSend === 'function'){
				options.beforeSend();
			}
		};

		params.success	= function(data,status,jqXHR){
			// trigger an event after change password
			pubsub.trigger('je:response:changePassword', data, status, jqXHR);
			if(options && typeof options.success === 'function'){
				options.success(data,status,jqXHR);
			}
		};

		params.error	= function(jqXHR, textStatus, errorThrown){
			// throw a notice
			pubsub.trigger('je:notification', {
				msg : textStatus,
				notice_type : 'error'
			});
			if(options && typeof options.error === 'function'){
				options.error(jqXHR, textStatus, errorThrown);
			}
		};

		return jQuery.ajax(params);
	},

	doAuth  : function(type, options){
		var params;

		if ( type === 'login' ){
			this.unset('user_pass_again', {silent: true});
			type = 'et_login';
		}
		else if (type === 'register'){
			type = 'et_register';
		}
		else {
			return false;
		}

		params  = _.extend({
			data : {
				action      : type,
				user_email  : this.get('user_email'),
				user_pass   : this.get('user_pass'),
				user_name   : this.get('user_name'),
				role		: this.get('role')
			}
		},this.params,options || {});

		params.data	=	_.extend(params.data, options);
		if( parseInt(et_globals.use_captcha) ) {
			params.data['recaptcha_challenge_field']	=	this.get('recaptcha_challenge_field');
			params.data['recaptcha_response_field']		=	this.get('recaptcha_response_field');
		}
		// overwrite before send event
		params.beforeSend  = function(){
			pubsub.trigger('je:request:waiting');
			if(options && typeof options.beforeSend === 'function'){
				options.beforeSend();
			}
		};

		// overwrite success event
		params.success  = function(data,status,jqXHR){
			pubsub.trigger('je:response:auth', data, status, jqXHR);

			if(options && typeof options.success === 'function'){

				options.success(data,status,jqXHR);
			}
		};

		params.error = function(jqXHR, textStatus, errorThrown){
			// throw a notice
			pubsub.trigger('je:notification', {
				msg : textStatus,
				notice_type : 'error'
			});
			if(options && typeof options.error === 'function'){

				options.error(jqXHR, textStatus, errorThrown);
			}
		};

		return jQuery.ajax(params);
	},

	doLogout    : function(options){
		var params		= _.extend({
			data : {action:'et_logout'}
		},this.params,options || {});

		if(options && typeof options.beforeSend === 'function'){
			params.beforeSend = options.beforeSend;
		}
		params.success  = function(data,status,jqXHR){
			pubsub.trigger('je:response:logout', data, status, jqXHR);
			if(options && typeof options.success === 'function'){
				options.success(data,status,jqXHR);
			}
		};

		return jQuery.ajax(params);
	},

	doResetPassword : function(options){

		var params = _.extend({
			data : {
				action: 'et_reset_password',
				user_login : this.get('user_name'),
				user_pass : this.get('user_pass'),
				user_key : this.get('user_key')
			}
		},this.params, options || {});

		params.beforeSend = function(){
			pubsub.trigger('je:request:waiting');
			if(options && typeof options.beforeSend === 'function'){
				options.beforeSend();
			}
		};

		params.success	= function(data, status, jqXHR){
			pubsub.trigger('je:response:reset_password', data, status, jqXHR);
			if (options && typeof options.success === 'function'){
				options.success(data, status, jqXHR);
			}
		};

		return jQuery.ajax(params);
	},

	doRequestResetPassword : function(options){

		var params = _.extend({
			data : {
				action: 'et_request_reset_password',
				user_login : this.get('user_email')
			}
		},this.params,options || {});

		params.beforeSend = function(){
			pubsub.trigger('je:request:requestResetPassWaiting');
			if(options && typeof options.beforeSend === 'function'){
				options.beforeSend();
			}
		};

		params.success	= function(data, status, jqXHR){
			pubsub.trigger('je:response:request_reset_password', data, status, jqXHR);
			if (options && typeof options.success === 'function'){
				options.success(data, status, jqXHR);
			}
		};

		return jQuery.ajax(params);
	}

});


// Model: Company
JobEngine.Models.Company  = JobEngine.Models.Auth.extend({

	defaults    : {
		display_name    : '',
		user_url		: '',
		post_url		: '',
		recent_location : ''
	},

	action  : 'et_company_sync',
	role 	: 'company',

	initialize	: function(){
		//_.bindAll(this);
		JobEngine.Models.Auth.prototype.initialize.call();
	},

	parse	: function(res){
		if(!res.success){
			pubsub.trigger('je:error:user_sync',res);
			return {};
		}
		else {
			return res.data;
		}
	},

	renderListItem : function(){
		return this.itemTemplate(this.toJSON);
	},

	setName	: function(value){
		this.set({display_name: value},{silent:true});
	},

	getName	: function(){
		return this.get('display_name');
	},

	setUrl	: function(value){
		this.set({user_url: value},{silent:true});
	},

	getUrl	: function(){
		return this.get('user_url');
	},

	setLocation : function (value) {
		this.set({recent_location : value}, {silent: true});
	},
	getLocation	: function(){
		return this.get('recent_location');
	},
	getApplyMethod : function () {
		return this.get('apply_method');
	},
	getApplyEmail : function () {
		return this.get('apply_email');
	},
	getApplicantDetail : function () {
		return this.get('applicant_detail');
	},
	setApplyMethod : function (value) {
		this.set({apply_method : value}, {silent: true});
	},
	setApplyEmail : function (value) {
		this.set({apply_email : value}, {silent: true});
	},
	setApplicantDetail : function (value) {
		this.set({applicant_detail : value}, {silent: true});
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
			params.action = model.action;
			params.data = {
				'id' : (model.id) ? model.id : '',
				'login_name' : (model.login_name) ? model.login_name : ''
			};
		}
		if (!params.data && model && (method == 'create' || method == 'update' || method == 'delete')) {
			params.action   = model.action;
			params.data     = model.toJSON();
		}

		if (params.type !== 'GET') {
			params.processData = false;
		}

		params.data = jQuery.param({action:params.action,method:method,content:params.data});
		// Make the request.
		return jQuery.ajax(params);
	}
});

/*************************************************
//                                              //
//              JOB ENGINE COLLECTIONS			//
//                                              //
*************************************************/
JobEngine.Collections.Jobs = Backbone.Collection.extend({
	model		: JobEngine.Models.Job,
	list_title	: '',
	fetchData	: {paged: 1, job_type : '', job_category : '', s : '', location: '' },
	action		: 'et_fetch_jobs',
	paginateData	: {},

	comparator	: function(job){
		var jobDate	= new Date(job.get('post_date'));

		// turn the whole things into a string & turn back into a negative number
		return -(parseInt(job.get('featured') + "" + jobDate.getTime(),10));
	},

	setData: function(args){
		this.fetchData = args;
		if (!('paged' in this.fetchData)){
			this.fetchData.paged = 1;
		}

		return this;
	},

	nextPage : function(options){

		var collection = this,
			params;

		if (!('paged' in this.fetchData)){
			this.fetchData.paged = 2;
		}
		else{
			this.fetchData.paged++;
		}

		var params = _.extend({data : this.fetchData, add : true},options || {});

		params.beforeSend = function (){
			collection.trigger('nextPageBeforeSend');
			if(options && typeof options.beforeSend === 'function'){
				options.beforeSend();
			}
		};

		params.success = function(data, resp){
			collection.trigger('nextPageSuccess');
			if(options && typeof options.success === 'function'){
				options.success(data, resp);
			}
		};

		// fetch for new jobs
		this.fetch(params);
	},

	filter : function(options) {
		var collection	= this,
			params	= _.extend( {
				data : _.extend( { paged : 1 }, this.fetchData )
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
				options.success(data, resp);
			}
			pubsub.trigger('je:afterLoadJob' , data , resp );
		};


		// fetch for new jobs
		this.fetch(params);
	},

	parse : function(response){
		if ( response.data ){
			if( 'list_title' in response.data){
				this.list_title	= response.data.list_title;
			}
			this.paginateData = _.clone( response.data );
			delete this.paginateData.jobs;

			return _.map(response.data.jobs, function(data){
				var job_model		= new JobEngine.Models.Job(data);
				if( data.author_id in response.data.authors ){
					job_model.author.set(response.data.authors[data.author_id],{silent:true}); // we don't want to trigger author-change event here
					job_model.updateJobAuthor(); // so we update the author_data in job model manually
				}
				return job_model;
			});
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

JobEngine.Collections.Companies = Backbone.Collection.extend({
	model: JobEngine.Models.Company,
	initialize: function(){}
});

/*************************************************
//                                              //
//              UTILITIES VIEWS                 //
//                                              //
*************************************************/
// View: AJAX Image Uploader
JobEngine.Views.File_Uploader	= Backbone.View.extend({
	initialize	: function(){
		_.bindAll(this, 'onFileUploaded', 'onFileAdded' , 'onFilesBeforeSend' , 'onUploadComplete' );

		this.uploaderID	= (this.options.uploaderID) ? this.options.uploaderID : 'et_uploader';

		this.config	= {
			runtimes			: 'gears,html5,flash,silverlight,browserplus,html4',
			multiple_queues		: true,
			multipart			: true,
			urlstream_upload	: true,
			multi_selection		: false,
			upload_later		: false,
			container			: this.uploaderID + '_container',
			browse_button		: this.uploaderID + '_browse_button',
			thumbnail			: this.uploaderID + '_thumbnail',
			thumbsize			: 'thumbnail',
			file_data_name		: this.uploaderID,
			max_file_size 		: '1mb',
			//chunk_size 			: '1mb',
			// this filters is an array so if we declare it when init Uploader View, this filters will be replaced instead of extend
			filters				: [
				{ title : 'Image Files', extensions : 'jpg,jpeg,gif,png' }
			],
			multipart_params	: {
				fileID		: this.uploaderID
			},
			init : {
	            Error: function(up , err) {
	            	var o = this;
		        	var message, details = "";
		            	message = '<strong>' + err.message + '</strong>';

		            switch (err.code) {
		                case plupload.FILE_EXTENSION_ERROR:
		                    details = et_globals.plupload_config.msg.FILE_EXTENSION_ERROR.replace("%s",o.settings.filters.mime_types[0].extensions);
		                    break;

		                case plupload.FILE_SIZE_ERROR:
		                    details = et_globals.plupload_config.msg.FILE_SIZE_ERROR.replace( "%s", o.settings.max_file_size );
		                    break;

		                case plupload.FILE_DUPLICATE_ERROR:
		                    details = et_globals.plupload_config.msg.FILE_DUPLICATE_ERROR;
		                    break;

		                case self.FILE_COUNT_ERROR:
		                    details = et_globals.plupload_config.msg.FILE_COUNT_ERROR;
		                    break;

		                case plupload.IMAGE_FORMAT_ERROR :
		                   details = et_globals.plupload_config.msg.IMAGE_FORMAT_ERROR;
		                    break;

		                case plupload.IMAGE_MEMORY_ERROR :
		                    details = et_globals.plupload_config.msg.IMAGE_MEMORY_ERROR;
		                    break;

		                /* // This needs a review
		                case plupload.IMAGE_DIMENSIONS_ERROR :
		                    details = o.sprintf(_('Resoultion out of boundaries! <b>%s</b> runtime supports images only up to %wx%hpx.'), up.runtime, up.features.maxWidth, up.features.maxHeight);
		                    break;  */

		                case plupload.HTTP_ERROR:
		                    details = et_globals.plupload_config.msg.HTTP_ERROR;
		                    break;
		            }
		            alert(details);
	            }
	        }
		};

		jQuery.extend( true, this.config, et_globals.plupload_config, this.options );

		this.controller	= new plupload.Uploader( this.config );
		this.controller.init();

		this.controller.bind( 'FileUploaded', this.onFileUploaded );
		this.controller.bind( 'FilesAdded', this.onFileAdded );
		this.controller.bind( 'BeforeUpload', this.onFilesBeforeSend );
		this.bind( 'UploadSuccessfully', this.onUploadComplete );

		if( typeof this.controller.settings.onProgress === 'function' ){
			this.controller.bind( 'UploadProgress', this.controller.settings.onProgress );
		}
		if( typeof this.controller.settings.onError === 'function' ){
			this.controller.bind( 'Error', this.controller.settings.onError );
		}
		if( typeof this.controller.settings.cbRemoved === 'function' ){
			this.controller.bind( 'FilesRemoved', this.controller.settings.cbRemoved );
		}

	},

	onFileAdded	: function(up, files){
		if( typeof this.controller.settings.cbAdded === 'function' ){
			this.controller.settings.cbAdded(up,files);
		}
		if(!this.controller.settings.upload_later){
			up.refresh();
			up.start();
		}
	},

	onFileUploaded	: function(up, file, res){
		res	= $.parseJSON(res.response);
		if( typeof this.controller.settings.cbUploaded === 'function' ){
			this.controller.settings.cbUploaded(up,file,res);
		}
		if (res.success){
			this.updateThumbnail(res.data);
			this.trigger('UploadSuccessfully', res);
		}
	},

	updateThumbnail	: function(res){
		var that		= this,
			$thumb_div	= this.$('#' + this.controller.settings['thumbnail']),
			$existing_imgs, thumbsize;

		if ($thumb_div.length>0){

			$existing_imgs	= $thumb_div.find('img'),
			thumbsize	= this.controller.settings['thumbsize'];


			if ($existing_imgs.length > 0){
				$existing_imgs.fadeOut(100, function(){
					$existing_imgs.remove();
					if( _.isArray(res[thumbsize]) ){
						that.insertThumb( res[thumbsize][0], $thumb_div );
					}
				});
			}
			else if( typeof res !='undefined' && _.isArray(res[thumbsize]) ){
				this.insertThumb( res[thumbsize][0], $thumb_div );
			}
		}
	},

	insertThumb	: function(src,target){
		jQuery('<img>').attr({
				'id'	: this.uploaderID + '_thumb',
				'src'	: src
			})
			// .hide()
			.appendTo(target)
			.fadeIn(300);
	},

	updateConfig	: function(options){
		if ('updateThumbnail' in options && 'data' in options ){
			this.updateThumbnail(options.data);
		}
		$.extend( true, this.controller.settings, options );
		this.controller.refresh();
	},

	onFilesBeforeSend : function(){
		if('beforeSend' in this.options && typeof this.options.beforeSend === 'function'){
			this.options.beforeSend(this.$el);
		}
	},
	onUploadComplete : function(res){
		if('success' in this.options && typeof this.options.success === 'function'){
			this.options.success(res);
		}
	}

});

// View: Modal Box
JobEngine.Views.Modal_Box   = Backbone.View.extend({
	defaults    : {
		top         : 100,
		overlay     : 0.5
	},
	$overlay    : null,

	initialize  : function(){
		// bind all functions of this object to itself
		_.bindAll(this,'openModal','closeModal');
		// update custom options if having any
		this.options  = jQuery.extend(this.defaults,this.options);
		// get the overlay to blur the screen	
		this.$overlay   = $('#lean_overlay');
		// if it is not already there, add it to the body
		if (this.$overlay.length <= 0){
			this.$overlay    = $("<div id='lean_overlay'></div>").appendTo("body");
		}
		// when click on the overlay & close button, close the modal
		this.$overlay.click(this.closeModal);
	},

	openModal   : function(){
		var view = this;
		this.$overlay.css({"display":"block",opacity:0})
			.fadeTo(200,this.options.overlay);
		this.$el
			.css({
				"display"       : "block",
				"position"      : "absolute",
				"opacity"       : 0,
				"z-index"       : 11000,
				"left"          : 50+"%",
				"margin-left"   : -(this.$el.outerWidth()/2)+"px",
				"top"           : $(window).scrollTop() + this.options.top+"px",
				"width"			: this.options.width,
				"height"		: this.options.height
			})
			.fadeTo(200,1, function(){
				view.$el.find('input[type=text]:first').focus();
			});
	},

	closeModal   : function(time, callback){
		var modal = this;
		time = time || 200,
		modal.$overlay.fadeOut(200, function(){
			modal.$el.hide();
			if (typeof callback === 'function'){
				callback();
			}
		});
		return false;
	}
});

// Modal Reject Job
JobEngine.Views.ModalReject = JobEngine.Views.Modal_Box.extend({
	el : '#modal-reject-job',

	events : {
		'click div.modal-close' : 'closeModal',
		'click a.cancel-modal'	: 'closeModal',
		'click #btn-reject'		: 'reject'
	},

	initialize : function(){
		JobEngine.Views.Modal_Box.prototype.initialize.apply(this, arguments );

		this.options = _.extend( this.options, this.defaults );

		pubsub.on('je:job:afterRejectJob', this.afterRejectJob, this);
		pubsub.on('je:resume:afterRejectResume', this.afterRejectJob, this);
		this.loadingBtn = new JobEngine.Views.LoadingButton({el : this.$('input#btn-reject')});		

	},
	onReject : function(args){
		this.model = args.model;
		// render title of modal reject
		this.$el
			.find('#job_title').html(this.model.get('title'))
			.end()
			.find('#company_name').html(this.model.get('author'));
		this.openModal();
	},

	reject: function(event){
		event.preventDefault();
		this.loadingBtn.loading();

		// get reason why job is rejected
		this.message	= this.$el.find('textarea[name=reason]').val();
		this.refund		= this.$el.find('input[name=refund]').val();

		// send reject request via AJAX
		var view = this;
		this.model.reject({
			data : {
				reason	: view.message,
				refund	: view.refund
			},
			silent:true
		});
	},

	afterRejectJob : function(){
		this.loadingBtn.finish();
		this.closeModal();
	}
});

JobEngine.Views.LoadingEffect = Backbone.View.extend({
	initialize : function(){},
	render : function(){
		this.$el.html(et_views.loadingImg);
		return this;
	},
	finish : function(){
		this.$el.html(et_views.loadingFinish);
		var view = this;
		setTimeout(function(){
			view.$el.fadeOut(500, function(){ $(this).remove(); });
		}, 1000);
	},
	remove : function(){
		view.$el.remove();
	}
});

JobEngine.Views.BlockUi = Backbone.View.extend({
	defaults : {
		image : et_globals.imgURL + '/loading.gif',
		opacity : '0.5',
		background_position : 'center center',
		background_color : '#ffffff'
	},

	isLoading : false,

	initialize : function(options){
		//var defaults = _.clone(this.defaults);
		options = _.extend( _.clone(this.defaults), options );

		var loadingImg = options.image;
		this.overlay = $('<div class="loading-blur loading"><div class="loading-overlay"></div><div class="loading-img"></div></div>');
		this.overlay.find('.loading-img').css({
			'background-image' : 'url(' + options.image + ')',
			'background-position' : options.background_position
			});

		this.overlay.find('.loading-overlay').css({
			'opacity'			: options.opacity,
			'filter'			: 'alpha(opacity=' + options.opacity*100 + ')',
			'background-color'	: options.background_color
			});
		this.$el.html( this.overlay );

		this.isLoading = false;
	},

	render : function(){
		this.$el.html( this.overlay );
		return this;
	},

	block: function(element){
		var $ele = $(element);
		// if ( $ele.css('position') !== 'absolute' || $ele.css('position') !== 'relative'){
		// 	$ele.css('position', 'relative');
		// }
		this.overlay.css({
			'position' 	: 'absolute',
			'top' 		: $ele.offset().top,
			'left' 		: $ele.offset().left,
			'width' 	: $ele.outerWidth(),
			'height' 	: $ele.outerHeight()
		});

		this.isLoading = true;

		this.render().$el.show().appendTo( $('body') );
	},

	unblock: function(){
		this.$el.remove();
		this.isLoading = false;
	},

	finish : function(){
		this.$el.fadeOut(500, function(){ $(this).remove();});
		this.isLoading = false;
	}
});

JobEngine.Views.LoadingButton = Backbone.View.extend({
	dotCount : 3,
	isLoading : false,
	initialize : function(){
		if ( this.$el.length <= 0 ) return false;
		var dom = this.$el[0];
		//if ( this.$el[0].tagName != 'BUTTON' && (this.$el[0].tagName != 'INPUT') ) return false;

		if ( this.$el[0].tagName == 'INPUT' ){
			this.title = this.$el.val();
		}else {
			this.title = this.$el.html();
		}

		this.isLoading = false;
	},
	loopFunc : function(view){
		var dots = '';
		for(i = 0; i < view.dotCount; i++)
			dots = dots + '.';
		view.dotCount = (view.dotCount + 1) % 3;
		view.setTitle(et_globals.loading + dots);
	},
	setTitle: function(title){
		if ( this.$el[0].tagName === 'INPUT' ){
			this.$el.val( title );
		}else {
			this.$el.html( title );
		}
	},
	loading : function(){
		//if ( this.$el[0].tagName != 'BUTTON' && this.$el[0].tagName != 'A' && (this.$el[0].tagName != 'INPUT') ) return false;
		this.setTitle(et_globals.loading);
		
		this.$el.addClass('disabled');
		var view		= this;

		view.isLoading	= true;
		view.dots		= '...';
		view.setTitle(et_globals.loading + view.dots);

		this.loop = setInterval(function(){
			if ( view.dots === '...' ) view.dots = '';
			else if ( view.dots === '..' ) view.dots = '...';
			else if ( view.dots === '.' ) view.dots = '..';
			else view.dots = '.';
			view.setTitle(et_globals.loading + view.dots);
		}, 500);
	},
	finish : function(){
		var dom		= this.$el[0];
		this.isLoading	= false;
		clearInterval(this.loop);
		this.setTitle(this.title);
		this.$el.removeClass('disabled');
	}
});

JobEngine.TaxFactory = (function () {

    // Storage for our vehicle types
    var types 		= {};
    var tax_items	= {};
    return {
		getTaxModel: function ( type, data ) {
            var Tax = types[type];

            return (Tax ? new Tax(data) : null);
        },

        registerTaxModel: function ( type, Tax ) {
           // var proto = Tax.prototype;

            // only register classes that fulfill the Tax contract
          //  if ( proto.drive && proto.breakDown ) {
                types[type] = Tax;
           // }

            return JobEngine.TaxFactory;
        },

        getTaxItem: function ( type, data ) {
            var TaxItem = tax_items[type];

            return (TaxItem ? new TaxItem(data) : null);
        },

        registerTaxItem: function ( type, TaxItem ) {
           // var proto = TaxItem.prototype;

            // only register classes that fulfill the TaxItem contract
           // if ( proto.drive && proto.breakDown ) {
                tax_items[type] = TaxItem;
           // }

            return JobEngine.TaxFactory;
        }
    };
})();

/**
 * Model Job Tax
 */
JobEngine.Models.Tax = Backbone.Model.extend({
	initialize	: function(){
		_.templateSettings = {
		    evaluate    : /<#([\s\S]+?)#>/g,
			interpolate : /\{\{(.+?)\}\}/g,
			escape      : /<%-([\s\S]+?)%>/g
		};
		this.action	=	'';
	},
	parse		: function(resp){
		if ( resp.data )
			return resp.data;
	},
	remove: function(options){
		var params		= _.extend(ajaxParams, options);

		var action	= this.action;

		params.data = _.extend( params.data, this.toJSON() );

		params.data = jQuery.param( {method : 'delete', action : action, content : params.data });

		return jQuery.ajax(params);
	},

	sync		: function(method, model, options) {
		var params		= _.extend(ajaxParams, options);

		var action	= this.action;

		params.data	= model.toJSON();
		//params.method	= method;
		params.data = jQuery.param( {method : method, action : action, content : params.data });

		return jQuery.ajax(params);
	}
});

// ===============================
// Backend Tax Item View
// ===============================
JobEngine.Views.TaxItem = Backbone.View.extend({
	tagName : 'li',
	events : {
		'click a.act-del'							: 'displayReplaceList',
		'click a.act-open-form'						: 'openForm',
		'submit .form-sub-tax' 						: 'addSubTax',
		'click .form-sub-tax a.act-add-sub' 		: 'addSubTax',
		'keyup .form-sub-tax a.act-add-sub' 		: 'keyupSubTax',
		'change input.tax-name'						: 'updateName',
		'keyup .new-tax'							: 'cancelAddition'
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
							<form action="" class="" data-tax="'+this.tax_name+'">\
								<input type="hidden" name="parent" value="{{id}}">\
								<input class="bg-grey-input new-tax" name="name" type="text" placeholder="Enter category name"> \
							</form> \
						</div> \
					</div>\
				</li>'),

	initialize: function(){	
		_.templateSettings = {
		    evaluate    : /<#([\s\S]+?)#>/g,
			interpolate : /\{\{(.+?)\}\}/g,
			escape      : /<%-([\s\S]+?)%>/g
		};
	},

	render : function(){
		this.$el.append( this.template(this.model.toJSON()) ).addClass('tax-item').attr('id', 'tax_' + this.model.get('id'));
		return this;
	},

	openForm : function(event){
		var view = this;
		var id = $(event.currentTarget).attr('rel');

		if ( this.model.get('id') == id ){
			$html = this.sub_template({id : id});
			if (view.$el.find('ul').length == 0)
				view.$el.append('<ul>');
			view.$el.children('ul').append($html);
			view.$el.children('ul').find('.new-tax').focus();
		}
	},

	keyupSubTax: function(event){
		event.preventDefault();
		if (keyup.which == 13)
			this.addSubTax(event);
		return false;
	},

	addSubTax : function(event){
		event.stopPropagation();
		event.preventDefault();
		var view = this;
		var formContainer = view.$el.children('ul').children('li.form-sub-tax');
		var form = formContainer.find('form'),
			loadingView = new JobEngine.Views.LoadingEffect();
		
		if (form.find('input[name=name]').val() == '') return false;
		/**
		 * use factory to create tax model
		*/
		var model	=	JobEngine.TaxFactory.getTaxModel (form.attr('data-tax'), {
			parent	: form.find('input[name=parent]').val(),
			name	: form.find('input[name=name]').val()
		});

		model.save(model.toJSON(), {
			beforeSend : function(){
				loadingView.render().$el.appendTo( formContainer.find('.controls') );
			},
			success: function(model, resp){
				if (resp.success){
					loadingView.finish();
					/**
					 * use factory to create object tax item
					*/
					var subView = JobEngine.TaxFactory.getTaxItem(form.attr('data-tax'),{model: model});
					/**
					 * render tax item view
					*/
					$(subView.render().el).insertBefore(view.$el.children('ul').find('li:last'));
					formContainer.remove();
				}
		}});
	},
	/**
	 * update tax name
	*/
	updateName: function(event){
		var current = $(event.currentTarget),
			id		= current.attr('rel'),
			val		= current.val(),
			loadingView = new JobEngine.Views.LoadingEffect(),
			view = this;

		if ( id == this.model.get('id') ){
			this.model.set('name', val);
			this.model.save(this.model.toJSON(), {
				beforeSend : function(){
					loadingView.render().$el.appendTo( view.$el.children('.container').find('.controls') );
				},
				success: function(model, resp){
					loadingView.finish();
				}
			});
		}
	},

	displayReplaceList: function(event){
		event.stopPropagation();
		var $html 		= $($('#'+this.confirm_html).html()),
			container  	= this.$el.children('.container'),
			view 		= this;
		var tax_list	= this.$el.parents('ul.list-tax').find('li');
		$html.find('select').html ('');
		_.each(tax_list, function (element, index) {
			$html.find('select').append('<option value="' + $(element).find('input').attr('rel')+ '" >'+ $(element).find('input').val() +'</option>');
		});

		if (this.$el.find('ul > li').length > 0){
			alert(et_setting.del_parent_cat_msg);
			return false;
		}
		
		// hide the container 
		container.fadeOut('normal', function(){			
			$html.insertAfter(container).hide().fadeIn('normal', function(){
				$html.find('button.accept-btn').bind('click', function(event){
					var def = $html.find('select').val();
					view.deleteTax(def);
				});
				$html.find('a.cancel-del').bind('click', function(event){
					$html.fadeOut('normal', function(){
						container.fadeIn();
					});
				});
				$html.bind('keyup', function(event){
					if (event.which == 27)
						$html.fadeOut('normal', function(){
							container.fadeIn();
						});
				});
			});
			// apply styling
			$html.find('option[value=' + view.model.get('id') + ']').remove();
			view.styleSelect();
		});
	},
	// perform delete action
	deleteTax : function(def){
		var view = this,
		blockUi = new JobEngine.Views.BlockUi(),
		loadingView = new JobEngine.Views.LoadingEffect();		
		this.model.remove({
			data : {
				default_cat : def
			},
			beforeSend : function(){
				blockUi.block(view.$el.find('.moved-tax'));
			},
			success : function(data){
				blockUi.unblock();
				//loadingView.finish();
				if ( data.success )
					view.$el.fadeOut('normal', function(){ $(this).remove(); });
			}
		});
	},

	cancelAddition : function(event){
		if (event.keyCode == 27) {
			this.closeForm();
		}
	},

	closeForm : function(event){
		this.$el.children('ul').children('li.form-sub-tax').remove();
		if (this.$el.children('ul').find('li').length == 0)
			this.$el.children('ul').remove();
	},

	styleSelect : function(){
		this.$(".select-style select").each(function(){
			//var title = $(this).attr('title') || $(this).html;
			var title = $(this).find('option:selected').html();
			var arrow = "";
			if ($(".select-style select").attr('arrow') !== undefined) 
				arrow = " " + $(".select-style select").attr('arrow');

			if( $('option:selected', this).val() != ''  ) title = $('option:selected',this).text() + arrow ;
			$(this)
				.css({'z-index':10,'opacity':0,'-khtml-appearance':'none'})
				.after('<span class="select">' + title + arrow + '</span>')
				.change(function(){
					val = $('option:selected',this).text() + arrow;
					$(this).next().text(val);
				})
		});
	}
});

JobEngine.Views.BackendTax = Backbone.View.extend({
	events: {
		'submit form.new_tax' 				: 'addTax',
		'click form.new_tax .button' 		: 'addTax',
		'click .input-form .bar-flag div'   : 'triggerChangeColor'
	},
	
	initialize: function(){
		this.initTax ();
		this.initView ();
	},	

	initView : function () {
		// this function should be override
		return false;
	},

	initTax : function () {
		// this function should be override by children classs
		return false;
	},

	sortTax: function(event, ui, action){
		var id = $(ui.item).attr('id').replace(/\D/g, ''),
			parent_id = $(ui.item).parents('li').length > 0 ? $(ui.item).parents('li').attr('id').replace(/\D/g, '') : '0' ,
			order = this.$el.find('.tax-sortable').nestedSortable('toArray', {startDepthCount: 0}) ,
			view = this;
		var block	=	new JobEngine.Views.BlockUi();
		var params = {
			url : et_globals.ajaxURL,
			type : 'post',
			data : {
				action : action,
				content : {
					order:JSON.stringify(order),
					id : id,
					parent : parent_id,
					json	: true
				}
			},
			beforeSend: function(){
				block.block( view.$el.find('.list-tax') ) ;
			},
			success : function(resp){
				block.unblock();
			}
		};
	
		$.ajax(params);			
	},

	addTax: function (event){
		event.preventDefault();
		var form 	= this.$el.find('form.new_tax'),
			view 	= this,
			loading = new JobEngine.Views.LoadingEffect(),
			container	=	view.$el.find('.list-tax');
		// prevent user add category too many times
		if (form.hasClass('disabled') || form.find('input[type=text]').val() == ''){
			return false;
		}
		
		// var model = new JobEngine.Models.Tax({
		// 	name : form.find('input[type=text]').val()
		// });
		
		var model =	JobEngine.TaxFactory.getTaxModel(form.attr('data-tax'), {
			color : form.find('div.cursor').attr('data') ? form.find('div.cursor').attr('data') : 0,
			name : form.find('input[type=text]').val()
		});

		model.save( model.toJSON(), {
			beforeSend : function(){
				form.addClass('disabled');
				loading.render().$el.appendTo( form.find('.controls') );
			},
			success : function( model, resp){
				form.removeClass('disabled');
				loading.finish();
				//adding to list
				var view =  JobEngine.TaxFactory.getTaxItem(form.attr('data-tax'),{model: model});				
				$(view.render().el).hide().appendTo( container ).fadeIn();
				form.find('input[type=text]').val('');
			}
		} );
	},

	submitForm : function(event){
		var form = $(event.target).parents('form');
		form.submit();
	},

	triggerChangeColor : function (event) {
		var target		=	jQuery(event.currentTarget),
			color 		=  	jQuery(event.currentTarget).attr("class"),
			color 		=  	color.replace(" active",""),
			appView		=	this;
		var code 		= target.attr('data');
		var action 		= target.parents('.input-form').attr('data-action');

		target.parent().parent().find(".cursor").removeAttr('class').addClass("cursor").addClass(color).attr('data', code);
		target.parent().parent().find("input").removeAttr('class').addClass("bg-grey-input").addClass(color);
		
		target.parent().remove();

		// send color data via ajax
		if ( $('.current-job-type').length > 0 ){
			var term_id 	= $('.current-job-type').attr('data');
			appView.changeJobTypeColor(term_id, code, action );
		}
	},

	changeJobTypeColor : function(term_id, code, action ){
		if(action == 'undefined') return;
		var params 	= ajaxParams,
			view	=	this;
		
		var block	=	new JobEngine.Views.BlockUi();
		params.data = {
			action  : action,
			content : {
				term_id : term_id,
				color : code
			}
		};

		params.beforeSend = function(){ block.block( view.$el.find('.list-tax') ) ; }
		params.success = function(resp){
			block.unblock();
		}

		$.ajax(params);
	}
});

/*************************************************
//                                              //
//              HELPER FUNCTIONS                //
//                                              //
*************************************************/

jQuery.fn.buttonLoading = function(){
	$(this).html( et_globals.loading );
};

})(jQuery);
