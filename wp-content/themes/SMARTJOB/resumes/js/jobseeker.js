(function ($) {
JobEngine.Models.User = Backbone.Model.extend({ 
	defaults    : {
		display_name    : '',
		user_url		: '',
		post_url		: '',
		recent_location : ''
	},

	params	: {
		type		: 'POST',
		dataType	: 'json',
		url			: et_globals.ajaxURL,
		contentType	: 'application/x-www-form-urlencoded;charset=UTF-8'
	},

	action  : 'et_user_sync',
	role	: 'subcriber',

	initialize	: function(){
		//_.bindAll(this);

	},

	setDisplayName	: function(value){
		this.set({display_name: value},{silent:true});
	},

	getDisplayName	: function(){
		return this.get('display_name');
	},

	setUrl	: function(value){
		this.set({user_url: value},{silent:true});
	},

	getUrl	: function(){
		return this.get('user_url');
	},

	setUserName	: function ( value ) {
		this.set({ user_name : value }, {silent: true});
	},

	getUserName : function () {
		return this.get('user_name') ;
	},

	setEmail    : function( value ){
		this.set({ user_email : value }, {silent: true});
	},

	getEmail  : function () {
		return this.get('user_email');
	},

	setPass : function(value){
		this.set({user_pass : value}, {silent: true});
	},

	setUserKey : function(value){
		this.set({user_key : value}, {silent : true});
	},

	get_logo : function () {
		return this.get('user_logo');
	},

	validate : function(attrs, options) {

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

		var params,
			model	=	this;
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
			data : _.extend({
				action      : type,
				user_email  : model.get('user_email'),
				user_pass   : model.get('user_pass'),
				user_name   : model.get('user_name'),
				role		: model.role
			},model.toJSON() )
		},model.params , options || {});

		// use in posting job page, when a user logs out & login using another account, we need to request another nonce
		if(options && 'renew_logo_nonce' in options && !!options.renew_logo_nonce){
			params.data.renew_logo_nonce = true;
		}
		if(options && 'redirect_url' in options && options.redirect_url != ''){
			params.data.redirect_url = options.redirect_url;
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
			pubsub.trigger('je:response:auth_'+type, data, status, jqXHR);
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
	},

	renderListItem : function(){
		return this.itemTemplate(this.toJSON);
	},

	sync : function (method, model, options) {
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

// Model: Jobseeker
JobEngine.Models.JobSeeker  = JobEngine.Models.User.extend({
	defaults    : {
		display_name    		: '',
		et_location 			: '',
		description				: '',
		et_profession_title		: '',
		et_avatar				: '',
		et_linkleInUrl			: '',
		et_accessible_companies : '',
		et_privacy              : ''
	},

	action  : 'et_jobseeker_sync',
	role 	: 'jobseeker',

	initialize	: function(){
		//_.bindAll(this);
		JobEngine.Models.User.prototype.initialize.call();
	},
	setLinkleInProfileUrl : function (value) {
		this.set ({et_linkleInUrl : value}, {silent: true});
	},
	setAvatar : function (value) {
		this.set ({et_avatar : value}, {silent: true});
	},
	getProTitle : function ()  {
		return this.get('et_profession_title');
	},
	setProTitle : function (value)  {
		this.set ({et_profession_title : value}, {silent: true});
	},

	getBio : function () {
		return this.get('description');
	},

	setBio : function (value) {
		this.set({description: value},{silent:true});
	},
	getLocation : function () {
		return this.get('et_location');
	},

	setLocation : function (value) {
		this.set({et_location: value},{silent:true});
	},

	parse	: function(res){
		if(!res.success){
			// pubsub.trigger('je:error:user_sync',res.msg);
			pubsub.trigger('je:notification',{
				msg	: res.msg,
				notice_type	: 'error'
			});
			return {};
		}
		else {
			return res.data;
		}
	},

	renderListItem : function(){
		return this.itemTemplate(this.toJSON);
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
			attrs	= _.clone(model.attributes);
			if (options && options.saveData && method !== 'create') {
				attrs	=	{};
				_.each(options.saveData, function(element, index){
					// render education html
					attrs[element]	=	model.attributes[element];

				});
				attrs['ID']	=	model.attributes['ID'];
				attrs['id']	=	model.attributes['id'];
				params.data	=	attrs;
			}else {
				params.data     = model.toJSON();
			}

			params.action   = model.action;

		}

		if (params.type !== 'GET') {
			params.processData = false;
		}

		params.data = jQuery.param({action:params.action,method:method,content:params.data});

		// Make the request.
		return jQuery.ajax(params);
	}
});

$(document).ready(function() {
	if( typeof JobEngine.app.currentUser.get('ID') == 'undefined' && $('.single-resume').length > 0  && et_resume.resumes_privacy ) {
		JobEngine.Views.PrivacyControl = Backbone.View.extend ({
			initialize : function () {
					JobEngine.app.header.modal_login.setOptions  ({ redirect_url : this.redirect_url }  );
					JobEngine.app.header.modal_login.openModalAuth();
					return ;
			}

		});
		new JobEngine.Views.PrivacyControl ();
	}
});
// var user 		=	new JobEngine.Models.User ({id : 1});
// var jobseeker	=	new JobEngine.Models.JobSeeker ({id : 1});
}) (jQuery);