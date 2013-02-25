<?php

/*---------------------------------------------------------*/
/* Settings Functions                                      */
/*---------------------------------------------------------*/

function mbt_load_settings() {
	global $mbt_settings;
	$mbt_settings = apply_filters("mbt_settings", get_option("mbt_settings"));
}

function mbt_get_setting($name) {
	global $mbt_settings;
	return isset($mbt_settings[$name]) ? $mbt_settings[$name] : NULL;
}

function mbt_update_setting($name, $value) {
	global $mbt_settings;
	$mbt_settings[$name] = $value;
	update_option("mbt_settings", apply_filters("mbt_update_settings", $mbt_settings));
}



/*---------------------------------------------------------*/
/* General                                                 */
/*---------------------------------------------------------*/

function mbt_is_socialmedia_active() {
	$active = !mbt_get_setting('disable_socialmedia');
	return apply_filters('mbt_is_socialmedia_active', $active);
}

function mbt_save_taxonomy_image($taxonomy, $term, $url) {
	$taxonomy_images = get_option($taxonomy."_meta");
	if(empty($taxonomy_images)) { $taxonomy_images = array(); }
	$taxonomy_images[$term] = $url;
	update_option($taxonomy."_meta", $taxonomy_images);
}

function mbt_get_taxonomy_image($taxonomy, $term) {
	$taxonomy_images = get_option($taxonomy."_meta");
	if(empty($taxonomy_images)) { $taxonomy_images = array(); }
	return isset($taxonomy_images[$term]) ? $taxonomy_images[$term] : '';
}

function mbt_is_mbt_page() {
	$booktable_page = intval(mbt_get_setting('booktable_page'));
	return (is_post_type_archive('mbt_book') or is_tax('mbt_author') or is_tax('mbt_genre') or is_tax('mbt_series') or is_singular('mbt_book') or (!empty($booktable_page) and is_page($booktable_page)));
}