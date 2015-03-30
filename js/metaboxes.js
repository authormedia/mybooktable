jQuery(document).ready(function() {

	/*---------------------------------------------------------*/
	/* Buy Buttons Metabox                                     */
	/*---------------------------------------------------------*/

	function mbt_reset_buybutton_numbers() {
		//there needs to be two passes so that ids are never reused, which breaks radio buttons
		jQuery('#mbt_buybutton_editors .mbt_buybutton_editor').each(function(i) {
			jQuery(this).find("div, input, textarea, select").each(function() {
				var ele = jQuery(this);
				if(ele.attr('name')) { ele.attr('name', ele.attr('name').replace(/mbt_buybutton\d*/, "mbt_buybutton_new_"+(i+1))); }
				if(ele.attr('id')) { ele.attr('id', ele.attr('id').replace(/mbt_buybutton\d*/, "mbt_buybutton_new_"+(i+1))); }
			});
		}).each(function() {
			jQuery(this).find("div, input, textarea, select").each(function() {
				var ele = jQuery(this);
				if(ele.attr('name')) { ele.attr('name', ele.attr('name').replace(/mbt_buybutton_new_*/, "mbt_buybutton")); }
				if(ele.attr('id')) { ele.attr('id', ele.attr('id').replace(/mbt_buybutton_new_*/, "mbt_buybutton")); }
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
			},
			function(response) {
				jQuery('#mbt_store_selector').removeAttr('disabled');
				jQuery('#mbt_buybutton_adder').removeAttr('disabled');
				element = jQuery(response);
				jQuery("#mbt_buybutton_editors").prepend(element);
				mbt_reset_buybutton_numbers();
			}
		);
		return false;
	});

	jQuery("#mbt_buybutton_editors").on("click", ".mbt_buybutton_remover", function() {
		jQuery(this).parents('.mbt_buybutton_editor').remove();
		mbt_reset_buybutton_numbers();
	});

	jQuery("#mbt_buybutton_editors").sortable({cancel: ".mbt_buybutton_editor_content,.mbt_buybutton_display_selector", stop: function(){mbt_reset_buybutton_numbers();}});

	// need to undisable form inputs or they will not be saved
	jQuery('form#post').submit(function() {
		jQuery("#mbt_buybutton_editors .mbt_buybutton_editor textarea").removeAttr("disabled");
		jQuery("#mbt_unique_id").removeAttr("disabled");
	});

	/*---------------------------------------------------------*/
	/* Book Image                                              */
	/*---------------------------------------------------------*/

	jQuery("#mbt_book_image_id").change(function() {
		jQuery.post(ajaxurl,
			{
				action: 'mbt_metadata_metabox',
				image_id: jQuery('#mbt_book_image_id').val(),
			},
			function(response) {
				if(response) {
					jQuery('#mbt_metadata .mbt-book-image').after(jQuery(response)).remove();
				}
			}
		);
	});

	/*---------------------------------------------------------*/
	/* Taxonomy Help                                           */
	/*---------------------------------------------------------*/

	jQuery('#mbt_authordiv .inside').append(jQuery(mbt_metabox_i18n.author_helptext));

});