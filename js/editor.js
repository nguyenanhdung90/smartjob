jQuery(document).ready (function ($) {
	
tinyMCE.init({
	//script_url : et_globals.jsURL + "/lib/tiny_mce/tiny_mce.js",
	mode : 'none',
	theme : "advanced",
	dialog_type : 'modal',
	plugins : et_editor.je_plugins,
	//language : "",
	// Theme options
	theme_advanced_buttons1 : et_editor.theme_advanced_buttons1,
	theme_advanced_buttons2	: et_editor.theme_advanced_buttons2,
	theme_advanced_buttons3 : et_editor.theme_advanced_buttons3,
	theme_advanced_buttons4  : et_editor.theme_advanced_buttons4,
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_resizing : true,
	theme_advanced_statusbar_location : "bottom",
	theme_advanced_resizing_use_cookie : false,
	theme_advanced_resizing_max_width : 470,
	//onchange_callback : et_editor.onchange_callback,
	spellchecker_languages : '+English=en',
	skin : "o2k7",
	skin_variant : et_editor.skin,
	invalid_elements : "h1,h2,h3,h4,h5,#h6",
	//valid_elements:  "*[*]",
	// Example content CSS (should be your site CSS)
	content_css : et_editor.jsURL+"/lib/tiny_mce/content.css",
	//paste_auto_cleanup_on_paste : true,
	paste_remove_styles : true,
	
	formats :{
		alignleft : [
			{selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles : {textAlign : 'left'}},
			{selector : 'img,table', classes : 'alignleft'}
		],
		aligncenter : [
			{selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles : {textAlign : 'center'}},
			{selector : 'img,table', classes : 'aligncenter'}
		],
		alignright : [
			{selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles : {textAlign : 'right'}},
			{selector : 'img,table', classes : 'alignright'}
		],
		strikethrough : {inline : 'del'}
	},
	onchange_callback : function (ed) {
		//tinyMCE.triggerSave();
		//$("#" + ed.id).valid();
	},
	// bind the event for every editor instance
	setup	: function(ed){
		ed.onChange.add(function(ed, l) {
			var content	= ed.getContent();
			if(ed.isDirty() || content === '' ){
				ed.save();
				$(ed.getElement()).blur(); // trigger change event for textarea
			}

		});

		// We set a tabindex value to the iframe instead of the initial textarea
		ed.onInit.add(function() {
			var editorId = ed.editorId,
				textarea = $('#'+editorId);
			$('#'+editorId+'_ifr').attr('tabindex', textarea.attr('tabindex'));
			textarea.attr('tabindex', null);
		});
	}
});

});
