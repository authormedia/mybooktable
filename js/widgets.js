jQuery(document).ready(function() {
	jQuery(".wrap").on("change", ".mbt_featured_book_selectmode", function() {
		element = jQuery(this);
		if(element.val() == "manual_select") {
			element.parents('.widget-content').find('.mbt_featured_book_manual_selector').show();
			element.parents('.widget-content').find('.mbt_featured_book_options').hide();
		} else {
			element.parents('.widget-content').find('.mbt_featured_book_manual_selector').hide();
			element.parents('.widget-content').find('.mbt_featured_book_options').show();
		}
	});
});