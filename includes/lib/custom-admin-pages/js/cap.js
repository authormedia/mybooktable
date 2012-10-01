/**
 * Controls the behaviours of custom metabox fields.
 *
 * @author Andrew Norcross
 * @author Jared Atchison
 * @author Bill Erickson
 * @see    https://github.com/jaredatch/Custom-Metaboxes-and-Fields-for-WordPress
 */

/*jslint browser: true, devel: true, indent: 4, maxerr: 50, sub: true */
/*global jQuery, tb_show, tb_remove */

/**
 * Custom jQuery for Custom Metaboxes and Fields
 */
jQuery(document).ready(function(){
	'use strict';

	var formfield;

	/**
	 * Initialize timepicker (this will be moved inline in a future release)
	 */
	jQuery('.cap_timepicker').each(function () {
		jQuery('#' + jQuery(this).attr('id')).timePicker({
			startTime: "07:00",
			endTime: "22:00",
			show24Hours: false,
			separator: ':',
			step: 30
		});
	});

	/**
	 * Initialize jQuery UI datepicker (this will be moved inline in a future release)
	 */
	jQuery('.cap_datepicker').each(function () {
		jQuery('#' + jQuery(this).attr('id')).datepicker();
		// $('#' + jQuery(this).attr('id')).datepicker({ dateFormat: 'yy-mm-dd' });
		// For more options see http://jqueryui.com/demos/datepicker/#option-dateFormat
	});
	// Wrap date picker in class to narrow the scope of jQuery UI CSS and prevent conflicts
	jQuery("#ui-datepicker-div").wrap('<div class="cap_element" />');
	
	/**
	 * Initialize color picker
	 */
    jQuery('input:text.cap_colorpicker').each(function (i) {
        jQuery(this).after('<div id="picker-' + i + '" style="z-index: 1000; background: #EEE; border: 1px solid #CCC; position: absolute; display: block;"></div>');
        jQuery('#picker-' + i).hide().farbtastic(jQuery(this));
    })
    .focus(function() {
        jQuery(this).next().show();
    })
    .blur(function() {
        jQuery(this).next().hide();
    });

	/**
	 * File and image upload handling
	 */
	jQuery('.cap_upload_button').live('click', function () {
		//tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
		var buttonLabel;
		formfield = jQuery(this).prev('input').attr('id');
		buttonLabel = "Use as "+jQuery(this).attr("name");
		tb_show('', 'media-upload.php?type=file&cap_force_send=true&cap_send_label=' + buttonLabel + '&TB_iframe=true');//
		return false;
	});

	jQuery('.cap_remove_file_button').live('click', function () {
		formfield = jQuery(this).attr('rel');
		jQuery('input#' + formfield).val('');
		jQuery(this).parent().remove();
		return false;
	});

	window.original_send_to_editor = window.send_to_editor;
    window.send_to_editor = function (html) {
		var itemurl, itemclass, itemClassBits, itemid, htmlBits, itemtitle,
			image, uploadStatus = true;

		if (formfield) {

	        if (jQuery(html).html(html).find('img').length > 0) {
				itemurl = jQuery(html).html(html).find('img').attr('src'); // Use the URL to the size selected.
	        } else {
				// It's not an image. Get the URL to the file instead.
				htmlBits = html.split("'"); // jQuery seems to strip out XHTML when assigning the string to an object. Use alternate method.
				itemurl = htmlBits[1]; // Use the URL to the file.
			}

			image = /(jpe?g|png|gif|ico)$/gi;

			if (itemurl.match(image)) {
				uploadStatus = '<div class="img_status"><img src="' + itemurl + '" alt="" /><a href="#" class="cap_remove_file_button" rel="' + formfield + '">Remove Image</a></div>';
			} else {
				// No output preview if it's not an image
				// Standard generic output if it's not an image.
				html = '<a href="' + itemurl + '" target="_blank" rel="external">View File</a>';
				uploadStatus = '<div class="no_image"><span class="file_link">' + html + '</span>&nbsp;&nbsp;&nbsp;<a href="#" class="cap_remove_file_button" rel="' + formfield + '">Remove</a></div>';
			}
			jQuery('#' + formfield).attr("value", itemurl);
			jQuery('#' + formfield).siblings('.cap_upload_status').slideDown().html(uploadStatus);
			tb_remove();

		} else {
			window.original_send_to_editor(html);
		}

		formfield = '';
	};
});