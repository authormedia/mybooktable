jQuery(document).ready(function() {

	/*---------------------------------------------------------*/
	/* Wordpress Sidebar Link                                  */
	/*---------------------------------------------------------*/

	jQuery('a[href="admin.php?page=mbt_upgrade_link"]').on('click', function() { jQuery(this).attr('target', '_blank'); });

	/*---------------------------------------------------------*/
	/* Feedback Boxes                                          */
	/*---------------------------------------------------------*/

	jQuery('.mbt_feedback_refresh').mbt_feedback();

	/*---------------------------------------------------------*/
	/* Settings Page                                           */
	/*---------------------------------------------------------*/

	jQuery('#mbt-tabs').tabs({active: jQuery('#mbt_current_tab').val()-1});
	jQuery('#mbt-help-link').off();

 	jQuery('#mbt_settings_form input[type="submit"]').click(function() { jQuery('#mbt_current_tab').val(jQuery(this).parents('.mbt-tab').attr('id').substring(8)); });

	/*---------------------------------------------------------*/
	/* Help Page                                               */
	/*---------------------------------------------------------*/

	if(jQuery('.mbt_help #mbt_selected_tutorial_video').length > 0) {
		jQuery('.mbt_help #mbt_video_'+jQuery('.mbt_help #mbt_selected_tutorial_video').val()).show();
		jQuery('html, body').animate({scrollTop: jQuery('.mbt_help .mbt_video_tutorials').offset().top-42}, 2000);
	} else {
		jQuery('.mbt_help .mbt_video_display .mbt_video:first').show();
	}

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

	/*---------------------------------------------------------*/
	/* Ajax Event Tracking                                     */
	/*---------------------------------------------------------*/

	jQuery('*[data-mbt-track-event]').click(function() {
		mbt_track_event(jQuery(this).attr('data-mbt-track-event'));
	});

	jQuery('a[data-mbt-track-event-override]').click(function(e) {
		if(event.which == 1) {
			var element = jQuery(this);
			mbt_track_event(element.attr('data-mbt-track-event-override'), function() {
				window.location = element.attr('href');
			});
			return false;
		} else {
			mbt_track_event(jQuery(this).attr('data-mbt-track-event-override'));
		}
	});

});

function mbt_track_event(event_name, after) {
	var jqxhr = jQuery.post(ajaxurl, {action: 'mbt_track_event', event_name: event_name});
	if(typeof after !== 'undefined') { jqxhr.always(after); }
}

/*---------------------------------------------------------*/
/* Feedback Boxes                                          */
/*---------------------------------------------------------*/

function mbt_do_feedback_refresh(element) {
	if(!element.attr('disabled')) {
		element.attr('disabled', 'disabled');
		if(element.attr('type') == 'radio') { jQuery('input[name='+element.attr('name')+']').attr('disabled', 'disabled'); }
		var feedback = element.parent().find('.mbt_feedback');

		var loading_size = {'width': 18, 'height': 18};
		if(feedback.children().length > 0) {
			child = jQuery(feedback.children()[0]);
			loading_size = {'width': Math.max(child.width(), loading_size['width']), 'height': Math.max(child.height(), loading_size['height'])};
		}
		feedback.empty().append(jQuery('<div class="mbt_feedback_loading"><div class="mbt_feedback_spinner"></div></div>').css(loading_size));

		var data = null;
		if(element.attr('data-element').search(",") === -1) {
			data = jQuery('#'+element.attr('data-element')).val();
		} else {
			elements = element.attr('data-element').split(",");
			data = {};
			for(var i = elements.length - 1; i >= 0; i--) {
				data[elements[i]] = jQuery('#'+elements[i]).val();
			}
		}

		jQuery.post(ajaxurl,
			{
				action: element.attr('data-refresh-action'),
				data: data,
			},
			function(response) {
				element.removeAttr('disabled');
				if(element.attr('type') == 'radio') { jQuery('input[name='+element.attr('name')+']').removeAttr('disabled', 'disabled'); }
				feedback.html(response);
			}
		);
	}
}

jQuery.fn.mbt_feedback = function() {
	jQuery(this).each(function(i, e) {
		var element = jQuery(this);

		if(element.hasClass('mbt_feedback_refresh_initial')) { mbt_do_feedback_refresh(element); }

		if(element.prop("tagName") == "DIV") {
			element.click(function() {
				mbt_do_feedback_refresh(element);
				return false;
			});
		} else {
			element.change(function() { mbt_do_feedback_refresh(element); });
		}
	});
}
