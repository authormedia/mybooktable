jQuery(document).ready(function() {
	function reset_numbers() {
		jQuery('#mbt_buybutton_editors .mbt_buybutton_editor').each(function(i) {
			jQuery(this).find("div, input, textarea, select").each(function() {
				ele = jQuery(this);
				if(ele.attr('name')) { ele.attr('name', ele.attr('name').replace(/mbt_buybutton\d*/, "mbt_buybutton"+i)); }
				if(ele.attr('id')) { ele.attr('id', ele.attr('id').replace(/mbt_buybutton\d*/, "mbt_buybutton"+i)); }
			});
		});
	}

	jQuery('#mbt_buybutton_adder').click(function(e) {
		if(!jQuery('#mbt_store_selector').val()){return false;}
		jQuery('#mbt_store_selector').attr('disabled', 'disabled');
		jQuery('#mbt_buybutton_adder').attr('disabled', 'disabled');
		jQuery.post(ajaxurl,
			{
				action: 'mbt_buybuttons_metabox',
				store: jQuery('#mbt_store_selector').val(),
				num: 0
			},
			function(response) {
				jQuery('#mbt_store_selector').removeAttr('disabled');
				jQuery('#mbt_buybutton_adder').removeAttr('disabled');
				element = jQuery(response);
				jQuery("#mbt_buybutton_editors").prepend(element);
				element.find(".mbt_buybutton_display_selector").each(apply_display_title);
				reset_numbers();
			}
		);
		return false;
	});

	jQuery("#mbt_buybutton_editors").on("click", ".mbt_buybutton_remover", function() {
		jQuery(this).parents('.mbt_buybutton_editor').remove();
		reset_numbers();
	});

	function display_description(display) {
		if(display == "book_only") { return mbt_metabox_i18n.book_only; }
		if(display == "text_only") { return mbt_metabox_i18n.text_only; }
		if(display == "featured") { return mbt_metabox_i18n.featured; }
	}
	function apply_display_title() {
		element = jQuery(this)
		element.tooltip();
		element.tooltip("option", "content", display_description(element.find("input").val()));
	}
	jQuery("#mbt_buybutton_editors").on("click", ".mbt_buybutton_display_selector", function() {
		element = jQuery(this);
		input = element.find('input');
		old_display = input.val();
		new_display = old_display == "featured" ? "book_only" : old_display == "book_only" ? "text_only" : "featured";
		input.val(new_display);
		element.removeClass("display_"+old_display);
		element.addClass("display_"+new_display);
		element.tooltip("option", "content", display_description(new_display));
	});
	jQuery(".mbt_buybutton_display_selector").each(apply_display_title);

	jQuery("#mbt_buybutton_editors").sortable({cancel: ".mbt_buybutton_editor_content,.mbt_buybutton_display_selector", stop: function(){reset_numbers();}});

	jQuery("#mbt_book_image_id").change(function(){
		jQuery.post(ajaxurl,
			{
				action: 'mbt_metadata_metabox',
				image_id: jQuery('#mbt_book_image_id').val(),
				num: 0
			},
			function(response) {
				if(response) {
					jQuery('#mbt_metadata .mbt-book-image').after(jQuery(response)).remove();
				}
			}
		);
	});
});