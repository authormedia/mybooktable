jQuery(document).ready(function() {

	/*---------------------------------------------------------*/
	/* Author Priority Slider                                  */
	/*---------------------------------------------------------*/

	function update_mbt_author_priority_label(event, ui) {
		var value = ui ? ui.value : jQuery("#mbt_author_priority_slider").slider("value");
		jQuery("#mbt_author_priority").val(value);
		if(value == 0) {
			jQuery("#mbt_author_priority_display").html("Lowest");
			jQuery("#mbt_author_priority_display").css("color", "#888");
		} else if(value == 25) {
			jQuery("#mbt_author_priority_display").html("Low");
			jQuery("#mbt_author_priority_display").css("color", "#666");
		}  else if(value == 75) {
			jQuery("#mbt_author_priority_display").html("High");
			jQuery("#mbt_author_priority_display").css("color", "#222");
		}  else if(value == 100) {
			jQuery("#mbt_author_priority_display").html("Highest");
			jQuery("#mbt_author_priority_display").css("color", "#000");
		} else {
			jQuery("#mbt_author_priority_display").html("Medium");
			jQuery("#mbt_author_priority_display").css("color", "#444");
		}
	}

	jQuery("#mbt_author_priority_slider").slider({
		value: jQuery("#mbt_author_priority").val(),
		min: 0,
		max: 100,
		step: 25,
		slide: update_mbt_author_priority_label
	});
	update_mbt_author_priority_label();

});