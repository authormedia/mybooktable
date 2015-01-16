jQuery(document).ready(function() {
	jQuery('.mbt_api_key_refresh').click(function(e) {
		var element = jQuery(this);

		if(!element.attr('disabled')) {
			element.attr('disabled', 'disabled');

			jQuery.post(ajaxurl,
				{
					action: element.attr('data-refresh-action'),
					api_key: jQuery('#'+element.attr('data-key-element')).val(),
				},
				function(response) {
					element.removeAttr('disabled');
					element.parent().find(".mbt_api_key_feedback").html(response);
				}
			);
		}
		return false;
	});
});