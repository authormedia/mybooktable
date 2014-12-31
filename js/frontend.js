jQuery(document).ready(function() {

	//---------------------------
	// Find Bookstore Form
	//---------------------------

	var geocoder = new google.maps.Geocoder();
	var formtimer = null;

	function mbt_update_bookstore_form(form) {
		form.find('[type="submit"]').prop('disabled', true);
		window.clearTimeout(formtimer);
		formtimer = setTimeout(function() {
			var city = form.find('.mbt-city').val();
			var state = form.find('.mbt-state').val();
			var zip = form.find('.mbt-zip').val();

			geocoder.geocode({ 'address': city + " " + state + " " + zip }, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					var lat = results[0].geometry.location.k;
					var lng = results[0].geometry.location.D;
					var url = "https://www.google.com/maps/search/bookstore/@"+lat+","+lng+",14z";
					form.attr('action', url);
					form.find('[type="submit"]').prop('disabled', false);
				}
			});
		}, 1000);
	}
	
	jQuery('form.mbt-find-bookstore-form').each(function(i, e) {
		var form = jQuery(e);

		updatefn = function() { mbt_update_bookstore_form(form); }
		form.find('.mbt-city').change(updatefn).keypress(updatefn);
		form.find('.mbt-state').change(updatefn).keypress(updatefn);
		form.find('.mbt-zip').change(updatefn).keypress(updatefn);

		form.submit(function() {
			window.open(form.attr('action'), "", "");
			return false;
		});
	});

});