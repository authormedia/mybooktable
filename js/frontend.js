jQuery(document).ready(function() {
	//Find Bookstore Form
	jQuery('body').on('submit', 'form.mbt-find-bookstore-form', function() {

		var city = jQuery(this).find('.city').val();
		var state = jQuery(this).find('.state').val();
		var zip = jQuery(this).find('.zip').val();

		var url = jQuery(this).attr('action') + "?q=bookstore&near=" + city + "+" + state + "+" + zip;

		window.open(url, "", "");

		return false;
	});
});