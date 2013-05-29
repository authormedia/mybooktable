jQuery(document).ready(function() {
	jQuery('#mbt_api_key_refresh').click(function(e) {
		if(!jQuery('#mbt_api_key_refresh').attr('disabled')) {
			jQuery('#mbt_api_key_refresh').attr('disabled', 'disabled');
			jQuery.post(ajaxurl,
				{
					action: 'mbt_api_key_refresh',
					api_key: jQuery('#mbt_api_key').val(),
				},
				function(response) {
					jQuery('#mbt_api_key_refresh').removeAttr('disabled');
					jQuery(".mbt_api_key_feedback").html(response);
				}
			);
		}
		return false;
	});
});