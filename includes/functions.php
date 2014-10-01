<?php

/*---------------------------------------------------------*/
/* Settings Functions                                      */
/*---------------------------------------------------------*/

function mbt_load_settings() {
	global $mbt_settings;
	$mbt_settings = apply_filters("mbt_settings", get_option("mbt_settings"));
	if(empty($mbt_settings)) { mbt_reset_settings(); }
}

function mbt_reset_settings() {
	global $mbt_settings;
	$mbt_settings = array(
		'version' => MBT_VERSION,
		'api_key' => '',
		'api_key_status' => 0,
		'api_key_message' => '',
		'dev_active' => false,
		'pro_active' => false,
		'installed' => '',
		'installed_examples' => false,
		'booktable_page' => 0,
		'compatibility_mode' => true,
		'style_pack' => 'Default',
		'image_size' => 'medium',
		'enable_socialmedia_badges_single_book' => true,
		'enable_socialmedia_badges_book_excerpt' => true,
		'enable_socialmedia_bar_single_book' => true,
		'enable_seo' => true,
		'enable_breadcrumbs' => true,
		'show_series' => true,
		'show_find_bookstore' => true,
		'book_button_size' => 'medium',
		'listing_button_size' => 'medium',
		'widget_button_size' => 'medium',
		'hide_domc_notice' => false,
		'series_in_excerpts' => false,
		'posts_per_page' => false,
		'enable_default_affiliates' => false,
		'product_name' => __('Books', 'mybooktable'),
		'product_slug' => _x('books', 'URL slug', 'mybooktable')
	);
	$mbt_settings = apply_filters("mbt_default_settings", $mbt_settings);
	update_option("mbt_settings", apply_filters("mbt_update_settings", $mbt_settings));
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
	$active = (bool)mbt_get_setting('enable_socialmedia');
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

function mbt_get_posts_per_page() {
	$posts_per_page = mbt_get_setting('posts_per_page');
	return empty($posts_per_page) ? get_option('posts_per_page') : $posts_per_page;
}

function mbt_is_mbt_page() {
	return (is_post_type_archive('mbt_book') or is_tax('mbt_author') or is_tax('mbt_genre') or is_tax('mbt_series') or is_tax('mbt_tag') or is_singular('mbt_book') or mbt_is_booktable_page() or mbt_is_taxonomy_query());
}

function mbt_is_booktable_page() {
	global $mbt_is_booktable_page;
	return !empty($mbt_is_booktable_page);
}

function mbt_get_booktable_url() {

	if(mbt_get_setting('booktable_page') <= 0 or !get_page(mbt_get_setting('booktable_page'))) {
		$url = get_post_type_archive_link('mbt_book');
	} else {
		$url = get_permalink(mbt_get_setting('booktable_page'));
	}
	return $url;
}

function mbt_get_product_name() {
	$name = mbt_get_setting('product_name');
	return apply_filters('mbt_product_name', empty($name) ? __('Books', 'mybooktable') : $name);
}

function mbt_get_product_slug() {
	$slug = mbt_get_setting('product_slug');
	return apply_filters('mbt_product_slug', empty($slug) ? _x('books', 'URL slug', 'mybooktable') : $slug);
}



/*---------------------------------------------------------*/
/* Styles                                                  */
/*---------------------------------------------------------*/

function mbt_image_url($image) {
	$url = mbt_current_style_url($image);
	return apply_filters('mbt_image_url', empty($url) ? plugins_url('styles/Default/'.$image, dirname(__FILE__)) : $url);
}

function mbt_current_style_url($file) {
	$style = mbt_get_setting('style_pack');
	if(empty($style)) { $style = 'Default'; }

	$url = mbt_style_url($file, $style);
	if(empty($url) and $style !== 'Default') { $url = mbt_style_url($file, 'Default'); }

	return $url;
}

function mbt_style_url($file, $style) {
	foreach(mbt_get_style_folders() as $folder) {
		if(file_exists($folder['dir'].'/'.$style)) {
			if(file_exists($folder['dir'].'/'.$style.'/'.$file)) {
				return $folder['url'].'/'.rawurlencode($style).'/'.$file;
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

	if(is_wp_error($raw_response) || 200 != wp_remote_retrieve_response_code($raw_response)) {
		mbt_update_setting('api_key_status', -1);
		mbt_update_setting('api_key_message', __('Unable to connect to server!', 'mybooktable'));
		return;
	}

	$response = maybe_unserialize(wp_remote_retrieve_body($raw_response));

	if(!is_array($response) or empty($response['status'])) {
		mbt_update_setting('api_key_status', -2);
		mbt_update_setting('api_key_message', __('Invalid response received from server', 'mybooktable'));
		return;
	}

	$status = $response['status'];

	if($status == 10) {
		mbt_update_setting('api_key_status', $status);
		$expires = empty($response['expires']) ? '' : ' Expires '.date('F j, Y', $response['expires']).'.';
		mbt_update_setting('api_key_message', __('Valid for MyBookTable Professional.', 'mybooktable').$expires);
		mbt_update_setting('pro_active', true);
		mbt_update_setting('dev_active', false);
	} else if($status == 11) {
		mbt_update_setting('api_key_status', $status);
		$expires = empty($response['expires']) ? '' : ' Expires '.date('F j, Y', $response['expires']).'.';
		mbt_update_setting('api_key_message', __('Valid for MyBookTable Developer.', 'mybooktable').$expires);
		mbt_update_setting('pro_active', true);
		mbt_update_setting('dev_active', true);
	} else if($status == -10) {
		mbt_update_setting('api_key_status', $status);
		mbt_update_setting('api_key_message', __('Key not found', 'mybooktable'));
		mbt_update_setting('pro_active', false);
		mbt_update_setting('dev_active', false);
	} else if($status == -11) {
		mbt_update_setting('api_key_status', $status);
		mbt_update_setting('api_key_message', __('Key has been deactivated', 'mybooktable'));
		mbt_update_setting('pro_active', false);
		mbt_update_setting('dev_active', false);
	} else if($status == -12) {
		mbt_update_setting('api_key_status', $status);
		mbt_update_setting('api_key_message', __('Key has expired. Please <a href="http://www.authormedia.com/mybooktable">renew your license</a>.', 'mybooktable'));
		mbt_update_setting('pro_active', false);
		mbt_update_setting('dev_active', false);
	} else if($status == -20) {
		mbt_update_setting('api_key_status', $status);
		mbt_update_setting('api_key_message', __('Permissions error!', 'mybooktable'));
		mbt_update_setting('pro_active', false);
		mbt_update_setting('dev_active', false);
	} else {
		mbt_update_setting('api_key_status', -2);
		mbt_update_setting('api_key_message', __('Invalid response received from server', 'mybooktable'));
		mbt_update_setting('pro_active', false);
		mbt_update_setting('dev_active', false);
	}
}

function mbt_update_check($updates) {
	if(empty($updates->checked)) { return $updates; }

	mbt_verify_api_key();

	return apply_filters('mbt_update_check', $updates);
}
