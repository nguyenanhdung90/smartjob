(function($){
JobEngine.Views.PageProfileManage = Backbone.View.extend({
	'el' 		: '#profile',
	'events' 	: {
		'click .btn-edit a' 				: 'onToggleInlineEdit',
		//'click .info-resume .btn-edit a' 	: 'onToggleInlineEditInfo',
		'click .inline-edit .close' 		: 'onToggleInlineEdit',
		'click a#approveResume'				: 'approveResume',
		'click a#rejectResume'				: 'rejectResume',
		'click .btn-republish'      		: 'republishResume',
		'submit #form_about' 				: 'saveAbout',
		'submit #form_education' 			: 'saveEducation',
		'submit #form_experience' 			: 'saveExperience',
		'submit #form_skills' 				: 'saveSkills',
		'submit #form_resume_categories' 	: 'saveJobPositions',
		'submit #form_available' 			: 'saveAvailable',
		'click #add_more_school'    		: 'addMoreSchool',
		'click #add_more_company' 			: 'addMoreCompany',
		'keypress #skill_input' 			: 'onAddSkill',
		'change #form_resume_categories .jobpos_select select' 		: 'addJobPosition'
	},

	initialize : function(){

		var resume  	= JSON.parse($('#data_resume').html());
		var jobseeker 	= JSON.parse($('#data_jobseeker').html());
		var view 		= this;
		this.loading 	= new JobEngine.Views.BlockUi();		
		resume.id 		= resume.ID;
		jobseeker.id 	= jobseeker.ID;
		this.resume 	= new JobEngine.Models.Resume(resume);
		this.jobseeker 	= new JobEngine.Models.JobSeeker(jobseeker);

		view.expViews = [];
		view.educationViews = [];
		pubsub.on('je:resume:afterRejectResume', this.afterRejectResume, this);

		/**
		 * Unload unused javascripts code when profile information doesn's show
		 */

		if ($('#resume_profile').length > 0){ 
			// initialize education views
			//view.educationViews = [];
			_.each(this.resume.get('et_education'), function(element, index, list){
				var $view = new JobEngine.Views.EducationView({
					data: element, 
					onDelete: function(instance){ view.onDeleteEducationItem(instance, view); }
				});

				view.educationViews.push($view);
				$('#inline_edu').append( $view.render().$el );
			});

			// initialize education views
			//view.expViews = [];
			_.each(this.resume.get('et_experience'), function(element, index, list){
				var $view = new JobEngine.Views.ExperienceView({
					data: element, 
					onDelete: function(instance){ view.onDeleteExpItem(instance, view); }
				});

				view.expViews.push($view);
				$('#inline_exp').append( $view.render().$el );
			});


			// autocomplete
			$('.skill-input').autocomplete({
				source: JSON.parse($('#data_skills').html()),
				select: function(event, ui){
					view.addSkill(ui.item.value);
					event.preventDefault();
				}, 
				change: function(event, ui){
					$('#skill_input').val('');
				} });

			// skills
			var skills = JSON.parse( $('#data_resume_skills').html() );

			view.skill_list = $('#form_skills > ul.skill-list');
			if(skills) {
				_.each(skills, function(element, index){
					var taxView = new JobEngine.Views.EditedTaxonomyItem(element, $('#edit_skill_item').html());
					view.skill_list.append( taxView.render().$el );
				});
			}

			// resume category
			var positions = JSON.parse( $('#data_resume_positions').html() );
			view.positions_list = $('#form_resume_categories ul.skill-list');
			if(positions) {
				_.each(positions, function(element, index){
					var taxView = new JobEngine.Views.EditedTaxonomyItem(element, $('#edit_position_item').html());
					view.positions_list.append( taxView.render().$el );
				});
			}
		}

		//uploader		
		$avatar = $('#avatar_container');
		if($avatar.length > 0 )
		this.avatar_uploader	= new JobEngine.Views.File_Uploader({
			el					: '#avatar_container',
			uploaderID			: 'avatar',
			thumbsize			: 'thumbnail',			

			multipart_params	: {
				// _ajax_nonce	: $user_logo.find('.et_ajaxnonce').attr('id'),
				action		: 'et_avatar_upload',
				author 		: $avatar.find('input[name=author]').val()
			},
			cbUploaded : function(up, file, res){
				view.loading.unblock();
				if (res.success){
					// this.updateThumbnail(res.data);
					// this.trigger('UploadSuccessfully', res);
				}else {
					pubsub.trigger('je:notification', {
						msg			: res.msg,
						notice_type	: 'error'
					});
				}
			},
			beforeSend		: function(element){
				view.loading.block($('#avatar_container').find('.thumb'));
			}
		});

		

		// for url validator
		patterns = [{
			'name' 		: 'l_url',
			'pattern' 	: /^(http\:\/\/|https\:\/\/)[a-zA-Z0-9-\.]*(linkedin)(\.+[a-z]{2,4})/,
			'message' 	: et_resume_profile.err_linked_url
		},{
			'name' 		: 't_url',
			'pattern' 	: /^(http\:\/\/|https\:\/\/)[a-zA-Z0-9-\.]*(twitter)(\.+[a-z]{2,4})/,
			'message' 	: et_resume_profile.err_twitter_url
		},{
			'name' 		: 'f_url',
			'pattern' 	: /^(http\:\/\/|https\:\/\/)[a-zA-Z0-9-\.]*(facebook)(\.+[a-z]{2,4})/,
			'message' 	: et_resume_profile.err_facebook_url
		},{
			'name' 		: 'g_url',
			'pattern' 	: /^(http\:\/\/|https\:\/\/)[a-zA-Z0-9-\.]*(google)(\.+[a-z]{2,4})/,
			'message' 	: et_resume_profile.err_google_url
		},{
			'name' 		: 'p_url',
			'pattern' 	: /^(http\:\/\/|https\:\/\/)[a-zA-Z0-9-^.]+(\.[a-z]{2,4})$/,
			'message' 	: et_resume_profile.err_personal_weburl
		}];

		_.each(patterns, function(item){
			var name = item.name;
			var pattern = item.pattern;
			$.validator.addMethod(name, function(value, element) {
				if ( /^(http:\/\/|https:\/\/)/.test($(element).val()) == false && value != ''){
					$(element).val( 'http://' + $(element).val() );
					value = $(element).val();
				}
				return value == '' || pattern.test(value);
			}, item.message);
		});
		
		// validator 
		var urlsValidator =  $('#urls_edit').validate({
			rules : {
				linkedin : 'l_url',
				twitter : 't_url'	,
				facebook : 'f_url',
				gplus : 'g_url',
				user_url : 'p_url'
			}
		});

		/***
		 * Jobseeker infomation editing
		 */

		view.infoViews = [];
		$(this.$el.find('.jobseeker-profile-widget .information .info')).each(function(){
			view.infoViews.push( new infoView({
				el : $(this),
				onSave : function(event, instance){
					var elements 	= this.$el.find('input[type=text]'),
						params 		= {},
						keys 		= [];

					_.each(elements, function(element, index){
						var name 		= $(element).attr('name'),
							value 		= $(element).val();
						keys.push(name);
						params[name] 	= value;
					});
					
					view.jobseeker.set( params, {silent: true});
					view.jobseeker.save( params, {
						saveData: keys,
						beforeSend: function(){
							view.loading.block($(instance.$el));
						},
						success: function( model, resp ){
							view.loading.unblock();
							_.each(elements, function(element, index){
								var name 		= $(element).attr('name');
								$('#info_' + name).html(params[name]);

							});

							// update title in profile page
							$('#info_display_name').html(resp.data.display_name);
							$('.heading .title').html (resp.data.profile_text);
							$('#breadcrums_name').html (resp.data.display_name);

							instance.toggleEdit();
						}
					});
				}
			} ) );
		});	

		$(this.$el.find('.jobseeker-profile-widget .social .info')).each(function(){
			$(this).find('input[type=text]').change(function(){
				
			});

			view.infoViews.push( new infoView({
				el : $(this),
				onSave : function(event, instance){
					if ( !urlsValidator.form() ) return false;

					var container 	= instance.$el,
						values 		= {},
						infoView  	= this;
					container.find('input[type=text]').each(function(){
						values[$(this).attr('name')] = $(this).val();
					});

					view.jobseeker.save(values,{
						saveData: ['twitter', 'linkedin', 'facebook', 'gplus', 'user_url'],
						beforeSend: function(){
							view.loading.block(container);
						},
						success: function(model, resp){
							view.loading.unblock();
							var containerUrl = container.find('#profile_urls')
							
							$.each(values, function(key, value){
								var item = containerUrl.find('#url_' + key);
								item.find('a').attr('href', value);
								if (value == ''){
									item.addClass('hide');
								}else {
									item.removeClass('hide');
								}
							});

							instance.toggleEdit();
						}
					});
				},
				renderDisplay : function(type, value){
					var map = {
						'twitter' 	: 'Twitter',
						'facebook' 	: 'Facebook',
						'user_url' 	: et_resume_profile.personal_website,
						'gplus' 	: et_resume_profile.google_plus,
						'linkedin' 	: 'Linkedin'
					};
					var html = '<div class="item view">' +
									'<a href="' + value + '">'+
										'<span class="sicon ' + type + '"></span>'+
										'<span class="name">' + map[type] + '</span>'+
									'</a>'+
								'</div>';
					return html;
				}
			} ) );
		});
	},
	
	approveResume : function (event) {
		var view = this;
		event.preventDefault();
		this.resume.approve({
			beforeSend: function(){
				view.loading.block(view.$el.find('.job-controls'));
			},
			success :function(model,res){
				view.loading.unblock();
				this.$('.message .main-center').html ('');
				
				this.$('.job-controls #approveResume').fadeOut();
				this.$('.job-controls #rejectResume').fadeIn(500);
			}
		});
	},
	republishResume :function(event){
		var view = this;
		event.preventDefault();
		this.resume.pending({
			beforeSend: function(){
				view.loading.block(view.$el.find('.main-center .text'));
			},
			success :function(model,res){
				view.loading.unblock();
				this.$('.message .main-center').html ('');
				
				this.$('.job-controls #approveResume').fadeOut();
				this.$('.job-controls #rejectResume').fadeIn(500);
			}
		});
	},
	rejectResume : function (event) {
		event.preventDefault()	;
		var rejectModalView = new JobEngine.Views.ModalReject();
		rejectModalView.onReject({model : this.resume });
	},

	afterRejectResume : function (model,res) {
		this.$('.message .main-center').html ('<div class="text">' +et_resume.reject_message + '</div>');
		this.$('.job-controls #rejectResume').fadeOut();
		this.$('.job-controls #approveResume').fadeIn(500);
	},

	onDeleteEducationItem : function(instance, view){
		_.each(view.educationViews, function(element, index){
			if (element == instance)
				view.educationViews.splice(index,1);
		});
	},

	onDeleteExpItem : function(instance, view){
		_.each(view.expViews, function(element, index){
			if (element == instance)
				view.expViews.splice(index,1);
		});
	},

	styleSelect : function(){

		this.$(".select-style select").each(function(){
			var title = $(this).find('option:selected').html();
			var arrow = "";
			var container = $(this).parent();

			container.children('span.select').remove();

			$(this)
				.css({'z-index':10,'opacity':0,'-khtml-appearance':'none'})
				.after('<span class="select">' + title + arrow + '</span>')
				.change(function(){
					val = $('option:selected',this).text() + arrow;
					$(this).next().text(val);
					});

			$(this).parent().addClass('styled');
		});
	},

	onToggleInlineEdit : function(event){
		var height	= parseInt($("#edu-content").css('height')) + 10,
			view	= this;	
		event.preventDefault();
		var element 	= $(event.currentTarget);
		var container 	= element.closest('.module');
		///$('.module').removeClass ('editing');
		$(container).toggleClass ('editing');
		
		$(".info-resume textarea").css('height',height);
		var module	=	$(event.currentTarget).attr ('data-module');
		if(module == 'work-exp' && view.expViews.length == 0) {
			var $view = new JobEngine.Views.ExperienceView ({
				data: {}, 
				onDelete: function(instance){ view.onDeleteExpItem(instance, view); } 
			});

			view.expViews.push($view);
			$('#inline_exp').append ($view.render().$el);			
		}
		
		if(module == 'school' && view.educationViews.length == 0) {
			var $view = new JobEngine.Views.EducationView ({
				data: {}, 
				onDelete: function(instance){ view.onDeleteEducationItem(instance, view); } 
			});

			view.educationViews.push($view);
			$('#inline_edu').append ($view.render().$el);

		}

		this.styleSelect();

	},

	onToggleInfoEdit : function(event){
		container = $(event.currentTarget).closest('.info');
		//$('.module').removeClass ('editing');
		$(container).toggleClass ('editing');
		
	},

	toggleInlineEdit: function(element){
		var container = $(element);

		if (!container.hasClass('editing'))
			container.addClass('editing');
		else {
			container.removeClass('editing');
		}
	},

	addMoreSchool : function(event){
		var element = $(event.currentTarget),
			parent  = element.parent(),
			view = this;

		// validate previous education view
		var validate = true;
		$.each(this.educationViews, function(index, item){
			if ( typeof item.validate == 'undefined' ) 
				return;

			if (!item.validate()) { 
				validate = false;
				return false;
			}
		});

		// escape if validate failed
		if (!validate) return false;
		// end validate

		// generate view for list
		var eduView = new JobEngine.Views.EducationView({data: {
			fromMonth : '', fromYear : '',
			toMonth: '', toYear : '',
			name: '', current : '', degree : '' , from : '' , to : '' 
		},	onDelete: function(instance){ view.onDeleteEducationItem(instance, view); } });
		// add new view into list
		this.educationViews.push(eduView);

		$('#inline_edu').append( eduView.render().$el );

		this.styleSelect();
	},

	addMoreCompany : function(event){
		var element = $(event.currentTarget),
			parent  = element.parent(),
			view 	= this

		// validate previous education view
		var validate = true;
		$.each(this.expViews, function(index, item){
			if ( typeof item.validate == 'undefined' ) 
				return;

			if (!item.validate()){
				validate = false;
				return false;
			}
		});

		// escape if validate failed
		if (!validate) return false;
		// end validate

		// generate new view 
		var expView = new JobEngine.Views.ExperienceView({data: {
				fromMonth : '', fromYear : '',
				toMonth: '', toYear : '',
				name: '', position: '', current : '' , from : '' , to : ''
			}, onDelete : function(instance){ view.onDeleteExpItem(instance, view); }
		});

		this.expViews.push(expView); // add view to list
		
		$('#inline_exp').append( expView.render().$el );
		// apply style select
		this.styleSelect();
	},

	autoAddFields: function(event){
		event.preventDefault();
		var container 			= $(event.currentTarget).closest('.auto-add');
		var unfilledElements 	= container.find('.auto-add-field').filter(function() { return $(this).val() == ""; });

		// automatically add new field when empty
		if (unfilledElements.length == 0){
			var newInput = $('<div class="jse-input"><span><input type="text" class="bg-default-input auto-add-field skill-input" value="" /></span></div>');
			container.append(newInput);
			$(newInput).find('input.skill-input').autocomplete({source: JSON.parse($('#data_skills').html()) });
		}
	},

	onAddSkill: function(event){
		var val 		= $(event.currentTarget).val();
		if ( event.which == 13 ){
			this.addSkill(val);
			$('#skill_input').val('');
		}

		return event.which != 13;
	},

	addSkill: function(skill){
		var duplicates 	= this.skill_list.find('input[type=hidden][value="' + skill + '"]');
		if ( duplicates.length == 0 && skill != '' ){
			var data = { 'name' : skill };
			var taxView = new JobEngine.Views.EditedTaxonomyItem(data, $('#edit_skill_item').html());
			this.skill_list.append( taxView.render().$el );	
			$('#skill_input').val('');
		}
	},

	addJobPosition: function(event){
		var val 		= $(event.currentTarget).val();
		var duplicates 	= this.positions_list.find('input[type=hidden][value="' + val + '"]');
		if (duplicates.length > 0){ alert(et_resume.duplicate_resume_category); };

		if ( duplicates.length == 0 ){
			var tempName = $(event.currentTarget).find('option:selected').text(); 
			var data = { 'term_id' : val, 'name' : $.trim(tempName) };
			var taxView = new JobEngine.Views.EditedTaxonomyItem(data, $('#edit_position_item').html());
			this.positions_list.append( taxView.render().$el );
		}
	},

	saveAbout: function(event){
		event.preventDefault();
		var view = this;
		var content = $( event.currentTarget ).find('textarea').val();
		var container = $( $( event.currentTarget ).closest('.module') );

		this.resume.set({'post_content' : content});
		this.resume.save( { 
				post_content : this.resume.get('post_content') 
			}, {
				saveData: ['post_content'],
				beforeSend: function(){
					view.loading.block(container);
				},
				success : function(model, resp){
					view.loading.unblock();
					if ( resp.success ){
						// apply change content
						view.resume.set( resp.data.resume, {silent: true});
						container.find('.cnt').html(view.resume.get('post_content_filtered'));
						view.toggleInlineEdit(container);
					}
				}
			});
	},

	saveEducation : function(event){
		event.preventDefault();
		var container = $($(event.currentTarget).closest('.module'));

		// get data
		var data = [];

		var validate = true;
		_.each(this.educationViews, function(element, index){
			var object 		= _.clone(element.toObject());
			if (!element.validate())
				validate = false;
			else {
				data.push(object);
			}
		});

		if (!validate) return false;

		// escape if validate failed
		//if (validate != true) return false;
		// end validate

		var contentContainer 	= container.find('.cnt');
		var contentHtml 		= '';
		var contentTemplate 	= _.template($('#template_edu_item').html());
		var view 				= this;

		this.resume.set('et_education', data);
		if(data.length <= 0 ) 
			var edu	=	'empty';
		else  
			var edu = data;

		this.resume.save({
			et_education : edu
		},{
			saveData : ['et_education'],
			beforeSend: function(){
				view.loading.block(container);
			},
			success:function(model, resp){
				// unloading effect
				view.loading.unblock();

				// render contents
				if (resp.success){
					$('#inline_edu').html('');
					if (resp.data.resume.et_education) {
						view.educationViews = [];
						_.each(resp.data.resume.et_education, function(element, index){
							//re create view
							var eduView = new JobEngine.Views.EducationView({data: element});
							view.educationViews.push(eduView);

							// render new view
							contentHtml += contentTemplate(element);
							$('#inline_edu').append( eduView.render().$el );
							view.styleSelect();
						});
					}
				}
				contentContainer.html(contentHtml);

				// toggle view
				view.toggleInlineEdit(container);
			}
		});

		return false;
	},

	saveExperience: function(event){
		event.preventDefault();

		var container 	= $($(event.currentTarget).closest('.module'));

		// get data
		var data 		= [];
		var validate 	= true;
		_.each(this.expViews, function(element, index){
			var object 		= _.clone(element.toObject());
			if (!element.validate())
				validate = false;
			else {
				data.push(object);
			}
		});

		if (!validate) return false;

		var contentContainer 	= container.find('.cnt');
		var contentHtml 		= '';
		var contentTemplate 	= _.template($('#template_exp_item').html());
		var view 				= this;

		// save

		if(data.length <= 0 ) 
			var exp	=	'empty';
		else  
			var exp = data;

		this.resume.set('et_experience', data);
		this.resume.save({
			et_experience : exp
		},{
			saveData: ['et_experience'],
			beforeSend: function(){
				view.loading.block(container);
			},
			success:function(model, resp){
				// unloading effect
				view.loading.unblock();

				// render contents
				if (resp.data.resume.et_experience)
					_.each(resp.data.resume.et_experience, function(element, index){
						contentHtml += contentTemplate(element);
					});
				contentContainer.html(contentHtml);

				// toggle view
				view.toggleInlineEdit(container);
			}
		});

		return false;
	},

	saveSkills: function(event){
		event.preventDefault();

		// collect skills
		var container 	= $($(event.currentTarget).closest('.module')),
			view 		= this,
			skills 		= $('#form_skills input[type=hidden]').filter(function(){ 
				return $(this).val() != ''; 
			}).map(function(){
				return $(this).val();
			}).get();

		if (skills.length == 0)
			skills = '';

		this.resume.set('skill', skills, {silent: true});
		this.resume.save({
			skill : skills
		},{
			saveData: ['skill'],
			beforeSend: function(){
				view.loading.block(container);
			},
			success:function(model, resp){
				// unloading effect
				view.loading.unblock();

				var content = container.find('.cnt');
				var html 	= '';

				$(resp.data.resume.skill).each(function(index){
					html += '<div class="item"><div class="content">'+ this.name +'</div></div>';
				});
				content.html(html);

				// toggle view
				view.toggleInlineEdit(container);
			}
		});
	},

	saveJobPositions : function(event){
		event.preventDefault();
		
		// collect resume category
		var container 	= $($(event.currentTarget).closest('.module')),
			view 		= this;

		//positions 	= unique(elements.get());
		var positions = $('#form_resume_categories input[type=hidden]').filter(function(){ 
			return $(this).val() != ''; 
		}).map(function(){
			return $(this).val();
		}).get();

		if (positions.length == 0)
			positions = '';
			
		this.resume.set('resume_category', positions, {silent: true});
		this.resume.save({
			resume_category : positions
		},{
			saveData : ['resume_category'],
			beforeSend: function(){
				view.loading.block(container);
			},
			success:function(model, resp){
				// unloading effect
				view.loading.unblock();

				var content = container.find('.cnt');
				var html 	= '';
				var data 	=  resp.data.resume.resume_category;
				$(data).each(function(index){
					html += '<div class="item"><div class="content">'+ this.name +'</div></div>';
				});
				content.html(html);

				// toggle view
				view.toggleInlineEdit(container);
			}
		});
	},

	saveAvailable: function(event){
		event.preventDefault();

		// collect available
		var $target		= $(event.currentTarget),
			container 	= $($target.closest('.module')),
			view 		= this;

		var availables = $target.find('input:checked').map(function(){ return $(this).val(); }).get();
		var color_avai = $target.find('input:checked').map(function(){ return { name: $(this).attr('data-name') , color : $(this).attr('data-color') }; }).get();

		if (availables.length == 0)
			availables = '';

		this.resume.set('available', availables, {silent: true});
		this.resume.save({
			available : availables
		},{
			saveData : ['available'],
			beforeSend: function(){
				view.loading.block(container);
			},
			success:function(model, resp){
				// unloading effect
				view.loading.unblock();

				var content = container.find('.cnt');
				var html 	= '';

				$(color_avai).each(function(index){
					html += '<div class="item">' + 
							'<div class="job-type color-'+this.color+'">' +
								'<span class="flag"></span>' + 
								  this.name + 
							'</div>' +
						'</div>';
				});
				content.html(html);

				// toggle view
				view.toggleInlineEdit(container);
			}
		});
	},
});

infoView = Backbone.View.extend({
	events: {
		'keyup input' 			: 'onKeyHandle',
		'dblclick .display' 	: 'onToggleEdit',
		'click .toggle-edit' 	: 'onToggleEdit',
		'click .save' 			: 'onSaveInfo',
		'submit .inline-edit form' 			: 'onSaveInfo',
	},
	initialize : function(params){
		var view = this;
		this.name 	= this.$el.find('input').attr('name');
		this.input 	= this.$el.find('input');
		this.onSave = params.onSave;

		// event on save
		this.on('saved', function(){
			view.saved();
		});
	},

	onSaveInfo: function(event){
		event.preventDefault();
		this.onSave(event, this);
	},

	saved: function(){
		this.$('.display .cnt').html( $(this.input).val() );
		this.toggleEdit();
	},

	onToggleEdit: function(event){
		event.preventDefault();
		this.toggleEdit();
	},

	toggleEdit: function(event){
		if (this.$el.hasClass('editing'))
			this.$el.removeClass('editing');
		else 
			this.$el.addClass('editing');
	},

	onKeyHandle : function(event){
		if (event.which == 27 ){
			this.toggleEdit();
		}else if(event.which == 13){
			this.onSave(event, this);
		}
	},

})

function unique(list) {
	var result = [];
	$.each(list, function(i, e) {
		if ($.inArray(e, result) == -1) result.push(e);
	});
	return result;
}

$(document).ready(function(){
	new JobEngine.Views.PageProfileManage();
});

})(jQuery);