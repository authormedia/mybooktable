function make_uploader(button, urlbox, title, desired_data) {
	var file_frame;

	jQuery(button).live('click', function(event) {

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
			jQuery(urlbox).val(attachment[typeof desired_data !== 'undefined' ? desired_data : 'url']).trigger('change');
		});

		// Finally, open the modal
		file_frame.open();
	});
};

jQuery(document).ready(function() {
	make_uploader('#mbt_upload_sample_button', '#mbt_sample_url', mbt_media_upload_i18n.mbt_upload_sample_button);
	make_uploader('#mbt_upload_tax_image_button', '#mbt_tax_image_url', mbt_media_upload_i18n.mbt_upload_tax_image_button);
	make_uploader('#mbt_set_book_image_button', '#mbt_book_image_id', mbt_media_upload_i18n.mbt_set_book_image_button, 'id');
});