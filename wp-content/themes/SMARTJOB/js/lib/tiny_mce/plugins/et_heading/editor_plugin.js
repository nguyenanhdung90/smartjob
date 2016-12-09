(function() {
	tinymce.create('tinymce.plugins.etHeading', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			var disabled = true;
			
			ed.addCommand ('etHeading', function () {
				ed.execCommand ('FormatBlock',false, 'h6');
			});
			// Register example button
			ed.addButton('et_heading', {
				title : 'Heading(Ctrl+Alt+H)',
				image  : url + '/h.png',
				// cmd : 'etHeading',
				onclick : function() {
		            ed.execCommand ('FormatBlock',false, 'h6');
		        },
				cmd : 'etHeading'
			});

			ed.addShortcut('ctrl+alt+h', ed.getLang('Ctrl+Alt+H'), 'etHeading');

			ed.onNodeChange.add(function(ed, cm, n, co) {
				cm.setDisabled("et_heading", co && n.nodeName != "H");
				cm.setActive("et_heading", n.nodeName == "H" && !n.name);
			});
		},
		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'Enginetheme Heading',
				author : 'Engine teams',
				authorurl : 'http://enginethemes.com',
				infourl : '',
				version : "1.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('etHeading', tinymce.plugins.etHeading);
})();