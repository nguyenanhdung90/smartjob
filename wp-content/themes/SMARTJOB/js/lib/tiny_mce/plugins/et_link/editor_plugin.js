(function () {
    tinymce.create("tinymce.plugins.etLink", {
        init: function (a, b) {
            this.editor = a;
            a.addCommand("etLink", function () {
                var c = a.selection;
                if (c.isCollapsed() && !a.dom.getParent(c.getNode(), "A")) {
                    return
                }
                elm = a.selection.getNode();
                if( elm.nodeName == "A") {
                	a.execCommand ('unlink',false, null);
                	return ;
                }
                a.windowManager.open({
                    file: b + "/link.htm",
                    width: 480 ,
                    height: 100 ,
                    inline: 1
                }, {
                    plugin_url: b
                })
            });
            a.addButton("etlink", {
                title: "Link/Unkink",
                cmd: "etLink"                
            });
            a.addShortcut("ctrl+k", "abc xyz", "etLink");
            a.onNodeChange.add(function (d, c, f, e) {
                c.setDisabled("etlink", e && f.nodeName != "A");
                c.setActive("etlink", f.nodeName == "A" && !f.name);
            })
        },
        getInfo: function () {
            return {
                longname: "Engine theme link plugin",
                author: "Engine themes",
                authorurl: "http://enginethemes.com",
                version: 1.0
            }
        }
    });
    tinymce.PluginManager.add("etLink", tinymce.plugins.etLink)
})();