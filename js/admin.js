jQuery(document).ready(function() {

	/*---------------------------------------------------------*/
	/* Wordpress Sidebar Link                                  */
	/*---------------------------------------------------------*/

	jQuery('a[href="admin.php?page=mbt_upgrade_link"]').on('click', function() { jQuery(this).attr('target', '_blank'); });

	/*---------------------------------------------------------*/
	/* Settings Page                                           */
	/*---------------------------------------------------------*/

	jQuery('#mbt-tabs').tabs({active: jQuery('#mbt_current_tab').val()-1});

 	jQuery('#mbt_settings_form input[type="submit"]').click(function() { jQuery('#mbt_current_tab').val(jQuery(this).parents('.mbt-tab').attr('id').substring(8)); });

	jQuery('.mbt-accordion').accordion({heightStyle: "content"});

	function mbt_do_feedback_refresh(element) {
		if(!element.attr('disabled')) {
			element.attr('disabled', 'disabled');
			if(element.attr("type") == 'radio') { jQuery('input[name='+element.attr("name")+']').attr('disabled', 'disabled'); }

			jQuery.post(ajaxurl,
				{
					action: element.attr('data-refresh-action'),
					data: jQuery('#'+element.attr('data-element')).val(),
				},
				function(response) {
					element.removeAttr('disabled');
					if(element.attr("type") == 'radio') { jQuery('input[name='+element.attr("name")+']').removeAttr('disabled', 'disabled'); }
					element.parent().find(".mbt_feedback").html(response);
				}
			);
		}
	}

	jQuery('.mbt_feedback_refresh_initial').each(function(i, e) { mbt_do_feedback_refresh(jQuery(this)); });

	jQuery('.mbt_feedback_refresh').each(function(i, e) {
		var element = jQuery(this);

		if(element.prop("tagName") == "DIV") {
			element.click(function() {
				mbt_do_feedback_refresh(element);
				return false;
			});
		} else {
			element.change(function() { mbt_do_feedback_refresh(element); });
		}
	});

	/*---------------------------------------------------------*/
	/* Help Page                                               */
	/*---------------------------------------------------------*/

	jQuery('.mbt_help .mbt_video_display .mbt_video:first').show();

	jQuery('.mbt_help .mbt_video_selector a').click(function() {
		var video_id = jQuery(this).attr('data-video-id');
		jQuery('.mbt_help .mbt_video_display .mbt_video').hide().detach().appendTo(jQuery('.mbt_help .mbt_video_display'));
		jQuery('#'+video_id).show();
		return false;
	});

	/*---------------------------------------------------------*/
	/* Media Upload Buttons                                    */
	/*---------------------------------------------------------*/

	function mbt_make_uploader(button, data_element, title, desired_data) {
		var file_frame;

		jQuery(button).on('click', function(event) {

			event.preventDefault();

			// If the media frame already exists, reopen it.
			if(file_frame) {
				file_frame.open();
				return;
			}

			// Create the media frame.
			file_frame = wp.media.frames.file_frame = wp.media({
				title: title,
				button: { text: mbt_media_upload_i18n.select },
				multiple: false  // Set to true to allow multiple files to be selected
			});

			// When an image is selected, run a callback.
			file_frame.on( 'select', function() {
				// We set multiple to false so only get one image from the uploader
				attachment = file_frame.state().get('selection').first().toJSON();

				// Save the returned url
				jQuery(data_element).val(attachment[typeof desired_data !== 'undefined' ? desired_data : 'url']).trigger('change');
			});

			// Finally, open the modal
			file_frame.open();
		});
	};

	mbt_make_uploader('#mbt_upload_sample_button', '#mbt_sample_url', mbt_media_upload_i18n.mbt_upload_sample_button);
	mbt_make_uploader('#mbt_upload_tax_image_button', '#mbt_tax_image_url', mbt_media_upload_i18n.mbt_upload_tax_image_button);
	mbt_make_uploader('#mbt_set_book_image_button', '#mbt_book_image_id', mbt_media_upload_i18n.mbt_set_book_image_button, 'id');
	mbt_make_uploader('#mbt_upload_style_pack_button', '#mbt_style_pack_id', mbt_media_upload_i18n.select, 'id');

});