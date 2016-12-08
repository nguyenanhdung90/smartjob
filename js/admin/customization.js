(function($){
JobEngine.Views.BackendCustomization = Backbone.View.extend({
	el : $('#engine_setting_content'),
	events : {
		'click .inner-menu li a'					: 'chooseSection',
		'click ul.list-column-style a'				: 'chooseLayout',
		'submit form#change_style'					: 'applyStyle',
		'click ul.list-color-schemes > li > div'	: 'chooseScheme',
		'click a#preview_style'						: 'previewStyle',
		'change textarea[name=custom_style]'		: 'updateCustomStyle'
	},
	initialize: function(){
		var i, $container;

		// apply custom look for select box
		this.$('.select-style select').each(function(){
			var $this = jQuery(this),
				title = $this.attr('title'),
				selectedOpt	= $this.find('option:selected');
			
			if( selectedOpt.val() !== '' ){
				title = selectedOpt.text();
			}

			$this.css({'z-index':10,'opacity':0,'-khtml-appearance':'none'})
				.after('<span class="select">' + title + '</span>')
				.change(function(){
					var val = jQuery('option:selected',this).text();
					jQuery(this).next().text(val);
				});
		});

		// activate color picker
		// this.$('.color-scheme .color-item').each(function(){
		// 	var $this = $(this);
		// 	$this.find('.modify').etColorPicker({target: $this});
		// });

		this.$('.color-scheme .color-item').each(function(){
			var $this = $(this);
			var trigger = $this.find('.modify');

			trigger.ColorPicker({
				color : $this.getHexBackgroundColor(),
				onChange : function(hsb, hex, rgb){
					$this.css('background-color', '#' + hex );
				}
			});
		});

		// create an array of targets for generating uploaders
		this.uploaderIDs	= ['website_logo','mobile_icon','default_logo'];
		this.uploaderThumbs	= ['large','thumbnail','company-logo'];
		this.uploaders		= [];

		var blockUi = new JobEngine.Views.BlockUi(),
			cbBeforeSend = function(ele){
				button = $(ele).find('.image');
				blockUi.block(button);
			},
			cbSuccess = function(){
				blockUi.unblock();
			};

		// loop through the array to init uploaders
		for( i=0; i<this.uploaderIDs.length; i++ ){
			// get the container of the target
			$container	= this.$('#' + this.uploaderIDs[i] + '_container');

			this.uploaders[this.uploaderIDs[i]]	= new JobEngine.Views.File_Uploader({
				el					: $container,
				uploaderID			: this.uploaderIDs[i],
				thumbsize			: this.uploaderThumbs[i],
				multipart_params	: {
					_ajax_nonce	: $container.find('.et_ajaxnonce').attr('id'),
					action		: 'et-change-branding',
					imgType		: this.uploaderIDs[i]
				},
				cbUploaded	: this.cbUploaded,
				beforeSend	: cbBeforeSend,
				success		: cbSuccess
			});
		}

		// Autosize textarea
		$('textarea.autosize').autosize();

		this.applySlider(jQuery(".slider-area-1"), [18,29]);
		this.applySlider(jQuery(".slider-area-2"), [12,14]);
	},

	applySlider : function(object, range, value){
		var min = range[0] || 18,
			max = range[1] || 29,
			value = $(object).attr('data') || min;

		value = Math.max( Math.min(value, max), min );

 		// Slider
		var slide_min = min, slide_max = max, 
			pad_left = 100/(slide_max-slide_min),
			this_slider = jQuery(object),
			notification = this_slider.find(".notification-value");

		notification.html(slide_min);
		this_slider.find(".slider").slider({
			min: min,
			max: max,
			range: "min",
			value: value,
			slide: function( event, ui ) {
				notification.css({ "left": pad_left*(ui.value-slide_min)+"%" }).html( ui.value );
				// save value to input
				this_slider.find('input[type=hidden]').val(ui.value + 'px');
			},
			create : function(event, ui){
				notification.css({ "left": pad_left*(value-slide_min)+"%" }).html( value );
				// save value to input
				this_slider.find('input[type=hidden]').val(value + 'px');
			}
		});

		for (i=slide_min; i<slide_max; i++){
			this_slider.find(".pad-line").append('<div style="left: '+pad_left * (i-slide_min)+'%"></div>');
		}
	},

	// callback, run when uploading completed
	cbUploaded	: function(up,file,res){
		pubsub.trigger('je:notification',{
			msg	: res.msg,
			notice_type	: (res.success) ? 'success' : 'error'
		});
	},

	chooseScheme : function(e){
		e.preventDefault();
		if ( !$(e.target).hasClass('modify') ) {
			$('ul.list-color-schemes > li > div').removeClass('active');
			$(e.currentTarget).addClass('active');
		}
	},

	chooseSection : function(event){
		event.preventDefault();

		var current = $(event.target);
		$('.inner-content').hide();
		$('.inner-menu li a.active').removeClass('active');
		$(current.attr('href')).show();
		current.addClass('active');

		// route url
		Backbone.history.navigate('section/ET_MenuCustomization/' + current.attr('menu-data'));
	},

	// choose a layout for theme
	chooseLayout : function (event) {
		event.preventDefault ();
		var $target	= $(event.currentTarget),
			layout	= $target.attr ('rel'),
			blockUi = new JobEngine.Views.BlockUi(),
			current = $('.list-column-style li a.active')[0];

		$.ajax ({
			url : et_globals.ajaxURL,
			type : 'post',
			data : {
				action : 'et-change-layout',
				layout : layout
			},
			beforeSend : function  () {
				blockUi.block($target.parent());
				$('.list-column-style li a.active').removeClass('active');
				$target.addClass('active');
			},
			success : function (response) {
				blockUi.unblock();
				if(response.success) {
				}else {
					$('.list-column-style li a.active').removeClass('active');
					$(current).addClass('active');
				}
			}
			
		});
	},

	getStyleData : function(){
		var colorsDiv		= $('ul.list-color-schemes > li > div.active');
		var italicPattern	= /italic/;
		var boldPattern = /bold/;
		var data = {
			background	: colorsDiv.find('.color-background').css('background-color'),
			header		: colorsDiv.find('.color-header').css('background-color'),
			heading	: colorsDiv.find('.color-heading').css('background-color'),
			text		: colorsDiv.find('.color-text').css('background-color'),
			action		: colorsDiv.find('.color-action').css('background-color'),
			'font-heading'			: $('select.font-heading').val(),
			'font-heading-style'	: italicPattern.test( $('select.font-heading-style').val() ) ? 'italic' : 'normal',
			'font-heading-weight'	: boldPattern.test( $('select.font-heading-style').val() ) ? 'bold' : 'normal',
			'font-heading-size'		: $('input.font-heading-size').val(),
			'font-text'				: $('select.font-text').val(),
			'font-text-style'		: italicPattern.test( $('select.font-text-style').val() ) ? 'italic' : 'normal',
			'font-text-weight'		: boldPattern.test( $('select.font-text-style').val() ) ? 'bold' : 'normal',
			'font-text-size'		: $('input.font-text-size').val(),
			'font-action'			: $('select.font-action').val(),
			'font-action-style'		: italicPattern.test( $('select.font-action-style').val() ) ? 'italic' : 'normal',
			'font-action-weight'	: boldPattern.test( $('select.font-action-style').val() ) ? 'bold' : 'normal',
			'font-action-size'		: $('input.font-action-size').val()
		};
		return data;
	},

	applyStyle : function(event){
		event.preventDefault();
		var button = $('form#change_style #save_style');
		// prevent submit when being disabled
		if( button.hasClass('disabled') ) return false;

		//search style
		var colorsDiv	= $('ul.list-color-schemes > li > div.active');
		var data		= this.getStyleData();
		var loadingBtn	= new JobEngine.Views.LoadingButton({el : button});

		var colorSets	= $('ul.list-color-schemes > li');
		var colors		= [];
		colorSets.each(function(){
			var c = [];
			$(this).find('.color-item').map(function(){
				c.push( $(this).css('background-color') );
			});
			colors.push(c);
		});

		// build ajax request
		var params = ajaxParams;
		params.data = {
			action : 'et-save-style',
			content : {
				data : data,
				colors : colors,
				choosenColor : colorsDiv.parent().index()
			}
		};
		params.beforeSend = function(){
			loadingBtn.loading();
		};
		params.success = function(){
			loadingBtn.finish();
		};
		$.ajax( params );
	},

	previewStyle : function(e){
		e.preventDefault();

		var button = $(e.currentTarget);
		// prevent submit when being disabled
		if ( button.hasClass('disabled') ) return false;

		var data = this.getStyleData();
		var params = ajaxParams;
		var loadingBtn = new JobEngine.Views.LoadingButton({el : $(e.currentTarget)});
		params.data = {
			action : 'et-preview-style',
			content : {
				data : data
			}
		};
		params.beforeSend = function(){
			loadingBtn.loading();
		};
		params.success = function(resp){
			loadingBtn.finish();
			if ( resp.success ){
				var content = $('<div id="preview_style" style="display:none"></div>')
					.css({
						position: 'absolute',
						left: '100px',
						right: '100px'
					})
					.html('<iframe src="'+resp.data.url+'" frameborder="0" width="100%" height="100%"></iframe>');
				$('body').append( content );
				var modal = new JobEngine.Views.Modal_Box({el : content, width: $(window).width() - 200, height: $(window).height() - 200 });
				modal.openModal();
			}
		};
		$.ajax( params );
	},

	updateCustomStyle : function(e){
		var params = ajaxParams;
		params.data = {
			action : 'et-update-custom-style',
			content : {
				style : $('textarea[name=custom_style]').val()
			}
		};
		var blockUi = new JobEngine.Views.BlockUi();
		params.beforeSend =  function(){
			blockUi.block($('textarea[name=custom_style]').parent());
		};
		params.success = function(){
			blockUi.unblock();
		};

		$.ajax(params);
	}
});

$.fn.etColorPicker = function(options){
	options = $.extend( {target : false}, options );
	var currentTarget = null;
	$(this).each(function(index){
		$(this).bind('click', function(e){
			e.preventDefault();
			$('.colorpicker').hide().remove();

			var $this	= $(this),
				offset	= $this.offset(),
				height	= $this.height(),
				picker	= $('<div id="colorpicker" class="colorpicker"></div>').css({ position: 'absolute', 'z-index' : 9999, display: 'none', left: offset.left , top: offset.top + height }),
				farb;

			if ( !options.target )
				currentTarget = $this;
			else
				currentTarget = $(options.target);

			$('body').prepend(picker);

			farb = $.farbtastic('#colorpicker', function(color){
				currentTarget.css('background', color);
			});

			farb.setColor( currentTarget.getHexBackgroundColor() );

			picker.fadeIn('normal', function(){
				$('body').unbind();
				$('body').bind('click', function(e){
					if ( !picker.is(':hidden') && $('#colorpicker').has( e.target ).length === 0 ){
						picker.hide().remove();
						$('body').unbind('click');
					}
				});
			});
		});

	});
};

$.fn.getHexBackgroundColor = function() {
	var rgb = $(this).css('background-color'),
		hex_rgb;

	if (!rgb) {
		return '#FFFFFF'; //default color
	}
	hex_rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
	function hex(x) {return ("0" + parseInt(x,10).toString(16)).slice(-2);}
	if (hex_rgb) {
		return "#" + hex(hex_rgb[1]) + hex(hex_rgb[2]) + hex(hex_rgb[3]);
	} else {
		return rgb; //ie8 returns background-color in hex format then it will make compatible, you can improve it checking if format is in hexadecimal
	}
};

})(jQuery);