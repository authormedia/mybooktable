// Uploading files
var file_frame;

jQuery('#mbt_upload_sample_button').live('click', function(event) {

	event.preventDefault();

	// If the media frame already exists, reopen it.
	if(file_frame) {
		file_frame.open();
		return;
	}

	// Create the media frame.
	file_frame = wp.media.frames.file_frame = wp.media({
		title: "Sample Chapter Upload",
		button: { text: "Select" },
		multiple: false  // Set to true to allow multiple files to be selected
	});

	// When an image is selected, run a callback.
	file_frame.on( 'select', function() {
		// We set multiple to false so only get one image from the uploader
		attachment = file_frame.state().get('selection').first().toJSON();

		// Save the returned url
		jQuery('#mbt_sample_url').val(attachment['url']);
	});

	// Finally, open the modal
	file_frame.open();
});