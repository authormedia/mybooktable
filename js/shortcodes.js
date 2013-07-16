(
	function(){
		tinymce.create(
			"tinymce.plugins.mbt_shortcodes",
			{
				init: function(button, editor) {},

				createControl: function(button, editor) {
					if(button == "mbt_shortcodes_button") {
						button = editor.createMenuButton("mbt_shortcodes_button", {title: "Insert Shortcode", icons: false});
						var myself = this;
						button.onRenderMenu.add(function(children, item) {
							myself.add_shortcode(item, 'All Books', '[mybooktable]');
							myself.add_shortcode(item, 'All Books in Series', '[mybooktable series=""]');
							myself.add_shortcode(item, 'All Books in Genre', '[mybooktable genre=""]');
							myself.add_shortcode(item, 'All Books by Author', '[mybooktable author=""]');
							myself.add_shortcode(item, 'Single Book', '[mybooktable book=""]');
							myself.add_shortcode(item, 'Single Book Summary', '[mybooktable book="" display="summary"]');
						});
						return button;
					}
					return null;
				},

				add_shortcode: function(item, title, content){
					item.add({'title': title, onclick: function(){tinyMCE.activeEditor.execCommand("mceInsertContent", false, content)}});
				}

			}
		);

		tinymce.PluginManager.add("mbt_shortcodes", tinymce.plugins.mbt_shortcodes);
	}
)();