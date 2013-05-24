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

function mbt_is_booktable_page() {
	global $mbt_is_booktable_page;
	return !empty($mbt_is_booktable_page);
}



/*---------------------------------------------------------*/
/* Styles                                                  */
/*---------------------------------------------------------*/

function mbt_image_url($image) {
	$url = mbt_current_style_url($image);
	return apply_filters('mbt_image_url', empty($url) ? plugins_url('styles/Default/'.$image, dirname(__FILE__)) : $url);
}

function mbt_current_style_url($url) {
	$style = mbt_get_setting('style_pack');
	if(empty($style)) { $style = 'Default'; }

	$url = mbt_style_url($url, $style);
	if(empty($url)) { $url = mbt_style_url($url, 'Default'); }

	return $url;
}

function mbt_style_url($url, $style) {
	$folders = mbt_get_style_folders();

	foreach($folders as $folder) {
		if(file_exists($folder['dir'].'/'.$style)) {
			if(file_exists($folder['dir'].'/'.$style.'/'.$url)) {
				return $folder['url'].'/'.$style.'/'.$url;
			}
		}
	}

	return '';
}

function mbt_get_style_packs() {
	$folders = mbt_get_style_folders();
	$styles = array();

	foreach($folders as $folder) {
		if($handle = opendir($folder['dir'])) {
			while(false !== ($entry = readdir($handle))) {
				if ($entry != '.' and $entry != '..' and $entry != 'Default' and !in_array($entry, $styles)) {
					$styles[] = $entry;
				}
			}
			closedir($handle);
		}
	}

	return $styles;
}

function mbt_get_style_folders() {
	return apply_filters('mbt_style_folders', array());
}

function mbt_add_default_style_folder($folders) {
	$folders[] = array('dir' => plugin_dir_path(dirname(__FILE__)).'styles', 'url' => plugins_url('styles', dirname(__FILE__)));
	return $folders;
}
add_filter('mbt_style_folders', 'mbt_add_default_style_folder', 100);



/*---------------------------------------------------------*/
/* API / Updates                                           */
/*---------------------------------------------------------*/

function mbt_set_api_key($api_key) {
	if($api_key == mbt_get_setting('api_key')) { return; }
	mbt_update_setting('api_key', $api_key);
	mbt_verify_api_key();
}

function mbt_verify_api_key() {
	global $wp_version;

	$to_send = array(
		'action' => 'basic_check',
		'version' => MBT_VERSION,
		'api-key' => mbt_get_setting('api_key'),
		'site' => md5(get_bloginfo('url'))
	);

	$options = array(
		'timeout' => 3,
		'body' => $to_send,
		'user-agent' => 'WordPress/'.$wp_version.'; '.get_bloginfo('url')
	);

	$raw_response = wp_remote_post('http://www.authormedia.com/plugins/mybooktable/key-check', $options);

	if(is_wp_error($raw_response) || 200 != wp_remote_retrieve_response_code($raw_response)) { return; }

	$response = maybe_unserialize(wp_remote_retrieve_body($raw_response));

	if(!is_array($response)) { return; }

	$key_valid = $response['key_valid'];

	if($key_valid) {
		mbt_update_setting('api_key_valid', true);
		$pro_active = $response['pro_active'];
		mbt_update_setting('pro_active', !empty($pro_active));
		$dev_active = $response['dev_active'];
		mbt_update_setting('dev_active', !empty($dev_active));
	} else {
		mbt_update_setting('api_key_valid', false);
		mbt_update_setting('pro_active', false);
		mbt_update_setting('dev_active', false);
	}
}

function mbt_update_check($updates) {
	global $wp_version;
	if(empty($updates->checked)) { return $updates; }

	mbt_verify_api_key();

	$to_send = array(
		'action' => 'basic_check',
		'version' => MBT_VERSION,
		'api-key' => mbt_get_setting('api_key'),
		'site' => get_bloginfo('url')
	);

	$options = array(
		'timeout' => ((defined('DOING_CRON') && DOING_CRON) ? 30 : 3),
		'body' => $to_send,
		'user-agent' => 'WordPress/'.$wp_version.'; '.get_bloginfo('url')
	);

	$raw_response = wp_remote_post('http://www.authormedia.com/plugins/mybooktable/update-check', $options);

	if(!is_wp_error($raw_response) and wp_remote_retrieve_response_code($raw_response) == 200) {

		$response = maybe_unserialize(wp_remote_retrieve_body($raw_response));

		if(is_array($response) and !empty($response['new_version']) and !empty($response['package'])) {
			$new_version = $response['new_version'];
			$package = $response['package'];

			$plugin_folder = plugin_basename(dirname(dirname(__FILE__)));
			$data = (object) array(
				'slug' => 'mybooktable',
				'new_version' => $new_version,
				'url' => "http://www.mybooktable.com",
				'package' => $package
			);
			$updates->response[$plugin_folder.'/mybooktable.php'] = $data;
		}
	}

	return apply_filters('mbt_update_check', $updates);
}

function mbt_plugin_information() {
	if($_REQUEST['plugin'] == "mybooktable") {
		wp_redirect('http://www.authormedia.com/mybooktable');
		die();
	}
}
add_action('install_plugins_pre_plugin-information', 'mbt_plugin_information');