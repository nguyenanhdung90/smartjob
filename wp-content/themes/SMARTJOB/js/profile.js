(function ($) {

JobEngine.Views.CompanyProfile = Backbone.View.extend({
	el : $('#page_company_profile'),

	events : {
		'click #submit_profile' 		: 'updateProfile',
		'keyup .form-account textarea'	: 'limitCharacter',
		'blur .form-account textarea'	: 'limitCharacter',
		'change .form-account textarea' : 'characterCount',
		'change #user_url'				: 'autoCompleteUrl'
	},

	initialize : function( attr, arguments){
		
		this.constructor.__super__.initialize.call(this);	
		
		var that		= this,
			$user_logo	= this.$('#user_logo_container');

		this.$form	= this.$('form#profile');

		this.model	= new JobEngine.Models.Company( JSON.parse( this.$('#profile_data').html() ) );

		var blockUi = new JobEngine.Views.BlockUi();
		this.logo_uploader	= new JobEngine.Views.File_Uploader({
			el					: $user_logo,
			uploaderID			: 'user_logo',
			thumbsize			: 'thumbnail',
			multipart_params	: {
				_ajax_nonce	: $user_logo.find('.et_ajaxnonce').attr('id'),
				action		: 'et_logo_upload',
				author		: this.model.get('id')
			},
			cbUploaded		: function(up,file,res){
				if(res.success){
					that.model.set('user_logo',res.data,{silent:true});
				}
				pubsub.trigger('je:notification',{
					msg	: res.msg,
					notice_type	: (res.success) ? 'success' : 'error'
				});
			},
			beforeSend			: function(element){
				blockUi.block($user_logo.find('.company-thumbs'));
			},
			success : function(){
				blockUi.unblock();
			}
		});
		var job_require_fields  = et_globals.job_require_fields,
            required_user_url   = $.inArray('user_url',job_require_fields) == -1 ? false : true;
		this.validator	= this.$form.validate({
			ignore: "select, .plupload input",
			rules: {
				display_name	: "required",
				user_email		: {
					required	: true,
					email		: true
				},
				user_url		: {
					required	: required_user_url,
					url			: true
				}
			}
		});
		document.onkeydown = this.keyCheck;  //or however you are calling your method
		//this.characterCountdown (('.form-account textarea'));
		var target	=	$('.form-account textarea');
		this.characterCountdown (target);

	},

	keyCheck : function KeyCheck(event) {

	   	var KeyID = event.keyCode;
	   	switch(KeyID) {
			case 8:
			case 46:
				var target	=	$('.form-account textarea');
				if((499-target.val().length) == 1) {
					$('#chacracter').parent ('label') .html ($('#1_char').val());
				} else
				if((499-target.val().length) <= 0) {
					$('#chacracter').parent ('label') .html ($('#0_char').val());
				} else {
					$('#chacracter').parent ('label').html ($('#n_char').val());

					$('#chacracter').html (499 - target.val().length);
				}
				break;
			default:
				break;
	  	}
	} ,

	limitCharacter : function (event) {
		var target	=	$(event.currentTarget);
		var str		=	target.val();
		if(str.length > 499 ) {
			target.val (str.substring(0,499));
			return false;
		}
		this.characterCountdown (target);
	},
	characterCount : function (event) {
		var target	=	$(event.currentTarget);
		this.characterCountdown (target);
	},

	characterCountdown : function (target) {
		if((499-target.val().length) == 1) {
			$('#chacracter').parent ('label') .html ($('#1_char').val());
		} else
		if((499-target.val().length) == 0) {
			$('#chacracter').parent ('label') .html ($('#0_char').val());
		} else {
			$('#chacracter').parent ('label').html ($('#n_char').val());
			$('#chacracter').html (499 - target.val().length);
		}

	},

	updateProfile : function(event){
		event.preventDefault();
		var	companyData = {},
			fields		= ['display_name','user_email','user_url', 'description'],
			$form		= this.$form,
			i;
		if ( this.validator.form() ){

			$form.find('textarea,input,select').each(function () {
				var $field	= $(this);
				companyData[$field.attr('id')]	= $field.val();
			});
			// companyData['role']	=	'company';
			this.model.save( companyData, { success : function(model, resp){
				pubsub.trigger('je:notification', {
					msg : resp.msg,
					notice_type : resp.success ? "success" : 'error'
				});
			}});
		}
	},

	autoCompleteUrl : function (event) {
		var val		=	$(event.currentTarget).val();
		if (val.length == 0) { return true; }

	    // if user has not entered http:// https:// or ftp:// assume they mean http://
	    if(!/^(https?|ftp):\/\//i.test(val)) {
	        val = 'http://'+val; // set both the value
	        $(event.currentTarget).val(val); // also update the form element
	        $(event.currentTarget).focus();
	    }
	}

});
jQuery(document).ready(function($){

	JobEngine.CompanyProfile	=	new JobEngine.Views.CompanyProfile();

});
})(jQuery);