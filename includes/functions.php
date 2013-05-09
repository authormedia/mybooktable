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

function mbt_image_url($image) {
	return apply_filters('mbt_image_url_'.$image, plugins_url('images/'.$image, dirname(__FILE__)));
}




/*---------------------------------------------------------*/
/* API Interface                                           */
/*---------------------------------------------------------*/

function mbt_check_for_update() {
	$version = get_option("mbt_version");
	if(empty($version)) { return; }

	$to_send = array(
		'action' => 'basic_check',
		'version' => $version,
		'api-key' => md5(get_bloginfo('url'))
	);

	$options = array(
		'timeout' => ((defined('DOING_CRON') && DOING_CRON) ? 30 : 3),
		'body' => $to_send,
		'user-agent' => 'WordPress/'.$wp_version.'; '.get_bloginfo('url')
	);

	$raw_response = wp_remote_post('http://api.authormedia.com/plugins/mybooktable/update-check', $options);

	if(is_wp_error($raw_response) || 200 != wp_remote_retrieve_response_code($raw_response)) { return false; }

	$response = maybe_unserialize(wp_remote_retrieve_body($raw_response));

	if(!is_array($response)) { return; }

	$new_version = $response['new_version'];
	$package = $response['package'];

	if(empty($new_version) or empty($response)) { return; }

	$plugin_folder = plugin_basename(dirname(__FILE__));
	$data = (object) array(
		'slug' => $plugin_folder,
		'new_version' => $new_version,
		'url' => "http://www.mybooktable.com",
		'package' => $package
	);

	$plugin_transient = get_site_transient('update_plugins');
	$plugin_transient->response[$plugin_folder.'/mybooktable.php'] = $data;
	set_site_transient('update_plugins', $plugin_transient);
}